<?php
session_start();
require_once("send.php");

// Identifica o usuário logado (cliente ou prestador)
$idUsuario = 0;
$tipoUsuario = 'cliente';
if (isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente'])) {
    $idUsuario = mysqli_real_escape_string($con, $_COOKIE['id_cliente']);
    $tipoUsuario = 'cliente';
} elseif (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $q = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
    if ($q && $r = mysqli_fetch_array($q)) { $idUsuario = $r['id']; $tipoUsuario = 'prestador'; }
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $idUsuario = mysqli_real_escape_string($con, $_COOKIE['id_prestador']);
    $tipoUsuario = 'prestador';
} elseif (isset($_COOKIE['codcliente']) && !empty($_COOKIE['codcliente'])) {
    $idUsuario = mysqli_real_escape_string($con, $_COOKIE['codcliente']);
    $tipoUsuario = 'cliente';
}

if (!$idUsuario) {
    echo "<script>window.location.href='login-unificado.php?retorno=verificacao.php';</script>";
    exit;
}

// Garante que a tabela de verificações exista
mysqli_query($con, "CREATE TABLE IF NOT EXISTS verificacoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario VARCHAR(50) NOT NULL,
    tipo_usuario VARCHAR(20) DEFAULT 'cliente',
    foto_pessoal VARCHAR(255) DEFAULT NULL,
    foto_documento VARCHAR(255) DEFAULT NULL,
    foto_comprovante VARCHAR(255) DEFAULT NULL,
    foto_antecedentes VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pendente',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(id_usuario)
)");

$mensagem = '';
$tipoMsg = '';

// Mapa de campos válidos para deleção/preview
$camposVerif = [
    'foto_pessoal'     => 'Foto Pessoal',
    'foto_documento'   => 'Foto do Documento',
    'foto_comprovante' => 'Comprovante de Endereço',
    'foto_antecedentes'=> 'Registro de Antecedentes Criminais',
];

// Processa exclusão de um arquivo específico
if (isset($_GET['deletar']) && array_key_exists($_GET['deletar'], $camposVerif)) {
    $campoDel = $_GET['deletar'];
    $qDel = mysqli_query($con, "SELECT * FROM verificacoes_usuario WHERE id_usuario='$idUsuario' AND tipo_usuario='$tipoUsuario' ORDER BY id DESC LIMIT 1");
    if ($qDel && $rowDel = mysqli_fetch_assoc($qDel)) {
        // Remove o arquivo físico, se existir
        if (!empty($rowDel[$campoDel])) {
            $caminhoArq = __DIR__ . '/verificacoes/' . basename($rowDel[$campoDel]);
            if (is_file($caminhoArq)) { @unlink($caminhoArq); }
        }
        // Limpa o campo no banco
        mysqli_query($con, "UPDATE verificacoes_usuario SET $campoDel=NULL WHERE id='".$rowDel['id']."'");
    }
    echo "<script>window.location.href='verificacao.php?msg=removido';</script>";
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'removido') {
    $mensagem = 'Documento removido. Envie um novo arquivo quando quiser.';
    $tipoMsg = 'sucesso';
}

