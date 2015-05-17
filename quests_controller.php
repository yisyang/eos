<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/functions.php'; ?>
<?php require 'include/quest_types.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'validate_quest'){
	$fqid = filter_var($_POST['fqid'], FILTER_SANITIZE_NUMBER_INT);
	$timenow = time();

	// Global vars for validate existing quests
	$quest_type_image = "";
	$quest_objective_message = "";
	$quest_progress_message = "";
	$quest_endtime = "";
	$reward_cash = 0;
	$reward_fame = 0;
	$quest_validated = 0;
	$quest_completed = 0;
	$quest_allow_supply = 0;

	// Validate on-going quests
	$ongoing_quests = array();
	$sql = "SELECT COUNT(*) AS cnt FROM firm_quest WHERE id = $fqid AND fid = $eos_firm_id AND !completed AND !failed";
	$ongoing_quest_found = $db->query($sql)->fetchColumn();
	if(!$ongoing_quest_found){
		$resp = array('success' => 0, 'msg' => 'Quest not found.');
		echo json_encode($resp);
		exit();
	}
	if(validate_quest($fqid)){
		$ongoing_quest = array('id' => $fqid, 'pid' => $quest_pid, 'quest_type_image' => $quest_type_image, 'quest_objective_message' => $quest_objective_message, 'quest_progress_msg' => '<font color="#008800"><b>Just Completed!</b></font>', 'quest_endtime' => $quest_endtime, 'reward_cash' => $reward_cash, 'reward_fame' => $reward_fame, 'quest_allow_supply' => 0);
	}else if($quest_validated){
		$ongoing_quest = array('id' => $fqid, 'pid' => $quest_pid, 'quest_type_image' => $quest_type_image, 'quest_objective_message' => $quest_objective_message, 'quest_progress_msg' => $quest_progress_message, 'quest_endtime' => $quest_endtime, 'reward_cash' => $reward_cash, 'reward_fame' => $reward_fame, 'quest_allow_supply' => $quest_allow_supply);
	}else{
		$ongoing_quest = array('id' => $fqid, 'pid' => $quest_pid, 'quest_type_image' => 'menu-quests', 'quest_objective_message' => 'Error: Quest not found.', 'quest_progress_msg' => 'N/A', 'quest_endtime' => time(), 'reward_cash' => 0, 'reward_fame' => 0, 'quest_allow_supply' => 0);
	}

	$msg = '<td width="120" style="border-right: 0;background-color:#ffd553;">';
	$msg .= '<img src="images/'.$ongoing_quest['quest_type_image'].'.gif" />';
	$msg .= '</td>';
	$msg .= '<td class="quests_table_content_td">';
	$msg .= 'Objective: '.$ongoing_quest['quest_objective_message'];
	if($ongoing_quest['quest_allow_supply'] && $ongoing_quest['quest_endtime'] >= $timenow){
		$sql = "SELECT COUNT(*) FROM market_prod WHERE pid = ".$ongoing_quest['pid'];
		$pid_available_b2b = $db->query($sql)->fetchColumn();
		$sql = "SELECT COUNT(*) FROM firm_wh WHERE fid = $eos_firm_id AND pid = ".$ongoing_quest['pid']." AND pidn > 0";
		$pid_available_wh = $db->query($sql)->fetchColumn();
		$msg .= ' 
		<a class="jqDialog" href="quests-prod-supply-confirm.php" params="fqid='.$ongoing_quest['id'].'" title="Supply Quest Items"><input type="button" class="bigger_input" value="Fulfill"'.($pid_available_wh ? '' : ' disabled="disabled"').' /></a> 
		<a href="/eos/market.php?view_type=prod&view_type_id='.$ongoing_quest['pid'].'" title="Purchase on B2B"><input type="button" class="bigger_input" value="B2B"'.($pid_available_b2b ? '' : ' disabled="disabled"').' /></a> 
		<a class="jqDialog" href="/eos/market-add-request.php?no_redirect=1&pid='.$ongoing_quest['pid'].'" title="Add a Purchase Request"><input type="button" class="bigger_input" value="REQ" /></a>';
	}
	$msg .= '<br /><br />';
	$msg .= 'Progress: '.$ongoing_quest['quest_progress_msg'].'<br /><br />';
	$msg .= 'Reward: <img src="images/money.gif" title="Cash" /> $'.number_format($ongoing_quest['reward_cash']/100, 2, '.', ',').' <img src="images/fame.gif" title="Fame" /> '.number_format($ongoing_quest['reward_fame'],0,'.',',').'<br /><br />';
	if($ongoing_quest['quest_endtime'] >= $timenow){
		$msg .= 'Time Left: '.sec2hms($ongoing_quest['quest_endtime'] - $timenow).' (Expires on '.date("F j, Y, g:i A", $ongoing_quest['quest_endtime']).')';
	}else{
		$msg .= 'Expired on: '.date("F j, Y, g:i A".$ongoing_quest['quest_endtime']);
	}
	$msg .= '</td>';

	$resp = array('success' => 1, 'msg' => $msg, 'completed' => $quest_completed);
	echo json_encode($resp);
	exit();
}
else if($action == 'supply_prod'){
	$fqid = filter_var($_POST['fqid'], FILTER_SANITIZE_NUMBER_INT);
	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);
	$snum = filter_var($_POST['snum'], FILTER_SANITIZE_NUMBER_INT);
	if(!$fqid || !$wh_id){
		$resp = array('success' => 0, 'msg' => 'Missing quest id or warehouse id.');
		echo json_encode($resp);
		exit();
	}
	if($snum < 1){
		$resp = array('success' => 0, 'msg' => 'Please specify the quantity of products that you would like to deliver.');
		echo json_encode($resp);
		exit();
	}

	// First check fqid belongs to eos_firm_id
	$sql = "SELECT * FROM firm_quest LEFT JOIN list_quest ON firm_quest.quest_id = list_quest.id WHERE firm_quest.id = $fqid AND firm_quest.fid = $eos_firm_id AND !firm_quest.completed AND !firm_quest.failed";
	$quest = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($quest)){
		fbox_breakout('quests.php');
	}else{
		$q_type = $quest["type"];
		$gen_target_id = $quest["gen_target_id"];
		$gen_target_n = $quest["gen_target_n"];
		$q_prod_q = $quest["q"];
	}

	// Make sure $q_type is between 1 to 9 (Product supply quest)
	if($q_type < 1 || $q_type > 9){
		fbox_breakout('quests.php');
	}

	// Collect prod info
	$sql = "SELECT * FROM list_prod WHERE id = '$gen_target_id'";
	$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$ipid_name = $result['name'];
	if($result['has_icon']){
		$ipid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid_name));
	}else{
		$ipid_filename = "no-icon";
	}

	// Finally check if ipid exists in the firm_wh, much easier now with only 1 quality
	if($q_prod_q){
		$sql = "SELECT * FROM firm_wh WHERE id = '$wh_id' AND fid = $eos_firm_id AND pidn > 0 AND pidq >= '$q_prod_q' LIMIT 0, 1";
	}else{
		$sql = "SELECT * FROM firm_wh WHERE id = '$wh_id' AND fid = $eos_firm_id AND pidn > 0 LIMIT 0, 1";
	}

	$wh_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(!empty($wh_prod)){
		$wh_id = $wh_prod['id'];
		$wh_pidq = $wh_prod['pidq'];
		$wh_pidn = $wh_prod['pidn'];
	}

	if($snum > $wh_pidn){
		$resp = array('success' => 0, 'msg' => 'Please specify the quantity of products that you would like to deliver.');
		echo json_encode($resp);
		exit();
	}
	if($snum > $gen_target_n) $snum = $gen_target_n;

	// Deduct ipid
	$wh_pidn_leftover = $wh_pidn - $snum;
	if($wh_pidn_leftover >= 1){
		$sql = "UPDATE firm_wh SET pidn = $wh_pidn_leftover WHERE id = $wh_id";
	}else{
		$sql = "UPDATE firm_wh SET pidn = 0, pidpartialsale = 0 WHERE id = $wh_id";
	}
	$result = $db->query($sql);
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	// Update quest
	$gen_target_n_leftover = $gen_target_n - $snum;
	if($gen_target_n_leftover){
		$sql = "UPDATE firm_quest SET gen_target_n = $gen_target_n_leftover WHERE id = $fqid";
	}else{
		$sql = "UPDATE firm_quest SET gen_target_n = 0 WHERE id = $fqid";
	}
	$result = $db->query($sql);
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	$msg = '<h3>Shipment Sent</h3>';
	$msg .= 'You have sent '.$snum.' units of <img style="vertical-align:middle;" src="/eos/images/prod/'.$ipid_filename.'.gif" alt="'.$ipid_name.'" title="'.$ipid_name.'" />.<br /><br />';
	if($gen_target_n_leftover){
		$msg .= $gen_target_n_leftover.' more units are needed to complete this quest.';
	}else{
		$msg .= 'Congratulations, this quest has been completed!';
	}

	$resp = array('success' => 1, 'msg' => $msg);
	echo json_encode($resp);
	exit();
}
?>