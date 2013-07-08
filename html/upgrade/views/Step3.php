<h2><?php echo Lang::t('_UPGRADE_CONFIG'); ?></h2>

<div id="upg_config_download" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_NOT_SAVED'); ?></p>
	<a href="../install/download_conf.php"><?php echo Lang::t('_DOWNLOAD_CONFIG'); ?></a> |
	<a href="" onclick="javascript: upgradeConfig(); return false;"><?php echo Lang::t('_TRY_AGAIN'); ?></a>
</div>

<div id="upg_config_ok" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_OK'); ?></p>
</div>

<script type="text/javascript">

	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
		upgradeConfig();
	});

	function upgradeConfig() {
		var callback_db = {
			success: function(o) {
				var arr =YAHOO.lang.JSON.parse(o.responseText);
				if (arr['res'] == 'ok') {
					disableBtnNext(false);
					YAHOO.util.Dom.get('upg_config_download').style.display ='none';
					YAHOO.util.Dom.get('upg_config_ok').style.display ='block';
				} else if (arr['res'] == 'not_saved') {
					YAHOO.util.Dom.get('upg_config_download').style.display ='block';
					YAHOO.util.Dom.get('upg_config_ok').style.display ='none';
				}
			}
		};

		var sUrl ='upg_config.php';
		YAHOO.util.Connect.asyncRequest('GET', sUrl, callback_db);
	}
</script>