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

class ClassroomLms extends Model {

	protected $_t_order = false;

	public function  __construct() {
	}

	/**
	 * This function return the correct order to use when you wish to diplay the a
	 * course list for the user.
	 * @param <array> $t_name the table name to use as a prefix for the field, if false is passed no prefix will e used
	 *							we need a prefix for the course user rows and a prefix for the course table
	 *							array('u', 'c')
	 * @return <string> the order to use in a ORDER BY clausole
	 */
	protected function _resolveOrder($t_name = array('', '')) {
		// read order for the course from database
		if($this->_t_order == false) {
			
			$t_order = Get::sett('tablist_mycourses', false);
			if($t_order != false) {

				$arr_order_course = explode(',', $t_order);
				$arr_temp = array();
				foreach($arr_order_course as $key=>$value) {

					switch ($value) {
						case 'status': $arr_temp[] = ' ?u.status '; break;
						case 'code': $arr_temp[] = ' ?c.code '; break;
						case 'name': $arr_temp[] = ' ?c.name '; break;
					}
				}
				$t_order = implode(', ', $arr_temp);
			} else {

				$t_order = '?u.status, ?c.name';
			}
			// save a class copy of the resolved list
			$this->_t_order = $t_order;
		}
		foreach($t_name as $key=>$value) {
			if($value != '') $t_name[$key] = $value.'.';
		}
		return str_replace(array('?u.', '?c.'), $t_name ,$this->_t_order);
	}
	
	public function compileWhere($conditions, $params) {

		if(!is_array($conditions)) return "1";
		
		$where = array();
		$find = array_keys($params);
		foreach($conditions as $key=>$value) {

			$where[] = str_replace($find, $params, $value);
		}
		return implode(" AND ", $where);
	}

	public function findAll($conditions, $params) {

		$conditions[] = ' c.course_type = ":course_type" ';
		$params[':course_type'] = 'classroom';
        
		$db = DbConn::getInstance();
		$query = $db->query(
			"SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.lang_code, c.difficult, "
			."	c.subscribe_method, c.date_begin, c.date_end, c.max_num_subscribe, c.create_date, "
			."	c.status AS course_status, c.course_edition, "
			."	c.classrooms, c.img_othermaterial, c.course_demo, c.course_vote, "
			."	c.use_logo_in_courselist, c.img_course, "
			."	c.can_subscribe, c.sub_start_date, c.sub_end_date, c.valid_time, c.userStatusOp, c.show_result,"
			."	cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting,"
			."	c.auto_unsubscribe, c.unsubscribe_date_limit"
			." FROM %lms_course AS c "
			." JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) "
			." WHERE ".$this->compileWhere($conditions, $params)
			." ORDER BY ".$this->_resolveOrder(array('cu', 'c'))
		);
		$result = array();
		$courses = array();
		while($data = sql_fetch_assoc($query)) {

			$data['enrolled'] = 0;
			$data['numof_waiting'] = 0;
			$courses[] = $data['idCourse'];
			$result[$data['idCourse']] = $data;
		}
		// find subscriptions
		$re_enrolled = $db->query(
			"SELECT c.idCourse, COUNT(*) as numof_associated, SUM(waiting) as numof_waiting"
			." FROM %lms_course AS c "
			." JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) "
			." WHERE c.idCourse IN (".implode(',', $courses).") "
			." GROUP BY c.idCourse"
		);
		while($data = sql_fetch_assoc($re_enrolled)) {

			$result[$data['idCourse']]['enrolled'] = $data['numof_associated'] - $data['numof_waiting'];
			$result[$data['idCourse']]['numof_waiting'] = $data['numof_waiting'];
		}
		return $result;
	}




