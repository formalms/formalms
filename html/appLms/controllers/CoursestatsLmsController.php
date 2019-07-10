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
			'view' => true,
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
            
		$view_all_perm = checkPerm('view_all', true, 'coursestats');
            
		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$total_users = $this->model->getCourseStatsTotal($id_course, false);
                
        //apply sub admin filters, if needed
        if( !$view_all_perm ) {
            $pagination = array(
                    'startIndex' => 0,
                    'results' => 9999999999,
                    'sort' => "",
                    'dir' => "asc"
            );

            $list = $this->model->getCourseStatsList($pagination, $id_course, $filter);

            //filter users
            require_once(_base_.'/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
            $idx = 0;
            foreach ($list as $record) {
                if ( !in_array ($record->idst, $ctrl_users) ) {
                    // Elimino gli studenti non amministrati
                    unset ($list[$idx]);
                }
                $idx ++;
            }
            $total_users = count($list);
        }
                
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
		$view_all_perm = checkPerm('view_all', true, 'coursestats');

		$startIndex = Get::req('start', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('length', DOTY_INT, $results);

		$dir = Get::req('dir', DOTY_STRING, "asc");

		$id_course = Get::req('id_course', DOTY_INT, $_SESSION['idCourse']);

		$pagination = array(
			'startIndex' => $startIndex,
			'rowsPerPage' => $rowsPerPage,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		if ($order = $_REQUEST['order']) {
			$pagination['order_column'] = $order[0]['column'];
			$pagination['order_dir'] = $order[0]['dir'];
		}

		if ($search = $_REQUEST['search']) {
			$pagination['search'] = $search['value'];
		} else {
			$pagination['search'] = null;
		}
                
		//get total from database and validate the results count
		$total = $this->model->getCourseStatsTotal($id_course, $pagination, false);
		$total_filtered = $this->model->getCourseStatsTotal($id_course, $pagination, true);

		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		$list = $this->model->getCourseStatsList($pagination, $id_course);

		require_once(_lms_.'/lib/lib.subscribe.php');
		$cman = new CourseSubscribe_Manager();
		$arr_status = $cman->getUserStatus();
		$arr_level = $cman->getUserLevel();

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
                
        //apply sub admin filters, if needed
        if( !$view_all_perm ) {
            //filter users
            require_once(_base_.'/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
            $idx = 0;
            foreach ($list as $record) {
                if ( !in_array ($record->idst, $ctrl_users) ) {
                    // Elimino gli studenti non amministrati
                    unset ($list[$idx]);
                }
                $idx ++;
            }
            $total = count($list);
        }
                
		if (is_array($list)) {
			$lo_list = $this->model->getCourseLOs($id_course);
			foreach ($list as $record) {
				$_userid = $acl_man->relativeId($record->userid);
				$row = array(
					// 'id' => (int)$record->idst,
					'userid' => '<a href="./index.php?r=coursestats/show_user&id_user='.(int)$record->idst.'">'.Layout::highlight($_userid, $filter_text).'</a>',
					'firstname' => Layout::highlight($record->lastname, $filter_text).' '.Layout::highlight($record->firstname, $filter_text),
					'level' => isset($arr_level[$record->level]) ? $arr_level[$record->level] : "",
					'status' => isset($arr_status[$record->status]) ? $arr_status[$record->status] : "",
				);

				//get LO data
				$completed = 0;
				foreach ($lo_list as $idOrg => $lo) {
					if (isset($record->lo_status[$idOrg])) {
						if ($record->lo_status[$idOrg] === 'completed') {
							$row['lo_'.$idOrg] = Lang::t('_COMPLETED', 'standard');
						} else if ($record->lo_status[$idOrg] == 'passed') {
							$row['lo_'.$idOrg] = Lang::t('passed', 'standard');
						} else if ($record->lo_status[$idOrg] == 'failed') {
							$row['lo_'.$idOrg] = Lang::t('failed', 'standard');
						} else {
							$row['lo_'.$idOrg] = Lang::t($record->lo_status[$idOrg], 'standard');
						}
						if ($record->lo_status[$idOrg] == 'completed' || $record->lo_status[$idOrg] == 'passed') $completed++;
					} else {
						$row['lo_'.$idOrg] = "Non iniziato";
					}
				}
				$row['completed'] = $completed.' / '.count($lo_list);

				$records[] = array_values($row);
			}
		}

		echo $this->json->encode([
			'data' => $records,
			'recordsFiltered' => $total_filtered,
			'recordsTotal' => $total,
		]);
		exit;
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
		$info->first_access = $course_info->date_first_access != "" ? Format::date($course_info->date_first_access, 'datetime', true) : Lang::t('_NEVER', '');
		$info->last_access = '';
		$info->date_complete = $course_info->date_complete != "" ? Format::date($course_info->date_complete, 'datetime', true) : Lang::t('_NONE', '');

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
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$id_course = Get::req('id_course', DOTY_INT, $_SESSION['idCourse']);
		$id_user = Get::req('id_user', DOTY_INT, 0);

		$pagination = [];
		$pagination['startIndex'] = Get::req('start', DOTY_INT, 0);
		$pagination['rowsPerPage'] = Get::req('length', DOTY_INT, 0);
		if ($search = $_REQUEST['search']) {
			$pagination['search'] = $search['value'];
		} else {
			$pagination['search'] = null;
		}

		if ($order = $_REQUEST['order']) {
			$pagination['order_column'] = $order[0]['column'];
			$pagination['order_dir'] = $order[0]['dir'];
		}

		$list = $this->model->getCourseUserStatsList($pagination, $id_course, $id_user);
		$total = $this->model->countTotalCourseUsersStats($id_course, $id_user, $pagination['search'], false);
		$total_filtered = $this->model->countTotalCourseUsersStats($id_course, $id_user, $pagination['search'], true);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			$lo_list = $this->model->getCourseLOs($id_course);
			foreach ($list as $record) {
				$path = str_replace('/root/', '', $record->path);
				$pathArray = explode('/', $path);
				foreach ($pathArray as &$p) {
					$p *= 1;
				}
				$path = implode('/', $pathArray);

				if ($last_access = $this->model->getUserScormHistoryTrackInfo($id_user, $record->idOrg)) {
					$seconds_diff = strtotime("1970-01-01 ".end($last_access)[3]." UTC");
					$last_access = date('Y-m-d H:i:s', strtotime(end($last_access)[0]) - $seconds_diff);
				} else {
					$last_access = $record->first_access;
				}

				$row = array(
					'id' => $record->idOrg,
					'path' => $path,
					'LO_name' => $record->title,
					'LO_type' => $record->objectType ?: 'folder',
					'LO_status' => $record->status,
					'first_access' => $record->first_access,
					'last_access' => $last_access,
					'history' => $record->history,
					'totaltime' => $this->model->roundTime($record->totaltime),
					'score' => $record->score,
					'edit' => $record->edit,
				);

				$records[] = $row;
			}
		}

		echo $this->json->encode([
			'data' => ($records),
			'recordsTotal' => $total,
			'recordsFiltered' => $total_filtered,
		]);
		exit;
	}

	// esportazione xls
	public function getusertabledataxls($id_course, $id_user) {
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		//get total from database and validate the results count
		$total = $this->model->getCourseUserStatsTotal($id_course, $id_user);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		$pagination = false;
		$list = $this->model->getCourseUserStatsList($pagination, $id_course, $id_user);

		//format models' data
		$records = array();
		$output = '';
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			$lo_list = $this->model->getCourseLOs($id_course);
			foreach ($list as $record) {

				if ($last_access = $this->model->getUserScormHistoryTrackInfo($id_user, $record->idOrg)) {
					$seconds_diff = strtotime("1970-01-01 ".end($last_access)[3]." UTC");
					$last_access = date('Y-m-d H:i:s', strtotime(end($last_access)[0]) - $seconds_diff);
				} else {
					$last_access = $record->first_access;
				}

				$output.= '<tr>';
				$row = array(
					'LO_name' => $record->title,
					'LO_type' => $record->objectType,
					'LO_status' => $record->status != "" ? $record->status : 'not attempted',
					'first_access' => Format::date($record->first_access, 'datetime', true),
					'last_access' => Format::date($last_access, 'datetime', true),
					'history' => $record->history,
					'totaltime' => $this->model->roundTime($record->totaltime),
					'score' => $record->score,
					'edit' => $record->edit,
				);

				foreach ($row as $row_data) {
					$output .= '<td>'.$row_data.'</td>';
				}
				$output .= '</tr>';
			}
		}
		return $output;
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
		$track_history = $this->model->getUserScormHistoryTrackInfo($id_user, $id_lo);
		$total_session_time = $this->model->getUserScormHistoryTrackTotaltime($id_user, $id_lo);
		
		$smodel = new SubscriptionAlms();
		$arr_status = $smodel->getUserStatusList();

		$info = new stdClass();
		$info->userid = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
		$info->firstname = $user_info[ACL_INFO_FIRSTNAME];
		$info->lastname = $user_info[ACL_INFO_LASTNAME];

		$info->course_status = isset($arr_status[$course_info->status]) ? $arr_status[$course_info->status] : "-";
		$info->course_first_access = $course_info->date_first_access != "" ? Format::date($course_info->date_first_access, 'datetime', true) : Lang::t('_NEVER', '');
		$info->course_last_access = '';
		$info->course_date_complete = $course_info->date_complete != "" ? Format::date($course_info->date_complete, 'datetime', true) : Lang::t('_NONE', '');

		$tracked = is_object($track_info);
		$never = Lang::t('_NEVER', 'standard');

		$info->LO_name = $lo_info->title;
		$info->LO_type = $lo_info->objectType;
		$info->status = $tracked ? Lang::t($track_info->status, "standard") : "not attempted";
		$info->score = $track_history ? $track_history[count($track_history)-1][1] : '-';

		$info->first_access = $tracked ? Format::date($track_info->first_access, 'datetime', true) : $never;
		$info->last_access = $tracked ? Format::date($track_info->last_access, 'datetime', true) : $never;
		$info->first_complete = $tracked ? Format::date($track_info->first_complete, 'datetime', true) : $never;
		$info->last_complete = $tracked ? Format::date($track_info->last_complete, 'datetime', true) : $never;

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
			'track_history'=>$track_history,
			'total_session_time'=>$total_session_time,
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
		$info = new stdClass();

		$info->LO_name = $lo_info->title;
		$info->LO_type = $lo_info->objectType;
		$info->status = '';
		$info->score = '';

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

        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        require_once(_lms_.'/lib/lib.param.php');
        
        $repoDb = new OrgDirDb($id_course);
        $folder = $repoDb->getFolderById( $id_lo );
        $id_resource = $folder->otherValues[REPOFIELDIDRESOURCE];
        $id_param = $folder->otherValues[ORGFIELDIDPARAM];
        $idReference = getLOParam($id_param, 'idReference');
        
        require_once(_lms_.'/class.module/track.object.php');
        $lo_info = $this->model->getLOInfo($id_lo);
        
        switch($lo_info->objectType){
            case 'faq':
                require_once(_lms_.'/class.module/track.faq.php');
                $itemtrack = new Track_Faq(null);
                break;
            case 'glossary': 
                require_once(_lms_.'/class.module/track.glossary.php');
                $itemtrack = new Track_Glossary(null);
                break;
            case 'htmlpage': 
                require_once(_lms_.'/class.module/track.htmlpage.php');
                $itemtrack = new Track_Htmlpage(null);
                break;
            case 'item': 
                require_once(_lms_.'/class.module/track.item.php');
                $itemtrack = new Track_Item(null, $id_user);
                break;
            case 'link': 
                require_once(_lms_.'/class.module/track.link.php');
                $itemtrack = new Track_Link(null);
                break;
            case 'poll': 
                require_once(_lms_.'/class.module/track.poll.php');
                $itemtrack = new Track_Poll(null);
                break;
            case 'scormorg':
                require_once(_lms_.'/modules/scorm/scorm_items_track.php');
                $itemtrack = new Scorm_ItemsTrack(null, $GLOBALS['prefix_lms']);
                break;
            case 'test': 
                require_once(_lms_.'/class.module/track.test.php');
                $itemtrack = new Track_Test(null);
                break;
        }
        
        list( $exist, $idTrack ) = $itemtrack->getIdTrack( $idReference, $id_user, $id_resource, TRUE );
        
        if( !$exist ){
                require_once( _lms_ . '/class.module/track.object.php' );
                $track_lo = new Track_Object( $idTrack );
                $track_lo->createTrack( $idReference, $idTrack, $id_user, date("Y-m-d H:i:s"), 'not attempted', $lo_info->objectType);
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
				$res = $this->model->changeLOUserFirstAccess($id_lo, $id_user, $new_value);
				$output['new_value'] = $new_value;
			} break;

			case "last_access": {
				$res = $this->model->changeLOUserLastAccess($id_lo, $id_user, $new_value);
				$output['success'] = $res ? true : false;
				$output['new_value'] = $new_value;
			} break;

			case "first_complete": {
				$res = $this->model->changeLOUserFirstComplete($id_lo, $id_user, $new_value);
				$output['success'] = $res ? true : false;
				$output['new_value'] = $new_value;
			} break;

			case "last_complete": {
				$res = $this->model->changeLOUserLastComplete($id_lo, $id_user, $new_value);
				$output['success'] = $res ? true : false;
				$output['new_value'] = $new_value;
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

	protected function _formatXlsValue($str) {
		$str = preg_replace("/\t/", "\\t", $str); 
		$str = preg_replace("/\r?\n/", "\\n", $str); 
		if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
		return $str;
	}

	public function export_csvTask() {
		//check permissions
		if (!$this->permissions['view']) Util::jump_to('index.php?r=coursestats/show');
                
		$view_all_perm = checkPerm('view_all', true, 'coursestats');

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
                
                //apply sub admin filters, if needed
                if( !$view_all_perm ) {
                        //filter users
                        require_once(_base_.'/lib/lib.preference.php');
                        $ctrlManager = new ControllerPreference();
                        $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
                        $idx = 0;
                        foreach ($records as $record) {
                            if ( !in_array ($record->idst, $ctrl_users) ) {
                                // Elimino gli studenti non amministrati
                                unset ($records[$idx]);
                            }
                            $idx ++;
                        }
                }
                
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

	
	public function export_csv3Task() {
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
		$pagination = false;
		$output = "";
		$lo_list = $this->model->getCourseLOs($id_course);
		$lo_total = count($lo_list);

		$records = $this->model->getCourseStatsList(false, $id_course, false);
		if (!empty($records)) {
			$acl_man = Docebo::user()->getAclManager();

			require_once(_lms_.'/lib/lib.subscribe.php');
			$cman = new CourseSubscribe_Manager();
			$arr_status = $cman->getUserStatus();
			$arr_level = $cman->getUserLevel();

			if (is_array($records)) {
				foreach ($records as $record) {
					// Dati anagrafici partecipante
					$rowa = array();
					$rowa[] = Lang::t('_PARTICIPANT_DATA', 'standard')." :";
					$rowa[] = Lang::t('_USERNAME', 'standard')." : ".$acl_man->relativeId($record->userid);
					$rowa[] = Lang::t('_FULLNAME', 'standard')." : ".$record->firstname.' '.$record->lastname;
					$rowa[] = Lang::t('_LEVEL', 'standard')." : ";
					$rowa[] = isset($arr_level[$record->level]) ? $arr_level[$record->level] : "";
					$rowa[] = Lang::t('_STATUS', 'standard')." : ";
					$rowa[] = isset($arr_status[$record->status]) ? $arr_status[$record->status] : "";
					$rowa[] =  "";
					$rowa[] =  "";
					$rowa[] =  "";
					$rowa[] =  "";
					
					
					$num_completed = 0;
					$csv_row = array();
					foreach ($rowa as $row_data) {
						$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
							}
					$output .= implode($separator, $csv_row).$line_end;
					//Intestazione  LO
					$head = array();
					$head[] = $this->_formatCsvValue(Lang::t('_SUBJECT_NAME', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_TYPE', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_STATUS', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_DATE_FIRST_ACCESS', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_DATE_LAST_ACCESS', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_ACCESS_IN_DETAIL', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_DATE', 'standard'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_DURATION', 'course'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_RESULT', 'course'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_TOTAL_ACCESS_TIME', 'course'), $delimiter);
					$head[] = $this->_formatCsvValue(Lang::t('_SCORE', 'standard'), $delimiter);
		

					$output .= implode($separator, $head).$line_end;
					// dettaglio LO
					$list = $this->model->getCourseUserStatsList2csv($pagination, $id_course, $record->idst);
					
					if (is_array($list)) {
						foreach ($list as $recordlo) {						
							$history = $recordlo->history;
						
							if (is_array($history)) {
								foreach ($history as $key=>$history_rec) {
									if ($key == 0) {
										$row = array();
										$row = array(
											'LO_name' => $recordlo->title,
											'LO_type' => $recordlo->objectType,
											'LO_status' => $recordlo->status != "" ? $recordlo->status : 'not attempted',
											'first_access' => Format::date($recordlo->first_access, 'datetime', true),
											'last_access' => Format::date($recordlo->last_access, 'datetime', true),
											'history_attempt' => $key + 1  ,
											'history_date' =>  Format::date($history_rec[0],'datetime', true),
											'history_duration' =>  $history_rec[3],
											'history_status' =>  $history_rec[4],
											'totaltime' => $recordlo->totaltime,
											'score'=>$recordlo->score
										);
									
									} else {
										$row = array();
										$row = array(
											'LO_name' => '',
											'LO_type' => '',
											'LO_status' => '',
											'first_access' => '',
											'last_access' => '',
											'history_attempt' => $key + 1,
											'history_date' =>  Format::date($history_rec[0],'datetime', true),
											'history_duration' =>  $history_rec[3],
											'history_status' =>  $history_rec[4],
											'totaltime' => '',
											'score'=>''
										);						
									}
									// aggiungi una riga per ogni record storico accessi
									$csv_row = array();
									foreach ($row as $row_data) {
										$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
									}
									$output .= implode($separator, $csv_row).$line_end;
								}
					
							} else {
								$row = array();
								$row = array(
									'LO_name' => $recordlo->title,
									'LO_type' => $recordlo->objectType,
									'LO_status' => $recordlo->status != "" ? $record->status : 'not attempted',
									'first_access' => Format::date($recordlo->first_access, 'datetime', true),
									'last_access' => Format::date($recordlo->last_access, 'datetime', true),
									'history_attempt' => 'nd'  ,
									'history_date' =>  'nd',
									'history_duration' =>  'nd',
									'history_status' =>  'nd',
									'totaltime' => $recordlo->totaltime,
									'score'=>$recordlo->score
								);
								$csv_row = array();
							
								foreach ($row as $row_data) {
									$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
								}
								$output .= implode($separator, $csv_row).$line_end;
							}
						} // each recordlo
					} // is array list
					//format row and produce a string text to add to CSV file
				}
			}
		}
		sendStrAsFile($output, 'coursestats_export_'.date("Ymd").'.csv');
	}

	public	function export_XlsTask() {
		//check permissions
		if (!$this->permissions['view']) Util::jump_to('index.php?r=coursestats/show');

		require_once(_base_.'/lib/lib.download.php');

		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$output = "";
		$lo_list = $this->model->getCourseLOs($id_course);
		$lo_total = count($lo_list);
		$output = '<table border="1">';
		$records = $this->model->getCourseStatsList(false, $id_course, false);
		if (!empty($records)) {
			$acl_man = Docebo::user()->getAclManager();

			require_once(_lms_.'/lib/lib.subscribe.php');
			$cman = new CourseSubscribe_Manager();
			$arr_status = $cman->getUserStatus();
			$arr_level = $cman->getUserLevel();

			if (is_array($records)) {
				foreach ($records as $record) {
					// Dati anagrafici partecipante
					$output .= '<tr>';
					$rowa = array();
					$rowa[] = Lang::t('_PARTICIPANT_DATA', 'standard')." :";
					$rowa[] = Lang::t('_USERNAME', 'standard')." : ".$acl_man->relativeId($record->userid);
					$rowa[] = Lang::t('_FULLNAME', 'standard')." : ".$record->firstname.' '.$record->lastname;
					$rowa[] = Lang::t('_LEVEL', 'standard')." : ";
					$rowa[] = isset($arr_level[$record->level]) ? $arr_level[$record->level] : "";
					$rowa[] = Lang::t('_STATUS', 'standard')." : ";
					$rowa[] = isset($arr_status[$record->status]) ? $arr_status[$record->status] : "";
					$rowa[] =  "";
					
					
					
					$num_completed = 0;
					
					foreach ($rowa as $row_data) {
						$output .= '<th>'.$row_data.'</th>';
					}
					$output .= '</tr>';
					//Intestazione  LO
					$output .= '<tr>';
					$head = array();
					$head[] = Lang::t('_SUBJECT_NAME', 'standard');
					$head[] = Lang::t('_TYPE', 'standard');
					$head[] = Lang::t('_STATUS', 'standard');
					$head[] = Lang::t('_DATE_FIRST_ACCESS', 'standard');
					$head[] = Lang::t('_DATE_LAST_ACCESS', 'standard');
					$head[] = Lang::t('_ACCESS_IN_DETAIL', 'standard');
					$head[] = Lang::t('_TOTAL_ACCESS_TIME', 'course');
					$head[] = Lang::t('_SCORE', 'standard');
					foreach ($head as $row_data) {
						$output .= '<th>'.$row_data.'</th>';
					}
					$output .= '</tr>';

					$dettaglio = $this->getusertabledataxls($id_course, $record->idst);
					// dettaglio LO

					if ($dettaglio) {
						$output .= $dettaglio;
					}
				}
			}
		}
		$output .= '</table>';
		sendStrAsFile($output, 'coursestats_export_'.date("Ymd").'.xls');
		exit();
	}
	
	public	function export_Xls2Task() {
		//check permissions
		if (!$this->permissions['view']) Util::jump_to('index.php?r=coursestats/show');

		require_once(_base_.'/lib/lib.download.php');

		$id_course = Get::req('id_course', DOTY_INT, $_SESSION['idCourse']);
		$id_user = Get::req('id_user', DOTY_INT, 0);
		if ((int)$id_course <= 0) {
			//...
			return;
		}
		$output = "";
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
		$info->first_access = $course_info->date_first_access != "" ? Format::date($course_info->date_first_access, 'datetime', true) : Lang::t('_NEVER', '');
		$info->last_access = '';
		$info->date_complete = $course_info->date_complete != "" ? Format::date($course_info->date_complete, 'datetime', true) : Lang::t('_NONE', '');

		$output = '<table border="1">';
		// Dati anagrafici partecipante
		$output .= '<tr>';
		$rowa = array();
		$rowa[] =  Lang::t('_PARTICIPANT_DATA', 'standard');
		$rowa[] = Lang::t('_USERNAME', 'standard')." : ".$acl_man->relativeId($info->userid);
		$rowa[] = Lang::t('_FULLNAME', 'standard')." : ".$info->firstname.' '.$info->lastname;
		$rowa[] = "";
		$rowa[] = "";
		$rowa[] = Lang::t('_DATE_FIRST_ACCESS', 'standard')." : ".$info->first_access;
		$rowa[] = Lang::t('_STATUS', 'standard')." : ".$info->course_status;
		$rowa[] =  "";
		
		$num_completed = 0;
		
		foreach ($rowa as $row_data) {
			$output .= '<th>'.$row_data.'</th>';
		}
		$output .= '</tr>';
		//Intestazione  LO
		$output .= '<tr>';
		$head = array();
		$head[] = Lang::t('_SUBJECT_NAME', 'standard');
		$head[] = Lang::t('_TYPE', 'standard');
		$head[] = Lang::t('_STATUS', 'standard');
		$head[] = Lang::t('_DATE_FIRST_ACCESS', 'standard');
		$head[] = Lang::t('_DATE_LAST_ACCESS', 'standard');
		$head[] = Lang::t('_ACCESS_IN_DETAIL', 'standard');
		$head[] = Lang::t('_TOTAL_ACCESS_TIME', 'course');
		$head[] = Lang::t('_SCORE', 'standard');
		foreach ($head as $row_data) {
			$output .= '<th>'.$row_data.'</th>';
		}
		$output .= '</tr>';

		$dettaglio =  $this->getusertabledataxls($id_course, $id_user );
		// dettaglio LO
		
		if ($dettaglio) {
			$output .= $dettaglio;
		}
			
		$output .= '</table>';

		sendStrAsFile($output, 'coursestats_export_'.date("Ymd").'.xls');

		exit();
	}
	
	public function export_Csv4Task() {
		//check permissions
		if (!$this->permissions['view']) Util::jump_to('index.php?r=coursestats/show');

		require_once(_base_.'/lib/lib.download.php');

		$id_course = isset($_SESSION['idCourse']) && $_SESSION['idCourse']>0 ? $_SESSION['idCourse'] : false;
		if ((int)$id_course <= 0) {
			//...
			return;
		}

		$separator = "\t";
		$delimiter = "'";
		$line_end = "\r\n";
		$pagination = false;
		$output = "";
		$lo_list = $this->model->getCourseLOs($id_course);
		$lo_total = count($lo_list);

		$records = $this->model->getCourseStatsList(false, $id_course, false);
		if (!empty($records)) {
			$acl_man = Docebo::user()->getAclManager();

			require_once(_lms_.'/lib/lib.subscribe.php');
			$cman = new CourseSubscribe_Manager();
			$arr_status = $cman->getUserStatus();
			$arr_level = $cman->getUserLevel();

			if (is_array($records)) {
				foreach ($records as $record) {
					// Dati anagrafici partecipante
					$rowa = array();
					$rowa[] = Lang::t('_PARTICIPANT_DATA', 'standard')." :";
					$rowa[] = Lang::t('_USERNAME', 'standard')." : ".$acl_man->relativeId($record->userid);
					$rowa[] = Lang::t('_FULLNAME', 'standard')." : ".$record->firstname.' '.$record->lastname;
					$rowa[] = Lang::t('_LEVEL', 'standard')." : ";
					$rowa[] = isset($arr_level[$record->level]) ? $arr_level[$record->level] : "";
					$rowa[] = Lang::t('_STATUS', 'standard')." : ";
					$rowa[] = isset($arr_status[$record->status]) ? $arr_status[$record->status] : "";
					$rowa[] =  "";
					$rowa[] =  "";
					$rowa[] =  "";
					$rowa[] =  "";
					
					
					$num_completed = 0;
					$csv_row = array();
					foreach ($rowa as $row_data) {
						$csv_row[] = $this->_formatXlsValue($row_data);
					}
					$output .= implode($separator, $csv_row).$line_end;
					//Intestazione  LO
					$head = array();
					$head[] = Lang::t('_SUBJECT_NAME', 'standard');
					$head[] = Lang::t('_TYPE', 'standard');
					$head[] = Lang::t('_STATUS', 'standard');
					$head[] = Lang::t('_DATE_FIRST_ACCESS', 'standard');
					$head[] = Lang::t('_DATE_LAST_ACCESS', 'standard');
					$head[] = Lang::t('_ACCESS_IN_DETAIL', 'standard');
					$head[] = Lang::t('_DATE', 'standard');
					$head[] = Lang::t('_DURATION', 'course');
					$head[] = Lang::t('_RESULT', 'course');
					$head[] = Lang::t('_TOTAL_ACCESS_TIME', 'course');
					$head[] = Lang::t('_SCORE', 'standard');

					$output .= implode($separator, $head).$line_end;
					// dettaglio LO
					$list = $this->model->getCourseUserStatsList2csv($pagination, $id_course, $record->idst);
					
					if (is_array($list)) {			
						foreach ($list as $recordlo) {
							$history = $recordlo->history;

						 	if (is_array($history)) {
					
								foreach ($history as $key=>$history_rec) {
									if ($key == 0) {
										$row = array();
										$row = array(
											'LO_name' => $recordlo->title,
											'LO_type' => $recordlo->objectType,
											'LO_status' => $recordlo->status != "" ? $recordlo->status : 'not attempted',
											'first_access' => Format::date($recordlo->first_access, 'datetime', true),
											'last_access' => Format::date($recordlo->last_access, 'datetime', true),
											'history_attempt' => $key + 1  ,
											'history_date' =>  Format::date($history_rec[0],'datetime', true),
											'history_duration' =>  $history_rec[3],
											'history_status' =>  $history_rec[4],
											'totaltime' => $recordlo->totaltime,
											'score'=>$recordlo->score
										);
									
									} else {
										$row = array();
										$row = array(
											'LO_name' => '',
											'LO_type' => '',
											'LO_status' => '',
											'first_access' => '',
											'last_access' => '',
											'history_attempt' => $key + 1,
											'history_date' =>  Format::date($history_rec[0],'datetime', true),
											'history_duration' =>  $history_rec[3],
											'history_status' =>  $history_rec[4],
											'totaltime' => '',
											'score'=>''
										);						
									}
								 	// aggiungi una riga per ogni record storico accessi
									$csv_row = array();
									foreach ($row as $row_data) {
										$csv_row[] = $this->_formatXlsValue($row_data);
									}
									$output .= implode($separator, $csv_row).$line_end;
								}
							} else {
								$row = array();
								$row = array(
									'LO_name' => $recordlo->title,
									'LO_type' => $recordlo->objectType,
									'LO_status' => $recordlo->status != "" ? $record->status : 'not attempted',
									'first_access' => Format::date($recordlo->first_access, 'datetime', true),
									'last_access' => Format::date($recordlo->last_access, 'datetime', true),
									'history_attempt' => 'nd'  ,
									'history_date' =>  'nd',
									'history_duration' =>  'nd',
									'history_status' =>  'nd',
									'totaltime' => $recordlo->totaltime,
									'score'=>$recordlo->score
								);
								$csv_row = array();

								foreach ($row as $row_data) {
									$csv_row[] = $this->_formatXlsValue($row_data);
								}
								$output .= implode($separator, $csv_row).$line_end;
							}
						} // each recordlo
					} // is array list
				}
			}
		}
		sendStrAsFile($output, 'coursestats_export_'.date("Ymd").'.csv');
		exit();
	}

	public function export_csv2Task() {
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
		$pagination = false;
		$id_course = Get::req('id_course', DOTY_INT, $_SESSION['idCourse']);
		$id_user = Get::req('id_user', DOTY_INT, 0);
		
		$output = "";
		
		$head = array();
		$head[] = $this->_formatCsvValue(Lang::t('_NAME', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_TYPE', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_STATUS', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_DATE_FIRST_ACCESS', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_DATE_LAST_ACCESS', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_ACCESS_IN_DETAIL', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_DATE', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_DURATION', 'course'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_RESULT', 'course'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_TOTAL_ACCESS_TIME', 'course'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_SCORE', 'standard'), $delimiter);
		
		$output .= implode($separator, $head).$line_end;

		$list = $this->model->getCourseUserStatsList2csv($pagination, $id_course, $id_user);
		
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$row = array(
					'LO_name' => $record->title,
					'LO_type' => $record->objectType,
					'LO_status' => $record->status != "" ? $record->status : 'not attempted',
					'first_access' => Format::date($record->first_access, 'datetime', true),
					'last_access' => Format::date($record->last_access, 'datetime', true),
					'history_attempt' => 'nd'  ,
					'history_date' =>  'nd',
					'history_duration' =>  'nd',
					'history_status' =>  'nd',
					'totaltime' => $record->totaltime,
					'score'=>$record->score
				);
				$history = $record->history;
				
				if (is_array($history)) {
					foreach ($history as $key=>$history_rec) { 
						if ($key == 0) {
							$row = array(
								'LO_name' => $record->title,
								'LO_type' => $record->objectType,
								'LO_status' => $record->status != "" ? $record->status : 'not attempted',
								'first_access' => Format::date($record->first_access, 'datetime', true),
								'last_access' => Format::date($record->last_access, 'datetime', true),
								'history_attempt' => $key + 1  ,
								'history_date' =>  Format::date($history_rec[0],'datetime', true),
								'history_duration' =>  $history_rec[3],
								'history_status' =>  $history_rec[4],
								'totaltime' => $record->totaltime,
								'score'=>$record->score
							);
							
						}
						else {
							$row = array(
								'LO_name' => '',
								'LO_type' => '',
								'LO_status' => '',
								'first_access' => '',
								'last_access' => '',
								'history_attempt' => $key + 1,
								'history_date' =>  Format::date($history_rec[0],'datetime', true),
								'history_duration' =>  $history_rec[3],
								'history_status' =>  $history_rec[4],
								'totaltime' => '',
								'score'=>''
							);
							
							
						}
						 // aggiungi una riga per ogni record storico accessi
						 $csv_row = array();
						foreach ($row as $row_data) {
						$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
							}
						$output .= implode($separator, $csv_row).$line_end;
						} // each
				} else {
					$csv_row = array();
					foreach ($row as $row_data) {
					$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
						}
					$output .= implode($separator, $csv_row).$line_end;
				}
			} // each list
        } // is array list
		sendStrAsFile($output, 'course_user_stats_export_'.$id_user.'_'.date("Ymd").'.csv');
	}
}
?>