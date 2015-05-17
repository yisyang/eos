<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php
	$sql = "SELECT name, networth FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		echo "Error encountered, company cannot be found.";
		exit();
	}else{
		$firm_name = $firm['name'];
		$firm_networth = $firm['networth'];
		$f_sell_price = 0.95 * $firm_networth;
	}
	$_SESSION['f_sell_time'] = time();
?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	if(!$ctrl_admin){
?>
		You are not authorized to perform this action.<br />
<?php
	}else if($eos_firm_is_public){
?>

		<?= $firm_name ?> is a publicly traded company, so you cannot simply sell it. You can, however, sell off all your shares in the company and let someone else take control.
<?php
	}else{
?>
	<div id="f_sell_form">
		<h3>Sell Company</h3>
		Selling <?= $firm_name ?> will convert its actual networth (less 5% commission and fees) to cash, which will then be deposited into your personal account.<br /><br />
		You will get <b>approximately $<?= number_format_readable($f_sell_price/100) ?></b> from the sales, actual figures may vary based on a third-party appraisal at the time of sale.<br /><br />
		<font color="#ff0000">WARNING! All company assets will be sold, including but not limited to: buildings, research, inventory, brand (fame). Once the company is sold, you will NOT be able to buy it back! </font><br /><br /><br />

		<input type="button" class="bigger_input" value="I have made my decision to sell the company" onclick="firmController.sellCompany();" />
	</div>
<?php
	}
?>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>