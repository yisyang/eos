<?php require 'include/prehtml.php'; ?>
<?php
	$type = isset($_GET['type']) ? filter_var($_GET['type'], FILTER_SANITIZE_STRING) : '';
	$pageNum = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT) : 1;
	$pageNum = max($pageNum, 1);
	
	if($type == ''){
		echo 'Invalid request.';
		exit();
	}
	
	function create_news_nav($type, $pageNum, $pagesTotal){
		$nav = '';
		if ($pageNum > 1){
			$page = $pageNum - 1;
			$nav .= '<div class="table_nav"> <a onclick="getNews(\''.$type.'\','.$page.')">'._("Prev").'</a> ';
		}else{
			$nav  .= '<div class="table_nav"> <a class="disabled">'._("Prev").'</a> ';
		}
		for ($i = 1; $i <= $pagesTotal; $i++){
			if(($i - $pageNum) >= 10 || ($pageNum - $i) >= 5){
				if(($i - $pageNum) == 10 || ($pageNum - $i) == 5){
					$nav .= ' <a onclick="getNews(\''.$type.'\','.$i.')">...</a> ';
				}
			}else{
				if($i != $pageNum){
					$nav .= ' <a onclick="getNews(\''.$type.'\','.$i.')">'.$i.'</a> ';
				}else{
					$nav .= ' <a class="selected" onclick="getNews(\''.$type.'\','.$i.')">'.$i.'</a> ';
				}
			}
		}
		if ($pageNum < $pagesTotal){
			$page = $pageNum + 1;
			$nav .= ' <a onclick="getNews(\''.$type.'\','.$page.')">'._("Next").'</a> </div>';
		}else{
			$nav .= ' <a class="disabled">'._("Next").'</a> </div>';
		}
		return $nav;
	}
	
	if($type == 'firm'){
		if(!$eos_firm_id){
			echo 'Company not found.';
			exit();
		}

		$perPage = 20;
		$query = $db->prepare("SELECT COUNT(*) FROM firm_news WHERE fid = ?");
		$query->execute(array($eos_firm_id));
		$resultsTotal = $query->fetchColumn();
		$pagesTotal = floor($resultsTotal / $perPage);
		$nav = create_news_nav($type, $pageNum, $pagesTotal);
		$offset = ($pageNum - 1) * $perPage;
		$query = $db->prepare("SELECT body, date_created AS news_date FROM firm_news WHERE fid = ? ORDER BY date_created DESC LIMIT $offset, $perPage");
		$query->execute(array($eos_firm_id));
		$news_items = $query->fetchAll(PDO::FETCH_ASSOC);
		
		if($pagesTotal > 1){ echo $nav; }
?>
		<table class="default_table default_table_smallfont vert_middle">
			<thead>
				<tr><td>Date</td><td>News</td></tr>
			</thead>
			<tbody>
		<?php
			foreach($news_items as $news_item){
				echo '<tr><td>';
				echo '<span class="no_wrap">',nl2br(date("M j, Y", strtotime($news_item["news_date"]))),'</span>';
				echo '<br /><span class="no_wrap">',nl2br(date("g:i A", strtotime($news_item["news_date"]))),'</span>';
				echo '</td><td style="text-align: left !important;">';
				echo nl2br(stripcslashes($news_item["body"]));
				echo '</td></tr>';
			}
		?>
			</tbody>
		</table>
<?php
		if($pagesTotal > 1){ echo $nav; }
		exit();
	}
	if($type == 'firm_store'){
		if(!$eos_firm_id){
			echo 'Company not found.';
			exit();
		}

		$perPage = 20;
		$query = $db->prepare("SELECT COUNT(*) FROM firm_store_news WHERE fid = ?");
		$query->execute(array($eos_firm_id));
		$resultsTotal = $query->fetchColumn();
		$pagesTotal = floor($resultsTotal / $perPage);
		$nav = create_news_nav($type, $pageNum, $pagesTotal);
		$offset = ($pageNum - 1) * $perPage;
		$query = $db->prepare("SELECT body, date_created AS news_date FROM firm_store_news WHERE fid = ? ORDER BY date_created DESC LIMIT $offset, $perPage");
		$query->execute(array($eos_firm_id));
		$news_items = $query->fetchAll(PDO::FETCH_ASSOC);
		
		if($pagesTotal > 1){ echo $nav; }
?>
		<table class="default_table default_table_smallfont vert_middle">
			<thead>
				<tr><td>Date</td><td>News</td></tr>
			</thead>
			<tbody>
		<?php
			foreach($news_items as $news_item){
				echo '<tr><td>';
				echo '<span class="no_wrap">',nl2br(date("M j, Y", strtotime($news_item["news_date"]))),'</span>';
				echo '<br /><span class="no_wrap">',nl2br(date("g:i A", strtotime($news_item["news_date"]))),'</span>';
				echo '</td><td style="text-align: left !important;">';
				echo nl2br(stripcslashes($news_item["body"]));
				echo '</td></tr>';
			}
		?>
			</tbody>
		</table>
<?php
		if($pagesTotal > 1){ echo $nav; }
		exit();
	}
	if($type == 'overview'){
		if(!$eos_firm_id){
			echo 'Company not found.';
			exit();
		}
		function format_balance_sheet_number($money_in_cents){
			if($money_in_cents < 0){
				return '<span style="color:#ff0000;"><b>($'.number_format_readable(abs($money_in_cents)/100).')</b></span>';
			}else{
				return '<span><b>$'.number_format_readable($money_in_cents/100).'</b></span>';
			}
		}
		$query = $db->prepare("SELECT is_public FROM firms_extended WHERE id = ?");
		$query->execute(array($eos_firm_id));
		$is_public = $query->fetchColumn();
		$query = $db->prepare("SELECT networth, cash, loan, total_gains, total_spending, production, store_sales, construction, research, b2b_sales, b2b_purchase, import, export, maintenance, salary, interest, exec_pay, tax, dividend, paid_in_capital, inventory, property, intangible, history_date FROM history_firms WHERE fid = ? AND history_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY) ORDER BY history_date DESC");
		$query->execute(array($eos_firm_id));
		$results = $query->fetchAll(PDO::FETCH_ASSOC);
		$count = count($results);
		for($i=0;$i<$count;$i++){
			$networth[$i] = $results[$i]["networth"];
			$cash[$i] = $results[$i]["cash"];
			$loan[$i] = $results[$i]["loan"];
			$inventory[$i] = $results[$i]["inventory"];
			$property[$i] = $results[$i]["property"];
			$intangible[$i] = $results[$i]["intangible"];
			$total_gains[$i] = $results[$i]["total_gains"];
			$store_sales[$i] = $results[$i]["store_sales"];
			$b2b_sales[$i] = $results[$i]["b2b_sales"];
			$export[$i] = $results[$i]["export"];
			$gross_income[$i] = $store_sales[$i] + $b2b_sales[$i] + $export[$i];
			$misc_gains[$i] = $total_gains[$i] - $gross_income[$i];

			$total_spending[$i] = $results[$i]["total_spending"];
			$salary[$i] = $results[$i]["salary"];
			$maintenance[$i] = $results[$i]["maintenance"];
			$production[$i] = $results[$i]["production"];
			$construction[$i] = $results[$i]["construction"];
			$research[$i] = $results[$i]["research"];
			$b2b_purchase[$i] = $results[$i]["b2b_purchase"];
			$import[$i] = $results[$i]["import"];
			$interest[$i] = $results[$i]["interest"];
			$paid_in_capital[$i] = $results[$i]["paid_in_capital"];

			$exec_pay[$i] = $results[$i]["exec_pay"];
			$total_expenditures[$i] = $salary[$i] + $exec_pay[$i] + $maintenance[$i] + $production[$i] + $construction[$i] + $research[$i] + $b2b_purchase[$i] + $import[$i];
			$misc_spending[$i] = $total_spending[$i] - $total_expenditures[$i] + $exec_pay[$i] - $interest[$i];
			$operating_profit[$i] = $gross_income[$i] - $total_expenditures[$i];
			$misc[$i] = $misc_gains[$i] - $misc_spending[$i];
			
			$earnings_before_tax[$i] = $operating_profit[$i] + $misc[$i] - $interest[$i];
			
			$tax[$i] = $results[$i]["tax"];
			$dividend[$i] = $results[$i]["dividend"];
			$net_earnings[$i] = $earnings_before_tax[$i] - $tax[$i];
			$retained_earnings[$i] = $networth[$i] - $paid_in_capital[$i];

			$history_date[$i] = strtotime($results[$i]["history_date"]);
			$history_date_display[$i] = date("F j",($history_date[$i] - 82000)).'<br />to<br />'.date("F j",$history_date[$i]);
		}
		if($count > 0){
			//Income Statement
			echo '<h3>Income Statement</h3>';
			echo '<table class="default_table default_table_smallfont"><thead><tr><td>&nbsp;</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',$history_date_display[$i],'</td>';
			}
			echo '</tr></thead><tbody>';
			echo '<tr><td>Store Sales</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($store_sales[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>B2B Sales</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($b2b_sales[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Export</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($export[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td><b>Gross Income</b></td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($gross_income[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Salaries</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $salary[$i] - $exec_pay[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Maintenance</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $maintenance[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Production</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $production[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Construction</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $construction[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Research</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $research[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>B2B Purchase</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $b2b_purchase[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Import</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $import[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td><b>Total Expenditures</b></td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $total_expenditures[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td><b>Operating Profit</b></td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($operating_profit[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Misc.</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($misc[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Loan Interest</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $interest[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td><b>Earnings Before Tax</b></td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($earnings_before_tax[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Tax</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number(0 - $tax[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td><b>Net Earnings</b></td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($net_earnings[$i]),'</td>';
			}
			echo '</tr>';
			echo '</tbody></table><br />';
			
			//Begin Balance Sheet
			echo '<h3>Balance Sheet</h3>';
			echo '<table class="default_table default_table_smallfont"><thead><tr><td>&nbsp;</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',$history_date_display[$i],'</td>';
			}
			echo '</tr></thead><tbody>';
			echo '<tr><td colspan="',($count + 1),'"><b><i>Assets</i></b></td></tr>';
			echo '<tr><td>Cash</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($cash[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Inventory</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($inventory[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Property / Equipment</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($property[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td>Intangible Assets</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($intangible[$i]),'</td>';
			}
			echo '</tr>';
			echo '<tr><td colspan="',($count + 1),'"><b><i>Liabilities</i></b></td></tr>';
			echo '<tr><td>Loan</td>';
			for($i=0;$i<$count;$i++){
				echo '<td>',format_balance_sheet_number($loan[$i]),'</td>';
			}
			echo '</tr>';
			if($is_public){
				echo '<tr><td colspan="',($count + 1),'"><b><i>Shareholder Equity</i></b></td></tr>';
				echo '<tr><td>Paid in Capital</td>';
				for($i=0;$i<$count;$i++){
					echo '<td>',format_balance_sheet_number($paid_in_capital[$i]),'</td>';
				}
				echo '</tr>';
				
				echo '<tr><td>Dividend Payout</td>';
				for($i=0;$i<$count;$i++){
					echo '<td>',format_balance_sheet_number($dividend[$i]),'</td>';
				}
				echo '</tr>';
				echo '<tr><td>Retained Earnings</td>';
				for($i=0;$i<$count;$i++){
					echo '<td>',format_balance_sheet_number($retained_earnings[$i]),'</td>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
		}else{
			echo 'No Data.';
		}
		exit();
	}
	if($type == 'player'){
		$query = $db->prepare("UPDATE players_extended SET player_news_last_read = NOW() WHERE id = ?");
		$query->execute(array($eos_player_id));

		$perPage = 20;
		$query = $db->prepare("SELECT COUNT(*) FROM player_news WHERE pid = ?");
		$query->execute(array($eos_player_id));
		$resultsTotal = $query->fetchColumn();
		$pagesTotal = floor($resultsTotal / $perPage);
		$nav = create_news_nav($type, $pageNum, $pagesTotal);
		$offset = ($pageNum - 1) * $perPage;
		$query = $db->prepare("SELECT body, date_created AS news_date FROM player_news WHERE pid = ? ORDER BY date_created DESC LIMIT $offset, $perPage");
		$query->execute(array($eos_player_id));
		$news_items = $query->fetchAll(PDO::FETCH_ASSOC);
		
		if($pagesTotal > 1){ echo $nav; }
?>
		<table class="default_table default_table_smallfont vert_middle">
			<thead>
				<tr><td>Date</td><td>News</td></tr>
			</thead>
			<tbody>
		<?php
			foreach($news_items as $news_item){
				echo '<tr><td>';
				echo '<span class="no_wrap">',nl2br(date("M j, Y", strtotime($news_item["news_date"]))),'</span>';
				echo '<br /><span class="no_wrap">',nl2br(date("g:i A", strtotime($news_item["news_date"]))),'</span>';
				echo '</td><td style="text-align: left !important;">';
				echo nl2br(stripcslashes($news_item["body"]));
				echo '</td></tr>';
			}
		?>
			</tbody>
		</table>
<?php
		if($pagesTotal > 1){ echo $nav; }
		exit();
	}
	if($type == 'world'){
		$query = $db->prepare("UPDATE players_extended SET world_news_last_read = NOW() WHERE id = ?");
		$query->execute(array($eos_player_id));

		$perPage = 20;
		$query = $db->prepare("SELECT COUNT(*) FROM world_news WHERE !hidden");
		$query->execute(array($eos_player_id));
		$resultsTotal = $query->fetchColumn();
		$pagesTotal = floor($resultsTotal / $perPage);
		$nav = create_news_nav($type, $pageNum, $pagesTotal);
		$offset = ($pageNum - 1) * $perPage;
		$query = $db->prepare("SELECT title, body, DATE(date_created) AS news_date FROM world_news WHERE !hidden ORDER BY date_created DESC, id DESC LIMIT $offset, $perPage");
		$query->execute(array());
		$news_items = $query->fetchAll(PDO::FETCH_ASSOC);
		
		if($pagesTotal > 1){ echo $nav; }
?>
		<table class="default_table default_table_smallfont vert_middle">
			<thead>
				<tr><td>Date</td><td>News</td></tr>
			</thead>
			<tbody>
		<?php
			foreach($news_items as $news_item){
				echo '<tr><td>';
				echo '<span class="no_wrap">',nl2br(date("M j, Y", strtotime($news_item["news_date"]))),'</span>';
				echo '</td><td style="text-align: left !important;">';
				echo '<b>'.$news_item["title"].'</b><br />';
				echo nl2br(stripcslashes($news_item["body"]));
				echo '</td></tr>';
			}
		?>
			</tbody>
		</table>
<?php
		if($pagesTotal > 1){ echo $nav; }
		exit();
	}
	if($type == 'system'){
		$query = $db->prepare("UPDATE players_extended SET system_news_last_read = NOW() WHERE id = ?");
		$query->execute(array($eos_player_id));

		$perPage = 20;
		$query = $db->prepare("SELECT COUNT(*) FROM system_news WHERE !hidden");
		$query->execute(array());
		$resultsTotal = $query->fetchColumn();
		$pagesTotal = floor($resultsTotal / $perPage);
		$nav = create_news_nav($type, $pageNum, $pagesTotal);
		$offset = ($pageNum - 1) * $perPage;
		$query = $db->prepare("SELECT title, body, DATE(date_created) AS news_date FROM system_news WHERE !hidden ORDER BY date_created DESC, id DESC LIMIT $offset, $perPage");
		$query->execute(array());
		$news_items = $query->fetchAll(PDO::FETCH_ASSOC);
		
		if($pagesTotal > 1){ echo $nav; }
?>
		<table class="default_table default_table_smallfont vert_middle">
			<thead>
				<tr><td>Date</td><td>News</td></tr>
			</thead>
			<tbody>
		<?php
			foreach($news_items as $news_item){
				echo '<tr><td>';
				echo '<span class="no_wrap">',nl2br(date("M j, Y", strtotime($news_item["news_date"]))),'</span>';
				echo '</td><td style="text-align: left !important;">';
				echo '<b>'.$news_item["title"].'</b><br />';
				echo nl2br(stripcslashes($news_item["body"]));
				echo '</td></tr>';
			}
		?>
			</tbody>
		</table>
<?php
		if($pagesTotal > 1){ echo $nav; }
		exit();
	}
?>