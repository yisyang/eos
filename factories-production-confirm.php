<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
// Config
$settings_maintenance_multiplier = 0.02;
$settings_salary_multiplier = 5000;

$ffid = filter_var($_POST['ffid'], FILTER_SANITIZE_NUMBER_INT);
$fcid = filter_var($_POST['fcid'], FILTER_SANITIZE_NUMBER_INT);
$default_pnum = isset($_POST['pnum']) ? filter_var($_POST['pnum'], FILTER_SANITIZE_NUMBER_INT) : 0;
$err = isset($_POST['err']) ? filter_var($_POST['err'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid1_cwi = isset($_POST['ipid1_cwi']) ? filter_var($_POST['ipid1_cwi'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid2_cwi = isset($_POST['ipid2_cwi']) ? filter_var($_POST['ipid2_cwi'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid3_cwi = isset($_POST['ipid3_cwi']) ? filter_var($_POST['ipid3_cwi'], FILTER_SANITIZE_NUMBER_INT) : 0;
$ipid4_cwi = isset($_POST['ipid4_cwi']) ? filter_var($_POST['ipid4_cwi'], FILTER_SANITIZE_NUMBER_INT) : 0;

if(!$ffid || !$fcid){
	fbox_breakout('buildings.php');
}
if($err){
	if($err == 1)
		$err_msg = "Please specify the # of units you would like to produce.";
	if($err == 2)
		$err_msg = "Not enough cash.";
	if($err == 3)
		$err_msg = "Daily spending limit reached.";
	if($err == 5)
		$err_msg = "Maximum queue cannot exceed 7 days (168 hours).";
	if($err == 11)
		$err_msg = "Please select input products.";
	if($err == 12)
		$err_msg = "You do not have enough of the input products you have selected.";
	if($err == 99)
		$err_msg = "Error encountered, please report to admin. Error code FPC-099.";
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

// Next check fcid belongs to fact_id
$sql = "SELECT * FROM list_fact_choices WHERE fact_id = $fact_id AND id = '$fcid'";
$fact_choice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
if(empty($fact_choice)){
	fbox_redirect('factories-production.php?ffid='.$ffid);
}

// Populate cost data in $, time, and ipids
$fcid = $fact_choice["id"];
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
if($opid1["has_icon"]){
	$opid1_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($opid1_name));
}else{
	$opid1_filename = "no-icon";
}

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
			
$allow_production = 1;
if(!$ctrl_fact_produce) $allow_production = 0;

// Add building maintenance and salary to cost
$opid1_cost_mt = $fact_size * $fact_cost_m2 * $settings_maintenance_multiplier * $fc_timecost / 86400;
$opid1_cost_salary = pow($fact_size, 1.2) * $settings_salary_multiplier * $fc_timecost / 86400;
$opid1_cost += $opid1_cost_mt;
$opid1_cost += $opid1_cost_salary;

// Initialize Firm Cash
$sql = "SELECT firms.cash FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];

// Calculate max_pnum
// and populate each ipidn from firm warehouse for each ipid where ipidn >= required, insufficient = error
$no_production_msg1 = "";
$no_production_msg2 = "";
$no_production_msg3 = "";
$no_production_msg4 = "";
if(!$fc_cost){
	$max_pnum = 99999999999999;
	$max_pnum_cash = 99999999999999;
}else{
	$max_pnum_cash = floor(min($ctrl_leftover_allowance, $firm_cash)/$fc_cost);
	$max_pnum = $max_pnum_cash;
}
if(!$max_pnum){
	$allow_production = 0;
	$no_production_msg1 = "Insufficient cash or daily spending limit reached.";
}
if($fc_timecost > (168*3600)){
	$allow_production = 0;
	$no_production_msg2 = "Factory is too small.";
}
$max_pnum_ipid1 = 0;
$max_pnum_ipid2 = 0;
$max_pnum_ipid3 = 0;
$max_pnum_ipid4 = 0;
$ipid1_wh_id = 0;
$ipid2_wh_id = 0;
$ipid3_wh_id = 0;
$ipid4_wh_id = 0;
$ipid1_n_display = '';
$ipid2_n_display = '';
$ipid3_n_display = '';
$ipid4_n_display = '';

$wh_query = $db->prepare("SELECT * FROM firm_wh WHERE pid = ? AND firm_wh.pidn >= 0 AND fid = ? ORDER BY pidq DESC");
if($fc_ipid1n){
	$fc_ipid1n_display = number_format_readable($fc_ipid1n);
	$product_query->execute(array($fc_ipid1));
	$prod = $product_query->fetch(PDO::FETCH_ASSOC);
	$ipid1_name = $prod['name'];
	if($prod['has_icon']){
		$ipid1_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid1_name));
	}else{
		$ipid1_filename = "no-icon";
	}
	$wh_query->execute(array($fc_ipid1, $eos_firm_id));
	$wh_prod = $wh_query->fetch(PDO::FETCH_ASSOC);
	$ipid1_count = 0;
	if(!empty($wh_prod)){
		$ipid1_count = 1;
		$ipid1_wh_id = $wh_prod["id"];
		$ipid1_q = $wh_prod["pidq"];
		$ipid1_n = $wh_prod["pidn"];
		$ipid1_cost = $wh_prod["pidcost"];
		if($ipid1_n < $fc_ipid1n){
			$allow_production = 0;
			$no_production_msg3 .= "$ipid1_name, ";
		}
		$ipid1_n_display = $ipid1_n;
		$max_pnum_ipid1 = $ipid1_n / $fc_ipid1n;
		$total_opid1_qm += $fc_ipid1qm;
		$opid1_q += $ipid1_q * $fc_ipid1qm;
		$opid1_cost += $ipid1_cost * $fc_ipid1n;
	}else{
		$allow_production = 0;
		$no_production_msg4 = "Missing raw material(s).";
	}
}
if($fc_ipid2n){
	$fc_ipid2n_display = number_format_readable($fc_ipid2n);
	$product_query->execute(array($fc_ipid2));
	$prod = $product_query->fetch(PDO::FETCH_ASSOC);
	$ipid2_name = $prod['name'];
	if($prod['has_icon']){
		$ipid2_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid2_name));
	}else{
		$ipid2_filename = "no-icon";
	}
	$wh_query->execute(array($fc_ipid2, $eos_firm_id));
	$wh_prod = $wh_query->fetch(PDO::FETCH_ASSOC);
	$ipid2_count = 0;
	if(!empty($wh_prod)){
		$ipid2_count = 1;
		$ipid2_wh_id = $wh_prod["id"];
		$ipid2_q = $wh_prod["pidq"];
		$ipid2_n = $wh_prod["pidn"];
		$ipid2_cost = $wh_prod["pidcost"];
		if($ipid2_n < $fc_ipid2n){
			$allow_production = 0;
			$no_production_msg3 .= "$ipid2_name, ";
		}
		$ipid2_n_display = $ipid2_n;
		$max_pnum_ipid2 = $ipid2_n / $fc_ipid2n;
		$total_opid1_qm += $fc_ipid2qm;
		$opid1_q += $ipid2_q * $fc_ipid2qm;
		$opid1_cost += $ipid2_cost * $fc_ipid2n;
	}else{
		$allow_production = 0;
		$no_production_msg4 = "Missing raw material(s).";
	}
}
if($fc_ipid3n){
	$fc_ipid3n_display = number_format_readable($fc_ipid3n);
	$product_query->execute(array($fc_ipid3));
	$prod = $product_query->fetch(PDO::FETCH_ASSOC);
	$ipid3_name = $prod['name'];
	if($prod['has_icon']){
		$ipid3_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid3_name));
	}else{
		$ipid3_filename = "no-icon";
	}
	$wh_query->execute(array($fc_ipid3, $eos_firm_id));
	$wh_prod = $wh_query->fetch(PDO::FETCH_ASSOC);
	$ipid3_count = 0;
	if(!empty($wh_prod)){
		$ipid3_count = 1;
		$ipid3_wh_id = $wh_prod["id"];
		$ipid3_q = $wh_prod["pidq"];
		$ipid3_n = $wh_prod["pidn"];
		$ipid3_cost = $wh_prod["pidcost"];
		if($ipid3_n < $fc_ipid3n){
			$allow_production = 0;
			$no_production_msg3 .= "$ipid3_name, ";
		}
		$ipid3_n_display = $ipid3_n;
		$max_pnum_ipid3 = $ipid3_n / $fc_ipid3n;
		$total_opid1_qm += $fc_ipid3qm;
		$opid1_q += $ipid3_q * $fc_ipid3qm;
		$opid1_cost += $ipid3_cost * $fc_ipid3n;
	}else{
		$allow_production = 0;
		$no_production_msg4 = "Missing raw material(s).";
	}
}
if($fc_ipid4n){
	$fc_ipid4n_display = number_format_readable($fc_ipid4n);
	$product_query->execute(array($fc_ipid4));
	$prod = $product_query->fetch(PDO::FETCH_ASSOC);
	$ipid4_name = $prod['name'];
	if($prod['has_icon']){
		$ipid4_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid4_name));
	}else{
		$ipid4_filename = "no-icon";
	}
	$wh_query->execute(array($fc_ipid4, $eos_firm_id));
	$wh_prod = $wh_query->fetch(PDO::FETCH_ASSOC);
	$ipid4_count = 0;
	if(!empty($wh_prod)){
		$ipid4_count = 1;
		$ipid4_wh_id = $wh_prod["id"];
		$ipid4_q = $wh_prod["pidq"];
		$ipid4_n = $wh_prod["pidn"];
		$ipid4_cost = $wh_prod["pidcost"];
		if($ipid4_n < $fc_ipid4n){
			$allow_production = 0;
			$no_production_msg3 .= "$ipid4_name, ";
		}
		$ipid4_n_display = $ipid4_n;
		$max_pnum_ipid4 = $ipid4_n / $fc_ipid4n;
		$total_opid1_qm += $fc_ipid4qm;
		$opid1_q += $ipid4_q * $fc_ipid4qm;
		$opid1_cost += $ipid4_cost * $fc_ipid4n;
	}else{
		$allow_production = 0;
		$no_production_msg4 = "Missing raw material(s).";
	}
}
$no_production_msg = '';
if($no_production_msg1) $no_production_msg .= '<img src="images/x.gif" /> '.$no_production_msg1.'<br />';
if($no_production_msg2) $no_production_msg .= '<img src="images/x.gif" /> '.$no_production_msg2.'<br />';
if($no_production_msg3){
	$no_production_msg3 = "Insufficient raw material(s): ".substr($no_production_msg3, 0, -2).".";
	$no_production_msg .= '<img src="images/x.gif" /> '.$no_production_msg3.'<br />';
}
if($no_production_msg4) $no_production_msg .= '<img src="images/x.gif" /> '.$no_production_msg4.'<br />';

