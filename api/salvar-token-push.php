<?php
// api/salvar-token-push.php
// Recebe o token push do Expo e salva no banco vinculado ao usuário

require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$token = $input['token'] ?? '';
$tipo = $input['tipo'] ?? ''; // 'prestador' ou 'cliente'
$idUsuario = $input['id_usuario'] ?? '';

if (empty($token) || empty($tipo) || empty($idUsuario)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Parâmetros obrigatórios: token, tipo, id_usuario']);
    exit;
}

// Valida se o token é do Expo
if (strpos($token, 'ExponentPushToken[') !== 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'Token inválido']);
    exit;
}

try {
    // Verifica se já existe um token para este usuário
    $check = $conexao->prepare("SELECT id FROM push_tokens WHERE token = ? AND tipo = ? AND id_usuario = ?");
    $check->bind_param("sss", $token, $tipo, $idUsuario);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Já existe, atualiza a data
        $update = $conexao->prepare("UPDATE push_tokens SET updated_at = NOW() WHERE token = ? AND tipo = ? AND id_usuario = ?");
        $update->bind_param("sss", $token, $tipo, $idUsuario);
        $update->execute();
        $update->close();
    } else {
        // Insere novo token
        $insert = $conexao->prepare("INSERT INTO push_tokens (token, tipo, id_usuario, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $insert->bind_param("sss", $token, $tipo, $idUsuario);
        $insert->execute();
        $insert->close();
    }

    $check->close();

    echo json_encode(['sucesso' => true, 'mensagem' => 'Token salvo com sucesso']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar token: ' . $e->getMessage()]);
}