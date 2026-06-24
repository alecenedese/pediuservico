<?php
	$hostname = "177.53.140.149";
	$bancodedados = "paxsaoju1_banco";
	$usuario = "paxsaoju1_user";
	$senha = ")qQ~eKZ@fF19"; 
    $pdo = new PDO("mysql:host=$hostname;dbname=$bancodedados", $usuario, $senha);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Cabeçalhos para permitir AJAX
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

function utf8ize($data) {
    if (is_array($data)) {
        return array_map('utf8ize', $data);
    } elseif (is_string($data)) {
        return mb_convert_encoding($data, 'UTF-8', 'auto');
    }
    return $data;
}


try {
    // Conecta ao banco de dados
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$codpedido = $_GET['codpedido'];
    // Consulta os prestadores
// Busca todos os registros, incluindo o ponto inicial (type = 3)
$stmt = $pdo->query("SELECT *, 
   CASE WHEN type = 2 THEN 'Disponível agora' ELSE 'Aguardando prestador aceitar' END as availability,
   CASE WHEN type = 2 THEN 2 ELSE 1 END AS prioridade
FROM markers 
WHERE codpedido = $codpedido
ORDER BY prioridade DESC, id_location DESC");
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Identifica o ponto inicial (type = 3)
$startingPoint = null;
foreach ($providers as $provider) {
    if ($provider['type'] == 3) {
        $startingPoint = [
            'lat' => floatval($provider['lat']),
            'lon' => floatval($provider['lon'])
        ];
        break;
    }
}

// Função para calcular a distância entre dois pontos (Haversine)
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Raio da Terra em km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Formata os dados para o formato esperado pelo JavaScript, excluindo o ponto inicial
$formattedProviders = [];
$prestadoresVistos = []; // Evita exibir o mesmo prestador mais de uma vez (markers duplicados)
foreach ($providers as $provider) {
    if ($provider['type'] == 3) {
        continue; // Ignora o ponto inicial na listagem final
    }

    // Deduplica: se este prestador já foi adicionado, ignora as linhas repetidas.
    // Como a query ordena por prioridade DESC, a primeira ocorrência é a melhor (ex.: "Disponível agora").
    $codCadAtual = (int)$provider['codcadastro'];
    if ($codCadAtual > 0) {
        if (isset($prestadoresVistos[$codCadAtual])) {
            continue;
        }
        $prestadoresVistos[$codCadAtual] = true;
    }

    // Calcula a distância se o ponto inicial foi encontrado
    $distance = $startingPoint
        ? haversineDistance($startingPoint['lat'], $startingPoint['lon'], floatval($provider['lat']), floatval($provider['lon']))
        : 0;
        if($provider['valor_min'] == 0 ){ $valor = ''; } else {
$valor = 'Valor fica entre <b>R$ '.$provider['valor_min'].' a R$ '.$provider['valor_max'].'</b>';
}

        // Item 14: Calcula a média das últimas 50 avaliações do prestador
        $rating = "Novo";
        try {
            $codcad = (int)$provider['codcadastro'];
            $stmtAvg = $pdo->query("SELECT AVG(qtd_estrela) as media, COUNT(*) as total FROM (SELECT qtd_estrela FROM avaliacoes WHERE codcadastro = $codcad ORDER BY id DESC LIMIT 50) t");
            if ($stmtAvg) {
                $rowAvg = $stmtAvg->fetch(PDO::FETCH_ASSOC);
                if ($rowAvg && $rowAvg['total'] > 0 && $rowAvg['media'] !== null) {
                    $rating = number_format((float)$rowAvg['media'], 1, '.', '');
                }
            }
        } catch (PDOException $eAvg) {
            $rating = "Novo";
        }

        // Item 7/12: Busca os selos de verificação do prestador
        $seloVerificado = 0; $seloSeguro = 0; $seloFundador = 0;
        try {
            $codcadS = (int)$provider['codcadastro'];
            $stmtSelo = $pdo->query("SELECT selo_verificado, selo_seguro, parceiro_fundador FROM verificacoes_usuario WHERE id_usuario = $codcadS AND tipo_usuario = 'prestador' ORDER BY id DESC LIMIT 1");
            if ($stmtSelo) {
                $rowSelo = $stmtSelo->fetch(PDO::FETCH_ASSOC);
                if ($rowSelo) {
                    $seloVerificado = (int)($rowSelo['selo_verificado'] ?? 0);
                    $seloSeguro = (int)($rowSelo['selo_seguro'] ?? 0);
                    $seloFundador = (int)($rowSelo['parceiro_fundador'] ?? 0);
                }
            }
        } catch (PDOException $eSelo) {}

        $formattedProviders[] = [
            'id' => (int)$provider['codcadastro'],
            'name' => $provider['nome'],
            'profession' => $valor,
            'rating' => $rating,
            'distance' => number_format($distance, 1, ',', '') . ' km',
            'availability' => $provider['availability'], // Agora usa o campo calculado
            'avatar' => "https://i.pravatar.cc/150?img=70",
            'location' => [floatval($provider['lat']), floatval($provider['lon'])],
            'contraproposta' => $provider['contraproposta'],
            'selo_verificado' => $seloVerificado,
            'selo_seguro' => $seloSeguro,
            'selo_fundador' => $seloFundador,
        ];
}

// Retorna os dados como JSON
echo json_encode($formattedProviders);


} catch (PDOException $e) {
    // Em caso de erro, retorna um array vazio e registra o erro
    error_log("Erro de banco de dados: " . $e->getMessage());
    echo json_encode([]);
}