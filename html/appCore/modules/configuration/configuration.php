<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

define("SMS_GROUP", 11);

function config() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.tab.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_adm_.'/class/class.conf.php');

	$lang 	=& DoceboLanguage::createInstance('configuration', 'framework');
	$active_tab = importVar('active_tab', false, 1);

	//instance class-------------------------------------------
	$conf = new Config_Framework();
	$groups = $conf->getRegroupUnit();

	cout(getTitleArea($lang->def('_CONFIGURATION'))
		.'<div class="std_block">'
	);
	//save page if require
	if(isset($_POST['save_config'])) {
		if($conf->saveElement($active_tab)) {
			cout(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
		} else {
			cout(getErrorUi($lang->def('_ERROR_IN_SAVE')));
		}
	}

	cout('<div id="global_conf" class="yui-navset">'
		.'<ul class="yui-nav">'
	);
	while(list($id, $name) = each($groups)) {
		// print the tab list
		cout('<li'.($id == $active_tab?' class="selected"':'').'><a href="#tab_g_'.$id.'"><em>'.$name['name'].'</em></a></li>');
	}
	reset($groups);
	cout('</ul>'
		.'<div class="yui-content">');
	while(list($id, $name) = each($groups)) {

		// print the tab content
		cout('<div id="tab_g_'.$id.'">'
			.'<h2>'.$name['name'].'</h2>'
			.'<p style="padding:4px">'.$name['descr'].'</p>'

			.Form::openForm('conf_option_'.$id, 'index.php?modname=configuration&amp;op=config')
			.Form::openElementSpace()
			.Form::getHidden('active_tab_'.$id, 'active_tab', $id)
		);
		switch($id) {
			case SMS_GROUP : {
				cout(show_sms_panel($lang).'<br />');
			};break;
			default:{
				cout('<br />');
			}
		}
		cout(''
			.$conf->getPageWithElement($id)

			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('save_config_'.$id, 'save_config', $lang->def('_SAVE'))
			.Form::getButton('undo_'.$id, 'undo', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::CloseForm()
			.'<br />'
			.'</div>');
	}
	cout('<script type="text/javascript">
		var targets =  YAHOO.util.Selector.query("span[id^=tt_target]");
		new YAHOO.widget.Tooltip("tooltip_info",
			{ context:targets,
			effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.20}
		 });
		</script>',
	'scripts');

	reset($groups);
	cout('</div>'
		.'<div style="clear:left">&nbsp;</div>'
		.'</div>'
		.'</div>');

	cout('<script type="text/javascript">'
		."	new YAHOO.widget.TabView('global_conf', {orientation:'left'});"
		.'</script>'
	, 'scripts');
}

function config_line($param_name, $param_value) {

	return '<div class="nofloat"><div class="label_effect">'
		.$param_name.'</div>'
		.$param_value
		.'</div>';
}

