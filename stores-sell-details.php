<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_GET['fsid'], FILTER_SANITIZE_NUMBER_INT);
$sc_pid = filter_var($_GET['sc_pid'], FILTER_SANITIZE_NUMBER_INT);
if(!$bldg_id || !$sc_pid){
	fbox_breakout('buildings.php');
}

// Make sure the eos user actually owns the building
$query = $db->prepare("SELECT store_id AS bldg_type_id, store_name AS bldg_name, size, slot FROM firm_store WHERE id = ? AND fid = ?");
$query->execute(array($bldg_id, $eos_firm_id));
$bldg = $query->fetch(PDO::FETCH_ASSOC);
if(empty($bldg)){
	fbox_breakout('buildings.php');
}else{
	$bldg_type_id = $bldg['bldg_type_id'];
	$bldg_name = $bldg['bldg_name'];
	$bldg_size = $bldg['size'];
	$bldg_slot = $bldg['slot'];
}

// Populate cost data in $, time, and ipids
// Next check sc_pid belongs to store_id
$sql = "SELECT name, value, cat_id, demand_met, has_icon FROM list_prod WHERE id = '$sc_pid'";
$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$sc_cat_id = $prod["cat_id"];
$sql = "SELECT COUNT(*) AS cnt FROM list_store_choices WHERE store_id = '$bldg_type_id' AND cat_id = '$sc_cat_id'";
$count = $db->query($sql)->fetchColumn();
if(!$count){
	fbox_redirect('stores-sell.php?fsid='.$bldg_id);
}
$ipid_name = $prod['name'];
$ipid_value_base = $prod['value'];
$ipid_demand_met = $prod['demand_met'];
if($prod['has_icon']){
	$ipid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($ipid_name));
}else{
	$ipid_filename = "no-icon";
}

// Populate prev and next
$sql = "SELECT list_prod.id, list_prod.name, list_prod.has_icon FROM list_prod LEFT JOIN list_store_choices ON list_prod.cat_id = list_store_choices.cat_id WHERE list_store_choices.store_id = $bldg_type_id ORDER BY list_prod.name ASC";
$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$store_choices_count = count($store_choices);
$matched = 0;
$i = 0;
$sc_link_prev = '';
$sc_link_next = '';

while(!$matched && $i < $store_choices_count){
	if($store_choices[$i]['id'] == $sc_pid){
		if($i > 0){
			if($store_choices[$i-1]['has_icon']){
				$sc_link_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($store_choices[$i-1]['name']));
			}else{
				$sc_link_filename = "no-icon";
			}
			$sc_link_prev = '<a class="jqDialog" href="stores-sell-details.php?fsid='.$bldg_id.'&sc_pid='.$store_choices[$i-1]['id'].'" title="Previous - '.$store_choices[$i-1]['name'].'">&#8592; <img src="/eos/images/prod/'.$sc_link_filename.'.gif" /></a>';
		}
		if($i + 1 < $store_choices_count){
			if($store_choices[$i+1]['has_icon']){
				$sc_link_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($store_choices[$i+1]['name']));
			}else{
				$sc_link_filename = "no-icon";
			}
			$sc_link_next = '<a class="jqDialog" href="stores-sell-details.php?fsid='.$bldg_id.'&sc_pid='.$store_choices[$i+1]['id'].'" title="Next - '.$store_choices[$i+1]['name'].'"><img src="/eos/images/prod/'.$sc_link_filename.'.gif" /> &#8594;</a>';
		}
		break;
	}
	$i++;
}

// and get ipidn from firm warehouse for each ipid where ipidn >= required
$sql = "SELECT * FROM firm_wh WHERE pid = '$sc_pid' AND fid = '$eos_firm_id' ORDER BY pidq ASC";
$wh_prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$timenow = time();
$timenow_tick = floor(($timenow - 1327104000)/900);
		
//Get some stats
$sql = "SELECT tick FROM log_sales ORDER BY id DESC LIMIT 0,1";
$final_tick = $db->query($sql)->fetchColumn();

$sql = "SELECT IFNULL(SUM(value),0) AS value_total, IFNULL(SUM(pidn),0) AS n_total FROM log_sales WHERE fid = $eos_firm_id AND pid = $sc_pid AND tick = $final_tick";
$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$recent_sold_value_total_15 = $result["value_total"];
$recent_sold_n_total_15 = $result["n_total"];

