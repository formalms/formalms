<h2><?php echo Lang::t('_UPGRADE_CONFIG'); ?></h2>

<?php
	$version = Get::req('start_version', DOTY_ALPHANUM, '3603');
	if ( version_compare($version, '3600','>=')  &&
	     version_compare($version, '4000','<') ) {
		echo "<div>UPGRADE  from  version : Docebo CE series  3.6.x version = '" . $GLOBALS['cfg']['versions'][$version] . "</div>";
	} else if ( version_compare($version, '4000','>=')  &&
	            version_compare($version, '5000','<' ) ) {
		echo "<div>UPGRADE  from  version : Docebo CE series  4.x.x version = '" . $GLOBALS['cfg']['versions'][$version] . "'</div>";
	} else  {
		echo "<div>UPGRADE  from  version : forma.lms 1.x.x version = '" . $GLOBALS['cfg']['versions'][$version] . "'</div>";
	}
?>
<div><br/><br/></div>

<div id="upg_config_download" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_NOT_SAVED'); ?></p>
	<a href="upg_config_forma.php?cur_step=3&dwnl=1"><?php echo Lang::t('_DOWNLOAD_CONFIG'); ?></a> |
	<a href="" onclick="javascript: upgradeConfigForma(); return false;"><?php echo Lang::t('_TRY_AGAIN'); ?></a>
	<p>
		<br /><Br />
		<h3><b>ATTENTION</b></h3>
		<br />
		Download config file, save it in the root folder of forma.lms and then click on "<b><?php echo Lang::t('_TRY_AGAIN'); ?></b>"
	</p>
</div>

<div id="upg_config_saved" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_OK'); ?></p>
</div>

<div id="upg_config_nochange" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_NOT_CHANGED'); ?></p>
</div>

<script type="text/javascript">

	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
		upgradeConfigForma();
	});

	function upgradeConfigForma() {
		var callback_db = {
			success: function(o) {
				var arr =YAHOO.lang.JSON.parse(o.responseText);
				if (arr['res'] == 'saved' || arr['res'] == 'ok') {
					disableBtnNext(false);
					YAHOO.util.Dom.get('upg_config_nochange').style.display ='none';
					YAHOO.util.Dom.get('upg_config_download').style.display ='none';
					YAHOO.util.Dom.get('upg_config_saved').style.display ='block';
				} else if (arr['res'] == 'not_saved') {
					YAHOO.util.Dom.get('upg_config_nochange').style.display ='none';
					YAHOO.util.Dom.get('upg_config_download').style.display ='block';
					YAHOO.util.Dom.get('upg_config_saved').style.display ='none';
				} else if (arr['res'] == 'not_change') {
					disableBtnNext(false);
					YAHOO.util.Dom.get('upg_config_nochange').style.display ='block';
					YAHOO.util.Dom.get('upg_config_download').style.display ='none';
					YAHOO.util.Dom.get('upg_config_saved').style.display ='none';
				}
			}
		};

		var sUrl ='upg_config_forma.php?cur_step=3';
		YAHOO.util.Connect.asyncRequest('GET', sUrl, callback_db);
	}
</script>
