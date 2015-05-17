<?php require 'include/prehtml.php'; ?>
<?php
	if(isset($_GET['player_id'])){
		$player_id = filter_var($_GET['player_id'], FILTER_SANITIZE_NUMBER_INT);
	}else if(isset($_SESSION['editing_player_id'])){
		$player_id = filter_var($_SESSION['editing_player_id'], FILTER_SANITIZE_NUMBER_INT);
	}else{
		$player_id = 0;
	}
	if(!$player_id){
		header( 'Location: index.php' );
		exit();
	}
	$sql = "SELECT players.id AS player_id, players.login_id, players.player_name, players.player_cash, players.player_networth, players.player_level, players.player_fame_level, players.influence, players.vip_level, players.vip_expires, players.in_jail, players.last_active FROM players WHERE players.id = $player_id";
	$player = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($player)){
		echo "Fatal Error: Player not found.";
		exit();
	}
	$player_level_desc = array("Student", "Businessman", "Entrepreneur", "Millionaire", "Manager", "General Manager", "CEO", "Chairman", "Capitalist", "Billionaire", "Industrialist", "Tycoon", "Trillionaire", "Dynast", "Deity");
	$player_level_size = sizeof($player_level_desc);
	$player_level_lowlimit = array(0, 25000000, 50000000, 100000000, 300000000, 1000000000, 3000000000, 10000000000, 30000000000, 100000000000, 1000000000000, 10000000000000, 100000000000000, 1000000000000000, 10000000000000000);
	$player_fame_desc = array("Unknown", "Unnoticed", "Trivial", "Obscure", "Uncertain", "Ordinary", "Recognized", "Distinguished", "Locally Known", "Well-Known", "Prominent", "Widely Acclaimed", "Illustrious", "Stellar", "Symbolic", "Monumental", "Universal", "Paramount", "Legendary", "Immortal");
		
	//Match Player Stats
	$rj_id = $player["login_id"];
	$dbrj = $dbeos = rjdb_connect('site');
	$sql = "SELECT ip_curr, ip_last, ip_norm FROM users_login WHERE u_id = $rj_id";
	$rj_ips = $dbrj->query($sql)->fetch(PDO::FETCH_ASSOC);
	$rj_ip_curr = $rj_ips['ip_curr'];
	$rj_ip_last = $rj_ips['ip_last'];
	$rj_ip_norm = $rj_ips['ip_norm'];
	$player_id = $player["player_id"];
	$player_name = $player["player_name"];
	$player_cash = '$'.number_format($player["player_cash"]/100, 2, '.', ',');
	$player_influence = $player["influence"];
	$player_in_jail_new = $player["in_jail"];
	$player_level = $player["player_level"];
	$player_vip = $player["vip_level"];
	$player_vip_expiration = $player["vip_expires"];
	$player_networth = '$'.number_format($player["player_networth"]/100, 2, '.', ',');
	$player_fame_level = $player["player_fame_level"];
	$player_fame_level = $player_fame_desc[min(19,floor($player_fame_level/5))]." (".$player_fame_level.")";
	$player_last_active = $player["last_active"];
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Edit User - Basics</title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
			var player_id = <?php echo $player_id; ?>;
			var adminPlayerController = {
				executeAjax: function(params){
					$.ajax({
                        type: "POST", 
                        url: "player_basics_controller.php",
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
				addAchievement: function(){
					var achievement_id = document.getElementById('achievement_id').value;
					var params = {action: 'add_achievement', player_id : player_id, achievement_id : achievement_id};
					this.executeAjax(params);
				},
				removeAchievement: function(){
					var achievement_id = document.getElementById('achievement_id').value;
					var params = {action: 'remove_achievement', player_id : player_id, achievement_id : achievement_id};
					this.executeAjax(params);
				},
				addCash: function(){
					var cash = 100 * document.getElementById('player_cash_add').value;
					var params = {action: 'add_cash', player_id : player_id, cash : cash};
					this.executeAjax(params);
				},
				setCash: function(){
					var cash = 100 * document.getElementById('player_cash_set').value;
					var params = {action: 'set_cash', player_id : player_id, cash : cash};
					this.executeAjax(params);
				},
				addInfluence: function(){
					var player_influence = document.getElementById('player_influence_add').value;
					var reason = document.getElementById('player_influence_add_reason').value;
					var params = {action: 'add_influence', player_id : player_id, influence : player_influence, reason : reason};
					this.executeAjax(params);
				},
				jailTime: function(){
					var player_in_jail = document.getElementById('player_in_jail_new').value;
					var params = {action: 'set_jail_time', player_id : player_id, in_jail : player_in_jail};
					this.executeAjax(params);
				},
				updateVIP: function(){
					var player_vip_new = document.getElementById('player_vip_new').value;
					var player_vip_expiration_new = document.getElementById('player_vip_expiration_new').value;
					var params = {action: 'update_vip', player_id : player_id, player_vip_new : player_vip_new, player_vip_expiration_new : player_vip_expiration_new};
					this.executeAjax(params);
				},
				addMessage: function(){
					var msg = document.getElementById('player_msg').value;
					var params = {action: 'add_msg', player_id : player_id, msg : msg};
					this.executeAjax(params);
				}
			}
		</script>
<?php require 'include/menu.php'; ?>
<div style="width: 950px; padding: 25px; overflow-x: auto;">
	<?php
		echo "Name: <a style=\"cursor:pointer;\" onclick=\"player_lookup_show_edit($player_id,'$player_name');\">$player_name</a> ($player_id)<br />";
		echo "RJ ID: $rj_id &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IP: <a class=\"info\">$rj_ip_norm<span>C: $rj_ip_curr<br />L: $rj_ip_last<br />N: $rj_ip_norm</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Last Active: $player_last_active<br />";
		echo "Cash: ".$player_cash;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo 'Add ($): <input id="player_cash_add" type="text" /> <input type="button" onclick="adminPlayerController.addCash()" value="Add" /> ';
		echo 'Set ($): <input id="player_cash_set" type="text" /> <input type="button" onclick="adminPlayerController.setCash()" value="Set" /><br />';
		echo "Influence: ".$player_influence;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo 'Add: <input id="player_influence_add" type="text" /> <a title="You were awarded X influence ">Reason: </a><input id="player_influence_add_reason" type="text" /> <input type="button" onclick="adminPlayerController.addInfluence()" value="Add" />';
		echo "<br />";
		echo "VIP: ".$player_vip;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo 'New: <input id="player_vip_new" type="text" value="',$player_vip,'" /> Expiration: <input id="player_vip_expiration_new" type="text" value="',$player_vip_expiration,'" /> <input type="button" onclick="adminPlayerController.updateVIP()" value="Set" />';
		echo "<br />";
		echo "In Jail: ".$player_in_jail_new;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo 'Until: <input id="player_in_jail_new" type="text" /> <input type="button" onclick="adminPlayerController.jailTime()" value="Set" /> (Now = ',time(),')';
		echo "<br />";
		echo "NW: ".$player_networth."<br />";
		echo "Level: ".$player_level."<br />";
		echo "Fame: ".$player_fame_level."<br /><br /><br />";
		
		echo 'Player News: <input id="player_msg" type="text" /> <input type="button" onclick="adminPlayerController.addMessage();" value="Send" /> <br /><br /><br />';
		
		echo 'Achievements:';
		$sql = "SELECT list_achievements.name, list_achievements.filename FROM (SELECT aid FROM player_achievements WHERE pid = $player_id) AS a LEFT JOIN list_achievements ON a.aid = list_achievements.id";
		$achievements = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($achievements)){
			foreach($achievements as $achievement){
				$achievement_title = $achievement['name'];
				echo '<br />',$achievement_title;
			}
		}else{
			echo '<br />None';
		}
		$sql = "SELECT list_achievements.id, list_achievements.name FROM list_achievements ORDER BY name ASC";
		$list_achievements = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($list_achievements)){
			echo '<br />';
			echo '<select id="achievement_id">';
			foreach($list_achievements as $achievement_item){
				echo '<option value="',$achievement_item['id'],'">',$achievement_item['name'],'</option>';
			}
			echo '</select>';
			echo ' <input type="button" onclick="adminPlayerController.addAchievement();" value="Add" /> <input type="button" onclick="adminPlayerController.removeAchievement();" value="Remove" />';
		}
		echo '<br /><br /><br />';
		
		$sql = "SELECT firms.name, firms.networth, firms_positions.fid, firms_positions.title, firms_positions.pay_flat, firms_positions.bonus_percent FROM firms_positions LEFT JOIN firms ON firms_positions.fid = firms.id WHERE firms_positions.pid = $player_id ORDER BY firms.networth DESC";
		$positions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($positions as $position){
			echo $position['title']," of <a style=\"cursor:pointer;\" onclick=\"firm_lookup_show_edit(",$position['fid'],",'",$position['name'],"');\">",$position['name'],"</a> (",$position['fid'],") (NW: $",number_format($position['networth']/100, 2, '.', ','),")<br />Pay: $",number_format($position['pay_flat']/100, 2, '.', ',')," Bonus: ",number_format($position['bonus_percent'], 2, '.', ','),"%<br /><br />";
		}
	?>
</div>
<?php require 'include/foot.php'; ?>