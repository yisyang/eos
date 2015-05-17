<?php require 'include/prehtml.php'; ?>
<?php
	$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);
	
	$sql = "SELECT firms.id AS firm_id, firms.name AS firm_name, firms.networth, firms.cash, firms.loan, es_positions.* FROM es_positions LEFT JOIN firms ON firms.id = es_positions.fid WHERE es_positions.id = $esp_id";
	$position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

	if(empty($position)){
		fbox_echoout('This position was recently filled or has been removed.', 'city-es-jobs.php');
	}
	
	$es_firm_id = $position['firm_id'];
	$es_firm_name = $position['firm_name'];
	$es_firm_networth = $position['networth'];
	$es_firm_cash = $position['cash'];
	$es_firm_loan = $position['loan'];

	$esc_title = $position['title'];
	$esc_duration = $position['duration'];
	$esc_pay = $position['pay_flat'];
	$esc_bonus = $position['bonus_percent'];

	$esc_daily_allowance = $position['daily_allowance'];

	$esc_bldg_hurry = $position['ctrl_bldg_hurry'];
	$esc_bldg_land = $position['ctrl_bldg_land'];
	$esc_bldg_view = $position['ctrl_bldg_view'];
	$esc_fact_produce = $position['ctrl_fact_produce'];
	$esc_fact_cancel = $position['ctrl_fact_cancel'];
	$esc_fact_build = $position['ctrl_fact_build'];
	$esc_fact_expand = $position['ctrl_fact_expand'];
	$esc_fact_sell = $position['ctrl_fact_sell'];
	$esc_store_price = $position['ctrl_store_price'];
	$esc_store_ad = $position['ctrl_store_ad'];
	$esc_store_build = $position['ctrl_store_build'];
	$esc_store_expand = $position['ctrl_store_expand'];
	$esc_store_sell = $position['ctrl_store_sell'];
	$esc_rnd_res = $position['ctrl_rnd_res'];
	$esc_rnd_cancel = $position['ctrl_rnd_cancel'];
	$esc_rnd_hurry = $position['ctrl_rnd_hurry'];
	$esc_rnd_build = $position['ctrl_rnd_build'];
	$esc_rnd_expand = $position['ctrl_rnd_expand'];
	$esc_rnd_sell = $position['ctrl_rnd_sell'];
	$esc_wh_view = $position['ctrl_wh_view'];
	$esc_wh_sell = $position['ctrl_wh_sell'];
	$esc_wh_discard = $position['ctrl_wh_discard'];
	$esc_b2b_buy = $position['ctrl_b2b_buy'];
	$esc_hr_post = $position['ctrl_hr_post'];
	$esc_hr_hire = $position['ctrl_hr_hire'];
	$esc_hr_fire = $position['ctrl_hr_fire'];
	
	$sql = "SELECT COUNT(*) AS cnt FROM es_applications WHERE esp_id = $esp_id AND pid = $eos_player_id";
	$esc_already_applied = $db->query($sql)->fetchColumn();
?>
		<style type="text/css">
			.job_responsibilities_list{
				float:left;width:220px;padding: 0 5px 15px 0;vertical-align:middle;
			}
			.job_responsibilities_list img{
				vertical-align:middle;
			}
			.last{
				padding-right: 0 !important;
			}
			.disabled{
				color:#888888;
			}
		</style>
