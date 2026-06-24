<?php
require_once("send.php");
header("Content-Type: text/html; charset=utf-8", true);

// Garante colunas de status individual e selos
$colunas = [
    'status_pessoal'     => "VARCHAR(20) DEFAULT 'pendente'",
    'status_documento'   => "VARCHAR(20) DEFAULT 'pendente'",
    'status_comprovante' => "VARCHAR(20) DEFAULT 'pendente'",
    'status_antecedentes'=> "VARCHAR(20) DEFAULT 'pendente'",
    'parceiro_fundador'  => "TINYINT(1) DEFAULT 0",
    'selo_verificado'    => "TINYINT(1) DEFAULT 0",
    'selo_seguro'        => "TINYINT(1) DEFAULT 0",
];
foreach ($colunas as $col => $def) {
    $chk = mysqli_query($con, "SHOW COLUMNS FROM verificacoes_usuario LIKE '$col'");
    if ($chk && mysqli_num_rows($chk) == 0) {
        mysqli_query($con, "ALTER TABLE verificacoes_usuario ADD COLUMN $col $def");
    }
}

$idVerif = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Função que recalcula os selos e o status geral (Item 11)
// Regras:
//  - doc_incompleto  : enviou MENOS que os 3 documentos obrigatórios (pessoal, documento, comprovante)
//  - em_analise      : enviou os 3, mas eu ainda não aprovei nem recusei o suficiente (nenhum recusado e não aprovou todos)
//  - rejeitado       : eu recusei 1 ou mais dos 3 documentos obrigatórios
//  - aprovado        : eu aprovei os 3 documentos obrigatórios  -> Perfil Verificado
//  - selo_seguro     : aprovei os 3 obrigatórios + Antecedentes Criminais -> Parceiro Seguro
function recalcularSelos($con, $idVerif) {
    $q = mysqli_query($con, "SELECT * FROM verificacoes_usuario WHERE id='$idVerif' LIMIT 1");
    if (!$q || !($d = mysqli_fetch_assoc($q))) return;

    // Documentos obrigatórios para o Perfil Verificado
    $obrigatorios = [
        'foto_pessoal'     => 'status_pessoal',
        'foto_documento'   => 'status_documento',
        'foto_comprovante' => 'status_comprovante',
    ];

    $enviados = 0; $aprovados = 0; $rejeitados = 0;
    foreach ($obrigatorios as $arq => $st) {
        if (!empty($d[$arq]))                      $enviados++;
        if (($d[$st] ?? '') === 'aprovado')        $aprovados++;
        if (($d[$st] ?? '') === 'rejeitado')       $rejeitados++;
    }

    $verificado = ($aprovados === 3) ? 1 : 0;
    // Parceiro Seguro exige os 3 obrigatórios aprovados + antecedentes aprovado
    $seguro = ($verificado && (($d['status_antecedentes'] ?? '') === 'aprovado')) ? 1 : 0;

    if ($enviados < 3) {
        $statusGeral = 'doc_incompleto';   // Documentação pendente (faltam documentos)
    } elseif ($rejeitados > 0) {
        $statusGeral = 'rejeitado';        // Documentação recusada
    } elseif ($verificado) {
        $statusGeral = 'aprovado';         // Perfil verificado
    } else {
        $statusGeral = 'em_analise';       // Pendente (aguardando aprovação dos 3)
    }

    mysqli_query($con, "UPDATE verificacoes_usuario SET selo_verificado='$verificado', selo_seguro='$seguro', status='$statusGeral' WHERE id='$idVerif'");
}

// Processa ações: aprovar/rejeitar documento individual
if ($idVerif > 0 && isset($_GET['doc']) && isset($_GET['acao'])) {
    $docs = ['pessoal'=>'status_pessoal','documento'=>'status_documento','comprovante'=>'status_comprovante','antecedentes'=>'status_antecedentes'];
    $doc = $_GET['doc'];
    if (isset($docs[$doc])) {
        $campo = $docs[$doc];
        $novoStatus = ($_GET['acao'] === 'aprovar') ? 'aprovado' : 'rejeitado';
        mysqli_query($con, "UPDATE verificacoes_usuario SET $campo='$novoStatus' WHERE id='$idVerif'");
        recalcularSelos($con, $idVerif);
    }
    echo "<script>window.location.href='".$urlserver."ver-verificacao?id=$idVerif';</script>";
    exit;
}

