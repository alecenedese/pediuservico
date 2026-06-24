<?php
// Conexão com o banco de dados (assumindo que 'send.php' faz isso)
require_once('send.php');

// Função para gerar um UUID v4, essencial para a chave de idempotência.
function generateUUID() {
    $data = openssl_random_pseudo_bytes(16);
    assert(strlen($data) == 16);
    // Ajusta os bits para o formato da UUID v4
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versão 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant 1
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// --- CONFIGURAÇÕES DA API ---
$Authorization = 'Bearer APP_USR-7427488261175895-092013-f8f5eab422a8a1152894cfbb35b0893b-15978406'; // Mantenha sua credencial segura
$url_curl = 'https://api.mercadopago.com/v1/payments';

// --- BUSCA DADOS DO USUÁRIO LOGADO ---
// Utilizando prepared statements para mais segurança
$stmt = $con->prepare("SELECT id, NOME, CNPJ_CPF FROM parceiro WHERE CNPJ_CPF = ?");
$stmt->bind_param("s", $_COOKIE['login']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Erro: Usuário não encontrado.");
}

// --- PREPARAÇÃO DOS DADOS PARA A API ---
$nomecompleto = $row['NOME'];
$partes = explode(' ', $nomecompleto, 2);
$nome = $partes[0];
$sobrenome = isset($partes[1]) ? $partes[1] : ''; // Garante que o sobrenome não seja nulo

// Limpa o CPF/CNPJ para conter apenas números
$cpf = preg_replace('/[^0-9]/', '', $row['CNPJ_CPF']);

$idempotencyKey = generateUUID();
$valor = (float)$_GET["valor"]; // Converte o valor para float, como esperado pela API
$moedas = $_GET["moedas"] ?? 'Moedas'; // Pega a quantidade de moedas ou usa um padrão
$email = "app@maoamigaapp.com"; // Email padrão

// --- MONTAGEM DO CORPO DA REQUISIÇÃO (JSON) ---
$postData = [
    "transaction_amount" => $valor,
    "description" => "COMPRA DE " . $moedas . " MOEDAS",
    "payment_method_id" => "pix",
    "payer" => [
        "email" => $email,
        "first_name" => $nome,
        "last_name" => $sobrenome,
        "identification" => [
            "type" => "CPF", // Assumindo CPF. Se puder ser CNPJ, a lógica precisa ser ajustada
            "number" => $cpf
        ]
    ]
];
// Codifica o array em uma string JSON
$postFields = json_encode($postData);

// --- EXECUÇÃO DA REQUISIÇÃO cURL ---
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url_curl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30, // Um timeout de 30 segundos é mais razoável
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postFields, // Envia o corpo da requisição como JSON
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $Authorization,
        'Content-Type: application/json', // Informa que o corpo é JSON
        'X-Idempotency-Key: ' . $idempotencyKey
    ],
]);
$response = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura o status HTTP
curl_close($curl);

// --- TRATAMENTO DA RESPOSTA DA API ---
$responseData = json_decode($response, true);

// Verificação de erro na resposta
if ($http_status >= 400 || !isset($responseData['point_of_interaction'])) {
    echo "<h1>Erro ao gerar o PIX</h1>";
    echo "<p>A API do Mercado Pago retornou um erro. Verifique os dados enviados.</p>";
    echo "<pre>";
    print_r($responseData); // Mostra a resposta do erro para depuração
    echo "</pre>";
    die();
}

