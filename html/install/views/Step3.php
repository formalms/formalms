<h2><?php echo Lang::t('_TITLE_STEP3'); ?></h2>

<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);

		YAHOO.util.Event.addListener("agree", "click", function(e) {
			disableBtnNext(!YAHOO.util.Dom.get('agree').checked);
		});
		
	});
</script>

<?php

$content="";
$fn = _installer_."/data/license/license_".Lang::getSelLang().".txt";
$english_fn = _installer_."/data/license/license_english.txt";

$handle=FALSE;
if ((!file_exists($fn)) && (file_exists($english_fn))) {
	$fn=$english_fn;
}

if (file_exists($fn)) {
	$handle = fopen($fn, "r");
	$content = fread($handle, filesize($fn));
	fclose($handle);
}

$text = '<label for="license" style="visibility: hidden;">'.( defined('_SOFTWARE_LICENSE') ? _SOFTWARE_LICENSE : 'License' ).'</label><br />';
//$text .= '<textarea rows="23" cols="62" id="license" name="license" readonly="readonly">';
$text .= '<div style="width:100%;overflow:auto;height:400px;border:1px solid #cccccc;"><pre>'
	.htmlspecialchars($content)
	.'</pre></div>';
$text .= '</textarea>';

$text.=Form::getCheckbox( Lang::t('_AGREE_LICENSE'), "agree", "agree", 1, false);

echo $text;

?>