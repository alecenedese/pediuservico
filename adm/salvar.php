<?php session_start();
 require_once("send.php"); 
 header("Content-Type: text/html; charset=utf-8",true);

 if($cpf == ''){
    $tipoDoc = $cnpj;
} else {
    $tipoDoc = $cpf;
}

    $queryEnvio = mysqli_query($con, "INSERT INTO parceiro (TIPO, STATUS, PERFIL_DO_CLIENTE, NOME, CNPJ_CPF, RG_IE, TELEFONE, CELULAR, CELULAR2, email_pessoal, email_comercial, ENDERECO, NUMERO, TIPO_IMOVEL, REFERENCIA, BAIRRO, ESTADO, MUNICIPIO, COD_IBGE, FOTO, FOTOCOMDOC, senha) VALUES
	('$tipo', '$status', '$perfil', '$nome', '$tipoDoc', '$rg', '$telefone', '$celular', '$celular2', '$email_pessoal', '$email_comercial', '$endereco', '$numero', '$tipo_imovel', '$referencia', '$bairro', '$estado', '$cidade', '0', '0', '0', '$senha')") or die(mysqli_error($con));


    header('location: https://gessomt.app.br/pediuservico/adm/listar-cadastros');

?>