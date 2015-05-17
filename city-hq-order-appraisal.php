<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Order Appraisal</h3>
	Ordering an appraisal allows you to know the most up-to-date value of your company. Please note that any unfinished production, sales, and research will not be taken into consideration. <br /><br />
	
	<div id="appraisal_results">
<?php
	if(!$ctrl_admin && ($eos_player_stock_percent < 10 || !$eos_player_is_msh)){
?>
		You are not authorized to perform this action.<br />
<?php
	}else{
		// Fetch firm stats and calculate appraisal fee
		$sql = "SELECT name, cash, level FROM firms WHERE id = $eos_firm_id";
		$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$appraisal_cost = 1000000 * pow($firm['level'], 2);
		
		if($firm['cash'] < $appraisal_cost){
			echo '<img src="/images/error.gif" /> Ordering an appraisal for '.$firm['name'].' costs $'.number_format($appraisal_cost/100,2,'.',',').', but you only have $'.number_format($firm['cash']/100,2,'.',',');
		}else{
			echo '<img src="/images/success.gif" /> Ordering an appraisal for '.$firm['name'].' costs $'.number_format($appraisal_cost/100,2,'.',',').', and you have $'.number_format($firm['cash']/100,2,'.',',').'<br /><br />';
			echo '<input type="button" class="bigger_input" value="Proceed" onclick="firmController.orderAppraisal();" />';
		}
	}
?>
	</div>
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>