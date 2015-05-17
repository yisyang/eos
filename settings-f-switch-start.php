<?php require 'include/prehtml.php'; ?>
<?php
	$new_fid = filter_var($_POST['new_active_firm'], FILTER_SANITIZE_NUMBER_INT);

	if(!$new_fid){
		header( 'Location: /eos/' );
		exit();
	}
	
	// Confirm player is an employee
	$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE firms_positions.fid = $new_fid AND firms_positions.pid = $eos_player_id";
	$eos_player_multi_firm_count = $db->query($sql)->fetchColumn();
	
	// Switch company
	if($eos_player_multi_firm_count){
		$sql = "UPDATE players SET fid = $new_fid WHERE id = $eos_player_id";
		$db->query($sql);
	}
	
	$referrer = $_SERVER['HTTP_REFERER'];
	$return_path = preg_replace('/^([^?]*).*$/', '$1', $referrer);

	header( 'Location: '.$return_path );
	exit();
?>