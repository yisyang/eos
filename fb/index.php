<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
session_start();
$_SESSION['from_fbc'] = 1;

require_once '../../scripts/db/dbconn.php';
require_once '../../scripts/functions.php';


/********************************************
 * SECTION REMOVED
 *
 * Original purpose:
 *  Set FB APP ID and APP SECRET
 *
 * Variables to set:
 *  See defines below
 ********************************************/
define('YOUR_APP_ID', 123);
define('YOUR_APP_SECRET', 'abcdefg');

function objectToArray($object){
	if(!is_object($object) && !is_array($object)){
		return $object;
	}
	if(is_object($object)){
		$object = get_object_vars($object);
	}
	return array_map('objectToArray', $object);
}
   
function parse_signed_request($signed_request, $secret) {
	list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
	$sig = base64_url_decode($encoded_sig);
	$data = json_decode(base64_url_decode($payload), true);
	if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
		error_log('Unknown algorithm. Expected HMAC-SHA256');
		return null;
	}
	$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	if ($sig !== $expected_sig) {
		error_log('Bad Signed JSON signature!');
		return null;
	}
	return $data;
}

function base64_url_decode($input) {
	return base64_decode(strtr($input, '-_', '+/'));
}

function get_facebook_cookie($app_id, $app_secret){
	if(isset($_COOKIE['fbsr_' . $app_id]) && $_COOKIE['fbsr_' . $app_id] != ''){
		return get_new_facebook_cookie($app_id, $app_secret);
	}else if(isset($_COOKIE['fbs_' . $app_id]) && $_COOKIE['fbs_' . $app_id] != ''){
		return get_old_facebook_cookie($app_id, $app_secret);
	}else{
		return false;
	}
}

function get_old_facebook_cookie($app_id, $app_secret){
	$args = array();
	parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
	ksort($args);
	$payload = '';
	foreach ($args as $key => $value) {
		if ($key != 'sig') {
			$payload .= $key . '=' . $value;
		}
	}
	if (md5($payload . $app_secret) != $args['sig']) {
		return null;
	}
	return $args;   
}

function get_new_facebook_cookie($app_id, $app_secret) {
	$signed_request = parse_signed_request($_COOKIE['fbsr_' . $app_id], $app_secret);
	// $signed_request should now have most of the old elements
	$signed_request['uid'] = $signed_request['user_id']; // for compatibility
	if (!is_null($signed_request)) {
		// the cookie is valid/signed correctly
		// lets change "code" into an "access_token"
		$access_token_response = file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=$app_id&redirect_uri=&client_secret=$app_secret&code=$signed_request[code]");
		parse_str($access_token_response);
		$signed_request['access_token'] = $access_token;
		$signed_request['expires'] = time() + $expires;
	}
	return $signed_request;
}

$login_confirmed = 0;
$fb_me = null;
$fb_id = 0;
// Session based API call.
$fb_cookie = get_facebook_cookie(YOUR_APP_ID, YOUR_APP_SECRET);
if($fb_cookie){
	$fb_me_obj = json_decode(file_get_contents('https://graph.facebook.com/me?access_token='.$fb_cookie['access_token']));
	$fb_me = objectToArray($fb_me_obj);
	$fb_id = $fb_me['id'];
}

