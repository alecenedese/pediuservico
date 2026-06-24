<?php
// ATENÇÃO: Inclua aqui o seu arquivo de conexão com o banco de dados.
 require_once('send.php'); // <-- AJUSTE ESTA LINHA CONFORME NECESSÁRIO

// --- LÓGICA 1: ATUALIZAR VÍNCULOS EXISTENTES (CORRIGIDA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_provider_id']) && !empty($_POST['selected_provider_id'])) {
    $codcadastro = mysqli_real_escape_string($con, $_POST['selected_provider_id']);
    $codsubcategorias = isset($_POST['codsubcategoria']) ? $_POST['codsubcategoria'] : [];

    mysqli_begin_transaction($con);
    try {
        mysqli_query($con, "DELETE FROM categoria_prestador WHERE codcadastro = '$codcadastro'");
        
        if (!empty($codsubcategorias)) {
            $values = [];
            foreach ($codsubcategorias as $codsubcategoria) {
                // *** MUDANÇA IMPORTANTE: Buscar o codgrupo para esta subcategoria ***
                $query_grupo = "SELECT codgrupo FROM categoria WHERE codigo = '" . mysqli_real_escape_string($con, $codsubcategoria) . "' LIMIT 1";
                $result_grupo = mysqli_query($con, $query_grupo);
                $row_grupo = mysqli_fetch_assoc($result_grupo);
                $codgrupo = $row_grupo ? $row_grupo['codgrupo'] : 0; // Pega o codgrupo ou usa 0 como fallback

                // *** Adiciona o codgrupo (codcategoria) ao INSERT ***
                $values[] = "('$codcadastro', '" . mysqli_real_escape_string($con, $codsubcategoria) . "', '$codgrupo')";
            }
            // *** Atualiza a query de INSERT para incluir a nova coluna ***
            $insert_query = "INSERT INTO categoria_prestador (codcadastro, codsubcategoria, codcategoria) VALUES " . implode(", ", $values);
            mysqli_query($con, $insert_query);
        }
        
        mysqli_commit($con);
        echo "<script>alert('Vínculos do prestador atualizados com sucesso!'); window.location.href=window.location.href;</script>";
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($con);
        // Para depuração, é útil ver o erro exato:
        // echo "<script>alert('Ocorreu um erro: " . addslashes($exception->getMessage()) . "');</script>";
        echo "<script>alert('Ocorreu um erro ao atualizar os vínculos.');</script>";
    }
}

