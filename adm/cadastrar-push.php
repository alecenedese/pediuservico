<?php 
 header("Content-Type: text/html; charset=utf-8",true);

if($_POST['acao']=="enviar") { 

$message = $titulo;
$title = $mensagem;
$path_to_fcm = 'https://fcm.googleapis.com/fcm/send';
$server_key = "AAAAgrLJAno:APA91bFluRVdlY_y3JnrsnU4-VQkwmVZUYI5GmMscC14G_RPPNWCY_uzNFqWFGRBQXtXFAg9hWw8GEHuNjrBAUPOVpuNhRpUlScDkYvxBL5c1BDRwGEw_M7_JBiA1a-KamvfFU1cLjHs";


$listaGSub = mysqli_query($con, "select * from tokens") or die(mysqli_error($con));
while($rowsub = mysqli_fetch_array($listaGSub)) {


     
    $deviceToken = $rowsub['token'];
    $headers = array(
        'Authorization:key=' .$server_key,
        'Content-Type:application/json'
    );

    $fields = array('to'=>$deviceToken,
        'notification'=>array('title'=>$title,'body'=>$message));

    $payload = json_encode($fields);


    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_URL, $path_to_fcm);
    curl_setopt($curl_session, CURLOPT_POST, true);
    curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
    $result = curl_exec($curl_session);



}

    echo "<script>alert('Mensagens Enviadas'); window.location.href='https://gessomt.app.br/pediuservico/adm2/cadastrar-push';</script>";
}
?>

  <!-- Layout container -->
  <div class="layout-page">
          <!-- Navbar -->

          <?php require_once("nav-topo.php"); ?>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mensagem Push /</span> Novo Registro</h4>

              <!-- Basic Layout -->
              <div class="row">
                <div class="col-xs">
                  <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0"></h5>
                 
                    </div>
                    <div class="card-body">
                      <form action="" method="post" class="row">

                      <input type="hidden" name="acao"value="enviar">

 
                      <div class="mb-3 col-md-12 col-sm-12">
                      </div>  
                      


                        <div class="mb-8 col-md-8 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Titulo</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="titulo" placeholder="" />
                        </div>

                        
                        <div class="mb-12 col-md-12 col-sm-12 mt-2">
                          <label class="form-label" for="basic-default-fullname">Mensagem</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="mensagem" placeholder="" />
                        </div>

                        <div class="mb-3 col-md-12 col-sm-12"></div>                       

                        <button type="submit" class="btn btn-primary mb-3 col-md-3 col-sm-3">Enviar</button>
                      </form>
                    </div>
                  </div>
                </div>
                
              </div>
            

              </div>
              </div>
              </div>
              </div>    