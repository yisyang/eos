<?php require 'include/config_newplayer.php'; ?>
<?php require 'include/prehtml.php'; ?>
<?php
if(!isset($_POST['action'])){
	$resp = array('success' => 0, 'msg' => 'Action missing.');
	echo json_encode($resp);
	exit();
}
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

if($action == 'show_content'){
	if($eos_player_is_new_user == 1){
	?>
		<div id="eos_narrow_screen_padding">
			<script type="text/javascript">
				var searchTimeout, lastSearch;
				function nameCheckInit(skipTimeout){
					jQuery("#p_name_submit").prop("disabled", true);
					clearTimeout(searchTimeout);
					if(typeof(skipTimeout) !== "undefined" && skipTimeout){
						nameCheck();
					}else{
						searchTimeout = setTimeout("nameCheck();", 1000);
					}
				}
				function nameCheck(){
					var search = document.getElementById("new_player_name").value;
					clearTimeout(searchTimeout);
					if(search !== lastSearch){
						lastSearch = search;
						tutorialController.checkPlayerName();
					}
				}
			</script>
			<div style="font-family:Palatino,Georgia;font-size:16px;line-height:150%;">
				<h3>Welcome to the new Economies of Scale!</h3><br />
				<form id="tutorial_form" onsubmit="tutorialController.submitPlayerName();return false;">
					You are 
					<input type="text" class="bigger_input" id="new_player_name" size="26" maxlength="24" value="" placeholder="Full Name" onKeyUp="nameCheckInit();" onChange="nameCheck();" />
					<span id="name_check_response" style="display:inline;padding-left:5px;font-size:12px;">&nbsp;</span><br /><br />
					It's summer vacation from your Freshman college year, and your uncle Johnny Appleseed invited you to help him manage his fruit plantation.<br /><br />
					Why not? You think to yourself.<br />
					It will be fun, boost your resume, and you will be giving orders instead of receiving them.<br /><br />
					<input id="p_name_submit" type="submit" class="bigger_input" value="Continue" disabled="disabled" />
				</form>
			</div>
		</div>
	<?php
	}else if($eos_player_is_new_user == 2){
	?>
		<div style="position:relative;top:0;left:0;width:780px;height:650px;background-color:#5fca46;">
		<?php
			// Initialize pre-generated terrain map, $max_buildings hard-coded as defined by map layout
			$max_buildings = 32;

			// Add map background(s)
			echo '<img class="no_select" style="position:absolute;left:0;top:0;z-index:0;" src="images/city/city_map_tutorial.jpg" width="780" height="650" />';
		?>
		<?php
			// Add placeholders for buildings

			$position_x = array(0, 315, 375, 435, 555, 615, 271, 331, 391, 511, 571, 226, 286, 346, 466, 526, 181, 241, 301, 421, 481, 392, 512, 300, 360, 480, 540, 226, 286, 406, 466, 254, 374);
			$position_y = array(0, 75, 91, 108, 141, 157, 99, 115, 132, 165, 181, 123, 139, 156, 189, 205, 147, 163, 180, 213, 229, 278, 311, 278, 294, 327, 343, 318, 334, 367, 383, 350, 383);
			$position_z = array(0, 328, 329, 330, 333, 334, 372, 373, 374, 377, 378, 424, 425, 426, 428, 429, 471, 472, 473, 476, 477, 611, 613, 649, 650, 653, 654, 739, 740, 742, 743, 779, 781);
			
			for($j=1;$j<=$max_buildings;$j++){
				echo '<div id="building_image_'.$j.'" style="position:absolute;left:'.$position_x[$j].'px;top:'.$position_y[$j].'px;z-index:'.$position_z[$j].';"></div>';
				echo '<div id="building_icon_'.$j.'" class="no_select" style="position:absolute;left:'.($position_x[$j]+24).'px;top:'.($position_y[$j]+3).'px;z-index:'.($position_z[$j]+200).';width:40px;height:40px;"><span id="cd_icon_back_'.$j.'" class="anim_placeholder"></span><span id="cd_icon_'.$j.'" class="anim_placeholder" style="z-index:'.($position_z[$j]+202).';"></span></div>';
			}
			
			echo '<img class="no_select" style="position:absolute;left:0;top:0;z-index:9001;" src="images/transparent.gif" width="760" height="650" usemap="#bldg_imap" />';
		?>
		<map id="bldg_imap" name="bldg_imap">
		<?php
			$poly_cords_offset = array(0,24,30,8,90,24,60,40,0,24);
			
			$j = 1;
			 echo '<area id="cd_icon_title_',$j,'" href="#" onclick="tutorialController.showFboxContent(\'production_1\');return false;" alt="" shape="poly" coords="',$position_x[$j]+$poly_cords_offset[0],',',$position_y[$j]+$poly_cords_offset[1],',',$position_x[$j]+$poly_cords_offset[2],',',$position_y[$j]+$poly_cords_offset[3],',',$position_x[$j]+$poly_cords_offset[4],',',$position_y[$j]+$poly_cords_offset[5],',',$position_x[$j]+$poly_cords_offset[6],',',$position_y[$j]+$poly_cords_offset[7],',',$position_x[$j]+$poly_cords_offset[8],',',$position_y[$j]+$poly_cords_offset[9],'" />';

			$j = 11;
			 echo '<area id="cd_icon_title_',$j,'" href="#" onclick="return false;" alt="" shape="poly" coords="',$position_x[$j]+$poly_cords_offset[0],',',$position_y[$j]+$poly_cords_offset[1],',',$position_x[$j]+$poly_cords_offset[2],',',$position_y[$j]+$poly_cords_offset[3],',',$position_x[$j]+$poly_cords_offset[4],',',$position_y[$j]+$poly_cords_offset[5],',',$position_x[$j]+$poly_cords_offset[6],',',$position_y[$j]+$poly_cords_offset[7],',',$position_x[$j]+$poly_cords_offset[8],',',$position_y[$j]+$poly_cords_offset[9],'" />';
		?>
		</map>
		<div class="tbox tbox_ra" style="left:85px;top:70px;">Welcome to the buildings interface, this is a <b>fruit plantation</b>.</div>
		<div class="tbox tbox_ra" style="left:130px;top:155px;">Lots for sale are marked with a trade icon, and can be purchased to become a usable lot. </div>
		<div class="tbox tbox_la" style="left:470px;top:130px;">Vacant lots are marked with a hammer icon.<br />New buildings may be built here, initially at 10 m&#178;, but can be expanded once built.</div>
		
		<div class="tbox" style="left:250px;top:300px;">When you are ready, please continue by clicking on the fruit plantation.</div>
		<div id="tipbox"></div>
	</div>
	<?php
	}else if($eos_player_is_new_user == 3){
	?>
		<div id="eos_body">
			<div id="eos_narrow_screen_padding">
				<div class="default_submenu" id="wh_submenu">
					<a class="submenu"><img width="36" height="36" title="Warehouse (New Products)" alt="[WH New]" src="/eos/images/wh_new.gif"></a> 
					<a class="submenu active"><img width="36" height="36" title="Warehouse (Alphabetical)" alt="[WH A-Z]" src="/eos/images/wh_az.gif"></a> 
					<a class="submenu"><img width="36" height="36" title="Warehouse (Filter by Factory)" alt="[WH by Factory]" src="/eos/images/wh_fact.gif"></a> 
					<a class="submenu"><img width="36" height="36" title="Warehouse (Filter by Store)" alt="[WH by Store]" src="/eos/images/wh_store.gif"></a> 
					<a class="submenu"><img width="36" height="36" title="Warehouse (Filter by Category)" alt="[WH by Cat]" src="/eos/images/wh_cat.gif"></a>
				</div>
				<div class="type_id_choices" id="type_id_choices" style="display: none;"></div>
				<table class="default_table compact" id="wh_table">
					<thead>
						<tr><td colspan="2">Product</td><td>Quality</td><td>Quantity</td><td>Cost</td><td>Sale Quantity</td><td>Unit Price</td><td style="width:113px;">Actions</td></tr>
					</thead>
					<tbody>
						<tr wh_id="123456" class="wh_tr" id="wh_display_123456">
							<td style="border-right: none;"><a onclick="jAlert('Disabled in tutorial.');"><img src="/eos/images/prod/apple.gif"></a></td><td class="warehouse_prod_name">Apple</td><td>0.00</td><td>5,000,000,000</td><td>$0.20</td><td><input type="text" maxlength="14" size="8" id="sell_quantity_123456"></td><td><input type="text" maxlength="9" size="8" id="sell_price_123456"></td><td><a onclick="tutorialController.sellToMarket();"><img title="Sell on Market" src="/eos/images/button-sell-small.gif"></a> <a onclick="jAlert('Disabled in tutorial.');"><img title="View on Market" src="/eos/images/button-magnifier-small.gif"></a> <a onclick="jAlert('Disabled in tutorial.');"><img title="Discard" src="/eos/images/button-trash-small.gif"></a></td>
						</tr>
						<tr wh_id="123457" class="wh_tr" id="wh_display_123457">
							<td style="border-right: none;"><a onclick="jAlert('Disabled in tutorial.');"><img src="/eos/images/prod/electricity.gif"></a></td><td class="warehouse_prod_name">Electricity</td><td>0.00</td><td>367,244,221</td><td>$0.08</td><td><input type="text" maxlength="14" size="8" id="sell_quantity_123457" disabled="disabled"></td><td><input type="text" maxlength="9" size="8" id="sell_price_123457" disabled="disabled"></td><td><a onclick="jAlert('Disabled in tutorial.');"><img title="Sell on Market" src="/eos/images/button-sell-small.gif"></a> <a onclick="jAlert('Disabled in tutorial.');"><img title="View on Market" src="/eos/images/button-magnifier-small.gif"></a> <a onclick="jAlert('Disabled in tutorial.');"><img title="Discard" src="/eos/images/button-trash-small.gif"></a></td></tr>
						<tr wh_id="123458" class="wh_tr" id="wh_display_123458">
							<td style="border-right: none;"><a onclick="jAlert('Disabled in tutorial.');"><img src="/eos/images/prod/water.gif"></a></td><td class="warehouse_prod_name">Water</td><td>0.00</td><td>981,826,733</td><td>$0.06</td><td><input type="text" maxlength="14" size="8" id="sell_quantity_123458" disabled="disabled"></td><td><input type="text" maxlength="9" size="8" id="sell_price_123458" disabled="disabled"></td><td><a onclick="jAlert('Disabled in tutorial.');"><img title="Sell on Market" src="/eos/images/button-sell-small.gif"></a> <a onclick="jAlert('Disabled in tutorial.');"><img title="View on Market" src="/eos/images/button-magnifier-small.gif"></a> <a onclick="jAlert('Disabled in tutorial.');"><img title="Discard" src="/eos/images/button-trash-small.gif"></a></td>
						</tr>
					</tbody>
				</table>
				<br />
				<div class="tbox_inline" style="width:600px;margin: 0 auto;">
					<h3>Tutorial - Warehouse</h3>
					<div style="font-family:Palatino,Georgia;font-size:16px;line-height:150%;">
						This is the warehouse, products owned by the company are displayed here.<br />
						The list summarizes their average quality, total quantity, production cost, and what you can do with them. The actions are (from left to right):
						<ul>
							<li>Sell on Market - Allows you to sell a product over the B2B Market at your listing price (Unit Price).</li>
							<li>View on Market - Allows you to visit the B2B Market to look up competitor prices.</li>
							<li>Discard - Remove the item from your warehouse to free up storage space.</li>
						</ul>
						Your uncle's company sure has a lot of apples!<br /><br />
						Why don't you put 50,000 of them on the market at $2 each?<br />
						(Enter 50000 in the Sale Quantity Field, and 2 in the Unit Price field, then click on the Sell on Market button ($))
						<br />
						<br />
						<input type="button" class="bigger_input" value="Skip This Section" onclick="tutorialController.updateProgress(4);" />
					</div>
				</div>
			</div>
		</div>
	<?php
	}else if($eos_player_is_new_user == 4){
	?>
		<div style="position:relative;top:0;left:0;width:780px;height:650px;background-color:#5fca46;">
		<?php
			// Initialize pre-generated terrain map, $max_buildings hard-coded as defined by map layout
			$max_buildings = 32;

			// Add map background(s)
			echo '<img class="no_select" style="position:absolute;left:0;top:0;z-index:0;" src="images/city/city_map_tutorial.jpg" width="780" height="650" />';
		?>
		<?php
			// Add placeholders for buildings

			$position_x = array(0, 315, 375, 435, 555, 615, 271, 331, 391, 511, 571, 226, 286, 346, 466, 526, 181, 241, 301, 421, 481, 392, 512, 300, 360, 480, 540, 226, 286, 406, 466, 254, 374);
			$position_y = array(0, 75, 91, 108, 141, 157, 99, 115, 132, 165, 181, 123, 139, 156, 189, 205, 147, 163, 180, 213, 229, 278, 311, 278, 294, 327, 343, 318, 334, 367, 383, 350, 383);
			$position_z = array(0, 328, 329, 330, 333, 334, 372, 373, 374, 377, 378, 424, 425, 426, 428, 429, 471, 472, 473, 476, 477, 611, 613, 649, 650, 653, 654, 739, 740, 742, 743, 779, 781);
			
			for($j=1;$j<=$max_buildings;$j++){
				echo '<div id="building_image_'.$j.'" style="position:absolute;left:'.$position_x[$j].'px;top:'.$position_y[$j].'px;z-index:'.$position_z[$j].';"></div>';
				echo '<div id="building_icon_'.$j.'" class="no_select" style="position:absolute;left:'.($position_x[$j]+24).'px;top:'.($position_y[$j]+3).'px;z-index:'.($position_z[$j]+200).';width:40px;height:40px;"><span id="cd_icon_back_'.$j.'" class="anim_placeholder"></span><span id="cd_icon_'.$j.'" class="anim_placeholder" style="z-index:'.($position_z[$j]+202).';"></span></div>';
			}
			
			echo '<img class="no_select" style="position:absolute;left:0;top:0;z-index:9001;" src="images/transparent.gif" width="760" height="650" usemap="#bldg_imap" />';
		?>
		<map id="bldg_imap" name="bldg_imap">
		<?php
			$poly_cords_offset = array(0,24,30,8,90,24,60,40,0,24);
			
			$j = 1;
			 echo '<area id="cd_icon_title_',$j,'" href="#" onclick="return false;" alt="" shape="poly" coords="',$position_x[$j]+$poly_cords_offset[0],',',$position_y[$j]+$poly_cords_offset[1],',',$position_x[$j]+$poly_cords_offset[2],',',$position_y[$j]+$poly_cords_offset[3],',',$position_x[$j]+$poly_cords_offset[4],',',$position_y[$j]+$poly_cords_offset[5],',',$position_x[$j]+$poly_cords_offset[6],',',$position_y[$j]+$poly_cords_offset[7],',',$position_x[$j]+$poly_cords_offset[8],',',$position_y[$j]+$poly_cords_offset[9],'" />';

			$j = 11;
			 echo '<area id="cd_icon_title_',$j,'" href="#" onclick="tutorialController.showFboxContent(\'store_1\');return false;" alt="" shape="poly" coords="',$position_x[$j]+$poly_cords_offset[0],',',$position_y[$j]+$poly_cords_offset[1],',',$position_x[$j]+$poly_cords_offset[2],',',$position_y[$j]+$poly_cords_offset[3],',',$position_x[$j]+$poly_cords_offset[4],',',$position_y[$j]+$poly_cords_offset[5],',',$position_x[$j]+$poly_cords_offset[6],',',$position_y[$j]+$poly_cords_offset[7],',',$position_x[$j]+$poly_cords_offset[8],',',$position_y[$j]+$poly_cords_offset[9],'" />';
		?>
		</map>
		<div class="tbox tbox_la" style="left:330px;top:120px;">Now that you learned to sell your goods on the B2B market, let me show you another way.<br />
		Click on the farmers market.</div>
		<div id="tipbox"></div>
	</div>
	<?php
	}else if($eos_player_is_new_user == 5){
	?>
		<div id="eos_narrow_screen_padding">
			<script type="text/javascript">
				var searchTimeout, lastSearch;
				function nameCheckInit(skipTimeout){
					jQuery("#p_name_submit").prop("disabled", true);
					clearTimeout(searchTimeout);
					if(typeof(skipTimeout) !== "undefined" && skipTimeout){
						nameCheck();
					}else{
						searchTimeout = setTimeout("nameCheck();", 1000);
					}
				}
				function nameCheck(){
					var search = document.getElementById("new_player_name").value;
					clearTimeout(searchTimeout);
					if(search !== lastSearch){
						lastSearch = search;
						tutorialController.checkPlayerName();
					}
				}
			</script>
			<div style="font-family:Palatino,Georgia;font-size:16px;line-height:150%;">
				<h3>Tutorial - The End</h3><br />
				<form id="tutorial_form" onsubmit="tutorialController.submitPlayerName();return false;">
					At the end of your vacation with uncle Johnny Appleseed, you feel more ready to have your own business, and what's more, your uncle agrees!<br /><br />
					With him acting as your guarantor, you are able to secure a $9 million loan from the bank. Your family helped amassed another million to make it $10 million, and your friends in college offered to support you with their knowledge.<br /><br />
					
					You have started <b><?= $_SESSION['firm_name'] ?></b> with the follow:<br /><br />
					$10,000,000 in cash (including $9,000,000 in loan)<br />
					<?php
						$sql = "SELECT firm_tech.quality, list_prod.name FROM firm_tech LEFT JOIN list_prod ON firm_tech.pid = list_prod.id WHERE firm_tech.fid = $eos_firm_id";
						$techs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
						
						foreach($techs as $tech){
							echo 'Level '.$tech['quality'].' tech in '.$tech['name'].'<br />';
						}
					?>
					<br />
					<input type="button" class="bigger_input" value="End Tutorial" onclick="tutorialController.updateProgress(99);" />
					<br /><br />
					<div class="tbox_inline">
						<b>One-Time Tips for New Players</b><br /><br />
						It is recommended for new players to choose from one of the following three <b>starting paths</b>:
						<ol>
							<li>Build a store, buy goods from B2B, sell them at a higher price in the store.</li>
							<li>Build a factory, buy raw materials from B2B, produce goods, sell finished goods on B2B.<br />
								(Note the B2B Market charges a 5% seller's commission for all completed sales.)</li>
							<li>Build a factory and a store, produce and sell your own goods.</li>
						</ol>
						Do not spend all your cash on buildings, as you will need some cash on hand for daily operations such as factory production or B2B purchase.<br /><br />
						Should you decide to sell products in stores, be sure to visit the EoS-Pedia to look up <b>% Demand Met</b>. You can turn higher profits when demand exceeds supply.<br /><br />
						Store sales are also affected by product quality, so you may also want to invest in an R&amp;D facility if you produce your own goods.<br /><br />
						<b>As a last resort</b>, you may restart your account from the settings menu (usable every 24 hours).
					</div>
					<br />
					<input type="button" class="bigger_input" value="End Tutorial" onclick="tutorialController.updateProgress(99);" />
				</form>
			</div>
		</div>
	<?php
	}
}
else if($action == 'show_fbox_content'){
	$content_name = filter_var($_POST['content_name'], FILTER_SANITIZE_STRING);
	if($content_name == 'production_1'){
		?>
		<div id="eos_body_fbox">
			<div style="float: left;padding-right: 15px;">
				<img src="/eos/images/fact/fruit_plantation.gif" width="180" height="80" />
			</div>
			<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;">
				<div class="building_name_container"><span class="building_name" id="building_name">Johnny's Apple Plantation (500 m&#178;)</span></div>
				<a id="bldg_expand_button"><img alt="[Expand]" title="Expand Building" src="/eos/images/button-build.gif"></a>
				<a id="bldg_sell_button"><img alt="[Sell]" title="Sell Building" src="/eos/images/button-sell.gif"></a>
			</div>
			<div class="tbox tbox_fbox tbox_la" style="left:340px;top:50px;width:405px !important;">The "Expand Building" button allows you to increase the size of your factories. A larger factory can produce more units of a product in the same amount of time. The "Sell Building" button allows you to magically convert the building into a vacant lot, if you ever plan to move into another industry.</div>
			<div class="tbox tbox_fbox tbox_la" style="left:125px;top:220px;width:280px !important;">The summary panel under each product lists the amount of research you have done for the product, and its production costs in time, money, and raw materials.</div>
			<div class="tbox tbox_fbox tbox_ra" style="left:-140px;top:130px;width:100px !important;">When you are ready, click on the big red apple.</div>
			<div class="prod_choices">
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img onclick="tutorialController.showFboxContent('production_2');" src="/eos/images/prod/large/apple.gif" title="Start Producing Apple" style="margin-bottom:6px;cursor:pointer;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 90<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/banana.gif" title="Start Producing Banana" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/blueberry.gif" title="Start Producing Blueberry" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.28<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.20<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 6.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/cherry.gif" title="Start Producing Cherry" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/coconut.gif" title="Start Producing Coconut" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.56<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.40<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 12<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/grapes.gif" title="Start Producing Grapes" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.28<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.20<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 6.00<br />
					</div>
				</div>
			</div>
			<div class="prod_choices">
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/lemon.gif" title="Start Producing Lemon" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/lime.gif" title="Start Producing Lime" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/mango.gif" title="Start Producing Mango" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.28<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.20<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 6.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/orange.gif" title="Start Producing Orange" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/pineapple.gif" title="Start Producing Pineapple" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.28<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.20<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 6.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img  src="/eos/images/prod/large/strawberry.gif" title="Start Producing Strawberry" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.14<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.10<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 3.00<br />
					</div>
				</div>
			</div>
			<div class="prod_choices">
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/tomato.gif" title="Start Producing Tomato" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $0.21<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 0.20<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 4.00<br />
					</div>
				</div>
				<div class="prod_choices_item">
					<div style="position: relative; left: 0; top: 0;">
						<img src="/eos/images/prod/large/watermelon.gif" title="Start Producing Watermelon" style="margin-bottom:6px;" />
						<br /><img src="/eos/images/star.gif" alt="Tech" title="Tech" /> 0<br /><img src="/eos/images/time.gif" alt="Time" title="Time" /> 00:00:01<br /><img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $1.10<br /><img src="/eos/images/prod/electricity.gif" alt="Electricity" title="Electricity" /> 1.00<br /><img src="/eos/images/prod/water.gif" alt="Water" title="Water" /> 25<br />
					</div>
				</div>
			</div>
			<div style="clear:both;">&nbsp;</div><br />
			<input type="button" value="Restart This Section" class="bigger_input jqDialog-close-btn" />
		</div>
		<?php
	}else if($content_name == 'production_2'){
		?>
		<div id="eos_body_fbox">
			<script type="text/javascript">
				var pnum, pnum_max, unit_cost, pnum_max_base, pnum_max_comp_1, pnum_max_comp_2;
				var opid1_value = 50;
				var opid1_cost = 32.8;
				var pnum_max_ipid1 = 999999999;
				var pnum_max_ipid2 = 999999999;
				var pnum_req_ipid1 = 0.1;
				var pnum_req_ipid2 = 3;
				var pnum_max_cash = 999999999;
				var pnum_max_time = 604800;
				var pnum_max_limit = 999999999;
				var unit_cost_adj = Math.pow(opid1_value, 0.5)/10000;

				pnum_max_base = Math.min(pnum_max_ipid1, pnum_max_ipid2, pnum_max_time, pnum_max_cash);

				//Calculate pnum_max
				pnum_max_comp_1 = pnum_max_base;
				unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_max_base * unit_cost_adj, 0.25);
				
				pnum_max_comp_2 = pnum_max_base / unit_cost;
				var i = 0;
				while(i < 10 && Math.floor(pnum_max_comp_2) > pnum_max_comp_1){
					i++;
					pnum_max_comp_1 = pnum_max_comp_2;
					unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_max_comp_1 * unit_cost_adj, 0.25);
					pnum_max_comp_2 = pnum_max_base / unit_cost;
				}
				pnum_max = Math.min(Math.floor(pnum_max_comp_2), pnum_max_limit);
				

				function pnumAdd1(){
					pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
					if(pnum < pnum_max){
						pnum = pnum + 1;
						document.getElementById('pnum').value = pnum;
						checkPnum();
					}
				}
				function pnumSubtract1(){
					pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
					if(pnum > 0){
						pnum = pnum - 1;
						document.getElementById('pnum').value = pnum;
						checkPnum();
					}
				}
				function pnumMax(){
					pnum = pnum_max;
					document.getElementById('pnum').value = pnum;
					checkPnum();
				}
				function checkPtime(){
					var pTime = Math.min(hms2sec(document.getElementById('ptime').value) + 1, 604800);
					if(pTime > 0){
						var pnum_time_base = pTime / 0.1;
						var pnum_time_comp_1 = pnum_time_base;
						unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_time_base * unit_cost_adj, 0.25);
						var pnum_time_comp_2 = pnum_time_base / unit_cost;
						var i = 0;
						while(i < 10 && Math.floor(pnum_time_comp_2) > pnum_time_comp_1){
							i++;
							pnum_time_comp_1 = pnum_time_comp_2;
							unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum_time_comp_1 * unit_cost_adj, 0.25);
							pnum_time_comp_2 = pnum_time_base / unit_cost;
						}
						document.getElementById('pnum').value = Math.floor(pnum_time_comp_2);
						checkPnum();
					}
				}
				function checkPnum(forced){
					pnum = Math.floor(stripCommas(document.getElementById('pnum').value));
					if(pnum > 0){
						if(pnum > pnum_max){
							pnum = pnum_max;
						}
						var i, e;
						var unit_cost = 0.5 + 0.5 / Math.pow(1 + pnum * unit_cost_adj, 0.25);
						var fpc_cost = 14, fpc_timecost = 0.1, fpc_totalcost = pnum * fpc_cost * unit_cost;
						var fpc_totaltimecost_uf = pnum * unit_cost * fpc_timecost;
						var fpc_totaltimecost = sec2hms(fpc_totaltimecost_uf);

						jQuery('#fpc_total_cost').html('$' + formatNum(fpc_totalcost/100, 2));
						if(pnum + 1 > pnum_max_cash / unit_cost){
							document.getElementById('fpc_total_cost').style.color="#FF0000";
						}else{
							document.getElementById('fpc_total_cost').style.color="#003300";
						}
						jQuery('#fpc_unit_cost').html('$' + formatNum(opid1_cost * unit_cost/100, 2));
						jQuery('#fpc_total_time').html(fpc_totaltimecost);
						var temp_ptime = hms2sec(document.getElementById('ptime').value);
						if(fpc_totaltimecost_uf - temp_ptime > 3 || fpc_totaltimecost_uf - temp_ptime < -3){
							document.getElementById('ptime').value = fpc_totaltimecost;
						}
						if(pnum + 1 > pnum_max_time / unit_cost){
							document.getElementById('fpc_total_time').style.color="#FF0000";
						}else{
							document.getElementById('fpc_total_time').style.color="#003300";
						}
						if(document.getElementById('pnum').value != pnum){
							document.getElementById('pnum').value = pnum;
						}
						jQuery("#slider_target").slider("value", pnum);

						if(pnum_req_ipid1){
							var ipid1n_req = Math.ceil(pnum_req_ipid1 * unit_cost * pnum);
							jQuery('#total_ipid1n').html(formatNum(ipid1n_req));
							if(pnum >= Math.floor(pnum_max_ipid1 / unit_cost)){
								document.getElementById('total_ipid1n').style.color="#FF0000";
							}else{
								document.getElementById('total_ipid1n').style.color="#003300";
							}
						}
						if(pnum_req_ipid2){
							var ipid2n_req = Math.ceil(pnum_req_ipid2 * unit_cost * pnum);
							jQuery('#total_ipid2n').html(formatNum(ipid2n_req));
							if(pnum >= Math.floor(pnum_max_ipid2 / unit_cost)){
								document.getElementById('total_ipid2n').style.color="#FF0000";
							}else{
								document.getElementById('total_ipid2n').style.color="#003300";
							}
						}
					}else{
						if(pnum || forced){
							pnum = 0;
							jQuery("#slider_target").slider("value", pnum);
							jQuery('#fpc_total_cost').html('$0');
							jQuery('#fpc_unit_cost').html('$0');
							jQuery('#fpc_total_time').html('00:00:00');
							document.getElementById('ptime').value = '0';
							document.getElementById('pnum').value = '0';
							if(pnum_req_ipid1){
								jQuery('#total_ipid1n').html('0');
							}
							if(pnum_req_ipid2){
								jQuery('#total_ipid2n').html('0');
							}
						}
					}
				}
			</script>
			<div class="tbox tbox_fbox tbox_la" style="left:230px;top:70px;width:392px !important;">Here are the raw materials that will be used for this production. Once again, there are information under the product icons: quality, quality effect on this production, and quantity in warehouse.</div>
			<div class="tbox tbox_fbox" style="left:240px;top:210px;width:460px !important;">To enter a production quantity, you can either use the slider bar or input units or time.<br />(Optional: Try entering 10, then add 0s to form 100, 1000, and 10000.)</div>
			<div class="tbox tbox_fbox tbox_ra" style="left:340px;top:480px;width:340px !important;">Did you notice how unit cost decreases when you produce more units? This is due to the cost advantage of mass production.<br /><br /><b>When you are done here, click on the button.</b></div>
			<h3 class="vert_middle">Raw Materials for Producing <img title="Apple" alt="Apple" src="/eos/images/prod/apple.gif"></h3>
			<div class="production_confirm_item">
				<img style="margin-bottom:6px;" title="Electricity (Quality 0.00, 0% Q. Dependence): 3067849997 Available (Need Min. 0.10)" src="/eos/images/prod/large/electricity.gif">
				<div class="vert_middle"><img title="Quality" alt="Quality" src="/eos/images/star.gif"> 0.00<br />&nbsp;&nbsp;&nbsp;&nbsp;(0%)</div>
				<div class="vert_middle"><img title="Quantity" alt="Quantity" src="/eos/images/box.png"> 3.06 B</div>
			</div>
			<div class="production_confirm_item">
				<img style="margin-bottom:6px;" title="Water (Quality 0.00, 0% Q. Dependence): 9999999990 Available (Need Min. 3.00)" src="/eos/images/prod/large/water.gif">
				<div class="vert_middle"><img title="Quality" alt="Quality" src="/eos/images/star.gif"> 0.00<br />&nbsp;&nbsp;&nbsp;&nbsp;(0%)</div>
				<div class="vert_middle"><img title="Quantity" alt="Quantity" src="/eos/images/box.png"> 9.99 B</div>
			</div>
			<div class="clearer no_select">&nbsp;</div>
			<form id="slider_form_1" class="default_slider_form" onsubmit="tutorialController.showFboxContent('production_3');return false;">
				<h3 style="vertical-align:middle;">Units or Time to Produce</h3>
				<div style="line-height:48px;" class="vert_middle">
					<div style="float:left;width:60px;"><img class="slider_button_subtract_one" src="images/slider_left.gif" style="cursor:pointer;" onClick="pnumSubtract1();" /></div>
					<div id="slider_target" class="slider_target"></div>
					<div style="float:left;width:60px;"><img class="slider_button_add_one" src="images/slider_right.gif" style="cursor:pointer;" onClick="pnumAdd1();" /></div>
					<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="pnumMax();" /></div>
					<div style="float:left;margin-left:70px;width:200px;line-height:30px;" class="vert_middle">
						Units <input id="pnum" type="text" style="border: 2px solid #997755;text-align:center;" size="8" maxlength="8" onkeyup="checkPnum()" /> (#)<br />
						Time <input id="ptime" type="text" style="border: 2px solid #997755;text-align:center;" size="9" maxlength="9" onkeyup="checkPtime()" /> (hh:mm:ss)
					</div>
					<div class="clearer"></div>
				</div>
				<br />
				<h3>Production Cost</h3>
				<img title="Time" alt="Time" src="/eos/images/time.gif"> <span id="fpc_total_time" style="color: rgb(255, 0, 0);">N/A</span> &nbsp; <img title="Cash" alt="Cash" src="/eos/images/money.gif"> <span id="fpc_total_cost" style="color: rgb(0, 51, 0);">N/A</span> &nbsp; <img title="Electricity" alt="Electricity" src="/eos/images/prod/electricity.gif"> <span id="total_ipid1n" style="color: rgb(0, 51, 0);">N/A</span> &nbsp; <img title="Water" alt="Water" src="/eos/images/prod/water.gif"> <span id="total_ipid2n" style="color: rgb(0, 51, 0);">N/A</span> &nbsp; <br /><br />
				<img onclick="tutorialController.showFboxContent('production_3');" title="Start Production" id="production_start_button" src="images/button-produce-big.gif" style="float:right;cursor:pointer;">
				<br /><h3>Final Quality: 90.00 <a class="info"><img src="images/info.png" style="vertical-align:middle;margin: 0 0 4px 0;"><span>0 (0%) from raw materials, 90 (100%) from production technology.</span></a></h3>
				<h3>Unit Cost: <span id="fpc_unit_cost">$0.18</span> <a class="info"><img src="images/info.png" style="vertical-align:middle;margin: 0 0 4px 0;"><span>Note only raw material costs and direct production costs are included. Salary and building maintenance costs are not included.</span></a></h3>
				<div style="display:none;"><input type="submit" value="submit" /></div>
			</form>
			<script type="text/javascript">
				jQuery("#slider_target").slider({
					value: 0,
					min: 0,
					max: pnum_max,
					slide: function( event, ui ){
						jQuery("#pnum").val(ui.value);
						checkPnum(1);
					}
				});
			</script>
			<br />
			<input type="button" value="Restart This Section" class="bigger_input jqDialog-close-btn" />
		</div>
		<?php
	}else if($content_name == 'production_3'){
		?>
		<div id="eos_body_fbox">	
			<h3>Production Started</h3>
			Bravo! You have just started production on a batch of <img title="Apple" alt="Apple" src="/eos/images/prod/apple.gif" style="vertical-align:middle;">. Simple isn't it?<br /><br />
			<input type="button" onclick="$('#jq-dialog-modal').dialog('close');tutorialController.updateProgress(3);" value="Continue to Next Section" class="bigger_input" />
			<br /><br />
			or<br /><br />
			<input type="button" value="Restart This Section" class="bigger_input jqDialog-close-btn" />
		</div>
		<?php
	}else if($content_name == 'warehouse_1'){
		?>
		<div id="eos_body_fbox">	
			<h3>Product Listed</h3>
			For small manufacturers without their own distribution channels, selling on the B2B is perhaps the easiest way to grow.<br /><br />
			Your company will receive the cash (minus a small commission) when another company purchases your goods, but keep an eye on manufacturing costs and never sell below your cost!<br /><br />
			<input type="button" onclick="$('#jq-dialog-modal').dialog('close');tutorialController.updateProgress(4);" value="Continue to Next Section" class="bigger_input" />
			<br /><br />
			or<br /><br />
			<input type="button" onclick="$('#jq-dialog-modal').dialog('close');tutorialController.showContent();" value="Restart This Section" class="bigger_input" />
		</div>
		<?php
	}else if($content_name == 'store_1'){
		?>
		<div id="eos_body_fbox">
			<div class="tbox tbox_fbox tbox_la" style="left:480px;top:50px;width:320px !important;">Two new buttons here:<br />
			Marketing - Spend on ads to temporarily increase traffic to your store. A sufficiently large store will generate traffic on its own.<br />
			B2B - A shortcut to the store specific page on the B2B, everything on that page can be sold in this store.
			</div>
			<div class="tbox tbox_fbox tbox_la" style="left:260px;top:220px;width:340px !important;">Another list of numbers, just remember whenever you are unsure, hover your mouse over each number to see what they are.</div>
			<div class="tbox tbox_fbox tbox_la" style="left:260px;top:340px;width:340px !important;">For your stores, you can set your own price. Keep in mind shoppers can always spot a good deal, so if you sell quality items at a discounted price, it is possible to run out of inventory.<br /><br />
			Sales are tick-based (every 15 minutes), so your company gets cash every 15 minutes.<br /><br />
			<b>When you are done here, click on the button below.</b><br />
			<input type="button" class="bigger_input" value="Continue" onclick="$('#jq-dialog-modal').dialog('close');tutorialController.updateProgress(5);" />
			</div>

			<div style="float: left;padding-right: 15px;">
				<img width="180" height="80" src="/eos/images/store/farmers_market.gif">
			</div>
			<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;">
				<div class="building_name_container">
					<span id="building_name" class="building_name">Farmers Market (500 m2 <a title="Marketing effect">+0.00%</a>) 
					<a class="info"><img src="images/info.png"><span style="width:300px;line-height:1.5;">Sales are tick-based (Every 15 min. on the 0th, 15th, 30th, and 45th minutes). <br /><br />Simply stock the shelves and set the price, anything sold will be deducted directly from your warehouse.<br /><br />Product prices and sales data are shared between all stores under the company.</span></a>
				</div>
				<a style="cursor:pointer;" id="bldg_expand_button"><img alt="[Expand]" title="Expand Building" src="/eos/images/button-build.gif"></a> &nbsp; 
				<a style="cursor:pointer;" id="bldg_sell_button"><img alt="[Sell]" title="Sell Building" src="/eos/images/button-sell.gif"></a> &nbsp; 
				<a><img alt="[Marketing]" title="Marketing" src="/eos/images/button-marketing.gif"></a> &nbsp; 
				<a><img alt="[B2B]" title="View B2B Products" src="/eos/images/b2b_store.gif"></a> &nbsp; 
			</div>
			<div class="prod_choices">
				<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;"><a class="info"><img style="margin-bottom:6px;" src="/eos/images/prod/large/apple.gif"><span>Apple</span></a><div style="position:absolute;top:0;left:72px;"><a class="info"><img src="images/box.png"><span>Replace Item on Shelf (disabled in tutorial)</span></a></div><div style="position:absolute;top:26px;left:72px;"><a class="info"><img src="images/pedia.png"><span>View on EOS-Pedia (disabled in tutorial)</span></a></div><br /><a style="margin: 0 0 0 10px;font-weight:normal;" class="info vert_middle"><img title="Saleable Quantity" alt="#" src="/eos/images/box.png"><div style="display:inline;color:rgb(0,127,32)"> 148 M</div><span>Quantity: <br />148,875,407<br /><br />Est. Supply: <br />N/A</span></a><br /><a style="margin: 0 0 0 10px;font-weight:normal;" class="info vert_middle"><img alt="#" src="/eos/images/money.gif"> 0.00<span>Revenue (15 min.): <br />$0.00</span></a><br /><a style="margin: 0 0 0 10px;font-weight:normal;" class="info vert_middle"><img alt="#" src="/eos/images/moneyp.gif"> <div style="display:inline;" id="revenue_projected_238514">1.08 k</div><span>Projected (15 min.): <br />$<div style="display:inline;" id="revenue_projected_long_238514">1,085.80</div></span></a><br /><span values="0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0" sparkbarcolor="green" class="sparklines_revenue"><canvas style="display: inline-block; width: 100px; height: 18px; vertical-align: top;" width="100" height="18"></canvas></span><br /><a style="font-weight:normal;" class="info vert_middle">Cost: $1.18<span>Cost: <br />$1.18</span></a><br /><a style="font-weight:normal;cursor:pointer;" class="info vert_middle">MSRP: $2.44<span>Manufacturer¡¯s Suggested Retail Price: <br />$2.44<br /><br />(Click to Use)</span></a><br /><div class="sspi_details"><input type="hidden" maxlength="10" size="10" value="244" style="display:none;" id="sales_price_238514">
				<span style="color:#997755;font-size:18px;font-weight:normal;">$ <input type="text" maxlength="10" value="2.44" style="width:65px;border:2px solid #997755;" id="sales_price_visible_238514" disabled="disabled"></span></div><a style="font-weight:normal;" class="info vert_middle">WASP: $1.34<span>World Average Store Price (15 min.): <br />$1.34</span></a><br /><a style="font-weight:normal;" class="info vert_middle">D. Met: 13.60%<span>Demand Met: <br />13.60%</span></a><br /><a style="font-weight:normal;" class="info vert_middle">MS: 0.00%<span>Market Share (15 min.): <br />0.00%</span></a><br /><div style="line-height:24px;" class="sspi_details" id="set_price_response_238514">&nbsp;</div></div></div>
				<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;"><a class="info"><img style="margin-bottom:6px;" src="/eos/images/prod/large/grapes.gif"><span>Grapes</span></a><div style="position:absolute;top:0;left:72px;"><a class="info"><img src="images/box.png"><span>Replace Item on Shelf (disabled in tutorial)</span></a></div><div style="position:absolute;top:26px;left:72px;"><a class="info"><img src="images/pedia.png"><span>View on EOS-Pedia (disabled in tutorial)</span></a></div><br /><a style="margin: 0 0 0 10px;font-weight:normal;" class="info vert_middle"><img title="Saleable Quantity" alt="#" src="/eos/images/box.png"><div style="display:inline;color:rgb(0,127,32)"> 999 M</div><span>Quantity: <br />999,905,999<br /><br />Est. Supply: <br />N/A</span></a><br /><a style="margin: 0 0 0 10px;font-weight:normal;" class="info vert_middle"><img alt="#" src="/eos/images/money.gif"> 0.00<span>Revenue (15 min.): <br />$0.00</span></a><br /><a style="margin: 0 0 0 10px;font-weight:normal;" class="info vert_middle"><img alt="#" src="/eos/images/moneyp.gif"> <div style="display:inline;" id="revenue_projected_238515">6.58 k</div><span>Projected (15 min.): <br />$<div style="display:inline;" id="revenue_projected_long_238515">6,584.00</div></span></a><br /><span values="0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0" sparkbarcolor="green" class="sparklines_revenue"><canvas style="display: inline-block; width: 100px; height: 18px; vertical-align: top;" width="100" height="18"></canvas></span><br /><a style="font-weight:normal;" class="info vert_middle">Cost: $4.00<span>Cost: <br />$4.00</span></a><br /><a style="font-weight:normal;cursor:pointer;" class="info vert_middle">MSRP: $8.00<span>Manufacturer¡¯s Suggested Retail Price: <br />$8.00<br /><br />(Click to Use)</span></a><br /><div class="sspi_details"><input type="hidden" maxlength="10" size="10" value="800" style="display:none;" id="sales_price_238515">
				<span style="color:#997755;font-size:18px;font-weight:normal;">$ <input type="text" maxlength="10" value="8" style="width:65px;border:2px solid #997755;" id="sales_price_visible_238515" disabled="disabled"></span></div><a style="font-weight:normal;" class="info vert_middle">WASP: $2.38<span>World Average Store Price (15 min.): <br />$2.38</span></a><br /><a style="font-weight:normal;" class="info vert_middle">D. Met: 30.02%<span>Demand Met: <br />30.02%</span></a><br /><a style="font-weight:normal;" class="info vert_middle">MS: 0.00%<span>Market Share (15 min.): <br />0.00%</span></a><br /><div style="line-height:24px;" class="sspi_details" id="set_price_response_238515">&nbsp;</div></div></div>
				<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;"><a class="info"><img style="margin-bottom:6px;" src="/eos/images/empty_shelf.gif"><span>Place Item on Shelf (disabled in tutorial)</span></a><br /></div></div>
				<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;"><a class="info"><img style="margin-bottom:6px;" src="/eos/images/empty_shelf.gif"><span>Place Item on Shelf (disabled in tutorial)</span></a><br /></div></div>
			</div>
			<div style="clear:both;">&nbsp;</div>
			<h3>Efficiency: 70% <a class="info"><img src="images/info.png"><span style="width:300px;line-height:1.5;">Selling power is distributed equally among all products. Empty shelves leads to increased sales on the products that are selling, but also comes with a penalty on sales efficiency.</span></a></h3>
			<div style="clear:both;">&nbsp;</div>
		</div>
		<?php
	}
}
else if($action == 'update_progress'){
	$progress = filter_var($_POST['progress'], FILTER_SANITIZE_NUMBER_INT);
	
	if(!$eos_player_is_new_user){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	if($progress == 5){
		$sql = "SELECT COUNT(*) AS cnt FROM firms_positions WHERE pid = $eos_player_id";
		$count = $db->query($sql)->fetchColumn();
		if(!$count){
			$gen_firm_name = $eos_player_name.' Co.';
			$query = $db->prepare("SELECT COUNT(*) AS cnt FROM firms WHERE name = ?");
			$query->execute(array($gen_firm_name));
			$count = $query->fetchColumn();
			$gen_num = 0;
			while($count > 0){
				$gen_num += 1;
				$gen_firm_name = $eos_player_name.' '.$gen_num.' Co.';
				$query->execute(array($gen_firm_name));
				$count = $query->fetchColumn();
			}

			// Generate firm color
			$fcolor_total_leftover = mt_rand(200, 1000); // Make colors lighter
			$fcolor_r = min($fcolor_total_leftover, mt_rand(0, 255));
			$fcolor_total_leftover = $fcolor_total_leftover - $fcolor_r;
			$fcolor_g = min($fcolor_total_leftover, mt_rand(0, 255));
			$fcolor_total_leftover = $fcolor_total_leftover - $fcolor_g;
			$fcolor_b = min($fcolor_total_leftover, mt_rand(0, 255));
			$fcolor = '#'.str_pad(dechex($fcolor_r), 2, '0', STR_PAD_LEFT).str_pad(dechex($fcolor_g), 2, '0', STR_PAD_LEFT).str_pad(dechex($fcolor_b), 2, '0', STR_PAD_LEFT);

			$query = $db->prepare("INSERT INTO firms (name, color, cash, loan, networth, level, last_login, last_active) VALUES (?, '$fcolor', 1000000000, 900000000, 100000000, 3, NOW(), NOW())");
			$query->execute(array($gen_firm_name));

			$query = $db->prepare("SELECT id FROM firms WHERE name = ?");
			$query->execute(array($gen_firm_name));
			$eos_firm_id = $query->fetchColumn();

			$sql = "INSERT INTO firms_extended (id, is_public, ceo) VALUES ('$eos_firm_id', 0, '$eos_player_id')";
			$db->query($sql);

			$sql = "SELECT id, name, tech_avg FROM list_prod WHERE value > 49 AND value < 2001";
			$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$prods_count = count($prods);

			// Generate 3 bonus techs
			$bonus_techs = array();
			while(count($bonus_techs) < 5){
				$prod_index = mt_rand(0, $prods_count-1);
				$bt_row = array($prods[$prod_index]['id'], $prods[$prod_index]['name'], floor(0.5 * $prods[$prod_index]['tech_avg']));
				if(!in_array($bt_row, $bonus_techs)){
					$bonus_techs[] = $bt_row;
				}
			}
			$timenow = time();
			foreach($bonus_techs as $bonus_tech){
				$sql = "INSERT INTO firm_tech (fid, pid, quality, update_time) VALUES ($eos_firm_id, ".$bonus_tech[0].", ".$bonus_tech[2].", '$timenow')";
				$db->query($sql);
			}

			// Update player
			$sql = "UPDATE players SET fid = $eos_firm_id, player_cash = 0, player_networth = 100000000 WHERE id = $eos_player_id";
			$db->query($sql);

			$sql = "UPDATE players_extended SET voted = 0, voted_streak = 0 WHERE id = $eos_player_id";
			$db->query($sql);

			$sql = "INSERT INTO firms_positions (fid, pid, title, pay_flat, bonus_percent, next_pay_flat, next_bonus_percent, next_accepted, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) VALUES ($eos_firm_id, $eos_player_id, 'Owner', 0, 0, 0, 0, 1, NOW(), '2222-01-01', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";
			$db->query($sql);
			
			$sql = "SELECT id FROM firms_positions WHERE fid = $eos_firm_id AND pid = $eos_player_id ORDER BY id DESC";
			$fp_id = $db->query($sql)->fetchColumn();

			// Insert into logs
			$sql = "INSERT INTO log_management (id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire) 
			SELECT id, fid, pid, title, starttime, endtime, ctrl_admin, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_cancel, ctrl_fact_sell, ctrl_store_ad, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM firms_positions WHERE firms_positions.id = $fp_id";
			$db->query($sql);

			$_SESSION['firm_name'] = $gen_firm_name;
			$_SESSION['firm_cash'] = 1000000000;
		}
	}
	
	if($progress == 99){
		$sql = "UPDATE players SET new_user = 0 WHERE id = $eos_player_id";
		$db->query($sql);

		$resp = array('success' => 1, 'end_tutorial' => 1);
		echo json_encode($resp);
		exit();
	}

	$sql = "UPDATE players SET new_user = ? WHERE id = $eos_player_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($progress));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'check_player_name'){
	$name = $_POST['name'];
	
	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM players WHERE player_name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The player name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$players = $db->query("(SELECT player_name FROM players WHERE id < 100) UNION (SELECT player_name FROM players ORDER BY player_networth DESC LIMIT 0, 200)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($players as $player){
		similar_text($sim_name, strtoupper($player['player_name']), $similarity_pst);
		if ((int) $similarity_pst > 70){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the player\'s name: '.$player['player_name']);
			echo json_encode($resp);
			exit();
		}
	}

	$resp = array('success' => 1, 'msg' => 'This name can be used.');
	echo json_encode($resp);
	exit();
}
else if($action == 'update_player_name'){
	$name = $_POST['name'];
	
	if(!$eos_player_is_new_user){
		$resp = array('success' => 0, 'msg' => 'Not authorized.');
		echo json_encode($resp);
		exit();
	}

	if(strlen($name) > 24 || strlen($name) < 3){
		$resp = array('success' => 0, 'msg' => 'Name must be between 3 and 24 characters.');
		echo json_encode($resp);
		exit();
	}

	$sql = "SELECT COUNT(*) AS cnt FROM players WHERE player_name = ?";
	$query = $db->prepare($sql);
	$query->execute(array($name));
	$count = $query->fetchColumn();

	if($count){
		$resp = array('success' => 0, 'msg' => 'The player name '.$name.' is already in use.');
		echo json_encode($resp);
		exit();
	}

	$players = $db->query("(SELECT player_name FROM players WHERE id < 100) UNION (SELECT player_name FROM players ORDER BY player_networth DESC LIMIT 0, 200)")->fetchAll(PDO::FETCH_ASSOC);

	$sim_name = strtoupper($name);
	foreach($players as $player){
		similar_text($sim_name, strtoupper($player['player_name']), $similarity_pst);
		if ((int) $similarity_pst > 70){
			$resp = array('success' => 0, 'msg' => 'The name you entered is too similar to the player\'s name: '.$player['player_name']);
			echo json_encode($resp);
			exit();
		}
	}

	// Change name
	$sql = "UPDATE players SET player_name = ?, new_user = 2 WHERE id = $eos_player_id";
	$query = $db->prepare($sql);
	$result = $query->execute(array($name));
	if(!$result){
		$resp = array('success' => 0, 'msg' => 'DB failed.');
		echo json_encode($resp);
		exit();
	}
	
	$resp = array('success' => 1);
	echo json_encode($resp);
	exit();
}
else if($action == 'create_company'){
	
}
?>