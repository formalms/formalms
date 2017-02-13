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

if(!Docebo::user()->isAnonymous()) {

function maskMultiple($name, $value) {

	require_once(_base_.'/lib/lib.form.php');
	$lang 	=& DoceboLanguage::createInstance('admin_config', 'scs');
	
	return Form::getOpenCombo($lang->def('_'.strtoupper($name)))
	
			.Form::getInputRadio('rules_'.$name.'_admin', 'rules['.$name.']', 'admin', ($value == 'admin'), '').'&nbsp;'
			.Form::getLabel( 'rules_'.$name.'_admin', $lang->def('_ADMIN'), 'label_padded' ).'&nbsp;'
			
			.Form::getInputRadio('rules_'.$name.'_alluser', 'rules['.$name.']', 'alluser', ($value == 'alluser'), '').'&nbsp;'
			.Form::getLabel('rules_'.$name.'_alluser', $lang->def('_ALLUSER'), 'label_padded' ).'&nbsp;'
			
			.Form::getInputRadio('rules_'.$name.'_noone', 'rules['.$name.']', 'noone', ($value == 'noone'), '').'&nbsp;'
			.Form::getLabel( 'rules_'.$name.'_noone', $lang->def('_NOONE'), 'label_padded' ).'&nbsp;'
			
			.Form::getCloseCombo();
}

function adminConf() {
	
	require_once(_base_.'/lib/lib.form.php');
	
	$lang 	=& DoceboLanguage::createInstance('admin_config', 'scs');
	$out	=&$GLOBALS['page'];
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_ADMIN_CONF'), 'admin_conf')
			.'<div class="std_block">');
	
	if(isset($_POST['save'])) {
		
		$query_update = "UPDATE ".$GLOBALS['prefix_scs']."_rules_admin SET ";
		if(isset($_POST['rules'])) {
			while(list($var_name, $new_value) = each($_POST['rules'])) {
				
				$query_update .= " $var_name = '".$new_value."',";
			}
			
			$re = sql_query(substr($query_update, 0 , -1));
		}
		if($re) $out->add(getResultUi($lang->def('_MOD_OK')));
		else $out->add(getErrorUi($lang->def('_MOD_ERR')));
	}
	
	$query_rules_admin = "
	SELECT server_status, 
		enable_recording_function, enable_advice_insert, enable_write, enable_chat_recording, 
		enable_private_subroom, enable_public_subroom, 
		enable_drawboard_watch, enable_drawboard_write, 
		enable_audio, enable_webcam, enable_stream_watch, enable_strem_write, enable_remote_desktop 
	FROM ".$GLOBALS['prefix_scs']."_rules_admin";
	$re_rules_admin = sql_query($query_rules_admin);
	$rules = sql_fetch_array($re_rules_admin);
	
	$out->add(
		Form::openForm('rules_admin', 'index.php?modname=admin_configuration&amp;op=conf')
		.Form::openElementSpace()
		
		.Form::getOpenCombo($lang->def('_SERVER_STATUS'))
			.Form::getInputRadio('rules_server_status_yes', 'rules[server_status]', 'yes', 
				($rules['server_status'] == 'yes'), '').'&nbsp;'
			.Form::getLabel( '', $lang->def('_YES'), 'label_padded' ).'&nbsp;'
			.Form::getInputRadio('rules_server_status_no', 'rules[server_status]', 'no', 
				($rules['server_status'] == 'no'), '').'&nbsp;'
			.Form::getLabel( '', $lang->def('_NO'), 'label_padded' ).'&nbsp;'
			.Form::getCloseCombo()
		
		.maskMultiple('enable_recording_function', $rules['enable_recording_function'])
		.maskMultiple('enable_advice_insert', $rules['enable_advice_insert'])
		.maskMultiple('enable_write', $rules['enable_write'])
		.maskMultiple('enable_chat_recording', $rules['enable_chat_recording'])
		.maskMultiple('enable_private_subroom', $rules['enable_private_subroom'])
		.maskMultiple('enable_public_subroom', $rules['enable_public_subroom'])
		.maskMultiple('enable_drawboard_watch', $rules['enable_drawboard_watch'])
		.maskMultiple('enable_drawboard_write', $rules['enable_drawboard_write'])
		.maskMultiple('enable_audio', $rules['enable_audio'])
		.maskMultiple('enable_webcam', $rules['enable_webcam'])
		.maskMultiple('enable_stream_watch', $rules['enable_stream_watch'])
		.maskMultiple('enable_strem_write', $rules['enable_strem_write'])
		.maskMultiple('enable_remote_desktop', $rules['enable_remote_desktop'])
		
		
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	);
	
	$out->add('</div>');
}

function adminConfDispatch($op) {
	
	switch($op) {
		case "conf" : {
			adminConf();
		};break;
	}
}

}

?>