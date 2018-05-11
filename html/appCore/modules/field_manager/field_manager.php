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
 * @version  $Id: field_manager.php 985 2007-02-28 16:52:50Z giovanni $
 * @category Field
 * @author   Fabio Pirovano <fabio@docebo.com>
 */

// XXX: field_list
function field_list() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	//require_once(_i18n_.'/lib.lang.php');

	$back_coded 	= htmlentities(urlencode('index.php?modname=field_manager&op=field_list'));
	$std_lang 		=& DoceboLanguage::createInstance('standard', 'framework');
	$lang 			=& DoceboLanguage::createInstance('field', 'framework');
	$out 			=& $GLOBALS['page'];
	$filter 		= new Form();

	//find available field type
	$re_field = sql_query("
	SELECT type_field FROM "
	.$GLOBALS['prefix_fw']
	."_field_type ORDER BY type_field");
	$field_av = array();
	$field_select = array('all_field' => $lang->def('_ALL_FIELD_TYPE'));
	while(list($type_field) = sql_fetch_row($re_field)) {
		$field_av[] = $type_field;
		$field_select[] = $lang->def('_'.strtoupper($type_field));
	}

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_FIELD_MANAGER'), 'field_manager'));
	$out->add('<div class="std_block">');

	//catch possible operation result
	if(isset($_GET['result'])) {
		if($_GET['result'] == 'success') $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
		if($_GET['result'] == 'fail') $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
	}

	$ord = importVar('ord', false, 'trans');
	$flip = importVar('flip', true, 0);

	//filter------------------------------------------------------------
	$filter_type_field = importVar('filter_type_field', false, 'all_field');
	$filter_name_field = importVar('filter_name_field', false, $lang->def('_SEARCH'));
	$out->add(
		$filter->openForm('field_filter', 'index.php?modname=field_manager&amp;op=field_list')
		.$filter->getOpenFieldset($lang->def('_SEARCH'))
		.$filter->getHidden('ord', 'ord', $ord)
		.$filter->getHidden('flip', 'flip', $flip)
		.$filter->getDropdown($lang->def('_FIELD_TYPE'), 'filter_type_field', 'filter_type_field',
			$field_select, $filter_type_field)
		.$filter->getTextfield($lang->def('_NAME'), 'filter_name_field', 'filter_name_field',
			'255', $filter_name_field)
		.$filter->openButtonSpace()
		.$filter->getButton('search', 'search', $std_lang->def('_SEARCH'))
		.$filter->closeButtonSpace()
		.$filter->getCloseFieldset()
		.$filter->closeForm()
	);

	//display inserted field--------------------------------------------
	$tb_field = new Table(Get::sett('visuItem'));

	$query_field_display = "
	SELECT id_common, type_field, translation
	FROM ".$GLOBALS['prefix_fw']."_field
	WHERE lang_code = '".getLanguage()."'
		".( isset($_POST['filter_type_field']) && $_POST['filter_type_field'] != 'all_field' ?
			" AND type_field = '".$field_av[$_POST['filter_type_field']]."' " :
			"" )."
		".( isset($_POST['filter_name_field']) && $_POST['filter_name_field'] != $lang->def('_SEARCH') ?
			" AND translation LIKE '%".$filter_name_field."%'" :
			"" )."
	ORDER BY sequence";
	$re_field_display = sql_query($query_field_display);
	$all_fields = sql_num_rows($re_field_display);

	$img_up = '<img class="valing-middle" src="'.getPathImage().'standard/up.png" alt="'.$std_lang->def('_MOVE_UP').'" />';
	$img_down = '<img class="valing-middle" src="'.getPathImage().'standard/down.png" alt="'.$std_lang->def('_MOVE_DOWN').'" />';

	$content_h 	= array(
		'<a href="index.php?modname=field_manager&amp;op=field_list">'.$lang->def('_FIELD_NAME').'</a>',
		'<a href="index.php?modname=field_manager&amp;op=field_list">'.$lang->def('_FIELD_TYPE').'</a>');
	$type_h 	= array('', 'align_center');

	$mod_perm = checkPerm('mod', true);
	$del_perm = checkPerm('del', true);
	if($mod_perm) {
		$content_h[] = $img_down;
		$content_h[] = $img_up;
		$content_h[] = '<img src="'.getPathImage().'standard/edit.png" alt="'.$std_lang->def('_MOD').'" />';
		$type_h[] = 'image';
		$type_h[] = 'image';
		$type_h[] = 'image';
	}
	if($del_perm) {
		$content_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$std_lang->def('_DEL').'" />';
		$type_h[] = 'image';
	}
	$tb_field->setColsStyle($type_h);
	$tb_field->addHead($content_h);

	$lat_type = 'textfield';
	$i = 1;
	while(list($id_common, $type_field, $translation) = sql_fetch_row($re_field_display)) {

		$cont = array($translation, $lang->def('_'.strtoupper($type_field)));
		if($mod_perm) {
			if($i != $all_fields) {
			$cont[] = '<a href="index.php?modname=field_manager&amp;op=movedown&amp;type_field='
				.$type_field.'&amp;id_common='.$id_common.'&amp;back='.$back_coded.'"'
				.' title="'.$lang->def('_MOVE_DOWN').'">'.$img_down.'</a>';
			} else $cont[] = '';
			if($i != 1) {
			$cont[] = '<a href="index.php?modname=field_manager&amp;op=moveup&amp;type_field='
				.$type_field.'&amp;id_common='.$id_common.'&amp;back='.$back_coded.'"'
				.' title="'.$lang->def('_MOVE_UP').'">'.$img_up.'</a>';
			} else $cont[] = '';
			$cont[] = '<a href="index.php?modname=field&amp;op=manage&amp;fo=edit&amp;type_field='
				.$type_field.'&amp;id_common='.$id_common.'&amp;back='.$back_coded.'"'
				.' title="'.$lang->def('_MOD').'">'
				.'<img src="'.getPathImage().'standard/edit.png" alt="'.$std_lang->def('_MOD').'" /></a>';
		}
		if($del_perm) {
			/*
			$cont[] = '<a href="index.php?modname=field_manager&amp;op=field_del&amp;id_common='.$id_common.'"'
				.' title="'.$lang->def('_DEL').'">'
				.'<img src="'.getPathImage().'standard/delete.png" alt="'.$std_lang->def('_DEL').'" /></a>';
			*/
			$cont[] = '<a href="index.php?modname=field&amp;op=manage&amp;fo=del&amp;type_field='.$type_field.'&amp;id_common='.$id_common.'&amp;back=index.php%3Fmodname%3Dfield_manager%26op%3Dfield_list"'
				.' title="'.$lang->def('_DEL').' : '.$translation.'">'
				.'<img src="'.getPathImage().'standard/delete.png" alt="'.$std_lang->def('_DEL').'" /></a>';
		}
		$tb_field->addBody($cont);
		$lat_type = $type_field;
		$i++;
	}

	$create_form = new Form();
	$select = '';
	foreach($field_av as $k => $type_field) {
		$select .= '<option value="'.$type_field.'"'
				.( $type_field == $lat_type ? ' selected="selected"' : '' ).'>'
				.$lang->def('_'.strtoupper($type_field)).'</option>';
	}

	if($del_perm) {
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=fo=del]');
	}

	//add form----------------------------------------------------------
	if(checkPerm('add', true)) {
		$tb_field->addActionAdd(
				$create_form->openForm('field_add', 'index.php?modname=field&amp;op=manage&amp;fo=create')
				.$create_form->getHidden('back', 'back', $back_coded)
				.'<label for="type_field">'
				.'<img class="valing-middle" src="'.getPathImage().'standard/add.png" alt="'.$std_lang->def('_ADD').'" />'
				.' '.$lang->def('_ADD_NEW_FIELD').'</label> '
				.'<select id="type_field" name="type_field">'
				.$select
				.'</select> '
				.$filter->getButton('new_field', 'new_field', $std_lang->def('_CREATE'), 'button_nowh')
				.$filter->closeForm()
		);
	}
	$out->add($tb_field->getTable());

	$out->add('<a href="index.php?modname=field_manager&amp;op=fixsequence&amp;back='.$back_coded.'"'
				.' title="'.$lang->def('_FIX_SEQUENCE').'">'.$lang->def('_FIX_SEQUENCE').'</a>');

	$out->add('</div>');
}

