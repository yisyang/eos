<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(isset($_POST['type'])){
	$type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
}

if($action == 'refresh_queue'){
	if(!$ctrl_bldg_view){
		echo '{"success" : 0, "msg" : "Unauthorized."}';
		exit();
	}
	$resp = array();
	$timenow = time();
	$resp['timenow'] = $timenow;
	if($type == 'fact' || $type == 'all'){
		$sql = "SELECT queue_prod.id, queue_prod.ffid, queue_prod.opid1, queue_prod.opid1q, queue_prod.opid1n, queue_prod.starttime, queue_prod.endtime, firm_fact.fact_name, list_prod.name, list_prod.has_icon FROM queue_prod LEFT JOIN list_prod ON queue_prod.opid1 = list_prod.id LEFT JOIN firm_fact ON queue_prod.ffid = firm_fact.id WHERE queue_prod.fid = $eos_firm_id AND queue_prod.endtime >= $timenow ORDER BY firm_fact.slot ASC, queue_prod.starttime ASC";
		$queue_prod = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$resp['update_queue_prod'] = 1;
		$resp['queue_prod'] = $queue_prod;
	}
	if($type == 'rnd' || $type == 'all'){
		$sql = "SELECT queue_res.id, queue_res.frid, queue_res.pid, queue_res.newlevel, queue_res.starttime, queue_res.endtime, firm_rnd.rnd_name, list_prod.name, list_prod.has_icon FROM queue_res LEFT JOIN list_prod ON queue_res.pid = list_prod.id LEFT JOIN firm_rnd ON queue_res.frid = firm_rnd.id WHERE queue_res.fid = $eos_firm_id AND queue_res.endtime >= $timenow ORDER BY firm_rnd.slot ASC, queue_res.starttime ASC";
		$queue_res = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$resp['update_queue_res'] = 1;
		$resp['queue_res'] = $queue_res;
	}
	
	$resp['success'] = 1;
	echo json_encode($resp);
}
else if($action == 'cancel_queue'){
	$queue_id = filter_var($_POST['queue_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$queue_id){
		echo '{"success" : 0, "msg" : "Missing Queue Id."}';
		exit();
	}
	if($type == 'fact'){
		if(!$ctrl_fact_cancel){
			echo '{"success" : 0, "msg" : "Unauthorized."}';
			exit();
		}

		// First check fpid belongs to eos_firm_id
		$sql = "SELECT queue_prod.*, firm_fact.slot FROM queue_prod LEFT JOIN firm_fact ON queue_prod.ffid = firm_fact.id WHERE queue_prod.id = '$queue_id' AND queue_prod.fid = $eos_firm_id";
		$queue_item_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($queue_item_prod)){
			echo '{"success" : 0, "msg" : "Queue item cannot be found. Perhaps it is done?"}';
			exit();
		}

		// Find out what's been produced and how much are done
		$timenow = time();
		$slot_affected = 0;
		$slot = $queue_item_prod["slot"];
		$ffid = $queue_item_prod["ffid"];
		$qp_fc_id = $queue_item_prod["fcid"];
		$qp_opid1_id = $queue_item_prod["opid1"];
		$sql = "SELECT name, value FROM list_prod WHERE id = '$qp_opid1_id'";
		$qp_opid1 = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$qp_opid1_name = $qp_opid1["name"];
		$qp_opid1_value_sqrt = pow($qp_opid1["value"], 0.5);
		$qp_opid1_q = $queue_item_prod["opid1q"];
		$qp_opid1_n = $queue_item_prod["opid1n"];
		$unit_cost = 0.5 + 0.5 / pow(1 + $qp_opid1_n * $qp_opid1_value_sqrt / 10000, 0.25);
		$qp_opid1_cost = $queue_item_prod["opid1cost"];
		$sql = "SELECT * FROM list_fact_choices WHERE id = '$qp_fc_id'";
		$fact_choice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$qp_cost = $fact_choice["cost"];
		$qp_ipid1 = $fact_choice["ipid1"];
		$qp_ipid2 = $fact_choice["ipid2"];
		$qp_ipid3 = $fact_choice["ipid3"];
		$qp_ipid4 = $fact_choice["ipid4"];
		$qp_ipid1_q = $queue_item_prod["ipid1q"];
		$qp_ipid2_q = $queue_item_prod["ipid2q"];
		$qp_ipid3_q = $queue_item_prod["ipid3q"];
		$qp_ipid4_q = $queue_item_prod["ipid4q"];
		$qp_ipid1_n = 0+$fact_choice["ipid1n"];
		$qp_ipid2_n = 0+$fact_choice["ipid2n"];
		$qp_ipid3_n = 0+$fact_choice["ipid3n"];
		$qp_ipid4_n = 0+$fact_choice["ipid4n"];
		$qp_ipid1_cost = 0+$queue_item_prod["ipid1cost"];
		$qp_ipid2_cost = 0+$queue_item_prod["ipid2cost"];
		$qp_ipid3_cost = 0+$queue_item_prod["ipid3cost"];
		$qp_ipid4_cost = 0+$queue_item_prod["ipid4cost"];
		$qp_starttime = $queue_item_prod["starttime"];
		$qp_endtime = $queue_item_prod["endtime"];
		$qp_totaltime = $qp_endtime - $qp_starttime;
		if($qp_starttime > $timenow){
			$qp_opid1_produced = 0;
			$unit_cost_produced = 1;
			$qp_opid1_np = $qp_opid1_n;
			$qp_cost_refund = $qp_cost * $qp_opid1_n * $unit_cost;
			$qp_opid1_cost = 0;
		}else{
			$slot_affected = 1;
			$qp_opid1_produced = floor($qp_opid1_n * ($timenow - $qp_starttime)/$qp_totaltime);
			$unit_cost_produced = 0.5 + 0.5 / pow(1 + $qp_opid1_produced * $qp_opid1_value_sqrt / 10000, 0.25);
			$qp_opid1_np = $qp_opid1_n - $qp_opid1_produced;
			$qp_cost_refund = floor($qp_cost * ($qp_opid1_n * $unit_cost - $qp_opid1_produced * $unit_cost_produced));

			// Update opid1 cost
			$qp_opid1_cost = $qp_opid1_cost * $unit_cost_produced / $unit_cost;
		}

		// Delete from production queue
		$sql = "DELETE FROM queue_prod WHERE id = '$queue_id'";
		$result = $db->query($sql);
		if(!$result){
			echo '{"success" : 0, "msg" : "DB failed"}';
			exit();
		}

		// Move everything else up in queue
		if($qp_starttime < $timenow){
			$sql = "UPDATE queue_prod SET endtime = endtime + $timenow - $qp_endtime, starttime = starttime + $timenow - $qp_endtime WHERE fid = $eos_firm_id AND ffid = $ffid AND starttime >= $qp_endtime";
			$db->query($sql);
		}else{
			$sql = "UPDATE queue_prod SET endtime = endtime + $qp_starttime - $qp_endtime, starttime = starttime + $qp_starttime - $qp_endtime WHERE fid = $eos_firm_id AND ffid = $ffid AND starttime >= $qp_endtime";
			$db->query($sql);
		}
		
		$sql_wh_pid = $db->prepare("SELECT COUNT(*) AS wh_count, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = :pid AND fid = :fid");
		$sql_wh_insert = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (:fid, :pid, :pidq, :pidn, :pidcost)");
		$sql_wh_update = $db->prepare("UPDATE firm_wh SET pidcost = :pidcost, pidn = :pidn, pidq = :pidq WHERE id = :id");
		if($qp_opid1_produced > 0){
			// Check if pid with pidq already exists in warehouse, add already finished opid to warehouse
			$sql_wh_pid->execute(array(':pid' => $qp_opid1_id, ':fid' => $eos_firm_id));
			$wh_opid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
			if($wh_opid1['wh_count']){
				// Update warehouse
				$qp_opid1_wh_id = $wh_opid1["id"];
				$qp_opid1_wh_n = $wh_opid1["pidn"];
				$qp_opid1_wh_q = $wh_opid1["pidq"];
				$qp_opid1_wh_cost = $wh_opid1["pidcost"];
				$qp_opid1_n_new = $qp_opid1_wh_n + $qp_opid1_produced;
				$qp_opid1_q_new = ($qp_opid1_wh_n * $qp_opid1_wh_q + $qp_opid1_produced * $qp_opid1_q)/$qp_opid1_n_new;
				$qp_opid1_cost_new = round(($qp_opid1_wh_n * $qp_opid1_wh_cost + $qp_opid1_produced * $qp_opid1_cost)/$qp_opid1_n_new);
				
				$sql_wh_update->execute(array(':id' => $qp_opid1_wh_id, ':pidq' => $qp_opid1_q_new, ':pidn' => $qp_opid1_n_new, ':pidcost' => $qp_opid1_cost_new));
			}else{
				$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $qp_opid1_id, ':pidq' => $qp_opid1_q, ':pidn' => $qp_opid1_produced, ':pidcost' => $qp_opid1_cost));
			}		
		}
		
		// Give refund to firm
		$sql = "INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES ($eos_firm_id, 0, $qp_opid1_id, $qp_opid1_np, $qp_opid1_q, $qp_cost_refund, 'Production', NOW())";
		$db->query($sql);
		$sql = "UPDATE firms SET cash = cash + $qp_cost_refund WHERE id=$eos_firm_id";
		$db->query($sql);
		
		// Calculate and add unused materials to warehouse
		if($qp_ipid1){
			$qp_ipid1_unused = floor($qp_ipid1_n * ($qp_opid1_n * $unit_cost - $qp_opid1_produced * $unit_cost_produced));
			if($qp_ipid1_unused > 0){
				$sql_wh_pid->execute(array(':pid' => $qp_ipid1, ':fid' => $eos_firm_id));
				$wh_ipid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
				if($wh_ipid1['wh_count']){
					$qp_ipid1_wh_id = $wh_ipid1["id"];
					$qp_ipid1_wh_n = $wh_ipid1["pidn"];
					$qp_ipid1_wh_q = $wh_ipid1["pidq"];
					$qp_ipid1_wh_cost = $wh_ipid1["pidcost"];
					$qp_ipid1_n_new = $qp_ipid1_wh_n + $qp_ipid1_unused;
					$qp_ipid1_q_new = ($qp_ipid1_wh_n * $qp_ipid1_wh_q + $qp_ipid1_unused * $qp_ipid1_q)/$qp_ipid1_n_new;
					$qp_ipid1_cost_new = round(($qp_ipid1_wh_n * $qp_ipid1_wh_cost + $qp_ipid1_unused * $qp_ipid1_cost)/$qp_ipid1_n_new);

					$sql_wh_update->execute(array(':id' => $qp_ipid1_wh_id, ':pidq' => $qp_ipid1_q_new, ':pidn' => $qp_ipid1_n_new, ':pidcost' => $qp_ipid1_cost_new));
				}else{
					$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $qp_ipid1, ':pidq' => $qp_ipid1_q, ':pidn' => $qp_ipid1_unused, ':pidcost' => $qp_ipid1_cost));
				}
			}
		}
		if($qp_ipid2){
			$qp_ipid2_unused = floor($qp_ipid2_n * ($qp_opid1_n * $unit_cost - $qp_opid1_produced * $unit_cost_produced));
			if($qp_ipid2_unused > 0){
				$sql_wh_pid->execute(array(':pid' => $qp_ipid2, ':fid' => $eos_firm_id));
				$wh_ipid2 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
				if($wh_ipid2['wh_count']){
					$qp_ipid2_wh_id = $wh_ipid2["id"];
					$qp_ipid2_wh_n = $wh_ipid2["pidn"];
					$qp_ipid2_wh_q = $wh_ipid2["pidq"];
					$qp_ipid2_wh_cost = $wh_ipid2["pidcost"];
					$qp_ipid2_n_new = $qp_ipid2_wh_n + $qp_ipid2_unused;
					$qp_ipid2_q_new = ($qp_ipid2_wh_n * $qp_ipid2_wh_q + $qp_ipid2_unused * $qp_ipid2_q)/$qp_ipid2_n_new;
					$qp_ipid2_cost_new = round(($qp_ipid2_wh_n * $qp_ipid2_wh_cost + $qp_ipid2_unused * $qp_ipid2_cost)/$qp_ipid2_n_new);

					$sql_wh_update->execute(array(':id' => $qp_ipid2_wh_id, ':pidq' => $qp_ipid2_q_new, ':pidn' => $qp_ipid2_n_new, ':pidcost' => $qp_ipid2_cost_new));
				}else{
					$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $qp_ipid2, ':pidq' => $qp_ipid2_q, ':pidn' => $qp_ipid2_unused, ':pidcost' => $qp_ipid2_cost));
				}
			}
		}
		if($qp_ipid3){
			$qp_ipid3_unused = floor($qp_ipid3_n * ($qp_opid1_n * $unit_cost - $qp_opid1_produced * $unit_cost_produced));
			if($qp_ipid3_unused > 0){
				$sql_wh_pid->execute(array(':pid' => $qp_ipid3, ':fid' => $eos_firm_id));
				$wh_ipid3 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
				if($wh_ipid3['wh_count']){
					$qp_ipid3_wh_id = $wh_ipid3["id"];
					$qp_ipid3_wh_n = $wh_ipid3["pidn"];
					$qp_ipid3_wh_q = $wh_ipid3["pidq"];
					$qp_ipid3_wh_cost = $wh_ipid3["pidcost"];
					$qp_ipid3_n_new = $qp_ipid3_wh_n + $qp_ipid3_unused;
					$qp_ipid3_q_new = ($qp_ipid3_wh_n * $qp_ipid3_wh_q + $qp_ipid3_unused * $qp_ipid3_q)/$qp_ipid3_n_new;
					$qp_ipid3_cost_new = round(($qp_ipid3_wh_n * $qp_ipid3_wh_cost + $qp_ipid3_unused * $qp_ipid3_cost)/$qp_ipid3_n_new);

					$sql_wh_update->execute(array(':id' => $qp_ipid3_wh_id, ':pidq' => $qp_ipid3_q_new, ':pidn' => $qp_ipid3_n_new, ':pidcost' => $qp_ipid3_cost_new));
				}else{
					$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $qp_ipid3, ':pidq' => $qp_ipid3_q, ':pidn' => $qp_ipid3_unused, ':pidcost' => $qp_ipid3_cost));
				}
			}
		}
		if($qp_ipid4){
			$qp_ipid4_unused = floor($qp_ipid4_n * ($qp_opid1_n * $unit_cost - $qp_opid1_produced * $unit_cost_produced));
			if($qp_ipid4_unused > 0){
				$sql_wh_pid->execute(array(':pid' => $qp_ipid4, ':fid' => $eos_firm_id));
				$wh_ipid4 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
				if($wh_ipid4['wh_count']){
					$qp_ipid4_wh_id = $wh_ipid4["id"];
					$qp_ipid4_wh_n = $wh_ipid4["pidn"];
					$qp_ipid4_wh_q = $wh_ipid4["pidq"];
					$qp_ipid4_wh_cost = $wh_ipid4["pidcost"];
					$qp_ipid4_n_new = $qp_ipid4_wh_n + $qp_ipid4_unused;
					$qp_ipid4_q_new = ($qp_ipid4_wh_n * $qp_ipid4_wh_q + $qp_ipid4_unused * $qp_ipid4_q)/$qp_ipid4_n_new;
					$qp_ipid4_cost_new = round(($qp_ipid4_wh_n * $qp_ipid4_wh_cost + $qp_ipid4_unused * $qp_ipid4_cost)/$qp_ipid4_n_new);

					$sql_wh_update->execute(array(':id' => $qp_ipid4_wh_id, ':pidq' => $qp_ipid4_q_new, ':pidn' => $qp_ipid4_n_new, ':pidcost' => $qp_ipid4_cost_new));
				}else{
					$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $qp_ipid4, ':pidq' => $qp_ipid4_q, ':pidn' => $qp_ipid4_unused, ':pidcost' => $qp_ipid4_cost));
				}
			}
		}

		$resp = array('success' => 1, 'slot' => $slot, 'slot_affected' => $slot_affected, 'qp_opid1_produced' => $qp_opid1_produced, 'qp_opid1_name' => $qp_opid1_name, 'qp_opid1_q' => $qp_opid1_q);
		echo json_encode($resp);
		exit();
	}
	else if($type == 'rnd'){
		if(!$ctrl_rnd_cancel){
			echo '{"success" : 0, "msg" : "Unauthorized."}';
			exit();
		}

		// First check queue_id belongs to eos_firm_id
		$sql = "SELECT queue_res.*, firm_rnd.slot FROM queue_res LEFT JOIN firm_rnd ON queue_res.frid = firm_rnd.id WHERE queue_res.id = '$queue_id' AND queue_res.fid = '$eos_firm_id'";
		$queue_item_res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($queue_item_res)){
			echo '{"success" : 0, "msg" : "Queue item cannot be found. Perhaps it is done?"}';
			exit();
		}

		// Find out what's researching, research price, and if it's actually finished
		$timenow = time();
		$frid = $queue_item_res["frid"];
		$slot = $queue_item_res["slot"];
		$qr_pid = $queue_item_res["pid"];
		$qr_newlevel = $queue_item_res["newlevel"];
		$sql = "SELECT name, res_cost, tech_avg FROM list_prod WHERE id = '$qr_pid'";
		$qr_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$qr_pid_name = $qr_prod["name"];
		$qr_pid_res_basecost = $qr_prod["res_cost"];
		$qr_pid_tech_avg = $qr_prod["tech_avg"];
		$qr_pid_res_cost = max(10000, $qr_pid_res_basecost * pow(1.2, $qr_newlevel - 0.25 * $qr_pid_tech_avg));
		$qr_starttime = $queue_item_res["starttime"];
		$slot_affected = 0;
		if($qr_starttime < $timenow) $slot_affected = 1;
		$qr_endtime = $queue_item_res["endtime"];
		$qr_totaltime = $qr_endtime - $qr_starttime;
		$qr_remaining = $qr_endtime - $timenow;
		$qr_remaining_rel = min($qr_endtime - $qr_starttime, $qr_remaining);
		if($qr_remaining_rel < 1){
			echo '{"success" : 1, "slot" : '.$slot.', "refund" : 0}';
			exit();
		}
		// Calculate research refund, based on
		// res_cost * (0.5 + 0.5 * remaining/totaltime)
		$qr_research_refund = $qr_pid_res_cost * (0.5 + 0.5 * min(1,($qr_remaining / $qr_totaltime)));

		// Delete from researching queue
		$sql = "DELETE FROM queue_res WHERE id = '$queue_id'";
		$result = $db->query($sql);
		if(!$result){
			echo '{"success" : 0, "msg" : "DB failed"}';
			exit();
		}

		// Move everything else up in queue including the subject, works with forbidden 2nd building research, does not add extra 15 s for hurry
		$sql = "UPDATE queue_res SET endtime = endtime - $qr_remaining_rel, starttime = starttime - $qr_remaining_rel WHERE fid = $eos_firm_id AND frid = $frid AND starttime >= $qr_starttime";
		$db->query($sql);
		
		// Also cancel anything that depends on this research
		$sql = "SELECT id, newlevel, frid, starttime, endtime FROM queue_res WHERE fid = $eos_firm_id AND pid = $qr_pid AND newlevel > $qr_newlevel ORDER BY starttime DESC";
		$dep_queues = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$dep_queues_count = count($dep_queues);
		if($dep_queues_count){
			// Delete them first before the variables get changed
			$sql = "DELETE FROM queue_res WHERE fid = $eos_firm_id AND pid = $qr_pid AND newlevel > $qr_newlevel";
			$db->query($sql);
			foreach($dep_queues as $dep_res){
				$qr_frid = $dep_res["frid"];
				$qr_newlevel = $dep_res["newlevel"];
				$qr_starttime = $dep_res["starttime"];
				$qr_endtime = $dep_res["endtime"];
				$qr_pid_res_cost = max(10000, $qr_pid_res_basecost * pow(1.2, $qr_newlevel - 0.25 * $qr_pid_tech_avg));
				$qr_research_refund += $qr_pid_res_cost;
				$sql = "UPDATE queue_res SET endtime = endtime + $qr_starttime - $qr_endtime, starttime = starttime + $qr_starttime - $qr_endtime WHERE fid = $eos_firm_id AND frid = $qr_frid AND starttime >= $qr_starttime";
				$db->query($sql);
			}
		}

		// Give research refund to firm
		$sql = "INSERT INTO log_revenue (fid, is_debit, pid, pidq, value, source, transaction_time) VALUES ($eos_firm_id, 0, $qr_pid, $qr_newlevel, $qr_research_refund, 'Research', NOW())";
		$db->query($sql);	
		$sql = "UPDATE firms SET cash = cash + $qr_research_refund WHERE id='$eos_firm_id'";
		$db->query($sql);

		$resp = array('success' => 1, 'slot' => $slot, 'slot_affected' => $slot_affected, 'refund' => $qr_research_refund);
		echo json_encode($resp);
		exit();
	}else{
		echo '{"success" : 0, "msg" : "Invalid queue type."}';
		exit();
	}
}
else if($action == 'hurry_queue'){
	$queue_id = filter_var($_POST['queue_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$queue_id){
		echo '{"success" : 0, "msg" : "Missing Queue Id."}';
		exit();
	}
	if($type == 'rnd'){
		if(!$ctrl_rnd_hurry){
			echo '{"success" : 0, "msg" : "Unauthorized."}';
			exit();
		}

		// First check queue_id belongs to eos_firm_id
		$sql = "SELECT queue_res.*, firm_rnd.slot FROM queue_res LEFT JOIN firm_rnd ON queue_res.frid = firm_rnd.id WHERE queue_res.id = '$queue_id' AND queue_res.fid = '$eos_firm_id'";
		$queue_item_res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($queue_item_res)){
			echo '{"success" : 0, "msg" : "Queue item cannot be found. Perhaps it is done?"}';
			exit();
		}

		// Find out what's researching, research price, and if it's actually finished
		$frid = $queue_item_res["frid"];
		$slot = $queue_item_res["slot"];
		$qr_pid = $queue_item_res["pid"];
		$qr_newlevel = $queue_item_res["newlevel"];
		$sql = "SELECT name, res_cost, tech_avg FROM list_prod WHERE id = '$qr_pid'";
		$qr_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$qr_pid_name = $qr_prod["name"];
		$qr_pid_res_basecost = $qr_prod["res_cost"];
		$qr_pid_tech_avg = $qr_prod["tech_avg"];
		$qr_pid_res_cost = max(10000, $qr_pid_res_basecost * pow(1.2, $qr_newlevel - 0.25 * $qr_pid_tech_avg));
		$qr_starttime = $queue_item_res["starttime"];
		$slot_affected = 0;
		if($qr_starttime < $timenow) $slot_affected = 1;
		$qr_endtime = $queue_item_res["endtime"];
		$qr_totaltime = $qr_endtime - $qr_starttime;
		$timenow = time();
		$qr_remaining = $qr_endtime - $timenow;
		$qr_remaining_rel = min($qr_endtime - $qr_starttime, $qr_remaining);
		if($qr_remaining_rel <= 15){
			echo '{"success" : 0, "msg" : "Documenting research results... please be patient."}';
			exit();
		}

		// Verify dependent research
		$sql = "SELECT COUNT(*) FROM queue_res WHERE fid = $eos_firm_id AND pid = $qr_pid AND newlevel < $qr_newlevel AND endtime > $timenow";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			echo '{"success" : 0, "msg" : "Please first complete research on lower quality levels."}';
			exit();
		}

		$qr_left = min(1, $qr_remaining / $qr_totaltime);
		$qr_outsource_multiplier = $qr_left * $qr_left * 20 + 0.5;
		$qr_hurry_cost = floor($qr_outsource_multiplier * $qr_pid_res_cost);

		// Initialize Firm Cash
		$sql = "SELECT firms.cash FROM firms WHERE firms.id = $eos_firm_id";
		$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$firm_cash = $firm['cash'];
		if($firm_cash < $qr_hurry_cost){
			echo '{"success" : 0, "msg" : "Insufficient cash."}';
			exit();
		}
		if($ctrl_leftover_allowance < $qr_hurry_cost){
			echo '{"success" : 0, "msg" : "Cost exceeds your daily spending limit."}';
			exit();
		}

		// Deduct $ from firm
		$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
		$result = $query->execute(array(':cost' => $qr_hurry_cost, ':firm_id' => $eos_firm_id));
		$affected = $query->rowCount();
		if(!$result || !$affected){
			echo '{"success" : 0, "msg" : "Insufficient cash."}';
			exit();
		}

		// Move everything else up in queue including the subject, works with forbidden 2nd building research, adds 15 s hurry cost
		$sql = "UPDATE queue_res SET endtime = endtime + 15 - $qr_remaining_rel, starttime = starttime + 15 - $qr_remaining_rel WHERE fid = $eos_firm_id AND frid = $frid AND starttime >= $qr_starttime";
		$db->query($sql);

		// Log research
		$sql = "INSERT INTO log_revenue (fid, is_debit, pid, pidq, value, source, transaction_time) VALUES ($eos_firm_id, 1, $qr_pid, $qr_newlevel, $qr_hurry_cost, 'Research', NOW())";
		$db->query($sql);
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $qr_hurry_cost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);

		$resp = array('success' => 1, 'slot' => $slot, 'slot_affected' => $slot_affected, 'hurry_cost' => $qr_hurry_cost);
		echo json_encode($resp);
		exit();
	}else{
		echo '{"success" : 0, "msg" : "Invalid queue type."}';
		exit();
	}
}
else{
	echo '{"success" : 0, "msg" : "Action not defined."}';
}
?>