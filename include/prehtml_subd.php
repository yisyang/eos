<?php
$show_page_gen_time = 0;
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
	$show_page_gen_time = 1;
}else{
	error_reporting(E_STRICT);
}
session_start();
date_default_timezone_set('America/Los_Angeles');
$timestart = microtime(1);

require_once '../scripts/db/dbconnrjeos.php';

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
	if(isset($_SESSION['from_fbc'])){
		$settings_narrow_screen = 1;
	}else{
		$settings_narrow_screen = 0;
	}
}else{
	$query = $db->prepare("SELECT players.fid, players.is_hidden, players.show_menu_tooltip, players.narrow_screen FROM players WHERE players.id = ?");
	$query->execute(array($eos_player_id));
	$player = $query->fetch(PDO::FETCH_ASSOC);

	$eos_firm_id = $player['fid'];
	$eos_player_is_hidden = $player['is_hidden'];
	$settings_show_menu_tooltip = $player['show_menu_tooltip'];
	$settings_narrow_screen = $player['narrow_screen'];
	if(!$eos_player_is_hidden){
		$query = $db->prepare("UPDATE players LEFT JOIN firms ON players.fid = firms.id SET players.last_active = NOW(), firms.last_active = NOW() WHERE players.id = ?");
		$query->execute(array($eos_player_id));
	}
	$query = $db->prepare("SELECT firms.name, firms.id AS fid FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.pid = ? ORDER BY firms.name ASC");
	$query->execute(array($eos_player_id));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	if($eos_firm_id == 0 && !empty($result)){
		$eos_firm_id = $result['fid'];
		$query = $db->prepare("UPDATE players SET fid = ? WHERE id = ?");
		$query->execute(array($eos_firm_id, $eos_player_id));
	}
}

function number_format_readable ($num, $digits = 3, $dec_sep = '.', $k_sep = ','){
	$num_abs = abs($num);
	if($num_abs < 10){
		if((int)$num_abs != $num_abs){
			if(strlen($num_abs) < 6 && $num_abs < 0.01){
				return $num;
			}else{
				return number_format($num, max(0,$digits-1), $dec_sep, $k_sep);	
			}
		}else{
			return number_format($num, max(0,$digits-1), $dec_sep, $k_sep);	
		}
	}
	if($num_abs < 1000){
		if((int)$num_abs == $num_abs){
			return (int)$num;
		}
		if($num_abs < 100){
			return number_format($num, max(0,$digits-2), $dec_sep, $k_sep);
		}
		return number_format($num, max(0,$digits-3), $dec_sep, $k_sep);
	}
	if($num_abs < 1000000){
		if($num_abs < 10000){
			return number_format(floor($num/10)/100, max(0,$digits-1), $dec_sep, $k_sep).' k';
		}
		if($num_abs < 100000){
			return number_format(floor($num/100)/10, max(0,$digits-2), $dec_sep, $k_sep).' k';
		}
		return number_format(floor($num/1000), max(0,$digits-3), $dec_sep, $k_sep).' k';
	}
	if($num_abs < 1000000000){
		if($num_abs < 10000000){
			return number_format(floor($num/10000)/100, max(0,$digits-1), $dec_sep, $k_sep).' M';
		}
		if($num_abs < 100000000){
			return number_format(floor($num/100000)/10, max(0,$digits-2), $dec_sep, $k_sep).' M';
		}
		return number_format(floor($num/1000000), max(0,$digits-3), $dec_sep, $k_sep).' M';
	}
	if($num_abs < 1000000000000){
		if($num_abs < 10000000000){
			return number_format(floor($num/10000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' B';
		}
		if($num_abs < 100000000000){
			return number_format(floor($num/100000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' B';
		}
		return number_format(floor($num/1000000000), max(0,$digits-3), $dec_sep, $k_sep).' B';
	}
	if($num_abs < 1000000000000000){
		if($num_abs < 10000000000000){
			return number_format(floor($num/10000000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' T';
		}
		if($num_abs < 100000000000000){
			return number_format(floor($num/100000000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' T';
		}
		return number_format(floor($num/1000000000000), max(0,$digits-3), $dec_sep, $k_sep).' T';
	}else{
		if($num_abs < 10000000000000000){
			return number_format(floor($num/10000000000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' Q';
		}
		if($num_abs < 100000000000000000){
			return number_format(floor($num/100000000000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' Q';
		}
		return number_format(floor($num/1000000000000000), max(0,$digits-3), $dec_sep, $k_sep).' Q';
	}
}
?>