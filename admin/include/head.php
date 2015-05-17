		<link href="scripts/admin.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/ui-lightness/jquery-ui-1.10.1.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/jAlerts/jquery.alerts.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/nyroModal/css/nyroModal.css" rel="stylesheet" type="text/css" />
		
		<script src="../scripts/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../scripts/jquery-ui-1.10.1.min.js" type="text/javascript"></script>
		<script src="../scripts/jAlerts/jquery.alerts.min.js" type="text/javascript"></script>
		<script src="../scripts/nyroModal/js/jquery.nyroModal.custom.min.js" type="text/javascript"></script>
		<script src="../scripts/eos_common.js" type="text/javascript"></script> 
		<script type="text/javascript">
			ajax_lookup_busy = 0;
			ajax_lookup_select_busy = 0;
			ajax_lookup_2_busy = 0;
			ajax_lookup_2_select_busy = 0;
			function firm_lookup(){
				var firm_name = document.getElementById("firm_lookup_name").value;
				var url="firm_lookup_show_selection.php?firm_name="+firm_name;
				if(ajax_lookup_busy == 1){
					alert("Script is busy");
					return false;
				}else{
					ajax_lookup_busy = 1;
					if (window.XMLHttpRequest){
						// code for IE7+, Firefox, Chrome, Opera, Safari
						var ajax_lookup=new XMLHttpRequest();
					}else{
						// code for IE6, IE5
						var ajax_lookup=new ActiveXObject("Microsoft.XMLHTTP");
					}
					ajax_lookup.onreadystatechange=function(){
						if (ajax_lookup.readyState==4 && ajax_lookup.status==200){
							document.getElementById("firm_lookup_select_container").innerHTML=ajax_lookup.responseText;
							ajax_lookup_busy = 0;
						}
					}
					ajax_lookup.open("GET",url,true);
					ajax_lookup.send();
				}
			}
			function firm_lookup_show_edit(fid, fname, noredirect){
				if(fid){
					var url="firm_lookup_show_edit.php?firm_id="+fid+"&firm_name="+escape(fname);
				}else{
					var firm_lookup_selected_index = document.getElementById("firm_lookup_select").options.selectedIndex;
					var firm_id = document.getElementById("firm_lookup_select").options[firm_lookup_selected_index].value;
					var firm_name = escape(document.getElementById("firm_lookup_select").options[firm_lookup_selected_index].text);
					
					var url="firm_lookup_show_edit.php?firm_id="+firm_id+"&firm_name="+firm_name;
				}

				if(ajax_lookup_select_busy != 1){
					ajax_lookup_select_busy = 1;
					if(!fid){
						document.getElementById("firm_lookup_select").disabled=1;
					}
					if (window.XMLHttpRequest){
						// code for IE7+, Firefox, Chrome, Opera, Safari
						var ajax_lookup=new XMLHttpRequest();
					}else{
						// code for IE6, IE5
						var ajax_lookup=new ActiveXObject("Microsoft.XMLHTTP");
					}
					ajax_lookup.onreadystatechange=function(){
						if (ajax_lookup.readyState==4 && ajax_lookup.status==200){
							document.getElementById("firm_lookup_edit_menu").innerHTML=ajax_lookup.responseText;
							ajax_lookup_select_busy = 0;
							if(!fid){
								document.getElementById("firm_lookup_select").disabled=0;
							}
							if(!noredirect){
								window.location.href="/eos/admin/firm_basics.php";
							}
						}
					}
					ajax_lookup.open("GET",url,true);
					ajax_lookup.send();
				}
			}
			function player_lookup(){
				var player_name = document.getElementById("player_lookup_name").value;
				var url="player_lookup_show_selection.php?player_name="+player_name;
				if(ajax_lookup_2_busy == 1){
					alert("Script is busy");
					return false;
				}else{
					ajax_lookup_2_busy = 1;
					if (window.XMLHttpRequest){
						// code for IE7+, Firefox, Chrome, Opera, Safari
						var ajax_lookup=new XMLHttpRequest();
					}else{
						// code for IE6, IE5
						var ajax_lookup=new ActiveXObject("Microsoft.XMLHTTP");
					}
					ajax_lookup.onreadystatechange=function(){
						if (ajax_lookup.readyState==4 && ajax_lookup.status==200){
							document.getElementById("player_lookup_select_container").innerHTML=ajax_lookup.responseText;
							ajax_lookup_2_busy = 0;
						}
					}
					ajax_lookup.open("GET",url,true);
					ajax_lookup.send();
				}
			}
			function player_lookup_show_edit(fid, fname, noredirect){
				if(fid){
					var url="player_lookup_show_edit.php?player_id="+fid+"&player_name="+escape(fname);
				}else{
					var player_lookup_selected_index = document.getElementById("player_lookup_select").options.selectedIndex;
					var player_id = document.getElementById("player_lookup_select").options[player_lookup_selected_index].value;
					var player_name = escape(document.getElementById("player_lookup_select").options[player_lookup_selected_index].text);
					
					var url="player_lookup_show_edit.php?player_id="+player_id+"&player_name="+player_name;
				}

				if(ajax_lookup_2_select_busy != 1){
					ajax_lookup_2_select_busy = 1;
					if(!fid){
						document.getElementById("player_lookup_select").disabled=1;
					}
					if (window.XMLHttpRequest){
						// code for IE7+, Firefox, Chrome, Opera, Safari
						var ajax_lookup=new XMLHttpRequest();
					}else{
						// code for IE6, IE5
						var ajax_lookup=new ActiveXObject("Microsoft.XMLHTTP");
					}
					ajax_lookup.onreadystatechange=function(){
						if (ajax_lookup.readyState==4 && ajax_lookup.status==200){
							document.getElementById("player_lookup_edit_menu").innerHTML=ajax_lookup.responseText;
							ajax_lookup_2_select_busy = 0;
							if(!fid){
								document.getElementById("player_lookup_select").disabled=0;
							}
							if(!noredirect){
								window.location.href="/eos/admin/player_basics.php";
							}
						}
					}
					ajax_lookup.open("GET",url,true);
					ajax_lookup.send();
				}
			}
			<?php
			//Initialize firm lookup session var
			if(isset($_SESSION['editing_firm_id'])){
				$editing_firm_id = 0 + filter_var($_SESSION['editing_firm_id'], FILTER_SANITIZE_NUMBER_INT);
				$editing_firm_name = filter_var($_SESSION['editing_firm_name'], FILTER_SANITIZE_STRING);
				echo 'firm_lookup_show_edit('.$editing_firm_id.',"'.$editing_firm_name.'",1);';
			}
			if(isset($_SESSION['editing_player_id'])){
				$editing_player_id = 0 + filter_var($_SESSION['editing_player_id'], FILTER_SANITIZE_NUMBER_INT);
				$editing_player_name = filter_var($_SESSION['editing_player_name'], FILTER_SANITIZE_STRING);
				echo 'player_lookup_show_edit('.$editing_player_id.',"'.$editing_player_name.'",1);';
			}
			?>
		</script>
	</head>
	<body>
		<div id="eos_wrapper">
			<noscript><br /><font size="4" color="#ff0000">&nbsp;&nbsp;&nbsp; This site requires javascript to function, please do not disable it.</font><br /><br /></noscript>
			<div id="eos_header">
				<a href="/eos/admin/"><img src="../images/rjeos.gif" height="32px" width="400px" /></a>
				<div style="float: right;text-align: right;margin-right: 10px;">
					<a href="/index.php"><img src="/images/ratjoy.gif" height="32px" width="44px" /><img src="/images/ratjoy-text.gif" height="32px" width="126px" /></a><br />
					<?php
						if(isset($_SESSION['admin_is_logged_in']) && $_SESSION['admin_is_logged_in']){
					?>
							<a class="link_w" style="font-size: 12px;color: #e8e8e8;" href="logout.php">Logout</a>
					<?php
						}
					?>
				</div>
			</div>