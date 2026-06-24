<?php
ob_start(); // <-- coloca isso
header("Content-Type: text/html; charset=utf-8", true);

/**
 * Página: Editar Categoria + Subcategorias (CRUD + Ordenação + Busca ao vivo)
 * Requisitos:
 * - $con (mysqli)
 * - $url[1] com o ID da categoria (ou use ?id=)
 * - Bootstrap 5 carregado no template
 */

// ----------------------------------------------------
// Helpers
// ----------------------------------------------------
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function getv($k, $d=null){ return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $d; }
function postv($k, $d=null){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }

function jsonOut($data, $code = 200) {
  // Evita qualquer lixo antes do JSON
  if (ob_get_length()) ob_clean();
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

// ----------------------------------------------------
// Cat ID (rota / querystring)
// ----------------------------------------------------
$catId = 0;
if (isset($url[1]) && is_numeric($url[1])) {
  $catId = (int)$url[1];
} elseif (isset($url) && is_array($url)) {
  // Tolera segmentos extras na rota (ex.: editar-categoria/categorias/grupo/39):
  // usa o primeiro segmento numérico encontrado como ID da categoria.
  foreach ($url as $iSeg => $seg) {
    if ($iSeg === 0) continue; // pula 'editar-categoria'
    if (is_numeric($seg)) { $catId = (int)$seg; break; }
  }
}
if ($catId <= 0 && is_numeric(getv('id'))) $catId = (int)getv('id');

if ($catId <= 0) {
  die("Categoria inválida.");
}

// ----------------------------------------------------
// AJAX Endpoints (SEM HTML)
// ----------------------------------------------------
if (isset($_GET['ajax'])) {
  $ajax = getv('ajax');

  // LISTA + BUSCA
  if ($ajax === 'sub_list') {
    $q = getv('q', '');
    $items = [];

    if ($q !== '') {
      $like = "%".$q."%";
      $stmt = mysqli_prepare($con, "SELECT codigo, titulo, ativo, ordem FROM subcategoria WHERE categoria_id=? AND titulo LIKE ? ORDER BY ordem ASC, titulo ASC");
      if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
      mysqli_stmt_bind_param($stmt, "is", $catId, $like);
    } else {
      $stmt = mysqli_prepare($con, "SELECT codigo, titulo, ativo, ordem FROM subcategoria WHERE categoria_id=? ORDER BY ordem ASC, titulo ASC");
      if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
      mysqli_stmt_bind_param($stmt, "i", $catId);
    }

    if (!mysqli_stmt_execute($stmt)) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $items[] = $r;
    mysqli_stmt_close($stmt);

    jsonOut(['ok'=>true, 'items'=>$items]);
  }

  // GET (clicou em um item)
  if ($ajax === 'sub_get') {
    $id = (int)getv('id', 0);
    if ($id <= 0) jsonOut(['ok'=>false,'msg'=>'ID inválido'], 400);

    $stmt = mysqli_prepare($con, "SELECT codigo, titulo, ativo, ordem FROM subcategoria WHERE codigo=? AND categoria_id=?");
    if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "ii", $id, $catId);

    if (!mysqli_stmt_execute($stmt)) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) jsonOut(['ok'=>false,'msg'=>'Não encontrado'], 404);
    jsonOut(['ok'=>true, 'item'=>$row]);
  }

  // CREATE
  if ($ajax === 'sub_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = postv('titulo', '');
    $ativo  = (int)postv('ativo', 1);

    if ($titulo === '') jsonOut(['ok'=>false,'msg'=>'Título obrigatório'], 400);

    // próxima ordem automática
    $stmt = mysqli_prepare($con, "SELECT COALESCE(MAX(ordem),0)+1 as prox FROM subcategoria WHERE categoria_id=?");
    if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "i", $catId);
    if (!mysqli_stmt_execute($stmt)) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    $res = mysqli_stmt_get_result($stmt);
    $prox = (int)(mysqli_fetch_assoc($res)['prox'] ?? 1);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($con, "INSERT INTO subcategoria (categoria_id, titulo, ativo, ordem) VALUES (?, ?, ?, ?)");
    if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "isii", $catId, $titulo, $ativo, $prox);

    if (!mysqli_stmt_execute($stmt)) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    $newId = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    jsonOut(['ok'=>true, 'id'=>$newId]);
  }

  // SAVE (editar)
  if ($ajax === 'sub_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)postv('id', 0);
    $titulo = postv('titulo', '');
    $ativo  = (int)postv('ativo', 1);

    if ($id <= 0 || $titulo === '') jsonOut(['ok'=>false,'msg'=>'Dados inválidos'], 400);

    $stmt = mysqli_prepare($con, "UPDATE subcategoria SET titulo=?, ativo=? WHERE codigo=? AND categoria_id=?");
    if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "siii", $titulo, $ativo, $id, $catId);

    if (!mysqli_stmt_execute($stmt)) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_close($stmt);

    jsonOut(['ok'=>true]);
  }

  // DELETE
  if ($ajax === 'sub_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)postv('id', 0);
    if ($id <= 0) jsonOut(['ok'=>false,'msg'=>'ID inválido'], 400);

    $stmt = mysqli_prepare($con, "DELETE FROM subcategoria WHERE codigo=? AND categoria_id=?");
    if (!$stmt) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "ii", $id, $catId);

    if (!mysqli_stmt_execute($stmt)) jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_close($stmt);

    jsonOut(['ok'=>true]);
  }

  // REORDER (drag & drop)
  if ($ajax === 'sub_reorder' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    $ids = $data['ids'] ?? null;

    if (!is_array($ids) || count($ids) === 0) jsonOut(['ok'=>false,'msg'=>'Lista inválida'], 400);

    mysqli_begin_transaction($con);

    $stmt = mysqli_prepare($con, "UPDATE subcategoria SET ordem=? WHERE codigo=? AND categoria_id=?");
    if (!$stmt) {
      mysqli_rollback($con);
      jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    }

    $ord = 1;
    foreach ($ids as $id) {
      $id = (int)$id;
      mysqli_stmt_bind_param($stmt, "iii", $ord, $id, $catId);
      if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_rollback($con);
        jsonOut(['ok'=>false,'msg'=>mysqli_error($con)], 500);
      }
      $ord++;
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($con);

    jsonOut(['ok'=>true]);
  }

  jsonOut(['ok'=>false,'msg'=>'Ação inválida'], 400);
}

