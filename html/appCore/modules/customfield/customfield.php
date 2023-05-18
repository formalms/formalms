<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version  $Id: field.php 977 2007-02-23 10:40:19Z fabio $
 *
 * @category Field
 *
 * @author   Fabio Pirovano <fabio@docebo.com>
 */

// XXX: field create
function field_create($type_field, $back)
{
    checkPerm('add', false, 'field_manager');
    $re_quest = sql_query("
	SELECT type_file, type_class 
	FROM %adm_customfield_type 
	WHERE type_field = '" . $type_field . "'");
    if (!sql_num_rows($re_quest)) {
        return;
    }
    list($type_file, $type_class) = sql_fetch_row($re_quest);

    require_once \FormaLms\lib\Forma::include(_adm_ . '/modules/customfield/', $type_file);

    $quest_obj = new $type_class(0);
    $quest_obj->setUrl('index.php?modname=customfield&amp;op=manage&amp;fo=create');
    $quest_obj->create($back);
}

// XXX: field edit
function field_edit($type_field, $id_field, $back)
{
    checkPerm('mod', false, 'field_manager');
    $re_quest = sql_query("
	SELECT type_file, type_class 
	FROM %adm_customfield_type 
	WHERE type_field = '" . $type_field . "'");
    if (!sql_num_rows($re_quest)) {
        return;
    }
    list($type_file, $type_class) = sql_fetch_row($re_quest);

    require_once \FormaLms\lib\Forma::include(_adm_ . '/modules/customfield/', $type_file);

    $quest_obj = new $type_class($id_field);
    $quest_obj->setUrl('index.php?modname=customfield&amp;op=manage&amp;fo=edit');
    $quest_obj->edit($back);
}

// XXX: field del
function field_del($type_field, $id_field, $back)
{
    checkPerm('del', false, 'field_manager');

    $re_quest = sql_query("
	SELECT type_file, type_class 
	FROM %adm_customfield_type 
	WHERE type_field = '" . $type_field . "'");
    if (!sql_num_rows($re_quest)) {
        return;
    }
    list($type_file, $type_class) = sql_fetch_row($re_quest);

    require_once \FormaLms\lib\Forma::include(_adm_ . '/modules/customfield/', $type_file);

    $quest_obj = new $type_class($id_field);
    $quest_obj->setUrl('index.php?modname=customfield&amp;op=manage&amp;fo=del');
    $quest_obj->del($back);
}

function field_specialop($type_field, $id_field, $back)
{
    $re_quest = sql_query("
	SELECT type_file, type_class 
	FROM %adm_customfield_type 
	WHERE type_field = '" . $type_field . "'");
    if (!sql_num_rows($re_quest)) {
        return;
    }
    list($type_file, $type_class) = sql_fetch_row($re_quest);

    require_once \FormaLms\lib\Forma::include(_adm_ . '/modules/customfield/', $type_file);

    $quest_obj = new $type_class($id_field);
    $quest_obj->setUrl('index.php?modname=customfield&amp;op=manage&amp;fo=special');
    $quest_obj->specialop($back);
}

// XXX: switch
$fo = importVar('fo');
switch ($fo) {
    case 'create':
        $back = urldecode(importVar('back'));
        $type_field = importVar('type_field');

        field_create($type_field, $back);
     break;
    case 'edit':
        $back = urldecode(importVar('back'));
        $id_field = importVar('id_field', true, 0);
        $type_field = importVar('type_field');

        field_edit($type_field, $id_field, $back);
     break;
    case 'del':
        $back = urldecode(importVar('back'));
        $id_field = importVar('id_field', true, 0);
        $type_field = importVar('type_field');

        field_del($type_field, $id_field, $back);
     break;
    case 'special':
        $back = urldecode(importVar('back'));
        $id_field = importVar('id_field', true, 0);
        $type_field = importVar('type_field');

        field_specialop($type_field, $id_field, $back);
     break;
}
