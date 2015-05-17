<?php
define('NEW_PLAYER_ALLOWED', 1);
function check_new_player($required_progress = null){
	global $eos_player_is_new_user;
	if(!$eos_player_is_new_user){
		header( 'Location: index.php' );
		exit();
	}
	// if($required_progress && $eos_player_is_new_user != $required_progress){
		// header( 'Location: tutorial-'.$eos_player_is_new_user.'.php' );
		// exit();
	// }
}
?>