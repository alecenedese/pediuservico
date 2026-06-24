<?php 
 require_once("send.php"); 
 header("Content-Type: text/html; charset=utf-8",true);
if($_GET['acao']=="deletar") {

  $codigo = $_GET['codigo'];

  $delete = mysqli_query($con, "DELETE FROM parceiro WHERE id='$codigo'") or die(mysqli_error($con));
  $delete2 = mysqli_query($con, "DELETE FROM categoria_prestador WHERE codcadastro='$codigo'")or die(mysqli_error($con));

    echo "<script>alert('Registro deletado com susseso!'); window.location.href='https://gessomt.app.br/pediuservico/adm2/listar-cadastros';</script>";

}
?>
  <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
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
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Cadastros</h4>
              <div class="card">
              <div class="card-header">
                <input style="float: left; width: 100%;" type="text" id="myInput" class="form-control" onkeyup="myFunction()" id="basic-default-fullname" name="moeda" placeholder="Buscar" />

                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table" id="myTable">
                    <thead>
                      <tr>
                      <th>CPF - CNPJ</th>
                        <th>Nome</th>                     
                        <th>Data Cad</th>
                        <th>Ultm. Acesso</th>
                        <th>Ação</th>
                      </tr>
                    </thead>

                    <?php
                      $lista = mysqli_query($con, "select * from clientes order by codigo desc");
                      while($row = mysqli_fetch_array($lista) ) {
                    ?>
                      <tr>
                      <td><?php echo $row['cpf_cnpj']; ?></td>
                        <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?php echo $row['nome'];?></strong></td>
                         <td><?php echo Data($row['dataCad']); ?></td>
                         <td><?php echo Data($row['ultimoAcesso']); ?></td>
                        <td>
                          <div class="dropdown dropup">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow dropup" data-bs-toggle="dropdown">
                              <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="editar-cadastro/<?php echo $row['id'] ?>"
                                ><i class="bx bx-edit-alt me-1"></i> Editar</a
                              >
                             <a class="dropdown-item" href="listar-cadastros.php?acao=deletar&codigo=<?php echo $row['id'] ?>"
                                ><i class="bx bx-trash me-1"></i> Deletar</a
                              >
                            </div>
                          </div>
                        </td>
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
              <div class="buy-now">
      <a
        href="https://gessomt.app.br/pediuservico/adm2/cadastrar-novo"
        class="btn btn-danger btn-buy-now"
        >Cadastrar novo</a
      >
    </div>