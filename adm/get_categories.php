<?php
// ATENÇÃO: Ajuste o caminho para o seu arquivo de conexão, se necessário.
require_once('send.php'); 

header('Content-Type: application/json');

// Habilita a exibição de erros para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

$searchTerm = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

$query = "SELECT c.codigo, c.titulo, g.titulo AS grupo 
          FROM categoria c
          JOIN grupos g ON g.codigo = c.codgrupo
          WHERE c.titulo LIKE '%$searchTerm%' OR g.titulo LIKE '%$searchTerm%'
          ORDER BY g.titulo, c.titulo";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['error' => 'Query falhou: ' . mysqli_error($con)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>