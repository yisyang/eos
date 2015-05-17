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

	$buyback_cost = 100000000;

	$min_buyback_shares = 0;
	$max_buyback_shares = max(0, $firm_shares_os - 100);

	$min_share_price = max(1, $firm_share_price);
	$max_share_price = min(999999999, 2 * $firm_share_price);

	$sql = "SELECT action_time FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="buyback_form">
		<h3>Initiate Buyback</h3>
<?php
	if(!$ctrl_admin){
		echo 'Only the chairman of the company has the authority to do this.';
		$proceed = 0;
	}else if(!$eos_firm_is_public){
		echo 'Do the IPO first.';
		$proceed = 0;
	}else{
		$proceed = 1;
		if($firm_cash < $buyback_cost){
			echo '<img src="/images/error.gif" /> Buyback costs $'.number_format_readable($buyback_cost/100).' in fees, but you only have $'.number_format_readable($firm_cash/100).'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Buyback costs $'.number_format_readable($buyback_cost/100).' in fees, and you have $'.number_format_readable($firm_cash/100).'<br />';
		}
		// Must not have any active IPO, SEO, Buyback
		$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			echo '<img src="/images/error.gif" /> Cannot initiate Buyback while another IPO, SEO, or Buyback is active.<br />';
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
	}
	if($proceed){
?>
		<script type="text/javascript">
			var shares_to_repurc, shares_to_repurc_temp;
			var shares_to_repurc_max = <?= $max_buyback_shares ?>;
			var shares_to_repurc_min = <?= $min_buyback_shares ?>;

			function sharesToRepurcMax(){
				shares_to_repurc = shares_to_repurc_max;
				document.getElementById('shares_to_repurc').value = shares_to_repurc;
				checkSharesToRepurc();
			}
			function sharesToRepurcMin(){
				shares_to_repurc = shares_to_repurc_min;
				document.getElementById('shares_to_repurc').value = shares_to_repurc;
				checkSharesToRepurc();
			}
			function checkSharesToRepurc(){
				shares_to_repurc = Math.round(stripCommas(document.getElementById('shares_to_repurc').value));
				document.getElementById('shares_to_repurc').value = shares_to_repurc;
				jQuery("#slider_target").slider("value", shares_to_repurc);
				doEstimates();
			}
			function updateSharesToRepurc(){
				shares_to_repurc_temp = document.getElementById('shares_to_repurc').value;
				if(shares_to_repurc_temp.charAt(shares_to_repurc_temp.length-1) == ".") {
					return false;
				}
				shares_to_repurc = stripCommas(shares_to_repurc_temp);
				if(shares_to_repurc != '' && !isNaN(shares_to_repurc)){
					if(shares_to_repurc > shares_to_repurc_max){
						shares_to_repurc = shares_to_repurc_max;
						document.getElementById('shares_to_repurc').value = shares_to_repurc;
					}
					if(shares_to_repurc < shares_to_repurc_min){
						shares_to_repurc = shares_to_repurc_min;
						document.getElementById('shares_to_repurc').value = shares_to_repurc;
					}
					document.getElementById('shares_to_repurc').value = shares_to_repurc;
					checkSharesToRepurc();
				}
			}
			var checkSharesToRepurcTimeout;
			function initUpdateSharesToRepurc(skipTimeout){
				clearTimeout(checkSharesToRepurcTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateSharesToRepurc();
				}else{
					checkSharesToRepurcTimeout = setTimeout("updateSharesToRepurc();", 1000);
				}
			}

			var share_price, share_price_temp;
			var share_price_max = <?= $max_share_price ?>;
			var share_price_min = <?= $min_share_price ?>;

			function sharePriceMax(){
				share_price = share_price_max;
				document.getElementById('share_price').value = share_price;
				checkSharePrice();
			}
			function sharePriceMin(){
				share_price = share_price_min;
				document.getElementById('share_price').value = share_price;
				checkSharePrice();
			}
			function checkSharePrice(){
				share_price = Math.floor(stripCommas(document.getElementById('share_price').value));
				document.getElementById('share_price').value = share_price;
				document.getElementById('share_price_visible').value = share_price/100;
				jQuery("#slider_target_2").slider("value", share_price);
				doEstimates();
			}
			function updateSharePrice(){
				share_price_temp = document.getElementById('share_price_visible').value;
				if(share_price_temp.charAt(share_price_temp.length-1) == ".") {
					return false;
				}
				share_price = Math.round(stripCommas(share_price_temp)*100);
				if(share_price != '' && !isNaN(share_price)){
					if(share_price > share_price_max){
						share_price = share_price_max;
						document.getElementById('share_price_visible').value = share_price/100;
					}
					if(share_price < share_price_min){
						share_price = share_price_min;
						document.getElementById('share_price_visible').value = share_price/100;
					}
					document.getElementById('share_price').value = share_price;
					checkSharePrice();
				}
			}
			var checkSharePriceTimeout;
			function initUpdateSharePrice(skipTimeout){
				clearTimeout(checkSharePriceTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateSharePrice();
				}else{
					checkSharePriceTimeout = setTimeout("updateSharePrice();", 1000);
				}
			}

			function doEstimates(){
				var shares_to_repurc = Math.round(stripCommas(document.getElementById('shares_to_repurc').value));
				var share_price = Math.floor(stripCommas(document.getElementById('share_price').value));
				document.getElementById("est_new_shares_os").innerHTML = formatNumReadable(<?= $firm_shares_os ?> - shares_to_repurc);
				var cost_total = shares_to_repurc * share_price;
				document.getElementById("est_total_cash_required").innerHTML = '$' + formatNumReadable(cost_total / 100);
				if(<?= $firm_cash ?> < cost_total){
					jQuery("#buyback_submit").prop("disabled", true);
				}else{
					jQuery("#buyback_submit").prop("disabled", false);
				}
			}
		</script>
		<br />
		<form id="slider_form_1" class="default_slider_form" onsubmit="stockController.startBuyback();return false;">
			<h3 style="vertical-align:middle;">Shares to Buyback</h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="sharesToRepurcMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="sharesToRepurcMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="shares_to_repurc" type="text" style="border: 2px solid #997755;text-align:center;" value="" size="12" maxlength="9" onkeyup="initUpdateSharesToRepurc();" onchange="updateSharesToRepurc();" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<h3 style="vertical-align:middle;">Share Price</h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="sharePriceMin();" /></div>
				<div id="slider_target_2" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="sharePriceMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					$ <input id="share_price_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="" size="12" maxlength="10" onkeyup="initUpdateSharePrice();" onchange="updat30eSharePrice();" />
					<input id="share_price" type="hidden" style="display:none;" value="" maxlength="9" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<h3>Estimates</h3>
			<b>Assuming</b> all shares to be bought:<br /><br />
			Total shares: <span id="est_shares_os"><?= number_format_readable($firm_shares_os) ?></span> => <span id="est_new_shares_os">N/A</span><br />
			Total cash required: <span id="est_total_cash_required">N/A</span><br />
			<br />
			NOTE: To limit insider trading, your Buyback will be announced immediately but cannot be sold to in the first 24 server hours. Full deposit is required to initiate buyback, and any unused portions will be refunded.
			<br /><br />
			<input id="buyback_submit" class="bigger_input" type="submit" value="Initiate Buyback" disabled="disabled" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: shares_to_repurc_min,
				min: shares_to_repurc_min,
				max: shares_to_repurc_max,
				slide: function(event, ui){
					jQuery("#shares_to_repurc").val(ui.value);
					checkSharesToRepurc();
				}
			});
			jQuery("#slider_target_2").slider({
				value: share_price_min,
				min: share_price_min,
				max: share_price_max,
				slide: function(event, ui){
					jQuery("#share_price").val(ui.value);
					checkSharePrice();
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