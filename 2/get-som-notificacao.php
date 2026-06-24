<?php
// Retorna a URL do som de notificação configurado no admin (item 9)
require_once("send.php");
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$url = '';
$tab = mysqli_query($con, "SHOW TABLES LIKE 'config_app'");
if ($tab && mysqli_num_rows($tab) > 0) {
    $q = mysqli_query($con, "SELECT valor FROM config_app WHERE chave='som_notificacao' LIMIT 1");
    if ($q && $r = mysqli_fetch_assoc($q)) {
        if (!empty($r['valor'])) {
            $url = 'https://gessomt.app.br/pediuservico/sons/' . rawurlencode($r['valor']);
        }
    }
}

echo json_encode(['url' => $url]);
