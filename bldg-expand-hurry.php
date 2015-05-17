<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
$bldg_slot = filter_var($_POST['slot'], FILTER_SANITIZE_NUMBER_INT);
$eh_time = filter_var($_POST['eh_time'], FILTER_SANITIZE_NUMBER_INT);
if(!$bldg_id && !$bldg_slot){
	fbox_breakout('buildings.php');
}
if($eh_time < 0){
	fbox_redirect("bldg-expand-status.php?id=$bldg_id&type=$bldg_type&slot=$bldg_slot", 'Thanks for trying to give the workers a break, but then again they are hard workers...');
}
if($bldg_type == 'fact'){
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_info = $db->prepare("SELECT fact_name AS bldg_name, slot FROM firm_fact WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_id = $db->prepare("SELECT id FROM firm_fact WHERE fid = :eos_firm_id AND slot = :slot");
	$query_get_bldg_list_info = $db->prepare("SELECT name, has_image FROM list_fact WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_fact SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_insert_bldg = $db->prepare("INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) SELECT :eos_firm_id, :building_type_id, name, :size, :slot FROM list_fact WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_prod WHERE ffid = :bldg_id");
}else if($bldg_type == 'store'){
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_info = $db->prepare("SELECT store_name AS bldg_name, slot FROM firm_store WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_id = $db->prepare("SELECT id FROM firm_store WHERE fid = :eos_firm_id AND slot = :slot");
	$query_get_bldg_list_info = $db->prepare("SELECT name, has_image FROM list_store WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_store SET size = :size, is_expanding = 0 WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_insert_bldg = $db->prepare("INSERT INTO firm_store (fid, store_id, store_name, size, slot) SELECT :eos_firm_id, :building_type_id, name, :size, :slot FROM list_store WHERE id = :building_type_id");
}else if($bldg_type == 'rnd'){
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_info = $db->prepare("SELECT rnd_name AS bldg_name, slot FROM firm_rnd WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_id = $db->prepare("SELECT id FROM firm_rnd WHERE fid = :eos_firm_id AND slot = :slot");
	$query_get_bldg_list_info = $db->prepare("SELECT name, has_image FROM list_rnd WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_rnd SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_insert_bldg = $db->prepare("INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) SELECT :eos_firm_id, :building_type_id, name, :size, :slot FROM list_rnd WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_res WHERE frid = :bldg_id");
}else{
	fbox_breakout('buildings.php');
}

// Redirect unauthorized
if(!$ctrl_bldg_hurry){
	fbox_breakout('buildings.php');
}

// First check $bldg_id belongs to $eos_firm_id, get $bldg_name
if($bldg_id){
	$query_get_bldg_info->execute(array(':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
	$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
	if(empty($firm_bldg)){
		fbox_breakout('buildings.php', 'Building not found.');
	}
	$bldg_name = $firm_bldg['bldg_name'];
	
	// and that it is not active
	if(isset($query_confirm_inactivity)){
		$query_confirm_inactivity->execute(array(':bldg_id' => $bldg_id));
		$count = $query_confirm_inactivity->fetchColumn();
		if($count){
			fbox_redirect($bldg_activity_url.$bldg_id);
		}
	}
}

// and that it IS under construction
if($bldg_id){
	$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
	$query->execute(array($bldg_type, $bldg_id));
	$queue_build = $query->fetch(PDO::FETCH_ASSOC);
}else{
	$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_slot = ? AND fid = ?");
	$query->execute(array($bldg_type, $bldg_slot, $eos_firm_id));
	$queue_build = $query->fetch(PDO::FETCH_ASSOC);
}
if(empty($queue_build)){
	fbox_breakout('buildings.php');
}
$b_expand_id = $queue_build["id"];
$b_expand_type_id = $queue_build["building_type_id"];
$b_expand_bldg_id = $queue_build["building_id"];
$b_expand_slot = $queue_build["building_slot"];
$b_expand_size = $queue_build["newsize"];
$b_expand_endtime = $queue_build["endtime"];
$b_expand_remaining = $b_expand_endtime - time();

// Initialize building name and image
$query_get_bldg_list_info->execute(array(':building_type_id' => $b_expand_type_id));
$bldg_list_info = $query_get_bldg_list_info->fetch(PDO::FETCH_ASSOC);
$generic_name = $bldg_list_info["name"];
if($bldg_list_info["has_image"]){
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($generic_name));
}else{
	$filename = "no-image";
}
if(!$bldg_id){
	$bldg_name = $generic_name;
}

// Initialize Firm Cash and Level
$sql = "SELECT firms.cash, firms.level FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];
$firm_level = $firm['level'];

// Initialize Player Influence and Level
$sql = "SELECT player_level, influence FROM players WHERE players.id = $eos_player_id";
$player = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$player_level = $player['player_level'];
$player_influence = $player['influence'];

$eh_time_max = ceil($b_expand_remaining/60);
$eh_time_sec = $eh_time * 60;
if($eh_time > $eh_time_max){
	$eh_time = $eh_time_max;
	$eh_time_sec = $b_expand_remaining;
}
if($b_expand_size > 10 && $b_expand_size < 501){
	$eh_unit_cost_inf = 0;
	$eh_unit_cost_cash = 0;
}else{
	$eh_unit_cost_inf = 0.2;
	$eh_unit_cost_cash = 600000 * max(0, $firm_level - 5);
}
$b_expand_influence_cost = ceil($eh_unit_cost_inf * $eh_time);
$b_expand_cash_cost = $eh_unit_cost_cash * $eh_time;

if($player_influence < $b_expand_influence_cost || $firm_cash < $b_expand_cash_cost){
	fbox_redirect("bldg-expand-status.php?id=$bldg_id&type=$bldg_type&slot=$bldg_slot", 'Insufficient influence or cash, redirecting...');
}else{
	if($b_expand_influence_cost > 0){
		$sql = "UPDATE players SET influence = influence - $b_expand_influence_cost WHERE id = $eos_player_id AND influence >= $b_expand_influence_cost";
		$affected = $db->query($sql)->rowCount();
		if(!$affected){
			fbox_redirect("bldg-expand-status.php?id=$bldg_id&type=$bldg_type&slot=$bldg_slot", 'Failed to deduct influence, redirecting...');
		}
	}
	if($b_expand_cash_cost > 0){
		if($ctrl_leftover_allowance < $b_expand_cash_cost){
			fbox_redirect("bldg-expand-status.php?id=$bldg_id&type=$bldg_type&slot=$bldg_slot", 'Failed to deduct cash, spending limit reached, redirecting...');
		}
		$sql = "UPDATE firms SET cash = cash - $b_expand_cash_cost WHERE id = $eos_firm_id AND cash >= $b_expand_cash_cost";
		$affected = $db->query($sql)->rowCount();
		if(!$affected){
			fbox_redirect("bldg-expand-status.php?id=$bldg_id&type=$bldg_type&slot=$bldg_slot", 'Failed to deduct cash, redirecting...');
		}
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $b_expand_cash_cost, 'Expansion', NOW())";
		$db->query($sql);
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $b_expand_cash_cost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);
		$ctrl_leftover_allowance = ($ctrl_daily_allowance == -1) ? -1 : ($ctrl_leftover_allowance - $b_expand_cash_cost);
	}
	if($eh_time >= $eh_time_max){
		$sql = "DELETE FROM queue_build WHERE id = $b_expand_id";
		$db->query($sql);
		if($b_expand_bldg_id){
			$query_update_bldg->execute(array(':size' => $b_expand_size, ':bldg_id' => $b_expand_bldg_id, ':eos_firm_id' => $eos_firm_id));
			$query_get_bldg_info->execute(array(':bldg_id' => $b_expand_bldg_id, ':eos_firm_id' => $eos_firm_id));
			$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
			$b_expand_slot = $firm_bldg['slot'];
		}else{
			$query_insert_bldg->execute(array(':eos_firm_id' => $eos_firm_id, ':building_type_id' => $b_expand_type_id, ':size' => $b_expand_size, ':slot' => $b_expand_slot));
			$query_get_bldg_id->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $b_expand_slot));
			$firm_bldg = $query_get_bldg_id->fetch(PDO::FETCH_ASSOC);
			$bldg_id = $firm_bldg['id'];
		}
	}else{
		$sql = "UPDATE queue_build SET endtime = endtime - $eh_time_sec WHERE id = $b_expand_id";
		$db->query($sql);
		if($b_expand_bldg_id){
			$query_get_bldg_info->execute(array(':bldg_id' => $b_expand_bldg_id, ':eos_firm_id' => $eos_firm_id));
			$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
			$b_expand_slot = $firm_bldg['slot'];
		}else{
			$query_get_bldg_id->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $b_expand_slot));
			$firm_bldg = $query_get_bldg_id->fetch(PDO::FETCH_ASSOC);
			$bldg_id = $firm_bldg['id'];
		}
	}
}

?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
		<script type="text/javascript">
			bldgController.updateSlot(<?= $b_expand_slot ?>);
			firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
		</script>
	<h3>Expand <?= $bldg_name.' (to '.$b_expand_size.' m&#178;)' ?></h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
		<?= ($eh_time >= $eh_time_max) ? 'Expansion completed!' : 'Speed-up successful!' ?>
		<br /><br />
<?php if($eh_time < $eh_time_max){ ?>
	<a class="jqDialog" href="bldg-expand-status.php?id=<?= $bldg_id ?>&type=<?= $bldg_type ?>&slot=<?= $bldg_slot ?>"><input type="button" class="bigger_input" value="Back" /></a> 
<?php } ?>
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>