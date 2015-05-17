<?php require 'include/prehtml.php'; ?>
<?php
	$ss = '';
	if(isset($_GET['ss'])){
		$ss = filter_var($_GET['ss'], FILTER_SANITIZE_STRING);
	}
	$type = 'add';
	if(isset($_GET['type'])){
		$type = filter_var($_GET['type'], FILTER_SANITIZE_STRING);
	}
	$order_type = '';

	if($type == 'edit'){
		if(isset($_GET['order_id']) && isset($_GET['order_type'])){
			$order_id = filter_var($_GET['order_id'], FILTER_SANITIZE_NUMBER_INT);
			$order_type = filter_var($_GET['order_type'], FILTER_SANITIZE_STRING);
			if($order_type == 'bid'){
				$sql = "SELECT stock_bid.*, firm_stock.symbol AS ss FROM stock_bid LEFT JOIN firm_stock ON stock_bid.fid = firm_stock.fid WHERE stock_bid.id = '$order_id' AND stock_bid.pid = $eos_player_id";
				$existing_order = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			}else if($order_type == 'ask'){
				$sql = "SELECT stock_ask.*, firm_stock.symbol AS ss FROM stock_ask LEFT JOIN firm_stock ON stock_ask.fid = firm_stock.fid WHERE stock_ask.id = '$order_id' AND stock_ask.pid = $eos_player_id";
				$existing_order = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
			}else{
				$type = 'add';
			}
			if(empty($existing_order)){
				$type = 'add';
			}
		}else{
			$type = 'add';
		}
	}
