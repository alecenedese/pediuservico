<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Debug</title>
    <?php include('pwa-include.php'); ?>
    <style>
        body { background: #1a2332; color: white; font-family: Arial; padding: 20px; }
        .check { padding: 10px; margin: 5px 0; border-radius: 8px; }
        .ok { background: rgba(16,185,129,.2); border: 1px solid #10b981; }
        .fail { background: rgba(239,68,68,.2); border: 1px solid #ef4444; }
        .warn { background: rgba(245,158,11,.2); border: 1px solid #f59e0b; }
        h2 { color: #00d4ff; }
        pre { background: #0d1117; padding: 10px; border-radius: 8px; overflow-x: auto; font-size: 12px; }
        #log { background: #0d1117; padding: 15px; border-radius: 8px; margin-top: 20px; max-height: 300px; overflow-y: auto; }
        .btn { background: #00d4ff; color: #1a2332; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <h1>PWA Debug</h1>
    
    <h2>Verificacoes do Servidor</h2>
    
    <?php
    $baseUrl = '/pediuservico';
    $checks = [];
    
    // Verifica manifest.json
    $manifestPath = __DIR__ . '/manifest.json';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if ($manifest) {
            echo '<div class="check ok">✅ manifest.json existe e é válido</div>';
            echo '<pre>' . json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        } else {
            echo '<div class="check fail">❌ manifest.json existe mas JSON inválido</div>';
        }
    } else {
        echo '<div class="check fail">❌ manifest.json NÃO existe</div>';
    }
    
    // Verifica sw.js
    $swPath = __DIR__ . '/sw.js';
    if (file_exists($swPath)) {
        echo '<div class="check ok">✅ sw.js existe (' . filesize($swPath) . ' bytes)</div>';
    } else {
        echo '<div class="check fail">❌ sw.js NÃO existe</div>';
    }
    
    // Verifica ícones
    $icons = ['icon-192x192.png', 'icon-512x512.png'];
    foreach ($icons as $icon) {
        $iconPath = __DIR__ . '/icons/' . $icon;
        if (file_exists($iconPath)) {
            $size = filesize($iconPath);
            if ($size > 100) {
                echo '<div class="check ok">✅ ' . $icon . ' existe (' . $size . ' bytes)</div>';
            } else {
                echo '<div class="check warn">⚠️ ' . $icon . ' existe mas muito pequeno (' . $size . ' bytes) - pode ser placeholder</div>';
            }
        } else {
            echo '<div class="check fail">❌ ' . $icon . ' NÃO existe - <a href="icons/generate-icons.php" style="color:#00d4ff">Gerar agora</a></div>';
        }
    }
    
    // Verifica HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    if ($isHttps) {
        echo '<div class="check ok">✅ HTTPS ativo</div>';
    } else {
        echo '<div class="check fail">❌ HTTPS NÃO ativo - PWA requer HTTPS</div>';
    }
    ?>
    
    <h2>Verificacoes do Navegador</h2>
    <div id="browser-checks"></div>
    
    <h2>Acoes</h2>
    <button class="btn" onclick="testInstall()">Testar Instalacao</button>
    <button class="btn" onclick="clearPwaData()">Limpar Dados PWA</button>
    <button class="btn" onclick="location.href='buscar.php'">Voltar ao App</button>
    
    <h2>Console Log</h2>
    <div id="log"></div>
    
    <script>
    function log(msg, type = 'info') {
        const logDiv = document.getElementById('log');
        const time = new Date().toLocaleTimeString();
        const color = type === 'ok' ? '#10b981' : type === 'fail' ? '#ef4444' : '#00d4ff';
        logDiv.innerHTML += `<div style="color:${color}">[${time}] ${msg}</div>`;
        logDiv.scrollTop = logDiv.scrollHeight;
        console.log(msg);
    }
    
    function addCheck(id, ok, msg) {
        const div = document.getElementById('browser-checks');
        const cls = ok ? 'ok' : 'fail';
        const icon = ok ? '✅' : '❌';
        div.innerHTML += `<div class="check ${cls}">${icon} ${msg}</div>`;
    }
    
    // Verificações do navegador
    addCheck('sw', 'serviceWorker' in navigator, 'Service Worker suportado');
    addCheck('pm', 'PushManager' in window, 'Push Manager suportado');
    addCheck('notif', 'Notification' in window, 'Notifications suportado');
    
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    addCheck('standalone', !isStandalone, isStandalone ? 'Rodando como PWA instalado' : 'Rodando no navegador (pode instalar)');
    
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    addCheck('platform', true, 'Plataforma: ' + (isIOS ? 'iOS' : isAndroid ? 'Android' : 'Desktop'));
    
    // Verifica localStorage
    const dismissed = localStorage.getItem('pwa-dismissed');
    const installed = localStorage.getItem('pwa-installed');
    addCheck('storage', true, `localStorage: dismissed=${dismissed}, installed=${installed}`);
    
    // Monitora eventos
    log('Aguardando eventos PWA...');
    
    window.addEventListener('beforeinstallprompt', (e) => {
        log('✅ beforeinstallprompt DISPARADO!', 'ok');
        window.deferredPromptDebug = e;
    });
    
    window.addEventListener('appinstalled', () => {
        log('✅ App instalado com sucesso!', 'ok');
    });
    
    // Verifica SW
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(regs => {
            if (regs.length > 0) {
                log('Service Workers registrados: ' + regs.length, 'ok');
                regs.forEach(r => log('  - Scope: ' + r.scope));
            } else {
                log('Nenhum Service Worker registrado ainda', 'fail');
            }
        });
    }
    
    // Testa fetch do manifest
    fetch('<?php echo $baseUrl; ?>/manifest.json')
        .then(r => {
            if (r.ok) {
                log('✅ manifest.json acessível via fetch', 'ok');
                return r.json();
            } else {
                log('❌ manifest.json retornou ' + r.status, 'fail');
            }
        })
        .then(data => {
            if (data) log('  start_url: ' + data.start_url);
        })
        .catch(e => log('❌ Erro ao buscar manifest: ' + e, 'fail'));
    
    function testInstall() {
        if (window.deferredPromptDebug) {
            log('Chamando prompt de instalação...');
            window.deferredPromptDebug.prompt();
        } else if (window.deferredPrompt) {
            log('Chamando prompt de instalação (global)...');
            window.deferredPrompt.prompt();
        } else {
            log('❌ Nenhum prompt disponível. O navegador não disparou beforeinstallprompt.', 'fail');
            log('Possíveis causas:', 'info');
            log('  - App já instalado', 'info');
            log('  - Ícones PNG inválidos ou ausentes', 'info');
            log('  - manifest.json com erro', 'info');
            log('  - Service Worker não registrado', 'info');
            log('  - Navegador não suporta (Safari/iOS)', 'info');
        }
    }
    
    function clearPwaData() {
        localStorage.removeItem('pwa-dismissed');
        localStorage.removeItem('pwa-installed');
        log('Dados PWA limpos do localStorage', 'ok');
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(regs => {
                regs.forEach(r => r.unregister());
                log('Service Workers removidos', 'ok');
            });
        }
        
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => caches.delete(name));
                log('Caches limpos', 'ok');
            });
        }
        
        setTimeout(() => {
            log('Recarregando página em 2s...', 'info');
            setTimeout(() => location.reload(), 2000);
        }, 500);
    }
    </script>
</body>
</html>
