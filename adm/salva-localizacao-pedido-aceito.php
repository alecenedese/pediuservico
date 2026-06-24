<?php

require_once("send.php");
error_reporting(0);

function haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
    // Converte de graus para radianos
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
    
    // Calcula a diferença
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    // Fórmula de Haversine
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius; // Distância em km
    }
    
    function isWithinRadius($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $radius = 25) {
    $distance = haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo);
    return $distance <= $radius;
    }

    $queryMoedas2 = mysqli_query($con, "select * from markers where type = '3' and codpedido='".$_GET['codpedido']."'");
    $rowcadend = mysqli_fetch_array($queryMoedas2);

    $latitudeCentral = $_GET['latitude'];  // Latitude do ponto central
    $longitudeCentral = $_GET['longitude']; // Longitude do ponto central
    $latitudeDestino = $rowcadend['lat'];  // Latitude do ponto de destino
    $longitudeDestino = $rowcadend['lon']; // Longitude do ponto de destino
    $radius = 25;                   // Raio em km

  //  if (isWithinRadius($latitudeCentral, $longitudeCentral, $latitudeDestino, $longitudeDestino, $radius)) {


if(isset($_POST['acao'])) {
 
    $queryEdit = mysqli_query($con, "select * from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
    $rowEdit = mysqli_fetch_array($queryEdit);

    $deleVermelho = mysqli_query($con, "DELETE from markers WHERE type = '1' and codpedido = '".$_GET['codpedido']."' and codcadastro = '".$rowEdit['id']."'") or die(mysqli_error($con));

// verificar se nao tem avaliacao
$queryAvl1 = mysqli_query($con, "select COUNT(id) AS conta, SUM(qtd_estrela) AS qtd from avaliacoes where cod_prestador='".$rowsub['codcadastro']."'");
$contaval = mysqli_num_rows($queryAvl1);
$queryAvl = mysqli_query($con, "select COUNT(id) AS conta, SUM(qtd_estrela) AS qtd from avaliacoes where cod_prestador='".$rowEdit['id']."'");
$rowAvl = mysqli_fetch_array($queryAvl);
if($contaval > 0) {
    $contaestrelas = intval($rowAvl['qtd'] / $rowAvl['conta']);
  } else { $contaestrelas = 1; }
  
    $queryPedi = mysqli_query($con, "INSERT INTO markers (nome, codcadastro, valor_min, valor_max, lat, lon, type, codpedido, qtdestrelas, contraproposta) VALUES
    ('".$rowEdit['NOME']."', '".$rowEdit['id']."', '".$_POST['minimo']."', '".$_POST['maximo']."', '".$_GET['latitude']."', '".$_GET['longitude']."', '2', '".$_GET['codpedido']."', '$contaestrelas', '".$_POST['contraproposta']."')") or die(mysqli_error($con));

$editaPedidoPedi = mysqli_query($con, "update pedido set status='Proposta Aceita' where codpedido = '".$_GET['codpedido']."'") or die(mysqli_error($con));

$editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='a', visto=0 where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$rowEdit['id']."'") or die(mysqli_error($con));

echo "<script>alert('Proposta enviada com sucesso'); window.location.href='".$urlserver."meus-orcamentos-aguardando.php';</script>";

}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceitar Orçamento - USERVICE</title>
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
        }

        /* Header styling identical to other USERVICE pages */
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

        /* Menu sidebar identical to other pages */
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .menu-sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 280px;
            height: 100%;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            z-index: 1000;
            transition: left 0.3s ease;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
        }

        .menu-sidebar.active {
            left: 0;
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }

        .menu-title {
            font-size: 18px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .close-menu {
            background: none;
            border: none;
            color: #00d4ff;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }

        .menu-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 8px;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .menu-nav a:hover {
            background: rgba(0, 212, 255, 0.1);
            color: #00d4ff;
        }

        .menu-nav a.active {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
        }

        .menu-nav svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        /* Main content layout for vertical mobile */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            gap: 1.5rem;
            max-width: 100%;
        }

        .page-header {
            text-align: center;
            color: #00d4ff;
            margin-bottom: 1rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .page-subtitle {
            font-size: 1rem;
            opacity: 0.8;
        }

        /* Form card styling */
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 212, 255, 0.1);
        }

        .form-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #1a2332;
            margin-bottom: 0.5rem;
        }

        .form-description {
            font-size: 0.9rem;
            color: #666;
        }

        /* Form styling adapted for vertical mobile */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            background: rgba(0, 212, 255, 0.05);
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            padding: 1rem;
            font-size: 1rem;
            color: #1a2332;
            transition: all 0.3s ease;
            min-height: 48px;
        }

        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            background: rgba(0, 212, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        .form-input::placeholder {
            color: #999;
            font-size: 0.9rem;
        }

        /* Submit button styling */
        .submit-button {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 0.9rem;
            font-weight: bold;
            color: #1a2332;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .submit-button svg {
            width: 20px;
            height: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .main-content {
                padding: 1rem;
            }
            
            .form-card {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }

                /* Adicionando grid de navegação rápida acima das tabs */
        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            padding: 0 0.5rem;
            margin-bottom: 1rem;
        }

        .nav-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            padding: 0.6rem 0.4rem;
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
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            line-height: 1.1;
        }

                .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 6px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s;
            min-height: 44px;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header with menu button -->
    <div class="header">
        <div class="logo">USERVICE</div>
        <a class="menu-button" href="buscar.php" style="text-decoration: none;">Buscar Prestador</a>
    </div>

    <!-- Menu sidebar -->
    <div class="menu-overlay" id="menu-overlay" onclick="closeMenu()"></div>
    <div class="menu-sidebar" id="menu-sidebar">
        <div class="menu-header">
            <div class="menu-title">USERVICE</div>
            <button class="close-menu" onclick="closeMenu()">×</button>
        </div>
        <nav class="menu-nav">
            <a href="index.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Início</span>
            </a>
            <a href="consumidor.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Buscar Prestador</span>
            </a>
            <a href="edicao.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Minha Conta</span>
            </a>
            <a href="meus-orcamentos.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Meus Endereços</span>
            </a>
            <a href="meus-orcamentos.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Orçamentos</span>
            </a>
            <a href="minhasmoedas.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Minhas Moedas</span>
            </a>
            <a href="listar_avaliacoes.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span>Minhas Avaliações</span>
            </a>
        </nav>
    </div>

    <div class="main-content">

            <!-- Adicionando grid de navegação rápida -->
        <div class="quick-nav-grid">
            <a href="meus-orcamentos.php" class="nav-card active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Orçamentos</span>
            </a>
            
            <a href="minhasmoedas.php" class="nav-card">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Minhas Moedas</span>
            </a>
            
            <a href="edicao.php" class="nav-card">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Editar Cadastro</span>
            </a>
            
            <a href="listar-avaliacoes.php" class="nav-card">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span>Minhas Avaliações</span>
            </a>
        </div>


        <div class="form-card">
            <div class="form-header">
                <div class="form-title">Informações de Pagamento</div>
            </div>
            
            <form action="" method="post">
                <input type="hidden" name="acao" value="envia">
                
                <div class="form-grid">
                    <!-- Converting desktop form to vertical mobile layout -->
                    <div class="form-group">
                        <label for="minimo" class="form-label">Valor Mínimo Oferecido</label>
                        <input 
                            type="tel" 
                            id="minimo" 
                            class="form-input" 
                            onKeyPress="return(MascaraMoeda(this,'.',',',event))"
                            name="minimo"
                            placeholder="R$ 0,00"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="maximo" class="form-label">Valor Máximo</label>
                        <input 
                            type="tel" 
                            id="maximo"
                            onKeyPress="return(MascaraMoeda(this,'.',',',event))"
                            class="form-input" 
                            name="maximo"
                            placeholder="R$ 0,00"
                            required
                        >
                    </div>

                        <label for="contraproposta" class="form-label">Observação</label>
