		<link href="scripts/admin.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/ui-lightness/jquery-ui-1.10.1.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/jAlerts/jquery.alerts.css" rel="stylesheet" type="text/css" />
		<link href="../scripts/nyroModal/css/nyroModal.css" rel="stylesheet" type="text/css" />
		
		<script src="../scripts/jquery-1.8.3.min.js" type="text/javascript"></script>
		<script src="../scripts/jquery-ui-1.10.1.min.js" type="text/javascript"></script>
		<script src="../scripts/jAlerts/jquery.alerts.min.js" type="text/javascript"></script>
		<script src="../scripts/nyroModal/js/jquery.nyroModal.custom.min.js" type="text/javascript"></script>
		<script src="../scripts/eos_common.js" type="text/javascript"></script> 
	</head>
	<body>
		<div id="eos_wrapper_nomenu">
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