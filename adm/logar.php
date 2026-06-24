<?php session_start();
 require_once("send.php"); 
$acao = $_POST['acao'];
if($acao=="acessar") {
		
$login = protect($_POST['login']);
$senha = protect($_POST['senha']);	

	$queryUsuario = mysqli_query($con, "SELECT * FROM admin WHERE login='".$login."' AND senha='".$senha."'");
	$totalUsuario = mysqli_num_rows($queryUsuario);
	$selecionaUsuario = mysqli_fetch_object($queryUsuario);
	
	$cod_usuario = $selecionaUsuario->codigo;
	$nomeUsuario = $selecionaUsuario->login;


	if($totalUsuario > 0) { 
			// Insere um bloqueio

			$_SESSION['nomeUsuario'] = $nomeUsuario;
			$_SESSION['cod_usuario'] = $cod_usuario;

			echo "<script>window.location.href='index.php';</script>";
	} else {
			echo "<script>alert('Olá, seu usuário e/ou senha foram digitados Incorretamente'); window.location.href='login.php';</script>";

	}


// Redireciona à index.php se o usuário já está logado
 } ?>
