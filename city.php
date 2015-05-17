<?php require 'include/prehtml.php'; ?>
<?php
	if($eos_firm_id){
		$view_type = 'hq';
	}else{
		$view_type = 'gov';
	}
	if(isset($_GET['view_type'])){
		$view_type = filter_var($_GET['view_type'], FILTER_SANITIZE_STRING);
	}
?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - City</title>
<?php require 'include/head.php'; ?>
		<link rel="stylesheet" media="screen" type="text/css" href="/eos/scripts/colorPicker/colorpicker.css" />
		<script src="/eos/scripts/colorPicker/colorpicker.min.js"></script>
<?php require 'include/stats.php'; ?>
<?php
	if(!$settings_narrow_screen){
		echo '<img src="/eos/images/title-city.jpg" style="padding-bottom: 10px;" /><br />';
	}
?>
	<div id="eos_narrow_screen_padding">
		<div id="messages_submenu" class="default_submenu">
			<a href="city.php?view_type=hq" class="submenu <?= $view_type == 'hq' ? 'active' : '' ?>"><img src="/eos/images/city_hq.gif" width="36" height="36" alt="[HQ]" title="Headquarters" /></a> 
			<a href="city.php?view_type=bank" class="submenu <?= $view_type == 'bank' ? 'active' : '' ?>"><img src="/eos/images/city_bank.gif" width="36" height="36" alt="[Bank]" title="Bank" /></a> 
			<a href="city.php?view_type=sec" class="submenu <?= $view_type == 'sec' ? 'active' : '' ?>"><img src="/eos/images/city_sec.gif" width="36" height="36" alt="[SEC]" title="Securities & Exchange Commission" /></a> 
			<a href="city.php?view_type=es" class="submenu <?= $view_type == 'es' ? 'active' : '' ?>"><img src="/eos/images/city_es.gif" width="36" height="36" alt="[ES]" title="Executive Search / Employment Service" /></a> 
			<a href="city.php?view_type=gov" class="submenu <?= $view_type == 'gov' ? 'active' : '' ?>"><img src="/eos/images/city_gov.gif" width="36" height="36" alt="[Gov]" title="City Hall" /></a> 
		</div>
		<br /><br />
	<?php
		function parseChoices($choices){
			$text = '';
			if(count($choices)){
				$text .= '<table class="default_table no_border align_left"><tbody>';
				foreach($choices as $choice){
					if(isset($choice['id'])){
						$text .= '<tr id="'.$choice['id'].'"><td>';
					}else{
						$text .= '<tr><td>';
					}
					if(isset($choice['link'])){
						$text .= '<a class="jqDialog" href="'.$choice['link'].'"><input type="button" class="bigger_input" value="'.$choice['title'].'" /></a>';
					}
					if(isset($choice['action'])){
						$text .= '<input type="button" class="bigger_input" value="'.$choice['title'].'" onclick="'.$choice['action'].'" />';
					}
					$text .= '</td><td>';
					$text .= $choice['desc'];
					$text .= '</td></tr>';
				}
				$text .= '</tbody></table>';
			}else{
				$text .= 'Nothing for you to do here.';
			}
			return $text;
		}

		$choices = array();
		
		// First populate company choices
		if($eos_firm_id){
			if($view_type == 'hq'){
				$choices[] = array('title' => 'Employee Roster', 'desc' => 'View everyone who works at this company.', 'link' => 'city-hq-view-employees.php');
				$choices[] = array('title' => 'Change Corporate Color', 'desc' => 'Change your company\'s color.', 'link' => 'city-hq-update-color.php');
				$choices[] = array('title' => 'Order Appraisal', 'desc' => 'Hire an appraiser to re-estimate the company\'s networth.', 'link' => 'city-hq-order-appraisal.php');
				if($ctrl_admin){
					$choices[] = array('title' => 'Set Your Salary', 'desc' => 'Set your compensation and bonus.', 'link' => 'city-hq-set-exec-pay.php');
					if(!$eos_firm_is_public){
						$choices[] = array('title' => 'Sell Company', 'desc' => 'Sell your company.', 'link' => 'city-hq-sell-company.php');
					}else{
						// $choices[] = array('title' => 'Appoint CEO', 'desc' => 'Put this company in the hands of somebody you trust.', 'link' => 'city-hq-appoint-ceo.php');
					}
				}else{
					$choices[] = array('title' => 'Negotiate Your Salary', 'desc' => 'Request a higher pay for your next term.', 'link' => 'city-hq-negotiate-pay.php');
					$choices[] = array('title' => 'Quit Your Job', 'desc' => 'Unhappy about the job? Freedom is just a few clicks away.', 'link' => 'city-hq-quit-job.php');
				}
			}
			if($view_type == 'bank'){
				if(!$eos_firm_is_public && $ctrl_admin){
					$choices[] = array('title' => 'Transfer Cash', 'desc' => 'Transfer cash from your personal account into your company account or vice versa.', 'link' => 'city-bank-transfer-cash.php');
				}
				$choices[] = array('title' => 'Obtain Loan', 'desc' => 'Obtain a new loan from People\'s National Bank.', 'link' => 'city-bank-obtain-loan.php');
				$choices[] = array('title' => 'Repay Loan', 'desc' => 'Make payments on your existing loan.', 'link' => 'city-bank-repay-loan.php');

				if($settings_auto_repay_loan){
					$new_status_text = "OFF";
				}else{
					$new_status_text = "ON";
				}
				$choices[] = array('id' => 'st_auto_repay_loan', 'title' => 'Turn '.$new_status_text.' Auto Loan Payments', 'desc' => 'If active, your company will attempt to repay the full amount of its outstanding loan with its cash at the start of each server day.', 'action' => 'settingsController.toggleOnOff(\'auto_repay_loan\', \'st_auto_repay_loan\');');
			}
			if($view_type == 'sec'){
				if(!$eos_firm_is_public && $ctrl_admin){
					$choices[] = array('title' => 'Initiate IPO', 'desc' => 'Go public, raise some cash, and become famous!', 'link' => 'city-sec-ipo.php');
				}else if($ctrl_admin){
					$choices[] = array('title' => 'Initiate SEO', 'desc' => 'Issue additional shares to raise cash.', 'link' => 'city-sec-seo.php');
					$choices[] = array('title' => 'Buy Back Shares', 'desc' => 'Buy back stocks with cash from shareholders.', 'link' => 'city-sec-buyback.php');
					$choices[] = array('title' => 'Initiate Stock Split', 'desc' => 'Split 1 share into multiple shares, or reverse split multiple shares into 1 share.', 'link' => 'city-sec-split.php');
					$choices[] = array('title' => 'Set Dividend', 'desc' => 'Set the dividend paid to shareholders.', 'link' => 'city-sec-set-dividend.php');
					$choices[] = array('title' => 'Go Private', 'desc' => 'Convert the company back into a private one.', 'link' => 'city-sec-go-private.php');
				}
			}
			if($view_type == 'es'){
				if($ctrl_hr_post){
					$choices[] = array('title' => 'Find Candidates', 'desc' => 'Use our executive search agency to find the best talents in the industry.', 'link' => 'city-es-assignments.php');
				}
			}
			if($view_type == 'gov'){
				$choices[] = array('title' => 'Change DBA', 'desc' => 'Change your company\'s displayed name.', 'link' => 'city-gov-rename.php');
			}
		}
		
		// Next populate player choices
		if($view_type == 'gov'){
			$choices[] = array('title' => 'Establish New Company', 'desc' => 'Start a new company.', 'link' => 'city-gov-new-company.php');
			$choices[] = array('title' => 'Visit Supreme Court', 'desc' => 'The Supreme Court of Econosia is closed year-round in a country where all citizens are honest and law-abiding.', 'link' => 'city-gov-court.php');
		}
		if($view_type == 'es'){
			$choices[] = array('title' => 'View Job Openings', 'desc' => 'Find out who is hiring, and submit your resume.', 'link' => 'city-es-jobs.php');
		}
		
		// Output choices
		echo parseChoices($choices);
	?>
	</div>
<?php require 'include/foot.php'; ?>