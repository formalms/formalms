<?php

if (isset($id_policy)) {
	$_form_id = 'mod_policy_form';
	$_form_action = 'ajax.adm_server.php?r=adm/privacypolicy/mod_action';
} else {
	$_form_id = 'add_policy_form';
	$_form_action = 'ajax.adm_server.php?r=adm/privacypolicy/add_action';
}

echo Form::openForm($_form_id, $_form_action);

echo Form::getTextfield(Lang::t('_NAME', 'standard'), 'policy_name', 'name', 255, (isset($id_policy) && isset($name) ? $name : ""));

//if we are editing an existent policy, print its id
if (isset($id_policy)) echo Form::getHidden('id_policy', 'id_policy', $id_policy);

//edit name and description in all languages (in a TabView widget)
echo '<div id="policy_langs_tab" class="yui-navset">';

$_tabview_titles = '<ul class="yui-nav">';
$_tabview_contents = '<div class="yui-content">';

//edit policy content in all languages
$_langs = Docebo::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {

	$_translation = isset($id_policy) && isset($translations[$_lang_code]) ? $translations[$_lang_code] : "";

	$_tabview_titles .= '<li'.($_lang_code == Lang::get() ? ' class="selected"' : '').'>'
		.'<a href="#langs_tab_'.$_lang_code.'"><em>'.$_lang_code //$_lang_data['description']
		.($_translation == '' && isset($id_policy) ? ' (*)' : '')
		.'</em></a></li>';

	$_tabview_contents .= '<div id="langs_tab_'.$_lang_code.'">';

	$_tabview_contents .= Form::getSimpleTextarea(
		Lang::t('_CONTENT', 'standard'),
		'translation_'.$_lang_code,
		'translation['.$_lang_code.']',
		$_translation
	);

	$_tabview_contents .= '</div>';

} //end for

$_tabview_titles .= '</ul>';
$_tabview_contents .= '</div>';

echo $_tabview_titles.$_tabview_contents;

echo '</div>';

echo Form::closeForm();

?>