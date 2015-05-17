<?php
	// Update script version to force reload of script files
	$script_ver = "0.90.02";

	if($settings_narrow_screen){
		echo '<link href="/eos/scripts/standard_fbc.css?ver='.$script_ver.'" rel="stylesheet" type="text/css">';
	}else{
		echo '<link href="/eos/scripts/standard.css?ver='.$script_ver.'" rel="stylesheet" type="text/css">';
	}
?>
		<link href="scripts/ui-lightness/jquery-ui-1.10.1.css" rel="stylesheet" type="text/css" />
		<link href="scripts/jAlerts/jquery.alerts.css" rel="stylesheet" type="text/css" />
		<script src="scripts/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="scripts/jquery-ui-1.10.1.min.js" type="text/javascript"></script>
		<script src="scripts/jAlerts/jquery.alerts.min.js" type="text/javascript"></script>
		<script src="scripts/jquery.sparkline.min.js" type="text/javascript"></script>
		<script src="scripts/moment.min.js" type="text/javascript"></script>
		<script src="scripts/eos_common.js?ver=<?= $script_ver ?>" type="text/javascript"></script>
		<script type="text/javascript">
			<?php if($settings_narrow_screen) echo 'rjJQModalWidth = 710;'; ?>
			
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
			<a href="/"><img src="/images/ratjoy.gif" height="32" width="44" alt="RatJoy.com" /></a><a href="index.php"><img src="images/rjeos.gif" height="32" width="400" alt="<?= GAME_TITLE ?>" /></a>
			<?php
				if(isset($_SESSION['eos_user_is_logged_in'])){
			?>
					<span style="float:right;text-align:right;margin-right:10px;height:32px;line-height:24px;vertical-align:middle;">
					<?php
						if(count($EOS_PLAYER_FIRMS) > 1){
							echo '<form style="display:inline;vertical-align: middle;" action="/eos/settings-f-switch-start.php" method="POST">';
							echo '<select style="padding: 3px;margin-right: 10px;" id="new_active_firm" name="new_active_firm" onchange="this.form.submit();">';
							foreach($EOS_PLAYER_FIRMS as $firm){
								if($firm['fid'] == $eos_firm_id){
									echo '<option value="',$firm['fid'],'" selected="selected">',$firm['name'],'</option>';
								}else{
									echo '<option value="',$firm['fid'],'">',$firm['name'],'</option>';
								}
							}
							echo '</select>';
							echo '</form>';
						}
					?>
						<a href="http://www.example.com/forum/"><img src="/images/forum.png" /> <span class="link_w" style="font-size: 14px;color: #e8e8e8;vertical-align:middle;">Forum</span></a> &nbsp;&nbsp;
						<a href="settings.php"><img src="/images/settings.png" /> <span class="link_w" style="font-size: 14px;color: #e8e8e8;vertical-align:middle;">Settings</span></a>
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