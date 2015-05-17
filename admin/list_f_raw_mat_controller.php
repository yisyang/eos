<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(isset($_POST['mat_id'])){
	$mat_id = filter_var($_POST['mat_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_mat'){
	$fc_id = filter_var($_POST['fc_id'], FILTER_SANITIZE_NUMBER_INT);
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	$value_to_buy = filter_var($_POST['value_to_buy'], FILTER_SANITIZE_NUMBER_INT);
	$price_multiplier = filter_var($_POST['price_multiplier'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	
	$query = $db->prepare("SELECT COUNT(*) FROM foreign_list_purcs WHERE fcid = ? AND cat_id = ?");
	$query->execute(array($fc_id, $cat_id));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Data already exists."}';
		exit();
	}

	$query = $db->prepare("INSERT INTO foreign_list_purcs (fcid, cat_id, value_to_buy, price_multiplier) VALUES (?, ?, ?, ?)");
	$result = $query->execute(array($fc_id, $cat_id, $value_to_buy, $price_multiplier));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize FCs
	$sql = "SELECT * FROM foreign_companies ORDER BY name ASC";
	$foreign_companies = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$list_cat = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM foreign_list_purcs WHERE id = $mat_id";
	$mat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($mat)){
		echo '{"success" : 0, "msg" : "Raw material not found."}';
		exit();
	}
	
	$current_fcid = $mat["fcid"];
	$current_cat_id = $mat['cat_id'];

	$msg = '<td>';
	$msg .= '<select id="list_f_purcs_edit_'.$mat_id.'_fcid">';
	if(count($foreign_companies)){
		$msg .= '<option value=""> </option>';
		foreach($foreign_companies as $foreign_company){
			$msg .= '<option value="'.$foreign_company["id"].'" '.($current_fcid == $foreign_company["id"] ? 'selected' : '').'>'.$foreign_company["name"].'</option>';
		}
	}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<select id="list_f_purcs_edit_'.$mat_id.'_cat_id">';
	if(count($list_cat)){
		$msg .= '<option value=""> </option>';
		foreach($list_cat as $cat){
			$msg .= '<option value="'.$cat["id"].'" '.($current_cat_id == $cat["id"] ? 'selected' : '').'>'.$cat["name"].'</option>';
		}
	}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_f_purcs_edit_'.$mat_id.'_value_to_buy" value="'.($mat["value_to_buy"]/100).'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_f_purcs_edit_'.$mat_id.'_price_multiplier" value="'.$mat["price_multiplier"].'" />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listFRMController.editConfirm(\''.$mat_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listFRMController.editCancel(\''.$mat_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$fc_id = filter_var($_POST['fc_id'], FILTER_SANITIZE_NUMBER_INT);
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	$value_to_buy = filter_var($_POST['value_to_buy'], FILTER_SANITIZE_NUMBER_INT);
	$price_multiplier = filter_var($_POST['price_multiplier'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

	$query = $db->prepare("UPDATE foreign_list_purcs SET fcid = ?, cat_id = ?, value_to_buy = ?, price_multiplier = ? WHERE id = ?");
	$result = $query->execute(array($fc_id, $cat_id, $value_to_buy, $price_multiplier, $mat_id));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT foreign_list_purcs.*, foreign_companies.name AS fc_name, list_cat.name AS cat_name FROM foreign_list_purcs LEFT JOIN foreign_companies ON foreign_list_purcs.fcid = foreign_companies.id LEFT JOIN list_cat ON foreign_list_purcs.cat_id = list_cat.id WHERE foreign_list_purcs.id='$mat_id'";
	$mat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($mat)){
		echo '{"success" : 0, "msg" : "Raw material not found."}';
		exit();
	}

	$msg = '<td>'.$mat["fc_name"].'</td><td>'.$mat["cat_name"].'</td><td>$'.number_format($mat["value_to_buy"]/100,2,'.',',').'</td><td>'.$mat["price_multiplier"].'</td><td><a style="cursor:pointer;" onclick="listFRMController.showEdit(\''.$mat_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT foreign_list_purcs.*, foreign_companies.name AS fc_name, list_cat.name AS cat_name FROM foreign_list_purcs LEFT JOIN foreign_companies ON foreign_list_purcs.fcid = foreign_companies.id LEFT JOIN list_cat ON foreign_list_purcs.cat_id = list_cat.id WHERE foreign_list_purcs.id='$mat_id'";
	$mat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($mat)){
		echo '{"success" : 0, "msg" : "Raw material not found."}';
		exit();
	}

	$msg = '<td>'.$mat["fc_name"].'</td><td>'.$mat["cat_name"].'</td><td>$'.number_format($mat["value_to_buy"]/100,2,'.',',').'</td><td>'.$mat["price_multiplier"].'</td><td><a style="cursor:pointer;" onclick="listFRMController.showEdit(\''.$mat_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>