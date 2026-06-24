<?php
// Item 3 & 24: Endpoint para retornar contagem de badges atualizada em tempo real
require_once("send.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'badges' => [
        'servicos' => 0,
        'novos' => 0,
        'aceitos' => 0,
        'enviados' => 0,
        'perdidos' => 0,
        'finalizados' => 0,
        'pedidos' => 0
    ]
];

// Busca ID do prestador
$_idPrestBadge = '';
if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $qPrest = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".$_COOKIE['login']."'");
    if ($qPrest && $rPrest = mysqli_fetch_array($qPrest)) {
        $_idPrestBadge = $rPrest['id'];
    }
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $_idPrestBadge = $_COOKIE['id_prestador'];
} elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
    $_idPrestBadge = $_COOKIE['id'];
}

// Verifica se coluna visto existe
$_colVistoExists = false;
$_checkVisto = mysqli_query($con, "SHOW COLUMNS FROM disparo_pedidos LIKE 'visto'");
if ($_checkVisto && mysqli_num_rows($_checkVisto) > 0) {
    $_colVistoExists = true;
}
$_vistoFilter = $_colVistoExists ? " AND d.visto = 0 " : "";

if (!empty($_idPrestBadge)) {
    // Novos
    $qN = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='n' $_vistoFilter");
    if ($qN && $rN = mysqli_fetch_array($qN)) $response['badges']['novos'] = (int)$rN['cnt'];

    // Aceitos
    $qA = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        INNER JOIN pega_contato pg ON pg.codcadastro = d.codcadastro AND pg.codpedido = d.codpedido
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='s' $_vistoFilter");
    if ($qA && $rA = mysqli_fetch_array($qA)) $response['badges']['aceitos'] = (int)$rA['cnt'];

    // Orçamentos enviados
    $qE = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        INNER JOIN markers m ON m.codpedido = d.codpedido AND m.type = 2 AND m.codcadastro = d.codcadastro
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito IN ('a','ac') $_vistoFilter");
    if ($qE && $rE = mysqli_fetch_array($qE)) $response['badges']['enviados'] = (int)$rE['cnt'];
    
    // Perdidos
    $qP = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        INNER JOIN pedido p ON p.codigo = d.codpedido
        INNER JOIN categoria_prestador cat ON cat.codcadastro = d.codcadastro AND p.categoria = cat.codcategoria AND p.subcategoria = cat.codsubcategoria
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='p' $_vistoFilter");
    if ($qP && $rP = mysqli_fetch_array($qP)) $response['badges']['perdidos'] = (int)$rP['cnt'];

    // Finalizados
    $qFin = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt 
        FROM disparo_pedidos d
        WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='f' AND d.visto=0");
    if ($qFin && $rFin = mysqli_fetch_array($qFin)) $response['badges']['finalizados'] = (int)$rFin['cnt'];

    $response['badges']['servicos'] = $response['badges']['novos'] + $response['badges']['aceitos'] + 
                                       $response['badges']['enviados'] + $response['badges']['perdidos'] + 
                                       $response['badges']['finalizados'];
}

// Conta pedidos pendentes para o cliente
$_codcli = isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '';
if (!empty($_codcli)) {
    $qCliB = mysqli_query($con, "SELECT COUNT(DISTINCT dp.codpedido) as cnt FROM disparo_pedidos dp INNER JOIN pedido p ON p.codigo=dp.codpedido WHERE dp.aceito IN ('a','ac') AND p.codcli='".$_codcli."'");
    if ($qCliB && $rCliB = mysqli_fetch_array($qCliB)) {
        $response['badges']['pedidos'] = (int)$rCliB['cnt'];
    }
}

$response['success'] = true;
echo json_encode($response);
?>
