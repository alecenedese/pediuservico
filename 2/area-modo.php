<?php
// area-modo.php
// Determina se o usuário é prestador e qual MODO (cliente/prestador) está ativo.
// Filosofia (estilo Mercado Livre):
//  - Por padrão TODO mundo vê a experiência de CLIENTE (busca de serviços).
//  - Nada de "área do prestador" aparece para quem está só buscando serviço.
//  - Quem JÁ é prestador pode alternar para o "Modo Prestador".
//  - Quem não é prestador recebe um convite discreto no menu da conta.

if (!isset($ehPrestador)) {
    $ehPrestador = isset($_COOKIE['eh_prestador']) && $_COOKIE['eh_prestador'] == '1';
}
$ehPrestadorLogin = isset($_COOKIE['login']) && !empty($_COOKIE['login']);
$ehPrestadorReal = $ehPrestador || $ehPrestadorLogin || (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador']));

// Modo ativo: 'cliente' (padrão) ou 'prestador'
$modoArea = isset($_COOKIE['modo_area']) ? $_COOKIE['modo_area'] : null;
if ($modoArea === null) {
    // Sem preferência salva: quem entrou pelo login de prestador começa em prestador.
    // Todo o resto começa como CLIENTE (experiência limpa de busca de serviço).
    $modoArea = $ehPrestadorLogin ? 'prestador' : 'cliente';
}

// Se não é prestador, FORÇA modo cliente (nunca vê nada de prestador)
if (!$ehPrestadorReal) {
    $modoArea = 'cliente';
}

// ===== Override pela PÁGINA atual (mantém o header/rodapé coerentes com a tela) =====
// Páginas que são claramente do PRESTADOR
$_paginasPrestador = array(
    'meus-orcamentos.php', 'meus-orcamentos2.php', 'meus-orcamentos-aguardando.php',
    'meus-orcamentos-perdidos.php', 'meus-orcamentos-finalizados.php',
    'minhasmoedas.php', 'minhas-categorias.php', 'verificacao.php',
    'meus-enderecos.php', 'edicao.php', 'salva-localizacao-pedido-aceito.php'
);
// Páginas que são claramente do CLIENTE
$_paginasCliente = array(
    'meus-orcamentos-cli.php', 'buscar.php', 'solicitar-servico.php',
    'servicos.php', 'novomapa.php', 'mapa.php', 'aguarda-prestador.php'
);
$_pgAtual = isset($paginaAtual) ? $paginaAtual : basename($_SERVER['PHP_SELF']);
if (in_array($_pgAtual, $_paginasPrestador) && $ehPrestadorReal) {
    $modoArea = 'prestador';
} elseif (in_array($_pgAtual, $_paginasCliente)) {
    $modoArea = 'cliente';
}
?>
