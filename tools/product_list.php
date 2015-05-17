<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - Product List</title>
<?php require '../include/head_subd.php'; ?>
		<div class="subd_body">
<?php
	$query = $db->prepare("SELECT list_prod.id, list_prod.name, list_prod.value FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id AND list_cat.name NOT LIKE '-%' ORDER BY list_prod.name ASC");
	$query->execute(array());
	$results = $query->fetchAll(PDO::FETCH_ASSOC);
	$search_result_count = count($results);
	
	echo '<h3>Product List</h3>';
	if($search_result_count){
		echo 'All prices are in cents.<br /><br />';
		echo '<table class="default_table default_table_smallfont" style="width: 100% !important;"><thead><tr><td>Product</td><td>Base Value</td></tr></thead>';
		//Populate Search Results
		foreach($results as $result){
			echo '<tbody><tr>';
			echo '<td>',$result['name'],'</td>';
			echo '<td>',$result['value'],'</td>';
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