// --- LÓGICA 2: CRIAR NOVAS CATEGORIAS (CORRIGIDA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $new_categories = $_POST['new_category'];
    $codgrupos = $_POST['codgrupo'];
    $link_to_provider = isset($_POST['link_to_current_provider']) && !empty($_POST['current_provider_id_for_linking']);
    $provider_id_to_link = mysqli_real_escape_string($con, $_POST['current_provider_id_for_linking']);
    
    $success_count = 0;
    mysqli_begin_transaction($con);
    try {
        foreach ($new_categories as $index => $category_name) {
            if (!empty($category_name)) {
                $codgrupo = mysqli_real_escape_string($con, $codgrupos[$index]);
                $category_name_safe = mysqli_real_escape_string($con, $category_name);
                
                // 1. Insere a nova categoria (subcategoria)
                mysqli_query($con, "INSERT INTO categoria (titulo, codgrupo) VALUES ('$category_name_safe', '$codgrupo')");
                $new_category_id = mysqli_insert_id($con);

                // 2. Se a opção de vincular foi marcada e um prestador válido foi selecionado
                if ($link_to_provider && $new_category_id > 0) {
                    // *** MUDANÇA IMPORTANTE: Adiciona o $codgrupo (que é a categoria pai) ao INSERT ***
                    mysqli_query($con, "INSERT INTO categoria_prestador (codcadastro, codsubcategoria, codcategoria) VALUES ('$provider_id_to_link', '$new_category_id', '$codgrupo')");
                }
                $success_count++;
            }
        }
        mysqli_commit($con);
        if ($success_count > 0) {
            echo "<script>alert('$success_count categoria(s) criada(s) com sucesso!'); window.location.href=window.location.href;</script>";
        }
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($con);
        echo "<script>alert('Ocorreu um erro ao criar as categorias.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias de Prestadores</title>
    <style>
        body { font-family: sans-serif; }
        .card { border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
        .card-header { padding: 15px; background-color: #f7f7f7; border-bottom: 1px solid #ddd; }
        .card-body { padding: 15px; }
        .form-label { font-weight: bold; margin-bottom: 5px; display: block; }
        .form-control, .form-select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .row { display: flex; gap: 20px; margin-bottom: 15px; }
        .col-md-6 { flex: 1; } .mb-3 { margin-bottom: 15px; } .mt-3 { margin-top: 15px; }
        .text-muted { color: #6c757d; }
        .provider-search-container { position: relative; }
        #provider-search-results { position: absolute; top: 100%; left: 0; right: 0; border: 1px solid #ddd; background: #fff; z-index: 1000; max-height: 250px; overflow-y: auto; }
        .result-item { padding: 10px; cursor: pointer; } .result-item:hover { background-color: #f0f0f0; }
        #selected-provider-display { margin-top: 10px; font-weight: bold; color: #28a745; }
        .category-list-wrapper, .selected-categories-wrapper { border: 1px solid #ddd; padding: 10px; border-radius: 5px; min-height: 320px; }
        .category-list { max-height: 270px; overflow-y: auto; }
        .category-item { cursor: pointer; padding: 8px; margin-bottom: 5px; border-radius: 4px; transition: background-color 0.2s; }
        .category-item:hover { background-color: #f1f1f1; } .category-item.existing { background-color: #fff3cd; font-weight: bold; } .category-item.selected { background-color: #d1e7dd; }
        .selected-category { display: flex; justify-content: space-between; align-items: center; margin: 5px 0; padding: 5px 10px; background-color: #e9ecef; border-radius: 5px; }
        .selected-category.existing { background-color: #fff3cd; } .remove-category { cursor: pointer; margin-left: 10px; color: #dc3545; font-weight: bold; }
        .category-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .category-row .form-control { flex: 2; } .category-row .form-select { flex: 1; }
    </style>
</head>
<body>

<div class="layout-page">
    <?php // require_once("nav-topo.php"); ?>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4>Gerenciar Categorias de Prestadores</h4>

            <!-- CARD 1: VINCULAR PRESTADOR -->
            <div class="card">
                <div class="card-header"><h5>Vincular Prestador a Categorias</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">1. Buscar Prestador</label>
                            <div class="provider-search-container">
                                <input type="text" id="provider-search" class="form-control" placeholder="Digite o nome do prestador para buscar...">
                                <div id="provider-search-results"></div>
                            </div>
                            <div id="selected-provider-display"><span class="text-muted">Nenhum prestador selecionado.</span></div>
                            <input type="hidden" id="selected-provider-id" name="selected_provider_id">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">2. Todas as Categorias</label>
                                <div class="category-list-wrapper">
                                    <input type="text" class="form-control" id="category-search" placeholder="Buscar categorias...">
                                    <div class="category-list mt-3"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">3. Categorias Vinculadas</label>
                                <div class="selected-categories-wrapper" id="selected-categories-container"><p class="text-muted">Selecione um prestador para começar.</p></div>
                                <select multiple name="codsubcategoria[]" id="codsubcategoria_hidden" style="display: none;"></select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Salvar Vínculos do Prestador</button>
                    </form>
                </div>
            </div>

            <!-- CARD 2: CRIAR NOVAS CATEGORIAS -->
            <div class="card">
                <div class="card-header"><h5>Criar Novas Categorias</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div id="new-categories-container">
                            <div class="category-row">
                                <input type="text" name="new_category[]" class="form-control" placeholder="Nome da Nova Categoria" required>
                                <select name="codgrupo[]" class="form-select" required>
                                    <option value="" disabled selected>Selecione um Grupo</option>
                                    <?php
                                    $grupos = mysqli_query($con, "SELECT codigo, titulo FROM grupos ORDER BY titulo");
                                    while ($rowGrupo = mysqli_fetch_array($grupos)) {
                                        echo "<option value='{$rowGrupo['codigo']}'>{$rowGrupo['titulo']}</option>";
                                    }
                                    ?>
                                </select>
                                <button type="button" class="btn btn-danger remove-category-row" style="display:none;">-</button>
                            </div>
                        </div>
                        <button type="button" id="add-category-row" class="btn btn-success mt-3">+</button>

                        <div class="mt-3">
                            <input type="checkbox" id="link_to_current_provider" name="link_to_current_provider" disabled>
                            <label for="link_to_current_provider" id="link-provider-label" class="text-muted">Vincular nova(s) categoria(s) ao prestador selecionado acima</label>
                            <input type="hidden" id="current_provider_id_for_linking" name="current_provider_id_for_linking">
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Criar Categoria(s)</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // --- ESTADO GLOBAL ---
    let allCategories = [];
    let selectedCategories = new Map();
    let existingCategories = new Set();
    let searchTimeout;

    // --- LÓGICA DO CARD 1: VINCULAR PRESTADOR ---
    $('#provider-search').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        const resultsContainer = $('#provider-search-results');
        if (searchTerm.length < 2) { resultsContainer.empty().hide(); return; }
        searchTimeout = setTimeout(() => {
            $.ajax({
                url: 'api_search_providers.php',
                dataType: 'json',
                data: { q: searchTerm },
                success: function(data) {
                    resultsContainer.empty().show();
                    if(data.length > 0) {
                        data.forEach(p => resultsContainer.append($('<div></div>').addClass('result-item').text(p.text).data({id: p.id, name: p.text})));
                    } else {
                        resultsContainer.html('<div class="result-item text-muted">Nenhum prestador encontrado.</div>');
                    }
                }
            });
        }, 300);
    });

    $('#provider-search-results').on('click', '.result-item', function() {
        const providerId = $(this).data('id');
        const providerName = $(this).data('name');
        if (!providerId) return;

        $('#selected-provider-id').val(providerId);
        $('#selected-provider-display').html(`Prestador Selecionado: <strong>${providerName}</strong>`);
        $('#provider-search').val('');
        $('#provider-search-results').empty().hide();

        // ATIVA A OPÇÃO DE VINCULAR NO SEGUNDO CARD
        $('#link_to_current_provider').prop('disabled', false).prop('checked', true);
        $('#link-provider-label').removeClass('text-muted').html(`Vincular nova(s) categoria(s) a <strong>${providerName}</strong>`);
        $('#current_provider_id_for_linking').val(providerId);

        handleProviderChange(providerId);
    });

    $(document).on('click', e => {
        if (!$(e.target).closest('.provider-search-container').length) $('#provider-search-results').empty().hide();
    });

    function handleProviderChange(providerId) {
        resetState();
        if (providerId) fetchExistingLinks(providerId); else renderAll();
    }

    // --- LÓGICA DO CARD 2: CRIAR CATEGORIAS ---
    $('#add-category-row').on('click', function() {
        const newRow = $('#new-categories-container .category-row:first').clone();
        newRow.find('input[type="text"]').val('');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.remove-category-row').show(); // Mostra o botão de remover na nova linha
        $('#new-categories-container').append(newRow);
    });

    $('#new-categories-container').on('click', '.remove-category-row', function() {
        $(this).closest('.category-row').remove();
    });


    // --- FUNÇÕES COMPARTILHADAS (Vincular e Criar) ---
    $('#category-search').on('input', renderAvailableCategories);
    $('.category-list').on('click', '.category-item', handleCategoryToggle);
    $('#selected-categories-container').on('click', '.remove-category', handleRemoveCategory);

    fetchAllCategories(); // Carrega todas as categorias ao iniciar

    function fetchExistingLinks(providerId) {
        $.ajax({
            url: 'api_get_existing_links.php', type: 'POST', data: { codcadastro: providerId }, dataType: 'json',
            success: data => {
                data.forEach(cat => {
                    const codigo = cat.codigo.toString();
                    existingCategories.add(codigo);
                    selectedCategories.set(codigo, { titulo: cat.titulo, grupo: cat.grupo });
                });
                renderAll();
            }
        });
    }

    function handleCategoryToggle(){ const el = $(this); const c = el.data('codigo').toString(); if(selectedCategories.has(c)){selectedCategories.delete(c);}else{selectedCategories.set(c,{titulo:el.data('titulo'),grupo:el.data('grupo')});} renderAll(); }
    function handleRemoveCategory(){ const c = $(this).data('codigo').toString(); if(selectedCategories.has(c)){selectedCategories.delete(c);} renderAll(); }
    function fetchAllCategories(){ $.ajax({url:'api_get_categories.php',type:'POST',dataType:'json',success:data=>{allCategories=data;renderAvailableCategories();}}); }
    function renderAll(){ renderAvailableCategories(); renderSelectedCategories(); updateHiddenFormInput(); }
    function renderAvailableCategories(){ const s=$('#category-search').val().toLowerCase(); const l=$('.category-list'); l.empty(); const f=allCategories.filter(c=>c.titulo.toLowerCase().includes(s)||c.grupo.toLowerCase().includes(s)); if(f.length===0){l.html('<p class="text-muted">Nenhuma categoria encontrada.</p>'); return;} f.forEach(c=>{const code=c.codigo.toString();const isE=existingCategories.has(code);const isS=selectedCategories.has(code);const i=$('<div></div>').addClass('category-item').data({codigo:code,titulo:c.titulo,grupo:c.grupo}).text(`${c.titulo} (${c.grupo})`); if(isS)i.addClass('selected'); if(isE)i.addClass('existing'); l.append(i);}); }
    function renderSelectedCategories(){ const c=$('#selected-categories-container'); c.empty(); if(selectedCategories.size===0){c.html('<p class="text-muted">Nenhuma categoria vinculada.</p>'); return;} selectedCategories.forEach((d,code)=>{const isE=existingCategories.has(code);const i=$('<div></div>').addClass('selected-category').addClass(isE?'existing':'').html(`<span>${d.titulo} (${d.grupo})</span><span class="remove-category" data-codigo="${code}">×</span>`); c.append(i);}); }
    function updateHiddenFormInput(){ const h=$('#codsubcategoria_hidden'); h.empty(); selectedCategories.forEach((d,c)=>{h.append(new Option(d.titulo,c,true,true));}); }
    function resetState(){ selectedCategories.clear(); existingCategories.clear(); $('#category-search').val(''); }
});
</script>

</body>
</html>