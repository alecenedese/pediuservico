<?php
if(isset($url[1])) {
$rowCat = mysqli_fetch_array(mysqli_query($con, "delete from categoria where codigo='".$url[1]."'"));
echo "<script>alert('Deletado com sucesso'); window.location.href='https://gessomt.app.br/pediuservico/adm2/categorias';</script>";
}
 ?>

<script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
	td = tr[i].getElementsByTagName("td")[0];
	if (td) {
	  txtValue = td.textContent || td.innerText;
	  if (txtValue.toUpperCase().indexOf(filter) > -1) {
		tr[i].style.display = "";
	  } else {
		tr[i].style.display = "none";
	  }
	}       
  }
}
</script>
 

 
 <!-- Layout container -->
  <div class="layout-page">
          <!-- Navbar -->

          <?php require_once("nav-topo.php"); ?>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Categorias com cadastros</h4>
              <div class="card">
                <div class="card-header">
                <input style="float: left; width: 100%;" type="text" id="myInput" class="form-control" onkeyup="myFunction()" id="basic-default-fullname" name="moeda" placeholder="Buscar" />

                </div>
                
                <div class="table-responsive text-nowrap">
                  <table class="table " id="myTable" >
                    <thead>
                      <tr>
                      <th>Nome</th>
                        
                        <th>Total</th>
                        <th>Grupo</th>
                      </tr>
                    </thead>
                 
                    <?php
                      $lista = mysqli_query($con, "SELECT DISTINCT cp.codsubcategoria, COUNT(*) AS total, cp.codcadastro, c.titulo AS titulo, g.titulo AS grupo FROM categoria_prestador cp
                      JOIN categoria c ON c.codigo = cp.codsubcategoria
                      JOIN grupos g ON g.codigo = c.codgrupo
                      GROUP BY cp.codsubcategoria order by 2 desc");
                      while($row = mysqli_fetch_array($lista) ) {
                    ?>
                      <tr>

                      <td> 

                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <?php echo $row['titulo'];?>
                            </button>
                            <div class="dropdown-menu">

                              <?php 
                                $listaP = mysqli_query($con, "SELECT cp.codcadastro, p.NOME, p.CNPJ_CPF, p.CELULAR from parceiro p
                                JOIN categoria_prestador cp ON p.id = cp.codcadastro WHERE cp.codsubcategoria = '".$row['codsubcategoria']."'");
                                while($rowCad = mysqli_fetch_array($listaP) ) {
                              ?>     
                              <a class="dropdown-item" style="border-bottom:1px solid #ccc; color:#000 !important;"><strong><?php echo $rowCad['NOME'] ?></strong> - <?php echo $rowCad['CELULAR'] ?></a>
                             <?php } ?>       
                            </div>
                          </div>

                    </td>
                      <td style="font-weight:bold;"><i class="fab fa-angular fa-lg text-danger me-3"></i> <?php echo $row['total'];?></td>
                      <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <?php echo $row['grupo'];?></td>
                      </tr>
                      <?php } ?>
                      
                
                    
                  </table>
                </div>
              </div>
             

              </div>
              </div>
              </div>
              </div>   
              </div>  