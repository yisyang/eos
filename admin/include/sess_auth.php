<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	session_start();
	if(!(isset($_SESSION['admin_is_logged_in']) && $_SESSION['admin_is_logged_in'])){
		header( 'Location: index.php' );
		exit();
	}
?>