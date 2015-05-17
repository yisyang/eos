<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_GET['frid'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = 'rnd';
if(!$bldg_id){
	fbox_breakout('buildings.php');
}

// Make sure the eos user actually owns the building
$query = $db->prepare("SELECT rnd_id AS bldg_type_id, rnd_name AS bldg_name, size, slot FROM firm_rnd WHERE id = ? AND fid = ?");
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

// Check research queue
$timenow = time();
$query = $db->prepare("SELECT * FROM queue_res WHERE fid = ? AND endtime < ?");
$query->execute(array($eos_firm_id, $timenow));
$queue_rcs = $query->fetchAll(PDO::FETCH_ASSOC);
foreach($queue_rcs as $queue_rc){
	$list_rnd_rq_id = $queue_rc["id"];
	$list_rnd_rq_pid = $queue_rc["pid"];
	$list_rnd_rq_newlevel = $queue_rc["newlevel"];
	
	// Delete from researching queue
	$query = $db->prepare("DELETE FROM queue_res WHERE id = ?");
	$query->execute(array($list_rnd_rq_id));

	// Give research level to firm, but first check whether or not the firm already has this tech
	$sql = "SELECT quality FROM firm_tech WHERE fid='$eos_firm_id' AND pid='$list_rnd_rq_pid'";
	$list_rnd_rq_oldlevel = $db->query($sql)->fetchColumn();
	if($list_rnd_rq_oldlevel){
		if($list_rnd_rq_newlevel > $list_rnd_rq_oldlevel){
			$query = $db->prepare("UPDATE firm_tech SET quality = ?, update_time = ? WHERE fid = ? AND pid = ?");
			$query->execute(array($list_rnd_rq_newlevel, $timenow, $eos_firm_id, $list_rnd_rq_pid));
		}
	}else{
		$query = $db->prepare("INSERT INTO firm_tech (fid, pid, quality, update_time) VALUES (?, ?, ?, ?)");
		$query->execute(array($eos_firm_id, $list_rnd_rq_pid, $list_rnd_rq_newlevel, $timenow));
	}
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
<?php
	// Initialize bldg image for rnd
	$sql = "SELECT name, has_image FROM list_rnd WHERE id = $bldg_type_id";
	$list_bldg = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if($list_bldg["has_image"]){
		$bldg_img_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_bldg["name"]));
	}else{
		$bldg_img_filename = "no-image";
	}

	// Check researching queue
	$sql = "SELECT queue_res.id, queue_res.pid, queue_res.newlevel, queue_res.starttime, queue_res.endtime, list_prod.name, list_prod.has_icon, list_prod.res_cost, list_prod.tech_avg FROM queue_res LEFT JOIN list_prod ON queue_res.pid = list_prod.id WHERE queue_res.frid = $bldg_id AND queue_res.endtime >= $timenow ORDER BY queue_res.starttime ASC, queue_res.id ASC";
	$results_researching = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$results_researching_count = count($results_researching);
	if($results_researching_count){
		// Populate current task status
		$result_researching = array_shift($results_researching);
		$list_rnd_res_id = $result_researching["id"];
		$list_rnd_res_pid = $result_researching["pid"];
		$list_rnd_res_pid_name = $result_researching["name"];
		if($result_researching["has_icon"]){
			$list_rnd_res_pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_rnd_res_pid_name));
		}else{
			$list_rnd_res_pid_filename = "no-icon";
		}
		$list_rnd_res_pid_res_basecost = $result_researching["res_cost"];
		$list_rnd_res_pid_tech_avg = $result_researching["tech_avg"];
		$list_rnd_res_newlevel = $result_researching["newlevel"];
		$list_rnd_res_starttime = $result_researching["starttime"];
		$list_rnd_res_endtime = $result_researching["endtime"];
		$list_rnd_res_totaltime = $list_rnd_res_endtime - $list_rnd_res_starttime;
		$timenow = time();
		$list_rnd_res_remaining = $list_rnd_res_endtime - $timenow;
		$list_rnd_res_left = min(1, $list_rnd_res_remaining / $list_rnd_res_totaltime);

		$current_task_res = '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($list_rnd_res_pid_name)).'.gif" alt="'.$list_rnd_res_pid_name.'" title="'.$list_rnd_res_pid_name.'" /> of quality '.$list_rnd_res_newlevel;
		
		$list_rnd_res_pid_res_cost = max(10000, $list_rnd_res_pid_res_basecost * pow(1.2, $list_rnd_res_newlevel - 0.25 * $list_rnd_res_pid_tech_avg));
		$list_rnd_outsource_multiplier = $list_rnd_res_left * $list_rnd_res_left * 20 + 1;
		$list_rnd_hurry_cost = $list_rnd_outsource_multiplier * $list_rnd_res_pid_res_cost;
		
		$sql = "SELECT COUNT(*) FROM queue_res WHERE fid = $eos_firm_id AND pid = $list_rnd_res_pid AND newlevel < $list_rnd_res_newlevel AND endtime > $timenow";
		$list_rnd_hurry_disabled = $db->query($sql)->fetchColumn();
	}
		
	// Initialize products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($prods as $prod){
		$prod_name[$prod["id"]] = $prod["name"];
		$prod_has_icon[$prod["id"]] = $prod["has_icon"];
	}
	
	// Initialize rnd choices
	$sql = "SELECT list_prod.* FROM list_prod LEFT JOIN list_rnd_choices ON list_prod.cat_id = list_rnd_choices.cat_id WHERE list_rnd_choices.rnd_id = '$bldg_type_id' ORDER BY list_prod.name ASC";
	$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
		<div style="float: left;padding-right: 15px;">
			<img src="/eos/images/<?= $bldg_type ?>/<?= $bldg_img_filename ?>.gif" width="180" height="80" />
		</div>
		<div style="float:left;font-size:16px;font-weight:bold;line-height:200%;">
			<div class="building_name_container"><span class="building_name" id="building_name"><?= $bldg_name.' ('.$bldg_size.' m&#178;)' ?> 
			<?php if($ctrl_rnd_sell){ ?><img src="/eos/images/edit.gif" width="24" height="24" title="Rename Building" onclick="bldgController.showNameUpdater('<?= htmlspecialchars($bldg_name) ?>',<?= $bldg_id ?>,'<?= $bldg_type ?>');" /><?php } ?></span> <a class="jqDialog" href="bldg-swap-slot.php?bldg_id=<?= $bldg_id ?>&bldg_type=<?= $bldg_type ?>"><img src="/eos/images/swap.png" width="24" height="24" title="Move Building" /></a></div>
			<?php if(!$results_researching_count){ ?>
			<a id="bldg_expand_button" style="cursor:pointer;"><img src="/eos/images/button-build.gif" title="Expand Building" alt="[Expand]" /></a> &nbsp; 
			<a id="bldg_sell_button" style="cursor:pointer;"><img src="/eos/images/button-sell.gif" title="Sell Building" alt="[Sell]" /></a>
			<?php } ?>
		</div>
