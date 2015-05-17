<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	require '../scripts/db/dbconnrjeos.php';
	
	$cron_report_msg = "";
	date_default_timezone_set('America/Los_Angeles');
	$timestart = microtime(1);
	if(!ini_get('safe_mode')){
		set_time_limit(600);
	}

	function daily_update(){
		global $db, $cron_report_msg, $timestart;
		$timenow = time();
		$timeupdate = mktime(23, 59, 59, date("n",$timenow), date("j",$timenow)-1, date("Y",$timenow));
		$timeupdate_dt = date("Y-m-d H:i:s",$timeupdate);
		$time_diff = intval(date("Gi",$timenow));
		$date_ran = intval(date("Ymd",$timenow));

		// Comment to disable time restriction
		if($time_diff < 0 || $time_diff > 10){
			$cron_report_msg = "Job failed because it was ran at a non-scheduled time.";
			return false;
		}

		$sql = "SELECT value FROM world_var WHERE name = 'su_last_ran'";
		$last_ran = $db->query($sql)->fetchColumn();
		if($date_ran == $last_ran){
			$cron_report_msg = "Server update had previously been ran for the date: ".$last_ran;
			return false;
		}
		$sql = "UPDATE world_var SET value = '$date_ran' WHERE name = 'su_last_ran'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		// Add 3 quests
		$sql = "UPDATE firms SET quests_available = LEAST(quests_available + 3, 10) WHERE quests_available < 10";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}

echo microtime(1) - $timestart.': Updating firms networth<br />';
		// Update all active firms' networth
		// Firm Networth = cash + $10*1.4^fame_level + 100% of land value + 100% of buildings value + about 50% of research value + 100% of warehouse quality adjusted value
		$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000, 12345678901234567890);
		$firm_level_size = sizeof($firm_level_lowlimit);
		$sql = "SELECT id, cash, loan, level, fame_level, max_bldg FROM firms";
		$firms = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($firms as $firm){
			$target_firm_id = $firm["id"];
			$target_firm_level = $firm["level"];
			$networth = 0;
			$appraised_property = 0;
			$appraised_inventory = 0;
			$appraised_intangible = 0;
			$networth += $firm["cash"];
			$networth -= $firm["loan"];
			$appraised_intangible += 1000*pow(1.4, $firm["fame_level"])-1000;

			// Land Value, surprisingly, the equation is the square of pascal's trangle...
			$max_bldg = $firm["max_bldg"];
			$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);

			// Building Value, using size * cost
			$sql = "SELECT IFNULL(SUM(firm_fact.size*list_fact.cost),0) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $target_firm_id";
			$appraised_property += $db->query($sql)->fetchColumn();
			$sql = "SELECT IFNULL(SUM(firm_store.size*list_store.cost),0) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $target_firm_id";
			$appraised_property += $db->query($sql)->fetchColumn();
			$sql = "SELECT IFNULL(SUM(firm_rnd.size*list_rnd.cost),0) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $target_firm_id";
			$appraised_property += $db->query($sql)->fetchColumn();

			// Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
			$sql = "SELECT IFNULL(SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)),0) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $target_firm_id";
			$appraised_intangible += $db->query($sql)->fetchColumn();

			// Warehouse value, using value, pidq, pidn
			$sql = "SELECT IFNULL(SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)),0) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $target_firm_id";
			$appraised_inventory += $db->query($sql)->fetchColumn();

			// Market value, using value, pidq, pidn
			$sql = "SELECT IFNULL(SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)),0) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $target_firm_id";
			$appraised_inventory += $db->query($sql)->fetchColumn();
			
			$networth += $appraised_land + $appraised_property + $appraised_inventory + $appraised_intangible;
		
			$networth = floor($networth);
			$new_level = $target_firm_level;
			$next_level = $new_level + 1;
			while(($next_level < $firm_level_size) && ($networth >= $firm_level_lowlimit[$next_level])){
				$new_level = $next_level;
				$next_level += 1;
			}
			if($new_level != $target_firm_level){
				$sql = "UPDATE firms SET networth = $networth, level = $new_level WHERE id = $target_firm_id";
			}else{
				$sql = "UPDATE firms SET networth = $networth WHERE id = $target_firm_id";
			}
			$db->query($sql);
			$sql = "UPDATE firms_extended SET inventory = $appraised_inventory, property = $appraised_property, intangible = $appraised_intangible WHERE id = $target_firm_id";
			$db->query($sql);
		}

