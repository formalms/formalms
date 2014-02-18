<?php

$content = '';

if (isset($id_parent)) {
	$_form_id = 'add_category_form';
	$_form_action = 'ajax.adm_server.php?r=adm/competences/add_category_action';
} else {
	$_form_id = 'mod_category_form';
	$_form_action = 'ajax.adm_server.php?r=adm/competences/mod_category_action';
}

$content .= Form::openForm($_form_id, $_form_action);

//edit name and description in all languages (in a TabView widget)
$content .= '<div id="category_langs_tab" class="yui-navset">';

$_tabview_titles = '<ul class="yui-nav">';
$_tabview_contents = '<div class="yui-content">';

//edit name and description in all languages
$_langs = Docebo::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {

	$_name = isset($category_langs[$_lang_code]) ? $category_langs[$_lang_code]['name'] : "";
	$_desc = isset($category_langs[$_lang_code]) ? $category_langs[$_lang_code]['description'] : "";

	$_tabview_titles .= '<li'.($_lang_code==getLanguage() ? ' class="selected"' : '').'>'
		.'<a href="#langs_tab_'.$_lang_code.'"><em>'.$_lang_code //$_lang_data['description']
		.($_name == '' && !isset($id_parent) ? ' (*)' : '')
		.'</em></a></li>';

	$_tabview_contents .= '<div id="langs_tab_'.$_lang_code.'">';

	$_tabview_contents .= Form::getTextfield(
		Lang::t('_NAME', 'standard'),
    'name_'.$_lang_code,
    'name['.$_lang_code.']',
    255,
    $_name
	);

	$_tabview_contents .= Form::getSimpleTextarea(
		Lang::t('_DESCRIPTION', 'standard'),
		'description_'.$_lang_code,
		'description['.$_lang_code.']',
		$_desc
	);

	$_tabview_contents .= '</div>';

} //end for

$_tabview_titles .= '</ul>';
$_tabview_contents .= '</div>';

$content .= $_tabview_titles.$_tabview_contents;

$content .= '</div>';

//if we are editing an existing category, than print its id
if (isset($id_category)) $content .= Form::getHidden('id_category', 'id_category', $id_category);

//if we are creating a new category, then get the id of the parent folder
if (isset($id_parent)) $content .= Form::getHidden('id_parent', 'id_parent', $id_parent);

$content .= Form::closeForm();

if (isset($json)) {
	$params = array(
		'success' => true,
		'header' => $title,
		'body' => $content
	);
	echo $json->encode($params);
} else {
	echo '<h2>'.$title.'</h2>';
	echo $content;
}

?>