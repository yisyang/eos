<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
session_start();

require_once 'scripts/db/dbconnrjeos.php';
$sql = "SELECT COUNT(*) AS cnt FROM players WHERE last_active > DATE_ADD(NOW(), INTERVAL -1 MINUTE)";
$stats_1m_active_users = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$sql = "SELECT COUNT(*) AS cnt FROM players WHERE last_active > DATE_ADD(NOW(), INTERVAL -15 MINUTE)";
$stats_15m_active_users = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$sql = "SELECT COUNT(*) AS cnt FROM players WHERE last_active > DATE_ADD(NOW(), INTERVAL -1 DAY)";
$stats_daily_active_users = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$sql = "SELECT COUNT(*) AS cnt FROM players WHERE last_active > DATE_ADD(NOW(), INTERVAL -7 DAY)";
$stats_weekly_active_users = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Browser-Based Business Simulation Game</title>
		<link href="scripts/standard_fbc.css" rel="stylesheet" type="text/css">
		<script src="scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
	</head>
	<body>
		<div id="eos_wrapper">
			<div style="background-color: #88aadd;margin: 0 auto;height:32px;">
				<img src="/images/ratjoy.gif" height="32px" width="44px" /><a href="/eos/"><img src="/eos/images/rjeos.gif" height="32px" width="400px" /></a>
			</div>
			<div id="eos_main">
				<div id="eos_body">
					<div style="width:500px;margin: 0 auto;padding:40px;font-size:18px;line-height:180%;text-align:center;">
						<div class="tbox_inline" style="font-size: 14px;">
							<h3>EoS Player Stats (<i><b><?= GAME_VERSION ?></b></i>)</h3>
							Active Users in 1 Minute: <?= $stats_1m_active_users['cnt'] ?><br />
							Active Users in 15 Minutes: <?= $stats_15m_active_users['cnt'] ?><br />
							Active Users in 24 Hours: <?= $stats_daily_active_users['cnt'] ?><br />
							Active Users in 7 Days: <?= $stats_weekly_active_users['cnt'] ?><br />
						</div>
					</div>
					<div class="clearer no_select">&nbsp;</div>
				</div>
			</div>
			<div id="footer">Footer goes here</div>
		</div>
	</body>
</html>