echo microtime(1) - $timestart.': Updating stocks history<br />';
		// Update firm stocks history
		$sql = "INSERT INTO history_stock (fid, share_price, history_date) SELECT fid, share_price, CURDATE() FROM firm_stock";
		$db->query($sql);
		$sql = "UPDATE firm_stock LEFT JOIN history_firms ON history_firms.fid = firm_stock.fid AND history_firms.history_date = DATE_ADD(CURDATE(), INTERVAL -7 DAY) LEFT JOIN firms ON firm_stock.fid = firms.id SET firm_stock.share_price_open = firm_stock.share_price, firm_stock.share_price_min = firm_stock.share_price, firm_stock.share_price_max = firm_stock.share_price, firm_stock.7de = IFNULL(firms.networth - history_firms.networth + history_firms.paid_in_capital - firm_stock.paid_in_capital, 0)";
		$db->query($sql);

echo microtime(1) - $timestart.': Summarizing PO<br />';
		// Prepare queries
		$query_give_cash = $db->prepare("UPDATE firms SET cash = cash + :cash WHERE id = :fid");
		$query_decrease_shares_os = $db->prepare("UPDATE firm_stock SET shares_os = shares_os - :shares, paid_in_capital = paid_in_capital - :cash WHERE fid = :fid AND shares_os >= :shares");
		$query_insert_firm_news = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:fid, :body, '$timeupdate_dt')");

		$sql = "SELECT fid, SUM(total_price) AS total_cash, SUM(shares) AS total_shares FROM firm_stock_issued_temp WHERE type = 'IPO' GROUP BY fid";
		$pos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($pos as $po){
			$query_give_cash->execute(array(':fid' => $po['fid'], ':cash' => $po['total_cash']));
			$query_insert_firm_news->execute(array(':fid' => $po['fid'], ':body' => '<b>IPO Report:</b> Investors bought '.number_format($po['total_shares'],0,'.',',').' shares today for a total of $'.number_format($po['total_cash']/100,2,'.',',').'.'));
		}

		$sql = "SELECT fid, SUM(total_price) AS total_cash, SUM(shares) AS total_shares FROM firm_stock_issued_temp WHERE type = 'SEO' GROUP BY fid";
		$pos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($pos as $po){
			$query_give_cash->execute(array(':fid' => $po['fid'], ':cash' => $po['total_cash']));
			$query_insert_firm_news->execute(array(':fid' => $po['fid'], ':body' => '<b>SEO Report:</b> Investors bought '.number_format($po['total_shares'],0,'.',',').' shares today for a total of $'.number_format($po['total_cash']/100,2,'.',',').'.'));
		}

		$sql = "SELECT fid, SUM(total_price) AS total_cash, SUM(shares) AS total_shares FROM firm_stock_issued_temp WHERE type = 'Buyback' GROUP BY fid";
		$pos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($pos as $po){
			$query_decrease_shares_os->execute(array(':fid' => $po['fid'], ':cash' => $po['total_cash'], ':shares' => $po['total_shares']));
			$query_insert_firm_news->execute(array(':fid' => $po['fid'], ':body' => '<b>Buyback Report:</b> Your company bought back '.number_format($po['total_shares'],0,'.',',').' shares today for a total of $'.number_format($po['total_cash']/100,2,'.',',').'.'));
		}

		// Insert news for all actions
		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issuance.fid, '<b>IPO Report:</b> Nobody bought any shares today.', '$timeupdate_dt' FROM firm_stock_issuance LEFT JOIN firm_stock_issued_temp ON firm_stock_issued_temp.fid = firm_stock_issuance.fid WHERE firm_stock_issued_temp.id IS NULL AND firm_stock_issuance.type = 'IPO' GROUP BY firm_stock_issuance.fid";
		$db->query($sql);

		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issuance.fid, '<b>SEO Report:</b> Nobody bought any shares today.', '$timeupdate_dt' FROM firm_stock_issuance LEFT JOIN firm_stock_issued_temp ON firm_stock_issued_temp.fid = firm_stock_issuance.fid WHERE firm_stock_issued_temp.id IS NULL AND firm_stock_issuance.type = 'SEO' GROUP BY firm_stock_issuance.fid";
		$db->query($sql);

		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issuance.fid, '<b>Buyback Report:</b> Nobody sold any shares today.', '$timeupdate_dt' FROM firm_stock_issuance LEFT JOIN firm_stock_issued_temp ON firm_stock_issued_temp.fid = firm_stock_issuance.fid WHERE firm_stock_issued_temp.id IS NULL AND firm_stock_issuance.type = 'Buyback' GROUP BY firm_stock_issuance.fid";
		$db->query($sql);
		
		// Insert news for completed actions (issued_temp available, but issuance deleted)
		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issued_temp.fid, '<b>IPO Report:</b> Your IPO is a big success! All shares have been purchased.', '$timeupdate_dt' FROM firm_stock_issued_temp LEFT JOIN firm_stock_issuance ON firm_stock_issued_temp.fid = firm_stock_issuance.fid WHERE firm_stock_issued_temp.type = 'IPO' AND firm_stock_issuance.id IS NULL GROUP BY firm_stock_issued_temp.fid";
		$db->query($sql);

		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issued_temp.fid, '<b>SEO Report:</b> Your SEO is a big success! All shares have been purchased.', '$timeupdate_dt' FROM firm_stock_issued_temp LEFT JOIN firm_stock_issuance ON firm_stock_issued_temp.fid = firm_stock_issuance.fid WHERE firm_stock_issued_temp.type = 'SEO' AND firm_stock_issuance.id IS NULL GROUP BY firm_stock_issued_temp.fid";
		$db->query($sql);

		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issued_temp.fid, '<b>Buyback Report:</b> Your Buyback worked! You were able to buyback the requested number of shares at the requested price.', '$timeupdate_dt' FROM firm_stock_issued_temp LEFT JOIN firm_stock_issuance ON firm_stock_issued_temp.fid = firm_stock_issuance.fid WHERE firm_stock_issued_temp.type = 'Buyback' AND firm_stock_issuance.id IS NULL GROUP BY firm_stock_issued_temp.fid";
		$db->query($sql);

		// Delete all issued_temp items
		$sql = "TRUNCATE TABLE firm_stock_issued_temp";
		$db->query($sql);

		// Insert news for expired actions (issuance exists with old expiration date)
		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issuance.fid, 'Your IPO has expired. Please refer to past company news for sales details.', '$timeupdate_dt' FROM firm_stock_issuance WHERE firm_stock_issuance.expiration < NOW() AND firm_stock_issuance.type = 'IPO'";
		$db->query($sql);

		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issuance.fid, 'Your SEO has expired. Please refer to past company news for sales details.', '$timeupdate_dt' FROM firm_stock_issuance WHERE firm_stock_issuance.expiration < NOW() AND firm_stock_issuance.type = 'SEO'";
		$db->query($sql);
		
		$sql = "INSERT INTO firm_news (fid, body, date_created) SELECT firm_stock_issuance.fid, 'Your Buyback has expired. Please refer to past company news for sales details. Unused portions of the deposit have been transferred back to the company.', '$timeupdate_dt' FROM firm_stock_issuance WHERE firm_stock_issuance.expiration < NOW() AND firm_stock_issuance.type = 'Buyback'";
		$db->query($sql);
		
		// Refund unused buyback credit
		$sql = "UPDATE firm_stock_issuance LEFT JOIN firms ON firm_stock_issuance.fid = firms.id SET firms.cash = firms.cash + firm_stock_issuance.shares * firm_stock_issuance.price WHERE firm_stock_issuance.expiration < NOW() AND firm_stock_issuance.type = 'Buyback'";
		$db->query($sql);
		
		// Delete expired actions
		$sql = "DELETE FROM firm_stock_issuance WHERE firm_stock_issuance.expiration < NOW()";
		$db->query($sql);

