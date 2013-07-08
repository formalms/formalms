<?php

$body = "";
$languages = Docebo::langManager()->getAllLanguages(true);//getAllLangCode();
$std_lang = getLanguage();

$body .= Form::openForm('addfolder_form', "ajax.adm_server.php?r=". $this->link."/createfolder");

$body .= Form::getHidden('addfolder_id_parent', 'id_parent', $id_parent);
$body .= Form::getTextfield(Lang::t('_CODE', 'organization_chart'), 'org_code', 'org_code', 50);
$body .= Form::getDropdown(Lang::t('_DEFAULTTEMPLATE', 'configuration'), 'associated_template', 'associated_template', getTemplateList(), $default_template);
$body .= Form::getBreakRow();

foreach ($languages as $language) {
	$lang_code = $language['code'];
	$lang_name = $language['description'];
	$body .= Form::getTextfield($lang_code, 'newfolder_'.$lang_code, 'langs['.$lang_code.']', 255);
}

$body .= Form::closeForm();

if (isset($json)) {
	$output['header'] = $title;
	$output['body'] = $body;
	echo $json->encode($output);
} else {
	echo '<h2>'.$title.'</h2>';
	echo $body;
}

?>