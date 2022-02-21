<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class PluginmanagerAdmController extends AdmController
{
    public function init()
    {
        $this->model = new PluginmanagerAdm();
        $this->json = new Services_JSON();
    }

    public function showTask()
    {
        $plugins = $this->model->getPlugins();
        $feedback = '';
        switch ($res = Get::req('result', DOTY_ALPHANUM, '')) {
            case 'ok': $feedback = Lang::t('_OPERATION_SUCCESSFUL', 'standard'); break;
            case 'err': $feedback = Lang::t('_OPERATION_FAILURE', 'standard') . PHP_EOL . Forma::getFormattedErrors(true); break;
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
        $plugin = Get::req('plugin');
        $res = $this->model->installPlugin($plugin, 0);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function uninstall()
    {
        $plugin = Get::req('plugin');
        $res = $this->model->uninstallPlugin($plugin);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function update()
    {
        $plugin = Get::req('plugin');
        $online = Get::req('online', DOTY_BOOL, false);
        $res = $this->model->updatePlugin($plugin, $online);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function activate()
    {
        $plugin = Get::req('plugin');
        $res = $this->model->setupPlugin($plugin, 1);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function deactivate()
    {
        $plugin = Get::req('plugin');
        $res = $this->model->setupPlugin($plugin, 0);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function set_priority()
    {
        $plugin = Get::req('plugin');
        $priority = Get::req('priority', DOTY_INT, 0);
        $res = $this->model->setPriority($plugin, $priority);
        if ($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }

    public function showSettings()
    {
        $plugin = Get::req('plugin');
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
        $pg_adm = new PluginmanagerAdm();
        if ($pg_adm->uploadPlugin($_FILES['plugin_file_upload'])) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&result=err');
        }
    }

    public function purge()
    {
        $plugin = Get::req('plugin');
        $pg_adm = new PluginmanagerAdm();
        if ($pg_adm->delete_files($plugin)) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=err');
        }
    }
}
