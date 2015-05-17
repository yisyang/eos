<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_GET['fsid'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = 'store';
$shelf_slot = filter_var($_GET['shelf_slot'], FILTER_SANITIZE_NUMBER_INT);
if(!$bldg_id || !$shelf_slot){
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

// and that it is not under construction
$sql = "SELECT COUNT(*) FROM queue_build WHERE building_type = '$bldg_type' AND building_id = '$bldg_id'";
$count = $db->query($sql)->fetchColumn();
if($count){
	fbox_redirect('bldg-expand-status.php?type='.$bldg_type.'&id='.$bldg_id);
}
?>
<?php require 'include/functions.php'; ?>
		<script type="text/javascript">
			function stockShelf(pid){
				var params = {action: 'stock_shelf', fsid: <?= $bldg_id ?>, shelf_slot: <?= $shelf_slot ?>, sc_pid: pid};
				storeController.executeAjax(params, function(resp){
					jqDialogInit('stores-sell.php?fsid=<?= $bldg_id ?>');
				});
			}
			jQuery(document).ready(function(){
				jQuery('a#bldg_expand_button').on('click', function(){
					jqDialogInit('bldg-expand.php', {
						id : <?= $bldg_id ?>,
						type : '<?= $bldg_type ?>'
					});
				});
				jQuery('a#bldg_sell_button').on('click', function(){
					jqDialogInit('bldg-sell.php', {
						id : <?= $bldg_id ?>,
						type : '<?= $bldg_type ?>'
					});
				});
				jQuery('a#store_lazy_button').on('click', function(){
					storeController.lazyPricing(<?= $bldg_id ?>);
				});
			});
		</script>
<?php require 'include/stats_fbox.php'; ?>
<?php
	// Initialize bldg image for store
	$sql = "SELECT name, has_image FROM list_store WHERE id = $bldg_type_id";
	$list_bldg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($list_bldg["has_image"]){
		$bldg_img_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_bldg["name"]));
	}else{
		$bldg_img_filename = "no-image";
	}

	//Initialize store sales choices
	$sql = "SELECT list_prod.id, IFNULL(COUNT(firm_wh.id),0) AS count_active, IFNULL(SUM(firm_wh.pidn),0) AS pidn_total, IF(SUM(firm_wh.pidn),1,0) AS pidn_available, list_prod.cat_id, list_prod.name, list_prod.has_icon, list_prod.value, list_prod.selltime FROM list_prod LEFT JOIN list_store_choices ON list_prod.cat_id = list_store_choices.cat_id LEFT JOIN firm_wh ON firm_wh.pid = list_prod.id AND firm_wh.fid = $eos_firm_id WHERE list_store_choices.store_id = $bldg_type_id AND list_prod.id NOT IN (SELECT IFNULL(ulp.id,0) FROM firm_store_shelves LEFT JOIN firm_wh AS ufw ON firm_store_shelves.wh_id = ufw.id LEFT JOIN list_prod AS ulp oN ufw.pid = ulp.id WHERE firm_store_shelves.fsid = $bldg_id) GROUP BY list_prod.id ORDER BY pidn_available DESC, list_prod.name ASC";
	// $sql = "SELECT list_prod.id, IFNULL(COUNT(firm_wh.id),0) AS count_active, IFNULL(SUM(firm_wh.pidn),0) AS pidn_total, IF(COUNT(firm_wh.id),1,0) AS pidn_available, list_prod.cat_id, list_prod.name, list_prod.has_icon, list_prod.value, list_prod.selltime FROM list_prod LEFT JOIN list_store_choices ON list_prod.cat_id = list_store_choices.cat_id LEFT JOIN firm_wh ON firm_wh.pid = list_prod.id AND firm_wh.fid = $eos_firm_id WHERE list_store_choices.store_id = $bldg_type_id GROUP BY list_prod.id ORDER BY pidn_available DESC, list_prod.name ASC";
	$store_shelf_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
	<div style="float: left;padding-right: 15px;">
		<img src="/eos/images/<?= $bldg_type ?>/<?= $bldg_img_filename ?>.gif" width="180" height="80" />
	</div>
	<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;">
		<div class="building_name_container"><span class="building_name" id="building_name"><?= $bldg_name.' ('.$bldg_size.' m&#178; <a title="Marketing effect">+'.number_format(pow($store_marketing, 0.25),2,'.',',').'%</a>)' ?> 
		<?php if($ctrl_store_sell){ ?><img src="/eos/images/edit.gif" width="24" height="24" title="Rename Building" onclick="bldgController.showNameUpdater('<?= htmlspecialchars($bldg_name) ?>',<?= $bldg_id ?>,'<?= $bldg_type ?>');" /><?php } ?></span> <a class="jqDialog" href="bldg-swap-slot.php?bldg_id=<?= $bldg_id ?>&bldg_type=<?= $bldg_type ?>"><img src="/eos/images/swap.png" width="24" height="24" title="Move Building" /></a></div>
		<a id="bldg_expand_button" style="cursor:pointer;"><img src="/eos/images/button-build.gif" title="Expand Building" alt="[Expand]" /></a> &nbsp; 
		<a id="bldg_sell_button" style="cursor:pointer;"><img src="/eos/images/button-sell.gif" title="Sell Building" alt="[Sell]" /></a> &nbsp; 
		<a class="jqDialog" href="stores-marketing.php?fsid=<?= $bldg_id ?>"><img src="/eos/images/button-marketing.gif" title="Marketing" alt="[Marketing]" /></a> &nbsp; 
		<a href="/eos/market.php?view_type=store&view_type_id=<?= $bldg_type_id ?>"><img src="/eos/images/b2b_store.gif" title="View B2B Products" alt="[B2B]" /></a> &nbsp; 
		<a id="store_lazy_button" class="info" style="cursor:pointer;"><img src="/eos/images/lazy_2x.gif" alt="[Lazy 2X]" /><span style="line-height:1.5;">Start <b>all paused sales</b> at 2x cost or 2x quality-adjusted value, whichever is higher.<br><br><font color="#ff0000">There will be no more confirmation.</font></span></a>
	</div>
	<div class="clearer">
		<br /><h3>Select Item to Place on Shelf</h3>
	</div>
