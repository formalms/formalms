<h2><?php echo Lang::t('_TITLE_STEP2'); ?></h2>

<?php $php_conf = ini_get_all(); ?>
<?php $cl = $this->checkRequirements(); ?>

<script type="text/javascript">
	var other_error =<?php echo ($cl['php'] == 'err' || $cl['strict_mode'] == 'err' || $cl['mbstring'] == 'err' || $cl['mime_ct'] == 'err' ? 'true' : 'false'); ?>;
	var config_ok ={
		'v3': <?php echo ($cl['config_v3'] == 'err' ? 'false' : 'true'); ?>,
		'v4': <?php echo ($cl['config_v4'] == 'err' ? 'false' : 'true'); ?>,
		'v1': <?php echo ($cl['config_v1'] == 'err' ? 'false' : 'true'); ?>
	}
	YAHOO.util.Event.onDOMReady(function() {
		YAHOO.util.Event.addListener("start_version", "change", startVersionChange);
	});

	function startVersionChange(e) {
		var version =e.target.value;
		var show_err =false;

		if (version > 3000 && version < 4000 && !config_ok.v3) {
			// docebo ce versions series 3.x.x.x
			show_err =true;
		}
		else if (version >= 4000 && version < 5000 && !config_ok.v4) {
			// docebo ce versions series 4.x.x.x
			show_err =true;
		}
		else if (version >= 10000 && !config_ok.v1) {
			// forma versions serie 1.x
			show_err =true;
		}

		var err_box =YAHOO.util.Dom.get('config_err_box');
		if (!other_error) {
			disableBtnNext(show_err);
		}
		YAHOO.util.Dom.setStyle(err_box, 'display', (show_err ? 'block' : 'none'));
	}
</script>

<h3><?php echo Lang::t('_VERSION'); ?></h3>
<p class="microform">
	<b><label for="start_version"><?php echo Lang::t('_START'); ?> : </label></b><?php echo $this->versionList(); ?><br />
	<b><?php echo Lang::t('_END'); ?> : </b><?php echo $GLOBALS['cfg']['versions'][$GLOBALS['cfg']['endversion']]; ?>
</p>

<?php if ( ($cl['config_v3'] == 'err' && $cl['config_v4'] == 'err' && $cl['config_v1'] == 'err') ||
           $cl['php'] == 'err' || $cl['strict_mode'] == 'err' || $cl['mbstring'] == 'err'): ?>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
	});
</script>
<?php endif; ?>
<?php if ($cl['config_v3'] == 'err' && $cl['config_v4'] == 'err' && $cl['config_v1'] == 'err') {
	$config_err_style ='style="display: block;"';
}
else {
	$config_err_style ='style="display: none;"';
}
?>

<?php if (!empty($cl['upg_not_needed'])): ?>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		YAHOO.util.Dom.get('start_version').disabled =true;
		disableBtnNext(true);
	});
</script>
<ul id="upg_not_needed_err_box" class="info">
	<li class="err"><span><?php echo Lang::t('_UPGRADE_NOT_NEEDED'); ?></span></li>
</ul>
<?php endif; ?>

<ul id="config_err_box" class="info" <?php echo $config_err_style; ?>>
	<li class="err"><span><?php echo Lang::t('_INVALID_CONFIG_FILE'); ?></span></li>
</ul>

<br/>
<h3><?php echo Lang::t('_SERVERINFO'); ?></h3>
<ul class="info">
	<li><?php echo Lang::t('_SERVER_SOFTWARE'); ?>: <span><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></li>
	<li class="<?php echo $cl['php']; ?>"><?php echo Lang::t('_PHPVERSION'); ?>: <span><?php echo phpversion(); ?></span></li>
	<li class="<?php echo $cl['mysql']; ?>"><?php echo Lang::t('_MYSQLCLIENT_VERSION'); ?>: <span><?php
	preg_match( '/([0-9]+\.[\.0-9]+)/', mysql_get_client_info(), $version );
	echo ( empty($version[1]) ? 'unknow' : $version[1] ); ?></span></li>
	<li class="<?php echo $cl['strict_mode']; ?>"><?php echo Lang::t('_SQL_STRICT_MODE'); ?>: <span><?php echo ($cl['strict_mode'] == 'ok' ? _OFF : _ON); ?></span></li>
	<li class="<?php echo $cl['mbstring']; ?>"><?php echo Lang::t('_MBSTRING'); ?>: <span><?php echo (extension_loaded('mbstring') ? _ON : _OFF); ?></span></li>
	<li class="<?php echo $cl['mime_ct']; ?>"><?php echo Lang::t('_MIME_CONTENT_TYPE'); ?>: <span><?php echo ($cl['mime_ct'] == 'ok' ? _ON : _OFF); ?></span></li>
	<li class="<?php echo $cl['ldap']; ?>"><?php echo Lang::t('_LDAP'); ?>: <span><?php echo (extension_loaded('ldap') ? _ON : _OFF.' '._ONLY_IF_YU_WANT_TO_USE_IT); ?></span></li>
	<li class="<?php echo $cl['openssl']; ?>"><?php echo Lang::t('_OPENSSL'); ?>: <span><?php echo (extension_loaded('openssl') ? _ON : _OFF.' '._WARINNG_SOCIAL); ?></span></li>
	<li class="ok"><?php echo Lang::t('_PHP_TIMEZONE'); ?>: <span><?php echo @date_default_timezone_get(); ?></span></li>
</ul>


<h3><?php echo Lang::t('_PHPINFO'); ?></h3>
<ul class="info">
	<li><?php echo Lang::t('_MAGIC_QUOTES_GPC'); ?>: <?php echo ($php_conf['magic_quotes_gpc']['local_value'] != '' ? _ON : _OFF); ?></li>
	<li><?php echo Lang::t('_SAFEMODE'); ?>: <?php echo ($php_conf['safe_mode']['local_value'] != '' ? _ON : _OFF); ?></li>
	<li><?php echo Lang::t('_REGISTER_GLOBALS'); ?>: <?php echo ($php_conf['register_globals']['local_value'] != '' ? _ON : _OFF); ?></li>
	<li class="<?php echo $cl['allow_url_fopen']; ?>"><?php echo Lang::t('_ALLOW_URL_FOPEN'); ?>: <?php echo ( $php_conf['allow_url_fopen']['local_value'] != '' ? _ON : _OFF.' '._WARINNG_SOCIAL); ?></li>
	<li><?php echo Lang::t('_ALLOW_URL_INCLUDE'); ?>: <?php echo ( $php_conf['allow_url_include']['local_value'] != '' ? _ON : _OFF); ?></li>
	<li><?php echo Lang::t('_UPLOAD_MAX_FILESIZE'); ?>: <?php echo $php_conf['upload_max_filesize']['local_value']; ?></li>
	<li><?php echo Lang::t('_POST_MAX_SIZE'); ?>: <?php echo $php_conf['post_max_size']['local_value']; ?></li>
	<li><?php echo Lang::t('_MAX_EXECUTION_TIME'); ?>: <?php echo $php_conf['max_execution_time']['local_value'].'s'; ?></li>
</ul>

<?php echo $this->checkFolderPerm(); ?>