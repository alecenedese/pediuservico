<?php
// Handle category deletion
if (isset($url[1])) {
    $rowCat = mysqli_fetch_array(mysqli_query($con, "DELETE FROM categoria WHERE codigo='" . mysqli_real_escape_string($con, $url[1]) . "'"));
    echo "<script>alert('Deletado com sucesso'); window.location.href='https://gessomt.app.br/pediuservico/adm/categorias';</script>";
}

// Handle form submission for linking provider to category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codcadastro'], $_POST['codsubcategoria'])) {
    $codcadastro = mysqli_real_escape_string($con, $_POST['codcadastro']);
    $codsubcategoria = mysqli_real_escape_string($con, $_POST['codsubcategoria']);

    // Busca o codgrupo (codcategoria) da subcategoria
    $queryCodGrupo = mysqli_query($con, "SELECT codgrupo FROM categoria WHERE codigo = '$codsubcategoria'");
    $codcategoria = 0;
    if ($queryCodGrupo && $rowGrupo = mysqli_fetch_array($queryCodGrupo)) {
        $codcategoria = $rowGrupo['codgrupo'];
    }
    
    // Check if the provider is already linked to the category
    $check = mysqli_query($con, "SELECT * FROM categoria_prestador WHERE codcadastro = '$codcadastro' AND codsubcategoria = '$codsubcategoria'");
    if (mysqli_num_rows($check) == 0) {
        // Insert the new link
        mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) VALUES ('$codcadastro', '$codcategoria', '$codsubcategoria')");
        echo "<script>alert('Prestador vinculado à categoria com sucesso!'); window.location.href=window.location.href;</script>";
    } else {
        echo "<script>alert('Prestador já está vinculado a esta categoria!');</script>";
    }
}

