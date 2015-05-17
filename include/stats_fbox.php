<?php
if($eos_firm_id){
	$query = $db->prepare("SELECT name, cash, loan FROM firms WHERE id = ?");
	$query->execute(array($eos_firm_id));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($result)){
		echo "Fatal Error: Error code on line 7 of stats_fbox. Firm not found.";
		exit();
	}else{
		//Fetch Firm Stats
		$firm_name = $result["name"];
		$firm_cash = $result["cash"];
		$firm_loan = $result["loan"];

		$_SESSION['firm_name'] = $firm_name;
		$_SESSION['firm_cash'] = $firm_cash;
		$_SESSION['firm_loan'] = $firm_loan;
	}
}
$query = $db->prepare("SELECT player_name, player_level, player_cash FROM players WHERE id = ?");
$query->execute(array($eos_player_id));
$result = $query->fetch(PDO::FETCH_ASSOC);
if(empty($result)){
	echo "Fatal Error: Error code on line 24 of stats_fbox. Player not found.";
	exit();
}else{
	$player_name = $result["player_name"];
	$player_level = $result["player_level"];
	$player_cash = $result["player_cash"];

	$_SESSION['player_name'] = $player_name;
	$_SESSION['player_cash'] = $player_cash;
}
?>
		<div id="eos_body_fbox">