<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Anti-cache para evitar tela branca ao clicar "Voltar"
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once("send.php");

// Se não tem os parâmetros mínimos, redireciona de volta
if (empty($_GET['codpedido'])) {
    echo "<script>window.location.href='meus-orcamentos.php';</script>";
    exit;
}

// Botão "Voltar" deve ir para a lista de pedidos novos (evita voltar para a tela
// de geolocalização que recarregaria e causaria erro/loop)
$voltarUrl = 'meus-orcamentos.php';

function haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
    // Converte de graus para radianos
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
    
    // Calcula a diferença
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    // Fórmula de Haversine
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius; // Distância em km
    }
    
    function isWithinRadius($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $radius = 25) {
    $distance = haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo);
    return $distance <= $radius;
    }

    $queryMoedas2 = mysqli_query($con, "select * from markers where type = '3' and codpedido='".$_GET['codpedido']."'");
    $rowcadend = mysqli_fetch_array($queryMoedas2);

    $latitudeCentral = $_GET['latitude'];  // Latitude do ponto central
    $longitudeCentral = $_GET['longitude']; // Longitude do ponto central
    $latitudeDestino = $rowcadend['lat'];  // Latitude do ponto de destino
    $longitudeDestino = $rowcadend['lon']; // Longitude do ponto de destino
    $radius = 25;                   // Raio em km

  //  if (isWithinRadius($latitudeCentral, $longitudeCentral, $latitudeDestino, $longitudeDestino, $radius)) {


if(isset($_POST['acao'])) {
 
    $queryEdit = mysqli_query($con, "select * from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
    $rowEdit = mysqli_fetch_array($queryEdit);

    $deleVermelho = mysqli_query($con, "DELETE from markers WHERE type = '1' and codpedido = '".$_GET['codpedido']."' and codcadastro = '".$rowEdit['id']."'") or die(mysqli_error($con));

// verificar se nao tem avaliacao
$queryAvl = mysqli_query($con, "select COUNT(id) AS conta, SUM(qtd_estrela) AS qtd from avaliacoes where codcadastro='".$rowEdit['id']."'");
$rowAvl = mysqli_fetch_array($queryAvl);
if($rowAvl['conta'] > 0) {
    $contaestrelas = intval($rowAvl['qtd'] / $rowAvl['conta']);
  } else { $contaestrelas = 1; }
  
    $queryPedi = mysqli_query($con, "INSERT INTO markers (nome, codcadastro, valor_min, valor_max, lat, lon, type, codpedido, qtdestrelas, contraproposta) VALUES
    ('".$rowEdit['NOME']."', '".$rowEdit['id']."', '".$_POST['minimo']."', '".$_POST['maximo']."', '".$_GET['latitude']."', '".$_GET['longitude']."', '2', '".$_GET['codpedido']."', '$contaestrelas', '".$_POST['contraproposta']."')") or die(mysqli_error($con));

$editaPedidoPedi = mysqli_query($con, "update pedido set status='Proposta Aceita' where codigo = '".$_GET['codpedido']."'") or die(mysqli_error($con));

$editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='a', visto=0 where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$rowEdit['id']."'") or die(mysqli_error($con));

echo "<script>window.location.href='".$urlserver."meus-orcamentos-aguardando.php?enviado=1';</script>";
exit;

}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Enviar Proposta - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8c 50%, #1e3a5f 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Arial', sans-serif;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 65px;
        }
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
        }
        .page-title {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            margin-bottom: 16px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
            align-items: end;
        }
        .form-group { display: flex; flex-direction: column; margin-bottom: 14px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            font-size: 15px;
            color: #ffffff;
            background: rgba(255,255,255,0.12);
            font-family: inherit;
            transition: all 0.2s;
        }
        .form-input::placeholder { color: rgba(255,255,255,0.4); }
        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            background: rgba(255,255,255,0.18);
        }
        textarea.form-input {
            resize: none;
            height: 100px;
        }
        .erro-msg { 
            color: #ff4444; 
            font-size: 13px; 
            margin-top: 8px; 
            padding: 10px;
            background: rgba(255, 68, 68, 0.1);
            border-left: 3px solid #ff4444;
            border-radius: 4px;
            display: none;
        }
        .submit-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
            box-shadow: 0 4px 15px rgba(0,188,212,0.4);
            transition: all 0.2s;
        }
        .submit-button:active { transform: scale(0.98); }
        .submit-button svg { width: 20px; height: 20px; }
    </style>
