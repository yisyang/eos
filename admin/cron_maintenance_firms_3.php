<?php
	error_reporting(E_STRICT);
	require_once '../scripts/db/dbconnrjeos.php';
	
	//Config
	$settings_maintenance_multiplier = 0.02;
	$settings_salary_multiplier = 5000;
	$settings_world_var = 'su_firms_last_ran_3000';
	$settings_world_var_time = 'su_firms_last_ran_3000_dur';
	$settings_firms_lower_limit = 20000;
	$settings_firms_upper_limit = 30001;
	$settings_updater_title = 'Cron Job - EoS - Firms';
	
	$cron_report_msg = "";
	date_default_timezone_set('America/Los_Angeles');
	$timestart = microtime(1);
	if(!ini_get('safe_mode')){
		set_time_limit(600);
	}

	include('cron_maintenance_firms_function.php');
?>