<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Economies of Scale - Jail</title>
<?php require '../include/head_subd.php'; ?>
<style type="text/css">
	.body_dark{	width: 100%;line-height:1.5;min-height: 680px;background-color:#000000;color:#ffffff;border-top: 1px solid #666666;border-bottom: 1px solid #666666; }
	.body_dark a{ color: #e0e0e0; text-decoration: none; }
	.body_dark a:visited { color: #c0c0ff; }
</style>
		<div class="body_dark">
			<div style="padding: 15px;">
				<h3>Jail</h3>
				This is the jail. It looks awfully empty, all you see is quality 999 walls in all directions.<br /><br />
				You don't know why you are here, or how you are able to stay alive in this closed space without proper ventilation, or how you got in here in the first place.<br /><br />
				<?php
					$timenow = time();
					if(isset($eos_player_id)){
						$query = $db->prepare("SELECT players.fid, players.in_jail, players.is_hidden, players.new_user, players.show_menu_tooltip, players.narrow_screen, players.enable_chat FROM players WHERE players.id = ?");
						$query->execute(array($eos_player_id));
						$player = $query->fetch(PDO::FETCH_ASSOC);
						$eos_firm_id = $player['fid'];
						if($player['in_jail']){
							$visiting = 0;
							echo 'You only hope this is a dream, and when you wake up from it you\'ll be in the real world again.<br /><br />';
							if($player['in_jail'] < $timenow + 86400 * 31){
								echo 'There is a note on the ground: "You\'ll remain here until ',date("F j, Y, g:i A", $player['in_jail']),'."';
							}else{
								echo 'There is a note on the ground: "Looks like you\'ll be here for a long long time."';
							}
						}else{
							$visiting = 1;
						}
					}else{
						$visiting = 1;
					}
					if($visiting){
						echo '<b>Oh, you are just visiting?</b> Here are the people currently in jail:<br /><br />';
						$query = $db->prepare("SELECT players.id, players.player_name, players.in_jail FROM players WHERE players.in_jail > ? ORDER BY in_jail ASC");
						$query->execute(array($timenow));
						$results = $query->fetchAll(PDO::FETCH_ASSOC);
						$count = count($results);
						foreach($results as $result){
							echo '<a href="/eos/player/',$result['id'],'">',$result['player_name'],'</a>';
							$in_jail = $result['in_jail'];
							if($in_jail < $timenow + 86400 * 365){
								echo '- Until ',date("F j, Y, g:i A", $in_jail);
							}else{
								echo '- Lifetime';
							}
							echo '<br />';
						}
					}
				?>
			</div>
		</div>
</div>
<br />
</body>
</html>