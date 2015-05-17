<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
// Absolute max number of buildings
$max_buildings = 32;
$initial_buildings = 12;

// Initialize Firm Cash and Max Building
$sql = "SELECT firms.cash, firms.max_bldg FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];
$firm_max_bldg = $firm['max_bldg'];
$bldg_slot = $firm_max_bldg + 1;

if($firm_max_bldg < $max_buildings && $ctrl_bldg_land){
	$cost_base = $firm_max_bldg - $initial_buildings + 1;
	$buy_land_cost = max(10000, $cost_base * $cost_base * $cost_base * 100000000);
}else{
	fbox_breakout('buildings.php');
}

// Deduct $ from firm
if($ctrl_leftover_allowance < $buy_land_cost){
	fbox_redirect("bldg-buy-land.php", 'Daily spending limit reached, redirecting...');
}
$sql = "UPDATE firms SET cash = cash - $buy_land_cost, max_bldg = max_bldg + 1 WHERE id = $eos_firm_id AND cash >= $buy_land_cost";
$affected = $db->query($sql)->rowCount();
if(!$affected){
	fbox_redirect("bldg-buy-land.php", 'Insufficient cash, redirecting...');
}
$sql = "INSERT INTO log_revenue (fid, is_debit, value, source, transaction_time) VALUES ('$eos_firm_id', 1, '$buy_land_cost', 'Land Purchase', NOW())";
$db->query($sql);
$sql = "UPDATE firms_positions SET used_allowance = used_allowance + $buy_land_cost WHERE fid = $eos_firm_id AND pid = $eos_player_id";
$db->query($sql);
$ctrl_leftover_allowance = ($ctrl_daily_allowance == -1) ? -1 : ($ctrl_leftover_allowance - $buy_land_cost);
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		bldgController.updateSlotEmptyLand(<?= $bldg_slot ?>);
		<?php if($bldg_slot < $max_buildings){ ?>
		bldgController.updateSlotBuyLand(<?= $bldg_slot + 1 ?>);
		<?php } ?>
		firmController.setCash("<?= $_SESSION['firm_cash'] ?>", <?= $ctrl_leftover_allowance ?>);
	</script>
	<h3>Land Purchased</h3>
	<img src="/eos/images/city/bldg_new_grass.gif" /></a><br /><br />
	Congratulations, you have just bought another piece of land!
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>