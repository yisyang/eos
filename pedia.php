<?php require 'include/prehtml.php'; ?>
<?php
	$view_type = 'prod';
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
	$view_type_id = 0;
	if(isset($_GET['view_type_id'])){
		$view_type_id = filter_var($_GET['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	}
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - EoS Pedia</title>
<?php require 'include/head.php'; ?>
<?php require 'include/stats.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				pediaController.showTable(1, '<?= $view_type ?>', <?= $view_type_id ?>, 'name', 1, 1);
			});
			var searchTimeout, lastSearch, lastSearchType;
			function initSearch(value, skipTimeout){
				clearTimeout(searchTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					doSearch(value);
				}else{
					searchTimeout = setTimeout("doSearch('" + value + "');", 1000);
				}
			}
			function doSearch(search, searchType){
				clearTimeout(searchTimeout);
				if(typeof(searchType) === "undefined") searchType = 'search';
				if(searchType !== lastSearchType || search !== lastSearch){
					lastSearch = search;
					lastSearchType = searchType;
					jQuery('#pedia_submenu .submenu').removeClass('active');
					jQuery('#pedia_submenu .searchbox_holder').addClass('active');
					pediaController.showTable(1, searchType, search, 'name', 1, 1);
				}
			}
		</script>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-pedia.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="pedia_submenu" class="default_submenu">
			<a href="pedia.php?view_type=prod" class="submenu <?= $view_type == 'prod' ? 'active' : '' ?>"><img src="/eos/images/pedia_prod.gif" width="36" height="36" alt="[Info Prod]" title="Product Information" /></a> 
			<a href="pedia.php?view_type=fact" class="submenu <?= $view_type == 'fact' ? 'active' : '' ?>"><img src="/eos/images/pedia_fact.gif" width="36" height="36" alt="[Info Fact]" title="Factory Information" /></a> 
			<a href="pedia.php?view_type=store" class="submenu <?= $view_type == 'store' ? 'active' : '' ?>"><img src="/eos/images/pedia_store.gif" width="36" height="36" alt="[Info Store]" title="Store Information" /></a> 
			<a href="pedia.php?view_type=cat" class="submenu <?= $view_type == 'cat' ? 'active' : '' ?>"><img src="/eos/images/pedia_cat.gif" width="36" height="36" alt="[Info Cat]" title="Product Information by Category" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search products" /></div>
		</div>
		<div id="suggest_search"><br />Would you like to search in other places? <input type="button" class="bigger_input" value="Products" onclick="doSearch(lastSearch, 'search');" /> <input type="button" class="bigger_input" value="Buildings" onclick="doSearch(lastSearch, 'buildings_search');" /> <input type="button" class="bigger_input" value="Categories" onclick="doSearch(lastSearch, 'cats_search');" /><br /><br /></div>
		<div id="type_id_choices" class="type_id_choices"></div>
		<div id="pedia_top_nav" class="pedia_nav_container clearer"></div>
		<table id="pedia_table" class="default_table"></table>
		<div class="pedia_nav_container"></div>
		
		<br />
		<div class="tbox_inline">
			Notes for the Curious:<br />
			* Average Quality is calculated from store sales.<br />
			** Leading Tech is calculated from the average research quality of the top 5 research leaders for each product.<br />
			*** Demand scales with total building size (to simulate population). It is also driven positively by lower average price and higher average quality.
		</div>
	</div>
<?php require 'include/foot.php'; ?>