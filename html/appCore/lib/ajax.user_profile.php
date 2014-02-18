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
 * @package admin-core
 * @subpackage user
 * @category ajax server
 * @version $Id:$
 */

// here all the specific code ==========================================================

$op = Get::req('op',DOTY_ALPHANUM, '');

switch($op) {
	
	case "get_lang" : {
		
		$module_name 	= Get::req('module_name',DOTY_ALPHANUM, '');
		$platform 		= Get::req('platform', DOTY_ALPHANUM, '');
		
		$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
		$lang->setGlobal();
		$lang =& DoceboLanguage::createInstance( $module_name, $platform);
		
		$value = array(
//			'_TITLE_ASK_A_FRIEND' 	=> $lang->def('_TITLE_ASK_A_FRIEND'),
//			'_WRITE_ASK_A_FRIEND' 	=> $lang->def('_WRITE_ASK_A_FRIEND'),
			'_SEND' 		=> $lang->def('_SEND'), 
			'_UNDO' 				=> $lang->def('_UNDO'),
//			'_ASK_FRIEND_SEND' 		=> $lang->def('_SEND'),
//			'_ASK_FRIEND_FAIL' 		=> $lang->def('failed'),
			
			'_SUBJECT' 		=> $lang->def('_SUBJECT'),
			'_MESSAGE_TEXT' 		=> $lang->def('_MESSAGE_TEXT'), 
			'_OPERATION_SUCCESSFUL' 		=> $lang->def('_OPERATION_SUCCESSFUL'), 
			'_OPERATION_FAILURE' 		=> $lang->def('_OPERATION_FAILURE')
		);
  
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON();
		$output = $json->encode($value);
  		aout($output);
	};break;
	case "send_ask_friend" : {
		
		require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
		
		$module_name 	= Get::req('module_name',DOTY_ALPHANUM, '');
		$platform 		= Get::req('platform', DOTY_ALPHANUM, '');
		
		$id_friend 			= importVar('id_friend');
		$message_request 	= importVar('message_request');
		
		$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
		$lang->setGlobal();
		$lang =& DoceboLanguage::createInstance( $module_name, $platform);
		
		$my_fr = new MyFriends(getLogUserId());
		if($my_fr->addFriend($id_friend, MF_WAITING, $message_request)) {
			$value = array('re' => true);
		} else {
			$value = array('re' => false);
		}
		
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON();
		$output = $json->encode($value);
  		aout($output);
	};break;
	case "send_message" : {
		
		require_once($GLOBALS['where_framework'].'/lib/lib.message.php');
		
		$module_name 	= importVar('module_name');
		$platform 		= importVar('platform');
		
		$recipient 			= importVar('send_to');
		$message_subject 	= importVar('message_subject');
		$message_text 		= importVar('message_text');
		
		$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
		$lang->setGlobal();
		$lang =& DoceboLanguage::createInstance( $module_name, $platform);
		
		if(MessageModule::quickSendMessage(getLogUserId(), $recipient, $message_subject, $message_text)) {
			$value = array('re' => true);
		} else {
			$value = array('re' => false);
		}
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON();
		$output = $json->encode($value);
  		aout($output);
	};break;
}
 
?>