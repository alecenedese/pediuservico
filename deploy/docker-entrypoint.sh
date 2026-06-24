#!/bin/sh
set -e

# --- Porta dinâmica do Render (default 10000; localmente cai para 80) ---
PORT="${PORT:-80}"
sed -ri "s/^Listen 80\$/Listen ${PORT}/" /etc/apache2/ports.conf || true
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf || true

# --- Gera send.php a partir das variáveis de ambiente ---
# Os segredos vivem nas Environment Variables do Render, nunca no repositório.
cat > /var/www/html/send.php <<'PHP'
<?php
$con = mysqli_connect(
    getenv('DB_HOST') ?: 'localhost',
    getenv('DB_USER') ?: 'root',
    getenv('DB_PASS') ?: '',
    getenv('DB_NAME') ?: 'pediuservico',
    (int)(getenv('DB_PORT') ?: 3306)
);
if (!$con) {
    http_response_code(500);
    die('Erro de conexao com o banco: ' . mysqli_connect_error());
}
mysqli_set_charset($con, 'utf8mb4');
date_default_timezone_set('America/Sao_Paulo');

// Prefixo base das URLs (app servida na raiz do domínio no Render).
$urlserver = getenv('BASE_URL') ?: '/';

// Sanitização básica de entradas esperada pelo código (logar.php, etc.).
if (!function_exists('protect')) {
    function protect($valor) {
        global $con;
        return mysqli_real_escape_string($con, trim((string)$valor));
    }
}
PHP

chown www-data:www-data /var/www/html/send.php

exec "$@"
