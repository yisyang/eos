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

//redirect back from https
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
	$redirect= "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	header("Location: $redirect");
}

require_once 'scripts/db/dbconnrjeos.php';

$sql = "SELECT value FROM world_var WHERE name = 'maintenance_mode'";
$maintenance_mode = $db->query($sql)->fetchColumn();
if($maintenance_mode){
	echo 'Sorry, the game is currently under maintenance. Please try again later. Thank you for your patience.';
	exit();
}

function fbox_breakout($url, $redirect_msg = ''){
	if($redirect_msg){
		echo $redirect_msg . '<br />';
		echo 'Please wait, you are being redirected... or you may <a href="'.$url.'">click here</a>.';
		echo '
		<script type="text/javascript">
			setTimeout("window.top.location.href=\"'.$url.'\"", 2500);
		</script>';
	}else{
		echo '
		<script type="text/javascript">
			window.top.location.href="'.$url.'";
		</script>';
	}
	exit();
}
function fbox_redirect($url, $redirect_msg = '', $postParams = array()){
	if($redirect_msg){
		echo $redirect_msg . '<br />';
		if(empty($postParams)){
			echo 'Please wait, you are being redirected... or you may <a class="jqDialog" href="'.$url.'">click here</a>.';
			echo '
			<script type="text/javascript">
				setTimeout(function(){jqDialogInit("'.$url.'");}, 2500);
			</script>';
		}else{
			echo 'Please wait, you are being redirected... or you may <a class="jqDialog" href="'.$url.'" params="'.http_build_query($postParams).'">click here</a>.';
			echo '
			<script type="text/javascript">
				setTimeout(function(){jqDialogInit("'.$url.'", '.json_encode($postParams).');}, 2500);
			</script>';
		}
	}else{
		if(empty($postParams)){
			echo '
			<script type="text/javascript">
				jqDialogInit("'.$url.'");
			</script>';
		}else{
			echo '
			<script type="text/javascript">
				jqDialogInit("'.$url.'", '.json_encode($postParams).');
			</script>';
		}
	}
	exit();
}
function fbox_echoout($echo_msg = '', $back_link = '', $postParams = array()){
	echo '<div id="eos_body_fbox">';
	echo $echo_msg;
	echo '<br /><br />';
	if($back_link){
		if(empty($postParams)){
			echo '<a class="jqDialog" href="'.$back_link.'"><input type="button" class="bigger_input" value="Back" /></a> ';
		}else{
			echo '<a class="jqDialog" href="'.$back_link.'" params="'.http_build_query($postParams).'"><input type="button" class="bigger_input" value="Back" /></a> ';
		}
	}
	echo '<input type="button" class="bigger_input" value="Close" onclick="$(\'#jq-dialog-modal\').dialog(\'close\');" />';
	echo '</div>';
	exit();
}

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
		if(!$login_confirmed){
			$_SESSION['eos_user_is_logged_in'] = false;
			fbox_breakout('/eos/login-auth.php');
		}
	}else{
		fbox_breakout('/eos/login-auth.php');
	}
}
if(!$login_confirmed){
	fbox_breakout('/index.php', 'User is not logged in.');
}else{
	$query = $db->prepare("SELECT players.player_name, players.player_alias, players.fid, players.in_jail, players.is_hidden, players.new_user, players.show_menu_tooltip, players.narrow_screen, players.queue_countdown, players.b2b_rows_per_page, players.enable_chat FROM players WHERE players.id = ?");
	$query->execute(array($eos_player_id));
	$player = $query->fetch(PDO::FETCH_ASSOC);

	$eos_firm_id = $player['fid'];
	$eos_player_name = $player['player_name'];
	$eos_player_alias = $player['player_alias'];
	$eos_player_is_new_user = $player['new_user'];
	$eos_player_is_in_jail = $player['in_jail'];
	$eos_player_is_hidden = $player['is_hidden'];
	$settings_show_menu_tooltip = $player['show_menu_tooltip'];
	$settings_narrow_screen = $player['narrow_screen'];
	$settings_queue_countdown = $player['queue_countdown'];
	$settings_b2b_rows_per_page = $player['b2b_rows_per_page'];
	$settings_enable_chat = false;
	if(!$eos_player_is_hidden){
		$query = $db->prepare("UPDATE players LEFT JOIN firms ON players.fid = firms.id SET players.last_active = NOW(), players.requests = players.requests + 1, firms.last_active = GREATEST(NOW(),firms.vacation_out) WHERE players.id = ?");
		$query->execute(array($eos_player_id));
	}
	if($eos_player_is_in_jail){
		if($eos_player_is_in_jail > time()){
			fbox_breakout('/eos/players/jail.php');
		}else{
			$query = $db->prepare("UPDATE players SET in_jail = 0 WHERE id = ?");
			$query->execute(array($eos_player_id));
		}
	}
	$query = $db->prepare("SELECT firms.name, firms.id AS fid FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.pid = ? ORDER BY firms.name ASC");
	$query->execute(array($eos_player_id));
	$EOS_PLAYER_FIRMS = $query->fetchAll(PDO::FETCH_ASSOC);
	if($eos_firm_id == 0 && count($EOS_PLAYER_FIRMS)){
		$eos_firm_id = $EOS_PLAYER_FIRMS[0]['fid'];
		$query = $db->prepare("UPDATE players SET fid = ? WHERE id = ?");
		$query->execute(array($eos_firm_id, $eos_player_id));
	}
}
if($eos_player_is_new_user && !defined('NEW_PLAYER_ALLOWED')){
	fbox_breakout('/eos/tutorial.php');
}
if($eos_firm_id){
	$query = $db->prepare("SELECT daily_allowance, used_allowance, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM firms_positions WHERE fid = ? AND pid = ?");
	$query->execute(array($eos_firm_id, $eos_player_id));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($result)){
		$query = $db->prepare("UPDATE players SET fid = 0 WHERE id = ?");
		$query->execute(array($eos_player_id));
		fbox_breakout('/eos/index.php');
	}
	$ctrl_daily_allowance = $result['daily_allowance'];
	if($ctrl_daily_allowance == -1){
		$ctrl_leftover_allowance = INF;
	}else{
		$ctrl_leftover_allowance = $ctrl_daily_allowance - $result['used_allowance'];
	}
	$ctrl_admin = $result['ctrl_admin'];
	$ctrl_bldg_hurry = $result['ctrl_bldg_hurry'];
	$ctrl_bldg_land = $result['ctrl_bldg_land'];
	$ctrl_bldg_view = $result['ctrl_bldg_view'];
	$ctrl_fact_produce = $result['ctrl_fact_produce'];
	$ctrl_fact_cancel = $result['ctrl_fact_cancel'];
	$ctrl_fact_build = $result['ctrl_fact_build'];
	$ctrl_fact_expand = $result['ctrl_fact_expand'];
	$ctrl_fact_sell = $result['ctrl_fact_sell'];
	$ctrl_store_price = $result['ctrl_store_price'];
	$ctrl_store_ad = $result['ctrl_store_ad'];
	$ctrl_store_build = $result['ctrl_store_build'];
	$ctrl_store_expand = $result['ctrl_store_expand'];
	$ctrl_store_sell = $result['ctrl_store_sell'];
	$ctrl_rnd_res = $result['ctrl_rnd_res'];
	$ctrl_rnd_cancel = $result['ctrl_rnd_cancel'];
	$ctrl_rnd_hurry = $result['ctrl_rnd_hurry'];
	$ctrl_rnd_build = $result['ctrl_rnd_build'];
	$ctrl_rnd_expand = $result['ctrl_rnd_expand'];
	$ctrl_rnd_sell = $result['ctrl_rnd_sell'];
	$ctrl_wh_view = $result['ctrl_wh_view'];
	$ctrl_wh_sell = $result['ctrl_wh_sell'];
	$ctrl_wh_discard = $result['ctrl_wh_discard'];
	$ctrl_b2b_buy = $result['ctrl_b2b_buy'];
	$ctrl_hr_post = $result['ctrl_hr_post'];
	$ctrl_hr_hire = $result['ctrl_hr_hire'];
	$ctrl_hr_fire = $result['ctrl_hr_fire'];
}
function require_active_firm(){
	global $eos_firm_id;
	if(!$eos_firm_id){
		fbox_breakout('/eos/index.php');
	}
}
function number_format_readable ($num, $digits = 3, $dec_sep = '.', $k_sep = ','){
	$num_abs = abs($num);
	if($num_abs < 10){
		if((int)$num_abs != $num_abs){
			if(strlen($num_abs) < 6 && $num_abs < 0.01){
				return $num;
			}else{
				return number_format($num, max(0, $digits-1), $dec_sep, $k_sep);	
			}
		}else{
			return number_format($num, max(0, $digits-1), $dec_sep, $k_sep);	
		}
	}
	if($num_abs < 1000){
		if((int)$num_abs == $num_abs){
			return (int)$num;
		}
		if($num_abs < 100){
			return number_format($num, max(0, $digits-2), $dec_sep, $k_sep);
		}
		return number_format($num, max(0, $digits-3), $dec_sep, $k_sep);
	}
	if($num_abs < 1000000){
		if($num_abs < 10000){
			return number_format(floor($num/10)/100, max(0, $digits-1), $dec_sep, $k_sep).' k';
		}
		if($num_abs < 100000){
			return number_format(floor($num/100)/10, max(0, $digits-2), $dec_sep, $k_sep).' k';
		}
		return number_format(floor($num/1000), max(0, $digits-3), $dec_sep, $k_sep).' k';
	}
	if($num_abs < 1000000000){
		if($num_abs < 10000000){
			return number_format(floor($num/10000)/100, max(0, $digits-1), $dec_sep, $k_sep).' M';
		}
		if($num_abs < 100000000){
			return number_format(floor($num/100000)/10, max(0, $digits-2), $dec_sep, $k_sep).' M';
		}
		return number_format(floor($num/1000000), max(0, $digits-3), $dec_sep, $k_sep).' M';
	}
	if($num_abs < 1000000000000){
		if($num_abs < 10000000000){
			return number_format(floor($num/10000000)/100, max(0, $digits-1), $dec_sep, $k_sep).' B';
		}
		if($num_abs < 100000000000){
			return number_format(floor($num/100000000)/10, max(0, $digits-2), $dec_sep, $k_sep).' B';
		}
		return number_format(floor($num/1000000000), max(0, $digits-3), $dec_sep, $k_sep).' B';
	}
	if($num_abs < 1000000000000000){
		if($num_abs < 10000000000000){
			return number_format(floor($num/10000000000)/100, max(0, $digits-1), $dec_sep, $k_sep).' T';
		}
		if($num_abs < 100000000000000){
			return number_format(floor($num/100000000000)/10, max(0, $digits-2), $dec_sep, $k_sep).' T';
		}
		return number_format(floor($num/1000000000000), max(0, $digits-3), $dec_sep, $k_sep).' T';
	}else{
		if($num_abs < 10000000000000000){
			return number_format(floor($num/10000000000000)/100, max(0, $digits-1), $dec_sep, $k_sep).' Q';
		}
		if($num_abs < 100000000000000000){
			return number_format(floor($num/100000000000000)/10, max(0, $digits-2), $dec_sep, $k_sep).' Q';
		}
		return number_format(floor($num/1000000000000000), max(0, $digits-3), $dec_sep, $k_sep).' Q';
	}
}
?>