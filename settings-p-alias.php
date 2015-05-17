<?php require 'include/prehtml.php'; ?>
<?php
	$sql = "SELECT player_alias FROM players WHERE id = $eos_player_id";
	$player_alias = $db->query($sql)->fetchColumn();
	
	$sql = "SELECT action_time FROM log_limited_actions WHERE action = 'player alias' AND actor_id = $eos_player_id AND action_time > DATE_ADD(NOW(), INTERVAL -30 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		var searchTimeout, lastSearch;
		function aliasCheckInit(skipTimeout){
			jQuery("#p_alias_submit").prop("disabled", true);
			clearTimeout(searchTimeout);
			if(typeof(skipTimeout) !== "undefined" && skipTimeout){
				aliasCheck();
			}else{
				searchTimeout = setTimeout("aliasCheck();", 1000);
			}
		}
		function aliasCheck(){
			var search = document.getElementById("new_player_alias").value;
			clearTimeout(searchTimeout);
			if(search !== lastSearch){
				lastSearch = search;
				settingsController.checkPlayerAlias();
			}
		}
	</script>
	<div id="p_alias_form">
		<h3>Change Player Alias</h3>
<?php
	if(!empty($action_performed)){
		// TODO: Add item New Alias Authorization to speedup cooldown
		echo '<img src="/images/error.gif" /> Player alias can be changed once every 30 days, you last performed this action on '.$action_performed['action_time'];
	}else{
		echo '<img src="/images/success.gif" /> Player alias can be changed once every 30 days.<br /><br />';
?>
		<form onsubmit="settingsController.updatePlayerAlias();return false;">
			<input type="text" class="bigger_input" id="new_player_alias" size="26" maxlength="24" value="<?= $player_alias ?>" onKeyUp="aliasCheckInit();" onChange="aliasCheck();" />
			<div id="alias_check_response"></div>
			<br /><br />
			<input id="p_alias_submit" class="bigger_input" type="submit" value="Change Alias" disabled="disabled" />
		</form>
<?php
	}
?>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>