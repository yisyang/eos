<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

$player_name = $_GET['player_name'];

if($player_name){
	$query = $db->prepare("SELECT * FROM players WHERE player_name LIKE :player_name");
	$query->execute(array(':player_name' => '%'.$player_name.'%'));
	$players = $query->fetchAll(PDO::FETCH_ASSOC);
	$count = count($players);

	if($count>0){
		$msg = '<select id="player_lookup_select" onChange="player_lookup_show_edit();">';
		$msg .= '<option value="">- Select Player -</option>';
		foreach($players as $player){
			$msg .= '<option value="'.$player["id"].'">';
			$msg .= $player["player_name"];
			$msg .= '</option>';
		}
		$msg .= '</select>';
		echo $msg;
		exit();
	}else{
		echo 'Player Not Found';
	}
}else{
	echo 'Missing Input';
}
?>