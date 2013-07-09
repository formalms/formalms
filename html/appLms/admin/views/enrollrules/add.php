<?php

$body = Form::openForm('add_rules', 'ajax.adm_server.php?r=alms/enrollrules/insert')

	.Form::getTextfield(Lang::t('_TITLE', 'enrolluser'), 'title', 'title', 255)
	.Form::getDropdown(Lang::t('_LANGUAGE', 'enrolluser'), 'lang_code', 'lang_code', $languages)
	.Form::getDropdown(Lang::t('_TYPE', 'enrolluser'), 'rule_type', 'rule_type', $types)
		
	.Form::closeForm();
$output = array(
	'success' => true,
	'header' => Lang::t('_ADD', 'enrolluser'),
	'body' => $body
);
echo $this->json->encode($output);