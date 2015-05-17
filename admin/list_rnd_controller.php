<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

if(isset($_POST['action'])){
	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
}else{
	echo "{'success' : 0, 'msg' : 'Action missing.'}";
	exit();
}

if(isset($_POST['rnd_id'])){
	$rnd_id = filter_var($_POST['rnd_id'], FILTER_SANITIZE_NUMBER_INT);
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

if($action == 'add_rnd'){
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$division_name = filter_var($_POST['division_name'], FILTER_SANITIZE_STRING);
	$cost = filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_INT);
	$timecost = filter_var($_POST['timecost'], FILTER_SANITIZE_NUMBER_INT);
	$firstcost = filter_var($_POST['firstcost'], FILTER_SANITIZE_NUMBER_INT);
	$firsttimecost = filter_var($_POST['firsttimecost'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$name){
		echo '{"success" : 0, "msg" : "R&amp;D name not specified."}';
		exit();
	}
	
	$query = $db->prepare("SELECT COUNT(*) FROM list_rnd WHERE name = ?");
	$query->execute(array($name));
	$count = $query->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Name already exists."}';
		exit();
	}

	$sql = "INSERT INTO list_rnd (name, division_name, cost, timecost, firstcost, firsttimecost) VALUES (:name, :division_name, COALESCE(:cost, DEFAULT(cost)), COALESCE(:timecost, DEFAULT(timecost)), COALESCE(:firstcost, DEFAULT(firstcost)), COALESCE(:firsttimecost, DEFAULT(firsttimecost)))";
	
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':division_name' => $division_name, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost, ':firstcost' => empty($firstcost) ? null : $firstcost, ':firsttimecost' => empty($firsttimecost) ? null : $firsttimecost));
	handleCallBack($result);
}
else if($action == 'show_edit'){
	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM list_rnd WHERE id = $rnd_id";
	$rnd = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($rnd)){
		echo '{"success" : 0, "msg" : "R&amp;D not found."}';
		exit();
	}
	
	$name = $rnd["name"];
	$msg = '<td>';
	$msg .= '<div class="image_upload_control" style="float:right;width:30px;text-align:right;overflow:hidden;">';
	$msg .= '<form id="form_image_up_'.$rnd_id.'" class="form_blank" style="width:30px;" action="#nogo" method="post" enctype="multipart/form-data"><div style="position:relative;top:0;left:0;">';
	$msg .= '<input id="form_image_up_btn_'.$rnd_id.'" type="file" style="visibility:hidden;position:absolute;top:0;left:0" name="'.$rnd_id.'_up[]" multiple="multiple" />';
	$msg .= '<div id="form_image_up_ddarea_'.$rnd_id.'" class="drag_drop_area" style="cursor:pointer;border:solid 1px #ff0000;" onclick="getElementById(\'form_image_up_btn_'.$rnd_id.'\').click();">'._("UP Area").'</div>';
	$msg .= '</div><br /></form>';
	$msg .= '<div id="form_image_up_progress_'.$rnd_id.'"></div>';
	$msg .= '</div>';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_rnd_edit_'.$rnd_id.'_name" value="'.$name.'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="16" id="list_rnd_edit_'.$rnd_id.'_division_name" value="'.$rnd["division_name"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="12" id="list_rnd_edit_'.$rnd_id.'_firstcost" value="'.number_format($rnd["firstcost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_rnd_edit_'.$rnd_id.'_firsttimecost" value="'.$rnd["firsttimecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="10" id="list_rnd_edit_'.$rnd_id.'_cost" value="'.number_format($rnd["cost"]/100, 2, '.', '').'" />';
	$msg .= '</td><td>';
	$msg .= '<input type="text" size="5" id="list_rnd_edit_'.$rnd_id.'_timecost" value="'.$rnd["timecost"].'" />';
	$msg .= '</td><td>';
	$msg .= '<div id="list_rnd_edit_'.$rnd_id.'_can_res">';
		$sql = "SELECT list_rnd_choices.id, list_cat.name AS cat_name FROM list_rnd_choices LEFT JOIN list_cat ON list_rnd_choices.cat_id = list_cat.id WHERE list_rnd_choices.rnd_id = $rnd_id ORDER BY list_cat.name ASC";
		$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		foreach($rnd_choices as $rnd_choice){
			$msg .= $rnd_choice["cat_name"] .' <a style="cursor:pointer;" onclick="listRndController.deleteCanRes(\''.$rnd_id.'\',\''.$rnd_choice["id"].'\')">[-]</a><br />';
		}
		$msg .= '<select id="list_rnd_edit_'.$rnd_id.'_add_cat_id">';
		if(!empty($cats)){
			$msg .= '<option value=""> </option>';
			foreach($cats as $cat){
				$msg .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
			}
		}
		$msg .= '</select><a style="cursor:pointer;" onclick="listRndController.addCanRes(\''.$rnd_id.'\')">[Add]</a>';
	$msg .= '</div>';
	$msg .= '</td><td>';
	$msg .= '<a style="cursor:pointer;" onclick="listRndController.editConfirm(\''.$rnd_id.'\')">[OK]</a> <a style="cursor:pointer;" onclick="listRndController.editCancel(\''.$rnd_id.'\')">[Cancel]</a>';
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
		echo '{"success" : 0, "msg" : "R&amp;D name not specified."}';
		exit();
	}

	//Select id from table, check if there is a change in name, and if the thing has image/icon
	$sql = "SELECT name, has_image FROM list_rnd WHERE id = $rnd_id";
	$rnd = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$old_name = $rnd["name"];
	$has_image = $rnd["has_image"];

	//If there is a name change, first find out if the new name is already taken
	if($name != $old_name){
		$query = $db->prepare("SELECT COUNT(*) FROM list_rnd WHERE name = ?");
		$query->execute(array($name));
		$count = $query->fetchColumn();

		if($count){
			echo '{"success" : 0, "msg" : "New name is already taken."}';
			exit();
		}
		//If not taken, name will be changed later, so proceed to change picture name if it exists
		if($has_image){
			$folder = '../images/rnd/';
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

	$sql = "UPDATE list_rnd SET name = :name, division_name = :division_name, firstcost = COALESCE(:firstcost, DEFAULT(firstcost)), firsttimecost = COALESCE(:firsttimecost, DEFAULT(firsttimecost)), cost = COALESCE(:cost, DEFAULT(cost)), timecost = COALESCE(:timecost, DEFAULT(timecost)) WHERE id = $rnd_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array(':name' => $name, ':division_name' => $division_name, ':firstcost' => empty($firstcost) ? null : $firstcost, ':firsttimecost' => empty($firsttimecost) ? null : $firsttimecost, ':cost' => empty($cost) ? null : $cost, ':timecost' => empty($timecost) ? null : $timecost));
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	$sql = "SELECT * FROM list_rnd WHERE id = $rnd_id";
	$rnd = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($rnd)){
		echo '{"success" : 0, "msg" : "R&amp;D not found."}';
		exit();
	}

	$name = $rnd["name"];
	$division_name = $rnd["division_name"];
	if($rnd["has_image"]){
		$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
	}else{
		$filename = "no-image";
	}

	$sql = "SELECT list_rnd_choices.id, list_cat.name AS cat_name FROM list_rnd_choices LEFT JOIN list_cat ON list_rnd_choices.cat_id = list_cat.id WHERE list_rnd_choices.rnd_id = $rnd_id ORDER BY list_cat.name ASC";
	$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($rnd_choices)){
		$list_rnd_can_res = '';
		foreach($rnd_choices as $rnd_choice){
			$list_rnd_can_res .= $rnd_choice['cat_name'] . ',<br />';
		}
		$list_rnd_can_res = substr($list_rnd_can_res, 0, -7);
	}else{
		$list_rnd_can_res = '&lt;Nothing&gt;';
	}
	
	$msg = '<td><img src="/eos/images/rnd/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($rnd["firstcost"]/100, 2, '.', ',').'</td><td>'.$rnd["firsttimecost"].' s'.'</td><td>'.'$'.number_format($rnd["cost"]/100, 2, '.', ',').'</td><td>'.$rnd["timecost"].' s'.'</td><td><small>'.$list_rnd_can_res.'</small></td><td><a style="cursor:pointer;" onclick="listRndController.showEdit(\''.$rnd_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'edit_cancel'){
	$sql = "SELECT * FROM list_rnd WHERE id = $rnd_id";
	$rnd = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($rnd)){
		echo '{"success" : 0, "msg" : "R&amp;D not found."}';
		exit();
	}

	$name = $rnd["name"];
	$division_name = $rnd["division_name"];
	if($rnd["has_image"]){
		$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
	}else{
		$filename = "no-image";
	}

	$sql = "SELECT list_rnd_choices.id, list_cat.name AS cat_name FROM list_rnd_choices LEFT JOIN list_cat ON list_rnd_choices.cat_id = list_cat.id WHERE list_rnd_choices.rnd_id = $rnd_id ORDER BY list_cat.name ASC";
	$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($rnd_choices)){
		$list_rnd_can_res = '';
		foreach($rnd_choices as $rnd_choice){
			$list_rnd_can_res .= $rnd_choice['cat_name'] . ',<br />';
		}
		$list_rnd_can_res = substr($list_rnd_can_res, 0, -7);
	}else{
		$list_rnd_can_res = '&lt;Nothing&gt;';
	}
	
	$msg = '<td><img src="/eos/images/rnd/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($rnd["firstcost"]/100, 2, '.', ',').'</td><td>'.$rnd["firsttimecost"].' s'.'</td><td>'.'$'.number_format($rnd["cost"]/100, 2, '.', ',').'</td><td>'.$rnd["timecost"].' s'.'</td><td><small>'.$list_rnd_can_res.'</small></td><td><a style="cursor:pointer;" onclick="listRndController.showEdit(\''.$rnd_id.'\')">[Edit]</a></td>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'add_can_res'){
	$cat_id = filter_var($_POST['cat_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$cat_id){
		echo '{"success" : 0, "msg" : "Missing value."}';
		exit();
	}

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	$sql = "SELECT COUNT(*) FROM list_rnd_choices WHERE rnd_id = $rnd_id AND cat_id = $cat_id";
	$count = $db->query($sql)->fetchColumn();
	if($count){
		echo '{"success" : 0, "msg" : "Duplicate value detected, action canceled."}';
		exit();
	};
	
	$sql = "INSERT INTO list_rnd_choices (rnd_id, cat_id) VALUES ($rnd_id, $cat_id)";
	$result = $db->query($sql);
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}
	
	$sql = "SELECT list_rnd_choices.id, list_cat.name AS cat_name FROM list_rnd_choices LEFT JOIN list_cat ON list_rnd_choices.cat_id = list_cat.id WHERE list_rnd_choices.rnd_id = $rnd_id ORDER BY list_cat.name ASC";
	$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$msg = '';
	foreach($rnd_choices as $rnd_choice){
		$msg .= $rnd_choice["cat_name"] .' <a style="cursor:pointer;" onclick="listRndController.deleteCanRes(\''.$rnd_id.'\',\''.$rnd_choice["id"].'\')">[-]</a><br />';
	}
	$msg .= '<select id="list_rnd_edit_'.$rnd_id.'_add_cat_id">';
	if(!empty($cats)){
		$msg .= '<option value=""> </option>';
		foreach($cats as $cat){
			$msg .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
		}
	}
	$msg .= '</select><a style="cursor:pointer;" onclick="listRndController.addCanRes(\''.$rnd_id.'\')">[Add]</a>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'delete_can_res'){
	$can_sell_id = filter_var($_POST['can_sell_id'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "DELETE FROM list_rnd_choices WHERE id = $can_sell_id";
	$result = $db->query($sql);
	if(!$result){
		echo '{"success" : 0, "msg" : "DB failed"}';
		exit();
	}

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT list_rnd_choices.id, list_cat.name AS cat_name FROM list_rnd_choices LEFT JOIN list_cat ON list_rnd_choices.cat_id = list_cat.id WHERE list_rnd_choices.rnd_id = $rnd_id ORDER BY list_cat.name ASC";
	$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$msg = '';
	foreach($rnd_choices as $rnd_choice){
		$msg .= $rnd_choice["cat_name"] .' <a style="cursor:pointer;" onclick="listRndController.deleteCanRes(\''.$rnd_id.'\',\''.$rnd_choice["id"].'\')">[-]</a><br />';
	}
	$msg .= '<select id="list_rnd_edit_'.$rnd_id.'_add_cat_id">';
	if(!empty($cats)){
		$msg .= '<option value=""> </option>';
		foreach($cats as $cat){
			$msg .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
		}
	}
	$msg .= '</select><a style="cursor:pointer;" onclick="listRndController.addCanRes(\''.$rnd_id.'\')">[Add]</a>';

	$resp = array('success' => 1, 'html' => $msg);
	echo json_encode($resp);
	exit();
}
else if($action == 'ajaxupload'){
	//Load the dd uploader script
	require_once '../scripts/dd_image_uploader.php';

	$sql = "SELECT * FROM list_rnd WHERE id = $rnd_id";
	$rnd = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($rnd)){
		echo '{"success" : 0, "msg" : "R&amp;D not found."}';
		exit();
	}
	
	$name = $rnd["name"];
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));

	list($success, $new_filename) = uploadImage($filename, 1, 'gif');
	
	if($success){
		$sql = "UPDATE list_rnd SET has_image = 1 WHERE id = $rnd_id";
		$result = $db->query($sql);

		if($result){
			$image = '<img src="/eos/images/rnd/'.$filename.'.gif?'.time().'" width="180" height="80" alt="'.$rnd["name"].'" title="'.$rnd["id"].' - '.$rnd["name"].'" />';
			$action = "$('#list_rnd_".$rnd["id"]."').children('td').eq(0).html('$image')";
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