// ----------------------------------------------------
// POST normal: salvar categoria
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postv('acao') === 'categoria_salvar') {
  $grupo = (int)postv('grupo', 0);
  $titulo = postv('titulo', '');
  $moeda  = postv('moeda', '');
  $proximaFase = (int)postv('proxima_fase', 0);

  $stmt = mysqli_prepare($con, "UPDATE categoria SET codgrupo=?, titulo=?, moeda=?, proxima_fase=? WHERE codigo=?");
  if (!$stmt) die(mysqli_error($con));
  mysqli_stmt_bind_param($stmt, "issii", $grupo, $titulo, $moeda, $proximaFase, $catId);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  // Volta para a listagem de categorias filtrada pelo grupo (usa URL absoluta do admin)
  $baseAdm = isset($urlserver) ? $urlserver : '/';
  if ($grupo > 0) {
      echo "<script> window.location.href='".$baseAdm."categorias/grupo/$grupo';</script>";
  } else {
      echo "<script> window.location.href='".$baseAdm."categorias';</script>";
  }

  exit;
}

// ----------------------------------------------------
// Carrega categoria
// ----------------------------------------------------
$stmt = mysqli_prepare($con, "SELECT * FROM categoria WHERE codigo=?");
if (!$stmt) die(mysqli_error($con));
mysqli_stmt_bind_param($stmt, "i", $catId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rowCat = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$rowCat) die("Categoria não encontrada.");

$ok = getv('ok', '');
?>

