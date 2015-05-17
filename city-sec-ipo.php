<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php
	$sql = "SELECT firms.name, firms.networth, firms.cash, firms.level, firms.fame_level FROM firms WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_echoout('Company not found.');
	}else{
		$firm_name = $firm['name'];
		$firm_cash = $firm['cash'];
		$firm_networth = $firm['networth'];
		$firm_level = $firm['level'];
		$firm_fame_level = $firm['fame_level'];
	}

	$ipo_cost = 100000000;
	$min_capital_to_raise = min(10000000000000000, max(0, floor($firm_networth * 5/9500) * 100));
	$max_capital_to_raise = min(999999998000000001, max(0, floor($firm_networth * 9/100) * 100));
	
	$min_share_price = max(100, $max_capital_to_raise/880000000);
	$max_share_price = min(999999999, max(10000, $max_capital_to_raise/10000));
	
	$sql = "SELECT action_time FROM log_limited_actions WHERE action IN ('ipo', 'seo', 'go private') AND actor_id = $eos_firm_id AND action_time > DATE_ADD(NOW(), INTERVAL -14 DAY)";
	$action_performed = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT COUNT(*) FROM history_firms WHERE fid = $eos_firm_id AND history_date = DATE_ADD(CURDATE(), INTERVAL -14 DAY);";
	$history_exists = $db->query($sql)->fetchColumn();
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
					var estSharesPublic = Math.floor(capital_to_raise / share_price);
					var estSharesMe = Math.floor(<?= $firm_networth ?> / share_price);
					var estCapRaised = estSharesPublic * share_price;
					document.getElementById("est_cap_raised").innerHTML = formatNumReadable(estCapRaised/100);
					document.getElementById("est_shares_os").innerHTML = estSharesMe + estSharesPublic;
					document.getElementById("est_shares_me").innerHTML = estSharesMe;
					document.getElementById("est_control").innerHTML = formatNum(100 * estSharesMe/(estSharesMe + estSharesPublic), 2, 1) + '%';
					jQuery("#symbol_name_submit").prop("disabled", false);
				}else{
					document.getElementById("est_cap_raised").innerHTML = 'N/A';
					document.getElementById("est_shares_os").innerHTML = 'N/A';
					document.getElementById("est_shares_me").innerHTML = 'N/A';
					document.getElementById("est_control").innerHTML = 'N/A';
					jQuery("#symbol_name_submit").prop("disabled", true);
				}
			}

			var searchTimeout, lastSearch;
			function nameCheckInit(skipTimeout){
				jQuery("#symbol_name_submit").prop("disabled", true);
				clearTimeout(searchTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					nameCheck();
				}else{
					searchTimeout = setTimeout("nameCheck();", 1000);
				}
			}
			function nameCheck(){
				var search = document.getElementById("new_symbol_name").value;
				clearTimeout(searchTimeout);
				if(search !== lastSearch){
					lastSearch = search;
					stockController.checkSymbolName();
				}
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<div id="ipo_form">
		<h3>Initiate IPO</h3>
<?php
	if(!$ctrl_admin){
		echo 'Only the owner of the company has the authority to do this.';
		$proceed = 0;
	}else if($eos_firm_is_public){
		echo 'This IS a public company!';
		$proceed = 0;
	}else{
		$proceed = 1;
		if($firm_cash < $ipo_cost){
			echo '<img src="/images/error.gif" /> Initiating IPO costs $'.number_format_readable($ipo_cost/100).', but you only have $'.number_format_readable($firm_cash/100).'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Initiating IPO costs $'.number_format_readable($ipo_cost/100).', and you have $'.number_format_readable($firm_cash/100).'<br />';
		}
		if($firm_networth < 10 * $ipo_cost){
			echo '<img src="/images/error.gif" /> Your company\'s last assessed networth is under $'.number_format_readable(10 * $ipo_cost/100).'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Your company\'s last assessed networth is above $'.number_format_readable(10 * $ipo_cost/100).'<br />';
		}
		if(!$history_exists){
			echo '<img src="/images/error.gif" /> Company is too new. The legal requirement for IPO is 2 years. (14 server days)<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Company has more than 2 years of history. (&gt; 14 server days)<br />';
		}
		if($firm_level < 6){
			echo '<img src="/images/error.gif" /> Your company is too small. (Need networth level > 5)<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Your company is large enough. (Networth level > 5)<br />';
		}
		if($firm_fame_level < 6){
			echo '<img src="/images/error.gif" /> Your company is not reputable enough. (Need fame level > 5)<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> Your company is reputable. (Fame level > 5)<br />';
		}
		if(!empty($action_performed)){
			echo '<img src="/images/error.gif" /> This action cannot be performed within 2 years (14 server days) of another IPO, SEO, or Going Private, which was done on '.$action_performed['action_time'].'<br />';
			$proceed = 0;
		}else{
			echo '<img src="/images/success.gif" /> No history of IPO, SEO, or Going Private within 2 years.<br />';
		}
	}
	if($proceed){
?>
		<br />
		<form id="slider_form_1" class="default_slider_form" onsubmit="stockController.startIPO();return false;">
			<h3>Stock Symbol</h3>
			Choose a stock symbol for your company. It must be composed of between 2 to 8 CAPitalized Roman characters.<br /><br />
			Stock Symbol: 
			<input type="text" class="bigger_input" id="new_symbol_name" size="26" maxlength="24" value="" onKeyUp="nameCheckInit();" onChange="nameCheck();" />
			<div id="name_check_response"></div>
			<br /><br />
			
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
			NOTE: To limit insider trading, your IPO will be announced immediately but cannot be purchased from in the first 24 server hours.
			<br /><br />
			<input id="symbol_name_submit" class="bigger_input" type="submit" value="Initiate IPO" disabled="disabled" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: capital_to_raise_min,
				min: capital_to_raise_min,
				max: capital_to_raise_max,
				slide: function(event, ui){
					jQuery("#capital_to_raise").val(ui.value);
					checkCapToRaise();
				},
				create: function(event, ui){
					jQuery("#capital_to_raise").val(capital_to_raise_min * 8.142857);
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
				},
				create: function(event, ui){
					jQuery("#share_price").val(Math.floor(capital_to_raise_min * 5 / 1000000)*100);
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