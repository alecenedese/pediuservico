<?php
// Envia push notification usando minishlink/web-push
// Requer: composer require minishlink/web-push

require_once(__DIR__ . '/../send.php');

// Carrega chaves VAPID
$vapidKeysFile = __DIR__ . '/vapid-keys.php';
if (file_exists($vapidKeysFile)) {
    require_once($vapidKeysFile);
} else {
    $VAPID_PUBLIC_KEY = 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U';
    $VAPID_PRIVATE_KEY = 'UUxI4O8-FbRouAf7-1Qq07qK-_2ySMYHAqGzuWhlqJc';
    $VAPID_SUBJECT = 'mailto:contato@pediuservico.com.br';
}

// Verifica se a biblioteca está instalada
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$webPushDisponivel = file_exists($autoloadPath);
if ($webPushDisponivel) {
    require_once($autoloadPath);
}

/**
 * Envia push notification NATIVA via Expo Push API
 * Usa os tokens salvos na tabela push_tokens (app Android/iOS instalado)
 */
function enviarExpoPush($con, $userId, $title, $body, $url = '/index.php', $extraData = []) {
    $logFile = __DIR__ . '/../log_push.txt';
    $userIdEsc = mysqli_real_escape_string($con, $userId);

    // Detecta as colunas existentes na tabela push_tokens (schema pode variar entre ambientes)
    $cols = [];
    try {
        $rc = mysqli_query($con, "SHOW COLUMNS FROM push_tokens");
        if ($rc) {
            while ($c = mysqli_fetch_assoc($rc)) { $cols[] = $c['Field']; }
        }
    } catch (\Throwable $e) {
        // Tabela não existe ainda
        return ['success' => false, 'error' => 'Tabela push_tokens não existe'];
    }

    if (!in_array('token', $cols) || !in_array('user_id', $cols)) {
        // Tabela existe mas com schema incompatível — não quebra o fluxo
        @file_put_contents($logFile, "\n[EXPO] push_tokens com schema incompatível (colunas: ".implode(',', $cols).")\n", FILE_APPEND);
        return ['success' => false, 'error' => 'Tabela push_tokens com schema incompatível'];
    }

    // Filtro de ativo só se a coluna existir
    $whereAtivo = in_array('ativo', $cols) ? " AND ativo=1" : "";

    // Buscar tokens nativos deste usuário
    $q = mysqli_query($con, "SELECT token FROM push_tokens WHERE user_id='$userIdEsc'".$whereAtivo);
    if (!$q || mysqli_num_rows($q) == 0) {
        return ['success' => false, 'error' => 'Sem token nativo (Expo) para este usuário'];
    }

    $messages = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $token = trim($row['token']);
        // Validar formato do token Expo
        if (strpos($token, 'ExponentPushToken') === false && strpos($token, 'ExpoPushToken') === false) {
            continue;
        }
        $messages[] = [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'priority' => 'high',
            'channelId' => 'pedidos_som_v1',
            'data' => array_merge([
                'url' => $url
            ], $extraData)
        ];
    }

    if (empty($messages)) {
        return ['success' => false, 'error' => 'Nenhum token Expo válido encontrado'];
    }

    // Enviar para a Expo Push API
    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    file_put_contents($logFile, "\n=== EXPO PUSH: " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
    file_put_contents($logFile, "User: $userId | Tokens: " . count($messages) . "\n", FILE_APPEND);
    file_put_contents($logFile, "Title: $title | Body: $body\n", FILE_APPEND);

    if ($curlErr) {
        file_put_contents($logFile, "Resultado: ERRO CURL - $curlErr\n", FILE_APPEND);
        return ['success' => false, 'error' => 'Erro cURL: ' . $curlErr];
    }

    $respData = json_decode($response, true);

    // Processar resposta e remover tokens inválidos (DeviceNotRegistered)
    if (isset($respData['data']) && is_array($respData['data'])) {
        foreach ($respData['data'] as $idx => $ticket) {
            if (isset($ticket['status']) && $ticket['status'] === 'error') {
                $errType = $ticket['details']['error'] ?? '';
                file_put_contents($logFile, "Ticket erro: " . json_encode($ticket) . "\n", FILE_APPEND);
                // Token expirado/desinstalado: desativar
                if ($errType === 'DeviceNotRegistered' && isset($messages[$idx]['to'])) {
                    $tokenInvalido = mysqli_real_escape_string($con, $messages[$idx]['to']);
                    mysqli_query($con, "UPDATE push_tokens SET ativo=0 WHERE token='$tokenInvalido'");
                    file_put_contents($logFile, "Token desativado: $tokenInvalido\n", FILE_APPEND);
                }
            }
        }
    }

    if ($httpCode == 200) {
        file_put_contents($logFile, "Resultado: SUCESSO (HTTP 200)\n", FILE_APPEND);
        return ['success' => true, 'message' => 'Push nativo enviado', 'sent' => count($messages), 'response' => $respData];
    } else {
        file_put_contents($logFile, "Resultado: FALHA HTTP $httpCode - $response\n", FILE_APPEND);
        return ['success' => false, 'error' => "HTTP $httpCode", 'response' => $respData];
    }
}

