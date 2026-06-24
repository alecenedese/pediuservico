<?php
// Script para gerar ícones PNG placeholder para o PWA
// Execute este script uma vez para criar os ícones

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Gerando icones PWA...</h2>";

// Verifica se GD esta disponivel
if (!function_exists('imagecreatetruecolor')) {
    echo "<p style='color:red'>ERRO: Extensao GD nao esta habilitada no PHP.</p>";
    echo "<p>Tentando metodo alternativo com base64...</p>";
    
    // Metodo alternativo: cria PNG minimo de 1x1 pixel azul e redimensiona
    // Cria um PNG basico via codigo binario
    $sizes = [192, 512];
    foreach ($sizes as $size) {
        // PNG minimo: header + IHDR + IDAT + IEND
        // Vamos criar via base64 de um PNG pre-gerado 1x1
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $filename = __DIR__ . "/icon-{$size}x{$size}.png";
        file_put_contents($filename, $pixel);
        echo "<p>Criado (placeholder minimo): icon-{$size}x{$size}.png</p>";
    }
    echo "<p style='color:orange'>Icones criados como placeholders. Para icones melhores, habilite a extensao GD.</p>";
    exit;
}

$sizes = [192, 512];

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);
    
    // Cores
    $bgColor = imagecolorallocate($img, 26, 35, 50);
    $fgColor = imagecolorallocate($img, 0, 212, 255);
    
    // Fundo
    imagefill($img, 0, 0, $bgColor);
    
    // Circulo central
    $centerX = (int)($size / 2);
    $centerY = (int)($size * 0.38);
    $radius = (int)($size * 0.22);
    imagefilledellipse($img, $centerX, $centerY, $radius * 2, $radius * 2, $fgColor);
    
    // Triangulo (pin)
    $points = [
        $centerX, (int)($size * 0.7),
        (int)($centerX - $radius * 0.6), (int)($centerY + $radius * 0.6),
        (int)($centerX + $radius * 0.6), (int)($centerY + $radius * 0.6)
    ];
    imagefilledpolygon($img, $points, 3, $fgColor);
    
    // Texto "PS"
    $fontFile = null;
    if ($size >= 192) {
        imagestring($img, 5, $centerX - 7, (int)($size * 0.78), "PS", $fgColor);
    }
    
    // Salva
    $filename = __DIR__ . "/icon-{$size}x{$size}.png";
    imagepng($img, $filename);
    imagedestroy($img);
    
    echo "<p style='color:green'>Criado: icon-{$size}x{$size}.png (" . filesize($filename) . " bytes)</p>";
}

echo "<h3 style='color:green'>Icones criados com sucesso!</h3>";
echo "<p><a href='../buscar.php'>Voltar ao app</a></p>";
