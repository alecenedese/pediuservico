<?php
// teste-carga.php
// Dispara N requisições SIMULTÂNEAS para um alvo e mede o desempenho.
//
// USO (navegador ou CLI):
//   teste-carga.php?n=400&sub=92            -> 400 requisições simultâneas, subcategoria 92
//   teste-carga.php?n=400&sub=92&worker=1   -> idem, disparando o worker
//   teste-carga.php?alvo=URL&sub=92         -> testar outra URL
//   teste-carga.php?limpar=1                -> apaga TODOS os dados de teste (fila, disparo, markers)
//
// IMPORTANTE: use uma subcategoria (sub) REAL com MUITOS prestadores, para o
// teste refletir o trabalho real do novomapa (query JOIN + inserts em massa).
//
// DICA: rode de OUTRA máquina (seu PC com laragon) contra a URL pública para um
// teste realista de rede. Rodar no próprio servidor mede a capacidade local.

@set_time_limit(300);
@ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

$base = 'https://gessomt.app.br/pediuservico/';

// Limpeza dos dados de teste
if (isset($_GET['limpar'])) {
    require_once(__DIR__ . '/send.php');
    mysqli_query($con, "DELETE FROM push_fila WHERE title LIKE '[TESTE]%'");
    $f = mysqli_affected_rows($con);
    mysqli_query($con, "DELETE FROM disparo_pedidos WHERE codpedido >= 9000000");
    $d = mysqli_affected_rows($con);
    mysqli_query($con, "DELETE FROM markers WHERE codpedido >= 9000000");
    $m = mysqli_affected_rows($con);
    echo "Limpeza concluida:\n";
    echo "  push_fila      : $f registros\n";
    echo "  disparo_pedidos: $d registros\n";
    echo "  markers        : $m registros\n";
    exit;
}

$n     = isset($_GET['n']) ? max(1, (int)$_GET['n']) : 400;
$sub   = isset($_GET['sub']) ? $_GET['sub'] : '';
$worker = isset($_GET['worker']) && $_GET['worker'] == '1' ? '&worker=1' : '';
$alvo  = isset($_GET['alvo']) ? $_GET['alvo'] : ($base . 'teste-carga-alvo.php');

if ($sub === '' && strpos($alvo, 'teste-carga-alvo.php') !== false) {
    echo "ERRO: informe a subcategoria real com &sub=ID (use uma que tenha muitos prestadores).\n";
    echo "Exemplo: teste-carga.php?n=400&sub=92\n";
    exit;
}

echo "========================================\n";
echo " TESTE DE CARGA\n";
echo "========================================\n";
echo "Requisicoes simultaneas : $n\n";
echo "Subcategoria (sub)      : $sub\n";
echo "Alvo                    : $alvo\n";
echo "Disparar worker         : " . ($worker ? 'SIM' : 'nao') . "\n";
echo "Inicio                  : " . date('H:i:s') . "\n\n";
echo "Disparando...\n\n";
@ob_flush(); @flush();

$mh = curl_multi_init();
// Permite muitas conexões simultâneas
if (defined('CURLMOPT_MAXCONNECTS')) {
    curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, $n);
}
$handles = [];
$inicioParede = microtime(true);

for ($i = 0; $i < $n; $i++) {
    $sep = (strpos($alvo, '?') !== false) ? '&' : '?';
    $url = $alvo . $sep . 'sub=' . rawurlencode($sub) . $worker . '&req=' . $i;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[$i] = $ch;
}

// Executa todas em paralelo
$running = null;
do {
    $status = curl_multi_exec($mh, $running);
    if ($running > 0) {
        curl_multi_select($mh, 1.0);
    }
} while ($running > 0 && $status == CURLM_OK);

$fimParede = microtime(true);
$tempoTotal = $fimParede - $inicioParede;

// Coleta resultados
$ok = 0; $erro = 0; $tempos = []; $codigos = []; $exemploErro = '';
foreach ($handles as $i => $ch) {
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $t = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $tempos[] = $t;
    $codigos[$code] = ($codigos[$code] ?? 0) + 1;
    if ($code == 200) {
        $ok++;
    } else {
        $erro++;
        if ($exemploErro === '') {
            $exemploErro = 'HTTP ' . $code . ' | ' . curl_error($ch);
        }
    }
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

sort($tempos);
$cnt = count($tempos);
$media = $cnt ? array_sum($tempos) / $cnt : 0;
$min = $tempos[0] ?? 0;
$max = $tempos[$cnt - 1] ?? 0;
$p50 = $tempos[(int)floor($cnt * 0.50)] ?? 0;
$p95 = $tempos[(int)floor($cnt * 0.95)] ?? $max;
$p99 = $tempos[(int)floor($cnt * 0.99)] ?? $max;

echo "========================================\n";
echo " RESULTADO\n";
echo "========================================\n";
echo "Tempo total (parede) : " . round($tempoTotal, 2) . "s\n";
echo "Throughput aprox.    : " . round($n / max(0.001, $tempoTotal), 1) . " req/s\n";
echo "Sucesso (200)        : $ok\n";
echo "Erros                : $erro\n";
echo "HTTP codes           : " . json_encode($codigos) . "\n";
if ($exemploErro) echo "Exemplo de erro      : $exemploErro\n";
echo "\nTempo por requisicao:\n";
echo "  min   : " . round($min * 1000) . " ms\n";
echo "  p50   : " . round($p50 * 1000) . " ms\n";
echo "  media : " . round($media * 1000) . " ms\n";
echo "  p95   : " . round($p95 * 1000) . " ms\n";
echo "  p99   : " . round($p99 * 1000) . " ms\n";
echo "  max   : " . round($max * 1000) . " ms\n";

echo "\n========================================\n";
echo "Como interpretar:\n";
echo "- Se 'Sucesso' = $n e p95 baixo (<2s): servidor aguenta bem.\n";
echo "- Se aparecerem erros 500/502/503 ou timeout: saturou (workers PHP/conexoes MySQL).\n";
echo "- Limpe os dados de teste depois: teste-carga.php?limpar=1\n";
echo "========================================\n";
