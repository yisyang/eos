<?php require 'include/prehtml.php'; ?>
<?php require 'include/stock_control.php'; ?>
<?php require_active_firm(); ?>
<?php
$building_sell_confirmation = strtolower(filter_var($_POST['building_sell_confirmation'], FILTER_SANITIZE_STRING));
$bldg_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
if(!$bldg_id || !$ctrl_rnd_sell){
	fbox_breakout('buildings.php');
}
if($bldg_type == 'fact'){
	$ctrl_sell = $ctrl_fact_sell;
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_info = $db->prepare("SELECT fact_name AS bldg_name, fact_id AS bldg_type_id, size, slot FROM firm_fact WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_fact WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_prod WHERE ffid = :bldg_id");
	$query_sell_bldg = $db->prepare("DELETE FROM firm_fact WHERE id = ? AND fid = ?");
}else if($bldg_type == 'store'){
	$ctrl_sell = $ctrl_store_sell;
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_info = $db->prepare("SELECT store_name AS bldg_name, store_id AS bldg_type_id, size, slot FROM firm_store WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_store WHERE id = :building_type_id");
	$query_sell_bldg = $db->prepare("DELETE FROM firm_store WHERE id = ? AND fid = ?");
}else if($bldg_type == 'rnd'){
	$ctrl_sell = $ctrl_rnd_sell;
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_info = $db->prepare("SELECT rnd_name AS bldg_name, rnd_id AS bldg_type_id, size, slot FROM firm_rnd WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_rnd WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_res WHERE frid = :bldg_id");
	$query_sell_bldg = $db->prepare("DELETE FROM firm_rnd WHERE id = ? AND fid = ?");
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

$building_sold = 0;
if($building_sell_confirmation == "sell"){
	//Destroy building
	if($query_sell_bldg->execute(array($bldg_id, $eos_firm_id))){
		//Add $ to firm
		$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ('$eos_firm_id', 0, '$bldg_sale_value', 'Building Sale', NOW())";
		$db->query($sql);
		$sql = "UPDATE firms SET cash = cash + $bldg_sale_value WHERE id = $eos_firm_id";
		$db->query($sql);
	}
	
	$building_sold = 1;
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	if($building_sold){
?>
	<script type="text/javascript">
		bldgController.updateSlotEmptyLand(<?= $bldg_slot ?>);
		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
	</script>
	<h3>Sell <?= $bldg_name.' ('.$bldg_size.' m&#178;)' ?> - SOLD</h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
	<?php
		echo "You have sold the ",$bldg_name," for $",number_format($bldg_sale_value/100,2,'.',',');
	?>
<?php
	}else{
?>
	<h3>Sell <?= $bldg_name.' ('.$bldg_size.' m&#178;)' ?> - NOT SOLD</h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
	Your employees are glad that you chose to keep the facility.
<?php
	}
?>
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>