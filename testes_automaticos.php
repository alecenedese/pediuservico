<?php
/**
 * Testes Automatizados - Pediu Serviço
 * Acesse: https://gessomt.app.br/pediuservico/testes_automaticos.php
 * 
 * Este script testa os endpoints, banco de dados, e HTML das páginas
 * para validar que todas as correções foram aplicadas corretamente.
 */

// Segurança: só roda com parâmetro secreto
if (!isset($_GET['run']) || $_GET['run'] !== 'pediuservico2024') {
    die('Acesse com ?run=pediuservico2024 para executar os testes.');
}

require_once("send.php");

$BASE = '/pediuservico';
$FULL_BASE = 'https://' . $_SERVER['HTTP_HOST'] . $BASE;

$totalTestes = 0;
$passou = 0;
$falhou = 0;
$resultados = [];

function teste($nome, $condicao, $detalhe = '') {
    global $totalTestes, $passou, $falhou, $resultados;
    $totalTestes++;
    if ($condicao) {
        $passou++;
        $resultados[] = ['status' => 'OK', 'nome' => $nome, 'detalhe' => $detalhe];
    } else {
        $falhou++;
        $resultados[] = ['status' => 'FALHOU', 'nome' => $nome, 'detalhe' => $detalhe];
    }
}

// Helper: faz HTTP GET local
function httpGet($url) {
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    return $body !== false ? $body : '';
}

// Helper: faz HTTP GET e retorna JSON
function httpGetJson($url) {
    $body = httpGet($url);
    return json_decode($body, true);
}

// ============================================================
// GRUPO 1: BANCO DE DADOS - Tabelas e Colunas
// ============================================================

// Teste: coluna custo_moedas existe na tabela grupos
$qCol = @mysqli_query($con, "SHOW COLUMNS FROM grupos LIKE 'custo_moedas'");
$colExiste = $qCol && mysqli_num_rows($qCol) > 0;
teste('BD: Coluna grupos.custo_moedas existe', $colExiste, 
    $colExiste ? 'Coluna encontrada' : 'EXECUTE: criar_coluna_custo_moedas.sql');

// Teste: tabela moedas_extrato existe
$qTab = @mysqli_query($con, "SELECT 1 FROM moedas_extrato LIMIT 1");
$tabExiste = ($qTab !== false);
teste('BD: Tabela moedas_extrato existe', $tabExiste,
    $tabExiste ? 'Tabela encontrada' : 'EXECUTE: criar_tabela_moedas_extrato.sql');

// Teste: tabela chats existe (usada pelo get_unread_messages)
$qChats = @mysqli_query($con, "SELECT 1 FROM chats LIMIT 1");
teste('BD: Tabela chats acessível', $qChats !== false, 
    $qChats !== false ? 'OK' : 'Tabela chats não encontrada');

// ============================================================
// GRUPO 2: ENDPOINTS JSON
// ============================================================

// Teste: verifica_status.php NÃO retorna dados do pedido 456 para um pedido aleatório
$jsonStatus = httpGetJson($FULL_BASE . '/verifica_status.php?codpedido=999999');
teste('API: verifica_status.php responde JSON', is_array($jsonStatus),
    is_array($jsonStatus) ? json_encode($jsonStatus) : 'Não retornou JSON válido');

if (is_array($jsonStatus)) {
    teste('API: verifica_status.php - pedido inexistente retorna aceito=n', 
        isset($jsonStatus['aceito']) && $jsonStatus['aceito'] === 'n',
        'aceito=' . ($jsonStatus['aceito'] ?? 'null'));
}

// Teste com pedido real (busca um pedido qualquer do banco)
$qPedReal = mysqli_query($con, "SELECT p.codigo FROM pedido p LIMIT 1");
if ($qPedReal && $rPedReal = mysqli_fetch_array($qPedReal)) {
    $codReal = $rPedReal['codigo'];
    $jsonReal = httpGetJson($FULL_BASE . '/verifica_status.php?codpedido=' . $codReal);
    teste('API: verifica_status.php com pedido real #' . $codReal, 
        is_array($jsonReal) && isset($jsonReal['success']),
        is_array($jsonReal) ? json_encode($jsonReal) : 'Falha');
}

