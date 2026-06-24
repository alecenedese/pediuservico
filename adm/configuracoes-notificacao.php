<?php
require_once("send.php");
header("Content-Type: text/html; charset=utf-8", true);

// Tabela simples de configurações (chave/valor)
mysqli_query($con, "CREATE TABLE IF NOT EXISTS config_app (
    chave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) DEFAULT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Pasta de sons fica na raiz do app (acessível publicamente): /pediuservico/sons/
$dirSons = __DIR__ . '/../sons';
if (!is_dir($dirSons)) { @mkdir($dirSons, 0755, true); }

$mensagem = '';
$tipoMsg = '';

// Upload do som
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['som_arquivo'])) {
    if ($_FILES['som_arquivo']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['mp3','ogg','wav','m4a'];
        $ext = strtolower(pathinfo($_FILES['som_arquivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidos)) {
            $mensagem = 'Formato inválido. Use MP3, OGG, WAV ou M4A.';
            $tipoMsg = 'danger';
        } elseif ($_FILES['som_arquivo']['size'] > 5 * 1024 * 1024) {
            $mensagem = 'Arquivo muito grande (máx. 5MB).';
            $tipoMsg = 'danger';
        } else {
            $nome = 'notificacao_' . date('YmdHis') . '.' . $ext;
            if (move_uploaded_file($_FILES['som_arquivo']['tmp_name'], $dirSons . '/' . $nome)) {
                // Remove o som anterior
                $qOld = mysqli_query($con, "SELECT valor FROM config_app WHERE chave='som_notificacao' LIMIT 1");
                if ($qOld && $rOld = mysqli_fetch_assoc($qOld)) {
                    $antigo = $dirSons . '/' . basename($rOld['valor']);
                    if (!empty($rOld['valor']) && is_file($antigo)) { @unlink($antigo); }
                }
                mysqli_query($con, "INSERT INTO config_app (chave, valor) VALUES ('som_notificacao', '".mysqli_real_escape_string($con, $nome)."')
                                    ON DUPLICATE KEY UPDATE valor='".mysqli_real_escape_string($con, $nome)."'");
                $mensagem = 'Som de notificação atualizado com sucesso!';
                $tipoMsg = 'success';
            } else {
                $mensagem = 'Não foi possível salvar o arquivo.';
                $tipoMsg = 'danger';
            }
        }
    } else {
        $mensagem = 'Nenhum arquivo enviado.';
        $tipoMsg = 'warning';
    }
}

// Remover som
if (isset($_GET['acao']) && $_GET['acao'] === 'remover') {
    $qOld = mysqli_query($con, "SELECT valor FROM config_app WHERE chave='som_notificacao' LIMIT 1");
    if ($qOld && $rOld = mysqli_fetch_assoc($qOld)) {
        $antigo = $dirSons . '/' . basename($rOld['valor']);
        if (!empty($rOld['valor']) && is_file($antigo)) { @unlink($antigo); }
    }
    mysqli_query($con, "DELETE FROM config_app WHERE chave='som_notificacao'");
    echo "<script>window.location.href='".$urlserver."configuracoes-notificacao';</script>";
    exit;
}

// Som atual
$somAtual = '';
$qAtual = mysqli_query($con, "SELECT valor FROM config_app WHERE chave='som_notificacao' LIMIT 1");
if ($qAtual && $rAtual = mysqli_fetch_assoc($qAtual)) {
    $somAtual = $rAtual['valor'];
}
$urlSom = $somAtual ? 'https://gessomt.app.br/pediuservico/sons/' . rawurlencode($somAtual) : '';
?>

<div class="layout-page">
    <?php require_once("nav-topo.php"); ?>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Configurações /</span> Som de Notificação</h4>

            <?php if ($mensagem) { ?>
                <div class="alert alert-<?php echo $tipoMsg; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php } ?>

            <div class="row">
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Enviar novo som</h5></div>
                        <div class="card-body">
                            <p class="text-muted">Este som será tocado dentro do app quando chegar uma nova notificação (útil para quem usa no PC com a tela sempre aberta).</p>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Arquivo de som (MP3, OGG, WAV ou M4A — máx. 5MB)</label>
                                    <input type="file" name="som_arquivo" class="form-control" accept=".mp3,.ogg,.wav,.m4a,audio/*" required>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="bx bx-upload"></i> Enviar som</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Som atual</h5></div>
                        <div class="card-body text-center">
                            <?php if ($somAtual) { ?>
                                <p class="mb-2"><i class="bx bx-volume-full" style="font-size:32px;color:#0ea5e9;"></i></p>
                                <audio controls style="width:100%;">
                                    <source src="<?php echo $urlSom; ?>">
                                    Seu navegador não suporta áudio.
                                </audio>
                                <p class="text-muted small mt-2"><?php echo htmlspecialchars($somAtual); ?></p>
                                <a href="<?php echo $urlserver; ?>configuracoes-notificacao?acao=remover" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm('Remover o som atual?')"><i class="bx bx-trash"></i> Remover</a>
                            <?php } else { ?>
                                <p class="text-muted py-4">Nenhum som configurado. O app usará um bipe padrão.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
