<?php require 'include/prehtml.php'; ?>
<?php
	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	
	function returnJSON($success = 1, $msg = 'DB failed.', $params = array()){
		$resp = array('success' => $success, 'msg' => $msg);
		if(!empty($params)) $resp = array_merge($resp, $params);
		echo json_encode($resp);
		exit();
	}

	if($action == 'update_all_slots'){
		$max_buildings = filter_var($_POST['max_buildings'], FILTER_SANITIZE_NUMBER_INT);
		if(!$max_buildings || $max_buildings > 200){
			returnJSON(0, "Parameter error.");
		}
		
		// Find out the player's max buildings 
		$query = $db->prepare("SELECT max_bldg FROM firms WHERE id = ?");
		$query->execute(array($eos_firm_id));
		$max_bldg = $query->fetchColumn();

		$timenow = time();

		// Check production queue, move anything completed to firm warehouse
		$query = $db->prepare("SELECT * FROM queue_prod WHERE fid = ? AND endtime < ?");
		$query->execute(array($eos_firm_id, $timenow));
		$queue_pcs = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($queue_pcs as $queue_pc){
			$list_fact_pc_id = $queue_pc["id"];
			$list_fact_pc_opid1 = $queue_pc["opid1"];
			$list_fact_pc_opid1_q = $queue_pc["opid1q"];
			$list_fact_pc_opid1_n = $queue_pc["opid1n"];
			$list_fact_pc_opid1_cost = $queue_pc["opid1cost"];
			$query = $db->prepare("DELETE FROM queue_prod WHERE id = ?");
			$query->execute(array($list_fact_pc_id));
			// Check if pid with pidq already exists in warehouse
			$query = $db->prepare("SELECT COUNT(*) AS cnt, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = ? AND fid = ?");
			$query->execute(array($list_fact_pc_opid1, $eos_firm_id));
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
				$query->execute(array($eos_firm_id, $list_fact_pc_opid1, $list_fact_pc_opid1_q, $list_fact_pc_opid1_n, $list_fact_pc_opid1_cost));
			}
		}

		// Check research queue
		$query = $db->prepare("SELECT * FROM queue_res WHERE fid = ? AND endtime < ?");
		$query->execute(array($eos_firm_id, $timenow));
		$queue_rcs = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($queue_rcs as $queue_rc){
			$list_rnd_rq_id = $queue_rc["id"];
			$list_rnd_rq_pid = $queue_rc["pid"];
			$list_rnd_rq_newlevel = $queue_rc["newlevel"];
			
			// Delete from researching queue
			$query = $db->prepare("DELETE FROM queue_res WHERE id = ?");
			$query->execute(array($list_rnd_rq_id));

			// Give research level to firm, but first check whether or not the firm already has this tech
			$sql = "SELECT quality FROM firm_tech WHERE fid='$eos_firm_id' AND pid='$list_rnd_rq_pid'";
			$list_rnd_rq_oldlevel = $db->query($sql)->fetchColumn();
			if($list_rnd_rq_oldlevel){
				if($list_rnd_rq_newlevel > $list_rnd_rq_oldlevel){
					$query = $db->prepare("UPDATE firm_tech SET quality = ?, update_time = ? WHERE fid = ? AND pid = ?");
					$query->execute(array($list_rnd_rq_newlevel, $timenow, $eos_firm_id, $list_rnd_rq_pid));
				}
			}else{
				$query = $db->prepare("INSERT INTO firm_tech (fid, pid, quality, update_time) VALUES (?, ?, ?, ?)");
				$query->execute(array($eos_firm_id, $list_rnd_rq_pid, $list_rnd_rq_newlevel, $timenow));
			}
		}

		// Check in build queue, update expanded buildings
		$query = $db->prepare("SELECT * FROM queue_build WHERE fid = ? AND endtime < ?");
		$query->execute(array($eos_firm_id, $timenow));
		$queue_bcs = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($queue_bcs as $queue_bc){
			$bc_id = $queue_bc["id"];
			$bc_bldg_id = $queue_bc["building_id"];
			$bc_bldg_type = $queue_bc["building_type"];
			$bc_bldg_type_id = $queue_bc["building_type_id"];
			$bc_slot = $queue_bc["building_slot"];
			$bc_size = $queue_bc["newsize"];
			$query = $db->prepare("DELETE FROM queue_build WHERE id = ?");
			$query->execute(array($bc_id));
			if($bc_bldg_type == 'fact'){
				if($bc_bldg_id){
					$query = $db->prepare("UPDATE firm_fact SET size = ? WHERE id = ? AND fid = ?");
					$query->execute(array($bc_size, $bc_bldg_id, $eos_firm_id));
				}else{
					$query = $db->prepare("INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) SELECT ?, ?, name, ?, ? FROM list_fact WHERE id = ?");
					$query->execute(array($eos_firm_id, $bc_bldg_type_id, $bc_size, $bc_slot, $bc_bldg_type_id));
				}
			}else if($bc_bldg_type == 'store'){
				if($bc_bldg_id){
					$query = $db->prepare("UPDATE firm_store SET size = ?, is_expanding = 0 WHERE id = ? AND fid = ?");
					$query->execute(array($bc_size, $bc_bldg_id, $eos_firm_id));
				}else{
					$query = $db->prepare("INSERT INTO firm_store (fid, store_id, store_name, size, slot) SELECT ?, ?, name, ?, ? FROM list_store WHERE id = ?");
					$query->execute(array($eos_firm_id, $bc_bldg_type_id, $bc_size, $bc_slot, $bc_bldg_type_id));
				}
			}else if($bc_bldg_type == 'rnd'){
				if($bc_bldg_id){
					$query = $db->prepare("UPDATE firm_rnd SET size = ? WHERE id = ? AND fid = ?");
					$query->execute(array($bc_size, $bc_bldg_id, $eos_firm_id));
				}else{
					$query = $db->prepare("INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) SELECT ?, ?, name, ?, ? FROM list_rnd WHERE id = ?");
					$query->execute(array($eos_firm_id, $bc_bldg_type_id, $bc_size, $bc_slot, $bc_bldg_type_id));
				}
			}
		}
		
		for($j=1;$j<=$max_buildings;$j++){
			$cd_on[$j] = 0;
			$cd_total[$j] = 0;
			$cd_remaining[$j] = 0;
			$bldg_type[$j] = '';
			$bldg_type_id[$j] = 0;
			$bldg_id[$j] = 0;
			$bldg_title[$j] = '';
			$bldg_status[$j] = '';
			$cd_icon_back[$j] = 'anim_placeholder';
			$cd_icon[$j] = 'anim_placeholder';
			$bldg_image[$j] = '';
			$bldg_link[$j] = '';
			if($j == 0) continue; // Populate 0th index to keep it defined, but otherwise skip it
			if($j <= $max_bldg){
				$sql = "(SELECT firm_fact.id, 'fact' AS bldg_type, firm_fact.fact_name AS bldg_name, firm_fact.size, list_fact.name AS generic_name, list_fact.has_image FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = :eos_firm_id AND firm_fact.slot = :slot) UNION (SELECT firm_store.id, 'store' AS bldg_type, firm_store.store_name, firm_store.size, list_store.name, list_store.has_image FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = :eos_firm_id AND firm_store.slot = :slot) UNION (SELECT firm_rnd.id, 'rnd' AS bldg_type, firm_rnd.rnd_name, firm_rnd.size, list_rnd.name, list_rnd.has_image FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = :eos_firm_id AND firm_rnd.slot = :slot)";
				$query = $db->prepare($sql);
				$query->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $j));
				$bldg = $query->fetch(PDO::FETCH_ASSOC);
				
				if(!empty($bldg)){
					$bldg_id[$j] = $bldg["id"];
					$bldg_type[$j] = $bldg["bldg_type"];
					$bldg_name[$j] = $bldg["bldg_name"];
					$bldg_size[$j] = $bldg["size"];
					if($bldg["has_image"]){
						$filename[$j] = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg["generic_name"]));
					}else{
						$filename[$j] = "no-image";
					}
					$bldg_image[$j] = '<img class="no_select" src="/eos/images/'.$bldg_type[$j].'/'.$filename[$j].'.gif" width="90" height="40" />';
					$bldg_title[$j] = $bldg_name[$j].' ('.$bldg_size[$j].' m&#178;)';
					
					if($bldg_type[$j] == 'fact'){
						// Check if ffid is producing anything
						$query = $db->prepare("SELECT queue_prod.opid1, queue_prod.opid1q, queue_prod.opid1n, queue_prod.starttime, queue_prod.endtime, list_prod.name, list_prod.has_icon FROM queue_prod LEFT JOIN list_prod ON queue_prod.opid1 = list_prod.id WHERE fid = ? AND ffid = ?");
						$query->execute(array($eos_firm_id, $bldg_id[$j]));
						$result_producing = $query->fetch(PDO::FETCH_ASSOC);
						if(!empty($result_producing)){
							if($result_producing["has_icon"]){
								$bldg_status[$j] = 'Producing '.$result_producing["opid1n"].' <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($result_producing["name"])).'.gif" />'.' (Q'.$result_producing["opid1q"].')';
							}else{
								$bldg_status[$j] = 'Producing '.$result_producing["opid1n"].' '.$result_producing["name"].' (Q'.$result_producing["opid1q"].')';
							}
							$cd_on[$j] = 1;
							$cd_total[$j] = $result_producing["endtime"] - $result_producing["starttime"];
							$cd_remaining[$j] = $result_producing["endtime"] - $timenow;
							$cd_icon_back[$j] = 'anim_gear anim_working';
							$cd_icon[$j] = 'anim_gear';
							$bldg_link[$j] = 'factories-production.php?ffid='.$bldg_id[$j];
						}
					}else if($bldg_type[$j] == 'rnd'){
						// Check if frid is researching anything
						$query = $db->prepare("SELECT * FROM queue_res WHERE fid = ? AND frid = ?");
						$query->execute(array($eos_firm_id, $bldg_id[$j]));
						$result_researching = $query->fetch(PDO::FETCH_ASSOC);
						if(!empty($result_researching)){
							$sql = "SELECT name, has_icon FROM list_prod WHERE id = ".$result_researching["pid"];
							$list_rnd_res_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
							if($list_rnd_res_prod["has_icon"]){
								$bldg_status[$j] = 'Researching <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_rnd_res_prod["name"])).'.gif" /> to quality '.$result_researching["newlevel"];
							}else{
								$bldg_status[$j] = 'Researching '.$list_rnd_res_prod["name"].' to quality '.$result_researching["newlevel"];
							}
							$cd_on[$j] = 1;
							$cd_total[$j] = $result_researching["endtime"] - $result_researching["starttime"];
							$cd_remaining[$j] = $result_researching["endtime"] - $timenow;
							$cd_icon_back[$j] = 'anim_gear anim_working';
							$cd_icon[$j] = 'anim_gear';
							$bldg_link[$j] = 'rnd-res.php?frid='.$bldg_id[$j];
						}
					}
					if(!$cd_on[$j]){
						// Check if building_id is expanding
						$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
						$query->execute(array($bldg_type[$j], $bldg_id[$j]));
						$result_expanding = $query->fetch(PDO::FETCH_ASSOC);
						if(!empty($result_expanding)){
							$bldg_status[$j] = 'Expanding to '.$result_expanding["newsize"].' m&#178;';
							$cd_on[$j] = 1;
							$cd_total[$j] = $result_expanding["endtime"] - $result_expanding["starttime"];
							$cd_remaining[$j] = $result_expanding["endtime"] - $timenow;
							$cd_icon_back[$j] = 'anim_hammer anim_working';
							$cd_icon[$j] = 'anim_hammer';
							$bldg_link[$j] = 'bldg-expand-status.php?type='.$bldg_type[$j].'&id='.$bldg_id[$j];
						}else{
							$bldg_status[$j] = 'Ready';
							if($bldg_type[$j] == 'fact'){
								$bldg_link[$j] = 'factories-production.php?ffid='.$bldg_id[$j];
							}else if($bldg_type[$j] == 'store'){
								$bldg_link[$j] = 'stores-sell.php?fsid='.$bldg_id[$j];
							}else if($bldg_type[$j] == 'rnd'){
								$bldg_link[$j] = 'rnd-res.php?frid='.$bldg_id[$j];
							}
						}
					}
				}else{
					//check new building
					$query = $db->prepare("SELECT queue_build.building_id, queue_build.building_type, queue_build.building_type_id, queue_build.newsize, queue_build.starttime, queue_build.endtime FROM queue_build WHERE queue_build.fid = ? AND queue_build.building_slot = ?");
					$query->execute(array($eos_firm_id, $j));
					$new_bldg = $query->fetch(PDO::FETCH_ASSOC);
					if(!empty($new_bldg)){
						$bldg_type_id[$j] = $new_bldg["building_type_id"];
						$bldg_type[$j] = $new_bldg["building_type"];
						$sql = "SELECT name AS generic_name, has_image FROM list_".$bldg_type[$j]." WHERE id = ".$bldg_type_id[$j];
						$new_bldg_info = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
						if($temp_building_id = $new_bldg["building_id"]){
							$sql = "SELECT ".$bldg_type[$j]."_name FROM firm_".$bldg_type[$j]." WHERE id = $temp_building_id";
							$bldg_name[$j] = $db->query($sql)->fetchColumn();
						}else{
							$bldg_name[$j] = $new_bldg_info["generic_name"];
						}
						$bldg_size[$j] = $new_bldg["newsize"];
						if($new_bldg_info["has_image"]){
							$filename[$j] = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($new_bldg_info["generic_name"]));
						}else{
							$filename[$j] = "no-image";
						}
						$bldg_image[$j] = '<img class="no_select" src="/eos/images/'.$bldg_type[$j].'/'.$filename[$j].'.gif" width="90" height="40" />';
						$bldg_title[$j] = $bldg_name[$j].' ('.$bldg_size[$j].' m&#178;)';
						$bldg_status[$j] = 'New Construction';
						$cd_on[$j] = 1;
						$cd_total[$j] = $new_bldg["endtime"] - $new_bldg["starttime"];
						$cd_remaining[$j] = $new_bldg["endtime"] - $timenow;
						$cd_icon_back[$j] = 'anim_hammer anim_working';
						$cd_icon[$j] = 'anim_hammer';
						$bldg_link[$j] = 'bldg-expand-status.php?type='.$bldg_type[$j].'&slot='.$j;
					}else{
						$bldg_image[$j] = '<img class="no_select" src="/eos/images/city/bldg_new.gif" width="90" height="40" />';
						$bldg_title[$j] = 'New Building';
						$bldg_status[$j] = '';
						$bldg_link[$j] = 'bldg-build.php?slot='.$j;
					}
				}
			}else{
				if($j == $max_bldg + 1){
					$bldg_image[$j] = '<img class="no_select" src="/eos/images/city/bldg_buy_land.gif" width="90" height="40" />';
					$bldg_link[$j] = 'bldg-buy-land.php';
					$bldg_title[$j] = 'Purchase Land';
					$bldg_status[$j] = '';
				}
			}
		}
		
		$results = array(
			'cd_on' => $cd_on,
			'cd_total' => $cd_total,
			'cd_remaining' => $cd_remaining,
			'bldg_type' => $bldg_type,
			'bldg_id' => $bldg_id,
			'bldg_title' => $bldg_title,
			'bldg_status' => $bldg_status,
			'cd_icon_back' => $cd_icon_back,
			'cd_icon' => $cd_icon,
			'bldg_image' => $bldg_image,
			'bldg_link' => $bldg_link
		);
		returnJSON(1, null, $results);
	}
	else if($action == 'update_slot'){
		$max_buildings = filter_var($_POST['max_buildings'], FILTER_SANITIZE_NUMBER_INT);
		if(!$max_buildings || $max_buildings > 200){
			returnJSON(0, "Parameter error.");
		}
		$slot = filter_var($_POST['slot'], FILTER_SANITIZE_NUMBER_INT);
		if(!$slot || $slot > 200){
			returnJSON(0, "Parameter error.");
		}

		// Find out the player's max buildings 
		$query = $db->prepare("SELECT max_bldg FROM firms WHERE id = ?");
		$query->execute(array($eos_firm_id));
		$max_bldg = $query->fetchColumn();

		$timenow = time();

		// Check in build queue, update expanded buildings
		$query = $db->prepare("SELECT * FROM queue_build WHERE fid = ? AND endtime < ?");
		$query->execute(array($eos_firm_id, $timenow));
		$queue_bcs = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($queue_bcs as $queue_bc){
			$bc_id = $queue_bc["id"];
			$bc_bldg_id = $queue_bc["building_id"];
			$bc_bldg_type = $queue_bc["building_type"];
			$bc_bldg_type_id = $queue_bc["building_type_id"];
			$bc_slot = $queue_bc["building_slot"];
			$bc_size = $queue_bc["newsize"];
			$query = $db->prepare("DELETE FROM queue_build WHERE id = ?");
			$query->execute(array($bc_id));
			if($bc_bldg_type == 'fact'){
				if($bc_bldg_id){
					$query = $db->prepare("UPDATE firm_fact SET size = ? WHERE id = ? AND fid = ?");
					$query->execute(array($bc_size, $bc_bldg_id, $eos_firm_id));
				}else{
					$query = $db->prepare("INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) SELECT ?, ?, name, ?, ? FROM list_fact WHERE id = ?");
					$query->execute(array($eos_firm_id, $bc_bldg_type_id, $bc_size, $bc_slot, $bc_bldg_type_id));
				}
			}else if($bc_bldg_type == 'store'){
				if($bc_bldg_id){
					$query = $db->prepare("UPDATE firm_store SET size = ?, is_expanding = 0 WHERE id = ? AND fid = ?");
					$query->execute(array($bc_size, $bc_bldg_id, $eos_firm_id));
				}else{
					$query = $db->prepare("INSERT INTO firm_store (fid, store_id, store_name, size, slot) SELECT ?, ?, name, ?, ? FROM list_store WHERE id = ?");
					$query->execute(array($eos_firm_id, $bc_bldg_type_id, $bc_size, $bc_slot, $bc_bldg_type_id));
				}
			}else if($bc_bldg_type == 'rnd'){
				if($bc_bldg_id){
					$query = $db->prepare("UPDATE firm_rnd SET size = ? WHERE id = ? AND fid = ?");
					$query->execute(array($bc_size, $bc_bldg_id, $eos_firm_id));
				}else{
					$query = $db->prepare("INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) SELECT ?, ?, name, ?, ? FROM list_rnd WHERE id = ?");
					$query->execute(array($eos_firm_id, $bc_bldg_type_id, $bc_size, $bc_slot, $bc_bldg_type_id));
				}
			}
		}
		
		$cd_on = 0;
		$cd_total = 0;
		$cd_remaining = 0;
		$bldg_type = '';
		$bldg_type_id = 0;
		$bldg_id = 0;
		$bldg_title = '';
		$bldg_status = '';
		$cd_icon_back = 'anim_placeholder';
		$cd_icon = 'anim_placeholder';
		$bldg_image = '';
		$bldg_link = '';
		if($slot <= $max_bldg){
			$sql = "(SELECT firm_fact.id, 'fact' AS bldg_type, firm_fact.fact_name AS bldg_name, firm_fact.size, list_fact.name AS generic_name, list_fact.has_image FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = :eos_firm_id AND firm_fact.slot = :slot) UNION (SELECT firm_store.id, 'store' AS bldg_type, firm_store.store_name, firm_store.size, list_store.name, list_store.has_image FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = :eos_firm_id AND firm_store.slot = :slot) UNION (SELECT firm_rnd.id, 'rnd' AS bldg_type, firm_rnd.rnd_name, firm_rnd.size, list_rnd.name, list_rnd.has_image FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = :eos_firm_id AND firm_rnd.slot = :slot)";
			$query = $db->prepare($sql);
			$query->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $slot));
			$bldg = $query->fetch(PDO::FETCH_ASSOC);
			
			if(!empty($bldg)){
				$bldg_id = $bldg["id"];
				$bldg_type = $bldg["bldg_type"];
				$bldg_name = $bldg["bldg_name"];
				$bldg_size = $bldg["size"];
				if($bldg["has_image"]){
					$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg["generic_name"]));
				}else{
					$filename = "no-image";
				}
				$bldg_image = '<img class="no_select" src="/eos/images/'.$bldg_type.'/'.$filename.'.gif" width="90" height="40" />';
				$bldg_title = $bldg_name.' ('.$bldg_size.' m&#178;)';
				
				if($bldg_type == 'fact'){
					// Check production queue, move anything completed to firm warehouse
					$timenow = time();
					$query = $db->prepare("SELECT * FROM queue_prod WHERE fid = ? AND endtime < ?");
					$query->execute(array($eos_firm_id, $timenow));
					$queue_pcs = $query->fetchAll(PDO::FETCH_ASSOC);
					foreach($queue_pcs as $queue_pc){
						$list_fact_pc_id = $queue_pc["id"];
						$list_fact_pc_opid1 = $queue_pc["opid1"];
						$list_fact_pc_opid1_q = $queue_pc["opid1q"];
						$list_fact_pc_opid1_n = $queue_pc["opid1n"];
						$list_fact_pc_opid1_cost = $queue_pc["opid1cost"];
						$query = $db->prepare("DELETE FROM queue_prod WHERE id = ?");
						$query->execute(array($list_fact_pc_id));
						// Check if pid with pidq already exists in warehouse
						$query = $db->prepare("SELECT COUNT(*) AS cnt, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = ? AND fid = ?");
						$query->execute(array($list_fact_pc_opid1, $eos_firm_id));
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
							$query->execute(array($eos_firm_id, $list_fact_pc_opid1, $list_fact_pc_opid1_q, $list_fact_pc_opid1_n, $list_fact_pc_opid1_cost));
						}
					}

					// Check if ffid is producing anything
					$query = $db->prepare("SELECT queue_prod.opid1, queue_prod.opid1q, queue_prod.opid1n, queue_prod.starttime, queue_prod.endtime, list_prod.name, list_prod.has_icon FROM queue_prod LEFT JOIN list_prod ON queue_prod.opid1 = list_prod.id WHERE fid = ? AND ffid = ?");
					$query->execute(array($eos_firm_id, $bldg_id));
					$result_producing = $query->fetch(PDO::FETCH_ASSOC);
					if(!empty($result_producing)){
						if($result_producing["has_icon"]){
							$bldg_status = 'Producing '.$result_producing["opid1n"].' <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($result_producing["name"])).'.gif" />'.' (Q'.$result_producing["opid1q"].')';
						}else{
							$bldg_status = 'Producing '.$result_producing["opid1n"].' '.$result_producing["name"].' (Q'.$result_producing["opid1q"].')';
						}
						$cd_on = 1;
						$cd_total = $result_producing["endtime"] - $result_producing["starttime"];
						$cd_remaining = $result_producing["endtime"] - $timenow;
						$cd_icon_back = 'anim_gear anim_working';
						$cd_icon = 'anim_gear';
						$bldg_link = 'factories-production.php?ffid='.$bldg_id;
					}
				}else if($bldg_type == 'rnd'){
					// Check research queue
					$query = $db->prepare("SELECT * FROM queue_res WHERE fid = ? AND endtime < ?");
					$query->execute(array($eos_firm_id, $timenow));
					$queue_rcs = $query->fetchAll(PDO::FETCH_ASSOC);
					foreach($queue_rcs as $queue_rc){
						$list_rnd_rq_id = $queue_rc["id"];
						$list_rnd_rq_pid = $queue_rc["pid"];
						$list_rnd_rq_newlevel = $queue_rc["newlevel"];
						
						// Delete from researching queue
						$query = $db->prepare("DELETE FROM queue_res WHERE id = ?");
						$query->execute(array($list_rnd_rq_id));

						// Give research level to firm, but first check whether or not the firm already has this tech
						$sql = "SELECT quality FROM firm_tech WHERE fid='$eos_firm_id' AND pid='$list_rnd_rq_pid'";
						$list_rnd_rq_oldlevel = $db->query($sql)->fetchColumn();
						if($list_rnd_rq_oldlevel){
							if($list_rnd_rq_newlevel > $list_rnd_rq_oldlevel){
								$query = $db->prepare("UPDATE firm_tech SET quality = ?, update_time = ? WHERE fid = ? AND pid = ?");
								$query->execute(array($list_rnd_rq_newlevel, $timenow, $eos_firm_id, $list_rnd_rq_pid));
							}
						}else{
							$query = $db->prepare("INSERT INTO firm_tech (fid, pid, quality, update_time) VALUES (?, ?, ?, ?)");
							$query->execute(array($eos_firm_id, $list_rnd_rq_pid, $list_rnd_rq_newlevel, $timenow));
						}
					}

					// Check if frid is researching anything
					$query = $db->prepare("SELECT * FROM queue_res WHERE fid = ? AND frid = ?");
					$query->execute(array($eos_firm_id, $bldg_id));
					$result_researching = $query->fetch(PDO::FETCH_ASSOC);
					if(!empty($result_researching)){
						$sql = "SELECT name, has_icon FROM list_prod WHERE id = ".$result_researching["pid"];
						$list_rnd_res_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
						if($list_rnd_res_prod["has_icon"]){
							$bldg_status = 'Researching <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_rnd_res_prod["name"])).'.gif" /> to quality '.$result_researching["newlevel"];
						}else{
							$bldg_status = 'Researching '.$list_rnd_res_prod["name"].' to quality '.$result_researching["newlevel"];
						}
						$cd_on = 1;
						$cd_total = $result_researching["endtime"] - $result_researching["starttime"];
						$cd_remaining = $result_researching["endtime"] - $timenow;
						$cd_icon_back = 'anim_gear anim_working';
						$cd_icon = 'anim_gear';
						$bldg_link = 'rnd-res.php?frid='.$bldg_id;
					}
				}
				if(!$cd_on){
					//Check if building_id is expanding
					$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
					$query->execute(array($bldg_type, $bldg_id));
					$result_expanding = $query->fetch(PDO::FETCH_ASSOC);
					if(!empty($result_expanding)){
						$bldg_status = 'Expanding to '.$result_expanding["newsize"].' m&#178;';
						$cd_on = 1;
						$cd_total = $result_expanding["endtime"] - $result_expanding["starttime"];
						$cd_remaining = $result_expanding["endtime"] - $timenow;
						$cd_icon_back = 'anim_hammer anim_working';
						$cd_icon = 'anim_hammer';
						$bldg_link = 'bldg-expand-status.php?type='.$bldg_type.'&id='.$bldg_id;
					}else{
						$bldg_status = 'Ready';
						if($bldg_type == 'fact'){
							$bldg_link = 'factories-production.php?ffid='.$bldg_id;
						}else if($bldg_type == 'store'){
							$bldg_link = 'stores-sell.php?fsid='.$bldg_id;
						}else if($bldg_type == 'rnd'){
							$bldg_link = 'rnd-res.php?frid='.$bldg_id;
						}
					}
				}
			}else{
				//check new building
				$query = $db->prepare("SELECT queue_build.building_id, queue_build.building_type, queue_build.building_type_id, queue_build.newsize, queue_build.starttime, queue_build.endtime FROM queue_build WHERE queue_build.fid = ? AND queue_build.building_slot = ?");
				$query->execute(array($eos_firm_id, $slot));
				$new_bldg = $query->fetch(PDO::FETCH_ASSOC);
				if(!empty($new_bldg)){
					$bldg_type_id = $new_bldg["building_type_id"];
					$bldg_type = $new_bldg["building_type"];
					$sql = "SELECT name AS generic_name, has_image FROM list_".$bldg_type." WHERE id = ".$bldg_type_id;
					$new_bldg_info = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
					if($temp_building_id = $new_bldg["building_id"]){
						$sql = "SELECT ".$bldg_type."_name FROM firm_".$bldg_type." WHERE id = $temp_building_id";
						$bldg_name = $db->query($sql)->fetchColumn();
					}else{
						$bldg_name = $new_bldg_info["generic_name"];
					}
					$bldg_size = $new_bldg["newsize"];
					if($new_bldg_info["has_image"]){
						$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($new_bldg_info["generic_name"]));
					}else{
						$filename = "no-image";
					}
					$bldg_image = '<img class="no_select" src="/eos/images/'.$bldg_type.'/'.$filename.'.gif" width="90" height="40" />';
					$bldg_title = $bldg_name.' ('.$bldg_size.' m&#178;)';
					$bldg_status = 'New Construction';
					$cd_on = 1;
					$cd_total = $new_bldg["endtime"] - $new_bldg["starttime"];
					$cd_remaining = $new_bldg["endtime"] - $timenow;
					$cd_icon_back = 'anim_hammer anim_working';
					$cd_icon = 'anim_hammer';
					$bldg_link = 'bldg-expand-status.php?type='.$bldg_type.'&slot='.$slot;
				}else{
					$bldg_image = '<img class="no_select" src="/eos/images/city/bldg_new.gif" width="90" height="40" />';
					$bldg_title = 'New Building';
					$bldg_status = '';
					$bldg_link = 'bldg-build.php?slot='.$slot;
				}
			}
		}else{
			if($slot == $max_bldg + 1){
				$bldg_image = '<img class="no_select" src="/eos/images/city/bldg_buy_land.gif" width="90" height="40" />';
				$bldg_link = 'bldg-buy-land.php';
				$bldg_title = 'Purchase Land';
				$bldg_status = '';
			}
		}
		
		$next_slot_is_vacant_lot = 0;
		if($slot == $max_bldg && $max_buildings > $max_bldg){
			$next_slot_is_vacant_lot = 1;
		}
		
		$results = array(
			'cd_on' => $cd_on,
			'cd_total' => $cd_total,
			'cd_remaining' => $cd_remaining,
			'bldg_type' => $bldg_type,
			'bldg_id' => $bldg_id,
			'bldg_title' => $bldg_title,
			'bldg_status' => $bldg_status,
			'cd_icon_back' => $cd_icon_back,
			'cd_icon' => $cd_icon,
			'bldg_image' => $bldg_image,
			'bldg_link' => $bldg_link,
			'next_slot_is_vacant_lot' => $next_slot_is_vacant_lot
		);
		returnJSON(1, null, $results);
	}
	else if($action == 'update_name'){
		$building_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
		$building_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
		$building_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

		if($building_type == 'fact' && !$ctrl_fact_sell){
			returnJSON(0, "Not authorized.");
		}
		if($building_type == 'store' && !$ctrl_store_sell){
			returnJSON(0, "Not authorized.");
		}
		if($building_type == 'rnd' && !$ctrl_rnd_sell){
			returnJSON(0, "Not authorized.");
		}
		if(!$building_name){
			returnJSON(0, "Name cannot be blank.");
		}
		if(strlen($building_name) > 24 || strlen($building_name) < 1){
			returnJSON(0, "Name must be between 1 and 24 characters.");
		}

		//Change name
		if($building_type == 'fact'){
			$query_edit = $db->prepare("UPDATE firm_fact SET fact_name = ? WHERE id = ?");
			$query_bldg = $db->prepare("SELECT fact_name AS building_name, slot, size FROM firm_fact WHERE id = ?");
		}
		else if($building_type == 'store'){
			$query_edit = $db->prepare("UPDATE firm_store SET store_name = ? WHERE id = ?");
			$query_bldg = $db->prepare("SELECT store_name AS building_name, slot, size FROM firm_store WHERE id = ?");
		}
		else if($building_type == 'rnd'){
			$query_edit = $db->prepare("UPDATE firm_rnd SET rnd_name = ? WHERE id = ?");
			$query_bldg = $db->prepare("SELECT rnd_name AS building_name, slot, size FROM firm_rnd WHERE id = ?");
		}else{
			returnJSON(0, "Undefined building type.");
		}
		$query_edit->execute(array($building_name, $building_id));
		$query_bldg->execute(array($building_id));
		$bldg = $query_bldg->fetch(PDO::FETCH_ASSOC);
		if(empty($bldg)){
			returnJSON(0, "DB failed.");
		}
		returnJSON(1, '', array('name' => $bldg['building_name'], 'size' => $bldg['size'], 'slot' => $bldg['slot']));
	}
	else if($action == 'swap_bldgs'){
		$bldg_1 = explode("_", filter_var($_POST['bldg_1'], FILTER_SANITIZE_STRING));
		$bldg_2 = explode("_", filter_var($_POST['bldg_2'], FILTER_SANITIZE_STRING));
		if(empty($bldg_1) || empty($bldg_2)){
			returnJSON(0, "Parameter error.");
		}
		$bldg_1_slot = 0 + filter_var($bldg_1[0], FILTER_SANITIZE_NUMBER_INT);
		$bldg_1_id = $bldg_1[1];
		$bldg_1_type = $bldg_1[2];
		$bldg_2_slot = 0 + filter_var($bldg_2[0], FILTER_SANITIZE_NUMBER_INT);
		$bldg_2_id = $bldg_2[1];
		$bldg_2_type = $bldg_2[2];
		if(!$bldg_1_slot || !$bldg_2_slot || !$bldg_1_type || !$bldg_2_type){
			returnJSON(0, "Parameter error.");
		}
		if($bldg_1_id == $bldg_2_id && $bldg_1_type == $bldg_2_type){
			returnJSON(0, "Swapping nothing... Done.");
		}
		$types_allowed = array('fact', 'store', 'rnd', 'queue', 'vacant');
		if(!in_array($bldg_1_type, $types_allowed)){
			returnJSON(0, "First slot's type is not allowed: ".$bldg_1_type);
		}
		if(!in_array($bldg_2_type, $types_allowed)){
			returnJSON(0, "Second slot's type is not allowed: ".$bldg_1_type);
		}
		// Get max number of slots
		$sql = "SELECT max_bldg FROM firms WHERE id = $eos_firm_id";
		$max_bldg = $db->query($sql)->fetchColumn();
		if($bldg_1_slot < 1 || $bldg_2_slot < 1 || $bldg_1_slot > $max_bldg || $bldg_2_slot > $max_bldg){
			returnJSON(0, "You have successfully sold the building for $0.01. Just kidding.");
		}

		// Prepare queries
		$query_slot_count = $db->prepare("SELECT SUM(cnt) AS total_cnt FROM (
			(SELECT COUNT(*) AS cnt FROM firm_fact WHERE fid = :fid AND slot = :slot) UNION
			(SELECT COUNT(*) AS cnt FROM firm_store WHERE fid = :fid AND slot = :slot) UNION
			(SELECT COUNT(*) AS cnt FROM firm_rnd WHERE fid = :fid AND slot = :slot) UNION
			(SELECT COUNT(*) AS cnt FROM queue_build WHERE fid = :fid AND building_slot = :slot)
		) AS a");
		$query_fact_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_fact WHERE fid = :fid AND slot = :slot AND id = :id");
		$query_store_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_store WHERE fid = :fid AND slot = :slot AND id = :id");
		$query_rnd_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_rnd WHERE fid = :fid AND slot = :slot AND id = :id");
		$query_queue_count = $db->prepare("SELECT COUNT(*) AS cnt, endtime FROM queue_build WHERE fid = :fid AND building_slot = :slot AND id = :id");
		$update_fact_slot = $db->prepare("UPDATE firm_fact SET slot = :new_slot WHERE fid = :fid AND slot = :slot AND id = :id");
		$update_store_slot = $db->prepare("UPDATE firm_store SET slot = :new_slot WHERE fid = :fid AND slot = :slot AND id = :id");
		$update_rnd_slot = $db->prepare("UPDATE firm_rnd SET slot = :new_slot WHERE fid = :fid AND slot = :slot AND id = :id");
		$update_queue_slot = $db->prepare("UPDATE queue_build SET building_slot = :new_slot WHERE fid = :fid AND building_slot = :slot AND id = :id");

		$query_slot_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_1_slot));
		$total_count = $query_slot_count->fetchColumn();
		if($bldg_1_type == 'vacant'){
			if($total_count != 0){
				returnJSON(0, "Slot 1 is NOT vacant!");
			}
		}else if($total_count != 1){
			returnJSON(0, "Slot 1 cannot be found.");
		}else{
			if($bldg_1_type == 'fact'){
				$query_fact_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
				if(!$query_fact_count->fetchColumn()){
					returnJSON(0, "Building in slot 1 cannot be found.");
				}
			}else if($bldg_1_type == 'store'){
				$query_store_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
				if(!$query_store_count->fetchColumn()){
					returnJSON(0, "Building in slot 1 cannot be found.");
				}
			}else if($bldg_1_type == 'rnd'){
				$query_rnd_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
				if(!$query_rnd_count->fetchColumn()){
					returnJSON(0, "Building in slot 1 cannot be found.");
				}
			}else if($bldg_1_type == 'queue'){
				$query_queue_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
				$queue_item = $query_queue_count->fetch(PDO::FETCH_ASSOC);
				if(!$queue_item['cnt']){
					returnJSON(0, "Construction in slot 1 cannot be found.");
				}
				if(time() - $queue_item['endtime'] < 10){
					returnJSON(0, "Construction in slot 1 is almost finished, please close and re-open the dialog when it's done.");
				}
			}
		}
		
		$query_slot_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_2_slot));
		$total_count = $query_slot_count->fetchColumn();
		if($bldg_2_type == 'vacant'){
			if($total_count != 0){
				returnJSON(0, "Slot 2 is NOT vacant!");
			}
		}else if($total_count != 1){
			returnJSON(0, "Slot 2 cannot be found.");
		}else{
			if($bldg_2_type == 'fact'){
				$query_fact_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
				if(!$query_fact_count->fetchColumn()){
					returnJSON(0, "Building in slot 2 cannot be found.");
				}
			}else if($bldg_2_type == 'store'){
				$query_store_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
				if(!$query_store_count->fetchColumn()){
					returnJSON(0, "Building in slot 2 cannot be found.");
				}
			}else if($bldg_2_type == 'rnd'){
				$query_rnd_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
				if(!$query_rnd_count->fetchColumn()){
					returnJSON(0, "Building in slot 2 cannot be found.");
				}
			}else if($bldg_2_type == 'queue'){
				$query_queue_count->execute(array(':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
				$queue_item = $query_queue_count->fetch(PDO::FETCH_ASSOC);
				if(!$queue_item['cnt']){
					returnJSON(0, "Construction in slot 2 cannot be found.");
				}
				if(time() - $queue_item['endtime'] < 10){
					returnJSON(0, "Construction in slot 2 is almost finished, please close and re-open the dialog when it's done.");
				}
			}
		}
		
		// All good so far, so let's do the transfer
		if($bldg_1_type !== 'vacant'){
			if($bldg_1_type == 'fact'){
				$update_fact_slot->execute(array(':new_slot' => $bldg_2_slot,':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
			}else if($bldg_1_type == 'store'){
				$update_store_slot->execute(array(':new_slot' => $bldg_2_slot,':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
			}else if($bldg_1_type == 'rnd'){
				$update_rnd_slot->execute(array(':new_slot' => $bldg_2_slot,':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
			}else if($bldg_1_type == 'queue'){
				$update_queue_slot->execute(array(':new_slot' => $bldg_2_slot,':fid' => $eos_firm_id, ':slot' => $bldg_1_slot, ':id' => $bldg_1_id));
			}
		}
		if($bldg_2_type !== 'vacant'){
			if($bldg_2_type == 'fact'){
				$update_fact_slot->execute(array(':new_slot' => $bldg_1_slot,':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
			}else if($bldg_2_type == 'store'){
				$update_store_slot->execute(array(':new_slot' => $bldg_1_slot,':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
			}else if($bldg_2_type == 'rnd'){
				$update_rnd_slot->execute(array(':new_slot' => $bldg_1_slot,':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
			}else if($bldg_2_type == 'queue'){
				$update_queue_slot->execute(array(':new_slot' => $bldg_1_slot,':fid' => $eos_firm_id, ':slot' => $bldg_2_slot, ':id' => $bldg_2_id));
			}
		}

		returnJSON(1, '', array('slot_1' => $bldg_1_slot, 'slot_2' => $bldg_2_slot));
	}
	else{
		returnJSON(0, "Action not defined.");
	}
?>