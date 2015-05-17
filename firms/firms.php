<?php require '../include/prehtml_subd.php'; ?>
<?php
		$firm_found = 0;
		if(isset($_GET["firm_id"])){
			$firm_id = 0+filter_var($_GET["firm_id"], FILTER_SANITIZE_NUMBER_INT);
			$query = $db->prepare("SELECT firms.*, IFNULL(firm_stock.symbol, 0) AS stock_symbol FROM firms LEFT JOIN firm_stock ON firm_stock.fid = firms.id WHERE firms.id = ?");
			$query->execute(array($firm_id));
			$firm_result = $query->fetch(PDO::FETCH_ASSOC);
			$firm_found = empty($firm_result) ? 0 : 1;
		}else if(isset($_GET["firm_alias"])){
			$firm_alias = filter_var($_GET["firm_alias"], FILTER_SANITIZE_STRING);
			$query = $db->prepare("SELECT firms.*, IFNULL(firm_stock.symbol, 0) AS stock_symbol FROM firms LEFT JOIN firm_stock ON firm_stock.fid = firms.id WHERE firms.alias = ?");
			$query->execute(array($firm_alias));
			$firm_result = $query->fetch(PDO::FETCH_ASSOC);
			$firm_found = empty($firm_result) ? 0 : 1;
		}
		if($firm_found){
			$firm_name = $firm_result["name"];
			$page_title = 'Company Info - '.$firm_name;
		}else{
			$page_title = 'Company Info';
		}
?>
<?php require '../include/html_subd.php'; ?>
		<title><?= GAME_TITLE ?> - <?= $page_title ?></title>
