<?php require 'include/prehtml.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Job Openings</h3>
<?php
	$sql = "SELECT firms.id AS firm_id, firms.name AS firm_name, firms.networth, es_positions.id, es_positions.title, es_positions.duration, es_positions.post_time, es_positions.pay_flat, es_positions.bonus_percent FROM es_positions LEFT JOIN firms ON firms.id = es_positions.fid ORDER BY es_positions.pay_flat DESC, es_positions.bonus_percent DESC";
	$positions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(count($positions)){
		echo '<table class="default_table"><thead><tr><td>Company</td><td>Networth</td><td>Title</td><td>Pay*</td><td>Bonus</td><td>Term**</td><td>Details</td></tr></thead><tbody>';
		foreach($positions as $position){
			$esp_id = $position['id'];
			$sql = "SELECT COUNT(*) AS cnt FROM es_applications WHERE esp_id = $esp_id AND pid = $eos_player_id";
			$applied = $db->query($sql)->fetchColumn();
			echo '<tr>
			<td><a href="/eos/firm/',$position['firm_id'],'">',$position['firm_name'],'</a></td>
			<td>$',number_format_readable($position['networth']/100),'</td>
			<td>',$position['title'],'</td>
			<td>$',number_format_readable($position['pay_flat']/100),'</td>
			<td>',$position['bonus_percent'],'%</td>
			<td>',round($position['duration'] / 7 * 12),' Months</td>';
			if($applied){
				echo '<td><a class="jqDialog" href="city-es-job-details.php" params="esp_id=',$esp_id,'"><span style="color:#008000;font-style:italic;">Applied</span></a></td></tr>';
			}else{
				echo '<td><a class="jqDialog" href="city-es-job-details.php" params="esp_id=',$esp_id,'"><input class="bigger_input" type="button" value="Details "/></a></td></tr>';
			}
		}
		echo '</tbody></table><br /><br />';
		echo '*To simplify calculations, the displayed pay rate is for each server day (52 game days).<br />**Terms are measured in game months, each 12 game months (1 game year) is equivalent to 7 server days (1 week).<br />During any term the employee will have the option to re-negotiate his/her salary for the next term.<br />Company staff with sufficient HR privileges can fire employees of lower authority or seniority at any time without a reason.<br />Pay and bonus are calculated and paid on the game server\'s daily updates.<br />Bonus is calculated based on company\'s net earnings before tax.';
	}else{
		echo 'No one is looking for an employee at this time.';
	}
?>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>