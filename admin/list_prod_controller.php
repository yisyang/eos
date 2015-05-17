<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(isset($_POST['action'])){
	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
}else{
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}

if(isset($_POST['prod_id'])){
	$prod_id = filter_var($_POST['prod_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_prod'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	$value = filter_var($_POST['value'], FILTER_SANITIZE_NUMBER_INT);
	$value_avg = 2 * $value;
	$selltime = filter_var($_POST['selltime'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$res_cost = filter_var($_POST['res_cost'], FILTER_SANITIZE_NUMBER_INT);
	$res_dep_1 = filter_var($_POST['res_dep_1'], FILTER_SANITIZE_NUMBER_INT);
	$res_dep_2 = filter_var($_POST['res_dep_2'], FILTER_SANITIZE_NUMBER_INT);
	$res_dep_3 = filter_var($_POST['res_dep_3'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$name){
		echo '{"success" : 0, "msg" : "Product name not specified."}';
		exit();
	}
	
	$query = $db->prepare("SELECT COUNT(*) FROM list_prod WHERE name = ?");
	$query->execute(array($name));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Name already exists."}';
		exit();
	}

	$sql = "INSERT INTO list_prod (name, cat_id, value, value_avg, selltime, res_cost, res_dep_1, res_dep_2, res_dep_3) VALUES (:name, :cat_id, COALESCE(:value, DEFAULT(value)), COALESCE(:value_avg, DEFAULT(value_avg)), COALESCE(:selltime, DEFAULT(selltime)), COALESCE(:res_cost, DEFAULT(res_cost)), :res_dep_1, :res_dep_2, :res_dep_3)";
	
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':cat_id' => $cat_id, ':value' => empty($value) ? null : $value, ':value_avg' => empty($value_avg) ? null : $value_avg, ':selltime' => empty($selltime) ? null : $selltime, ':res_cost' => empty($res_cost) ? null : $res_cost, ':res_dep_1' => empty($res_dep_1) ? null : $res_dep_1, ':res_dep_2' => empty($res_dep_2) ? null : $res_dep_2, ':res_dep_3' => empty($res_dep_3) ? null : $res_dep_3));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//Initialize Res Deps
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$res_deps = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	$sql = "SELECT * FROM list_prod WHERE id = $prod_id";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($prod)){
		echo '{"success" : 0, "msg" : "Product not found."}';
		exit();
	}
	
	$name = $prod["name"];
	$msg = '<td>';
	$msg .= '<div class="image_upload_control" style="float:right;width:30px;text-align:right;overflow:hidden;">';
	$msg .= '<form id="form_image_up_'.$prod_id.'" class="form_blank" style="width:30px;" action="#nogo" method="post" enctype="multipart/form-data"><div style="position:relative;top:0;left:0;">';
	$msg .= '<input id="form_image_up_btn_'.$prod_id.'" type="file" style="visibility:hidden;position:absolute;top:0;left:0" name="'.$prod_id.'_up[]" multiple="multiple" />';
	$msg .= '<div id="form_image_up_ddarea_'.$prod_id.'" class="drag_drop_area" style="cursor:pointer;border:solid 1px #ff0000;" onclick="getElementById(\'form_image_up_btn_'.$prod_id.'\').click();">'._("UP Area").'</div>';
	$msg .= '</div><br /></form>';
	$msg .= '<div id="form_image_up_progress_'.$prod_id.'"></div>';
	$msg .= '</div>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_prod_edit_'.$prod_id.'_name" value="'.$name.'" />';
	$msg .= '</td><td>';
	$msg .= '<select id="list_prod_edit_'.$prod_id.'_cat_id">';
	if(!empty($cats)){
		$msg .= '<option value=""> </option>';
		foreach($cats as $cat){
			$msg .= '<option value="'.$cat['id'].'" '.($prod["cat_id"] == $cat['id'] ? 'selected' : '').'>'.$cat['name'].'</option>';
		}
	}
	$msg .= '</select>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="10" id="list_prod_edit_'.$prod_id.'_value" value="'.number_format($prod["value"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_prod_edit_'.$prod_id.'_selltime" value="'.$prod["selltime"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="10" id="list_prod_edit_'.$prod_id.'_res_cost" value="'.number_format($prod["res_cost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<select id="list_prod_edit_'.$prod_id.'_res_dep_1" class="select_100px">';
	if(!empty($res_deps)){
		$msg .= '<option value=""> </option>';
		foreach($res_deps as $res_dep){
			$msg .= '<option value="'.$res_dep['id'].'" '.($prod["res_dep_1"] == $res_dep['id'] ? 'selected' : '').'>'.$res_dep['name'].'</option>';
		}
	}
	$msg .= '</select><br />';
	$msg .= '<select id="list_prod_edit_'.$prod_id.'_res_dep_2" class="select_100px">';
	if(!empty($res_deps)){
		$msg .= '<option value=""> </option>';
		foreach($res_deps as $res_dep){
			$msg .= '<option value="'.$res_dep['id'].'" '.($prod["res_dep_2"] == $res_dep['id'] ? 'selected' : '').'>'.$res_dep['name'].'</option>';
		}
	}
	$msg .= '</select><br />';
	$msg .= '<select id="list_prod_edit_'.$prod_id.'_res_dep_3" class="select_100px">';
	if(!empty($res_deps)){
		$msg .= '<option value=""> </option>';
		foreach($res_deps as $res_dep){
			$msg .= '<option value="'.$res_dep['id'].'" '.($prod["res_dep_3"] == $res_dep['id'] ? 'selected' : '').'>'.$res_dep['name'].'</option>';
		}
	}
	$msg .= '</select><br />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listProdController.editConfirm(\''.$prod_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listProdController.editCancel(\''.$prod_id.'\')">[Cancel]</a>';
	$msg .= '</td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_confirm'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	$value = filter_var($_POST['value'], FILTER_SANITIZE_NUMBER_INT);
	$selltime = filter_var($_POST['selltime'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$res_cost = filter_var($_POST['res_cost'], FILTER_SANITIZE_NUMBER_INT);
	$res_dep_1 = filter_var($_POST['res_dep_1'], FILTER_SANITIZE_NUMBER_INT);
	$res_dep_2 = filter_var($_POST['res_dep_2'], FILTER_SANITIZE_NUMBER_INT);
	$res_dep_3 = filter_var($_POST['res_dep_3'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$name){
		echo '{"success" : 0, "msg" : "Product name not specified."}';
		exit();
	}

	//Select id from table, check if there is a change in name, and if the thing has image/icon
	$sql = "SELECT name, has_icon FROM list_prod WHERE id = $prod_id";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$old_name = $prod["name"];
	$has_image = $prod["has_icon"];

	//If there is a name change, first find out if the new name is already taken
	if($name != $old_name){
		$query = $db->prepare("SELECT COUNT(*) FROM list_prod WHERE name = ?");
		$query->execute(array($name));
		$count = $query->fetchColumn();

		if($count){
			echo '{"success" : 0, "msg" : "New name is already taken."}';
			exit();
		}

		//If not taken, name will be changed later, so proceed to change picture name if it exists
		if($has_image){
			$folder = '../images/prod/';
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
					echo '{"success" : 0, "msg" : "Icon with new product name is already taken."}';
					exit();
				}
				rename($oldfile,$newfile);
				rename($oldfilelarge,$newfilelarge);
			}
		}
	}

	$sql = "UPDATE list_prod SET name = :name, cat_id = :cat_id, value = COALESCE(:value, DEFAULT(value)), selltime = COALESCE(:selltime, DEFAULT(selltime)), res_cost = COALESCE(:res_cost, DEFAULT(res_cost)), res_dep_1 = :res_dep_1, res_dep_2 = :res_dep_2, res_dep_3 = :res_dep_3 WHERE id = $prod_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':cat_id' => $cat_id, ':value' => empty($value) ? null : $value, ':selltime' => empty($selltime) ? null : $selltime, ':res_cost' => empty($res_cost) ? null : $res_cost, ':res_dep_1' => empty($res_dep_1) ? null : $res_dep_1, ':res_dep_2' => empty($res_dep_2) ? null : $res_dep_2, ':res_dep_3' => empty($res_dep_3) ? null : $res_dep_3));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT list_prod.*, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($prods as $prod){
		$filename_temp = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod["name"]));
		if($prod["has_icon"]){
			$prod_img[$prod["id"]] = '<img src="/eos/images/prod/'.$filename_temp.'.gif" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
		}else{
			$prod_img[$prod["id"]] = '<img src="/eos/images/prod/no-icon.gif" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
		}
	}
	
	$sql = "SELECT list_prod.*, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_prod.id = $prod_id";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($prod)){
		echo '{"success" : 0, "msg" : "Product not found."}';
		exit();
	}

	$msg = '<td>'.$prod_img[$prod_id].'</td>';
	$msg .= '<td>'.$prod["name"].'</td><td>'.$prod["cat_name"].'</td><td>'.'$'.number_format($prod["value"]/100, 2, '.', ',').'</td><td>'.$prod["selltime"].' s'.'</td><td>'.'$'.number_format($prod["res_cost"]/100, 2, '.', ',').'</td><td>';
	if($res_dep = $prod["res_dep_1"]){
		$msg .= $prod_img[$res_dep];
		if($res_dep = $prod["res_dep_2"]){
			$msg .= ' '.$prod_img[$res_dep];
			if($res_dep = $prod["res_dep_3"]){
				$msg .= ' '.$prod_img[$res_dep];
			}
		}
	}else{
		$msg .= '&nbsp;';
	}
	$msg .= '</td><td><a style="cursor:pointer;" onclick="listProdController.showEdit(\''.$prod_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT list_prod.*, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($prods as $prod){
		$filename_temp = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod["name"]));
		if($prod["has_icon"]){
			$prod_img[$prod["id"]] = '<img src="/eos/images/prod/'.$filename_temp.'.gif" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
		}else{
			$prod_img[$prod["id"]] = '<img src="/eos/images/prod/no-icon.gif" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
		}
	}
	
	$sql = "SELECT list_prod.*, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_prod.id = $prod_id";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($prod)){
		echo '{"success" : 0, "msg" : "Product not found."}';
		exit();
	}

	$msg = '<td>'.$prod_img[$prod_id].'</td>';
	$msg .= '<td>'.$prod["name"].'</td><td>'.$prod["cat_name"].'</td><td>'.'$'.number_format($prod["value"]/100, 2, '.', ',').'</td><td>'.$prod["selltime"].' s'.'</td><td>'.'$'.number_format($prod["res_cost"]/100, 2, '.', ',').'</td><td>';
	if($res_dep = $prod["res_dep_1"]){
		$msg .= $prod_img[$res_dep];
		if($res_dep = $prod["res_dep_2"]){
			$msg .= ' '.$prod_img[$res_dep];
			if($res_dep = $prod["res_dep_3"]){
				$msg .= ' '.$prod_img[$res_dep];
			}
		}
	}else{
		$msg .= '&nbsp;';
	}
	$msg .= '</td><td><a style="cursor:pointer;" onclick="listProdController.showEdit(\''.$prod_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'ajaxupload'){
	//Load the dd uploader script
	require_once '../scripts/dd_image_uploader.php';

	$sql = "SELECT * FROM list_prod WHERE id = $prod_id";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($prod)){
		echo '{"success" : 0, "msg" : "Product not found."}';
		exit();
	}
	
	$name = $prod["name"];
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));

	list($success, $new_filename) = uploadImage($filename, 1, 'gif');
	
	if($success){
		$sql = "UPDATE list_prod SET has_icon = 1 WHERE id = $prod_id";
		$result = $db->query($sql);

		if($result){
			$image = '<img src="/eos/images/prod/'.$filename.'.gif?'.time().'" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
			$action = "$('#list_prod_".$prod["id"]."').children('td').eq(0).html('$image')";
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