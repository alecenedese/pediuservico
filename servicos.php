<?php
include("send.php");

$categoriaId = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$grupo = isset($_GET['grupo']) ? $_GET['grupo'] : '';
if ($categoriaId <= 0) die("categoria_id inválido.");

// Pega o nome da categoria
$stmt = mysqli_prepare($con, "SELECT titulo FROM categoria WHERE codigo=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $categoriaId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$cat = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
$nomeCategoria = $cat ? $cat['titulo'] : "Categoria #".$categoriaId;

// Carrega subcategorias
$subs = [];
$stmt = mysqli_prepare($con, "
  SELECT codigo AS id, titulo
  FROM subcategoria
  WHERE categoria_id=? AND ativo=1
  ORDER BY ordem ASC, titulo ASC
  LIMIT 200
");
mysqli_stmt_bind_param($stmt, "i", $categoriaId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while($r = mysqli_fetch_assoc($res)) $subs[] = $r;
mysqli_stmt_close($stmt);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$navAtiva = 'buscar';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=h($nomeCategoria)?> - Subcategorias</title>
    <?php include 'pwa-include.php'; ?>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8c 50%, #1e3a5f 100%);
            background-attachment: fixed;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            padding-bottom: 150px;
        }
        .content-area {
            flex: 1;
            padding: 16px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }
        .page-title {
            text-align: center;
            margin-bottom: 16px;
        }
        .page-title h1 {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }
        .page-title p {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            margin-top: 4px;
        }
        .sub-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .sub-item {
            background: rgba(255,255,255,0.95);
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .sub-item:active { transform: scale(0.98); }
        .sub-item.selected {
            border-color: #00bcd4;
            background: #e0f7fa;
            box-shadow: 0 2px 10px rgba(0,188,212,0.25);
        }
        .sub-check {
            width: 24px; height: 24px;
            border-radius: 8px;
            border: 2px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .sub-item.selected .sub-check {
            background: linear-gradient(135deg, #00bcd4, #0097a7);
            border-color: #00bcd4;
        }
        .sub-check svg { display: none; }
        .sub-item.selected .sub-check svg { display: block; }
        .sub-name {
            font-size: 15px;
            font-weight: 600;
            color: #1a2332;
            flex: 1;
        }
        .sub-item.selected .sub-name { color: #006064; }

        .bottom-bar {
            position: fixed;
            bottom: 65px;
            left: 0; right: 0;
            background: rgba(30,58,95,0.97);
            border-top: 2px solid rgba(0,212,255,0.3);
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            z-index: 9990;
            backdrop-filter: blur(8px);
        }
        .bar-info {
            color: #fff;
            font-size: 13px;
            flex-shrink: 0;
        }
        .bar-info strong { color: #00d4ff; font-size: 16px; }
        .btn-continuar {
            background: linear-gradient(135deg, #00bcd4, #0097a7);
            color: #fff;
            border: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            opacity: 0.4;
            pointer-events: none;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-continuar.active {
            opacity: 1;
            pointer-events: auto;
        }
        .btn-continuar.active:active { transform: scale(0.96); }

        .empty-msg {
            text-align: center;
            color: rgba(255,255,255,0.6);
            padding: 40px 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include('header-app.php'); ?>

<div class="content-area">
    <div class="page-title">
        <h1><?=h($nomeCategoria)?></h1>
        <p>Selecione o tipo de serviço</p>
    </div>

    <div class="sub-list" id="subList">
        <?php if (count($subs) === 0): ?>
            <div class="empty-msg">Nenhum serviço disponível nesta categoria.</div>
        <?php else: foreach($subs as $s): ?>
            <div class="sub-item" data-id="<?=(int)$s['id']?>" data-title="<?=h($s['titulo'])?>">
                <span class="sub-check">
                    <svg width="14" height="14" viewBox="0 0 24 24"><path fill="#fff" d="M20.3 5.7a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-1.4 0l-5-5a1 1 0 1 1 1.4-1.4l4.3 4.3 9.3-9.3a1 1 0 0 1 1.4 0z"/></svg>
                </span>
                <span class="sub-name"><?=h($s['titulo'])?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="bottom-bar">
    <div class="bar-info"><strong id="countSel">0</strong> selecionado(s)</div>
    <button class="btn-continuar" id="btnContinuar">Continuar →</button>
</div>

<?php include('bottom-nav.php'); ?>

<script>
const categoriaId = <?=(int)$categoriaId?>;
const grupo = '<?=addslashes($grupo)?>';
const selected = new Set();

document.querySelectorAll('.sub-item').forEach(item => {
    item.addEventListener('click', function(){
        const id = this.dataset.id;
        if (selected.has(id)) {
            selected.delete(id);
            this.classList.remove('selected');
        } else {
            selected.add(id);
            this.classList.add('selected');
        }
        updateBar();
    });
});

function updateBar() {
    document.getElementById('countSel').textContent = selected.size;
    const btn = document.getElementById('btnContinuar');
    if (selected.size > 0) {
        btn.classList.add('active');
    } else {
        btn.classList.remove('active');
    }
}

document.getElementById('btnContinuar').addEventListener('click', function(){
    if (selected.size === 0) return;
    // Vai para solicitar-servico.php pulando a checagem de subcategorias
    window.location.href = 'solicitar-servico.php?categoria=' + encodeURIComponent(grupo) + '&subcategoria=' + encodeURIComponent(categoriaId) + '&sub_ok=1';
});
</script>
</body>
</html>
