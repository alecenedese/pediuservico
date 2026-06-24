<?php
/**
 * DIAGNÓSTICO — Verifica se os arquivos no servidor estão atualizados.
 * Acesse: /pediuservico/diagnostico-arquivos.php
 *
 * Mostra trechos-chave de cada arquivo modificado para confirmar
 * se a versão no servidor corresponde às últimas alterações.
 */
header('Content-Type: text/html; charset=utf-8');

function checar($titulo, $arquivo, $buscar, $descricaoEsperada) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (!file_exists($caminho)) {
        return ['arquivo'=>$arquivo, 'ok'=>false, 'msg'=>'ARQUIVO NÃO EXISTE no servidor', 'titulo'=>$titulo, 'trecho'=>''];
    }
    $conteudo = file_get_contents($caminho);
    $encontrado = (strpos($conteudo, $buscar) !== false);
    // Tenta extrair trecho relevante para mostrar
    $trecho = '';
    if (preg_match("/aceito\s+IN\s*\([^)]*\)/i", $conteudo, $m)) {
        $trecho = trim($m[0]);
    }
    return [
        'arquivo'=>$arquivo,
        'ok'=>$encontrado,
        'msg'=>$encontrado ? 'ATUALIZADO ✓' : 'DESATUALIZADO ✗ — '.$descricaoEsperada,
        'titulo'=>$titulo,
        'trecho'=>$trecho,
        'modificado'=>date('d/m/Y H:i:s', filemtime($caminho))
    ];
}

$checagens = [
    checar('get_pedidos.php — Item 7 (pendentes inclui ac)', 'get_pedidos.php', "IN ('n', 'a', 'ac')", "deveria conter: dp.aceito IN ('n', 'a', 'ac')"),
    checar('get-uninterested-count.php — Item 10', 'get-uninterested-count.php', "aceito='p' AND visto=1", "deveria filtrar visto=1"),
    checar('novomapa.php — Item 10 contador', 'novomapa.php', 'uninterested-counter', "deveria ter o contador"),
    checar('novomapa2.php — layout + rodapé', 'novomapa2.php', 'bottom-nav.php', "deveria incluir bottom-nav.php"),
    checar('salvar-avaliacao.php — Item 14 nome cliente', 'salvar-avaliacao.php', 'cliente', "deveria salvar nome do cliente"),
    checar('listar-avaliacoes.php — Item 14 média', 'listar-avaliacoes.php', 'LIMIT 50', "deveria calcular média"),
    checar('get_providers.php — Item 14 média prestador', 'get_providers.php', 'AVG(qtd_estrela)', "deveria puxar média"),
    checar('contaAguardando.php — Item 8 timer', 'contaAguardando.php', 'timer_acordo', "deveria ter timer"),
    checar('meus-orcamentos-finalizados.php — Item 13', 'meus-orcamentos-finalizados.php', 'Fallback', "deveria ter fallbacks"),
    checar('header-app.php — Item 15 menu', 'header-app.php', 'user-menu-dropdown', "deveria ter dropdown"),
    checar('verificacao.php — Item 15 página nova', 'verificacao.php', 'foto_antecedentes', "deveria ter os 4 uploads"),
    checar('meus-orcamentos-cli.php — Sem Resposta', 'meus-orcamentos-cli.php', 'Sem Resposta', "deveria ter aba Sem Resposta"),
];

$totalOk = 0;
foreach ($checagens as $c) { if ($c['ok']) $totalOk++; }
$total = count($checagens);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Diagnóstico de Arquivos</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#1a2332;color:#fff;padding:16px}
.header{text-align:center;padding:20px;margin-bottom:20px;background:rgba(0,212,255,.1);border-radius:12px;border:1px solid rgba(0,212,255,.3)}
.header h1{color:#00d4ff;font-size:20px;margin-bottom:8px}
.resumo{font-size:24px;font-weight:bold;margin-top:8px}
.resumo.ok{color:#28a745}.resumo.fail{color:#dc3545}
.item{background:rgba(255,255,255,.05);border-radius:8px;padding:12px 14px;margin:6px 0;border-left:4px solid #555}
.item.ok{border-left-color:#28a745}
.item.fail{border-left-color:#dc3545;background:rgba(220,53,69,.1)}
.item .nome{font-weight:bold;font-size:14px;margin-bottom:4px}
.item .status{font-size:13px;font-weight:700}
.item.ok .status{color:#28a745}
.item.fail .status{color:#dc3545}
.item .det{font-size:11px;color:#999;margin-top:4px;font-family:monospace;word-break:break-all}
.aviso{background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.4);color:#fbbf24;padding:14px;border-radius:8px;margin-top:16px;font-size:13px;line-height:1.6}
</style>
</head>
<body>
<div class="header">
    <h1>🔍 Diagnóstico de Arquivos no Servidor</h1>
    <p style="font-size:12px;color:#aaa">Confirma se as alterações foram enviadas para o servidor</p>
    <div class="resumo <?php echo $totalOk === $total ? 'ok' : 'fail'; ?>">
        <?php echo $totalOk; ?> / <?php echo $total; ?> arquivos atualizados
    </div>
</div>

<?php foreach ($checagens as $c): ?>
<div class="item <?php echo $c['ok'] ? 'ok' : 'fail'; ?>">
    <div class="nome"><?php echo htmlspecialchars($c['titulo']); ?></div>
    <div class="status"><?php echo htmlspecialchars($c['msg']); ?></div>
    <?php if (!empty($c['trecho'])): ?>
        <div class="det">Encontrado no arquivo: <?php echo htmlspecialchars($c['trecho']); ?></div>
    <?php endif; ?>
    <?php if (isset($c['modificado'])): ?>
        <div class="det">Última modificação: <?php echo $c['modificado']; ?></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php if ($totalOk < $total): ?>
<div class="aviso">
    ⚠️ <strong>Alguns arquivos estão desatualizados no servidor.</strong><br>
    Os arquivos marcados em vermelho precisam ser enviados (upload via FTP/cPanel/Git)
    da pasta local para <code>/pediuservico/</code> no servidor.<br>
    A data de "última modificação" mostra quando cada arquivo foi atualizado no servidor.
</div>
<?php else: ?>
<div class="aviso" style="background:rgba(40,167,69,.12);border-color:rgba(40,167,69,.4);color:#28a745;">
    ✅ Todos os arquivos estão atualizados no servidor!
</div>
<?php endif; ?>

</body>
</html>
