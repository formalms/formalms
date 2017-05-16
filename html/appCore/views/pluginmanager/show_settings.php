<?php
echo getTitleArea(Lang::t('_PLUGIN_SETTINGS', 'configuration'));
echo getBackUi('index.php?r=adm/pluginmanager/show', Lang::t('_BACK'));
echo Form::openForm('conf_option', 'index.php?r=adm/setting/save&plugin='.$plugin)
    .Form::openElementSpace()
    .Form::getHidden('active_tab', 'active_tab', $regroup);

$setting_adm->printPageWithElement($regroup);

echo Form::closeElementSpace()
    .Form::openButtonSpace()
    .Form::getButton('save_config', 'save_config', Lang::t('_SAVE', 'configuration'))
    .Form::closeButtonSpace()
    .Form::CloseForm();