function server_info() {

	$lang =& DoceboLanguage::createInstance('configuration', 'framework');

	$php_conf = ini_get_all();

	$intest = '<div>'
			.'<div class="label_effect">';

	$html = '<div class="conf_line_title">'.$lang->def('_SERVERINFO').'</div>'
		.config_line($lang->def('_SERVER_ADDR'), $_SERVER['SERVER_ADDR'] )
		.config_line($lang->def('_SERVER_PORT'), $_SERVER['SERVER_PORT'] )
		.config_line($lang->def('_SERVER_NAME'), $_SERVER['SERVER_NAME'] )
		.config_line($lang->def('_SERVER_ADMIN'), $_SERVER['SERVER_ADMIN'] )
		.config_line($lang->def('_SERVER_SOFTWARE'), $_SERVER['SERVER_SOFTWARE'] )
		.'<br />'

		.'<div class="conf_line_title">'.$lang->def('_SERVER_MYSQL').'</div>'
		.config_line($lang->def('_sql_VERS'), sql_get_server_info())
		.'<br />'

		.'<div class="conf_line_title">'.$lang->def('_PHPINFO').'</div>'
		.config_line($lang->def('_PHPVERSION'), phpversion())
		.config_line($lang->def('_SAFEMODE'), ( $php_conf['safe_mode']['local_value']
			? $lang->def('_ON')
			: $lang->def('_OFF') ))
		.config_line($lang->def('_REGISTER_GLOBAL'), ( $php_conf['register_globals']['local_value']
			? $lang->def('_ON')
			: $lang->def('_OFF') ))
		.config_line($lang->def('_MAGIC_QUOTES_GPC'), ( $php_conf['magic_quotes_gpc']['local_value']
			? $lang->def('_ON')
			: $lang->def('_OFF') ))
		.config_line($lang->def('_UPLOAD_MAX_FILESIZE'), $php_conf['upload_max_filesize']['local_value'])
		.config_line($lang->def('_POST_MAX_SIZE'), $php_conf['post_max_size']['local_value'])
		.config_line($lang->def('_MAX_EXECUTION_TIME'), $php_conf['max_execution_time']['local_value'].'s' )
		.config_line($lang->def('_LDAP'), ( extension_loaded('ldap')
			? $lang->def('_ON')
			: '<span class="font_red">'.$lang->def('_OFF').' '.$lang->def('_USEFULL_ONLY_IF').'</span>') )
		.config_line($lang->def('_PHP_TIMEZONE'), @date_default_timezone_get() );

	if(version_compare(phpversion(), "5.0.0") == -1) {

		$html .= config_line($lang->def('_DOMXML'), ( extension_loaded('domxml')
				? $lang->def('_ON')
				: '<span class="font_red">'.$lang->def('_OFF').' ('.$lang->def('_NOTSCORM').')</span>' ));
	}
	if (version_compare(phpversion(), "5.2.0", ">"))
	{
		$html .= config_line($lang->def('_ALLOW_URL_INCLUDE'), ( $php_conf['allow_url_include']['local_value']
			? '<span class="font_red">'.$lang->def('_ON').'</span>'
			: $lang->def('_OFF') ));
	}
	if(Get::cfg('uploadType') == 'ftp') {

		if(function_exists("ftp_connect")) {

			require_once( _base_.'/lib/lib.upload.php' );
			$re_con = sl_open_fileoperations();
			$html .= config_line($lang->def('_UPLOADFTP'), ( $re_con
				? $lang->def('_FTPOK')
				: '<span class="font_red">'.$lang->def('_FTPERR').'</span>') );
			if($re_con) sl_close_fileoperations();
		} else {

			$html .= config_line($lang->def('_UPLOADFTP'), '<span class="font_red">'.$lang->def('_FTPERR').'</span>' );
		}
	}
	$html .= '<div class="nofloat"></div><br />';
	return $html;
}

function show_sms_panel(&$lang) {

	$sms_credit = Get::sett('sms_credit', 0);
	if($sms_credit == 0) {
		$credit_left="0";
		$note="(".$lang->def("_SMS_CREDIT_UPDATE").")";
	} else {
		$credit_left=number_format($sms_credit/1000, 2, ",", "")." &euro;";
		$note="";
	}
	cout('<div class="container-smsmarket">'
		.'<p>'
		.'<a href=""http://www.smsmarket.it/" onclick="window.open(this.href); return false;" title="'.$lang->def("_SMSMARKET_LOGO").'">'
		.'<img src="".getPathImage()."config/smsmarket.gif" alt="'.$lang->def("_SMSMARKET_LOGO").'" title="'.$lang->def("_SMSMARKET_LOGO").'" /></a>'
		.'</p>'
		.'<p><b>'.$lang->def("_SMS_CREDIT").': '.$credit_left.'</b> '.$note.'</p>'
		.'<p><a href="http://www.smsmarket.it/acquista_sms.php" onclick="window.open(this.href); return false;">'.$lang->def("_SMS_BUY_RECHARGE").'</a></p>'
		.'</div>');
}



// XXX: switch
function configurationDispatch($op) {
switch($op) {
	case "config" : {
		config();
	};break;
}
}

?>