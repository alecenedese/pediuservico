<?php
// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("send.php");
header('Content-Type: application/json; charset=utf-8');

$userId = isset($_GET['id']) ? mysqli_real_escape_string($con, $_GET['id']) : '';

$response = [
    'success' => false,
    'categorias' => [],
    'debug' => [],
    'userId' => $userId
];

if (empty($userId)) {
    $response['debug'][] = 'ID do usuário não fornecido';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$response['debug'][] = 'Buscando categorias para usuário ID: ' . $userId;

// Buscar categorias do usuário
$query = "
    SELECT 
        g.titulo as categoria,
        c.titulo as subcategoria
    FROM categoria_prestador cp
    INNER JOIN grupos g ON g.codigo = cp.codcategoria
    INNER JOIN categoria c ON c.codigo = cp.codsubcategoria
    WHERE cp.codcadastro = '$userId'
    ORDER BY g.titulo, c.titulo
";

$response['debug'][] = 'Query montada';

$result = mysqli_query($con, $query);

if (!$result) {
    $response['debug'][] = 'Erro na query: ' . mysqli_error($con);
    $response['error'] = mysqli_error($con);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$response['debug'][] = 'Query executada com sucesso';

$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $response['categorias'][] = [
        'categoria' => $row['categoria'],
        'subcategoria' => $row['subcategoria']
    ];
    $count++;
}

$response['debug'][] = 'Total de categorias encontradas: ' . $count;
$response['success'] = true;

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
