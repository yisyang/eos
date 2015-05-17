<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
// Config
$settings_maintenance_multiplier = 0.02;
$settings_salary_multiplier = 5000;

$ffid = filter_var($_POST['ffid'], FILTER_SANITIZE_NUMBER_INT);
$fcid = filter_var($_POST['fcid'], FILTER_SANITIZE_NUMBER_INT);
$pnum = filter_var($_POST['pnum'], FILTER_SANITIZE_NUMBER_INT);
$ipid1_wh_id = isset($_POST['ipid1_wh_id']) ? filter_var($_POST['ipid1_wh_id'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid2_wh_id = isset($_POST['ipid2_wh_id']) ? filter_var($_POST['ipid2_wh_id'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid3_wh_id = isset($_POST['ipid3_wh_id']) ? filter_var($_POST['ipid3_wh_id'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid4_wh_id = isset($_POST['ipid4_wh_id']) ? filter_var($_POST['ipid4_wh_id'], FILTER_SANITIZE_NUMBER_INT) : 0;

// Check pnum, max_pnum will be treated later in this file
if($pnum < 1){
	fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 1));
}

if(!$ffid || !$fcid){
	fbox_breakout('buildings.php');
}
if(!$pnum || !$ctrl_fact_produce){
	fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 1));
}

// Make sure the eos user actually owns the factory
$query = $db->prepare("SELECT firm_fact.fact_id, firm_fact.fact_name, firm_fact.size, firm_fact.slot, list_fact.cost FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.id = ? AND firm_fact.fid = ?");
$query->execute(array($ffid, $eos_firm_id));
$fact = $query->fetch(PDO::FETCH_ASSOC);
if(empty($fact)){
	fbox_breakout('buildings.php');
}else{
	$fact_id = $fact['fact_id'];
	$fact_name = $fact['fact_name'];
	$fact_size = $fact['size'];
	$fact_slot = $fact['slot'];
	$fact_cost_m2 = $fact['cost'];
}

// Then check if the ffid is producing stuff, and assign starttime accordingly
$sql = "SELECT endtime FROM queue_prod WHERE fid = ? AND ffid = ? ORDER BY endtime DESC";
$query = $db->prepare($sql);
$query->execute(array($eos_firm_id, $ffid));
$result_producing = $query->fetch(PDO::FETCH_ASSOC);
if(empty($result_producing)){
	$starttime_override = 0;
}else{
	$starttime_override = $result_producing['endtime'];
}

// and that it is not under construction
$sql = "SELECT COUNT(*) FROM queue_build WHERE building_type = 'fact' AND building_id = '$ffid'";
$count = $db->query($sql)->fetchColumn();
if($count){
	fbox_redirect('bldg-expand-status.php?type=fact&id='.$ffid);
}

//Next check fcid belongs to fact_id
$sql = "SELECT * FROM list_fact_choices WHERE fact_id = $fact_id AND id = '$fcid'";
$fact_choice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
if(empty($fact_choice)){
	fbox_redirect('factories-production.php?ffid='.$ffid);
}

// Populate cost data in $, time, and ipids
$fc_cost = $fact_choice["cost"];
$fc_timecost = $fact_choice["timecost"] * 10/$fact_size;
$fc_ipid1 = $fact_choice["ipid1"];
$fc_ipid1n = $fact_choice["ipid1n"]+0; //+0 is used to remove insignificant decimal pts but keep others
$fc_ipid1qm = $fact_choice["ipid1qm"];
$fc_ipid2 = $fact_choice["ipid2"];
$fc_ipid2n = $fact_choice["ipid2n"]+0;
$fc_ipid2qm = $fact_choice["ipid2qm"];
$fc_ipid3 = $fact_choice["ipid3"];
$fc_ipid3n = $fact_choice["ipid3n"]+0;
$fc_ipid3qm = $fact_choice["ipid3qm"];
$fc_ipid4 = $fact_choice["ipid4"];
$fc_ipid4n = $fact_choice["ipid4n"]+0;
$fc_ipid4qm = $fact_choice["ipid4qm"];
$fc_opid1 = $fact_choice["opid1"];
$fc_opid1usetech = $fact_choice["opid1usetech"];

$product_query = $db->prepare("SELECT name, value, has_icon FROM list_prod WHERE id = ?");
$product_query->execute(array($fc_opid1));
$opid1 = $product_query->fetch(PDO::FETCH_ASSOC);
$opid1_name = $opid1['name'];
$opid1_value = $opid1['value'];
$opid1_value_sqrt = pow($opid1_value, 0.5);
if($opid1["has_icon"]){
	$opid1_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($opid1_name));
}else{
	$opid1_filename = "no-icon";
}

$unit_cost = 0.5 + 0.5 / pow(1 + $pnum * $opid1_value_sqrt / 10000, 0.25);
$fc_timecost_actual = $fc_timecost * $unit_cost;
$max_pnum = min(floor(168*3600/$fc_timecost_actual)+1, 99999999999999);
// Estimate to prevent abuse
while($pnum > $max_pnum){
	$pnum = 0.95 * $max_pnum;
	$unit_cost = 0.5 + 0.5 / pow(1 + $pnum * $opid1_value_sqrt / 10000, 0.25);
	$fc_timecost_actual = $fc_timecost * $unit_cost;
	$max_pnum = floor(168*3600/$fc_timecost_actual);
}

$fc_total_cost = $fc_cost * $pnum * $unit_cost;
$fc_total_timecost = $fc_timecost_actual * $pnum;
$total_opid1_qm = 0;
$opid1_q = 0;
$opid1_cost = $fc_cost;
$ipid1_q = 0;
$ipid2_q = 0;
$ipid3_q = 0;
$ipid4_q = 0;
$ipid1_cost = 0;
$ipid2_cost = 0;
$ipid3_cost = 0;
$ipid4_cost = 0;

$starttime = max($starttime_override, time());
$endtime = $starttime + $fc_total_timecost;
// Make sure the queue is shorter than 168 hours
if($endtime - time() > 168 * 3600){
	fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 5));
}

