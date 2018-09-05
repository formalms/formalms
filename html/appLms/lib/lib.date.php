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

define('_DATE_STATUS_ACTIVE', 0);
define('_DATE_STATUS_FINISHED', 1);
define('_DATE_STATUS_CANCELLED', 2);
define('_DATE_STATUS_PREPARATION', 3);

define('_DATE_TEST_TYPE_WEB', 0);
define('_DATE_TEST_TYPE_PAPER', 1);
define('_DATE_TEST_TYPE_NONE', 2);

class DateManager
{
	var $date_table;
	var $day_date_table;
	var $user_date_table;
	var $presence_date_table;
	var $classroom_table;
	var $location_table;
	var $course_table;
	var $courseuser_table;
	var $user_table;

	var $lang;
	var $acl_man;
	var $subscribe_man;

	public function __construct()
	{
		require_once(_lms_.'/lib/lib.subscribe.php');

		$this->date_table = $GLOBALS['prefix_lms'].'_course_date';
		$this->day_date_table = $GLOBALS['prefix_lms'].'_course_date_day';
		$this->user_date_table = $GLOBALS['prefix_lms'].'_course_date_user';
		$this->presence_date_table = $GLOBALS['prefix_lms'].'_course_date_presence';
		$this->classroom_table = $GLOBALS['prefix_lms'].'_classroom';
		$this->location_table = $GLOBALS['prefix_lms'].'_class_location';
		$this->course_table = $GLOBALS['prefix_lms'].'_course';
		$this->courseuser_table = $GLOBALS['prefix_lms'].'_courseuser';
		$this->user_table = $GLOBALS['prefix_fw'].'_user';

		$this->lang =& DoceboLanguage::CreateInstance('admin_date', 'lms');
		$this->acl_man = $acl_man =& Docebo::user()->getAclManager();
		$this->subscribe_man = new CourseSubscribe_Manager();
	}

	public function __destruct()
	{

	}

	public function getDateNumber($id_course, $all = false)
	{
		$query =	"SELECT dt.id_date, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." WHERE dt.id_course = ".$id_course
					." GROUP BY dt.id_date"
					." ORDER BY dy.date_begin";

		$result = sql_query($query);

		$res = 0;

		while($row = sql_fetch_assoc($result))
		{
			if(strcmp($row['date_begin'],date('Y-m-d H:i:s')) > 0 || $all)
			{
				if(isset($_SESSION['date_begin_filter']) && $_SESSION['date_begin_filter'] !== '' && isset($_SESSION['date_end_filter']) && $_SESSION['date_end_filter'] !== '')
				{
					if(strcmp(Format::dateDb($_SESSION['date_begin_filter']), $row['date_begin']) <= 0 && strcmp(Format::dateDb($_SESSION['date_end_filter']), $row['date_end']) >= 0)
						$res++;
				}
				elseif(isset($_SESSION['date_begin_filter']) && $_SESSION['date_begin_filter'] !== '')
				{
					if(strcmp(Format::dateDb($_SESSION['date_begin_filter']), $row['date_begin']) <= 0 )
						$res++;
				}
				elseif(isset($_SESSION['date_end_filter']) && $_SESSION['date_end_filter'] !== '')
				{
					if(strcmp(Format::dateDb($_SESSION['date_end_filter']), $row['date_end']) >= 0)
						$res++;
				}
				else
					$res++;
			}
		}

		return $res;
	}

	public function getDateNumberNoLimit($id_course)
	{
		$query =	"SELECT dt.id_date, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." WHERE dt.id_course = ".$id_course
					." GROUP BY dt.id_date"
					." ORDER BY dy.date_begin";

		$result = sql_query($query);

		$res = 0;

		while($row = sql_fetch_assoc($result))
		{
			if(isset($_SESSION['date_begin_filter']) && $_SESSION['date_begin_filter'] !== '' && isset($_SESSION['date_end_filter']) && $_SESSION['date_end_filter'] !== '')
			{
				if(strcmp(Format::dateDb($_SESSION['date_begin_filter']), $row['date_begin']) <= 0 && strcmp(Format::dateDb($_SESSION['date_end_filter']), $row['date_end']) >= 0)
					$res++;
			}
			elseif(isset($_SESSION['date_begin_filter']) && $_SESSION['date_begin_filter'] !== '')
			{
				if(strcmp(Format::dateDb($_SESSION['date_begin_filter']), $row['date_begin']) <= 0 )
					$res++;
			}
			elseif(isset($_SESSION['date_end_filter']) && $_SESSION['date_end_filter'] !== '')
			{
				if(strcmp(Format::dateDb($_SESSION['date_end_filter']), $row['date_end']) >= 0)
					$res++;
			}
			else
				$res++;
		}

		return $res;
	}

	public function getClassroomForDropdown()
	{
		$res = array();
		$res[0] = $this->lang->def('_NOT_ASSIGNED');

		$query =	"SELECT idClassroom, location, name"
					." FROM %lms_class_location as loc JOIN ".$this->classroom_table." AS cl "
					." ON (loc.location_id = cl.location_id) ";
		
		if (Docebo::user()->getUserLevelId() !== ADMIN_GROUP_GODADMIN) {
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$arr_locations = $adminManager->getAdminClasslocation(Docebo::user()->getIdst());
			if (!empty($arr_locations)) {
				$query .= " WHERE loc.location_id IN (".implode(",", $arr_locations).") ";
			} else {
				return $res;
			}
		}
		
		$query .= " ORDER BY location, name";

		$result = sql_query($query);

		while(list($id_classroom, $location, $name) = sql_fetch_row($result))
			$res[$id_classroom] = '<b>'.$location.'</b> - '.$name;

		return $res;
	}

	public function insDate($id_course, $code, $name, $description, $medium_time, $max_par, $price, $overbooking, $status, $test_type, $sub_start_date, $sub_end_date, $unsubscribe_date_limit)
	{
		$query =	"INSERT INTO ".$this->date_table
					." (`id_course`, `code`, `name`, `description`, `medium_time`, "
					." `max_par`, `price`, `overbooking`, `test_type`, `status`, "
					." `sub_start_date`, `sub_end_date`, `unsubscribe_date_limit`) "
					." VALUES (".(int)$id_course.", '".$code."', '".$name."', "
					." '".$description."', '".$medium_time."', ".(int)$max_par.", "
					." '".$price."', ".($overbooking ? '1' : '0').", ".$test_type.", "
					." ".$status.", '".$sub_start_date."', '".$sub_end_date."', "
					." '".$unsubscribe_date_limit."')";
					//." VALUES (".$id_course.", '".Util::strip_slashes($code)."', '".Util::strip_slashes($name)."', '".Util::strip_slashes($description)."', '".$medium_time."', ".(int)$max_par.", '".Util::strip_slashes($price)."', ".$overbooking.", ".$test_type.", ".$status.")";

		$result = sql_query($query);

		if($result)
		{
			$res = sql_insert_id();
			return $res;
		}
		return $result;
	}

