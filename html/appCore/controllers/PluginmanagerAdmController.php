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
		if(!Get::cfg('enable_plugins', false)){
			cout("Plugin feature disabled");
			return;
		}
		$model = new PluginAdm();
		$plugins=$model->getInstalledPlugins();
		
		$plugins_info=$model->getPluginsInfo($plugins);
		
		$active_tab = $_GET['active_tab'];
		
		$this->render('show', array(
			'model' => $model,
			'plugins' => $plugins,
			'active_tab' => $active_tab,
			'plugins_info' => $plugins_info)
		);

	}

	public function saveTask() {

		$model = new PluginAdm();

		$active_tab = importVar('active_tab', false, 1);
		if($model->saveElement($active_tab)) {

			Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$active_tab.'&result=ok');
		} else {

			Util::jump_to('index.php?r=adm/pluginmanager/show&active_tab='.$active_tab.'&result=err');
		}

	}

}

?>