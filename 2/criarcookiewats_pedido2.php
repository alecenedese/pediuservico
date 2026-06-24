<?php session_start();

$celular = $_GET['celular'];
$numero = $_GET['numero'];
$nome = $_GET['nome'];

$confirmanumero = $_GET['confirmanumero'];
$codcliente = $_GET['codcliente'];

if($confirmanumero == $numero) { 

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
echo "<script>window.location.href='meus-orcamentos-cli.php';</script>";

} else {
 
    echo "<script>alert('Código de verificação incorreto, Digite novo código enviado.'); window.location.href='confirmanumero_cliente_pedido.php?nome=$nome&codpedido=$codpedido&codcadastro=$codcadastro&celularCli=$celular';</script>";
}


?>