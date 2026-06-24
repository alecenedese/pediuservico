<?php
session_start();
require_once("send.php");

$msg = '';
$msgTipo = '';
$senhaRecuperada = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpfCnpjRaw = isset($_POST['cpfCnpj']) ? trim($_POST['cpfCnpj']) : '';
    $cpfCnpjLimpo = preg_replace('/\D/', '', $cpfCnpjRaw);

    if (empty($cpfCnpjLimpo) || (strlen($cpfCnpjLimpo) !== 11 && strlen($cpfCnpjLimpo) !== 14)) {
        $msg = 'Digite um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.';
        $msgTipo = 'erro';
    } else {
        $cpfEsc = mysqli_real_escape_string($con, $cpfCnpjLimpo);
        $cpfFmt = mysqli_real_escape_string($con, $cpfCnpjRaw);
        $encontrou = false;
        $novaSenha = '';

        // Gera nova senha aleatoria (6 digitos)
        $novaSenha = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $novaSenhaEsc = mysqli_real_escape_string($con, $novaSenha);

        // Tenta em parceiro
        $qP = mysqli_query($con, "
            SELECT id, NOME FROM parceiro
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc'
               OR CNPJ_CPF = '$cpfFmt'
            LIMIT 1
        ");
        if ($qP && mysqli_num_rows($qP) > 0) {
            $rP = mysqli_fetch_assoc($qP);
            mysqli_query($con, "UPDATE parceiro SET senha='$novaSenhaEsc' WHERE id='".$rP['id']."'");
            $encontrou = true;
        }

        // Tenta em clientes
        // Garante coluna senha
        $check = mysqli_query($con, "SHOW COLUMNS FROM clientes LIKE 'senha'");
        if (!$check || mysqli_num_rows($check) === 0) {
            @mysqli_query($con, "ALTER TABLE clientes ADD COLUMN senha VARCHAR(100) DEFAULT '' AFTER MUNICIPIO");
        }
        $qC = mysqli_query($con, "
            SELECT id, NOME FROM clientes
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc'
               OR CNPJ_CPF = '$cpfFmt'
            LIMIT 1
        ");
        if ($qC && mysqli_num_rows($qC) > 0) {
            $rC = mysqli_fetch_assoc($qC);
            mysqli_query($con, "UPDATE clientes SET senha='$novaSenhaEsc' WHERE id='".$rC['id']."'");
            $encontrou = true;
        }

        if ($encontrou) {
            $senhaRecuperada = $novaSenha;
            $msg = 'Senha redefinida com sucesso!';
            $msgTipo = 'ok';
        } else {
            $msg = 'CPF/CNPJ não encontrado em nosso sistema.';
            $msgTipo = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container { width:100%; max-width:400px; padding:20px; }
        .logo-area { text-align:center; margin-bottom:30px; }
        .logo-area h1 { color:#00d4ff; font-size:24px; text-shadow:0 0 15px rgba(0,212,255,.3); margin-bottom:8px; }
        .logo-area p { color:rgba(255,255,255,.6); font-size:14px; }
        .card {
            background: rgba(255,255,255,.95);
            border-radius: 16px;
            padding: 30px 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,.3);
            border: 1px solid rgba(0,212,255,.2);
        }
        .card h2 { color:#1a2332; font-size:20px; margin-bottom:6px; text-align:center; }
        .card .subtitle { color:#6b7280; font-size:14px; text-align:center; margin-bottom:24px; }
        .form-group { margin-bottom:16px; }
        .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .form-input {
            width:100%; padding:14px 16px;
            border:2px solid #d1d5db; border-radius:10px;
            font-size:18px; background:#f9fafb;
            transition: all .3s; letter-spacing:1px;
        }
        .form-input:focus { outline:none; border-color:#00d4ff; background:#fff; box-shadow:0 0 0 3px rgba(0,212,255,.1); }
        .btn-primary {
            width:100%; background:linear-gradient(145deg,#00d4ff,#00f0ff);
            color:#1a2332; border:none; padding:14px; border-radius:10px;
            font-size:16px; font-weight:700; cursor:pointer;
            transition:all .3s; box-shadow:0 4px 12px rgba(0,212,255,.3);
        }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,212,255,.4); }
        .btn-primary:disabled { opacity:.6; cursor:not-allowed; transform:none; }
        .msg-box { border-radius:10px; padding:14px; margin-bottom:18px; font-size:14px; text-align:center; }
        .msg-erro { background:rgba(220,53,69,.1); border:1px solid rgba(220,53,69,.3); color:#991b1b; }
        .msg-ok   { background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.3); color:#065f46; }
        .senha-box {
            background:#f0fdfa; border:2px dashed #10b981; border-radius:12px;
            padding:20px; text-align:center; margin:16px 0;
        }
        .senha-box .label { font-size:13px; color:#6b7280; margin-bottom:8px; }
        .senha-box .valor { font-size:32px; font-weight:800; color:#1a2332; letter-spacing:6px; font-family:monospace; }
        .senha-box .aviso { font-size:12px; color:#dc3545; margin-top:10px; font-weight:600; }
        .back-link { text-align:center; margin-top:16px; }
        .back-link a { color:#00d4ff; text-decoration:none; font-weight:600; font-size:14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="logo-area">
        <h1>Pediu Serviço</h1>
        <p>Recuperação de senha</p>
    </div>

    <div class="card">
        <h2>Recuperar Senha</h2>
        <p class="subtitle">Informe seu CPF ou CNPJ cadastrado</p>

        <?php if ($msg && $msgTipo === 'erro'): ?>
            <div class="msg-box msg-erro"><strong><?php echo $msg; ?></strong></div>
        <?php endif; ?>

        <?php if ($msgTipo === 'ok'): ?>
            <div class="msg-box msg-ok"><strong><?php echo $msg; ?></strong></div>
            <div class="senha-box">
                <div class="label">Sua nova senha é:</div>
                <div class="valor"><?php echo $senhaRecuperada; ?></div>
                <div class="aviso">Anote esta senha! Você pode alterá-la depois no seu cadastro.</div>
            </div>
            <a href="login-unificado.php" class="btn-primary" style="display:block;text-align:center;text-decoration:none;margin-top:12px;">
                Ir para o Login
            </a>
        <?php else: ?>
            <form method="POST" id="formRecuperar">
                <div class="form-group">
                    <label class="form-label">CPF ou CNPJ</label>
                    <input
                        type="text"
                        class="form-input"
                        id="cpfCnpj"
                        name="cpfCnpj"
                        placeholder="000.000.000-00"
                        required
                        maxlength="18"
                        autocomplete="off"
                    >
                </div>

                <button type="submit" class="btn-primary" id="btnRecuperar">
                    Recuperar Senha
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login-unificado.php">← Voltar ao Login</a>
        </div>
    </div>
</div>

<script>
document.getElementById('cpfCnpj') && document.getElementById('cpfCnpj').addEventListener('input', function() {
    var v = this.value.replace(/\D/g, '');
    if (v.length > 14) v = v.substring(0, 14);
    if (v.length <= 11) {
        if (v.length > 9)      v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2}).*/, '$1.$2.$3-$4');
        else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3}).*/, '$1.$2.$3');
        else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3}).*/, '$1.$2');
    } else {
        v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2}).*/, '$1.$2.$3/$4-$5');
    }
    this.value = v;
});

var form = document.getElementById('formRecuperar');
if (form) {
    form.addEventListener('submit', function(e) {
        var v = document.getElementById('cpfCnpj').value.replace(/\D/g, '');
        if (v.length !== 11 && v.length !== 14) {
            e.preventDefault();
            alert('Digite um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.');
            return false;
        }
        document.getElementById('btnRecuperar').disabled = true;
        document.getElementById('btnRecuperar').textContent = 'Verificando...';
    });
}
</script>

</body>
</html>
