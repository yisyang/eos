<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
	if(!$ctrl_wh_view){
		header( 'Location: /eos/index.php' );
		exit();
	}
	$view_type = 'alpha';
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
	$view_type_id = 0;
	if(isset($_GET['view_type_id'])){
		$view_type_id = filter_var($_GET['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Warehouse</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				warehouseController.showTable(1, '<?= $view_type ?>', <?= $view_type_id ?>, 1);

				$('#wh_table').on('keypress', '.wh_tr td input', function(e){
					if(e.which == 13){
						var wh_id = $(e.target).closest('tr').attr('wh_id');
						warehouseController.sellToMarket(wh_id);
					}
				});
			});
			var searchTimeout, lastSearch;
			function initSearch(value, skipTimeout){
				clearTimeout(searchTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					doSearch(value);
				}else{
					searchTimeout = setTimeout("doSearch('" + value + "');", 1000);
				}
			}
			function doSearch(search){
				clearTimeout(searchTimeout);
				if(search !== lastSearch){
					lastSearch = search;
					jQuery('#wh_submenu .submenu').removeClass('active');
					jQuery('#wh_submenu .searchbox_holder').addClass('active');
					warehouseController.showTable(1, 'search', search);
				}
			}
		</script>
<?php require 'include/stats.php'; ?>
<?php
	if($firm_locked){
		fbox_breakout('/eos/index.php');
	}

	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-warehouse.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="wh_submenu" class="default_submenu">
			<a href="warehouse.php?view_type=new" class="submenu <?= $view_type == 'new' ? 'active' : '' ?>"><img src="/eos/images/wh_new.gif" width="36" height="36" alt="[WH New]" title="Warehouse (New Products)" /></a> 
			<a href="warehouse.php?view_type=alpha" class="submenu <?= $view_type == 'alpha' ? 'active' : '' ?>"><img src="/eos/images/wh_az.gif" width="36" height="36" alt="[WH A-Z]" title="Warehouse (Alphabetical)" /></a> 
			<a href="warehouse.php?view_type=fact" class="submenu <?= $view_type == 'fact' ? 'active' : '' ?>"><img src="/eos/images/wh_fact.gif" width="36" height="36" alt="[WH by Factory]" title="Warehouse (Filter by Factory)" /></a> 
			<a href="warehouse.php?view_type=store" class="submenu <?= $view_type == 'store' ? 'active' : '' ?>"><img src="/eos/images/wh_store.gif" width="36" height="36" alt="[WH by Store]" title="Warehouse (Filter by Store)" /></a> 
			<a href="warehouse.php?view_type=cat" class="submenu <?= $view_type == 'cat' ? 'active' : '' ?>"><img src="/eos/images/wh_cat.gif" width="36" height="36" alt="[WH by Cat]" title="Warehouse (Filter by Category)" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search products" /></div>
		</div>
		<div id="type_id_choices" class="type_id_choices"></div>
		<div id="wh_top_nav" class="wh_nav_container clearer"></div>
		<table id="wh_table" class="default_table compact"></table>
		<div class="wh_nav_container"></div>
	</div>
<?php require 'include/foot.php'; ?>