/**
 * Dispara o worker da fila SEM esperar a resposta (fire-and-forget).
 * Garante entrega quase instantânea; o lock no worker evita execuções paralelas.
 */
function dispararWorkerFila() {
    $url = 'https://gessomt.app.br/pediuservico/processar-fila-push.php';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT_MS => 250,          // não espera o worker terminar
        CURLOPT_CONNECTTIMEOUT_MS => 250,
        CURLOPT_NOSIGNAL => 1,
    ]);
    @curl_exec($ch);
    @curl_close($ch);
}

/**
 * Garante que a tabela da fila de push exista.
 */
function garantirTabelaFila($con) {
    static $ok = false;
    if ($ok) return;
    @mysqli_query($con, "CREATE TABLE IF NOT EXISTS push_fila (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        url VARCHAR(255) DEFAULT '/meus-orcamentos.php',
        status VARCHAR(20) DEFAULT 'pendente',
        tentativas INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        INDEX idx_status (status),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $ok = true;
}

/**
 * Enfileira notificações para vários usuários (mesma mensagem).
 * NÃO envia na hora — apenas grava na fila (rápido). O worker (cron) envia depois.
 */
function enfileirarPush($con, $userIds, $title, $body, $url = '/meus-orcamentos.php') {
    $userIds = array_values(array_unique(array_filter(array_map('intval', (array)$userIds))));
    if (empty($userIds)) return ['success' => false, 'error' => 'sem destinatarios'];
    garantirTabelaFila($con);
    $t = mysqli_real_escape_string($con, $title);
    $b = mysqli_real_escape_string($con, $body);
    $u = mysqli_real_escape_string($con, $url);
    $values = [];
    foreach ($userIds as $uid) {
        $values[] = "($uid, '$t', '$b', '$u', 'pendente', NOW())";
    }
    foreach (array_chunk($values, 500) as $bloco) {
        @mysqli_query($con, "INSERT INTO push_fila (user_id, title, body, url, status, created_at) VALUES ".implode(',', $bloco));
    }
    return ['success' => true, 'enfileirados' => count($userIds)];
}

/**
 * Busca tokens nativos de VÁRIOS usuários de uma vez (1 query só).
 * Retorna mapa [user_id => [token1, token2...]]
 */
function buscarTokensUsuarios($con, $userIds) {
    $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
    if (empty($userIds)) return [];

    // Detecta colunas (schema pode variar)
    $cols = [];
    try {
        $rc = mysqli_query($con, "SHOW COLUMNS FROM push_tokens");
        if ($rc) { while ($c = mysqli_fetch_assoc($rc)) { $cols[] = $c['Field']; } }
    } catch (\Throwable $e) { return []; }
    if (!in_array('token', $cols) || !in_array('user_id', $cols)) return [];

    $whereAtivo = in_array('ativo', $cols) ? " AND ativo=1" : "";
    $inList = implode(',', $userIds);
    $map = [];
    $q = mysqli_query($con, "SELECT user_id, token FROM push_tokens WHERE user_id IN ($inList)$whereAtivo");
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $t = trim($r['token']);
            if (strpos($t, 'ExponentPushToken') !== false || strpos($t, 'ExpoPushToken') !== false) {
                $map[$r['user_id']][] = $t;
            }
        }
    }
    return $map;
}

