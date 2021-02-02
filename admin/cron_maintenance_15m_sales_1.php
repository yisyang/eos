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

	function n_sold_k_eq($quality, $q_avg, $demand_met, $price){
		$nsk = max(0.3,(1 + 0.02 * ($quality - $q_avg))) / $price * (5/(1 + 10 * $demand_met));
		$nsk = $nsk * $nsk;
		return $nsk;
	}

	function sell_pid($fid, $fsid, $pid, $wh_id, $wh_n, $n_sold_raw, $pid_q, $price){
		global $db, $timeupdate_tick, $cron_report_msg_level_2;
		if($n_sold_raw >= 1 && $wh_id){
			// Remove stuff from warehouse
			$n_sold = floor($n_sold_raw);
			$wh_pidpartialsale = $n_sold_raw - $n_sold;
			$wh_n_leftover = $wh_n - $n_sold;
			if($wh_n_leftover){
				$sql = "UPDATE firm_wh SET pidn = '$wh_n_leftover', pidpartialsale = '$wh_pidpartialsale' WHERE id = $wh_id";
			}else{
				$sql = "UPDATE firm_wh SET pidn = 0, pidpartialsale = 0 WHERE id = $wh_id";
			}
			$result = $db->query($sql);
			if(!$result){
				$cron_report_msg_level_2 .= "<br />Warning, Failed Query: ".$sql;
			}
			// Give sales revenue to firm, log sales
			$revenue = $price * $n_sold;
			$sql = "UPDATE firms SET cash = cash + $revenue WHERE id = $fid";
			$db->query($sql);
			$sql = "INSERT INTO log_sales (fid, fsid, pid, pidn, pidq, value, tick) VALUES ($fid, $fsid, $pid, $n_sold, $pid_q, $revenue, $timeupdate_tick)";
			$db->query($sql);
			$sql = "INSERT INTO log_sales_tick (fid, pid, pidn, value) VALUES ($fid, $pid, $n_sold, $revenue) ON DUPLICATE KEY UPDATE pidn = pidn + $n_sold, value = value + $revenue";
			$db->query($sql);
		}else{
			$sql = "UPDATE firm_wh SET pidpartialsale = '$n_sold_raw' WHERE id = '$wh_id'";
			$db->query($sql);
		}
	}

	function update_fifteen_min(){
		global $db, $cron_report_msg, $timeupdate_dt, $timeupdate_tick, $timestart;
		$timenow = time();
		$timeupdate = $timenow;
		$timeupdate_dt = date("Y-m-d H:i:s", $timeupdate);
		$timeupdate_tick = floor(($timenow - 1327104000)/900);
		$time_diff = intval(date("i",$timenow));
echo microtime(1) - $timestart.': Starting<br />';

		$sql = "SELECT value FROM world_var WHERE name = 'su_last_ran_fifteen_sales_1000'";
		$last_ran_hourly = 0 + $db->query($sql)->fetchColumn();
		$last_ran_dur = $timenow - $last_ran_hourly;
		if($last_ran_dur < 500){
			$cron_report_msg = "Server update had previously been ran in less than 15 min: ".$last_ran_dur;
			return false;
		}
		// Update REAL store sale
		// Initialize product price multiplier
echo microtime(1) - $timestart.': Initializing products<br />';
		$sql = "SELECT list_prod.id, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.demand_met, list_prod.selltime, list_cat.price_multiplier FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id";
		$list_prod = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($list_prod as $list_prod_item){
			$pid = $list_prod_item["id"];
			$pid_value_base[$pid] = $list_prod_item["value"];
			$pid_value_avg[$pid] = $list_prod_item["value_avg"];
			$pid_q_avg[$pid] = $list_prod_item["q_avg"];
			$pid_demand_met[$pid] = $list_prod_item["demand_met"];
			$pid_price_multiplier[$pid] = $list_prod_item["price_multiplier"];
			$pid_selltime[$pid] = $list_prod_item["selltime"];
			$n_k_const_doubler[$pid] = (min(1,$pid_demand_met[$pid]) * 0.5 * min($pid_value_avg[$pid],50*$pid_value_base[$pid]) + (2 - min(1,max(0.15,$pid_demand_met[$pid]))) * $pid_value_base[$pid]) * $pid_price_multiplier[$pid];
			$pid_n_k_const[$pid] = $n_k_const_doubler[$pid] * $n_k_const_doubler[$pid];
		}
		// Find all stores ordered by store_id
echo microtime(1) - $timestart.': Finding all stores<br />';
		$sql = "SELECT firm_store.id AS fsid, firm_store.fid, firm_store.store_id, firm_store.size, firm_store.marketing, list_store.multiplier FROM firm_store LEFT JOIN firms ON firm_store.fid = firms.id LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firms.last_active > DATE_ADD(NOW(), INTERVAL -7 DAY) AND firms.vacation_out < NOW() AND !firm_store.is_expanding ORDER BY store_id ASC";
		$firm_stores = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo microtime(1) - $timestart.': Starting sales loop<br />';
		foreach($firm_stores as $firm_store){
			$current_fsid = $firm_store["fsid"];
			$current_fid = $firm_store["fid"];
			$current_store_size = $firm_store["size"];
echo microtime(1) - $timestart.': Get shelf and warehouse data '.$current_fsid.'<br />';
			$sql = "SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidprice, firm_wh.pidpartialsale FROM firm_store_shelves LEFT JOIN firm_wh ON firm_store_shelves.wh_id = firm_wh.id WHERE firm_store_shelves.fsid = $current_fsid AND !firm_wh.no_sell AND firm_wh.pidn > 0 ORDER BY firm_wh.pid ASC, firm_wh.pidn ASC";

			$firm_wh_sellable_result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$firm_wh_sellable_count = count($firm_wh_sellable_result);
			if($firm_wh_sellable_count){
				$current_store_size = (14.4/$firm_wh_sellable_count + 1.2) * $current_store_size * (1 + pow($firm_store["marketing"],0.25)/100);
			}
echo microtime(1) - $timestart.': Loop sellables '.$current_fsid.'<br />';
			for($j=0;$j<$firm_wh_sellable_count;$j++){
				$current_pid = $firm_wh_sellable_result[$j]["pid"];
				//Store info here until a different pid is encountered
				$current_wh_id = $firm_wh_sellable_result[$j]["id"];
				$firm_wh_pidq = $firm_wh_sellable_result[$j]["pidq"];
				$firm_wh_pidn = $firm_wh_sellable_result[$j]["pidn"];
				$firm_wh_pidpartialsale = $firm_wh_sellable_result[$j]["pidpartialsale"];
				if($firm_wh_sellable_result[$j]["pidprice"]){
					$firm_wh_pidprice = $firm_wh_sellable_result[$j]["pidprice"];
				}else{
					$firm_wh_pidprice = $pid_value_avg[$current_pid];
				}
				if($j + 1 < $firm_wh_sellable_count){
					$next_pid = $firm_wh_sellable_result[$j+1]["pid"];
					if($current_pid == $next_pid){
						$run_thingy = 0;
					}else{
						$run_thingy = 1;
					}
				}else{
					$run_thingy = 1;
				}
				if($run_thingy){
					// Simple sell
					$n_k_const = $pid_n_k_const[$current_pid] * $current_store_size / $pid_selltime[$current_pid];
					$pid_n_sold_k = n_sold_k_eq($firm_wh_pidq,$pid_q_avg[$current_pid],$pid_demand_met[$current_pid],$firm_wh_pidprice);
					$pid_n_sold = min($firm_wh_pidn, $pid_n_sold_k * $n_k_const + $firm_wh_pidpartialsale);
					sell_pid($current_fid,$current_fsid,$current_pid,$current_wh_id,$firm_wh_pidn,$pid_n_sold,$firm_wh_pidq,$firm_wh_pidprice);
				}
			}
		}
		
		// Send news
echo microtime(1) - $timestart.': Organizing sales on this tick<br />';
		$sql = "SELECT log_sales_tick.fid, list_prod.name AS prod_name, log_sales_tick.pidn, log_sales_tick.value FROM log_sales_tick LEFT JOIN list_prod ON log_sales_tick.pid = list_prod.id ORDER BY log_sales_tick.fid ASC, list_prod.name ASC";
		$sales_this_tick = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$query_news = $db->prepare("INSERT INTO firm_store_news (fid, body, date_created) VALUES (:fid, :body, '$timeupdate_dt')");
		$last_fid = 0;
echo microtime(1) - $timestart.': Sending news<br />';
		foreach($sales_this_tick as $sale_item){
			if($sale_item['fid'] != $last_fid){
				if($last_fid){
					$news = 'Your stores have sold '.$sold_items.'for a total of $'.number_format_readable($total_value/100);
					$query_news->execute(array(':fid' => $last_fid, ':body' => $news));
				}
				$last_fid = $sale_item['fid'];
				$sold_items = '';
				$total_value = 0;
			}
			$sold_items .= '$'.number_format_readable($sale_item['value']/100).' in <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($sale_item['prod_name'])).'.gif" alt="'.$sale_item['prod_name'].'" title="'.$sale_item['prod_name'].'" />, ';
			$total_value += $sale_item['value'];
		}
		$news = 'Your stores have sold '.$sold_items.'for a total of $'.number_format_readable($total_value/100);
		$query_news->execute(array(':fid' => $last_fid, ':body' => $news));

		// Cleanup
echo microtime(1) - $timestart.': Cleanup<br />';
		$sql = "CREATE TABLE log_sales_tick_new LIKE log_sales_tick";
		$db->query($sql);
		
		$sql = "DROP TABLE log_sales_tick";
		$db->query($sql);

		$sql = "RENAME TABLE log_sales_tick_new TO log_sales_tick";
		$db->query($sql);
		
		// Summarize event
echo microtime(1) - $timestart.': Done.<br />';
		$sql = "UPDATE world_var SET value = '$timenow' WHERE name = 'su_last_ran_fifteen_sales_1000'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$timetaken = microtime(1) - $timestart;
		$sql = "UPDATE world_var SET value = '$timetaken' WHERE name = 'su_last_ran_fifteen_sales_1000_dur'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$cron_report_msg = "OK";
		return true;
	}
	
	echo microtime(1) - $timestart,' - Initialized.<br />';
	
	if(update_fifteen_min()){
		echo microtime(1) - $timestart,' - Success.<br />';
	}
?>