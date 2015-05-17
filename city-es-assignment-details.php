<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
	$sql = "SELECT firms.name, firms.cash, firms.networth FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms.id = $eos_firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		fbox_breakout('city.php');
	}else{
		$firm_name = $firm["name"];
		$firm_cash = $firm["cash"];
		$firm_networth = $firm["networth"];
	}
	$sql = "SELECT SUM(bonus_percent) FROM firms_positions WHERE fid = $eos_firm_id";
	$bonus_percent_spent = $db->query($sql)->fetchColumn();
	
	$min_salary = max(1000000, floor($firm_networth / 10000));
	$max_salary = max(100000000, floor($firm_networth / 10000) * 100);
	$min_bonus = 0;
	$max_bonus = max(0, min(2000, 8000 - 100 * $bonus_percent_spent));
	
	$esp_id = 0;
	$existing_es_post = 0;
	if(isset($_POST['esp_id'])){
		$esp_id = filter_var($_POST['esp_id'], FILTER_SANITIZE_NUMBER_INT);
		$sql = "SELECT title, duration, pay_flat, bonus_percent, daily_allowance, ctrl_bldg_hurry, ctrl_bldg_land, ctrl_bldg_view, ctrl_fact_produce, ctrl_fact_cancel, ctrl_fact_build, ctrl_fact_expand, ctrl_fact_sell, ctrl_store_price, ctrl_store_ad, ctrl_store_build, ctrl_store_expand, ctrl_store_sell, ctrl_rnd_res, ctrl_rnd_cancel, ctrl_rnd_hurry, ctrl_rnd_build, ctrl_rnd_expand, ctrl_rnd_sell, ctrl_wh_view, ctrl_wh_sell, ctrl_wh_discard, ctrl_b2b_buy, ctrl_hr_post, ctrl_hr_hire, ctrl_hr_fire FROM es_positions WHERE id = $esp_id AND fid = $eos_firm_id";
		$es_position = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if(!empty($es_position)){
			$existing_es_post = 1;

			$min_salary = max($min_salary, $es_position['pay_flat']);
			$max_salary = max($min_salary, $max_salary);
			$min_bonus = max($min_bonus, 100 * $es_position['bonus_percent']);
			$max_bonus = max($min_bonus, $max_bonus);

			$esc_daily_allowance = $es_position['daily_allowance'];
			$esc_daily_allowance_unlimited_status = $esc_daily_allowance == -1 ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';

			$esc_title = $es_position['title'];
			$esc_duration = $es_position['duration'];
			$esc_pay = 0 + $es_position['pay_flat'];
			$esc_bonus = 0 + $es_position['bonus_percent'];

			$esc_bldg_hurry = $es_position['ctrl_bldg_hurry'];
			$esc_bldg_land = $es_position['ctrl_bldg_land'];
			// $esc_bldg_view = $es_position['ctrl_bldg_view'];
			$esc_fact_produce = $es_position['ctrl_fact_produce'];
			$esc_fact_cancel = $es_position['ctrl_fact_cancel'];
			$esc_fact_build = $es_position['ctrl_fact_build'];
			$esc_fact_expand = $es_position['ctrl_fact_expand'];
			$esc_fact_sell = $es_position['ctrl_fact_sell'];
			$esc_store_price = $es_position['ctrl_store_price'];
			$esc_store_ad = $es_position['ctrl_store_ad'];
			$esc_store_build = $es_position['ctrl_store_build'];
			$esc_store_expand = $es_position['ctrl_store_expand'];
			$esc_store_sell = $es_position['ctrl_store_sell'];
			$esc_rnd_res = $es_position['ctrl_rnd_res'];
			$esc_rnd_cancel = $es_position['ctrl_rnd_cancel'];
			$esc_rnd_hurry = $es_position['ctrl_rnd_hurry'];
			$esc_rnd_build = $es_position['ctrl_rnd_build'];
			$esc_rnd_expand = $es_position['ctrl_rnd_expand'];
			$esc_rnd_sell = $es_position['ctrl_rnd_sell'];
			$esc_wh_view = $es_position['ctrl_wh_view'];
			$esc_wh_sell = $es_position['ctrl_wh_sell'];
			$esc_wh_discard = $es_position['ctrl_wh_discard'];
			$esc_b2b_buy = $es_position['ctrl_b2b_buy'];
			$esc_hr_post = $es_position['ctrl_hr_post'];
			$esc_hr_hire = $es_position['ctrl_hr_hire'];
			$esc_hr_fire = $es_position['ctrl_hr_fire'];
			
			$esc_bldg_hurry_status = $esc_bldg_hurry ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_bldg_land_status = $esc_bldg_land ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			// $esc_bldg_view_status = $esc_bldg_view ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_fact_produce_status = $esc_fact_produce ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_fact_cancel_status = $esc_fact_cancel ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_fact_build_status = $esc_fact_build ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_fact_expand_status = $esc_fact_expand ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_fact_sell_status = $esc_fact_sell ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_store_price_status = $esc_store_price ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_store_ad_status = $esc_store_ad ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_store_build_status = $esc_store_build ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_store_expand_status = $esc_store_expand ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_store_sell_status = $esc_store_sell ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_rnd_res_status = $esc_rnd_res ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_rnd_cancel_status = $esc_rnd_cancel ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_rnd_hurry_status = $esc_rnd_hurry ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_rnd_build_status = $esc_rnd_build ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_rnd_expand_status = $esc_rnd_expand ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_rnd_sell_status = $esc_rnd_sell ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_wh_view_status = $esc_wh_view ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_wh_sell_status = $esc_wh_sell ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_wh_discard_status = $esc_wh_discard ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_b2b_buy_status = $esc_b2b_buy ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_hr_post_status = $esc_hr_post ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_hr_hire_status = $esc_hr_hire ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
			$esc_hr_fire_status = $esc_hr_fire ? 'checked = "checked" disabled = "disabled"': 'disabled = "disabled"';
		}
	}
	if(!$existing_es_post){
		$esc_duration = 7;
		$esc_pay = $min_salary;
		$esc_bonus = $min_bonus;

		$esc_daily_allowance = 0;
		$esc_daily_allowance_unlimited_status = $ctrl_daily_allowance == -1 ? '': 'disabled = "disabled"';

		$esc_bldg_hurry_status = $ctrl_bldg_hurry ? '': 'disabled = "disabled"';
		$esc_bldg_land_status = $ctrl_bldg_land ? '': 'disabled = "disabled"';
		// $esc_bldg_view_status = $ctrl_bldg_view ? '': 'disabled = "disabled"';
		$esc_fact_produce_status = $ctrl_fact_produce ? '': 'disabled = "disabled"';
		$esc_fact_cancel_status = $ctrl_fact_cancel ? '': 'disabled = "disabled"';
		$esc_fact_build_status = $ctrl_fact_build ? '': 'disabled = "disabled"';
		$esc_fact_expand_status = $ctrl_fact_expand ? '': 'disabled = "disabled"';
		$esc_fact_sell_status = $ctrl_fact_sell ? '': 'disabled = "disabled"';
		$esc_store_price_status = $ctrl_store_price ? '': 'disabled = "disabled"';
		$esc_store_ad_status = $ctrl_store_ad ? '': 'disabled = "disabled"';
		$esc_store_build_status = $ctrl_store_build ? '': 'disabled = "disabled"';
		$esc_store_expand_status = $ctrl_store_expand ? '': 'disabled = "disabled"';
		$esc_store_sell_status = $ctrl_store_sell ? '': 'disabled = "disabled"';
		$esc_rnd_res_status = $ctrl_rnd_res ? '': 'disabled = "disabled"';
		$esc_rnd_cancel_status = $ctrl_rnd_cancel ? '': 'disabled = "disabled"';
		$esc_rnd_hurry_status = $ctrl_rnd_hurry ? '': 'disabled = "disabled"';
		$esc_rnd_build_status = $ctrl_rnd_build ? '': 'disabled = "disabled"';
		$esc_rnd_expand_status = $ctrl_rnd_expand ? '': 'disabled = "disabled"';
		$esc_rnd_sell_status = $ctrl_rnd_sell ? '': 'disabled = "disabled"';
		$esc_wh_view_status = $ctrl_wh_view ? '': 'disabled = "disabled"';
		$esc_wh_sell_status = $ctrl_wh_sell ? '': 'disabled = "disabled"';
		$esc_wh_discard_status = $ctrl_wh_discard ? '': 'disabled = "disabled"';
		$esc_b2b_buy_status = $ctrl_b2b_buy ? '': 'disabled = "disabled"';
		$esc_hr_post_status = $ctrl_hr_post ? '': 'disabled = "disabled"';
		$esc_hr_hire_status = $ctrl_hr_hire ? '': 'disabled = "disabled"';
		$esc_hr_fire_status = $ctrl_hr_fire ? '': 'disabled = "disabled"';
	}
