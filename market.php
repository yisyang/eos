<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
	$view_type = 'new';
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
	$view_type_id = 0;
	if(isset($_GET['view_type_id'])){
		$view_type_id = filter_var($_GET['view_type_id'], FILTER_SANITIZE_NUMBER_INT);
	}
?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - B2B Market - Listings</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				firmController.firmId = <?= $eos_firm_id ?>;
				marketController.showTable(1, '<?= $view_type ?>', <?= $view_type_id ?>, 1);

				$('#b2b_table').on('keypress', '.b2b_tr td input', function(e){
					if(e.which == 13){
						var b2b_id = $(e.target).closest('tr').attr('b2b_id');
						marketController.buy(b2b_id);
					}
				});
				$('#b2b_table').on('mouseenter', '.market_firm_name', function(){
					var that = $(this);
					if (this.offsetWidth < this.scrollWidth && !that.attr('title'))
						that.attr('title', that.text());
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
					jQuery('#b2b_submenu .submenu').removeClass('active');
					jQuery('#b2b_submenu .searchbox_holder').addClass('active');
					marketController.showTable(1, 'search', search);
				}
			}
		</script>
<?php require 'include/stats.php'; ?>
<?php
	if($firm_locked){
		fbox_breakout('/eos/index.php');
	}

	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-b2b.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="b2b_submenu" class="default_submenu">
			<a href="market.php?view_type=new" class="submenu <?= $view_type == 'new' ? 'active' : '' ?>"><img src="/eos/images/b2b_new.gif" width="36" height="36" alt="[B2B New]" title="B2B Listings (New Products)" /></a> 
			<a href="market.php?view_type=alpha" class="submenu <?= $view_type == 'alpha' ? 'active' : '' ?>"><img src="/eos/images/b2b_az.gif" width="36" height="36" alt="[B2B A-Z]" title="B2B Listings (Alphabetical)" /></a> 
			<a href="market.php?view_type=store" class="submenu <?= $view_type == 'store' ? 'active' : '' ?>"><img src="/eos/images/b2b_store.gif" width="36" height="36" alt="[B2B Store]" title="B2B Listings (Filter by Store)" /></a> 
			<a href="market.php?view_type=cat" class="submenu <?= $view_type == 'cat' ? 'active' : '' ?>"><img src="/eos/images/b2b_cat.gif" width="36" height="36" alt="[B2B Cat]" title="B2B Listings (Filter by Category)" /></a> 
			<a href="market.php?view_type=my" class="submenu <?= $view_type == 'my' ? 'active' : '' ?>"><img src="/eos/images/b2b_my.gif" width="36" height="36" alt="[B2B My]" title="B2B Listings (My Listings)" /></a> 
			&nbsp;&nbsp; 
			<a href="market-requests.php?view_type=new" class="submenu"><img src="/eos/images/b2b_req_new.gif" width="36" height="36" alt="[Req A-Z]" title="B2B Requests (Alphabetical)" /></a> 
			<a href="market-requests.php?view_type=alpha" class="submenu"><img src="/eos/images/b2b_req_az.gif" width="36" height="36" alt="[Req A-Z]" title="B2B Requests (Alphabetical)" /></a> 
			<a href="market-requests.php?view_type=fact" class="submenu"><img src="/eos/images/b2b_req_fact.gif" width="36" height="36" alt="[Req Fact]" title="B2B Requests (Filter by Factory)" /></a> 
			<a href="market-requests.php?view_type=cat" class="submenu"><img src="/eos/images/b2b_req_cat.gif" width="36" height="36" alt="[Req Cat]" title="B2B Requests (Filter by Category)" /></a> 
			<a href="market-requests.php?view_type=my" class="submenu"><img src="/eos/images/b2b_req_my.gif" width="36" height="36" alt="[Req My]" title="B2B Requests (My Requests)" /></a> 
			&nbsp;&nbsp; 
			<a class="jqDialog submenu" href="market-add-request.php"><img src="/eos/images/b2b_req_add.gif" width="36" height="36" alt="[New Req]" title="Add New Request" /></a> 
			<a href="market-history.php?view_type=purcs" class="submenu"><img src="/eos/images/b2b_hist_purc.gif" width="36" height="36" alt="[Purc Hist]" title="B2B Purchase History" /></a> 
			<a href="market-history.php?view_type=sales" class="submenu"><img src="/eos/images/b2b_hist_sales.gif" width="36" height="36" alt="[Sales Hist]" title="B2B Sales History" /></a> 

			<div class="searchbox_holder"><input class="searchbox" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search products" /></div>
		</div>
		<div id="type_id_choices" class="type_id_choices"></div>
		<div id="b2b_top_nav" class="b2b_nav_container clearer"></div>
		<table id="b2b_table" class="default_table market_for_sale compact"></table>
		<div class="b2b_nav_container"></div>
	</div>
<?php require 'include/foot.php'; ?>