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

// and that it is not under construction
$sql = "SELECT COUNT(*) FROM queue_build WHERE building_type = '$bldg_type' AND building_id = '$bldg_id'";
$count = $db->query($sql)->fetchColumn();
if($count){
	fbox_redirect('bldg-expand-status.php?type='.$bldg_type.'&id='.$bldg_id);
}
?>
<?php require 'include/functions.php'; ?>
<?php			
	// Initialize bldg image for store
	$sql = "SELECT name, has_image FROM list_store WHERE id = $bldg_type_id";
	$list_bldg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($list_bldg["has_image"]){
		$bldg_img_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_bldg["name"]));
	}else{
		$bldg_img_filename = "no-image";
	}

	// Initialize store copy choices
	$sql = "SELECT firm_store.id AS fsid, firm_store.store_name, firm_store_shelves.shelf_slot, list_prod.id, list_prod.cat_id, list_prod.name, list_prod.has_icon FROM firm_store LEFT JOIN firm_store_shelves ON firm_store.fid = $eos_firm_id AND firm_store.store_id = $bldg_type_id AND firm_store.id = firm_store_shelves.fsid LEFT JOIN firm_wh ON firm_store_shelves.wh_id = firm_wh.id AND firm_wh.fid = $eos_firm_id LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE firm_store_shelves.fsid != $bldg_id ORDER BY firm_store.id ASC, firm_store_shelves.shelf_slot ASC";
	$store_copy_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
		<script type="text/javascript">
			function storeCopyStart(sfsid){
				var params = {action: 'copy', fsid: <?= $bldg_id ?>, sfsid: sfsid};
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
	<div style="float: left;padding-right: 15px;">
		<img src="/eos/images/<?= $bldg_type ?>/<?= $bldg_img_filename ?>.gif" width="180" height="80" />
	</div>
	<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;">
		<div class="building_name_container"><span class="building_name" id="building_name"><?= $bldg_name.' ('.$bldg_size.' m&#178; <a title="Marketing effect">+'.number_format(pow($store_marketing, 0.25),2,'.',',').'%</a>)' ?> 
		<?php if($ctrl_store_sell){ ?><img src="/eos/images/edit.gif" width="24" height="24" onclick="bldgController.showNameUpdater('<?= htmlspecialchars($bldg_name) ?>',<?= $bldg_id ?>,'<?= $bldg_type ?>');" /><?php } ?></span></div>
		<a id="bldg_expand_button" style="cursor:pointer;"><img src="/eos/images/button-build.gif" title="Expand Building" alt="[Expand]" /></a> &nbsp; 
		<a id="bldg_sell_button" style="cursor:pointer;"><img src="/eos/images/button-sell.gif" title="Sell Building" alt="[Sell]" /></a> &nbsp; 
		<a class="jqDialog" href="stores-marketing.php?fsid=<?= $bldg_id ?>"><img src="/eos/images/button-marketing.gif" title="Marketing" alt="[Marketing]" /></a> &nbsp; 
		<a href="/eos/market.php?view_type=store&view_type_id=<?= $bldg_type_id ?>"><img src="/eos/images/b2b_store.gif" title="View B2B Products" alt="[B2B]" /></a> &nbsp; 
		<a id="store_lazy_button" class="info" style="cursor:pointer;"><img src="/eos/images/lazy_2x.gif" alt="[Lazy 2X]" /><span style="line-height:1.5;">Start <b>all paused sales</b> at 2x cost or 2x quality-adjusted value, whichever is higher.<br><br><font color="#ff0000">There will be no more confirmation.</font></span></a>
	</div>
	<div class="clearer">
		<br /><h3>Copy Store Layout</h3>
	</div>
<?php
	if(!empty($store_copy_choices)){
		$total_shelves = 8;
		for($k = 1; $k <= $total_shelves; $k++){
			$sc_shelf_in_use[$k] = 0;
		}
		$sc_temp_fsid = 0;
		$store_copy_choices_remaining = count($store_copy_choices);
		foreach($store_copy_choices as $store_copy_choice){
			$store_copy_choices_remaining--;
			$output_fsid = 0;
			if($sc_temp_fsid != $store_copy_choice['fsid']){
				if($sc_temp_fsid == 0){
					$sc_temp_fsid = $store_copy_choice['fsid'];
					$sc_store_name = $store_copy_choice['store_name'];
				}else{
					$output_fsid = 1;
				}
			}else if(!$store_copy_choices_remaining){
				$sc_shelf_slot = $store_copy_choice['shelf_slot'];
				$sc_shelf_in_use[$sc_shelf_slot] = 1;
				$sc_pid[$sc_shelf_slot] = $store_copy_choice['id'];
				$sc_cat_id[$sc_shelf_slot] = $store_copy_choice['cat_id'];
				$sc_name[$sc_shelf_slot] = $store_copy_choice['name'];
				$sc_has_icon[$sc_shelf_slot] = $store_copy_choice['has_icon'];
				$output_fsid = 1;	// Store data first, then output, then store again but it is the last one so who cares
			}
			if($output_fsid){
				echo $sc_store_name,' - <a style="cursor:pointer;" onclick="storeCopyStart(',$sc_temp_fsid,')"><input type="button" class="bigger_input" value="Copy"></a> <a class="jqDialog" href="stores-sell.php?fsid=',$sc_temp_fsid,'"><input type="button" class="bigger_input" value="Visit"></a><br /><br />';
				for($k = 1; $k <= $total_shelves; $k++){
					if($sc_shelf_in_use[$k]){
						if($sc_has_icon[$k]){
							$sc_ipid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($sc_name[$k]));
						}else{
							$sc_ipid_filename = "no-icon";
						}
						echo '<img src="/eos/images/prod/large/',$sc_ipid_filename,'.gif" title="',$sc_name[$k],'" style="margin-bottom:6px;" width="48" height="48" /> ';
					}else{
						echo '<img src="/eos/images/prod/large/no-icon.gif" title="Empty" style="margin-bottom:6px;" width="48" height="48" /> ';
					}
				}
				echo '<br /><br /><br />';
				$sc_temp_fsid = $store_copy_choice['fsid'];
				$sc_store_name = $store_copy_choice['store_name'];
				for($k = 1; $k <= $total_shelves; $k++){
					$sc_shelf_in_use[$k] = 0;
				}
			}
			$sc_shelf_slot = $store_copy_choice['shelf_slot'];
			$sc_shelf_in_use[$sc_shelf_slot] = 1;
			$sc_pid[$sc_shelf_slot] = $store_copy_choice['id'];
			$sc_cat_id[$sc_shelf_slot] = $store_copy_choice['cat_id'];
			$sc_name[$sc_shelf_slot] = $store_copy_choice['name'];
			$sc_has_icon[$sc_shelf_slot] = $store_copy_choice['has_icon'];
		}
	}else{
		echo 'This company does not have any other active stores of this type.';
	}
?>
	<div style="clear:both;">&nbsp;</div>
	<br />
	<a class="jqDialog" href="stores-sell.php?fsid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>