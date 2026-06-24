<?php
// Redireciona para o novo arquivo unificado
$grupo = $_GET['codgrupo'];
$categoria = $_GET['categoria'];
header("Location: solicitar-servico.php?categoria=$grupo&subcategoria=$categoria");
exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Servico - Quando voce precisa?</title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
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
            padding: 13px; /* reduzindo padding de 16px para 13px */
            max-width: 500px;
            width: 100%;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .card-title {
            text-align: center;
            font-size: 14px; /* reduzindo font-size de 16px para 14px */
            font-weight: bold;
            color: #00f0ff;
            margin-bottom: 8px;
            text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
        }

        .card-subtitle {
            text-align: center;
            font-size: 11px; /* reduzindo font-size de 13px para 11px */
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 16px;
        }

        .time-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 16px;
        }

        .time-option {
            width: 100%;
            padding: 11px; /* reduzindo padding de 13px para 11px */
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            border: 2px solid #00f0ff;
            border-radius: 6px;
            color: #00f0ff;
            font-size: 11px; /* reduzindo font-size de 13px para 11px */
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .time-option:hover {
            transform: translateY(-2px);
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
            border-color: #40ffff;
        }

        .time-option.selected {
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.6), rgba(64, 255, 255, 0.7));
            border-color: #40ffff;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.5);
        }

        .time-option.emergency {
            border-color: rgba(255, 100, 100, 0.5);
            background: linear-gradient(145deg, rgba(255, 100, 100, 0.3), rgba(255, 150, 150, 0.4));
        }

        .time-option.emergency:hover,
        .time-option.emergency.selected {
            border-color: #ff6464;
            background: linear-gradient(145deg, rgba(255, 100, 100, 0.5), rgba(255, 150, 150, 0.6));
        }

        .option-icon {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
            color: #00f0ff;
        }

        .emergency .option-icon {
            color: #ff6464;
        }

        .date-time-section {
            border-top: 2px solid rgba(0, 240, 255, 0.3);
            padding-top: 8px;
            margin-top: 8px;
            display: none;
        }

        .date-time-section.active {
            display: block;
        }

        /* Adicionando estilos para o modal popup */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: linear-gradient(145deg, #1a2332, #2d4a6b);
            border: 2px solid #00f0ff;
            border-radius: 8px;
            padding: 16px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 240, 255, 0.3);
        }

        .modal-title {
            font-size: 14px;
            font-weight: bold;
            color: #00f0ff;
            margin-bottom: 16px;
            text-align: center;
            text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
        }

        .modal-buttons {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            justify-content: flex-end;
        }

        .modal-btn {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 10px;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .modal-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
            background: linear-gradient(145deg, #00f0ff, #40ffff);
        }

        .modal-btn.cancel {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            color: #00f0ff;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .modal-btn.cancel:hover {
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.4);
        }

        .date-time-title {
            font-size: 11px;
            font-weight: bold;
            color: #00f0ff;
            margin-bottom: 8px;
            text-align: center;
        }

        .date-time-inputs {
            display: flex;
            gap: 8px;
        }

        .input-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .input-group label {
            font-size: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .input-group input {
            padding: 6px;
            background: rgba(0, 240, 255, 0.1);
            border: 2px solid rgba(0, 240, 255, 0.3);
            border-radius: 4px;
            color: white;
            font-size: 10px;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: #00f0ff;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.3);
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .error-message {
            color: #ff6464;
            font-size: 8px;
            display: none;
        }

        .error-message.visible {
            display: block;
        }

        .footer {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-top: 16px;
        }

        .footer-btn {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 10px;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .footer-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
            background: linear-gradient(145deg, #00f0ff, #40ffff);
        }

        .footer-btn:disabled {
            background: rgba(100, 100, 100, 0.5);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            border-color: rgba(100, 100, 100, 0.5);
        }

        .footer-btn.ghost {
            background: linear-gradient(145deg, rgba(0, 212, 255, 0.3), rgba(0, 240, 255, 0.4));
            color: #00f0ff;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.2);
        }

        .footer-btn.ghost:hover {
            background: linear-gradient(145deg, rgba(0, 240, 255, 0.5), rgba(64, 255, 255, 0.6));
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.4);
        }

        @media (max-width: 768px) {
            .date-time-inputs {
                flex-direction: column;
            }
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
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>

    <div class="main-content">
        <div class="card">
            <div class="card-title">Quando você precisa do serviço?</div>
            <div class="card-subtitle">Escolha o prazo ideal para início do atendimento</div>
            
            <div class="time-options" id="timeOptions">
                <button class="time-option emergency" data-value="Até 1 hora (Emergência)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22c6-3 10-8.5 10-13.5a6.5 6.5 0 0 0-13 0c0 5 4 10.5 10 13.5"/>
                        <line x1="12" y1="7" x2="12" y2="13"/>
                        <line x1="12" y1="15" x2="12" y2="15.5"/>
                    </svg>
                    Até 1 hora (Emergência)
                </button>
                
                <button class="time-option" data-value="Pra hoje, em qualquer horário">
                    <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Pra hoje, em qualquer horário
                </button>
                
                <button class="time-option" data-value="Pra hoje no horário comercial">
                    <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Pra hoje no horário comercial
                </button>
                
                <button class="time-option" data-value="specific-date-time">
                    <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <circle cx="12" cy="16" r="1"/>
                    </svg>
                    Agendar data e hora específicas
                </button>
            </div>
        </div>
    </div>

    <!-- Adicionando modal popup para seleção de data e hora -->
    <div class="modal-overlay" id="dateTimeModal">
        <div class="modal-content">
            <div class="modal-title">Selecione a data e horário desejados</div>
            <div class="date-time-inputs">
                <div class="input-group">
                    <label for="dateInput">Data</label>
                    <input type="date" id="dateInput" min="">
                </div>
                <div class="input-group">
                    <label for="timeInput">Horário</label>
                    <input type="time" id="timeInput">
                    <div id="timeError" class="error-message">Por favor, selecione um horário válido</div>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn cancel" onclick="closeModal()">Cancelar</button>
                <button class="modal-btn" onclick="confirmDateTime()">Confirmar</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeOptions = document.getElementById('timeOptions');
            const dateTimeModal = document.getElementById('dateTimeModal');
            const dateInput = document.getElementById('dateInput');
            const timeInput = document.getElementById('timeInput');
            
            let selectedOption = null;
            let selectedDate = null;
            let selectedTime = null;

            // Define data mínima como hoje
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;

            // Manipula cliques nas opções de tempo
            timeOptions.addEventListener('click', function(event) {
                const clickedOption = event.target.closest('.time-option');
                if (clickedOption) {
                    // Remove seleção de todas as opções
                    const options = timeOptions.querySelectorAll('.time-option');
                    options.forEach(option => option.classList.remove('selected'));
                    
                    // Adiciona seleção à opção clicada
                    clickedOption.classList.add('selected');
                    selectedOption = clickedOption.dataset.value;
                    
                    if (selectedOption === 'specific-date-time') {
                        openModal();
                    } else {
                        handleAutoRedirect();
                    }
                }
            });

            function openModal() {
                dateTimeModal.classList.add('active');
                // Limpa valores anteriores
                dateInput.value = '';
                timeInput.value = '';
                selectedDate = null;
                selectedTime = null;
            }

            window.closeModal = function() {
                dateTimeModal.classList.remove('active');
                // Remove seleção da opção
                const options = timeOptions.querySelectorAll('.time-option');
                options.forEach(option => option.classList.remove('selected'));
                selectedOption = null;
            }

            window.confirmDateTime = function() {
                selectedDate = dateInput.value;
                selectedTime = timeInput.value;
                
                if (!selectedDate || !selectedTime) {
                    alert('Por favor, selecione uma data e horário válidos.');
                    return;
                }
                
                dateTimeModal.classList.remove('active');
                handleAutoRedirect();
            }

            // Valida seleção de data
            dateInput.addEventListener('change', function() {
                selectedDate = this.value;
            });

            // Valida seleção de hora
            timeInput.addEventListener('change', function() {
                selectedTime = this.value;
            });

            function handleAutoRedirect() {
                if (selectedOption) {
                    let tempoParam = '';
                    
                  if (selectedOption === 'specific-date-time' && selectedDate && selectedTime) {
    const dateObj = new Date(selectedDate);
    const formattedDate = dateObj.toLocaleDateString('pt-BR'); // dd/mm/aaaa
    tempoParam = `Agendado para ${formattedDate} às ${selectedTime}`;
    window.location.href = `opcoes3.php?codgrupo=<?php echo $_GET['codgrupo']; ?>&categoria=<?php echo $_GET['categoria']; ?>&local=<?php echo $_GET['local']; ?>&tempo=${encodeURIComponent(tempoParam)}`;
} else if (selectedOption === 'emergency') {
                        tempoParam = 'Até 1 hora (Emergência)';
                    } else if (selectedOption === 'today-anytime') {
                        tempoParam = 'Pra hoje, em qualquer horário';
                    } else if (selectedOption === 'today-business') {
                        tempoParam = 'Pra hoje no horário comercial';
                    }
                    
                    setTimeout(() => {
                          window.location.href = `opcoes3.php?codgrupo=<?php echo $_GET['codgrupo']; ?>&categoria=<?php echo $_GET['categoria']; ?>&local=<?php echo $_GET['local']; ?>&tempo=${encodeURIComponent(selectedOption)}`;

                    }, 300);
                }
            }
        });
    </script>
</body>
</html>
