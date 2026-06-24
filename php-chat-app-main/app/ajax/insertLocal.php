<?php 
session_start();
$codpedido = $_GET['codpedido'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

# check if the user is logged in
if (isset($_SESSION['username'])) {

	if (isset($_POST['to_id'])) {
	
	# database connection file
	include '../db.conn.php';

	# get data from XHR request and store them in var
	$message = '
    <div style="width:100%;max-width:240px;border-radius:10px;overflow:hidden;position:relative;">
        <iframe
            src="https://www.google.com/maps?q='.$latitude.','.$longitude.'&z=15&output=embed"
            width="100%"
            height="140"
            style="border:0;display:block;"
            allowfullscreen=""
            loading="lazy">
        </iframe>
        <a href="https://www.google.com/maps?q='.$latitude.','.$longitude.'" 
           target="_blank" rel="noopener" 
           style="display:block;text-align:center;padding:6px;background:rgba(0,0,0,0.6);color:#fff;font-size:12px;font-weight:600;text-decoration:none;">
           📍 Abrir no Google Maps
        </a>
    </div>';
	$to_id = $_POST['to_id'];

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