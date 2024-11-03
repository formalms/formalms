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

class PluginmanagerAdmController extends AdmController
{
    public Services_JSON $json;
    public PluginmanagerAdm $model;

    public function init()
    {
        $this->model = new PluginmanagerAdm();
        $this->json = new Services_JSON();
    }

    public function showTask()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugins = $this->model->getPlugins();
        $feedback = '';
        switch ($res = FormaLms\lib\Get::req('result', DOTY_ALPHANUM, '')) {
            case 'ok': $feedback = Lang::t('_OPERATION_SUCCESSFUL', 'standard'); break;
            case 'err': $feedback = Lang::t('_OPERATION_FAILURE', 'standard') . PHP_EOL . \FormaLms\lib\Forma::getFormattedErrors(true); break;
            default:
        }
        $this->render('show', [
                'plugins' => $plugins,
                'feedback' => $feedback,
                'res' => $res,
            ]
        );
    }

    // nuova
    public function getTableData()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }

        $plugins = $this->model->getPlugins();

        echo $this->json->encode([
            'data' => array_values($plugins),
            'recordsTotal' => count($plugins),
            'recordsFiltered' => count($plugins),
        ]);
        exit;
    }

    public function install()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->installPlugin($plugin, 0);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function uninstall()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->uninstallPlugin($plugin);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function update()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $online = FormaLms\lib\Get::req('online', DOTY_BOOL, false);
        $res = $this->model->updatePlugin($plugin, $online);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function activate()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->setupPlugin($plugin, 1);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function deactivate()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->setupPlugin($plugin, 0);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function set_priority()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $priority = FormaLms\lib\Get::req('priority', DOTY_INT, 0);
        $res = $this->model->setPriority($plugin, $priority);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function showSettings()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $settingAdm = new SettingAdm();
        $pg_adm = new PluginmanagerAdm();
        $plugin_info = $pg_adm->getPluginFromDB($plugin, 'name');
        $this->render('show_settings', [
                'setting_adm' => $settingAdm,
                'plugin' => $plugin,
                'regroup' => $plugin_info['regroup'],
            ]
        );
    }

    public function upload()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $pg_adm = new PluginmanagerAdm();
        if ($pg_adm->uploadPlugin($_FILES['plugin_file_upload'])) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&result=err');
        }
    }

    public function purge()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
        
        $plugin = FormaLms\lib\Get::req('plugin');
        $pg_adm = new PluginmanagerAdm();
        if ($pg_adm->delete_files($plugin)) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }
}
