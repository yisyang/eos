<?php require 'include/prehtml.php'; ?>
<?php
	setcookie('rj_eos_active',1,time() + (86400 * 365),'/');
	$query = $db->prepare("SELECT player_news_last_read, world_news_last_read, system_news_last_read FROM players_extended WHERE id = ?");
	$query->execute(array($eos_player_id));
	$result = $query->fetch(PDO::FETCH_ASSOC);
	$player_news_last_read = $result["player_news_last_read"];
	$world_news_last_read = $result["world_news_last_read"];
	$system_news_last_read = $result["system_news_last_read"];
	
	$query = $db->prepare("SELECT COUNT(*) FROM player_news WHERE pid = ? AND date_created > ?");
	$query->execute(array($eos_player_id, $player_news_last_read));
	$player_news_count = $query->fetchColumn();

	$query = $db->prepare("SELECT COUNT(*) FROM world_news WHERE date_created > ?");
	$query->execute(array($world_news_last_read));
	$world_news_count = $query->fetchColumn();

	$query = $db->prepare("SELECT COUNT(*) FROM system_news WHERE date_created > ?");
	$query->execute(array($system_news_last_read));
	$system_news_count = $query->fetchColumn();
?>
<?php require 'include/functions.php'; ?>
<?php require 'include/html.php'; ?>
		<title><?= GAME_TITLE ?></title>
<?php require 'include/head.php'; ?>
		<script type="text/javascript">
<?php if($eos_firm_id){ ?>
			var getNewsRunning = 0;
			var activeButtonId = 'news_button_firm';
			jQuery(document).ready(function(){
				getNews('firm');
			});
<?php }else{ ?>
			var getNewsRunning = 0;
			var activeButtonId = 'news_button_player';
			jQuery(document).ready(function(){
				getNews('player');
			});
<?php } ?>
			
			function getNews(newsType, pageNum){
				if(typeof(pageNum) == "undefined"){
					pageNum = 0;
				}
				if(getNewsRunning){
					return false;
				}
				getNewsRunning = 1;
				if(newsType != "firm" && newsType != "firm_store" && newsType != "player" && newsType != "world"  && newsType != "system" && newsType != "overview"){
					getNewsRunning = 0;
					return false;
				}else{
					if(newsType == "firm"){
						buttonTitle = "Company";
					}
					if(newsType == "firm_store"){
						buttonTitle = "Store";
					}
					if(newsType == "player"){
						buttonTitle = "Player";
					}
					if(newsType == "world"){
						buttonTitle = "World";
					}
					if(newsType == "system"){
						buttonTitle = "System";
					}
					if(newsType == "overview"){
						buttonTitle = "Revenue Sheet";
					}
					var divId = "news_content";
					var buttonId = "news_button_"+newsType;
					jQuery("#"+buttonId).html(buttonTitle);

					$.ajax({
						type: "GET",
						url: "news-controller.php",
						data: { type: newsType, page: pageNum }
					}).done(function(news) {
						jQuery("#"+activeButtonId).removeClass("active");
						jQuery("#"+divId).fadeOut(300, function(){
							jQuery("#"+divId).html(news);
							document.getElementById(divId).scrollIntoView();
							jQuery("#"+divId).fadeIn(300);
							jQuery("#"+buttonId).addClass("active");
							activeButtonId = buttonId;
							getNewsRunning = 0;
						});
					});
				}
			}
		</script>
<?php require 'include/stats.php'; ?>
		<div id="eos_narrow_screen_padding">
<?php if($eos_firm_id){ ?>
			<span id="news_button_firm" class="mimic_button no_select active" onclick="getNews('firm')">Company</span> 
			<span id="news_button_firm_store" class="mimic_button no_select" onclick="getNews('firm_store')">Store</span> 
			<span id="news_button_overview" class="mimic_button no_select" onclick="getNews('overview')">Revenue Sheet</span> 
			<span id="news_button_player" class="mimic_button no_select" onclick="getNews('player')">Player (<?= $player_news_count ?>)</span> 
<?php }else{ ?>
			<span id="news_button_player" class="mimic_button no_select active" onclick="getNews('player')">Player (<?= $player_news_count ?>)</span> 
<?php } ?>
			<span id="news_button_world" class="mimic_button no_select" onclick="getNews('world')">World (<?= $world_news_count ?>)</span> 
			<span id="news_button_system" class="mimic_button no_select" onclick="getNews('system')">System (<?= $system_news_count ?>)</span> 
			<br /><br />
			<div id="news_content">Loading...</div>
		</div>
<?php require 'include/foot.php'; ?>