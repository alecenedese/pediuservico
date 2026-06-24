<?php
session_start();
require("send.php");

header('Content-Type: application/json');

$codpedido = isset($_POST['codpedido']) ? mysqli_real_escape_string($con, $_POST['codpedido']) : '';
$codcadastro = isset($_POST['codcadastro']) ? mysqli_real_escape_string($con, $_POST['codcadastro']) : '';
$nota = isset($_POST['nota']) ? (int)$_POST['nota'] : 0;
$mensagem = isset($_POST['mensagem']) ? mysqli_real_escape_string($con, $_POST['mensagem']) : '';

if (empty($codpedido) || empty($codcadastro) || $nota < 1 || $nota > 5) {
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos']);
    exit;
}

// Descobre o nome do cliente que está avaliando (a partir do pedido / cookies)
$nomeCliente = '';
if (isset($_COOKIE['nome_usuario']) && !empty($_COOKIE['nome_usuario'])) {
    $nomeCliente = mysqli_real_escape_string($con, $_COOKIE['nome_usuario']);
} elseif (isset($_COOKIE['nomeCli']) && !empty($_COOKIE['nomeCli'])) {
    $nomeCliente = mysqli_real_escape_string($con, $_COOKIE['nomeCli']);
}
// Fallback: busca pelo codcli do pedido
if (empty($nomeCliente)) {
    $qCli = mysqli_query($con, "SELECT c.NOME FROM pedido p INNER JOIN clientes c ON c.id = p.codcli WHERE p.codigo = '$codpedido' LIMIT 1");
    if ($qCli && $rCli = mysqli_fetch_array($qCli)) {
        $nomeCliente = mysqli_real_escape_string($con, $rCli['NOME']);
    }
}
if (empty($nomeCliente)) { $nomeCliente = 'Cliente'; }

// Garante que a coluna 'cliente' e 'denuncia' existam na tabela avaliacoes
$colCliente = mysqli_query($con, "SHOW COLUMNS FROM avaliacoes LIKE 'cliente'");
if ($colCliente && mysqli_num_rows($colCliente) == 0) {
    mysqli_query($con, "ALTER TABLE avaliacoes ADD COLUMN cliente VARCHAR(255) DEFAULT NULL");
}
$colDenuncia = mysqli_query($con, "SHOW COLUMNS FROM avaliacoes LIKE 'denuncia'");
if ($colDenuncia && mysqli_num_rows($colDenuncia) == 0) {
    mysqli_query($con, "ALTER TABLE avaliacoes ADD COLUMN denuncia TINYINT(1) DEFAULT 0");
}

// Verifica se já existe avaliação
$checkAvl = mysqli_query($con, "SELECT id FROM avaliacoes WHERE codcadastro='$codcadastro' AND codpedido='$codpedido'");
if ($checkAvl && mysqli_num_rows($checkAvl) > 0) {
    // Atualiza avaliação existente
    mysqli_query($con, "UPDATE avaliacoes SET qtd_estrela=$nota, mensagem='$mensagem', cliente='$nomeCliente', denuncia=0 WHERE codcadastro='$codcadastro' AND codpedido='$codpedido'");
} else {
    // Insere nova avaliação
    mysqli_query($con, "INSERT INTO avaliacoes (codcadastro, codpedido, qtd_estrela, mensagem, cliente, denuncia) VALUES ('$codcadastro', '$codpedido', $nota, '$mensagem', '$nomeCliente', 0)");
}

// Marca o pedido como finalizado (aceito='f') para o prestador
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='f', visto=0 WHERE codpedido='$codpedido' AND codcadastro='$codcadastro'");

echo json_encode(['ok' => true]);
?>
