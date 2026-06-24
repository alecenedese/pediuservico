<?php
// Detecta se está logado
$logado = (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') || (isset($_COOKIE['celular_usuario']) && !empty($_COOKIE['celular_usuario'])) || (isset($_COOKIE['login']) && !empty($_COOKIE['login']));
$navAtiva = isset($navAtiva) ? $navAtiva : '';

// Modo (cliente/prestador) e se é prestador
include_once(__DIR__ . '/area-modo.php');

// Badges do prestador (se ainda não foram calculados)
if (!isset($_badgeServicos)) {
    include('badge-counts.php');
}

// Conta pedidos pendentes para o cliente (badge nas "Meus Pedidos")
$_badgePedidos = 0;
$_codcli = isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : (isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '');
if (!empty($_codcli)) {
    $qCliB = mysqli_query($con, "SELECT COUNT(DISTINCT dp.codpedido) as cnt FROM disparo_pedidos dp INNER JOIN pedido p ON p.codigo=dp.codpedido WHERE dp.aceito IN ('a','ac') AND p.codcli='".$_codcli."'");
    if ($qCliB && $rCliB = mysqli_fetch_array($qCliB)) {
        $_badgePedidos = (int)$rCliB['cnt'];
    }
}
?>
<style>
.bottom-nav{position:fixed;bottom:0;left:0;right:0;background:linear-gradient(180deg,#3a5681 0%,#2f4668 100%);border-top:2px solid rgba(0,212,255,.45);display:flex;z-index:9999;padding:6px 8px 8px;box-shadow:0 -4px 18px rgba(0,0,0,.4)}
.nav-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;margin:0 5px;padding:8px 4px;text-decoration:none;color:rgba(255,255,255,.85);font-size:11px;font-weight:700;transition:.15s;gap:3px;position:relative;border:none;cursor:pointer;font-family:inherit;border-radius:12px;background:linear-gradient(180deg,#46689a 0%,#37557f 100%);box-shadow:0 3px 0 rgba(0,0,0,.28),inset 0 1px 0 rgba(255,255,255,.22)}
.nav-item.active{color:#00f0ff;background:linear-gradient(180deg,#4f74ab 0%,#3c5d8c 100%);box-shadow:0 3px 0 rgba(0,0,0,.32),inset 0 0 0 1.5px rgba(0,212,255,.6)}
.nav-item:active{transform:translateY(2px);box-shadow:0 1px 0 rgba(0,0,0,.28),inset 0 1px 0 rgba(255,255,255,.22)}
.nav-item .nav-icon{font-size:21px;line-height:1}
.nav-item:hover{color:#00f0ff}
.nav-item .nav-label{white-space:nowrap}
.nav-badge{position:absolute;top:2px;right:50%;transform:translateX(22px);background:#dc3545;color:#fff;font-size:11px;font-weight:700;min-width:20px;height:20px;line-height:20px;border-radius:10px;text-align:center;padding:0 5px;box-shadow:0 1px 4px rgba(220,53,69,.4)}
</style>
<nav class="bottom-nav">
<?php if ($modoArea === 'prestador'): ?>
    <!-- ===================== MODO PRESTADOR ===================== -->
    <a href="meus-orcamentos.php" id="nav-servicos" class="nav-item <?php echo $navAtiva == 'servicos' ? 'active' : ''; ?>">
        <span class="nav-icon">🔧</span>
        <span class="nav-label">Meus Serviços</span>
        <?php if ($_badgeServicos > 0) { ?><span class="nav-badge"><?php echo $_badgeServicos; ?></span><?php } ?>
    </a>
    <a href="minhasmoedas.php" class="nav-item <?php echo $navAtiva == 'moedas' ? 'active' : ''; ?>">
        <span class="nav-icon">🪙</span>
        <span class="nav-label">Minhas Moedas</span>
    </a>
    <a href="javascript:void(0)" onclick="abrirConta()" class="nav-item">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Conta</span>
    </a>
<?php else: ?>
    <!-- ===================== MODO CLIENTE (padrão) ===================== -->
    <a href="buscar.php" class="nav-item <?php echo $navAtiva == 'buscar' ? 'active' : ''; ?>">
        <span class="nav-icon">🔍</span>
        <span class="nav-label">Buscar Serviço</span>
    </a>
    <a href="meus-orcamentos-cli.php" id="nav-pedidos" class="nav-item <?php echo $navAtiva == 'pedidos' ? 'active' : ''; ?>">
        <span class="nav-icon">📋</span>
        <span class="nav-label">Meus Pedidos</span>
        <?php if ($_badgePedidos > 0) { ?><span class="nav-badge"><?php echo $_badgePedidos; ?></span><?php } ?>
    </a>
    <a href="javascript:void(0)" onclick="abrirConta()" class="nav-item">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Conta</span>
    </a>
<?php endif; ?>
</nav>
<script>
// Alterna entre modo cliente e prestador (só para quem é prestador)
function setModoArea(modo){
    document.cookie = 'modo_area=' + modo + ';path=/;max-age=' + (365*24*3600);
    window.location.href = (modo === 'prestador') ? 'meus-orcamentos.php' : 'buscar.php';
}
// Abre o menu da conta (ou login se não estiver logado)
function abrirConta(){
    if (typeof window.toggleUserMenu === 'function') {
        window.toggleUserMenu();
    } else {
        window.location.href = '<?php echo $logado ? "dados-pessoais.php" : "login-unificado.php"; ?>';
    }
}

// Atualização automática de badges + som de notificação
let somNotificacaoUrl = '';
let ultimoTotalNotif = null;
fetch('get-som-notificacao.php', {cache:'no-store'})
    .then(r => r.json())
    .then(d => { somNotificacaoUrl = d.url || ''; })
    .catch(() => {});

function tocarSomNotificacao() {
    try {
        if (somNotificacaoUrl) {
            const a = new Audio(somNotificacaoUrl);
            a.play().catch(() => {});
        } else {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = new Ctx();
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.type = 'sine'; o.frequency.value = 880; g.gain.value = 0.08;
            o.start();
            setTimeout(() => { o.stop(); ctx.close(); }, 250);
        }
    } catch (e) {}
}

function atualizarBadges() {
    fetch('get-badge-counts.php?t=' + Date.now(), { method:'GET', cache:'no-store', credentials:'same-origin' })
    .then(response => response.json())
    .then(data => {
        if (!data.success) return;
        const totalNotif = (parseInt(data.badges.servicos) || 0) + (parseInt(data.badges.pedidos) || 0);
        if (ultimoTotalNotif !== null && totalNotif > ultimoTotalNotif) { tocarSomNotificacao(); }
        ultimoTotalNotif = totalNotif;

        // Badge "Meus Serviços" (modo prestador)
        var elServ = document.querySelector('#nav-servicos .nav-badge') || (document.getElementById('nav-servicos') ? null : null);
        var itemServ = document.getElementById('nav-servicos');
        if (itemServ) {
            var b = itemServ.querySelector('.nav-badge');
            if ((data.badges.servicos||0) > 0) {
                if (!b) { b = document.createElement('span'); b.className='nav-badge'; itemServ.appendChild(b); }
                b.textContent = data.badges.servicos; b.style.display='block';
            } else if (b) { b.style.display='none'; }
        }
        // Badge "Meus Pedidos" (modo cliente)
        var itemPed = document.getElementById('nav-pedidos');
        if (itemPed && data.badges.pedidos !== undefined) {
            var bp = itemPed.querySelector('.nav-badge');
            if ((data.badges.pedidos||0) > 0) {
                if (!bp) { bp = document.createElement('span'); bp.className='nav-badge'; itemPed.appendChild(bp); }
                bp.textContent = data.badges.pedidos; bp.style.display='block';
            } else if (bp) { bp.style.display='none'; }
        }

        // Badges nas tabs internas (páginas de prestador)
        document.querySelectorAll('.tab-badge').forEach(badge => {
            const tab = badge.closest('.tab'); if (!tab) return;
            const href = tab.getAttribute('href'); if (!href) return;
            let count = 0;
            if (href.includes('meus-orcamentos.php') && !href.includes('-')) count = data.badges.novos;
            else if (href.includes('meus-orcamentos2.php')) count = data.badges.aceitos;
            else if (href.includes('aguardando')) count = data.badges.enviados;
            else if (href.includes('perdidos')) count = data.badges.perdidos;
            else if (href.includes('finalizados')) count = data.badges.finalizados;
            if (count > 0) { badge.textContent = count; badge.style.display='block'; }
            else { badge.style.display='none'; }
        });
    })
    .catch(err => console.error('Erro ao atualizar badges:', err));
}
setInterval(atualizarBadges, 10000);
setTimeout(atualizarBadges, 2000);
</script>
