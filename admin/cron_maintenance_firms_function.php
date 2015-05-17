<?php
	function daily_update(){
		global $db, $cron_report_msg, $timestart, $settings_maintenance_multiplier, $settings_salary_multiplier, $settings_world_var, $settings_world_var_time, $settings_firms_lower_limit, $settings_firms_upper_limit, $settings_updater_title;
		$timenow = time();
		$timeupdate = mktime(23, 59, 59, date("n",$timenow), date("j",$timenow)-1, date("Y",$timenow));
		$timeupdate_dt = date("Y-m-d H:i:s",$timeupdate);
		$timeupdate_tick = floor(($timenow - 1327104000)/900);
		$time_diff = intval(date("Gi",$timenow));
		$date_ran = intval(date("Ymd",$timenow));

		// Comment to disable time restriction
		// if($time_diff < 0 || $time_diff > 10){
			// $cron_report_msg = "Job failed because it was ran at a non-scheduled time.";
			// return false;
		// }

		$sql = "SELECT value FROM world_var WHERE name = '$settings_world_var'";
		$last_ran = $db->query($sql)->fetchColumn();
		if($date_ran == $last_ran){
			$cron_report_msg = "Server update had previously been ran for the date: ".$last_ran;
			return false;
		}

		// Update all firms' history
		$sql = "SELECT a.id, a.name, a.level, a.cash, a.loan, a.networth, a.inventory, a.property, a.intangible, a.fame_level, a.last_active, a.vacation_out, a.is_public, a.ceo, a.dividend_flat, a.auto_repay_loan, a.total_pay_flat, a.total_bonus_percent, b.total_fact_size_12 + c.total_store_size_12 + d.total_rnd_size_12 AS total_building_size_12, b.total_fact_cost + c.total_store_cost + d.total_rnd_cost AS total_building_cost FROM (SELECT firms.id, firms.name, firms.level, firms.cash, firms.loan, firms.networth, firms.fame_level, firms.last_active, firms.vacation_out, firms_extended.inventory, firms_extended.property, firms_extended.intangible, firms_extended.is_public, firms_extended.ceo, firms_extended.dividend_flat, firms_extended.auto_repay_loan, IFNULL(SUM(firms_positions.pay_flat),0) AS total_pay_flat, IFNULL(SUM(firms_positions.bonus_percent),0) AS total_bonus_percent FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id LEFT JOIN firms_positions ON firms.id = firms_positions.fid GROUP BY firms.id) AS a LEFT JOIN (SELECT firms.id, IFNULL(SUM(POW(firm_fact.size,1.2)),0) AS total_fact_size_12, IFNULL(SUM(firm_fact.size * list_fact.cost),0) AS total_fact_cost FROM firms LEFT JOIN firm_fact ON firms.id = firm_fact.fid LEFT JOIN list_fact ON list_fact.id = firm_fact.fact_id GROUP BY firms.id) AS b ON a.id = b.id LEFT JOIN (SELECT firms.id, IFNULL(SUM(POW(firm_store.size,1.2)),0) AS total_store_size_12, IFNULL(SUM(firm_store.size * list_store.cost),0) AS total_store_cost FROM firms LEFT JOIN firm_store ON firms.id = firm_store.fid LEFT JOIN list_store ON list_store.id = firm_store.store_id GROUP BY firms.id) AS c ON a.id = c.id LEFT JOIN (SELECT firms.id, IFNULL(SUM(POW(firm_rnd.size,1.2)),0) AS total_rnd_size_12, IFNULL(SUM(firm_rnd.size * list_rnd.cost),0) AS total_rnd_cost FROM firms LEFT JOIN firm_rnd ON firms.id = firm_rnd.fid LEFT JOIN list_rnd ON list_rnd.id = firm_rnd.rnd_id GROUP BY firms.id) AS d ON a.id = d.id WHERE a.id > $settings_firms_lower_limit AND a.id < $settings_firms_upper_limit";
		$target_firms = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$hist_date = date("Y-m-d",$timenow);
		foreach($target_firms as $target_firm){
			$target_firm_id = $target_firm["id"];
			$target_firm_name = $target_firm["name"];
			$target_firm_level = $target_firm["level"];
			$target_firm_cash = $target_firm["cash"];
			$target_firm_loan = $target_firm["loan"];
			$target_firm_inventory = $target_firm["inventory"];
			$target_firm_property = $target_firm["property"];
			$target_firm_intangible = $target_firm["intangible"];
			$target_firm_maintenance = $target_firm["total_building_cost"] * $settings_maintenance_multiplier;
			$target_firm_salary = $target_firm["total_building_size_12"] * $settings_salary_multiplier;
			$target_firm_networth = $target_firm["networth"];
			$target_firm_fame_level = $target_firm["fame_level"];
			$target_firm_last_active = $target_firm["last_active"];
			$target_firm_vacation_out = strtotime($target_firm["vacation_out"]);
			$target_firm_is_public = $target_firm["is_public"];
			$target_firm_ceo = $target_firm["ceo"];
			$target_firm_dividend_flat = $target_firm["dividend_flat"];
			$target_firm_total_pay_flat = $target_firm["total_pay_flat"];
			$target_firm_total_bonus_percent = $target_firm["total_bonus_percent"];
			$target_firm_auto_repay_loan = $target_firm["auto_repay_loan"];

			$target_firm_is_inactive = 0;
			$target_firm_is_on_vacation = 0;
			if($timenow - strtotime($target_firm_last_active) > 640000){
				$target_firm_is_inactive = 1;
			}
			if($target_firm_vacation_out > $timenow){
				$target_firm_is_on_vacation = 1;
			}
			//Common Start: Loan
			$target_firm_interest_now = 0.01 * $target_firm_loan;
			//Calculate Dividend
			$target_firm_dividend = 0;
			$target_firm_paid_in_capital = 0;
			if($target_firm_is_public){
				$sql = "SELECT shares_os, paid_in_capital FROM firm_stock WHERE fid = $target_firm_id";
				$firm_stock = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
				$target_firm_shares_os = $firm_stock['shares_os'];
				$target_firm_paid_in_capital = $firm_stock['paid_in_capital'];
				if($target_firm_dividend_flat > 0){
					$target_firm_dividend = $target_firm_shares_os * $target_firm_dividend_flat;
					if($target_firm_cash < $target_firm_dividend && $target_firm_networth < (2 * ($target_firm_loan + $target_firm_dividend))){
						//No cash, set dividend to 0, notify ALL shareholders of dividend change
						$target_firm_dividend_flat = 0;
						$target_firm_dividend = 0;
						$sql = "UPDATE firms_extended SET dividend_flat = 0 WHERE id = $target_firm_id";
						$db->query($sql);
						$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, CONCAT('Dear Investor, Due to lack of cash and equity ', ?, ' is no longer able to sustain its dividend payment, and was forced to completely cancel its dividend.'), NOW() FROM player_stock WHERE fid = $target_firm_id");
						$query->execute(array($target_firm_name));
						if($target_firm_ceo){
							$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) VALUES ($target_firm_ceo, CONCAT('Dear CEO, Your company ', ?, ' is no longer able to sustain its dividend payment, and was forced to completely cancel its dividend.'), NOW())");
							$query->execute(array($target_firm_name));
						}
					}
				}
			}

			//Specific to Active/Inactive
			$target_firm_no_pay = 0;
			$target_firm_stock_issuance = 0;
			if($target_firm_is_on_vacation){
				$target_firm_maintenance = 0;
				$target_firm_salary = 0;
				$target_firm_bonus_base = 0;
				$target_firm_exec_pay = 0;
				$target_firm_no_pay = 1;
				$target_firm_interest_now = 0;
			}
			if($target_firm_is_inactive){
				$target_firm_production = 0;
				$target_firm_store_sales = 0;
				$target_firm_construction = 0;
				$target_firm_research = 0;
				$target_firm_b2b_sales = 0;
				$target_firm_b2b_purc = 0;
				$target_firm_import = 0;
				$target_firm_export = 0;
				$target_firm_total_gains = 0;
				$target_firm_salary = 0;
				$target_firm_total_spending = $target_firm_interest_now + $target_firm_maintenance;
				$target_firm_net_earnings = 0 - $target_firm_total_spending;
				$target_firm_bonus_base = 0;
				$target_firm_exec_pay = 0;
				$target_firm_tax = 0;
				$target_firm_no_pay = 1;
				$target_firm_interest = $target_firm_interest_now;
			}else{
				if($target_firm_is_public){
					$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Stock Issuance' AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
					$target_firm_stock_issuance = $db->query($sql)->fetchColumn();
				}
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Production' AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_production = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Production' AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_production_canceled = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_sales WHERE fid = $target_firm_id AND tick >= $timeupdate_tick - 96 AND tick < $timeupdate_tick";
				$target_firm_store_sales = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND (source = 'Construction' OR source = 'Expansion') AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_construction = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Research' AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_research = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Research' AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_research_canceled = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'B2B Sales' AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_b2b_sales = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'B2B Purchase' AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_b2b_purc = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Import' AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_import = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Export' AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_export = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Interest' AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_interest = $target_firm_interest_now + $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Transfer' AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_transfer_in = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND source = 'Transfer' AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_transfer_out = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND !is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_total_gains = $db->query($sql)->fetchColumn();
				$sql = "SELECT IFNULL(SUM(value),0) FROM log_revenue WHERE fid = $target_firm_id AND is_debit AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND transaction_time < CURRENT_DATE()";
				$target_firm_total_spending = $db->query($sql)->fetchColumn();
				
				$target_firm_production = $target_firm_production - $target_firm_production_canceled;
				$target_firm_research = $target_firm_research - $target_firm_research_canceled;
				$target_firm_total_gains = $target_firm_total_gains + $target_firm_store_sales - $target_firm_production_canceled - $target_firm_research_canceled - $target_firm_transfer_in - $target_firm_stock_issuance;
				$target_firm_total_spending = $target_firm_total_spending + $target_firm_salary + $target_firm_maintenance + $target_firm_interest_now - $target_firm_production_canceled - $target_firm_research_canceled - $target_firm_transfer_out;

				//Calculate net_earnings
				$target_firm_net_earnings = $target_firm_total_gains - $target_firm_total_spending;
				$target_firm_bonus_base = max(0, $target_firm_net_earnings/100);
				//Find $target_firm_exec_pay based on net earnings
				$target_firm_exec_pay = $target_firm_total_pay_flat + $target_firm_total_bonus_percent * $target_firm_bonus_base;
				if($target_firm_networth < (2 * ($target_firm_loan + $target_firm_exec_pay))){
					//No cash, set exec_pay to 0, bonus = 0, notify ALL employees of the change
					$target_firm_no_pay = 1;
					$target_firm_exec_pay = 0;
					$target_firm_bonus_base = 0;
				}
				//Re-calculate net_earnings
				$target_firm_net_earnings = $target_firm_net_earnings - $target_firm_exec_pay;
				//Calculate tax (15%)
				$target_firm_tax = max(0, floor($target_firm_net_earnings * (0.02 * $target_firm_level)));
			}

			//Add interest on loan, compare exec_pay + tax + dividend, compare to cash, take new loan if needed
			$total_costs = $target_firm_salary + $target_firm_maintenance + $target_firm_exec_pay + $target_firm_interest_now + $target_firm_tax + $target_firm_dividend;
			if($target_firm_cash < $total_costs){
				$total_costs = $total_costs - $target_firm_cash;
				$new_loan = 1.05 * $total_costs;
				$sql = "INSERT INTO firm_news (fid, body, date_created) VALUES ($target_firm_id, 'The company is short on cash and have taken a loan in the amount of $".number_format($new_loan/100,2,'.',',').". (Loan amount includes a 5% processing fee)', NOW())";
				$db->query($sql);
				$target_firm_cash_deduct = $target_firm_cash;
				$target_firm_loan_add = $new_loan;
			}else{
				$target_firm_cash_left = $target_firm_cash - $total_costs;
				$target_firm_cash_deduct = $total_costs;
				//If auto-repay and cash left, try paying off loan
				if($target_firm_auto_repay_loan && $target_firm_loan > 0){
					if($target_firm_cash_left > $target_firm_loan){
						$target_firm_cash_deduct += $target_firm_loan;
						$target_firm_loan_add = 0 - $target_firm_loan;
					}else{
						$target_firm_cash_deduct = $target_firm_cash;
						$target_firm_loan_add = $total_costs - $target_firm_cash;
					}
				}else{
					$target_firm_loan_add = 0;
				}
			}

			//Update company cash, loan
			$sql = "UPDATE firms SET cash = cash - $target_firm_cash_deduct, loan = loan + $target_firm_loan_add WHERE id = $target_firm_id";
			$db->query($sql);
			if($target_firm_is_inactive || $target_firm_no_pay){
				$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, CONCAT('You received nothing from ', ?, ' today, either because the company is without leadership, or because it simply does not have the money.'), NOW() FROM firms_positions WHERE fid = $target_firm_id");
				$query->execute(array($target_firm_name));
			}else{
				//Pay All Employees
				$sql = "SELECT pid, pay_flat, bonus_percent FROM firms_positions WHERE fid = $target_firm_id";
				$employees = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				
				$sql = "INSERT INTO player_news (pid, body, date_created) VALUES (?, CONCAT('You have received $', ?, ' in salary and $', ?, ' in bonus from ', ?, ' as payment for your services.'), NOW())";
				$pay_employee_action = $db->prepare($sql);
				$sql = "UPDATE players SET player_cash = player_cash + ? + ? WHERE id = ?";
				$pay_employee_news = $db->prepare($sql);
				foreach($employees as $employee){
					$employee_bonus = $employee["bonus_percent"] * $target_firm_bonus_base;
					$pay_employee_news->execute(array($employee["pay_flat"], $employee_bonus, $employee["pid"]));
					$pay_employee_action->execute(array($employee["pid"], number_format($employee["pay_flat"]/100,2,'.',','), number_format($employee_bonus/100,2,'.',','), $target_firm_name));
				}
			}
			//Pay Dividend, give message
			if($target_firm_is_public){
				if($target_firm_dividend_flat > 0){
					$sql = "UPDATE player_stock LEFT JOIN players ON player_stock.pid = players.id SET players.player_cash = players.player_cash + player_stock.shares * $target_firm_dividend_flat WHERE player_stock.fid = $target_firm_id";
					$db->query($sql);
					$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT player_stock.pid, CONCAT('You have received $', FORMAT(player_stock.shares * $target_firm_dividend_flat/100,2), ' as stock dividend from ', ?, '.'), NOW() FROM player_stock LEFT JOIN players ON player_stock.pid = players.id WHERE player_stock.fid = $target_firm_id");
					$query->execute(array($target_firm_name));
				}
			}
			$sql = "INSERT INTO history_firms (fid, networth, cash, loan, inventory, property, intangible, total_gains, total_spending, production, store_sales, construction, research, b2b_sales, b2b_purchase, import, export, maintenance, salary, paid_in_capital, tax, interest, dividend, exec_pay, history_date) VALUES ($target_firm_id, $target_firm_networth, $target_firm_cash, $target_firm_loan, $target_firm_inventory, $target_firm_property, $target_firm_intangible, $target_firm_total_gains, $target_firm_total_spending, $target_firm_production, $target_firm_store_sales, $target_firm_construction, $target_firm_research, $target_firm_b2b_sales, $target_firm_b2b_purc, $target_firm_import, $target_firm_export, $target_firm_maintenance, $target_firm_salary, $target_firm_paid_in_capital, $target_firm_tax, $target_firm_interest, $target_firm_dividend, $target_firm_exec_pay, '$hist_date')";
			$db->query($sql);
			if($target_firm_is_public){
				$sql = "INSERT INTO firm_news (fid, body, date_created) VALUES ($target_firm_id, 'Your company paid $".number_format($target_firm_tax/100,2,'.',',')." in taxes, $".number_format($target_firm_salary/100,2,'.',',')." in salaries, $".number_format($target_firm_maintenance/100,2,'.',',')." in building maintenance, $".number_format($target_firm_interest/100,2,'.',',')." in interests, $".number_format($target_firm_exec_pay/100,2,'.',',')." in executive compensation, and $".number_format($target_firm_dividend/100,2,'.',',')." in dividends.', NOW())";
			}else{
				$sql = "INSERT INTO firm_news (fid, body, date_created) VALUES ($target_firm_id, 'Your company paid $".number_format($target_firm_tax/100,2,'.',',')." in taxes, $".number_format($target_firm_salary/100,2,'.',',')." in salaries, $".number_format($target_firm_maintenance/100,2,'.',',')." in building maintenance, $".number_format($target_firm_interest/100,2,'.',',')." in interests, and $".number_format($target_firm_exec_pay/100,2,'.',',')." in executive compensation.', NOW())";
			}
			$db->query($sql);
		}
		
		//Summarize event
		$sql = "UPDATE world_var SET value = '$date_ran' WHERE name = '$settings_world_var'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$timetaken = microtime(1) - $timestart;
		$sql = "UPDATE world_var SET value = '$timetaken' WHERE name = '$settings_world_var_time'";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$cron_report_msg = "Everything ran ok.";
		return true;
	}
	
	if(daily_update()){
		$subject = "$settings_updater_title - Success";
		echo "<br /><br />Everything ran ok.";
	}else{
		$subject = "$settings_updater_title - FAILED";
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
	}
?>