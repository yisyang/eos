<?php require 'include/prehtml.php'; ?>
<?php require 'include/functions.php'; ?>
<?php
	$view_type = 'watchlist';
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
	$view_type_id = 0;
	if(isset($_GET['view_type_id'])){
		$view_type_id = filter_var($_GET['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	}
?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Stock Market</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				playerController.playerId = <?= $eos_player_id ?>;
				stockController.showTable(1, '<?= $view_type ?>', 0, 1);
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
					jQuery('#stock_submenu .submenu').removeClass('active');
					jQuery('#stock_submenu .searchbox_holder').addClass('active');
					stockController.showTable(1, 'search', search);
				}
			}
		</script>
<?php require 'include/stats.php'; ?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-stock.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="stock_submenu" class="default_submenu">
			<a href="stock.php?view_type=watchlist" class="submenu <?= $view_type == 'watchlist' ? 'active' : '' ?>"><img src="/eos/images/stock_home.gif" width="36" height="36" alt="[SE Home]" title="SE Home Screen" /></a> 
			<a href="stock.php?view_type=new" class="submenu <?= $view_type == 'new' ? 'active' : '' ?>"><img src="/eos/images/stock_new.gif" width="36" height="36" alt="[SE New]" title="All Companies (Newest First)" /></a> 
			<a href="stock.php?view_type=alpha" class="submenu <?= $view_type == 'alpha' ? 'active' : '' ?>"><img src="/eos/images/stock_az.gif" width="36" height="36" alt="[SE A-Z]" title="All Companies (Alphabetical)" /></a> 
			&nbsp;&nbsp; 
			<a href="stock-po.php" class="submenu"><img src="/eos/images/stock_po.gif" width="36" height="36" alt="[SE PO]" title="Public Offerings and Buybacks" /></a> 
			&nbsp;&nbsp; 
			<a class="jqDialog submenu" href="stock-add-order.php"><img src="/eos/images/stock_add_order.gif" width="36" height="36" alt="[New Order]" title="Add New Order" /></a> 
			<a href="stock-orders.php" class="submenu"><img src="/eos/images/stock_curr_orders.gif" width="36" height="36" alt="[Current Orders]" title="View Current Orders" /></a> 
			&nbsp;&nbsp; 
			<a href="stock-history.php" class="submenu"><img src="/eos/images/stock_hist.gif" width="36" height="36" alt="[SE Hist]" title="SE History" /></a> 
			<a href="stock-portfolio.php" class="submenu"><img src="/eos/images/stock_my.gif" width="36" height="36" alt="[SE MY]" title="SE Portfolio" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search Symbols" /></div>
		</div>
		<div id="stock_top_nav" class="stock_nav_container clearer"></div>
		<table id="stock_table" class="default_table"></table>
		<div class="stock_nav_container"></div>
		
	<?php
		if($view_type == 'watchlist'){
	?>
		<br /><br />
		<h3>Add to Watchlist</h3>
		<input id="new_watchlist_symbol" class="bigger_input" placeholder="Symbol" /> <input id="new_watchlist_symbol_btn" type="button" class="bigger_input" value="Add" />
		<script>			
			$('#new_watchlist_symbol').on('keypress', function(e){
				if(e.which == 13){
					stockController.addToWatchlist();
				}
			});
			$('#new_watchlist_symbol_btn').on('click', function(){
				stockController.addToWatchlist();
			});
		</script>
	<?php
		}
	?>
		<br /><br />
		<h3>Find Symbols</h3>
		<input id="new_find_symbol" class="bigger_input" style="width:250px;" placeholder="Company Name or Symbol" /> <input id="new_find_symbol_btn" type="button" class="bigger_input" value="Find" />
		<script>			
			$('#new_find_symbol').on('keypress', function(e){
				if(e.which == 13){
					stockController.findSymbol();
				}
			});
			$('#new_find_symbol_btn').on('click', function(){
				stockController.findSymbol();
			});
		</script>
		<table id="find_symbols_table" class="default_table"></table>
	</div>
<?php require 'include/foot.php'; ?>