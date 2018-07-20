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

require_once(_base_.'/lib/lib.json.php');

class PresenceLmsController extends LmsController
{
	protected $model;
	protected $json;
	protected $permissions;

	protected $id_date;

	public function init()
	{
		$this->id_date = Get::req('id_date', DOTY_INT, 0);
		$this->model = new PresenceLms($_SESSION['idCourse'], $this->id_date);
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view' => true
		);
	}

	protected function _getMessage($code)
	{
		switch($code)
		{
			default:
				$message = "";
			break;
		}

		return $message;
	}

	public function presenceTask()
	{
		require_once(_base_.'/lib/lib.form.php');

		YuiLib::load();

		$user_date = $this->model->getUserDateForCourse(getLogUserId(), $_SESSION['idCourse']);
		$date_info = $this->model->getDateInfoForPublicPresence($user_date);

		if($this->id_date == 0)
			$this->id_date = (isset($date_info[0]['id_date']) ? $date_info[0]['id_date'] : 0);

		$this->model->setIdDate($this->id_date);

		if(isset($_POST['save']))
			$this->model->savePresence();

		foreach($date_info as $info_date)
			$date_for_dropdown[$info_date['id_date']] = $info_date['code'].' - '.$info_date['name'].' ('.Format::date($info_date['date_begin'], 'date').')';

		if($this->id_date == 0)
			$this->render('presence_empty', array());
		else
		{
			$params = array();
			$params['model'] = $this->model;
			$params['dropdown'] = $date_for_dropdown;
			$params['tb'] = $this->model->getPresenceTable();
			$params['test_type'] = $this->model->getTestType();
			$params['date_for_dropdown'] = $date_for_dropdown;
			$this->render('presence', $params);
		}
	}
}
?>