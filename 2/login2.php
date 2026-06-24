<?php 
ob_start();
require("send.php");
if(!isset($_COOKIE['login'])) {

  } else { 
    echo "<script>window.location.href='edicao.php';</script>";
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 13px;
            overflow: hidden;
        }
                .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 13px;
        }

        .logo {
            font-size: 19px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .step-indicator {
            color: #00d4ff;
            font-size: 10px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* adicionando estilo do botão voltar igual opcoes2.html */
        .back-btn {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
            background: linear-gradient(145deg, #00f0ff, #40ffff);
        }

        /* Login card container with USERVICE styling */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.2);
            margin-top: 20px;
        }

        /* Logo styling */
        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
            letter-spacing: 2px;
        }

        .logo-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 16px;
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        /* Password field with toggle */
        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 8px;
        }

        .password-toggle:hover {
            color: #00d4ff;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        /* Links styling */
        .form-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .forgot-password {
            color: #00d4ff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* Login button */
        .login-button {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
        }

        /* Create account link */
        .create-account {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(0, 212, 255, 0.2);
        }

        .create-account p {
            color: #666;
            font-size: 14px;
        }

        .create-account a {
            color: #00d4ff;
            text-decoration: none;
            font-weight: 600;
        }

        .create-account a:hover {
            text-decoration: underline;
        }

        /* Error message styling */
        .error-message {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 14px;
        }

        .error-message a {
            color: #00d4ff;
            text-decoration: underline;
            font-weight: 600;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            // CPF AND CNPJ MASK
            var CpfCnpjMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
            },
            cpfCnpjpOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(CpfCnpjMaskBehavior.apply({}, arguments), options);
                }
            };
            $('.cpf_cnpj').mask(CpfCnpjMaskBehavior, cpfCnpjpOptions);
        });
    </script>
</head>
<body>
        <div class="header">
        <div class="logo">USERVICE</div>
        <!-- substituindo step-indicator por botão voltar igual opcoes2.html -->
        <button class="back-btn" onclick="window.history.back()">← Voltar</button>
    </div>

    <div class="login-card">
        <!-- Logo with USERVICE branding -->

        <!-- Error message with USERVICE styling -->
        <?php if(isset($_GET['msg'])) { ?>  
            <div class="error-message">
                <strong>Usuário ou senha incorretos</strong><br>
                Caso não tenha cadastro, <a href="<?php echo $urlserver; ?>cadastro.php">clique aqui para criar sua conta</a>
            </div>
        <?php } ?>
        
        <!-- Login form with vertical mobile layout -->
        <form action="<?php echo $urlserver; ?>logar.php" method="POST">
            <div class="form-group">
                <label for="cpf-cnpj" class="form-label">CPF/CNPJ</label>
                <input 
                    type="text" 
                    id="cpf-cnpj" 
                    class="form-input cpf_cnpj" 
                    placeholder="Digite apenas números"
                    name="cpfCnpj"
                    required
                >
            </div>
            
            <div class="form-group">
                <div class="form-links">
                    <label for="password" class="form-label">Senha</label>
                    <a href="<?php echo $urlserver; ?>recuperar-senha.php" class="forgot-password">Esqueceu a senha?</a>
                </div>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="senha2"
                        class="form-input" 
                        placeholder="••••••••••"
                        required
                    >
                    <button type="button" id="toggle-password" class="password-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="login-button">
                Fazer Login
            </button>
            
            <div class="create-account">
                <p>
                    Não tem cadastro? <a href="<?php echo $urlserver; ?>cadastro.php" style="font-weight: bold;">Criar Conta</a>
                </p>
            </div>
        </form>
    </div>
    
    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                `;
            } else {
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                `;
            }
        });
    </script>
</body>
</html>
