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

class EditionManager
{
	var $edition_table;
	var $edition_user;
	var $course_table;
	var $courseuser_table;
	var $user_table;

	var $db;
	var $acl_man;
	var $subscribe_man;

	var $status_list;

	public function __construct()
	{
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');

		$this->db = DbConn::getInstance();

		$this->edition_table = $GLOBALS['prefix_lms'].'_course_editions';
		$this->edition_user = $GLOBALS['prefix_lms'].'_course_editions_user';

		$this->course_table = $GLOBALS['prefix_lms'].'_course';
		$this->courseuser_table = $GLOBALS['prefix_lms'].'_courseuser';
		$this->user_table = $GLOBALS['prefix_fw'].'_user';

		$this->acl_man = $acl_man =& Docebo::user()->getAclManager();
		$this->subscribe_man = new CourseSubscribe_Manager();

		$this->status_list = array(	CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
									CST_AVAILABLE 	=> Lang::t('_CST_AVAILABLE', 'course'),
									CST_EFFECTIVE 	=> Lang::t('_CST_CONFIRMED', 'course'),
									CST_CONCLUDED 	=> Lang::t('_CST_CONCLUDED', 'course'),
									CST_CANCELLED 	=> Lang::t('_CST_CANCELLED', 'course'));
	}

	public function __destruct()
	{

	}

	public function getEditionNumber($id_course = false)
	{
		if(is_numeric($id_course))
		{
			$query =	"SELECT count(*)"
						." FROM ".$this->edition_table
						." WHERE id_course = '".$id_course."'";

			list($res) = sql_fetch_row(sql_query($query));
		}
		elseif (is_array($id_course) && count($id_course) > 0)
		{
			//Util::array_validate($id_course, DOTY_INT);
			$query =	"SELECT count(*)"
						." FROM ".$this->edition_table
						." WHERE id_course IN ('".implode("','", $id_course)."')";

			list($res) = sql_fetch_row(sql_query($query));
		}
		else
		{
			$query =	"SELECT count(*), id_course"
						." FROM ".$this->edition_table
						." WHERE 1"
						." GROUP BY id_course";

			$res = array();

			$result = sql_query($query);

			while(list($count, $id_course) = sql_fetch_row($result))
				$res[$id_course] = $count;
		}

		return $res;
	}

	public function getEdition($id_course, $start_index = false, $results = false, $sort = false, $dir = false)
	{
		$query = "SELECT e.id_edition, e.code, e.name, e.status, e.date_begin, e.date_end"
					." FROM ".$this->edition_table." AS e "
					." WHERE e.id_course = '".$id_course."' ";

		switch ($sort) {
			case 'code':
				$query .= " ORDER BY e.".$sort." ".$dir.", e.name ".$dir.", e.date_begin ".$dir;
			break;

			case 'name':
				$query .= " ORDER BY e.".$sort." ".$dir.", e.code ".$dir.", e.date_begin ".$dir;
			break;

			case 'status':
				$query .= " ORDER BY e.".$sort." ".$dir.", e.code ".$dir.", e.name ".$dir.", e.date_begin ".$dir;
			break;

			case 'date_begin':
				$query .= " ORDER BY e.".$sort." ".$dir.", e.code ".$dir.", e.name ".$dir;
			break;

			case 'date_end':
				$query .= " ORDER BY e.".$sort." ".$dir.", e.date_begin ".$dir.", e.code ".$dir.", e.name ".$dir;
			break;
		}

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);
		$res = array();

		while(list($id_edition, $code, $name, $status, $date_begin, $date_end) = sql_fetch_row($result))
		{
			$num_subscription = $this->getTotalUserSubscribed($id_course, $id_edition);
			$num_student = $this->getTotalStudentsSubscribed($id_course, $id_edition);

			$res[] = array( 'id_course' => $id_course,
				'id_edition' => $id_edition,
				'code' => $code,
				'name' => $name,
				'status' => $status,
				'status_tr' => $this->status_list[$status],
				'date_begin' => $date_begin,
				'date_end' => $date_end,
				'students' => $num_student,
				'num_subscription' => $num_subscription,
				'subscription' => '<a class="nounder" href="index.php?r=alms/subscription/show&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'">'
						.$num_subscription.' '.Get::img('course/subscribe.png', Lang::t('_SUBSCRIPTION', 'course')).'</a>',
				'edit' => '<a href="index.php?r=alms/edition/edit&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'">'.Get::img('standard/edit.png', Lang::t('_MOD', 'course')).'</a>',
				'del' => 'ajax.adm_server.php?r=alms/edition/del&amp;id_course='.$id_course.'&id_edition='.$id_edition);
		}

