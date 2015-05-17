<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(isset($_POST['cat_id'])){
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_cat'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$price_multiplier = filter_var($_POST['price_multiplier'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	
	if(!$price_multiplier) $price_multiplier = 1;
	
	$query = $db->prepare("SELECT COUNT(*) FROM list_cat WHERE name = ?");
	$query->execute(array($name));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Name already exists."}';
		exit();
	}

	$query = $db->prepare("INSERT INTO list_cat (name, price_multiplier) VALUES (?, ?)");
	$result = $query->execute(array($name, $price_multiplier));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	$sql = "SELECT * FROM list_cat WHERE id = $cat_id";
	$cat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($cat)){
		echo '{"success" : 0, "msg" : "Category not found."}';
		exit();
	}
	
	$msg = '<td>';
	$msg .= '<input type="text" size="24" id="list_cat_edit_'.$cat_id.'_name" value="'.$cat["name"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_cat_edit_'.$cat_id.'_price_multiplier" value="'.number_format($cat["price_multiplier"], 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listCatController.editConfirm(\''.$cat_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listCatController.editCancel(\''.$cat_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$price_multiplier = filter_var($_POST['price_multiplier'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

	$query = $db->prepare("UPDATE list_cat SET name = ?, price_multiplier = ? WHERE id = ?");
	$result = $query->execute(array($name, $price_multiplier, $cat_id));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT * FROM list_cat WHERE id = $cat_id";
	$cat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($cat)){
		echo '{"success" : 0, "msg" : "Category not found."}';
		exit();
	}

	$msg = '<td>'.$cat["name"].'</td><td>'.number_format($cat["price_multiplier"], 2, '.', '').'</td><td><a style="cursor:pointer;" onclick="listCatController.showEdit(\''.$cat_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT * FROM list_cat WHERE id = $cat_id";
	$cat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($cat)){
		echo '{"success" : 0, "msg" : "Category not found."}';
		exit();
	}

	$msg = '<td>'.$cat["name"].'</td><td>'.number_format($cat["price_multiplier"], 2, '.', '').'</td><td><a style="cursor:pointer;" onclick="listCatController.showEdit(\''.$cat_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>