<!-- Layout container -->
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

        <!-- Esquerda: Categoria -->
        <div class="col-12 col-lg-5">
          <!-- Busca Rápida de Grupo -->
          <div class="card mb-3">
            <div class="card-body">
              <label class="form-label fw-bold">Busca Rápida de Grupo/Categoria</label>
              <input type="text" id="quickSearchGroup" class="form-control" placeholder="Digite o nome do grupo ou categoria para navegar..." autocomplete="off">
              <div id="quickSearchResults" class="list-group mt-2" style="display: none; max-height: 300px; overflow-y: auto;"></div>
              <small class="text-muted d-block mt-1">Digite para buscar e clicar para editar outra categoria</small>
            </div>
          </div>
          
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Dados da categoria</h5>
              <small class="text-muted">Edite e salve. Subcategorias ao lado.</small>
            </div>

            <div class="card-body">
              <form method="post" class="row g-3">
                <input type="hidden" name="acao" value="categoria_salvar">

                <div class="col-12">
                  <label class="form-label">Grupo</label>
                  <select class="form-select" name="grupo" required>
                    <?php
                      $grupoAtual = (int)($rowCat['codgrupo'] ?? 0);
                      // Placeholder: aparece selecionado apenas quando a categoria ainda não tem grupo
                      echo '<option value=""'.($grupoAtual <= 0 ? ' selected' : '').' disabled>— Selecione um grupo —</option>';
                      $lista = mysqli_query($con, "SELECT * FROM grupos ORDER BY titulo ASC");
                      while($g = mysqli_fetch_assoc($lista)) {
                        $sel = ($grupoAtual > 0 && (int)$g['codigo'] === $grupoAtual) ? "selected" : "";
                        echo '<option value="'.(int)$g['codigo'].'" '.$sel.'>'.h($g['titulo']).'</option>';
                      }
                    ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Título</label>
                  <input class="form-control" name="titulo" value="<?=h($rowCat['titulo'])?>" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Moeda</label>
                  <input class="form-control" name="moeda" value="<?=h($rowCat['moeda'])?>">
                </div>

                <div class="col-12">
                  <label class="form-label" for="proxima_fase">Disponível na próxima fase</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="proxima_fase" id="proxima_fase" value="1" <?=((int)($rowCat['proxima_fase'] ?? 0) === 1) ? 'checked' : '';?>>
                    <label class="form-check-label" for="proxima_fase">[x] Disponível para a próxima fase</label>
                  </div>
                </div>

                <div class="col-12 d-grid">
                  <button class="btn btn-primary" type="submit">Salvar categoria</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Direita: Subcategorias -->
        <div class="col-12 col-lg-7">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-1">Subcategorias</h5>
              <small class="text-muted">Arraste para ordenar. Clique para editar.</small>
            </div>

            <div class="card-body">
              <div class="row g-3">

                <!-- Lista -->
                <div class="col-12 col-md-6">
                  <div class="d-flex gap-2 mb-2">
                    <input id="subSearch" class="form-control" placeholder="Buscar em tempo real...">
                    <button id="btnNew" class="btn btn-success" type="button">+</button>
                  </div>

                  <div class="list-group" id="subList" style="min-height: 340px;">
                    <!-- JS -->
                  </div>

                  <small class="text-muted d-block mt-2">
                    Dica: segure e arraste para mudar a ordem.
                  </small>
                </div>

                <!-- Editor -->
                <div class="col-12 col-md-6">
                  <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <strong id="editorTitle">Selecione uma subcategoria</strong>
                      <span id="saveStatus" class="text-muted small"></span>
                    </div>

                    <!-- Editar -->
                    <form id="subForm" class="d-none">
                      <input type="hidden" id="sub_id">

                      <div class="mb-2">
                        <label class="form-label">Título</label>
                        <input class="form-control" id="sub_titulo" required>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="sub_ativo">
                          <option value="1">Ativo</option>
                          <option value="0">Inativo</option>
                        </select>
                      </div>

                      <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Salvar</button>
                        <button type="button" id="btnDelete" class="btn btn-outline-danger">Deletar</button>
                      </div>
                    </form>

                    <!-- Criar -->
                    <form id="newForm" class="d-none">
                      <div class="mb-2">
                        <label class="form-label">Nova subcategoria</label>
                        <input class="form-control" id="new_titulo" placeholder="Ex: Alimentação" required>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="new_ativo">
                          <option value="1" selected>Ativo</option>
                          <option value="0">Inativo</option>
                        </select>
                      </div>

                      <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1">Criar</button>
                        <button type="button" id="btnCancelNew" class="btn btn-outline-secondary">Cancelar</button>
                      </div>
                    </form>

                  </div>
                </div>

              </div><!-- row -->
            </div><!-- body -->
          </div><!-- card -->
        </div>

      </div><!-- row -->
    </div><!-- container -->
  </div><!-- wrapper -->
</div><!-- layout -->

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
/**
 * Script para Subcategorias usando API separada:
 * subcategoria_api.php?cat_id=ID&action=list|get|create|save|delete|reorder
 *
 * Requisitos no HTML:
 * - #subList, #subSearch
 * - #subForm (com #sub_id, #sub_titulo, #sub_ativo, #btnDelete)
 * - #newForm (com #new_titulo, #new_ativo, #btnCancelNew)
 * - #btnNew
 * - #editorTitle, #saveStatus
 * - SortableJS carregado
 */

// --------- CONFIG ---------
const CAT_ID = <?= (int)$catId ?>;
// Determina o caminho base a partir da URL atual para manter /pediuservico/adm2/
const baseUrl = window.location.pathname.split('/editar-categoria')[0]; 
const API_BASE = `${window.location.origin}${baseUrl}/subcategoria_api.php`;

