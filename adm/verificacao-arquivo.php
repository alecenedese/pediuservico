<?php
// Serve arquivos de verificação apenas para administradores autenticados.
session_start();

// Bloqueia acesso de quem não é admin logado
if (!isset($_SESSION['nomeUsuario'])) {
    http_response_code(403);
    exit('Acesso negado.');
}

$arquivo = isset($_GET['f']) ? $_GET['f'] : '';

// Sanitiza: aceita apenas o nome do arquivo (sem diretórios) para evitar path traversal
$arquivo = basename($arquivo);
if ($arquivo === '' || strpos($arquivo, '..') !== false) {
    http_response_code(400);
    exit('Arquivo inválido.');
}

$caminho = __DIR__ . '/../verificacoes/' . $arquivo;

if (!is_file($caminho)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

// Define o content-type conforme a extensão
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
