<?php
header("Content-Type: text/html; charset=utf-8", true);

// id da categoria (vindo da sua rota)
$catId = isset($url[1]) ? (int)$url[1] : 0;
if ($catId <= 0) { die("Categoria inválida."); }

// helpers
function post($k, $default = null) {
  return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $default;
}
function get($k, $default = null) {
  return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $default;
}

// -------------------------
// AÇÕES (POST)
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Salvar categoria
  if (post('acao') === 'categoria_salvar') {
    $grupo = (int)post('grupo', 0);
    $titulo = post('titulo', '');
    $moeda  = post('moeda', '');

    $stmt = mysqli_prepare($con, "UPDATE categoria SET codgrupo=?, titulo=?, moeda=? WHERE codigo=?");
    mysqli_stmt_bind_param($stmt, "issi", $grupo, $titulo, $moeda, $catId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ?ok=cat");
    exit;
  }

  // Criar subcategoria
  if (post('sub_acao') === 'criar') {
    $stitulo = post('sub_titulo', '');
    $ordem   = (int)post('sub_ordem', 0);
    $ativo   = (int)post('sub_ativo', 1);

    if ($stitulo !== '') {
      $stmt = mysqli_prepare($con, "INSERT INTO subcategoria (categoria_id, titulo, ordem, ativo) VALUES (?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "isii", $catId, $stitulo, $ordem, $ativo);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }

    header("Location: ?ok=sub_create");
    exit;
  }

  // Editar subcategoria
  if (post('sub_acao') === 'editar') {
    $subId   = (int)post('sub_id', 0);
    $stitulo = post('sub_titulo', '');
    $ordem   = (int)post('sub_ordem', 0);
    $ativo   = (int)post('sub_ativo', 1);

    if ($subId > 0 && $stitulo !== '') {
      $stmt = mysqli_prepare($con, "UPDATE subcategoria SET titulo=?, ordem=?, ativo=? WHERE codigo=? AND categoria_id=?");
      mysqli_stmt_bind_param($stmt, "siiii", $stitulo, $ordem, $ativo, $subId, $catId);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }

    header("Location: ?ok=sub_edit");
    exit;
  }

  // Deletar subcategoria
  if (post('sub_acao') === 'deletar') {
    $subId = (int)post('sub_id', 0);
    if ($subId > 0) {
      $stmt = mysqli_prepare($con, "DELETE FROM subcategoria WHERE codigo=? AND categoria_id=?");
      mysqli_stmt_bind_param($stmt, "ii", $subId, $catId);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
    header("Location: ?ok=sub_del");
    exit;
  }
}

// -------------------------
// DADOS DA CATEGORIA
// -------------------------
$stmt = mysqli_prepare($con, "SELECT * FROM categoria WHERE codigo=?");
mysqli_stmt_bind_param($stmt, "i", $catId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rowCat = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$rowCat) { die("Categoria não encontrada."); }

// -------------------------
// LISTAGEM DE SUBCATEGORIAS (com busca)
// -------------------------
$q = get('q', '');
$like = "%".$q."%";

if ($q !== '') {
  $stmt = mysqli_prepare($con, "SELECT * FROM subcategoria WHERE categoria_id=? AND titulo LIKE ? ORDER BY ordem ASC, titulo ASC");
  mysqli_stmt_bind_param($stmt, "is", $catId, $like);
} else {
  $stmt = mysqli_prepare($con, "SELECT * FROM subcategoria WHERE categoria_id=? ORDER BY ordem ASC, titulo ASC");
  mysqli_stmt_bind_param($stmt, "i", $catId);
}
mysqli_stmt_execute($stmt);
$subRes = mysqli_stmt_get_result($stmt);
$subs = [];
while ($r = mysqli_fetch_assoc($subRes)) $subs[] = $r;
mysqli_stmt_close($stmt);

$ok = get('ok', '');
?>

<div class="layout-page">
  <?php require_once("nav-topo.php"); ?>

  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="fw-bold mb-0">
          <span class="text-muted fw-light">Categoria /</span> Editar
        </h4>

        <?php if ($ok): ?>
          <span class="badge bg-success">Salvo ✔</span>
        <?php endif; ?>
      </div>

      <div class="row g-4">
        <!-- COL ESQUERDA: CATEGORIA -->
        <div class="col-12 col-lg-5">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Dados da categoria</h5>
              <small class="text-muted">Edite e salve. As subcategorias ficam ao lado.</small>
            </div>

            <div class="card-body">
              <form action="" method="post" class="row g-3">
                <input type="hidden" name="acao" value="categoria_salvar">

                <div class="col-12">
                  <label class="form-label">Grupo</label>
                  <select class="form-select" name="grupo" required>
                    <?php
                      $lista = mysqli_query($con, "SELECT * FROM grupos ORDER BY titulo ASC");
                      while($row = mysqli_fetch_array($lista)) {
                    ?>
                      <option value="<?php echo (int)$row['codigo']; ?>"
                        <?php echo ((int)$row['codigo'] === (int)$rowCat['codgrupo']) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($row['titulo']); ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Título</label>
                  <input type="text" class="form-control" name="titulo"
                         value="<?php echo htmlspecialchars($rowCat['titulo']); ?>" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Moeda</label>
                  <input type="text" class="form-control" name="moeda"
                         value="<?php echo htmlspecialchars($rowCat['moeda']); ?>">
                </div>

                <div class="col-12">
                  <button type="submit" class="btn btn-primary w-100">
                    Salvar categoria
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- COL DIREITA: SUBCATEGORIAS -->
        <div class="col-12 col-lg-7">
          <div class="card">
            <div class="card-header d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between">
              <div>
                <h5 class="mb-0">Subcategorias</h5>
                <small class="text-muted">Buscar, criar rápido, editar e deletar.</small>
              </div>

              <form class="d-flex gap-2" method="get" action="">
                <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($q); ?>"
                       placeholder="Pesquisar subcategoria...">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
              </form>
            </div>

            <div class="card-body">
              <!-- FORM NOVA SUBCATEGORIA (inline e rápido) -->
              <div class="border rounded p-3 mb-4">
                <form method="post" class="row g-2 align-items-end">
                  <input type="hidden" name="sub_acao" value="criar">

                  <div class="col-12 col-md-6">
                    <label class="form-label">Nova subcategoria</label>
                    <input class="form-control" name="sub_titulo" placeholder="Ex: Alimentação" required>
                  </div>

                  <div class="col-6 col-md-2">
                    <label class="form-label">Ordem</label>
                    <input class="form-control" type="number" name="sub_ordem" value="0">
                  </div>

                  <div class="col-6 col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="sub_ativo">
                      <option value="1" selected>Ativo</option>
                      <option value="0">Inativo</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-2 d-grid">
                    <button class="btn btn-success" type="submit">Adicionar</button>
                  </div>
                </form>
              </div>

              <!-- TABELA -->
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th style="width:80px;">Ordem</th>
                      <th>Subcategoria</th>
                      <th style="width:100px;">Status</th>
                      <th class="text-end" style="width:160px;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($subs) === 0): ?>
                      <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma subcategoria encontrada.</td></tr>
                    <?php else: foreach ($subs as $s): ?>
                      <tr>
                        <td><?php echo (int)$s['ordem']; ?></td>
                        <td>
                          <div class="fw-semibold"><?php echo htmlspecialchars($s['titulo']); ?></div>
                          <small class="text-muted">#<?php echo (int)$s['codigo']; ?></small>
                        </td>
                        <td>
                          <?php if ((int)$s['ativo'] === 1): ?>
                            <span class="badge bg-success">Ativo</span>
                          <?php else: ?>
                            <span class="badge bg-secondary">Inativo</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-end">
                          <!-- Editar (abre modal) -->
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarSub"
                            data-id="<?php echo (int)$s['codigo']; ?>"
                            data-titulo="<?php echo htmlspecialchars($s['titulo'], ENT_QUOTES); ?>"
                            data-ordem="<?php echo (int)$s['ordem']; ?>"
                            data-ativo="<?php echo (int)$s['ativo']; ?>"
                          >
                            Editar
                          </button>

                          <!-- Deletar -->
                          <form method="post" class="d-inline"
                                onsubmit="return confirm('Tem certeza que deseja deletar esta subcategoria?');">
                            <input type="hidden" name="sub_acao" value="deletar">
                            <input type="hidden" name="sub_id" value="<?php echo (int)$s['codigo']; ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Deletar</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>

            </div>
          </div>
        </div>
      </div><!-- /row -->
    </div>
  </div>
</div>

<!-- MODAL EDITAR SUBCATEGORIA -->
<div class="modal fade" id="modalEditarSub" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Editar subcategoria</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="sub_acao" value="editar">
          <input type="hidden" name="sub_id" id="edit_sub_id">

          <div class="mb-3">
            <label class="form-label">Título</label>
            <input class="form-control" name="sub_titulo" id="edit_sub_titulo" required>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Ordem</label>
              <input class="form-control" type="number" name="sub_ordem" id="edit_sub_ordem" value="0">
            </div>
            <div class="col-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="sub_ativo" id="edit_sub_ativo">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const modal = document.getElementById('modalEditarSub');
  if (modal) {
    modal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;

      document.getElementById('edit_sub_id').value     = btn.getAttribute('data-id');
      document.getElementById('edit_sub_titulo').value = btn.getAttribute('data-titulo');
      document.getElementById('edit_sub_ordem').value  = btn.getAttribute('data-ordem');
      document.getElementById('edit_sub_ativo').value  = btn.getAttribute('data-ativo');
    });
  }
</script>
