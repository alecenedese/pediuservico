<?php
$base_url = 'https://gessomt.app.br/pediuservico/adm/';
if (isset($url[1])) {
    if ($url[0] === 'categorias' && is_numeric($url[1])) {
        mysqli_query($con, "DELETE FROM categoria WHERE codigo='" . mysqli_real_escape_string($con, $url[1]) . "'");
        echo "<script>alert('Deletado com sucesso'); window.location.href='" . $base_url . "categorias';</script>";
        exit;
    } elseif ($url[1] === 'grupo' && isset($url[2]) && is_numeric($url[2])) {
        // No action needed for group filter, just proceed to display filtered categories
    }
}
?>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>
const BASE_URL = '<?php echo $base_url; ?>';

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

function redirectToGroup(groupId) {
    // Vai para rota de filtro por grupo (evita rota de delete)
    if (!groupId) {
        window.location.href = BASE_URL + 'categorias';
    } else {
        window.location.href = BASE_URL + 'categorias/grupo/' + groupId;
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
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Categorias</h4>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <input style="width: 70%;" type="text" id="myInput" class="form-control me-2" onkeyup="myFunction()" placeholder="Buscar" />
                    <select class="form-select" style="width: 25%;" name="grupo" onchange="redirectToGroup(this.value)">
                        <option value="">Todos os Grupos</option>
                        <?php
                        $grupos = mysqli_query($con, "SELECT codigo, titulo FROM grupos ORDER BY titulo");
                        while ($rowGrupo = mysqli_fetch_array($grupos)) {
                            $selected = (isset($url[2]) && $url[1] === 'grupo' && $url[2] == $rowGrupo['codigo']) ? 'selected' : '';
                            echo "<option value='{$rowGrupo['codigo']}' $selected>{$rowGrupo['titulo']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="table-responsive text-nowrap">
                    <table class="table" id="myTable">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Grupo</th>
                                <th>Valor Moeda</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                    
                        <?php
                        $query = "SELECT c.*, g.titulo AS grupo_titulo FROM categoria c JOIN grupos g ON c.codgrupo = g.codigo";
                        if (isset($url[1]) && $url[1] === 'grupo' && isset($url[2]) && is_numeric($url[2])) {
                            $grupo = mysqli_real_escape_string($con, $url[2]);
                            $query .= " WHERE c.codgrupo = '$grupo'";
                        }
                        $query .= " ORDER BY c.titulo ASC";
                        $lista = mysqli_query($con, $query);
                        while ($row = mysqli_fetch_array($lista)) {
                        ?>
                            <tr>
                                <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <?php echo $row['titulo']; ?></td>
                                <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?php echo $row['grupo_titulo']; ?></strong></td>
                                <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <?php echo $row['moeda']; ?></td>
                                <td>
                                    <a href="<?php echo $base_url; ?>editar-categoria/<?php echo $row['codigo']; ?>" class="btn btn-sm btn-primary me-1"><i class="bx bx-edit-alt"></i> Editar</a>
                                    <a href="<?php echo $base_url; ?>categorias/<?php echo $row['codigo']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja deletar esta categoria?')"><i class="bx bx-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="buy-now">
    <a href="<?php echo $base_url; ?>cadastrar-categoria" class="btn btn-danger btn-buy-now">Nova Categoria</a>
</div>