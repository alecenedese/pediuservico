<?php
// teste-carga-alvo.php
// Alvo da simulação de carga. Reproduz FIELMENTE o trabalho do novomapa.php otimizado:
//   1. Query JOIN (prestadores + nome + endereço da subcategoria)
//   2. INSERT em massa em disparo_pedidos e markers (com codpedido de TESTE)
//   3. Enfileira as notificações em push_fila (título [TESTE])
//
// Usa codpedido de teste (>= 9000000) e título [TESTE] para limpeza fácil.
// Os IDs/dados são reais na LEITURA (mede o custo real da query), mas as ESCRITAS
// vão para um codpedido fake, sem afetar pedidos reais.
//
// Parâmetros:
//   sub    = subcategoria real para consultar (use uma com MUITOS prestadores)
//   worker = 1 para disparar o worker ao final
//   req    = identificador da requisição (passado pelo runner)

require_once(__DIR__ . '/send.php');
require_once(__DIR__ . '/api/push-send.php');

$sub = isset($_GET['sub']) ? mysqli_real_escape_string($con, $_GET['sub']) : '';
$dispararWorker = isset($_GET['worker']) && $_GET['worker'] == '1';

// codpedido de teste fixo (facilita a limpeza). Não conflita com pedidos reais.
$codPedidoTeste = 9000000;

$t0 = microtime(true);

garantirTabelaFila($con);

// 1) MESMA query do novomapa (JOIN prestador + endereço via subquery)
$lista = mysqli_query($con, "
    SELECT cat.codcadastro,
           p.NOME,
           (SELECT e.lat FROM endereco_prestador e WHERE e.cod_cadastro = cat.codcadastro LIMIT 1) AS elat,
           (SELECT e.log FROM endereco_prestador e WHERE e.cod_cadastro = cat.codcadastro LIMIT 1) AS elon
    FROM categoria_prestador cat
    INNER JOIN parceiro p ON p.id = cat.codcadastro
    WHERE cat.codsubcategoria = '$sub'
");

$disparoValues = [];
$markersValues = [];
$filaValues = [];
$qtd = 0;

if ($lista) {
    while ($row = mysqli_fetch_assoc($lista)) {
        $qtd++;
        $cod = mysqli_real_escape_string($con, $row['codcadastro']);
        $nome = mysqli_real_escape_string($con, $row['NOME'] ?? '');
        $lat = !empty($row['elat']) ? mysqli_real_escape_string($con, $row['elat']) : '-11.84';
        $lon = !empty($row['elon']) ? mysqli_real_escape_string($con, $row['elon']) : '-55.52';

        $disparoValues[] = "('$cod', '', '$codPedidoTeste', 'n')";
        $markersValues[] = "('$nome', '$cod', '', '', '$lat', '$lon', '1', '$codPedidoTeste', '1')";

        $primeiro = $nome !== '' ? explode(' ', trim($nome))[0] : '';
        $corpo = $primeiro !== '' ? "$primeiro, voce tem uma nova solicitacao!" : "Nova solicitacao disponivel!";
        $filaValues[] = "($cod, '[TESTE] Novo Pedido', '" . mysqli_real_escape_string($con, $corpo) . "', '/meus-orcamentos.php', 'pendente', NOW())";
    }
}

// 2) INSERTs em massa (igual ao novomapa)
foreach (array_chunk($disparoValues, 200) as $bloco) {
    @mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito) VALUES " . implode(',', $bloco));
}
foreach (array_chunk($markersValues, 200) as $bloco) {
    @mysqli_query($con, "INSERT INTO markers (nome, codcadastro, valor_min, valor_max, lat, lon, type, codpedido, qtdestrelas) VALUES " . implode(',', $bloco));
}

// 3) Enfileira as notificações
foreach (array_chunk($filaValues, 500) as $bloco) {
    @mysqli_query($con, "INSERT INTO push_fila (user_id, title, body, url, status, created_at) VALUES " . implode(',', $bloco));
}

if ($dispararWorker) {
    dispararWorkerFila();
}

$ms = round((microtime(true) - $t0) * 1000);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'prestadores' => $qtd,
    'ms' => $ms,
    'req' => isset($_GET['req']) ? $_GET['req'] : null
]);
