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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class SettingAdmController extends AdmController
{
    public function showTask()
    {
        switch (FormaLms\lib\Get::req('result', DOTY_ALPHANUM, '')) {
            case 'ok': UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
            case 'err': UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard')); break;
        }

        $model = new SettingAdm();
        $regroup = $model->getRegroupUnit();

        $active_tab = FormaLms\lib\Get::req('active_tab', DOTY_MIXED, 1);
        $this->render('show', [
            'model' => $model,
            'regroup' => $regroup,
            'active_tab' => $active_tab, ]
        );
    }

    public function saveTask()
    {
        $model = new SettingAdm();

        $active_tab = importVar('active_tab', false, 1);
        $plugin = FormaLms\lib\Get::req('plugin');

        if (isset($_POST['undo'])) {
            if ($plugin) {
                Util::jump_to('index.php?r=adm/pluginmanager/showSettings&plugin=' . $plugin);
            } else {
                Util::jump_to('index.php?r=adm/setting/show&active_tab=' . $active_tab);
            }
        } elseif ($model->saveElement($active_tab)) {
            if ($plugin) {
                Util::jump_to('index.php?r=adm/pluginmanager/showSettings&plugin=' . $plugin . '&result=ok');
            } else {
                Util::jump_to('index.php?r=adm/setting/show&active_tab=' . $active_tab . '&result=ok');
            }
        } else {
            if ($plugin) {
                Util::jump_to('index.php?r=adm/pluginmanager/showSettings&plugin=' . $plugin . '&result=err');
            } else {
                Util::jump_to('index.php?r=adm/setting/show&active_tab=' . $active_tab . '&result=err');
            }
        }
    }

    public function clearTwigCache()
    {
        $twigCacheDir = FormaLms\appCore\Template\TwigManager::getCacheDir();

        $this->rrmdir($twigCacheDir);

        Util::jump_to('index.php?r=adm/setting/show&result=ok&active_tab=8');
    }

    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . '/' . $object)) {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
