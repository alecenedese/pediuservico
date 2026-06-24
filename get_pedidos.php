<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Captura erros fatais e devolve como JSON
set_exception_handler(function($e) {
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode(['success'=>false,'error'=>$e->getMessage(),'line'=>$e->getLine()]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['success'=>false,'error'=>$err['message'],'file'=>$err['file'],'line'=>$err['line']]);
    }
});

session_start();
require("send.php"); // Conexão com o banco de dados

header('Content-Type: application/json');
@mysqli_report(MYSQLI_REPORT_OFF);

// Define codcliente (NÃO usa $_COOKIE['id'] pois é cookie de prestador)
$codcliente = 0;
if (isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
    $codcliente = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
} elseif (isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
    $codcliente = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
} else {
    // Resolve pelo CPF/CNPJ unificado
    $cpfLimpo = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
    if ($cpfLimpo !== '') {
        $cpfEsc = mysqli_real_escape_string($con, $cpfLimpo);
        $qR = mysqli_query($con, "SELECT id FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc' LIMIT 1");
        if ($qR && $rR = mysqli_fetch_array($qR)) {
            $codcliente = (int)$rR['id'];
            setcookie('id_cliente', $codcliente, time()+30*24*3600, '/');
        }
    }
}

// 1. Pedidos Pendentes
$codigos_pendentes_sessao = [];
if (!empty($_SESSION['mapas_salvos'])) {
    foreach ($_SESSION['mapas_salvos'] as $codPedido => $mapaUrl) {
        if (is_numeric($codPedido)) {
            $codigos_pendentes_sessao[] = (int)$codPedido;
        }
    }
}

$where_conditions = [];
if ($codcliente > 0) {
    $where_conditions[] = "p.codcli = '$codcliente'";
}
if (!empty($codigos_pendentes_sessao)) {
    $codigos_string = implode(',', $codigos_pendentes_sessao);
    $where_conditions[] = "p.codigo IN ($codigos_string)";
}

