<?php

$languages = Docebo::langManager()->getAllLanguages(true);
$std_lang = getLanguage();
$form_content = "";

$form_content .= Form::openForm('addfolder_form', "ajax.adm_server.php?r=alms/kb/createfolder");
$form_content .= Form::getHidden('addfolder_id_parent', 'id_parent', $id_parent);

foreach ($languages as $language) {
	$lang_code = $language['code'];
	$lang_name = $language['description'];
	$form_content .= Form::getTextfield($lang_code, 'newfolder_'.$lang_code, 'langs['.$lang_code.']', 50);
}

$form_content .= Form::closeForm();

if (isset($json)) {
	$output['success'] = true;
	$output['header'] = $title;
	$output['body'] = $form_content;
	echo $json->encode($output);
} else {
	echo '<h2>'.$title.'</h2>';
	echo $form_content;
}

?>