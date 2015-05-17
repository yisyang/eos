<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
		<script type="text/javascript">
			function addRequest(noRedirect){
				if(typeof(noRedirect) === 'undefined') noRedirect = 0;
				var pid = document.getElementById("request_pid").value;
				if(!pid){
					jAlert('Please select a product or enter product name!');
					return false;
				}
				var pidq = Math.round(stripCommas(document.getElementById("request_pidq").value));
				var pidn = Math.round(stripCommas(document.getElementById("request_pidn").value));
				if(jQuery("#request_pidn_unlimited").prop("checked")){
					pidn = -1;
				}
				var aon = 0;
				if(jQuery("#request_pidn_aon").prop("checked")){
					aon = 1;
				}
				var price = Math.round(stripCommas(document.getElementById("request_price").value)*100);
				var params = {action: 'add_request', pid: pid, pidq: pidq, pidn: pidn, price: price, aon: aon};

				marketController.executeAjax(params, function(){
					var patt = new RegExp('^.*\/([^/]*)');
					var currPage = window.location.pathname.replace(patt, "$1");
					var currSearch = window.location.search;
					if(currPage == 'market-requests.php' && currSearch == '?view_type=my'){
						jQuery('#jq-dialog-modal').dialog('close');
						marketController.showTableRequests(1, 'my', 0);
						firmController.getCash();
					}else{
						if(noRedirect){
							$('#add_request_form').html('Request successfully added.');
						}else{
							window.location.href = '/eos/market-requests.php?view_type=my';
						}
					}
				});
			}

			jQuery(document).ready(function(){
				jQuery('#add_request_form').on('click', '#request_submit_btn', function(){
					addRequest(<?= isset($_GET['no_redirect']) ? 1 : 0 ?>);
				});
				// Adding the holder to document to bypass preventDefault on checkbox elements
				jQuery(document).on('click', '#request_pidn_unlimited_holder', function(){
					if(jQuery("#request_pidn_unlimited").prop("checked")){
						document.getElementById("request_pidn").value = '';
						document.getElementById("request_pidn").disabled = true;
						jQuery("#request_pidn_aon").prop("checked", false);
					}else{
						document.getElementById("request_pidn").disabled = false;
					}
				});
				jQuery('#add_request_form').on('click', '#request_pidn_aon_holder', function(){
					jQuery("#request_pidn_unlimited").prop("checked", false);
					document.getElementById("request_pidn").disabled = false;
				});
				jQuery('#add_request_form').on('keypress', 'input', function(e){
					if(e.which == 13){
						addRequest(<?= isset($_GET['no_redirect']) ? 1 : 0 ?>);
					}
				});
				jQuery('#request_prod_name').on('change', function(){
					var prod_name = $(this).val();
					var matched = 0;
					if(prod_name == '') return false;
					jQuery("#request_pid option").each(function(i){
						if($(this).text() ==  prod_name){
							$(this).attr('selected', true);
							matched = 1;
						}
					});
					if(!matched){
						jAlert('No product matched.');
					}
					jQuery('#request_prod_name').val('');
				});
			});
		</script>
<?php require 'include/stats_fbox.php'; ?>
		<h3>Add New Request</h3>
<?php
	if(!$ctrl_b2b_buy){
?>
		Sorry, but you are not authorized to make B2B purchases on behalf of <?= $_SESSION['firm_name'] ?>.<br />
<?php
	}else{
?>
		<div id="add_request_form">
			<label style="display:inline-block;width:200px;">Product Wanted</label>
			<?php
				$prods = $db->query("SELECT list_prod.id, list_prod.name FROM list_prod LEFT JOIN list_cat ON list_prod.cat_id = list_cat.id WHERE list_cat.id IS NOT NULL AND list_cat.name NOT LIKE '-%' ORDER BY list_prod.name ASC")->fetchAll(PDO::FETCH_ASSOC);

				echo '<datalist id="list_prod">';
				foreach($prods as $prod){
					echo '<option value="'.$prod["name"].'" />';
				}
				echo '</datalist>';
				
				$prod_selection = '<select id="request_pid" class="bigger_input" style="width:150px;">';
				$prod_selection .= '<option value="">- Select Product -</option>';
				$selected_prod = isset($_GET['pid']) ? filter_var($_GET['pid'], FILTER_SANITIZE_NUMBER_INT) : 0;
				foreach($prods as $prod){
					$prod_selection .= '<option value="'.$prod["id"].'"'.($selected_prod == $prod["id"] ? ' selected="selected"' : '').'>'.$prod["name"].'</option>';
				}
				$prod_selection .= '</select>';
				echo $prod_selection;
			?>
			<input id="request_prod_name" type="text" class="bigger_input" style="width:240px;" list="list_prod" placeholder="... or Write Product Name" /><br />
			<label style="display:inline-block;width:200px;">Units to Purchase</label>
			<input id="request_pidn" type="text" class="bigger_input" size="10" maxlength="15" pattern="\d*" /> 
			<a id="request_pidn_unlimited_holder" class="info"><input id="request_pidn_unlimited" name="unlimited" class="bigger_input" type="checkbox" value="1" /><label for="request_pidn_unlimited" style="margin-left:10px;">Unlimited</label><span>Unlimited until canceled<br />You may at any time cancel your requests from the My Requests tab within the B2B Market.</span></a> 
			<a id="request_pidn_aon_holder" class="info"><input id="request_pidn_aon" name="aon" class="bigger_input" type="checkbox" value="1" /><label for="request_pidn_aon" style="margin-left:10px;">AON</label><span>All Or None<br />The supplier must supply the full quantity to you at once, or no purchase will take place.</span></a>
			<br />
			<label style="display:inline-block;width:200px;">Unit Price ($)</label>
			<input id="request_price" type="text" class="bigger_input" size="10" maxlength="9" pattern="[\d.,]*" /><br />
			<label style="display:inline-block;width:200px;">Min. Quality (Optional)</label>
			<input id="request_pidq" type="text" class="bigger_input" size="4" maxlength="4" pattern="\d*" /><br />
			<br />
			<span style="display:inline-block;width:200px;"></span><input id="request_submit_btn" class="bigger_input" type="button" value="Submit" />
			<br /><br />
			<div class="tbox_inline" style="width:400px;margin:0 auto;">
				Please note a non-refundable processing fee of $500 will be deducted for each product request.
			</div>
		</div>
<?php
	}
?>
	<br /><br />
	<div style="clear:both;">&nbsp;</div>
	<br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>