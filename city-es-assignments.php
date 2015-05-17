<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Current Positions</h3>
<?php
	$sql = "SELECT es_positions.id, es_positions.title, es_positions.duration, es_positions.post_time, es_positions.pay_flat, es_positions.bonus_percent FROM es_positions WHERE es_positions.fid = $eos_firm_id ORDER BY es_positions.pay_flat DESC, es_positions.bonus_percent DESC";
	$positions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(count($positions)){
		echo '<table class="default_table"><thead><tr><td>Title</td><td>Pay*</td><td>Bonus</td><td>Term**</td><td>Details</td></tr></thead><tbody>';
		foreach($positions as $position){
			$esp_id = $position['id'];
			$sql = "SELECT COUNT(*) AS cnt FROM es_applications WHERE esp_id = $esp_id";
			$applicants = $db->query($sql)->fetchColumn();
			echo '<tr>
			<td>',$position['title'],'</td>
			<td>$',number_format_readable($position['pay_flat']/100),'</td>
			<td>',$position['bonus_percent'],'%</td>
			<td>',round($position['duration'] / 7 * 12),' Months</td>
			<td>';
			if($applicants){
				echo '<a class="jqDialog" href="city-es-view-applicants.php" params="esp_id=',$esp_id,'"><input class="bigger_input" type="button" value="'.($applicants == 1 ? 'View Applicant (1)' : 'View Applicants ('.$applicants.')').'"/></a>';
			}else{
				echo '<span style="color:#008000;font-style:italic;">No Applicant</span>';
			}
			echo ' <a class="jqDialog" href="city-es-assignment-details.php" params="esp_id='.$esp_id.'"><input class="bigger_input" type="button" value="Update Assignment" /></a>';
			echo '</td></tr>';
		}
		echo '</tbody></table><br /><br />';
		echo '*To simplify calculations, the displayed pay rate is for each server day (52 game days).<br />**Terms are measured in game months, each 12 game months (1 game year) is equivalent to 7 server days (1 week).';
	}else{
		echo 'None.';
	}
?>
	<br /><br />
	<a class="jqDialog" href="city-es-assignment-details.php"><input type="button" class="bigger_input" value="Post New Search Assignment" /></a>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>