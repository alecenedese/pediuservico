<?php
session_start();
include("send.php");

$grupo = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$subcategoria = isset($_GET['subcategoria']) ? $_GET['subcategoria'] : '';

// Reuso de pedido: se voltou de novomapa.php, recebe o codpedido para reaproveitar (sem duplicar)
$codpedidoReuso = isset($_GET['codpedido']) ? intval($_GET['codpedido']) : 0;
$pedidoExistente = null;
if ($codpedidoReuso > 0) {
    $qPed = mysqli_query($con, "SELECT descricao, local, tempo FROM pedido WHERE codigo='".intval($codpedidoReuso)."' AND status='Procurando Prestador' LIMIT 1");
    if ($qPed && mysqli_num_rows($qPed) > 0) {
        $pedidoExistente = mysqli_fetch_assoc($qPed);
    } else {
        // Pedido não existe mais ou já foi aceito: não reaproveita
        $codpedidoReuso = 0;
    }
}

// Verifica se tem subcategorias (e se o usuário já não passou por elas)
if (empty($_GET['sub_ok']) && !empty($subcategoria)) {
    $queryGrupos = "SELECT codigo FROM subcategoria where categoria_id='".intval($subcategoria)."' ORDER BY titulo";
    $resGrupos = mysqli_query($con, $queryGrupos);
    $resultGrupos = $resGrupos ? mysqli_num_rows($resGrupos) : 0;
    if($resultGrupos > 0) {
        echo "<script> window.location.href='servicos.php?categoria_id=$subcategoria&grupo=$grupo';</script>";
        exit;
    }
}

