<?php
// Company Stats
if($eos_firm_id){
	$query = $db->prepare("SELECT firms.name, firms.networth, firms.cash, firms.loan, firms.level, firms.fame_level, firms.fame_exp, firms.vacation_out, firms_extended.auto_repay_loan FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms.id = ?");
	$query->execute(array($eos_firm_id));
	$firm = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		echo "Fatal Error: Error code on STATS-008. Firm not found.";
		exit();
	}else{
		// Initialize Descriptions - total 15
		$firm_level_desc = array("Garage Shop", "Fledgling Start-Up", "Start-Up", "Small Enterprise", "Medium Enterprise", "Large Enterprise", "Nano Cap", "Micro Cap", "Small Cap", "Mid Cap", "Large Cap", "Conglomerate", "Large Conglomerate", "MNC", "Corporate Empire");
		// NW limit: 				0 					250k 			500k 			1kk 				3kk		 			10kk 			30kk			100kk		300kk 		1kkk 		10kkk 		100kkk 				1kkkk			10kkkk		100kkkk
		$firm_level_size = sizeof($firm_level_desc);
		$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
		$firm_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Acclaimed", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
		// Match Firm Stats
		$firm_name = $firm["name"];
		$firm_networth = $firm["networth"];
		$firm_cash = $firm["cash"];
		$firm_loan = $firm["loan"];
		$settings_auto_repay_loan = $firm["auto_repay_loan"];
		$firm_locked_time = strtotime($firm["vacation_out"]);
		$firm_locked = 0;
		if($firm_locked_time > time()){
			$firm_locked = 1;
		}
		$firm_level = $firm["level"];
		$firm_fame_level = $firm["fame_level"];
		$firm_fame_level_next = $firm_fame_level + 1;
		$firm_fame_exp = $firm["fame_exp"];
		$firm_fame_level_next_exp = 100 * $firm_fame_level_next * $firm_fame_level_next * $firm_fame_level_next;
		if($firm_fame_exp >= $firm_fame_level_next_exp){
			while($firm_fame_exp >= $firm_fame_level_next_exp){
				$firm_fame_level = $firm_fame_level_next;
				$firm_fame_level_next += 1;
				$firm_fame_exp -= $firm_fame_level_next_exp;
				$firm_fame_level_next_exp = 100 * $firm_fame_level_next * $firm_fame_level_next * $firm_fame_level_next;
			}
			$query = $db->prepare("UPDATE firms SET fame_level = ?, fame_exp = ? WHERE id = ?");
			$query->execute(array($firm_fame_level, $firm_fame_exp, $eos_firm_id));
		}

		$_SESSION['firm_name'] = $firm_name;
		$_SESSION['firm_cash'] = $firm_cash;
		$_SESSION['firm_loan'] = $firm_loan;
		$firm_cash_display = '<a class="info">$'.number_format_readable(min($ctrl_leftover_allowance, $firm_cash)/100).'<span style="width:240px !important;">Cash: $'.number_format($firm_cash/100,2,'.',',').'<br />Allowance: '.($ctrl_daily_allowance == -1 ? 'Unlimited' : '$'.number_format($ctrl_leftover_allowance/100,2,'.',',')).'</span></a>';
		$firm_loan_display = '<a class="info">$'.number_format_readable($firm_loan/100).'<span style="width:240px !important;">Loan: $'.number_format($firm_loan/100,2,'.',',').'</span></a>';
		$firm_networth_display = '<a class="info">$'.number_format_readable($firm_networth/100).'<span style="width:240px !important;">Networth: $'.number_format($firm_networth/100,2,'.',',').'<br />Networth is updated daily around midnight server time.</span></a>';
		if($firm_level + 1 < $firm_level_size){
			$firm_level_display = '<a class="info">'.$firm_level_desc[$firm_level].'<span style="width:240px !important;">Company Level: '.$firm_level.' ('.$firm_level_desc[$firm_level].')<br />Next Level: '.($firm_level+1).' ('.$firm_level_desc[$firm_level+1].')<br />At $'.number_format($firm_level_lowlimit[$firm_level+1]/100, 0, '.', ',').' NW ($'.number_format_readable($firm_level_lowlimit[$firm_level+1]/100).')</span></a>';
		}else{
			$firm_level_display = '<a class="info">'.$firm_level_desc[$firm_level].'<span style="width:240px !important;">Congratulations!<br />Your company is at the highest level.<br />Perhaps it\'s time to turn to charity and leave some market for the smaller companies.</span></a>';
		}
		$firm_fame_display = '<a class="info">'.$firm_fame_desc[min(19,floor($firm_fame_level/5))].'<span style="width:240px !important;">Company Fame Level: '.$firm_fame_level.' ('.$firm_fame_desc[min(19,floor($firm_fame_level/5))].')<br />Next Level: '.$firm_fame_level_next.' ('.$firm_fame_desc[min(19,floor($firm_fame_level_next/5))].')<br />Current EXP: '.$firm_fame_exp.'/'.$firm_fame_level_next_exp.'</span></a>';
	}
}
	// Player Stats and Messages
	$query = $db->prepare("SELECT player_name, player_cash, influence, player_level, player_networth, player_fame_level, player_fame, avatar_filename FROM players WHERE id = ?");
	$query->execute(array($eos_player_id));
	$player = $query->fetch(PDO::FETCH_ASSOC);
	if(empty($player)){
		echo "Fatal Error: Error code STATS-063. Player not found.";
		exit();
	}else{
		// Initialize Descriptions - total 15
		$player_level_desc = array("Student", "Businessman", "Entrepreneur", "Millionaire", "Manager", "General Manager", "CEO", "Chairman", "Capitalist", "Billionaire", "Industrialist", "Tycoon", "Trillionaire", "Dynast", "Deity");
		// NW limit: 				0 			250k 			500k 			1kk 		3kk	 		10kk 			30kk	100kk		300kk 			1kkk 			10kkk 		100kkk 			1kkkk		10kkkk		100kkkk
		$player_level_size = sizeof($player_level_desc);
		$player_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
		$player_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Acclaimed", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
		// Match Player Stats
		$player_name = $player["player_name"];
		if($player_name == null){
			$player_name = "[New Player]";
		}
		$player_cash = $player["player_cash"];
		$player_influence = $player["influence"];
		$player_level = $player["player_level"];
		$player_networth = $player["player_networth"];
		$player_fame_level = $player["player_fame_level"];
		$player_fame_level_next = $player_fame_level + 1;
		$player_fame_exp = $player["player_fame"];
		$player_fame_level_next_exp = 100 * $player_fame_level_next * $player_fame_level_next * $player_fame_level_next;
		$player_avatar_filename = $player["avatar_filename"];
		if(!$player_avatar_filename){
			$player_avatar_filename = "no-avatar.jpg";
		}
		if($player_fame_exp >= $player_fame_level_next_exp){
			while($player_fame_exp >= $player_fame_level_next_exp){
				$player_fame_level = $player_fame_level_next;
				$player_fame_level_next += 1;
				$player_fame_exp -= $player_fame_level_next_exp;
				$player_fame_level_next_exp = 100 * $player_fame_level_next * $player_fame_level_next * $player_fame_level_next;
			}
			$query = $db->prepare("UPDATE players SET player_fame_level = ?, player_fame = ? WHERE id = ?");
			$query->execute(array($player_fame_level, $player_fame_exp, $eos_player_id));
		}

		$_SESSION['player_name'] = $player_name;
		$_SESSION['player_cash'] = $player_cash;
		$player_cash_display = '<a class="info">$'.number_format_readable($player_cash/100).'<span style="width:240px !important;">Cash: $'.number_format($player_cash/100,2,'.',',').'</span></a>';
		$player_influence_display = '<a class="info">'.number_format_readable($player_influence).'<span style="width:240px !important;">Influence: '.number_format($player_influence,0,'',',').'<br />Influence can be gained from fame level and event rewards, and is used to perform actions that are normally unavailable, such as hurrying construction.</span></a>';
		$player_networth_display = '<a class="info">$'.number_format_readable($player_networth/100).'<span style="width:240px !important;">Networth: $'.number_format($player_networth/100,2,'.',',').'<br />Networth is updated daily around midnight server time.</span></a>';
		if($player_level + 1 < $player_level_size){
			$player_level_display = '<a class="info">'.$player_level_desc[$player_level].'<span style="width:240px !important;">Networth Level: '.$player_level.' ('.$player_level_desc[$player_level].')<br />Next Level: '.($player_level+1).' ('.$player_level_desc[$player_level+1].')<br />At $'.number_format($player_level_lowlimit[$player_level+1]/100, 0, '.', ',').' NW ($'.number_format_readable($player_level_lowlimit[$player_level+1]/100).')</span></a>';
		}else{
			$player_level_display = '<a class="info">'.$player_level_desc[$player_level].'<span style="width:240px !important;">Congratulations!<br />You are at the highest level. Time to take a break?</span></a>';
		}
		$player_fame_display = '<a class="info">'.$player_fame_desc[min(19,floor($player_fame_level/5))].'<span style="width:240px !important;">Fame Level: '.$player_fame_level.' ('.$player_fame_desc[min(19,floor($player_fame_level/5))].')<br />Next Level: '.$player_fame_level_next.' ('.$player_fame_desc[min(19,floor($player_fame_level_next/5))].')<br />Current EXP: '.$player_fame_exp.'/'.$player_fame_level_next_exp.'</span></a>';
	}
	$query = $db->prepare("SELECT COUNT(*) FROM messages WHERE recipient = ? AND sender != ? AND !recipient_read");
	$query->execute(array($eos_player_id, $eos_player_id));
	$new_messages_exist = $query->fetchColumn();
	if($new_messages_exist){
		$messages_filename = "menu-messages-new";
		if($new_messages_exist > 1){
			$messages_detail = '<br /><br /><font color="#ff0000">You have new messages!</font>';
		}else{
			$messages_detail = '<br /><br /><font color="#ff0000">You have a new message!</font>';
		}
	}else{
		$messages_filename = "menu-messages";
		$messages_detail = "";
	}
