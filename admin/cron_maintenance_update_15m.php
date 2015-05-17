<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	require '../scripts/db/dbconnrjeos.php';
	
	$cron_report_msg = "";
	$cron_report_msg_level_2 = "";
	date_default_timezone_set('America/Los_Angeles');
	$timestart = microtime(1);
	function update_fifteen_min(){
		global $db, $cron_report_msg, $timeupdate_dt, $timestart;
		$timenow = time();
		$timeupdate = $timenow;
		$timeupdate_dt = date("Y-m-d H:i:s",$timeupdate);
		$timeupdate_tick = floor(($timenow - 1327104000)/900);
		$time_diff = intval(date("i",$timenow));
echo microtime(1) - $timestart.': Starting<br />';
		
		$sql = "SELECT value FROM world_var WHERE name = 'su_last_ran_fifteen'";
		$last_ran_hourly = 0 + $db->query($sql)->fetchColumn();
		$last_ran_dur = $timenow - $last_ran_hourly;
		if($last_ran_dur < 600){
			$cron_report_msg = "Server update had previously been ran in less than 15 min: ".$last_ran_dur;
			return false;
		}
		// Clear request counter
echo microtime(1) - $timestart.': Decaying Request Counters<br />';
		$sql = "UPDATE players SET in_jail = 999999999999 WHERE requests > 45000";
		$db->query($sql);
		$sql = "UPDATE players SET requests = 0.8 * requests";
		$db->query($sql);
		// Update all constructions
echo microtime(1) - $timestart.': Emptying Queues<br />';
echo microtime(1) - $timestart.': Adding New Factories<br />';
		$sql = "INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) SELECT queue_build.fid, queue_build.building_type_id, list_fact.name, queue_build.newsize, queue_build.building_slot FROM queue_build LEFT JOIN list_fact ON queue_build.building_type_id = list_fact.id WHERE queue_build.building_type = 'fact' AND queue_build.endtime <= '$timeupdate' AND ISNULL(queue_build.building_id)";
		$db->query($sql);
echo microtime(1) - $timestart.': Updating Existing Factories<br />';
		$sql = "UPDATE (SELECT queue_build.fid, queue_build.building_id, queue_build.newsize FROM queue_build WHERE queue_build.building_type = 'fact' AND queue_build.endtime <= '$timeupdate' AND !ISNULL(queue_build.building_id)) AS a LEFT JOIN firm_fact ON firm_fact.id = a.building_id SET firm_fact.size = a.newsize";
		$db->query($sql);
echo microtime(1) - $timestart.': Adding New Stores<br />';
		$sql = "INSERT INTO firm_store (fid, store_id, store_name, size, slot) SELECT queue_build.fid, queue_build.building_type_id, list_store.name, queue_build.newsize, queue_build.building_slot FROM queue_build LEFT JOIN list_store ON queue_build.building_type_id = list_store.id WHERE queue_build.building_type = 'store' AND queue_build.endtime <= '$timeupdate' AND ISNULL(queue_build.building_id)";
		$db->query($sql);
echo microtime(1) - $timestart.': Updating Existing Stores<br />';
		$sql = "UPDATE (SELECT queue_build.fid, queue_build.building_id, queue_build.newsize FROM queue_build WHERE queue_build.building_type = 'store' AND queue_build.endtime <= '$timeupdate' AND !ISNULL(queue_build.building_id)) AS a LEFT JOIN firm_store ON firm_store.id = a.building_id SET firm_store.size = a.newsize, firm_store.is_expanding = 0";
		$db->query($sql);
echo microtime(1) - $timestart.': Adding New RnD<br />';
		$sql = "INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) SELECT queue_build.fid, queue_build.building_type_id, list_rnd.name, queue_build.newsize, queue_build.building_slot FROM queue_build LEFT JOIN list_rnd ON queue_build.building_type_id = list_rnd.id WHERE queue_build.building_type = 'rnd' AND queue_build.endtime <= '$timeupdate' AND ISNULL(queue_build.building_id)";
		$db->query($sql);
echo microtime(1) - $timestart.': Updating Existing RnD<br />';
		$sql = "UPDATE (SELECT queue_build.fid, queue_build.building_id, queue_build.newsize FROM queue_build WHERE queue_build.building_type = 'rnd' AND queue_build.endtime <= '$timeupdate' AND !ISNULL(queue_build.building_id)) AS a LEFT JOIN firm_rnd ON firm_rnd.id = a.building_id SET firm_rnd.size = a.newsize";
		$db->query($sql);
		$sql = "DELETE FROM queue_build WHERE endtime <= '$timeupdate'";
		$db->query($sql);
		
echo microtime(1) - $timestart.': Moving Produced Items<br />';
		// Take care of all completed production
		$sql = "SELECT * FROM queue_prod WHERE endtime <= '$timeupdate'";
		$queue_prod = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($queue_prod as $qp_item){
			$list_fact_pc_id = $qp_item["id"];
			$list_fact_pc_firm_id = $qp_item["fid"];
			$list_fact_pc_opid1 = $qp_item["opid1"];
			$list_fact_pc_opid1_q = $qp_item["opid1q"];
			$list_fact_pc_opid1_n = $qp_item["opid1n"];
			$list_fact_pc_opid1_cost = $qp_item["opid1cost"];

			// Check if pid already exists in warehouse
			$sql = "SELECT id, pidn, pidq, pidcost FROM firm_wh WHERE pid = $list_fact_pc_opid1 AND fid = $list_fact_pc_firm_id";
			$firm_wh = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			if(!empty($firm_wh)){
				// Update warehouse
				$list_fact_pc_opid1_wh_id = $firm_wh["id"];
				$list_fact_pc_opid1_wh_n = $firm_wh["pidn"];
				$list_fact_pc_opid1_wh_q = $firm_wh["pidq"];
				$list_fact_pc_opid1_wh_cost = $firm_wh["pidcost"];
				$list_fact_pc_opid1_n_new = $list_fact_pc_opid1_wh_n + $list_fact_pc_opid1_n;
				$list_fact_pc_opid1_q_new = ($list_fact_pc_opid1_wh_n * $list_fact_pc_opid1_wh_q + $list_fact_pc_opid1_n * $list_fact_pc_opid1_q)/$list_fact_pc_opid1_n_new;
				$list_fact_pc_opid1_cost_new = round(($list_fact_pc_opid1_wh_n * $list_fact_pc_opid1_wh_cost + $list_fact_pc_opid1_n * $list_fact_pc_opid1_cost)/$list_fact_pc_opid1_n_new);
				$sql = "UPDATE firm_wh SET pidcost = $list_fact_pc_opid1_cost_new, pidn = $list_fact_pc_opid1_n_new, pidq = $list_fact_pc_opid1_q_new WHERE id = $list_fact_pc_opid1_wh_id";
			}else{
				// Insert into warehouse
				$sql = "INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES ($list_fact_pc_firm_id, $list_fact_pc_opid1, $list_fact_pc_opid1_q, $list_fact_pc_opid1_n, $list_fact_pc_opid1_cost)";
			}
			$db->query($sql);
		}
		$sql = "DELETE FROM queue_prod WHERE endtime <= '$timeupdate'";
		$db->query($sql);

echo microtime(1) - $timestart.': Documenting Finished Research<br />';
		// Take care of all completed research - possible to simplify to one line
		$sql = "SELECT * FROM queue_res WHERE endtime <= '$timeupdate'";
		$queue_res = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($queue_res as $qr_item){
			// Find out what's researching, and if any has been researched
			$list_rnd_rq_firm_id = $qr_item["fid"];
			$list_rnd_rq_pid = $qr_item["pid"];
			$list_rnd_rq_newlevel = $qr_item["newlevel"];
			// Give research level to firm, but first check whether or not the firm already has this tech
			$sql = "SELECT quality FROM firm_tech WHERE fid = $list_rnd_rq_firm_id AND pid = $list_rnd_rq_pid ORDER BY quality DESC";
			$firm_tech = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			if(!empty($firm_tech)){
				$list_rnd_rq_oldlevel = $firm_tech['quality'];
				if($list_rnd_rq_newlevel > $list_rnd_rq_oldlevel){
					$sql = "UPDATE firm_tech SET quality = $list_rnd_rq_newlevel, update_time = $timeupdate WHERE fid = $list_rnd_rq_firm_id AND pid = $list_rnd_rq_pid";
					$db->query($sql);
				}
			}else{
				$sql = "INSERT INTO firm_tech (fid, pid, quality, update_time) VALUES ($list_rnd_rq_firm_id, '$list_rnd_rq_pid', '$list_rnd_rq_newlevel', '$timeupdate')";
				$db->query($sql);
			}
		}
		$sql = "DELETE FROM queue_res WHERE endtime <= '$timeupdate'";
		$db->query($sql);

echo microtime(1) - $timestart.': Generating Product History<br />';
		// Product history for this session
		$sql = "INSERT INTO history_prod (pid, q_avg, price_avg, sales_vol, sales_total, history_tick, history_datetime) (
		SELECT a.id, IFNULL(b.q_avg, a.q_avg), IFNULL(b.price_avg, a.value_avg), IFNULL(b.sales_vol, 0), IFNULL(b.sales_total, 0), $timeupdate_tick, '$timeupdate_dt' FROM (SELECT list_prod.id, list_prod.value_avg, list_prod.q_avg FROM list_prod) AS a LEFT JOIN (SELECT list_prod.id AS pid, SUM(log_sales.pidn * log_sales.pidq) / SUM(log_sales.pidn) AS q_avg, SUM(log_sales.value) / SUM(log_sales.pidn) AS price_avg, SUM(log_sales.pidn) AS sales_vol, SUM(log_sales.value) AS sales_total FROM list_prod LEFT JOIN log_sales ON list_prod.id = log_sales.pid WHERE log_sales.tick = $timeupdate_tick GROUP BY list_prod.id) AS b ON a.id = b.pid)";
		$db->query($sql);
