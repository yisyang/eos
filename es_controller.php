<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'es_apply'){
	$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);
	$cover_letter = filter_var($_POST['cover_letter'], FILTER_SANITIZE_STRING);
	
	$sql = "SELECT firms.id AS firm_id, firms.name AS firm_name, es_positions.title FROM es_positions LEFT JOIN firms ON firms.id = es_positions.fid WHERE es_positions.id = $esp_id";
	$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($position)){
		$resp = array('success' => 0, 'msg' => 'This position was recently filled or has been removed.');
		echo json_encode($resp);
		exit();
	}else{
		$es_firm_id = $position['firm_id'];
		$es_firm_name = $position['firm_name'];
		$esc_title = $position['title'];

		// Check if application exists
		$sql = "SELECT COUNT(*) AS cnt FROM es_applications WHERE esp_id = $esp_id AND pid = $eos_player_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			$resp = array('success' => 0, 'msg' => 'You have already applied for this position.');
			echo json_encode($resp);
			exit();
		}
		$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE fid = $es_firm_id AND pid = $eos_player_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			$resp = array('success' => 0, 'msg' => 'You are already working for this company.');
			echo json_encode($resp);
			exit();
		}
		$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE firms_positions.pid = $eos_player_id";
		$count = $db->query($sql)->fetchColumn();
		if($count > 19){
			$resp = array('success' => 0, 'msg' => 'The company would not hire you due to conflict of interest. (You have too many active positions)');
			echo json_encode($resp);
			exit();
		}
	}

	// Insert job listing
	$query = $db->prepare("INSERT INTO es_applications (esp_id, pid, cover_letter, apply_time) VALUES (?, ?, ?, NOW())");
	$query->execute(array($esp_id, $eos_player_id, $cover_letter));

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'es_hire'){
	$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);
	$esp_pid = filter_var($_POST['esp_pid'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$ctrl_hr_hire){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to hire new employees.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT firms.name AS firm_name, es_positions.id, es_positions.title, es_positions.pay_flat, es_positions.bonus_percent FROM es_positions LEFT JOIN firms ON firms.id = es_positions.fid WHERE es_positions.id = $esp_id AND es_positions.fid = $eos_firm_id";
	$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($position)){
		$resp = array('success' => 0, 'msg' => 'This position was recently filled or has been removed.');
		echo json_encode($resp);
		exit();
	}

	$esp_id = $position['id'];
	$esp_fid = $eos_firm_id;
		
	$sql = "SELECT players.player_name FROM es_applications LEFT JOIN players ON es_applications.pid = players.id WHERE es_applications.esp_id = $esp_id AND es_applications.pid = '$esp_pid'";
	$applicant = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($applicant)){
		$resp = array('success' => 0, 'msg' => 'The candidate did not apply for the job or has withdrawn his/her application.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE fid = $eos_firm_id AND pid = $esp_pid";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'This person is already working at your company!');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE firms_positions.pid = $esp_pid";
	$count = $db->query($sql)->fetchColumn();
	if($count > 19){
		$resp = array('success' => 0, 'msg' => 'The law forbids hiring this person at this time, because he/she is working at too many jobs.');
		echo json_encode($resp);
		exit();
	}	

	// Insert new contracts
	$sql = "INSERT INTO firms_positions (fid, pid, title, pay_flat, bonus_percent, next_pay_flat, next_bonus_percent, next_accepted, starttime, endtime, duration, daily_allowance, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) SELECT :fid, :pid, :title, pay_flat, bonus_percent, pay_flat AS npf, bonus_percent AS nbp, 1, NOW(), DATE_ADD(NOW(), INTERVAL duration DAY), duration, daily_allowance, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM es_positions WHERE es_positions.id = $esp_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':fid' => $esp_fid, ':pid' => $esp_pid, ':title' => $position['title']));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	// Grab fp_id
	$sql = "SELECT id FROM firms_positions WHERE fid = $esp_fid AND pid = $esp_pid ORDER BY id DESC";
	$fp_id = $db->query($sql)->fetchColumn();
	
	// Insert into logs
	$sql = "INSERT INTO log_management (id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) 
	SELECT id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM firms_positions WHERE firms_positions.id = $fp_id";
	$db->query($sql);

	// Notify hired
	$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($esp_pid, ?, NOW())";
	$query = $db->prepare($sql);
	$query->execute(array('Congratulations! You have been accepted as the '.$position['title'].' of '.$position['firm_name'].'!'));
	
	// Notify shareholders
	$sql = "INSERT INTO player_news (pid, body, date_created) SELECT pid, ?, NOW() FROM player_stock WHERE fid = $eos_firm_id";
	$query = $db->prepare($sql);
	$query->execute(array('Dear Investor, <a href="/eos/firm/'.$esp_fid.'">'.$position['firm_name'].'</a> has recently accepted <a href="/eos/player/'.$esp_pid.'">'.$applicant['player_name'].'</a> into the position of '.$position['title'].'.'));
	
	// Delete applications
	$sql = "DELETE FROM es_positions WHERE id = $esp_id";
	$db->query($sql);
	$sql = "DELETE FROM es_applications WHERE esp_id = $esp_id";
	$db->query($sql);
	$sql = "DELETE es_applications.* FROM es_applications LEFT JOIN es_positions ON es_applications.esp_id = es_positions.id WHERE es_positions.fid = $eos_firm_id AND es_applications.pid = $esp_pid";
	$db->query($sql);
	
	$resp = array('success' => 1, 'msg' => 'Dear Recruiter, you have successfully recruited <a href="/eos/player/'.$esp_pid.'">'.$applicant['player_name'].'</a> as the new '.$position['title'].' of <a href="/eos/firm/'.$esp_fid.'">'.$position['firm_name'].'</a>.');
	echo json_encode($resp);
	exit();
}
else if($action == 'new_assignment'){
	$salary = floor(filter_var($_POST['salary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	$bonus = filter_var($_POST['bonus'], FILTER_SANITIZE_NUMBER_INT)/100;

	$sql = "SELECT firms.name, firms.level, firms.cash, firms.networth FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_breakout('city.php');
	}else{
		$firm_name = $firm["name"];
		$firm_level = $firm["level"];
		$firm_cash = $firm["cash"];
		$firm_networth = $firm["networth"];
	}
	$sql = "SELECT SUM(bonus_percent) FROM firms_positions WHERE fid = $eos_firm_id";
	$bonus_percent_spent = $db->query($sql)->fetchColumn();
	
	$min_salary = max(1000000, floor($firm_networth / 10000));
	$max_salary = max(100000000, floor($firm_networth / 10000) * 100);
	$min_bonus = 0;
	$max_bonus = max(0, min(20, 80 - $bonus_percent_spent));

	$posting_fee = $firm_level * 20000000;
	if($firm_cash < $posting_fee){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}
	if($ctrl_leftover_allowance < $posting_fee){
		$resp = array('success' => 0, 'msg' => 'Daily spending limit reached.');
		echo json_encode($resp);
		exit();
	}
	if(!$ctrl_hr_post){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to post search assignments.');
		echo json_encode($resp);
		exit();
	}
	if($salary < $min_salary || $salary > $max_salary){
		$resp = array('success' => 0, 'msg' => 'The salary you have entered is invalid. Please check and re-submit the form.');
		echo json_encode($resp);
		exit();
	}
	if($bonus < $min_bonus || $bonus > $max_bonus){
		$resp = array('success' => 0, 'msg' => 'The bonus you have entered is invalid. Please check and re-submit the form.');
		echo json_encode($resp);
		exit();
	}

	$esc_title = filter_var($_POST['esc_title'], FILTER_SANITIZE_STRING);
	if(!$esc_title || strtolower($esc_title) == "owner" || strtolower($esc_title) == "chair" || strtolower($esc_title) == "chairman"){
		$resp = array('success' => 0, 'msg' => 'Please provide a valid job title.');
		echo json_encode($resp);
		exit();
	}
	$esc_duration = filter_var($_POST['esc_duration'], FILTER_SANITIZE_NUMBER_INT);
	if(!$esc_duration || $esc_duration > 30 || $esc_duration < 7){
		$resp = array('success' => 0, 'msg' => 'Please keep the term between 7 to 30 server days.');
		echo json_encode($resp);
		exit();
	}
	
	$esc_daily_allowance_unlimited = filter_var($_POST['esc_daily_allowance_unlimited'], FILTER_SANITIZE_NUMBER_INT);
	if($esc_daily_allowance_unlimited){
		if($ctrl_daily_allowance != -1){
			$resp = array('success' => 0, 'msg' => 'You are not authorized to post jobs with an unlimited spending limit.');
			echo json_encode($resp);
			exit();
		}
		$esc_daily_allowance = -1;
	}else{
		$esc_daily_allowance = floor(filter_var($_POST['esc_daily_allowance'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
		if($esc_daily_allowance <= 0){
			$resp = array('success' => 0, 'msg' => 'Cash is needed for just about everything: production, purchase, marketing, research, b2b requests and relisting, even appraisal and job posting. If you are certain that no spending is needed for this position, please enter $0.01 as the daily spending limit.');
			echo json_encode($resp);
			exit();
		}
		if($esc_daily_allowance > 99999999999999900){
			$esc_daily_allowance = 99999999999999900;
		}
		if($ctrl_daily_allowance != -1 && $esc_daily_allowance > $ctrl_daily_allowance){
			$resp = array('success' => 0, 'msg' => 'Invalid spending limit, it must not be greater than your own: '.number_format($ctrl_daily_allowance/100, 2, '.', ','));
			echo json_encode($resp);
			exit();
		}
	}

	$esc_bldg_hurry = min($ctrl_bldg_hurry, filter_var($_POST['esc_bldg_hurry'], FILTER_SANITIZE_NUMBER_INT));
	$esc_bldg_land = min($ctrl_bldg_land, filter_var($_POST['esc_bldg_land'], FILTER_SANITIZE_NUMBER_INT));
	// $esc_bldg_view = 1;
	$esc_fact_produce = min($ctrl_fact_produce, filter_var($_POST['esc_fact_produce'], FILTER_SANITIZE_NUMBER_INT));
	$esc_fact_cancel = min($ctrl_fact_cancel, filter_var($_POST['esc_fact_cancel'], FILTER_SANITIZE_NUMBER_INT));
	$esc_fact_build = min($ctrl_fact_build, filter_var($_POST['esc_fact_build'], FILTER_SANITIZE_NUMBER_INT));
	$esc_fact_expand = min($ctrl_fact_expand, filter_var($_POST['esc_fact_expand'], FILTER_SANITIZE_NUMBER_INT));
	$esc_fact_sell = min($ctrl_fact_sell, filter_var($_POST['esc_fact_sell'], FILTER_SANITIZE_NUMBER_INT));
	$esc_store_price = min($ctrl_store_price, filter_var($_POST['esc_store_price'], FILTER_SANITIZE_NUMBER_INT));
	$esc_store_ad = min($ctrl_store_ad, filter_var($_POST['esc_store_ad'], FILTER_SANITIZE_NUMBER_INT));
	$esc_store_build = min($ctrl_store_build, filter_var($_POST['esc_store_build'], FILTER_SANITIZE_NUMBER_INT));
	$esc_store_expand = min($ctrl_store_expand, filter_var($_POST['esc_store_expand'], FILTER_SANITIZE_NUMBER_INT));
	$esc_store_sell = min($ctrl_store_sell, filter_var($_POST['esc_store_sell'], FILTER_SANITIZE_NUMBER_INT));
	$esc_rnd_res = min($ctrl_rnd_res, filter_var($_POST['esc_rnd_res'], FILTER_SANITIZE_NUMBER_INT));
	$esc_rnd_cancel = min($ctrl_rnd_cancel, filter_var($_POST['esc_rnd_cancel'], FILTER_SANITIZE_NUMBER_INT));
	$esc_rnd_hurry = min($ctrl_rnd_hurry, filter_var($_POST['esc_rnd_hurry'], FILTER_SANITIZE_NUMBER_INT));
	$esc_rnd_build = min($ctrl_rnd_build, filter_var($_POST['esc_rnd_build'], FILTER_SANITIZE_NUMBER_INT));
	$esc_rnd_expand = min($ctrl_rnd_expand, filter_var($_POST['esc_rnd_expand'], FILTER_SANITIZE_NUMBER_INT));
	$esc_rnd_sell = min($ctrl_rnd_sell, filter_var($_POST['esc_rnd_sell'], FILTER_SANITIZE_NUMBER_INT));
	$esc_wh_view = min($ctrl_wh_view, filter_var($_POST['esc_wh_view'], FILTER_SANITIZE_NUMBER_INT));
	$esc_wh_sell = min($ctrl_wh_sell, filter_var($_POST['esc_wh_sell'], FILTER_SANITIZE_NUMBER_INT));
	$esc_wh_discard = min($ctrl_wh_discard, filter_var($_POST['esc_wh_discard'], FILTER_SANITIZE_NUMBER_INT));
	$esc_b2b_buy = min($ctrl_b2b_buy, filter_var($_POST['esc_b2b_buy'], FILTER_SANITIZE_NUMBER_INT));
	$esc_hr_post = min($ctrl_hr_post, filter_var($_POST['esc_hr_post'], FILTER_SANITIZE_NUMBER_INT));
	$esc_hr_hire = min($ctrl_hr_hire, filter_var($_POST['esc_hr_hire'], FILTER_SANITIZE_NUMBER_INT));
	$esc_hr_fire = min($ctrl_hr_fire, filter_var($_POST['esc_hr_fire'], FILTER_SANITIZE_NUMBER_INT));

	// Charge them
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $posting_fee, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$affected){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}

	// Log expense
	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES (:firm_id, :is_debit, :cost, :source, NOW())");
	$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':cost' => $posting_fee, ':source' => 'ES Fee'));
	$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $posting_fee WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$db->query($sql);

	// Insert job listing
	$sql = "INSERT INTO es_positions (fid, title, pay_flat, bonus_percent, duration, post_time, daily_allowance, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) VALUES ($eos_firm_id, '$esc_title', $salary, $bonus, $esc_duration, NOW(), $esc_daily_allowance, 0, $esc_bldg_hurry, $esc_bldg_land, 1, $esc_fact_produce, $esc_fact_cancel, $esc_fact_build, $esc_fact_expand, $esc_fact_sell, $esc_store_price, $esc_store_ad, $esc_store_build, $esc_store_expand, $esc_store_sell, $esc_rnd_res, $esc_rnd_cancel, $esc_rnd_hurry, $esc_rnd_build, $esc_rnd_expand, $esc_rnd_sell, $esc_wh_view, $esc_wh_sell, $esc_wh_discard, $esc_b2b_buy, $esc_hr_post, $esc_hr_hire, $esc_hr_fire)";
	$result = $db->query($sql);
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'update_assignment'){
	$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);
	$salary = floor(filter_var($_POST['salary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	$bonus = filter_var($_POST['bonus'], FILTER_SANITIZE_NUMBER_INT)/100;

	// Check if job listing exists
	$sql = "SELECT pay_flat, bonus_percent FROM es_positions WHERE fid = $eos_firm_id AND id = $esp_id";
	$assignment = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($assignment)){
		$resp = array('success' => 0, 'msg' => 'This position was recently filled or has been removed.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT firms.name, firms.level, firms.cash, firms.networth FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_breakout('city.php');
	}else{
		$firm_name = $firm["name"];
		$firm_level = $firm["level"];
		$firm_cash = $firm["cash"];
		$firm_networth = $firm["networth"];
	}
	$sql = "SELECT SUM(bonus_percent) FROM firms_positions WHERE fid = $eos_firm_id";
	$bonus_percent_spent = $db->query($sql)->fetchColumn();
	
	$min_salary = max(1000000, floor($firm_networth / 10000), $assignment['pay_flat']);
	$max_salary = max(100000000, floor($firm_networth / 10000) * 100, $min_salary);
	$min_bonus = max(0, $assignment['bonus_percent']);
	$max_bonus = max(0, min(20, 80 - $bonus_percent_spent), $min_bonus);

	if(!$ctrl_hr_post){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to post search assignments.');
		echo json_encode($resp);
		exit();
	}
	if($salary < $min_salary || $salary > $max_salary){
		$resp = array('success' => 0, 'msg' => 'The salary you have entered is invalid. Please check and re-submit the form.');
		echo json_encode($resp);
		exit();
	}
	if($bonus < $min_bonus || $bonus > $max_bonus){
		$resp = array('success' => 0, 'msg' => 'The bonus you have entered is invalid. Please check and re-submit the form.');
		echo json_encode($resp);
		exit();
	}

	$sql = "UPDATE es_positions SET pay_flat = GREATEST(pay_flat, $salary), bonus_percent = GREATEST(bonus_percent, $bonus), post_time = NOW() WHERE fid = $eos_firm_id AND id = $esp_id";
	$db->query($sql);
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'cancel_assignment'){
	$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);

	if(!$ctrl_hr_post){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to alter search assignments.');
		echo json_encode($resp);
		exit();
	}
	$sql = "SELECT COUNT(*) AS cnt FROM es_positions WHERE id = $esp_id AND fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if(!$count){
		$resp = array('success' => 0, 'msg' => 'This position was recently filled or has been removed.');
		echo json_encode($resp);
		exit();
	}
	
	// Delete Applications
	$sql = "DELETE FROM es_positions WHERE id = $esp_id";
	$db->query($sql);
	$sql = "DELETE FROM es_applications WHERE esp_id = $esp_id";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
?>