<?php
session_start();
require_once("send.php");

$celular = isset($_GET['celular']) ? $_GET['celular'] : '';
$numero = isset($_GET['numero']) ? $_GET['numero'] : '';
$confirmanumero = isset($_GET['confirmanumero']) ? $_GET['confirmanumero'] : '';
$codcliente = isset($_GET['codcliente']) ? $_GET['codcliente'] : '';
$nomeCli = isset($_GET['nomeCli']) ? $_GET['nomeCli'] : '';

if ($confirmanumero == $numero) {
    // Limpa cookies antigos
    unset($_COOKIE['nomeCli']);
    setcookie('nomeCli', null, -1, '/');
    unset($_COOKIE['celularCli']);
    setcookie('celularCli', null, -1, '/');
    unset($_COOKIE['tipo']);
    setcookie('tipo', null, -1, '/');
    unset($_COOKIE['codcliente']);
    setcookie('codcliente', null, -1, '/');

    // Cria novos cookies
    $tipo = "cliente";
    setcookie("tipo", $tipo, time() + 172800000, "/");
    setcookie("codcliente", $codcliente, time() + 172800000, "/");
    setcookie("nomeCli", $nomeCli, time() + 172800000, "/");
    setcookie("celularCli", $celular, time() + 172800000, "/");

    echo "<script>alert('Login realizado com sucesso!'); window.location.href='meus-orcamentos-cli.php';</script>";
} else {
    echo "<script>alert('Código de verificação incorreto. Tente novamente.'); window.location.href='confirmanumero_cliente_login.php?celularCli=" . urlencode($celular) . "';</script>";
}
?>
