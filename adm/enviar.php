<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$url = "http://api.wordmensagens.com.br/send-doc";
require_once("send.php");
$pedido = $_GET['codpedido'];
function limpar_texto($str){ 
    return preg_replace("/[^0-9]/", "", $str); 
    }

    $verificar = mysqli_query($conn, "SELECT * FROM propostas where id='$pedido'  order by id desc");
$row = mysqli_fetch_assoc($verificar);
       $clientess = mysqli_query($conn, "SELECT * FROM cadastro where codigo='".$row["cliente"]."'");
       $rowcli = mysqli_fetch_assoc($clientess);
$nomecliente = $rowcli["nome"];
$kwh = $row["consumo"];

    $pdf_file = 'pdf/'.$nomecliente.'-'.$kwh.'KWH.pdf';
    $numerolimpo = limpar_texto($_GET['celular']);
    $data = array('instance' => "B9A170425113047OWN742",
                  'to' => "55$numerolimpo",
                  'token' => "3P0QN-1ZA-037N0",
    'message' => "",
    'url' => "https://cliente.energysolarmt.com.br/".$pdf_file);
  
  $curl = curl_init();
  
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://api.wordmensagens.com.br/send-docnew',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $data,
  ));
  
  $response = curl_exec($curl);
  
  curl_close($curl);
  
  $response = json_decode($response, true);
  
  if($response['erro'] == false){
    echo "Enviado com sucesso";
  }else if($response['erro'] == true){
    echo "Erro no Envio > ".$response['message'];
  }
  
 echo "<script>alert('Pediddo Enviado com sucesso'); window.close();</script>";

?>