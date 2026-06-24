<?php
header('Content-Type: application/json; charset=utf-8');
include("send.php"); // precisa ter $con

$categoriaId = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

if ($categoriaId <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'categoria_id inválido']);
  exit;
}

$items = [];

if ($q !== '') {
  $like = "%{$q}%";
  $stmt = mysqli_prepare($con, "
    SELECT codigo AS id, titulo
    FROM subcategoria
    WHERE categoria_id = ? AND ativo = 1 AND titulo LIKE ?
    ORDER BY ordem ASC, titulo ASC
    LIMIT 50
  ");
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>mysqli_error($con)]);
    exit;
  }
  mysqli_stmt_bind_param($stmt, "is", $categoriaId, $like);
} else {
  $stmt = mysqli_prepare($con, "
    SELECT codigo AS id, titulo
    FROM subcategoria
    WHERE categoria_id = ? AND ativo = 1
    ORDER BY ordem ASC, titulo ASC
    LIMIT 200
  ");
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>mysqli_error($con)]);
    exit;
  }
  mysqli_stmt_bind_param($stmt, "i", $categoriaId);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) $items[] = $r;
mysqli_stmt_close($stmt);

echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
