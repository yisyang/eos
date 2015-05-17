<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="quit_job_form">
		<input type="button" class="bigger_input" value="<?= $firm_name ?> sucks, let me out!" onclick="firmController.quitJob();" />
	</div>
	<br /><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>