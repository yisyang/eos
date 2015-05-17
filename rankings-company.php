<?php require 'include/prehtml.php'; ?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Company Rankings</title>
		<style type="text/css">
			.three_col{
				float: left;
				width: 242px;
				padding: 0 9px;
			}
			.default_table tbody a{
				color: #222233 !important;
			}
			.default_table tbody td{
				height: 48px;
			}
			.ranking_item{
				font-size: 12px;
				width: 110px;
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
	$sql = "SELECT id, name, networth FROM firms ORDER BY networth DESC LIMIT 0, 20";
	$top_nw = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT id, name, fame_level FROM firms ORDER BY fame_level DESC LIMIT 0, 20";
	$top_fame = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT id, name, cash FROM firms ORDER BY cash DESC LIMIT 0, 20";
	$top_cash = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, SUM(firm_fact.size) AS total_size FROM firms LEFT JOIN firm_fact ON firms.id = firm_fact.fid GROUP BY firms.id ORDER BY total_size DESC LIMIT 0, 10";
	$top_manufacturers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, SUM(firm_store.size) AS total_size FROM firms LEFT JOIN firm_store ON firms.id = firm_store.fid GROUP BY firms.id ORDER BY total_size DESC LIMIT 0, 10";
	$top_wholesalers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, SUM(firm_rnd.size) AS total_size FROM firms LEFT JOIN firm_rnd ON firms.id = firm_rnd.fid GROUP BY firms.id ORDER BY total_size DESC LIMIT 0, 10";
	$top_researchers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, list_fact.name AS bldg_name, firm_fact.size FROM firms LEFT JOIN firm_fact ON firms.id = firm_fact.fid LEFT JOIN list_fact ON firm_fact.fact_id = list_fact.id ORDER BY firm_fact.size DESC, firm_fact.id ASC LIMIT 0, 10";
	$top_facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, list_store.name AS bldg_name, firm_store.size FROM firms LEFT JOIN firm_store ON firms.id = firm_store.fid LEFT JOIN list_store ON firm_store.store_id = list_store.id ORDER BY firm_store.size DESC, firm_store.id ASC LIMIT 0, 10";
	$top_stores = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, list_rnd.name AS bldg_name, firm_rnd.size FROM firms LEFT JOIN firm_rnd ON firms.id = firm_rnd.fid LEFT JOIN list_rnd ON firm_rnd.rnd_id = list_rnd.id ORDER BY firm_rnd.size DESC, firm_rnd.id ASC LIMIT 0, 10";
	$top_rnds = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firms.id, firms.name, list_prod.name AS prod_name, firm_tech.quality FROM firms LEFT JOIN firm_tech ON firms.id = firm_tech.fid LEFT JOIN list_prod ON list_prod.id = firm_tech.pid WHERE firm_tech.quality > 5 ORDER BY firm_tech.quality DESC, firm_tech.update_time ASC LIMIT 0, 30";
	$top_res = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$top_res_count = count($top_res);
?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-rankings.jpg" style="padding-bottom: 10px;" /><br />';
	}else{
		echo '<br />';
	}
?>
		<input type="button" class="bigger_input" value="Company Rankings" disabled="disabled" />
		<a href="rankings-player.php"><input type="button" class="bigger_input" value="Player Rankings" /></a><br /><br />
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Networth [<a href="rankings.php?view_type=company_networth"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_nw as $top_nwer){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_nwer['id'].'">'.$top_nwer['name'].'</a></div></td>';
						echo '<td>$'.number_format_readable($top_nwer['networth']/100).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Fame Level [<a href="rankings.php?view_type=company_fame"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_fame as $top_famer){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_famer['id'].'">'.$top_famer['name'].'</a></div></td>';
						echo '<td>'.$top_famer['fame_level'].'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Cash [<a href="rankings.php?view_type=company_cash"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_cash as $top_casher){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_casher['id'].'">'.$top_casher['name'].'</a></div></td>';
						echo '<td>$'.number_format_readable($top_casher['cash']/100).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Manufacturers [<a href="rankings.php?view_type=company_fact_all"><i>More</i></a>]<br />(Total Factory Size in m&#178;)</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_manufacturers as $top_manufacturer){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_manufacturer['id'].'">'.$top_manufacturer['name'].'</a></div></td>';
						echo '<td>'.number_format_readable($top_manufacturer['total_size']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Wholesalers [<a href="rankings.php?view_type=company_store_all"><i>More</i></a>]<br />(Total Store Size in m&#178;)</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_wholesalers as $top_wholesaler){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_wholesaler['id'].'">'.$top_wholesaler['name'].'</a></div></td>';
						echo '<td>'.number_format_readable($top_wholesaler['total_size']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Researchers [<a href="rankings.php?view_type=company_rnd_all"><i>More</i></a>]<br />(Total R&amp;D Size in m&#178;)</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_researchers as $top_researcher){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_researcher['id'].'">'.$top_researcher['name'].'</a></div></td>';
						echo '<td>'.number_format_readable($top_researcher['total_size']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Biggest Factories [<a href="rankings.php?view_type=company_fact"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_facts as $top_fact){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_fact['id'].'">'.$top_fact['name'].'<br /><small>'.$top_fact['bldg_name'].'</small></a></div></td>';
						echo '<td>'.number_format_readable($top_fact['size']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Biggest Stores [<a href="rankings.php?view_type=company_store"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_stores as $top_store){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_store['id'].'">'.$top_store['name'].'<br /><small>'.$top_store['bldg_name'].'</small></a></div></td>';
						echo '<td>'.number_format_readable($top_store['size']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Biggest R&amp;Ds [<a href="rankings.php?view_type=company_rnd"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_rnds as $top_rnd){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_rnd['id'].'">'.$top_rnd['name'].'<br /><small>'.$top_rnd['bldg_name'].'</small></a></div></td>';
						echo '<td>'.number_format_readable($top_rnd['size']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Highest Research [<a href="rankings.php?view_type=company_res"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$count = min($top_res_count, 10);
					for($i=0;$i<$count;$i++){
						echo '<tr><td>'.($i+1).'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_res[$i]['id'].'">'.$top_res[$i]['name'].'<br /><small>'.$top_res[$i]['prod_name'].'</small></a></div></td>';
						echo '<td>'.$top_res[$i]['quality'].'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Highest Research (cont.)</td></tr>
				</thead>
				<tbody>
				<?php
					$count = min($top_res_count, 20);
					for($i=10;$i<$count;$i++){
						echo '<tr><td>'.($i+1).'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_res[$i]['id'].'">'.$top_res[$i]['name'].'<br /><small>'.$top_res[$i]['prod_name'].'</small></a></div></td>';
						echo '<td>'.$top_res[$i]['quality'].'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Highest Research (cont.)</td></tr>
				</thead>
				<tbody>
				<?php
					$count = min($top_res_count, 30);
					for($i=20;$i<$count;$i++){
						echo '<tr><td>'.($i+1).'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_res[$i]['id'].'">'.$top_res[$i]['name'].'<br /><small>'.$top_res[$i]['prod_name'].'</small></a></div></td>';
						echo '<td>'.$top_res[$i]['quality'].'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>

<?php require 'include/foot.php'; ?>