// Processa o upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dir = __DIR__ . '/verificacoes';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }

    // Busca registro existente
    $qExist = mysqli_query($con, "SELECT * FROM verificacoes_usuario WHERE id_usuario='$idUsuario' AND tipo_usuario='$tipoUsuario' ORDER BY id DESC LIMIT 1");
    $existente = ($qExist && mysqli_num_rows($qExist) > 0) ? mysqli_fetch_assoc($qExist) : null;

    function uploadVerif($campo, $dir, $idUsuario, $tipo) {
        if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $permitidos = ['jpg','jpeg','png','pdf','webp'];
        $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidos)) { return null; }
        // Limite de 8MB
        if ($_FILES[$campo]['size'] > 8 * 1024 * 1024) { return null; }
        $nome = 'verif_'.$idUsuario.'_'.$tipo.'_'.date('YmdHis').'.'.$ext;
        if (move_uploaded_file($_FILES[$campo]['tmp_name'], $dir.'/'.$nome)) {
            return $nome;
        }
        return null;
    }

    $fotoPessoal     = uploadVerif('foto_pessoal', $dir, $idUsuario, 'pessoal');
    $fotoDocumento   = uploadVerif('foto_documento', $dir, $idUsuario, 'documento');
    $fotoComprovante = uploadVerif('foto_comprovante', $dir, $idUsuario, 'comprovante');
    $fotoAntecedentes= uploadVerif('foto_antecedentes', $dir, $idUsuario, 'antecedentes');

    // Se um novo arquivo foi enviado para um campo, remove o arquivo antigo (evita órfãos)
    if ($existente) {
        $novos = [
            'foto_pessoal' => $fotoPessoal,
            'foto_documento' => $fotoDocumento,
            'foto_comprovante' => $fotoComprovante,
            'foto_antecedentes' => $fotoAntecedentes,
        ];
        foreach ($novos as $campoAntigo => $novoArquivo) {
            if ($novoArquivo && !empty($existente[$campoAntigo]) && $novoArquivo !== $existente[$campoAntigo]) {
                $antigo = $dir . '/' . basename($existente[$campoAntigo]);
                if (is_file($antigo)) { @unlink($antigo); }
            }
        }
    }

    // Mantém os arquivos antigos se nenhum novo foi enviado para aquele campo
    if (!$fotoPessoal && $existente)      $fotoPessoal      = $existente['foto_pessoal'];
    if (!$fotoDocumento && $existente)    $fotoDocumento    = $existente['foto_documento'];
    if (!$fotoComprovante && $existente)  $fotoComprovante  = $existente['foto_comprovante'];
    if (!$fotoAntecedentes && $existente) $fotoAntecedentes = $existente['foto_antecedentes'];

    $fp = $fotoPessoal !== null ? "'".mysqli_real_escape_string($con, $fotoPessoal)."'" : "NULL";
    $fd = $fotoDocumento !== null ? "'".mysqli_real_escape_string($con, $fotoDocumento)."'" : "NULL";
    $fc = $fotoComprovante !== null ? "'".mysqli_real_escape_string($con, $fotoComprovante)."'" : "NULL";
    $fa = $fotoAntecedentes !== null ? "'".mysqli_real_escape_string($con, $fotoAntecedentes)."'" : "NULL";

    // Item 11: status conforme quantidade de documentos obrigatórios enviados
    $obrigEnviados = 0;
    foreach ([$fotoPessoal, $fotoDocumento, $fotoComprovante] as $docObrig) {
        if (!empty($docObrig)) $obrigEnviados++;
    }
    $statusUpload = ($obrigEnviados < 3) ? 'doc_incompleto' : 'em_analise';

    if ($existente) {
        mysqli_query($con, "UPDATE verificacoes_usuario SET foto_pessoal=$fp, foto_documento=$fd, foto_comprovante=$fc, foto_antecedentes=$fa, status='$statusUpload', data_envio=NOW() WHERE id='".$existente['id']."'");
    } else {
        mysqli_query($con, "INSERT INTO verificacoes_usuario (id_usuario, tipo_usuario, foto_pessoal, foto_documento, foto_comprovante, foto_antecedentes, status) VALUES ('$idUsuario', '$tipoUsuario', $fp, $fd, $fc, $fa, '$statusUpload')");
    }

    $mensagem = 'Documentos enviados com sucesso! Sua verificação está em análise.';
    $tipoMsg = 'sucesso';
}

// Busca dados atuais para exibir
$dadosVerif = null;
$qV = mysqli_query($con, "SELECT * FROM verificacoes_usuario WHERE id_usuario='$idUsuario' AND tipo_usuario='$tipoUsuario' ORDER BY id DESC LIMIT 1");
if ($qV && mysqli_num_rows($qV) > 0) {
    $dadosVerif = mysqli_fetch_assoc($qV);
}
$statusAtual = $dadosVerif ? $dadosVerif['status'] : 'pendente';

/**
 * Renderiza um campo de upload com prévia do arquivo já enviado
 * e botões para editar (trocar) ou deletar.
 */
