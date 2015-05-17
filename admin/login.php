<?php
session_start();
require_once '../scripts/db/dbconnrjeos.php';
require '../../scripts/functions.php';

$username = filter_var($_GET['username'], FILTER_SANITIZE_STRING);
/********************************************
 * SECTION REMOVED
 *
 * Original purpose:
 *  Encrypt posted password
 ********************************************/
$password = encryptPassword($_GET['password']);

if($username && $password){
	$query = $db->prepare("SELECT * FROM admin WHERE username = ? and password = ?");
	$query->execute(array($username, $password));
	$admin = $query->fetch(PDO::FETCH_ASSOC);

	if(empty($admin)){
		echo '<font color="red">Incorrect User/Pass.</font>';
		exit();
	}else{
		$id = $admin['id'];
		/********************************************
		 * SECTION REMOVED
		 *
		 * Original purpose:
		 *  Generate random access key
		 *
		 * Variables to set:
		 *    $rk = generateRandomKey(length);
		 ********************************************/
		$rk = generateRandomKey();
		$access_last = $admin['access_last'];
		$ip_last = $admin['ip_last'];
		$ip_current = $_SERVER['REMOTE_ADDR'];

		$sql = "UPDATE admin SET rk = '$rk', access_last = access_current, ip_last = ip_current, access_current = NOW(), ip_current = '$ip_current' WHERE id='$id'";
		$db->query($sql);

		$_SESSION['admin_is_logged_in'] = true;
		$_SESSION['admin_id'] = $id;
		$_SESSION['admin_username'] = $username;
		$_SESSION['admin_rk'] = $rk;
		$_SESSION['access_last'] = $access_last;
		$_SESSION['ip_last'] = $ip_last;

		echo "OK";
		exit();
	}
}else{
	echo '<font color="red">Missing User/Pass.</font>';
}
?>