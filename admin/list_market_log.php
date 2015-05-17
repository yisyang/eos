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
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Market Log</title>
		<script type="text/javascript">
		</script>
<?php require 'include/head.php'; ?>
<?php require 'include/menu.php'; ?>
<div style="width: 950px; padding: 25px; overflow-x: auto;">
	<?php
		// TODO: Not yet completed
		$count = 1;
		if($count){
			echo '<table class="edit_table"><thead><tr><td>Seller / NW</td><td>Owner / Public</td><td>Buyer / NW</td><td>Owner / Public</td><td>Item</td><td>Unit Price</td><td>Total</td><td>Ratio</td><td>Actions</td></tr>';
			echo '</thead><tbody>';
			echo '</tbody></table>';
		}
	?>
</div>
<?php require 'include/foot.php'; ?>