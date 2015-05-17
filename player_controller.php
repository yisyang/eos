<?php require 'include/prehtml.php'; ?>
<?php require 'include/stock_control.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'get_cash'){
	$query = $db->prepare("SELECT players.player_name, players.player_cash FROM players WHERE players.id = ?");
	$query->execute(array($eos_player_id));
	$player = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($player)){
		$resp = array('success' => 0, 'msg' => 'Player not found.');
		echo json_encode($resp);
		exit();
	}
	$_SESSION['player_cash'] = $player['player_cash'];
	
	$resp = array('success' => 1, 'cash' => $player['player_cash']);
	echo json_encode($resp);
	exit();
}
else if($action == 'get_all'){
	// $query = $db->prepare("SELECT firms.name, firms.networth, firms.cash, firms.loan, firms.level, firms.fame_level, firms.fame_exp, firms.vacation_out FROM firms WHERE firms.id = ?");
	// $query->execute(array($eos_firm_id));
	// $firm = $query->fetch(PDO::FETCH_ASSOC);
	// if(empty($firm)){
		// $resp = array('success' => 0, 'msg' => 'Firm not found.');
		// echo json_encode($resp);
		// exit();
	// }

	// $_SESSION['firm_name'] = $firm['name'];
	// $_SESSION['firm_cash'] = $firm['cash'];
	// $_SESSION['firm_loan'] = $firm['loan'];
	
	// $resp = array('success' => 1, 'name' => $firm['name'], 'networth' => $firm['networth'], 'cash' => $firm['cash'], 'loan' => $firm['loan'], 'level' => $firm['level'], 'fame_level' => $firm['fame_level'], 'fame_exp' => $firm['fame_exp']);
	// echo json_encode($resp);
	// exit();
}
?>