<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_GET['ffid'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = 'fact';
if(!$bldg_id){
	fbox_breakout('buildings.php');
}

// Make sure the eos user actually owns the building
$query = $db->prepare("SELECT fact_id AS bldg_type_id, fact_name AS bldg_name, size, slot FROM firm_fact WHERE id = ? AND fid = ?");
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

// and that it is not under construction
$sql = "SELECT COUNT(*) FROM queue_build WHERE building_type = '$bldg_type' AND building_id = '$bldg_id'";
$count = $db->query($sql)->fetchColumn();
if($count){
	fbox_redirect('bldg-expand-status.php?type='.$bldg_type.'&id='.$bldg_id);
}

// Check production queue, move anything completed to firm warehouse
$timenow = time();
$query = $db->prepare("SELECT * FROM queue_prod WHERE ffid = ? AND endtime < ?");
$query->execute(array($bldg_id, $timenow));
$queue_pcs = $query->fetchAll(PDO::FETCH_ASSOC);
foreach($queue_pcs as $queue_pc){
	$list_fact_pc_id = $queue_pc["id"];
	$list_fact_pc_opid1 = $queue_pc["opid1"];
	$list_fact_pc_opid1_q = $queue_pc["opid1q"];
	$list_fact_pc_opid1_n = $queue_pc["opid1n"];
	$list_fact_pc_opid1_cost = $queue_pc["opid1cost"];
	$query = $db->prepare("DELETE FROM queue_prod WHERE id = ?");
	$query->execute(array($list_fact_pc_id));
	// Check if pid with pidq already exists in warehouse
	$query = $db->prepare("SELECT COUNT(*) AS cnt, id, pidn, pidq, pidcost FROM firm_wh WHERE pid = ? AND fid = ?");
	$query->execute(array($list_fact_pc_opid1, $eos_firm_id));
	$wh_prod = $query->fetch(PDO::FETCH_ASSOC);
	if($wh_prod["cnt"]){
		// Update warehouse
		$list_fact_pc_opid1_wh_id = $wh_prod["id"];
		$list_fact_pc_opid1_wh_n = $wh_prod["pidn"];
		$list_fact_pc_opid1_wh_q = $wh_prod["pidq"];
		$list_fact_pc_opid1_wh_cost = $wh_prod["pidcost"];
		$list_fact_pc_opid1_n_new = $list_fact_pc_opid1_wh_n + $list_fact_pc_opid1_n;
		$list_fact_pc_opid1_q_new = ($list_fact_pc_opid1_wh_n * $list_fact_pc_opid1_wh_q + $list_fact_pc_opid1_n * $list_fact_pc_opid1_q)/$list_fact_pc_opid1_n_new;
		$list_fact_pc_opid1_cost_new = round(($list_fact_pc_opid1_wh_n * $list_fact_pc_opid1_wh_cost + $list_fact_pc_opid1_n * $list_fact_pc_opid1_cost)/$list_fact_pc_opid1_n_new);
		$query = $db->prepare("UPDATE firm_wh SET pidcost = ?, pidn = ?, pidq = ? WHERE id = ?");
		$query->execute(array($list_fact_pc_opid1_cost_new, $list_fact_pc_opid1_n_new, $list_fact_pc_opid1_q_new, $list_fact_pc_opid1_wh_id));
	}else{
		// Insert into warehouse
		$query = $db->prepare("INSERT INTO firm_wh (fid, pid, pidq, pidn, pidcost) VALUES (?, ?, ?, ?, ?)");
		$query->execute(array($eos_firm_id, $list_fact_pc_opid1, $list_fact_pc_opid1_q, $list_fact_pc_opid1_n, $list_fact_pc_opid1_cost));
	}
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	// Initialize bldg image for fact
	$sql = "SELECT name, has_image FROM list_fact WHERE id = $bldg_type_id";
	$list_bldg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($list_bldg["has_image"]){
		$bldg_img_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_bldg["name"]));
	}else{
		$bldg_img_filename = "no-image";
	}

	// Then check if the ffid is producing stuff
	$sql = "SELECT queue_prod.id, queue_prod.opid1, queue_prod.opid1q, queue_prod.opid1n, queue_prod.starttime, queue_prod.endtime, firm_fact.fact_name, list_prod.name, list_prod.has_icon FROM queue_prod LEFT JOIN list_prod ON queue_prod.opid1 = list_prod.id LEFT JOIN firm_fact ON queue_prod.ffid = firm_fact.id WHERE queue_prod.fid = $eos_firm_id AND queue_prod.ffid = $bldg_id AND queue_prod.endtime >= $timenow ORDER BY queue_prod.starttime ASC";
	$results_producing = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$results_producing_count = count($results_producing);
	if($results_producing_count){
		// Populate current task status
		$result_producing = array_shift($results_producing);
		$list_fact_prod_id = $result_producing["id"];
		$list_fact_prod_opid1 = $result_producing["opid1"];
		$list_fact_prod_opid1_name = $result_producing["name"];
		$list_fact_prod_opid1_has_icon = $result_producing["has_icon"];
		if($list_fact_prod_opid1_has_icon){
			$list_fact_prod_opid1_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_fact_prod_opid1_name));
		}else{
			$list_fact_prod_opid1_filename = "no-icon";
		}
		$list_fact_prod_opid1_q = $result_producing["opid1q"];
		$list_fact_prod_opid1_n = $result_producing["opid1n"];
		$list_fact_prod_starttime = $result_producing["starttime"];
		$list_fact_prod_endtime = $result_producing["endtime"];
		$list_fact_prod_totaltime = $list_fact_prod_endtime - $list_fact_prod_starttime;
		$timenow = time();
		$list_fact_prod_remaining = $list_fact_prod_endtime - $timenow;
		$list_fact_prod_already_produced = floor($list_fact_prod_opid1_n * ($timenow - $list_fact_prod_starttime)/$list_fact_prod_totaltime);
	}

	// Initialize products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($prods as $prod){
		$prod_name[$prod["id"]] = $prod["name"];
		$prod_has_icon[$prod["id"]] = $prod["has_icon"];
	}
	
	// Initialize fact choices:
	$sql = "SELECT list_fact_choices.*, list_prod.value, IFNULL(firm_tech.quality, 0) AS quality FROM list_fact_choices LEFT JOIN list_prod ON list_fact_choices.opid1=list_prod.id LEFT JOIN firm_tech ON firm_tech.fid = $eos_firm_id AND list_fact_choices.opid1 = firm_tech.pid WHERE list_fact_choices.fact_id = $bldg_type_id ORDER BY list_prod.name ASC";
	$fact_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
		<div style="float:left;padding-right:15px;">
			<img src="/eos/images/<?= $bldg_type ?>/<?= $bldg_img_filename ?>.gif" width="180" height="80" />
		</div>
		<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;padding-right:15px;">
			<div class="building_name_container"><span class="building_name" id="building_name"><?= $bldg_name.' ('.$bldg_size.' m&#178;)' ?> 
			<?php if($ctrl_fact_sell){ ?><img src="/eos/images/edit.gif" width="24" height="24" title="Rename Building" onclick="bldgController.showNameUpdater('<?= htmlspecialchars($bldg_name) ?>',<?= $bldg_id ?>,'<?= $bldg_type ?>');" /><?php } ?></span> <a class="jqDialog" href="bldg-swap-slot.php?bldg_id=<?= $bldg_id ?>&bldg_type=<?= $bldg_type ?>"><img src="/eos/images/swap.png" width="24" height="24" title="Move Building" /></a></div>
			<?php if(!$results_producing_count){ ?>
			<a id="bldg_expand_button" style="cursor:pointer;"><img src="/eos/images/button-build.gif" title="Expand Building" alt="[Expand]" /></a> &nbsp; 
			<a id="bldg_sell_button" style="cursor:pointer;"><img src="/eos/images/button-sell.gif" title="Sell Building" alt="[Sell]" /></a>
			<?php } ?> &nbsp; 
			<a href="/eos/market-requests.php?view_type=fact&view_type_id=<?= $bldg_type_id ?>"><img src="/eos/images/b2b_req_fact.gif" title="View Product Requests" alt="[B2B]" /></a>
		</div>
