<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
if(isset($_GET['id'])){
	$bldg_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
}else{
	$bldg_id = 0;
}
$bldg_type = filter_var($_GET['type'], FILTER_SANITIZE_STRING);
if(isset($_GET['slot'])){
	$bldg_slot = filter_var($_GET['slot'], FILTER_SANITIZE_NUMBER_INT);
}else{
	$bldg_slot = 0;
}
if(!$bldg_id && !$bldg_slot){
	fbox_breakout('buildings.php');
}
if($bldg_type == 'fact'){
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_info = $db->prepare("SELECT fact_name AS bldg_name, slot FROM firm_fact WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_id = $db->prepare("SELECT id FROM firm_fact WHERE fid = :eos_firm_id AND slot = :slot");
	$query_get_bldg_list_info = $db->prepare("SELECT name, has_image FROM list_fact WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_fact SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_insert_bldg = $db->prepare("INSERT INTO firm_fact (fid, fact_id, fact_name, size, slot) SELECT :eos_firm_id, :building_type_id, name, :size, :slot FROM list_fact WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_prod WHERE ffid = :bldg_id");
}else if($bldg_type == 'store'){
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_info = $db->prepare("SELECT store_name AS bldg_name, slot FROM firm_store WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_id = $db->prepare("SELECT id FROM firm_store WHERE fid = :eos_firm_id AND slot = :slot");
	$query_get_bldg_list_info = $db->prepare("SELECT name, has_image FROM list_store WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_store SET size = :size, is_expanding = 0 WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_insert_bldg = $db->prepare("INSERT INTO firm_store (fid, store_id, store_name, size, slot) SELECT :eos_firm_id, :building_type_id, name, :size, :slot FROM list_store WHERE id = :building_type_id");
}else if($bldg_type == 'rnd'){
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_info = $db->prepare("SELECT rnd_name AS bldg_name, slot FROM firm_rnd WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_id = $db->prepare("SELECT id FROM firm_rnd WHERE fid = :eos_firm_id AND slot = :slot");
	$query_get_bldg_list_info = $db->prepare("SELECT name, has_image FROM list_rnd WHERE id = :building_type_id");
	$query_update_bldg = $db->prepare("UPDATE firm_rnd SET size = :size WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_insert_bldg = $db->prepare("INSERT INTO firm_rnd (fid, rnd_id, rnd_name, size, slot) SELECT :eos_firm_id, :building_type_id, name, :size, :slot FROM list_rnd WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_res WHERE frid = :bldg_id");
}else{
	fbox_breakout('buildings.php');
}
// First check $bldg_id belongs to $eos_firm_id, get $bldg_name
if($bldg_id){
	$query_get_bldg_info->execute(array(':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
	$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
	if(empty($firm_bldg)){
		fbox_breakout('buildings.php', 'Building not found.');
	}
	$bldg_name = $firm_bldg['bldg_name'];
	
	// and that it is not active
	if(isset($query_confirm_inactivity)){
		$query_confirm_inactivity->execute(array(':bldg_id' => $bldg_id));
		$count = $query_confirm_inactivity->fetchColumn();
		if($count){
			fbox_redirect($bldg_activity_url.$bldg_id);
		}
	}
}

// and that it IS under construction
if($bldg_id){
	$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
	$query->execute(array($bldg_type, $bldg_id));
	$queue_build = $query->fetch(PDO::FETCH_ASSOC);
}else{
	$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_slot = ? AND fid = ?");
	$query->execute(array($bldg_type, $bldg_slot, $eos_firm_id));
	$queue_build = $query->fetch(PDO::FETCH_ASSOC);
}
if(empty($queue_build)){
	fbox_breakout('buildings.php');
}
$b_expand_id = $queue_build["id"];
$b_expand_type_id = $queue_build["building_type_id"];
$b_expand_frid = $queue_build["building_id"];
$b_expand_slot = $queue_build["building_slot"];
$b_expand_size = $queue_build["newsize"];
$b_expand_endtime = $queue_build["endtime"];
$b_expand_remaining = $b_expand_endtime - time();

// Initialize building name and image
$query_get_bldg_list_info->execute(array(':building_type_id' => $b_expand_type_id));
$bldg_list_info = $query_get_bldg_list_info->fetch(PDO::FETCH_ASSOC);
$generic_name = $bldg_list_info["name"];
if($bldg_list_info["has_image"]){
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($generic_name));
}else{
	$filename = "no-image";
}
if(!$bldg_id){
	$bldg_name = $generic_name;
}

// Catch non-updated completed construction
if($b_expand_remaining <= 0){
	$sql = "DELETE FROM queue_build WHERE id = $b_expand_id";
	$db->query($sql);
	if($bldg_id){
		$query_update_bldg->execute(array(':size' => $b_expand_size, ':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
		$query_get_bldg_info->execute(array(':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
		$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
		$b_expand_slot = $firm_bldg['slot'];
	}else{
		$query_insert_bldg->execute(array(':eos_firm_id' => $eos_firm_id, ':building_type_id' => $b_expand_type_id, ':size' => $b_expand_size, ':slot' => $b_expand_slot));
		$query_get_bldg_id->execute(array(':eos_firm_id' => $eos_firm_id, ':slot' => $b_expand_slot));
		$firm_bldg = $query_get_bldg_id->fetch(PDO::FETCH_ASSOC);
		$bldg_id = $firm_bldg['id'];
	}
	?>
		<script type="text/javascript">
			var slot = <?= $b_expand_slot ?>;
			cd_on[slot] = 0;
			cd_total[slot] = 0;
			cd_remaining[slot] = 0;
			
			bldg_title[slot] = '<?= $bldg_name.' ('.$b_expand_size.' m&#178;)' ?>';
			bldg_status[slot] = 'Ready';
			document.getElementById("cd_icon_back_"+slot).className = "anim_placeholder";
			document.getElementById("cd_icon_"+slot).className = "anim_placeholder";
			jQuery("#building_image_"+slot).html('<img class="no_select" src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" width="90" height="40" />');
			$("#cd_icon_title_"+slot).attr("href","<?= $bldg_activity_url.$bldg_id ?>");
			document.getElementById("cd_icon_title_"+slot).onclick = function (){jQuery('#a_proxy').attr('href', this.href).click();return false;};

			jqDialogInit("<?= $bldg_activity_url.$bldg_id ?>");
		</script>
	<?php
	exit();
}

// Initialize Firm Cash and Level
$sql = "SELECT firms.cash, firms.level FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];
$firm_level = $firm['level'];

// Initialize Player Influence and Level
$sql = "SELECT player_level, influence FROM players WHERE players.id = $eos_player_id";
$player = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$player_level = $player['player_level'];
$player_influence = $player['influence'];

if($b_expand_size > 10 && $b_expand_size < 501){
	$eh_unit_cost_inf = 0;
	$eh_unit_cost_cash = 0;
	$eh_max = ceil($b_expand_remaining/60);
}else{
	$eh_unit_cost_inf = 0.2;
	$eh_unit_cost_cash = 600000 * max(0, $firm_level - 5);
	if($eh_unit_cost_cash > 0){
		$eh_max = min(ceil($b_expand_remaining/60), $player_influence / $eh_unit_cost_inf, $firm_cash / $eh_unit_cost_cash);
	}else{
		$eh_max = min(ceil($b_expand_remaining/60), $player_influence / $eh_unit_cost_inf);
	}
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Expanding <?= $bldg_name.' (to '.$b_expand_size.' m&#178;)' ?></h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
		Currently expanding to <?= $b_expand_size ?> m&#178;.<br />
		Time remaining: <span id="cd_div"><?= sec2hms($b_expand_remaining) ?></span><br /><br />
		
		<script type="text/javascript">
			var ehTime;
			var ehTime_curr = 0;
			var ehTime_max = <?= $eh_max ?>;
			var ehUnitCostInf = <?= $eh_unit_cost_inf ?>;
			var ehUnitCostCash = <?= $eh_unit_cost_cash ?>;
			var ehCostMet = 0;

			function displayEhActionButton(){
				if(ehTime > 0 && ehCostMet){
					document.getElementById('eh_confirm_div').style.display = "block";
					document.getElementById('eh_confirm_div2').style.display = "none";
					document.getElementById('eh_confirm_div3').style.display = "none";
				}else{
					if(ehTime > 0){
						document.getElementById('eh_confirm_div').style.display = "none";
						document.getElementById('eh_confirm_div2').style.display = "block";
						document.getElementById('eh_confirm_div3').style.display = "none";
					}else{
						document.getElementById('eh_confirm_div').style.display = "none";
						document.getElementById('eh_confirm_div2').style.display = "none";
						document.getElementById('eh_confirm_div3').style.display = "block";
					}
				}
			}
			function ehTimeAdd1(){
				ehTime = hm2min(document.getElementById('ehTime').value);
				ehTime = Math.min(ehTime + 1, ehTime_max);
				document.getElementById('ehTimeProxy').value = min2hm(ehTime);
				document.getElementById('ehTime').value = ehTime;
				checkEhTime();
			}
			function ehTimeSubtract1(){
				ehTime = hm2min(document.getElementById('ehTime').value);
				ehTime = Math.max(0, ehTime - 1);
				document.getElementById('ehTimeProxy').value = min2hm(ehTime);
				document.getElementById('ehTime').value = ehTime;
				checkEhTime();
			}
			function ehTimeMax(){
				ehTime = ehTime_max;
				document.getElementById('ehTimeProxy').value = min2hm(ehTime);
				document.getElementById('ehTime').value = ehTime;
				checkEhTime();
			}
			function checkEhTimeProxy(){
				ehTime = hm2min(document.getElementById('ehTimeProxy').value);
				document.getElementById('ehTime').value = ehTime;
				checkEhTime();
			}
			function checkEhTime(){
				ehTime = Math.min(document.getElementById('ehTime').value, ehTime_max);
				if(ehTime > 0){
					unit_cost = 0.5 + 0.5 / Math.pow(ehTime,0.3);
					var ehCostInf = Math.ceil(ehTime * ehUnitCostInf);
					var ehCostCash = Math.ceil(ehTime * ehUnitCostCash);
					ehCostMet = 1;
					if(<?= min($firm_cash, $ctrl_leftover_allowance) ?> < ehCostCash){
						ehCostMet = 0;
						jQuery('#eh_cost_cash').html('<font color="#ff0000"><b>$' + formatNum(ehCostCash/100, 2) + '</b></font>');
					}else{
						jQuery('#eh_cost_cash').html('$' + formatNum(ehCostCash/100, 2));
					}
					if(<?= $player_influence ?> < ehCostInf){
						ehCostMet = 0;
						jQuery('#eh_cost_inf').html('<font color="#ff0000"><b>' + Math.ceil(ehCostInf) + '</b></font>');
					}else{
						jQuery('#eh_cost_inf').html(Math.ceil(ehCostInf));
					}
					document.getElementById('ehTimeProxy').value = min2hm(ehTime);
					document.getElementById('ehTime').value = ehTime;
				}else{
					ehTime = 0;
					ehCostMet = 0;
					jQuery('#eh_cost_cash').html('$0');
					jQuery('#eh_cost_inf').html('0');
					jQuery('#ehTimeProxy').value = '0';
					document.getElementById('ehTime').value = 0;
				}
				jQuery("#slider_target").slider("value", ehTime);
				displayEhActionButton();
			}
			function hurryExpansion(){
				ehTime = document.getElementById('ehTime').value;
				jqDialogInit("bldg-expand-hurry.php", {
					id : <?= $bldg_id ?>,
					type : '<?= $bldg_type ?>',
					slot : <?= $bldg_slot ?>,
					eh_time : ehTime
				});
			}
			
			var cd_remaining_fbox = <?= $b_expand_remaining ?>;
			var reloading_fbox = 0;

			function countdown_fbox(){
				if(typeof(document.getElementById("cd_div")) !== 'undefined' && document.getElementById("cd_div") !== null){
					cd_remaining_fbox -= 1;
					ehTime_max = Math.min(ehTime_max, Math.ceil(cd_remaining_fbox/60)); // TODO: Clean up this ugly hack
					jQuery("#cd_div").html(sec2hms(cd_remaining_fbox));
					if(cd_remaining_fbox <= 0){
						if(!reloading_fbox){
							reloading_fbox = 1;
							clearInterval(modalController.modalCdt);
							setTimeout(function(){
								jqDialogInit("<?= $bldg_activity_url.$bldg_id ?>");
							}, 1500);
							return;
						}
					}
				}else{
					clearInterval(modalController.modalCdt);
				}
			}

			if(typeof(modalController.modalCdt) !== 'undefined' && modalController.modalCdt) clearInterval(modalController.modalCdt);
			modalController.modalCdt = setInterval("countdown_fbox()", 1000);
		</script>
<?php
	if(!$ctrl_bldg_hurry){
?>
		You are not authorized to hire extra help for construction.<br />
<?php
	}else{
?>
		Workers from the state-owned RJ Construction, Inc. are working as hard as they can, but you can make them work even harder by filling the right pockets.</b><br /><br />
	<form id="slider_form_1" class="default_slider_form" onsubmit="hurryExpansion();return false;">
		How much time would you like to save?<br /><br />
		<div style="line-height:48px;" class="vert_middle">
			<div style="float:left;width:60px;"><img class="slider_button_subtract_one" src="images/slider_left.gif" style="cursor:pointer;" onClick="ehTimeSubtract1();" /></div>
			<div id="slider_target" class="slider_target"></div>
			<div style="float:left;width:60px;"><img class="slider_button_add_one" src="images/slider_right.gif" style="cursor:pointer;" onClick="ehTimeAdd1();" /></div>
			<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="ehTimeMax();" /></div>
			<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
				<input id="ehTimeProxy" type="text" style="border: 2px solid #997755;text-align:center;" size="8" maxlength="8" onchange="checkEhTimeProxy();" /> (hh:mm)
				<input id="ehTime" type="hidden" />
			</div>
			<div class="clearer"></div>
		</div>
		<br />
		Extra spending: <img src="images/money.gif" alt="Cash:" title="Cash" /> <span id="eh_cost_cash">N/A</span> &nbsp; <img src="images/influence.gif" alt="Influence:" title="Influence" /> <span id="eh_cost_inf">N/A</span> / <?= $player_influence ?><br /><br />
		<div id="eh_confirm_div3" style="float:right;display: none;">
			<a class="info"><span><font color="#ff0000">Insufficient influence, cash, or spending limit reached.</font></span><img src="/eos/images/button-trade-inactive.gif" alt="[Cannot Hurry]" /></a>
		</div>
		<div id="eh_confirm_div2" style="float:right;">
			<a class="info"><span><font color="#ff0000">Please input time.</font></span><img src="/eos/images/button-trade-inactive.gif" alt="[Cannot Hurry]" /></a>
		</div>
		<div id="eh_confirm_div" style="float:right;display: none;">
			<a class="info" style="cursor:pointer;" onclick="hurryExpansion()"><img src="/eos/images/button-trade-big.gif" alt="[Hurry]" /><span>Click to "provide incentives".<br /><br /><font color="#ff0000">There will be no more confirmation</font></span></a>
		</div>
		<div style="display:none;"><input type="submit" value="submit" /></div>
	</form>
	<script type="text/javascript">
		jQuery("#slider_target").slider({
			value: 0,
			min: 0,
			max: ehTime_max,
			slide: function( event, ui ){
				jQuery("#ehTime").val(ui.value);
				checkEhTime();
			}
		});
	</script>
<?php } ?>
		<div class="clearer no_select">&nbsp;</div>
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>