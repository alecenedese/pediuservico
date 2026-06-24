<?php
// Debug de Push Notifications
require_once('../send.php');

echo "<h2>Debug Push Notifications</h2>";

// Verifica se tabela existe
$tableCheck = mysqli_query($con, "SHOW TABLES LIKE 'push_subscriptions'");
if (mysqli_num_rows($tableCheck) == 0) {
    echo "<p style='color:red;'><strong>❌ Tabela push_subscriptions NÃO existe!</strong></p>";
    echo "<p>Acesse: <a href='init-push-tables.php'>init-push-tables.php</a> para criar</p>";
} else {
    echo "<p style='color:green;'><strong>✅ Tabela push_subscriptions existe</strong></p>";
    
    // Lista inscrições
    $query = mysqli_query($con, "SELECT ps.*, p.NOME as nome_prestador 
        FROM push_subscriptions ps 
        LEFT JOIN parceiro p ON p.id = ps.user_id AND ps.user_type = 'prestador'
        ORDER BY ps.created_at DESC");
    
    $total = mysqli_num_rows($query);
    echo "<h3>Total de inscrições: $total</h3>";
    
    if ($total > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
        echo "<tr style='background:#00d4ff;color:#1a2332;'>";
        echo "<th>ID</th><th>User ID</th><th>Tipo</th><th>Nome</th><th>Criado em</th><th>Endpoint (início)</th>";
        echo "</tr>";
        
        while ($row = mysqli_fetch_assoc($query)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['user_type'] . "</td>";
            echo "<td>" . ($row['nome_prestador'] ?? '-') . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . substr($row['endpoint'], 0, 50) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ Nenhuma inscrição encontrada. Os prestadores precisam:</p>";
        echo "<ol>";
        echo "<li>Instalar o PWA no celular</li>";
        echo "<li>Acessar a área logada (edicao.php)</li>";
        echo "<li>Permitir notificações quando solicitado</li>";
        echo "</ol>";
    }
}

// Verifica tabela verification_codes
echo "<hr><h3>Tabela verification_codes:</h3>";
$tableCheck2 = mysqli_query($con, "SHOW TABLES LIKE 'verification_codes'");
if (mysqli_num_rows($tableCheck2) == 0) {
    echo "<p style='color:red;'>❌ Tabela verification_codes NÃO existe</p>";
} else {
    echo "<p style='color:green;'>✅ Tabela verification_codes existe</p>";
}

// Log de push recente
echo "<hr><h3>Log de Push (últimas linhas):</h3>";
$logFile = __DIR__ . '/../log_push.txt';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -30);
    echo "<pre style='background:#1a2332;color:#00d4ff;padding:10px;max-height:300px;overflow:auto;'>";
    echo htmlspecialchars(implode("\n", $lastLines));
    echo "</pre>";
} else {
    echo "<p>Arquivo de log não existe ainda (será criado no primeiro envio)</p>";
}
?>
