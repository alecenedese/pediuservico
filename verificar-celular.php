<?php
session_start();
require_once("send.php");
date_default_timezone_set('America/Cuiaba');

$celular = isset($_GET['celular']) ? $_GET['celular'] : '';
$numerolimpo = preg_replace('/\D/', '', $celular);

if (empty($numerolimpo) || strlen($numerolimpo) < 10) {
    echo "<script>alert('Número inválido.'); window.location.href='login-unificado.php';</script>";
    exit;
}

// Verifica se existe como prestador ou cliente
$queryPrestador = mysqli_query($con, "SELECT id, NOME, CELULAR FROM parceiro WHERE REPLACE(REPLACE(REPLACE(REPLACE(CELULAR,'(',''),')',''),'-',''),' ','') = '$numerolimpo' OR CELULAR LIKE '%$numerolimpo%'");
$ehPrestador = mysqli_num_rows($queryPrestador) > 0;
$dadosPrestador = $ehPrestador ? mysqli_fetch_assoc($queryPrestador) : null;

$queryCliente = mysqli_query($con, "SELECT id, NOME, CELULAR FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(CELULAR,'(',''),')',''),'-',''),' ','') = '$numerolimpo' OR CELULAR LIKE '%$numerolimpo%'");
$ehCliente = mysqli_num_rows($queryCliente) > 0;
$dadosCliente = $ehCliente ? mysqli_fetch_assoc($queryCliente) : null;

if (!$ehPrestador && !$ehCliente) {
    // Número não cadastrado - cria como cliente automaticamente
    $dataCad = date("Y-m-d");
    mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad) VALUES ('', '', '', '', '$celular', '', '', '$dataCad')") or die(mysqli_error($con));
    $contaUltimo = mysqli_fetch_array(mysqli_query($con, "SELECT max(x.id) FROM clientes x"));
    
    // Criar user chat
    mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) VALUES ('".$contaUltimo[0]."', '', '', '', 'user-default.png', '', '$celular')");
    
    $ehCliente = true;
    $dadosCliente = ['id' => $contaUltimo[0], 'NOME' => '', 'CELULAR' => $celular];
}

$nomeUsuario = '';
if ($ehPrestador && !empty($dadosPrestador['NOME'])) {
    $nomeUsuario = $dadosPrestador['NOME'];
} elseif ($ehCliente && !empty($dadosCliente['NOME'])) {
    $nomeUsuario = $dadosCliente['NOME'];
}

$idPrestador = $ehPrestador ? $dadosPrestador['id'] : 0;
$idCliente = $ehCliente ? $dadosCliente['id'] : 0;

// Login direto - redireciona para confirmação automaticamente
$retorno = isset($_GET['retorno']) ? $_GET['retorno'] : '';
$urlConfirmar = "login-confirmar.php?celular=" . urlencode($celular) 
    . "&codigo_esperado=direto&codigo=direto"
    . "&nome=" . urlencode($nomeUsuario)
    . "&id_prestador=" . $idPrestador
    . "&id_cliente=" . $idCliente
    . "&eh_prestador=" . ($ehPrestador ? '1' : '0')
    . "&eh_cliente=" . ($ehCliente ? '1' : '0');
if (!empty($retorno)) {
    $urlConfirmar .= "&retorno=" . urlencode($retorno);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrando - Pediu Servico</title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:linear-gradient(135deg,#1a2332 0%,#2d4a6b 100%);font-family:Arial,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{background:rgba(255,255,255,.95);border-radius:16px;padding:40px 30px;box-shadow:0 8px 32px rgba(0,0,0,.3);text-align:center;max-width:350px;width:90%}
        .phone{color:#00d4ff;font-weight:bold;font-size:18px;margin-bottom:8px}
        .name{color:#374151;font-size:16px;margin-bottom:15px}
        .badges{display:flex;gap:8px;justify-content:center;margin-bottom:20px}
        .badge{padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600}
        .badge-p{background:rgba(0,212,255,.15);color:#00d4ff;border:1px solid rgba(0,212,255,.3)}
        .badge-c{background:rgba(16,185,129,.15);color:#10b981;border:1px solid rgba(16,185,129,.3)}
        .loading{margin:20px 0}
        .spinner{width:40px;height:40px;border:4px solid rgba(0,212,255,.2);border-top-color:#00d4ff;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto}
        @keyframes spin{to{transform:rotate(360deg)}}
        .msg{color:#6b7280;font-size:14px;margin-top:15px}
        .change{color:#00d4ff;text-decoration:none;font-size:13px;display:inline-block;margin-top:20px}
    </style>
</head>
<body>
<div class="card">
    <div class="phone"><?php echo $celular; ?></div>
    <?php if(!empty($nomeUsuario)): ?>
        <div class="name">Ola, <strong><?php echo $nomeUsuario; ?></strong></div>
    <?php endif; ?>
    <div class="badges">
        <?php if($ehPrestador): ?><span class="badge badge-p">Prestador</span><?php endif; ?>
        <?php if($ehCliente): ?><span class="badge badge-c">Cliente</span><?php endif; ?>
    </div>
    <div class="loading"><div class="spinner"></div></div>
    <div class="msg">Entrando...</div>
    <a href="login-unificado.php" class="change">Trocar numero</a>
</div>
<script>
    setTimeout(function() {
        window.location.href = '<?php echo $urlConfirmar; ?>';
    }, 1500);
</script>
</body>
</html>