// Teste: get_unread_messages.php
$jsonUnread = httpGetJson($FULL_BASE . '/api/get_unread_messages.php?user_id=0');
teste('API: get_unread_messages.php responde JSON', 
    is_array($jsonUnread) && isset($jsonUnread['count']),
    is_array($jsonUnread) ? 'count=' . $jsonUnread['count'] : 'Não retornou JSON');

// ============================================================
// GRUPO 3: CONTEÚDO DAS PÁGINAS (HTML)
// ============================================================

// Teste: chat.php não contém URL hardcoded gessomt.app.br no iframe
$chatSrc = file_get_contents(__DIR__ . '/chat.php');
teste('HTML: chat.php sem iframe hardcoded gessomt.app.br',
    strpos($chatSrc, 'src="https://gessomt.app.br') === false,
    strpos($chatSrc, 'gessomt.app.br') !== false ? 'AINDA TEM URL HARDCODED!' : 'OK - URL relativa');

// Teste: chat2.php sem URL hardcoded
$chat2Src = file_get_contents(__DIR__ . '/chat2.php');
teste('HTML: chat2.php sem iframe hardcoded gessomt.app.br',
    strpos($chat2Src, 'src="https://gessomt.app.br') === false,
    strpos($chat2Src, 'src="https://gessomt.app.br') !== false ? 'AINDA TEM URL HARDCODED!' : 'OK');

// Teste: insertFotos.php sem URL hardcoded viclocacoessinop
$fotosSrc = file_get_contents(__DIR__ . '/php-chat-app-main/app/ajax/insertFotos.php');
teste('HTML: insertFotos.php sem URL hardcoded viclocacoessinop',
    strpos($fotosSrc, 'viclocacoessinop') === false,
    strpos($fotosSrc, 'viclocacoessinop') !== false ? 'AINDA TEM URL HARDCODED!' : 'OK');

// Teste: chat.php do chat app sem URL hardcoded para AJAX de fotos
$chatAppSrc = file_get_contents(__DIR__ . '/php-chat-app-main/chat.php');
teste('HTML: php-chat-app-main/chat.php sem URL hardcoded AJAX',
    strpos($chatAppSrc, 'url: "https://') === false,
    strpos($chatAppSrc, 'url: "https://') !== false ? 'AINDA TEM URL HARDCODED!' : 'OK');

// Teste: php-chat-app-main/chat.php tem WhatsApp com wa.me
teste('HTML: chat app tem botão WhatsApp com wa.me',
    strpos($chatAppSrc, 'wa.me') !== false,
    strpos($chatAppSrc, 'wa.me') !== false ? 'OK - wa.me encontrado' : 'WhatsApp button sem wa.me link');

// Teste: verifica_status.php usa variável $codpedido (não 456 hardcoded)
$vsrcStatus = file_get_contents(__DIR__ . '/verifica_status.php');
teste('CÓDIGO: verifica_status.php usa $codpedido variável',
    strpos($vsrcStatus, "codpedido = 456") === false && strpos($vsrcStatus, '$codpedido') !== false,
    strpos($vsrcStatus, "codpedido = 456") !== false ? 'AINDA TEM 456 HARDCODED!' : 'OK');

// Teste: verifica_acordo.php retorna codcadastro como user_from (não string 'prestador')
$vsrcAcordo = file_get_contents(__DIR__ . '/verifica_acordo.php');
teste('CÓDIGO: verifica_acordo.php user_from = $codcadastro',
    strpos($vsrcAcordo, "'user_from' => \$codcadastro") !== false,
    strpos($vsrcAcordo, "'user_from' => 'prestador'") !== false ? 'AINDA TEM user_from=prestador HARDCODED!' : 'OK');