echo microtime(1) - $timestart.': Ending Fulfilled Contracts<br />';
		// Kill terminated contracts
		$sql = "SELECT players.player_name, firms.name AS firm_name, firms_positions.pid, firms_positions.fid, firms_positions.title, firms_positions.pay_flat FROM firms_positions LEFT JOIN players ON firms_positions.pid = players.id LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.endtime < '$timeupdate_dt' AND !firms_positions.next_accepted";
		$f_positions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$fp_notify_inv = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, CONCAT('Dear Investor, ', ?, ' is no longer the ', ?, ' of ', ?, '.'), '$timeupdate_dt' FROM player_stock WHERE fid = ?");
		$fp_notify_player = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (?, CONCAT('You are no longer the ', ?, ' of ', ?, '.'), '$timeupdate_dt')");
		foreach($f_positions as $f_position){
			if($f_position['pay_flat'] >= 1000000000){
				$fp_notify_inv->execute(array($f_position['player_name'], $f_position['title'], $f_position['firm_name'], $f_position['fid']));
			}
			$fp_notify_player->execute(array($f_position['pid'], $f_position['title'], $f_position['firm_name']));
		}
		$sql = "UPDATE firms_positions LEFT JOIN log_management ON log_management.id = firms_positions.id AND log_management.pid = firms_positions.pid SET log_management.endtime = DATE_ADD('$timeupdate_dt', INTERVAL firms_positions.duration DAY) WHERE firms_positions.endtime < '$timeupdate_dt' AND firms_positions.next_accepted";
		$db->query($sql);
		$sql = "INSERT INTO player_news (pid, body, date_created) SELECT firms_positions.pid, CONCAT('Dear ',firms_positions.title,', your contract for ', firms.name, ' has been extended for another ', firms_positions.duration, ' server days.'), '$timeupdate_dt' FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.endtime < '$timeupdate_dt' AND firms_positions.next_accepted";
		$db->query($sql);
		$sql = "UPDATE firms_positions SET endtime = DATE_ADD('$timeupdate_dt', INTERVAL duration DAY) WHERE endtime < '$timeupdate_dt' AND next_accepted";
		$db->query($sql);
		$sql = "DELETE FROM firms_positions WHERE endtime < '$timeupdate_dt' AND !next_accepted";
		$db->query($sql);
		
