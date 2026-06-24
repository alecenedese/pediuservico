<?php
session_start();
require("send.php");

// Verifica se o usuário está logado
if(!isset($_COOKIE['login'])) {
    echo "<script>alert('Você precisa estar logado!'); window.location.href='login.php';</script>";
    exit;
}

// Busca informações do prestador
$queryEdit = mysqli_query($con, "SELECT * FROM parceiro WHERE CNPJ_CPF='".$_COOKIE['login']."'");
$rowEdit = mysqli_fetch_array($queryEdit);

if(!$rowEdit) {
    echo "<script>alert('Prestador não encontrado!'); window.location.href='login.php';</script>";
    exit;
}

$codcadastro = $rowEdit['id'];

// Verifica se foram enviadas subcategorias
if(isset($_POST['subcategoria']) && is_array($_POST['subcategoria']) && count($_POST['subcategoria']) > 0) {
    
    $subcategorias = $_POST['subcategoria'];
    $sucessos = 0;
    $erros = 0;
    
    foreach($subcategorias as $codsubcategoria) {
        $codsubcategoria = mysqli_real_escape_string($con, $codsubcategoria);
        
        // Busca o codgrupo (codcategoria) da subcategoria
        $queryCodGrupo = mysqli_query($con, "SELECT codgrupo FROM categoria WHERE codigo='$codsubcategoria'");
        $codcategoria = 0;
        if ($queryCodGrupo && $rowGrupo = mysqli_fetch_array($queryCodGrupo)) {
            $codcategoria = $rowGrupo['codgrupo'];
        }
        
        // Verifica se já existe essa categoria para o prestador
        $queryVerifica = mysqli_query($con, "SELECT * FROM categoria_prestador WHERE codcadastro='$codcadastro' AND codsubcategoria='$codsubcategoria'");
        
        if(mysqli_num_rows($queryVerifica) == 0) {
            // Insere a nova categoria com codcategoria
            $queryInsert = mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) VALUES ('$codcadastro', '$codcategoria', '$codsubcategoria')");
            
            if($queryInsert) {
                $sucessos++;
            } else {
                $erros++;
            }
        }
    }
    
    if($sucessos > 0) {
        echo "<script>alert('$sucessos categoria(s) adicionada(s) com sucesso!'); window.location.href='edicao.php';</script>";
    } else if($erros > 0) {
        echo "<script>alert('Erro ao adicionar categorias. Tente novamente.'); window.location.href='edicao.php';</script>";
    } else {
        echo "<script>alert('As categorias selecionadas já estão cadastradas.'); window.location.href='edicao.php';</script>";
    }
    
} else {
    echo "<script>alert('Nenhuma categoria foi selecionada!'); window.location.href='edicao.php';</script>";
}

exit;
?>
