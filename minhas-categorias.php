<?php
session_start();
require_once("send.php");

// Identifica o prestador logado
$idPrest = 0;
if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $q = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
    if ($q && $r = mysqli_fetch_assoc($q)) $idPrest = $r['id'];
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $idPrest = (int)$_COOKIE['id_prestador'];
} elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
    $idPrest = (int)$_COOKIE['id'];
} elseif (isset($_COOKIE['cpf_cnpj_unificado']) && !empty($_COOKIE['cpf_cnpj_unificado'])) {
    $cpf = preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']);
    $cpfEsc = mysqli_real_escape_string($con, $cpf);
    $q = mysqli_query($con, "SELECT id FROM parceiro WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc' LIMIT 1");
    if ($q && $r = mysqli_fetch_assoc($q)) $idPrest = $r['id'];
}

// Se não é prestador, manda para tornar-se prestador
if (!$idPrest) {
    if (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') {
        echo "<script>window.location.href='tornar-prestador.php';</script>";
    } else {
        echo "<script>window.location.href='login-unificado.php?retorno=minhas-categorias.php';</script>";
    }
    exit;
}

$mensagem = '';

// Processa remoção de categoria
if (isset($_GET['remover']) && is_numeric($_GET['remover'])) {
    $codsub = (int)$_GET['remover'];
    mysqli_query($con, "DELETE FROM categoria_prestador WHERE codcadastro='$idPrest' AND codsubcategoria='$codsub'");
    echo "<script>window.location.href='minhas-categorias.php?msg=removido';</script>";
    exit;
}

// Processa adição de categorias (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subcategoria']) && is_array($_POST['subcategoria'])) {
    foreach ($_POST['subcategoria'] as $codsub) {
        $codsub = mysqli_real_escape_string($con, $codsub);
        if ($codsub === '') continue;
        $qG = mysqli_query($con, "SELECT codgrupo FROM categoria WHERE codigo='$codsub' LIMIT 1");
        $codgrupo = ($qG && $rG = mysqli_fetch_array($qG)) ? $rG['codgrupo'] : 0;
        $qDup = mysqli_query($con, "SELECT 1 FROM categoria_prestador WHERE codcadastro='$idPrest' AND codsubcategoria='$codsub' LIMIT 1");
        if (!$qDup || mysqli_num_rows($qDup) === 0) {
            mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) VALUES ('$idPrest', '$codgrupo', '$codsub')");
        }
    }
    echo "<script>window.location.href='minhas-categorias.php?msg=salvo';</script>";
    exit;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'salvo') $mensagem = 'Categorias adicionadas com sucesso!';
    elseif ($_GET['msg'] === 'removido') $mensagem = 'Categoria removida.';
}

