<?php
session_start();
require("send.php");

header('Content-Type: application/json');

$codpedido = isset($_POST['codpedido']) ? mysqli_real_escape_string($con, $_POST['codpedido']) : '';
$codcadastro = isset($_POST['codcadastro']) ? mysqli_real_escape_string($con, $_POST['codcadastro']) : '';
$motivo = isset($_POST['motivo']) ? mysqli_real_escape_string($con, $_POST['motivo']) : '';
$tipo = isset($_POST['tipo']) ? mysqli_real_escape_string($con, $_POST['tipo']) : '';

if (empty($codpedido) || empty($codcadastro)) {
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos']);
    exit;
}

// Verifica se tabela denuncias existe, se não cria
$checkTable = mysqli_query($con, "SHOW TABLES LIKE 'denuncias'");
if (!$checkTable || mysqli_num_rows($checkTable) == 0) {
    mysqli_query($con, "CREATE TABLE denuncias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codpedido INT NOT NULL,
        codcadastro VARCHAR(50) NOT NULL,
        tipo VARCHAR(100),
        motivo TEXT,
        data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(codpedido),
        INDEX(codcadastro)
    )");
}

// Insere denúncia
$insertDen = mysqli_query($con, "INSERT INTO denuncias (codpedido, codcadastro, tipo, motivo) VALUES ('$codpedido', '$codcadastro', '$tipo', '$motivo')");

// Marca o pedido como finalizado com nota 0 (denúncia)
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='f', visto=0 WHERE codpedido='$codpedido' AND codcadastro='$codcadastro'");

// Insere avaliação com 0 estrelas para denúncia
$checkAvl = mysqli_query($con, "SELECT id FROM avaliacoes WHERE codcadastro='$codcadastro' AND codpedido='$codpedido'");
if ($checkAvl && mysqli_num_rows($checkAvl) > 0) {
    mysqli_query($con, "UPDATE avaliacoes SET qtd_estrela=0, mensagem='[DENÚNCIA]', denuncia=1 WHERE codcadastro='$codcadastro' AND codpedido='$codpedido'");
} else {
    mysqli_query($con, "INSERT INTO avaliacoes (codcadastro, codpedido, qtd_estrela, mensagem, denuncia) VALUES ('$codcadastro', '$codpedido', 0, '[DENÚNCIA]', 1)");
}

echo json_encode(['ok' => true]);
?>
