<?php
include("send.php");

$termo = isset($_GET['termo']) ? mysqli_real_escape_string($con, $_GET['termo']) : '';
$resultados = array();

if (!empty($termo)) {
    $query = "SELECT DISTINCT c.codigo, c.titulo, c.codgrupo 
              FROM categoria c
              WHERE c.titulo LIKE '%$termo%' 
              ORDER BY c.titulo
              LIMIT 15";
    
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $resultados[] = array(
                'id' => $row['codigo'],
                'codgrupo' => $row['codgrupo'],
                'nome' => $row['titulo']
            );
        }
    }
}

header('Content-Type: application/json');
echo json_encode($resultados);
?>
