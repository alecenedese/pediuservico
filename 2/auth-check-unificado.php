<?php
// Auto-logout para sessoes antigas (login por celular) que nao migraram
// para o novo metodo (CPF/CNPJ + senha).
// Inclua este arquivo no topo das paginas que requerem o usuario logado
// (ou pelo menos antes de exibir conteudo restrito).

if (!isset($_COOKIE['login_unificado']) || $_COOKIE['login_unificado'] !== '1') {
    // Detecta cookies do metodo antigo (login por celular) e os limpa.
    $cookiesLegado = [
        'celular_usuario', 'nome_usuario', 'eh_cliente', 'eh_prestador',
        'celularCli', 'codcliente', 'nomeCli',
        'id_cliente', 'id_prestador'
    ];
    $temLegado = false;
    foreach ($cookiesLegado as $c) {
        if (isset($_COOKIE[$c]) && $_COOKIE[$c] !== '') { $temLegado = true; break; }
    }

    if ($temLegado) {
        foreach ($cookiesLegado as $c) {
            unset($_COOKIE[$c]);
            setcookie($c, '', time() - 3600, '/');
        }
        // Mantem cookies do prestador antigo (login.php por CPF+senha) intactos:
        // 'login', 'senha', 'nome', 'tipo', 'id', 'celularPrestador'
        // pois o usuario que entrou por login.php ja usa CPF/CNPJ + senha.

        // Sinaliza que houve logout forcado para a pagina de login mostrar aviso.
        setcookie('forcou_relogin', '1', time() + 60, '/');
    }
}