?>
		<noscript><br /><font size="4" color="#ff0000">&nbsp;&nbsp;&nbsp; This site requires javascript to function, please do not disable it.</font><br /><br /></noscript>
		<div id="eos_menu">
			<?php
			if($eos_player_is_new_user){
			?>
				<a class="info" href="tutorial.php"><img src="/eos/images/menu-tutorial.gif" width="64" height="64" alt="Tutorial" /><?php if($settings_show_menu_tooltip){ ?><span><b>Tutorial</b><br /><br />Complete the tutorial to start your own company.</span><?php } ?></a>
			<?php
			}else{
				if(!$settings_narrow_screen){
				?>
					<a class="info" href="index.php"><img src="/eos/images/menu-news.gif" width="64" height="64" alt="News" /><?php if($settings_show_menu_tooltip){ ?><span><b>News</b><br /><br />View current events and company revenue sheet.</span><?php } ?></a>
				<?php
				}
				
				/*
					The following requires being CEO of an active company
					buildings, warehouse, market, quests
				*/
				if($eos_firm_id && !$firm_locked){
				?>
					<?php if($ctrl_bldg_view){ ?><a class="info" href="buildings.php"><img src="/eos/images/menu-buildings.gif" width="64" height="64" alt="Buildings" /><?php if($settings_show_menu_tooltip){ ?><span><b>Buildings</b><br /><br />Build factories, stores, and research facilities.</span><?php } ?></a><?php } ?>
					<?php if($ctrl_wh_view){ ?><a class="info" href="warehouse.php"><img src="/eos/images/menu-warehouse.gif" width="64" height="64" alt="Warehouse" /><?php if($settings_show_menu_tooltip){ ?><span><b>Warehouse</b><br /><br />The warehouse stores products that you currently own.</span><?php } ?></a><?php } ?>
					<?php if($ctrl_b2b_buy){ ?><a class="info" href="market.php"><img src="/eos/images/menu-market.gif" width="64" height="64" alt="B2B Market" /><?php if($settings_show_menu_tooltip){ ?><span><b>B2B Marketplace</b><br /><br />Trade raw materials and products on the domestic B2B market, or participate in import/export with foreign companies.</span><?php } ?></a><?php } ?>
					<a class="info" href="quests.php"><img src="/eos/images/menu-quests.gif" width="64" height="64" alt="Quests" /><?php if($settings_show_menu_tooltip){ ?><span><b>Quests</b><br /><br />Obtain new quests or check completion status on current quests.</span><?php } ?></a>
				<?php
				}

				/*
					Always present
					stock_market, home/collections, messages, rankings, pedia

				*/
				?>
					<span class="no_select">&nbsp;&nbsp;</span>
					<a class="info" href="stock.php"><img src="/eos/images/menu-stock.gif" width="64" height="64" alt="Stock Market" /><?php if($settings_show_menu_tooltip){ ?><span><b>Stock Market</b><br /><br />Trade shares of other companies to make a profit (or a loss).</span><?php } ?></a>
					<a class="info" href="city.php"><img src="/eos/images/menu-city.gif" width="64" height="64" alt="City" /><?php if($settings_show_menu_tooltip){ ?><span><b>City</b><br /><br />A place of opportunities.</span><?php } ?></a>
					<a class="info" href="messages.php"><img src="/eos/images/<?= $messages_filename ?>.gif" width="64" height="64" alt="Messages" /><?php if($settings_show_menu_tooltip){ ?><span><b>Messages</b><br /><br />Check new messages or send messages to other players.<?= $messages_detail ?></span><?php } ?></a>
				<div style="float:right;">
					<a class="info" href="rankings.php"><img src="/eos/images/menu-rankings.gif" width="64" height="64" alt="Rankings" /><?php if($settings_show_menu_tooltip){ ?><span><b>Rankings</b><br /><br />See how your company performs, and understand your competitors.</span><?php } ?></a>
					<a class="info" href="pedia.php"><img src="/eos/images/menu-pedia.gif" width="64" height="64" alt="EOS-Pedia" /><?php if($settings_show_menu_tooltip){ ?><span><b>EOS-Pedia</b><br /><br />Find out where you can produce, sell, and research each product.</span><?php } ?></a>
				</div>
			<?php
			}
			?>
		</div>
		<div id="eos_wrapper">
			<div id="eos_main">
				<div id="eos_stats_panel_wrapper">
					<div id="eos_stats_panel">
						<div id="eos_stats_panel_avatar">
							<img src="/eos/images/players/<?= $player_avatar_filename ?>" width="120" class="img_b3px" /><br /><br />
						</div>
		<?php
			if($settings_narrow_screen){
				echo '<span style="float:right;text-align:right;margin-right:10px;height:32px;">
					<a href="/eos/"><img src="/eos/images/newspaper.png" title="News" width="24" height="24" /></a> &nbsp;&nbsp; 
					<a href="messages.php?action=view_contacts"><img src="/eos/images/mail_contacts.png" title="Contacts" width="24" height="24" /></a> &nbsp;&nbsp;
					<a href="http://www.example.com/forum/"><img src="/images/forum.png" title="Forum" /></a> &nbsp;&nbsp;
					<a href="settings.php"><img src="/images/settings.png" title="Settings" /></a>';
				if(!isset($_SESSION["user_is_fb_user"])){
					echo ' &nbsp;&nbsp; <a href="/logout.php"><img src="/images/logout.png" title="Logout" /></a>';
				}				
				echo '</span>';
				if($settings_enable_chat){
					echo '<div id="chatbox_ctrl_ns" class="hidden"><a class="show_chat_control"><img src="/eos/images/chat.png" title="Chat" /></a></div>';
				}
			}
			
			echo '
				<div id="eos_stats_panel_player">
					<div style="margin-bottom:4px;"><a href="/eos/player/'.($eos_player_alias ? urlencode($eos_player_alias) : $eos_player_id).'">'.$_SESSION['player_name'].'</a></div>
					<div id="spd_control_content"'.($eos_firm_id ? '' : ' class="displayed"' ).'>
						<div class="spd_item"><img src="/eos/images/money.gif" title="Cash" /> <span id="player_cash">'.$player_cash_display.'</span></div>
						<div class="spd_item"><img src="/eos/images/networth.gif" title="Networth" /> '.$player_networth_display.'</div>
						<div class="spd_item"><img src="/eos/images/star.gif" title="Level" /> '.$player_level_display.'</div>
						<div class="spd_item"><img src="/eos/images/fame.gif" title="Fame" /> '.$player_fame_display.'</div>
						<div class="spd_item"><img src="/eos/images/influence.gif" title="Influence" /> '.$player_influence_display.'</div>
					</div>
				</div>
