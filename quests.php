<?php require 'include/prehtml.php'; ?>
<?php require 'include/functions.php'; ?>
<?php require 'include/quest_types.php'; ?>
	<?php
		// Add new quests
		$sql = "SELECT quests_available FROM firms WHERE id = $eos_firm_id";
		$quests_available = $db->query($sql)->fetchColumn();
		if($quests_available){
			$quests_added = 0;
			for($i=0;$i<$quests_available;$i++){
				if(add_quest($eos_firm_id)){
					$quests_added += 1;
				}
			}
			$sql = "UPDATE firms SET quests_available = quests_available - $quests_added WHERE id = $eos_firm_id";
			$db->query($sql);
		}
		
		// Global vars for validate existing quests
		$quest_type_image = "";
		$quest_objective_message = "";
		$quest_progress_message = "";
		$quest_endtime = "";
		$reward_cash = 0;
		$reward_fame = 0;
		$quest_validated = 0;
		$quest_allow_supply = 0;
		
		// Validate 5 completed quests (placed above ongoing quests so just completed quests don't get counted twice)
		$completed_quests = array();
		$sql = "SELECT id FROM firm_quest WHERE fid = $eos_firm_id AND completed AND !failed ORDER BY endtime DESC LIMIT 0, 5";
		$completed_quest_ids = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($completed_quest_ids as $completed_quest_id){
			if(validate_completed_quest($completed_quest_id['id'])){
				$completed_quests[] = array('id' => $completed_quest_id['id'], 'quest_type_image' => $quest_type_image, 'quest_objective_message' => $quest_objective_message, 'quest_endtime' => $quest_endtime, 'reward_cash' => $reward_cash, 'reward_fame' => $reward_fame);
			}else if($quest_validated){
				$completed_quests[] = array('id' => $completed_quest_id['id'], 'quest_type_image' => $quest_type_image, 'quest_objective_message' => $quest_objective_message, 'quest_endtime' => $quest_endtime, 'reward_cash' => $reward_cash, 'reward_fame' => $reward_fame);
			}else{
				$completed_quests[] = array('id' => $completed_quest_id['id'], 'quest_type_image' => 'menu-quests', 'quest_objective_message' => 'Error: Quest not found.', 'quest_endtime' => 2147485547, 'reward_cash' => 0, 'reward_fame' => 0);
			}
		}
		
		// Validate on-going quests
		$ongoing_quests = array();
		$sql = "SELECT id FROM firm_quest WHERE fid = $eos_firm_id AND !completed AND !failed ORDER BY endtime ASC";
		$ongoing_quest_ids = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($ongoing_quest_ids as $ongoing_quest_id){
			if(validate_quest($ongoing_quest_id['id'])){
				$ongoing_quests[] = array('id' => $ongoing_quest_id['id'], 'pid' => $quest_pid, 'quest_type_image' => $quest_type_image, 'quest_objective_message' => $quest_objective_message, 'quest_progress_msg' => '<font color="#008800"><b>Just Completed!</b></font>', 'quest_endtime' => $quest_endtime, 'reward_cash' => $reward_cash, 'reward_fame' => $reward_fame, 'quest_allow_supply' => 0);
			}else if($quest_validated){
				$ongoing_quests[] = array('id' => $ongoing_quest_id['id'], 'pid' => $quest_pid, 'quest_type_image' => $quest_type_image, 'quest_objective_message' => $quest_objective_message, 'quest_progress_msg' => $quest_progress_message, 'quest_endtime' => $quest_endtime, 'reward_cash' => $reward_cash, 'reward_fame' => $reward_fame, 'quest_allow_supply' => $quest_allow_supply);
			}else{
				$ongoing_quests[] = array('id' => $ongoing_quest_id['id'], 'pid' => $quest_pid, 'quest_type_image' => 'menu-quests', 'quest_objective_message' => 'Error: Quest not found.', 'quest_progress_msg' => 'N/A', 'quest_endtime' => time(), 'reward_cash' => 0, 'reward_fame' => 0, 'quest_allow_supply' => 0);
			}
		}
	?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Quests</title>