echo microtime(1) - $timestart.': Assigning Chairmen<br />';
		// Assign chairmen for public companies
		$sql = "SELECT xceo.fid, xceo.new_ceo, xceo.new_ceo_shares, xceo.old_ceo, pa.player_name AS new_ceo_name, pb.player_name AS old_ceo_name, firms.name, firm_stock.shares_os 
		FROM 
			(SELECT ps.fid, ps.pid AS new_ceo, x.new_ceo_shares, firms_extended.ceo AS old_ceo 
			FROM 
				(SELECT fid, max(shares) AS new_ceo_shares 
				FROM player_stock GROUP BY fid) AS x 
			INNER JOIN player_stock AS ps ON ps.fid = x.fid AND ps.shares = x.new_ceo_shares 
			LEFT JOIN firms_extended ON ps.fid = firms_extended.id 
			WHERE ps.pid != firms_extended.ceo GROUP BY ps.fid) AS xceo 
		LEFT JOIN players AS pa ON pa.id = xceo.new_ceo 
		LEFT JOIN players AS pb ON pb.id = xceo.old_ceo 
		LEFT JOIN firms ON xceo.fid = firms.id 
		LEFT JOIN firm_stock ON xceo.fid = firm_stock.fid";
		$player_stocks = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($player_stocks as $player_stock){
			$old_ceo = $player_stock['old_ceo'];
			$new_ceo = $player_stock['new_ceo'];
			$old_ceo_name = $player_stock['old_ceo_name'];
			$new_ceo_name = $player_stock['new_ceo_name'];
			if($new_ceo != $old_ceo && $player_stock['new_ceo_shares'] > 0.0999 * $player_stock['shares_os']){
				$fid = $player_stock['fid'];
				$fname = $player_stock['name'];
				$sql = "UPDATE firms_extended SET ceo = $new_ceo WHERE id = $fid";
				$db->query($sql);
				// Delete old position just in case
				$sql = "SELECT id FROM firms_positions WHERE fid = $fid AND pid = $new_ceo ORDER BY id DESC";
				$fp_id = $db->query($sql)->fetchColumn();
				if($fp_id){
					$sql = "UPDATE log_management SET endtime = NOW() WHERE id = $fp_id";
					$db->query($sql);
					$sql = "DELETE FROM firms_positions WHERE id = $fp_id";
					$db->query($sql);
				}
				$sql = "INSERT INTO firms_positions (fid, pid, title, pay_flat, bonus_percent, next_pay_flat, next_bonus_percent, next_accepted, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) VALUES ($fid, $new_ceo, 'Chairman', 0, 0, 0, 0, 1, NOW(), '2222-01-01', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";
				$db->query($sql);
				$sql = "SELECT id FROM firms_positions WHERE fid = $fid AND pid = $new_ceo ORDER BY id DESC";
				$fp_id = $db->query($sql)->fetchColumn();
				// Insert into logs
				$sql = "INSERT INTO log_management (id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) 
				SELECT id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM firms_positions WHERE firms_positions.id = $fp_id";
				$db->query($sql);
				
				$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($new_ceo, CONCAT('You were elected as the chairman of the board for <a href=\"/eos/firm/$fid\">', ?, '</a>.'), NOW())";
				$query = $db->prepare($sql);
				$query->execute(array($fname));
				if($old_ceo){
					$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($old_ceo, CONCAT('You are no longer the chairman of <a href=\"/eos/firm/$fid\">', ?, '</a>.'), NOW())";
					$query = $db->prepare($sql);
					$query->execute(array($fname));
					$sql = "UPDATE players SET fid = 0 WHERE id = $old_ceo AND fid = $fid";
					$db->query($sql);
					$sql = "SELECT id FROM firms_positions WHERE fid = $fid AND pid = $old_ceo ORDER BY id DESC";
					$old_fp_id = $db->query($sql)->fetchColumn();
					if($old_fp_id){
						$sql = "UPDATE log_management SET endtime = NOW() WHERE id = $old_fp_id";
						$db->query($sql);
						$sql = "DELETE FROM firms_positions WHERE id = $old_fp_id";
						$db->query($sql);
					}
					$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, CONCAT('Dear Investor, ', ?, ' was succeeded by ', ?, ' as Chairman of ', ?, '.'), NOW() FROM player_stock WHERE fid = $fid");
					$query->execute(array($old_ceo_name, $new_ceo_name, $fname));
				}
			}
		}
