<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_type = filter_var($_POST['bldg_type'], FILTER_SANITIZE_STRING);
$bldg_type_id = filter_var($_POST['bldg_type_id'], FILTER_SANITIZE_NUMBER_INT);
$bldg_slot = filter_var($_POST['slot'], FILTER_SANITIZE_NUMBER_INT);
if(!$bldg_type || !$bldg_type_id || !$bldg_slot || $bldg_slot < 1){
	fbox_breakout('buildings.php');
}

if($bldg_type == 'fact'){
	$ctrl_build = $ctrl_fact_build;
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_list_info = $db->prepare("SELECT * FROM list_fact WHERE id = :building_type_id");
	$query_insert_new_bldg = $db->prepare("INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) VALUES (:eos_firm_id, :bldg_type_id, :generic_name, 10, :bldg_slot)");
	$query_get_new_bldg_id = $db->prepare("SELECT id FROM firm_fact WHERE fid = :eos_firm_id AND slot = :bldg_slot");
}else if($bldg_type == 'store'){
	$ctrl_build = $ctrl_store_build;
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_list_info = $db->prepare("SELECT * FROM list_store WHERE id = :building_type_id");
	$query_insert_new_bldg = $db->prepare("INSERT INTO firm_store (fid, store_id, store_name, size, slot) VALUES (:eos_firm_id, :bldg_type_id, :generic_name, 10, :bldg_slot)");
	$query_get_new_bldg_id = $db->prepare("SELECT id FROM firm_store WHERE fid = :eos_firm_id AND slot = :bldg_slot");
}else if($bldg_type == 'rnd'){
	$ctrl_build = $ctrl_rnd_build;
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_list_info = $db->prepare("SELECT * FROM list_rnd WHERE id = :building_type_id");
	$query_insert_new_bldg = $db->prepare("INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) VALUES (:eos_firm_id, :bldg_type_id, :generic_name, 10, :bldg_slot)");
	$query_get_new_bldg_id = $db->prepare("SELECT id FROM firm_rnd WHERE fid = :eos_firm_id AND slot = :bldg_slot");
}else{
	fbox_breakout('buildings.php');
}
if(!$ctrl_build){
	fbox_breakout('buildings.php');
}

// Check if there is already a building on the given slot
$query = $db->prepare("SELECT COUNT(*) AS cnt, building_type FROM queue_build WHERE fid = ? AND building_slot = ?");
$query->execute(array($eos_firm_id, $bldg_slot));
$result = $query->fetch(PDO::FETCH_ASSOC);
if($result['cnt']){
	fbox_redirect('bldg-expand-status.php?type='.$result['building_type'].'&slot='.$bldg_slot);
}
$sql = "(SELECT id, 'fact' AS bldg_type FROM firm_fact WHERE fid = :eos_firm_id AND slot = :slot) UNION (SELECT id, 'store' AS bldg_type FROM firm_store WHERE fid = :eos_firm_id AND slot = :slot) UNION (SELECT id, 'rnd' AS bldg_type FROM firm_rnd WHERE fid = :eos_firm_id AND slot = :slot)";
$query_get_bldg_id = $db->prepare($sql);
$query_get_bldg_id->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $bldg_slot));
$firm_bldg = $query_get_bldg_id->fetch(PDO::FETCH_ASSOC);
if(!empty($firm_bldg)){
	$bldg_id = $firm_bldg['id'];
	fbox_redirect($bldg_activity_url.$bldg_id);
}

// Check firm cash, and find out the player's max buildings 
$query = $db->prepare("SELECT cash, max_bldg FROM firms WHERE id = ?");
$query->execute(array($eos_firm_id));
$firm = $query->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];
if($firm['max_bldg'] < $bldg_slot){
	fbox_breakout('buildings.php');
}

// Initialize building
$query_get_bldg_list_info->execute(array(':building_type_id' => $bldg_type_id));
$list_bldg_info = $query_get_bldg_list_info->fetch(PDO::FETCH_ASSOC);
if(empty($list_bldg_info)){
	echo 'Error encountered, please report to admin. Error code BBS-061.';
	exit();
}
$bldg_firstcost = $list_bldg_info["firstcost"];
// $bldg_firsttimecost = $list_bldg_info["firsttimecost"];
$bldg_firsttimecost = 10;
$generic_name = $list_bldg_info["name"];
if($list_bldg_info["has_image"]){
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($generic_name));
}else{
	$filename = "no-image";
}

