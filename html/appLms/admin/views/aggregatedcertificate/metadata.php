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

     cout(
                getTitleArea($page_title)
                . '<div class="std_block">'
                . getBackUi('index.php?r=alms/' . $controller_name . '/show', Lang::t('_BACK'))
                . Form::openForm('metadataForm', 'index.php?r=alms/' . $controller_name . '/saveMetaData')
                . Form::openElementSpace());
     if (isset($id_certificate)) {
         cout(Form::getHidden('id_certificate', 'id_certificate', $id_certificate));
     }

    cout(
        Form::getTextfield(Lang::t('_CODE'), 'code', 'code', 255, (isset($metacert['code']) ? $metacert['code'] : ''))
        . Form::getTextfield(Lang::t('_NAME'), 'name', 'name', 255, (isset($metacert['name']) ? $metacert['name'] : ''))
        . Form::getDropdown(
        Lang::t('_BASE_LANGUAGE', 'certificate'),
        'base_language',
        'base_language',
         $languages,
          (isset($metacert['base_language']) ? $metacert['base_language'] : Lang::get())
           )
        . Form::getCheckbox(Lang::t('_USER_RELEASE'), 'user_release', 'user_release', '1', (isset($metacert['user_release']) ? $metacert['user_release'] : 1))
        . Form::getTextarea(Lang::t('_DESCRIPTION'), 'descr', 'descr', (isset($metacert['description']) ? $metacert['description'] : ''))
        . Form::closeElementSpace()
        . Form::openButtonSpace()
        . Form::getButton('certificate', 'certificate', (isset($metacert) ? Lang::t('_SAVE') : Lang::t('_INSERT')))
        . Form::getButton('undo', 'undo', Lang::t('_UNDO'))
        . Form::closeButtonSpace()
        . Form::closeForm()
        . '</div>'
        );
