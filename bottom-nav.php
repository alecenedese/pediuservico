<?php
// Detecta se está logado
$logado = (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') || (isset($_COOKIE['celular_usuario']) && !empty($_COOKIE['celular_usuario'])) || (isset($_COOKIE['login']) && !empty($_COOKIE['login']));
$navAtiva = isset($navAtiva) ? $navAtiva : '';

// Badges do prestador (se ainda não foram calculados)
if (!isset($_badgeServicos)) {
    include('badge-counts.php');
}

// Conta pedidos pendentes para o cliente (badge nas "Minhas Buscas")
$_badgePedidos = 0;
$_codcli = isset($_COOKIE['id_cliente']) && !empty($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : (isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '');
if (!empty($_codcli)) {
    $qCliB = mysqli_query($con, "SELECT COUNT(DISTINCT dp.codpedido) as cnt FROM disparo_pedidos dp INNER JOIN pedido p ON p.codigo=dp.codpedido WHERE dp.aceito IN ('a','ac') AND p.codcli='".$_codcli."'");
    if ($qCliB && $rCliB = mysqli_fetch_array($qCliB)) {
        $_badgePedidos = (int)$rCliB['cnt'];
    }
}

// Badge de finalizados para o prestador (avaliações não vistas)
if (!isset($_badgeFinalizados)) {
    $_badgeFinalizados = 0;
    if (!empty($_idPrestBadge)) {
        $qF2 = mysqli_query($con, "SELECT COUNT(DISTINCT d.codpedido) as cnt FROM disparo_pedidos d WHERE d.codcadastro='$_idPrestBadge' AND d.aceito='f' AND d.visto=0");
        if ($qF2 && $rF2 = mysqli_fetch_array($qF2)) $_badgeFinalizados = (int)$rF2['cnt'];
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
    <a href="meus-orcamentos-cli.php" class="nav-item <?php echo $navAtiva == 'pedidos' ? 'active' : ''; ?>">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Área do Consumidor</span>
        <?php if ($_badgePedidos > 0) { ?><span class="nav-badge"><?php echo $_badgePedidos; ?></span><?php } ?>
    </a>
    <a href="buscar.php" class="nav-item <?php echo $navAtiva == 'buscar' ? 'active' : ''; ?>">
        <span class="nav-icon">🔍</span>
        <span class="nav-label">Buscar Prestador</span>
    </a>
    <a href="meus-orcamentos.php" class="nav-item <?php echo $navAtiva == 'servicos' ? 'active' : ''; ?>">
        <span class="nav-icon">🔧</span>
        <span class="nav-label">Área dos Prestadores</span>
        <?php if ($_badgeServicos > 0) { ?><span class="nav-badge"><?php echo $_badgeServicos; ?></span><?php } ?>
    </a>
</nav>
<script>
function toggleBuscasMenu(){
    const d=document.getElementById('buscas-dropdown');
    const o=document.getElementById('buscas-dropdown-overlay');
    const open=d.style.display==='block';
    d.style.display=open?'none':'block';
    o.style.display=open?'none':'block';
}
function fecharBuscasMenu(){
    document.getElementById('buscas-dropdown').style.display='none';
    document.getElementById('buscas-dropdown-overlay').style.display='none';
}
document.querySelectorAll('#buscas-dropdown a').forEach(function(a){
    a.addEventListener('click',function(){fecharBuscasMenu();});
});

// Item 3 & 24: Atualização automática de badges a cada 10 segundos
// Item 9: som de notificação quando o total de notificações aumenta
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
            // Bipe padrão (caso nenhum som tenha sido configurado no admin)
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
    fetch('get-badge-counts.php?t=' + Date.now(), {
        method: 'GET',
        cache: 'no-store',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Item 9: detecta aumento no total de notificações e toca o som
            const totalNotif = (parseInt(data.badges.servicos) || 0) + (parseInt(data.badges.pedidos) || 0);
            if (ultimoTotalNotif !== null && totalNotif > ultimoTotalNotif) {
                tocarSomNotificacao();
            }
            ultimoTotalNotif = totalNotif;

            // Atualiza badge de "Área dos Prestadores" (servicos já inclui finalizados)
            const servicosBadge = document.querySelector('.nav-item[href="meus-orcamentos.php"] .nav-badge');
            const totalServicos = data.badges.servicos;
            if (servicosBadge) {
                if (totalServicos > 0) {
                    servicosBadge.textContent = totalServicos;
                    servicosBadge.style.display = 'block';
                } else {
                    servicosBadge.style.display = 'none';
                }
            }
            
            // Atualiza badge de "Minhas Buscas"
            const pedidosBadge = document.querySelector('button.nav-item .nav-badge');
            if (pedidosBadge && data.badges.pedidos !== undefined) {
                if (data.badges.pedidos > 0) {
                    pedidosBadge.textContent = data.badges.pedidos;
                    pedidosBadge.style.display = 'block';
                } else {
                    pedidosBadge.style.display = 'none';
                }
            }
            
            // Atualiza badges nas tabs (se estiver em página de prestador)
            const tabBadges = document.querySelectorAll('.tab-badge');
            if (tabBadges.length > 0) {
                tabBadges.forEach(badge => {
                    const tab = badge.closest('.tab');
                    if (!tab) return;
                    
                    const href = tab.getAttribute('href');
                    if (!href) return;
                    
                    let count = 0;
                    if (href.includes('meus-orcamentos.php') && !href.includes('-')) count = data.badges.novos;
                    else if (href.includes('meus-orcamentos2.php')) count = data.badges.aceitos;
                    else if (href.includes('aguardando')) count = data.badges.enviados;
                    else if (href.includes('perdidos')) count = data.badges.perdidos;
                    else if (href.includes('finalizados')) count = data.badges.finalizados;
                    
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }
        }
    })
    .catch(err => {
        console.error('Erro ao atualizar badges:', err);
    });
}

// Atualiza badges a cada 10 segundos
setInterval(atualizarBadges, 10000);

// Primeira atualização após 2 segundos (para não conflitar com carregamento inicial)
setTimeout(atualizarBadges, 2000);
</script>
