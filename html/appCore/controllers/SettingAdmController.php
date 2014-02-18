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
		if($model->saveElement($active_tab)) {

			Util::jump_to('index.php?r=adm/setting/show&active_tab='.$active_tab.'&result=ok');
		} else {

			Util::jump_to('index.php?r=adm/setting/show&active_tab='.$active_tab.'&result=err');
		}

	}

}

?>