echo microtime(1) - $timestart.': Updating player fame and nw<br />';
		// Update all players' fame & networth
		// Player Networth = cash + $10*1.4^fame_level + public: 100% of stock value + nonpublic: active_firm_networth + (TODO: 100% of collections value)
		// Player Fame = fame + total company fame_level*100
		$sql = "UPDATE players LEFT JOIN (SELECT players.id, players.player_level, players.player_fame_level, players.player_fame, players.player_cash, players.player_networth, SUM(firms.networth) AS private_networth, SUM(firms.fame_level) AS private_fame_level FROM players LEFT JOIN (SELECT id, ceo FROM firms_extended WHERE !is_public) AS fenp ON players.id = fenp.ceo LEFT JOIN firms ON fenp.id = firms.id GROUP BY players.id) AS a ON players.id = a.id LEFT JOIN (SELECT players.id, SUM(CAST(player_stock.shares AS DECIMAL(20,0)) * CAST(firm_stock.share_price AS DECIMAL(20,0))) AS public_networth, SUM(player_stock.shares * firms.fame_level / firm_stock.shares_os) AS public_fame_level FROM players LEFT JOIN player_stock ON players.id = player_stock.pid LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid LEFT JOIN firms ON firm_stock.fid = firms.id GROUP BY players.id) AS b ON players.id = b.id SET players.player_networth = a.player_cash + IFNULL(a.private_networth,0) + IFNULL(b.public_networth,0), players.player_fame = players.player_fame + 100*(IFNULL(a.private_fame_level,0) + IFNULL(b.public_fame_level,0))";
		$db->query($sql);
		
		$player_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
		$player_level_size = sizeof($player_level_lowlimit);
		$sql = "SELECT id, player_level, player_networth, player_fame_level, player_fame FROM players WHERE last_active > DATE_ADD(NOW(), INTERVAL -7 DAY)";
		$players = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$player_level_up_query = $db->prepare("UPDATE players SET player_level = ?, player_fame_level = ?, player_fame = ? WHERE id = ?");
		
		foreach($players as $player){
			$target_player_fame_level_new = $player["player_fame_level"];
			$target_player_fame_level_next = $target_player_fame_level_new + 1;
			$target_player_fame_exp = $player["player_fame"];
			$target_player_fame_level_next_exp = 100 * $target_player_fame_level_next * $target_player_fame_level_next * $target_player_fame_level_next;
			if($target_player_fame_exp >= $target_player_fame_level_next_exp){
				while($target_player_fame_exp >= $target_player_fame_level_next_exp){
					$target_player_fame_level_new = $target_player_fame_level_next;
					$target_player_fame_level_next += 1;
					$target_player_fame_exp -= $target_player_fame_level_next_exp;
					$target_player_fame_level_next_exp = 100 * $target_player_fame_level_next * $target_player_fame_level_next * $target_player_fame_level_next;
				}
			}

			$target_player_level_new = $player["player_level"];
			$next_level = $target_player_level_new + 1;
			while(($next_level < $player_level_size) && ($player["player_networth"] >= $player_level_lowlimit[$next_level])){
				$target_player_level_new = $next_level;
				$next_level += 1;
			}
			if($target_player_level_new != $player["player_level"] || $target_player_fame_level_new != $player["player_fame_level"]){
				$player_level_up_query->execute(array($target_player_level_new, $target_player_fame_level_new, $target_player_fame_exp, $player["id"]));
			}
		}

		$sql = "UPDATE players_extended SET voted = 0 WHERE voted";
		$db->query($sql);
		$sql = "UPDATE players SET influence = influence + 10*player_fame_level*(1 + vip_level) WHERE last_active > DATE_ADD(NOW(), INTERVAL -7 DAY)";
		$db->query($sql);

echo microtime(1) - $timestart.': Reset allowance<br />';
		// Update all players' history
		$hist_date = date("Y-m-d",$timenow);
		$sql = "UPDATE firms_positions SET used_allowance = 0";
		$db->query($sql);

echo microtime(1) - $timestart.': Updating players history<br />';
		// Update all players' history
		$hist_date = date("Y-m-d",$timenow);
		$sql = "INSERT INTO history_players (pid, networth, cash, history_date) SELECT id, player_networth, player_cash, '$hist_date' FROM players";
		$db->query($sql);

echo microtime(1) - $timestart.': Done<br />';
		//Summarize event
		$timetaken = microtime(1) - $timestart;
		$sql = "UPDATE world_var SET value = '$timetaken' WHERE name = 'su_last_ran_dur'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$cron_report_msg = "Everything ran ok.";
		return true;
	}
	
	if(daily_update()){
		$subject = "Cron Job - EoS - Success";
	}else{
		$subject = "Cron Job - EoS - FAILED";
	}
	$timetaken = microtime(1) - $timestart;
	$cron_report_msg .= '<br />Time taken (s): '.$timetaken;

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
	echo $cron_report_msg;
?>
