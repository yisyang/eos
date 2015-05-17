<?php require 'include/prehtml_no_auth.php'; ?>
<?php
	if(!isset($_GET["cat_id"])){
		fbox_breakout('pedia.php');
	}

	// Initialize cat
	$cat_id = filter_var($_GET["cat_id"], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT id, name FROM list_cat WHERE id = '$cat_id' AND name NOT LIKE '-%'";
	$cat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($cat)){
		echo 'Category not found.';
		exit();
	}

	// Get products
	$sql = "SELECT id, name, has_icon FROM list_prod WHERE cat_id = $cat_id ORDER BY name ASC";
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

<div style="float:left;width:150px;height:100px;"><img src="/eos/images/cat/large/<?= preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($cat["name"])) ?>.gif" /></div>
<div style="float:left;width:290px;height:98px;padding-top:2px;vertical-align:middle;">
	<h3 style="margin-bottom:6px;"><?= $cat["name"] ?></h3>
</div>

<div class="clearer no_select"></div><br />
<h3>Products in This Category:</h3>
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