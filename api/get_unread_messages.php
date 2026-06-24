<?php
// Retorna contagem de mensagens não lidas para um usuário
header('Content-Type: application/json');
require_once(__DIR__ . '/../send.php');

$user_id = isset($_GET['user_id']) ? mysqli_real_escape_string($con, $_GET['user_id']) : '';

if (empty($user_id)) {
    echo json_encode(['count' => 0]);
    exit;
}

// Conta mensagens não lidas (opened=0) enviadas PARA este usuário
$query = mysqli_query($con, "SELECT COUNT(*) as cnt FROM chats WHERE to_id='$user_id' AND opened=0");
$count = 0;
if ($query && $row = mysqli_fetch_array($query)) {
    $count = (int)$row['cnt'];
}

echo json_encode(['count' => $count]);