// Item 19: Removido o check de login aqui - agora só verifica no momento do submit
// O usuário pode preencher todas as informações antes de fazer login
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pediu Servico - Solicitar Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <?php include('pwa-include.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8c 50%, #1e3a5f 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            padding-bottom: 65px;
        }

        .content-area {
            flex: 1;
            padding: 16px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
            overflow-y: auto;
        }

        .field-group { margin-bottom: 16px; }

        .field-label {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .custom-select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.12);
            font-size: 14px;
            font-weight: 500;
            color: #ffffff;
            font-family: inherit;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300d4ff' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .custom-select option { background: #1e3a5f; color: #fff; }
        .custom-select:focus {
            outline: none;
            border-color: #00d4ff;
            background: rgba(255,255,255,0.18);
        }

        .textarea {
            width: 100%;
            height: 80px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            color: #ffffff;
            font-size: 13px;
            resize: none;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .textarea::placeholder { color: rgba(255,255,255,0.5); }
        .textarea:focus {
            outline: none;
            border-color: #00d4ff;
            background: rgba(255,255,255,0.18);
        }

        .desc-row { display: flex; gap: 8px; align-items: stretch; }
        .desc-row .textarea { flex: 1; }

        .audio-col {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
            align-items: center;
        }

        .mic-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            background: #dc3545;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .mic-btn.recording { animation: pulse 1s infinite; }
        .mic-btn-label { font-size: 9px; color: rgba(255,255,255,0.6); text-align: center; }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220,53,69,0.5); }
            50% { box-shadow: 0 0 0 10px rgba(220,53,69,0); }
        }

        .audio-timer { font-size: 11px; font-weight: bold; color: #dc3545; }
        .audio-preview { margin-top: 8px; }
        .audio-preview audio { width: 100%; height: 36px; }
        .btn-delete-audio {
            background: none; border: none; color: #ff6b7a;
            font-size: 11px; cursor: pointer; padding: 4px;
        }

        .photo-row { display: flex; gap: 8px; }
        .photo-slot {
            flex: 1;
            aspect-ratio: 1;
            border: 2px dashed rgba(255,255,255,0.3);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: rgba(255,255,255,0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .photo-slot:hover { border-color: #00d4ff; background: rgba(255,255,255,0.15); }
        .photo-slot.has-image { border-style: solid; border-color: #00d4ff; }
        .photo-slot img { width: 100%; height: 100%; object-fit: cover; }
        .photo-slot .remove-btn {
            position: absolute; top: 4px; right: 4px;
            background: rgba(220,53,69,0.9); color: white;
            border: none; border-radius: 50%;
            width: 22px; height: 22px; cursor: pointer;
            font-size: 13px; display: flex; align-items: center; justify-content: center;
        }
        .photo-icon { font-size: 22px; opacity: 0.7; }
        .photo-label { font-size: 9px; color: rgba(255,255,255,0.5); margin-top: 2px; }

        .card-footer { margin-top: 8px; padding-bottom: 8px; }

        .agree-row {
            display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
        }
        .agree-row input[type="checkbox"] {
            width: 18px; height: 18px;
            accent-color: #00d4ff; cursor: pointer; flex-shrink: 0;
        }
        .agree-row label {
            font-size: 12px; color: rgba(255,255,255,0.8);
            line-height: 1.3; cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            border: none; color: white; font-size: 16px; font-weight: 600;
            padding: 14px; border-radius: 12px; cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,188,212,0.4);
        }
        .submit-btn:disabled { opacity: 1; cursor: pointer; }
        .submit-btn:active:not(:disabled) { transform: scale(0.98); }

        .hidden { display: none; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            display: none; justify-content: center; align-items: center; z-index: 10000;
        }
        .modal-overlay.active { display: flex; }
        .modal-content {
            background: #1e3a5f; border: 1px solid rgba(255,255,255,0.2);
            border-radius: 16px; padding: 24px; max-width: 400px; width: 90%;
        }
        .modal-title {
            font-size: 16px; font-weight: 700; color: #fff;
            margin-bottom: 16px; text-align: center;
        }
        .input-group { margin-bottom: 16px; }
        .input-group label {
            display: block; font-size: 13px; color: rgba(255,255,255,0.7);
            margin-bottom: 6px; font-weight: 500;
        }
        .input-group input {
            width: 100%; padding: 10px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.25);
            border-radius: 8px; font-size: 14px; color: #fff;
        }
        .input-group input:focus {
            outline: none; border-color: #00d4ff; background: rgba(255,255,255,0.15);
        }
        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .modal-btn {
            flex: 1; padding: 12px; border-radius: 8px;
            border: none; font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .modal-btn.cancel { background: rgba(255,255,255,0.15); color: white; }
        .modal-btn.confirm {
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%); color: white;
        }
    </style>
</head>
<body>

<?php include('header-app.php'); ?>

<div class="content-area">

            <div class="field-group">
                <div class="field-label">📍 Local do Serviço</div>
                <select class="custom-select" id="select-local">
                    <option value="">Onde o serviço será realizado?</option>
                    <option value="O prestador irá até sua localização" selected>Quero que venha até mim</option>
                    <option value="Você irá até o local do prestador">Posso ir até o prestador</option>
                    <option value="A localização não é um fator importante">Tanto faz</option>
                </select>
            </div>

            <div class="field-group">
                <div class="field-label">⏰ Quando você precisa?</div>
                <select class="custom-select" id="select-tempo">
                    <option value="Prestador me diz uma data disponível" selected>Prestador me diz uma data disponível</option>
                    <option value="Até 1 hora (Emergência)">Emergência - Até 1 hora</option>
                    <option value="Pra hoje, em qualquer horário">Hoje - Qualquer horário</option>
                    <option value="Pra hoje no horário comercial">Hoje - Horário comercial</option>
                    <option value="specific-date">Agendar data e hora</option>
                </select>
            </div>

            <div class="field-group">
                <div class="field-label">📝 Descreva o serviço</div>
                <div class="desc-row">
                    <textarea id="service-description" class="textarea" placeholder="Descreva o que você precisa..."></textarea>
                    <div class="audio-col">
                        <button type="button" class="mic-btn" id="btn-record" onclick="toggleRecording()">🎙️</button>
                        <span class="mic-btn-label">Áudio</span>
                        <span class="audio-timer hidden" id="audio-timer">00:00</span>
                    </div>
                </div>
                <div class="audio-preview hidden" id="audio-preview">
                    <!-- Item 18: Áudios serão renderizados dinamicamente aqui -->
                </div>
            </div>

            <div class="field-group">
                <div class="field-label">📷 Adicione fotos (opcional)</div>
                <div class="photo-row">
                    <div class="photo-slot" onclick="selectPhoto(0)">
                        <input type="file" id="photo-0" class="hidden" accept="image/*" onchange="handlePhotoUpload(0)">
                        <span class="photo-icon">📷</span>
                        <span class="photo-label">Foto 1</span>
                    </div>
                    <div class="photo-slot" onclick="selectPhoto(1)">
                        <input type="file" id="photo-1" class="hidden" accept="image/*" onchange="handlePhotoUpload(1)">
                        <span class="photo-icon">📷</span>
                        <span class="photo-label">Foto 2</span>
                    </div>
                    <div class="photo-slot" onclick="selectPhoto(2)">
                        <input type="file" id="photo-2" class="hidden" accept="image/*" onchange="handlePhotoUpload(2)">
                        <span class="photo-icon">📷</span>
                        <span class="photo-label">Foto 3</span>
                    </div>
                    <div class="photo-slot" onclick="selectPhoto(3)">
                        <input type="file" id="photo-3" class="hidden" accept="image/*" onchange="handlePhotoUpload(3)">
                        <span class="photo-icon">📷</span>
                        <span class="photo-label">Foto 4</span>
                    </div>
                </div>
            </div>


        <div class="card-footer">
            <div class="agree-row">
                <input type="checkbox" id="agreement">
                <label for="agreement">Concordo em buscar um prestador. O valor será acordado com o profissional.</label>
            </div>
            <button class="submit-btn" id="submit-btn" type="button" onclick="submitService()">Encontrar Profissional</button>
        </div>
</div>

<!-- Modal de Data/Hora -->
<div class="modal-overlay" id="dateTimeModal">
    <div class="modal-content">
        <div class="modal-title">Escolha a data e horário</div>
        <div class="input-group">
            <label for="dateInput">Data</label>
            <input type="date" id="dateInput" min="">
        </div>
        <div class="input-group">
            <label for="timeInput">Horário</label>
            <input type="time" id="timeInput">
        </div>
        <div class="modal-buttons">
            <button class="modal-btn cancel" onclick="closeModal()">Cancelar</button>
            <button class="modal-btn confirm" onclick="confirmDateTime()">Confirmar</button>
        </div>
    </div>
</div>

<script>
let selectedLocal = 'O prestador irá até sua localização';
let selectedTempo = 'Prestador me diz uma data disponível';
let selectedDate = '';
let selectedTime = '';
// Categoria (grupo) e subcategoria — guardadas em JS para sobreviver ao fluxo de login
let catParam = <?php echo json_encode((string)$grupo); ?>;
let subParam = <?php echo json_encode((string)$subcategoria); ?>;
const uploadedPhotos = {};

let mediaRecorder = null;
let audioChunks = [];
let recordedAudioBlobs = []; // Item 18: Array para múltiplos áudios
let recordingInterval = null;
let recordingSeconds = 0;

// Validação do formulário (apenas visual, botão sempre ativo)
function validateForm() {
    // Botão sempre habilitado - validação ocorre no submit
}

// Seleção de local
document.getElementById('select-local').addEventListener('change', function() {
    selectedLocal = this.value;
    validateForm();
});

// Seleção de tempo
document.getElementById('select-tempo').addEventListener('change', function() {
    if (this.value === 'specific-date') {
        openModal();
    } else {
        selectedTempo = this.value;
        selectedDate = '';
        selectedTime = '';
        validateForm();
    }
});

// Modal de data/hora
const today = new Date().toISOString().split('T')[0];
document.getElementById('dateInput').min = today;

function openModal() {
    document.getElementById('dateTimeModal').classList.add('active');
    document.getElementById('dateInput').value = '';
    document.getElementById('timeInput').value = '';
}

function closeModal() {
    document.getElementById('dateTimeModal').classList.remove('active');
    document.getElementById('select-tempo').value = '';
    selectedTempo = '';
}

function confirmDateTime() {
    selectedDate = document.getElementById('dateInput').value;
    selectedTime = document.getElementById('timeInput').value;
    
    if (!selectedDate || !selectedTime) {
        alert('Por favor, selecione uma data e horário válidos.');
        return;
    }
    
    const dateObj = new Date(selectedDate);
    const formattedDate = dateObj.toLocaleDateString('pt-BR');
    selectedTempo = `Agendado para ${formattedDate} às ${selectedTime}`;
    
    document.getElementById('dateTimeModal').classList.remove('active');
    validateForm();
}

// Gravação de áudio
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
        let mimeType = 'audio/webm;codecs=opus';
        if (typeof MediaRecorder.isTypeSupported === 'function') {
            if (MediaRecorder.isTypeSupported('audio/mp4')) {
                mimeType = 'audio/mp4';
            }
        }
        mediaRecorder = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);

        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
        mediaRecorder.onstop = () => {
            stream.getTracks().forEach(t => t.stop());
            const usedMime = mediaRecorder.mimeType || 'audio/webm';
            const newBlob = new Blob(audioChunks, { type: usedMime });
            // Item 18: Adiciona ao array ao invés de sobrescrever
            recordedAudioBlobs.push(newBlob);
            renderAudioPreviews();
            validateForm();
        };

        mediaRecorder.start();
        recordingSeconds = 0;
        updateTimerDisplay();
        document.getElementById('audio-timer').classList.remove('hidden');
        const btn = document.getElementById('btn-record');
        btn.classList.add('recording');
        btn.innerHTML = '⏹️';
        document.getElementById('audio-timer').classList.remove('hidden');

        recordingInterval = setInterval(() => {
            recordingSeconds++;
            updateTimerDisplay();
            if (recordingSeconds >= 120) stopRecording();
        }, 1000);
    }).catch(err => {
        alert('Não foi possível acessar o microfone.');
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
    btn.innerHTML = '🎙️';
}

// Item 18: Função para deletar um áudio específico
function deleteAudio(index) {
    recordedAudioBlobs.splice(index, 1);
    renderAudioPreviews();
    validateForm();
}

// Item 18: Renderiza todos os áudios gravados
function renderAudioPreviews() {
    const container = document.getElementById('audio-preview');
    if (recordedAudioBlobs.length === 0) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    container.innerHTML = '';
    
    recordedAudioBlobs.forEach((blob, index) => {
        const url = URL.createObjectURL(blob);
        const audioDiv = document.createElement('div');
        audioDiv.style.marginBottom = '8px';
        audioDiv.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;">
                <audio controls style="flex:1;height:36px;" preload="metadata">
                    <source src="${url}">
                </audio>
                <button type="button" class="btn-delete-audio" onclick="deleteAudio(${index})">🗑️</button>
            </div>
        `;
        container.appendChild(audioDiv);
    });
}

function updateTimerDisplay() {
    const m = String(Math.floor(recordingSeconds / 60)).padStart(2, '0');
    const s = String(recordingSeconds % 60).padStart(2, '0');
    document.getElementById('audio-timer').textContent = m + ':' + s;
}

// Upload de fotos
function selectPhoto(index) {
    document.getElementById(`photo-${index}`).click();
}

function handlePhotoUpload(index) {
    const input = document.getElementById(`photo-${index}`);
    const file = input.files[0];
    
    if (file) {
        uploadedPhotos[index] = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            const photoSlot = input.parentElement;
            photoSlot.innerHTML = `
                <img src="${e.target.result}" alt="Foto ${index + 1}">
                <button class="remove-btn" onclick="removePhoto(${index}, event)">×</button>
            `;
            photoSlot.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    }
}

function removePhoto(index, event) {
    event.stopPropagation();
    delete uploadedPhotos[index];
    const photoSlot = document.getElementById(`photo-${index}`).parentElement;
    photoSlot.innerHTML = `
        <input type="file" id="photo-${index}" class="hidden" accept="image/*" onchange="handlePhotoUpload(${index})">
        <span class="photo-icon">📷</span>
        <span class="photo-label">Foto ${index + 1}</span>
    `;
    photoSlot.classList.remove('has-image');
    photoSlot.onclick = () => selectPhoto(index);
}

// Event listeners
document.getElementById('service-description').addEventListener('input', validateForm);
document.getElementById('agreement').addEventListener('change', validateForm);

// Submissão do serviço
function submitService() {
    const description = document.getElementById('service-description').value.trim();
    
    if (!document.getElementById('agreement').checked) {
        alert('Você precisa concordar com os termos para continuar.');
        return;
    }

    if (!description && recordedAudioBlobs.length === 0) {
        alert('Favor gravar áudio ou escrever texto.');
        return;
    }
    
    // Item 19: Verifica se está logado ANTES de enviar
    // Se não estiver, salva dados na sessionStorage e redireciona para login
    const logado = <?php
        $logadoNovo  = isset($_COOKIE['login_unificado']) && $_COOKIE['login_unificado'] === '1';
        $logadoLegado = isset($_COOKIE['celular_usuario']) && !empty($_COOKIE['celular_usuario']);
        $logadoPrest = isset($_COOKIE['login']) && !empty($_COOKIE['login']);
        echo ($logadoNovo || $logadoLegado || $logadoPrest) ? 'true' : 'false';
    ?>;
    
    if (!logado) {
        // Salva dados do formulário para recuperar após login
        const formData = {
            descricao: description,
            local: selectedLocal,
            tempo: selectedTempo,
            selectedDate: selectedDate,
            selectedTime: selectedTime,
            cat: catParam,
            sub: subParam,
            agreement: document.getElementById('agreement').checked
        };
        sessionStorage.setItem('pedido_temp', JSON.stringify(formData));
        // Marca que veio do fluxo de solicitar serviço (para auto-submit após login)
        sessionStorage.setItem('fluxo_servico', '1');

        // Redireciona para login com retorno para esta página
        const currentUrl = window.location.href;
        window.location.href = 'login-unificado.php?retorno=' + encodeURIComponent(currentUrl) + '&from=servico';
        return;
    }
    
    const formData = new FormData();
    formData.append('descricao', description);
    
    // Adicionar fotos
    Object.keys(uploadedPhotos).forEach(index => {
        formData.append('fotos[]', uploadedPhotos[index]);
    });
    
    // Item 18: Adicionar múltiplos áudios
    if (recordedAudioBlobs.length > 0) {
        recordedAudioBlobs.forEach((blob, index) => {
            const ext = blob.type.includes('mp4') ? 'mp4' : 'webm';
            formData.append('audios[]', blob, `audio_${index}.${ext}`);
        });
    }
    
    // Mostrar loading
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.textContent = 'Enviando...';
    submitBtn.disabled = true;
    
    // Enviar para o servidor (com retry automático se o servidor estiver sobrecarregado)
    var urlSalvar = 'salvarorcamento.php?codgrupo=' + encodeURIComponent(catParam) + '&categoria=' + encodeURIComponent(subParam) + '<?php echo $codpedidoReuso > 0 ? '&codpedido='.$codpedidoReuso : ''; ?>&local=' + encodeURIComponent(selectedLocal) + '&tempo=' + encodeURIComponent(selectedTempo);

    function enviarComRetry(tentativasRestantes, delay) {
        fetch(urlSalvar, { method: 'POST', body: formData })
        .then(function(response) {
            // 508/503/502/429 = servidor recusou por limite de recursos (nada foi criado) -> retry seguro
            if (response.status === 508 || response.status === 503 || response.status === 502 || response.status === 429) {
                if (tentativasRestantes > 0) {
                    submitBtn.textContent = 'Servidor ocupado, tentando...';
                    setTimeout(function() {
                        enviarComRetry(tentativasRestantes - 1, Math.min(delay * 2, 4000));
                    }, delay);
                    return null;
                }
                throw new Error('Servidor ocupado (HTTP ' + response.status + ')');
            }
            return response.json();
        })
        .then(function(data) {
            if (data === null) return; // retry agendado
            if (data.success) {
                sessionStorage.removeItem('pedido_temp');
                var pid = data.pedido_id || '';
                window.location.href = 'mapa.php?subcategoria=' + encodeURIComponent(subParam) + (pid ? '&codpedido=' + pid : '');
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
                submitBtn.textContent = 'Enviar';
                submitBtn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Erro:', error);
            alert('Erro ao enviar solicitacao. Tente novamente.');
            submitBtn.textContent = 'Enviar';
            submitBtn.disabled = false;
        });
    }

    // até 4 tentativas com backoff: 600ms -> 1.2s -> 2.4s -> 4s
    enviarComRetry(4, 600);
}

// Item 19: Recupera dados salvos após login e auto-submete se veio do fluxo de serviço
document.addEventListener('DOMContentLoaded', function() {
    const savedData = sessionStorage.getItem('pedido_temp');
    const fluxoServico = sessionStorage.getItem('fluxo_servico');
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            document.getElementById('service-description').value = data.descricao || '';
            selectedLocal = data.local || 'O prestador irá até sua localização';
            selectedTempo = data.tempo || 'Prestador me diz uma data disponível';
            selectedDate = data.selectedDate || '';
            selectedTime = data.selectedTime || '';
            // Recupera categoria/subcategoria se os valores da URL vieram vazios após o login
            if (data.cat && !catParam) catParam = data.cat;
            if (data.sub && !subParam) subParam = data.sub;
            document.getElementById('agreement').checked = data.agreement || false;

            // Atualiza os selects
            document.getElementById('select-local').value = selectedLocal;
            document.getElementById('select-tempo').value = selectedTempo;

            validateForm();

            // Se veio do fluxo de serviço (após login), auto-submete após breve delay
            if (fluxoServico === '1') {
                sessionStorage.removeItem('fluxo_servico');
                setTimeout(function() {
                    submitService();
                }, 500);
            }
        } catch (e) {
            console.error('Erro ao recuperar dados salvos:', e);
        }
    }
});

// Inicializar
validateForm();

<?php if ($pedidoExistente): ?>
// Reuso de pedido: pré-preenche o formulário com os dados do pedido existente
(function() {
    var descPed = <?php echo json_encode($pedidoExistente['descricao']); ?>;
    var localPed = <?php echo json_encode($pedidoExistente['local']); ?>;
    var tempoPed = <?php echo json_encode($pedidoExistente['tempo']); ?>;

    if (descPed) document.getElementById('service-description').value = descPed;
    if (localPed) {
        selectedLocal = localPed;
        var selLocal = document.getElementById('select-local');
        if ([].some.call(selLocal.options, function(o){ return o.value === localPed; })) {
            selLocal.value = localPed;
        }
    }
    if (tempoPed) {
        selectedTempo = tempoPed;
        var selTempo = document.getElementById('select-tempo');
        if ([].some.call(selTempo.options, function(o){ return o.value === tempoPed; })) {
            selTempo.value = tempoPed;
        }
    }
    // Já passou pelos termos antes
    document.getElementById('agreement').checked = true;
})();
<?php endif; ?>
</script>

<?php $navAtiva = 'buscar'; include('bottom-nav.php'); ?>
</body>
</html>
