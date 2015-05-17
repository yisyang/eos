<?php require 'include/prehtml_no_auth.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'get_msgs'){
	$conn_id = filter_var($_POST['conn_id'], FILTER_SANITIZE_NUMBER_INT);
	$channel_id = 0 +filter_var($_POST['chan_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!isset($_SESSION['chat_conn_id']) || !isset($_SESSION['eos_player_id']) || !$conn_id || $conn_id !== $_SESSION['chat_conn_id']){
		$resp = array('success' => 0, 'msg' => 'Chat disconnected.', 'disconnect' => 1);
		echo json_encode($resp);
		exit();
	}
	$eos_player_id = filter_var($_SESSION['eos_player_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT COUNT(*) AS cnt FROM chat_connections WHERE id = '$conn_id' AND pid = '$eos_player_id'";
	$count = $db->query($sql)->fetchColumn();
	if(!$count){
		$resp = array('success' => 0, 'msg' => 'Chat disconnected.', 'disconnect' => 1);
		echo json_encode($resp);
		exit();
	}
	$sql = "UPDATE chat_connections SET last_active = NOW() WHERE id = '$conn_id'";
	$db->query($sql);

	$curr_time = time();
	$start_time = max($curr_time - 1800, filter_var($_POST['st'], FILTER_SANITIZE_NUMBER_INT) - 8);
	$fst = date("Y-m-d H:i:s", $start_time);
	$sql = "SELECT chat.id, chat.sender AS player_id, players.player_name, chat.body FROM chat LEFT JOIN players ON chat.sender = players.id WHERE chat.channel_id = '$channel_id' AND chat.sendtime > '$fst' ORDER BY chat.id ASC";
	$msgs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$resp = array('success' => 1, 'curr_time' => $curr_time, 'msgs' => $msgs);
	echo json_encode($resp);
	exit();
}
else if($action == 'send_msg'){
	$conn_id = filter_var($_POST['conn_id'], FILTER_SANITIZE_NUMBER_INT);
	$channel_id = 0 +filter_var($_POST['chan_id'], FILTER_SANITIZE_NUMBER_INT);
	$msg_body = trim(filter_var($_POST['msg_body'], FILTER_SANITIZE_STRING));
	if($msg_body == ''){
		$resp = array('success' => 0, 'msg' => 'Cannot send empty message.');
		echo json_encode($resp);
		exit();
	}
	if(!isset($_SESSION['chat_conn_id']) || !isset($_SESSION['eos_player_id']) || !$conn_id || $conn_id !== $_SESSION['chat_conn_id']){
		$resp = array('success' => 0, 'msg' => 'Chat disconnected.', 'disconnect' => 1);
		echo json_encode($resp);
		exit();
	}
	$eos_player_id = filter_var($_SESSION['eos_player_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT COUNT(*) AS cnt FROM chat_connections LEFT JOIN players_extended ON chat_connections.pid = players_extended.id WHERE chat_connections.id = '$conn_id' AND chat_connections.pid = '$eos_player_id' AND !players_extended.muted";
	$count = $db->query($sql)->fetchColumn();
	if(!$count){
		$resp = array('success' => 0, 'msg' => 'Chat disconnected or player is muted.');
		echo json_encode($resp);
		exit();
	}
	$sql = "UPDATE chat_connections SET last_active = NOW() WHERE id = '$conn_id'";
	$db->query($sql);

	$system_msg = '';
	$flagged = 0;
	// Check count recent messages
	$sql = "SELECT COUNT(*) AS cnt FROM chat WHERE sender = '$eos_player_id' AND sendtime > DATE_ADD(NOW(), INTERVAL -1 MINUTE)";
	$count = $db->query($sql)->fetchColumn();
	if($count > 12){
		$resp = array('success' => 0, 'msg' => 'You are sending messages too quickly, please wait a minute.');
		echo json_encode($resp);
		exit();
	}

	// Compare most recent message (against spam)
	$sql = "SELECT body FROM chat WHERE sender = '$eos_player_id' AND chat.channel_id = '$channel_id' AND sendtime > DATE_ADD(NOW(), INTERVAL -1 MINUTE) ORDER BY id DESC LIMIT 0, 2";
	$messages = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(count($messages) > 1){
		if($messages[0]['body'] == $msg_body && $messages[1]['body'] == $msg_body){
			$system_msg = 'Please do not spam. Repeated offenses may get you jailed or banned.';
			$flagged = 1;
		}
	}

	// Validate against profanity
	$pattern = '/\b(anal|asshole|bitch|ballsack|blow job|blowjob|clit|cock|cum|cunt|dick|douchebag|faggot|felch|fuck|fucktard|gay|homo|nigger|penis|pussy|shit|slut|smegma|stfu|tit|twat|vagina)(s?)\b/i';
	if(preg_match($pattern, $msg_body)){
		$system_msg = 'Please remain civilized. Repeated use of profanity may get you jailed or banned.';
		$flagged = 1;
	}
	
	// Insert message
	$query = $db->prepare("INSERT INTO chat (sender, body, channel_id) VALUES (:pid, :body, :channel_id)");
	$query->execute(array(':pid' => $eos_player_id, ':body' => $msg_body, ':channel_id' => $channel_id));
	$message_id = $db->lastInsertId();

	if($system_msg == ''){
		$resp = array('success' => 1, 'msg_id' => $message_id);
	}else{
		$resp = array('success' => 1, 'msg_id' => $message_id, 'msg' => $system_msg);
	}
	echo json_encode($resp);
	exit();
}
else if($action == 'connect'){
	$login_confirmed = 0;
	if(isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in']){
		if(isset($_SESSION['eos_user_is_logged_in']) && $_SESSION['eos_user_is_logged_in']){
			$eos_player_id = filter_var($_SESSION['eos_player_id'], FILTER_SANITIZE_NUMBER_INT);
			$rk = filter_var($_SESSION['rk'], FILTER_SANITIZE_STRING);
			if($eos_player_id){
				$query = $db->prepare("SELECT COUNT(*) AS cnt, player_name FROM players WHERE id = ? AND rk = ?");
				$query->execute(array($eos_player_id, $rk));
				$login_data = $query->fetch(PDO::FETCH_ASSOC);
				$login_confirmed = $login_data['cnt'];
				$player_name = $login_data['player_name'];
			}
		}
	}
	if(!$login_confirmed){
		$resp = array('success' => 0, 'msg' => 'Player is not logged in.');
		echo json_encode($resp);
		exit();
	}
	$sql = "DELETE FROM chat_connections WHERE pid = '$eos_player_id'";
	$db->query($sql);
	$sql = "INSERT INTO chat_connections (pid) VALUES ('$eos_player_id')";
	$db->query($sql);

	$conn_id = $db->lastInsertId();
	$_SESSION['chat_conn_id'] = $conn_id;

	$resp = array('success' => 1, 'conn_id' => $conn_id, 'player_id' => $eos_player_id, 'player_name' => $player_name);
	echo json_encode($resp);
	exit();
}
else if($action == 'disconnect'){
	$eos_player_id = filter_var($_SESSION['eos_player_id'], FILTER_SANITIZE_NUMBER_INT);
	$conn_id = filter_var($_POST['conn_id'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "DELETE FROM chat_connections WHERE id = '$conn_id' AND pid = '$eos_player_id'";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'update_position'){
	$login_confirmed = 0;
	if(isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in']){
		if(isset($_SESSION['eos_user_is_logged_in']) && $_SESSION['eos_user_is_logged_in']){
			$eos_player_id = filter_var($_SESSION['eos_player_id'], FILTER_SANITIZE_NUMBER_INT);
			$rk = filter_var($_SESSION['rk'], FILTER_SANITIZE_STRING);
			if($eos_player_id){
				$query = $db->prepare("SELECT COUNT(*) FROM players WHERE id = ? AND rk = ?");
				$query->execute(array($eos_player_id, $rk));
				$login_confirmed = $query->fetchColumn();
			}
		}
	}
	if(!$login_confirmed){
		$resp = array('success' => 0, 'msg' => 'Player is not logged in.');
		echo json_encode($resp);
		exit();
	}
	$left = filter_var($_POST['left'], FILTER_SANITIZE_NUMBER_INT);
	$top = filter_var($_POST['top'], FILTER_SANITIZE_NUMBER_INT);
	$width = filter_var($_POST['width'], FILTER_SANITIZE_NUMBER_INT);
	$height = filter_var($_POST['height'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "UPDATE players_extended SET chatbox_x = '$left', chatbox_y = '$top', chatbox_width = '$width', chatbox_height = '$height' WHERE id = '$eos_player_id'";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
?>