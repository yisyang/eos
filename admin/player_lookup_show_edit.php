<?php
require 'include/sess_auth.php';
$player_name = $_GET['player_name'];
$_SESSION['editing_player_name'] = $player_name;
$player_id = $_GET['player_id'];
$_SESSION['editing_player_id'] = $player_id;

if($player_id){
	echo '<b>Edit '.$player_name.'</b><br />';
	echo '(ID: '.$player_id.')<br />';
	echo '<a href="player_basics.php">Basics</a><br />';
}else{
	echo 'Player not found.';
}
?>