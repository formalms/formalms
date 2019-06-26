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

class SettingAdmController extends AdmController {

	public function showTask() {
        switch (Get::req('result', DOTY_ALPHANUM, "")) {
            case 'ok': UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
            case 'err': UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard')); break;
        }

		$model = new SettingAdm();
		$regroup = $model->getRegroupUnit();


		$active_tab = Get::req('active_tab', DOTY_MIXED, 1);
		$this->render('show', array(
			'model' => $model,
			'regroup' => $regroup,
			'active_tab' => $active_tab)
		);

	}

	public function saveTask() {

		$model = new SettingAdm();

		$active_tab = importVar('active_tab', false, 1);
        $plugin = Get::req('plugin');

        if ($plugin){

        }
		if($model->saveElement($active_tab)) {
            if ($plugin){
                Util::jump_to('index.php?r=adm/pluginmanager/showSettings&plugin='.$plugin.'&result=ok');
            } else {
                Util::jump_to('index.php?r=adm/setting/show&active_tab='.$active_tab.'&result=ok');
            }
		} else {
            if ($plugin){
                Util::jump_to('index.php?r=adm/pluginmanager/showSettings&plugin='.$plugin.'&result=err');
            } else {
                Util::jump_to('index.php?r=adm/setting/show&active_tab='.$active_tab.'&result=err');
            }
		}

	}

	public function clearTwigCache(){

        $twigCacheDir = \appCore\Template\TwigManager::getCacheDir();

        $this->rrmdir($twigCacheDir);

        Util::jump_to('index.php?r=adm/setting/show&result=ok');
    }

    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this->rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

}

?>
