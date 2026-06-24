<?php
session_start();
require_once("send.php");

$celular = isset($_GET['celular']) ? $_GET['celular'] : '';
$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
$codigoEsperado = isset($_GET['codigo_esperado']) ? $_GET['codigo_esperado'] : '';
$nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$idPrestador = isset($_GET['id_prestador']) ? $_GET['id_prestador'] : '0';
$idCliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : '0';
$ehPrestador = isset($_GET['eh_prestador']) ? $_GET['eh_prestador'] : '0';
$ehCliente = isset($_GET['eh_cliente']) ? $_GET['eh_cliente'] : '0';

// Login direto ou com código
if ($codigoEsperado != 'direto' && $codigo != $codigoEsperado) {
    echo "<script>alert('Código incorreto. Tente novamente.'); window.location.href='verificar-celular.php?celular=" . urlencode($celular) . "';</script>";
    exit;
}

$numerolimpo = preg_replace('/\D/', '', $celular);

// Limpa TODOS os cookies antigos (prestador e cliente)
$cookiesToClear = ['tipo', 'nome', 'login', 'senha', 'celularPrestador', 'id', 'nomeCli', 'celularCli', 'codcliente', 'celular_usuario', 'nome_usuario', 'id_prestador', 'id_cliente', 'eh_prestador', 'eh_cliente'];
foreach ($cookiesToClear as $c) {
    unset($_COOKIE[$c]);
    setcookie($c, null, -1, '/');
}

$expiry = time() + 172800000; // ~2000 dias

// Cookies unificados
setcookie("celular_usuario", $celular, $expiry, "/");
setcookie("nome_usuario", $nome, $expiry, "/");
setcookie("eh_prestador", $ehPrestador, $expiry, "/");
setcookie("eh_cliente", $ehCliente, $expiry, "/");
setcookie("id_prestador", $idPrestador, $expiry, "/");
setcookie("id_cliente", $idCliente, $expiry, "/");

// Mantém cookies antigos para compatibilidade com páginas existentes
if ($ehPrestador == '1') {
    // Busca dados completos do prestador
    $queryP = mysqli_query($con, "SELECT * FROM parceiro WHERE id='$idPrestador'");
    $dadosP = mysqli_fetch_assoc($queryP);
    
    setcookie("tipo", "prestador", $expiry, "/");
    setcookie("nome", $nome, $expiry, "/");
    setcookie("login", isset($dadosP['CNPJ_CPF']) ? $dadosP['CNPJ_CPF'] : '', $expiry, "/");
    setcookie("senha", isset($dadosP['senha']) ? $dadosP['senha'] : '', $expiry, "/");
    setcookie("celularPrestador", $celular, $expiry, "/");
    setcookie("id", $idPrestador, $expiry, "/");
    
    // Atualiza último acesso
    $data = date('Y-m-d H:i:s');
    mysqli_query($con, "UPDATE parceiro SET ultimoAcesso='$data' WHERE id='$idPrestador'");
}

if ($ehCliente == '1') {
    setcookie("codcliente", $idCliente, $expiry, "/");
    setcookie("nomeCli", $nome, $expiry, "/");
    setcookie("celularCli", $celular, $expiry, "/");
}

// Marca código como usado
mysqli_query($con, "UPDATE verification_codes SET usado=1 WHERE celular='$numerolimpo' AND codigo='$codigoEsperado'");

// Item 1: Ao logar sempre cai na opção buscar prestador
$retorno = isset($_GET['retorno']) ? $_GET['retorno'] : '';
if (!empty($retorno)) {
    // Se tem retorno específico, vai para lá
    echo "<script>window.location.href='" . htmlspecialchars($retorno) . "';</script>";
} else {
    // Item 1: Sempre redireciona para buscar prestador após login
    echo "<script>window.location.href='buscar.php';</script>";
}
?>
