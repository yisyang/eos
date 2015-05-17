<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
if(!$bldg_id){
	fbox_breakout('buildings.php');
}
if($bldg_type == 'fact'){
	$ctrl_sell = $ctrl_fact_sell;
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_info = $db->prepare("SELECT fact_name AS bldg_name, fact_id AS bldg_type_id, size, slot FROM firm_fact WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_fact WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_prod WHERE ffid = :bldg_id");
}else if($bldg_type == 'store'){
	$ctrl_sell = $ctrl_store_sell;
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_info = $db->prepare("SELECT store_name AS bldg_name, store_id AS bldg_type_id, size, slot FROM firm_store WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_store WHERE id = :building_type_id");
}else if($bldg_type == 'rnd'){
	$ctrl_sell = $ctrl_rnd_sell;
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_info = $db->prepare("SELECT rnd_name AS bldg_name, rnd_id AS bldg_type_id, size, slot FROM firm_rnd WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_rnd WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_res WHERE frid = :bldg_id");
}else{
	fbox_breakout('buildings.php');
}

// First check $bldg_id belongs to $eos_firm_id, get $bldg_name, $bldg_type_id, and $bldg_size
if($bldg_id){
	$query_get_bldg_info->execute(array(':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
	$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
	if(empty($firm_bldg)){
		fbox_breakout('buildings.php', 'Building not found.');
	}
	$bldg_name = $firm_bldg['bldg_name'];
	$bldg_type_id = $firm_bldg['bldg_type_id'];
	$bldg_size = $firm_bldg['size'];
	$bldg_slot = $firm_bldg['slot'];
	
	// and that it is not active
	if(isset($query_confirm_inactivity)){
		$query_confirm_inactivity->execute(array(':bldg_id' => $bldg_id));
		$count = $query_confirm_inactivity->fetchColumn();
		if($count){
			fbox_redirect($bldg_activity_url.$bldg_id);
		}
	}
}

// and that it is NOT under construction
$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
$query->execute(array($bldg_type, $bldg_id));
$queue_build = $query->fetch(PDO::FETCH_ASSOC);
if(!empty($queue_build)){
	fbox_breakout('buildings.php');
}

// Initialize building name and image
$query_get_bldg_list_info->execute(array(':building_type_id' => $bldg_type_id));
$bldg_list_info = $query_get_bldg_list_info->fetch(PDO::FETCH_ASSOC);
if(empty($bldg_list_info)){
	fbox_breakout('buildings.php', 'Building prototype not found.');
}
$expand_cost = $bldg_list_info["cost"];
$expand_timecost = $bldg_list_info["timecost"];
if($bldg_list_info["has_image"]){
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg_list_info["name"]));
}else{
	$filename = "no-image";
}

$bldg_worth = $bldg_size * $expand_cost;
$bldg_sale_value = 0.9 * $bldg_worth;
?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Sell <?= $bldg_name.' ('.$bldg_size.' m&#178;)' ?></h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
<?php
	if(!$ctrl_sell){
?>
		You are not authorized to sell this <?php echo $bldg_name; ?>.<br />
<?php
	}else{
?>
	Selling this building yields:<br />
	<span class="vert_middle"><img title="Cash" alt="Cash" src="/eos/images/money.gif"> $<?php echo number_format($bldg_sale_value/100,2,'.',','); ?></span><br /><br />
	<div style="text-align:center;line-height:200%;">
		<form onsubmit="bldgSellConfirm();return false;">
			<label>Please note this action is not undo-able.<br />
			Type <b>SELL</b> in the box below and click on "Confirm Sale" to proceed</label><br />
			<input class="bigger_input" id="building_sell_confirmation" name="building_sell_confirmation" type="text" size="10" maxlength="10" value="" /><br />
			<input class="bigger_input" type="submit" value="Confirm Sale" />
		</form>
		<script type="text/javascript">
			function bldgSellConfirm(){
				building_sell_confirmation = document.getElementById('building_sell_confirmation').value;
				jqDialogInit('bldg-sell-start.php', {
					id : <?= $bldg_id ?>,
					type : '<?= $bldg_type ?>',
					building_sell_confirmation : building_sell_confirmation
				});
			}
		</script>
	</div>
<?php
	}
?>
	<div class="clearer no_select">&nbsp;</div>
	<a class="jqDialog" href="<?= $bldg_activity_url.$bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>