$pendentes = [];
if (!empty($where_conditions)) {
    $combined_where = implode(' OR ', $where_conditions);
    $sql_pendentes = "
        SELECT 
            g.titulo as cat, 
            p.codigo, 
            s.titulo AS sub, 
            p.local, 
            p.tempo, 
            p.descricao, 
            p.lat, 
            p.log, 
            p.data_hora, 
            p.valor, 
            p.foto_1, 
            p.foto_2, 
            p.foto_3, 
            p.foto_4, 
            p.audio,
            s.codigo AS codsub,
            p.status
        FROM 
            pedido p
            INNER JOIN categoria s ON p.subcategoria = s.codigo
            INNER JOIN grupos g ON g.codigo = s.codgrupo AND g.codigo = p.categoria
            LEFT JOIN disparo_pedidos dp ON dp.codpedido = p.codigo
        WHERE 
            (dp.codpedido IS NULL OR dp.aceito IN ('n', 'a', 'ac'))
            AND p.status NOT IN ('Cancelado', 'Finalizado')
            AND ($combined_where)
        GROUP BY p.codigo
        ORDER BY p.codigo DESC
    ";
    
    $query_pendentes = mysqli_query($con, $sql_pendentes);
    // Fallback: se falhou (coluna audio pode nao existir), tenta sem audio
    if (!$query_pendentes) {
        $sql_pendentes = str_replace("p.audio,", "", $sql_pendentes);
        $query_pendentes = mysqli_query($con, $sql_pendentes);
    }
    while ($query_pendentes && $row = mysqli_fetch_assoc($query_pendentes)) {
        $mapa_url_do_pedido = '';
        if (!empty($_SESSION['mapas_salvos'])) {
            foreach ($_SESSION['mapas_salvos'] as $codPedido => $mapaUrl) {
                if ((int)$codPedido === (int)$row['codigo']) {
                    $mapa_url_do_pedido = $mapaUrl;
                    break;
                }
            }
        }
        // Fallback: gera URL do mapa se nao encontrou na sessao
        if (empty($mapa_url_do_pedido)) {
            $mapa_url_do_pedido = 'novomapa2.php?codpedido=' . $row['codigo'] . '&subcategoria=' . ($row['codsub'] ?? '');
        }
        
        $status_class = '';
        $status_text = '';
        $dynamic_content = '';
        
        switch ($row['status']) {
            case 'Procurando Prestador':
                $status_class = 'status-1';
                $status_text = 'Procurando Prestador';
                $dynamic_content = '<a href="' . htmlspecialchars($mapa_url_do_pedido) . '" style="display:inline-block;padding:12px 20px;background:linear-gradient(145deg,#00d4ff,#0ea5e9);color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;text-align:center;width:100%;box-shadow:0 4px 12px rgba(0,212,255,0.4);">🔍 ACOMPANHAR BUSCA</a>';
                break;
            case 'Proposta Recebida':
                $status_class = 'status-2';
                $status_text = 'Proposta Recebida';
                $dynamic_content = '<a href="' . htmlspecialchars($mapa_url_do_pedido) . '" style="display:inline-block;padding:12px 20px;background:linear-gradient(145deg,#00d4ff,#0ea5e9);color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;text-align:center;width:100%;box-shadow:0 4px 12px rgba(0,212,255,0.4);">🔍 ACOMPANHAR BUSCA</a>';
                break;
            case 'Prestador Disponível':
                $status_class = 'status-3';
                $status_text = 'Prestador Disponível';
                $dynamic_content = `
                    <div class="action-buttons">
                        <a href="${htmlspecialchars($mapa_url_do_pedido)}&acao=conversa" class="action-button conversa">Ver Conversa</a>
                        <a href="${htmlspecialchars($mapa_url_do_pedido)}&acao=encerrar" class="action-button encerrar">Encerrar O.S</a>
                    </div>`;
                break;
            case 'Proposta Aceita':
                $status_class = 'status-4';
                $status_text = 'Ver propostas';
                $dynamic_content = '<a href="' . htmlspecialchars($mapa_url_do_pedido) . '" style="display:inline-block;padding:10px 20px;background:linear-gradient(145deg,#00d4ff,#0ea5e9);color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;text-align:center;width:100%;">📋 Ver propostas</a>';
                break;
            default:
                $status_class = '';
                $status_text = 'Desconhecido';
                $dynamic_content = '<span>Status desconhecido</span>';
        }

        $fotos = [];
        for ($fi = 1; $fi <= 4; $fi++) {
            if (!empty($row['foto_'.$fi])) {
                $fp = $row['foto_'.$fi];
                if (strpos($fp, 'fotos/') === false && strpos($fp, 'http') === false) $fp = 'fotos/' . $fp;
                $fotos[] = $fp;
            }
        }

        $pendentes[] = [
            'tipo' => 'pendente',
            'codigo' => $row['codigo'],
            'descricao' => $row['descricao'],
            'data_hora' => date('d/m/Y H:i:s', strtotime($row['data_hora'])),
            'cat' => $row['cat'],
            'sub' => $row['sub'],
            'status' => $row['status'],
            'status_class' => $status_class,
            'status_text' => $status_text,
            'dynamic_content' => $dynamic_content,
            'mapa_url' => $mapa_url_do_pedido,
            'lat' => $row['lat'],
            'log' => $row['log'],
            'codsub' => $row['codsub'],
            'tempo' => $row['tempo'],
            'fotos' => $fotos,
            'audio' => isset($row['audio']) && !empty($row['audio']) ? 'audios/' . $row['audio'] : null
        ];
    }
}

