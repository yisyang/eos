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
	$sql = "SELECT firms.*, players.id AS player_id, players.player_name, players.player_cash, players.player_networth, players.player_level, players.player_fame_level, players.influence FROM firms LEFT JOIN firms_extended ON firms.id = firms_extended.id LEFT JOIN players ON players.id = firms_extended.ceo WHERE firms.id = $firm_id";
	$firm = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($firm)){
		echo "Fatal Error: Player not found.";
		exit();
	}
	$firm_level_desc = array("Garage Shop", "Fledgling Start-Up", "Start-Up", "Small Enterprise", "Medium Enterprise", "Large Enterprise", "Nano Cap", "Micro Cap", "Small Cap", "Mid Cap", "Large Cap", "Conglomerate", "Large Conglomerate", "MNC", "Corporate Empire");
	$firm_level_size = sizeof($firm_level_desc);
	$firm_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
	$firm_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Acclaimed", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
	
	$player_level_desc = array("Student", "Businessman", "Entrepreneur", "Millionaire", "Manager", "General Manager", "CEO", "Chairman", "Capitalist", "Billionaire", "Industrialist", "Tycoon", "Trillionaire", "Dynast", "Deity");
	$player_level_size = sizeof($player_level_desc);
	$player_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
	$player_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Acclaimed", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
		
	//Match Firm Stats
	$firm_name = $firm["name"];
	$firm_cash = '$'.number_format($firm["cash"]/100, 2, '.', ',');
	$firm_loan = '$'.number_format($firm["loan"]/100, 2, '.', ',');
	$firm_networth = '$'.number_format($firm["networth"]/100, 2, '.', ',');
	
	$firm_level = $firm["level"];
	$firm_level = $firm_level_desc[$firm_level]." (".$firm_level."/".$firm_level_size.")";
	$firm_fame_level = $firm["fame_level"];
	$firm_fame_level = $firm_fame_desc[min(19,floor($firm_fame_level/5))]." (".$firm_fame_level.")";

	$firm_max_bldg = $firm["max_bldg"];

	$player_id = $firm["player_id"];
	$player_name = $firm["player_name"];
	$player_cash = '$'.number_format($firm["player_cash"]/100, 2, '.', ',');
	$player_influence = $firm["influence"];
	$player_level = $firm["player_level"];
	$player_networth = '$'.number_format($firm["player_networth"]/100, 2, '.', ',');
	$player_fame_level = $firm["player_fame_level"];
	$player_fame_level = $player_fame_desc[min(19,floor($player_fame_level/5))]." (".$player_fame_level.")";
	
	$sql = "SELECT firm_fact.id, firm_fact.size, firm_fact.slot, list_fact.name, list_fact.cost FROM firm_fact LEFT JOIN list_fact on firm_fact.fact_id = list_fact.id WHERE firm_fact.fid = $firm_id ORDER BY slot ASC";
	$facts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT queue_build.id, queue_build.building_type_id, list_fact.name, list_fact.cost, queue_build.building_id, IFNULL(queue_build.building_slot,firm_fact.slot) AS building_slot, queue_build.newsize, queue_build.endtime FROM queue_build LEFT JOIN list_fact ON queue_build.building_type_id = list_fact.id LEFT JOIN firm_fact ON queue_build.building_id = firm_fact.id WHERE queue_build.building_type = 'fact' AND queue_build.fid = $firm_id ORDER BY building_slot ASC";
	$queue_fact = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	$sql = "SELECT firm_store.id, firm_store.size, firm_store.slot, list_store.name, list_store.cost FROM firm_store LEFT JOIN list_store on firm_store.store_id = list_store.id WHERE firm_store.fid = $firm_id ORDER BY slot ASC";
	$stores = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT queue_build.id, queue_build.building_type_id, list_store.name, list_store.cost, queue_build.building_id, IFNULL(queue_build.building_slot,firm_store.slot) AS building_slot, queue_build.newsize, queue_build.endtime FROM queue_build LEFT JOIN list_store ON queue_build.building_type_id = list_store.id LEFT JOIN firm_store ON queue_build.building_id = firm_store.id WHERE queue_build.building_type = 'store' AND queue_build.fid = $firm_id ORDER BY building_slot ASC";
	$queue_store = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	$sql = "SELECT firm_rnd.id, firm_rnd.size, firm_rnd.slot, list_rnd.name, list_rnd.cost FROM firm_rnd LEFT JOIN list_rnd on firm_rnd.rnd_id = list_rnd.id WHERE firm_rnd.fid = $firm_id ORDER BY slot ASC";
	$rnds = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT queue_build.id, queue_build.building_type_id, list_rnd.name, list_rnd.cost, queue_build.building_id, IFNULL(queue_build.building_slot,firm_rnd.slot) AS building_slot, queue_build.newsize, queue_build.endtime FROM queue_build LEFT JOIN list_rnd ON queue_build.building_type_id = list_rnd.id LEFT JOIN firm_rnd ON queue_build.building_id = firm_rnd.id WHERE queue_build.building_type = 'rnd' AND queue_build.fid = $firm_id ORDER BY building_slot ASC";
	$queue_rnd = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Edit User - Basics</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var firm_id = <?php echo $firm_id; ?>;
			var player_id = <?php echo $player_id; ?>;
			var adminFirmController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "firm_basics_controller.php",
                        data: params,
                        dataType: "json",
                        success: function(resp){
							if(resp.success){
								window.location.reload();
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
				addCash: function(){
					var cash = 100 * document.getElementById('firm_cash_add').value;
					var params = {action: 'add_cash', firm_id : firm_id, cash : cash};
					this.executeAjax(params);
				},
				setCash: function(){
					var cash = 100 * document.getElementById('firm_cash_set').value;
					var params = {action: 'set_cash', firm_id : firm_id, cash : cash};
					this.executeAjax(params);
				},
				addLoan: function(){
					var loan = 100 * document.getElementById('firm_loan_add').value;
					var params = {action: 'add_loan', firm_id : firm_id, loan : loan};
					this.executeAjax(params);
				},
				setLoan: function(){
					var loan = 100 * document.getElementById('firm_loan_set').value;
					var params = {action: 'set_loan', firm_id : firm_id, loan : loan};
					this.executeAjax(params);
				},
				addInfluence: function(){
					var player_influence = document.getElementById('player_influence_add').value;
					var reason = document.getElementById('player_influence_add_reason').value;
					var params = {action: 'add_influence', firm_id : firm_id, player_id : player_id, influence : player_influence, reason : reason};
					this.executeAjax(params);
				},
				addMessage: function(){
					var msg = document.getElementById('firm_msg').value;
					var params = {action: 'add_msg', firm_id : firm_id, msg : msg};
					this.executeAjax(params);
				},
				addBuilding: function(b_type){
					if(b_type == "fact"){
						var b_type_id = document.getElementById('fact_type_id').value;
						var size = document.getElementById('fact_size').value;
						var slot = document.getElementById('fact_slot').value;
					}
					if(b_type == "store"){
						var b_type_id = document.getElementById('store_type_id').value;
						var size = document.getElementById('store_size').value;
						var slot = document.getElementById('store_slot').value;
					}
					if(b_type == "rnd"){
						var b_type_id = document.getElementById('rnd_type_id').value;
						var size = document.getElementById('rnd_size').value;
						var slot = document.getElementById('rnd_slot').value;
					}
					var params = {action: 'add_building', firm_id : firm_id, b_type : b_type, b_type_id : b_type_id, size : size, slot : slot};
					this.executeAjax(params);
				},
				addBuildingSize: function(b_type, b_id){
					that = this;
					jPrompt('Please input building size to add', '', 'Add Building Size', function(size){
						if(size){
							var params = {action: 'add_building_size', firm_id : firm_id, b_type : b_type, b_id : b_id, size: size};
							that.executeAjax(params);
						}
					});
				},
				setBuildingSize: function(b_type, b_id){
					that = this;
					jPrompt('Please input new building size', '', 'Set Building Size', function(size){
						if(size){
							var params = {action: 'set_building_size', firm_id : firm_id, b_type : b_type, b_id : b_id, size: size};
							that.executeAjax(params);
						}
					});
				},
				deleteBuilding: function(b_type, b_id){
					var params = {action: 'delete_building', firm_id : firm_id, b_type : b_type, b_id : b_id};
					this.executeAjax(params);
				},
				deleteBuildingQueue: function(b_type, queue_id){
					var params = {action: 'delete_building_queue', firm_id : firm_id, b_type : b_type, queue_id : queue_id};
					this.executeAjax(params);
				}
			}
		</script>
<?php require 'include/menu.php'; ?>
<div style="width: 950px; padding: 25px; overflow-x: auto;">
	<?php
		echo "Name: <a style=\"cursor:pointer;\" onclick=\"firm_lookup_show_edit($firm_id,'$firm_name');\">$firm_name</a> ($firm_id)<br />";
		echo '<span style="display:inline-block;min-width:200px;">Cash: ',$firm_cash,'</span> ';
		echo 'Add ($): <input id="firm_cash_add" type="text" /> <input type="button" onclick="adminFirmController.addCash()" value="Add" /> ';
		echo 'Set ($): <input id="firm_cash_set" type="text" /> <input type="button" onclick="adminFirmController.setCash()" value="Set" />';
		echo "<br />";
		echo '<span style="display:inline-block;min-width:200px;">Loan: ',$firm_loan,'</span> ';
		echo 'Add ($): <input id="firm_loan_add" type="text" /> <input type="button" onclick="adminFirmController.addLoan()" value="Add" /> ';
		echo 'Set ($): <input id="firm_loan_set" type="text" /> <input type="button" onclick="adminFirmController.setLoan()" value="Set" /> ';
		echo "<br />";
		echo "NW: <a href='firm_basics_appraise.php'>".$firm_networth."</a><br />";
		echo "Level: ".$firm_level."<br />";
		echo "Fame: ".$firm_fame_level."<br /><br /><br />";

		echo "Name: <a style=\"cursor:pointer;\" onclick=\"player_lookup_show_edit($player_id,'$player_name');\">$player_name</a> ($player_id)<br />";
		echo "Cash: ",$player_cash,"<br />";
		echo '<span style="display:inline-block;min-width:200px;">Influence: ',$player_influence,'</span> ';
		echo 'Add: <input id="player_influence_add" type="text" /> <a title="You were awarded X influence ">Reason: </a><input id="player_influence_add_reason" type="text" /> <input type="button" onclick="adminFirmController.addInfluence()" value="Add" />';
		echo "<br />";
		echo "NW: ".$player_networth."<br />";
		echo "Level: ".$player_level."<br />";
		echo "Fame: ".$player_fame_level."<br /><br /><br />";
		
		echo 'Firm News: <input id="firm_msg" type="text" /> <input type="button" onclick="adminFirmController.addMessage();" value="Send" /> <br /><br /><br />';
		//Silent add cash, another function for custom message.

		echo '<h3>Factories</h3>';
		if(!empty($facts)){
			echo '<table class="edit_table"><thead><tr><td>Name</td><td>Size</td><td>Slot</td><td>Cost ($)</td><td>Actions</td></tr></thead><tbody>';
			foreach($facts as $bldg){
				echo '<tr><td>',$bldg['name'],'</td><td>',$bldg['size'],'</td><td>',$bldg['slot'],'</td><td>',$bldg['cost']*$bldg['size']/100,'</td><td>
				<a onclick="adminFirmController.addBuildingSize(\'fact\',',$bldg['id'],')">[Add]</a> 
				<a onclick="adminFirmController.setBuildingSize(\'fact\',',$bldg['id'],')">[Set]</a> 
				<a onclick="adminFirmController.deleteBuilding(\'fact\',',$bldg['id'],')">[Del]</a> 
				</td></tr>';
			}
			echo '</tbody></table>';
		}else{
			echo 'None<br />';
		}
		$sql = "SELECT id, name FROM list_fact ORDER BY name ASC";
		$building_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		echo '<select id="fact_type_id">';
		foreach($building_choices as $building_choice){
			echo '<option value="',$building_choice['id'],'">',$building_choice['name'],'</option>';
		}
		echo '</select>';
		echo '<input id="fact_size" type="text" />';
		echo '<select id="fact_slot">';
		for($i=1;$i<=$firm_max_bldg;$i++){
			echo '<option value="',$i,'">',$i,'</option>';
		}
		echo '</select>';
		echo '<a onclick="adminFirmController.addBuilding(\'fact\')">[Add]</a></td>';
		if(!empty($queue_fact)){
			echo 'Queues:<br />';
			echo '<table class="edit_table"><thead><tr><td>Name</td><td>New Size</td><td>Slot</td><td>Cost/Unit ($)</td><td>End Time</td><td>Actions</td></tr></thead><tbody>';
			foreach($queue_fact as $queue_bldg){
				echo '<tr><td>',$queue_bldg['name'],'</td><td>',$queue_bldg['newsize'],'</td><td>',$queue_bldg['building_slot'],'</td><td>',$queue_bldg['cost']/100,'</td><td>',$queue_bldg['endtime'],'</td><td><a onclick="adminFirmController.deleteBuildingQueue(\'fact\',',$queue_bldg['id'],')">[Del]</a></td></tr>';
			}
			echo '</tbody></table>';
		}
		
		echo '<h3>Stores</h3>';
		if(!empty($stores)){
			echo '<table class="edit_table"><thead><tr><td>Name</td><td>Size</td><td>Slot</td><td>Cost ($)</td><td>Actions</td></tr></thead><tbody>';
			foreach($stores as $bldg){
				echo '<tr><td>',$bldg['name'],'</td><td>',$bldg['size'],'</td><td>',$bldg['slot'],'</td><td>',$bldg['cost']*$bldg['size']/100,'</td><td>
				<a onclick="adminFirmController.addBuildingSize(\'store\',',$bldg['id'],')">[Add]</a> 
				<a onclick="adminFirmController.setBuildingSize(\'store\',',$bldg['id'],')">[Set]</a> 
				<a onclick="adminFirmController.deleteBuilding(\'store\',',$bldg['id'],')">[Del]</a> 
				</td></tr>';
			}
			echo '</tbody></table>';
		}else{
			echo 'None<br />';
		}
		$sql = "SELECT id, name FROM list_store ORDER BY name ASC";
		$building_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		echo '<select id="store_type_id">';
		foreach($building_choices as $building_choice){
			echo '<option value="',$building_choice['id'],'">',$building_choice['name'],'</option>';
		}
		echo '</select>';
		echo '<input id="store_size" type="text" />';
		echo '<select id="store_slot">';
		for($i=1;$i<=$firm_max_bldg;$i++){
			echo '<option value="',$i,'">',$i,'</option>';
		}
		echo '</select>';
		echo '<a onclick="adminFirmController.addBuilding(\'store\')">[Add]</a></td>';
		if(!empty($queue_store)){
			echo 'Queues:<br />';
			echo '<table class="edit_table"><thead><tr><td>Name</td><td>New Size</td><td>Slot</td><td>Cost/Unit ($)</td><td>End Time</td><td>Actions</td></tr></thead><tbody>';
			foreach($queue_store as $queue_bldg){
				echo '<tr><td>',$queue_bldg['name'],'</td><td>',$queue_bldg['newsize'],'</td><td>',$queue_bldg['building_slot'],'</td><td>',$queue_bldg['cost']/100,'</td><td>',$queue_bldg['endtime'],'</td><td><a onclick="adminFirmController.deleteBuildingQueue(\'store\',',$queue_bldg['id'],')">[Del]</a></td></tr>';
			}
			echo '</tbody></table>';
		}

		echo '<h3>R&Ds</h3>';
		if(!empty($rnds)){
			echo '<table class="edit_table"><thead><tr><td>Name</td><td>Size</td><td>Slot</td><td>Cost ($)</td><td>Actions</td></tr></thead><tbody>';
			foreach($rnds as $bldg){
				echo '<tr><td>',$bldg['name'],'</td><td>',$bldg['size'],'</td><td>',$bldg['slot'],'</td><td>',$bldg['cost']*$bldg['size']/100,'</td><td>
				<a onclick="adminFirmController.addBuildingSize(\'rnd\',',$bldg['id'],')">[Add]</a> 
				<a onclick="adminFirmController.setBuildingSize(\'rnd\',',$bldg['id'],')">[Set]</a> 
				<a onclick="adminFirmController.deleteBuilding(\'rnd\',',$bldg['id'],')">[Del]</a> 
				</td></tr>';
			}
			echo '</tbody></table>';
		}else{
			echo 'None<br />';
		}
		$sql = "SELECT id, name FROM list_rnd ORDER BY name ASC";
		$building_choices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		echo '<select id="rnd_type_id">';
		foreach($building_choices as $building_choice){
			echo '<option value="',$building_choice['id'],'">',$building_choice['name'],'</option>';
		}
		echo '</select>';
		echo '<input id="rnd_size" type="text" />';
		echo '<select id="rnd_slot">';
		for($i=1;$i<=$firm_max_bldg;$i++){
			echo '<option value="',$i,'">',$i,'</option>';
		}
		echo '</select>';
		echo '<a onclick="adminFirmController.addBuilding(\'rnd\')">[Add]</a></td>';
		if(!empty($queue_rnd)){
			echo 'Queues:<br />';
			echo '<table class="edit_table"><thead><tr><td>Name</td><td>New Size</td><td>Slot</td><td>Cost/Unit ($)</td><td>End Time</td><td>Actions</td></tr></thead><tbody>';
			foreach($queue_rnd as $queue_bldg){
				echo '<tr><td>',$queue_bldg['name'],'</td><td>',$queue_bldg['newsize'],'</td><td>',$queue_bldg['building_slot'],'</td><td>',$queue_bldg['cost']/100,'</td><td>',$queue_bldg['endtime'],'</td><td><a onclick="adminFirmController.deleteBuildingQueue(\'rnd\',',$queue_bldg['id'],')">[Del]</a></td></tr>';
			}
			echo '</tbody></table>';
		}
	?>
</div>
<?php require 'include/foot.php'; ?>