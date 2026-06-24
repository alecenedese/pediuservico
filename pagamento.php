<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Moedas - USERVICE</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
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
        }

        /* Header styling identical to other USERVICE pages */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: rgba(0, 212, 255, 0.1);
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }

        .header .logo {
            font-size: 18px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .menu-button {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, 0.3);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-button:hover {
            background: rgba(0, 212, 255, 0.3);
            transform: translateY(-1px);
        }

        /* Menu lateral identical to other pages */
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

        /* Main content layout */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
            max-width: 100%;
            align-items: center;
        }

        /* Payment card */
        .payment-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #00d4ff;
            text-decoration: none;
            font-size: 19px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-3px);
        }

        .page-title {
            font-size: 21px;
            font-weight: bold;
            color: #1a2332;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 32px;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 24px;
        }

        .form-select {
            width: 100%;
            padding: 16px;
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 8px;
            font-size: 16px;
            background: white;
            color: #1a2332;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .price-display {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin: 24px 0;
        }

        /* PIX button */
        .pix-button {
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 24px;
        }

        .pix-button:hover {
            transform: scale(1.05);
        }

        .pix-button img {
            width: 200px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

          /* Adicionando grid de navegação rápida acima das tabs */
        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 0 8px;
            margin-bottom: 16px;
            margin-top: 16px;
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

        .nav-card:hover {
            background: rgba(0, 212, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-card.active {
            background: rgba(0, 212, 255, 0.25);
            border-color: rgba(0, 212, 255, 0.4);
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
    </style>
</head>
<body>
<style>
            .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .logo {
            font-size: 19px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }
    
        .category-button2 {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            border: 2px solid #00f0ff;
            color: #00f0ff;
            font-size: 13px; /* aumentado de 10px para 13px para melhor legibilidade */
            font-weight: 600;
            padding: 8px 8px; /* aumentado padding de 10px 6px para 11px 8px */
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px; /* aumentado gap de 5px para 6px */
            text-decoration: none;
            min-height: 17px; /* aumentado de 70px para 75px */
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .category-button2:hover {
            transform: translateY(-2px);
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
            border-color: #40ffff;
            color: #40ffff;
        }

         .category-button3 {
            background: #db9f9f;
            border: 2px solid red;
            color: #e1001e;
            font-size: 13px; /* aumentado de 10px para 13px para melhor legibilidade */
            font-weight: 600;
            padding: 8px 8px; /* aumentado padding de 10px 6px para 11px 8px */
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px; /* aumentado gap de 5px para 6px */
            text-decoration: none;
            min-height: 17px; /* aumentado de 70px para 75px */
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

</style>
    <div class="header">
                <a href="index.php" class="category-button2">Ínicio</a>
        <a href="javascript: history.back()" class="category-button2">← Voltar</a>
        <a href="sair.php" class="category-button3">Sair</a>

 </div>
                <!-- Adicionando grid de navegação rápida -->
        <div class="quick-nav-grid">
            <a href="meus-orcamentos.php" class="nav-card ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Meus Orçamentos</span>
            </a>
            
            <a href="minhasmoedas.php" class="nav-card active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Minhas Moedas</span>
            </a>
        </div>

    <div class="main-content">


        <div class="payment-card">
            <div class="page-title">💰 COMPRAR MOEDAS</div>
            <div class="page-subtitle">Digite o valor que deseja recarregar (mínimo R$ 10,00)</div>
            
            <form action="gerar-pix.php" method="GET" id="formMoedas" onsubmit="return validarValor()">
                <div class="form-group">
                    <label style="display:block;color:#fff;font-size:14px;font-weight:600;margin-bottom:8px;">Valor em reais (R$)</label>
                    <input type="number" class="form-select" id="valorReais" min="10" step="1" value="10" 
                           style="font-size:20px;font-weight:700;text-align:center;" oninput="calcularMoedas()">
                    <input type="hidden" name="valor" id="valor" value="10.00">
                    <input type="hidden" name="moedas" id="moedas" value="80">
                </div>
                
                <div class="price-display" id="qtdMoedas" style="font-size:18px;">Você receberá <strong>80 moedas</strong></div>
                <div id="erroValor" style="color:#ff6b6b;font-size:13px;text-align:center;margin-top:6px;display:none;">O valor mínimo é R$ 10,00</div>
                
                <button type="submit" class="pix-button">
                    <img src="10.png" width="200" alt="Pagar com PIX">
                </button>
            </form>
        </div>
    </div>

    <script>
        // Proporção: 8 moedas = R$ 1,00  →  1 real = 8 moedas
        const MOEDAS_POR_REAL = 8;
        const VALOR_MINIMO = 10;

        function calcularMoedas() {
            const valorReais = parseFloat(document.getElementById('valorReais').value) || 0;
            const moedas = Math.floor(valorReais * MOEDAS_POR_REAL);
            const erro = document.getElementById('erroValor');

            document.getElementById('qtdMoedas').innerHTML = 'Você receberá <strong>' + moedas + ' moedas</strong>';
            document.getElementById('valor').value = valorReais.toFixed(2);
            document.getElementById('moedas').value = moedas;

            if (valorReais < VALOR_MINIMO) {
                erro.style.display = 'block';
            } else {
                erro.style.display = 'none';
            }
        }

        function validarValor() {
            const valorReais = parseFloat(document.getElementById('valorReais').value) || 0;
            if (valorReais < VALOR_MINIMO) {
                document.getElementById('erroValor').style.display = 'block';
                alert('O valor mínimo de recarga é R$ 10,00');
                return false;
            }
            return true;
        }

        // Calcula inicial
        calcularMoedas();

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
    </script>
</body>
</html>
