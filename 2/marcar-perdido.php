<?php
require("send.php");

header('Content-Type: application/json; charset=utf-8');

$codpedido = isset($_GET['codpedido']) ? mysqli_real_escape_string($con, $_GET['codpedido']) : '';
$codcadastro = isset($_GET['codcadastro']) ? mysqli_real_escape_string($con, $_GET['codcadastro']) : '';

if(empty($codpedido) || empty($codcadastro)) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

// Marca como perdido apenas se ainda não foi confirmado
$queryCheck = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido = '$codpedido' AND codcadastro = '$codcadastro'");
$row = mysqli_fetch_array($queryCheck);

if($row && $row['aceito'] !== 's') {
    // Marca como perdido (p)
    mysqli_query($con, "UPDATE disparo_pedidos SET aceito = 'p', visto = 0 WHERE codpedido = '$codpedido' AND codcadastro = '$codcadastro'");
    
    // Atualiza o timer
    mysqli_query($con, "UPDATE timer_acordo SET status = 'expirado' WHERE codpedido = '$codpedido' AND codcadastro = '$codcadastro'");
    
    echo json_encode(['success' => true, 'message' => 'Marcado como perdido']);
} else {
    echo json_encode(['success' => false, 'message' => 'Já foi confirmado']);
}
exit;
?>