// Acessa as variáveis qr_code e qr_code_base64 de forma segura
$qrCode = $responseData['point_of_interaction']['transaction_data']['qr_code'] ?? null;
$qrCodeBase64 = $responseData['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
$paymentId = $responseData['id'] ?? null;

if (!$qrCode || !$qrCodeBase64 || !$paymentId) {
    die("Erro: A resposta da API não contém os dados do PIX esperados.");
}

// --- INSERÇÃO NO BANCO DE DADOS (COM PREPARED STATEMENTS) ---
$sql = "INSERT INTO pagamento (cod_cliente, dia, mes, ano, data, pix_qrcode, pix_base64, pix_amount, nome, sobrenome, email, cpf, id) 
        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $con->prepare($sql);
$dia = date("d");
$mes = date("m");
$ano = date("Y");
$stmt->bind_param(
    "issssssssssi",
    $row['id'],
    $dia,
    $mes,
    $ano,
    $qrCode,
    $qrCodeBase64,
    $valor,
    $nome,
    $sobrenome,
    $email,
    $cpf,
    $paymentId
);
$stmt->execute();
$stmt->close();

// O restante do seu código HTML/PHP continua aqui...
// ...
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento PIX - USERVICE</title>
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
            align-items: center;
        }

        /* Payment card */
        .payment-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #00d4ff;
            text-decoration: none;
            font-size: 19px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-3px);
        }

        .page-title {
            font-size: 21px;
            font-weight: bold;
            color: #1a2332;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 32px;
        }

        /* QR Code display */
        .qr-container {
            margin: 32px 0;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .price-display {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin: 24px 0;
        }

        /* Copy section */
        .copy-section {
            margin: 32px 0;
        }

        .copy-input {
            width: 100%;
            padding: 16px;
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
            background: #f8f9fa;
            color: #1a2332;
        }

        .copy-button {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
            width: 100%;
        }

        .copy-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 212, 255, 0.4);
        }

        .copy-button.copied {
            background: linear-gradient(145deg, #28a745, #34ce57);
            color: white;
        }

        /* Status area */
        .status-area {
            margin-top: 32px;
            padding: 16px;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(0, 212, 255, 0.2);
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
<style>
            .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .logo {
            font-size: 19px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }
    
        .category-button2 {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            border: 2px solid #00f0ff;
            color: #00f0ff;
            font-size: 13px; /* aumentado de 10px para 13px para melhor legibilidade */
            font-weight: 600;
            padding: 8px 8px; /* aumentado padding de 10px 6px para 11px 8px */
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px; /* aumentado gap de 5px para 6px */
            text-decoration: none;
            min-height: 17px; /* aumentado de 70px para 75px */
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .category-button2:hover {
            transform: translateY(-2px);
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
            border-color: #40ffff;
            color: #40ffff;
        }

         .category-button3 {
            background: #db9f9f;
            border: 2px solid red;
            color: #e1001e;
            font-size: 13px; /* aumentado de 10px para 13px para melhor legibilidade */
            font-weight: 600;
            padding: 8px 8px; /* aumentado padding de 10px 6px para 11px 8px */
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px; /* aumentado gap de 5px para 6px */
            text-decoration: none;
            min-height: 17px; /* aumentado de 70px para 75px */
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

</style>
    <div class="header">
                <a href="index.php" class="category-button2">Ínicio</a>
        <a href="javascript: history.back()" class="category-button2">← Voltar</a>
        <a href="sair.php" class="category-button3">Sair</a>

 </div>
 
                <!-- Adicionando grid de navegação rápida -->
        <div class="quick-nav-grid">
            <a href="meus-orcamentos.php" class="nav-card ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Orçamentos</span>
            </a>
            
            <a href="minhasmoedas.php" class="nav-card active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Minhas Moedas</span>
            </a>
        </div>

    <div class="main-content">
        <div class="payment-card">
            <a href="pagamento.php" class="back-link" style="font-size: 30px;">
                ← 
            </a>
            
            <div class="page-title">💰 COMPRAR <?php echo $_GET["moedas"]; ?> MOEDAS</div>
            <div class="page-subtitle">Instruções para fazer o pagamento via PIX!</div>
            
            <div class="qr-container">
               <img src='data:image/png;base64,<?php echo $qrCodeBase64; ?>' class="qr-code" alt="QR Code PIX">
            </div>
            
            <div class="price-display">R$ <?php echo number_format($_GET["valor"], 2, ",", "."); ?></div>
            
            <div class="copy-section">
               <input id="input" type="text" class="copy-input" value="<?php echo $qrCode; ?>" readonly />
                
                <button id="execCopy" class="copy-button">
                    📋 Copiar código do QR Code
                </button>
                <button id="clipboardCopy" class="copy-button copied" style="display: none;">
                    ✅ Código QR Code Copiado
                </button>
            </div>
            
            <div class="status-area" id="pixpago">
                <!-- Status updates loaded here -->
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            setInterval(function(){
                $('#pixpago').load('iframePix.php?id=<?php echo $paymentId; ?>&qtd=<?php echo $_GET["moedas"]; ?>');
            }, 1200);
        });

        // Copy functionality
        document.getElementById('execCopy').addEventListener('click', execCopy);
        function execCopy() {
            document.querySelector("#input").select();
            document.execCommand("copy");
            $("#execCopy").hide(100);
            $("#clipboardCopy").show(300);
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
    </script>
</body>
</html>
