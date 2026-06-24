<?php
  
http_response_code(200);
require_once("send.php");
date_default_timezone_set('America/Cuiaba');
$datat = date("Y-m-d H:i:s"); 


$subcategoria = isset($_GET['subcategoria']) ? $_GET['subcategoria'] : '';

// Usa o codpedido vindo do salvamento (reuso/novo). Só cai no max(codigo) se não vier.
$codpedidoParam = isset($_GET['codpedido']) ? intval($_GET['codpedido']) : 0;
if ($codpedidoParam > 0) {
    $codpedidoAtual = $codpedidoParam;
} else {
    $ultimopedido = mysqli_fetch_array(mysqli_query($con, "select max(codigo) from pedido")) or die(mysqli_error($con));
    $codpedidoAtual = $ultimopedido[0];
}

?>


<!DOCTYPE html>
<html> 
<head> 
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
  <title>Buscando Prestadores - USERVICE</title> 
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
  <link rel="stylesheet" href="global-font-size.css">
  <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
      font-family: 'Arial', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
    }
    
    .loading-container {
      text-align: center;
      padding: 40px;
    }
    
    .loading-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 30px;
      position: relative;
    }
    
    .loading-icon::before {
      content: '📍';
      font-size: 50px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      animation: bounce 1.5s ease-in-out infinite;
    }
    
    .loading-ring {
      width: 80px;
      height: 80px;
      border: 4px solid rgba(0, 212, 255, 0.2);
      border-top: 4px solid #00d4ff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    @keyframes bounce {
      0%, 100% { transform: translate(-50%, -50%) scale(1); }
      50% { transform: translate(-50%, -60%) scale(1.1); }
    }
    
    .loading-title {
      font-size: 22px;
      font-weight: bold;
      color: #00d4ff;
      margin-bottom: 15px;
      text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
    }
    
    .loading-text {
      font-size: 16px;
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 10px;
    }
    
    .loading-subtext {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.5);
    }
    
    .loading-dots {
      display: inline-block;
    }
    
    .loading-dots::after {
      content: '';
      animation: dots 1.5s steps(4, end) infinite;
    }
    
    @keyframes dots {
      0% { content: ''; }
      25% { content: '.'; }
      50% { content: '..'; }
      75% { content: '...'; }
      100% { content: ''; }
    }
    
    .error-container {
      display: none;
      text-align: center;
      padding: 40px;
    }
    
    .error-icon {
      font-size: 60px;
      margin-bottom: 20px;
    }
    
    .error-title {
      font-size: 20px;
      color: #ff6b6b;
      margin-bottom: 15px;
    }
    
    .error-text {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 20px;
      max-width: 300px;
    }
    
    .retry-button {
      background: linear-gradient(145deg, #00d4ff, #00f0ff);
      color: #1a2332;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .retry-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
    }
    
    .progress-bar {
      width: 200px;
      height: 4px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 2px;
      margin: 20px auto 0;
      overflow: hidden;
    }
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #00d4ff, #00f0ff);
      border-radius: 2px;
      animation: progress 2s ease-in-out infinite;
    }
    
    @keyframes progress {
      0% { width: 0%; margin-left: 0; }
      50% { width: 70%; margin-left: 0; }
      100% { width: 0%; margin-left: 100%; }
    }
  </style>
</head> 
<body>

<div class="loading-container" id="loading">
  <div class="loading-icon">
    <div class="loading-ring"></div>
  </div>
  <div class="loading-title">Pediu Serviço</div>
  <div class="loading-text">Obtendo sua localização<span class="loading-dots"></span></div>
  <div class="loading-subtext">Buscando prestadores próximos a você</div>
  <div class="progress-bar">
    <div class="progress-fill"></div>
  </div>
</div>

<div class="error-container" id="error">
  <div class="error-icon">📍</div>
  <div class="error-title">Não foi possível obter sua localização</div>
  <div class="error-text">Por favor, permita o acesso à localização ou verifique sua conexão com a internet.</div>
  <button class="retry-button" onclick="getLocation()">Tentar Novamente</button>
</div>

<button style="display: none;" id="botao" onclick="getLocation()">Clique Aqui</button>

<form style="display: none;" action="mapa2.php">
  <input type="text" id="latitude" name="latitude">
  <input type="text" id="longitude" name="longitude">
  <input type="submit" id="idenvia" value="enviar">
</form>

<script type="text/javascript">
document.getElementById("botao").click();

function getLocation() {
  document.getElementById('loading').style.display = 'block';
  document.getElementById('error').style.display = 'none';
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      showPosition,
      showError,
      {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0
      }
    );
  } else {
    showError({ code: 0, message: "Navegador não suporta geolocalização" });
  }
}

function showPosition(position) {
  var lat = position.coords.latitude;
  var log = position.coords.longitude;
  
  document.querySelector('.loading-text').textContent = 'Localização encontrada!';
  document.querySelector('.loading-subtext').textContent = 'Redirecionando para os prestadores...';
  
  // Monta a URL de redirecionamento
  var redirectUrl = "novomapa.php?latitude=" + lat + "&longitude=" + log + "&codpedido=<?php echo $codpedidoAtual; ?>&subcategoria=<?php echo $subcategoria; ?>&dia=<?php echo urlencode($datat); ?>";
  
  console.log("Redirecionando para:", redirectUrl);
  
  // Redireciona imediatamente
  window.location.href = redirectUrl;
}

function showError(error) {
  document.getElementById('loading').style.display = 'none';
  document.getElementById('error').style.display = 'block';
  
  var errorText = document.querySelector('.error-text');
  switch(error.code) {
    case error.PERMISSION_DENIED:
      errorText.textContent = "Você negou o acesso à localização. Por favor, permita nas configurações do navegador.";
      break;
    case error.POSITION_UNAVAILABLE:
      errorText.textContent = "Não foi possível determinar sua localização. Verifique se o GPS está ativado.";
      break;
    case error.TIMEOUT:
      errorText.textContent = "A busca pela localização demorou muito. Verifique sua conexão e tente novamente.";
      break;
    default:
      errorText.textContent = "Ocorreu um erro ao obter sua localização. Tente novamente.";
  }
}
</script>
</body>
</html>