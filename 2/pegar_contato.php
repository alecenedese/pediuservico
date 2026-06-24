<?php session_start();
require_once ("send.php");

// Verifica se o CLIENTE está logado (cookie celularCli ou cookies unificados com eh_cliente)
$clienteLogado = false;
$nomeCliente = '';
$celularCliente = '';
$codCliente = '';

if (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') {
    $clienteLogado = true;
    $nomeCliente = isset($_COOKIE['nome_usuario']) ? $_COOKIE['nome_usuario'] : '';
    $celularCliente = isset($_COOKIE['celular_usuario']) ? $_COOKIE['celular_usuario'] : '';
    $codCliente = isset($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : (isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '');
    // Resolve codCliente pelo CPF/CNPJ se necessario
    if (empty($codCliente)) {
        $cpfLimpo = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
        if ($cpfLimpo !== '') {
            $cpfEsc = mysqli_real_escape_string($con, $cpfLimpo);
            $qR = mysqli_query($con, "SELECT id FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc' LIMIT 1");
            if ($qR && $rR = mysqli_fetch_array($qR)) { $codCliente = (int)$rR['id']; }
        }
    }
} elseif (isset($_COOKIE['celularCli']) && !empty($_COOKIE['celularCli'])) {
    $clienteLogado = true;
    $nomeCliente = isset($_COOKIE['nomeCli']) ? $_COOKIE['nomeCli'] : '';
    $celularCliente = $_COOKIE['celularCli'];
    $codCliente = isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '';
} elseif (isset($_COOKIE['eh_cliente']) && $_COOKIE['eh_cliente'] == '1' && isset($_COOKIE['celular_usuario'])) {
    $clienteLogado = true;
    $nomeCliente = isset($_COOKIE['nome_usuario']) ? $_COOKIE['nome_usuario'] : '';
    $celularCliente = $_COOKIE['celular_usuario'];
    $codCliente = isset($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : '';
}

if ($clienteLogado && !empty($codCliente)) {
    
    // Validar parâmetros GET obrigatórios
    if (!isset($_GET['codpedido']) || !isset($_GET['codcadastro'])) {
        die('<div style="padding: 20px; text-align: center; color: red;">❌ Erro: Parâmetros obrigatórios não informados (codpedido e codcadastro).</div>');
    }
    
    $codpedido = mysqli_real_escape_string($con, $_GET['codpedido']);
    $codcadastro = mysqli_real_escape_string($con, $_GET['codcadastro']);
    
    $queryEnvioLogin = mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) VALUES
     ('".$nomeCliente."', '".$celularCliente."', '".$codpedido."', '".$codcadastro."', '".$codCliente."', 'sim')") or die(mysqli_error($con));

    $editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='ac', visto=0 where codpedido = '".$codpedido."' and codcadastro = '".$codcadastro."'") or die(mysqli_error($con));

    // Envia push notification para o prestador informando que o cliente aceitou
    require_once(__DIR__ . '/api/push-send.php');
    $codPrestadorNotif = $codcadastro;
    $codPedidoNotif = $codpedido;
    $primeiroNomeCli = !empty($nomeCliente) ? explode(' ', trim($nomeCliente))[0] : 'Um cliente';

    // Item 14: custo de moedas vem da CATEGORIA (categoria.codigo = pedido.subcategoria)
    $custoMoedasAuto = 0;
    $qCustoAuto = mysqli_query($con, "SELECT c.moeda FROM pedido p INNER JOIN categoria c ON c.codigo = p.subcategoria WHERE p.codigo = '".mysqli_real_escape_string($con, $codPedidoNotif)."'");
    if ($qCustoAuto && $rCustoAuto = mysqli_fetch_array($qCustoAuto)) {
        $custoMoedasAuto = (int)$rCustoAuto['moeda'];
    }
    if ($custoMoedasAuto <= 0) {
        $qCustoAutoG = mysqli_query($con, "SELECT g.custo_moedas FROM pedido p INNER JOIN grupos g ON g.codigo = p.categoria WHERE p.codigo = '".mysqli_real_escape_string($con, $codPedidoNotif)."'");
        if ($qCustoAutoG && $rCustoAutoG = mysqli_fetch_array($qCustoAutoG)) {
            $cgAuto = (int)$rCustoAutoG['custo_moedas'];
            if ($cgAuto > 0) $custoMoedasAuto = $cgAuto;
        }
    }
    if ($custoMoedasAuto <= 0) $custoMoedasAuto = 5;

    // Auto-accept: se o prestador tem moedas suficientes, debita automaticamente e confirma o acordo
    $queryMoedasAuto = mysqli_query($con, "select * from quantidade_pedidos where tipo = 'pre' and codcadastro='".$codPrestadorNotif."'");
    $rowMoedasAuto = mysqli_fetch_array($queryMoedasAuto);
    if ($rowMoedasAuto && $rowMoedasAuto['qtd'] >= $custoMoedasAuto) {
        // Debita moedas conforme custo da categoria
        $novaQtd = $rowMoedasAuto['qtd'] - $custoMoedasAuto;
        mysqli_query($con, "update quantidade_pedidos set qtd='$novaQtd' where codcadastro = '".$codPrestadorNotif."'");
        // Registra no extrato de moedas
        mysqli_query($con, "INSERT INTO moedas_extrato (codcadastro, tipo, quantidade, descricao, codpedido, data_hora) VALUES ('".$codPrestadorNotif."', 'debito', '$custoMoedasAuto', 'Débito automático pedido #$codPedidoNotif', '$codPedidoNotif', NOW())");
        // Atualiza status do pedido
        mysqli_query($con, "update pedido set status='Prestador Disponível' where codigo = '".$codPedidoNotif."'");
// Marca outros orçamentos como perdidos
mysqli_query($con, "update disparo_pedidos set aceito='p', visto=0 where codpedido = '".$codPedidoNotif."'");
        // Confirma este orçamento
        mysqli_query($con, "update disparo_pedidos set aceito='s', visto=0 where codpedido = '".$codPedidoNotif."' and codcadastro = '".$codPrestadorNotif."'");
        // Atualiza timer
        mysqli_query($con, "update timer_acordo set status='confirmado' where codpedido = '".$codPedidoNotif."' and codcadastro = '".$codPrestadorNotif."'");

        enviarPushNotification($con, $codPrestadorNotif, 'prestador', 
            'Acordo Firmado!', 
            "$primeiroNomeCli aceitou sua proposta no pedido #$codPedidoNotif. $custoMoedasAuto moeda(s) debitada(s) automaticamente. Acordo firmado!", 
            "/meus-orcamentos.php"
        );

        // Auto-accept confirmou: redireciona direto ao chat (sem passar por aguarda-prestador)
        if(!empty($codPedidoNotif)) {
            $userEnc = urlencode($nomeCliente);
            $userIdEnc = urlencode($codCliente);
            $userFromEnc = urlencode($codPrestadorNotif);
            echo "<script>window.location.href='chat.php?codpedido=".$codPedidoNotif."&user=".$userEnc."&user_id=".$userIdEnc."&user_from=".$userFromEnc."';</script>";
        } else {
            echo "<script>window.location.href='meus-orcamentos-cli.php';</script>";
        }
        exit;
    } else {
        // Marca todos os OUTROS prestadores como perdidos imediatamente
        mysqli_query($con, "update disparo_pedidos set aceito='p', visto=0 where codpedido = '".$codPedidoNotif."' and codcadastro != '".$codPrestadorNotif."'");

        enviarPushNotification($con, $codPrestadorNotif, 'prestador', 
            'Proposta Aceita!', 
            "$primeiroNomeCli aceitou sua proposta no pedido #$codPedidoNotif. Acesse para firmar o acordo!", 
            "/meus-orcamentos.php"
        );

        // Sem auto-accept: redireciona para aguardar prestador debitar moedas
        if(empty($codpedido)) {
            echo "<script>window.location.href='meus-orcamentos-cli.php';</script>";
        } else {
            echo "<script>window.location.href='aguarda-prestador.php?codpedido=".$codpedido."&codcadastro=".$codcadastro."';</script>";
        }
        exit;
    }
}

// Cliente NÃO está logado - mostra formulário de login/cadastro abaixo

?>

<?php
// Verifica se o parâmetro 'url' está definido
if (isset($_GET['url'])) {
    // Decodifica a URL para garantir que ela esteja no formato correto
    $url = $_GET['url'];
    
    // Reconstrói a query string com todos os parâmetros, exceto 'url'
    $queryParams = $_GET;
    unset($queryParams['url']); // Remove o parâmetro 'url' para evitar duplicação
    $queryString = http_build_query($queryParams); // Cria a query string com os parâmetros
    
    // Combina a URL base com a query string
    $fullUrl = $url . ($queryString ? '?' . $queryString : '');
    
    // Escapa a URL completa para uso no HTML
    $escapedUrl = htmlspecialchars($fullUrl, ENT_QUOTES, 'UTF-8');
} else {
    // URL padrão caso 'url' não esteja definido
    $escapedUrl = '#';
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

                .category-button2 {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            border: 2px solid #00f0ff;
            color: #00f0ff;
            font-size: 13px; /* aumentado de 10px para 13px para melhor legibilidade */
            font-weight: 600;
            padding: 8px 8px; /* aumentado padding de 10px 6px para 11px 8px */
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px; /* aumentado gap de 5px para 6px */
            text-align: center;
            text-decoration: none;
            min-height: 17px; /* aumentado de 70px para 75px */
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .category-button2:hover {
            transform: translateY(-2px);
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
            border-color: #40ffff;
            color: #40ffff;
        }
    </style>
</head>
<body>
    <!-- Using standard USERVICE header -->
    <div class="header">
        <div class="logo">USERVICE</div>
        <a href="index.php" class="category-button2">Ínicio</a>
<a href="<?php echo $escapedUrl; ?>" class="category-button2">← Voltar</a>
    </div>

    <div class="main-container">
        <div class="login-card">
            <div class="tabs">
                <div class="tab active" onclick="switchTab('login')">Entrar</div>
                <div class="tab" onclick="switchTab('register')">Cadastrar</div>
            </div>

            <!-- Formulário de Login -->
            <div id="login-form" class="form-section active">
                <div class="form-container">
                    <div class="user-icon">👤</div>
                    <h2 class="form-title">Bem-vindo!</h2>
                    <p class="form-subtitle">Digite seu número de celular para entrar</p>
                    
                    <form onsubmit="handleLogin(event)">
                        <!-- Added hidden fields to pass URL parameters -->
                        <input type="hidden" class="form-control" name="nome" id="login-nome" />
                        <input type="hidden" class="form-control" name="codpedido" id="login-codpedido" />
                        <input type="hidden" class="form-control" name="codcadastro" id="login-codcadastro" />
                        
                        <div class="form-group">
                            <input 
                                type="tel" 
                                class="form-input" 
                                placeholder="(00) 00000-0000"
                                id="login-phone"
                                required
                            >
                        </div>
                        
                        <button type="submit" class="submit-button">Entrar</button>
                        
                        <div class="form-link" style="font-size: 120% !important; font-weight: bold;">
                            Não tem uma conta? <a href="#" onclick="switchTab('register')">Cadastre-se</a>
                        </div>
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
    </script>
</body>
</html>
