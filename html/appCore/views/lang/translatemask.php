<?php
echo Form::openForm('add_lang', 'ajax.adm_server.php?r=adm/lang/insertkey')

	.Form::getTextfield(
		Lang::t('_LANG_MODULE', 'admin_lang'),
		'lang_module',
		'lang_module',
		255,
		''
	)
	.Form::getTextfield(
		Lang::t('_LANG_KEY', 'admin_lang'),
		'lang_key',
		'lang_key',
		255,
		''
	);
?>
<div id="translation_tab">
	<ul class="nav nav-tabs">
<?php
$_langs = Docebo::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {

	echo '<li'.($_lang_code==getLanguage() ? ' class="active"' : '').'>'
		.'<a data-toggle="tab" href="#langs_tab_'.$_lang_code.'"><em>'.$_lang_code.'</em></a>'
		.'</li>';
}
?>
	</ul>
	<div class="tab-content">
<?php
foreach ($_langs as $_lang_code => $_lang_data) {
	echo '<div class="tab-pane'.($_lang_code==getLanguage() ? ' active' : '').'" id="langs_tab_'.$_lang_code.'">'

		.Form::getSimpleTextarea( Lang::t('_DESCRIPTION', 'standard'),
									'translation_'.$_lang_code,
									'translation['.$_lang_code.']',
									'' )
		.'</div>';
}
?>
	</div>
</div>
<?php echo Form::closeForm(); ?>