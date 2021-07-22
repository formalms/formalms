
<h2 id="intro_title"><?php echo Lang::t('_TITLE_STEP1'); ?></h2>

<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		YAHOO.util.Event.addListener("language", "change", function(e) {
			var lang =YAHOO.util.Dom.get('language').value;


			var callback = {
				success: function(o) {
					// we get this back from lib.lang.php
					var res =YAHOO.lang.JSON.parse(o.responseText);
					YAHOO.util.Dom.get('intro_txt').innerHTML=res['intro'];
					YAHOO.util.Dom.get('intro_title').innerHTML=res['title'];
					YAHOO.util.Dom.get('btn_next').innerHTML=res['btn'];
				}
			};

			var sUrl ='index.php?set_lang='+lang;

			YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		});

	});
</script>


<div class="lang_sel_box">
<?php
	echo Form::getDropdown(Lang::t('_LANGUAGE').':', 'language', 'language', Lang::getLanguageList('language'), Lang::getSelLang());
?>

</div><div class="no_float"></div>

<div id="intro_txt">
<?php echo _INSTALLER_INTRO_TEXT; ?>
</div>