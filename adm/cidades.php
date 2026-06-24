<?php
header( 'Cache-Control: no-cache' );
header( 'Content-type: application/xml; charset="utf-8"', true );
 
require("send.php");
 
//Recebe o id via ajax 
$id = $_POST["id"];

//faz a busca da cidade, baseada no ID do estado
$buscaCidade = mysqli_query($con, "SELECT Codigo, Nome
FROM cidades
WHERE Uf='".$id."'
ORDER BY Nome");

//itera o while  cada resultado encontrado
while($cidade = mysqli_fetch_object($buscaCidade)):

   echo "<option value='$cidade->Nome'> $cidade->Nome </option>";

endwhile; 