<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
$bldg_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
if(!$bldg_id){
	fbox_breakout('buildings.php');
}
if($bldg_type == 'fact'){
	$ctrl_expand = $ctrl_fact_expand;
	$bldg_activity_url = 'factories-production.php?ffid=';
	$query_get_bldg_info = $db->prepare("SELECT fact_name AS bldg_name, fact_id AS bldg_type_id, size, slot FROM firm_fact WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_fact WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_prod WHERE ffid = :bldg_id");
}else if($bldg_type == 'store'){
	$ctrl_expand = $ctrl_store_expand;
	$bldg_activity_url = 'stores-sell.php?fsid=';
	$query_get_bldg_info = $db->prepare("SELECT store_name AS bldg_name, store_id AS bldg_type_id, size, slot FROM firm_store WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_store WHERE id = :building_type_id");
}else if($bldg_type == 'rnd'){
	$ctrl_expand = $ctrl_rnd_expand;
	$bldg_activity_url = 'rnd-res.php?frid=';
	$query_get_bldg_info = $db->prepare("SELECT rnd_name AS bldg_name, rnd_id AS bldg_type_id, size, slot FROM firm_rnd WHERE id = :bldg_id AND fid = :eos_firm_id");
	$query_get_bldg_list_info = $db->prepare("SELECT name, cost, timecost, has_image FROM list_rnd WHERE id = :building_type_id");
	$query_confirm_inactivity = $db->prepare("SELECT COUNT(*) FROM queue_res WHERE frid = :bldg_id");
}else{
	fbox_breakout('buildings.php');
}

// First check $bldg_id belongs to $eos_firm_id, get $bldg_name, $bldg_type_id, and $bldg_size
if($bldg_id){
	$query_get_bldg_info->execute(array(':bldg_id' => $bldg_id, ':eos_firm_id' => $eos_firm_id));
	$firm_bldg = $query_get_bldg_info->fetch(PDO::FETCH_ASSOC);
	if(empty($firm_bldg)){
		fbox_breakout('buildings.php', 'Building not found.');
	}
	$bldg_name = $firm_bldg['bldg_name'];
	$bldg_type_id = $firm_bldg['bldg_type_id'];
	$bldg_size = $firm_bldg['size'];
	$bldg_slot = $firm_bldg['slot'];
	
	// and that it is not active
	if(isset($query_confirm_inactivity)){
		$query_confirm_inactivity->execute(array(':bldg_id' => $bldg_id));
		$count = $query_confirm_inactivity->fetchColumn();
		if($count){
			fbox_redirect($bldg_activity_url.$bldg_id);
		}
	}
}

// and that it is NOT under construction
$query = $db->prepare("SELECT * FROM queue_build WHERE building_type = ? AND building_id = ?");
$query->execute(array($bldg_type, $bldg_id));
$queue_build = $query->fetch(PDO::FETCH_ASSOC);
if(!empty($queue_build)){
	fbox_breakout('buildings.php');
}

// Initialize building name and image
$query_get_bldg_list_info->execute(array(':building_type_id' => $bldg_type_id));
$bldg_list_info = $query_get_bldg_list_info->fetch(PDO::FETCH_ASSOC);
if(empty($bldg_list_info)){
	fbox_breakout('buildings.php', 'Building prototype not found.');
}
$expand_cost = $bldg_list_info["cost"];
$expand_timecost = $bldg_list_info["timecost"];
if($bldg_list_info["has_image"]){
	$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($bldg_list_info["name"]));
}else{
	$filename = "no-image";
}