// Initialize tech variables
if($fc_opid1usetech){
	$sql = "SELECT quality FROM firm_tech WHERE pid = '$fc_opid1' AND fid = '$eos_firm_id'";
	$opid1_techq = $db->query($sql)->fetchColumn();
	if(!$opid1_techq){
		// Allows quality 0 production for all items without research
		$opid1_techq = 0;
	}
	$opid1_tech_qm = 1 - $total_opid1_qm;
	$opid1_q_from_ipid = $opid1_q;
	$opid1_q_from_tech = $opid1_tech_qm * $opid1_techq;
	$opid1_q = $opid1_q_from_tech + $opid1_q;
	$quality_summary = $opid1_q_from_ipid.' ('.($total_opid1_qm*100).'%) from raw materials, '.$opid1_q_from_tech.' ('.($opid1_tech_qm*100).'%) from production technology.';
}else{
	$opid1_tech_qm = 0;
	$opid1_q_from_ipid = $opid1_q;
	$opid1_q_from_tech = 0;
	if($total_opid1_qm > 0){
		$quality_summary = $opid1_q_from_ipid.' ('.($total_opid1_qm*100).'%) from raw materials only, product quality is NOT affected by production technology.';
	}else{
		$quality_summary = 'The current product\'s quality remains at 0 and is NOT affected by raw materials or production technology.';
	}
}

