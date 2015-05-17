<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(isset($_POST['action'])){
	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
}else{
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}

if(isset($_POST['store_id'])){
	$store_id = filter_var($_POST['store_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_store'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$division_name = filter_var($_POST['division_name'], FILTER_SANITIZE_STRING);
	$cost = filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_INT);
	$timecost = filter_var($_POST['timecost'], FILTER_SANITIZE_NUMBER_INT);
	$firstcost = filter_var($_POST['firstcost'], FILTER_SANITIZE_NUMBER_INT);
	$firsttimecost = filter_var($_POST['firsttimecost'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$name){
		echo '{"success" : 0, "msg" : "Store name not specified."}';
		exit();
	}
	
	$query = $db->prepare("SELECT COUNT(*) FROM list_store WHERE name = ?");
	$query->execute(array($name));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Name already exists."}';
		exit();
	}

	$sql = "INSERT INTO list_store (name, division_name, cost, timecost, firstcost, firsttimecost) VALUES (:name, :division_name, COALESCE(:cost, DEFAULT(cost)), COALESCE(:timecost, DEFAULT(timecost)), COALESCE(:firstcost, DEFAULT(firstcost)), COALESCE(:firsttimecost, DEFAULT(firsttimecost)))";
	
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':division_name' => $division_name, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost, ':firstcost' => empty($firstcost) ? null : $firstcost, ':firsttimecost' => empty($firsttimecost) ? null : $firsttimecost));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM list_store WHERE id = $store_id";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		echo '{"success" : 0, "msg" : "Store not found."}';
		exit();
	}
	
	$name = $store["name"];
	$msg = '<td>';
	$msg .= '<div class="image_upload_control" style="float:right;width:30px;text-align:right;overflow:hidden;">';
	$msg .= '<form id="form_image_up_'.$store_id.'" class="form_blank" style="width:30px;" action="#nogo" method="post" enctype="multipart/form-data"><div style="position:relative;top:0;left:0;">';
	$msg .= '<input id="form_image_up_btn_'.$store_id.'" type="file" style="visibility:hidden;position:absolute;top:0;left:0" name="'.$store_id.'_up[]" multiple="multiple" />';
	$msg .= '<div id="form_image_up_ddarea_'.$store_id.'" class="drag_drop_area" style="cursor:pointer;border:solid 1px #ff0000;" onclick="getElementById(\'form_image_up_btn_'.$store_id.'\').click();">'._("UP Area").'</div>';
	$msg .= '</div><br /></form>';
	$msg .= '<div id="form_image_up_progress_'.$store_id.'"></div>';
	$msg .= '</div>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_store_edit_'.$store_id.'_name" value="'.$name.'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_store_edit_'.$store_id.'_division_name" value="'.$store["division_name"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_store_edit_'.$store_id.'_firstcost" value="'.number_format($store["firstcost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_store_edit_'.$store_id.'_firsttimecost" value="'.$store["firsttimecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="10" id="list_store_edit_'.$store_id.'_cost" value="'.number_format($store["cost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_store_edit_'.$store_id.'_timecost" value="'.$store["timecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<div id="list_store_edit_'.$store_id.'_can_sell">';
		$sql = "SELECT list_store_choices.id, list_cat.name AS cat_name FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = $store_id ORDER BY list_cat.name ASC";
		$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		foreach($store_choices as $store_choice){
			$msg .= $store_choice["cat_name"] .' <a style="cursor:pointer;" onclick="listStoreController.deleteCanSell(\''.$store_id.'\',\''.$store_choice["id"].'\')">[-]</a><br />';
		}
		$msg .= '<select id="list_store_edit_'.$store_id.'_add_cat_id">';
		if(!empty($cats)){
			$msg .= '<option value=""> </option>';
			foreach($cats as $cat){
				$msg .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
			}
		}
		$msg .= '</select><a style="cursor:pointer;" onclick="listStoreController.addCanSell(\''.$store_id.'\')">[Add]</a>';
	$msg .= '</div>';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listStoreController.editConfirm(\''.$store_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listStoreController.editCancel(\''.$store_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$division_name = filter_var($_POST['division_name'], FILTER_SANITIZE_STRING);
	$firstcost = filter_var($_POST['firstcost'], FILTER_SANITIZE_NUMBER_INT);
	$firsttimecost = filter_var($_POST['firsttimecost'], FILTER_SANITIZE_NUMBER_INT);
	$cost = filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_INT);
	$timecost = filter_var($_POST['timecost'], FILTER_SANITIZE_NUMBER_INT);

	if(!$name){
		echo '{"success" : 0, "msg" : "Store name not specified."}';
		exit();
	}

	//Select id from table, check if there is a change in name, and if the thing has image/icon
	$sql = "SELECT name, has_image FROM list_store WHERE id = $store_id";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$old_name = $store["name"];
	$has_image = $store["has_image"];

	//If there is a name change, first find out if the new name is already taken
	if($name != $old_name){
		$query = $db->prepare("SELECT COUNT(*) FROM list_store WHERE name = ?");
		$query->execute(array($name));
		$count = $query->fetchColumn();

		if($count){
			echo '{"success" : 0, "msg" : "New name is already taken."}';
			exit();
		}
		//If not taken, name will be changed later, so proceed to change picture name if it exists
		if($has_image){
			$folder = '../images/store/';
			$oldfilename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($old_name));
			$newfilename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
			$oldfile = $folder.$oldfilename.".gif";
			$oldfilelarge = $folder.'large/'.$oldfilename.".gif";
			$newfile = $folder.$newfilename.".gif";
			$newfilelarge = $folder.'large/'.$newfilename.".gif";
			if(!file_exists($oldfile)){
				// For Windows
				$oldfile = $folder.utf8_decode($oldfilename).".gif";
				$oldfilelarge = $folder.'large/'.utf8_decode($oldfilename).".gif";
				$newfile = $folder.utf8_decode($newfilename).".gif";
				$newfilelarge = $folder.'large/'.utf8_decode($newfilename).".gif";
			}
			if(file_exists($oldfile)){
				if(file_exists($newfile)){
					//New file somehow exists, so give error and exit
					echo '{"success" : 0, "msg" : "Image with new name is already taken."}';
					exit();
				}
				rename($oldfile,$newfile);
				rename($oldfilelarge,$newfilelarge);
			}
		}
	}

	$sql = "UPDATE list_store SET name = :name, division_name = :division_name, firstcost = COALESCE(:firstcost, DEFAULT(firstcost)), firsttimecost = COALESCE(:firsttimecost, DEFAULT(firsttimecost)), cost = COALESCE(:cost, DEFAULT(cost)), timecost = COALESCE(:timecost, DEFAULT(timecost)) WHERE id = $store_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':division_name' => $division_name, ':firstcost' => empty($firstcost) ? null : $firstcost, ':firsttimecost' => empty($firsttimecost) ? null : $firsttimecost, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT * FROM list_store WHERE id = $store_id";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		echo '{"success" : 0, "msg" : "Store not found."}';
		exit();
	}

	$name = $store["name"];
	$division_name = $store["division_name"];
	if($store["has_image"]){
		$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
	}else{
		$filename = "no-image";
	}

	$sql = "SELECT list_store_choices.id, list_cat.name AS cat_name FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = $store_id ORDER BY list_cat.name ASC";
	$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($store_choices)){
		$list_store_can_sell = '';
		foreach($store_choices as $store_choice){
			$list_store_can_sell .= $store_choice['cat_name'] . ',<br />';
		}
		$list_store_can_sell = substr($list_store_can_sell, 0, -7);
	}else{
		$list_store_can_sell = '&lt;Nothing&gt;';
	}
	
	$msg = '<td><img src="/eos/images/store/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($store["firstcost"]/100, 2, '.', ',').'</td><td>'.$store["firsttimecost"].' s'.'</td><td>'.'$'.number_format($store["cost"]/100, 2, '.', ',').'</td><td>'.$store["timecost"].' s'.'</td><td><small>'.$list_store_can_sell.'</small></td><td><a style="cursor:pointer;" onclick="listStoreController.showEdit(\''.$store_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT * FROM list_store WHERE id = $store_id";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		echo '{"success" : 0, "msg" : "Store not found."}';
		exit();
	}

	$name = $store["name"];
	$division_name = $store["division_name"];
	if($store["has_image"]){
		$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
	}else{
		$filename = "no-image";
	}

	$sql = "SELECT list_store_choices.id, list_cat.name AS cat_name FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = $store_id ORDER BY list_cat.name ASC";
	$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($store_choices)){
		$list_store_can_sell = '';
		foreach($store_choices as $store_choice){
			$list_store_can_sell .= $store_choice['cat_name'] . ',<br />';
		}
		$list_store_can_sell = substr($list_store_can_sell, 0, -7);
	}else{
		$list_store_can_sell = '&lt;Nothing&gt;';
	}
	
	$msg = '<td><img src="/eos/images/store/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($store["firstcost"]/100, 2, '.', ',').'</td><td>'.$store["firsttimecost"].' s'.'</td><td>'.'$'.number_format($store["cost"]/100, 2, '.', ',').'</td><td>'.$store["timecost"].' s'.'</td><td><small>'.$list_store_can_sell.'</small></td><td><a style="cursor:pointer;" onclick="listStoreController.showEdit(\''.$store_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'add_can_sell'){
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$cat_id){
		echo '{"success" : 0, "msg" : "Missing value."}';
		exit();
	}

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT COUNT(*) FROM list_store_choices WHERE store_id = $store_id AND cat_id = $cat_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Duplicate value detected, action canceled."}';
		exit();
	};
	
	$sql = "INSERT INTO list_store_choices (store_id, cat_id) VALUES ($store_id, $cat_id)";
	$result = $db->query($sql);
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}
	
	$sql = "SELECT list_store_choices.id, list_cat.name AS cat_name FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = $store_id ORDER BY list_cat.name ASC";
	$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$msg = '';
	foreach($store_choices as $store_choice){
		$msg .= $store_choice["cat_name"] .' <a style="cursor:pointer;" onclick="listStoreController.deleteCanSell(\''.$store_id.'\',\''.$store_choice["id"].'\')">[-]</a><br />';
	}
	$msg .= '<select id="list_store_edit_'.$store_id.'_add_cat_id">';
	if(!empty($cats)){
		$msg .= '<option value=""> </option>';
		foreach($cats as $cat){
			$msg .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
		}
	}
	$msg .= '</select><a style="cursor:pointer;" onclick="listStoreController.addCanSell(\''.$store_id.'\')">[Add]</a>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'delete_can_sell'){
	$can_sell_id = filter_var($_POST['can_sell_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "DELETE FROM list_store_choices WHERE id = $can_sell_id";
	$result = $db->query($sql);
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT list_store_choices.id, list_cat.name AS cat_name FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = $store_id ORDER BY list_cat.name ASC";
	$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$msg = '';
	foreach($store_choices as $store_choice){
		$msg .= $store_choice["cat_name"] .' <a style="cursor:pointer;" onclick="listStoreController.deleteCanSell(\''.$store_id.'\',\''.$store_choice["id"].'\')">[-]</a><br />';
	}
	$msg .= '<select id="list_store_edit_'.$store_id.'_add_cat_id">';
	if(!empty($cats)){
		$msg .= '<option value=""> </option>';
		foreach($cats as $cat){
			$msg .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
		}
	}
	$msg .= '</select><a style="cursor:pointer;" onclick="listStoreController.addCanSell(\''.$store_id.'\')">[Add]</a>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'ajaxupload'){
	//Load the dd uploader script
	require_once '../scripts/dd_image_uploader.php';

	$sql = "SELECT * FROM list_store WHERE id = $store_id";
	$store = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($store)){
		echo '{"success" : 0, "msg" : "Store not found."}';
		exit();
	}
	
	$name = $store["name"];
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));

	list($success, $new_filename) = uploadImage($filename, 1, 'gif');
	
	if($success){
		$sql = "UPDATE list_store SET has_image = 1 WHERE id = $store_id";
		$result = $db->query($sql);

		if($result){
			$image = '<img src="/eos/images/store/'.$filename.'.gif?'.time().'" width="180" height="80" alt="'.$store["name"].'" title="'.$store["id"].' - '.$store["name"].'" />';
			$action = "$('#list_store_".$store["id"]."').children('td').eq(0).html('$image')";
			echo json_encode(array('success' => 1, 'jsonAction' => $action));
			exit();
		}else{
			echo json_encode(array('success' => 0, 'jsonMsg' => _('Image uploaded but SQL failed.')));
			exit();
		}
	}else{
		$error_msg = _('Error(s) Found: ');
		foreach($new_filename as $error){
	    	$error_msg .= $error.', ';
		}
		echo json_encode(array('success' => 0, 'jsonMsg' => $error_msg));
		exit();
	}
}
else{
	echo "{'success' : 0, 'msg' : 'Action not defined.'}";
}
?>