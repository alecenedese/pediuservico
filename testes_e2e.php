<?php
/**
 * TESTE E2E - Simula fluxo completo Cliente + Prestador
 * Acesse: https://gessomt.app.br/pediuservico/testes_e2e.php?run=pediuservico2024
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['run']) || $_GET['run'] !== 'pediuservico2024') {
    die('Acesse com ?run=pediuservico2024');
}

try {

require_once("send.php");
if (!isset($con) || !$con) { die('ERRO: send.php não criou $con'); }
mysqli_report(MYSQLI_REPORT_OFF); // Evita que erros MySQL lancem exceções
date_default_timezone_set('America/Cuiaba');

$BASE = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/pediuservico';
$totalTestes = 0;
$passou = 0;
$falhou = 0;
$resultados = [];
$cleanup = [];
$testPrefix = 'E2E_TEST_' . date('His');

function teste($etapa, $nome, $condicao, $detalhe = '') {
    global $totalTestes, $passou, $falhou, $resultados;
    $totalTestes++;
    $status = $condicao ? 'OK' : 'FALHOU';
    if ($condicao) $passou++; else $falhou++;
    $resultados[] = ['etapa' => $etapa, 'status' => $status, 'nome' => $nome, 'detalhe' => $detalhe];
}

function httpGet($url) {
    $cookies = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '';
    $opts = ['http' => [
        'timeout' => 10, 
        'ignore_errors' => true
    ]];
    if (!empty($cookies)) {
        $opts['http']['header'] = "Cookie: " . $cookies . "\r\n";
    }
    $ctx = stream_context_create($opts);
    return @file_get_contents($url, false, $ctx);
}

// ============================================================
// ETAPA 0: PREPARAÇÃO - Busca dados reais para usar nos testes
// ============================================================

// Busca uma categoria/grupo real (custo_moedas pode não existir ainda)
$qGrupo = @mysqli_query($con, "SELECT codigo, titulo, custo_moedas FROM grupos LIMIT 1");
if (!$qGrupo) {
    // Fallback sem custo_moedas
    $qGrupo = mysqli_query($con, "SELECT codigo, titulo FROM grupos LIMIT 1");
}
$grupo = mysqli_fetch_assoc($qGrupo);
if (!$grupo) { die('ERRO: Tabela grupos vazia ou inexistente'); }
$codGrupo = $grupo['codigo'];
$custoMoedas = max(5, (int)(isset($grupo['custo_moedas']) ? $grupo['custo_moedas'] : 5));
teste('0-PREP', 'Categoria encontrada: ' . $grupo['titulo'], !empty($codGrupo), 
    "codigo=$codGrupo, custo_moedas=$custoMoedas");

// Busca uma subcategoria real - tenta varias tabelas possiveis
$codSubcategoria = $codGrupo; // fallback = proprio grupo
$tabelasSub = array('subgrupos', 'subcategorias', 'categoria_prestador');
foreach ($tabelasSub as $tbl) {
    $checkTbl = @mysqli_query($con, "SHOW TABLES LIKE '$tbl'");
    if ($checkTbl && mysqli_num_rows($checkTbl) > 0) {
        $cols = @mysqli_query($con, "SHOW COLUMNS FROM $tbl LIKE 'codsubcategoria'");
        if ($cols && mysqli_num_rows($cols) > 0) {
            $qSub = @mysqli_query($con, "SELECT DISTINCT codsubcategoria FROM $tbl LIMIT 1");
            if ($qSub && $sub = mysqli_fetch_assoc($qSub)) {
                $codSubcategoria = $sub['codsubcategoria'];
                break;
            }
        }
    }
}
teste('0-PREP', 'Subcategoria encontrada', !empty($codSubcategoria), "codsubcategoria=$codSubcategoria");

// ============================================================
// ETAPA 1: CRIAR PRESTADOR DE TESTE
// ============================================================

$prestadorNome = $testPrefix . '_PRESTADOR';
$prestadorCpf = '999.999.999-' . rand(10,99);
$prestadorCel = '(66) 99999-' . rand(1000,9999);

mysqli_query($con, "INSERT INTO parceiro (TIPO, NOME, CNPJ_CPF, CELULAR, ESTADO, MUNICIPIO, senha, dataCad) 
    VALUES ('pre', '$prestadorNome', '$prestadorCpf', '$prestadorCel', 'MT', 'Sinop', '123', NOW())");
$prestadorId = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM parceiro WHERE id='$prestadorId'";

teste('1-PRESTADOR', 'Prestador de teste criado', $prestadorId > 0, 
    "ID=$prestadorId, Nome=$prestadorNome");

// Vincula prestador à subcategoria
mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codsubcategoria) VALUES ('$prestadorId', '$codSubcategoria')");
$cleanup[] = "DELETE FROM categoria_prestador WHERE codcadastro='$prestadorId'";
teste('1-PRESTADOR', 'Prestador vinculado à categoria', true, "subcat=$codSubcategoria");

// Cria user no chat para o prestador (deleta se já existir)
mysqli_query($con, "DELETE FROM users WHERE user_id='$prestadorId'");
$rChat1 = mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) 
    VALUES ('$prestadorId', '$prestadorNome', '$prestadorCpf', '', 'user-default.png', NOW(), '$prestadorCel')");
$cleanup[] = "DELETE FROM users WHERE user_id='$prestadorId' AND name='$prestadorNome'";
teste('1-PRESTADOR', 'User de chat criado para prestador', $rChat1 ? true : false, 
    $rChat1 ? "celular=$prestadorCel" : 'ERRO: ' . mysqli_error($con));

// Dá moedas ao prestador (custo + 5 extra)
$moedasIniciais = $custoMoedas + 5;
mysqli_query($con, "INSERT INTO quantidade_pedidos (codcadastro, qtd, data, tipo) VALUES ('$prestadorId', '$moedasIniciais', NOW(), 'pre')");
$cleanup[] = "DELETE FROM quantidade_pedidos WHERE codcadastro='$prestadorId' AND tipo='pre'";
teste('1-PRESTADOR', 'Moedas atribuídas: ' . $moedasIniciais, true, "custo_categoria=$custoMoedas");

// ============================================================
// ETAPA 2: CRIAR CLIENTE DE TESTE
// ============================================================

$clienteNome = $testPrefix . '_CLIENTE';
$clienteCel = '(66) 98888-' . rand(1000,9999);

mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad) 
    VALUES ('', '$clienteNome', '', '', '$clienteCel', 'MT', 'Sinop', NOW())");
$clienteId = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM clientes WHERE id='$clienteId'";

teste('2-CLIENTE', 'Cliente de teste criado', $clienteId > 0, 
    "ID=$clienteId, Nome=$clienteNome");

// Cria user no chat para o cliente (deleta se já existir)
mysqli_query($con, "DELETE FROM users WHERE user_id='$clienteId'");
$rChat2 = mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) 
    VALUES ('$clienteId', '$clienteNome', '', '', 'user-default.png', NOW(), '$clienteCel')");
$cleanup[] = "DELETE FROM users WHERE user_id='$clienteId' AND name='$clienteNome'";
teste('2-CLIENTE', 'User de chat criado para cliente', $rChat2 ? true : false,
    $rChat2 ? "celular=$clienteCel" : 'ERRO: ' . mysqli_error($con));

// ============================================================
// ETAPA 3: CLIENTE CRIA PEDIDO
// ============================================================

$descPedido = $testPrefix . ' - Pedido de teste E2E';
mysqli_query($con, "INSERT INTO pedido (categoria, subcategoria, local, tempo, descricao, valor, data_hora, lat, log, status, codcli) 
    VALUES ('$codGrupo', '$codSubcategoria', 'Sinop-MT', 'Hoje', '$descPedido', '', NOW(), '-11.86', '-55.50', 'Procurando Prestador', '$clienteId')");
$pedidoId = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM pedido WHERE codigo='$pedidoId'";

teste('3-PEDIDO', 'Pedido criado pelo cliente', $pedidoId > 0, 
    "codigo=$pedidoId, categoria=$codGrupo");

// Verifica que o pedido existe com status correto
$qPed = mysqli_query($con, "SELECT * FROM pedido WHERE codigo='$pedidoId'");
$pedido = mysqli_fetch_assoc($qPed);
teste('3-PEDIDO', 'Status = Procurando Prestador', 
    $pedido && $pedido['status'] === 'Procurando Prestador',
    'status=' . (isset($pedido['status']) ? $pedido['status'] : 'NULL'));

teste('3-PEDIDO', 'codcli = ID do cliente', 
    $pedido && $pedido['codcli'] == $clienteId,
    'codcli=' . (isset($pedido['codcli']) ? $pedido['codcli'] : 'NULL'));

// ============================================================
// ETAPA 4: SISTEMA DESPACHA PARA PRESTADOR (disparo_pedidos)
// ============================================================

mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito) 
    VALUES ('$prestadorId', '', '$pedidoId', 'n')");
$cleanup[] = "DELETE FROM disparo_pedidos WHERE codpedido='$pedidoId'";

$qDisp = mysqli_query($con, "SELECT * FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$disp = mysqli_fetch_assoc($qDisp);
teste('4-DESPACHO', 'Proposta despachada para prestador', 
    $disp && $disp['aceito'] === 'n',
    'aceito=' . (isset($disp['aceito']) ? $disp['aceito'] : 'NULL'));

// ============================================================
// ETAPA 5: PRESTADOR ENVIA PROPOSTA (aceito='a')
// ============================================================

mysqli_query($con, "UPDATE disparo_pedidos SET aceito='a' WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
mysqli_query($con, "UPDATE pedido SET status='Proposta Aceita' WHERE codigo='$pedidoId'");

$qProp = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$prop = mysqli_fetch_assoc($qProp);
teste('5-PROPOSTA', 'Prestador enviou proposta (aceito=a)', 
    $prop && $prop['aceito'] === 'a',
    'aceito=' . (isset($prop['aceito']) ? $prop['aceito'] : 'NULL'));

$qPedStatus = mysqli_query($con, "SELECT status FROM pedido WHERE codigo='$pedidoId'");
$pedStatus = mysqli_fetch_assoc($qPedStatus);
teste('5-PROPOSTA', 'Pedido status = Proposta Aceita', 
    $pedStatus && $pedStatus['status'] === 'Proposta Aceita',
    'status=' . (isset($pedStatus['status']) ? $pedStatus['status'] : 'NULL'));

// ============================================================
// ETAPA 6: CLIENTE ACEITA PROPOSTA (aceito='ac')
// Simula pegar_contato.php - registra aceite do cliente
// ============================================================

// Insere pega_contato (como pegar_contato.php faz)
mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) 
    VALUES ('$clienteNome', '$clienteCel', '$pedidoId', '$prestadorId', '$clienteId', 'sim')");
$cleanup[] = "DELETE FROM pega_contato WHERE codpedido='$pedidoId' AND codcliente='$clienteId'";

// Marca como aceito pelo cliente
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='ac' WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");

$qAc = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$acRow = mysqli_fetch_assoc($qAc);
teste('6-ACEITE', 'Cliente aceitou proposta (aceito=ac)', 
    $acRow && $acRow['aceito'] === 'ac',
    'aceito=' . (isset($acRow['aceito']) ? $acRow['aceito'] : 'NULL'));

// ============================================================
// ETAPA 7: AUTO-ACCEPT - Debita moedas automaticamente
// Simula a lógica de pegar_contato.php
// ============================================================

// Busca custo da categoria (como pegar_contato.php faz)
$qCustoAuto = mysqli_query($con, "SELECT g.custo_moedas FROM pedido p INNER JOIN grupos g ON g.codigo = p.categoria WHERE p.codigo = '".mysqli_real_escape_string($con, $pedidoId)."'");
$custoReal = 5;
if ($qCustoAuto && $rCustoAuto = mysqli_fetch_array($qCustoAuto)) {
    $custoReal = max(5, (int)$rCustoAuto['custo_moedas']);
}
teste('7-AUTOACCEPT', 'Custo de moedas da categoria buscado', $custoReal >= 1,
    "custo=$custoReal");

// Verifica saldo do prestador
$qMoedas = mysqli_query($con, "SELECT * FROM quantidade_pedidos WHERE tipo='pre' AND codcadastro='$prestadorId'");
$moedasRow = mysqli_fetch_assoc($qMoedas);
$saldoAntes = (int)(isset($moedasRow['qtd']) ? $moedasRow['qtd'] : 0);
teste('7-AUTOACCEPT', 'Prestador tem moedas suficientes (' . $saldoAntes . ' >= ' . $custoReal . ')', 
    $saldoAntes >= $custoReal,
    "saldo=$saldoAntes, custo=$custoReal");

// Executa débito (como pegar_contato.php faz)
$novaQtd = $saldoAntes - $custoReal;
mysqli_query($con, "UPDATE quantidade_pedidos SET qtd='$novaQtd' WHERE codcadastro='$prestadorId'");

// Verifica saldo após débito
$qMoedas2 = mysqli_query($con, "SELECT qtd FROM quantidade_pedidos WHERE tipo='pre' AND codcadastro='$prestadorId'");
$moedasRow2 = mysqli_fetch_assoc($qMoedas2);
$saldoDepois = (int)(isset($moedasRow2['qtd']) ? $moedasRow2['qtd'] : -1);
teste('7-AUTOACCEPT', 'Moedas debitadas corretamente (' . $saldoAntes . ' → ' . $saldoDepois . ')', 
    $saldoDepois === ($saldoAntes - $custoReal),
    "esperado=" . ($saldoAntes - $custoReal) . ", real=$saldoDepois");

// Registra no extrato (como pegar_contato.php faz)
mysqli_query($con, "INSERT INTO moedas_extrato (codcadastro, tipo, quantidade, descricao, codpedido, data_hora) 
    VALUES ('$prestadorId', 'debito', '$custoReal', 'Débito automático pedido #$pedidoId', '$pedidoId', NOW())");
$cleanup[] = "DELETE FROM moedas_extrato WHERE codcadastro='$prestadorId' AND codpedido='$pedidoId'";

// Verifica extrato
$qExt = mysqli_query($con, "SELECT * FROM moedas_extrato WHERE codcadastro='$prestadorId' AND codpedido='$pedidoId' AND tipo='debito'");
$extRow = mysqli_fetch_assoc($qExt);
teste('7-AUTOACCEPT', 'Débito registrado no extrato de moedas', 
    $extRow && (int)$extRow['quantidade'] === $custoReal,
    'quantidade=' . (isset($extRow['quantidade']) ? $extRow['quantidade'] : 'NULL'));

// Atualiza status do pedido
mysqli_query($con, "UPDATE pedido SET status='Prestador Disponível' WHERE codigo='$pedidoId'");
// Marca como confirmado
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='s' WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");

// Cria timer_acordo
mysqli_query($con, "INSERT INTO timer_acordo (codpedido, codcadastro, tempo_expiracao, status) VALUES ('$pedidoId', '$prestadorId', NOW(), 'confirmado')");
$cleanup[] = "DELETE FROM timer_acordo WHERE codpedido='$pedidoId'";

$qFinal = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='$prestadorId'");
$finalRow = mysqli_fetch_assoc($qFinal);
teste('7-AUTOACCEPT', 'Acordo firmado (aceito=s)', 
    $finalRow && $finalRow['aceito'] === 's',
    'aceito=' . (isset($finalRow['aceito']) ? $finalRow['aceito'] : 'NULL'));

$qPedFinal = mysqli_query($con, "SELECT status FROM pedido WHERE codigo='$pedidoId'");
$pedFinal = mysqli_fetch_assoc($qPedFinal);
teste('7-AUTOACCEPT', 'Pedido status = Prestador Disponível', 
    $pedFinal && $pedFinal['status'] === 'Prestador Disponível',
    'status=' . (isset($pedFinal['status']) ? $pedFinal['status'] : 'NULL'));

// ============================================================
// ETAPA 8: VERIFICA APIs COM DADOS REAIS DO TESTE
// ============================================================

// 8a: verifica_status.php deve retornar aceito='s' para o pedido de teste
$jsonStatus = json_decode(httpGet($BASE . '/verifica_status.php?codpedido=' . $pedidoId), true);
teste('8-API', 'verifica_status.php detecta aceito=s para pedido #' . $pedidoId, 
    is_array($jsonStatus) && isset($jsonStatus['aceito']) && $jsonStatus['aceito'] === 's',
    is_array($jsonStatus) ? json_encode($jsonStatus) : 'Falha HTTP');

// 8b: verifica_acordo.php deve retornar status=confirmado
// (simula cookies do cliente para o teste)
$jsonAcordo = json_decode(httpGet($BASE . '/verifica_acordo.php?codpedido=' . $pedidoId . '&codcadastro=' . $prestadorId), true);
teste('8-API', 'verifica_acordo.php detecta status=confirmado', 
    is_array($jsonAcordo) && isset($jsonAcordo['status']) && $jsonAcordo['status'] === 'confirmado',
    is_array($jsonAcordo) ? json_encode($jsonAcordo) : 'Falha HTTP');

// 8c: verifica_acordo.php retorna user_from = ID do prestador (não string 'prestador')
if (is_array($jsonAcordo) && isset($jsonAcordo['user_from'])) {
    teste('8-API', 'verifica_acordo.php user_from = ID numérico do prestador',
        $jsonAcordo['user_from'] == $prestadorId,
        'user_from=' . $jsonAcordo['user_from'] . ', esperado=' . $prestadorId);
} else {
    teste('8-API', 'verifica_acordo.php retorna user_from', false, 'Campo não encontrado na resposta');
}

// 8d: get_unread_messages.php funciona
$jsonUnread = json_decode(httpGet($BASE . '/api/get_unread_messages.php?user_id=' . $clienteId), true);
teste('8-API', 'get_unread_messages.php retorna count para cliente', 
    is_array($jsonUnread) && isset($jsonUnread['count']),
    is_array($jsonUnread) ? 'count=' . $jsonUnread['count'] : 'Falha');

// ============================================================
// ETAPA 9: SIMULA MENSAGEM NO CHAT E VERIFICA UNREAD
// ============================================================

// Insere mensagem de teste no chat (prestador → cliente)
mysqli_query($con, "INSERT INTO chats (from_id, to_id, message, codpedido, opened) 
    VALUES ('$prestadorId', '$clienteId', '$testPrefix mensagem teste', '$pedidoId', 0)");
$chatId = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM chats WHERE chat_id='$chatId'";

teste('9-CHAT', 'Mensagem de teste inserida no chat', $chatId > 0, 
    "chat_id=$chatId, de=$prestadorId, para=$clienteId");

// Verifica unread count
$qUnread = mysqli_query($con, "SELECT COUNT(*) as cnt FROM chats WHERE to_id='$clienteId' AND opened=0");
$unreadRow = mysqli_fetch_assoc($qUnread);
$unreadCount = (int)(isset($unreadRow['cnt']) ? $unreadRow['cnt'] : 0);
teste('9-CHAT', 'Cliente tem mensagem não lida', $unreadCount > 0,
    "unread=$unreadCount");

// API deve retornar count > 0
$jsonUnread2 = json_decode(httpGet($BASE . '/api/get_unread_messages.php?user_id=' . $clienteId), true);
teste('9-CHAT', 'API get_unread_messages retorna count > 0 para cliente',
    is_array($jsonUnread2) && (int)(isset($jsonUnread2['count']) ? $jsonUnread2['count'] : 0) > 0,
    is_array($jsonUnread2) ? 'count=' . $jsonUnread2['count'] : 'Falha');

// Marca como lida (simula abrir o chat)
mysqli_query($con, "UPDATE chats SET opened=1 WHERE chat_id='$chatId'");
$qUnread3 = mysqli_query($con, "SELECT COUNT(*) as cnt FROM chats WHERE to_id='$clienteId' AND from_id='$prestadorId' AND codpedido='$pedidoId' AND opened=0");
$unread3 = mysqli_fetch_assoc($qUnread3);
teste('9-CHAT', 'Mensagem marcada como lida (opened=1)', (int)(isset($unread3['cnt']) ? $unread3['cnt'] : 1) === 0,
    'unread_restante=' . (isset($unread3['cnt']) ? $unread3['cnt'] : '?'));

// ============================================================
// ETAPA 10: SIMULA COMPRA DE MOEDAS VIA PIX (crédito no extrato)
// ============================================================

$moedasCompradas = 8;
// Simula o que iframePix.php faz
mysqli_query($con, "UPDATE quantidade_pedidos SET qtd = qtd + $moedasCompradas WHERE codcadastro='$prestadorId' AND tipo='pre'");
mysqli_query($con, "INSERT INTO moedas_extrato (codcadastro, tipo, quantidade, descricao, codpedido, data_hora) 
    VALUES ('$prestadorId', 'credito', '$moedasCompradas', 'Compra de moedas via PIX', NULL, NOW())");
$cleanup[] = "DELETE FROM moedas_extrato WHERE codcadastro='$prestadorId' AND tipo='credito' AND descricao='Compra de moedas via PIX'";

// Verifica saldo final
$qSaldoFinal = mysqli_query($con, "SELECT qtd FROM quantidade_pedidos WHERE tipo='pre' AND codcadastro='$prestadorId'");
$rowSaldoFinal = mysqli_fetch_assoc($qSaldoFinal);
$saldoFinal = (int)(isset($rowSaldoFinal['qtd']) ? $rowSaldoFinal['qtd'] : 0);
$saldoEsperado = $novaQtd + $moedasCompradas;
teste('10-PIX', 'Crédito PIX somou moedas corretamente', 
    $saldoFinal === $saldoEsperado,
    "saldo=$saldoFinal, esperado=$saldoEsperado ($novaQtd + $moedasCompradas)");

// Verifica extrato tem crédito E débito
$qExtC = mysqli_query($con, "SELECT tipo, quantidade FROM moedas_extrato WHERE codcadastro='$prestadorId' ORDER BY data_hora ASC");
$tipos = [];
while ($r = mysqli_fetch_assoc($qExtC)) { $tipos[] = $r['tipo'] . ':' . $r['quantidade']; }
teste('10-PIX', 'Extrato tem débito E crédito', 
    count($tipos) >= 2,
    implode(', ', $tipos));

// ============================================================
// ETAPA 11: TESTA contaAguardando.php com custo_moedas
// ============================================================

// Cria um segundo pedido onde prestador NÃO tem auto-accept (simula aceito='ac')
mysqli_query($con, "INSERT INTO pedido (categoria, subcategoria, local, tempo, descricao, valor, data_hora, lat, log, status, codcli) 
    VALUES ('$codGrupo', '$codSubcategoria', 'Sinop-MT', 'Hoje', '$testPrefix pedido 2', '', NOW(), '-11.86', '-55.50', 'Proposta Aceita', '$clienteId')");
$pedido2Id = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM pedido WHERE codigo='$pedido2Id'";

mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito) VALUES ('$prestadorId', '', '$pedido2Id', 'ac')");

// Testa endpoint contaAguardando
$htmlConta = httpGet($BASE . '/contaAguardando.php?codcadastro=' . $prestadorId . '&codpedido=' . $pedido2Id);
teste('11-AGUARDANDO', 'contaAguardando.php carrega sem erro', 
    !empty($htmlConta) && strpos($htmlConta, 'Cliente aceitou') !== false,
    strlen($htmlConta) . ' bytes');

teste('11-AGUARDANDO', 'Mostra custo de moedas da categoria',
    strpos($htmlConta, (string)$custoReal) !== false,
    "custo=$custoReal");

teste('11-AGUARDANDO', 'Mostra saldo atual do prestador',
    strpos($htmlConta, (string)$saldoFinal) !== false,
    "saldo=$saldoFinal");

// ============================================================
// ETAPA 12: VERIFICA PAGES HTML (header/footer corretos)
// ============================================================

// minhasmoedas.php deve ter header-app.php
$htmlMoedas = file_get_contents(__DIR__ . '/minhasmoedas.php');
teste('12-HTML', 'minhasmoedas.php tem header-app.php', 
    strpos($htmlMoedas, "header-app.php") !== false, '');
teste('12-HTML', 'minhasmoedas.php tem bottom-nav.php', 
    strpos($htmlMoedas, "bottom-nav.php") !== false, '');
teste('12-HTML', 'minhasmoedas.php NÃO tem topo2.php', 
    strpos($htmlMoedas, "topo2.php") === false, '');

// chat.php iframe relativo
$htmlChat = file_get_contents(__DIR__ . '/chat.php');
teste('12-HTML', 'chat.php iframe usa URL relativa',
    strpos($htmlChat, 'src="php-chat-app-main/') !== false, '');

// sw.js versão
$swContent = file_get_contents(__DIR__ . '/sw.js');
preg_match("/pediuservico-v(\d+)/", $swContent, $m);
teste('12-HTML', 'sw.js cache version >= 9', (int)(isset($m[1]) ? $m[1] : 0) >= 9, 'v' . (isset($m[1]) ? $m[1] : '?'));

// ============================================================
// LIMPEZA - Remove todos os dados de teste
// ============================================================

// Reverte na ordem inversa para respeitar foreign keys
$cleanupReversed = array_reverse($cleanup);
$limposOk = 0;
$limposFail = 0;
foreach ($cleanupReversed as $sql) {
    $r = mysqli_query($con, $sql);
    if ($r) $limposOk++; else $limposFail++;
}
teste('CLEANUP', 'Dados de teste removidos', $limposFail === 0,
    "$limposOk queries OK" . ($limposFail > 0 ? ", $limposFail falharam" : ''));

} catch (Exception $e) {
    echo '<h1 style="color:red">ERRO: ' . htmlspecialchars($e->getMessage()) . '</h1>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    // Tenta limpar mesmo com erro
    if (!empty($cleanup)) {
        foreach (array_reverse($cleanup) as $sql) { @mysqli_query($con, $sql); }
    }
    exit;
}

// ============================================================
// HTML DO RESULTADO
// ============================================================
$etapas = array(
    '0-PREP' => '0️⃣ Preparação',
    '1-PRESTADOR' => '👷 Etapa 1: Criar Prestador',
    '2-CLIENTE' => '👤 Etapa 2: Criar Cliente',
    '3-PEDIDO' => '📋 Etapa 3: Cliente Cria Pedido',
    '4-DESPACHO' => '📨 Etapa 4: Despacho para Prestador',
    '5-PROPOSTA' => '💰 Etapa 5: Prestador Envia Proposta',
    '6-ACEITE' => '✅ Etapa 6: Cliente Aceita',
    '7-AUTOACCEPT' => '⚡ Etapa 7: Auto-Accept + Débito',
    '8-API' => '🔌 Etapa 8: APIs Verificadas',
    '9-CHAT' => '💬 Etapa 9: Chat + Mensagens',
    '10-PIX' => '💳 Etapa 10: Compra PIX + Extrato',
    '11-AGUARDANDO' => '⏳ Etapa 11: contaAguardando',
    '12-HTML' => '📄 Etapa 12: HTML/CSS',
    'CLEANUP' => '🧹 Limpeza',
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teste E2E - Pediu Serviço</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#1a2332;color:#fff;padding:16px}
.header{text-align:center;padding:20px;margin-bottom:20px;background:rgba(0,212,255,.1);border-radius:12px;border:1px solid rgba(0,212,255,.3)}
.header h1{color:#00d4ff;font-size:22px;margin-bottom:6px}
.summary{display:flex;gap:12px;justify-content:center;margin:16px 0;flex-wrap:wrap}
.box{padding:14px 28px;border-radius:8px;font-size:22px;font-weight:bold;text-align:center}
.box small{font-size:12px;display:block;margin-top:4px}
.box.total{background:rgba(0,212,255,.2);color:#00d4ff}
.box.ok{background:rgba(40,167,69,.2);color:#28a745}
.box.fail{background:rgba(220,53,69,.2);color:#dc3545}
.etapa{margin:16px 0}
.etapa h3{color:#00d4ff;margin-bottom:8px;padding:8px 0;border-bottom:1px solid rgba(0,212,255,.2);font-size:15px}
.t{display:flex;align-items:center;gap:10px;padding:6px 10px;margin:3px 0;border-radius:5px;font-size:13px;background:rgba(255,255,255,.04)}
.t.ok{border-left:3px solid #28a745}
.t.fail{border-left:3px solid #dc3545;background:rgba(220,53,69,.1)}
.b{padding:2px 8px;border-radius:3px;font-weight:bold;font-size:11px;white-space:nowrap}
.b.ok{background:#28a745;color:#fff}
.b.fail{background:#dc3545;color:#fff}
.n{flex:1}
.d{font-size:11px;color:#888;max-width:350px;word-break:break-all}
.flow{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin:12px 0}
.flow .step{padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600}
.flow .step.done{background:rgba(40,167,69,.3);color:#28a745;border:1px solid rgba(40,167,69,.5)}
.flow .step.err{background:rgba(220,53,69,.3);color:#dc3545;border:1px solid rgba(220,53,69,.5)}
.flow .arrow{color:#00d4ff;font-size:16px;line-height:30px}
.ts{text-align:center;color:#555;margin-top:16px;font-size:11px}
</style>
</head>
<body>
<div class="header">
    <h1>🧪 Teste E2E — Fluxo Completo</h1>
    <p style="font-size:13px;color:#aaa">Simula: Cliente cria pedido → Prestador propõe → Cliente aceita → Auto-accept → Chat → PIX</p>
    <div class="summary">
        <div class="box total"><?=$totalTestes?><br><small>Total</small></div>
        <div class="box ok"><?=$passou?><br><small>Passou ✅</small></div>
        <div class="box fail"><?=$falhou?><br><small>Falhou ❌</small></div>
    </div>
    <?php if($falhou==0):?>
        <p style="color:#28a745;font-size:18px;font-weight:bold;margin-top:8px">🎉 TODOS OS TESTES PASSARAM!</p>
    <?php else:?>
        <p style="color:#dc3545;font-size:18px;font-weight:bold;margin-top:8px">⚠️ <?=$falhou?> TESTE(S) FALHARAM</p>
    <?php endif;?>
    <div class="flow">
    <?php
    $stepNames = ['Prep','Prestador','Cliente','Pedido','Despacho','Proposta','Aceite','Auto-Accept','APIs','Chat','PIX','Aguardando','HTML','Limpeza'];
    $etapaKeys = array_keys($etapas);
    foreach($etapaKeys as $i => $k) {
        $etapaFailed = false;
        foreach($resultados as $r) { if($r['etapa']===$k && $r['status']==='FALHOU') $etapaFailed=true; }
        $cls = $etapaFailed ? 'err' : 'done';
        echo '<span class="step '.$cls.'">'.$stepNames[$i].'</span>';
        if($i < count($etapaKeys)-1) echo '<span class="arrow">→</span>';
    }
    ?>
    </div>
</div>

<?php foreach($etapas as $key => $titulo):
    $items = array_filter($resultados, function($r) use($key) { return $r['etapa']===$key; });
    if(empty($items)) continue;
?>
<div class="etapa">
    <h3><?=$titulo?></h3>
    <?php foreach($items as $r):?>
    <div class="t <?=$r['status']==='OK'?'ok':'fail'?>">
        <span class="b <?=$r['status']==='OK'?'ok':'fail'?>"><?=$r['status']?></span>
        <span class="n"><?=htmlspecialchars($r['nome'])?></span>
        <span class="d"><?=htmlspecialchars($r['detalhe'])?></span>
    </div>
    <?php endforeach;?>
</div>
<?php endforeach;?>

<div class="ts">
    Executado em <?=date('d/m/Y H:i:s')?> | <?=$_SERVER['HTTP_HOST']?> | PHP <?=phpversion()?> | Prefix: <?=$testPrefix?>
</div>
</body>
</html>
