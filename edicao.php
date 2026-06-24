<?php
     require("send.php");

     if(isset($_GET['acao']) && $_GET['acao'] == 'del'){
      $sqlCityd = "delete from categoria_prestador where codigo = '".$_GET['codigo']."'";
      $sqlCityd = mysqli_query( $con, $sqlCityd );

      echo "<script> window.location.href='edicao.php';</script>";
    }


      $queryEditc = mysqli_query($con, "select * from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
      $rowEdit = mysqli_fetch_array($queryEditc);

      $queryEnd = mysqli_query($con, "SELECT * FROM endereco_prestador WHERE cod_cadastro='".$rowEdit['id']."'");
      $rowEnd = mysqli_fetch_array($queryEnd);
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Pediu Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 70px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            background: rgba(0, 212, 255, 0.1);
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
            flex-shrink: 0;
        }

        .header .logo {
            font-size: 16px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .menu-button {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, 0.3);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-button:hover {
            background: rgba(0, 212, 255, 0.3);
            transform: translateY(-1px);
        }

        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .menu-sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 280px;
            height: 100%;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            z-index: 1000;
            transition: left 0.3s ease;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
        }

        .menu-sidebar.active {
            left: 0;
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }

        .menu-title {
            font-size: 18px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .close-menu {
            background: none;
            border: none;
            color: #00d4ff;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }

        .menu-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 8px;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .menu-nav a:hover {
            background: rgba(0, 212, 255, 0.1);
            color: #00d4ff;
        }

        .menu-nav a.active {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
        }

        .menu-nav svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 8px;
            gap: 8px;
            max-width: 100%;
            overflow: hidden;
        }

        .page-header {
            text-align: center;
            color: #00d4ff;
            flex-shrink: 0;
        }

        .page-title {
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 3px;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .page-subtitle {
            font-size: 14px;
            opacity: 0.8;
        }

        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 0 8px;
            flex-shrink: 0;
        }

        .nav-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 10px 6px;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            text-decoration: none;
            color: #ffffff;
            transition: all 0.3s ease;
            min-height: 65px;
        }

        .nav-card svg {
            width: 22px;
            height: 22px;
            stroke-width: 2;
            color: #00d4ff;
        }

        .nav-card span {
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            line-height: 1.1;
        }

        .tabs-container {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-shrink: 0;
        }

        .tab {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 11px;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 110px;
            text-align: center;
        }

        .tab:not(.active) {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            border-color: rgba(0, 212, 255, 0.3);
        }

        .content-container {
            flex: 1;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(0, 212, 255, 0.2);
            padding: 13px;
            overflow-y: auto;
            position: relative;
            min-height: 0;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            padding: 13px;
            margin-bottom: 13px;
            border-left: 4px solid #00d4ff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1a2332;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        @media (min-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-input {
            padding: 10px;
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 6px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s;
            min-height: 38px;
        }

        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
        }

        .form-input:read-only {
            background: rgba(240, 240, 240, 0.8);
            color: #666;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 18px;
            padding: 3px;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 6px;
            background: rgba(0, 212, 255, 0.05);
            border-radius: 6px;
            border: 1px solid rgba(0, 212, 255, 0.1);
        }

        .category-name {
            font-size: 14px;
            color: #1a2332;
            font-weight: 500;
        }

        .category-group {
            font-size: 16px;
            font-weight: bold;
            color: #00d4ff;
            margin: 13px 0 6px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }

        .delete-btn {
            background: linear-gradient(145deg, #ff4757, #ff6b81);
            color: white;
            border: none;
            padding: 8px 13px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            box-shadow: 0 2px 6px rgba(255, 71, 87, 0.3);
        }

        .delete-btn:hover {
            background: linear-gradient(145deg, #ee5a6f, #ff4757);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(255, 71, 87, 0.4);
        }

        .delete-btn svg {
            width: 14px;
            height: 14px;
        }

        .add-category-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 11px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 13px;
            width: 100%;
            min-height: 40px;
        }

        .add-category-btn:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .add-category-section {
            display: none;
            background: rgba(0, 212, 255, 0.05);
            border-radius: 8px;
            padding: 13px;
            margin-bottom: 13px;
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 10px;
            min-height: 38px;
        }

        .accordion-header {
            background: rgba(0, 212, 255, 0.1);
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 6px;
            transition: background 0.3s;
            min-height: 38px;
            display: flex;
            align-items: center;
        }

        .accordion-header:hover {
            background: rgba(0, 212, 255, 0.2);
        }

        .accordion-content {
            display: none;
            padding: 6px;
            max-height: 250px;
            overflow-y: scroll;
        }

        .accordion-content.active {
            display: block;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px;
            font-size: 14px;
            min-height: 36px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .selected-services {
            display: none;
            margin-top: 10px;
        }

        .selected-services.active {
            display: block;
        }

        .service-tag {
            display: inline-block;
            background: rgba(0, 212, 255, 0.2);
            color: #1a2332;
            padding: 6px 11px;
            border-radius: 15px;
            font-size: 14px;
            margin: 3px;
        }

        .submit-btn {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 14px;
            font-weight: 600;
            padding: 13px 19px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 10px;
            min-height: 44px;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.6);
        }

        .categories-list-container {
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>


    <div class="main-content">
        <div class="page-header">
            <div class="page-title">Minha Conta</div>
            <div class="page-subtitle">Gerencie suas informações e categorias</div>
        </div>

        <div class="quick-nav-grid">
            <a href="meus-orcamentos.php" class="nav-card">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Orçamentos</span>
            </a>

            <a href="minhasmoedas.php" class="nav-card">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Minhas Moedas</span>
            </a>
        </div>

        <div class="tabs-container">
            <button class="tab active" onclick="switchTab('dados')">
                👤 Dados Pessoais
            </button>
            <button class="tab" onclick="switchTab('categorias')">
                ⚙️ Categorias
            </button>
            <button class="tab" onclick="switchTab('endereco')">
                📍 Endereço
            </button>
        </div>

        <div class="content-container">
            <!-- Personal Data Section -->
            <div id="dados-content">
                <form method="POST" action="editar-prestador.php">
                    <div class="form-section">
                        <div class="section-title">
                            📋 Informações Pessoais
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">CPF / CNPJ</label>
                                <input type="text" name="cpf_cnpj" class="form-input"
                                       value="<?php echo $rowEdit['CNPJ_CPF']; ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-input"
                                       value="<?php echo $rowEdit['NOME']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Celular</label>
                                <input type="tel" name="telefone" class="form-input"
                                       value="<?php echo $rowEdit['CELULAR']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Senha</label>
                                <div class="password-container">
                                    <input type="password" id="pass" name="pass" class="form-input"
                                           value="<?php echo $rowEdit['senha']; ?>" minlength="3" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                                </div>
                            </div>


                        </div>

                        <button type="submit" class="submit-btn">
                            🔄 Atualizar Perfil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories Section -->
            <div id="categorias-content" style="display: none;">
                <div class="form-section">
                    <div class="section-title">
                        ⚙️ Suas Categorias de Serviço
                    </div>

                    <button type="button" class="add-category-btn" onclick="toggleAddCategory()">
                        ➕ Adicionar Categoria
                    </button>

                    <div class="categories-list-container">
                        <?php
                        $currentGroup = '';
                        $sql = "SELECT g.titulo as grupo_titulo, cat.titulo, cat.codigo, cp.codigo as coddel
                                FROM categoria cat
                                JOIN categoria_prestador cp ON cat.codigo = cp.codsubcategoria
                                JOIN grupos g ON cat.codgrupo = g.codigo
                                WHERE cp.codcadastro = '".$rowEdit['id']."'
                                ORDER BY g.titulo, cat.titulo";
                        $res = mysqli_query($con, $sql);
                        while ($row = mysqli_fetch_assoc($res)) {
                            if ($currentGroup != $row['grupo_titulo']) {
                                $currentGroup = $row['grupo_titulo'];
                                echo '<div class="category-group">'.$row['grupo_titulo'].'</div>';
                            }
                        ?>
                            <div class="category-item">
                                <span class="category-name"><?php echo $row['titulo']; ?></span>
                                <button onclick="confirmDelete('<?php echo $row['coddel']; ?>')" class="delete-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Deletar
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="add-category-section" id="add-category-section">
                        <input type="text" id="search-categories" class="search-input"
                               placeholder="🔍 Buscar categoria..." onkeyup="searchCategories()">

                        <form action="salvar-categorias.php" method="POST">
                            <div id="categories-accordion">
                                <?php
                                $sql = "SELECT * from grupos ORDER BY titulo asc";
                                $res = mysqli_query($con, $sql);
                                while ($row = mysqli_fetch_assoc($res)) { ?>
                                <div class="accordion-item" id="group-<?php echo $row['codigo']; ?>">
                                    <div class="accordion-header" onclick="toggleAccordion(<?php echo $row['codigo']; ?>)">
                                        📁 <?php echo $row['titulo']; ?>
                                    </div>
                                    <div class="accordion-content" id="content-<?php echo $row['codigo']; ?>">
                                        <?php
                                        $sql2 = "SELECT * from categoria where codgrupo='".$row['codigo']."' ORDER BY titulo asc";
                                        $res2 = mysqli_query($con, $sql2);
                                        while ($rowSub = mysqli_fetch_assoc($res2)) { ?>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="cat-<?php echo $rowSub['codigo']; ?>"
                                                   value="<?php echo $rowSub['codigo']; ?>"
                                                   name="subcategoria[]" onchange="updateSelectedServices()">
                                            <label for="cat-<?php echo $rowSub['codigo']; ?>"><?php echo $rowSub['titulo']; ?></label>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <div class="selected-services" id="selected-services">
                                <div class="section-title">✅ Serviços Selecionados:</div>
                                <div id="services-tags"></div>
                            </div>

                            <button type="submit" class="submit-btn" style="margin-top: 16px;">
                                💾 Salvar Categorias
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Endereço Section -->
            <div id="endereco-content" style="display: none;">
                <div class="form-section">
                    <div class="section-title">
                        📍 Meu Endereço
                    </div>

                    <form action="converteendemcord.php" method="GET" id="endereco-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">CEP:</label>
                                <input type="text" id="cep" name="cep" class="form-input" placeholder="13483-000" maxlength="9" value="<?php echo isset($rowEnd['cep']) ? $rowEnd['cep'] : ''; ?>">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Endereço:</label>
                                <input type="text" id="endereco" name="endereco" class="form-input" value="<?php echo isset($rowEnd['endereco']) ? $rowEnd['endereco'] : ''; ?>">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Número:</label>
                                <input type="text" id="numero" name="n" class="form-input" value="<?php echo isset($rowEnd['n']) ? $rowEnd['n'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Bairro:</label>
                                <input type="text" id="bairro" name="bairro" class="form-input" value="<?php echo isset($rowEnd['bairro']) ? $rowEnd['bairro'] : ''; ?>">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">UF:</label>
                                <input type="text" id="uf" name="uf" class="form-input" maxlength="2" value="<?php echo isset($rowEnd['uf']) ? $rowEnd['uf'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cidade:</label>
                                <input type="text" id="cidade" name="cidade" class="form-input" value="<?php echo isset($rowEnd['cidade']) ? $rowEnd['cidade'] : ''; ?>">
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            💾 Salvar Endereço
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTab = 'dados';

        function toggleMenu() {
            const sidebar = document.getElementById('menu-sidebar');
            const overlay = document.getElementById('menu-overlay');

            sidebar.classList.add('active');
            overlay.style.display = 'block';
        }

        function closeMenu() {
            const sidebar = document.getElementById('menu-sidebar');
            const overlay = document.getElementById('menu-overlay');

            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        }

        function switchTab(tab) {
            // Update tab buttons
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');

            // Update content
            document.getElementById('dados-content').style.display = tab === 'dados' ? 'block' : 'none';
            document.getElementById('categorias-content').style.display = tab === 'categorias' ? 'block' : 'none';
            document.getElementById('endereco-content').style.display = tab === 'endereco' ? 'block' : 'none';

            currentTab = tab;
        }

        function togglePassword() {
            const passInput = document.getElementById('pass');
            const toggleBtn = document.querySelector('.toggle-password');

            if (passInput.type === 'password') {
                passInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        function toggleAddCategory() {
            const section = document.getElementById('add-category-section');
            const btn = document.querySelector('.add-category-btn');

            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
                btn.textContent = '❌ Cancelar';
            } else {
                section.style.display = 'none';
                btn.textContent = '➕ Adicionar Categoria';
            }
        }

        function toggleAccordion(groupId) {
            const content = document.getElementById('content-' + groupId);
            content.classList.toggle('active');
        }

        function searchCategories() {
            const searchTerm = document.getElementById('search-categories').value.toLowerCase();
            const accordionItems = document.querySelectorAll('.accordion-item');

            accordionItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        }

        function updateSelectedServices() {
            const checkboxes = document.querySelectorAll('input[name="subcategoria[]"]:checked');
            const servicesDiv = document.getElementById('selected-services');
            const tagsDiv = document.getElementById('services-tags');

            if (checkboxes.length > 0) {
                servicesDiv.classList.add('active');
                let html = '';
                checkboxes.forEach(checkbox => {
                    // Pegando o label associado ao checkbox para mostrar o nome real
                    const label = document.querySelector(`label[for="${checkbox.id}"]`);
                    const categoryName = label ? label.textContent : checkbox.value;
                    html += `<span class="service-tag">${categoryName}</span>`;
                });
                tagsDiv.innerHTML = html;
            } else {
                servicesDiv.classList.remove('active');
            }
        }



        function confirmDelete(codigo) {
            if (confirm('Tem certeza que deseja deletar esta categoria?')) {
                window.location.href = 'edicao.php?codigo=' + codigo + '&acao=del';
            }
        }

        document.getElementById('cep')?.addEventListener('blur', function() {
            var cep = this.value.replace(/[^0-9]/, "");

            if(cep.length != 8){
                return false;
            }

            var url = "https://viacep.com.br/ws/"+cep+"/json/";

            fetch(url)
                .then(response => response.json())
                .then(dadosRetorno => {
                    try{
                        document.getElementById("endereco").value = dadosRetorno.logradouro || '';
                        document.getElementById("bairro").value = dadosRetorno.bairro || '';
                        document.getElementById("cidade").value = dadosRetorno.localidade || '';
                        document.getElementById("uf").value = dadosRetorno.uf || '';
                    }catch(ex){}
                })
                .catch(error => console.error('Erro ao buscar CEP:', error));
        });
    </script>
    
    <!-- Inicializa Push Notifications para o prestador -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prestadorId = <?php echo $rowEdit['id']; ?>;
            if (prestadorId && window.PushHelper) {
                initPushForPrestador(prestadorId);
            }
        });
    </script>

<?php $navAtiva = 'servicos'; include('bottom-nav.php'); ?>
</body>
</html>
