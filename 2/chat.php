<?php 
require("send.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - USERVICE</title>
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
            flex-direction: column;
            padding-bottom: 70px;
            overflow: hidden;
        }

        iframe {
            width: 100%;
            flex: 1;
            border: none;
            height: calc(100vh - 115px);
            max-height: calc(100vh - 115px);
            border-radius: 0;
            background: #fff;
        }

        /* Main content layout - Item 8: removendo padding extra */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0;
            gap: 0;
            max-width: 100%;
            overflow: hidden;
        }

        /* Content card - Item 8: removendo estilos que criam "setinha" ou espaço extra */
        .content-card {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            border: none;
            padding: 0;
            overflow: hidden;
        }

        /* Success message styling - Cores dos cards de orçamento */
        .success-message {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            color: #00d4ff;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 600;
        }

        #status-text {
            text-align: center;
            font-weight: bold;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            margin: 16px;
        }
    </style>
</head>
<body>
    <?php $navAtiva = 'pedidos'; include('header-app.php'); ?>

    <div class="main-content">
        <div class="content-card">
            <?php
            // variáveis que já existiam na sua página
            $codpedido = isset($_GET['codpedido']) ? htmlspecialchars($_GET['codpedido']) : '';
            $user = isset($_GET['user']) ? urlencode($_GET['user']) : '';
            $user_id = isset($_GET['user_id']) ? urlencode($_GET['user_id']) : '';
            $user_from = isset($_GET['user_from']) ? urlencode($_GET['user_from']) : '';
            ?>
            <p id="status-text">
                🚗 Aguardando prestador aceitar a proposta...
            </p>
        </div>
    </div>

<script>
(function(){
    const codpedido = '<?php echo $codpedido; ?>';
    const user = '<?php echo $user; ?>';
    const user_id = '<?php echo $user_id; ?>';
    const user_from = '<?php echo $user_from; ?>';
    const container = document.querySelector('.main-content');
    const statusText = document.getElementById('status-text');
    let intervalId = null;
    let alreadyLoadedIframe = false;

    if (!codpedido) {
        console.error('codpedido indefinido. Verifique a URL.');
        statusText.innerText = 'Erro: pedido inválido.';
        return;
    }

    async function checkStatus() {
        try {
            const url = `verifica_status.php?codpedido=${encodeURIComponent(codpedido)}&t=${Date.now()}`;
            const resp = await fetch(url, {cache: 'no-store', credentials: 'same-origin'});
            console.log('[checkStatus] HTTP', resp.status, resp.statusText);

            const text = await resp.text();
            console.log('[checkStatus] resposta bruta:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('[checkStatus] JSON.parse falhou:', e);
                statusText.innerText = 'Erro ao verificar status (resposta inválida).';
                return;
            }

            console.log('[checkStatus] json:', data);

            if (data.success && data.aceito === 's') {
                if (!alreadyLoadedIframe) {
                    container.innerHTML = `
                        <iframe 
                            src="php-chat-app-main/chat.php?user=${user}&user_id=${user_id}&user_from=${user_from}&codpedido=${encodeURIComponent(codpedido)}" 
                            style="width:100%; flex:1; border:none; height:calc(100vh - 115px); max-height:calc(100vh - 115px); background:#fff;">
                        </iframe>
                    `;
                    alreadyLoadedIframe = true;
                    if (intervalId) {
                        clearInterval(intervalId);
                        intervalId = null;
                        console.log('[checkStatus] aceito encontrado — intervalo parado.');
                    }
                }
            } else {
                statusText.innerText = data.message || '🚗 Aguardando prestador aceitar a proposta...';
            }
        } catch (err) {
            console.error('[checkStatus] erro fetch:', err);
            statusText.innerText = 'Erro de rede ao verificar status.';
        }
    }

    checkStatus();
    intervalId = setInterval(checkStatus, 1000);
})();
</script>

<?php include('bottom-nav.php'); ?>
</body>
</html>
