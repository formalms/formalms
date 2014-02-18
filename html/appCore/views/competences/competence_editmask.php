<?php

if (isset($id_competence))
	$_text = Lang::t('_MOD', 'standard').(isset($competence_langs[getLanguage()]) ? ': '.$competence_langs[getLanguage()]['name'] : '');
else
	$_text = Lang::t('_ADD_COMPETENCE', 'competences');

echo getTitleArea(array(
	'index.php?r=adm/competences/show' => Lang::t('_COMPETENCES', 'competences'),
	$_text
));

?>
<div class="std_block">
<?php
/*
 * Variables used in the form:
 *  - $id_competence: (optional)
 *  - $id_category: competence category's id
 *  - $competence_langs: name and description of the competence in the languages of the platform
 *  - $competence_typology:
 *  - $competence_type:
 *  - $competence_score:
 *  - $competence_expiration:
 *
 */

if (isset($id_competence)) {
	$_form_id = 'mod_competence_form';
	$_form_action = 'index.php?r=adm/competences/mod_competence_action';
} else {
	$_form_id = 'add_competence_form';
	$_form_action = 'index.php?r=adm/competences/add_competence_action';
}

echo Form::openForm($_form_id, $_form_action);

echo Form::getDropdown(
	Lang::t('_CATEGORY', 'competences'),
	'id_category',
	'id_category',
	$competence_categories,
	isset($id_category) && (int)$id_category>0 ? (int)$id_category : '0'
);

//edit name and description in all languages (in a TabView widget)
echo '<div id="competence_langs_tab" class="yui-navset">';

$_tabview_titles = '<ul class="yui-nav">';
$_tabview_contents = '<div class="yui-content">';

$_langs = Docebo::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {

	$_name = isset($competence_langs[$_lang_code]) ? $competence_langs[$_lang_code]['name'] : "";
	$_desc = isset($competence_langs[$_lang_code]) ? $competence_langs[$_lang_code]['description'] : "";

	//echo Form::getOpenFieldset($_lang_data['description']);

	$_tabview_titles .= '<li'.($_lang_code==getLanguage() ? ' class="selected"' : '').'>'
		.'<a href="#langs_tab_'.$_lang_code.'"><em>'.$_lang_code //$_lang_data['description']
		.($_name == '' && isset($id_competence) ? ' (*)' : '')
		.'</em></a></li>';

	$_tabview_contents .= '<div id="langs_tab_'.$_lang_code.'">';

	$_tabview_contents .= Form::getTextfield(
		Lang::t('_NAME', 'standard'),
    'name_'.$_lang_code,
    'name['.$_lang_code.']',
    255,
    $_name
	);

	$_tabview_contents .= Form::getTextarea(
		Lang::t('_DESCRIPTION', 'standard'),
		'description_'.$_lang_code,
		'description['.$_lang_code.']',
		$_desc
	);

	$_tabview_contents .= '</div>';

	//echo Form::getCloseFieldset();

} //end for

$_tabview_titles .= '</ul>';
$_tabview_contents .= '</div>';

echo $_tabview_titles.$_tabview_contents;

echo '</div>';

echo '<script type="text/javascript">YAHOO.util.Event.onDOMReady(function() {'
	.' var tabs = new YAHOO.widget.TabView("competence_langs_tab"); });</script>';

//if we are editing an existing competence, than print its id
if (isset($id_competence)) echo Form::getHidden('id_competence', 'id_competence', $id_competence);

//if we are creating a new competence, than get the category id in which the competence should be created
//if (isset($id_category)) echo Form::getHidden('id_category', 'id_category', $id_category);

//competence properties
echo Form::getDropDown(
	Lang::t('_TYPOLOGY', 'competences'),
	'typology',
	'typology',
	$competence_typologies,
	isset($competence_typology) ? $competence_typology : 'skill'
);

echo Form::getDropDown(
	Lang::t('_TYPE', 'standard'),
	'type',
	'type',
	$competence_types,
	isset($competence_type) ? $competence_type : 'score'
);
/*
echo Form::getTextfield(
	Lang::t('_SCORE', 'standard'),
	'score',
	'score',
	255,
	isset($competence_score) ? (int)$competence_score : ''
);

echo Form::getTextfield(
	Lang::t('_EXPIRATION', 'competences'),
	'expiration',
	'expiration',
	3,
	isset($competence_expiration) ? (int)$competence_expiration : '',
	'&nbsp;(0 = '.Lang::t('_NEVER', 'standard').')'
);
*/
//save buttons
echo Form::openButtonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonspace();

echo Form::closeForm();

?>
</div>