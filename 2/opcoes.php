<?php
// Redireciona para o novo arquivo unificado
$grupo = $_GET['categoria'];
$categoria = $_GET['subcategoria'];
header("Location: solicitar-servico.php?categoria=$grupo&subcategoria=$categoria");
exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USERVICE - Local do Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
                .back-button {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .back-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
            background: linear-gradient(145deg, #00f0ff, #40ffff);
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 13px;
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 13px;
        }

        .logo {
            font-size: 19px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .step-indicator {
            color: #00d4ff;
            font-size: 10px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* adicionando estilo do botão voltar igual opcoes2.html */
        .back-btn {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
            background: linear-gradient(145deg, #00f0ff, #40ffff);
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px;
        }

        .card {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.15), rgba(0, 240, 255, 0.1));
            border: 2px solid #00f0ff;
            border-radius: 8px;
            /* reduzindo padding de 16px para 13px */
            padding: 13px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .card-title {
            /* reduzindo font-size de 16px para 14px */
            font-size: 14px;
            font-weight: 600;
            color: #00f0ff;
            text-align: center;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .card-subtitle {
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            margin-bottom: 16px;
            /* reduzindo font-size de 13px para 11px */
            font-size: 11px;
        }

        .options-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 16px;
        }

        .option-button {
            display: flex;
            align-items: center;
            /* reduzindo padding de 13px para 11px */
            padding: 11px;
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            border: 2px solid #00f0ff;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .option-button:hover {
            transform: translateY(-2px);
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
            border-color: #40ffff;
        }

        .option-button.selected {
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.6), rgba(64, 255, 255, 0.7));
            border-color: #40ffff;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.5);
        }

        .option-icon {
            color: #00f0ff;
            margin-right: 8px;
            flex-shrink: 0;
            width: 12px;
            height: 12px;
        }

        .option-text {
            flex: 1;
        }

        .option-title {
            font-weight: 600;
            color: #00f0ff;
            /* reduzindo font-size de 13px para 11px */
            font-size: 11px;
            margin-bottom: 2px;
        }

        .option-description {
            color: rgba(255, 255, 255, 0.8);
            /* reduzindo font-size de 11px para 10px */
            font-size: 10px;
        }

        .footer {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-top: 16px;
        }

        .btn {
            padding: 6px 13px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 10px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-secondary {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            color: #00f0ff;
            border: 2px solid #00f0ff;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.4);
        }

        .btn-primary {
            background: linear-gradient(145deg, #00f0ff, #40ffff);
            color: #1a2332;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
        }

        .btn-primary:disabled {
            background: rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .icon {
            width: 12px;
            height: 12px;
        }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>
    

    <div class="main-content">
        <div class="card">
            <h1 class="card-title">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Local do Serviço
            </h1>
            <p class="card-subtitle">Escolha sua preferência de localização para o serviço:</p>
            
            <div class="options-container" id="options-container">
                <button class="option-button" data-option="O prestador irá até sua localização">
                    <svg class="option-icon icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <div class="option-text">
                        <div class="option-title">Quero que venha até mim</div>
                        <div class="option-description">O prestador irá até sua localização</div>
                    </div>
                </button>

                <button class="option-button" data-option="Você irá até o local do prestado">
                    <svg class="option-icon icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="20" height="14" x="2" y="7" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    <div class="option-text">
                        <div class="option-title">Posso ir até o prestador</div>
                        <div class="option-description">Você irá até o local do prestador</div>
                    </div>
                </button>

                <button class="option-button" data-option="A localização não é um fator importante">
                    <svg class="option-icon icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                        <path d="M2 12h20"/>
                    </svg>
                    <div class="option-text">
                        <div class="option-title">Independente</div>
                        <div class="option-description">A localização não é um fator importante</div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const optionsContainer = document.getElementById('options-container');
            let selectedOption = null;
            let selectedDescription = '';

            optionsContainer.addEventListener('click', function (event) {
                const clickedOption = event.target.closest('.option-button');
                if (clickedOption) {
                    // Remove a seleção de todas as opções
                    const options = optionsContainer.querySelectorAll('.option-button');
                    options.forEach(option => option.classList.remove('selected'));

                    // Adiciona a classe 'selected' na opção clicada
                    clickedOption.classList.add('selected');

                    // Salva o valor do data-option
                    selectedOption = clickedOption.dataset.option;

                    // Salva a descrição
                    const descriptionElement = clickedOption.querySelector('.option-description');
                    selectedDescription = descriptionElement ? descriptionElement.textContent.trim() : '';

                    console.log('Selected Option:', selectedOption);
                    console.log('Selected Description:', selectedDescription);

                    // Redirecionamento automático após seleção
                    setTimeout(() => {
                        window.location.href = `opcoes2.php?codgrupo=<?php echo $_GET['categoria']; ?>&categoria=<?php echo $_GET['subcategoria']; ?>&local=${encodeURIComponent(selectedOption)}`;

                    }, 300);
                }
            });
        });
    </script>
</body>
</html>
