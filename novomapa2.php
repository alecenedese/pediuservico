<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// É FUNDAMENTAL iniciar a sessão no topo do seu script.
session_start();

require_once("send.php");
include_once 'conexao.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$urlCompleta = $protocol . $host . $uri;

// Botão "Voltar" vai para solicitar-servico.php (evita recriar pedido em mapa.php)
$subParaVoltar = isset($_GET['subcategoria']) ? $_GET['subcategoria'] : '';
$codPedidoVoltar = isset($_GET['codpedido']) ? intval($_GET['codpedido']) : 0;
$grupoParaVoltar = '';
if ($subParaVoltar !== '') {
    $qGrupoVoltar = mysqli_query($con, "SELECT codgrupo FROM categoria WHERE codigo='".mysqli_real_escape_string($con, $subParaVoltar)."' LIMIT 1");
    if ($qGrupoVoltar && $rGrupoVoltar = mysqli_fetch_array($qGrupoVoltar)) {
        $grupoParaVoltar = $rGrupoVoltar['codgrupo'];
    }
}
if ($subParaVoltar !== '' && $grupoParaVoltar !== '') {
    $voltarUrl = 'solicitar-servico.php?categoria='.urlencode($grupoParaVoltar).'&subcategoria='.urlencode($subParaVoltar).'&sub_ok=1';
    if ($codPedidoVoltar > 0) {
        $voltarUrl .= '&codpedido='.$codPedidoVoltar;
    }
} else {
    $voltarUrl = 'buscar.php';
}

// Garante que o array de mapas na sessão exista.
if (!isset($_SESSION['mapas_salvos']) || !is_array($_SESSION['mapas_salvos'])) {
    $_SESSION['mapas_salvos'] = [];
}

// Guarda apenas uma URL por pedido (identificada pelo codpedido)
$codPedidoSessao = isset($_GET['codpedido']) ? intval($_GET['codpedido']) : 0;
if ($codPedidoSessao > 0) {
    if (!isset($_SESSION['mapas_salvos'][$codPedidoSessao]) || $_SESSION['mapas_salvos'][$codPedidoSessao] !== $urlCompleta) {
        $_SESSION['mapas_salvos'][$codPedidoSessao] = $urlCompleta;
    }
}

// Verifica se este pedido já notificou os prestadores (evita duplicar push/markers ao reentrar/voltar)
$jaGerado = false;
$codPedidoCheck = isset($_GET['codpedido']) ? intval($_GET['codpedido']) : 0;
if ($codPedidoCheck > 0) {
    $qJaGerado = mysqli_query($con, "SELECT 1 FROM disparo_pedidos WHERE codpedido='".$codPedidoCheck."' LIMIT 1");
    if ($qJaGerado && mysqli_num_rows($qJaGerado) > 0) {
        $jaGerado = true;
    }
}

if(isset($_GET['ver']) || $jaGerado){
    
} else {

$subcategoria = isset($_GET['subcategoria']) ? $_GET['subcategoria'] : '';
$cordenadas = mysqli_query($con, "update pedido set lat='".(isset($_GET['latitude']) ? $_GET['latitude'] : '')."', log='".(isset($_GET['longitude']) ? $_GET['longitude'] : '')."' where codigo = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."'") or die(mysqli_error($con));

$listaGSub = mysqli_query($con, "select * from categoria_prestador where codsubcategoria='".$subcategoria."'");
$prestadoresNotificados = []; // Array para guardar IDs dos prestadores notificados

while ($rowsub = mysqli_fetch_array($listaGSub)) {
    $queryPedi44 = mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito) VALUES
    ('".$rowsub['codcadastro']."', '', ".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '').", 'n')") or die(mysqli_error($con));

    $queryEdits = mysqli_query($con, "SELECT * FROM parceiro WHERE id='" . $rowsub['codcadastro'] . "'");
    $rowEdits = mysqli_fetch_array($queryEdits);

    $listaGCadend = mysqli_query($con, "SELECT * FROM endereco_prestador WHERE cod_cadastro='" . $rowsub['codcadastro'] . "'") or die(mysqli_error($con));

    // Pega endereço do prestador ou usa localização do cliente como fallback
    $latPrestador = isset($_GET['latitude']) ? $_GET['latitude'] : '';
    $lonPrestador = isset($_GET['longitude']) ? $_GET['longitude'] : '';

    if ($rowcadend = mysqli_fetch_array($listaGCadend)) {
        $latPrestador = $rowcadend['lat'];
        $lonPrestador = $rowcadend['log'];
    }

    // Insere TODOS os prestadores no markers (com ou sem endereço)
    $queryPedisss = mysqli_query($con, "INSERT INTO markers (nome, codcadastro, valor_min, valor_max, lat, lon, type, codpedido, qtdestrelas) VALUES ('" . $rowEdits['NOME'] . "', '" . $rowEdits['id'] . "', '', '', '" . $latPrestador . "', '" . $lonPrestador . "', '1', '" . (isset($_GET['codpedido']) ? $_GET['codpedido'] : '') . "', '1')") or die(mysqli_error($con));
    
    // Guardar ID do prestador para enviar push Expo depois
    $prestadoresNotificados[] = $rowsub['codcadastro'];
}

}

