<?php require 'include/prehtml.php'; ?>
<?php
	$message_id = filter_var($_POST['message_id'], FILTER_SANITIZE_NUMBER_INT);
	if(!$message_id){
		echo 'Message not found.';
		exit();
	}
?>
<?php require 'include/stats_fbox.php'; ?>
<?php
		// Populate message
		$sql = "SELECT messages.*, IFNULL(s.id, 0) AS sender_id, IFNULL(s.player_name, '<i>Not Found</i>') AS sender_name, IFNULL(r.id, 0) AS recipient_id, IFNULL(r.player_name, '<i>Not Found</i>') AS recipient_name FROM messages LEFT JOIN players AS s ON messages.sender = s.id LEFT JOIN players AS r ON messages.recipient = r.id WHERE (messages.recipient = $eos_player_id OR messages.sender = $eos_player_id) AND messages.id = '$message_id'";
		$message = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(empty($message)){
			echo 'Message not found.';
			exit();
		}

		if(!$message['recipient_read'] && $message['recipient'] == $eos_player_id){
			$sql = "UPDATE messages SET recipient_read = 1 WHERE id = '$message_id'";
			$db->query($sql);
		}
?>
		<table class="message_table">
			<thead>
				<tr>
					<td width="80px">From: </td><td width="260px"><a href="/eos/player/<?= $message['sender'] ?>"><?= $message['sender_name'] ?></a></td>
					<td width="80px">To: </td><td><?= $message['recipient_name'] ?></td>
				</tr>
				<tr>
					<td>Date: </td><td colspan="3"><?= date("F j, Y \a\\t g:i a", strtotime($message['sendtime'])) ?></td>
				</tr>
				<tr>
					<td>Subject: </td><td colspan="3"><?= $message['subject'] ?></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="4">
						<div style="min-height: 100px;"><?= nl2br($message['body']) ?></div><br />
						<?php
							if($message['sender'] != $eos_player_id || $message['recipient'] != $eos_player_id){
								echo '<a href="messages.php?action=write&reply_to='.$message['id'].'"><input type="button" class="bigger_input" value="Reply" /></a> ';
							}
							echo ' <a onclick="messagesController.deleteMessage('.$message['id'].');jQuery(\'#jq-dialog-modal\').dialog(\'close\');"><input type="button" class="bigger_input" value="Delete and Close" /></a>';
							
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<?php require 'include/foot_fbox.php'; ?>