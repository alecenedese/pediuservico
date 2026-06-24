<?php
session_start();
require_once("send.php");

// =============================================================
// Pagina: tornar-se prestador
// - Reaproveita os dados do cliente logado (nome, CPF/CNPJ, whatsapp, senha)
// - Cria registro em "parceiro" se ainda nao existir
// - Mostra accordion de categorias para o usuario escolher
// - Apos salvar, marca cookies eh_prestador=1 e redireciona p/ meus-orcamentos.php
// =============================================================

// 1) Exige login unificado
$logado = isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1';
if (!$logado) {
    echo "<script>window.location.href='login-unificado.php?retorno=tornar-prestador.php';</script>";
    exit;
}

// 2) Coleta dados do usuario via cookies do login unificado
$cpfCnpjLimpo = isset($_COOKIE['cpf_cnpj_unificado']) ? preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']) : '';
$nomeUsuario  = isset($_COOKIE['nome_usuario'])    ? $_COOKIE['nome_usuario']    : '';
$whatsapp     = isset($_COOKIE['celular_usuario']) ? $_COOKIE['celular_usuario'] : '';

if (empty($cpfCnpjLimpo)) {
    echo "<script>alert('Sessao invalida. Faca login novamente.'); window.location.href='login-unificado.php';</script>";
    exit;
}

$cpfCnpjEsc = mysqli_real_escape_string($con, $cpfCnpjLimpo);

