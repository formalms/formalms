<?php

$body = Form::openForm('add_rules', 'ajax.adm_server.php?r=alms/enrollrules/update')

	.Form::getHidden('id_rule', 'id_rule', $rule->id_rule)
	.Form::getTextfield(Lang::t('_TITLE', 'enrolluser'), 'title', 'title', 255, $rule->title)
	.Form::getDropdown(Lang::t('_LANGUAGE', 'enrolluser'), 'lang_code', 'lang_code', $languages, array_search($rule->lang_code, $languages))

	.Form::closeForm();
$output = array(
	'success' => true,
	'header' => Lang::t('_ADD', 'enrolluser'),
	'body' => $body
);
echo $this->json->encode($output);