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
	$view_type_id = filter_var($_POST['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	$sort_type = filter_var($_POST['sort_type'], FILTER_SANITIZE_STRING);
	$sort_asc = filter_var($_POST['sort_asc'], FILTER_SANITIZE_NUMBER_INT);
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = 50;
	
	$offset = ($page_num - 1) * $per_page;

	$suggest_search = 0;
	$check_type_id = 0;
	$force_query_type = 0;
	$query_count = null;
	$query_results = null;
	$query_params = array();
	$query_count = null;
	$query_results = null;
	$query_type_params = array();

	$sort_type_query = 'list_prod.name';
	if($sort_type == 'value') $sort_type_query = 'list_prod.value';
	if($sort_type == 'price') $sort_type_query = 'list_cat.sellable DESC, list_prod.value_avg';
	if($sort_type == 'quality') $sort_type_query = 'list_cat.sellable DESC, list_prod.q_avg';
	if($sort_type == 'tech') $sort_type_query = 'list_prod.tech_avg';
	if($sort_type == 'demand_met') $sort_type_query = 'list_cat.sellable DESC, list_prod.demand_met';
	$sort_asc_query = 'ASC';
	if($sort_asc == 0) $sort_asc_query = 'DESC';

	switch($view_type){
		case 'prod':
			$query_count = $db->prepare("SELECT COUNT(*) FROM list_prod");
			$query_results = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_prod.has_icon, list_cat.sellable FROM list_cat LEFT JOIN list_prod ON list_prod.cat_id = list_cat.id WHERE list_cat.name NOT LIKE '-%' AND list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY $sort_type_query $sort_asc_query LIMIT $offset, $per_page");
			break;
		case 'cat':
			$query_count = $db->prepare("SELECT COUNT(*) FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_cat.id = :view_type_id");
			$query_results = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_prod.has_icon, list_cat.sellable FROM list_cat LEFT JOIN list_prod ON list_prod.cat_id = list_cat.id WHERE list_cat.id = :view_type_id AND list_cat.name NOT LIKE '-%' AND list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY $sort_type_query $sort_asc_query LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT * FROM list_cat WHERE name NOT LIKE '-%' ORDER BY name ASC");
			$check_type_id = 1;
			break;
		case 'fact':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT list_prod.id FROM list_prod LEFT JOIN list_fact_choices ON list_prod.id = list_fact_choices.opid1 WHERE list_fact_choices.fact_id = :view_type_id AND list_prod.id IS NOT NULL GROUP BY list_prod.id) AS a");
			$query_results = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_prod.has_icon, list_cat.sellable FROM list_prod LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id LEFT JOIN list_fact_choices ON list_prod.id = list_fact_choices.opid1 WHERE list_fact_choices.fact_id = :view_type_id AND list_cat.name NOT LIKE '-%' AND list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY $sort_type_query $sort_asc_query LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT 'fact' AS bldg_type, list_fact.id, list_fact.name FROM list_fact ORDER BY list_fact.name ASC");
			$check_type_id = 1;
			break;
		case 'store':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT list_prod.id FROM list_prod LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id WHERE list_store_choices.store_id = :view_type_id AND list_prod.id IS NOT NULL GROUP BY list_prod.id) AS a");
			$query_results = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_prod.has_icon, list_cat.sellable FROM list_prod LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id WHERE list_store_choices.store_id = :view_type_id AND list_cat.name NOT LIKE '-%' AND list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY $sort_type_query $sort_asc_query LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT 'store' AS bldg_type, list_store.id, list_store.name FROM list_store ORDER BY list_store.name ASC");
			$check_type_id = 1;
			break;
		case 'rnd':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT list_prod.id FROM list_prod LEFT JOIN list_rnd_choices ON list_rnd_choices.cat_id = list_prod.cat_id WHERE list_rnd_choices.rnd_id = :view_type_id AND list_prod.id IS NOT NULL GROUP BY list_prod.id) AS a");
			$query_results = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_prod.has_icon, list_cat.sellable FROM list_prod LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id LEFT JOIN list_rnd_choices ON list_rnd_choices.cat_id = list_prod.cat_id WHERE list_rnd_choices.rnd_id = :view_type_id AND list_cat.name NOT LIKE '-%' AND list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY $sort_type_query $sort_asc_query LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT 'rnd' AS bldg_type, list_rnd.id, list_rnd.name FROM list_prod LEFT JOIN list_rnd_choices ON list_rnd_choices.cat_id = list_prod.cat_id LEFT JOIN list_rnd ON list_rnd_choices.rnd_id = list_rnd.id WHERE list_rnd.id IS NOT NULL GROUP BY list_rnd.id ORDER BY list_rnd.name ASC");
			$check_type_id = 1;
			break;
		case 'search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM list_prod WHERE list_prod.name LIKE :search_term");
			$query_results = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_prod.has_icon, list_cat.sellable FROM list_prod LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id WHERE list_prod.name LIKE :search_term ORDER BY $sort_type_query $sort_asc_query LIMIT $offset, $per_page");
			$query_params = array(':search_term' => '%'.$search_term.'%');
			$suggest_search = 1;
			break;
		case 'cats_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_type_results = $db->prepare("SELECT * FROM list_cat WHERE name LIKE :search_term AND name NOT LIKE '-%' ORDER BY name ASC");
			$query_type_params = array(':search_term' => '%'.$search_term.'%');
			$check_type_id = 1;
			$suggest_search = 1;
			$force_query_type = 1;
			break;
		case 'buildings_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_type_results = $db->prepare("
			(SELECT 'fact' AS bldg_type, list_fact.id, list_fact.name FROM list_fact WHERE list_fact.name LIKE :search_term ORDER BY list_fact.name ASC) UNION
			(SELECT 'store' AS bldg_type, list_store.id, list_store.name FROM list_store WHERE list_store.name LIKE :search_term ORDER BY list_store.name ASC) UNION
			(SELECT 'rnd' AS bldg_type, list_rnd.id, list_rnd.name FROM list_rnd WHERE list_rnd.name LIKE :search_term ORDER BY list_rnd.name ASC)");
			$query_type_params = array(':search_term' => '%'.$search_term.'%');
			$check_type_id = 1;
			$suggest_search = 1;
			$force_query_type = 1;
			break;
		default:
			$resp = array('success' => 0, 'msg' => 'Unknown view type.');
			echo json_encode($resp);
			exit();
			break;
	}
	if(!$force_query_type && (!$check_type_id || $view_type_id)){
		$query_count->execute($query_params);
		$total_items = intval($query_count->fetchColumn());
		$pages_total = ceil($total_items/$per_page);

		$query_results->execute($query_params);
		$pedia_results = $query_results->fetchAll(PDO::FETCH_ASSOC);

		$resp = array('success' => 1, 'suggestSearch' => $suggest_search, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => $total_items, 'results' => $pedia_results);
		echo json_encode($resp);
		exit();
	}else{
		$query_type_results->execute($query_type_params);
		$type_results = $query_type_results->fetchAll(PDO::FETCH_ASSOC);

		$resp = array('success' => 1, 'suggestSearch' => $suggest_search, 'needTypeId' => 1, 'typeResults' => $type_results);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'show_prod'){
	$pedia_id = filter_var($_POST['pedia_id'], FILTER_SANITIZE_NUMBER_INT);

	if($pedia_id){
		$sql = "SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.id = '$pedia_id'";
		
		$pedia_row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		
		if(empty($pedia_row)){			
			$resp = array('success' => 1, 'notFound' => 1);
			echo json_encode($resp);
			exit();
		}
		
		$resp = array('success' => 1, 'notFound' => 0, 'resultRow' => $pedia_row);
		echo json_encode($resp);
		exit();
	}
}
?>