<textarea class="form-input" id="contraproposta" name="contraproposta" rows="5" oninput="validarContraproposta(this)"></textarea>
<span id="erroMensagem" style="color: red;"></span>

<script>
function validarContraproposta(textarea) {
    const erroMensagem = document.getElementById('erroMensagem');
    const texto = textarea.value;

    // Função para contar números (incluindo por extenso)
    function contarNumeros(str) {
        // Regex para números inteiros ou horários (ex.: 16:30)
        const regexNumeros = /\b\d+\b|\b\d{1,2}:\d{2}\b/g;
        // Palavras que representam números por extenso
        const numerosExtenso = ['zero', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove', 'dez'];
        
        let contagem = 0;
        // Contar números inteiros e horários
        const matchesNumeros = str.match(regexNumeros) || [];
        contagem += matchesNumeros.length;

        // Contar números por extenso
        numerosExtenso.forEach(num => {
            const regexExtenso = new RegExp(`\\b${num}\\b`, 'gi');
            const matchesExtenso = str.match(regexExtenso) || [];
            contagem += matchesExtenso.length;
        });

        return contagem;
    }

    // Regex para detectar números de telefone (ex.: (11) 91234-5678, 11912345678, etc.)
    const regexTelefone = /\b(\(\d{2}\)\s?\d{4,5}-?\d{4}|\d{10,11})\b/;
    
    // Verificar se há número de telefone
    if (regexTelefone.test(texto)) {
        erroMensagem.textContent = 'Números de telefone não são permitidos!';
        textarea.value = ''; // Limpa o campo
        return;
    }

    // Contar números (inteiros, horários e por extenso)
    const totalNumeros = contarNumeros(texto);
    
    // Verificar se excede o limite de 5 números
    if (totalNumeros > 5) {
        erroMensagem.textContent = 'Apenas até 5 números (incluindo por extenso) são permitidos!';
        textarea.value = ''; // Limpa o campo
        return;
    }

    // Se passar nas validações, limpar mensagem de erro
    erroMensagem.textContent = '';
}
</script>
                                
                </div>
                
                <!-- Updated submit button to match USERVICE design -->
                <button type="submit" class="submit-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirmar
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('menu-sidebar');
            const overlay = document.getElementById('menu-overlay');
            
            sidebar.classList.add('active');
            overlay.style.display = 'block';
        }

        function closeMenu() {
            const sidebar = document.getElementById('menu-sidebar');
            const overlay = document.getElementById('menu-overlay');
            
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        }

        function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e){
            var sep = 0;
            var key = '';
            var i = j = 0;
            var len = len2 = 0;
            var strCheck = '0123456789';
            var aux = aux2 = '';
            var whichCode = (window.Event) ? e.which : e.keyCode;
            if (whichCode == 13) return true;
            key = String.fromCharCode(whichCode); // Valor para o código da Chave
            if (strCheck.indexOf(key) == -1) return false; // Chave inválida
            len = objTextBox.value.length;
            for(i = 0; i < len; i++)
                if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
            aux = '';
            for(; i < len; i++)
                if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) aux += objTextBox.value.charAt(i);
            aux += key;
            len = aux.length;
            if (len == 0) objTextBox.value = '';
            if (len == 1) objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
            if (len == 2) objTextBox.value = '0'+ SeparadorDecimal + aux;
            if (len > 2) {
                aux2 = '';
                for (j = 0, i = len - 3; i >= 0; i--) {
                    if (j == 3) {
                        aux2 += SeparadorMilesimo;
                        j = 0;
                    }
                    aux2 += aux.charAt(i);
                    j++;
                }
                objTextBox.value = '';
                len2 = aux2.length;
                for (i = len2 - 1; i >= 0; i--)
                objTextBox.value += aux2.charAt(i);
                objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
            }
            return false;
        }
    </script>
</body>
</html>

<?php /* }

else {

    echo "<script>alert('sua distância não permite aceitar esse pedido!');  window.location.href='".$urlserver."meus-orcamentos';</script>";
}*/

?>
