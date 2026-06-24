<?php
require("send.php");
header('Content-Type: application/json');

$codpedido = isset($_GET['codpedido']) ? mysqli_real_escape_string($con, $_GET['codpedido']) : '';

if (empty($codpedido)) {
    echo json_encode(['count' => 0]);
    exit;
}

// Conta prestadores que clicaram em "não tenho interesse" (aceito='p' E visto=1)
// visto=1 diferencia a auto-rejeição (não tenho interesse) das perdas por escolha de outro prestador
$qCount = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$codpedido' AND aceito='p' AND visto=1");
$row = mysqli_fetch_array($qCount);
$count = $row ? (int)$row['total'] : 0;

echo json_encode(['count' => $count]);
