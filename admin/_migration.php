<?php
	error_reporting(E_ALL);
	require '../scripts/db/dbconnrjeos.php';

function number_format_readable ($num, $digits = 3, $dec_sep = '.', $k_sep = ','){
	$num_abs = abs($num);
	if($num_abs < 10){
		if((int)$num_abs != $num_abs){
			if(strlen($num_abs) < 6 && $num_abs < 0.01){
				return $num;
			}else{
				return number_format($num, max(0, $digits-1), $dec_sep, $k_sep);	
			}
		}else{
			return number_format($num, max(0, $digits-1), $dec_sep, $k_sep);	
		}
	}
	if($num_abs < 1000){
		if((int)$num_abs == $num_abs){
			return (int)$num;
		}
		if($num_abs < 100){
			return number_format($num, max(0, $digits-2), $dec_sep, $k_sep);
		}
		return number_format($num, max(0, $digits-3), $dec_sep, $k_sep);
	}
	if($num_abs < 1000000){
		if($num_abs < 10000){
			return number_format(floor($num/10)/100, max(0, $digits-1), $dec_sep, $k_sep).' k';
		}
		if($num_abs < 100000){
			return number_format(floor($num/100)/10, max(0, $digits-2), $dec_sep, $k_sep).' k';
		}
		return number_format(floor($num/1000), max(0, $digits-3), $dec_sep, $k_sep).' k';
	}
	if($num_abs < 1000000000){
		if($num_abs < 10000000){
			return number_format(floor($num/10000)/100, max(0, $digits-1), $dec_sep, $k_sep).' M';
		}
		if($num_abs < 100000000){
			return number_format(floor($num/100000)/10, max(0, $digits-2), $dec_sep, $k_sep).' M';
		}
		return number_format(floor($num/1000000), max(0, $digits-3), $dec_sep, $k_sep).' M';
	}
	if($num_abs < 1000000000000){
		if($num_abs < 10000000000){
			return number_format(floor($num/10000000)/100, max(0, $digits-1), $dec_sep, $k_sep).' B';
		}
		if($num_abs < 100000000000){
			return number_format(floor($num/100000000)/10, max(0, $digits-2), $dec_sep, $k_sep).' B';
		}
		return number_format(floor($num/1000000000), max(0, $digits-3), $dec_sep, $k_sep).' B';
	}
	if($num_abs < 1000000000000000){
		if($num_abs < 10000000000000){
			return number_format(floor($num/10000000000)/100, max(0, $digits-1), $dec_sep, $k_sep).' T';
		}
		if($num_abs < 100000000000000){
			return number_format(floor($num/100000000000)/10, max(0, $digits-2), $dec_sep, $k_sep).' T';
		}
		return number_format(floor($num/1000000000000), max(0, $digits-3), $dec_sep, $k_sep).' T';
	}else{
		if($num_abs < 10000000000000000){
			return number_format(floor($num/10000000000000)/100, max(0, $digits-1), $dec_sep, $k_sep).' Q';
		}
		if($num_abs < 100000000000000000){
			return number_format(floor($num/100000000000000)/10, max(0, $digits-2), $dec_sep, $k_sep).' Q';
		}
		return number_format(floor($num/1000000000000000), max(0, $digits-3), $dec_sep, $k_sep).' Q';
	}
}

	
// Migration Script
echo '000. Pre-mig stuff<br />';
	$sql = "ALTER TABLE `players` ADD `b2b_rows_per_page` TINYINT UNSIGNED NOT NULL DEFAULT '20' AFTER `queue_countdown`";
	$db->query($sql);
	
	$sql = "UPDATE players SET new_user = 2 WHERE new_user > 2";
	$db->query($sql);

	$sql = "ALTER TABLE `list_cat` ADD `price_multiplier_target` DECIMAL( 8, 2 ) NOT NULL DEFAULT '1' AFTER `price_multiplier`";
	$db->query($sql);
	
	$sql = "ALTER TABLE `firms_positions` 
	ADD `ctrl_bldg_hurry` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_admin` ,
	ADD `ctrl_bldg_land` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_bldg_hurry` ,
	ADD `ctrl_bldg_view` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_bldg_land` ,
	ADD `ctrl_rnd_cancel` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_rnd_res` ,
	ADD `ctrl_hr_fire` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_hr_hire`";
	$db->query($sql);

	$sql = "UPDATE firms_positions SET 
	ctrl_bldg_hurry = GREATEST(ctrl_admin, ctrl_fact_sell, ctrl_store_sell, ctrl_rnd_sell),
	ctrl_bldg_land = GREATEST(ctrl_admin, ctrl_fact_sell, ctrl_store_sell, ctrl_rnd_sell),
	ctrl_bldg_view = GREATEST(ctrl_admin, ctrl_fact_view, ctrl_store_view, ctrl_rnd_view),
	ctrl_rnd_cancel = GREATEST(ctrl_admin, ctrl_rnd_hurry),
	ctrl_hr_fire = GREATEST(ctrl_admin, ctrl_hr_hire)";
	$db->query($sql);

	$sql = "ALTER TABLE `firms_positions` 
	DROP `ctrl_fact_land` ,
	DROP `ctrl_store_land` ,
	DROP `ctrl_rnd_land` ,
	DROP `ctrl_fact_view` ,
	DROP `ctrl_store_view` ,
	DROP `ctrl_rnd_view` ,
	DROP `ctrl_wh_mix`,
	DROP `ctrl_b2b_import`,
	DROP `ctrl_b2b_export`";
	$db->query($sql);

	$sql = "ALTER TABLE `es_positions` 
	ADD `ctrl_bldg_hurry` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_admin` ,
	ADD `ctrl_bldg_land` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_bldg_hurry` ,
	ADD `ctrl_bldg_view` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_bldg_land` ,
	ADD `ctrl_rnd_cancel` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_rnd_res` ,
	ADD `ctrl_hr_fire` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_hr_hire`";
	$db->query($sql);

	$sql = "UPDATE es_positions SET 
	ctrl_bldg_hurry = GREATEST(ctrl_admin, ctrl_fact_sell, ctrl_store_sell, ctrl_rnd_sell),
	ctrl_bldg_land = GREATEST(ctrl_admin, ctrl_fact_sell, ctrl_store_sell, ctrl_rnd_sell),
	ctrl_bldg_view = GREATEST(ctrl_admin, ctrl_fact_view, ctrl_store_view, ctrl_rnd_view),
	ctrl_rnd_cancel = GREATEST(ctrl_admin, ctrl_rnd_hurry),
	ctrl_hr_fire = GREATEST(ctrl_admin, ctrl_hr_hire)";
	$db->query($sql);

	$sql = "ALTER TABLE `es_positions` 
	DROP `ctrl_fact_land` ,
	DROP `ctrl_store_land` ,
	DROP `ctrl_rnd_land` ,
	DROP `ctrl_fact_view` ,
	DROP `ctrl_store_view` ,
	DROP `ctrl_rnd_view` ,
	DROP `ctrl_wh_mix`,
	DROP `ctrl_b2b_import`,
	DROP `ctrl_b2b_export`";
	$db->query($sql);

	$sql = "ALTER TABLE `log_management` 
	ADD `ctrl_bldg_hurry` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_admin` ,
	ADD `ctrl_bldg_land` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_bldg_hurry` ,
	ADD `ctrl_bldg_view` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_bldg_land` ,
	ADD `ctrl_rnd_res` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_store_sell` ,
	ADD `ctrl_rnd_hurry` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_rnd_res` ,
	ADD `ctrl_rnd_cancel` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_rnd_res` ,
	ADD `ctrl_hr_fire` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ctrl_hr_hire` ,
	DROP `ctrl_wh_mix`,
	DROP `ctrl_b2b_import`,
	DROP `ctrl_b2b_export`";
	$db->query($sql);

	$sql = "UPDATE `log_management` SET 
	ctrl_bldg_hurry = ctrl_admin,
	ctrl_bldg_land = ctrl_admin,
	ctrl_bldg_view = 1,
	ctrl_rnd_res = ctrl_admin,
	ctrl_rnd_hurry = ctrl_admin,
	ctrl_rnd_cancel = ctrl_admin,
	ctrl_hr_fire = ctrl_hr_hire";
	$db->query($sql);

	$sql = "UPDATE firms SET name = CONCAT('[NPC] ', name), networth = 100000000000, cash = 10000000000, level = 10, fame_level = 50 WHERE id > 50 AND id < 60";
	$db->query($sql);
	$sql = "ALTER TABLE `foreign_list_goods` ADD `value_sold` BIGINT UNSIGNED NOT NULL AFTER `value_to_sell`";
	$db->query($sql);
	$sql = "UPDATE `foreign_companies` SET id = id + 50";
	$db->query($sql);
	$sql = "UPDATE `foreign_list_goods` SET fcid = fcid + 50";
	$db->query($sql);
	$sql = "UPDATE `foreign_list_raw_mat` SET fcid = fcid + 50";
	$db->query($sql);
	$sql = "UPDATE `foreign_raw_mat_purc` SET fcid = fcid + 50";
	$db->query($sql);
	$sql = "DROP TABLE `foreign_prod`";
	$db->query($sql);

	$sql = "ALTER TABLE `market_prod` ADD `listed` DATETIME NOT NULL AFTER `price`";
	$db->query($sql);
	$sql = "UPDATE market_prod SET listed = NOW();";
	$db->query($sql);
	$sql = "ALTER TABLE `foreign_list_raw_mat` ADD `value_bought` BIGINT( 20 ) UNSIGNED NOT NULL AFTER `value_to_buy`";
	$db->query($sql);
	$sql = "RENAME TABLE `foreign_list_raw_mat` TO `foreign_list_purcs`";
	$db->query($sql);

	$sql = "CREATE TABLE `market_requests` (
	`id` bigint( 20 ) unsigned NOT NULL AUTO_INCREMENT ,
	`fid` int( 10 ) unsigned NOT NULL ,
	`pid` int( 10 ) unsigned NOT NULL ,
	`pidq` float unsigned NOT NULL ,
	`pidn` bigint( 20 ) NOT NULL ,
	`price` bigint( 20 ) unsigned NOT NULL ,
	`aon` tinyint( 1 ) unsigned NOT NULL DEFAULT '0' ,
	`requested` DATETIME NOT NULL ,
	PRIMARY KEY ( `id` ) ,
	KEY `pid` ( `pid` )
	) ENGINE = InnoDB DEFAULT CHARSET = utf8";
	$db->query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `log_limited_actions` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `action` varchar(24) NOT NULL,
	  `actor_id` int(10) unsigned NOT NULL,
	  `action_time` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `action` (`action`,`actor_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$db->query($sql);

	$sql = 'UPDATE `messages` SET subject = REPLACE(subject, "\\\\", ""), body = REPLACE(body, "\\\\", "")';
	$db->query($sql);
	$sql = 'UPDATE `player_contacts` SET u_notes = REPLACE(u_notes, "\\\\", "")';
	$db->query($sql);
	$sql = 'UPDATE `players_extended` SET player_desc = REPLACE(player_desc, "\\\\", "")';
	$db->query($sql);
	$sql = "UPDATE `players_extended` SET player_desc = REPLACE(player_desc, '<br />', '\r\n')";
	$db->query($sql);

