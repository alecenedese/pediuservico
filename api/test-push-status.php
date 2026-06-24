<?php
require_once('../send.php');
header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'prestador';

if (empty($userId)) {
    // Lista todas as inscrições
    $query = mysqli_query($con, "SELECT user_id, user_type, LEFT(endpoint, 80) as endpoint_short, created_at, updated_at FROM push_subscriptions ORDER BY updated_at DESC");
    $subs = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $subs[] = $row;
    }
    echo json_encode(['total' => count($subs), 'subscriptions' => $subs], JSON_PRETTY_PRINT);
} else {
    $userId = mysqli_real_escape_string($con, $userId);
    $userType = mysqli_real_escape_string($con, $userType);
    $query = mysqli_query($con, "SELECT * FROM push_subscriptions WHERE user_id='$userId' AND user_type='$userType'");
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        echo json_encode(['found' => true, 'endpoint' => substr($row['endpoint'], 0, 80) . '...', 'updated_at' => $row['updated_at']]);
    } else {
        echo json_encode(['found' => false, 'message' => "Nenhuma inscrição para user_id=$userId type=$userType"]);
    }
}
