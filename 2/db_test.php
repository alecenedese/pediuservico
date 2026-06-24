<?php
require 'adm2/send.php';
$res = mysqli_query($con, 'SHOW CREATE TABLE subcategoria');
$row = mysqli_fetch_assoc($res);
echo "SUBCATEGORIA:\n" . $row['Create Table'] . "\n\n";

$res2 = mysqli_query($con, 'SHOW CREATE TABLE categoria');
$row2 = mysqli_fetch_assoc($res2);
echo "CATEGORIA:\n" . $row2['Create Table'] . "\n\n";
