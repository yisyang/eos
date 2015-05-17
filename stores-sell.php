<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_GET['fsid'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = 'store';
if(!$bldg_id){
	fbox_breakout('buildings.php');
}

// Make sure the eos user actually owns the building
$query = $db->prepare("SELECT store_id AS bldg_type_id, store_name AS bldg_name, size, slot, marketing FROM firm_store WHERE id = ? AND fid = ?");
$query->execute(array($bldg_id, $eos_firm_id));
$bldg = $query->fetch(PDO::FETCH_ASSOC);
if(empty($bldg)){
	fbox_breakout('buildings.php');
}else{
	$bldg_type_id = $bldg['bldg_type_id'];
	$bldg_name = $bldg['bldg_name'];
	$bldg_size = $bldg['size'];
	$bldg_slot = $bldg['slot'];
	$store_marketing = $bldg['marketing'];
}

// and that it is not under construction
$sql = "SELECT COUNT(*) FROM queue_build WHERE building_type = '$bldg_type' AND building_id = '$bldg_id'";
$count = $db->query($sql)->fetchColumn();
if($count){
	fbox_redirect('bldg-expand-status.php?type='.$bldg_type.'&id='.$bldg_id);
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	// Initialize bldg image for store
	$sql = "SELECT name, has_image FROM list_store WHERE id = $bldg_type_id";
	$list_bldg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($list_bldg["has_image"]){
		$bldg_img_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_bldg["name"]));
	}else{
		$bldg_img_filename = "no-image";
	}

	// Initialize store shelves choices
	$sql = "SELECT firm_store_shelves.shelf_slot, list_prod.id, firm_wh.id AS wh_id, firm_wh.pidq, firm_wh.pidcost AS pid_cost, firm_wh.pidprice AS pid_price, firm_wh.pidpartialsale, firm_wh.no_sell, IFNULL(firm_wh.pidn,0) AS pidn_total, list_prod.cat_id, list_prod.name, list_prod.has_icon, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.demand_met, list_prod.selltime, list_cat.price_multiplier 
	FROM firm_store_shelves 
	LEFT JOIN firm_wh ON firm_store_shelves.wh_id = firm_wh.id AND firm_wh.fid = $eos_firm_id 
	LEFT JOIN list_prod ON firm_wh.pid = list_prod.id 
	LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id 
	WHERE firm_store_shelves.fsid = $bldg_id AND !ISNULL(firm_wh.id) GROUP BY list_prod.id ORDER BY firm_store_shelves.shelf_slot ASC";
	$store_shelves = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$total_shelves = 8;
	for($i = 1; $i <= $total_shelves; $i++){
		$sc_shelf_active[$i] = 0;
	}
	if(count($store_shelves)){
		$sql = "SELECT tick FROM log_sales ORDER BY id DESC LIMIT 0,1";
		$final_tick = $db->query($sql)->fetchColumn();
		foreach($store_shelves as $store_shelf){
			$sc_shelf_slot = $store_shelf['shelf_slot'];
			$sc_shelf_active[$sc_shelf_slot] = 1;
			$ipid_wh_id[$sc_shelf_slot] = $store_shelf['wh_id'];
			$ipid_q[$sc_shelf_slot] = $store_shelf['pidq'];
			$ipid_q_avg[$sc_shelf_slot] = $store_shelf['q_avg'];
			$ipid_value_base[$sc_shelf_slot] = $store_shelf['value'];
			$ipid_value_avg[$sc_shelf_slot] = $store_shelf['value_avg'];
			$ipid_cost[$sc_shelf_slot] = $store_shelf['pid_cost'];
			$ipid_msrp[$sc_shelf_slot] = max(2 * $ipid_cost[$sc_shelf_slot], 2 * $ipid_value_base[$sc_shelf_slot] * (1 + 0.02 * $ipid_q[$sc_shelf_slot]));
			$ipid_price[$sc_shelf_slot] = $store_shelf['pid_price'];
			$ipid_price_multiplier[$sc_shelf_slot] = $store_shelf['price_multiplier'];

			$sc_pid[$sc_shelf_slot] = $store_shelf['id'];
			$sc_pid_inactive[$sc_shelf_slot] = $store_shelf['no_sell'];
			$sc_pid_n_total[$sc_shelf_slot] = $store_shelf['pidn_total'];
			
			$sc_pid_partial_sale[$sc_shelf_slot] = $store_shelf['pidpartialsale'];
			$sc_cat_id[$sc_shelf_slot] = $store_shelf['cat_id'];
			$sc_name[$sc_shelf_slot] = $store_shelf['name'];
			$sc_has_icon[$sc_shelf_slot] = $store_shelf['has_icon'];
			$sc_demand_met[$sc_shelf_slot] = $store_shelf['demand_met'];
			$sc_selltime[$sc_shelf_slot] = $store_shelf['selltime'];
			
			$nskp = max(0.3,(1 + 0.02 * ($ipid_q[$sc_shelf_slot] - $ipid_q_avg[$sc_shelf_slot]))) * (5/(1 + 10 * max(0.15, $sc_demand_met[$sc_shelf_slot])));
			$nkcd = (min(1,$sc_demand_met[$sc_shelf_slot]) * 0.5 * min($ipid_value_avg[$sc_shelf_slot],50*$ipid_value_base[$sc_shelf_slot]) + (2 - min(1,max(0.15,$sc_demand_met[$sc_shelf_slot]))) * $ipid_value_base[$sc_shelf_slot]) * $ipid_price_multiplier[$sc_shelf_slot];
			$nskp2[$sc_shelf_slot] = $nskp * $nskp * $nkcd * $nkcd / $sc_selltime[$sc_shelf_slot];

			$modified_store_size = 0;
			$sc_pid_est_revenue[$sc_shelf_slot] = 0;
			if($sc_pid_n_total[$sc_shelf_slot]){
				$sql = "SELECT a.size, a.marketing, COUNT(firm_wh.id) AS total_selling FROM (SELECT firm_store.id, firm_store.size, firm_store.marketing FROM firm_store_shelves LEFT JOIN firm_store ON firm_store_shelves.wh_id = ".$ipid_wh_id[$sc_shelf_slot]." AND firm_store_shelves.fsid = firm_store.id WHERE !firm_store.is_expanding) AS a LEFT JOIN firm_store_shelves ON a.id = firm_store_shelves.fsid LEFT JOIN firm_wh ON firm_store_shelves.wh_id = firm_wh.id WHERE !firm_wh.no_sell AND firm_wh.pidn GROUP BY a.id";
				$shelves_details = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				foreach($shelves_details as $shelf_details){
					$modified_store_size += (14.4 / $shelf_details['total_selling'] + 1.2) * $shelf_details['size'] * (1 + pow($shelf_details['marketing'], 0.25) / 100);
				}
				$nskp2[$sc_shelf_slot] = $nskp2[$sc_shelf_slot] * $modified_store_size;
				if($ipid_price[$sc_shelf_slot] > 0){
					$sc_pid_est_revenue[$sc_shelf_slot] = $ipid_price[$sc_shelf_slot] * min($sc_pid_n_total[$sc_shelf_slot], floor($nskp2[$sc_shelf_slot] / $ipid_price[$sc_shelf_slot] / $ipid_price[$sc_shelf_slot] + $sc_pid_partial_sale[$sc_shelf_slot]));
				}else{
					$sc_pid_est_revenue[$sc_shelf_slot] = 0;
				}
			}

			$sql = "SELECT IFNULL(SUM(value),0) AS value_total, IFNULL(SUM(pidn),0) AS n_total FROM log_sales WHERE fid=$eos_firm_id AND pid=".$sc_pid[$sc_shelf_slot]." AND tick = $final_tick";
			$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			$recent_sold_value_total_15[$sc_shelf_slot] = $result["value_total"];
			$recent_sold_value_n_15[$sc_shelf_slot] = $result["n_total"];
			
			$final_tick_48 = $final_tick - 48;
			$sql = "SELECT tick, IFNULL(SUM(value),0) AS value_total FROM log_sales WHERE fid = $eos_firm_id AND pid = $sc_pid[$sc_shelf_slot] AND tick > $final_tick_48 GROUP BY tick";
			$sparkline_results = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			for($j=$final_tick_48+1;$j<=$final_tick;$j++){
				$recent_sold_value_total_sparkline_data[$j] = 0;
			}
			if(!empty($sparkline_results)){
				foreach($sparkline_results as $sparkline_result){
					$tick = $sparkline_result['tick'];
					$recent_sold_value_total_sparkline_data[$tick] = round($sparkline_result['value_total']/100);
				}
			}
			$recent_sold_value_total_sparkline[$sc_shelf_slot] = '';
			for($j=$final_tick_48+1;$j<=$final_tick;$j++){
				$recent_sold_value_total_sparkline[$sc_shelf_slot] .= $recent_sold_value_total_sparkline_data[$j].',';
			}
			$recent_sold_value_total_sparkline[$sc_shelf_slot] = substr($recent_sold_value_total_sparkline[$sc_shelf_slot],0,-1);
			
			if($recent_sold_value_n_15[$sc_shelf_slot]){
				$temp_est_supply = $sc_pid_n_total[$sc_shelf_slot] / $recent_sold_value_n_15[$sc_shelf_slot];
				if($temp_est_supply > 672){
					$sc_pid_n_est_supply[$sc_shelf_slot] = 'Greater than 1 year <br />(&gt;7 server days)';
				}else{
					$sc_pid_n_est_supply[$sc_shelf_slot] = number_format($temp_est_supply*7/1152,2,'.','').' months <br />('.number_format($temp_est_supply/96,2,'.','').' server days)';
				}
				if($temp_est_supply > 288){
					$temp_est_supply = 288;
				}
			}else{
				$temp_est_supply = 288;
				$sc_pid_n_est_supply[$sc_shelf_slot] = 'N/A';
			}
			$temp_color_adj = ($temp_est_supply - 144) / 144;
			// 1 = > 3 days worth of supply = rgb(0,127,32)
			// 0 = 144 ticks (1.5 days) worth of supply = rgb(191,191,0)
			// -1 = ran out = rgb(255,0,0)
			if($temp_color_adj < 0){
				$temp_color_r = floor(191 - 64 * $temp_color_adj);
				$temp_color_g = floor(191 + 191 * $temp_color_adj);
				$sc_pid_n_color[$sc_shelf_slot] = "rgb($temp_color_r,$temp_color_g,0)";
			}else if($temp_color_adj > 0){
				$temp_color_r = floor(191 - 191 * $temp_color_adj);
				$temp_color_g = floor(191 - 64 * $temp_color_adj);
				$temp_color_b = floor(32 * $temp_color_adj);
				$sc_pid_n_color[$sc_shelf_slot] = "rgb($temp_color_r,$temp_color_g,$temp_color_b)";
			}else{
				$sc_pid_n_color[$sc_shelf_slot] = "rgb(191,191,0)";
			}
			
			$sql = "SELECT IFNULL(SUM(value),0) AS value_total_all, IFNULL(SUM(pidn),0) AS n_total_all FROM log_sales WHERE pid=".$sc_pid[$sc_shelf_slot]." AND tick = $final_tick";
			$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			$recent_sold_value_total_15_all = $result["value_total_all"];
			$recent_sold_n_total_15_all = $result["n_total_all"];
			$recent_sold_value_avg_15_all[$sc_shelf_slot] = 0;
			$recent_sold_value_market_share_15[$sc_shelf_slot] = 0;
			if($recent_sold_n_total_15_all){
				$recent_sold_value_avg_15_all[$sc_shelf_slot] = $recent_sold_value_total_15_all/$recent_sold_n_total_15_all;
				if($recent_sold_value_total_15[$sc_shelf_slot]){
					$recent_sold_value_market_share_15[$sc_shelf_slot] = $recent_sold_value_total_15[$sc_shelf_slot]/$recent_sold_value_total_15_all;
				}
			}
			
			$sql = "SELECT COUNT(*) FROM market_prod WHERE pid = ".$sc_pid[$sc_shelf_slot];
			$pid_available_b2b[$sc_shelf_slot] = $db->query($sql)->fetchColumn();
		}
	}
