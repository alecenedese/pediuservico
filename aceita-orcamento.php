<?php require_once("send.php"); ?>
<!DOCTYPE html>
<html> 
<head> 
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
  <title>Google Maps Multiple Markers</title> 
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
</head> 
<body>

<button style="display: none;" id="botao" onchange="getLocation()" onclick="getLocation()">Clique Aqui</button>


<form style="display: none;" action="mapa2.php">
  <input type="text" id="latitude" name="latitude"  >
  <input type="text" id="longitude" name="longitude">
  <input type="submit"  id="idenvia" value="enviar">
</form>


<script type="text/javascript">

document.getElementById("botao").click();


var x=document.getElementById("demo");
var lat = document.getElementById('latitude');
function getLocation()
{
    if (navigator.geolocation){
        navigator.geolocation.getCurrentPosition(showPosition);
    }else{x.innerHTML="O seu navegador não suporta Geolocalização.";}
}
function showPosition(position)
{
   var lat = position.coords.latitude;
   var log = position.coords.longitude;

  <?php if(isset($_GET['contraproposta'])) { ?>
  window.location.href="<?php echo $urlserver; ?>salva-localizacao-contraproposta.php?latitude=" + lat + "&longitude=" + log + "&codpedido=<?php echo $_GET['codigo']; ?>&maximo=<?php echo $_GET['maximo']; ?>&minimo=<?php echo $_GET['minimo']; ?>&contraproposta=<?php echo urlencode($_GET['contraproposta']); ?>";
  <?php } else { ?> 
  window.location.href="<?php echo $urlserver; ?>salva-localizacao-pedido-aceito.php?latitude=" + lat + "&longitude=" + log + "&codpedido=<?php echo $_GET['codigo']; ?>";
  <?php  } ?>
}

  </script>
</body>
</html>