<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(!isset($_POST['player_id'])){
	echo "{'success' : 0, 'msg' : 'Player ID missing.'}";
	exit();
}
$player_id = filter_var($_POST['player_id'], FILTER_SANITIZE_NUMBER_INT);

function handleCallBack($db_success){
	if($db_success){
		echo '{"success" : 1}';
		exit();
	}else{
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}
}

if($action == 'add_achievement'){
	$achievement_id = filter_var($_POST['achievement_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT COUNT(*) FROM player_achievements WHERE pid = $player_id AND aid = $achievement_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "The player already has this achievement!"}';
		exit();
	}
	$sql = "INSERT INTO player_achievements (pid, aid, awarded) VALUES ($player_id, $achievement_id, NOW())";
	handleCallBack($db->query($sql));
}
else if($action == 'remove_achievement'){
	$achievement_id = filter_var($_POST['achievement_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT COUNT(*) FROM player_achievements WHERE pid = $player_id AND aid = $achievement_id";
	$count = $db->query($sql)->fetchColumn();
	if(!$count){
		echo '{"success" : 0, "msg" : "The player does not have this achievement!"}';
		exit();
	}
	$sql = "DELETE FROM player_achievements WHERE pid = $player_id AND aid = $achievement_id";
	handleCallBack($db->query($sql));
}
else if($action == 'add_cash'){
	$cash = filter_var($_POST['cash'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "UPDATE players SET player_cash = player_cash + $cash WHERE id = $player_id";
	handleCallBack($db->query($sql));
}
else if($action == 'set_cash'){
	$cash = filter_var($_POST['cash'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "UPDATE players SET player_cash = $cash WHERE id = $player_id";
	handleCallBack($db->query($sql));
}
else if($action == 'add_influence'){
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
else if($action == 'set_jail_time'){
	$in_jail = filter_var($_POST['in_jail'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "UPDATE players SET in_jail = '$in_jail' WHERE id = $player_id";
	handleCallBack($db->query($sql));
}
else if($action == 'update_vip'){
	$player_vip_new = filter_var($_POST['player_vip_new'], FILTER_SANITIZE_NUMBER_INT);
	$player_vip_expiration_new = filter_var($_POST['player_vip_expiration_new'], FILTER_SANITIZE_STRING);
	$query = $db->prepare("UPDATE players SET vip_level = ?, vip_expires = ? WHERE id = ?");
	$result = $query->execute(array($player_vip_new, $player_vip_expiration_new, $player_id));
	handleCallBack($result);
}
else if($action == 'add_msg'){
	$msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);
	$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (?, ?, NOW())");
	$result = $query->execute(array($player_id, $msg));
	handleCallBack($result);
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>