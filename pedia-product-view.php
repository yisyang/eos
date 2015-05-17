<?php require 'include/prehtml_no_auth.php'; ?>
<?php
	if(!isset($_GET["pid"])){
		fbox_breakout('pedia.php');
	}

	// Initialize products
	$sql = "SELECT id, name, has_icon FROM list_prod ORDER BY name ASC";
	$list_prod = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	foreach($list_prod as $prod){
		$prod_name[$prod['id']] = $prod['name'];
		$prod_has_icon[$prod['id']] = $prod['has_icon'];
	}

	// Product info from list_prod
	$pid = filter_var($_GET["pid"], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT list_prod.*, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_prod.id = '$pid'";
	$prod = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($prod)){
		echo 'Product not found.';
		exit();
	}
	$this_prod_name = $prod["name"];
	if($prod["has_icon"]){
		$this_prod_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($this_prod_name));
	}else{
		$this_prod_filename = "no-icon";
	}
	
	// Production (output) info from list_fact_choices
	$sql = "SELECT list_fact.id, list_fact.name, list_fact.has_image, list_fact_choices.cost, list_fact_choices.timecost, list_fact_choices.ipid1, list_fact_choices.ipid1n, list_fact_choices.ipid1qm, list_fact_choices.ipid2, list_fact_choices.ipid2n, list_fact_choices.ipid2qm, list_fact_choices.ipid3, list_fact_choices.ipid3n, list_fact_choices.ipid3qm, list_fact_choices.ipid4, list_fact_choices.ipid4n, list_fact_choices.ipid4qm, list_fact_choices.opid1usetech FROM list_fact_choices LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id WHERE list_fact_choices.opid1 = $pid ORDER BY list_fact.name ASC";
	$fc_ops = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	// Production (input) info from list_fact_choices
	$sql = "SELECT list_fact_choices.opid1 FROM list_fact_choices LEFT JOIN list_prod ON list_fact_choices.opid1 = list_prod.id WHERE list_fact_choices.ipid1 = $pid OR list_fact_choices.ipid2 = $pid OR list_fact_choices.ipid3 = $pid OR list_fact_choices.ipid4 = $pid GROUP BY list_prod.name ORDER BY list_prod.name ASC";
	$fc_ips = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	// Store choices from list_store_choices 
	$sql = "SELECT list_store.* FROM list_store_choices LEFT JOIN list_store ON list_store_choices.store_id = list_store.id WHERE cat_id = ".$prod["cat_id"]." ORDER BY list_store.name ASC";
	$scs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	// Research choices from list_rnd_choices 
	$sql = "SELECT list_rnd.* FROM list_rnd_choices LEFT JOIN list_rnd ON list_rnd_choices.rnd_id = list_rnd.id WHERE cat_id=".$prod["cat_id"]." ORDER BY list_rnd.name ASC";
	$rcs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/stats_fbox_no_auth.php'; ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		if(modalController.backLink != ''){
			jQuery('.backLinkHolder').html('<a class="jqDialog" href="' + modalController.backLink + '"><input type="button" class="bigger_input" value="' + modalController.backLinkTitle + '" /></a> ');
			jQuery('div.backLinkHolder').css({paddingBottom: '8px'});
		}
	});
</script>
<div class="backLinkHolder"></div>

<div style="float:left;width:150px;height:100px;"><img src="/eos/images/prod/large/<?= $this_prod_filename ?>.gif" /></div>
<div style="float:left;width:290px;height:98px;padding-top:2px;vertical-align:middle;">
	<h3 style="margin-bottom:6px;"><?= $this_prod_name ?></h3>
	Category: <a class="jqDialog" href="/eos/pedia-cat-view.php?cat_id=<?= $prod["cat_id"] ?>"><?= $prod["cat_name"] ?></a><br /><br />
	<img style="vertical-align:middle;" src="/eos/images/money.gif" title="Wholesale Value at Quality 0" /> $<?php echo number_format_readable($prod["value"]/100); ?> (Wholesale Value)<br />
</div>

