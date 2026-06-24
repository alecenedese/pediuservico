<?php 
session_start();
require_once ("send.php");

$clienteData = null;
$idCliente = '';
if(isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
    $idCliente = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
} elseif(isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
    $idCliente = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
} else {
    // Resolve pelo CPF/CNPJ unificado
    $cpfLimpo = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
    if ($cpfLimpo !== '') {
        $cpfEsc = mysqli_real_escape_string($con, $cpfLimpo);
        $qR = mysqli_query($con, "SELECT id FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc' LIMIT 1");
        if ($qR && $rR = mysqli_fetch_array($qR)) {
            $idCliente = (int)$rR['id'];
            setcookie('id_cliente', $idCliente, time()+30*24*3600, '/');
        }
    }
}

if (!empty($idCliente)) {
    $query = mysqli_query($con, "SELECT c.NOME, c.CELULAR FROM clientes c WHERE id = '$idCliente'");
    if($query && mysqli_num_rows($query) > 0) {
        $clienteData = mysqli_fetch_assoc($query);
    }
}

if(isset($_POST['atualizar_dados']) && !empty($idCliente)) {
    $nome = mysqli_real_escape_string($con, $_POST['nome']);
    $celular = mysqli_real_escape_string($con, $_POST['celular']);
    
    $updateQuery = mysqli_query($con, "UPDATE clientes SET NOME = '$nome', CELULAR = '$celular' WHERE id = '$idCliente'");
    
    if($updateQuery) {
        // Atualiza cookies de nome/celular
        $exp = time() + (30*24*3600);
        setcookie('nome_usuario', $nome, $exp, '/');
        setcookie('celular_usuario', $celular, $exp, '/');
        setcookie('nomeCli', $nome, $exp, '/');
        setcookie('celularCli', $celular, $exp, '/');
        echo "<script>alert('Dados atualizados com sucesso!'); window.location.href='editar-cadastro-cliente.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar dados. Tente novamente.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cadastro - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 70px;
        }

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

        .menu-button {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, 0.3);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-button:hover {
            background: rgba(0, 212, 255, 0.3);
            transform: translateY(-1px);
        }

        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
            overflow-y: auto;
        }

        .edit-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .user-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 24px;
        }

        .form-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
            text-align: center;
        }

        .form-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
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

        .cancel-button {
            width: 100%;
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
            border: 2px solid #d1d5db;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .cancel-button:hover {
            background: rgba(107, 114, 128, 0.2);
            border-color: #9ca3af;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100vh;
            background: rgba(26, 35, 50, 0.95);
            backdrop-filter: blur(10px);
            transition: right 0.3s ease;
            z-index: 1000;
            padding: 20px;
            border-left: 1px solid rgba(0, 212, 255, 0.2);
        }

        .sidebar.open {
            right: 0;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }

        .sidebar-title {
            color: #00d4ff;
            font-size: 18px;
            font-weight: bold;
        }

        .close-sidebar {
            background: none;
            border: none;
            color: #00d4ff;
            font-size: 24px;
            cursor: pointer;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 15px;
        }

        .sidebar-menu a {
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            padding: 10px 15px;
            display: block;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .overlay.show {
            display: block;
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 8px;
            }
            
            .edit-card {
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

                /* Adicionando grid de navegação rápida acima das tabs */
        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 0 8px;
            margin-bottom: 16px;
            margin-top: 16px;
        }

        .nav-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 10px 6px;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            text-decoration: none;
            color: #ffffff;
            transition: all 0.3s ease;
            min-height: 65px;
        }

        .nav-card:hover {
            background: rgba(0, 212, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-card.active {
            background: rgba(0, 212, 255, 0.25);
            border-color: rgba(0, 212, 255, 0.4);
        }

        .nav-card svg {
            width: 22px;
            height: 22px;
            stroke-width: 2;
            color: #00d4ff;
        }

        .nav-card span {
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            line-height: 1.1;
        }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>

            <!-- Adicionando grid de navegação rápida -->
        <div class="quick-nav-grid">
            <a href="meus-orcamentos-cli.php" class="nav-card ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Pedidos</span>
            </a>
            
            
            <a href="edicao.php" class="nav-card active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Cadastro</span>
            </a>
            
        </div>

    <div class="main-container">
        <div class="edit-card">
            <div class="user-icon">            
                <a href="sair-unificado.php" class="logout" style="color: #aa0e0e;
    background: #edb3b3a3;
    text-decoration: none;
    font-size: 16px;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #aa0e0e;">Sair</a>

 </div>
            <h2 class="form-title">Editar Cadastro</h2>
            <p class="form-subtitle">Atualize suas informações pessoais</p>
            
            <?php if($clienteData): ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nome completo</label>
                    <input 
                        type="text" 
                        class="form-input" 
                        name="nome"
                        value="<?php echo htmlspecialchars($clienteData['NOME']); ?>"
                        placeholder="Seu nome completo"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label">Número de celular</label>
                    <input 
                        type="tel" 
                        class="form-input" 
                        name="celular"
                        value="<?php echo htmlspecialchars($clienteData['CELULAR']); ?>"
                        placeholder="(00) 00000-0000"
                        id="phone-input"
                        required
                    >
                </div>
                
                <button type="submit" name="atualizar_dados" class="submit-button">
                    Salvar Alterações
                </button>
                
                <a href="javascript:history.back()" class="cancel-button">
                    Cancelar
                </a>
            </form>
            <?php else: ?>
            <div style="text-align: center; color: #6b7280; padding: 32px;">
                <p>Erro ao carregar dados do cliente.</p>
                <a href="login-unificado.php" class="cancel-button" style="margin-top: 16px;">
                    Fazer Login
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="overlay" onclick="closeSidebar()"></div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">Menu</div>
            <button class="close-sidebar" onclick="closeSidebar()">×</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="meus-orcamentos.php">Meus Orçamentos</a></li>
            <li><a href="minhasmoedas.php">Minhas Moedas</a></li>
            <li><a href="meus-enderecos.php">Meus Endereços</a></li>
            <li><a href="editar-cadastro-cliente.php">Editar Cadastro</a></li>
            <li><a href="listar-avaliacoes.php">Minhas Avaliações</a></li>
            <li><a href="index.html">Página Inicial</a></li>
        </ul>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone-input');
            if(phoneInput) {
                phoneInput.addEventListener('input', function() {
                    formatPhone(this);
                });
            }
        });

        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            input.value = value;
        }
    </script>

<?php $navAtiva = 'pedidos'; include('bottom-nav.php'); ?>
</body>
</html>
