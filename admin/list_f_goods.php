<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Foreign Companies List - Goods</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listFGoodsController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "list_f_goods_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									var div_id = "list_f_goods_"+params['goods_id'];
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
				showEdit: function(goods_id){
					var params = {action: 'show_edit', goods_id : goods_id};
					this.executeAjax(params);
				},
				editCancel: function(goods_id){
					var params = {action: 'edit_cancel', goods_id : goods_id};
					this.executeAjax(params);
				},
				editConfirm: function(goods_id){
					var fc_id = document.getElementById("list_f_goods_edit_"+goods_id+"_fcid").value;
					var cat_id = document.getElementById("list_f_goods_edit_"+goods_id+"_cat_id").value;
					var quality = document.getElementById("list_f_goods_edit_"+goods_id+"_quality").value;
					var value_to_sell = 100 * document.getElementById("list_f_goods_edit_"+goods_id+"_value_to_sell").value;
					var price_multiplier = document.getElementById("list_f_goods_edit_"+goods_id+"_price_multiplier").value;
					var params = {action: 'edit_confirm', goods_id : goods_id, fc_id : fc_id, cat_id : cat_id, quality : quality, value_to_sell : value_to_sell, price_multiplier : price_multiplier};
					this.executeAjax(params);
				},
				addGoods: function(){
					var fc_id = document.getElementById("list_f_goods_add_fcid").value;
					var cat_id = document.getElementById("list_f_goods_add_cat_id").value;
					var quality = document.getElementById("list_f_goods_add_quality").value;
					var value_to_sell = 100 * document.getElementById("list_f_goods_add_value_to_sell").value;
					var price_multiplier = document.getElementById("list_f_goods_add_price_multiplier").value;
					var params = {action: 'add_goods', fc_id : fc_id, cat_id : cat_id, quality : quality, value_to_sell : value_to_sell, price_multiplier : price_multiplier};
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

	//$sql = "SELECT * FROM foreign_list_goods ORDER BY name ASC";
	$sql = "SELECT foreign_list_goods.*, foreign_companies.name AS fc_name, list_cat.name AS cat_name FROM foreign_list_goods LEFT JOIN foreign_companies ON foreign_list_goods.fcid = foreign_companies.id LEFT JOIN list_cat ON foreign_list_goods.cat_id = list_cat.id ORDER BY fc_name ASC, cat_name ASC";
	$f_goods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Company Name</td><td>Category Name</td><td>Quality</td><td>Value to Sell</td><td>Price Multiplier</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		if(count($f_goods)){
			foreach($f_goods as $f_good){
				$list_f_goods_id = $f_good["id"];
				echo '<tr id="list_f_goods_'.$list_f_goods_id.'"><td>'.$f_good["fc_name"].'</td><td>'.$f_good["cat_name"].'</td><td>'.$f_good["quality"].'</td><td>$'.number_format($f_good["value_to_sell"]/100,2,'.',',').'</td><td>'.$f_good["price_multiplier"].'</td><td><a style="cursor:pointer;" onclick="listFGoodsController.showEdit(\''.$list_f_goods_id.'\')">[Edit]</a></td></tr>';
			}
		}
	?>
	</tbody>
	<tfoot>
		<tr>
			<td>
				<select id="list_f_goods_add_fcid">
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
				<select id="list_f_goods_add_cat_id">
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
			<td><input type="text" size="12" id="list_f_goods_add_quality" /></td><td><input type="text" size="12" id="list_f_goods_add_value_to_sell" /></td><td><input type="text" size="12" id="list_f_goods_add_price_multiplier" /></td><td><a style="cursor:pointer;" onclick="listFGoodsController.addGoods()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>