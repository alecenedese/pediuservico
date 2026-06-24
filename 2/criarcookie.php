<?php session_start();
require_once("send.php");

$nome = $_GET['nome'];
$login = $_GET['login'];
$senha = $_GET['senha'];
$celular = $_GET['celular'];
$id = $_GET['id'];
$tipo = "prestador";

setcookie("tipo",$tipo, time() + 172800000,  $path = "/");
setcookie("nome",$nome, time() + 172800000,  $path = "/");
setcookie("login",$login, time() + 172800000,  $path = "/");
setcookie("senha",$senha, time() + 172800000,  $path = "/");
setcookie("celularPrestador",$celular, time() + 172800000,  $path = "/");
setcookie("id",$id, time() + 172800000,  $path = "/");

//echo "<script>window.location.href='https://gessomt.app.br/pediuservico/edicao';</script>";
echo "<script>window.location.href='buscar.php';</script>";

?>