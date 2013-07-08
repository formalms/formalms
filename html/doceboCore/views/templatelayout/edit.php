<script type="text/javascript">
YAHOO.namespace("TemplateLayout");


YAHOO.TemplateLayout.initPicker = function(id, color) {

	var oColorPickerMenu = new YAHOO.widget.Menu(id+"-picker-menu");

	var onButtonOption = function() {

		var oColorPicker = new YAHOO.widget.ColorPicker(oColorPickerMenu.body.id, {
			showcontrols: true,
			images: {
				PICKER_THUMB: "<?php echo Get::rel_path('base'); ?>/addons/yui/colorpicker/assets/picker_thumb.png",
				HUE_THUMB: "<?php echo Get::rel_path('base'); ?>/addons/yui/colorpicker/assets/hue_thumb.png"
			},
			ids: {
				R: id+"-picker-r",
				R_HEX: id+"-picker-rhex",
				G: id+"-picker-g",
				G_HEX: id+"-picker-ghex",
				B: id+"-picker-b",
				B_HEX: id+"-picker-bhex",
				H: id+"-picker-h",
				S: id+"-picker-s",
				V: id+"-picker-v",
				PICKER_BG: id+"-picker-bg",
				PICKER_THUMB: id+"-picker-thumb",
				HUE_BG: id+"-picker-hue-bg",
				HUE_THUMB: id+"-picker-hue-thumb",
				HEX: id+"-picker-hex",
				SWATCH: id+"-picker-swatch",
				WEBSAFE_SWATCH: id+"-picker-websafe-swatch",
				CONTROLS: id+"-picker-controls",
				RGB_CONTROLS: id+"-picker-rgb-controls",
				HSV_CONTROLS: id+"-picker-hsv-controls",
				HEX_CONTROLS: id+"-picker-hex-controls",
				HEX_SUMMARY: id+"-picker-hex-summary",
				CONTROLS_LABEL: id+"-picker-controls-label"
			},
			txt: {
				ILLEGAL_HEX: "<?php echo Lang::t('_ILLEGAL_HEX_VALUE', 'template'); ?>",//"Illegal hex value entered",
				SHOW_CONTROLS: "<?php echo Lang::t('_SHOW_COLOR_DETAILS', 'template'); ?>",//"Show color details",
				HIDE_CONTROLS: "<?php echo Lang::t('_HIDE_COLOR_DETAILS', 'template'); ?>",//"Hide color details",
				CURRENT_COLOR: "<?php echo Lang::t('_CURRENTLY_SELECTED_COLOR', 'template'); ?>",//"Currently selected color: {rgb}",
				CLOSEST_WEBSAFE: "<?php echo Lang::t('_CLOSEST_WEBSAFE_COLOR', 'template'); ?>",//"Closest websafe color: {rgb}. Click to select.",
				R: "R",
				G: "G",
				B: "B",
				H: "H",
				S: "S",
				V: "V",
				HEX: "#",
        DEG: "\u00B0",
				PERCENT: "%"
			}
		});

		//catch submit event for input text
		YAHOO.util.Event.addListener(id+"-picker-hex", "keypress", function(e) {
			YAHOO.util.Event.stopEvent(e);
			//alert("ASD");
		});

		//YAHOO.util.KeyListener("", {keys:13}, functionToDo);

		oColorPickerMenu.body.style.width = "380px";
		oColorPickerMenu.body.style.height = "200px";

		oColorPicker.on("rgbChange", function (p_oEvent) {
			var sColor = "#" + this.get("hex");
			oButton.set("value", sColor);
			YAHOO.util.Dom.setStyle(id+"-current-color", "backgroundColor", sColor);
			YAHOO.util.Dom.get(id+"-current-color-hex").innerHTML = sColor;
		});

		// Remove this event listener so that this code runs only once
		this.unsubscribe("option", onButtonOption);
	}

	//Create a Button instance of type "split"
	var oButton = new YAHOO.widget.Button({
		type: "split",
		id: id+"-picker-button",
		label: '<em id="'+id+'-current-color">&nbsp;&nbsp;</em>&nbsp;<span id="'+id+'-current-color-hex">#'+color+'</span>',
		menu: oColorPickerMenu,
		container: id//"color_container" //this
	});

	oButton.on("appendTo", function () {
		oColorPickerMenu.setBody(" ");
		oColorPickerMenu.body.id = id+"-picker-container";
		// Render the Menu into the Button instance's parent element
		oColorPickerMenu.render(this.get("container"));
	});

	oButton.on("option", onButtonOption);
	oButton.on("click", function () { });

};


