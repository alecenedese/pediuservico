<?php
session_start();
require_once("send.php");

// Captura retorno
$retorno = isset($_GET['retorno']) ? $_GET['retorno'] : '';

// Item 8: detecta se o cadastro foi iniciado pela "Área dos Prestadores"
$comoPrestador = (isset($_GET['comoprestador']) && $_GET['comoprestador'] == '1')
    || (strpos($retorno, 'meus-orcamentos') !== false)
    || (strpos($retorno, 'tornar-prestador') !== false);

// Se ja esta logado pelo novo metodo, redireciona
if (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') {
    $dest = !empty($retorno) ? $retorno : 'buscar.php';
    echo "<script>window.location.href='".htmlspecialchars($dest, ENT_QUOTES)."';</script>";
    exit;
}

$msgErro = isset($_GET['erro']) ? $_GET['erro'] : '';
$voltarLogin = 'login-unificado.php' . (!empty($retorno) ? '?retorno='.urlencode($retorno) : ($comoPrestador ? '?comoprestador=1' : ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(135deg,#1a2332 0%,#2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .container { width:100%; max-width:440px; padding:20px; }
        .logo-area { text-align:center; margin-bottom:24px; }
        .logo-area h1 { color:#00d4ff; font-size:24px; text-shadow:0 0 15px rgba(0,212,255,.3); margin-bottom:6px; }
        .logo-area p { color:rgba(255,255,255,.6); font-size:14px; }

        .card {
            background: rgba(255,255,255,.95);
            border-radius: 16px;
            padding: 28px 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,.3);
            border: 1px solid rgba(0,212,255,.2);
        }
        .card h2 { color:#1a2332; font-size:20px; margin-bottom:6px; text-align:center; }
        .card .subtitle { color:#6b7280; font-size:14px; text-align:center; margin-bottom:20px; }

        .tipo-selector {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            padding: 4px;
        }
        .tipo-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            transition: all .2s;
        }
        .tipo-option.active {
            background: #00d4ff;
            color: #1a2332;
            box-shadow: 0 2px 6px rgba(0,212,255,.3);
        }
        input[name="tipo"] { display:none; }

        .form-group { margin-bottom: 14px; }
        .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:5px; }
        .form-input {
            width: 100%;
            padding: 13px 14px;
            border: 2px solid #d1d5db;
            border-radius: 10px;
            font-size: 16px;
            background: #f9fafb;
            transition: all .25s;
        }
        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,212,255,.1);
        }
        .pass-hint { font-size:12px; color:#dc3545; margin-top:5px; min-height:16px; }
        .pass-hint.ok { color:#16a34a; }

        .btn-primary {
            width: 100%;
            background: linear-gradient(145deg,#00d4ff,#00f0ff);
            color: #1a2332;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .25s;
            box-shadow: 0 4px 12px rgba(0,212,255,.3);
            margin-top: 6px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,212,255,.4); }
        .btn-primary:disabled { opacity:.6; cursor:not-allowed; transform:none; }

        .login-link { text-align:center; margin-top:16px; font-size:14px; color:#6b7280; }
        .login-link a { color:#00d4ff; text-decoration:none; font-weight:600; }

        .alert {
            background: rgba(220,53,69,.1);
            border: 1px solid rgba(220,53,69,.3);
            color: #991b1b;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
            text-align: center;
        }
        .btn-voltar-cad {
            position: fixed;
            top: 14px;
            left: 14px;
            z-index: 100;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            backdrop-filter: blur(6px);
        }
        .banner-prestador {
            background: rgba(0,212,255,.12);
            border: 1px solid rgba(0,212,255,.35);
            color: #0c4a6e;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
            text-align: center;
        }
        .banner-prestador strong { color: #0ea5e9; }
    </style>
</head>
<body>

<a href="<?php echo htmlspecialchars($voltarLogin, ENT_QUOTES); ?>" class="btn-voltar-cad">← Voltar</a>

<div class="container">
    <div class="logo-area">
        <h1>Pediu Serviço</h1>
        <p>Crie sua conta em poucos segundos</p>
    </div>

    <div class="card">
        <h2>Criar Conta</h2>
        <p class="subtitle">É rápido e gratuito</p>

        <?php if ($comoPrestador): ?>
            <div class="banner-prestador">🛠️ Você está criando uma conta para <strong>oferecer serviços (Prestador)</strong>.<br>Depois de criar, você vai escolher suas categorias.</div>
        <?php endif; ?>

        <?php if ($msgErro === 'duplicado'): ?>
            <div class="alert"><strong>Este CPF/CNPJ já está cadastrado.</strong><br>Use a tela de login.</div>
        <?php elseif ($msgErro === 'invalido'): ?>
            <div class="alert">Dados inválidos. Verifique e tente novamente.</div>
        <?php elseif ($msgErro === 'senha'): ?>
            <div class="alert">As senhas não conferem.</div>
        <?php elseif ($msgErro === 'banco'): ?>
            <div class="alert">Erro ao salvar cadastro. Tente novamente.</div>
        <?php endif; ?>

        <form id="formCadastro" action="processar-cadastro-unificado.php" method="POST">
            <?php if (!empty($retorno)): ?>
            <input type="hidden" name="retorno" value="<?php echo htmlspecialchars($retorno, ENT_QUOTES); ?>">
            <?php endif; ?>
            <input type="hidden" name="comoprestador" value="<?php echo $comoPrestador ? '1' : '0'; ?>">
            <input type="hidden" name="tipo" id="tipo" value="F">

            <div class="tipo-selector">
                <div class="tipo-option active" data-tipo="F" onclick="setTipo('F')">Pessoa Física</div>
                <div class="tipo-option" data-tipo="J" onclick="setTipo('J')">Pessoa Jurídica</div>
            </div>

            <div class="form-group">
                <label class="form-label" id="lblDoc">CPF</label>
                <input type="text" class="form-input" id="cpfCnpj" name="cpfCnpj"
                       placeholder="000.000.000-00" required maxlength="18" autocomplete="off">
            </div>

            <div class="form-group">
                <label class="form-label" id="lblNome">Nome Completo</label>
                <input type="text" class="form-input" id="nome" name="nome"
                       placeholder="Seu nome" required maxlength="100" autocomplete="name">
            </div>

            <div class="form-group">
                <label class="form-label">WhatsApp (com DDD)</label>
                <input type="tel" class="form-input" id="whatsapp" name="whatsapp"
                       placeholder="(66) 99999-9999" required maxlength="15" autocomplete="tel">
            </div>

            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" class="form-input" id="senha" name="senha"
                       placeholder="Mínimo 4 caracteres" required minlength="4" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Senha</label>
                <input type="password" class="form-input" id="senha2" name="senha2"
                       placeholder="Repita a senha" required minlength="4" autocomplete="new-password"
                       oninput="validarSenhas()">
                <div id="passHint" class="pass-hint"></div>
            </div>

            <button type="submit" class="btn-primary" id="btnEnviar">Criar Conta</button>
        </form>

        <div class="login-link">
            Já tem conta? <a href="login-unificado.php<?php echo !empty($retorno) ? '?retorno='.urlencode($retorno) : ''; ?>">Entrar</a>
        </div>
    </div>
</div>

<script>
function setTipo(t) {
    document.getElementById('tipo').value = t;
    document.querySelectorAll('.tipo-option').forEach(el => {
        el.classList.toggle('active', el.dataset.tipo === t);
    });
    var inp = document.getElementById('cpfCnpj');
    var lbl = document.getElementById('lblDoc');
    var lblNome = document.getElementById('lblNome');
    if (t === 'F') {
        lbl.textContent = 'CPF';
        lblNome.textContent = 'Nome Completo';
        inp.placeholder = '000.000.000-00';
        inp.maxLength = 14;
    } else {
        lbl.textContent = 'CNPJ';
        lblNome.textContent = 'Razão Social';
        inp.placeholder = '00.000.000/0000-00';
        inp.maxLength = 18;
    }
    inp.value = '';
}

document.getElementById('cpfCnpj').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    var t = document.getElementById('tipo').value;
    if (t === 'F') {
        if (v.length > 11) v = v.substring(0,11);
        if (v.length > 9)      v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2}).*/, '$1.$2.$3-$4');
        else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3}).*/, '$1.$2.$3');
        else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3}).*/, '$1.$2');
    } else {
        if (v.length > 14) v = v.substring(0,14);
        if (v.length > 12)      v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2}).*/, '$1.$2.$3/$4-$5');
        else if (v.length > 8)  v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4}).*/, '$1.$2.$3/$4');
        else if (v.length > 5)  v = v.replace(/(\d{2})(\d{3})(\d{1,3}).*/, '$1.$2.$3');
        else if (v.length > 2)  v = v.replace(/(\d{2})(\d{1,3}).*/, '$1.$2');
    }
    this.value = v;
});

