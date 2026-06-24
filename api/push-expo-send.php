<?php
// Envia push notification via Expo Push Service para app nativo
// Requer: composer require expo-server-sdk-php (ou usar cURL direto)

require_once(__DIR__ . '/../send.php');

header('Content-Type: application/json');

/**
 * Envia push notification via Expo Push Service
 * 
 * @param mysqli $con Conexão com banco
 * @param string $expoToken Token Expo do dispositivo (ExponentPushToken[...])
 * @param string $title Título da notificação
 * @param string $body Corpo da notificação
 * @param array $data Dados adicionais (ex: codpedido, url, etc)
 * @param string $sound Nome do arquivo de som (ex: 'moedas.mp3')
 * @return array Resultado do envio
 */
function enviarExpoPush($con, $expoToken, $title, $body, $data = [], $sound = 'moedas.mp3') {
    // Validar token Expo
    if (!preg_match('/^ExponentPushToken\[(.*)\]$/', $expoToken)) {
        return ['success' => false, 'error' => 'Token Expo inválido'];
    }
    
    $url = 'https://exp.host/--/api/v2/push/send';
    
    $payload = [
        'to' => $expoToken,
        'title' => $title,
        'body' => $body,
        'data' => $data,
        'sound' => $sound,
        'priority' => 'high',
        'channelId' => 'default',
        'categoryId' => 'pedido',
        'mutableContent' => true,
        'contentAvailable' => true,
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    $logFile = __DIR__ . '/../log_expo_push.txt';
    file_put_contents($logFile, "\n=== EXPO PUSH: " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
    file_put_contents($logFile, "Token: $expoToken\n", FILE_APPEND);
    file_put_contents($logFile, "Title: $title | Body: $body\n", FILE_APPEND);
    file_put_contents($logFile, "Data: " . json_encode($data) . "\n", FILE_APPEND);
    file_put_contents($logFile, "HTTP Code: $httpCode\n", FILE_APPEND);
    
    if ($curlError) {
        file_put_contents($logFile, "cURL Error: $curlError\n", FILE_APPEND);
        return ['success' => false, 'error' => 'Erro cURL: ' . $curlError];
    }
    
    $result = json_decode($response, true);
    file_put_contents($logFile, "Response: " . json_encode($result) . "\n", FILE_APPEND);
    
    if ($httpCode === 200 && isset($result['data']['status']) && $result['data']['status'] === 'ok') {
        return ['success' => true, 'message' => 'Notificação Expo enviada', 'ticket' => $result['data']['id'] ?? null];
    } elseif ($httpCode === 200 && isset($result['data']['status']) && $result['data']['status'] === 'error') {
        $error = $result['data']['message'] ?? 'Erro desconhecido';
        $details = $result['data']['details'] ?? null;
        
        // Se token inválido, marcar como inativo no banco
        if (strpos($error, 'DeviceNotRegistered') !== false || strpos($error, 'InvalidCredentials') !== false) {
            $tokenClean = mysqli_real_escape_string($con, $expoToken);
            mysqli_query($con, "UPDATE push_tokens SET ativo = 0 WHERE token = '$tokenClean'");
            file_put_contents($logFile, "Token inválido - marcado como inativo\n", FILE_APPEND);
        }
        
        return ['success' => false, 'error' => $error, 'details' => $details];
    }
    
    return ['success' => false, 'error' => 'Erro HTTP: ' . $httpCode, 'response' => $result];
}

/**
 * Envia push para todos os prestadores de um pedido via Expo
 * Busca tokens na tabela push_tokens
 */
function enviarExpoPushParaPrestadores($con, $codpedido, $title, $body, $data = []) {
    $codpedido = intval($codpedido);
    
    // Buscar prestadores que receberam o disparo
    $query = mysqli_query($con, "
        SELECT DISTINCT dp.codcadastro 
        FROM disparo_pedidos dp 
        WHERE dp.codpedido = $codpedido AND dp.aceito = 'n'
    ");
    
    $results = [];
    
    if ($query && mysqli_num_rows($query) > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
            $codcadastro = $row['codcadastro'];
            
            // Buscar token Expo ativo do prestador
            $tokenQuery = mysqli_query($con, "
                SELECT token FROM push_tokens 
                WHERE user_id = '$codcadastro' AND platform = 'android' AND ativo = 1
                ORDER BY updated_at DESC LIMIT 1
            ");
            
            if ($tokenQuery && $tokenRow = mysqli_fetch_assoc($tokenQuery)) {
                $expoToken = $tokenRow['token'];
                
                // Adicionar codpedido aos dados
                $pushData = array_merge($data, [
                    'codpedido' => $codpedido,
                    'codcadastro' => $codcadastro,
                    'type' => 'novo_pedido'
                ]);
                
                $result = enviarExpoPush($con, $expoToken, $title, $body, $pushData);
                $results[$codcadastro] = $result;
            } else {
                $results[$codcadastro] = ['success' => false, 'error' => 'Token Expo não encontrado'];
            }
        }
    }
    
    return $results;
}

/**
 * Envia push para um usuário específico (cliente ou prestador)
 */
function enviarExpoPushParaUsuario($con, $userId, $userType, $title, $body, $data = []) {
    $userId = mysqli_real_escape_string($con, $userId);
    $userType = mysqli_real_escape_string($con, $userType);
    
    $platform = ($userType === 'cliente') ? 'android' : 'android'; // Ambos usam Android por enquanto
    
    $query = mysqli_query($con, "
        SELECT token FROM push_tokens 
        WHERE user_id = '$userId' AND platform = '$platform' AND ativo = 1
        ORDER BY updated_at DESC LIMIT 1
    ");
    
    if ($query && $row = mysqli_fetch_assoc($query)) {
        $expoToken = $row['token'];
        return enviarExpoPush($con, $expoToken, $title, $body, $data);
    }
    
    return ['success' => false, 'error' => 'Token Expo não encontrado para usuário'];
}

// Se chamado diretamente via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
        exit;
    }
    
    $expoToken = $input['token'] ?? null;
    $title = $input['title'] ?? 'Pediu Serviço';
    $body = $input['body'] ?? 'Nova notificação';
    $data = $input['data'] ?? [];
    $sound = $input['sound'] ?? 'moedas.mp3';
    
    if (!$expoToken) {
        echo json_encode(['success' => false, 'error' => 'Token Expo não fornecido']);
        exit;
    }
    
    $result = enviarExpoPush($con, $expoToken, $title, $body, $data, $sound);
    echo json_encode($result);
}