// 2. Pedidos Aceitos
$sql_aceitos = "
    SELECT 
        g.titulo, 
        p.codigo, 
        s.titulo AS sub, 
        p.local, 
        p.tempo, 
        p.descricao, 
        p.lat, 
        p.log, 
        p.data_hora, 
        pc.codcadastro, 
        pa.NOME,
        p.foto_1, 
        p.foto_2, 
        p.foto_3, 
        p.foto_4,
        p.audio
    FROM 
        grupos g,
        pedido p,
        categoria s,
        pega_contato pc,
        parceiro pa,
        disparo_pedidos dp
    WHERE 
        pc.codcliente = '$codcliente'
        AND p.categoria = g.codigo
        AND p.subcategoria = s.codigo
        AND pc.codpedido = p.codigo
        AND pc.codcadastro = pa.id
        AND p.codigo = dp.codpedido
        AND dp.aceito = 's'
    GROUP BY p.codigo
    ORDER BY p.codigo DESC
";

$aceitos = [];
$query_aceitos = mysqli_query($con, $sql_aceitos);
// Fallback: se falhou (coluna audio pode nao existir), tenta sem audio
if (!$query_aceitos) {
    $sql_aceitos = "
    SELECT 
        g.titulo, 
        p.codigo, 
        s.titulo AS sub, 
        p.local, 
        p.tempo, 
        p.descricao, 
        p.lat, 
        p.log, 
        p.data_hora, 
        pc.codcadastro, 
        pa.NOME,
        p.foto_1, 
        p.foto_2, 
        p.foto_3, 
        p.foto_4
    FROM 
        grupos g,
        pedido p,
        categoria s,
        pega_contato pc,
        parceiro pa,
        disparo_pedidos dp
    WHERE 
        pc.codcliente = '$codcliente'
        AND p.categoria = g.codigo
        AND p.subcategoria = s.codigo
        AND pc.codpedido = p.codigo
        AND pc.codcadastro = pa.id
        AND p.codigo = dp.codpedido
        AND dp.aceito = 's'
    GROUP BY p.codigo
    ORDER BY p.codigo DESC
    ";
    $query_aceitos = mysqli_query($con, $sql_aceitos);
}
while ($query_aceitos && $row = mysqli_fetch_assoc($query_aceitos)) {
    $mapa_url_do_pedido = '';
    if (!empty($_SESSION['mapas_salvos'])) {
        foreach ($_SESSION['mapas_salvos'] as $codPedido => $mapaUrl) {
            if ((int)$codPedido === (int)$row['codigo']) {
                $mapa_url_do_pedido = $mapaUrl;
                break;
            }
        }
    }
    
    $fotos_ac = [];
    for ($fi = 1; $fi <= 4; $fi++) {
        if (!empty($row['foto_'.$fi])) {
            $fp = $row['foto_'.$fi];
            if (strpos($fp, 'fotos/') === false && strpos($fp, 'http') === false) $fp = 'fotos/' . $fp;
            $fotos_ac[] = $fp;
        }
    }
    
    $aceitos[] = [
        'tipo' => 'aceito',
        'codigo' => $row['codigo'],
        'descricao' => $row['descricao'],
        'data_hora' => date('d/m/Y H:i:s', strtotime($row['data_hora'])),
        'cat' => $row['titulo'],
        'sub' => $row['sub'],
        'nome_prestador' => $row['NOME'],
        'codcadastro' => $row['codcadastro'],
        'tempo' => $row['tempo'],
        'mapa_url' => $mapa_url_do_pedido,
        'lat' => $row['lat'],
        'log' => $row['log'],
        'fotos' => $fotos_ac,
        'audio' => isset($row['audio']) && !empty($row['audio']) ? 'audios/' . $row['audio'] : null
    ];
}

// 3. Pedidos Sem Resposta (expirados - mais de 48h sem aceite)
$sem_resposta = [];
$where_conditions_sr = [];
if ($codcliente > 0) {
    $where_conditions_sr[] = "p.codcli = '$codcliente'";
}
if (!empty($codigos_pendentes_sessao)) {
    $codigos_string = implode(',', $codigos_pendentes_sessao);
    $where_conditions_sr[] = "p.codigo IN ($codigos_string)";
}

