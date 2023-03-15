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

$body .= Form::openForm('user_status_form', 'ajax.adm_server.php?r=adm/usermanagement/profile_dialog');

$view_button = '<span class="yui-button">'
    . '<span class="first-child view_button"><button id="user_status_button">&nbsp;</button></span></span>';

$body .= Form::getTextfield(Lang::t('_USERNAME', 'standard'), 'status_userid', 'userid', 255, '', '', $view_button);
$body .= '<div id="status_userid_container"></div>';
$body .= Form::getBreakrow();
$body .= '<div id="user_status_viewport"></div>';

$body .= Form::closeForm();

if (isset($json)) {
    $output = [];
    $output['header'] = $title;
    $output['body'] = $body;
    echo $json->encode($output);
} else {
    echo getTitleArea($title);
    echo '<div class="std_block">';
    echo $body;
    echo '</div>';
}
