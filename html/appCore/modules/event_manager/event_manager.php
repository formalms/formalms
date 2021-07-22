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

/**
 * @package  DoceboCore
 * @version  $Id: event_manager.php 446 2006-06-17 07:23:27Z fabio $
 * @category Event
 * @author   Emanuele Sandri <esandri@docebo.com>
 */

require_once(_base_.'/lib/lib.eventmanager.php' );

function event_manager_view($op) {
	checkPerm('view_event_manager');
	//DoceboEventManager::registerEventConsumer(array('UserNew','UserMod'), 'DoceboUserNotifier', $GLOBALS['where_framework'].'/lib/lib.usernotifier.php');
	
	require_once(_base_.'/lib/lib.table.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('event_manager', 'framework');
	$out  =& $GLOBALS['page'];
	$form = new Form();
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_EVENT_MANAGER'), 'event_manager'));
	$out->add('<div class="std_block">');
	
	if( $op == 'save' ) {
		$arr_permission = 	$_POST['permission'];
		$arr_channel = 		$_POST['channel'];
		foreach( $arr_permission as $key => $permission) {
			$result = sql_query(	"UPDATE ".$GLOBALS['prefix_fw']."_event_manager "
									."   SET permission='".$permission."'"
									.(isset($arr_channel[$key])?
											(", channel='".implode(',',$arr_channel[$key])."'")
											:(", channel=''"))
									." WHERE idEventMgr = '".(int)$key."'" );
			if( $result === FALSE )
				break;
		}
		if( $result )
			$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
		else
			$out->add(getErrorUi($lang->def('_ERROR_IN_SAVE')));
	}
	
	$out->add($form->openForm('event_settings', 'index.php?modname=event_manager&amp;op=save'));
	$out->add($form->openElementSpace());
	
	//$ord = importVar('ord', false, 'trans');
	$ord = Get::req('ord',DOTY_STRING,'trans');
	//$flip = importVar('flip', true, 0);
	$flip = Get::req('flip', DOTY_INT, 0);

	$tb_event_classes = new Table(Get::sett('visuItem'), $lang->def('_EVENT_SETTINGS'), $lang->def('_EVENT_SETTINGS'));

	$content_h 	= array(
		$lang->def('_EVENT_PLATFORM'), 
		$lang->def('_NAME'),
		//$lang->def('_DESCRIPTION'),
		$lang->def('_EVENT_PERM_NOTUSED'),
		$lang->def('_MANDATORY'),
		//$lang->def('_EVENT_PERM_USERSEL'),
		$lang->def('_EMAIL'),
		$lang->def('_EVENT_CHANNEL_SMS'),
		$lang->def('_RECIPIENTS')
		);
	//$type_h 	= array('', '', '', 'image', 'image', 'image', 'image', 'image', '');
	$type_h 	= array('', '', 'image', 'image','image', 'image', '');

	$tb_event_classes->setColsStyle($type_h);
	$tb_event_classes->addHead($content_h);

	$rs = sql_query( "SELECT ec.idClass, class, platform, description, idEventMgr, permission, channel, recipients"
						." FROM %adm_event_class as ec"
						." JOIN %adm_event_manager as em"
						." WHERE ec.idClass = em.idClass"
						." ORDER BY idEventMgr" );

	while( list($idClass,$class,$platform,$description,$idEventMgr,$permission,$channel,$recipients) = sql_fetch_row($rs) ) {
		$cont = array();
		$cont[] = $lang->def('_EVENT_PLATFORM_'.$platform);
		$cont[] = $lang->def('_EVENT_CLASS_'.$class);
		//$cont[] = $lang->def($description);
		$perm_not_used = ($permission == 'not_used')?' checked="checked" ':"";
		$perm_mandatory = ($permission == 'mandatory')?' checked="checked" ':"";
		$perm_user_selectable = ($permission == 'user_selectable')?' checked="checked" ':"";
		$cont[] = '<input type="radio" name="permission['.$idEventMgr.']" value="not_used"'.$perm_not_used.'/>';
		$cont[] = '<input type="radio" name="permission['.$idEventMgr.']" value="mandatory"'.$perm_mandatory.'/>';
		//$cont[] = '<input type="radio" name="permission['.$idEventMgr.']" value="user_selectable"'.$perm_user_selectable.'/>';
		$arr_channel = explode(',',$channel);
		$channel_email = in_array('email',$arr_channel)?' checked="checked" ':"";
		$channel_sms = in_array('sms',$arr_channel)?' checked="checked" ':"";
		$cont[] = '<input type="checkbox" name="channel['.$idEventMgr.'][email]" value="email"'.$channel_email.'/>';
		$cont[] = '<input type="checkbox" name="channel['.$idEventMgr.'][sms]" value="sms"'.$channel_sms.'/>';
		$cont[] = $lang->def($recipients);
		$tb_event_classes->addBody($cont);
	}
	
	$out->add($tb_event_classes->getTable());
	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	$out->add($form->getButton('save', 'save', $lang->def('_SAVE')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());
	$out->add('</div>');
	
}

function event_user_view($op) {
	checkPerm('view');
	require_once(_base_.'/lib/lib.table.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('event_manager', 'framework');
	$out  =& $GLOBALS['page'];
	$form = new Form();
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_EVENT_USER'), 'event_user'));
	$out->add('<div class="std_block">');

	
	if( $op == 'user_save' ) {
		$rs = sql_query( "SELECT idEventMgr"
							." FROM ".$GLOBALS['prefix_fw']."_event_manager"
							." ORDER BY idEventMgr" );

		$arr_channel = $_POST['channel'];
		
		if(isset($_POST['save'])) {
			while( list($idEventMgr) = sql_fetch_row( $rs ) ) {
				$rs_test = sql_query( "SELECT channel"
								."  FROM ".$GLOBALS['prefix_fw']."_event_user "
								." WHERE idEventMgr = '".$idEventMgr."'" 
								."   AND idst = '".Docebo::user()->getIdSt()."'");
				
				$channels = isset($arr_channel[$idEventMgr])?(implode(',',$arr_channel[$idEventMgr])):'';
				if( sql_num_rows( $rs_test ) == 1 )
					$query = "UPDATE ".$GLOBALS['prefix_fw']."_event_user "
							." SET channel='".$channels."'"
							." WHERE idEventMgr = '".(int)$idEventMgr."'" 
							."   AND idst = '".Docebo::user()->getIdSt()."'";
				else
					$query = "INSERT INTO ".$GLOBALS['prefix_fw']."_event_user "
							." (idEventMgr,idst,channel) VALUES"
							." ('".(int)$idEventMgr."','".Docebo::user()->getIdSt()."','".$channels."' )";
				$result = sql_query( $query );
				sql_free_result($rs_test);
				if( $result === FALSE )
					break;
			}
			
			if( $result )
				$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
			else
				$out->add(getErrorUi($lang->def('_ERROR_IN_SAVE')));
				
		}
	}
	
	$out->add($form->openForm('event_settings', 'index.php?modname=userevent&amp;op=user_save'));
	//$out->add($form->openElementSpace());
	
	//$out->add('<script type="text/javascript">window.setTimeout("document.forms[0].submit()",5000);</script>');
	
	$ord = importVar('ord', false, 'trans');
	$flip = importVar('flip', true, 0);

	$tb_event_classes = new Table(Get::sett('visuItem'), $lang->def('_EVENT_SETTINGS'), $lang->def('_EVENT_SETTINGS'));

	$content_h 	= array(
		$lang->def('_EVENT_PLATFORM'), 
		$lang->def('_NAME'),
		$lang->def('_DESCRIPTION'),
		$lang->def('_EMAIL'),
		$lang->def('_EVENT_CHANNEL_SMS'),
		);
	$type_h 	= array('', '', '', 'image', 'image');

	$tb_event_classes->setColsStyle($type_h);
	$tb_event_classes->addHead($content_h);

	$rs = sql_query( "SELECT ec.idClass, class, platform, description, idEventMgr, permission, channel"
						." FROM ".$GLOBALS['prefix_fw']."_event_class as ec"
						." JOIN ".$GLOBALS['prefix_fw']."_event_manager as em"
						." WHERE ec.idClass = em.idClass"
						." ORDER BY idEventMgr" );

	while( list($idClass,$class,$platform,$description,$idEventMgr,$permission,$channel) = sql_fetch_row($rs) ) {
		
		$perm_not_used = ($permission == 'not_used');
		$perm_mandatory = ($permission == 'mandatory');
		$perm_user_selectable = ($permission == 'user_selectable');
		$arr_channel = explode(',',$channel);
		$channel_email = in_array('email',$arr_channel);
		$channel_sms = in_array('sms',$arr_channel);
		
		if( $perm_mandatory || $perm_user_selectable ) {
			$cont = array();
			$cont[] = $lang->def('_EVENT_PLATFORM_'.$platform);
			$cont[] = $lang->def('_EVENT_CLASS_'.$class);
			$cont[] = $lang->def($description);
			
			if( $perm_mandatory ) { 
				$cont[] = '<input type="checkbox" name="Mchannel['.$idEventMgr.'][email]" value="email"'
							.($channel_email?' checked="checked"':'')
							.' disabled="disabled"/>';
				$cont[] = '<input type="checkbox" name="Mchannel['.$idEventMgr.'][sms]" value="sms"'				
							.($channel_sms?' checked="checked"':'')
							.' disabled="disabled"/>';
			} else {
				$query = "SELECT channel "
						." FROM ".$GLOBALS['prefix_fw']."_event_user"
						." WHERE idEventMgr='".$idEventMgr."'"
						."   AND idst='".Docebo::user()->getIdSt()."'";
				$rs_user = sql_query( $query );
				if( sql_num_rows($rs_user) == 1 ) {
					list( $user_channel ) = sql_fetch_row( $rs_user );
					$arr_user_channel = explode( ',', $user_channel );
					$cont[] = '<input type="checkbox" name="channel['.$idEventMgr.'][email]" value="email"'
								.($channel_email?
											(in_array('email',$arr_user_channel)?' checked="checked"':'')
											:' disabled="disabled"')
								.' />';
					$cont[] = '<input type="checkbox" name="channel['.$idEventMgr.'][sms]" value="sms"'
								.($channel_sms?
											(in_array('sms',$arr_user_channel)?' checked="checked"':'')
											:' disabled="disabled"')
								.' />';
				} else {
					$cont[] = '<input type="checkbox" name="channel['.$idEventMgr.'][email]" value="email"'
								.($channel_email?'':' disabled="disabled"')
								.' />';
					$cont[] = '<input type="checkbox" name="channel['.$idEventMgr.'][sms]" value="sms"'
								.($channel_sms?'':' disabled="disabled"')
								.' />';
				}
				sql_free_result($rs_user);
			}
			
			$tb_event_classes->addBody($cont);
		}
	}
	
	$out->add($tb_event_classes->getTable());
	//$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	$out->add($form->getButton('save', 'save', $lang->def('_SAVE')));
	$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());
	$out->add('</div>');
	
}

/**
 * Only for input. Special function.
 **/
function event_special_view($op) {
	checkPerm('view_event_manager');
	
	require_once(_base_.'/lib/lib.table.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('event_manager', 'framework');
	$out  =& $GLOBALS['page'];
	$form = new Form();
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_EVENT_MANAGER'), 'event_manager'));
	$out->add('<div class="std_block">');
	
	if( $op == 'special_save' ) {
		$arr_platform 	 =	$_POST['platform'];
		$arr_class 		 =	$_POST['class'];
		$arr_description = 	$_POST['description'];
		
		$arr_recipients  = 	$_POST['recipients'];
		$arr_show_level  = 	$_POST['show_level'];
		$idClass = 0;
		foreach( $arr_platform as $key => $platform) {
			if( $key == 0 ) {
				if( $platform != '' ) {
					$result1 = sql_query(	"INSERT INTO ".$GLOBALS['prefix_fw']."_event_class "
											." (platform,class,description) VALUES "
											." ('".$platform."','".$arr_class[$key]."','".$arr_description[$key]."') ");
					if( $result1 === FALSE )
						break;
					$idClass = sql_insert_id();
					DoceboEventManager::registerEventConsumer(array($arr_class[$key]), 'DoceboUserNotifier', $GLOBALS['where_framework'].'/lib/lib.usernotifier.php');
					
					$result1 = sql_query(	"INSERT INTO ".$GLOBALS['prefix_fw']."_event_manager "
											." (idClass,recipients,show_level) VALUES "
											." ('".$idClass."','".$arr_recipients[$key]."','".$arr_show_level[$key]."') ");
				}
			} else {
				$result1 = sql_query(	"UPDATE ".$GLOBALS['prefix_fw']."_event_class "
										."   SET platform='".$platform."',"
										."       class='".$arr_class[$key]."',"
										."       description='".$arr_description[$key]."'"
										." WHERE idClass = '".(int)$key."'" );
			}
			if( $result1 === FALSE )
				break;
		}
		foreach( $arr_recipients as $key => $recipients ) {
			if( $key == 0 ) {
				// do nothing
			} else {
				$result2 = sql_query(	"UPDATE ".$GLOBALS['prefix_fw']."_event_manager "
										."   SET recipients='".$recipients."',"
										."       show_level='".$arr_show_level[$key]."'"
										." WHERE idEventMgr = '".(int)$key."'" );				
			}
			if( $result1 === FALSE )
				break;
		}
		
		if( $result1 && $result2 )
			$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
		else
			$out->add(getErrorUi($lang->def('_ERROR_IN_SAVE')));
	}
	
	$out->add($form->openForm('event_special_insert', 'index.php?modname=event_manager&amp;op=special_save'));
	$out->add($form->openElementSpace());
	
	$tb_event_classes = new Table(400, $lang->def('_EVENT_SETTINGS'), $lang->def('_EVENT_SETTINGS'));

	$content_h 	= array(
		$lang->def('_EVENT_PLATFORM'), 
		$lang->def('_NAME'),
		$lang->def('_DESCRIPTION'),
		$lang->def('_RECIPIENTS'),
		'show_level'
		);
	$type_h 	= array('', '', '', 'image', 'image', 'image', 'image', 'image', '');

	$tb_event_classes->setColsStyle($type_h);
	$tb_event_classes->addHead($content_h);

	$rs = sql_query( "SELECT ec.idClass, class, platform, description, idEventMgr, recipients, show_level"
						." FROM ".$GLOBALS['prefix_fw']."_event_class as ec"
						." JOIN ".$GLOBALS['prefix_fw']."_event_manager as em"
						." WHERE ec.idClass = em.idClass"
						." ORDER BY ec.idClass" );

	while( list($idClass,$class,$platform,$description,$idEventMgr,$recipients,$show_level) = sql_fetch_row($rs) ) {
		$cont = array();
		$cont[] = $form->getInputTextfield( 	'', 
											'platform_'.$idClass, 
											'platform['.$idClass.']',
											$platform, 
											'',
											50, 
											'');
		
		$cont[] = $form->getInputTextfield( 	'', 
											'class_'.$idClass, 
											'class['.$idClass.']',
											$class, 
											'',
											50, 
											'');
											
		$cont[] = $form->getInputTextfield( 	'', 
											'description_'.$idClass, 
											'description['.$idClass.']',
											$description, 
											'',
											50, 
											'');
		
		$cont[] = $form->getInputTextfield( 	'', 
											'recipients_'.$idEventMgr, 
											'recipients['.$idEventMgr.']',
											$recipients, 
											'',
											50, 
											'');
		
		$cont[] = $form->getInputTextfield( 	'', 
											'show_level_'.$idEventMgr, 
											'show_level['.$idEventMgr.']',
											$show_level, 
											'',
											50, 
											'');
											
		$tb_event_classes->addBody($cont);
	}
	$cont = array();
	
	$cont[] = $form->getInputTextfield( '', 
										'platform_0', 
										'platform[0]',
										'', 
										'',
										50, 
										'');
		
	$cont[] = $form->getInputTextfield( '', 
										'class_0', 
										'class[0]',
										'', 
										'',
										50, 
										'');
											
	$cont[] = $form->getInputTextfield( '', 
										'description_0', 
										'description[0]',
										'', 
										'',
										50, 
										'');
		
	$cont[] = $form->getInputTextfield( '', 
										'recipients_0', 
										'recipients[0]',
										'', 
										'',
										50, 
										'');
		
	$cont[] = $form->getInputTextfield( '', 
										'show_level_0', 
										'show_level[0]',
										'', 
										'',
										50, 
										'');
											
	$tb_event_classes->addBody($cont);
	
	
	
	$out->add($tb_event_classes->getTable());
	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	$out->add($form->getButton('save', 'save', $lang->def('_SAVE')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());
	$out->add('</div>');
	
}

function eventDispatch($op) {

	switch($op) {
		case "display":
		case "save":
			event_manager_view($op);
		break;
		case "special":
		case "special_save":
			event_special_view($op);
		break;
		case "user_display":
		case "user_save":
			event_user_view($op);
		break;
	}
}

?>