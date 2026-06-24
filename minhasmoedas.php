<?php 
require("send.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Moedas - USERVICE</title>
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
            padding-bottom: 60px;
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

        /* Menu lateral identical to other pages */
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

        /* Main content layout */
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

        /* Content card */
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Success message styling */
        .success-message {
            background: rgba(65, 163, 14, 0.1);
            border: 1px solid rgba(65, 163, 14, 0.3);
            color: #41a30e;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 600;
        }

        /* Coin display */
        .coin-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin: 32px 0;
        }

        .coin-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(145deg, #ffd700, #ffed4e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }

        .coin-amount {
            font-size: 48px;
            font-weight: bold;
            color: #28a745;
        }

        .coin-label {
            font-size: 19px;
            color: #1a2332;
            margin-left: 8px;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 32px;
        }

        .action-button {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 212, 255, 0.4);
        }

        .action-button.secondary {
            background: linear-gradient(145deg, #28a745, #34ce57);
            color: white;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .action-button.secondary:hover {
            box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
        }

        @media (min-width: 480px) {
            .action-buttons {
                flex-direction: row;
                justify-content: center;
            }
            
            .action-button {
                flex: 1;
                max-width: 250px;
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
    <?php $navAtiva = 'servicos'; include('header-app.php'); ?>


    <div class="main-content">

        <div class="content-card">
            <?php if(isset($_GET['qtd'])) { ?>
                <div class="success-message">
                    Foram Creditados <strong><?php echo $_GET['qtd']; ?> Moedas</strong>
                </div>
                <script>
                    setTimeout(function() {
                        const message = document.querySelector('.success-message');
                        if (message) message.style.display = 'none';
                    }, 5000);
                </script>
            <?php } ?>

            <?php
            $queryEditc = mysqli_query($con, "select * from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
            $rowEdit = mysqli_fetch_array($queryEditc);
            ?>

            <h2 style="margin-bottom: 24px; color: #1a2332;">Você tem:</h2>
            
            <?php
            $queryMoedas = mysqli_query($con, "select * from quantidade_pedidos where tipo = 'pre' and codcadastro='".$rowEdit['id']."'");
            $rowM = mysqli_fetch_array($queryMoedas);
            ?>
            
            <div class="coin-display">
                <div class="coin-icon">💰</div>
                <div class="coin-amount"><?php if($rowM['qtd'] == '') { echo '0'; } else { echo $rowM['qtd']; } ?></div>
                <div class="coin-label">Moedas</div>
            </div>

            <div class="action-buttons">
                <a href="pagamento.php" class="action-button">
                    COMPRAR MOEDAS
                </a>
                <a href="meus-orcamentos-aguardando.php" class="action-button secondary">
                    ACEITAR ORÇAMENTOS
                </a>
                <!-- Adicionando botão para ver extrato de compras -->
                <button onclick="toggleExtrato()" class="action-button" style="background: linear-gradient(145deg, #6c757d, #868e96);">
                    📋 EXTRATO DE COMPRAS
                </button>
            </div>
            
            <!-- Seção do extrato completo (créditos e débitos) -->
            <div id="extrato-section" style="display: none; margin-top: 32px; border-top: 2px solid #e9ecef; padding-top: 32px;">
                <h3 style="color: #1a2332; margin-bottom: 24px; text-align: center;">📋 Extrato de Moedas</h3>
                
                <?php
                // Busca extrato da tabela moedas_extrato (créditos e débitos)
                $queryExtrato = @mysqli_query($con, "SELECT * FROM moedas_extrato WHERE codcadastro='".$rowEdit['id']."' ORDER BY data_hora DESC LIMIT 50");
                $temExtrato = $queryExtrato && mysqli_num_rows($queryExtrato) > 0;
                
                if ($temExtrato) {
                    echo '<div style="display: flex; flex-direction: column; gap: 12px;">';
                    while ($ext = mysqli_fetch_array($queryExtrato)) {
                        $dataFmt = date('d/m/Y H:i', strtotime($ext['data_hora']));
                        $isCredito = $ext['tipo'] == 'credito';
                        $corFundo = $isCredito ? 'rgba(40,167,69,0.1)' : 'rgba(220,53,69,0.1)';
                        $corBorda = $isCredito ? 'rgba(40,167,69,0.3)' : 'rgba(220,53,69,0.3)';
                        $corTexto = $isCredito ? '#28a745' : '#dc3545';
                        $icone = $isCredito ? '⬆️' : '⬇️';
                        $sinal = $isCredito ? '+' : '-';
                        
                        echo '<div style="background:'.$corFundo.'; border:1px solid '.$corBorda.'; border-radius:8px; padding:14px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">';
                        echo '<div>';
                        echo '<div style="font-weight:600; color:#1a2332; margin-bottom:4px;">'.$icone.' '.htmlspecialchars($ext['descricao']).'</div>';
                        echo '<div style="font-size:13px; color:#666;">'.$dataFmt.'</div>';
                        echo '</div>';
                        echo '<div style="text-align:right;">';
                        echo '<div style="font-size:20px; font-weight:bold; color:'.$corTexto.';">'.$sinal.$ext['quantidade'].' moeda(s)</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                
                // Fallback: também mostra compras antigas da tabela pagamento caso moedas_extrato esteja vazia
                if (!$temExtrato) {
                    $queryPagamentos = mysqli_query($con, "SELECT * FROM pagamento WHERE cod_cliente = '".$rowEdit['id']."' ORDER BY data DESC");
                    if ($queryPagamentos && mysqli_num_rows($queryPagamentos) > 0) {
                        echo '<div style="display: flex; flex-direction: column; gap: 12px;">';
                        while ($pagamento = mysqli_fetch_array($queryPagamentos)) {
                            $dataFormatada = date('d/m/Y H:i', strtotime($pagamento['data']));
                            $valorFormatado = number_format($pagamento['pix_amount'], 2, ',', '.');
                            $moedas = '';
                            if($pagamento['pix_amount'] == 1.00) $moedas = '8';
                            elseif($pagamento['pix_amount'] == 1.90) $moedas = '15';
                            elseif($pagamento['pix_amount'] == 3.00) $moedas = '30';
                            else $moedas = '?';
                            
                            echo '<div style="background:rgba(40,167,69,0.1); border:1px solid rgba(40,167,69,0.3); border-radius:8px; padding:14px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">';
                            echo '<div>';
                            echo '<div style="font-weight:600; color:#1a2332; margin-bottom:4px;">⬆️ Compra de moedas via PIX</div>';
                            echo '<div style="font-size:13px; color:#666;">'.$dataFormatada.'</div>';
                            echo '</div>';
                            echo '<div style="text-align:right;">';
                            echo '<div style="font-size:20px; font-weight:bold; color:#28a745;">+'.$moedas.' moeda(s)</div>';
                            echo '<div style="font-size:12px; color:#28a745;">R$ '.$valorFormatado.'</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<div style="text-align:center; padding:32px; color:#666; background:rgba(108,117,125,0.1); border-radius:8px;">';
                        echo '<div style="font-size:48px; margin-bottom:16px;">📋</div>';
                        echo '<div style="font-size:18px; margin-bottom:8px;">Nenhuma movimentação</div>';
                        echo '<div style="font-size:14px;">Seus créditos e débitos de moedas aparecerão aqui</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

<?php include('bottom-nav.php'); ?>

    <script>
        function toggleExtrato() {
            const extrato = document.getElementById('extrato-section');
            const button = event.target;
            
            if (extrato.style.display === 'none' || extrato.style.display === '') {
                extrato.style.display = 'block';
                button.innerHTML = '📋 OCULTAR EXTRATO';
                extrato.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                extrato.style.display = 'none';
                button.innerHTML = '📋 VER EXTRATO DE COMPRAS';
            }
        }

    </script>
</body>
</html>
