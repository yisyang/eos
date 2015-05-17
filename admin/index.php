<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	session_start();
	
	require_once '../scripts/db/dbconnrjeos.php';
	
	$login_confirmed = 0;

/********************************************
 * SECTION REMOVED
 *
 * Original purpose:
 *  Authorize admins and set session variables
 *
 * Variables to set:
 *  $_SESSION['admin_id'] = 123;
 *	$_SESSION['admin_rk'] = generateRandomKey();
 *	$login_confirmed = true;
 ********************************************/
	
	$username = "";
	if(isset($_SESSION['admin_username'])){
		$username = filter_var($_SESSION['admin_username'], FILTER_SANITIZE_STRING);
	}
?>
<?php require 'include/html.php'; ?>
		<title>Economies of Scale - Control Panel</title>
		<script type="text/javascript">
			function adminlogin(){
				// TODO: Use ajax post instead
				var username = encodeURIComponent(document.getElementById('username').value);
				var password = encodeURIComponent(document.getElementById('password').value);
				nocache = Math.random();
				var url="login.php?username="+username+"&password="+password+"&nocache="+nocache;
				document.getElementById("login_response").innerHTML="Waiting...";
				if (window.XMLHttpRequest){
					// code for IE7+, Firefox, Chrome, Opera, Safari
					var xmlhttp=new XMLHttpRequest();
				}else{
					// code for IE6, IE5
					var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange=function(){
					if (xmlhttp.readyState==4 && xmlhttp.status==200){
						if(xmlhttp.responseText == "OK"){
							window.location.href = "index.php";
						}else{
							document.getElementById("login_response").innerHTML=xmlhttp.responseText;
							return false;
						}
					}
				}
				xmlhttp.open("GET",url,true);
				xmlhttp.send();
			}
		</script>
<?php require 'include/head.php'; ?>
<?php require 'include/menu.php'; ?>
<?php
		if($login_confirmed){
?>
			<h3>Admin Control Panel</h3>
			You are logged in from: <?php echo $_SERVER['REMOTE_ADDR'];  ?><br />
			The last login is from <?php echo $_SESSION['ip_last'];  ?> on <?php echo $_SESSION['access_last'];  ?>
			<br /><br />
			<a href="list_cat.php">Product Categories List</a><br />
			<a href="list_prod.php">Products List</a><br />
			<a href="list_fact.php">Factories List</a><br />
			<a href="list_fact_choices.php">F Choices</a><br />
			<a href="list_rnd.php">RnD List</a><br />
			<a href="list_store.php">Stores List</a><br />
			<br />
			<a href="list_fc.php">Foreign Companies List</a><br />
			<a href="list_f_raw_mat.php">Foreign Companies Raw Materials Purchase List</a><br />
			<a href="list_f_goods.php">Foreign Companies Goods List</a><br />
<?php
		}else{
?>
			<h3>Control Panel Login</h3>
			<br />
			<form onsubmit="return false;">
				User: <input id="username" name="username" value="<?php echo $username; ?>" type="text" size="16" /> <br />
				Pass: <input id="password" name="password" type="password" size="16" /> <br /><br />
				<input name="submit" type="submit" value="Log In" onclick="adminlogin();" />
			</form>
			<br />
			<div id="login_response" style="clear: both;float: left;font-size: 12px;color: #888888;vertical-align: bottom;">&nbsp;</div>
<?php
		}
?>
<?php require 'include/foot.php'; ?>

<?php
/*
Factories List	list_fact	id (fact_id), name, cost, timecost, firstcost, firsttimecost
F Choices	list_fact_choices	id, fact_id, cost, timecost, ipid1, ipid1n, ipid1qm, ipid2, ipid2n, ipid2qm, ipid3, ipid3n, ipid3qm, ipid4, ipid4n, ipid4qm, opid1
Products List	list_prod	id (pid), cat_id, name, value, selltime
RnD List	list_rnd	id (rnd_id), name, cost, timecost, firstcost, firsttimecost
RnD Choices	list_rnd_choices	id, rnd_id, pid, cost, timecost, firstcost, firsttimecost, dep_rnd_id1, dep_rnd_id1n, dep_rnd_id2, dep_rnd_id2n, dep_rnd_id3, dep_rnd_id3n
Stores List	list_store	id (store_id), name, cost, timecost, firstcost, firsttimecost, cat_id1, cat_id2, cat_id3, cat_id4, cat_id5, cat_id6
*/
?>