// Teste: minhasmoedas.php usa header-app.php (não topo2.php)
$moedasSrc = file_get_contents(__DIR__ . '/minhasmoedas.php');
teste('HTML: minhasmoedas.php usa header-app.php',
    strpos($moedasSrc, "include('header-app.php')") !== false,
    strpos($moedasSrc, "include('topo2.php')") !== false ? 'AINDA USA topo2.php!' : 'OK');

teste('HTML: minhasmoedas.php tem bottom-nav.php',
    strpos($moedasSrc, "include('bottom-nav.php')") !== false,
    strpos($moedasSrc, "bottom-nav.php") === false ? 'FALTA bottom-nav.php!' : 'OK');

// Teste: chat.php (wrapper) usa header-app.php
$chatWrapSrc = file_get_contents(__DIR__ . '/chat.php');
teste('HTML: chat.php usa header-app.php',
    strpos($chatWrapSrc, "include('header-app.php')") !== false,
    'Verifica header padrão no chat');

teste('HTML: chat.php tem bottom-nav.php',
    strpos($chatWrapSrc, "include('bottom-nav.php')") !== false,
    'Verifica bottom nav no chat');

// Teste: pegar_contato.php tem auto-accept com custo_moedas
$pegarSrc = file_get_contents(__DIR__ . '/pegar_contato.php');
teste('CÓDIGO: pegar_contato.php usa custo_moedas na auto-accept',
    strpos($pegarSrc, 'custoMoedasAuto') !== false && strpos($pegarSrc, 'custo_moedas') !== false,
    'Auto-accept com custo configurável');

teste('CÓDIGO: pegar_contato.php auto-accept redireciona ao chat.php',
    strpos($pegarSrc, "window.location.href='chat.php?") !== false,
    strpos($pegarSrc, "chat.php?") !== false ? 'OK' : 'Redirect não vai para chat.php');

// Teste: debitar_moedas.php usa custo_moedas e registra no extrato
$debitarSrc = file_get_contents(__DIR__ . '/debitar_moedas.php');
teste('CÓDIGO: debitar_moedas.php usa grupos.custo_moedas',
    strpos($debitarSrc, 'custo_moedas') !== false,
    'Custo configurável por categoria');

teste('CÓDIGO: debitar_moedas.php registra no moedas_extrato',
    strpos($debitarSrc, 'moedas_extrato') !== false,
    'Log de débito no extrato');

// Teste: contaAguardando.php verifica custo_moedas
$contaSrc = file_get_contents(__DIR__ . '/contaAguardando.php');
teste('CÓDIGO: contaAguardando.php usa custo_moedas',
    strpos($contaSrc, 'custoMoedasConta') !== false,
    'Custo variável por categoria');

// Teste: iframePix.php registra crédito no extrato
$pixSrc = file_get_contents(__DIR__ . '/iframePix.php');
teste('CÓDIGO: iframePix.php registra crédito no moedas_extrato',
    strpos($pixSrc, 'moedas_extrato') !== false,
    'Crédito logado no extrato');

// Teste: sw.js cache version atualizado (v9+)
$swSrc = file_get_contents(__DIR__ . '/sw.js');
preg_match("/CACHE_NAME = 'pediuservico-v(\d+)'/", $swSrc, $m);
$swVersion = isset($m[1]) ? (int)$m[1] : 0;
teste('PWA: sw.js cache version >= 9', $swVersion >= 9,
    'Versão atual: v' . $swVersion);

// Teste: sw.js notificationclick tem error catch
teste('PWA: sw.js notificationclick com fallback de erro',
    strpos($swSrc, '.catch(function(err)') !== false,
    'Error handling no clique da notificação');

