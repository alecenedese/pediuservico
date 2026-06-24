<?php
include("send.php");

// Detecta login
$logado = (isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1') || (isset($_COOKIE['celular_usuario']) && !empty($_COOKIE['celular_usuario'])) || (isset($_COOKIE['login']) && !empty($_COOKIE['login']));
$nomeUsuario = isset($_COOKIE['nome_usuario']) ? $_COOKIE['nome_usuario'] : '';
$primeiroNome = !empty($nomeUsuario) ? explode(' ', trim($nomeUsuario))[0] : '';

// Buscar grupos com categorias ativas ou marcadas para próxima fase
$queryGrupos = "SELECT g.codigo, g.titulo,
                       SUM(CASE WHEN c.proxima_fase = 1 THEN 1 ELSE 0 END) AS next_phase_count,
                       SUM(CASE WHEN c.proxima_fase = 0 AND cp.codsubcategoria IS NOT NULL THEN 1 ELSE 0 END) AS active_count,
                       COUNT(DISTINCT cp.codcadastro) AS total_prestadores
                FROM grupos g
                INNER JOIN categoria c ON c.codgrupo = g.codigo
                LEFT JOIN categoria_prestador cp ON cp.codsubcategoria = c.codigo
                GROUP BY g.codigo, g.titulo
                HAVING active_count > 0 OR next_phase_count > 0
                ORDER BY g.titulo";
$resultGrupos = mysqli_query($con, $queryGrupos);

$termoBusca = isset($_GET['busca']) ? mysqli_real_escape_string($con, $_GET['busca']) : '';
$navAtiva = 'buscar';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Servico - Buscar Prestador</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <?php include('pwa-include.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8c 50%, #1e3a5f 100%);
            background-attachment: fixed;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            padding-bottom: 75px;
        }

        .content-area {
            flex: 1;
            padding: 20px 16px;
            overflow-y: auto;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 24px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .hero-title .highlight {
            color: #4fc3f7;
            font-weight: 700;
        }

        .hero-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .search-box {
            position: relative;
            z-index: 100;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(79, 195, 247, 0.5);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .search-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .search-form {
            position: relative;
            margin-bottom: 12px;
        }

        .search-input {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            border: none;
            border-radius: 12px;
            background: #ffffff;
            color: #333;
            outline: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .search-input::placeholder {
            color: #999;
        }

        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #ffffff;
            border-radius: 0 0 12px 12px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 10000;
            margin-top: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .autocomplete-dropdown.active {
            display: block;
        }

        .autocomplete-item {
            padding: 12px 16px;
            color: #333;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .autocomplete-item:hover {
            background: #f5f5f5;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            border: none;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 188, 212, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .categories-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .category-button {
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid rgba(79, 195, 247, 0.4);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            padding: 14px 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            text-decoration: none;
            backdrop-filter: blur(10px);
        }

        .category-button:active {
            transform: scale(0.95);
            background: rgba(255, 255, 255, 0.2);
        }

        .category-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .category-button.next-phase {
            background: rgba(255, 149, 0, 0.18);
            border-color: rgba(255, 149, 0, 0.6);
            color: #ffe0b2;
        }

        .next-phase-label {
            font-size: 11px;
            color: #ffb74d;
        }

        .prestadores-label {
            font-size: 11px;
            color: #4fc3f7;
            font-weight: 600;
        }

        .benefits-section {
            margin-bottom: 20px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .benefit-item .icon {
            font-size: 16px;
        }

        .benefit-item .text {
            flex: 1;
        }

        .benefit-item .text strong {
            font-weight: 600;
        }

        .location-info {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .rapid-mode {
            background: linear-gradient(135deg, rgba(0, 188, 212, 0.2) 0%, rgba(0, 151, 167, 0.2) 100%);
            border: 2px solid rgba(79, 195, 247, 0.5);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            backdrop-filter: blur(10px);
        }

        .rapid-mode:active {
            transform: scale(0.98);
        }

        .rapid-mode .icon-box {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .rapid-mode .content {
            flex: 1;
        }

        .rapid-mode .title {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .rapid-mode .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            line-height: 1.3;
        }

        .rapid-mode .arrow {
            color: #4fc3f7;
            font-size: 24px;
            flex-shrink: 0;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            color: #ffffff;
            font-size: 15px;
            padding: 32px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(79, 195, 247, 0.3);
            border-radius: 12px;
        }

        @media (max-width: 900px) {
            .hero-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<?php include('header-app.php'); ?>

<div class="content-area">
    
    <div class="hero-section">
        <h1 class="hero-title">Resolva seu <span class="highlight">problema</span><br>agora 🚀</h1>
        <p class="hero-subtitle">Conectamos você ao profissional certo<br>em poucos minutos</p>
    </div>

    <div class="search-box">
        <div class="search-label">
            🔍 Qual profissional você precisa encontrar?
        </div>
        <form class="search-form" method="GET" action="buscar.php">
            <input 
                type="text" 
                id="searchInput"
                name="busca" 
                class="search-input" 
                placeholder="Digitar aqui..."
                value="<?php echo htmlspecialchars($termoBusca); ?>"
                autocomplete="off"
            >
            <div id="autocompleteDropdown" class="autocomplete-dropdown"></div>
        </form>
        <button type="button" class="btn-primary" onclick="document.querySelector('.search-form').submit();">
            🚀 Encontrar profissional agora
        </button>
    </div>

    <div class="categories-container" id="categoriesContainer">
        <?php
        if (mysqli_num_rows($resultGrupos) > 0) {
            $icones = [
                'Conserto' => '🔧',
                'Elétrica' => '💡',
                'Encanamento' => '🚰',
                'Ar-condicionado' => '❄️',
                'Limpeza' => '🧹',
                'Chaveiro' => '🔑',
                'Pintura' => '🎨',
                'Jardinagem' => '🌱',
                'Mudança' => '📦',
                'Reforma' => '🏗️'
            ];
            
            while ($grupo = mysqli_fetch_array($resultGrupos)) {
                $titulo = htmlspecialchars($grupo['titulo']);
                
                $somenteFuturo = ((int)$grupo['active_count'] === 0 && (int)$grupo['next_phase_count'] > 0);
                $buttonClass = $somenteFuturo ? 'category-button next-phase' : 'category-button';
                // Item 6: Sempre clicável, mesmo se for próxima fase
                echo '<a href="categoria.php?categoria='.$grupo['codigo'].'" class="'.$buttonClass.'">';
                echo '<span class="category-text"><span>'.$titulo.'</span>';
                if ($somenteFuturo) {
                    echo '<span class="next-phase-label">Disponível em breve</span>';
                } else {
                    $qtdP = (int)$grupo['total_prestadores'];
                    echo '<span class="prestadores-label">'.$qtdP.' '.($qtdP === 1 ? 'prestador' : 'prestadores').'</span>';
                }
                echo '</span>';
                echo '</a>';
            }
        } else {
            echo '<div class="no-results">Nenhuma categoria disponível</div>';
        }
        ?>
    </div>

    <div class="benefits-section">
        <div class="benefit-item">
            <span class="icon">⚡</span>
            <span class="text"><strong>Profissionais</strong> respondem em poucos minutos</span>
        </div>
        <div class="benefit-item">
            <span class="icon">⭐</span>
            <span class="text"><strong>Profissionais</strong> avaliados</span>
        </div>
        <div class="benefit-item">
            <span class="icon">✅</span>
            <span class="text">Serviço confiável</span>
        </div>
    </div>

    <div class="location-info">
        📍 Atendendo sua região agora
    </div>

</div><!-- /content-area -->

<?php include('bottom-nav.php'); ?>

    <script>
        const searchInput = document.getElementById('searchInput');
        const autocompleteDropdown = document.getElementById('autocompleteDropdown');
        let timeoutId;

        searchInput.addEventListener('input', function() {
            const termo = this.value.trim();
            
            clearTimeout(timeoutId);
            
            if (termo.length < 2) {
                autocompleteDropdown.classList.remove('active');
                autocompleteDropdown.innerHTML = '';
                return;
            }

            timeoutId = setTimeout(() => {
                fetch('buscar-autocomplete.php?termo=' + encodeURIComponent(termo))
                    .then(response => response.json())
                    .then(data => {
                        autocompleteDropdown.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.textContent = item.nome;
                                div.onclick = function() {
                                    window.location.href = `opcoes.php?categoria=${item.codgrupo}&subcategoria=${item.id}`;
                                };
                                autocompleteDropdown.appendChild(div);
                            });
                            autocompleteDropdown.classList.add('active');
                        } else {
                            autocompleteDropdown.innerHTML = '<div class="autocomplete-item">Nenhum resultado encontrado</div>';
                            autocompleteDropdown.classList.add('active');
                        }
                    })
                    .catch(error => {
                        console.error('Erro na busca:', error);
                    });
            }, 300);
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.search-form')) {
                autocompleteDropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>