<?php
	if($ctrl_fact_produce){
		$sql = "SELECT fcid, opid1, opid1q, opid1n FROM log_queue_prod WHERE fid = $eos_firm_id AND ffid = $bldg_id ORDER BY starttime DESC LIMIT 0, 3";
		$recent_prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($recent_prods)){
			echo '<div class="vert_middle" style="float:right;font-size:16px;line-height:200%;">';
			echo '<b>Recent:</b><br />';
			foreach($recent_prods as $recent_prod){
				if($prod_has_icon[$recent_prod["opid1"]]){
					$prod_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$recent_prod["opid1"]]));
				}else{
					$prod_filename = "no-icon";
				}
				echo number_format_readable($recent_prod["opid1n"]).' <img src="/eos/images/prod/'.$prod_filename.'.gif" title="Repeat Production" /> (Q'.floor($recent_prod["opid1q"]).') <a class="jqDialog" href="factories-production-confirm.php" params="ffid='.$bldg_id.'&fcid='.$recent_prod["fcid"].'&pnum='.$recent_prod["opid1n"].'"><input type="button" class="bigger_input" value="Repeat" /></a><br />';
			}
			echo '</div>';
		}
	}
?>
<?php if($results_producing_count){ ?>
		<div class="clearer no_select"></div>
		<div class="vert_middle">
			<span style="display:inline-block;width:100px;">Producing: </span><span style="display:inline-block;min-width:80px;text-align:right;"><?= $list_fact_prod_opid1_n ?></span> <span style="display:inline-block;min-width:140px;"><img src="/eos/images/prod/<?= $list_fact_prod_opid1_filename ?>.gif" alt="<?= $list_fact_prod_opid1_name ?>" title="<?= $list_fact_prod_opid1_name ?>" /> of quality <?= $list_fact_prod_opid1_q ?></span><span id="cd_div" style="display:inline-block;min-width:80px;text-align:right;">(<?= sec2hms($list_fact_prod_remaining) ?>)</span>
		<?php if($ctrl_fact_cancel){ ?>
				 &nbsp; <a class="info" style="cursor:pointer;"><img src="/eos/images/x.gif" alt="[Cancel]" onClick="cancelCurrentProduction();" /><span><b>Cancel current production</b><br />When canceling production, units already produced will be transferred to your warehouse. Any unused materials (whole units) will also be transferred back.</span></a>
		<?php } ?>
		</div>
			<span id="cd_div2" style="display:inline-block;min-width:180px;text-align:right;">&#8776; <?= $list_fact_prod_already_produced ?></span> units produced<br /><br />
		<?php
			if($results_producing_count > 1){
				echo '<div style="float:left;width:100px;">Queued:</div>';
				echo '<div id="queued_prod_in_modal" style="float:left;width:400px;" class="vert_middle">';
				foreach($results_producing as $result_producing){
					$result_producing_id = $result_producing["id"];
					$result_producing_prod_name = $result_producing["name"];
					if($result_producing["has_icon"]){
						$result_producing_prod_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($result_producing_prod_name));
					}else{
						$result_producing_prod_filename = "no-icon";
					}
					echo '<div id="queue_item_',$result_producing_id,'"><span style="display:inline-block;min-width:80px;text-align:right;">',$result_producing["opid1n"],'</span> <span style="display:inline-block;min-width:140px;"><img src="/eos/images/prod/',$result_producing_prod_filename,'.gif" alt="',$result_producing_prod_name,'" title="',$result_producing_prod_name,'" /> of quality ',$result_producing["opid1q"],'</span><span style="display:inline-block;min-width:80px;text-align:right;">(',sec2hms($result_producing["endtime"] - $result_producing["starttime"]),')</span>';
					if($ctrl_fact_cancel){
						echo ' &nbsp; <a style="cursor:pointer;" alt="Cancel" title="Cancel queued production" onclick="cancelQueue(',$result_producing_id,')"><img src="/eos/images/x.gif" /></a>';
					}
					echo '</div>';
				}
				echo '<br />Total time inc. current production: '.sec2hms($result_producing["endtime"] - time());
				echo '</div><div class="clearer no_select"></div><br />';
			}
		?>
		<script type="text/javascript">
			var cd_remaining_fbox = <?= $list_fact_prod_remaining ?>;
			var reloading_fbox = 0;

			function countdown_fbox(){
				if(typeof(document.getElementById("cd_div")) !== 'undefined' && document.getElementById("cd_div") !== null){
					cd_remaining_fbox -= 0.2;
					jQuery("#cd_div").html('(' + sec2hms(cd_remaining_fbox) + ')');
					jQuery("#cd_div2").html('&#8776; ' + unitscomp(cd_remaining_fbox, <?= $list_fact_prod_totaltime ?>));
					if(cd_remaining_fbox <= 0){
						if(!reloading_fbox){
							reloading_fbox = 1;
							clearInterval(modalController.modalCdt);
							setTimeout(function(){
								jqDialogInit('factories-production.php?ffid=<?= $bldg_id ?>');
							}, 1500);
							return;
						}
					}
				}else{
					clearInterval(modalController.modalCdt);
				}
			}
			function unitscomp(remaining, total){
				var uc;
				if(remaining < 0){
					return "<?= $list_fact_prod_opid1_n ?>";
				}
				uc = <?= $list_fact_prod_opid1_n ?>*(1 - (remaining/total));
				uc = Math.floor(uc);
				return uc;
			}
			function cancelCurrentProduction(){
				jConfirm('Cancel production?', 'Cancel Production', function(conf){
					if(conf){
						jqDialogInit('factories-production-cancel.php', {
							ffid : <?= $bldg_id ?>,
							fpid : <?= $list_fact_prod_id ?>
						});
					}
				});
			}
			function cancelQueue(queue_id){
				progressController.cancelQueue('fact', queue_id, function(){
					progressController.refreshQueue('fact', function(resp){
						// Transfer usable progress items
						var queue_prod = new Array();
						var qp_count = resp.queue_prod.length;
						for(var i = 0; i < qp_count; i++){
							if(resp.queue_prod[i].ffid == <?= $bldg_id ?>){
								queue_prod.push(resp.queue_prod[i]);
							}
						}
						var qp_count = queue_prod.length;
						if(qp_count > 1){
							var queue_prod_list = '';
							for(var i = 1; i < qp_count; i++){
								var queue_prod_item = queue_prod[i];
								if(queue_prod_item.has_icon){
									var filename = queue_prod_item.name.toLowerCase().replace(/[\s\&\']/g, '_').replace(/_{2,}/g, '_');
								}else{
									var filename = 'no-icon';
								}
								queue_prod_list += '<div id="queue_item_' + queue_prod_item.id + '"><span style="display:inline-block;min-width:80px;text-align:right;">' + queue_prod_item.opid1n + '</span> <span style="display:inline-block;min-width:140px;"><img src="/eos/images/prod/' + filename + '.gif" alt="' + queue_prod_item.name + '" title="' + queue_prod_item.name + '" /> of quality ' + queue_prod_item.opid1q + '</span><span style="display:inline-block;min-width:80px;text-align:right;">(' + sec2hms(queue_prod_item.endtime - queue_prod_item.starttime) + ')</span>';
								if(<?= $ctrl_fact_cancel ?> != "0"){
									queue_prod_list += ' &nbsp; <a style="cursor:pointer;" alt="Cancel" title="Cancel queued production" onclick="cancelQueue(' + queue_prod_item.id + ')"><img src="/eos/images/x.gif" /></a>';
								}
								queue_prod_list += '</div>';
							}
							queue_prod_list += '<br />Total time inc. current production: ' + sec2hms(queue_prod_item.endtime - <?= time() ?>);
						}else{
							var queue_prod_list = 'None';
						}
						jQuery("#queued_prod_in_modal").html(queue_prod_list);
					});
					firmController.getCash();
				});
			}

			if(typeof(modalController.modalCdt) !== 'undefined' && modalController.modalCdt) clearInterval(modalController.modalCdt);
			modalController.modalCdt = setInterval("countdown_fbox()", 200);
		</script>
<?php
	} // End if results_producing_count
?>
	<script type="text/javascript">
		modalController.backLink = 'factories-production.php?ffid=<?= $bldg_id ?>';
		modalController.backLinkTitle = 'Back to Factory';
		
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
		});
	</script>