<div class="clearer no_select"></div><br />
<h3>Produced from:</h3>
<?php
if(!count($fc_ops)){
	echo 'N/A<br />';
}else{
	foreach($fc_ops as $fc_op){
		echo '<div style="float:left;width:50%;">';
		if($fc_op['has_image']){
			$fc_op_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($fc_op['name']));
		}else{
			$fc_op_filename = "no-image";
		}
		echo '<div style="float:left;width:240px;"><a class="jqDialog" href="/eos/pedia-building-view.php?type=fact&id=',$fc_op['id'],'"><img src="/eos/images/fact/',$fc_op_filename,'.gif" title="',$fc_op['name'],'" /></a></div>';
		if($fc_op['timecost'] < 1){
			echo '<div style="float:left;width:200px;"><img src="/eos/images/money.gif" title="Cash" /> $'.number_format_readable($fc_op['cost']/100).'<br /> <img src="/eos/images/time.gif" title="Time" /> 00:00:0'.$fc_op['timecost'].'</div>';
		}else{
			echo '<div style="float:left;width:200px;"><img src="/eos/images/money.gif" title="Cash" /> $'.number_format_readable($fc_op['cost']/100).'<br /> <img src="/eos/images/time.gif" title="Time" /> '.sec2hms($fc_op['timecost']).'</div>';
		}
		echo '<div class="clearer no_select"></div>';
		$qm_tech = 1;
		$pv_raw_materials = "Raw Materials: None";
		$pv_quality = "Quality: 100% from Research";
		if($fc_op['ipid1']){
			if($prod_has_icon[$fc_op['ipid1']]){
				$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fc_op['ipid1']]));
			}else{
				$filename = "no-icon";
			}
			$qm_tech = $qm_tech - $fc_op['ipid1qm'];
			$pv_raw_materials = 'Raw Materials: '.number_format_readable($fc_op['ipid1n']).' <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid1'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid1']].'" /></a>';
			$pv_quality = 'Quality: '.($fc_op['ipid1qm']*100).'% <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid1'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid1']].'" /></a>';
			if($fc_op['ipid2']){
				if($prod_has_icon[$fc_op['ipid2']]){
					$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fc_op['ipid2']]));
				}else{
					$filename = "no-icon";
				}
				$qm_tech = $qm_tech - $fc_op['ipid2qm'];
				$pv_raw_materials .= ' + '.number_format_readable($fc_op['ipid2n']).' <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid2'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid2']].'" /></a>';
				$pv_quality .= ' + '.($fc_op['ipid2qm']*100).'% <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid2'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid2']].'" /></a>';
				if($fc_op['ipid3']){
					if($prod_has_icon[$fc_op['ipid3']]){
						$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fc_op['ipid3']]));
					}else{
						$filename = "no-icon";
					}
					$qm_tech = $qm_tech - $fc_op['ipid3qm'];
					$pv_raw_materials .= ' + '.number_format_readable($fc_op['ipid3n']).' <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid3'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid3']].'" /></a>';
					$pv_quality .= ' + '.($fc_op['ipid3qm']*100).'% <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid3'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid3']].'" /></a>';
					if($fc_op['ipid4']){
						if($prod_has_icon[$fc_op['ipid4']]){
							$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fc_op['ipid4']]));
						}else{
							$filename = "no-icon";
						}
						$qm_tech = $qm_tech - $fc_op['ipid4qm'];
						$pv_raw_materials .= ' + '.number_format_readable($fc_op['ipid4n']).' <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid4'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid4']].'" /></a>';
						$pv_quality .= ' + '.($fc_op['ipid4qm']*100).'% <a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_op['ipid4'].'"><img src="/eos/images/prod/'.$filename.'.gif" title="'.$prod_name[$fc_op['ipid4']].'" /></a>';
					}
				}
			}
		}
		if(!$fc_op['opid1usetech']){
			$qm_tech = 0;
		}
		$pv_quality .= ' + '.($qm_tech*100).'% <img src="/eos/images/star.gif" title="Research" />';
		echo $pv_raw_materials.'<br />';
		echo $pv_quality.'<br />';
		// echo '<br />';
		echo '</div>';
	}
}
?>
<div class="clearer"></div><br />