</head>
<body>

<?php include('header-app.php'); ?>

<div class="content-area">
    <div class="page-title">Enviar Proposta</div>
    <form action="" method="post">
            <input type="hidden" name="acao" value="envia">

            <div class="form-row">
                <div class="form-group" style="margin-bottom:0">
                    <label for="minimo" class="form-label">Valor Mínimo Oferecido</label>
                    <input type="tel" id="minimo" class="form-input" onKeyPress="return(MascaraMoeda(this,'.',',',event))" name="minimo" placeholder="R$ 0,00" required>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label for="maximo" class="form-label">Valor Máximo</label>
                    <input type="tel" id="maximo" class="form-input" onKeyPress="return(MascaraMoeda(this,'.',',',event))" name="maximo" placeholder="R$ 0,00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="contraproposta" class="form-label">Observação</label>
                <textarea class="form-input" id="contraproposta" name="contraproposta" oninput="validarContraproposta(this)"></textarea>
                <span class="erro-msg" id="erroMensagem"></span>
            </div>

            <button type="submit" class="submit-button" onclick="return validarMinMax() && validarContraproposta(document.getElementById('contraproposta'))">>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Confirmar
            </button>
        </form>
</div>

<?php $navAtiva = 'servicos'; include('bottom-nav.php'); ?>

<script>
// Item 6 + Item 9: Validação robusta contra números de telefone
function validarContraproposta(textarea) {
    const erroMensagem = document.getElementById('erroMensagem');
    const texto = textarea.value;
    
    // Item 9: Conta total de dígitos na mensagem (ignorando espaços e separadores)
    const totalDigitos = (texto.match(/\d/g) || []).length;
    if (totalDigitos > 5) {
        erroMensagem.innerHTML = '<strong style="color:#ff4444;">❌ Muitos números detectados!</strong><br>* Tentativa de passar o número através de mensagem pode acarretar banimento';
        erroMensagem.style.display = 'block';
        return false;
    }
    
    // Regex para detectar telefones formatados
    const regexTelefone = [
        /\b(\(\d{2}\)\s?\d{4,5}[-\s]?\d{4})\b/gi,
        /\b\d{2}\s?\d{4,5}[-\s]?\d{4}\b/gi,
        /\b\d{10,11}\b/gi,
        /\b\d{5}[-\s]\d{4}\b/gi,
        /\b\d{4}[-\s]\d{4}\b/gi,
        /\b\d{2}\s\d{5}\s\d{4}\b/gi,
        /\d{1,2}[-\s]\d{4}[-\s]\d{4}/gi
    ];
    
    for (let regex of regexTelefone) {
        if (regex.test(texto)) {
            erroMensagem.innerHTML = '<strong style="color:#ff4444;">❌ Número de telefone detectado!</strong><br>* Tentativa de passar o número através de mensagem pode acarretar banimento';
            erroMensagem.style.display = 'block';
            return false;
        }
    }
    
    erroMensagem.textContent = '';
    erroMensagem.style.display = 'none';
    return true;
}

function parseMoeda(val) {
    return parseFloat(val.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
}

function validarMinMax() {
    var minVal = parseMoeda(document.getElementById('minimo').value);
    var maxVal = parseMoeda(document.getElementById('maximo').value);
    if (maxVal < minVal) { alert('O valor máximo não pode ser menor que o valor mínimo!'); return false; }
    if (minVal <= 0 || maxVal <= 0) { alert('Os valores devem ser maiores que zero!'); return false; }
    return true;
}

function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e){
    var sep = 0; var key = ''; var i = j = 0; var len = len2 = 0;
    var strCheck = '0123456789'; var aux = aux2 = '';
    var whichCode = (window.Event) ? e.which : e.keyCode;
    if (whichCode == 13) return true;
    key = String.fromCharCode(whichCode);
    if (strCheck.indexOf(key) == -1) return false;
    len = objTextBox.value.length;
    for(i = 0; i < len; i++)
        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
    aux = '';
    for(; i < len; i++)
        if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) aux += objTextBox.value.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) objTextBox.value = '';
    if (len == 1) objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
    if (len == 2) objTextBox.value = '0'+ SeparadorDecimal + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) { aux2 += SeparadorMilesimo; j = 0; }
            aux2 += aux.charAt(i); j++;
        }
        objTextBox.value = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--) objTextBox.value += aux2.charAt(i);
        objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
    }
    return false;
}
</script>
</body>
</html>
