<?php
require_once ("send.php");

if(isset($_GET['celularCli2'])) {
    $celular = $_GET['celularCli2'];
} else {
    $celular = $_GET['celularCli'];}

$cpfCnpj = $_GET['cpfCnpj'];
function limpar_texto($str){ 
    return preg_replace("/[^0-9]/", "", $str); 
    }

    $numerolimpo = limpar_texto($celular);

    if(isset($_GET['cadastro'])) {

     
        $queryUsuario = mysqli_query($con, "SELECT * FROM clientes WHERE CELULAR='".$celular."'");
        $totalUsuario = mysqli_num_rows($queryUsuario);

        if($totalUsuario > 0) { 

         echo "<script>alert('Já Existe um Cadastro Com este Número.'); window.location.href='pegar_contato.php?nome=$nome&codpedido=$codpedido&codcadastro=$codcadastro&codcliente=$id';</script>";

        } else {
            $nomeCli = $_GET['nome_completo'];

            $dataCad = date("Y-m-d");
            $queryEnvio = mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad) VALUES
            ('', '$nomeCli', '', '', '$celular', 'MT', 'Sinop', '$dataCad')") or die(mysqli_error($con));

            $contaUltimo = mysqli_fetch_array(mysqli_query($con, "SELECT max(x.id) FROM clientes x")) or die(mysqli_error($con));

            // user chat
            $queryEnviochat = mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) VALUES
                    ('".$contaUltimo[0]."', '$nomeCli', '$nomeCli', '', 'user-default.png', '', '$celular')") or die(mysqli_error($con));

            $id = $contaUltimo[0];       

            $queryUsuario = mysqli_query($con, "SELECT * FROM clientes WHERE CELULAR='".$celular."'");
            $totalUsuario = mysqli_num_rows($queryUsuario);
            $selecionaUsuario = mysqli_fetch_object($queryUsuario);
            $nomeUsuario = $selecionaUsuario->NOME;
            $id = $selecionaUsuario->id;

            $numero = 20;
            $min = 5;
            $max = 10000;
            $gera = rand($min,$max);
            $enviarnumero = '0'.$gera; 
    
            $numerolimpo = preg_replace('/\D/', '', $numerolimpo);
            
            // Salva o código no banco para validação (exibido na tela)
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            mysqli_query($con, "DELETE FROM verification_codes WHERE celular='$numerolimpo' AND usado=0");
            mysqli_query($con, "INSERT INTO verification_codes (celular, codigo, tipo, expires_at) VALUES ('$numerolimpo', '$enviarnumero', 'pedido', '$expiresAt')");

        }   

    } else {

        $queryPrestador = mysqli_query($con, "SELECT * FROM parceiro WHERE CELULAR='".$celular."'");
        $totalPrestador = mysqli_num_rows($queryPrestador);
        $selecionaPrestador = mysqli_fetch_object($queryPrestador);
        $nomeCli = $selecionaPrestador->NOME;
        $CNPJ_CPF = $selecionaPrestador->CNPJ_CPF;

        $queryUsuario = mysqli_query($con, "SELECT * FROM clientes WHERE CELULAR='".$celular."'");
        $totalUsuario = mysqli_num_rows($queryUsuario);
        $selecionaUsuario = mysqli_fetch_object($queryUsuario);
        $nomeUsuario = $selecionaUsuario->NOME;
        $id = $selecionaUsuario->id;

        if($totalUsuario == 0 && $totalPrestador > 0) {

            $dataCad = date("Y-m-d");
            $queryEnvio = mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad) VALUES
            ('', '$nomeCli', '$CNPJ_CPF', '', '$celular', 'MT', 'Sinop', '$dataCad')") or die(mysqli_error($con));
    
            $contaUltimo = mysqli_fetch_array(mysqli_query($con, "SELECT max(x.id) FROM clientes x")) or die(mysqli_error($con));
            //cadastro do login vinculo do pedido
            $queryEnvioLogin = mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) VALUES
                ('".$nomeCli."', '".$celular."', '".$_GET['codpedido']."', '".$_GET['codcadastro']."', '".$contaUltimo[0]."', 'sim')") or die(mysqli_error($con));

            // user chat
            $queryEnviochat = mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) VALUES
                    ('".$contaUltimo[0]."', '$nomeCli', '$nomeCli', '', 'user-default.png', '', '$celular')") or die(mysqli_error($con));

            // Envia push notification para o prestador
            require_once(__DIR__ . '/api/push-send.php');
            $codPrestadorNotifNovo = $_GET['codcadastro'];
            $codPedidoNotifNovo = $_GET['codpedido'];
            $primeiroNomeCliNovo = !empty($nomeCli) ? explode(' ', trim($nomeCli))[0] : 'Um cliente';
            enviarPushNotification($con, $codPrestadorNotifNovo, 'prestador', 
                'Proposta Aceita!', 
                "$primeiroNomeCliNovo aceitou sua proposta no pedido #$codPedidoNotifNovo. Acesse para firmar o acordo!", 
                "/meus-orcamentos.php"
            );

        } 

        if($totalUsuario > 0) { 

            $queryEnvioLogin = mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) VALUES
            ('".$nomeUsuario."', '".$celular."', '".$_GET['codpedido']."', '".$_GET['codcadastro']."', '".$id."', 'sim')") or die(mysqli_error($con));

            $editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='p', visto=0 where codpedido = '".$_GET['codpedido']."'") or die(mysqli_error($con));
            $editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='s', visto=0 where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$id."'") or die(mysqli_error($con));

            // Envia push notification para o prestador
            require_once(__DIR__ . '/api/push-send.php');
            $codPrestadorNotif = $_GET['codcadastro'];
            $codPedidoNotif = $_GET['codpedido'];
            $primeiroNomeCli = !empty($nomeUsuario) ? explode(' ', trim($nomeUsuario))[0] : 'Um cliente';
            enviarPushNotification($con, $codPrestadorNotif, 'prestador', 
                'Proposta Aceita!', 
                "$primeiroNomeCli aceitou sua proposta no pedido #$codPedidoNotif. Acesse para firmar o acordo!", 
                "/meus-orcamentos.php"
            );

        }

        if($totalUsuario > 0) { 

    
            $numero = 20;
            $min = 5;
            $max = 10000;
            $gera = rand($min,$max);
            $enviarnumero = '0'.$gera; 
            $numerolimpo = preg_replace('/\D/', '', $numerolimpo);
            
            // Salva o código no banco para validação (exibido na tela)
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            mysqli_query($con, "DELETE FROM verification_codes WHERE celular='$numerolimpo' AND usado=0");
            mysqli_query($con, "INSERT INTO verification_codes (celular, codigo, tipo, expires_at) VALUES ('$numerolimpo', '$enviarnumero', 'pedido', '$expiresAt')");
        } else {
            echo "<script>alert('Dados Incorretos ou Não Cadastrado'); window.location.href='pegar_contato.php?nome=$nome&codpedido=$codpedido&codcadastro=$codcadastro&codcliente=$id';</script>";
        }


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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Converting to USERVICE standard header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: rgba(0, 212, 255, 0.1);
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }

        .header .logo {
            font-size: 18px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .back-button {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, 0.3);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .back-button:hover {
            background: rgba(0, 212, 255, 0.3);
            transform: translateY(-1px);
        }

        /* Converting main container to vertical layout */
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
            overflow-y: auto;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
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

        .tab:hover {
            color: #00d4ff;
            background: #f0f9ff;
        }

        .form-container {
            text-align: center;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .user-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }

        .form-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .submit-button {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 212, 255, 0.4);
        }

        .form-link {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .form-link a {
            color: #00d4ff;
            text-decoration: none;
            font-weight: 600;
        }

        .form-link a:hover {
            text-decoration: underline;
        }

        .terms-text {
            font-size: 13px;
            color: #9ca3af;
            line-height: 1.4;
            text-align: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .terms-text a {
            color: #00d4ff;
            text-decoration: none;
        }

        .terms-text a:hover {
            text-decoration: underline;
        }

        /* Adding responsive design for mobile */
        @media (max-width: 480px) {
            .main-container {
                padding: 8px;
            }
            
            .login-card {
                padding: 16px;
            }
            
            .form-title {
                font-size: 20px;
            }
            
            .user-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php 
        include 'topo2.php';
    ?>

    <div class="main-container">
        <div class="login-card">
            <div class="tabs">
                <div class="tab active" onclick="switchTab('login')">Entrar</div>
            </div>

            <!-- Formulário de Login -->
            <div id="login-form" class="form-section active">
                <div class="form-container">
                   
                    <b><?php echo $celular; ?></b> <a href="pegar_contato.php?nome=<?php echo $_GET['nome']; ?>&codpedido=<?php echo $_GET['codpedido']; ?>&codcadastro=<?php echo $_GET['codcadastro']; ?>"> Trocar de número</a><br>
                    <strong style="color: #00d4ff; font-size: 28px; display: block; margin: 15px 0; letter-spacing: 8px;"><?php echo $enviarnumero; ?></strong>
                    Digite o código acima para confirmar sua identidade</p>

                    <form action="criarcookiewats_pedido.php?codpedido=<?php echo $_GET['codpedido']; ?>&codcadastro=<?php echo $_GET['codcadastro']; ?>&nome=<?php echo $_GET['nome']; ?>" method="get">
                    <input type="hidden" name="contato">
                        <input type="hidden" class="form-control" name="nome" value="<?php echo $_GET['nome']; ?>" />
                        <input type="hidden" class="form-control" name="nomeCli" value="<?php echo $nomeUsuario; ?>" />
                        <input type="hidden" class="form-control" name="numero" value="<?php echo $enviarnumero; ?>" />
                        <input type="hidden" class="form-control" name="celular" value="<?php echo $celular; ?>" />
                        <input type="hidden" class="form-control" name="codpedido" value="<?php echo $_GET['codpedido']; ?>" />
                        <input type="hidden" class="form-control" name="codcadastro" value="<?php echo $_GET['codcadastro']; ?>" />
                        <input type="hidden" class="form-control" name="codcliente" value="<?php echo $id; ?>" />
                        
                        <div class="form-group">
                            <input 
                                type="tel" 
                                class="form-input" 
                                placeholder="Digite o código acima"
                                id="login-phone"
                                name="confirmanumero"
                                required
                            >
                        </div>
                        
                        <button type="submit" class="submit-button">Entrar</button>
                        
                        <button type="button" class="submit-button" onclick="window.location.reload()" style="background: transparent; color: #00d4ff; border: 2px solid #00d4ff; font-size: 14px;">
                            🔄 Gerar novo código
                        </button>
                        <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">O código expira em 10 minutos</p>

                    </form>
                </div>
            </div>

            <!-- Formulário de Cadastro -->
            <div id="register-form" class="form-section">
                <div class="form-container">
                    <div class="user-icon">👥</div>
                    <h2 class="form-title">Crie sua conta</h2>
                    
                    <form onsubmit="handleRegister(event)">
                        <!-- Added hidden fields to pass URL parameters -->
                        <input type="hidden" class="form-control" name="nome" id="register-nome" />
                        <input type="hidden" class="form-control" name="codpedido" id="register-codpedido" />
                        <input type="hidden" class="form-control" name="codcadastro" id="register-codcadastro" />
                        
                        <div class="form-group">
                            <label class="form-label">Nome completo</label>
                            <input 
                                type="text" 
                                class="form-input" 
                                placeholder="Seu nome completo"
                                id="register-name"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Número de celular</label>
                            <input 
                                type="tel" 
                                class="form-input" 
                                placeholder="(00) 00000-0000"
                                id="register-phone"
                                required
                            >
                        </div>
                        
                        <button type="submit" class="submit-button">Criar conta</button>
                        
                        <div class="form-link">
                            Já tem uma conta? <a href="#" onclick="switchTab('login')">Faça login</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="terms-text">
                Ao continuar, você concorda com nossos 
                <a href="#">Termos de Serviço</a> e 
                <a href="#">Política de Privacidade</a>.
            </div>
        </div>
    </div>

    <script>
        function getUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            return {
                nome: urlParams.get('nome') || '',
                codpedido: urlParams.get('codpedido') || '',
                codcadastro: urlParams.get('codcadastro') || ''
            };
        }

        function populateHiddenFields() {
            const params = getUrlParameters();
            
            // Populate login form hidden fields
            document.getElementById('login-nome').value = params.nome;
            document.getElementById('login-codpedido').value = params.codpedido;
            document.getElementById('login-codcadastro').value = params.codcadastro;
            
            // Populate register form hidden fields
            document.getElementById('register-nome').value = params.nome;
            document.getElementById('register-codpedido').value = params.codpedido;
            document.getElementById('register-codcadastro').value = params.codcadastro;
        }

        function switchTab(tabName) {
            // Remove active class from all tabs and forms
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding form
            event.target.classList.add('active');
            document.getElementById(tabName + '-form').classList.add('active');
        }

        function handleLogin(event) {
            event.preventDefault();
            const phone = document.getElementById('login-phone').value;
            
            if (phone) {
                const params = getUrlParameters();
                const queryString = new URLSearchParams({
                    nome: params.nome,
                    codpedido: params.codpedido,
                    codcadastro: params.codcadastro,
                    celularCli: phone
                }).toString();
                
                window.location.href = `confirmanumero_cliente_pedido.php?${queryString}`;
            }
        }

        function handleRegister(event) {
            event.preventDefault();
            const name = document.getElementById('register-name').value;
            const phone = document.getElementById('register-phone').value;
            
            if (name && phone) {
                const params = getUrlParameters();
                const queryString = new URLSearchParams({
                    nome: params.nome,
                    codpedido: params.codpedido,
                    codcadastro: params.codcadastro,
                    nome_completo: name,
                    celularCli2: phone,
                    cadastro: true
                }).toString();
                
                window.location.href = `confirmanumero_cliente_pedido.php?${queryString}`;
            }
        }

        // Máscara para telefone
        document.addEventListener('DOMContentLoaded', function() {
            populateHiddenFields();
            
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                input.addEventListener('input', function() {
                    formatPhone(this);
                });
            });
        });

        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            input.value = value;
        }

        // Countdown para reenviar código
        var countdownTimer;
        function startCountdown() {
            var seconds = 30;
            var btn = document.getElementById('resendBtn');
            var countdownSpan = document.getElementById('countdown');
            if (!btn) return;
            btn.disabled = true;
            countdownTimer = setInterval(function() {
                seconds--;
                if (countdownSpan) countdownSpan.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    btn.disabled = false;
                    btn.innerHTML = 'Reenviar código';
                    btn.style.background = 'linear-gradient(145deg, #00d4ff, #00f0ff)';
                    btn.style.color = '#1a2332';
                    btn.style.border = 'none';
                }
            }, 1000);
        }

        function reenviarCodigo() {
            window.location.reload();
        }

        // Iniciar countdown quando página carrega (código já foi enviado)
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('resendBtn')) {
                startCountdown();
            }
        });
    </script>
</body>
</html>