<?php if($results_researching_count){ ?>
		<div class="clearer no_select"></div>
		<div class="vert_middle">
			<span style="display:inline-block;width:100px;">Researching: </span><span style="display:inline-block;min-width:140px;"><?= $current_task_res ?></span><span id="cd_div" style="display:inline-block;min-width:80px;text-align:right;">(<?= sec2hms($list_rnd_res_remaining) ?>)</span>
			<?php if($ctrl_rnd_cancel){ ?>
					 &nbsp; <a id="btn_cancel_res_cd" class="info" style="cursor:pointer;" onclick="cancelCurrentResearch()"><img src="/eos/images/x.gif" alt="[Cancel]" /><span><b>Cancel current production</b><br />When canceling research, unused research funds (between 50% to 100%) will be credited back to the company.</span></a>
			<?php } ?>
		</div>
		<?php 
			if($list_rnd_res_remaining > 15){
				echo 'Cost to outsource: <span id="cd_div2">',number_format($list_rnd_hurry_cost/100,2,'.',','),'</span><br /><br />';
			}
		?>
		<?php
			if($ctrl_rnd_hurry){
				if($list_rnd_res_remaining > 15){
					if($list_rnd_hurry_disabled){
						echo '<a class="info"><input id="btn_outsource" type="button" class="bigger_input" value="Outsource Research" disabled="disabled" /><span id="btn_outsource_text">This research depends on an another research that is currently in progress.</span></a>';
					}else{
						if($_SESSION['firm_cash'] < $list_rnd_hurry_cost){
							echo '<a class="info"><input id="btn_outsource" type="button" class="bigger_input" value="Outsource Research" disabled="disabled" /><span id="btn_outsource_text">Your company does not have enough cash to outsource this project.</span></a>';
						}else if($ctrl_leftover_allowance < $list_rnd_hurry_cost){
							echo '<a class="info"><input id="btn_outsource" type="button" class="bigger_input" value="Outsource Research" disabled="disabled" /><span id="btn_outsource_text">Cost to outsource this project exceeds your daily spending limit.</span></a>';
						}else{
							echo '<a class="info"><input id="btn_outsource" type="button" class="bigger_input" value="Outsource Research" onClick="hurryCurrentResearch();" /><span id="btn_outsource_text">You can outsource your research project to other companies for the aforementioned fee.</span></a>';
						}
					}
				}else{
					echo '<a class="info"><input id="btn_outsource" type="button" class="bigger_input" value="Outsource Research" disabled="disabled" /><span id="btn_outsource_text">This research has ended, all it takes is a little more time to document the results.</span></a>';
				}
		?>
				<a class="info"><input id="btn_cancel_res" type="button" class="bigger_input" value="Cancel Research" onClick="cancelCurrentResearch();" /><span id="btn_cancel_res_text">When canceling research, unused research funds (between 50% to 100%) will be credited back to the company.</span></a>
				<br /><br /><br />
		<?php
			}
		?>
		<?php
			if($results_researching_count > 1){
				echo '<div style="float:left;width:100px;">Queued:</div>';
				echo '<div id="queued_res_in_modal" style="float:left;width:400px;" class="vert_middle">';
				foreach($results_researching as $result_researching){
					$result_researching_id = $result_researching["id"];
					$result_researching_prod_name = $result_researching["name"];
					if($result_researching["has_icon"]){
						$result_researching_prod_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($result_researching_prod_name));
					}else{
						$result_researching_prod_filename = "no-icon";
					}
					echo '<div id="queue_item_',$result_researching_id,'"><span style="display:inline-block;min-width:140px;"><img src="/eos/images/prod/',$result_researching_prod_filename,'.gif" alt="',$result_researching_prod_name,'" title="',$result_researching_prod_name,'" /> to quality ',$result_researching["newlevel"],'</span><span style="display:inline-block;min-width:80px;text-align:right;">(',sec2hms($result_researching["endtime"] - $result_researching["starttime"]),')</span>';
					if($ctrl_rnd_cancel){
						echo ' &nbsp; <a style="cursor:pointer;" alt="Cancel" title="Cancel queued research" onclick="cancelQueue(',$result_researching_id,')"><img src="/eos/images/x.gif" /></a>';
					}
					echo '</div>';
				}
				echo '<br />Total time inc. current research: '.sec2hms($result_researching["endtime"] - time());
				echo '</div><div class="clearer no_select"></div><br />';
			}
		?>
		<script type="text/javascript">
			var cd_remaining_fbox = <?= $list_rnd_res_remaining ?>;
			var reloading_fbox = 0;

			function countdown_fbox(){
				if(typeof(document.getElementById("cd_div")) !== 'undefined' && document.getElementById("cd_div") !== null){
					cd_remaining_fbox -= 0.2;
					jQuery("#cd_div").html('(' + sec2hms(cd_remaining_fbox) + ')');
					if(cd_remaining_fbox > 15){
						jQuery("#cd_div2").html(estOSCost(cd_remaining_fbox, <?= $list_rnd_res_totaltime ?>));
					}else{
						jQuery("#cd_div2").html('Research ended. Documenting results...');
						jQuery("#btn_outsource").prop("disabled", true);
						jQuery("#btn_outsource_text").html('This research has ended, all it takes is a little more time to document the results.');
						jQuery("#btn_cancel_res").prop("disabled", true);
						jQuery("#btn_cancel_res_text").html('This research has ended, all it takes is a little more time to document the results.');
						jQuery("#btn_cancel_res_cd").hide();

						if(cd_remaining_fbox <= 0){
							if(!reloading_fbox){
								reloading_fbox = 1;
								clearInterval(modalController.modalCdt);
								jQuery("#cd_div").html('<a class="jqDialog" href="rnd-res.php?frid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Completed - Click to Refresh" /></a>');
								return;
							}
						}
					}
				}else{
					clearInterval(modalController.modalCdt);
				}
			}
			function estOSCost(remaining, total){
				var left = Math.min(1, remaining/total);
				var multiplier = left * left * 20 + 1;
				var cost = multiplier * <?= $list_rnd_res_pid_res_cost ?>;
				if(remaining > 15){
					return '$' + formatNum(cost/100, 2);
				}
			}
			function hurryCurrentResearch(){
				jqDialogInit("rnd-res-hurry.php", {
					frid : <?= $bldg_id ?>,
					rnd_res_id : <?= $list_rnd_res_id ?>
				});
			}
			function cancelCurrentResearch(){
				jConfirm('Cancel this research project and all of its dependent projects?', 'Cancel Research', function(conf){
					if(conf){
						jqDialogInit('rnd-res-cancel.php', {
							frid : <?= $bldg_id ?>,
							rnd_res_id : <?= $list_rnd_res_id ?>
						});
					}
				});
			}
			function cancelQueue(queue_id){
				progressController.cancelQueue('rnd', queue_id, function(){
					progressController.refreshQueue('rnd', function(resp){
						// Transfer usable progress items
						var queue_res = new Array();
						var qr_count = resp.queue_res.length;
						for(var i = 0; i < qr_count; i++){
							if(resp.queue_res[i].frid == <?= $bldg_id ?>){
								queue_res.push(resp.queue_res[i]);
							}
						}
						var qr_count = queue_res.length;
						if(qr_count > 1){
							var queue_res_list = '';
							for(var i = 1; i < qr_count; i++){
								var queue_res_item = queue_res[i];
								if(queue_res_item.has_icon){
									var filename = queue_res_item.name.toLowerCase().replace(/[\s\&\']/g, '_').replace(/_{2,}/g, '_');
								}else{
									var filename = 'no-icon';
								}
								queue_res_list += '<div id="queue_item_' + queue_res_item.id + '"><span style="display:inline-block;min-width:140px;"><img src="/eos/images/prod/' + filename + '.gif" alt="' + queue_res_item.name + '" title="' + queue_res_item.name + '" /> to quality ' + queue_res_item.newlevel + '</span><span style="display:inline-block;min-width:80px;text-align:right;">(' + sec2hms(queue_res_item.endtime - queue_res_item.starttime) + ')</span>';
								if(<?= $ctrl_rnd_cancel ?> != "0"){
									queue_res_list += ' &nbsp; <a style="cursor:pointer;" alt="Cancel" title="Cancel queued research" onclick="cancelQueue(' + queue_res_item.id + ')"><img src="/eos/images/x.gif" /></a>';
								}
								queue_res_list += '</div>';
							}
							queue_res_list += '<br />Total time inc. current research: ' + sec2hms(queue_res_item.endtime - <?= time() ?>);
						}else{
							var queue_res_list = 'None';
						}
						jQuery("#queued_res_in_modal").html(queue_res_list);
					});
					firmController.getCash();
				});
			}

			if(typeof(modalController.modalCdt) !== 'undefined' && modalController.modalCdt) clearInterval(modalController.modalCdt);
			modalController.modalCdt = setInterval("countdown_fbox()", 200);
		</script>
<?php
	} // End if results_researching_count
?>
	<script type="text/javascript">
		modalController.backLink = 'rnd-res.php?frid=<?= $bldg_id ?>';
		modalController.backLinkTitle = 'Back to R&amp;D';

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
	if(!$ctrl_rnd_res){
		echo '<div class="tbox_inline clearer">You are not authorized to start research projects.</div>';
	}else{
		if($results_researching_count){
			echo '<h3>Add to Queue</h3>';
			echo 'You may add additional research into the research queue.<br /><br />';
		}
		if($settings_narrow_screen){
			$j_per_row = 5;
		}else{
			$j_per_row = 6;
		}
		$j = $j_per_row;
		$rnd_choices_remaining = count($rnd_choices);
		$query_tech_exists = $db->prepare("SELECT COUNT(*) FROM firm_tech WHERE fid = :eos_firm_id AND pid = :pid AND quality > 0");
		foreach($rnd_choices as $rnd_choice){
			if($j == $j_per_row){
				$j = 0;
				echo '<div class="prod_choices">';
			}
			$j++;
			$rnd_choices_remaining--;
			$rc_pid = $rnd_choice["id"];
			$rc_name = $rnd_choice["name"];
			$rc_tech_avg = $rnd_choice["tech_avg"];
			$rc_has_icon = $rnd_choice["has_icon"];
			$rc_is_disabled = 0;
			$rc_is_new = 1;
			$rc_cur_quality = 0;
			$rc_next_quality = 1;

			// Check that the rc_pid is not already being researched in queue_res for the fid
			$sql = "SELECT frid, newlevel FROM queue_res WHERE fid = '$eos_firm_id' AND pid = '$rc_pid' ORDER BY newlevel DESC";
			$rc_existing_queue = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			
			// Check current quality from firm_tech
			$sql = "SELECT quality FROM firm_tech WHERE fid = '$eos_firm_id' AND pid = '$rc_pid'";
			$firm_tech = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

			// Assume whatever is in queue is higher than firm tech
			if(!empty($rc_existing_queue)){
				$rc_is_new = 0;
				if($rc_existing_queue['frid'] != $bldg_id){
					$rc_is_disabled = 1;
					$rc_is_disabled_msg = 'This research is queued at another building.';
				}
				$rc_cur_quality = $rc_existing_queue['newlevel'];
				$rc_next_quality = $rc_cur_quality + 1;
				$rc_cost = max(10000, $rnd_choice["res_cost"] * pow(1.2, $rc_next_quality - 0.25 * $rc_tech_avg));
				$rc_restime = 1000/$bldg_size * pow(max(1, $rc_next_quality - 0.25 * $rc_tech_avg),3);
			}else if(!empty($firm_tech)){
				$rc_is_new = 0;
				$rc_cur_quality = $firm_tech["quality"];
				$rc_next_quality = $rc_cur_quality + 1;
				$rc_cost = max(10000, $rnd_choice["res_cost"] * pow(1.2, $rc_next_quality - 0.25 * $rc_tech_avg));
				$rc_restime = 1000/$bldg_size * pow(max(1, $rc_next_quality - 0.25 * $rc_tech_avg),3);
			}else{
				$rc_cost = max(10000, $rnd_choice["res_cost"] * pow(1.2, 1 - 0.25 * $rc_tech_avg));
				$rc_restime = 1000/$bldg_size;
				
				// Check research dependencies, and provide message if they are not met
				$rc_res_dep_1 = $rnd_choice["res_dep_1"];
				$rc_res_dep_2 = $rnd_choice["res_dep_2"];
				$rc_res_dep_3 = $rnd_choice["res_dep_3"];
				if($rc_res_dep_1 || $rc_res_dep_2 || $rc_res_dep_3){
					$rc_is_disabled_msg = 'You must first discover the following products:<br />';
					if($rc_res_dep_1){
						$query_tech_exists->execute(array(':eos_firm_id' => $eos_firm_id, ':pid' => $rc_res_dep_1));
						if(!$query_tech_exists->fetchColumn()){
							$rc_is_disabled = 1;
							if($prod_has_icon[$rc_res_dep_1]){
								$rc_is_disabled_msg .= '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$rc_res_dep_1])).'.gif" /> '.$prod_name[$rc_res_dep_1].'<br />';
							}else{
								$rc_is_disabled_msg .= '<img src="/eos/images/prod/no-icon.gif" /> '.$prod_name[$rc_res_dep_1].'<br />';
							}
						}
					}
					if($rc_res_dep_2){
						$query_tech_exists->execute(array(':eos_firm_id' => $eos_firm_id, ':pid' => $rc_res_dep_2));
						if(!$query_tech_exists->fetchColumn()){
							$rc_is_disabled = 1;
							if($prod_has_icon[$rc_res_dep_2]){
								$rc_is_disabled_msg .= '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$rc_res_dep_2])).'.gif" /> '.$prod_name[$rc_res_dep_2].'<br />';
							}else{
								$rc_is_disabled_msg .= '<img src="/eos/images/prod/no-icon.gif" /> '.$prod_name[$rc_res_dep_2].'<br />';
							}
						}
					}
					if($rc_res_dep_3){
						$query_tech_exists->execute(array(':eos_firm_id' => $eos_firm_id, ':pid' => $rc_res_dep_3));
						if(!$query_tech_exists->fetchColumn()){
							$rc_is_disabled = 1;
							if($prod_has_icon[$rc_res_dep_3]){
								$rc_is_disabled_msg .= '<img src="/eos/images/prod/'.preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$rc_res_dep_3])).'.gif" /> '.$prod_name[$rc_res_dep_3].'<br />';
							}else{
								$rc_is_disabled_msg .= '<img src="/eos/images/prod/no-icon.gif" /> '.$prod_name[$rc_res_dep_3].'<br />';
							}
						}
					}
				}
			}
			if(!$rc_is_disabled){
				if($_SESSION['firm_cash'] < $rc_cost){
					$rc_is_disabled = 1;
					$rc_is_disabled_msg = 'You do not have enough cash to start this research.<br />';
				}else if($ctrl_leftover_allowance < $rc_cost){
					$rc_is_disabled = 1;
					$rc_is_disabled_msg = 'Research cost exceeds your daily spending limit.<br />';
				}
			}
			
			if($rc_has_icon){
				$rc_pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($rc_name));
			}else{
				$rc_pid_filename = "no-icon";
			}
			echo '<div class="prod_choices_item"><div style="position: relative; left: 0; top: 0;">';
			if($rc_is_disabled){
				echo '<div style="position:absolute;left:0;top:0;width:96px;height:96px;"><a class="info"><img src="/eos/images/prod/large/not-available.png" /><span><font color="#ff0000">Cannot research '.$rc_name.'.<br />'.$rc_is_disabled_msg.'</font></span></a></div>';
				echo '<img src="/eos/images/prod/large/'.$rc_pid_filename.'.gif" title="'.$rc_name.'" style="margin-bottom:6px;" /><div style="position:absolute;top:0;left:72px;"><a class="jqDialog" href="pedia-product-view.php?pid='.$rc_pid.'&frid='.$bldg_id.'" title="View on EOS-Pedia"><img src="images/pedia.png" title="View on EOS-Pedia" /></a></div><br />';
			}else{
				if($ctrl_rnd_res){
					echo '<img class="jqDialog" href="rnd-res-start.php" params="frid='.$bldg_id.'&rc_pid='.$rc_pid.'" src="/eos/images/prod/large/'.$rc_pid_filename.'.gif" title="'.$rc_name.'" style="margin-bottom:6px;" /><div style="position:absolute;top:0;left:72px;"><a class="jqDialog" href="pedia-product-view.php?pid='.$rc_pid.'&frid='.$bldg_id.'" title="View on EOS-Pedia"><img src="images/pedia.png" title="View on EOS-Pedia" /></a></div><br />';
				}else{
					echo '<img src="/eos/images/prod/large/'.$rc_pid_filename.'.gif" title="'.$rc_name.'" style="margin-bottom:6px;" /><div style="position:absolute;top:0;left:72px;"><a class="jqDialog" href="pedia-product-view.php?pid='.$rc_pid.'&frid='.$bldg_id.'" title="View on EOS-Pedia"><img src="images/pedia.png" title="View on EOS-Pedia" /></a></div><br />';
				}
			}
			echo '<img src="/eos/images/star.gif" alt="Quality" title="Quality" /> '.$rc_next_quality.'<br />';
			echo '<img src="/eos/images/time.gif" alt="Time" title="Time" /> '.sec2hms($rc_restime).'<br />';
			echo '<img src="/eos/images/money.gif" alt="Cash" title="Cash" /> $'.number_format($rc_cost/100, 0, '.', ',').'<br />';
			echo '</div></div>';
			
			if($j == $j_per_row || !$rnd_choices_remaining){
				echo '</div>';
			}
		}
	}
?>
		<div style="clear:both;">&nbsp;</div><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />	
<?php require 'include/foot_fbox.php'; ?>