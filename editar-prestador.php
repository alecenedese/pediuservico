<?php
require_once("send.php");
header("Content-Type: text/html; charset=utf-8", true);

if (isset($_POST['cpf_cnpj'])) {
    $cpf_cnpj = mysqli_real_escape_string($con, $_POST['cpf_cnpj']);
    $nome = mysqli_real_escape_string($con, $_POST['nome']);
    $telefone = mysqli_real_escape_string($con, $_POST['telefone']);
    $pass = mysqli_real_escape_string($con, $_POST['pass']);

    $queryEnvio = mysqli_query($con, "UPDATE parceiro SET NOME='$nome', CELULAR='$telefone', senha='$pass' WHERE CNPJ_CPF='$cpf_cnpj'") or die(mysqli_error($con));

    echo "<script>alert('Perfil atualizado com sucesso!'); window.location.href='edicao.php';</script>";
} else {
    echo "<script>alert('Erro: dados não recebidos.'); window.location.href='edicao.php';</script>";
}
?>
