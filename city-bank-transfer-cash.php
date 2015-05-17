<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php
	$sql = "SELECT name, cash, loan FROM firms WHERE id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		echo "Error encountered, please report to admin. Error code CBTC-08.";
		exit();
	}else{
		$firm_name = $firm["name"];
		$firm_cash = $firm["cash"];
		$firm_loan = $firm["loan"];
	}

	$sql = "SELECT player_cash FROM players WHERE id = $eos_player_id";
	$player_cash = $db->query($sql)->fetchColumn();

	if($firm_cash > $firm_loan){
		$min_xfund = $firm_loan - $firm_cash;
	}else{
		$min_xfund = 0;
	}	
	if($player_cash > 0){
		$max_xfund = $player_cash;
	}else{
		$max_xfund = 0;
	}
?>
		<script type="text/javascript">
			var xfund, xfund_temp, lastRan = 0;
			var xfund_max = <?= $max_xfund ?>;
			var xfund_min = <?= $min_xfund ?>;

			firmController.allowance = <?= ($ctrl_daily_allowance == -1) ? -1 : $ctrl_leftover_allowance ?>;

			function xFundMax(){
				xfund = xfund_max;
				document.getElementById('xfund').value = xfund;
				checkXFund();
			}
			function xFundMin(){
				xfund = xfund_min;
				document.getElementById('xfund').value = xfund;
				checkXFund();
			}
			function checkXFund(){
				xfund = Math.floor(stripCommas(document.getElementById('xfund').value));
				document.getElementById('xfund').value = xfund;
				document.getElementById('xfund_visible').value = xfund/100;
				jQuery("#slider_target").slider("value", xfund);
			}
			function updateXFund(){
				xfund_temp = document.getElementById('xfund_visible').value;
				if(xfund_temp.charAt(xfund_temp.length-1) == ".") {
					return false;
				}
				xfund = Math.round(stripCommas(xfund_temp)*100);
				if(xfund != 0 && !isNaN(xfund)){
					if(xfund > xfund_max){
						xfund = xfund_max;
						document.getElementById('xfund_visible').value = xfund/100;
					}
					if(xfund < xfund_min){
						xfund = xfund_min;
						document.getElementById('xfund_visible').value = xfund/100;
					}
					document.getElementById('xfund').value = xfund;
					checkXFund();
				}
			}
			var checkXFundTimeout;
			function initUpdateXFund(skipTimeout){
				clearTimeout(checkXFundTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateXFund();
				}else{
					checkXFundTimeout = setTimeout("updateXFund();", 1000);
				}
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Transfer Cash</h3>
<?php
	if(!$ctrl_admin){
?>
		You are not authorized to perform this action.
<?php
	}else if($eos_firm_is_public){
?>
		You are only allowed to freely transfer cash to or from your privately-held company.
<?php
	}else{
?>
	<div id="transfer_cash_form">
		<form id="slider_form_1" class="default_slider_form" onsubmit="firmController.transferCash();return false;">
			<?= $firm_name ?> is a privately-held company, so you can freely deposit money into it (positive amount) or withdraw money from it (negative amount). Withdrawing may be limited or unavailable if you have a loan.<br /><br />
			The <b>transfer fee is only 0.5%</b>, but please note <b>any transfer made is taxable.</b><br /><br />
			So, how much money would you like to <b>deposit into the company?</b><br /><br />
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="xFundMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="xFundMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="xfund_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="0" size="17" maxlength="17" onkeyup="initUpdateXFund();" onchange="updateXFund();" />
					<input id="xfund" type="hidden" style="display:none;" value="0" maxlength="20" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<img class="big_action_button" src="images/button-trade-big.gif" id="fund_start_button" title="Confirm" onClick="firmController.transferCash();" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: 0,
				min: xfund_min,
				max: xfund_max,
				slide: function(event, ui){
					jQuery("#xfund").val(ui.value);
					checkXFund();
				}
			});
		</script>
	</div>
<?php
	}
?>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>