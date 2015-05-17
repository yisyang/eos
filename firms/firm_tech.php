<?php require '../include/prehtml_subd.php'; ?>
<?php
		$firm_found = 0;
		if(isset($_GET["fid"])){
			$firm_id = 0+filter_var($_GET["fid"], FILTER_SANITIZE_NUMBER_INT);
			$query = $db->prepare("SELECT * FROM firms WHERE id = ?");
			$query->execute(array($firm_id));
			$firm_result = $query->fetch(PDO::FETCH_ASSOC);
			$firm_found = empty($firm_result) ? 0 : 1;
		}
		if($firm_found){
			$firm_name = $firm_result["name"];
			$page_title = 'Company Researches - '.$firm_name;
		}else{
			$page_title = 'Company Researches';
		}
?>
<?php require '../include/html_subd.php'; ?>
		<title><?= GAME_TITLE ?> - <?= $page_title ?></title>
<?php require '../include/head_subd.php'; ?>
<?php
		if($firm_found){
			//Match Firm Stats
			$firm_id = $firm_result["id"];
			$firm_name = $firm_result["name"];
			$firm_last_active = strtotime($firm_result["last_active"])+0;
			$firm_last_active_passed = time() - $firm_last_active;
			if($firm_last_active_passed < 900){
				$firm_last_active = "Within 15 minutes";
			}else{
				$firm_last_active = date("F j, Y, g:i A",$firm_last_active);
			}
			
			//Populate Top Researches
			$query = $db->prepare("SELECT pid, quality, name, has_icon 
			FROM firm_tech 
			LEFT JOIN list_prod ON firm_tech.pid = list_prod.id 
			WHERE fid = ? 
			ORDER BY firm_tech.quality DESC");
			$query->execute(array($firm_id));
			$res_results = $query->fetchAll(PDO::FETCH_ASSOC);
			$top_res_display = "";
			if(count($res_results)){
				foreach($res_results as $res_result){
					$pid = $res_result["pid"];
					$pid_name = $res_result["name"];
					$pid_quality = $res_result["quality"];
					$pid_has_icon = $res_result["has_icon"];
					if($pid_has_icon){
						$pid_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($pid_name));
					}else{
						$pid_filename = 'no-icon';
					}
					$top_res_display .= '<div style="float:left;width:105px;text-align:center;">';
					$top_res_display .= '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$pid.'"><img src="/eos/images/prod/large/'.$pid_filename.'.gif" alt="'.$pid_name.'" title="'.$pid_name.'" /></a>';
					$top_res_display .= '<div style="text-align:center;color:#00aa00;font-size:14px;text-shadow:#ffffff 0 0 3px;">Q'.$pid_quality.'</div>';
					$top_res_display .= '</div>';
				}
				$top_res_display .= '<div class="clearer no_select">&nbsp;</div>';
			}else{
				$top_res_display = "None<br />";
			}
?>
			<div class="subd_body">
				<div style="padding: 15px;">
				<?php
					//Add avatar
					//Right side add histogram
					echo '<big>',$firm_name,'\'s Researches</big><br />';
					echo '<small>(Company Last Active: ',$firm_last_active,')</small><br />
					<br />
					',$top_res_display,'<br />
					';
				?>
				</div>
			</div>
<?php
		}else{
?>
			<div style="width: 100%;min-height: 680px;background-color: #faf8e1;border-top: 1px solid #666666;border-bottom: 1px solid #666666;">
				<div style="padding: 15px;">
					The company was not found.
				</div>
			</div>
<?php
		}
?>
<?php require '../include/foot_subd.php'; ?>