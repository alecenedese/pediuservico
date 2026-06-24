<?php
// Cria as tabelas necessárias para Push Notifications
require_once('../send.php');

header('Content-Type: application/json');

// Tabela de inscrições push
$sql1 = "CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('prestador', 'cliente') DEFAULT 'prestador',
    endpoint TEXT NOT NULL,
    p256dh VARCHAR(255) NOT NULL,
    auth VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_endpoint (endpoint(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Tabela de códigos de verificação
$sql2 = "CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    celular VARCHAR(20) NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    tipo ENUM('login', 'cadastro', 'pedido') DEFAULT 'login',
    usado TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX idx_celular (celular),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$results = [];

if (mysqli_query($con, $sql1)) {
    $results['push_subscriptions'] = 'OK';
} else {
    $results['push_subscriptions'] = 'ERRO: ' . mysqli_error($con);
}

if (mysqli_query($con, $sql2)) {
    $results['verification_codes'] = 'OK';
} else {
    $results['verification_codes'] = 'ERRO: ' . mysqli_error($con);
}

echo json_encode([
    'success' => true,
    'tables' => $results
]);