echo microtime(1) - $timestart.': Initializing Product List and Demands<br />';
		// Move price multiplier closer to targets
		$sql = "UPDATE list_cat SET price_multiplier = GREATEST(price_multiplier_target, price_multiplier - 0.01) WHERE price_multiplier_target < price_multiplier";
		$db->query($sql);
		
		$sql = "UPDATE list_cat SET price_multiplier = LEAST(price_multiplier_target, price_multiplier + 0.01) WHERE price_multiplier_target > price_multiplier";
		$db->query($sql);

		// Initialize product price multiplier
		$sql = "SELECT list_prod.id, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.demand_met, list_prod.selltime, list_cat.price_multiplier FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id";
		$list_prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($list_prods as $list_prod){
			$pid = $list_prod["id"];
			$pid_value_base[$pid] = $list_prod["value"];
			$pid_value_avg[$pid] = $list_prod["value_avg"];
			$pid_q_avg[$pid] = $list_prod["q_avg"];
			$pid_demand_met[$pid] = $list_prod["demand_met"];
			$pid_price_multiplier[$pid] = $list_prod["price_multiplier"];
			$pid_selltime[$pid] = $list_prod["selltime"];
		}
		// Total Demand
		$sql = "SELECT SUM(size) FROM firm_fact";
		$total_npc_bldg_size = $db->query($sql)->fetchColumn();
		$sql = "SELECT SUM(size) FROM firm_store";
		$total_npc_bldg_size += $db->query($sql)->fetchColumn();
		$sql = "SELECT SUM(size) FROM firm_rnd";
		$total_npc_bldg_size += $db->query($sql)->fetchColumn();
		$total_demand = $total_npc_bldg_size*150;

echo microtime(1) - $timestart.': Waiting for People to Forget Old Advertisements<br />';
		// Decay those marketing efforts
		$sql = "UPDATE firm_store SET marketing = 0.997 * marketing + POW(size, 1.6)/10";
		$db->query($sql);
echo microtime(1) - $timestart.': Calculating Total Building Size<br />';
		$sql = "SELECT SUM(size) FROM firm_fact";
		$total_npc_bldg_size = $db->query($sql)->fetchColumn();
		$sql = "SELECT SUM(size) FROM firm_store";
		$total_npc_bldg_size += $db->query($sql)->fetchColumn();
		$sql = "SELECT SUM(size) FROM firm_rnd";
		$total_npc_bldg_size += $db->query($sql)->fetchColumn();
