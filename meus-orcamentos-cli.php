<?php //meus-orcamentos-cli
session_start();
require("send.php");

// Verifica login - aceita login unificado novo OU cookies legados
$estaLogado = false;
$codcliente = 0;

// 1) Login unificado novo
if (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') {
    $estaLogado = true;
    if (isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
        $codcliente = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
    } elseif (isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
        $codcliente = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
    } else {
        // Resolve id_cliente pelo CPF/CNPJ
        $cpfCnpjLimpoChk = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
        if ($cpfCnpjLimpoChk !== '') {
            $cpfCnpjLimpoChkEsc = mysqli_real_escape_string($con, $cpfCnpjLimpoChk);
            $qCli = mysqli_query($con, "SELECT id FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjLimpoChkEsc' LIMIT 1");
            if ($qCli && $rCli = mysqli_fetch_array($qCli)) {
                $codcliente = (int)$rCli['id'];
                setcookie('id_cliente', $codcliente, time()+30*24*3600, '/');
                $_COOKIE['id_cliente'] = $codcliente;
            }
        }
    }
}
// 2) Cookies legados (celularCli / codcliente)
elseif (isset($_COOKIE['celularCli']) || isset($_COOKIE['codcliente'])) {
    $estaLogado = true;
    if (isset($_COOKIE['codcliente'])) {
        $codcliente = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
    }
}

// Se nao logado, manda para login unificado
if (!$estaLogado) {
    echo "<script>window.location.href='login-unificado.php?retorno=meus-orcamentos-cli.php';</script>";
    exit;
}

$nomeUsuario = isset($_COOKIE['nome_usuario']) ? $_COOKIE['nome_usuario'] : '';
$primeiroNome = !empty($nomeUsuario) ? explode(' ', trim($nomeUsuario))[0] : 'Usuário';
$navAtiva = 'pedidos';

