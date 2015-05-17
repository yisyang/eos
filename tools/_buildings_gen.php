<?php require '../include/prehtml_subd.php'; ?>
<?php require '../include/html_subd.php'; ?>
		<title>Map Generator</title>
<?php require '../include/head_subd.php'; ?>
<img id="tiler_grass" style="border-color:#000000;border-width:1px;border-style:solid;" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('grass');" src="../images/city/grass.gif" />
<img id="tiler_city_1" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_1');" src="../images/city/city_1.gif" />
<img id="tiler_city_2" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_2');" src="../images/city/city_2.gif" />
<img id="tiler_city_3" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_3');" src="../images/city/city_3.gif" />
<img id="tiler_city_4" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_4');" src="../images/city/city_4.gif" />
<img id="tiler_city_fountain" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_fountain');" src="../images/city/city_fountain.gif" />
<img id="tiler_city_trees_1" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_trees_1');" src="../images/city/city_trees_1.gif" />
<img id="tiler_city_trees_2" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_trees_2');" src="../images/city/city_trees_2.gif" />
<img id="tiler_city_trees_3" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_trees_3');" src="../images/city/city_trees_3.gif" />
<img id="tiler_city_trees_4" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('city_trees_4');" src="../images/city/city_trees_4.gif" />
<img id="tiler_road_x" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_x');" src="../images/city/road_x.gif" />
<img id="tiler_road_ns" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_ns');" src="../images/city/road_ns.gif" />
<img id="tiler_road_ew" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_ew');" src="../images/city/road_ew.gif" />
<img id="tiler_road_ne" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_ne');" src="../images/city/road_ne.gif" />
<img id="tiler_road_es" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_es');" src="../images/city/road_es.gif" />
<img id="tiler_road_sw" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_sw');" src="../images/city/road_sw.gif" />
<img id="tiler_road_wn" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_wn');" src="../images/city/road_wn.gif" />
<img id="tiler_road_t_n" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_t_n');" src="../images/city/road_t_n.gif" />
<img id="tiler_road_t_e" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_t_e');" src="../images/city/road_t_e.gif" />
<img id="tiler_road_t_s" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_t_s');" src="../images/city/road_t_s.gif" />
<img id="tiler_road_t_w" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('road_t_w');" src="../images/city/road_t_w.gif" />
<img id="tiler_empty" style="border-color:#000000;border-width:1px;border-style:solid;" class="no_select" onclick="setTiler('empty');" src="../images/city/empty.gif" />

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
	$map_seed = array('empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','city_trees_4','city_trees_1','city_trees_4','road_ns','city_trees_3','road_ns','city_trees_1','city_trees_2','city_trees_2','city_trees_3','city_trees_4','city_trees_2','empty','empty','empty','empty','empty','city_trees_4','road_t_e','empty','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_sw','empty','empty','empty','empty','city_trees_4','city_trees_2','city_trees_3','road_ns','grass','empty','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','empty','empty','empty','city_trees_3','city_trees_1','city_trees_4','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_4','empty','empty','empty','empty','city_trees_2','city_trees_3','city_trees_2','city_trees_1','city_trees_3','road_t_e','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','empty','empty','empty','empty','empty','empty','city_trees_2','city_trees_4','city_trees_2','city_trees_4','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_4','road_ns','empty','empty','empty','empty','city_trees_2','city_trees_4','city_trees_4','city_trees_1','city_trees_4','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_3','city_3','road_ns','city_3','empty','empty','city_trees_3','city_trees_1','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_t_e','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_w','city_4','city_4','road_ns','city_3','empty','empty','city_trees_1','city_4','city_trees_2','city_4','city_3','city_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','city_1','road_ns','city_4','city_trees_4','empty','city_4','city_3','city_3','city_1','city_1','city_3','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_3','road_ns','city_4','city_3','city_3','city_4','road_ew','road_t_s','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_sw','road_ns','city_4','city_4','city_3','city_4','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','city_3','city_4','city_3','city_4','city_3','road_ne','road_sw','city_1','city_3','city_4','city_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_1','city_3','city_3','city_4','city_3','city_1','road_ns','city_1','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_w','city_trees_1','city_trees_4','city_2','city_1','city_3','road_ns','city_2','city_1','city_1','city_3','city_1','city_1','city_2','city_2','city_1','road_ns','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_4','city_3','city_4','city_3','road_ns','city_4','city_1','city_2','city_2','city_2','city_2','city_2','city_2','city_1','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_3','city_4','city_4','city_2','road_ns','city_1','city_2','city_trees_2','city_trees_3','city_trees_4','city_trees_1','city_trees_4','city_2','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_es','road_ew','road_ew','road_ew','road_t_s','road_ew','road_t_s','road_ew','road_ew','road_ew','road_sw','city_trees_2','road_ne','road_ew','road_ew','road_ew','road_sw','road_ns','city_3','city_2','city_trees_3','grass','city_fountain','grass','city_trees_2','city_2','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ne','road_sw','road_ns','city_1','city_4','city_trees_2','city_trees_4','grass','city_trees_3','city_trees_4','city_1','city_2','road_t_e','road_ew','road_x','road_ew','road_ew','road_wn','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ne','road_ew','road_ew','road_sw','city_trees_2','city_trees_2','city_4','road_ns','city_3','city_1','city_2','city_1','road_ns','city_2','city_2','city_1','city_1','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_3','road_ns','city_trees_3','city_4','city_2','city_1','road_ns','city_2','city_2','city_2','city_2','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_2','road_ns','city_1','city_3','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_t_n','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_fountain','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_fountain','road_ns','city_fountain','road_ns','city_fountain','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_t_s','road_ew','city_2','city_1','city_2','city_1','city_2','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_3','road_ns','city_1','city_3','road_ns','city_1','city_4','city_2','city_2','city_2','city_2','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_1','road_ns','city_4','city_2','road_ns','city_2','city_4','road_ns','city_trees_3','city_trees_4','city_2','city_2','road_t_e','road_ew','road_x','road_ew','road_ew','road_sw','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_es','road_ew','road_ew','road_wn','city_trees_2','city_trees_2','city_3','road_ns','city_3','city_1','road_ns','city_4','city_3','road_ns','city_trees_2','city_2','city_1','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_4','road_ns','city_3','city_4','road_ns','city_4','city_3','road_ns','empty','city_2','city_1','road_ns','city_trees_4','road_ns','city_trees_2','city_trees_2','road_ne','road_ew','road_ew','road_ew','road_t_n','road_ew','road_t_n','road_ew','road_ew','road_ew','road_wn','city_trees_2','road_es','road_ew','road_ew','road_ew','road_ew','road_t_w','city_4','city_3','road_ns','city_3','city_4','road_ns','empty','empty','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_3','city_4','city_3','city_4','road_ns','city_4','city_4','road_ns','city_4','city_1','road_ns','empty','empty','empty','city_2','road_ns','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_4','city_trees_4','city_trees_1','city_3','road_ns','city_1','city_3','road_ns','city_3','city_4','road_ns','empty','empty','empty','empty','road_ns','city_trees_3','road_ns','city_1','city_3','city_3','city_4','city_3','city_1','city_4','city_1','city_3','city_4','city_4','city_3','city_1','city_4','road_ns','city_1','city_3','city_4','city_1','road_ns','city_2','city_1','road_ns','city_1','city_3','road_ns','empty','empty','empty','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_s','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_t_n','road_ew','road_ew','road_x','road_ew','road_ew','road_t_n','road_ew','road_ns','city_2','city_2','city_2','city_3','city_3','city_1','city_4','city_4','road_ns','city_1','city_4','city_4','city_1','city_3','road_ns','city_3','city_4','city_1','city_4','city_1','city_4','city_3','road_ns','empty','empty','empty','road_ns','city_2','city_3','city_1','city_3','city_1','city_2','city_3','city_4','road_ns','city_3','city_4','city_1','city_3','city_1','road_ns','city_4','city_1','city_3','city_4','city_3','city_1','city_4','road_ns','empty','empty','road_ew','road_ew','road_ew','road_ew','road_t_s','road_t_s','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_wn','city_4','city_3','city_4','road_ns','road_ns','city_3','city_4','road_ns','city_3','city_1','city_1','city_3','city_3','road_ns','city_4','city_1','city_3','city_3','city_4','city_3','city_4','city_3','city_4','road_ns','road_ns','city_1','city_4','road_ns','city_1','city_4','city_4','city_2','city_3','road_ns','city_4','city_trees_1','city_trees_3','city_trees_4','city_trees_2','city_4','city_1','road_ns','road_ns','city_1','city_4','road_t_e','road_ew','road_ew','road_sw','city_1','city_4','road_ns','city_1','city_trees_4','city_trees_2','city_trees_3','city_4','road_ns','road_ns','city_3','city_4','road_ns','city_1','city_4','road_ns','city_4','city_3','road_ns','city_3','city_trees_3','road_ne','road_wn','city_1','city_3','road_ns','city_2','city_4','road_ne','road_ew','road_ew','road_wn','city_trees_4','city_4','city_4','city_1','road_ns','city_1','city_2','city_1','city_4','city_1','city_3','city_1','city_3','road_ns','city_4','city_trees_4','city_trees_3','city_trees_2','city_4','city_3','road_ns','city_1','city_trees_1','city_1','road_ns','city_2','empty');
}
	$k = 0;
	$z_counter = 300;
	$left_start = 1382;
	$top_start = 57;
	$map_seed_prefix_len = strlen($map_seed_prefix);
	for($j=10;$j<16;$j++){
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
			echo '<img id="tile',($k-1),'" class="no_select" onclick="newTile(',($k-1),');" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" src="../images/city/'.$filename.'.gif" width="50px" height="23px" />';
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
			echo '<img id="tile',($k-1),'" class="no_select" onclick="newTile(',($k-1),');" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" src="../images/city/'.$filename.'.gif" width="50px" height="23px" />';
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
			echo '<img id="tile',($k-1),'" class="no_select" onclick="newTile(',($k-1),');" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" src="../images/city/'.$filename.'.gif" width="50px" height="23px" />';
		}
	}
	//For debugging, finished z-index should be around 500 (started at 300);
	//echo "Current Z: ".$z_index;
	
	/*
	echo '<img class="no_select" style="position:absolute;left:710px;top:117px;z-index:800;" src="../images/city/bg_fact_fs.gif" width="50px" height="90px" />';
	echo '<img class="no_select" style="position:absolute;left:0px;top:426px;z-index:801;" src="../images/city/bg_fact_fb.gif" width="760px" height="224px" />';

	echo '<img class="no_select" style="position:absolute;left:300px;top:68px;z-index:327;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:460px;top:112px;z-index:328;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	
	echo '<img class="no_select" style="position:absolute;left:120px;top:67px;z-index:344;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:240px;top:100px;z-index:345;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:400px;top:144px;z-index:346;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:520px;top:177px;z-index:347;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';

	echo '<img class="no_select" style="position:absolute;left:30px;top:115px;z-index:365;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:150px;top:148px;z-index:366;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:270px;top:181px;z-index:367;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:430px;top:225px;z-index:368;" src="../images/fact/fact_new.gif" width="180px" height="80px" />';

	echo '<img class="no_select" style="position:absolute;left:90px;top:180px;z-index:383;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:210px;top:213px;z-index:384;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:370px;top:257px;z-index:385;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:490px;top:290px;z-index:386;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';

	echo '<img class="no_select" style="position:absolute;left:80px;top:250px;z-index:405;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:200px;top:283px;z-index:406;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:360px;top:327px;z-index:407;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:480px;top:360px;z-index:408;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';

	echo '<img class="no_select" style="position:absolute;left:140px;top:315px;z-index:424;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	echo '<img class="no_select" style="position:absolute;left:300px;top:359px;z-index:425;" src="../images/fact/fact_buy_land.gif" width="180px" height="80px" />';
	*/
	
	//echo '<img class="no_select" style="position:absolute;left:0px;top:0px;z-index:501;" src="../images/transparent.gif" width="760px" height="650px" usemap="#fact_imap" />';
