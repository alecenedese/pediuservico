<?php
header('Content-Type: text/html; charset=utf-8');
$baseUrl = '/pediuservico';
$_pwa_idPrestador = isset($_COOKIE['id_prestador']) ? $_COOKIE['id_prestador'] : (isset($_COOKIE['id']) ? $_COOKIE['id'] : '0');
$_pwa_idCliente = isset($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : (isset($_COOKIE['codcliente']) ? $_COOKIE['codcliente'] : '0');
$_pwa_ehPrestador = (isset($_COOKIE['eh_prestador']) && $_COOKIE['eh_prestador'] == '1') || isset($_COOKIE['login']);
$_pwa_ehCliente = (isset($_COOKIE['eh_cliente']) && $_COOKIE['eh_cliente'] == '1') || isset($_COOKIE['codcliente']);
// Modo cliente/prestador (mesma lógica do header e bottom-nav)
include_once(__DIR__ . '/area-modo.php');
?>
<link rel="manifest" href="<?php echo $baseUrl; ?>/manifest.json">
<meta name="theme-color" content="#1a2332">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Pediu Serviço">
<link rel="apple-touch-icon" href="<?php echo $baseUrl; ?>/icons/icon-192x192.png">
<link rel="apple-touch-icon" sizes="512x512" href="<?php echo $baseUrl; ?>/icons/icon-512x512.png">
<style>
/* iOS splash screen background to prevent white flash */
html, body { background: #1a2332; }
/* iOS PWA splash overlay - covers screen until content loads */
#pwa-splash{position:fixed;top:0;left:0;width:100%;height:100%;background:#1a2332;display:flex;align-items:center;justify-content:center;z-index:999999;transition:opacity .3s}
#pwa-splash img{width:80px;height:80px;border-radius:16px}
#pwa-splash span{color:#00d4ff;font-size:18px;font-weight:700;margin-top:12px;font-family:Arial,sans-serif}
</style>
<script>
// Splash screen: remove after page fully loads
window.addEventListener('load',function(){var s=document.getElementById('pwa-splash');if(s){s.style.opacity='0';setTimeout(function(){s.remove()},350);}});
</script>
<!-- PWA Install Banner DESATIVADO (agora usa app Android nativo)
<style>
#pwa-install-banner{display:none;position:fixed;bottom:70px;left:10px;right:10px;background:linear-gradient(145deg,#1a2332,#2d4a6b);padding:15px 20px;box-shadow:0 4px 20px rgba(0,0,0,.5);z-index:99999;border:2px solid #00d4ff;border-radius:12px}
#pwa-install-banner .pwa-content{display:flex;align-items:center;justify-content:space-between;max-width:600px;margin:0 auto}
#pwa-install-banner .pwa-text{color:white;font-size:14px;flex:1}
#pwa-install-banner .pwa-text strong{color:#00d4ff;display:block;margin-bottom:4px}
#pwa-install-banner .pwa-buttons{display:flex;gap:8px}
#pwa-install-banner button{padding:10px 16px;border-radius:8px;font-weight:bold;cursor:pointer;border:none;font-size:13px}
#pwa-install-btn{background:linear-gradient(145deg,#00d4ff,#00f0ff);color:#1a2332}
#pwa-dismiss-btn{background:transparent;color:#00d4ff;border:1px solid #00d4ff!important}
</style>
-->

<script src="<?php echo $baseUrl; ?>/push-config.js"></script>
<script>
let deferredPrompt;
const pwaBaseUrl = '<?php echo $baseUrl; ?>';

// Registra Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register(pwaBaseUrl + '/sw.js')
            .then(function(reg) { console.log('SW registrado:', reg.scope); })
            .catch(function(err) { console.log('SW falhou:', err); });
    });
}

// Cria splash screen para PWA standalone (evita tela branca no iOS)
if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
    var sp = document.createElement('div');
    sp.id = 'pwa-splash';
    sp.style.flexDirection = 'column';
    sp.innerHTML = '<img src="' + pwaBaseUrl + '/icons/icon-192x192.png" alt=""><span>Pediu Serviço</span>';
    document.body.insertBefore(sp, document.body.firstChild);
}

