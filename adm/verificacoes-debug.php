<?php
// Diagnóstico rápido das verificações. Acesse: /adm/verificacoes-debug.php
header("Content-Type: text/plain; charset=utf-8");

$conApp = new mysqli("177.53.140.149", "paxsaoju1_user", ")qQ~eKZ@fF19", "paxsaoju1_banco");
if ($conApp->connect_errno) {
    exit("ERRO ao conectar: " . $conApp->connect_error);
}
$conApp->set_charset('utf8');

echo "=== DIAGNÓSTICO DE VERIFICAÇÕES ===\n\n";
echo "Banco: paxsaoju1_banco @ 177.53.140.149\n\n";

// A tabela existe?
$qT = mysqli_query($conApp, "SHOW TABLES LIKE 'verificacoes_usuario'");
if (!$qT || mysqli_num_rows($qT) === 0) {
    echo "A tabela 'verificacoes_usuario' NÃO existe neste banco.\n";
    echo "Isso significa que nenhuma verificação foi gravada ainda, ou o app usa outro banco.\n";
    exit;
}
echo "Tabela 'verificacoes_usuario' existe. ✓\n\n";

// Estrutura
echo "--- Colunas ---\n";
$qC = mysqli_query($conApp, "SHOW COLUMNS FROM verificacoes_usuario");
while ($qC && $c = mysqli_fetch_assoc($qC)) {
    echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

// Total de registros
$qN = mysqli_query($conApp, "SELECT COUNT(*) as total FROM verificacoes_usuario");
$total = $qN ? (int)mysqli_fetch_assoc($qN)['total'] : 0;
echo "\n--- Total de registros: $total ---\n\n";

// Lista os registros
$qL = mysqli_query($conApp, "SELECT * FROM verificacoes_usuario ORDER BY id DESC LIMIT 20");
while ($qL && $r = mysqli_fetch_assoc($qL)) {
    echo "ID {$r['id']} | usuario={$r['id_usuario']} | tipo={$r['tipo_usuario']} | status={$r['status']} | enviado={$r['data_envio']}\n";
    echo "   pessoal=".($r['foto_pessoal'] ?: '-')." | doc=".($r['foto_documento'] ?: '-')." | comprov=".($r['foto_comprovante'] ?: '-')." | antec=".($r['foto_antecedentes'] ?: '-')."\n";
}

if ($total === 0) {
    echo "Nenhum registro encontrado. Verifique se o envio pelo app realmente gravou no banco.\n";
}
