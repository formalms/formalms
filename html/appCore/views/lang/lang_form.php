<?php

echo Form::openForm('add_lang', 'ajax.adm_server.php?r=adm/lang/insertlang')

	.Form::getTextfield(
		Lang::t('_LANGUAGE', 'standard'),
		'lang_code',
		'lang_code',
		255,
		$lang->lang_code
	)
	.Form::getTextfield(
		Lang::t('_DESCRIPTION', 'standard'),
		'lang_description',
		'lang_description',
		255,
		$lang->lang_description
	)
	.Form::getRadioSet(
		Lang::t('_ORIENTATION', 'admin_lang'),
		'lang_direction',
		'lang_direction',
		array(	Lang::t('_DIRECTION_LTR', 'admin_lang') => 'ltr',
				Lang::t('_DIRECTION_RTL', 'admin_lang') => 'rtl' ),
		$lang->lang_direction
	)
	.Form::getTextfield(
		Lang::t('_LANG_BROWSERCODE', 'admin_lang'),
		'lang_browsercode',
		'lang_browsercode',
		255,
		$lang->lang_browsercode
	)
	.Form::closeForm();