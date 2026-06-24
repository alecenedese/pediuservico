<?php
// Item 5: botão único "Meus Orçamentos / Meus Pedidos" que abre um popup
// com as situações do pedido (antes eram abas/botões soltos).
// Uso: defina $areaSituacao = 'prestador' | 'consumidor' antes de incluir.

$areaSituacao = isset($areaSituacao) ? $areaSituacao : 'prestador';

// Garante que os badges estejam calculados
if (!isset($_badgeServicos) || !isset($_badgeNovos)) {
    @include('badge-counts.php');
}
if (!isset($_badgeNovos)) $_badgeNovos = 0;
if (!isset($_badgeAceitos)) $_badgeAceitos = 0;
if (!isset($_badgeEnviados)) $_badgeEnviados = 0;
if (!isset($_badgePerdidos)) $_badgePerdidos = 0;
if (!isset($_badgeFinalizados)) $_badgeFinalizados = 0;

// Badge de pedidos pendentes do consumidor
$_badgePedidosCli = 0;
$_codcliSit = isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : (isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '');
if (!empty($_codcliSit)) {
    $qSitB = mysqli_query($con, "SELECT COUNT(DISTINCT dp.codpedido) as cnt FROM disparo_pedidos dp INNER JOIN pedido p ON p.codigo=dp.codpedido WHERE dp.aceito IN ('a','ac') AND p.codcli='".$_codcliSit."'");
    if ($qSitB && $rSitB = mysqli_fetch_array($qSitB)) $_badgePedidosCli = (int)$rSitB['cnt'];
}

$paginaAtualSit = basename($_SERVER['PHP_SELF']);

if ($areaSituacao === 'consumidor') {
    $tituloBtnSit = '📋 Meus Pedidos';
    $itensSit = [
        ['Pendentes',   'meus-orcamentos-cli.php',          0],
        ['Em andamento','pedidos-aceitos-cli.php',          $_badgePedidosCli],
        ['Finalizados', 'pedidos-finalizados-cli.php',      0],
    ];
} else {
    $tituloBtnSit = '🔧 Meus Orçamentos';
    $itensSit = [
        ['Novos',              'meus-orcamentos.php',             $_badgeNovos],
        ['Aceitos',            'meus-orcamentos2.php',            $_badgeAceitos],
        ['Orçamentos Enviados','meus-orcamentos-aguardando.php',  $_badgeEnviados],
        ['Perdidos',           'meus-orcamentos-perdidos.php',    $_badgePerdidos],
        ['Finalizados',        'meus-orcamentos-finalizados.php', $_badgeFinalizados],
    ];
}

// Texto da situação atual (para mostrar no botão)
$situacaoAtualLabel = '';
foreach ($itensSit as $it) {
    if (basename($it[1]) === $paginaAtualSit) { $situacaoAtualLabel = $it[0]; break; }
}
?>
<style>
.sit-btn{width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;background:linear-gradient(145deg,#00d4ff,#00f0ff);color:#1a2332;border:none;padding:14px 16px;border-radius:12px;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(0,212,255,.35);margin-bottom:14px}
.sit-btn .sit-atual{font-size:12px;font-weight:600;background:rgba(26,35,50,.15);padding:3px 10px;border-radius:12px}
.sit-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:10050;display:none}
.sit-overlay.active{display:block}
.sit-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:90%;max-width:420px;background:#1e3a5f;border:1px solid rgba(0,212,255,.3);border-radius:16px;z-index:10051;display:none;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,.5)}
.sit-modal.active{display:block}
.sit-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px;border-bottom:1px solid rgba(255,255,255,.12)}
.sit-modal-head h3{color:#fff;font-size:16px;margin:0}
.sit-modal-head button{background:none;border:none;color:rgba(255,255,255,.7);font-size:22px;cursor:pointer}
.sit-list a{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:15px 18px;color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08);font-size:15px;font-weight:600}
.sit-list a:last-child{border-bottom:none}
.sit-list a:hover{background:rgba(0,212,255,.12)}
.sit-list a.atual{background:rgba(0,212,255,.18);color:#00f0ff}
.sit-list a .sit-badge{background:#dc3545;color:#fff;font-size:12px;font-weight:700;min-width:22px;height:22px;line-height:22px;border-radius:11px;text-align:center;padding:0 6px}
</style>

<button type="button" class="sit-btn" onclick="abrirSituacoes()">
    <span><?php echo $tituloBtnSit; ?></span>
    <?php if ($situacaoAtualLabel): ?><span class="sit-atual"><?php echo $situacaoAtualLabel; ?> ▾</span><?php else: ?><span class="sit-atual">Ver situações ▾</span><?php endif; ?>
</button>

<div class="sit-overlay" id="sitOverlay" onclick="fecharSituacoes()"></div>
<div class="sit-modal" id="sitModal">
    <div class="sit-modal-head">
        <h3>Situação dos pedidos</h3>
        <button type="button" onclick="fecharSituacoes()">×</button>
    </div>
    <div class="sit-list">
        <?php foreach ($itensSit as $it):
            $atual = (basename($it[1]) === $paginaAtualSit) ? 'atual' : '';
        ?>
            <a href="<?php echo $it[1]; ?>" class="<?php echo $atual; ?>">
                <span><?php echo $it[0]; ?></span>
                <?php if ((int)$it[2] > 0): ?><span class="sit-badge"><?php echo (int)$it[2]; ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
function abrirSituacoes(){
    document.getElementById('sitOverlay').classList.add('active');
    document.getElementById('sitModal').classList.add('active');
}
function fecharSituacoes(){
    document.getElementById('sitOverlay').classList.remove('active');
    document.getElementById('sitModal').classList.remove('active');
}
</script>
