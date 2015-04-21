<h2><?php echo Lang::t('_TITLE_STEP2'); ?></h2>

<?php $php_conf =ini_get_all(); ?>
<?php $cl =checkRequirements(); ?>

<h3><?php echo Lang::t('_SERVERINFO'); ?></h3>
<ul class="info">
	<li><?php echo Lang::t('_SERVER_SOFTWARE'); ?>: <span><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></li>
	<li class="<?php echo $cl['php']; ?>"><?php echo Lang::t('_PHPVERSION'); ?>: <span><?php echo phpversion(); ?></span></li>
	<li class="<?php echo $cl['mysql']; ?>"><?php echo Lang::t('_MYSQLCLIENT_VERSION'); ?>: <span><?php
	preg_match( '/([0-9]+\.[\.0-9]+)/', mysql_get_client_info(), $version );
	echo ( empty($version[1]) ? 'unknow' : $version[1] ); ?></span></li>
	<li class="<?php echo $cl['mbstring']; ?>"><?php echo Lang::t('_MBSTRING'); ?>: <span><?php echo (extension_loaded('mbstring') ? _ON : _OFF); ?></span></li>
	<li class="<?php echo $cl['mime_ct']; ?>"><?php echo Lang::t('_MIME_CONTENT_TYPE'); ?>: <span><?php echo ($cl['mime_ct'] == 'ok' ? _ON : _OFF); ?></span></li>
	<li class="<?php echo $cl['ldap']; ?>"><?php echo Lang::t('_LDAP'); ?>: <span><?php echo (extension_loaded('ldap') ? _ON : _OFF.' '._ONLY_IF_YU_WANT_TO_USE_IT); ?></span></li>
	<li class="<?php echo $cl['openssl']; ?>"><?php echo Lang::t('_OPENSSL'); ?>: <span><?php echo (extension_loaded('openssl') ? _ON : _OFF.' '._WARINNG_SOCIAL); ?></span></li>
	<li class="ok"><?php echo Lang::t('_PHP_TIMEZONE'); ?>: <span><?php echo @date_default_timezone_get(); ?></span></li>
</ul>

<?php if ($cl['php'] == 'err' || $cl['mysql'] == 'err' || $cl['mime_ct'] == 'err' || $cl['mbstring'] == 'err'): ?>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
	});
</script>
<?php endif; ?>

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


<?php echo checkFolderPerm(); ?>