const apiUrl = (action, params = {}) => {
  const u = new URL(API_BASE);
  u.searchParams.set('cat_id', String(CAT_ID));
  u.searchParams.set('action', action);
  Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, String(v)));
  return u.toString();
};

// --------- ELEMENTOS ---------
const subList = document.getElementById('subList');
const subSearch = document.getElementById('subSearch');

const editorTitle = document.getElementById('editorTitle');
const saveStatus = document.getElementById('saveStatus');

const subForm = document.getElementById('subForm');
const sub_id = document.getElementById('sub_id');
const sub_titulo = document.getElementById('sub_titulo');
const sub_ativo = document.getElementById('sub_ativo');
const btnDelete = document.getElementById('btnDelete');

const btnNew = document.getElementById('btnNew');
const newForm = document.getElementById('newForm');
const new_titulo = document.getElementById('new_titulo');
const new_ativo = document.getElementById('new_ativo');
const btnCancelNew = document.getElementById('btnCancelNew');

let activeId = null;

// --------- HELPERS ---------
function debounce(fn, ms=250){
  let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), ms); };
}

function escapeHtml(str){
  return String(str)
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'","&#039;");
}

/**
 * Fetch JSON seguro: se vier HTML, mostra no console.
 */
async function fetchJson(url, options) {
  const r = await fetch(url, options);
  const txt = await r.text();
  try {
    return JSON.parse(txt);
  } catch (e) {
    console.error('Resposta NÃO é JSON. URL:', url);
    console.error('Status:', r.status);
    console.error('Resposta (primeiros 800 chars):', txt.slice(0, 800));
    alert('Servidor retornou HTML (não JSON). Veja o Console para detalhes.');
    throw e;
  }
}

function setEditorMode(mode) {
  // mode: 'none' | 'edit' | 'new'
  saveStatus.textContent = '';
  if (mode === 'none') {
    subForm.classList.add('d-none');
    newForm.classList.add('d-none');
    editorTitle.textContent = 'Selecione uma subcategoria';
    return;
  }
  if (mode === 'edit') {
    newForm.classList.add('d-none');
    subForm.classList.remove('d-none');
    return;
  }
  if (mode === 'new') {
    subForm.classList.add('d-none');
    newForm.classList.remove('d-none');
    editorTitle.textContent = 'Nova subcategoria';
    return;
  }
}

// --------- RENDER LISTA ---------
async function loadList(q='') {
  // preserva o texto no search
  const j = await fetchJson(apiUrl('list', { q }));

  if(!j.ok) {
    subList.innerHTML = `<div class="text-danger p-2">Erro: ${escapeHtml(j.msg || 'Falha ao listar')}</div>`;
    return;
  }

  subList.innerHTML = j.items.map(item => {
    const badge = item.ativo == 1 ? 'bg-success' : 'bg-secondary';
    const active = (String(item.codigo) === String(activeId)) ? 'active' : '';
    return `
      <button type="button"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ${active}"
        data-id="${item.codigo}">
        <span class="text-truncate">${escapeHtml(item.titulo)}</span>
        <span class="badge ${badge}">${item.ativo == 1 ? 'Ativo' : 'Inativo'}</span>
      </button>
    `;
  }).join('');

  // clique => carregar editor
  [...subList.querySelectorAll('[data-id]')].forEach(btn => {
    btn.addEventListener('click', () => selectSub(btn.getAttribute('data-id')));
  });
}

// --------- SELECIONAR ITEM ---------
async function selectSub(id) {
  activeId = id;
  setEditorMode('edit');
  saveStatus.textContent = '';

  const j = await fetchJson(apiUrl('get', { id }));
  if(!j.ok) {
    alert(j.msg || 'Não foi possível carregar');
    return;
  }

  editorTitle.textContent = `Editando #${j.item.codigo}`;
  sub_id.value = j.item.codigo;
  sub_titulo.value = j.item.titulo;
  sub_ativo.value = j.item.ativo;

  // re-render pra marcar active
  await loadList(subSearch.value);
}

// --------- EDITAR / SALVAR ---------
subForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  saveStatus.textContent = 'Salvando...';

  const fd = new FormData();
  fd.append('id', sub_id.value);
  fd.append('titulo', sub_titulo.value);
  fd.append('ativo', sub_ativo.value);

  const j = await fetchJson(apiUrl('save'), { method:'POST', body: fd });

  if(!j.ok) {
    saveStatus.textContent = 'Erro';
    alert(j.msg || 'Erro ao salvar');
    return;
  }

  saveStatus.textContent = 'Salvo ✔';
  await loadList(subSearch.value);
});

