<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(isset($_POST['action'])){
	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
}else{
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}

if(isset($_POST['fact_choice_id'])){
	$fact_choice_id = filter_var($_POST['fact_choice_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_fact_choice'){
	$fact_id = filter_var($_POST['fact_id'], FILTER_SANITIZE_NUMBER_INT);
	$cost = filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_INT);
	$timecost = filter_var($_POST['timecost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid1 = filter_var($_POST['ipid1'], FILTER_SANITIZE_NUMBER_INT);
	$ipid1n = filter_var($_POST['ipid1n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid1qm = filter_var($_POST['ipid1qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid2 = filter_var($_POST['ipid2'], FILTER_SANITIZE_NUMBER_INT);
	$ipid2n = filter_var($_POST['ipid2n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid2qm = filter_var($_POST['ipid2qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid3 = filter_var($_POST['ipid3'], FILTER_SANITIZE_NUMBER_INT);
	$ipid3n = filter_var($_POST['ipid3n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid3qm = filter_var($_POST['ipid3qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid4 = filter_var($_POST['ipid4'], FILTER_SANITIZE_NUMBER_INT);
	$ipid4n = filter_var($_POST['ipid4n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid4qm = filter_var($_POST['ipid4qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$opid1 = filter_var($_POST['opid1'], FILTER_SANITIZE_NUMBER_INT);

	if(!$fact_id){
		echo '{"success" : 0, "msg" : "Missing Factory ID."}';
		exit();
	}
	if(!$opid1){
		echo '{"success" : 0, "msg" : "Missing Output Product."}';
		exit();
	}

	$sql = "INSERT INTO list_fact_choices (fact_id, cost, timecost, ipid1, ipid1n, ipid1qm, ipid2, ipid2n, ipid2qm, ipid3, ipid3n, ipid3qm, ipid4, ipid4n, ipid4qm, opid1) VALUES (:fact_id, COALESCE(:cost, DEFAULT(cost)), COALESCE(:timecost, DEFAULT(timecost)), :ipid1, :ipid1n, :ipid1qm, :ipid2, :ipid2n, :ipid2qm, :ipid3, :ipid3n, :ipid3qm, :ipid4, :ipid4n, :ipid4qm, :opid1)";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':fact_id' => $fact_id, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost, ':ipid1' => empty($ipid1) ? null : $ipid1, ':ipid1n' => empty($ipid1n) ? null : $ipid1n, ':ipid1qm' => empty($ipid1qm) ? null : $ipid1qm, ':ipid2' => empty($ipid2) ? null : $ipid2, ':ipid2n' => empty($ipid2n) ? null : $ipid2n, ':ipid2qm' => empty($ipid2qm) ? null : $ipid2qm, ':ipid3' => empty($ipid3) ? null : $ipid3, ':ipid3n' => empty($ipid3n) ? null : $ipid3n, ':ipid3qm' => empty($ipid3qm) ? null : $ipid3qm, ':ipid4' => empty($ipid4) ? null : $ipid4, ':ipid4n' => empty($ipid4n) ? null : $ipid4n, ':ipid4qm' => empty($ipid4qm) ? null : $ipid4qm, ':opid1' => empty($opid1) ? null : $opid1));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize Factories
	$sql = "SELECT * FROM list_fact ORDER BY name ASC";
	$facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//Initialize Products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($prods as $prod){
		$prod_value[$prod["id"]] = $prod["value"];
	}
	
	$sql = "SELECT * FROM list_fact_choices WHERE id = $fact_choice_id";
	$fact_choice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact_choice)){
		echo '{"success" : 0, "msg" : "Factory Choice not found."}';
		exit();
	}

	$msg = '<td>';
	$msg .= '<select id="list_fact_choices_edit_'.$fact_choice_id.'_fact_id">';
		foreach($facts as $fact){
			$msg .= '<option value="'.$fact['id'].'" '.($fact_choice["fact_id"] == $fact['id'] ? 'selected' : '').'>'.$fact['name'].'</option>';
		}
	$msg .=	'</select>';
	$msg .= '</td><td>';
	$msg .= '<select id="list_fact_choices_edit_'.$fact_choice_id.'_opid1">';
		foreach($prods as $prod){
			$msg .= '<option value="'.$prod['id'].'" '.($fact_choice["opid1"] == $prod['id'] ? 'selected' : '').'>'.$prod['name'].' ($'.number_format($prod['value']/100, 2, ".", ",").')</option>';
		}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_fact_choices_edit_'.$fact_choice_id.'_cost" value="'.number_format($fact_choice["cost"]/100, 2, '.', '').'" /></td><td><input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_timecost" value="'.$fact_choice["timecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<select id="list_fact_choices_edit_'.$fact_choice_id.'_ipid1">';
		$msg .= '<option value=""> </option>';
		foreach($prods as $prod){
			$msg .= '<option value="'.$prod['id'].'" '.($fact_choice["ipid1"] == $prod['id'] ? 'selected' : '').'>'.$prod['name'].' ($'.number_format($prod['value']/100, 2, ".", ",").')</option>';
		}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid1n" value="'.$fact_choice["ipid1n"].'" /></td><td><input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid1qm" value="'.$fact_choice["ipid1qm"].'" />';
	$msg .= '</td><td>';
	$msg .= '<select id="list_fact_choices_edit_'.$fact_choice_id.'_ipid2">';
		$msg .= '<option value=""> </option>';
		foreach($prods as $prod){
			$msg .= '<option value="'.$prod['id'].'" '.($fact_choice["ipid2"] == $prod['id'] ? 'selected' : '').'>'.$prod['name'].' ($'.number_format($prod['value']/100, 2, ".", ",").')</option>';
		}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid2n" value="'.$fact_choice["ipid2n"].'" /></td><td><input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid2qm" value="'.$fact_choice["ipid2qm"].'" />';
	$msg .= '</td><td>';
	$msg .= '<select id="list_fact_choices_edit_'.$fact_choice_id.'_ipid3">';
		$msg .= '<option value=""> </option>';
		foreach($prods as $prod){
			$msg .= '<option value="'.$prod['id'].'" '.($fact_choice["ipid3"] == $prod['id'] ? 'selected' : '').'>'.$prod['name'].' ($'.number_format($prod['value']/100, 2, ".", ",").')</option>';
		}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid3n" value="'.$fact_choice["ipid3n"].'" /></td><td><input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid3qm" value="'.$fact_choice["ipid3qm"].'" />';
	$msg .= '</td><td>';
	$msg .= '<select id="list_fact_choices_edit_'.$fact_choice_id.'_ipid4">';
		$msg .= '<option value=""> </option>';
		foreach($prods as $prod){
			$msg .= '<option value="'.$prod['id'].'" '.($fact_choice["ipid4"] == $prod['id'] ? 'selected' : '').'>'.$prod['name'].' ($'.number_format($prod['value']/100, 2, ".", ",").')</option>';
		}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid4n" value="'.$fact_choice["ipid4n"].'" /></td><td><input type="text" size="5" id="list_fact_choices_edit_'.$fact_choice_id.'_ipid4qm" value="'.$fact_choice["ipid4qm"].'" />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listFactChoicesController.editConfirm(\''.$fact_choice_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listFactChoicesController.editCancel(\''.$fact_choice_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$fact_id = filter_var($_POST['fact_id'], FILTER_SANITIZE_NUMBER_INT);
	$cost = filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_INT);
	$timecost = filter_var($_POST['timecost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid1 = filter_var($_POST['ipid1'], FILTER_SANITIZE_NUMBER_INT);
	$ipid1n = filter_var($_POST['ipid1n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid1qm = filter_var($_POST['ipid1qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid2 = filter_var($_POST['ipid2'], FILTER_SANITIZE_NUMBER_INT);
	$ipid2n = filter_var($_POST['ipid2n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid2qm = filter_var($_POST['ipid2qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid3 = filter_var($_POST['ipid3'], FILTER_SANITIZE_NUMBER_INT);
	$ipid3n = filter_var($_POST['ipid3n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid3qm = filter_var($_POST['ipid3qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid4 = filter_var($_POST['ipid4'], FILTER_SANITIZE_NUMBER_INT);
	$ipid4n = filter_var($_POST['ipid4n'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$ipid4qm = filter_var($_POST['ipid4qm'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$opid1 = filter_var($_POST['opid1'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "UPDATE list_fact_choices SET fact_id = :fact_id, cost = COALESCE(:cost, DEFAULT(cost)), timecost = COALESCE(:timecost, DEFAULT(timecost)), ipid1 = :ipid1, ipid1n = :ipid1n, ipid1qm = :ipid1qm, ipid2 = :ipid2, ipid2n = :ipid2n, ipid2qm = :ipid2qm, ipid3 = :ipid3, ipid3n = :ipid3n, ipid3qm = :ipid3qm, ipid4 = :ipid4, ipid4n = :ipid4n, ipid4qm = :ipid4qm, opid1 = :opid1 WHERE id = $fact_choice_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':fact_id' => $fact_id, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost, ':ipid1' => empty($ipid1) ? null : $ipid1, ':ipid1n' => empty($ipid1n) ? null : $ipid1n, ':ipid1qm' => empty($ipid1qm) ? null : $ipid1qm, ':ipid2' => empty($ipid2) ? null : $ipid2, ':ipid2n' => empty($ipid2n) ? null : $ipid2n, ':ipid2qm' => empty($ipid2qm) ? null : $ipid2qm, ':ipid3' => empty($ipid3) ? null : $ipid3, ':ipid3n' => empty($ipid3n) ? null : $ipid3n, ':ipid3qm' => empty($ipid3qm) ? null : $ipid3qm, ':ipid4' => empty($ipid4) ? null : $ipid4, ':ipid4n' => empty($ipid4n) ? null : $ipid4n, ':ipid4qm' => empty($ipid4qm) ? null : $ipid4qm, ':opid1' => empty($opid1) ? null : $opid1));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	//Initialize Factories
	$sql = "SELECT * FROM list_fact ORDER BY name ASC";
	$facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//Initialize Products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$prod_options = '<option value=""> </option>';
	$prod_name[0] = '';
	$prod_name[null] = '';
	foreach($prods as $prod){
		$prod_name[$prod["id"]] = $prod["name"];
	}

	$sql = "SELECT list_fact_choices.*, list_fact.name AS fact_name FROM list_fact_choices LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id WHERE list_fact_choices.id = $fact_choice_id";
	$fact_choice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact_choice)){
		echo '{"success" : 0, "msg" : "Factory Choice not found."}';
		exit();
	}

	$msg = '<td>'.$fact_choice["fact_name"].'</td><td>'.$prod_name[$fact_choice["opid1"]].'</td>
	<td>'.'$'.number_format($fact_choice["cost"]/100, 2, '.', ',').'</td><td>'.$fact_choice["timecost"].' s'.'</td>';
	if($fact_choice["ipid1"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid1"]].'</td><td>'.$fact_choice["ipid1n"].'</td><td>'.$fact_choice["ipid1qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($fact_choice["ipid2"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid2"]].'</td><td>'.$fact_choice["ipid2n"].'</td><td>'.$fact_choice["ipid2qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($fact_choice["ipid3"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid3"]].'</td><td>'.$fact_choice["ipid3n"].'</td><td>'.$fact_choice["ipid3qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($fact_choice["ipid4"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid4"]].'</td><td>'.$fact_choice["ipid4n"].'</td><td>'.$fact_choice["ipid4qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	$msg .= '<td><a style="cursor:pointer;" onclick="listFactChoicesController.showEdit(\''.$fact_choice_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	//Initialize Factories
	$sql = "SELECT * FROM list_fact ORDER BY name ASC";
	$facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//Initialize Products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$prod_options = '<option value=""> </option>';
	$prod_name[0] = '';
	$prod_name[null] = '';
	foreach($prods as $prod){
		$prod_name[$prod["id"]] = $prod["name"];
	}

	$sql = "SELECT list_fact_choices.*, list_fact.name AS fact_name FROM list_fact_choices LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id WHERE list_fact_choices.id = $fact_choice_id";
	$fact_choice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact_choice)){
		echo '{"success" : 0, "msg" : "Factory Choice not found."}';
		exit();
	}

	$msg = '<td>'.$fact_choice["fact_name"].'</td><td>'.$prod_name[$fact_choice["opid1"]].'</td>
	<td>'.'$'.number_format($fact_choice["cost"]/100, 2, '.', ',').'</td><td>'.$fact_choice["timecost"].' s'.'</td>';
	if($fact_choice["ipid1"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid1"]].'</td><td>'.$fact_choice["ipid1n"].'</td><td>'.$fact_choice["ipid1qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($fact_choice["ipid2"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid2"]].'</td><td>'.$fact_choice["ipid2n"].'</td><td>'.$fact_choice["ipid2qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($fact_choice["ipid3"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid3"]].'</td><td>'.$fact_choice["ipid3n"].'</td><td>'.$fact_choice["ipid3qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($fact_choice["ipid4"]){
		$msg .= '<td>'.$prod_name[$fact_choice["ipid4"]].'</td><td>'.$fact_choice["ipid4n"].'</td><td>'.$fact_choice["ipid4qm"].'</td>';
	}else{
		$msg .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	$msg .= '<td><a style="cursor:pointer;" onclick="listFactChoicesController.showEdit(\''.$fact_choice_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else{
	echo '{"success" : 0, "msg" : "Action not defined."}';
}
?>