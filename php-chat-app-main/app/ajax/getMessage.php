<?php 

session_start();
$codpedido = $_GET['codpedido'];
# check if the user is logged in
if (isset($_SESSION['username'])) {

	if (isset($_POST['id_2'])) {
	
	# database connection file
	include '../db.conn.php';

	$id_1  = $_SESSION['user_id'];
	$id_2  = $_POST['id_2'];
	$opend = 0;

	$sql = "SELECT * FROM chats
	        WHERE to_id=?
	        AND   from_id= ?
			and codpedido='$codpedido'
	        ORDER BY chat_id ASC";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id_1, $id_2]);

	if ($stmt->rowCount() > 0) {
	    $chats = $stmt->fetchAll();

	    # looping through the chats
	    foreach ($chats as $chat) {
	    	if ($chat['opened'] == 0) {
	    		
	    		$opened = 1;
	    		$chat_id = $chat['chat_id'];

	    		$sql2 = "UPDATE chats
	    		         SET opened = ?
	    		         WHERE chat_id = ?";
	    		$stmt2 = $conn->prepare($sql2);
	            $stmt2->execute([$opened, $chat_id]); 

	            ?>
                  <div class="msg-received">
                      <?=$chat['message']?> 
                      <span class="msg-time"><?=$chat['created_at']?></span>
                  </div>
	            <?php
	    	}
	    }
	}

 }

}else {
	
}