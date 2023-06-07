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

$content = '';

if (isset($id_group)) {
    $_form_id = 'mod_group_form';
    $_form_action = 'ajax.adm_server.php?r=adm/functionalroles/mod_group_action';
} else {
    $_form_id = 'add_group_form';
    $_form_action = 'ajax.adm_server.php?r=adm/functionalroles/add_group_action';
}

$content .= Form::openForm($_form_id, $_form_action);

//edit name and description in all languages (in a TabView widget)
$content .= '<div id="group_langs_tab">';

$_tabview_titles = '<ul class="nav nav-tabs">';
$_tabview_contents = '<div class="tab-content">';

//edit name and description in all languages
$_langs = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {
    $_name = isset($group_langs[$_lang_code]) ? $group_langs[$_lang_code]['name'] : '';
    $_desc = isset($group_langs[$_lang_code]) ? $group_langs[$_lang_code]['description'] : '';

    $_tabview_titles .= '<li' . ($_lang_code == Lang::get() ? ' class="active"' : '') . '>'
        . '<a data-toggle="tab" href="#langs_tab_' . $_lang_code . '"><em>' . $_lang_code //$_lang_data['description']
        . ($_name == '' && isset($id_group) ? ' (*)' : '')
        . '</em></a></li>';

    $_tabview_contents .= '<div class="tab-pane' . ($_lang_code == Lang::get() ? ' active' : '') . '" id="langs_tab_' . $_lang_code . '">';

    $_tabview_contents .= Form::getTextfield(
        Lang::t('_NAME', 'standard'),
    'name_' . $_lang_code,
    'name[' . $_lang_code . ']',
    255,
    $_name
    );

    $_tabview_contents .= Form::getSimpleTextarea(
        Lang::t('_DESCRIPTION', 'standard'),
        'description_' . $_lang_code,
        'description[' . $_lang_code . ']',
        $_desc
    );

    $_tabview_contents .= '</div>';
} //end for

$_tabview_titles .= '</ul>';
$_tabview_contents .= '</div>';

$content .= $_tabview_titles . $_tabview_contents;

$content .= '</div>';

//if we are editing a group, then get the id of the group in an input
if (isset($id_group)) {
    $content .= Form::getHidden('id_group', 'id_group', $id_group);
}

$content .= Form::closeForm();

//send output
if (isset($json)) {
    $params = [
        'success' => true,
        'header' => $title,
        'body' => $content,
    ];
    echo $json->encode($params);
} else {
    echo '<h2>' . $title . '</h2>';
    echo $content;
}
