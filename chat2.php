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
            min-height: 100vh;
            display: flex;
            height: 100%;
            overflow: hidden;
            flex-direction: column;
            padding-bottom: 70px;
        }

                iframe {
            width: 100%;
            height: 100%;
            border: none;
            min-height: 550px;
        }


        /* Main content layout */
        .main-content {
            flex: 1;
            flex-direction: column;
            padding: 3px;
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
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Content card */
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #00d4ff;
            padding: 16px;
        }

                .back-link {
            display: inline-flex;
            color: #00d4ff;
            text-decoration: none;
            font-size: 19px;
            text-align: left !important;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-3px);
        }

    </style>
</head>
<body>
    <?php include('topo2.php'); ?>

    <div class="main-content">
        <div class="page-header">
                       <a href="meus-orcamentos2.php" class="back-link" style="font-size: 30px;">
                ← 
            </a>
        </div>

        <div class="content-card">

            
          <iframe src="php-chat-app-main/chat.php?user=<?php echo $_GET['user']; ?>&user_id=<?php echo $_GET['user_id']; ?>&user_from=<?php echo $_GET['user_from']; ?>&codpedido=<?php echo $_GET['codpedido']; ?>"></iframe>




        </div>
    </div>

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

    <?php include('bottom-nav.php'); ?>
</body>
</html>
