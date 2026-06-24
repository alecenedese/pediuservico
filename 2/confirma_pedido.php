<?php 
      $queryEditc = mysqli_query($con, "select * from clientes where id='".$_COOKIE['codcliente']."'");
      $rowEdit = mysqli_fetch_array($queryEditc);
      $nome = $rowEdit['NOME'];
      if(isset($_COOKIE['id'])){
        $codcliente = $_COOKIE['id'];
      } else {
      $codcliente = $_COOKIE['codcliente'];
    }

    ?>
<?php
require_once('send.php');
$codpedido = $_GET['codpedido'];
$verificacao = mysqli_query($con, "SELECT g.titulo, p.codigo, s.titulo AS sub, p.local, 
      p.tempo, p.descricao, p.lat, p.log, p.data_hora, pc.codcadastro, pa.NOME,
      p.foto_1, p.foto_2, p.foto_3, p.foto_4
      FROM 
      grupos g,
      pedido p,
      categoria s,
      pega_contato pc,
      parceiro pa,
      disparo_pedidos dp
      WHERE 
      pc.codcliente = '$codcliente'
      and p.codigo = '$codpedido'
      AND p.categoria = g.codigo
      AND p.subcategoria = s.codigo
      AND pc.codpedido = p.codigo	
      AND pc.codcadastro = pa.id	
      AND p.codigo = dp.codpedido
      AND dp.aceito = 's'
      GROUP BY p.codigo
      ORDER BY p.codigo desc");
if(mysqli_num_rows($verificacao)>0){ 
?>

<div style="width: 100%; float: left; color: green;">
                Pedido Aceito! 
            </div>

<?php  echo "<script>window.location.href='meus-orcamentos-cli2.php';</script>"; } ?>