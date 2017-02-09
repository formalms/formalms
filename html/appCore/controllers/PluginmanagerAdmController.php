<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class PluginmanagerAdmController extends AdmController {

    public function showTask() {
        $model = new PluginAdm();
        $plugins = $model->getPlugins();
        $this->render('show', array(
                'model' => $model,
                'plugins' => $plugins
            )
        );
    }

    public function install() {
        $model = new PluginAdm();
        $plugin = Get::req('plugin');
        $res=$model->installPlugin($plugin,0);
        if($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=err');
        }
    }

    public function uninstall() {
        $model = new PluginAdm();
        $plugin = Get::req('plugin');
        $res=$model->uninstallPlugin($plugin);
        if($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=err');
        }
    }

    public function update() {
        $model = new PluginAdm();
        $plugin = Get::req('plugin');
        $online = Get::req('online');
        $res=$model->updatePlugin($plugin,$online);
        if($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=err');
        }
    }

    public function activate() {
        $model = new PluginAdm();
        $plugin = Get::req('plugin');
        $res=$model->setupPlugin($plugin, 1);
        if($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=err');
        }
    }

    public function deactivate() {
        $model = new PluginAdm();
        $plugin = Get::req('plugin');
        $res=$model->setupPlugin($plugin, 0);
        if($res) {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=ok');
        } else {
            Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$plugin.'&result=err');
        }
    }

    public function showSettings(){
        $plugin = Get::req('plugin');
        $settingAdm=new SettingAdm();
        $pg_adm=new PluginAdm();
        $plugin_info=$pg_adm->getPluginFromDB($plugin,'name');
        $this->render('show_settings', array(
                'setting_adm' => $settingAdm,
                'plugin' =>$plugin,
                'regroup' =>$plugin_info['regroup']
            )
        );
    }
}

?>