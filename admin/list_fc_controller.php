<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(!isset($_POST['action'])){
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if(isset($_POST['fc_id'])){
	$fc_id = filter_var($_POST['fc_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_company'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$country_id = filter_var($_POST['country_id'], FILTER_SANITIZE_NUMBER_INT);
	$country_name = filter_var($_POST['country_name'], FILTER_SANITIZE_STRING);
	$query = $db->prepare("SELECT COUNT(*) FROM foreign_companies WHERE name = ?");
	$query->execute(array($name));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Name already exists."}';
		exit();
	}

	$query = $db->prepare("INSERT INTO foreign_companies (name, country_id, country_name) VALUES (?, ?, ?)");
	$result = $query->execute(array($name, $country_id, $country_name));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	$sql="SELECT * FROM foreign_companies WHERE id = $fc_id";
	$fc = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fc)){
		echo '{"success" : 0, "msg" : "Company not found."}';
		exit();
	}

	$msg = '<td>';
	$msg .= '<input type="text" size="24" id="list_fc_edit_'.$fc_id.'_name" value="'.$fc["name"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_fc_edit_'.$fc_id.'_country_id" value="'.$fc["country_id"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_fc_edit_'.$fc_id.'_country_name" value="'.$fc["country_name"].'" />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listFcController.editConfirm(\''.$fc_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listFcController.editCancel(\''.$fc_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$country_id = filter_var($_POST['country_id'], FILTER_SANITIZE_NUMBER_INT);
	$country_name = filter_var($_POST['country_name'], FILTER_SANITIZE_STRING);

	$query = $db->prepare("UPDATE foreign_companies SET name = ?, country_id = ?, country_name = ? WHERE id = ?");
	$result = $query->execute(array($name, $country_id, $country_name, $fc_id));

	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql="SELECT * FROM foreign_companies WHERE id = $fc_id";
	$fc = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fc)){
		echo '{"success" : 0, "msg" : "Company not found."}';
		exit();
	}

	$msg = '<td>'.$fc["name"].'</td><td>'.$fc["country_id"].'</td><td>'.$fc["country_name"].'</td><td><a style="cursor:pointer;" onclick="listFcController.showEdit(\''.$fc_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT * FROM foreign_companies WHERE id = $fc_id";
	$fc = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fc)){
		echo '{"success" : 0, "msg" : "Company not found."}';
		exit();
	}

	$msg = '<td>'.$fc["name"].'</td><td>'.$fc["country_id"].'</td><td>'.$fc["country_name"].'</td><td><a style="cursor:pointer;" onclick="listFcController.showEdit(\''.$fc_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>