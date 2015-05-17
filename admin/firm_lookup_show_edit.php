<?php
require 'include/sess_auth.php';
$firm_name = $_GET['firm_name'];
$_SESSION['editing_firm_name'] = $firm_name;
$firm_id = $_GET['firm_id'];
$_SESSION['editing_firm_id'] = $firm_id;

if($firm_id){
	echo '<b>Edit '.$firm_name.'</b><br />';
	echo '(ID: '.$firm_id.')<br />';
	echo '<a href="firm_basics.php">Basics</a><br />';
	echo '<a href="firm_wh.php">Warehouse</a><br />';
}else{
	echo 'Firm not found.';
}
?>