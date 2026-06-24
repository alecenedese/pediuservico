<?php session_start();
require_once("send.php");

$nome = $_GET['nome'];
$login = $_GET['login'];
$senha = $_GET['senha'];
$tipo = "cliente";

setcookie("tipo",$tipo, time() + 172800000,  $path = "/");
setcookie("nome",$nome, time() + 172800000,  $path = "/");
setcookie("login",$login, time() + 172800000,  $path = "/");
setcookie("senha",$senha, time() + 172800000,  $path = "/");


echo "<script>window.location.href='https://gessomt.app.br/pediuservico/perfil';</script>";

?>