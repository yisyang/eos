<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Foreign Companies List - Raw Materials</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listFRMController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "list_f_purcs_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									var div_id = "list_f_purcs_"+params['mat_id'];
									document.getElementById(div_id).innerHTML = resp.html;
								}else{
									window.location.reload();
								}
							}else{
								if(typeof(resp.msg) !== 'undefined' && resp.msg){
									jAlert(resp.msg); 
								}else{
									jAlert('Something went wrong');
								}
							}
						},
                        error: function(xhr, ajaxOptions, thrownError){ alert(xhr.responseText); }
                    });
				},
				showEdit: function(mat_id){
					var params = {action: 'show_edit', mat_id : mat_id};
					this.executeAjax(params);
				},
				editCancel: function(mat_id){
					var params = {action: 'edit_cancel', mat_id : mat_id};
					this.executeAjax(params);
				},
				editConfirm: function(mat_id){
					var fc_id = document.getElementById("list_f_purcs_edit_"+mat_id+"_fcid").value;
					var cat_id = document.getElementById("list_f_purcs_edit_"+mat_id+"_cat_id").value;
					var value_to_buy = 100 * document.getElementById("list_f_purcs_edit_"+mat_id+"_value_to_buy").value;
					var price_multiplier = document.getElementById("list_f_purcs_edit_"+mat_id+"_price_multiplier").value;
					var params = {action: 'edit_confirm', mat_id : mat_id, fc_id : fc_id, cat_id : cat_id, value_to_buy : value_to_buy, price_multiplier : price_multiplier};
					this.executeAjax(params);
				},
				addMat: function(){
					var fc_id = document.getElementById("list_f_purcs_add_fcid").value;
					var cat_id = document.getElementById("list_f_purcs_add_cat_id").value;
					var value_to_buy = 100 * document.getElementById("list_f_purcs_add_value_to_buy").value;
					var price_multiplier = document.getElementById("list_f_purcs_add_price_multiplier").value;
					var params = {action: 'add_mat', fc_id : fc_id, cat_id : cat_id, value_to_buy : value_to_buy, price_multiplier : price_multiplier};
					this.executeAjax(params);
				}
			}
		</script>
<?php require 'include/menu.php'; ?>
<?php
	//Initialize FCs
	$sql = "SELECT * FROM foreign_companies ORDER BY name ASC";
	$foreign_companies = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$list_cat = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	//$sql = "SELECT * FROM foreign_list_purcs ORDER BY name ASC";
	$sql = "SELECT foreign_list_purcs.*, foreign_companies.name AS fc_name, list_cat.name AS cat_name FROM foreign_list_purcs LEFT JOIN foreign_companies ON foreign_list_purcs.fcid = foreign_companies.id LEFT JOIN list_cat ON foreign_list_purcs.cat_id = list_cat.id ORDER BY fc_name ASC, cat_name ASC";
	$f_mats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Company Name</td><td>Category Name</td><td>Value to Buy</td><td>Price Multiplier</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		if(count($f_mats)){
			foreach($f_mats as $f_mat){
				$list_f_purcs_id = $f_mat["id"];
				echo '<tr id="list_f_purcs_'.$list_f_purcs_id.'"><td>'.$f_mat["fc_name"].'</td><td>'.$f_mat["cat_name"].'</td><td>$'.number_format($f_mat["value_to_buy"]/100,2,'.',',').'</td><td>'.$f_mat["price_multiplier"].'</td><td><a style="cursor:pointer;" onclick="listFRMController.showEdit(\''.$list_f_purcs_id.'\')">[Edit]</a></td></tr>';
			}
		}
	?>
	</tbody>
	<tfoot>
		<tr>
			<td>
				<select id="list_f_purcs_add_fcid">
					<?php
						if(count($foreign_companies)){
							echo '<option value=""> </option>';
							foreach($foreign_companies as $foreign_company){
								echo '<option value="'.$foreign_company["id"].'">'.$foreign_company["name"].'</option>';
							}
						}
					?>
				</select>
			</td>
			<td>
				<select id="list_f_purcs_add_cat_id">
					<?php
						if(count($list_cat)){
							echo '<option value=""> </option>';
							foreach($list_cat as $cat){
								echo '<option value="'.$cat["id"].'">'.$cat["name"].'</option>';
							}
						}
					?>
				</select>
			</td>
			<td><input type="text" size="12" id="list_f_purcs_add_value_to_buy" /></td><td><input type="text" size="12" id="list_f_purcs_add_price_multiplier" /></td><td><a style="cursor:pointer;" onclick="listFRMController.addMat()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>