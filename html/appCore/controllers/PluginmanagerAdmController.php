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
    public PluginmanagerAdm $model;

    public function init()
    {
        $this->model = new PluginmanagerAdm();
        
    }

    /**
     * Verifica se l'utente ha i permessi di amministratore
     */
    private function checkAdminAccess(): void
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            exit("You can't access");
        }
    }

    /**
     * Esegue il redirect con il risultato dell'operazione
     */
    private function redirectWithResult(string $plugin, bool $success): void
    {
        $result = $success ? 'ok' : 'err';
        Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab=' . $plugin . '&result=' . $result);
    }

    /**
     * Pulisce la cache delle lingue
     */
    private function clearLangCache(): void
    {
        \FormaLms\lib\Cache\Lang\LangCache::getInstance()->clear();
    }

    public function showTask()
    {
        $this->checkAdminAccess();

        $plugins = $this->model->getPlugins();
        $feedback = '';
        switch ($res = FormaLms\lib\Get::req('result', DOTY_ALPHANUM, '')) {
            case 'ok':
                $feedback = Lang::t('_OPERATION_SUCCESSFUL', 'standard');
                break;
            case 'err':
                $feedback = Lang::t('_OPERATION_FAILURE', 'standard') . PHP_EOL . \FormaLms\lib\Forma::getFormattedErrors(true);
                break;
            default:
        }
        $this->render('show', [
                'plugins' => $plugins,
                'feedback' => $feedback,
                'res' => $res,
            ]
        );
    }

    public function getTableData()
    {
        $this->checkAdminAccess();

        $plugins = $this->model->getPlugins();

        echo \FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode([
            'data' => array_values($plugins),
            'recordsTotal' => count($plugins),
            'recordsFiltered' => count($plugins),
        ], \FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON);
        exit;
    }

    public function install()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->installPlugin($plugin, 0);

        if ($res) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $res);
    }

    public function uninstall()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->uninstallPlugin($plugin);

        if ($res) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $res);
    }

    public function update()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $online = FormaLms\lib\Get::req('online', DOTY_BOOL, false);

        $res = $this->model->updatePlugin($plugin, $online);

        if ($res) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $res);
    }

    public function activate()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->setupPlugin($plugin, 1);

        if ($res) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $res);
    }

    public function deactivate()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $res = $this->model->setupPlugin($plugin, 0);

        if ($res) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $res);
    }

    public function set_priority()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $priority = FormaLms\lib\Get::req('priority', DOTY_INT, 0);
        $res = $this->model->setPriority($plugin, $priority);

        if ($res) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $res);
    }

    public function showSettings()
    {
        $this->checkAdminAccess();

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
        $this->checkAdminAccess();

        $pg_adm = new PluginmanagerAdm();
        $result = $pg_adm->uploadPlugin($_FILES['plugin_file_upload']);

        if ($result) {
            $this->clearLangCache();
        }

        $resultParam = $result ? 'ok' : 'err';
        Util::jump_to('index.php?r=adm/pluginmanager/show&result=' . $resultParam);
    }

    public function purge()
    {
        $this->checkAdminAccess();

        $plugin = FormaLms\lib\Get::req('plugin');
        $pg_adm = new PluginmanagerAdm();
        $result = $pg_adm->delete_files($plugin);

        if ($result) {
            $this->clearLangCache();
        }

        $this->redirectWithResult($plugin, $result);
    }
}