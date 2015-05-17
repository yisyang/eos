<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
	$view_type = 'purcs';
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
	// Discover target
	$target_firm_id = $eos_firm_id;
	if(isset($_GET['fid'])){
		$target_firm_id = filter_var($_GET['fid'], FILTER_SANITIZE_NUMBER_INT);
		$sql = "SELECT COUNT(*) FROM firms_positions WHERE fid = '$target_firm_id' AND pid = $eos_player_id";
		if(!($eos_player_id < 100 || $db->query($sql)->fetchColumn())){
			$target_firm_id = $eos_firm_id;
		}
	}
	$target_firm_id;
?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - B2B Market - History</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				firmController.firmId = <?= $eos_firm_id ?>;
				marketController.showTableHistory(1, 'history_<?= $view_type ?>', <?= $target_firm_id ?>, 0, 1);

				$('#b2b_table').on('mouseenter', '.market_firm_name', function(){
					var that = $(this);
					if (this.offsetWidth < this.scrollWidth && !that.attr('title'))
						that.attr('title', that.text());
				});
			});
			var searchTimeout;
			function initSearch(value, skipTimeout){
				clearTimeout(searchTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					doSearch(value);
				}else{
					searchTimeout = setTimeout("doSearch('" + value + "');", 1000);
				}
			}
			function doSearch(value){
				jQuery('#b2b_submenu .submenu').removeClass('active');
				jQuery('#b2b_submenu .searchbox_holder').addClass('active');
				marketController.showTableHistory(1, 'history_<?= $view_type ?>_search', <?= $target_firm_id ?>, value);
			}
		</script>
<?php require 'include/stats.php'; ?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-b2b-hist.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="b2b_submenu" class="default_submenu">
			<a href="market.php?view_type=new" class="submenu"><img src="/eos/images/b2b_new.gif" width="36" height="36" alt="[B2B New]" title="B2B (New Products)" /></a> 
			<a href="market.php?view_type=alpha" class="submenu"><img src="/eos/images/b2b_az.gif" width="36" height="36" alt="[B2B A-Z]" title="B2B (Alphabetical)" /></a> 
			<a href="market.php?view_type=store" class="submenu"><img src="/eos/images/b2b_store.gif" width="36" height="36" alt="[B2B by Store]" title="B2B (Filter by Store)" /></a> 
			<a href="market.php?view_type=cat" class="submenu"><img src="/eos/images/b2b_cat.gif" width="36" height="36" alt="[B2B by Cat]" title="B2B (Filter by Category)" /></a> 
			<a href="market.php?view_type=my" class="submenu"><img src="/eos/images/b2b_my.gif" width="36" height="36" alt="[B2B My]" title="B2B (My Listings)" /></a> 
			&nbsp;&nbsp; 
			<a href="market-requests.php?view_type=new" class="submenu"><img src="/eos/images/b2b_req_new.gif" width="36" height="36" alt="[Req A-Z]" title="B2B Requests (Alphabetical)" /></a> 
			<a href="market-requests.php?view_type=alpha" class="submenu"><img src="/eos/images/b2b_req_az.gif" width="36" height="36" alt="[Req A-Z]" title="B2B Requests (Alphabetical)" /></a> 
			<a href="market-requests.php?view_type=fact" class="submenu"><img src="/eos/images/b2b_req_fact.gif" width="36" height="36" alt="[Req Fact]" title="B2B Requests (Filter by Factory)" /></a> 
			<a href="market-requests.php?view_type=cat" class="submenu"><img src="/eos/images/b2b_req_cat.gif" width="36" height="36" alt="[Req Cat]" title="B2B Requests (Filter by Category)" /></a> 
			<a href="market-requests.php?view_type=my" class="submenu"><img src="/eos/images/b2b_req_my.gif" width="36" height="36" alt="[Req My]" title="B2B Requests (My Requests)" /></a> 
			&nbsp;&nbsp; 
			<a class="jqDialog submenu" href="market-add-request.php"><img src="/eos/images/b2b_req_add.gif" width="36" height="36" alt="[New Req]" title="Add New Request" /></a> 
			<a href="market-history.php?view_type=purcs" class="submenu <?= $view_type == 'purcs' ? 'active' : '' ?>"><img src="/eos/images/b2b_hist_purc.gif" width="36" height="36" alt="[Purc Hist]" title="B2B Purchase History" /></a> 
			<a href="market-history.php?view_type=sales" class="submenu <?= $view_type == 'sales' ? 'active' : '' ?>"><img src="/eos/images/b2b_hist_sales.gif" width="36" height="36" alt="[Sales Hist]" title="B2B Sales History" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search products" /></div>
		</div>
		<div id="b2b_top_nav" class="b2b_nav_container"></div>
		<table id="b2b_table" class="default_table"></table>
		<div class="b2b_nav_container"></div>
	</div>
<?php require 'include/foot.php'; ?>