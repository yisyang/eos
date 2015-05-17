<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_slot = filter_var($_GET['slot'], FILTER_SANITIZE_NUMBER_INT);
if(!$bldg_slot || $bldg_slot < 1){
	fbox_breakout('buildings.php');
}

// Check if there is already a building on the given slot
$query = $db->prepare("SELECT COUNT(*) AS cnt, building_type FROM queue_build WHERE fid = ? AND building_slot = ?");
$query->execute(array($eos_firm_id, $bldg_slot));
$result = $query->fetch(PDO::FETCH_ASSOC);
if($result['cnt']){
	fbox_redirect('bldg-expand-status.php?type='.$result['building_type'].'&slot='.$bldg_slot);
}
$sql = "(SELECT id, 'fact' AS bldg_type FROM firm_fact WHERE fid = :eos_firm_id AND slot = :slot) UNION (SELECT id, 'store' AS bldg_type FROM firm_store WHERE fid = :eos_firm_id AND slot = :slot) UNION (SELECT id, 'rnd' AS bldg_type FROM firm_rnd WHERE fid = :eos_firm_id AND slot = :slot)";
$query_get_bldg_id = $db->prepare($sql);
$query_get_bldg_id->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $bldg_slot));
$firm_bldg = $query_get_bldg_id->fetch(PDO::FETCH_ASSOC);
if(!empty($firm_bldg)){
	$bldg_id = $firm_bldg['id'];
	$bldg_type = $firm_bldg['bldg_type'];
	if($bldg_type == 'fact'){
		$bldg_activity_url = 'factories-production.php?ffid=';
	}else if($bldg_type == 'store'){
		$bldg_activity_url = 'stores-sell.php?fsid=';
	}else if($bldg_type == 'rnd'){
		$bldg_activity_url = 'rnd-res.php?frid=';
	}else{
		fbox_breakout('buildings.php');
	}
	fbox_redirect($bldg_activity_url.$bldg_id);
}

// Find out the player's max buildings 
$query = $db->prepare("SELECT max_bldg FROM firms WHERE id = ?");
$query->execute(array($eos_firm_id));
$max_bldg = $query->fetchColumn();
if($max_bldg < $bldg_slot){
	fbox_breakout('buildings.php');
}
?>
<?php require 'include/functions.php'; ?>
		<script type="text/javascript">
			function build_confirm(bldg_type, bldg_type_id){
				jqDialogInit('bldg-build-start.php', {
					bldg_type : bldg_type,
					bldg_type_id : bldg_type_id,
					slot : '<?= $bldg_slot ?>'
				});
			}
			function toggleBuild(bldg_type){
				document.getElementById("build_btn_fact").src="/eos/images/build-factory-inactive.gif";
				document.getElementById("build_btn_store").src="/eos/images/build-store-inactive.gif";
				document.getElementById("build_btn_rnd").src="/eos/images/build-rnd-inactive.gif";
				document.getElementById('build_dialog_fact').style.display = 'none';
				document.getElementById('build_dialog_store').style.display = 'none';
				document.getElementById('build_dialog_rnd').style.display = 'none';
				if(bldg_type == 'fact'){
					document.getElementById("build_btn_fact").src="/eos/images/build-factory.gif";
					document.getElementById('build_dialog_fact').style.display = '';
				}
				if(bldg_type == 'store'){
					document.getElementById("build_btn_store").src="/eos/images/build-store.gif";
					document.getElementById('build_dialog_store').style.display = '';
				}
				if(bldg_type == 'rnd'){
					document.getElementById("build_btn_rnd").src="/eos/images/build-rnd.gif";
					document.getElementById('build_dialog_rnd').style.display = '';
				}
			}
			jQuery(document).ready(function(){
				toggleBuild('fact');
				jQuery('#build_btn_fact').on('click', function(){toggleBuild('fact')});
				jQuery('#build_btn_store').on('click', function(){toggleBuild('store')});
				jQuery('#build_btn_rnd').on('click', function(){toggleBuild('rnd')});
			});
		</script>
