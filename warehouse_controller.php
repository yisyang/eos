<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'show_table'){
	if(!$ctrl_wh_view){
		$resp = array('success' => 0, 'msg' => 'Unauthorized.');
		echo json_encode($resp);
		exit();
	}
	$view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
	$view_type_id = filter_var($_POST['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = $settings_b2b_rows_per_page;
	
	$offset = ($page_num - 1) * $per_page;

	switch($view_type){
		case 'alpha':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_wh WHERE fid = :eos_firm_id AND firm_wh.pidn > 0");
			$query_results = $db->prepare("SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 ORDER BY list_prod.name ASC, firm_wh.pidq ASC LIMIT $offset, $per_page");
			$query_params = array(':eos_firm_id' => $eos_firm_id);
			$check_type_id = 0;
			break;
		case 'new':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_wh WHERE fid = :eos_firm_id AND firm_wh.pidn > 0");
			$query_results = $db->prepare("SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 ORDER BY firm_wh.id DESC LIMIT $offset, $per_page");
			$query_params = array(':eos_firm_id' => $eos_firm_id);
			$check_type_id = 0;
			break;
		case 'fact':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT firm_wh.id FROM firm_wh LEFT JOIN list_fact_choices ON firm_wh.pid = list_fact_choices.opid1 WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_fact_choices.fact_id = :view_type_id GROUP BY firm_wh.id) AS a");
			$query_results = $db->prepare("SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id LEFT JOIN list_fact_choices ON firm_wh.pid = list_fact_choices.opid1 WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_fact_choices.fact_id = :view_type_id GROUP BY firm_wh.id ORDER BY list_prod.name ASC, firm_wh.pidq ASC LIMIT $offset, $per_page");
			$query_params = array(':eos_firm_id' => $eos_firm_id, ':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_fact.id, list_fact.name FROM firm_wh LEFT JOIN list_fact_choices ON firm_wh.pid = list_fact_choices.opid1 LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_fact.id IS NOT NULL GROUP BY list_fact.id ORDER BY list_fact.name ASC");
			$check_type_id = 1;
			break;
		case 'store':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT firm_wh.id FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_store_choices.store_id = :view_type_id GROUP BY firm_wh.id) AS a");
			$query_results = $db->prepare("SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_store_choices.store_id = :view_type_id GROUP BY firm_wh.id ORDER BY list_prod.name ASC, firm_wh.pidq ASC LIMIT $offset, $per_page");
			$query_params = array(':eos_firm_id' => $eos_firm_id, ':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_store.id, list_store.name FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id LEFT JOIN list_store ON list_store_choices.store_id = list_store.id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_store.id IS NOT NULL GROUP BY list_store.id ORDER BY list_store.name ASC");
			$check_type_id = 1;
			break;
		case 'cat':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_prod.cat_id = :view_type_id");
			$query_results = $db->prepare("SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_prod.cat_id = :view_type_id ORDER BY list_prod.name ASC, firm_wh.pidq ASC LIMIT $offset, $per_page");
			$query_params = array(':eos_firm_id' => $eos_firm_id, ':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_cat.id, list_cat.name FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_cat.id IS NOT NULL AND list_cat.name NOT LIKE '-%' GROUP BY list_cat.id ORDER BY list_cat.name ASC");
			$check_type_id = 1;
			break;
		case 'search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_prod.name LIKE :search_term");
			$query_results = $db->prepare("SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.fid = :eos_firm_id AND firm_wh.pidn > 0 AND list_prod.name LIKE :search_term ORDER BY list_prod.name ASC, firm_wh.pidq ASC LIMIT $offset, $per_page");
			$query_params = array(':eos_firm_id' => $eos_firm_id, ':search_term' => '%'.$search_term.'%');
			$check_type_id = 0;
			break;
		default:
			$resp = array('success' => 0, 'msg' => 'Unknown view type.');
			echo json_encode($resp);
			exit();
			break;
	}
	if(!$check_type_id || $view_type_id){
		$query_count->execute($query_params);
		$total_items = intval($query_count->fetchColumn());
		$pages_total = ceil($total_items/$per_page);

		$query_results->execute($query_params);
		$wh_results = $query_results->fetchAll(PDO::FETCH_ASSOC);

		$resp = array('success' => 1, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => $total_items, 'results' => $wh_results);
		echo json_encode($resp);
		exit();
	}else{
		$query_type_results->execute(array(':eos_firm_id' => $eos_firm_id));
		$type_results = $query_type_results->fetchAll(PDO::FETCH_ASSOC);

		$resp = array('success' => 1, 'needTypeId' => 1, 'typeResults' => $type_results);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'refresh_row'){
	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);

	if($wh_id){
		$sql = "SELECT firm_wh.id, firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.has_icon FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.id = $wh_id AND firm_wh.pidn > 0 AND firm_wh.fid = $eos_firm_id";
		$wh_row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		
		if(empty($wh_row)){			
			$resp = array('success' => 1, 'notFound' => 1);
			echo json_encode($resp);
			exit();
		}
		
		$resp = array('success' => 1, 'notFound' => 0, 'resultRow' => $wh_row);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'discard'){
	if(!$ctrl_wh_discard){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);

	if($wh_id){
		$sql = "UPDATE firm_wh SET pidn = 0, pidpartialsale = 0 WHERE id = $wh_id AND fid = $eos_firm_id";
		if($result = $db->query($sql)){
			$resp = array('success' => 1);
			echo json_encode($resp);
			exit();
		}else{
			$resp = array('success' => 0, 'msg' => 'Product does not exist or delete FAILED.');
			echo json_encode($resp);
			exit();
		}
	}
}
else if($action == 'sell_to_market'){
	if(!$ctrl_wh_sell){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);
	$sell_num = filter_var($_POST['sell_num'], FILTER_SANITIZE_NUMBER_INT);
	$sell_price = filter_var($_POST['sell_price'], FILTER_SANITIZE_NUMBER_INT);
	$force_listing = 0;
	if(isset($_POST['force_listing'])){
		$force_listing = filter_var($_POST['force_listing'], FILTER_SANITIZE_NUMBER_INT);
	}

	if($sell_num > 999999999999999)
		$sell_num = 999999999999999;

	if($sell_num < 1){
		$resp = array('success' => 0, 'msg' => 'Please input a valid quantity.');
		echo json_encode($resp);
		exit();
	}
	if($sell_price < 1){
		$resp = array('success' => 0, 'msg' => 'Please input a valid price.');
		echo json_encode($resp);
		exit();
	}
	if($sell_num * $sell_price > 9999999999999999999){
		$resp = array('success' => 0, 'msg' => 'Sorry boss, our current software does not allow us to input such a huge listing. Please keep total price under $10 Q by lowering price or quantity.');
		echo json_encode($resp);
		exit();
	}
	if(!$wh_id){
		$resp = array('success' => 0, 'msg' => 'Warehouse item not specified.');
		echo json_encode($resp);
		exit();
	}

	// Restricts company to 200 listings
	$sql = "SELECT COUNT(*) AS cnt FROM market_prod WHERE fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if($count >= 200){
		$resp = array('success' => 0, 'msg' => 'Sorry, but Econosia law forbids companies from having more than 200 listings.');
		echo json_encode($resp);
		exit();
	}
	
	// Confirm that the firm has enough of this pid
	$sql = "SELECT firm_wh.pid, firm_wh.pidq, firm_wh.pidn, firm_wh.pidcost, list_prod.name, list_prod.value, list_prod.tech_avg FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.id = '$wh_id' AND firm_wh.fid = '$eos_firm_id'";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$ipid1 = $prod['pid'];
	$ipid1_q = $prod['pidq'];
	$ipid1_n = $prod['pidn'];
	$ipid1_cost = $prod['pidcost'];
	$ipid1_name = $prod['name'];
	$ipid1_value = $prod['value'];
	$ipid1_tech_avg = $prod['tech_avg'];
	
	if(empty($prod)){
		$resp = array('success' => 0, 'msg' => 'Warehouse item not found.');
		echo json_encode($resp);
		exit();
	}
	if($ipid1_value <= 1){
		$resp = array('success' => 0, 'msg' => $ipid1_name.' cannot be sold.');
		echo json_encode($resp);
		exit();
	}
	if($sell_price < (0.5 * min($ipid1_cost, $ipid1_value))){
		$resp = array('success' => 0, 'msg' => 'Dear Sir or Madam, <br /><br />Please understand that our company is funded by sales commissions, <br />and a price this low isn\'t good for OUR business.<br /><br />Econosia B2B Company');
		echo json_encode($resp);
		exit();
	}
	if($sell_price > ((100 + $ipid1_q) * $ipid1_value)){
		$resp = array('success' => 0, 'msg' => 'Dear Sir or Madam, <br /><br />We regret to inform you that your listing was rejected due to its extreme pricing.<br /><br />We receive hundreds of requests each day from the government for disclosure of client data, <br />and the price you are asking for will likely put your company on one of those requests.<br /><br />Econosia B2B Company');
		echo json_encode($resp);
		exit();
	}

	// Check too see if foreign company offers better deal
	// $sql = "SELECT foreign_raw_mat_purc.price FROM foreign_raw_mat_purc WHERE foreign_raw_mat_purc.pid = $ipid1 ORDER BY price DESC LIMIT 0,1";
	// $foreign_purc = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	// if(!empty($foreign_purc)){
		// $foreign_price = $foreign_purc['price'] * (1 + 0.02 * max(0, $ipid1_q - 0.67* $ipid1_tech_avg));
		
		// if(!$force_listing && $foreign_price >= $sell_price){
			// $resp = array('success' => 1, 'confNeeded' => 1, 'confMsg' => 'Are you sure? Your goods may sell for a higher price on the export market.');
			// echo json_encode($resp);
			// exit();
		// }
	// }

	if($ipid1_n <= $sell_num){
		// Sell all available
		$sell_num = $ipid1_n;
		if($ipid1_n < 1){
			$resp = array('success' => 0, 'msg' => 'Nothing to sell.');
			echo json_encode($resp);
			exit();
		}
		// Delete row when everything sold
		$sql = "UPDATE firm_wh SET pidn = 0, pidpartialsale = 0 WHERE id = $wh_id";
		$affected = $db->query($sql)->rowCount();
	}else{
		// Deduct from firm_wh
		$sql = "UPDATE firm_wh SET pidn = pidn - $sell_num WHERE id = $wh_id AND pidn >= $sell_num";
		$affected = $db->query($sql)->rowCount();
	}
	if(!$affected){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}

	// Add to market
	$sql = "INSERT INTO market_prod (fid, pid, pidq, pidn, pidcost, price, listed) VALUES ($eos_firm_id, $ipid1, $ipid1_q, $sell_num, $ipid1_cost, $sell_price, NOW())";
	$result = $db->query($sql);
	if($result){
		$resp = array('success' => 1, 'msg' => $sell_num.' unit(s) of '.strtolower($ipid1_name).'(s) listed the on market for $'.number_format($sell_price/100, 2, '.', ',').' ea.');
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
}
?>