<?php require 'include/prehtml.php'; ?>
<?php
	$action = 'view';
	if(isset($_GET['action'])){
		$action = filter_var($_GET['action'], FILTER_SANITIZE_STRING);
	}
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}else{
		$view_type = '';
		if($action == 'view'){
			$view_type = 'received';	// received, sent, notes
		}
		if($action == 'view_contacts'){
			$view_type = 'all'; // all, online
		}
	}
	$view_type_id = 0;
	if(isset($_GET['view_type_id'])){
		$view_type_id = filter_var($_GET['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	}
?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Messages</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				<?php if($action == "view"){ ?>
				messagesController.showTable(1, '<?= $view_type ?>', <?= $view_type_id ?>, 1);
				<?php }else if($action == "view_contacts"){ ?>
				messagesController.showContacts(1, '<?= $view_type ?>', 1);
				<?php } ?>
			});
		</script>
<?php require 'include/stats.php'; ?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-messages.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="messages_submenu" class="default_submenu">
			<a href="messages.php?action=view&view_type=received" class="submenu <?= $view_type == 'received' ? 'active' : '' ?>"><img src="/eos/images/mail.png" width="36" height="36" alt="[Received]" title="Received Messages" /></a> 
			<a href="messages.php?action=view&view_type=sent" class="submenu <?= $view_type == 'sent' ? 'active' : '' ?>"><img src="/eos/images/mail_sent.png" width="36" height="36" alt="[Sent]" title="Sent Message" /></a> 
			<a href="messages.php?action=view&view_type=notes" class="submenu <?= $view_type == 'notes' ? 'active' : '' ?>"><img src="/eos/images/mail_notes.png" width="36" height="36" alt="[Notes]" title="Notes to Self" /></a> 
			&nbsp;&nbsp;&nbsp;
			<a href="messages.php?action=write" class="submenu <?= $action == 'write' ? 'active' : '' ?>"><img src="/eos/images/mail_write.png" width="36" height="36" alt="[Compose]" title="Compose Message" /></a> 
			<a href="messages.php?action=view_contacts" class="submenu <?= $action == 'view_contacts' ? 'active' : '' ?>"><img src="/eos/images/mail_contacts.png" width="36" height="36" alt="[Contacts]" title="Contacts List" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search messages" /></div>
		</div>
		<div id="messages_top_nav" class="messages_nav_container clearer"></div>
		<div id="messages_table_actions" style="display:none;">
			<input type="button" class="bigger_input" value="Check All" onclick="messagesController.checkAllMessages();" /> 
			<input type="button" class="bigger_input" value="Uncheck All" onclick="messagesController.unCheckAllMessages();" /> 
			<input type="button" class="bigger_input" value="Delete Checked" onclick="messagesController.deleteCheckedMessages();" />
		</div>
		<table id="messages_table" class="default_table"></table>
		<div class="messages_nav_container"></div>
<?php
	if($action == "write"){
		function botcheck_generate_question(){
			global $eos_player_id, $db;
			$n_begin = mt_rand(10,90);
			$n_change = mt_rand(0,9);
			$n_price = mt_rand(0,9);
			$fruit_list = array("apples","coconuts","mangos","oranges","peaches","watermelons");
			$fruit_1 = mt_rand(0,5);
			$fruit_2 = mt_rand(0,5);
			while($fruit_2 == $fruit_1){
				$fruit_2 = mt_rand(0,5);
			}
			$fruit_1 = $fruit_list[$fruit_1];
			$fruit_2 = $fruit_list[$fruit_2];
			
			$option = mt_rand(1,10);
			switch($option){
				case 1:
					$question = 'If you sold '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how much money (in $) did you make from selling '.$fruit_1.'?';
					$answer = $n_change * $n_price;
					break;
				case 2:
					$question = 'If you bought '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how much money (in $) did you spend on buying '.$fruit_1.'?';
					$answer = $n_change * $n_price;
					break;
				case 3:
					$question = 'If you bought '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how much money (in $) did you spend on buying '.$fruit_2.'?';
					$answer = 0;
					break;
				case 4:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and sold '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how many '.$fruit_1.' do you have now?';
					$answer = $n_begin - $n_change;
					break;
				case 5:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and bought '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how many '.$fruit_1.' do you have now?';
					$answer = $n_begin + $n_change;
					break;
				case 6:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and bought '.$n_change.' '.$fruit_2.' at $'.$n_price.' each, how many '.$fruit_1.' do you have now?';
					$answer = $n_begin;
					break;
				case 7:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and sold '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how many '.$fruit_1.' did you sell?';
					$answer = $n_change;
					break;
				case 8:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and bought '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how many '.$fruit_1.' did you buy?';
					$answer = $n_change;
					break;
				case 9:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and sold '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how many '.$fruit_2.' did you sell?';
					$answer = 0;
					break;
				case 10:
					$question = 'If you had '.$n_begin.' '.$fruit_1.', and bought '.$n_change.' '.$fruit_1.' at $'.$n_price.' each, how many '.$fruit_2.' did you buy?';
					$answer = 0;
					break;
				default:
					$question = 'Enter '.$n_begin.' as the answer.';
					$answer = $n_begin;
					break;
			}
			$sql = "UPDATE players_extended SET bot_check = '$answer' WHERE id = '$eos_player_id'";
			$db->query($sql);
			return $question;
		}

		$message_recipient = '';
		$message_subject = '';
		$message_body = '';
		if(isset($_GET['recipient_id'])){
			$recipient_id = filter_var($_GET['recipient_id'], FILTER_SANITIZE_NUMBER_INT);
			$sql = "SELECT player_name FROM players WHERE id = '$recipient_id'";
			$message_recipient = $db->query($sql)->fetchColumn();
		}
		if(isset($_GET['reply_to'])){
			$reply_to = filter_var($_GET['reply_to'], FILTER_SANITIZE_NUMBER_INT);
			$query = $db->prepare("SELECT messages.id, messages.subject, IFNULL(s.id, 0) AS sender_id, IFNULL(s.player_name, 'Not Found') AS sender_name, IFNULL(r.id, 0) AS recipient_id, IFNULL(r.player_name, 'Not Found') AS recipient_name FROM messages LEFT JOIN players AS s ON messages.sender = s.id LEFT JOIN players AS r ON messages.recipient = r.id WHERE messages.id = :reply_to AND (messages.recipient = :eos_player_id OR messages.sender = :eos_player_id)");
			$query_params = array(':eos_player_id' => $eos_player_id, ':reply_to' => $reply_to);
			$query->execute($query_params);
			$orig_message = $query->fetch(PDO::FETCH_ASSOC);
			if(!empty($orig_message)){
				if($orig_message["sender_id"] == $eos_player_id){
					$message_recipient = $orig_message["recipient_name"];
				}
				if($orig_message["recipient_id"] == $eos_player_id){
					$message_recipient = $orig_message["sender_name"];
				}
				$message_subject = substr("Re: ".$orig_message["subject"], 0, 100);
			}
		}
?>
		<br />
		<form id="new_message_form" onsubmit="messagesController.sendMessage();return false;">
			<h3>New Message</h3>
			<table>
				<tr>
					<td style="width:140px;">To:</td><td><input id="message_recipient" class="bigger_input" name="message_recipient" type="text" size="40" maxlength="30" value="<?= $message_recipient ?>" /> <a onclick="messagesController.showPlayerFinder('message_recipient');"><img src="images/button-magnifier-small.gif" /></a></td>
				</tr>
				<tr>
					<td>Subject:</td><td><input id="message_subject" class="bigger_input" name="message_subject" type="text" size="40" maxlength="100" value="<?= $message_subject ?>" /></td>
				</tr>
				<tr>
					<td>Body:</td><td><textarea id="message_body" class="bigger_input" name="message_body" rows="8" cols="70" maxlength="5000"><?= $message_body ?></textarea></td>
				</tr>
		<?php
			// Check Message Limit
			// If exceeded: Generate question, and plant answer in db
			
			$sql = "SELECT COUNT(*) FROM messages WHERE sender = '$eos_player_id' AND recipient != '$eos_player_id' AND sendtime > DATE_ADD(NOW(), INTERVAL -15 MINUTE)";
			$recent_messages_count = $db->query($sql)->fetchColumn();
			$recent_messages_limit = max(3,(5 * $player_level - 10));

			if($recent_messages_count > $recent_messages_limit){
				$recent_messages_question = botcheck_generate_question();
		?>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td colspan="2">Please prove that you are human by answering the following question <a class="info"><img src="images/info.png" /><span>This is because you have sent too many messages in the past 15 minutes. Your message limit increases as your player level grows. The simple question here is designed to discourage spammers from sending spam to players.</span></a>:<br /><?= $recent_messages_question ?></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td colspan="2">Your answer (as integer): <input type="text" id="bot_check_answer" class="bigger_input" name="bot_check_answer" size="5"></input></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
		<?php
			}
		?>
				<tr>
					<td colspan="2" align="center"><input class="bigger_input" type="submit" value="Send" /></td>
				</tr>
			</table>
		</form>
		<div id="new_message_sent" class="vert_middle" style="text-align:center;display:none;">
			<h3><img src="/eos/images/check.gif" />&nbsp; Your message has been successfully sent.</h3>
			<input class="bigger_input" type="button" value="Write Another Message" onclick="messagesController.anotherMessage();" />
		</div>
<?php
	}else if($action == "view_contacts"){
		// TODO: Check Friends
		// If > 200: Certainly you're not planning on adding everyone to your list?
		// Increase the limit as game expands		
?>
		<br /><br />
		<form id="update_contact_form" onsubmit="messagesController.updateContact();return false;">
			<h3>Add/Edit Contact</h3>
			<table>
				<tr>
					<td>Player Name:</td><td><input id="contact_name" class="bigger_input" type="text" size="40" maxlength="30" value="" /> <a onclick="messagesController.showPlayerFinder('contact_name');"><img src="images/button-magnifier-small.gif" /></a></td>
				</tr>
				<tr>
					<td>Brief Description:</td><td><textarea id="contact_desc" class="bigger_input" rows="3" cols="40" maxlength="200"></textarea></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input class="bigger_input" type="submit" value="Add/Edit Contact" /></td>
				</tr>
			</table>
		</form>
<?php
	}
?>
	</div>
<?php require 'include/foot.php'; ?>