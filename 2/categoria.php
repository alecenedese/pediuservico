<?php
require_once("send.php");
$codigo = $_GET['categoria'];
$listaG = mysqli_query($con, "select * from grupos where codigo='".$codigo."' order by titulo asc");
$rowg = mysqli_fetch_array($listaG);
$titulo = $rowg['titulo'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Servico - <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8c 50%, #1e3a5f 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            padding-bottom: 65px;
        }

        .content-wrap {
            flex: 1;
            padding: 20px 16px;
            overflow-y: auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-title h1 {
            font-size: 20px;
            font-weight: 600;
            color: #ffffff;
        }

        .page-title p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            margin-top: 4px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            align-content: start;
            width: 100%;
        }

        .category-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 16px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 80px;
            text-decoration: none;
        }

        .category-card:active {
            transform: scale(0.97);
            background: rgba(255,255,255,0.14);
        }

        .category-card.next-phase {
            background: rgba(255, 149, 0, 0.2);
            border-color: rgba(255, 149, 0, 0.55);
            cursor: not-allowed;
            pointer-events: none;
        }

        .category-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .category-name {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            text-align: center;
            line-height: 1.3;
        }

        .services-count {
            background: rgba(0,212,255,0.2);
            color: #00d4ff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
        }

        .services-count.next-phase {
            background: rgba(255, 149, 0, 0.25);
            color: #ffb74d;
        }

        .next-phase-label {
            font-size: 11px;
            color: #ffb74d;
        }

        .category-description {
            display: none;
        }

        @media (min-width: 480px) {
            .categories-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>
    
    <div class="content-wrap">
    <div class="page-title">
        <h1><?php echo htmlspecialchars($titulo); ?></h1>
        <p>Selecione uma categoria</p>
    </div>

    <div class="categories-grid" id="categoriesGrid">
        <!-- Categorias serão inseridas aqui via JavaScript -->
    </div>

<?php
$categories = [];

$sql = "
   SELECT 
    c.codigo,
    c.titulo AS titulo,
    c.proxima_fase,
    g.titulo AS grupo,
    COUNT(cp.codsubcategoria) AS total
FROM categoria c
INNER JOIN grupos g ON g.codigo = c.codgrupo
LEFT JOIN categoria_prestador cp ON cp.codsubcategoria = c.codigo
WHERE c.codgrupo = '".$codigo. "'
GROUP BY c.codigo
HAVING total > 0 OR c.proxima_fase = 1
ORDER BY c.titulo ASC
";


$lista2 = mysqli_query($con, $sql);

while ($rowCont = mysqli_fetch_array($lista2)) { 
    $categories[] = [
        'name' => $rowCont['titulo'],
        'codigo' => $rowCont['codigo'],
        'services' => (int)$rowCont['total'],
        'next_phase' => (int)($rowCont['proxima_fase'] ?? 0),
        'description' => 'Descrição genérica ou outra coluna do banco'
    ];
}
?>


    <script>
        // Dados simulados das subcategorias
        const subcategories = <?php echo json_encode($categories, JSON_UNESCAPED_UNICODE); ?>;

        const categoriesGrid = document.getElementById('categoriesGrid');

        function renderSubcategories(categoriesToRender) {
            categoriesGrid.innerHTML = categoriesToRender.map(category => `
                <div class="category-card ${category.next_phase ? 'next-phase' : ''}" ${category.next_phase ? '' : `onclick="selectSubcategory('${category.codigo}')"`}>
                    <div class="category-header">
                        <div class="category-name">${category.name}</div>
                        <div class="services-count ${category.next_phase ? 'next-phase' : ''}">${category.next_phase ? 'Disponível em breve' : `${category.services} prestadores`}</div>
                    </div>
                    <div class="category-description">${category.description}</div>
                </div>
            `).join('');
        }

        function selectSubcategory(subcategoryName) {
            const urlParams = new URLSearchParams(window.location.search);
            const categoryName = urlParams.get('categoria') || 'Categoria Selecionada';
            window.location.href = `opcoes.php?categoria=${encodeURIComponent(categoryName)}&subcategoria=${encodeURIComponent(subcategoryName)}`;
        }

        // Renderização inicial
        renderSubcategories(subcategories);
    </script>
    </div><!-- /content-wrap -->

<?php $navAtiva = 'buscar'; include('bottom-nav.php'); ?>
</body>
</html>
