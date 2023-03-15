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

$body .= Form::openForm('changepwd_form', 'ajax.adm_server.php?r=' . $this->link . '/changepwd_action');

$body .= Form::getHidden('changepwd_idst', 'idst', 0); //init with invalid idst: we have to choose it with autocomplete textfield
$body .= '<div id="changepwd_userid_container"></div>';
$body .= Form::getTextfield(Lang::t('_USERNAME', 'standard'), 'changepwd_userid', 'userid', 255, '');
$body .= Form::getPassword(Lang::t('_NEW_PASSWORD', 'register'), 'changepwd_new_password', 'new_password', 255);
$body .= Form::getPassword(Lang::t('_RETYPE_PASSWORD', 'register'), 'changepwd_confirm_password', 'confirm_password', 255);
$body .= Form::getCheckBox(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'), 'force_changepwd', 'force_changepwd', 1, false);

$body .= Form::closeForm();

if (isset($json)) {
    $output['header'] = $title;
    $output['body'] = $body;
    echo $json->encode($output);
} else {
    echo '<h2>' . $title . '</h2>';
    echo $body;
}
