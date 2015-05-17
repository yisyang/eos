<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php
	session_regenerate_id(); // Testing - regenerate session
	if(!$ctrl_bldg_view){
		header( 'Location: /eos/index.php' );
		exit();
	}

	function convert_cd_array($iarray, $num = true){
		$array_count = max(array_keys($iarray));
		if($num){
			$oarray = '[0,';
			for($x = 1; $x <= $array_count; $x++){
				$oarray .= (0+@$iarray[$x]) . ',' ;
			}
			$oarray = substr($oarray,0,-1).'];'; 
		}else{
			$oarray = '["",';
			for($x = 1; $x <= $array_count; $x++){
				$oarray .= '"'. @$iarray[$x] . '",' ;
			}
			$oarray = substr($oarray,0,-1).'];'; 
		}
		return $oarray;
	}
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?> - Buildings</title>
<?php require 'include/head.php'; ?>
	<script type="text/javascript" src="scripts/jqplot/jquery.jqplot.custom.js?ver=1.0"></script>
	<link rel="stylesheet" type="text/css" href="scripts/jqplot/jquery.jqplot.rj.css" />
<?php require 'include/stats.php'; ?>
<?php
	if($firm_locked){
		fbox_breakout('/eos/index.php');
	}
?>
	<div id="progress_panel" class="progress_panel">
		<div class="production_panel_label">Production 
			<a onclick="toggleDisplayNone('progress_prod');"><span id="progress_prod_ctrl">(-)</span></a>
		</div>
		<div id="progress_prod">Loading...</div>
		<br />
		<div class="production_panel_label">Research 
			<a onclick="toggleDisplayNone('progress_res');"><span id="progress_res_ctrl">(-)</span></a>
		</div>
		<div id="progress_res">Loading...</div>
	</div>
	<div style="position:relative;top:0;left:0;width:780px;height:650px;background-color:#5fca46;">
		<?php
			// Initialize pre-generated terrain map, $max_buildings hard-coded as defined by map layout
			$max_buildings = 32;
			$map_seed_prefix = "city_";
			
			/* BEGIN Map Generation */ 
			$map_seed = array('empty','empty','empty','empty','empty','empty','empty','empty','empty','empty','city_trees_4','city_trees_1','city_trees_4','road_ns','city_trees_3','road_ns','city_trees_1','city_trees_2','city_trees_2','city_trees_3','city_trees_4','city_trees_2','empty','empty','empty','empty','empty','city_trees_4','road_t_e','empty','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_sw','empty','empty','empty','empty','city_trees_4','city_trees_2','city_trees_3','road_ns','grass','empty','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','empty','empty','empty','city_trees_3','city_trees_1','city_trees_4','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_4','empty','empty','empty','empty','city_trees_2','city_trees_3','city_trees_2','city_trees_1','city_trees_3','road_t_e','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','empty','empty','empty','empty','empty','empty','city_trees_2','city_trees_4','city_trees_2','city_trees_4','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_4','road_ns','empty','empty','empty','empty','city_trees_2','city_trees_4','city_trees_4','city_trees_1','city_trees_4','city_trees_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_3','city_3','road_ns','city_3','empty','empty','city_trees_3','city_trees_1','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_t_e','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_w','city_4','city_4','road_ns','city_3','empty','empty','city_trees_1','city_4','city_trees_2','city_4','city_3','city_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','city_1','road_ns','city_4','city_trees_4','empty','city_4','city_3','city_3','city_1','city_1','city_3','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_3','road_ns','city_4','city_3','city_3','city_4','road_ew','road_t_s','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_sw','road_ns','city_4','city_4','city_3','city_4','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_3','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_4','city_3','city_4','city_3','city_4','city_3','road_ne','road_sw','city_1','city_3','city_4','city_1','road_ns','grass','grass','grass','grass','grass','grass','grass','grass','grass','road_ns','city_trees_4','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_1','city_1','city_3','city_3','city_4','city_3','city_1','road_ns','city_1','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_w','city_trees_1','city_trees_4','city_2','city_1','city_3','road_ns','city_2','city_1','city_1','city_3','city_1','city_1','city_2','city_2','city_1','road_ns','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_4','city_3','city_4','city_3','road_ns','city_4','city_1','city_2','city_2','city_2','city_2','city_2','city_2','city_1','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_3','city_4','city_4','city_2','road_ns','city_1','city_2','city_trees_2','city_trees_3','city_trees_4','city_trees_1','city_trees_4','city_2','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_es','road_ew','road_ew','road_ew','road_t_s','road_ew','road_t_s','road_ew','road_ew','road_ew','road_sw','city_trees_2','road_ne','road_ew','road_ew','road_ew','road_sw','road_ns','city_3','city_2','city_trees_3','grass','city_fountain','grass','city_trees_2','city_2','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ne','road_sw','road_ns','city_1','city_4','city_trees_2','city_trees_4','grass','city_trees_3','city_trees_4','city_1','city_2','road_t_e','road_ew','road_x','road_ew','road_ew','road_wn','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ne','road_ew','road_ew','road_sw','city_trees_2','city_trees_2','city_4','road_ns','city_3','city_1','city_2','city_1','road_ns','city_2','city_2','city_1','city_1','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_3','road_ns','city_trees_3','city_4','city_2','city_1','road_ns','city_2','city_2','city_2','city_2','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_2','road_ns','city_1','city_3','road_ew','road_ew','road_t_n','road_ew','road_ew','road_ew','road_t_n','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_fountain','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_fountain','road_ns','city_fountain','road_ns','city_fountain','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_t_s','road_ew','city_2','city_1','city_2','city_1','city_2','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_fountain','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_3','road_ns','city_1','city_3','road_ns','city_1','city_4','city_2','city_2','city_2','city_2','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_1','road_ns','city_4','city_2','road_ns','city_2','city_4','road_ns','city_trees_3','city_trees_4','city_2','city_2','road_t_e','road_ew','road_x','road_ew','road_ew','road_sw','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_es','road_ew','road_ew','road_wn','city_trees_2','city_trees_2','city_3','road_ns','city_3','city_1','road_ns','city_4','city_3','road_ns','city_trees_2','city_2','city_1','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','road_ns','grass','grass','grass','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_4','road_ns','city_3','city_4','road_ns','city_4','city_3','road_ns','empty','city_2','city_1','road_ns','city_trees_4','road_ns','city_trees_2','city_trees_2','road_ne','road_ew','road_ew','road_ew','road_t_n','road_ew','road_t_n','road_ew','road_ew','road_ew','road_wn','city_trees_2','road_es','road_ew','road_ew','road_ew','road_ew','road_t_w','city_4','city_3','road_ns','city_3','city_4','road_ns','empty','empty','city_2','road_ns','city_trees_3','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_3','city_4','city_3','city_4','road_ns','city_4','city_4','road_ns','city_4','city_1','road_ns','empty','empty','empty','city_2','road_ns','city_trees_2','road_ns','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','city_trees_2','road_ns','city_4','city_trees_4','city_trees_1','city_3','road_ns','city_1','city_3','road_ns','city_3','city_4','road_ns','empty','empty','empty','empty','road_ns','city_trees_3','road_ns','city_1','city_3','city_3','city_4','city_3','city_1','city_4','city_1','city_3','city_4','city_4','city_3','city_1','city_4','road_ns','city_1','city_3','city_4','city_1','road_ns','city_2','city_1','road_ns','city_1','city_3','road_ns','empty','empty','empty','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_t_s','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_t_n','road_ew','road_ew','road_x','road_ew','road_ew','road_t_n','road_ew','road_ns','city_2','city_2','city_2','city_3','city_3','city_1','city_4','city_4','road_ns','city_1','city_4','city_4','city_1','city_3','road_ns','city_3','city_4','city_1','city_4','city_1','city_4','city_3','road_ns','empty','empty','empty','road_ns','city_2','city_3','city_1','city_3','city_1','city_2','city_3','city_4','road_ns','city_3','city_4','city_1','city_3','city_1','road_ns','city_4','city_1','city_3','city_4','city_3','city_1','city_4','road_ns','empty','empty','road_ew','road_ew','road_ew','road_ew','road_t_s','road_t_s','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_x','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_ew','road_wn','city_4','city_3','city_4','road_ns','road_ns','city_3','city_4','road_ns','city_3','city_1','city_1','city_3','city_3','road_ns','city_4','city_1','city_3','city_3','city_4','city_3','city_4','city_3','city_4','road_ns','road_ns','city_1','city_4','road_ns','city_1','city_4','city_4','city_2','city_3','road_ns','city_4','city_trees_1','city_trees_3','city_trees_4','city_trees_2','city_4','city_1','road_ns','road_ns','city_1','city_4','road_t_e','road_ew','road_ew','road_sw','city_1','city_4','road_ns','city_1','city_trees_4','city_trees_2','city_trees_3','city_4','road_ns','road_ns','city_3','city_4','road_ns','city_1','city_4','road_ns','city_4','city_3','road_ns','city_3','city_trees_3','road_ne','road_wn','city_1','city_3','road_ns','city_2','city_4','road_ne','road_ew','road_ew','road_wn','city_trees_4','city_4','city_4','city_1','road_ns','city_1','city_2','city_1','city_4','city_1','city_3','city_1','city_3','road_ns','city_4','city_trees_4','city_trees_3','city_trees_2','city_4','city_3','road_ns','city_1','city_trees_1','city_1','road_ns','city_2','empty');
			
			$k = 0;
			$z_counter = 300;
			$left_start = 691;
			$top_start = 28.5;
			$map_seed_prefix_len = strlen($map_seed_prefix);
			for($j=10;$j<16;$j++){
				$left = $left_start-35*$j-20*intval($j/2.2); // 1 sw = -30, 1 nw = -40
				$top = $top_start+2.5*$j-5.5*intval($j/2.2); // 1 sw = +16, 1 nw = -11
				$i_max = 2*$j - intval($j/4) + intval($j/2.2) + 1;
				for($i=0;$i<$i_max;$i++){
					$tile_filename = $map_seed[$k];
					$k++;
					$left = $left + 20;
					$top = $top + 5.5;
					if(substr($tile_filename, 0, $map_seed_prefix_len) == $map_seed_prefix){
						$z_counter++;
						$z_index = intval($z_counter);
					}else{
						$z_counter += 0.34;
						$z_index = 300;
					}
					echo '<span class="no_select city_icon '.$tile_filename.'" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" ></span>';
				}
			}
			for($j=0;$j<24;$j++){
				$left = $left_start-640+5*$j-20*intval($j/4);
				$top = $top_start+18+13.5*$j-5.5*intval($j/4);
				$i_max = 33;
				for($i=0;$i<$i_max;$i++){
					$tile_filename = $map_seed[$k];
					$k++;
					$left = $left + 20;
					$top = $top + 5.5;
					if(substr($tile_filename, 0, $map_seed_prefix_len) == $map_seed_prefix){
						$z_counter++;
						$z_index = intval($z_counter);
					}else{
						$z_counter += 0.34;
						$z_index = 300;
					}
					echo '<span class="no_select city_icon '.$tile_filename.'" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" ></span>';
				}
			}
			for($j=0;$j<16;$j++){
				$left = $left_start-640+5*$j-20*intval($j/4);
				$top = $top_start+309+13.5*$j-5.5*intval($j/4);
				$i_max = 2*(15-$j) - intval((15-$j)/4) + intval((15-$j)/2.2) + 1;

				for($i=0;$i<$i_max;$i++){
					$tile_filename = $map_seed[$k];
					$k++;
					$left = $left + 20;
					$top = $top + 5.5;
					if(substr($tile_filename, 0, $map_seed_prefix_len) == $map_seed_prefix){
						$z_counter++;
						$z_index = intval($z_counter);
					}else{
						$z_counter += 0.34;
						$z_index = 300;
					}
					echo '<span class="no_select city_icon '.$tile_filename.'" style="position:absolute;left:'.$left.'px;top:'.$top.'px;z-index:'.$z_index.';" ></span>';
				}
			}
			/* END Map Generation */ 

			// Add map background(s)
			echo '<img class="no_select" style="position:absolute;left:0;top:0;z-index:0;" src="images/city/city_map_cutout.png" width="780" height="650" />';
		?>
		<?php
			// Add placeholders for buildings

			$position_x = array(0, 315, 375, 435, 555, 615, 271, 331, 391, 511, 571, 226, 286, 346, 466, 526, 181, 241, 301, 421, 481, 392, 512, 300, 360, 480, 540, 226, 286, 406, 466, 254, 374);
			$position_y = array(0, 75, 91, 108, 141, 157, 99, 115, 132, 165, 181, 123, 139, 156, 189, 205, 147, 163, 180, 213, 229, 278, 311, 278, 294, 327, 343, 318, 334, 367, 383, 350, 383);
			$position_z = array(0, 328, 329, 330, 333, 334, 372, 373, 374, 377, 378, 424, 425, 426, 428, 429, 471, 472, 473, 476, 477, 611, 613, 649, 650, 653, 654, 739, 740, 742, 743, 779, 781);
			
			for($j=1;$j<=$max_buildings;$j++){
				echo '<div id="building_image_'.$j.'" style="position:absolute;left:'.$position_x[$j].'px;top:'.$position_y[$j].'px;z-index:'.$position_z[$j].';"></div>';
				echo '<div id="building_icon_'.$j.'" class="no_select" style="position:absolute;left:'.($position_x[$j]+24).'px;top:'.($position_y[$j]+3).'px;z-index:'.($position_z[$j]+200).';width:40px;height:40px;"><span id="cd_icon_back_'.$j.'" class="anim_placeholder"></span><span id="cd_icon_'.$j.'" class="anim_placeholder" style="z-index:'.($position_z[$j]+202).';"></span></div>';
			}
			
			echo '<img class="no_select" style="position:absolute;left:0;top:0;z-index:9001;" src="images/transparent.gif" width="760" height="650" usemap="#bldg_imap" />';
		?>
		<map id="bldg_imap" name="bldg_imap">
		<?php
			$poly_cords_offset = array(0,24,30,8,90,24,60,40,0,24);
			for($j=1;$j<=$max_buildings;$j++){
				echo '<area id="cd_icon_title_',$j,'" href="#loading" onclick="bldgController.go(this.href);return false;" alt="" onmouseover="bldgController.setTip(',$j,');" onmouseout="bldgController.unsetTip();" shape="poly" coords="',$position_x[$j]+$poly_cords_offset[0],',',$position_y[$j]+$poly_cords_offset[1],',',$position_x[$j]+$poly_cords_offset[2],',',$position_y[$j]+$poly_cords_offset[3],',',$position_x[$j]+$poly_cords_offset[4],',',$position_y[$j]+$poly_cords_offset[5],',',$position_x[$j]+$poly_cords_offset[6],',',$position_y[$j]+$poly_cords_offset[7],',',$position_x[$j]+$poly_cords_offset[8],',',$position_y[$j]+$poly_cords_offset[9],'" />';
			}
		?>
		</map>
		<div id="tipbox"></div>
		<div id="chatbox"></div>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			bldgController.max_buildings = <?= $max_buildings ?>;
			bldgController.updateAllSlots();
			progressController.refreshQueue('all');
			setInterval('bldgController.countDown();', 1000);
			<?php if($settings_queue_countdown){ ?>
			setInterval('progressController.countDown();', 1000);
			<?php } ?>
			<?php
			if($settings_enable_chat){
				$sql = "SELECT chatbox_x, chatbox_y, chatbox_width, chatbox_height FROM players_extended WHERE id = $eos_player_id";
				$chatbox = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			?>
				chatController.config_x = <?= $chatbox['chatbox_x'] ?>;
				chatController.config_y = <?= $chatbox['chatbox_y'] ?>;
				chatController.config_width = <?= $chatbox['chatbox_width'] ?>;
				chatController.config_height = <?= $chatbox['chatbox_height'] ?>;
				chatController.init();
			<?php
			}
			?>
		});
	</script>
<?php require 'include/foot.php'; ?>