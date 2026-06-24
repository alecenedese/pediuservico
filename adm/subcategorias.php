  <!-- Layout container -->
  <div class="layout-page">
          <!-- Navbar -->

          <?php require_once("nav-topo.php"); ?>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Sub-Categoria</h4>
              <div class="card">
                <h5 class="card-header"></h5>
                <div class="table-responsive text-nowrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Grupo</th>
                        <th>Categoria</th>

                        <th>Ação</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">

                    <?php
                      $lista = mysqli_query($con, "select * from subcategoria");
                      while($row = mysqli_fetch_array($lista) ) {
                    ?>
                      <tr>
                      <?php
                      $grupos = mysqli_query($con, "select * from grupos where codigo='".$row['codgrupo']."'");
                      while($row1 = mysqli_fetch_array($grupos) ) {
                    ?>      
                        <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?php echo $row1['titulo'];?></strong></td>
                        <?php } ?>

                        <?php
                      $grupos = mysqli_query($con, "select * from categoria where codigo='".$row['codcategoria']."'");
                      while($row1 = mysqli_fetch_array($grupos) ) {
                    ?>      
                        <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?php echo $row1['titulo'];?></strong></td>
                        <?php } ?>


                      <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <?php echo $row['titulo'];?></td>

                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                              <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="javascript:void(0);"
                                ><i class="bx bx-edit-alt me-1"></i> Edit</a
                              >
                              <a class="dropdown-item" href="javascript:void(0);"
                                ><i class="bx bx-trash me-1"></i> Delete</a
                              >
                            </div>
                          </div>
                        </td>
                      </tr>
                      <?php } ?>
                      
                    </tbody>
                  </table>
                </div>
              </div>
             

              </div>
              </div>
              </div>
              </div>   
              </div>  
              <div class="buy-now">
      <a
        href="cadastrar-novo"
        class="btn btn-danger btn-buy-now"
        >Cadastrar novo</a
      >
    </div>