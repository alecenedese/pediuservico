<?php
// ATENÇÃO: Inclua aqui o seu arquivo de conexão com o banco de dados.
// Exemplo: require_once('../config/conexao.php');
require_once('send.php'); // <-- AJUSTE ESTA LINHA

header('Content-Type: application/json');

// Habilita a exibição de erros para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// A variável $con deve ser a sua variável de conexão
$searchTerm = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : '';

$query = "SELECT id, NOME FROM parceiro WHERE NOME LIKE '%$searchTerm%' ORDER BY NOME LIMIT 20";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['error' => 'Query falhou: ' . mysqli_error($con)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_array($result)) {
    $data[] = ['id' => $row['id'], 'text' => $row['NOME']];
}

echo json_encode($data);
?>