// Categorias já cadastradas pelo prestador
$minhasCategorias = [];
$qMinhas = mysqli_query($con, "
    SELECT c.codigo, c.titulo, g.titulo as grupo
    FROM categoria_prestador cp
    INNER JOIN categoria c ON c.codigo = cp.codsubcategoria
    INNER JOIN grupos g ON g.codigo = c.codgrupo
    WHERE cp.codcadastro = '$idPrest'
    ORDER BY g.titulo, c.titulo
");
$jaCadastradas = [];
while ($qMinhas && $r = mysqli_fetch_assoc($qMinhas)) {
    $minhasCategorias[] = $r;
    $jaCadastradas[$r['codigo']] = true;
}

$navAtiva = 'servicos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Categorias - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            background-attachment: fixed;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 70px;
            color: #fff;
        }
        .main-content { flex:1; padding:16px; max-width:600px; margin:0 auto; width:100%; }
        .page-title { text-align:center; color:#00d4ff; font-size:22px; font-weight:bold; margin-bottom:16px; }
        .msg-sucesso { background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.4); color:#22c55e; padding:12px; border-radius:8px; text-align:center; font-weight:600; margin-bottom:16px; }
        .card { background:rgba(255,255,255,0.95); border-radius:12px; padding:18px; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-bottom:16px; }
        .card-title { font-size:15px; font-weight:700; color:#1a2332; margin-bottom:12px; }
        /* Categorias cadastradas */
        .cat-tag { display:inline-flex; align-items:center; gap:8px; background:#e0f7fa; color:#006064; border:1px solid #00bcd4; border-radius:20px; padding:6px 12px; font-size:13px; font-weight:600; margin:4px 4px 4px 0; }
        .cat-tag .remove { background:#dc3545; color:#fff; border:none; border-radius:50%; width:18px; height:18px; font-size:11px; cursor:pointer; line-height:1; }
        .sem-cat { color:#666; font-size:13px; font-style:italic; }
        /* Accordion */
        .accordion-item { border:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px; overflow:hidden; }
        .accordion-header { padding:12px 14px; background:#f8f9fa; cursor:pointer; font-weight:600; color:#1a2332; font-size:14px; }
        .accordion-header:hover { background:#eef2f5; }
        .accordion-content { display:none; padding:8px 14px; }
        .accordion-content.open { display:block; }
        .checkbox-item { display:flex; align-items:center; gap:8px; padding:8px 0; border-bottom:1px solid #f0f0f0; }
        .checkbox-item label { color:#1a2332; font-size:14px; cursor:pointer; flex:1; }
        .checkbox-item input { width:18px; height:18px; accent-color:#00bcd4; }
        .checkbox-item.ja-tem label { color:#999; }
        .busca-cat { width:100%; padding:10px 14px; border:2px solid #00bcd4; border-radius:8px; font-size:14px; margin-bottom:12px; color:#1a2332; }
        .submit-btn { width:100%; padding:14px; background:linear-gradient(135deg,#00bcd4,#0097a7); color:#fff; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:pointer; margin-top:12px; }
        .submit-btn:active { transform:scale(0.99); }
    </style>
</head>
<body>
<?php include('header-app.php'); ?>

<div class="main-content">
    <div class="page-title">📂 Minhas Categorias</div>

    <?php if ($mensagem) { ?>
        <div class="msg-sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php } ?>

    <!-- Categorias já cadastradas -->
    <div class="card">
        <div class="card-title">Categorias que você atende</div>
        <?php if (count($minhasCategorias) === 0) { ?>
            <div class="sem-cat">Você ainda não tem categorias cadastradas. Adicione abaixo.</div>
        <?php } else { foreach ($minhasCategorias as $mc) { ?>
            <span class="cat-tag">
                <?php echo htmlspecialchars($mc['titulo']); ?>
                <button class="remove" onclick="if(confirm('Remover esta categoria?')) window.location.href='minhas-categorias.php?remover=<?php echo $mc['codigo']; ?>'">✕</button>
            </span>
        <?php } } ?>
    </div>

    <!-- Adicionar novas categorias -->
    <div class="card">
        <div class="card-title">Adicionar categorias</div>
        <input type="text" class="busca-cat" id="buscaCat" placeholder="🔍 Buscar categoria..." onkeyup="filtrarCat()">
        <form method="POST">
            <div id="accordion">
                <?php
                $resG = mysqli_query($con, "SELECT * FROM grupos ORDER BY titulo ASC");
                while ($g = mysqli_fetch_assoc($resG)) { ?>
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAcc(this)">📁 <?php echo htmlspecialchars($g['titulo']); ?></div>
                        <div class="accordion-content">
                            <?php
                            $resC = mysqli_query($con, "SELECT * FROM categoria WHERE codgrupo='".$g['codigo']."' ORDER BY titulo ASC");
                            while ($c = mysqli_fetch_assoc($resC)) {
                                $jaTem = isset($jaCadastradas[$c['codigo']]);
                            ?>
                                <div class="checkbox-item <?php echo $jaTem ? 'ja-tem' : ''; ?>">
                                    <input type="checkbox" id="cat-<?php echo $c['codigo']; ?>" value="<?php echo $c['codigo']; ?>" name="subcategoria[]" <?php echo $jaTem ? 'checked disabled' : ''; ?>>
                                    <label for="cat-<?php echo $c['codigo']; ?>"><?php echo htmlspecialchars($c['titulo']); ?><?php echo $jaTem ? ' (já cadastrada)' : ''; ?></label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <button type="submit" class="submit-btn">💾 Salvar Categorias</button>
        </form>
    </div>
</div>

<?php include('bottom-nav.php'); ?>

<script>
function toggleAcc(header) {
    const content = header.nextElementSibling;
    content.classList.toggle('open');
}
function filtrarCat() {
    const termo = document.getElementById('buscaCat').value.toLowerCase();
    document.querySelectorAll('.accordion-item').forEach(item => {
        let temMatch = false;
        item.querySelectorAll('.checkbox-item').forEach(ci => {
            const txt = ci.textContent.toLowerCase();
            const match = txt.includes(termo);
            ci.style.display = match ? 'flex' : 'none';
            if (match) temMatch = true;
        });
        if (termo) {
            item.style.display = temMatch ? 'block' : 'none';
            if (temMatch) item.querySelector('.accordion-content').classList.add('open');
        } else {
            item.style.display = 'block';
        }
    });
}
</script>
</body>
</html>
