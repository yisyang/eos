<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php
	$sql = "SELECT firm_stock.shares_os, firm_stock.share_price, firms.name, firms.networth, firms.cash FROM firm_stock LEFT JOIN firms ON firm_stock.fid = firms.id WHERE firm_stock.fid = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_echoout('Company not found.');
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_shares_os = $firm['shares_os'];
		$firm_share_price = $firm['share_price'];
	}

	$split_cost = 100000000;

	$sql = "SELECT action_time FROM log_limited_actions WHERE action = 'split' AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -7 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="split_form">
		<h3>Split / Reverse-Split</h3>
<?php
	if(!$ctrl_admin){
		echo 'Only the chairman of the company has the authority to do this.';
		$proceed = 0;
	}else if(!$eos_firm_is_public){
		echo 'Do the IPO first.';
		$proceed = 0;
	}else{
		$proceed = 1;
		if($firm_cash < $split_cost){
			echo '<img src="/images/error.gif" /> Stock split costs $'.number_format_readable($split_cost/100).', but you only have $'.number_format_readable($firm_cash/100).'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Stock split costs $'.number_format_readable($split_cost/100).', and you have $'.number_format_readable($firm_cash/100).'<br />';
		}
		// Must not have any active IPO, SEO, Buyback
		$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			echo '<img src="/images/error.gif" /> Cannot perform this action while another IPO, SEO, or Buyback is active.<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> No active IPO, SEO, or Buyback.<br />';
		}
		if(!empty($action_performed)){
			echo '<img src="/images/error.gif" /> This action cannot be performed within 1 year (7 server days) of another stock split, which was done on '.$action_performed['action_time'].'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> No history of another stock split within 1 year.<br />';
		}
	}
	if($proceed){
		$sql = "SELECT IFNULL(SUM(MOD(shares, 2)), 0) AS rs2, IFNULL(SUM(MOD(shares, 3)), 0) AS rs3, IFNULL(SUM(MOD(shares, 5)), 0) AS rs5, IFNULL(SUM(MOD(shares, 10)), 0) AS rs10 FROM player_stock WHERE fid = $eos_firm_id";
		$rs_list = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
		<script type="text/javascript">
			function doEstimates(){
				var remainder = new Array();
				remainder[2] = <?= $rs_list['rs2'] ?>;
				remainder[3] = <?= $rs_list['rs3'] ?>;
				remainder[5] = <?= $rs_list['rs5'] ?>;
				remainder[10] = <?= $rs_list['rs10'] ?>;
				var split_choice = document.getElementById('split_choice').value;
				if(split_choice == ''){
					document.getElementById("est_new_share_price").innerHTML = 'N/A';
					document.getElementById("est_new_shares_os").innerHTML = 'N/A';
					document.getElementById("est_fraction_share_price").innerHTML = 'N/A';
					jQuery("#split_submit").prop("disabled", true);
					return false;
				}
				var split_params = split_choice.split('_');
				var split_from = split_params[0];
				var split_to = split_params[1];

				document.getElementById("est_new_share_price").innerHTML = '$' + formatNumReadable(Math.round(<?= $firm_share_price ?> / 100 / split_to * split_from));
				document.getElementById("est_new_shares_os").innerHTML = formatNumReadable(Math.floor(<?= $firm_shares_os ?> / split_from) * split_to);
				if(split_from > 1){
					var reparation = <?= max(round($firm_networth / $firm_shares_os), $firm_share_price) ?> * remainder[split_from];
					var cost_total = reparation + <?= $split_cost ?>;
					document.getElementById("est_fraction_share_price").innerHTML = '$' + formatNumReadable(reparation / 100);
					document.getElementById("est_total_cash_required").innerHTML = '$' + formatNumReadable(cost_total / 100);
				}else{
					var cost_total = <?= $split_cost ?>;
					document.getElementById("est_fraction_share_price").innerHTML = 'N/A';
					document.getElementById("est_total_cash_required").innerHTML = '$' + formatNumReadable(cost_total / 100);
				}
				if(<?= $firm_cash ?> < cost_total){
					jQuery("#split_submit").prop("disabled", true);
				}else{
					jQuery("#split_submit").prop("disabled", false);
				}
			}
		</script>
		<br />
		<form onsubmit="stockController.startSplit();return false;">
			<h3>Split Ratio</h3>
			Note: For reverse-splits, fraction shares will be bought back at latest traded price or estimated networth value, whichever is higher.
			<select id="split_choice" class="bigger_input" onchange="doEstimates();">
				<option value="" style="font-weight:bold;">-- Split Options --</option>
				<option value="2_3">3-for-2</option>
				<option value="1_2">2-for-1</option>
				<option value="1_3">3-for-1</option>
				<option value="1_5">5-for-1</option>
				<option value="1_10">10-for-1</option>
				<option value="" style="font-weight:bold;margin-top:4px">-- Reverse-Split Options --</option>
				<option value="3_2">2-for-3</option>
				<option value="2_1">1-for-2</option>
				<option value="3_1">1-for-3</option>
				<option value="5_1">1-for-5</option>
				<option value="10_1">1-for-10</option>
			</select>
			<br /><br />
			<h3>Estimates</h3>
			Share price: <span id="est_share_price">$<?= number_format_readable($firm_share_price/100) ?></span> => <span id="est_new_share_price">N/A</span><br />
			Total shares: <span id="est_shares_os"><?= number_format_readable($firm_shares_os) ?></span> => <span id="est_new_shares_os">N/A</span><br />
			Cost to repurchase fraction shares: <span id="est_fraction_share_price">N/A</span><br />
			Total cash required: <span id="est_total_cash_required">N/A</span><br />
			<br />
			<input id="split_submit" class="bigger_input" type="submit" value="Initiate Stock Split" disabled="disabled" />
		</form>
<?php
	}
?>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>