	private function clearDateDay($id_date)
	{
		$query =	"DELETE FROM ".$this->day_date_table
					." WHERE	ID_DATE = ".$id_date;

		return sql_query($query);
	}

	public function insDateDay($id_date, $array_day)
	{
		$res = false;

		if($this->clearDateDay($id_date))
		{
			$query =	"INSERT INTO ".$this->day_date_table
						." (id_day, id_date, classroom, date_begin, date_end, pause_begin, pause_end)";

			$first = true;

			foreach($array_day as $id_day => $day_info)
			{
				if($first)
				{
					$first = false;
					$query .= " VALUES (".$id_day.", ".$id_date.", ".$day_info['classroom'].", '".$day_info['date_begin']."', '".$day_info['date_end']."', '".$day_info['pause_begin']."', '".$day_info['pause_end']."')";
				}
				else
					$query .= ", (".$id_day.", ".$id_date.", ".$day_info['classroom'].", '".$day_info['date_begin']."', '".$day_info['date_end']."', '".$day_info['pause_begin']."', '".$day_info['pause_end']."')";
			}

			return sql_query($query);
		}
		return $res;
	}

	
    
    public function getAvailableDate($id_course){
        $res =  $this->getCourseDate($id_course, false);
        foreach ($res as $k => $v) {
            if ($v['status'] != 0) 
                unset($res[$k]);
        }
        return $res; 
    }
    
    public function getCourseDate($id_course, $all = true, $ini = 0, $num_element = 0)
	{
		$res = array();

		if (empty($id_course) || $id_course <= 0) return $res;
		if (is_numeric($id_course)) $id_course = array((int)$id_course);
		if (!is_array($id_course)) return false;

		$query =	"SELECT dt.*, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end, COUNT(dy.id_day) as num_day, COUNT(DISTINCT du.id_user) as user_subscribed"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." LEFT JOIN ".$this->user_date_table." as du ON du.id_date = dt.id_date"
					." WHERE dt.id_course IN (".implode(",", $id_course).") "
					." GROUP BY dt.id_date"
					." ORDER BY dy.date_begin"
					.($num_element > 0 ? " LIMIT ".$ini.",".$num_element : '');

		$result = sql_query($query);

		

		while($row = sql_fetch_assoc($result))
		{
			if(strcmp($row['date_begin'],date('Y-m-d H:i:s')) > 0 || $all)
			{
				$row['classroom'] = $this->getDateClassrooms($row['id_date']);

				if($row['user_subscribed'] > 1)
					$row['num_day'] = $row['num_day'] / $row['user_subscribed'];

				if(isset($_SESSION['date_begin_filter']) && $_SESSION['date_begin_filter'] !== '' && isset($_SESSION['date_end_filter']) && $_SESSION['date_end_filter'] !== '')
				{
					if(strcmp(Format::dateDb($_SESSION['date_begin_filter']), $row['date_begin']) <= 0 && strcmp(Format::dateDb($_SESSION['date_end_filter']), $row['date_end']) >= 0)
						$res[ $row['id_date'] ] = $row;
				}
				elseif(isset($_SESSION['date_begin_filter']) && $_SESSION['date_begin_filter'] !== '')
				{
					if(strcmp(Format::dateDb($_SESSION['date_begin_filter']), $row['date_begin']) <= 0 )
						$res[ $row['id_date'] ] = $row;
				}
				elseif(isset($_SESSION['date_end_filter']) && $_SESSION['date_end_filter'] !== '')
				{
					if(strcmp(Format::dateDb($_SESSION['date_end_filter']), $row['date_end']) >= 0)
						$res[ $row['id_date'] ] = $row;
				}
				else
					$res[ $row['id_date'] ] = $row;
			}
		}

		return $res;
	}

	public function getDateClassrooms($id_date, $show_location = false)
	{
		$query =	"SELECT DISTINCT classroom"
					." FROM ".$this->day_date_table
					." WHERE id_date = ".$id_date;

		$result = sql_query($query);
		$array_classroom = array();

		while(list($id_classroom) = sql_fetch_row($result))
			$array_classroom[$id_classroom] = $id_classroom;

		$res = '';
		$first = true;

		if(isset($array_classroom[0]))
		{
			$first = false;
			$res .= $this->lang->def('_NOT_ASSIGNED');
		}

		$query = "SELECT c.name, cl.location "
					." FROM ".$this->classroom_table." AS c "
					." JOIN ".$this->location_table." AS cl "
					." ON (c.location_id = cl.location_id) "
					." WHERE c.idClassroom IN (".implode(',', $array_classroom).")"
					." ORDER BY c.name";

		$result = sql_query($query);

		$first = true;
		while(list($name, $location) = sql_fetch_row($result)) {
			$_name = ($show_location && trim($location)!='' ? $location.' - ' : '').$name;
			if($first)
			{
				$first = false;
				$res .= $_name;
			}
			else
				$res .= ', '.$_name;
		}
		
		return $res;
	}

	public function getDateNumDay($id_date)
	{
		$query =	"SELECT COUNT(id_day) as num_day"
					." FROM ".$this->day_date_table
					." WHERE id_date = ".$id_date;

		list($num_day) = sql_fetch_row(sql_query($query));

		return $num_day;
	}

    
    public function getClassromByID($id_classroom)
    {
        $query =    "SELECT name, location "
                    ." FROM ".$this->classroom_table .", ".$this->location_table
                    ." WHERE idClassroom = ".$id_classroom ." and ".$this->location_table.".location_id=".$this->classroom_table.".location_id";
                    
        list($name,$location) = sql_fetch_row(sql_query($query));

        return $location." - ".$name;
    }    
    
    
    
	public function getDateInfo($id_date)
	{
		$query =	"SELECT dt.*, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end, COUNT(dy.id_day) as num_day, COUNT(DISTINCT du.id_user) as user_subscribed"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." LEFT JOIN ".$this->user_date_table." as du ON du.id_date = dt.id_date"
					." WHERE dt.id_date = ".$id_date
					." GROUP BY dt.id_date"
					." ORDER BY dy.date_begin";

		$res = sql_fetch_assoc(sql_query($query));

		if($res['user_subscribed'] > 1)
				$res['num_day'] = $res['num_day'] / $res['user_subscribed'];

		//find the number of students and waiting students already subscribed, we need him for overbooiking checkings
		$query = "SELECT COUNT(*) FROM %lms_courseuser AS cu JOIN %lms_course_date AS cd JOIN %lms_course_date_user AS cdu "
			." ON (cd.id_date = cdu.id_date AND cd.id_course = cu.idCourse AND cu.idUser = cdu.id_user) "
			." WHERE cd.id_date = ".(int)$id_date." AND cu.level = 3";
		$rs = sql_query($query);
		if ($rs) {
			list($count) = sql_fetch_row($rs);
			$res['num_students'] = (int)$count;
		}

		return $res;
	}

