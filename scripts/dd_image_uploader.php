<?php
if($_SERVER["SERVER_NAME"] == "localhost"){
	error_reporting(E_ALL);
}else{
	error_reporting(E_STRICT);
}
	ini_set('memory_limit', '256M');
	
	function uploadImageMain($file_upfield_filename, $givenname, $use_exact_name, $tempFile, $maxSize, $filesize, $maxW, $maxH = null, $maxWThumb, $maxHThumb, $width_orig, $height_orig, $relPath, $colorR, $colorG, $colorB, $allowed_ext = "jpg,jpeg,gif,png,bmp", $relPathThumb){
		$folder = $relPath;
		$maxlimit = $maxSize;
		$match = 0;
		if($filesize > 0){
			if($filesize < 1){ 
				$errorList[] = _("File size is empty.");
			}
			if($filesize > $maxlimit){ 
				$errorList[] = _("File size is too big.");
			}
			$file_ext = preg_split("/\./",$file_upfield_filename);
			$allowed_ext = preg_split("/\,/",$allowed_ext);
			$filetype = strtolower(end($file_ext));
			foreach($allowed_ext as $ext){
				if($ext==$filetype){
					$match = 1; // File is allowed
					$ntime = substr(time(), -8);
					if($use_exact_name){
						$new_filename = $givenname.".".$filetype;
						$save = $folder.$new_filename;
						if($relPathThumb) $savet = $folder.$relPathThumb.$new_filename;
					}else{
						$new_filename = $givenname."_".$ntime.".".$filetype;
						$save = $folder.$new_filename;
						if($relPathThumb) $savet = $folder.$relPathThumb.$new_filename;
						while(file_exists($save)){
							$ntime = $ntime + 1;
							$new_filename = $givenname."_".$ntime.".".$filetype;
							$save = $folder.$new_filename;
							if($relPathThumb) $savet = $folder.$relPathThumb.$new_filename;
						}
					}
					if($maxH == null){
						if($width_orig < $maxW){
							$fwidth = $width_orig;
						}else{
							$fwidth = $maxW;
						}
						$ratio_orig = $width_orig/$height_orig;
						$fheight = $fwidth/$ratio_orig;

						$blank_height = $fheight;
						$top_offset = 0;
					}else{
						if($width_orig <= $maxW && $height_orig <= $maxH){
							$fheight = $height_orig;
							$fwidth = $width_orig;
						}else{
							if($width_orig > $maxW || $height_orig > $maxH){
								$fwidth_ratio = $width_orig / $maxW;
								$fheight_ratio = $height_orig / $maxH;
							}
							if($fheight_ratio > $fwidth_ratio){
								$fheight = $maxH;
								$fwidth = $width_orig / $fheight_ratio;
							}else{
								$fwidth = $maxW;
								$fheight = $height_orig / $fwidth_ratio;
							}
						}
						if($relPathThumb){
							if($width_orig <= $maxWThumb && $height_orig <= $maxHThumb){
								$fheight_thumb = $height_orig;
								$fwidth_thumb = $width_orig;
							}else{
								if($width_orig > $maxWThumb || $height_orig > $maxHThumb){
									$fwidth_ratio = $width_orig / $maxWThumb;
									$fheight_ratio = $height_orig / $maxHThumb;
								}
								if($fheight_ratio > $fwidth_ratio){
									$fheight_thumb = $maxHThumb;
									$fwidth_thumb = $width_orig / $fheight_ratio;
								}else{
									$fwidth_thumb = $maxWThumb;
									$fheight_thumb = $height_orig / $fwidth_ratio;
								}
							}
						}
						if($fheight == 0 || $fwidth == 0 || $height_orig == 0 || $width_orig == 0){
							die("FATAL ERROR: Image has no dimension.");
						}
						if($fheight < 24){
							$blank_height = 24;
							$top_offset = round(($blank_height - $fheight)/2);
						}else{
							$blank_height = $fheight;
						}
						if($fwidth < 24){
							$blank_width = 24;
							$left_offset = round(($blank_width - $fwidth)/2);
						}else{
							$blank_width = $fwidth;
						}
						if($relPathThumb){
							if($fheight_thumb < 24){
								$blank_height_thumb = 24;
								$top_offset_thumb = round(($blank_height_thumb - $fheight_thumb)/2);
							}else{
								$blank_height_thumb = $fheight_thumb;
							}
							if($fwidth_thumb < 24){
								$blank_width_thumb = 24;
								$left_offset_thumb = round(($blank_width_thumb - $fwidth_thumb)/2);
							}else{
								$blank_width_thumb = $fwidth_thumb;
							}
						}
					}

					$image_p = imagecreatetruecolor($blank_width, $blank_height);
					if($relPathThumb) $image_pt = imagecreatetruecolor($blank_width_thumb, $blank_height_thumb);

					switch($filetype){
						case "gif":
							$image = @imagecreatefromgif($tempFile);
							//the fun way of getting transparency
							$found = false;
							$colorRmin = max(0,$colorR - 20);
							$colorGmin = max(0,$colorG - 20);
							$colorBmin = max(0,$colorB - 20);
							$colorRmax = min(255,$colorR + 20);
							$colorGmax = min(255,$colorG + 20);
							$colorBmax = min(255,$colorB + 20);
							while($found == false) {
								$colorR = mt_rand($colorRmin, $colorRmax);
								$colorG = mt_rand($colorGmin, $colorGmax);
								$colorB = mt_rand($colorBmin, $colorBmax);
								if(imagecolorexact($image, $colorR, $colorG, $colorB == -1)) {
									$found = true;
								}
							}
							$bg_new = imagecolorallocate($image_p, $colorR, $colorG, $colorB);
							imagefill($image_p, 0, 0, $bg_new);
							imagecolortransparent($image_p, $bg_new);
							if($relPathThumb){
								$bg_new_t = imagecolorallocate($image_pt, $colorR, $colorG, $colorB);
								imagefill($image_pt, 0, 0, $bg_new_t);
								imagecolortransparent($image_pt, $bg_new_t);
							}
							break;
						case "jpg":
							$image = @imagecreatefromjpeg($tempFile);
							$bg_new = imagecolorallocate($image_p, $colorR, $colorG, $colorB);
							imagefill($image_p, 0, 0, $bg_new);
							if($relPathThumb){
								$bg_new_t = imagecolorallocate($image_pt, $colorR, $colorG, $colorB);
								imagefill($image_pt, 0, 0, $bg_new_t);
							}
							break;
						case "jpeg":
							$image = @imagecreatefromjpeg($tempFile);
							$bg_new = imagecolorallocate($image_p, $colorR, $colorG, $colorB);
							imagefill($image_p, 0, 0, $bg_new);
							if($relPathThumb){
								$bg_new_t = imagecolorallocate($image_pt, $colorR, $colorG, $colorB);
								imagefill($image_pt, 0, 0, $bg_new_t);
							}
							break;
						case "png":
							$image = @imagecreatefrompng($tempFile);
							// the fun way of getting transparency
							$found = false;
							$colorRmin = max(0,$colorR - 20);
							$colorGmin = max(0,$colorG - 20);
							$colorBmin = max(0,$colorB - 20);
							$colorRmax = min(255,$colorR + 20);
							$colorGmax = min(255,$colorG + 20);
							$colorBmax = min(255,$colorB + 20);
							while($found == false) {
								$colorR = mt_rand($colorRmin, $colorRmax);
								$colorG = mt_rand($colorGmin, $colorGmax);
								$colorB = mt_rand($colorBmin, $colorBmax);
								if(imagecolorexact($image, $colorR, $colorG, $colorB == -1)) {
									$found = true;
								}
							}
							$bg_new = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
							imagefill($image_p, 0, 0, $bg_new);
							imagecolortransparent($image_p, $bg_new);
							if($relPathThumb){
								$bg_new_t = imagecolorallocatealpha($image_pt, 0, 0, 0, 127);
								imagefill($image_pt, 0, 0, $bg_new_t);
								imagecolortransparent($image_pt, $bg_new_t);
							}
							break;
					}

					@imagecopyresampled($image_p, $image, $left_offset, $top_offset, 0, 0, $fwidth, $fheight, $width_orig, $height_orig);
					if($relPathThumb) @imagecopyresampled($image_pt, $image, $left_offset_thumb, $top_offset_thumb, 0, 0, $fwidth_thumb, $fheight_thumb, $width_orig, $height_orig);

					switch($filetype){
						case "gif":
							if(!@imagegif($image_p, $save)){
								$errorList[]= _("PERMISSION DENIED [GIF]");
							}else{
								if($relPathThumb) @imagegif($image_pt, $savet);
							}
						break;
						case "jpg":
							if(!@imagejpeg($image_p, $save, 100)){
								$errorList[]= _("PERMISSION DENIED [JPG]");
							}else{
								if($relPathThumb) @imagejpeg($image_pt, $savet);
							}
						break;
						case "jpeg":
							if(!@imagejpeg($image_p, $save, 100)){
								$errorList[]= _("PERMISSION DENIED [JPEG]");
							}else{
								if($relPathThumb) @imagejpeg($image_pt, $savet);
							}
						break;
						case "png":
							if(!@imagepng($image_p, $save, 0)){
								$errorList[]= _("PERMISSION DENIED [PNG]");
							}else{
								if($relPathThumb) @imagepng($image_pt, $savet);
							}
						break;
					}
					@imagedestroy($image);
				}
			}		
		}else{
			$errorList[]= _("NO FILE SELECTED");
		}
		if(!$match){
			$errorList[]= _("File type is not allowed: $file_upfield_filename");
		}
		if(isset($errorList)){
			if(sizeof($errorList) == 0){
				return array(1, $new_filename);
			}else{
				return array(0, $errorList);
			}
		}else{
			return array(1, $new_filename);
		}
	}

	function uploadImage($givenname, $use_exact_name = 0, $allowed_ext = null){
		if(isset($_FILES['file'])){
			$file_upfield_name = 'file';
			$file_upfield_filename = $_FILES[$file_upfield_name]['name'];
			$tempFile = $_FILES[$file_upfield_name]['tmp_name'];
			$filesize = $_FILES[$file_upfield_name]['size'];
			list($width_orig, $height_orig) = getimagesize($tempFile);
		}else if(isset($_POST['fileurl'])){
			$pattern = '/^((http|https|ftp):\/\/)?(\w+:{0,1}\w*@)?[a-zA-Z0-9\-\.]+\.?[a-zA-Z]{0,4}(:[a-zA-Z0-9]*)?\/?([a-zA-Z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~!])*[^\.\,\)\(\s]$/';
			$pattern2 = '/^((http|https|ftp)\:\/\/)(\w+:{0,1}\w*@)?[a-zA-Z0-9\-\.]+\.?[a-zA-Z]{0,4}(:[a-zA-Z0-9]*)?\/?([a-zA-Z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~!])*[^\.\,\)\(\s]$/';
			if(preg_match($pattern, $_POST['fileurl'])){
				if(preg_match($pattern2, $_POST['fileurl'])){
					$file_url = $_POST['fileurl'];
				}else{
					$file_url = "http://".$_POST['fileurl'];
				}
			}else{
				$errorList[] = _("Error: Invalid URL.")." ".$_POST['fileurl'];
				return array(0, $errorList);
			}
			$ch = curl_init($file_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			$input=curl_exec($ch);
			curl_close($ch);
			$tempFile = tempnam(sys_get_temp_dir(), 'upl');
			$temp = fopen($tempFile, "w");
			fwrite($temp, $input);
			fflush($temp);

			$filesize = filesize($tempFile);
			if($filesize > 0){
				if($filetypetemp = exif_imagetype($tempFile)){
					$file_upfield_filename = 'upl'.time().image_type_to_extension($filetypetemp);
				}else{
					$errorList[] = _("File type cannot be determined.");
					return array(0, $errorList);
				}
			}else{
				$errorList[] = _("File size is empty.")." ".$_POST['fileurl'];
				return array(0, $errorList);
			}

			list($width_orig, $height_orig) = getimagesize($tempFile);
		}else if(isset($_POST['filename'])){
			$file_upfield_name = strip_tags($_POST['filename']);
			$file_upfield_filename = $_FILES[$file_upfield_name]['name'];
			$tempFile = $_FILES[$file_upfield_name]['tmp_name'];
			$filesize = $_FILES[$file_upfield_name]['size'];
			list($width_orig, $height_orig) = getimagesize($tempFile);
		}else{
			$errorList[] = _("Unable to detect uploaded file.");
			return array(0, $errorList);
		}
		$maxSize = strip_tags($_POST['maxSize']);
		$maxW = strip_tags($_POST['maxW']);
		$relPathThumb = '';
		if(isset($_POST['relPathThumb'])){
			$relPathThumb = $_POST['relPathThumb'];
		}
		$maxWThumb = 0;
		if(isset($_POST['maxWThumb'])){
			$maxWThumb = strip_tags($_POST['maxWThumb']);
		}
		$maxHThumb = 0;
		if(isset($_POST['maxHThumb'])){
			$maxHThumb = strip_tags($_POST['maxHThumb']);
		}
		$relPath = strip_tags($_POST['relPath']);
		if(isset($_POST['colorR'])){
			$colorR = strip_tags($_POST['colorR']);
		}else{
			$colorR = 255;
		}
		if(isset($_POST['colorG'])){
			$colorG = strip_tags($_POST['colorG']);
		}else{
			$colorG = 255;
		}
		if(isset($_POST['colorB'])){
			$colorB = strip_tags($_POST['colorB']);
		}else{
			$colorB = 255;
		}
		if(isset($_POST['maxH'])){
			$maxH = strip_tags($_POST['maxH']);
		}else{
			$maxH = null;
		}

		if($filesize > 0){
			list($success, $new_filename) = uploadImageMain($file_upfield_filename, $givenname, $use_exact_name, $tempFile, $maxSize, $filesize, $maxW, $maxH, $maxWThumb, $maxHThumb, $width_orig, $height_orig, $relPath, $colorR, $colorG, $colorB, $allowed_ext, $relPathThumb);

			if($success){
				return array($success, $new_filename);
			}else{
				if(is_array($new_filename)){
					foreach($new_filename as $key => $value){
						if($value == "-ERROR-") {
							unset($new_filename[$key]);
						}
					}
					$errors = array_values($new_filename);
					foreach($errors as $error){
						$errorList[] = $error;
					}
					return array(0, $errorList);
				}else{
					$errorList[] = _("Unknown error");
					return array(0, $errorList);
				}
			}
			
		}else{
			$errorList[] = _("File size is empty");
			return array(0, $errorList);
		}
	}
?>