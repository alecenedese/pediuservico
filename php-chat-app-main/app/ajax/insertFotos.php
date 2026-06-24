<?php 
session_start();
$codpedido = $_GET['codpedido'];

# check if the user is logged in
if (isset($_SESSION['username'])) {

	if (isset($_GET['to_id'])) {
	
	# database connection file
	include '../db.conn.php';

	$message = '';

	if (!empty($_FILES['files']['name'][0])) {
		$uploadedFiles = $_FILES['files'];
	
		// Array para armazenar os caminhos dos arquivos enviados
		$uploadedPaths = [];
	
		// Diretório para salvar os arquivos
		$uploadDir = '../../../fotos/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0777, true); // Cria o diretório se não existir
		}
	
// Processar cada arquivo enviado
for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
    $fileName = basename($uploadedFiles['name'][$i]);
    $tempPath = $uploadedFiles['tmp_name'][$i];
    $filePath = $uploadDir . $fileName; // Caminho completo no servidor

    // Mover o arquivo para o diretório de uploads
    if (move_uploaded_file($tempPath, $filePath)) {
        // Garantir que o caminho relativo seja armazenado corretamente
        $uploadedPaths[] = str_replace('../', '', $uploadDir) . $fileName; 
    } else {
        $message .= "Erro ao mover o arquivo: " . $fileName . "<br>";
    }
}

// Gerar a mensagem com as imagens enviadas
if (!empty($uploadedPaths)) {
	foreach ($uploadedPaths as $path) {
		$message .= "<div style='position: relative; margin-top: 10px; width: 300px; border-radius: .25rem !important; border: 1px solid #dee2e6 !important; background: #f8f9fa; '>
		<img src='../../$path' alt='Uploaded Image' style='width: 278px; margin: 10px; cursor: pointer;' onclick=\"openModal('../../$path')\">
		</div>";
	}

	
} else {
    $message = "Erro no upload dos arquivos.";
}


	
	} else {
		echo "Nenhum arquivo enviado.";
	}
	


	$to_id = $_GET['to_id'];

	# get the logged in user's username from the SESSION
	$from_id = $_SESSION['user_id'];

	$sql = "INSERT INTO 
	       chats (from_id, to_id, message, codpedido) 
	       VALUES (?, ?, ?, '$codpedido')";
	$stmt = $conn->prepare($sql);
	$res  = $stmt->execute([$from_id, $to_id, $message]);
    
    # if the message inserted
    if ($res) {
    	/**
       check if this is the first
       conversation between them
       **/
       $sql2 = "SELECT * FROM conversations
               WHERE (user_1=? AND user_2=?)
               OR    (user_2=? AND user_1=?)";
       $stmt2 = $conn->prepare($sql2);
	   $stmt2->execute([$from_id, $to_id, $from_id, $to_id]);

	    // setting up the time Zone
		// It Depends on your location or your P.c settings

		setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
		date_default_timezone_set('America/Cuiaba');

		$time = date("h:i:s a");

		if ($stmt2->rowCount() == 0 ) {
			# insert them into conversations table 
			$sql3 = "INSERT INTO 
			         conversations(user_1, user_2)
			         VALUES (?,?)";
			$stmt3 = $conn->prepare($sql3); 
			$stmt3->execute([$from_id, $to_id]);
		}
		?>

		<div class="msg-sent">
		    <?=$message?>  
		    <span class="msg-time"><?=$time?></span>
		</div>

    <?php 
     }
  }
}else {
	
}