	public function getDateDay($id_date)
	{
		$query =	"SELECT *"
					." FROM ".$this->day_date_table
					." WHERE id_date = ".$id_date
					." ORDER BY date_begin";

		$result = sql_query($query);

		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[$row['id_day']] = $row;

		return $res;
	}

	public function getDateDayForControl($id_date)
	{
		$query =	"SELECT classroom, date_begin, date_end, pause_begin, pause_end"
					." FROM ".$this->day_date_table
					." WHERE id_date = ".$id_date;

		$result = sql_query($query);
		$res = array();

		while(list($classroom, $date_begin, $date_end, $pause_begin, $pause_end) = sql_fetch_row($result))
		{
			$day = substr($date_begin, 0, 10);

			$res[$day]['classroom'] = $classroom;
			$res[$day]['b_hours'] = substr($date_begin, 11, 2);
			$res[$day]['b_minutes'] = substr($date_begin, 14, 2);
			$res[$day]['pb_hours'] = substr($pause_begin, 11, 2);
			$res[$day]['pb_minutes'] = substr($pause_begin, 14, 2);
			$res[$day]['pe_hours'] = substr($pause_end, 11, 2);
			$res[$day]['pe_minutes'] = substr($pause_end, 14, 2);
			$res[$day]['e_hours'] = substr($date_end, 11, 2);
			$res[$day]['e_minutes'] = substr($date_end, 14, 2);
		}

		return $res;
	}

	//public function upDate($id_date, $code, $name, $max_par, $price, $overbooking, $status, $test_type)
	public function upDate($id_date, $code, $name, $description, $medium_time, $max_par, $price, $overbooking, $status, $test_type, $sub_start_date, $sub_end_date, $unsubscribe_date_limit)
	{
		$query =	"UPDATE ".$this->date_table
					." SET `code` = '".$code."',"
					." name = '".$name."',"
					." description = '".$description."',"
					." medium_time = ".$medium_time.","
					." max_par = '".$max_par."',"
					." price = '".$price."',"
					." overbooking = ".$overbooking.","
					." test_type = ".$test_type.","
					." `status` = ".$status.","
					." sub_start_date = '".$sub_start_date."',"
					." sub_end_date = '".$sub_end_date."',"
					." unsubscribe_date_limit = '".$unsubscribe_date_limit."'"
					." WHERE id_date = ".$id_date;

		return sql_query($query);
	}

	public function delDate($id_date)
	{
		$res = false;

		$id_course = $this->getDateCourse($id_date);

		$subscribed = $this->getDateSubscribed($id_date);

		foreach($subscribed as $id_user) {

			$control = $this->removeUserFromDate($id_user, $id_date, $id_course);
			if(!$control) {

				//require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
				require_once(_lms_.'/lib/lib.course.php');

				$docebo_course = new DoceboCourse($id_course);

				$course_man = new Man_Course();

				$course_info = $course_man->getCourseInfo($id_course);

				$date_begin = $course_info["date_begin"];
				$date_end = $course_info["date_end"];

				$group_levels = $docebo_course->getCourseLevel($id_course);
				$user_levels = getSubscribedLevel($id_course, false, false, 0);

				removeSubscription($id_course, $id_user, $group_levels[$user_levels[$id_user]], 0, $date_begin, $date_end);
			}
		}

		if($this->clearDateDay($id_date))
		{
			$query =	"DELETE FROM ".$this->date_table
						." WHERE id_date = ".$id_date;

			$res = sql_query($query);
		}

		return $res;
	}

	private function getDateCourse($id_date)
	{
		$query =	"SELECT id_course"
					." FROM ".$this->date_table
					." WHERE id_date = ".$id_date;

		list($id_course) = sql_fetch_row(sql_query($query));

		return $id_course;
	}

	public function getDateConflict($id_course)
	{
		$array_date_foreach = $this->getCourseDate($id_course, true);
		$array_date = $array_date_foreach;

		$res = array();

		foreach($array_date_foreach as $date_info)
		{
			if($date_info['classroom'] != 0)
			{
				$array_date_begin = $this->getDateDayDateDetails($date_info['id_date']);

				$res[$date_info['id_date']] = array();

				foreach($array_date_begin as $date_day)
				{
					$query =	"SELECT dy.id_date"
								." FROM ".$this->day_date_table." AS dy"
								." JOIN ".$this->date_table." AS dt ON dt.id_date = dy.id_date"
								." WHERE dy.date_begin <= '".$date_day['date_begin']."'"
								." AND dy.date_end > '".$date_day['date_begin']."'"
								." AND dy.id_date <> ".$date_info['id_date']
								." AND dt.classroom = ".$date_info['classroom'];

					$result = sql_query($query);

					if(sql_num_rows($result) > 0)
						while(list($id_date_conflict) = sql_fetch_row($result))
							if(array_search($id_date_conflict, $res[$date_info['id_date']]) === false)
								if(isset($res[$id_date_conflict]) && array_search($date_info['id_date'], $res[$id_date_conflict]) === false)
									$res[$date_info['id_date']][] = $id_date_conflict;

					$query =	"SELECT dy.id_date"
								." FROM ".$this->day_date_table." AS dy"
								." JOIN ".$this->date_table." AS dt ON dt.id_date = dy.id_date"
								." WHERE dy.date_begin <= '".$date_day['date_end']."'"
								." AND dy.date_end > '".$date_day['date_end']."'"
								." AND dy.id_date <> ".$date_info['id_date']
								." AND dt.classroom = ".$date_info['classroom'];

					$result = sql_query($query);

					if(sql_num_rows($result) > 0)
						while(list($id_date_conflict) = sql_fetch_row($result))
							if(array_search($id_date_conflict, $res[$date_info['id_date']]) === false)
								if(isset($res[$id_date_conflict]) && array_search($date_info['id_date'], $res[$id_date_conflict]) === false)
									$res[$date_info['id_date']][] = $id_date_conflict;
				}
			}
		}

		return $res;
	}

	public function getDateDayDateDetails($id_date)
	{
		$query =	"SELECT date_begin, date_end, classroom"
					." FROM ".$this->day_date_table
					." WHERE id_date = ".$id_date;

		$result = sql_query($query);

		$res = array();
		$i = 0;

		while($row = sql_fetch_assoc($result))
		{
			$res[$i]['date_begin'] = $row['date_begin'];
			$res[$i]['date_end'] = $row['date_end'];
            $res[$i]['classroom'] = $this->getClassromByID($row['classroom']);
			$i++;
		}

		return $res;
	}

