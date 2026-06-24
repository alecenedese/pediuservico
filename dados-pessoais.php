<?php
session_start();
require_once("send.php");

// Identifica usuário (cliente ou prestador)
$nome = '';
$celular = '';
$cpfCnpj = '';
$senha = '';
$idUsuario = 0;
$tipoUsuario = '';
$tabela = '';

if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $q = mysqli_query($con, "SELECT id, NOME, CELULAR, CNPJ_CPF, senha FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
    if ($q && $r = mysqli_fetch_assoc($q)) { $idUsuario = $r['id']; $nome = $r['NOME']; $celular = $r['CELULAR']; $cpfCnpj = $r['CNPJ_CPF']; $senha = $r['senha']; $tipoUsuario = 'prestador'; $tabela = 'parceiro'; }
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $id = mysqli_real_escape_string($con, $_COOKIE['id_prestador']);
    $q = mysqli_query($con, "SELECT id, NOME, CELULAR, CNPJ_CPF, senha FROM parceiro WHERE id='$id'");
    if ($q && $r = mysqli_fetch_assoc($q)) { $idUsuario = $r['id']; $nome = $r['NOME']; $celular = $r['CELULAR']; $cpfCnpj = $r['CNPJ_CPF']; $senha = $r['senha']; $tipoUsuario = 'prestador'; $tabela = 'parceiro'; }
} elseif (isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
    $id = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
    $q = mysqli_query($con, "SELECT id, NOME, CELULAR, CNPJ_CPF, senha FROM clientes WHERE id='$id'");
    if ($q && $r = mysqli_fetch_assoc($q)) { $idUsuario = $r['id']; $nome = $r['NOME']; $celular = $r['CELULAR']; $cpfCnpj = $r['CNPJ_CPF']; $senha = $r['senha']; $tipoUsuario = 'cliente'; $tabela = 'clientes'; }
} elseif (isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
    $id = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
    $q = mysqli_query($con, "SELECT id, NOME, CELULAR, CNPJ_CPF, senha FROM clientes WHERE id='$id'");
    if ($q && $r = mysqli_fetch_assoc($q)) { $idUsuario = $r['id']; $nome = $r['NOME']; $celular = $r['CELULAR']; $cpfCnpj = $r['CNPJ_CPF']; $senha = $r['senha']; $tipoUsuario = 'cliente'; $tabela = 'clientes'; }
}

if (!$idUsuario) {
    echo "<script>window.location.href='login-unificado.php?retorno=dados-pessoais.php';</script>";
    exit;
}

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $novoNome = mysqli_real_escape_string($con, $_POST['nome']);
    $novoCelular = mysqli_real_escape_string($con, $_POST['celular']);
    $novaSenha = isset($_POST['senha']) ? mysqli_real_escape_string($con, $_POST['senha']) : '';

    // Atualiza nome e celular (CPF não pode ser alterado)
    if ($novaSenha !== '') {
        mysqli_query($con, "UPDATE $tabela SET NOME='$novoNome', CELULAR='$novoCelular', senha='$novaSenha' WHERE id='$idUsuario'");
        $senha = $novaSenha;
    } else {
        mysqli_query($con, "UPDATE $tabela SET NOME='$novoNome', CELULAR='$novoCelular' WHERE id='$idUsuario'");
    }
    $nome = $novoNome;
    $celular = $novoCelular;
    // Atualiza cookies
    $exp = time() + (30*24*3600);
    setcookie('nome_usuario', $novoNome, $exp, '/');
    setcookie('celular_usuario', $novoCelular, $exp, '/');
    if ($tipoUsuario === 'cliente') { setcookie('nomeCli', $novoNome, $exp, '/'); setcookie('celularCli', $novoCelular, $exp, '/'); }
    $mensagem = 'Dados atualizados com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados Pessoais - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%); background-attachment: fixed; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 70px; }
        .main-content { flex: 1; padding: 16px; max-width: 600px; margin: 0 auto; width: 100%; }
        .page-title { text-align: center; color: #00d4ff; font-size: 22px; font-weight: bold; margin-bottom: 16px; }
        .card { background: rgba(255,255,255,0.95); border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-weight: 600; color: #1a2332; font-size: 14px; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 16px; color: #1a2332; }
        .form-input:focus { outline: none; border-color: #00d4ff; box-shadow: 0 0 0 3px rgba(0,212,255,0.1); }
        .form-input:disabled { background: #f1f3f5; color: #888; cursor: not-allowed; }
        .form-hint { font-size: 11px; color: #888; margin-top: 4px; }
        .btn-salvar { width: 100%; padding: 14px; background: linear-gradient(145deg, #00d4ff, #0ea5e9); color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; }
        .btn-salvar:active { transform: scale(0.99); }
        .msg-sucesso { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); color: #22c55e; padding: 12px; border-radius: 8px; text-align: center; font-weight: 600; margin-bottom: 16px; }
        .tipo-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; margin-bottom: 16px; }
        .tipo-prestador { background: #dbeafe; color: #1d4ed8; }
        .tipo-cliente { background: #dcfce7; color: #166534; }
        .senha-wrap { position: relative; }
        .toggle-senha { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 18px; }
    </style>
</head>
<body>
<?php include('header-app.php'); ?>
<div class="main-content">
    <div class="page-title">👤 Dados Pessoais</div>

    <?php if ($mensagem) { ?>
        <div class="msg-sucesso"><?php echo $mensagem; ?></div>
    <?php } ?>

    <div class="card">
        <span class="tipo-badge tipo-<?php echo $tipoUsuario; ?>"><?php echo ucfirst($tipoUsuario); ?></span>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nome completo</label>
                <input type="text" name="nome" class="form-input" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">CPF / CNPJ</label>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars($cpfCnpj); ?>" disabled>
                <div class="form-hint">O CPF/CNPJ não pode ser alterado.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Celular / WhatsApp</label>
                <input type="text" name="celular" class="form-input" value="<?php echo htmlspecialchars($celular); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Senha</label>
                <div class="senha-wrap">
                    <input type="password" name="senha" id="senhaInput" class="form-input" value="<?php echo htmlspecialchars($senha); ?>" placeholder="Digite para alterar">
                    <button type="button" class="toggle-senha" onclick="toggleSenha()">👁️</button>
                </div>
                <div class="form-hint">Deixe como está para manter a senha atual.</div>
            </div>
            <button type="submit" name="salvar" class="btn-salvar">💾 Salvar Alterações</button>
        </form>
    </div>
</div>
<?php include('bottom-nav.php'); ?>
<script>
function toggleSenha() {
    var inp = document.getElementById('senhaInput');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
