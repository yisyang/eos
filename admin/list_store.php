<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Stores List</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listStoreController = {
				executeAjax: function(params, custCallback){
					$.ajax({
                        type: "POST", 
                        url: "list_store_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									if(params['target']){
										var div_id = params['target'];
									}else{
										var div_id = "list_store_"+params['store_id'];
									}
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
				showEdit: function(store_id){
					var params = {action : 'show_edit', store_id : store_id};
					this.executeAjax(params, function(){
						var uploadParams = {action : 'ajaxupload', store_id : store_id, filename : store_id + '_up[]', maxSize : 5000000, relPath : '../images/store/', relPathThumb : 'large/', maxW : 180, maxH : 80, maxWThumb : 360, maxHThumb : 160};
						var extraParams = {subId : store_id, reqTitle : 0, uploadUrl : 'list_store_controller.php', uploadParams : uploadParams};
						DDInit('form_image_up', extraParams);
					});
				},
				editCancel: function(store_id){
					var params = {action : 'edit_cancel', store_id : store_id};
					this.executeAjax(params);
				},
				editConfirm: function(store_id){
					var name = document.getElementById("list_store_edit_"+store_id+"_name").value;
					var division_name = document.getElementById("list_store_edit_"+store_id+"_division_name").value;
					var firstcost = document.getElementById("list_store_edit_"+store_id+"_firstcost").value*100;
					var firsttimecost = document.getElementById("list_store_edit_"+store_id+"_firsttimecost").value;
					var cost = document.getElementById("list_store_edit_"+store_id+"_cost").value*100;
					var timecost = document.getElementById("list_store_edit_"+store_id+"_timecost").value;
					var params = {action : 'edit_confirm', store_id : store_id, name : name, division_name : division_name, firstcost : firstcost, firsttimecost : firsttimecost, cost : cost, timecost : timecost};
					this.executeAjax(params);
				},
				addStore: function(){
					var name = document.getElementById("list_store_add_name").value;
					var division_name = document.getElementById("list_store_add_division_name").value;
					var firstcost = document.getElementById("list_store_add_firstcost").value*100;
					var firsttimecost = document.getElementById("list_store_add_firsttimecost").value;
					var cost = document.getElementById("list_store_add_cost").value*100;
					var timecost = document.getElementById("list_store_add_timecost").value;
					var params = {action : 'add_store', name : name, division_name : division_name, firstcost : firstcost, firsttimecost : firsttimecost, cost : cost, timecost : timecost};
					this.executeAjax(params);
				},
				addCanSell: function(store_id){
					var cat_id = document.getElementById("list_store_edit_"+store_id+"_add_cat_id").value;
					var div_id = "list_store_edit_"+store_id+"_can_sell";
					var params = {action : 'add_can_sell', target : div_id, store_id : store_id, cat_id : cat_id};
					this.executeAjax(params);
				},
				deleteCanSell: function(store_id, can_sell_id){
					var div_id = "list_store_edit_"+store_id+"_can_sell";
					var params = {action : 'delete_can_sell', target : div_id, store_id : store_id, can_sell_id : can_sell_id};
					this.executeAjax(params);
				}
			}
			jQuery(document).ready(function(){
				$('.edit_table').on('keypress', '.store_tr td input', function(e){
					if(e.which == 13){
						var store_id = $(this).closest('tr').attr('store_id');
						listStoreController.editConfirm(store_id);
					}
				});
				$('.edit_table').on('keypress', '.add_store_tr td input', function(e){
					if(e.which == 13){
						listStoreController.addStore();
					}
				});
			});
		</script>
<?php require 'include/menu.php'; ?>

<?php
	$sql = "SELECT * FROM list_store ORDER BY name ASC";
	$stores = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Image</td><td>Name</td><td>Division Name</td><td>Initial Cost ($)</td><td>I. Time Cost (s)</td><td>Expand Cost ($)</td><td>E. Time Cost (s)</td><td>Can Sell</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($stores as $store){
			$list_store_id = $store["id"];
			$name = $store["name"];
			$division_name = $store["division_name"];
			if($store["has_image"]){
				$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
			}else{
				$filename = "no-image";
			}
			$sql = "SELECT list_store_choices.cat_id, list_cat.name AS cat_name FROM list_store_choices LEFT JOIN list_cat ON list_store_choices.cat_id = list_cat.id WHERE list_store_choices.store_id = $list_store_id ORDER BY list_cat.name ASC";
			$store_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if(!empty($store_choices)){
				$list_store_can_sell = '';
				foreach($store_choices as $store_choice){
					$list_store_can_sell .= $store_choice['cat_name'] . ',<br />';
				}
				$list_store_can_sell = substr($list_store_can_sell, 0, -7);
			}else{
				$list_store_can_sell = '&lt;Nothing&gt;';
			}
			echo '<tr class="store_tr" id="list_store_'.$list_store_id.'" store_id="'.$list_store_id.'">';
			echo '<td><img src="/eos/images/store/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($store["firstcost"]/100, 2, '.', ',').'</td><td>'.$store["firsttimecost"].' s'.'</td><td>'.'$'.number_format($store["cost"]/100, 2, '.', ',').'</td><td>'.$store["timecost"].' s'.'</td><td><small>'.$list_store_can_sell.'</small></td><td><a style="cursor:pointer;" onclick="listStoreController.showEdit(\''.$list_store_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr class="add_store_tr"><td>&nbsp;</td><td><input type="text" size="16" id="list_store_add_name" /></td><td><input type="text" size="16" id="list_store_add_division_name" /></td><td><input type="text" size="12" id="list_store_add_firstcost" /></td><td><input type="text" size="5" id="list_store_add_firsttimecost" /></td><td><input type="text" size="10" id="list_store_add_cost" /></td><td><input type="text" size="5" id="list_store_add_timecost" /></td><td>&nbsp;</td><td><a style="cursor:pointer;" onclick="listStoreController.addStore()">[Add]</a></td></tr>
	</tfoot>
</table>
<?php require 'include/foot.php'; ?>