?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				<?php
					if($type == 'edit'){ 
						echo "stockController.addOrderValidateSymbol('".$existing_order['ss']."', '".$order_type."');";
					}else if($ss){
						echo "stockController.addOrderValidateSymbol('".$ss."', '".$order_type."');";
					}
				?>
				jQuery('#order_expiration').datepicker({ minDate: 0, maxDate: "+1M" });
				<?php if($type !== 'edit') echo "jQuery('#order_expiration').datepicker('setDate', '+0');"; ?>
				jQuery('#add_order_form').on('click', '#order_submit_btn', function(){
					stockController.addOrder(<?= $type == 'edit' ? 1 : 0 ?>, <?= isset($_GET['force_redirect']) ? 0 : 1 ?>);
				});
				// Adding the holder to document to bypass preventDefault on checkbox elements
				jQuery(document).on('click', '#order_price_mp_holder', function(){
					if(jQuery("#order_price_mp").prop("disabled")) return false;
					if(jQuery("#order_price_mp").prop("checked")){
						document.getElementById("order_price").value = '';
						document.getElementById("order_price").disabled = true;
					}else{
						document.getElementById("order_price").disabled = false;
					}
				});
				jQuery('#order_symbol').on('change', function(){
					stockController.addOrderValidateSymbol(jQuery('#order_symbol').val());
				});
				jQuery('#order_type').on('change', function(){
					var order_type = jQuery('#order_type').val();
					if(order_type == 'ipo' || order_type == 'seo'){
						document.getElementById("order_price").value = parseInt(stockController.addOrderPOPrice)/100;
						document.getElementById("order_price").disabled = true;
						document.getElementById("order_price_mp").disabled = true;
						jQuery("#order_shares_aon").prop("checked", false);
						jQuery("#order_shares_aon").prop("disabled", true);
					}else if(order_type == 'obb'){
						document.getElementById("order_price").value = parseInt(stockController.addOrderBuybackPrice)/100;
						document.getElementById("order_price").disabled = true;
						document.getElementById("order_price_mp").disabled = true;
						jQuery("#order_shares_aon").prop("checked", false);
						jQuery("#order_shares_aon").prop("disabled", true);
					}else{
						if(order_type !== 'bid'){
							jQuery("#order_shares_aon").prop("checked", false);
							jQuery("#order_shares_aon").prop("disabled", true);
						}else{
							jQuery("#order_shares_aon").prop("disabled", false);
						}
						document.getElementById("order_price_mp").disabled = false;
						if(jQuery("#order_price_mp").prop("checked")){
							document.getElementById("order_price").value = '';
							document.getElementById("order_price").disabled = true;
						}else{
							document.getElementById("order_price").disabled = false;
						}
					}
				});
				jQuery('#add_order_form').on('keypress', 'input', function(e){
					if(e.which == 13){
						stockController.addOrder(<?= $type == 'edit' ? 1 : 0 ?>, <?= isset($_GET['force_redirect']) ? 0 : 1 ?>);
					}
				});
			});
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<?php
		if($type == 'edit'){
	?>
		<h3>Edit Order</h3>
		<div id="add_order_form" class="vert_middle">
			<input id="old_order_id" type="hidden" value="<?= $order_id ?>" />
			<input id="old_order_type" type="hidden" value="<?= $order_type ?>" />
			<label style="display:inline-block;width:200px;">Symbol</label>
			<input id="order_symbol" type="text" class="bigger_input" size="10" maxlength="10" pattern="[\w]*" value="<?= $existing_order['ss'] ?>" /> <span id="validate_symbol_result"></span><br />
			<label style="display:inline-block;width:200px;">Buy/Sell</label>
			<select id="order_type" class="bigger_input" disabled="disabled">
				<option value="">Enter Symbol First</option>
			</select><br />
			<label style="display:inline-block;width:200px;">Shares</label>
			<input id="order_shares" type="text" class="bigger_input" size="10" maxlength="9" pattern="\d*" value="<?= $existing_order['shares'] ?>" /> 
			<a id="order_shares_aon_holder" class="info"><input id="order_shares_aon" name="aon" class="bigger_input" type="checkbox" value="1"<?= (isset($existing_order['aon']) && $existing_order['aon']) ? ' checked="checked"' : '' ?> /><label for="order_shares_aon" style="margin-left:10px;">AON</label><span>All Or None, applies to Buy orders only.<br />All shares will be bought in one transaction, or no purchase will take place.</span></a>
			<br />
			<label style="display:inline-block;width:200px;">Limit Price ($)</label>
			<input id="order_price" type="text" class="bigger_input" size="10" maxlength="7" pattern="[\d.,]*" <?= ($existing_order['price'] == 999999999) ? 'disabled="disabled"' : 'value="'.($existing_order['price']/100).'"' ?> /> 
			<a id="order_price_mp_holder" class="info"><input id="order_price_mp" name="mp" class="bigger_input" type="checkbox" value="1"<?= $existing_order['price'] == 999999999 ? ' checked="checked"' : '' ?> /><label for="order_price_mp" style="margin-left:10px;">Use Market Price</label><span>Purchase shares at any price when available.<br /><br />DO NOT use this option for stocks with low trade volume. Completed trades will not be reversed.</span></a>
			<br />
			<label style="display:inline-block;width:200px;">Expiration <a class="info">(?)<span>Your order will expire at the end of this date. Defaults to end of today.</span></a></label>
			<input id="order_expiration" type="text" class="bigger_input" size="12" maxlength="20" value="<?= date('m/d/Y', strtotime($existing_order['expiration'])) ?>" /> 
			<br />
			<br />
			<span style="display:inline-block;width:200px;"></span><input id="order_submit_btn" class="bigger_input" type="button" value="Submit" />
			<br /><br />
			<div class="tbox_inline" style="width:300px;margin:0 auto;">
				You will be charged 1% commission for any completed purchase or sale.
			</div>
		</div>
	<?php
		}else{
	?>
		<h3>Add New Order</h3>
		<div id="add_order_form" class="vert_middle">
			<label style="display:inline-block;width:200px;">Symbol</label>
			<input id="order_symbol" type="text" class="bigger_input" size="10" maxlength="10" pattern="[\w]*" value="<?= $ss ?>" /> <span id="validate_symbol_result"></span><br />
			<label style="display:inline-block;width:200px;">Buy/Sell</label>
			<select id="order_type" class="bigger_input" disabled="disabled">
				<option value="">Enter Symbol First</option>
			</select><br />
			<label style="display:inline-block;width:200px;">Shares</label>
			<input id="order_shares" type="text" class="bigger_input" size="10" maxlength="9" pattern="\d*" /> 
			<a id="order_shares_aon_holder" class="info"><input id="order_shares_aon" name="aon" class="bigger_input" type="checkbox" value="1" /><label for="order_shares_aon" style="margin-left:10px;">AON</label><span>All Or None, applies to Buy orders only.<br />All shares will be bought in one transaction, or no purchase will take place.</span></a>
			<br />
			<label style="display:inline-block;width:200px;">Limit Price ($)</label>
			<input id="order_price" type="text" class="bigger_input" size="10" maxlength="7" pattern="[\d.,]*" /> 
			<a id="order_price_mp_holder" class="info"><input id="order_price_mp" name="mp" class="bigger_input" type="checkbox" value="1" /><label for="order_price_mp" style="margin-left:10px;">Use Market Price</label><span>Purchase shares at any price when available.<br /><br />DO NOT use this option for stocks with low trade volume. Completed trades will not be reversed.</span></a>
			<br />
			<label style="display:inline-block;width:200px;">Expiration <a class="info">(?)<span>Your order will expire at the end of this date. Defaults to end of today.</span></a></label>
			<input id="order_expiration" type="text" class="bigger_input" size="12" maxlength="20" /> 
			<br />
			<br />
			<span style="display:inline-block;width:200px;"></span><input id="order_submit_btn" class="bigger_input" type="button" value="Submit" />
			<br /><br />
			<div class="tbox_inline" style="width:300px;margin:0 auto;">
				You will be charged 1% commission for any completed purchase or sale.
			</div>
		</div>
	<?php
		}
	?>
	<div style="clear:both;">&nbsp;</div>
	<br />
	<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>