echo microtime(1) - $timestart.': Calculating New Supply and Demand<br />';
		$total_demand = $total_npc_bldg_size*150;
		$sql = "SELECT pid, SUM(sales_vol) AS n_total, SUM(sales_vol * q_avg) AS q_total, SUM(sales_total) AS value_total FROM history_prod WHERE history_datetime > DATE_ADD(NOW(), INTERVAL -12 HOUR) GROUP BY pid";
		$history_prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($history_prods as $history_prod){
			$list_prod_id = $history_prod["pid"];
			$list_prod_n_total = $history_prod["n_total"];
			if($list_prod_n_total){
				$list_prod_q_total = $history_prod["q_total"];
				$list_prod_value_total = $history_prod["value_total"];
				$list_prod_value_avg = max(1, floor($list_prod_value_total/$list_prod_n_total));
				$list_prod_q_avg = number_format($list_prod_q_total/$list_prod_n_total,2,'.','');
				$list_prod_demand = $total_demand * $pid_price_multiplier[$list_prod_id] * (1 + 0.02 * $list_prod_q_avg) * pow(max(1,3*$pid_value_base[$list_prod_id] - $list_prod_value_avg/10),0.2);
				$list_prod_demand_met = $list_prod_value_total/$list_prod_demand;
				$sql = "UPDATE list_prod SET value_avg = $list_prod_value_avg, q_avg = $list_prod_q_avg, demand = $list_prod_demand, demand_met = $list_prod_demand_met WHERE id = $list_prod_id";
				$db->query($sql);
			}else{
				$list_prod_demand = $total_demand * $pid_price_multiplier[$list_prod_id] * pow(max(1,2.9*$pid_value_base[$list_prod_id]),0.2);
				$sql = "UPDATE list_prod SET value_avg = value, q_avg = 0, demand = $list_prod_demand, demand_met = 0 WHERE id = $list_prod_id";
				$db->query($sql);
			}
		}

		$sql = "UPDATE (SELECT pid, SUM(quality)/COUNT(*) AS q_max_avg FROM (SELECT id, quality, pid, @rn := CASE WHEN @pid=pid THEN @rn + 1 ELSE 1 END AS rn, @pid := pid FROM firm_tech, (SELECT @rn := 0, @pid := NULL) AS vars ORDER BY pid ASC, quality DESC) AS t1 WHERE rn <= 5 GROUP BY pid) AS a LEFT JOIN list_prod ON a.pid = list_prod.id SET list_prod.tech_avg = a.q_max_avg";
		$db->query($sql);

echo microtime(1) - $timestart.': Re-listing Products from Foreign Companies<br />';
		// Update price multiplier
		$sql = "UPDATE foreign_list_goods SET price_multiplier = GREATEST(1, price_multiplier * (1 + 0.5 * POW(LEAST(0.5, value_sold / value_to_sell - 0.5), 3)))";
		$db->query($sql);
		
		// Update value to sell
		$sql = "UPDATE foreign_list_goods LEFT JOIN (
			SELECT cat_id, SUM(demand) / COUNT(id) AS dp FROM `list_prod` GROUP BY cat_id
		) AS p ON foreign_list_goods.cat_id = p.cat_id 
		SET foreign_list_goods.value_sold = value_sold / 2, foreign_list_goods.value_to_sell = p.dp / 50000 * POW(foreign_list_goods.price_multiplier, 3)";
		$db->query($sql);
		
		// Remove old products from market
		$sql = "DELETE market_prod.* FROM foreign_companies LEFT JOIN market_prod ON foreign_companies.id = market_prod.fid";
		$db->query($sql);
		
		// Insert new products into market
		$sql = "INSERT INTO market_prod (fid, pid, pidq, pidn, pidcost, price, listed) 
		SELECT foreign_list_goods.fcid, list_prod.id, foreign_list_goods.quality, GREATEST(10, foreign_list_goods.value_to_sell / (foreign_list_goods.price_multiplier * list_prod.value * (1 + 0.02 * foreign_list_goods.quality))), 0, foreign_list_goods.price_multiplier * list_prod.value * (1 + 0.02 * foreign_list_goods.quality), CURDATE() FROM foreign_list_goods LEFT JOIN list_prod ON foreign_list_goods.cat_id = list_prod.cat_id";
		$db->query($sql);

