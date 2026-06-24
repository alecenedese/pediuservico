<?php
/**
 * TESTE DE FLUXO - Orçamentos Cliente + Prestador
 * Simula automaticamente o fluxo de aceitar e visualizar orçamentos
 * como PRESTADOR e como CLIENTE.
 *
 * Acesse: https://seudominio/pediuservico/teste-fluxo-orcamentos.php?run=fluxo2024
 *
 * Cobre:
 *  - Item 7: pedido permanece em "pendentes" até prestador firmar acordo (aceito='s')
 *  - Item 10: contador de prestadores "não tenho interesse" (aceito='p' AND visto=1)
 *  - Visualização de orçamentos pelo cliente (get_pedidos.php: pendentes/aceitos/sem_resposta/finalizados)
 *  - Visualização de orçamentos pelo prestador (novos / aguardando / finalizados)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['run']) || $_GET['run'] !== 'fluxo2024') {
    die('Acesse com ?run=fluxo2024');
}

require_once("send.php");
if (!isset($con) || !$con) { die('ERRO: send.php não criou $con'); }
mysqli_report(MYSQLI_REPORT_OFF);
date_default_timezone_set('America/Cuiaba');

$totalTestes = 0;
$passou = 0;
$falhou = 0;
$resultados = [];
$cleanup = [];
$testPrefix = 'FLUXO_' . date('His');

function teste($etapa, $nome, $condicao, $detalhe = '') {
    global $totalTestes, $passou, $falhou, $resultados;
    $totalTestes++;
    $status = $condicao ? 'OK' : 'FALHOU';
    if ($condicao) $passou++; else $falhou++;
    $resultados[] = ['etapa' => $etapa, 'status' => $status, 'nome' => $nome, 'detalhe' => $detalhe];
}

/**
 * Simula a query de PENDENTES do get_pedidos.php para um cliente/pedido.
 */
