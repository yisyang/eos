<?php require 'include/prehtml.php'; ?>
<?php require 'include/stats_fbox.php'; ?>
	<div id="f_color_form">
		<h3>Change Company Color</h3>
		Your company color does not affect actual gameplay in any way other than as a displayed color. An example of where it is used is the market share pie chart on the store's product details page.<br /><br />
		<img src="/images/success.gif" /> Company color can be changed at any time.<br /><br />
		<form onsubmit="firmController.updateCompanyColor();return false;">
			<?php
				$sql = "SELECT color FROM firms WHERE id = $eos_firm_id";
				$firm_color = $db->query($sql)->fetchColumn();
			?>
			<div style="float:left;">
				<input type="text" class="bigger_input" id="new_firm_color" size="7" maxlength="7" value="<?= $firm_color ?>" />
			</div>
			<div id="new_firm_color_preview" style="float:left;margin:5px;width:20px;height:20px;border:1px solid #000000;background-color:<?= $firm_color ?>"></div>
			<br /><br />
			<input id="f_color_submit" class="bigger_input" type="submit" value="Change Color" />
		</form>
		<script>
			jQuery('#new_firm_color').ColorPicker({
				onBeforeShow: function () {
					jQuery('#new_firm_color').ColorPickerSetColor(this.value);
				},
				onShow: function (colpkr) {
					jQuery(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					jQuery(colpkr).fadeOut(500);
					return false;
				},
				onSubmit: function(hsb, hex, rgb, el, parent) {
					jQuery(el).val('#' + hex);
					jQuery(el).ColorPickerHide();
				},
				onChange: function (hsb, hex, rgb) {
					jQuery('#new_firm_color_preview').css('backgroundColor', '#' + hex);
					jQuery('#new_firm_color').val('#' + hex);
				}
			})
			.on('keyup', function(){
				jQuery('#new_firm_color').ColorPickerSetColor(this.value);
			});
			jQuery('#new_firm_color_preview').on('click', function(){
				jQuery('#new_firm_color').ColorPickerShow();
			});
		</script>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>