	public function getUserEditionsInfo($id_user, $courses) {
		if ((int)$id_user <= 0) return FALSE;
		if (is_numeric($courses)) $courses = array($courses);
		if (!is_array($courses)) return FALSE;
		if (empty($courses)) return array();

		$enrolled_arr =array();
		$qtxt ="SELECT d.id_course, d.id_date, COUNT(*) AS enrolled FROM
			%lms_course_date_user as du
			JOIN %lms_course_date d ON (du.id_date = d.id_date AND d.id_course IN (".implode(",", $courses)."))
			GROUP BY du.id_date";

		$q =sql_query($qtxt);
		while ($obj=sql_fetch_object($q)) {
			$enrolled_arr[$obj->id_course][$obj->id_date] = $obj->enrolled;
			$date_arr[$obj->id_date] = $obj->enrolled;//$date_arr[$obj->id_course][$obj->id_date] = $obj->enrolled;
			$id_date_arr[]=$obj->id_date;
		}


		array_unique($id_date_arr);
		if (empty($id_date_arr)) { $id_date_arr =array(0); }

		$date_arr =array();
		$qtxt ="SELECT dd.id_date, MIN(dd.date_begin) AS date_begin, MAX(dd.date_end) AS date_end,
			dd.pause_begin, dd.pause_end, c.idClassroom, c.name AS class_name,
			GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') AS location, d.id_course
			FROM %lms_course_date_day AS dd
			JOIN %lms_course_date AS d ON d.id_date = dd.id_date
			LEFT JOIN %lms_classroom AS c ON dd.classroom = c.idClassroom
			LEFT JOIN %lms_class_location AS l ON l.location_id = c.location_id
			WHERE dd.id_date IN (".implode(',', $id_date_arr).")
			GROUP BY dd.id_date";

		$q =sql_query($qtxt);
		while ($row=sql_fetch_assoc($q)) {
			$date_arr[$row['id_date']] = $row;//$date_arr[$row['id_course']][$row['id_date']] = $row;
		}


		$dates_minmax = array();
		$query_minmax = "SELECT id_date, MIN(date_begin) AS date_min, MAX(date_end) AS date_max "
			." FROM %lms_course_date_day WHERE id_date IN (".implode(',', $id_date_arr).") GROUP BY id_date";
		$res_minmax = sql_query($query_minmax);
		while (list($id_date, $date_min, $date_max) = sql_fetch_row($res_minmax)) {
			$dates_minmax[$id_date] = array($date_min, $date_max);
		}

		$output = array();
		$query = "SELECT d.id_date, d.id_course, d.code, d.name, d.status
			FROM %lms_course_date AS d
			JOIN %lms_course_date_user AS du ON (du.id_date = d.id_date)
			WHERE du.id_user = ".(int)$id_user." AND d.id_course IN (".implode(",", $courses).")";


		$id_date_arr =array();
		$res = sql_query($query);
		while ($obj = sql_fetch_object($res)) {
			if (isset($date_arr[$obj->id_date])) {
				$output[$obj->id_course][$obj->id_date] = $obj;
				if (isset($enrolled_arr[$obj->id_course][$obj->id_date])) {
					$output[$obj->id_course][$obj->id_date]->enrolled =$enrolled_arr[$obj->id_course][$obj->id_date];
				}

				if (isset($dates_minmax[$obj->id_date])) {
					$output[$obj->id_course][$obj->id_date]->date_min = $dates_minmax[$obj->id_date][0];
					$output[$obj->id_course][$obj->id_date]->date_max = $dates_minmax[$obj->id_date][1];
				} else {
					$output[$obj->id_course][$obj->id_date]->date_min = '';
					$output[$obj->id_course][$obj->id_date]->date_max = '';
				}

				$output[$obj->id_course][$obj->id_date]->date_info =$date_arr[$obj->id_date];
			}
		}
		
		return $output;
	}


	public function getFilterYears($id_user) {
		$output = array(0 => Lang::t('_ALL', 'standard'));
		$db = DbConn::getInstance();
		$query = "SELECT DISTINCT YEAR(dd.date_begin) AS inscr_year "
			." FROM %lms_course_date_user AS du JOIN %lms_course_date_day AS dd "
			." ON (du.id_date = dd.id_date) "
			." WHERE du.id_user = ".(int)$id_user." "
			." ORDER BY inscr_year ASC";
		$res = $db->query($query);
		if ($res && $db->num_rows($res) > 0) {
			while (list($inscr_year) = $db->fetch_row($res)) {
				$output[$inscr_year] = $inscr_year;
			}
		}
		return $output;
	}

	public function getUserCoursesByYear($id_user, $year) {
		if ((int)$year <= 0) return false;
		$output = array();
		$db = DbConn::getInstance();
		$date_1 = $year.'-01-01 00:00:00';
		$date_2 = $year.'-12-31 23:59:59';
		$query = "SELECT DISTINCT d.id_course "
			." FROM %lms_course_date AS d JOIN %lms_course_date_day AS dd JOIN %lms_course_date_user AS du "
			." ON (d.id_date = dd.id_date AND d.id_date = du.id_date) "
			." WHERE du.id_user = ".(int)$id_user." "
			." AND (dd.date_begin >= '".$date_1."' AND dd.date_begin <= '".$date_2."')";
		$res = $db->query($query);
		if ($res && $db->num_rows($res) > 0) {
			while (list($id_course) = $db->fetch_row($res)) {
				$output[] = $id_course;
			}
		}
		return $output;
	}


	public function getUserCoursePathCourses( $id_user ) {
		require_once(_lms_.'/lib/lib.coursepath.php');
		$cp_man = new Coursepath_Manager();
		$output = array();
		$cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
		if (!empty($cp_list)) {
			$cp_list = array_keys($cp_list);
			$output = $cp_man->getAllCourses($cp_list);
		}
		return $output;
	}


}