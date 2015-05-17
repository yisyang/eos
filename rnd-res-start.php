<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['frid'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = 'rnd';
$rc_pid = filter_var($_POST['rc_pid'], FILTER_SANITIZE_NUMBER_INT);

if(!$bldg_id || !$rc_pid || !$ctrl_rnd_res){
	fbox_breakout('buildings.php');
}
	
// Make sure the eos user actually owns the building
$query = $db->prepare("SELECT rnd_id AS bldg_type_id, rnd_name AS bldg_name, size, slot FROM firm_rnd WHERE id = ? AND fid = ?");
$query->execute(array($bldg_id, $eos_firm_id));
$fact = $query->fetch(PDO::FETCH_ASSOC);
if(empty($fact)){
	fbox_breakout('buildings.php');
}else{
	$bldg_type_id = $fact['bldg_type_id'];
	$bldg_name = $fact['bldg_name'];
	$bldg_size = $fact['size'];
	$bldg_slot = $fact['slot'];
}

// and that it is not under construction
$sql = "SELECT COUNT(*) FROM queue_build WHERE building_type = '$bldg_type' AND building_id = '$bldg_id'";
$count = $db->query($sql)->fetchColumn();
if($count){
	fbox_redirect('bldg-expand-status.php?type='.$bldg_type.'&id='.$bldg_id);
}

// Check that the rc_pid is not already being researched in queue_res for the fid
$sql = "SELECT frid, newlevel, endtime FROM queue_res WHERE fid = $eos_firm_id AND pid = $rc_pid ORDER BY newlevel DESC LIMIT 0,1";
$res_existing = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$starttime_override = 0;
$newlevel_override = 0;
if(!empty($res_existing)){
	if($res_existing['frid'] != $bldg_id){
		fbox_redirect('rnd-res.php?frid='.$bldg_id, 'Cannot add to queue, as the product is being researched in another facility.');
	}
	$newlevel_override = $res_existing['newlevel'] + 1;
	$starttime_override = $res_existing['endtime'];
}
$sql = "SELECT endtime FROM queue_res WHERE fid = $eos_firm_id AND frid = $bldg_id ORDER BY endtime DESC";
$starttime_override_2 = $db->query($sql)->fetchColumn();
if($starttime_override_2){
	$starttime_override = max($starttime_override, $starttime_override_2);
}

// Next check rc_pid belongs to rnd_id, and record res_dep_1 to 3
$sql = "SELECT * FROM list_prod WHERE id = '$rc_pid'";
$rc_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$rc_cat_id = $rc_prod["cat_id"];
$sql = "SELECT COUNT(*) FROM list_rnd_choices WHERE rnd_id = '$bldg_type_id' AND cat_id = $rc_cat_id";
$count = $db->query($sql)->fetchColumn();
if(!$count){
	fbox_redirect('rnd-res.php?frid='.$bldg_id);
}
$rc_res_dep_1 = $rc_prod["res_dep_1"];
$rc_res_dep_2 = $rc_prod["res_dep_2"];
$rc_res_dep_3 = $rc_prod["res_dep_3"];

// Populate prod data
$rc_name = $rc_prod["name"];
$rc_tech_avg = $rc_prod["tech_avg"];
if($rc_prod["has_icon"]){
	$rc_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($rc_name));
}else{
	$rc_filename = "no-icon";
}
$rc_cost_base = $rc_prod["res_cost"];

if($newlevel_override){
	$rc_newlevel = $newlevel_override;
}else{
	// Check current quality from firm_tech
	$sql = "SELECT quality FROM firm_tech WHERE fid = '$eos_firm_id' AND pid = '$rc_pid'";
	$firm_tech = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(!empty($firm_tech)){
		$rc_cur_quality = $firm_tech["quality"];
		$rc_newlevel = $rc_cur_quality + 1;
	}else{
		// For new research, check research dependencies
		$query_tech_exists = $db->prepare("SELECT COUNT(*) FROM firm_tech WHERE fid = :eos_firm_id AND pid = :pid AND quality > 0");
		if($rc_res_dep_1){
			$query_tech_exists->execute(array(':eos_firm_id' => $eos_firm_id, ':pid' => $rc_res_dep_1));
			if(!$query_tech_exists->fetchColumn()){
				fbox_redirect('rnd-res.php?frid='.$bldg_id);
			}
		}
		if($rc_res_dep_2){
			$query_tech_exists->execute(array(':eos_firm_id' => $eos_firm_id, ':pid' => $rc_res_dep_2));
			if(!$query_tech_exists->fetchColumn()){
				fbox_redirect('rnd-res.php?frid='.$bldg_id);
			}
		}
		if($rc_res_dep_3){
			$query_tech_exists->execute(array(':eos_firm_id' => $eos_firm_id, ':pid' => $rc_res_dep_3));
			if(!$query_tech_exists->fetchColumn()){
				fbox_redirect('rnd-res.php?frid='.$bldg_id);
			}
		}
		$rc_newlevel = 1;
	}
}
$rc_cost = max(10000, $rc_cost_base * pow(1.2, $rc_newlevel - 0.25 * $rc_tech_avg));
$rc_restime = 1000/$bldg_size * pow(max(1, $rc_newlevel - 0.25 * $rc_tech_avg), 3);
$rc_newlevel_next = $rc_newlevel + 1;
$rc_cost_next = max(10000, $rc_cost_base * pow(1.2, $rc_newlevel_next - 0.25 * $rc_tech_avg));
$rc_restime_next = 1000/$bldg_size * pow(max(1, $rc_newlevel_next - 0.25 * $rc_tech_avg), 3);

