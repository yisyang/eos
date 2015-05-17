<?php require 'include/prehtml.php'; ?>
<?php
	$_SESSION['rb_time'] = time();
?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		function sendBugReport(){
			var message_subject = document.getElementById('message_subject').value;
			var message_body = document.getElementById('message_body').value;
			var params = {subject: message_subject, body: message_body};
			jQuery.ajax({
				type: "POST", 
				url: "report-bugs-send.php",
				data: params,
				dataType: "json",
				success: function(resp){
					if(resp.success){
						jqDialogInit('report-bugs-success.php');
					}else{
						if(typeof(resp.msg) !== 'undefined' && resp.msg){
							jAlert(resp.msg); 
						}else{
							jAlert('Something went wrong');
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError){ alert(xhr.responseText); }
			});
		}
	</script>

	<h3>Submit Bug Report</h3>
	<br />
	Please be as specific as you can and include any error messages if applicable.<br />
	If a screenshot is necessary, you may e-mail your report to ADMIN_EMAIL.<br /><br />
	Thank you for your contributions!<br /><br />
	<form onsubmit="sendBugReport();return false;">
		<table>
			<tr>
				<td>Title:</td><td><input id="message_subject" name="message_subject" type="text" size="60" maxlength="100" value="" /></td>
			</tr>
			<tr>
				<td>Details:</td><td><textarea id="message_body" name="message_body" rows="10" cols="80" maxlength="1000"></textarea></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input class="bigger_input" type="button" value="Send Report" onclick="sendBugReport();return false;" /></td>
			</tr>
		</table>
	</form>
	
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>