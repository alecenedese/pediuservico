<?php 
 header("Content-Type: text/html; charset=utf-8",true);

if($_POST['acao']=="enviar") { 
    $queryEnvio = mysqli_query($con, "INSERT INTO grupos (titulo) VALUES
	('$titulo')") or die(mysqli_error());

    echo "<script> window.location.href='cadastros-grupos';</script>";
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
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Grupos /</span> Novo Registro</h4>

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
                      

                        <div class="mb-6 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Titulo</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="titulo" placeholder="Grupo" />
                        </div>


                        <div class="mb-3 col-md-12 col-sm-12"></div>                       

                        <button type="submit" class="btn btn-primary mb-3 col-md-3 col-sm-3">Salvar</button>
                      </form>
                    </div>
                  </div>
                </div>
                
              </div>
            

              </div>
              </div>
              </div>
              </div>    