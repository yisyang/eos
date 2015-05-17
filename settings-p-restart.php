<?php require 'include/prehtml.php'; ?>
<?php
	$_SESSION['p_restart_time'] = time();
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="restart_form">
		<h3>Restart Player</h3>
		What? You want to restart your EoS life? Is this even possible?<br /><br />
		Please note restarting a player is an obnoxiously difficult task, so here are the requirements:<br />
		<ol><li>Your player's networth must be under $10 billion ($10,000,000,000.00).</li><li>You must not own any stock.</li><li>You can restart only if you have not restarted in the previous 24 hours.</li></ol><br />
		After restarting, you will start with:<br />
		<ul><li>The same player name.</li><li>$0 in the player's bank account.</li><li>A new private company with $10,000,000 in cash (including $9,000,000 from loan).</li><li>Some research in 5 random products with a base value between $0.50 to $20.00.</li><li>No buildings.</li></ul>
		<br /><br />
		Please note this action is not undo-able, to proceed, type <b>RESTART</b> in the box below, then click the confirmation button.<br /><br />
		<div style="text-align:center;line-height:200%;">
			<form onsubmit="settingsController.restartPlayer();return false;">
				<input class="bigger_input" id="restart_confirmation" name="restart_confirmation" type="text" size="10" maxlength="10" value="" /><br />
				<input class="bigger_input" type="submit" value="Confirm Restart" />
			</form>
		</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
	</div>
<?php require 'include/foot_fbox.php'; ?>