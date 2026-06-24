<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'adm2/send.php';

echo "<h2>Tabela SUBCATEGORIA</h2><pre>";
$res = mysqli_query($con, 'SHOW CREATE TABLE subcategoria');
if($res && $row = mysqli_fetch_assoc($res)) {
    echo htmlspecialchars($row['Create Table']);
} else {
    echo mysqli_error($con);
}
echo "</pre>";

echo "<h2>Tabela CATEGORIA</h2><pre>";
$res2 = mysqli_query($con, 'SHOW CREATE TABLE categoria');
if($res2 && $row2 = mysqli_fetch_assoc($res2)) {
    echo htmlspecialchars($row2['Create Table']);
} else {
    echo mysqli_error($con);
}
echo "</pre>";
?>
