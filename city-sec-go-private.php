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

	$go_private_cost = 100000000;
	$est_buyback_price = max(1, round(1.5 * max($firm_networth / $firm_shares_os, $firm_share_price)));

	$sql = "SELECT action_time FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="go_private_form">
		<h3>Go Private</h3>
<?php
	if(!$ctrl_admin){
		echo 'Only the chairman of the company has the authority to do this.';
		$proceed = 0;
	}else if(!$eos_firm_is_public){
		echo 'Do the IPO first.';
		$proceed = 0;
	}else{
		$proceed = 1;
		if($firm_cash < $go_private_cost){
			echo '<img src="/images/error.gif" /> Going private costs $'.number_format_readable($go_private_cost/100).' in fees, but you only have $'.number_format_readable($firm_cash/100).'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Going private costs $'.number_format_readable($go_private_cost/100).' in fees, and you have $'.number_format_readable($firm_cash/100).'<br />';
		}
		// Must not have any active IPO, SEO, Buyback
		$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			echo '<img src="/images/error.gif" /> Cannot Go Private while an IPO, SEO, or Buyback is active.<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> No active IPO, SEO, or Buyback.<br />';
		}
		if(!empty($action_performed)){
			echo '<img src="/images/error.gif" /> This action cannot be performed within 2 years (14 server days) of another IPO, SEO, Buyback, or Going Private, which was done on '.$action_performed['action_time'].'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> No history of IPO, SEO, Buyback, or Going Private within 2 years.<br />';
		}
		// Must not have any other active shareholders with >=1% of total shares
		$sql = "SELECT COUNT(players.id) AS cnt FROM player_stock LEFT JOIN players ON player_stock.pid = players.id WHERE player_stock.fid = $eos_firm_id AND player_stock.pid != $eos_player_id AND player_stock.shares >= 0.01 * $firm_shares_os AND players.last_active > DATE_ADD(NOW(), INTERVAL -14 DAY)";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			echo '<img src="/images/error.gif" /> Cannot Go Private when another active (within 14 server days) shareholder has more than 1% of total shares.<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> No other active shareholders with more than 1% of total shares.<br />';
		}
	}
	if($proceed){
		$sql = "SELECT SUM(shares) AS ssh FROM player_stock WHERE fid = $eos_firm_id AND pid != $eos_player_id";
		$shares_to_buyback = $db->query($sql)->fetchColumn();
?>
		<br />
		<form id="slider_form_1" class="default_slider_form" onsubmit="stockController.goPrivate();return false;">
			<input id="go_private_conf" type="hidden" style="display:none;" value="<?= $eos_firm_id ?>" maxlength="9" />
			<h3>Estimates</h3>
			Total shares to repurchase: <?= number_format_readable($shares_to_buyback) ?><br />
			Last traded share price: $<?= number_format_readable($firm_share_price / 100) ?><br />
			Per share book value: $<?= number_format_readable($firm_networth / $firm_shares_os / 100) ?><br />
			Per share repurchase price: $<?= number_format_readable($est_buyback_price / 100) ?><br />
			Total cash required: $<?= number_format_readable(($go_private_cost + $est_buyback_price * $shares_to_buyback) / 100) ?><br />
			<br />
			NOTE: Actual repurchase price may be higher. Estimates are based on the company's current networth and share price.
			<br /><br />
			<input id="go_private_submit" class="bigger_input" type="submit" <?= $firm_cash > ($go_private_cost + $est_buyback_price * $shares_to_buyback) ?'value="Go Private"' : 'value="Go Private (Insufficient Cash)" disabled="disabled"' ?> />
		</form>
<?php
	}
?>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>