$sql = "SELECT IFNULL(SUM(value),0) AS value_total_all, IFNULL(SUM(pidn),0) AS n_total_all FROM log_sales WHERE fid != 7 AND pid = $sc_pid AND tick = $final_tick";
$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$recent_sold_value_total_15_all = $result["value_total_all"];
$recent_sold_n_total_15_all = $result["n_total_all"];

$recent_sold_value_avg_15_all = 0;
$recent_sold_value_avg_15 = 0;
$recent_sold_value_market_share_15 = 0;
$recent_sold_n_market_share_15 = 0;
if($recent_sold_n_total_15_all){
	$recent_sold_value_avg_15_all = $recent_sold_value_total_15_all/$recent_sold_n_total_15_all;
	if($recent_sold_n_total_15){
		$recent_sold_value_avg_15 = $recent_sold_value_total_15/$recent_sold_n_total_15;
		$recent_sold_value_market_share_15 = $recent_sold_value_total_15/$recent_sold_value_total_15_all;
		$recent_sold_n_market_share_15 = $recent_sold_n_total_15/$recent_sold_n_total_15_all;
	}
}

$sql = "SELECT IFNULL(SUM(value),0) AS value_total, IFNULL(SUM(pidn),0) AS n_total FROM log_sales WHERE fid = $eos_firm_id AND pid = $sc_pid AND tick > $final_tick-96";
$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$recent_sold_value_total = $result["value_total"];
$recent_sold_n_total = $result["n_total"];

$sql = "SELECT IFNULL(SUM(value),0) AS value_total_all, IFNULL(SUM(pidn),0) AS n_total_all FROM log_sales WHERE fid != 7 AND pid = $sc_pid AND tick > $final_tick-96";
$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$recent_sold_value_total_all = $result["value_total_all"];
$recent_sold_n_total_all = $result["n_total_all"];

