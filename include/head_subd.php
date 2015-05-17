<?php
	if($settings_narrow_screen){
		echo '<link href="/eos/scripts/standard_fbc.css" rel="stylesheet" type="text/css">';
	}else{
		echo '<link href="/eos/scripts/standard.css" rel="stylesheet" type="text/css">';
	}
?>
		<link href="../scripts/jAlerts/jquery.alerts.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/ui-lightness/jquery-ui-1.10.1.css" rel="stylesheet" type="text/css" />
		<script src="../scripts/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../scripts/jquery-ui-1.10.1.min.js" type="text/javascript"></script>
		<script src="../scripts/jAlerts/jquery.alerts.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			// jQuery(document).ready();

			//GA
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-28808097-1']);
			_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
	</head>
	<body>
		<div id="eos_header">
			<a href="/"><img src="/images/ratjoy.gif" height="32" width="44" alt="RatJoy.com" /></a><a href="../index.php"><img src="../images/rjeos.gif" height="32" width="400" alt="<?= GAME_TITLE ?>" /></a>
			<?php
				if(isset($_SESSION['eos_user_is_logged_in'])){
			?>
					<a class="link_w" style="font-size: 14px;color: #e8e8e8;" href="javascript:history.go(-1)">(Previous page)</a>
					<span style="float:right;text-align:right;margin-right:10px;height:32px;">
						<a href="http://www.example.com/forum/"><img src="/images/forum.png" /> <span class="link_w" style="font-size: 14px;color: #e8e8e8;vertical-align:middle;">Forum</span></a> &nbsp;&nbsp;
						<a href="../settings.php"><img src="/images/settings.png" /> <span class="link_w" style="font-size: 14px;color: #e8e8e8;vertical-align:middle;">Settings</span></a>
			<?php
					if(!isset($_SESSION["user_is_fb_user"])){
						echo ' &nbsp;&nbsp; <a href="/logout.php"><img src="/images/logout.png" /> <span class="link_w" style="font-size: 14px;color: #e8e8e8;vertical-align:middle;">Logout</span></a>';
					}
			?>
					</span>
			<?php
				}
			?>
		</div>
		<div id="eos_wrapper">
			<?php
				if($settings_narrow_screen){
					echo '<div id="eos_header_compact"><a class="link_w" style="font-size: 14px;color: #e8e8e8;" href="/eos/">(Return to the game)</a></div>';
				}
			?>