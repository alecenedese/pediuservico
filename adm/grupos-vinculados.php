<?php
/**
 * ADMIN: Gerenciar grupos vinculados (mostrar grupo X dentro de grupo Y)
 * Exemplo: Mostrar "Limpeza" dentro de "Manutenção Residencial"
 */
header("Content-Type: text/html; charset=utf-8", true);

$base_url = 'https://gessomt.app.br/pediuservico/adm/';

// Garante que a tabela existe
mysqli_query($con, "CREATE TABLE IF NOT EXISTS grupos_vinculados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codgrupo_origem INT NOT NULL COMMENT 'Grupo que será exibido dentro de outro',
    codgrupo_destino INT NOT NULL COMMENT 'Grupo onde o outro será mostrado',
    UNIQUE KEY uk_vinculo (codgrupo_origem, codgrupo_destino),
    INDEX(codgrupo_destino)
)");

// Processar ação de adicionar vínculo
if (isset($_POST['acao']) && $_POST['acao'] === 'vincular') {
    $origem = (int)($_POST['grupo_origem'] ?? 0);
    $destino = (int)($_POST['grupo_destino'] ?? 0);
    if ($origem > 0 && $destino > 0 && $origem !== $destino) {
        // Verifica se já existe
        $qExist = mysqli_query($con, "SELECT id FROM grupos_vinculados WHERE codgrupo_origem='$origem' AND codgrupo_destino='$destino'");
        if ($qExist && mysqli_num_rows($qExist) === 0) {
            mysqli_query($con, "INSERT INTO grupos_vinculados (codgrupo_origem, codgrupo_destino) VALUES ('$origem', '$destino')");
            echo "<script>alert('Vínculo criado com sucesso!');</script>";
        } else {
            echo "<script>alert('Este vínculo já existe.');</script>";
        }
    } else {
        echo "<script>alert('Selecione dois grupos diferentes.');</script>";
    }
}

// Processar exclusão
if (isset($_GET['remover']) && is_numeric($_GET['remover'])) {
    $idRem = (int)$_GET['remover'];
    mysqli_query($con, "DELETE FROM grupos_vinculados WHERE id='$idRem'");
    echo "<script>alert('Vínculo removido.'); window.location.href='grupos-vinculados';</script>";
    exit;
}

// Buscar todos os grupos
$todosGrupos = [];
$qGrupos = mysqli_query($con, "SELECT codigo, titulo FROM grupos ORDER BY titulo ASC");
while ($qGrupos && $rg = mysqli_fetch_assoc($qGrupos)) {
    $todosGrupos[] = $rg;
}

// Buscar vínculos existentes
$vinculos = [];
$qVinc = mysqli_query($con, "
    SELECT gv.id, g1.titulo AS origem_titulo, g2.titulo AS destino_titulo, gv.codgrupo_origem, gv.codgrupo_destino
    FROM grupos_vinculados gv
    INNER JOIN grupos g1 ON g1.codigo = gv.codgrupo_origem
    INNER JOIN grupos g2 ON g2.codigo = gv.codgrupo_destino
    ORDER BY g2.titulo ASC, g1.titulo ASC
");
while ($qVinc && $rv = mysqli_fetch_assoc($qVinc)) {
    $vinculos[] = $rv;
}
?>

<!-- Layout container -->
<div class="layout-page">
    <?php require_once("nav-topo.php"); ?>

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Configurações /</span> Grupos Vinculados</h4>

            <!-- Formulário para criar vínculo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Mostrar um grupo dentro de outro</h5>
                    <small class="text-muted">Exemplo: Mostrar "Limpeza" quando o cliente acessar "Manutenção Residencial"</small>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3 align-items-end">
                        <input type="hidden" name="acao" value="vincular">
                        <div class="col-md-5">
                            <label class="form-label">Mostrar grupo:</label>
                            <select name="grupo_origem" class="form-select" required>
                                <option value="">Selecione o grupo a ser exibido</option>
                                <?php foreach ($todosGrupos as $g) { ?>
                                    <option value="<?php echo $g['codigo']; ?>"><?php echo htmlspecialchars($g['titulo']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Dentro de:</label>
                            <select name="grupo_destino" class="form-select" required>
                                <option value="">Selecione o grupo destino</option>
                                <?php foreach ($todosGrupos as $g) { ?>
                                    <option value="<?php echo $g['codigo']; ?>"><?php echo htmlspecialchars($g['titulo']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bx bx-link"></i> Vincular</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de vínculos existentes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Vínculos ativos</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Grupo exibido</th>
                                <th></th>
                                <th>Dentro de</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($vinculos) === 0) { ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhum vínculo criado ainda.</td></tr>
                            <?php } else { foreach ($vinculos as $v) { ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($v['origem_titulo']); ?></strong></td>
                                    <td class="text-center"><i class="bx bx-right-arrow-alt text-primary" style="font-size:20px;"></i></td>
                                    <td><strong><?php echo htmlspecialchars($v['destino_titulo']); ?></strong></td>
                                    <td>
                                        <a href="grupos-vinculados?remover=<?php echo $v['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover este vínculo?')">
                                            <i class="bx bx-trash"></i> Remover
                                        </a>
                                    </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
