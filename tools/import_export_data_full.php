<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - Export Data</title>
<?php require '../include/head_subd.php'; ?>
		<div class="subd_body">
<?php
	echo'<div class="tbox_inline">Note: Import data was removed because it was merged with the b2b, and is therefore irrelevant.</div><br /><br />';
	
	//Raw mat export
	$query = $db->prepare("SELECT list_prod.name, list_prod.value, foreign_raw_mat_purc.price AS export_price, list_prod.value_avg AS store_price, list_prod.q_avg AS store_q, (foreign_raw_mat_purc.price/list_prod.value) AS export_ratio, list_prod.value_avg/(list_prod.value * (1 + 0.02 * list_prod.q_avg)) AS store_ratio_qa, list_cat.sellable FROM (SELECT pid, MAX(price) AS max_price FROM foreign_raw_mat_purc GROUP BY pid) AS frmp LEFT JOIN foreign_raw_mat_purc ON frmp.pid = foreign_raw_mat_purc.pid AND frmp.max_price = foreign_raw_mat_purc.price LEFT JOIN list_prod ON foreign_raw_mat_purc.pid = list_prod.id LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id GROUP BY foreign_raw_mat_purc.pid ORDER BY list_prod.name ASC");
	$query->execute(array());
	$results = $query->fetchAll(PDO::FETCH_ASSOC);

	echo '<h3>Raw Material Market (Export)</h3>';
	echo '<table class="default_table default_table_smallfont" style="width: 100% !important;">';
	echo '<thead><tr><td>Product</td><td>Base Value</td><td>Export Price</td><td>Store Price</td><td>Store Q</td><td width="100px">Export Price Ratio</td><td width="100px">Store Price Ratio (Quality Adjusted)</td></tr></thead>';
	echo '<tbody>';
	foreach($results as $result){
		if($result["sellable"]){
			echo '<tr><td>',$result["name"],'</td><td>',$result["value"],'</td><td>',$result["export_price"],'</td><td>',$result["store_price"],'</td><td>',$result["store_q"],'</td><td>',number_format($result["export_ratio"],3,'.',','),'</td><td>',number_format($result["store_ratio_qa"],3,'.',','),'</td>';
		}else{
			echo '<tr><td>',$result["name"],'</td><td>',$result["value"],'</td><td>',$result["export_price"],'</td><td>N/A</td><td>N/A</td><td>',number_format($result["export_ratio"],3,'.',','),'</td><td>N/A</td>';
		}
	}
	echo '</tbody></table><br /><br />';
?>
		</div>
<?php require '../include/foot_subd.php'; ?>