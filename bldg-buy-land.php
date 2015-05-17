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

if($firm_max_bldg < $max_buildings){
	$cost_base = $firm_max_bldg - $initial_buildings + 1;
	$buy_land_cost = max(10000, $cost_base * $cost_base * $cost_base * 100000000);
}else{
	fbox_breakout('buildings.php');
}
?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Buy Land</h3>
	<img src="/eos/images/city/bldg_buy_land_grass.gif" /></a><br /><br />
<?php
	if(!$ctrl_bldg_land){
?>
		You are not authorized to purchase land lots for this company.<br />
<?php
	}else{
?>
	Real estate prices are ridiculous these days. Would you like to buy this piece of land for <br /><img src="images/money.gif" alt="Cash:" title="Cash" /> $<?php echo number_format($buy_land_cost/100, 0, '', ','); ?>?<br /><br />
	<?php
		if($firm_cash < $buy_land_cost){
			echo '<a class="info"><span><font color="#ff0000">The company does not have enough cash.</font></span><img src="/eos/images/button-trade-inactive.gif" alt="[Cannot Purchase Land]" /></a>';
		}else if($ctrl_leftover_allowance < $buy_land_cost){
			echo '<a class="info"><span><font color="#ff0000">Cost exceeds your daily spending limit.</font></span><img src="/eos/images/button-trade-inactive.gif" alt="[Cannot Purchase Land]" /></a>';
		}else{
			echo '<a class="jqDialog info" href="bldg-buy-land-start.php"><span>Click to purchase land. <br /><br /><font color="#ff0000">There will be no more confirmation.<br />This action is non-undoable.</font></span><img src="/eos/images/button-trade.gif" alt="[Purchase Land]" /></a>';
		}
	?>
<?php
	}
?>
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>