// Marcar/desmarcar parceiro fundador
if ($idVerif > 0 && isset($_GET['fundador'])) {
    $val = $_GET['fundador'] === '1' ? 1 : 0;
    mysqli_query($con, "UPDATE verificacoes_usuario SET parceiro_fundador='$val' WHERE id='$idVerif'");
    echo "<script>window.location.href='".$urlserver."ver-verificacao?id=$idVerif';</script>";
    exit;
}

// Busca dados
$dados = null;
if ($idVerif > 0) {
    $q = mysqli_query($con, "SELECT * FROM verificacoes_usuario WHERE id='$idVerif' LIMIT 1");
    if ($q) $dados = mysqli_fetch_assoc($q);
}

$nomeUsuario = ''; $contatoUsuario = '';
if ($dados) {
    $idEsc = mysqli_real_escape_string($con, $dados['id_usuario']);
    if ($dados['tipo_usuario'] === 'prestador') {
        $qU = mysqli_query($con, "SELECT NOME, CNPJ_CPF, CELULAR FROM parceiro WHERE id='$idEsc' LIMIT 1");
    } else {
        $qU = mysqli_query($con, "SELECT NOME, CNPJ_CPF, CELULAR FROM clientes WHERE id='$idEsc' LIMIT 1");
    }
    if ($qU && $rU = mysqli_fetch_array($qU)) {
        $nomeUsuario = $rU['NOME'];
        $contatoUsuario = trim(($rU['CNPJ_CPF'] ?? '') . ' • ' . ($rU['CELULAR'] ?? ''), ' •');
    }
}

$baseUploads = 'verificacao-arquivo.php?f=';

function badgeDoc($status) {
    switch ($status) {
        case 'aprovado':  return '<span class="badge bg-success">Aprovado</span>';
        case 'rejeitado': return '<span class="badge bg-danger">Rejeitado</span>';
        default:          return '<span class="badge bg-warning">Pendente</span>';
    }
}

// Renderiza documento com botões individuais (Item 11)
function renderDocAprovacao($baseUploads, $urlserver, $idVerif, $arquivo, $titulo, $icone, $docKey, $statusDoc) {
    echo '<div class="col-md-6 mb-4">';
    echo '<div class="card h-100">';
    echo '<div class="card-header d-flex align-items-center justify-content-between"><span><i class="bx '.$icone.' me-2"></i><strong>'.$titulo.'</strong></span>'.badgeDoc($statusDoc).'</div>';
    echo '<div class="card-body text-center">';
    if (empty($arquivo)) {
        echo '<div class="text-muted py-4"><i class="bx bx-block" style="font-size:32px;"></i><br>Não enviado</div>';
    } else {
        $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
        $url = $baseUploads . urlencode($arquivo);
        if ($ext === 'pdf') {
            echo '<a href="'.$url.'" target="_blank" class="btn btn-outline-primary mb-3"><i class="bx bxs-file-pdf"></i> Abrir PDF</a>';
        } else {
            echo '<a href="'.$url.'" target="_blank"><img src="'.$url.'" alt="'.$titulo.'" style="max-width:100%;max-height:240px;border-radius:8px;object-fit:contain;"></a>';
        }
        echo '<div class="mt-3 d-flex gap-2 justify-content-center">';
        echo '<a href="'.$urlserver.'ver-verificacao?id='.$idVerif.'&doc='.$docKey.'&acao=aprovar" class="btn btn-sm btn-success"><i class="bx bx-check"></i> Aprovar</a>';
        echo '<a href="'.$urlserver.'ver-verificacao?id='.$idVerif.'&doc='.$docKey.'&acao=rejeitar" class="btn btn-sm btn-danger"><i class="bx bx-x"></i> Rejeitar</a>';
        echo '</div>';
    }
    echo '</div></div></div>';
}
?>

