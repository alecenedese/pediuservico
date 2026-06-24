<?php 
 header("Content-Type: text/html; charset=utf-8",true);

if($_POST['acao']=="enviar") { 
    $grupo = $_POST['grupo'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $moeda = $_POST['moeda'] ?? '';
    $proxima_fase = isset($_POST['proxima_fase']) ? 1 : 0;

    $queryEnvio = mysqli_query($con, "INSERT INTO categoria (codgrupo, titulo, moeda, proxima_fase) VALUES
	('$grupo', '$titulo', '$moeda', '$proxima_fase')") or die(mysqli_error());

    echo "<script> window.location.href='https://gessomt.app.br/pediuservico/adm/categorias';</script>";
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
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Categoria /</span> Novo Registro</h4>

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
                      <div class="mb-3 col-md-3 col-sm-3">
                        <label for="exampleFormControlSelect1" class="form-label">Grupo</label>

                        <select class="form-select" id="exampleFormControlSelect1" name="grupo" aria-label="Default select example" required>
                          <option >Selecione..</option>
                        
                            <?php
                            $lista = mysqli_query($con, "select * from grupos order by titulo asc");
                            while($row = mysqli_fetch_array($lista) ) {
                            ?>
                                <option value="<?php echo $row['codigo'] ?>" ><?php echo $row['titulo'] ?></option>
                            <?php } ?>
                        </select>
                      </div>

 
                      <div class="mb-3 col-md-12 col-sm-12">
                      </div>  
                      

                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Titulo</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="titulo" placeholder="Categoria" />
                        </div>

                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Moeda</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="moeda" placeholder="Categoria" />
                        </div>

                        <div class="mb-3 col-md-4 col-sm-4">
                          <label class="form-label" for="proxima_fase">Disponível na próxima fase</label>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="proxima_fase" id="proxima_fase" value="1">
                            <label class="form-check-label" for="proxima_fase">[x] Disponível para a próxima fase</label>
                          </div>
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