// PWA Install Banner DESATIVADO - usa app Android nativo
// Cria o banner de instalação no body via JS
/*
function criarBannerPWA() {
    if (document.getElementById('pwa-install-banner')) return;
    var b = document.createElement('div');
    b.id = 'pwa-install-banner';
    b.innerHTML = '<div class="pwa-content"><div class="pwa-text"><strong>Instalar Pediu Servico</strong>Adicione a tela inicial para acesso rapido</div><div class="pwa-buttons"><button id="pwa-dismiss-btn">Depois</button><button id="pwa-install-btn">Instalar</button></div></div>';
    document.body.appendChild(b);

    document.getElementById('pwa-install-btn').addEventListener('click', async function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            var result = await deferredPrompt.userChoice;
            if (result.outcome === 'accepted') localStorage.setItem('pwa-installed','true');
            deferredPrompt = null;
            b.style.display = 'none';
        } else {
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            if (isIOS) {
                alert('Para instalar no iPhone/iPad:\n\n1. Toque no botao Compartilhar (quadrado com seta)\n2. Toque em "Adicionar a Tela de Inicio"\n3. Toque em "Adicionar"');
            }
        }
    });

    document.getElementById('pwa-dismiss-btn').addEventListener('click', function() {
        localStorage.setItem('pwa-dismissed', Date.now().toString());
        b.style.display = 'none';
    });
}

function mostrarBannerPWA() {
    var dismissed = localStorage.getItem('pwa-dismissed');
    var installed = localStorage.getItem('pwa-installed');
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    if (isStandalone || installed) return false;
    if (dismissed && (Date.now() - parseInt(dismissed)) < 14400000) return false;
    return true;
}

// Captura o evento de instalação
window.addEventListener('beforeinstallprompt', function(e) {
    console.log('beforeinstallprompt disparado');
    e.preventDefault();
    deferredPrompt = e;
    if (mostrarBannerPWA()) {
        criarBannerPWA();
        document.getElementById('pwa-install-banner').style.display = 'block';
    }
});

// Fallback: mostra banner após 3s se beforeinstallprompt não disparou (iOS + Android fallback)
setTimeout(function() {
    var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    if (!deferredPrompt && !isStandalone && mostrarBannerPWA()) {
        criarBannerPWA();
        if (isIOS) {
            document.getElementById('pwa-install-btn').textContent = 'Como instalar';
        } else {
            document.getElementById('pwa-install-btn').textContent = 'Instalar App';
            document.getElementById('pwa-install-btn').addEventListener('click', function() {
                alert('Para instalar:\n\n1. Toque no menu do navegador (⋮)\n2. Toque em "Instalar app" ou "Adicionar à tela inicial"');
            }, {once: true});
        }
        document.getElementById('pwa-install-banner').style.display = 'block';
    }
}, 1500);

window.addEventListener('appinstalled', function() {
    localStorage.setItem('pwa-installed','true');
    var b = document.getElementById('pwa-install-banner');
    if (b) b.style.display = 'none';
});
*/

// Push via navegador DESATIVADO - usa app Android nativo
/*
function initPushForPrestador(prestadorId) {
    console.log('initPushForPrestador chamado, id:', prestadorId, 'PushHelper:', !!window.PushHelper);
    if (window.PushHelper && prestadorId) {
        PushHelper.init(prestadorId, 'prestador').then(function(r) {
            console.log('Push init prestador resultado:', JSON.stringify(r));
        }).catch(function(e) {
            console.error('Push init prestador ERRO:', e);
        });
    } else {
        console.warn('PushHelper não disponível ou prestadorId vazio');
    }
}

function initPushForCliente(clienteId) {
    console.log('initPushForCliente chamado, id:', clienteId, 'PushHelper:', !!window.PushHelper);
    if (window.PushHelper && clienteId) {
        PushHelper.init(clienteId, 'cliente').then(function(r) {
            console.log('Push init cliente resultado:', JSON.stringify(r));
        }).catch(function(e) {
            console.error('Push init cliente ERRO:', e);
        });
    } else {
        console.warn('PushHelper não disponível ou clienteId vazio');
    }
}

// Inscrição push automática ao abrir qualquer página
window.addEventListener('load', function() {
    var prestadorId = '<?php echo $_pwa_idPrestador; ?>';
    var clienteId = '<?php echo $_pwa_idCliente; ?>';
    var ehPrestador = <?php echo $_pwa_ehPrestador ? 'true' : 'false'; ?>;
    var ehCliente = <?php echo $_pwa_ehCliente ? 'true' : 'false'; ?>;

    console.log('PWA Push: prestador=' + prestadorId + ' ehPrestador=' + ehPrestador + ' cliente=' + clienteId + ' ehCliente=' + ehCliente);
    console.log('PWA Push: Notification.permission=' + (window.Notification ? Notification.permission : 'N/A'));
    console.log('PWA Push: PushHelper=' + !!window.PushHelper + ' SW=' + ('serviceWorker' in navigator));

    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        console.warn('PWA Push: Navegador não suporta SW ou Notification');
        return;
    }

    function inscreverPush() {
        console.log('PWA Push: Tentando inscrever...');
        if (ehPrestador && prestadorId !== '0') initPushForPrestador(prestadorId);
        if (ehCliente && clienteId !== '0') initPushForCliente(clienteId);
    }

    if (Notification.permission === 'default') {
        // Pede permissão após 1.5s
        setTimeout(function() {
            console.log('PWA Push: Pedindo permissão...');
            Notification.requestPermission().then(function(permission) {
                console.log('PWA Push: Permissão resultado:', permission);
                if (permission === 'granted' && (ehPrestador || ehCliente)) {
                    // Espera SW ficar pronto antes de inscrever
                    navigator.serviceWorker.ready.then(function() {
                        console.log('PWA Push: SW pronto, inscrevendo...');
                        inscreverPush();
                    });
                }
            });
        }, 1500);
    } else if (Notification.permission === 'granted' && (ehPrestador || ehCliente)) {
        // Já tem permissão - espera SW ficar pronto
        navigator.serviceWorker.ready.then(function() {
            console.log('PWA Push: SW pronto (já tinha permissão), inscrevendo...');
            inscreverPush();
        });
    }
});
*/