// Initialize Firm Cash
$sql = "SELECT firms.cash FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];
if($firm_cash < $rc_cost){
	fbox_redirect('rnd-res.php?frid='.$bldg_id, 'Insufficient cash.');
}
if($ctrl_leftover_allowance < $rc_cost){
	fbox_redirect('rnd-res.php?frid='.$bldg_id, 'Daily spending limit reached.');
}
$firm_cash_leftover = $firm_cash - $rc_cost;

// Deduct $ from firm
$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
$result = $query->execute(array(':cost' => $rc_cost, ':firm_id' => $eos_firm_id));
$affected = $query->rowCount();
if(!$result || !$affected){
	fbox_redirect('rnd-res.php?frid='.$bldg_id, 'Insufficient cash.');
}
// Write to logs
$sql = "INSERT INTO log_revenue (fid, is_debit, pid, pidq, value, source, transaction_time) VALUES ($eos_firm_id, 1, $rc_pid, $rc_newlevel, $rc_cost, 'Research', NOW())";
$db->query($sql);
$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $rc_cost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
$db->query($sql);
$ctrl_leftover_allowance = ($ctrl_daily_allowance == -1) ? -1 : ($ctrl_leftover_allowance - $rc_cost);

// Add to researching queue
$starttime = max($starttime_override, time());
$endtime = $starttime + $rc_restime;
$sql="INSERT INTO queue_res (fid, frid, pid, newlevel, starttime, endtime) VALUES ($eos_firm_id, $bldg_id, $rc_pid, $rc_newlevel, $starttime, $endtime)";
$result = $db->query($sql);
if(!$result){	
	fbox_breakout('buildings.php');
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
<?php if(!$starttime_override){ ?>
		var slot = <?= $bldg_slot ?>;
		bldgController.cd_total[slot] = <?= $rc_restime ?>;
		bldgController.cd_remaining[slot] = <?= $rc_restime ?>;
		bldgController.cd_on[slot] = 1;
		bldgController.bldg_status[slot] = '<?= 'Researching <img src="/eos/images/prod/'.$rc_filename.'.gif" /> to quality '.$rc_newlevel ?>';
		document.getElementById("cd_icon_back_"+slot).className = "anim_gear anim_working";
		document.getElementById("cd_icon_"+slot).className = "anim_gear";
<?php } ?>
		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
		progressController.refreshQueue('rnd');
	</script>
	<div class="vert_middle">
	<h3>Research Started</h3>
	<?php
		$rc_restime_display = sec2hms($rc_restime);
		$current_task_res = 'You have just added quality '.$rc_newlevel.' <img src="/eos/images/prod/'.$rc_filename.'.gif" alt="'.$rc_name.'" title="'.$rc_name.'" /> to your research queue.';
		echo $current_task_res;
		echo '<br /><br />';
		echo '<h3>Queue Additional Research</h3>';
		echo 'Adding the next level (quality ',$rc_newlevel_next,') into queue will cost:<br /><br />';
		echo '<img src="/eos/images/time.gif" alt="Time" title="Time" /> '.sec2hms($rc_restime_next).'<br />';
		echo '<img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $'.number_format($rc_cost_next/100, 0, '.', ',').'<br /><br />';
		if($firm_cash_leftover > $rc_cost_next){
			echo '<a class="jqDialog" href="rnd-res-start.php" params="frid='.$bldg_id.'&rc_pid='.$rc_pid.'"><input type="button" class="bigger_input" value="Add Next Level to Queue" /></a>';
		}else{
			echo '[Insufficient Funds]';
		}
	?>
	</div>
	<br /><br />
	<a class="jqDialog" href="rnd-res.php?frid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>

