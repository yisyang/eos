<?php

function rjdb_connect($dbName = 'site', $environment = false)
{

	/********************************************
	 * SECTION REMOVED
	 *
	 * Original purpose:
	 *  Get DB configs
	 ********************************************/
	$dbConfig = [];

	if (!$environment) {
		if (defined('ENV')) {
			$environment = ENV;
		} else if ($_SERVER["SERVER_NAME"] == "localhost") {
			$environment = 'dev';
		} else if ($_SERVER["SERVER_NAME"] == "example.com" || $_SERVER["SERVER_NAME"] == "www.example.com") {
			$environment = 'example';
		} else {
			die("DB Connection is not configured, quitting...");
		}
	}

	// Undefined DB, something is wrong
	if (!isset($dbConfig[$environment]) || !isset($dbConfig[$environment][$dbName]))
		die("DB Connection is not configured, quitting...");

	$db = new PDO('mysql:host=' . $dbConfig[$environment][$dbName]['host'] . ';dbname=' . $dbConfig[$environment][$dbName]['name'] . ';charset=utf8', $dbConfig[$environment][$dbName]['user'], $dbConfig[$environment][$dbName]['pass']) or die ('Error connecting to DB using PDO');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$db->query("SET time_zone = 'America/Los_Angeles'");
	$db->query("SET CHARACTER SET utf8");

	return $db;
}

$db = rjdb_connect('eos');