if (!empty($where_conditions_sr)) {
    $combined_where_sr = implode(' OR ', $where_conditions_sr);
    $sql_sem_resposta = "
        SELECT 
            g.titulo as cat, 
            p.codigo, 
            s.titulo AS sub, 
            p.local, 
            p.tempo, 
            p.descricao, 
            p.lat, 
            p.log, 
            p.data_hora, 
            p.valor, 
            p.foto_1, 
            p.foto_2, 
            p.foto_3, 
            p.foto_4, 
            p.audio,
            s.codigo AS codsub,
            p.status
        FROM 
            grupos g,
            pedido p,
            categoria s
        WHERE 
            p.subcategoria = s.codigo
            AND g.codigo = s.codgrupo
            AND g.codigo = p.categoria
            AND p.data_hora < DATE_SUB(NOW(), INTERVAL 48 HOUR)
            AND NOT EXISTS (
                SELECT 1 FROM disparo_pedidos dp 
                WHERE dp.codpedido = p.codigo 
                AND dp.aceito = 's'
            )
            AND ($combined_where_sr)
        GROUP BY p.codigo
        ORDER BY p.codigo DESC
    ";
    
    $query_sem_resposta = mysqli_query($con, $sql_sem_resposta);
    if (!$query_sem_resposta) {
        $sql_sem_resposta = str_replace("p.audio,", "", $sql_sem_resposta);
        $query_sem_resposta = mysqli_query($con, $sql_sem_resposta);
    }
    while ($query_sem_resposta && $row = mysqli_fetch_assoc($query_sem_resposta)) {
        $mapa_url_do_pedido = '';
        if (isset($_SESSION['mapas_salvos'])) {
            foreach ($_SESSION['mapas_salvos'] as $codPedido => $mapaUrl) {
                if ((int)$codPedido === (int)$row['codigo']) {
                    $mapa_url_do_pedido = $mapaUrl;
                    break;
                }
            }
        }

        $fotos = [];
        for ($fi = 1; $fi <= 4; $fi++) {
            if (!empty($row['foto_'.$fi])) {
                $fp = $row['foto_'.$fi];
                if (strpos($fp, 'fotos/') === false && strpos($fp, 'http') === false) $fp = 'fotos/' . $fp;
                $fotos[] = $fp;
            }
        }

        $sem_resposta[] = [
            'tipo' => 'sem_resposta',
            'codigo' => $row['codigo'],
            'descricao' => $row['descricao'],
            'data_hora' => date('d/m/Y H:i:s', strtotime($row['data_hora'])),
            'cat' => $row['cat'],
            'sub' => $row['sub'],
            'status' => 'Serviço Finalizado',
            'status_class' => 'status-expirado',
            'status_text' => 'Serviço Finalizado',
            'dynamic_content' => '<span style="color:#dc3545;">Nenhum prestador respondeu</span>',
            'mapa_url' => $mapa_url_do_pedido,
            'lat' => $row['lat'],
            'log' => $row['log'],
            'codsub' => $row['codsub'],
            'tempo' => $row['tempo'],
            'fotos' => $fotos,
            'audio' => isset($row['audio']) && !empty($row['audio']) ? 'audios/' . $row['audio'] : null
        ];
    }
}

