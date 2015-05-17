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
	$view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
	$view_type_id = filter_var($_POST['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	$page_num = intval(filter_var($_POST['page_num'], FILTER_SANITIZE_NUMBER_INT));
	$per_page = $settings_b2b_rows_per_page;
	
	$offset = ($page_num - 1) * $per_page;

	switch($view_type){
		case 'alpha':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_prod");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array();
			$check_type_id = 0;
			break;
		case 'new':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_prod");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id ORDER BY market_prod.listed DESC LIMIT $offset, $per_page");
			$query_params = array();
			$check_type_id = 0;
			break;
		case 'fact':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT market_prod.id FROM market_prod LEFT JOIN list_fact_choices ON market_prod.pid = list_fact_choices.opid1 WHERE list_fact_choices.fact_id = :view_type_id GROUP BY market_prod.id) AS a");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_fact_choices ON market_prod.pid = list_fact_choices.opid1 WHERE list_fact_choices.fact_id = :view_type_id GROUP BY market_prod.id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_fact.id, list_fact.name FROM market_prod LEFT JOIN list_fact_choices ON market_prod.pid = list_fact_choices.opid1 LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id WHERE list_fact.id IS NOT NULL GROUP BY list_fact.id ORDER BY list_fact.name ASC");
			$check_type_id = 1;
			break;
		case 'store':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM (SELECT market_prod.id FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id LEFT JOIN list_store ON list_store_choices.store_id = list_store.id WHERE list_store_choices.store_id = :view_type_id GROUP BY market_prod.id) AS a");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id WHERE list_store_choices.store_id = :view_type_id GROUP BY market_prod.id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_store.id, list_store.name FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id LEFT JOIN list_store ON list_store_choices.store_id = list_store.id WHERE list_store.id IS NOT NULL GROUP BY list_store.id ORDER BY list_store.name ASC");
			$check_type_id = 1;
			break;
		case 'cat':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.cat_id = :view_type_id");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.cat_id = :view_type_id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_cat.id, list_cat.name FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id WHERE list_cat.id IS NOT NULL AND list_cat.name NOT LIKE '-%' GROUP BY list_cat.id ORDER BY list_cat.name ASC");
			$check_type_id = 1;
			break;
		case 'prod':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.id = :view_type_id");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.id = :view_type_id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_prod.id, list_prod.name FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY list_prod.name ASC");
			$check_type_id = 1;
			break;
		case 'firm':
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = :view_type_id");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = :view_type_id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$check_type_id = 0;
			break;
		case 'my':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_prod WHERE market_prod.fid = $eos_firm_id");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.fid = $eos_firm_id ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array();
			$check_type_id = 0;
			break;
		case 'search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.name LIKE :search_term");
			$query_results = $db->prepare("SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE list_prod.name LIKE :search_term ORDER BY list_prod.name ASC, market_prod.price ASC, market_prod.listed ASC LIMIT $offset, $per_page");
			$query_params = array(':search_term' => '%'.$search_term.'%');
			$check_type_id = 0;
			break;
		case 'requests_alpha':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_requests");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array();
			$check_type_id = 0;
			break;
		case 'requests_new':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_requests");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq ORDER BY market_requests.requested DESC LIMIT $offset, $per_page");
			$query_params = array();
			$check_type_id = 0;
			break;
		case 'requests_fact':
			$query_count = $db->prepare("SELECT COUNT(*) FROM (SELECT market_requests.id FROM market_requests LEFT JOIN list_fact_choices ON market_requests.pid = list_fact_choices.opid1 WHERE list_fact_choices.fact_id = :view_type_id GROUP BY market_requests.id) AS a");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_fact_choices ON market_requests.pid = list_fact_choices.opid1 LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE list_fact_choices.fact_id = :view_type_id GROUP BY market_requests.id ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_fact.id, list_fact.name FROM market_requests LEFT JOIN list_fact_choices ON market_requests.pid = list_fact_choices.opid1 LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id WHERE list_fact.id IS NOT NULL GROUP BY list_fact.id ORDER BY list_fact.name ASC");
			$check_type_id = 1;
			break;
		case 'requests_store':
			$query_count = $db->prepare("SELECT COUNT(*) FROM (SELECT market_requests.id FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id LEFT JOIN list_store ON list_store_choices.store_id = list_store.id WHERE list_store_choices.store_id = :view_type_id GROUP BY market_requests.id) AS a");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id LEFT JOIN list_store ON list_store_choices.store_id = list_store.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE list_store_choices.store_id = :view_type_id GROUP BY market_requests.id ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_store.id, list_store.name FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_store_choices ON list_store_choices.cat_id = list_prod.cat_id LEFT JOIN list_store ON list_store_choices.store_id = list_store.id WHERE list_store.id IS NOT NULL GROUP BY list_store.id ORDER BY list_store.name ASC");
			$check_type_id = 1;
			break;
		case 'requests_cat':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id WHERE list_prod.cat_id = :view_type_id");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE list_prod.cat_id = :view_type_id ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_cat.id, list_cat.name FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_cat ON list_cat.id = list_prod.cat_id WHERE list_cat.id IS NOT NULL AND list_cat.name NOT LIKE '-%' GROUP BY list_cat.id ORDER BY list_cat.name ASC");
			$check_type_id = 1;
			break;
		case 'requests_prod':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id WHERE list_prod.id = :view_type_id");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE list_prod.id = :view_type_id ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$query_type_results = $db->prepare("SELECT list_prod.id, list_prod.name FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id WHERE list_prod.id IS NOT NULL GROUP BY list_prod.id ORDER BY list_prod.name ASC");
			$check_type_id = 1;
			break;
		case 'requests_firm':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_requests WHERE market_requests.fid = :view_type_id");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE market_requests.fid = :view_type_id ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array(':view_type_id' => $view_type_id);
			$check_type_id = 0;
			break;
		case 'requests_my':
			$query_count = $db->prepare("SELECT COUNT(*) FROM market_requests WHERE market_requests.fid = $eos_firm_id");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE market_requests.fid = $eos_firm_id ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array();
			$check_type_id = 0;
			break;
		case 'requests_search':
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id WHERE list_prod.name LIKE :search_term");
			$query_results = $db->prepare("SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE list_prod.name LIKE :search_term ORDER BY list_prod.name ASC, market_requests.price DESC, market_requests.requested ASC LIMIT $offset, $per_page");
			$query_params = array(':search_term' => '%'.$search_term.'%');
			$check_type_id = 0;
			break;
		case 'history_purcs':
			$target_fid = filter_var($_POST['target_fid'], FILTER_SANITIZE_NUMBER_INT);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM log_market_prod WHERE bfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY)");
			$query_results = $db->prepare("SELECT log_market_prod.id, log_market_prod.sfid, log_market_prod.bfid, log_market_prod.pid, log_market_prod.pidq, log_market_prod.pidn, log_market_prod.price, log_market_prod.hide, log_market_prod.transaction_time, list_prod.name, list_prod.has_icon, sf.name AS sf_name, bf.name AS bf_name FROM log_market_prod LEFT JOIN firms AS sf ON log_market_prod.sfid = sf.id LEFT JOIN firms AS bf ON log_market_prod.bfid = bf.id LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE log_market_prod.bfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) ORDER BY log_market_prod.hide ASC, log_market_prod.transaction_time DESC LIMIT $offset, $per_page");
			$query_params = array(':firm_id' => $target_fid);
			$check_type_id = 0;
			break;
		case 'history_purcs_search':
			$target_fid = filter_var($_POST['target_fid'], FILTER_SANITIZE_NUMBER_INT);
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM log_market_prod LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE bfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) AND list_prod.name LIKE :search_term");
			$query_results = $db->prepare("SELECT log_market_prod.id, log_market_prod.sfid, log_market_prod.bfid, log_market_prod.pid, log_market_prod.pidq, log_market_prod.pidn, log_market_prod.price, log_market_prod.hide, log_market_prod.transaction_time, list_prod.name, list_prod.has_icon, sf.name AS sf_name, bf.name AS bf_name FROM log_market_prod LEFT JOIN firms AS sf ON log_market_prod.sfid = sf.id LEFT JOIN firms AS bf ON log_market_prod.bfid = bf.id LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE log_market_prod.bfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) AND list_prod.name LIKE :search_term ORDER BY log_market_prod.hide ASC, log_market_prod.transaction_time DESC LIMIT $offset, $per_page");
			$query_params = array(':firm_id' => $target_fid, ':search_term' => '%'.$search_term.'%');
			$check_type_id = 0;
			break;
		case 'history_sales':
			$target_fid = filter_var($_POST['target_fid'], FILTER_SANITIZE_NUMBER_INT);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM log_market_prod WHERE sfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY)");
			$query_results = $db->prepare("SELECT log_market_prod.id, log_market_prod.sfid, log_market_prod.bfid, log_market_prod.pid, log_market_prod.pidq, log_market_prod.pidn, log_market_prod.price, log_market_prod.hide, log_market_prod.transaction_time, list_prod.name, list_prod.has_icon, sf.name AS sf_name, bf.name AS bf_name FROM log_market_prod LEFT JOIN firms AS sf ON log_market_prod.sfid = sf.id LEFT JOIN firms AS bf ON log_market_prod.bfid = bf.id LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE log_market_prod.sfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) ORDER BY log_market_prod.hide ASC, log_market_prod.transaction_time DESC LIMIT $offset, $per_page");
			$query_params = array(':firm_id' => $target_fid);
			$check_type_id = 0;
			break;
		case 'history_sales_search':
			$target_fid = filter_var($_POST['target_fid'], FILTER_SANITIZE_NUMBER_INT);
			$search_term = filter_var($_POST['view_type_id'], FILTER_SANITIZE_STRING);
			$query_count = $db->prepare("SELECT COUNT(*) AS cnt FROM log_market_prod LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE sfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) AND list_prod.name LIKE :search_term");
			$query_results = $db->prepare("SELECT log_market_prod.id, log_market_prod.sfid, log_market_prod.bfid, log_market_prod.pid, log_market_prod.pidq, log_market_prod.pidn, log_market_prod.price, log_market_prod.hide, log_market_prod.transaction_time, list_prod.name, list_prod.has_icon, sf.name AS sf_name, bf.name AS bf_name FROM log_market_prod LEFT JOIN firms AS sf ON log_market_prod.sfid = sf.id LEFT JOIN firms AS bf ON log_market_prod.bfid = bf.id LEFT JOIN list_prod ON log_market_prod.pid = list_prod.id WHERE log_market_prod.sfid = :firm_id AND log_market_prod.transaction_time > DATE_ADD(NOW(), INTERVAL -14 DAY) AND list_prod.name LIKE :search_term ORDER BY log_market_prod.hide ASC, log_market_prod.transaction_time DESC LIMIT $offset, $per_page");
			$query_params = array(':firm_id' => $target_fid, ':search_term' => '%'.$search_term.'%');
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
		$b2b_results = $query_results->fetchAll(PDO::FETCH_ASSOC);

		$resp = array('success' => 1, 'perPage' => $per_page, 'pageNum' => $page_num, 'totalItems' => $total_items, 'results' => $b2b_results);
		echo json_encode($resp);
		exit();
	}else{
		$query_type_results->execute();
		$type_results = $query_type_results->fetchAll(PDO::FETCH_ASSOC);

		$resp = array('success' => 1, 'needTypeId' => 1, 'typeResults' => $type_results);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'refresh_row'){
	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);

	if($b2b_id){
		$sql = "SELECT market_prod.id, market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.price, market_prod.listed, list_prod.name, list_prod.has_icon, firms.name AS firm_name FROM market_prod LEFT JOIN firms ON market_prod.fid = firms.id LEFT JOIN list_prod ON market_prod.pid = list_prod.id WHERE market_prod.id = '$b2b_id'";
		
		$b2b_row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		
		if(empty($b2b_row)){			
			$resp = array('success' => 1, 'notFound' => 1);
			echo json_encode($resp);
			exit();
		}
		
		$resp = array('success' => 1, 'notFound' => 0, 'resultRow' => $b2b_row);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'refresh_row_requests'){
	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);

	if($b2b_id){
		$sql = "SELECT market_requests.id, market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.has_icon, firms.name AS firm_name, IFNULL(firm_wh.pidn, 0) AS owned FROM market_requests LEFT JOIN firms ON market_requests.fid = firms.id LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN firm_wh ON firm_wh.fid = $eos_firm_id AND firm_wh.pid = market_requests.pid AND firm_wh.pidq >= market_requests.pidq WHERE market_requests.id = '$b2b_id'";
		
		$b2b_row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		
		if(empty($b2b_row)){			
			$resp = array('success' => 1, 'notFound' => 1);
			echo json_encode($resp);
			exit();
		}
		
		$resp = array('success' => 1, 'notFound' => 0, 'resultRow' => $b2b_row);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'buy'){
	if(!$ctrl_b2b_buy){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);
	$buy_num = filter_var($_POST['buy_num'], FILTER_SANITIZE_NUMBER_INT);
	$force_buy = 0;
	if(isset($_POST['force_buy'])){
		$force_buy = filter_var($_POST['force_buy'], FILTER_SANITIZE_NUMBER_INT);
	}

	if($buy_num < 1){
		$resp = array('success' => 0, 'msg' => 'Please input a valid quantity.');
		echo json_encode($resp);
		exit();
	}
	if(!$b2b_id){
		$resp = array('success' => 0, 'msg' => 'Product listing not found.');
		echo json_encode($resp);
		exit();
	}

	// Prepare firm_pay_query to deduct cash, and log_revenue_query to log expense
	$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES (:firm_id, :is_debit, :pid, :pidn, :pidq, :cost, :source, NOW())");

	// Get firm info
	$sql = "SELECT name, cash, networth FROM firms WHERE id = $eos_firm_id";	
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];
	$firm_networth = $firm['networth'];

	// Get listing info
	$sql = "SELECT market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.pidcost, market_prod.price, market_prod.listed, list_prod.name, list_prod.value, list_prod.cat_id, list_cat.price_multiplier FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE market_prod.id = '$b2b_id'";
	$listing = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($listing)){
		$resp = array('success' => 0, 'msg' => 'Purchase failed. All units have been sold out.');
		echo json_encode($resp);
		exit();
	}
	
	$listing_fid = $listing['fid'];
	$listing_pid = $listing['pid'];
	$listing_pidq = $listing['pidq'];
	$listing_pidn = $listing['pidn'];
	$listing_pidcost = $listing['pidcost'];
	$listing_price = $listing['price'];
	$listing_cat_id = $listing['cat_id'];
	$prod_name = $listing['name'];
	$prod_value = $listing['value'];
	$prod_price_multiplier = $listing['price_multiplier'];

	if($listing_pidn < $buy_num){
		if(!$force_buy){
			$resp = array('success' => 1, 'confNeeded' => 1, 'confMsg' => 'Only '.$listing_pidn.' units of '.$prod_name.'(s) remain, would you like to purchase all that is remaining?');
			echo json_encode($resp);
			exit();
		}else{
			$buy_num = $listing_pidn;
		}
	}
	$total_price = $buy_num * $listing_price;
	if($listing_fid == $eos_firm_id){
		$fee = round(0.002 * $total_price);
		if($ctrl_leftover_allowance < $fee){
			$resp = array('success' => 0, 'msg' => 'Removal failed. Daily spending limit reached.');
			echo json_encode($resp);
			exit();
		}
		$total_receipt = round(0.998 * $total_price);
		// Execute firm_pay_query and check success
		$result = $firm_pay_query->execute(array(':cost' => $fee, ':firm_id' => $eos_firm_id));
		$affected = $firm_pay_query->rowCount();
	}else{
		if($ctrl_leftover_allowance < $total_price){
			$resp = array('success' => 0, 'msg' => 'Purchase failed. Daily spending limit reached.');
			echo json_encode($resp);
			exit();
		}
		$total_receipt = round(0.95 * $total_price);
		// Execute firm_pay_query and check success
		$result = $firm_pay_query->execute(array(':cost' => $total_price, ':firm_id' => $eos_firm_id));
		$affected = $firm_pay_query->rowCount();
	}
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Purchase failed. Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}
	
	// Immediately update market
	if($listing_pidn == $buy_num){
		$sql = "DELETE FROM market_prod WHERE id = '$b2b_id'";
		$db->query($sql);
	}else{
		$listing_pidn_leftover = $listing_pidn - $buy_num;
		$sql = "UPDATE market_prod SET pidn = $listing_pidn_leftover WHERE id = '$b2b_id'";
		$db->query($sql);
	}
	
	// Log purchase and sales, add news
	$unit = ' units';
	if($buy_num == 1) $unit = ' unit';
	if($listing_fid == $eos_firm_id){
		$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':pid' => $listing_pid, ':pidn' => $buy_num, ':pidq' => $listing_pidq, ':cost' => $fee, ':source' => 'B2B Purchase'));
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $fee WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);
		$news = 'Your company paid $'.number_format($fee/100,2,'.',',').' in B2B fees to remove '.$buy_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> from the market.';
		$success_msg = $buy_num.$unit.' of '.strtolower($prod_name).'(s) removed, $'.number_format($fee/100, 2, '.', ',').' paid in B2B fees.';
	}else{
		$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':pid' => $listing_pid, ':pidn' => $buy_num, ':pidq' => $listing_pidq, ':cost' => $total_price, ':source' => 'B2B Purchase'));
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $total_price WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);
		$news = '<a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> bought '.$buy_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> from your company for a total of $'.number_format($total_receipt/100,2,'.',',').' after commission.';
		$success_msg = $buy_num.$unit.' of '.strtolower($prod_name).'(s) bought for $'.number_format($total_price/100, 2, '.', ',').' total.';

		// Special hack - if seller is foreign company
		if($listing_fid > 50 && $listing_fid < 70){
			$sql = "UPDATE foreign_list_goods SET value_sold = value_sold + $total_price WHERE fcid = $listing_fid AND cat_id = $listing_cat_id";
			$db->query($sql);
		}else{
			// Pay seller and log receipt
			$sql = "UPDATE firms SET cash = cash + $total_receipt WHERE id = $listing_fid";
			$db->query($sql);
			$log_revenue_query->execute(array(':firm_id' => $listing_fid, ':is_debit' => 0, ':pid' => $listing_pid, ':pidn' => $buy_num, ':pidq' => $listing_pidq, ':cost' => $total_receipt, ':source' => 'B2B Sales'));
		}
	}
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:firm_id, :news, NOW())");
	$query->execute(array(':firm_id' => $listing_fid, ':news' => $news));

	// Add to market log
	$price_to_value = $listing_price / $prod_value;
	$total_value = $prod_value * $buy_num;
	if($listing_fid == $eos_firm_id){
		$hide = 1;
	}else{
		$threadhold1 = 0.05 * $firm_networth;
		$threadhold2 = 0.01 * $firm_networth;
		if($price_to_value < (10 * $prod_price_multiplier) && $price_to_value > 0.1){
			$hide = 1;
		}else if($price_to_value < 0.0001 || $price_to_value > 10000){
			$hide = 0;
		}else if($total_price < $threadhold1 && $total_value < $threadhold1){
			$hide = 1;
		}else{
			$hide = 0;
		}
		if($total_price < $threadhold2 && $total_value < $threadhold2){
			$hide = 1;
		}
	}
	$sql = "INSERT INTO log_market_prod (sfid, bfid, pid, pidq, pidn, cost, price, pricetovalue, hide, transaction_time) VALUES ('$listing_fid', $eos_firm_id, '$listing_pid', '$listing_pidq', '$buy_num', '$listing_pidcost', '$listing_price', '$price_to_value', '$hide', NOW())";
	$db->query($sql);
	
	// Update warehouse
	$sql_wh_pid = $db->prepare("SELECT COUNT(*) AS wh_count, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = :pid AND fid = :fid");
	$sql_wh_insert = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (:fid, :pid, :pidq, :pidn, :pidcost)");
	$sql_wh_update = $db->prepare("UPDATE firm_wh SET pidcost = :pidcost, pidn = :pidn, pidq = :pidq WHERE id = :id");

	// Check if pid with pidq already exists in warehouse, add already finished opid to warehouse
	$sql_wh_pid->execute(array(':pid' => $listing_pid, ':fid' => $eos_firm_id));
	$wh_opid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
	if($wh_opid1['wh_count']){
		$prod_wh_id = $wh_opid1["id"];
		$prod_wh_n = $wh_opid1["pidn"];
		$prod_wh_q = $wh_opid1["pidq"];
		$prod_wh_cost = $wh_opid1["pidcost"];
		$prod_n_new = $prod_wh_n + $buy_num;
		$prod_q_new = ($prod_wh_n * $prod_wh_q + $buy_num * $listing_pidq)/$prod_n_new;
		$prod_cost_new = round(($prod_wh_n * $prod_wh_cost + $buy_num * $listing_price)/$prod_n_new);
		
		$sql_wh_update->execute(array(':id' => $prod_wh_id, ':pidq' => $prod_q_new, ':pidn' => $prod_n_new, ':pidcost' => $prod_cost_new));
	}else{
		$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $listing_pid, ':pidq' => $listing_pidq, ':pidn' => $buy_num, ':pidcost' => $listing_price));
	}

	$resp = array('success' => 1, 'msg' => $success_msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'buy_all'){
	if(!$ctrl_b2b_buy){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$b2b_id){
		$resp = array('success' => 0, 'msg' => 'Product listing not found.');
		echo json_encode($resp);
		exit();
	}

	// Prepare firm_pay_query to deduct cash, and log_revenue_query to log expense
	$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES (:firm_id, :is_debit, :pid, :pidn, :pidq, :cost, :source, NOW())");

	// Get firm info
	$sql = "SELECT name, cash, networth FROM firms WHERE id = $eos_firm_id";	
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];
	$firm_networth = $firm['networth'];

	// Get listing info
	$sql = "SELECT market_prod.fid, market_prod.pid, market_prod.pidq, market_prod.pidn, market_prod.pidcost, market_prod.price, market_prod.listed, list_prod.name, list_prod.value, list_prod.cat_id, list_cat.price_multiplier FROM market_prod LEFT JOIN list_prod ON market_prod.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE market_prod.id = '$b2b_id'";
	$listing = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($listing)){
		$resp = array('success' => 0, 'msg' => 'Purchase failed. All units have been sold out.');
		echo json_encode($resp);
		exit();
	}
	
	$listing_fid = $listing['fid'];
	$listing_pid = $listing['pid'];
	$listing_pidq = $listing['pidq'];
	$listing_pidn = $listing['pidn'];
	$listing_pidcost = $listing['pidcost'];
	$listing_price = $listing['price'];
	$listing_cat_id = $listing['cat_id'];
	$prod_name = $listing['name'];
	$prod_value = $listing['value'];
	$prod_price_multiplier = $listing['price_multiplier'];

	$buy_num = $listing_pidn;$total_price = $buy_num * $listing_price;
	if($listing_fid == $eos_firm_id){
		$fee = round(0.002 * $total_price);
		if($ctrl_leftover_allowance < $fee){
			$resp = array('success' => 0, 'msg' => 'Removal failed. Daily spending limit reached.');
			echo json_encode($resp);
			exit();
		}
		$total_receipt = round(0.998 * $total_price);
		// Execute firm_pay_query and check success
		$result = $firm_pay_query->execute(array(':cost' => $fee, ':firm_id' => $eos_firm_id));
		$affected = $firm_pay_query->rowCount();
	}else{
		if($ctrl_leftover_allowance < $total_price){
			$resp = array('success' => 0, 'msg' => 'Purchase failed. Daily spending limit reached.');
			echo json_encode($resp);
			exit();
		}
		$total_receipt = round(0.95 * $total_price);
		// Execute firm_pay_query and check success
		$result = $firm_pay_query->execute(array(':cost' => $total_price, ':firm_id' => $eos_firm_id));
		$affected = $firm_pay_query->rowCount();
	}
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Purchase failed. Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}

	// Immediately update market
	$sql = "DELETE FROM market_prod WHERE id = '$b2b_id'";
	$db->query($sql);

	// Log purchase and sales, add news
	$unit = ' units';
	if($buy_num == 1) $unit = ' unit';
	if($listing_fid == $eos_firm_id){
		$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':pid' => $listing_pid, ':pidn' => $buy_num, ':pidq' => $listing_pidq, ':cost' => $fee, ':source' => 'B2B Purchase'));
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $fee WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);
		$news = 'Your company paid $'.number_format($fee/100,2,'.',',').' in B2B fees to remove '.$buy_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> from the market.';
		$success_msg = $buy_num.$unit.' of '.strtolower($prod_name).'(s) removed, $'.number_format($fee/100, 2, '.', ',').' paid in B2B fees.';
	}else{
		$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':pid' => $listing_pid, ':pidn' => $buy_num, ':pidq' => $listing_pidq, ':cost' => $total_price, ':source' => 'B2B Purchase'));
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $total_price WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);
		$news = '<a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> bought '.$buy_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> from your company for a total of $'.number_format($total_receipt/100,2,'.',',').' after commission.';
		$success_msg = $buy_num.$unit.' of '.strtolower($prod_name).'(s) bought for $'.number_format($total_price/100, 2, '.', ',').' total.';

		// Special hack - if seller is foreign company
		if($listing_fid > 50 && $listing_fid < 70){
			$sql = "UPDATE foreign_list_goods SET value_sold = value_sold + $total_price WHERE fcid = $listing_fid AND cat_id = $listing_cat_id";
			$db->query($sql);
		}else{
			// Pay seller and log receipt
			$sql = "UPDATE firms SET cash = cash + $total_receipt WHERE id = $listing_fid";
			$db->query($sql);
			$log_revenue_query->execute(array(':firm_id' => $listing_fid, ':is_debit' => 0, ':pid' => $listing_pid, ':pidn' => $buy_num, ':pidq' => $listing_pidq, ':cost' => $total_receipt, ':source' => 'B2B Sales'));
		}
	}
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:firm_id, :news, NOW())");
	$query->execute(array(':firm_id' => $listing_fid, ':news' => $news));

	// Add to market log
	$price_to_value = $listing_price / $prod_value;
	$total_value = $prod_value * $buy_num;
	if($listing_fid == $eos_firm_id){
		$hide = 1;
	}else{
		$threadhold1 = 0.05 * $firm_networth;
		$threadhold2 = 0.01 * $firm_networth;
		if($price_to_value < (10 * $prod_price_multiplier) && $price_to_value > 0.1){
			$hide = 1;
		}else if($price_to_value < 0.0001 || $price_to_value > 10000){
			$hide = 0;
		}else if($total_price < $threadhold1 && $total_value < $threadhold1){
			$hide = 1;
		}else{
			$hide = 0;
		}
		if($total_price < $threadhold2 && $total_value < $threadhold2){
			$hide = 1;
		}
	}
	$sql = "INSERT INTO log_market_prod (sfid, bfid, pid, pidq, pidn, cost, price, pricetovalue, hide, transaction_time) VALUES ('$listing_fid', $eos_firm_id, '$listing_pid', '$listing_pidq', '$buy_num', '$listing_pidcost', '$listing_price', '$price_to_value', '$hide', NOW())";
	$db->query($sql);
	
	// Update warehouse
	$sql_wh_pid = $db->prepare("SELECT COUNT(*) AS wh_count, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = :pid AND fid = :fid");
	$sql_wh_insert = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (:fid, :pid, :pidq, :pidn, :pidcost)");
	$sql_wh_update = $db->prepare("UPDATE firm_wh SET pidcost = :pidcost, pidn = :pidn, pidq = :pidq WHERE id = :id");

	// Check if pid with pidq already exists in warehouse, add already finished opid to warehouse
	$sql_wh_pid->execute(array(':pid' => $listing_pid, ':fid' => $eos_firm_id));
	$wh_opid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
	if($wh_opid1['wh_count']){
		$prod_wh_id = $wh_opid1["id"];
		$prod_wh_n = $wh_opid1["pidn"];
		$prod_wh_q = $wh_opid1["pidq"];
		$prod_wh_cost = $wh_opid1["pidcost"];
		$prod_n_new = $prod_wh_n + $buy_num;
		$prod_q_new = ($prod_wh_n * $prod_wh_q + $buy_num * $listing_pidq)/$prod_n_new;
		$prod_cost_new = round(($prod_wh_n * $prod_wh_cost + $buy_num * $listing_price)/$prod_n_new);
		
		$sql_wh_update->execute(array(':id' => $prod_wh_id, ':pidq' => $prod_q_new, ':pidn' => $prod_n_new, ':pidcost' => $prod_cost_new));
	}else{
		$sql_wh_insert->execute(array(':fid' => $eos_firm_id, ':pid' => $listing_pid, ':pidq' => $listing_pidq, ':pidn' => $buy_num, ':pidcost' => $listing_price));
	}

	$resp = array('success' => 1, 'msg' => $success_msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'sell'){
	if(!$ctrl_wh_sell){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);
	$sell_num = filter_var($_POST['sell_num'], FILTER_SANITIZE_NUMBER_INT);

	if($sell_num < 1){
		$resp = array('success' => 0, 'msg' => 'Please input a valid quantity.');
		echo json_encode($resp);
		exit();
	}
	if(!$b2b_id){
		$resp = array('success' => 0, 'msg' => 'Product request not found.');
		echo json_encode($resp);
		exit();
	}

	// Prepare firm_pay_query to deduct cash, and log_revenue_query to log expense
	$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES (:firm_id, :is_debit, :pid, :pidn, :pidq, :cost, :source, NOW())");

	// Get firm info
	$sql = "SELECT name, cash, networth FROM firms WHERE id = $eos_firm_id";	
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];
	$firm_networth = $firm['networth'];

	// Get listing info
	$sql = "SELECT market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.value, list_prod.cat_id, list_cat.price_multiplier FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE market_requests.id = '$b2b_id'";
	$listing = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($listing)){
		$resp = array('success' => 0, 'msg' => 'Transaction failed. The request is already fulfilled or has been canceled.');
		echo json_encode($resp);
		exit();
	}
	
	$request_fid = $listing['fid'];
	$request_pid = $listing['pid'];
	$request_pidq = $listing['pidq'];
	$request_pidn = $listing['pidn'];
	$request_price = $listing['price'];
	$request_aon = $listing['aon'];
	$request_cat_id = $listing['cat_id'];
	$prod_name = $listing['name'];
	$prod_value = $listing['value'];
	$prod_price_multiplier = $listing['price_multiplier'];

	if($request_aon && $request_pidn > $sell_num){
		$resp = array('success' => 0, 'msg' => 'Transaction failed. This is an All Or None request.');
		echo json_encode($resp);
		exit();
	}

	if($request_pidn != -1 && $request_pidn < $sell_num){
		$sell_num = $request_pidn;
	}
	$total_price = $sell_num * $request_price;
	$total_receipt = round(0.95 * $total_price);

	// Execute warehouse deduction and check success
	$sql_wh_pid = $db->prepare("SELECT COUNT(*) AS wh_count, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = :pid AND pidn >= :pidn AND pidq >= :pidq AND fid = :fid");
	$sql_wh_insert = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (:fid, :pid, :pidq, :pidn, :pidcost)");
	$sql_wh_update = $db->prepare("UPDATE firm_wh SET pidcost = :pidcost, pidn = :pidn, pidq = :pidq WHERE id = :id");
	$sql_wh_update_pidn = $db->prepare("UPDATE firm_wh SET pidn = :pidn WHERE id = :id");
	$sql_wh_delete = $db->prepare("DELETE FROM firm_wh WHERE id = :id");

	$sql_wh_pid->execute(array(':pid' => $request_pid, ':pidn' => $sell_num, ':pidq' => $request_pidq, ':fid' => $eos_firm_id));
	$wh_item = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
	if(!$wh_item['wh_count']){
		$resp = array('success' => 0, 'msg' => 'Transaction failed. Product is not in warehouse or is not found in sufficient quantity.');
		echo json_encode($resp);
		exit();
	}
	$wh_pidn_leftover = $wh_item['pidn'] - $sell_num;
	$wh_pidq = $wh_item['pidq'];
	$wh_pidcost = $wh_item['pidcost'];
	
	// Special hack - if buyer is foreign company
	if($request_fid > 50 && $request_fid < 70){
		$sql = "UPDATE foreign_list_purcs SET value_bought = value_bought + $total_price WHERE fcid = $request_fid AND cat_id = $request_cat_id";
		$db->query($sql);
	}else{
		// Execute firm_pay_query and check success
		$result = $firm_pay_query->execute(array(':cost' => $total_price, ':firm_id' => $request_fid));
		$affected = $firm_pay_query->rowCount();
		if(!$result || !$affected){
			// Requester does not have enough money, so cancel request
			$sql = "DELETE FROM market_requests WHERE id = '$b2b_id'";
			$db->query($sql);
			$news = 'Your product request for <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> was canceled due to lack of cash when <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> offered to sell the product.';
			$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (SELECT ?, ?, NOW())");
			$query->execute(array($request_fid, $news));
			
			$resp = array('success' => 0, 'msg' => 'Transaction failed. Requesting company does not have enough cash.');
			echo json_encode($resp);
			exit();
		}
	}

	// Immediately update market
	if($request_pidn != -1){
		if($request_pidn == $sell_num){
			$sql = "DELETE FROM market_requests WHERE id = '$b2b_id'";
			$db->query($sql);
		}else{
			$request_pidn_leftover = $request_pidn - $sell_num;
			$sql = "UPDATE market_requests SET pidn = $request_pidn_leftover WHERE id = '$b2b_id'";
			$db->query($sql);
		}
	}

	// Then update warehouse
	if(!$wh_pidn_leftover){
		$sql_wh_delete->execute(array(':id' => $wh_item['id']));
	}else{
		$sql_wh_update_pidn->execute(array(':id' => $wh_item['id'], ':pidn' => $wh_pidn_leftover));
	}

	// Log purchase
	$log_revenue_query->execute(array(':firm_id' => $request_fid, ':is_debit' => 1, ':pid' => $request_pid, ':pidn' => $sell_num, ':pidq' => $wh_pidq, ':cost' => $total_price, ':source' => 'B2B Purchase'));

	// Pay seller and log receipt
	$sql = "UPDATE firms SET cash = cash + $total_receipt WHERE id = $eos_firm_id";
	$db->query($sql);
	$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 0, ':pid' => $request_pid, ':pidn' => $sell_num, ':pidq' => $wh_pidq, ':cost' => $total_receipt, ':source' => 'B2B Sales'));

	// Add news
	$unit = ' units';
	if($sell_num == 1) $unit = ' unit';
	$news = '<a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> sold '.$sell_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> to your company for a total of $'.number_format($total_price/100,2,'.',',').'.';
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:firm_id, :news, NOW())");
	$query->execute(array(':firm_id' => $request_fid, ':news' => $news));

	// Add to market log
	$price_to_value = $request_price / $prod_value;
	$total_value = $prod_value * $sell_num;
	if($request_fid == $eos_firm_id){
		$hide = 1;
	}else{
		$threadhold1 = 0.05 * $firm_networth;
		$threadhold2 = 0.01 * $firm_networth;
		if($price_to_value < (10 * $prod_price_multiplier) && $price_to_value > 0.1){
			$hide = 1;
		}else if($price_to_value < 0.0001 || $price_to_value > 10000){
			$hide = 0;
		}else if($total_price < $threadhold1 && $total_value < $threadhold1){
			$hide = 1;
		}else{
			$hide = 0;
		}
		if($total_price < $threadhold2 && $total_value < $threadhold2){
			$hide = 1;
		}
	}
	$sql = "INSERT INTO log_market_prod (sfid, bfid, pid, pidq, pidn, cost, price, pricetovalue, hide, transaction_time) VALUES ('$eos_firm_id', $request_fid, '$request_pid', '$wh_pidq', '$sell_num', '$wh_pidcost', '$request_price', '$price_to_value', '$hide', NOW())";
	$db->query($sql);

	// Check if pid with pidq already exists in warehouse, add already finished opid to warehouse
	$sql_wh_pid->execute(array(':pid' => $request_pid, ':pidn' => 0, ':pidq' => 0, ':fid' => $request_fid));
	$wh_opid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
	if($wh_opid1['wh_count']){
		$prod_wh_id = $wh_opid1["id"];
		$prod_wh_n = $wh_opid1["pidn"];
		$prod_wh_q = $wh_opid1["pidq"];
		$prod_wh_cost = $wh_opid1["pidcost"];
		$prod_n_new = $prod_wh_n + $sell_num;
		$prod_q_new = ($prod_wh_n * $prod_wh_q + $sell_num * $wh_pidq)/$prod_n_new;
		$prod_cost_new = round(($prod_wh_n * $prod_wh_cost + $sell_num * $request_price)/$prod_n_new);
		
		$sql_wh_update->execute(array(':id' => $prod_wh_id, ':pidq' => $prod_q_new, ':pidn' => $prod_n_new, ':pidcost' => $prod_cost_new));
	}else{
		$sql_wh_insert->execute(array(':fid' => $request_fid, ':pid' => $request_pid, ':pidq' => $wh_pidq, ':pidn' => $sell_num, ':pidcost' => $request_price));
	}
	
	
	$unit = ' units';
	if($sell_num == 1) $unit = ' unit';
	$resp = array('success' => 1, 'msg' => $sell_num.$unit.' of '.strtolower($prod_name).'(s) sold for $'.number_format($total_price/100, 2, '.', ',').' total.');
	echo json_encode($resp);
	exit();
}
else if($action == 'sell_all'){
	if(!$ctrl_wh_sell){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$b2b_id){
		$resp = array('success' => 0, 'msg' => 'Product request not found.');
		echo json_encode($resp);
		exit();
	}

	// Prepare firm_pay_query to deduct cash, and log_revenue_query to log expense
	$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, pid, pidn, pidq, value, source, transaction_time) VALUES (:firm_id, :is_debit, :pid, :pidn, :pidq, :cost, :source, NOW())");

	// Get firm info
	$sql = "SELECT name, cash, networth FROM firms WHERE id = $eos_firm_id";	
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];
	$firm_networth = $firm['networth'];

	// Get listing info
	$sql = "SELECT market_requests.fid, market_requests.pid, market_requests.pidq, market_requests.pidn, market_requests.price, market_requests.aon, market_requests.requested, list_prod.name, list_prod.value, list_prod.cat_id, list_cat.price_multiplier FROM market_requests LEFT JOIN list_prod ON market_requests.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE market_requests.id = '$b2b_id'";
	$listing = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($listing)){
		$resp = array('success' => 0, 'msg' => 'Transaction failed. The request is already fulfilled or has been canceled.');
		echo json_encode($resp);
		exit();
	}
	
	$request_fid = $listing['fid'];
	$request_pid = $listing['pid'];
	$request_pidq = $listing['pidq'];
	$request_pidn = $listing['pidn'];
	$request_price = $listing['price'];
	$request_aon = $listing['aon'];
	$request_cat_id = $listing['cat_id'];
	$prod_name = $listing['name'];
	$prod_value = $listing['value'];
	$prod_price_multiplier = $listing['price_multiplier'];

	$sell_num = $request_pidn;
	if($request_pidn == -1) $sell_num = 1;
	$total_price = $sell_num * $request_price;
	$total_receipt = round(0.95 * $total_price);
	
	// Execute warehouse deduction and check success
	$sql_wh_pid = $db->prepare("SELECT COUNT(*) AS wh_count, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = :pid AND pidn >= :pidn AND pidq >= :pidq AND fid = :fid");
	$sql_wh_insert = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (:fid, :pid, :pidq, :pidn, :pidcost)");
	$sql_wh_update = $db->prepare("UPDATE firm_wh SET pidcost = :pidcost, pidn = :pidn, pidq = :pidq WHERE id = :id");
	$sql_wh_update_pidn = $db->prepare("UPDATE firm_wh SET pidn = :pidn WHERE id = :id");
	$sql_wh_delete = $db->prepare("DELETE FROM firm_wh WHERE id = :id");

	$sql_wh_pid->execute(array(':pid' => $request_pid, ':pidn' => 1, ':pidq' => $request_pidq, ':fid' => $eos_firm_id));
	$wh_item = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
	if(!$wh_item['wh_count']){
		$resp = array('success' => 0, 'msg' => 'Transaction failed. Product is not in warehouse or is not found in sufficient quantity.');
		echo json_encode($resp);
		exit();
	}
	if($request_pidn == -1 || $request_pidn > $wh_item['pidn']){
		if($request_aon){
			$resp = array('success' => 0, 'msg' => 'Transaction failed. This is an All Or None request.');
			echo json_encode($resp);
			exit();
		}
		$sell_num = $wh_item['pidn'];
		$total_price = $sell_num * $request_price;
		$total_receipt = round(0.95 * $total_price);
	}
	$wh_pidn_leftover = $wh_item['pidn'] - $sell_num;
	$wh_pidq = $wh_item['pidq'];
	$wh_pidcost = $wh_item['pidcost'];
	
	// Special hack - if buyer is foreign company
	if($request_fid > 50 && $request_fid < 70){
		$sql = "UPDATE foreign_list_purcs SET value_bought = value_bought + $total_price WHERE fcid = $request_fid AND cat_id = $request_cat_id";
		$db->query($sql);
	}else{
		// Execute firm_pay_query and check success
		$result = $firm_pay_query->execute(array(':cost' => $total_price, ':firm_id' => $request_fid));
		$affected = $firm_pay_query->rowCount();
		if(!$result || !$affected){
			// Requester does not have enough money, so cancel request
			$sql = "DELETE FROM market_requests WHERE id = '$b2b_id'";
			$db->query($sql);
			$news = 'Your product request for <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> was canceled due to lack of cash when <a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> offered to sell the product.';
			$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (SELECT ?, ?, NOW())");
			$query->execute(array($request_fid, $news));
			
			$resp = array('success' => 0, 'msg' => 'Transaction failed. Requesting company does not have enough cash.');
			echo json_encode($resp);
			exit();
		}
	}

	// Immediately update market
	if($request_pidn != -1){
		if($request_pidn == $sell_num){
			$sql = "DELETE FROM market_requests WHERE id = '$b2b_id'";
			$db->query($sql);
		}else{
			$request_pidn_leftover = $request_pidn - $sell_num;
			$sql = "UPDATE market_requests SET pidn = $request_pidn_leftover WHERE id = '$b2b_id'";
			$db->query($sql);
		}
	}

	// Then update warehouse
	if(!$wh_pidn_leftover){
		$sql_wh_delete->execute(array(':id' => $wh_item['id']));
	}else{
		$sql_wh_update_pidn->execute(array(':id' => $wh_item['id'], ':pidn' => $wh_pidn_leftover));
	}

	// Log purchase
	$log_revenue_query->execute(array(':firm_id' => $request_fid, ':is_debit' => 1, ':pid' => $request_pid, ':pidn' => $sell_num, ':pidq' => $wh_pidq, ':cost' => $total_price, ':source' => 'B2B Purchase'));

	// Pay seller and log receipt
	$sql = "UPDATE firms SET cash = cash + $total_receipt WHERE id = $eos_firm_id";
	$db->query($sql);
	$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 0, ':pid' => $request_pid, ':pidn' => $sell_num, ':pidq' => $wh_pidq, ':cost' => $total_receipt, ':source' => 'B2B Sales'));

	// Add news
	$unit = ' units';
	if($sell_num == 1) $unit = ' unit';
	$news = '<a href="/eos/firm/'.$eos_firm_id.'">'.$firm_name.'</a> sold '.$sell_num.$unit.' of <img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name)).'.gif" alt="'.$prod_name.'" title="'.$prod_name.'" /> to your company for a total of $'.number_format($total_price/100,2,'.',',').'.';
	$query = $db->prepare("INSERT INTO firm_news (fid, body, date_created) VALUES (:firm_id, :news, NOW())");
	$query->execute(array(':firm_id' => $request_fid, ':news' => $news));

	// Add to market log
	$price_to_value = $request_price / $prod_value;
	$total_value = $prod_value * $sell_num;
	if($request_fid == $eos_firm_id){
		$hide = 1;
	}else{
		$threadhold1 = 0.05 * $firm_networth;
		$threadhold2 = 0.01 * $firm_networth;
		if($price_to_value < (10 * $prod_price_multiplier) && $price_to_value > 0.1){
			$hide = 1;
		}else if($price_to_value < 0.0001 || $price_to_value > 10000){
			$hide = 0;
		}else if($total_price < $threadhold1 && $total_value < $threadhold1){
			$hide = 1;
		}else{
			$hide = 0;
		}
		if($total_price < $threadhold2 && $total_value < $threadhold2){
			$hide = 1;
		}
	}
	$sql = "INSERT INTO log_market_prod (sfid, bfid, pid, pidq, pidn, cost, price, pricetovalue, hide, transaction_time) VALUES ('$eos_firm_id', $request_fid, '$request_pid', '$wh_pidq', '$sell_num', '$wh_pidcost', '$request_price', '$price_to_value', '$hide', NOW())";
	$db->query($sql);

	// Check if pid with pidq already exists in warehouse, add already finished opid to warehouse
	$sql_wh_pid->execute(array(':pid' => $request_pid, ':pidn' => 0, ':pidq' => 0, ':fid' => $request_fid));
	$wh_opid1 = $sql_wh_pid->fetch(PDO::FETCH_ASSOC);
	if($wh_opid1['wh_count']){
		$prod_wh_id = $wh_opid1["id"];
		$prod_wh_n = $wh_opid1["pidn"];
		$prod_wh_q = $wh_opid1["pidq"];
		$prod_wh_cost = $wh_opid1["pidcost"];
		$prod_n_new = $prod_wh_n + $sell_num;
		$prod_q_new = ($prod_wh_n * $prod_wh_q + $sell_num * $wh_pidq)/$prod_n_new;
		$prod_cost_new = round(($prod_wh_n * $prod_wh_cost + $sell_num * $request_price)/$prod_n_new);
		
		$sql_wh_update->execute(array(':id' => $prod_wh_id, ':pidq' => $prod_q_new, ':pidn' => $prod_n_new, ':pidcost' => $prod_cost_new));
	}else{
		$sql_wh_insert->execute(array(':fid' => $request_fid, ':pid' => $request_pid, ':pidq' => $wh_pidq, ':pidn' => $sell_num, ':pidcost' => $request_price));
	}
	
	
	$unit = ' units';
	if($sell_num == 1) $unit = ' unit';
	$resp = array('success' => 1, 'msg' => $sell_num.$unit.' of '.strtolower($prod_name).'(s) sold for $'.number_format($total_price/100, 2, '.', ',').' total.');
	echo json_encode($resp);
	exit();
}
else if($action == 'add_request'){
	if(!$ctrl_b2b_buy){
		$resp = array('success' => 0, 'msg' => 'Sorry, but you are not authorized to make B2B purchases for this company.');
		echo json_encode($resp);
		exit();
	}

	$pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
	$pidq = filter_var($_POST['pidq'], FILTER_SANITIZE_NUMBER_INT);
	$pidn = filter_var($_POST['pidn'], FILTER_SANITIZE_NUMBER_INT);
	$price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);
	$aon = filter_var($_POST['aon'], FILTER_SANITIZE_NUMBER_INT);

	if($pid < 1){
		$resp = array('success' => 0, 'msg' => 'Please select a product!');
		echo json_encode($resp);
		exit();
	}
	if($pidn < 1 && $pidn != -1){
		$resp = array('success' => 0, 'msg' => 'Add request failed. Please input a valid quantity.');
		echo json_encode($resp);
		exit();
	}
	if($pidn > 999999999999999){
		$pidn = 999999999999999;
	}
	if($price < 1){
		$resp = array('success' => 0, 'msg' => 'Add request failed. Please input a valid price.');
		echo json_encode($resp);
		exit();
	}
	if($pidn * $price > 9999999999999999999){
		$resp = array('success' => 0, 'msg' => 'Sorry boss, our current software does not allow us to input such a huge request. Please keep total price under $10 Q by lowering price or quantity, or by selecting unlimited units as the quantity.');
		echo json_encode($resp);
		exit();
	}
	if($pidq < 0){
		$pidq = 0;
	}
	if($pidq > 9999){
		$pidq = 9999;
	}
	
	$sql = "SELECT name FROM list_prod WHERE id = '$pid'";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($prod)){
		$resp = array('success' => 0, 'msg' => 'Add request failed. The chosen product type does not exist.');
		echo json_encode($resp);
		exit();
	}
	
	// Restricts company to 100 requests
	$sql = "SELECT COUNT(*) AS cnt FROM market_requests WHERE fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if($count >= 100){
		$resp = array('success' => 0, 'msg' => 'Sorry, but Econosia law forbids companies from having more than 100 requests.');
		echo json_encode($resp);
		exit();
	}

	// Prepare firm_pay_query to deduct cash
	$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");

	// Execute firm_pay_query and check success
	if($ctrl_leftover_allowance < 50000){
		$resp = array('success' => 0, 'msg' => 'Add request failed. Daily spending limit reached.');
		echo json_encode($resp);
		exit();
	}
	$result = $firm_pay_query->execute(array(':cost' => 50000, ':firm_id' => $eos_firm_id));
	$affected = $firm_pay_query->rowCount();
	if(!$result || !$affected){
		$resp = array('success' => 0, 'msg' => 'Add request failed. Company does not have enough cash.');
		echo json_encode($resp);
		exit();
	}

	$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES (:firm_id, :is_debit, :cost, :source, NOW())");
	$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':cost' => 50000, ':source' => 'B2B Fee'));
	$sql = "UPDATE firms_positions SET used_allowance = used_allowance + 50000 WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$db->query($sql);

	// Insert new row
	$query = $db->prepare("INSERT INTO market_requests (fid, pid, pidq, pidn, price, aon, requested) VALUES (?, ?, ?, ?, ?, ?, NOW())");
	$result = $query->execute(array($eos_firm_id, $pid, $pidq, $pidn, $price, $aon));
	if($result){
		$resp = array('success' => 1, 'msg' => 'Successfully added purchase request for '.$pidn.' units of '.$prod['name'].' at $'.number_format($price/100, 2, '.', ',').' each.');
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'cancel_request'){
	if(!$ctrl_b2b_buy || !$ctrl_wh_sell){
		$resp = array('success' => 0, 'msg' => 'Not authorized. Canceling a request requires both permissions to buy and sell products for this company.');
		echo json_encode($resp);
		exit();
	}

	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);

	if($b2b_id){
		// Get old data
		$sql = "SELECT id FROM market_requests WHERE id = '$b2b_id' AND fid = $eos_firm_id";
		$old_entry = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($old_entry)){
			$resp = array('success' => 0, 'msg' => 'Failed to cancel request. The listing cannot be found or all products have been sold.');
			echo json_encode($resp);
			exit();
		}

		// Delete old row
		$sql = "DELETE FROM market_requests WHERE id = '$b2b_id'";
		$db->query($sql);

		$resp = array('success' => 1, 'msg' => 'Request successfully canceled.');
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'relist'){
	if(!$ctrl_b2b_buy || !$ctrl_wh_sell){
		$resp = array('success' => 0, 'msg' => 'Not authorized. Re-listing a product requires permissions to both buy and sell products for this company.');
		echo json_encode($resp);
		exit();
	}

	$b2b_id = filter_var($_POST['b2b_id'], FILTER_SANITIZE_NUMBER_INT);
	$new_price = filter_var($_POST['new_price'], FILTER_SANITIZE_NUMBER_INT);
	
	if($new_price < 1){
		$resp = array('success' => 0, 'msg' => 'Re-listing failed. Please input a valid price.');
		echo json_encode($resp);
		exit();
	}

	if($b2b_id){
		// Prepare firm_pay_query to deduct cash
		$firm_pay_query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");

		// Get old data
		$sql = "SELECT fid, pid, pidq, pidn, pidcost, price FROM market_prod WHERE id = '$b2b_id' AND fid = $eos_firm_id";
		$old_entry = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($old_entry)){
			$resp = array('success' => 0, 'msg' => 'Re-listing failed. The listing cannot be found or all products have been sold.');
			echo json_encode($resp);
			exit();
		}
		$fee = ceil(0.002 * $old_entry['pidn'] * $old_entry['price']);

		// Get product value
		$sql = "SELECT value FROM list_prod WHERE id = ".$old_entry['pid'];
		$pid_value = $db->query($sql)->fetchColumn();

		if($new_price < (0.5 * min($old_entry['pidcost'], $pid_value))){
			$resp = array('success' => 0, 'msg' => 'Dear Sir or Madam, <br /><br />Please understand that our company is funded by sales commissions, <br />and a price this low isn\'t good for OUR business.<br /><br />Econosia B2B Company');
			echo json_encode($resp);
			exit();
		}
		if($new_price > ((100 + $old_entry['pidq']) * $pid_value)){
			$resp = array('success' => 0, 'msg' => 'Dear Sir or Madam, <br /><br />We regret to inform you that your update was rejected due to its extreme pricing.<br /><br />We receive hundreds of requests each day from the government for disclosure of client data, <br />and the price you are asking for will likely put your company on one of those requests.<br /><br />Econosia B2B Company');
			echo json_encode($resp);
			exit();
		}
		
		// Execute firm_pay_query and check success
		if($ctrl_leftover_allowance < $fee){
			$resp = array('success' => 0, 'msg' => 'Re-listing failed. Daily spending limit reached.');
			echo json_encode($resp);
			exit();
		}
		$result = $firm_pay_query->execute(array(':cost' => $fee, ':firm_id' => $eos_firm_id));
		$affected = $firm_pay_query->rowCount();
		if(!$result || !$affected){
			$resp = array('success' => 0, 'msg' => 'Re-listing failed. Company does not have enough cash.');
			echo json_encode($resp);
			exit();
		}

		// Delete old row
		$sql = "DELETE FROM market_prod WHERE id = '$b2b_id'";
		$db->query($sql);

		// Log expense
		$log_revenue_query = $db->prepare("INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES (:firm_id, :is_debit, :cost, :source, NOW())");
		$log_revenue_query->execute(array(':firm_id' => $eos_firm_id, ':is_debit' => 1, ':cost' => $fee, ':source' => 'B2B Fee'));
		$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $fee WHERE fid = $eos_firm_id AND pid = $eos_player_id";
		$db->query($sql);

		// Insert new row
		$query = $db->prepare("INSERT INTO market_prod (fid, pid, pidq, pidn, pidcost, price, listed) VALUES (?, ?, ?, ?, ?, ?, NOW())");
		$result = $query->execute(array($old_entry['fid'], $old_entry['pid'], $old_entry['pidq'], $old_entry['pidn'], $old_entry['pidcost'], $new_price));
		if($result){
			$resp = array('success' => 1, 'msg' => $old_entry['pidn'].' units re-listed at $'.number_format($new_price/100, 2, '.', ',').' each. <a href="market.php?view_type=new"><input type="button" class="bigger_input" value="Go to New Listings" /></a>');
			echo json_encode($resp);
			exit();
		}else{
			$resp = array('success' => 0, 'msg' => 'DB failed.');
			echo json_encode($resp);
			exit();
		}
	}
}
?>