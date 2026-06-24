<?php
require("send.php");
header('Content-Type: application/json');

$codpedido = isset($_POST['codpedido']) ? mysqli_real_escape_string($con, $_POST['codpedido']) : '';

if (empty($codpedido) || !isset($_COOKIE['login'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$qPrest = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
$rowPrest = mysqli_fetch_array($qPrest);
if (!$rowPrest) {
    echo json_encode(['ok' => false]);
    exit;
}
$idPrest = $rowPrest['id'];

// Marca apenas este prestador como não interessado (visto=1 = auto-rejeição, sem badge)
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=1
    WHERE codpedido='$codpedido' AND codcadastro='$idPrest' AND aceito='n'");

$ok = (mysqli_affected_rows($con) > 0);
echo json_encode(['ok' => $ok]);
