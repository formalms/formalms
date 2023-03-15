<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

$body = Form::openForm('add_rules', 'ajax.adm_server.php?r=alms/enrollrules/update')

    . Form::getHidden('id_rule', 'id_rule', $rule->id_rule)
    . Form::getTextfield(Lang::t('_TITLE', 'enrolluser'), 'title', 'title', 255, $rule->title)
    . Form::getDropdown(Lang::t('_LANGUAGE', 'enrolluser'), 'lang_code', 'lang_code', $languages, array_search($rule->lang_code, $languages))

    . Form::closeForm();
$output = [
    'success' => true,
    'header' => Lang::t('_ADD', 'enrolluser'),
    'body' => $body,
];
echo $this->json->encode($output);
