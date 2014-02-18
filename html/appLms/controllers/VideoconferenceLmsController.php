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

class VideoconferenceLmsController extends LmsController {

	public $name = 'videoconference';
	public $model;

	protected $_default_action = 'show';

	public $info = false;

	public function isTabActive($tab_name) {
		
		switch($tab_name) {
			case "live" : {
				if(!$this->info['live']) return false;
			};break;
			case "planned" : {
				if(!$this->info['planned']) return false;
			};break;
			case "history" : {
				if(!$this->info['history']) return false;
			};break;
		}
		return true;
	}

	public function init()
	{
		YuiLib::load('base,tabview');
		Lang::init('course');
		$this->model = new VideoconferenceLms(Docebo::user()->getIdSt());

		$upd = new UpdatesLms();
		$this->info = $upd->videoconferenceCounterUpdates();
	}

	public function showTask() {

		$this->render('_tabs', array());
	}

	public function live()
	{
		$tb = $this->getActiveTable();
		
		$this->render('conference', array('tb' => $tb));
	}

	public function getActiveTable()
	{
		require_once($GLOBALS['where_scs'].'/lib/lib.conference.php');
		$conference_man = new Conference_Manager();

		$conference = $this->model->getActiveConference();
		$course_name = $this->model->getCourseName();

		require_once(_base_.'/lib/lib.table.php');
		$tb = new Table(NULL, Lang::t('_ACTIVE', 'course'), Lang::t('_ACTIVE', 'course'));

		$tb_h = array(	Lang::t('_VIDEOCONFERENCE', 'course'),
						Lang::t('_TYPE', 'course'),
						Lang::t('_NAME', 'course'),
						Lang::t('_START_DATE', 'course'),
						Lang::t('_DATE_END', 'course'),
						Lang::t('_HOURS', 'course'),
						Lang::t('_MAX_PARTICIPANTS', 'conference'),
						'');
		$tb_s = array('','','','','','','','image');

		$tb->setColsStyle($tb_s);
		$tb->addHead($tb_h);

		foreach($conference as $conference_info)
			$tb->addBody(array(	$conference_info['name'],
								$conference_info['room_type'],
								$course_name[$conference_info['idCourse']],
								Format::date(date('Y-m-d H:i:s', $conference_info['starttime']), 'datetime'),
								Format::date(date('Y-m-d H:i:s', $conference_info['endtime']), 'datetime'),
								$conference_info['meetinghours'],
								$conference_info['maxparticipants'],
								$conference_man->getUrl($conference_info['id'],$conference_info['room_type'])));

		return $tb;
	}

	public function planned()
	{
		$tb = $this->getPlannedTable();

		$this->render('conference', array('tb' => $tb));
	}

	public function getPlannedTable()
	{
		$conference = $this->model->getPlannedConference();
		$course_name = $this->model->getCourseName();

		require_once(_base_.'/lib/lib.table.php');
		$tb = new Table(NULL, Lang::t('_PLANNED', 'course'), Lang::t('_ACTIVE', 'course'));

		$tb_h = array(	Lang::t('_VIDEOCONFERENCE', 'course'),
						Lang::t('_TYPE', 'course'),
						Lang::t('_NAME', 'course'),
						Lang::t('_START_DATE', 'course'),
						Lang::t('_DATE_END', 'course'),
						Lang::t('_HOURS', 'course'),
						Lang::t('_MAX_PARTICIPANTS', 'conference'));
		$tb_s = array('','','','','','','');

		$tb->setColsStyle($tb_s);
		$tb->addHead($tb_h);

		foreach($conference as $conference_info)
			$tb->addBody(array(	$conference_info['name'],
								$conference_info['room_type'],
								$course_name[$conference_info['idCourse']],
								Format::date(date('Y-m-d H:i:s', $conference_info['starttime']), 'datetime'),
								Format::date(date('Y-m-d H:i:s', $conference_info['endtime']), 'datetime'),
								$conference_info['meetinghours'],
								$conference_info['maxparticipants']));

		return $tb;
	}

	public function history()
	{
		$tb = $this->getHistoryTable();

		$this->render('conference', array('tb' => $tb));
	}

	public function getHistoryTable()
	{
		$conference = $this->model->getHistoryConference();
		$course_name = $this->model->getCourseName();

		require_once(_base_.'/lib/lib.table.php');
		$tb = new Table(NULL, Lang::t('_HISTORY', 'course'), Lang::t('_ACTIVE', 'course'));

		$tb_h = array(	Lang::t('_VIDEOCONFERENCE', 'course'),
						Lang::t('_TYPE', 'course'),
						Lang::t('_NAME', 'course'),
						Lang::t('_START_DATE', 'course'),
						Lang::t('_DATE_END', 'course'),
						Lang::t('_HOURS', 'course'),
						Lang::t('_MAX_PARTICIPANTS', 'conference'));
		$tb_s = array('','','','','','','');

		$tb->setColsStyle($tb_s);
		$tb->addHead($tb_h);

		foreach($conference as $conference_info)
			$tb->addBody(array(	$conference_info['name'],
								$conference_info['room_type'],
								$course_name[$conference_info['idCourse']],
								Format::date(date('Y-m-d H:i:s', $conference_info['starttime']), 'datetime'),
								Format::date(date('Y-m-d H:i:s', $conference_info['endtime']), 'datetime'),
								$conference_info['meetinghours'],
								$conference_info['maxparticipants']));

		return $tb;
	}

}