		return $res;
	}

	public function getStatusForDropdown()
	{
		return $this->status_list;
	}

	public function insertEdition($id_course, $code, $name, $description, $status, $max_par, $min_par, $price, $date_begin, $date_end, $overbooking, $can_subscribe, $sub_date_begin, $sub_date_end)
	{
		($date_begin !== '' ? $date_begin = Format::dateDb($date_begin, 'date') : '');
		($date_end !== '' ? $date_end = Format::dateDb($date_end, 'datetime') : '');
		($sub_date_begin !== '' ? $sub_date_begin = Format::dateDb($sub_date_begin, 'date') : '');
		($sub_date_end !== '' ? $sub_date_end = Format::dateDb($sub_date_end, 'date') : '');

		$query =	"INSERT INTO ".$this->edition_table
					." (id_edition, id_course, code, name, description, status, max_num_subscribe, min_num_subscribe, price, date_begin, date_end, overbooking, can_subscribe, sub_date_begin, sub_date_end)"
					." VALUES (NULL, '".$id_course."', '".$code."', '".$name."', '".$description."', '".$status."', '".$max_par."', '".$min_par."', '".$price."', '".$date_begin."', '".$date_end."', '".$overbooking."', '".$can_subscribe."', '".$sub_date_begin."', '".$sub_date_end."')";

                $ret = sql_query($query);
                
                $id_edition = sql_insert_id();
                
                // Salvataggio CustomField
                require_once(_adm_.'/lib/lib.customfield.php');
                $extra_field = new CustomFieldList();
                $extra_field->setFieldArea( "COURSE_EDITION" );
                $extra_field->storeFieldsForObj( $id_edition );
		
		return $ret;
	}

	public function getEditionInfo($id_edition)
	{
		$query =	"SELECT *"
					." FROM ".$this->edition_table
					." WHERE id_edition = ".(int)$id_edition;

		$res = sql_fetch_assoc(sql_query($query));

		return $res;
	}


	/*
	 * given a list of edition ids, it returns an array of objects with editions
	 * info grouped by edition id
	 */
	public function getEditionsInfo($id_editions, $use_objects = false)
	{
		if (is_numeric($id_editions))
			$arr = array($id_editions);
		elseif (is_array($id_editions) && count($id_editions)>0)
			$arr =& $id_editions;
		else
			return false;

		//Util::array_validate($arr, DOTY_INT);
		$output = array();
		$query =	"SELECT *"
					." FROM ".$this->edition_table
					." WHERE id_edition IN ('".implode("','", $arr)."')";

		$res = $this->db->query($query);
		if ($res) {
			$fetch_method = $use_objects ? 'fetch_obj' : 'fetch_assoc';
			while ($obj = $this->db->$fetch_method($res)) {
				$id = $use_objects ? $obj->id_edition : $obj['id_edition'];
				$output[$id] = $obj;
			}
		}

		return $output;
	}

	/*
	 * given a list of courses ids, it returns an array of objects with editions
	 * info associated by specified courses and grouped by [course id][edition id]
	 */
	public function getEditionsInfoByCourses($id_courses, $use_objects = false)
	{
		if (is_numeric($id_courses))
			$arr = array($id_courses);
		elseif (is_array($id_courses) && count($id_courses)>0)
			$arr =& $id_courses;
		else
			return false;

		//Util::array_validate($arr, DOTY_INT);
		$output = array();
		$query =	"SELECT e.* "
					." FROM ".$this->edition_table." as e JOIN ".$this->course_table." as c "
					." ON (e.id_course = c.idCourse) "
					." WHERE c.idCourse IN ('".implode("','", $arr)."')";

		$res = $this->db->query($query);
		if ($res) {
			$fetch_method = $use_objects ? 'fetch_obj' : 'fetch_assoc';
			while ($obj = $this->db->$fetch_method($res)) {
				$id_course = $use_objects ? $obj->id_course : $obj['id_course'];
				$id_edition = $use_objects ? $obj->id_edition : $obj['id_edition'];
				$output[$id_course][$id_edition] = $obj;
			}
		}

		return $output;
	}


	public function modEdition($id_edition, $code, $name, $description, $status, $max_par, $min_par, $price, $date_begin, $date_end, $overbooking, $can_subscribe, $sub_date_begin, $sub_date_end)
	{
		($date_begin !== '' ? $date_begin = Format::dateDb($date_begin, 'date') : '');
		if (!DateTime::createFromFormat('d-m-Y H:i', $date_end)){
			return false;
		}
		($date_end !== '' ? $date_end = Format::dateDb($date_end, 'datetime') : '');
		($sub_date_begin !== '' ? $sub_date_begin = Format::dateDb($sub_date_begin, 'date') : '');
		($sub_date_end !== '' ? $sub_date_end = Format::dateDb($sub_date_end, 'date') : '');

		$query =	"UPDATE ".$this->edition_table
					." SET code = '".$code."',"
					." name = '".$name."',"
					." description = '".$description."',"
					." status = '".$status."',"
					." max_num_subscribe = '".$max_par."',"
					." min_num_subscribe = '".$min_par."',"
					." price = '".$price."',"
					." date_begin = '".$date_begin."',"
					." date_end = '".$date_end."',"
					." overbooking = '".$overbooking."',"
					." can_subscribe = '".$can_subscribe."',"
					." sub_date_begin = '".$sub_date_begin."',"
					." sub_date_end = '".$sub_date_end."'"
					." WHERE id_edition = ".(int)$id_edition;

                $ret = sql_query($query);
                
                // Salvataggio CustomField
                require_once(_adm_.'/lib/lib.customfield.php');
                $extra_field = new CustomFieldList();
                $extra_field->setFieldArea( "COURSE_EDITION" );
                $extra_field->storeFieldsForObj( $id_edition );
		
		return $ret;
	}

	public function delEdition($id_edition)
	{
		$query =	"DELETE FROM ".$this->edition_table
					." WHERE id_edition = ".(int)$id_edition;

                $ret = sql_query($query);
                
		//remove customfield
		$ret = sql_query("DELETE FROM ".$GLOBALS['prefix_fw']."_customfield_entry WHERE id_field IN (SELECT id_field FROM core_customfield WHERE area_code = 'COURSE_EDITION') AND id_obj = '".$id_edition."'");

		return $ret;
	}


	/*
	 * it returns a list of subscribed users to an edition or a group of editions
	 */
	public function getEditionSubscribed($id_edition, $no_flat = false, $filter = '')
	{
		if (is_numeric($id_edition)) $id_edition = array($id_edition);
		if (is_array($id_edition) && count($id_edition) > 0)
		{
			$query =	"SELECT eu.id_user, eu.id_edition "
						." FROM ".$this->edition_user." AS eu"
						." JOIN ".$this->user_table." AS u ON u.idst = eu.id_user"
						." WHERE eu.id_edition IN (".implode(",", $id_edition).")";

			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
			{
				require_once(_base_.'/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'eu.id_user');
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

				/*
				if (isset($filter['date_valid']) && strlen($filter['date_valid']) >= 10) {
					$query .= " AND (s.date_begin_validity <= '".$filter['date_valid']."' OR s.date_begin_validity IS NULL OR s.date_begin_validity='0000-00-00 00:00:00') ";
					$query .= " AND (s.date_expire_validity >= '".$filter['date_valid']."' OR s.date_expire_validity IS NULL OR s.date_expire_validity='0000-00-00 00:00:00') ";
				}
				*/
			}

			$result = $this->db->query($query);

			$res = array();

			while(list($id_user, $id_edition) = $this->db->fetch_row($result)) {
				if ($no_flat)
					$res[$id_edition][$id_user] = $id_user;
				else
					$res[$id_user] = (int)$id_user;
			}
			if (!$no_flat) $res = array_unique($res);

			return $res;
		}
		else
		{
			return false;
		}
	}


	/*
	 * given an id_course or a set of id_courses, it returns the editions
	 */
	public function getCourseEditions($courses, $no_flat = false) {
		if (is_numeric($courses)) {
			$arr_courses = array($courses);
		} elseif (is_array($courses) && count($courses) > 0) {
			$arr_courses =& $courses;
		} else {
			return false;
		}

		$output = array();
		$query = "SELECT id_edition, id_course FROM ".$this->edition_table." "
			." WHERE id_course IN (".implode(",", $arr_courses).")";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_edition, $id_course) = $this->db->fetch_row($res)) {
				if ($no_flat)
					$output[$id_course][] = $id_edition;
				else
					$output[] = $id_edition;
			}
			return $output;
		} else {
			return false;
		}
	}


	public function getCourseEditionSubscription($id_course, $id_edition, $start_index, $results, $sort, $dir, $filter)
	{
		$query =	"SELECT u.idst, u.userid, u.firstname, u.lastname, s.level, s.status, s.date_complete, s.date_begin_validity, s.date_expire_validity"
					." FROM ".$this->courseuser_table." AS s"
					." JOIN ".$this->user_table." AS u ON s.idUser = u.idst"
					." WHERE s.idCourse = ".(int)$id_course
					." AND u.idst IN (".implode(', ', $this->getEditionSubscribed($id_edition)).")";

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

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
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

		while(list($id_user, $userid, $firstname, $lastname, $level, $status, $date_complete, $date_begin_validity, $date_expire_validity) = sql_fetch_row($result))
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
							'level_id' => $level,
							'status_id' => $status,
							'date_complete' => $date_complete,
							'date_begin_validity' => $date_begin_validity,
							'date_expire_validity' => $date_expire_validity,
							'del' => 'ajax.adm_server.php?r=alms/subscription/delPopUp&id_course='.$id_course.'&id_edition='.$id_edition.'&id_user='.$id_user);
		}

		return $res;
	}

	public function getTotalUserSubscribed($id_course, $id_edition, $filter = "")
	{
		$subscribed = $this->getEditionSubscribed($id_edition);
		if(count($subscribed) == 0 ) return 0;
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->courseuser_table." AS s"
					." JOIN ".$this->user_table." AS u ON s.idUser = u.idst"
					." WHERE s.idCourse = ".(int)$id_course
					." AND u.idst IN (".implode(', ', $subscribed).")";

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
		}

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}
	
	public function getTotalStudentsSubscribed($id_course, $id_edition, $filter = "")
	{
		$subscribed = $this->getEditionSubscribed($id_edition);
		if(count($subscribed) == 0 ) return 0;
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->courseuser_table." AS s"
					." JOIN ".$this->user_table." AS u ON s.idUser = u.idst"
					." WHERE s.idCourse = ".(int)$id_course
					." AND u.idst IN (".implode(', ', $subscribed).")"
					." AND s.level = 3";

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
		}

		list($res) = sql_fetch_row(sql_query($query));
		return $res;
	}

	public function addUserToEdition($id_edition, $id_user, $id_subscriber)
	{
		if($this->controlEditionUserSubscriptions($id_user, $id_edition))
			return true;

		$query =	"INSERT INTO ".$this->edition_user
					." (id_edition, id_user, date_subscription, subscribed_by)"
					." VALUES (".$id_edition.", ".$id_user.", '".date('Y-m-d H:i:s')."', ".$id_subscriber.")";

		return sql_query($query);
	}

	public function controlEditionUserSubscriptions($id_user, $id_edition)
	{
		$query =	"SELECT COUNT(*)"
					." FROM %lms_course_editions_user"
					." WHERE id_user = ".(int)$id_user
					." AND id_editions = ".(int)$id_edition;

		list($control) = sql_fetch_row(sql_query($query));

		if($control > 0)
			return true;
		return false;
	}

	public function subscribeUserToEdition($id_user, $id_course, $id_edition, $level, $waiting, $date_begin_validity = false, $date_expire_validity = false)
	{
		require_once(_lms_.'/lib/lib.subscribe.php');

		$subscribe_man = new CourseSubscribe_Manager();

		if(!$subscribe_man->controlSubscription($id_user, $id_course))
			$subscribe_man->subscribeUserToCourse($id_user, $id_course, $level, $waiting, $date_begin_validity = false, $date_expire_validity = false);
		else
			$subscribe_man->updateForNewDateSubscribe($id_user, $id_course, $waiting);

		return $this->addUserToEdition($id_edition, $id_user, getLogUserId());
	}

	private function controlUserSubscriptions($id_user, $id_course)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->edition_user
					." WHERE id_user = ".$id_user
					." AND id_edition IN"
					." ("
						." SELECT id_edition"
						." FROM ".$this->edition_table
						." WHERE id_course = ".$id_course
					." )";

		list($res) = sql_fetch_row(sql_query($query));

		if($res > 0)
			return true;
		return false;
	}

	public function removeUserFromEdition($id_user, $id_edition, $id_course)
	{
		$query =	"DELETE FROM ".$this->edition_user
					." WHERE id_user = ".$id_user
					." AND id_edition = ".$id_edition;

		$res = sql_query($query);
		return $res;
	}

	public function delUserFromEdition($id_user, $id_course, $id_edition)
	{
		$level = $this->subscribe_man->getUserLeveInCourse($id_user, $id_course);
		$this->removeUserFromEdition($id_user, $id_edition, $id_course);
		
		if(!$this->controlUserSubscriptions($id_user, $id_course)) {
		
			require_once(_lms_.'/lib/lib.subscribe.php');
			require_once(_lms_.'/lib/lib.course.php');
			
			$subscribe_man = new CourseSubscribe_Manager();
			$subscribe_man->delUserFromCourse($id_user, $id_course);

			$docebo_course = new DoceboCourse($id_course);
			$level_idst =& $docebo_course->getCourseLevel($id_course);
			$this->acl_man->removeFromGroup($level_idst[$level], $id_user);
		}

		return true;
	}

	public function setEditionFinished($id_edition, $id_user)
	{
		$query =	"UPDATE ".$this->edition_user
					." SET date_complete = '".date('Y-m-d H:i:s')."'"
					." WHERE id_edition = ".$id_edition
					." AND id_user = ".$id_user;

		return sql_query($query);
	}

	public function getEditionAvailableForCourse($id_user, $id_course)
	{
		$edition_full = $this->getFullEdition($id_course);
		$user_edition = $this->getUserEdition($id_user);

		$query =	"SELECT id_edition"
					." FROM ".$this->edition_table
					." WHERE ("
					." date_begin = '0000-00-00'"
					." OR date_begin > '".date('Y-m-d')."')"
					.(count($user_edition) > 0 ? " AND id_edition NOT IN (".implode(',', $user_edition).")" : '')
					.(count($edition_full) > 0 ? " AND id_edition NOT IN (".implode(',', $edition_full).")" : '')
					." AND status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")"
					." AND id_course = ".(int)$id_course;

		$result = sql_query($query);
		$res = array();

		while(list($id_edition) = sql_fetch_row($result))
			$res[] = $id_edition;

		return $res;
	}

	public function getEditionAvailableWithInfo($id_user, $id_course)
	{
		$query =	"SELECT *"
					." FROM ".$this->edition_table
					." WHERE ("
					." date_begin = '0000-00-00'"
					." OR date_begin > '".date('Y-m-d')."')"
					." AND id_course = ".(int)$id_course." "
					." AND status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")";

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
		{
			$row['num_subscribed'] = $this->getTotalUserSubscribed($id_course, $row['id_edition']);
			$res[$row['id_edition']] = $row;
		}

		return $res;
	}

	public function getUserEdition($id_user)
	{
		$query =	"SELECT id_edition"
					." FROM ".$this->edition_user
					." WHERE id_user = '".$id_user."'";

		$result = sql_query($query);
		$res = array();

		while(list($id_edition) = sql_fetch_row($result))
			$res[$id_edition] = $id_edition;

		return $res;
	}

	public function getFullEdition($id_course)
	{
		$query =	"SELECT id_edition, max_num_subscribe"
					." FROM ".$this->edition_table
					." WHERE id_course = '".$id_course."'";

		$result = sql_query($query);
		$res = array();

		while(list($id_edition, $max_par) = sql_fetch_row($result))
		{
			if($max_par != 0)
			{
				$query =	"SELECT COUNT(*)"
							." FROM ".$this->edition_user
							." WHERE id_edition = ".$id_edition;

				list($control) = sql_fetch_row(sql_query($query));

				if($control >= $max_par)
					$res[$id_edition] = $id_edition;
			}
		}

		return $res;
	}

	public function getNotConfirmetEditionForCourse($id_course)
	{
		$query =	"SELECT id_edition"
					." FROM ".$this->edition_table
					." WHERE status IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")";

		$result = sql_query($query);
		$res = array();

		while(list($id_edition) = sql_fetch_row($result))
		{
			$res[$id_edition] = $id_edition;
		}

		return $res;
	}
}

?>