<?php require '../include/head_subd.php'; ?>
<?php
		if($firm_found){
			//Initialize Descriptions - total 15
			$firm_level_desc = array("Garage Shop", "Fledgling Start-Up", "Start-Up", "Small Enterprise", "Medium Enterprise", "Large Enterprise", "Nano Cap", "Micro Cap", "Small Cap", "Mid Cap", "Large Cap", "Conglomerate", "Large Conglomerate", "MNC", "Corporate Empire");
			$firm_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Known", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
			
			//Match Firm Stats
			$firm_id = $firm_result["id"];
			$firm_name = $firm_result["name"];
			$firm_last_active = strtotime($firm_result["last_active"])+0;
			$firm_last_active_passed = time() - $firm_last_active;
			if($firm_last_active_passed < 900){
				$firm_last_active = "Within 15 minutes";
			}else{
				$firm_last_active = date("F j, Y, g:i A",$firm_last_active);
			}
			$firm_vacation_out = strtotime($firm_result["vacation_out"])+0;
			if($firm_vacation_out > time()){
				$firm_on_vacation = 1;
				$firm_vacation_out = date("F j, Y, g:i A",$firm_vacation_out);
			}else{
				$firm_on_vacation = 0;
			}
			$firm_cash = $firm_result["cash"];
			$firm_loan = $firm_result["loan"];
			$firm_networth = $firm_result["networth"];
			$firm_level = $firm_result["level"];
			$firm_fame_level = $firm_result["fame_level"];

			$firm_cash_display = '$'.number_format($firm_cash/100,2,'.',',').' ($'.number_format_readable($firm_cash/100).')';
			$firm_loan_display = '$'.number_format($firm_loan/100,2,'.',',').' ($'.number_format_readable($firm_loan/100).')';
			$firm_networth_display = '$'.number_format($firm_networth/100,2,'.',',').' ($'.number_format_readable($firm_networth/100).')';
			$firm_level_display = $firm_level_desc[$firm_level].' (Level '.$firm_level.')';
			$firm_fame_display = $firm_fame_desc[min(19,floor($firm_fame_level/5))].' (Level '.$firm_fame_level.')';
			
			//Populate Top Researches
			$query = $db->prepare("SELECT pid, quality, name, has_icon FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE fid = ? ORDER BY firm_tech.quality DESC LIMIT 0,3");
			$query->execute(array($firm_id));
			$res_results = $query->fetchAll(PDO::FETCH_ASSOC);
			$res_count = count($res_results);
			$top_res_display = "";
			if($res_count){
				foreach($res_results as $res_result){
					if($res_result["has_icon"]){
						$pid_filename = preg_replace(array("/[\s\&\']/","/___/"), '_', strtolower($res_result["name"]));
					}else{
						$pid_filename = 'no-icon';
					}
					$top_res_display .= '<div style="float:left;width:105px;text-align:center;">';
					$top_res_display .= '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$res_result["pid"].'"><img src="/eos/images/prod/large/'.$pid_filename.'.gif" alt="'.$res_result["name"].'" title="'.$res_result["name"].'" /></a>';
					$top_res_display .= '<div style="text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">Q'.$res_result["quality"].'</div>';
					$top_res_display .= '</div>';
				}
				$top_res_display .= '<div class="clearer no_select">&nbsp;</div>';
			}else{
				$top_res_display = "None<br />";
			}
?>
			<div class="subd_body">
				<div style="float:left;width:450px;padding:15px;line-height:160%;">
				<?php
					// Add avatar
					// Right side add histogram
					echo '<big>',$firm_name;
					if($firm_result["stock_symbol"]){
						echo ' (ESE: <a href="/eos/stock-details.php?ss='.$firm_result['stock_symbol'].'">'.$firm_result["stock_symbol"].'</a>)';
					}
					echo '</big><br />';
					
					$query = $db->prepare("SELECT COUNT(*) FROM market_prod WHERE fid = ?");
					$query->execute(array($firm_id));
					$count = $query->fetchColumn();
					if($count){
						echo '<a href="/eos/market.php?view_type=firm&view_type_id=',$firm_id,'"><img src="/eos/images/b2b_my.gif" width="36" height="36"></a><br />';
					}
					// Find Workers
					$query = $db->prepare("SELECT firms_positions.title, players.id AS player_id, players.player_name 
					FROM firms_positions 
					LEFT JOIN players ON firms_positions.pid = players.id 
					WHERE firms_positions.fid = ? 
					ORDER BY starttime ASC LIMIT 0,10");
					$query->execute(array($firm_id));
					$employee_results = $query->fetchAll(PDO::FETCH_ASSOC);
					$employee_count = count($employee_results);
					if($employee_count){
						foreach($employee_results as $employee_result){
							echo $employee_result["title"],': <a href="/eos/player/',$employee_result["player_id"],'">',$employee_result["player_name"],'</a>',' <a href="/eos/messages.php?action=write&recipient_id='.$employee_result['player_id'].'"><img src="/eos/images/mail_write.png" width="24" height="24" title="Write to ',$employee_result["player_name"],'" /></a><br />';
						}
					}else{
						echo 'Currently without leadership.<br />';
					}
					if($firm_on_vacation){
						echo '<small>(Vacation Mode - Locked Until: ',$firm_vacation_out,')</small><br />';
					}else{
						echo '<small>(Company Last Active: ',$firm_last_active,')</small><br />';
					}
					echo '<br />
					<span style="display:inline-block;width:100px;">Level: </span>',$firm_level_display,'<br />
					<span style="display:inline-block;width:100px;">Fame: </span>',$firm_fame_display,'<br />
					<span style="display:inline-block;width:100px;">Networth: </span>',$firm_networth_display,'<br />
					<span style="display:inline-block;width:100px;">Cash: </span>',$firm_cash_display,'<br />
					<span style="display:inline-block;width:100px;">Loan: </span>',$firm_loan_display,'<br />
					<br />
					Top Researches (<a href="/eos/firms/firm_tech.php?fid=',$firm_id,'">Show All</a>)<br />
					',$top_res_display,'<br />
					';
				?>
				</div>
				<div class="subd_chart" style="width:480px;padding:15px 15px 15px 0;">
					<img src="/eos/firms/firm_history.php?fid=<?= $firm_id ?>" width="480" height="450" />
				</div>
				<div class="clearer no_select">&nbsp;</div>
			</div>
<?php
		}else{
?>
			<div style="width: 100%;min-height: 680px;background-color: #faf8e1;border-top: 1px solid #666666;border-bottom: 1px solid #666666;">
				<div style="padding: 15px;">
					The company was not found.
				</div>
			</div>
<?php
		}
?>
<?php require '../include/foot_subd.php'; ?>