<?php
//Disabled because function is not ready yet.
exit();

if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	require '../scripts/db/dbconnrjeos.php';
	date_default_timezone_set('America/Los_Angeles');
	$timestart = microtime(1);
	
if($_GET['conf'] == 1 && $_GET['player_list']){
	$player_list = filter_var($_GET["player_list"], FILTER_SANITIZE_STRING);
	$player_list = preg_replace('/\s+/', '', $player_list);
	$target_player_array = explode(",", $player_list);
	//var_dump($target_player_array);
	//exit();
	foreach($target_player_array as $target_player_id){
		if($target_player_id = filter_var($target_player_id, FILTER_VALIDATE_INT)){
			$query = $db->prepare("SELECT id FROM firms_extended WHERE !is_public AND ceo = ?");
			$query->execute(array($target_player_id));
			$target_firms = $query->fetchAll(PDO::FETCH_ASSOC);
			
			$sql = "UPDATE firms_extended SET ceo = 0 WHERE firms_extended.ceo = $target_player_id";
			$db->query($sql) or die($sql);
			$sql = "UPDATE firms_extended SET president = 0 WHERE firms_extended.president = $target_player_id";
			$db->query($sql) or die($sql);
			
			foreach($target_firms as $target_firm){
				$target_firm_id = $target_firm['id'];
				// Delete company
				$sql = "DELETE FROM firm_fact WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firm_rnd WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firm_store WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firm_news WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firm_quest WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firm_tech WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firm_wh WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM history_firms WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM market_prod WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM market_requests WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM log_market_prod WHERE sfid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM log_market_prod WHERE bfid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM queue_build WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM queue_prod WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM queue_res WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE es_positions.*, es_applications.* FROM es_positions LEFT JOIN es_applications ON es_positions.id = es_applications.esp_id WHERE es_positions.fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "INSERT INTO player_news (pid, body, date_created) SELECT firms_positions.pid, CONCAT('Dear ',firms_positions.title,', your job with ',firms.name,' has ended because the company no longer exists.'), NOW() FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firms_positions WHERE fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "UPDATE log_management SET endtime = NOW() WHERE endtime > NOW() AND fid = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "INSERT INTO log_firms_sold (pid, fid) VALUES ($eos_player_id, $target_firm_id)";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firms_extended WHERE id = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "DELETE FROM firms WHERE id = $target_firm_id";
				$db->query($sql) or die($sql);
				$sql = "UPDATE players SET fid = 0 WHERE fid = '$target_firm_id'";
				$db->query($sql) or die($sql);
			}
			$sql = "DELETE FROM players WHERE id='$target_player_id'";
			$db->query($sql) or die($sql);
			$sql = "DELETE FROM players_extended WHERE id='$target_player_id'";
			$db->query($sql) or die($sql);
			$sql = "DELETE FROM player_contacts WHERE u_pid='$target_player_id'";
			$db->query($sql) or die($sql);
			$sql = "DELETE FROM player_news WHERE pid='$target_player_id'";
			$db->query($sql) or die($sql);
			$sql = "DELETE FROM player_stock WHERE pid='$target_player_id'";
			$db->query($sql) or die($sql);
			$sql = "DELETE FROM history_players WHERE pid='$target_player_id'";
			$db->query($sql) or die($sql);
		}
	}
	echo 'Done';
}else{
	echo 'Hmm?';
}
?>