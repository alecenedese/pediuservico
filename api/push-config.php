<?php
// Configuração das chaves VAPID para Web Push
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Carrega chaves VAPID do arquivo gerado
$vapidKeysFile = __DIR__ . '/vapid-keys.php';
if (file_exists($vapidKeysFile)) {
    require_once($vapidKeysFile);
    $publicKey = $VAPID_PUBLIC_KEY;
} else {
    $publicKey = 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U';
}

echo json_encode([
    'publicKey' => $publicKey
]);
