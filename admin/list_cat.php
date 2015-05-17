<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Categories List</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listCatController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "list_cat_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									var div_id = "list_cat_"+params['cat_id'];
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
				showEdit: function(cat_id){
					var params = {action: 'show_edit', cat_id : cat_id};
					this.executeAjax(params);
				},
				editCancel: function(cat_id){
					var params = {action: 'edit_cancel', cat_id : cat_id};
					this.executeAjax(params);
				},
				editConfirm: function(cat_id){
					var name = document.getElementById("list_cat_edit_"+cat_id+"_name").value;
					var price_multiplier = document.getElementById("list_cat_edit_"+cat_id+"_price_multiplier").value;
					var params = {action: 'edit_confirm', cat_id : cat_id, name : name, price_multiplier : price_multiplier};
					this.executeAjax(params);
				},
				addCat: function(){
					var name = document.getElementById("list_cat_add_name").value;
					var price_multiplier = document.getElementById("list_cat_add_price_multiplier").value;
					var params = {action: 'add_cat', name : name, price_multiplier : price_multiplier};
					this.executeAjax(params);
				}
			}
		</script>
<?php require 'include/menu.php'; ?>
<?php
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Name</td><td>Price Multiplier</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($cats as $cat){
			$cat_id = $cat["id"];
			echo '<tr id="list_cat_'.$cat_id.'"><td>'.$cat["name"].'</td><td>'.number_format($cat["price_multiplier"], 2, '.', '').'</td><td><a style="cursor:pointer;" onclick="listCatController.showEdit(\''.$cat_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr><td><input type="text" size="24" id="list_cat_add_name" /></td><td><input type="text" size="12" id="list_cat_add_price_multiplier" /></td><td><a style="cursor:pointer;" onclick="listCatController.addCat()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>