';
			
			if($eos_firm_id){
				$query = $db->prepare("SELECT shares_os FROM firm_stock WHERE fid = ?");
				$query->execute(array($eos_firm_id));
				$shares_os = $query->fetchColumn();
				if($shares_os){
					$eos_firm_is_public = 1;
					$query = $db->prepare("SELECT shares FROM player_stock WHERE pid = ? AND fid = ?");
					$query->execute(array($eos_player_id, $eos_firm_id));
					$shares_player = $query->fetchColumn();
					if($shares_player){
						$eos_player_stock_percent = 100*$shares_player/$shares_os;
					}else{
						$eos_player_stock_percent = 0;
					}
				}else{
					$eos_firm_is_public = 0;
					$eos_player_stock_percent = 0;
				}
				
				echo '<div id="eos_stats_panel_firm">';
				if($settings_narrow_screen){
					if(count($EOS_PLAYER_FIRMS) > 1){
						echo '<div style="margin-bottom:6px;">';
						echo '<form style="display:inline;vertical-align: middle;" action="/eos/settings-f-switch-start.php" method="POST">';
						echo '<select style="padding: 3px;margin-right: 10px;" id="new_active_firm" name="new_active_firm" onchange="this.form.submit();">';
							foreach($EOS_PLAYER_FIRMS as $firm){
								if($firm['fid'] == $eos_firm_id){
									echo '<option value="',$firm['fid'],'" selected="selected">',$firm['name'],'</option>';
								}else{
									echo '<option value="',$firm['fid'],'">',$firm['name'],'</option>';
								}
							}
						echo '</select>';
						echo '</form>';
						echo '</div>';
					}else{
						echo '<div style="margin-bottom:6px;"><a href="/eos/firm/'.$eos_firm_id.'">'.$_SESSION['firm_name'].'</a></div>';
					}
				}else{
					echo '<div style="margin-bottom:4px;"><a href="/eos/firm/'.$eos_firm_id.'">'.$_SESSION['firm_name'].'</a></div>';
				}
				echo '
				<div class="spd_item"><img src="/eos/images/money.gif" title="Cash" /> <span id="firm_cash">'.$firm_cash_display.'</span></div>
				<div class="spd_item"><img src="/eos/images/loan.png" title="Loan" /> <span id="firm_loan">'.$firm_loan_display.'</span></div>
				<div class="spd_item"><img src="/eos/images/networth.gif" title="Networth" /> <span id="firm_networth">'.$firm_networth_display.'</span></div>
				<div class="spd_item"><img src="/eos/images/star.gif" title="Level" /> <span id="firm_level">'.$firm_level_display.'</span></div>
				<div class="spd_item"><img src="/eos/images/fame.gif" title="Fame" /> <span id="firm_fame_level">'.$firm_fame_display.'</span></div>
				';
				if($eos_firm_is_public){
					echo '<div class="spd_item"><img src="/eos/images/fist.gif" title="Control" /> <span id="firm_control"><a class="info">'.number_format($eos_player_stock_percent,2,'.',',').'%<span>This is the % of company stock that you currently own.</span></a></span></div>';
				}
				echo '</div>';
			}
			if(!$eos_player_is_new_user){
				echo '<div class="hide_in_ns" style="margin-top: 24px;"><a href="messages.php?action=view_contacts"><img src="/eos/images/mail_contacts.png" title="Contacts" /></a></div>';
			}
			if($settings_enable_chat){
				echo '<div id="chatbox_ctrl_norm" class="hide_in_ns hidden" style="margin-top: 24px;"><a class="show_chat_control"><img src="/eos/images/chat.png" title="Chat" /></a></div>';
			}
		?>
					</div>
				</div>
				<div id="eos_body">