echo 'A. create temp columns<br />';
	$sql = "ALTER TABLE `firm_fact` ADD `assigned` TINYINT NOT NULL DEFAULT '0' AFTER `slot`, ADD `new_slot` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `assigned`";
	$db->query($sql);
	$sql = "ALTER TABLE `firm_store` ADD `assigned` TINYINT NOT NULL DEFAULT '0' AFTER `slot`, ADD `new_slot` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `assigned`";
	$db->query($sql);
	$sql = "ALTER TABLE `firm_rnd` ADD `assigned` TINYINT NOT NULL DEFAULT '0' AFTER `slot`, ADD `new_slot` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `assigned`";
	$db->query($sql);

echo 'B. mass join migration to fact, store, rnd, set assigned = 1<br />';
	$sql = "UPDATE firm_fact LEFT JOIN migration ON firm_fact.fid = migration.fid AND firm_fact.id = migration.building_id AND migration.building_type = 'fact' SET firm_fact.assigned = 1, firm_fact.new_slot = migration.slot WHERE migration.id IS NOT NULL";
	$db->query($sql);
	$sql = "UPDATE firm_store LEFT JOIN migration ON firm_store.fid = migration.fid AND firm_store.id = migration.building_id AND migration.building_type = 'store' SET firm_store.assigned = 1, firm_store.new_slot = migration.slot WHERE migration.id IS NOT NULL";
	$db->query($sql);
	$sql = "UPDATE firm_rnd LEFT JOIN migration ON firm_rnd.fid = migration.fid AND firm_rnd.id = migration.building_id AND migration.building_type = 'rnd' SET firm_rnd.assigned = 1, firm_rnd.new_slot = migration.slot WHERE migration.id IS NOT NULL";
	$db->query($sql);

