<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	require '../../scripts/db/dbconn.php';
	
	$cron_report_msg = "";
	date_default_timezone_set('America/Los_Angeles');
	$timestart = microtime(1);
	
	function update_fifteen_min(){
		global $db, $cron_report_msg;
		$timenow = time();
		$timeupdate = $timenow;
		$timeupdate_dt = date("Y-m-d H:i:s",$timeupdate);
		$time_diff = intval(date("i",$timenow));

		// Comment to disable time restriction
		// if(!(($time_diff >= 0 && $time_diff < 5) || ($time_diff >= 15 && $time_diff < 20) || ($time_diff >= 30 && $time_diff < 35) || ($time_diff >= 45 && $time_diff < 50))){
			// $cron_report_msg = "Job failed because it was ran at a non-scheduled time.";
			// return false;
		// }

		// $sql = "SELECT value FROM world_var WHERE name = 'su_last_ran_fifteen'";
		// $last_ran_hourly = 0 + $db->query($sql)->fetchColumn();
		// $last_ran_dur = $timenow - $last_ran_hourly;
		// if($last_ran_dur < 600){
			// $cron_report_msg = "Server update had previously been ran in less than 15 min: ".$last_ran_dur;
			// return false;
		// }
		$sql = "UPDATE users_ip SET fails = 0";
		if(!$db->query($sql)){
			$cron_report_msg = "Error running sql: ".$sql;
			return false;
		}
		$cron_report_msg = "Everything ran ok.";
		return true;
	}
	
	if(update_fifteen_min()){
		$subject = "Cron Job - EoS - Success - 15 min RJ";
		echo "<br /><br />Everything ran ok.";
	}else{
		$subject = "Cron Job - EoS - FAILED - 15 min RJ";

		$timetaken = microtime(1) - $timestart;
		$cron_report_msg .= '<br />Time taken (s): '.$timetaken;

		$sender = "admin@example.com";
		$sender_name = "Example Mailer";
		$recipient = "someguy@example.com";
		$add_reply = "someguy@example.com";

		$headers = "MIME-Version: 1.0\n";
		$headers .= "From: $sender_name <$sender>\n";
		$headers .= "X-Sender: <$sender>\n";
		$headers .= "X-Mailer: Example Mailer 1.0\n"; //mailer
		$headers .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
		$headers .= "Content-Type: text/html\n";
		$headers .= "Return-Path: <$add_reply>\n";

		mail($recipient, $subject, $cron_report_msg, $headers);
		echo $cron_report_msg;
	}
?>
