<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Products List</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listProdController = {
				executeAjax: function(params, custCallback){
					$.ajax({
                        type: "POST", 
                        url: "list_prod_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									var div_id = "list_prod_"+params['prod_id'];
									document.getElementById(div_id).innerHTML = resp.html;
									if(typeof(custCallback) == 'function'){
										custCallback();
									}
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
				showEdit: function(prod_id){
					var params = {action : 'show_edit', prod_id : prod_id};
					this.executeAjax(params, function(){
						var uploadParams = {action : 'ajaxupload', prod_id : prod_id, filename : prod_id + '_up[]', maxSize : 5000000, relPath : '../images/prod/', maxW : 24, maxH : 24, maxWThumb : 96, maxHThumb : 96};
						var extraParams = {subId : prod_id, reqTitle : 0, uploadUrl : 'list_prod_controller.php', uploadParams : uploadParams};
						DDInit('form_image_up', extraParams);
					});
				},
				editCancel: function(prod_id){
					var params = {action : 'edit_cancel', prod_id : prod_id};
					this.executeAjax(params);
				},
				editConfirm: function(prod_id){
					var name = document.getElementById("list_prod_edit_"+prod_id+"_name").value;
					var cat_id = document.getElementById("list_prod_edit_"+prod_id+"_cat_id").value;
					var value = document.getElementById("list_prod_edit_"+prod_id+"_value").value*100;
					var selltime = document.getElementById("list_prod_edit_"+prod_id+"_selltime").value;
					var res_cost = document.getElementById("list_prod_edit_"+prod_id+"_res_cost").value*100;
					var res_dep_1 = document.getElementById("list_prod_edit_"+prod_id+"_res_dep_1").value;
					var res_dep_2 = document.getElementById("list_prod_edit_"+prod_id+"_res_dep_2").value;
					var res_dep_3 = document.getElementById("list_prod_edit_"+prod_id+"_res_dep_3").value;
					var params = {action : 'edit_confirm', prod_id : prod_id, name : name, cat_id : cat_id, value : value, selltime : selltime, res_cost : res_cost, res_dep_1 : res_dep_1, res_dep_2 : res_dep_2, res_dep_3 : res_dep_3};
					this.executeAjax(params);
				},
				addProd: function(){
					var name = document.getElementById("list_prod_add_name").value;
					var cat_id = document.getElementById("list_prod_add_cat_id").value;
					var value = document.getElementById("list_prod_add_value").value*100;
					var selltime = document.getElementById("list_prod_add_selltime").value;
					var res_cost = document.getElementById("list_prod_add_res_cost").value*100;
					var res_dep_1 = document.getElementById("list_prod_add_res_dep_1").value;
					var res_dep_2 = document.getElementById("list_prod_add_res_dep_2").value;
					var res_dep_3 = document.getElementById("list_prod_add_res_dep_3").value;
					var params = {action : 'add_prod', name : name, cat_id : cat_id, value : value, selltime : selltime, res_cost : res_cost, res_dep_1 : res_dep_1, res_dep_2 : res_dep_2, res_dep_3 : res_dep_3};
					this.executeAjax(params);
				}
			}
			jQuery(document).ready(function(){
				$('.edit_table').on('keypress', '.prod_tr td input', function(e){
					if(e.which == 13){
						var prod_id = $(this).closest('tr').attr('prod_id');
						listProdController.editConfirm(prod_id);
					}
				});
				$('.edit_table').on('keypress', '.add_prod_tr td input', function(e){
					if(e.which == 13){
						listProdController.addProd();
					}
				});
			});
		</script>
<?php require 'include/menu.php'; ?>

<?php
	//Initialize Cats
	$sql = "SELECT * FROM list_cat ORDER BY name ASC";
	$cats = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	//Initialize Res Deps
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$res_deps = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	//Standard
	$sort_by = 'default';
	if(isset($_GET['sortby']) && $_GET['sortby']){
		$sort_by = $_GET['sortby'];
	}
	switch($sort_by){
		case 'cat':
			$sql_sort = 'ORDER BY list_cat.name ASC, list_prod.name ASC';
			break;
		case 'value':
			$sql_sort = 'ORDER BY list_prod.value ASC';
			break;
		default:
			$sql_sort = 'ORDER BY list_prod.name ASC';
			break;
	}
	$sql = "SELECT list_prod.*, list_cat.name AS cat_name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id $sql_sort";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($prods as $prod){
		$filename_temp = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($prod["name"]));
		if($prod["has_icon"]){
			$prod_img[$prod["id"]] = '<img src="/eos/images/prod/'.$filename_temp.'.gif" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
		}else{
			$prod_img[$prod["id"]] = '<img src="/eos/images/prod/no-icon.gif" width="24" height="24" alt="'.$prod["name"].'" title="'.$prod["id"].' - '.$prod["name"].'" />';
		}
	}
?>
<table class="edit_table">
	<thead>
		<tr><td>Icon</td><td><a href="?sortby=name">Name</a></td><td><a href="?sortby=cat">Category</a></td><td><a href="?sortby=value">Value ($)</a></td><td>Sell Time (s)</td><td>Research Cost</td><td>Res. Dependencies</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($prods as $prod){
			$prod_id = $prod["id"];
			
			echo '<tr class="prod_tr" id="list_prod_'.$prod_id.'" prod_id="'.$prod_id.'">';
			echo '<td>',$prod_img[$prod_id],'</td>';
			echo '<td>'.$prod["name"].' ('.$prod_id.')</td><td>'.$prod["cat_name"].'</td><td>'.'$'.number_format($prod["value"]/100, 2, '.', ',').'</td><td>'.$prod["selltime"].' s'.'</td><td>'.'$'.number_format($prod["res_cost"]/100, 2, '.', ',').'</td><td>';
			if($res_dep = $prod["res_dep_1"]){
				echo $prod_img[$res_dep];
				if($res_dep = $prod["res_dep_2"]){
					echo ' '.$prod_img[$res_dep];
					if($res_dep = $prod["res_dep_3"]){
						echo ' '.$prod_img[$res_dep];
					}
				}
			}else{
				echo '&nbsp;';
			}
			echo '</td><td><a style="cursor:pointer;" onclick="listProdController.showEdit(\''.$prod_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr class="add_prod_tr"><td></td>
		<td><input type="text" size="16" id="list_prod_add_name" /></td>
		<td>
			<select id="list_prod_add_cat_id">
				<?php
					if(!empty($cats)){
						echo '<option value=""> </option>';
						foreach($cats as $cat){
							echo '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
						}
					}
				?>
			</select>
		</td>
		<td><input type="text" size="10" id="list_prod_add_value" /></td>
		<td><input type="text" size="5" id="list_prod_add_selltime" /></td>
		<td><input type="text" size="5" id="list_prod_add_res_cost" onblur="list_prod_add()" /></td>
		<td>
			<select id="list_prod_add_res_dep_1" class="select_100px">
				<?php
					if(!empty($res_deps)){
						echo '<option value=""> </option>';
						foreach($res_deps as $res_dep){
							echo '<option value="'.$res_dep['id'].'">'.$res_dep['name'].'</option>';
						}
					}
				?>
			</select><br />
			<select id="list_prod_add_res_dep_2" class="select_100px">
				<?php
					if(!empty($res_deps)){
						echo '<option value=""> </option>';
						foreach($res_deps as $res_dep){
							echo '<option value="'.$res_dep['id'].'">'.$res_dep['name'].'</option>';
						}
					}
				?>
			</select><br />
			<select id="list_prod_add_res_dep_3" class="select_100px">
				<?php
					if(!empty($res_deps)){
						echo '<option value=""> </option>';
						foreach($res_deps as $res_dep){
							echo '<option value="'.$res_dep['id'].'">'.$res_dep['name'].'</option>';
						}
					}
				?>
			</select><br />
		<td><a style="cursor:pointer;" onclick="listProdController.addProd()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>