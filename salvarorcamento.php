<?php
http_response_code(200);
require_once("send.php");
date_default_timezone_set('America/Cuiaba');

// Garante que mysqli devolva false (em vez de throw) em queries com erro
// (PHP 8.1+ usa MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT por padrao -> exceptions)
@mysqli_report(MYSQLI_REPORT_OFF);

// Em caso de erro fatal, devolve JSON em vez de HTML para o fetch nao quebrar
set_exception_handler(function($e) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $err['message']]);
    }
});
$datat = date("Y-m-d H:i:s"); 

$grupo = isset($_GET['codgrupo']) ? $_GET['codgrupo'] : '';
$subcategoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$local = isset($_GET['local']) ? $_GET['local'] : '';
$tempo = isset($_GET['tempo']) ? $_GET['tempo'] : '';
$descreva = isset($_POST['descricao']) ? $_POST['descricao'] : '';

// Reuso de pedido: quando o usuário volta de novomapa.php e prossegue de novo,
// passamos o codpedido para ATUALIZAR o pedido existente em vez de criar um novo (evita duplicacao).
$codpedidoReuso = isset($_GET['codpedido']) ? intval($_GET['codpedido']) : 0;

$dir = 'fotos/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$foto_1 = null;
$foto_2 = null;
$foto_3 = null;
$foto_4 = null;

// Processar fotos enviadas como array
if (isset($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
    for ($i = 0; $i < count($_FILES['fotos']['name']) && $i < 4; $i++) {
        if (!empty($_FILES['fotos']['name'][$i])) {
            $fotoArray = [
                'name' => $_FILES['fotos']['name'][$i],
                'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
                'error' => $_FILES['fotos']['error'][$i],
                'size' => $_FILES['fotos']['size'][$i],
                'type' => $_FILES['fotos']['type'][$i]
            ];
            
            switch($i) {
                case 0: $foto_1 = $fotoArray; break;
                case 1: $foto_2 = $fotoArray; break;
                case 2: $foto_3 = $fotoArray; break;
                case 3: $foto_4 = $fotoArray; break;
            }
        }
    }
}

function uploadFoto($foto, $dir, $tipo) {
    $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $nomeFoto = date("Ymd_His").'_'.$tipo.'.'.$ext;
    $destino = $dir.'/'.$nomeFoto;
    move_uploaded_file($foto['tmp_name'], $destino);
    return $nomeFoto;
}

$foto_12 = $foto_1 ? "'".uploadFoto($foto_1, $dir, "foto_1")."'" : "NULL";
$foto_22 = $foto_2 ? "'".uploadFoto($foto_2, $dir, "foto_2")."'" : "NULL";
$foto_32 = $foto_3 ? "'".uploadFoto($foto_3, $dir, "foto_3")."'" : "NULL";
$foto_42 = $foto_4 ? "'".uploadFoto($foto_4, $dir, "foto_4")."'" : "NULL";

// Item 18: Processar múltiplos áudios enviados
$audioSql = "NULL";
$audioFilenames = [];

if (isset($_FILES['audios']) && is_array($_FILES['audios']['name'])) {
    $audioDir = 'audios/';
    if (!is_dir($audioDir)) {
        mkdir($audioDir, 0777, true);
    }
    
    for ($i = 0; $i < count($_FILES['audios']['name']); $i++) {
        if ($_FILES['audios']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['audios']['name'][$i], PATHINFO_EXTENSION);
            if (!$ext) {
                // Detectar extensão pelo mime type
                $mime = $_FILES['audios']['type'][$i];
                if (strpos($mime, 'mp4') !== false) $ext = 'mp4';
                elseif (strpos($mime, 'ogg') !== false) $ext = 'ogg';
                else $ext = 'webm';
            }
            $nomeAudio = date('Ymd_His') . '_audio_' . $i . '.' . $ext;
            move_uploaded_file($_FILES['audios']['tmp_name'][$i], $audioDir . $nomeAudio);
            $audioFilenames[] = $nomeAudio;
        }
    }
    
    if (count($audioFilenames) > 0) {
        // Armazena múltiplos áudios separados por vírgula
        $audioSql = "'" . mysqli_real_escape_string($con, implode(',', $audioFilenames)) . "'";
    }
} elseif (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
    // Fallback para compatibilidade com código antigo (single audio)
    $audioDir = 'audios/';
    if (!is_dir($audioDir)) {
        mkdir($audioDir, 0777, true);
    }
    $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
    if (!$ext) {
        $mime = $_FILES['audio']['type'];
        if (strpos($mime, 'mp4') !== false) $ext = 'mp4';
        elseif (strpos($mime, 'ogg') !== false) $ext = 'ogg';
        else $ext = 'webm';
    }
    $nomeAudio = date('Ymd_His') . '_audio.' . $ext;
    move_uploaded_file($_FILES['audio']['tmp_name'], $audioDir . $nomeAudio);
    $audioSql = "'" . mysqli_real_escape_string($con, $nomeAudio) . "'";
}

$cookieName = "servicos_oferecidos_" . $subcategoria;
$servicosCsv = '';

if (!empty($_COOKIE[$cookieName])) {
  $val = $_COOKIE[$cookieName];

  // base64-url -> base64 normal
  $b64 = strtr($val, '-_', '+/');
  $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);

  $json = base64_decode($b64);
  $payload = json_decode($json, true);

  if (is_array($payload) && isset($payload['titulos']) && is_array($payload['titulos'])) {
    // limpa e remove duplicados
    $titulos = array_values(array_unique(array_filter(array_map('trim', $payload['titulos']))));

    // "titulo1,titulo2,titulo3"
    $servicosCsv = implode(',', $titulos);
  }
}