$recent_sold_value_avg_all = 0;
$recent_sold_value_avg = 0;
$recent_sold_value_market_share = 0;
$recent_sold_n_market_share = 0;
if($recent_sold_n_total_all){
	$recent_sold_value_avg_all = $recent_sold_value_total_all/$recent_sold_n_total_all;
	if($recent_sold_n_total){
		$recent_sold_value_avg = $recent_sold_value_total/$recent_sold_n_total;
		$recent_sold_value_market_share = $recent_sold_value_total/$recent_sold_value_total_all;
		$recent_sold_n_market_share = $recent_sold_n_total/$recent_sold_n_total_all;
	}
}
?>
<?php require 'include/functions.php'; ?>
		<script type="text/javascript">
			var timer_is_on;
			var sales_price = [];
			var sales_price_old = [];
			var ipid_base_value = <?= $ipid_value_base ?>;
			var sales_price_min = 0;
			var sales_price_max = 999999999900;

			function initUpdateSprice(whId){
				document.getElementById('sales_price_visible_'+whId).style.color = "#AA0000";
				jQuery("#set_price_response_"+whId).html("&nbsp;");
				if(!timer_is_on){
					t = setTimeout('updateSprice("'+whId+'")',1200);
					timer_is_on = 1;
				}else{
					clearTimeout(t);
					t = setTimeout('updateSprice("'+whId+'")',1200);
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
					document.getElementById('sales_price_'+whId).value = sales_price[whId];
					jQuery("#set_price_response_"+whId).html("<img src=\"/images/success.gif\" /> OK");
					document.getElementById('sales_price_visible_'+whId).style.color = "#00AA00";
					if(typeof(document.getElementById("store_sns_div_"+whId)) !== 'undefined' && document.getElementById("store_sns_div_"+whId) !== null){
						document.getElementById("store_sns_div_"+whId).style.backgroundImage="url(images/anim_gear_2.gif)";
						jQuery("#store_sns_div_"+whId).html("<img src=\"images/translucent.png\" alt=\"Pause Sales\" title=\"Click to Pause Sales\" width=\"40\" height=\"40\" onclick=\"storeController.toggleSellable("+whId+",0);\" />");
					}
					setTimeout('jQuery("#set_price_response_'+whId+'").html("&nbsp;")', 1000);
				});
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
<h3>Details (<a class="jqDialog" href="stores-sell.php?fsid=<?= $bldg_id ?>">Back to Store</a>)</h3>
<div id="fbox_inner_wrapper" style="width:670px;margin: 0 auto;">
	<div class="store_sell_module_prod">
		<?php
			echo '<span class="vert_middle" style="display:inline-block;width:48px;">',$sc_link_prev,'</span>';
			echo '<span class="vert_middle" style="display:inline-block;width:48px;text-align:right;">',$sc_link_next,'</span>';
		?>
		<form>
		<?php
			if(count($wh_prods)){
				foreach($wh_prods as $wh_prod){
					if(!$wh_prod["pidprice"]){
						$wh_prod["pidprice"] = $ipid_value_base*2;
					}
					echo '<div class="store_set_price_items"><div style="position: relative; left: 0; top: 0;">';
						echo '<img src="/eos/images/prod/large/',$ipid_filename,'.gif" title="',$ipid_name,' (Quality ',$wh_prod["pidq"],'): ',number_format($wh_prod["pidn"], 0, '.', ','),' Available" style="margin-bottom:6px;" />';
						if($ctrl_store_price){
							if($wh_prod["no_sell"]){
								echo '<div id="store_sns_div_',$wh_prod["id"],'" style="position:absolute;top:28px;left:28px;background-image:url(images/gear_inactive.png);">';
									echo '<img src="images/translucent.png" alt="Start Selling" title="Click to Start Selling" width="40" height="40" onclick="storeController.toggleSellable(',$wh_prod["id"],',0);" />';
								echo '</div>';
							}else{
								echo '<div id="store_sns_div_',$wh_prod["id"],'" style="position:absolute;top:28px;left:28px;background-image:url(images/anim_gear_2.gif);">';
									echo '<img src="images/translucent.png" alt="Pause Sales" title="Click to Pause Sales" width="40" height="40" onclick="storeController.toggleSellable(',$wh_prod["id"],',0);" />';
								echo '</div>';
							}
						}
						
						echo '<div class="sspi_details"><a title="Quality: ',$wh_prod["pidq"],'"><img alt="Q" src="/eos/images/star.gif" /> ',$wh_prod["pidq"],'</a></div>';
						if($wh_prod["pidn"]){
							echo '<div class="sspi_details"><a title="Quantity: ',number_format($wh_prod["pidn"], 0, '.', ',').'"><img alt="#" src="/eos/images/box.png" /> ',number_format_readable($wh_prod["pidn"]),'</a></div>';
						}else{
							echo '<div class="sspi_details"><a title="Quantity: OUT OF STOCK"><img alt="#" src="/eos/images/box.png" /> OOS</a></div>';
						}
						echo '<div class="sspi_details"><a title="Cost: $',number_format($wh_prod["pidcost"]/100, 2, '.', ',').'"><img alt="#" src="/eos/images/money.gif" /> $',number_format_readable($wh_prod["pidcost"]/100),'</a></div>';
						echo '<div class="sspi_details">';
						if($ctrl_store_price){
							echo '<input id="sales_price_',$wh_prod["id"],'" type="hidden" style="display:none;" value="',$wh_prod["pidprice"],'" size="10" maxlength="10" />
							<span style="color:#997755;font-size:18px;">$ <input id="sales_price_visible_',$wh_prod["id"],'" type="text" style="width:65px;border:2px solid #997755;" value="',($wh_prod["pidprice"]/100),'" maxlength="10" onkeyup="initUpdateSprice(',$wh_prod["id"],')" onblur="updateSprice(',$wh_prod["id"],');" /></span>';
						}else{
							echo '<span style="color:#997755;font-size:18px;">$ ',($wh_prod["pidprice"]/100),'</span>';
						}
						echo '</div>';
						echo '<div id="set_price_response_',$wh_prod["id"],'" class="sspi_details" style="line-height:24px;">&nbsp;</div>';
					echo '</div></div>';
				}
			}else{
				echo '<div style="position: relative; left: 0; top: 0;">';
					echo '<img src="/eos/images/prod/large/',$ipid_filename,'.gif" title="',$ipid_name,'" style="margin-bottom:6px;" />';
				echo '</div>';
				echo '<div>None availble.</div><br />';
			}
		?>
		</form>
	</div>
	<?php
		if($recent_sold_n_total_all){
	?>
	<div id="plot_revenue" style="float:right;height:200px;width:450px; "></div>
	<div id="plot_quality" style="float:right;height:200px;width:450px; "></div>
	<div id="plot_market_share" style="float:left;height:240px;width:670px; "></div>
	<?php
		}else{
	?>
	<div id="plot_revenue" style="float:left;height:200px;width:450px; ">Graphs not available due to lack of sales data.</div>
	<?php
		}
	?>
	<div class="clearer no_select">&nbsp;</div>
	<div class="store_sell_module_stats" style="width:668px;background-color:#ffffff;border:solid 1px #000000;font-size:14px;">
		<div style="float:left;width:315px;padding:9px;">
			<h3>15 Min. Sales:</h3>
			<?php
				if($recent_sold_n_total_15_all){
					if($recent_sold_n_total_15){
						echo '<span class="store_sell_module_stats_line">Average selling price (You):</span> $',number_format_readable($recent_sold_value_avg_15/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Average selling price (World):</span> $',number_format_readable($recent_sold_value_avg_15_all/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (You):</span> ',number_format_readable($recent_sold_n_total_15),'<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (World):</span> ',number_format_readable($recent_sold_n_total_15_all),'<br /><br />';
						
						echo '<span class="store_sell_module_stats_line">Your Revenue:</span> $',number_format_readable($recent_sold_value_total_15/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Demand Met:</span> ',number_format($ipid_demand_met*100,2,'.',','),'%<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Revenue):</span> ',number_format($recent_sold_value_market_share_15*100,2,'.',''),'%<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Count):</span> ',number_format($recent_sold_n_market_share_15*100,2,'.',''),'%<br />';
					}else{
						echo '<span class="store_sell_module_stats_line">Average selling price (You):</span> N/A<br />';
						echo '<span class="store_sell_module_stats_line">Average selling price (World):</span> $',number_format_readable($recent_sold_value_avg_15_all/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (You):</span> 0<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (World):</span> ',number_format_readable($recent_sold_n_total_15_all),'<br /><br />';
						
						echo '<span class="store_sell_module_stats_line">Your Revenue:</span> N/A<br />';
						echo '<span class="store_sell_module_stats_line">Demand Met:</span> ',number_format($ipid_demand_met*100,2,'.',','),'%<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Revenue):</span> 0.00%<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Count):</span> 0.00%<br />';
					}
				}else{
					echo '<span class="store_sell_module_stats_line">Average selling price (You):</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Average selling price (World):</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Total units sold (You):</span> 0<br />';
					echo '<span class="store_sell_module_stats_line">Total units sold (World):</span> 0<br /><br />';
					
					echo '<span class="store_sell_module_stats_line">Your Revenue:</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Demand Met:</span> ',number_format($ipid_demand_met*100,2,'.',','),'%<br />';
					echo '<span class="store_sell_module_stats_line">Your market share (Revenue):</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Your market share (Count):</span> N/A<br />';
				}
			?>
		</div>
		<div style="float:left;width:315px;padding:9px;">
			<h3>24 Hours Sales:</h3>
			<?php
				if($recent_sold_n_total_all){
					if($recent_sold_n_total){
						echo '<span class="store_sell_module_stats_line">Average selling price (You):</span> $',number_format_readable($recent_sold_value_avg/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Average selling price (World):</span> $',number_format_readable($recent_sold_value_avg_all/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (You):</span> ',number_format_readable($recent_sold_n_total),'<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (World):</span> ',number_format_readable($recent_sold_n_total_all),'<br /><br />';
						
						echo '<span class="store_sell_module_stats_line">Your Revenue:</span> $',number_format_readable($recent_sold_value_total/100),'<br />';
						echo '<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Revenue):</span> ',number_format($recent_sold_value_market_share*100,2,'.',''),'%<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Count):</span> ',number_format($recent_sold_n_market_share*100,2,'.',''),'%<br />';
					}else{
						echo '<span class="store_sell_module_stats_line">Average selling price (You):</span> N/A<br />';
						echo '<span class="store_sell_module_stats_line">Average selling price (World):</span> $',number_format_readable($recent_sold_value_avg_all/100),'<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (You):</span> 0<br />';
						echo '<span class="store_sell_module_stats_line">Total units sold (World):</span> ',number_format_readable($recent_sold_n_total_all),'<br /><br />';
						
						echo '<span class="store_sell_module_stats_line">Your Revenue:</span> N/A<br />';
						echo '<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Revenue):</span> 0.00%<br />';
						echo '<span class="store_sell_module_stats_line">Your market share (Count):</span> 0.00%<br />';
					}
				}else{
					echo '<span class="store_sell_module_stats_line">Average selling price (You):</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Average selling price (World):</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Total units sold (You):</span> 0<br />';
					echo '<span class="store_sell_module_stats_line">Total units sold (World):</span> 0<br /><br />';
					
					echo '<span class="store_sell_module_stats_line">Your Revenue:</span> N/A<br />';
					echo '<br />';
					echo '<span class="store_sell_module_stats_line">Your market share (Revenue):</span> N/A<br />';
					echo '<span class="store_sell_module_stats_line">Your market share (Count):</span> N/A<br />';
				}
			?>
		</div>
		<div class="clearer"></div>
		<div style="padding:9px;">
			* Note the market share % displayed here is used for quest purposes and does not account for unmet demand. For scaled market share % please refer to the pie chart above.
		</div>
	</div>
	<?php
		// Sales graphs
		if($recent_sold_n_total_all){
			$sql = "SELECT history_prod.history_tick, history_prod.price_avg, history_prod.q_avg, history_prod.sales_vol, IFNULL(SUM(log_sales.pidn),0) AS sales_vol_local, IFNULL(SUM(log_sales.value),0) AS sales_total_local, IFNULL(SUM(log_sales.pidn * log_sales.pidq),0) AS sales_q_sum_local FROM history_prod LEFT JOIN log_sales ON history_prod.history_tick = log_sales.tick AND history_prod.pid = log_sales.pid AND log_sales.fid = $eos_firm_id WHERE history_prod.pid = $sc_pid GROUP BY history_prod.history_tick ORDER BY history_prod.history_tick DESC LIMIT 0,49";
			$prod_sales_history = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			// Get own color
			$sql = "SELECT color FROM firms WHERE id = $eos_firm_id";
			$firm_color = $db->query($sql)->fetchColumn();
			
			// Zerofill and populate arrays
			for($i=0; $i<49; $i++){
				$hist_tick[$i] = '';
				$ss_pidn[$i] = 0;
				$ss_pidq[$i] = 0;
				$ss_pidq_avg[$i] = 0;
				$ss_revenue[$i] = 0;
				$ss_price[$i] = 0;
				$ss_price_avg[$i] = 0;
				$ss_revenue_js[$i] = 0;
				$ss_price_js[$i] = 0;
				$ss_price_avg_js[$i] = 0;
			}
			for($i=0; $i<count($prod_sales_history); $i++){
				$prod_sales_item = $prod_sales_history[$i];
				$hist_tick[(48-$i)] = $prod_sales_item["history_tick"];
				$ss_pidn[(48-$i)] = $prod_sales_item["sales_vol_local"];
				if($ss_pidn[(48-$i)]){
					$ss_pidq[(48-$i)] = $prod_sales_item["sales_q_sum_local"] / $ss_pidn[(48-$i)];
					$ss_revenue[48-$i] = $prod_sales_item["sales_total_local"];
					$ss_price[48-$i] = $ss_revenue[48-$i] / $ss_pidn[(48-$i)];
				}
				$ss_pidq_avg[48-$i] = $prod_sales_item["q_avg"];
				$ss_price_avg[48-$i] = $prod_sales_item["price_avg"];
				$ss_revenue_js[48-$i] = $ss_revenue[48-$i]/100;
				$ss_price_js[48-$i] = $ss_price[48-$i]/100;
				$ss_price_avg_js[48-$i] = $ss_price_avg[48-$i]/100;
			}
			$sql = "SELECT IFNULL(firms.id, 0) AS fid, firms.name AS firm_name, firms.color AS firm_color, a.sales_total FROM (SELECT log_sales.fid, SUM(log_sales.value) AS sales_total FROM log_sales WHERE log_sales.pid = $sc_pid AND log_sales.tick > $final_tick - 96 GROUP BY log_sales.fid ORDER BY sales_total DESC LIMIT 0, 5) AS a LEFT JOIN firms ON a.fid = firms.id";
			$market_share_results = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if($ipid_demand_met < 1){
				$pmsd_ms_multiplier = $ipid_demand_met;
				$pmsd_ms_left = 100 * $ipid_demand_met;
				$pmsd_ms_empty = 100 - $pmsd_ms_left;
			}else{
				$pmsd_ms_multiplier = 1;
				$pmsd_ms_left = 100;
				$pmsd_ms_empty = 0;
			}
			$plot_market_share_data = '[';
			$plot_market_share_colors = '[';
			foreach($market_share_results as $market_share_result){
				$pmsd_fid = $market_share_result['fid'];
				if($pmsd_fid){
					$pmsd_ms = 100 * $pmsd_ms_multiplier * $market_share_result['sales_total']/$recent_sold_value_total_all;
					$pmsd_ms_left = $pmsd_ms_left - $pmsd_ms;
					$plot_market_share_data .= '[\''.str_replace("'", "\\'", str_replace("\\", "\\\'", $market_share_result['firm_name'])).'\','.$pmsd_ms.'],';
					$plot_market_share_colors .= '"'.$market_share_result['firm_color'].'",';
				}
			}
			if($pmsd_ms_left){
				$plot_market_share_data .= '[\'Other\','.$pmsd_ms_left.'],';
				$plot_market_share_colors .= '"#909090",';
			}
			if($pmsd_ms_empty){
				$plot_market_share_data .= '[\'(Not Met)\','.$pmsd_ms_empty.']]';
				$plot_market_share_colors .= '"#f0f0f0"]';
			}else{
				$plot_market_share_data = substr($plot_market_share_data,0,-1).']';
				$plot_market_share_colors = substr($plot_market_share_colors,0,-1).']';
			}
			
			function convert_array($iarray, $num = true){
				$array_count = max(array_keys($iarray));
				if($num){
					$oarray = '[';
					for($x = 0; $x <= $array_count; $x++){
						$oarray .= (0+$iarray[$x]) . ',' ;
					}
					$oarray = substr($oarray,0,-1).'];'; 
				}else{
					$oarray = '[';
					for($x = 0; $x <= $array_count; $x++){
						$oarray .= '"'. $iarray[$x] . '",' ;
					}
					$oarray = substr($oarray,0,-1).'];'; 
				}
				return $oarray;
			}
	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			var data = <?= $plot_market_share_data ?>;
			var plotMarketShare = jQuery.jqplot('plot_market_share', [data], {
				seriesColors: <?= $plot_market_share_colors ?>,
				seriesDefaults: {
					renderer: jQuery.jqplot.PieRenderer,
					rendererOptions: {
						showDataLabels: true
					}
				},
				legend: { show:true, location: 'e', rowSpacing: '0' }
			});
			var plotRevenue = jQuery.jqplot('plot_revenue', [<?= substr(convert_array($ss_revenue_js),0,-1).','.substr(convert_array($ss_price_js),0,-1).','.substr(convert_array($ss_price_avg_js),0,-1) ?>], {
				seriesColors: ['#008000','<?= $firm_color ?>','#ee3333'],
				//title: 'Plot With Options',
				series:[{yaxis:'yaxis', label:'Revenue', showMarker: false}, {yaxis:'y2axis', label:'Our Price', showMarker: false}, {yaxis:'y2axis', label:'WASP', showMarker: false}],
				axes: {
					xaxis: {
						pad: 0
					},
					yaxis: {
						label: 'Revenue',
						autoscale: true,
						tickOptions: {
							formatString: "$%'.2f"
						},
						pad: 0,
						labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
						labelOptions: { angle: -90 }
					},
					y2axis: {
						label: 'Price',
						autoscale: true,
						tickOptions: {
							formatString: "$%'.2f"
						},
						pad: 0,
						labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
						labelOptions: { angle: 90 }
					}
				},
				legend: {
					show: true,
					location: 'sw',
					rowSpacing: '0'
				},
				highlighter: {
					show: true,
					showLabel: false,
					tooltipAxes: 'y',
					sizeAdjust: 7.5,
					tooltipLocation : 'ne'
				}
			});
			var plotQuality = jQuery.jqplot('plot_quality', [<?= substr(convert_array($ss_pidq),0,-1).','.substr(convert_array($ss_pidq_avg),0,-1) ?>], {
				seriesColors: ['<?= $firm_color ?>','#ee3333'],
				//title: 'Plot With Options',
				series:[{yaxis:'yaxis', label:'Our Quality', showMarker: false}, {yaxis:'yaxis', label:'World Quality', showMarker: false}],
				axes: {
					xaxis: {
						pad: 0
					},
					yaxis: {
						label: 'Quality',
						autoscale: true,
						tickOptions: {
							formatString: "%.6s"
						},
						labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
						labelOptions: { angle: -90 }
					}
				},
				legend: {
					show: true,
					location: 'sw',
					rowSpacing: '0'
				},
				highlighter: {
					show: true,
					showLabel: false,
					tooltipAxes: 'y',
					sizeAdjust: 7.5,
					tooltipLocation : 'ne'
				}
			});
			colorPieChartLabels(<?= $plot_market_share_colors ?>);
		});
	</script>
	<?php
		} //End sales graphs
	?>
	<div class="clearer no_select">&nbsp;</div>
</div>
<a class="jqDialog" href="stores-sell.php?fsid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>