?>
		<script type="text/javascript">
			var salary, salary_temp;
			var bonus, bonus_temp;
			var salary_max = <?= $max_salary ?>;
			var salary_min = <?= $min_salary ?>;
			var bonus_max = <?= $max_bonus ?>;
			var bonus_min = <?= $min_bonus ?>;

			function salaryMax(){
				salary = salary_max;
				document.getElementById('salary').value = salary;
				checkSalary();
			}
			function salaryMin(){
				salary = salary_min;
				document.getElementById('salary').value = salary;
				checkSalary();
			}
			function checkSalary(){
				salary = Math.floor(stripCommas(document.getElementById('salary').value));
				document.getElementById('salary').value = salary;
				document.getElementById('salary_visible').value = salary/100;
				jQuery("#slider_target").slider("value", salary);
			}
			function updateSalary(){
				salary_temp = document.getElementById('salary_visible').value;
				if(salary_temp.charAt(salary_temp.length-1) == ".") {
					return false;
				}
				salary = Math.round(stripCommas(salary_temp)*100);
				if(salary != '' && !isNaN(salary)){
					if(salary > salary_max){
						salary = salary_max;
						document.getElementById('salary_visible').value = salary/100;
					}
					if(salary < salary_min){
						salary = salary_min;
						document.getElementById('salary_visible').value = salary/100;
					}
					document.getElementById('salary').value = salary;
					checkSalary();
				}
			}
			var checkSalaryTimeout;
			function initUpdateSalary(skipTimeout){
				clearTimeout(checkSalaryTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateSalary();
				}else{
					checkSalaryTimeout = setTimeout("updateSalary();", 3000);
				}
			}
			function bonusMax(){
				bonus = bonus_max;
				document.getElementById('bonus').value = bonus;
				checkBonus();
			}
			function bonusMin(){
				bonus = bonus_min;
				document.getElementById('bonus').value = bonus;
				checkBonus();
			}
			function checkBonus(){
				bonus = Math.floor(stripCommas(document.getElementById('bonus').value));
				document.getElementById('bonus').value = bonus;
				document.getElementById('bonus_visible').value = bonus/100;
				jQuery("#slider_target_2").slider("value", bonus);
			}
			function updateBonus(){
				bonus_temp = document.getElementById('bonus_visible').value;
				if(bonus_temp.charAt(bonus_temp.length-1) == ".") {
					return false;
				}
				bonus = Math.round(stripCommas(bonus_temp)*100);
				if(bonus != '' && !isNaN(bonus)){
					if(bonus > bonus_max){
						bonus = bonus_max;
						document.getElementById('bonus_visible').value = bonus/100;
					}
					if(bonus < bonus_min){
						bonus = bonus_min;
						document.getElementById('bonus_visible').value = bonus/100;
					}
					document.getElementById('bonus').value = bonus;
					checkBonus();
				}
			}
			var checkBonusTimeout;
			function initUpdateBonus(skipTimeout){
				clearTimeout(checkBonusTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateBonus();
				}else{
					checkBonusTimeout = setTimeout("updateBonus();", 1000);
				}
			}
		</script>
		<style type="text/css">
			.disabled{
				color:#888888;
			}
		</style>
