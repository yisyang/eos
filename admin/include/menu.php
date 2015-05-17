			<div id="eos_main">
				<div id="eos_stats_panel">
					<?php
						if(isset($_SESSION['admin_is_logged_in']) && $_SESSION['admin_is_logged_in']){
							echo "Admin: ".$_SESSION['admin_username'];
					?>
						<br /><br />
						<b>World Settings</b><br />
						<a href="list_cat.php">P Categories List</a><br />
						<a href="list_prod.php">Products List</a><br />
						<a href="list_fact.php">Factories List</a><br />
						<a href="list_fact_choices.php">F Choices</a><br />
						<a href="list_store.php">Stores List</a><br />
						<a href="list_rnd.php">RnD List</a><br />
						<a href="list_market_log.php">Market Log Viewer</a><br />
						<br /><br />
						<b>Edit Firm</b><br />
						<form onsubmit="firm_lookup();return false;">
							<input id="firm_lookup_name" type="text" size="10" /> <input style="background-color: #ffffff;color:#000000;border: 1px solid #000000;" type="button" value="&#8595;" onClick="firm_lookup();" />
						</form><br />
						<div id="firm_lookup_select_container">
						</div>
						<form onsubmit="firm_lookup_show_edit(document.getElementById('firm_lookup_id').value,'Firm');return false;">
							<input id="firm_lookup_id" type="text" size="10" onchange="firm_lookup_show_edit(this.value,'Firm');" />
						</form><br />
						<br />
						<div id="firm_lookup_edit_menu">
						</div>
						<br /><br />
						<b>Edit Player</b><br />
						<form onsubmit="player_lookup();return false;">
							<input id="player_lookup_name" type="text" size="10" /> <input style="background-color: #ffffff;color:#000000;border: 1px solid #000000;" type="button" value="&#8595;" onClick="player_lookup();" />
						</form><br />
						<div id="player_lookup_select_container">
						</div>
						<form onsubmit="player_lookup_show_edit(document.getElementById('player_lookup_id').value,'Player');return false;">
							<input id="player_lookup_id" type="text" size="10" onchange="player_lookup_show_edit(this.value,'Player');" />
						</form><br />
						<br />
						<div id="player_lookup_edit_menu">
						</div>
					<?php
						}
					?>
				</div>
				<div id="eos_body">