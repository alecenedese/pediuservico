<?php
session_start();
require_once("send.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login-unificado.php');
    exit;
}

$cpfCnpjRaw = isset($_POST['cpfCnpj']) ? $_POST['cpfCnpj'] : '';
$senha = isset($_POST['senha']) ? $_POST['senha'] : '';
$cpfCnpjLimpo = preg_replace('/\D/', '', $cpfCnpjRaw);

if (empty($cpfCnpjLimpo) || empty($senha) || (strlen($cpfCnpjLimpo) !== 11 && strlen($cpfCnpjLimpo) !== 14)) {
    header('Location: login-unificado.php?erro=credenciais');
    exit;
}

$cpfCnpjEsc = mysqli_real_escape_string($con, $cpfCnpjLimpo);
$cpfCnpjFmt = mysqli_real_escape_string($con, $cpfCnpjRaw);
$senhaEsc   = mysqli_real_escape_string($con, $senha);

// Garante coluna senha em clientes
$check = mysqli_query($con, "SHOW COLUMNS FROM clientes LIKE 'senha'");
if (!$check || mysqli_num_rows($check) === 0) {
    @mysqli_query($con, "ALTER TABLE clientes ADD COLUMN senha VARCHAR(100) DEFAULT '' AFTER MUNICIPIO");
}

$ehPrestador = false; $ehCliente = false;
$idPrestador = 0; $idCliente = 0;
$nomeUsuario = ''; $celularUsuario = '';

// 1) Tenta como PRESTADOR (parceiro)
$qP = mysqli_query($con, "
    SELECT id, NOME, CELULAR, CNPJ_CPF
    FROM parceiro
    WHERE (REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjEsc'
           OR CNPJ_CPF = '$cpfCnpjFmt')
      AND senha = '$senhaEsc'
    LIMIT 1
");
if ($qP && mysqli_num_rows($qP) > 0) {
    $rP = mysqli_fetch_assoc($qP);
    $ehPrestador = true;
    $idPrestador = $rP['id'];
    $nomeUsuario = $rP['NOME'];
    $celularUsuario = $rP['CELULAR'];
    @mysqli_query($con, "UPDATE parceiro SET ultimoAcesso='".date('Y-m-d H:i:s')."' WHERE id='$idPrestador'");
}

// 2) Tenta tambem como CLIENTE (mesmo CPF/CNPJ pode existir nas duas tabelas)
$qC = mysqli_query($con, "
    SELECT id, NOME, CELULAR, CNPJ_CPF
    FROM clientes
    WHERE (REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjEsc'
           OR CNPJ_CPF = '$cpfCnpjFmt')
      AND senha = '$senhaEsc'
    LIMIT 1
");
if ($qC && mysqli_num_rows($qC) > 0) {
    $rC = mysqli_fetch_assoc($qC);
    $ehCliente = true;
    $idCliente = $rC['id'];
    if (empty($nomeUsuario))    $nomeUsuario = $rC['NOME'];
    if (empty($celularUsuario)) $celularUsuario = $rC['CELULAR'];
}

if (!$ehPrestador && !$ehCliente) {
    header('Location: login-unificado.php?erro=credenciais');
    exit;
}

// Define cookies unificados (validade 30 dias)
$exp = time() + (30 * 24 * 3600);
setcookie('login_unificado', '1',                $exp, '/');
setcookie('cpf_cnpj_unificado', $cpfCnpjLimpo,   $exp, '/');
setcookie('nome_usuario',  $nomeUsuario,         $exp, '/');
setcookie('celular_usuario', $celularUsuario,    $exp, '/');
setcookie('eh_prestador', $ehPrestador ? '1':'0',$exp, '/');
setcookie('eh_cliente',   $ehCliente   ? '1':'0',$exp, '/');
if ($idPrestador) setcookie('id_prestador', $idPrestador, $exp, '/');
if ($idCliente)   setcookie('id_cliente',   $idCliente,   $exp, '/');

// Cookies de compatibilidade com codigo legado
if ($ehPrestador) {
    setcookie('login', $cpfCnpjFmt,  $exp, '/');
    setcookie('senha', $senha,       $exp, '/');
    setcookie('nome',  $nomeUsuario, $exp, '/');
    setcookie('id',    $idPrestador, $exp, '/');
    setcookie('tipo',  'prestador',  $exp, '/');
    setcookie('celularPrestador', $celularUsuario, $exp, '/');
}
if ($ehCliente) {
    setcookie('codcliente', $idCliente, $exp, '/');
    setcookie('nomeCli',    $nomeUsuario, $exp, '/');
    setcookie('celularCli', $celularUsuario, $exp, '/');
}

// Honra parametro retorno (POST ou GET) para voltar a pagina de origem
$retorno = '';
$fromServico = false;
if (!empty($_POST['retorno'])) {
    $retorno = $_POST['retorno'];
} elseif (!empty($_GET['retorno'])) {
    $retorno = $_GET['retorno'];
}
// Sanitiza: aceita apenas paths relativos / mesma origem
if (!empty($retorno)) {
    $parsed = parse_url($retorno);
    // Bloqueia URLs externas (com host) e javascript:
    if (!empty($parsed['host']) || (isset($parsed['scheme']) && !in_array(strtolower($parsed['scheme']), ['http','https']))) {
        $retorno = '';
    } else {
        // Extrai apenas o path + query (sem o host/scheme)
        $retorno = ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
    }
}

// Se veio do fluxo de solicitar serviço, volta para lá (não para o retorno que seria a mesma página)
if (!empty($_POST['from_servico'])) {
    $fromServico = true;
}

if ($fromServico) {
    // Extrai a URL base sem query string para evitar loops
    $parsed = parse_url($retorno);
    $baseUrl = ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
    header("Location: " . ($baseUrl ?: 'solicitar-servico.php'));
} elseif (!empty($retorno)) {
    header("Location: " . $retorno);
} else {
    // Sempre cai em buscar.php após o login (cliente ou prestador)
    header("Location: buscar.php");
}
exit;
