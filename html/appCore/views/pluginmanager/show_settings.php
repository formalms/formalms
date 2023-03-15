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

echo getTitleArea(Lang::t('_PLUGIN_SETTINGS', 'configuration'));
echo getBackUi('index.php?r=adm/pluginmanager/show', Lang::t('_BACK'));
echo Form::openForm('conf_option', 'index.php?r=adm/setting/save&plugin=' . $plugin)
    . Form::openElementSpace()
    . Form::getHidden('active_tab', 'active_tab', $regroup);

$setting_adm->printPageWithElement($regroup, true);

echo Form::closeElementSpace()
    . Form::openButtonSpace()
    . Form::getButton('save_config', 'save_config', Lang::t('_SAVE', 'configuration'))
    . Form::closeButtonSpace()
    . Form::CloseForm();
