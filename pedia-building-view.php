<?php require 'include/prehtml_no_auth.php'; ?>
<?php
	if(!isset($_GET["id"]) || !isset($_GET["type"])){
		fbox_breakout('pedia.php');
	}

	// Initialize buildings
	$bldg_id = filter_var($_GET["id"], FILTER_SANITIZE_NUMBER_INT);
	$bldg_type = filter_var($_GET["type"], FILTER_SANITIZE_STRING);
	if($bldg_type != "fact" && $bldg_type != "store" && $bldg_type != "rnd"){
		fbox_breakout('pedia.php');
	}
	
	if($bldg_type == "fact"){
		$bldg_type_title = "Factory";
		$sql = "SELECT name, cost, timecost, firstcost, firsttimecost, has_image FROM list_fact WHERE id = $bldg_id";
	}
	if($bldg_type == "store"){
		$bldg_type_title = "Store";
		$sql = "SELECT name, cost, timecost, firstcost, firsttimecost, has_image FROM list_store WHERE id = $bldg_id";
	}
	if($bldg_type == "rnd"){
		$bldg_type_title = "R&D Facility";
		$sql = "SELECT name, cost, timecost, firstcost, firsttimecost, has_image FROM list_rnd WHERE id = $bldg_id";
	}
	// Product info from list_prod
	$building = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($building)){
		echo 'Building not found.';
		exit();
	}

	if($building["has_image"]){
		$building_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($building["name"]));
	}else{
		$building_filename = "no-image";
	}

	// Populate cats and prods
	if($bldg_type == "fact"){
		$sql = "SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.has_icon FROM list_fact_choices left JOIN list_prod ON list_fact_choices.opid1 = list_prod.id WHERE list_fact_choices.fact_id = $bldg_id GROUP BY list_prod.id ORDER BY list_prod.name ASC";
	}
	if($bldg_type == "store"){
		$sql = "SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.has_icon FROM list_store_choices left JOIN list_prod ON list_store_choices.cat_id = list_prod.cat_id WHERE list_store_choices.store_id = $bldg_id GROUP BY list_prod.id ORDER BY list_prod.name ASC";
	}
	if($bldg_type == "rnd"){
		$sql = "SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.has_icon FROM list_rnd_choices left JOIN list_prod ON list_rnd_choices.cat_id = list_prod.cat_id WHERE list_rnd_choices.rnd_id = $bldg_id GROUP BY list_prod.id ORDER BY list_prod.name ASC";
	}
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox_no_auth.php'; ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		if(modalController.backLink != ''){
			jQuery('.backLinkHolder').html('<a class="jqDialog" href="' + modalController.backLink + '"><input type="button" class="bigger_input" value="' + modalController.backLinkTitle + '" /></a> ');
			jQuery('div.backLinkHolder').css({paddingBottom: '8px'});
		}
	});
</script>
<div class="backLinkHolder"></div>

<div style="float:left;width:180px;padding-right:20px;"><img src="/eos/images/<?php echo $bldg_type; ?>/<?php echo $building_filename; ?>.gif" /></div>
<div style="float:left;width:450px;padding-top:2px;vertical-align:middle;">
	<h3 style="margin-bottom:6px;"><?php echo $building["name"]; ?></h3>
	Type: <b><?php echo $bldg_type_title; ?></b><br /><br />
	<div style="float:left;width:50%;padding-top:2px;vertical-align:middle;">
		<b>Construction Cost</b><br />
		<img style="vertical-align:middle;" src="/eos/images/money.gif" title="Initial Cost" /> $<?php echo number_format_readable($building["firstcost"]/100); ?><br />
		<img style="vertical-align:middle;" src="/eos/images/time.gif" title="Initial Time" /> $<?php echo sec2hms($building["firsttimecost"]); ?><br /><br />
	</div>
	<div style="float:left;width:50%;padding-top:2px;vertical-align:middle;">
		<b>Expansion Cost (per m&#178;)</b><br />
		<img style="vertical-align:middle;" src="/eos/images/money.gif" title="Expansion Cost per m&178;" /> $<?php echo number_format_readable($building["cost"]/100); ?><br />
		<img style="vertical-align:middle;" src="/eos/images/time.gif" title="Expansion Time per m&178;" /> $<?php echo sec2hms($building["timecost"]); ?> <br />
	</div>
	<div class="clearer"></div>
</div>
<div class="clearer"></div><br />

<h3>Products:</h3>
<?php
if(!count($prods)){
	echo 'N/A<br />';
}else{
	foreach($prods as $prod){
		echo '<div style="float:left;width:50px;">';
		if($prod['has_icon']){
			$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod["name"]));
		}else{
			$filename = "no-icon";
		}
		echo '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$prod['id'].'"><img src="/eos/images/prod/large/'.$filename.'.gif" title="'.$prod["name"].'" width="48" height="48" /></a> ';
		echo '</div>';
	}
}
?>
<div class="clearer"></div><br />

<span class="backLinkHolder"></span>
<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>