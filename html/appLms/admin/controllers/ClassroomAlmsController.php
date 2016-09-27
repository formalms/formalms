<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

Class ClassroomAlmsController extends AlmsController {

	protected $json;
	protected $acl_man;
	protected $model;

	protected $data;
	protected $permissions;

	protected $base_link_course;
	protected $base_link_classroom;
	protected $base_link_subscription;

	public function init() {
		checkPerm('view', false, 'course', 'lms');
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();
		$this->model = new ClassroomAlms();

		$this->base_link_course = 'alms/course';
		$this->base_link_classroom = 'alms/classroom';
		$this->base_link_subscription = 'alms/subscription';

		$this->permissions = array(
			'view'			=> checkPerm('view', true, 'course', 'lms'),
			'add'				=> checkPerm('add', true, 'course', 'lms'),
			'mod'				=> checkPerm('mod', true, 'course', 'lms'),
			'del'				=> checkPerm('del', true, 'course', 'lms'),
			'moderate'	=> checkPerm('moderate', true, 'course', 'lms'),
			'subscribe'	=> checkPerm('subscribe', true, 'course', 'lms')
		);
	}

	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
		}
		return $message;
	}

	protected function classroom()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$cmodel = new CourseAlms();
		$course_info = $cmodel->getInfo($id_course);
		$course_name = ($course_info['code'] !== '' ? '['.$course_info['code'].'] ' : '').$course_info['name'];

		$result_message = Get::req('result', DOTY_MIXED, false);
		switch ($result_message) {
			case 'ok_mod':
			case 'ok_ins': UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
			case 'err_mod':
			case 'err_ins': UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard')); break;
		}

		$model = new ClassroomAlms($id_course);
		$this->render('edition', array(
			'model' => $model,
			'permissions' => $this->permissions,
			'base_link_course' => $this->base_link_course,
			'base_link_classroom' => $this->base_link_classroom,
			'course_name' => $course_name
		));
	}

	protected function getclassroomedition()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		//Datatable info
		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort			= Get::req('sort', DOTY_MIXED, 'userid');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');

		$model = new ClassroomAlms($id_course);

		$total_course = $model->getCourseEditionNumber();
		$array_edition = $model->loadCourseEdition($start_index, $results, $sort, $dir);

		$result = array(
				'totalRecords' => $total_course,
				'startIndex' => $start_index,
				'sort' => $sort,
				'dir' => $dir,
				'rowsPerPage' => $results,
				'results' => count($array_edition),
				'records' => $array_edition
		);

		$this->data = $this->json->encode($result);

		echo $this->data;
	}



	protected function _getSessionTreeData($index, $default = false) {
		if (!$index || !is_string($index)) return false;
		if (!isset($_SESSION['course_category']['filter_status'][$index])) {
			$_SESSION['course_category']['filter_status'][$index] = $default;
		}
		return $_SESSION['course_category']['filter_status'][$index];
	}

	protected function _setSessionTreeData($index, $value) {
		$_SESSION['course_category']['filter_status'][$index] = $value;
	}


	protected function _getNodeActions($id_category, $is_leaf) {
		$node_options = array();

		$node_options[] = array(
			'id' => 'mod_'.$id_category,
			'command' => 'modify',
			//'content' => '<img src="'.Get::tmpl_path().'images/standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />'
			'icon' => 'standard/edit.png',
			'alt' => Lang::t('_MOD')
		);

		if ($is_leaf) {
			$node_options[] = array(
				'id' => 'del_'.$id_category,
				'command' => 'delete',
				//'content' => '<img src="'.Get::tmpl_path().'images/standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />'
				'icon' => 'standard/delete.png',
				'alt' => Lang::t('_DEL')
			);
		} else {
			$node_options[] = array(
				'id' => 'del_'.$id_category,
				'command' => false,
				//'content' => '<img src="'.Get::tmpl_path().'images/blank.png" />'
				'icon' => 'blank.png'
			);
		}

		return $node_options;
	}


	public function gettreedata() {
		require_once(_lms_.'/lib/category/class.categorytree.php');
		$treecat = new Categorytree();

		$command = Get::req('command', DOTY_ALPHANUM, "");
		switch ($command) {

			case "expand": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$initial = Get::req('initial', DOTY_INT, 0);

				$db = DbConn::getInstance();
				$result = array();
				if ($initial==1) {
					$treestatus = $this->_getSessionTreeData('c_category', 0);
					$folders = $treecat->getOpenedFolders( $treestatus );
					$result = array();

					$ref =& $result;
					foreach ($folders as $folder) {

						if ($folder > 0) {
							for ($i=0; $i<count($ref); $i++) {
								if ($ref[$i]['node']['id'] == $folder) {
									$ref[$i]['children'] = array();
									$ref =& $ref[$i]['children'];
									break;
								}
							}
						}

						$childrens = $treecat->getChildrensById($folder);
						while (list($id_category, $idParent, $path, $lev, $left, $right) = $db->fetch_row($childrens)) {
							$is_leaf = ($right-$left) == 1;
							$node_options = $this->_getNodeActions($id_category, $is_leaf);
							$ref[] = array(
								'node' => array(
									'id' => $id_category,
									'label' => end(explode('/', $path)),
									'is_leaf' => $is_leaf,
									'count_content' => (int)(($right-$left-1)/2),
									'options' => $node_options
								)
							);
						}

					}

				} else { //not initial selection, just an opened folder

					$re = $treecat->getChildrensById($node_id);
					while (list($id_category, $idParent, $path, $lev, $left, $right) = $db->fetch_row($re)) {

						$is_leaf = ($right-$left) == 1;

						$node_options = $this->_getNodeActions($id_category, $is_leaf);

						$result[] = array(
							'id' => $id_category,
							'label' => end(explode('/', $path)),
							'is_leaf' => $is_leaf,
							'count_content' => (int)(($right-$left-1)/2),
							'options' => $node_options
						); //change this
					}

				}


				$output = array('success'=>true, 'nodes'=>$result, 'initial'=>($initial==1));
				echo $this->json->encode($output);
			};break;

			case "set_selected_node": {
				$id_node = Get::req('node_id', DOTY_INT, -1);
				if ($id_node >= 0) $this->_setSessionTreeData('c_category', $id_node);
			} break;

			case "modify": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$new_name = Get::req('name', DOTY_STRING, false);

				$result = array('success'=>false);
				if ($new_name !== false) $result['success'] = $treecat->renameFolderById($node_id, $new_name);
				if ($result['success']) $result['new_name'] = stripslashes($new_name);

				echo $this->json->encode($result);
			};break;


			case "create": {
				$node_id = Get::req('node_id', DOTY_INT, false);
				$node_name = Get::req('name', DOTY_STRING, false); //no multilang required for categories

				$result = array();
				if ($node_id === false) {
					$result['success'] = false;
				} else {
					$success = false;
					$new_node_id = $treecat->addFolderById($node_id, $node_name);
					if ($new_node_id != false && $new_node_id>0) $success = true;

					$result['success'] = $success;
					if ($success) {
						$result['node'] = array(
							'id' => $new_node_id,
							'label' => $node_name,
							'is_leaf' => true,
							'count_content' => 0,
							'options' => $this->_getNodeActions($new_node_id, true)
						);
					}
				}
				echo $this->json->encode($result);
			};break;

			case "delete": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$result = array('success' => $treecat->deleteTreeById($node_id));
				echo $this->json->encode($result);
			};break;

			case "move": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$node_dest = Get::req('node_dest', DOTY_INT, 0);

				$result = array('success'=>$treecat->move($node_id, $node_dest));
				echo $this->json->encode($result);
			};break;

			case "options": {
				$node_id = Get::req('node_id', DOTY_INT, 0);

				//get properties from DB
				$count = $treecat->getChildrenCount($node_id);
				$is_leaf = true;
				if ($count>0) $is_leaf = false;
				$node_options = $this->_getNodeActions($node_id, $is_leaf);

				$result = array('success'=>true, 'options'=>$node_options, '_debug'=>$count);
				echo $this->json->encode($result);
			};break;

			//invalid command
			default: {}

		}

	}

	protected function addclassroom()
	{
		require_once(_base_.'/lib/lib.form.php');

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		//Step info
		$step = Get::req('step', DOTY_INT, 1);

		$model = new ClassroomAlms($id_course);

		if(isset($_POST['back']))
			$step -= 2;

		if(isset($_POST['undo']))
			$step = 0;

		switch($step)
		{
			case '0':
				Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&id_course='.$model->getIdCourse());
			break;

			case '1':
				$course_info = $model->getCourseInfo();

				$this->render('add_step_1', array(
					'model' => $model,
					'course_info' => $course_info,
					'base_link_course' => $this->base_link_course,
					'base_link_classroom' => $this->base_link_classroom
				));
			break;

			case '2':
				$date_info = $model->getDateInfoFromPost();
				$date_info['sub_start_date'] = ($date_info['sub_start_date'] === '' ? '00-00-0000' : $date_info['sub_start_date']);
				$date_info['sub_end_date'] = ($date_info['sub_end_date'] === '' ? '00-00-0000' : $date_info['sub_end_date']);
				$date_info['unsubscribe_date_limit'] = ($date_info['unsubscribe_date_limit'] === '' ? '00-00-0000' : $date_info['unsubscribe_date_limit']);
				if(strcmp($date_info['sub_start_date'], $date_info['sub_end_date']) > 0 && $date_info['sub_end_date'] !== '00-00-0000')
				{
					$course_info = $model->getCourseInfo();

					$this->render('add_step_1', array(
						'model' => $model,
						'course_info' => $course_info,
						'base_link_course' => $this->base_link_course,
						'base_link_classroom' => $this->base_link_classroom,
						'err_avail' => '_SUBSCRIPTION_DATE',
						'availability_info' => ''
					));
					return;
				}
				$this->render('add_step_2', array(
					'model' => $model,
					'base_link_course' => $this->base_link_course,
					'base_link_classroom' => $this->base_link_classroom
				));
			break;

			case '3':

				//check availability
				$date_info = $model->getDateInfoFromPost();
				$array_day_tmp = explode(',', $date_info['date_selected']);
				$array_day = array();
				for($i = 0; $i < count($array_day_tmp); $i++) {
					$array_day[$i]['date_begin'] = $array_day_tmp[$i].' '.$_POST['b_hours_'.$i].':'.$_POST['b_minutes_'.$i].':00';
					$array_day[$i]['pause_begin'] = $array_day_tmp[$i].' '.$_POST['pb_hours_'.$i].':'.$_POST['pb_minutes_'.$i].':00';
					$array_day[$i]['pause_end'] = $array_day_tmp[$i].' '.$_POST['pe_hours_'.$i].':'.$_POST['pe_minutes_'.$i].':00';
					$array_day[$i]['date_end'] = $array_day_tmp[$i].' '.$_POST['e_hours_'.$i].':'.$_POST['e_minutes_'.$i].':00';
					$array_day[$i]['classroom'] = $_POST['classroom_'.$i];
				}

				$availability_info = $model->checkDateAvailability($array_day);
				if (!empty($availability_info)) {
					$this->render('add_step_2', array(
						'model' => $model,
						'base_link_course' => $this->base_link_course,
						'base_link_classroom' => $this->base_link_classroom,
						'err_avail' => '',
						'availability_info' => $availability_info
					));
					return;
				}

				//save class info
				if($model->saveNewDate())
					Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&id_course='.$model->getIdCourse().'&result=ok_ins');
				Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&id_course='.$model->getIdCourse().'&result=err_ins');
			break;
		}
	}

	protected function modclassroom()
	{
		require_once(_base_.'/lib/lib.form.php');

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		//Step info
		$step = Get::req('step', DOTY_INT, 1);

		$model = new ClassroomAlms($id_course, $id_date);

		if(isset($_POST['back']))
			$step -= 2;

		if(isset($_POST['undo']))
			$step = 0;

		switch($step)
		{
			// jump back (undo)
			case '0':
				Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$model->getIdCourse());
			break;
			// editions info
			case '1':
				$date_info = $model->getDateInfo();

				$array_day = $model->getDateDay();

				$this->render('mod_step_1', array(	'model' => $model,
													'date_info' => $date_info,
													'array_day' => $array_day,
													'base_link_course' => $this->base_link_course,
													'base_link_classroom' => $this->base_link_classroom));
			break;
			//daily hours and classroom
			case '2':
				$date_info_mod = $model->getDateInfoFromPost();
				$date_info = $model->getDateInfo();
				$date_info_mod['sub_start_date'] = ($date_info_mod['sub_start_date'] === '' ? '00-00-0000' : $date_info_mod['sub_start_date']);
				$date_info_mod['sub_end_date'] = ($date_info_mod['sub_end_date'] === '' ? '00-00-0000' : $date_info_mod['sub_end_date']);
				$date_info_mod['unsubscribe_date_limit'] = ($date_info_mod['unsubscribe_date_limit'] === '' ? '00-00-0000' : $date_info_mod['unsubscribe_date_limit']);
				if(strcmp($date_info_mod['sub_start_date'], $date_info_mod['sub_end_date']) > 0 && $date_info['sub_end_date'] !== '00-00-0000')
				{
					$array_day = $model->getDateDay();

					$this->render('mod_step_1', array(
						'model' => $model,
						'date_info' => $date_info,
						'array_day' => $array_day,
						'base_link_course' => $this->base_link_course,
						'base_link_classroom' => $this->base_link_classroom,
						'err_avail' => '_SUBSCRIPTION_DATE',
						'availability_info' => ''
					));
					return;
				}

				$this->render('mod_step_2', array(	'model' => $model,
													'date_info' => $date_info,
													'base_link_course' => $this->base_link_course,
													'base_link_classroom' => $this->base_link_classroom));
			break;
			// saving collected datas
			case '3':
				if($model->updateDate())
					Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$model->getIdCourse().'&amp;result=ok_mod');
				Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$model->getIdCourse().'&amp;result=err_mod');
			break;
		}
	}

	protected function delPopUp()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		$model = new ClassroomAlms($id_course, $id_date);

		$date_info = $model->getDateInfo();

		$res = array(	'message' => Lang::t('_AREYOUSURE', 'course', array('[name]' => $date_info['name'], '[code]' => $date_info['code'])),
						'title' => Lang::t('_DEL_COURSE_EDITION', 'course'),
						'action' => 'ajax.adm_server.php?r='.$this->base_link_classroom.'/delclassroom&id_course='.$model->getIdCourse().'&id_date='.$model->getIdDate(),
						'success' => true);

		$this->data = $this->json->encode($res);

		echo $this->data;
	}

	protected function delclassroom()
	{
		if(Get::cfg('demo_mode'))
			die('Cannot del course during demo mode.');
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		$model = new ClassroomAlms($id_course, $id_date);

		$res = array('success' => $model->delClassroom());

		$this->data = $this->json->encode($res);

		echo $this->data;
	}

	protected function delcourse()
	{
		if(Get::cfg('demo_mode'))
			die('Cannot del course during demo mode.');
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$model = new ClassroomAlms($id_course);

		$res = array('success' => $model->delCourse());

		$this->data = $this->json->encode($res);

		echo $this->data;
	}
    
    protected function export() {
//      define("IN_FORMA", "ok");
//      include('../config.php');
//      error_reporting(0);
//      $db = sql_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass']);
//      sql_select_db($cfg['db_name']);

      $today = getdate();
      $mday = $today['mday'];
      if ($mday < 10)
        $mday = "0" . $mday;
      $month = $today['mon'];
      if ($month < 10)
        $month = "0" . $month;
      $year = $today['year'];
      $ore = $today['hours'];
      if ($ore < 10)
        $ore = "0" . $ore;
      $min = $today['minutes'];
      if ($min < 10)
        $min = "0" . $min;
      $sec = $today['seconds'];
      if ($sec < 10)
        $sec = "0" . $sec;
      $file_parameters = $mday . "-" . $month . "-" . $year . "_h" . $ore . "_" . $min . "_" . $sec;
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);
      $query = "SELECT code, name FROM learning_course_date WHERE id_course=" . $id_course . " AND id_date=" . $id_date;
      $res = sql_query($query);
      $row = sql_fetch_array($res);
      $course_code = $row[0];
      $edition_name = $row[1];

      header("Content-type: application/x-msdownload");
      header("Content-Disposition: attachment; filename=export_presenze_[" . $course_code . "]_" . $file_parameters . ".xls");
      header("Pragma: no-cache");
      header("Expires: 0");
      
      ob_end_clean();
		
      $array_date = array();
      print "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"></head><body>";
      print $edition_name;
      print "<table border=1><tr><td><b>Username</b></td><td><b>".Lang::t('_FULLNAME', 'standard')."</b></td>";
      $query = "SELECT DISTINCT day FROM learning_course_date_presence WHERE day<>'0000-00-00' AND id_date=" . $id_date . " ORDER BY day";
      $res = sql_query($query);
      while ($row = sql_fetch_array($res)) {
        print "<td><b>" . substr($row[0], 8, 2) . "-" . substr($row[0], 5, 2) . "-" . substr($row[0], 0, 4) . "</b></td>";
        array_push($array_date, $row[0]);
      }
      print "<td><b>".Lang::t('_NOTES', 'standard')."</b></td></tr>";

      $query = "SELECT U.userid, U.firstname, U.lastname, U.idst FROM learning_course_date_user L, core_user U WHERE L.id_user=U.idst AND L.id_date=" . $id_date . " ORDER BY id_user";
      $res = sql_query($query);
      while ($row = sql_fetch_array($res)) {
        print "<tr><td>" . substr($row[0], 1, strlen($row[0])) . "</td><td>" . $row[2] . " " . $row[1] . "</td>";

        for ($i = 0; $i < count($array_date); $i++) {
          $query = "SELECT presence FROM learning_course_date_presence WHERE id_date=" . $id_date . " AND id_user=" . $row[3] . " AND day='" . $array_date[$i] . "'";
          $res2 = sql_query($query);
          $row2 = sql_fetch_array($res2);
          if ($row2[0] == 0)
            print "<td>&nbsp;</td>";
          else
            print "<td>X</td>";
        }
        $query = "SELECT note FROM learning_course_date_presence WHERE id_date=" . $id_date . " AND id_user=" . $row[3] . " AND day='0000-00-00'";
        $res3 = sql_query($query);
        $row3 = sql_fetch_array($res3);
        print "<td>" . $row3[0] . "</td></tr>";
      }

      print "</table></body>";
      exit(0);
    }

	protected function presence()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		$model = new ClassroomAlms($id_course, $id_date);

		if(isset($_POST['save']))
		{
			if($model->savePresence()) {
				Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$model->getIdCourse().'&result=ok');
			} else {
				Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$model->getIdCourse().'&result=err_pres');
			}
		} elseif(isset($_POST['undo'])) {
			Util::jump_to('index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$model->getIdCourse());
		}

		$cmodel = new CourseAlms();
		$course_info = $cmodel->getInfo($id_course, FALSE, $id_date);
		$course_name = ($course_info['code'] !== '' ? '['.$course_info['code'].'] ' : '').$course_info['name'];

		$this->render('presence', array(
			'model' => $model,
			'base_link_course' => $this->base_link_course,
			'base_link_classroom' => $this->base_link_classroom,
			'course_name' => $course_name
		));
	}

	public function saveData()
	{
		require_once(_base_.'/lib/lib.json.php');

		$json = new Services_JSON();

		$id_course = Get::req('id_course', DOTY_INT, false);
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

				echo $json->encode(array('success' => $res, 'new_value' => $new_value, 'old_value' => $old_value));
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

				echo $json->encode(array('success' => $res, 'new_value' => stripslashes($new_value), 'old_value' => stripslashes($old_value)));
			break;
		}
	}
}
?>