<?php
	if(!$ctrl_fact_produce){
		echo '<div class="tbox_inline clearer">You are not authorized to give production orders.</div>';
	}else{
		if($results_producing_count){
			echo '<h3>Add to Queue</h3>';
			echo 'You may add up to 168 hours worth of production into the production queue.<br /><br />';
		}
		if($settings_narrow_screen){
			$j_per_row = 5;
		}else{
			$j_per_row = 6;
		}
		$j = $j_per_row;
		$fact_choices_remaining = count($fact_choices);
		foreach($fact_choices as $fact_choice){
			if($j == $j_per_row){
				$j = 0;
				echo '<div class="prod_choices">';
			}
			$j++;
			$fact_choices_remaining--;
			$fc_timecost_display = sec2hms(max(1, $fact_choice["timecost"] * 10/$bldg_size));
			$fc_ipid1n = $fact_choice["ipid1n"]+0; //+0 is used to remove insignificant decimal pts but keep others
			$fc_ipid2n = $fact_choice["ipid2n"]+0;
			$fc_ipid3n = $fact_choice["ipid3n"]+0;
			$fc_ipid4n = $fact_choice["ipid4n"]+0;

			if($prod_has_icon[$fact_choice["opid1"]]){
				$prod_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fact_choice["opid1"]]));
			}else{
				$prod_filename = "no-icon";
			}
			echo '<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;">';
			echo '<img class="jqDialog" href="factories-production-confirm.php" params="ffid='.$bldg_id.'&fcid='.$fact_choice["id"].'" src="/eos/images/prod/large/'.$prod_filename.'.gif" title="Start Producing '.$prod_name[$fact_choice["opid1"]].'" style="margin-bottom:6px;" /><img class="jqDialog" style="position:absolute;top:0;left:72px;" href="pedia-product-view.php?pid='.$fact_choice["opid1"].'&ffid='.$bldg_id.'" src="images/pedia.png" title="View on EOS-Pedia" /><br />';
			echo '<img src="/eos/images/star.gif" alt="Tech" title="Tech" /> '.$fact_choice["quality"].'<br />';
			echo '<img src="/eos/images/time.gif" alt="Time" title="Time" /> '.$fc_timecost_display.'<br />';
			echo '<img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $'.number_format_readable($fact_choice["cost"]/100).'<br />';
			if($fact_choice["ipid1"]){
				if($prod_has_icon[$fact_choice["ipid1"]]){
					echo '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fact_choice["ipid1"]])).'.gif" alt="'.$prod_name[$fact_choice["ipid1"]].'" title="'.$prod_name[$fact_choice["ipid1"]].'" /> '.number_format_readable($fc_ipid1n).'<br />';
				}else{
					echo '<img src="/eos/images/prod/no-icon.gif" alt="'.$prod_name[$fact_choice["ipid1"]].'" title="'.$prod_name[$fact_choice["ipid1"]].'" /> '.number_format_readable($fc_ipid1n).'<br />';
				}
				if($fact_choice["ipid2"]){
					if($prod_has_icon[$fact_choice["ipid2"]]){
						echo '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fact_choice["ipid2"]])).'.gif" alt="'.$prod_name[$fact_choice["ipid2"]].'" title="'.$prod_name[$fact_choice["ipid2"]].'" /> '.number_format_readable($fc_ipid2n).'<br />';
					}else{
						echo '<img src="/eos/images/prod/no-icon.gif" alt="'.$prod_name[$fact_choice["ipid2"]].'" title="'.$prod_name[$fact_choice["ipid2"]].'" /> '.number_format_readable($fc_ipid2n).'<br />';
					}
					if($fact_choice["ipid3"]){
						if($prod_has_icon[$fact_choice["ipid3"]]){
							echo '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fact_choice["ipid3"]])).'.gif" alt="'.$prod_name[$fact_choice["ipid3"]].'" title="'.$prod_name[$fact_choice["ipid3"]].'" /> '.number_format_readable($fc_ipid3n).'<br />';
						}else{
							echo '<img src="/eos/images/prod/no-icon.gif" alt="'.$prod_name[$fact_choice["ipid3"]].'" title="'.$prod_name[$fact_choice["ipid3"]].'" /> '.number_format_readable($fc_ipid3n).'<br />';
						}
						if($fact_choice["ipid4"]){
							if($prod_has_icon[$fact_choice["ipid4"]]){
								echo '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fact_choice["ipid4"]])).'.gif" alt="'.$prod_name[$fact_choice["ipid4"]].'" title="'.$prod_name[$fact_choice["ipid4"]].'" /> '.number_format_readable($fc_ipid4n);
							}else{
								echo '<img src="/eos/images/prod/no-icon.gif" alt="'.$prod_name[$fact_choice["ipid4"]].'" title="'.$prod_name[$fact_choice["ipid4"]].'" /> '.number_format_readable($fc_ipid4n);
							}
						}
					}
				}
			}
			
			echo '</div></div>';
			
			if($j == $j_per_row || !$fact_choices_remaining){
				echo '</div>';
			}
		}
	}
?>
		<div style="clear:both;">&nbsp;</div><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>