<?php require 'include/prehtml.php'; ?>
<?php
	$sql = "SELECT player_desc FROM players_extended WHERE id = $eos_player_id";
	$player_desc = $db->query($sql)->fetchColumn();
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="p_desc_form">
		<h3>Change Player Description</h3>
		Your player description can be changed any number of times, and is displayed on your player profile page.<br /><br />
		HTML tags are not allowed except for the following:<br />&lt;b&gt;<b>bold</b>&lt;/b&gt;, &lt;i&gt;<i>italics</i>&lt;/i&gt;, &lt;u&gt;<u>underline</u>&lt;/u&gt;, &lt;big&gt;<big>big</big>&lt;/big&gt;, &lt;small&gt;<small>small</small>&lt;/small&gt;, ul, ol, li<br /><br />
		<form onsubmit="settingsController.updatePlayerDesc();return false;">
			<textarea id="player_desc" class="bigger_input" rows="10" cols="80"><?= $player_desc ?></textarea>
			<br /><br />
			<input class="bigger_input" type="submit" value="Change Description" />
		</form>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>