<?php 
    require("send.php");

    $codcliente = $_GET['codcadastro'];
    $codpedido = $_GET['codpedido'];
    $listaG = mysqli_query($con, "
    select * from disparo_pedidos 
        WHERE
codcadastro = '$codcliente'
    AND codpedido = '".$codpedido."'
    and aceito = 'ac'
    ");
    $rowg = mysqli_num_rows($listaG); 

    // Item 14: custo de moedas vem da CATEGORIA (categoria.codigo = pedido.subcategoria)
    $custoMoedasConta = 0;
    $qCustoConta = mysqli_query($con, "SELECT c.moeda FROM pedido p INNER JOIN categoria c ON c.codigo = p.subcategoria WHERE p.codigo = '".mysqli_real_escape_string($con, $codpedido)."'");
    if ($qCustoConta && $rCustoConta = mysqli_fetch_array($qCustoConta)) {
        $custoMoedasConta = (int)$rCustoConta['moeda'];
    }
    if ($custoMoedasConta <= 0) {
        $qCustoContaG = mysqli_query($con, "SELECT g.custo_moedas FROM pedido p INNER JOIN grupos g ON g.codigo = p.categoria WHERE p.codigo = '".mysqli_real_escape_string($con, $codpedido)."'");
        if ($qCustoContaG && $rCustoContaG = mysqli_fetch_array($qCustoContaG)) {
            $cgConta = (int)$rCustoContaG['custo_moedas'];
            if ($cgConta > 0) $custoMoedasConta = $cgConta;
        }
    }
    if ($custoMoedasConta <= 0) $custoMoedasConta = 5;

if($rowg > 0) {
    $queryMoedas = mysqli_query($con, "select * from quantidade_pedidos where tipo = 'pre' and codcadastro='".$codcliente."'");
    $rowM = mysqli_fetch_array($queryMoedas);
    $qtdMoedas = isset($rowM['qtd']) ? (int)$rowM['qtd'] : 0;
    $temMoedasSuficientes = $qtdMoedas >= $custoMoedasConta;

    // Item 8: Calcula o tempo restante do timer de acordo (mesmo que o cliente vê)
    $segundosRestantes = 0;
    $qTimer = mysqli_query($con, "SELECT tempo_expiracao FROM timer_acordo WHERE codpedido='$codpedido' AND codcadastro='$codcliente' ORDER BY id DESC LIMIT 1");
    if ($qTimer && $rTimer = mysqli_fetch_array($qTimer)) {
        $expira = strtotime($rTimer['tempo_expiracao']);
        $agora = time();
        $segundosRestantes = max(0, $expira - $agora);
    }
?>
 <h6 class="card-title" style=" padding: 10px 0; font-size: 16px !important; color:green !important; line-height: 15px; text-align: center;     margin-bottom: 1px;">
    Cliente aceitou seu pedido!<br>
    <?php if($temMoedasSuficientes) { ?>
    Deseja debitar <?php echo $custoMoedasConta; ?> moeda(s) nesse pedido? (Saldo: <?php echo $qtdMoedas; ?>)<br>
    <?php } else { ?>
    Você precisa de <?php echo $custoMoedasConta; ?> moeda(s) para este serviço. (Saldo: <?php echo $qtdMoedas; ?>)<br>
    Deseja recarregar moedas?<br>
      <?php } ?>
</h6>

<?php if ($segundosRestantes > 0) { ?>
<!-- Item 8: Timer de tempo restante para firmar o acordo -->
<div style="text-align:center;margin:8px 0;padding:10px;background:rgba(220,53,69,0.1);border:1px solid rgba(220,53,69,0.3);border-radius:8px;">
    <div style="font-size:11px;color:#dc3545;font-weight:600;text-transform:uppercase;margin-bottom:2px;">⏳ Tempo restante para firmar o acordo</div>
    <div id="timer-acordo-<?php echo $codpedido; ?>" data-segundos="<?php echo $segundosRestantes; ?>" style="font-size:26px;font-weight:800;color:#dc3545;line-height:1;"><?php echo sprintf('%02d:%02d', floor($segundosRestantes/60), $segundosRestantes%60); ?></div>
    <div style="font-size:10px;color:#856404;margin-top:2px;">Recarregue antes do tempo acabar para não perder o pedido</div>
</div>
<script>
(function(){
    var el = document.getElementById('timer-acordo-<?php echo $codpedido; ?>');
    if (!el || el.dataset.iniciado) return;
    el.dataset.iniciado = '1';
    var restante = parseInt(el.getAttribute('data-segundos'), 10);
    var intv = setInterval(function(){
        restante--;
        if (restante <= 0) {
            clearInterval(intv);
            el.textContent = '00:00';
            el.style.color = '#999';
            return;
        }
        var m = Math.floor(restante/60), s = restante%60;
        el.textContent = (m<10?'0':'')+m + ':' + (s<10?'0':'')+s;
    }, 1000);
})();
</script>
<?php } ?>

<div style="width: 100%; text-align: center;">
    <?php if($temMoedasSuficientes) { ?>
    <a href="debitar_moedas.php?codpedido=<?php echo $codpedido; ?>&codcliente=<?php echo $codcliente; ?>" class="btn btn-primary " style="margin-top: 5px !important;    
    background: #00c3ff;
    text-decoration: none;
    position: relative;
    color: #fff;
    padding: 7px 12px;
    border-radius: 6px;" >SIM</a>
    <?php } else { ?>
      <a href="pagamento.php?codpedido=<?php echo $codpedido; ?>&codcliente=<?php echo $codcliente; ?>" class="btn btn-primary " style="margin-top: 5px !important;
    background: #00c3ff;
    text-decoration: none;
    color: #fff;
    padding: 7px 12px;
    position: relative;
    border-radius: 6px;" >SIM</a>
      <?php } ?>

<?php } ?>

</div>