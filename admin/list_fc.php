<?php require 'include/prehtml.php'; ?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Foreign Companies List</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var listFcController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "list_fc_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									var div_id = "list_fc_"+params['fc_id'];
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
				showEdit: function(fc_id){
					var params = {action: 'show_edit', fc_id : fc_id};
					this.executeAjax(params);
				},
				editCancel: function(fc_id){
					var params = {action: 'edit_cancel', fc_id : fc_id};
					this.executeAjax(params);
				},
				editConfirm: function(fc_id){
					var name = document.getElementById("list_fc_edit_"+fc_id+"_name").value;
					var country_id = document.getElementById("list_fc_edit_"+fc_id+"_country_id").value;
					var country_name = document.getElementById("list_fc_edit_"+fc_id+"_country_name").value;
					var params = {action: 'edit_confirm', fc_id : fc_id, name : name, country_id : country_id, country_name : country_name};
					this.executeAjax(params);
				},
				addCompany: function(){
					var name = document.getElementById("list_fc_add_name").value;
					var country_id = document.getElementById("list_fc_add_country_id").value;
					var country_name = document.getElementById("list_fc_add_country_name").value;
					var params = {action: 'add_company', name : name, country_id : country_id, country_name : country_name};
					this.executeAjax(params);
				}
			}
		</script>
<?php require 'include/menu.php'; ?>
<?php
	//$sql = "SELECT * FROM foreign_companies ORDER BY name ASC";
	$sql = "SELECT * FROM foreign_companies";
	$foreign_companies = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="edit_table">
	<thead>
		<tr><td>Name</td><td>Country Id</td><td>Country Name</td><td>Actions</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($foreign_companies as $foreign_company){
			$list_fc_id = $foreign_company["id"];
			echo '<tr id="list_fc_'.$list_fc_id.'"><td>'.$foreign_company["name"].'</td><td>'.$foreign_company["country_id"].'</td><td>'.$foreign_company["country_name"].'</td><td><a style="cursor:pointer;" onclick="listFcController.showEdit(\''.$list_fc_id.'\')">[Edit]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr><td><input type="text" size="24" id="list_fc_add_name" /></td><td><input type="text" size="12" id="list_fc_add_country_id" /></td><td><input type="text" size="12" id="list_fc_add_country_name" /></td><td><a style="cursor:pointer;" onclick="listFcController.addCompany()">[Add]</a></td></tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>