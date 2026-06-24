<?php
// ATENÇÃO: Ajuste o caminho para o seu arquivo de conexão, se necessário.
require_once('send.php'); 

header('Content-Type: application/json');

// Habilita a exibição de erros para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['codcadastro'])) {
    $codcadastro = mysqli_real_escape_string($con, $_POST['codcadastro']);

    $query = "SELECT c.codigo, c.titulo, g.titulo AS grupo
              FROM categoria_prestador cp
              JOIN categoria c ON c.codigo = cp.codsubcategoria
              JOIN grupos g ON g.codigo = c.codgrupo
              WHERE cp.codcadastro = '$codcadastro'
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
} else {
    // Retorna um array vazio se nenhum prestador for enviado
    echo json_encode([]);
}
?>