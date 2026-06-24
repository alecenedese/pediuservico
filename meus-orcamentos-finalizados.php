<?php
require("send.php");
include('badge-counts.php');
$navAtiva = 'servicos';

// Marca finalizados como vistos ao entrar na página
if (!empty($_idPrestBadge)) {
    mysqli_query($con, "UPDATE disparo_pedidos SET visto=1 WHERE codcadastro='$_idPrestBadge' AND aceito='f' AND visto=0");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizados - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            background-attachment: fixed;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 70px;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
            max-width: 100%;
        }

        /* Tabs - idêntico ao meus-orcamentos.php */
        .tabs-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        @media (min-width: 480px) {
            .tabs-container {
                grid-template-columns: repeat(5, 1fr);
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

        .tab svg { width: 16px; height: 16px; }

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

        /* Quick nav grid */
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

        .nav-card svg { width: 22px; height: 22px; stroke-width: 2; color: #00d4ff; }
        .nav-card span { font-size: 12px; font-weight: 500; text-align: center; line-height: 1.1; }

        /* Cards de pedidos */
        .order-card { background: rgba(255,255,255,.95); border-radius: 12px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,.1); margin-bottom: 12px; }
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .order-number { font-size: 14px; font-weight: 700; color: #1a2332; }
        .status-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; background: #22c55e; color: #fff; }
        .info-row { display: flex; gap: 8px; flex-direction: column; margin-bottom: 10px; }
        .info-item { display: flex; gap: 8px; align-items: flex-start; }
        .info-label { font-size: 10px; font-weight: 700; color: #666; text-transform: uppercase; }
        .info-value { font-size: 13px; color: #1a2332; font-weight: 600; }
        .estrelas { color: #f59e0b; font-size: 22px; letter-spacing: 2px; margin: 8px 0; }
        .mensagem-avaliacao { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px; font-size: 13px; color: #166534; margin-top: 8px; line-height: 1.4; }

        /* Card de média */
        .media-card { background: linear-gradient(135deg, #00bcd4, #0097a7); border-radius: 12px; padding: 20px; text-align: center; color: #fff; box-shadow: 0 4px 16px rgba(0,188,212,0.3); margin-bottom: 12px; }
        .media-valor { font-size: 40px; font-weight: 800; line-height: 1; margin-bottom: 6px; }
        .media-estrelas { font-size: 22px; letter-spacing: 3px; margin-bottom: 6px; }
        .media-info { font-size: 12px; opacity: .85; }

        .empty-state { text-align: center; padding: 60px 20px; color: rgba(255,255,255,.6); }
        .empty-state .icon { font-size: 56px; margin-bottom: 12px; }
        .empty-state h3 { font-size: 18px; color: #fff; margin-bottom: 6px; }
    </style>
</head>
<body>
    <?php include('header-app.php'); ?>

    <div class="main-content">

        <?php include('panel-banner-prestador.php'); ?>

        <!-- Grid de navegação rápida -->
        <div class="quick-nav-grid">
            <a href="meus-orcamentos.php" class="nav-card">
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

        <!-- Conteúdo -->
        <?php
        $idPrest = 0;
        if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
            $qPrest = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".$_COOKIE['login']."'");
            $rPrest = mysqli_fetch_array($qPrest);
            $idPrest = $rPrest ? $rPrest['id'] : 0;
        } elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
            $idPrest = mysqli_real_escape_string($con, $_COOKIE['id_prestador']);
        } elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
            $idPrest = mysqli_real_escape_string($con, $_COOKIE['id']);
        }

        // Card de média (Item 6: inclui denúncias com 0 estrelas no cálculo)
        if ($idPrest) {
            $qMedia = mysqli_query($con, "SELECT AVG(qtd_estrela) as media, COUNT(*) as total FROM (SELECT qtd_estrela FROM avaliacoes WHERE codcadastro='$idPrest' ORDER BY id DESC LIMIT 50) t");
            $rMedia = $qMedia ? mysqli_fetch_assoc($qMedia) : null;
            $mediaVal = ($rMedia && $rMedia['media'] !== null) ? round((float)$rMedia['media'], 1) : 0;
            $totalAvl = ($rMedia) ? (int)$rMedia['total'] : 0;
            if ($totalAvl > 0) {
                $mediaInt = (int)floor($mediaVal);
                $estrelasHtml = '';
                for ($i = 1; $i <= 5; $i++) { $estrelasHtml .= ($i <= $mediaInt) ? '★' : '☆'; }
                echo '<div class="media-card">';
                echo '<div class="media-valor">'.number_format($mediaVal, 1, ',', '').'</div>';
                echo '<div class="media-estrelas">'.$estrelasHtml.'</div>';
                echo '<div class="media-info">Média das últimas '.$totalAvl.' avaliação(ões)</div>';
                echo '</div>';
            }
        }

        // Listagem de finalizados
        if ($idPrest) {
            $qFin = mysqli_query($con, "
                SELECT dp.codpedido, p.data_hora, g.titulo as cat, s.titulo as sub,
                       avl.qtd_estrela as nota, avl.mensagem, avl.cliente, avl.denuncia
                FROM disparo_pedidos dp
                INNER JOIN pedido p ON p.codigo = dp.codpedido
                INNER JOIN grupos g ON g.codigo = p.categoria
                INNER JOIN categoria s ON s.codigo = p.subcategoria
                LEFT JOIN avaliacoes avl ON avl.codcadastro = dp.codcadastro AND avl.codpedido = dp.codpedido
                WHERE dp.codcadastro = '$idPrest' AND dp.aceito = 'f'
                GROUP BY dp.codpedido
                ORDER BY dp.codpedido DESC
            ");
            if (!$qFin) {
                $qFin = mysqli_query($con, "
                    SELECT dp.codpedido, p.data_hora, g.titulo as cat, s.titulo as sub,
                           avl.qtd_estrela as nota, avl.mensagem
                    FROM disparo_pedidos dp
                    INNER JOIN pedido p ON p.codigo = dp.codpedido
                    INNER JOIN grupos g ON g.codigo = p.categoria
                    INNER JOIN categoria s ON s.codigo = p.subcategoria
                    LEFT JOIN avaliacoes avl ON avl.codcadastro = dp.codcadastro AND avl.codpedido = dp.codpedido
                    WHERE dp.codcadastro = '$idPrest' AND dp.aceito = 'f'
                    GROUP BY dp.codpedido
                    ORDER BY dp.codpedido DESC
                ");
            }
            if (!$qFin) {
                $qFin = mysqli_query($con, "
                    SELECT dp.codpedido, p.data_hora, g.titulo as cat, s.titulo as sub
                    FROM disparo_pedidos dp
                    INNER JOIN pedido p ON p.codigo = dp.codpedido
                    INNER JOIN grupos g ON g.codigo = p.categoria
                    INNER JOIN categoria s ON s.codigo = p.subcategoria
                    WHERE dp.codcadastro = '$idPrest' AND dp.aceito = 'f'
                    GROUP BY dp.codpedido
                    ORDER BY dp.codpedido DESC
                ");
            }

            $count = 0;
            while ($qFin && $row = mysqli_fetch_assoc($qFin)) {
                $ehDenuncia = isset($row['denuncia']) && $row['denuncia'] == 1;
                // Item 6: Denúncia computa na média mas NÃO aparece como card para o prestador
                if ($ehDenuncia) { continue; }
                $count++;
                $nota = (int)($row['nota'] ?? 5);
                $estrelas = str_repeat('★', $nota) . str_repeat('☆', 5 - $nota);
                $dataFormatada = date('d/m/Y H:i', strtotime($row['data_hora']));
                echo '<div class="order-card">';
                echo '<div class="order-header">';
                echo '<div class="order-number">Pedido #'.$row['codpedido'].'</div>';
                echo '<div class="status-badge">✅ Avaliado</div>';
                echo '</div>';
                echo '<div class="info-row">';
                echo '<div class="info-item"><div><div class="info-label">Serviço</div><div class="info-value">'.$row['cat'].' / '.$row['sub'].'</div></div></div>';
                echo '<div class="info-item"><div><div class="info-label">Data</div><div class="info-value">'.$dataFormatada.'</div></div></div>';
                if (!empty($row['cliente'])) echo '<div class="info-item"><div><div class="info-label">Cliente</div><div class="info-value">'.htmlspecialchars($row['cliente']).'</div></div></div>';
                echo '</div>';
                echo '<div class="estrelas">'.$estrelas.' ('.$nota.'/5)</div>';
                if (!empty($row['mensagem'])) {
                    echo '<div class="mensagem-avaliacao">💬 '.htmlspecialchars($row['mensagem']).'</div>';
                }
                echo '</div>';
            }

            if ($count === 0) {
                echo '<div class="empty-state"><div class="icon">⭐</div><h3>Nenhum serviço finalizado</h3><p>Quando clientes avaliarem seus serviços, aparecerão aqui.</p></div>';
            }
        }
        ?>

    </div>

    <?php include('bottom-nav.php'); ?>
</body>
</html>
