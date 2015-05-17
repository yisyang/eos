<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - Product Stats</title>
<?php require '../include/head_subd.php'; ?>
		<div class="subd_body">
<?php
	$query = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.demand, list_prod.demand_met FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_cat.sellable AND list_cat.name NOT LIKE '-%' ORDER BY list_prod.name ASC");
	$query->execute(array());
	$results = $query->fetchAll(PDO::FETCH_ASSOC);
	$search_result_count = count($results);
	
	echo '<h3>Product Sales Stats</h3>';
	if($search_result_count){
		echo 'All prices are in cents.<br /><br />';
		echo '<table class="default_table default_table_smallfont" style="width: 100% !important;"><thead><tr><td>Product</td><td>Base Value</td><td>Avg Store Price</td><td>Avg Quality</td><td>Units Sold</td><td>Demand</td><td>% Demand Met</td></tr></thead>';
		//Populate Search Results
		foreach($results as $result){
			echo '<tbody><tr>';
			echo '<td>',$result['name'],'</td>';
			echo '<td>',$result['value'],'</td>';
			echo '<td>',$result['value_avg'],'</td>';
			echo '<td>',number_format($result['q_avg'],2,'.',''),'</td>';
			$sr_demand[$i] = number_format($result['demand'] / $result['value_avg'],2,'.','');
			$sr_supply[$i] = number_format($sr_demand[$i] * $result['demand_met'],2,'.','');
			echo '<td>',$sr_supply[$i],'</td>';
			echo '<td>',$sr_demand[$i],'</td>';
			echo '<td>',number_format($result['demand_met']*100,2,'.',','),'%</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}else{
		echo 'No results found.';
	}
	echo '<br /><br />';
?>
		</div>
<?php require '../include/foot_subd.php'; ?>