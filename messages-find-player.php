<?php require 'include/prehtml.php'; ?>
<?php
	$fill_target = '';
	if(isset($_POST['fill_target'])){
		$fill_target = filter_var($_POST['fill_target'], FILTER_SANITIZE_STRING);
	}
?>
<?php require 'include/stats_fbox.php'; ?>
		<script type="text/javascript">
			var searchTimeout, lastSearch;
			function initSearch(value, skipTimeout){
				clearTimeout(searchTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					doSearch(value);
				}else{
					searchTimeout = setTimeout("doSearch('" + value + "');", 1000);
				}
			}
			function doSearch(search){
				clearTimeout(searchTimeout);
				if(search !== lastSearch){
					lastSearch = search;
					messagesController.findPlayers(search);
				}
			}
			function fillPN(text){
				var fillTarget = "<?= $fill_target ?>";
				if(fillTarget != ''){
					document.getElementById(fillTarget).value = text;
				}
				$('#jq-dialog-modal').dialog('close');
			}
		</script>
	<h3>Find Player or Company</h3>
	<input class="searchbox" style="width:300px;" onkeyup="initSearch(this.value);" onchange="initSearch(this.value, 1);" placeholder="Search players or companies" />
	<br /><br />
	<table id="players_table" class="default_table"></table>
	<div class="clearer no_select"></div><br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>

