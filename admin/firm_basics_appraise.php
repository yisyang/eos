<?php require 'include/prehtml.php'; ?>
<?php
		$firm_id = filter_var($_SESSION['editing_firm_id'], FILTER_SANITIZE_NUMBER_INT);
		if(!$firm_id){
			echo 'Firm ID is missing.';
			exit();
		}
		// Firm Networth = cash + $10*1.4^fame_level + 100% of land value + 100% of buildings value + about 50% of research value + 50% of warehouse quality adjusted value
		$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
		$firm_level_size = sizeof($firm_level_lowlimit);
		$sql = "SELECT id, cash, loan, level, fame_level, max_bldg FROM firms WHERE id = $firm_id";
		$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($firm)){
			echo 'Firm not found.';
			exit();
		}
		$target_firm_id = $firm["id"];
		$target_firm_level = $firm["level"];
		$networth = 0;
		$appraised_cash = $firm["cash"];
		$networth += $appraised_cash;
		$appraised_loan = $firm["loan"];
		$networth -= $appraised_loan;
		$appraised_fame = 1000*pow(1.4,$firm["fame_level"])-1000;
		$networth += $appraised_fame;
		//Land Value, surprisingly, the equation is the square of pascal's trangle...
		$max_bldg = $firm["max_bldg"];
		$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
		$networth += $appraised_land;
		
		//Building Value, using size * cost
		$appraised_building = 0;
		
		$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $target_firm_id";
		$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
		
		$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $target_firm_id";
		$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
		
		$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $target_firm_id";
		$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
		
		$networth += $appraised_building;
		
		//Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
		$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $target_firm_id";
		$appraised_research = $db->query($sql)->fetchColumn();
		$networth += $appraised_research;
		
		//Warehouse value, using value, pidq, pidn
		$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $target_firm_id";
		$appraised_wh = $db->query($sql)->fetchColumn();
		$networth += $appraised_wh;

		//Market value, using value, pidq, pidn
		$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $target_firm_id";
		$appraised_market = $db->query($sql)->fetchColumn();
		$networth += $appraised_market;
		
		$networth = floor($networth);
		$new_level = $target_firm_level;
		$next_level = $new_level + 1;
		while(($networth >= $firm_level_lowlimit[$next_level]) && ($next_level < $firm_level_size)){
			$new_level = $next_level;
			$next_level += 1;
		}
		$appraised_networth = $networth;
?>
		<h3>Appraisal Result</h3>
<?php
		echo '<i>Current Networth:</i><br /><br />';
		echo '<b>$'.number_format($appraised_networth/100,2,'.',',').'</b><br /><br /><br />';
		echo '<i>Details:</i><br /><br />';
		echo '<span style="display:inline-block;width:150px;">Cash: </span>$<span style="float:right;">'.number_format($appraised_cash/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Loan: </span>$<span style="float:right;">'.number_format($appraised_loan/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Fame: </span>$<span style="float:right;">'.number_format($appraised_fame/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Land Value: </span>$<span style="float:right;">'.number_format($appraised_land/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Building Value: </span>$<span style="float:right;">'.number_format($appraised_building/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Research Value: </span>$<span style="float:right;">'.number_format($appraised_research/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Warehouse Inven.: </span>$<span style="float:right;">'.number_format($appraised_wh/100,2,'.',',').'</span><br />';
		echo '<span style="display:inline-block;width:150px;">Market Inven.: </span>$<span style="float:right;">'.number_format($appraised_market/100,2,'.',',').'</span><br />';
?>