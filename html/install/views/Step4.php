<h2><?php echo Lang::t('_TITLE_STEP4'); ?></h2>
<?php $php_conf =ini_get_all(); ?>

<script type="text/javascript">

	var upload_method='http';

	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);

		var check_fields =['site_url', 'db_host', 'db_name', 'db_user', 'db_pass', 'upload_method', 'ftp_host', 'ftp_port', 'ftp_user', 'ftp_pass'];

		YAHOO.util.Event.addListener("my_button", "mouseenter", function(e) {
			validateInput(check_fields, 'final_check');
		});


		YAHOO.util.Event.addListener(['site_url', 'db_user', 'db_pass', 'db_name', 'ftp_pass'], "blur", function(e) {
			validateInput(check_fields);
		});


		YAHOO.util.Event.addListener("http_upload", "click", function(e) {
			upload_method ='http';
			YAHOO.util.Dom.get('upload_method').value =upload_method;
			YAHOO.util.Dom.get('ftp_data').style.visibility ='hidden';
			validateInput(check_fields);
		});


		YAHOO.util.Event.addListener("ftp_upload", "click", function(e) {
			upload_method ='ftp';
			YAHOO.util.Dom.get('upload_method').value =upload_method;
			YAHOO.util.Dom.get('ftp_data').style.visibility ='visible';
		});

	});
</script>


<div style="margin-bottom: 1.8em; margin-top: 1.8em;">
<?php
		$https=(isset($_SERVER["HTTPS"]) ? $_SERVER["HTTPS"] : FALSE);
		$base_url=($https ? "https://" : "http://").$_SERVER["HTTP_HOST"].dirname($_SERVER['PHP_SELF'])."/";
		$base_url=preg_replace("/install\\/$/", "", $base_url);
?>
<?php echo Form::getTextfield(Lang::t('_SITE_BASE_URL'), "site_url", "site_url", 255, $base_url); ?>
</div>

<h3><?php echo Lang::t('_DATABASE_INFO'); ?></h3>

<?php echo Form::getTextfield(Lang::t('_DB_HOST'), "db_host", "db_info[db_host]", 255, "localhost"); ?>
<?php echo Form::getTextfield(Lang::t('_DB_NAME'), "db_name", "db_info[db_name]", 255); ?>
<?php echo Form::getTextfield(Lang::t('_DB_USERNAME'), "db_user", "db_info[db_user]", 255); ?>
<?php echo Form::getPassword(Lang::t('_DB_PASS'), "db_pass", "db_info[db_pass]", 255); ?>
	
<h3><?php echo Lang::t('_UPLOAD_METHOD'); ?></h3>


<?php echo Form::getRadio(Lang::t('_HTTP_UPLOAD'), "http_upload", "ul_info[upload_method]", "http", $php_conf['safe_mode']['local_value'] == ''); ?>

<?php echo Form::getRadio(Lang::t('_FTP_UPLOAD'), "ftp_upload", "ul_info[upload_method]", "ftp", $php_conf['safe_mode']['local_value'] != ''); ?>

<div id="ftp_data" style="visibility: hidden; margin-top: 1.6em;">
<?php echo Form::getTextfield(Lang::t('_FTP_HOST'), "ftp_host", "ul_info[ftp_host]", 255, "localhost"); ?>
<?php echo Form::getTextfield(Lang::t('_FTP_PORT'), "ftp_port", "ul_info[ftp_port]", 255, "21"); ?>
<?php echo Form::getTextfield(Lang::t('_FTP_USERNAME'), "ftp_user", "ul_info[ftp_user]", 255); ?>
<?php echo Form::getPassword(Lang::t('_FTP_PASS'), "ftp_pass", "ul_info[ftp_pass]", 255); ?>
<?php echo Form::getTextfield(Lang::t('_FTP_PATH'), "ftp_path", "ul_info[ftp_path]", 255, "/"); ?>
</div>

<?php echo Form::getHidden("upload_method", "upload_method", "http"); ?>