// Enviar notificações push nativas via Expo para os prestadores notificados
if (!empty($prestadoresNotificados) && isset($_GET['codpedido'])) {
    $codpedido = intval($_GET['codpedido']);
    
    // Incluir arquivo de envio Expo
    require_once __DIR__ . '/api/push-expo-send.php';
    
    // Buscar detalhes do pedido para a notificação
    $queryPedido = mysqli_query($con, "SELECT * FROM pedido WHERE codigo = $codpedido LIMIT 1");
    $pedidoInfo = mysqli_fetch_assoc($queryPedido);
    
    $subcategoriaNome = '';
    if (isset($_GET['subcategoria'])) {
        $qSub = mysqli_query($con, "SELECT nome FROM categoria WHERE codigo = '".mysqli_real_escape_string($con, $_GET['subcategoria'])."' LIMIT 1");
        if ($qSub && $rSub = mysqli_fetch_assoc($qSub)) {
            $subcategoriaNome = $rSub['nome'];
        }
    }
    
    $title = 'Novo Pedido Disponível!';
    $body = $subcategoriaNome ? "Pedido de $subcategoriaNome" : 'Você tem um novo pedido disponível';
    
    $pushData = [
        'codpedido' => $codpedido,
        'type' => 'novo_pedido',
        'subcategoria' => $subcategoriaNome,
        'url' => 'novomapa2.php?codpedido=' . $codpedido
    ];
    
    // Enviar para cada prestador
    foreach ($prestadoresNotificados as $codcadastro) {
        enviarExpoPushParaUsuario($con, $codcadastro, 'prestador', $title, $body, $pushData);
    }
}

