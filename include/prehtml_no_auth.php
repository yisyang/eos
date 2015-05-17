<?php
$show_page_gen_time = 0;
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
	$show_page_gen_time = 1;
}else{
	error_reporting(E_STRICT);
}
session_start();
date_default_timezone_set('America/Los_Angeles');
$timestart = microtime(1);

require_once 'scripts/db/dbconnrjeos.php';

function fbox_breakout($url, $redirect_msg = ''){
	if($redirect_msg){
		echo $redirect_msg . '<br />';
		echo 'Please wait, you are being redirected... or you may <a href="'.$url.'">click here</a>.';
		echo '
		<script type="text/javascript">
			setTimeout("window.top.location.href=\"'.$url.'\"", 2500);
		</script>';
	}else{
		echo '
		<script type="text/javascript">
			window.top.location.href="'.$url.'";
		</script>';
	}
	exit();
}
function fbox_redirect($url, $redirect_msg = '', $postParams = array()){
	if($redirect_msg){
		echo $redirect_msg . '<br />';
		if(empty($postParams)){
			echo 'Please wait, you are being redirected... or you may <a class="jqDialog" href="'.$url.'">click here</a>.';
			echo '
			<script type="text/javascript">
				setTimeout(function(){jqDialogInit("'.$url.'");}, 2500);
			</script>';
		}else{
			echo 'Please wait, you are being redirected... or you may <a class="jqDialog" href="'.$url.'" params="'.http_build_query($postParams).'">click here</a>.';
			echo '
			<script type="text/javascript">
				setTimeout(function(){jqDialogInit("'.$url.'", '.json_encode($postParams).');}, 2500);
			</script>';
		}
	}else{
		if(empty($postParams)){
			echo '
			<script type="text/javascript">
				jqDialogInit("'.$url.'");
			</script>';
		}else{
			echo '
			<script type="text/javascript">
				jqDialogInit("'.$url.'", '.json_encode($postParams).');
			</script>';
		}
	}
	exit();
}
function fbox_echoout($echo_msg = '', $back_link = '', $postParams = array()){
	echo '<div id="eos_body_fbox">';
	echo $echo_msg;
	echo '<br /><br />';
	if($back_link){
		if(empty($postParams)){
			echo '<a class="jqDialog" href="'.$back_link.'"><input type="button" class="bigger_input" value="Back" /></a> ';
		}else{
			echo '<a class="jqDialog" href="'.$back_link.'" params="'.http_build_query($postParams).'"><input type="button" class="bigger_input" value="Back" /></a> ';
		}
	}
	echo '<input type="button" class="bigger_input" value="Close" onclick="$(\'#jq-dialog-modal\').dialog(\'close\');" />';
	echo '</div>';
	exit();
}
function require_active_firm(){
	global $eos_firm_id;
	if(!$eos_firm_id){
		fbox_breakout("/eos/index.php");
	}
}
function number_format_readable ($num, $digits = 3, $dec_sep = '.', $k_sep = ','){
	$num_abs = abs($num);
	if($num_abs < 10){
		if((int)$num_abs != $num_abs){
			if(strlen($num_abs) < 6 && $num_abs < 0.01){
				return $num;
			}else{
				return number_format($num, max(0,$digits-1), $dec_sep, $k_sep);	
			}
		}else{
			return number_format($num, max(0,$digits-1), $dec_sep, $k_sep);	
		}
	}
	if($num_abs < 1000){
		if((int)$num_abs == $num_abs){
			return (int)$num;
		}
		if($num_abs < 100){
			return number_format($num, max(0,$digits-2), $dec_sep, $k_sep);
		}
		return number_format($num, max(0,$digits-3), $dec_sep, $k_sep);
	}
	if($num_abs < 1000000){
		if($num_abs < 10000){
			return number_format(floor($num/10)/100, max(0,$digits-1), $dec_sep, $k_sep).' k';
		}
		if($num_abs < 100000){
			return number_format(floor($num/100)/10, max(0,$digits-2), $dec_sep, $k_sep).' k';
		}
		return number_format(floor($num/1000), max(0,$digits-3), $dec_sep, $k_sep).' k';
	}
	if($num_abs < 1000000000){
		if($num_abs < 10000000){
			return number_format(floor($num/10000)/100, max(0,$digits-1), $dec_sep, $k_sep).' M';
		}
		if($num_abs < 100000000){
			return number_format(floor($num/100000)/10, max(0,$digits-2), $dec_sep, $k_sep).' M';
		}
		return number_format(floor($num/1000000), max(0,$digits-3), $dec_sep, $k_sep).' M';
	}
	if($num_abs < 1000000000000){
		if($num_abs < 10000000000){
			return number_format(floor($num/10000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' B';
		}
		if($num_abs < 100000000000){
			return number_format(floor($num/100000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' B';
		}
		return number_format(floor($num/1000000000), max(0,$digits-3), $dec_sep, $k_sep).' B';
	}
	if($num_abs < 1000000000000000){
		if($num_abs < 10000000000000){
			return number_format(floor($num/10000000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' T';
		}
		if($num_abs < 100000000000000){
			return number_format(floor($num/100000000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' T';
		}
		return number_format(floor($num/1000000000000), max(0,$digits-3), $dec_sep, $k_sep).' T';
	}else{
		if($num_abs < 10000000000000000){
			return number_format(floor($num/10000000000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' Q';
		}
		if($num_abs < 100000000000000000){
			return number_format(floor($num/100000000000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' Q';
		}
		return number_format(floor($num/1000000000000000), max(0,$digits-3), $dec_sep, $k_sep).' Q';
	}
}
?>