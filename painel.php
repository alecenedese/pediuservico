<?php
session_start();
require_once("send.php");

$aba = isset($_GET['aba']) ? $_GET['aba'] : 'pedidos';

// Se nao esta logado, redireciona para login com retorno
$logadoUnificado = isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1';
$logadoLegado    = isset($_COOKIE['celular_usuario']) && !empty($_COOKIE['celular_usuario']);
if (!$logadoUnificado && !$logadoLegado) {
    echo "<script>alert('Faca login para acessar essa area.'); window.location.href='login-unificado.php?retorno=" . urlencode("painel.php?aba=$aba") . "';</script>";
    exit;
}

$celularUsuario = isset($_COOKIE['celular_usuario']) ? $_COOKIE['celular_usuario'] : '';
$nomeUsuario = isset($_COOKIE['nome_usuario']) ? $_COOKIE['nome_usuario'] : '';
$ehPrestador = isset($_COOKIE['eh_prestador']) ? $_COOKIE['eh_prestador'] == '1' : false;
$ehCliente = isset($_COOKIE['eh_cliente']) ? $_COOKIE['eh_cliente'] == '1' : false;
$idPrestador = isset($_COOKIE['id_prestador']) ? $_COOKIE['id_prestador'] : 0;
$idCliente = isset($_COOKIE['id_cliente']) ? $_COOKIE['id_cliente'] : 0;