<?php require 'include/stats_fbox.php'; ?>
	<div id="es_application_form">
		<h3>Job Details</h3>
		<div style="float:left;width:49%;">
			Job Title: <?= $esc_title ?><br />
			Salary: $<?= number_format($esc_pay/100, 2, '.', ',') ?><br />
			Bonus: <?= $esc_bonus ?>%<br />
			Term: <?= round($esc_duration / 7 * 12) ?> Months (<?= $esc_duration ?> server days)<br />
		</div>
		<div style="float:right;width:49%;">
			Company: <a href="/eos/firm/<?= $es_firm_id ?>"><?= $es_firm_name ?></a><br />
			Networth: $<?= number_format($es_firm_networth/100, 2, '.', ',') ?><br />
			Cash: $<?= number_format($es_firm_cash/100, 2, '.', ',') ?><br />
			Loan: $<?= number_format($es_firm_loan/100, 2, '.', ',') ?><br />
		</div>
		<div class="clearer no_select"></div>
		<br />
		<div class="job_subsection">Job Responsibilities</div>
		<div class="job_responsibilities_list">
			<b>Factories</b><br />
			<?= $esc_fact_produce ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Start Production</div>
			<?= $esc_fact_cancel ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Stop Production</div>
			<br />
			<b>Stores</b><br />
			<?= $esc_store_price ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Set Sales Price</div>
			<?= $esc_store_ad ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Spend on Marketing</div>
			<br />
			<b>R&amp;D</b><br />
			<?= $esc_rnd_res ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Start Research</div>
			<?= $esc_rnd_cancel ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Cancel Research</div>
			<?= $esc_rnd_hurry ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Hurry Research</div>
			<br />
		</div>
		<div class="job_responsibilities_list">
			<b>Purchasing and Sales</b><br />
			<?= $esc_wh_view ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> View Warehouse</div>
			<?= $esc_wh_sell ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Sell Items on B2B</div>
			<?= $esc_b2b_buy ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Buy Items from B2B</div>
			<?= $esc_wh_discard ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Discard Warehouse Items</div>
			<br />
			<b>Hiring</b><br />
			<?= $esc_hr_post ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Post Job Search</div>
			<?= $esc_hr_hire ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Hire Employees</div>
			<?= $esc_hr_fire ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Fire Employees</div>
			<br />
		</div>
		<div class="job_responsibilities_list last">
			<b>Construction</b><br />
			<?= $esc_fact_build ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Build Factories</div>
			<?= $esc_fact_expand ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Expand Factories</div>
			<?= $esc_fact_sell ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Sell Factories</div>
			<?= $esc_store_build ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Open New Stores</div>
			<?= $esc_store_expand ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Expand Stores</div>
			<?= $esc_store_sell ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Sell Stores</div>
			<?= $esc_rnd_build ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Construct R&amp;Ds</div>
			<?= $esc_rnd_expand ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Expand R&amp;Ds</div>
			<?= $esc_rnd_sell ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Sell R&amp;Ds</div>
			<?= $esc_bldg_hurry ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Hurry Expansions</div>
			<?= $esc_bldg_land ? '<div><img src="/eos/images/check.gif" alt="-YES-" />' : '<div class="disabled"><img src="/eos/images/x.gif" alt="-NO-" />' ?> Purchase Land</div>
			<br />
		</div>
		<div class="clearer no_select"></div>
		<div class="job_subsection">Daily Spending Limit:</div>
		<?= $esc_daily_allowance == -1 ? 'Unlimited' : '$'.number_format_readable($esc_daily_allowance/100) ?>
		<div class="clearer no_select"></div>
		<br />
		<div class="job_subsection">Application</div>
	<?php if($esc_already_applied){ ?>
		Already applied.
	<?php }else{ ?>
		<span class="store_sell_module_stats_line">Name: </span><?= $eos_player_name ?><br />
		<span class="store_sell_module_stats_line">Address: </span>100 Main St., Econosia City, Econosia<br />
		<span class="store_sell_module_stats_line">Phone: </span>800-555-1212<br />
		<span class="store_sell_module_stats_line">Email: </span><?= preg_replace('~[^\\pL\d]+~u', '-', $eos_player_name) ?>@example.com<br /><br />
		<span class="store_sell_module_stats_line">Resume: </span>(Attached)<br /><br />
		<label for="esc_cover_letter">Cover Letter (Optional, &lt; 2000 characters)</label>
		<textarea id="esc_cover_letter" class="bigger_input" name="esc_cover_letter" style="width:650px;" rows="3" maxlength="2000" placeholder="Why should you be hired?" /></textarea><br />
		<input type="button" class="bigger_input" value="Apply" onclick="esController.submitApplication(<?= $esp_id ?>);" />
	<?php } ?>
	</div>
		<br /><br />
		<a class="jqDialog" href="city-es-jobs.php"><input type="button" class="bigger_input" value="Back" /></a> 
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" /> 
<?php require 'include/foot_fbox.php'; ?>