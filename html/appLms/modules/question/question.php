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
function quest_create($type_quest, $idTest, $back_test) {
	
	
	$re_quest = sql_query("
	SELECT type_file, type_class 
	FROM ".$GLOBALS['prefix_lms']."_quest_type 
	WHERE type_quest = '".$type_quest."'");
	if( !sql_num_rows($re_quest) ) return;
	list($type_file, $type_class) = sql_fetch_row($re_quest);
	
	require_once(Forma::inc(_folder_lms_.'/modules/question/'.$type_file));
	
	$quest_obj = new $type_class( 0 );
	$quest_obj->create($idTest, $back_test);
}

// XXX: quest_edit
function quest_edit($type_quest, $idQuest, $back_test) {
	
	
	$re_quest = sql_query("
	SELECT type_file, type_class 
	FROM ".$GLOBALS['prefix_lms']."_quest_type 
	WHERE type_quest = '".$type_quest."'");
	if( !sql_num_rows($re_quest) ) return;
	list($type_file, $type_class) = sql_fetch_row($re_quest);
	
	require_once(Forma::inc(_folder_lms_.'/modules/question/'.$type_file));
	
	$quest_obj = eval("return new $type_class( $idQuest );");
	
	$quest_obj->edit($back_test);
}

// XXX: switch
switch($GLOBALS['op']) {
	case "create" : {
		
		$type_quest = importVar('type_quest');
		$idTest = importVar('idTest', true, 0);
		$back_test = urldecode(importVar('back_test'));
		
		quest_create($type_quest, $idTest, $back_test);
	};break;
	case "edit" : {
		
		$type_quest = importVar('type_quest');
		$idQuest = importVar('idQuest', true, 0);
		$back_test = urldecode(importVar('back_test'));
		
		quest_edit($type_quest, $idQuest, $back_test);
	};break;
	case "quest_download" : {
		
		$type_quest = importVar('type_quest');
		$id_quest 	= importVar('id_quest', true, 0);
		$id_track 	= importVar('id_track', true, 0);
		
		$re_quest = sql_query("
		SELECT type_file, type_class 
		FROM ".$GLOBALS['prefix_lms']."_quest_type 
		WHERE type_quest = '".$type_quest."'");
		if(!sql_num_rows($re_quest) ) return;
		list($type_file, $type_class) = sql_fetch_row($re_quest);
		
		require_once(Docebo::inc(_folder_lms_.'/modules/question/'.$type_file));
		
		$quest_obj = eval("return new $type_class( $id_quest );");
		
		$quest_obj->download($id_track);
	};break;
}

?>