<?php session_start();
require_once("send.php");
$celular = $_GET['celular'];
$numero = $_GET['numero'];
$nome = $_GET['nome'];
$codpedido = $_GET['codpedido'];
$codcadastro = $_GET['codcadastro'];
$confirmanumero = $_GET['confirmanumero'];
$codcliente = $_GET['codcliente'];

if($confirmanumero == $numero) { 

 $editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='ac', visto=0 where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$_GET['codcadastro']."'") or die(mysqli_error($con));


$nomeCli = $_GET['nomeCli'];

unset($_COOKIE['nomeCli']);
setcookie('nomeCli', null, -1, '/');

unset($_COOKIE['celularCli']);
setcookie('celularCli', null, -1, '/');

unset($_COOKIE['tipo']);
setcookie('tipo', null, -1, '/');

unset($_COOKIE['codcliente']);
setcookie('codcliente', null, -1, '/');

$tipo = "cliente";
setcookie("tipo",$tipo, time() + 172800000,  $path = "/");
setcookie("codcliente",$codcliente, time() + 172800000,  $path = "/");
setcookie("nomeCli",$nomeCli, time() + 172800000,  $path = "/");
setcookie("celularCli",$celular, time() + 172800000,  $path = "/");

//echo "<script>alert('Login realizado com sucesso! Você será redirecionado para o WhatsApp do Prestador');</script>";
echo "<script>window.location.href='pegar_contato.php?nome=$nome&codpedido=$codpedido&codcadastro=$codcadastro&codcliente=$codcliente';</script>";

} else {
 
    echo "<script>alert('Código de verificação incorreto, Digite novo código enviado.'); window.location.href='confirmanumero_cliente_pedido.php?nome=$nome&codpedido=$codpedido&codcadastro=$codcadastro&celularCli=$celular';</script>";
}


?>