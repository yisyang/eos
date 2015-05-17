<?php require 'include/prehtml.php'; ?>
<?php
	$view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
	if(!$view_type){
		$resp = array('success' => 0, 'msg' => 'Unknown view type.');
		echo json_encode($resp);
		exit();
	}
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = 50;
	
	$offset = ($page_num - 1) * $per_page;

	switch($view_type){
		case 'company_networth':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms");
			$query_results = $db->prepare("SELECT id, name, networth AS value FROM firms ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Company Networth';
			$value_type = 'money';
			break;
		case 'company_fame':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms");
			$query_results = $db->prepare("SELECT id, name, fame_level AS value FROM firms ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Company Fame Level';
			$value_type = 'integer';
			break;
		case 'company_cash':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms");
			$query_results = $db->prepare("SELECT id, name, cash AS value FROM firms ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Company Cash';
			$value_type = 'money';
			break;
		case 'company_fact_all':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms");
			$query_results = $db->prepare("SELECT firms.id, firms.name, SUM(firm_fact.size) AS value FROM firms LEFT JOIN firm_fact ON firms.id = firm_fact.fid GROUP BY firms.id ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Total Factory Size';
			$value_type = 'large_integer';
			break;
		case 'company_store_all':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms");
			$query_results = $db->prepare("SELECT firms.id, firms.name, SUM(firm_store.size) AS value FROM firms LEFT JOIN firm_store ON firms.id = firm_store.fid GROUP BY firms.id ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Total Store Size';
			$value_type = 'large_integer';
			break;
		case 'company_rnd_all':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms");
			$query_results = $db->prepare("SELECT firms.id, firms.name, SUM(firm_rnd.size) AS value FROM firms LEFT JOIN firm_rnd ON firms.id = firm_rnd.fid GROUP BY firms.id ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Total R&amp;D Size';
			$value_type = 'large_integer';
			break;
		case 'company_fact':
			$query_count = $db->prepare("SELECT COUNT(firms.id) FROM firms LEFT JOIN firm_fact ON firms.id = firm_fact.fid WHERE firm_fact.id IS NOT NULL");
			$query_results = $db->prepare("SELECT firms.id, firms.name, firm_fact.size AS value FROM firms LEFT JOIN firm_fact ON firms.id = firm_fact.fid ORDER BY value DESC, firm_fact.id ASC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Largest Factory Size';
			$value_type = 'large_integer';
			break;
		case 'company_store':
			$query_count = $db->prepare("SELECT COUNT(*) FROM firms LEFT JOIN firm_store ON firms.id = firm_store.fid WHERE firm_store.id IS NOT NULL");
			$query_results = $db->prepare("SELECT firms.id, firms.name, firm_store.size AS value FROM firms LEFT JOIN firm_store ON firms.id = firm_store.fid ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Largest Store Size';
			$value_type = 'large_integer';
			break;
		case 'company_rnd':
			$query_count = $db->prepare("SELECT COUNT(firms.id) FROM firms LEFT JOIN firm_rnd ON firms.id = firm_rnd.fid WHERE firm_rnd.id IS NOT NULL");
			$query_results = $db->prepare("SELECT firms.id, firms.name, firm_rnd.size AS value FROM firms LEFT JOIN firm_rnd ON firms.id = firm_rnd.fid ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Largest R&amp;D Size';
			$value_type = 'large_integer';
			break;
		case 'company_res':
			$query_count = $db->prepare("SELECT COUNT(firm_tech.id) FROM firms LEFT JOIN firm_tech ON firms.id = firm_tech.fid");
			$query_results = $db->prepare("SELECT firms.id, CONCAT(firms.name, ' (', list_prod.name, ')') AS name, firm_tech.quality AS value FROM firms LEFT JOIN firm_tech ON firms.id = firm_tech.fid LEFT JOIN list_prod ON list_prod.id = firm_tech.pid WHERE firm_tech.quality > 5 ORDER BY value DESC, firm_tech.update_time ASC LIMIT $offset, $per_page");
			$ranking_type = 'firm';
			$ranking_title = 'Highest Research';
			$value_type = 'integer';
			break;
		case 'player_networth':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT id, player_name AS name, player_networth AS value FROM players WHERE player_networth > 200000000 ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'Player Networth';
			$value_type = 'money';
			break;
		case 'player_fame':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT id, player_name AS name, player_fame_level AS value FROM players WHERE player_fame_level > 1 ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'Player Fame Level';
			$value_type = 'integer';
			break;
		case 'player_cash':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT id, player_name AS name, player_cash AS value FROM players WHERE player_cash > 100000000 ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'Player Cash';
			$value_type = 'money';
			break;
		case 'player_influence':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT id, player_name AS name, influence AS value FROM players WHERE influence ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'Player Influence';
			$value_type = 'large_integer';
			break;
		case 'player_stock':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT players.id, players.player_name AS name, SUM(player_stock.shares * firm_stock.share_price) AS value FROM players LEFT JOIN player_stock ON players.id = player_stock.pid LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid GROUP BY players.id ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'Player Stock Value';
			$value_type = 'money';
			break;
		case 'player_salary':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT players.id, CONCAT(players.player_name, ' (', firms.name, ')') AS name, firms_positions.pay_flat AS value FROM firms_positions LEFT JOIN players ON firms_positions.pid = players.id LEFT JOIN firms ON firms_positions.fid = firms.id ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'Player Salary';
			$value_type = 'money';
			break;
		case 'player_jobs':
			$query_count = $db->prepare("SELECT COUNT(*) FROM players");
			$query_results = $db->prepare("SELECT players.id, players.player_name AS name, COUNT(firms_positions.id) AS value FROM players LEFT JOIN firms_positions ON firms_positions.pid = players.id GROUP BY players.id ORDER BY value DESC LIMIT $offset, $per_page");
			$ranking_type = 'player';
			$ranking_title = 'No. of Jobs';
			$value_type = 'integer';
			break;
		
		default:
			$resp = array('success' => 0, 'msg' => 'Unknown view type.');
			echo json_encode($resp);
			exit();
			break;
	}
	$query_count->execute();
	$total_items = intval($query_count->fetchColumn());
	$pages_total = ceil($total_items/$per_page);

	$query_results->execute();
	$results = $query_results->fetchAll(PDO::FETCH_ASSOC);

	$resp = array('success' => 1, 'title' => $ranking_title, 'type' => $ranking_type, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => min(1000, $total_items), 'results' => $results, 'value_type' => $value_type);
	echo json_encode($resp);
	exit();
?>