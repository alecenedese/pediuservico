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
$voltarUrl = isset($voltarUrl) ? $voltarUrl : ''; // página pode definir uma URL de voltar customizada
$ehPrestador = isset($_COOKIE['eh_prestador']) && $_COOKIE['eh_prestador'] == '1';
$ehPrestadorLogin = isset($_COOKIE['login']) && !empty($_COOKIE['login']);
$ehPrestadorReal = $ehPrestador || $ehPrestadorLogin || (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador']));
$linkCadastro = $ehPrestador ? 'edicao.php' : 'editar-cadastro-cliente.php';
?>
<style>
.app-header{position:fixed;top:0;left:0;right:0;display:flex;align-items:center;justify-content:space-between;padding:6px 12px;background:#fff;border-bottom:1px solid #e5e7eb;z-index:10000;min-height:44px;box-sizing:border-box;color-scheme:light}
.app-header .hdr-left{display:flex;align-items:center;gap:8px}
.app-header .btn-back{background:rgba(14,165,233,.1);border:1px solid rgba(14,165,233,.3);color:#0ea5e9;font-size:13px;cursor:pointer;padding:6px 12px;text-decoration:none;display:flex;align-items:center;border-radius:8px;font-weight:600;gap:4px}
.app-header .hdr-logo img{height:28px;display:block}
.app-header .hdr-right{display:flex;align-items:center;gap:6px}
.app-header .btn-user{display:flex;align-items:center;gap:5px;background:rgba(0,212,255,.1);border:1px solid rgba(0,212,255,.3);color:#0ea5e9;padding:5px 10px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none}
.app-header .btn-user:hover{background:rgba(0,212,255,.2)}
.app-header .user-dot{width:22px;height:22px;border-radius:50%;background:linear-gradient(145deg,#00d4ff,#0ea5e9);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:11px}
.app-header-spacer{height:44px;flex-shrink:0}
/* Item 15: Dropdown do usuário (posicionado no nível raiz para evitar conflito de z-index) */
.user-menu-dropdown{position:fixed;top:50px;right:10px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);min-width:210px;z-index:10002;overflow:hidden;display:none}
.user-menu-dropdown.active{display:block}
.user-menu-dropdown a{display:flex;align-items:center;gap:10px;padding:13px 16px;color:#1a2332;text-decoration:none;font-size:14px;font-weight:500;border-bottom:1px solid #f0f0f0;transition:background .2s}
.user-menu-dropdown a:last-child{border-bottom:none}
.user-menu-dropdown a:hover{background:#f0f9ff;color:#0ea5e9}
.user-menu-dropdown a.sair{color:#dc3545}
.user-menu-dropdown a.sair:hover{background:#fef2f2}
.user-menu-dropdown .menu-icon{font-size:16px;width:20px;text-align:center}
.user-menu-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:transparent;z-index:10001;display:none}
.user-menu-overlay.active{display:block}
@media (prefers-color-scheme: dark){.app-header{background:#fff!important;color-scheme:light!important}}
</style>
<div class="app-header" data-user-id="<?php echo isset($_COOKIE['id_prestador']) ? htmlspecialchars($_COOKIE['id_prestador']) : ''; ?>" data-codcadastro="<?php echo isset($_COOKIE['id_prestador']) ? htmlspecialchars($_COOKIE['id_prestador']) : ''; ?>">
    <div class="hdr-left">
        <?php if(!$ehBuscar): ?>
            <a href="<?php echo $voltarUrl !== '' ? htmlspecialchars($voltarUrl) : 'javascript:history.back()'; ?>" class="btn-back">← Voltar</a>
        <?php endif; ?>
        <a href="buscar.php" class="hdr-logo"><img src="<?php echo $baseUrl; ?>/logonova.jpg" alt="Pediu Servico"></a>
    </div>
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
<!-- Dropdown e overlay no nível raiz (fora do header) para evitar conflito de empilhamento -->
<div class="user-menu-overlay" id="userMenuOverlay"></div>
<div class="user-menu-dropdown" id="userMenuDropdown">
    <a href="dados-pessoais.php"><span class="menu-icon">👤</span> Dados pessoais</a>
    <?php if($ehPrestadorReal): ?>
        <a href="minhas-categorias.php"><span class="menu-icon">📂</span> Categorias</a>
        <a href="minhasmoedas.php"><span class="menu-icon">🪙</span> Minhas moedas</a>
        <a href="meus-enderecos.php"><span class="menu-icon">📍</span> Endereço</a>
        <a href="verificacao.php"><span class="menu-icon">✅</span> Verificação</a>
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
    // Expõe globalmente (compatibilidade)
    window.toggleUserMenu = toggleUserMenu;
    window.closeUserMenu = closeUserMenu;

    // Item 13: consumidor tentando acessar área de prestador
    window.checkPrestador = function(e){
        if(e){ e.preventDefault(); }
        if(confirm('Essa área é exclusiva para prestadores.\n\nDeseja se cadastrar como prestador?')){
            window.location.href = 'tornar-prestador.php';
        }
        return false;
    };

    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('btnUserMenu');
        if(btn){ btn.addEventListener('click', toggleUserMenu); }
        var ov = document.getElementById('userMenuOverlay');
        if(ov){ ov.addEventListener('click', closeUserMenu); }
    });
    // Se o DOM já estiver pronto (header incluído após carregamento), liga imediatamente
    if(document.readyState !== 'loading'){
        var btnNow = document.getElementById('btnUserMenu');
        if(btnNow && !btnNow.dataset.bound){ btnNow.dataset.bound='1'; btnNow.addEventListener('click', toggleUserMenu); }
        var ovNow = document.getElementById('userMenuOverlay');
        if(ovNow && !ovNow.dataset.bound){ ovNow.dataset.bound='1'; ovNow.addEventListener('click', closeUserMenu); }
    }
})();
</script>
