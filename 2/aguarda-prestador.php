<?php 
session_start();
require("send.php");

// Pega os parâmetros
$codpedido = isset($_GET['codpedido']) ? mysqli_real_escape_string($con, $_GET['codpedido']) : '';
$codcadastro = isset($_GET['codcadastro']) ? mysqli_real_escape_string($con, $_GET['codcadastro']) : '';

if(empty($codpedido) || empty($codcadastro)) {
    echo "<script>alert('Dados inválidos'); window.location.href='meus-orcamentos-cli.php';</script>";
    exit;
}

// Busca os dados do pedido para construir o link de retorno ao mapa
$queryPedido = mysqli_query($con, "SELECT lat, log, subcategoria, data_hora FROM pedido WHERE codigo = '$codpedido'");
$rowPedido = mysqli_fetch_array($queryPedido);

$latitude = $rowPedido['lat'];
$longitude = $rowPedido['log'];
$subcategoria = $rowPedido['subcategoria'];
$dia = $rowPedido['data_hora'];

// Verifica se já existe um registro de tempo de expiração
$queryTimer = mysqli_query($con, "SELECT * FROM timer_acordo WHERE codpedido = '$codpedido' AND codcadastro = '$codcadastro'");
$rowTimer = mysqli_fetch_array($queryTimer);

if(!$rowTimer) {
    // Cria registro de timer (10 minutos = 600 segundos)
    $tempo_expiracao = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    mysqli_query($con, "INSERT INTO timer_acordo (codpedido, codcadastro, tempo_expiracao, status) VALUES ('$codpedido', '$codcadastro', '$tempo_expiracao', 'aguardando')");
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aguardando Prestador - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
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

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 24px;
            gap: 24px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        .aguardo-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 212, 255, 0.2);
            text-align: center;
        }

        .timer-display {
            font-size: 48px;
            font-weight: bold;
            color: #00d4ff;
            margin: 32px 0;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .timer-label {
            font-size: 19px;
            color: #1a2332;
            margin-bottom: 16px;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(0, 212, 255, 0.2);
            border-top: 5px solid #00d4ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 32px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .status-message {
            font-size: 18px;
            color: #666;
            margin-top: 16px;
            line-height: 1.6;
        }

        .warning-message {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #856404;
            padding: 16px;
            border-radius: 8px;
            margin-top: 24px;
            font-size: 15px;
        }

        .expired-message {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #721c24;
            padding: 24px;
            border-radius: 8px;
            margin-top: 24px;
        }

        .expired-message h3 {
            font-size: 21px;
            margin-bottom: 16px;
        }

        .btn-primary {
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
            display: inline-block;
            margin-top: 16px;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 212, 255, 0.4);
        }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>

    <div class="main-content">
        <div class="aguardo-card">
            <div class="loading-spinner"></div>
            
            <h2 class="timer-label">Aguardando prestador firmar o acordo</h2>
            
            <div class="timer-display" id="timer">10:00</div>
            
            <p class="status-message" id="status-msg">
                O prestador tem 10 minutos para confirmar o acordo e debitar as moedas necessárias.
            </p>

            <div id="expired-container" style="display: none;">
                <div class="expired-message">
                    <h3>Tempo Expirado</h3>
                    <p>O prestador parece que não conseguiu firmar acordo, escolha o orçamento de outro prestador.</p>
                    <a href="novomapa.php?latitude=<?php echo urlencode($latitude); ?>&longitude=<?php echo urlencode($longitude); ?>&codpedido=<?php echo urlencode($codpedido); ?>&subcategoria=<?php echo urlencode($subcategoria); ?>&dia=<?php echo urlencode($dia); ?>&ver=esfs" class="btn-primary">OK</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const codpedido = '<?php echo $codpedido; ?>';
        const codcadastro = '<?php echo $codcadastro; ?>';
        let timeLeft = 600; // 10 minutos em segundos
        let timerInterval = null;
        let checkInterval = null;

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }

        function updateTimer() {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                clearInterval(checkInterval);
                showExpiredMessage();
                return;
            }

            document.getElementById('timer').textContent = formatTime(timeLeft);
            timeLeft--;
        }

        function showExpiredMessage() {
            document.querySelector('.loading-spinner').style.display = 'none';
            document.querySelector('.timer-label').style.display = 'none';
            document.querySelector('.timer-display').style.display = 'none';
            document.getElementById('status-msg').style.display = 'none';
            document.getElementById('expired-container').style.display = 'block';

            // Marca como perdido no banco
            fetch('marcar-perdido.php?codpedido=' + encodeURIComponent(codpedido) + '&codcadastro=' + encodeURIComponent(codcadastro))
                .catch(err => console.error('Erro ao marcar como perdido:', err));
        }

        async function checkStatus() {
            try {
                const response = await fetch('verifica_acordo.php?codpedido=' + encodeURIComponent(codpedido) + '&codcadastro=' + encodeURIComponent(codcadastro) + '&t=' + Date.now());
                const data = await response.json();

                if (data.success && data.status === 'confirmado') {
                    // Prestador confirmou! Redireciona para o chat
                    clearInterval(timerInterval);
                    clearInterval(checkInterval);
                    window.location.href = 'chat.php?codpedido=' + encodeURIComponent(codpedido) + '&user=' + data.user + '&user_id=' + data.user_id + '&user_from=' + data.user_from;
                }
            } catch (err) {
                console.error('Erro ao verificar status:', err);
            }
        }

        // Inicia o timer
        timerInterval = setInterval(updateTimer, 1000);
        
        // Verifica status a cada 2 segundos
        checkInterval = setInterval(checkStatus, 2000);
        
        // Primeira verificação imediata
        checkStatus();
    })();
    </script>
</body>
</html>