if($fb_cookie){
	$fb_id = $fb_me['id'];
}
if($fb_cookie && $fb_id){
	$ip_current = $_SERVER['REMOTE_ADDR'];

	//First check if the ip has failed too much recently
	$query = $db->prepare("SELECT COUNT(*) AS cnt, fails, banned FROM users_ip WHERE ip = ?");
	$query->execute(array($ip_current));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	$ip_in_record = $result["cnt"];
	$banned = $result["banned"];
	$fails = $result["fails"];
	if($fails > 10){
		echo '<font color="red">Too many failed attempts in 15 minutes.</font>';
		exit();
	}
	if($banned){
		echo '<font color="red">Bye.</font>';
		exit();
	}

	$query = $db->prepare("SELECT COUNT(*) FROM users WHERE fb_id = ?");
	$query->execute(array($fb_id));
	$fb_registered = $query->fetchColumn();
	if(!$fb_registered){
		// If FB user and not found in DB, add user
		$query = $db->prepare("INSERT INTO users (fb_id, verified, first_name, last_name, date_created) VALUES (?, 1, ?, ?, CURDATE())");
		$query->execute(array($fb_id, $fb_me['first_name'], $fb_me['last_name']));
		$fb_registered = 1;
	}
	
	$query = $db->prepare("SELECT users.id, users.rk, users.verified, users.first_name, users.last_name, users_login.u_id, users_login.ip_norm, users_login.last_login_norm FROM users LEFT JOIN users_login ON users.id = users_login.u_id WHERE users.fb_id = :fb_id");
	$query->execute(array(':fb_id' => $fb_id));
	$user = $query->fetch(PDO::FETCH_ASSOC);

	$id = $user['id'];
	$rk = $user['rk'];
	$verified = $user['verified'];
	$u_id_found = $user['u_id'];
	$pkey = genRandomString(120, 1);
	if($u_id_found){
		$ip_norm = $user['ip_norm'];
		$last_login_norm = strtotime($user['last_login_norm']);
		if($ip_norm == $ip_current){
			$query = $db->prepare("UPDATE users JOIN users_login ON users.id = users_login.u_id SET users.last_login = CURDATE(), users_login.ip_last = users_login.ip_curr, users_login.ip_curr = :ip_current, users_login.last_login = NOW(), users_login.last_login_norm = NOW() WHERE id = :id");
			$query->execute(array(':id' => $id, ':ip_current' => $ip_current));
		}else{
			if(time() - $last_login_norm > 864000){
				$query = $db->prepare("UPDATE users JOIN users_login ON users.id = users_login.u_id SET users.last_login = CURDATE(), users_login.ip_last = users_login.ip_curr, users_login.ip_curr = :ip_current, users_login.ip_norm = :ip_current, users_login.last_login = NOW(), users_login.last_login_norm = NOW() WHERE id = :id");
				$query->execute(array(':id' => $id, ':ip_current' => $ip_current));
			}else{
				$query = $db->prepare("UPDATE users JOIN users_login ON users.id = users_login.u_id SET users.last_login = CURDATE(), users_login.ip_last = users_login.ip_curr, users_login.ip_curr = :ip_current, users_login.last_login = NOW() WHERE id = :id");
				$query->execute(array(':id' => $id, ':ip_current' => $ip_current));
			}
		}
	}else{
		$query = $db->prepare("INSERT INTO users_login (u_id, ip_curr, ip_last, ip_norm, last_login, last_login_norm) VALUES (:id, :ip_current, :ip_current, :ip_current, NOW(), NOW())");
		$query->execute(array(':id' => $id, ':ip_current' => $ip_current));
		/********************************************
		 * SECTION REMOVED
		 *
		 * Original purpose:
		 *  Generate random access key
		 *
		 * Variables to set:
		 *    $rk = generateRandomKey(length);
		 ********************************************/
		$rk = generateRandomKey();
		$query = $db->prepare("UPDATE users SET rk = :rk, last_login = CURDATE() WHERE id = :id");
		$query->execute(array(':id' => $id, ':rk' => $rk));
	}
	
	//Add login session info
	$login_confirmed = 1;
	$_SESSION['user_is_logged_in'] = true;
	$_SESSION['eos_user_is_logged_in'] = false;
	$_SESSION['user_is_fb_user'] = true;
	$_SESSION['name'] = $fb_me['name'];
	$_SESSION['id'] = $id;
	$_SESSION['rk'] = $rk;
	
	//If has cookie, void it
	if(isset($_COOKIE['rj_persistent_u_id']) && isset($_COOKIE['rj_persistent_key'])){
		$id_old = filter_var($_COOKIE['rj_persistent_u_id'], FILTER_SANITIZE_NUMBER_INT);
		$pkey_old = filter_var($_COOKIE['rj_persistent_key'], FILTER_SANITIZE_STRING);
		if($id_old && $pkey_old){
			$query = $db->prepare("UPDATE users_login_active SET enabled = 0 WHERE u_id = ? AND u_pkey = ?");
			$query->execute(array($id_old, $pkey_old));
		}
	}

	//Generate new cookie
	$query = $db->prepare("INSERT INTO users_login_active (u_id, u_pkey, ip, login_time, last_active, enabled) VALUES (?, ?, ?, NOW(), NOW(), 1)");
	$query->execute(array($id, $pkey, $ip_current));
	setcookie('rj_persistent_u_id',$id,time() + (86400 * 365), '/');
	setcookie('rj_persistent_key',$pkey,time() + (86400 * 365), '/');
		
	// TODO: Refresh friend list
}else{
	if(isset($_SESSION['user_is_fb_user']) && $_SESSION['user_is_fb_user']){
		header( 'Location: /logout.php' );
		exit();
	}
	if(isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in']){
		$id = filter_var($_SESSION['id'], FILTER_SANITIZE_NUMBER_INT);
		$rk = filter_var($_SESSION['rk'], FILTER_SANITIZE_STRING);
		$query = $db->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND rk = ?");
		$query->execute(array($id, $rk));
		$login_confirmed = $query->fetchColumn();
		if(!$login_confirmed){
			unset($_SESSION['user_is_logged_in']);
		}
	}
	$username = "";
	if(isset($_SESSION['username'])){
		$username = filter_var($_SESSION['username'], FILTER_SANITIZE_STRING);
	}
}
if($login_confirmed){
	if(isset($_GET["redirect"])){
		$login_redirect_url = filter_var($_GET["redirect"], FILTER_SANITIZE_URL);
	}else{
		$login_redirect_url = "/eos/";
	}
	header( 'Location: '.$login_redirect_url );
	exit();
}else{
	$dbeos = rjdb_connect('eos');
	$query = $dbeos->prepare("SELECT COUNT(*) FROM players WHERE last_active > DATE_ADD(NOW(), INTERVAL -1 DAY)");
	$query->execute();
	$stats_daily_active_users = $query->fetchColumn();
}
?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - Browser-Based Business Simulation Game</title>
		<link href="../scripts/standard_fbc.css" rel="stylesheet" type="text/css">
		<script src="../scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
	</head>
	<body>
		<div id="fb-root"></div>
		<script type="text/javascript">
			window.fbAsyncInit = function() {
				FB.init({
					appId      : '<?= YOUR_APP_ID ?>',
					status     : true, 
					cookie     : true,
					xfbml      : true,
					oauth      : true
				});
				FB.getLoginStatus(function(response) {
					if (response.status === 'connected') {
						setTimeout('window.location = "http://www.example.com/eos/fb/"', 2000);
					}
				});
				FB.Event.subscribe('auth.login', function(response) {
					setTimeout('window.location = "http://www.example.com/eos/fb/"', 2000);
				});
			};
			
			(function(d){
				var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
				js = d.createElement('script'); js.id = id; js.async = true;
				js.src = "//connect.facebook.net/en_US/all.js";
				d.getElementsByTagName('head')[0].appendChild(js);
			}(document));
		</script>
		<noscript><br /><font size="4" color="#ff0000">&nbsp;&nbsp;&nbsp; This game requires javascript to function, please do not disable it.</font><br /><br /></noscript>
		<div id="eos_wrapper">
			<div style="background-color: #88aadd;margin: 0 auto;height:32px;">
				<img src="/images/ratjoy.gif" height="32px" width="44px" /><img src="/eos/images/rjeos.gif" height="32px" width="400px" />
			</div>
			<div id="eos_main">
				<div id="eos_body">
					<div style="float:left;width:400px;padding:40px;">
						<h3>Economies of Scale</h3>
						Genre: Browser-Based MMO Business Simulation<br />
						Atmosphere: Friendly / Cooperative<br />
						Status: Public Beta<br /><br />
						<img src="/images/eos.jpg" />
						<br /><br />
						<i>Description:</i><br />
						<p>Guide your company to glory by producing top quality goods, or control market price by having the highest market share, or simply sit back and invest in a profitable company and let them do the work for you.</p>
						<div style="text-align:center;"><a href="/ss_eos.php"><img src="/images/button_ss.gif" alt="Screenshots from Economies of Scale" /></a></div>
					</div>
					<div style="float:right;width:200px;padding:40px;font-size:18px;line-height:180%;text-align:center;">
						<div class="tbox_inline">
							Please login<br />to play the game.<br />
							<div class="fb-login-button" style="font-size: 11px;color: #5050c0;padding:10px 0 6px 0;">Login with Facebook</div>
						</div>
						<br />
						<div class="tbox_inline" style="font-size: 14px;">
							<i><b><?= GAME_VERSION ?></b></i><br />
							Current Active Users: <?= $stats_daily_active_users ?>
						</div>
						<br />
						<div class="tbox_inline" style="font-size: 12px;line-height:140%;text-align:left;">
							"This is not your average button-clicking game, you will need some business skills to get around."<br /><div style="text-align:right;">- Rubyton, Inc.</div>
						</div>
						<br />
						<div class="tbox_inline" style="font-size: 12px;line-height:140%;text-align:left;">
							"I really view overwhelming complexity as a positive.  It should really take some brain work to get things done."<br /><div style="text-align:right;">- Maxwell Farms</div>
						</div>
					</div>
					<div class="clearer no_select">&nbsp;</div>
				</div>
			</div>
			<div id="footer">Footer goes here</div>
		</div>
	</body>
</html>