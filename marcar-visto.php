<?php
require("send.php");
header('Content-Type: application/json');

$codpedido = isset($_GET['codpedido']) ? mysqli_real_escape_string($con, $_GET['codpedido']) : '';

if (empty($codpedido) || !isset($_COOKIE['login'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$qPrest = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".$_COOKIE['login']."'");
$rowPrest = mysqli_fetch_array($qPrest);
if (!$rowPrest) {
    echo json_encode(['ok' => false]);
    exit;
}
$idPrest = $rowPrest['id'];

mysqli_query($con, "UPDATE disparo_pedidos SET visto=1
    WHERE codpedido='$codpedido' AND codcadastro='$idPrest' AND visto=0");

$decremented = (mysqli_affected_rows($con) > 0);
echo json_encode(['ok' => true, 'decremented' => $decremented]);
