<?php require 'include/prehtml.php'; ?>
<?php
	$_SESSION['p_vacation_time'] = time();
?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="vacation_form">
		<h3>Vacation Mode <a class="info"><img src="/eos/images/info.png" /><span>Under vacation mode, all queues including construction, research and production are halted. Stores are closed. Salary / maintenance / interest will not be charged. Taxes and B2B transactions are not affected.</span></a>
		</h3>
		If you're going on an extended vacation, you may choose to lock down your private companies. As soon as a company is put into lock down, <b>NO ONE will be able to access the company before time is up.</b><br /><br />
		Select the companies and enter the number of days for the lock down, then submit the form.<br /><br />
		<div style="text-align:center;line-height:150%;">
			<form onsubmit="settingsController.setVacationMode();return false;" class="vert_middle">
				My Private Companies:<br />
				<?php
					$sql = "SELECT firms.id, firms.name, firms.vacation_out FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id WHERE firms_extended.ceo = $eos_player_id AND !firms_extended.is_public";
					$firms = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					if(!count($firms)){
						echo 'You do not own any private company.';
					}else{
						$timenow = time();
						foreach($firms as $firm){
							if(strtotime($firm['vacation_out']) > $timenow){
								echo '<a class="info"><img src="/eos/images/info.png" width="16" height="16" /><span>LOCKED DOWN UNTIL:<br />'.$firm['vacation_out'].'</span></a> <i>'.$firm['name'].'</i><br />';
							}else{
								echo '<input id="vacation_company_'.$firm['id'].'" class="bigger_input vacation_cb" type="checkbox" value="'.$firm['id'].'" /><label for="vacation_company_'.$firm['id'].'">'.$firm['name'].'</label><br />';
							}
						}
				?>
				<br />Lock Down Duration in Days:<br />(Min. 1; 2.5 = two days and 12 hours)<br />
				<input class="bigger_input" id="vacation_duration" name="vacation_duration" type="text" size="10" maxlength="10" value="" /><br />
				<input class="bigger_input" type="submit" value="Enter Vacation Mode" />
				<?php
					}
				?>
			</form>
			<br />
			<div class="tbox_inline">Tip: If you have 3 days worth of queues / inventory, and are going on a 14 days vacation, you should lock down your company for 11 days so production / sales will be complete as soon as you are back.</div>
		</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
	</div>
<?php require 'include/foot_fbox.php'; ?>