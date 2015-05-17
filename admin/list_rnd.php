<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - R&amp;D List</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listRndController = {
				executeAjax: function(params, custCallback){
					$.ajax({
                        type: "POST", 
                        url: "list_rnd_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									if(params['target']){
										var div_id = params['target'];
									}else{
										var div_id = "list_rnd_"+params['rnd_id'];
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
				showEdit: function(rnd_id){
					var params = {action : 'show_edit', rnd_id : rnd_id};
					this.executeAjax(params, function(){
						var uploadParams = {action : 'ajaxupload', rnd_id : rnd_id, filename : rnd_id + '_up[]', maxSize : 5000000, relPath : '../images/rnd/', relPathThumb : 'large/', maxW : 180, maxH : 80, maxWThumb : 360, maxHThumb : 160};
						var extraParams = {subId : rnd_id, reqTitle : 0, uploadUrl : 'list_rnd_controller.php', uploadParams : uploadParams};
						DDInit('form_image_up', extraParams);
					});
				},
				editCancel: function(rnd_id){
					var params = {action : 'edit_cancel', rnd_id : rnd_id};
					this.executeAjax(params);
				},
				editConfirm: function(rnd_id){
					var name = document.getElementById("list_rnd_edit_"+rnd_id+"_name").value;
					var division_name = document.getElementById("list_rnd_edit_"+rnd_id+"_division_name").value;
					var firstcost = document.getElementById("list_rnd_edit_"+rnd_id+"_firstcost").value*100;
					var firsttimecost = document.getElementById("list_rnd_edit_"+rnd_id+"_firsttimecost").value;
					var cost = document.getElementById("list_rnd_edit_"+rnd_id+"_cost").value*100;
					var timecost = document.getElementById("list_rnd_edit_"+rnd_id+"_timecost").value;
					var params = {action : 'edit_confirm', rnd_id : rnd_id, name : name, division_name : division_name, firstcost : firstcost, firsttimecost : firsttimecost, cost : cost, timecost : timecost};
					this.executeAjax(params);
				},
				addRnd: function(){
					var name = document.getElementById("list_rnd_add_name").value;
					var division_name = document.getElementById("list_rnd_add_division_name").value;
					var firstcost = document.getElementById("list_rnd_add_firstcost").value*100;
					var firsttimecost = document.getElementById("list_rnd_add_firsttimecost").value;
					var cost = document.getElementById("list_rnd_add_cost").value*100;
					var timecost = document.getElementById("list_rnd_add_timecost").value;
					var params = {action : 'add_rnd', name : name, division_name : division_name, firstcost : firstcost, firsttimecost : firsttimecost, cost : cost, timecost : timecost};
					this.executeAjax(params);
				},
				addCanRes: function(rnd_id){
					var cat_id = document.getElementById("list_rnd_edit_"+rnd_id+"_add_cat_id").value;
					var div_id = "list_rnd_edit_"+rnd_id+"_can_res";
					var params = {action : 'add_can_res', target : div_id, rnd_id : rnd_id, cat_id : cat_id};
					this.executeAjax(params);
				},
				deleteCanRes: function(rnd_id, can_sell_id){
					var div_id = "list_rnd_edit_"+rnd_id+"_can_res";
					var params = {action : 'delete_can_res', target : div_id, rnd_id : rnd_id, can_sell_id : can_sell_id};
					this.executeAjax(params);
				}
			}
			jQuery(document).ready(function(){
				$('.edit_table').on('keypress', '.rnd_tr td input', function(e){
					if(e.which == 13){
						var rnd_id = $(this).closest('tr').attr('rnd_id');
						listRndController.editConfirm(rnd_id);
					}
				});
				$('.edit_table').on('keypress', '.add_rnd_tr td input', function(e){
					if(e.which == 13){
						listRndController.addRnd();
					}
				});
			});
		</script>
<?php require 'include/menu.php'; ?>

<?php
	$sql = "SELECT * FROM list_rnd ORDER BY name ASC";
	$rnds = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Image</td><td>Name</td><td>Division Name</td><td>Initial Cost ($)</td><td>I. Time Cost (s)</td><td>Expand Cost ($)</td><td>E. Time Cost (s)</td><td>Can Research</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($rnds as $rnd){
			$list_rnd_id = $rnd["id"];
			$name = $rnd["name"];
			$division_name = $rnd["division_name"];
			if($rnd["has_image"]){
				$filename = preg_replace(array("/[\s\&\']/","/_{2,}/"), '_', strtolower($name));
			}else{
				$filename = "no-image";
			}

			$sql = "SELECT list_rnd_choices.cat_id, list_cat.name AS cat_name FROM list_rnd_choices LEFT JOIN list_cat ON list_rnd_choices.cat_id = list_cat.id WHERE list_rnd_choices.rnd_id = $list_rnd_id ORDER BY list_cat.name ASC";
			$rnd_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if(!empty($rnd_choices)){
				$list_rnd_can_res = '';
				foreach($rnd_choices as $rnd_choice){
					$list_rnd_can_res .= $rnd_choice['cat_name'] . ',<br />';
				}
				$list_rnd_can_res = substr($list_rnd_can_res, 0, -7);
			}else{
				$list_rnd_can_res = '&lt;Nothing&gt;';
			}
			echo '<tr class="rnd_tr" id="list_rnd_'.$list_rnd_id.'" rnd_id="'.$list_rnd_id.'">';
			echo '<td><img src="/eos/images/rnd/'.$filename.'.gif" width="180px" height="80px" /></td><td>'.$name.'</td><td>'.$division_name.'</td><td>'.'$'.number_format($rnd["firstcost"]/100, 2, '.', ',').'</td><td>'.$rnd["firsttimecost"].' s'.'</td><td>'.'$'.number_format($rnd["cost"]/100, 2, '.', ',').'</td><td>'.$rnd["timecost"].' s'.'</td><td><small>'.$list_rnd_can_res.'</small></td><td><a style="cursor:pointer;" onclick="listRndController.showEdit(\''.$list_rnd_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr class="add_rnd_tr"><td>&nbsp;</td><td><input type="text" size="16" id="list_rnd_add_name" /></td><td><input type="text" size="16" id="list_rnd_add_division_name" /></td><td><input type="text" size="12" id="list_rnd_add_firstcost" /></td><td><input type="text" size="5" id="list_rnd_add_firsttimecost" /></td><td><input type="text" size="10" id="list_rnd_add_cost" /></td><td><input type="text" size="5" id="list_rnd_add_timecost" /></td><td>&nbsp;</td><td><a style="cursor:pointer;" onclick="listRndController.addRnd()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>