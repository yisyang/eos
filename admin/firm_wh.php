<?php require 'include/prehtml.php'; ?>
<?php
	if(isset($_GET['firm_id'])){
		$firm_id = filter_var($_GET['firm_id'], FILTER_SANITIZE_NUMBER_INT);
	}else if(isset($_SESSION['editing_firm_id'])){
		$firm_id = filter_var($_SESSION['editing_firm_id'], FILTER_SANITIZE_NUMBER_INT);
	}else{
		$firm_id = 0;
	}
	if(!$firm_id){
		header( 'Location: index.php' );
		exit();
	}
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Edit User - Warehouse</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var adminFirmWHController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "firm_wh_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								if(typeof(resp.html) !== 'undefined'){
									var div_id = "firm_wh_"+params['wh_id'];
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
				showEdit: function(wh_id){
					var params = {action: 'show_edit', wh_id : wh_id};
					this.executeAjax(params);
				},
				editCancel: function(wh_id){
					var params = {action: 'edit_cancel', wh_id : wh_id};
					this.executeAjax(params);
				},
				editConfirm: function(wh_id){
					var firm_id = <?php echo $firm_id; ?>;
					var pid = document.getElementById("firm_wh_edit_"+wh_id+"_pid").value;
					var pidn = document.getElementById("firm_wh_edit_"+wh_id+"_pidn").value;
					var pidq = document.getElementById("firm_wh_edit_"+wh_id+"_pidq").value;
					var params = {action: 'edit_confirm', wh_id : wh_id, firm_id : firm_id, pid : pid, pidn : pidn, pidq : pidq};
					this.executeAjax(params);
				},
				addWH: function(){
					var firm_id = <?php echo $firm_id; ?>;
					var pid = document.getElementById("firm_wh_add_pid").value;
					var pidn = document.getElementById("firm_wh_add_pidn").value;
					var pidq = document.getElementById("firm_wh_add_pidq").value;
					var params = {action: 'add_wh', firm_id : firm_id, pid : pid, pidn : pidn, pidq : pidq};
					this.executeAjax(params);
				},
				deleteWH: function(wh_id){
					var firm_id = <?php echo $firm_id; ?>;
					var params = {action: 'delete_wh', wh_id : wh_id, firm_id: firm_id};
					this.executeAjax(params);
				}
			}
		</script>
<?php require 'include/menu.php'; ?>

<?php
	//Initialize Products
	$sql = "SELECT * FROM list_prod ORDER BY name ASC";
	$list_prod = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT firm_wh.*, list_prod.name AS prod_name FROM firm_wh LEFT JOIN list_prod ON firm_wh.pid = list_prod.id WHERE fid = $firm_id ORDER BY list_prod.name ASC";
	$firm_wh = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="width: 950px; overflow-x: auto;">
<table class="edit_table">
	<thead>
		<tr><td>Product</td><td>Quality</td><td>#</td><td>Cost</td><td>Actions</td></tr>
	</thead>
	<tbody style="height: 650px; overflow-y: auto; overflow-x: hidden;">
	<?php
		foreach($firm_wh as $wh_item){
			$firm_wh_id = $wh_item["id"];
			echo '<tr id="firm_wh_'.$firm_wh_id.'">
			<td>'.$wh_item["prod_name"].'</td>
			<td>'.$wh_item["pidq"].'</td>
			<td>'.$wh_item["pidn"].'</td>
			<td>$'.number_format_readable($wh_item["pidcost"]/100).'</td>
			<td><a href="#" onclick="adminFirmWHController.showEdit(\''.$firm_wh_id.'\')">[Edit]</a> <a href="#" onclick="adminFirmWHController.deleteWH(\''.$firm_wh_id.'\')">[Delete]</a></td></tr>';
		}
	?>
	</tbody>
	<tfoot>
		<tr>
			<td>
				<select id="firm_wh_add_pid">
				<?php
					if(count($list_prod)){
						echo '<option value=""> </option>';
						foreach($list_prod as $prod){
							echo '<option value="'.$prod["id"].'">'.$prod["name"].'</option>';
						}
					}
				?>
				</select>
			</td>
			<td><input type="text" size="5" id="firm_wh_add_pidq" /></td>
			<td><input type="text" size="10" id="firm_wh_add_pidn" /></td>
			<td></td>
			<td><a href="#" onclick="adminFirmWHController.addWH()">[Add]</a></td>
		</tr>
	</tfoot>
</table>

<?php require 'include/foot.php'; ?>