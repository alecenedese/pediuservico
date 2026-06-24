<?php
// Instalador web-push para servidores sem SSH (cPanel)
// Funciona 100% pelo navegador - baixa e extrai tudo via PHP

set_time_limit(600);
ini_set('memory_limit', '256M');
header('Content-Type: text/html; charset=utf-8');

$dir = __DIR__;
$vendorDir = $dir . '/vendor';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Instalador Web Push</title>
<style>
body{background:#1a2332;color:white;font-family:Arial;padding:20px}
h2{color:#00d4ff}
.ok{color:#10b981}.fail{color:#ef4444}.warn{color:#f59e0b}
pre{background:#0d1117;padding:15px;border-radius:8px;overflow-x:auto;font-size:12px;max-height:400px;overflow-y:auto}
.btn{display:inline-block;background:#00d4ff;color:#1a2332;padding:12px 24px;border-radius:8px;font-weight:bold;text-decoration:none;margin:5px}
.btn:hover{background:#00f0ff}
</style>
</head>
<body>
<h2>Instalador Web Push - Pediu Servico</h2>
<?php

// Verifica se já está instalado
if (file_exists($vendorDir . '/autoload.php') && is_dir($vendorDir . '/minishlink/web-push')) {
    echo "<p class='ok'>A biblioteca web-push ja esta instalada!</p>";
    
    // Verifica se chaves VAPID existem
    if (!file_exists($dir . '/api/vapid-keys.php')) {
        echo "<p class='warn'>Chaves VAPID nao encontradas. Gerando...</p>";
        require_once($vendorDir . '/autoload.php');
        gerarChavesVapid($dir);
    } else {
        echo "<p class='ok'>Chaves VAPID encontradas.</p>";
    }
    
    echo "<p><a class='btn' href='api/test-push.php'>Testar Push</a></p>";
    echo "</body></html>";
    exit;
}

// Verificações do ambiente
echo "<h3>Verificacoes do ambiente</h3>";

$phpVersion = phpversion();
echo "<p>" . (version_compare($phpVersion, '7.4', '>=') ? "<span class='ok'>✅</span>" : "<span class='fail'>❌</span>") . " PHP $phpVersion (minimo 7.4)</p>";

$gmpOk = extension_loaded('gmp') || extension_loaded('bcmath');
echo "<p>" . ($gmpOk ? "<span class='ok'>✅</span>" : "<span class='warn'>⚠️</span>") . " GMP/BCMath: " . (extension_loaded('gmp') ? 'GMP' : (extension_loaded('bcmath') ? 'BCMath' : 'Nenhum')) . "</p>";

$curlOk = extension_loaded('curl');
echo "<p>" . ($curlOk ? "<span class='ok'>✅</span>" : "<span class='fail'>❌</span>") . " cURL</p>";

$mbOk = extension_loaded('mbstring');
echo "<p>" . ($mbOk ? "<span class='ok'>✅</span>" : "<span class='fail'>❌</span>") . " mbstring</p>";

$opensslOk = extension_loaded('openssl');
echo "<p>" . ($opensslOk ? "<span class='ok'>✅</span>" : "<span class='fail'>❌</span>") . " OpenSSL</p>";

$writeOk = is_writable($dir);
echo "<p>" . ($writeOk ? "<span class='ok'>✅</span>" : "<span class='fail'>❌</span>") . " Pasta gravavel: $dir</p>";

// Tenta usar Composer primeiro (alguns cPanels têm)
if ($step == 0) {
    echo "<h3>Metodo 1: Tentar via Composer</h3>";
    
    $composerPaths = ['/usr/local/bin/composer', '/usr/bin/composer', 'composer'];
    $composerFound = false;
    
    foreach ($composerPaths as $cp) {
        $out = [];
        @exec("$cp --version 2>&1", $out, $ret);
        if ($ret === 0) {
            echo "<p class='ok'>Composer encontrado: $cp</p>";
            echo "<pre>" . implode("\n", $out) . "</pre>";
            
            // Tenta instalar
            echo "<p>Instalando web-push...</p>";
            $out2 = [];
            $home = '/home2/gessomt';
            @exec("export HOME=$home && export COMPOSER_HOME=$home/.composer && cd $dir && $cp require minishlink/web-push 2>&1", $out2, $ret2);
            echo "<pre>" . implode("\n", $out2) . "</pre>";
            
            if ($ret2 === 0 && file_exists($vendorDir . '/autoload.php')) {
                echo "<p class='ok'>Instalado com sucesso via Composer!</p>";
                require_once($vendorDir . '/autoload.php');
                gerarChavesVapid($dir);
                echo "<p><a class='btn' href='api/test-push.php'>Testar Push</a></p>";
                echo "</body></html>";
                exit;
            }
            $composerFound = true;
            break;
        }
    }
    
    if (!$composerFound) {
        echo "<p class='warn'>Composer nao encontrado no servidor.</p>";
    }
    
    // Tenta baixar e instalar composer.phar
    echo "<h3>Metodo 2: Baixar Composer e instalar</h3>";
    
    $composerPhar = $dir . '/composer.phar';
    if (!file_exists($composerPhar)) {
        echo "<p>Baixando composer.phar...</p>";
        
        // Tenta com file_get_contents
        $composerContent = @file_get_contents('https://getcomposer.org/composer-stable.phar');
        
        // Fallback com cURL
        if ($composerContent === false && function_exists('curl_init')) {
            echo "<p>Tentando com cURL...</p>";
            $ch = curl_init('https://getcomposer.org/composer-stable.phar');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $composerContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode != 200) $composerContent = false;
        }
        
        // Fallback: baixa via exec
        if ($composerContent === false) {
            echo "<p>Tentando com wget/curl cli...</p>";
            @exec("cd $dir && curl -sS -o composer.phar https://getcomposer.org/composer-stable.phar 2>&1", $dlOut, $dlRet);
            if ($dlRet !== 0) {
                @exec("cd $dir && wget -q -O composer.phar https://getcomposer.org/composer-stable.phar 2>&1", $dlOut, $dlRet);
            }
        } else {
            file_put_contents($composerPhar, $composerContent);
        }
        
        if (file_exists($composerPhar) && filesize($composerPhar) > 1000) {
            @chmod($composerPhar, 0755);
            echo "<p class='ok'>composer.phar baixado (" . round(filesize($composerPhar)/1024) . " KB)</p>";
        } else {
            echo "<p class='fail'>Nao foi possivel baixar composer.phar</p>";
            @unlink($composerPhar);
        }
    } else {
        echo "<p class='ok'>composer.phar ja existe (" . round(filesize($composerPhar)/1024) . " KB)</p>";
    }
    
    if (file_exists($composerPhar) && filesize($composerPhar) > 1000) {
        echo "<p>Instalando via composer.phar...</p>";
        $out3 = [];
        
        $phpBin = PHP_BINARY ?: 'php';
        $home = dirname($dir);
        putenv("HOME=$home");
        putenv("COMPOSER_HOME=$home/.composer");
        @exec("export HOME=$home && export COMPOSER_HOME=$home/.composer && cd $dir && $phpBin $composerPhar require minishlink/web-push 2>&1", $out3, $ret3);
        echo "<pre>" . implode("\n", $out3) . "</pre>";
        
        if (file_exists($vendorDir . '/autoload.php')) {
            echo "<p class='ok'>Instalado com sucesso!</p>";
            require_once($vendorDir . '/autoload.php');
            gerarChavesVapid($dir);
            echo "<p><a class='btn' href='api/test-push.php'>Testar Push</a></p>";
            echo "</body></html>";
            exit;
        }
    }
    
    // Método 3: cPanel Terminal
    echo "<h3>Metodo 3: Instalar via cPanel Terminal</h3>";
    echo "<p>Se o seu cPanel tem a opcao <strong>'Terminal'</strong>, siga estes passos:</p>";
    echo "<ol>";
    echo "<li>Acesse o cPanel</li>";
    echo "<li>Clique em <strong>'Terminal'</strong> (na secao Avancado)</li>";
    echo "<li>Execute os comandos abaixo:</li>";
    echo "</ol>";
    
    // Descobre o caminho real
    $realPath = realpath($dir);
    echo "<pre style='background:#0d1117;padding:15px;user-select:all'>";
    echo "cd $realPath\n";
    echo "php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\"\n";
    echo "php composer-setup.php\n";
    echo "php composer.phar require minishlink/web-push\n";
    echo "</pre>";
    
    echo "<p>Depois de executar, <a class='btn' href='install-webpush.php'>clique aqui para verificar</a></p>";
    
    // Método 4: Upload manual
    echo "<h3>Metodo 4: Upload manual pelo Gerenciador de Arquivos</h3>";
    echo "<p>Se nenhum metodo acima funcionou, <a class='btn' href='install-webpush.php?step=4'>clique aqui para o metodo de download direto</a></p>";
}

if ($step == 4) {
    // Método 4: Download direto dos pacotes e extração via PHP
    echo "<h3>Download direto dos pacotes</h3>";
    echo "<p>Baixando pacotes diretamente do Packagist...</p>";
    echo "<pre>";
    
    @mkdir($vendorDir, 0755, true);
    
    $packages = [
        'minishlink/web-push' => 'https://github.com/web-push-libs/web-push-php/archive/refs/tags/v8.0.0.zip',
        'web-token/jwt-library' => 'https://github.com/web-token/jwt-library/archive/refs/tags/3.4.7.zip',
    ];
    
    echo "Este metodo pode nao funcionar para todas as dependencias.\n";
    echo "A melhor opcao e usar o Terminal do cPanel.\n\n";
    
    echo "Tentando baixar web-push-php...\n";
    
    $zipUrl = 'https://github.com/web-push-libs/web-push-php/archive/refs/heads/master.zip';
    $zipFile = $dir . '/webpush-temp.zip';
    
    $ctx = stream_context_create(['http' => ['follow_location' => true, 'timeout' => 120, 'user_agent' => 'PHP']]);
    $zipContent = @file_get_contents($zipUrl, false, $ctx);
    
    if ($zipContent !== false) {
        file_put_contents($zipFile, $zipContent);
        echo "Download concluido: " . round(filesize($zipFile)/1024) . " KB\n";
        
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zipFile) === true) {
                $zip->extractTo($dir . '/webpush-temp/');
                $zip->close();
                echo "Extraido com sucesso\n";
                
                echo "\nPorem, este pacote tem muitas dependencias que tambem precisam ser instaladas.\n";
                echo "A forma mais confiavel e usar o Terminal do cPanel.\n";
            }
            @unlink($zipFile);
        } else {
            echo "ZipArchive nao disponivel\n";
        }
    } else {
        echo "Nao foi possivel baixar\n";
    }
    
    echo "</pre>";
    
    echo "<h3>Recomendacao final</h3>";
    echo "<p>Use o <strong>Terminal do cPanel</strong> e copie os comandos:</p>";
    $realPath = realpath($dir);
    echo "<pre style='user-select:all;background:#0d1117;padding:15px'>";
    echo "cd $realPath && php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\" && php composer-setup.php && php composer.phar require minishlink/web-push";
    echo "</pre>";
    echo "<p>E um unico comando - copie e cole no Terminal do cPanel.</p>";
    echo "<p><a class='btn' href='install-webpush.php'>Verificar instalacao</a></p>";
}

function gerarChavesVapid($dir) {
    if (class_exists('Minishlink\WebPush\VAPID')) {
        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        
        $keysContent = "<?php\n// Chaves VAPID geradas em " . date('Y-m-d H:i:s') . "\n";
        $keysContent .= "\$VAPID_PUBLIC_KEY = '" . $keys['publicKey'] . "';\n";
        $keysContent .= "\$VAPID_PRIVATE_KEY = '" . $keys['privateKey'] . "';\n";
        $keysContent .= "\$VAPID_SUBJECT = 'mailto:contato@pediuservico.com.br';\n";
        
        file_put_contents($dir . '/api/vapid-keys.php', $keysContent);
        
        echo "<p class='ok'>Chaves VAPID geradas e salvas!</p>";
        echo "<pre>Public Key: " . $keys['publicKey'] . "\nPrivate Key: " . $keys['privateKey'] . "</pre>";
        echo "<p class='warn'>IMPORTANTE: Apos gerar novas chaves, os prestadores precisam reabrir o app para se reinscrever.</p>";
    }
}
?>
</body>
</html>
