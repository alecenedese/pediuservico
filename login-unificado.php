<?php
session_start();
require_once("send.php");

// Captura retorno antes de qualquer redirect
$retorno = isset($_GET['retorno']) ? $_GET['retorno'] : '';
$fromServico = isset($_GET['from']) && $_GET['from'] === 'servico';

// Se ja esta logado pelo NOVO metodo, redireciona
if (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') {
    if (!empty($retorno)) {
        echo "<script>window.location.href='".htmlspecialchars($retorno, ENT_QUOTES)."';</script>";
    } elseif (isset($_COOKIE['eh_prestador']) && $_COOKIE['eh_prestador'] == '1') {
        echo "<script>window.location.href='meus-orcamentos.php';</script>";
    } else {
        echo "<script>window.location.href='buscar.php';</script>";
    }
    exit;
}

// Se chegou aqui com cookies antigos (celular_usuario, etc.), limpa-os
$cookiesLegado = ['celular_usuario','nome_usuario','eh_cliente','eh_prestador','celularCli','codcliente','nomeCli','id_cliente','id_prestador'];
foreach ($cookiesLegado as $c) {
    if (isset($_COOKIE[$c])) { unset($_COOKIE[$c]); setcookie($c, '', time()-3600, '/'); }
}

$msgErro = isset($_GET['erro']) ? $_GET['erro'] : '';
$forcouRelogin = isset($_COOKIE['forcou_relogin']) && $_COOKIE['forcou_relogin'] === '1';
if ($forcouRelogin) { setcookie('forcou_relogin', '', time()-3600, '/'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-area h1 {
            color: #00d4ff;
            font-size: 24px;
            text-shadow: 0 0 15px rgba(0,212,255,0.3);
            margin-bottom: 8px;
        }

        .logo-area p {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .login-card h2 {
            color: #1a2332;
            font-size: 20px;
            margin-bottom: 6px;
            text-align: center;
        }

        .login-card .subtitle {
            color: #6b7280;
            font-size: 14px;
            text-align: center;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #d1d5db;
            border-radius: 10px;
            font-size: 18px;
            background: #f9fafb;
            transition: all 0.3s;
            letter-spacing: 1px;
        }

        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            background: white;
            box-shadow: 0 0 0 3px rgba(0,212,255,0.1);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,212,255,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,212,255,0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            margin: 20px 0;
            position: relative;
        }

        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #e5e7eb;
        }

        .divider::before { left: 0; }
        .divider::after { right: 0; }

        .register-link {
            text-align: center;
            margin-top: 16px;
        }

        .register-link a {
            color: #00d4ff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .info-box {
            background: rgba(0,212,255,0.08);
            border: 1px solid rgba(0,212,255,0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #374151;
            text-align: center;
        }

        .info-box strong { color: #00d4ff; }
    </style>
</head>
<body>
<?php include('topo2.php'); ?>

<div class="login-container">
    <div class="logo-area">
        <h1>Pediu Serviço</h1>
        <p>Encontre e ofereça serviços</p>
    </div>

    <div class="login-card">
        <h2>Entrar</h2>
        <p class="subtitle">Use seu CPF ou CNPJ e senha</p>

        <?php if ($forcouRelogin): ?>
        <div class="info-box" style="background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.35);color:#92400e;">
            <strong>Atualizamos o sistema de login.</strong><br>
            Por favor, entre novamente com seu CPF/CNPJ e senha.
        </div>
        <?php elseif ($msgErro === 'credenciais'): ?>
        <div class="info-box" style="background:rgba(220,53,69,.1);border-color:rgba(220,53,69,.3);color:#991b1b;">
            <strong>CPF/CNPJ ou senha incorretos.</strong>
        </div>
        <?php else: ?>
        <div class="info-box">
            <strong>Uma conta para tudo!</strong><br>
            Peça serviços e ofereça seus serviços no mesmo lugar.
        </div>
        <?php endif; ?>

        <form id="loginForm" action="processar-login-unificado.php<?php echo !empty($retorno) ? '?retorno='.urlencode($retorno) : ''; ?>" method="POST">
            <?php if (!empty($retorno)): ?>
            <input type="hidden" name="retorno" value="<?php echo htmlspecialchars($retorno, ENT_QUOTES); ?>">
            <?php endif; ?>
            <?php if ($fromServico): ?>
            <input type="hidden" name="from_servico" value="1">
            <?php endif; ?>
            <div class="form-group">
                <label class="form-label">CPF ou CNPJ</label>
                <input
                    type="tel"
                    inputmode="numeric"
                    class="form-input"
                    id="cpfCnpj"
                    name="cpfCnpj"
                    placeholder="000.000.000-00"
                    required
                    maxlength="18"
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Senha</label>
                <input
                    type="password"
                    class="form-input"
                    id="senha"
                    name="senha"
                    placeholder="Sua senha"
                    required
                    minlength="3"
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-primary" id="btnEnviar">
                Entrar
            </button>
        </form>

        <div style="text-align:center;margin-top:12px;">
            <a href="recuperar-senha.php" style="color:#6b7280;font-size:13px;text-decoration:none;">Esqueceu a senha?</a>
        </div>

        <div class="divider">ou</div>

        <div class="register-link">
            <a href="cadastro-unificado.php<?php echo !empty($retorno) ? '?retorno='.urlencode($retorno) : ''; ?>">Criar uma nova conta</a>
        </div>
    </div>
</div>

<script>
// Mascara dinamica CPF/CNPJ
document.getElementById('cpfCnpj').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length > 14) v = v.substring(0, 14);
    if (v.length <= 11) {
        // CPF: 000.000.000-00
        if (v.length > 9)      v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2}).*/, '$1.$2.$3-$4');
        else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3}).*/, '$1.$2.$3');
        else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3}).*/, '$1.$2');
    } else {
        // CNPJ: 00.000.000/0000-00
        v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2}).*/, '$1.$2.$3/$4-$5');
    }
    this.value = v;
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    var v = document.getElementById('cpfCnpj').value.replace(/\D/g, '');
    if (v.length !== 11 && v.length !== 14) {
        e.preventDefault();
        alert('Digite um CPF (11 digitos) ou CNPJ (14 digitos) valido.');
        return false;
    }
    document.getElementById('btnEnviar').disabled = true;
    document.getElementById('btnEnviar').textContent = 'Entrando...';
});
</script>

</body>
</html>
