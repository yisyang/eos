<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(isset($_POST['action'])){
	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
}else{
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}

if(isset($_POST['fact_id'])){
	$fact_id = filter_var($_POST['fact_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_fact'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$division_name = filter_var($_POST['division_name'], FILTER_SANITIZE_STRING);
	$cost = filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_INT);
	$timecost = filter_var($_POST['timecost'], FILTER_SANITIZE_NUMBER_INT);
	$firstcost = filter_var($_POST['firstcost'], FILTER_SANITIZE_NUMBER_INT);
	$firsttimecost = filter_var($_POST['firsttimecost'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$name){
		echo '{"success" : 0, "msg" : "Factory name not specified."}';
		exit();
	}
	
	$query = $db->prepare("SELECT COUNT(*) FROM list_fact WHERE name = ?");
	$query->execute(array($name));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Name already exists."}';
		exit();
	}

	$sql = "INSERT INTO list_fact (name, division_name, cost, timecost, firstcost, firsttimecost) VALUES (:name, :division_name, COALESCE(:cost, DEFAULT(cost)), COALESCE(:timecost, DEFAULT(timecost)), COALESCE(:firstcost, DEFAULT(firstcost)), COALESCE(:firsttimecost, DEFAULT(firsttimecost)))";
	
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':division_name' => $division_name, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost, ':firstcost' => empty($firstcost) ? null : $firstcost, ':firsttimecost' => empty($firsttimecost) ? null : $firsttimecost));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	$sql = "SELECT * FROM list_fact WHERE id = $fact_id";
	$fact = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact)){
		echo '{"success" : 0, "msg" : "Factory not found."}';
		exit();
	}
	
	$name = $fact["name"];
	$msg = '<td>';
	$msg .= '<div class="image_upload_control" style="float:right;width:30px;text-align:right;overflow:hidden;">';
	$msg .= '<form id="form_image_up_'.$fact_id.'" class="form_blank" style="width:30px;" action="#nogo" method="post" enctype="multipart/form-data"><div style="position:relative;top:0;left:0;">';
	$msg .= '<input id="form_image_up_btn_'.$fact_id.'" type="file" style="visibility:hidden;position:absolute;top:0;left:0" name="'.$fact_id.'_up[]" multiple="multiple" />';
	$msg .= '<div id="form_image_up_ddarea_'.$fact_id.'" class="drag_drop_area" style="cursor:pointer;border:solid 1px #ff0000;" onclick="getElementById(\'form_image_up_btn_'.$fact_id.'\').click();">'._("UP Area").'</div>';
	$msg .= '</div><br /></form>';
	$msg .= '<div id="form_image_up_progress_'.$fact_id.'"></div>';
	$msg .= '</div>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_fact_edit_'.$fact_id.'_name" value="'.$name.'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_fact_edit_'.$fact_id.'_division_name" value="'.$fact["division_name"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_fact_edit_'.$fact_id.'_firstcost" value="'.number_format($fact["firstcost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_fact_edit_'.$fact_id.'_firsttimecost" value="'.$fact["firsttimecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="10" id="list_fact_edit_'.$fact_id.'_cost" value="'.number_format($fact["cost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_fact_edit_'.$fact_id.'_timecost" value="'.$fact["timecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listFactController.editConfirm(\''.$fact_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listFactController.editCancel(\''.$fact_id.'\')">[Cancel]</a>';
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
		echo '{"success" : 0, "msg" : "Factory name not specified."}';
		exit();
	}

	//Select id from table, check if there is a change in name, and if the thing has image/icon
	$sql = "SELECT name, has_image FROM list_fact WHERE id = $fact_id";
	$fact = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$old_name = $fact["name"];
	$has_image = $fact["has_image"];

	//If there is a name change, first find out if the new name is already taken
	if($name != $old_name){
		$query = $db->prepare("SELECT COUNT(*) FROM list_fact WHERE name = ?");
		$query->execute(array($name));
		$count = $query->fetchColumn();

		if($count){
			echo '{"success" : 0, "msg" : "New name is already taken."}';
			exit();
		}
		//If not taken, name will be changed later, so proceed to change picture name if it exists
		if($has_image){
			$folder = '../images/fact/';
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

	$sql = "UPDATE list_fact SET name = :name, division_name = :division_name, firstcost = COALESCE(:firstcost, DEFAULT(firstcost)), firsttimecost = COALESCE(:firsttimecost, DEFAULT(firsttimecost)), cost = COALESCE(:cost, DEFAULT(cost)), timecost = COALESCE(:timecost, DEFAULT(timecost)) WHERE id = $fact_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':division_name' => $division_name, ':firstcost' => empty($firstcost) ? null : $firstcost, ':firsttimecost' => empty($firsttimecost) ? null : $firsttimecost, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT * FROM list_fact WHERE id = $fact_id";
	$fact = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact)){
		echo '{"success" : 0, "msg" : "Factory not found."}';
		exit();
	}

	$name = $fact["name"];
	$division_name = $fact["division_name"];
	if($fact["has_image"]){
		$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
	}else{
		$filename = "no-image";
	}

	$msg = '<td><img src="/eos/images/fact/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($fact["firstcost"]/100, 2, '.', ',').'</td><td>'.$fact["firsttimecost"].' s'.'</td><td>'.'$'.number_format($fact["cost"]/100, 2, '.', ',').'</td><td>'.$fact["timecost"].' s'.'</td><td><a style="cursor:pointer;" onclick="listFactController.showEdit(\''.$fact_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT * FROM list_fact WHERE id = $fact_id";
	$fact = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact)){
		echo '{"success" : 0, "msg" : "Factory not found."}';
		exit();
	}

	$name = $fact["name"];
	$division_name = $fact["division_name"];
	if($fact["has_image"]){
		$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
	}else{
		$filename = "no-image";
	}

	$msg = '<td><img src="/eos/images/fact/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($fact["firstcost"]/100, 2, '.', ',').'</td><td>'.$fact["firsttimecost"].' s'.'</td><td>'.'$'.number_format($fact["cost"]/100, 2, '.', ',').'</td><td>'.$fact["timecost"].' s'.'</td><td><a style="cursor:pointer;" onclick="listFactController.showEdit(\''.$fact_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'ajaxupload'){
	//Load the dd uploader script
	require_once '../scripts/dd_image_uploader.php';

	$sql = "SELECT * FROM list_fact WHERE id = $fact_id";
	$fact = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($fact)){
		echo '{"success" : 0, "msg" : "Factory not found."}';
		exit();
	}
	
	$name = $fact["name"];
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));

	list($success, $new_filename) = uploadImage($filename, 1, 'gif');
	
	if($success){
		$sql = "UPDATE list_fact SET has_image = 1 WHERE id = $fact_id";
		$result = $db->query($sql);

		if($result){
			$image = '<img src="/eos/images/fact/'.$filename.'.gif?'.time().'" width="180" height="80" alt="'.$fact["name"].'" title="'.$fact["id"].' - '.$fact["name"].'" />';
			$action = "$('#list_fact_".$fact["id"]."').children('td').eq(0).html('$image')";
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