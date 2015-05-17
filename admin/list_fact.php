<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Factories List</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listFactController = {
				executeAjax: function(params, custCallback){
					$.ajax({
                        type: "POST", 
                        url: "list_fact_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									if(params['target']){
										var div_id = params['target'];
									}else{
										var div_id = "list_fact_"+params['fact_id'];
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
				showEdit: function(fact_id){
					var params = {action : 'show_edit', fact_id : fact_id};
					this.executeAjax(params, function(){
						var uploadParams = {action : 'ajaxupload', fact_id : fact_id, filename : fact_id + '_up[]', maxSize : 5000000, relPath : '../images/fact/', relPathThumb : 'large/', maxW : 180, maxH : 80, maxWThumb : 360, maxHThumb : 160};
						var extraParams = {subId : fact_id, reqTitle : 0, uploadUrl : 'list_fact_controller.php', uploadParams : uploadParams};
						DDInit('form_image_up', extraParams);
					});
				},
				editCancel: function(fact_id){
					var params = {action : 'edit_cancel', fact_id : fact_id};
					this.executeAjax(params);
				},
				editConfirm: function(fact_id){
					var name = document.getElementById("list_fact_edit_"+fact_id+"_name").value;
					var division_name = document.getElementById("list_fact_edit_"+fact_id+"_division_name").value;
					var firstcost = document.getElementById("list_fact_edit_"+fact_id+"_firstcost").value*100;
					var firsttimecost = document.getElementById("list_fact_edit_"+fact_id+"_firsttimecost").value;
					var cost = document.getElementById("list_fact_edit_"+fact_id+"_cost").value*100;
					var timecost = document.getElementById("list_fact_edit_"+fact_id+"_timecost").value;
					var params = {action : 'edit_confirm', fact_id : fact_id, name : name, division_name : division_name, firstcost : firstcost, firsttimecost : firsttimecost, cost : cost, timecost : timecost};
					this.executeAjax(params);
				},
				addFact: function(){
					var name = document.getElementById("list_fact_add_name").value;
					var division_name = document.getElementById("list_fact_add_division_name").value;
					var firstcost = document.getElementById("list_fact_add_firstcost").value*100;
					var firsttimecost = document.getElementById("list_fact_add_firsttimecost").value;
					var cost = document.getElementById("list_fact_add_cost").value*100;
					var timecost = document.getElementById("list_fact_add_timecost").value;
					var params = {action : 'add_fact', name : name, division_name : division_name, firstcost : firstcost, firsttimecost : firsttimecost, cost : cost, timecost : timecost};
					this.executeAjax(params);
				}
			}
			jQuery(document).ready(function(){
				$('.edit_table').on('keypress', '.fact_tr td input', function(e){
					if(e.which == 13){
						var fact_id = $(this).closest('tr').attr('fact_id');
						listFactController.editConfirm(fact_id);
					}
				});
				$('.edit_table').on('keypress', '.add_fact_tr td input', function(e){
					if(e.which == 13){
						listFactController.addFact();
					}
				});
			});
		</script>
<?php require 'include/menu.php'; ?>

<?php
	$sql = "SELECT * FROM list_fact ORDER BY name ASC";
	$facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Image</td><td>Name</td><td>Division Name</td><td>Initial Cost ($)</td><td>Initial Time Cost (s)</td><td>Expand Cost ($)</td><td>Expand Time Cost (s)</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($facts as $fact){
			$list_fact_id = $fact["id"];
			$name = $fact["name"];
			$division_name = $fact["division_name"];
			if($fact["has_image"]){
				$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
			}else{
				$filename = "no-image";
			}
			echo '<tr class="fact_tr" id="list_fact_'.$list_fact_id.'" fact_id="'.$list_fact_id.'">';
			echo '<td><img src="/eos/images/fact/'.$filename.'.gif" width="180" height="80" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($fact["firstcost"]/100, 2, '.', ',').'</td><td>'.$fact["firsttimecost"].' s'.'</td><td>'.'$'.number_format($fact["cost"]/100, 2, '.', ',').'</td><td>'.$fact["timecost"].' s'.'</td><td><a style="cursor:pointer;" onclick="listFactController.showEdit(\''.$list_fact_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr class="add_fact_tr"><td>&nbsp;</td><td><input type="text" size="16" id="list_fact_add_name" /></td><td><input type="text" size="16" id="list_fact_add_division_name" /></td><td><input type="text" size="12" id="list_fact_add_firstcost" /></td><td><input type="text" size="5" id="list_fact_add_firsttimecost" /></td><td><input type="text" size="10" id="list_fact_add_cost" /></td><td><input type="text" size="5" id="list_fact_add_timecost" /></td><td><a style="cursor:pointer;" onclick="listFactController.addFact()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>