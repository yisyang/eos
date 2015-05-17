<?php require '../include/prehtml_subd.php'; ?>
<?php
if(isset($_GET["fid"])){
	$fid = filter_var($_GET['fid'], FILTER_SANITIZE_NUMBER_INT);
}
function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    if ($thick == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) {
        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}
if($fid){
	$query = $db->prepare("SELECT * FROM history_firms WHERE fid = ? ORDER BY history_date DESC LIMIT 0,14");
	$query->execute(array($fid));
	$history_firms = $query->fetchAll(PDO::FETCH_ASSOC);
	$hf_count = count($history_firms);
	
	//Zerofill and populate arrays
	$timenow = time();
	$time_diff = intval(date("Gi",$timenow))-100;
	if($time_diff > 0){
		for($i=0; $i<14; $i++){
			$firm_hist_date[$i] = date("m/d",$timenow-(86400*(13-$i)));
			$firm_networth[$i] = 0;
			$firm_store_sales[$i] = 0;
			$firm_b2b_sales[$i] = 0;
		}
	}else{
		for($i=0; $i<14; $i++){
			$firm_hist_date[$i] = date("m/d",$timenow-(86400*(14-$i)));
			$firm_networth[$i] = 0;
			$firm_store_sales[$i] = 0;
			$firm_b2b_sales[$i] = 0;
		}
	}
	for($i=0; $i<$hf_count; $i++){
		$firm_hist_date[(13-$i)] = date("m/d",strtotime($history_firms[$i]["history_date"]));
		$firm_networth[(13-$i)] = $history_firms[$i]["networth"];
		$firm_store_sales[(13-$i)] = $history_firms[$i]["store_sales"];
		$firm_b2b_sales[(13-$i)] = $history_firms[$i]["b2b_sales"];
	}
	$firm_nw_max = max($firm_networth);
	$firm_nw_min = min($firm_networth);
	$firm_store_sales_max = max($firm_store_sales);
	$firm_store_sales_min = min($firm_store_sales);
	$firm_b2b_sales_max = max($firm_b2b_sales);
	$firm_b2b_sales_min = min($firm_b2b_sales);
	
	$firm_universal_max = max($firm_nw_max, $firm_store_sales_max, $firm_b2b_sales_max);
	$firm_universal_min = min($firm_nw_min, $firm_store_sales_min, $firm_b2b_sales_min);
	$firm_universal_range = $firm_universal_max - $firm_universal_min;
	for($i=0;$i<6;$i++){
		$firm_universal_display[$i] = '$'.number_format_readable(($firm_universal_min+($i/5*$firm_universal_range))/100);
	}
	
	if($firm_universal_range > 0){
		for($i=0; $i<14; $i++){
			$firm_nw_norm[$i] = 30+floor(300 * ($firm_networth[$i]-$firm_universal_min)/$firm_universal_range);
			$firm_store_sales_norm[$i] = 30+floor(300 * ($firm_store_sales[$i]-$firm_universal_min)/$firm_universal_range);
			$firm_b2b_sales_norm[$i] = 30+floor(300 * ($firm_b2b_sales[$i]-$firm_universal_min)/$firm_universal_range);
		}
	}else{
		for($i=0; $i<14; $i++){
			$firm_nw_norm[$i] = 180;
			$firm_store_sales_norm[$i] = 180;
			$firm_b2b_sales_norm[$i] = 180;
		}
	}

	$imgWidth=480;
	$imgHeight=450;
	$image=imagecreate($imgWidth, $imgHeight);
	$colorWhite=imagecolorallocate($image, 255, 255, 255);
	$colorGrey=imagecolorallocate($image, 192, 192, 192);
	$colorBlue=imagecolorallocate($image, 0, 0, 255);
	$colorRed=imagecolorallocate($image, 255, 0, 0);
	$colorGreen=imagecolorallocate($image, 0, 200, 0);
	$colorTransparent=imagecolorallocate($image, 0, 0, 0);
	$colorBlack=imagecolorallocate($image, 10, 10, 10);
	imagefill($image, 0, 0, $colorTransparent);
	imagecolortransparent($image, $colorTransparent);
	imagefilledrectangle($image, 10, 0, 400, 359, $colorWhite);
	imagerectangle($image, 10, 0, 400, 359, $colorGrey);
	for($i=0;$i<14;$i++){
		imageline($image, $i*30+10, 0, $i*30+10, 359, $colorGrey);
		imagestringup($image, 4, $i*30+2, 405, $firm_hist_date[$i], $colorBlack);
	}
	for($i=1;$i<12;$i++){
		imageline($image, 10, $i*30, 400, $i*30, $colorGrey);
	}
	for($i=0;$i<13;$i++){
		imagelinethick($image, $i*30+10, (360-$firm_nw_norm[$i]), ($i+1)*30+10, (360-$firm_nw_norm[$i+1]), $colorBlue,2);
		imagelinethick($image, $i*30+10, (360-$firm_store_sales_norm[$i]), ($i+1)*30+10, (360-$firm_store_sales_norm[$i+1]), $colorRed,2);
		imagelinethick($image, $i*30+10, (360-$firm_b2b_sales_norm[$i]), ($i+1)*30+10, (360-$firm_b2b_sales_norm[$i+1]), $colorGreen,2);
	}
	for($i=0;$i<6;$i++){
		imagestring($image, 4, 410, 323-$i*60, $firm_universal_display[$i], $colorBlack);
	}
	imagefilledrectangle($image, 10, 420, 400, 449, $colorWhite);
	imagerectangle($image, 10, 420, 400, 449, $colorGrey);
	
	imagefilledrectangle($image, 27, 432, 42, 437, $colorBlue);
	imagestring($image, 4, 55, 427, "Networth", $colorBlack);
	imagefilledrectangle($image, 142, 432, 157, 437, $colorRed);
	imagestring($image, 4, 170, 427, "Store Sales", $colorBlack);
	imagefilledrectangle($image, 280, 432, 295, 437, $colorGreen);
	imagestring($image, 4, 308, 427, "B2B Sales", $colorBlack);
	//imagettftext($image, 20, 0, 100, 100, $colorBlack, "arial", "test");
	
	header("Content-type: image/png");
	imagepng($image);
	imagedestroy($image);
}else{
	echo "Error: Unauthorized Access.";
	exit();
}
?>