<?php
require_once('send.php');

header('Content-Type: application/json; charset=utf-8');

// Aceita tanto POST quanto GET
$searchTerm = '';
if (isset($_GET['q'])) {
    $searchTerm = mysqli_real_escape_string($con, trim($_GET['q']));
} elseif (isset($_POST['search'])) {
    $searchTerm = mysqli_real_escape_string($con, trim($_POST['search']));
}

// Se não houver termo de busca, retorna todas as categorias
if (strlen($searchTerm) < 2) {
    $query = "SELECT c.codigo, c.titulo, COALESCE(g.titulo, 'Sem Grupo') AS grupo 
              FROM categoria c
              LEFT JOIN grupos g ON g.codigo = c.codgrupo
              ORDER BY c.titulo";
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        echo json_encode(['error' => mysqli_error($con)]);
        exit;
    }
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    echo json_encode($items);
    exit;
}

$query = "SELECT c.codigo, c.titulo, COALESCE(g.titulo, 'Sem Grupo') AS grupo 
          FROM categoria c
          LEFT JOIN grupos g ON g.codigo = c.codgrupo
          WHERE c.titulo LIKE '%$searchTerm%' OR g.titulo LIKE '%$searchTerm%'
          ORDER BY c.titulo
          LIMIT 20";
          
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['ok' => false, 'msg' => 'Erro na consulta: ' . mysqli_error($con)]);
    exit;
}

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

echo json_encode($items);
?>