function clienteVePendente($con, $clienteId, $pedidoId) {
    $q = mysqli_query($con, "
        SELECT p.codigo
        FROM pedido p
        LEFT JOIN disparo_pedidos dp ON dp.codpedido = p.codigo
        WHERE (dp.codpedido IS NULL OR dp.aceito IN ('n', 'a', 'ac'))
        AND p.status NOT IN ('Cancelado', 'Finalizado')
        AND p.codcli = '$clienteId'
        AND p.codigo = '$pedidoId'
        GROUP BY p.codigo
    ");
    return $q && mysqli_num_rows($q) > 0;
}

/**
 * Simula a query de ACEITOS do get_pedidos.php (apenas aceito='s').
 */
function clienteVeAceito($con, $clienteId, $pedidoId) {
    $q = mysqli_query($con, "
        SELECT p.codigo
        FROM pedido p
        INNER JOIN pega_contato pc ON pc.codpedido = p.codigo
        INNER JOIN disparo_pedidos dp ON dp.codpedido = p.codigo
        WHERE pc.codcliente = '$clienteId'
        AND dp.aceito = 's'
        AND p.codigo = '$pedidoId'
        GROUP BY p.codigo
    ");
    return $q && mysqli_num_rows($q) > 0;
}

/**
 * Conta prestadores "não tenho interesse" (Item 10).
 */
function contaNaoInteressados($con, $pedidoId) {
    $q = mysqli_query($con, "SELECT COUNT(*) as total FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='p' AND visto=1");
    $r = $q ? mysqli_fetch_assoc($q) : null;
    return (int)($r['total'] ?? 0);
}

try {

// ============================================================
// ETAPA 0: PREPARAÇÃO - dados reais
// ============================================================
$qGrupo = @mysqli_query($con, "SELECT codigo, titulo FROM grupos LIMIT 1");
$grupo = $qGrupo ? mysqli_fetch_assoc($qGrupo) : null;
if (!$grupo) { die('ERRO: Tabela grupos vazia'); }
$codGrupo = $grupo['codigo'];
teste('0-PREP', 'Categoria encontrada: ' . $grupo['titulo'], !empty($codGrupo), "codigo=$codGrupo");

// Subcategoria real
$codSubcategoria = $codGrupo;
$qSub = @mysqli_query($con, "SELECT codigo FROM categoria WHERE codgrupo='$codGrupo' LIMIT 1");
if ($qSub && $sub = mysqli_fetch_assoc($qSub)) {
    $codSubcategoria = $sub['codigo'];
}
teste('0-PREP', 'Subcategoria encontrada', !empty($codSubcategoria), "codsub=$codSubcategoria");

// ============================================================
// ETAPA 1: CRIAR 3 PRESTADORES
// ============================================================
$prestadores = [];
for ($i = 1; $i <= 3; $i++) {
    $nome = $testPrefix . "_PRESTADOR_$i";
    $cpf = '888.888.888-' . rand(10,99) . $i;
    $cel = '(66) 97777-' . rand(1000,9999);
    mysqli_query($con, "INSERT INTO parceiro (TIPO, NOME, CNPJ_CPF, CELULAR, ESTADO, MUNICIPIO, senha, dataCad)
        VALUES ('pre', '$nome', '$cpf', '$cel', 'MT', 'Sinop', '123', NOW())");
    $pid = mysqli_insert_id($con);
    $prestadores[$i] = ['id' => $pid, 'nome' => $nome, 'cpf' => $cpf, 'cel' => $cel];
    $cleanup[] = "DELETE FROM parceiro WHERE id='$pid'";

    // Vincula à subcategoria
    mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codsubcategoria, codcategoria) VALUES ('$pid', '$codSubcategoria', '$codGrupo')");
    $cleanup[] = "DELETE FROM categoria_prestador WHERE codcadastro='$pid'";

    // Moedas
    mysqli_query($con, "INSERT INTO quantidade_pedidos (codcadastro, qtd, data, tipo) VALUES ('$pid', '20', NOW(), 'pre')");
    $cleanup[] = "DELETE FROM quantidade_pedidos WHERE codcadastro='$pid' AND tipo='pre'";
}
teste('1-PRESTADOR', '3 prestadores criados', count($prestadores) === 3,
    'IDs: ' . implode(', ', array_map(fn($p) => $p['id'], $prestadores)));

// ============================================================
// ETAPA 2: CRIAR CLIENTE
// ============================================================
$clienteNome = $testPrefix . '_CLIENTE';
$clienteCpf = '777.777.777-' . rand(10,99);
$clienteCel = '(66) 96666-' . rand(1000,9999);
mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad)
    VALUES ('', '$clienteNome', '$clienteCpf', '', '$clienteCel', 'MT', 'Sinop', NOW())");
$clienteId = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM clientes WHERE id='$clienteId'";
teste('2-CLIENTE', 'Cliente criado', $clienteId > 0, "ID=$clienteId");

// ============================================================
// ETAPA 3: CLIENTE CRIA PEDIDO
// ============================================================
$descPedido = $testPrefix . ' - Pedido fluxo orçamentos';
mysqli_query($con, "INSERT INTO pedido (categoria, subcategoria, local, tempo, descricao, valor, data_hora, lat, log, status, codcli)
    VALUES ('$codGrupo', '$codSubcategoria', 'Sinop-MT', 'Hoje', '$descPedido', '', NOW(), '-11.86', '-55.50', 'Procurando Prestador', '$clienteId')");
$pedidoId = mysqli_insert_id($con);
$cleanup[] = "DELETE FROM pedido WHERE codigo='$pedidoId'";
teste('3-PEDIDO', 'Pedido criado pelo cliente', $pedidoId > 0, "codigo=$pedidoId");

// ============================================================
// ETAPA 4: DESPACHO PARA OS 3 PRESTADORES
// ============================================================
foreach ($prestadores as $p) {
    mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito, visto)
        VALUES ('".$p['id']."', '', '$pedidoId', 'n', 0)");
}
$cleanup[] = "DELETE FROM disparo_pedidos WHERE codpedido='$pedidoId'";
$qDisp = mysqli_query($con, "SELECT COUNT(*) as c FROM disparo_pedidos WHERE codpedido='$pedidoId' AND aceito='n'");
$dispCount = $qDisp ? (int)(mysqli_fetch_assoc($qDisp)['c'] ?? 0) : 0;
teste('4-DESPACHO', 'Pedido despachado para 3 prestadores', $dispCount === 3, "disparos=$dispCount");

// Prestador 1 vê o pedido em "Novos" (aceito='n')
$qNovos = mysqli_query($con, "SELECT COUNT(*) as c FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."' AND aceito='n'");
$novosCount = $qNovos ? (int)(mysqli_fetch_assoc($qNovos)['c'] ?? 0) : 0;
teste('4-DESPACHO', 'Prestador 1 vê pedido em NOVOS', $novosCount === 1, '');

// ============================================================
// ETAPA 5: PRESTADOR 3 CLICA "NÃO TENHO INTERESSE" (Item 10)
// ============================================================
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=1
    WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[3]['id']."' AND aceito='n'");
$afetados = mysqli_affected_rows($con);
teste('5-NAO-INTERESSE', 'Prestador 3 marcou "não tenho interesse"', $afetados === 1, "afetados=$afetados");

$naoInteressados = contaNaoInteressados($con, $pedidoId);
teste('5-NAO-INTERESSE', 'Contador de não interessados = 1 (Item 10)', $naoInteressados === 1,
    "count=$naoInteressados");

// Garante que perda por escolha de outro (visto=0) NÃO conta no Item 10
mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito, visto)
    VALUES ('0', '', '$pedidoId', 'p', 0)");
$naoInteressados2 = contaNaoInteressados($con, $pedidoId);
teste('5-NAO-INTERESSE', 'Perda com visto=0 NÃO conta no Item 10', $naoInteressados2 === 1,
    "count ainda=$naoInteressados2 (esperado 1)");
mysqli_query($con, "DELETE FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='0'");

// ============================================================
// ETAPA 6: PRESTADOR 1 ENVIA PROPOSTA (aceito='a')
// ============================================================
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='a', visto=0 WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
mysqli_query($con, "UPDATE pedido SET status='Proposta Aceita' WHERE codigo='$pedidoId'");
// markers type 2 (proposta enviada) — necessário para a tela "aguardando" do prestador
mysqli_query($con, "INSERT INTO markers (nome, codcadastro, valor_min, valor_max, lat, lon, type, codpedido, qtdestrelas)
    VALUES ('".$prestadores[1]['nome']."', '".$prestadores[1]['id']."', '50', '100', '-11.86', '-55.50', '2', '$pedidoId', '5')");
$cleanup[] = "DELETE FROM markers WHERE codpedido='$pedidoId'";

$qProp = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
$prop = $qProp ? mysqli_fetch_assoc($qProp) : null;
teste('6-PROPOSTA', 'Prestador 1 enviou proposta (aceito=a)', $prop && $prop['aceito'] === 'a',
    'aceito=' . ($prop['aceito'] ?? 'NULL'));

// Cliente continua vendo em PENDENTES (proposta recebida, ainda não firmada)
teste('6-PROPOSTA', 'Cliente vê pedido em PENDENTES (proposta a caminho)',
    clienteVePendente($con, $clienteId, $pedidoId), '');

// ============================================================
// ETAPA 7: CLIENTE ACEITA PROPOSTA DO PRESTADOR 1 (aceito='ac')
// Item 7: prestador SEM moedas -> aguarda firmar acordo
// ============================================================
mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento)
    VALUES ('$clienteNome', '$clienteCel', '$pedidoId', '".$prestadores[1]['id']."', '$clienteId', 'sim')");
$cleanup[] = "DELETE FROM pega_contato WHERE codpedido='$pedidoId'";
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='ac', visto=0 WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");

$qAc = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
$rowAc = $qAc ? mysqli_fetch_assoc($qAc) : null;
teste('7-ACEITE', 'Cliente aceitou proposta (aceito=ac)',
    $rowAc && $rowAc['aceito'] === 'ac', '');

// Item 7: com status 'ac', pedido DEVE continuar em PENDENTES e NÃO em ACEITOS
teste('7-ACEITE', 'Item 7: pedido CONTINUA em PENDENTES (status ac)',
    clienteVePendente($con, $clienteId, $pedidoId), 'Aguardando prestador firmar acordo');
teste('7-ACEITE', 'Item 7: pedido NÃO aparece em ACEITOS ainda',
    !clienteVeAceito($con, $clienteId, $pedidoId), 'Correto: ac != s');

// ============================================================
// ETAPA 8: PRESTADOR 1 FIRMA ACORDO / DEBITA MOEDAS (aceito='s')
// ============================================================
// Debita moedas
mysqli_query($con, "UPDATE quantidade_pedidos SET qtd = qtd - 5 WHERE codcadastro='".$prestadores[1]['id']."' AND tipo='pre'");
// Outros prestadores viram perdidos (visto=0, não contam no Item 10)
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='p', visto=0 WHERE codpedido='$pedidoId' AND codcadastro NOT IN ('".$prestadores[1]['id']."') AND aceito IN ('n','a')");
// Prestador 1 confirma
mysqli_query($con, "UPDATE disparo_pedidos SET aceito='s', visto=0 WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
mysqli_query($con, "UPDATE pedido SET status='Prestador Disponível' WHERE codigo='$pedidoId'");

$qS = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
$rowS = $qS ? mysqli_fetch_assoc($qS) : null;
teste('8-ACORDO', 'Prestador 1 firmou acordo (aceito=s)',
    $rowS && $rowS['aceito'] === 's', '');

// Item 7: agora o pedido DEVE aparecer em ACEITOS
teste('8-ACORDO', 'Item 7: pedido APARECE em ACEITOS após acordo',
    clienteVeAceito($con, $clienteId, $pedidoId), 'status=s');

// Contador de não interessados não foi afetado pela perda dos outros (visto=0)
$naoInteressados3 = contaNaoInteressados($con, $pedidoId);
teste('8-ACORDO', 'Item 10: contador permanece 1 (perdas não contam)',
    $naoInteressados3 === 1, "count=$naoInteressados3");

// ============================================================
// ETAPA 9: PRESTADOR VÊ PEDIDO EM "AGUARDANDO/ACEITOS"
// (meus-orcamentos-aguardando.php usa aceito IN ('a','ac'); aceitos usa 's')
// ============================================================
// Verifica que prestador 1 tem o pedido com markers type 2 (necessário p/ tela aguardando)
$qMark = mysqli_query($con, "SELECT COUNT(*) as c FROM markers WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."' AND type='2'");
$markCount = $qMark ? (int)(mysqli_fetch_assoc($qMark)['c'] ?? 0) : 0;
teste('9-PRESTADOR-VE', 'Marker type=2 existe para prestador (tela aguardando)',
    $markCount === 1, '');

// Prestador 2 (perdedor) vê em PERDIDOS (aceito='p', visto=0)
$qPerd = mysqli_query($con, "SELECT aceito, visto FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[2]['id']."'");
$perd = $qPerd ? mysqli_fetch_assoc($qPerd) : null;
teste('9-PRESTADOR-VE', 'Prestador 2 vê pedido em PERDIDOS (p, visto=0)',
    $perd && $perd['aceito'] === 'p' && (int)$perd['visto'] === 0, 'aceito='.($perd['aceito']??'?'));

// ============================================================
// ETAPA 10: CLIENTE FINALIZA E AVALIA (aceito='f')
// ============================================================
// Cria tabela avaliacoes se necessário e insere avaliação
$temAvaliacoes = mysqli_query($con, "SHOW TABLES LIKE 'avaliacoes'");
if ($temAvaliacoes && mysqli_num_rows($temAvaliacoes) > 0) {
    // Garante que as colunas cliente e denuncia existam (igual ao salvar-avaliacao.php)
    $colCli = mysqli_query($con, "SHOW COLUMNS FROM avaliacoes LIKE 'cliente'");
    if ($colCli && mysqli_num_rows($colCli) == 0) {
        mysqli_query($con, "ALTER TABLE avaliacoes ADD COLUMN cliente VARCHAR(255) DEFAULT NULL");
    }
    $colDen = mysqli_query($con, "SHOW COLUMNS FROM avaliacoes LIKE 'denuncia'");
    if ($colDen && mysqli_num_rows($colDen) == 0) {
        mysqli_query($con, "ALTER TABLE avaliacoes ADD COLUMN denuncia TINYINT(1) DEFAULT 0");
    }

    $insOk = mysqli_query($con, "INSERT INTO avaliacoes (codcadastro, codpedido, qtd_estrela, mensagem, cliente, denuncia)
        VALUES ('".$prestadores[1]['id']."', '$pedidoId', '5', 'Excelente serviço! (teste)', '$clienteNome', 0)");
    $cleanup[] = "DELETE FROM avaliacoes WHERE codpedido='$pedidoId'";
    teste('10-AVALIACAO', 'Avaliação inserida na tabela', $insOk !== false,
        $insOk !== false ? '' : mysqli_error($con));

    // Marca disparo como finalizado
    mysqli_query($con, "UPDATE disparo_pedidos SET aceito='f', visto=0 WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");

    $qF = mysqli_query($con, "SELECT aceito FROM disparo_pedidos WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
    $rowF = $qF ? mysqli_fetch_assoc($qF) : null;
    teste('10-AVALIACAO', 'Pedido finalizado após avaliação (aceito=f)',
        $rowF && $rowF['aceito'] === 'f', 'aceito='.($rowF['aceito'] ?? 'NULL'));

    // Avaliação registrada
    $qAv = mysqli_query($con, "SELECT qtd_estrela, mensagem FROM avaliacoes WHERE codpedido='$pedidoId' AND codcadastro='".$prestadores[1]['id']."'");
    $av = $qAv ? mysqli_fetch_assoc($qAv) : null;
    teste('10-AVALIACAO', 'Avaliação registrada (5 estrelas)',
        $av && (int)$av['qtd_estrela'] === 5, 'estrelas='.($av['qtd_estrela'] ?? '?'));

    // Prestador vê em FINALIZADOS (query do meus-orcamentos-finalizados.php)
    $qFin = mysqli_query($con, "
        SELECT dp.codpedido, avl.qtd_estrela
        FROM disparo_pedidos dp
        INNER JOIN pedido p ON p.codigo = dp.codpedido
        LEFT JOIN avaliacoes avl ON avl.codcadastro = dp.codcadastro AND avl.codpedido = dp.codpedido
        WHERE dp.codcadastro = '".$prestadores[1]['id']."' AND dp.aceito = 'f' AND dp.codpedido='$pedidoId'
        GROUP BY dp.codpedido
    ");
    teste('10-AVALIACAO', 'Prestador vê pedido em FINALIZADOS',
        $qFin && mysqli_num_rows($qFin) > 0, '');

    // Média das últimas 50 avaliações do prestador
    $qMedia = mysqli_query($con, "SELECT AVG(qtd_estrela) as media FROM (SELECT qtd_estrela FROM avaliacoes WHERE codcadastro='".$prestadores[1]['id']."' AND denuncia=0 ORDER BY id DESC LIMIT 50) t");
    // Fallback se a coluna denuncia não existir
    if (!$qMedia) {
        $qMedia = mysqli_query($con, "SELECT AVG(qtd_estrela) as media FROM (SELECT qtd_estrela FROM avaliacoes WHERE codcadastro='".$prestadores[1]['id']."' ORDER BY id DESC LIMIT 50) t");
    }
    $rowMedia = $qMedia ? mysqli_fetch_assoc($qMedia) : null;
    $media = $rowMedia ? $rowMedia['media'] : null;
    teste('10-AVALIACAO', 'Média de estrelas calculável (últimas 50)',
        $media !== null && (float)$media > 0, 'media='.round((float)$media, 2));
} else {
    teste('10-AVALIACAO', 'Tabela avaliacoes não existe (pulando)', false, 'Crie a tabela avaliacoes');
}

// ============================================================
// ETAPA 11: VERIFICAÇÃO DE ARQUIVOS (layout/rodapé novomapa2)
// ============================================================
$novomapa2 = file_get_contents(__DIR__ . '/novomapa2.php');
teste('11-LAYOUT', 'novomapa2.php usa header-app.php',
    strpos($novomapa2, "header-app.php") !== false, '');
teste('11-LAYOUT', 'novomapa2.php usa bottom-nav.php (rodapé)',
    strpos($novomapa2, "bottom-nav.php") !== false, '');
teste('11-LAYOUT', 'novomapa2.php tem layout moderno (.content-area)',
    strpos($novomapa2, "content-area") !== false, '');
teste('11-LAYOUT', 'novomapa2.php tem contador não interessados (Item 10)',
    strpos($novomapa2, "uninterested-counter") !== false, '');
teste('11-LAYOUT', 'novomapa2.php NÃO usa window.history.back bugado',
    strpos($novomapa2, "window.history.back") === false, '');

// meus-orcamentos-cli.php: aba renomeada para "Sem Resposta"
$cli = file_get_contents(__DIR__ . '/meus-orcamentos-cli.php');
teste('11-LAYOUT', 'Aba do consumidor renomeada para "Sem Resposta"',
    strpos($cli, 'Sem Resposta') !== false, '');

// get_pedidos.php: pendentes inclui 'ac', aceitos só 's'
$getPedidos = file_get_contents(__DIR__ . '/get_pedidos.php');
// Verifica de forma tolerante: a cláusula de pendentes deve conter 'ac'
$temAcPendentes = preg_match("/aceito\s+IN\s*\([^)]*'ac'[^)]*\)/i", $getPedidos) === 1;
// Detalhe: extrai o trecho encontrado para diagnóstico
$trechoDetalhe = '';
if (preg_match("/aceito\s+IN\s*\([^)]*\)/i", $getPedidos, $mTrecho)) {
    $trechoDetalhe = trim($mTrecho[0]);
} else {
    $trechoDetalhe = 'cláusula "aceito IN (...)" não encontrada — arquivo do servidor pode estar desatualizado';
}
teste('11-LAYOUT', 'get_pedidos: pendentes inclui ac (Item 7)',
    $temAcPendentes, $trechoDetalhe);

// ============================================================
// ETAPA 12: VERIFICAÇÃO DOS ITENS 8, 12, 13, 14, 15
// ============================================================

// Item 14: salvar-avaliacao salva o nome do cliente
$salvarAvl = file_get_contents(__DIR__ . '/salvar-avaliacao.php');
teste('12-ITENS', 'Item 14: salvar-avaliacao salva nome do cliente',
    strpos($salvarAvl, 'cliente') !== false && strpos($salvarAvl, "aceito='f'") !== false, '');

// Item 14: listar-avaliacoes calcula média das últimas 50
$listarAvl = file_get_contents(__DIR__ . '/listar-avaliacoes.php');
teste('12-ITENS', 'Item 14: listar-avaliacoes calcula média (LIMIT 50)',
    strpos($listarAvl, 'LIMIT 50') !== false && strpos($listarAvl, 'MÉDIA DE AVALIAÇÕES') !== false, '');
teste('12-ITENS', 'Item 14: listar-avaliacoes usa mysqli (não PDO quebrado)',
    strpos($listarAvl, 'mysqli_query($con, $query_avaliacoes)') !== false, '');

// Item 14: get_providers puxa média real (não fixo "1")
$getProviders = file_get_contents(__DIR__ . '/get_providers.php');
teste('12-ITENS', 'Item 14: get_providers calcula média das avaliações',
    strpos($getProviders, 'AVG(qtd_estrela)') !== false && strpos($getProviders, 'LIMIT 50') !== false, '');

// Item 8: contaAguardando mostra timer de recarga
$contaAg = file_get_contents(__DIR__ . '/contaAguardando.php');
teste('12-ITENS', 'Item 8: contaAguardando tem timer de recarga',
    strpos($contaAg, 'timer_acordo') !== false && strpos($contaAg, 'Tempo restante') !== false, '');

// Item 13: finalizados do prestador tem fallbacks robustos
$finalizados = file_get_contents(__DIR__ . '/meus-orcamentos-finalizados.php');
teste('12-ITENS', 'Item 13: finalizados tem fallback de query robusto',
    substr_count($finalizados, 'Fallback') >= 2, '');

// Item 15: header tem dropdown de menu
$header = file_get_contents(__DIR__ . '/header-app.php');
teste('12-ITENS', 'Item 15: header tem dropdown do usuário',
    strpos($header, 'user-menu-dropdown') !== false && strpos($header, 'toggleUserMenu') !== false, '');
teste('12-ITENS', 'Item 15: menu tem opção Verificação',
    strpos($header, 'verificacao.php') !== false, '');
teste('12-ITENS', 'Item 15: menu tem Dados pessoais, Categorias, Endereço, Sair',
    strpos($header, 'Dados pessoais') !== false && strpos($header, 'Categorias') !== false
    && strpos($header, 'Endereço') !== false && strpos($header, 'sair.php') !== false, '');

// Item 15: página de verificação existe com os 4 uploads
teste('12-ITENS', 'Item 15: verificacao.php existe', file_exists(__DIR__ . '/verificacao.php'), '');
if (file_exists(__DIR__ . '/verificacao.php')) {
    $verif = file_get_contents(__DIR__ . '/verificacao.php');
    teste('12-ITENS', 'Item 15: verificação tem 4 uploads (pessoal/doc/comprovante/antecedentes)',
        strpos($verif, 'foto_pessoal') !== false && strpos($verif, 'foto_documento') !== false
        && strpos($verif, 'foto_comprovante') !== false && strpos($verif, 'foto_antecedentes') !== false, '');
}

// Item 15: painel do consumidor NÃO tem mais botão "Cadastro" na grid
teste('12-ITENS', 'Item 15: painel consumidor sem botão Cadastro na grid',
    substr_count($cli, 'editar-cadastro-cliente.php') === 0 || strpos($cli, '<span>Cadastro</span>') === false, '');

// Item 12: aba duplicada resolvida (só uma aba "Sem Resposta" + uma "Finalizados")
teste('12-ITENS', 'Item 12: abas sem duplicação de finalizados',
    substr_count($cli, "switchTab('finalizados')") === 1 && substr_count($cli, "switchTab('sem_resposta')") === 1, '');

} catch (Exception $e) {
    echo '<h1 style="color:red">ERRO: ' . htmlspecialchars($e->getMessage()) . '</h1>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

// ============================================================
// LIMPEZA
// ============================================================
$limposOk = 0; $limposFail = 0;
foreach (array_reverse($cleanup) as $sql) {
    if (mysqli_query($con, $sql)) $limposOk++; else $limposFail++;
}
teste('CLEANUP', 'Dados de teste removidos', $limposFail === 0,
    "$limposOk queries OK" . ($limposFail > 0 ? ", $limposFail falharam" : ''));

// ============================================================
// HTML DO RESULTADO
// ============================================================
$etapas = [
    '0-PREP' => '0️⃣ Preparação',
    '1-PRESTADOR' => '👷 Etapa 1: Criar 3 Prestadores',
    '2-CLIENTE' => '👤 Etapa 2: Criar Cliente',
    '3-PEDIDO' => '📋 Etapa 3: Cliente Cria Pedido',
    '4-DESPACHO' => '📨 Etapa 4: Despacho aos Prestadores',
    '5-NAO-INTERESSE' => '🚫 Etapa 5: "Não Tenho Interesse" (Item 10)',
    '6-PROPOSTA' => '💰 Etapa 6: Prestador Envia Proposta',
    '7-ACEITE' => '✅ Etapa 7: Cliente Aceita (Item 7)',
    '8-ACORDO' => '🤝 Etapa 8: Prestador Firma Acordo',
    '9-PRESTADOR-VE' => '👀 Etapa 9: Prestador Vê Orçamentos',
    '10-AVALIACAO' => '⭐ Etapa 10: Cliente Avalia',
    '11-LAYOUT' => '📄 Etapa 11: Layout/Arquivos',
    '12-ITENS' => '🔧 Etapa 12: Itens 8/12/13/14/15',
    'CLEANUP' => '🧹 Limpeza',
];
$stepNames = ['Prep','Prestadores','Cliente','Pedido','Despacho','Não-Interesse','Proposta','Aceite','Acordo','Prestador-Vê','Avaliação','Layout','Itens-8-15','Limpeza'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teste de Fluxo - Orçamentos</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#1a2332;color:#fff;padding:16px}
.header{text-align:center;padding:20px;margin-bottom:20px;background:rgba(0,212,255,.1);border-radius:12px;border:1px solid rgba(0,212,255,.3)}
.header h1{color:#00d4ff;font-size:22px;margin-bottom:6px}
.summary{display:flex;gap:12px;justify-content:center;margin:16px 0;flex-wrap:wrap}
.box{padding:14px 28px;border-radius:8px;font-size:22px;font-weight:bold;text-align:center}
.box small{font-size:12px;display:block;margin-top:4px}
.box.total{background:rgba(0,212,255,.2);color:#00d4ff}
.box.ok{background:rgba(40,167,69,.2);color:#28a745}
.box.fail{background:rgba(220,53,69,.2);color:#dc3545}
.etapa{margin:16px 0}
.etapa h3{color:#00d4ff;margin-bottom:8px;padding:8px 0;border-bottom:1px solid rgba(0,212,255,.2);font-size:15px}
.t{display:flex;align-items:center;gap:10px;padding:6px 10px;margin:3px 0;border-radius:5px;font-size:13px;background:rgba(255,255,255,.04)}
.t.ok{border-left:3px solid #28a745}
.t.fail{border-left:3px solid #dc3545;background:rgba(220,53,69,.1)}
.b{padding:2px 8px;border-radius:3px;font-weight:bold;font-size:11px;white-space:nowrap}
.b.ok{background:#28a745;color:#fff}
.b.fail{background:#dc3545;color:#fff}
.n{flex:1}
.d{font-size:11px;color:#888;max-width:350px;word-break:break-all}
.flow{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin:12px 0}
.flow .step{padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600}
.flow .step.done{background:rgba(40,167,69,.3);color:#28a745;border:1px solid rgba(40,167,69,.5)}
.flow .step.err{background:rgba(220,53,69,.3);color:#dc3545;border:1px solid rgba(220,53,69,.5)}
.flow .arrow{color:#00d4ff;font-size:16px;line-height:30px}
.ts{text-align:center;color:#555;margin-top:16px;font-size:11px}
</style>
</head>
<body>
<div class="header">
    <h1>🧪 Teste de Fluxo — Orçamentos (Cliente + Prestador)</h1>
    <p style="font-size:13px;color:#aaa">Pedido → Não-Interesse → Proposta → Aceite → Acordo → Avaliação</p>
    <div class="summary">
        <div class="box total"><?=$totalTestes?><br><small>Total</small></div>
        <div class="box ok"><?=$passou?><br><small>Passou ✅</small></div>
        <div class="box fail"><?=$falhou?><br><small>Falhou ❌</small></div>
    </div>
    <?php if($falhou==0):?>
        <p style="color:#28a745;font-size:18px;font-weight:bold;margin-top:8px">🎉 TODOS OS TESTES PASSARAM!</p>
    <?php else:?>
        <p style="color:#dc3545;font-size:18px;font-weight:bold;margin-top:8px">⚠️ <?=$falhou?> TESTE(S) FALHARAM</p>
    <?php endif;?>
    <div class="flow">
    <?php
    $etapaKeys = array_keys($etapas);
    foreach($etapaKeys as $i => $k) {
        $etapaFailed = false;
        foreach($resultados as $r) { if($r['etapa']===$k && $r['status']==='FALHOU') $etapaFailed=true; }
        $cls = $etapaFailed ? 'err' : 'done';
        echo '<span class="step '.$cls.'">'.$stepNames[$i].'</span>';
        if($i < count($etapaKeys)-1) echo '<span class="arrow">→</span>';
    }
    ?>
    </div>
</div>

<?php foreach($etapas as $key => $titulo):
    $items = array_filter($resultados, function($r) use($key) { return $r['etapa']===$key; });
    if(empty($items)) continue;
?>
<div class="etapa">
    <h3><?=$titulo?></h3>
    <?php foreach($items as $r):?>
    <div class="t <?=$r['status']==='OK'?'ok':'fail'?>">
        <span class="b <?=$r['status']==='OK'?'ok':'fail'?>"><?=$r['status']?></span>
        <span class="n"><?=htmlspecialchars($r['nome'])?></span>
        <span class="d"><?=htmlspecialchars($r['detalhe'])?></span>
    </div>
    <?php endforeach;?>
</div>
<?php endforeach;?>

<div class="ts">
    Executado em <?=date('d/m/Y H:i:s')?> | <?=$_SERVER['HTTP_HOST']?> | PHP <?=phpversion()?> | Prefix: <?=$testPrefix?>
</div>
</body>
</html>