<h3>Used in:</h3>
<?php
if(!count($fc_ips)){
	echo 'N/A<br />';
}else{
	if(count($fc_ips) > 20){
		echo count($fc_ips).' products. ';
		echo '<a id="fc_ips_ctrl" onclick="toggleDisplayNone(\'fc_ips\')">(+)</a><br />';
		echo '<div id="fc_ips" style="display:none;">';
	}
	foreach($fc_ips as $fc_ip){
		echo '<div style="float:left;width:50px;">';
		if($prod_has_icon[$fc_ip['opid1']]){
			$fc_ip_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod_name[$fc_ip['opid1']]));
		}else{
			$fc_ip_filename = "no-icon";
		}
		echo '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$fc_ip['opid1'].'"><img src="/eos/images/prod/large/'.$fc_ip_filename.'.gif" title="'.$prod_name[$fc_ip['opid1']].'" width="48" height="48" /></a> ';
		echo '</div>';
	}
	if(count($fc_ips) > 20){
		echo '<div class="clearer"></div></div>';
	}
}
?>
<div class="clearer"></div><br />

<h3>Sold at:</h3>
<?php
if(!count($scs)){
	echo 'N/A<br />';
}else{
	foreach($scs as $sc){
		echo '<div style="float:left;width:220px;">';
		if($sc['has_image']){
			$sc_store_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($sc['name']));
		}else{
			$sc_store_filename = "no-image";
		}
		echo '<a class="jqDialog" href="/eos/pedia-building-view.php?type=store&id=',$sc['id'],'"><img src="/eos/images/store/',$sc_store_filename,'.gif" title="',$sc['name'],'" /></a> ';
		echo '</div>';
	}
}
?>
<div class="clearer"></div><br />

<h3>Researched at:</h3>
<?php
if(!count($rcs)){
	echo 'N/A<br />';
}else{
	echo '<div style="float:left;width:240px;">';
	foreach($rcs as $rc){
		if($rc['has_image']){
			$rc_rnd_filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($rc['name']));
		}else{
			$rc_rnd_filename = "no-image";
		}
		echo '<a class="jqDialog" href="/eos/pedia-building-view.php?type=rnd&id=',$rc['id'],'"><img src="/eos/images/rnd/',$rc_rnd_filename,'.gif" title="',$rc['name'],'" /></a> ';
	}
	echo '</div>';
	echo '<div style="float:left;width:200px;">';
	echo '<img src="/eos/images/money.gif" title="Base Research Cost" /> $'.number_format_readable($prod["res_cost"]/100).' <a class="info"><img src="/eos/images/info.png" alt="i"><span>Each level (quality) of research costs more time and money than the previous level. Actual research costs decrease over time as existing technology spreads and becomes more accessible.</span></a><br /><br />';
	if($prod["res_dep_1"]){
		echo '<a class="info">Research Dependencies:<span>The following product(s) must be discovered (researched to Quality 1) before you can start research on <img src="/eos/images/prod/'.$this_prod_filename.'.gif" /></span></a>';
		$res_dep_name = $prod_name[$prod["res_dep_1"]];
		if($prod_has_icon[$prod["res_dep_1"]]){
			$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($res_dep_name));
		}else{
			$filename = "no-icon";
		}
		echo '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$prod["res_dep_1"].'"><img src="/eos/images/prod/large/'.$filename.'.gif" title="'.$res_dep_name.'" width="48" height="48" /></a> ';
		if($prod["res_dep_2"]){
			$res_dep_name = $prod_name[$prod["res_dep_2"]];
			if($prod_has_icon[$prod["res_dep_2"]]){
				$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($res_dep_name));
			}else{
				$filename = "no-icon";
			}
			echo '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$prod["res_dep_2"].'"><img src="/eos/images/prod/large/'.$filename.'.gif" title="'.$res_dep_name.'" width="48" height="48" /></a> ';
			if($prod["res_dep_3"]){
				$res_dep_name = $prod_name[$prod["res_dep_3"]];
				if($prod_has_icon[$prod["res_dep_3"]]){
					$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($res_dep_name));
				}else{
					$filename = "no-icon";
				}
				echo '<a class="jqDialog" href="/eos/pedia-product-view.php?pid='.$prod["res_dep_3"].'"><img src="/eos/images/prod/large/'.$filename.'.gif" title="'.$res_dep_name.'" width="48" height="48" /></a> ';
			}
		}
	}
	echo '</div>';
}
?>
<div class="clearer"></div><br />

<span class="backLinkHolder"></span>
<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>