<?php

	$html = "";
	$html .= '<div id="tech_info_dialog_content">';

	$html .= '<h3>'.Lang::t('_SERVERINFO', 'configuration').'</h3><ul class="link_list">';

	$html .= '<li>'.Lang::t('_SERVER_ADDR', 'configuration').':&nbsp;<b>'.$_SERVER['SERVER_ADDR'].'</b>';
	$html .= '<li>'.Lang::t('_SERVER_PORT', 'configuration').':&nbsp;<b>'.$_SERVER['SERVER_PORT'].'</b>';
	$html .= '<li>'.Lang::t('_SERVER_NAME', 'configuration').':&nbsp;<b>'.$_SERVER['SERVER_NAME'].'</b>';
	$html .= '<li>'.Lang::t('_SERVER_ADMIN', 'configuration').':&nbsp;<b>'.$_SERVER['SERVER_ADMIN'].'</b>';
	$html .= '<li>'.Lang::t('_SERVER_SOFTWARE', 'configuration').':&nbsp;<b>'.$_SERVER['SERVER_SOFTWARE'].'</b>';

	$html .= '</ul><br />';

	$html .= '<h3>'.Lang::t('_SERVER_MYSQL', 'configuration').'</h3><ul class="link_list">';

	$html .= '<li>'.Lang::t('_MYSQL_VERS', 'configuration').':&nbsp;<b>'.$sql_server_info.'</b>';
	$html .= '<li>'.Lang::t('_MYSQL_MODE', 'configuration').':&nbsp;'.($sql_additional_info['sql_mode'] ? '<b>'.$sql_additional_info['sql_mode'].'</b>' : '<i>""</i>');
	$html .= '<li>'.Lang::t('_MYSQL_ENCODING', 'configuration').':&nbsp;<b>'.$sql_additional_info['character_info']['character_set_connection'].'</b>';
	$html .= '<li>'.Lang::t('_MYSQL_COLLATION', 'configuration').':&nbsp;<b>'.$sql_additional_info['collation_info']['collation_connection'].'</b>';
	$html .= '<li>'.Lang::t('_MYSQL_TIMEZONE', 'configuration').':&nbsp;<b>'.$sql_additional_info['sql_timezone'].'</b>';

	$html .= '</ul><br />';

	$html .= '<h3>'.Lang::t('_PHPINFO', 'configuration').'</h3><ul class="link_list">';

	$html .= '<li>'.Lang::t('_PHPVERSION', 'configuration').':&nbsp;<b>'.phpversion().'</b>';
	$html .= '<li>'.Lang::t('_PHP_TIMEZONE', 'configuration').':&nbsp;<b>'.@date_default_timezone_get().'</b>';
	$html .= '<li>'.Lang::t('_SAFEMODE', 'configuration').':&nbsp;<b>'.( $php_conf['safe_mode']['local_value']
			? Lang::t('_ON', 'standard')
			: '<span class="red">'.Lang::t('_OFF', 'standard') ).'</span></b>';
	$html .= '<li>'.Lang::t('_REGISTER_GLOBAL', 'configuration').':&nbsp;<b>'.( $php_conf['register_globals']['local_value']
			? '<span class="red">'.Lang::t('_ON', 'standard').'</span>'
			: Lang::t('_OFF', 'standard') ).'</b>';
	$html .= '<li>'.Lang::t('_MAGIC_QUOTES_GPC', 'configuration').':&nbsp;<b>'.( $php_conf['magic_quotes_gpc']['local_value']
			? Lang::t('_ON', 'standard')
			: Lang::t('_OFF', 'standard') ).'</b>';
	$html .= '<li>'.Lang::t('_UPLOAD_MAX_FILESIZE', 'configuration').':&nbsp;<b>'.$php_conf['upload_max_filesize']['local_value'].'</b>';
	$html .= '<li>'.Lang::t('_POST_MAX_SIZE', 'configuration').':&nbsp;<b>'.$php_conf['post_max_size']['local_value'].'</b>';
	$html .= '<li>'.Lang::t('_MAX_EXECUTION_TIME', 'configuration').':&nbsp;<b>'.$php_conf['max_execution_time']['local_value'].'s'.'</b>';
	$html .= '<li>'.Lang::t('_LDAP', 'configuration').':&nbsp;<b>'.( extension_loaded('ldap')
			? Lang::t('_ON', 'standard')
			: '<span class="red">'.Lang::t('_OFF', 'standard').' '.Lang::t('_USEFULL_ONLY_IF', 'configuration').'</span>').'</b>';


	if(version_compare(phpversion(), "5.0.0") == -1) {
		$html .= '<li>'.Lang::t('_DOMXML', 'configuration').':&nbsp;<b>'.(extension_loaded('domxml')
				? Lang::t('_ON', 'standard')
				: '<span class="red">'.Lang::t('_OFF').' ('.Lang::t('_NOTSCORM', 'configuration').')</span>').'</b>';
	}

	if (version_compare(phpversion(), "5.2.0", ">")) {
		$html .= '<li>'.Lang::t('_ALLOW_URL_INCLUDE', 'configuration').':&nbsp;<b>'.($php_conf['allow_url_include']['local_value']
			? '<span class="red">'.Lang::t('_ON').'</span>'
			: Lang::t('_OFF')).'</b>';;
	}

	if (Get::sett('uploadType', '') == 'ftp') {
		if (function_exists("ftp_connect")) {
			require_once( $GLOBALS['where_framework'].'/lib/lib.upload.php' );
			$re_con = sl_open_fileoperations();
			$html .= '<li>'.Lang::t('_UPLOADFTP', 'configuration').':&nbsp;<b>'.($re_con
				? Lang::t('_FTPOK', 'configuration')
				: '<span class="red">'.Lang::t('_FTPERR', 'configuration').'</span>').'<b>';
			if($re_con) sl_close_fileoperations();
		} else {
			$html .= '<li>'.Lang::t('_UPLOADFTP', 'configuration').':&nbsp;<b><span class="red">'.Lang::t('_FTPERR').'</span></b>';
		}
	}

	$html .= '<li>'.Lang::t('_OPENSSL', 'configuration').':&nbsp;<b>'.( extension_loaded('openssl')
			? Lang::t('_ON', 'standard')
			: '<span class="red">'.Lang::t('_OFF', 'standard').' '.Lang::t('_WARINNG_SOCIAL', 'configuration').'</span>').'</b>';
	
	$html .= '<li>'.Lang::t('_ALLOW_URL_FOPEN', 'configuration').':&nbsp;<b>'.($php_conf['allow_url_fopen']['local_value']
		? Lang::t('_ON')
		: '<span class="red">'.Lang::t('_OFF', 'standard').' '.Lang::t('_WARINNG_SOCIAL', 'configuration').'</span>').'</b>';;

$html .= '</ul>';
$html .= '</div>';

if (isset($json)) {
	$params = array(
		'success' => true,
		'header' => $title,
		'body' => $html
	);
	echo $json->encode($params);
} else {
	echo getTitleArea($title);
	echo '<div class="std_block">'.$html.'</div>';
}

?>