// Confirm that the firm has enough $$
if($bldg_firstcost < 0 || $firm_cash < $bldg_firstcost){
	fbox_redirect("bldg-build.php?slot=$bldg_slot", 'Insufficient cash, redirecting...');
}
if($ctrl_leftover_allowance < $bldg_firstcost){
	fbox_redirect("bldg-build.php?slot=$bldg_slot", 'Daily spending limit reached, redirecting...');
}

// Deduct $ from firm
$sql = "UPDATE firms SET cash = cash - $bldg_firstcost WHERE id = $eos_firm_id AND cash >= $bldg_firstcost";
$affected = $db->query($sql)->rowCount();
if(!$affected){
	fbox_redirect("bldg-build.php?slot=$bldg_slot", 'Insufficient cash, redirecting...');
}
$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ('$eos_firm_id', 1, '$bldg_firstcost', 'Construction', NOW())";
$db->query($sql);
$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $bldg_firstcost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
$db->query($sql);
$ctrl_leftover_allowance = ($ctrl_daily_allowance == -1) ? -1 : ($ctrl_leftover_allowance - $bldg_firstcost);

// Initialize player level
$sql = "SELECT player_level FROM players WHERE id = '$eos_player_id'";
$player_level = $db->query($sql)->fetchColumn();
if($player_level < 5){
	// Instant Completion
	$query_insert_new_bldg->execute(array(':eos_firm_id' => $eos_firm_id, ':bldg_type_id' => $bldg_type_id, ':generic_name' => $generic_name, ':bldg_slot' => $bldg_slot));
	$query_get_new_bldg_id->execute(array(':eos_firm_id' => $eos_firm_id, ':bldg_slot' => $bldg_slot));
	$bldg_id = $query_get_new_bldg_id->fetchColumn();
}else{
	// Start building
	$starttime = time();
	$endtime = $starttime + $bldg_firsttimecost;
	$query_insert_new_queue = $db->prepare("INSERT INTO queue_build (fid, building_type, building_type_id, building_slot, newsize, starttime, endtime) VALUES (:eos_firm_id, :bldg_type, :bldg_type_id, :bldg_slot, 10, :starttime, :endtime)");
	$query_insert_new_queue->execute(array(':eos_firm_id' => $eos_firm_id, ':bldg_type' => $bldg_type, ':bldg_type_id' => $bldg_type_id, ':bldg_slot' => $bldg_slot, ':starttime' => $starttime, ':endtime' => $endtime));
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	if($player_level < 5){
?>
	<script type="text/javascript">
		var slot = <?= $bldg_slot ?>;
		bldgController.cd_on[slot] = 0;
		bldgController.cd_total[slot] = 0
		bldgController.cd_remaining[slot] = 0
		bldgController.bldg_title[slot] = '<?= $generic_name ?> (10 m&#178;)';
		bldgController.bldg_status[slot] = 'Ready';
		document.getElementById("cd_icon_back_"+slot).className = "anim_placeholder";
		document.getElementById("cd_icon_"+slot).className = "anim_placeholder";
		jQuery("#building_image_"+slot).html('<img class="no_select" src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" width="90" height="40" />');
		$("#cd_icon_title_"+slot).attr("href","<?= $bldg_activity_url.$bldg_id ?>");

		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
	</script>
	<h3>Construction Completed</h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
	Thanks to government subsidy, a new <?= $generic_name ?> is built in no time.
<?php
	}else{
?>
	<script type="text/javascript">
		var slot = <?= $bldg_slot ?>;
		bldgController.cd_total[slot] = <?= $bldg_firsttimecost ?>;
		bldgController.cd_remaining[slot] = <?= $bldg_firsttimecost ?>;
		bldgController.cd_on[slot] = 1;
		bldgController.bldg_title[slot] = '<?= $generic_name ?> (10 m&#178;)';
		bldgController.bldg_status[slot] = 'New Construction';
		document.getElementById("cd_icon_back_"+slot).className = "anim_hammer anim_working";
		document.getElementById("cd_icon_"+slot).className = "anim_hammer";
		jQuery("#building_image_"+slot).html('<img class="no_select" src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" width="90" height="40" />');
		$("#cd_icon_title_"+slot).attr("href","bldg-expand-status.php?id=0&type=<?= $bldg_type ?>&slot=<?= $bldg_slot ?>");

		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
	</script>
	<h3>Construction Started</h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
	You have commissioned to build a new <?= $generic_name ?>.
<?php
	}
?>
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>