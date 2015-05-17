<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	$sql = "SELECT firms_positions.id, firms_positions.title, firms_positions.pid, firms_positions.ctrl_admin, firms_positions.starttime, firms_positions.endtime, firms_positions.pay_flat, firms_positions.bonus_percent, firms_positions.next_pay_flat, firms_positions.next_bonus_percent, firms_positions.next_accepted, players.id AS player_id, players.player_name, players.player_networth, players.influence, players.avatar_filename, players_extended.player_created FROM firms_positions LEFT JOIN players ON firms_positions.pid = players.id LEFT JOIN players_extended ON players.id = players_extended.id WHERE firms_positions.fid = $eos_firm_id ORDER BY firms_positions.starttime ASC";
	$employees = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if(count($employees)){
		echo '<h3>Employee Roster</h3>';
		foreach($employees as $employee){
			$esp_avatar = $employee['avatar_filename'];
			if(!$esp_avatar){ $esp_avatar = 'no-avatar.jpg'; }
			$esp_player_age = floor((time() - $employee["player_created"])/604800) + 18;
			$skip_evaluation = substr($employee['endtime'], 0, 4) == '2222' ? 1 : 0;

			echo '<div id="employee_display_'.$employee['player_id'].'" class="player_profile_wrapper">';
				echo '<div class="player_profile_avatar"><img src="/eos/images/players/',$esp_avatar,'" alt="[Avatar]" />';
				echo 'Networth: $',number_format_readable($employee['player_networth']/100),'<br />Influence: ',number_format_readable($employee['influence']);
				if($ctrl_hr_hire && !$employee['ctrl_admin']){
					echo '<br /><br /><input type="button" class="bigger_input" value="FIRE" onclick="firmController.fireEmployee(',$employee['player_id'],');" />';
				}
				echo '</div>';
				echo '<div class="player_profile_overview"><b><a href="/eos/player/',$employee['player_id'],'">',$employee['player_name'],'</a> <a href="/eos/messages.php?action=write&recipient_id='.$employee['player_id'].'"><img src="/eos/images/mail_write.png" width="24" height="24" title="Write to ',$employee['player_name'],'" /></a></b><br />Title: ',$employee['title'],'<br />Age: ',$esp_player_age,'<br /><br />';
				echo 'Employed: ', date('m/d/y', strtotime($employee['starttime'])), '<br />';
				echo 'Evaluation: ', ($skip_evaluation ? 'Never' : date('m/d/y', strtotime($employee['endtime']))), '<br />';
				echo 'Salary: $', number_format_readable($employee['pay_flat']/100), '<br />';
				echo 'Bonus: ', number_format($employee['bonus_percent'], 2, '.', ','), '%<br /><br />';
				if(!$skip_evaluation && ($ctrl_admin || $ctrl_hr_hire)){
					if($employee['pay_flat'] != $employee['next_pay_flat'] || $employee['bonus_percent'] != $employee['next_bonus_percent']){
						echo '<b>Salary/Bonus Request</b><br />';
						echo '<b>Salary: $'.number_format_readable($employee['next_pay_flat']/100).'</b><br />';
						echo '<b>Bonus: '.number_format($employee['next_bonus_percent'], 2, '.', ',').'%</b><br />';
					}
					echo '<input id="next_acceptance_',$employee['player_id'],'" type="button" class="bigger_input" value="'.($employee['next_accepted'] ? 'Next Term Accepted' : 'Next Term Denied').'" title="Click to Change" onclick="firmController.toggleNextAcceptance(',$employee['player_id'],');" />';
				}
				
				echo '</div>';
			echo '</div>';
		}
	}
?>
	<div class="clearer no_select"></div><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>