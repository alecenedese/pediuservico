<?php
// Serve arquivos de verificação apenas para o próprio dono (usuário logado).
session_start();
require_once("send.php");

// Identifica o usuário logado (cliente ou prestador)
$idUsuario = 0;
$tipoUsuario = 'cliente';
if (isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
    $idUsuario = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
    $tipoUsuario = 'cliente';
} elseif (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $q = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
    if ($q && $r = mysqli_fetch_array($q)) { $idUsuario = $r['id']; $tipoUsuario = 'prestador'; }
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $idUsuario = mysqli_real_escape_string($con, $_COOKIE['id_prestador']);
    $tipoUsuario = 'prestador';
} elseif (isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
    $idUsuario = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
    $tipoUsuario = 'cliente';
}

if (!$idUsuario) {
    http_response_code(403);
    exit('Acesso negado.');
}

$arquivo = isset($_GET['f']) ? basename($_GET['f']) : '';
if ($arquivo === '' || strpos($arquivo, '..') !== false) {
    http_response_code(400);
    exit('Arquivo inválido.');
}

// Confere que o arquivo pertence ao usuário logado (está em algum campo do seu registro)
$qV = mysqli_query($con, "SELECT foto_pessoal, foto_documento, foto_comprovante, foto_antecedentes FROM verificacoes_usuario WHERE id_usuario='$idUsuario' AND tipo_usuario='$tipoUsuario' ORDER BY id DESC LIMIT 1");
$pertence = false;
if ($qV && $row = mysqli_fetch_assoc($qV)) {
    foreach ($row as $valor) {
        if ($valor === $arquivo) { $pertence = true; break; }
    }
}

if (!$pertence) {
    http_response_code(403);
    exit('Acesso negado.');
}

$caminho = __DIR__ . '/verificacoes/' . $arquivo;
if (!is_file($caminho)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

$ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
$tipos = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'pdf'  => 'application/pdf',
];
$contentType = isset($tipos[$ext]) ? $tipos[$ext] : 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($caminho));
header('Cache-Control: private, max-age=300');
readfile($caminho);
exit;
