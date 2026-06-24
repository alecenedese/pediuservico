<?php session_start();
 require_once("send.php"); 
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	
$login = protect($_POST['cpfCnpj']);
$senha = protect($_POST['senha2']);	

	$queryUsuario = mysqli_query($con, "SELECT * FROM parceiro WHERE CNPJ_CPF='".$login."' AND senha='".$senha."'");
	$totalUsuario = mysqli_num_rows($queryUsuario);
	$selecionaUsuario = mysqli_fetch_object($queryUsuario);
	
	$nomeUsuario = $selecionaUsuario->NOME;
	$id = $selecionaUsuario->id;
	$celular = $selecionaUsuario->CELULAR;


	if($totalUsuario > 0) { 

		$data = date('Y-m-d H:i:s');	
		$update = mysqli_query($con, "UPDATE parceiro SET ultimoAcesso='".$data."' where id = '".$id."'") or die(mysqli_error($con));

		//echo "<script> window.location.href='https://gessomt.app.br/pediuservico/criarcookie.php?nome=$nomeUsuario&login=$login&senha=$senha';</script>";
		echo "<script> window.location.href='criarcookie.php?nome=$nomeUsuario&login=$login&senha=$senha&celular=$celular&id=$id';</script>";

			
	} else {
			echo "<script>window.location.href='login.php?msg=erro';</script>";

	}


// Redireciona à index.php se o usuário já está logado
 ?>
