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

$body = '';

$body .= Form::openForm('subscr_course_form', 'ajax.adm_server.php?r=' . $this->link . '/fast_subscribe_dialog_action');

$body .= Form::getHidden('subscr_id_user', 'id_user', 0); //init with invalid id: we have to choose it with autocomplete textfield
$body .= Form::getHidden('subscr_id_course', 'id_course', 0); //init with invalid id: we have to choose it with autocomplete textfield

$body .= Form::getTextfield(Lang::t('_COURSE', 'standard'), 'subscr_course', 'course', 255, '');
$body .= '<div id="subscr_course_container"></div>';

//hidden boxes for editions and classrooms dropdowns
$body .= '<div id="editions_div" style="display:none;">';
$body .= Form::getDropdown(Lang::t('_EDITIONS', 'course'), 'editions_sel', 'edition', []);
$body .= '</div>';
$body .= '<div id="classrooms_div" style="display:none;">';
$body .= Form::getDropdown(Lang::t('_CLASSROOMS', 'course'), 'classrooms_sel', 'classroom', []);
$body .= '</div>';

$body .= Form::getTextfield(Lang::t('_USER', 'standard'), 'subscr_userid', 'userid', 255, '');
$body .= '<div id="subscr_userid_container"></div>';

$body .= Form::getDropdown(Lang::t('_LEVEL', 'standard'), 'subscr_level', 'level', $levels, $selected_level);

$body .= Form::closeForm();

if (isset($json)) {
    $output['header'] = $title;
    $output['body'] = $body;
    echo $json->encode($output);
} else {
    echo getTitleArea($title);
    echo '<div class="std_block">';
    echo $body;
    echo '</div>';
}
