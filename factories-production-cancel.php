<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
$bldg_id = filter_var($_POST['ffid'], FILTER_SANITIZE_NUMBER_INT);
$fpid = filter_var($_POST['fpid'], FILTER_SANITIZE_NUMBER_INT);
if(!$fpid || !$ctrl_fact_cancel){
	fbox_breakout('buildings.php');
}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<script type="text/javascript">
		progressController.cancelQueue('fact', <?= $fpid ?>, function(resp){
			jQuery('#cancelMsg').html("Production successfully stopped. " + resp.qp_opid1_produced + " " + resp.qp_opid1_name + "(s) of quality " + resp.qp_opid1_q + " have been moved into the warehouse, along with any unused materials.");
			bldgController.updateSlot(resp.slot);
			progressController.refreshQueue('fact');	
			firmController.getCash();
		});
	</script>
	<h3>Production Canceled</h3>
		<span id="cancelMsg">Sending notice to the factory manager...</span>
	<br /><br />
	<a class="jqDialog" href="factories-production.php?ffid=<?= $bldg_id ?>"><input type="button" class="bigger_input" value="Back" /></a> 
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>

