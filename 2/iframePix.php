<?php
require_once('send.php');

// --- ETAPA 1: VALIDAÇÃO E SEGURANÇA DOS DADOS DE ENTRADA ---
// Garante que o ID é um número inteiro para evitar injeção de SQL.
$id_pagamento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$qtd_moedas = filter_input(INPUT_GET, 'qtd', FILTER_VALIDATE_INT);

// Se o ID não for um número válido, interrompe a execução.
if ($id_pagamento === false || $qtd_moedas === false) {
    die("ID de pagamento ou quantidade de moedas inválido.");
}

// --- ETAPA 2: VERIFICAR SE O PAGAMENTO JÁ FOI PROCESSADO NO BANCO ---
// Usando Prepared Statements para segurança máxima.
$stmt_check = $con->prepare("SELECT pix_pago, cod_cliente FROM pagamento WHERE id = ?");
$stmt_check->bind_param("i", $id_pagamento);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$pagamento_local = $result_check->fetch_assoc();
$stmt_check->close();

// Se não encontrou o pagamento no banco, algo está errado.
if (!$pagamento_local) {
    die("Pagamento não encontrado no sistema.");
}

// Se o campo 'pix_pago' já está marcado como 1, significa que já processamos.
// Apenas mostramos a mensagem de sucesso e o JS de redirecionamento.
if ($pagamento_local['pix_pago'] == 1) {
?>
    <div class="alert alert-success">Pagamento Confirmado!</div>
    <script>window.location.href = 'minhasmoedas.php?msg=sucesso&qtd=<?php echo $qtd_moedas; ?>';</script>
<?php
    exit(); // Encerra o script para não fazer a chamada à API desnecessariamente.
}

// --- ETAPA 3: SE AINDA NÃO FOI PROCESSADO, CONSULTAR A API DO MERCADO PAGO ---
$Authorization = 'Bearer APP_USR-7427488261175895-092013-f8f5eab422a8a1152894cfbb35b0893b-15978406';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $id_pagamento,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $Authorization,
    ],
]);
$response = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$responseData = json_decode($response, true);

// --- ETAPA 4: PROCESSAR A RESPOSTA E ATUALIZAR O BANCO (SE NECESSÁRIO) ---
// Verificamos se a chamada foi bem-sucedida E se o status é "approved"
if ($http_status == 200 && isset($responseData['status']) && $responseData['status'] == "approved") {

    // LÓGICA CRÍTICA: Executar a atualização e adição de moedas DENTRO de uma transação.
    // Isso garante que ambas as operações ocorram com sucesso, ou nenhuma delas.
    $con->begin_transaction();
    try {
        // 1. Marcar o pagamento como pago
        $stmt_update_pagamento = $con->prepare("UPDATE pagamento SET pix_pago = 1 WHERE id = ? AND pix_pago IS NULL");
        $stmt_update_pagamento->bind_param("i", $id_pagamento);
        $stmt_update_pagamento->execute();
        
        // Se a linha foi afetada (ou seja, a atualização ocorreu agora)
        if ($stmt_update_pagamento->affected_rows > 0) {
            // 2. Adicionar as moedas
            $cod_cliente = $pagamento_local['cod_cliente'];
            
            // Usamos INSERT ... ON DUPLICATE KEY UPDATE para simplificar a lógica de adicionar/atualizar moedas
            $stmt_update_moedas = $con->prepare("UPDATE quantidade_pedidos SET qtd = qtd + ?, data = NOW() WHERE codcadastro = ? AND tipo = 'pre'");
            $stmt_update_moedas->bind_param("ii", $qtd_moedas, $cod_cliente);
            $stmt_update_moedas->execute();

            if ($stmt_update_moedas->affected_rows === 0) {
                $stmt_insert_moedas = $con->prepare("INSERT INTO quantidade_pedidos (codcadastro, qtd, data, tipo) VALUES (?, ?, NOW(), 'pre')");
                $stmt_insert_moedas->bind_param("ii", $cod_cliente, $qtd_moedas);
                $stmt_insert_moedas->execute();
                $stmt_insert_moedas->close();
            }

            // Registra crédito no extrato de moedas
            $stmt_extrato = $con->prepare("INSERT INTO moedas_extrato (codcadastro, tipo, quantidade, descricao, codpedido, data_hora) VALUES (?, 'credito', ?, 'Compra de moedas via PIX', NULL, NOW())");
            $stmt_extrato->bind_param("ii", $cod_cliente, $qtd_moedas);
            $stmt_extrato->execute();
            $stmt_extrato->close();
        }
        $stmt_update_pagamento->close();

        // Se tudo deu certo, confirma as alterações no banco
        $con->commit();

    } catch (mysqli_sql_exception $exception) {
        $con->rollback(); // Se algo deu errado, desfaz tudo
        // Opcional: logar o erro $exception->getMessage()
    }

    // Agora, exibe a mensagem de sucesso e redireciona
    ?>
    <div class="alert alert-success">Pagamento Confirmado!</div>
    <script>window.location.href = 'minhasmoedas.php?msg=sucesso&qtd=<?php echo $qtd_moedas; ?>';</script>
    <?php

} else {
    // Se o pagamento ainda não foi aprovado, mostra a mensagem de aguardando.
    ?>
    <style>
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.2; } 100% { opacity: 1; } }
        .blink { animation: blink 3s infinite; font-family: Verdana, sans-serif; font-size: 15px; color:black; padding-top: 20px; font-weight: 600; text-align: center; }
    </style>
    <h1 class="blink">Aguardando Pagamento..</h1>
    <?php
}
?>