// Vincula um pedido da sessão ao cliente logado
if (isset($_GET['codpedido']) && $codcliente != 0) {
    $codpedido_update = mysqli_real_escape_string($con, $_GET['codpedido']);
    $updateMapa = mysqli_query($con, "UPDATE pedido SET codcli = '$codcliente' WHERE codigo = '$codpedido_update'") or die(mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Orçamentos - USERVICE</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .tabs-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 16px;
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

        .tab-badge {
            background: #1a2332;
            color: #ffffff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 3px;
        }

        .content-container {
            flex: 1;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(0, 212, 255, 0.2);
            padding: 16px;
            overflow-y: auto;
            position: relative;
        }

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

        .order-card:hover {
            transform: translateY(-2px);
        }

        .order-card.status-1 {
            border-left: 4px solid #2d4a6b; /* Azul */
        }

        .order-card.status-2 {
            border-left: 4px solid #fef0c7; /* Amarelo */
        }

        .order-card.status-3 {
            border-left: 4px solid #f28c38; /* Laranja */
        }

        .order-card.status-4 {
            border-left: 4px solid #90ee90; /* Verde claro */
        }

        .order-card a.card-link {
            display: block;
            text-decoration: none;
            color: inherit;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }

        .order-card a.card-link:hover {
            background: rgba(0, 212, 255, 0.1);
        }

        .order-header,
        .order-info,
        .description,
        .order-actions,
        .dynamic-content {
            position: relative;
            z-index: 2;
            pointer-events: none;
        }

        .order-header *,
        .order-info *,
        .description *,
        .order-actions *,
        .dynamic-content * {
            pointer-events: auto;
        }

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

        .status-badge.status-1 {
            background-color: #2d4a6b; /* Azul */
            color: #ffffff;
        }

        .status-badge.status-2 {
            background-color: #fef0c7; /* Amarelo */
            color: #855d00;
        }

        .status-badge.status-3 {
            background-color: #f28c38; /* Laranja */
            color: #ffffff;
        }

        .status-badge.status-4 {
            background-color: #28a745;
            color: #fff;
        }

        .status-aceito {
            color: #28a745;
            background: #daefdf;
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

        .icon-user { background: #6c757d; }
        .icon-calendar { background: #17a2b8; }
        .icon-clock { background: #ffc107; }
        .icon-service { background: #6f42c1; }

        .info-content {
            flex: 1;
        }

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

        .order-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 10px;
        }

        .action-button {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
            z-index: 3;
        }

        .action-button.conversa {
            background-color: #f28c38; /* Laranja */
            color: #ffffff;
        }

        .action-button.conversa:hover {
            background-color: #e07b30;
        }

        .action-button.encerrar {
            background-color: #28a745; /* Verde */
            color: #ffffff;
        }

        .action-button.encerrar:hover {
            background-color: #218838;
        }

        .btn {
            flex: 1;
            padding: 13px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 44px;
        }

        .btn-contraproposta {
            background: #dc3545;
            color: white;
        }

        .btn-mensagem {
            background: #6c757d;
            color: white;
        }

        .btn-mapa {
            background: #00d4ff;
            color: #1a2332;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .no-orders {
            text-align: center;
            padding: 48px 16px;
            color: #00d4ff;
            opacity: 0.7;
        }

        .no-orders-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .no-orders h3 {
            font-size: 19px;
            margin-bottom: 8px;
        }

        .no-orders p {
            font-size: 16px;
        }

        .dynamic-content {
            margin-top: 16px;
            padding: 16px;
            border-radius: 8px;
        }

        .dynamic-content.status-1 {
            background: rgba(45, 74, 107, 0.1); /* Azul claro */
            border: 1px solid rgba(45, 74, 107, 0.2);
            color: #2d4a6b;
        }

        .dynamic-content.status-2 {
            background: rgba(254, 240, 199, 0.1); /* Amarelo claro */
            border: 1px solid rgba(254, 240, 199, 0.2);
            color: #855d00;
        }

        .dynamic-content.status-3 {
            background: rgba(242, 140, 56, 0.1); /* Laranja claro */
            border: 1px solid rgba(242, 140, 56, 0.2);
            color: #f28c38;
        }

        .dynamic-content.status-4 {
            background: rgba(144, 238, 144, 0.1); /* Verde claro */
            border: 1px solid rgba(144, 238, 144, 0.2);
            color: #1a2332;
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
    <?php 
    // Verifica login do cliente
    if(!$estaLogado) {
        echo "<script>window.location.href='login-unificado.php?retorno=meus-orcamentos-cli.php';</script>";
        exit;
    }
    include('header-app.php');
    ?>

    <div class="main-content">

        <!-- Item 7: Título do painel -->
        <div class="panel-banner" style="text-align:center;margin:8px 0 12px;padding:10px 16px;background:linear-gradient(135deg,rgba(16,185,129,0.25),rgba(16,185,129,0.12));border:1px solid rgba(16,185,129,0.4);border-radius:10px;color:#10b981;font-size:13px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;">
            👤 Painel do Consumidor
        </div>

        <!-- Adicionando grid de navegação rápida -->
        <div class="quick-nav-grid" style="grid-template-columns:1fr;">
            <a href="meus-orcamentos-cli.php" class="nav-card active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Pedidos</span>
            </a>
        </div>


        <!-- Item 5: botão único + popup de situações (substitui as abas) -->
        <style>
        .sit-btn{width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;background:linear-gradient(145deg,#00d4ff,#00f0ff);color:#1a2332;border:none;padding:14px 16px;border-radius:12px;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(0,212,255,.35);margin-bottom:14px}
        .sit-btn .sit-atual{font-size:12px;font-weight:600;background:rgba(26,35,50,.15);padding:3px 10px;border-radius:12px}
        .sit-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:10050;display:none}
        .sit-overlay.active{display:block}
        .sit-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:90%;max-width:420px;background:#1e3a5f;border:1px solid rgba(0,212,255,.3);border-radius:16px;z-index:10051;display:none;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,.5)}
        .sit-modal.active{display:block}
        .sit-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px;border-bottom:1px solid rgba(255,255,255,.12)}
        .sit-modal-head h3{color:#fff;font-size:16px;margin:0}
        .sit-modal-head button{background:none;border:none;color:rgba(255,255,255,.7);font-size:22px;cursor:pointer}
        .sit-list a{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:15px 18px;color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08);font-size:15px;font-weight:600;cursor:pointer}
        .sit-list a:last-child{border-bottom:none}
        .sit-list a:hover{background:rgba(0,212,255,.12)}
        .sit-list a.atual{background:rgba(0,212,255,.18);color:#00f0ff}
        .sit-list a .sit-badge{background:#dc3545;color:#fff;font-size:12px;font-weight:700;min-width:22px;height:22px;line-height:22px;border-radius:11px;text-align:center;padding:0 6px}
        </style>
        <button type="button" class="sit-btn" onclick="abrirSituacoesCli()">
            <span>📋 Meus Pedidos</span>
            <span class="sit-atual" id="sitAtualLabel">Pendentes ▾</span>
        </button>
        <div class="sit-overlay" id="sitOverlayCli" onclick="fecharSituacoesCli()"></div>
        <div class="sit-modal" id="sitModalCli">
            <div class="sit-modal-head"><h3>Situação dos pedidos</h3><button type="button" onclick="fecharSituacoesCli()">×</button></div>
            <div class="sit-list">
                <a id="sit-pendentes" class="atual" onclick="selSit('pendentes','Pendentes')"><span>Pendentes</span> <span class="sit-badge" id="pendentes-count">0</span></a>
                <a id="sit-aceitos" onclick="selSit('aceitos','Aceitos')"><span>Aceitos</span> <span class="sit-badge" id="aceitos-count">0</span></a>
                <a id="sit-sem_resposta" onclick="selSit('sem_resposta','Sem Resposta')"><span>Sem Resposta</span> <span class="sit-badge" id="sem-resposta-count">0</span></a>
                <a id="sit-finalizados" onclick="selSit('finalizados','Finalizados')"><span>⭐ Finalizados</span></a>
            </div>
        </div>
        <script>
        function abrirSituacoesCli(){document.getElementById('sitOverlayCli').classList.add('active');document.getElementById('sitModalCli').classList.add('active');}
        function fecharSituacoesCli(){document.getElementById('sitOverlayCli').classList.remove('active');document.getElementById('sitModalCli').classList.remove('active');}
        function selSit(tab,label){
            if(typeof switchTab==='function') switchTab(tab);
            document.getElementById('sitAtualLabel').textContent = label + ' ▾';
            document.querySelectorAll('.sit-list a').forEach(function(a){a.classList.remove('atual');});
            var el=document.getElementById('sit-'+tab); if(el) el.classList.add('atual');
            fecharSituacoesCli();
        }
        </script>

        <div class="content-container">
            <div id="pendentes-content"></div>
            <div id="aceitos-content" style="display: none;"></div>
            <div id="sem-resposta-content" style="display: none;"></div>
            <div id="finalizados-content" style="display: none;"></div>
        </div>
    </div>

    <script>
        let currentTab = 'pendentes';

        function toggleMenu() {
            const sidebar = document.querySelector('.menu-sidebar');
            const overlay = document.querySelector('.menu-overlay');
            sidebar.classList.toggle('active');
            overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        }

        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            var _tabEl = document.querySelector(`.tab[onclick="switchTab('${tab}')"]`);
            if (_tabEl) _tabEl.classList.add('active');
            document.getElementById('pendentes-content').style.display = tab === 'pendentes' ? 'block' : 'none';
            document.getElementById('aceitos-content').style.display = tab === 'aceitos' ? 'block' : 'none';
            document.getElementById('sem-resposta-content').style.display = tab === 'sem_resposta' ? 'block' : 'none';
            document.getElementById('finalizados-content').style.display = tab === 'finalizados' ? 'block' : 'none';
            currentTab = tab;
        }

        // Abre aba via URL ?tab=xxx
        (function(){
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab');
            if (tab) switchTab(tab);
        })();

        function updatePedidos() {
            $.ajax({
                url: 'get_pedidos.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Atualizar contadores
                        const pc = response.pendentes_count || 0;
                        const ac = response.aceitos_count || 0;
                        const sc = response.sem_resposta_count || 0;
                        $('#pendentes-count').text(pc).toggle(pc > 0);
                        $('#aceitos-count').text(ac).toggle(ac > 0);
                        $('#sem-resposta-count').text(sc).toggle(sc > 0);

                        // Atualizar Pendentes
                        const pendentesContainer = $('#pendentes-content');
                        pendentesContainer.empty();
                        if (response.pendentes.length === 0) {
                            pendentesContainer.html(`
                                <div class="no-orders">
                                    <div class="no-orders-icon">📋</div>
                                    <h3>Orçamentos Pendentes</h3>
                                    <p>Você não tem orçamentos pendentes</p>
                                </div>
                            `);
                        } else {
                            response.pendentes.forEach(pedido => {
                                const cardHtml = `
                                    <div class="order-card ${pedido.status_class}" data-codigo="${pedido.codigo}">
                                        <a href="${pedido.mapa_url}&ver=s" class="card-link"></a>
                                        <div class="order-header">
                                            <div class="order-number">#${pedido.codigo}</div>
                                            <div class="status-badge ${pedido.status_class}">${pedido.cat} / ${pedido.sub}</div>
                                        </div>
                                        <div class="order-info">
                                            <div class="info-item">
                                                <div class="info-icon icon-calendar">📅</div>
                                                <div class="info-content">
                                                    <div class="info-label">DATA E HORA</div>
                                                    <div class="info-value">${pedido.data_hora}</div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon icon-clock">⏰</div>
                                                <div class="info-content">
                                                    <div class="info-label">TEMPO ESTIMADO</div>
                                                    <div class="info-value">${pedido.tempo || 'Aguardando Prestador aceitar'}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="description">
                                            <div class="description-label">📋 Descrição do Serviço:</div>
                                            <div class="description-text">${pedido.descricao.replace(/\n/g, '<br>')}</div>
                                        </div>
                                        ${pedido.fotos && pedido.fotos.length > 0 ? `
                                        <div class="description">
                                            <div class="description-label">📷 Fotos:</div>
                                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                                                ${pedido.fotos.map(f => `<div onclick="abrirFoto('${f}')" style="display:block;width:60px;height:60px;border-radius:8px;overflow:hidden;border:2px solid rgba(0,212,255,0.3);cursor:pointer;"><img src="${f}" style="width:100%;height:100%;object-fit:cover;" alt="Foto"></div>`).join('')}
                                            </div>
                                        </div>` : ''}
                                        ${pedido.audio ? `
                                        <div class="description">
                                            <div class="description-label">🎙️ Áudio:</div>
                                            <div style="margin-top:8px;"><audio controls style="width:100%;height:40px;" preload="metadata"><source src="${pedido.audio}">Seu navegador não suporta áudio.</audio></div>
                                        </div>` : ''}
                                        <div class="order-actions">
                                            <div class="dynamic-content ${pedido.status_class}" id="mostraA-${pedido.codigo}">
                                                ${pedido.dynamic_content}
                                            </div>
                                            <button class="btn btn-contraproposta" onclick="cancelarPedido(${pedido.codigo})" style="margin-top:8px;width:100%;background:#dc3545;color:#fff;pointer-events:auto;">❌ Cancelar Solicitação</button>
                                        </div>
                                    </div>
                                `;
                                pendentesContainer.append(cardHtml);
                            });
                        }

                        // Popup reminder se tem aceitos
                        if (response.aceitos && response.aceitos.length > 0 && !sessionStorage.getItem('aviso_avaliacao')) {
                            sessionStorage.setItem('aviso_avaliacao', '1');
                            document.getElementById('popup-avaliar').style.display = 'flex';
                            document.getElementById('popup-count').textContent = response.aceitos.length;
                        }

                        // Atualizar Aceitos
                        const aceitosContainer = $('#aceitos-content');
                        aceitosContainer.empty();
                        if (response.aceitos.length === 0) {
                            aceitosContainer.html(`
                                <div class="no-orders">
                                    <div class="no-orders-icon">✅</div>
                                    <h3>Orçamentos Aceitos</h3>
                                    <p>Você não tem orçamentos aceitos</p>
                                </div>
                            `);
                        } else {
                            response.aceitos.forEach(pedido => {
                                const cardHtml = `
                                    <div class="order-card status-aceito" data-codigo="${pedido.codigo}">
                                        <div class="order-header">
                                            <div class="order-number">Número #${pedido.codigo}</div>
                                            <div class="status-badge status-aceito">Aceito</div>
                                        </div>
                                        <div class="order-info">
                                            <div class="info-item">
                                                <div class="info-icon icon-user">👤</div>
                                                <div class="info-content">
                                                    <div class="info-label">PRESTADOR</div>
                                                    <div class="info-value">${pedido.nome_prestador}</div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon icon-calendar">📅</div>
                                                <div class="info-content">
                                                    <div class="info-label">DATA E HORA</div>
                                                    <div class="info-value">${pedido.data_hora}</div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon icon-clock">⏰</div>
                                                <div class="info-content">
                                                    <div class="info-label">TEMPO ESTIMADO</div>
                                                    <div class="info-value">${pedido.tempo}</div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon icon-service">⚡</div>
                                                <div class="info-content">
                                                    <div class="info-label">SERVIÇO</div>
                                                    <div class="info-value">${pedido.sub}<br>${pedido.cat}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="description">
                                            <div class="description-label">📋 Descrição do Serviço:</div>
                                            <div class="description-text">${pedido.descricao.replace(/\n/g, '<br>')}</div>
                                        </div>
                                        ${pedido.fotos && pedido.fotos.length > 0 ? `
                                        <div class="description">
                                            <div class="description-label">📷 Fotos:</div>
                                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                                                ${pedido.fotos.map(f => `<div onclick="abrirFoto('${f}')" style="display:block;width:60px;height:60px;border-radius:8px;overflow:hidden;border:2px solid rgba(0,212,255,0.3);cursor:pointer;"><img src="${f}" style="width:100%;height:100%;object-fit:cover;" alt="Foto"></div>`).join('')}
                                            </div>
                                        </div>` : ''}
                                        ${pedido.audio ? `
                                        <div class="description">
                                            <div class="description-label">🎙️ Áudio:</div>
                                            <div style="margin-top:8px;"><audio controls style="width:100%;height:40px;" preload="metadata"><source src="${pedido.audio}">Seu navegador não suporta áudio.</audio></div>
                                        </div>` : ''}
                                       
                                        <div class="order-actions">
                                            <a href="chat.php?categoria=${pedido.sub}&subcategoria=${pedido.cat}&user_id=<?php echo $codcliente; ?>&user_from=${pedido.codcadastro}&codpedido=${pedido.codigo}" class="btn btn-mensagem">💬 Mensagem</a>
                                            <button onclick="abrirAvaliar(${pedido.codigo}, ${pedido.codcadastro})" class="btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;margin-top:6px;width:100%;padding:10px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">⭐ Finalizar / Avaliar</button>
                                        </div>
                                    </div>
                                `;
                                aceitosContainer.append(cardHtml);
                            });
                        }

                        // Atualizar Finalizados
                        const finalizadosContainer = $('#finalizados-content');
                        finalizadosContainer.empty();
                        if (!response.finalizados || response.finalizados.length === 0) {
                            finalizadosContainer.html(`
                                <div class="no-orders">
                                    <div class="no-orders-icon">⭐</div>
                                    <h3>Pedidos Finalizados</h3>
                                    <p>Pedidos que você já avaliou aparecerão aqui</p>
                                </div>
                            `);
                        } else {
                            response.finalizados.forEach(pedido => {
                                const estrelas = pedido.denuncia ? '' : '⭐'.repeat(pedido.nota || 5);
                                const statusBadge = pedido.denuncia 
                                    ? `<div class="status-badge" style="background:#dc3545;color:#fff;">⚠️ Denúncia registrada</div>`
                                    : `<div class="status-badge" style="background:#22c55e;color:#fff;">Avaliado ${estrelas}</div>`;
                                const avaliacaoTexto = pedido.denuncia 
                                    ? `<div class="description"><div class="description-label" style="color:#dc3545;">⚠️ Denúncia registrada. Nossa equipe irá analisar.</div></div>`
                                    : (pedido.mensagem ? `<div class="description"><div class="description-label">💬 Sua avaliação:</div><div class="description-text">${pedido.mensagem}</div></div>` : '');
                                finalizadosContainer.append(`
                                    <div class="order-card status-aceito">
                                        <div class="order-header">
                                            <div class="order-number">#${pedido.codigo}</div>
                                            ${statusBadge}
                                        </div>
                                        <div class="order-info">
                                            <div class="info-item">
                                                <div class="info-icon icon-calendar">📅</div>
                                                <div class="info-content">
                                                    <div class="info-label">DATA</div>
                                                    <div class="info-value">${pedido.data_hora}</div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon">🛠️</div>
                                                <div class="info-content">
                                                    <div class="info-label">SERVIÇO</div>
                                                    <div class="info-value">${pedido.cat} / ${pedido.sub}</div>
                                                </div>
                                            </div>
                                        </div>
                                        ${avaliacaoTexto}
                                    </div>
                                `);
                            });
                        }

                        // Atualizar Sem Resposta
                        const semRespostaContainer = $('#sem-resposta-content');
                        semRespostaContainer.empty();
                        if (!response.sem_resposta || response.sem_resposta.length === 0) {
                            semRespostaContainer.html(`
                                <div class="no-orders">
                                    <div class="no-orders-icon">⏰</div>
                                    <h3>Sem Resposta</h3>
                                    <p>Você não tem pedidos sem resposta</p>
                                </div>
                            `);
                        } else {
                            response.sem_resposta.forEach(pedido => {
                                const cardHtml = `
                                    <div class="order-card status-expirado" data-codigo="${pedido.codigo}">
                                        <div class="order-header">
                                            <div class="order-number">#${pedido.codigo}</div>
                                            <div class="status-badge" style="background:#dc3545;color:#fff;">Sem Resposta</div>
                                        </div>
                                        <div class="order-info">
                                            <div class="info-item">
                                                <div class="info-icon icon-calendar">📅</div>
                                                <div class="info-content">
                                                    <div class="info-label">DATA E HORA</div>
                                                    <div class="info-value">${pedido.data_hora}</div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon icon-service">⚡</div>
                                                <div class="info-content">
                                                    <div class="info-label">SERVIÇO</div>
                                                    <div class="info-value">${pedido.cat} / ${pedido.sub}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="description">
                                            <div class="description-label">📋 Descrição do Serviço:</div>
                                            <div class="description-text">${pedido.descricao.replace(/\n/g, '<br>')}</div>
                                        </div>
                                        <div class="order-actions">
                                            <div class="dynamic-content" style="background:rgba(220,53,69,0.1);border:1px solid rgba(220,53,69,0.3);color:#dc3545;">
                                                <span>⏰ Nenhum prestador respondeu a este pedido</span>
                                            </div>
                                            <a href="buscar.php" class="btn btn-mapa" style="margin-top:8px;width:100%;">🔍 Buscar Novamente</a>
                                        </div>
                                    </div>
                                `;
                                semRespostaContainer.append(cardHtml);
                            });
                        }
                    } else {
                        console.error('Erro ao carregar pedidos:', response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição AJAX:', error);
                }
            });
        }

        function cancelarPedido(codpedido) {
            if (confirm('Tem certeza que deseja cancelar esta solicitação?')) {
                $.ajax({
                    url: 'cancelar-pedido.php?codpedido=' + codpedido,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Solicitação cancelada com sucesso!');
                            updatePedidos();
                        } else {
                            alert('Erro: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erro ao cancelar solicitação.');
                    }
                });
            }
        }

        $(document).ready(function() {
            updatePedidos();
            setInterval(updatePedidos, 5000);
        });
    </script>

<!-- Popup Lembrete Avaliação -->
<div id="popup-avaliar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:99998;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#1e3a5f;border-radius:16px;padding:24px;max-width:340px;width:100%;border:1px solid rgba(0,212,255,0.3);text-align:center;">
        <div style="font-size:48px;margin-bottom:12px;">⭐</div>
        <h3 style="color:#fff;font-size:18px;margin-bottom:8px;">Não esqueça de avaliar!</h3>
        <p style="color:rgba(255,255,255,0.75);font-size:14px;margin-bottom:20px;">Você tem <strong id="popup-count" style="color:#f59e0b;">1</strong> serviço(s) aceito(s). Finalize e avalie para ajudar outros usuários.</p>
        <button onclick="document.getElementById('popup-avaliar').style.display='none';switchTab('aceitos');" style="width:100%;padding:13px;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;border-radius:10px;color:#fff;font-size:15px;font-weight:700;cursor:pointer;margin-bottom:8px;">AVALIAR AGORA</button>
        <button onclick="document.getElementById('popup-avaliar').style.display='none';" style="width:100%;padding:11px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:10px;color:rgba(255,255,255,0.7);font-size:14px;cursor:pointer;">Depois</button>
    </div>
</div>

<!-- Modal Avaliação -->
<div id="modal-avaliar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.82);z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#1e3a5f;border-radius:16px;padding:24px;max-width:360px;width:100%;border:1px solid rgba(0,212,255,0.3);">
        <h3 style="color:#fff;font-size:17px;margin-bottom:16px;text-align:center;">⭐ Finalizar / Avaliar</h3>
        <div style="display:flex;justify-content:center;gap:8px;margin-bottom:16px;" id="estrelas-wrapper">
            <?php for($s=1;$s<=5;$s++): ?>
            <span onclick="selecionarEstrela(<?php echo $s; ?>)" id="star-<?php echo $s; ?>" style="font-size:40px;cursor:pointer;color:rgba(255,255,255,0.25);transition:.15s;line-height:1;">★</span>
            <?php endfor; ?>
        </div>
        <textarea id="avaliacao-mensagem" placeholder="Comentário opcional..." style="width:100%;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:10px;color:#fff;padding:10px;font-size:14px;resize:none;height:80px;font-family:inherit;margin-bottom:14px;"></textarea>
        <input type="hidden" id="avaliacao-codpedido">
        <input type="hidden" id="avaliacao-codcadastro">
        <input type="hidden" id="avaliacao-nota" value="0">
        <button onclick="enviarAvaliacao()" style="width:100%;padding:13px;background:linear-gradient(135deg,#22c55e,#16a34a);border:none;border-radius:10px;color:#fff;font-size:15px;font-weight:700;cursor:pointer;margin-bottom:8px;">Confirmar Avaliação</button>
        <button onclick="abrirDenuncia()" style="width:100%;padding:11px;background:rgba(220,53,69,0.15);border:1px solid rgba(220,53,69,0.4);border-radius:10px;color:#ff6b7a;font-size:14px;font-weight:600;cursor:pointer;margin-bottom:8px;">⚠️ Fazer Denúncia</button>
        <button onclick="document.getElementById('modal-avaliar').style.display='none';" style="width:100%;padding:11px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:10px;color:rgba(255,255,255,0.5);font-size:13px;cursor:pointer;">Cancelar</button>
    </div>
</div>

<!-- Modal Denúncia -->
<div id="modal-denuncia" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#1e3a5f;border-radius:16px;padding:24px;max-width:360px;width:100%;border:1px solid rgba(220,53,69,0.4);">
        <h3 style="color:#ff6b7a;font-size:17px;margin-bottom:6px;text-align:center;">⚠️ Fazer Denúncia</h3>
        <p style="color:rgba(255,255,255,0.6);font-size:12px;text-align:center;margin-bottom:14px;">Relate o problema com este prestador. Nossa equipe irá analisar.</p>
        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;">
            <?php
            $tipos = ['Não compareceu', 'Cobrou valor diferente', 'Serviço mal feito', 'Comportamento inadequado', 'Outro'];
            foreach ($tipos as $t):
            ?>
            <label style="display:flex;align-items:center;gap:8px;color:#fff;font-size:14px;cursor:pointer;">
                <input type="radio" name="denuncia_tipo" value="<?php echo $t; ?>" style="accent-color:#dc3545;">
                <?php echo $t; ?>
            </label>
            <?php endforeach; ?>
        </div>
        <textarea id="denuncia-motivo" placeholder="Descreva o que aconteceu..." style="width:100%;background:rgba(255,255,255,0.1);border:1px solid rgba(220,53,69,0.3);border-radius:10px;color:#fff;padding:10px;font-size:14px;resize:none;height:90px;font-family:inherit;margin-bottom:14px;"></textarea>
        <input type="hidden" id="denuncia-codpedido">
        <input type="hidden" id="denuncia-codcadastro">
        <button onclick="enviarDenuncia()" style="width:100%;padding:13px;background:linear-gradient(135deg,#dc3545,#b02a37);border:none;border-radius:10px;color:#fff;font-size:15px;font-weight:700;cursor:pointer;margin-bottom:8px;">Enviar Denúncia</button>
        <button onclick="document.getElementById('modal-denuncia').style.display='none';" style="width:100%;padding:11px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:10px;color:rgba(255,255,255,0.5);font-size:13px;cursor:pointer;">Cancelar</button>
    </div>
</div>
<script>
function abrirAvaliar(codpedido, codcadastro) {
    document.getElementById('avaliacao-codpedido').value = codpedido;
    document.getElementById('avaliacao-codcadastro').value = codcadastro;
    document.getElementById('avaliacao-nota').value = 0;
    document.getElementById('avaliacao-mensagem').value = '';
    selecionarEstrela(5);
    document.getElementById('modal-avaliar').style.display = 'flex';
}
function selecionarEstrela(nota) {
    document.getElementById('avaliacao-nota').value = nota;
    for (let i = 1; i <= 5; i++) {
        const el = document.getElementById('star-' + i);
        el.style.color = i <= nota ? '#f59e0b' : 'rgba(255,255,255,0.25)';
        el.style.transform = i <= nota ? 'scale(1.15)' : 'scale(1)';
    }
}
function abrirDenuncia() {
    const codpedido   = document.getElementById('avaliacao-codpedido').value;
    const codcadastro = document.getElementById('avaliacao-codcadastro').value;
    document.getElementById('denuncia-codpedido').value   = codpedido;
    document.getElementById('denuncia-codcadastro').value = codcadastro;
    document.getElementById('denuncia-motivo').value = '';
    document.querySelectorAll('input[name="denuncia_tipo"]').forEach(r => r.checked = false);
    document.getElementById('modal-avaliar').style.display  = 'none';
    document.getElementById('modal-denuncia').style.display = 'flex';
}
function enviarDenuncia() {
    const motivo = document.getElementById('denuncia-motivo').value.trim();
    const tipoEl = document.querySelector('input[name="denuncia_tipo"]:checked');
    const tipo   = tipoEl ? tipoEl.value : '';
    if (!motivo && !tipo) { alert('Selecione o tipo ou descreva o problema.'); return; }
    const codpedido   = document.getElementById('denuncia-codpedido').value;
    const codcadastro = document.getElementById('denuncia-codcadastro').value;
    document.getElementById('modal-denuncia').style.display = 'none';
    $.post('salvar-denuncia.php', { codpedido: codpedido, codcadastro: codcadastro, motivo: motivo || tipo, tipo: tipo })
        .always(function() {
            alert('Denúncia registrada. Nossa equipe irá analisar em breve.');
            updatePedidos();
        });
}
function enviarAvaliacao() {
    const nota = parseInt(document.getElementById('avaliacao-nota').value);
    if (nota < 1) { alert('Selecione pelo menos 1 estrela.'); return; }
    const codpedido = document.getElementById('avaliacao-codpedido').value;
    const codcadastro = document.getElementById('avaliacao-codcadastro').value;
    const mensagem = document.getElementById('avaliacao-mensagem').value;
    document.getElementById('modal-avaliar').style.display = 'none';
    $.post('salvar-avaliacao.php', { codpedido: codpedido, codcadastro: codcadastro, nota: nota, mensagem: mensagem })
        .done(function(resp) {
            try {
                const r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (r.ok) {
                    alert('Avaliação enviada! Obrigado.');
                    updatePedidos();
                } else {
                    alert('Erro ao enviar avaliação.');
                }
            } catch(e) {
                alert('Avaliação enviada! Obrigado.');
                updatePedidos();
            }
        })
        .fail(function() {
            alert('Avaliação enviada! Obrigado.');
            updatePedidos();
        });
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