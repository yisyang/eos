<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'toggle_on_off'){
	$setting = filter_var($_POST['setting'], FILTER_SANITIZE_STRING);
	switch($setting){
		case 'menu_tooltip':
			$query_current = $db->prepare("SELECT show_menu_tooltip FROM players WHERE id = $eos_player_id");
			$query_update = $db->prepare("UPDATE players SET show_menu_tooltip = :new_value WHERE id = $eos_player_id");
			$sn = 'Menu Tooltip';
			break;
		case 'narrow_screen':
			$query_current = $db->prepare("SELECT narrow_screen FROM players WHERE id = $eos_player_id");
			$query_update = $db->prepare("UPDATE players SET narrow_screen = :new_value WHERE id = $eos_player_id");
			$sn = 'Minimal Interface';
			break;
		case 'queue_countdown':
			$query_current = $db->prepare("SELECT queue_countdown FROM players WHERE id = $eos_player_id");
			$query_update = $db->prepare("UPDATE players SET queue_countdown = :new_value WHERE id = $eos_player_id");
			$sn = 'Queue Countdown';
			break;
		case 'enable_chat':
			$query_current = $db->prepare("SELECT enable_chat FROM players WHERE id = $eos_player_id");
			$query_update = $db->prepare("UPDATE players SET enable_chat = :new_value WHERE id = $eos_player_id");
			$sn = 'Chat';
			break;
		case 'auto_repay_loan':
			$query_current = $db->prepare("SELECT auto_repay_loan FROM firms_extended WHERE id = $eos_firm_id");
			$query_update = $db->prepare("UPDATE firms_extended SET auto_repay_loan = :new_value WHERE id = $eos_firm_id");
			$sn = 'Auto Loan Payments';
			break;
		default:
			$resp = array('success' => 0, 'msg' => 'No setting specified.');
			echo json_encode($resp);
			exit();
	}

	$query_current->execute();
	$setting = $query_current->fetch(PDO::FETCH_NUM);

	if(empty($setting)){
		$resp = array('success' => 0, 'msg' => 'Cannot find setting.');
		echo json_encode($resp);
		exit();
	}else{
		$new_value = 1 - $setting[0];
		$query_update->execute(array(':new_value' => $new_value));
		if($new_value == 1){
			$resp = array('success' => 1, 'title' => 'Turn OFF '.$sn);
			echo json_encode($resp);
			exit();
		}else{
			$resp = array('success' => 1, 'title' => 'Turn ON '.$sn);
			echo json_encode($resp);
			exit();
		}
	}
}
else if($action == 'update_b2b_rows_per_page'){
	$rows = filter_var($_POST['rows'], FILTER_SANITIZE_NUMBER_INT);

	if(!$rows || $rows < 5 || $rows > 200){
		$resp = array('success' => 0, 'msg' => 'Please select a value within the valid range.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "UPDATE players SET b2b_rows_per_page = '$rows' WHERE id = $eos_player_id";
	$db->query($sql);
	
	$resp = array('success' => 1, 'rows' => $rows);
	echo json_encode($resp);
	exit();
}
else if($action == 'player_avatar_ajaxupload'){
	//Load the dd uploader script
	require_once 'scripts/dd_image_uploader.php';

	$filename = $eos_player_id;
	list($success, $new_filename) = uploadImage($filename, 1, 'jpg,jpeg,gif,png');
	
	if($success){
		$sql = "SELECT avatar_filename FROM players WHERE id = $eos_player_id";
		$old_filename = $db->query($sql)->fetchColumn();
		if($old_filename != $new_filename){
			@unlink('images/players/'.$old_filename);
		}
		
		$sql = "UPDATE players SET avatar_filename = '$new_filename' WHERE id = $eos_player_id";
		$result = $db->query($sql);

		if($result){
			echo json_encode(array('success' => 1, 'callback' => 'settingsController.updatePlayerAvatar', 'filename' => $new_filename));
			exit();
		}else{
			echo json_encode(array('success' => 0, 'jsonMsg' => _('Image uploaded but SQL failed.')));
			exit();
		}
	}else{
		$error_msg = _('Error(s) Found: ');
		foreach($new_filename as $error){
	    	$error_msg .= $error.', ';
		}
		echo json_encode(array('success' => 0, 'jsonMsg' => $error_msg));
		exit();
	}
}
else if($action == 'player_avatar_ajaxupload_fb'){
	// Load the dd uploader script
	require_once 'scripts/dd_image_uploader.php';

	$fb_player_fb_id = filter_var($_POST['fb_id'], FILTER_SANITIZE_STRING);
	$fb_player_avatar_link = "http://graph.facebook.com/".$fb_player_fb_id."/picture?type=large";
	
	// Ugly but fast hack
	$_POST['fileurl'] = $fb_player_avatar_link;
	
	$filename = $eos_player_id;
	list($success, $new_filename) = uploadImage($filename, 1, 'jpg,jpeg,gif,png');
	
	if($success){
		$sql = "SELECT avatar_filename FROM players WHERE id = $eos_player_id";
		$old_filename = $db->query($sql)->fetchColumn();
		if($old_filename != $new_filename){
			@unlink('images/players/'.$old_filename);
		}
		
		$sql = "UPDATE players SET avatar_filename = '$new_filename' WHERE id = $eos_player_id";
		$result = $db->query($sql);

		if($result){
			echo json_encode(array('success' => 1, 'callback' => 'settingsController.updatePlayerAvatar', 'filename' => $new_filename));
			exit();
		}else{
			echo json_encode(array('success' => 0, 'jsonMsg' => _('Image uploaded but SQL failed.')));
			exit();
		}
	}else{
		$error_msg = _('Error(s) Found: ');
		foreach($new_filename as $error){
	    	$error_msg .= $error.', ';
		}
		echo json_encode(array('success' => 0, 'jsonMsg' => $error_msg));
		exit();
	}
}
else if($action == 'check_player_alias'){
	$alias = $_POST['alias'];

	if(strlen($alias) > 24 || strlen($alias) < 3){
		$resp = array('success' => 0, 'msg' => 'Alias must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	if(!preg_match('/^[a-zA-Z0-9-_]+$/', $alias)){
		$resp = array('success' => 0, 'msg' => 'Please only use alphanumeric characters (a-Z, 0-9), dashes (-), or underline (_).');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM players WHERE player_alias = ?";
	$query = $db->prepare($sql);
	$query->execute(array($alias));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The alias '.$alias.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$resp = array('success' => 1, 'msg' => 'This alias can be used.');
	echo json_encode($resp);
	exit();
}
else if($action == 'update_player_alias'){
	$alias = $_POST['alias'];

	if(strlen($alias) > 24 || strlen($alias) < 3){
		$resp = array('success' => 0, 'msg' => 'Alias must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	if(!preg_match('/^[a-zA-Z0-9-_]+$/', $alias)){
		$resp = array('success' => 0, 'msg' => 'Please only use alphanumeric characters (a-Z, 0-9), dashes (-), or underline (_).');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM players WHERE player_alias = ?";
	$query = $db->prepare($sql);
	$query->execute(array($alias));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The alias '.$alias.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) FROM log_limited_actions WHERE action = 'player alias' AND actor_id = $eos_player_id AND action_time > DATE_ADD(NOW(), INTERVAL -30 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'You have changed your alias within the past 30 days!');
		echo json_encode($resp);
		exit();
	}

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('player alias', $eos_player_id, NOW())";
	$db->query($sql);

	// Change alias
	$sql = "UPDATE players SET player_alias = ? WHERE id = $eos_player_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($alias));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'check_player_name'){
	$name = $_POST['name'];
	
	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM players WHERE player_name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The player name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$players = $db->query("(SELECT player_name FROM players WHERE id < 100) UNION (SELECT player_name FROM players ORDER BY player_networth DESC LIMIT 0, 200)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($players as $player){
		similar_text($sim_name, strtoupper($player['player_name']), $similarity_pst);
		if ((int) $similarity_pst > 70){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the player\'s name: '.$player['player_name']);
			echo json_encode($resp);
			exit();
		}
	}

	$resp = array('success' => 1, 'msg' => 'This name can be used.');
	echo json_encode($resp);
	exit();
}
else if($action == 'update_player_name'){
	$name = $_POST['name'];
	
	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM players WHERE player_name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The player name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$players = $db->query("(SELECT player_name FROM players WHERE id < 100) UNION (SELECT player_name FROM players ORDER BY player_networth DESC LIMIT 0, 200)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($players as $player){
		similar_text($sim_name, strtoupper($player['player_name']), $similarity_pst);
		if ((int) $similarity_pst > 70){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the player\'s name: '.$player['player_name']);
			echo json_encode($resp);
			exit();
		}
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action = 'player rename' AND actor_id = $eos_player_id AND action_time > DATE_ADD(NOW(), INTERVAL -30 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'You have changed your name within the past 30 days!');
		echo json_encode($resp);
		exit();
	}

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('player rename', $eos_player_id, NOW())";
	$db->query($sql);

	// Change name
	$sql = "UPDATE players SET player_name = ? WHERE id = $eos_player_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($name));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'update_player_desc'){
	$desc = $_POST['desc'];
	
	if(strlen($desc) > 3000){
		$resp = array('success' => 0, 'msg' => "Please don't write an essay here. (Try to keep the description under 2000 characters)");
		echo json_encode($resp);
		exit();
	}
	
	// Remove non-allowed html tags
	$allowed_tags = array("br", "br \/", "b", "i", "u", "big", "small", "ul", "ol", "li");
	$desc = str_replace(array("<", ">"), array("&lt;","&gt;"), $desc);
	$regex = sprintf("~&lt;(/)?(%s)&gt;~", implode("|",$allowed_tags));
	$desc = preg_replace($regex, "<\\1\\2>", $desc);
	
	// Change desc
	$sql = "UPDATE players_extended SET player_desc = ? WHERE id = $eos_player_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($desc));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1, 'desc' => nl2br($desc));
	echo json_encode($resp);
	exit();
}
else if($action == 'vacation_mode'){
	if(!isset($_POST['firm_ids'])){
		$resp = array('success' => 0, 'msg' => 'Lock down order failed. No company selected.');
		echo json_encode($resp);
		exit();
	}

	$firm_ids = filter_var_array($_POST['firm_ids'], FILTER_SANITIZE_NUMBER_INT);
	$vacation_duration = filter_var($_POST['vacation_duration'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

	$referrer = $_SERVER['HTTP_REFERER'];
	if(strpos($referrer, '?')) $referrer = substr($referrer, 0, strpos($referrer, '?'));
	$referrer = substr($referrer, strrpos($referrer, "/") + 1);
	if($referrer != "settings.php" && $referrer != "city.php"){
		$resp = array('success' => 0, 'msg' => 'Lock down order failed. Invalid referrer.');
		echo json_encode($resp);
		exit();
	}
	if(empty($firm_ids)){
		$resp = array('success' => 0, 'msg' => 'Lock down order failed. No company selected.');
		echo json_encode($resp);
		exit();
	}
	if(!$vacation_duration || $vacation_duration < 1){
		$resp = array('success' => 0, 'msg' => 'Sorry, this feature is not available for vacations lasting less than a day.');
		echo json_encode($resp);
		exit();
	}
	if($vacation_duration > 60){
		$resp = array('success' => 0, 'msg' => 'Sorry, the maximum vacation time is 60 days. This is not France.');
		echo json_encode($resp);
		exit();
	}
	if(!isset($_SESSION['p_vacation_time']) || time() - $_SESSION['p_vacation_time'] > 150){
		$resp = array('success' => 0, 'msg' => 'Lock down order failed. You took too long, please close the vacation mode dialog and re-open it to try again.');
		echo json_encode($resp);
		exit();
	}
	unset($_SESSION['p_vacation_time']);
	
	$vacation_extended_time = floor(86400 * $vacation_duration);
	$vacation_out_time = date("Y-m-d H:i:s", time() + $vacation_extended_time);

	foreach($firm_ids as $firm_id){
		// Verify ownership
		if($firm_id){
			$sql = "SELECT COUNT(*) AS cnt FROM firms_extended LEFT JOIN firms ON firms.id = firms_extended.id WHERE firms_extended.id = '$firm_id' AND firms_extended.ceo = $eos_player_id AND firms.vacation_out < NOW()";
			$verified = $db->query($sql)->fetchColumn();
		}
		if($verified){
			$sql = "UPDATE firms SET firms.last_active = '$vacation_out_time', firms.vacation_out = '$vacation_out_time' WHERE firms.id = $firm_id";
			$db->query($sql);
			
			$sql = "UPDATE queue_build SET starttime = starttime + $vacation_extended_time, endtime = endtime + $vacation_extended_time WHERE fid = $firm_id";
			$db->query($sql);
			$sql = "UPDATE queue_prod SET starttime = starttime + $vacation_extended_time, endtime = endtime + $vacation_extended_time WHERE fid = $firm_id";
			$db->query($sql);
			$sql = "UPDATE queue_res SET starttime = starttime + $vacation_extended_time, endtime = endtime + $vacation_extended_time WHERE fid = $firm_id";
			$db->query($sql);
		}
	}

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'restart_player'){
	$p_restart_confirmation = strtolower(filter_var($_POST['restart_confirmation'], FILTER_SANITIZE_STRING));

	if($p_restart_confirmation !== "restart"){
		$resp = array('success' => 0, 'msg' => 'Restart failed. Confirmation text is missing. You must type RESTART into the input box if you choose to restart.');
		echo json_encode($resp);
		exit();
	}
	$referrer = $_SERVER['HTTP_REFERER'];
	if(strpos($referrer, '?')) $referrer = substr($referrer, 0, strpos($referrer, '?'));
	$referrer = substr($referrer, strrpos($referrer, "/") + 1);
	if($referrer != "settings.php" && $referrer != "city.php"){
		$resp = array('success' => 0, 'msg' => 'Restart failed. Invalid referrer.');
		echo json_encode($resp);
		exit();
	}
	if(!isset($_SESSION['p_restart_time']) || time() - $_SESSION['p_restart_time'] > 150){
		$resp = array('success' => 0, 'msg' => 'Restart failed. You took too long, please close the restart dialog and re-open it to try again.');
		echo json_encode($resp);
		exit();
	}
	unset($_SESSION['p_restart_time']);

	$sql = "SELECT COUNT(*) AS cnt FROM player_stock WHERE pid = $eos_player_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Restart failed. You still have shares on your hands.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT player_networth FROM players WHERE id = $eos_player_id";
	$player_nw = $db->query($sql)->fetchColumn();
	if($player_nw > 1000000000000){
		$resp = array('success' => 0, 'msg' => 'You cannot do a reset anymore as you are now an indispensable part of our economy.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT action_time FROM log_limited_actions WHERE actor_id = $eos_player_id AND action = 'restart' AND action_time > DATE_ADD(NOW(), INTERVAL -1 DAY)";
	$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(!empty($result)){
		$resp = array('success' => 0, 'msg' => 'You last restarted on: '.$result['action_time'].'. You may only restart once every 24 hours.');
		echo json_encode($resp);
		exit();
	}	

	// $sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('restart', $eos_player_id, NOW())";
	// $db->query($sql);

	$sql = "SELECT id FROM firms_extended WHERE !is_public AND ceo = $eos_player_id";
	$firms = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($firms as $firm){
		$target_firm_id = $firm['id'];
		$sql = "SELECT COUNT(*) FROM firms WHERE id = $target_firm_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			// Delete company
			$sql = "DELETE FROM firm_fact WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firm_rnd WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firm_store WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firm_news WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firm_quest WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firm_tech WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firm_wh WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM history_firms WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM market_prod WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM market_requests WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM log_market_prod WHERE sfid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM log_market_prod WHERE bfid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM queue_build WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM queue_prod WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM queue_res WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE es_positions.*, es_applications.* FROM es_positions LEFT JOIN es_applications ON es_positions.id = es_applications.esp_id WHERE es_positions.fid = $target_firm_id";
			$db->query($sql);
			$sql = "INSERT INTO player_news (pid, body, date_created) SELECT firms_positions.pid, CONCAT('Dear ',firms_positions.title,', your job with ',firms.name,' has ended because the company no longer exists.'), NOW() FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firms_positions WHERE fid = $target_firm_id";
			$db->query($sql);
			$sql = "UPDATE log_management SET endtime = NOW() WHERE endtime > NOW() AND fid = $target_firm_id";
			$db->query($sql);
			$sql = "INSERT INTO log_firms_sold (pid, fid, firm_name) SELECT $eos_player_id, firms.id, firms.name FROM firms WHERE id = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firms_extended WHERE id = $target_firm_id";
			$db->query($sql);
			$sql = "DELETE FROM firms WHERE id = $target_firm_id";
			$db->query($sql);
			$sql = "UPDATE players SET fid = 0 WHERE fid = '$target_firm_id'";
			$db->query($sql);
		}
	}

	$sql = "DELETE FROM player_news WHERE pid = $eos_player_id";
	$db->query($sql);
	$sql = "SELECT player_name FROM players WHERE id = $eos_player_id";
	$gen_player_name = $db->query($sql)->fetchColumn();
	if(strlen($gen_player_name) > 15){
		$gen_player_name = substr($gen_player_name, 0, 15);
	}

	$gen_firm_name = $gen_player_name.' Inc.';
	$query = $db->prepare("SELECT COUNT(*) AS cnt FROM firms WHERE name = ?");
	$query->execute(array($gen_firm_name));
	$count = $query->fetchColumn();
	$gen_num = 0;
	while($count > 0){
		$gen_num += 1;
		$gen_firm_name = $gen_player_name.' '.$gen_num.' Inc.';
		$query->execute(array($gen_firm_name));
		$count = $query->fetchColumn();
	}

	// Generate firm color
	$fcolor_total_leftover = mt_rand(200, 1000); // Make colors lighter
	$fcolor_r = min($fcolor_total_leftover, mt_rand(0, 255));
	$fcolor_total_leftover = $fcolor_total_leftover - $fcolor_r;
	$fcolor_g = min($fcolor_total_leftover, mt_rand(0, 255));
	$fcolor_total_leftover = $fcolor_total_leftover - $fcolor_g;
	$fcolor_b = min($fcolor_total_leftover, mt_rand(0, 255));
	$fcolor = '#'.str_pad(dechex($fcolor_r), 2, '0', STR_PAD_LEFT).str_pad(dechex($fcolor_g), 2, '0', STR_PAD_LEFT).str_pad(dechex($fcolor_b), 2, '0', STR_PAD_LEFT);

	$query = $db->prepare("INSERT INTO firms (name, color, cash, loan, networth, level, last_login, last_active) VALUES (?, '$fcolor', 1000000000, 900000000, 100000000, 3, NOW(), NOW())");
	$query->execute(array($gen_firm_name));

	$query = $db->prepare("SELECT id FROM firms WHERE name = ?");
	$query->execute(array($gen_firm_name));
	$eos_firm_id = $query->fetchColumn();

	$sql = "INSERT INTO firms_extended (id, is_public, ceo) VALUES ('$eos_firm_id', 0, '$eos_player_id')";
	$db->query($sql);

	$sql = "SELECT id, name, tech_avg FROM list_prod WHERE value > 49 AND value < 2001";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$prods_count = count($prods);

	// Generate 3 bonus techs
	$bonus_techs = array();
	while(count($bonus_techs) < 5){
		$prod_index = mt_rand(0, $prods_count-1);
		$bt_row = array($prods[$prod_index]['id'], $prods[$prod_index]['name'], floor(0.5 * $prods[$prod_index]['tech_avg']));
		if(!in_array($bt_row, $bonus_techs)){
			$bonus_techs[] = $bt_row;
		}
	}
	$timenow = time();
	foreach($bonus_techs as $bonus_tech){
		$sql = "INSERT INTO firm_tech (fid, pid, quality, update_time) VALUES ($eos_firm_id, ".$bonus_tech[0].", ".$bonus_tech[2].", '$timenow')";
		$db->query($sql);
	}

	// Update player
	$sql = "UPDATE players SET fid = $eos_firm_id, player_cash = 0, player_networth = 100000000 WHERE id = $eos_player_id";
	$db->query($sql);

	$sql = "UPDATE players_extended SET voted = 0, voted_streak = 0 WHERE id = $eos_player_id";
	$db->query($sql);

	$sql = "INSERT INTO firms_positions (fid, pid, title, pay_flat, bonus_percent, next_pay_flat, next_bonus_percent, next_accepted, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) VALUES ($eos_firm_id, $eos_player_id, 'Owner', 0, 0, 0, 0, 1, NOW(), '2222-01-01', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";
	$db->query($sql);
	
	$sql = "SELECT id FROM firms_positions WHERE fid = $eos_firm_id AND pid = $eos_player_id ORDER BY id DESC";
	$fp_id = $db->query($sql)->fetchColumn();

	// Insert into logs
	$sql = "INSERT INTO log_management (id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) 
	SELECT id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM firms_positions WHERE firms_positions.id = $fp_id";
	$db->query($sql);

	$_SESSION['firm_name'] = $gen_firm_name;
	$_SESSION['firm_cash'] = 1000000000;

	$resp = array('success' => 1, 'bonus_techs' => $bonus_techs);
	echo json_encode($resp);
	exit();
}
?>