<?php require 'include/stats_fbox.php'; ?>
<?php
	// Initialize player level
	$sql = "SELECT player_level FROM players WHERE id = '$eos_player_id'";
	$player_level = $db->query($sql)->fetchColumn();
	if($player_level < 5){
?>
	<div class="tbox_inline">
		<b>Did you know?</b><br /><br />
		You can hover your mouse cursor over each building to see what it can produce, sell, or research.<br />
		Once construction has started, you may click on the building to see its construction status, or to hire RJ Construction to instantly finish your project. 
		Since you are a new entrepreneur, RJ Construction will automatically finish your construction project, and on expansions for <b>buildings up to 500 m&#178;</b> using government subsidy.<br />
	</div>
	<br />
<?php
	}

	function get_bldg_functions($db, $bldg_type, $bldg_id){
		// Populate building function list
		if($bldg_type == 'fact'){
			$sql = "SELECT opid1 FROM list_fact_choices LEFT JOIN list_prod ON list_fact_choices.opid1 = list_prod.id WHERE list_fact_choices.fact_id = '$bldg_id' GROUP BY list_prod.name ORDER BY list_prod.name ASC";
			$produceables = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$list_bldg_functions = "<i>Produces: </i>";
			if(count($produceables)){
				foreach($produceables as $produceable){
					$sql = "SELECT name FROM list_prod WHERE id = ".$produceable['opid1'];
					$list_bldg_functions .= "<br />".$db->query($sql)->fetchColumn();
				}
			}else{
				$list_bldg_functions .= "<br />Nothing";
			}
			return $list_bldg_functions;
		}
		if($bldg_type == 'store'){
			$sql = "SELECT list_store_choices.cat_id FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = '$bldg_id' ORDER BY list_cat.name ASC";
			$sellables = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$list_bldg_functions = "<i>Sells: </i>";
			if(count($sellables)){
				foreach($sellables as $sellable){
					$sql = "SELECT name FROM list_cat WHERE id = ".$sellable['cat_id'];
					$list_bldg_functions .= "<br /><b>".$db->query($sql)->fetchColumn()."</b> (";
					
					$sql = "SELECT name FROM list_prod WHERE cat_id = ".$sellable['cat_id']." ORDER BY name ASC";
					$sell_prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					if(count($sell_prods)){
						foreach($sell_prods as $sell_prod){
							$list_bldg_functions .= $sell_prod['name'].", ";
						}
						$list_bldg_functions = substr($list_bldg_functions, 0, -2);
					}else{
						$list_bldg_functions .= "<i>Nothing</i>";
					}
					$list_bldg_functions .= ")";
				}
			}else{
				$list_bldg_functions .= "<br />Nothing";
			}
			return $list_bldg_functions;
		}
		if($bldg_type == 'rnd'){
			$sql = "SELECT cat_id FROM list_rnd_choices WHERE rnd_id = '$bldg_id'";
			$researchables = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$list_bldg_functions = "<i>Researches: </i>";
			if(count($researchables)){
				foreach($researchables as $researchable){
					$sql = "SELECT name FROM list_cat WHERE id = ".$researchable['cat_id'];
					$list_bldg_functions .= "<br /><b>".$db->query($sql)->fetchColumn()."</b> (";
					
					$sql = "SELECT name FROM list_prod WHERE cat_id = ".$researchable['cat_id']." ORDER BY name ASC";
					$res_prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					if(count($res_prods)){
						foreach($res_prods as $res_prod){
							$list_bldg_functions .= $res_prod['name'].", ";
						}
						$list_bldg_functions = substr($list_bldg_functions, 0, -2);
					}else{
						$list_bldg_functions .= "<i>Nothing</i>";
					}
					$list_bldg_functions .= ")";
				}
			}else{
				$list_bldg_functions .= "<br />Nothing";
			}
			return $list_bldg_functions;
		}
	}
	function do_output($db, $bldg_type, $ctrl_build, $player_level, $ctrl_leftover_allowance){
		if($bldg_type == 'fact'){
			$query_get_bldg_list_info_all = $db->prepare("SELECT id, name, firstcost, firsttimecost, has_image FROM list_fact ORDER BY name ASC");
		}else if($bldg_type == 'store'){
			$query_get_bldg_list_info_all = $db->prepare("SELECT id, name, firstcost, firsttimecost, has_image FROM list_store ORDER BY name ASC");
		}else if($bldg_type == 'rnd'){
			$query_get_bldg_list_info_all = $db->prepare("SELECT id, name, firstcost, firsttimecost, has_image FROM list_rnd ORDER BY name ASC");
		}else{
			echo 'Unknown building type.<br />';
			return false;
		}
		if(!$ctrl_build){
			if($bldg_type == 'fact') $bldg_type_display = 'factories';
			if($bldg_type == 'store') $bldg_type_display = 'stores';
			if($bldg_type == 'rnd') $bldg_type_display = 'research facilities';
			echo 'You are not authorized to construct new '.$bldg_type_display.'.<br />';
			return false;
		}

		// Initialize buildings list
		$query_get_bldg_list_info_all->execute();
		$bldg_list = $query_get_bldg_list_info_all->fetchAll(PDO::FETCH_ASSOC);

		foreach($bldg_list as $bldg_list_item){
			$list_bldg_id = $bldg_list_item["id"];
			$build_cost = $bldg_list_item["firstcost"];
			$generic_name = $bldg_list_item["name"];
			if($bldg_list_item["has_image"]){
				$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($generic_name));
			}else{
				$filename = "no-image";
			}

			$list_bldg_functions = get_bldg_functions($db, $bldg_type, $list_bldg_id);
			echo '<div class="build_choices b3d">';
			echo '<b>',$generic_name,'</b> <a class="info" style="font-size:12px;"><img src="/eos/images/info.png" /><span style="text-align:left !important;width: 35em !important;">',$list_bldg_functions,'</span></a><br />';
			echo '<img style="padding-top:5px;" src="/eos/images/',$bldg_type,'/',$filename,'.gif" /><br />';
			echo '<img src="/eos/images/money.gif" alt="Build Cost:" title="Build Cost" /> $',number_format($build_cost/100, 2, '.', ','),'<br />';
			if($player_level < 5){
				echo '<img src="/eos/images/time.gif" alt="Build Time:" title="Build Time" /> <i>INSTANT</i><br />';
			}else{
				// echo '<img src="/eos/images/time.gif" alt="Build Time:" title="Build Time" /> ',number_format($bldg_list_item["firsttimecost"]/60, 2, '.', ''),' min.<br />';
				echo '<img src="/eos/images/time.gif" alt="Build Time:" title="Build Time" /> 10 sec.<br />';
			}
			if($_SESSION['firm_cash'] < $build_cost){
				echo '<a class="info"><span><font color="#ff0000">Not Enough Money</font></span><img src="/eos/images/button-build-inactive.gif" alt="[Cannot Build]" /></a>';
			}else if($ctrl_leftover_allowance < $build_cost){
				echo '<a class="info"><span><font color="#ff0000">Cost exceeds your daily spending limit.</font></span><img src="/eos/images/button-build-inactive.gif" alt="[Cannot Build]" /></a>';
			}else{
				echo '<a class="info" style="cursor:pointer;" onclick="build_confirm(\'',$bldg_type, '\',', $list_bldg_id,')"><span>Click to Start Building<br /><br /><font color="#ff0000">There will be no confirmation</font></span><img src="/eos/images/button-build.gif" alt="[Build]" /></a>';
			}
			echo '</div>';
		}
	}
?>
	<img id="build_btn_fact" style="cursor:pointer;" src="/eos/images/build-factory-inactive.gif" />
	<img id="build_btn_store" style="cursor:pointer;" src="/eos/images/build-store-inactive.gif" />
	<img id="build_btn_rnd" style="cursor:pointer;" src="/eos/images/build-rnd-inactive.gif" />
	<div class="clearer no_select"></div>
	<div id="build_dialog_fact" style="display:none;">
		<?php do_output($db, 'fact', $ctrl_fact_build, $player_level, $ctrl_leftover_allowance); ?>
	</div>
	<div id="build_dialog_store" style="display:none;">
		<?php do_output($db, 'store', $ctrl_store_build, $player_level, $ctrl_leftover_allowance); ?>
	</div>
	<div id="build_dialog_rnd" style="display:none;">
		<?php do_output($db, 'rnd', $ctrl_rnd_build, $player_level, $ctrl_leftover_allowance); ?>
	</div>
	<div class="clearer no_select">&nbsp;</div>
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>