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


class CoursestatsLmsController extends LmsController {

	protected $model;
	protected $json;
	protected $permissions;

	public function init() {
		$this->model = new CoursestatsLms();
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view' => true,//checkPerm('view', true, 'coursestats')
			'mod' => true
		);
	}


	protected function _getErrorMessage($code) {
		$message = "";

		switch ($code) {
			case "invalid data": $message = ""; break;
			case "invalid column": $message = ""; break;
			case "reset success": $message = Lang::t('_TRACK_RESET_SUCCESS', 'error'); break;
			case "reset error": $message = Lang::t('_TRACK_RESET_ERROR', 'error');; break;
			case "": $message = ""; break;
			default: $message = ""; break;
		}
		
		return $message;
	}


	protected function _getJsArrayLevel()	{
		$first = true;
		$output = '[';
		$model = new SubscriptionAlms();
		$list = $model->getUserLevelList();
		foreach($list as $id_level => $level_translation) {
			if ($first) $first = false; else $output .= ',';
			$output .= '{"value":'.$this->json->encode($id_level).',"label":'.$this->json->encode($level_translation).'}';
		}
		$output .= ']';
		return $output;
	}

	protected function _getJsArrayStatus()	{
		$first = true;
		$output = '[';
		$model = new SubscriptionAlms();
		$list = $model->getUserStatusList();
		foreach($list as $id_status => $status_translation) {
			if ($first) $first = false; else $output .= ', ';
			$output .= '{"value":'.$this->json->encode($id_status).',"label":'.$this->json->encode($status_translation).'}';
		}
		$output .= ']';
		return $output;
	}

	protected function _getJsArrayLOStatus()	{
		$first = true;
		$output = '[';
		$list = array(
			'failed' => 'failed',
			'incomplete' => 'incomplete',
			'not attempted' => 'not attempted',
			'attempted' => 'attempted',
			'ab-initio' => 'ab-initio',
			'completed' => 'completed',
			'passed' => 'passed'
		);
		foreach($list as $id_status => $status_translation) {
			if ($first) $first = false; else $output .= ', ';
			$output .= '{"value":'.$this->json->encode($id_status).',"label":'.$this->json->encode($status_translation).'}';
		}
		$output .= ']';
		return $output;
	}

	//----------------------------------------------------------------------------

	public function showTask() {
		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		Util::get_js(Get::rel_path("base").'/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path("lms").'/views/coursestats/coursestats.js', true, true);

		$total_users = $this->model->getCourseStatsTotal($id_course, false);
		$lo_totals = $this->model->getLOsTotalCompleted($id_course);
		$_arr_js = array();
		foreach ($lo_totals as $id_lo => $total_lo) {
			$_arr_js[] = '{id:"lo_totals_'.$id_lo.'", total:"'.$total_lo.' / '.$total_users.'", '
				.'percent:"'.number_format(($total_lo/$total_users), 2).' %"}';
		}
		$lo_totals_js = implode(",", $_arr_js);
		//WARNING: lo_list and lo_totals must have the same keys order

		$umodel = new UsermanagementAdm();
		$gmodel = new GroupmanagementAdm();

		$params = array(
			'id_course' => $id_course,
			'lo_list' => $this->model->getCourseLOs($id_course),
			'filter_text' => "",
			'filter_selection' => 0,
			'filter_orgchart' => 0,
			'filter_groups' => 0,
			'filter_descendants' => false,
			'is_active_advanced_filter' => false,
			'orgchart_list' => $umodel->getOrgChartDropdownList(),
			'groups_list' => $gmodel->getGroupsDropdownList(),
			'total_users' => (int)$total_users,
			'lo_totals_js' => $lo_totals_js,
			'status_list' => $this->_getJsArrayStatus(),
			'permissions' => $this->permissions
		);

		$this->render('show', $params);
	}


	public function gettabledataTask() {
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		$id_course = Get::req('id_course', DOTY_INT, $_SESSION['idCourse']);
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_selection = Get::req('filter_selection', DOTY_INT, 0);
		$filter_orgchart = Get::req('filter_orgchart', DOTY_INT, 0);
		$filter_groups = Get::req('filter_groups', DOTY_INT, 0);
		$filter_descendants = Get::req('filter_descendants', DOTY_INT, 0) > 0;

		$filter = array(
			'text' => $filter_text,
			'selection' => $filter_selection,
			'orgchart' => $filter_orgchart,
			'groups' => $filter_groups,
			'descendants' => $filter_descendants
		);

		//get total from database and validate the results count
		$total = $this->model->getCourseStatsTotal($id_course, $filter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		$list = $this->model->getCourseStatsList($pagination, $id_course, $filter);


		require_once(_lms_.'/lib/lib.subscribe.php');
		$cman = new CourseSubscribe_Manager();
		$arr_status = $cman->getUserStatus();
		$arr_level = $cman->getUserLevel();

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			$lo_list = $this->model->getCourseLOs($id_course);
			foreach ($list as $record) {
				$_userid = $acl_man->relativeId($record->userid);
				$row = array(
					'id' => (int)$record->idst,
					'userid' => Layout::highlight($_userid, $filter_text),
					'firstname' => Layout::highlight($record->firstname, $filter_text),
					'lastname' => Layout::highlight($record->lastname, $filter_text),
					'status' => isset($arr_status[$record->status]) ? $arr_status[$record->status] : "",
					'status_id' => $record->status,
					'level' => isset($arr_level[$record->level]) ? $arr_level[$record->level] : ""
				);

				//get LO data
				$completed = 0;
				foreach ($lo_list as $idOrg => $lo) {
					if (isset($record->lo_status[$idOrg])) {
						$row['lo_'.$idOrg] = $record->lo_status[$idOrg];
						if ($record->lo_status[$idOrg] == 'completed' || $record->lo_status[$idOrg] == 'passed') $completed++;
					} else {
						$row['lo_'.$idOrg] = "";
					}
				}
				$row['completed'] = $completed;

				$records[] = $row;
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $total,
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}



	public function show_userTask() {
		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			//...
			return;
		}

		//require_once(_lms_.'/lib/lib.subscribe.php');
		//$cman = new CourseSubscribe_Manager();
		//$arr_status = $cman->getUserStatus();
		$smodel = new SubscriptionAlms();
		$arr_status = $smodel->getUserStatusList();

		$acl_man = Docebo::user()->getACLManager();
		$user_info = $acl_man->getUser($id_user, false);
		$course_info = $this->model->getUserCourseInfo($id_course, $id_user);
		$info = new stdClass();
		$info->userid = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
		$info->firstname = $user_info[ACL_INFO_FIRSTNAME];
		$info->lastname = $user_info[ACL_INFO_LASTNAME];
		$info->course_status = isset($arr_status[$course_info->status]) ? $arr_status[$course_info->status] : "";
		$info->first_access = $course_info->date_first_access != "" ? Format::date($course_info->date_first_access, 'datetime') : Lang::t('_NEVER', '');
		$info->last_access = '';
		$info->date_complete = $course_info->date_complete != "" ? Format::date($course_info->date_complete, 'datetime') : Lang::t('_NONE', '');

		$params = array(
			'id_course' => $id_course,
			'id_user' => $id_user,
			'info' => $info,
			'status_list_js' => $this->_getJsArrayLOStatus(),
			'permissions' => $this->permissions
		);

		$this->render('show_user', $params);
	}


	public function getusertabledataTask() {
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		$id_course = Get::req('id_course', DOTY_INT, $_SESSION['idCourse']);
		$id_user = Get::req('id_user', DOTY_INT, 0);

		//get total from database and validate the results count
		$total = $this->model->getCourseUserStatsTotal($id_course, $id_user);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		$pagination = false;/*array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);*/


		$list = $this->model->getCourseUserStatsList($pagination, $id_course, $id_user);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			$lo_list = $this->model->getCourseLOs($id_course);
			foreach ($list as $record) {
				$row = array(
					'id' => (int)$record->idOrg,
					'LO_name' => $record->title,
					'LO_type' => $record->objectType,
					'LO_status' => $record->status != "" ? $record->status : 'not attempted',
					'first_access' => Format::date($record->first_access, 'datetime'),
					'last_access' => Format::date($record->last_access, 'datetime'),
					'first_complete' => Format::date($record->first_complete, 'datetime'),
					'last_complete' => Format::date($record->last_complete, 'datetime'),
					'first_access_timestamp' => Format::toTimestamp($record->first_access == null ? date("U") : $record->first_access ),
					'last_access_timestamp' => Format::toTimestamp($record->last_access == null ? date("U") : $record->last_access ),
					'first_complete_timestamp' => Format::toTimestamp($record->first_complete == null ? date("U") : $record->first_complete ),
					'last_complete_timestamp' => Format::toTimestamp($record->last_complete == null ? date("U") : $record->last_complete )
				);

				$records[] = $row;
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $total,
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}



	public function show_user_objectTask() {
		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			//...
			return;
		}

		$id_lo = Get::req('id_lo', DOTY_INT, -1);
		if ($id_lo <= 0) {
			//...
			return;
		}

		$result_message = "";
		$res = Get::req('res', DOTY_STRING, "");
		switch ($res) {
			case 'ok_reset': $result_message = UIFeedback::info($this->_getErrorMessage('reset success')); break;
			case 'err_reset': $result_message = UIFeedback::error($this->_getErrorMessage('reset error')); break;
		}

		$acl_man = Docebo::user()->getACLManager();
		$user_info = $acl_man->getUser($id_user, false);
		$lo_info = $this->model->getLOInfo($id_lo);
		$course_info = $this->model->getUserCourseInfo($id_course, $id_user);
		$track_info = $this->model->getUserTrackInfo($id_user, $id_lo);
		$smodel = new SubscriptionAlms();
		$arr_statust = $smodel->getUserStatusList();

		$info = new stdClass();
		$info->userid = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
		$info->firstname = $user_info[ACL_INFO_FIRSTNAME];
		$info->lastname = $user_info[ACL_INFO_LASTNAME];

		$info->course_status = isset($arr_status[$course_info->status]) ? $arr_status[$course_info->status] : "";
		$info->course_first_access = $course_info->date_first_access != "" ? Format::date($course_info->date_first_access, 'datetime') : Lang::t('_NEVER', '');
		$info->course_last_access = '';
		$info->course_date_complete = $course_info->date_complete != "" ? Format::date($course_info->date_complete, 'datetime') : Lang::t('_NONE', '');

		$tracked = is_object($track_info);
		$never = Lang::t('_NEVER', 'standard');

		$info->LO_name = $lo_info->title;
		$info->LO_type = $lo_info->objectType;
		$info->status = $tracked ? $track_info->status : "not attempted";
		$info->score = '-';//$track_info->score.' / '.$track_info->max_score;

		$info->first_access = $tracked ? Format::date($track_info->first_access, 'datetime') : $never;
		$info->last_access = $tracked ? Format::date($track_info->last_access, 'datetime') : $never;
		$info->first_complete = $tracked ? Format::date($track_info->first_complete, 'datetime') : $never;
		$info->last_complete = $tracked ? Format::date($track_info->last_complete, 'datetime') : $never;

		$id_track = $this->model->getTrackId($id_lo, $id_user);
		$params = array(
			'id_course' => $id_course,
			'id_user' => $id_user,
			'id_lo' => $id_lo,
			'result_message' => $result_message,
			'from_user' => Get::req('from_user', DOTY_INT, 0) > 0,
			'tracked' => $tracked,
			'info' => $info,
			'object_lo' => $this->model->getLOTrackObject($id_track, $lo_info->objectType),
			'permissions' => $this->permissions
		);

		$this->render('show_user_object', $params);
	}



	public function show_objectTask() {
		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$id_lo = Get::req('id_lo', DOTY_INT, -1);
		if ($id_lo <= 0) {
			//...
			return;
		}

		$lo_info = $this->model->getLOInfo($id_lo);
		//$track_info = $this->model->getTrackInfo($id_lo, $id_user);

		$info = new stdClass();

		$info->LO_name = $lo_info->title;
		$info->LO_type = $lo_info->objectType;
		$info->status = '';//$track_info->status;
		$info->score = '';//$track_info->score.' / '.$track_info->max_score;

		$info->first_access = '';
		$info->last_access = '';
		$info->first_complete = '';
		$info->last_complete = '';

		$params = array(
			'id_course' => $id_course,
			'id_lo' => $id_lo,
			'info' => $info,
			'object_lo' => $this->model->getLOTrackObject(false, $lo_info->type),
			'permissions' => $this->permissions
		);

		$this->render('show_object', $params);
	}



	public function resetTask() {
		if (!$this->permissions['mod']) {
			//...
			return;
		}

		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			//...
			return;
		}

		$id_lo = Get::req('id_lo', DOTY_INT, -1);
		if ($id_lo <= 0) {
			//...
			return;
		}

		$res = $this->model->resetTrack($id_lo, $id_user);
		Util::jump_to('index.php?r=coursestats/show_user_object&id_user='.(int)$id_user.'&id_lo='.(int)$id_lo.'&res='.($res ? 'ok_reset' : 'err_reset'));
	}


	public function inline_editorTask() {
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid course'));
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid user'));
			echo $this->json->encode($output);
			return;
		}

		$old_value = Get::req('old_value', DOTY_MIXED, false);
		$new_value = Get::req('new_value', DOTY_MIXED, false);

		if ($old_value === false || $new_value === false) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage("invalid data"));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$col = Get::req('col', DOTY_STRING, "");
		switch ($col) {
			case "status": {
				$smodel = new SubscriptionAlms($id_course);
				$slist = $smodel->getUserStatusList();
				$res = $smodel->updateUserStatus($id_user, $new_value);
				$output['success'] = $res ? true : false;
				$output['new_value'] = isset($slist[$new_value]) ? $slist[$new_value] : "";
			} break;

			default: {
				$output['success'] = false;
				$output['message'] = $this->_getErrorMessage("invalid column");
			}
		}

		echo $this->json->encode($output);
	}


	public function user_inline_editorTask() {
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid course'));
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid user'));
			echo $this->json->encode($output);
			return;
		}

		$id_lo = Get::req('id_lo', DOTY_INT, -1);
		if ($id_lo <= 0) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid lo'));
			echo $this->json->encode($output);
			return;
		}

		$old_value = Get::req('old_value', DOTY_MIXED, false);
		$new_value = Get::req('new_value', DOTY_MIXED, false);

		if ($old_value === false || $new_value === false) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage("invalid data"));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$col = Get::req('col', DOTY_STRING, "");
		switch ($col) {
			case "LO_status": {
				$res = $this->model->changeLOUserStatus($id_lo, $id_user, $new_value);
				$output['success'] = $res ? true : false;
				$output['new_value'] = $new_value;
			} break;


			case "first_access": {
				$new_date = date("Y-m-d H:i:s", $new_value);
				$res = $this->model->changeLOUserFirstAccess($id_lo, $id_user, $new_date);
				$output['new_value'] = Format::date($new_date);
			} break;

			case "last_access": {
				$new_date = date("Y-m-d H:i:s", $new_value);
				$res = $this->model->changeLOUserLastAccess($id_lo, $id_user, $new_date);
				$output['success'] = $res ? true : false;
				$output['new_value'] = Format::date($new_date);
			} break;

			case "first_complete": {
				$new_date = date("Y-m-d H:i:s", $new_value);
				$res = $this->model->changeLOUserFirstComplete($id_lo, $id_user, $new_date);
				$output['success'] = $res ? true : false;
				$output['new_value'] = Format::date($new_date);
			} break;

			case "last_complete": {
				$new_date = date("Y-m-d H:i:s", $new_value);
				$res = $this->model->changeLOUserLastComplete($id_lo, $id_user, $new_date);
				$output['success'] = $res ? true : false;
				$output['new_value'] = Format::date($new_date);
			} break;

			default: {
				$output['success'] = false;
				$output['message'] = $this->_getErrorMessage("invalid column");
			}
		}

		echo $this->json->encode($output);
	}



	protected function _formatCsvValue($value, $delimiter) {
		$formatted_value = str_replace($delimiter, '\\'.$delimiter, $value);
		return $delimiter.$formatted_value.$delimiter;
	}

	public function export_csvTask() {
		//check permissions
		if (!$this->permissions['view']) Util::jump_to('index.php?r=coursestats/show');

		require_once(_base_.'/lib/lib.download.php');

		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$separator = ',';
		$delimiter = '"';
		$line_end = "\r\n";

		$output = "";
		$lo_list = $this->model->getCourseLOs($id_course);
		$lo_total = count($lo_list);

		$head = array();
		$head[] = $this->_formatCsvValue(Lang::t('_USERNAME', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_FULLNAME', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_LEVEL', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_STATUS', 'standard'), $delimiter);
		foreach ($lo_list as $id_lo => $lo_info) {
			$head[] = $this->_formatCsvValue($lo_info->title, $delimiter);
		}
		$head[] = $this->_formatCsvValue(Lang::t('_COMPLETED', 'course'), $delimiter);

		$output .= implode($separator, $head).$line_end;

		$records = $this->model->getCourseStatsList(false, $id_course, false);
		if (!empty($records)) {
			$acl_man = Docebo::user()->getAclManager();

			require_once(_lms_.'/lib/lib.subscribe.php');
			$cman = new CourseSubscribe_Manager();
			$arr_status = $cman->getUserStatus();
			$arr_level = $cman->getUserLevel();

			if (is_array($records)) {
				foreach ($records as $record) {
					$row = array();
					$row[] = $acl_man->relativeId($record->userid);
					$row[] = $record->firstname.' '.$record->lastname;
					$row[] = isset($arr_level[$record->level]) ? $arr_level[$record->level] : "";
					$row[] = isset($arr_status[$record->status]) ? $arr_status[$record->status] : "";
					$num_completed = 0;
					foreach ($lo_list as $id_lo => $lo_info) {
						$_lo_status = isset($record->lo_status[$id_lo]) ? $record->lo_status[$id_lo] : "";
						$row[] = $_lo_status;
						if ($_lo_status=='completed' || $_lo_status=='passed') $num_completed++;
					}
					$row[] = $num_completed.' / '.$lo_total;

					//format row and produce a string text to add to CSV file
					$csv_row = array();
					foreach ($row as $row_data) {
						$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
					}

					$output .= implode($separator, $csv_row).$line_end;
				}
			}
		}

		sendStrAsFile($output, 'coursestats_export_'.date("Ymd").'.csv');
	}

}

?>