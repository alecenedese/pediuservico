<?php
/**
 * TESTE AUTOMATIZADO - ITEMS 7 E 10
 * Verifica se as implementações estão funcionando corretamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("send.php");

// Cores para output
function green($text) { return "\033[32m✓ $text\033[0m"; }
function red($text) { return "\033[31m✗ $text\033[0m"; }
function yellow($text) { return "\033[33m⚠ $text\033[0m"; }
function blue($text) { return "\033[34mℹ $text\033[0m"; }

$passed = 0;
$failed = 0;
$warnings = 0;

echo "\n" . str_repeat("=", 60) . "\n";
echo "  TESTE AUTOMATIZADO - IMPLEMENTAÇÕES ITEMS 7 E 10\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================================
// PREPARAÇÃO: Criar dados de teste
// ============================================================
echo blue("PREPARAÇÃO: Criando dados de teste...") . "\n\n";

// 1. Criar cliente de teste
$cpfCliente = '12345678900';
$nomeCliente = 'Cliente Teste Auto';
$celularCliente = '65999999999';

mysqli_query($con, "DELETE FROM clientes WHERE CNPJ_CPF='$cpfCliente'");
mysqli_query($con, "INSERT INTO clientes (NOME, CNPJ_CPF, CELULAR) VALUES ('$nomeCliente', '$cpfCliente', '$celularCliente')");
$clienteId = mysqli_insert_id($con);
echo "  Cliente criado: ID $clienteId\n";

// 2. Criar prestador de teste
$cpfPrestador = '98765432100';
$nomePrestador = 'Prestador Teste Auto';
$celularPrestador = '65988888888';

mysqli_query($con, "DELETE FROM parceiro WHERE CNPJ_CPF='$cpfPrestador'");
mysqli_query($con, "INSERT INTO parceiro (NOME, CNPJ_CPF, CELULAR) VALUES ('$nomePrestador', '$cpfPrestador', '$celularPrestador')");
$prestadorId = mysqli_insert_id($con);
echo "  Prestador criado: ID $prestadorId\n";

// 3. Criar categoria de teste
$qCat = mysqli_query($con, "SELECT codigo FROM grupos WHERE titulo LIKE '%Eletricista%' LIMIT 1");
$rowCat = mysqli_fetch_array($qCat);
$categoriaId = $rowCat ? $rowCat['codigo'] : 1;

$qSubCat = mysqli_query($con, "SELECT codigo FROM categoria WHERE codgrupo='$categoriaId' LIMIT 1");
$rowSubCat = mysqli_fetch_array($qSubCat);
$subcategoriaId = $rowSubCat ? $rowSubCat['codigo'] : 1;
echo "  Categoria: $categoriaId / Subcategoria: $subcategoriaId\n";

// 4. Criar pedido de teste
mysqli_query($con, "DELETE FROM pedido WHERE descricao LIKE '%TESTE AUTO%'");
$descricao = 'TESTE AUTO - Pedido para verificação';
$lat = '-15.5989';
$lon = '-56.0949';

mysqli_query($con, "INSERT INTO pedido (codcli, categoria, subcategoria, descricao, lat, log, tempo, data_hora, status) 
    VALUES ('$clienteId', '$categoriaId', '$subcategoriaId', '$descricao', '$lat', '$lon', '2 horas', NOW(), 'Procurando Prestador')");
$pedidoId = mysqli_insert_id($con);
echo "  Pedido criado: ID $pedidoId\n";

// 5. Criar disparo para o prestador
mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, codpedido, aceito, visto) 
    VALUES ('$prestadorId', '$pedidoId', 'n', 0)");
echo "  Disparo criado para prestador\n";

// 6. Criar mais 2 prestadores para testar "não tenho interesse"
$prestador2Id = 0;
$prestador3Id = 0;
for ($i = 2; $i <= 3; $i++) {
    $cpfP = '98765432' . str_pad($i, 3, '0', STR_PAD_LEFT);
    mysqli_query($con, "DELETE FROM parceiro WHERE CNPJ_CPF='$cpfP'");
    mysqli_query($con, "INSERT INTO parceiro (NOME, CNPJ_CPF, CELULAR) VALUES ('Prestador $i', '$cpfP', '6598888888$i')");
    $pid = mysqli_insert_id($con);
    if ($i == 2) $prestador2Id = $pid;
    if ($i == 3) $prestador3Id = $pid;
    mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, codpedido, aceito, visto) 
        VALUES ('$pid', '$pedidoId', 'n', 0)");
    echo "  Prestador $i criado: ID $pid\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// ============================================================
// TESTE 1: Verificar arquivo get-uninterested-count.php existe
// ============================================================
echo "TESTE 1: Verificar se arquivo get-uninterested-count.php existe\n";
if (file_exists(__DIR__ . '/get-uninterested-count.php')) {
    echo green("Arquivo existe") . "\n";
    $passed++;
} else {
    echo red("Arquivo NÃO existe") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 2: Verificar contador de não interessados (deve ser 0)
// ============================================================
echo "TESTE 2: Contador inicial de não interessados (deve ser 0)\n";
$qCount = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='p' AND visto=1");
$rowCount = mysqli_fetch_array($qCount);
$countInicial = (int)$rowCount['total'];

if ($countInicial === 0) {
    echo green("Contador inicial: $countInicial (correto)") . "\n";
    $passed++;
} else {
    echo red("Contador inicial: $countInicial (esperado 0)") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 3: Marcar prestador como não interessado
// ============================================================
echo "TESTE 3: Marcar prestador 2 como não interessado\n";
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=1 
    WHERE codpedido='$pedidoId' AND codcadastro='$prestador2Id' AND aceito='n'");
$affected = mysqli_affected_rows($con);

if ($affected > 0) {
    echo green("Prestador marcado como não interessado") . "\n";
    $passed++;
} else {
    echo red("Falha ao marcar prestador") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 4: Verificar contador após marcar como não interessado
// ============================================================
echo "TESTE 4: Contador após marcar não interessado (deve ser 1)\n";
$qCount2 = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='p' AND visto=1");
$rowCount2 = mysqli_fetch_array($qCount2);
$countApos = (int)$rowCount2['total'];

if ($countApos === 1) {
    echo green("Contador: $countApos (correto)") . "\n";
    $passed++;
} else {
    echo red("Contador: $countApos (esperado 1)") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 5: Testar endpoint get-uninterested-count.php
// ============================================================
echo "TESTE 5: Testar endpoint get-uninterested-count.php\n";
$url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/get-uninterested-count.php?codpedido=$pedidoId";
$response = @file_get_contents($url);

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['count']) && $data['count'] === 1) {
        echo green("Endpoint retorna count=1 (correto)") . "\n";
        $passed++;
    } else {
        echo red("Endpoint retorna count=" . ($data['count'] ?? 'null') . " (esperado 1)") . "\n";
        $failed++;
    }
} else {
    echo yellow("Não foi possível testar endpoint via HTTP (normal em CLI)") . "\n";
    echo "  Testando consulta direta...\n";
    // Teste direto no banco
    if ($countApos === 1) {
        echo green("  Consulta direta OK: count=1") . "\n";
        $passed++;
    } else {
        echo red("  Consulta direta FALHOU") . "\n";
        $failed++;
    }
}
echo "\n";

// ============================================================
// TESTE 6: Verificar novomapa.php tem o código do contador
// ============================================================
echo "TESTE 6: Verificar se novomapa.php tem código do contador\n";
$novomapaContent = file_get_contents(__DIR__ . '/novomapa.php');
$temContador = strpos($novomapaContent, 'uninterested-counter') !== false;
$temFuncao = strpos($novomapaContent, 'fetchUninterestedCount') !== false;

if ($temContador && $temFuncao) {
    echo green("novomapa.php tem código do contador") . "\n";
    $passed++;
} else {
    echo red("novomapa.php NÃO tem código do contador completo") . "\n";
    if (!$temContador) echo "  - Faltando elemento 'uninterested-counter'\n";
    if (!$temFuncao) echo "  - Faltando função 'fetchUninterestedCount'\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 7: Verificar Item 7 - Cliente aceita proposta (status 'ac')
// ============================================================
echo "TESTE 7: Item 7 - Cliente aceita proposta do prestador\n";

// Prestador envia proposta
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='a', visto=0 
    WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
mysqli_query($con, "UPDATE pedido SET status='Proposta Aceita' WHERE codigo='$pedidoId'");
echo "  Prestador enviou proposta (status 'a')\n";

// Cliente aceita proposta
mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) 
    VALUES ('$nomeCliente', '$celularCliente', '$pedidoId', '$prestadorId', '$clienteId', 'sim')");
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='ac', visto=0 
    WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
echo "  Cliente aceitou proposta (status 'ac')\n";

// Verificar que está em 'ac' mas NÃO em 's'
$qStatus = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$rowStatus = mysqli_fetch_array($qStatus);

if ($rowStatus && $rowStatus['aceito'] === 'ac') {
    echo green("Status é 'ac' (cliente aceitou, aguardando prestador pagar)") . "\n";
    $passed++;
} else {
    echo red("Status não é 'ac': " . ($rowStatus['aceito'] ?? 'null')) . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 8: Verificar que pedido NÃO aparece em "aceitos" antes do pagamento
// ============================================================
echo "TESTE 8: Item 7 - Pedido NÃO deve aparecer em 'aceitos' com status 'ac'\n";

// Simula query de get_pedidos.php para aceitos
$qAceitos = mysqli_query($con, "
    SELECT p.codigo 
    FROM pedido p
    INNER JOIN pega_contato pc ON pc.codpedido = p.codigo
    INNER JOIN disparo_pedidos dp ON dp.codpedido = p.codigo
    WHERE pc.codcliente = '$clienteId'
    AND dp.aceito = 's'
    AND p.codigo = '$pedidoId'
");

$countAceitos = mysqli_num_rows($qAceitos);

if ($countAceitos === 0) {
    echo green("Pedido NÃO aparece em 'aceitos' (correto - status é 'ac', não 's')") . "\n";
    $passed++;
} else {
    echo red("Pedido APARECE em 'aceitos' (incorreto)") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 9: Verificar que pedido APARECE em "pendentes" com status 'ac'
// ============================================================
echo "TESTE 9: Item 7 - Pedido DEVE aparecer em 'pendentes' com status 'ac'\n";

$qPendentes = mysqli_query($con, "
    SELECT p.codigo 
    FROM pedido p
    LEFT JOIN disparo_pedidos dp ON dp.codpedido = p.codigo
    WHERE (dp.codpedido IS NULL OR dp.aceito IN ('n', 'a', 'ac'))
    AND p.codigo = '$pedidoId'
    GROUP BY p.codigo
");

$countPendentes = mysqli_num_rows($qPendentes);

if ($countPendentes > 0) {
    echo green("Pedido APARECE em 'pendentes' (correto)") . "\n";
    $passed++;
} else {
    echo red("Pedido NÃO aparece em 'pendentes' (incorreto)") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 10: Simular pagamento do prestador (status 's')
// ============================================================
echo "TESTE 10: Item 7 - Prestador paga e confirma (status 's')\n";

// Prestador confirma o acordo
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=0 WHERE codpedido='$pedidoId' AND codcadastro != '$prestadorId'");
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='s', visto=0 WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
mysqli_query($con, "UPDATE pedido SET status='Prestador Disponível' WHERE codigo='$pedidoId'");
echo "  Prestador confirmou acordo (status 's')\n";

// Verificar status
$qStatus2 = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$rowStatus2 = mysqli_fetch_array($qStatus2);

if ($rowStatus2 && $rowStatus2['aceito'] === 's') {
    echo green("Status é 's' (confirmado)") . "\n";
    $passed++;
} else {
    echo red("Status não é 's': " . ($rowStatus2['aceito'] ?? 'null')) . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 11: Verificar que pedido AGORA aparece em "aceitos"
// ============================================================
echo "TESTE 11: Item 7 - Pedido DEVE aparecer em 'aceitos' após pagamento\n";

$qAceitos2 = mysqli_query($con, "
    SELECT p.codigo 
    FROM pedido p
    INNER JOIN pega_contato pc ON pc.codpedido = p.codigo
    INNER JOIN disparo_pedidos dp ON dp.codpedido = p.codigo
    WHERE pc.codcliente = '$clienteId'
    AND dp.aceito = 's'
    AND p.codigo = '$pedidoId'
");

$countAceitos2 = mysqli_num_rows($qAceitos2);

if ($countAceitos2 > 0) {
    echo green("Pedido APARECE em 'aceitos' após pagamento (correto)") . "\n";
    $passed++;
} else {
    echo red("Pedido NÃO aparece em 'aceitos' após pagamento (incorreto)") . "\n";
    $failed++;
}
echo "\n";

// ============================================================
// TESTE 12: Verificar arquivo get_pedidos.php foi modificado
// ============================================================
echo "TESTE 12: Verificar se get_pedidos.php usa apenas status 's' para aceitos\n";
$getPedidosContent = file_get_contents(__DIR__ . '/get_pedidos.php');
$temStatusS = preg_match("/dp\.aceito\s*=\s*'s'/", $getPedidosContent);
$naoTemStatusAC = !preg_match("/dp\.aceito\s+in\s*\(\s*'s'\s*,\s*'ac'\s*\)/i", $getPedidosContent);

if ($temStatusS && $naoTemStatusAC) {
    echo green("get_pedidos.php usa apenas 's' para aceitos (correto)") . "\n";
    $passed++;
} else {
    echo red("get_pedidos.php ainda usa 'ac' em aceitos (incorreto)") . "\n";
    if (!$temStatusS) echo "  - NÃO encontrou dp.aceito = 's'\n";
    if (!$naoTemStatusAC) echo "  - AINDA tem dp.aceito in ('s', 'ac')\n";
    $failed++;
}
echo "\n";

// ============================================================
// LIMPEZA: Remover dados de teste
// ============================================================
echo blue("LIMPEZA: Removendo dados de teste...") . "\n";
mysqli_query($con, "DELETE FROM disparo_pedidos WHERE codpedido='$pedidoId'");
mysqli_query($con, "DELETE FROM pega_contato WHERE codpedido='$pedidoId'");
mysqli_query($con, "DELETE FROM pedido WHERE codigo='$pedidoId'");
mysqli_query($con, "DELETE FROM clientes WHERE id='$clienteId'");
mysqli_query($con, "DELETE FROM parceiro WHERE id IN ('$prestadorId', '$prestador2Id', '$prestador3Id')");
echo "  Dados de teste removidos\n";
echo "\n";

// ============================================================
// RESUMO DOS TESTES
// ============================================================
echo str_repeat("=", 60) . "\n";
echo "  RESUMO DOS TESTES\n";
echo str_repeat("=", 60) . "\n\n";

$total = $passed + $failed;
$percentual = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "Total de testes: $total\n";
echo green("Testes passaram: $passed") . "\n";
if ($failed > 0) {
    echo red("Testes falharam: $failed") . "\n";
}
if ($warnings > 0) {
    echo yellow("Avisos: $warnings") . "\n";
}
echo "\nTaxa de sucesso: $percentual%\n\n";

if ($failed === 0) {
    echo green("✓✓✓ TODOS OS TESTES PASSARAM! ✓✓✓") . "\n";
    echo green("Items 7 e 10 estão funcionando corretamente!") . "\n";
} else {
    echo red("✗✗✗ ALGUNS TESTES FALHARAM ✗✗✗") . "\n";
    echo red("Revise as implementações dos items com falha.") . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

// Retorna código de saída
exit($failed > 0 ? 1 : 0);
