<?php require 'include/prehtml.php'; ?>
<?php
	$sql = "SELECT name, cash, level FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$firm_name = $firm['name'];
	$firm_cash = $firm['cash'];
	$rename_cost = 10000 * pow(3, $firm['level']);
	
	$sql = "SELECT action_time FROM log_limited_actions WHERE action = 'firm rename' AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -30 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		var searchTimeout, lastSearch;
		function nameCheckInit(skipTimeout){
			jQuery("#f_name_submit").prop("disabled", true);
			clearTimeout(searchTimeout);
			if(typeof(skipTimeout) !== "undefined" && skipTimeout){
				nameCheck();
			}else{
				searchTimeout = setTimeout("nameCheck();", 1000);
			}
		}
		function nameCheck(){
			var search = document.getElementById("new_firm_name").value;
			clearTimeout(searchTimeout);
			if(search !== lastSearch){
				lastSearch = search;
				firmController.checkCompanyName();
			}
		}
	</script>
	<div id="f_name_form">
		<h3>Change Company Name</h3>
		Changing your company name significantly decreases its fame (-1 fame level).<br />
		In addition, a DBA change can only be filed once every 30 days, so choose wisely.<br /><br />
<?php
	if(!empty($action_performed)){
		// TODO: Add item New Name Authorization to speedup cooldown
		echo '<img src="/images/error.gif" /> Company name can be changed once every 30 days, you last performed this action on '.$action_performed['action_time'];
	}else if($rename_cost > $firm_cash){
		echo '<img src="/images/error.gif" /> The process costs $'.number_format($rename_cost/100,2,'.',',').', but you only have $'.number_format($firm_cash/100,2,'.',',');
	}else{
		echo '<img src="/images/success.gif" /> Company name can be changed once every 30 days.<br />';
		echo '<img src="/images/success.gif" /> The process costs $'.number_format($rename_cost/100,2,'.',',').', and you have $'.number_format($firm_cash/100,2,'.',',').'<br /><br />';
?>
		<form onsubmit="firmController.updateCompanyName();return false;">
			<input type="text" class="bigger_input" id="new_firm_name" size="26" maxlength="24" value="<?= $firm_name ?>" onKeyUp="nameCheckInit();" onChange="nameCheck();" />
			<div id="name_check_response"></div>
			<br /><br />
			<input id="f_name_submit" class="bigger_input" type="submit" value="Change Name" disabled="disabled" />
		</form>
<?php
	}
?>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>