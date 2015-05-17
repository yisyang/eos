<?php require 'include/prehtml.php'; ?>
<?php
// Check if user is from FB, and set link to profile picture
$is_fb_player = 0;
$query = $db->prepare("SELECT login_id, avatar_filename FROM players WHERE id = ?");
$query->execute(array($eos_player_id));
$results = $query->fetch(PDO::FETCH_ASSOC);
if(!count($results)){
	echo "Error encountered, please report to admin. Error code SPA15.";
	exit();
}
$player_login_id = $results["login_id"];
$player_avatar_filename = $results["avatar_filename"];
if(!$player_avatar_filename){
	$player_avatar_filename = "no-avatar.jpg";
}
if($player_login_id){
	// Check if user is from FB
	$dbmain = rjdb_connect('site');
	$query = $dbmain->prepare("SELECT fb_id FROM users WHERE id = ?");
	$query->execute(array($player_login_id));
	$fb_player_fb_id = $query->fetchColumn();
	if($fb_player_fb_id){
		$is_fb_player = 1;
		// $fb_player_avatar_link = "https://graph.facebook.com/".$fb_player_fb_id."/picture?type=large";
	}
}
?>
<?php require 'include/stats_fbox.php'; ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var uploadParams = {action : 'player_avatar_ajaxupload', filename : 'player_avatar_up', maxSize : 5000000, relPath : 'images/players/', maxW : 120, maxH : 120};
				var extraParams = {reqTitle : 0, uploadUrl : 'settings_controller.php', uploadParams : uploadParams};
				DDInit('form_image_up', extraParams);
			});

			function ajaxConfirmFBPic(){
				var params = {action: 'player_avatar_ajaxupload_fb', fb_id: <?= $fb_player_fb_id ? $fb_player_fb_id : 0 ?>, maxSize : 5000000, relPath : 'images/players/', maxW : 120, maxH : 120};
				settingsController.executeAjax(params, function(resp){
					settingsController.updatePlayerAvatar(resp);
				});
			}
		</script>
		<h3>Change Player Avatar</h3>
		You can upload your own JPG or GIF image to be used as your avatar (suggested dimensions: 120px * 120px).<br /><br />
		<div style="float:left;width:300px;">
			<h3>Current Avatar</h3>
			<div id="avatar_preview" style="width:280px;height:130px;border:0;margin:0 auto;padding:0;text-align:center;overflow:hidden;">
				<img class="img_b3px" src="images/players/<?= $player_avatar_filename ?>" />
			</div>
		</div>
		<div style="float:left;width:300px;">	
			<h3>Upload New Avatar</h3>
			<div class="image_upload_control" style="text-align:center;">
				<form id="form_image_up" class="form_blank" style="width:302px;" action="#nogo" method="post" enctype="multipart/form-data">
					<div style="position:relative;top:0;left:0;">
						<input id="form_image_up_btn" type="file" style="visibility:hidden;position:absolute;top:0;left:0" name="player_avatar_up" />
						<div id="form_image_up_ddarea" class="drag_drop_area" onclick="getElementById('form_image_up_btn').click();">Click to Select an Image,<br />or Drag and Drop Image Here</div>
					</div>
					<br />
					<div id="form_image_up_progress"></div>
				</form>
			</div>
<?php
		if($is_fb_player){
			echo '<br /><br />
			<h3>Use FB Profile Pic</h3>
			<input class="bigger_input" type="button" value="or Use FB Profile Pic" onclick="ajaxConfirmFBPic(this.form)" />';
		}
?>
		</div>
		<div class="clearer"></div><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>