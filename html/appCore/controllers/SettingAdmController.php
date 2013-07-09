<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
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