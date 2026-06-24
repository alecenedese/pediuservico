<?php
require_once("send.php");
header("Content-Type: text/html; charset=utf-8", true);

// Garante que a tabela exista (mesma estrutura criada em verificacao.php do app)
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

// Ação de aprovar/rejeitar direto da listagem
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $idVerif = (int)$_GET['id'];
    if ($_GET['acao'] === 'aprovar') {
        mysqli_query($con, "UPDATE verificacoes_usuario SET status='aprovado' WHERE id='$idVerif'");
        echo "<script>alert('Verificação aprovada!'); window.location.href='".$urlserver."verificacoes';</script>";
        exit;
    } elseif ($_GET['acao'] === 'rejeitar') {
        mysqli_query($con, "UPDATE verificacoes_usuario SET status='rejeitado' WHERE id='$idVerif'");
        echo "<script>alert('Verificação rejeitada.'); window.location.href='".$urlserver."verificacoes';</script>";
        exit;
    }
}

// Filtro por status
$filtroStatus = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$where = "WHERE 1=1";
if (in_array($filtroStatus, ['pendente', 'em_analise', 'aprovado', 'rejeitado'])) {
    $where .= " AND v.status = '".mysqli_real_escape_string($con, $filtroStatus)."'";
}

// Busca verificações com o nome do usuário (prestador ou cliente)
$query = "SELECT v.* FROM verificacoes_usuario v $where ORDER BY 
            CASE v.status WHEN 'em_analise' THEN 0 WHEN 'pendente' THEN 1 WHEN 'rejeitado' THEN 2 ELSE 3 END,
            v.data_envio DESC";
$lista = mysqli_query($con, $query);

// Função para resolver o nome do usuário
function nomeUsuarioVerif($con, $idUsuario, $tipoUsuario) {
    $idEsc = mysqli_real_escape_string($con, $idUsuario);
    if ($tipoUsuario === 'prestador') {
        $q = mysqli_query($con, "SELECT NOME FROM parceiro WHERE id='$idEsc' LIMIT 1");
    } else {
        $q = mysqli_query($con, "SELECT NOME FROM clientes WHERE id='$idEsc' LIMIT 1");
    }
    if ($q && $r = mysqli_fetch_array($q)) {
        return $r['NOME'];
    }
    return 'Usuário #'.$idUsuario;
}

// Contadores por status
$contadores = ['em_analise'=>0, 'pendente'=>0, 'doc_incompleto'=>0, 'aprovado'=>0, 'rejeitado'=>0];
$qCont = mysqli_query($con, "SELECT status, COUNT(*) as total FROM verificacoes_usuario GROUP BY status");
while ($qCont && $rc = mysqli_fetch_array($qCont)) {
    if (isset($contadores[$rc['status']])) $contadores[$rc['status']] = (int)$rc['total'];
}

function badgeStatus($status) {
    switch ($status) {
        case 'aprovado':       return '<span class="badge bg-success">Perfil verificado</span>';
        case 'rejeitado':      return '<span class="badge bg-danger">Documentação recusada</span>';
        case 'em_analise':     return '<span class="badge bg-info">Pendente (em análise)</span>';
        case 'doc_incompleto': return '<span class="badge bg-secondary">Documentação pendente</span>';
        default:               return '<span class="badge bg-warning">Pendente</span>';
    }
}
?>

<!-- Layout container -->
<div class="layout-page">
    <?php require_once("nav-topo.php"); ?>

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Verificações</h4>

            <!-- Cards de resumo -->
            <div class="row mb-4">
                <div class="col-6 col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-info"><?php echo $contadores['em_analise']; ?></h3>
                            <small class="text-muted">Em análise</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-warning"><?php echo $contadores['pendente'] + $contadores['doc_incompleto']; ?></h3>
                            <small class="text-muted">Doc. pendente</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-success"><?php echo $contadores['aprovado']; ?></h3>
                            <small class="text-muted">Aprovados</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-danger"><?php echo $contadores['rejeitado']; ?></h3>
                            <small class="text-muted">Rejeitados</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Solicitações de Verificação</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo $urlserver; ?>verificacoes?filtro=todos" class="btn btn-outline-secondary <?php echo $filtroStatus=='todos'?'active':''; ?>">Todos</a>
                        <a href="<?php echo $urlserver; ?>verificacoes?filtro=em_analise" class="btn btn-outline-info <?php echo $filtroStatus=='em_analise'?'active':''; ?>">Em análise</a>
                        <a href="<?php echo $urlserver; ?>verificacoes?filtro=aprovado" class="btn btn-outline-success <?php echo $filtroStatus=='aprovado'?'active':''; ?>">Aprovados</a>
                        <a href="<?php echo $urlserver; ?>verificacoes?filtro=rejeitado" class="btn btn-outline-danger <?php echo $filtroStatus=='rejeitado'?'active':''; ?>">Rejeitados</a>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>Documentos</th>
                                <th>Status</th>
                                <th>Enviado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                        <?php
                        $temRegistros = false;
                        while ($lista && $row = mysqli_fetch_assoc($lista)) {
                            $temRegistros = true;
                            $nome = nomeUsuarioVerif($con, $row['id_usuario'], $row['tipo_usuario']);
                            $qtdDocs = 0;
                            foreach (['foto_pessoal','foto_documento','foto_comprovante','foto_antecedentes'] as $c) {
                                if (!empty($row[$c])) $qtdDocs++;
                            }
                            $dataFmt = !empty($row['data_envio']) ? date('d/m/Y H:i', strtotime($row['data_envio'])) : '-';
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($nome); ?></strong><br><small class="text-muted">ID: <?php echo htmlspecialchars($row['id_usuario']); ?></small></td>
                                <td><span class="badge bg-label-primary"><?php echo ucfirst($row['tipo_usuario']); ?></span></td>
                                <td><i class="bx bx-file"></i> <?php echo $qtdDocs; ?>/4</td>
                                <td><?php echo badgeStatus($row['status']); ?></td>
                                <td><?php echo $dataFmt; ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?php echo $urlserver; ?>ver-verificacao?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Ver detalhes">
                                            <i class="bx bx-show"></i> Ver
                                        </a>
                                        <a href="<?php echo $urlserver; ?>verificacoes?acao=aprovar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" title="Aprovar" onclick="return confirm('Aprovar esta verificação?')">
                                            <i class="bx bx-check"></i>
                                        </a>
                                        <a href="<?php echo $urlserver; ?>verificacoes?acao=rejeitar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Rejeitar" onclick="return confirm('Rejeitar esta verificação?')">
                                            <i class="bx bx-x"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (!$temRegistros) { ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma verificação encontrada.</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