echo 'C. loop through all firms<br />';
	$converted_slot_num = array(12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29);
	$converted_slot_values = array(0, 4, 36, 144, 400, 900, 1764, 3136, 5184, 8100, 12100, 17424, 24336, 33124, 44100, 57600, 73984, 93636);

	// Prepared queries
	$query_uf = $db->prepare("UPDATE firm_fact SET new_slot = :new_slot, assigned = 1 WHERE id = :id");
	$query_us = $db->prepare("UPDATE firm_store SET new_slot = :new_slot, assigned = 1 WHERE id = :id");
	$query_ur = $db->prepare("UPDATE firm_rnd SET new_slot = :new_slot, assigned = 1 WHERE id = :id");
	$query_unassigned = $db->prepare("SELECT building_id, building_type FROM ((SELECT id AS building_id, 'fact' AS building_type, size FROM firm_fact WHERE fid = :firm_id AND !assigned) UNION (SELECT id AS building_id, 'store' AS building_type, size FROM firm_store WHERE fid = :firm_id AND !assigned) UNION (SELECT id AS building_id, 'rnd' AS building_type, size FROM firm_rnd WHERE fid = :firm_id AND !assigned)) AS a ORDER BY a.size DESC LIMIT 0, :max_slots");
	// SELECT building_id, building_type FROM ((SELECT id AS building_id, 'fact' AS building_type, size FROM firm_fact WHERE fid = 1 AND !assigned) UNION (SELECT id AS building_id, 'store' AS building_type, size FROM firm_store WHERE fid = 1 AND !assigned) UNION (SELECT id AS building_id, 'rnd' AS building_type, size FROM firm_rnd WHERE fid = 1 AND !assigned)) AS a ORDER BY a.size DESC LIMIT 0, 30
	$query_add_cash = $db->prepare("UPDATE firms SET cash = cash + :cash WHERE id = :firm_id");
	$query_add_news = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:firm_id, :news, NOW())");
	$query_add_p_cash = $db->prepare("UPDATE players SET player_cash = player_cash + :cash WHERE id = :player_id");
	$query_add_p_influence = $db->prepare("UPDATE players SET influence = influence + :influence WHERE id = :player_id");
	$query_add_p_news = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (:player_id, :news, NOW())");
	$query_controlling_player = $db->prepare("SELECT pid FROM firms_positions WHERE ctrl_admin AND fid = :firm_id LIMIT 0,1");

	$sql = "SELECT id, max_fact, max_store, max_rnd FROM firms";
	$firms = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($firms as $firm){
		$firm_id = $firm['id'];

		// 1. Calculate max slots
		$max_fact = $firm['max_fact'];
		$max_store = $firm['max_store'];
		$max_rnd = $firm['max_rnd'];
		$init_fact = 3;
		$init_store = 3;
		$init_rnd = 0;
		$appraised_land = ($max_fact - $init_fact) * ($max_fact - $init_fact) * ($max_fact - $init_fact + 1) * ($max_fact - $init_fact + 1);
		$appraised_land += ($max_store - $init_store) * ($max_store - $init_store) * ($max_store - $init_store + 1) * ($max_store - $init_store + 1);
		$appraised_land += ($max_rnd - $init_rnd) * ($max_rnd - $init_rnd) * ($max_rnd - $init_rnd + 1) * ($max_rnd - $init_rnd + 1);

		$max_slots = 12;
		for($i = 0; $i < count($converted_slot_num); $i++){
			if($appraised_land > $converted_slot_values[$i]){
				$max_slots = $converted_slot_num[$i];
			}
		}
		$max_slots += 3;
		$sql = "UPDATE firms SET max_bldg = $max_slots WHERE id = $firm_id";
		$db->query($sql);
		
		// 2. Initialize new slots
		$slot = array();
		$slot_type = array();
		
		// 3. Get list of assigned from migration
		$sql = "SELECT building_id, building_type, slot FROM migration WHERE fid = $firm_id";
		$migs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($migs as $mig){
			$slot[$mig['slot']] = $mig['building_id'];
			$slot_type[$mig['slot']] = $mig['building_type'];
		}
		
		// 4. SELECT all facts, stores, rnds WHERE !assigned ORDER BY size DESC for firm LIMIT 0, max_slots
		$query_unassigned->bindValue(':firm_id', $firm_id);
		$query_unassigned->bindValue(':max_slots', (int) $max_slots, PDO::PARAM_INT);
		$query_unassigned->execute();
		
		// 5. for i = 1; i <= max slots, if $slot[$i] is empty and $results->fetch still has data, assign $result['id'] to $slot, $result['bldg_type'] to $slot_type
		for($i = 1; $i <= $max_slots; $i++){
			if(empty($slot[$i])){
				$bldg = $query_unassigned->fetch(PDO::FETCH_ASSOC);
				if(empty($bldg)){
					break;
				}
				$slot[$i] = $bldg['building_id'];
				$slot_type[$i] = $bldg['building_type'];
			}
		}
		
		// 6. Foreach $slot, update assigned = 1, new_slot = $i for fact, store, rnd
		for($i = 1; $i <= $max_slots; $i++){
			if(empty($slot[$i])){
				continue;
			}
			if($slot_type[$i] == 'fact'){
				$query_uf->execute(array(':id' => $slot[$i], ':new_slot' => $i));
			}else if($slot_type[$i] == 'store'){
				$query_us->execute(array(':id' => $slot[$i], ':new_slot' => $i));
			}else if($slot_type[$i] == 'rnd'){
				$query_ur->execute(array(':id' => $slot[$i], ':new_slot' => $i));
			}
		}
	}
	
	// echo 'sofarsogood';
	// exit();

