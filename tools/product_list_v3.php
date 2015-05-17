<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - Product Stats</title>
<?php require '../include/head_subd.php'; ?>
		<div class="subd_body">
<?php
	$query = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value, list_prod.value_avg, list_prod.q_avg, list_prod.tech_avg, list_prod.demand, list_prod.demand_met, list_cat.sellable, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_cat.name NOT LIKE '-%' ORDER BY list_prod.name ASC");
	$query->execute(array());
	$results = $query->fetchAll(PDO::FETCH_ASSOC);
	$search_result_count = count($results);
	
	echo '<h3>Product Sales Stats</h3>';
	if($search_result_count){
		echo 'All prices are in cents.<br /><br />';
		echo '<table class="default_table default_table_smallfont" style="width: 100% !important;"><thead><tr><td>PID</td><td>Product</td><td>Category</td><td>Base Value</td><td>Avg Store Price</td><td>Avg Quality</td><td>Lead Tech</td><td>Units Sold</td><td>Demand</td><td>% Demand Met</td></tr></thead>';
		//Populate Search Results
		foreach($results as $result){
			echo '<tbody><tr>';
			echo '<td>',$result['id'],'</td>';
			echo '<td>',$result['name'],'</td>';
			echo '<td>',$result['cat_name'],'</td>';
			echo '<td>',$result['value'],'</td>';
		if($result['sellable']){
			echo '<td>',$result['value_avg'],'</td>';
			echo '<td>',number_format($result['q_avg'],2,'.',''),'</td>';
		}else{
			echo '<td>N/A</td>';
			echo '<td>N/A</td>';
		}
			echo '<td>',number_format($result['tech_avg'],2,'.',''),'</td>';
		if($result['sellable']){
			$sr_demand = number_format($result['demand'] / $result['value_avg'],2,'.','');
			$sr_supply = number_format($sr_demand * $result['demand_met'],2,'.','');
			echo '<td>',$sr_supply,'</td>';
			echo '<td>',$sr_demand,'</td>';
			echo '<td>',number_format($result['demand_met']*100,2,'.',','),'%</td>';
		}else{
			echo '<td>N/A</td>';
			echo '<td>N/A</td>';
			echo '<td>N/A</td>';
		}
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