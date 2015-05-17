<?php
/*
QUEST TYPES

Production:
1. Turn in # of completely random prod of unit value < x and total value <= y
2. Turn in # of specific prod of min. Q # (can be done in batches) (also useful for 1 prod of high Q)
3. Turn in # of random prod from already researched prods (Q > 9), with total value < y
Cash:
10. Reach x cash
11. Reach next level
Buildings:
20. Have building type of min. size
21. Have building_id AND type of min. size
Research:
30. Have pid research of min. quality
31. Have cat_id research of min. quality
32. Have any research of min. quality
33. Improve any existing research by 1 quality
Sales:
40. $$$ from sales (day) 
41. $$$ from sales (week) 
	Log_revenue (C: Sales, B2B Sales, Research (refund), Production (refund), System; D: Research, Production, B2B Purchase, Construction, Expansion, Land Purchase, System)
42. Reach x% market share (day) for pid
43. Reach x% market share (week) for pid (already researched)
B2B:
50. $$$ from B2B sales
51. $$$ from B2B purchase

Not yet implemented
B2B:
52. Reach x% market share (day) for pid from B2B sales
53. Reach x% market share (week) for pid from B2B sales (already researched)
Stock:
60. Stock price N
61. Hold x% own stock
62. Control another company
Personal:
70. Personal cash reach x
71. Personal networth (incl. stock value) reach x
72. Improve living quality (furniture, assets, food, beverage, ...)
*/

