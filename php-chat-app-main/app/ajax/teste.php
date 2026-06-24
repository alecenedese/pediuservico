
<?php
$to_id = $_POST['to_id'];
$codpedido = $_GET['codpedido'];
?>
<!DOCTYPE html>
<html> 
<head> 
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
  <title>Google Maps Multiple Markers</title> 

</head> 
<body>

<button style="display: none;" id="botao" onclick="getLocation()">Clique Aqui</button>

<form style="display: none;" action="mapa2.php">
  <input type="text" id="latitude" name="latitude"  >
  <input type="text" id="longitude" name="longitude">
  <input type="submit" id="idenvia" value="enviar">
</form>

<script type="text/javascript">

    // Chama a função getLocation automaticamente
    document.getElementById("botao").click();

    var x = document.getElementById("demo");
    var lat = document.getElementById('latitude');

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            x.innerHTML = "O seu navegador não suporta Geolocalização.";
        }
    }

    function showPosition(position) {
        var lat = position.coords.latitude;
        var log = position.coords.longitude;

        // Enviar dados via AJAX em vez de redirecionar
        $.ajax({
            url: "http://localhost:8080/maoamiga/php-chat-app-main/app/ajax/insertLocal.php",
            method: "POST",
            data: {
                latitude: lat,
                longitude: log,
                codpedido: "<?php echo $_GET['codpedido']; ?>",
                to_id: "<?php echo $to_id; ?>"
            },
            success: function(response) {
                console.log("Localização enviada com sucesso:");
            },
            error: function(xhr, status, error) {
                console.error("Erro ao enviar localização:", error);
            }
        });
    }

</script>

</body>
</html>