<?php require 'include/head.php'; ?>
<?php require 'include/stats.php'; ?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-quests.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<table class="default_table">
		<?php
		$timenow = time();
		foreach($ongoing_quests as $ongoing_quest){
			echo '<tr id="ongoing_quest_',$ongoing_quest['id'],'">';
			echo '<td width="120" style="border-right: 0;background-color:#ffd553;">';
			echo '<img src="images/',$ongoing_quest['quest_type_image'],'.gif" />';
			echo '</td>';
			echo '<td class="quests_table_content_td">';
			echo 'Objective: ',$ongoing_quest['quest_objective_message'];
			if($ongoing_quest['quest_allow_supply'] && $ongoing_quest['quest_endtime'] >= $timenow){
				$sql = "SELECT COUNT(*) FROM market_prod WHERE pid = ".$ongoing_quest['pid'];
				$pid_available_b2b = $db->query($sql)->fetchColumn();
				$sql = "SELECT COUNT(*) FROM firm_wh WHERE fid = $eos_firm_id AND pid = ".$ongoing_quest['pid']." AND pidn > 0";
				$pid_available_wh = $db->query($sql)->fetchColumn();
				echo ' 
				<a class="jqDialog" href="quests-prod-supply-confirm.php" params="fqid=',$ongoing_quest['id'],'" title="Supply Quest Items"><input type="button" class="bigger_input" value="Fulfill"',($pid_available_wh ? '' : ' disabled="disabled"'),' /></a> 
				<a href="/eos/market.php?view_type=prod&view_type_id=',$ongoing_quest['pid'],'" title="Purchase on B2B"><input type="button" class="bigger_input" value="B2B"',($pid_available_b2b ? '' : ' disabled="disabled"'),' /></a> 
				<a class="jqDialog" href="/eos/market-add-request.php?no_redirect=1&pid=',$ongoing_quest['pid'],'" title="Add a Purchase Request"><input type="button" class="bigger_input" value="REQ" /></a>';
			}
			echo '<br /><br />';
			echo 'Progress: ',$ongoing_quest['quest_progress_msg'],'<br /><br />';
			echo 'Reward: <img src="images/money.gif" title="Cash" /> $',number_format($ongoing_quest['reward_cash']/100, 2, '.', ','),' <img src="images/fame.gif" title="Fame" /> ',number_format($ongoing_quest['reward_fame'],0,'.',','),'<br /><br />';
			if($ongoing_quest['quest_endtime'] >= $timenow){
				echo 'Time Left: ',sec2hms($ongoing_quest['quest_endtime'] - $timenow),' (Expires on ',date("F j, Y, g:i A", $ongoing_quest['quest_endtime']),')';
			}else{
				echo 'Expired on: ',date("F j, Y, g:i A",$ongoing_quest['quest_endtime']);
			}
			echo '</td>';
			echo '</tr>';
		}
		foreach($completed_quests as $completed_quest){
			echo '<tr>';
			echo '<td width="120" style="border-right: 0;background-color:#b0b0b0;">';
			echo '<img src="images/',$completed_quest['quest_type_image'],'.gif" />';
			echo '</td>';
			echo '<td class="quests_table_content_td" style="background-color:#f0f0f0;color:#606060;">';
			echo 'Objective: ',$completed_quest['quest_objective_message'],'<br /><br />';
			echo 'Reward: <img src="images/money.gif" title="Cash" /> $',number_format($completed_quest['reward_cash']/100, 2, '.', ','),' <img src="images/fame.gif" title="Fame" /> ',number_format($completed_quest['reward_fame'],0,'.',','),'<br /><br />';
			echo 'Completed on: ',date("F j, Y, g:i A",$completed_quest['quest_endtime']);
			echo '</td>';
			echo '</tr>';
		}
		?>
	</table>
<?php require 'include/foot.php'; ?>