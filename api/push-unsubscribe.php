<?php
// Remove inscrição de push notification
require_once('../send.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'user_id não fornecido']);
    exit;
}

$userId = mysqli_real_escape_string($con, $input['user_id']);
$userType = isset($input['user_type']) ? mysqli_real_escape_string($con, $input['user_type']) : 'prestador';

$deleteQuery = mysqli_query($con, "DELETE FROM push_subscriptions WHERE user_id='$userId' AND user_type='$userType'");

if ($deleteQuery) {
    echo json_encode(['success' => true, 'message' => 'Inscrição removida']);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}
