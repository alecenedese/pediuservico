<?php
// Script para corrigir registros de categoria_prestador que estao sem codcategoria
// Execute uma vez e depois pode deletar este arquivo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

require("send.php");

if (!isset($con) || $con->connect_errno) {
    die("Erro de conexão com o banco de dados.");
}

// Buscar registros que estao com codcategoria = 0, NULL ou vazio
$query = mysqli_query($con, "
    SELECT cp.codigo, cp.codsubcategoria, cp.codcategoria, c.codgrupo 
    FROM categoria_prestador cp
    LEFT JOIN categoria c ON c.codigo = cp.codsubcategoria
    WHERE cp.codcategoria IS NULL OR cp.codcategoria = 0 OR cp.codcategoria = ''
");

if (!$query) {
    die("Erro na consulta: " . mysqli_error($con));
}

$total = mysqli_num_rows($query);
echo "Encontrados <strong>$total</strong> registros sem codcategoria.<br><br>";

$corrigidos = 0;
$erros = 0;

while ($row = mysqli_fetch_assoc($query)) {
    if (!empty($row['codgrupo'])) {
        $update = mysqli_query($con, "UPDATE categoria_prestador SET codcategoria = '".$row['codgrupo']."' WHERE codigo = '".$row['codigo']."'");
        if ($update) {
            $corrigidos++;
            echo "✅ Registro #{$row['codigo']} (sub: {$row['codsubcategoria']}) → codcategoria = {$row['codgrupo']}<br>";
        } else {
            $erros++;
            echo "❌ Erro no registro #{$row['codigo']}: " . mysqli_error($con) . "<br>";
        }
    } else {
        $erros++;
        echo "⚠️ Registro #{$row['codigo']} (sub: {$row['codsubcategoria']}) — subcategoria não encontrada na tabela categoria<br>";
    }
}

echo "<br>🎉 Concluído! <strong>$corrigidos</strong> corrigidos, <strong>$erros</strong> erros.";
?>