echo 'D. calculate refund for fact/store/rnd<br />';
	$sql = "SELECT fid, SUM(firm_fact.size * list_fact.cost) AS cash_refund, SUM(firm_fact.size * list_fact.timecost / 1285) AS influence_refund FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE !firm_fact.assigned GROUP BY firm_fact.fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received $'.number_format_readable($cash_refund/100).' for selling one or more of its factories to the government.'));
		}
		$influence_refund = $refund_item['influence_refund'];
		if($influence_refund > 0){
			$query_controlling_player->execute(array(':firm_id' => $firm_id));
			$player_id = $query_controlling_player->fetchColumn();
			if($player_id){
				$query_add_p_influence->execute(array(':player_id' => $player_id, ':influence' => $influence_refund));
				$query_add_p_news->execute(array(':player_id' => $player_id, ':news' => 'You have been awarded '.$influence_refund.' influence for selling one or more factories to the government.'));
			}
		}
	}
	$sql = "SELECT fid, SUM(firm_store.size * list_store.cost) AS cash_refund, SUM(firm_store.size * list_store.timecost / 1285) AS influence_refund FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE !firm_store.assigned GROUP BY firm_store.fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received $'.number_format_readable($cash_refund/100).' for selling one or more of its stores to the government.'));
		}
		$influence_refund = $refund_item['influence_refund'];
		if($influence_refund > 0){
			$query_controlling_player->execute(array(':firm_id' => $firm_id));
			$player_id = $query_controlling_player->fetchColumn();
			if($player_id){
				$query_add_p_influence->execute(array(':player_id' => $player_id, ':influence' => $influence_refund));
				$query_add_p_news->execute(array(':player_id' => $player_id, ':news' => 'You have been awarded '.$influence_refund.' influence for selling one or more stores to the government.'));
			}
		}
	}
	$sql = "SELECT fid, SUM(firm_rnd.size * list_rnd.cost) AS cash_refund, SUM(firm_rnd.size * list_rnd.timecost / 1285) AS influence_refund FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE !firm_rnd.assigned GROUP BY firm_rnd.fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received $'.number_format_readable($cash_refund/100).' for selling one or more of its R&Ds to the government.'));
		}
		$influence_refund = $refund_item['influence_refund'];
		if($influence_refund > 0){
			$query_controlling_player->execute(array(':firm_id' => $firm_id));
			$player_id = $query_controlling_player->fetchColumn();
			if($player_id){
				$query_add_p_influence->execute(array(':player_id' => $player_id, ':influence' => $influence_refund));
				$query_add_p_news->execute(array(':player_id' => $player_id, ':news' => 'You have been awarded '.$influence_refund.' influence for selling one or more R&Ds to the government.'));
			}
		}
	}

