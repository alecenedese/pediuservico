<?php
require("send.php"); // deve definir $con

header('Content-Type: application/json; charset=utf-8');

$codpedido_raw = $_GET['codpedido'] ?? '';
if ($codpedido_raw === '') {
    echo json_encode(['success' => false, 'aceito' => 'n', 'message' => 'codpedido não informado']);
    exit;
}

// se codpedido for numérico, use intval; se for string, escape
$codpedido = is_numeric($codpedido_raw) ? intval($codpedido_raw) : mysqli_real_escape_string($con, $codpedido_raw);

$response = ['success' => false, 'aceito' => 'n', 'message' => 'Sem registro'];

$sql = "SELECT aceito FROM disparo_pedidos WHERE codpedido = '$codpedido' AND aceito = 's'";
$result = mysqli_query($con, $sql);

if ($result === false) {
    // opcional: log do erro em arquivo para debugging
    error_log("verifica_status.php SQL ERROR: " . mysqli_error($con) . " -- SQL: $sql\n", 3, __DIR__ . '/verifica_status_error.log');
    echo json_encode(['success' => false, 'aceito' => 'n', 'message' => 'Erro na consulta SQL']);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $aceito = $row['aceito'];
    $response['success'] = true;
    $response['aceito'] = $aceito;
    $response['message'] = ($aceito === 's') ? 'aceito' : 'Aguardando Prestador';
} else {
    $response['success'] = true;
    $response['aceito'] = 'n';
    $response['message'] = 'Registro não encontrado';
}

echo json_encode($response);
exit;
?>
