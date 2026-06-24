<?php
// Salva inscrição de push notification no banco de dados
require_once('../send.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['subscription']) || !isset($input['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

$subscription = $input['subscription'];
$userId = mysqli_real_escape_string($con, $input['user_id']);
$userType = isset($input['user_type']) ? mysqli_real_escape_string($con, $input['user_type']) : 'prestador';

$endpoint = mysqli_real_escape_string($con, $subscription['endpoint']);
$p256dh = mysqli_real_escape_string($con, $subscription['keys']['p256dh']);
$auth = mysqli_real_escape_string($con, $subscription['keys']['auth']);

// Verifica se já existe uma inscrição para este usuário
$checkQuery = mysqli_query($con, "SELECT id FROM push_subscriptions WHERE user_id='$userId' AND user_type='$userType'");

if (mysqli_num_rows($checkQuery) > 0) {
    // Atualiza a inscrição existente
    $row = mysqli_fetch_assoc($checkQuery);
    $updateQuery = mysqli_query($con, "UPDATE push_subscriptions SET 
        endpoint='$endpoint', 
        p256dh='$p256dh', 
        auth='$auth',
        updated_at=NOW()
        WHERE id='" . $row['id'] . "'");
    
    if ($updateQuery) {
        echo json_encode(['success' => true, 'message' => 'Inscrição atualizada']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
} else {
    // Cria nova inscrição
    $insertQuery = mysqli_query($con, "INSERT INTO push_subscriptions 
        (user_id, user_type, endpoint, p256dh, auth, created_at, updated_at) 
        VALUES ('$userId', '$userType', '$endpoint', '$p256dh', '$auth', NOW(), NOW())");
    
    if ($insertQuery) {
        echo json_encode(['success' => true, 'message' => 'Inscrição criada']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}