echo microtime(1) - $timestart.': Re-listing Purchase Requests from Foreign Companies<br />';
		// Update price multiplier
		$sql = "UPDATE foreign_list_purcs SET price_multiplier = LEAST(20, GREATEST(0.5, price_multiplier * (1 - 0.5 * POW(LEAST(0.5, value_bought / value_to_buy - 0.5), 3))))";
		$db->query($sql);
		
		// Update value to buy
		$sql = "UPDATE foreign_list_purcs LEFT JOIN (
			SELECT cat_id, SUM(demand) / COUNT(id) AS dp FROM `list_prod` GROUP BY cat_id
		) AS p ON foreign_list_purcs.cat_id = p.cat_id 
		SET foreign_list_purcs.value_bought = value_bought / 2, foreign_list_purcs.value_to_buy = p.dp / 1000 / POW(foreign_list_purcs.price_multiplier, 2)";
		$db->query($sql);
		
		// Remove old products from market
		$sql = "DELETE market_requests.* FROM foreign_companies LEFT JOIN market_requests ON foreign_companies.id = market_requests.fid";
		$db->query($sql);
		
		// Insert new products into market
		$sql = "INSERT INTO market_requests (fid, pid, pidq, pidn, price, requested) 
		SELECT foreign_list_purcs.fcid, list_prod.id, 0, GREATEST(1, foreign_list_purcs.value_to_buy / (foreign_list_purcs.price_multiplier * list_prod.value)), foreign_list_purcs.price_multiplier * list_prod.value, CURDATE() FROM foreign_list_purcs LEFT JOIN list_prod ON foreign_list_purcs.cat_id = list_prod.cat_id";
		$db->query($sql);

echo microtime(1) - $timestart.': Matching B2B Sales with Requests<br />';
		$sql = "SELECT list_prod.id, list_prod.cat_id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_cat.price_multiplier FROM (SELECT pid, price FROM (SELECT market_prod.pid, market_prod.price FROM market_prod ORDER BY pid ASC, price ASC) AS a GROUP BY pid) AS sales LEFT JOIN market_requests AS purcs ON sales.pid = purcs.pid LEFT JOIN list_prod ON sales.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE sales.price <= purcs.price AND purcs.id IS NOT NULL AND list_prod.id IS NOT NULL GROUP BY list_prod.id";
		$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		foreach($prods AS $prod){
			// Find everything for sale (id, n, price) sorted by price ASC, id ASC
			$sql = "SELECT market_prod.id, market_prod.fid, firms.name AS firm_name, firms.networth, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.pidcost FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id WHERE market_prod.pid = ".$prod['id']." ORDER BY market_prod.price ASC, market_prod.id ASC";
			$sales = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			// Find all purchase requests (id, n, price, aon) sorted by price DESC
			$sql = "SELECT market_requests.id, market_requests.fid, firms.name AS firm_name, firms.networth, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id WHERE market_requests.pid = ".$prod['id']." ORDER BY market_requests.price DESC, market_requests.id ASC";
			$purcs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			// Loop through each for sale, match against each purc request
			foreach($sales as $sale){
				$break_all = 1;
				foreach($purcs as $key => $purc){
					if($sale['price'] > $purc['price']){
						if($break_all) break 2;
						break;
					}
					if($purc['pidq'] > $sale['pidq']){
						continue;
					}
					if($purc['aon'] && $purc['pidn'] > $sale['pidn']){
						continue;
					}
					$break_all = 0;
					
					// Do sale with $sale and $purc, update $sale['pidn'] and/or slice away $purc from $purcs
					list($sale, $purc) = do_sale($sale, $purc, $prod);
					if($purc['pidn'] == 0){
						unset($purcs[$key]);
					}else{
						$purcs[$key]['pidn'] = $purc['pidn'];
					}
					if($sale['pidn'] == 0){
						break;
					}
				}
			}
		}
		
		
echo microtime(1) - $timestart.': Adding Stock History<br />';
		$sql = "INSERT INTO history_stock_fine (fid, share_price, history_datetime) SELECT fid, share_price, NOW() FROM firm_stock";
		$db->query($sql);
echo microtime(1) - $timestart.': Cleaning old Stock Orders<br />';
		$sql = "DELETE FROM stock_bid WHERE expiration < CURDATE()";
		$db->query($sql);
		$sql = "DELETE FROM stock_ask WHERE expiration < CURDATE()";
		$db->query($sql);
echo microtime(1) - $timestart.': Clearing Non-Suspicious Transactions.<br />';
		$sql = "UPDATE log_market_prod LEFT JOIN firms AS sf ON log_market_prod.sfid = sf.id LEFT JOIN firms_extended AS sfe ON sf.id = sfe.id LEFT JOIN firms AS bf ON log_market_prod.bfid = bf.id LEFT JOIN firms_extended AS bfe ON bf.id = bfe.id LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id SET log_market_prod.hide = 1 WHERE !log_market_prod.hide AND sfe.ceo = bfe.ceo AND !sfe.is_public AND !bfe.is_public";
		$db->query($sql);

		// Summarize event
