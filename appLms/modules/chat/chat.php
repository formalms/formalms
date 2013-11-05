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

if(Docebo::user()->isAnonymous()) die("You can't access");

function chat() {
	checkPerm('view');
	$lang 	=& DoceboLanguage::createInstance('chat');
	
	require_once($GLOBALS['where_scs'].'/lib/lib.chat.php');
	
	$chat_man = new ChatManager();
	
	$id_room = $chat_man->getIdRoom('lms', 'course', $_SESSION['idCourse']);
	if (!$id_room) {
		require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
		$course_name=$GLOBALS['course_descriptor']->getValue('name');
		$rules = array(
					'room_name' => $course_name,
					'room_type' => 'course',
					'id_source' => $_SESSION['idCourse'] );
		$id_room = insertRoom($rules);
	}
	
	
	// show only the room of the current course
	$chat_man->setRoomFilter(array($id_room));
	
	//$users = $chat_man->getRoomUserOnline('lms', $id_room);
	
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_CHAT'), 'advice')
		.'<div class="std_block">'
		.'<div>'.$lang->def('_CHAT_DESCRIPTION').'</div><br />'
		.'<div>'.$chat_man->getOpenChatCommand($lang->def('_OPENCHAT'), $lang->def('_OPENCHAT_WA'), 'lms', $id_room).'</div>'
		.'</div>'
	, 'content');
}

function chatDispatch($op) {
	switch($op) {
		case "chat" : {
			chat();
		};break;
	}
}

?>