<?php   

 require_once("send.php"); 
 include('token/config.php');

 header("Content-Type: text/html; charset=utf-8",true);

    if($cpf == ''){

        $tipo = $cnpj;
    } else {
        $tipo = $cpf;
    }
/*
for($i=0; $i <= $_POST['subcategoria[]']; $i++) {

    echo $_POST['subcategoria'][$i];

}*/

    $checkboxPaletras =  $_POST['subcategoria'];


$dataCad = date("Y-m-d");
$queryEnvio = mysqli_query($con, "INSERT INTO parceiro (TIPO, NOME, CNPJ_CPF, CELULAR, ESTADO, MUNICIPIO, senha, serviconao, dataCad) VALUES
('pre', '$nome', '$tipo', '$celular', '$estado', '$cidade', '$confirm_pass', '$serviconao', '$dataCad')") or die(mysqli_error($con));

$contaUltimo = mysqli_fetch_array(mysqli_query($con, "SELECT max(x.id) FROM parceiro x")) or die(mysqli_error($con));

// user chat
$queryEnviochat = mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) VALUES
        ('".$contaUltimo[0]."', '$nome',  '$nome',  '', 'user-default.png', '', '$celular')") or die(mysqli_error($con));


foreach($checkboxPaletras as $paletras) :
  
   if($paletras <> '') { 
    $queryCategoria = mysqli_fetch_array(mysqli_query($con, "SELECT codgrupo, codigo FROM categoria where titulo='".$paletras."'")) or die(mysqli_error($con));
    $querySubs = mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) values
     ('".$contaUltimo[0]."', '".$queryCategoria[0]."', '".$queryCategoria[1]."')");
} 
endforeach;




    echo "<script>alert('Cadastro Realizado Com Sucesso!'); window.location.href='https://gessomt.app.br/pediuservico/criarcookie.php?nome=$nome&login=$tipo&senha=$confirm_pass';</script>";


?>