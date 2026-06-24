<?php

require_once("send.php");

// Identifica o prestador logado (mesma lógica de meus-enderecos.php)
$codigo = 0;
if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $queryEdit = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
    if ($queryEdit && $rowEdit = mysqli_fetch_array($queryEdit)) {
        $codigo = $rowEdit['id'];
    }
} elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
    $codigo = (int)$_COOKIE['id'];
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $codigo = (int)$_COOKIE['id_prestador'];
} elseif (isset($_COOKIE['cpf_cnpj_unificado']) && !empty($_COOKIE['cpf_cnpj_unificado'])) {
    $cpfLimpo = preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']);
    $cpfEsc = mysqli_real_escape_string($con, $cpfLimpo);
    $queryEdit = mysqli_query($con, "SELECT id FROM parceiro WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc' LIMIT 1");
    if ($queryEdit && $rowEdit = mysqli_fetch_array($queryEdit)) {
        $codigo = $rowEdit['id'];
    }
}

if ($codigo <= 0) {
    echo "<script>alert('Não foi possível identificar seu cadastro. Faça login novamente.'); window.location.href='login-unificado.php';</script>";
    exit;
}

// Sanitiza os dados recebidos
$cep      = mysqli_real_escape_string($con, $_GET['cep']);
$endereco = mysqli_real_escape_string($con, $_GET['endereco']);
$n        = mysqli_real_escape_string($con, $_GET['n']);
$bairro   = mysqli_real_escape_string($con, $_GET['bairro']);
$uf       = mysqli_real_escape_string($con, $_GET['uf']);
$cidade   = mysqli_real_escape_string($con, $_GET['cidade']);
$latitude = mysqli_real_escape_string($con, $_GET['latitude']);
$longitude= mysqli_real_escape_string($con, $_GET['longitude']);

// Remove endereço anterior e insere o novo (vinculado ao cadastro correto)
mysqli_query($con, "DELETE FROM endereco_prestador WHERE cod_cadastro = '$codigo'") or die(mysqli_error($con));

mysqli_query($con, "INSERT INTO endereco_prestador (cod_cadastro, cep, endereco, n, bairro, uf, cidade, lat, log) VALUES
('$codigo', '$cep', '$endereco', '$n', '$bairro', '$uf', '$cidade', '$latitude', '$longitude')") or die(mysqli_error($con));

echo "<script>alert('Endereço salvo com sucesso!'); window.location.href='meus-enderecos.php';</script>";

?>