?>
		<script type="text/javascript">
			var timer_is_on;
			var sales_price = [];
			var sales_price_old = [];
			var sales_price_min = 0;
			var sales_price_max = 999999999900;
			var data_n_total = [];
			var data_partial_sale = [];
			var data_partial_sale_new = [];
			var data_nskp2 = [];
			<?php
				foreach($store_shelves as $store_shelf){
					$sc_shelf_slot = $store_shelf['shelf_slot'];
					if($sc_pid_n_total[$sc_shelf_slot]){
						echo 'data_n_total[',$ipid_wh_id[$sc_shelf_slot],'] = ',$sc_pid_n_total[$sc_shelf_slot],';';
						echo 'data_partial_sale[',$ipid_wh_id[$sc_shelf_slot],'] = ',$sc_pid_partial_sale[$sc_shelf_slot],';';
						echo 'data_nskp2[',$ipid_wh_id[$sc_shelf_slot],'] = ',$nskp2[$sc_shelf_slot],';';
					}
				}
				//$sc_pid_est_revenue[$sc_shelf_slot] = $ipid_price[$sc_shelf_slot] * min($sc_pid_n_total[$sc_shelf_slot], floor($nskp2[$sc_shelf_slot] / $ipid_price[$sc_shelf_slot] / $ipid_price[$sc_shelf_slot] + $sc_pid_partial_sale[$sc_shelf_slot]));
			?>
			
			function useMSRP(whId, MSRP){
				document.getElementById('sales_price_visible_'+whId).value = formatNum(MSRP, 2);
				updateSprice(whId);
				estimateRevenue(whId);
			}
			function estimateRevenue(whId){
				var est_rev = 0;
				sales_price[whId] = Math.floor(stripCommas(document.getElementById('sales_price_visible_'+whId).value)*100+0.5);
				sales_price_old[whId] = document.getElementById('sales_price_'+whId).value;
				if(!sales_price[whId]){
					return false;
				}
				if(typeof(data_n_total[whId]) !== 'undefined'){
					data_partial_sale_new[whId] = data_partial_sale[whId] * sales_price_old[whId] * sales_price_old[whId] / sales_price[whId] / sales_price[whId];
					est_rev = sales_price[whId] * Math.min(data_n_total[whId], Math.floor(data_nskp2[whId] / sales_price[whId] / sales_price[whId] + data_partial_sale_new[whId]));
				}
				jQuery("#revenue_projected_"+whId).html(formatNumReadable(est_rev/100));
				jQuery("#revenue_projected_long_"+whId).html(formatNum(est_rev/100, 2));
			}
			function initUpdateSprice(whId){
				document.getElementById('sales_price_visible_'+whId).style.color = "#AA0000";
				jQuery("#set_price_response_"+whId).html("&nbsp;");
				estimateRevenue(whId);
				if(!timer_is_on){
					t = setTimeout('updateSprice("'+whId+'")',3000);
					timer_is_on = 1;
				}else{
					clearTimeout(t);
					t = setTimeout('updateSprice("'+whId+'")',3000);
				}
			}
			function updateSprice(whId){
				sales_price[whId] = Math.floor(stripCommas(document.getElementById('sales_price_visible_'+whId).value)*100+0.5);
				sales_price_old[whId] = document.getElementById('sales_price_'+whId).value;
				if(!sales_price[whId]){
					return false;
				}
				if(sales_price[whId] > sales_price_max){
					sales_price[whId] = sales_price_max;
					document.getElementById('sales_price_visible_'+whId).value = sales_price[whId]/100;
				}
				if(sales_price[whId] < sales_price_min){
					sales_price[whId] = sales_price_min;
					document.getElementById('sales_price_visible_'+whId).value = sales_price[whId]/100;
				}
				if(sales_price[whId] == sales_price_old[whId]){
					document.getElementById('sales_price_visible_'+whId).style.color = "#00AA00";
					return false;
				}
				if(sales_price[whId] != sales_price_old[whId]){
					document.getElementById('sales_price_visible_'+whId).value = sales_price[whId]/100;
					setNewPrice(whId);
				}
			}
			function setNewPrice(whId){
				var params = {action: 'set_price', sales_price: sales_price[whId], wh_id: whId};
				storeController.executeAjax(params, function(resp){
					data_partial_sale[whId] = data_partial_sale[whId] * sales_price_old[whId] * sales_price_old[whId] / sales_price[whId] / sales_price[whId];
					document.getElementById('sales_price_'+whId).value = sales_price[whId];
					jQuery("#set_price_response_"+whId).html("<img src=\"/images/success.gif\" /> OK");
					document.getElementById('sales_price_visible_'+whId).style.color = "#00AA00";
					if(typeof(document.getElementById("store_sns_div_"+whId)) !== 'undefined' && document.getElementById("store_sns_div_"+whId) !== null){
						document.getElementById("store_sns_div_"+whId).style.display = 'none';
					}
					setTimeout('jQuery("#set_price_response_'+whId+'").html("&nbsp;")', 1000);
				});
			}

			modalController.backLink = 'stores-sell.php?fsid=<?= $bldg_id ?>';
			modalController.backLinkTitle = 'Back to Store';

			jQuery(document).ready(function(){
				jQuery('a#bldg_expand_button').on('click', function(){
					jqDialogInit('bldg-expand.php', {
						id : <?= $bldg_id ?>,
						type : '<?= $bldg_type ?>'
					});
				});
				jQuery('a#bldg_sell_button').on('click', function(){
					jqDialogInit('bldg-sell.php', {
						id : <?= $bldg_id ?>,
						type : '<?= $bldg_type ?>'
					});
				});
				jQuery('a#store_paste_button').on('click', function(){
					storeController.pasteToAll(<?= $bldg_id ?>);
				});
				jQuery('a#store_lazy_button').on('click', function(){
					storeController.lazyPricing(<?= $bldg_id ?>);
				});
				setTimeout(function(){
					jQuery('.sparklines_revenue').sparkline('html', { type: 'line', width: '100px', enableTagOptions: true });
				}, 1000);
			});
		</script>
		<div style="float: left;padding-right: 15px;">
			<img src="/eos/images/<?= $bldg_type ?>/<?= $bldg_img_filename ?>.gif" width="180" height="80" />
		</div>
		<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;">
			<div class="building_name_container"><span class="building_name" id="building_name"><?= $bldg_name.' ('.$bldg_size.' m&#178; <a title="Marketing effect">+'.number_format(pow($store_marketing, 0.25),2,'.',',').'%</a>)' ?> 
			<?php if($ctrl_store_sell){ ?><img src="/eos/images/edit.gif" width="24" height="24" title="Rename Building" onclick="bldgController.showNameUpdater('<?= htmlspecialchars($bldg_name) ?>',<?= $bldg_id ?>,'<?= $bldg_type ?>');" /><?php } ?></span> <a class="jqDialog" href="bldg-swap-slot.php?bldg_id=<?= $bldg_id ?>&bldg_type=<?= $bldg_type ?>"><img src="/eos/images/swap.png" width="24" height="24" title="Move Building" /></a> <a class="info"><img src="images/info.png" /><span style="width:300px;line-height:1.5;">Sales are tick-based (Every 15 min. on the 0th, 15th, 30th, and 45th minutes). <br /><br />Simply stock the shelves and set the price, anything sold will be deducted directly from your warehouse.<br /><br />Product prices and sales data are shared between all stores under the company.</span></a></div>
			<a id="bldg_expand_button" style="cursor:pointer;"><img src="/eos/images/button-build.gif" title="Expand Building" alt="[Expand]" /></a> &nbsp; 
			<a id="bldg_sell_button" style="cursor:pointer;"><img src="/eos/images/button-sell.gif" title="Sell Building" alt="[Sell]" /></a> &nbsp; 
			<a class="jqDialog" href="stores-marketing.php?fsid=<?= $bldg_id ?>"><img src="/eos/images/button-marketing.gif" title="Marketing" alt="[Marketing]" /></a> &nbsp; 
			<a href="/eos/market.php?view_type=store&view_type_id=<?= $bldg_type_id ?>"><img src="/eos/images/b2b_store.gif" title="View B2B Products" alt="[B2B]" /></a> &nbsp; 
			<a id="store_lazy_button" class="info" style="cursor:pointer;"><img src="/eos/images/lazy_2x.gif" alt="[Lazy 2X]" /><span style="line-height:1.5;">Start <b>all paused sales</b> at 2x cost or 2x quality-adjusted value, whichever is higher.<br><br><font color="#ff0000">There will be no more confirmation.</font></span></a> &nbsp; 
			<?php if($ctrl_store_price){ ?><a class="jqDialog" href="stores-sell-copy.php?fsid=<?= $bldg_id ?>"><img src="/eos/images/shelf-copy.gif" title="Copy shelves layout from another store." alt="[Copy Shelves]" /></a> &nbsp; <?php } ?>
			<?php if($ctrl_store_price){ ?><a id="store_paste_button" class="info" style="cursor:pointer;"><img src="/eos/images/shelf-paste.gif" alt="[Paste All]" /><span style="line-height:1.5;">Paste the current store's shelves layout to all other stores of this type. <br><br><font color="#ff0000">There will be no more confirmation.</font></span></a><?php } ?>
		</div>

<?php
	$j_per_row = 4;
	$j = $j_per_row;
	$efficiency_boost = 0;
	for($i = 1; $i <= $total_shelves; $i++){
		if($j == $j_per_row){
			$j = 1;
			echo '<div class="prod_choices">';
		}else{
			$j++;
		}
		echo '<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;">';
		if($ctrl_store_price){
			$shelf_link = 'stores-sell-shelf.php?fsid='.$bldg_id.'&shelf_slot='.$i;
		}else{
			$shelf_link = '#no_auth';
		}
		if(isset($sc_pid[$i])){
			$chart_link = 'stores-sell-details.php?sc_pid='.$sc_pid[$i].'&fsid='.$bldg_id;
		}else{
			$sc_pid[$i] = 0;
			$chart_link = $shelf_link;
		}
		if($sc_shelf_active[$i] && $ipid_wh_id[$i]){
			if($sc_has_icon[$i]){
				$sc_ipid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($sc_name[$i]));
			}else{
				$sc_ipid_filename = "no-icon";
			}
			if($ipid_wh_id[$i]){
				if($sc_pid_n_total[$i] < 1){
					echo '<a class="jqDialog" href="',$chart_link,'"><img src="/eos/images/prod/large/',$sc_ipid_filename,'.gif" style="margin-bottom:6px;" /></a>';
					echo '<div style="position:absolute;left:0;top:0;width:96px;height:96px;"><a class="jqDialog info" href="',$chart_link,'"><img src="/eos/images/prod/large/not-available.png" /><span>',$sc_name[$i],'<br /><font style="color:#ff0000;font-style:italic;">SOLD OUT</font><br /><br />Produce or buy more goods, or use the replace item button (box icon at upper right) to sell another product.</span></a></div>';
				}else if($sc_pid_inactive[$i]){
					echo '<a class="jqDialog info" href="',$chart_link,'"><img src="/eos/images/prod/large/',$sc_ipid_filename,'.gif" style="margin-bottom:6px;" /><span>',$sc_name[$i],'</span></a>';
					echo '<div id="store_sns_div_',$ipid_wh_id[$i],'" style="position:absolute;top:0;left:0;">';
						echo '<a style="cursor:pointer;" class="info" onclick="storeController.toggleSellable(',$ipid_wh_id[$i],', 1)"><img src="images/warning.png" width="36" height="36" /><span><font style="color:#ff0000;font-style:italic;">Inactive</font><br /><br />Sales is paused on this item. Change the sales price or give a direct order to begin sales.</span></a>';
					echo '</div>';
				}else{
					echo '<a class="jqDialog info" href="',$chart_link,'"><img src="/eos/images/prod/large/',$sc_ipid_filename,'.gif" style="margin-bottom:6px;" /><span>',$sc_name[$i],'</span></a>';
					$efficiency_boost += 5;
				}
				echo '<div style="position:absolute;top:0;left:72px;"><a class="jqDialog info" href="',$shelf_link,'"><img src="images/box.png" /><span>Replace Item on Shelf</span></a></div><div style="position:absolute;top:26px;left:72px;"><a class="jqDialog info" href="pedia-product-view.php?pid='.$sc_pid[$i].'&fsid='.$bldg_id.'"><img src="images/pedia.png" /><span>View on EOS-Pedia</span></a></div><br />';
				echo '<a class="info vert_middle" style="margin: 0 0 0 10px;font-weight:normal;"><img src="/eos/images/box.png" alt="#" title="Saleable Quantity" /><div style="display:inline;color:',$sc_pid_n_color[$i],'"> ',number_format_readable($sc_pid_n_total[$i]),'</div><span>Quantity: <br />',number_format($sc_pid_n_total[$i],0,'.',','),'<br /><br />Est. Supply: <br />',$sc_pid_n_est_supply[$i],'</span></a><br />';
				echo '<a class="info vert_middle" style="margin: 0 0 0 10px;font-weight:normal;"><img src="/eos/images/money.gif" alt="#" /> '.number_format_readable($recent_sold_value_total_15[$i]/100).'<span>Revenue (15 min.): <br />$',number_format($recent_sold_value_total_15[$i]/100,2,'.',','),'</span></a><br />';
				echo '<a class="info vert_middle" style="margin: 0 0 0 10px;font-weight:normal;"><img src="/eos/images/moneyp.gif" alt="#" /> <div id="revenue_projected_',$ipid_wh_id[$i],'" style="display:inline;">'.number_format_readable($sc_pid_est_revenue[$i]/100).'</div><span>Projected (15 min.): <br />$<div id="revenue_projected_long_',$ipid_wh_id[$i],'" style="display:inline;">',number_format($sc_pid_est_revenue[$i]/100,2,'.',','),'</div></span></a><br />';
				
				echo '<span class="sparklines_revenue" sparkBarColor="green" values="',$recent_sold_value_total_sparkline[$i],'"></span><br />';
				echo '<a class="info vert_middle" style="font-weight:normal;">Cost: $'.number_format_readable($ipid_cost[$i]/100).'<span>Cost: <br />$',number_format($ipid_cost[$i]/100,2,'.',','),'</span></a><br />';
				echo '<a class="info vert_middle" style="font-weight:normal;cursor:pointer;" onclick="useMSRP(',$ipid_wh_id[$i],',',$ipid_msrp[$i]/100,')">MSRP: $'.number_format_readable($ipid_msrp[$i]/100).'<span>Manufacturer&rsquo;s Suggested Retail Price: <br />$',number_format($ipid_msrp[$i]/100,2,'.',','),'<br /><br />(Click to Use)</span></a><br />';
				echo '<div class="sspi_details">';
				if($ctrl_store_price){
					echo '<input id="sales_price_',$ipid_wh_id[$i],'" type="hidden" style="display:none;" value="',$ipid_price[$i],'" size="10" maxlength="10" />
					<span style="color:#997755;font-size:18px;font-weight:normal;">$ <input id="sales_price_visible_',$ipid_wh_id[$i],'" type="text" style="width:65px;border:2px solid #997755;" value="',($ipid_price[$i]/100),'" maxlength="10" onkeyup="initUpdateSprice(',$ipid_wh_id[$i],')" onblur="updateSprice(',$ipid_wh_id[$i],');" /></span>';
				}else{
					echo '<span style="color:#997755;font-size:18px;">$ ',($ipid_price[$i]/100),'</span>';
				}
				echo '</div>';
				echo '<a class="info vert_middle" style="font-weight:normal;">WASP: $'.number_format_readable($recent_sold_value_avg_15_all[$i]/100).'<span>World Average Store Price (15 min.): <br />$',number_format($recent_sold_value_avg_15_all[$i]/100,2,'.',','),'</span></a><br />';
				echo '<a class="info vert_middle" style="font-weight:normal;">D. Met: '.number_format($sc_demand_met[$i]*100,2,'.',',').'%<span>Demand Met: <br />',number_format($sc_demand_met[$i]*100,2,'.',','),'%</span></a><br />';
				echo '<a class="info vert_middle" style="font-weight:normal;">MS: '.number_format($recent_sold_value_market_share_15[$i]*100,2,'.',',').'%<span>Market Share (15 min.): <br />',number_format($recent_sold_value_market_share_15[$i]*100,2,'.',','),'%</span></a><br />';
				
				if($pid_available_b2b[$i]){
					echo '<a href="/eos/market.php?view_type=prod&view_type_id=',$sc_pid[$i],'" title="Purchase on B2B"><input type="button" class="bigger_input" value="B2B" /></a>';
				}else{
					echo '<input type="button" class="bigger_input" value="B2B" disabled="disabled" title="No B2B listing found." />';
				}
				echo '<div id="set_price_response_',$ipid_wh_id[$i],'" class="sspi_details" style="line-height:24px;">&nbsp;</div>';
			}
		}else{
			echo '<a class="jqDialog info" href="',$shelf_link,'"><img src="/eos/images/empty_shelf.gif" style="margin-bottom:6px;" /><span>Click to Place Item on Shelf</span></a><br />';
		}
		echo '</div></div>';
		
		if($j == $j_per_row || $i == $total_shelves){
			echo '</div>';
		}
	}
?>
	<div style="clear:both;">&nbsp;</div>
	<h3>Efficiency: <?= ($efficiency_boost) ? 60 + $efficiency_boost : 0 ?>% <a class="info"><img src="images/info.png" /><span style="width:300px;line-height:1.5;">Selling power is distributed equally among all products. Empty shelves leads to increased sales on the products that are selling, but also comes with a penalty on sales efficiency.</span></a></h3>
	<div style="clear:both;">&nbsp;</div>
	<br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>