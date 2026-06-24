<?php
// Conta pedidos NÃO VISTOS para o prestador (badges por etapa)
// Usa os mesmos JOINs das páginas para contar apenas o que é exibido
$_badgeServicos = 0;
$_badgeNovos = 0;
$_badgeAceitos = 0;
$_badgeEnviados = 0;
$_badgePerdidos = 0;
$_badgeFinalizados = 0;

// Busca ID do prestador (cookie login ou id)
$_idPrestBadge = '';
if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $qPrest = mysqli_query($con, "select id from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
    if ($qPrest && $rPrest = mysqli_fetch_array($qPrest)) {
        $_idPrestBadge = $rPrest['id'];
    }
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $_idPrestBadge = $_COOKIE['id_prestador'];
} elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
    $_idPrestBadge = $_COOKIE['id'];
}

// Verifica se coluna visto existe; se não, cria automaticamente
$_colVistoExists = false;
$_checkVisto = mysqli_query($con, "SHOW COLUMNS FROM disparo_pedidos LIKE 'visto'");
if ($_checkVisto && mysqli_num_rows($_checkVisto) > 0) {
    $_colVistoExists = true;
} else {
    // Cria a coluna visto automaticamente
    @mysqli_query($con, "ALTER TABLE disparo_pedidos ADD COLUMN visto TINYINT(1) DEFAULT 0");
    @mysqli_query($con, "UPDATE disparo_pedidos SET visto = 1 WHERE visto = 0");
    $_colVistoExists = true;
}
$_vistoFilter = $_colVistoExists ? " AND d.visto = 0 " : "";

if (!empty($_idPrestBadge)) {
    // Novos: mesmos JOINs de meus-orcamentos.php (categoria_prestador)
    $qN = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='n' $_vistoFilter");
    if ($qN && $rN = mysqli_fetch_array($qN)) $_badgeNovos = (int)$rN['cnt'];

    // Aceitos: mesmos JOINs de meus-orcamentos2.php (categoria_prestador + pega_contato)
    $qA = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        INNER JOIN pega_contato pg ON pg.codcadastro = d.codcadastro AND pg.codpedido = d.codpedido
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='s' $_vistoFilter");
    if ($qA && $rA = mysqli_fetch_array($qA)) $_badgeAceitos = (int)$rA['cnt'];

    // Orçamentos enviados: mesmos JOINs de meus-orcamentos-aguardando.php (categoria_prestador + markers)
    $qE = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        INNER JOIN markers m ON m.codpedido = d.codpedido AND m.type = 2 AND m.codcadastro = d.codcadastro
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito IN ('a','ac') $_vistoFilter");
    if ($qE && $rE = mysqli_fetch_array($qE)) $_badgeEnviados = (int)$rE['cnt'];
    
    // Perdidos: mesmos JOINs de meus-orcamentos-perdidos.php (categoria_prestador)
    $qP = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='p' $_vistoFilter");
    if ($qP && $rP = mysqli_fetch_array($qP)) $_badgePerdidos = (int)$rP['cnt'];

    // Finalizados: pedidos avaliados pelo cliente (aceito='f') não vistos pelo prestador
    $qFin = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='f' AND d.visto=0");
    if ($qFin && $rFin = mysqli_fetch_array($qFin)) $_badgeFinalizados = (int)$rFin['cnt'];

    $_badgeServicos = $_badgeNovos + $_badgeAceitos + $_badgeEnviados + $_badgePerdidos + $_badgeFinalizados;
}
?>
