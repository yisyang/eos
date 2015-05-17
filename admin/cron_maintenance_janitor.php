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
	if(!ini_get('safe_mode')){
		set_time_limit(600);
	}

	function kzc_msg($firm_name){
		$kzc_msg_array = array("<b>$firm_name burned down in an electrical fire.</b><br />There was a serious electrical fire, the good news is no one was killed or seriously injured. The bad news is all buildings belonging to $firm_name were burned down to the ground.","<b>$firm_name swept by a tornado.</b><br />A tornado in Econosia has destroyed all factories, stores, and research buildings belonging to $firm_name.","<b>$firm_name flooded.</b><br />All factories, stores, and research buildings belonging to $firm_name have been destroyed by a flood.","<b>$firm_name eaten by termites.</b><br />A factory belonging to $firm_name has collapsed, causing many injuries. Further studies revealed $firm_name\'s other buildings to be structurally unsafe as well due to prolonged termite infestation. Employees fear for their safety and have abandoned the company.","<b>Nuclear meltdown near $firm_name.</b><br />Some idiots have been secretly building a nuclear enrichment facility near $firm_name, but have accidentally caused a nuclear meltdown. Everyone has fled the area.","<b>$firm_name struck by a meteor.</b><br />Nobody saw it coming, but a comet suddenly changed its direction as it passed near Earth\'s orbit, and fell directly on top of $firm_name\'s headquarters, destroying all buildings in the area.","<b>$firm_name destroyed in an eruption.</b><br />An inactive volcano near the Econosia suddenly became active and destroyed the much of the city, including all buildings belonging to $firm_name.","<b>$firm_name destroyed by an earthquake.</b><br />The earth has opened itself and swallowed $firm_name\'s headquarters, nearby buildings were also destroyed by subsequent seismic waves.");
		$kzc_msg_count = sizeof($kzc_msg_array);
		return $kzc_msg_array[mt_rand(0,$kzc_msg_count-1)];
	}
	function update_cleanup(){
		global $db, $cron_report_msg, $timeupdate_dt, $timestart;
		$timenow = time();
		$timeupdate = $timenow;
		$timeupdate_tick = floor(($timenow - 1327104000)/900);
		$time_diff = intval(date("Gi",$timenow));
		$date_ran = intval(date("Ymd",$timenow));

		// Comment to disable time restriction
		if($time_diff < 2330 || $time_diff > 2359){
			$cron_report_msg = "Job failed because it was ran at a non-scheduled time.";
			return false;
		}

		$sql = "SELECT value FROM world_var WHERE name = 'su_last_ran_cleanup'";
		$last_ran = $db->query($sql)->fetchColumn();
		if($date_ran == $last_ran){
			$cron_report_msg = "Server cleanup had previously been ran for the date: ".$last_ran;
			return false;
		}

		// Remove old market listings and requests
		$sql = "INSERT INTO firm_news (fid, body, date_created) (SELECT fid, 'WARNING: One or more of your B2B listings have been sitting on the market for over 60 days, please re-list them or they will be thrown out as soon as their listed time reach 90 days.', NOW() FROM market_prod WHERE listed < DATE_ADD(NOW(), INTERVAL -60 DAY) GROUP BY fid)";
		$db->query($sql);
		
		$sql = "INSERT INTO firm_news (fid, body, date_created) (SELECT fid, 'NOTICE: Some of your B2B listings were thrown out because they have been on the market for over 90 days.', NOW() FROM market_prod WHERE listed < DATE_ADD(NOW(), INTERVAL -90 DAY) GROUP BY fid)";
		$db->query($sql);
		
		$sql = "INSERT INTO firm_news (fid, body, date_created) (SELECT fid, 'NOTICE: Some of your B2B requests were canceled because they have been on the market for over 90 days.', NOW() FROM market_requests WHERE requested < DATE_ADD(NOW(), INTERVAL -90 DAY) GROUP BY fid)";
		$db->query($sql);
		
		$sql = "DELETE FROM market_prod WHERE listed < DATE_ADD(NOW(), INTERVAL -90 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM market_requests WHERE listed < DATE_ADD(NOW(), INTERVAL -90 DAY)";
		$db->query($sql);
		
		$sql = "DELETE FROM stock_bid WHERE expiration <= CURDATE()";
		$db->query($sql);
		$sql = "DELETE FROM stock_ask WHERE expiration <= CURDATE()";
		$db->query($sql);
		$sql = "DELETE FROM stock_edit_temp WHERE expiration <= CURDATE()";
		$db->query($sql);
		
		// KILL ZOMBIE COMPANIES, YEAH!!!
		$sql = "SELECT firms.id, firms.name, firms.level, firms.cash, firms.loan, firms.fame_level, firms.max_bldg, firms_extended.is_public, firms_extended.ceo FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms.id > 100 AND firms.last_active < DATE_ADD(NOW(), INTERVAL -30 DAY)";
		$zombie_companies = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($zombie_companies as $zombie_company){
			$target_firm_is_public = $zombie_company["is_public"];

			//Estimate Worth
			$target_firm_id = $zombie_company["id"];
			$target_firm_name = $zombie_company["name"];
			$target_firm_level = $zombie_company["level"];
			$target_firm_ceo = $zombie_company["ceo"];
			$networth = 0;
			$networth += $zombie_company["cash"];
			$networth -= $zombie_company["loan"];
			$networth += 1000*pow(1.4,$zombie_company["fame_level"])-1000;
			//Land Value, surprisingly, the equation is the square of pascal's trangle...
			$max_bldg = $firm["max_bldg"];
			$networth += 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
			
			//Building Value, using size * cost
			$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $target_firm_id";
			$networth += $db->query($sql)->fetchColumn();
			
			$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $target_firm_id";
			$networth += $db->query($sql)->fetchColumn();
			
			$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $target_firm_id";
			$networth += $db->query($sql)->fetchColumn();
			
			//Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
			$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $target_firm_id";
			$networth += $db->query($sql)->fetchColumn();

			//Warehouse value, using value, pidq, pidn
			$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $target_firm_id AND firm_wh.pidn";
			$networth += $db->query($sql)->fetchColumn();

			//Market value, using value, pidq, pidn
			$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $target_firm_id";
			$networth += $db->query($sql)->fetchColumn();

			$f_sell_price = floor(0.95 * $networth);
			
			if($target_firm_is_public){
				//Find shareholder info
				$target_firm_shares_os = $db->query("SELECT shares_os FROM firm_stock WHERE fid = $target_firm_id")->fetchColumn();
				$target_firm_shareholders = $db->prepare("SELECT pid, shares FROM player_stock WHERE fid = $target_firm_id")->fetchAll(PDO::FETCH_ASSOC);
				$target_firm_shareholders_count = count($target_firm_shareholders);
				
				//Delete firm, and any building/queue/market/log involving fid, and update player
				$sql = "DELETE FROM firm_fact WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_rnd WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_store WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_news WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_quest WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_tech WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_wh WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM history_firms WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM history_stock WHERE fid='$target_firm_id'";
				$db->query($sql);
				$sql = "DELETE FROM history_stock_fine WHERE fid='$target_firm_id'";
				$db->query($sql);
				$sql = "DELETE FROM market_prod WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM market_requests WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM queue_build WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM queue_prod WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM queue_res WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_stock WHERE fid='$target_firm_id'";
				$db->query($sql);
				$sql = "DELETE FROM player_stock WHERE fid='$target_firm_id'";
				$db->query($sql);
				$sql = "DELETE es_positions.*, es_applications.* FROM es_positions LEFT JOIN es_applications ON es_positions.id = es_applications.esp_id WHERE es_positions.fid = $target_firm_id";
				$db->query($sql);
				$sql = "INSERT INTO player_news (pid, body, date_created) SELECT firms_positions.pid, CONCAT('Dear ', firms_positions.title,', your job with ', firms.name, ' has ended because the company no longer exists.'), NOW() FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firms_positions WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "UPDATE log_management SET endtime = NOW() WHERE endtime > NOW() AND fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firms_extended WHERE id = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firms WHERE id = $target_firm_id";
				$db->query($sql);
				$sql = "UPDATE players SET fid = 0 WHERE fid = '$target_firm_id'";
				$db->query($sql);

				//Give Money and Send Message
				if($target_firm_shareholders_count){
					$kzc_msg_this_all = kzc_msg($target_firm_name);
					foreach($target_firm_shareholders as $shareholder){
						$target_firm_sh_id = $shareholder["pid"];
						$target_firm_sh_shares = $shareholder["shares"];
						if($f_sell_price > 1){
							$target_firm_sh_payment = floor($f_sell_price * $target_firm_sh_shares / $target_firm_shares_os);
							if($target_firm_sh_payment > 0){
								$query = $db->prepare("UPDATE players SET player_cash = player_cash + ? WHERE id = ?");
								$query->execute(array($target_firm_sh_payment, $target_firm_sh_id));
								$kzc_msg_this = $kzc_msg_this_all.'<br />'.$target_firm_name.' is history, and as a shareholder you have received '.(number_format($f_sell_price,2,'.',',')/100).' in exchange.';
							}else{
								$kzc_msg_this = $kzc_msg_this_all.'<br />'.$target_firm_name.' is history, but you have received nothing as the company had negative equity.';
							}
						}else{
							$kzc_msg_this = $kzc_msg_this_all.'<br />'.$target_firm_name.' is history, but you have received nothing as the company had negative equity.';
						}
						$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (?, ?, NOW())");
						$query->execute(array($target_firm_sh_id, $kzc_msg_this));
					}
				}
			}else{
				//Delete firm, and any building/queue/market/log involving fid, and update player
				$sql = "DELETE FROM firm_fact WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_rnd WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_store WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_news WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_quest WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_tech WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firm_wh WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM history_firms WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM market_prod WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM market_requests WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM queue_build WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM queue_prod WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM queue_res WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE es_positions.*, es_applications.* FROM es_positions LEFT JOIN es_applications ON es_positions.id = es_applications.esp_id WHERE es_positions.fid = $target_firm_id";
				$db->query($sql);
				$sql = "INSERT INTO player_news (pid, body, date_created) SELECT firms_positions.pid, CONCAT('Dear ', firms_positions.title, ', your job with ', firms.name, ' has ended because the company no longer exists.'), NOW() FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firms_positions WHERE fid = $target_firm_id";
				$db->query($sql);
				$sql = "UPDATE log_management SET endtime = NOW() WHERE endtime > NOW() AND fid = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firms_extended WHERE id = $target_firm_id";
				$db->query($sql);
				$sql = "DELETE FROM firms WHERE id = $target_firm_id";
				$db->query($sql);
				$sql = "UPDATE players SET fid = 0 WHERE fid = '$target_firm_id'";
				$db->query($sql);
				
				//Give Money and Send Message
				$kzc_msg_this = kzc_msg($target_firm_name);
				if($f_sell_price > 0){
					$query = $db->prepare("UPDATE players SET player_cash = player_cash + ? WHERE id = ?");
					$query->execute(array($f_sell_price, $target_firm_ceo));
					$kzc_msg_this .= '<br />'.$target_firm_name.' is history, but fortunately you were able to obtain '.number_format($f_sell_price/100,2,'.',',').' from the insurance company.';
				}else{
					$kzc_msg_this .= '<br />'.$target_firm_name.' is history, and you felt good because the company had negative equity.';
				}
				$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES (?, ?, NOW())");
				$query->execute(array($target_firm_ceo, $kzc_msg_this));
			}
		}

		// Delete and archive old logs
		// $sql = "INSERT IGNORE INTO EOS_ARCHIVES.archive_history_firms SELECT * FROM history_firms";
		// $db->query($sql);
		$sql = "DELETE FROM history_firms WHERE history_date < DATE_ADD(NOW(), INTERVAL -31 DAY)";
		$db->query($sql);
		// $sql = "INSERT IGNORE INTO EOS_ARCHIVES.archive_history_players SELECT * FROM history_players";
		// $db->query($sql);
		$sql = "DELETE FROM history_players WHERE history_date < DATE_ADD(NOW(), INTERVAL -31 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM history_prod WHERE history_tick < ?";
		$query = $db->prepare($sql);
		$query->execute(array($timeupdate_tick - 1400));
		$sql = "DELETE FROM firm_news WHERE date_created < DATE_ADD(NOW(), INTERVAL -31 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM player_news WHERE date_created < DATE_ADD(NOW(), INTERVAL -31 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM log_market_prod WHERE transaction_time < DATE_ADD(NOW(), INTERVAL -31 DAY)";
		$db->query($sql);
		// $sql = "INSERT IGNORE INTO EOS_ARCHIVES.archive_history_stock SELECT * FROM history_stock";
		// $db->query($sql);
		$sql = "DELETE FROM history_stock WHERE history_date < DATE_ADD(CURDATE(), INTERVAL -31 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM history_stock_fine WHERE history_datetime < DATE_ADD(NOW(), INTERVAL -8 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM log_limited_actions WHERE action_time < DATE_ADD(NOW(), INTERVAL -31 DAY)";
		$db->query($sql);
		$sql = "DELETE FROM log_revenue WHERE transaction_time < DATE_ADD(NOW(), INTERVAL -8 DAY)";
		$db->query($sql);
		// Delete shelves belonging to dead stores
		$sql = "DELETE firm_store_shelves.* FROM firm_store_shelves LEFT JOIN firm_store ON firm_store_shelves.fsid = firm_store.id WHERE firm_store.id IS NULL";
		$db->query($sql);
		// Tough one: DELETE FROM log_sales
		$result = $db->query("SELECT id, tick FROM log_sales ORDER BY id ASC LIMIT 0,1")->fetch(PDO::FETCH_ASSOC);
		$log_sales_starting_id = $result['id'];
		$log_sales_starting_tick = $result['tick'];
		$result = $db->query("SELECT id, tick FROM log_sales ORDER BY id DESC LIMIT 0,1")->fetch(PDO::FETCH_ASSOC);
		$log_sales_final_id = $result['id'];
		$log_sales_final_tick = $result['tick'];
		$log_sales_guess_id = floor(($log_sales_starting_id + $log_sales_final_id) / 2);
		$query = $db->prepare("SELECT tick FROM log_sales WHERE id = ?");
		$query->execute(array($log_sales_guess_id));
		$log_sales_guess_tick = $query->fetchColumn();
		while($log_sales_guess_tick + 97 > $log_sales_final_tick){
			$log_sales_guess_id = floor(($log_sales_starting_id + $log_sales_guess_id) / 2);
			$query->execute(array($log_sales_guess_id));
			$log_sales_guess_tick = $query->fetchColumn();
		}
		$sql = "DELETE FROM log_sales WHERE id < $log_sales_guess_id";
		$db->query($sql);
		
		// Summarize event
		$sql = "UPDATE world_var SET value = '$date_ran' WHERE name = 'su_last_ran_cleanup'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$timetaken = microtime(1) - $timestart;
		$sql = "UPDATE world_var SET value = '$timetaken' WHERE name = 'su_last_ran_cleanup_dur'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$cron_report_msg = "OK";
		return true;
	}
	
	if(update_cleanup()){
		$subject = "Cron Job - EoS - Success - Cleanup";
	}
	
	if($cron_report_msg != "OK" || $cron_report_msg_level_2){
		$subject = "Cron Job - EoS - FAILED - Cleanup";

		$timetaken = microtime(1) - $timestart;
		$cron_report_msg .= $cron_report_msg_level_2.'<br />Time taken (s): '.$timetaken;

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
		echo 'OK';
	}else{
		$timetaken = microtime(1) - $timestart;
		$cron_report_msg .= '<br />All done.'.'<br />Time taken (s): '.$timetaken;
		echo $cron_report_msg;
	}
?>