// ESCAPA para SQL (para não quebrar com aspas/acentos)
$servicosSql = mysqli_real_escape_string($con, $servicosCsv);

$codcli = '0';
if (!empty($_COOKIE['id_cliente'])) {
    $codcli = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
} elseif (!empty($_COOKIE['codcliente'])) {
    $codcli = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
} else {
    // Resolve pelo CPF/CNPJ unificado
    $cpfLimpo = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
    if ($cpfLimpo !== '') {
        $cpfLimpoEsc = mysqli_real_escape_string($con, $cpfLimpo);
        $qCli = mysqli_query($con, "SELECT id FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfLimpoEsc' LIMIT 1");
        if ($qCli && $rCli = mysqli_fetch_array($qCli)) {
            $codcli = (int)$rCli['id'];
        }
    }
}

// Verifica se é reuso de pedido existente (usuário voltou e prosseguiu novamente).
// Só reaproveita se o pedido pertence ao cliente e ainda está "Procurando Prestador".
$reusarPedido = false;
if ($codpedidoReuso > 0 && !empty($codcli)) {
    $qReuso = mysqli_query($con, "SELECT codigo FROM pedido WHERE codigo='".intval($codpedidoReuso)."' AND codcli='$codcli' AND status='Procurando Prestador' LIMIT 1");
    if ($qReuso && mysqli_num_rows($qReuso) > 0) {
        $reusarPedido = true;
    }
}

// Item 5: Limitar 2 pedidos por dia da mesma categoria (subcategoria)
// (não aplica quando estamos apenas reaproveitando um pedido existente)
if (!$reusarPedido && !empty($codcli) && !empty($subcategoria)) {
    $checkLimit = mysqli_query($con, "
        SELECT COUNT(*) as total 
        FROM pedido 
        WHERE codcli = '$codcli' 
        AND subcategoria = '$subcategoria' 
        AND DATE(data_hora) = CURDATE()
    ");
    
    if ($checkLimit) {
        $limitRow = mysqli_fetch_array($checkLimit);
        if ($limitRow['total'] >= 2) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Você já fez 2 pedidos desta categoria hoje. Aguarde até amanhã para fazer um novo pedido desta categoria.',
                'limite_atingido' => true
            ]);
            exit;
        }
    }
}


if ($reusarPedido) {
    // ATUALIZA o pedido existente (evita criar duplicado ao voltar e prosseguir)
    $sets = [];
    $sets[] = "categoria='$grupo'";
    $sets[] = "subcategoria='$subcategoria'";
    $sets[] = "local='$local'";
    $sets[] = "tempo='$tempo'";
    $sets[] = "descricao='$descreva'";
    $sets[] = "data_hora='$datat'";
    $sets[] = "servicos='$servicosSql'";
    $sets[] = "status='Procurando Prestador'";
    // Fotos e áudio só são atualizados se foram reenviados nesta submissão
    if ($foto_12 !== 'NULL') $sets[] = "foto_1=$foto_12";
    if ($foto_22 !== 'NULL') $sets[] = "foto_2=$foto_22";
    if ($foto_32 !== 'NULL') $sets[] = "foto_3=$foto_32";
    if ($foto_42 !== 'NULL') $sets[] = "foto_4=$foto_42";

    $setsComAudio = $sets;
    if ($audioSql !== 'NULL') $setsComAudio[] = "audio=$audioSql";

    $updateSql = "UPDATE pedido SET ".implode(', ', $setsComAudio)." WHERE codigo='".intval($codpedidoReuso)."'";
    $queryPedi = mysqli_query($con, $updateSql);

    // Fallback: se falhou (coluna audio pode nao existir), tenta sem audio
    if (!$queryPedi) {
        $updateSql2 = "UPDATE pedido SET ".implode(', ', $sets)." WHERE codigo='".intval($codpedidoReuso)."'";
        $queryPedi = mysqli_query($con, $updateSql2) or die(mysqli_error($con));
    }

    $pedidoIdFinal = intval($codpedidoReuso);
} else {
    $queryPedi = mysqli_query($con, "
      INSERT INTO pedido
      (categoria, subcategoria, local, tempo, descricao, valor, data_hora, lat, log, foto_1, foto_2, foto_3, foto_4, status, servicos, audio, codcli)
      VALUES
      ('$grupo', '$subcategoria', '$local', '$tempo', '$descreva', '', '$datat', '', '', $foto_12, $foto_22, $foto_32, $foto_42, 'Procurando Prestador', '$servicosSql', $audioSql, '$codcli')
    ");

    // Fallback: se falhou (coluna audio pode nao existir), tenta sem audio
    if (!$queryPedi) {
        $queryPedi = mysqli_query($con, "
          INSERT INTO pedido
          (categoria, subcategoria, local, tempo, descricao, valor, data_hora, lat, log, foto_1, foto_2, foto_3, foto_4, status, servicos, codcli)
          VALUES
          ('$grupo', '$subcategoria', '$local', '$tempo', '$descreva', '', '$datat', '', '', $foto_12, $foto_22, $foto_32, $foto_42, 'Procurando Prestador', '$servicosSql', '$codcli')
        ") or die(mysqli_error($con));
    }

    $pedidoIdFinal = mysqli_insert_id($con);
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Serviço cadastrado com sucesso!',
    'pedido_id' => $pedidoIdFinal,
    'data' => [
        'descricao' => $descreva,
        'fotos_enviadas' => [
            'foto_1' => $foto_1 ? true : false,
            'foto_2' => $foto_2 ? true : false,
            'foto_3' => $foto_3 ? true : false,
            'foto_4' => $foto_4 ? true : false
        ]
    ]
]);

?>
