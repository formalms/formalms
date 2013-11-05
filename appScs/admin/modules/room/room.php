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
	
			.Form::getInputRadio('rules_'.$name.'_admin', 'rules['.$name.']', 'admin', ($value == 'admin'), '').'&nbsp'
			.Form::getLabel( 'rules_'.$name.'_admin', $lang->def('_ADMIN'), 'label_padded' ).'&nbsp'
			
			.Form::getInputRadio('rules_'.$name.'_alluser', 'rules['.$name.']', 'alluser', ($value == 'alluser'), '').'&nbsp'
			.Form::getLabel('rules_'.$name.'_alluser', $lang->def('_ALLUSER'), 'label_padded' ).'&nbsp'
			
			.Form::getInputRadio('rules_'.$name.'_noone', 'rules['.$name.']', 'noone', ($value == 'noone'), '').'&nbsp'
			.Form::getLabel( 'rules_'.$name.'_noone', $lang->def('_NOONE'), 'label_padded' ).'&nbsp'
			
			.Form::getCloseCombo();
}

function listroom() {
	
	require_once(_base_.'/lib/lib.table.php');
	
	$lang 		=& DoceboLanguage::createInstance('admin_config', 'scs');
	$out		=& $GLOBALS['page'];
	$mod_perm	= checkPerm('mod', true);
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_ROOM_MAN'), 'admin_conf')
			.'<div class="std_block">');
	$tb_room = new Table(0, $lang->def('_ALL_ROOMS'), $lang->def('_ALL_ROOMS_SUMMARY'));
	
	$cont_h = array($lang->def('_ROOM_NAME'), $lang->def('_ROOM_TYPE'));
	$type_h = array('', 'align_center');
	if($mod_perm) {
		
		$cont_h[] = '<img src="'.getPathImage('fw').'standard/edit.png" alt="'.$lang->def('_MOD').'" />';
		$type_h[] = 'image';
		$cont_h[] = '<img src="'.getPathImage('fw').'standard/delete.png" alt="'.$lang->def('_DEL').'" />';
		$type_h[] = 'image';
	}
	$tb_room->setColsStyle($type_h);
	$tb_room->addHead($cont_h);
	
	$query_rooms = "
	SELECT id_room, room_name, room_type 
	FROM ".$GLOBALS['prefix_scs']."_rules_room ";
	$re_rooms = sql_query($query_rooms);
	while(list($id_room, $room_name, $room_type) = sql_fetch_row($re_rooms)) {
		
		$cont = array($room_name);
		switch($room_type) {
			case "course" : 	$cont[] = $lang->def('_COURSE');	break;
			case "private" : 	$cont[] = $lang->def('_PRIVATE');	break;
			case "public" : 	$cont[] = $lang->def('_PUBLIC');	break;
		}
		if($mod_perm) {
			
			$cont[] = '<a href="index.php?modname=room&amp;op=modroom&amp;id_room='.$id_room.'"'
					.' title="'.$lang->def('_MOD').' : '.$room_name.'">'
				.'<img src="'.getPathImage('fw').'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$room_name.'" /></a>';
				
			$cont[] = '<a href="index.php?modname=room&amp;op=delroom&amp;id_room='.$id_room.'"'
					.' title="'.$lang->def('_DEL').' : '.$room_name.'">'
				.'<img src="'.getPathImage('fw').'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$room_name.'" /></a>';
		}
		$tb_room->addBody($cont);
	}
	if($mod_perm) {
		
		$tb_room->addActionAdd(
			'<a href="index.php?modname=room&amp;op=newroom">'
			.'<img src="'.getPathImage('fw').'standard/add.png" alt="'.$lang->def('_NEW_ROOM').'" />'
			.'&nbsp;'.$lang->def('_NEW_ROOM')
			.'</a>');
	}
	$out->add($tb_room->getTable());
	
	$out->add('</div>');
}

function newroom() {
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
	$lang 	=& DoceboLanguage::createInstance('admin_config', 'scs');
	$out	=&$GLOBALS['page'];
	
	$rules = getAdminRules();
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_ROOM_MAN'), 'admin_conf')
			.'<div class="std_block">');
			
	$room_types = array(
		"course" => $lang->def('_COURSE'),
		"private" => $lang->def('_PRIVATE'), 
		"public" => $lang->def('_PUBLIC')
	);
	$out->add(
		Form::openForm('rules_admin', 'index.php?modname=room&amp;op=insroom')
		.Form::openElementSpace()
		
		.Form::getTextfield($lang->def('_ROOM_NAME'), 'rules_room_name', 'rules[room_name]', 255)
		.Form::getDropdown($lang->def('_ROOM_TYPE'), 'rules_room_type', 'rules[room_type]', $room_types)
	);
	
	while(list($var_name, $var_value) = each($rules)) {
		
		$out->add(maskMultiple($var_name, $var_value));
	}
	$out->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm() 
	);
	$out->add('</div>');
}

function insroom() {
	
	require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
	
	$re = insertRoom($_POST['rules']);
	
	Util::jump_to('index.php?modname=room&amp;op=room&amp;result='.( $re ? 'ok' : 'err' ));
}


function modroom() {
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
	$lang 	=& DoceboLanguage::createInstance('admin_config', 'scs');
	$out	=&$GLOBALS['page'];
	$id_room = importVar('id_room', true, 0);
	
	$rules = getRoomRules($id_room);
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_ROOM_MAN'), 'admin_conf')
			.'<div class="std_block">');
	$room_types = array(
		"course" => $lang->def('_COURSE'),
		"private" => $lang->def('_PRIVATE'), 
		"public" => $lang->def('_PUBLIC')
	);
	$out->add(
		Form::openForm('rules_admin', 'index.php?modname=room&amp;op=updroom')
		.Form::openElementSpace()
		.Form::getHidden('id_room', 'id_room', $id_room)
		
		.Form::getTextfield($lang->def('_ROOM_NAME'), 'rules_room_name', 'rules[room_name]', 255, $rules['room_name'])
		.Form::getDropdown($lang->def('_ROOM_TYPE'), 'rules_room_type', 'rules[room_type]', $room_types, $rules['room_type'])
	);
	
	reset($rules);
	while(list($var_name, $var_value) = each($rules)) {
		
		if(substr($var_name, 0, 6) == 'enable') {
			$out->add(maskMultiple($var_name, $var_value));
		}
	}
	$out->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm() 
	);
	$out->add('</div>');
}

function updroom() {
	
	require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
	$id_room = importVar('id_room', true, 0);
	$re = updateRoom($id_room, $_POST['rules']);
	
	Util::jump_to('index.php?modname=room&amp;op=room&amp;result='.( $re ? 'ok' : 'err' ));
}

function delroom() {
	
	require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
	
	$re = deleteRoom(importVar('id_room', true, 0));
	
	Util::jump_to('index.php?modname=room&amp;op=room&amp;result='.( $re ? 'ok' : 'err' ));
}

function roomDispatch($op) {
	
	switch($op) {
		case "room" : {
			listroom();
		};break;
		case "newroom" : {
			newroom();
		};break;
		case "insroom" : {
			insroom();
		};break;
		
		case "modroom" : {
			modroom();
		};break;
		case "updroom" : {
			updroom();
		};break;
		
		case "delroom" : {
			delroom();
		};break;
	}
}

}

?>