// Add building maintenance and salary to cost
$opid1_cost += $fact_size * $fact_cost_m2 * $settings_maintenance_multiplier * $fc_timecost / 86400;
$opid1_cost += pow($fact_size, 1.2) * $settings_maintenance_multiplier * $fc_timecost / 86400;

// Initialize Firm Cash
$sql = "SELECT firms.cash FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];

// Compare cash with $ needed, insufficient = exit
if($firm_cash < $fc_total_cost){
	fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 2));
}
if($ctrl_leftover_allowance < $fc_total_cost){
	fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 3));
}

// Calculate required ipidn
// and get ipidn from firm warehouse for each ipid where ipidn >= required, insufficient = exit
$wh_conf_query = $db->prepare("SELECT * FROM firm_wh WHERE id = ? AND pid = ? AND firm_wh.pidn >= ? AND fid = ? ORDER BY pidq DESC");
if($fc_ipid1n){
	if(!$ipid1_wh_id){
		//need to select ipid
		fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 11));
	}
	$fc_total_ipid1n = ceil($fc_ipid1n * $pnum * $unit_cost);
	$wh_conf_query->execute(array($ipid1_wh_id, $fc_ipid1, $fc_total_ipid1n, $eos_firm_id));
	$wh_prod = $wh_conf_query->fetch(PDO::FETCH_ASSOC);
	if(empty($wh_prod)){
		//Selected ipid not found in warehouse
		fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
	}
	$ipid1_q = $wh_prod["pidq"];
	$ipid1_cost = $wh_prod["pidcost"];
	$total_opid1_qm += $fc_ipid1qm;
	$opid1_q += $ipid1_q * $fc_ipid1qm;
	$opid1_cost += $ipid1_cost * $fc_ipid1n;

	if($fc_ipid2n){
		if(!$ipid2_wh_id){
			//need to select ipid
			fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 11));
		}
		$fc_total_ipid2n = ceil($fc_ipid2n * $pnum * $unit_cost);
		$wh_conf_query->execute(array($ipid2_wh_id, $fc_ipid2, $fc_total_ipid2n, $eos_firm_id));
		$wh_prod = $wh_conf_query->fetch(PDO::FETCH_ASSOC);
		if(empty($wh_prod)){
			//Selected ipid not found in warehouse
			fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
		}
		$ipid2_q = $wh_prod["pidq"];
		$ipid2_cost = $wh_prod["pidcost"];
		$total_opid1_qm += $fc_ipid2qm;
		$opid1_q += $ipid2_q * $fc_ipid2qm;
		$opid1_cost += $ipid2_cost * $fc_ipid2n;
	
		if($fc_ipid3n){
			if(!$ipid3_wh_id){
				//need to select ipid
				fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 11));
			}
			$fc_total_ipid3n = ceil($fc_ipid3n * $pnum * $unit_cost);
			$wh_conf_query->execute(array($ipid3_wh_id, $fc_ipid3, $fc_total_ipid3n, $eos_firm_id));
			$wh_prod = $wh_conf_query->fetch(PDO::FETCH_ASSOC);
			if(empty($wh_prod)){
				//Selected ipid not found in warehouse
				fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
			}
			$ipid3_q = $wh_prod["pidq"];
			$ipid3_cost = $wh_prod["pidcost"];
			$total_opid1_qm += $fc_ipid3qm;
			$opid1_q += $ipid3_q * $fc_ipid3qm;
			$opid1_cost += $ipid3_cost * $fc_ipid3n;
			
			if($fc_ipid4n){
				if(!$ipid4_wh_id){
					//need to select ipid
					fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 11));
				}
				$fc_total_ipid4n = ceil($fc_ipid4n * $pnum * $unit_cost);
				$wh_conf_query->execute(array($ipid4_wh_id, $fc_ipid4, $fc_total_ipid4n, $eos_firm_id));
				$wh_prod = $wh_conf_query->fetch(PDO::FETCH_ASSOC);
				if(empty($wh_prod)){
					//Selected ipid not found in warehouse
					fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
				}
				$ipid4_q = $wh_prod["pidq"];
				$ipid4_cost = $wh_prod["pidcost"];
				$total_opid1_qm += $fc_ipid4qm;
				$opid1_q += $ipid4_q * $fc_ipid4qm;
				$opid1_cost += $ipid4_cost * $fc_ipid4n;
			}
		}
	}
}

