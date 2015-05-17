<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_GET['fsid'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = 'store';
if(!$bldg_id){
	fbox_breakout('buildings.php');
}

// Make sure the eos user actually owns the building
$query = $db->prepare("SELECT store_id AS bldg_type_id, store_name AS bldg_name, size, slot, marketing FROM firm_store WHERE id = ? AND fid = ?");
$query->execute(array($bldg_id, $eos_firm_id));
$bldg = $query->fetch(PDO::FETCH_ASSOC);
if(empty($bldg)){
	fbox_breakout('buildings.php');
}else{
	$bldg_type_id = $bldg['bldg_type_id'];
	$bldg_name = $bldg['bldg_name'];
	$bldg_size = $bldg['size'];
	$bldg_slot = $bldg['slot'];
	$store_marketing = $bldg['marketing'];
}

// Find out the number of stores of this type
$sql = "SELECT COUNT(*) FROM firm_store WHERE fid = $eos_firm_id AND store_id = $bldg_type_id";
$store_type_count = $db->query($sql)->fetchColumn();

// Initialize bldg image for store
$sql = "SELECT name, has_image, cost, timecost FROM list_store WHERE id = $bldg_type_id";
$list_bldg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$expand_cost = $list_bldg["cost"];
$expand_timecost = $list_bldg["timecost"];
$generic_name = $list_bldg["name"];
if($list_bldg["has_image"]){
	$bldg_img_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_bldg["name"]));
}else{
	$bldg_img_filename = "no-image";
}

// Initialize Firm Cash
$sql = "SELECT name, cash FROM firms WHERE id = $eos_firm_id";	
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];

$min_xfund = 0;
if($firm_cash > 0){
	$max_xfund = min($ctrl_leftover_allowance, $firm_cash);
}else{
	$max_xfund = 0;
}
?>
		<script type="text/javascript">
			var xfund, xfund_temp, lastRan = 0;
			var xfund_max = <?= $max_xfund ?>;
			var xfund_min = <?= $min_xfund ?>;
			var marketing = <?= $store_marketing ?>;
			var marketing_effect = Math.pow(marketing, 0.25);
			var marketing_new, marketing_effect_new;
			var divideMarketing = 1;
			var divideMarketingCount = <?= $store_type_count ?>;

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
				divideMarketing = document.getElementById('divide_marketing').checked;
				if(divideMarketing){
					divideMarketing = 1;
					marketing_new = Math.floor(marketing + xfund/divideMarketingCount);
				}else{
					divideMarketing = 0;
					marketing_new = marketing + xfund;
				}
				marketing_effect_new = Math.pow(marketing_new, 0.25);
				jQuery('#marketing_power_new').html(formatNumReadable(marketing_new));
				jQuery('#marketing_effect_new').html(marketing_effect_new.toFixed(2));
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
			var checkTimeout;
			function initUpdateXFund(value, skipTimeout){
				clearTimeout(checkTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateXFund(value);
				}else{
					checkTimeout = setTimeout("updateXFund('" + value + "');", 1000);
				}
			}
			function xFundStart(){
				var cSNow = (new Date().valueOf() * 0.01)|0;
				var cSDiff = cSNow - lastRan;
				if(cSDiff < 15){
					return false;
				}
				lastRan = cSNow;
				xfund = document.getElementById('xfund').value;
				
				var params = {action: 'increase_marketing', fsid: <?= $bldg_id ?>, xfund: xfund, divide_marketing: divideMarketing};
				storeController.executeAjax(params, function(resp){
					jQuery("#marketing_form").html('You have successfully <b>spent $' + formatNum(xfund/100, 2) + '</b> on advertisements.');
					jQuery("#marketing_effect").html('+' + formatNum(Math.pow(resp.marketing_new, 0.25),2) + '%');
					firmController.getCash();
				});
			}
			jQuery(document).ready(function(){
				// Adding the holder to document to bypass preventDefault on checkbox elements
				jQuery(document).on('click', '#divide_marketing_holder', function(){
					checkXFund();
				});
			});
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Marketing for <?= $bldg_name.' ('.$bldg_size.' m&#178; <a id="marketing_effect" title="Marketing effect">+'.number_format(pow($store_marketing, 0.25),2,'.',',').'%</a>)' ?><br /></h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $bldg_img_filename ?>.gif" /></a><br /><br />
<?php
	if(!$ctrl_store_ad){
?>
		You are not authorized to perform this action.<br /><br />
<?php
	}else{
?>
	<div id="marketing_form">
		<form id="slider_form_1" class="default_slider_form" onsubmit="xFundStart();return false;">
			<h3>Marketing</h3>
			How much cash would you like to spend?<br />
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="xFundMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;" onClick="xFundMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					$ <input id="xfund_visible" type="text" style="border: 2px solid #997755;text-align:center;" size="17" maxlength="17" onkeyup="initUpdateXFund();" onchange="updateXFund();" />
					<input id="xfund" type="hidden" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<?php
				$store_marketing_formatted = number_format_readable($store_marketing);
				$store_marketing_effect_formatted = number_format(pow($store_marketing, 0.25),2,'.',',');
			?>
			<div id="divide_marketing_holder"><input id="divide_marketing" class="bigger_input" type="checkbox" value="1" checked="checked" /><label for="divide_marketing" style="margin-left:10px;">Divide spending between <?= $store_type_count.' '.$generic_name ?>s</label></div>
			<br /><br />
			<h3>Summary <a class="info vert_middle"><img src="/eos/images/info.png" /><span style="width:300px;">With each tick your store's marketing power (not effect) will decreases by 0.3%, and as a benefit of economies of scale, your store can generate free publicity based on the SQUARE of its size.</span></a></h3>
			<div style="float:left;width:300px;">
				Current Marketing Power: <span id="marketing_power_curr"><?= $store_marketing_formatted ?></span><br />
				Current Effect: +<span id="marketing_effect_curr"><?= $store_marketing_effect_formatted ?><span>%<br />
				Estimated New Power: <span id="marketing_power_new"><?= $store_marketing_formatted ?></span><br />
				Estimated New Effect: +<span id="marketing_effect_new"><?= $store_marketing_effect_formatted ?></span>%<br />
			</div>
			<img style="float:right;cursor:pointer;" src="images/button-trade-big.gif" id="fund_start_button" title="Confirm Spending" onClick="xFundStart();" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: 0,
				min: <?= $min_xfund ?>,
				max: <?= $max_xfund ?>,
				slide: function( event, ui ){
					jQuery("#xfund").val(ui.value);
					checkXFund();
				}
			});
		</script>
	</div>
<?php
	}
?>
	<div style="clear:both;">&nbsp;</div>
	<br />
	<a class="jqDialog" href="stores-sell.php?fsid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>