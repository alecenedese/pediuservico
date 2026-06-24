<?php
ob_start(); // Inicia buffer de saída para evitar output antes do JSON header
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_exception_handler(function($e) {
    ob_clean(); // Limpa qualquer output anterior
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage(),'line'=>$e->getLine()]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean(); // Limpa qualquer output anterior
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'msg'=>$err['message'],'file'=>$err['file'],'line'=>$err['line']]);
    }
});

header('Content-Type: application/json; charset=utf-8');

function out($data, $code=200){
  http_response_code($code);
  echo json_encode($data);
  exit;
}
function getv($k,$d=null){ return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $d; }
function postv($k,$d=null){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }

require_once 'send.php'; // <-- aqui você inclui sua conexão $con
ob_clean(); // Limpa qualquer output de send.php antes de continuar

// Verifica se a conexão foi estabelecida
if (!isset($con) || !$con) {
    out(['ok'=>false,'msg'=>'Falha ao conectar ao banco de dados'], 500);
}
if ($con->connect_error) {
    out(['ok'=>false,'msg'=>'Erro de conexão: '.$con->connect_error], 500);
}

$action = getv('action','');

if ($action === 'debug') {
    out(['get' => $_GET, 'server' => $_SERVER['REQUEST_URI']]);
}

$catId = (int)getv('cat_id', 0);
if ($catId <= 0 && isset($_POST['cat_id'])) {
    $catId = (int)$_POST['cat_id'];
}
if ($catId <= 0) out(['ok'=>false,'msg'=>'cat_id inválido'], 400);

if ($action === 'list') {
  $q = getv('q','');
  $items = [];

  if ($q !== '') {
    $like = "%$q%";
    $stmt = mysqli_prepare($con, "SELECT codigo,titulo,ativo,ordem FROM subcategoria WHERE categoria_id=? AND titulo LIKE ? ORDER BY ordem ASC, titulo ASC");
    if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "is", $catId, $like);
  } else {
    $stmt = mysqli_prepare($con, "SELECT codigo,titulo,ativo,ordem FROM subcategoria WHERE categoria_id=? ORDER BY ordem ASC, titulo ASC");
    if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    mysqli_stmt_bind_param($stmt, "i", $catId);
  }

  if(!mysqli_stmt_execute($stmt)) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  $res = mysqli_stmt_get_result($stmt);
  while($r = mysqli_fetch_assoc($res)) $items[] = $r;
  mysqli_stmt_close($stmt);

  out(['ok'=>true,'items'=>$items]);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD']==='POST') {
  $titulo = postv('titulo','');
  $ativo  = (int)postv('ativo',1);
  if($titulo==='') out(['ok'=>false,'msg'=>'Título obrigatório'], 400);

  $stmt = mysqli_prepare($con, "SELECT COALESCE(MAX(ordem),0)+1 as prox FROM subcategoria WHERE categoria_id=?");
  if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_bind_param($stmt, "i", $catId);
  if(!mysqli_stmt_execute($stmt)) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  $res = mysqli_stmt_get_result($stmt);
  $rowProx = mysqli_fetch_assoc($res);
  $prox = (int)(isset($rowProx['prox']) ? $rowProx['prox'] : 1);
  mysqli_stmt_close($stmt);

  $stmt = mysqli_prepare($con, "INSERT INTO subcategoria (categoria_id,titulo,ativo,ordem) VALUES (?,?,?,?)");
  if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_bind_param($stmt, "isii", $catId, $titulo, $ativo, $prox);
  if(!mysqli_stmt_execute($stmt)) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  $id = mysqli_insert_id($con);
  mysqli_stmt_close($stmt);

  out(['ok'=>true,'id'=>$id]);
}

if ($action === 'get') {
  $id = (int)getv('id',0);
  $stmt = mysqli_prepare($con, "SELECT codigo,titulo,ativo,ordem FROM subcategoria WHERE codigo=? AND categoria_id=?");
  if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_bind_param($stmt, "ii", $id, $catId);
  if(!mysqli_stmt_execute($stmt)) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  if(!$row) out(['ok'=>false,'msg'=>'Não encontrado'], 404);
  out(['ok'=>true,'item'=>$row]);
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD']==='POST') {
  $id = (int)postv('id',0);
  $titulo = postv('titulo','');
  $ativo = (int)postv('ativo',1);
  if($id<=0 || $titulo==='') out(['ok'=>false,'msg'=>'Dados inválidos'], 400);

  $stmt = mysqli_prepare($con, "UPDATE subcategoria SET titulo=?, ativo=? WHERE codigo=? AND categoria_id=?");
  if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_bind_param($stmt, "siii", $titulo, $ativo, $id, $catId);
  if(!mysqli_stmt_execute($stmt)) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_close($stmt);

  out(['ok'=>true]);
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD']==='POST') {
  $id = (int)postv('id',0);

  $stmt = mysqli_prepare($con, "DELETE FROM subcategoria WHERE codigo=? AND categoria_id=?");
  if(!$stmt) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_bind_param($stmt, "ii", $id, $catId);
  if(!mysqli_stmt_execute($stmt)) out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
  mysqli_stmt_close($stmt);

  out(['ok'=>true]);
}

if ($action === 'reorder' && $_SERVER['REQUEST_METHOD']==='POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  $ids = isset($data['ids']) ? $data['ids'] : [];
  if(!is_array($ids) || !count($ids)) out(['ok'=>false,'msg'=>'Lista inválida'], 400);

  mysqli_begin_transaction($con);
  $stmt = mysqli_prepare($con, "UPDATE subcategoria SET ordem=? WHERE codigo=? AND categoria_id=?");
  if(!$stmt){ mysqli_rollback($con); out(['ok'=>false,'msg'=>mysqli_error($con)], 500); }

  $ord = 1;
  foreach($ids as $id){
    $id = (int)$id;
    mysqli_stmt_bind_param($stmt, "iii", $ord, $id, $catId);
    if(!mysqli_stmt_execute($stmt)){
      mysqli_stmt_close($stmt);
      mysqli_rollback($con);
      out(['ok'=>false,'msg'=>mysqli_error($con)], 500);
    }
    $ord++;
  }
  mysqli_stmt_close($stmt);
  mysqli_commit($con);

  out(['ok'=>true]);
}

out(['ok'=>false,'msg'=>'Ação inválida'], 400);
