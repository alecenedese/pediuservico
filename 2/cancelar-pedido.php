<?php
require_once("send.php");
header('Content-Type: application/json');

$codpedido = isset($_GET['codpedido']) ? mysqli_real_escape_string($con, $_GET['codpedido']) : '';

if (empty($codpedido)) {
    echo json_encode(['success' => false, 'message' => 'Código do pedido não informado']);
    exit;
}

// Marcar todos os disparos deste pedido como perdido
$query1 = mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=0 WHERE codpedido = '$codpedido'");

// Atualizar status do pedido
$query2 = mysqli_query($con, "UPDATE pedido SET status='Cancelado pelo Cliente' WHERE codigo = '$codpedido'");

// Remover markers do mapa
$query3 = mysqli_query($con, "DELETE FROM markers WHERE codpedido = '$codpedido'");

if ($query1 && $query2) {
    echo json_encode(['success' => true, 'message' => 'Solicitação cancelada com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cancelar solicitação']);
}
?>
