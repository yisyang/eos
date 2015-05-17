<?php
	$fn = $_GET['fn'];
	$fnf = filter_var($fn, FILTER_SANITIZE_STRING);
	$alert_msg = "";
	
	if(!$fn){
		$alert_msg = "Name cannot be blank.";
	}
	if($fn != $fnf){
		$alert_msg = "Please avoid using special characters.";
	}
	
	if(strlen($fn) > 24){
		$alert_msg = "Must be 24 characters or less.";
	}

	if(!$alert_msg){
		require_once 'scripts/db/dbconnrjeos.php';
		$sql = "SELECT COUNT(*) FROM firms WHERE name = '$fn'";
		$count = mysql_result(mysql_query($sql), 0);
		
		if($count){
			$alert_msg = "Name currently used.";
		}
	}
	
	if($alert_msg){
		echo '<img src="/images/error.gif" /> <font color="#ff0000">'.$alert_msg.'</font>';
	}else{
		echo 'OK';
	}
?>