// If max_pnum does not exist, simply disable production, else take the min of max_pnum by raw materials, 8 digits, or 7 days time
$time_limit = 168 * 3600;
if($starttime_override){
	$time_limit = max(0, $time_limit + time() - $starttime_override);
}
$max_pnum_time = floor($time_limit/$fc_timecost);
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<?php
		if($err){
			echo '<div class="err_msg">Error: '.$err_msg.'</div><br />';
		}
	?>
	<h3 class="vert_middle">Raw Materials for Producing <?= '<img src="/eos/images/prod/'.$opid1_filename.'.gif" alt="'.$opid1_name.'" title="'.$opid1_name.'" />' ?> <a style="cursor:pointer;" onclick="refreshThisDialog();" title="Refresh Dialog"><img src="/eos/images/refresh.png" title="Refresh Dialog" /></a><span id="refresh_notice" style="color:#008000"></span></h3>
	<?php
		function displayIpid($ipid, $ipid_name, $ipid_filename, $ipid_count, $ipid_q, $ipid_n_display, $fc_ipidqm, $fc_ipidn_display){
			echo '<div class="production_confirm_item">';
			if($ipid_count){
				echo '<img src="/eos/images/prod/large/',$ipid_filename,'.gif" title="',$ipid_name,' (Quality ',number_format($ipid_q,2,'.',''),', ',($fc_ipidqm*100),'% Q. Dependence): ',$ipid_n_display,' Available (Need Min. ',$fc_ipidn_display,')" style="margin-bottom:6px;" />';
				echo '<div class="vert_middle"><img src="/eos/images/star.gif" alt="Quality" title="Quality" /> ',number_format($ipid_q,2,'.',''),'<br />&nbsp;&nbsp;&nbsp;&nbsp;(',($fc_ipidqm*100),'%)</div>';
				echo '<div class="vert_middle"><img src="/eos/images/box.png" alt="Quantity" title="Quantity" /> ',number_format_readable($ipid_n_display),'</div>';
			}else{
				echo '<img src="/eos/images/prod/large/',$ipid_filename,'.gif" title="',$ipid_name,' (',($fc_ipidqm*100),'% Q. Dependence): Not Available (Need Min. ',$fc_ipidn_display,')" style="margin-bottom:6px;" />';
				echo '<div class="vert_middle"><img src="/eos/images/star.gif" alt="Quality" title="Quality" /><span style="color:#ff0000;"> N/A</span><br />&nbsp;&nbsp;&nbsp;&nbsp;(',($fc_ipidqm*100),'%)</div>';
				echo '<div class="vert_middle"><img src="/eos/images/box.png" alt="Quantity" title="Quantity" /><span style="color:#ff0000;"> N/A</span></div>';
				echo '<a href="/eos/market.php?view_type=prod&view_type_id=',$ipid,'" title="ctrl+click to open in new tab" ><input type="button" class="bigger_input" value="B2B" /></a><br />';
			}
			echo '</div>';
		}
		if($fc_ipid1n){
			displayIpid($fc_ipid1, $ipid1_name, $ipid1_filename, $ipid1_count, $ipid1_q, $ipid1_n_display, $fc_ipid1qm, $fc_ipid1n_display);
		}
		if($fc_ipid2n){
			displayIpid($fc_ipid2, $ipid2_name, $ipid2_filename, $ipid2_count, $ipid2_q, $ipid2_n_display, $fc_ipid2qm, $fc_ipid2n_display);
		}
		if($fc_ipid3n){
			displayIpid($fc_ipid3, $ipid3_name, $ipid3_filename, $ipid3_count, $ipid3_q, $ipid3_n_display, $fc_ipid3qm, $fc_ipid3n_display);
		}
		if($fc_ipid4n){
			displayIpid($fc_ipid4, $ipid4_name, $ipid4_filename, $ipid4_count, $ipid4_q, $ipid4_n_display, $fc_ipid4qm, $fc_ipid4n_display);
		}
	?>
	<div class="clearer no_select">&nbsp;</div>
	<?php
		if($allow_production){
	?>
		<script type="text/javascript">
			var pnum, pnum_max, unit_cost, pnum_max_base, pnum_max_comp_1, pnum_max_comp_2;
			var opid1_value = <?= $opid1_value ?>;
			var opid1_cost = <?= $opid1_cost ?>;
			var pnum_max_ipid1 = <?= $max_pnum_ipid1 ?>;
			var pnum_max_ipid2 = <?= $max_pnum_ipid2 ?>;
			var pnum_max_ipid3 = <?= $max_pnum_ipid3 ?>;
			var pnum_max_ipid4 = <?= $max_pnum_ipid4 ?>;
			var pnum_req_ipid1 = <?= $fc_ipid1n ?>;
			var pnum_req_ipid2 = <?= $fc_ipid2n ?>;
			var pnum_req_ipid3 = <?= $fc_ipid3n ?>;
			var pnum_req_ipid4 = <?= $fc_ipid4n ?>;
			var pnum_max_cash = <?= $max_pnum_cash ?>;
			var pnum_max_time = <?= $max_pnum_time ?>;
			var pnum_max_limit = 99999999999999;
			var unit_cost_adj = Math.pow(opid1_value, 0.5)/10000;

			if(pnum_req_ipid4){
				pnum_max_base = Math.min(pnum_max_ipid1, pnum_max_ipid2, pnum_max_ipid3, pnum_max_ipid4, pnum_max_time, pnum_max_cash);
			}else if(pnum_req_ipid3){
				pnum_max_base = Math.min(pnum_max_ipid1, pnum_max_ipid2, pnum_max_ipid3, pnum_max_time, pnum_max_cash);
			}else if(pnum_req_ipid2){
				pnum_max_base = Math.min(pnum_max_ipid1, pnum_max_ipid2, pnum_max_time, pnum_max_cash);
			}else if(pnum_req_ipid1){
				pnum_max_base = Math.min(pnum_max_ipid1, pnum_max_time, pnum_max_cash);
			}else{
				pnum_max_base = Math.min(pnum_max_time, pnum_max_cash);
			}

			//Calculate pnum_max
			pnum_max_comp_1 = pnum_max_base;
			unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_max_base * unit_cost_adj, 0.25);
			
			pnum_max_comp_2 = pnum_max_base / unit_cost;
			var i = 0;
			while(i < 10 && Math.floor(pnum_max_comp_2) > pnum_max_comp_1){
				i++;
				pnum_max_comp_1 = pnum_max_comp_2;
				unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_max_comp_1 * unit_cost_adj, 0.25);
				pnum_max_comp_2 = pnum_max_base / unit_cost;
			}
			pnum_max = Math.min(Math.floor(pnum_max_comp_2), pnum_max_limit);
			

			function pnumAdd1(){
				pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
				if(pnum < pnum_max){
					pnum = pnum + 1;
					document.getElementById('pnum').value = pnum;
					checkPnum();
				}
			}
			function pnumSubtract1(){
				pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
				if(pnum > 0){
					pnum = pnum - 1;
					document.getElementById('pnum').value = pnum;
					checkPnum();
				}
			}
			function pnumMax(){
				pnum = pnum_max;
				document.getElementById('pnum').value = pnum;
				checkPnum();
			}
			function checkPtime(){
				var pTime = Math.min(hms2sec(document.getElementById('ptime').value) + 1, 604800);
				if(pTime > 0){
					var pnum_time_base = pTime / <?= $fc_timecost ?>;
					var pnum_time_comp_1 = pnum_time_base;
					unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_time_base * unit_cost_adj, 0.25);
					var pnum_time_comp_2 = pnum_time_base / unit_cost;
					var i = 0;
					while(i < 10 && Math.floor(pnum_time_comp_2) > pnum_time_comp_1){
						i++;
						pnum_time_comp_1 = pnum_time_comp_2;
						unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_time_comp_1 * unit_cost_adj, 0.25);
						pnum_time_comp_2 = pnum_time_base / unit_cost;
					}
					document.getElementById('pnum').value = Math.floor(pnum_time_comp_2);
					checkPnum();
				}
			}
			function checkPnum(forced){
				pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
				if(pnum > 0){
					if(pnum > pnum_max){
						pnum = pnum_max;
					}
					var i, e;
					var unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum * unit_cost_adj, 0.25);
					var fpc_cost = <?= $fc_cost ?>, fpc_timecost = <?= $fc_timecost ?>, fpc_totalcost = pnum * fpc_cost * unit_cost;
					var fpc_totaltimecost_uf = pnum * unit_cost * fpc_timecost;
					var fpc_totaltimecost = sec2hms(fpc_totaltimecost_uf);

					jQuery('#fpc_total_cost').html('$' + formatNum(fpc_totalcost/100, 2));
					if(pnum + 1 > pnum_max_cash / unit_cost){
						document.getElementById('fpc_total_cost').style.color="#FF0000";
					}else{
						document.getElementById('fpc_total_cost').style.color="#003300";
					}
					jQuery('#fpc_unit_cost').html('$' + formatNum(opid1_cost * unit_cost/100, 2));
					jQuery('#fpc_total_time').html(fpc_totaltimecost);
					
					var temp_ptime = hms2sec(document.getElementById('ptime').value);
					if(fpc_totaltimecost_uf - temp_ptime > 3 || fpc_totaltimecost_uf - temp_ptime < -3){
						document.getElementById('ptime').value = fpc_totaltimecost;
					}
					if(pnum + 1 > pnum_max_time / unit_cost){
						document.getElementById('fpc_total_time').style.color="#FF0000";
					}else{
						document.getElementById('fpc_total_time').style.color="#003300";
					}
					if(document.getElementById('pnum').value != pnum){
						document.getElementById('pnum').value = pnum;
					}
					jQuery("#slider_target").slider("value", pnum);

					if(pnum_req_ipid1){
						var ipid1n_req = Math.ceil(pnum_req_ipid1 * unit_cost * pnum);
						jQuery('#total_ipid1n').html(formatNum(ipid1n_req));
						if(pnum >= Math.floor(pnum_max_ipid1 / unit_cost)){
							document.getElementById('total_ipid1n').style.color="#FF0000";
						}else{
							document.getElementById('total_ipid1n').style.color="#003300";
						}
					}
					if(pnum_req_ipid2){
						var ipid2n_req = Math.ceil(pnum_req_ipid2 * unit_cost * pnum);
						jQuery('#total_ipid2n').html(formatNum(ipid2n_req));
						if(pnum >= Math.floor(pnum_max_ipid2 / unit_cost)){
							document.getElementById('total_ipid2n').style.color="#FF0000";
						}else{
							document.getElementById('total_ipid2n').style.color="#003300";
						}
					}
					if(pnum_req_ipid3){
						var ipid3n_req = Math.ceil(pnum_req_ipid3 * unit_cost * pnum);
						jQuery('#total_ipid3n').html(formatNum(ipid3n_req));
						if(pnum >= Math.floor(pnum_max_ipid3 / unit_cost)){
							document.getElementById('total_ipid3n').style.color="#FF0000";
						}else{
							document.getElementById('total_ipid3n').style.color="#003300";
						}
					}
					if(pnum_req_ipid4){
						var ipid4n_req = Math.ceil(pnum_req_ipid4 * unit_cost * pnum);
						jQuery('#total_ipid4n').html(formatNum(ipid4n_req));
						if(pnum >= Math.floor(pnum_max_ipid4 / unit_cost)){
							document.getElementById('total_ipid4n').style.color="#FF0000";
						}else{
							document.getElementById('total_ipid4n').style.color="#003300";
						}
					}
				}else{
					if(pnum || forced){
						pnum = 0;
						jQuery("#slider_target").slider("value", pnum);
						jQuery('#fpc_total_cost').html('$0');
						jQuery('#fpc_unit_cost').html('$0');
						jQuery('#fpc_total_time').html('00:00:00');
						document.getElementById('ptime').value = '0';
						document.getElementById('pnum').value = '0';
						if(pnum_req_ipid1){
							jQuery('#total_ipid1n').html('0');
						}
						if(pnum_req_ipid2){
							jQuery('#total_ipid2n').html('0');
						}
						if(pnum_req_ipid3){
							jQuery('#total_ipid3n').html('0');
						}
						if(pnum_req_ipid4){
							jQuery('#total_ipid4n').html('0');
						}
					}
				}
			}
			function productionStart(){
				pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
				jqDialogInit('factories-production-start.php', {
					ffid : <?= $ffid ?>,
					fcid : <?= $fcid ?>,
					pnum : pnum,
					ipid1_wh_id : <?= $ipid1_wh_id ?>,
					ipid2_wh_id : <?= $ipid2_wh_id ?>,
					ipid3_wh_id : <?= $ipid3_wh_id ?>,
					ipid4_wh_id : <?= $ipid4_wh_id ?>
				});
			}
			function refreshThisDialog(){
				jqDialogInit('factories-production-confirm.php', {
					ffid : <?= $ffid ?>,
					fcid : '<?= $fcid ?>'
				}, function(){
					jQuery('#refresh_notice').html(' &#x2714; Refresh Success');
					setTimeout(function(){
						jQuery('#refresh_notice').html('');
					}, 3000);
				});
			}
		</script>
	<form id="slider_form_1" class="default_slider_form" onsubmit="productionStart();return false;">
		<h3 style="vertical-align:middle;">Units or Time to Produce</h3>
		<div style="line-height:48px;" class="vert_middle">
			<div style="float:left;width:60px;"><img class="slider_button_subtract_one" src="images/slider_left.gif" style="cursor:pointer;" onClick="pnumSubtract1();" /></div>
			<div id="slider_target" class="slider_target"></div>
			<div style="float:left;width:60px;"><img class="slider_button_add_one" src="images/slider_right.gif" style="cursor:pointer;" onClick="pnumAdd1();" /></div>
			<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="pnumMax();" /></div>
			<div style="float:left;margin-left:70px;width:200px;line-height:30px;" class="vert_middle">
				Units <input id="pnum" type="text" style="border: 2px solid #997755;text-align:center;" size="14" maxlength="14" onkeyup="checkPnum()" /> (#)<br />
				Time <input id="ptime" type="text" style="border: 2px solid #997755;text-align:center;" size="9" maxlength="9" onkeyup="checkPtime()" /> (hh:mm:ss)
			</div>
			<div class="clearer"></div>
		</div>
		<br />
		<h3>Production Cost</h3>
		<?php
			echo '<img src="/eos/images/time.gif" alt="Time" title="Time" /> <span id="fpc_total_time">N/A</span> &nbsp; ';
			echo '<img src="/eos/images/money.gif" alt="Cash" title="Cash" /> <span id="fpc_total_cost">N/A</span> &nbsp; ';
			if($fc_ipid1n){
				echo '<img src="/eos/images/prod/'.$ipid1_filename.'.gif" alt="'.$ipid1_name.'" title="'.$ipid1_name.'" /> <span id="total_ipid1n">N/A</span> &nbsp; ';
				if($fc_ipid2n){
					echo '<img src="/eos/images/prod/'.$ipid2_filename.'.gif" alt="'.$ipid2_name.'" title="'.$ipid2_name.'" /> <span id="total_ipid2n">N/A</span> &nbsp; ';
					if($fc_ipid3n){
						echo '<img src="/eos/images/prod/'.$ipid3_filename.'.gif" alt="'.$ipid3_name.'" title="'.$ipid3_name.'" /> <span id="total_ipid3n">N/A</span> &nbsp; ';
						if($fc_ipid4n){
							echo '<img src="/eos/images/prod/'.$ipid4_filename.'.gif" alt="'.$ipid4_name.'" title="'.$ipid4_name.'" /> <span id="total_ipid4n">N/A</span>';
						}
					}
				}
			}
			echo '<br /><br />';
		?>
		<img style="float:right;cursor:pointer;" src="images/button-produce-big.gif" id="production_start_button" title="Start Production" onClick="productionStart();" />
		<br /><h3>Final Quality: <?= number_format($opid1_q,2,'.','') ?> <a class="info"><img style="vertical-align:middle;margin: 0 0 4px 0;" src="images/info.png" /><span><?= $quality_summary ?></span></a></h3>
		<h3>Unit Cost: <span id="fpc_unit_cost">N/A</span> <a class="info"><img style="vertical-align:middle;margin: 0 0 4px 0;" src="images/info.png" /><span>Including raw material, salary (<?= number_format(100 * $opid1_cost_salary / $opid1_cost, 2, '.', ',') ?>%) and building maintenance (<?= number_format(100 * $opid1_cost_mt / $opid1_cost, 2, '.', ',') ?>%) costs. Research cost not included.</span></a></h3>
		<div style="display:none;"><input type="submit" value="submit" /></div>
	</form>
	
	<script type="text/javascript">
		jQuery("#slider_target").slider({
			value: 0,
			min: 0,
			max: pnum_max,
			slide: function( event, ui ){
				jQuery("#pnum").val(ui.value);
				checkPnum(1);
			},
			create: function(event, ui){
				jQuery("#pnum").val(Math.min(<?= $default_pnum ?>, pnum_max));
				checkPnum(1);
			}
		});
	</script>
	<?php
		}else{
			if(!$ctrl_fact_produce){
				echo '<h3>Cannot Start Production</h3>';
				echo '<div class="vert_middle" style="color:#ff0000;font-size: 16px;line-height:180%;">You are not authorized to give production orders.</div>';
			}else{
				echo '<h3>Cannot Start Production</h3>';
				echo '<div class="vert_middle" style="color:#ff0000;font-size: 16px;line-height:180%;">'.$no_production_msg.'</div>';
			}
		}
	?>
	<br />
	<a class="jqDialog" href="factories-production.php?ffid=<?= $ffid ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>