<?php require 'include/stats_fbox.php'; ?>
	<div id="es_assignment_form">
		<h3>Post Search Assignment</h3>
<?php
	if(!$ctrl_hr_post){
?>
		You are not authorized to post job offerings.
<?php
	}else if($max_salary <= 0){
?>
		Your company must be in good standing before you can recruit others.
<?php
	}else{
?>
<?php
		if($existing_es_post){
?>
			<font color="#ff0000">You always have the option to raise the salary and the bonus, but a new search assignment is required if you'd like to change job responsibilities or spending limit.</font><br /><br />
			
			<h3>Job Title</h3>
			<b><?= $esc_title; ?></b><br /><br />
			<h3>Job Term</h3>
			<b><?= round($esc_duration / 7 * 12) ?> Months (<?= $esc_duration ?> server days)</b><br /><br />
			
<?php
		}else{
			$sql = "SELECT level, cash FROM firms WHERE id = $eos_firm_id";
			$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			$firm_level = $firm['level'];
			$firm_cash = $firm['cash'];
?>
			<b>Our agency charges a non-refundable $<?= number_format_readable($firm_level * 200000) ?> for each candidate search.</b> We will advertise your position for up to a year (7 server days) or until the position is filled, after which the contract ends and we shall have no further obligations to you.<br /><br />
			If you agree to the terms, simply post the position's salary, bonus, and requirements, and come back at a later time to pick your desired candidate.<br /><br />
			
			<h3>Job Title</h3>
			<input type="text" class="bigger_input" id="esc_title" value="Manager" maxlength="60" /><br /><br />
			<h3>Job Term (between 7-30 server days)</h3>
			<input type="text" class="bigger_input" id="esc_duration" value="7" maxlength="2" /><br /><br />
<?php
		}
?>
		
		<h3>Responsibilities <a class="info"><img style="vertical-align:middle;" src="images/info.png" /><span style="width:400px;">Certain responsibilities require others to function (e.g. View Warehouse is needed for Sell Items on B2B). In order to prevent privilege escalation, job posters may only post job functions in which they are authorized to perform, with spending limit equal to or lower than their own. <br /><br /><b>Warning: </b><br />Litigations can be costly and you cannot rely on others for justice, so DO NOT give authority to people whom you cannot trust.</span></a></h3>
		<div style="float:left;width:220px;padding: 0 5px 15px 0;">
			<b>Factory</b><br />
			<input type="checkbox" class="bigger_input" id="esc_fact_produce" <?= $esc_fact_produce_status ?>/>
			<label for="esc_fact_produce" <?= $esc_fact_produce_status ? 'class="disabled"' : '' ?>>Start Production</label><br />
			<input type="checkbox" class="bigger_input" id="esc_fact_cancel" <?= $esc_fact_cancel_status ?>/>
			<label for="esc_fact_cancel" <?= $esc_fact_cancel_status ? 'class="disabled"' : '' ?>>Cancel Production</label><br />
			<br />
			<b>Stores</b><br />
			<input type="checkbox" class="bigger_input" id="esc_store_price" <?= $esc_store_price_status ?>/>
			<label for="esc_store_price" <?= $esc_store_price_status ? 'class="disabled"' : '' ?>>Set Sales Price</label><br />
			<input type="checkbox" class="bigger_input" id="esc_store_ad" <?= $esc_store_ad_status ?>/>
			<label for="esc_store_ad" <?= $esc_store_ad_status ? 'class="disabled"' : '' ?>>Spend on Marketing</label><br />
			<br />
			<b>R&amp;D</b><br />
			<input type="checkbox" class="bigger_input" id="esc_rnd_res" <?= $esc_rnd_res_status ?>/>
			<label for="esc_rnd_res" <?= $esc_rnd_res_status ? 'class="disabled"' : '' ?>>Start Research</label><br />
			<input type="checkbox" class="bigger_input" id="esc_rnd_cancel" <?= $esc_rnd_cancel_status ?>/>
			<label for="esc_rnd_cancel" <?= $esc_rnd_cancel_status ? 'class="disabled"' : '' ?>>Cancel Research</label><br />
			<input type="checkbox" class="bigger_input" id="esc_rnd_hurry" <?= $esc_rnd_hurry_status ?>/>
			<label for="esc_rnd_hurry" <?= $esc_rnd_hurry_status ? 'class="disabled"' : '' ?>>Hurry Research</label><br />
		</div>
		<div id="b2b_hiring_auths" style="float:left;width:220px;padding: 0 5px 15px 0;">
			<b>Purchasing and Sales</b><br />
			<input type="checkbox" class="bigger_input" id="esc_wh_view" <?= $esc_wh_view_status ?>/>
			<label for="esc_wh_view" <?= $esc_wh_view_status ? 'class="disabled"' : '' ?>>View Warehouse</label><br />
			<input type="checkbox" class="bigger_input" id="esc_wh_sell" <?= $esc_wh_sell_status ?>/>
			<label for="esc_wh_sell" <?= $esc_wh_sell_status ? 'class="disabled"' : '' ?>>Sell Items on B2B</label><br />
			<input type="checkbox" class="bigger_input" id="esc_b2b_buy" <?= $esc_b2b_buy_status ?>/>
			<label for="esc_b2b_buy" <?= $esc_b2b_buy_status ? 'class="disabled"' : '' ?>>Buy Items from B2B</label><br />
			<input type="checkbox" class="bigger_input" id="esc_wh_discard" <?= $esc_wh_discard_status ?>/>
			<label for="esc_wh_discard" <?= $esc_wh_discard_status ? 'class="disabled"' : '' ?>>Discard Warehouse Items</label><br />
			<br />
			<b>Hiring</b><br />
			<input type="checkbox" class="bigger_input" id="esc_hr_post" <?= $esc_hr_post_status ?>/>
			<label for="esc_hr_post" <?= $esc_hr_post_status ? 'class="disabled"' : '' ?>>Post Job Search</label><br />
			<input type="checkbox" class="bigger_input" id="esc_hr_hire" <?= $esc_hr_hire_status ?>/>
			<label for="esc_hr_hire" <?= $esc_hr_hire_status ? 'class="disabled"' : '' ?>>Hire Employees</label><br />
			<input type="checkbox" class="bigger_input" id="esc_hr_fire" <?= $esc_hr_fire_status ?>/>
			<label for="esc_hr_fire" <?= $esc_hr_fire_status ? 'class="disabled"' : '' ?>>Fire Employees</label><br />
		</div>
		<div style="float:left;width:220px;padding: 0 0 15px 0;">
			<b>Construction</b><br />
			<input type="checkbox" class="bigger_input" id="esc_fact_build" name="x" <?= $esc_fact_build_status ?>/>
			<label for="esc_fact_build" <?= $esc_fact_build_status ? 'class="disabled"' : '' ?>>Build Factories</label><br />
			<input type="checkbox" class="bigger_input" id="esc_fact_expand" name="y" <?= $esc_fact_expand_status ?>/>
			<label for="esc_fact_expand" <?= $esc_fact_expand_status ? 'class="disabled"' : '' ?>>Expand Factories</label><br />
			<input type="checkbox" class="bigger_input" id="esc_fact_sell" name="z" <?= $esc_fact_sell_status ?>/>
			<label for="esc_fact_sell" <?= $esc_fact_sell_status ? 'class="disabled"' : '' ?>>Sell Factories</label><br />
			<input type="checkbox" class="bigger_input" id="esc_store_build" <?= $esc_store_build_status ?>/>
			<label for="esc_store_build" <?= $esc_store_build_status ? 'class="disabled"' : '' ?>>Open New Stores</label><br />
			<input type="checkbox" class="bigger_input" id="esc_store_expand" <?= $esc_store_expand_status ?>/>
			<label for="esc_store_expand" <?= $esc_store_expand_status ? 'class="disabled"' : '' ?>>Expand Stores</label><br />
			<input type="checkbox" class="bigger_input" id="esc_store_sell" <?= $esc_store_sell_status ?>/>
			<label for="esc_store_sell" <?= $esc_store_sell_status ? 'class="disabled"' : '' ?>>Sell Stores</label><br />
			<input type="checkbox" class="bigger_input" id="esc_rnd_build" <?= $esc_rnd_build_status ?>/>
			<label for="esc_rnd_build" <?= $esc_rnd_build_status ? 'class="disabled"' : '' ?>>Construct R&amp;Ds</label><br />
			<input type="checkbox" class="bigger_input" id="esc_rnd_expand" <?= $esc_rnd_expand_status ?>/>
			<label for="esc_rnd_expand" <?= $esc_rnd_expand_status ? 'class="disabled"' : '' ?>>Expand R&amp;Ds</label><br />
			<input type="checkbox" class="bigger_input" id="esc_rnd_sell" <?= $esc_rnd_sell_status ?>/>
			<label for="esc_rnd_sell" <?= $esc_rnd_sell_status ? 'class="disabled"' : '' ?>>Sell R&amp;Ds</label><br />
			<input type="checkbox" class="bigger_input" id="esc_bldg_hurry" <?= $esc_bldg_hurry_status ?>/>
			<label for="esc_bldg_hurry" <?= $esc_bldg_hurry_status ? 'class="disabled"' : '' ?>>Hurry Expansions</label><br />
			<input type="checkbox" class="bigger_input" id="esc_bldg_land" <?= $esc_bldg_land_status ?>/>
			<label for="esc_bldg_land" <?= $esc_bldg_land_status ? 'class="disabled"' : '' ?>>Purchase Land</label><br />
		</div>
		<div class="clearer"></div>
		<h3>Daily Spending Limit <a class="info"><img style="vertical-align:middle;" src="images/info.png" /><span style="width:400px;">You may assign a spending limit to prevent certain employees from overspending.</span></a></h3>
		<span id="esc_daily_allowance_unlimited_holder">
			<input type="checkbox" class="bigger_input" id="esc_daily_allowance_unlimited" <?= $esc_daily_allowance_unlimited_status ?>/>
			<label for="esc_daily_allowance_unlimited" <?= $esc_daily_allowance_unlimited_status ? 'class="disabled"' : '' ?>>Unlimited</label>
		</span>
		or $ <input type="text" class="bigger_input" id="esc_daily_allowance" value="<?= $esc_daily_allowance == -1 ? '' : $esc_daily_allowance/100 ?>" maxlength="15" <?= ($esc_daily_allowance == -1 || $existing_es_post)? 'disabled="disabled" ' : '' ?>/><br /><br />
		<div class="clearer"></div><br />
		<form id="slider_form_1" class="default_slider_form" onsubmit="esController.updateAssignment();return false;">
			<h3 style="vertical-align:middle;">Base Salary<br /><small>(Per server day)</small></h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="salaryMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="salaryMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					$ <input id="salary_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="<?= $esc_pay / 100 ?>" size="13" maxlength="13" onkeyup="initUpdateSalary();" onchange="updateSalary();" />
					<input id="salary" type="hidden" style="display:none;" value="<?= $esc_pay ?>" maxlength="17" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<h3 style="vertical-align:middle;">Bonus Percentage<br /><small>(Per server day, as % of net earnings before tax)</small></h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="bonusMin();" /></div>
				<div id="slider_target_2" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="bonusMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="bonus_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="<?= $esc_bonus ?>" size="5" maxlength="5" onkeyup="initUpdateBonus();" onchange="updateBonus();" /> %
					<input id="bonus" type="hidden" style="display:none;" value="<?= 100 * $esc_bonus ?>" maxlength="17" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<?php if($existing_es_post){ ?>
			<img class="big_action_button" style="right: 130px;" src="images/button-cancel-big.gif" id="fund_delete_button" title="Cancel Assignment - NO REFUND" onClick="esController.cancelAssignment(<?= $esp_id ?>);" />
			<?php } ?>
			<img class="big_action_button" src="images/button-trade-big.gif" id="fund_start_button" title="Confirm" onClick="esController.updateAssignment(<?= $esp_id ?>);" />
			<br />
			Note:<br />
			During any term the employee will have the option to re-negotiate his/her salary for the next term.<br />
			Company staff with sufficient HR privileges can fire employees of lower authority or seniority at any time without a reason.<br />
			Severance pay may be required when firing senior employees.<br />
			Pay and bonus are calculated and paid on the game server's daily updates.<br />
			Bonus is calculated based on company's net earnings before tax.
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: <?= $esc_pay ?>,
				min: salary_min,
				max: salary_max,
				slide: function(event, ui){
					jQuery("#salary").val(ui.value);
					checkSalary();
				}
			});
			jQuery("#slider_target_2").slider({
				value: <?= 100 * $esc_bonus ?>,
				min: bonus_min,
				max: bonus_max,
				slide: function(event, ui){
					jQuery("#bonus").val(ui.value);
					checkBonus();
				}
			});
			
			jQuery(document).ready(function(){
				jQuery(document).on('click', '#esc_daily_allowance_unlimited_holder', function(){
					if(jQuery("#esc_daily_allowance_unlimited").prop("checked")){
						document.getElementById("esc_daily_allowance").value = '';
						document.getElementById("esc_daily_allowance").disabled = true;
					}else{
						document.getElementById("esc_daily_allowance").disabled = false;
					}
				});
				jQuery(document).on('click', '#b2b_hiring_auths', function(){
					if(jQuery("#esc_wh_sell").prop("checked")){
						jQuery("#esc_wh_view").prop("checked", true);
					}
					if(jQuery("#esc_wh_discard").prop("checked")){
						jQuery("#esc_wh_view").prop("checked", true);
					}
				});
			});
		</script>
<?php
	}
?>
	</div>
		<br /><br />
		<a class="jqDialog" href="city-es-assignments.php"><input type="button" class="bigger_input" value="Back" /></a> 
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" /> 
<?php require 'include/foot_fbox.php'; ?>