// Injeta bottom-nav em todas as páginas
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.bottom-nav')) return;
    var currentPage = window.location.pathname.split('/').pop() || 'buscar.php';
    
    var nav = document.createElement('nav');
    nav.className = 'bottom-nav';
<?php if ($modoArea === 'prestador'): ?>
    // MODO PRESTADOR
    nav.innerHTML = '<a href="meus-orcamentos.php" class="nav-item' + (currentPage=='meus-orcamentos.php' ? ' active' : '') + '"><span class="nav-icon">🔧</span><span class="nav-label">Meus Serviços</span></a>'
        + '<a href="minhasmoedas.php" class="nav-item' + (currentPage=='minhasmoedas.php' ? ' active' : '') + '"><span class="nav-icon">🪙</span><span class="nav-label">Minhas Moedas</span></a>'
        + '<a href="dados-pessoais.php" class="nav-item"><span class="nav-icon">👤</span><span class="nav-label">Conta</span></a>';
<?php else: ?>
    // MODO CLIENTE (padrão) — sem nada de prestador
    nav.innerHTML = '<a href="buscar.php" class="nav-item' + (currentPage=='buscar.php' ? ' active' : '') + '"><span class="nav-icon">🔍</span><span class="nav-label">Buscar Serviço</span></a>'
        + '<a href="meus-orcamentos-cli.php" class="nav-item' + (currentPage=='meus-orcamentos-cli.php' ? ' active' : '') + '"><span class="nav-icon">📋</span><span class="nav-label">Meus Pedidos</span></a>'
        + '<a href="dados-pessoais.php" class="nav-item"><span class="nav-icon">👤</span><span class="nav-label">Conta</span></a>';
<?php endif; ?>
    document.body.appendChild(nav);
    
    // Adiciona padding-bottom ao body para não cobrir conteúdo
    document.body.style.paddingBottom = '65px';
});

// Badge de mensagens não lidas - polling a cada 30s
(function() {
    var chatUserId = '<?php echo $_pwa_ehPrestador ? $_pwa_idPrestador : $_pwa_idCliente; ?>';
    if (!chatUserId || chatUserId === '0') return;

    function checkUnreadMessages() {
        fetch(pwaBaseUrl + '/api/get_unread_messages.php?user_id=' + encodeURIComponent(chatUserId))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var count = data.count || 0;
                // Atualiza badge no título da página
                var baseTitle = document.title.replace(/^\(\d+\)\s*/, '');
                document.title = count > 0 ? '(' + count + ') ' + baseTitle : baseTitle;
                // Atualiza badges de mensagem nos nav-items (Minhas Buscas)
                document.querySelectorAll('.bottom-nav .nav-item').forEach(function(item) {
                    var existingMsgBadge = item.querySelector('.msg-badge');
                    if (existingMsgBadge) existingMsgBadge.remove();
                    var href = item.getAttribute('href') || '';
                    if (count > 0 && (href.indexOf('meus-orcamentos-cli') >= 0 || href.indexOf('meus-orcamentos.php') >= 0)) {
                        var badge = document.createElement('span');
                        badge.className = 'msg-badge';
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.cssText = 'position:absolute;top:-2px;right:50%;transform:translateX(20px);background:#ff4444;color:#fff;font-size:9px;font-weight:700;min-width:16px;height:16px;line-height:16px;border-radius:8px;text-align:center;padding:0 3px;';
                        item.style.position = 'relative';
                        item.appendChild(badge);
                    }
                });
            })
            .catch(function() {});
    }
    checkUnreadMessages();
    setInterval(checkUnreadMessages, 30000);
})();
</script>
<style>
.bottom-nav{position:fixed;bottom:0;left:0;right:0;background:rgba(26,35,50,.97);border-top:2px solid rgba(0,212,255,.3);display:flex;z-index:9998;backdrop-filter:blur(10px);padding:4px 0}
.nav-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:6px 4px;text-decoration:none;color:rgba(255,255,255,.45);font-size:10px;font-weight:600;transition:.2s;gap:2px}
.nav-item.active{color:#00d4ff}
.nav-item .nav-icon{font-size:20px;line-height:1}
.nav-item:hover{color:#00d4ff}
.nav-item .nav-label{white-space:nowrap}
</style>
