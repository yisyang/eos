<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php
	$sql = "SELECT firms.name, firms.networth, firms.cash, firm_stock.dividend, firm_stock.shares_os FROM firms LEFT JOIN firm_stock ON firms.id = firm_stock.fid WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_echoout('Unable to confirm your position in the company. Please make sure you are still an employee here.');
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$current_dividend = $firm['dividend'];
	}

	$min_dividend = 0;
	$max_dividend = max(0, floor($firm['networth'] / 100 / $firm['shares_os']));
	
	$sql = "SELECT action_time FROM log_limited_actions WHERE action = 'set dividend' AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -7 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
		<script type="text/javascript">
			var dividend, dividend_temp;
			var dividend_max = <?= $max_dividend ?>;
			var dividend_min = <?= $min_dividend ?>;

			function dividendMax(){
				dividend = dividend_max;
				document.getElementById('dividend').value = dividend;
				checkDividend();
			}
			function dividendMin(){
				dividend = dividend_min;
				document.getElementById('dividend').value = dividend;
				checkDividend();
			}
			function checkDividend(){
				dividend = Math.floor(stripCommas(document.getElementById('dividend').value));
				document.getElementById('dividend').value = dividend;
				document.getElementById('dividend_visible').value = dividend/100;
				jQuery("#slider_target").slider("value", dividend);
			}
			function updateDividend(){
				dividend_temp = document.getElementById('dividend_visible').value;
				if(dividend_temp.charAt(dividend_temp.length-1) == ".") {
					return false;
				}
				dividend = Math.round(stripCommas(dividend_temp)*100);
				if(dividend != '' && !isNaN(dividend)){
					if(dividend > dividend_max){
						dividend = dividend_max;
						document.getElementById('dividend_visible').value = dividend/100;
					}
					if(dividend < dividend_min){
						dividend = dividend_min;
						document.getElementById('dividend_visible').value = dividend/100;
					}
					document.getElementById('dividend').value = dividend;
					checkDividend();
				}
			}
			var checkDividendTimeout;
			function initUpdateDividend(skipTimeout){
				clearTimeout(checkDividendTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateDividend();
				}else{
					checkDividendTimeout = setTimeout("updateDividend();", 1000);
				}
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Set Dividend</h3>
	<div id="dividend_form">
<?php

	if(!$ctrl_admin){
		echo 'Only the chairman of the company has the authority to change this.';
	}else if(!empty($action_performed)){
		echo '<img src="/images/error.gif" /> Dividend can be changed once every 7 days, you last performed this action on '.$action_performed['action_time'];
	}else{
?>
		Dividends are paid per server day on each daily update with the company's cash or loan.<br />
		Once set, it cannot be changed in the next 7 server days (1 game year).<br /><br />

		<img src="/images/success.gif" /> Dividend has not changed in the last 7 days.<br /><br />
		
		<form id="slider_form_1" class="default_slider_form" onsubmit="stockController.setDividend();return false;">
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="dividendMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="dividendMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="dividend_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="<?= $current_dividend / 100 ?>" size="13" maxlength="13" onkeyup="initUpdateDividend();" onchange="updateDividend();" /> ($ / share)
					<input id="dividend" type="hidden" style="display:none;" value="<?= $current_dividend ?>" maxlength="17" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<img class="big_action_button" src="images/button-trade-big.gif" id="fund_start_button" title="Confirm" onClick="stockController.setDividend();" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: <?= $current_dividend ?>,
				min: dividend_min,
				max: dividend_max,
				slide: function(event, ui){
					jQuery("#dividend").val(ui.value);
					checkDividend();
				}
			});
		</script>
<?php
	}
?>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>