echo microtime(1) - $timestart.': Done.<br />';
		$sql = "UPDATE world_var SET value = '$timenow' WHERE name = 'su_last_ran_fifteen'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$timetaken = microtime(1) - $timestart;
		$sql = "UPDATE world_var SET value = '$timetaken' WHERE name = 'su_last_ran_fifteen_dur'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$cron_report_msg = "OK";
		return true;
	}
	
	// Prepare firm_pay_query to deduct cash, and log_revenue_query to log expense
	$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES (:firm_id, :is_debit, :pid, :pidn, :pidq, :cost, :source, NOW())");
	$add_news_query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:firm_id, :news, NOW())");
	$sql_wh_pid = $db->prepare("SELECT COUNT(*) AS wh_count, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = :pid AND fid = :fid");
	$sql_wh_insert = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (:fid, :pid, :pidq, :pidn, :pidcost)");
	$sql_wh_update = $db->prepare("UPDATE firm_wh SET pidcost = :pidcost, pidn = :pidn, pidq = :pidq WHERE id = :id");

	function do_sale($seller_sale, $buyer_purc, $prod){
		global $db, $firm_pay_query, $log_revenue_query, $add_news_query, $sql_wh_pid, $sql_wh_insert, $sql_wh_update;

		// Populate vars
		$listing_id = $seller_sale['id'];
		$listing_fid = $seller_sale['fid'];
		$listing_firm_name = $seller_sale['firm_name'];
		$listing_pid = $seller_sale['pid'];
		$listing_pidq = $seller_sale['pidq'];
		$listing_pidn = $seller_sale['pidn'];
		$listing_pidcost = $seller_sale['pidcost'];
		$listing_price = $seller_sale['price'];
		$prod_name = $prod['name'];
		$prod_value = $prod['value'];
		$prod_cat_id = $prod['cat_id'];
		$prod_price_multiplier = $prod['price_multiplier'];
		$request_id = $buyer_purc['id'];
		$request_fid = $buyer_purc['fid'];
		$request_firm_name = $buyer_purc['firm_name'];
		$request_pidn = $buyer_purc['pidn'];
		$request_price = $buyer_purc['price'];

		// Calculate quantity to sell
		if($request_pidn != -1){
			$sell_num = min($listing_pidn, $request_pidn);
		}else{
			$sell_num = $listing_pidn;
		}
		$purchase_cost = $sell_num * $request_price;
		$total_receipt = round(0.95 * $sell_num * $listing_price);
		
		// Special hack - if buyer is foreign company
		if($request_fid > 50 && $request_fid < 70){
			$sql = "UPDATE foreign_list_purcs SET value_bought = value_bought + $purchase_cost WHERE fcid = $request_fid AND cat_id = $prod_cat_id";
			$db->query($sql);
			$affected = 1;
		}else{
			// Execute firm_pay_query and check success
			$result = $firm_pay_query->execute(array(':cost' => $purchase_cost, ':firm_id' => $request_fid));
			$affected = $firm_pay_query->rowCount();
			
			if($affected){
				// Log purchase
				$log_revenue_query->execute(array(':firm_id' => $request_fid, ':is_debit' => 1, ':pid' => $listing_pid, ':pidn' => $sell_num, ':pidq' => $listing_pidq, ':cost' => $purchase_cost, ':source' => 'B2B Purchase'));

				// Add news
				$unit = ' units';
				if($sell_num == 1) $unit = ' unit';
				$news = '<a href="/eos/firm/'.$listing_fid.'">'.$listing_firm_name.'</a> sold '.$sell_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> to your company for a total of $'.number_format($purchase_cost/100,2,'.',',').'.';
				$add_news_query->execute(array(':firm_id' => $request_fid, ':news' => $news));
				
				// Check if pid with pidq already exists in warehouse, add already finished opid to warehouse
				$sql_wh_pid->execute(array(':pid' => $listing_pid, ':fid' => $request_fid));
				$wh_opid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
				if($wh_opid1['wh_count']){
					$prod_n_new = $wh_opid1["pidn"] + $sell_num;
					$prod_q_new = ($wh_opid1["pidn"] * $wh_opid1["pidq"] + $sell_num * $listing_pidq)/$prod_n_new;
					$prod_cost_new = round(($wh_opid1["pidn"] * $wh_opid1["pidcost"] + $sell_num * $listing_price)/$prod_n_new);
					
					$sql_wh_update->execute(array(':id' => $wh_opid1["id"], ':pidq' => $prod_q_new, ':pidn' => $prod_n_new, ':pidcost' => $prod_cost_new));
				}else{
					$sql_wh_insert->execute(array(':fid' => $request_fid, ':pid' => $listing_pid, ':pidq' => $listing_pidq, ':pidn' => $sell_num, ':pidcost' => $listing_price));
				}
			}
		}

		if($affected){
			// Immediately update market
			if($listing_pidn == $sell_num){
				$seller_sale['pidn'] = 0;
				$sql = "DELETE FROM market_prod WHERE id = '$listing_id'";
				$db->query($sql);
			}else{
				$listing_pidn_leftover = $listing_pidn - $sell_num;
				$seller_sale['pidn'] = $listing_pidn_leftover;
				$sql = "UPDATE market_prod SET pidn = $listing_pidn_leftover WHERE id = '$listing_id'";
				$db->query($sql);
			}
			if($request_pidn != -1){
				if($request_pidn == $sell_num){
					$buyer_purc['pidn'] = 0;
					$sql = "DELETE FROM market_requests WHERE id = '$request_id'";
					$db->query($sql);
				}else{
					$request_pidn_leftover = $request_pidn - $sell_num;
					$buyer_purc['pidn'] = $request_pidn_leftover;
					$sql = "UPDATE market_requests SET pidn = $request_pidn_leftover WHERE id = '$request_id'";
					$db->query($sql);
				}
			}
			
			// Special hack - if seller is foreign company
			if($listing_fid > 50 && $listing_fid < 70){
				$sql = "UPDATE foreign_list_goods SET value_sold = value_sold + $purchase_cost WHERE fcid = $listing_fid AND cat_id = $prod_cat_id";
				$db->query($sql);
			}else{
				// Pay seller and log receipt
				$sql = "UPDATE firms SET cash = cash + $total_receipt WHERE id = $listing_fid";
				$db->query($sql);
				$log_revenue_query->execute(array(':firm_id' => $listing_fid, ':is_debit' => 0, ':pid' => $listing_pid, ':pidn' => $sell_num, ':pidq' => $listing_pidq, ':cost' => $total_receipt, ':source' => 'B2B Sales'));
				
				// Add news
				$unit = ' units';
				if($sell_num == 1) $unit = ' unit';
				$news = '<a href="/eos/firm/'.$request_fid.'">'.$request_firm_name.'</a> bought '.$sell_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> from your company for a total of $'.number_format($total_receipt/100,2,'.',',').' after commission.';
				$add_news_query->execute(array(':firm_id' => $listing_fid, ':news' => $news));
			}

			// Add to market log
			$price_to_value = $listing_price / $prod_value;
			$total_value = $prod_value * $sell_num;
			if($listing_fid == $request_fid){
				$hide = 1;
			}else{
				$threadhold1 = 0.05 * $seller_sale['networth'];
				$threadhold2 = 0.01 * $seller_sale['networth'];
				if($price_to_value < (10 * $prod_price_multiplier) && $price_to_value > 0.1){
					$hide = 1;
				}else if($price_to_value < 0.0001 || $price_to_value > 10000){
					$hide = 0;
				}else if($purchase_cost < $threadhold1 && $total_value < $threadhold1){
					$hide = 1;
				}else{
					$hide = 0;
				}
				if($purchase_cost < $threadhold2 && $total_value < $threadhold2){
					$hide = 1;
				}
			}
			$sql = "INSERT INTO log_market_prod (sfid, bfid, pid, pidq, pidn, cost, price, pricetovalue, hide, transaction_time) VALUES ('$listing_fid', $request_fid, '$listing_pid', '$listing_pidq', '$sell_num', '$listing_pidcost', '$listing_price', '$price_to_value', '$hide', NOW())";
			$db->query($sql);
		}
		
		return array($seller_sale, $buyer_purc);
	}

	
	if(update_fifteen_min()){
		$subject = "Cron Job - EoS - Success - 15 min Regular";
		echo "<br /><br />Everything ran ok.";
		exit();
	}else{
		if($cron_report_msg != "OK" || $cron_report_msg_level_2){
			$subject = "Cron Job - EoS - FAILED - 15 min Regular";

			$timetaken = microtime(1) - $timestart;
			$cron_report_msg .= $cron_report_msg_level_2.'<br />Time taken (s): '.$timetaken;
			// $cron_report_msg .= '<br />'.serialize($_SERVER);

			$sender = "admin@example.com";
			$sender_name = "Example Mailer";
			$recipient = "someguy@example.com";
			$add_reply = "someguy@example.com";

			$headers = "MIME-Version: 1.0\n";
			$headers .= "From: $sender_name <$sender>\n";
			$headers .= "X-Sender: <$sender>\n";
			$headers .= "X-Mailer: Example Mailer 1.0\n"; //mailer
			$headers .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
			$headers .= "Content-Type: text/html\n";
			$headers .= "Return-Path: <$add_reply>\n";

			mail($recipient, $subject, $cron_report_msg, $headers);
		}
		echo $cron_report_msg;
	}
?>