?>
</div>
<form action="_buildings_gen.php" method="POST">
<?php
	$map_seed_str = '';
	$map_seed_length = count($map_seed);
	for($i=0;$i<$map_seed_length;$i++){
		$map_seed_str .= $map_seed[$i].',';
	}
	$map_seed_str = substr($map_seed_str, 0, -1);
	echo '<textarea id="map_seed_textbox" name="map_seed" rows="8" cols="100">';
	echo $map_seed_str;
	echo '</textarea>';
?>
<br />
<input type="button" value="Generate Seed" onclick="refreshSeed();" /> <input type="submit" value="Use Seed" /> <input type="button" value="View Map (in New Window)" onclick="viewMap();" />
<script type="text/javascript">
	var map_seed = <?php echo convert_array($map_seed,0); ?>
	var map_seed_size = map_seed.length;
	var map_seed_display = '';
	var current_tiler = 'grass';
	function setTiler(tiler){
		if(typeof(current_tiler) !== 'undefined'){
			document.getElementById('tiler_'+current_tiler).style.borderColor="#000000";
		}
		current_tiler = tiler;
		document.getElementById('tiler_'+current_tiler).style.borderColor="#ff0000";
	}
	function newTile(n){
		map_seed[n] = current_tiler;
		document.getElementById('tile'+n).src="../images/city/"+current_tiler+".gif";
	}
	function refreshSeed(){
		map_seed_display = '';
		for(i=0;i<map_seed_size;i++){
			map_seed_display = map_seed_display + map_seed[i] + ',';
		}
		document.getElementById('map_seed_textbox').innerHTML = map_seed_display.substr(0,map_seed_display.length-1);
	}
	function viewMap(){
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", "_buildings_gen_view.php");
		form.setAttribute("target", "_blank");

		var hiddenField = document.createElement("input");
		map_seed_display = document.getElementById('map_seed_textbox').innerHTML;
		hiddenField.setAttribute("name", "map_seed");
		hiddenField.setAttribute("value", map_seed_display);
		form.appendChild(hiddenField);
		document.body.appendChild(form);
		form.submit();
	}
</script>
</form>

<?php require '../include/foot_subd.php'; ?>