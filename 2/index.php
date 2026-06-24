<?php
// Redireciona para a página principal (buscar.php)
header('Location: buscar.php');
exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Serviço - Painel Principal</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a2f4a 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: rgba(0, 212, 255, 0.08);
            border-bottom: 1px solid rgba(0, 212, 255, 0.15);
        }

        .header .logo {
            font-size: 16px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .menu-button {
            background: #00d4ff;
            color: #1a2332;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-button:hover {
            background: #00f0ff;
            transform: translateY(-1px);
        }

        /* Novo layout vertical com seções bem definidas */
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 24px 20px;
            gap: 32px;
            overflow-y: auto;
            padding-top: 30px;
        }

        .panel-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .panel-title {
            font-size: 28px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .buttons-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-large {
            background: #00d4ff;
            color: #1a2332;
            border: none;
            padding: 18px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
            letter-spacing: 0.5px;
        }

        .btn-large:hover {
            background: #00f0ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.5);
        }

        .btn-large:active {
            transform: translateY(0);
        }

        .btn-small {
            background: transparent;
            color: #00d4ff;
            border: 2px solid #00d4ff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .btn-small:hover {
            background: #00d4ff;
            color: #1a2332;
            transform: translateY(-1px);
        }

        .btn-small:active {
            transform: translateY(0);
        }

        .help-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            cursor: pointer;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .help-section:hover {
            background: rgba(0, 212, 255, 0.1);
        }

        .whatsapp-icon {
            font-size: 32px;
            color: #00d4ff;
        }

        .help-text {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            letter-spacing: 0.5px;
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 16px 12px;
                gap: 24px;
            }

            .panel-title {
                font-size: 22px;
            }

            .btn-large {
                padding: 14px 16px;
                font-size: 14px;
            }

            .btn-small {
                padding: 10px 16px;
                font-size: 12px;
            }

            .help-text {
                font-size: 14px;
            }

            .whatsapp-icon {
                font-size: 28px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="header">
        <div class="logo">Pediu Serviço</div>
    </div>

    <div class="main-container">
        <!-- Painel do Cliente -->
        <div class="panel-section" style="margin-top: 30px;">
            <h2 class="panel-title">Painel do Cliente</h2>
            <div class="buttons-group">
                <button class="btn-large" onclick="window.location.href='buscar.php'">
                    Quero Buscar Um Prestador
                </button>
                <button class="btn-small" onclick="window.location.href='meus-orcamentos-cli.php'">
                    Meus Pedidos
                </button>
            </div>
        </div>

        <!-- Painel do Prestador -->
        <div class="panel-section" style="margin-top: 60px;">
            <h2 class="panel-title">Painel do Prestador</h2>
            <div class="buttons-group">
                <button class="btn-large" onclick="window.location.href='login.php'">
                    Quero Prestar Serviços
                </button>
                <button class="btn-small" onclick="window.location.href='meus-orcamentos.php'">
                    Meus Serviços
                </button>
            </div>
        </div>

        <!-- Seção de Ajuda -->
        <div class="help-section" onclick="window.location.href='https://wa.me/seu-numero-aqui'">
            <div class="whatsapp-icon"><i class="fa fa-whatsapp" style="font-size:48px;color:#18d5fd;"></i></div>
            <span class="help-text">Preciso de Ajuda</span>
        </div>
    </div>

    <script>
        function toggleMenu() {
            console.log('Menu clicado');
        }
    </script>
</body>
</html>