echo 'E. delete all unassigned buildings<br />';
	$sql = "DELETE FROM firm_fact WHERE !assigned";
	$db->query($sql);
	$sql = "DELETE FROM firm_store WHERE !assigned";
	$db->query($sql);
	$sql = "DELETE FROM firm_rnd WHERE !assigned";
	$db->query($sql);

echo 'F. select queue build join fact, store, rnd with NULL id (gone)<br />';
	$sql = "SELECT fid, SUM(expand_cost) AS cash_refund FROM (SELECT queue_build.fid, ((queue_build.endtime - queue_build.starttime) / list_fact.timecost * list_fact.cost) AS expand_cost FROM queue_build LEFT JOIN firm_fact ON queue_build.building_id = firm_fact.id LEFT JOIN list_fact ON queue_build.building_type_id = list_fact.id WHERE queue_build.building_type = 'fact' AND queue_build.building_id IS NOT NULL AND firm_fact.id IS NULL ORDER BY queue_build.fid ASC) AS a GROUP BY fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($cash_refund/100).' for the canceled expansion on its factory/factories.'));
		}
	}
	
	$sql = "SELECT fid, SUM(expand_cost) AS cash_refund FROM (SELECT queue_build.fid, ((queue_build.endtime - queue_build.starttime) / list_store.timecost * list_store.cost) AS expand_cost FROM queue_build LEFT JOIN firm_store ON queue_build.building_id = firm_store.id LEFT JOIN list_store ON queue_build.building_type_id = list_store.id WHERE queue_build.building_type = 'store' AND queue_build.building_id IS NOT NULL AND firm_store.id IS NULL ORDER BY queue_build.fid ASC) AS a GROUP BY fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($cash_refund/100).' for the canceled expansion on its store(s).'));
		}
	}
	
	$sql = "SELECT fid, SUM(expand_cost) AS cash_refund FROM (SELECT queue_build.fid, ((queue_build.endtime - queue_build.starttime) / list_rnd.timecost * list_rnd.cost) AS expand_cost FROM queue_build LEFT JOIN firm_rnd ON queue_build.building_id = firm_rnd.id LEFT JOIN list_rnd ON queue_build.building_type_id = list_rnd.id WHERE queue_build.building_type = 'rnd' AND queue_build.building_id IS NOT NULL AND firm_rnd.id IS NULL ORDER BY queue_build.fid ASC) AS a GROUP BY fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($cash_refund/100).' for the canceled expansion on its R&D(s).'));
		}
	}
	
	$sql = "SELECT queue_build.fid, SUM(list_fact.firstcost) AS cash_refund FROM queue_build LEFT JOIN list_fact ON queue_build.building_type_id = list_fact.id WHERE queue_build.building_type = 'fact' AND queue_build.building_slot IS NOT NULL GROUP BY queue_build.fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($cash_refund/100).' for the canceled construction on its factory/factories.'));
		}
	}
	
	$sql = "SELECT queue_build.fid, SUM(list_store.firstcost) AS cash_refund FROM queue_build LEFT JOIN list_store ON queue_build.building_type_id = list_store.id WHERE queue_build.building_type = 'store' AND queue_build.building_slot IS NOT NULL GROUP BY queue_build.fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($cash_refund/100).' for the canceled construction on its store(s).'));
		}
	}
	
	$sql = "SELECT queue_build.fid, SUM(list_rnd.firstcost) AS cash_refund FROM queue_build LEFT JOIN list_rnd ON queue_build.building_type_id = list_rnd.id WHERE queue_build.building_type = 'rnd' AND queue_build.building_slot IS NOT NULL GROUP BY queue_build.fid";
	$refund_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($refund_list as $refund_item){
		$firm_id = $refund_item['fid'];
		$cash_refund = $refund_item['cash_refund'];
		if($cash_refund > 0){
			$query_add_cash->execute(array(':firm_id' => $firm_id, ':cash' => $cash_refund));
			$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($cash_refund/100).' for the canceled construction on its R&D(s).'));
		}
	}

