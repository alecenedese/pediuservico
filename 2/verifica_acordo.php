<?php
require("send.php");

header('Content-Type: application/json; charset=utf-8');

$codpedido = isset($_GET['codpedido']) ? mysqli_real_escape_string($con, $_GET['codpedido']) : '';
$codcadastro = isset($_GET['codcadastro']) ? mysqli_real_escape_string($con, $_GET['codcadastro']) : '';

if(empty($codpedido) || empty($codcadastro)) {
    echo json_encode(['success' => false, 'status' => 'erro', 'message' => 'Parâmetros inválidos']);
    exit;
}

// Verifica se o prestador já debitou as moedas (status 's' = confirmado)
$query = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido = '$codpedido' AND codcadastro = '$codcadastro'");
$row = mysqli_fetch_array($query);

if($row && $row['aceito'] === 's') {
    // Busca ID do cliente a partir dos cookies
    $codClienteChat = '';
    if (isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
        $codClienteChat = $_COOKIE['codcliente'];
    } elseif (isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
        $codClienteChat = $_COOKIE['id_cliente'];
    }
    // Busca nome do cliente
    $nomeClienteChat = '';
    if (isset($_COOKIE['nomeCli'])) {
        $nomeClienteChat = $_COOKIE['nomeCli'];
    } elseif (isset($_COOKIE['nome_usuario'])) {
        $nomeClienteChat = $_COOKIE['nome_usuario'];
    }
    
    echo json_encode([
        'success' => true,
        'status' => 'confirmado',
        'message' => 'Prestador confirmou o acordo',
        'user' => $nomeClienteChat,
        'user_id' => $codClienteChat,
        'user_from' => $codcadastro
    ]);
} else {
    echo json_encode([
        'success' => true,
        'status' => 'aguardando',
        'message' => 'Aguardando confirmação do prestador'
    ]);
}
exit;
?>
