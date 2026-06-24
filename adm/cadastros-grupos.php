<?php
if(isset($url[1])) {
    if (!isset($_GET['confirmar'])) {
        // Se não confirmou via JS, redireciona de volta
        echo "<script>window.location.href='cadastros-grupos';</script>";
        exit;
    }
    $rowCat = mysqli_fetch_array(mysqli_query($con, "delete from grupos where codigo='".$url[1]."'"));
    echo "<script>alert('Deletado com sucesso'); window.location.href='cadastros-grupos';</script>";
    exit;
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
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Grupos com cadastros</h4>
              <div class="card">
                <div class="card-header">
                <input style="float: left; width: 100%;" type="text" id="myInput" class="form-control" onkeyup="myFunction()" id="basic-default-fullname" name="moeda" placeholder="Buscar" />

                </div>
                
                <div class="table-responsive text-nowrap">
<table class="table" id="myTable">
                    <thead>
                      <tr>
                        <th>Nome do Grupo</th>
                        <th>Total de Prestadores</th>
                        <th>Categorias Próx. Fase</th>
                        <th>Ação</th>
                      </tr>
                    </thead>
                    <tbody> <!-- ANOTAÇÃO: Adicionado <tbody> para estrutura HTML correta -->
                 
                    <?php
                      $lista = mysqli_query($con, "SELECT
                          g.codigo AS codgrupo,
                          g.titulo AS grupo,
                          COUNT(DISTINCT cp.codcadastro) AS total_prestadores_vinculados,
                          SUM(CASE WHEN c.proxima_fase = 1 THEN 1 ELSE 0 END) AS categorias_proxima_fase
                      FROM
                          grupos g
                      LEFT JOIN
                          categoria c ON g.codigo = c.codgrupo
                      LEFT JOIN
                          categoria_prestador cp ON c.codigo = cp.codsubcategoria
                      GROUP BY
                          g.codigo, g.titulo
                      ORDER BY
                          total_prestadores_vinculados DESC, g.titulo ASC");

                      while($row = mysqli_fetch_array($lista)) {
                    ?>
                      <tr>
                        <td> 
                          <!-- O Dropdown mostra o nome do grupo -->
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                              <?php echo $row['grupo']; ?>
                            </button>
                            <div class="dropdown-menu">
                              <?php 
                                // --- ANOTAÇÃO: QUERY DO DROPDOWN CORRIGIDA ---
                                // Agora busca todos os prestadores únicos (DISTINCT)
                                // que estão em qualquer categoria que pertença a este grupo (c.codgrupo).
                                $codgrupo_seguro = mysqli_real_escape_string($con, $row['codgrupo']);
                                $listaP = mysqli_query($con, "SELECT DISTINCT p.NOME, p.CELULAR 
                                    FROM parceiro p
                                    JOIN categoria_prestador cp ON p.id = cp.codcadastro
                                    JOIN categoria c ON cp.codsubcategoria = c.codigo
                                    WHERE c.codgrupo = '$codgrupo_seguro'
                                    ORDER BY p.NOME ASC");
                                
                                if (mysqli_num_rows($listaP) > 0) {
                                  while($rowCad = mysqli_fetch_array($listaP)) {
                              ?>     
                                  <a class="dropdown-item" style="border-bottom:1px solid #ccc; color:#000 !important;">
                                    <strong><?php echo $rowCad['NOME'] ?></strong> - <?php echo $rowCad['CELULAR'] ?>
                                  </a>
                              <?php 
                                  } // Fim do while dos prestadores
                                } else {
                              ?>
                                  <span class="dropdown-item text-muted">Nenhum prestador vinculado a este grupo.</span>
                              <?php
                                } // Fim do if
                              ?>       
                            </div>
                          </div>
                        </td>
                        
                        <!-- ANOTAÇÃO: Usando a coluna correta "total_prestadores_vinculados" -->
                        <td style="font-weight:bold;"><?php echo $row['total_prestadores_vinculados']; ?></td>
                        <td><?php 
                            $nf = (int)$row['categorias_proxima_fase'];
                            if ($nf > 0) {
                                echo '<span class="badge bg-warning text-dark">'.$nf.' cat. próx. fase</span>';
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                        ?></td>
                      
                        <td>
                          <a href="categorias/grupo/<?php echo $row['codgrupo']; ?>" class="btn btn-sm btn-info me-1"><i class="bx bx-list-ul"></i> Categorias</a>
                          <a href="editar-grupo/<?php echo $row['codgrupo']; ?>" class="btn btn-sm btn-primary me-1"><i class="bx bx-edit-alt"></i> Editar</a>
                          <a href="cadastros-grupos/<?php echo $row['codgrupo']; ?>?confirmar=1" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja deletar este grupo?')"><i class="bx bx-trash"></i> Delete</a>
                        </td>
                      </tr>
                    <?php } // Fim do while principal ?>
                      
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
    <a href="cadastrar-grupo" class="btn btn-danger btn-buy-now">Novo Grupo</a>
</div>