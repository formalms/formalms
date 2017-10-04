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

// user course subscription

//define("_CUS_CANCELLED",	-4);
//define("_CUS_RESERVED",		-3); //booked, not confirmed by BUYER
define("_CUS_WAITING_LIST",		-2); //_CUS_WAITING_PAYMENT
define("_CUS_CONFIRMED",		-1);
define("_CUS_SUBSCRIBED",		0);
define("_CUS_BEGIN",			1);
define("_CUS_END",				2);
define("_CUS_SUSPEND",			3);
define("_CUS_OVERBOOKING",	4); //the user is overbooked

class CourseSubscribe_Manager
{
	protected $course_table;
	protected $subscribe_table;
	protected $user_table;

	protected $db;
	protected $acl_man;
	protected $lang;

	protected $array_user_status;
	protected $array_user_level;

	public function __construct()
	{
		$this->course_table = $GLOBALS['prefix_lms'].'_course';
		$this->subscribe_table = $GLOBALS['prefix_lms'].'_courseuser';
		$this->user_table = $GLOBALS['prefix_fw'].'_user';

		$this->db = DbConn::getInstance();
		$this->acl_man = $acl_man =& Docebo::user()->getAclManager();
		$this->lang =& DoceboLanguage::CreateInstance('levels', 'lms');
		$this->lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');

		$this->array_user_status = array(
											//-4 => $this->lang->def('_USER_STATUS_CANCELLED'),
											//-3 => '',
											_CUS_WAITING_LIST => $this->lang->def('_WAITING_USERS'),
											_CUS_CONFIRMED => $this->lang->def('_USER_STATUS_CONFIRMED'),
											_CUS_SUBSCRIBED => $this->lang->def('_USER_STATUS_SUBS'),
											_CUS_BEGIN => $this->lang->def('_USER_STATUS_BEGIN'),
											_CUS_END => $this->lang->def('_USER_STATUS_END'),
											_CUS_SUSPEND => $this->lang->def('_USER_STATUS_SUSPEND'),
											_CUS_OVERBOOKING => $this->lang->def('_USER_STATUS_OVERBOOKING')
		);

		$this->array_user_level = array(	1 => $this->lang->def('_LEVEL_1', 'levels', 'lms'),
											2 => $this->lang->def('_LEVEL_2', 'levels', 'lms'),
											3 => $this->lang->def('_LEVEL_3', 'levels', 'lms'),
											4 => $this->lang->def('_LEVEL_4', 'levels', 'lms'),
											5 => $this->lang->def('_LEVEL_5', 'levels', 'lms'),
											6 => $this->lang->def('_LEVEL_6', 'levels', 'lms'),
											7 => $this->lang->def('_LEVEL_7', 'levels', 'lms'));
	}

	/**
	 * Retrive the translation for the status of an user into a course
	 *
	 * @param int $status	The id of the status
	 * @return string The translation for the status
	 */
	public function getUserStatusTr($status)
	{
		return $this->array_user_status[$status];
	}

	/**
	 * Retrive the translation for the level of an user into a course
	 *
	 * @param int $level The id of the level
	 * @return string The translation for the level
	 */
	public function getUserLevelTr($level) {
		return isset($this->array_user_level[$level]) ? $this->array_user_level[$level] : '';
	}

	public function getUserStatus()
	{
		return $this->array_user_status;
	}

	public function getUserLevel()
	{
		return $this->array_user_level;
	}

	/**
	 * Retrive the number of subscribed user of a course
	 *
	 * @param int $id_course The id of the course
	 * @return int The total number of user subscribed to a course
	 */
	public function getTotalUserSubscribed($id_course, $filter = "", $level = false)
	{
		if (is_array($filter)) {
			$query =	"SELECT COUNT(*)"
					." FROM ".$this->subscribe_table." as s JOIN ".$this->user_table." as u "
					." ON (s.idUser = u.idst) "
					." WHERE idCourse = ".(int)$id_course;

			if (isset($filter['text']) && $filter['text'] != "")
				$query .= " AND (u.userid LIKE '%".$filter['text']."%' OR u.firstname LIKE '%".$filter['text']."%' OR u.lastname LIKE '%".$filter['text']."%') ";

			$arr_idst = array();
			if (isset($filter['orgchart']) && $filter['orgchart']>0) {
				$umodel = new UsermanagementAdm();
				$use_desc = (isset($filter['descendants']) && $filter['descendants']);
				$ulist = $umodel->getFolderUsers($filter['orgchart'], $use_desc);
				if (!empty($ulist)) $arr_idst = $ulist;
				unset($ulist);
			}
			if (!empty($arr_idst)) $conditions[] = " AND u.idst IN (".implode(",", $arr_idst).") ";

			if (isset($filter['date_valid']) && strlen($filter['date_valid']) >= 10) {
				$query .= " AND (s.date_begin_validity <= '".$filter['date_valid']."' OR s.date_begin_validity IS NULL OR s.date_begin_validity='0000-00-00 00:00:00') ";
				$query .= " AND (s.date_expire_validity >= '".$filter['date_valid']."' OR s.date_expire_validity IS NULL OR s.date_expire_validity='0000-00-00 00:00:00') ";
			}

			if (isset($filter['show'])) {
				//validate values
				switch ($filter['show']) {
					case 0: { //all
						//no condition to check ...
					} break;

					case 1: { //expired
						$query .= " AND (s.date_expire_validity IS NOT NULL AND s.date_expire_validity < NOW())";
					} break;

					case 2: { //not expired with expiring date
						$query .= " AND (s.date_expire_validity IS NOT NULL AND s.date_expire_validity > NOW())";
					} break;

					case 3: { //not expired without expiring date
						$query .= " AND (s.date_expire IS NULL OR s.date_expire='' OR s.date_expire='0000-00-00 00:00:00') ";
					} break;

					default: {
						//all ...
					} break;
				}
			}

		} else {
			$query =	"SELECT COUNT(*)"
					." FROM ".$this->subscribe_table." AS s "
					." WHERE s.idCourse = ".(int)$id_course;
		}

		$waiting = (is_array($filter) && isset($filter['waiting']) && $filter['waiting']);
		$query .= " AND s.waiting = '".($waiting ? "1" : "0")."' ";

		if ($level && (int)$level>0) {
			$query .= " AND s.level=".(int)$level;
		}

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .=	" AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
		}

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getCourseSubscription($id_course, $start_index = false, $results = false, $sort = false, $dir = false, $filter = false)
	{
		require_once(_base_.'/lib/lib.form.php');

		$query =	"SELECT u.idst, u.userid, u.firstname, u.lastname, s.level, s.status, s.date_complete, s.date_begin_validity, s.date_expire_validity, s.waiting "
					." FROM ".$this->subscribe_table." AS s"
					." JOIN ".$this->user_table." AS u ON s.idUser = u.idst"
					." WHERE s.idCourse = ".$id_course;

		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != "")
				$query .= " AND (u.userid LIKE '%".$filter['text']."%' OR u.firstname LIKE '%".$filter['text']."%' OR u.lastname LIKE '%".$filter['text']."%') ";