function renderCampoVerif($campo, $dadosVerif, $icone, $idNome) {
    $arquivo = ($dadosVerif && !empty($dadosVerif[$campo])) ? $dadosVerif[$campo] : '';

    if ($arquivo) {
        $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
        $url = 'verificacao-arquivo.php?f=' . urlencode($arquivo);
        echo '<div class="arquivo-preview" id="preview-'.$campo.'">';
        if ($ext === 'pdf') {
            echo '<a href="'.$url.'" target="_blank" class="preview-pdf">';
            echo '<span class="preview-pdf-icon">📄</span>';
            echo '<span class="preview-pdf-text">Ver PDF enviado</span>';
            echo '</a>';
        } else {
            echo '<a href="'.$url.'" target="_blank" class="preview-img-link">';
            echo '<img src="'.$url.'" alt="Arquivo enviado" class="preview-img">';
            echo '</a>';
        }
        echo '<div class="preview-acoes">';
        // Editar: abre o seletor de arquivo (input fica oculto até clicar)
        echo '<button type="button" class="btn-acao btn-editar" onclick="trocarArquivo(\''.$campo.'\')">✏️ Editar</button>';
        // Deletar: remove via GET
        echo '<a href="verificacao.php?deletar='.$campo.'" class="btn-acao btn-deletar" onclick="return confirm(\'Deseja realmente remover este arquivo?\')">🗑️ Deletar</a>';
        echo '</div>';
        echo '</div>';

        // Input oculto, ativado pelo botão Editar
        echo '<div class="file-input-wrapper" id="wrapper-'.$campo.'" style="display:none;margin-top:10px;">';
        echo '<input type="file" name="'.$campo.'" accept="image/*,application/pdf" onchange="mostrarNome(this, \''.$idNome.'\')">';
        echo '<div class="file-input-icon">'.$icone.'</div>';
        echo '<div class="file-input-text">Toque para escolher um novo arquivo</div>';
        echo '<div class="file-selected-name" id="'.$idNome.'"></div>';
        echo '</div>';
    } else {
        // Sem arquivo: mostra o seletor normal
        echo '<div class="file-input-wrapper">';
        echo '<input type="file" name="'.$campo.'" accept="image/*,application/pdf" onchange="mostrarNome(this, \''.$idNome.'\')">';
        echo '<div class="file-input-icon">'.$icone.'</div>';
        echo '<div class="file-input-text">Toque para escolher o arquivo</div>';
        echo '<div class="file-selected-name" id="'.$idNome.'"></div>';
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 70px;
            color: #fff;
        }
        .main-content {
            flex: 1;
            padding: 16px;
            padding-bottom: 90px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }
        .page-title {
            text-align: center;
            color: #00d4ff;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .page-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            margin-bottom: 20px;
        }
        .status-banner {
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .status-pendente { background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.4); color: #fbbf24; }
        .status-doc_incompleto { background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.4); color: #fbbf24; }
        .status-em_analise { background: rgba(0,212,255,0.15); border: 1px solid rgba(0,212,255,0.4); color: #00d4ff; }
        .status-aprovado { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); color: #22c55e; }
        .status-rejeitado { background: rgba(220,53,69,0.15); border: 1px solid rgba(220,53,69,0.4); color: #dc3545; }
        .msg-sucesso {
            background: rgba(34,197,94,0.15);
            border: 1px solid rgba(34,197,94,0.4);
            color: #22c55e;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .upload-card {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .upload-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #1a2332;
            margin-bottom: 4px;
        }
        .upload-desc {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .file-input-wrapper {
            position: relative;
            border: 2px dashed #00d4ff;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            background: rgba(0,212,255,0.05);
            transition: all 0.2s;
            cursor: pointer;
        }
        .file-input-wrapper:hover { background: rgba(0,212,255,0.1); }
        .file-input-wrapper input[type=file] {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .file-input-icon { font-size: 28px; margin-bottom: 4px; }
        .file-input-text { font-size: 13px; color: #0ea5e9; font-weight: 600; }
        .file-selected-name { font-size: 12px; color: #22c55e; margin-top: 6px; font-weight: 600; word-break: break-all; }
        .ja-enviado {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #22c55e;
            font-weight: 600;
            margin-top: 6px;
        }
        /* Prévia do arquivo enviado */
        .arquivo-preview {
            border: 2px solid #22c55e;
            border-radius: 10px;
            padding: 12px;
            background: rgba(34,197,94,0.05);
            text-align: center;
        }
        .preview-img-link { display: block; }
        .preview-img {
            max-width: 100%;
            max-height: 220px;
            border-radius: 8px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        .preview-pdf {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 20px;
            text-decoration: none;
            color: #1a2332;
        }
        .preview-pdf-icon { font-size: 42px; }
        .preview-pdf-text { font-size: 14px; font-weight: 700; color: #0ea5e9; }
        .preview-acoes {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            justify-content: center;
        }
        .btn-acao {
            flex: 1;
            max-width: 140px;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        .btn-editar { background: #0ea5e9; color: #fff; }
        .btn-editar:active { transform: scale(0.97); }
        .btn-deletar { background: #fee2e2; color: #dc3545; border: 1px solid #fecaca; }
        .btn-deletar:active { transform: scale(0.97); }
        .btn-enviar {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #0ea5e9);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(0,212,255,0.3);
        }
        .btn-enviar:active { transform: scale(0.99); }
        /* Item 10: barra fixa no rodapé com o botão de envio */
        .enviar-bar {
            position: fixed;
            left: 0; right: 0; bottom: 60px;
            padding: 10px 16px;
            background: linear-gradient(180deg, rgba(26,35,50,0) 0%, #1a2332 30%);
            z-index: 50;
        }
        .enviar-bar .btn-enviar { max-width: 600px; margin: 0 auto; display: block; }
        .info-box {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 12px;
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            line-height: 1.5;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<?php include('header-app.php'); ?>

<div class="main-content">
    <div class="page-title">✅ Verificação de Conta</div>
    <div class="page-subtitle">Envie seus documentos para verificar sua conta e aumentar sua confiabilidade</div>

    <?php if ($mensagem) { ?>
        <div class="msg-<?php echo $tipoMsg; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php } ?>

    <div class="status-banner status-<?php echo $statusAtual; ?>">
        <?php
        $statusTexto = [
            'pendente' => '⏳ Verificação pendente — envie seus documentos',
            'doc_incompleto' => '⏳ Documentação pendente — envie os 3 documentos obrigatórios',
            'em_analise' => '🔍 Em análise — nossa equipe está avaliando',
            'aprovado' => '✅ Perfil verificado',
            'rejeitado' => '❌ Documentação recusada — reenvie os documentos',
        ];
        echo $statusTexto[$statusAtual] ?? 'Status desconhecido';
        ?>
    </div>

    <div class="info-box">
        📌 Formatos aceitos: JPG, PNG, WEBP ou PDF (máx. 8MB cada).<br>
        Seus documentos são confidenciais e usados apenas para verificação.
    </div>

    <form method="POST" enctype="multipart/form-data" id="verifForm">

        <!-- Foto Pessoal -->
        <div class="upload-card">
            <div class="upload-label">📸 Foto Pessoal</div>
            <div class="upload-desc">Uma selfie segurando seu documento</div>
            <?php renderCampoVerif('foto_pessoal', $dadosVerif, '📷', 'nome-pessoal'); ?>
        </div>

        <!-- Foto do Documento -->
        <div class="upload-card">
            <div class="upload-label">🪪 Foto do Documento</div>
            <div class="upload-desc">RG, CNH ou outro documento com foto</div>
            <?php renderCampoVerif('foto_documento', $dadosVerif, '🪪', 'nome-documento'); ?>
        </div>

        <!-- Comprovante de Endereço -->
        <div class="upload-card">
            <div class="upload-label">🏠 Comprovante de Endereço</div>
            <div class="upload-desc">Conta de luz, água ou telefone recente</div>
            <?php renderCampoVerif('foto_comprovante', $dadosVerif, '🏠', 'nome-comprovante'); ?>
        </div>

        <!-- Antecedentes Criminais -->
        <div class="upload-card">
            <div class="upload-label">📋 Registro de Antecedentes Criminais</div>
            <div class="upload-desc">
                Certidão de antecedentes criminais — <strong>não é obrigatório</strong>, mas garante mais credibilidade dentro da plataforma.<br>
                <a href="https://www.gov.br/pt-br/servicos/emitir-certidao-de-antecedentes-criminais" target="_blank" style="color:#0ea5e9;text-decoration:underline;">📎 Clique aqui para emitir gratuitamente no site do Governo</a>
            </div>
            <?php renderCampoVerif('foto_antecedentes', $dadosVerif, '📋', 'nome-antecedentes'); ?>
        </div>

    </form>
</div>

<!-- Item 10: botão de envio fixo no rodapé -->
<div class="enviar-bar">
    <button type="submit" form="verifForm" class="btn-enviar">📤 Enviar Documentos</button>
</div>

<?php $navAtiva = ''; if (file_exists(__DIR__ . '/bottom-nav.php')) include('bottom-nav.php'); ?>

<script>
function mostrarNome(input, idDestino) {
    var dest = document.getElementById(idDestino);
    if (input.files && input.files.length > 0) {
        dest.textContent = '✓ ' + input.files[0].name;
    } else {
        dest.textContent = '';
    }
}

// Item: botão "Editar" mostra o seletor de novo arquivo e esconde a prévia
function trocarArquivo(campo) {
    var wrapper = document.getElementById('wrapper-' + campo);
    var preview = document.getElementById('preview-' + campo);
    if (wrapper) wrapper.style.display = 'block';
    if (preview) preview.style.display = 'none';
}
</script>
</body>
</html>