echo 'G. delete queue_build<br />';
	$sql = "DELETE queue_build.* FROM queue_build LEFT JOIN firm_fact ON queue_build.building_id = firm_fact.id WHERE queue_build.building_type = 'fact' AND queue_build.building_id IS NOT NULL AND firm_fact.id IS NULL";
	$db->query($sql);
	$sql = "DELETE queue_build.* FROM queue_build LEFT JOIN firm_store ON queue_build.building_id = firm_store.id WHERE queue_build.building_type = 'store' AND queue_build.building_id IS NOT NULL AND firm_store.id IS NULL";
	$db->query($sql);
	$sql = "DELETE queue_build.* FROM queue_build LEFT JOIN firm_rnd ON queue_build.building_id = firm_rnd.id WHERE queue_build.building_type = 'rnd' AND queue_build.building_id IS NOT NULL AND firm_rnd.id IS NULL";
	$db->query($sql);
	$sql = "DELETE queue_build.* FROM queue_build WHERE queue_build.building_slot IS NOT NULL";
	$db->query($sql);

echo 'H. for each queue_prod without bldg, just complete it and hand it over<br />';
	$sql = "SELECT queue_prod.* FROM queue_prod LEFT JOIN firm_fact ON queue_prod.ffid = firm_fact.id WHERE firm_fact.id IS NULL";
	$queue_pcs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($queue_pcs as $queue_pc){
		$firm_id = $queue_pc['fid'];
		$list_fact_pc_id = $queue_pc["id"];
		$list_fact_pc_opid1 = $queue_pc["opid1"];
		$list_fact_pc_opid1_q = $queue_pc["opid1q"];
		$list_fact_pc_opid1_n = $queue_pc["opid1n"];
		$list_fact_pc_opid1_cost = $queue_pc["opid1cost"];
		$query = $db->prepare("DELETE FROM queue_prod WHERE id = ?");
		$query->execute(array($list_fact_pc_id));
		// Check if pid with pidq already exists in warehouse
		$query = $db->prepare("SELECT COUNT(*) AS cnt, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = ? AND fid = ?");
		$query->execute(array($list_fact_pc_opid1, $firm_id));
		$wh_prod = $query->fetch(PDO::FETCH_ASSOC);
		if($wh_prod["cnt"]){
			// Update warehouse
			$list_fact_pc_opid1_wh_id = $wh_prod["id"];
			$list_fact_pc_opid1_wh_n = $wh_prod["pidn"];
			$list_fact_pc_opid1_wh_q = $wh_prod["pidq"];
			$list_fact_pc_opid1_wh_cost = $wh_prod["pidcost"];
			$list_fact_pc_opid1_n_new = $list_fact_pc_opid1_wh_n + $list_fact_pc_opid1_n;
			$list_fact_pc_opid1_q_new = ($list_fact_pc_opid1_wh_n * $list_fact_pc_opid1_wh_q + $list_fact_pc_opid1_n * $list_fact_pc_opid1_q)/$list_fact_pc_opid1_n_new;
			$list_fact_pc_opid1_cost_new = round(($list_fact_pc_opid1_wh_n * $list_fact_pc_opid1_wh_cost + $list_fact_pc_opid1_n * $list_fact_pc_opid1_cost)/$list_fact_pc_opid1_n_new);
			$query = $db->prepare("UPDATE firm_wh SET pidcost = ?, pidn = ?, pidq = ? WHERE id = ?");
			$query->execute(array($list_fact_pc_opid1_cost_new, $list_fact_pc_opid1_n_new, $list_fact_pc_opid1_q_new, $list_fact_pc_opid1_wh_id));
		}else{
			// Insert into warehouse
			$query = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (?, ?, ?, ?, ?)");
			$query->execute(array($firm_id, $list_fact_pc_opid1, $list_fact_pc_opid1_q, $list_fact_pc_opid1_n, $list_fact_pc_opid1_cost));
		}
	}

