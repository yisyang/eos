<?php require 'include/config_newplayer.php'; ?>
<?php require 'include/prehtml.php'; ?>
<?php
	check_new_player();
?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Tutorial</title>
<?php require 'include/head.php'; ?>
<?php require 'include/stats.php'; ?>
	<div id="tutorial_div"></div>
	<script type="text/javascript">
		tutorialController.showContent();
	</script>
<?php require 'include/foot.php'; ?>