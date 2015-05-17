<?php require 'include/prehtml.php'; ?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Player Rankings</title>
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
	$sql = "SELECT id, player_name, player_networth FROM players WHERE player_networth > 200000000 ORDER BY player_networth DESC LIMIT 0, 20";
	$top_nw = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT id, player_name, player_fame_level FROM players WHERE player_fame_level > 1 ORDER BY player_fame_level DESC LIMIT 0, 20";
	$top_fame = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	$sql = "SELECT id, player_name, player_cash FROM players WHERE player_cash > 100000000 ORDER BY player_cash DESC LIMIT 0, 20";
	$top_cash = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT id, player_name, influence FROM players ORDER BY influence DESC LIMIT 0, 10";
	$top_influence = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT players.id, players.player_name, SUM(player_stock.shares * firm_stock.share_price) AS stock_value FROM players LEFT JOIN player_stock ON players.id = player_stock.pid LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid GROUP BY players.id ORDER BY stock_value DESC LIMIT 0, 10";
	$top_stock = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT players.id, players.player_name, firms.name, firms_positions.pay_flat FROM firms_positions LEFT JOIN players ON firms_positions.pid = players.id LEFT JOIN firms ON firms_positions.fid = firms.id ORDER BY firms_positions.pay_flat DESC LIMIT 0, 10";
	$top_paid = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT players.id, players.player_name, COUNT(firms_positions.id) AS jobs FROM players LEFT JOIN firms_positions ON firms_positions.pid = players.id GROUP BY players.id ORDER BY jobs DESC, players.id DESC LIMIT 0, 10";
	$top_workers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-rankings.jpg" style="padding-bottom: 10px;" /><br />';
	}else{
		echo '<br />';
	}
?>
		<a href="rankings-company.php"><input type="button" class="bigger_input" value="Company Rankings" /></a>
		<input type="button" class="bigger_input" value="Player Rankings" disabled="disabled" /><br /><br />
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Networth [<a href="rankings.php?view_type=player_networth"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_nw as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'</a></div></td>';
						echo '<td>$'.number_format_readable($top_player['player_networth']/100).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Fame Level [<a href="rankings.php?view_type=player_fame"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_fame as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'</a></div></td>';
						echo '<td>'.$top_player['player_fame_level'].'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Cash [<a href="rankings.php?view_type=player_cash"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_cash as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'</a></div></td>';
						echo '<td>$'.number_format_readable($top_player['player_cash']/100).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Influence [<a href="rankings.php?view_type=player_influence"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_influence as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'</a></div></td>';
						echo '<td>'.number_format_readable($top_player['influence']).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Stock Holdings [<a href="rankings.php?view_type=player_stock"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_stock as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'</a></div></td>';
						echo '<td>$'.number_format_readable($top_player['stock_value']/100).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Salaries [<a href="rankings.php?view_type=player_salary"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_paid as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'<br /><small>'.$top_player['name'].'</small></a></div></td>';
						echo '<td>$'.number_format_readable($top_player['pay_flat']/100).'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>
		<div class="three_col">
			<table class="default_table" style="width:240px !important;">
				<thead>
					<tr><td colspan="3">Top Workaholics [<a href="rankings.php?view_type=player_jobs"><i>More</i></a>]</td></tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach($top_workers as $top_player){
						$i++;
						echo '<tr><td>'.$i.'</td>';
						echo '<td><div class="ranking_item"><a href="firm/'.$top_player['id'].'">'.$top_player['player_name'].'</a></div></td>';
						echo '<td>'.$top_player['jobs'].'</td></tr>';
					}
				?>
				</tbody>
			</table>
		</div>
		<div class="clearer no_select">&nbsp;</div>

<?php require 'include/foot.php'; ?>