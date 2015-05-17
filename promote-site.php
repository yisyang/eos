<?php require 'include/prehtml.php'; ?>
<?php
	$sql = "SELECT voted, voted_streak FROM players_extended WHERE id = '$eos_player_id'";
	$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	$voted = $result['voted'];
	$voted_streak = $result['voted_streak'];
	if(!$voted){
		switch($voted_streak){
			case 0:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.player_cash = players.player_cash + 10000000, players_extended.voted = 1, players_extended.voted_streak = 1 WHERE players.id = '$eos_player_id'";
				break;
			case 1:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.player_cash = players.player_cash + 15000000, players_extended.voted = 1, players_extended.voted_streak = 2 WHERE players.id = '$eos_player_id'";
				break;
			case 2:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.player_cash = players.player_cash + 20000000, players_extended.voted = 1, players_extended.voted_streak = 3 WHERE players.id = '$eos_player_id'";
				break;
			case 3:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.influence = players.influence + 200, players_extended.voted = 1, players_extended.voted_streak = 4 WHERE players.id = '$eos_player_id'";
				break;
			case 4:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.player_cash = players.player_cash + 25000000, players_extended.voted = 1, players_extended.voted_streak = 5 WHERE players.id = '$eos_player_id'";
				break;
			case 5:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.player_cash = players.player_cash + 30000000, players_extended.voted = 1, players_extended.voted_streak = 6 WHERE players.id = '$eos_player_id'";
				break;
			case 6:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.influence = players.influence + 300, players_extended.voted = 1, players_extended.voted_streak = 0 WHERE players.id = '$eos_player_id'";
				break;
			default:
				$sql = "UPDATE players LEFT JOIN players_extended ON players.id = players_extended.id SET players.player_cash = players.player_cash + 10000000, players_extended.voted = 1, players_extended.voted_streak = 1 WHERE players.id = '$eos_player_id'";
				break;
		}
		$db->query($sql);
	}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Help Promote EoS</title>
<?php require 'include/head.php'; ?>
<?php require 'include/stats.php'; ?>
		<div id="eos_narrow_screen_padding">
			<h3>Promote EoS</h3>
			<br />
			Thank you for helping to promote this game! (All bonuses apply to the player, cash is transferable to company through settings.)
			<br /><br />
		<?php
			if(!$voted){
				echo '<b>You have been awarded your daily player\'s bonus.</b><br />Player cash can used to fund your company through settings.<br />Rewards increases for each consecutive day, and resets after the cycle is completed.';
			}else{
				echo '<b>You have already claimed your daily bonus today.</b><br />You can claim the daily bonus again after the next server refresh (2-3 minutes past midnight).';
			}
				echo '<br /><br /><table class="default_table"><thead><tr><td>Day 1</td><td>Day 2</td><td>Day 3</td><td>Day 4</td><td>Day 5</td><td>Day 6</td><td>Day 7</td></tr></thead><tbody>';
				echo '<tr><td>$100,000 Cash</td><td>$150,000 Cash</td><td>$200,000 Cash</td><td>200 Influence</td><td>$250,000 Cash</td><td>$300,000 Cash</td><td>300 Influence</td></tr><tr>';
				for($i=0;$i<7;$i++){
					if($i < $voted_streak){
						echo '<td><i>Claimed</i></td>';
					}else{
						if($i == $voted_streak && !$voted){
							echo '<td><i><b>Just Claimed</b></i></td>';
						}else{
							echo '<td>&nbsp;</td>';
						}
					}
				}
				echo '</tr></tbody></table>';
		?>
				/********************************************
				* SECTION REMOVED
				*
				* Original purpose:
				*  Vote links for MMO rankings
				********************************************/

				/********************************************
				* SECTION REMOVED
				*
				* Original purpose:
				*  Like buttons
				*
				* Going forward, I'd recommend using services
				* such as AddToAny
				********************************************/
		</div>
<?php require 'include/foot.php'; ?>