			$arr_idst = array();
			if (isset($filter['orgchart']) && $filter['orgchart']>0) {
				$umodel = new UsermanagementAdm();
				$use_desc = (isset($filter['descendants']) && $filter['descendants']);
				$ulist = $umodel->getFolderUsers($filter['orgchart'], $use_desc);
				if (!empty($ulist)) $arr_idst = $ulist;
				unset($ulist);
			}

			if (!empty($arr_idst)) $query .= " AND u.idst IN (".implode(",", $arr_idst).") ";

			if (isset($filter['date_valid']) && strlen($filter['date_valid']) >= 10) {
				//$query .= " AND (s.date_begin_validity <= '".$filter['date_valid']."' OR s.date_begin_validity IS NULL OR s.date_begin_validity='0000-00-00 00:00:00') ";
				//$query .= " AND (s.date_expire_validity >= '".$filter['date_valid']."' OR s.date_expire_validity IS NULL OR s.date_expire_validity='0000-00-00 00:00:00') ";
				$time_validity_date = strtotime($filter['date_valid']);
				$validity_date = date('Y-m-d H:i:s', $time_validity_date);
				$query .= " AND (s.date_begin_validity <= '".$validity_date."' OR s.date_begin_validity IS NULL OR s.date_begin_validity='0000-00-00 00:00:00') ";
				$query .= " AND (s.date_expire_validity >= '".$validity_date."' OR s.date_expire_validity IS NULL OR s.date_expire_validity='0000-00-00 00:00:00') ";
			}

