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

// XXX: quest_create
function quest_create($type_quest, $id_poll, $back_poll) {
	
	$re_quest = sql_query("
	SELECT type_file, type_class 
	FROM ".$GLOBALS['prefix_lms']."_quest_type_poll 
	WHERE type_quest = '".$type_quest."'");
	if( !sql_num_rows($re_quest) ) return;
	list($type_file, $type_class) = sql_fetch_row($re_quest);
	
	require_once($GLOBALS['where_lms'].'/modules/question_poll/'.$type_file);
	
	$quest_obj = eval("return new $type_class( 0 );");
	$quest_obj->create($id_poll, $back_poll);
}

// XXX: quest_edit
function quest_edit($type_quest, $id_quest, $back_poll) {
	
	$re_quest = sql_query("
	SELECT type_file, type_class 
	FROM ".$GLOBALS['prefix_lms']."_quest_type_poll 
	WHERE type_quest = '".$type_quest."'");
	if( !sql_num_rows($re_quest) ) return;
	list($type_file, $type_class) = sql_fetch_row($re_quest);
	
	require_once($GLOBALS['where_lms'].'/modules/question_poll/'.$type_file);
	
	$quest_obj = eval("return new $type_class( $id_quest );");
	
	$quest_obj->edit($back_poll);
}

// XXX: switch
switch($GLOBALS['op']) {
	case "create" : {
		
		$type_quest = importVar('type_quest');
		$id_poll = importVar('id_poll', true, 0);
		$back_poll = urldecode(importVar('back_poll'));
		
		quest_create($type_quest, $id_poll, $back_poll);
	};break;
	case "edit" : {
		
		$type_quest = importVar('type_quest');
		$id_quest = importVar('id_quest', true, 0);
		$back_poll = urldecode(importVar('back_poll'));
		
		quest_edit($type_quest, $id_quest, $back_poll);
	};break;
}

?>