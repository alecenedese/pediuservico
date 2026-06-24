<?php
// Auto-logout para sessoes antigas (login por celular) sem o novo flag
@include_once(__DIR__ . '/auth-check-unificado.php');

// Header unificado para todas as páginas
$baseUrl = '/pediuservico';
$logadoNovo  = isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1';
$logadoAntigo = isset($_COOKIE['login']) && !empty($_COOKIE['login']); // prestador antigo (login.php)
$logado = $logadoNovo || $logadoAntigo;
$nomeUsuario = isset($_COOKIE['nome_usuario']) ? $_COOKIE['nome_usuario'] : (isset($_COOKIE['nome']) ? $_COOKIE['nome'] : '');
$primeiroNome = !empty($nomeUsuario) ? explode(' ', trim($nomeUsuario))[0] : '';
$paginaAtual = basename($_SERVER['PHP_SELF']);
$ehBuscar = ($paginaAtual == 'buscar.php');
$voltarUrl = isset($voltarUrl) ? $voltarUrl : '';

// Modo (cliente/prestador) — estilo Mercado Livre
include_once(__DIR__ . '/area-modo.php');
?>
<style>
.app-header{position:fixed;top:0;left:0;right:0;display:flex;align-items:center;justify-content:space-between;padding:6px 12px;background:#fff;border-bottom:1px solid #e5e7eb;z-index:10000;min-height:44px;box-sizing:border-box;color-scheme:light;gap:8px}
.app-header .hdr-left{display:flex;align-items:center;gap:8px;min-width:0}
.app-header .btn-back{background:rgba(14,165,233,.1);border:1px solid rgba(14,165,233,.3);color:#0ea5e9;font-size:13px;cursor:pointer;padding:6px 10px;text-decoration:none;display:flex;align-items:center;border-radius:8px;font-weight:600;gap:4px}
.app-header .hdr-logo img{height:28px;display:block}
.app-header .hdr-right{display:flex;align-items:center;gap:6px}
.app-header .btn-user{display:flex;align-items:center;gap:5px;background:rgba(0,212,255,.1);border:1px solid rgba(0,212,255,.3);color:#0ea5e9;padding:5px 10px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none}
.app-header .btn-user:hover{background:rgba(0,212,255,.2)}
.app-header .user-dot{width:22px;height:22px;border-radius:50%;background:linear-gradient(145deg,#00d4ff,#0ea5e9);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:11px}
.app-header-spacer{height:44px;flex-shrink:0}
/* Alternador de modo (só para prestadores) */
.modo-switch{display:flex;background:#eef2f7;border-radius:20px;padding:2px;gap:2px;flex-shrink:0}
.modo-switch .ms-btn{border:none;background:transparent;color:#64748b;font-size:11px;font-weight:700;padding:5px 11px;border-radius:18px;cursor:pointer;font-family:inherit;white-space:nowrap}
.modo-switch .ms-btn.on{background:#0ea5e9;color:#fff;box-shadow:0 1px 4px rgba(14,165,233,.4)}
/* Dropdown do usuário */
.user-menu-dropdown{position:fixed;top:50px;right:10px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);min-width:230px;z-index:10002;overflow:hidden;display:none}
.user-menu-dropdown.active{display:block}
.user-menu-dropdown a{display:flex;align-items:center;gap:10px;padding:13px 16px;color:#1a2332;text-decoration:none;font-size:14px;font-weight:500;border-bottom:1px solid #f0f0f0;transition:background .2s}
.user-menu-dropdown a:last-child{border-bottom:none}
.user-menu-dropdown a:hover{background:#f0f9ff;color:#0ea5e9}
.user-menu-dropdown a.sair{color:#dc3545}
.user-menu-dropdown a.sair:hover{background:#fef2f2}
.user-menu-dropdown a.destaque{color:#0ea5e9;font-weight:700;background:#f0f9ff}
.user-menu-dropdown .menu-icon{font-size:16px;width:20px;text-align:center}
.user-menu-dropdown .menu-sep{padding:7px 16px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;background:#f8fafc;border-bottom:1px solid #f0f0f0}
.user-menu-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:transparent;z-index:10001;display:none}
.user-menu-overlay.active{display:block}
/* Overrides para VENCER o global-font-size.css (!important) e evitar sobreposição no header */
.app-header{flex-wrap:nowrap!important;padding:6px 8px!important;gap:5px!important}
.app-header .hdr-left{gap:5px!important;flex-shrink:1;min-width:0}
.app-header .btn-back{font-size:15px!important;padding:4px 7px!important;flex-shrink:0;line-height:1!important}
.app-header .hdr-logo img{height:22px!important}
.app-header .hdr-logo{flex-shrink:1;min-width:0;overflow:hidden}
.modo-switch{flex-shrink:0;padding:2px!important}
.modo-switch .ms-btn{font-size:11px!important;padding:5px 9px!important;line-height:1!important}
.app-header .btn-user,.app-header .btn-user span{font-size:11px!important}
.app-header .btn-user{padding:4px 8px!important;max-width:86px;overflow:hidden;white-space:nowrap;flex-shrink:1;line-height:1!important;gap:4px!important}
.app-header .btn-user .user-dot{flex-shrink:0;width:20px!important;height:20px!important;font-size:10px!important}
@media (prefers-color-scheme: dark){.app-header{background:#fff!important;color-scheme:light!important}}
</style>
<div class="app-header" data-user-id="<?php echo isset($_COOKIE['id_prestador']) ? htmlspecialchars($_COOKIE['id_prestador']) : ''; ?>" data-codcadastro="<?php echo isset($_COOKIE['id_prestador']) ? htmlspecialchars($_COOKIE['id_prestador']) : ''; ?>">
    <div class="hdr-left">
        <?php if(!$ehBuscar): ?>
            <a href="<?php echo $voltarUrl !== '' ? htmlspecialchars($voltarUrl) : 'javascript:history.back()'; ?>" class="btn-back">←</a>
        <?php endif; ?>
        <a href="<?php echo $modoArea === 'prestador' ? 'meus-orcamentos.php' : 'buscar.php'; ?>" class="hdr-logo"><img src="<?php echo $baseUrl; ?>/logonova.jpg" alt="Pediu Servico"></a>
    </div>

    <?php if($logado && $ehPrestadorReal): ?>
    <!-- Alternador de modo: só aparece para quem JÁ é prestador -->
    <div class="modo-switch">
        <button class="ms-btn <?php echo $modoArea === 'cliente' ? 'on' : ''; ?>" onclick="setModoArea('cliente')">Cliente</button>
        <button class="ms-btn <?php echo $modoArea === 'prestador' ? 'on' : ''; ?>" onclick="setModoArea('prestador')">Prestador</button>
    </div>
    <?php endif; ?>

    <div class="hdr-right">
        <?php if($logado): ?>
            <a href="javascript:void(0)" class="btn-user" id="btnUserMenu">
                <span class="user-dot"><?php echo strtoupper(substr($primeiroNome,0,1)); ?></span>
                <?php echo $primeiroNome; ?>
                <span style="font-size:9px;margin-left:2px;">▼</span>
            </a>
        <?php else: ?>
            <a href="login-unificado.php" class="btn-user">👤 Entrar</a>
        <?php endif; ?>
    </div>
</div>
<?php if($logado): ?>
<div class="user-menu-overlay" id="userMenuOverlay"></div>
<div class="user-menu-dropdown" id="userMenuDropdown">
    <a href="dados-pessoais.php"><span class="menu-icon">👤</span> Dados pessoais</a>

    <?php if($ehPrestadorReal && $modoArea === 'prestador'): ?>
        <!-- Opções do prestador: só no MODO PRESTADOR -->
        <div class="menu-sep">Prestador</div>
        <a href="minhas-categorias.php"><span class="menu-icon">📂</span> Categorias</a>
        <a href="minhasmoedas.php"><span class="menu-icon">🪙</span> Minhas moedas</a>
        <a href="meus-enderecos.php"><span class="menu-icon">📍</span> Endereço</a>
        <a href="verificacao.php"><span class="menu-icon">✅</span> Verificação</a>
        <a href="javascript:void(0)" onclick="setModoArea('cliente')" class="destaque"><span class="menu-icon">🔄</span> Mudar para Cliente</a>
    <?php elseif($ehPrestadorReal && $modoArea === 'cliente'): ?>
        <!-- Prestador navegando como cliente: oferece voltar ao modo prestador -->
        <a href="javascript:void(0)" onclick="setModoArea('prestador')" class="destaque"><span class="menu-icon">🔄</span> Mudar para Prestador</a>
    <?php else: ?>
        <!-- Cliente comum: convite DISCRETO para virar prestador -->
        <a href="tornar-prestador.php" class="destaque"><span class="menu-icon">💼</span> Quero oferecer serviços</a>
    <?php endif; ?>

    <a href="sair.php" class="sair"><span class="menu-icon">🚪</span> Sair</a>
</div>
<?php endif; ?>
<div class="app-header-spacer"></div>
<script>
(function(){
    function toggleUserMenu(e){
        if(e){ e.preventDefault(); e.stopPropagation(); }
        var dd = document.getElementById('userMenuDropdown');
        var ov = document.getElementById('userMenuOverlay');
        if(!dd) return;
        var isActive = dd.classList.toggle('active');
        if(ov) ov.classList.toggle('active', isActive);
    }
    function closeUserMenu(){
        var dd = document.getElementById('userMenuDropdown');
        var ov = document.getElementById('userMenuOverlay');
        if(dd) dd.classList.remove('active');
        if(ov) ov.classList.remove('active');
    }
    window.toggleUserMenu = toggleUserMenu;
    window.closeUserMenu = closeUserMenu;

    // Alterna o modo (cliente/prestador) e leva para a home do modo
    window.setModoArea = function(modo){
        document.cookie = 'modo_area=' + modo + ';path=/;max-age=' + (365*24*3600);
        window.location.href = (modo === 'prestador') ? 'meus-orcamentos.php' : 'buscar.php';
    };

    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('btnUserMenu');
        if(btn){ btn.addEventListener('click', toggleUserMenu); }
        var ov = document.getElementById('userMenuOverlay');
        if(ov){ ov.addEventListener('click', closeUserMenu); }
    });
    if(document.readyState !== 'loading'){
        var btnNow = document.getElementById('btnUserMenu');
        if(btnNow && !btnNow.dataset.bound){ btnNow.dataset.bound='1'; btnNow.addEventListener('click', toggleUserMenu); }
        var ovNow = document.getElementById('userMenuOverlay');
        if(ovNow && !ovNow.dataset.bound){ ovNow.dataset.bound='1'; ovNow.addEventListener('click', closeUserMenu); }
    }
})();
</script>
