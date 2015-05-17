<?php require 'include/prehtml.php'; ?>
<?php
	$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$esp_id){
		fbox_redirect('city-es-assignments.php');
	}
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="es_view_applicants_form">
		<h3>Position Offered</h3>
	<?php
		$sql = "SELECT firms.id AS firm_id, firms.name AS firm_name, firms.networth, es_positions.id, es_positions.title, es_positions.duration, es_positions.post_time, es_positions.pay_flat, es_positions.bonus_percent FROM es_positions LEFT JOIN firms ON firms.id = es_positions.fid WHERE es_positions.id = $esp_id AND es_positions.fid = $eos_firm_id ORDER BY es_positions.pay_flat DESC, es_positions.bonus_percent DESC";
		$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($position)){
			fbox_echoout('Applications for this position are unavailable to you.', 'city-es-assignments.php');
		}
		echo '<table class="default_table default_table_smallfont" style="width:670px;"><thead><tr><td>Company</td><td>Title</td><td>Pay</td><td>Bonus</td><td>Term</td></tr></thead><tbody>';
		echo '<tr><td><a href="/eos/firm/'.$position['firm_id'].'">'.$position['firm_name'].'</a></td><td>'.$position['title'].'</td><td>$'.number_format_readable($position['pay_flat']/100).'</td><td>'.$position['bonus_percent'].'%</td><td>'.round($position['duration'] / 7 * 12).' Months</td></tr>';
		echo '</tbody></table><br /><br />';
	?>
		<h3>Current Applicants</h3>
	<?php
		$sql = "SELECT players.id, players.player_name, players.player_networth, players.avatar_filename, players_extended.player_created, es_applications.cover_letter FROM es_applications LEFT JOIN players ON es_applications.pid = players.id LEFT JOIN players_extended ON players.id = players_extended.id WHERE es_applications.esp_id = $esp_id AND players.id IS NOT NULL ORDER BY es_applications.id ASC";
		$applicants = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($applicants)){
			foreach($applicants as $applicant){
				$esp_player_id = $applicant['id'];
				$esp_player_name = $applicant['player_name'];
				$esc_cover_letter = $applicant['cover_letter'];
				$esp_avatar = $applicant['avatar_filename'];
				if(!$esp_avatar){ $esp_avatar = 'no-avatar.jpg'; }
				$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE pid = $esp_player_id";
				$esp_player_multi_firm_count = $db->query($sql)->fetchColumn();
				$esp_player_age = floor((time() - $applicant["player_created"])/604800) + 18;

				echo '<div class="player_profile_wrapper"><div class="player_profile_avatar"><img src="/eos/images/players/',$esp_avatar,'" alt="[Avatar]" /></div>';
				echo '<div class="player_profile_overview"><b><a href="/eos/player/',$esp_player_id,'">',$esp_player_name,'</a> <a href="/eos/messages.php?action=write&recipient_id='.$esp_player_id.'"><img src="/eos/images/mail_write.png" width="24" height="24" title="Write to ',$esp_player_name,'" /></a></b><br />Age: ',$esp_player_age,'<br />Networth: $',number_format_readable($applicant['player_networth']/100),'<br />Active Positions: ',$esp_player_multi_firm_count;
				
				if($ctrl_hr_hire){
					echo '<br /><br /><input type="button" class="bigger_input" value="Hire Candidate" onclick="esController.hireCandidate('.$esp_id.', '.$esp_player_id.');" />';
				}
				echo '</div>';
				echo '<div class="player_profile_cover_letter">';
				echo '<i>Cover Letter / Resume:</i><br />';
				if($esc_cover_letter){
					echo nl2br(stripcslashes($esc_cover_letter));
				}else{
					echo 'N/A';
				}
				echo '</div></div>';
			}
		}else{
			echo 'No applicants yet.';
		}
	?>
		<div class="clearer"></div>
	</div>
		<br /><br />
		<a class="jqDialog" href="city-es-assignments.php"><input type="button" class="bigger_input" value="Back" /></a> 
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>