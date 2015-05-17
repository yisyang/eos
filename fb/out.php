<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
session_start();
unset($_SESSION['from_fbc']);

//redirect to https
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
	$redirect= "https://www.example.com/eos/";
	header("Location:$redirect");
}else{
	$redirect= "http://www.example.com/eos/";
	header("Location:$redirect");
}
?>