			if (isset($filter['show'])) {
				//validate values
				switch ($filter['show']) {
					case 0: { //all
						//no condition to check ...
					} break;

					case 1: { //expired
						$query .= " AND (s.date_expire_validity IS NOT NULL AND s.date_expire_validity < NOW())";
					} break;

					case 2: { //not expired with expiring date
						$query .= " AND (s.date_expire_validity IS NOT NULL AND s.date_expire_validity > NOW())";
					} break;

					case 3: { //not expired without expiring date
						$query .= " AND (s.date_expire_validity IS NULL OR s.date_expire_validity='' OR s.date_expire_validity='0000-00-00 00:00:00') ";
					} break;

					default: {
						//all ...
					} break;
				}
			}
		}

		$waiting = (is_array($filter) && isset($filter['waiting']) && $filter['waiting']);
		$query .= " AND s.waiting = '".($waiting ? "1" : "0")."' ";

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .=	" AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
		}

		switch($sort)
		{
			case 'userid':
				$query .= " ORDER BY u.userid ".$dir;
			break;

			case 'fullname':
				$query .= " ORDER BY u.firstname ".$dir.", u.lastname ".$dir.", u.userid ".$dir;
			break;

			case 'level':
				$query .= " ORDER BY s.level ".$dir.", u.userid ".$dir;
			break;

			case 'status':
				$query .= " ORDER BY s.status ".$dir.", u.userid ".$dir;
			break;
		}

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);
		$res = array();

		while(list($id_user, $userid, $firstname, $lastname, $level, $status, $date_complete, $date_begin_validity, $date_expire_validity, $waiting) = sql_fetch_row($result))
		{
			if($firstname !== '' && $lastname !== '')
				$user = $firstname.' '.$lastname;
			elseif($firstname !== '')
				$user = $firstname;
			elseif($lastname !== '')
				$user = $lastname;
			else
				$user = '';

			$res[] = array(	'sel' => '',
							'id_user' => $id_user,
							'userid' => $this->acl_man->relativeId($userid),
							'fullname' => $user,
							'level' => $this->getUserLevelTr($level),
							'status' => $this->getUserStatusTr($status),
							'level_id' => $level,
							'status_id' => $status,
							'date_complete' => $date_complete,
							'date_begin_validity' => $date_begin_validity,
							'date_expire_validity' => $date_expire_validity,
							'waiting' => $waiting > 0,
							'del' => 'ajax.adm_server.php?r=alms/subscription/delPopUp&id_course='.$id_course.'&id_user='.$id_user);
		}

		return $res;
	}

	public function getCourseSubscribedUserIdst($id_course, $no_flat = false, $filter = '')
	{
		if (is_numeric($id_course))
			$arr = array($id_course);
		elseif (is_array($id_course) && count($id_course)>0)
			$arr =& $id_course;
		else
			return false;

		$query =	"SELECT s.idUser, s.idCourse "
					." FROM ".$this->subscribe_table." AS s "
					." JOIN ".$this->user_table." AS u ON u.idst = s.idUser "
					." WHERE s.idCourse IN (".implode(",", $arr).") ";

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .=	" AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 's.idUser');
		}

		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != "")
				$query .= " AND (u.userid LIKE '%".$filter['text']."%' OR u.firstname LIKE '%".$filter['text']."%' OR u.lastname LIKE '%".$filter['text']."%') ";

			$arr_idst = array();
			if (isset($filter['orgchart']) && $filter['orgchart']>0) {
				$umodel = new UsermanagementAdm();
				$use_desc = (isset($filter['descendants']) && $filter['descendants']);
				$ulist = $umodel->getFolderUsers($filter['orgchart'], $use_desc);
				if (!empty($ulist)) $arr_idst = $ulist;
				unset($ulist);
			}
			if (!empty($arr_idst)) $conditions[] = " AND u.idst IN (".implode(",", $arr_idst).") ";

			if (isset($filter['date_valid']) && strlen($filter['date_valid']) >= 10) {
				$query .= " AND (s.date_begin_validity <= '".$filter['date_valid']."' OR s.date_begin_validity IS NULL OR s.date_begin_validity='0000-00-00 00:00:00') ";
				$query .= " AND (s.date_expire_validity >= '".$filter['date_valid']."' OR s.date_expire_validity IS NULL OR s.date_expire_validity='0000-00-00 00:00:00') ";
			}
		}

		$result = $this->db->query($query);
		$res = array();

		while (list($id_user, $id_course) = $this->db->fetch_row($result)) {
			if ($no_flat) {
				$res[$id_course][$id_user] = $id_user;
			} else {
				$res[$id_user] = (int)$id_user;
			}
		}
		if (!$no_flat) $res = array_unique($res);

		return $res;
	}

	public function subscribeUserToCourse($id_user, $id_course, $level = 3, $waiting = 0, $date_begin_validity = false, $date_expire_validity = false)
	{
		if($this->controlSubscription($id_user, $id_course))
			return true;

		$query =	"INSERT INTO ".$this->subscribe_table
					." (idUser, idCourse, level, waiting, subscribed_by, date_inscr"
					.($date_begin_validity ? ", date_begin_validity" : "")
					.($date_expire_validity ? ", date_expire_validity" : "")
					.")"
					." VALUES ('".$id_user."', '".$id_course."', '".$level."', '".(int)$waiting."', '".getLogUserId()."', '".date('Y-m.d H:i:s')."'"
					.($date_begin_validity ? ", '".substr($date_begin_validity, 0, 10)."'" : "")
					.($date_expire_validity ? ", '".substr($date_expire_validity, 0, 10)."'" : "")
					.")";

		return sql_query($query);
	}

	public function delUserFromCourse($id_user, $id_course)
	{
		$query =	"DELETE FROM ".$this->subscribe_table
					." WHERE idCourse = ".$id_course
					." AND idUser = ".$id_user;

		return sql_query($query);
	}

	public function getUserLeveInCourse($id_user, $id_course)
	{
		$query =	"SELECT level"
					." FROM ".$this->subscribe_table
					." WHERE idUser = ".$id_user
					." AND idCourse = ".$id_course;

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function updateUserLeveInCourse($id_user, $id_course, $new_level) {
		$query_lvl =	"SELECT idUser, level"
					." FROM ".$this->subscribe_table
					." WHERE idCourse = ".(int)$id_course
					." AND idUser ";

		$query =	"UPDATE ".$this->subscribe_table
					." SET level = ".(int)$new_level
					." WHERE idCourse = ".(int)$id_course
					." AND idUser ";

		if (is_array($id_user)) {
			$query .= " IN (".implode(',', $id_user).")";
			$query_lvl .= " IN (".implode(',', $id_user).")";
		} else {
			$query .= " = ".(int)$id_user;
			$query_lvl .= " = ".(int)$id_user;
			$id_user = array($id_user);
		}

		$result = $this->db->query($query);
		$old_level = array();

		while(list($id_user_t, $level) = $this->db->fetch_row($result))
			   $old_level[$id_user_t] = $level;

		$res = $this->db->query($query);

		if($res)
		{
			require_once(_lms_ . '/lib/lib.course.php');

			$docebo_course = new DoceboCourse($id_course);

			$level_idst = & $docebo_course->getCourseLevel($id_course);
			if(count($level_idst) == 0 || $level_idst[1] == '')
				$level_idst = & $docebo_course->createCourseLevel($id_course);

			foreach($id_user as $id_user_t)
			{
				$this->acl_man->removeFromGroup($level_idst[$old_level[$id_user]], $id_user_t);
				$this->acl_man->addToGroup($level_idst[$new_level], $id_user_t);
			}
		}

		return $res;
	}

	public function updateUserStatusInCourse($id_user, $id_course, $new_status, $new_date_complete = "") {
		$_new_date = $new_date_complete ? "'".$new_date_complete."'" : "NOW()";
		$query = "UPDATE ".$this->subscribe_table
					." SET status = ".(int)$new_status
					.", waiting = ".($new_status < 0 ? "1" : "0" )." "
					.", date_complete = ".($new_status == _CUS_END ? $_new_date : " null")
					." WHERE idCourse = ".$id_course
					." AND idUser ";

		if (is_array($id_user)) {
			$query .= " IN (".implode(',', $id_user).")";
		} else {
			$query .= " = ".(int)$id_user;
		}

		if ($this->db->query($query)) {
			if($new_status == _CUS_END) {
				//require_once($GLOBALS['where_lms'].'/lib/lib.competences.php');
				//$cman = new Competences_Manager();
				$cmodel = new CompetencesAdm();
				$list = is_array($id_user) ? $id_user : array((int)$id_user);
				foreach ($list as $idst_user) {
					$res1 = $cmodel->assignCourseCompetencesToUser($id_course, $id_user);//$cman->AssignCourseCompetencesToUser($id_course, $id_user);
					$res2 = $this->saveTrackStatusChange($id_user, $id_course, $new_status);
					//TO DO: check if all users are been tracked  and had competences assigned
				}
				return true; //this should be in dependance with above results
			}	else //no 'completed' status --> just return with "ok"
				return true;
		}	else //query error
			return false;
	}


	public function updateUserDateCompleteInCourse($id_user, $id_course, $new_date_complete) {
		$_new_date = $new_date_complete ? "'".$new_date_complete."'" : "NULL";
		$query =	"UPDATE ".$this->subscribe_table
					." SET date_complete = ".$_new_date
					." WHERE idCourse = ".(int)$id_course
					." AND status = "._CUS_END
					." AND idUser ";

		if (is_array($id_user)) {
			$query .= " IN (".implode(',', $id_user).")";
		} else {
			$query .= " = ".(int)$id_user;
		}

		return $this->db->query($query);
	}

	public function updateUserDateBeginValidityInCourse($id_user, $id_course, $new_date_begin) {
		$_new_date = $new_date_begin ? "'".$new_date_begin."'" : "NULL";
		$query =	"UPDATE ".$this->subscribe_table
					." SET date_begin_validity = ".$_new_date
					." WHERE idCourse = ".(int)$id_course
					." AND idUser ";

		if (is_array($id_user)) {
			$query .= " IN (".implode(',', $id_user).")";
		} else {
			$query .= " = ".(int)$id_user;
		}

		return $this->db->query($query);
	}
	
	public function updateUserDateExpireValidityInCourse($id_user, $id_course, $new_date_expire) {
		$_new_date = $new_date_expire ? "'".$new_date_expire."'" : "NULL";
		$query =	"UPDATE ".$this->subscribe_table
					." SET date_expire_validity = ".$_new_date
					." WHERE idCourse = ".(int)$id_course
					." AND idUser ";

		if (is_array($id_user)) {
			$query .= " IN (".implode(',', $id_user).")";
		} else {
			$query .= " = ".(int)$id_user;
		}

		return $this->db->query($query);
	}

	
	public function resetValidityDateBegin($id_course, $id_edition, $id_user) {
		if ($id_course <= 0 || $id_user <= 0) return false;

		$query = "UPDATE %lms_courseuser SET date_begin_validity = NULL "
			." WHERE idCourse = ".(int)$id_course
			.((int)$id_edition > 0 ? " AND edition_id = ".(int)$id_edition : "");

			if (is_array($id_user)) {
				$query .= " AND idUser IN (".implode(',', $id_user).")";
			} else {
				$query .= " AND idUser = ".(int)$id_user;
			}
		$res = sql_query($query);

		return $res ? true : false;
	}

	
	public function resetValidityDateExpire($id_course, $id_edition, $id_user) {
		if ($id_course <= 0 || $id_user <= 0) return false;

		$query = "UPDATE %lms_courseuser SET date_expire_validity = NULL "
			." WHERE idCourse = ".(int)$id_course
			.((int)$id_edition > 0 ? " AND edition_id = ".(int)$id_edition : "");

		if (is_array($id_user)) {
			$query .= "  AND idUser IN (".implode(',', $id_user).")";
		} else {
			$query .= "  AND idUser = ".(int)$id_user;
		}
		$res = sql_query($query);

		return $res ? true : false;
	}
	

	public function saveTrackStatusChange($idUser, $idCourse, $status)
	{
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

		list($prev_status) = sql_fetch_row(sql_query("
		SELECT status
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".(int)$idUser."' AND idCourse = '".(int)$idCourse."'"));

		$extra = '';
		if($prev_status != $status) {
			switch($status) {
				case _CUS_SUBSCRIBED : {
					//approved subscriptin for example
					$extra = ", date_inscr = NOW()";
				};break;
				case _CUS_BEGIN : {
					//first access
					UpdatesLms::resetCache();
					$extra = ", date_first_access = NOW()";
				};break;
				case _CUS_END : {
					//end course
					$extra = ", date_complete = NOW()";
				};break;
			}
		}

		if(!sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_courseuser
		SET status = '".(int)$status."' ".$extra."
		WHERE idUser = '".(int)$idUser."' AND idCourse = '".(int)$idCourse."'")) return false;

		$re = sql_query("
		SELECT when_do
		FROM ".$GLOBALS['prefix_lms']."_statuschangelog
		WHERE status_user = '".(int)$status."' AND
			idUser = '".(int)$idUser."' AND
			idCourse = '".(int)$idCourse."'");

		if( sql_num_rows($re) ) {
			sql_query("
			UPDATE ".$GLOBALS['prefix_lms']."_statuschangelog
			SET when_do = NOW()
			WHERE status_user = '".(int)$status."' AND
				idUser = '".(int)$idUser."' AND
				idCourse = '".(int)$idCourse."'");

		} else {
			sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']."_statuschangelog
			SET status_user = '".(int)$status."',
				idUser = '".(int)$idUser."',
				idCourse = '".(int)$idCourse."',
				when_do = NOW()");
		}

		if($prev_status != $status && $status == _CUS_END) {
			// send alert

			if(!sql_num_rows($re)) {
				/*
				//add course's competences scores to user
				require_once($GLOBALS['where_lms'].'/lib/lib.competences.php');
				$competences_man = new Competences_Manager();
				$competences_man->AssignCourseCompetencesToUser($idCourse, $idUser);
				 */
			}

			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
			require_once(_base_.'/lib/lib.eventmanager.php');


			$teachers = Man_Course::getIdUserOfLevel($idCourse, '6');
			$cd = new DoceboCourse($idCourse);
			$acl_man =& Docebo::user()->getAclManager();

			$array_subst = array(
				'[user]' => $acl_man->getUserName($idUser),
				'[course]' => $cd->getValue('name')
			);

			$msg_composer = new EventMessageComposer();

			$msg_composer->setSubjectLangText('email', '_USER_END_COURSE_SBJ', false);
			$msg_composer->setBodyLangText('email', '_USER_END_COURSE_TEXT', $array_subst);

			$msg_composer->setBodyLangText('sms', '_USER_END_COURSE_TEXT_SMS', $array_subst);

			// send message to the user subscribed
			createNewAlert(	'UserCourseEnded',
							'status',
							'modify',
							'1',
							'User end course',
							$teachers,
							$msg_composer );

			//add course's competences scores to user
			/*
			require_once($GLOBALS['where_lms'].'/lib/lib.competences.php');
			$competences_man = new Competences_Manager();
			$competences_man->AssignCourseCompetencesToUser($idCourse, $idUser);
			*/

			//increment coursecompleted if this course is in a coursepath
			require_once(_lms_.'/lib/lib.coursepath.php');
			$cpmodel = new CoursePath_Manager();
			$cpmodel->assignComplete($idCourse, $idUser);
		}
		return true;
	}

	public function controlSubscription($id_user, $id_course)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->subscribe_table
					." WHERE idUser = ".(int)$id_user
					." AND idCourse = ".(int)$id_course;

		list($res) = sql_fetch_row(sql_query($query));

		if($res == 1)
			return true;
		return false;
	}

	public function updateForNewDateSubscribe($id_user, $id_course, $waiting)
	{
		$query =	"UPDATE ".$this->subscribe_table
					." SET waiting = ".$waiting.","
					." status = 0"
					." WHERE idCourse = ".$id_course
					." AND idUser = ".$id_user;

		$result = sql_query($query);
	}
}

class CourseSubscribe_Management {

	var $course_man;

	var $acl;

	var $acl_man;
	var $db =NULL;

	function CourseSubscribe_Management() {

		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');

		$this->course_man 	= new Man_Course();
		$this->acl 			=& Docebo::user()->getAcl();
		$this->acl_man 		=& Docebo::user()->getAclManager();
		$this->db = DbConn::getInstance();
	}


	function _query( $query ) {
		return $this->db->query($query);
	}


	function _insQuery( $query ) {

		if(!$this->db->query( $query )) return FALSE;
		return $this->db->insert_id();
	}


	/**
	 * Subscribe a group of users(N) to a group of courses(N)
	 * @param array 	$arr_user 		the id of the users
	 * @param array 	$arr_course 	the id of the courses
	 * @param mixed 	$levels 		a matrix defined in this way
	 *									array( id_course => array( id_user => lv_number, ... ), ... )
	 *									or else a level_number that is used for all the users
	 *
	 * @return bool true if success, false otherwise
	 */
	function multipleSubscribe($arr_users, $arr_courses, $levels, $id_log = false) {

		$re = true;
		while(list(, $id_course) = each($arr_courses)) {

			$re &= $this->subscribeUsers($arr_users , $id_course, ( is_array($levels) ? $levels[$id_course] : $levels ), $id_log);
		}
		return $re;
	}

	/**
	 * Subscribe a user(1) to a group of courses(N)
	 * @param int 		$id_user 		the id of the users
	 * @param array 	$arr_course 	the id of the courses
	 * @param mixed 	$levels 		a matrix defined in this way
	 *									array( id_course => lv_number, ... )
	 *									or else a level_number that is used for all the users
	 *
	 * @return bool true if success, false otherwise
	 */
	function multipleUserSubscribe($id_user, $arr_courses, $levels, $id_log = false) {

		if(empty($arr_courses)) return true;

		while(list(, $id_course) = each($arr_courses)) {

			$re = true;

			$group_levels 	=& $this->course_man->getCourseIdstGroupLevel($id_course);
			$user_level 	= $this->course_man->getLevelsOfUsers($id_course, array($id_user));

			$lv = ( is_array($levels) ? $levels[$id_course] : $levels );

			if(!isset($user_level[$id_user])) {

				$this->acl_man->addToGroup($group_levels[$lv], $id_user);

				$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
				( idUser, idCourse, level, waiting, subscribed_by, date_inscr, rule_log ) VALUES
				( '".$id_user."', '".$id_course."', '".$lv."', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."', ".( $id_log ? (int)$id_log : 'NULL' )." )";

				$re &= $this->_query($query);
			} elseif($user_level[$id_user] != $lv) {

				$old_lv = $user_level[$id_user];

				$this->acl_man->removeFromGroup($group_levels[$old_lv], $id_user);
				$this->acl_man->addToGroup($group_levels[$lv], $id_user);

				$query = "
				UPDATE ".$GLOBALS['prefix_lms']."_courseuser
				SET level = '".$lv."'
				WHERE idUser = '".$id_user."' AND
						idCourse = '".$id_course."'";
				$re &= $this->_query($query);
			}

		}
		return $re;
	}

	/**
	 * Subscribe a group of users(N) to a course(1)
	 * @param array 	$arr_user 	the id of the users
	 * @param int 		$id_course 	the id of the course
	 * @param mixed 	$levels 		a array defined in this way
	 *									array( id_user => lv_number, ... )
	 *									or else a level_number that is used for all the users
	 *
	 * @return bool true if success, false otherwise
	 */
	function subscribeUsers($arr_users, $id_course, $levels, $id_log = false) {

		if(empty($arr_users)) return true;

		$re = true;
		$group_levels 	=& $this->course_man->getCourseIdstGroupLevel($id_course);
		$user_level 	= $this->course_man->getLevelsOfUsers($id_course, $arr_users);

		while(list(, $id_user) = each($arr_users)) {

			$lv = ( is_array($levels) ? $levels[$id_user] : $levels );
			if(!isset($user_level[$id_user])) {

				$this->acl_man->addToGroup($group_levels[$lv], $id_user);

				$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
				( idUser, idCourse, level, waiting, subscribed_by, date_inscr, rule_log ) VALUES
				( '".$id_user."', '".$id_course."', '".$lv."', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."', ".( $id_log ? (int)$id_log : 'NULL' )." )";
				$re &= $this->_query($query);
			} else {

				$old_lv = $user_level[$id_user];

				$this->acl_man->removeFromGroup($group_levels[$old_lv], $id_user);
				$this->acl_man->addToGroup($group_levels[$lv], $id_user);

				$query = "
				UPDATE ".$GLOBALS['prefix_lms']."_courseuser
				SET level = '".$lv."'
				WHERE idUser = '".$id_user."' AND idCourse = '".$id_course."'";
				$re &= $this->_query($query);
			}
		}

		return $re;
	}

	/**
	 * Subscribe a user(1) to a course(1)
	 * @param int 	$id_user 		the id of the user
	 * @param int 	$id_course 		the id of the course
	 * @param int 	$level_number 	the level number of the user
	 *
	 * @return bool true if success, false otherwise
	 */
	function subscribeUser($id_user, $id_course, $level_number) {

		return $this->subscribeUsers(array($id_user), $id_course, $level_number);
	}

	function retriveLogSubscriptionInfo($id_log) {
		$data = $this->db->query("SELECT u.userid, u.lastname, u.firstname, c.code, c.name ".
		"FROM ( %lms_course AS c JOIN %lms_courseuser AS cu ON ( c.idCourse = cu.idCourse ) )".
		"	JOIN %adm_user AS u ON ( u.idst = cu.idUser )".
		"WHERE rule_log = ".(int)$id_log." ".
		"ORDER BY u.userid, c.code");
		$logs = array();
		while($obj = $this->db->fetch_obj($data)) {

			$logs[] = $obj;
		}
		return $logs;
	}

	function removeRuleLogSubscription($id_log) {

		$users = array();
		$courses = array();
		$re = $this->db->query("SELECT idUser, idCourse FROM %lms_courseuser WHERE rule_log = ".(int)$id_log." ");
		while($obj = $this->db->fetch_obj($re)) {

			$users[] = $obj->idUser;
			$courses[] = $obj->idCourse;
		}
		return $this->multipleUnsubscribe($users, $courses);
	}

	/**
	 * Unsubscribe a group of users to a course
	 * @param array 	$arr_user 		the id of the users
	 * @param array 	$arr_course 	the id of the courses
	 *
	 * @return bool true if success, false otherwise
	 */
	function multipleUnsubscribe($arr_users, $arr_courses) {

		$re = true;
		while(list(, $id_course) = each($arr_courses)) {

			$re &= $this->unsubscribeUsers($arr_users , $id_course);
		}
		return $re;
	}

	/**
	 * Unsubscribe a group of users to a course
	 * @param array 	$arr_user 	the id of the users
	 * @param int 		$id_course 	the id of the course
	 *
	 * @return bool true if success, false otherwise
	 */
	function unsubscribeUsers($arr_users, $id_course) {

		if(empty($arr_users)) return true;

		$group_levels =& $this->course_man->getCourseIdstGroupLevel($id_course);
		$user_level = $this->course_man->getLevelsOfUsers($id_course, $arr_users);

		while(list(, $id_user) = each($arr_users)) {

			if(isset($user_level[$id_user])) {
				$lv = $user_level[$id_user];
				$this->acl_man->removeFromGroup($group_levels[$lv], $id_user);
			}
		}
		$re = $this->_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser IN ( ".implode(',', $arr_users)." ) AND idCourse = '".$id_course."'");

		return $re;
	}

	/**
	 * Unsubscribe a user to a course
	 * @param int 	$id_user 	the id of the user
	 * @param int 	$id_course 	the id of the course
	 *
	 * @return bool true if success, false otherwise
	 */
	function unsubscribeUser($id_user, $id_course) {

		return $this->unsubscribeUsers(array($id_user), $id_course);
	}

	/**
	 * Unsubscribe a user from all the courses
	 * @param int 	$id_user 	the id of the user
	 *
	 * @return bool true if success, false otherwise
	 */
	function unsubscribeUserFromAllCourses($id_user) {

		$re = $this->_query("
		SELECT idCourse
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".$id_user."'");

		$res = true;
		while(list($id_course) = sql_fetch_row($re)) {

			$res &= $this->unsubscribeUsers(array($id_user), $id_course);
		}
		return $res;
	}

	/**
	 * Suspend a user from a course
	 * @param int 	$id_user 	the id of the user
	 *
	 * @return bool true if success, false otherwise
	 */
	function suspendUser($id_user, $id_course) {

		require_once( $GLOBALS['where_lms'] . '/lib/lib.course.php' );

		$re = $this->_query("
		UPDATE ".$GLOBALS['prefix_lms']."_courseuser
		SET status = '"._CUS_SUSPEND."'
		WHERE idUser = '".$id_user."' AND idCourse = '".$id_course."'");

		return $re;
	}

	// if there is edition ----------------------------------------------------------

	/**
	 * Subscribe a group of users(N) to a course edition(1)
	 * @param array 	$arr_user 	the id of the users
	 * @param int 		$id_course 	the id of the course
	 * @param mixed 	$levels 		a array defined in this way
	 *									array( id_user => lv_number, ... )
	 *									or else a level_number that is used for all the users
	 *
	 * @return bool true if success, false otherwise
	 */
	function subscribeEditionUsers($arr_users, $id_edition, $levels, $id_course = false) {

		if(empty($arr_users)) return true;
		if($id_course == false) {

			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

			$man = new Man_Course();
			$info = $man->getEditionInfo($id_edition);
			$id_course = $info['idCourse'];
		}

		$re = true;
		$acl_man =& Docebo::user()->getAclManager();
		$group_levels 	=& $this->course_man->getCourseIdstGroupLevel($id_course);
		$user_level 	= $this->course_man->getLevelsOfUsers($id_course, $arr_users);

		$edition_group 	= $acl_man->getGroupST('/lms/course_edition/'.$id_edition.'/subscribed');
		if($edition_group === FALSE) {
			$edition_group = $acl_man->registerGroup('/lms/course_edition/'.$id_edition.'/subscribed', 'all the user of a course edition', true, "course");
		}
		while(list(, $id_user) = each($arr_users)) {

			$lv = ( is_array($levels) ? $levels[$id_user] : $levels );
			if(!isset($user_level[$id_user])) {

				$this->acl_man->addToGroup($group_levels[$lv], $id_user);
				$this->acl_man->addToGroup($edition_group, $id_user);

				$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
				( idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr ) VALUES
				( '".$id_user."', '".$id_course."', '".$id_edition."', '".$lv."', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."' )";

				$re &= $this->_query($query);
			} else {

				$old_lv = $user_level[$id_user];

				$this->acl_man->removeFromGroup($group_levels[$old_lv], $id_user);
				$this->acl_man->addToGroup($group_levels[$lv], $id_user);

				$query = "
				UPDATE ".$GLOBALS['prefix_lms']."_courseuser
				SET level = '".$lv."'
				WHERE idUser = '".$id_user."'
					AND idCourse = '".$id_course."'
					AND edition_id = '".$id_edition."'";
				$re &= $this->_query($query);
			}
		}

		return $re;
	}


	// special subscribe for connector ----------------------------------------------

	/**
	 * Subscribe a user(1) to a course(1), connection control
	 * @param int 		$id_user 	the id of the users
	 * @param int 		$id_course 	the id of the course
	 * @param int 		$level 		the level
	 *
	 * @return bool true if success, false otherwise
	 */
	function subscribeUserWithConnection($id_user, $id_course, $level, $connection, $date = false) {

		$query_courseuser = "
		SELECT idUser, level, imported_from_connection
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idCourse = '".$id_course."' AND idUser = '".$id_user."'";
		$re_courseuser = $this->_query($query_courseuser);

		$re = true;
		$group_levels 	=& $this->course_man->getCourseIdstGroupLevel($id_course);

		if(!sql_num_rows($re_courseuser)) {

			$this->acl_man->addToGroup($group_levels[$level], $id_user);

			$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
			( idUser, idCourse, level, waiting, subscribed_by, date_inscr, imported_from_connection ) VALUES
			( '".$id_user."', '".$id_course."', '".$level."', '0', '".getLogUserId()."', '".($date ? $date : date("Y-m-d H:i:s"))."', '".$connection."' )";

			$re &= $this->_query($query);
		} else {

			list($id_user, $old_lv, $import_from) = sql_fetch_row($re_courseuser);
			if($import_from !== $connection) return 'jump';

			if($old_lv != $level) {

				$this->acl_man->removeFromGroup($group_levels[$old_lv], $id_user);
				$this->acl_man->addToGroup($group_levels[$level], $id_user);
			}
			$query = "
			UPDATE ".$GLOBALS['prefix_lms']."_courseuser
			SET level = '".$level."'
			WHERE idUser = '".$id_user."' AND
					idCourse = '".$id_course."'";
			$re &= $this->_query($query);
		}

		return $re;
	}

	/**
	 * Suspend a user from a course
	 * @param int 	$id_user 	the id of the user
	 *
	 * @return bool true if success, false otherwise
	 */
	function suspendUserWithConnection($id_user, $id_course, $connection) {

		require_once( $GLOBALS['where_lms'] . '/lib/lib.course.php' );

		$re = $this->_query("
		UPDATE ".$GLOBALS['prefix_lms']."_courseuser
		SET status = '"._CUS_SUSPEND."'
		WHERE idUser = '".$id_user."'
			AND idCourse = '".$id_course."'
			AND imported_from_connection = '".$connection."'");
		return $re;
	}


	/**
	 * Unsubscribe a user from a course
	 * @param array 	$arr_user 	the id of the users
	 * @param int 		$id_course 	the id of the course
	 *
	 * @return bool true if success, false otherwise
	 */
	function unsubscribeUserWithConnection($id_user, $id_course, $connection) {

		$group_levels =& $this->course_man->getCourseIdstGroupLevel($id_course);


		$query_courseuser = "
		SELECT idUser, level, imported_from_connection
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idCourse = '".$id_course."' AND idUser = '".$id_user."'";
		$re_courseuser = $this->_query($query_courseuser);

		list($id_user, $level, $import_from) = sql_fetch_row($re_courseuser);
		if($import_from == $connection) return 'jump';

		$this->acl_man->removeFromGroup($group_levels[$level], $id_user);

		$re = $this->_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".$id_user."' AND idCourse = '".$id_course."'");

		return $re;
	}


	/**
	 * Unsubscribe a group of users to a course
	 * @param array 	$arr_user 	the id of the users
	 * @param int 		$id_course 	the id of the course
	 *
	 * @return bool true if success, false otherwise
	 */
	function unsubscribeUsersEd($arr_users, $id_edition, $id_course = false) {

		if(empty($arr_users)) return true;
		if($id_course == false) {

			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

			$man = new Man_Course();
			$info = $man->getEditionInfo($id_edition);
			$id_course = $info['idCourse'];
		}
		$group_levels =& $this->course_man->getCourseIdstGroupLevel($id_course);
		$user_level = $this->course_man->getLevelsOfUsers($id_course, $arr_users);

		$re = $this->_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser IN ( ".implode(',', $arr_users)." ) AND idCourse = '".$id_course."' AND editon_id = '".$id_edition."'");

		$survivor = array();
		$query = "
		SELECT idUser
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser IN ( ".implode(',', $arr_users)." ) AND idCourse = '".$id_course."'";
		$re_query = sql_query($query);
		while(list($idu) = sql_fetch_row($re_query)) {
			$survivor[$idu] = $idu;
		}

		while(list(, $id_user) = each($arr_users)) {

			if(isset($user_level[$id_user]) && !isset($survivor[$id_user])) {
				$lv = $user_level[$id_user];
				$this->acl_man->removeFromGroup($group_levels[$lv], $id_user);
			}
		}

		return $re;
	}

	/**
	 * Unsubscribe a user to a course
	 * @param int 	$id_user 	the id of the user
	 * @param int 	$id_course 	the id of the course
	 *
	 * @return bool true if success, false otherwise
	 */
	function unsubscribeUserFromEd($id_user, $id_edition, $id_course = false) {

		return $this->unsubscribeUsersEd(array($id_user), $id_edition, $id_course);
	}

	function subscribeToCourse($id_user, $id_course, $id_date = 0)
	{
		//require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
		require_once (_lms_.'/lib/lib.date.php');
		require_once (_lms_.'/lib/lib.course.php');

		$date_man = new DateManager();
		$acl_man =& Docebo::user()->getAclManager();

		$query =	"SELECT idCourse"
					." FROM ".$GLOBALS['prefix_lms']."_courseuser"
					." WHERE idUser = ".$id_user;

		$result = sql_query($query);
		$courses = array();

		while(list($id_c) = sql_fetch_row($result))
			$courses[$id_c] = $id_c;

		$dates = $date_man->getUserDates($id_user);

		$level_idst =& getCourseLevel($id_course);

		if(count($level_idst) == 0 || $level_idst[1] == '')
			$level_idst =& DoceboCourse::createCourseLevel($id_course);

		list($subscribe_method) = sql_fetch_row(sql_query("SELECT subscribe_method FROM ".$GLOBALS['prefix_lms']."_course WHERE idCourse = ".$id_course));

		$waiting = 0;
		if($subscribe_method == '1')
			$waiting = 1;

		if($id_date != 0)
		{
			if(array_search($id_course, $courses) !== false)
			{
				if(array_search($id_date, $dates) === false)
				{
					if(!$date_man->addUserToDate($id_date, $id_user, getLogUserId()))
						return false;
				}
			}
			else
			{
				$acl_man->addToGroup($level_idst[3], $id_user);

				$re = sql_query(	"INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
									(idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)
									VALUES ('".$id_user."', '".$id_course."', '0', '3', '".$waiting."', '".getLogUserId()."', '".date("Y-m-d H:i:s")."')");

				if($re)
				{
					addUserToTimeTable($id_user, $id_course, 0);

					if(!$date_man->addUserToDate($id_date, $id_user, getLogUserId()))
						return false;
				}
			}
		}
		else
		{
			if(array_search($id_course, $courses) === false)
			{
				$acl_man->addToGroup($level_idst[3], $id_user);

				$re = sql_query(	"INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
									(idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)
									VALUES ('".$id_user."', '".$id_course."', '0', '3', '".$waiting."', '".getLogUserId()."', '".date("Y-m-d H:i:s")."')");
				if($re)
					addUserToTimeTable($id_user, $id_course, 0);
				else
					return false;
			}
		}

		return true;
	}
}

?>
