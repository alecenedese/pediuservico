<?php 
     require("send.php");
     include('badge-counts.php');
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);
if(isset($_GET['codpedido'])){
//Aguarde enquanto enviamos para o cliente
  echo "<script>alert('Contraproposta enviada com sucesso!')</script>";
  $texto = urlencode($_GET['contraproposta']);
  echo "<script>window.location.href='".$urlserver."aceita-orcamento.php?codigo=".$_GET['codpedido']."&contraproposta=$texto&maximo=$maximo&minimo=$minimo';</script>";
}    
if(isset($_GET['codpedidoperdido'])){
$editaPedidoCad = mysqli_query($con, "update disparo_pedidos set aceito='p' where codpedido = '".$_GET['codpedidoperdido']."'") or die(mysqli_error($con));
}  

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamentos Aguardando - USERVICE</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
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

        /* Using USERVICE header styling from meus-orcamentos2.php */
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

        /* Menu lateral identical to meus-orcamentos2.php */
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

        /* Main content layout adapted for vertical mobile */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
            max-width: 100%;
        }

        .page-header {
            text-align: center;
            color: #00d4ff;
            margin-bottom: 16px;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .page-subtitle {
            font-size: 16px;
            opacity: 0.8;
        }

        /* Tabs adapted for vertical layout with "Aguardando" as active */
        .tabs-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        @media (min-width: 480px) {
            .tabs-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .tab {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 13px;
            font-weight: 600;
            padding: 13px 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            position: relative;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            min-height: 60px;
        }

        .tab:not(.active) {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            border-color: rgba(0, 212, 255, 0.3);
        }

        .tab.active {
            background: linear-gradient(145deg, #f95757, #ff3535);
            color: #fff;
            border-color: #ff3535;
        }

        .tab svg {
            width: 16px;
            height: 16px;
        }

        .tab-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #dc3545;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            line-height: 18px;
            border-radius: 9px;
            text-align: center;
            padding: 0 4px;
            box-shadow: 0 1px 4px rgba(220,53,69,0.4);
        }

        /* Content container adapted for vertical scrolling */
        .content-container {
            flex: 1;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(0, 212, 255, 0.2);
            padding: 16px;
            overflow-y: auto;
            position: relative;
        }

        /* Order cards adapted from meus-orcamentos2.php styling with orange accent for waiting */
        .order-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #f41212;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid rgba(0, 212, 255, 0.1);
        }

        .order-number {
            font-size: 19px;
            font-weight: bold;
            color: #1a2332;
        }

.order-status {
    background: #ffd2d2;
    color: #f41212;
    padding: 6px 13px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 600;
}

        .order-content {
            padding: 16px;
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 13px;
            margin-bottom: 16px;
        }

        @media (min-width: 480px) {
            .order-info {
                grid-template-columns: 1fr 1fr;
            }
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .info-icon {
            background: rgba(0, 212, 255, 0.1);
            padding: 8px;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .info-icon svg {
            width: 16px;
            height: 16px;
            color: #00d4ff;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 13px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 14px;
            color: #1a2332;
            line-height: 1.4;
        }

        /* Dynamic content area for real-time updates */
        .dynamic-content {
            margin-top: 16px;
            padding: 16px;
            background: rgba(255, 165, 0, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(255, 165, 0, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 48px 16px;
            color: #666;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 19px;
            margin-bottom: 8px;
            color: #1a2332;
        }

        .empty-state p {
            font-size: 16px;
            opacity: 0.8;
        }
            /* Adicionando grid de navegação rápida acima das tabs */
        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 0 8px;
            margin-bottom: 16px;
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
    <!-- Header with menu button instead of back button -->
    <?php 
    $navAtiva = 'servicos';
    include('header-app.php'); 
?>

    <!-- Menu sidebar identical to meus-orcamentos2.php -->
    <div class="menu-overlay" id="menu-overlay" onclick="closeMenu()"></div>
    <div class="menu-sidebar" id="menu-sidebar">
        <div class="menu-header">
            <div class="menu-title">USERVICE</div>
        <a class="menu-button" href="buscar.php" style="text-decoration: none;">Buscar Prestador</a>
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

        <?php include('panel-banner-prestador.php'); ?>

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
        </div>


        <!-- Item 5: botão único + popup de situações (substitui as abas) -->
        <?php $areaSituacao = 'prestador'; include('popup-situacoes.php'); ?>

        <div class="content-container">
            <!-- Order cards with vertical mobile layout for waiting orders -->
            <?php
            $queryEdit = mysqli_query($con, "select * from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
            $rowEdit = mysqli_fetch_array($queryEdit);
              $queryEdit2 = mysqli_query($con, "
              SELECT g.titulo, p.codigo, s.titulo AS sub, p.local, p.tempo, p.descricao, p.lat, p.log, p.data_hora, p.valor, p.foto_1, p.foto_2, p.foto_3, p.foto_4, p.servicos, d.visto FROM 
              grupos g,
              categoria_prestador cat,
              pedido p,
              categoria s,
              disparo_pedidos d
              WHERE 
              g.codigo = cat.codcategoria 
              and p.categoria = cat.codcategoria
              and p.subcategoria = cat.codsubcategoria
               AND s.codigo = cat.codsubcategoria
              AND cat.codcadastro = '".$rowEdit['id']."'
              AND d.codcadastro = cat.codcadastro
              AND d.codpedido = p.codigo
              AND d.aceito =  'p'
              ORDER BY p.codigo desc

              ");
            
            if (mysqli_num_rows($queryEdit2) == 0) {
                echo '<div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3>Nenhum orçamento Perdido</h3>
                </div>';
            }
            
            while($rowEdit1 = mysqli_fetch_array($queryEdit2)) {
            ?>
                <div class="order-card" data-codpedido="<?php echo $rowEdit1['codigo']; ?>" data-visto="<?php echo isset($rowEdit1['visto']) ? (int)$rowEdit1['visto'] : 0; ?>">
                    <div class="order-header">
                        <div class="order-number">Pedido #<?php echo $rowEdit1['codigo']; ?></div>
                        <div class="order-status">Perdido</div>
                    </div>
                    
                    <div class="order-content">
                        <div class="order-info">
                            <div class="info-item">
                                <div class="info-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Data e Hora</div>
                                    <div class="info-value">
                                        <?php
                                        date_default_timezone_set('America/Sao_Paulo');
                                        $DataEspecifica = new DateTime($rowEdit1['data_hora']);
                                        echo $DataEspecifica->format('d/m/Y H:i:s');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Tempo Estimado</div>
                                    <div class="info-value">
                                        <?php 
                                        if($rowEdit1['tempo']=="Até 1 hora (Emergência)"){ echo "O Cliente Pode Ser Atendido Ate 1 Hora (Emergência)";}
                                        else if($rowEdit1['tempo']=="Pra hoje, em qualquer horário"){ echo "O Cliente Pode hoje qualquer horário";}
                                        else if($rowEdit1['tempo']=="Pra Hoje no horário comercial"){ echo "O Cliente Só Pode hoje no horário comercial";}
                                        else if($rowEdit1['tempo']=="Até 24 horas"){ echo "O Cliente Pode Ser Atendido Em Ate 24 Horas";}
                                        else if($rowEdit1['tempo']=="Pra essa semana"){ echo "O Cliente Pode Ser Atendido Em Ate 7 Dias";}
                                        else if($rowEdit1['tempo']=="Pra esse mês"){ echo "O Cliente Pode Ser Atendido Em Ate 30 Dias";}
                                        else if($rowEdit1['tempo']=="Para qualquer dia que o prestador estiver disponível desde que faça o serviço"){ echo "O Cliente Pode Ser Atendido Em Qualquer Dia que estiver Disponível";}
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-item" style="grid-column: 1 / -1;">
                                <div class="info-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Descrição</div>
                                    <div class="info-value"><?php echo $rowEdit1['descricao']; ?></div>
                                </div>
                            </div>
                                       <?php if($rowEdit1['servicos'] <> ''){ ?>
                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <div class="info-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Serviços Escolhidos</div>
                                        <div class="info-value"><?php echo $rowEdit1['servicos']; ?></div>
                                    </div>
                                
                                </div>
                             <?php } ?>
                        </div>
                    </div>
                </div>

            <?php } ?>
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
    </script>

<script>
(function() {
    let badgeCount = <?php echo (int)$_badgePerdidos; ?>;

    function atualizarBadge() {
        const el = document.querySelector('a[href="meus-orcamentos-perdidos.php"] .tab-badge');
        if (badgeCount > 0) {
            if (el) { el.textContent = badgeCount; }
            else {
                const link = document.querySelector('a[href="meus-orcamentos-perdidos.php"]');
                if (link) {
                    const span = document.createElement('span');
                    span.className = 'tab-badge';
                    span.textContent = badgeCount;
                    link.appendChild(span);
                }
            }
        } else {
            if (el) el.remove();
        }
    }

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;
            const card = entry.target;
            if (card.dataset.visto === '1') return;
            card.dataset.visto = '1';
            observer.unobserve(card);
            fetch('marcar-visto.php?codpedido=' + encodeURIComponent(card.dataset.codpedido))
                .then(r => r.json())
                .then(data => {
                    if (data.decremented) {
                        badgeCount = Math.max(0, badgeCount - 1);
                        atualizarBadge();
                    }
                })
                .catch(function(){});
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.order-card[data-codpedido][data-visto="0"]').forEach(function(card) {
        observer.observe(card);
    });
})();
</script>

<?php include('bottom-nav.php'); ?>
</body>
</html>
