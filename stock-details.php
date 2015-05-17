<?php require 'include/prehtml.php'; ?>
<?php
	if(isset($_GET["ss"])){
		$stock_symbol = filter_var($_GET["ss"], FILTER_SANITIZE_STRING);
	}
	if(!$stock_symbol){
		header( 'Location: stock.php' );
		exit();
	}
	// Fetch fid and stock info from firm_stock
	$query = $db->prepare("SELECT firm_stock.id, firm_stock.fid, firm_stock.shares_os, firm_stock.share_price, firm_stock.share_price_min, firm_stock.share_price_max, firm_stock.dividend, firm_stock.7de, firm_stock.last_active, firms.name AS firm_name, firms.alias AS firm_alias FROM firm_stock LEFT JOIN firms ON firm_stock.fid = firms.id WHERE firm_stock.symbol = :symbol");
	$query->execute(array(':symbol' => $stock_symbol));
	$stock_details = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($stock_details)){
		header( 'Location: stock.php' );
		exit();
	}

	$stock_firm_id = $stock_details['fid'];
	$stock_market_cap = $stock_details['shares_os'] * $stock_details['share_price'];
	$stock_eps = $stock_details['7de'] / $stock_details['shares_os'];
	if($stock_eps > 0.5 || $stock_eps < -0.5){
		$stock_pe = $stock_details['share_price']/$stock_eps;
		$stock_pe_display = number_format_readable($stock_pe);
	}else{
		$stock_pe = 9999999999;
		$stock_pe_display = 'N/A';
	}
	if($stock_details['firm_alias'] !== NULL){
		$stock_firm_title = '<a href="/eos/firm/'.urlencode($stock_details['firm_alias']).'">'.$stock_details['firm_name'].'</a> ('.$stock_symbol.')';
	}else{
		$stock_firm_title = '<a href="/eos/firm/'.$stock_firm_id.'">'.$stock_details['firm_name'].'</a> ('.$stock_symbol.')';
	}
	
	// Populate stock chart
	$sql = "SELECT history_datetime, share_price FROM history_stock_fine WHERE fid = $stock_firm_id ORDER BY id ASC LIMIT 0,10000";
	$stock_history = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Stock Market - <?= $stock_symbol ?></title>
<?php require 'include/head.php'; ?>
	<script type="text/javascript" src="scripts/jqplot/jquery.jqplot.custom.js?ver=1.0"></script>
	<link rel="stylesheet" type="text/css" href="scripts/jqplot/jquery.jqplot.rj.css" />
	<script type="text/javascript">
			jQuery(document).ready(function(){
				playerController.playerId = <?= $eos_player_id ?>;
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
					jQuery('#stock_details_container').hide();
					stockController.showTable(1, 'search', search);
				}
			}
		</script>