<?php
	if($settings_narrow_screen){
		$j_per_row = 5;
	}else{
		$j_per_row = 6;
	}
	echo '<div class="prod_choices">';
	echo '<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;">';
	echo '<a class="jqDialog" href="stores-sell.php?fsid=',$bldg_id,'"><img src="/eos/images/button-return-big.gif" title="Cancel" style="margin-bottom:6px;" /></a><br />';
	echo '</div></div>';
	echo '<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;">';
	echo '<a style="cursor:pointer;" onclick="stockShelf(0);"><img src="/eos/images/button-cancel-big.gif" title="Empty Shelf" style="margin-bottom:6px;" /></a><br />';
	echo '</div></div>';
	$j = 2;
	$store_shelf_choices_remaining = count($store_shelf_choices);
	foreach($store_shelf_choices as $store_shelf_choice){
		if($j == $j_per_row){
			$j = 0;
			echo '<div class="prod_choices">';
		}
		$j++;
		$store_shelf_choices_remaining--;
		
		if($store_shelf_choice['has_icon']){
			$sc_ipid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($store_shelf_choice['name']));
		}else{
			$sc_ipid_filename = "no-icon";
		}
		echo '<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;">';
		if($store_shelf_choice['count_active'] && $store_shelf_choice['pidn_total']){
			echo '<a style="cursor:pointer;" onclick="stockShelf(',$store_shelf_choice['id'],');"><img src="/eos/images/prod/large/',$sc_ipid_filename,'.gif" title="',$store_shelf_choice['name'],'" style="margin-bottom:6px;" /></a><br />';
			echo '<a title="Total Quantity: ',number_format($store_shelf_choice['pidn_total'],0,'.',','),'" class="vert_middle" style="margin: 0 0 0 10px;font-weight:normal;"><img src="/eos/images/box_closed.png" alt="#" title="Total" /> '.number_format_readable($store_shelf_choice['pidn_total']).'</a><br />';
			echo '<a href="/eos/market.php?view_type=prod&view_type_id=',$store_shelf_choice['id'],'"><input type="button" class="bigger_input" value="B2B" /></a><br />';
		}else{
			echo '<div style="position:absolute;left:0;top:0;width:96px;height:96px;"><img src="/eos/images/prod/large/not-available.png" title="',$store_shelf_choice['name'],' - Product not found in warehouse." /></div>';
			echo '<img src="/eos/images/prod/large/',$sc_ipid_filename,'.gif" title="',$store_shelf_choice['name'],'" style="margin-bottom:6px;" /><br /><img src="/eos/images/box.png" alt="#" title="Quantity" /> <small>Out of Stock</small><br /><a href="/eos/market.php?view_type=prod&view_type_id=',$store_shelf_choice['id'],'"><input type="button" class="bigger_input" value="B2B" /></a><br />';
		}
		echo '</div></div>';

		if($j == $j_per_row || !$store_shelf_choices_remaining){
			echo '</div>';
		}
	}
?>
	<div style="clear:both;">&nbsp;</div>
	<br />
	<a class="jqDialog" href="stores-sell.php?fsid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>