$primeiroNome = !empty($nomeUsuario) ? explode(' ', trim($nomeUsuario))[0] : 'Usuario';
$navAtiva = $aba;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Servico</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:linear-gradient(135deg,#1a2332 0%,#2d4a6b 100%);font-family:Arial,sans-serif;min-height:100vh;display:flex;flex-direction:column;color:white;padding-bottom:65px}
        .header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:rgba(0,212,255,.08);border-bottom:1px solid rgba(0,212,255,.15);position:sticky;top:0;z-index:100;backdrop-filter:blur(10px)}
        .header .logo{font-size:16px;font-weight:bold;color:#00d4ff;text-shadow:0 0 10px rgba(0,212,255,.3)}
        .header-right{display:flex;align-items:center;gap:10px}
        .btn-config{background:rgba(0,212,255,.15);border:1px solid rgba(0,212,255,.3);color:#00d4ff;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer;text-decoration:none}
        .main-content{flex:1;padding:16px;overflow-y:auto}
        .welcome{margin-bottom:16px}
        .welcome h2{font-size:18px;color:#00d4ff;margin-bottom:4px}
        .welcome p{font-size:13px;color:rgba(255,255,255,.6)}
        .badges{display:flex;gap:8px;margin-top:8px}
        .badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-prestador{background:rgba(0,212,255,.15);color:#00d4ff;border:1px solid rgba(0,212,255,.3)}
        .badge-cliente{background:rgba(16,185,129,.15);color:#10b981;border:1px solid rgba(16,185,129,.3)}
        .section-title{font-size:16px;color:#00f0ff;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid rgba(0,240,255,.15)}
        .card-list{display:flex;flex-direction:column;gap:10px}
        .card{background:rgba(0,240,255,.06);border:1px solid rgba(0,240,255,.2);border-radius:12px;padding:14px 16px;text-decoration:none;color:white;display:block;transition:.2s}
        .card:hover{background:rgba(0,240,255,.1)}
        .card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
        .card-title{font-size:14px;font-weight:bold;color:#00f0ff}
        .card-status{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .status-pendente{background:rgba(245,158,11,.15);color:#f59e0b;border:1px solid rgba(245,158,11,.3)}
        .status-aceito{background:rgba(16,185,129,.15);color:#10b981;border:1px solid rgba(16,185,129,.3)}
        .status-recusado{background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3)}
        .card-info{font-size:12px;color:rgba(255,255,255,.6);margin-top:4px}
        .empty-state{text-align:center;padding:40px 20px;color:rgba(255,255,255,.5)}
        .empty-state .icon{font-size:48px;margin-bottom:12px}
        .empty-state p{font-size:14px;margin-bottom:16px}
        .btn-action{display:inline-block;background:linear-gradient(145deg,#00d4ff,#00f0ff);color:#1a2332;padding:10px 24px;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;border:none;cursor:pointer}

        /* Bottom Nav */
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;background:rgba(26,35,50,.97);border-top:2px solid rgba(0,212,255,.3);display:flex;z-index:999;backdrop-filter:blur(10px)}
        .nav-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8px 4px;text-decoration:none;color:rgba(255,255,255,.5);font-size:11px;font-weight:600;transition:.2s;gap:3px}
        .nav-item.active{color:#00d4ff}
        .nav-item.active .nav-icon{background:rgba(0,212,255,.15)}
        .nav-icon{width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:16px}
        .nav-item:hover{color:#00d4ff}
    </style>
</head>
<body>

<div class="header">
    <div class="logo">Pediu Servico</div>
    <div class="header-right">
        <span style="font-size:12px;color:rgba(255,255,255,.5)"><?php echo $primeiroNome; ?></span>
        <?php if($ehPrestador): ?>
            <a href="edicao.php" class="btn-config">Minha Conta</a>
        <?php endif; ?>
        <a href="sair-unificado.php" class="btn-config" style="color:#ef4444;border-color:rgba(239,68,68,.3)">Sair</a>
    </div>
</div>

<div class="main-content">
    <div class="welcome">
        <h2>Ola, <?php echo $primeiroNome; ?>!</h2>
        <p><?php echo $celularUsuario; ?></p>
        <div class="badges">
            <?php if($ehPrestador): ?><span class="badge badge-prestador">Prestador</span><?php endif; ?>
            <?php if($ehCliente): ?><span class="badge badge-cliente">Cliente</span><?php endif; ?>
        </div>
    </div>

    <?php if($aba == 'pedidos'): ?>
        <!-- ABA: MEUS PEDIDOS (como cliente) -->
        <div class="section-title">Meus Pedidos</div>
        <div class="card-list">
        <?php
        if($ehCliente && $idCliente > 0) {
            $queryPedidos = mysqli_query($con, "
                SELECT p.codigo, p.descricao, p.tempo, p.data_hora, p.subcategoria,
                       c.titulo as categoria_nome,
                       (SELECT COUNT(*) FROM disparo_pedidos dp WHERE dp.codpedido = p.codigo AND dp.aceito='s') as aceitos,
                       (SELECT COUNT(*) FROM disparo_pedidos dp WHERE dp.codpedido = p.codigo) as total_disparos
                FROM pedido p
                LEFT JOIN categoria c ON c.codigo = p.subcategoria
                LEFT JOIN pega_contato pc ON pc.codpedido = p.codigo
                WHERE pc.codcliente = '$idCliente'
                GROUP BY p.codigo
                ORDER BY p.data_hora DESC
                LIMIT 20
            ");
            
            if($queryPedidos && mysqli_num_rows($queryPedidos) > 0) {
                while($pedido = mysqli_fetch_assoc($queryPedidos)) {
                    $statusClass = 'status-pendente';
                    $statusText = 'Pendente';
                    if($pedido['aceitos'] > 0) { $statusClass = 'status-aceito'; $statusText = $pedido['aceitos'] . ' aceito(s)'; }
                    
                    echo '<a href="meus-orcamentos-cli.php" class="card">';
                    echo '<div class="card-header">';
                    echo '<span class="card-title">' . htmlspecialchars($pedido['categoria_nome'] ?? 'Servico') . '</span>';
                    echo '<span class="card-status ' . $statusClass . '">' . $statusText . '</span>';
                    echo '</div>';
                    if(!empty($pedido['descricao'])) echo '<div class="card-info">' . htmlspecialchars(substr($pedido['descricao'], 0, 60)) . '</div>';
                    echo '<div class="card-info">' . date('d/m/Y H:i', strtotime($pedido['data_hora'])) . '</div>';
                    echo '</a>';
                }
            } else {
                echo '<div class="empty-state">';
                echo '<div class="icon">📋</div>';
                echo '<p>Voce ainda nao fez nenhum pedido</p>';
                echo '<a href="buscar.php" class="btn-action">Buscar Servicos</a>';
                echo '</div>';
            }
        } else {
            echo '<div class="empty-state">';
            echo '<div class="icon">📋</div>';
            echo '<p>Voce ainda nao fez nenhum pedido</p>';
            echo '<a href="buscar.php" class="btn-action">Buscar Servicos</a>';
            echo '</div>';
        }
        ?>
        </div>

    <?php elseif($aba == 'servicos'): ?>
        <!-- ABA: MEUS SERVICOS (como prestador) -->
        <div class="section-title">Meus Servicos</div>
        <div class="card-list">
        <?php
        if($ehPrestador && $idPrestador > 0) {
            $queryServicos = mysqli_query($con, "
                SELECT dp.codpedido, dp.aceito, p.descricao, p.tempo, p.data_hora, p.subcategoria,
                       c.titulo as categoria_nome
                FROM disparo_pedidos dp
                INNER JOIN pedido p ON p.codigo = dp.codpedido
                LEFT JOIN categoria c ON c.codigo = p.subcategoria
                WHERE dp.codcadastro = '$idPrestador'
                ORDER BY p.data_hora DESC
                LIMIT 20
            ");
            
            if($queryServicos && mysqli_num_rows($queryServicos) > 0) {
                while($servico = mysqli_fetch_assoc($queryServicos)) {
                    $statusClass = 'status-pendente';
                    $statusText = 'Novo';
                    if($servico['aceito'] == 's' || $servico['aceito'] == 'ac') { $statusClass = 'status-aceito'; $statusText = 'Aceito'; }
                    elseif($servico['aceito'] == 'p') { $statusClass = 'status-recusado'; $statusText = 'Perdido'; }
                    
                    echo '<a href="meus-orcamentos.php" class="card">';
                    echo '<div class="card-header">';
                    echo '<span class="card-title">' . htmlspecialchars($servico['categoria_nome'] ?? 'Servico') . '</span>';
                    echo '<span class="card-status ' . $statusClass . '">' . $statusText . '</span>';
                    echo '</div>';
                    if(!empty($servico['descricao'])) echo '<div class="card-info">' . htmlspecialchars(substr($servico['descricao'], 0, 60)) . '</div>';
                    echo '<div class="card-info">' . ($servico['tempo'] ?? '') . ' - ' . date('d/m/Y H:i', strtotime($servico['data_hora'])) . '</div>';
                    echo '</a>';
                }
            } else {
                echo '<div class="empty-state">';
                echo '<div class="icon">🔧</div>';
                echo '<p>Nenhum servico recebido ainda</p>';
                if(!$ehPrestador) {
                    echo '<a href="cadastro.php" class="btn-action">Cadastrar como Prestador</a>';
                } else {
                    echo '<p style="font-size:12px">Quando clientes pedirem servicos da sua categoria, voce vera aqui</p>';
                }
                echo '</div>';
            }
        } else {
            echo '<div class="empty-state">';
            echo '<div class="icon">🔧</div>';
            echo '<p>Voce ainda nao oferece servicos</p>';
            echo '<p style="font-size:12px;margin-bottom:16px;color:rgba(255,255,255,.4)">Cadastre-se como prestador para receber pedidos de clientes</p>';
            echo '<a href="cadastro.php" class="btn-action" style="margin-bottom:12px">Quero oferecer servicos</a><br>';
            echo '<a href="buscar.php" style="color:#00d4ff;font-size:13px;text-decoration:none">← Voltar para busca</a>';
            echo '</div>';
        }
        ?>
        </div>
    <?php endif; ?>
</div>

<?php include('bottom-nav.php'); ?>

<?php if($ehPrestador): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const prestadorId = <?php echo $idPrestador; ?>;
        if (prestadorId && window.PushHelper) {
            initPushForPrestador(prestadorId);
        }
    });
</script>
<?php endif; ?>

</body>
</html>