// Initialize tech variables
if($fc_opid1usetech){
	$sql = "SELECT quality FROM firm_tech WHERE pid = '$fc_opid1' AND fid = '$eos_firm_id'";
	$opid1_techq = $db->query($sql)->fetchColumn();
	if(!$opid1_techq){
		// Allows quality 0 production for all items without research
		$opid1_techq = 0;
	}
	$opid1_q = (1 - $total_opid1_qm) * $opid1_techq + $opid1_q;
}else{
	$opid1_q = 0;
}

// Deduct $ from firm
if($fc_total_cost > 0){
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $fc_total_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 2));
	}
	$sql = "INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES ($eos_firm_id, 1, $fc_opid1, $pnum, $opid1_q, $fc_total_cost, 'Production', NOW())";
	$db->query($sql);
	$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $fc_total_cost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$db->query($sql);
	$ctrl_leftover_allowance = ($ctrl_daily_allowance == -1) ? -1 : ($ctrl_leftover_allowance - $fc_total_cost);
}

// Deduct ipids
$query_allocate_ipid = $db->prepare("UPDATE firm_wh SET pidn = pidn - :ipid_n WHERE id = :wh_id AND pidn >= :ipid_n");
if($fc_ipid1n){
	$result = $query_allocate_ipid->execute(array(':wh_id' => $ipid1_wh_id, ':ipid_n' => $fc_total_ipid1n));
	$affected = $query_allocate_ipid->rowCount();
	if(!$result || !$affected){
		fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
	}
	if($fc_ipid2n){
		$result = $query_allocate_ipid->execute(array(':wh_id' => $ipid2_wh_id, ':ipid_n' => $fc_total_ipid2n));
		$affected = $query_allocate_ipid->rowCount();
		if(!$result || !$affected){
			fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
		}
		if($fc_ipid3n){
			$result = $query_allocate_ipid->execute(array(':wh_id' => $ipid3_wh_id, ':ipid_n' => $fc_total_ipid3n));
			$affected = $query_allocate_ipid->rowCount();
			if(!$result || !$affected){
				fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
			}
			if($fc_ipid4n){
				$result = $query_allocate_ipid->execute(array(':wh_id' => $ipid4_wh_id, ':ipid_n' => $fc_total_ipid4n));
				$affected = $query_allocate_ipid->rowCount();
				if(!$result || !$affected){
					fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 12));
				}
			}
		}
	}
}
$opid1_cost = $opid1_cost * $unit_cost;

