<?php require 'include/prehtml.php'; ?>
<?php
$sql = "SELECT title FROM firms_positions WHERE fid = 90 AND pid = $eos_player_id";
$court_position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>The Supreme Court of Econosia</h3>
	<img src="/eos/images/court.jpg" /><br />
	So long as Ratan is in power, the public will happily wait outside.<br />
	Openness is unnecessary because we all trust the government.<br /><br />
	Unhappiness is strictly forbidden.
	<br /><br />
	<?php
		if(!empty($court_position)){
			echo '"Oh, you must be the ',$court_position['title'],'. Everyone is happy right now and there are no cases."<br /><br />';
		}
	?>
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>