// 3) Busca dados completos em clientes (NOME, CNPJ_CPF, CELULAR, senha) para usar como base
$qCli = mysqli_query($con, "
    SELECT id, TIPO, NOME, CNPJ_CPF, CELULAR, senha
    FROM clientes
    WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjEsc'
    LIMIT 1
");
$dadosCli = ($qCli && mysqli_num_rows($qCli) > 0) ? mysqli_fetch_assoc($qCli) : null;

// 4) Verifica se ja existe um parceiro com esse CPF/CNPJ
$qPrest = mysqli_query($con, "
    SELECT id, NOME, CNPJ_CPF, CELULAR
    FROM parceiro
    WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfCnpjEsc'
    LIMIT 1
");
$idPrestador = 0;
if ($qPrest && mysqli_num_rows($qPrest) > 0) {
    $rowPrest = mysqli_fetch_assoc($qPrest);
    $idPrestador = (int)$rowPrest['id'];
} else {
    // Cria parceiro reaproveitando dados do cliente
    $nomeIns    = mysqli_real_escape_string($con, $dadosCli['NOME']    ?? $nomeUsuario);
    $cpfCnpjIns = mysqli_real_escape_string($con, $dadosCli['CNPJ_CPF'] ?? $cpfCnpjLimpo);
    $celIns     = mysqli_real_escape_string($con, $dadosCli['CELULAR'] ?? $whatsapp);
    $senhaIns   = mysqli_real_escape_string($con, $dadosCli['senha']   ?? '');
    $dataCad    = date('Y-m-d');

    $insOk = mysqli_query($con, "
        INSERT INTO parceiro (TIPO, NOME, CNPJ_CPF, CELULAR, ESTADO, MUNICIPIO, senha, serviconao, dataCad)
        VALUES ('pre', '$nomeIns', '$cpfCnpjIns', '$celIns', '', '', '$senhaIns', '', '$dataCad')
    ");
    if ($insOk) {
        $idPrestador = mysqli_insert_id($con);
    } else {
        echo "<script>alert('Erro ao criar perfil de prestador.'); window.location.href='buscar.php';</script>";
        exit;
    }
}

// 5) Marca cookies de prestador
$exp = time() + (30 * 24 * 3600);
setcookie('eh_prestador', '1',           $exp, '/');
setcookie('id_prestador', $idPrestador,  $exp, '/');
$_COOKIE['eh_prestador'] = '1';
$_COOKIE['id_prestador'] = $idPrestador;

// 6) Processa POST (salvamento das categorias selecionadas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subcategoria']) && is_array($_POST['subcategoria'])) {
    $sucessos = 0;
    foreach ($_POST['subcategoria'] as $codsub) {
        $codsub = mysqli_real_escape_string($con, $codsub);
        if ($codsub === '') continue;

        // Descobre codgrupo da subcategoria
        $qG = mysqli_query($con, "SELECT codgrupo FROM categoria WHERE codigo='$codsub' LIMIT 1");
        $codgrupo = 0;
        if ($qG && $rG = mysqli_fetch_array($qG)) $codgrupo = $rG['codgrupo'];

        // Evita duplicidade
        $qDup = mysqli_query($con, "SELECT 1 FROM categoria_prestador WHERE codcadastro='$idPrestador' AND codsubcategoria='$codsub' LIMIT 1");
        if (!$qDup || mysqli_num_rows($qDup) === 0) {
            mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) VALUES ('$idPrestador', '$codgrupo', '$codsub')");
            $sucessos++;
        }
    }
    // Garante que users (chat) tenha registro do prestador
    @mysqli_query($con, "INSERT IGNORE INTO users (user_id, name, username, password, p_p, last_seen, celular) VALUES ('$idPrestador', '".mysqli_real_escape_string($con,$nomeUsuario)."', '".mysqli_real_escape_string($con,$nomeUsuario)."', '', 'user-default.png', '', '".mysqli_real_escape_string($con,$whatsapp)."')");

    echo "<script>alert('Categorias salvas com sucesso! Voce ja pode receber pedidos.'); window.location.href='meus-orcamentos.php';</script>";
    exit;
}

// 7) Categorias atualmente selecionadas pelo prestador
$selecionadas = [];
$qSel = mysqli_query($con, "SELECT codsubcategoria FROM categoria_prestador WHERE codcadastro='$idPrestador'");
while ($qSel && $r = mysqli_fetch_array($qSel)) { $selecionadas[$r['codsubcategoria']] = true; }

$temCategorias = !empty($selecionadas);

$navAtiva = 'servicos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tornar-se Prestador - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#1a2332 0%,#2d4a6b 100%);min-height:100vh;padding-bottom:90px}
        .container{max-width:560px;margin:0 auto;padding:16px}
        .card{background:rgba(255,255,255,.97);border-radius:14px;padding:22px 18px;box-shadow:0 8px 24px rgba(0,0,0,.25);margin-top:14px}
        .card h2{color:#1a2332;font-size:20px;margin-bottom:6px}
        .card .sub{color:#6b7280;font-size:13px;margin-bottom:16px}

        .info-box{background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.25);color:#0c4a6e;border-radius:10px;padding:12px;margin-bottom:14px;font-size:13px}
        .info-box strong{color:#0ea5e9}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;font-size:12px;color:#374151}
        .info-grid div span{font-weight:600;color:#1a2332}

        .search-input{width:100%;padding:11px 14px;border:2px solid rgba(0,212,255,.25);border-radius:24px;font-size:14px;background:#f9fafb;margin-bottom:12px}
        .search-input:focus{outline:none;border-color:#00d4ff;background:#fff}

        .accordion{border:1px solid rgba(0,212,255,.2);border-radius:10px;overflow:hidden}
        .ac-item{border-bottom:1px solid rgba(0,212,255,.12)}
        .ac-item:last-child{border-bottom:none}
        .ac-head{background:rgba(0,212,255,.08);padding:12px 14px;font-weight:600;color:#1a2332;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-size:14px}
        .ac-head:hover{background:rgba(0,212,255,.18)}
        .ac-head .badge{background:#00d4ff;color:#1a2332;border-radius:10px;font-size:11px;padding:2px 8px;font-weight:700;display:none}
        .ac-head .badge.on{display:inline-block}
        .ac-body{padding:10px 14px;background:#fff;display:none}
        .ac-body.show{display:block}

        .chk-item{display:flex;align-items:center;gap:8px;padding:6px 0}
        .chk-item input{width:18px;height:18px;accent-color:#00d4ff}
        .chk-item label{font-size:14px;color:#1a2332;cursor:pointer}

        .selected-bar{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:10px 12px;margin-top:14px;font-size:13px;color:#065f46;display:none}
        .selected-bar.on{display:block}

        .btn-save{margin-top:16px}
        .btn-save button{width:100%;display:block;padding:14px;background:linear-gradient(145deg,#00d4ff,#00f0ff);color:#1a2332;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(0,212,255,.4)}
        .btn-save button:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(0,212,255,.5)}
        .btn-save button:disabled{opacity:.6;cursor:not-allowed;transform:none}
    </style>
</head>
<body>
<?php include('header-app.php'); ?>

<div class="container">
    <div class="card">
        <h2>🛠️ Quer se tornar um prestador?</h2>
        <p class="sub">Você já tem cadastro. Agora só falta escolher as categorias dos serviços que você oferece.</p>

        <div class="info-box">
            <strong>Seu cadastro atual:</strong>
            <div class="info-grid">
                <div>Nome: <span><?php echo htmlspecialchars($nomeUsuario); ?></span></div>
                <div>CPF/CNPJ: <span><?php echo htmlspecialchars($dadosCli['CNPJ_CPF'] ?? $cpfCnpjLimpo); ?></span></div>
                <div>WhatsApp: <span><?php echo htmlspecialchars($whatsapp); ?></span></div>
                <div>Status: <span>Cliente <?php echo $temCategorias ? '+ Prestador' : ''; ?></span></div>
            </div>
        </div>

        <form method="POST" id="formCategorias">
            <input type="search" id="buscaCat" class="search-input" placeholder="🔍 Procurar categoria...">

            <div class="accordion" id="acc">
                <?php
                $qGrupos = mysqli_query($con, "SELECT * FROM grupos ORDER BY titulo ASC");
                while ($g = mysqli_fetch_assoc($qGrupos)) {
                    $gid = (int)$g['codigo'];
                    echo '<div class="ac-item" data-titulo="'.htmlspecialchars(strtolower($g['titulo'])).'">';
                    echo   '<div class="ac-head" onclick="toggleAc('.$gid.')">';
                    echo     '<span>'.htmlspecialchars($g['titulo']).'</span>';
                    echo     '<span class="badge" id="bdg'.$gid.'">0</span>';
                    echo   '</div>';
                    echo   '<div class="ac-body" id="body'.$gid.'">';
                    $qSubs = mysqli_query($con, "SELECT codigo, titulo FROM categoria WHERE codgrupo='$gid' ORDER BY titulo ASC");
                    while ($s = mysqli_fetch_assoc($qSubs)) {
                        $sid = (int)$s['codigo'];
                        $checked = isset($selecionadas[$sid]) ? 'checked' : '';
                        echo '<div class="chk-item">';
                        echo   '<input type="checkbox" id="sub'.$sid.'" name="subcategoria[]" value="'.$sid.'" data-grupo="'.$gid.'" '.$checked.' onchange="recalc('.$gid.')">';
                        echo   '<label for="sub'.$sid.'">'.htmlspecialchars($s['titulo']).'</label>';
                        echo '</div>';
                    }
                    echo   '</div>';
                    echo '</div>';
                }
                ?>
            </div>

            <div class="selected-bar" id="selBar"><strong>Selecionados:</strong> <span id="selCount">0</span> serviço(s)</div>

            <div class="btn-save">
                <button type="submit" id="btnSalvar">💾 Salvar e continuar</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAc(id) {
    var b = document.getElementById('body'+id);
    b.classList.toggle('show');
}
function recalc(grupoId) {
    // badge por grupo
    var checks = document.querySelectorAll('input[data-grupo="'+grupoId+'"]:checked');
    var bdg = document.getElementById('bdg'+grupoId);
    if (checks.length > 0) { bdg.textContent = checks.length; bdg.classList.add('on'); }
    else                   { bdg.textContent = 0; bdg.classList.remove('on'); }
    // total
    var tot = document.querySelectorAll('input[name="subcategoria[]"]:checked').length;
    document.getElementById('selCount').textContent = tot;
    document.getElementById('selBar').classList.toggle('on', tot > 0);
}
// Inicializa badges
document.querySelectorAll('.ac-item').forEach(function(it) {
    var c = it.querySelector('.ac-head .badge');
    if (!c) return;
    var gid = c.id.replace('bdg','');
    recalc(gid);
});
// Filtro
document.getElementById('buscaCat').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.ac-item').forEach(function(it) {
        var titulo = it.dataset.titulo || '';
        var match = !q || titulo.indexOf(q) !== -1;
        // verifica sub-itens
        if (!match) {
            it.querySelectorAll('.chk-item label').forEach(function(lb) {
                if (lb.textContent.toLowerCase().indexOf(q) !== -1) match = true;
            });
        }
        it.style.display = match ? '' : 'none';
        if (match && q) it.querySelector('.ac-body').classList.add('show');
    });
});

document.getElementById('formCategorias').addEventListener('submit', function(e) {
    var tot = document.querySelectorAll('input[name="subcategoria[]"]:checked').length;
    if (tot === 0) {
        e.preventDefault();
        alert('Selecione ao menos 1 categoria para se cadastrar como prestador.');
        return;
    }
    document.getElementById('btnSalvar').disabled = true;
    document.getElementById('btnSalvar').textContent = 'Salvando...';
});
</script>
</body>
</html>
