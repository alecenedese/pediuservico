<?php
/**
 * 📤 ENVIAR NOTIFICAÇÃO PUSH VIA EXPO
 * 
 * Uso:
 * enviarNotificacaoPush($user_id, 'Título', 'Mensagem', ['url' => 'https://...']);
 */

function enviarNotificacaoPush($user_id, $titulo, $mensagem, $data = []) {
    require_once 'config.php';
    
    // 🔍 Buscar tokens do usuário
    $stmt = $conn->prepare("
        SELECT token FROM push_tokens 
        WHERE user_id = ? AND active = 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("⚠️ Nenhum token encontrado para user_id: $user_id");
        return [
            'success' => false,
            'error' => 'Nenhum token encontrado para este usuário'
        ];
    }
    
    $tokens = [];
    while ($row = $result->fetch_assoc()) {
        $tokens[] = $row['token'];
    }
    
    // 📦 Preparar mensagens para o Expo Push API
    $messages = [];
    foreach ($tokens as $token) {
        $messages[] = [
            'to' => $token,
            'title' => $titulo,
            'body' => $mensagem,
            'data' => $data,
            'sound' => 'default',
            'priority' => 'high',
            'channelId' => 'default'
        ];
    }
    
    // 📤 Enviar para Expo Push API
    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Accept-Encoding: gzip, deflate'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['data'])) {
        error_log("✅ Notificação enviada para user_id: $user_id");
        return [
            'success' => true,
            'sent_to' => count($tokens),
            'response' => $responseData
        ];
    } else {
        error_log("❌ Erro ao enviar notificação: " . $response);
        return [
            'success' => false,
            'error' => 'Erro ao enviar notificação',
            'response' => $responseData
        ];
    }
}

/**
 * 📤 ENVIAR NOTIFICAÇÃO PARA MÚLTIPLOS USUÁRIOS
 */
function enviarNotificacaoMultiplosUsuarios($user_ids, $titulo, $mensagem, $data = []) {
    $resultados = [];
    
    foreach ($user_ids as $user_id) {
        $resultados[$user_id] = enviarNotificacaoPush($user_id, $titulo, $mensagem, $data);
    }
    
    return $resultados;
}

// 🧪 TESTE (remover em produção)
if (isset($_GET['testar'])) {
    header('Content-Type: application/json');
    
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode([
            'error' => 'Informe o user_id: ?testar&user_id=123'
        ]);
        exit;
    }
    
    $resultado = enviarNotificacaoPush(
        $user_id,
        '🧪 Teste de Notificação',
        'Se você recebeu isso, o push está funcionando!',
        ['url' => 'https://gessomt.app.br/pediuservico/']
    );
    
    echo json_encode($resultado, JSON_PRETTY_PRINT);
    exit;
}
?>
