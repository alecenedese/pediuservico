<?php


if($_GET['acao']=="deletar") {

  $codigo = $_GET['id'];
  $delete = mysqli_query($con, "DELETE FROM parceiro WHERE id='$codigo'");

    echo "<script>alert('Registro deletado com susseso!'); window.location.href='listar-cadastros';</script>";

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
                      
                        <th>Nome</th>    
                        <th>Celular</th>                 
                        <th>Data Cad</th>
                        <th>Não Encontrou</th>
                      </tr>
                    </thead>

                    <?php
                      $lista = mysqli_query($con, "select * from parceiro where serviconao<>'' order by NOME asc");
                      while($row = mysqli_fetch_array($lista) ) {
                    ?>
                      <tr>
                      
                        <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?php echo $row['NOME'];?></strong></td>
                        <td><?php echo $row['CELULAR']; ?></td>

                         <td><?php echo Data($row['dataCad']); ?></td>
                         <td><?php echo $row['serviconao']; ?></td>


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
        href="https://gessomt.app.br/pediuservico/adm2/cadastrar-categoria"
        class="btn btn-danger btn-buy-now"
        >Cadastrar novo</a
      >
    </div>