//Add to production queue
$sql = "INSERT INTO queue_prod (fid, ffid, fcid, opid1, opid1q, opid1n, opid1cost, ipid1q, ipid2q, ipid3q, ipid4q, ipid1cost, ipid2cost, ipid3cost, ipid4cost, starttime, endtime) VALUES ('$eos_firm_id', '$ffid', '$fcid', '$fc_opid1', '$opid1_q', '$pnum', '$opid1_cost', '$ipid1_q', '$ipid2_q', '$ipid3_q', '$ipid4_q', '$ipid1_cost', '$ipid2_cost', '$ipid3_cost', '$ipid4_cost', '$starttime', '$endtime')";
$result = $db->query($sql);
if(!$result){
	fbox_redirect('factories-production-confirm.php', '', array('ffid' => $ffid, 'fcid' => $fcid, 'ipid1_cwi' => $ipid1_wh_id, 'ipid2_cwi' => $ipid2_wh_id, 'ipid3_cwi' => $ipid3_wh_id, 'ipid4_cwi' => $ipid4_wh_id, 'err' => 99));
}
$sql = "INSERT INTO log_queue_prod (fid, ffid, fcid, opid1, opid1q, opid1n, starttime) VALUES ('$eos_firm_id', '$ffid', '$fcid', '$fc_opid1', '$opid1_q', '$pnum', '$starttime')";
$db->query($sql);
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
<?php if(!$starttime_override){ ?>
		var slot = <?= $fact_slot ?>;
		bldgController.cd_total[slot] = <?= $fc_total_timecost ?>;
		bldgController.cd_remaining[slot] = <?= $fc_total_timecost ?>;
		bldgController.cd_on[slot] = 1;
		bldgController.bldg_status[slot] = '<?= 'Producing '.$pnum.' <img src="/eos/images/prod/'.$opid1_filename.'.gif" />'.' (Q'.$opid1_q.')' ?>';
		document.getElementById("cd_icon_back_"+slot).className = "anim_gear anim_working";
		document.getElementById("cd_icon_"+slot).className = "anim_gear";
<?php } ?>
		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
		progressController.refreshQueue('fact');
	</script>
	<h3>Production Started</h3>
	<?php
		echo 'You are now producing ',$pnum,' quality ',$opid1_q,' <img style="vertical-align:middle;" src="/eos/images/prod/',$opid1_filename,'.gif" alt="',$opid1_name,'" title="',$opid1_name,'" />.';
	?>
	<br /><br />
	<a class="jqDialog" href="factories-production.php?ffid=<?= $ffid ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>

