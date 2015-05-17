<?php
if ($_SERVER["SERVER_NAME"] == "localhost") {
	error_reporting(E_ALL);
} else {
	error_reporting(E_STRICT);
}
session_start();

require_once '../scripts/functions.php';

$login_confirmed = 0;
if (isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in']) {

	/********************************************
	 * SECTION REMOVED
	 *
	 * Original purpose:
	 *  Authorize user and set session variables
	 *
	 * Variables to set:
	 *  $_SESSION['id'] = 123;
	 *	$_SESSION['rk'] = generateRandomKey();
	 *	$login_confirmed = true;
	 ********************************************/

}

if (!$login_confirmed) {
	unset($_SESSION['user_is_logged_in']);
	header('Location: /index.php');
	exit();
}

if (!isset($_SESSION['eos_user_is_logged_in']) || !$_SESSION['eos_user_is_logged_in']) {
	$query = $db->prepare("SELECT COUNT(*) FROM players WHERE login_id = ?");
	$query->execute(array($id));
	$eos_registered = $query->fetchColumn();
	if ($id && !$eos_registered) {
		// If user not found in EOS DB, add user
		$_SESSION['player_name'] = "[New Player]";
		$_SESSION['firm_name'] = "[New Company]";
		$query = $db->prepare("INSERT INTO players (login_id) VALUES (?)");
		$query->execute(array($id));
		$query = $db->prepare("SELECT id FROM players WHERE login_id = ?");
		$query->execute(array($id));
		$eos_player_id = $query->fetchColumn();
		$query = $db->prepare("INSERT INTO players_extended SET id = ?, player_created = ?");
		$query->execute(array($eos_player_id, time()));
	}
	$query = $db->prepare("SELECT id FROM players WHERE login_id = ?");
	$query->execute(array($id));
	$eos_player_id = $query->fetchColumn();
	$query = $db->prepare("UPDATE players SET rk = ?, last_login = CURDATE() WHERE id = ?");
	$query->execute(array($rk, $eos_player_id));
	$_SESSION['eos_user_is_logged_in'] = true;
	$_SESSION['eos_player_id'] = $eos_player_id;
} else {
	// If user session exists and contains both ID and random key, then user is logged in
	$eos_player_id = filter_var($_SESSION['eos_player_id'], FILTER_SANITIZE_NUMBER_INT);
	$rk = filter_var($_SESSION['rk'], FILTER_SANITIZE_STRING);
	if ($eos_player_id && $rk) {
		$query = $db->prepare("SELECT COUNT(*) FROM players WHERE id = ? AND rk = ?");
		$query->execute(array($eos_player_id, $rk));
		$login_confirmed = $query->fetchColumn();
		// Forgot why I did this
		if (!$login_confirmed) {
			$query = $db->prepare("UPDATE players SET rk = ? WHERE id = ?");
			$query->execute(array($rk, $eos_player_id));
			$login_confirmed = 1;
			$_SESSION['last_activity'] = time(); // Prevents premature removal
		}
	} else {
		$login_confirmed = 0;
	}

	// Send user to outer index (not included here) to login
	if (!$login_confirmed) {
		$_SESSION['eos_user_is_logged_in'] = false;
		header('Location: /index.php');
		exit();
	}
}

// Redirect to in-game index
header('Location: index.php');