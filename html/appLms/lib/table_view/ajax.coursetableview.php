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


$command = Get::req('command', DOTY_ALPHANUM, false);

switch ($command) {

	case 'get_rows': {

		$lang 	=& DoceboLanguage::CreateInstance('course', 'lms');

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, 0); //GLOBALS --> visuItem
		$sort = Get::req('sort', DOTY_ALPHANUM, 'name');
		$dir = Get::req('dir', DOTY_ALPHANUM, 'asc');

		$table_status = array();
		$table_status['startIndex'] = $startIndex;
		$table_status['sort'] = $sort;
		$table_status['dir'] = $dir;
		$_SESSION['course_category']['table_status'] = $table_status;

		$filter = Get::req('filter', DOTY_MIXED, false);

		$filter_status = array();
		if (isset($filter['c_category']['value'])) $filter_status['c_category'] = $filter['c_category']['value']; else $filter_status['c_category'] = $_SESSION['course_category']['filter_status']['c_category'];
		if (isset($filter['c_filter']['value'])) $filter_status['c_filter'] = $filter['c_filter']['value']; else $filter_status['c_filter'] = $_SESSION['course_category']['filter_status']['c_filter'];
		if (isset($filter['c_flatview']['value'])) $filter_status['c_flatview'] = $filter['c_flatview']['value']; else $filter_status['c_flatview'] = $_SESSION['course_category']['filter_status']['c_flatview'];
		if (isset($filter['c_waiting']['value'])) $filter_status['c_waiting'] = $filter['c_waiting']['value']; else $filter_status['c_waiting'] = $_SESSION['course_category']['filter_status']['c_waiting'];

		$_SESSION['course_category']['filter_status'] = $filter_status;
	
		require_once(_lms_.'/lib/lib.course.php');
		$man_courses = new Man_Course();

		require_once(_lms_.'/lib/lib.edition.php');
		$edition_manager = new EditionManager();

		$num_edition = $edition_manager->getEditionNumber();

		$course_status = array(
			CST_PREPARATION => $lang->def('_CST_PREPARATION'),
			CST_AVAILABLE 	=> $lang->def('_CST_AVAILABLE'),
			CST_EFFECTIVE 	=> $lang->def('_CST_CONFIRMED'),
			CST_CONCLUDED 	=> $lang->def('_CST_CONCLUDED'),
			CST_CANCELLED 	=> $lang->def('_CST_CANCELLED')
		);
		$courses = array();
		$course_list =& $man_courses->getCoursesRequest($startIndex, $results, $sort, $dir, $filter);

		require_once(_lms_.'/lib/lib.permission.php');

		if(Docebo::user()->getUserLevelId() == ADMIN_GROUP_ADMIN)
			$moderate = checkPerm('moderate', true, 'course', 'lms');
		else
			$moderate = true;

		while ($row = sql_fetch_assoc($course_list)) {

			$row['status'] = $course_status[$row['status']];

			$highlight = false;
			if (isset($filter['c_filter']['value']) && $filter['c_filter']['value'] != '') $highlight = true;

			$courses[] = array(
				'idCourse'	=> $row['idCourse'],
				'code'		=> ($highlight ? highlightText($row['code'], $filter['c_filter']['value']) : $row['code']),
				'name'		=> ($highlight ? highlightText($row['name'], $filter['c_filter']['value']) : $row['name']),
				'status'	=> $row['status'],

				'waiting' => ( $row['pending'] && $moderate
					? '<a href="index.php?modname=subscribe&op=waitinguser&id_course='.$row['idCourse'].'">'.$row['pending'].'</a>'
					: '' ),

				'subscriptions' => ($row['course_edition'] != 1 ? ( isset($row['subscriptions']) ? $row['subscriptions'] : 0 ) : '--'),
				'classroom' => ($row['course_edition'] == 1 ? '<a href="index.php?r=alms/edition/show&amp;id_course='.$row['idCourse'].'">'.(isset($num_edition[$row['idCourse']]) ? $num_edition[$row['idCourse']] : '0').'</a>' : ''),
				'certificate' => true,
				'competence' => true,
				'menu' => true,
				'dup' => '<a id="dup_'.$row['idCourse'].'" href="index.php?modname=course&amp;op=dup_course&id_course='.$row['idCourse'].'">'.Get::img('standard/dup.png', $lang->def('_MAKE_A_COPY')).'</a>',
				'mod' => true,
				'del' => true
			);
		}				
				
		$output = array(
			'startIndex' => (int)$startIndex,
			'recordsReturned' => count($courses),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => (int)$man_courses->getCoursesCountFiltered($filter),
			'pageSize' => (int)$results,
			//'totalFilteredRecords' => $man_courses->getCoursesCountFiltered($filter),
			'records' => $courses
		);

		$json = new Services_JSON();
		aout($json->encode($output));

	};break;



	case 'del_row': {
		require_once(_lms_.'/lib/lib.course.php');
		
		$output = array('success'=>false);

		$id_course = Get::req('idrow', DOTY_INT, -1);
		if ($id_course > 0) {
			$man_course = new Man_Course();
			$output['success'] = $man_course->deleteCourse($id_course);
		}

		$json = new Services_JSON();
		aout($json->encode($output));
	};break;



	case 'set_name': {
		$output = array('success' => false);
		$id_course = Get::req('id_course', DOTY_INT, false);
		$new_name = Get::req('new_name', DOTY_STRING, '');

		if (is_numeric($id_course)) {
			if (sql_query("UPDATE ".$GLOBALS['prefix_lms']."_course SET name='".$new_name."' WHERE idCourse=".$id_course))
				$output['success'] = true;
		}

		aout($json->encode($output));
	};break;



	case 'updateField':
		require_once(_base_.'/lib/lib.json.php');

		$json = new Services_JSON();

		$id_course = Get::req('idCourse', DOTY_INT, false);
		$field = Get::req('col', DOTY_MIXED, false);
		$old_value = Get::req('old_value', DOTY_MIXED, false);
		$new_value = Get::req('new_value', DOTY_MIXED, false);

		switch($field)
		{
			case 'name':
				$res = false;

				if($new_value !== '')
				{
					$query =	"UPDATE ".$GLOBALS['prefix_lms']."_course"
								." SET name = '".$new_value."'"
								." WHERE idCourse = ".(int)$id_course;

					$res = sql_query($query);
				}

				aout($json->encode(array('success' => $res, 'new_value' => $new_value, 'old_value' => $old_value)));
			break;

			case 'code':
				$res = false;

				if($new_value !== '')
				{
					$query =	"UPDATE ".$GLOBALS['prefix_lms']."_course"
								." SET code = '".$new_value."'"
								." WHERE idCourse = ".(int)$id_course;

					$res = sql_query($query);
				}

				aout($json->encode(array('success' => $res, 'new_value' => stripslashes($new_value), 'old_value' => stripslashes($old_value))));
			break;
		}
	break;



	default: {}
}

?>