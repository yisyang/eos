<?php require 'include/prehtml.php'; ?>
<?php
$fqid = filter_var($_POST['fqid'], FILTER_SANITIZE_NUMBER_INT);
if(!($fqid)){
	fbox_breakout('quests.php');
}
// if($err){
	// if($err == 1)
		// $err_msg = "Please specify the quantity of products that you would like to turn in.";
	// if($err == 12)
		// $err_msg = "You do not have the amount that you are trying to supply for the target products.";
	// if($err == 98)
		// $err_msg = "Error encountered, your quest products were lost due to a system error, please report to admin for reimbursement. Error code QPS98.";
	// if($err == 99)
		// $err_msg = "Error encountered, please report to admin. Error code QPS99.";
// }

// First check fqid belongs to eos_firm_id
$sql = "SELECT * FROM firm_quest LEFT JOIN list_quest ON firm_quest.quest_id = list_quest.id WHERE firm_quest.id = $fqid AND firm_quest.fid = $eos_firm_id AND !firm_quest.completed AND !firm_quest.failed";
$quest = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
if(empty($quest)){
	fbox_breakout('quests.php');
}else{
	$q_type = $quest["type"];
	$gen_target_id = $quest["gen_target_id"];
	$gen_target_n = $quest["gen_target_n"];
	$q_prod_q = $quest["q"];
}

// Make sure $q_type is between 1 to 9 (Product supply quests)
if($q_type < 1 || $q_type > 9){
	fbox_breakout('quests.php');
}

// Collect prod info
$sql = "SELECT * FROM list_prod WHERE id = '$gen_target_id'";
$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$ipid_name = $result['name'];
if($result['has_icon']){
	$ipid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid_name));
}else{
	$ipid_filename = "no-icon";
}

// Finally check if ipid exists in the firm_wh, much easier now with only 1 quality
if($q_prod_q){
	$sql = "SELECT * FROM firm_wh WHERE pid = '$gen_target_id' AND fid = $eos_firm_id AND pidn > 0 AND pidq >= '$q_prod_q' LIMIT 0, 1";
}else{
	$sql = "SELECT * FROM firm_wh WHERE pid = '$gen_target_id' AND fid = $eos_firm_id AND pidn > 0 LIMIT 0, 1";
}

$max_snum = 0;
$wh_id = 0;
$wh_prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
if(!empty($wh_prod)){
	$wh_id = $wh_prod['id'];
	$wh_pidq = $wh_prod['pidq'];
	$wh_pidn = $wh_prod['pidn'];
	
	$max_snum = min($wh_pidn, $gen_target_n);
}
?>
<?php require 'include/functions.php'; ?>
		<script type="text/javascript">
			var snum;
			var snum_min = 0;
			var snum_max = <?= $max_snum ?>;

			function snumAdd1(){
				snum = Math.floor(stripCommas(document.getElementById('snum').value));
				if(snum < snum_max){
					snum = snum + 1;
					document.getElementById('snum').value = snum;
					checkSnum();
				}
			}
			function snumSubtract1(){
				snum = Math.floor(stripCommas(document.getElementById('snum').value));
				if(snum > 0){
					snum = snum - 1;
					document.getElementById('snum').value = snum;
					checkSnum();
				}
			}
			function snumMax(){
				snum = snum_max;
				document.getElementById('snum').value = snum;
				checkSnum();
			}
			function checkSnum(){
				snum = Math.floor(stripCommas(document.getElementById('snum').value));
				if(!isNaN(snum)){
					if(snum > snum_max){
						snum = snum_max;
						document.getElementById('snum').value = snum;
					}
					if(snum < snum_min){
						snum = snum_min;
						document.getElementById('snum').value = snum;
					}
					jQuery("#slider_target").slider("value", snum);
				}
			}
			var checkSnumTimeout;
			function initCheckSnum(skipTimeout){
				clearTimeout(checkSnumTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					checkSnum();
				}else{
					checkSnumTimeout = setTimeout("checkSnum();", 1000);
				}
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<div id="quest_supply_form">
		<h3>Inventory for <img style="vertical-align:middle;" src="/eos/images/prod/<?= $ipid_filename ?>.gif" alt="<?= $ipid_name ?>" title="<?= $ipid_name ?>" /></h3>
		<div style="float:left;width:105px;text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">
		<?php
			if(empty($wh_prod)){
				echo '<img src="/eos/images/prod/large/'.$ipid_filename.'.gif" title="'.$ipid_name.': Not Available" style="margin-bottom:6px;" />';
				echo '<div class="vert_middle"><img src="/eos/images/star.gif" alt="#" title="Quality" /> N/A</div>';
				echo '<div class="vert_middle"><img src="/eos/images/box.png" alt="#" title="Quantity" /> N/A</div>';
			}else{
				echo '<img src="/eos/images/prod/large/'.$ipid_filename.'.gif" title="'.$ipid_name.' (Quality '.number_format($wh_pidq, 2, '.', ',').'): '.number_format($wh_pidn, 0, '.', ',').' Available" style="margin-bottom:6px;" />';
				echo '<div class="vert_middle"><img src="/eos/images/star.gif" alt="#" title="Quality" /> '.number_format($wh_pidq, 2, '.', ',').'</div>';
				echo '<div class="vert_middle"><img src="/eos/images/box.png" alt="#" title="Quantity" /> '.number_format_readable($wh_pidn).'</div>';
			}
		?>
		</div>
		<div class="clearer no_select">&nbsp;</div>
<?php
	if(empty($wh_prod)){
?>	
		<font color="#ff0000">You don't have any <img style="vertical-align:middle;" src="/eos/images/prod/<?= $ipid_filename ?>.gif" alt="<?= $ipid_name ?>" title="<?= $ipid_name ?>" /> or they are of insufficient quality, please produce them at your factories or purchase them at the B2B market.</font><br /><br />
		<h3>Note:</h3>
		Supplying products in multiple batches is allowed, but remember you are only rewarded for the completion of this quest. Should the quest expire before its completion, everything that is supplied will be lost.
<?php
	}else{
?>
		<form id="slider_form_1" class="default_slider_form" onsubmit="questsController.supplyProd(<?= $fqid ?>, <?= $wh_id ?>);return false;">
			<h3 style="vertical-align:middle;">Select Quantity</h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:60px;"><img class="slider_button_subtract_one" src="images/slider_left.gif" style="cursor:pointer;" onclick="snumSubtract1();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:60px;"><img class="slider_button_add_one" src="images/slider_right.gif" style="cursor:pointer;" onclick="snumAdd1();" /></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onclick="snumMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="snum" type="text" style="border: 2px solid #997755;text-align:center;" value="0" size="10" maxlength="10" onkeyup="initCheckSnum();" onchange="checkSnum();" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<img class="big_action_button" src="images/button-trade-big.gif" id="fund_start_button" title="Confirm Delivery" onclick="questsController.supplyProd(<?= $fqid ?>, <?= $wh_id ?>);" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: 0,
				min: snum_min,
				max: snum_max,
				slide: function(event, ui){
					jQuery("#snum").val(ui.value);
					checkSnum();
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

