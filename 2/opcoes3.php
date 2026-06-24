<?php
// Redireciona para o novo arquivo unificado
$grupo = isset($_GET['codgrupo']) ? $_GET['codgrupo'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
header("Location: solicitar-servico.php?categoria=$grupo&subcategoria=$categoria");
exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Servico - Solicitar</title>
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
            padding: 0;
            padding-bottom: 130px;
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

        .back-button {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
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

        .menu-button2 {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 1px solid #00f0ff;
            color: #1a2332;
            font-size: 12px;
            font-weight: 800;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            text-decoration: none;
        }

        /* Main content layout converted to vertical flow */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 8px;
            padding-bottom: 120px; /* Space for fixed navigation */
            gap: 8px;
            max-width: 100%;
            overflow-y: auto;
        }

        /* Progress bar now in normal document flow */
        .progress-container {
            background: rgba(0, 212, 255, 0.1);
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
            flex-shrink: 0;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #00f0ff;
            font-weight: 600;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(0, 240, 255, 0.2);
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #00d4ff, #00f0ff);
            border-radius: 6px;
            transition: width 0.3s ease;
        }

        /* Step container now uses normal flow instead of absolute positioning */
        .step-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 8px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .step-title {
            font-size: 18px;
            font-weight: bold;
            color: #1a2332;
            text-align: center;
            margin-bottom: 8px;
        }

        .step-description {
            font-size: 13px;
            color: #666;
            text-align: center;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .textarea {
            width: 100%;
            min-height: 110px;
            padding: 13px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            color: #1a2332;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .textarea::placeholder {
            color: #6c757d;
        }

        .textarea:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
            background: #ffffff;
        }

        /* Photo grid converted to 2x2 grid for better mobile experience */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 8px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .photo-slot {
            aspect-ratio: 1;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: #f8f9fa;
            min-height: 80px;
            max-width: 100%;
            overflow: hidden;
        }

        .photo-slot:hover {
            border-color: #00d4ff;
            background: rgba(0, 212, 255, 0.05);
            transform: translateY(-2px);
        }

        .photo-slot.has-image {
            border-style: solid;
            border-color: #00d4ff;
            padding: 0;
        }

        .photo-slot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .photo-slot .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-slot .icon {
            width: 32px;
            height: 32px;
            margin-bottom: 6px;
            stroke: #6c757d;
            flex-shrink: 0;
        }

        .photo-slot .label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
            text-align: center;
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            flex: 1;
        }

        .checkbox {
            margin-top: 2px;
            accent-color: #00d4ff;
            width: 18px;
            height: 18px;
        }

        .checkbox-label {
            font-size: 14px;
            color: #1a2332;
            line-height: 1.5;
        }

        /* Navigation fixed at bottom so button is always visible */
        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            position: fixed;
            bottom: 56px;
            left: 0;
            right: 0;
            padding: 12px 16px;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            border-top: 1px solid rgba(0, 212, 255, 0.2);
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.3);
            z-index: 9999;
        }

        .nav-button {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .nav-button.back {
            background: #6c757d;
            color: white;
            border: 2px solid #6c757d;
        }

        .nav-button.back:hover {
            background: #5a6268;
            border-color: #5a6268;
            transform: translateY(-1px);
        }

        .nav-button.next {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: 2px solid #00f0ff;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .nav-button.next:hover {
            background: linear-gradient(145deg, #00f0ff, #40ffff);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
        }

        .nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .hidden {
            display: none;
        }

        .file-input {
            display: none;
        }

        /* Audio recorder styles */
        .audio-section {
            margin-top: 16px;
            padding: 16px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }

        .audio-section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 12px;
            text-align: center;
        }

        .audio-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .audio-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 25px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .audio-btn.record {
            background: #dc3545;
            color: white;
        }

        .audio-btn.record.recording {
            background: #c82333;
            animation: pulse-record 1s infinite;
        }

        .audio-btn.stop {
            background: #6c757d;
            color: white;
        }

        .audio-btn.delete {
            background: transparent;
            color: #dc3545;
            border: 2px solid #dc3545;
            padding: 8px 14px;
            font-size: 12px;
        }

        @keyframes pulse-record {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220,53,69,0.5); }
            50% { box-shadow: 0 0 0 10px rgba(220,53,69,0); }
        }

        .audio-timer {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            font-variant-numeric: tabular-nums;
        }

        .audio-preview {
            margin-top: 12px;
            width: 100%;
        }

        .audio-preview audio {
            width: 100%;
            height: 40px;
        }

        .audio-or-divider {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin: 10px 0 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Improved mobile responsiveness */
        @media (max-width: 768px) {
            .main-content {
                padding: 5px;
            }
            
            .step-container {
                padding: 13px;
            }
            
            .photo-grid {
                gap: 5px;
            }
            
            .navigation {
                flex-direction: column;
                gap: 8px;
                padding: 10px 12px;
            }
            
            .nav-button {
                width: 100%;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header with USERVICE branding and navigation -->
    <?php include('topo2.php'); ?>

    <script>
    // Sobrescrever o botão Voltar do header para voltar o step em vez de sair da página
    document.addEventListener('DOMContentLoaded', function() {
        var links = document.querySelectorAll('.header a');
        links.forEach(function(link) {
            if (link.getAttribute('href') === 'javascript: history.back()') {
                link.setAttribute('href', 'javascript:void(0)');
                link.onclick = function(e) {
                    e.preventDefault();
                    if (typeof currentStep !== 'undefined' && currentStep > 1) {
                        previousStep();
                    } else {
                        history.back();
                    }
                };
            }
        });
    });
    </script>


    <div class="main-content">
        <!-- Progress bar now in normal document flow -->
        <div class="progress-container">
            <div class="progress-info">
                <span id="step-indicator">Passo 1 de 3</span>
                <span id="progress-percentage">33% completo</span>
            </div>
            <div class="progress-bar">
                <div id="progress-fill" class="progress-fill" style="width: 33%;"></div>
            </div>
        </div>

        <!-- Step containers now use normal flow -->
        <div id="step-1" class="step-container">
            <div class="step-title">📝 Descreva seu serviço</div>
            <div class="step-description">
                Quanto mais detalhes você fornecer, melhor será a comunicação com os prestadores.
            </div>
            <textarea 
                id="service-description" 
                class="textarea" 
                placeholder="Ex: Preciso de um eletricista para instalar 3 tomadas novas no quarto. A fiação já está passada, falta apenas a instalação das tomadas."
            ></textarea>



            <div class="audio-section">
                <div class="audio-section-title">🎙️ Gravar áudio descrevendo o serviço</div>
                <div class="audio-controls" id="audio-controls">
                    <button type="button" class="audio-btn record" id="btn-record" onclick="toggleRecording()">
                        🎙️ Gravar
                    </button>
                    <span class="audio-timer hidden" id="audio-timer">00:00</span>
                </div>
                <div class="audio-preview hidden" id="audio-preview">
                    <audio id="audio-player" controls></audio>
                    <div style="text-align:center;margin-top:8px;">
                        <button type="button" class="audio-btn delete" onclick="deleteAudio()">🗑️ Remover áudio</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="step-2" class="step-container hidden">
            <div class="step-title">📷 Adicione fotos do serviço</div>
            <div class="step-description">
                Fotos ajudam os prestadores a entenderem melhor o serviço necessário.
            </div>
            <div class="photo-grid">
                <div class="photo-slot" onclick="selectPhoto(0)">
                    <input type="file" id="photo-0" class="file-input" accept="image/*" onchange="handlePhotoUpload(0)">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="label">Foto 1</span>
                </div>
                <div class="photo-slot" onclick="selectPhoto(1)">
                    <input type="file" id="photo-1" class="file-input" accept="image/*" onchange="handlePhotoUpload(1)">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="label">Foto 2</span>
                </div>
                <div class="photo-slot" onclick="selectPhoto(2)">
                    <input type="file" id="photo-2" class="file-input" accept="image/*" onchange="handlePhotoUpload(2)">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="label">Foto 3</span>
                </div>
                <div class="photo-slot" onclick="selectPhoto(3)">
                    <input type="file" id="photo-3" class="file-input" accept="image/*" onchange="handlePhotoUpload(3)">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="label">Foto 4</span>
                </div>
            </div>
        </div>

        <div id="step-3" class="step-container hidden">
            <div class="step-title">✅ Confirme os detalhes</div>
            <div class="step-description">
                Último passo antes de encontrarmos o prestador ideal para você.
            </div>
            <div class="checkbox-container">
                <input type="checkbox" id="agreement" class="checkbox" required>
                <label for="agreement" class="checkbox-label">
                    Concordo em buscar um prestador disponível sem orçamento prévio. 
                    Entendo que o valor final será acordado diretamente com o prestador.
                </label>
            </div>
        </div>

        <!-- Navigation now in normal flow instead of fixed positioning -->
        <div class="navigation">
            <button id="next-btn" class="nav-button next" onclick="nextStep()">
                Próximo →
            </button>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;
        const uploadedPhotos = {};

        // ===== Audio Recording (native MediaRecorder) =====
        let mediaRecorder = null;
        let audioChunks = [];
        let recordedAudioBlob = null;
        let recordingInterval = null;
        let recordingSeconds = 0;

        function toggleRecording() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                stopRecording();
            } else {
                startRecording();
            }
        }

        function startRecording() {
            navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                audioChunks = [];
                // prefer mp4/aac for iOS WebView compat, fallback to webm
                let mimeType = 'audio/webm;codecs=opus';
                if (typeof MediaRecorder.isTypeSupported === 'function') {
                    if (MediaRecorder.isTypeSupported('audio/mp4')) {
                        mimeType = 'audio/mp4';
                    } else if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                        mimeType = 'audio/webm;codecs=opus';
                    } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                        mimeType = 'audio/webm';
                    } else {
                        mimeType = '';
                    }
                }
                mediaRecorder = mimeType
                    ? new MediaRecorder(stream, { mimeType })
                    : new MediaRecorder(stream);

                mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
                mediaRecorder.onstop = () => {
                    stream.getTracks().forEach(t => t.stop());
                    const usedMime = mediaRecorder.mimeType || 'audio/webm';
                    recordedAudioBlob = new Blob(audioChunks, { type: usedMime });
                    const url = URL.createObjectURL(recordedAudioBlob);
                    document.getElementById('audio-player').src = url;
                    document.getElementById('audio-preview').classList.remove('hidden');
                };

                mediaRecorder.start();
                recordingSeconds = 0;
                updateTimerDisplay();
                document.getElementById('audio-timer').classList.remove('hidden');
                const btn = document.getElementById('btn-record');
                btn.classList.add('recording');
                btn.innerHTML = '⏹️ Parar';
                document.getElementById('audio-preview').classList.add('hidden');

                recordingInterval = setInterval(() => {
                    recordingSeconds++;
                    updateTimerDisplay();
                    if (recordingSeconds >= 120) stopRecording(); // max 2 min
                }, 1000);
            }).catch(err => {
                alert('Não foi possível acessar o microfone. Verifique as permissões.');
                console.error('Mic error:', err);
            });
        }

        function stopRecording() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
            }
            clearInterval(recordingInterval);
            const btn = document.getElementById('btn-record');
            btn.classList.remove('recording');
            btn.innerHTML = '🎙️ Gravar novamente';
        }

        function deleteAudio() {
            recordedAudioBlob = null;
            document.getElementById('audio-player').src = '';
            document.getElementById('audio-preview').classList.add('hidden');
            document.getElementById('audio-timer').classList.add('hidden');
            const btn = document.getElementById('btn-record');
            btn.innerHTML = '🎙️ Gravar';
            recordingSeconds = 0;
        }

        function updateTimerDisplay() {
            const m = String(Math.floor(recordingSeconds / 60)).padStart(2, '0');
            const s = String(recordingSeconds % 60).padStart(2, '0');
            document.getElementById('audio-timer').textContent = m + ':' + s;
        }

        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('step-indicator').textContent = `Passo ${currentStep} de ${totalSteps}`;
            document.getElementById('progress-percentage').textContent = `${Math.round(progress)}% completo`;
            document.getElementById('progress-fill').style.width = `${progress}%`;
        }

        function showStep(step) {
            // Hide all steps
            for (let i = 1; i <= totalSteps; i++) {
                document.getElementById(`step-${i}`).classList.add('hidden');
            }
            
            // Show current step
            document.getElementById(`step-${step}`).classList.remove('hidden');
            
            // Update navigation buttons
            const prevBtn = document.getElementById('prev-btn');
            if (prevBtn) {
                prevBtn.style.display = step === 1 ? 'none' : '';
            }
            
            const nextBtn = document.getElementById('next-btn');
            if (step === totalSteps) {
                nextBtn.textContent = 'Concluir';
            } else {
                nextBtn.textContent = 'Próximo →';
            }
        }

        function validateCurrentStep() {
            if (currentStep === 1) {
                const description = document.getElementById('service-description').value.trim();
                if (!description && !recordedAudioBlob) {
                    alert('Por favor, descreva o serviço por texto ou grave um áudio.');
                    return false;
                }
            } else if (currentStep === 3) {
                const agreement = document.getElementById('agreement').checked;
                if (!agreement) {
                    alert('Você precisa concordar com os termos para continuar.');
                    return false;
                }
            }
            return true;
        }

        function nextStep() {
            if (!validateCurrentStep()) {
                return;
            }

            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
                updateProgress();
            } else {
                submitServiceRequest();
            }
        }

        function submitServiceRequest() {
            console.log("[v0] Starting service request submission");
            
            const formData = new FormData();
            
            const description = document.getElementById('service-description').value.trim();
            formData.append('descricao', description);
            console.log("[v0] Description added:", description);
            
            const photosArray = [];
            Object.keys(uploadedPhotos).forEach(index => {
                photosArray.push(uploadedPhotos[index]);
            });
            
            // Add photos as array to match PHP expectations
            photosArray.forEach((photo, index) => {
                formData.append('fotos[]', photo);
            });
            console.log("[v0] Photos added:", photosArray.length, "photos");

            // Add audio if recorded
            if (recordedAudioBlob) {
                const ext = recordedAudioBlob.type.includes('mp4') ? 'mp4' : 'webm';
                formData.append('audio', recordedAudioBlob, 'audio_descricao.' + ext);
                console.log('[v0] Audio added, size:', recordedAudioBlob.size);
            }
            
            // Show loading state
            const nextBtn = document.getElementById('next-btn');
            const originalText = nextBtn.textContent;
            nextBtn.textContent = 'Enviando...';
            nextBtn.disabled = true;
            
            console.log("[v0] Making fetch request to processar_servico.php");
            
            // Submit to PHP
            fetch('salvarorcamento.php?codgrupo=<?php echo $_GET['codgrupo']; ?>&categoria=<?php echo $_GET['categoria']; ?>&local=<?php echo $_GET['local']; ?>&tempo=<?php echo $_GET['tempo']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("[v0] Response received:", response.status, response.statusText);
                console.log("[v0] Response headers:", response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.log("[v0] Response is not JSON, getting text instead");
                    return response.text().then(text => {
                        console.log("[v0] Response text:", text);
                        throw new Error('Resposta do servidor não é JSON válido: ' + text);
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log("[v0] JSON data received:", data);
                if (data.success) {
                    alert('Serviço solicitado com sucesso! Você será redirecionado para encontrar prestadores.');
                    window.location.href = 'mapa.php?subcategoria=<?php echo $_GET['categoria']; ?>';
                } else {
                    alert('Erro ao enviar solicitação: ' + (data.message || 'Erro desconhecido'));
                    // Restore button state
                    nextBtn.textContent = originalText;
                    nextBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('[v0] Fetch error:', error);
                console.log("[v0] Error details:", error.message);
                alert('Erro ao enviar solicitação: ' + error.message);
                // Restore button state
                nextBtn.textContent = originalText;
                nextBtn.disabled = false;
            });
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                updateProgress();
            }
        }

        function selectPhoto(index) {
            document.getElementById(`photo-${index}`).click();
        }

        function handlePhotoUpload(index) {
            const input = document.getElementById(`photo-${index}`);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const photoSlot = input.parentElement;
                    photoSlot.innerHTML = `
                        <img src="${e.target.result}" alt="Uploaded photo">
                        <button class="remove-btn" onclick="removePhoto(${index})" type="button">×</button>
                        <input type="file" id="photo-${index}" class="file-input" accept="image/*" onchange="handlePhotoUpload(${index})">
                    `;
                    photoSlot.classList.add('has-image');
                    uploadedPhotos[index] = file;
                };
                reader.readAsDataURL(file);
            }
        }

        function removePhoto(index) {
            const photoSlot = document.querySelector(`#photo-${index}`).parentElement;
            photoSlot.innerHTML = `
                <input type="file" id="photo-${index}" class="file-input" accept="image/*" onchange="handlePhotoUpload(${index})">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="label">Foto ${index + 1}</span>
            `;
            photoSlot.classList.remove('has-image');
            photoSlot.onclick = () => selectPhoto(index);
            delete uploadedPhotos[index];
        }

        // Initialize the page
        updateProgress();
        showStep(currentStep);
    </script>
</body>
</html>
