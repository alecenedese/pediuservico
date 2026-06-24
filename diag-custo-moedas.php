<?php
// Diagnóstico do custo de moedas de um pedido.
// Acesse: /pediuservico/diag-custo-moedas.php?codpedido=NUMERO
require_once("send.php");
header("Content-Type: text/plain; charset=utf-8");

$codpedido = isset($_GET['codpedido']) ? (int)$_GET['codpedido'] : 0;
if ($codpedido <= 0) {
    die("Informe ?codpedido=NUMERO");
}

echo "=== DIAGNÓSTICO DE CUSTO DE MOEDAS — pedido #$codpedido ===\n\n";

// 1) Dados do pedido
$qP = mysqli_query($con, "SELECT codigo, categoria, subcategoria, status FROM pedido WHERE codigo='$codpedido' LIMIT 1");
if (!$qP || !($p = mysqli_fetch_assoc($qP))) {
    die("Pedido não encontrado.\n");
}
echo "PEDIDO:\n";
echo "  codigo        = {$p['codigo']}\n";
echo "  categoria (grupo) = {$p['categoria']}\n";
echo "  subcategoria  = {$p['subcategoria']}  <-- usado no JOIN com categoria.codigo\n";
echo "  status        = {$p['status']}\n\n";

// 2) Categoria correspondente (mesmo JOIN do débito)
$qC = mysqli_query($con, "SELECT codigo, titulo, codgrupo, moeda FROM categoria WHERE codigo='".mysqli_real_escape_string($con,$p['subcategoria'])."' LIMIT 1");
echo "CATEGORIA (categoria.codigo = pedido.subcategoria):\n";
if ($qC && $c = mysqli_fetch_assoc($qC)) {
    echo "  codigo  = {$c['codigo']}\n";
    echo "  titulo  = {$c['titulo']}\n";
    echo "  codgrupo= {$c['codgrupo']}\n";
    echo "  moeda   = '".$c['moeda']."'  (int = ".(int)$c['moeda'].")\n\n";
} else {
    echo "  >>> NENHUMA categoria encontrada com codigo = {$p['subcategoria']}.\n";
    echo "  >>> Por isso o débito cai no valor padrão (5). O pedido aponta para uma subcategoria que NÃO existe na tabela 'categoria'.\n\n";
}

// 3) Grupo (fallback)
$qG = mysqli_query($con, "SELECT codigo, titulo, custo_moedas FROM grupos WHERE codigo='".mysqli_real_escape_string($con,$p['categoria'])."' LIMIT 1");
echo "GRUPO (fallback):\n";
if ($qG && $g = mysqli_fetch_assoc($qG)) {
    echo "  codigo       = {$g['codigo']}\n";
    echo "  titulo       = {$g['titulo']}\n";
    echo "  custo_moedas = '".(isset($g['custo_moedas']) ? $g['custo_moedas'] : '(coluna inexistente)')."'\n\n";
} else {
    echo "  Grupo não encontrado.\n\n";
}

// 4) Reproduz exatamente a lógica do débito
$custoMoedas = 5;
$qCusto = mysqli_query($con, "SELECT c.moeda FROM pedido p INNER JOIN categoria c ON c.codigo = p.subcategoria WHERE p.codigo = '$codpedido'");
if ($qCusto && $rCusto = mysqli_fetch_array($qCusto)) {
    $custoMoedas = max(1, (int)$rCusto['moeda']);
}
echo "RESULTADO DA LÓGICA ATUAL DO DÉBITO:\n";
echo "  custoMoedas calculado = $custoMoedas\n";
echo ($custoMoedas == 5 ? "  (valor padrão — indica que o JOIN não achou a categoria ou a moeda está como 5)\n" : "  (lido da categoria)\n");