/**
 * Envia um LOTE de mensagens para a Expo Push API (chunks de 100).
 * $mensagens: array de ['to','title','body','data',...]
 * Essencial para escala: N prestadores = ceil(N/100) chamadas (não N).
 */
function enviarExpoPushLote($con, $mensagens) {
    $logFile = __DIR__ . '/../log_push.txt';
    if (empty($mensagens)) return ['success' => false, 'error' => 'sem mensagens', 'enviadas' => 0];

    $lotes = 0; $okTotal = 0;
    foreach (array_chunk($mensagens, 100) as $chunk) {
        $ch = curl_init('https://exp.host/--/api/v2/push/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(array_values($chunk)),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $lotes++;

        $data = json_decode($resp, true);
        if ($code == 200 && isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $i => $ticket) {
                if (($ticket['status'] ?? '') === 'ok') {
                    $okTotal++;
                } elseif (($ticket['details']['error'] ?? '') === 'DeviceNotRegistered' && isset($chunk[$i]['to'])) {
                    $tk = mysqli_real_escape_string($con, $chunk[$i]['to']);
                    @mysqli_query($con, "UPDATE push_tokens SET ativo=0 WHERE token='$tk'");
                }
            }
        }
        @file_put_contents($logFile, "\n[EXPO LOTE] ".date('Y-m-d H:i:s')." http=$code enviadas=".count($chunk)."\n", FILE_APPEND);
    }
    return ['success' => true, 'lotes' => $lotes, 'ok' => $okTotal, 'total' => count($mensagens)];
}

/**
 * Envia push notification para um usuário
 * Envia TANTO para o app nativo (Expo) QUANTO para o navegador (Web Push)
 */
