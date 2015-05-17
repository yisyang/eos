<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = 0;
$bldg_type = 'undefined';
if(isset($_GET['bldg_id'])){
	$bldg_id = filter_var($_GET['bldg_id'], FILTER_SANITIZE_NUMBER_INT);
}
if(isset($_GET['bldg_type'])){
	$bldg_type = filter_var($_GET['bldg_type'], FILTER_SANITIZE_STRING);
}
$ctrl_sell = max($ctrl_fact_sell, $ctrl_store_sell, $ctrl_rnd_sell);
if(!$ctrl_sell){
	fbox_breakout('buildings.php');
}

// Get complete list of buildings and their slots
$sql = "SELECT id, name, size, slot, type FROM (
	(SELECT firm_fact.id, firm_fact.fact_name AS name, firm_fact.size, firm_fact.slot, 'fact' AS type FROM firm_fact WHERE firm_fact.fid = $eos_firm_id) UNION
	(SELECT firm_store.id, firm_store.store_name AS name, firm_store.size, firm_store.slot, 'store' AS type FROM firm_store WHERE firm_store.fid = $eos_firm_id) UNION
	(SELECT firm_rnd.id, firm_rnd.rnd_name, firm_rnd.size, firm_rnd.slot, 'rnd' AS type FROM firm_rnd WHERE firm_rnd.fid = $eos_firm_id)
	UNION
	(SELECT queue_build.id AS id, 'Under Construction' AS name, queue_build.newsize, queue_build.building_slot AS slot, 'queue' AS type FROM queue_build WHERE queue_build.fid = $eos_firm_id)
) AS a ORDER BY slot ASC";
$buildings = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Get max number of slots
$sql = "SELECT max_bldg FROM firms WHERE id = $eos_firm_id";
$max_bldg = $db->query($sql)->fetchColumn();

// Prepare list
for($i = 1; $i <= $max_bldg; $i++){
	$swap_bldg_id[$i] = 0;
	$swap_bldg_type[$i] = 'vacant';
	$swap_bldg_desc[$i] = 'Slot '.$i.': Vacant Lot';
}
foreach($buildings as $building){
	$swap_bldg_id[$building['slot']] = $building['id'];
	$swap_bldg_type[$building['slot']] = $building['type'];
	$swap_bldg_desc[$building['slot']] = 'Slot '.$building['slot'].': '.$building['name'].' ('.$building['size'].' m&#178;)';
}

?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<h3 class="vert_middle">Swap Buildings <a class="info"><img src="/eos/images/info.png" /><span>The swap building option utilitizes a very costly "magical teleportation" devices that distorts time and space, and soon it will be only accessible to a small privileged group of people (VIP members). Use it while you still have access!</span></a></h3>
	<div id="swap_building_prompt">
		<select id="swap_building_1">
			<option value="0">-- First Slot --</option>
		<?php
			for($i = 1; $i <= $max_bldg; $i++){
				echo '<option value="'.$i.'_'.$swap_bldg_id[$i].'_'.$swap_bldg_type[$i].'"'.(($bldg_id == $swap_bldg_id[$i] && $bldg_type == $swap_bldg_type[$i]) ? ' selected="selected"' : '').'>'.$swap_bldg_desc[$i].'</option>';
			}
		?>
		</select> <===>
		<select id="swap_building_2">
			<option value="0">-- Second Slot --</option>
		<?php
			for($i = 1; $i <= $max_bldg; $i++){
				echo '<option value="'.$i.'_'.$swap_bldg_id[$i].'_'.$swap_bldg_type[$i].'">'.$swap_bldg_desc[$i].'</option>';
			}
		?>
		</select>
		<br /><br />
		<a style="cursor:pointer;" onclick="bldgController.swapBldgs();"><input type="button" class="bigger_input" value="Swap" /></a> 
	</div>
	<br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Cancel and Close" />
<?php require 'include/foot_fbox.php'; ?>