// Initialize Firm Cash
$sql = "SELECT firms.cash FROM firms WHERE firms.id = $eos_firm_id";
$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
$firm_cash = $firm['cash'];
$max_esize_cost = floor($firm_cash/$expand_cost);
?>
		<script type="text/javascript">
			var esize;
			var esize_curr = <?= $bldg_size ?>;
			var esize_instant = Math.max(0, 500 - esize_curr);
			var fe_cost = <?= $expand_cost ?>;
			var fe_timecost = <?= $expand_timecost ?>;
			var esize_max_base = esize_instant + 168 * 3600 / fe_timecost;
			var esize_max_cost = <?= $max_esize_cost ?>;
			var cost_met = 0;
			var unit_cost;

			//Calculate esize_max
			var esize_max_comp_1 = esize_max_base;
			unit_cost = 0.5 + 0.5 / Math.pow(esize_max_base,0.3);
			
			var esize_max_comp_2 = esize_instant + (esize_max_base - esize_instant) / unit_cost;
			var i = 0;
			while(i < 10 && Math.floor(esize_max_comp_2) > esize_max_comp_1){
				i++;
				esize_max_comp_1 = esize_max_comp_2;
				unit_cost = 0.5 + 0.5 / Math.pow(esize_max_comp_1,0.3);
				esize_max_comp_2 = esize_instant + (esize_max_base - esize_instant) / unit_cost;
			}
			var esize_max = Math.min(Math.floor(esize_max_comp_2), esize_max_cost);

			function update_allow_expansion(){
				if(esize > 0 && cost_met){
					document.getElementById('expand_confirm_div').style.display = "block";
					document.getElementById('expand_confirm_div2').style.display = "none";
					document.getElementById('expand_confirm_div3').style.display = "none";
				}else{
					if(esize > 0){
						document.getElementById('expand_confirm_div').style.display = "none";
						document.getElementById('expand_confirm_div2').style.display = "block";
						document.getElementById('expand_confirm_div3').style.display = "none";
					}else{
						document.getElementById('expand_confirm_div').style.display = "none";
						document.getElementById('expand_confirm_div2').style.display = "none";
						document.getElementById('expand_confirm_div3').style.display = "block";
					}
				}
			}
			function esizeAdd1(){
				esize = Math.floor(stripCommas(document.getElementById('esize').value));
				if(esize < esize_max){
					esize = esize + 1;
					document.getElementById('esize').value = esize;
					checkEsize();
				}
			}
			function esizeSubtract1(){
				esize = Math.floor(stripCommas(document.getElementById('esize').value));
				if(esize > 0){
					esize = esize - 1;
					document.getElementById('esize').value = esize;
					checkEsize();
				}
			}
			function esizeMax(){
				esize = esize_max;
				document.getElementById('esize').value = esize;
				checkEsize();
			}
			function checkEsize(){
				esize = Math.floor(document.getElementById('esize').value);
				if(esize > 0){
					if(esize > esize_max){
						esize = esize_max;
					}
					unit_cost = 0.5 + 0.5 / Math.pow(esize, 0.3);
					var fe_totalcost = esize * fe_cost;
					var fe_totaltimecost = sec2hms(Math.max(0, esize - esize_instant) * fe_timecost * unit_cost);
					if(<?= $firm_cash ?> < fe_totalcost){
						cost_met = 0;
						jQuery('#fe_total_cost').html('<font color="#ff0000"><b>$' + formatNum(fe_totalcost/100, 2) + '</b></font>');
					}else{
						cost_met = 1;
						jQuery('#fe_total_cost').html('$' + formatNum(fe_totalcost/100, 2));
					}
					jQuery('#fe_total_time').html(fe_totaltimecost);
					document.getElementById('esize').value = esize;
				}else{
					var fe_totalcost = 0;
					cost_met = 0;
					jQuery('#fe_total_cost').html('$0');
					jQuery('#fe_total_time').html('00:00:00');
					document.getElementById('esize').value = '0';
				}
				jQuery("#slider_target").slider("value", esize);
				update_allow_expansion();
			}
			
			function expandConfirm(){
				esize = document.getElementById('esize').value;
				jqDialogInit('bldg-expand-start.php', {
					id : <?= $bldg_id ?>,
					type : '<?= $bldg_type ?>',
					esize : esize
				});
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<?php if($player_level < 5){ ?>
		<div class="tbox_inline">
			<i>Note to Small Business Owners:</i><br />
			The state-owned RJ Construction, Inc. will instantly finish any expansion on <b>buildings up to 500 m&#178;</b> using government subsidy.
		</div><br />
	<?php } ?>
	<h3>Expand <?= $bldg_name.' ('.$bldg_size.' m&#178;)' ?></h3>
	<img src="/eos/images/<?= $bldg_type ?>/<?= $filename ?>.gif" /></a><br /><br />
<?php
	if(!$ctrl_expand){
?>
		You are not authorized to expand this <?= $bldg_name ?>.<br />
<?php
	}else{
?>
	<form id="slider_form_1" class="default_slider_form" onsubmit="expandConfirm();return false;">
		How many m&#178; do you plan to add?<br />
		<div style="line-height:48px;" class="vert_middle">
			<div style="float:left;width:60px;"><img class="slider_button_subtract_one" src="images/slider_left.gif" style="cursor:pointer;" onClick="esizeSubtract1();" /></div>
			<div id="slider_target" class="slider_target"></div>
			<div style="float:left;width:60px;"><img class="slider_button_add_one" src="images/slider_right.gif" style="cursor:pointer;" onClick="esizeAdd1();" /></div>
			<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="esizeMax();" /></div>
			<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
				<input id="esize" type="text" style="border: 2px solid #997755;text-align:center;" size="8" maxlength="8" onchange="checkEsize();" />
			</div>
			<div class="clearer"></div>
		</div>
		<br />
		Expansion cost: <img src="images/money.gif" alt="Cash:" title="Cash" /> <span id="fe_total_cost">N/A</span> &nbsp; <img src="images/time.gif" alt="Time:" title="Time" /> <span id="fe_total_time">N/A</span><br /><br />
		<div id="expand_confirm_div3" style="float:right;display: none;">
			<a class="info"><span><font color="#ff0000">You do not have enough cash.</font></span><img src="/eos/images/button-build-inactive.gif" alt="[Cannot Expand]" /></a>
		</div>
		<div id="expand_confirm_div2" style="float:right;">
			<a class="info"><span><font color="#ff0000">Please input expansion size.</font></span><img src="/eos/images/button-build-inactive.gif" alt="[Cannot Expand]" /></a>
		</div>
		<div id="expand_confirm_div" style="float:right;display: none;">
			<a class="info" style="cursor:pointer;" onclick="expandConfirm()"><img src="/eos/images/button-build-big.gif" alt="[Expand]" /><span>Click to start expanding. <br />Expansion cannot be stopped until it is fully completed.<br /><br /><font color="#ff0000">There will be no more confirmation</font></span></a>
		</div>
		<div style="display:none;"><input type="submit" value="submit" /></div>
	</form>
	<script type="text/javascript">
		jQuery("#slider_target").slider({
			value: 0,
			min: 0,
			max: esize_max,
			slide: function(event, ui){
				jQuery("#esize").val(ui.value);
				checkEsize();
			}
		});
	</script>
<?php
	}
?>
	<div class="clearer no_select">&nbsp;</div>
	<a class="jqDialog" href="<?= $bldg_activity_url.$bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>