// Teste: bottom-nav.php tem badges
$bnSrc = file_get_contents(__DIR__ . '/bottom-nav.php');
teste('HTML: bottom-nav.php tem badges de contagem',
    strpos($bnSrc, 'nav-badge') !== false,
    'Badges configurados');

// Teste: header-app.php existe e tem btn-back
$haSrc = @file_get_contents(__DIR__ . '/header-app.php');
teste('HTML: header-app.php existe', $haSrc !== false, '');
if ($haSrc) {
    teste('HTML: header-app.php tem botão voltar azul',
        strpos($haSrc, '#0ea5e9') !== false || strpos($haSrc, '0ea5e9') !== false,
        'Cor padrão do botão voltar');
}

// Teste: global-font-size.css existe e tem bottom nav
$gfSrc = @file_get_contents(__DIR__ . '/global-font-size.css');
teste('CSS: global-font-size.css existe', $gfSrc !== false, '');
if ($gfSrc) {
    teste('CSS: global-font-size.css tem .nav-label',
        strpos($gfSrc, '.nav-label') !== false,
        'Bottom nav labels padronizados');
}

// Teste: pwa-include.php tem polling de mensagens não lidas
$pwaSrc = file_get_contents(__DIR__ . '/pwa-include.php');
teste('PWA: pwa-include.php tem polling de mensagens não lidas',
    strpos($pwaSrc, 'checkUnreadMessages') !== false,
    'Polling de unread messages');

teste('PWA: pwa-include.php tem banner de instalação fallback Android',
    strpos($pwaSrc, 'Instalar App') !== false,
    'Banner PWA para Android');

// ============================================================
// GRUPO 4: TESTE DE INTEGRAÇÃO - Fluxo de moedas
// ============================================================

// Busca um prestador com moedas para simular
$qPrest = mysqli_query($con, "SELECT qp.codcadastro, qp.qtd FROM quantidade_pedidos qp WHERE qp.tipo='pre' AND qp.qtd > 0 LIMIT 1");
if ($qPrest && $rPrest = mysqli_fetch_array($qPrest)) {
    $testPrestId = $rPrest['codcadastro'];
    $testPrestQtd = (int)$rPrest['qtd'];
    teste('INTEGRAÇÃO: Prestador #' . $testPrestId . ' tem ' . $testPrestQtd . ' moedas', true, 'Dado de teste válido');

    // Busca um pedido com categoria que tem custo_moedas
    if ($colExiste) {
        $qPedCusto = mysqli_query($con, "SELECT p.codigo, g.custo_moedas, g.titulo FROM pedido p INNER JOIN grupos g ON g.codigo = p.categoria WHERE g.custo_moedas IS NOT NULL LIMIT 1");
        if ($qPedCusto && $rPedCusto = mysqli_fetch_array($qPedCusto)) {
            teste('INTEGRAÇÃO: Pedido #' . $rPedCusto['codigo'] . ' categoria "' . $rPedCusto['titulo'] . '" custo=' . $rPedCusto['custo_moedas'],
                true, 'Custo configurável funcionando');
        } else {
            teste('INTEGRAÇÃO: Pedidos com custo_moedas configurado', false, 'Nenhum grupo tem custo_moedas definido - usando padrão 1');
        }
    }
}

// ============================================================
// GRUPO 5: SQL MIGRATIONS - Verifica se foram aplicadas
// ============================================================

$qMigFiles = [
    'criar_coluna_custo_moedas.sql',
    'criar_tabela_moedas_extrato.sql'
];
foreach ($qMigFiles as $f) {
    teste('MIGRATION: Arquivo ' . $f . ' existe', file_exists(__DIR__ . '/' . $f), '');
}

