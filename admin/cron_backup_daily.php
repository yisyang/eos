<?php
error_reporting(E_STRICT);
require_once '../scripts/db/dbconnrjeos.php';

$cron_report_msg = "";
date_default_timezone_set('America/Los_Angeles');
$timestart = microtime(1);
if (!ini_get('safe_mode')) {
	set_time_limit(600);
}

/*
function export_table($table){
	global $date_ran;
	$file = '../_backup/'.$date_ran.'_'.$table.'.sql';
	$sql = "SELECT * INTO OUTFILE '$file' FROM $table";
	//$result = mysql_query($sql);
	echo $sql;
	echo '<br />';
}
*/

function daily_backup()
{
	global $cron_report_msg, $timestart, $date_ran, $dbuser, $dbpass;
	$timenow = time();
	$timeupdate = mktime(23, 59, 59, date("n", $timenow), date("j", $timenow) - 1, date("Y", $timenow));
	$timeupdate_dt = date("Y-m-d H:i:s", $timeupdate);
	$time_diff = intval(date("Gi", $timenow));
	$date_ran = intval(date("Ymd", $timenow));
	if (1) {
		//if($time_diff >= 0 && $time_diff < 10){
		$sql = "SELECT value FROM world_var WHERE name = 'su_backup_last_ran'";
		$last_ran = mysql_result(mysql_query($sql), 0);
		if ($date_ran == $last_ran) {
			$cron_report_msg = "Server update had previously been ran for the date: " . $last_ran;
			return false;
		}
		$sql = "UPDATE world_var SET value = '$date_ran' WHERE name = 'su_backup_last_ran'";
		if (!mysql_query($sql)) {
			$cron_report_msg = "Error running sql: " . $sql;
			return false;
		}

		$command = "mysqldump --add-drop-table -h localhost -u " . $dbuser . " --password=" . $dbpass . " database_name > ../../_backup/" . $date_ran . "_eos.sql";
		//echo $command;
		echo exec($command);

		/*
		export_table('es_applications');
		export_table('es_positions');
		export_table('firms_positions');
		export_table('firms');
		export_table('firms_extended');
		export_table('firm_news');
		export_table('firm_quest');
		export_table('firm_fact');
		export_table('firm_store');
		export_table('firm_store_shelves');
		export_table('firm_rnd');
		export_table('firm_stock');
		export_table('firm_tech');
		export_table('firm_wh');

		export_table('market_prod');
		export_table('messages');

		export_table('players');
		export_table('players_extended');
		export_table('player_contacts');
		export_table('player_news');
		export_table('player_stock');

		export_table('queue_build');
		export_table('queue_prod');
		export_table('queue_res');

		export_table('system_news');
		export_table('world_news');
		*/

		/*
		Lists:

		export_table('foreign_companies');
		export_table('foreign_list_goods');
		export_table('foreign_list_raw_mat');
		export_table('foreign_prod');
		export_table('foreign_raw_mat_purc');
		export_table('list_cat');
		export_table('list_fact');
		export_table('list_fact_choices');
		export_table('list_prod');
		export_table('list_quest');
		export_table('list_rnd');
		export_table('list_rnd_choices');
		export_table('list_store');
		export_table('list_store_choices');
		export_table('world_var');
		*/

		/*
		Histories:

		export_table('history_firms');
		export_table('history_players');
		export_table('history_prod');
		export_table('history_stock');
		export_table('history_stock_fine');
		*/

		/*
		Logs:

		export_table('log_firms_sold');
		export_table('log_management');
		export_table('log_market_prod');
		export_table('log_player_restarts');
		export_table('log_revenue');
		export_table('log_sales');
		export_table('log_stock');
		*/


		//Summarize event
		$timetaken = microtime(1) - $timestart;
		$sql = "UPDATE world_var SET value = '$timetaken' WHERE name = 'su_backup_last_ran_dur'";
		mysql_query($sql);
		$cron_report_msg = "Everything ran ok.";
		return true;
	} else {
		$cron_report_msg = "Job failed because it was ran at a non-scheduled time.";
		return false;
	}
}

if (daily_backup()) {
	$subject = "Cron Job - EoS - Backup - Success";
} else {
	$subject = "Cron Job - EoS - Backup - FAILED";
}
$timetaken = microtime(1) - $timestart;
$cron_report_msg .= '<br />Time taken (s): ' . $timetaken;
/*
	$sender = "admin@example.com";
	$sender_name = "Example Mailer";
	$recipient = "ADMIN EMAIL";
	$add_reply = "ADMIN EMAIL";

	$headers = "MIME-Version: 1.0\n";
	$headers .= "From: $sender_name <$sender>\n";
	$headers .= "X-Sender: <$sender>\n";
	$headers .= "X-Mailer: Example Mailer 1.0\n"; //mailer
	$headers .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
	$headers .= "Content-Type: text/html\n";
	$headers .= "Return-Path: <$add_reply>\n";
*/
//mail($recipient, $subject, $cron_report_msg, $headers);
echo $cron_report_msg;