<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'show_table'){
	$view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = 25;
	
	$offset = ($page_num - 1) * $per_page;

	switch($view_type){
		case 'new':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firm_stock");
			$query_results = $db->prepare("SELECT firm_stock.fid, firm_stock.symbol, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_open, (firm_stock.share_price-firm_stock.share_price_open)/firm_stock.share_price_open AS gain FROM firm_stock ORDER BY firm_stock.id DESC LIMIT $offset, $per_page");
			$query_params = array();
			break;
		case 'alpha':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firm_stock");
			$query_results = $db->prepare("SELECT firm_stock.fid, firm_stock.symbol, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_open, (firm_stock.share_price-firm_stock.share_price_open)/firm_stock.share_price_open AS gain FROM firm_stock ORDER BY firm_stock.symbol ASC LIMIT $offset, $per_page");
			$query_params = array();
			break;
		case 'watchlist':
			$query_count = $db->prepare("SELECT COUNT(*) FROM stock_watchlist LEFT JOIN firm_stock ON stock_watchlist.fid = firm_stock.fid WHERE stock_watchlist.pid = :player_id AND firm_stock.fid IS NOT NULL");
			$query_results = $db->prepare("SELECT firm_stock.fid, firm_stock.symbol, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_open, (firm_stock.share_price-firm_stock.share_price_open)/firm_stock.share_price_open AS gain FROM stock_watchlist LEFT JOIN firm_stock ON stock_watchlist.fid = firm_stock.fid WHERE stock_watchlist.pid = :player_id AND firm_stock.fid IS NOT NULL ORDER BY firm_stock.symbol ASC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id);
			break;
		case 'search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_stock WHERE firm_stock.symbol LIKE :search_term");
			$query_results = $db->prepare("SELECT firm_stock.fid, firm_stock.symbol, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_open, (firm_stock.share_price-firm_stock.share_price_open)/firm_stock.share_price_open AS gain FROM firm_stock WHERE firm_stock.symbol LIKE :search_term ORDER BY firm_stock.symbol ASC LIMIT $offset, $per_page");
			$query_params = array(':search_term' => '%'.$search_term.'%');
			break;
		case 'po':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_stock_issuance");
			$query_results = $db->prepare("SELECT firm_stock_issuance.id, firm_stock.fid, firm_stock.symbol, firm_stock_issuance.shares, firm_stock_issuance.price, firm_stock_issuance.type, IF(firm_stock_issuance.starts > NOW(), 0, 1) AS started, firm_stock_issuance.starts, firm_stock_issuance.expiration FROM firm_stock_issuance LEFT JOIN firm_stock ON firm_stock_issuance.fid = firm_stock.fid ORDER BY firm_stock_issuance.id DESC LIMIT $offset, $per_page");
			$query_params = array();
			break;
		case 'po_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_stock_issuance LEFT JOIN firm_stock ON firm_stock_issuance.fid = firm_stock.fid WHERE firm_stock.symbol LIKE :search_term");
			$query_results = $db->prepare("SELECT firm_stock_issuance.id, firm_stock.fid, firm_stock.symbol, firm_stock_issuance.shares, firm_stock_issuance.price, firm_stock_issuance.type, IF(firm_stock_issuance.starts > NOW(), 0, 1) AS started, firm_stock_issuance.starts, firm_stock_issuance.expiration FROM firm_stock_issuance LEFT JOIN firm_stock ON firm_stock_issuance.fid = firm_stock.fid WHERE firm_stock.symbol LIKE :search_term ORDER BY firm_stock_issuance.id DESC LIMIT $offset, $per_page");
			$query_params = array(':search_term' => '%'.$search_term.'%');
			break;
		case 'bid':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM stock_bid WHERE pid = :player_id");
			$query_results = $db->prepare("SELECT stock_bid.id, stock_bid.fid, stock_bid.pid, stock_bid.shares, stock_bid.price, stock_bid.aon, stock_bid.expiration, firm_stock.symbol FROM stock_bid LEFT JOIN firm_stock ON stock_bid.fid = firm_stock.fid WHERE stock_bid.pid = :player_id ORDER BY stock_bid.id DESC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id);
			break;
		case 'bid_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM stock_bid LEFT JOIN firm_stock ON stock_bid.fid = firm_stock.fid WHERE stock_bid.pid = :player_id AND firm_stock.symbol LIKE :search_term");
			$query_results = $db->prepare("SELECT stock_bid.id, stock_bid.fid, stock_bid.pid, stock_bid.shares, stock_bid.price, stock_bid.aon, stock_bid.expiration, firm_stock.symbol FROM stock_bid LEFT JOIN firm_stock ON stock_bid.fid = firm_stock.fid WHERE stock_bid.pid = :player_id AND firm_stock.symbol LIKE :search_term ORDER BY stock_bid.id DESC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id, ':search_term' => '%'.$search_term.'%');
			break;
		case 'ask':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM stock_ask WHERE pid = :player_id");
			$query_results = $db->prepare("SELECT stock_ask.id, stock_ask.fid, stock_ask.pid, stock_ask.shares, stock_ask.price, stock_ask.expiration, firm_stock.symbol FROM stock_ask LEFT JOIN firm_stock ON stock_ask.fid = firm_stock.fid WHERE stock_ask.pid = :player_id ORDER BY stock_ask.id DESC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id);
			break;
		case 'ask_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM stock_ask LEFT JOIN firm_stock ON stock_ask.fid = firm_stock.fid WHERE stock_ask.pid = :player_id AND firm_stock.symbol LIKE :search_term");
			$query_results = $db->prepare("SELECT stock_ask.id, stock_ask.fid, stock_ask.pid, stock_ask.shares, stock_ask.price, stock_ask.expiration, firm_stock.symbol FROM stock_ask LEFT JOIN firm_stock ON stock_ask.fid = firm_stock.fid WHERE stock_ask.pid = :player_id AND firm_stock.symbol LIKE :search_term ORDER BY stock_ask.id DESC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id, ':search_term' => '%'.$search_term.'%');
			break;
		case 'history':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM log_stock WHERE bpid = :player_id OR spid = :player_id");
			$query_results = $db->prepare("SELECT log_stock.id, log_stock.fid, log_stock.bpid, log_stock.spid, log_stock.shares, log_stock.share_price, log_stock.total_price, log_stock.transaction_time, firm_stock.symbol FROM log_stock LEFT JOIN firm_stock ON log_stock.fid = firm_stock.fid WHERE log_stock.bpid = :player_id OR log_stock.spid = :player_id ORDER BY log_stock.transaction_time DESC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id);
			break;
		case 'history_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM log_stock LEFT JOIN firm_stock ON log_stock.fid = firm_stock.fid WHERE (log_stock.bpid = :player_id OR log_stock.spid = :player_id) AND firm_stock.symbol LIKE :search_term");
			$query_results = $db->prepare("SELECT log_stock.id, log_stock.fid, log_stock.bpid, log_stock.spid, log_stock.shares, log_stock.share_price, log_stock.total_price, log_stock.transaction_time, firm_stock.symbol FROM log_stock LEFT JOIN firm_stock ON log_stock.fid = firm_stock.fid WHERE (log_stock.bpid = :player_id OR log_stock.spid = :player_id) AND firm_stock.symbol LIKE :search_term ORDER BY log_stock.transaction_time DESC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id, ':search_term' => '%'.$search_term.'%');
			break;
		case 'portfolio':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM player_stock WHERE pid = :player_id");
			$query_results = $db->prepare("SELECT player_stock.id, player_stock.fid, player_stock.pid, player_stock.shares, firm_stock.symbol, firm_stock.share_price FROM player_stock LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid WHERE player_stock.pid = :player_id ORDER BY firm_stock.symbol ASC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id);
			break;
		case 'portfolio_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM player_stock LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid WHERE pid = :player_id AND firm_stock.symbol LIKE :search_term");
			$query_results = $db->prepare("SELECT player_stock.id, player_stock.fid, player_stock.pid, player_stock.shares, firm_stock.symbol, firm_stock.share_price FROM player_stock LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid WHERE player_stock.pid = :player_id AND firm_stock.symbol LIKE :search_term ORDER BY firm_stock.symbol ASC LIMIT $offset, $per_page");
			$query_params = array(':player_id' => $eos_player_id, ':search_term' => '%'.$search_term.'%');
			break;
		case 'find_symbol':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_stock LEFT JOIN firms ON firms.id = firm_stock.fid WHERE firm_stock.symbol LIKE :search_term OR firms.name LIKE :search_term");
			$query_results = $db->prepare("SELECT firm_stock.id, firm_stock.fid, firm_stock.symbol, firms.name AS firm_name FROM firm_stock LEFT JOIN firms ON firms.id = firm_stock.fid WHERE firm_stock.symbol LIKE :search_term OR firms.name LIKE :search_term ORDER BY firm_name ASC LIMIT 0, 100");
			$query_params = array(':player_id' => $eos_player_id, ':search_term' => $search_term.'%');
			break;
		default:
			$resp = array('success' => 0, 'msg' => 'Unknown view type.');
			echo json_encode($resp);
			exit();
			break;
	}

	$query_count->execute($query_params);
	$total_items = intval($query_count->fetchColumn());
	$pages_total = ceil($total_items/$per_page);

	$query_results->execute($query_params);
	$stock_results = $query_results->fetchAll(PDO::FETCH_ASSOC);

	$resp = array('success' => 1, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => $total_items, 'results' => $stock_results);
	echo json_encode($resp);
	exit();
}
else if($action == 'refresh_row'){
	$stock_fid = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);

	if($stock_fid){
		$sql = "SELECT firm_stock.fid, firm_stock.symbol, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_open, (firm_stock.share_price-firm_stock.share_price_open)/firm_stock.share_price_open AS gain FROM firm_stock WHERE firm_stock.fid = '$stock_fid' LIMIT 0,1";
		
		$stock_row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		
		if(empty($stock_row)){			
			$resp = array('success' => 1, 'notFound' => 1);
			echo json_encode($resp);
			exit();
		}
		
		$resp = array('success' => 1, 'notFound' => 0, 'resultRow' => $stock_row);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'refresh_row_po'){
	$po_id = filter_var($_POST['po_id'], FILTER_SANITIZE_NUMBER_INT);

	if($po_id){
		$sql = "SELECT firm_stock.fid, firm_stock.symbol, firm_stock_issuance.shares, firm_stock_issuance.price, firm_stock_issuance.type, IF(firm_stock_issuance.starts > NOW(), 0, 1) AS started, firm_stock_issuance.starts, firm_stock_issuance.expiration FROM firm_stock_issuance LEFT JOIN firm_stock ON firm_stock_issuance.fid = firm_stock.fid WHERE firm_stock_issuance.id = '$po_id'";
		
		$stock_row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		
		if(empty($stock_row)){			
			$resp = array('success' => 1, 'notFound' => 1);
			echo json_encode($resp);
			exit();
		}
		
		$resp = array('success' => 1, 'notFound' => 0, 'resultRow' => $stock_row);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'show_sd_fundamentals'){
	$target_firm_id = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$target_firm_id){
		echo 'Company not found.';
		exit();
	}

	// Fetch fid and stock info from firm_stock
	$query = $db->prepare("SELECT firm_stock.id, firm_stock.fid, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_min, firm_stock.share_price_max, firm_stock.dividend, firm_stock.7de, firm_stock.last_active FROM firm_stock WHERE firm_stock.fid = :fid");
	$query->execute(array(':fid' => $target_firm_id));
	$stock_details = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($stock_details)){
		echo 'Not found.';
		exit();
	}

	$stock_firm_id = $stock_details['fid'];
	$stock_market_cap = $stock_details['shares_os'] * $stock_details['share_price'];
	$stock_eps = $stock_details['7de'] / $stock_details['shares_os'];
	if($stock_eps > 0.5 || $stock_eps < -0.5){
		$stock_pe = $stock_details['share_price']/$stock_eps;
		$stock_pe_display = number_format_readable($stock_pe);
	}else{
		$stock_pe = 9999999999;
		$stock_pe_display = 'N/A';
	}
	
	// Get firm info
	$sql = "SELECT firms.*, firms_extended.dividend_flat FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms.id = '$stock_firm_id'";
	$firm_info = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm_info)){
		echo 'Not found.';
		exit();
	}

	// Fetch Bid and Ask prices
	$sql = "SELECT price, IFNULL(SUM(shares),0)/100 AS vol FROM stock_bid WHERE fid = '$stock_firm_id' GROUP BY price DESC LIMIT 0, 1";
	$bid = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$sql = "SELECT price, IFNULL(SUM(shares),0)/100 AS vol FROM stock_ask WHERE fid = '$stock_firm_id' GROUP BY price DESC LIMIT 0, 1";
	$ask = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

	// Fetch volume
	$sql = "SELECT SUM(shares) FROM log_stock WHERE fid = '$stock_firm_id' AND transaction_time >= CURRENT_DATE()";
	$stock_volume = 0 + $db->query($sql)->fetchColumn();

	// 30 Day Range
	$sql = "SELECT MIN(share_price) AS sp_min, MAX(share_price) AS sp_max FROM log_stock WHERE fid = '$stock_firm_id' AND transaction_time >= DATE_ADD(CURRENT_DATE(), INTERVAL -30 DAY)";
	$stock_range = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($stock_range['sp_min'] !== NULL){
		$stock_range['sp_min'] = min($stock_range['sp_min'], $stock_details['share_price_min']);
	}else{
		$stock_range['sp_min'] = $stock_details['share_price_min'];
	}
	if($stock_range['sp_max'] !== NULL){
		$stock_range['sp_max'] = max($stock_range['sp_max'], $stock_details['share_price_max']);
	}else{
		$stock_range['sp_max'] = $stock_details['share_price_max'];
	}
	?>
		<div class="stock_fundamentals_holder" style="float:left;">
			<div class="label">Bid</div>
			<?= '$'.number_format($bid['price']/100,2,'.',',').' x '.floor($bid['vol']) ?><br />
			<div class="label">Ask</div>
			<?= '$'.number_format($ask['price']/100,2,'.',',').' x '.floor($ask['vol']) ?><br />
			<div class="label">Range</div>
			<?= '$'.number_format_readable($stock_details['share_price_min']/100, 4).' - $'.number_format_readable($stock_details['share_price_max']/100, 4) ?><br />
			<div class="label">30-Day Range</div>
			<?= '$'.number_format_readable($stock_range['sp_min']/100, 4).' - $'.number_format_readable($stock_range['sp_max']/100, 4) ?><br />
			<div class="label">Volume</div>
			<?= number_format_readable($stock_volume, 4) ?><br />
		</div>
		<div class="stock_fundamentals_holder" style="float:right;">
			<div class="label">Total Shares</div>
			<?= number_format_readable($stock_details['shares_os']) ?><br />
			<div class="label">Market Cap</div>
			$<?= number_format_readable($stock_market_cap/100) ?><br />
			<div class="label">P/E</div>
			<?= $stock_pe_display ?><br />
			<div class="label">7-Day EPS</div>
			$<?= number_format($stock_eps/100,2,'.',',') ?><br />
			<div class="label">Dividend<a class="info">*<span>per server day</span></a></div>
			$<?= number_format($firm_info['dividend_flat']/100,2,'.',',') ?> (<?= number_format(100*$firm_info['dividend_flat']/$stock_details['share_price'],2,'.',',') ?>%)<br />
		</div>
	<?php
	exit();
}
else if($action == 'show_sd_revenue'){
	$target_firm_id = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$target_firm_id){
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
	$query->execute(array($target_firm_id));
	$is_public = $query->fetchColumn();
	if(!$is_public){
		echo 'No Data.';
		exit();
	}

	$query = $db->prepare("SELECT networth, cash, loan, total_gains, total_spending, production, store_sales, construction, research, b2b_sales, b2b_purchase, import, export, maintenance, salary, interest, exec_pay, tax, dividend, paid_in_capital, inventory, property, intangible, history_date FROM history_firms WHERE fid = ? AND history_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY) ORDER BY history_date DESC");
	$query->execute(array($target_firm_id));
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
else if($action == 'show_sd_recent_b2b'){
	$target_firm_id = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$target_firm_id){
		echo 'Company not found.';
		exit();
	}
	$query = $db->prepare("SELECT is_public FROM firms_extended WHERE id = ?");
	$query->execute(array($target_firm_id));
	$is_public = $query->fetchColumn();
	if(!$is_public){
		echo 'No Data.';
		exit();
	}
	$sql = "SELECT log_market_prod.id, log_market_prod.sfid, log_market_prod.bfid, log_market_prod.pid, log_market_prod.pidq, log_market_prod.pidn, log_market_prod.price, log_market_prod.hide, log_market_prod.transaction_time, list_prod.name, list_prod.has_icon, sf.name AS sf_name, bf.name AS bf_name FROM log_market_prod LEFT JOIN firms AS sf ON log_market_prod.sfid = sf.id LEFT JOIN firms AS bf ON log_market_prod.bfid = bf.id LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE (log_market_prod.bfid = $target_firm_id OR log_market_prod.sfid = $target_firm_id) AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) ORDER BY log_market_prod.hide ASC, log_market_prod.transaction_time DESC LIMIT 0, 30";
	$b2b_transactions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	$table = '<table class="default_table default_table_smallfont">
	<thead>
		<tr>
			<td colspan="2">Product</td><td>Quality</td><td>Purchased Quantity</td><td>Unit Price</td><td>Total Price</td><td>Purchase Time</td>
		</tr>
	</thead>
	<tbody>';
	if(count($b2b_transactions)){
		foreach($b2b_transactions as $b2b_item){
			$table .= '<tr id="b2b_display_' . $b2b_item['id'] . '" class="b2b_tr' . ($b2b_item['hide'] ? '' : ' flagged') . '" b2b_id="' . $b2b_item['id'] . '">';
			$table .= '<td style="border-right: none;"><a class="jqDialog" href="/eos/pedia-product-view.php?pid=' . $b2b_item['pid'] . '" title="View ' . $b2b_item['name'] . ' on EOS-Pedia"><img src="/eos/images/prod/' . ($b2b_item['has_icon'] ? preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($b2b_item['name'])) : 'no-icon') . '.gif" /></a></td><td class="market_prod_name">' . $b2b_item['name'] . '<br /><span class="market_firm_name">by <a href="/eos/firm/' . $b2b_item['sfid'] . '" target="_blank">' . $b2b_item['sf_name'] . '</a></span></td><td>' . number_format($b2b_item['pidq'], 2, '.', ',') . '</td><td>' . number_format($b2b_item['pidn'], 0) . '</td><td>$' . number_format_readable($b2b_item['price']/100) . '</td><td>$' . number_format_readable($b2b_item['price'] * $b2b_item['pidn'] / 100) . '</td>';

			$table .= '<td>' . date('M j, h:i A', strtotime($b2b_item['transaction_time'])) . '<br /><span class="market_firm_name">by <a href="/eos/firm/' . $b2b_item['bfid'] . '" target="_blank">' . $b2b_item['bf_name'] . '</a></span></td></tr>';
		}
	}else{
		$table .= '<tr><td colspan="8">No history found.</td></tr>';
	}
	$table .= '</tbody>';
	echo $table;
	exit();
}
else if($action == 'show_sd_buildings'){
	$target_firm_id = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$target_firm_id){
		echo 'Company not found.';
		exit();
	}
	// Populate Top Factories
	$sql = "SELECT fact_id, size, name, has_image FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE fid = $target_firm_id ORDER BY firm_fact.size DESC LIMIT 0, 3";
	$bldgs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$top_fact_display = "";
	if(empty($bldgs)){
		$top_fact_display = "None<br />";
	}else{
		foreach($bldgs as $bldg){
			$top_fact_display .= '<div style="float:left;width:190px;text-align:center;">';
			$top_fact_display .= '<a class="jqDialog" href="/eos/pedia-building-view.php?type=fact&id='.$bldg['fact_id'].'"><img src="/eos/images/fact/'.($bldg['has_image'] ? preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg['name'])) : 'no-image').'.gif" alt="'.$bldg['name'].'" title="'.$bldg['name'].'" /></a>';
			$top_fact_display .= '<div style="text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">'.$bldg['size'].' m&#178;</div>';
			$top_fact_display .= '</div>';
		}
		$top_fact_display .= '<div class="clearer no_select">&nbsp;</div>';
	}
	// Populate Top Stores
	$sql = "SELECT store_id, size, name, has_image FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE fid = $target_firm_id ORDER BY firm_store.size DESC LIMIT 0, 3";
	$bldgs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$top_store_display = "";
	if(empty($bldgs)){
		$top_store_display = "None<br />";
	}else{
		foreach($bldgs as $bldg){
			$top_store_display .= '<div style="float:left;width:190px;text-align:center;">';
			$top_store_display .= '<a class="jqDialog" href="/eos/pedia-building-view.php?type=store&id='.$bldg['store_id'].'"><img src="/eos/images/store/'.($bldg['has_image'] ? preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg['name'])) : 'no-image').'.gif" alt="'.$bldg['name'].'" title="'.$bldg['name'].'" /></a>';
			$top_store_display .= '<div style="text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">'.$bldg['size'].' m&#178;</div>';
			$top_store_display .= '</div>';
		}
		$top_store_display .= '<div class="clearer no_select">&nbsp;</div>';
	}
	// Populate Top RnDs
	$sql = "SELECT rnd_id, size, name, has_image FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE fid = $target_firm_id ORDER BY firm_rnd.size DESC LIMIT 0, 3";
	$bldgs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$top_rnd_display = "";
	if(empty($bldgs)){
		$top_rnd_display = "None<br />";
	}else{
		foreach($bldgs as $bldg){
			$top_rnd_display .= '<div style="float:left;width:190px;text-align:center;">';
			$top_rnd_display .= '<a class="jqDialog" href="/eos/pedia-building-view.php?type=rnd&id='.$bldg['rnd_id'].'"><img src="/eos/images/rnd/'.($bldg['has_image'] ? preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg['name'])) : 'no-image').'.gif" alt="'.$bldg['name'].'" title="'.$bldg['name'].'" /></a>';
			$top_rnd_display .= '<div style="text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">'.$bldg['size'].' m&#178;</div>';
			$top_rnd_display .= '</div>';
		}
		$top_rnd_display .= '<div class="clearer no_select">&nbsp;</div>';
	}
	
	echo '
		<h3>Largest Factories</h3>
		'.$top_fact_display.'<br />
		<h3>Largest Stores</h3>
		'.$top_store_display.'<br />
		<h3>Largest R&amp;D</h3>
		'.$top_rnd_display.'<br />';
	exit();
}
else if($action == 'show_sd_researches'){
	$target_firm_id = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$target_firm_id){
		echo 'Company not found.';
		exit();
	}

	// Populate Top Researches
	$sql = "SELECT pid, quality, name, has_icon FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE fid = $target_firm_id ORDER BY firm_tech.quality DESC LIMIT 0, 12";
	$reses = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$top_res_display = "";
	if(empty($reses)){
		$top_res_display = "None<br />";
	}else{
		foreach($reses as $res){
			$top_res_display .= '<div style="float:left;width:105px;text-align:center;">';
			$top_res_display .= '<a class="jqDialog" href="pedia-product-view.php?pid='.$res['pid'].'"><img src="/eos/images/prod/large/'.($res['has_icon'] ? preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($res['name'])) : 'no-image').'.gif" alt="'.$res['name'].'" title="'.$res['name'].'" /></a>';
			$top_res_display .= '<div style="text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">Q'.$res['quality'].'</div>';
			$top_res_display .= '</div>';
		}
		$top_res_display .= '<div class="clearer no_select">&nbsp;</div>';
	}

	echo '
		<h3>Highest Researches</h3>
		'.$top_res_display.'<br />';
	exit();
}
else if($action == 'show_sd_shareholders'){
	$target_firm_id = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$target_firm_id){
		echo 'Company not found.';
		exit();
	}
	
	// Get outstanding shares
	$query = $db->prepare("SELECT firm_stock.shares_os FROM firm_stock WHERE firm_stock.fid = :fid");
	$query->execute(array(':fid' => $target_firm_id));
	$shares_os = $query->fetchColumn();
	
	// Populate Major Shareholders
	$sql = "SELECT player_stock.pid AS player_id, player_stock.shares, player_name FROM player_stock LEFT JOIN players ON player_stock.pid = players.id WHERE player_stock.fid = $target_firm_id ORDER BY player_stock.shares DESC LIMIT 0, 10";
	$shareholders = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$shareholders_display = '<table class="default_table"><thead><tr><td>Name</td><td>Shares</td><td>Percentage Owned</td></thead><tbody>';
	$shareholders_display_contents = "";
	foreach($shareholders as $shareholder){
		$msh_shares_percent = number_format(100 * $shareholder["shares"] / $shares_os, 2, '.', '');
		if($msh_shares_percent >= 1){
			$shareholders_display_contents .= '<tr><td><a href="/eos/player/'.$shareholder["player_id"].'">'.$shareholder["player_name"].'</a></td><td>'.number_format_readable($shareholder["shares"]).'</td><td>'.$msh_shares_percent.'%</td></tr>';
		}
	}
	if($shareholders_display_contents){
		$shareholders_display .= $shareholders_display_contents;
	}else{
		$shareholders_display .= '<tr><td colspan="3">No major shareholders.</td></tr>';
	}
	$shareholders_display .= '</tbody></table>';

	echo '
		<h3>Major Shareholders</h3>
		'.$shareholders_display.'<br />';
	exit();
}
else if($action == 'add_to_watchlist'){
	$symbol = filter_var($_POST['symbol'], FILTER_SANITIZE_STRING);
	$query = $db->prepare("SELECT fid FROM firm_stock WHERE firm_stock.symbol = :symbol");
	$query->execute(array(':symbol' => $symbol));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($result)){
		$resp = array('success' => 0, 'msg' => 'Symbol not found.');
		echo json_encode($resp);
		exit();
	}
	$query = $db->prepare("INSERT IGNORE INTO stock_watchlist (fid, pid) VALUES (:fid, :pid)");
	$query->execute(array(':fid' => $result['fid'], ':pid' => $eos_player_id));

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'remove_from_watchlist'){
	$stock_fid = filter_var($_POST['stock_fid'], FILTER_SANITIZE_NUMBER_INT);
	
	$query = $db->prepare("DELETE FROM stock_watchlist WHERE fid = :fid AND pid = :pid");
	$query->execute(array(':fid' => $stock_fid, ':pid' => $eos_player_id));

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'validate_symbol'){
	$symbol = strtoupper(filter_var($_POST['symbol'], FILTER_SANITIZE_STRING));
	
	$query = $db->prepare("SELECT fid FROM firm_stock WHERE firm_stock.symbol = :symbol");
	$query->execute(array(':symbol' => $symbol));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($result)){
		$resp = array('success' => 1, 'found' => 0, 'msg' => 'Symbol not found.');
		echo json_encode($resp);
		exit();
	}

	$fid = $result['fid'];
	$query = $db->prepare("SELECT id, fid, shares, price, type, IF(starts > NOW(), 0, 1) AS started, expiration FROM firm_stock_issuance WHERE fid = :fid ORDER BY id DESC LIMIT 0, 1");
	$query->execute(array(':fid' => $fid));
	$stock_issuance = $query->fetch(PDO::FETCH_ASSOC);

	if(empty($stock_issuance)){
		$resp = array('success' => 1, 'found' => 1, 'po' => 0);
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 1, 'found' => 1, 'po' => 1, 'podata' => $stock_issuance);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'add_order'){
	$is_edit = filter_var($_POST['is_edit'], FILTER_SANITIZE_NUMBER_INT);
	$order_type = filter_var($_POST['order_type'], FILTER_SANITIZE_STRING);
	$ss = strtoupper(filter_var($_POST['ss'], FILTER_SANITIZE_STRING));
	$shares = 0 + filter_var($_POST['shares'], FILTER_SANITIZE_NUMBER_INT);
	$aon = 0 + filter_var($_POST['aon'], FILTER_SANITIZE_NUMBER_INT);
	$price = 0 + filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);
	$mp = 0 + filter_var($_POST['mp'], FILTER_SANITIZE_NUMBER_INT);
	$expiration = date("Y-m-d", max(time(), min(strtotime("+30 days"), strtotime($_POST['expiration']))));

	$allowed_order_types = array('bid', 'ask', 'ipo', 'seo', 'obb');
	if(!in_array($order_type, $allowed_order_types)){
		$resp = array('success' => 0, 'msg' => 'Please select an order type!');
		echo json_encode($resp);
		exit();
	}
	if(!$shares || $shares < 1){
		$resp = array('success' => 0, 'msg' => 'Please input the number of shares you\'d like to trade.');
		echo json_encode($resp);
		exit();
	}
	if($shares > 999999999){
		$shares = 999999999;
	}
	if($order_type == 'bid' || $order_type == 'ask'){
		if(!$mp && $price < 1){
			$resp = array('success' => 0, 'msg' => 'Please input a valid price.');
			echo json_encode($resp);
			exit();
		}
		if($price > 999999999){
			$price = 999999999;
		}
	}

	// Confirm symbol exists, get fid
	$query = $db->prepare("SELECT firm_stock.id, firm_stock.fid, firm_stock.share_price FROM firm_stock WHERE firm_stock.symbol = :symbol");
	$query->execute(array(':symbol' => $ss));
	$stock_details = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($stock_details)){
		$resp = array('success' => 0, 'msg' => 'Symbol does not exist.');
		echo json_encode($resp);
		exit();
	}
	$fid = $stock_details['fid'];
	$market_price = $stock_details['share_price'];

	// Find out whether IPO data exists
	$query = $db->prepare("SELECT id, fid, shares, price, type, expiration FROM firm_stock_issuance WHERE fid = :fid ORDER BY id DESC LIMIT 0, 1");
	$query->execute(array(':fid' => $fid));
	$stock_issuance = $query->fetch(PDO::FETCH_ASSOC);
	if(!empty($stock_issuance)){
		if($stock_issuance['type'] == 'IPO'){
			if($order_type !== 'ipo'){
				$resp = array('success' => 0, 'msg' => 'The target company is still undergoing IPO.');
				echo json_encode($resp);
				exit();
			}
			if($shares > $stock_issuance['shares']){
				$resp = array('success' => 0, 'msg' => 'Sorry boss, only '.$stock_issuance['shares'].' shares are available or requested.');
				echo json_encode($resp);
				exit();
			}
		}
	}

	if($is_edit){
		$old_order_id = filter_var($_POST['old_order_id'], FILTER_SANITIZE_NUMBER_INT);
		$old_order_type = filter_var($_POST['old_order_type'], FILTER_SANITIZE_STRING);

		$old_order_transferred = 0;
		$old_order_deleted = 0;
		if($old_order_type == 'bid'){
			$query = $db->prepare("INSERT INTO stock_edit_temp (id, type, fid, pid, shares, aon, price, expiration) SELECT id, 'bid', fid, pid, shares, aon, price, expiration FROM stock_bid WHERE id = :id AND pid = :pid");
			$query->execute(array(':id' => $old_order_id, ':pid' => $eos_player_id));
			$old_order_transferred = $query->rowCount();
		}else if($old_order_type == 'ask'){
			$query = $db->prepare("INSERT INTO stock_edit_temp (id, type, fid, pid, shares, price, expiration) SELECT id, 'ask', fid, pid, shares, price, expiration FROM stock_ask WHERE id = :id AND pid = :pid");
			$query->execute(array(':id' => $old_order_id, ':pid' => $eos_player_id));
			$old_order_transferred = $query->rowCount();
		}
		if(!$old_order_transferred){
			$resp = array('success' => 0, 'msg' => 'Original order is missing, expired, or completed.');
			echo json_encode($resp);
			exit();
		}
		if($old_order_type == 'bid'){
			$query = $db->prepare("DELETE FROM stock_bid WHERE id = :id AND pid = :pid");
			$query->execute(array(':id' => $old_order_id, ':pid' => $eos_player_id));
			$old_order_deleted = $query->rowCount();
			
			$query_readd_old_order = $db->prepare("INSERT INTO stock_bid (id, fid, pid, shares, aon, price, expiration) SELECT id, fid, pid, shares, aon, price, expiration FROM stock_edit_temp WHERE id = :id AND type = 'bid'");
			$query_delete_temp_order = $db->prepare("DELETE FROM stock_edit_temp WHERE id = :id AND type = 'bid'");
		}else if($old_order_type == 'ask'){
			$query = $db->prepare("DELETE FROM stock_ask WHERE id = :id AND pid = :pid");
			$query->execute(array(':id' => $old_order_id, ':pid' => $eos_player_id));
			$old_order_deleted = $query->rowCount();
			
			$query_readd_old_order = $db->prepare("INSERT INTO stock_ask (id, fid, pid, shares, price, expiration) SELECT id, fid, pid, shares, price, expiration FROM stock_edit_temp WHERE id = :id AND type = 'ask'");
			$query_delete_temp_order = $db->prepare("DELETE FROM stock_edit_temp WHERE id = :id AND type = 'ask'");
		}
		if(!$old_order_deleted){
			$query_delete_temp_order->execute(array(':id' => $old_order_id));
			$resp = array('success' => 0, 'msg' => 'Original order is missing, expired, or completed.');
			echo json_encode($resp);
			exit();
		}
	}
	
	if($order_type == 'bid'){
		$sql = "SELECT COUNT(*) AS cnt FROM stock_bid WHERE pid = $eos_player_id";
		$count = $db->query($sql)->fetchColumn();

		// Restricts player to 100 orders
		if($count >= 100){
			$resp = array('success' => 0, 'msg' => 'Sorry, but Econosia law forbids players from having more than 100 active buy orders.');
			echo json_encode($resp);
			exit();
		}

		// If market price, price = 999999999 (max)
		if($mp) $price = 999999999;

		// Prepare queries to find matches
		$iteration_limit = 200;
		if($aon){
			$query_matches_count = $db->prepare("SELECT COUNT(*) FROM stock_ask WHERE fid = :fid AND price <= :price AND shares >= :shares");
			$query_matches_results = $db->prepare("SELECT id, pid, shares, price FROM stock_ask WHERE fid = :fid AND price <= :price AND shares >= :shares ORDER BY price ASC, id ASC LIMIT 0, $iteration_limit");
			$query_matches_param = array(':fid' => $fid, ':price' => $price, ':shares' => $shares);
		}else{
			$query_matches_count = $db->prepare("SELECT COUNT(*) FROM stock_ask WHERE fid = :fid AND price <= :price AND shares > 0");
			$query_matches_results = $db->prepare("SELECT id, pid, shares, price FROM stock_ask WHERE fid = :fid AND price <= :price AND shares > 0 ORDER BY price ASC, id ASC LIMIT 0, $iteration_limit");
			$query_matches_param = array(':fid' => $fid, ':price' => $price);
		}
		
		// Prepare queries to deduct shares, add/remove cash, and write logs
		$query_deduct_shares = $db->prepare("UPDATE stock_ask SET shares = shares - :shares WHERE id = :id AND shares > :shares");
		$query_delete_ask = $db->prepare("DELETE FROM stock_ask WHERE id = :id AND shares = :shares");
		$query_deduct_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash - :cash WHERE id = :id AND player_cash >= :cash");
		$query_give_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash + :cash WHERE id = :id");
		$query_deduct_player_shares = $db->prepare("UPDATE player_stock SET shares = shares - :shares WHERE pid = :pid AND fid = :fid AND shares > :shares");
		$query_delete_player_shares = $db->prepare("DELETE FROM player_stock WHERE pid = :pid AND fid = :fid AND shares = :shares");
		$query_give_player_shares = $db->prepare("UPDATE player_stock SET shares = shares + :shares WHERE pid = :pid AND fid = :fid");
		$query_insert_player_shares = $db->prepare("INSERT INTO player_stock (pid, fid, shares) VALUES (:pid, :fid, :shares)");
		$query_write_stock_log = $db->prepare("INSERT INTO log_stock (fid, spid, bpid, shares, share_price, total_price, transaction_time) VALUES (:fid, :spid, :bpid, :shares, :price, :total_price, NOW())");
		$query_update_stock_price = $db->prepare("UPDATE firm_stock SET share_price = :price, share_price_min = LEAST(share_price_min, :price), share_price_max = GREATEST(share_price_max, :price), last_active = NOW() WHERE fid = :fid");

		$query_matches_count->execute($query_matches_param);
		$count_matches = $query_matches_count->fetchColumn();
		$query_matches_results->execute($query_matches_param);
		$continue = 1;
		$last_price = 0;
		$oc_shares = 0;
		$oc_cash = 0;
		while($shares > 0 && $continue){
			if($result_matched = $query_matches_results->fetch(PDO::FETCH_ASSOC)){
				if($result_matched['shares'] >= $shares){
					$shares_to_transfer = $shares;
				}else{
					$shares_to_transfer = $result_matched['shares'];
				}
				// Special case, market price
				if($result_matched['price'] == 1){
					if($mp){
						$result_matched['price'] = $market_price;
					}else{
						$result_matched['price'] = $price;
					}
				}
				$total_price = $shares_to_transfer * $result_matched['price'];
				// Try to deduct cash
				$query_deduct_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(1.01 * $total_price)));
				$affected = $query_deduct_player_cash->rowCount();
				if(!$affected){
					if($aon){
						$resp = array('success' => 0, 'msg' => 'Sorry, your order cannot be placed due to insufficient (player) cash.');
					}else{
						$resp = array('success' => 0, 'msg' => 'Sorry, your order cannot be completed due to insufficient (player) cash. Please check your orders history for partially completed purchases (if they exist).');
					}
					echo json_encode($resp);
					exit();
				}
				// Try to remove shares from ask
				if($result_matched['shares'] > $shares_to_transfer){
					$query_deduct_shares->execute(array(':id' => $result_matched['id'], ':shares' => $shares_to_transfer));
					$affected = $query_deduct_shares->rowCount();
				}else{
					$query_delete_ask->execute(array(':id' => $result_matched['id'], ':shares' => $shares_to_transfer));
					$affected = $query_delete_ask->rowCount();
				}
				if(!$affected){
					// Reimburse cash, cancel order, add news, and skip to next item
					$query_give_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(1.01 * $total_price)));
					$sql = "DELETE FROM stock_ask WHERE id = ".$result_matched['id'];
					$db->query($sql);
					$sql = "INSERT INTO player_news (pid, body, date_created) VALUES (".$result_matched['pid'].", 'Your order for selling $ss was canceled due to a shortage of shares.', NOW())";
					$db->query($sql);
					continue;
				}
				// Try to remove shares from player
				$query_delete_player_shares->execute(array(':pid' => $result_matched['pid'], ':fid' => $fid, ':shares' => $shares_to_transfer));
				$affected = $query_delete_player_shares->rowCount();
				if(!$affected){
					$query_deduct_player_shares->execute(array(':pid' => $result_matched['pid'], ':fid' => $fid, ':shares' => $shares_to_transfer));
					$affected = $query_deduct_player_shares->rowCount();
				}
				if(!$affected){
					// Reimburse cash and skip to next item
					$query_give_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(1.01 * $total_price)));
					continue;
				}
				
				// Success, give shares and cash
				$query_give_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
				$affected = $query_give_player_shares->rowCount();
				if(!$affected){
					$query_insert_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
				}
				$query_give_player_cash->execute(array(':id' => $result_matched['pid'], ':cash' => ceil(0.99 * $total_price)));

				// Log transfer
				$query_write_stock_log->execute(array(':fid' => $fid, ':spid' => $result_matched['pid'], ':bpid' => $eos_player_id, ':shares' => $shares_to_transfer, ':price' => $result_matched['price'], ':total_price' => $total_price));
				$oc_shares += $shares_to_transfer;
				$oc_cash += 1.01 * $total_price;
				if($last_price != $result_matched['price']){
					$query_update_stock_price->execute(array(':fid' => $fid, ':price' => $result_matched['price']));
					$last_price = $result_matched['price'];
				}
				$shares = $shares - $shares_to_transfer;
			}else{
				if($count_matches > $iteration_limit){
					// Re-iterate
					$query_matches_count->execute($query_matches_param);
					$count_matches = $query_matches_count->fetchColumn();
					$query_matches_results->execute($query_matches_param);
				}else{
					// Add remaining shares to bid table
					$query = $db->prepare("INSERT INTO stock_bid (fid, pid, shares, aon, price, expiration) VALUES (:fid, :pid, :shares, :aon, :price, :expiration)");
					$query->execute(array(':fid' => $fid, ':pid' => $eos_player_id, ':shares' => $shares, ':aon' => $aon, ':price' => $price, ':expiration' => $expiration));
					$continue = 0;
				}
			}
		}
	}
	else if($order_type == 'ask'){
		$sql = "SELECT COUNT(*) AS cnt FROM stock_ask WHERE pid = $eos_player_id";
		$count = $db->query($sql)->fetchColumn();

		// Restricts player to 100 orders
		if($count >= 100){
			$resp = array('success' => 0, 'msg' => 'Sorry, but Econosia law forbids players from having more than 100 active sell orders.');
			echo json_encode($resp);
			exit();
		}

		// Check to make sure player has this many shares not tied up for sale
		$sql = "SELECT IFNULL(shares, 0) FROM player_stock WHERE pid = $eos_player_id AND fid = $fid";
		$available_shares = $db->query($sql)->fetchColumn();
		$sql = "SELECT IFNULL(SUM(shares), 0) FROM stock_ask WHERE pid = $eos_player_id AND fid = $fid";
		$asked_shares = $db->query($sql)->fetchColumn();
		if($shares > $available_shares - $asked_shares){
			if($is_edit){
				$query_readd_old_order->execute(array(':id' => $old_order_id));
				$query_delete_temp_order->execute(array(':id' => $old_order_id));
			}
			$resp = array('success' => 0, 'msg' => 'Sorry, short selling is not allowed here.');
			echo json_encode($resp);
			exit();
		}
		
		// If market price, price = 1 (min)
		if($mp) $price = 1;

		// Prepare queries to find matches
		$iteration_limit = 200;
		$query_matches_count = $db->prepare("SELECT COUNT(*) FROM stock_bid WHERE fid = :fid AND price >= :price AND shares > 0 AND (shares <= :shares OR NOT aon)");
		$query_matches_results = $db->prepare("SELECT id, pid, shares, price FROM stock_bid WHERE fid = :fid AND price >= :price AND shares > 0 AND (shares <= :shares OR NOT aon) ORDER BY price DESC, id ASC LIMIT 0, $iteration_limit");
		$query_matches_param = array(':fid' => $fid, ':price' => $price, ':shares' => $shares);
		
		// Prepare queries to deduct shares, add/remove cash, and write logs
		$query_deduct_shares = $db->prepare("UPDATE stock_bid SET shares = shares - :shares WHERE id = :id AND shares > :shares");
		$query_delete_bid = $db->prepare("DELETE FROM stock_bid WHERE id = :id AND shares = :shares");
		$query_deduct_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash - :cash WHERE id = :id AND player_cash >= :cash");
		$query_give_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash + :cash WHERE id = :id");
		$query_deduct_player_shares = $db->prepare("UPDATE player_stock SET shares = shares - :shares WHERE pid = :pid AND fid = :fid AND shares > :shares");
		$query_delete_player_shares = $db->prepare("DELETE FROM player_stock WHERE pid = :pid AND fid = :fid AND shares = :shares");
		$query_give_player_shares = $db->prepare("UPDATE player_stock SET shares = shares + :shares WHERE pid = :pid AND fid = :fid");
		$query_insert_player_shares = $db->prepare("INSERT INTO player_stock (pid, fid, shares) VALUES (:pid, :fid, :shares)");
		$query_write_stock_log = $db->prepare("INSERT INTO log_stock (fid, spid, bpid, shares, share_price, total_price, transaction_time) VALUES (:fid, :spid, :bpid, :shares, :price, :total_price, NOW())");
		$query_update_stock_price = $db->prepare("UPDATE firm_stock SET share_price = :price, share_price_min = LEAST(share_price_min, :price), share_price_max = GREATEST(share_price_max, :price), last_active = NOW() WHERE fid = :fid");

		$query_matches_count->execute($query_matches_param);
		$count_matches = $query_matches_count->fetchColumn();
		$query_matches_results->execute($query_matches_param);
		$continue = 1;
		$last_price = 0;
		$oc_shares = 0;
		$oc_cash = 0;
		while($shares > 0 && $continue){
			if($result_matched = $query_matches_results->fetch(PDO::FETCH_ASSOC)){
				if($result_matched['shares'] >= $shares){
					$shares_to_transfer = $shares;
				}else{
					$shares_to_transfer = $result_matched['shares'];
				}
				// Special case, market price
				if($result_matched['price'] == 999999999){
					if($mp){
						$result_matched['price'] = $market_price;
					}else{
						$result_matched['price'] = $price;
					}
				}
				$total_price = $shares_to_transfer * $result_matched['price'];
				// Try to deduct cash
				$query_deduct_player_cash->execute(array(':id' => $result_matched['pid'], ':cash' => ceil(1.01 * $total_price)));
				$affected = $query_deduct_player_cash->rowCount();
				if(!$affected){
					// Cancel order, add news, and skip to next item
					$sql = "DELETE FROM stock_bid WHERE id = ".$result_matched['id'];
					$db->query($sql);
					$sql = "INSERT INTO player_news (pid, body, date_created) VALUES (".$result_matched['pid'].", 'Your order for buying $ss was canceled due to a shortage of cash.', NOW())";
					$db->query($sql);
					continue;
				}
				// Try to remove shares from player
				$query_delete_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
				$affected = $query_delete_player_shares->rowCount();
				if(!$affected){
					$query_deduct_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
					$affected = $query_deduct_player_shares->rowCount();
				}
				if(!$affected){
					// Reimburse cash and fail
					$query_give_player_cash->execute(array(':id' => $result_matched['pid'], ':cash' => ceil(1.01 * $total_price)));

					$resp = array('success' => 0, 'msg' => 'Sorry, your order cannot be completed due to a shortage of shares.');
					echo json_encode($resp);
					exit();
				}
				// Try to remove shares from bid
				if($result_matched['shares'] > $shares_to_transfer){
					$query_deduct_shares->execute(array(':id' => $result_matched['id'], ':shares' => $shares_to_transfer));
					$affected = $query_deduct_shares->rowCount();
				}else{
					$query_delete_bid->execute(array(':id' => $result_matched['id'], ':shares' => $shares_to_transfer));
					$affected = $query_delete_bid->rowCount();
				}
				if(!$affected){
					// Reimburse cash and shares, then skip to next item
					$query_give_player_cash->execute(array(':id' => $result_matched['pid'], ':cash' => ceil(1.01 * $total_price)));
					$query_give_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
					$affected = $query_give_player_shares->rowCount();
					if(!$affected){
						$query_insert_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
					}
					continue;
				}
				
				// Success, give shares and cash
				$query_give_player_shares->execute(array(':pid' => $result_matched['pid'], ':fid' => $fid, ':shares' => $shares_to_transfer));
				$affected = $query_give_player_shares->rowCount();
				if(!$affected){
					$query_insert_player_shares->execute(array(':pid' => $result_matched['pid'], ':fid' => $fid, ':shares' => $shares_to_transfer));
				}
				$query_give_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(0.99 * $total_price)));

				// Log transfer
				$query_write_stock_log->execute(array(':fid' => $fid, ':spid' => $eos_player_id, ':bpid' => $result_matched['pid'], ':shares' => $shares_to_transfer, ':price' => $result_matched['price'], ':total_price' => $total_price));
				$oc_shares += $shares_to_transfer;
				$oc_cash += 0.99 * $total_price;
				if($last_price != $result_matched['price']){
					$query_update_stock_price->execute(array(':fid' => $fid, ':price' => $result_matched['price']));
					$last_price = $result_matched['price'];
				}
				$shares = $shares - $shares_to_transfer;
			}else{
				if($count_matches > $iteration_limit){
					// Re-iterate
					$query_matches_count->execute($query_matches_param);
					$count_matches = $query_matches_count->fetchColumn();
					$query_matches_results->execute($query_matches_param);
				}else{
					// Add remaining shares to ask table
					$query = $db->prepare("INSERT INTO stock_ask (fid, pid, shares, price, expiration) VALUES (:fid, :pid, :shares, :price, :expiration)");
					$query->execute(array(':fid' => $fid, ':pid' => $eos_player_id, ':shares' => $shares, ':price' => $price, ':expiration' => $expiration));
					$continue = 0;
				}
			}
		}
	}
	else{
		if($order_type == 'ipo' || $order_type == 'seo'){
			$query_deduct_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash - :cash WHERE id = :id AND player_cash >= :cash");
			$query_give_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash + :cash WHERE id = :id");
			$query_deduct_po = $db->prepare("UPDATE firm_stock_issuance SET shares = shares - :shares WHERE id = :id AND shares > :shares");
			$query_delete_po = $db->prepare("DELETE FROM firm_stock_issuance WHERE id = :id AND shares = :shares");
			$query_give_player_shares = $db->prepare("UPDATE player_stock SET shares = shares + :shares WHERE pid = :pid AND fid = :fid");
			$query_insert_player_shares = $db->prepare("INSERT INTO player_stock (pid, fid, shares) VALUES (:pid, :fid, :shares)");
			$query_increase_shares_os = $db->prepare("UPDATE firm_stock SET shares_os = shares_os + :shares, paid_in_capital = paid_in_capital + :cash WHERE fid = :fid");
			$query_write_stock_log = $db->prepare("INSERT INTO log_stock (fid, spid, bpid, shares, share_price, total_price, transaction_time) VALUES (:fid, :spid, :bpid, :shares, :price, :total_price, NOW())");
			$query_log_issued = $db->prepare("INSERT INTO firm_stock_issued_temp (fid, shares, total_price, type) VALUES (:fid, :shares, :total_price, :type)");
			$oc_shares = 0;
			$oc_cash = 0;

			$shares_to_transfer = $shares;
			$total_price = $shares_to_transfer * $stock_issuance['price'];
			// Try to deduct cash
			$query_deduct_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(1.01 * $total_price)));
			$affected = $query_deduct_player_cash->rowCount();
			if(!$affected){
				$resp = array('success' => 0, 'msg' => 'Sorry, your order cannot be placed due to insufficient (player) cash.');
				echo json_encode($resp);
				exit();
			}
			// Try to remove shares from po
			if($stock_issuance['shares'] > $shares_to_transfer){
				$query_deduct_po->execute(array(':id' => $stock_issuance['id'], ':shares' => $shares_to_transfer));
				$affected = $query_deduct_po->rowCount();
			}else{
				$query_delete_po->execute(array(':id' => $stock_issuance['id'], ':shares' => $shares_to_transfer));
				$affected = $query_delete_po->rowCount();
			}
			if(!$affected){
				// Reimburse cash, fail out
				$query_give_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(1.01 * $total_price)));
				
				// Find out whether IPO data exists
				$query = $db->prepare("SELECT id, fid, shares, price, type, expiration FROM firm_stock_issuance WHERE fid = :fid ORDER BY id DESC LIMIT 0, 1");
				$query->execute(array(':fid' => $fid));
				$stock_issuance = $query->fetch(PDO::FETCH_ASSOC);
				if(!empty($stock_issuance)){
					$resp = array('success' => 0, 'msg' => 'Sorry boss, only '.$stock_issuance['shares'].' shares are available.');
					echo json_encode($resp);
					exit();
				}else{
					$resp = array('success' => 0, 'msg' => 'Sorry, this public offering is no longer available.');
					echo json_encode($resp);
					exit();
				}
			}
			
			// Success, give shares
			$query_give_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
			$affected = $query_give_player_shares->rowCount();
			if(!$affected){
				$query_insert_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
			}
			$query_increase_shares_os->execute(array(':fid' => $fid, ':cash' => $total_price, ':shares' => $shares_to_transfer));

			// Log transfer
			$query_write_stock_log->execute(array(':fid' => $fid, ':spid' => 0, ':bpid' => $eos_player_id, ':shares' => $shares_to_transfer, ':price' => $stock_issuance['price'], ':total_price' => $total_price));
			$order_type_desc = 'SEO';
			if($order_type == 'ipo') $order_type_desc = 'IPO';
			$query_log_issued->execute(array(':fid' => $fid, ':shares' => $shares_to_transfer, ':total_price' => $total_price, ':type' => $order_type_desc));
			$oc_shares += $shares_to_transfer;
			$oc_cash += 1.01 * $total_price;
			$shares = 0;
		}
		else if($order_type == 'obb'){
			// Check to make sure player has this many shares not tied up for sale
			$sql = "SELECT IFNULL(shares, 0) FROM player_stock WHERE pid = $eos_player_id AND fid = $fid";
			$available_shares = $db->query($sql)->fetchColumn();
			$sql = "SELECT IFNULL(SUM(shares), 0) FROM stock_ask WHERE pid = $eos_player_id AND fid = $fid";
			$asked_shares = $db->query($sql)->fetchColumn();
			if($shares > $available_shares - $asked_shares){
				$resp = array('success' => 0, 'msg' => 'Sorry, short selling is not allowed here.');
				echo json_encode($resp);
				exit();
			}

			$query_deduct_player_shares = $db->prepare("UPDATE player_stock SET shares = shares - :shares WHERE pid = :pid AND fid = :fid AND shares > :shares");
			$query_delete_player_shares = $db->prepare("DELETE FROM player_stock WHERE pid = :pid AND fid = :fid AND shares = :shares");
			$query_give_player_shares = $db->prepare("UPDATE player_stock SET shares = shares + :shares WHERE pid = :pid AND fid = :fid");
			$query_insert_player_shares = $db->prepare("INSERT INTO player_stock (pid, fid, shares) VALUES (:pid, :fid, :shares)");
			$query_deduct_po = $db->prepare("UPDATE firm_stock_issuance SET shares = shares - :shares WHERE id = :id AND shares > :shares");
			$query_delete_po = $db->prepare("DELETE FROM firm_stock_issuance WHERE id = :id AND shares = :shares");
			$query_give_player_cash = $db->prepare("UPDATE players SET player_cash = player_cash + :cash WHERE id = :id");
			$query_write_stock_log = $db->prepare("INSERT INTO log_stock (fid, spid, bpid, shares, share_price, total_price, transaction_time) VALUES (:fid, :spid, :bpid, :shares, :price, :total_price, NOW())");
			$query_log_issued = $db->prepare("INSERT INTO firm_stock_issued_temp (fid, shares, total_price, type) VALUES (:fid, :shares, :total_price, :type)");
			$oc_shares = 0;
			$oc_cash = 0;

			$shares_to_transfer = $shares;
			$total_price = $shares_to_transfer * $stock_issuance['price'];

			// Try to remove shares from player
			$query_delete_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
			$affected = $query_delete_player_shares->rowCount();
			if(!$affected){
				$query_deduct_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
				$affected = $query_deduct_player_shares->rowCount();
			}
			if(!$affected){
				$resp = array('success' => 0, 'msg' => 'Sorry, your order cannot be completed due to a shortage of shares.');
				echo json_encode($resp);
				exit();
			}
			// Try to remove shares from po
			if($stock_issuance['shares'] > $shares_to_transfer){
				$query_deduct_po->execute(array(':id' => $stock_issuance['id'], ':shares' => $shares_to_transfer));
				$affected = $query_deduct_po->rowCount();
			}else{
				$query_delete_po->execute(array(':id' => $stock_issuance['id'], ':shares' => $shares_to_transfer));
				$affected = $query_delete_po->rowCount();
			}
			if(!$affected){
				// Reimburse shares, fail out
				$query_give_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
				$affected = $query_give_player_shares->rowCount();
				if(!$affected){
					$query_insert_player_shares->execute(array(':pid' => $eos_player_id, ':fid' => $fid, ':shares' => $shares_to_transfer));
				}

				// Find out whether IPO data exists
				$query = $db->prepare("SELECT id, fid, shares, price, type, expiration FROM firm_stock_issuance WHERE fid = :fid ORDER BY id DESC LIMIT 0, 1");
				$query->execute(array(':fid' => $fid));
				$stock_issuance = $query->fetch(PDO::FETCH_ASSOC);
				if(!empty($stock_issuance)){
					$resp = array('success' => 0, 'msg' => 'Sorry boss, only '.$stock_issuance['shares'].' shares are requested.');
					echo json_encode($resp);
					exit();
				}else{
					$resp = array('success' => 0, 'msg' => 'Sorry, this buyback is no longer available.');
					echo json_encode($resp);
					exit();
				}
			}
			
			// Success, give cash
			$query_give_player_cash->execute(array(':id' => $eos_player_id, ':cash' => ceil(0.99 * $total_price)));

			// Log transfer
			$query_write_stock_log->execute(array(':fid' => $fid, ':spid' => $eos_player_id, ':bpid' => 0, ':shares' => $shares_to_transfer, ':price' => $stock_issuance['price'], ':total_price' => $total_price));
			$query_log_issued->execute(array(':fid' => $fid, ':shares' => $shares_to_transfer, ':total_price' => $total_price, ':type' => 'Buyback'));
			$oc_shares += $shares_to_transfer;
			$oc_cash += 0.99 * $total_price;
			$shares = 0;
		}
	}

	// Cleanup
	if($is_edit){
		$query_delete_temp_order->execute(array(':id' => $old_order_id));
	}

	if($shares == 0){
		if($order_type == 'bid' || $order_type == 'ipo' || $order_type == 'seo'){
			$summary = 'You have successfully purchased '.number_format($oc_shares, 0, '.', ',').' shares of '.$ss.' for an average of $'.number_format($oc_cash / $oc_shares / 100, 2, '.', ',').' per share.';
		}else if($order_type == 'ask' || $order_type == 'obb'){
			$summary = 'You have successfully sold '.number_format($oc_shares, 0, '.', ',').' shares of '.$ss.' for an average of $'.number_format($oc_cash / $oc_shares / 100, 2, '.', ',').' per share.';
		}
		$resp = array('success' => 1, 'completed' => 1, 'summary' => $summary);
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 1);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'cancel_order'){
	$order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
	$order_type = filter_var($_POST['order_type'], FILTER_SANITIZE_STRING);

	if($order_id){
		$affected = 0;
		if($order_type == 'bid'){
			$query = $db->prepare("DELETE FROM stock_bid WHERE id = :id AND pid = :pid");
			$query->execute(array(':id' => $order_id, ':pid' => $eos_player_id));
			$affected = $query->rowCount();
		}else if($order_type == 'ask'){
			$query = $db->prepare("DELETE FROM stock_ask WHERE id = :id AND pid = :pid");
			$query->execute(array(':id' => $order_id, ':pid' => $eos_player_id));
			$affected = $query->rowCount();
		}
		if(!$affected){
			$resp = array('success' => 0, 'msg' => 'Cannot find order.');
			echo json_encode($resp);
			exit();
		}else{
			$resp = array('success' => 1, 'msg' => 'Order successfully canceled.');
			echo json_encode($resp);
			exit();
		}
	}
}
else if($action == 'check_symbol_name'){
	$symbol = $_POST['symbol'];
	
	$is_cap_alpha = preg_match("/^([A-Z])+$/", $symbol);
	if(!$is_cap_alpha){
		$resp = array('success' => 0, 'msg' => 'Stock symbol must consist of only capital latin alphabets (A-Z).');
		echo json_encode($resp);
		exit();
	}else if(strlen($symbol) < 2 || strlen($symbol) > 8){
		$resp = array('success' => 0, 'msg' => 'Symbol must be between 2 to 8 characters.');
		echo json_encode($resp);
		exit();
	}
	
	$sql = "SELECT COUNT(*) FROM firm_stock WHERE symbol = '$symbol'";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Symbol '.$symbol.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$resp = array('success' => 1, 'msg' => 'This symbol can be used.');
	echo json_encode($resp);
	exit();
}
else if($action == 'start_ipo'){
	$symbol = filter_var($_POST['symbol'], FILTER_SANITIZE_STRING);
	$capital_to_raise = filter_var($_POST['capital_to_raise'], FILTER_SANITIZE_NUMBER_INT);
	$share_price = filter_var($_POST['share_price'], FILTER_SANITIZE_NUMBER_INT);

	$is_cap_alpha = preg_match("/^([A-Z])+$/", $symbol);
	if(!$is_cap_alpha){
		$resp = array('success' => 0, 'msg' => 'Stock symbol must consist of only capital latin alphabets (A-Z).');
		echo json_encode($resp);
		exit();
	}else if(strlen($symbol) < 2 || strlen($symbol) > 8){
		$resp = array('success' => 0, 'msg' => 'Symbol must be between 2 to 8 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT firms.name, firms.networth, firms.cash, firms.level, firms.fame_level FROM firms WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company not found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_level = $firm['level'];
		$firm_fame_level = $firm['fame_level'];
	}

	$ipo_cost = 100000000;
	if($firm_cash < $ipo_cost){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}
	if($firm_networth < 10 * $ipo_cost){
		$resp = array('success' => 0, 'msg' => 'Your company\'s last assessed networth is too low.');
		echo json_encode($resp);
		exit();
	}
	if($firm_level < 6){
		$resp = array('success' => 0, 'msg' => 'Your company is too small.');
		echo json_encode($resp);
		exit();
	}
	if($firm_fame_level < 6){
		$resp = array('success' => 0, 'msg' => 'Your company is not reputable enough.');
		echo json_encode($resp);
		exit();
	}

	$min_capital_to_raise = min(10000000000000000, max(0, floor($firm_networth * 5/9500) * 100));
	$max_capital_to_raise = min(999999998000000001, max(0, floor($firm_networth * 9/100) * 100));
	if($capital_to_raise < 1 || $capital_to_raise < $min_capital_to_raise || $capital_to_raise > $max_capital_to_raise){
		$resp = array('success' => 0, 'msg' => 'The amount of capital to raise is invalid.');
		echo json_encode($resp);
		exit();
	}

	$min_share_price = max(100, $max_capital_to_raise/880000000);
	$max_share_price = min(999999999, max(10000, $max_capital_to_raise/10000));
	if($share_price < $min_share_price || $share_price > $max_share_price){
		$resp = array('success' => 0, 'msg' => 'The share price is invalid.');
		echo json_encode($resp);
		exit();
	}
	
	$shares_to_issue = floor($capital_to_raise / $share_price);
	if($shares_to_issue < 1 || $shares_to_issue > 999999999){
		$resp = array('success' => 0, 'msg' => 'The number of shares to issue is invalid.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'This action cannot be performed within 2 years of another IPO, SEO, Buyback, or Going Private.');
		echo json_encode($resp);
		exit();
	}

	// Appraise the company
	$sql = "SELECT name, cash, loan, fame_level, max_bldg FROM firms WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];

	$networth = 0;
	$appraised_cash = $firm["cash"];
	$networth += $appraised_cash;
	$appraised_loan = $firm["loan"];
	$networth -= $appraised_loan;
	$appraised_fame = floor(1000*pow(1.4,$firm["fame_level"])-1000);
	$networth += $appraised_fame;

	// Land Value, surprisingly, the equation is the square of pascal's trangle...
	$max_bldg = $firm["max_bldg"];
	$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
	$networth += $appraised_land;
	
	// Building Value, using size * cost
	$appraised_building = 0;
	
	$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$networth += $appraised_building;
	
	// Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
	$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $eos_firm_id";
	$appraised_research = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_research;
	
	// Warehouse value, using value, pidq, pidn
	$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $eos_firm_id";
	$appraised_wh = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_wh;

	// Market value, using value, pidq, pidn
	$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $eos_firm_id";
	$appraised_market = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_market;
	
	$networth = floor($networth);
	$shares_self = floor($networth / $share_price);
	if($shares_self < 1){
		$resp = array('success' => 0, 'msg' => 'Company\'s networth has changed, please get an up-to-date appraisal.');
		echo json_encode($resp);
		exit();
	}
	if($shares_self + $shares_to_issue > 999999999){
		$resp = array('success' => 0, 'msg' => 'Company\'s networth has changed, please issue less shares.');
		echo json_encode($resp);
		exit();
	}

	// Deduct cash
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $ipo_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $ipo_cost, 'SEC Fee', NOW())";
	$db->query($sql);

	$query = $db->prepare("INSERT INTO firm_stock (fid, symbol, shares_os, share_price, share_price_min, share_price_max, paid_in_capital, last_active) VALUES ($eos_firm_id, :symbol, :shares, :share_price, :share_price, :share_price, 0, NOW())");
	$query->execute(array(':symbol' => $symbol, ':shares' => $shares_self, ':share_price' => $share_price));
	
	$query = $db->prepare("INSERT INTO firm_stock_issuance (fid, shares, price, type, starts, expiration) VALUES ($eos_firm_id, :shares, :share_price, 'IPO', DATE_ADD(NOW(), INTERVAL +1 DAY), DATE_ADD(NOW(), INTERVAL +15 DAY))");
	$query->execute(array(':shares' => $shares_to_issue, ':share_price' => $share_price));

	$sql= "UPDATE firms SET networth = $networth WHERE id = $eos_firm_id";
	$db->query($sql);
	$sql = "UPDATE firms_extended SET firms_extended.is_public = 1 WHERE firms_extended.id = $eos_firm_id";
	$db->query($sql);
	$sql = "INSERT into player_stock (pid, fid, shares) VALUES ('$eos_player_id', '$eos_firm_id', '$shares_self')";
	$db->query($sql);
	$sql = "UPDATE firms_positions SET title = 'Chairman' WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$db->query($sql);
	$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($eos_player_id, 'You are now the chairman of the board for <a href=\"/eos/firm/$eos_firm_id\">$firm_name</a>, shareholders will be looking forward to the company\'s growth.', NOW())";
	$db->query($sql);

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('ipo', $eos_firm_id, NOW())";
	$db->query($sql);

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'start_seo'){
	$capital_to_raise = filter_var($_POST['capital_to_raise'], FILTER_SANITIZE_NUMBER_INT);
	$share_price = filter_var($_POST['share_price'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "SELECT firm_stock.shares_os, firm_stock.share_price, firm_stock.symbol, firms.name, firms.networth, firms.cash FROM firm_stock LEFT JOIN firms ON firm_stock.fid = firms.id WHERE firm_stock.fid = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company not found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_shares_os = $firm['shares_os'];
		$firm_share_price = $firm['share_price'];
		$firm_stock_symbol = $firm['symbol'];
	}

	$seo_cost = 100000000;
	if($firm_cash < $seo_cost){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}

	$min_additional_shares = 0;
	$max_additional_shares = max(0, 999999999 - $firm_shares_os);

	$min_share_price = max(1, floor(0.7 * $firm_share_price));
	$max_share_price = $firm_share_price;
	if($share_price < $min_share_price || $share_price > $max_share_price){
		$resp = array('success' => 0, 'msg' => 'The share price is invalid, perhaps market conditions have changed.');
		echo json_encode($resp);
		exit();
	}

	$min_capital_to_raise = 0;
	$max_capital_to_raise = min($max_additional_shares * $max_share_price, max(0, floor($firm_networth / 1000) * 100));
	if($capital_to_raise < 1 || $capital_to_raise < $min_capital_to_raise || $capital_to_raise > $max_capital_to_raise){
		$resp = array('success' => 0, 'msg' => 'The amount of capital to raise is invalid, perhaps market conditions have changed.');
		echo json_encode($resp);
		exit();
	}

	$shares_to_issue = floor($capital_to_raise / $share_price);
	if($shares_to_issue < 1 || $shares_to_issue > 999999999){
		$resp = array('success' => 0, 'msg' => 'The number of shares to issue is invalid.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Cannot initiate SEO while another IPO, SEO, or Buyback is active.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'This action cannot be performed within 2 years of another IPO, SEO, Buyback, or Going Private.');
		echo json_encode($resp);
		exit();
	}

	// Deduct cash
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $seo_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $seo_cost, 'SEC Fee', NOW())";
	$db->query($sql);

	$query = $db->prepare("INSERT INTO firm_stock_issuance (fid, shares, price, type, starts, expiration) VALUES ($eos_firm_id, :shares, :share_price, 'SEO', DATE_ADD(NOW(), INTERVAL +1 DAY), DATE_ADD(NOW(), INTERVAL +15 DAY))");
	$query->execute(array(':shares' => $shares_to_issue, ':share_price' => $share_price));

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('seo', $eos_firm_id, NOW())";
	$db->query($sql);

	// Notify shareholders
	$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, ?, NOW() FROM player_stock WHERE fid = ".$eos_firm_id);
	$query->execute(array('Dear Investor, <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> (<a href="/eos/stock-details.php?ss='.$firm_stock_symbol.'">'.$firm_stock_symbol.'</a>) has just filed a <a href="/eos/stock-po.php">new SEO</a> at $'.number_format_readable($share_price/100).' per share.'));

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'start_buyback'){
	$shares_to_repurc = filter_var($_POST['shares_to_repurc'], FILTER_SANITIZE_NUMBER_INT);
	$share_price = filter_var($_POST['share_price'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "SELECT firm_stock.shares_os, firm_stock.share_price, firm_stock.symbol, firms.name, firms.networth, firms.cash FROM firm_stock LEFT JOIN firms ON firm_stock.fid = firms.id WHERE firm_stock.fid = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company not found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_shares_os = $firm['shares_os'];
		$firm_share_price = $firm['share_price'];
		$firm_stock_symbol = $firm['symbol'];
	}

	$buyback_cost = 100000000;
	if($firm_cash < $buyback_cost){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}

	$min_buyback_shares = 0;
	$max_buyback_shares = max(0, $firm_shares_os - 100);
	if($shares_to_repurc < $min_buyback_shares || $shares_to_repurc > $max_buyback_shares){
		$resp = array('success' => 0, 'msg' => 'The number of shares to buyback is invalid.');
		echo json_encode($resp);
		exit();
	}

	$min_share_price = max(1, $firm_share_price);
	$max_share_price = min(999999999, 2 * $firm_share_price);
	if($share_price < $min_share_price || $share_price > $max_share_price){
		$resp = array('success' => 0, 'msg' => 'The share price is invalid, perhaps market conditions have changed.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Cannot initiate SEO while another IPO, SEO, or Buyback is active.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'This action cannot be performed within 2 years of another IPO, SEO, Buyback, or Going Private.');
		echo json_encode($resp);
		exit();
	}

	$total_cost = $buyback_cost + $shares_to_repurc * $share_price;

	// Deduct cash
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $total_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $buyback_cost, 'SEC Fee', NOW())";
	$db->query($sql);

	$query = $db->prepare("INSERT INTO firm_stock_issuance (fid, shares, price, type, starts, expiration) VALUES ($eos_firm_id, :shares, :share_price, 'Buyback', DATE_ADD(NOW(), INTERVAL +1 DAY), DATE_ADD(NOW(), INTERVAL +15 DAY))");
	$query->execute(array(':shares' => $shares_to_repurc, ':share_price' => $share_price));

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('buyback', $eos_firm_id, NOW())";
	$db->query($sql);

	// Notify shareholders
	$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, ?, NOW() FROM player_stock WHERE fid = ".$eos_firm_id);
	$query->execute(array('Dear Investor, <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> (<a href="/eos/stock-details.php?ss='.$firm_stock_symbol.'">'.$firm_stock_symbol.'</a>) has just filed a <a href="/eos/stock-po.php">Buyback request</a> at $'.number_format_readable($share_price/100).' per share.'));

	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'go_private'){
	$go_private_conf = filter_var($_POST['go_private_conf'], FILTER_SANITIZE_NUMBER_INT);
	if($go_private_conf != $eos_firm_id){
		$resp = array('success' => 0, 'msg' => 'Company not found.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT firm_stock.shares_os, firm_stock.share_price, firm_stock.symbol, firms.name, firms.networth, firms.cash FROM firm_stock LEFT JOIN firms ON firm_stock.fid = firms.id WHERE firm_stock.fid = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company not found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_shares_os = $firm['shares_os'];
		$firm_share_price = $firm['share_price'];
		$firm_stock_symbol = $firm['symbol'];
	}

	$go_private_cost = 100000000;
	if($firm_cash < $go_private_cost){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Cannot initiate SEO while another IPO, SEO, or Buyback is active.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'This action cannot be performed within 2 years of another IPO, SEO, Buyback, or Going Private.');
		echo json_encode($resp);
		exit();
	}

	// Must not have any other active shareholders with >=1% of total shares
	$sql = "SELECT COUNT(players.id) AS cnt FROM player_stock LEFT JOIN players ON player_stock.pid = players.id WHERE player_stock.fid = $eos_firm_id AND player_stock.pid != $eos_player_id AND player_stock.shares >= 0.01 * $firm_shares_os AND players.last_active > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Cannot Go Private when another active shareholder has more than 1% of total shares.');
		echo json_encode($resp);
		exit();
	}

	// Appraise the company
	$sql = "SELECT name, cash, loan, fame_level, max_bldg FROM firms WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];

	$networth = 0;
	$appraised_cash = $firm["cash"];
	$networth += $appraised_cash;
	$appraised_loan = $firm["loan"];
	$networth -= $appraised_loan;
	$appraised_fame = floor(1000*pow(1.4,$firm["fame_level"])-1000);
	$networth += $appraised_fame;

	// Land Value, surprisingly, the equation is the square of pascal's trangle...
	$max_bldg = $firm["max_bldg"];
	$appraised_land = 25000000 * ($max_bldg-12) * ($max_bldg-12) * ($max_bldg-11) * ($max_bldg-11);
	$networth += $appraised_land;
	
	// Building Value, using size * cost
	$appraised_building = 0;
	
	$sql = "SELECT SUM(firm_fact.size*list_fact.cost) FROM firm_fact LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_store.size*list_store.cost) FROM firm_store LEFT JOIN list_store ON firm_store.store_id = list_store.id WHERE firm_store.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$sql = "SELECT SUM(firm_rnd.size*list_rnd.cost) FROM firm_rnd LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $eos_firm_id";
	$appraised_building = $appraised_building + $db->query($sql)->fetchColumn();
	
	$networth += $appraised_building;
	
	// Research value, actual value is 1.8333 of last level, 5 used to account for depreciation
	$sql = "SELECT SUM(5 * list_prod.res_cost * POW(1.2, firm_tech.quality - 0.25 * list_prod.tech_avg)) AS tech_nw FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $eos_firm_id";
	$appraised_research = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_research;
	
	// Warehouse value, using value, pidq, pidn
	$sql = "SELECT SUM(firm_wh.pidn * list_prod.value * (1 + 0.02 * firm_wh.pidq)) FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = $eos_firm_id";
	$appraised_wh = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_wh;

	// Market value, using value, pidq, pidn
	$sql = "SELECT SUM(market_prod.pidn * list_prod.value * (1 + 0.02 * market_prod.pidq)) FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $eos_firm_id";
	$appraised_market = floor($db->query($sql)->fetchColumn());
	$networth += $appraised_market;
	
	$networth = floor($networth);
	$repurchase_price = max(1, round(1.5 * max($networth / $firm_shares_os, $firm_share_price)));
	
	$sql = "SELECT SUM(shares) AS ssh FROM player_stock WHERE fid = $eos_firm_id AND pid != $eos_player_id";
	$shares_to_repurc = $db->query($sql)->fetchColumn();

	$total_cost = $go_private_cost + $shares_to_repurc * $repurchase_price;

	// Deduct cash
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $total_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Company does not have enough cash. (Need $'.number_format_readable($total_cost/100).')');
		echo json_encode($resp);
		exit();
	}
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $go_private_cost, 'SEC Fee', NOW())";
	$db->query($sql);

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('go private', $eos_firm_id, NOW())";
	$db->query($sql);

	// Reimburse and notify shareholders
	$sql = "UPDATE (SELECT pid, IFNULL(shares, 0) AS rs FROM player_stock WHERE fid = $eos_firm_id AND pid != $eos_player_id) AS a LEFT JOIN players ON a.pid = players.id SET players.player_cash = players.player_cash + a.rs * $repurchase_price WHERE a.rs > 0";
	$db->query($sql);
	$sql = "INSERT INTO player_news (pid, body, date_created) SELECT a.pid, CONCAT('$firm_stock_symbol just went private. You have received $', FORMAT(a.rs * $repurchase_price / 100, 0), ' as compensation for the ', a.rs, ' shares you owned.'), NOW() FROM (SELECT pid, IFNULL(shares, 0) AS rs FROM player_stock WHERE fid = $eos_firm_id AND pid != $eos_player_id) AS a LEFT JOIN players ON a.pid = players.id WHERE a.rs > 0";
	$db->query($sql);
	
	// De-list
	$sql = "UPDATE firms_extended SET firms_extended.is_public = 0 WHERE firms_extended.id = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM player_stock WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "DELETE FROM firm_stock WHERE fid = $eos_firm_id";
	$db->query($sql);
	$sql = "UPDATE firms_positions SET title = 'Owner' WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$db->query($sql);
	$sql = "INSERT INTO player_news (pid, body, date_created) VALUES ($eos_player_id, 'Congratulations! You are now the owner of <a href=\"/eos/firm/$eos_firm_id\">$firm_name</a>, a privately held company.', NOW())";
	$db->query($sql);

	$resp = array('success' => 1, 'shares_to_repurc' => $shares_to_repurc, 'total_cost' => $total_cost);
	echo json_encode($resp);
	exit();
}
else if($action == 'start_split'){
	$split_from = filter_var($_POST['split_from'], FILTER_SANITIZE_NUMBER_INT);
	$split_to = filter_var($_POST['split_to'], FILTER_SANITIZE_NUMBER_INT);
	if($split_from == $split_to){
		$resp = array('success' => 0, 'msg' => 'Needless split.');
		echo json_encode($resp);
		exit();
	}
	if($split_from < 1 || $split_from > 10 || $split_to < 1 || $split_to > 10){
		$resp = array('success' => 0, 'msg' => 'Invalid split ratio.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT firm_stock.shares_os, firm_stock.share_price, firm_stock.symbol, firms.name, firms.networth, firms.cash FROM firm_stock LEFT JOIN firms ON firm_stock.fid = firms.id WHERE firm_stock.fid = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		$resp = array('success' => 0, 'msg' => 'Company not found.');
		echo json_encode($resp);
		exit();
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_shares_os = $firm['shares_os'];
		$firm_share_price = $firm['share_price'];
		$firm_stock_symbol = $firm['symbol'];
	}

	$final_shares = $firm_shares_os / $split_from * $split_to;
	if($final_shares < 100 && $final_shares < $firm_shares_os){
		$resp = array('success' => 0, 'msg' => 'The number of total shares must not drop below 100 as result from a split.');
		echo json_encode($resp);
		exit();
	}else if($final_shares > 999999999){
		$resp = array('success' => 0, 'msg' => 'The number of total shares must remain under 1 billion.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		$resp = array('success' => 0, 'msg' => 'Cannot perform stock split while another IPO, SEO, or Buyback is active.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action = 'split' AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -7 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'This action cannot be performed within 1 year of another stock split.');
		echo json_encode($resp);
		exit();
	}

	$split_cost = 100000000;
	$repurchase_value = 0;
	if($split_from > 1){
		$sql = "SELECT IFNULL(SUM(MOD(shares, $split_from)), 0) AS rs FROM player_stock WHERE fid = $eos_firm_id";
		$repurchase_shares = $db->query($sql)->fetchColumn();
		$repurchase_price = max(round($firm_networth / $firm_shares_os), $firm_share_price);
		$repurchase_value = $repurchase_price * $repurchase_shares;
		$split_cost += $repurchase_value;
	}
	if($firm_cash < $split_cost){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}

	// Deduct cash
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $split_cost, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ($eos_firm_id, 1, $split_cost, 'SEC Fee', NOW())";
	$db->query($sql);

	// Give reimbursement
	if($split_from > 1){
		$sql = "UPDATE (SELECT pid, IFNULL(SUM(MOD(shares, $split_from)), 0) AS rs FROM player_stock WHERE fid = $eos_firm_id) AS a LEFT JOIN players ON a.pid = players.id SET players.player_cash = players.player_cash + a.rs * $repurchase_price WHERE a.rs > 0";
		$db->query($sql);
		$sql = "INSERT INTO player_news (pid, body, date_created) SELECT a.pid, CONCAT('You have received $', FORMAT(a.rs * $repurchase_price / 100, 0), ' for selling ', a.rs, ' shares of $firm_stock_symbol in the reverse-split.'), NOW() FROM (SELECT pid, IFNULL(SUM(MOD(shares, $split_from)), 0) AS rs FROM player_stock WHERE fid = $eos_firm_id) AS a LEFT JOIN players ON a.pid = players.id WHERE a.rs > 0";
		$db->query($sql);
	}
	
	// Delete active orders
	$sql = "INSERT INTO player_news (pid, body, date_created) SELECT b.pid, 'Your orders on $firm_stock_symbol were canceled due to the stock split, please submit new ones with new prices.', NOW() FROM (SELECT pid FROM stock_edit_temp WHERE fid = $eos_firm_id UNION SELECT pid FROM stock_ask WHERE fid = $eos_firm_id UNION SELECT pid FROM stock_bid WHERE fid = $eos_firm_id) AS b GROUP BY b.pid";
	$db->query($sql);
	$sql = "DELETE stock_ask.*, stock_bid.*, stock_edit_temp.* FROM firm_stock 
	LEFT JOIN stock_edit_temp ON firm_stock.fid = stock_edit_temp.fid 
	LEFT JOIN stock_ask ON firm_stock.fid = stock_ask.fid 
	LEFT JOIN stock_bid ON firm_stock.fid = stock_bid.fid 
	WHERE firm_stock.fid = $eos_firm_id";
	$db->query($sql);

	// Do split
	$sql = "UPDATE firm_stock 
	LEFT JOIN player_stock ON firm_stock.fid = player_stock.fid 
	SET firm_stock.share_price = GREATEST(1, LEAST(999999999, ROUND(firm_stock.share_price / $split_to * $split_from))), firm_stock.share_price_min = GREATEST(1, LEAST(999999999, ROUND(firm_stock.share_price_min / $split_to * $split_from))), firm_stock.share_price_max = GREATEST(1, LEAST(999999999, ROUND(firm_stock.share_price_max / $split_to * $split_from))), firm_stock.share_price_open = GREATEST(1, LEAST(999999999, ROUND(firm_stock.share_price_open / $split_to * $split_from))), player_stock.shares = FLOOR(player_stock.shares / $split_from) * $split_to 
	WHERE firm_stock.fid = $eos_firm_id";
	$db->query($sql);

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('split', $eos_firm_id, NOW())";
	$db->query($sql);

	// Notify shareholders
	$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, ?, NOW() FROM player_stock WHERE fid = ".$eos_firm_id);
	$query->execute(array('Dear Investor, <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> (<a href="/eos/stock-details.php?ss='.$firm_stock_symbol.'">'.$firm_stock_symbol.'</a>) just had a '.$split_to.'-for-'.$split_from.' stock split.'));

	$sql = "UPDATE (SELECT fid, SUM(player_stock.shares) AS ssh FROM player_stock WHERE fid = $eos_firm_id) AS a LEFT JOIN firm_stock ON a.fid = firm_stock.fid SET firm_stock.shares_os = a.ssh";
	$db->query($sql);
	$sql = "SELECT shares_os, share_price FROM firm_stock WHERE fid = $eos_firm_id";
	$firm_stock_info = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$resp = array('success' => 1, 'total_shares' => $firm_stock_info['shares_os'], 'share_price' => $firm_stock_info['share_price'], 'total_repurc_value' => $repurchase_value);
	echo json_encode($resp);
	exit();
}
else if($action == 'set_dividend'){
	require_active_firm();
	$dividend = floor(filter_var($_POST['dividend'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	
	$sql = "SELECT firms.name, firms.networth, firms.cash, firm_stock.dividend, firm_stock.shares_os FROM firms LEFT JOIN firm_stock ON firms.id = firm_stock.fid WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_echoout('Unable to confirm your position in the company. Please make sure you are still an employee here.');
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$current_dividend = $firm['dividend'];
	}

	$min_dividend = 0;
	$max_dividend = max(0, floor($firm['networth'] / 100 / $firm['shares_os']));

	if($dividend < $min_dividend || $dividend > $max_dividend){
		$resp = array('success' => 0, 'msg' => 'The dividend you have entered is invalid. Please check and re-submit the form.');
		echo json_encode($resp);
		exit();
	}

	if($current_dividend == $dividend){
		$resp = array('success' => 1, 'dividend' => $dividend/100);
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM log_limited_actions WHERE action = 'set dividend' AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -7 DAY)";
	$action_performed = $db->query($sql)->fetchColumn();
	if($action_performed){
		$resp = array('success' => 0, 'msg' => 'Dividend for this company was changed within the past 7 days.');
		echo json_encode($resp);
		exit();
	}

	// Set dividend
	$sql = "UPDATE firm_stock SET dividend = '$dividend' WHERE fid = $eos_firm_id";
	$result = $db->query($sql);
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	$sql = "INSERT INTO log_limited_actions (action, actor_id, action_time) VALUES ('set dividend', $eos_firm_id, NOW())";
	$db->query($sql);

	// Notify shareholders
	$sql = "SELECT firms_extended.is_public, firm_stock.symbol FROM firms_extended LEFT JOIN firm_stock ON firms_extended.id = firm_stock.fid WHERE firms_extended.id = $eos_firm_id";
	$firm_stock_info = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($firm_stock_info['is_public']){
		$query = $db->prepare("INSERT INTO player_news (pid, body, date_created) SELECT pid, ?, NOW() FROM player_stock WHERE fid = ".$eos_firm_id);
		$query->execute(array('Dear Investor, <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> (<a href="/eos/stock-details.php?ss='.$firm_stock_info['symbol'].'">'.$firm_stock_info['symbol']."</a>) has changed its dividend to $".number_format($dividend/100,2,'.',',')."."));
	}

	$resp = array('success' => 1, 'dividend' => $dividend/100);
	echo json_encode($resp);
	exit();
}

?>