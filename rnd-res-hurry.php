<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['frid'], FILTER_SANITIZE_NUMBER_INT);
$rnd_res_id = filter_var($_POST['rnd_res_id'], FILTER_SANITIZE_NUMBER_INT);
if(!$rnd_res_id || !$ctrl_rnd_hurry){
	fbox_breakout('buildings.php');
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		progressController.hurryQueue('rnd', <?= $rnd_res_id ?>, function(resp){
			jQuery('#cancelMsg').html("You have paid an outside contractor $" + formatNum(resp.hurry_cost/100, 2) + " to complete this research. It'll only take a moment for your researchers to do the necessary communications before they are ready to move on to other projects.");
			bldgController.cd_remaining[resp.slot] = 15;
			progressController.refreshQueue('rnd');	
			firmController.getCash();
		});
	</script>
	<h3>Research Outsourced</h3>
		<span id="cancelMsg">Sending notice to the lead scientist...</span>
	<br /><br />
	<a class="jqDialog" href="rnd-res.php?frid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>

