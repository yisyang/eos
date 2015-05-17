<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Map Generator - Preview</title>
<?php require '../include/head_subd.php'; ?>
<div style="position:relative;top:0;left:0;width:1520px;height:1200px;background-color:#5fca46;">
<?php
$map_seed_prefix = "city_";
$rand_min = 1;
$rand_max = 4;

function convert_array($iarray, $num = true){
	$array_count = max(array_keys($iarray));
	if($num){
		$oarray = '[';
		for($x = 0; $x <= $array_count; $x++){
			$oarray .= (0+$iarray[$x]) . ',' ;
		}
		$oarray = substr($oarray,0,-1).'];'; 
	}else{
		$oarray = '[';
		for($x = 0; $x <= $array_count; $x++){
			$oarray .= '"'. $iarray[$x] . '",' ;
		}
		$oarray = substr($oarray,0,-1).'];'; 
	}
	return $oarray;
}

if(isset($_POST['map_seed'])){
	$map_seed_src = strip_tags($_POST['map_seed']);
	$map_seed = explode(",", $map_seed_src);
	//print_r($map_seed);
	//exit();
}else{
	$map_seed = array('city_trees_1','road_ns','city_trees_2','road_ns','city_trees_3','road_ns','city_trees_1','road_ns','city_trees_2','city_trees_2','city_trees_1','city_trees_4','road_ns','city_trees_3','road_ns','city_3','city_trees_3','city_trees_4','city_trees_2','city_3','city_4','road_ns','city_trees_3','road_ns','city_trees_4','city_4','city_trees_4','city_trees_1','city_trees_3','city_trees_4','city_trees_1','city_trees_3','empty','city_trees_4','empty','city_trees_2','city_trees_3','city_trees_2','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','city_trees_1','city_trees_3','city_trees_1','empty','empty','empty','empty','empty','empty','empty','city_trees_4','city_trees_1','city_trees_4','road_ns','city_trees_3','road_ns','city_trees_1','city_trees_2','city_trees_2','city_trees_3','city_trees_4','city_trees_2','city_trees_3','city_trees_4','city_trees_2','city_trees_1','city_trees_1','city_trees_4','road_t_e','empty','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_sw','city_1','city_trees_4','city_trees_4','city_trees_3','city_trees_4','city_trees_2','city_trees_3','road_ns','grass','empty','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','road_ns','city_trees_2','city_trees_1','city_trees_3','city_trees_1','city_trees_4','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_4','city_trees_2','city_trees_3','road_ns','city_trees_1','city_trees_2','city_trees_3','city_trees_2','city_trees_1','city_trees_3','road_t_e','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_sw','city_trees_1','city_trees_2','city_trees_1','road_ns','city_trees_3','city_trees_2','city_trees_4','city_trees_2','city_trees_4','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_4','road_ns','city_4','city_trees_1','city_trees_4','road_ns','city_trees_2','city_trees_4','city_trees_4','city_trees_1','city_trees_4','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_3','city_3','road_ns','city_3','city_trees_1','road_ns','city_trees_3','city_trees_1','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_t_e','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_w','city_4','city_4','road_ns','city_3','city_trees_1','road_ns','city_trees_1','city_4','city_trees_2','city_4','city_3','city_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','city_1','road_ns','city_4','city_trees_4','city_trees_3','city_4','city_3','city_3','city_1','city_1','city_3','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_3','road_ns','city_4','city_3','city_3','city_4','road_ew','road_t_s','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_sw','road_ns','city_4','city_4','city_3','city_4','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','city_3','city_4','city_3','city_4','city_3','road_ne','road_sw','city_1','city_3','city_4','city_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_1','city_3','city_3','city_4','city_3','city_1','road_ns','city_1','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_w','city_trees_1','city_trees_4','city_2','city_1','city_3','road_ns','city_2','city_1','city_1','city_3','city_1','city_1','city_2','city_2','city_1','road_ns','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_4','city_3','city_4','city_3','road_ns','city_4','city_1','city_2','city_2','city_2','city_2','city_2','city_2','city_1','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_3','city_4','city_4','city_2','road_ns','city_1','city_2','city_trees_2','city_trees_3','city_trees_4','city_trees_1','city_trees_4','city_2','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_es','road_ew','road_ew','road_ew','road_t_s','road_ew','road_t_s','road_ew','road_ew','road_ew','road_sw','city_trees_2','road_ne','road_ew','road_ew','road_ew','road_sw','road_ns','city_3','city_2','city_trees_3','grass','city_fountain','grass','city_trees_2','city_2','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ne','road_sw','road_ns','city_1','city_4','city_trees_2','city_trees_4','grass','city_trees_3','city_trees_4','city_1','city_2','road_t_e','road_ew','road_x','road_ew','road_ew','road_wn','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ne','road_ew','road_ew','road_sw','city_trees_2','city_trees_2','city_4','road_ns','city_3','city_1','city_2','city_1','road_ns','city_2','city_2','city_1','city_1','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_3','road_ns','city_trees_3','city_4','city_2','city_1','road_ns','city_2','city_2','city_2','city_2','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_2','road_ns','city_1','city_3','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_t_n','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_t_w','road_ew','road_ew','road_ew','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_fountain','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_fountain','road_ns','city_fountain','road_ns','city_fountain','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_t_s','road_ew','city_2','city_1','city_2','city_1','city_2','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_3','road_ns','city_1','city_3','road_ns','city_1','city_4','city_2','city_2','city_2','city_2','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_1','road_ns','city_4','city_2','road_ns','city_2','city_4','road_ns','city_trees_3','city_trees_4','city_2','city_2','road_t_e','road_ew','road_x','road_ew','road_ew','road_sw','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_es','road_ew','road_ew','road_wn','city_trees_2','city_trees_2','city_3','road_ns','city_3','city_1','road_ns','city_4','city_3','road_ns','city_trees_2','city_2','city_1','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_4','road_ns','city_3','city_4','road_ns','city_4','city_3','road_ns','empty','city_2','city_1','road_ns','city_trees_4','road_ns','city_trees_2','city_trees_2','road_ne','road_ew','road_ew','road_ew','road_t_n','road_ew','road_t_n','road_ew','road_ew','road_ew','road_wn','city_trees_2','road_es','road_ew','road_ew','road_ew','road_ew','road_t_w','city_4','city_3','road_ns','city_3','city_4','road_ns','empty','empty','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_3','city_4','city_3','city_4','road_ns','city_4','city_4','road_ns','city_4','city_1','road_ns','empty','empty','empty','city_2','road_ns','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_4','city_trees_4','city_trees_1','city_3','road_ns','city_1','city_3','road_ns','city_3','city_4','road_ns','empty','empty','empty','empty','road_ns','city_trees_3','road_ns','city_1','city_3','city_3','city_4','city_3','city_1','city_4','city_1','city_3','city_4','city_4','city_3','city_1','city_4','road_ns','city_1','city_3','city_4','city_1','road_ns','city_2','city_1','road_ns','city_1','city_3','road_ns','empty','empty','empty','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_s','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_t_n','road_ew','road_ew','road_x','road_ew','road_ew','road_t_n','road_ew','road_ns','city_2','city_2','city_2','city_3','city_3','city_1','city_4','city_4','road_ns','city_1','city_4','city_4','city_1','city_3','road_ns','city_3','city_4','city_1','city_4','city_1','city_4','city_3','road_ns','grass','grass','grass','road_ns','city_2','city_3','city_1','city_3','city_1','city_2','city_3','city_4','road_ns','city_3','city_4','city_1','city_3','city_1','road_ns','city_4','city_1','city_3','city_4','city_3','city_1','city_4','road_ns','grass','grass','road_ew','road_ew','road_ew','road_ew','road_t_s','road_t_s','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_wn','city_4','city_3','city_4','road_ns','road_ns','city_3','city_4','road_ns','city_3','city_1','city_1','city_3','city_3','road_ns','city_4','city_1','city_3','city_3','city_4','city_3','city_4','city_3','city_4','road_ns','road_ns','city_1','city_4','road_ns','city_1','city_4','city_4','city_2','city_3','road_ns','city_4','city_trees_1','city_trees_3','city_trees_4','city_trees_2','city_4','city_1','road_ns','road_ns','city_1','city_4','road_t_e','road_ew','road_ew','road_sw','city_1','city_4','road_ns','city_1','city_trees_4','city_trees_2','city_trees_3','city_4','road_ns','road_ns','city_3','city_4','road_ns','city_1','city_4','road_ns','city_4','city_3','road_ns','city_3','city_trees_3','road_ne','road_wn','city_1','city_3','road_ns','city_2','city_4','road_ne','road_ew','road_ew','road_wn','city_trees_4','city_4','city_4','city_1','road_ns','city_1','city_2','city_1','city_4','city_1','city_3','city_1','city_3','road_ns','city_4','city_trees_4','city_trees_3','city_trees_2','city_4','city_3','road_ns','city_1','city_trees_1','city_1','road_ns','city_2','road_ns');
}
	$k = 0;
	$z_counter = 300;
	$left_start = 1300;
	$top_start = 20;
	$map_seed_prefix_len = strlen($map_seed_prefix);
	for($j=0;$j<16;$j++){
		$left = $left_start-70*$j-40*intval($j/2.2); // 1 sw = -30, 1 nw = -40
		$top = $top_start+5*$j-11*intval($j/2.2); // 1 sw = +16, 1 nw = -11
		$i_max = 2*$j - intval($j/4) + intval($j/2.2) + 1;
		for($i=0;$i<$i_max;$i++){
			//$map_seed[$k] = $map_seed_prefix.mt_rand($rand_min,$rand_max);
			$filename = $map_seed[$k];
			$k++;
			$left = $left + 40; //80
			$top = $top + 11; //21
			if(substr($filename,0,$map_seed_prefix_len) == $map_seed_prefix){
				$z_counter++;
				$z_index = intval($z_counter);
			}else{
				$z_counter += 0.34;
				$z_index = 300;
			}
			echo '<img class="no_select" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" src="../images/city/'.$filename.'.gif" width="70px" height="43px" />';
		}
	}
	for($j=0;$j<24;$j++){
		$left = $left_start-1280+10*$j-40*intval($j/4);
		$top = $top_start+36+27*$j-11*intval($j/4);
		$i_max = 33;
		for($i=0;$i<$i_max;$i++){
			//$map_seed[$k] = $map_seed_prefix.mt_rand($rand_min,$rand_max);
			$filename = $map_seed[$k];
			$k++;
			$left = $left + 40; //80
			$top = $top + 11; //21
			if(substr($filename,0,$map_seed_prefix_len) == $map_seed_prefix){
				$z_counter++;
				$z_index = intval($z_counter);
			}else{
				$z_counter += 0.34;
				$z_index = 300;
			}
			echo '<img class="no_select" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" src="../images/city/'.$filename.'.gif" width="70px" height="43px" />';
		}
	}
	for($j=0;$j<16;$j++){
		$left = $left_start-1280+10*$j-40*intval($j/4);
		$top = $top_start+618+27*$j-11*intval($j/4);
		$i_max = 2*(15-$j) - intval((15-$j)/4) + intval((15-$j)/2.2) + 1;
		for($i=0;$i<$i_max;$i++){
			//$map_seed[$k] = $map_seed_prefix.mt_rand($rand_min,$rand_max);
			$filename = $map_seed[$k];
			$k++;
			$left = $left + 40; //80
			$top = $top + 11; //21
			if(substr($filename,0,$map_seed_prefix_len) == $map_seed_prefix){
				$z_counter++;
				$z_index = intval($z_counter);
			}else{
				$z_counter += 0.34;
				$z_index = 300;
			}
			echo '<img class="no_select" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" src="../images/city/'.$filename.'.gif" width="70px" height="43px" />';
		}
	}
?>
</div>
<?php require '../include/foot_subd.php'; ?>