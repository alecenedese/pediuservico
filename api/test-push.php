<?php
// Teste de Push Notification
require_once('../send.php');

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Teste de Push Notification</h2>";

// 1. Verifica tabela push_subscriptions
echo "<h3>1. Inscricoes Push no Banco</h3>";
$query = mysqli_query($con, "SELECT * FROM push_subscriptions ORDER BY created_at DESC LIMIT 10");
if (!$query) {
    echo "<p style='color:red'>Erro: " . mysqli_error($con) . "</p>";
    echo "<p>Tabela nao existe? <a href='init-push-tables.php'>Criar tabelas</a></p>";
} else {
    $total = mysqli_num_rows($query);
    echo "<p>Total de inscricoes: <strong>$total</strong></p>";
    
    if ($total > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;font-size:12px'>";
        echo "<tr style='background:#00d4ff'><th>ID</th><th>User ID</th><th>Tipo</th><th>Endpoint (inicio)</th><th>Criado</th></tr>";
        while ($row = mysqli_fetch_assoc($query)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['user_type'] . "</td>";
            echo "<td>" . substr($row['endpoint'], 0, 60) . "...</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>Nenhuma inscricao encontrada!</p>";
        echo "<p>Os prestadores precisam:</p>";
        echo "<ol>";
        echo "<li>Acessar o site pelo navegador Chrome</li>";
        echo "<li>Permitir notificacoes quando solicitado</li>";
        echo "<li>Estar logados como prestador</li>";
        echo "</ol>";
    }
}

// 2. Verifica log de push
echo "<h3>2. Log de Push (ultimas linhas)</h3>";
$logFile = __DIR__ . '/../log_push.txt';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -50);
    echo "<pre style='background:#1a2332;color:#00d4ff;padding:10px;max-height:400px;overflow:auto;font-size:11px'>";
    echo htmlspecialchars(implode("\n", $lastLines));
    echo "</pre>";
} else {
    echo "<p>Arquivo de log nao existe ainda</p>";
}

// 3. Teste de envio manual
echo "<h3>3. Teste de Envio Manual</h3>";
if (isset($_GET['testar']) && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    require_once('push-send.php');
    
    echo "<p>Enviando push para user_id: $userId...</p>";
    $result = enviarPushNotification($con, $userId, 'prestador', 'Teste Push', 'Esta e uma notificacao de teste!', '/painel.php');
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}

// Lista prestadores para teste
$queryPrest = mysqli_query($con, "SELECT ps.user_id, p.NOME FROM push_subscriptions ps LEFT JOIN parceiro p ON p.id = ps.user_id WHERE ps.user_type = 'prestador' LIMIT 5");
if ($queryPrest && mysqli_num_rows($queryPrest) > 0) {
    echo "<p>Prestadores com inscricao push:</p>";
    while ($p = mysqli_fetch_assoc($queryPrest)) {
        echo "<a href='?testar=1&user_id=" . $p['user_id'] . "' style='display:inline-block;margin:5px;padding:8px 15px;background:#00d4ff;color:#1a2332;text-decoration:none;border-radius:5px'>Testar: " . ($p['NOME'] ?? 'ID ' . $p['user_id']) . "</a> ";
    }
}

echo "<hr><p><a href='../buscar.php'>Voltar ao app</a></p>";
?>
