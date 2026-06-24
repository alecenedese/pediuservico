<?php 
session_start();

$_SESSION['username'] = $_GET['user'];
$_SESSION['user_id'] = $_GET['user_id'];
$user_from = $_GET['user_from'];

if (isset($_SESSION['username'])) {
    include 'app/db.conn.php';
    include 'app/helpers/user.php';
    include 'app/helpers/chat.php';
    include 'app/helpers/opened.php';
    include 'app/helpers/timeAgo.php';

    $chatWith = getUser($user_from, $conn);
    if (empty($chatWith)) { $chatWith = ['name'=>'Usuário','p_p'=>'user-default.png','last_seen'=>'','user_id'=>$user_from,'celular'=>'']; }

    $chats = getChats($_SESSION['user_id'], $chatWith['user_id'], $conn, $_GET['codpedido']);
    opened($chatWith['user_id'], $conn, $chats);

    function DataHora($data) {
        $dataK = explode(' ', $data);
        $dataK2 = explode(':', $dataK[1] ?? '00:00:00');
        $dataKK = explode('-', $dataK[0]);
        return ($dataKK[2] ?? '').'/'.$dataKK[1].'/'.($dataKK[0] ?? '').' '.$dataK2[0].':'.$dataK2[1];
    }

    // WhatsApp link
    $celularWhatsApp = isset($chatWith['celular']) ? preg_replace('/\D/', '', $chatWith['celular']) : '';
    if (!empty($celularWhatsApp) && strlen($celularWhatsApp) >= 10) {
        if (strlen($celularWhatsApp) <= 11) $celularWhatsApp = '55' . $celularWhatsApp;
        $whatsappUrl = 'https://wa.me/' . $celularWhatsApp;
    } else {
        $whatsappUrl = '';
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chat - USERVICE</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body {
            height: 100%;
        }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* Header do chat */
        .chat-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: rgba(0,212,255,0.08);
            border-bottom: 1px solid rgba(0,212,255,0.2);
            flex-shrink: 0;
        }
        .chat-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            border: 2px solid #00d4ff;
            object-fit: cover;
            flex-shrink: 0;
        }
        .chat-user-info { flex: 1; }
        .chat-user-name { font-size: 15px; font-weight: 700; color: #fff; }
        .chat-user-status { font-size: 11px; color: rgba(255,255,255,0.6); display: flex; align-items: center; gap: 5px; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; }
        .chat-actions { display: flex; gap: 6px; }
        .chat-actions a {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-whatsapp { background: #25D366; color: #fff; }
        .btn-location { background: #0ea5e9; color: #fff; border: none; cursor: pointer; padding: 8px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }

        /* Área de mensagens */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        /* Bolha de mensagem enviada (direita) */
        .msg-sent {
            align-self: flex-end;
            max-width: 80%;
            background: linear-gradient(135deg, #00bcd4, #0097a7);
            color: #fff;
            padding: 10px 14px;
            border-radius: 14px 14px 4px 14px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
            box-shadow: 0 2px 6px rgba(0,188,212,0.3);
        }

        /* Bolha de mensagem recebida (esquerda) */
        .msg-received {
            align-self: flex-start;
            max-width: 80%;
            background: rgba(255,255,255,0.1);
            color: #fff;
            padding: 10px 14px;
            border-radius: 14px 14px 14px 4px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }

        .msg-time {
            display: block;
            font-size: 10px;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }

        .msg-empty {
            text-align: center;
            color: rgba(0,212,255,0.7);
            padding: 40px 20px;
            font-size: 14px;
        }
        .msg-empty .emoji { font-size: 40px; margin-bottom: 10px; display: block; }

        /* Imagens e mapas no chat */
        .chat-messages img {
            max-width: 220px;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid rgba(0,212,255,0.3);
        }
        .chat-messages iframe {
            width: 100%;
            max-width: 250px;
            height: 150px;
            border-radius: 10px;
            border: 2px solid rgba(0,212,255,0.3);
            display: block;
        }
        .msg-sent iframe, .msg-received iframe {
            max-width: 100%;
        }

        /* Input */
        .chat-input-area {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: rgba(0,212,255,0.06);
            border-top: 1px solid rgba(0,212,255,0.2);
            flex-shrink: 0;
        }
        .chat-input-area textarea {
            flex: 1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(0,212,255,0.3);
            border-radius: 12px;
            color: #fff;
            padding: 10px 14px;
            font-size: 14px;
            resize: none;
            max-height: 80px;
            min-height: 42px;
            font-family: inherit;
        }
        .chat-input-area textarea:focus { outline: none; border-color: #00d4ff; background: rgba(255,255,255,0.12); }
        .chat-input-area textarea::placeholder { color: rgba(255,255,255,0.4); }
        .btn-send {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00d4ff, #0097a7);
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .btn-send:active { transform: scale(0.93); }

        /* Modal de imagem */
        .img-modal {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .img-modal.active { display: flex; }
        .img-modal img { max-width: 92%; max-height: 88vh; border-radius: 10px; object-fit: contain; }
        .img-modal-close {
            position: absolute; top: 12px; right: 16px;
            background: rgba(255,255,255,0.15);
            border: none; color: #fff; font-size: 22px;
            padding: 8px 14px; border-radius: 8px; cursor: pointer;
        }

        /* Scrollbar */
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-track { background: transparent; }
        .chat-messages::-webkit-scrollbar-thumb { background: rgba(0,212,255,0.3); border-radius: 4px; }
    </style>
</head>
<body>

<!-- Header -->
<div class="chat-header">
    <img src="uploads/<?=$chatWith['p_p']?>" class="chat-avatar" alt="">
    <div class="chat-user-info">
        <div class="chat-user-name"><?=htmlspecialchars($chatWith['name'])?></div>
        <div class="chat-user-status">
            <?php if (last_seen($chatWith['last_seen']) == "Active") { ?>
                <span class="status-dot"></span> Online
            <?php } else { ?>
                Visto: <?=last_seen($chatWith['last_seen'])?>
            <?php } ?>
        </div>
    </div>
    <div class="chat-actions">
        <?php if (!empty($whatsappUrl)) { ?>
            <a href="<?=$whatsappUrl?>" target="_blank" class="btn-whatsapp">💬 WhatsApp</a>
        <?php } ?>
        <button type="button" class="btn-location" id="sendLocal">📍 Enviar localização</button>
    </div>
</div>

<!-- Mensagens -->
<div class="chat-messages" id="chatBox">
    <?php if (!empty($chats)) {
        foreach($chats as $chat) {
            $isMine = ($chat['from_id'] == $_SESSION['user_id']);
            $cls = $isMine ? 'msg-sent' : 'msg-received';
    ?>
        <div class="<?=$cls?>"><?=$chat['message']?><span class="msg-time"><?=DataHora($chat['created_at'])?></span></div>
    <?php } } else { ?>
        <div class="msg-empty"><span class="emoji">💬</span>Nenhuma mensagem ainda.<br>Inicie a conversa!</div>
    <?php } ?>
</div>

<!-- Input -->
<div class="chat-input-area">
    <textarea id="message" placeholder="Digite sua mensagem..." rows="1"></textarea>
    <button class="btn-send" id="sendBtn">➤</button>
</div>

<!-- Modal de imagem -->
<div class="img-modal" id="imageModal">
    <button class="img-modal-close" onclick="document.getElementById('imageModal').classList.remove('active')">✕</button>
    <img id="modalImage" src="">
</div>

<!-- Hidden form para geolocalização -->
<form style="display:none;">
    <input type="text" id="latitude" readonly>
    <input type="text" id="longitude" readonly>
</form>

<script>
function openModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.add('active');
}

var scrollDown = function(){
    var chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
};
scrollDown();

$(document).ready(function(){
    var userLat = null, userLng = null;

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(p){ userLat = p.coords.latitude; userLng = p.coords.longitude; },
                function(e){ console.error('Geo:', e.message); }
            );
        }
    }
    getLocation();

    // Enviar mensagem
    function enviarMsg() {
        var msg = $("#message").val().trim();
        if (!msg) return;
        $.post("app/ajax/insert.php?codpedido=<?=$_GET['codpedido']?>",
            { message: msg, to_id: <?=$chatWith['user_id']?> },
            function(data){
                $("#message").val("");
                $("#chatBox").append(data);
                scrollDown();
            });
    }

    $("#sendBtn").on('click', enviarMsg);
    $("#message").on('keydown', function(e){
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMsg(); }
    });

    // Enviar localização
    $("#sendLocal").on('click', function(){
        if (!userLat) {
            getLocation();
            setTimeout(function(){ if(userLat) enviarLoc(); else alert('Ative a localização.'); }, 1500);
        } else { enviarLoc(); }
    });

    function enviarLoc() {
        $.post("app/ajax/insertLocal.php?codpedido=<?=$_GET['codpedido']?>",
            { latitude: userLat, longitude: userLng, message: '', to_id: <?=$chatWith['user_id']?> },
            function(data){ $("#chatBox").append(data); scrollDown(); });
    }

    // Auto-refresh mensagens
    function fetchNew(){
        $.post("app/ajax/getMessage.php?codpedido=<?=$_GET['codpedido']?>",
            { id_2: <?=$chatWith['user_id']?> },
            function(data){ if(data.trim()){ $("#chatBox").append(data); scrollDown(); } });
    }
    setInterval(fetchNew, 500);

    // Last seen
    function updateSeen(){ $.get("app/ajax/update_last_seen.php"); }
    updateSeen();
    setInterval(updateSeen, 10000);
});
</script>
</body>
</html>
<?php
} else {
    header("Location: index.php");
    exit;
}
?>
