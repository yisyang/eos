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

	$seo_cost = 100000000;
	
	$min_additional_shares = 0;
	$max_additional_shares = max(0, 999999999 - $firm_shares_os);

	$min_share_price = floor(0.7 * $firm_share_price);
	$max_share_price = $firm_share_price;

	$min_capital_to_raise = 0;
	$max_capital_to_raise = min($max_additional_shares * $max_share_price, max(0, floor($firm_networth / 1000) * 100));

	$sql = "SELECT action_time FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'buyback', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="seo_form">
		<h3>Initiate SEO</h3>
<?php
	if(!$ctrl_admin){
		echo 'Only the chairman of the company has the authority to do this.';
		$proceed = 0;
	}else if(!$eos_firm_is_public){
		echo 'Do the IPO first.';
		$proceed = 0;
	}else{
		$proceed = 1;
		if($firm_cash < $seo_cost){
			echo '<img src="/images/error.gif" /> SEO costs $'.number_format_readable($seo_cost/100).', but you only have $'.number_format_readable($firm_cash/100).'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> SEO costs $'.number_format_readable($seo_cost/100).', and you have $'.number_format_readable($firm_cash/100).'<br />';
		}
		// Must not have any active IPO, SEO, Buyback
		$sql = "SELECT COUNT(*) FROM firm_stock_issuance WHERE fid = $eos_firm_id";
		$count = $db->query($sql)->fetchColumn();
		if($count){
			echo '<img src="/images/error.gif" /> Cannot initiate SEO while another IPO, SEO, or Buyback is active.<br />';
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
			var capital_to_raise, capital_to_raise_temp;
			var capital_to_raise_max = <?= $max_capital_to_raise ?>;
			var capital_to_raise_min = <?= $min_capital_to_raise ?>;

			function capToRaiseMax(){
				capital_to_raise = capital_to_raise_max;
				document.getElementById('capital_to_raise').value = capital_to_raise;
				checkCapToRaise();
			}
			function capToRaiseMin(){
				capital_to_raise = capital_to_raise_min;
				document.getElementById('capital_to_raise').value = capital_to_raise;
				checkCapToRaise();
			}
			function checkCapToRaise(){
				capital_to_raise = Math.floor(stripCommas(document.getElementById('capital_to_raise').value));
				document.getElementById('capital_to_raise').value = capital_to_raise;
				document.getElementById('capital_to_raise_visible').value = Math.floor(capital_to_raise/100);
				jQuery("#slider_target").slider("value", capital_to_raise);
				doEstimates();
			}
			function updateCapToRaise(){
				capital_to_raise_temp = document.getElementById('capital_to_raise_visible').value;
				if(capital_to_raise_temp.charAt(capital_to_raise_temp.length-1) == ".") {
					return false;
				}
				capital_to_raise = Math.round(stripCommas(capital_to_raise_temp)*100);
				if(capital_to_raise != '' && !isNaN(capital_to_raise)){
					if(capital_to_raise > capital_to_raise_max){
						capital_to_raise = capital_to_raise_max;
						document.getElementById('capital_to_raise_visible').value = Math.floor(capital_to_raise/100);
					}
					if(capital_to_raise < capital_to_raise_min){
						capital_to_raise = capital_to_raise_min;
						document.getElementById('capital_to_raise_visible').value = Math.floor(capital_to_raise/100);
					}
					document.getElementById('capital_to_raise').value = capital_to_raise;
					checkCapToRaise();
				}
			}
			var checkCapToRaiseTimeout;
			function initUpdateCapToRaise(skipTimeout){
				clearTimeout(checkCapToRaiseTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateCapToRaise();
				}else{
					checkCapToRaiseTimeout = setTimeout("updateCapToRaise();", 1000);
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
				share_price = Math.floor(stripCommas(document.getElementById('share_price').value));
				capital_to_raise = Math.floor(stripCommas(document.getElementById('capital_to_raise').value));
				if(capital_to_raise != '' && !isNaN(capital_to_raise) && share_price != '' && !isNaN(share_price)){
					var estSharesPublic = <?= $firm_shares_os ?> + Math.floor(capital_to_raise / share_price);
					var estSharesMe = <?= $eos_player_stock_shares ?>;
					var estCapRaised = Math.floor(capital_to_raise / share_price) * share_price;
					document.getElementById("est_cap_raised").innerHTML = formatNumReadable(estCapRaised/100);
					if(estSharesPublic > 999999999){
						document.getElementById("est_shares_os").innerHTML = '<a class="info" style="color:#ff0000;">' + estSharesPublic + '<span>Per section 610.1 of Econosia Civil Code, the maximum number of shares issued by a company must remain under 1 billion.</span></a>';
						jQuery("#seo_submit").prop("disabled", true);
					}else{
						document.getElementById("est_shares_os").innerHTML = estSharesPublic;
						jQuery("#seo_submit").prop("disabled", false);
					}
					document.getElementById("est_shares_me").innerHTML = estSharesMe;
					document.getElementById("est_control").innerHTML = formatNum(100 * estSharesMe/estSharesPublic, 2, 1) + '%';
				}else{
					document.getElementById("est_cap_raised").innerHTML = 'N/A';
					document.getElementById("est_shares_os").innerHTML = 'N/A';
					document.getElementById("est_shares_me").innerHTML = 'N/A';
					document.getElementById("est_control").innerHTML = 'N/A';
					jQuery("#seo_submit").prop("disabled", true);
				}
			}
		</script>
		<br />
		<form id="slider_form_1" class="default_slider_form" onsubmit="stockController.startSEO();return false;">
			<h3 style="vertical-align:middle;">Capital to Raise</h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="capToRaiseMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="capToRaiseMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					$ <input id="capital_to_raise_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="" size="18" maxlength="18" onkeyup="initUpdateCapToRaise();" onchange="updateCapToRaise();" />
					<input id="capital_to_raise" type="hidden" style="display:none;" value="" maxlength="19" />
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
			<b>Assuming</b> all shares to be bought by the public:<br /><br />
			Capital raised: <span id="est_cap_raised">N/A</span><br />
			Total shares: <span id="est_shares_os">N/A</span><br />
			Your shares: <span id="est_shares_me">N/A</span><br />
			Your ownership: <span id="est_control">N/A</span><br />
			<br />
			NOTE: To limit insider trading, your SEO will be announced immediately but cannot be purchased from in the first 24 server hours.
			<br /><br />
			<input id="seo_submit" class="bigger_input" type="submit" value="Initiate SEO" disabled="disabled" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: capital_to_raise_min,
				min: capital_to_raise_min,
				max: capital_to_raise_max,
				slide: function(event, ui){
					jQuery("#capital_to_raise").val(ui.value);
					checkCapToRaise();
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