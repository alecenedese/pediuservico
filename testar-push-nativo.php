<?php
// testar-push-nativo.php - Testa o envio de push NATIVO (Expo) para o app instalado
// Uso: testar-push-nativo.php?user_id=123

require_once(__DIR__ . '/send.php');
require_once(__DIR__ . '/api/push-send.php');

header('Content-Type: text/html; charset=utf-8');

$user_id = $_GET['user_id'] ?? '';

// Garante que a tabela push_tokens tenha as colunas necessárias (migração não destrutiva)
$existeTabela = mysqli_query($con, "SHOW TABLES LIKE 'push_tokens'");
if ($existeTabela && mysqli_num_rows($existeTabela) > 0) {
    $colsExist = [];
    $rcM = mysqli_query($con, "SHOW COLUMNS FROM push_tokens");
    if ($rcM) { while ($cM = mysqli_fetch_assoc($rcM)) { $colsExist[] = $cM['Field']; } }
    if (!in_array('user_id', $colsExist))    @mysqli_query($con, "ALTER TABLE push_tokens ADD COLUMN user_id VARCHAR(100) NOT NULL DEFAULT ''");
    if (!in_array('token', $colsExist))      @mysqli_query($con, "ALTER TABLE push_tokens ADD COLUMN token TEXT NOT NULL");
    if (!in_array('platform', $colsExist))   @mysqli_query($con, "ALTER TABLE push_tokens ADD COLUMN platform VARCHAR(20) DEFAULT 'android'");
    if (!in_array('ativo', $colsExist))      @mysqli_query($con, "ALTER TABLE push_tokens ADD COLUMN ativo TINYINT(1) DEFAULT 1");
    if (!in_array('updated_at', $colsExist)) @mysqli_query($con, "ALTER TABLE push_tokens ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
} else {
    @mysqli_query($con, "CREATE TABLE push_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL DEFAULT '',
        token TEXT NOT NULL,
        platform VARCHAR(20) DEFAULT 'android',
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Remove tokens-lixo salvos com user_id não numérico (capturados errado do DOM)
@mysqli_query($con, "DELETE FROM push_tokens WHERE user_id NOT REGEXP '^[0-9]+$'");

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">';
echo '<title>Teste Push Nativo</title>';
echo '<style>body{font-family:Arial;max-width:800px;margin:20px auto;padding:20px;}';
echo 'table{width:100%;border-collapse:collapse;margin:15px 0;}';
echo 'th,td{padding:8px;border:1px solid #ddd;text-align:left;font-size:13px;}';
echo 'th{background:#007AFF;color:#fff;}';
echo '.ok{color:green;font-weight:bold;}.erro{color:red;font-weight:bold;}';
echo 'pre{background:#f4f4f4;padding:12px;border-radius:6px;overflow:auto;}';
echo 'input,button{padding:8px;font-size:14px;}</style></head><body>';
echo '<h1>🔔 Teste de Push Nativo (Expo)</h1>';

// Formulário
echo '<form method="get" style="margin-bottom:20px;">';
echo 'User ID (id_prestador / codcadastro): ';
echo '<input type="text" name="user_id" value="'.htmlspecialchars($user_id).'" placeholder="Ex: 123" required>';
echo '<button type="submit">Testar</button>';
echo '</form>';

// Listar tokens salvos
echo '<h2>📱 Tokens nativos cadastrados</h2>';

// Detecta colunas existentes (schema pode variar)
$colsTok = [];
$rcT = mysqli_query($con, "SHOW COLUMNS FROM push_tokens");
if ($rcT) { while ($cT = mysqli_fetch_assoc($rcT)) { $colsTok[] = $cT['Field']; } }

$temPlatform = in_array('platform', $colsTok);
$temAtivo = in_array('ativo', $colsTok);
$temUpdated = in_array('updated_at', $colsTok);

$selCols = "user_id, LEFT(token, 30) as token_preview";
if ($temPlatform) $selCols .= ", platform";
if ($temAtivo)    $selCols .= ", ativo";
if ($temUpdated)  $selCols .= ", updated_at";
$orderBy = $temUpdated ? " ORDER BY updated_at DESC" : "";

$qTokens = mysqli_query($con, "SELECT $selCols FROM push_tokens$orderBy LIMIT 30");
if ($qTokens && mysqli_num_rows($qTokens) > 0) {
    echo '<table><tr><th>User ID</th><th>Plataforma</th><th>Ativo</th><th>Token (preview)</th><th>Atualizado</th></tr>';
    while ($t = mysqli_fetch_assoc($qTokens)) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars($t['user_id']).'</td>';
        echo '<td>'.htmlspecialchars($temPlatform ? ($t['platform'] ?? '') : '-').'</td>';
        echo '<td>'.($temAtivo ? (($t['ativo'] ?? 1) ? '<span class="ok">Sim</span>' : '<span class="erro">Não</span>') : '-').'</td>';
        echo '<td>'.htmlspecialchars($t['token_preview']).'...</td>';
        echo '<td>'.htmlspecialchars($temUpdated ? ($t['updated_at'] ?? '') : '-').'</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p class="erro">⚠️ Nenhum token nativo cadastrado ainda. O app precisa ser aberto e logado para registrar o token.</p>';
}

// Enviar teste
if (!empty($user_id)) {
    echo '<h2>📤 Enviando push de teste para user_id = '.htmlspecialchars($user_id).'</h2>';
    
    $resultado = enviarExpoPush(
        $con,
        $user_id,
        '🔔 Teste Pediu Serviço',
        'Se você recebeu isso no app instalado, o push NATIVO está funcionando! 🎉',
        '/meus-orcamentos.php',
        ['teste' => true]
    );
    
    echo '<pre>'.htmlspecialchars(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre>';
    
    if (!empty($resultado['success'])) {
        echo '<p class="ok">✅ Push nativo enviado! Verifique o celular com o app instalado.</p>';
    } else {
        echo '<p class="erro">❌ Falha: '.htmlspecialchars($resultado['error'] ?? 'desconhecido').'</p>';
    }
}

// Visualizador do log de tokens recebidos (debug)
echo '<h2>🪵 Log de chamadas recebidas (salvar_token_push)</h2>';
$logTok = __DIR__ . '/log_push_tokens.txt';
if (file_exists($logTok)) {
    $conteudo = file_get_contents($logTok);
    // Mostra só os últimos 3000 caracteres
    $conteudo = substr($conteudo, -3000);
    echo '<pre>'.htmlspecialchars($conteudo).'</pre>';
    echo '<p><a href="?limpar_log=1'.(!empty($user_id) ? '&user_id='.urlencode($user_id) : '').'" class="erro">Limpar log</a></p>';
} else {
    echo '<p class="erro">⚠️ Nenhuma chamada recebida ainda (arquivo de log não existe). Isso significa que o app NÃO está chamando salvar_token_push.php — provavelmente não capturou o user_id ou não obteve o token Expo.</p>';
}

if (isset($_GET['limpar_log']) && file_exists($logTok)) {
    @unlink($logTok);
    echo '<script>window.location.href="testar-push-nativo.php'.(!empty($user_id) ? '?user_id='.urlencode($user_id) : '').'";</script>';
}

echo '</body></html>';
