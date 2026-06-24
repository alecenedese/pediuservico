<?php session_start();
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

include("send.php"); // precisa ter $con

function out($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) out(['ok'=>false, 'msg'=>'Body inválido (JSON esperado)'], 400);

$categoriaId = isset($data['categoria_id']) ? (int)$data['categoria_id'] : 0;
$ids = $data['ids'] ?? [];

if ($categoriaId <= 0) out(['ok'=>false, 'msg'=>'categoria_id inválido'], 400);
if (!is_array($ids) || count($ids) === 0) out(['ok'=>false, 'msg'=>'Selecione ao menos 1 serviço'], 400);

// sanitiza ids
$idsLimpos = [];
foreach ($ids as $id) {
  $id = (int)$id;
  if ($id > 0) $idsLimpos[] = $id;
}
$idsLimpos = array_values(array_unique($idsLimpos));
if (count($idsLimpos) === 0) out(['ok'=>false, 'msg'=>'IDs inválidos'], 400);

// monta IN seguro
$placeholders = implode(',', array_fill(0, count($idsLimpos), '?'));
$types = str_repeat('i', count($idsLimpos) + 1); // +1 categoria_id
$params = array_merge([$categoriaId], $idsLimpos);

// busca títulos no banco (garante que pertencem à categoria)
$sql = "SELECT codigo, titulo
        FROM subcategoria
        WHERE categoria_id = ? AND codigo IN ($placeholders) AND ativo = 1";

$stmt = mysqli_prepare($con, $sql);
if (!$stmt) out(['ok'=>false, 'msg'=>mysqli_error($con)], 500);

// bind_param com quantidade dinâmica
mysqli_stmt_bind_param($stmt, $types, ...$params);

if (!mysqli_stmt_execute($stmt)) out(['ok'=>false, 'msg'=>mysqli_error($con)], 500);

$res = mysqli_stmt_get_result($stmt);
$titulos = [];
while ($r = mysqli_fetch_assoc($res)) {
  $titulos[] = $r['titulo'];
}
mysqli_stmt_close($stmt);

if (count($titulos) === 0) out(['ok'=>false, 'msg'=>'Nenhum serviço válido encontrado'], 400);

// salva na sessão (títulos)
$_SESSION['servicos_oferecidos_titulos'] ??= [];
$_SESSION['servicos_oferecidos_titulos'][$categoriaId] = $titulos;

out(['ok'=>true, 'qtd'=>count($titulos), 'titulos'=>$titulos]);