// 4. Pedidos Finalizados (avaliados pelo cliente - aceito='f')
$finalizados = [];
if ($codcliente > 0) {
    // Considera pedidos do cliente por codcli OU por pega_contato (alinha com a aba aceitos)
    $sql_fin = "
    SELECT p.codigo, p.descricao, p.data_hora, g.titulo, s.titulo as sub,
           dp.codcadastro, avl.qtd_estrela as nota, avl.mensagem, avl.denuncia,
           den.tipo as denuncia_tipo, den.motivo as denuncia_motivo
    FROM pedido p
    INNER JOIN grupos g ON g.codigo = p.categoria
    INNER JOIN categoria s ON s.codigo = p.subcategoria
    INNER JOIN disparo_pedidos dp ON dp.codpedido = p.codigo AND dp.aceito = 'f'
    LEFT JOIN avaliacoes avl ON avl.codcadastro = dp.codcadastro AND avl.codpedido = p.codigo
    LEFT JOIN denuncias den ON den.codcadastro = dp.codcadastro AND den.codpedido = p.codigo
    WHERE p.codcli = '$codcliente'
       OR p.codigo IN (SELECT codpedido FROM pega_contato WHERE codcliente = '$codcliente')
    GROUP BY p.codigo
    ORDER BY p.codigo DESC";
    $q_fin = mysqli_query($con, $sql_fin);
    // Fallback: se a query falhou (coluna denuncia/tabela denuncias pode não existir)
    if (!$q_fin) {
        $sql_fin = "
        SELECT p.codigo, p.descricao, p.data_hora, g.titulo, s.titulo as sub,
               dp.codcadastro, avl.qtd_estrela as nota, avl.mensagem
        FROM pedido p
        INNER JOIN grupos g ON g.codigo = p.categoria
        INNER JOIN categoria s ON s.codigo = p.subcategoria
        INNER JOIN disparo_pedidos dp ON dp.codpedido = p.codigo AND dp.aceito = 'f'
        LEFT JOIN avaliacoes avl ON avl.codcadastro = dp.codcadastro AND avl.codpedido = p.codigo
        WHERE p.codcli = '$codcliente'
           OR p.codigo IN (SELECT codpedido FROM pega_contato WHERE codcliente = '$codcliente')
        GROUP BY p.codigo
        ORDER BY p.codigo DESC";
        $q_fin = mysqli_query($con, $sql_fin);
    }
    while ($q_fin && $row = mysqli_fetch_assoc($q_fin)) {
        $ehDenuncia = isset($row['denuncia']) && $row['denuncia'] == 1;
        $finalizados[] = [
            'codigo'   => $row['codigo'],
            'data_hora'=> date('d/m/Y H:i:s', strtotime($row['data_hora'])),
            'cat'      => $row['titulo'],
            'sub'      => $row['sub'],
            'nota'     => (int)($row['nota'] ?? 5),
            'mensagem' => $ehDenuncia ? '' : ($row['mensagem'] ?? ''),
            'denuncia' => $ehDenuncia,
            'denuncia_tipo' => $ehDenuncia ? ($row['denuncia_tipo'] ?? 'Denúncia') : '',
            'denuncia_motivo' => $ehDenuncia ? ($row['denuncia_motivo'] ?? '') : ''
        ];
    }
}

// DEBUG temporario - remover depois
$debug = [
    'codcliente' => $codcliente,
    'cookie_id_cliente' => isset($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : 'NAO_SET',
    'cookie_codcliente' => isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : 'NAO_SET',
    'cookie_cpf_cnpj' => isset($_COOKIE['cpf_cnpj_unificado']) ? $_COOKIE['cpf_cnpj_unificado'] : 'NAO_SET',
    'where_conditions' => $where_conditions,
    'sql_pendentes' => isset($sql_pendentes) ? $sql_pendentes : 'NAO_EXECUTOU',
    'sql_error' => isset($query_pendentes) && !$query_pendentes ? mysqli_error($con) : null,
    'sessao_mapas' => !empty($_SESSION['mapas_salvos']) ? array_keys($_SESSION['mapas_salvos']) : 'VAZIO',
];

// Testa query direta para ver se existem pedidos do cliente
if ($codcliente > 0) {
    $qTest = mysqli_query($con, "SELECT codigo, codcli, status, data_hora FROM pedido WHERE codcli='$codcliente' ORDER BY codigo DESC LIMIT 5");
    $debug['pedidos_direto'] = [];
    while ($qTest && $rt = mysqli_fetch_assoc($qTest)) {
        $debug['pedidos_direto'][] = $rt;
    }
}

echo json_encode([
    'success' => true,
    'pendentes' => $pendentes,
    'aceitos' => $aceitos,
    'sem_resposta' => $sem_resposta,
    'finalizados' => $finalizados,
    'pendentes_count' => count($pendentes),
    'aceitos_count' => count($aceitos),
    'sem_resposta_count' => count($sem_resposta),
    'debug' => $debug
]);
?>