YAHOO.util.Event.onDOMReady(function() {
	YAHOO.TemplateLayout.initPicker("color_1", "<?php echo isset($data['color_1']) ? $data['color_1'] : 'FFFFFF'; ?>");
	YAHOO.TemplateLayout.initPicker("color_2", "<?php echo isset($data['color_2']) ? $data['color_2'] : 'FFFFFF'; ?>");
	YAHOO.TemplateLayout.initPicker("color_3", "<?php echo isset($data['color_3']) ? $data['color_3'] : 'FFFFFF'; ?>");
	YAHOO.TemplateLayout.initPicker("color_4", "<?php echo isset($data['color_4']) ? $data['color_4'] : 'FFFFFF'; ?>");
	YAHOO.TemplateLayout.initPicker("color_5", "<?php echo isset($data['color_5']) ? $data['color_5'] : 'FFFFFF'; ?>");
	YAHOO.TemplateLayout.initPicker("color_6", "<?php echo isset($data['color_6']) ? $data['color_6'] : 'FFFFFF'; ?>");
});
</script>
<?php echo getBackUi('index.php?r=adm/templatelayout/show', Lang::t('_BACK', 'template')); ?>
<?php
if (isset($error)) {
	echo $error;
} else {
?>

<?php
	if (isset($id)) {
		echo Form::openForm('edit_template_form', 'index.php?r=adm/templatelayout/update', false, 'POST', 'multipart/form-data');
		echo Form::getHidden('id_tmpl', 'id_tmpl', $id);
	} else {
		echo Form::openForm('create_template_form', 'index.php?r=adm/templatelayout/save', false, 'POST', 'multipart/form-data');
	}
?>

<?php
	echo '<label for="color_1">'.Lang::t('_COLOR_1', 'template').':</label>&nbsp;<span id="color_1"></span>';
	echo Form::getBreakRow();
	echo '<label for="color_2">'.Lang::t('_COLOR_2', 'template').':</label>&nbsp;<span id="color_2"></span>';
	echo Form::getBreakRow();
	echo '<label for="color_3">'.Lang::t('_COLOR_3', 'template').':</label>&nbsp;<span id="color_3"></span>';
	echo Form::getBreakRow();
	echo '<label for="color_4">'.Lang::t('_COLOR_4', 'template').':</label>&nbsp;<span id="color_4"></span>';
	echo Form::getBreakRow();
	echo '<label for="color_5">'.Lang::t('_COLOR_5', 'template').':</label>&nbsp;<span id="color_5"></span>';
	echo Form::getBreakRow();
	echo '<label for="color_6">'.Lang::t('_COLOR_6', 'template').':</label>&nbsp;<span id="color_6"></span>';
	echo Form::getBreakRow();

	echo Form::getFilefield(Lang::t('_LOGO_1'), 'logo_1', 'logo_1', isset($data['logo_1']) ? $data['logo_1'] : '');
	echo Form::getFilefield(Lang::t('_LOGO_2'), 'logo_2', 'logo_2', isset($data['logo_2']) ? $data['logo_2'] : '');

?>

<?php
	echo Form::openButtonSpace();
	echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
	echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
	echo Form::closeButtonSpace();
	echo Form::closeForm();
?>
<?php } ?>
<?php echo getBackUi('index.php?r=adm/templatelayout/show', Lang::t('_BACK', 'template')); ?>