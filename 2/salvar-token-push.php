<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Permitir OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

// 📥 Receber dados JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 📝 Log para debug
error_log("📥 Dados recebidos em salvar-token-push.php: " . print_r($data, true));

if (!$data) {
    echo json_encode([
        'success' => false,
        'error' => 'Dados inválidos'
    ]);
    exit;
}

$user_id = $data['user_id'] ?? null;
$token = $data['token'] ?? null;
$platform = $data['platform'] ?? 'expo-android';
$type = $data['type'] ?? 'expo-push-token';

if (!$user_id || !$token) {
    echo json_encode([
        'success' => false,
        'error' => 'user_id e token são obrigatórios',
        'received' => $data
    ]);
    exit;
}

try {
    // 🔍 Verificar se já existe um token para este usuário
    $stmt = $conn->prepare("
        SELECT id FROM push_tokens 
        WHERE user_id = ? AND platform = ?
    ");
    $stmt->bind_param("is", $user_id, $platform);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // ♻️ Atualizar token existente
        $row = $result->fetch_assoc();
        $stmt = $conn->prepare("
            UPDATE push_tokens 
            SET token = ?, type = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $token, $type, $row['id']);
        $stmt->execute();
        
        error_log("♻️ Token atualizado para user_id: $user_id");
        
        echo json_encode([
            'success' => true,
            'message' => 'Token atualizado com sucesso',
            'action' => 'updated',
            'user_id' => $user_id,
            'token_id' => $row['id']
        ]);
    } else {
        // ➕ Inserir novo token
        $stmt = $conn->prepare("
            INSERT INTO push_tokens (user_id, token, platform, type, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("isss", $user_id, $token, $platform, $type);
        $stmt->execute();
        
        error_log("✅ Novo token salvo para user_id: $user_id");
        
        echo json_encode([
            'success' => true,
            'message' => 'Token salvo com sucesso',
            'action' => 'inserted',
            'user_id' => $user_id,
            'token_id' => $conn->insert_id
        ]);
    }
    
} catch (Exception $e) {
    error_log("❌ Erro ao salvar token: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
