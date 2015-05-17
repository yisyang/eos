<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'show_table'){
	$view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = 50;
	
	$offset = ($page_num - 1) * $per_page;

	switch($view_type){
		case 'received':
			$query_count = $db->prepare("SELECT COUNT(*) FROM messages WHERE recipient = :eos_player_id AND sender != :eos_player_id AND !recipient_delete");
			$query_results = $db->prepare("SELECT messages.id, messages.subject, messages.recipient_read, messages.recipient_starred, messages.no_delete, IFNULL(players.id, 0) AS sender_id, IFNULL(players.player_name, '<i>Not Found</i>') AS sender_name FROM messages LEFT JOIN players ON messages.sender = players.id WHERE messages.recipient = :eos_player_id AND messages.sender != :eos_player_id AND !messages.recipient_delete ORDER BY messages.id DESC LIMIT $offset, $per_page");
			$query_params = array(':eos_player_id' => $eos_player_id);
			break;
		case 'sent':
			$query_count = $db->prepare("SELECT COUNT(*) FROM messages WHERE sender = :eos_player_id AND recipient != :eos_player_id AND !sender_delete");
			$query_results = $db->prepare("SELECT messages.id, messages.subject, messages.recipient_read, messages.recipient_starred, messages.no_delete, IFNULL(players.id, 0) AS recipient_id, IFNULL(players.player_name, '<i>Not Found</i>') AS recipient_name FROM messages LEFT JOIN players ON messages.recipient = players.id WHERE messages.sender = :eos_player_id AND messages.recipient != :eos_player_id AND !messages.sender_delete ORDER BY messages.id DESC LIMIT $offset, $per_page");
			$query_params = array(':eos_player_id' => $eos_player_id);
			break;
		case 'notes':
			$query_count = $db->prepare("SELECT COUNT(*) FROM messages WHERE recipient = :eos_player_id AND sender = :eos_player_id AND !recipient_delete");
			$query_results = $db->prepare("SELECT messages.id, messages.subject, messages.recipient_read, messages.recipient_starred, messages.no_delete FROM messages WHERE recipient = :eos_player_id AND sender = :eos_player_id AND !recipient_delete ORDER BY id DESC LIMIT $offset, $per_page");
			$query_params = array(':eos_player_id' => $eos_player_id);
			break;
		default:
			$resp = array('success' => 0, 'msg' => 'Unknown view type.');
			echo json_encode($resp);
			exit();
			break;
	}
	$query_count->execute($query_params);
	$total_items = intval($query_count->fetchColumn());
	$pages_total = ceil($total_items/$per_page);

	$query_results->execute($query_params);
	$msg_results = $query_results->fetchAll(PDO::FETCH_ASSOC);

	$resp = array('success' => 1, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => $total_items, 'results' => $msg_results);
	echo json_encode($resp);
	exit();
}
else if($action == 'show_contacts'){
	$view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = 50;
	
	$offset = ($page_num - 1) * $per_page;

	switch($view_type){
		case 'all':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM player_contacts WHERE player_contacts.u_pid = :eos_player_id");
			$query_results = $db->prepare("SELECT player_contacts.u_notes, players.id AS player_id, players.player_name, players.last_active, firms.id, firms.name FROM player_contacts LEFT JOIN players ON player_contacts.c_pid = players.id LEFT JOIN firms ON players.fid = firms.id WHERE player_contacts.u_pid = :eos_player_id ORDER BY players.player_name LIMIT $offset, $per_page");
			$query_params = array(':eos_player_id' => $eos_player_id);
			break;
		case 'online':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM player_contacts LEFT JOIN players ON player_contacts.c_pid = players.id WHERE player_contacts.u_pid = :eos_player_id AND players.last_active > DATE_ADD(NOW(), INTERVAL -900 SECOND)");
			$query_results = $db->prepare("SELECT player_contacts.u_notes, players.id AS player_id, players.player_name, players.last_active, firms.id, firms.name FROM player_contacts LEFT JOIN players ON player_contacts.c_pid = players.id LEFT JOIN firms ON players.fid = firms.id WHERE player_contacts.u_pid = :eos_player_id AND players.last_active > DATE_ADD(NOW(), INTERVAL -900 SECOND) ORDER BY players.player_name LIMIT $offset, $per_page");
			$query_params = array(':eos_player_id' => $eos_player_id);
			break;
		default:
			$resp = array('success' => 0, 'msg' => 'Unknown view type.');
			echo json_encode($resp);
			exit();
			break;
	}
	$query_count->execute($query_params);
	$total_items = intval($query_count->fetchColumn());
	$pages_total = ceil($total_items/$per_page);

	$query_results->execute($query_params);
	$contact_results = $query_results->fetchAll(PDO::FETCH_ASSOC);

	$resp = array('success' => 1, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => $total_items, 'results' => $contact_results);
	echo json_encode($resp);
	exit();
}
else if($action == 'update_contact'){
	$contact_name = filter_var($_POST['contact_name'], FILTER_SANITIZE_STRING);
	$contact_desc = filter_var($_POST['contact_desc'], FILTER_SANITIZE_STRING);

	if($contact_name == ""){
		$resp = array('success' => 0, 'msg' => 'Contact name is missing.');
		echo json_encode($resp);
		exit();
	}

	$query = $db->prepare("SELECT id FROM players WHERE player_name = :player_name");
	$query->execute(array(':player_name' => $contact_name));
	$contact = $query->fetch(PDO::FETCH_ASSOC);
	
	if(empty($contact)){
		$resp = array('success' => 0, 'msg' => 'Action failed. Player is not be found.');
		echo json_encode($resp);
		exit();
	}
	$contact_id = $contact['id'];
	
	$sql = "SELECT COUNT(*) AS cnt FROM player_contacts WHERE u_pid = $eos_player_id AND c_pid = $contact_id";
	$contact_exists = $db->query($sql)->fetchColumn();
	
	if($contact_exists){
		$query = $db->prepare("UPDATE player_contacts SET u_notes = :notes WHERE u_pid = $eos_player_id AND c_pid = $contact_id");
		$query->execute(array(':notes' => $contact_desc));
		$resp = array('success' => 1, 'contactIsNew' => 0, 'contactId' => $contact_id);
		echo json_encode($resp);
		exit();
	}else{
		$query = $db->prepare("INSERT INTO player_contacts (u_pid, c_pid, u_notes) VALUES ($eos_player_id, $contact_id, :notes)");
		$query->execute(array(':notes' => $contact_desc));
		$resp = array('success' => 1, 'contactIsNew' => 1, 'contactId' => $contact_id);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'delete_contact'){
	$contact_id = filter_var($_POST['contact_id'], FILTER_SANITIZE_NUMBER_INT);

	$query = $db->prepare("DELETE FROM player_contacts WHERE u_pid = $eos_player_id AND c_pid = :contact_id");
	$query->execute(array(':contact_id' => $contact_id));
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'find_players'){
	$search_name = filter_var($_POST['search_name'], FILTER_SANITIZE_STRING);
	if($search_name == ''){
		$resp = array('success' => 0, 'msg' => 'Please enter a player name or company name.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT players.id, players.player_name, IFNULL(firms.name, 'N/A') AS firm_name, IFNULL(firms_extended.id, 0) AS fid FROM players LEFT JOIN firms_extended ON players.id = firms_extended.ceo LEFT JOIN firms ON firms_extended.id = firms.id WHERE players.is_searchable AND (players.player_name LIKE :search_name OR firms.name LIKE :search_name) ORDER BY players.player_name ASC LIMIT 0, 50";
	$query = $db->prepare($sql);
	$query->execute(array(':search_name' => $search_name.'%'));
	$players = $query->fetchAll(PDO::FETCH_ASSOC);

	$resp = array('success' => 1, 'results' => $players);
	echo json_encode($resp);
	exit();
}
else if($action == 'send_message'){
	$message_recipient = filter_var($_POST['message_recipient'], FILTER_SANITIZE_STRING);
	$message_subject = $_POST['message_subject'];
	$message_body = $_POST['message_body'];

	if($message_recipient == ''){
		$resp = array('success' => 0, 'msg' => 'Please enter the player name to whom you are sending this message.');
		echo json_encode($resp);
		exit();
	}
	if($message_subject == ''){
		$resp = array('success' => 0, 'msg' => 'Please enter a message subject.');
		echo json_encode($resp);
		exit();
	}
	if(strlen($message_body) > 5000){
		$resp = array('success' => 0, 'msg' => 'Please keep your message under 5000 characters.');
		echo json_encode($resp);
		exit();
	}

	// If recipient is not found in DB
	$sql = "SELECT id FROM players WHERE player_name = '$message_recipient'";
	$recipient_id = $db->query($sql)->fetchColumn();
	if(!$recipient_id){
		$resp = array('success' => 0, 'msg' => 'The player you are sending the message to does not exist or had a name change.');
		echo json_encode($resp);
		exit();
	}

	// Check Message Limit
	$sql = "SELECT player_level FROM players WHERE id = $eos_player_id";
	$player_level = $db->query($sql)->fetchColumn();

	$sql = "SELECT COUNT(*) AS cnt FROM messages WHERE sender = $eos_player_id AND recipient != $eos_player_id AND sendtime > DATE_ADD(NOW(), INTERVAL -15 MINUTE)";
	$recent_messages_count = $db->query($sql)->fetchColumn();
	$recent_messages_limit = max(3,(5 * $player_level - 10));

	if($recent_messages_count > $recent_messages_limit){
		// Limit reached: Retrieve answer, check answer against db, clear db answer
		$bot_check_answer_posted = filter_var($_POST['bot_check_answer'], FILTER_SANITIZE_NUMBER_INT);
		if($bot_check_answer_posted === ''){
			$resp = array('success' => 0, 'msg' => 'Please enter an answer for the human check.');
			echo json_encode($resp);
			exit();
		}
		$sql = "SELECT bot_check FROM players_extended WHERE id = $eos_player_id";
		$bot_check_answer = intval($db->query($sql)->fetchColumn());
		if($bot_check_answer_posted === $bot_check_answer){
			$sql = "UPDATE players_extended SET bot_check = NULL WHERE id = $eos_player_id";
			$db->query($sql);
		}else{
			$sql = "UPDATE players_extended SET bot_flag = bot_flag + 1 WHERE id = $eos_player_id";
			$db->query($sql);
			$resp = array('success' => 0, 'msg' => 'Wrong answer provided for human check. Please read the question carefully and try again.');
			echo json_encode($resp);
			exit();
		}
	}

	// "Send" the message
	$query = $db->prepare("INSERT INTO messages (sender, recipient, subject, body, sendtime) VALUES ($eos_player_id, '$recipient_id', :message_subject, :message_body, NOW())");
	$query->execute(array(':message_subject' => $message_subject, ':message_body' => $message_body));
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'delete_message'){
	$message_id = filter_var($_POST['message_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$message_id){
		$resp = array('success' => 0, 'msg' => 'Message ID is missing.');
		echo json_encode($resp);
		exit();
	}

	// Discover messages
	$sql = "SELECT COUNT(*) AS cnt FROM messages WHERE id = '$message_id' AND (sender = $eos_player_id OR recipient = $eos_player_id)";
	$count = $db->query($sql)->fetchColumn();
	if(!$count){
		$resp = array('success' => 0, 'msg' => 'Message not found.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "UPDATE messages SET recipient_read = 1, recipient_delete = 1 WHERE id = '$message_id' AND recipient = $eos_player_id AND !no_delete";
	$db->query($sql);
	
	$sql = "UPDATE messages SET sender_delete = 1 WHERE id = '$message_id' AND sender = $eos_player_id AND !no_delete";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'delete_all_messages'){
	$message_ids = filter_var_array($_POST['message_ids'], FILTER_SANITIZE_NUMBER_INT);
	if(empty($message_ids)){
		$resp = array('success' => 0, 'msg' => 'Message deletion failed. No message selected.');
		echo json_encode($resp);
		exit();
	}
	$in_cond = implode(',', $message_ids);

	$sql = "UPDATE messages SET recipient_read = 1, recipient_delete = 1 WHERE id IN ($in_cond) AND recipient = $eos_player_id AND !no_delete";
	$db->query($sql);

	$sql = "UPDATE messages SET sender_delete = 1 WHERE id IN ($in_cond) AND sender = $eos_player_id AND !no_delete";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'toggle_importance'){
	$message_id = filter_var($_POST['message_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$message_id){
		$resp = array('success' => 0, 'msg' => 'Message ID is missing.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT recipient_starred FROM messages WHERE id = '$message_id' AND recipient = $eos_player_id";
	$msg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($msg)){
		$resp = array('success' => 0, 'msg' => 'Message cannot be found.');
		echo json_encode($resp);
		exit();
	}

	$starred = 1 - $msg['recipient_starred'];
	$sql = "UPDATE messages SET recipient_starred = $starred WHERE id = '$message_id'";
	$db->query($sql);

	$resp = array('success' => 1, 'important' => $starred);
	echo json_encode($resp);
	exit();
}
?>