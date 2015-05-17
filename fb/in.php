<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
session_start();
$_SESSION['from_fbc'] = 1;

//redirect to https
if($_SERVER['HTTPS']=="on"){
	$redirect= "https://www.example.com/eos/";
	header("Location:$redirect");
}else{
	$redirect= "http://www.example.com/eos/";
	header("Location:$redirect");
}