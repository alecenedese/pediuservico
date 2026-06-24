<?php
session_start();
require_once("send.php");

// Se já está logado como cliente, redireciona
if (isset($_COOKIE['celularCli']) || isset($_COOKIE['codcliente'])) {
    echo "<script>window.location.href='meus-orcamentos-cli.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
            overflow-y: auto;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.2);
        }
        .tabs {
            display: flex;
            margin-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 8px 8px 0 0;
            overflow: hidden;
        }
        .tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            color: #9ca3af;
            background: #f8f9fa;
        }
        .tab.active {
            color: #00d4ff;
            border-bottom-color: #00d4ff;
            background: white;
        }
        .tab:hover { color: #00d4ff; background: #f0f9ff; }
        .form-container { text-align: center; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        .user-icon {
            width: 60px; height: 60px;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            color: white; font-size: 24px;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }
        .form-title { font-size: 24px; font-weight: bold; color: #1f2937; margin-bottom: 8px; }
        .form-subtitle { font-size: 14px; color: #6b7280; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; text-align: left; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 12px 16px;
            border: 2px solid #d1d5db; border-radius: 8px;
            font-size: 16px; transition: all 0.3s ease; background: #f9fafb;
        }
        .form-input:focus { outline: none; border-color: #00d4ff; background: white; box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1); }
        .submit-button {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332; border: none; padding: 12px;
            border-radius: 8px; font-size: 18px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }
        .submit-button:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0, 212, 255, 0.4); }
        .form-link { font-size: 14px; color: #6b7280; margin-bottom: 16px; }
        .form-link a { color: #00d4ff; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>

    <div class="main-container">
        <div class="login-card">
            <div class="tabs">
                <div class="tab active" onclick="switchTab('login')">Entrar</div>
                <div class="tab" onclick="switchTab('register')">Cadastrar</div>
            </div>

            <div id="login-form" class="form-section active">
                <div class="form-container">
                    <div class="user-icon">👤</div>
                    <h2 class="form-title">Entrar</h2>
                    <p class="form-subtitle">Digite seu celular para acessar seus pedidos</p>
                    
                    <form action="confirmanumero_cliente_login.php" method="get">
                        <div class="form-group">
                            <label class="form-label">Número de celular</label>
                            <input type="tel" class="form-input" placeholder="(00) 00000-0000" name="celularCli" oninput="mascaraCelular(this)" maxlength="15" required>
                        </div>
                        <button type="submit" class="submit-button">Entrar</button>
                    </form>
                    <div class="form-link">
                        Não tem conta? <a href="#" onclick="switchTab('register')">Cadastre-se</a>
                    </div>
                </div>
            </div>

            <div id="register-form" class="form-section">
                <div class="form-container">
                    <div class="user-icon">👥</div>
                    <h2 class="form-title">Cadastrar</h2>
                    <p class="form-subtitle">Crie sua conta para solicitar serviços</p>
                    
                    <form action="confirmanumero_cliente_login.php" method="get">
                        <input type="hidden" name="cadastro" value="1">
                        <div class="form-group">
                            <label class="form-label">Nome completo</label>
                            <input type="text" class="form-input" placeholder="Seu nome completo" name="nome_completo" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Número de celular</label>
                            <input type="tel" class="form-input" placeholder="(00) 00000-0000" name="celularCli" oninput="mascaraCelular(this)" maxlength="15" required>
                        </div>
                        <button type="submit" class="submit-button">Cadastrar</button>
                    </form>
                    <div class="form-link">
                        Já tem conta? <a href="#" onclick="switchTab('login')">Faça login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mascaraCelular(input) {
            let v = input.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            if (v.length > 6) {
                v = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7);
            } else if (v.length > 2) {
                v = '(' + v.slice(0,2) + ') ' + v.slice(2);
            } else if (v.length > 0) {
                v = '(' + v;
            }
            input.value = v;
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(section => section.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById(tabName + '-form').classList.add('active');
        }
    </script>
</body>
</html>
