<?php
/*
Includes the following PHP functions:
sec2hms
*/
function sec2hms ($sec, $padHours = true){
	$hms = "";
	$hours = intval(intval($sec) / 3600); 
	$hms .= ($padHours) 
		? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
		: $hours. ":";
	$minutes = intval(($sec / 60) % 60); 
	$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
	$seconds = intval($sec % 60); 
	$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
	return $hms;
}
?>