<?php require '../include/prehtml_subd.php'; ?>
<?php
		$player_found = 0;
		if(isset($_GET["player_id"])){
			$player_id = 0+filter_var($_GET["player_id"], FILTER_SANITIZE_NUMBER_INT);
			$query = $db->prepare("SELECT players.*, players_extended.player_created, players_extended.player_desc FROM players LEFT JOIN players_extended ON players.id = players_extended.id WHERE players.id = ?");
			$query->execute(array($player_id));
			$player = $query->fetch(PDO::FETCH_ASSOC);
			$player_found = empty($player) ? 0 : 1;
		}
		if(isset($_GET["player_alias"])){
			$player_alias = filter_var($_GET["player_alias"], FILTER_SANITIZE_STRING);
			$query = $db->prepare("SELECT players.*, players_extended.player_created, players_extended.player_desc FROM players LEFT JOIN players_extended ON players.id = players_extended.id WHERE players.player_alias = ?");
			$query->execute(array($player_alias));
			$player = $query->fetch(PDO::FETCH_ASSOC);
			$player_found = empty($player) ? 0 : 1;
		}
		if($player_found){
			$player_name = $player["player_name"];
			$page_title = 'Player Info - '.$player_name;
		}else{
			$page_title = 'Player Info';
		}
?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - <?= $page_title ?></title>
<?php require '../include/head_subd.php'; ?>
<?php
		if($player_found){
			// Initialize Descriptions - total 15
			$player_level_desc = array("Student", "Businessman", "Entrepreneur", "Millionaire", "Manager", "General Manager", "CEO", "Chairman", "Capitalist", "Billionaire", "Industrialist", "Tycoon", "Trillionaire", "Dynast", "Deity");
			$player_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Known", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
			
			// Match Player Stats
			$player_id = $player["id"];
			$player_fid = $player["fid"];
			$player_name = $player["player_name"];
			$player_is_in_jail = $player["in_jail"];
			$player_last_active = strtotime($player["last_active"])+0;
			$player_last_active_passed = time() - $player_last_active;
			if($player_last_active_passed < 900){
				$player_last_active = "Within 15 minutes";
			}else{
				$player_last_active = date("F j, Y, g:i A",$player_last_active);
			}
			$player_desc = stripcslashes($player["player_desc"]);
			if(!$player_desc){
				$player_desc = "None";
			}
			$player_avatar = $player["avatar_filename"];
			$player_cash = $player["player_cash"];
			$player_networth = $player["player_networth"];
			$player_level = $player["player_level"];
			$player_fame_level = $player["player_fame_level"];
			$player_age = floor((time() - $player["player_created"])/604800) + 18;
			$player_vip_level = $player["vip_level"];

			$player_cash_display = '$'.number_format($player_cash/100,2,'.',',').' ($'.number_format_readable($player_cash/100).')';
			$player_networth_display = '$'.number_format($player_networth/100,2,'.',',').' ($'.number_format_readable($player_networth/100).')';
			$player_level_display = $player_level_desc[$player_level].' (Level '.$player_level.')';
			$player_fame_display = $player_fame_desc[min(19,floor($player_fame_level/5))].' (Level '.$player_fame_level.')';
			
			// Populate Stock Portfolio, $player_stock_display_count controls the number of rows to display
			$player_stock_display_count = 5;
			$query = $db->prepare("SELECT firm_stock.fid, firm_stock.symbol, player_stock.shares, player_stock.shares * firm_stock.share_price AS stock_value FROM player_stock LEFT JOIN firm_stock ON player_stock.fid = firm_stock.fid WHERE player_stock.pid = ? ORDER BY stock_value DESC LIMIT 0,".($player_stock_display_count + 1));
			$query->execute(array($player_id));
			$results = $query->fetchAll(PDO::FETCH_ASSOC);
			$count = count($results);
			if($count){
				$player_stock_portfolio = '<table class="default_table default_table_smallfont" style="width:450px;"><thead><tr><td>Symbol</td><td>Shares</td><td>Value</td></tr></thead><tbody>';
				for($i=0;$i<min($player_stock_display_count,$count);$i++){
					$player_stock_portfolio .= '<tr><td>'.$results[$i]['symbol'].'</td><td>'.number_format_readable($results[$i]['shares']).'</td><td>$'.number_format_readable($results[$i]['stock_value']/100).'</td></tr>';
				}
				$player_stock_portfolio .= '</tbody></table>';
				if($count > $player_stock_display_count){
					$player_stock_portfolio .= '<div style="font-size:12px;color:#505050;padding:4px 0;line-height:130%;">*Displayed above are '.$player_name.'\'s  top '.$player_stock_display_count.' holdings by value, other holdings exist but are not displayed here.</div><br />';
				}
			}else{
				$player_stock_portfolio = 'None<br />';
			}
			
			// Populate Ownership Stats
			$query = $db->prepare("SELECT firms.id, firms.name, firms.networth, firms_positions.title FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.pid = ? ORDER BY firms.networth DESC");
			$query->execute(array($player_id));
			$firms = $query->fetchAll(PDO::FETCH_ASSOC);

			// TODO: also check for firms where player has over 10% holding
?>
			<div class="subd_body">
				<div style="float:left;width:450px;padding:15px;line-height:160%;">
				<?php
					// Add avatar
					echo '
					<h3>',$player_name,' <a href="/eos/messages.php?action=write&recipient_id='.$player_id.'"><img src="/eos/images/mail_write.png" width="24" height="24" title="Write to ',$player_name,'" /></a></h3>';
					if($player_avatar){
						echo '<img src="/eos/images/players/',$player_avatar,'" alt="something" /><br />';
					}
					if(count($firms)){
						foreach($firms as $firm){
							echo $firm["title"].' of <a href="/eos/firm/'.$firm["id"].'">'.$firm["name"].'</a><br />';
						}
					}else{
						echo 'Full-Time Investor<br />';
					}
					if($player_is_in_jail > time()){
						if($player_is_in_jail > time() + 86400 * 365){
							echo '<small>Sentenced to life in <a href="/eos/players/jail.php">prison</a></small><br />';
						}else{
							echo '<small><a href="/eos/players/jail.php">Jailed</a> until: ',date("F j, Y, g:i A",$player_is_in_jail),'</small><br />';
						}
					}else{
						echo '<small>(Player Last Active: ',$player_last_active,')</small><br />';
					}
					echo'<br />
					<span style="display:inline-block;width:100px;">Level: </span>',$player_level_display,'<br />
					<span style="display:inline-block;width:100px;">Fame: </span>',$player_fame_display,'<br />
					<span style="display:inline-block;width:100px;">Networth: </span>',$player_networth_display,'<br />
					<span style="display:inline-block;width:100px;">Cash: </span>',$player_cash_display,'<br />
					<span style="display:inline-block;width:100px;">Age: </span>',$player_age,'<br />
					<br />
					<h3><i>Description</i></h3>
					',$player_desc,'<br />
					<br />
					<h3><i>Stock Portfolio</i></h3>
					',$player_stock_portfolio,'
					<br />
					<h3><i>Achievements</i></h3>';
					if($player_vip_level == 2){
						echo '<img src="/eos/images/badges/vip_platinum.gif">';
					}else if($player_vip_level == 1){
						echo '<img src="/eos/images/badges/vip.gif">';
					}
					$query = $db->prepare("SELECT list_achievements.name, list_achievements.filename FROM (SELECT aid FROM player_achievements WHERE pid = ?) AS a LEFT JOIN list_achievements ON a.aid = list_achievements.id");
					$query->execute(array($player_id));
					$results = $query->fetchAll(PDO::FETCH_ASSOC);
					$count = count($results);
					if($count){
						foreach($results as $result){
							$achievement_title = $result['name'];
							$achievement_filename = $result['filename'];
							echo '<img src="/eos/images/badges/',$achievement_filename,'.gif" alt="',$achievement_title,'" title="',$achievement_title,'">';
						}
					}else{
						if(!$player_vip_level){
							echo 'None';
						}
					}
					echo '<br /><br />
					<h3><i>Collectibles</i></h3>
					<small>Coming Soon</small><br />
					<br />
					';
				?>
				</div>
				<div class="subd_chart" style="width:480px;padding:15px 15px 15px 0;">
					<img src="/eos/players/player_history.php?pid=<?= $player_id ?>" width="480" height="450" />
				</div>
				<div class="clearer no_select">&nbsp;</div>
			</div>
<?php
		}else{
?>
			<div style="width: 100%;min-height: 680px;background-color: #faf8e1;border-top: 1px solid #666666;border-bottom: 1px solid #666666;">
				<div style="padding: 15px;">
					The player was not found.
				</div>
			</div>
<?php
		}
?>
<?php require '../include/foot_subd.php'; ?>