	public function getDateName($id_date)
	{
		$query =	"SELECT name"
					." FROM ".$this->date_table
					." WHERE id_date = ".$id_date;

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getClassroomForDate($id_date)
	{
		$query =	"SELECT classroom"
					." FROM ".$this->date_table
					." WHERE id_date = ".$id_date;

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getDateSubscribed($id_date, $filter = '')
	{
		$query =	"SELECT ud.id_user"
					." FROM ".$this->user_date_table." AS ud"
					." JOIN ".$this->user_table." AS u ON u.idst = ud.id_user"
					." WHERE ud.id_date = ".$id_date;

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {

			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), ' ud.id_user');
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

		}

		$result = sql_query($query);

		$res = array();

		while(list($id_user) = sql_fetch_row($result))
			$res[$id_user] = (int)$id_user;

		return $res;
	}



	public function getDatesSubscribed($arr_id_date, $no_flat = false, $filter = '')
	{
		if (is_numeric($arr_id_date)) $arr_id_date = array((int)$arr_id_date);
		if (!is_array($arr_id_date)) return false;
		if (empty($arr_id_date)) return array();

		$query =	"SELECT ud.id_user, ud.id_date "
					." FROM ".$this->user_date_table." AS ud "
					." JOIN ".$this->user_table." AS u ON u.idst = ud.id_user "
					." WHERE ud.id_date IN (".implode(",", $arr_id_date).")";

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), ' ud.id_user');
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

		}

		$result = sql_query($query);
		if (!$result) return false;

		$res = array();
		while(list($id_user, $id_date) = sql_fetch_row($result)) {
			if ($no_flat)
				$res[$id_date][$id_user] = $id_user;
			else
				$res[$id_user] = (int)$id_user;
		}
		if (!$no_flat) $res = array_unique($res);

		return $res;
	}


	public function addUserToDate($id_date, $id_user, $id_subscriber, $overbooking = FALSE)
	{
		if($this->controlDateUserSubscriptions($id_user, $id_date))
			return true;

		$query =	"INSERT INTO ".$this->user_date_table
					." (id_date, id_user, date_subscription, subscribed_by, overbooking)"
					." VALUES (".$id_date.", ".$id_user.", '".date('Y-m-d H:i:s')."', ".$id_subscriber.", ".($overbooking ? '1' : '0').")";

		return sql_query($query);
	}

	public function controlDateUserSubscriptions($id_user, $id_date)
	{
		$query =	"SELECT COUNT(*)"
					." FROM %lms_course_date_user"
					." WHERE id_user = ".(int)$id_user
					." AND id_date = ".(int)$id_date;

		list($control) = sql_fetch_row(sql_query($query));

		if($control > 0)
			return true;
		return false;
	}

	public function setDateFinished($id_date, $id_user)
	{
		$query =	"UPDATE ".$this->user_date_table
					." SET date_complete = '".date('Y-m-d H:i:s')."'"
					." WHERE id_date = ".$id_date
					." AND id_user = ".$id_user;

		return sql_query($query);
	}

	public function toggleDateFinished($id_date, $id_user)
	{
		$query =	"UPDATE ".$this->user_date_table
					." SET date_complete = '0000-00-00 00:00:00'"
					." WHERE id_date = ".$id_date
					." AND id_user = ".$id_user;

		return sql_query($query);
	}

	//the same function as the one below, but this has the right name; TO DO: cancel it and use only "remove"
	public function removeUserFromDate($id_user, $id_date, $id_course)
	{
		$query =	"DELETE FROM ".$this->user_date_table
					." WHERE id_user = ".$id_user
					." AND id_date = ".$id_date;

		$res = sql_query($query);

		if($res)
		{
			$query =	"DELETE FROM ".$this->presence_date_table
						." WHERE id_user = ".$id_user
						." AND id_date = ".$id_date;

			$res = sql_query($query);
		}

		return $res;
	}

	private function controlUserSubscriptions($id_user, $id_course)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->user_date_table
					." WHERE id_user = ".$id_user
					." AND id_date IN"
					." ("
						." SELECT id_date"
						." FROM ".$this->date_table
						." WHERE id_course = ".$id_course
					." )";

		list($res) = sql_fetch_row(sql_query($query));

		if($res > 0)
			return true;
		return false;
	}

	public function getStatusForDropdown()
	{
		return array(	_DATE_STATUS_PREPARATION => $this->lang->def('_CST_PREPARATION', 'course'),
						_DATE_STATUS_ACTIVE => $this->lang->def('_CST_CONFIRMED', 'course'),
						_DATE_STATUS_FINISHED => $this->lang->def('_CST_CONCLUDED', 'course'),
						_DATE_STATUS_CANCELLED => $this->lang->def('_CST_CANCELLED', 'course') );
	}

	public function getTestTypeForDropdown()
	{
		return array(	_DATE_TEST_TYPE_WEB => $this->lang->def('_WEB_TEST'),
						_DATE_TEST_TYPE_PAPER => $this->lang->def('_PAPER_TEST'),
						_DATE_TEST_TYPE_NONE => $this->lang->def('_NONE'));
	}

	public function getHours()
	{
		return array(	'00' => '00',
						'01' => '01',
						'02' => '02',
						'03' => '03',
						'04' => '04',
						'05' => '05',
						'06' => '06',
						'07' => '07',
						'08' => '08',
						'09' => '09',
						'10' => '10',
						'11' => '11',
						'12' => '12',
						'13' => '13',
						'14' => '14',
						'15' => '15',
						'16' => '16',
						'17' => '17',
						'18' => '18',
						'19' => '19',
						'20' => '20',
						'21' => '21',
						'22' => '22',
						'23' => '23');
	}

	public function getMinutes()
	{
		return array(	'00' => '00',
						'05' => '05',
						'10' => '10',
						'15' => '15',
						'20' => '20',
						'25' => '25',
						'30' => '30',
						'35' => '35',
						'40' => '40',
						'45' => '45',
						'50' => '50',
						'55' => '55');
	}

	public function getUserForPresence($id_date)
	{
		$acl_man =& Docebo::user()->getAclManager();

		$is_admin = false;

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN && Docebo::user()->getUserLevelId() != ADMIN_GROUP_USER)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$is_admin = true;
		}
        
		$view_all_perm = checkPerm('view_all', true, 'presence');

		$query = "SELECT u.idst, u.userid, u.firstname, u.lastname"
				." FROM ".$this->user_date_table.' AS d'
				." JOIN ".$this->courseuser_table.' AS c ON c.idUser = d.id_user'
				." JOIN ".$this->user_table.' AS u ON u.idst = d.id_user'
				." WHERE d.id_date = ".$id_date
				." AND c.level = 3";

		if ( !$view_all_perm && Docebo::user()->getUserLevelId() == '/framework/level/admin' ) {
			$query.= ($is_admin ?" AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'd.id_user') : '');	
		}
		$query.= " ORDER BY u.lastname, u.firstname, u.userid";

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
		{
			$row['userid'] = $acl_man->relativeId($row['userid']);
			$res[$row['idst']] = $row;
		}

		return $res;
	}

	public function getTestType($id_date)
	{
		$query =	"SELECT test_type"
					." FROM ".$this->date_table
					." WHERE id_date = ".$id_date;

		list($test_type) = sql_fetch_row(sql_query($query));

		return $test_type;
	}
          
	public function getUserPresenceForDate($id_date)
	{
		$query =	"SELECT *"
					." FROM ".$this->presence_date_table
					." WHERE id_date = ".$id_date;

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'id_user');
		}

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[$row['id_user']][$row['day']] = $row;

		return $res;
	}

	private function clearDatePresence($id_date)
	{
		$query =	"DELETE FROM ".$this->presence_date_table
					." WHERE id_date = ".$id_date;

		return sql_query($query);
	}

	public function insDatePresence($id_course, $id_date, $user, $day, $score_min = 0)
	{
		$clear = $this->clearDatePresence($id_date);

		if($clear)
		{
			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.stats.php');
			//require_once($GLOBALS['where_lms'].'/lib/lib.competences.php');

			//$cman = new Competences_Manager();
			$cmodel = new CompetencesAdm();

			$first = true;
			$test_type = $this->getTestType($id_date);

			$query =	"INSERT INTO ".$this->presence_date_table
						." (`day`, `id_date`, `id_user`, `id_day`, `presence`, `score`, `note`)"
						." VALUES";

			foreach($user as $id_user => $user_info)
			{
				$num_day = 0;
				$num_day_finished = 0;

				foreach($user_info['day_presence'] as $id_day => $presence)
				{
					$day_tmp = substr($day[$id_day]['date_begin'], 0, 10);

					$num_day++;

					if($presence == 1)
						$num_day_finished++;

					if($first)
					{
						$first = false;
						$query .= " ('".$day_tmp."', ".$id_date.", ".$id_user.", ".$id_day.", ".$presence.", NULL, NULL)";
					}
					else
						$query .= ", ('".$day_tmp."', ".$id_date.", ".$id_user.", ".$id_day.", ".$presence.", NULL, NULL)";
				}

				if($test_type == _DATE_TEST_TYPE_PAPER)
				{
					$num_day++;

					if($user_info['score'] >= $score_min)
						$num_day_finished++;

					if($first)
					{
						$first = false;
						$query .= " ('0000-00-00', ".$id_date.", ".$id_user.", 0, ".($user_info['score'] >= $score_min ? 1 : 0).", '".$user_info['score']."', '".$user_info['note']."')";
					}
					else
						$query .= ", ('0000-00-00', ".$id_date.", ".$id_user.", 0, ".($user_info['score'] >= $score_min ? 1 : 0).", '".$user_info['score']."', '".$user_info['note']."')";
				}
				else
				{
					if($first)
					{
						$first = false;
						$query .= " ('0000-00-00', ".$id_date.", ".$id_user.", 0, 0, NULL, '".$user_info['note']."')";
					}
					else
						$query .= ", ('0000-00-00', ".$id_date.", ".$id_user.", 0, 0, NULL, '".$user_info['note']."')";
				}

				if($num_day == $num_day_finished && ($test_type == _DATE_TEST_TYPE_NONE || $test_type == _DATE_TEST_TYPE_PAPER))
				{
					saveTrackStatusChange($id_user, $id_course , _CUS_END);

					//$cman->AssignCourseCompetencesToUser($id_course, $id_user);
					$cmodel->assignCourseCompetencesToUser($id_course, $id_user);

					$this->setDateFinished($id_date, $id_user);
				}
				elseif($test_type == _DATE_TEST_TYPE_NONE || $test_type == _DATE_TEST_TYPE_PAPER)
				{
					$query_itinere =	"UPDATE ".$this->courseuser_table
										." SET `status` = "._CUS_BEGIN.","
										." date_complete = NULL"
										." WHERE idUser = ".$id_user
										." AND idCourse = ".$id_course;

					sql_query($query_itinere);

					//TODO: funzione per togliere la competenza ad un utente se gli era stata precedentemente assegnata (forse)
				}
			}

			return sql_query($query);
		}

		return false;
	}

	public function getUserDates($id_user)
	{
		$acl_manager=Docebo::user()->getAclManager();

		if($id_user == $acl_manager->getAnonymousId())
			return array();

		$query =	"SELECT id_date"
					." FROM ".$this->user_date_table
					." WHERE id_user = ".$id_user;

		$result = sql_query($query);
		$res = array();

		while(list($id_date) = sql_fetch_row($result))
			$res[$id_date] = $id_date;

		return $res;
	}

	public function getFullDateForCourse($id_course)
	{
		$query =	"SELECT id_date, max_par"
					." FROM ".$this->date_table
					." WHERE id_course = ".$id_course;

		$result = sql_query($query);
		$res = array();

		while(list($id_date, $max_par) = sql_fetch_row($result))
		{
			if($max_par != 0)
			{
				$query =	"SELECT COUNT(*)"
							." FROM ".$this->user_date_table
							." WHERE id_date = ".$id_date;

				list($control) = sql_fetch_row(sql_query($query));

				if($control >= $max_par)
					$res[$id_date] = $id_date;
			}
		}

		return $res;
	}

	public function getNotConfirmetDateForCourse($id_course)
	{
		$query =	"SELECT id_date"
					." FROM ".$this->date_table
					." WHERE status IN ("._DATE_STATUS_CANCELLED.","._DATE_STATUS_FINISHED.","._DATE_STATUS_PREPARATION.")"
					." AND id_course = ".$id_course;

		$result = sql_query($query);
		$res = array();

		while(list($id_date) = sql_fetch_row($result))
			$res[$id_date] = $id_date;

		return $res;
	}


	public function getCourseWithPresence($month, $year, $users, $completed)
	{
		if($month != 0 && $year != 0)
		{
			$day = date('t', mktime(0, 0, 0, $month, 1, $year));

			if($month < 10)
				$month = '0'.$month;

			$date_filter = " AND date_begin BETWEEN '".$year."-".$month."-01 00:00:00' AND '".$year."-".$month."-".$day." 23:59:59'";
		}
		elseif($month == 0 && $year != 0)
			$date_filter = " AND date_begin BETWEEN '".$year."-01-01 00:00:00' AND '".$year."-12-31 23:59:59'";
		else
			$date_filter = '';

		$query =	"SELECT d.id_course, d.id_date, d.test_type, dp.id_user, SUM(dp.presence) AS sum_presence, COUNT(*) AS tot_day"
					." FROM ".$this->date_table." AS d"
					." LEFT JOIN ".$this->presence_date_table." AS dp ON dp.id_date = d.id_date"
					." WHERE d.id_date IN"
					." ("
					." SELECT dd.id_date"
					." FROM ".$this->day_date_table." AS dd"
					." WHERE id_day = 0"
					.$date_filter
					." )"
					.(count($users) > 0 ? " AND dp.id_user IN (".implode(',', $users).")" : '')
					." AND id_course IN"
					." ("
					." SELECT idCourse"
					." FROM ".$this->course_table
					." WHERE 1"
					." )"
					." GROUP BY d.id_course, d.id_date, dp.id_user";

		$result = sql_query($query);

		$res = array();

		while(list($id_course, $id_date, $test_type, $id_user, $sum_presence, $tot_day) = sql_fetch_row($result))
		{
			if($test_type != _DATE_TEST_TYPE_PAPER)
				$tot_day--;

			if($sum_presence == $tot_day)
			{
				$res['user'][$id_user]['id_user'] = $id_user;
				$res['user'][$id_user]['id_date'][$id_date] = $id_date;
				$res[$id_course]['dates'][$id_date] = $id_date;
				$res['presence'][$id_user][$id_date] = true;
			}
			elseif($completed == 0)
			{
				$res['user'][$id_user]['id_user'] = $id_user;
				$res['user'][$id_user]['id_date'][$id_date] = $id_date;
				$res[$id_course]['dates'][$id_date] = $id_date;
				$res['presence'][$id_user][$id_date] = false;
			}
		}

		return $res;
	}

	public function getUserDateForCourse($id_user, $id_course)
	{
		$acl_manager=$GLOBALS["current_user"]->getAclManager();

		if($id_user == $acl_manager->getAnonymousId())
			return array();

		$query =	"SELECT id_date"
					." FROM ".$this->user_date_table
					." WHERE id_user = ".$id_user
					." AND id_date IN"
					." ("
					." SELECT id_date"
					." FROM ".$this->date_table
					." WHERE id_course = ".$id_course
					.")";

		$result = sql_query($query);
		$res = array();

		while(list($id_date) = sql_fetch_row($result))
			$res[] = $id_date;

		return $res;
	}

	public function checkUserPresence($id_user, $id_course)
	{
		$course_date = $this->getCourseDate($id_course, true);

		$user_date = $this->getUserDateForCourse($id_user, $id_course);

		foreach($course_date as $date_info)
		{
			if(array_search($date_info['id_date'], $user_date) !== false)
			{
				$query =	"SELECT SUM(presence) AS sum_presence, COUNT(*) AS tot_day"
							." FROM ".$this->presence_date_table
							." WHERE id_date = ".(int)$date_info['id_date']
							." AND id_user = ".(int)$id_user
							." GROUP BY id_date, id_user";
				$re = sql_query($query);
				if(!sql_num_rows($re)) return false;

				list($sum_presence, $tot_day) = sql_fetch_row($re);
				$tot_day--;

				if($sum_presence >= $tot_day)
					return true;
				else
					return false;
			}
		}
		return false;
	}

	public function getAlertForAdmin($array_user, $day_to_control)
	{
		$date = mktime(0, 0, 0, date('m'), date('d') + $day_to_control, date('Y'));

		$query =	"SELECT id_date, id_user"
					." FROM ".$this->user_date_table
					." WHERE id_user IN (".implode(',', $array_user).")"
					." AND id_date IN"
					." ("
					." SELECT id_date"
					." FROM ".$this->day_date_table
					." WHERE id_day = 0"
					." AND date_begin BETWEEN '".date('Y-m-d', $date)." 00:00:00' AND '".date('Y-m-d', $date)." 23:59:59'"
					." )";

		$result = sql_query($query);

		$res = array();

		if(sql_num_rows($result))
		{
			while(list($id_date, $id_user) = sql_fetch_row($result))
				$res[$id_date]['user'][$id_user] = $id_user;
		}

		return $res;
	}

	public function controlDateGratisForUser($id_date, $id_user)
	{
		$query =	"SELECT gratis"
					." FROM ".$this->user_date_table
					." WHERE id_user = ".$id_user
					." AND id_date = ".$id_date;

		list($gratis) = sql_fetch_row(sql_query($query));

		if($gratis == 0)
			return false;
		else
			return true;
	}

	public function getGratisUserForDate($id_date)
	{
		$query =	"SELECT id_user"
					." FROM ".$this->user_date_table
					." WHERE gratis = 1";

		$result = sql_query($query);

		$res = array();

		if(sql_num_rows($result) > 0)
			while(list($id_user) = sql_fetch_row($result))
				$res[$id_user] = $id_user;

		return $res;
	}

	public function setDateGratis($id_date, $id_user)
	{
		$query =	"UPDATE ".$this->user_date_table
					." SET gratis = 1"
					." WHERE id_user = ".$id_user
					." AND id_date = ".$id_date;

		return sql_query($query);
	}

	public function setDatePayment($id_date, $id_user)
	{
		$query =	"UPDATE ".$this->user_date_table
					." SET gratis = 0"
					." WHERE id_user = ".$id_user
					." AND id_date = ".$id_date;

		return sql_query($query);
	}

	public function getDateClassroomsWithInfo($id_date)
	{
		$query =	"SELECT DISTINCT classroom"
					." FROM ".$this->day_date_table
					." WHERE id_date = ".$id_date;

		$result = sql_query($query);
		$array_classroom = array();

		while(list($id_classroom) = sql_fetch_row($result))
			$array_classroom[$id_classroom] = $id_classroom;

		$res = '';
		$first = true;

		if(isset($array_classroom[0]))
		{
			$first = false;
			$res .= $this->lang->def('_NOT_ASSIGNED');
		}

		$query =	"SELECT *"
					." FROM ".$this->classroom_table
					." WHERE idClassroom IN (".implode(',', $array_classroom).")"
					." ORDER BY name";

		$result = sql_query($query);

		while($row = sql_fetch_assoc($result))
			$res[] = $row;

		return $res;
	}

	/*
	 *chosen a begin date and an end date, finds all courses which have editions starting between the two dates
	 *
	 */
	public function getCourseWithDateInPeriod($date_begin, $date_end, $id_course = array())
	{
		$query =	"SELECT dt.*, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end, COUNT(dy.id_day) as num_day, COUNT(DISTINCT du.id_user) as user_subscribed"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." LEFT JOIN ".$this->user_date_table." as du ON du.id_date = dt.id_date"
					.(is_array($id_course) && !empty($id_course)? " WHERE dt.id_course IN (".implode(', ', $id_course).")" : '')
					." GROUP BY dt.id_date"
					." ORDER BY date_begin";

		$result = sql_query($query);

		$res = array();

		while($row = sql_fetch_assoc($result))
			if(strcmp($row['date_begin'], $date_begin) > 0 && strcmp($row['date_begin'], $date_end) < 0)
				$res[$row['id_course']] = $row['id_course'];

		return $res;
	}

	public function getCourseDateNumber($id_course)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->date_table
					." WHERE id_course = ".$id_course;

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getCourseEdition($id_course, $start_index = false, $results = false, $sort = false, $dir = false, $ed_arr=false)
	{

		if ($ed_arr !== false && is_array($ed_arr) && empty($ed_arr)) {
			$ed_arr[]=0;
		}

		$query =	"SELECT dt.id_date, dt.code, dt.name, dt.status, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end, COUNT(dy.id_day) as num_day, COUNT(DISTINCT du.id_user) as user_subscribed, dt.unsubscribe_date_limit"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." LEFT JOIN ".$this->user_date_table." as du ON du.id_date = dt.id_date"
					." LEFT JOIN ".$this->user_table." AS u ON u.idst = du.id_user"
					." WHERE dt.id_course = ".$id_course
					.(!empty($ed_arr) && is_array($ed_arr) ? " AND dt.id_date IN (".implode(",", $ed_arr).") " : "")
					." GROUP BY dt.id_date";

		switch($sort)
		{
			case 'code':
				$query .= " ORDER BY dt.code ".$dir.", dt.name ".$dir.", date_begin ".$dir;
			break;

			case 'name':
				$query .= " ORDER BY dt.name ".$dir.", date_begin ".$dir;
			break;

			case 'status':
				$query .= " ORDER BY dt.status ".$dir.", dt.code ".$dir.", dt.name ".$dir.", date_begin ".$dir;
			break;

			case 'date_begin':
				$query .= " ORDER BY date_begin ".$dir.", dt.code ".$dir.", dt.name ".$dir;
			break;

			case 'date_end':
				$query .= " ORDER BY date_end ".$dir.", date_begin ".$dir.", dt.code ".$dir.", dt.name ".$dir;
			break;
		}

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);

		$res = array();

		while(list($id_date, $code, $name, $status, $date_begin, $date_end, $num_day, $user_subscribed, $unsubscribe_date_limit) = sql_fetch_row($result))
		{
			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
			{
				require_once(_base_.'/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$query =	"SELECT COUNT(*)"
							." FROM ".$this->user_date_table
							." WHERE id_date = '".$id_date."'"
							." AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'id_user');

				list($user_subscribed) = sql_fetch_row(sql_query($query));
				
				$query =	"SELECT COUNT(*) FROM %lms_courseuser AS cu JOIN %lms_course_date AS cd JOIN %lms_course_date_user AS cdu "
						." ON (cd.id_date = cdu.id_date AND cd.id_course = cu.idCourse AND cu.idUser = cdu.id_user) "
						." WHERE cd.id_date = ".(int)$id_date." AND cu.level = 3"
						." AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'u.id_user');

				list($num_student) = sql_fetch_row(sql_query($query));
			}
			else
			{
				$query =	"SELECT COUNT(*) FROM %lms_courseuser AS cu JOIN %lms_course_date AS cd JOIN %lms_course_date_user AS cdu "
						." ON (cd.id_date = cdu.id_date AND cd.id_course = cu.idCourse AND cu.idUser = cdu.id_user) "
						." WHERE cd.id_date = ".(int)$id_date." AND cu.level = 3";

				list($num_student) = sql_fetch_row(sql_query($query));
			}

			$res[] = array(
				'id_date' => $id_date,
				'code' => $code,
				'name' => $name,
				'status' => $status,
				'date_begin' => Format::date($date_begin, 'date'),
				'date_end' => Format::date($date_end, 'date'),
				'classroom' => $this->getDateClassrooms($id_date, true),
				'students' => $num_student,
				'num_subscribe' => $user_subscribed,
				'subscription' => '<a href="index.php?r=alms/subscription/show&id_course='.$id_course.'&amp;id_date='.$id_date.'" title="'.Lang::t('_SUBSCRIPTION', 'course').'">'
						.($user_subscribed?$user_subscribed:0)
						.' <span class="ico-sprite subs_'.($user_subscribed>0?'users':'notice').'"><span>'.Lang::t('_USER_STATUS_SUBS').'</span></span></a>',
				'presence' => '<a href="index.php?r=alms/classroom/presence&id_course='.$id_course.'&amp;id_date='.$id_date.'">'.Lang::t('_ATTENDANCE', 'course').'</a>',
				'num_day' => $num_day,
				'user_subscribed' => $user_subscribed,
				'mod' => '<a href="index.php?r=alms/classroom/modclassroom&id_course='.$id_course.'&amp;id_date='.$id_date.'">'.Get::img('standard/edit.png', Lang::t('_MOD', 'course')).'</a>',
				'del' => 'ajax.adm_server.php?r=alms/classroom/delclassroom&id_course='.$id_course.'&amp;id_date='.$id_date,
				'unsubscribe_date_limit' => $unsubscribe_date_limit);
		}

		return $res;
	}

	public function getCourseEditionSubscription($id_course, $id_date, $start_index = false, $results = false, $sort = false, $dir = false, $filter = false)
	{
		$query =	"SELECT u.idst, u.userid, u.firstname, u.lastname, s.level, s.status, s.date_complete, s.date_begin_validity, s.date_expire_validity "
					." FROM ".$this->courseuser_table." AS s"
					." JOIN ".$this->user_table." AS u ON s.idUser = u.idst"
					." WHERE s.idCourse = ".(int)$id_course
					." AND u.idst IN (".implode(', ', $this->getDateSubscribed($id_date)).")";

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

		$overbooking_users = $this->getDateOverbookingUsers($id_date);

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
							'overbooking' => in_array($id_user, $overbooking_users),
							'del' => 'ajax.adm_server.php?r=alms/subscription/delPopUp&id_course='.$id_course.'&id_date='.$id_date.'&id_user='.$id_user);
		}

		return $res;
	}

	public function getTotalUserSubscribed($id_course, $id_date, $filter = "")
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->courseuser_table." AS s"
					." JOIN ".$this->user_table." AS u ON s.idUser = u.idst"
					." WHERE s.idCourse = ".(int)$id_course
					." AND u.idst IN (".implode(', ', $this->getDateSubscribed($id_date)).")";

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

	public function subscribeUserToDate($id_user, $id_course, $id_date, $level, $waiting, $date_begin_validity = false, $date_expire_validity = false)
	{
		require_once(_lms_.'/lib/lib.subscribe.php');

		//check for overbooking
		$is_overbooking = FALSE;
		$cinfo = $this->getDateInfo($id_date);
		if ($cinfo['max_par'] > 0 && $cinfo['max_par'] <= $cinfo['user_subscribed']) {
			//max number of participants has been already reached
			if ($cinfo['overbooking'] > 0) {
				$is_overbooking = TRUE; //if course allows overbooking, then put the user in list
			} else {
				return FALSE; //otherwise go back and don't subscribe the user
			}
		}

		$subscribe_man = new CourseSubscribe_Manager();

		if(!$subscribe_man->controlSubscription($id_user, $id_course))
			$subscribe_man->subscribeUserToCourse($id_user, $id_course, $level, $waiting, $date_begin_validity, $date_expire_validity);
		else
			$subscribe_man->updateForNewDateSubscribe($id_user, $id_course, $waiting);

		return $this->addUserToDate($id_date, $id_user, Docebo::user()->getIdst(), $is_overbooking);
	}



	public function delUserFromDate($id_user, $id_course, $id_date)
	{
		$this->removeUserFromDate($id_user, $id_date, $id_course);
		if(!$this->controlUserSubscriptions($id_user, $id_course)) {

			require_once(_lms_.'/lib/lib.course.php');
			require_once(_lms_.'/lib/lib.subscribe.php');

			$subscribe_man = new CourseSubscribe_Manager();
			$level = $this->subscribe_man->getUserLeveInCourse($id_user, $id_course);
			$subscribe_man->delUserFromCourse($id_user, $id_course);

			$docebo_course = new DoceboCourse($id_course);
			$level_idst =& $docebo_course->getCourseLevel($id_course);
			$this->acl_man->removeFromGroup($level_idst[$level], $id_user);

			//check if there are overbooked users
			$cinfo = $this->getDateInfo($id_date);
			$overbooking_users = $this->getDateOverbookingUsers($id_date);
			if ($cinfo['overbooking'] > 0 && !empty($overbooking_users)) {
				if (($cinfo['user_subscribed'] - count($overbooking_users)) < $cinfo['max_par']) {
					$this->setFirstOverbookingUser($id_date);
				}
			}
		}
		return true;
	}

	public function getDateIdForCourse($id_course)
	{
		$query =	"SELECT id_date"
					." FROM ".$this->date_table
					." WHERE id_course = ".(int)$id_course;

		$result = sql_query($query);
		$res = array();

		while(list($id_date) = sql_fetch_row($result))
			$res[] = $id_date;

		return $res;
	}

	public function getDateInfoForPublicPresence($id_date)
	{
		$query =	"SELECT dt.*, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end, dy.pause_begin, dy.pause_end, COUNT(dy.id_day) as num_day, COUNT(DISTINCT du.id_user) as user_subscribed"
					." FROM ".$this->date_table." as dt"
					." JOIN ".$this->day_date_table." as dy ON dy.id_date = dt.id_date"
					." LEFT JOIN ".$this->user_date_table." as du ON du.id_date = dt.id_date"
					." WHERE dt.id_date IN (".implode(',',$id_date).")"
					." GROUP BY dt.id_date"
					." ORDER BY date_begin DESC";

		$result = sql_query($query);

		$res = array();

		while($row = sql_fetch_assoc($result))
		{
			$row['classroom'] = $this->getDateClassrooms($row['id_date']);

			if($row['user_subscribed'] > 1)
				$row['num_day'] = $row['num_day'] / $row['user_subscribed'];

			$res[] = $row;
		}

		return $res;
	}


	/**
	 * Find classroom which are full and overbookable
	 *
	 * @param <type> $id_course = ID of the course
	 * @return <type> array of classrooms
	 */
	public function getOverbookingDateForCourse($id_course) {
		$query =	"SELECT id_date, max_par"
					." FROM ".$this->date_table
					." WHERE id_course = ".$id_course."	AND overbooking = 1";

		$result = sql_query($query);
		$res = array();

		while(list($id_date, $max_par) = sql_fetch_row($result))
		{
			if($max_par != 0)
			{
				$query =	"SELECT COUNT(*)"
							." FROM ".$this->user_date_table
							." WHERE id_date = ".$id_date;

				list($control) = sql_fetch_row(sql_query($query));

				if($control >= $max_par)
					$res[$id_date] = $id_date;
			}
		}

		return $res;
	}



	public function getDateOverbookingUsers($id_date) {
		$output = array();
		$query = "SELECT id_user FROM ".$this->user_date_table." WHERE id_date = ".(int)$id_date." AND overbooking = 1";
		$res = sql_query($query);
		while (list($id_user) = sql_fetch_row($res)) {
			$output[] = $id_user;
		}
		return $output;
	}


	public function setFirstOverbookingUser($id_date) {
		$query = "SELECT * FROM ".$this->user_date_table." "
			." WHERE id_date = ".(int)$id_date." AND overbooking = 1 "
			." ORDER BY date_subscription ASC LIMIT 1";
		$res = sql_query($query);
		if ($res && sql_num_rows($res) > 0) {
			$obj = sql_fetch_object($res);
			$query = "UPDATE ".$this->user_date_table." SET overbooking = 0 "
				." WHERE id_date = ".$obj->id_date." AND id_user = ".$obj->id_user;
			$res = sql_query($query);
		}
		return $res ? TRUE : FALSE;
	}

	public function checkHasValidUnsubscribePeriod($id_course, $id_user)
	{
		$user_dates = $this->getUserDates($id_user);

		$query =	"SELECT COUNT(*)"
					." FROM ".$this->date_table
					." WHERE id_date IN (".implode(',', $user_dates).")"
					." AND id_course = ".(int)$id_course
					." AND (
						unsubscribe_date_limit >= '".date('Y-m-d')."' OR
						unsubscribe_date_limit = '0000-00-00 00:00:00' OR
						unsubscribe_date_limit = ''
					)";

		list($control) = sql_fetch_row(sql_query($query));

		if($control > 0)
			return true;
		return false;
	}





	/*
	 * given a list of courses ids, it returns an array of objects with editions
	 * info associated by specified courses and grouped by [course id][edition id]
	 */
	public function getDatesInfoByCourses($id_courses, $use_objects = false)
	{
		if (is_numeric($id_courses))
			$arr = array((int)$id_courses);
		elseif (is_array($id_courses) && count($id_courses)>0)
			$arr =& $id_courses;
		else
			return false;

		$output = array();

		if (!empty($arr)) {
			$query =	"SELECT d.*, MIN(dd.date_begin) as date_begin, MAX(dd.date_end) as date_end
				FROM learning_course_date as d
				JOIN learning_course_date_day as dd
				ON (dd.id_date = d.id_date)
				WHERE d.id_course IN ('".implode("','", $arr)."')
				GROUP BY d.id_date
				ORDER BY d.code, d.name, date_begin";
			$res = sql_query($query);
			if ($res) {
				$fetch_method = $use_objects ? 'sql_fetch_object' : 'sql_fetch_assoc';
				while ($obj = $fetch_method($res)) {
					$id_course = $use_objects ? $obj->id_course : $obj['id_course'];
					$id_date = $use_objects ? $obj->id_date : $obj['id_date'];
					$output[$id_course][$id_date] = $obj;
				}
			}
		}

		return $output;
	}


}

?>