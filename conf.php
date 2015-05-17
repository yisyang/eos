<?php
define('SCRIPT_VERSION', '0.81.00');
define('GAME_VERSION', 'Open Source 1.0');

$DIRNAME = dirname(__FILE__);
if (strstr($DIRNAME, '/example.com/')) {
	define('ENV', 'example');
	define('GAME_TITLE', 'EoS on Example');
	define('SERVER_BASEURL', 'http://www.example.com/');
	define('SERVER_BASEDIR', strstr($DIRNAME, '/eos/', true) . '/');
	define('GAME_BASEURL', SERVER_BASEURL . 'eos/');
	define('GAME_BASEDIR', SERVER_BASEDIR . 'eos/');
} else if (strstr($DIRNAME, '/dev/')) {
	define('ENV', 'stage');
	define('GAME_TITLE', 'EoS on Staging');
	define('SERVER_BASEURL', 'http://eos.stage/');
	define('SERVER_BASEDIR', strstr($DIRNAME, '/eos/', true) . '/');
	define('GAME_BASEURL', SERVER_BASEURL . 'eos/');
	define('GAME_BASEDIR', SERVER_BASEDIR . 'eos/');
} else if (strstr($DIRNAME, '\\web\\')) {
	define('ENV', 'dev');
	define('GAME_TITLE', 'EoS on Local Windows Machine');
	define('SERVER_BASEURL', 'http://localhost/');
	define('SERVER_BASEDIR', strstr($DIRNAME, '\\eos\\', true) . '\\');
	define('GAME_BASEURL', SERVER_BASEURL . 'eos/');
	define('GAME_BASEDIR', SERVER_BASEDIR . 'eos\\');
} else {
	exit('Server configuration error.');
}