$contacordenadas = mysqli_num_rows(mysqli_query($con, "select * from markers where type = '3' and codpedido = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."'"));

if($contacordenadas > 0) {

} else {
  $queryPedi = mysqli_query($con, "INSERT INTO markers (nome, valor_min, valor_max, lat, lon, type, codpedido) VALUES
  ('Minha Localização', '', '', ".(isset($_GET['latitude']) ? $_GET['latitude'] : '').", ".(isset($_GET['longitude']) ? $_GET['longitude'] : '').", '3', '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."')") or die(mysqli_error($con));
}

$pedidos = mysqli_query($con, "SELECT p.tempo FROM pedido p, disparo_pedidos d WHERE d.codpedido = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."'	and p.codigo = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."' AND d.aceito = 'n'") or die(mysqli_error($con));
$rowTemp = mysqli_fetch_array( $pedidos );

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestadores Disponíveis - USERVICE</title>
    <?php include 'pwa-include.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8c 50%, #1e3a5f 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            padding-bottom: 65px;
        }

        .content-area {
            flex: 1;
            padding: 10px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .map-section {
            height: 160px;
            width: 100%;
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            margin-bottom: 10px;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .providers-section {
            flex: 1;
            overflow-y: auto;
        }

        .providers-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding: 8px 10px;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .providers-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #00d4ff;
            font-weight: 500;
        }

        .loading-spinner {
            width: 12px;
            height: 12px;
            border: 2px solid #00d4ff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .providers-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .provider-card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 10px;
            padding: 8px 10px;
            transition: all 0.2s ease;
        }
        .provider-card:active { transform: scale(0.99); }
        .provider-card.offline { opacity: 0.5; }
        .provider-card.available {
            background: #ffffff !important;
            border: 2px solid #00bcd4 !important;
            box-shadow: 0 4px 16px rgba(0,188,212,0.4) !important;
        }
        .provider-card.available .provider-name { color: #1e3a5f !important; }
        .provider-card.available .provider-name2 { color: #555 !important; }
        .provider-card.available .provider-rating { color: #f59e0b !important; }
        .provider-card.available .provider-details { color: #444 !important; }

        .provider-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .provider-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            position: relative;
            flex-shrink: 0;
        }

        .online-indicator {
            position: absolute;
            bottom: -1px; right: -1px;
            width: 10px; height: 10px;
            border-radius: 50%;
            border: 2px solid rgba(30,58,95,0.8);
        }
        .online-indicator.available { background: #22c55e; }
        .online-indicator.offline { background: #9ca3af; }

        .provider-info { flex: 1; }

        .provider-name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1px;
        }
        .selo-badge { cursor: pointer; font-size: 14px; margin-left: 2px; }

        .provider-name2 {
            font-size: 11px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 1px;
        }

        .provider-rating {
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 11px;
            color: #f59e0b;
        }

        .provider-details {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 6px;
        }

        .provider-buttons { display: flex; gap: 6px; }

        .provider-button {
            flex: 1;
            padding: 6px 8px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .provider-button.primary {
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            color: #fff;
        }
        .provider-button.primary:active { transform: scale(0.97); }
        .provider-button.secondary { background: #f59e0b; color: #fff; }
        .provider-button.secondary:active { transform: scale(0.97); }
        .provider-button.disabled {
            background: rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.4);
            cursor: not-allowed;
        }

        .card-footer {
            margin-top: 10px;
            position: sticky;
            bottom: 70px;
            z-index: 100;
            background: linear-gradient(180deg, transparent 0%, #1e3a5f 20%);
            padding-top: 10px;
        }

        .cancel-btn {
            width: 100%;
            background: #dc3545;
            border: none;
            color: white;
            font-size: 14px;
            font-weight: 600;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .cancel-btn:active { transform: scale(0.98); }

        .modal {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0; visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal.active { opacity: 1; visibility: visible; }

        .modal-content {
            background: #1e3a5f;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 16px;
            padding: 20px;
            max-width: 90%;
            width: 400px;
            max-height: 80%;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .modal-title { font-size: 16px; font-weight: 700; color: #fff; }

        .modal-close {
            background: none; border: none; color: rgba(255,255,255,0.6);
            font-size: 20px; cursor: pointer; padding: 4px;
        }

        .modal-body { color: rgba(255,255,255,0.85); font-size: 14px; line-height: 1.5; }

        .slide-in-top {
            animation: slideInTop 0.4s ease forwards;
        }

        @keyframes slideInTop {
            from { transform: translateY(-15px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
<?php include 'header-app.php'; ?>

    <div class="content-area">
        <!-- Informações do Pedido no Topo -->
        <?php
        $codpedido = isset($_GET['codpedido']) ? $_GET['codpedido'] : '';
        if ($codpedido) {
            $infoPedido = mysqli_fetch_array(mysqli_query($con, "SELECT p.data_hora, p.tempo, c.titulo as categoria FROM pedido p LEFT JOIN categoria c ON c.codigo = p.subcategoria WHERE p.codigo = '$codpedido'"));
            if ($infoPedido) {
                $dataFormatada = date('d/m/Y H:i', strtotime($infoPedido['data_hora']));
                $categoria = $infoPedido['categoria'] ?? 'Serviço';
                echo '<div style="background:rgba(0,212,255,0.15);border:2px solid rgba(0,212,255,0.4);border-radius:12px;padding:12px;margin-bottom:12px;">';
                echo '<div style="text-align:center;color:#fff;font-weight:700;font-size:13px;margin-bottom:6px;">📋 INFORMAÇÕES DO PEDIDO</div>';
                echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">';
                echo '<div><span style="color:rgba(255,255,255,0.7);">Data:</span> <strong style="color:#00d4ff;">'.$dataFormatada.'</strong></div>';
                echo '<div><span style="color:rgba(255,255,255,0.7);">Pedido:</span> <strong style="color:#00d4ff;">#'.$codpedido.'</strong></div>';
                echo '<div style="grid-column:1/-1;"><span style="color:rgba(255,255,255,0.7);">Categoria:</span> <strong style="color:#00d4ff;">'.$categoria.'</strong></div>';
                echo '</div>';
                echo '</div>';
            }
        }
        ?>

        <div class="map-section">
            <div id="map"></div>
        </div>

        <div class="providers-section">
            <div class="providers-header">
                <div class="providers-title">Prestadores Disponíveis</div>
                <div class="status-indicator">
                    <div class="loading-spinner" id="loading-spinner"></div>
                    <span id="status-text">Buscando...</span>
                </div>
            </div>

            <!-- Item 10: Contador de prestadores não interessados -->
            <div id="uninterested-counter" style="background:rgba(220,53,69,0.15);border:1px solid rgba(220,53,69,0.3);border-radius:8px;padding:8px 12px;margin-bottom:10px;text-align:center;font-size:12px;color:#dc3545;font-weight:600;display:none;">
                <span id="uninterested-count">0</span> prestador(es) não interessado(s) neste pedido
            </div>

            <!-- Mensagem de Pedido Registrado -->
            <div style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.4);border-radius:10px;padding:10px;margin-bottom:10px;text-align:center;color:#22c55e;font-size:13px;font-weight:600;">
                ✅ PEDIDO REGISTRADO NA ÁREA DO CONSUMIDOR EM PEDIDOS PENDENTES
            </div>

            <div class="providers-list" id="providers-list">
            </div>
        </div>

        <div class="card-footer">
            <button class="cancel-btn" onclick="cancelarSolicitacao()">❌ Cancelar Solicitação</button>
        </div>
    </div>

    <div id="contraproposta-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modal-title">Contraproposta</div>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body" id="modal-content">
                <!-- Conteúdo será inserido aqui -->
            </div>
        </div>
    </div>

    <script>
        function cancelarSolicitacao() {
            if (!confirm('Tem certeza que deseja cancelar esta solicitação?')) return;
            fetch('cancelar-pedido.php?codpedido=<?php echo isset($_GET["codpedido"]) ? $_GET["codpedido"] : ""; ?>')
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) window.location.href = 'index.php';
                })
                .catch(() => alert('Erro ao cancelar solicitação'));
        }

        // Armazena o estado dos prestadores da última atualização
        let previousProviders = {};
        // Armazena os marcadores do mapa
        let mapMarkers = {};
        // Referência para o mapa
        let map;

        function initMap() {
            map = L.map('map', {
                zoomControl: true,
                dragging: true,
                scrollWheelZoom: true,
                attributionControl: false
            }).setView([-11.8548, -55.5017], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        }

        function createProviderCard(provider, isNewlyAvailable) {
            const isAvailableNow = provider.availability === "Disponível agora";
            const animationClass = isNewlyAvailable ? 'slide-in-top' : '';
            const offlineClass = isAvailableNow ? 'available' : 'offline';
            const indicatorClass = isAvailableNow ? 'available' : 'offline';

            // Gera iniciais do nome para o avatar
            const initials = String(provider.index || '?');

            // Verifica se tem contraproposta
            const hasContraproposta = provider.contraproposta && provider.contraproposta !== '' && provider.contraproposta !== '0';
            const encodedName = encodeURIComponent(provider.name || '');
            const encodedContraproposta = encodeURIComponent(provider.contraproposta || '');

            // Botões baseados na disponibilidade e contraproposta
            let buttonsHtml = '';

            if (hasContraproposta) {
                buttonsHtml = `
                    <button onclick="showContraproposta('${provider.id}', '${encodedName}', '${encodedContraproposta}')" class="provider-button secondary">
                        Ver Observação
                    </button>
                    <button onclick="confirmarAceiteProposta('pegar_contato.php?url=<?php echo $urlCompleta; ?>&codcadastro=${provider.id}&codpedido=<?php echo $_GET['codpedido']; ?>&nome=${provider.name}')" class="provider-button ${isAvailableNow ? 'primary' : 'disabled'}" ${!isAvailableNow ? 'disabled' : ''}>
                        Aceitar Proposta
                    </button>
                `;
            } else {
                buttonsHtml = `
                    <button onclick="confirmarAceiteProposta('pegar_contato.php?url=<?php echo $urlCompleta; ?>&codcadastro=${provider.id}&codpedido=<?php echo $_GET['codpedido']; ?>&nome=${provider.name}')" class="provider-button ${isAvailableNow ? 'primary' : 'disabled'}" ${!isAvailableNow ? 'disabled' : ''}>
                        Aceitar Proposta
                    </button>
                `;
            }

            // Item 12: Selos de verificação
            let selosHtml = '';
            if (provider.selo_verificado == 1) selosHtml += `<span class="selo-badge" onclick="mostrarSelo(event,'verificado')" title="Perfil Verificado">🛡️</span>`;
            if (provider.selo_seguro == 1) selosHtml += `<span class="selo-badge" onclick="mostrarSelo(event,'seguro')" title="Parceiro Seguro">✅</span>`;
            if (provider.selo_fundador == 1) selosHtml += `<span class="selo-badge" onclick="mostrarSelo(event,'fundador')" title="Parceiro Fundador">👑</span>`;

            return `
                <div class="provider-card ${offlineClass} ${animationClass}" data-id="${provider.id}">
                    <div class="provider-header">
                        <div class="provider-avatar">
                            ${initials}
                            <span class="online-indicator ${indicatorClass}"></span>
                        </div>
                        <div class="provider-info">
                            <div class="provider-name">Proposta ${provider.index} ${selosHtml}</div>
                            <div class="provider-name2">${provider.profession}</div>
                            <div class="provider-rating">
                                ★ ${provider.rating}
                            </div>
                        </div>
                    </div>
                    <div class="provider-details">
                        <span>📍 ${provider.distance}</span>
                        <span>⏰ ${provider.availability}</span>
                    </div>
                    <div class="provider-buttons">
                        ${buttonsHtml}
                    </div>
                </div>
            `;
        }

        // Item 12: Balão explicativo dos selos
        function mostrarSelo(e, tipo) {
            e.stopPropagation();
            const textos = {
                'verificado': '🛡️ Perfil Verificado\n\nEste prestador teve sua foto pessoal, documento e comprovante de endereço confirmados pela nossa equipe.',
                'seguro': '✅ Parceiro Seguro\n\nEste prestador apresentou certidão de antecedentes criminais verificada pela plataforma.',
                'fundador': '👑 Parceiro Fundador\n\nUm dos primeiros prestadores da plataforma, reconhecido pela confiança e pioneirismo.'
            };
            alert(textos[tipo] || '');
        }

        // Modal customizado para aceitar proposta (Sim/Não ao invés de OK/Cancelar)
        function confirmarAceiteProposta(url) {
            const modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:99999;';
            modal.innerHTML = `
                <div style="background:#fff;border-radius:12px;padding:24px;max-width:90%;width:400px;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
                    <h3 style="color:#1e3a5f;margin:0 0 16px 0;font-size:18px;">Aceitar Proposta?</h3>
                    <p style="color:#666;margin:0 0 24px 0;line-height:1.5;">Deseja realmente aceitar esta proposta?<br><br>Caso prefira, ainda podem chegar novos orçamentos.</p>
                    <div style="display:flex;gap:12px;">
                        <button onclick="this.closest('[role=dialog]').remove()" style="flex:1;padding:12px;background:#e0e0e0;color:#333;border:none;border-radius:8px;font-weight:600;font-size:15px;cursor:pointer;">Não</button>
                        <button onclick="window.location.href='${url}'" style="flex:1;padding:12px;background:#00bcd4;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:15px;cursor:pointer;">Sim</button>
                    </div>
                </div>
            `;
            modal.setAttribute('role', 'dialog');
            document.body.appendChild(modal);
        }

        // Função para mostrar o modal de contraproposta
        function showContraproposta(id, name, contraproposta) {
            const modal = document.getElementById('contraproposta-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalContent = document.getElementById('modal-content');

            name = decodeURIComponent(name || '');
            contraproposta = decodeURIComponent(contraproposta || '');
            contraproposta = contraproposta.split('\n').map(line => line.trim()).join('\n');
            modalTitle.textContent = `Observação de ${name}`;

            modalContent.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; padding: 32px 0;">
                    <div class="loading-spinner"></div>
                </div>
            `;

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            fetch(`get_contraproposta.php?id=${id}&codpedido=<?php echo $_GET['codpedido']; ?>`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data || !data.success) {
                        modalContent.innerHTML = `
                            <div>
                                <h4 style="color: #fff; margin-bottom: 8px;">Detalhes da Observação:</h4>
                                <p>${contraproposta || 'Sem detalhes adicionais'}</p>
                            </div>
                        `;
                        return;
                    }

                    let content = `
                        <div>
                            <h4 style="color: #fff; margin-bottom: 8px;">Detalhes da Observação:</h4>
                            <p style="margin-bottom: 16px;">${data.contraproposta || contraproposta || 'Sem detalhes adicionais'}</p>
                    `;

                    if (data.valor_min) {
                        content += `
                            <div style="margin-bottom: 8px;">
                                <h4 style="color: #fff; margin-bottom: 4px;">Valor Mínimo:</h4>
                                <p style="color: #22c55e; font-weight: bold;">${data.valor_min}</p>
                            </div>
                        `;
                    }

                    if (data.valor_max) {
                        content += `
                            <div>
                                <h4 style="color: #fff; margin-bottom: 4px;">Valor Máximo:</h4>
                                <p style="color: #22c55e; font-weight: bold;">${data.valor_max}</p>
                            </div>
                        `;
                    }

                    content += `</div>`;
                    modalContent.innerHTML = content;
                })
                .catch(error => {
                    console.error('Erro ao buscar contraproposta:', error);
                    modalContent.innerHTML = `
                        <div>
                            <h4 style="color: #fff; margin-bottom: 8px;">Detalhes da Observação:</h4>
                            <p>${contraproposta || 'Sem detalhes adicionais'}</p>
                        </div>
                    `;
                });
        }

        function closeModal() {
            const modal = document.getElementById('contraproposta-modal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        function fetchProviders() {
            const spinner = document.getElementById('loading-spinner');
            const statusText = document.getElementById('status-text');

            spinner.style.display = 'block';
            statusText.textContent = 'Atualizando...';

            const timestamp = new Date().getTime();

            fetch(`get_providers.php?codpedido=<?php echo $_GET['codpedido']; ?>&t=${timestamp}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na rede ao buscar prestadores');
                    }
                    return response.json();
                })
                .then(providers => {
                    if (!Array.isArray(providers)) {
                        console.error("Dados recebidos não são um array:", providers);
                        return;
                    }

                    providers.forEach(provider => {
                        if (!provider.location || !Array.isArray(provider.location) || provider.location.length !== 2) {
                            if (provider.lat !== undefined && provider.lon !== undefined) {
                                provider.location = [Number.parseFloat(provider.lat), Number.parseFloat(provider.lon)];
                            }
                        }
                    });

                    updateProvidersDisplay(providers);
                    spinner.style.display = 'none';
                    statusText.textContent = `${providers.filter(p => p.availability === "Disponível agora").length} disponível(is)`;
                })
                .catch(error => {
                    console.error('Erro ao buscar prestadores:', error);
                    spinner.style.display = 'none';
                    statusText.textContent = 'Erro ao atualizar';
                });
        }

        function updateProvidersDisplay(providers) {
            const providersList = document.getElementById('providers-list');

            const availableProviders = providers.filter(p => p.availability === "Disponível agora");
            const waitingProviders = providers.filter(p => p.availability !== "Disponível agora");

            const newlyAvailableProviders = availableProviders.filter(p => {
                return previousProviders[p.id] &&
                       previousProviders[p.id].availability !== "Disponível agora";
            });

            const brandNewProviders = availableProviders.filter(p => {
                return !previousProviders[p.id];
            });

            const allNewAvailableProviders = [...newlyAvailableProviders, ...brandNewProviders];

            if (allNewAvailableProviders.length > 0) {
                const statusText = document.getElementById('status-text');
                statusText.textContent = `${allNewAvailableProviders.length} novo(s) prestador(es) disponível(is)!`;
                setTimeout(() => {
                    statusText.textContent = `${availableProviders.length} disponível(is)`;
                }, 5000);
            }

            const currentProviders = {};
            providers.forEach(p => {
                currentProviders[p.id] = p;
            });

            providersList.innerHTML = '';

            let propostaIndex = 1;
            availableProviders.forEach(provider => {
                const isNewlyAvailable = newlyAvailableProviders.some(p => p.id === provider.id) ||
                                         brandNewProviders.some(p => p.id === provider.id);
                provider.index = propostaIndex++;
                providersList.innerHTML += createProviderCard(provider, isNewlyAvailable);
            });

            waitingProviders.forEach(provider => {
                provider.index = propostaIndex++;
                providersList.innerHTML += createProviderCard(provider, false);
            });

            updateMapMarkers(providers);

            previousProviders = currentProviders;
        }

        function updateMapMarkers(providers) {
            Object.keys(mapMarkers).forEach(id => {
                const providerExists = providers.some(p => p.id.toString() === id);
                if (!providerExists) {
                    map.removeLayer(mapMarkers[id]);
                    delete mapMarkers[id];
                }
            });

            const validProviders = [];

            providers.forEach(provider => {
                const id = provider.id.toString();

                if (!provider.location || provider.location.length !== 2 ||
                    isNaN(provider.location[0]) || isNaN(provider.location[1])) {
                    return;
                }

                validProviders.push(provider);

                const isAvailable = provider.availability === "Disponível agora";

                const icon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + (isAvailable ? 'green' : 'grey') + '.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                if (mapMarkers[id]) {
                    mapMarkers[id].setLatLng(provider.location);
                    mapMarkers[id].setIcon(icon);
                } else {
                    mapMarkers[id] = L.marker(provider.location, { icon: icon })
                        .bindPopup(`<b>Proposta ${provider.index}</b><br>${provider.availability}`)
                        .addTo(map);
                }
            });

            if (validProviders.length > 0) {
                const bounds = [];
                validProviders.forEach(provider => {
                    bounds.push(provider.location);
                });

                if (bounds.length > 0) {
                    try {
                        map.fitBounds(bounds, { padding: [20, 20], maxZoom: 15 });
                    } catch (e) {
                        console.error("Erro ao ajustar o mapa:", e);
                        if (bounds.length > 0) {
                            map.setView(bounds[0], 13);
                        }
                    }
                }
            }
        }

        // Item 10: Função para buscar e atualizar contador de não interessados
        function fetchUninterestedCount() {
            const codpedido = '<?php echo isset($_GET["codpedido"]) ? $_GET["codpedido"] : ""; ?>';
            fetch(`get-uninterested-count.php?codpedido=${codpedido}`)
                .then(response => response.json())
                .then(data => {
                    const counterDiv = document.getElementById('uninterested-counter');
                    const countSpan = document.getElementById('uninterested-count');
                    if (data.count > 0) {
                        countSpan.textContent = data.count;
                        counterDiv.style.display = 'block';
                    } else {
                        counterDiv.style.display = 'none';
                    }
                })
                .catch(err => console.error('Erro ao buscar não interessados:', err));
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            fetchProviders();
            fetchUninterestedCount();
            setInterval(fetchProviders, 5000);
            setInterval(fetchUninterestedCount, 5000);

            const contrapropostaModal = document.getElementById('contraproposta-modal');
            if (contrapropostaModal) {
                contrapropostaModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            }
        });
    </script>

<?php $navAtiva = 'pedidos'; include('bottom-nav.php'); ?>
</body>
</html>
