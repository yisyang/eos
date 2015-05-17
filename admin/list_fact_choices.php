<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Factory Choices List</title>
<?php require 'include/head_nomenu.php'; ?>
		<script type="text/javascript">
			var listFactChoicesController = {
				executeAjax: function(params, custCallback){
					$.ajax({
                        type: "POST", 
                        url: "list_fact_choices_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									if(params['target']){
										var div_id = params['target'];
									}else{
										var div_id = "list_fact_choices_"+params['fact_choice_id'];
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
				showEdit: function(fact_choice_id){
					var params = {action : 'show_edit', fact_choice_id : fact_choice_id};
					this.executeAjax(params);
				},
				editCancel: function(fact_choice_id){
					var params = {action : 'edit_cancel', fact_choice_id : fact_choice_id};
					this.executeAjax(params);
				},
				editConfirm: function(fact_choice_id){
					var fact_id = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_fact_id").value;
					var cost = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_cost").value*100;
					var timecost = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_timecost").value;
					var ipid1 = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid1").value;
					var ipid1n = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid1n").value;
					var ipid1qm = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid1qm").value;
					var ipid2 = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid2").value;
					var ipid2n = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid2n").value;
					var ipid2qm = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid2qm").value;
					var ipid3 = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid3").value;
					var ipid3n = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid3n").value;
					var ipid3qm = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid3qm").value;
					var ipid4 = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid4").value;
					var ipid4n = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid4n").value;
					var ipid4qm = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_ipid4qm").value;
					var opid1 = document.getElementById("list_fact_choices_edit_"+fact_choice_id+"_opid1").value;

					var params = {action : 'edit_confirm', fact_choice_id : fact_choice_id, fact_id : fact_id, cost : cost, timecost : timecost, ipid1 : ipid1, ipid1n : ipid1n, ipid1qm : ipid1qm, ipid2 : ipid2, ipid2n : ipid2n, ipid2qm : ipid2qm, ipid3 : ipid3, ipid3n : ipid3n, ipid3qm : ipid3qm, ipid4 : ipid4, ipid4n : ipid4n, ipid4qm : ipid4qm, opid1 : opid1};
					this.executeAjax(params);
				},
				addFactChoice: function(){
					var fact_id = document.getElementById("list_fact_choices_add_fact_id").value;
					var cost = document.getElementById("list_fact_choices_add_cost").value*100;
					var timecost = document.getElementById("list_fact_choices_add_timecost").value;
					var ipid1 = document.getElementById("list_fact_choices_add_ipid1").value;
					var ipid1n = document.getElementById("list_fact_choices_add_ipid1n").value;
					var ipid1qm = document.getElementById("list_fact_choices_add_ipid1qm").value;
					var ipid2 = document.getElementById("list_fact_choices_add_ipid2").value;
					var ipid2n = document.getElementById("list_fact_choices_add_ipid2n").value;
					var ipid2qm = document.getElementById("list_fact_choices_add_ipid2qm").value;
					var ipid3 = document.getElementById("list_fact_choices_add_ipid3").value;
					var ipid3n = document.getElementById("list_fact_choices_add_ipid3n").value;
					var ipid3qm = document.getElementById("list_fact_choices_add_ipid3qm").value;
					var ipid4 = document.getElementById("list_fact_choices_add_ipid4").value;
					var ipid4n = document.getElementById("list_fact_choices_add_ipid4n").value;
					var ipid4qm = document.getElementById("list_fact_choices_add_ipid4qm").value;
					var opid1 = document.getElementById("list_fact_choices_add_opid1").value;

					var params = {action : 'add_fact_choice', fact_id : fact_id, cost : cost, timecost : timecost, ipid1 : ipid1, ipid1n : ipid1n, ipid1qm : ipid1qm, ipid2 : ipid2, ipid2n : ipid2n, ipid2qm : ipid2qm, ipid3 : ipid3, ipid3n : ipid3n, ipid3qm : ipid3qm, ipid4 : ipid4, ipid4n : ipid4n, ipid4qm : ipid4qm, opid1 : opid1};
					this.executeAjax(params);
				}
			}
			jQuery(document).ready(function(){
				$('.edit_table').on('keypress', '.fact_choice_tr td input', function(e){
					if(e.which == 13){
						var fact_choice_id = $(this).closest('tr').attr('fact_choice_id');
						listFactChoicesController.editConfirm(fact_choice_id);
					}
				});
				$('.edit_table').on('keypress', '.add_fact_choice_tr td input', function(e){
					if(e.which == 13){
						listFactChoicesController.addFactChoice();
					}
				});
			});
		</script>
<?php require 'include/menu_nomenu.php'; ?>

<?php
	//Initialize Factories
	$sql = "SELECT * FROM list_fact ORDER BY name ASC";
	$facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//Initialize Products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$prods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$prod_options = '<option value=""> </option>';
	$prod_name[0] = '';
	$prod_name[null] = '';
	foreach($prods as $prod){
		$prod_name[$prod["id"]] = $prod["name"];
		// $prod_value[$prod["id"]] = $prod["value"];
		$prod_options .= '<option value="'.$prod["id"].'">'.$prod["name"].' ($'.number_format($prod["value"]/100, 2, ".", ",").')</option>';
	}
	
	//Search for Factory Choices
	$sql = "SELECT list_fact_choices.*, list_fact.name AS fact_name FROM list_fact_choices LEFT JOIN list_prod ON list_fact_choices.opid1 = list_prod.id LEFT JOIN list_fact ON list_fact_choices.fact_id = list_fact.id ORDER BY list_fact.name ASC, list_prod.name ASC";
	//$sql = "SELECT lfc.*, (lfc.cost - (op.value - (IFNULL(ip1.value * lfc.ipid1n,0) + IFNULL(ip2.value * lfc.ipid2n,0) + IFNULL(ip3.value * lfc.ipid3n,0) + IFNULL(ip4.value * lfc.ipid4n,0)) - list_cat.va_tc*lfc.timecost)) AS cost_diff FROM list_fact_choices AS lfc LEFT JOIN list_prod AS op ON lfc.opid1 = op.id LEFT JOIN list_cat ON op.cat_id = list_cat.id LEFT JOIN list_prod AS ip1 ON lfc.ipid1 = ip1.id LEFT JOIN list_prod AS ip2 ON lfc.ipid2 = ip2.id LEFT JOIN list_prod AS ip3 ON lfc.ipid3 = ip3.id LEFT JOIN list_prod AS ip4 ON lfc.ipid4= ip4.id ORDER BY cost_diff ASC";
	$fact_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="padding: 0 5px 5px 5px;"><a style="font-size: 9px;" href="list_fact_choices_w_menu.php">Menu hidden (click here to show Menu)</a></div>
<table class="edit_table smallfont" style="margin: 0 auto;">
	<thead>
		<tr><td>Factory</td><td>Output</td><td>Cost ($)</td><td>Time Cost (s)</td><td>Input 1</td><td>#</td><td>Qual. Multi.</td><td>Input 2</td><td>#</td><td>Qual. Multi.</td><td>Input 3</td><td>#</td><td>Qual. Multi.</td><td>Input 4</td><td>#</td><td>Qual. Multi.</td><td>Actions</td></tr>
	</thead>
	<tbody style="height: 650px; overflow-y: auto; overflow-x: hidden;">
	<?php
		foreach($fact_choices as $fact_choice){
			$list_fact_choice_id = $fact_choice["id"];
			echo '<tr class="fact_choice_tr" id="list_fact_choices_'.$list_fact_choice_id.'" fact_choice_id="'.$list_fact_choice_id.'">';
			echo '<td>'.$fact_choice["fact_name"].'</td><td>OP '.$prod_name[$fact_choice["opid1"]].'</td>
			<td>'.'$'.number_format($fact_choice["cost"]/100, 2, '.', ',').'</td><td>'.$fact_choice["timecost"].' s'.'</td>
			<td>'.$prod_name[$fact_choice["ipid1"]].'</td><td>'.$fact_choice["ipid1n"].'</td><td>'.$fact_choice["ipid1qm"].'</td>
			<td>'.$prod_name[$fact_choice["ipid2"]].'</td><td>'.$fact_choice["ipid2n"].'</td><td>'.$fact_choice["ipid2qm"].'</td>
			<td>'.$prod_name[$fact_choice["ipid3"]].'</td><td>'.$fact_choice["ipid3n"].'</td><td>'.$fact_choice["ipid3qm"].'</td>
			<td>'.$prod_name[$fact_choice["ipid4"]].'</td><td>'.$fact_choice["ipid4n"].'</td><td>'.$fact_choice["ipid4qm"].'</td>
			<td><a style="cursor:pointer;" onclick="listFactChoicesController.showEdit(\''.$list_fact_choice_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr class="add_fact_choice_tr">
			<td>
				<select id="list_fact_choices_add_fact_id">
					<?php
						foreach($facts as $fact){
							echo '<option value="'.$fact["id"].'">'.$fact["name"].'</option>';
						}
					?>
				</select>
			</td>
			<td>
				<select id="list_fact_choices_add_opid1">
					<?= $prod_options ?>
				</select>
			</td>
			<td><input type="text" size="12" id="list_fact_choices_add_cost" /></td><td><input type="text" size="5" id="list_fact_choices_add_timecost" /></td>
			<td>
				<select id="list_fact_choices_add_ipid1">
					<?= $prod_options ?>
				</select>
			</td>
			<td><input type="text" size="5" id="list_fact_choices_add_ipid1n" /></td><td><input type="text" size="5" id="list_fact_choices_add_ipid1qm" /></td>
			<td>
				<select id="list_fact_choices_add_ipid2">
					<?= $prod_options ?>
				</select>
			</td>
			<td><input type="text" size="5" id="list_fact_choices_add_ipid2n" /></td><td><input type="text" size="5" id="list_fact_choices_add_ipid2qm" /></td>
			<td>
				<select id="list_fact_choices_add_ipid3">
					<?= $prod_options ?>
				</select>
			</td>
			<td><input type="text" size="5" id="list_fact_choices_add_ipid3n" /></td><td><input type="text" size="5" id="list_fact_choices_add_ipid3qm" /></td>
			<td>
				<select id="list_fact_choices_add_ipid4">
					<?= $prod_options ?>
				</select>
			</td>
			<td><input type="text" size="5" id="list_fact_choices_add_ipid4n" /></td><td><input type="text" size="5" id="list_fact_choices_add_ipid4qm" /></td>
			<td><a style="cursor:pointer;" onclick="listFactChoicesController.addFactChoice()">[Add]</a></td>
		</tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>