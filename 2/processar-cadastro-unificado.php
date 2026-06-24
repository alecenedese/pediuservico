<?php
session_start();
require_once("send.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro-unificado.php');
    exit;
}

$tipo       = isset($_POST['tipo']) && $_POST['tipo'] === 'J' ? 'J' : 'F';
$cpfCnpjRaw = isset($_POST['cpfCnpj']) ? trim($_POST['cpfCnpj']) : '';
$nome       = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$whatsapp   = isset($_POST['whatsapp']) ? trim($_POST['whatsapp']) : '';
$senha      = isset($_POST['senha']) ? $_POST['senha'] : '';
$senha2     = isset($_POST['senha2']) ? $_POST['senha2'] : '';

$cpfCnpjLimpo = preg_replace('/\D/', '', $cpfCnpjRaw);
$whatsappLimpo = preg_replace('/\D/', '', $whatsapp);

// Validacoes basicas
if ($senha !== $senha2 || strlen($senha) < 4) {
    header('Location: cadastro-unificado.php?erro=senha');
    exit;
}
if (empty($nome) || empty($cpfCnpjLimpo) || empty($whatsappLimpo)) {
    header('Location: cadastro-unificado.php?erro=invalido');
    exit;
}
if ($tipo === 'F' && strlen($cpfCnpjLimpo) !== 11) {
    header('Location: cadastro-unificado.php?erro=invalido');
    exit;
}
if ($tipo === 'J' && strlen($cpfCnpjLimpo) !== 14) {
    header('Location: cadastro-unificado.php?erro=invalido');
    exit;
}
if (strlen($whatsappLimpo) < 10) {
    header('Location: cadastro-unificado.php?erro=invalido');
    exit;
}

// Garante coluna senha em clientes
$check = mysqli_query($con, "SHOW COLUMNS FROM clientes LIKE 'senha'");
if (!$check || mysqli_num_rows($check) === 0) {
    @mysqli_query($con, "ALTER TABLE clientes ADD COLUMN senha VARCHAR(100) DEFAULT '' AFTER MUNICIPIO");
}

$cpfCnpjEsc = mysqli_real_escape_string($con, $cpfCnpjLimpo);
$cpfCnpjFmt = mysqli_real_escape_string($con, $cpfCnpjRaw);
$nomeEsc    = mysqli_real_escape_string($con, $nome);
$whatsEsc   = mysqli_real_escape_string($con, $whatsapp);
$senhaEsc   = mysqli_real_escape_string($con, $senha);

// Verifica duplicidade em clientes (com ou sem mascara)
$qDup = mysqli_query($con, "
    SELECT id FROM clientes
    WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjEsc'
       OR CNPJ_CPF = '$cpfCnpjFmt'
    LIMIT 1
");
if ($qDup && mysqli_num_rows($qDup) > 0) {
    // Ja cadastrado: redireciona ao login
    header('Location: cadastro-unificado.php?erro=duplicado');
    exit;
}

$dataCad = date('Y-m-d');
$ins = mysqli_query($con, "
    INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, senha, dataCad)
    VALUES ('$tipo', '$nomeEsc', '$cpfCnpjFmt', '', '$whatsEsc', '', '', '$senhaEsc', '$dataCad')
");
if (!$ins) {
    header('Location: cadastro-unificado.php?erro=banco');
    exit;
}
$idCliente = mysqli_insert_id($con);

// Cria registro em users (chat) - mantem compatibilidade com fluxos existentes
@mysqli_query($con, "
    INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular)
    VALUES ('$idCliente', '$nomeEsc', '$nomeEsc', '', 'user-default.png', '', '$whatsEsc')
");

// Define cookies (login automatico - validade 30 dias)
$exp = time() + (30 * 24 * 3600);
setcookie('login_unificado',     '1',           $exp, '/');
setcookie('cpf_cnpj_unificado',  $cpfCnpjLimpo, $exp, '/');
setcookie('nome_usuario',        $nome,         $exp, '/');
setcookie('celular_usuario',     $whatsapp,     $exp, '/');
setcookie('eh_prestador',        '0',           $exp, '/');
setcookie('eh_cliente',          '1',           $exp, '/');
setcookie('id_cliente',          $idCliente,    $exp, '/');

// Compatibilidade legada
setcookie('codcliente', $idCliente, $exp, '/');
setcookie('nomeCli',    $nome,      $exp, '/');
setcookie('celularCli', $whatsapp,  $exp, '/');

// Honra retorno se fornecido
$retorno = isset($_POST['retorno']) ? $_POST['retorno'] : '';
if (!empty($retorno)) {
    $parsed = parse_url($retorno);
    if (!empty($parsed['host']) || (isset($parsed['scheme']) && !in_array(strtolower($parsed['scheme']), ['http','https']))) {
        $retorno = '';
    }
}

// Item 8: se o cadastro foi iniciado pela Área dos Prestadores, leva direto para
// escolher as categorias (tornar-prestador.php cria o registro de parceiro e marca eh_prestador).
$comoPrestador = (isset($_POST['comoprestador']) && $_POST['comoprestador'] == '1')
    || (strpos($retorno, 'meus-orcamentos') !== false)
    || (strpos($retorno, 'tornar-prestador') !== false);

if ($comoPrestador) {
    header('Location: tornar-prestador.php');
    exit;
}

header('Location: ' . (!empty($retorno) ? $retorno : 'buscar.php'));
exit;
