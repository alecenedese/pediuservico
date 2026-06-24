<?php 
require("send.php");
include_once 'conexao.php';
$navAtiva = 'servicos';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Avaliações - USERVICE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        }

        /* Avaliações styling */
        .avaliacoes-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .avaliacao-card {
            background: rgba(0, 212, 255, 0.05);
            border: 1px solid rgba(0, 212, 255, 0.1);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s ease;
        }

        .avaliacao-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.1);
        }

        .cliente-nome {
            font-size: 18px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cliente-nome::before {
            content: "👤";
            font-size: 19px;
        }

        .estrelas-container {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 16px;
        }

        .estrela-preenchida {
            color: #ffd700;
            font-size: 19px;
        }

        .estrela-vazia {
            color: #ddd;
            font-size: 19px;
        }

        .rating-text {
            margin-left: 8px;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .mensagem-avaliacao {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 16px;
            border-left: 4px solid #00d4ff;
            font-style: italic;
            color: #1a2332;
            line-height: 1.5;
        }

        .mensagem-avaliacao::before {
            content: """;
            font-size: 32px;
            color: #00d4ff;
            opacity: 0.3;
            float: left;
            line-height: 1;
            margin-right: 8px;
        }

        .no-avaliacoes {
            text-align: center;
            padding: 48px 16px;
            color: #666;
        }

        .no-avaliacoes-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .no-avaliacoes h3 {
            font-size: 21px;
            margin-bottom: 8px;
            color: #1a2332;
        }

        .no-avaliacoes p {
            font-size: 16px;
            line-height: 1.5;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .content-card {
                padding: 24px;
            }
            
            .avaliacao-card {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
        <?php include('header-app.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <div class="page-title">⭐ Avaliações dos Clientes</div>
        </div>

        <?php
        // Calcula a média das últimas 50 avaliações (Item 14)
        $idMedia = 0;
        if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
            $qPrestMedia = mysqli_query($con, "select id from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
            $rPrestMedia = mysqli_fetch_array($qPrestMedia);
            $idMedia = $rPrestMedia ? $rPrestMedia['id'] : 0;
        } elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
            $idMedia = mysqli_real_escape_string($con, $_COOKIE['id_prestador']);
        } elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
            $idMedia = mysqli_real_escape_string($con, $_COOKIE['id']);
        }

        $mediaEstrelas = 0;
        $totalAvaliacoes = 0;
        if ($idMedia) {
            $qMedia = mysqli_query($con, "SELECT AVG(qtd_estrela) as media, COUNT(*) as total FROM (SELECT qtd_estrela FROM avaliacoes WHERE codcadastro='$idMedia' ORDER BY id DESC LIMIT 50) t");
            if ($qMedia && $rMedia = mysqli_fetch_array($qMedia)) {
                $mediaEstrelas = $rMedia['media'] !== null ? round((float)$rMedia['media'], 1) : 0;
                $totalAvaliacoes = (int)$rMedia['total'];
            }
        }
        $mediaInt = (int)floor($mediaEstrelas);
        $temMeia = ($mediaEstrelas - $mediaInt) >= 0.5;
        ?>

        <!-- Card de Média de Estrelas (Item 14) -->
        <div style="background:linear-gradient(135deg,#00d4ff,#0097a7);border-radius:12px;padding:20px;margin-bottom:16px;text-align:center;color:#fff;box-shadow:0 4px 12px rgba(0,212,255,0.3);">
            <div style="font-size:14px;font-weight:600;opacity:0.9;margin-bottom:6px;">MÉDIA DE AVALIAÇÕES</div>
            <div style="font-size:42px;font-weight:800;line-height:1;margin-bottom:8px;"><?php echo number_format($mediaEstrelas, 1, ',', ''); ?></div>
            <div style="font-size:22px;letter-spacing:2px;margin-bottom:6px;">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $mediaInt) {
                        echo '<span style="color:#ffd700;">★</span>';
                    } elseif ($i == $mediaInt + 1 && $temMeia) {
                        echo '<span style="color:#ffd700;">⯨</span>';
                    } else {
                        echo '<span style="color:rgba(255,255,255,0.4);">★</span>';
                    }
                }
                ?>
            </div>
            <div style="font-size:12px;opacity:0.85;">Baseado nas últimas <?php echo $totalAvaliacoes; ?> avaliação(ões)</div>
        </div>

        <div class="content-card">
            <div class="avaliacoes-container">
                <?php
                // Suporte a cookies antigos e novos para prestador
                $id = 0;
                if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
                    $qPrestAv = mysqli_query($con, "select id from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
                    $rPrestAv = mysqli_fetch_array($qPrestAv);
                    $id = $rPrestAv ? $rPrestAv['id'] : 0;
                } elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
                    $id = mysqli_real_escape_string($con, $_COOKIE['id_prestador']);
                } elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
                    $id = mysqli_real_escape_string($con, $_COOKIE['id']);
                }

                $query_avaliacoes = "SELECT id, qtd_estrela, mensagem, cliente, denuncia
                            FROM avaliacoes where codcadastro = '$id'
                            ORDER BY id DESC";

                $result_avaliacoes = mysqli_query($con, $query_avaliacoes);
                // Fallback caso a coluna denuncia/cliente ainda não exista
                if (!$result_avaliacoes) {
                    $result_avaliacoes = mysqli_query($con, "SELECT id, qtd_estrela, mensagem FROM avaliacoes where codcadastro = '$id' ORDER BY id DESC");
                }

                $has_avaliacoes = false;

                while ($result_avaliacoes && $row_avaliacao = mysqli_fetch_assoc($result_avaliacoes)) {
                    $cliente = isset($row_avaliacao['cliente']) ? $row_avaliacao['cliente'] : 'Cliente';
                    $qtd_estrela = isset($row_avaliacao['qtd_estrela']) ? $row_avaliacao['qtd_estrela'] : 0;
                    $mensagem = isset($row_avaliacao['mensagem']) ? $row_avaliacao['mensagem'] : '';
                    $denuncia = isset($row_avaliacao['denuncia']) ? $row_avaliacao['denuncia'] : 0;

                    // Item 6: Denúncia entra na média mas NÃO é exibida ao prestador
                    if ($denuncia == 1) { continue; }
                    $has_avaliacoes = true;
                    $qtd_estrela_exibir = $qtd_estrela;

                    echo '<div class="avaliacao-card">';
                    echo '<div class="cliente-nome">' . htmlspecialchars($cliente) . '</div>';

                    echo '<div class="estrelas-container">';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $qtd_estrela_exibir) {
                            echo '<i class="estrela-preenchida fa-solid fa-star"></i>';
                        } else {
                            echo '<i class="estrela-vazia fa-solid fa-star"></i>';
                        }
                    }
                    echo '<span class="rating-text">(' . $qtd_estrela_exibir . '/5 estrelas)</span>';
                    echo '</div>';

                    if (!empty($mensagem)) {
                        echo '<div class="mensagem-avaliacao">' . htmlspecialchars($mensagem) . '</div>';
                    }

                    echo '</div>';
                }

                if (!$has_avaliacoes) {
                    echo '<div class="no-avaliacoes">';
                    echo '<div class="no-avaliacoes-icon">⭐</div>';
                    echo '<h3>Nenhuma avaliação ainda</h3>';
                    echo '<p>Suas avaliações de clientes aparecerão aqui após a conclusão dos serviços.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php include('bottom-nav.php'); ?>

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
</body>
</html>
