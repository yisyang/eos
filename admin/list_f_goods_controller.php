<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(isset($_POST['goods_id'])){
	$goods_id = filter_var($_POST['goods_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_goods'){
	$fc_id = filter_var($_POST['fc_id'], FILTER_SANITIZE_NUMBER_INT);
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	$quality = filter_var($_POST['quality'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$value_to_sell = filter_var($_POST['value_to_sell'], FILTER_SANITIZE_NUMBER_INT);
	$price_multiplier = filter_var($_POST['price_multiplier'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	
	$query = $db->prepare("SELECT COUNT(*) FROM foreign_list_goods WHERE fcid = ? AND cat_id = ?");
	$query->execute(array($fc_id, $cat_id));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Data already exists."}';
		exit();
	}

	$query = $db->prepare("INSERT INTO foreign_list_goods (fcid, cat_id, quality, value_to_sell, price_multiplier) VALUES (?, ?, ?, ?, ?)");
	$result = $query->execute(array($fc_id, $cat_id, $quality, $value_to_sell, $price_multiplier));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize FCs
	$sql = "SELECT * FROM foreign_companies ORDER BY name ASC";
	$foreign_companies = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$list_cat = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM foreign_list_goods WHERE id = $goods_id";
	$goods = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($goods)){
		echo '{"success" : 0, "msg" : "Goods not found."}';
		exit();
	}
	
	$current_fcid = $goods["fcid"];
	$current_cat_id = $goods['cat_id'];

	$msg = '<td>';
	$msg .= '<select id="list_f_goods_edit_'.$goods_id.'_fcid">';
	if(count($foreign_companies)){
		$msg .= '<option value=""> </option>';
		foreach($foreign_companies as $foreign_company){
			$msg .= '<option value="'.$foreign_company["id"].'" '.($current_fcid == $foreign_company["id"] ? 'selected' : '').'>'.$foreign_company["name"].'</option>';
		}
	}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<select id="list_f_goods_edit_'.$goods_id.'_cat_id">';
	if(count($list_cat)){
		$msg .= '<option value=""> </option>';
		foreach($list_cat as $cat){
			$msg .= '<option value="'.$cat["id"].'" '.($current_cat_id == $cat["id"] ? 'selected' : '').'>'.$cat["name"].'</option>';
		}
	}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_f_goods_edit_'.$goods_id.'_quality" value="'.$goods["quality"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_f_goods_edit_'.$goods_id.'_value_to_sell" value="'.($goods["value_to_sell"]/100).'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_f_goods_edit_'.$goods_id.'_price_multiplier" value="'.$goods["price_multiplier"].'" />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listFGoodsController.editConfirm(\''.$goods_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listFGoodsController.editCancel(\''.$goods_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$fc_id = filter_var($_POST['fc_id'], FILTER_SANITIZE_NUMBER_INT);
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	$quality = filter_var($_POST['quality'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$value_to_sell = filter_var($_POST['value_to_sell'], FILTER_SANITIZE_NUMBER_INT);
	$price_multiplier = filter_var($_POST['price_multiplier'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

	$query = $db->prepare("UPDATE foreign_list_goods SET fcid = ?, cat_id = ?, quality = ?, value_to_sell = ?, price_multiplier = ? WHERE id = ?");
	$result = $query->execute(array($fc_id, $cat_id, $quality, $value_to_sell, $price_multiplier, $goods_id));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT foreign_list_goods.*, foreign_companies.name AS fc_name, list_cat.name AS cat_name FROM foreign_list_goods LEFT JOIN foreign_companies ON foreign_list_goods.fcid = foreign_companies.id LEFT JOIN list_cat ON foreign_list_goods.cat_id = list_cat.id WHERE foreign_list_goods.id = $goods_id";
	$goods = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($goods)){
		echo '{"success" : 0, "msg" : "Goods not found."}';
		exit();
	}

	$msg = '<td>'.$goods["fc_name"].'</td><td>'.$goods["cat_name"].'</td><td>'.$goods["quality"].'</td><td>$'.number_format($goods["value_to_sell"]/100,2,'.',',').'</td><td>'.$goods["price_multiplier"].'</td><td><a style="cursor:pointer;" onclick="listFGoodsController.showEdit(\''.$goods_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT foreign_list_goods.*, foreign_companies.name AS fc_name, list_cat.name AS cat_name FROM foreign_list_goods LEFT JOIN foreign_companies ON foreign_list_goods.fcid = foreign_companies.id LEFT JOIN list_cat ON foreign_list_goods.cat_id = list_cat.id WHERE foreign_list_goods.id = $goods_id";
	$goods = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($goods)){
		echo '{"success" : 0, "msg" : "Goods not found."}';
		exit();
	}

	$msg = '<td>'.$goods["fc_name"].'</td><td>'.$goods["cat_name"].'</td><td>'.$goods["quality"].'</td><td>$'.number_format($goods["value_to_sell"]/100,2,'.',',').'</td><td>'.$goods["price_multiplier"].'</td><td><a style="cursor:pointer;" onclick="listFGoodsController.showEdit(\''.$goods_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>