function field_del() {
	checkPerm('del');
	$back_coded 	= htmlentities(urlencode('index.php?modname=field_manager&op=field_list'));
	$std_lang 		=& DoceboLanguage::createInstance('standard', 'framework');
	$lang 			=& DoceboLanguage::createInstance('field', 'framework');
	$out 			=& $GLOBALS['page'];

	$id_common = importVar('id_common', true, 0);

	//find available field type
	$re_field = sql_query("
	SELECT type_field, translation
	FROM ".$GLOBALS['prefix_fw']."_field
	WHERE id_common = '".(int)$id_common."'
	ORDER BY type_field");
	list($type_field, $translation) = sql_fetch_row($re_field);

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_FIELD_MANAGER'), 'field_manager'));
	$out->add('<div class="std_block">');

	$out->add('<div class="boxinfo_title">'.$lang->def('_AREYOUSURE').'</div>'
			.'<div class="boxinfo_container">'
			.'<span class="text_bold">'.$lang->def('_FIELD_TYPE').' : </span>'.$lang->def('_'.strtoupper($type_field)).'<br />'
			.'<span class="text_bold">'.$lang->def('_FIELD_NAME').' : </span>'.$translation
			.'</div>'
			.'<div class="del_container">'
			.'<a href="index.php?modname=field&amp;op=manage&amp;fo=del&amp;type_field='
					.$type_field.'&amp;id_common='.(int)$id_common.'&amp;back='.$back_coded.'">'
				.'<img src="'.getPathImage().'standard/delete.png" alt="'.$std_lang->def('_CONFIRM').'" />&nbsp;'
				.$std_lang->def('_CONFIRM').'</a>&nbsp;&nbsp;'
			.'<a href="index.php?modname=field_manager&amp;op=field_list&amp;result=undo">'
				.'<img src="'.getPathImage().'standard/cancel.png" alt="'.$std_lang->def('_UNDO').'" />&nbsp;'
				.$std_lang->def('_UNDO').' </a>'
			.'</div>');

	$out->add('</div>');
}

function movefield($direction) {
	checkPerm('mod');
	$out 			=& $GLOBALS['page'];

	$id_common = importVar('id_common', true, 0);

	$re_field = sql_query("
	SELECT tf.type_file, tf.type_class, f.sequence
	FROM ".$GLOBALS['prefix_fw']."_field_type AS tf JOIN
		".$GLOBALS['prefix_fw']."_field AS f
	WHERE tf.type_field = f.type_field AND
		id_common = '".(int)$id_common."'");
	list($type_file_1, $type_class_1, $sequence) = sql_fetch_row($re_field);

	if($direction == 'up') {
		$next_seq = $sequence - 1;
	} else {
		$next_seq = $sequence + 1;
	}

	$query_field_2 = "
	SELECT tf.type_file, tf.type_class, f.id_common
	FROM ".$GLOBALS['prefix_fw']."_field_type AS tf JOIN
		".$GLOBALS['prefix_fw']."_field AS f
	WHERE tf.type_field = f.type_field AND
		f.sequence = '".(int)$next_seq."'";

	$re_field_2 = sql_query($query_field_2);
	list($type_file_2, $type_class_2, $id_common_2) = sql_fetch_row($re_field_2);

	$back = urldecode(importVar('back'));
	if($type_file_2 == '') {

		fixsequence(false);
		$re_field_2 = sql_query($query_field_2);
		list($type_file_2, $type_class_2, $id_common_2) = sql_fetch_row($re_field_2);

		if($type_file_2 == '') Util::jump_to($back);
	}

	require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file_1);
	$first_instance = eval("return new $type_class_1( $id_common );");
	$first_instance->movetoposition($next_seq);

	require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file_2);
	$second_instance = eval("return new $type_class_2( $id_common_2 );");
	$second_instance->movetoposition($sequence);

	Util::jump_to($back);
}

function fixsequence($jump = true) {
	checkPerm('mod');

	$re_field = sql_query("
	SELECT DISTINCT tf.type_file, tf.type_class, f.id_common
	FROM ".$GLOBALS['prefix_fw']."_field_type AS tf JOIN
		".$GLOBALS['prefix_fw']."_field AS f
	WHERE tf.type_field = f.type_field
	ORDER BY f.sequence");

	$new_sequence = 1;
	while(list($type_file, $type_class, $id_common) = sql_fetch_row($re_field)) {

		require_once(Forma::inc($GLOBALS['where_framework'].'/modules/field/'.$type_file));
		$first_instance = eval("return new $type_class( $id_common );");
		$first_instance->movetoposition($new_sequence++);
	}

	$back = urldecode(importVar('back'));
	if($jump) Util::jump_to($back);
}

// XXX: switch
switch($GLOBALS['op']) {
	case "field_list" : {
		field_list();
	};break;
	case "field_del" : {
		field_del();
	};break;

	case "moveup" : {
		movefield('up');
	};break;
	case "movedown" : {
		movefield('down');
	};break;
	case "fixsequence" : {
		fixsequence();
	};break;
}

?>