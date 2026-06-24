<?php
// ATENÇÃO: Se o seu arquivo de conexão com o banco não se chama 'db_connection.php'
// ou não está na mesma pasta, ajuste o caminho aqui.
require_once('send.php'); 

header('Content-Type: application/json');

// Habilita a exibição de erros para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// A variável $con deve ser a sua variável de conexão do arquivo 'db_connection.php'
$searchTerm = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : '';

$query = "SELECT id, NOME FROM parceiro WHERE NOME LIKE '%$searchTerm%' ORDER BY NOME LIMIT 20";
$result = mysqli_query($con, $query);

if (!$result) {
    // Se a query falhar, retorna um erro em JSON para ajudar a depurar
    echo json_encode(['error' => 'Query falhou: ' . mysqli_error($con)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_array($result)) {
    $data[] = ['id' => $row['id'], 'text' => $row['NOME']];
}

echo json_encode($data);
?>