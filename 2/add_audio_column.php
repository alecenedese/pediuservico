<?php
// Script para adicionar coluna audio na tabela pedido
// Execute uma vez e depois pode deletar este arquivo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

try {
    require("send.php");
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

if (!isset($con) || $con->connect_errno) {
    die("Erro de conexão com o banco de dados.");
}

// Verificar se a coluna já existe
$checkCol = mysqli_query($con, "SHOW COLUMNS FROM pedido LIKE 'audio'");
if ($checkCol && mysqli_num_rows($checkCol) > 0) {
    echo "✅ Coluna 'audio' já existe na tabela 'pedido'.<br>";
} else {
    $sql = "ALTER TABLE pedido ADD COLUMN audio VARCHAR(255) NULL DEFAULT NULL";
    if (mysqli_query($con, $sql)) {
        echo "✅ Coluna 'audio' adicionada com sucesso na tabela 'pedido'!<br>";
    } else {
        echo "❌ Erro ao adicionar coluna: " . mysqli_error($con) . "<br>";
    }
}

// Criar diretório de áudios se não existir
$audioDir = __DIR__ . '/audios/';
if (!is_dir($audioDir)) {
    if (mkdir($audioDir, 0777, true)) {
        echo "✅ Diretório 'audios/' criado com sucesso!<br>";
    } else {
        echo "❌ Erro ao criar diretório 'audios/'.<br>";
    }
} else {
    echo "✅ Diretório 'audios/' já existe.<br>";
}

echo "<br>🎉 Migração concluída!";
?>
