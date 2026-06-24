<?php
session_start();

// Limpa todos os cookies
$cookiesToClear = ['tipo', 'nome', 'login', 'senha', 'celularPrestador', 'id', 'nomeCli', 'celularCli', 'codcliente', 'celular_usuario', 'nome_usuario', 'id_prestador', 'id_cliente', 'eh_prestador', 'eh_cliente', 'login_unificado', 'cpf_cnpj_unificado', 'forcou_relogin'];
foreach ($cookiesToClear as $c) {
    unset($_COOKIE[$c]);
    setcookie($c, null, -1, '/');
}

session_destroy();

echo "<script>window.location.href='login-unificado.php';</script>";
?>
