<h2><?php echo Lang::t('_TITLE_STEP6'); ?></h2>

<h3><?php echo Lang::t('_DATABASE'); ?></h3>
<div id="import_db_info">
	<span id="loading_db"><img src="<?php echo getTemplatePath() ?>images/loading.gif" alt="loading" /></span>
	<?php echo Lang::t('_DB_IMPORTING'); ?>
</div>
<br/>
<div id="logs" style="white-space:pre;overflow:auto;max-height:200px;"></div>
<h3><?php echo Lang::t('_LANGUAGES'); ?></h3>
<div id="import_lang_info">
	<?php foreach ($_SESSION["lang_install"] as $code => $ok): ?>
			<span id="loading_img_<?php echo $code; ?>" style="visibility: hidden;"><img src="<?php echo getTemplatePath() ?>images/loading.gif" alt="loading" /></span>
			<span><?php echo ucfirst($code); ?></span>
	<?php endforeach; ?>
</div>
<br/>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {

		var callback_db = {
			success: function(o) {
				var res =YAHOO.lang.JSON.parse(o.responseText);
				if(res['result'] == true) {
					YAHOO.util.Dom.get('loading_db').childNodes[0].src='templates/standard/images/complete.png';
					importLanguages();
				} else {
					YAHOO.util.Dom.get('loading_db').childNodes[0].src='templates/standard/images/failed.png';
					YAHOO.util.Dom.get('logs').style.border = '1px solid #cccccc';
					YAHOO.util.Dom.get('logs').style.padding = '4px';
					YAHOO.util.Dom.get('logs').innerHTML = res['text'];
				}

			}
		};
		var sUrl ='import_db.php';
		disableBtnNext(true);
		YAHOO.util.Connect.asyncRequest('GET', sUrl, callback_db);
	});
	function importLanguages() {

		var language =new Array('<?php echo implode("','", array_keys($_SESSION["lang_install"])); ?>');
		var platform =new Array('<?php echo implode("','", array_keys($_SESSION["platform_arr"])); ?>');

		var prev_lang ='';
		var callback = {
			success: function(o) {
				var res =YAHOO.lang.JSON.parse(o.responseText);
				var next_lang =res['next_lang'];
				var current_lang =res['current_lang'];
				var next_platform =res['next_platform'];

				if (next_lang != current_lang) {
					YAHOO.util.Dom.get('loading_img_'+current_lang).childNodes[0].src='templates/standard/images/complete.png';
					if (next_lang != false) {
						YAHOO.util.Dom.get('loading_img_'+next_lang).style.visibility='visible';
					}
				}

				if (next_lang != false) {
					param ="lang="+next_lang+"&platform="+next_platform;
					YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, param);
				}
				else {
					disableBtnNext(false);
				}
			},
			error: function(o) {
				disableBtnNext(false);
			}
		};

		var sUrl ='import_lang.php';

		YAHOO.util.Dom.get('loading_img_'+language[0]).style.visibility='visible';

		param ="lang="+language[0]+"&platform="+platform[0];
		YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, param);

	}
</script>