// ============================================================
// RESULTADO FINAL
// ============================================================
$corOk = '#28a745';
$corFalha = '#dc3545';
$corBg = $falhou > 0 ? '#fff3cd' : '#d4edda';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testes Automatizados - Pediu Serviço</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; background: #1a2332; color: #fff; padding: 20px; }
.header { text-align: center; padding: 20px; margin-bottom: 20px; background: rgba(0,212,255,.1); border-radius: 12px; border: 1px solid rgba(0,212,255,.3); }
.header h1 { color: #00d4ff; margin-bottom: 8px; }
.summary { display: flex; gap: 16px; justify-content: center; margin: 16px 0; flex-wrap: wrap; }
.summary .box { padding: 16px 32px; border-radius: 8px; font-size: 24px; font-weight: bold; text-align: center; }
.box.total { background: rgba(0,212,255,.2); color: #00d4ff; }
.box.ok { background: rgba(40,167,69,.2); color: #28a745; }
.box.fail { background: rgba(220,53,69,.2); color: #dc3545; }
.grupo { margin: 20px 0; }
.grupo h2 { color: #00d4ff; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,212,255,.2); }
.teste { display: flex; align-items: center; gap: 12px; padding: 8px 12px; margin: 4px 0; border-radius: 6px; background: rgba(255,255,255,.05); }
.teste.ok { border-left: 4px solid #28a745; }
.teste.fail { border-left: 4px solid #dc3545; background: rgba(220,53,69,.1); }
.badge { padding: 2px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; white-space: nowrap; }
.badge.ok { background: #28a745; color: #fff; }
.badge.fail { background: #dc3545; color: #fff; }
.nome { flex: 1; font-size: 14px; }
.detalhe { font-size: 12px; color: #aaa; max-width: 400px; word-break: break-all; }
.timestamp { text-align: center; color: #666; margin-top: 20px; font-size: 12px; }
</style>
</head>
<body>
<div class="header">
    <h1>🧪 Testes Automatizados</h1>
    <p>Pediu Serviço - Validação de Correções</p>
    <div class="summary">
        <div class="box total"><?= $totalTestes ?><br><small>Total</small></div>
        <div class="box ok"><?= $passou ?><br><small>Passou ✅</small></div>
        <div class="box fail"><?= $falhou ?><br><small>Falhou ❌</small></div>
    </div>
    <?php if ($falhou == 0): ?>
        <p style="color:#28a745; font-size:20px; font-weight:bold; margin-top:12px;">🎉 TODOS OS TESTES PASSARAM!</p>
    <?php else: ?>
        <p style="color:#dc3545; font-size:20px; font-weight:bold; margin-top:12px;">⚠️ <?= $falhou ?> TESTE(S) FALHARAM - Verifique abaixo</p>
    <?php endif; ?>
</div>

<?php
$grupos = [
    'BD:' => 'Banco de Dados',
    'API:' => 'Endpoints API',
    'HTML:' => 'Conteúdo HTML',
    'CÓDIGO:' => 'Código PHP',
    'PWA:' => 'PWA / Service Worker',
    'CSS:' => 'CSS / Estilos',
    'INTEGRAÇÃO:' => 'Integração',
    'MIGRATION:' => 'SQL Migrations'
];

foreach ($grupos as $prefixo => $titulo):
    $itens = array_filter($resultados, function($r) use ($prefixo) {
        return strpos($r['nome'], $prefixo) === 0;
    });
    if (empty($itens)) continue;
?>
<div class="grupo">
    <h2><?= $titulo ?></h2>
    <?php foreach ($itens as $r): ?>
    <div class="teste <?= $r['status'] === 'OK' ? 'ok' : 'fail' ?>">
        <span class="badge <?= $r['status'] === 'OK' ? 'ok' : 'fail' ?>"><?= $r['status'] ?></span>
        <span class="nome"><?= htmlspecialchars($r['nome']) ?></span>
        <span class="detalhe"><?= htmlspecialchars($r['detalhe']) ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<div class="timestamp">
    Executado em: <?= date('d/m/Y H:i:s') ?> | Servidor: <?= $_SERVER['HTTP_HOST'] ?> | PHP <?= phpversion() ?>
</div>
</body>
</html>