function add_quest($fid = NULL, $q_id = NULL, $q_level = NULL){
	global $db;

	if(!$fid){
		return false;
	}
	if(!$q_level){
		$query = $db->prepare("SELECT level FROM firms WHERE id = ?");
		$query->execute(array($fid));
		$q_level = $query->fetchColumn();
	}
	// If completely random quest based on level
	if(!$q_id){
		$query = $db->prepare("SELECT id FROM list_quest WHERE level_min <= :q_level AND level_max >= :q_level");
		$query->execute(array(':q_level' =>  $q_level));
		$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
		$q_count = count($q_results);
		if(!$q_count){
			return false;
		}
		$q_index = mt_rand(0, $q_count-1);
		$q_id = $q_results[$q_index]["id"];
	}

	// Initialize quest type
	$query = $db->prepare("SELECT * FROM list_quest WHERE id = ?");
	$query->execute(array($q_id));
	$q_result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($q_result)){
		return false;
	}
	$q_type = $q_result["type"];
	$value_unit_max = $q_result["value_unit_max"];
	$value_total = $q_result["value_total"];
	$target_id = $q_result["target_id"]; 
	$target_type = $q_result["target_type"];
	$n = $q_result["n"];
	$q = $q_result["q"];
	$cash = $q_result["cash"];
	$duration = $q_result["duration"];
	$starttime = time();
	if(!$duration){
		$duration = 8*24*3600;
	}
	$endtime = $starttime + $duration;

	switch($q_type){
		case 1:
			if(!$value_unit_max){
				$value_unit_max = max(100,5*pow(4,$q_level));
			}
			$query = $db->prepare("SELECT id, value FROM list_prod WHERE value > 10 AND value <= ?");
			$query->execute(array($value_unit_max));
			$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
			$q_count = count($q_results);
			$q_index = mt_rand(0, $q_count-1);
			$value_unit = $q_results[$q_index]["value"];
			$gen_target_id = $q_results[$q_index]["id"];
			if(!$value_total){
				$value_total = 500*pow(4,$q_level);
			}
			$gen_target_n = floor($value_total / $value_unit);
			$reward_cash = 2 * $value_total;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 2:
			$query = $db->prepare("SELECT value FROM list_prod WHERE id = ?");
			$query->execute(array($target_id));
			$value_unit = $query->fetchColumn();
			$gen_target_id = $target_id;
			$gen_target_n = $n;
			$value_total = $gen_target_n * $value_unit;
			$reward_cash = 2 * $value_total;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 3:
			$query = $db->prepare("SELECT pid, quality FROM firm_tech WHERE fid = ? AND quality > 9 ORDER BY quality DESC LIMIT 0,10");
			$query->execute(array($fid));
			$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
			$q_count = count($q_results);
			if($q_count){
				$q_index = mt_rand(0, $q_count-1);
				$gen_target_id = $q_results[$q_index]["pid"];
				$query = $db->prepare("SELECT value FROM list_prod WHERE id = ?");
				$query->execute(array($gen_target_id));
				$value_unit = $query->fetchColumn();
			}else{
				if(!$value_unit_max){
					$value_unit_max = max(100,5*pow(4,$q_level));
				}
				$query = $db->prepare("SELECT id, value FROM list_prod WHERE value > 10 AND value <= ?");
				$query->execute(array($value_unit_max));
				$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
				$q_count = count($q_results);
				$q_index = mt_rand(0, $q_count-1);
				$gen_target_id = $q_results[$q_index]["id"];
				$value_unit = $q_results[$q_index]["value"];
			}
			if(!$value_total){
				$value_total = 500*pow(4,$q_level);
			}
			$gen_target_n = max(1,floor($value_total / $value_unit));
			$reward_cash = 2 * $value_total;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 10:
			$gen_target_n = 10000*pow(4,$q_level);
			$reward_cash = 0;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 11:
			$gen_target_n = $q_level + 1;
			$reward_cash = 0;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 20:
			$gen_target_n = 10+floor(0.05*pow($q_level+3,4));
			$reward_cash = $gen_target_n * 10000;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 21:
			if(!$n){
				$gen_target_n = $n;
			}else{
				$gen_target_n = 10+floor(0.05*pow($q_level+2,4));
			}
			$reward_cash = $gen_target_n*12500;
			$reward_fame = 100 * pow(4, 0.6 * $q_level);
			break;
		case 30:
			$reward_cash = 100000 * $q;
			$reward_fame = min(1000000, 1000 * $q);
			break;
		case 31:
			$reward_cash = 100000 * $q;
			$reward_fame = min(1000000, 1000 * $q);
			break;
		case 32:
			$reward_cash = 100000 * $q;
			$reward_fame = min(1000000, 1000 * $q);
			break;
		case 33:
			if($target_id){
				$gen_target_id = $target_id;
				$query = $db->prepare("SELECT quality FROM firm_tech WHERE fid = ? AND pid = ?");
				$query->execute(array($fid, $gen_target_id));
				$q_result = $query->fetch(PDO::FETCH_ASSOC);
				$q_count = empty($q_result) ? 0 : 1;
				if($q_count){
					$gen_target_n = $q_result["quality"] + 1;
				}else{
					$gen_target_n = 1;
				}
			}else{
				$query = $db->prepare("SELECT pid, quality FROM firm_tech WHERE fid = ?");
				$query->execute(array($fid));
				$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
				$q_count = count($q_results);
				if($q_count){
					$q_index = mt_rand(0, $q_count-1);
					$gen_target_id = $q_results[$q_index]["pid"];
					$gen_target_n = $q_results[$q_index]["quality"] + 1;
				}else{
					$query = $db->prepare("SELECT id FROM list_prod WHERE value > 10");
					$query->execute(array());
					$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
					$q_count = count($q_results);
					$q_index = mt_rand(0, $q_count-1);
					$gen_target_id = $q_results[$q_index]["id"];
					$gen_target_n = 1;
				}
			}
			// Scale reward with avg tech
			$sql = "SELECT res_cost, tech_avg FROM list_prod WHERE id = $gen_target_id";
			$prod_res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			$reward_cash = max(1000, $prod_res['res_cost'] * pow(1.2, $gen_target_n - 0.25 * $prod_res['tech_avg']));
			$reward_fame = 1000 * pow(max(1, $gen_target_n - 0.25 * $prod_res['tech_avg']), 2);
			break;
		case 40:
			$gen_target_n = 5000*pow(4,$q_level);
			$reward_cash = $gen_target_n/10;
			$reward_fame = 300 * pow(4, 0.6 * $q_level);
			break;
		case 41:
			$gen_target_n = 30000*pow(4,$q_level);
			$reward_cash = $gen_target_n/10;
			$reward_fame = 500 * pow(4, 0.6 * $q_level);
			break;
		case 42:
			if($target_id){
				$gen_target_id = $target_id;
			}else{
				$query = $db->prepare("SELECT firm_tech.pid, firm_tech.quality FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE firm_tech.fid = ? AND list_cat.sellable = 1 ORDER BY firm_tech.quality DESC LIMIT 0,8");
				$query->execute(array($fid));
				$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
				$q_count = count($q_results);
				if($q_count){
					$q_index = mt_rand(0, $q_count-1);
					$gen_target_id = $q_results[$q_index]["pid"];
				}else{
					$value_unit_max = max(100,5*pow(4,$q_level));
					$query = $db->prepare("SELECT list_prod.id, list_prod.value FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_prod.value <= ? AND list_cat.sellable = 1");
					$query->execute(array($value_unit_max));
					$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
					$q_count = count($q_results);
					$q_index = mt_rand(0, $q_count-1);
					$gen_target_id = $q_results[$q_index]["id"];
				}
			}
			$gen_target_n = ceil($q_level/2);
			$reward_cash = 0;
			$reward_fame = 200 * pow(4, 0.6 * $q_level);
			break;
		case 43:
			if($target_id){
				$gen_target_id = $target_id;
			}else{
				$query = $db->prepare("SELECT firm_tech.pid, firm_tech.quality FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE firm_tech.fid = ? AND list_cat.sellable = 1 ORDER BY firm_tech.quality DESC LIMIT 0,8");
				$query->execute(array($fid));
				$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
				$q_count = $q_count = count($q_results);
				if($q_count){
					$q_index = mt_rand(0, $q_count-1);
					$gen_target_id = $q_results[$q_index]["pid"];
				}else{
					$value_unit_max = max(100,5*pow(4,$q_level));
					$query = $db->prepare("SELECT list_prod.id, list_prod.value FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_prod.value <= ? AND list_cat.sellable = 1");
					$query->execute(array($value_unit_max));
					$q_results = $query->fetchAll(PDO::FETCH_ASSOC);
					$q_count = count($q_results);
					$q_index = mt_rand(0, $q_count-1);
					$gen_target_id = $q_results[$q_index]["id"];
				}
			}
			$gen_target_n = ceil($q_level/2);
			$reward_cash = 0;
			$reward_fame = 500 * pow(4, 0.6 * $q_level);
			break;
		case 50:
			$gen_target_n = 5000*pow(4,$q_level);
			$reward_cash = $gen_target_n/10;
			$reward_fame = floor(14*24*3600/$duration * sqrt($gen_target_n/50));
			break;
		case 51:
			$gen_target_n = 5000*pow(4,$q_level);
			$reward_cash = $gen_target_n/10;
			$reward_fame = floor(14*24*3600/$duration * sqrt($gen_target_n/50));
			break;
		default:
			return false;
			break;
	}
	$sql = "INSERT INTO firm_quest SET fid = ?, quest_id = ?, starttime = ?, endtime = ?, reward_cash = ?, reward_fame = ?";
	$params = array($fid, $q_id, $starttime, $endtime, $reward_cash, $reward_fame);
	if(isset($gen_target_id) && $gen_target_id){
		$sql .= ", gen_target_id = ?";
		$params[] = $gen_target_id;
	}
	if(isset($gen_target_n) && $gen_target_n){
		$sql .= ", gen_target_n = ?";
		$params[] = $gen_target_n;
	}
	$query = $db->prepare($sql);
	$result = $query->execute($params);
	if($result){
		return true;
	}else{
		return false;
	}
}
function validate_quest($fqid = NULL){
	global $db, $quest_pid, $quest_type_image, $quest_objective_message, $quest_progress_message, $quest_endtime, $reward_cash, $reward_fame, $quest_allow_supply, $quest_validated, $quest_completed;
	$quest_validated = 0;
	$quest_completed = 0;
	$quest_pid = 0;
	if(!$fqid){
		return false;
	}
	//Initialize quest type
	$query = $db->prepare("SELECT * FROM firm_quest LEFT JOIN list_quest ON firm_quest.quest_id = list_quest.id WHERE firm_quest.id = ? AND !firm_quest.completed AND !firm_quest.failed");
	$query->execute(array($fqid));
	$q_result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($q_result)){
		return false;
	}
	$fid = $q_result["fid"];
	$quest_id = $q_result["quest_id"];
	$gen_target_id = $q_result["gen_target_id"];
	$gen_target_n = $q_result["gen_target_n"];
	$starttime = $q_result["starttime"];
	$quest_endtime = $q_result["endtime"];
	$timenow = time();
	$timenow_tick = floor(($timenow - 1327104000)/900);
	$reward_cash = $q_result["reward_cash"];
	$reward_fame = $q_result["reward_fame"];
	$target_id = $q_result["target_id"];
	$target_type = $q_result["target_type"];
	$q_type = $q_result["type"];
	$q = $q_result["q"];
	
	$quest_validated = 1;
	$quest_allow_supply = 0;
	switch($q_type) {
		case 1:
			$quest_type_image = "menu-factories";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_pid = $gen_target_id;
			$quest_objective_message = 'Supply a batch of <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a>.';
			if($gen_target_n > 0){
				$quest_progress_message = number_format($gen_target_n,0,'.',',')." more needed.";
				$quest_allow_supply = 1;
			}else{
				$quest_completed = 1;
			}
			break;
		case 2:
			$quest_type_image = "menu-factories";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_pid = $gen_target_id;
			$quest_objective_message = 'Supply a batch of <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> of quality '.$q.' or above.';
			if($gen_target_n > 0){
				$quest_progress_message = number_format($gen_target_n,0,'.',',')." more needed.";
				$quest_allow_supply = 1;
			}else{
				$quest_completed = 1;
			}
			break;
		case 3:
			$quest_type_image = "menu-factories";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_pid = $gen_target_id;
			$quest_objective_message = 'Supply a batch of <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> of quality '.$q.' or above.';
			if($gen_target_n > 0){
				$quest_progress_message = number_format($gen_target_n,0,'.',',')." more needed.";
				$quest_allow_supply = 1;
			}else{
				$quest_completed = 1;
			}
			break;
		case 10:
			$quest_type_image = "menu-rankings";
			$quest_objective_message = 'Have at least the following amount of cash: $'.number_format($gen_target_n/100,2,'.',',');
			$query = $db->prepare("SELECT cash FROM firms WHERE id = ?");
			$query->execute(array($fid));
			$q_firm_cash = $query->fetchColumn();
			if($gen_target_n > $q_firm_cash){
				$q_percent = round(100*$q_firm_cash/$gen_target_n,2);
				$quest_progress_message = $q_percent."% Completed.";
			}else{
				$quest_completed = 1;
			}
			break;
		case 11:
			$quest_type_image = "menu-rankings";
			$firm_level_desc = array("Garage Shop", "Fledgling Start-Up", "Start-Up", "Small Enterprise", "Medium Enterprise", "Large Enterprise", "Nano Cap", "Micro Cap", "Small Cap", "Mid Cap", "Large Cap", "Conglomerate", "Large Conglomerate", "MNC", "Corporate Empire");
			$quest_objective_message = 'Advance to the next level: '.$firm_level_desc[$gen_target_n];
			$query = $db->prepare("SELECT COUNT(*) AS cnt FROM firms WHERE id = ? AND level >= ?");
			$query->execute(array($fid, $gen_target_n));
			$q_count = $query->fetchColumn();
			if(!$q_count){
				$quest_progress_message = "Requirement not met.";
			}else{
				$quest_completed = 1;
			}
			break;
		case 20:
			$quest_type_image = "menu-construction";
			if($target_type == "fact"){
				$quest_objective_message = 'Expand any factory to '.$gen_target_n.' m&#178; or above.';
				$sql = "SELECT size FROM firm_fact WHERE fid = $fid ORDER BY size DESC LIMIT 0, 1";
			}
			if($target_type == "store"){
				$quest_objective_message = 'Expand any store to '.$gen_target_n.' m&#178; or above.';
				$sql = "SELECT size FROM firm_store WHERE fid = $fid ORDER BY size DESC LIMIT 0, 1";
			}
			if($target_type == "rnd"){
				$quest_objective_message = 'Expand any R&amp;D to '.$gen_target_n.' m&#178; or above.';
				$sql = "SELECT size FROM firm_rnd WHERE fid = $fid ORDER BY size DESC LIMIT 0, 1";
			}
			$q_result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			if(!empty($q_result)){
				$q_size = $q_result['size'];
			}else{
				$q_size = 0;
			}
			if($q_size < $gen_target_n){
				$quest_progress_message = $q_size."/".$gen_target_n;
			}else{
				$quest_completed = 1;
			}
			break;
		case 21:
			$quest_type_image = "menu-construction";
			if($target_type == "fact"){
				$query = $db->prepare("SELECT name FROM list_fact WHERE id = ?");
				$query->execute(array($gen_target_id));
				$q_building_name = $query->fetchColumn();
				$quest_objective_message = 'Build and/or expand a '.$q_building_name.' (factory) to '.$gen_target_n.' m&#178; or above.';
				$query = $db->prepare("SELECT firm_fact FROM firm_store WHERE fid = ? AND fact_id = ? ORDER BY size DESC LIMIT 0, 1");
				$query->execute(array($fid, $gen_target_id));
			}
			if($target_type == "store"){
				$query = $db->prepare("SELECT name FROM list_store WHERE id = ?");
				$query->execute(array($gen_target_id));
				$q_building_name = $query->fetchColumn();
				$quest_objective_message = 'Build and/or expand a '.$q_building_name.' (store) to '.$gen_target_n.' m&#178; or above.';
				$query = $db->prepare("SELECT size FROM firm_store WHERE fid = ? AND store_id = ? ORDER BY size DESC LIMIT 0, 1");
				$query->execute(array($fid, $gen_target_id));
			}
			if($target_type == "rnd"){
				$query = $db->prepare("SELECT name FROM list_rnd WHERE id = ?");
				$query->execute(array($gen_target_id));
				$q_building_name = $query->fetchColumn();
				$quest_objective_message = 'Build and/or expand a '.$q_building_name.' (R&amp;D) to '.$gen_target_n.' m&#178; or above.';
				$query = $db->prepare("SELECT size FROM firm_rnd WHERE fid = ? AND rnd_id = ? ORDER BY size DESC LIMIT 0, 1");
				$query->execute(array($fid, $gen_target_id));
			}
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			if(!empty($q_result)){
				$q_size = $q_result['size'];
			}else{
				$q_size = 0;
			}
			if($q_size < $gen_target_n){
				$quest_progress_message = $q_size."/".$gen_target_n;
			}else{
				$quest_completed = 1;
			}
			break;
		case 30:
			$quest_type_image = "menu-rnd";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Research <a class="jqDialog" href="pedia-product-view.php?pid='.$target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> to improve its quality to '.$q.' or above.';
			$query = $db->prepare("SELECT quality FROM firm_tech WHERE fid = ? AND pid = ?");
			$query->execute(array($fid, $target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			if(!empty($q_result)){
				$q_quality = $q_result['quality'];
			}else{
				$q_quality = 0;
			}
			if($q_quality < $q){
				$quest_progress_message = $q_quality."/".$q;
			}else{
				$quest_completed = 1;
			}
			break;
		case 31:
			$quest_type_image = "menu-rnd";
			$query = $db->prepare("SELECT name FROM list_cat WHERE id = ?");
			$query->execute(array($target_id));
			$cat_name = $query->fetchColumn();
			$quest_objective_message = 'Research any product in the <a class="jqDialog" href="pedia-product-list-cat.php?cat_id='.$target_id.'"><b>'.$cat_name.'</b></a> category, and improve its quality to '.$q.' or above.';
			$query = $db->prepare("SELECT firm_tech.quality FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $fid AND list_prod.cat_id = ? ORDER BY firm_tech.quality DESC");
			$query->execute(array($target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			if(!empty($q_result)){
				$q_quality = $q_result['quality'];
			}else{
				$q_quality = 0;
			}
			if($q_quality < $q){
				$quest_progress_message = $q_quality."/".$q;
			}else{
				$quest_completed = 1;
			}
			break;
		case 32:
			$quest_type_image = "menu-rnd";
			$quest_objective_message = 'Research any product to improve its quality to '.$q.' or above.';
			$query = $db->prepare("SELECT quality FROM firm_tech WHERE fid = ? ORDER BY quality DESC");
			$query->execute(array($fid));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			if(!empty($q_result)){
				$q_quality = $q_result['quality'];
			}else{
				$q_quality = 0;
			}
			if($q_quality < $q){
				$quest_progress_message = $q_quality."/".$q;
			}else{
				$quest_completed = 1;
			}
			break;
		case 33:
			$quest_type_image = "menu-rnd";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Research <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> to improve its quality to '.$gen_target_n.' or above.';
			$query = $db->prepare("SELECT quality FROM firm_tech WHERE fid = ? AND pid = ?");
			$query->execute(array($fid, $gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			if(!empty($q_result)){
				$q_quality = $q_result['quality'];
			}else{
				$q_quality = 0;
			}
			if($q_quality < $gen_target_n){
				$quest_progress_message = $q_quality."/".$gen_target_n;
			}else{
				$quest_completed = 1;
			}
			break;
		case 40:
			$quest_type_image = "menu-stores";
			$quest_objective_message = 'Obtain $'.number_format($gen_target_n/100,2,'.',',').' in store sales revenue from the previous 24 hours period (between now and the same time yesterday).';
			$query = $db->prepare("SELECT SUM(value) FROM log_sales WHERE fid = ? AND tick > ?");
			$query->execute(array($fid, $timenow_tick - 96));
			$q_revenue = $query->fetchColumn();
			if($q_revenue < $gen_target_n){
				$q_percent = round(100*$q_revenue/$gen_target_n,2);
				$quest_progress_message = $q_percent."% Completed.";
			}else{
				$quest_completed = 1;
			}
			break;
		case 41:
			$quest_type_image = "menu-stores";
			$quest_objective_message = 'Obtain $'.number_format($gen_target_n/100,2,'.',',').' in store sales revenue from the previous 7 days.';
			$query = $db->prepare("SELECT SUM(store_sales) FROM history_firms WHERE fid = ? AND history_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY)");
			$query->execute(array($fid));
			$q_revenue = $query->fetchColumn();
			if($q_revenue < $gen_target_n){
				$q_percent = round(100*$q_revenue/$gen_target_n,2);
				$quest_progress_message = $q_percent."% Completed.";
			}else{
				$quest_completed = 1;
			}
			break;
		case 42:
			$quest_type_image = "menu-stores";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Reach at least '.$gen_target_n.'% market share by store sales revenue for <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> from the previous 8 hours period.';
			$query = $db->prepare("SELECT SUM(value) FROM log_sales WHERE fid = ? AND pid = ? AND tick > ?");
			$query->execute(array($fid, $gen_target_id, $timenow_tick - 32));
			$q_revenue = $query->fetchColumn();
			$query = $db->prepare("SELECT SUM(value) FROM log_sales WHERE pid = ? AND tick > ?");
			$query->execute(array($gen_target_id, $timenow_tick - 32));
			$q_revenue_world = $query->fetchColumn();
			$q_market_share = 0;
			if($q_revenue_world > 0){
				$q_market_share = round(($q_revenue/$q_revenue_world)*100,2);
			}
			if($q_market_share < $gen_target_n){
				$quest_progress_message = "Current market share: ".$q_market_share."%";
			}else{
				$quest_completed = 1;
			}
			break;
		case 43:
			$quest_type_image = "menu-stores";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Reach at least '.$gen_target_n.'% market share by store sales revenue for <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> from the previous 24 hours period.';
			$query = $db->prepare("SELECT SUM(value) FROM log_sales WHERE fid = ? AND pid = ? AND tick > ?");
			$query->execute(array($fid, $gen_target_id, $timenow_tick - 96));
			$q_revenue = $query->fetchColumn();
			$query = $db->prepare("SELECT SUM(value) FROM log_sales WHERE pid = ? AND tick > ?");
			$query->execute(array($gen_target_id, $timenow_tick - 96));
			$q_revenue_world = $query->fetchColumn();
			$q_market_share = 0;
			if($q_revenue_world > 0){
				$q_market_share = round(($q_revenue/$q_revenue_world)*100,2);
			}
			if($q_market_share < $gen_target_n){
				$quest_progress_message = "Current market share: ".$q_market_share."%";
			}else{
				$quest_completed = 1;
			}
			break;
		case 50:
			$quest_type_image = "menu-market";
			$quest_objective_message = 'Obtain $'.number_format($gen_target_n/100,2,'.',',').' in B2B sales revenue from the previous 24 hours period (between now and the same time yesterday).';
			$query = $db->prepare("SELECT SUM(value) FROM log_revenue WHERE fid = ? AND source = 'B2B Sales' AND !is_debit AND transaction_time > DATE_ADD(NOW(), INTERVAL -1 DAY)");
			$query->execute(array($fid));
			$q_revenue = $query->fetchColumn();
			if($q_revenue < $gen_target_n){
				$q_percent = round(100*$q_revenue/$gen_target_n,2);
				$quest_progress_message = $q_percent."% Completed.";
			}else{
				$quest_completed = 1;
			}
			break;
		case 51:
			$quest_type_image = "menu-market";
			$quest_objective_message = 'Spend $'.number_format($gen_target_n/100,2,'.',',').' on B2B purchase from the previous 24 hours period (between now and the same time yesterday).';
			$query = $db->prepare("SELECT SUM(value) FROM log_revenue WHERE fid = ? AND source = 'B2B Purchase' AND is_debit AND transaction_time > DATE_ADD(NOW(), INTERVAL -1 DAY)");
			$query->execute(array($fid));
			$q_revenue = $query->fetchColumn();
			if($q_revenue < $gen_target_n){
				$q_percent = round(100*$q_revenue/$gen_target_n,2);
				$quest_progress_message = $q_percent."% Completed.";
			}else{
				$quest_completed = 1;
			}
			break;
		default:
			$quest_validated = 0;
			break;
	}
	if($timenow > $quest_endtime){
		$query = $db->prepare("UPDATE firm_quest SET failed = 1 WHERE id = ?");
		$query->execute(array($fqid));
		$quest_progress_message = '<font color="#ff0000"><b>Quest Expired!</b></font>';
		return false;
	}
	if($quest_completed){
		$query = $db->prepare("UPDATE firm_quest SET completed = 1, endtime = ? WHERE id = ?");
		$query->execute(array($timenow, $fqid));
		if($reward_cash > 0){
			$query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES (?, 0, ?, 'Quest Reward', NOW())");
			$query->execute(array($fid, $reward_cash));
		}
		$query = $db->prepare("UPDATE firms SET cash = cash + ?, fame_exp = fame_exp + ? WHERE id = ?");
		$query->execute(array($reward_cash, $reward_fame, $fid));
		return true;
	}else{
		return false;
	}
}
function validate_completed_quest($fqid = NULL){
	global $db, $quest_type_image, $quest_objective_message, $quest_progress_message, $quest_endtime, $reward_cash, $reward_fame, $quest_validated;
	$quest_validated = 0;
	if(!$fqid){
		return false;
	}
	//Initialize quest type
	$query = $db->prepare("SELECT * FROM firm_quest LEFT JOIN list_quest ON firm_quest.quest_id = list_quest.id WHERE firm_quest.id = ? AND firm_quest.completed");
	$query->execute(array($fqid));
	$q_result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($q_result)){
		return false;
	}
	$fid = $q_result["fid"];
	$quest_id = $q_result["quest_id"];
	$gen_target_id = $q_result["gen_target_id"];
	$gen_target_n = $q_result["gen_target_n"];
	$starttime = $q_result["starttime"];
	$quest_endtime = $q_result["endtime"];
	$timenow = time();
	$reward_cash = $q_result["reward_cash"];
	$reward_fame = $q_result["reward_fame"];
	$target_id = $q_result["target_id"];
	$target_type = $q_result["target_type"];
	$q_type = $q_result["type"];
	$q = $q_result["q"];
	
	$quest_validated = 1;
	switch($q_type) {
		case 1:
			$quest_type_image = "menu-factories";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Supply a batch of <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a>.';
			return true;
			break;
		case 2:
			$quest_type_image = "menu-factories";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Supply a batch of <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> of quality '.$q.' or above.';
			return true;
			break;
		case 3:
			$quest_type_image = "menu-factories";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Supply a batch of <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> of quality '.$q.' or above.';
			return true;
			break;
		case 10:
			$quest_type_image = "menu-rankings";
			$quest_objective_message = 'Have at least the following amount of cash: $'.number_format($gen_target_n/100,2,'.',',');
			return true;
			break;
		case 11:
			$quest_type_image = "menu-rankings";
			$firm_level_desc = array("Garage Shop", "Fledgling Start-Up", "Start-Up", "Small Enterprise", "Medium Enterprise", "Large Enterprise", "Nano Cap", "Micro Cap", "Small Cap", "Mid Cap", "Large Cap", "Conglomerate", "Large Conglomerate", "MNC", "Corporate Empire");
			$quest_objective_message = 'Advance to the next level: '.$firm_level_desc[$gen_target_n];
			return true;
			break;
		case 20:
			$quest_type_image = "menu-construction";
			if($target_type == "fact"){
				$quest_objective_message = 'Expand any factory to '.$gen_target_n.' m&#178; or above.';
			}
			if($target_type == "store"){
				$quest_objective_message = 'Expand any store to '.$gen_target_n.' m&#178; or above.';
			}
			if($target_type == "rnd"){
				$quest_objective_message = 'Expand any R&amp;D to '.$gen_target_n.' m&#178; or above.';
			}
			return true;
			break;
		case 21:
			$quest_type_image = "menu-construction";
			if($target_type == "fact"){
				$query = $db->prepare("SELECT name FROM list_fact WHERE id = ?");
				$query->execute(array($gen_target_id));
				$q_building_name = $query->fetchColumn();
				$quest_objective_message = 'Build and/or expand a '.$q_building_name.' (factory) to '.$gen_target_n.' m&#178; or above.';
			}
			if($target_type == "store"){
				$query = $db->prepare("SELECT name FROM list_store WHERE id = ?");
				$query->execute(array($gen_target_id));
				$q_building_name = $query->fetchColumn();
				$quest_objective_message = 'Build and/or expand a '.$q_building_name.' (store) to '.$gen_target_n.' m&#178; or above.';
			}
			if($target_type == "rnd"){
				$query = $db->prepare("SELECT name FROM list_rnd WHERE id = ?");
				$query->execute(array($gen_target_id));
				$q_building_name = $query->fetchColumn();
				$quest_objective_message = 'Build and/or expand a '.$q_building_name.' (R&amp;D) to '.$gen_target_n.' m&#178; or above.';
			}
			return true;
			break;
		case 30:
			$quest_type_image = "menu-rnd";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Research <a class="jqDialog" href="pedia-product-view.php?pid='.$target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> to improve its quality to '.$q.' or above.';
			return true;
			break;
		case 31:
			$quest_type_image = "menu-rnd";
			$query = $db->prepare("SELECT name FROM list_cat WHERE id = ?");
			$query->execute(array($target_id));
			$cat_name = $query->fetchColumn();
			$quest_objective_message = 'Research any product in the <b>'.$cat_name.'</b> category, and improve its quality to '.$q.' or above.';
			return true;
			break;
		case 32:
			$quest_type_image = "menu-rnd";
			$quest_objective_message = 'Research any product to improve its quality to '.$q.' or above.';
			return true;
			break;
		case 33:
			$quest_type_image = "menu-rnd";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Research <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> to improve its quality to '.$gen_target_n.' or above.';
			return true;
			break;
		case 40:
			$quest_type_image = "menu-stores";
			$quest_objective_message = 'Obtain $'.number_format($gen_target_n/100,2,'.',',').' in store sales revenue from the previous 24 hours period (between now and the same time yesterday).';
			return true;
			break;
		case 41:
			$quest_type_image = "menu-stores";
			$quest_objective_message = 'Obtain $'.number_format($gen_target_n/100,2,'.',',').' in store sales revenue from the previous 7 days.';
			return true;
			break;
		case 42:
			$quest_type_image = "menu-stores";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Reach at least '.$gen_target_n.'% market share by store sales revenue for <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> from the previous 8 hours period.';
			return true;
			break;
		case 43:
			$quest_type_image = "menu-stores";
			$query = $db->prepare("SELECT name, has_icon FROM list_prod WHERE id = ?");
			$query->execute(array($gen_target_id));
			$q_result = $query->fetch(PDO::FETCH_ASSOC);
			$pid_name = $q_result["name"];
			$pid_has_icon = $q_result["has_icon"];
			if($pid_has_icon){
				$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
			}else{
				$pid_filename = 'no-icon';
			}
			$quest_objective_message = 'Reach at least '.$gen_target_n.'% market share by store sales revenue for <a class="jqDialog" href="pedia-product-view.php?pid='.$gen_target_id.'"><img style="vertical-align:middle;" src="/eos/images/prod/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a> from the previous 24 hours period.';
			return true;
			break;
		case 50:
			$quest_type_image = "menu-market";
			$quest_objective_message = 'Obtain $'.number_format($gen_target_n/100,2,'.',',').' in B2B sales revenue from the previous 24 hours period (between now and the same time yesterday).';
			return true;
			break;
		case 51:
			$quest_type_image = "menu-market";
			$quest_objective_message = 'Spend $'.number_format($gen_target_n/100,2,'.',',').' on B2B purchase from the previous 24 hours period (between now and the same time yesterday).';
			return true;
			break;
		default:
			$quest_validated = 0;
			return false;
			break;
	}
}
?>