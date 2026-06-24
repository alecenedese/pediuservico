<?php
// salvar_token_push.php - Endpoint para salvar token de push notification do app nativo
// Recebe: token (Expo push token), user_id, platform (ios/android)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'send.php';

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);

// LOG de debug: registra toda chamada recebida
@file_put_contents(__DIR__ . '/log_push_tokens.txt',
    "\n=== " . date('Y-m-d H:i:s') . " ===\n" .
    "Method: " . $_SERVER['REQUEST_METHOD'] . "\n" .
    "Raw: " . $rawBody . "\n",
    FILE_APPEND);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'JSON inválido']);
    exit();
}

$token = $input['token'] ?? '';
$user_id = $input['user_id'] ?? '';
$platform = $input['platform'] ?? '';

if (empty($token) || empty($user_id)) {
    echo json_encode(['success' => false, 'error' => 'Token e user_id são obrigatórios']);
    exit();
}

// Rejeita user_id inválido (não numérico) — evita salvar lixo capturado do DOM
// (ex: "👤 Entrar", "← Voltar"). IDs de prestador/cliente são sempre numéricos.
if (!ctype_digit((string)$user_id)) {
    echo json_encode(['success' => false, 'error' => 'user_id inválido (não numérico)', 'recebido' => $user_id]);
    exit();
}

try {
    // Verificar se a tabela push_tokens existe, se não criar
    $checkTable = $pdo->query("SHOW TABLES LIKE 'push_tokens'");
    if ($checkTable->rowCount() === 0) {
        $pdo->exec("
            CREATE TABLE push_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(100) NOT NULL,
                token TEXT NOT NULL,
                platform VARCHAR(20) DEFAULT 'android',
                ativo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // Detecta colunas existentes (o schema pode ser antigo/diferente)
    $colsStmt = $pdo->query("SHOW COLUMNS FROM push_tokens");
    $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Adiciona colunas que faltam (migração automática, não destrutiva)
    if (!in_array('user_id', $cols)) {
        $pdo->exec("ALTER TABLE push_tokens ADD COLUMN user_id VARCHAR(100) NOT NULL DEFAULT ''");
        $cols[] = 'user_id';
    }
    if (!in_array('token', $cols)) {
        $pdo->exec("ALTER TABLE push_tokens ADD COLUMN token TEXT NOT NULL");
        $cols[] = 'token';
    }
    if (!in_array('platform', $cols)) {
        $pdo->exec("ALTER TABLE push_tokens ADD COLUMN platform VARCHAR(20) DEFAULT 'android'");
        $cols[] = 'platform';
    }
    if (!in_array('ativo', $cols)) {
        $pdo->exec("ALTER TABLE push_tokens ADD COLUMN ativo TINYINT(1) DEFAULT 1");
        $cols[] = 'ativo';
    }
    if (!in_array('updated_at', $cols)) {
        $pdo->exec("ALTER TABLE push_tokens ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        $cols[] = 'updated_at';
    }

    $temPlatform = in_array('platform', $cols);

    // Verifica se já existe registro para este usuário (e plataforma, se houver a coluna)
    if ($temPlatform) {
        $sel = $pdo->prepare("SELECT id FROM push_tokens WHERE user_id = ? AND platform = ? LIMIT 1");
        $sel->execute([$user_id, $platform]);
    } else {
        $sel = $pdo->prepare("SELECT id FROM push_tokens WHERE user_id = ? LIMIT 1");
        $sel->execute([$user_id]);
    }
    $existente = $sel->fetch(PDO::FETCH_ASSOC);

    if ($existente) {
        // Atualiza o token existente
        if ($temPlatform) {
            $upd = $pdo->prepare("UPDATE push_tokens SET token = ?, platform = ?, ativo = 1, updated_at = NOW() WHERE id = ?");
            $upd->execute([$token, $platform, $existente['id']]);
        } else {
            $upd = $pdo->prepare("UPDATE push_tokens SET token = ?, ativo = 1, updated_at = NOW() WHERE id = ?");
            $upd->execute([$token, $existente['id']]);
        }
    } else {
        // Insere novo registro
        if ($temPlatform) {
            $ins = $pdo->prepare("INSERT INTO push_tokens (user_id, token, platform, ativo, updated_at) VALUES (?, ?, ?, 1, NOW())");
            $ins->execute([$user_id, $token, $platform]);
        } else {
            $ins = $pdo->prepare("INSERT INTO push_tokens (user_id, token, ativo, updated_at) VALUES (?, ?, 1, NOW())");
            $ins->execute([$user_id, $token]);
        }
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Token salvo com sucesso',
        'user_id' => $user_id,
        'platform' => $platform
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao salvar token push: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor', 'detalhe' => $e->getMessage()]);
}