function enviarPushNotification($con, $userId, $userType, $title, $body, $url = '/index.php') {
    global $VAPID_PUBLIC_KEY, $VAPID_PRIVATE_KEY, $VAPID_SUBJECT, $webPushDisponivel;
    
    $userId = mysqli_real_escape_string($con, $userId);
    $userType = mysqli_real_escape_string($con, $userType);
    
    // 1. PRIMEIRO: Enviar push NATIVO via Expo (app Android/iOS instalado)
    $resultadoExpo = enviarExpoPush($con, $userId, $title, $body, $url);
    
    // 2. SEGUNDO: Enviar Web Push (navegador/PWA)
    $query = mysqli_query($con, "SELECT * FROM push_subscriptions WHERE user_id='$userId' AND user_type='$userType'");
    
    if (!$query || mysqli_num_rows($query) == 0) {
        // Não tem web push, mas pode ter enviado nativo com sucesso
        if (!empty($resultadoExpo['success'])) {
            return ['success' => true, 'message' => 'Notificação nativa enviada', 'expo' => $resultadoExpo];
        }
        return ['success' => false, 'error' => 'Usuário não tem inscrição push', 'expo' => $resultadoExpo];
    }
    
    $subscription = mysqli_fetch_assoc($query);
    
    $payload = json_encode([
        'title' => $title,
        'body' => $body,
        'icon' => '/pediuservico/icons/icon-192x192.png',
        'badge' => '/pediuservico/icons/icon-192x192.png',
        'url' => '/pediuservico' . $url,
        'tag' => 'pedido-' . time()
    ]);
    
    $endpoint = $subscription['endpoint'];
    $p256dh = $subscription['p256dh'];
    $auth = $subscription['auth'];
    
    $logFile = __DIR__ . '/../log_push.txt';
    
    // Usa a biblioteca minishlink/web-push se disponível
    if ($webPushDisponivel && class_exists('Minishlink\WebPush\WebPush')) {
        try {
            $webPush = new \Minishlink\WebPush\WebPush([
                'VAPID' => [
                    'subject' => $VAPID_SUBJECT,
                    'publicKey' => $VAPID_PUBLIC_KEY,
                    'privateKey' => $VAPID_PRIVATE_KEY
                ]
            ]);
            
            $subscriptionObj = \Minishlink\WebPush\Subscription::create([
                'endpoint' => $endpoint,
                'keys' => [
                    'p256dh' => $p256dh,
                    'auth' => $auth
                ]
            ]);
            
            $report = $webPush->sendOneNotification($subscriptionObj, $payload);
            
            file_put_contents($logFile, "\n=== PUSH (web-push-php): " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
            file_put_contents($logFile, "User: $userId ($userType)\n", FILE_APPEND);
            file_put_contents($logFile, "Title: $title | Body: $body\n", FILE_APPEND);
            
            if ($report->isSuccess()) {
                file_put_contents($logFile, "Resultado: SUCESSO\n", FILE_APPEND);
                return ['success' => true, 'message' => 'Notificação enviada (web + nativo)', 'expo' => $resultadoExpo];
            } else {
                $reason = $report->getReason();
                file_put_contents($logFile, "Resultado: FALHA - $reason\n", FILE_APPEND);
                
                if ($report->isSubscriptionExpired()) {
                    mysqli_query($con, "DELETE FROM push_subscriptions WHERE id='" . $subscription['id'] . "'");
                    file_put_contents($logFile, "Inscricao expirada - removida do banco\n", FILE_APPEND);
                }
                // Web push falhou, mas nativo pode ter funcionado
                if (!empty($resultadoExpo['success'])) {
                    return ['success' => true, 'message' => 'Notificação nativa enviada (web falhou)', 'expo' => $resultadoExpo, 'web_error' => $reason];
                }
                return ['success' => false, 'error' => $reason, 'expo' => $resultadoExpo];
            }
        } catch (Exception $e) {
            file_put_contents($logFile, "\n=== PUSH ERRO: " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
            file_put_contents($logFile, "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            // Web push deu exceção, mas nativo pode ter funcionado
            if (!empty($resultadoExpo['success'])) {
                return ['success' => true, 'message' => 'Notificação nativa enviada (web exception)', 'expo' => $resultadoExpo];
            }
            return ['success' => false, 'error' => $e->getMessage(), 'expo' => $resultadoExpo];
        }
    } else {
        // Biblioteca web-push não instalada - mas o nativo (Expo) pode ter funcionado
        file_put_contents($logFile, "\n=== PUSH WEB: " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
        file_put_contents($logFile, "User: $userId ($userType) | Biblioteca web-push NAO instalada (nativo via Expo prossegue)\n", FILE_APPEND);
        if (!empty($resultadoExpo['success'])) {
            return ['success' => true, 'message' => 'Notificação nativa enviada (web-push não instalado)', 'expo' => $resultadoExpo];
        }
        return ['success' => false, 'error' => 'Biblioteca web-push não instalada e sem token nativo', 'expo' => $resultadoExpo];
    }
}

/**
 * Envia push para múltiplos prestadores
 */
function enviarPushParaPrestadores($con, $prestadorIds, $title, $body, $url = '/index.php') {
    $results = [];
    foreach ($prestadorIds as $id) {
        $results[$id] = enviarPushNotification($con, $id, 'prestador', $title, $body, $url);
    }
    return $results;
}

// Se chamado diretamente via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
        exit;
    }
    
    $userId = $input['user_id'] ?? null;
    $userType = $input['user_type'] ?? 'prestador';
    $title = $input['title'] ?? 'Pediu Serviço';
    $body = $input['body'] ?? 'Você tem uma nova notificação';
    $url = $input['url'] ?? '/index.php';
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'user_id não fornecido']);
        exit;
    }
    
    $result = enviarPushNotification($con, $userId, $userType, $title, $body, $url);
    echo json_encode($result);
}
