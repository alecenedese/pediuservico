<?php
/**
 * Script de teste para API uazapi
 * Execute este arquivo diretamente no navegador para testar o envio
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Teste de Envio - API uazapi</h2>";

// Configurações - ALTERE O NÚMERO PARA TESTAR
$numeroTeste = "5566996939739"; // Coloque seu número aqui (com DDD)
$tokenAPI = "7000bcff-6c7a-4ee5-aef7-94e4caefb972";
$mensagemTeste = "Teste de envio via API uazapi - " . date('d/m/Y H:i:s');

echo "<p><strong>Número:</strong> $numeroTeste</p>";
echo "<p><strong>Mensagem:</strong> $mensagemTeste</p>";
echo "<hr>";

// Teste 1: Verificar se cURL está disponível
echo "<h3>1. Verificando cURL...</h3>";
if (function_exists('curl_init')) {
    echo "<p style='color:green'>✅ cURL está instalado e disponível</p>";
} else {
    echo "<p style='color:red'>❌ cURL NÃO está disponível! Instale a extensão php_curl</p>";
    exit;
}

// Teste 2: Tentar enviar mensagem
echo "<h3>2. Enviando mensagem de teste...</h3>";

$curl = curl_init();

$payload = [
    'number' => $numeroTeste,
    'text' => $mensagemTeste
];

echo "<p><strong>Payload enviado:</strong></p>";
echo "<pre>" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

curl_setopt_array($curl, [
    CURLOPT_URL => "https://pediuservico.uazapi.com/send/text",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/json",
        "token: " . $tokenAPI
    ],
    CURLOPT_SSL_VERIFYPEER => false, // Desabilita verificação SSL para teste
    CURLOPT_VERBOSE => true
]);

// Captura informações de debug
$verbose = fopen('php://temp', 'w+');
curl_setopt($curl, CURLOPT_STDERR, $verbose);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$err = curl_error($curl);
$errno = curl_errno($curl);

curl_close($curl);

// Mostra informações de debug
rewind($verbose);
$verboseLog = stream_get_contents($verbose);

echo "<h3>3. Resultado:</h3>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($err) {
    echo "<p style='color:red'><strong>❌ Erro cURL ($errno):</strong> $err</p>";
} else {
    echo "<p style='color:green'>✅ Requisição enviada com sucesso</p>";
}

echo "<p><strong>Resposta da API:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Decodifica resposta JSON
$responseData = json_decode($response, true);
if ($responseData) {
    echo "<p><strong>Resposta decodificada:</strong></p>";
    echo "<pre>" . print_r($responseData, true) . "</pre>";
    
    // Verifica possíveis erros na resposta
    if (isset($responseData['error'])) {
        echo "<p style='color:red'><strong>❌ Erro da API:</strong> " . $responseData['error'] . "</p>";
    }
    if (isset($responseData['message'])) {
        echo "<p><strong>Mensagem:</strong> " . $responseData['message'] . "</p>";
    }
}

echo "<h3>4. Log de Debug cURL:</h3>";
echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";

echo "<hr>";
echo "<h3>Possíveis problemas:</h3>";
echo "<ul>";
echo "<li><strong>Token inválido:</strong> Verifique se o token está correto e ativo</li>";
echo "<li><strong>Sessão desconectada:</strong> O WhatsApp pode ter deslogado da API</li>";
echo "<li><strong>Número incorreto:</strong> Formato deve ser 55 + DDD + número (ex: 5565999999999)</li>";
echo "<li><strong>API free.uazapi.com:</strong> Pode ter limitações ou estar offline</li>";
echo "</ul>";

echo "<p><em>Teste executado em: " . date('d/m/Y H:i:s') . "</em></p>";
?>
