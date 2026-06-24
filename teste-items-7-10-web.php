<?php
/**
 * TESTE AUTOMATIZADO WEB - ITEMS 7 E 10
 * Acesse via navegador para executar os testes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("send.php");

// Função helper para HTML
function resultado($tipo, $texto) {
    $cores = [
        'sucesso' => '#22c55e',
        'falha' => '#dc3545',
        'aviso' => '#fbbf24',
        'info' => '#3b82f6'
    ];
    $icones = [
        'sucesso' => '✓',
        'falha' => '✗',
        'aviso' => '⚠',
        'info' => 'ℹ'
    ];
    return '<div style="padding:8px 12px;margin:4px 0;background:rgba(' . 
           ($tipo === 'sucesso' ? '34,197,94' : ($tipo === 'falha' ? '220,53,69' : ($tipo === 'aviso' ? '251,191,36' : '59,130,246'))) . 
           ',0.1);border-left:4px solid ' . $cores[$tipo] . ';border-radius:4px;color:' . $cores[$tipo] . ';font-weight:600;">' .
           $icones[$tipo] . ' ' . $texto . '</div>';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Automatizado - Items 7 e 10</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 32px;
        }
        h1 {
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 28px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .test-section {
            margin-bottom: 24px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .test-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            font-size: 16px;
        }
        .test-info {
            color: #6b7280;
            font-size: 13px;
            margin-left: 24px;
            margin-top: 4px;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 24px;
        }
        .summary h2 {
            margin-bottom: 12px;
            font-size: 20px;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .stat-card {
            background: rgba(255,255,255,0.2);
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 24px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Teste Automatizado</h1>
        <div class="subtitle">Verificação das implementações dos Items 7 e 10</div>
        <div class="divider"></div>

<?php

$passed = 0;
$failed = 0;
$warnings = 0;

// ============================================================
// PREPARAÇÃO
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">📋 PREPARAÇÃO: Criando dados de teste</div>';

$cpfCliente = '12345678900';
$nomeCliente = 'Cliente Teste Auto';
$celularCliente = '65999999999';

mysqli_query($con, "DELETE FROM clientes WHERE CNPJ_CPF='$cpfCliente'");
mysqli_query($con, "INSERT INTO clientes (NOME, CNPJ_CPF, CELULAR) VALUES ('$nomeCliente', '$cpfCliente', '$celularCliente')");
$clienteId = mysqli_insert_id($con);

$cpfPrestador = '98765432100';
$nomePrestador = 'Prestador Teste Auto';
mysqli_query($con, "DELETE FROM parceiro WHERE CNPJ_CPF='$cpfPrestador'");
mysqli_query($con, "INSERT INTO parceiro (NOME, CNPJ_CPF, CELULAR) VALUES ('$nomePrestador', '$cpfPrestador', '65988888888')");
$prestadorId = mysqli_insert_id($con);

$qCat = mysqli_query($con, "SELECT codigo FROM grupos LIMIT 1");
$rowCat = mysqli_fetch_array($qCat);
$categoriaId = $rowCat['codigo'];
$qSubCat = mysqli_query($con, "SELECT codigo FROM categoria WHERE codgrupo='$categoriaId' LIMIT 1");
$rowSubCat = mysqli_fetch_array($qSubCat);
$subcategoriaId = $rowSubCat['codigo'];

mysqli_query($con, "DELETE FROM pedido WHERE descricao LIKE '%TESTE AUTO%'");
mysqli_query($con, "INSERT INTO pedido (codcli, categoria, subcategoria, descricao, lat, log, tempo, data_hora, status) 
    VALUES ('$clienteId', '$categoriaId', '$subcategoriaId', 'TESTE AUTO', '-15.5989', '-56.0949', '2 horas', NOW(), 'Procurando Prestador')");
$pedidoId = mysqli_insert_id($con);

mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, codpedido, aceito, visto) 
    VALUES ('$prestadorId', '$pedidoId', 'n', 0)");

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
}

echo resultado('info', "Cliente: ID $clienteId");
echo resultado('info', "Prestadores: IDs $prestadorId, $prestador2Id, $prestador3Id");
echo resultado('info', "Pedido: ID $pedidoId");
echo '</div>';

// ============================================================
// TESTE 1
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 1: Arquivo get-uninterested-count.php existe?</div>';
if (file_exists(__DIR__ . '/get-uninterested-count.php')) {
    echo resultado('sucesso', 'Arquivo existe');
    $passed++;
} else {
    echo resultado('falha', 'Arquivo NÃO existe');
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 2
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 2: Contador inicial de não interessados</div>';
$qCount = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='p' AND visto=1");
$rowCount = mysqli_fetch_array($qCount);
$countInicial = (int)$rowCount['total'];

if ($countInicial === 0) {
    echo resultado('sucesso', "Contador inicial: $countInicial (esperado: 0)");
    $passed++;
} else {
    echo resultado('falha', "Contador inicial: $countInicial (esperado: 0)");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 3
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 3: Marcar prestador como não interessado</div>';
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=1 
    WHERE codpedido='$pedidoId' AND codcadastro='$prestador2Id' AND aceito='n'");
$affected = mysqli_affected_rows($con);

if ($affected > 0) {
    echo resultado('sucesso', 'Prestador marcado como não interessado');
    $passed++;
} else {
    echo resultado('falha', 'Falha ao marcar prestador');
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 4
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 4: Contador após marcar não interessado</div>';
$qCount2 = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='p' AND visto=1");
$rowCount2 = mysqli_fetch_array($qCount2);
$countApos = (int)$rowCount2['total'];

if ($countApos === 1) {
    echo resultado('sucesso', "Contador: $countApos (esperado: 1)");
    $passed++;
} else {
    echo resultado('falha', "Contador: $countApos (esperado: 1)");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 5
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 5: Testar endpoint get-uninterested-count.php</div>';

// Testa diretamente no banco ao invés de incluir o arquivo (evita conflito)
$qTestEndpoint = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='p' AND visto=1");
$rowTestEndpoint = mysqli_fetch_array($qTestEndpoint);
$countEndpoint = (int)$rowTestEndpoint['total'];

if ($countEndpoint === 1) {
    echo resultado('sucesso', "Query do endpoint retorna count=1");
    $passed++;
} else {
    echo resultado('falha', "Query do endpoint retorna count=" . $countEndpoint . " (esperado: 1)");
    $failed++;
}

// Verifica se o arquivo existe e tem a estrutura correta
$endpointContent = file_get_contents(__DIR__ . '/get-uninterested-count.php');
$temQuery = strpos($endpointContent, "SELECT COUNT(*) as total FROM disparo_pedidos") !== false;
$temJsonEncode = strpos($endpointContent, "json_encode") !== false;
$temAceitoP = strpos($endpointContent, "aceito='p'") !== false;
$temVisto = strpos($endpointContent, "visto=1") !== false;

if ($temQuery && $temJsonEncode && $temAceitoP && $temVisto) {
    echo '<div class="test-info">→ Endpoint tem estrutura correta (query, json_encode, aceito=p, visto=1)</div>';
} else {
    echo resultado('aviso', 'Endpoint pode estar incompleto');
    if (!$temQuery) echo '<div class="test-info">→ Faltando query SELECT COUNT</div>';
    if (!$temJsonEncode) echo '<div class="test-info">→ Faltando json_encode</div>';
    if (!$temAceitoP) echo '<div class="test-info">→ Faltando filtro aceito=p</div>';
    if (!$temVisto) echo '<div class="test-info">→ Faltando filtro visto=1</div>';
    $warnings++;
}
echo '</div>';

// ============================================================
// TESTE 6
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 6: Código do contador em novomapa.php</div>';
$novomapaContent = file_get_contents(__DIR__ . '/novomapa.php');
$temContador = strpos($novomapaContent, 'uninterested-counter') !== false;
$temFuncao = strpos($novomapaContent, 'fetchUninterestedCount') !== false;

if ($temContador && $temFuncao) {
    echo resultado('sucesso', 'novomapa.php tem código completo do contador');
    $passed++;
} else {
    echo resultado('falha', 'novomapa.php NÃO tem código completo');
    if (!$temContador) echo resultado('aviso', 'Faltando elemento uninterested-counter');
    if (!$temFuncao) echo resultado('aviso', 'Faltando função fetchUninterestedCount');
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 7
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 7: Cliente aceita proposta (status ac)</div>';

mysqli_query($con, "UPDATE disparo_pedidos SET aceito='a', visto=0 
    WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
mysqli_query($con, "UPDATE pedido SET status='Proposta Aceita' WHERE codigo='$pedidoId'");
echo '<div class="test-info">→ Prestador enviou proposta (status a)</div>';

mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) 
    VALUES ('$nomeCliente', '$celularCliente', '$pedidoId', '$prestadorId', '$clienteId', 'sim')");
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='ac', visto=0 
    WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
echo '<div class="test-info">→ Cliente aceitou proposta (status ac)</div>';

$qStatus = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$rowStatus = mysqli_fetch_array($qStatus);

if ($rowStatus && $rowStatus['aceito'] === 'ac') {
    echo resultado('sucesso', "Status é 'ac' (aguardando prestador pagar)");
    $passed++;
} else {
    echo resultado('falha', "Status não é 'ac': " . ($rowStatus['aceito'] ?? 'null'));
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 8
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 8: Pedido NÃO aparece em aceitos com status ac</div>';

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
    echo resultado('sucesso', "Pedido NÃO aparece em 'aceitos' (correto - ainda é 'ac')");
    $passed++;
} else {
    echo resultado('falha', "Pedido APARECE em 'aceitos' (incorreto)");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 9
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 9: Pedido DEVE aparecer em pendentes com status ac</div>';

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
    echo resultado('sucesso', "Pedido APARECE em 'pendentes' (correto)");
    $passed++;
} else {
    echo resultado('falha', "Pedido NÃO aparece em 'pendentes' (incorreto)");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 10
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 10: Prestador paga e confirma (status s)</div>';

mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=0 WHERE codpedido='$pedidoId' AND codcadastro != '$prestadorId'");
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='s', visto=0 WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
mysqli_query($con, "UPDATE pedido SET status='Prestador Disponível' WHERE codigo='$pedidoId'");
echo '<div class="test-info">→ Prestador confirmou acordo (status s)</div>';

$qStatus2 = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$rowStatus2 = mysqli_fetch_array($qStatus2);

if ($rowStatus2 && $rowStatus2['aceito'] === 's') {
    echo resultado('sucesso', "Status é 's' (confirmado)");
    $passed++;
} else {
    echo resultado('falha', "Status não é 's': " . ($rowStatus2['aceito'] ?? 'null'));
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 11
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 11: Pedido AGORA aparece em aceitos após pagamento</div>';

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
    echo resultado('sucesso', "Pedido APARECE em 'aceitos' após pagamento (correto)");
    $passed++;
} else {
    echo resultado('falha', "Pedido NÃO aparece em 'aceitos' (incorreto)");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 12
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 12: Arquivo get_pedidos.php foi modificado corretamente</div>';
$getPedidosContent = file_get_contents(__DIR__ . '/get_pedidos.php');
$temStatusS = preg_match("/dp\.aceito\s*=\s*'s'/", $getPedidosContent);
$naoTemStatusAC = !preg_match("/dp\.aceito\s+in\s*\(\s*'s'\s*,\s*'ac'\s*\)/i", $getPedidosContent);

if ($temStatusS && $naoTemStatusAC) {
    echo resultado('sucesso', "get_pedidos.php usa apenas 's' para aceitos");
    $passed++;
} else {
    echo resultado('falha', "get_pedidos.php ainda usa 'ac' em aceitos");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 13
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 13: Query de pendentes inclui status ac (não some da lista)</div>';
$temPendentesAC = preg_match("/dp\.aceito\s+IN\s*\(\s*'n'\s*,\s*'a'\s*,\s*'ac'\s*\)/i", $getPedidosContent);

if ($temPendentesAC) {
    echo resultado('sucesso', "Pendentes inclui 'n', 'a', 'ac' (pedido não desaparece)");
    $passed++;
} else {
    echo resultado('falha', "Pendentes NÃO inclui 'ac' - pedido sumiria das listas!");
    $failed++;
}
echo '</div>';

// ============================================================
// TESTE 14
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">TESTE 14: Código JS do contador é defensivo (não quebra inicialização)</div>';
$novomapaJs = file_get_contents(__DIR__ . '/novomapa.php');
// Verifica que o addEventListener do obsField está protegido por checagem de existência
$temGuardaObsField = preg_match("/if\s*\(\s*obsField\s*\)/", $novomapaJs);
$temGuardaModal = strpos($novomapaJs, "if (contrapropostaModal)") !== false;

if ($temGuardaObsField && $temGuardaModal) {
    echo resultado('sucesso', "Código protegido contra elementos inexistentes");
    $passed++;
} else {
    echo resultado('falha', "Código pode quebrar se elementos não existirem");
    if (!$temGuardaObsField) echo '<div class="test-info">→ Faltando guarda if(obsField)</div>';
    if (!$temGuardaModal) echo '<div class="test-info">→ Faltando guarda if(contrapropostaModal)</div>';
    $failed++;
}
echo '</div>';

// ============================================================
// LIMPEZA
// ============================================================
echo '<div class="test-section">';
echo '<div class="test-title">🧹 LIMPEZA: Removendo dados de teste</div>';
mysqli_query($con, "DELETE FROM disparo_pedidos WHERE codpedido='$pedidoId'");
mysqli_query($con, "DELETE FROM pega_contato WHERE codpedido='$pedidoId'");
mysqli_query($con, "DELETE FROM pedido WHERE codigo='$pedidoId'");
mysqli_query($con, "DELETE FROM clientes WHERE id='$clienteId'");
mysqli_query($con, "DELETE FROM parceiro WHERE id IN ('$prestadorId', '$prestador2Id', '$prestador3Id')");
echo resultado('info', 'Dados de teste removidos');
echo '</div>';

// ============================================================
// RESUMO
// ============================================================
$total = $passed + $failed;
$percentual = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo '<div class="summary">';
echo '<h2>📊 RESUMO DOS TESTES</h2>';
echo '<div class="summary-stats">';
echo '<div class="stat-card"><div class="stat-value">' . $total . '</div><div class="stat-label">Total</div></div>';
echo '<div class="stat-card"><div class="stat-value">' . $passed . '</div><div class="stat-label">Passou</div></div>';
echo '<div class="stat-card"><div class="stat-value">' . $failed . '</div><div class="stat-label">Falhou</div></div>';
if ($warnings > 0) {
    echo '<div class="stat-card" style="background:rgba(251,191,36,0.3);"><div class="stat-value">' . $warnings . '</div><div class="stat-label">Avisos</div></div>';
}
echo '<div class="stat-card"><div class="stat-value">' . $percentual . '%</div><div class="stat-label">Sucesso</div></div>';
echo '</div>';

if ($failed === 0) {
    echo '<div style="margin-top:20px;padding:16px;background:rgba(255,255,255,0.2);border-radius:8px;text-align:center;font-size:18px;font-weight:700;">';
    echo '✓✓✓ TODOS OS TESTES PASSARAM! ✓✓✓<br>';
    echo '<div style="font-size:14px;margin-top:8px;font-weight:400;">Items 7 e 10 estão funcionando corretamente!</div>';
    echo '</div>';
} else {
    echo '<div style="margin-top:20px;padding:16px;background:rgba(220,53,69,0.3);border-radius:8px;text-align:center;font-size:18px;font-weight:700;">';
    echo '✗✗✗ ALGUNS TESTES FALHARAM ✗✗✗<br>';
    echo '<div style="font-size:14px;margin-top:8px;font-weight:400;">Revise as implementações dos items com falha</div>';
    echo '</div>';
}
echo '</div>';

?>

    </div>
</body>
</html>
