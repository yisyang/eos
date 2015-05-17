<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'toggle_sellable'){
	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT firm_wh.pidprice, firm_wh.no_sell, list_prod.value FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.id = '$wh_id' AND firm_wh.fid = $eos_firm_id";
	$wh_item = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	
	if(empty($wh_item) || !$ctrl_store_price){
		$resp = array('success' => 0, 'msg' => 'Unauthorized.');
		echo json_encode($resp);
		exit();
	}else{
		$no_sell_new = 1 - $wh_item["no_sell"];
		if($wh_item["pidprice"] > 0){
			$pidprice = $wh_item["pidprice"];
			$sql = "UPDATE firm_wh SET no_sell = $no_sell_new WHERE id = $wh_id AND fid = $eos_firm_id";
		}else{
			$pidprice = $wh_item["value"] * 2;
			$sql = "UPDATE firm_wh SET no_sell = $no_sell_new, pidprice = $pidprice WHERE id = $wh_id AND fid = $eos_firm_id";
		}
		$db->query($sql);
		$resp = array('success' => 1, 'selling' => (1 - $no_sell_new), 'price' => $pidprice);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'set_price'){
	$sales_price = filter_var($_POST['sales_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);

	if($sales_price < 1){
		$resp = array('success' => 0, 'msg' => 'Price too low.');
		exit();
	}
	if($sales_price > 999999999999){
		$sales_price = 999999999999;
	}
	if(!$sales_price || !$wh_id){
		$resp = array('success' => 0, 'msg' => 'Missing price or warehouse item.');
		echo json_encode($resp);
		exit();
	}
	if(!$ctrl_store_price){
		$resp = array('success' => 0, 'msg' => 'Unauthorized.');
		echo json_encode($resp);
		exit();
	}

	// Check if wh_id exists in the firm_wh
	$sql = "SELECT COUNT(*) FROM firm_wh WHERE id = '$wh_id' AND fid = $eos_firm_id";
	$count = $db->query($sql)->fetchColumn();
	if(!$count){
		$resp = array('success' => 0, 'msg' => 'Product not found in warehouse.');
		echo json_encode($resp);
		exit();
	}

	// Price it
	$sql = "UPDATE firm_wh SET pidpartialsale = LEAST((pidpartialsale * pidprice * pidprice / $sales_price / $sales_price), 1073741824), pidprice = $sales_price, no_sell = 0 WHERE id = '$wh_id' AND fid = $eos_firm_id";
	$result = $db->query($sql);
	if($result){
		$resp = array('success' => 1);
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 0, 'msg' => 'Set price failed.');
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'stock_shelf'){
	$bldg_id = filter_var($_POST['fsid'], FILTER_SANITIZE_NUMBER_INT);
	$shelf_slot = filter_var($_POST['shelf_slot'], FILTER_SANITIZE_NUMBER_INT);
	$sc_pid = filter_var($_POST['sc_pid'], FILTER_SANITIZE_NUMBER_INT);

	if(!$bldg_id || !$shelf_slot){
		$resp = array('success' => 0, 'msg' => 'Missing building or shelf.');
		echo json_encode($resp);
		exit();
	}
	if(!$ctrl_store_price || $shelf_slot > 8){
		$resp = array('success' => 0, 'msg' => 'Unauthorized.');
		echo json_encode($resp);
		exit();
	}

	// Make sure the eos user actually owns the building
	$query = $db->prepare("SELECT store_id AS bldg_type_id, store_name AS bldg_name, size, slot FROM firm_store WHERE id = ? AND fid = ?");
	$query->execute(array($bldg_id, $eos_firm_id));
	$bldg = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($bldg)){
		fbox_breakout('buildings.php');
	}else{
		$bldg_type_id = $bldg['bldg_type_id'];
		$bldg_name = $bldg['bldg_name'];
		$bldg_size = $bldg['size'];
		$bldg_slot = $bldg['slot'];
	}

	if($sc_pid){
		// Check if sc_pid exists in the firm_wh
		$sql = "SELECT id FROM firm_wh WHERE pid = '$sc_pid' AND fid = $eos_firm_id";
		$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($result)){
			$resp = array('success' => 0, 'msg' => 'Product not found in warehouse.');
			echo json_encode($resp);
			exit();
		}
		$wh_id = $result['id'];

		// Check if the store can sell this product
		$sql = "SELECT COUNT(*) FROM list_store_choices LEFT JOIN list_prod ON list_prod.cat_id = list_store_choices.cat_id WHERE list_prod.id = $sc_pid AND list_store_choices.store_id = $bldg_type_id";
		$count = $db->query($sql)->fetchColumn();
		if(!$count){
			$resp = array('success' => 0, 'msg' => 'Product is not sellable by this store.');
			echo json_encode($resp);
			exit();
		}
		
		// Check for repeat listing
		$sql = "SELECT COUNT(*) FROM firm_store_shelves WHERE fsid = $bldg_id AND wh_id = $wh_id AND shelf_slot != $shelf_slot";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			$resp = array('success' => 0, 'msg' => 'Local law forbids duplicate shelves in the same store.');
			echo json_encode($resp);
			exit();
		}

		// List the product
		$sql = "SELECT id FROM firm_store_shelves WHERE fsid = $bldg_id AND shelf_slot = $shelf_slot";
		$fss_id = $db->query($sql)->fetchColumn();
		if($fss_id){
			$sql = "UPDATE firm_store_shelves SET wh_id = $wh_id WHERE id = $fss_id";
			$result = $db->query($sql);
		}else{
			$sql = "INSERT INTO firm_store_shelves (fsid, shelf_slot, wh_id) VALUES ($bldg_id, $shelf_slot, $wh_id)";
			$result = $db->query($sql);
		}
	}else{
		// Empty the shelf
		$sql = "SELECT id FROM firm_store_shelves WHERE fsid = $bldg_id AND shelf_slot = $shelf_slot";
		$fss_id = $db->query($sql)->fetchColumn();
		if($fss_id){
			$sql = "DELETE FROM firm_store_shelves WHERE id = $fss_id";
			$result = $db->query($sql);
		}else{
			$result = 1;
		}
	}
	if($result){
		$resp = array('success' => 1);
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 0, 'msg' => 'Set price failed.');
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'increase_marketing'){
	$xfund = floor(filter_var($_POST['xfund'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + 0.01);
	$bldg_id = filter_var($_POST['fsid'], FILTER_SANITIZE_NUMBER_INT);
	$divide_marketing = filter_var($_POST['divide_marketing'], FILTER_SANITIZE_NUMBER_INT);
	if(!$bldg_id || !$ctrl_store_ad){
		$resp = array('success' => 0, 'msg' => 'Unauthorized.');
		echo json_encode($resp);
		exit();
	}

	// Initialize Firm Cash
	$sql = "SELECT name, cash FROM firms WHERE id = $eos_firm_id";	
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_cash = $firm['cash'];
	if($firm_cash < $xfund){
		$resp = array('success' => 0, 'msg' => 'Insufficient cash.');
		echo json_encode($resp);
		exit();
	}
	if($ctrl_leftover_allowance < $xfund){
		$resp = array('success' => 0, 'msg' => 'Cost exceeds your daily spending limit.');
		echo json_encode($resp);
		exit();
	}

	if(!$xfund || $xfund < 0){
		$resp = array('success' => 0, 'msg' => 'Please select an amount to spend.');
		echo json_encode($resp);
		exit();
	}

	// Make sure the eos user actually owns the building
	$query = $db->prepare("SELECT store_id AS bldg_type_id, store_name AS bldg_name, size, slot FROM firm_store WHERE id = ? AND fid = ?");
	$query->execute(array($bldg_id, $eos_firm_id));
	$bldg = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($bldg)){
		fbox_breakout('buildings.php');
	}else{
		$bldg_type_id = $bldg['bldg_type_id'];
		$bldg_name = $bldg['bldg_name'];
		$bldg_size = $bldg['size'];
		$bldg_slot = $bldg['slot'];
	}
	
	// Deduct $ from firm
	$query = $db->prepare("UPDATE firms SET cash = cash - :cost WHERE id = :firm_id AND cash >= :cost");
	$result = $query->execute(array(':cost' => $xfund, ':firm_id' => $eos_firm_id));
	$affected = $query->rowCount();
	if(!$result || !$affected){
		echo '{"success" : 0, "msg" : "Insufficient cash."}';
		exit();
	}

	if($divide_marketing){
		// Find out the number of stores of this type
		$sql = "SELECT COUNT(*) FROM firm_store WHERE fid = $eos_firm_id AND store_id = $bldg_type_id";
		$store_type_count = $db->query($sql)->fetchColumn();
		// Give marketing points
		$sql = "UPDATE firm_store SET marketing = marketing + $xfund / $store_type_count WHERE fid = $eos_firm_id AND store_id = $bldg_type_id";
		$db->query($sql);
	}else{
		// Give marketing points
		$sql = "UPDATE firm_store SET marketing = marketing + $xfund WHERE id = $bldg_id";
		$db->query($sql);
	}

	$sql = "SELECT marketing FROM firm_store WHERE id = $bldg_id";
	$marketing_new = $db->query($sql)->fetchColumn();
	
	// Write to logs
	$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ('$eos_firm_id', 1, '$xfund', 'Marketing', NOW())";
	$db->query($sql);
	$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $xfund WHERE fid = $eos_firm_id AND pid = $eos_player_id";
	$db->query($sql);

	$resp = array('success' => 1, 'marketing_new' => $marketing_new);
	echo json_encode($resp);
	exit();
}
else if($action == 'paste_to_all'){
	$bldg_id = filter_var($_POST['fsid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$bldg_id){
		$resp = array('success' => 0, 'msg' => 'Missing store parameter.');
		echo json_encode($resp);
		exit();
	}

	// Make sure the eos user actually owns the building
	$query = $db->prepare("SELECT store_id AS bldg_type_id, store_name AS bldg_name, size, slot FROM firm_store WHERE id = ? AND fid = ?");
	$query->execute(array($bldg_id, $eos_firm_id));
	$bldg = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($bldg)){
		$resp = array('success' => 0, 'msg' => 'Store not found.');
		echo json_encode($resp);
		exit();
	}else{
		$bldg_type_id = $bldg['bldg_type_id'];
	}

	// Find target stores
	$sql = "SELECT id, store_id, store_name, size, slot FROM firm_store WHERE store_id = $bldg_type_id AND fid = $eos_firm_id AND id != $bldg_id";
	$bldgs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(!count($bldgs)){
		$resp = array('success' => 0, 'msg' => 'Store not found.');
		echo json_encode($resp);
		exit();
	}else{
		foreach($bldgs as $bldg){
			// Do actions
			$target_fsid = $bldg['id'];
			$sql = "DELETE FROM firm_store_shelves WHERE fsid = $target_fsid";
			$db->query($sql);
			$sql = "INSERT INTO firm_store_shelves (fsid, shelf_slot, wh_id) SELECT $target_fsid, x.shelf_slot, x.wh_id FROM firm_store_shelves AS x WHERE x.fsid = $bldg_id";
			$db->query($sql);
		}
		$resp = array('success' => 1);
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'lazy_pricing'){
	$bldg_id = filter_var($_POST['fsid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$bldg_id || !$ctrl_store_price){
		$resp = array('success' => 0, 'msg' => 'Unauthorized.');
		echo json_encode($resp);
		exit();
	}

	// Make sure the eos user actually owns the store
	$sql = "SELECT store_id, store_name, size, slot FROM firm_store WHERE id = '$bldg_id' AND fid = '$eos_firm_id'";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		$resp = array('success' => 0, 'msg' => 'Store not found.');
		echo json_encode($resp);
		exit();
	}else{
		$store_type_id = $store['store_id'];
	}

	$sql = "UPDATE (SELECT firm_wh.id, GREATEST(2 * firm_wh.pidcost, a.prod_value * (2 + 0.04 * firm_wh.pidq)) AS new_price FROM (SELECT list_store_choices.store_id, list_prod.id AS prod_id, list_prod.value AS prod_value FROM list_store_choices LEFT JOIN list_prod ON list_store_choices.cat_id = list_prod.cat_id WHERE list_store_choices.store_id = $store_type_id) AS a LEFT JOIN firm_wh ON a.prod_id = firm_wh.pid WHERE firm_wh.fid = $eos_firm_id AND firm_wh.no_sell) AS b LEFT JOIN firm_wh ON b.id = firm_wh.id SET firm_wh.pidprice = b.new_price, firm_wh.no_sell = 0";
	$result = $db->query($sql);
	if($result){
		$resp = array('success' => 1);
		echo json_encode($resp);
		exit();
	}else{
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
}
else if($action == 'copy'){
	$fsid = filter_var($_POST['fsid'], FILTER_SANITIZE_NUMBER_INT);
	$source_fsid = filter_var($_POST['sfsid'], FILTER_SANITIZE_NUMBER_INT);
	if(!$fsid || !$source_fsid){
		$resp = array('success' => 0, 'msg' => 'Missing store parameter.');
		echo json_encode($resp);
		exit();
	}

	// Make sure the eos user actually owns the store
	$sql = "SELECT store_id, store_name, size, slot FROM firm_store WHERE id = '$fsid' AND fid = '$eos_firm_id'";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		$resp = array('success' => 0, 'msg' => 'Store not found.');
		echo json_encode($resp);
		exit();
	}else{
		$store_type_id = $store['store_id'];
	}

	// and the source store
	$sql = "SELECT store_id, store_name, size, slot FROM firm_store WHERE id = '$source_fsid' AND fid = '$eos_firm_id'";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		$resp = array('success' => 0, 'msg' => 'Store not found.');
		echo json_encode($resp);
		exit();
	}else{
		$source_store_type_id = $store['store_id'];
	}

	if($source_store_type_id != $store_type_id){
		$resp = array('success' => 0, 'msg' => 'Invalid store type.');
		echo json_encode($resp);
		exit();
	}

	if($source_fsid != $fsid){
		// Do actions
		$sql = "DELETE FROM firm_store_shelves WHERE fsid = $fsid";
		$db->query($sql);
		$sql = "INSERT INTO firm_store_shelves (fsid, shelf_slot, wh_id) SELECT $fsid, x.shelf_slot, x.wh_id FROM firm_store_shelves AS x WHERE x.fsid = $source_fsid";
		$db->query($sql);
	}
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
?>