<?php require 'include/prehtml.php'; ?>
<?php
	$sql = "SELECT player_name FROM players WHERE id = $eos_player_id";
	$player_name = $db->query($sql)->fetchColumn();
	
	$sql = "SELECT action_time FROM log_limited_actions WHERE action = 'player rename' AND actor_id = $eos_player_id AND action_time > DATE_ADD(NOW(), INTERVAL -30 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		var searchTimeout, lastSearch;
		function nameCheckInit(skipTimeout){
			jQuery("#p_name_submit").prop("disabled", true);
			clearTimeout(searchTimeout);
			if(typeof(skipTimeout) !== "undefined" && skipTimeout){
				nameCheck();
			}else{
				searchTimeout = setTimeout("nameCheck();", 1000);
			}
		}
		function nameCheck(){
			var search = document.getElementById("new_player_name").value;
			clearTimeout(searchTimeout);
			if(search !== lastSearch){
				lastSearch = search;
				settingsController.checkPlayerName();
			}
		}
	</script>
	<div id="p_name_form">
		<h3>Change Player Name</h3>
		Changing player name does not affect your fame, but it can only be done once every 30 days, so choose wisely.<br /><br />
<?php
	if(!empty($action_performed)){
		// TODO: Add item New Name Authorization to speedup cooldown
		echo '<img src="/images/error.gif" /> Player name can be changed once every 30 days, you last performed this action on '.$action_performed['action_time'];
	}else{
		echo '<img src="/images/success.gif" /> Player name can be changed once every 30 days.<br /><br />';
?>
		<form onsubmit="settingsController.updatePlayerName();return false;">
			<input type="text" class="bigger_input" id="new_player_name" size="26" maxlength="24" value="<?= $player_name ?>" onKeyUp="nameCheckInit();" onChange="nameCheck();" />
			<div id="name_check_response"></div>
			<br /><br />
			<input id="p_name_submit" class="bigger_input" type="submit" value="Change Name" disabled="disabled" />
		</form>
<?php
	}
?>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
	</div>
<?php require 'include/foot_fbox.php'; ?>