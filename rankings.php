<?php require 'include/prehtml.php'; ?>
<?php
	$view_type = 'company_networth';
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Rankings</title>
		<style type="text/css">
			.two_col{
				float: left;
				width: 370px;
				padding: 0 0 0 15px;
			}
			.default_table tbody a{
				color: #222233 !important;
			}
			.default_table tbody td{
				height: 48px;
			}
			.ranking_item{
				font-size: 12px;
				width: 200px;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}
			.ranking_wrapper{
				position:relative;
				text-decoration:none;
			}
			.ranking_wrapper span{display:none;}
			.ranking_wrapper:hover span{
				display:block;
				position:absolute;
				top:1em; left:0; width:15em;
				padding:1em;
				border:1px solid #88bbff;
				background-color:#ffffff; color:#000000;
				font-family:'Lucida Grande', Verdana, Arial, sans-serif;
				font-size:12px;
				font-weight:normal;
				z-index:9999;
			}
			.ranking_wrapper:focus{outline:none;}
		</style>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				rankingsController.showTable(1, '<?= $view_type ?>', 1);
			});
			jQuery(document).on('mouseenter', '.ranking_item', function(){
				var that = $(this);
				if (this.offsetWidth < this.scrollWidth && !that.parent('.ranking_wrapper').length){
					that.wrap('<div class="ranking_wrapper" />');
					var wrap = that.parent('.ranking_wrapper');
					wrap.append('<span>' + that.html() + '</span>');
				}
			});
		</script>
<?php require 'include/stats.php'; ?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-rankings.jpg" style="padding-bottom: 10px;" /><br />';
	}else{
		echo '<br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<h3>More Company Rankings:</h3>
		<a href="rankings.php?view_type=company_networth"><input type="button" class="bigger_input" value="Top Networth" /></a>
		<a href="rankings.php?view_type=company_fame"><input type="button" class="bigger_input" value="Top Fame Level" /></a>
		<a href="rankings.php?view_type=company_cash"><input type="button" class="bigger_input" value="Top Cash" /></a>
		<a href="rankings.php?view_type=company_fact_all"><input type="button" class="bigger_input" value="Top Manufacturers" /></a>
		<a href="rankings.php?view_type=company_store_all"><input type="button" class="bigger_input" value="Top Wholesalers" /></a>
		<a href="rankings.php?view_type=company_rnd_all"><input type="button" class="bigger_input" value="Top Researchers" /></a>
		<a href="rankings.php?view_type=company_fact"><input type="button" class="bigger_input" value="Biggest Factories" /></a>
		<a href="rankings.php?view_type=company_store"><input type="button" class="bigger_input" value="Biggest Stores" /></a>
		<a href="rankings.php?view_type=company_rnd"><input type="button" class="bigger_input" value="Biggest R&amp;Ds" /></a>
		<a href="rankings.php?view_type=company_res"><input type="button" class="bigger_input" value="Highest Research" /></a>
		<br /><br />
		<h3>More Player Rankings:</h3>
		<a href="rankings.php?view_type=player_networth"><input type="button" class="bigger_input" value="Top Networth" /></a>
		<a href="rankings.php?view_type=player_fame"><input type="button" class="bigger_input" value="Top Fame Level" /></a>
		<a href="rankings.php?view_type=player_cash"><input type="button" class="bigger_input" value="Top Cash" /></a>
		<a href="rankings.php?view_type=player_influence"><input type="button" class="bigger_input" value="Top Influence" /></a>
		<a href="rankings.php?view_type=player_stock"><input type="button" class="bigger_input" value="Top Stock Holdings" /></a>
		<a href="rankings.php?view_type=player_salary"><input type="button" class="bigger_input" value="Top Salaries" /></a>
		<a href="rankings.php?view_type=player_jobs"><input type="button" class="bigger_input" value="Top Workaholics" /></a>
	</div>
	<br /><br />
		<div id="ranking_top_nav" class="ranking_nav_container clearer"></div>
		<div class="two_col">
			<table id="ranking_table_1" class="default_table" style="width:100% !important;"></table>
		</div>
		<div class="two_col">
			<table id="ranking_table_2" class="default_table" style="width:100% !important;"></table>
		</div>
		<div class="ranking_nav_container"></div>
<?php require 'include/foot.php'; ?>