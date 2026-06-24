<?php 
 header("Content-Type: text/html; charset=utf-8",true);


    $queryEnvio = mysqli_query($con, "INSERT INTO categoria (codgrupo, titulo) VALUES
	('$codgrupo', '$titulo')") or die(mysqli_error());

    header('location: https://gessomt.app.br/pediuservico/maoamiga/categorias');

?>