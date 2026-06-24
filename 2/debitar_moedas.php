<?php 
require("send.php");

$codpedido = $_GET['codpedido'];
$codcliente = $_GET['codcliente'];

// Item 14: custo de moedas vem da CATEGORIA (categoria.codigo = pedido.subcategoria).
// Se a categoria não tiver custo definido, usa o grupo como fallback; por último, padrão 5.
$custoMoedas = 0;
$qCusto = mysqli_query($con, "SELECT c.moeda FROM pedido p INNER JOIN categoria c ON c.codigo = p.subcategoria WHERE p.codigo = '".mysqli_real_escape_string($con, $codpedido)."'");
if ($qCusto && $rCusto = mysqli_fetch_array($qCusto)) {
    $custoMoedas = (int)$rCusto['moeda'];
}
if ($custoMoedas <= 0) {
    $qCustoG = mysqli_query($con, "SELECT g.custo_moedas FROM pedido p INNER JOIN grupos g ON g.codigo = p.categoria WHERE p.codigo = '".mysqli_real_escape_string($con, $codpedido)."'");
    if ($qCustoG && $rCustoG = mysqli_fetch_array($qCustoG)) {
        $custoG = (int)$rCustoG['custo_moedas'];
        if ($custoG > 0) $custoMoedas = $custoG;
    }
}
if ($custoMoedas <= 0) $custoMoedas = 5; // último recurso

$queryMoedas = mysqli_query($con, "select * from quantidade_pedidos where tipo = 'pre' and codcadastro='".$codcliente."'");
$rowM = mysqli_fetch_array($queryMoedas);

if (!$rowM || $rowM['qtd'] < $custoMoedas) {
    echo "<script>alert('Você não tem moedas suficientes. Necessário: $custoMoedas moeda(s).');window.location.href='minhasmoedas.php';</script>";
    exit;
}

$qtdMoeda = $rowM['qtd'] - $custoMoedas;

// Debita as moedas
$editaQtd = mysqli_query($con, "update quantidade_pedidos set qtd='$qtdMoeda' where codcadastro = '".$codcliente."'") or die(mysqli_error($con));

// Registra no extrato de moedas
mysqli_query($con, "INSERT INTO moedas_extrato (codcadastro, tipo, quantidade, descricao, codpedido, data_hora) VALUES ('".$codcliente."', 'debito', '$custoMoedas', 'Débito para pedido #$codpedido', '$codpedido', NOW())");

// Atualiza status do pedido
$editaPedidoPedi = mysqli_query($con, "update pedido set status='Prestador Disponível' where codigo = '".$_GET['codpedido']."'") or die(mysqli_error($con));

// Marca outros orçamentos como perdidos
$editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='p', visto=0 where codpedido = '".$_GET['codpedido']."'") or die(mysqli_error($con));

// IMPORTANTE: Só marca como 's' (confirmado) APÓS o débito bem-sucedido
$editaPedidoCads2 = mysqli_query($con, "update disparo_pedidos set aceito='s', visto=0 where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$_GET['codcliente']."'") or die(mysqli_error($con));

// Atualiza o timer como confirmado
mysqli_query($con, "update timer_acordo set status='confirmado' where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$_GET['codcliente']."'");

echo "<script>alert('$custoMoedas Moeda(s) Debitada(s)! Acordo firmado com sucesso.');window.location.href='meus-orcamentos2.php';</script>"; 


 ?>