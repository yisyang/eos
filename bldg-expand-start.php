<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
$esize = filter_var($_POST['esize'], FILTER_SANITIZE_NUMBER_INT);
if(!$bldg_id){
	fbox_breakout('buildings.php');
}
if(!$esize || $esize < 1){
	fbox_redirect("bldg-expand.php", '', array('id' => $bldg_id, 'type' => $bldg_type));
}
if($bldg_type == 'fact'){
	$ctrl_expand = $ctrl_fact_expand;
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_info = $db->prepare("SELECT fact_name AS bldg_name, fact_id AS bldg_type_id, size, slot FROM firm_fact WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_fact WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_prod WHERE ffid = :bldg_id");
	$query_update_bldg = $db->prepare("UPDATE firm_fact SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
}else if($bldg_type == 'store'){
	$ctrl_expand = $ctrl_store_expand;
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_info = $db->prepare("SELECT store_name AS bldg_name, store_id AS bldg_type_id, size, slot FROM firm_store WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_store WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_store SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
}else if($bldg_type == 'rnd'){
	$ctrl_expand = $ctrl_rnd_expand;
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_info = $db->prepare("SELECT rnd_name AS bldg_name, rnd_id AS bldg_type_id, size, slot FROM firm_rnd WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_rnd WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_res WHERE frid = :bldg_id");
	$query_update_bldg = $db->prepare("UPDATE firm_rnd SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
}else{
	fbox_breakout('buildings.php');
}
if(!$ctrl_expand){
	fbox_redirect("bldg-expand.php", '', array('id' => $bldg_id, 'type' => $bldg_type));
}

// First check $bldg_id belongs to $eos_firm_id, get $bldg_name, $bldg_type_id, $bldg_size, and $bldg_slot
if($bldg_id){
	$query_get_bldg_info->execute(array(':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
	$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
	if(empty($firm_bldg)){
		fbox_breakout('buildings.php', 'Building not found.');
	}
	$bldg_name = $firm_bldg['bldg_name'];
	$bldg_type_id = $firm_bldg['bldg_type_id'];
	$bldg_size = $firm_bldg['size'];
	$bldg_slot = $firm_bldg['slot'];
	
	// and that it is not active
	if(isset($query_confirm_inactivity)){
		$query_confirm_inactivity->execute(array(':bldg_id' => $bldg_id));
		$count = $query_confirm_inactivity->fetchColumn();
		if($count){
			fbox_redirect($bldg_activity_url.$bldg_id);
		}
	}
}

// and that it is NOT under construction
$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
$query->execute(array($bldg_type, $bldg_id));
$queue_build = $query->fetch(PDO::FETCH_ASSOC);
if(!empty($queue_build)){
	fbox_breakout('buildings.php');
}

// Initialize building name and image
$query_get_bldg_list_info->execute(array(':building_type_id' => $bldg_type_id));
$bldg_list_info = $query_get_bldg_list_info->fetch(PDO::FETCH_ASSOC);
if(empty($bldg_list_info)){
	fbox_breakout('buildings.php', 'Building prototype not found.');
}
$unit_cost = 0.5 + 0.5 / pow($esize, 0.3);
$expand_cost = $bldg_list_info["cost"];
$expand_timecost = $bldg_list_info["timecost"];
if($bldg_list_info["has_image"]){
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg_list_info["name"]));
}else{
	$filename = "no-image";
}

// Initialize Firm Cash
$sql = "SELECT firms.cash FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];

$esize_instant = max(0, 500 - $bldg_size);
$expand_totalcost = $expand_cost * $esize;
$expand_totaltimecost = $expand_timecost * ($esize - $esize_instant) * $unit_cost;
$expand_newsize = $bldg_size + $esize;

if($expand_totaltimecost > 604800){
	fbox_redirect("bldg-expand.php", '', array('id' => $bldg_id, 'type' => $bldg_type));
}

// Confirm that the firm has enough $$
if($expand_totalcost > 0){
	if($ctrl_leftover_allowance < $expand_totalcost){
		fbox_redirect("bldg-expand.php", 'Daily spending limit reached, redirecting...', array('id' => $bldg_id, 'type' => $bldg_type));
	}else if($firm_cash < $expand_totalcost){
		fbox_redirect("bldg-expand.php", 'Insufficient cash, redirecting...', array('id' => $bldg_id, 'type' => $bldg_type));
	}else{
		// Deduct $ from firm
		if($expand_totalcost > 0){
			$sql = "UPDATE firms SET cash = cash - $expand_totalcost WHERE id = $eos_firm_id AND cash >= $expand_totalcost";
			$affected = $db->query($sql)->rowCount();
			if(!$affected){
				fbox_redirect("bldg-expand.php", 'Failed to deduct cash, redirecting...', array('id' => $bldg_id, 'type' => $bldg_type));
			}
		}
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $expand_totalcost, 'Expansion', NOW())";
		$db->query($sql);
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $expand_totalcost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);
		$ctrl_leftover_allowance = ($ctrl_daily_allowance == -1) ? -1 : ($ctrl_leftover_allowance - $expand_totalcost);

		//Start building
		if($expand_newsize < 501){
			$query_update_bldg->execute(array(':size' => $expand_newsize, ':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
		}else{
			if($bldg_size < 500){
				$bldg_size = 500;
				$query_update_bldg->execute(array(':size' => 500, ':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
			}
			$starttime = time();
			$endtime = $starttime + $expand_totaltimecost;
			$query = $db->prepare("INSERT INTO queue_build (fid, building_type, building_type_id, building_id, newsize, starttime, endtime) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$query->execute(array($eos_firm_id, $bldg_type, $bldg_type_id, $bldg_id, $expand_newsize, $starttime, $endtime));
			if($bldg_type == 'store'){
				$sql = "UPDATE firm_store SET is_expanding = 1 WHERE id = $bldg_id AND fid = $eos_firm_id";
				$db->query($sql);
			}
		}
	}
}else{
	echo "Error encountered, please report to admin. Error code BES-136.";
	exit();
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	if($expand_newsize < 501){
?>
	<script type="text/javascript">
		var slot = <?= $bldg_slot ?>;
		bldgController.bldg_title[slot] = '<?= $bldg_name.' ('.$expand_newsize ?> m&#178;)';

		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
	</script>
	<h3>Expansion Completed</h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
	<?php
		echo "RJ Construction came and worked on your ".$bldg_name.". It is now ".$expand_newsize."m&#178;.";
	?>
<?php
	}else{
?>
	<script type="text/javascript">
		var slot = <?= $bldg_slot ?>;
		bldgController.cd_total[slot] = <?= $expand_totaltimecost ?>;
		bldgController.cd_remaining[slot] = <?= $expand_totaltimecost ?>;
		bldgController.bldg_title[slot] = '<?= $bldg_name.' ('.$bldg_size ?> m&#178;)';
		bldgController.bldg_status[slot] = 'Expanding to <?= $expand_newsize ?> m&#178;';
		document.getElementById("cd_icon_back_"+slot).className = "anim_hammer anim_working";
		document.getElementById("cd_icon_"+slot).className = "anim_hammer";
		$("#cd_icon_title_"+slot).attr("href","bldg-expand-status.php?id=<?= $bldg_id ?>&type=<?= $bldg_type ?>&slot=<?= $bldg_slot ?>");
		bldgController.cd_on[slot] = 1;
		
		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
	</script>
	<h3>Expansion Started</h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
	<?php
		echo "You are now expanding ".$bldg_name." by ".$esize."m&#178;.";
	?>
<?php
	}
?>
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>