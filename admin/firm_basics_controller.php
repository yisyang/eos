<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(!isset($_POST['firm_id'])){
	echo "{'success' : 0, 'msg' : 'Firm ID missing.'}";
	exit();
}
$firm_id = filter_var($_POST['firm_id'], FILTER_SANITIZE_NUMBER_INT);

function handleCallBack($db_success){
	if($db_success){
		echo '{"success" : 1}';
		exit();
	}else{
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}
}

if($action == 'add_cash'){
	$cash = filter_var($_POST['cash'], FILTER_SANITIZE_NUMBER_INT);
	if($cash > 0){
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($firm_id, 0, $cash, 'System', NOW())";
		$db->query($sql);
	}else{
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($firm_id, 1, '".(0-$cash)."', 'System', NOW())";
		$db->query($sql);
	}
	$sql = "UPDATE firms SET cash = cash + $cash WHERE id = $firm_id";
	handleCallBack($db->query($sql));
}
else if($action == 'set_cash'){
	$cash = filter_var($_POST['cash'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT cash FROM firms WHERE id = $firm_id";
	$old_cash = $db->query($sql)->fetchColumn();
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($firm_id, 1, $old_cash, 'System', NOW())";
	$db->query($sql);
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($firm_id, 0, $cash, 'System', NOW())";
	$db->query($sql);
	$sql = "UPDATE firms SET cash = $cash WHERE id = $firm_id";
	handleCallBack($db->query($sql));
}
else if($action == 'add_loan'){
	$loan = filter_var($_POST['loan'], FILTER_SANITIZE_NUMBER_INT);
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (?, ?, NOW())");
	$query->execute(array($firm_id, 'Your company has been granted a special $'.number_format($loan/100,2,'.',',').' loan by the Small Business Development and Relief Foundation.'));
	$sql = "UPDATE firms SET cash = cash + $loan, loan = loan + $loan WHERE id = $firm_id";
	handleCallBack($db->query($sql));
}
else if($action == 'set_loan'){
	$loan = filter_var($_POST['loan'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT loan FROM firms WHERE id = $firm_id";
	$old_loan = $db->query($sql)->fetchColumn();
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($firm_id, 0, $old_loan, 'System', NOW())";
	$db->query($sql);
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($firm_id, 1, $loan, 'System', NOW())";
	$db->query($sql);
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (?, ?, NOW())");
	$query->execute(array($firm_id, 'The banks consolidated your company\'s loans to a new total of $'.number_format($loan/100,2,'.',',')));
	$sql = "UPDATE firms SET loan = $loan WHERE id = $firm_id";
	handleCallBack($db->query($sql));
}
else if($action == 'add_influence'){
	$player_id = filter_var($_POST['player_id'], FILTER_SANITIZE_NUMBER_INT);
	$influence = filter_var($_POST['influence'], FILTER_SANITIZE_NUMBER_INT);
	$reason = filter_var($_POST['reason'], FILTER_SANITIZE_STRING);
	if(!$reason){
		$reason = "for your outstanding contributions to the development of Econosia.";
	}
	$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (?, ?, NOW())");
	$query->execute(array($player_id, 'You were awarded '.number_format($influence,0,'.',',').' influence '.$reason));
	$sql = "UPDATE players SET influence = influence + '$influence' WHERE id = $player_id";
	handleCallBack($db->query($sql));
}
else if($action == 'add_msg'){
	$msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (?, ?, NOW())");
	$result = $query->execute(array($firm_id, $msg));
	handleCallBack($result);
}
else if($action == 'add_building'){
	$b_type = filter_var($_POST['b_type'], FILTER_SANITIZE_STRING);
	$b_type_id = filter_var($_POST['b_type_id'], FILTER_SANITIZE_NUMBER_INT);
	$size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
	$slot = filter_var($_POST['slot'], FILTER_SANITIZE_NUMBER_INT);
	if($b_type == "fact"){
		$sql = "INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) SELECT $firm_id, $b_type_id, name, $size, $slot FROM list_fact WHERE id = $b_type_id";
	}
	if($b_type == "store"){
		$sql = "INSERT INTO firm_store (fid, store_id, store_name, size, slot) SELECT $firm_id, $b_type_id, name, $size, $slot FROM list_store WHERE id = $b_type_id";
	}
	if($b_type == "rnd"){
		$sql = "INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) SELECT $firm_id, $b_type_id, name, $size, $slot FROM list_rnd WHERE id = $b_type_id";
	}
	handleCallBack($db->query($sql));
}
else if($action == 'delete_building'){
	$b_type = filter_var($_POST['b_type'], FILTER_SANITIZE_STRING);
	$b_id = filter_var($_POST['b_id'], FILTER_SANITIZE_NUMBER_INT);
	if($b_type == "fact"){
		$sql = "DELETE FROM queue_prod WHERE ffid = $b_id";
		$db->query($sql);
		$sql = "DELETE FROM firm_fact WHERE id = $b_id";
	}
	if($b_type == "store"){
		$sql = "DELETE FROM firm_store WHERE id = $b_id";
	}
	if($b_type == "rnd"){
		$sql = "DELETE FROM queue_res WHERE frid = $b_id";
		$db->query($sql);
		$sql = "DELETE FROM firm_rnd WHERE id = $b_id";
	}
	handleCallBack($db->query($sql));
}
else if($action == 'add_building_size'){
	$b_type = filter_var($_POST['b_type'], FILTER_SANITIZE_STRING);
	$b_id = filter_var($_POST['b_id'], FILTER_SANITIZE_NUMBER_INT);
	$size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
	if($b_type == "fact"){
		$sql = "UPDATE firm_fact SET size = size + $size WHERE id = $b_id";
	}
	if($b_type == "store"){
		$sql = "UPDATE firm_store SET size = size + $size WHERE id = $b_id";
	}
	if($b_type == "rnd"){
		$sql = "UPDATE firm_rnd SET size = size + $size WHERE id = $b_id";
	}
	handleCallBack($db->query($sql));
}
else if($action == 'set_building_size'){
	$b_type = filter_var($_POST['b_type'], FILTER_SANITIZE_STRING);
	$b_id = filter_var($_POST['b_id'], FILTER_SANITIZE_NUMBER_INT);
	$size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
	if($b_type == "fact"){
		$sql = "UPDATE firm_fact SET size = $size WHERE id = $b_id";
	}
	if($b_type == "store"){
		$sql = "UPDATE firm_store SET size = $size WHERE id = $b_id";
	}
	if($b_type == "rnd"){
		$sql = "UPDATE firm_rnd SET size = $size WHERE id = $b_id";
	}
	handleCallBack($db->query($sql));
}
else if($action == 'delete_building_queue'){
	$b_type = filter_var($_POST['b_type'], FILTER_SANITIZE_STRING);
	$queue_id = filter_var($_POST['queue_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT building_type, building_id FROM queue_build WHERE id = $queue_id";
	$queue = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($queue)){
		echo "{'success' : 0, 'msg' : 'Building queue does not exist.'}";
		exit();
	}
	$b_type = $queue['building_type'];
	$b_id = $queue['building_id'];
	if($b_type == "store" && $b_id){
		$sql = "UPDATE firm_store SET is_expanding = 0 WHERE id = $b_id";
		$db->query($sql);
	}
	$sql = "DELETE FROM queue_build WHERE id = $queue_id";
	handleCallBack($db->query($sql));
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>