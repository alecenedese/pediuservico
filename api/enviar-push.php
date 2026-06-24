<?php
// api/enviar-push.php
// Dispara push notification via Expo Push API

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

$tipo = $input['tipo'] ?? '';       // 'prestador' ou 'cliente'
$idUsuario = $input['id_usuario'] ?? '';
$titulo = $input['titulo'] ?? 'Pediu Serviço';
$mensagem = $input['mensagem'] ?? '';
$dados = $input['dados'] ?? [];     // dados extras (ex: url, pedido_id)

if (empty($tipo) || empty($idUsuario) || empty($mensagem)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Parâmetros obrigatórios: tipo, id_usuario, mensagem']);
    exit;
}

try {
    // Busca os tokens do usuário
    $stmt = $conexao->prepare("SELECT token FROM push_tokens WHERE tipo = ? AND id_usuario = ?");
    $stmt->bind_param("ss", $tipo, $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $tokens = [];
    while ($row = $result->fetch_assoc()) {
        $tokens[] = $row['token'];
    }
    $stmt->close();

    if (empty($tokens)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum token encontrado para este usuário']);
        exit;
    }

    // Monta as mensagens para a Expo Push API
    $messages = [];
    foreach ($tokens as $token) {
        $message = [
            'to' => $token,
            'sound' => 'moedas.mp3',
            'title' => $titulo,
            'body' => $mensagem,
            'priority' => 'high',
            'channelId' => 'default',
        ];

        // Adiciona dados extras se houver
        if (!empty($dados)) {
            $message['data'] = $dados;
        }

        $messages[] = $message;
    }

    // Envia para a Expo Push API
    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($messages),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro na Expo Push API', 'http_code' => $httpCode]);
        exit;
    }

    $resultado = json_decode($response, true);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Push enviado para ' . count($messages) . ' dispositivo(s)',
        'resposta_expo' => $resultado
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao enviar push: ' . $e->getMessage()]);
}