// --------- DELETAR ---------
btnDelete.addEventListener('click', async () => {
  if(!sub_id.value) return;
  if(!confirm('Deletar esta subcategoria?')) return;

  const fd = new FormData();
  fd.append('id', sub_id.value);

  const j = await fetchJson(apiUrl('delete'), { method:'POST', body: fd });

  if(!j.ok) {
    alert(j.msg || 'Erro ao deletar');
    return;
  }

  activeId = null;
  setEditorMode('none');
  await loadList(subSearch.value);
});

// --------- NOVO ---------
btnNew.addEventListener('click', () => {
  activeId = null;
  setEditorMode('new');
  new_titulo.value = '';
  new_ativo.value = '1';
  loadList(subSearch.value);
});

btnCancelNew.addEventListener('click', () => {
  setEditorMode('none');
});

newForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  saveStatus.textContent = 'Criando...';

  const fd = new FormData();
  fd.append('titulo', new_titulo.value);
  fd.append('ativo', new_ativo.value);
  fd.append('cat_id', CAT_ID); // Envia no body por garantia

  const j = await fetchJson(apiUrl('create'), { method:'POST', body: fd });

  if(!j.ok) {
    saveStatus.textContent = 'Erro';
    alert(j.msg || 'Erro ao criar');
    return;
  }

  saveStatus.textContent = 'Criado ✔';
  await loadList(subSearch.value);
  await selectSub(j.id); // já abre pra editar
  
  // Após criar subcategoria, foca no campo de busca rápida para facilitar navegação
  setTimeout(() => {
    if (window.focusQuickSearch) {
      window.focusQuickSearch();
    }
  }, 500);
});

// --------- BUSCA AO VIVO ---------
subSearch.addEventListener('input', debounce(() => {
  loadList(subSearch.value);
}, 250));

// --------- ORDENAR (drag & drop) ---------
if (window.Sortable) {
  new Sortable(subList, {
    animation: 150,
    onEnd: debounce(async () => {
      const ids = [...subList.querySelectorAll('[data-id]')].map(el => el.getAttribute('data-id'));

      const j = await fetchJson(apiUrl('reorder'), {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ ids })
      });

      if(!j.ok) {
        alert(j.msg || 'Erro ao reordenar');
        return;
      }

      await loadList(subSearch.value);
    }, 200)
  });
} else {
  console.warn('SortableJS não carregou. Ordenação por arrastar desativada.');
}

// --------- BUSCA RÁPIDA DE GRUPOS/CATEGORIAS ---------
const quickSearchGroup = document.getElementById('quickSearchGroup');
const quickSearchResults = document.getElementById('quickSearchResults');

if (quickSearchGroup && quickSearchResults) {
  quickSearchGroup.addEventListener('input', debounce(async () => {
    const query = quickSearchGroup.value.trim();
    
    if (query.length < 2) {
      quickSearchResults.style.display = 'none';
      quickSearchResults.innerHTML = '';
      return;
    }
    
    try {
      // Busca categorias que correspondem à query
      const response = await fetch(`get_categories.php?q=${encodeURIComponent(query)}`);
      const data = await response.json();
      
      if (data.ok && data.items && data.items.length > 0) {
        quickSearchResults.innerHTML = data.items.map(item => `
          <a href="${baseUrl}/editar-categoria/${item.codigo}" 
             class="list-group-item list-group-item-action">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>${escapeHtml(item.titulo)}</strong>
                <br>
                <small class="text-muted">${escapeHtml(item.grupo_titulo || 'Sem grupo')}</small>
              </div>
              <span class="badge bg-primary">Editar</span>
            </div>
          </a>
        `).join('');
        quickSearchResults.style.display = 'block';
      } else {
        quickSearchResults.innerHTML = '<div class="list-group-item text-muted">Nenhuma categoria encontrada</div>';
        quickSearchResults.style.display = 'block';
      }
    } catch (error) {
      console.error('Erro na busca rápida:', error);
      quickSearchResults.innerHTML = '<div class="list-group-item text-danger">Erro ao buscar</div>';
      quickSearchResults.style.display = 'block';
    }
  }, 300));
  
  // Fecha resultados ao clicar fora
  document.addEventListener('click', (e) => {
    if (!quickSearchGroup.contains(e.target) && !quickSearchResults.contains(e.target)) {
      quickSearchResults.style.display = 'none';
    }
  });
}

// --------- INIT ---------
setEditorMode('none');
loadList('');

// Foca no campo de busca rápida após criar subcategoria
window.focusQuickSearch = () => {
  if (quickSearchGroup) {
    quickSearchGroup.focus();
    quickSearchGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
};
</script>