document.getElementById('whatsapp').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length > 11) v = v.substring(0,11);
    if (v.length > 7) v = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
    else if (v.length > 2) v = '(' + v.substring(0,2) + ') ' + v.substring(2);
    else if (v.length > 0) v = '(' + v;
    this.value = v;
});

function validarSenhas() {
    var s1 = document.getElementById('senha').value;
    var s2 = document.getElementById('senha2').value;
    var hint = document.getElementById('passHint');
    if (!s2) { hint.textContent = ''; hint.classList.remove('ok'); return; }
    if (s1 === s2) {
        hint.textContent = '✓ Senhas conferem';
        hint.classList.add('ok');
    } else {
        hint.textContent = '✗ As senhas não conferem';
        hint.classList.remove('ok');
    }
}

document.getElementById('formCadastro').addEventListener('submit', function(e) {
    var doc = document.getElementById('cpfCnpj').value.replace(/\D/g,'');
    var t = document.getElementById('tipo').value;
    var s1 = document.getElementById('senha').value;
    var s2 = document.getElementById('senha2').value;
    var wa = document.getElementById('whatsapp').value.replace(/\D/g,'');

    if (t === 'F' && doc.length !== 11) { e.preventDefault(); alert('CPF inválido (precisa ter 11 dígitos).'); return; }
    if (t === 'J' && doc.length !== 14) { e.preventDefault(); alert('CNPJ inválido (precisa ter 14 dígitos).'); return; }
    if (wa.length < 10) { e.preventDefault(); alert('WhatsApp inválido. Inclua o DDD.'); return; }
    if (s1 !== s2) { e.preventDefault(); alert('As senhas não conferem.'); return; }

    document.getElementById('btnEnviar').disabled = true;
    document.getElementById('btnEnviar').textContent = 'Criando conta...';
});
</script>

</body>
</html>
