<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	session_start();
	
	require_once '../scripts/db/dbconnrjeos.php';
	
	if($_SESSION['admin_is_logged_in']){
		$id = filter_var($_SESSION['admin_id'], FILTER_SANITIZE_NUMBER_INT);
		$rk = filter_var($_SESSION['admin_rk'], FILTER_SANITIZE_STRING);
		$username = filter_var($_SESSION['admin_username'], FILTER_SANITIZE_STRING);
		$query = $db->prepare("SELECT COUNT(*) FROM admin WHERE id = ? AND rk = ?");
		$query->execute(array($id, $rk));
		$login_confirmed = $query->fetchColumn();
	}
	if(!$login_confirmed){
		unset($_SESSION['admin_is_logged_in']);
		unset($_SESSION['admin_rk']);
		header( 'Location: index.php' );
		exit();
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
			return number_format(floor($num/10000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' G';
		}
		if($num_abs < 100000000000){
			return number_format(floor($num/100000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' G';
		}
		return number_format(floor($num/1000000000), max(0,$digits-3), $dec_sep, $k_sep).' G';
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
			return number_format(floor($num/10000000000000)/100, max(0,$digits-1), $dec_sep, $k_sep).' P';
		}
		if($num_abs < 100000000000000000){
			return number_format(floor($num/100000000000000)/10, max(0,$digits-2), $dec_sep, $k_sep).' P';
		}
		return number_format(floor($num/1000000000000000), max(0,$digits-3), $dec_sep, $k_sep).' P';
	}
}
?>