echo 'I. for each queue_res without bldg... fully refund them<br />';
	$sql = "SELECT queue_res.*, list_prod.res_cost, list_prod.tech_avg FROM queue_res LEFT JOIN list_prod ON queue_res.pid = list_prod.id LEFT JOIN firm_rnd ON queue_res.frid = firm_rnd.id WHERE firm_rnd.id IS NULL";
	$queue_reses = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($queue_reses as $queue_item_res){
		// Find out what's researching, research price, and if it's actually finished
		$timenow = time();
		$firm_id = $queue_item_res["fid"];
		$frid = $queue_item_res["frid"];
		$qr_pid = $queue_item_res["pid"];
		$qr_newlevel = $queue_item_res["newlevel"];
		$qr_pid_res_basecost = $queue_item_res["res_cost"];
		$qr_pid_tech_avg = $queue_item_res["tech_avg"];
		$qr_pid_res_cost = max(10000, $qr_pid_res_basecost * pow(1.2, $qr_newlevel - 0.25 * $qr_pid_tech_avg));
		$qr_research_refund = $qr_pid_res_cost;

		// Delete from researching queue
		// $sql = "DELETE FROM queue_res WHERE id = '$queue_id'";
		// $result = $db->query($sql);

		// Also cancel anything that depends on this research
		$sql = "SELECT id, newlevel, frid, starttime, endtime FROM queue_res WHERE fid = $firm_id AND pid = $qr_pid AND newlevel > $qr_newlevel ORDER BY starttime DESC";
		$dep_queues = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$dep_queues_count = count($dep_queues);
		if($dep_queues_count){
			// Delete them first before the variables get changed
			$sql = "DELETE FROM queue_res WHERE fid = $firm_id AND pid = $qr_pid AND newlevel > $qr_newlevel";
			$db->query($sql);
			foreach($dep_queues as $dep_res){
				$qr_frid = $dep_res["frid"];
				$qr_newlevel = $dep_res["newlevel"];
				$qr_starttime = $dep_res["starttime"];
				$qr_endtime = $dep_res["endtime"];
				$qr_pid_res_cost = max(10000, $qr_pid_res_basecost * pow(1.2, $qr_newlevel - 0.25 * $qr_pid_tech_avg));
				$qr_research_refund += $qr_pid_res_cost;
				$sql = "UPDATE queue_res SET endtime = endtime + $qr_starttime - $qr_endtime, starttime = starttime + $qr_starttime - $qr_endtime WHERE fid = $firm_id AND frid = $qr_frid AND starttime >= $qr_starttime";
				$db->query($sql);
			}
		}

		// Give research refund to firm
		$sql = "INSERT INTO log_revenue (fid, is_debit, pid, pidq, value, source, transaction_time) VALUES ($firm_id, 0, $qr_pid, $qr_newlevel, $qr_research_refund, 'Research', NOW())";
		$db->query($sql);	
		$sql = "UPDATE firms SET cash = cash + $qr_research_refund WHERE id='$firm_id'";
		$db->query($sql);
		$query_add_news->execute(array(':firm_id' => $firm_id, ':news' => 'Your company received a refund of $'.number_format_readable($qr_research_refund/100).' for canceled research(es).'));
	}
	
echo 'J. Done<br />';
	$sql = "UPDATE firm_fact SET slot = new_slot";
	$db->query($sql);
	$sql = "UPDATE firm_store SET slot = new_slot";
	$db->query($sql);
	$sql = "UPDATE firm_rnd SET slot = new_slot";
	$db->query($sql);
	$sql = "ALTER TABLE `firm_fact` DROP `assigned`, DROP `new_slot`";
	$db->query($sql);
	$sql = "ALTER TABLE `firm_store` DROP `assigned`, DROP `new_slot`";
	$db->query($sql);
	$sql = "ALTER TABLE `firm_rnd` DROP `assigned`, DROP `new_slot`";
	$db->query($sql);
?>