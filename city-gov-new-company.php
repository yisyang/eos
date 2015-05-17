<?php require 'include/prehtml.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		var searchTimeout, lastSearch;
		function nameCheckInit(skipTimeout){
			jQuery("#p_name_submit").prop("disabled", true);
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
	<div id="f_new_form">
		<h3>New Company</h3>
<?php
	$sql = "SELECT COUNT(*) FROM firms_positions WHERE firms_positions.pid = $eos_player_id AND ctrl_admin";
	$eos_player_multi_firm_count = $db->query($sql)->fetchColumn();
	if($eos_player_multi_firm_count){
		$f_new_cost = 200000000 * pow(5,$eos_player_multi_firm_count);
		$f_new_proceed = 0;
		if($player_cash >= $f_new_cost && $eos_player_multi_firm_count < 10){
			echo 'Hi Boss, we are glad that the Small Business Stimulus Act is working, as demonstrated by your success.<br />Land usage for <b>your next company will cost $',number_format_readable($f_new_cost/100),'.</b><br /><br />Would you like to name your company now?<br /><br />';
			$f_new_proceed = 1;
		}else if($eos_player_multi_firm_count >= 10){
			echo 'Sorry, the Anti-Trust Act of 2013 forbids players from starting new companies while holding more than 10 positions of authority.<br /><br />';
		}else{
			echo 'Land usage for <b>your next company will cost $',number_format_readable($f_new_cost/100),'.</b><br /><br />Unfortunately your personal account seems to be short on cash.<br /><br />';
		}
	}else{
		echo 'Since this will be your first company, the application fee is waived under the Small Business Stimulus Act of 2012. Your company will be given free non-transferable usage rights to a dozen plots of land. Before we go further, what would you like to name your company?<br />(e.g. Example, Inc.)<br /><br />';
		$f_new_proceed = 1;
	}
	if($f_new_proceed){
?>
		<form onsubmit="firmController.startNewCompany();return false;">
			<input type="text" class="bigger_input" id="new_firm_name" size="26" maxlength="24" value="" onKeyUp="nameCheckInit();" onChange="nameCheck();" />
			<div id="name_check_response"></div>
			<br /><br />
			<input id="f_name_submit" class="bigger_input" type="submit" value="Start Company" disabled="disabled" />
		</form>
<?php
	}
?>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
	</div>
<?php require 'include/foot_fbox.php'; ?>