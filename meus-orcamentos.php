<?php //meus-orcamentos
     require("send.php");
     include('badge-counts.php');
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);

if(isset($_GET['codpedido']) && isset($_GET['contraproposta'])){
//Aguarde enquanto enviamos para o cliente
  echo "<script>alert('Contraproposta enviada com sucesso!')</script>";
  $texto = urlencode($_GET['contraproposta']);
  $minimo = $_GET['minimo'];
  $maximo = $_GET['maximo'];
  echo "<script>window.location.href='".$urlserver."aceita-orcamento.php?codigo=".$_GET['codpedido']."&contraproposta=$texto&maximo=$maximo&minimo=$minimo';</script>";
}    

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Orçamentos - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
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

        /* Using USERVICE header styling from edicao.php */
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

        /* Menu lateral identical to edicao.php */
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

        /* Tabs adapted for vertical layout with "Aceitos" as active */
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
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
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

            /* ====== CARD (igual ao cliente) ====== */
            .order-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
            position: relative;
            }

            .order-card:hover { transform: translateY(-2px); }

            .order-card.status-1 { border-left: 4px solid #2d4a6b; }
            .order-card.status-2 { border-left: 4px solid #fef0c7; }
            .order-card.status-3 { border-left: 4px solid #f28c38; }
            .order-card.status-4 { border-left: 4px solid #90ee90; }

            .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            }

            .order-number {
            font-size: 19px;
            font-weight: bold;
            color: #1a2332;
            }

            .status-badge {
            padding: 5px 11px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 2px;
            }

            .status-badge.status-1 { background: #2d4a6b; color: #fff; }
            .status-badge.status-2 { background: #fef0c7; color: #855d00; }
            .status-badge.status-3 { background: #f28c38; color: #fff; }
            .status-badge.status-4 { background: #90ee90; color: #1a2332; }

            .order-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 13px;
            margin-bottom: 16px;
            }

            @media (min-width: 480px) {
            .order-info { grid-template-columns: 1fr 1fr; }
            }

            .info-item { display: flex; align-items: flex-start; gap: 8px; }

            .info-icon {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: white;
            flex-shrink: 0;
            margin-top: 2px;
            }

            .icon-calendar { background: #17a2b8; }
            .icon-clock { background: #ffc107; }
            .icon-service { background: #6f42c1; }

            .info-label {
            color: #666;
            font-size: 11px;
            margin-bottom: 2px;
            font-weight: 600;
            text-transform: uppercase;
            }

            .info-value {
            color: #1a2332;
            font-weight: 500;
            font-size: 13px;
            line-height: 1.2;
            }

            .description {
            margin: 16px 0;
            padding: 13px;
            background: rgba(0, 212, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(0, 212, 255, 0.1);
            }

            .description-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 600;
            }

            .description-text {
            font-size: 13px;
            color: #1a2332;
            line-height: 1.3;
            }

            /* Botões no mesmo “clima” do cliente */
            .order-actions { margin-top: 13px; }

            .action-buttons{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            justify-content:center;
            }

            .action-button{
            padding:10px 18px;
            border-radius:8px;
            font-size:15px;
            font-weight:700;
            cursor:pointer;
            border:none;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
            transition:transform .2s ease;
            }

            .action-button:hover{ transform: translateY(-1px); }

            .action-button.contraproposta{ background:#dc3545; color:#fff; }
            .action-button.recusar{ background:#fff; color:#dc3545; border:1px solid #dc3545; }
            .action-button.aceitar{ background:#28a745; color:#fff; }
            .action-button.local{ background:#00d4ff; color:#1a2332; }

        .action-btn {
            padding: 13px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            border: none;
        }

        .action-btn svg {
            width: 16px;
            height: 16px;
        }

        .btn-contraproposta {
            background: #dc3545;
            color: white;
        }

        .btn-contraproposta:hover {
            background: #c82333;
        }

        .btn-recusar {
            background: white;
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .btn-recusar:hover {
            background: #f8f9fa;
        }

        .btn-aceitar {
            background: #28a745;
            color: white;
        }

        .btn-aceitar:hover {
            background: #218838;
        }

        .btn-local {
            background: #fd7e14;
            color: white;
        }

        .btn-local:hover {
            background: #e96b00;
        }

        /* Modal styling adapted for mobile */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 95%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            padding: 16px;
            border-radius: 12px 12px 0 0;
            position: relative;
        }

        .modal-header h5 {
            font-size: 19px;
            font-weight: bold;
            margin: 0;
        }

        .modal-body {
            padding: 24px;
        }

        .form-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 600;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 13px;
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 6px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s;
            min-height: 44px;
            margin-bottom: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
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
        /* Card aceito (fundo verde igual ao print 1) */
    .order-card.status-aceito{
    background: #daefdf;       /* mesmo valor do cliente */
    border-left: none;         /* no print 1 não aparece “faixa” lateral */
    }

    /* Badge “Aceito” sem parecer pílula (no print 1 é quase só texto) */
    .status-badge.status-aceito{
    background: transparent;   /* deixa “sumir” a pílula */
    padding: 0;                /* fica só o texto */
    color: #28a745;
    font-weight: 700;
    }

    /* Descrição mais “suave”, parecido com o print 1 */
    .order-card.status-aceito .description{
    background: rgba(255,255,255,0.35);
    border: 1px solid rgba(40,167,69,0.18);
    }

    </style>

</head>
<body>
    <?php 
    // Verifica login - aceita login antigo de prestador OU novo unificado
    $estaLogado = false;
    $ehPrestadorReal = false;
    $idPrestadorCheck = '';

    // 1) Login unificado novo (CPF/CNPJ + senha)
    if (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') {
        $estaLogado = true;
        // Tenta achar parceiro pelo CPF/CNPJ unificado
        $cpfCnpjLimpoChk = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
        if ($cpfCnpjLimpoChk !== '') {
            $cpfCnpjLimpoChk = mysqli_real_escape_string($con, $cpfCnpjLimpoChk);
            $qP = mysqli_query($con, "SELECT id FROM parceiro WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjLimpoChk' LIMIT 1");
            if ($qP && $rP = mysqli_fetch_array($qP)) {
                $idPrestadorCheck = (int)$rP['id'];
                $ehPrestadorReal = true;
                // Sincroniza cookies de prestador via JS (headers ja foram enviados)
                $_COOKIE['eh_prestador'] = '1';
                $_COOKIE['id_prestador'] = $idPrestadorCheck;
                echo "<script>document.cookie='eh_prestador=1;path=/;max-age=".(30*24*3600)."';document.cookie='id_prestador=".$idPrestadorCheck.";path=/;max-age=".(30*24*3600)."';</script>";
            }
        }
    }
    // 2) Cookie antigo de prestador (login.php)
    elseif (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
        $estaLogado = true;
        $ehPrestadorReal = true;
        $qPrestCheck = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".$_COOKIE['login']."'");
        if ($qPrestCheck && $rPrestCheck = mysqli_fetch_array($qPrestCheck)) {
            $idPrestadorCheck = $rPrestCheck['id'];
        }
    }

    if (!$estaLogado) {
        echo "<script>window.location.href='login-unificado.php?retorno=meus-orcamentos.php';</script>";
        exit;
    }

    // Usuario logado mas ainda nao e prestador -> envia para tornar-prestador.php
    // (reaproveita o mesmo cadastro: nome, CPF/CNPJ, whatsapp, senha)
    if (!$ehPrestadorReal) {
        echo "<script>window.location.href='tornar-prestador.php';</script>";
        exit;
    }

    // Verifica se tem categorias cadastradas; se nao, envia para escolher
    if (!empty($idPrestadorCheck)) {
        $qCatCheck = mysqli_query($con, "SELECT COUNT(*) as total FROM categoria_prestador WHERE codcadastro='$idPrestadorCheck'");
        if ($qCatCheck && $rCatCheck = mysqli_fetch_array($qCatCheck)) {
            if ($rCatCheck['total'] == 0) {
                echo "<script>window.location.href='tornar-prestador.php';</script>";
                exit;
            }
        }
    }
    
    $navAtiva = 'servicos';
    include('header-app.php'); 
    ?>



    <div class="main-content">

        <!-- Item 7: Título do painel -->
        <div class="panel-banner" style="text-align:center;margin:8px 0 12px;padding:10px 16px;background:linear-gradient(135deg,rgba(16,185,129,0.25),rgba(16,185,129,0.12));border:1px solid rgba(16,185,129,0.4);border-radius:10px;color:#10b981;font-size:13px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;">
            🔧 Painel do Prestador
        </div>

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
            <!-- Order cards with vertical mobile layout -->
            <?php
            // Busca prestador por cookie antigo ou novo
            $loginPrestador = '';
            if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
                $loginPrestador = $_COOKIE['login'];
                $queryEdit = mysqli_query($con, "SELECT * FROM parceiro WHERE CNPJ_CPF='".$loginPrestador."'");
            } elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
                $idPrestador = $_COOKIE['id_prestador'];
                $queryEdit = mysqli_query($con, "SELECT * FROM parceiro WHERE id='".$idPrestador."'");
            } elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
                $idPrestador = $_COOKIE['id'];
                $queryEdit = mysqli_query($con, "SELECT * FROM parceiro WHERE id='".$idPrestador."'");
            }
            $rowEdit = mysqli_fetch_array($queryEdit);
            // Buscar lat/lon do prestador
            $qEndPrest = mysqli_query($con, "SELECT lat, log FROM endereco_prestador WHERE cod_cadastro='".$rowEdit['id']."' LIMIT 1");
            if ($qEndPrest && $rEndPrest = mysqli_fetch_array($qEndPrest)) {
                $rowEdit['latitude'] = $rEndPrest['lat'];
                $rowEdit['longitude'] = $rEndPrest['log'];
            }
            $hasAudioColumn = true;
            $queryEdit2 = mysqli_query($con, "
            SELECT g.titulo, p.codigo, s.titulo AS sub, p.local, p.tempo, p.descricao, p.lat, p.log, p.data_hora, p.valor, p.foto_1, p.foto_2, p.foto_3, p.foto_4, p.servicos, p.audio, MIN(d.visto) as visto FROM 
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
            AND d.aceito =  'n'
            GROUP BY p.codigo
            ORDER BY p.codigo desc
            ");
            
            // Fallback: se a query falhou (ex: coluna audio nao existe), tenta sem audio
            if (!$queryEdit2) {
                $hasAudioColumn = false;
                $queryEdit2 = mysqli_query($con, "
                SELECT g.titulo, p.codigo, s.titulo AS sub, p.local, p.tempo, p.descricao, p.lat, p.log, p.data_hora, p.valor, p.foto_1, p.foto_2, p.foto_3, p.foto_4, p.servicos FROM 
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
                AND d.aceito =  'n'
                GROUP BY p.codigo
                ORDER BY p.codigo desc
                ");
            }
            
            if (!$queryEdit2 || mysqli_num_rows($queryEdit2) == 0) {
                echo '<div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3>Nenhum pedido novo</h3>
                    <p>Quando houver novos pedidos, eles aparecerão aqui.</p>
                </div>';
            }
            
            while($rowEdit1 = mysqli_fetch_array($queryEdit2)) {
            ?>
<div class="order-card status-aceito" data-codpedido="<?php echo $rowEdit1['codigo']; ?>" data-visto="<?php echo isset($rowEdit1['visto']) ? (int)$rowEdit1['visto'] : 0; ?>">
  <div class="order-header">
    <div class="order-number">Número #<?php echo $rowEdit1['codigo']; ?></div>
    <div>
      <div class="status-badge status-aceito"><?php echo $rowEdit1['titulo']; ?></div>
      <div style="font-size: 11px; color: #666; margin-top: 4px; text-align: right;">
        <?php echo $rowEdit1['titulo']; ?> → <?php echo $rowEdit1['sub']; ?>
      </div>
    </div>
  </div>

  <div class="order-info">

    <div class="info-item">
      <div class="info-icon icon-calendar">📅</div>
      <div class="info-content">
        <div class="info-label">DATA E HORA</div>
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
      <div class="info-icon icon-clock">⏰</div>
      <div class="info-content">
        <div class="info-label">TEMPO ESTIMADO</div>
        <div class="info-value"><?php echo $rowEdit1['tempo']; ?></div>
      </div>
    </div>

    <?php if($rowEdit1['servicos'] <> ''){ ?>
    <div class="info-item">
      <div class="info-icon icon-service">⚡</div>
      <div class="info-content">
        <div class="info-label">SERVIÇO ESCOLHIDOS</div>
        <!-- no cliente é "sub <br> cat" -->
        <div class="info-value"><?php echo $rowEdit1['servicos']; ?></div>
      </div>
    </div>
    <?php } ?>
  </div>

  <div class="description">
    <div class="description-label">📋 Descrição do Serviço:</div>
    <div class="description-text"><?php echo nl2br($rowEdit1['descricao']); ?></div>
  </div>

  <?php
    $fotos = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($rowEdit1['foto_'.$i])) {
            $fotoPath = $rowEdit1['foto_'.$i];
            if (strpos($fotoPath, 'fotos/') === false && strpos($fotoPath, 'http') === false) {
                $fotoPath = 'fotos/' . $fotoPath;
            }
            $fotos[] = $fotoPath;
        }
    }
    if (count($fotos) > 0) { ?>
  <div class="description">
    <div class="description-label">📷 Fotos:</div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
      <?php foreach ($fotos as $foto) { ?>
        <div onclick="abrirFoto('<?php echo $foto; ?>')" style="display: block; width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 2px solid rgba(0,212,255,0.3); cursor:pointer;">
          <img src="<?php echo $foto; ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Foto do serviço">
        </div>
      <?php } ?>
    </div>
  </div>
  <?php } ?>

  <?php if ($hasAudioColumn && !empty($rowEdit1['audio'])) { 
    // Item 18: Suporte para múltiplos áudios separados por vírgula
    $audios = explode(',', $rowEdit1['audio']);
    $audios = array_filter(array_map('trim', $audios));
    if (count($audios) > 0) {
  ?>
  <div class="description">
    <div class="description-label">🎙️ Áudio<?php echo count($audios) > 1 ? 's' : ''; ?>:</div>
    <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
      <?php foreach ($audios as $index => $audioFile) { ?>
        <div style="display: flex; align-items: center; gap: 8px;">
          <?php if (count($audios) > 1) { ?>
            <span style="font-size: 11px; color: #666; font-weight: 600;">Áudio <?php echo $index + 1; ?>:</span>
          <?php } ?>
          <audio controls style="flex: 1; height: 40px;" preload="metadata">
            <source src="audios/<?php echo htmlspecialchars($audioFile); ?>">
            Seu navegador não suporta áudio.
          </audio>
        </div>
      <?php } ?>
    </div>
  </div>
  <?php } } ?>

    <?php
    // Calcula distância em KM entre prestador e pedido
    $distanciaKm = '';
    if (!empty($rowEdit1['lat']) && !empty($rowEdit1['log']) && !empty($rowEdit['latitude']) && !empty($rowEdit['longitude'])) {
        $latFrom = deg2rad($rowEdit['latitude']);
        $lonFrom = deg2rad($rowEdit['longitude']);
        $latTo = deg2rad($rowEdit1['lat']);
        $lonTo = deg2rad($rowEdit1['log']);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distanciaKm = round($angle * 6371, 1);
    }
    ?>
    <?php if ($distanciaKm !== '') { ?>
    <div class="description">
        <div class="description-label">📍 Distância</div>
        <div class="description-text" style="font-weight:700;color:#0ea5e9;"><?php echo $distanciaKm; ?> Km</div>
    </div>
    <?php } ?>

    <div class="order-actions">
    <div class="action-buttons">

        <button type="button"
        onclick="confirmReject('<?php echo $rowEdit1['codigo']; ?>')"
        class="action-button recusar">
        ❌ Não tenho interesse
        </button>

        <a href="aceita-orcamento.php?codigo=<?php echo $rowEdit1['codigo']; ?>"
        class="action-button aceitar">
        ✅ Enviar Proposta
        </a>

    </div>
    </div>


</div>



                <!-- Modal adapted for mobile -->
                <div id="modal-<?php echo $rowEdit1['codigo']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Contraproposta - Pedido #<?php echo $rowEdit1['codigo']; ?></h5>
                            <button type="button" onclick="closeModal('modal-<?php echo $rowEdit1['codigo']; ?>')" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: #1a2332; font-size: 24px; cursor: pointer;">×</button>
                        </div>
                        <div class="modal-body">
                            <div style="margin-bottom: 16px; padding: 16px; background: rgba(0, 212, 255, 0.1); border-radius: 8px;">
                                <h6 style="font-weight: bold; margin-bottom: 8px;"><?php echo $rowEdit1['sub']; ?></h6>
                                <p style="margin: 3px 0; font-size: 14px;"><strong>Categoria:</strong> <?php echo $rowEdit1['titulo']; ?></p>
                                <p style="margin: 3px 0; font-size: 14px;"><strong>Local:</strong> <?php echo $rowEdit1['local']; ?></p>
                                <p style="margin: 3px 0; font-size: 14px;"><strong>Tempo:</strong> <?php echo $rowEdit1['tempo']; ?></p>
                                <p style="margin: 3px 0; font-size: 14px;"><strong>Descrição:</strong> <?php echo $rowEdit1['descricao']; ?></p>
                            </div>
                            
                            <form action="meus-orcamentos.php" method="get">
                                <label for="minimo<?php echo $rowEdit1['codigo']; ?>" class="form-label">Valor Mínimo Oferecido</label>
                                <input type="tel" class="form-control" id="minimo<?php echo $rowEdit1['codigo']; ?>" name="minimo" value="R$" onKeyPress="return(MascaraMoeda(this,'.',',',event))" required />
                                
                                <label for="maximo<?php echo $rowEdit1['codigo']; ?>" class="form-label">Valor Máximo</label>
                                <input type="tel" class="form-control" id="maximo<?php echo $rowEdit1['codigo']; ?>" name="maximo" value="R$" onKeyPress="return(MascaraMoeda(this,'.',',',event))" required />
                                
                                <label for="contraproposta<?php echo $rowEdit1['codigo']; ?>" class="form-label">Descreva sua Contraproposta</label>
                                <textarea class="form-control" id="contraproposta<?php echo $rowEdit1['codigo']; ?>" name="contraproposta" rows="5" required></textarea>
                                
                                <input type="hidden" name="codpedido" value="<?php echo $rowEdit1['codigo']; ?>">
                                
                                <div style="display: flex; gap: 8px; margin-top: 16px;">
                                    <button type="button" onclick="closeModal('modal-<?php echo $rowEdit1['codigo']; ?>')" style="flex: 1; padding: 13px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">Cancelar</button>
                                    <button type="submit" style="flex: 1; padding: 13px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Enviar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        function confirmReject(codpedido) {
            if (confirm('Tem certeza que não tem interesse neste pedido?')) {
                fetch('nao-tenho-interesse.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'codpedido=' + codpedido
                })
                .then(r => r.json())
                .then(resp => {
                    const card = document.querySelector('.order-card[data-codpedido="' + codpedido + '"]');
                    if (card) card.remove();
                    // Atualiza badge de novos no bottom-nav
                    const badges = document.querySelectorAll('.nav-badge');
                    badges.forEach(b => {
                        const n = parseInt(b.textContent || '0') - 1;
                        if (n <= 0) b.style.display = 'none';
                        else b.textContent = n;
                    });
                })
                .catch(() => { window.location.href = 'meus-orcamentos.php'; });
            }
        }

        // IntersectionObserver: marca card como visto quando aparece na tela
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const card = entry.target;
                        if (card.dataset.visto === '0') {
                            const codpedido = card.dataset.codpedido;
                            fetch('marcar-visto.php?codpedido=' + codpedido)
                                .then(r => r.json())
                                .then(resp => {
                                    if (resp.decremented) {
                                        card.dataset.visto = '1';
                                        // Decrementa badge do bottom-nav
                                        const navBadges = document.querySelectorAll('.nav-badge');
                                        navBadges.forEach(b => {
                                            const n = parseInt(b.textContent || '0') - 1;
                                            if (n <= 0) b.style.display = 'none';
                                            else b.textContent = n;
                                        });
                                    }
                                });
                        }
                        observer.unobserve(card);
                    }
                });
            }, {threshold: 0.3});
            document.querySelectorAll('.order-card[data-codpedido]').forEach(c => observer.observe(c));
        }

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

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Currency mask function
        function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e) {
            var sep = 0;
            var key = '';
            var i = j = 0;
            var len = len2 = 0;
            var strCheck = '0123456789';
            var aux = aux2 = '';
            var whichCode = (window.Event) ? e.which : e.keyCode;
            if (whichCode == 13) return true;
            key = String.fromCharCode(whichCode);
            if (strCheck.indexOf(key) == -1) return false;
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

<!-- Modal Lightbox para fotos -->
<div id="fotoModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:99999;flex-direction:column;align-items:center;justify-content:center;">
    <button onclick="fecharFoto()" style="position:absolute;top:12px;left:12px;background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:16px;padding:10px 18px;border-radius:8px;cursor:pointer;z-index:100000;font-weight:700;">← Voltar</button>
    <img id="fotoModalImg" src="" style="max-width:92%;max-height:85vh;border-radius:12px;object-fit:contain;" alt="Foto">
</div>
<script>
function abrirFoto(src) {
    document.getElementById('fotoModalImg').src = src;
    document.getElementById('fotoModal').style.display = 'flex';
}
function fecharFoto() {
    document.getElementById('fotoModal').style.display = 'none';
    document.getElementById('fotoModalImg').src = '';
}
document.getElementById('fotoModal').addEventListener('click', function(e) {
    if (e.target === this) fecharFoto();
});
</script>

<?php include('bottom-nav.php'); ?>
</body>
</html>