// Handle new category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'], $_POST['codgrupo'])) {
    $new_category = mysqli_real_escape_string($con, $_POST['new_category']);
    $codgrupo = mysqli_real_escape_string($con, $_POST['codgrupo']);
    
    // Insert new category
    mysqli_query($con, "INSERT INTO categoria (titulo, codgrupo) VALUES ('$new_category', '$codgrupo')");
    $new_category_id = mysqli_insert_id($con);
    
    // Link provider to new category if codcadastro is provided
    if (isset($_POST['codcadastro_new'])) {
        $codcadastro = mysqli_real_escape_string($con, $_POST['codcadastro_new']);
        mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) VALUES ('$codcadastro', '$codgrupo', '$new_category_id')");
        echo "<script>alert('Nova categoria criada e vinculada com sucesso!'); window.location.href=window.location.href;</script>";
    } else {
        echo "<script>alert('Nova categoria criada com sucesso!'); window.location.href=window.location.href;</script>";
    }
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
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Gerenciar Categorias de Prestadores</h4>

            <!-- Form to select provider and link to category -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Vincular Prestador a Categoria</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Selecionar Prestador</label>
                            <select class="form-select" name="codcadastro" required>
                                <option value="">Selecione um prestador</option>
                                <?php
                                $prestadores = mysqli_query($con, "SELECT id, NOME FROM parceiro ORDER BY NOME");
                                while ($rowPrestador = mysqli_fetch_array($prestadores)) {
                                    echo "<option value='{$rowPrestador['id']}'>{$rowPrestador['NOME']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Selecionar Categoria Existente</label>
                            <select class="form-select" name="codsubcategoria">
                                <option value="">Selecione uma categoria</option>
                                <?php
                                $categorias = mysqli_query($con, "SELECT c.codigo, c.titulo, g.titulo AS grupo FROM categoria c JOIN grupos g ON c.codgrupo = g.codigo ORDER BY c.titulo");
                                while ($rowCategoria = mysqli_fetch_array($categorias)) {
                                    echo "<option value='{$rowCategoria['codigo']}'>{$rowCategoria['titulo']} ({$rowCategoria['grupo']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Vincular</button>
                    </form>
                </div>
            </div>

            <!-- Form to create new category -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Criar Nova Categoria</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nome da Nova Categoria</label>
                            <input type="text" class="form-control" name="new_category" required placeholder="Digite o nome da nova categoria">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grupo</label>
                            <select class="form-select" name="codgrupo" required>
                                <option value="">Selecione um grupo</option>
                                <?php
                                $grupos = mysqli_query($con, "SELECT codigo, titulo FROM grupos ORDER BY titulo");
                                while ($rowGrupo = mysqli_fetch_array($grupos)) {
                                    echo "<option value='{$rowGrupo['codigo']}'>{$rowGrupo['titulo']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vincular a Prestador (Opcional)</label>
                            <select class="form-select" name="codcadastro_new">
                                <option value="">Nenhum prestador</option>
                                <?php
                                $prestadores = mysqli_query($con, "SELECT id, NOME FROM parceiro ORDER BY NOME");
                                while ($rowPrestador = mysqli_fetch_array($prestadores)) {
                                    echo "<option value='{$rowPrestador['id']}'>{$rowPrestador['NOME']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar e Vincular</button>
                    </form>
                </div>
            </div>

            <!-- Table to list categories and linked providers -->
            <div class="card">
                <div class="card-header">
                    <input style="width: 100%;" type="text" id="myInput" class="form-control" onkeyup="myFunction()" placeholder="Buscar">
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table" id="myTable">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Total</th>
                                <th>Grupo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $lista = mysqli_query($con, "SELECT DISTINCT cp.codsubcategoria, COUNT(*) AS total, c.titulo AS titulo, g.titulo AS grupo FROM categoria_prestador cp
                                JOIN categoria c ON c.codigo = cp.codsubcategoria
                                JOIN grupos g ON g.codigo = c.codgrupo
                                GROUP BY cp.codsubcategoria ORDER BY total DESC");
                            while ($row = mysqli_fetch_array($lista)) {
                            ?>
                                <tr>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <?php echo $row['titulo']; ?>
                                            </button>
                                            <div class="dropdown-menu">
                                                <?php
                                                $listaP = mysqli_query($con, "SELECT cp.codcadastro, p.NOME, p.CNPJ_CPF, p.CELULAR FROM parceiro p
                                                    JOIN categoria_prestador cp ON p.id = cp.codcadastro WHERE cp.codsubcategoria = '{$row['codsubcategoria']}'");
                                                while ($rowCad = mysqli_fetch_array($listaP)) {
                                                ?>
                                                    <a class="dropdown-item" style="border-bottom:1px solid #ccc; color:#000 !important;">
                                                        <strong><?php echo $rowCad['NOME']; ?></strong> - <?php echo $rowCad['CELULAR']; ?>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight:bold;"><?php echo $row['total']; ?></td>
                                    <td><?php echo $row['grupo']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Display categories for selected provider -->
            <?php if (isset($_POST['codcadastro']) && !empty($_POST['codcadastro'])) {
                $selected_prestador = mysqli_real_escape_string($con, $_POST['codcadastro']);
                $prestador_info = mysqli_fetch_array(mysqli_query($con, "SELECT NOME FROM parceiro WHERE id = '$selected_prestador'"));
            ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Categorias Vinculadas ao Prestador: <?php echo $prestador_info['NOME']; ?></h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php
                            $categorias_prestador = mysqli_query($con, "SELECT c.titulo, g.titulo AS grupo FROM categoria_prestador cp
                                JOIN categoria c ON c.codigo = cp.codsubcategoria
                                JOIN grupos g ON g.codigo = c.codgrupo
                                WHERE cp.codcadastro = '$selected_prestador'");
                            if (mysqli_num_rows($categorias_prestador) > 0) {
                                while ($rowCat = mysqli_fetch_array($categorias_prestador)) {
                                    echo "<li class='list-group-item'>{$rowCat['titulo']} ({$rowCat['grupo']})</li>";
                                }
                            } else {
                                echo "<li class='list-group-item'>Nenhuma categoria vinculada.</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>