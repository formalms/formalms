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

echo Form::openForm('add_lang', 'ajax.adm_server.php?r=adm/lang/insertlang')

    . Form::getTextfield(
        Lang::t('_LANGUAGE', 'standard'),
        'lang_code',
        'lang_code',
        255,
        $lang->lang_code
    )
    . Form::getTextfield(
        Lang::t('_DESCRIPTION', 'standard'),
        'lang_description',
        'lang_description',
        255,
        $lang->lang_description
    )
    . Form::getRadioSet(
        Lang::t('_ORIENTATION', 'admin_lang'),
        'lang_direction',
        'lang_direction',
        [Lang::t('_DIRECTION_LTR', 'admin_lang') => 'ltr',
                Lang::t('_DIRECTION_RTL', 'admin_lang') => 'rtl', ],
        $lang->lang_direction
    )
    . Form::getTextfield(
        Lang::t('_LANG_BROWSERCODE', 'admin_lang'),
        'lang_browsercode',
        'lang_browsercode',
        255,
        $lang->lang_browsercode
    )
    . Form::closeForm();
