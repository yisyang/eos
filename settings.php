<?php require 'include/prehtml.php'; ?>
<?php require 'include/stock_control.php'; ?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Settings</title>
<?php require 'include/head.php'; ?>
<?php require 'include/stats.php'; ?>
		<?php
			if(!$settings_narrow_screen){
				echo '<img src="/eos/images/title-settings.jpg" style="padding-bottom: 10px;" /><br />';
			}
		?>
		<div id="eos_narrow_screen_padding">
			<?php
				function parseChoices($choices){
					$text = '';
					if(count($choices)){
						$text .= '<table class="default_table no_border align_left"><tbody>';
						foreach($choices as $choice){
							if(isset($choice['id'])){
								$text .= '<tr id="'.$choice['id'].'"><td>';
							}else{
								$text .= '<tr><td>';
							}
							if(isset($choice['link'])){
								$text .= '<a class="jqDialog" href="'.$choice['link'].'"><input type="button" class="bigger_input" value="'.$choice['title'].'" /></a>';
							}else if(isset($choice['choices'])){
								$text .= '<label>'.$choice['title'].': </label>';
								$text .= '<select class="bigger_input" onchange="'.$choice['action'].'">';
								if(!isset($choice['selected'])) $choice['selected'] = '';
								foreach($choice['choices'] as $opt => $optval){
									$text .= '<option value="'.$optval.'"'.($choice['selected'] == $optval ? ' selected="selected"' : '').'>'.$opt.'</option>';
								}
								$text .= '</select>';
							}else if(isset($choice['action'])){
								$text .= '<input type="button" class="bigger_input" value="'.$choice['title'].'" onclick="'.$choice['action'].'" />';
							}
							$text .= '</td><td>';
							$text .= $choice['desc'];
							$text .= '</td></tr>';
						}
						$text .= '</tbody></table>';
					}else{
						$text .= 'None available.';
					}
					return $text;
				}

				/*
					Company actions now in the city
					
					Buy & sell stocks
						Price change
							Buy: New price = price * (1+0.1*x%), money gained = average of old and new, commission $10.00 + 0.5%
							Sell: New price = price * 1/(1+0.1*x%), and no less than 1 cent, money gained = average of old and new
					(C) Fund Company - Invest $ from personal account into company account, does not affect stocks in any way
								Can only be done before IPO
					(C) Initiate IPO - Start having stocks, raise x% (25%/50%/100%/150%/200%) of networth value in stocks at $1-10 per stock, dilutes personal control to 100%/(100%+x%)
								Can only be done before IPO, requires level 6, $1,000,000.00 cash cost
					Issue More Stocks - Issue x% more stocks at (100% - x/2%) of current price, gain equal amount in cash, lowers existing stock price to (100% - x/2%)
								As CEO: min 5%, max 100%, orig req 67%+ stock
								As majority share holder: min 5%, max 100%, must personally retain 10%+ of new stocks
								Can be done every 7 days, shared timer with buy back stocks, use special purchase-able item to reduce cooldown by 3 days, ultra-special non purc item to reduce by 7 days
								Requires 67%+ stock
					Buy Back Stocks - Remove x% of stocks, acquire them from public, increase current stock price to 5^(x%/100%) * 100% of current price (max 1000%), pay for 0.6213*new price*num_shares
								Example:	original price $100, 10000 shares, remove 60% -> 4000 shares left, new price $262.65 (rounded), paid $979,117.03 for original worth $600,000
								Example 2:	original price $100, 10000 shares, remove 40% -> 6000 shares left, new price $190.37 (rounded), paid $473,096.08, remove another 33.3% -> 4000 left, new price $325.52 (rounded), paid $404,491.46, much higher price increase, significantly less paid
								Can be done every 7 days, shared timer with issue stocks, use special purchase-able item to reduce cooldown by 3 days
								Requires 50%+ stock
					(C) Set Dividend - Sets the dividend that shareholders receive from company profit.
								Requires 67%+ stock
					(C) Set Executive Pay - As % of company profit before paying dividend.
								Requires 67%+ stock
					(C) Change Company Name - Change company name.
								Requires 50%+ stock or MSH
					(C) Order Appraisal - (Re-estimate networth, pay $10,000.00 * level^2 in cash as appraisal fee)
								Requires 10%+ stock or MSH, CEO
					(C) Sell Company - (5% commissions and fees deducted, no appraisal included)
								Only available with active firm before IPO, cannot be done more than once on the same day (except for the first time, probably using special item given by tutorial)
					Resign - After IPO, keep stocks but quit job as CEO
				*/
			?>
			<h3>Player Options and Personalization</h3>
			<?php
				/*
					(C) Change Avatar - Upload avatar or use fb avatar
						- Upload button
						- validate user supplied filename (replace all chars except [a-z0-9] with underscore)
						- validate file suffix (allow only image suffixes - jpg, jpeg, gif, png)
						- open and re-save image in the images/players directory
						- for fb avatar, open from https://graph.facebook.com/USER_ID/picture?type=large and resize to 120px
					(C* - depends on inven sys) Change Player Name - similar to method for choosing new company name
						May use special name change item to speedup cooldown
					(C) Change Profile Alias
					(C) Change Player Description
				*/

				$choices = array();

				// Populate choices
				if($settings_show_menu_tooltip){
					$new_status_text = "OFF";
				}else{
					$new_status_text = "ON";
				}
				$choices[] = array('title' => 'Change Avatar', 'desc' => 'Change your profile avatar or use facebook avatar.', 'link' => 'settings-p-avatar.php');
				$choices[] = array('title' => 'Change Player Name', 'desc' => 'Change your player\'s displayed name (note your player ID does not change, so this may not prevent others from finding you).', 'link' => 'settings-p-rename.php');
				$choices[] = array('title' => 'Change Profile Alias', 'desc' => 'Change your profile alias that links to your player\'s profile: <br />(e.g. http://www.'.$_SERVER['HTTP_HOST'].'.com/eos/player/'.($eos_player_alias ? $eos_player_alias : 'alias').').', 'link' => 'settings-p-alias.php');
				$choices[] = array('title' => 'Change Profile Description', 'desc' => 'Write a little bit about your virtual self.', 'link' => 'settings-p-desc.php');
				$choices[] = array('title' => 'Vacation Mode', 'desc' => 'Lock down one or more of your companies and put all workers on unpaid vacation.', 'link' => 'settings-p-vacation.php');
				$choices[] = array('title' => 'Restart Account', 'desc' => 'Escape from your responsibilities by traveling way back in time.', 'link' => 'settings-p-restart.php');

				// Output choices
				echo parseChoices($choices);
			?>
			<br />
			<h3>Miscellaneous (Following settings will take effect on page change)</h3>
			<?php
				$choices = array();

				// Populate choices
				$choices[] = array('id' => 'st_b2b_rows_to_display', 'title' => 'Rows Per Page', 'choices' => array('8' => 8, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100), 'selected' => $settings_b2b_rows_per_page, 'desc' => 'Set the number of rows to display per page on the warehouse and B2B screens.', 'action' => 'settingsController.updateB2BRowsPerPage(this.value, \'st_b2b_rows_to_display\');');
				if($settings_show_menu_tooltip){
					$new_status_text = "OFF";
				}else{
					$new_status_text = "ON";
				}
				$choices[] = array('id' => 'st_menu_tooltip', 'title' => 'Turn '.$new_status_text.' Menu Tooltip', 'desc' => 'This is the tooltip given for each of the main menu buttons (Buildings, Warehouse, B2B, etc.). It appears when you hover over (or for some mobile users, tap on) the menu buttons.', 'action' => 'settingsController.toggleOnOff(\'menu_tooltip\', \'st_menu_tooltip\');');
				if($settings_narrow_screen){
					$new_status_text = "OFF";
				}else{
					$new_status_text = "ON";
				}
				$choices[] = array('id' => 'st_narrow_screen', 'title' => 'Turn '.$new_status_text.' Minimal Interface', 'desc' => 'The minimal interface is narrower than the regular interface, making it ideal for those with smaller screens.', 'action' => 'settingsController.toggleOnOff(\'narrow_screen\', \'st_narrow_screen\');');
				if($settings_queue_countdown){
					$new_status_text = "OFF";
				}else{
					$new_status_text = "ON";
				}
				$choices[] = array('id' => 'st_queue_countdown', 'title' => 'Turn '.$new_status_text.' Queue Countdown', 'desc' => 'The countdown timer in production and research queues provide extra information at the cost of performance.', 'action' => 'settingsController.toggleOnOff(\'queue_countdown\', \'st_queue_countdown\');');
				if($settings_enable_chat){
					$new_status_text = "OFF";
				}else{
					$new_status_text = "ON";
				}
				$choices[] = array('id' => 'st_enable_chat', 'title' => 'Turn '.$new_status_text.' Chat', 'desc' => 'The chat screen is only visible on the buildings interface.', 'action' => 'settingsController.toggleOnOff(\'enable_chat\', \'st_enable_chat\');');
					
				// Output choices
				echo parseChoices($choices);
			?>
		</div>
<?php require 'include/foot.php'; ?>