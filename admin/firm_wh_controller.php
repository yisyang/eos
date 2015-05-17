<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(isset($_POST['wh_id'])){
	$wh_id = filter_var($_POST['wh_id'], FILTER_SANITIZE_NUMBER_INT);
}

function handleCallBack($db_success){
	if($db_success){
		echo '{"success" : 1}';
		exit();
	}else{
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}
}

if($action == 'add_wh'){
	$firm_id = filter_var($_POST['firm_id'], FILTER_SANITIZE_NUMBER_INT);
	$pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
	$pidq = filter_var($_POST['pidq'], FILTER_SANITIZE_NUMBER_INT);
	$pidn = filter_var($_POST['pidn'], FILTER_SANITIZE_NUMBER_INT);
	if(!$pidq){
		$pidq = 0;
	}
	if(!$pidn){
		$pidn = 1;
	}
	
	//Check if pid with pidq already exists in warehouse
	$query = $db->prepare("SELECT id, pidn, pidq, pidcost FROM firm_wh WHERE pid = ? AND fid = ?");
	$query->execute(array($pid, $firm_id));
	$wh_item = $query->fetch();
	if(!empty($wh_item)){
		//Update warehouse
		$list_fact_pc_opid1_n_new = $wh_item["pidn"] + $pidn;
		$list_fact_pc_opid1_q_new = ($wh_item["pidn"] * $wh_item["pidq"] + $pidn * $pidq)/$list_fact_pc_opid1_n_new;
		$list_fact_pc_opid1_cost_new = round(($wh_item["pidn"] * $wh_item["pidcost"])/$list_fact_pc_opid1_n_new);
		$query = $db->prepare("UPDATE firm_wh SET pidcost = ?, pidn = ?, pidq = ? WHERE id = ?");
		$result = $query->execute(array($list_fact_pc_opid1_cost_new, $list_fact_pc_opid1_n_new, $list_fact_pc_opid1_q_new, $wh_item["id"]));
	}else{
		//Insert into warehouse
		$query = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (?, ?, ?, ?, ?)");
		$result = $query->execute(array($firm_id, $pid, $pidq, $pidn, 0));
	}
	
	handleCallBack($result);
}
else if($action == 'delete_wh'){
	$firm_id = filter_var($_POST['firm_id'], FILTER_SANITIZE_NUMBER_INT);
	$query = $db->prepare("DELETE FROM firm_wh WHERE id = ? AND fid = ?");
	$result = $query->execute(array($wh_id, $firm_id));
	
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize Products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$list_prod = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM firm_wh WHERE id = $wh_id";
	$wh_item = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($wh_item)){
		echo '{"success" : 0, "msg" : "Warehouse item not found."}';
		exit();
	}
	
	$current_pid = $wh_item["pid"];

	$msg = '<td>';
	$msg .= '<select id="firm_wh_edit_'.$wh_id.'_pid">';
	if(count($list_prod)){
		$msg .= '<option value=""> </option>';
		foreach($list_prod as $prod){
			$msg .= '<option value="'.$prod["id"].'" '.($current_pid == $prod["id"] ? 'selected' : '').'>'.$prod["name"].'</option>';
		}
	}
	$msg .=	'</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="firm_wh_edit_'.$wh_id.'_pidq" value="'.$wh_item["pidq"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="10" id="firm_wh_edit_'.$wh_id.'_pidn" value="'.$wh_item["pidn"].'" />';
	$msg .= '</td><td>';
	$msg .= '$'.number_format_readable($wh_item["pidcost"]/100);
	$msg .= '</td><td>';
	$msg .= '<a href="#" onclick="adminFirmWHController.editConfirm(\''.$wh_id.'\')">[OK]</a> <a href="#" onclick="adminFirmWHController.editCancel(\''.$wh_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$firm_id = filter_var($_POST['firm_id'], FILTER_SANITIZE_NUMBER_INT);
	$pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
	$pidq = filter_var($_POST['pidq'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$pidn = filter_var($_POST['pidn'], FILTER_SANITIZE_NUMBER_INT);

	$query = $db->prepare("UPDATE firm_wh SET pid = ?, pidq = ?, pidn = ? WHERE id = ?");
	$result = $query->execute(array($pid, $pidq, $pidn, $wh_id));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT firm_wh.*, list_prod.name AS prod_name FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.id = $wh_id ORDER BY list_prod.name ASC";
	$wh_item = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($wh_item)){
		echo '{"success" : 0, "msg" : "Warehouse item not found."}';
		exit();
	}

	$msg = '<td>'.$wh_item["prod_name"].'</td>
		<td>'.$wh_item["pidq"].'</td>
		<td>'.$wh_item["pidn"].'</td>
		<td>$'.number_format_readable($wh_item["pidcost"]/100).'</td>
		<td><a href="#" onclick="adminFirmWHController.showEdit(\''.$wh_id.'\')">[Edit]</a> <a href="#" onclick="adminFirmWHController.deleteWH(\''.$wh_id.'\')">[Delete]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT firm_wh.*, list_prod.name AS prod_name FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_wh.id = $wh_id ORDER BY list_prod.name ASC";
	$wh_item = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($wh_item)){
		echo '{"success" : 0, "msg" : "Warehouse item not found."}';
		exit();
	}

	$msg = '<td>'.$wh_item["prod_name"].'</td>
		<td>'.$wh_item["pidq"].'</td>
		<td>'.$wh_item["pidn"].'</td>
		<td>$'.number_format_readable($wh_item["pidcost"]/100).'</td>
		<td><a href="#" onclick="adminFirmWHController.showEdit(\''.$wh_id.'\')">[Edit]</a> <a href="#" onclick="adminFirmWHController.deleteWH(\''.$wh_id.'\')">[Delete]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>