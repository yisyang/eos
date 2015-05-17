<?php
require 'include/sess_auth.php';
require_once '../scripts/db/dbconnrjeos.php';

$firm_name = $_GET['firm_name'];

if($firm_name){
	$query = $db->prepare("SELECT * FROM firms WHERE name LIKE :firm_name");
	$query->execute(array(':firm_name' => '%'.$firm_name.'%'));
	$firms = $query->fetchAll(PDO::FETCH_ASSOC);
	$count = count($firms);

	if($count>0){
		$msg = '<select id="firm_lookup_select" onChange="firm_lookup_show_edit();">';
		$msg .= '<option value="">- Select Firm -</option>';
		foreach($firms as $firm){
			$msg .= '<option value="'.$firm["id"].'">';
			$msg .= $firm["name"];
			$msg .= '</option>';
		}
		$msg .= '</select>';
		echo $msg;
		exit();
	}else{
		echo 'Firm Not Found';
	}
}else{
	echo 'Missing Input';
}
?>