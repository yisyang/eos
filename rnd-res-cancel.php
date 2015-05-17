<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['frid'], FILTER_SANITIZE_NUMBER_INT);
$rnd_res_id = filter_var($_POST['rnd_res_id'], FILTER_SANITIZE_NUMBER_INT);
if(!$rnd_res_id || !$ctrl_rnd_cancel){
	fbox_breakout('buildings.php');
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		progressController.cancelQueue('rnd', <?= $rnd_res_id ?>, function(resp){
			jQuery('#cancelMsg').html("Research successfully stopped. <br /> Unused research funds were transferred back to your company. (+$" + formatNum(resp.refund/100, 2) + ")");
			bldgController.updateSlot(resp.slot);
			progressController.refreshQueue('rnd');	
			firmController.getCash();
		});
	</script>
	<h3>Research Canceled</h3>
		<span id="cancelMsg">Sending notice to the lead scientist...</span>
	<br /><br />
	<a class="jqDialog" href="rnd-res.php?frid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>