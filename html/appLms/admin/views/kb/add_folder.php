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

$languages = Forma::langManager()->getAllLanguages(true);
$std_lang = getLanguage();
$form_content = '';

$form_content .= Form::openForm('addfolder_form', 'ajax.adm_server.php?r=alms/kb/createfolder');
$form_content .= Form::getHidden('addfolder_id_parent', 'id_parent', $id_parent);

foreach ($languages as $language) {
    $lang_code = $language['code'];
    $lang_name = $language['description'];
    $form_content .= Form::getTextfield($lang_code, 'newfolder_' . $lang_code, 'langs[' . $lang_code . ']', 50);
}

$form_content .= Form::closeForm();

if (isset($json)) {
    $output['success'] = true;
    $output['header'] = $title;
    $output['body'] = $form_content;
    echo $json->encode($output);
} else {
    echo '<h2>' . $title . '</h2>';
    echo $form_content;
}
