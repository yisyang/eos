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
	require_active_firm();

	$query = $db->prepare("SELECT firms.cash FROM firms WHERE firms.id = ?");
	$query->execute(array($eos_firm_id));
	$firm = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Firm not found.');
		echo json_encode($resp);
		exit();
	}
	$_SESSION['firm_cash'] = $firm['cash'];

	$allowance = ($ctrl_daily_allowance == -1) ? -1 : $ctrl_leftover_allowance;

	$resp = array('success' => 1, 'cash' => $firm['cash'], 'allowance' => $allowance);
	echo json_encode($resp);
	exit();
}
else if($action == 'get_all'){
	require_active_firm();

	$query = $db->prepare("SELECT firms.name, firms.networth, firms.cash, firms.loan, firms.level, firms.fame_level, firms.fame_exp, firms.vacation_out FROM firms WHERE firms.id = ?");
	$query->execute(array($eos_firm_id));
	$firm = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Firm not found.');
		echo json_encode($resp);
		exit();
	}

	$_SESSION['firm_name'] = $firm['name'];
	$_SESSION['firm_cash'] = $firm['cash'];
	$_SESSION['firm_loan'] = $firm['loan'];

	$allowance = ($ctrl_daily_allowance == -1) ? -1 : $ctrl_leftover_allowance;

	$resp = array('success' => 1, 'name' => $firm['name'], 'networth' => $firm['networth'], 'cash' => $firm['cash'], 'allowance' => $allowance, 'loan' => $firm['loan'], 'level' => $firm['level'], 'fame_level' => $firm['fame_level'], 'fame_exp' => $firm['fame_exp']);
	echo json_encode($resp);
	exit();
}
else if($action == 'check_firm_name'){
	$name = $_POST['name'];
	
	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM firms WHERE name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The company name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$firms = $db->query("(SELECT name FROM firms WHERE id < 100) UNION (SELECT name FROM firms ORDER BY networth DESC LIMIT 0, 100)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($firms as $firm){
		similar_text($sim_name, strtoupper($firm['name']), $similarity_pst);
		if ((int) $similarity_pst > 80){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the famous '.$firm['name']);
			echo json_encode($resp);
			exit();
		}
	}

	$resp = array('success' => 1, 'msg' => 'This name can be used.');
	echo json_encode($resp);
	exit();
}
else if($action == 'update_firm_name'){
	require_active_firm();

	$name = $_POST['name'];
	if(!$ctrl_admin){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM firms WHERE name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The company name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$firms = $db->query("(SELECT name FROM firms WHERE id < 100) UNION (SELECT name FROM firms ORDER BY networth DESC LIMIT 0, 100)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($firms as $firm){
		similar_text($sim_name, strtoupper($firm['name']), $similarity_pst);
		if ((int) $similarity_pst > 80){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the famous '.$firm['name']);
			echo json_encode($resp);
			exit();
		}
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action = 'firm rename' AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -30 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'You have changed your company name within the past 30 days!');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT name, cash, level, fame_level, fame_exp FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company cannot be found.');
		echo json_encode($resp);
		exit();
	}

	// Fetch Firm Stats
	if($firm['fame_level'] > 0){
		$firm_fame_level_new = $firm['fame_level'] - 1;
		$firm_fame_level_max_exp = 100 * $firm['fame_level'] * $firm['fame_level'] * $firm['fame_level'] - 1;
		$firm_fame_exp_new = min($firm['fame_exp'], $firm_fame_level_max_exp);
	}else{
		$firm_fame_level_new = 0;
		$firm_fame_exp_new = 0;
	}
	$rename_cost = 10000 * pow(3, $firm['level']);
	if($firm['cash'] < $rename_cost){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}
	
	// Deduct $ from firm, also update fame
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost, fame_level = $firm_fame_level_new, fame_exp = $firm_fame_exp_new WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $rename_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		echo '{"success" : 0, "msg" : "Insufficient cash."}';
		exit();
	}

	// Change name
	$sql = "UPDATE firms SET name = ? WHERE id = $eos_firm_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($name));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('firm rename', $eos_firm_id, NOW())";
	$db->query($sql);
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'update_firm_color'){
	require_active_firm();

	$color = $_POST['color'];
	if(!$ctrl_admin){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	if(!preg_match('/^#[a-f0-9]{6}$/i', $color)){
		$resp = array('success' => 0, 'msg' => 'Invalid hex color.');
		echo json_encode($resp);
		exit();
	}

	// Update color
	$sql = "UPDATE firms SET color = ? WHERE id = $eos_firm_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($color));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'start_new_firm'){
	$name = $_POST['name'];
	
	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM firms WHERE name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The company name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$firms = $db->query("(SELECT name FROM firms WHERE id < 100) UNION (SELECT name FROM firms ORDER BY networth DESC LIMIT 0, 100)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($firms as $firm){
		similar_text($sim_name, strtoupper($firm['name']), $similarity_pst);
		if ((int) $similarity_pst > 80){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the famous '.$firm['name']);
			echo json_encode($resp);
			exit();
		}
	}

	// Fetch Player Stats
	$sql = "SELECT COUNT(*) FROM firms_positions WHERE firms_positions.pid = $eos_player_id AND ctrl_admin";
	$eos_player_multi_firm_count = $db->query($sql)->fetchColumn();
	if($eos_player_multi_firm_count){
		$f_new_cost = 200000000 * pow(5, $eos_player_multi_firm_count);

		$sql = "SELECT player_cash FROM players WHERE id = $eos_player_id";
		$player_cash = $db->query($sql)->fetchColumn();
		
		if($player_cash < $f_new_cost){
			$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
			echo json_encode($resp);
			exit();
		}
		if($eos_player_multi_firm_count >= 10){
			$resp = array('success' => 0, 'msg' => 'Action is forbidden by the Anti-Trust Act of 2013.');
			echo json_encode($resp);
			exit();
		}

		// Deduct $ from player
		$query = $db->prepare("UPDATE players SET player_cash = player_cash - :cost WHERE id = :player_id AND player_cash >= :cost");
		$result = $query->execute(array(':cost' => $f_new_cost, ':player_id' => $eos_player_id));
		$affected = $query->rowCount();
		if(!$result || !$affected){
			echo '{"success" : 0, "msg" : "Insufficient cash."}';
			exit();
		}
	}

	// Generate firm color
	$fcolor_total_leftover = mt_rand(200, 1000); // Make colors lighter
	$fcolor_r = min($fcolor_total_leftover, mt_rand(0, 255));
	$fcolor_total_leftover = $fcolor_total_leftover - $fcolor_r;
	$fcolor_g = min($fcolor_total_leftover, mt_rand(0, 255));
	$fcolor_total_leftover = $fcolor_total_leftover - $fcolor_g;
	$fcolor_b = min($fcolor_total_leftover, mt_rand(0, 255));
	$fcolor = '#'.str_pad(dechex($fcolor_r), 2, '0', STR_PAD_LEFT).str_pad(dechex($fcolor_g), 2, '0', STR_PAD_LEFT).str_pad(dechex($fcolor_b), 2, '0', STR_PAD_LEFT);

	// Create company
	$sql = "INSERT INTO firms (name, color, quests_available, last_login, last_active) VALUES (?, '$fcolor', 0, NOW(), NOW())";
	$query = $db->prepare($sql);
	$result = $query->execute(array($name));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed. Code FC-246');
		echo json_encode($resp);
		exit();
	}
	$sql = "SELECT id FROM firms WHERE name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$new_firm_id = $query->fetchColumn();
	if(!$new_firm_id){
		$resp = array('success' => 0, 'msg' => 'DB failed. Code FC-255');
		echo json_encode($resp);
		exit();
	}
	$sql = "INSERT INTO firms_extended (id, is_public, ceo) VALUES ($new_firm_id, 0, $eos_player_id)";
	$db->query($sql);
	$sql = "UPDATE players SET fid = $new_firm_id WHERE id = $eos_player_id";
	$db->query($sql);

	// Insert into firm controls
	$sql = "INSERT INTO firms_positions (fid, pid, title, pay_flat, bonus_percent, next_pay_flat, next_bonus_percent, next_accepted, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) VALUES ($new_firm_id, $eos_player_id, 'Owner', 0, 0, 0, 0, 1, NOW(), '2222-01-01', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";
	$db->query($sql);

	$sql = "SELECT id FROM firms_positions WHERE fid = $new_firm_id AND pid = $eos_player_id ORDER BY id DESC";
	$fp_id = $db->query($sql)->fetchColumn();

	// Insert into logs
	$sql = "INSERT INTO log_management (id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) 
	SELECT id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM firms_positions WHERE firms_positions.id = $fp_id";
	$db->query($sql);

	$sql = "UPDATE players SET fid = $new_firm_id WHERE id = $eos_player_id";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'transfer_cash'){
	require_active_firm();

	$xfund = floor(filter_var($_POST['xfund'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	if(!$ctrl_admin || $eos_firm_is_public){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT name, cash, loan FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company is missing.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm["name"];
		$firm_cash = $firm["cash"];
		$firm_loan = $firm["loan"];
	}
	
	$sql = "SELECT player_cash FROM players WHERE id = $eos_player_id";
	$player_cash = $db->query($sql)->fetchColumn();

	if($firm_cash > $firm_loan){
		$min_xfund = $firm_loan - $firm_cash;
	}else{
		$min_xfund = 0;
	}	
	if($player_cash > 0){
		$max_xfund = $player_cash;
	}else{
		$max_xfund = 0;
	}
	if($xfund < $min_xfund){
		$resp = array('success' => 0, 'msg' => 'Please check your numbers to make sure they are valid.');
		echo json_encode($resp);
		exit();
	}
	if($xfund > $max_xfund){
		$resp = array('success' => 0, 'msg' => 'Please check your numbers to make sure they are valid.');
		echo json_encode($resp);
		exit();
	}
	if($xfund == 0){
		$resp = array('success' => 0, 'msg' => 'Sorry we don\'t do $0 transfers here.');
		echo json_encode($resp);
		exit();
	}else if($xfund > 0){
		$xfund_is_deposit = 1;
		$origination_account = 'personal account';
		$destination_account = 'corporate account';
		$fee = ceil(0.005 * $xfund);
		$xfund_effective = floor(0.995 * $xfund);
		$player_cash_new = $player_cash - $xfund;
		$firm_cash_new = $firm_cash + $xfund_effective;
	}else{
		$xfund_is_deposit = 0;
		$origination_account = 'corporate account';
		$destination_account = 'personal account';
		$xfund = 0 - $xfund;
		$fee = ceil(0.005 * $xfund);
		$xfund_effective = floor(0.995 * $xfund);
		$firm_cash_new = $firm_cash - $xfund;
		$player_cash_new = $player_cash + $xfund_effective;
	}
	// Deduct funds, write to log revenue for fid
	if($xfund_is_deposit){
		$query = $db->prepare("UPDATE players SET player_cash = player_cash - :cost WHERE id = :player_id AND player_cash >= :cost");
		$result = $query->execute(array(':cost' => $xfund, ':player_id' => $eos_player_id));
		$affected = $query->rowCount();
		if(!$result || !$affected){
			echo '{"success" : 0, "msg" : "Insufficient cash."}';
			exit();
		}
	}else{
		$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
		$result = $query->execute(array(':cost' => $xfund, ':firm_id' => $eos_firm_id));
		$affected = $query->rowCount();
		if(!$result || !$affected){
			echo '{"success" : 0, "msg" : "Insufficient cash."}';
			exit();
		}
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $xfund, 'Transfer', NOW())";
		$db->query($sql);
	}
	
	// Give funds, write to log revenue for fid
	if($xfund_is_deposit){
		$sql = "UPDATE firms SET cash = cash + $xfund_effective WHERE id = $eos_firm_id";
		$db->query($sql);
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 0, $xfund_effective, 'Transfer', NOW())";
		$db->query($sql);
	}else{
		$sql = "UPDATE players SET player_cash = player_cash + $xfund_effective WHERE id = $eos_player_id";
		$db->query($sql);
	}

	$msg = 'You have successfully transferred $'.number_format($xfund/100, 2, '.', ',').' from your '.$origination_account.' to your '.$destination_account.'. A transfer fee of $'.number_format($fee/100, 2, '.', ',').' was deducted from the transfer.';
	$resp = array('success' => 1, 'msg' => $msg, 'firm_cash_new' => $firm_cash_new, 'player_cash_new' => $player_cash_new);
	echo json_encode($resp);
	exit();
}
else if($action == 'obtain_loan'){
	require_active_firm();

	$xfund = floor(filter_var($_POST['xfund'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	if(!$ctrl_admin){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT id, name, cash, loan, networth, level, fame_level, max_bldg FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company is missing.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm["name"];
		$firm_cash = $firm["cash"];
		$firm_loan = $firm["loan"];
		$firm_networth = $firm["networth"];
	}
	$max_xfund = max(0, $firm_networth * 0.5 - $firm_loan);
	
	if($xfund <= 0){
		$resp = array('success' => 0, 'msg' => 'Please select an amount to borrow.');
		echo json_encode($resp);
		exit();
	}
	if($xfund > $max_xfund){
		$resp = array('success' => 0, 'msg' => 'After carefully reviewing your application, we regret to inform you that your request for loan cannot be approved.');
		echo json_encode($resp);
		exit();
	}
	
	if($xfund > 900000000 || $xfund > 0.05 * $firm_networth){
		// Appraise the company
		$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
		$firm_level_size = sizeof($firm_level_lowlimit);

		$firm_name = $firm['name'];

		$networth = 0;
		$appraised_cash = $firm["cash"];
		$networth += $appraised_cash;
		$appraised_loan = $firm["loan"];
		$networth -= $appraised_loan;
		$appraised_fame = floor(1000*pow(1.4,$firm["fame_level"])-1000);
		$networth += $appraised_fame;

		// Land Value, surprisingly, the equation is the square of pascal's trangle...
		$max_bldg = $firm["max_bldg"];
		$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
		$networth += $appraised_land;
		
		// Building Value, using size * cost
		$appraised_building = 0;
		
		$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $eos_firm_id";
		$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
		
		$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $eos_firm_id";
		$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
		
		$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $eos_firm_id";
		$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
		
		$networth += $appraised_building;
		
		// Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
		$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $eos_firm_id";
		$appraised_research = floor($db->query($sql)->fetchColumn());
		$networth += $appraised_research;
		
		// Warehouse value, using value, pidq, pidn
		$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $eos_firm_id";
		$appraised_wh = floor($db->query($sql)->fetchColumn());
		$networth += $appraised_wh;

		// Market value, using value, pidq, pidn
		$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $eos_firm_id";
		$appraised_market = floor($db->query($sql)->fetchColumn());
		$networth += $appraised_market;
		
		$networth = floor($networth);
		
		$max_xfund = max(0, $networth * 0.5 - $appraised_loan);

		if($xfund > $max_xfund){
			$resp = array('success' => 0, 'msg' => 'After carefully reviewing your application, we regret to inform you that your request for loan cannot be approved.');
			echo json_encode($resp);
			exit();
		}
	}
	
	// Add funds
	$origination_fee = floor($xfund * 0.02);
	$actual_loan_received = $xfund - $origination_fee;
	$sql = "UPDATE firms SET cash = cash + $actual_loan_received, loan = loan + $xfund WHERE id = $eos_firm_id";
	$db->query($sql);
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $origination_fee, 'Interest', NOW())";
	$db->query($sql);
	$sql = "INSERT INTO firm_news (fid, body, date_created) VALUES ($eos_firm_id, 'Your company has successfully taken out a loan in the amount of $".number_format($xfund/100, 2, '.', ',')."', NOW())";
	$db->query($sql);

	$firm_cash_new = $firm_cash + $actual_loan_received;
	$firm_loan_new = $firm_loan + $xfund;
	
	$msg = 'Your company has successfully taken out a loan in the amount of $'.number_format($xfund/100, 2, '.', ',').'.';
	$resp = array('success' => 1, 'msg' => $msg, 'firm_cash_new' => $firm_cash_new, 'firm_loan_new' => $firm_loan_new);
	echo json_encode($resp);
	exit();
}
else if($action == 'repay_loan'){
	require_active_firm();

	$xfund = floor(filter_var($_POST['xfund'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	if(!$ctrl_admin){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT name, cash, loan FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company is missing.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm["name"];
		$firm_cash = $firm["cash"];
		$firm_loan = $firm["loan"];
	}
	
	if($xfund <= 0){
		$resp = array('success' => 0, 'msg' => 'Please select an amount to repay.');
		echo json_encode($resp);
		exit();
	}
	if($xfund > $firm_cash){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}
	
	// Deduct funds, DO NOT write to log revenue for fid for tax purposes
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost, loan = loan - :cost WHERE id = :firm_id AND cash >= :cost AND loan >= :cost");
	$result = $query->execute(array(':cost' => $xfund, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		echo '{"success" : 0, "msg" : "Payment failed. Are you trying to pay more than what you owe?"}';
		exit();
	}
	// $sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $xfund, 'Loan Repayment', NOW())";
	// $db->query($sql);
	
	// Insert News
	$sql = "INSERT INTO firm_news (fid, body, date_created) VALUES ($eos_firm_id, 'Your company has successfully paid off $".number_format($xfund/100, 2, '.', ',')." of your outstanding loan.', NOW())";
	$db->query($sql);

	$firm_cash_new = $firm_cash - $xfund;
	$firm_loan_new = $firm_loan - $xfund;
	
	$msg = 'Your company has successfully paid off $'.number_format($xfund/100, 2, '.', ',').' of your outstanding loan.';
	if(!$firm_loan_new) $msg .= '<br /><br /><b>Congratulations! The loan is now paid in full.</b>';
	$resp = array('success' => 1, 'msg' => $msg, 'firm_cash_new' => $firm_cash_new, 'firm_loan_new' => $firm_loan_new);
	echo json_encode($resp);
	exit();
}
else if($action == 'set_self_pay'){
	require_active_firm();

	$salary = floor(filter_var($_POST['salary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	$bonus = filter_var($_POST['bonus'], FILTER_SANITIZE_NUMBER_INT)/100;
	
	$sql = "SELECT firms.name, firms.networth, firms.cash, firms_positions.pay_flat, firms_positions.bonus_percent FROM firms LEFT JOIN firms_positions ON firms.id = firms_positions.fid WHERE firms.id = $eos_firm_id AND firms_positions.pid = $eos_player_id";
	$paydata = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($paydata)){
		$resp = array('success' => 0, 'msg' => 'Company cannot be found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $paydata["name"];
		$firm_cash = $paydata["cash"];
	}
	$sql = "SELECT SUM(bonus_percent) FROM firms_positions WHERE fid = $eos_firm_id";
	$bonus_percent_spent = $db->query($sql)->fetchColumn();

	if($ctrl_admin){
		$min_salary = 0;
		$max_salary = max(100000000, floor($paydata["networth"] / 50));
		$min_bonus = 0;
		$max_bonus = max(0, min(20, 80 - $bonus_percent_spent));
	}else{
		$min_salary = 0;
		$max_salary = 0;
		$min_bonus = 0;
		$max_bonus = 0;
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

	// Set ceo pay
	$sql = "UPDATE firms_positions SET pay_flat = '$salary', bonus_percent = '$bonus' WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$result = $db->query($sql);
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	// Notify shareholders
	$sql = "SELECT firms_extended.is_public, firm_stock.symbol FROM firms_extended LEFT JOIN firm_stock ON firms_extended.id = firm_stock.fid WHERE firms_extended.id = $eos_firm_id";
	$firm_stock_info = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($firm_stock_info['is_public']){
		$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, ?, NOW() FROM player_stock WHERE fid = ".$eos_firm_id);
		$query->execute(array('Dear Investor, <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> (<a href="/eos/stock-details.php?ss='.$firm_stock_info['symbol'].'">'.$firm_stock_info['symbol']."</a>) has changed its Chairman's salary to $".number_format($salary/100,0,'.',',')." and bonus to ".$bonus."% of the company's net gains."));
	}
	
	$resp = array('success' => 1, 'salary' => $salary/100, 'bonus' => $bonus);
	echo json_encode($resp);
	exit();
}
else if($action == 'request_a_raise'){
	require_active_firm();

	$salary = floor(filter_var($_POST['salary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	$bonus = filter_var($_POST['bonus'], FILTER_SANITIZE_NUMBER_INT)/100;
	
	$sql = "SELECT firms.name, firms.networth, firms.cash, firms_positions.pay_flat, firms_positions.bonus_percent FROM firms LEFT JOIN firms_positions ON firms.id = firms_positions.fid WHERE firms.id = $eos_firm_id AND firms_positions.pid = $eos_player_id";
	$paydata = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($paydata)){
		$resp = array('success' => 0, 'msg' => 'Company cannot be found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $paydata["name"];
		$firm_cash = $paydata["cash"];
	}
	$sql = "SELECT SUM(bonus_percent) FROM firms_positions WHERE fid = $eos_firm_id";
	$bonus_percent_spent = $db->query($sql)->fetchColumn();

	$min_salary = $paydata["pay_flat"];
	$max_salary = $paydata["pay_flat"] + max(10000000, floor(0.2 * $paydata["pay_flat"]));
	$min_bonus = 0;
	$max_bonus = max($paydata["bonus_percent"], min(20, 80 - $bonus_percent_spent));

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

	// Set next pay and remove acceptance
	$sql = "UPDATE firms_positions SET next_pay_flat = '$salary', next_bonus_percent = '$bonus', next_accepted = 0 WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$result = $db->query($sql);
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	// Notify chairman
	$sql = "SELECT pid, title FROM firms_positions WHERE fid = $eos_firm_id AND ctrl_admin";
	$admins = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($admins)){
		$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (:pid, :body, NOW())");
		foreach($admins as $admin){
			$query->execute(array(':pid' => $admin['pid'], ':body' => 'Dear '.$admin['title'].', <a href="/eos/player/'.$eos_player_id.'">'.$eos_player_name.'</a> from your company <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> has requested a raise. Please go to <a href="city.php?view_type=hq"><b>City->HQ->Employee Roster</b></a> to review and approve the new salary and bonus.'));
		}
	}

	$resp = array('success' => 1, 'salary' => $salary/100, 'bonus' => $bonus);
	echo json_encode($resp);
	exit();
}
else if($action == 'order_appraisal'){
	require_active_firm();

	if(!$ctrl_admin && ($eos_player_stock_percent < 10 || !$eos_player_is_msh)){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to perform this action.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT name, cash, level FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$appraisal_cost = 1000000 * pow($firm['level'], 2);
	
	if($firm['cash'] < $appraisal_cost){
		$resp = array('success' => 0, 'msg' => 'Failed to start appraisal. Insufficient cash.');
		echo json_encode($resp);
		exit();
	}
	if($ctrl_leftover_allowance < $appraisal_cost){
		$resp = array('success' => 0, 'msg' => 'Failed to start appraisal. Spending limit reached.');
		echo json_encode($resp);
		exit();
	}

	if($appraisal_cost > 0){
		// Deduct $ from firm
		$sql = "UPDATE firms SET cash = cash - $appraisal_cost WHERE id = $eos_firm_id AND cash >= $appraisal_cost";
		$affected = $db->query($sql)->rowCount();
		if(!$affected){
			$resp = array('success' => 0, 'msg' => 'Failed to start appraisal. Insufficient cash.');
			echo json_encode($resp);
			exit();
		}
		
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ('$eos_firm_id', 1, $appraisal_cost, 'Misc', NOW())";
		$db->query($sql);
	}
	
	// Firm Networth = cash + $10*1.4^fame_level + 100% of land value + 100% of buildings value + about 50% of research value + 50% of warehouse quality adjusted value
	$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
	$firm_level_size = sizeof($firm_level_lowlimit);
	$sql = "SELECT id, cash, loan, level, fame_level, max_bldg FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Failed to start appraisal. Company not found.');
		echo json_encode($resp);
		exit();
	}
	$networth = 0;
	$appraised_cash = $firm["cash"];
	$networth += $appraised_cash;
	$appraised_loan = $firm["loan"];
	$networth -= $appraised_loan;
	$appraised_fame = floor(1000*pow(1.4,$firm["fame_level"])-1000);
	$networth += $appraised_fame;

	// Land Value, surprisingly, the equation is the square of pascal's trangle...
	$max_bldg = $firm["max_bldg"];
	$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
	$networth += $appraised_land;
	
	// Building Value, using size * cost
	$appraised_building = 0;
	
	$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$networth += $appraised_building;
	
	// Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
	$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $eos_firm_id";
	$appraised_research = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_research;
	
	// Warehouse value, using value, pidq, pidn
	$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $eos_firm_id";
	$appraised_wh = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_wh;

	// Market value, using value, pidq, pidn
	$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $eos_firm_id";
	$appraised_market = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_market;
	
	$networth = floor($networth);
	$new_level = $firm["level"];
	$next_level = $new_level + 1;
	while(($networth >= $firm_level_lowlimit[$next_level]) && ($next_level < $firm_level_size)){
		$new_level = $next_level;
		$next_level += 1;
	}
	
	// Do some updates
	if($new_level != $firm["level"]){
		$sql = "UPDATE firms SET networth = $networth, level = $new_level WHERE id = $eos_firm_id";
		$db->query($sql);
	}else{
		$sql = "UPDATE firms SET networth = $networth WHERE id = $eos_firm_id";
		$db->query($sql);
	}
	$sql = "UPDATE firms_extended SET inventory = $appraised_wh + $appraised_market, property = $appraised_land + $appraised_building, intangible = $appraised_fame + $appraised_research WHERE id = $eos_firm_id";
	$db->query($sql);
	$appraised_networth = $networth;
	
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES ($eos_firm_id, :news, NOW())");
	$query->execute(array(':news' => '<h3>Appraisal Results</h3><span style="display:inline-block;width:150px;">Cash: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_cash/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Loan: </span>$<span style="display:inline-block;width:200px;text-align:right;color:#ff0000;">-'.number_format($appraised_loan/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Fame: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_fame/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Land Value: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_land/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Building Value: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_building/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Research Value: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_research/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Warehouse Inven.: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_wh/100, 2, '.', ',').'</span><br /><span style="display:inline-block;width:150px;">Market Inven.: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($appraised_market/100, 2, '.', ',').'</span><br /><br /><span style="display:inline-block;width:150px;">Total Networth.: </span>$<span style="display:inline-block;width:200px;text-align:right;">'.number_format($networth/100, 2, '.', ',').'</span>'));

	$resp = array('success' => 1, 'nw' => $networth, 'cash' => $appraised_cash, 'loan' => $appraised_loan, 'fame' => $appraised_fame, 'land' => $appraised_land, 'bldg' => $appraised_building, 'res' => $appraised_research, 'wh' => $appraised_wh, 'market' => $appraised_market);
	echo json_encode($resp);
	exit();
}
else if($action == 'toggle_next_acceptance'){
	require_active_firm();

	$target_pid = filter_var($_POST['player_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$ctrl_hr_hire && !$ctrl_admin){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to do this.');
		echo json_encode($resp);
		exit();
	}

	// Set next pay and remove acceptance
	$sql = "SELECT id, next_accepted FROM firms_positions WHERE fid = $eos_firm_id AND pid = $target_pid";
	$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	
	if(empty($position)){
		$resp = array('success' => 0, 'msg' => 'Employee file is missing, did somebody just fire him/her?');
		echo json_encode($resp);
		exit();
	}
	
	$fp_id = $position['id'];
	$next_accepted = 1 - $position['next_accepted'];
	$sql = "UPDATE firms_positions SET next_accepted = $next_accepted WHERE id = $fp_id";
	$db->query($sql);

	if($next_accepted == 1){
		$resp = array('success' => 1, 'title' => 'Next Term Accepted');
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 1, 'title' => 'Next Term Denied');
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'fire_employee'){
	require_active_firm();

	$target_pid = filter_var($_POST['player_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$ctrl_hr_fire && !$ctrl_admin){
		$resp = array('success' => 0, 'msg' => 'You are not authorized to fire employees.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT starttime FROM firms_positions WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$hr_starttime = strtotime($db->query($sql)->fetchColumn());
	
	$sql = "SELECT firms_positions.id, firms_positions.title, firms_positions.pid, firms_positions.ctrl_admin, firms_positions.ctrl_hr_hire, firms_positions.ctrl_hr_fire, firms_positions.starttime, firms_positions.pay_flat, players.id AS player_id, players.player_name, players.last_active, firms.name AS firm_name FROM firms_positions LEFT JOIN players ON firms_positions.pid = players.id LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $eos_firm_id AND firms_positions.pid = '$target_pid'";
	$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($position)){
		$resp = array('success' => 0, 'msg' => 'Employee not found.');
		echo json_encode($resp);
		exit();
	}

	if($position['ctrl_admin']){
		$resp = array('success' => 0, 'msg' => 'Are you out of your mind? This person can fire YOU!');
		echo json_encode($resp);
		exit();
	}

	if(!$ctrl_admin && ($position['ctrl_hr_fire'] && strtotime($position['starttime']) <= $hr_starttime && (strtotime($position['last_active']) + 1209600) > time())){
		$resp = array('success' => 0, 'msg' => 'Cannot fire '.$position['player_name'].' due to lack of a just cause. This employee has more seniority than you and is still active.');
		echo json_encode($resp);
		exit();
	}

	$sql = "UPDATE log_management SET endtime = NOW() WHERE id = ".$position['id'];
	$db->query($sql);
	$sql = "DELETE FROM firms_positions WHERE id = ".$position['id'];
	$db->query($sql);
	$sql = "UPDATE players SET fid = 0 WHERE id = $target_pid AND fid = $eos_firm_id";
	$db->query($sql);;

	if($position['pay_flat'] > 1000000000){
		$sql = "INSERT INTO player_news (pid, body, date_created) SELECT pid, :body, NOW() FROM player_stock WHERE fid = $eos_firm_id";
		$query = $db->prepare($sql);
		$query->execute(array(':body' => 'Dear Investor, '.$position['player_name'].' is no longer the '.$position['title'].' of '.$position['firm_name'].'.'));
	}
	$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($target_pid, :body, NOW())";
	$query = $db->prepare($sql);
	$query->execute(array(':body' => 'Your job with '.$position['firm_name'].' was terminated early. The notice was delivered to you by '.$eos_player_name));
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'quit_job'){
	require_active_firm();

	$target_pid = $eos_player_id;
	
	$sql = "SELECT firms_positions.id, firms_positions.title, firms_positions.pid, firms_positions.ctrl_admin, firms_positions.ctrl_hr_hire, firms_positions.ctrl_hr_fire, firms_positions.starttime, firms_positions.pay_flat, players.id AS player_id, players.player_name, players.last_active, firms.name AS firm_name FROM firms_positions LEFT JOIN players ON firms_positions.pid = players.id LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $eos_firm_id AND firms_positions.pid = '$target_pid'";
	$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($position)){
		$resp = array('success' => 0, 'msg' => 'Employee not found.');
		echo json_encode($resp);
		exit();
	}

	if($position['ctrl_admin']){
		$resp = array('success' => 0, 'msg' => 'Please don\'t quit, this company can\'t operate without you!');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "UPDATE log_management SET endtime = NOW() WHERE id = ".$position['id'];
	$db->query($sql);
	$sql = "DELETE FROM firms_positions WHERE id = ".$position['id'];
	$db->query($sql);
	$sql = "UPDATE players SET fid = 0 WHERE id = $target_pid AND fid = $eos_firm_id";
	$db->query($sql);;
	
	if($position['pay_flat'] > 1000000000){
		$sql = "INSERT INTO player_news (pid, body, date_created) SELECT pid, :body, NOW() FROM player_stock WHERE fid = $eos_firm_id";
		$query = $db->prepare($sql);
		$query->execute(array(':body' => 'Dear Investor, '.$position['player_name'].' is no longer the '.$position['title'].' of '.$position['firm_name'].'.'));
	}
	$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($target_pid, :body, NOW())";
	$query = $db->prepare($sql);
	$query->execute(array(':body' => 'You quit your job at '.$position['firm_name'].'.'));
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'sell_firm'){
	require_active_firm();

	$referrer = $_SERVER['HTTP_REFERER'];
	if(strpos($referrer, '?')) $referrer = substr($referrer, 0, strpos($referrer, '?'));
	$referrer = substr($referrer, strrpos($referrer, "/") + 1);
	if($referrer != "settings.php" && $referrer != "city.php"){
		$resp = array('success' => 0, 'msg' => 'Selling company failed. Invalid referrer.');
		echo json_encode($resp);
		exit();
	}
	if(!isset($_SESSION['f_sell_time']) || time() - $_SESSION['f_sell_time'] > 60){
		$resp = array('success' => 0, 'msg' => 'Selling company failed. You took too long, please close the sell company dialog and re-open it to try again.');
		echo json_encode($resp);
		exit();
	}
	unset($_SESSION['f_sell_time']);
	if($eos_firm_is_public){
		$resp = array('success' => 0, 'msg' => 'You cannot sell a publicly traded company in this way.');
		echo json_encode($resp);
		exit();
	}

	// Check for flagged transactions
	$sql = "SELECT COUNT(*) AS cnt FROM log_market_prod WHERE hide = 0 AND (sfid = $eos_firm_id OR bfid = $eos_firm_id)";
	$flagged_count = $db->query($sql)->fetchColumn();
	if($flagged_count){
		$resp = array('success' => 0, 'msg' => 'You cannot sell this company because some of its B2B transactions are under investigation.');
		echo json_encode($resp);
		exit();
	}
	
	// Firm Networth = cash + $10*1.4^fame_level + 100% of land value + 100% of buildings value + about 50% of research value + 50% of warehouse quality adjusted value
	$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
	$firm_level_size = sizeof($firm_level_lowlimit);
	$sql = "SELECT id, name, cash, loan, level, fame_level, max_bldg FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Failed to start appraisal. Company not found.');
		echo json_encode($resp);
		exit();
	}
	$firm_name = $firm['name'];

	$networth = 0;
	$appraised_cash = $firm["cash"];
	$networth += $appraised_cash;
	$appraised_loan = $firm["loan"];
	$networth -= $appraised_loan;
	$appraised_fame = floor(1000*pow(1.4,$firm["fame_level"])-1000);
	$networth += $appraised_fame;

	// Land Value, surprisingly, the equation is the square of pascal's trangle...
	$max_bldg = $firm["max_bldg"];
	$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
	$networth += $appraised_land;
	
	// Building Value, using size * cost
	$appraised_building = 0;
	
	$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$networth += $appraised_building;
	
	// Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
	$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $eos_firm_id";
	$appraised_research = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_research;
	
	// Warehouse value, using value, pidq, pidn
	$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $eos_firm_id";
	$appraised_wh = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_wh;

	// Market value, using value, pidq, pidn
	$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $eos_firm_id";
	$appraised_market = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_market;
	
	$networth = floor($networth);
	$f_sell_price = floor(0.95 * $networth);
	
	if($f_sell_price <= 0){
		$resp = array('success' => 0, 'msg' => 'Nobody wants to buy your company. Does it have negative equity? (If this is your only company, consider using the restart option in settings)');
		echo json_encode($resp);
		exit();
	}
	
	// Delete company
	$sql = "DELETE FROM firm_fact WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_rnd WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_store WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_news WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_quest WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_tech WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_wh WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM history_firms WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM market_prod WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM market_requests WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM log_market_prod WHERE sfid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM log_market_prod WHERE bfid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM queue_build WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM queue_prod WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM queue_res WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE es_positions.*, es_applications.* FROM es_positions LEFT JOIN es_applications ON es_positions.id = es_applications.esp_id WHERE es_positions.fid = $eos_firm_id";
	$db->query($sql);
	$sql = "INSERT INTO player_news (pid, body, date_created) SELECT firms_positions.pid, CONCAT('Dear ',firms_positions.title,', your job with ',firms.name,' has ended because the company no longer exists.'), NOW() FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firms_positions WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "UPDATE log_management SET endtime = NOW() WHERE endtime > NOW() AND fid = $eos_firm_id";
	$db->query($sql);
	$sql = "INSERT INTO log_firms_sold (pid, fid, firm_name) SELECT $eos_player_id, firms.id, firms.name FROM firms WHERE id = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firms_extended WHERE id = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firms WHERE id = $eos_firm_id";
	$db->query($sql);
	$sql = "UPDATE players SET fid = 0 WHERE fid = '$eos_firm_id'";
	$db->query($sql);
	
	// Give cash
	$sql = "UPDATE players SET player_cash = player_cash + $f_sell_price WHERE id = $eos_player_id";
	$db->query($sql);

	$resp = array('success' => 1, 'firmName' => $firm_name, 'soldCash' => $f_sell_price);
	echo json_encode($resp);
	exit();
}
?>