<?php require 'include/stats.php'; ?>
	<div id="eos_narrow_screen_padding">
		<div id="stock_submenu" class="default_submenu">
			<a href="stock.php?view_type=watchlist" class="submenu"><img src="/eos/images/stock_home.gif" width="36" height="36" alt="[SE Home]" title="SE Home Screen" /></a> 
			<a href="stock.php?view_type=new" class="submenu"><img src="/eos/images/stock_new.gif" width="36" height="36" alt="[SE New]" title="All Companies (Newest First)" /></a> 
			<a href="stock.php?view_type=alpha" class="submenu"><img src="/eos/images/stock_az.gif" width="36" height="36" alt="[SE A-Z]" title="All Companies (Alphabetical)" /></a> 
			&nbsp;&nbsp; 
			<a href="stock-po.php" class="submenu"><img src="/eos/images/stock_po.gif" width="36" height="36" alt="[SE PO]" title="Public Offerings and Buybacks" /></a> 
			&nbsp;&nbsp; 
			<a class="jqDialog submenu" href="stock-add-order.php"><img src="/eos/images/stock_add_order.gif" width="36" height="36" alt="[New Order]" title="Add New Order" /></a> 
			<a href="stock-orders.php" class="submenu"><img src="/eos/images/stock_curr_orders.gif" width="36" height="36" alt="[Current Orders]" title="View Current Orders" /></a> 
			&nbsp;&nbsp; 
			<a href="stock-history.php" class="submenu"><img src="/eos/images/stock_hist.gif" width="36" height="36" alt="[SE Hist]" title="SE History" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search Symbols" /></div>
		</div>
		<div id="stock_top_nav" class="stock_nav_container clearer"></div>
		<table id="stock_table" class="default_table"></table>
		<div class="stock_nav_container"></div>

		<div id="stock_details_container">
			<div style="float:left;">
				<h3><?= $stock_firm_title ?></h3>
				<h1>$<?= number_format($stock_details['share_price']/100,2,'.',',') ?></h1><br />
			</div>
			<div style="float:left;padding:10px;">
				<a class="jqDialog" href="stock-add-order.php?ss=<?= $stock_symbol ?>"><img src="/eos/images/button-trade.gif" title="Add Order for <?= $stock_symbol ?>" /></a><br />
			</div>
			<div class="clearer no_select"></div>
			<div id="stock_plot" style="position:relative;top:0;left:0;height:300px;width:680px;"></div>
			<div id="stock_controller_plot" style="position:relative;top:0;left:0;height:80px;width:680px;"></div>
			<div class="clearer no_select">&nbsp;</div>
			<span id="sd_btn_fundamentals" class="mimic_button no_select active" onclick="stockController.showStockDetails(<?= $stock_firm_id ?>, 'fundamentals');">Fundamentals</span> 
			<span id="sd_btn_revenue" class="mimic_button no_select" onclick="stockController.showStockDetails(<?= $stock_firm_id ?>, 'revenue');">Revenue Sheet</span> 
			<span id="sd_btn_recent_b2b" class="mimic_button no_select" onclick="stockController.showStockDetails(<?= $stock_firm_id ?>, 'recent_b2b');">Recent B2B Activity</span> 
			<span id="sd_btn_buildings" class="mimic_button no_select" onclick="stockController.showStockDetails(<?= $stock_firm_id ?>, 'buildings');">Buildings</span> 
			<span id="sd_btn_researches" class="mimic_button no_select" onclick="stockController.showStockDetails(<?= $stock_firm_id ?>, 'researches');">Researches</span> 
			<span id="sd_btn_shareholders" class="mimic_button no_select" onclick="stockController.showStockDetails(<?= $stock_firm_id ?>, 'shareholders');">Shareholders</span> 
			<br /><br />

			<div id="stock_details_content">Loading...</div>

			<script type="text/javascript">
				jQuery(document).ready(function(){
					stockController.showStockDetails(<?= $stock_firm_id ?>, 'fundamentals');

					stockController.stockData = [
					<?php
						$stock_di = array();
						foreach($stock_history as $stock_data){
							$stock_di[] = "['".$stock_data['history_datetime']."',". $stock_data['share_price']/100 ."]";
						}
						echo implode(',', $stock_di);
					?>
					];

					stockController.stockPlot = jQuery.jqplot ('stock_plot', [stockController.stockData], {
						seriesColors: ['#3333ff'],
						seriesDefaults: { showMarker: false, shadow: false },
						axes: {
							xaxis: {
								renderer: $.jqplot.DateAxisRenderer,
								tickOptions: {formatString:'%b %e (%#I %p)'}, 
								// min: "2013-03-01",
								// max: "2013-06-01",
								// tickInterval: "1 week",
							},
							yaxis: {
								// label: 'Price',
								autoscale: true,
								tickOptions: {
									formatString: "$%'.2f"
								}
								// labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
								// labelOptions: { angle: -90 }
							},
						},
						cursor:{
							show: true,
							zoom: true,
							showTooltip: false
						},
						highlighter: {
							show: true,
							showLabel: false,
							tooltipAxes: 'xy',
							yvalues: 1,
							formatString: '<div class="jqPlotTooltip">%s<br />%s</div>',
							tooltipLocation : 'ne'
						}
					});
					
					stockController.stockControllerPlot = $.jqplot('stock_controller_plot', [stockController.stockData], {
						seriesColors: ['#3333ff'],
						seriesDefaults: { showMarker: false, shadow: false },
						axes: {
							xaxis: {
								renderer: $.jqplot.DateAxisRenderer,
								tickOptions: {formatString:'%b %e'}
							},
							yaxis: {
								autoscale: true,
								tickOptions: {
									formatString: "$%'.2f"
								}
							}
						},
						cursor:{
							show: true,
							zoom: true,
							showTooltip: false,
							constrainZoomTo: 'x'
						}
					});

					$.jqplot.Cursor.zoomProxy(stockController.stockPlot, stockController.stockControllerPlot);
					$.jqplot._noToImageButton = true;
				});
			</script>
		</div>

		<div class="clearer"></div><br />
	</div>
<?php require 'include/foot.php'; ?>