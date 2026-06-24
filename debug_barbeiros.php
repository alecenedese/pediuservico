<?php
require_once("send.php");

echo "<h2>Debug Barbeiros - Subcategoria 457</h2>";

// Conta total na categoria_prestador
$result = mysqli_query($con, "SELECT COUNT(*) as total FROM categoria_prestador WHERE codsubcategoria='457'");
$row = mysqli_fetch_array($result);
echo "<p><strong>Total na categoria_prestador:</strong> " . $row['total'] . "</p>";

// Lista todos os prestadores dessa categoria
echo "<h3>Prestadores cadastrados:</h3>";
$lista = mysqli_query($con, "SELECT cp.codcadastro, p.NOME, p.CELULAR 
    FROM categoria_prestador cp 
    JOIN parceiro p ON p.id = cp.codcadastro 
    WHERE cp.codsubcategoria='457'");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>#</th><th>ID</th><th>Nome</th><th>Celular</th></tr>";
$i = 1;
while($r = mysqli_fetch_array($lista)) {
    echo "<tr>";
    echo "<td>" . $i++ . "</td>";
    echo "<td>" . $r['codcadastro'] . "</td>";
    echo "<td>" . $r['NOME'] . "</td>";
    echo "<td>" . $r['CELULAR'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Verifica a categoria
echo "<h3>Info da Categoria:</h3>";
$cat = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM categoria WHERE codigo='457'"));
echo "<pre>";
print_r($cat);
echo "</pre>";
?>