<div class="layout-page">
    <?php require_once("nav-topo.php"); ?>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Verificações /</span> Detalhes</h4>
                <a href="<?php echo $urlserver; ?>verificacoes" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> Voltar</a>
            </div>

            <?php if (!$dados) { ?>
                <div class="alert alert-warning">Verificação não encontrada.</div>
            <?php } else { ?>

            <!-- Dados do usuário + selos -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-1"><?php echo htmlspecialchars($nomeUsuario ?: 'Usuário #'.$dados['id_usuario']); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($contatoUsuario); ?></p>

                    <!-- Selos atuais -->
                    <div class="mb-3">
                        <?php if (!empty($dados['selo_verificado'])) { ?>
                            <span class="badge bg-primary me-1">🛡️ Perfil Verificado</span>
                        <?php } ?>
                        <?php if (!empty($dados['selo_seguro'])) { ?>
                            <span class="badge bg-success me-1">✅ Parceiro Seguro</span>
                        <?php } ?>
                        <?php if (!empty($dados['parceiro_fundador'])) { ?>
                            <span class="badge bg-warning text-dark me-1">👑 Parceiro Fundador</span>
                        <?php } ?>
                        <?php if (empty($dados['selo_verificado']) && empty($dados['selo_seguro']) && empty($dados['parceiro_fundador'])) { ?>
                            <span class="text-muted">Nenhum selo concedido ainda</span>
                        <?php } ?>
                    </div>

                    <!-- Parceiro Fundador (Item 12) -->
                    <div class="d-flex align-items-center gap-2">
                        <strong>Parceiro Fundador:</strong>
                        <?php if (empty($dados['parceiro_fundador'])) { ?>
                            <a href="<?php echo $urlserver; ?>ver-verificacao?id=<?php echo $idVerif; ?>&fundador=1" class="btn btn-sm btn-outline-warning">👑 Conceder selo de fundador</a>
                        <?php } else { ?>
                            <a href="<?php echo $urlserver; ?>ver-verificacao?id=<?php echo $idVerif; ?>&fundador=0" class="btn btn-sm btn-outline-secondary">Remover selo de fundador</a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Como funcionam os selos e o status:</strong><br>
                🛡️ <strong>Perfil Verificado</strong>: aprovar os 3 obrigatórios — Foto Pessoal + Documento + Comprovante de Endereço.<br>
                ✅ <strong>Parceiro Seguro</strong>: aprovar os 3 obrigatórios <u>e também</u> os Antecedentes Criminais.<br>
                👑 <strong>Parceiro Fundador</strong>: concedido manualmente.<br>
                <hr class="my-2">
                <small>
                <strong>Documentação pendente</strong>: o prestador enviou menos que os 3 documentos obrigatórios.<br>
                <strong>Pendente (em análise)</strong>: enviou os 3, mas você ainda não aprovou todos (e não recusou nenhum).<br>
                <strong>Documentação recusada</strong>: você recusou 1 ou mais dos 3 obrigatórios.<br>
                <strong>Perfil verificado</strong>: você aprovou os 3 obrigatórios.
                </small>
            </div>

            <!-- Documentos com aprovação individual (Item 11) -->
            <div class="row">
                <?php
                renderDocAprovacao($baseUploads, $urlserver, $idVerif, $dados['foto_pessoal'], 'Foto Pessoal', 'bx-user', 'pessoal', $dados['status_pessoal'] ?? 'pendente');
                renderDocAprovacao($baseUploads, $urlserver, $idVerif, $dados['foto_documento'], 'Documento com Foto', 'bx-id-card', 'documento', $dados['status_documento'] ?? 'pendente');
                renderDocAprovacao($baseUploads, $urlserver, $idVerif, $dados['foto_comprovante'], 'Comprovante de Endereço', 'bx-home', 'comprovante', $dados['status_comprovante'] ?? 'pendente');
                renderDocAprovacao($baseUploads, $urlserver, $idVerif, $dados['foto_antecedentes'], 'Antecedentes Criminais', 'bx-file', 'antecedentes', $dados['status_antecedentes'] ?? 'pendente');
                ?>
            </div>

            <?php } ?>
        </div>
    </div>
</div>
