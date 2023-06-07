<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

if (isset($id_questcategory)) {
    $_form_id = 'mod_questcategory_form';
    $_form_action = 'ajax.adm_server.php?r=alms/questcategory/edit_action';
    $_title = Lang::t('_MOD', 'standard');
} else {
    $_form_id = 'add_questcategory_form';
    $_form_action = 'ajax.adm_server.php?r=alms/questcategory/create_action';
    $_title = Lang::t('_ADD', 'standard');
}

$_body = '';
$_body .= Form::openForm($_form_id, $_form_action);

//if we are editing an existent role, print its id
if (isset($id_questcategory)) {
    $_body .= Form::getHidden('id_questcategory', 'id', $id_questcategory);
}

$_body .= Form::getTextfield(
    Lang::t('_NAME', 'standard'),
    'name_questcategory',
    'name',
    255,
    isset($name) ? $name : ''
);

$_body .= Form::getSimpleTextarea(
    Lang::t('_DESCRIPTION', 'standard'),
    'description_questcategory',
    'description',
    isset($description) ? $description : ''
);

$_body .= Form::closeForm();

//send output
if (isset($json)) {
    $params = [
        'success' => true,
        'header' => $_title,
        'body' => $_body,
    ];
    echo $json->encode($params);
} else {
    echo getTitleArea($_title);
    echo $_body;
}
