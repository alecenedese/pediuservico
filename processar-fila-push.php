<?php
// processar-fila-push.php
// Worker que processa a fila de push notifications (push_fila).
// Deve ser chamado por CRON a cada 1 minuto.
// Também pode ser aberto no navegador para testar: processar-fila-push.php?debug=1

@set_time_limit(60);
@ignore_user_abort(true);

require_once(__DIR__ . '/send.php');
require_once(__DIR__ . '/api/push-send.php');

$debug = isset($_GET['debug']);
if ($debug) { header('Content-Type: text/plain; charset=utf-8'); }

$logFile = __DIR__ . '/log_push.txt';

// TRAVA: garante que só 1 worker rode por vez (evita push duplicado e sobrecarga)
$lockFile = __DIR__ . '/.push_fila.lock';
$lockHandle = @fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    if ($debug) { echo "Outro worker já está em execução. Saindo.\n"; }
    exit;
}

function logFila($msg, $debug, $logFile) {
    @file_put_contents($logFile, "[FILA] ".date('Y-m-d H:i:s')." $msg\n", FILE_APPEND);
    if ($debug) echo date('H:i:s')." $msg\n";
}

// Garante a tabela
garantirTabelaFila($con);

$inicio = time();
$LIMITE_TEMPO = 50;       // segundos (deixa folga para o cron de 1 min)
$LOTE_FILA = 500;         // quantas linhas da fila processar por rodada
$totalEnviadas = 0;
$totalRodadas = 0;

logFila("Worker iniciado", $debug, $logFile);

while ((time() - $inicio) < $LIMITE_TEMPO) {

    // 1) Reivindica um bloco de pendentes (evita 2 workers pegarem as mesmas linhas)
    $ids = [];
    $q = mysqli_query($con, "SELECT id FROM push_fila WHERE status='pendente' ORDER BY id ASC LIMIT $LOTE_FILA");
    if ($q) { while ($r = mysqli_fetch_assoc($q)) { $ids[] = (int)$r['id']; } }

    if (empty($ids)) {
        logFila("Sem pendentes. Encerrando.", $debug, $logFile);
        break;
    }

    $inIds = implode(',', $ids);
    mysqli_query($con, "UPDATE push_fila SET status='processando', tentativas=tentativas+1 WHERE id IN ($inIds) AND status='pendente'");

    // 2) Lê as linhas reivindicadas por este worker
    $rows = [];
    $userIds = [];
    $qRows = mysqli_query($con, "SELECT id, user_id, title, body, url FROM push_fila WHERE id IN ($inIds) AND status='processando'");
    if ($qRows) {
        while ($r = mysqli_fetch_assoc($qRows)) {
            $rows[] = $r;
            $userIds[] = $r['user_id'];
        }
    }

    if (empty($rows)) { continue; }

    // 3) Busca todos os tokens de uma vez
    $tokensMap = buscarTokensUsuarios($con, $userIds);

    // 4) Monta as mensagens (uma por token de cada destinatário)
    $messages = [];
    $idsProcessados = [];
    foreach ($rows as $row) {
        $idsProcessados[] = (int)$row['id'];
        $toks = $tokensMap[$row['user_id']] ?? [];
        foreach ($toks as $tk) {
            $messages[] = [
                'to' => $tk,
                'title' => $row['title'],
                'body' => $row['body'],
                'sound' => 'default',
                'priority' => 'high',
                'channelId' => 'pedidos_som_v1',
                'data' => ['url' => $row['url']]
            ];
        }
    }

    // 5) Envia em lotes de 100 (dentro de enviarExpoPushLote)
    if (!empty($messages)) {
        $res = enviarExpoPushLote($con, $messages);
        $totalEnviadas += count($messages);
        logFila("Rodada: ".count($rows)." linhas, ".count($messages)." mensagens, ".($res['lotes'] ?? 0)." lote(s)", $debug, $logFile);
    } else {
        logFila("Rodada: ".count($rows)." linhas sem token nativo", $debug, $logFile);
    }

    // 6) Marca como enviado (processado), mesmo as sem token (para não reprocessar)
    if (!empty($idsProcessados)) {
        $inProc = implode(',', $idsProcessados);
        mysqli_query($con, "UPDATE push_fila SET status='enviado', sent_at=NOW() WHERE id IN ($inProc)");
    }

    $totalRodadas++;
}

// Reabilita linhas que ficaram presas em 'processando' há mais de 5 minutos (worker que morreu)
mysqli_query($con, "UPDATE push_fila SET status='pendente' WHERE status='processando' AND created_at < (NOW() - INTERVAL 5 MINUTE) AND tentativas < 5");

// Limpa enviados com mais de 2 dias (evita a tabela crescer infinitamente)
mysqli_query($con, "DELETE FROM push_fila WHERE status='enviado' AND sent_at < (NOW() - INTERVAL 2 DAY)");

logFila("Worker finalizado. Rodadas=$totalRodadas Mensagens=$totalEnviadas", $debug, $logFile);

if ($debug) {
    echo "\nConcluído. Rodadas: $totalRodadas | Mensagens enviadas: $totalEnviadas\n";
}
