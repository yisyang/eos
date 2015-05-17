<?php
function check_referrer($referrer = "", $allow_direct_link = 1){
	if($referrer == ""){
		if(isset($_SERVER['HTTP_REFERER'])){
			$domain = parse_url($_SERVER['HTTP_REFERER']);
		}else{
			if($allow_direct_link){
				return true;
			}else{
				echo "This page is dynamically generated based on individual players. Please DO NOT link to this page. Thank you.";
				exit();
			}
		}
	}else{
		$domain = parse_url($referrer);
	}
	if(!($domain['host'] == "www.example.com" || $domain['host'] == "example.com" || $domain['host'] == "localhost")){
		echo "This page is dynamically generated based on individual players. Please DO NOT link to this page. Thank you.";
		exit();
	}
}
?>