<h2><?php echo Lang::t('_UPGRADE_CONFIG'); ?></h2>

<?php
	$version = Get::req('start_version', DOTY_ALPHANUM, '3603');
	if ( version_compare($version, '3600','>=')  &&
	     version_compare($version, '4000','<') ) {
?>

<!-- <div>UPGRADE  from  version : Docebo series  3.6.x version = <?php echo $version ;?></div> -->
<div id="upg_config_download" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_NOT_SAVED'); ?></p>
	<a href="../install/download_conf.php"><?php echo Lang::t('_DOWNLOAD_CONFIG'); ?></a> |
	<a href="" onclick="javascript: upgradeConfig(); return false;"><?php echo Lang::t('_TRY_AGAIN'); ?></a>
	<p>
		<br /><Br />
		<h3><b>ATTENTION</b></h3>
		<br />
		Download config file, save it in the root folder of Forma and then click on "<b><?php echo Lang::t('_TRY_AGAIN'); ?></b>"
	</p>
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

<?php
	} else if ( version_compare($version, '4000','>=')  &&
	            version_compare($version, '5000','<' ) ) {
?>
<!-- <div>UPGRADE  from  version : Docebo series  4.x.x version = <?php echo $version ;?></div> -->
<div id="upg_config_download" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_NOT_SAVED'); ?></p>
	<a href="upg_config_d400.php?dwnl=1"><?php echo Lang::t('_DOWNLOAD_CONFIG'); ?></a> |
	<a href="" onclick="javascript: upgradeConfig400(); return false;"><?php echo Lang::t('_TRY_AGAIN'); ?></a>
	<p>
		<br /><Br />
		<h3><b>ATTENTION</b></h3>
		<br />
		Download config file, save it in the root folder of Forma and then click on "<b><?php echo Lang::t('_TRY_AGAIN'); ?></b>"
	</p>
</div>

<div id="upg_config_ok" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_OK'); ?></p>
</div>

<script type="text/javascript">

	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
		upgradeConfig400();
	});

	function upgradeConfig400() {
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

		var sUrl ='upg_config_d400.php';
		YAHOO.util.Connect.asyncRequest('GET', sUrl, callback_db);
	}
</script>

<?php
	} else {
		// version_compare($version, '10000','>=')
		// FORMA Version: 1.x
?>
<!-- <div>UPGRADE  from  version : FORMALMS series  1.x.x version = <?php echo $version ;?></div> -->
<div id="upg_config_download" style="display: none;">
	<p><?php echo Lang::t('_UPG_CONFIG_OK'); ?></p>
</div>

<div id="upg_config_ok" style="display: block;">
	<p><?php echo Lang::t('_UPG_CONFIG_OK'); ?></p>
</div>

<script type="text/javascript">

	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(false);
	});

</script>

<?php
	}
?>
