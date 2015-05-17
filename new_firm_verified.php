<?php require 'include/prehtml.php'; ?>
<?php
//Find out if firm is STILL a new firm
$sql = "SELECT new_firm FROM firms WHERE id='$eos_firm_id'";
$new_firm = mysql_result(mysql_query($sql), 0);
if($new_firm == 2){
	//Add $200,000.00, remove newbie status
	$sql = "UPDATE firms SET new_firm = 0, cash = 20000000 WHERE id = '$eos_firm_id'";
	mysql_query($sql);
}

//Into the real world!
header( 'Location: index.php' );
?>