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

Class CourseAlmsController extends AlmsController
{
	protected $json;
	protected $acl_man;
	protected $model;

	protected $data;

	protected $permissions;

	protected $base_link_course;
	protected $base_link_classroom;
	protected $base_link_edition;
	protected $base_link_subscription;
	protected $base_link_competence;

	protected $lo_types_cache;

	public function init()
	{
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();
		$this->model = new CourseAlms();

		$this->base_link_course = 'alms/course';
		$this->base_link_classroom = 'alms/classroom';
		$this->base_link_edition = 'alms/edition';
		$this->base_link_subscription = 'alms/subscription';
		$this->base_link_competence = 'adm/competences';

		$this->lo_types_cache = false;

		$this->permissions = array(
			'view'			=> checkPerm('view', true, 'course', 'lms'),
			'add'			=> checkPerm('add', true, 'course', 'lms'),
			'mod'			=> checkPerm('mod', true, 'course', 'lms'),
			'del'			=> checkPerm('del', true, 'course', 'lms'),
			'moderate'		=> checkPerm('moderate', true, 'course', 'lms'),
			'subscribe'		=> checkPerm('subscribe', true, 'course', 'lms'),
			'add_category'	=> checkPerm('add', true, 'coursecategory', 'lms'),
			'mod_category'	=> checkPerm('mod', true, 'coursecategory', 'lms'),
			'del_category'	=> checkPerm('del', true, 'coursecategory', 'lms'),
			'view_cert'		=> checkPerm('view', true, 'certificate', 'lms'),
			'mod_cert'		=> checkPerm('mod', true, 'certificate', 'lms')
		);
	}

	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
			case "": $message = ""; break;
		}
		return $message;
	}
	
	// funzione (ajax)
	public function getlolist($p=0, $sk = '') {
		if (isset($_GET['idCourse'])) {
			$query_list = "SELECT * FROM %lms_organization WHERE idCourse = '".(int)$_GET['idCourse']."' AND idParent = ".$p." ORDER BY path ASC";
			$result_list = sql_query($query_list);
			if (sql_num_rows($result_list) > 0) {
				if ($p == 0)
					echo "<div id='treeDiv' class='ygtv-checkbox'>";
				echo "<ul>";
				while($lo = sql_fetch_array($result_list)) {
					echo "<li class=\"expanded\"> <input onclick='cascade(\"".$lo['idOrg']."\")' class='".$sk."' type='checkbox' id='".$lo['idOrg']."' name='lo_list[]' value='".$lo['idOrg']."' checked='checked' /> <label for='".$lo['idOrg']."'>".$lo['title']."</label>";
					$this->getlolist($lo['idOrg'], $sk." ".$lo['idOrg']);
					echo "</li>";
				}
				echo "</ul>";
				if ($p == 0) {
					echo "</div>";
				}
			}
		} else
			echo "Error";
	}
	

	public function show()
	{
		if(isset($_GET['res']) && $_GET['res'] !== '')
			UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard'));

		if(isset($_GET['err']) && $_GET['err'] !== '')
			UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard'));

		$params = array();

		if(!isset($_SESSION['course_filter']))
		{
			$_SESSION['course_filter']['text'] = '';
			$_SESSION['course_filter']['classroom'] = false;
			$_SESSION['course_filter']['descendants'] = false;
			$_SESSION['course_filter']['waiting'] = false;
		}

		if(isset($_POST['c_filter_set']))
		{
			$classroom = (bool)Get::req('classroom', DOTY_INT, false);
			$descendants = (bool)Get::req('descendants', DOTY_INT, false);
			$waiting = (bool)Get::req('waiting', DOTY_INT, false);
			$filter_text = Get::req('text', DOTY_STRING, '');
		}
		else
		{
			$classroom = $_SESSION['course_filter']['classroom'];
			$descendants = $_SESSION['course_filter']['descendants'];
			$waiting = $_SESSION['course_filter']['waiting'];
			$filter_text = $_SESSION['course_filter']['text'];
		}

		$filter_open = false;

		if($descendants || $waiting)
			$filter_open = true;

		$filter = array(
			'classroom' => $classroom,
			'descendants' => $descendants,
			'waiting' => $waiting,
			'text' => $filter_text,
			'open' => $filter_open,
			'id_category' => $this->_getSessionTreeData('id_category', 0));

		$_SESSION['course_filter']['text'] = $filter_text;
		$_SESSION['course_filter']['classroom'] = $classroom;
		$_SESSION['course_filter']['descendants'] = $descendants;
		$_SESSION['course_filter']['waiting'] = $waiting;

		$params['initial_selected_node'] = $this->_getSessionTreeData('id_category', 0);
		$params['filter'] = $filter;
		$params['root_name'] = Lang::t('_CATEGORY', 'admin_course_managment');
		$params['permissions'] = $this->permissions;

		$params['base_link_course'] = $this->base_link_course;
		$params['base_link_classroom'] = $this->base_link_classroom;
		$params['base_link_edition'] = $this->base_link_edition;
		$params['base_link_subscription'] = $this->base_link_subscription;

		$smodel = new SubscriptionAlms();
		$params['unsubscribe_requests'] = $smodel->countPendingUnsubscribeRequests();

		$this->render('show', $params);
	}

	protected function _getSessionTreeData($index, $default = false)
	{
		if (!$index || !is_string($index)) return false;
		if (!isset($_SESSION['course_category']['filter_status'][$index]))
			$_SESSION['course_category']['filter_status'][$index] = $default;
		return $_SESSION['course_category']['filter_status'][$index];
	}

	protected function _setSessionTreeData($index, $value)
	{
		$_SESSION['course_category']['filter_status'][$index] = $value;
	}

	public function filterevent()
	{
		$_SESSION['course_filter']['classroom'] = Get::req('classroom', DOTY_MIXED, false);
		$_SESSION['course_filter']['descendants'] = Get::req('descendants', DOTY_MIXED, false);
		$_SESSION['course_filter']['waiting'] = Get::req('waiting', DOTY_MIXED, false);
		$_SESSION['course_filter']['text'] = Get::req('text', DOTY_STRING, '');

		if($_SESSION['course_filter']['classroom'] === 'false')
			$_SESSION['course_filter']['classroom'] = false;
		else
			$_SESSION['course_filter']['classroom'] = true;

		if($_SESSION['course_filter']['descendants'] === 'false')
			$_SESSION['course_filter']['descendants'] = false;
		else
			$_SESSION['course_filter']['descendants'] = true;

		if($_SESSION['course_filter']['waiting'] === 'false')
			$_SESSION['course_filter']['waiting'] = false;
		else
			$_SESSION['course_filter']['waiting'] = true;

		echo $this->json->encode(array('success' => true));
	}

	public function resetevent()
	{
		$_SESSION['course_filter']['text'] = '';
		$_SESSION['course_filter']['classroom'] = false;
		$_SESSION['course_filter']['descendants'] = false;
		$_SESSION['course_filter']['waiting'] = false;
	}

	protected function _getNodeActions($id_category, $is_leaf, $associated_courses = 0)
	{
		$node_options = array();

		//modify category action
		if ($this->permissions['mod_category']) {
			$node_options[] = array(
				'id' => 'mod_'.$id_category,
				'command' => 'modify',
				'icon' => 'standard/edit.png',
				'alt' => Lang::t('_MOD')
			);
		}

		//delete category action
		if ($this->permissions['del_category']) {
			if ($is_leaf && $associated_courses == 0)
			{
				$node_options[] = array(
					'id' => 'del_'.$id_category,
					'command' => 'delete',
					'icon' => 'standard/delete.png',
					'alt' => Lang::t('_DEL'));
			}
			else
			{
				$node_options[] = array(
					'id' => 'del_'.$id_category,
					'command' => false,
					'icon' => 'blank.png');
			}
		}

		return $node_options;
	}

	public function gettreedata()
	{
		require_once(_lms_.'/lib/category/class.categorytree.php');
		$treecat = new Categorytree();

		$command = Get::req('command', DOTY_ALPHANUM, "");
		switch ($command)
		{
			case "expand":
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$initial = Get::req('initial', DOTY_INT, 0);

				$db = DbConn::getInstance();
				$result = array();
				if ($initial==1)
				{
					$treestatus = $this->_getSessionTreeData('id_category', 0);
					$folders = $treecat->getOpenedFolders( $treestatus );
					$result = array();

					$ref =& $result;
					foreach ($folders as $folder)
					{
						if ($folder > 0)
						{
							for ($i=0; $i<count($ref); $i++)
							{
								if ($ref[$i]['node']['id'] == $folder)
								{
									$ref[$i]['children'] = array();
									$ref =& $ref[$i]['children'];
									break;
								}
							}
						}

						$childrens = $treecat->getJoinedChildrensById($folder);
						while (list($id_category, $idParent, $path, $lev, $left, $right, $associated_courses) = $db->fetch_row($childrens))
						{
							$is_leaf = ($right-$left) == 1;
							$node_options = $this->_getNodeActions($id_category, $is_leaf, $associated_courses);
							$ref[] = array(
								'node' => array(
									'id' => $id_category,
									'label' => end(explode('/', $path)),
									'is_leaf' => $is_leaf,
									'count_content' => (int)(($right-$left-1)/2),
									'options' => $node_options));
						}
					}

				}
				else
				{ //not initial selection, just an opened folder
					$re = $treecat->getJoinedChildrensById($node_id);
					while (list($id_category, $idParent, $path, $lev, $left, $right, $associated_courses) = $db->fetch_row($re))
					{
						$is_leaf = ($right-$left) == 1;

						$node_options = $this->_getNodeActions($id_category, $is_leaf, $associated_courses);

						$result[] = array(
							'id' => $id_category,
							'label' => end(explode('/', $path)),
							'is_leaf' => $is_leaf,
							'count_content' => (int)(($right-$left-1)/2),
							'options' => $node_options); //change this
					}
				}

				$output = array('success'=>true, 'nodes'=>$result, 'initial'=>($initial==1));
				echo $this->json->encode($output);
			break;

			case "set_selected_node":
				$id_node = Get::req('node_id', DOTY_INT, -1);
				if ($id_node >= 0) $this->_setSessionTreeData('id_category', $id_node);
			break;

			case "modify":
				if (!$this->permissions['mod_category']) {
					$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
					echo $this->json->encode($output);
					return;
				}

				$node_id = Get::req('node_id', DOTY_INT, 0);
				$new_name = Get::req('name', DOTY_STRING, false);

				$result = array('success'=>false);
				if ($new_name !== false) $result['success'] = $treecat->renameFolderById($node_id, $new_name);
				if ($result['success']) $result['new_name'] = stripslashes($new_name);

				echo $this->json->encode($result);
			break;


			case "create":
				if (!$this->permissions['add_category']) {
					$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
					echo $this->json->encode($output);
					return;
				}

				$node_id = Get::req('node_id', DOTY_INT, false);
				$node_name = Get::req('name', DOTY_STRING, false); //no multilang required for categories

				$result = array();
				if ($node_id === false)
					$result['success'] = false;
				else
				{
					$success = false;
					$new_node_id = $treecat->addFolderById($node_id, $node_name);
					if ($new_node_id != false && $new_node_id>0) $success = true;

					$result['success'] = $success;
					if ($success)
						$result['node'] = array(
							'id' => $new_node_id,
							'label' => stripslashes($node_name),
							'is_leaf' => true,
							'count_content' => 0,
							'options' => $this->_getNodeActions($new_node_id, true));
				}
				echo $this->json->encode($result);
			break;

			case "delete":
				if (!$this->permissions['del_category']) {
					$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
					echo $this->json->encode($output);
					return;
				}

				$node_id = Get::req('node_id', DOTY_INT, 0);
				$result = array('success' => $treecat->deleteTreeById($node_id));
				echo $this->json->encode($result);
			break;

			case "move":
				if (!$this->permissions['mod_category']) {
					$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
					echo $this->json->encode($output);
					return;
				}
				
				$node_id = Get::req('src', DOTY_INT, 0);
				$node_dest = Get::req('dest', DOTY_INT, 0);
				$model = new CoursecategoryAlms();
				$result = array('success'=>$model->moveFolder($node_id, $node_dest));

				echo $this->json->encode($result);
			break;

			case "options":
				$node_id = Get::req('node_id', DOTY_INT, 0);

				//get properties from DB
				$count = $treecat->getChildrenCount($node_id);
				$is_leaf = true;
				if ($count>0) $is_leaf = false;
				$node_options = $this->_getNodeActions($node_id, $is_leaf);

				$result = array('success'=>true, 'options'=>$node_options, '_debug'=>$count);
				echo $this->json->encode($result);
			break;
			//invalid command
			default: {}
		}
	}

	public function getcourselist()
	{
		//Datatable info
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'userid');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$id_category = Get::req('node_id', DOTY_INT, (int)$this->_getSessionTreeData('id_category', 0));
		$filter_text = $_SESSION['course_filter']['text'];
		$classroom = $_SESSION['course_filter']['classroom'];
		$descendants = $_SESSION['course_filter']['descendants'];
		$waiting = $_SESSION['course_filter']['waiting'];

		$filter_open = false;

		if($descendants || $waiting)
			$filter_open = true;

		$filter = array(
			'id_category' => $id_category,
			'classroom' => $classroom,
			'descendants' => $descendants,
			'waiting' => $waiting,
			'text' => $filter_text,
			'open' => $filter_open
		);

		$total_course = $this->model->getCourseNumber($filter);
		if ($start_index >= $total_course) {
			if ($total_course<$results) {
				$start_index = 0;
			} else {
				$start_index = $total_course - $results;
			}
		}
		$course_res = $this->model->loadCourse($start_index, $results, $sort, $dir, $filter);
		$course_with_cert = $this->model->getCourseWithCertificate();
		$course_with_competence = $this->model->getCourseWithCompetence();

		$list = array();

		while($row = sql_fetch_assoc($course_res)) {
			$course_type = 'elearning';
			switch ($row['course_type']) {
				case 'classroom': $course_type = 'classroom';
				case 'elearning': {
					if ($row['course_edition'] > 0) $course_type = 'edition';
				}
			}

			$num_subscribed = $row['subscriptions'] - $row['pending'];

			$list[ $row['idCourse'] ] = array(
				'id' => $row['idCourse'],
				'code' => $row['code'],
				'name' => $row['name'],
				'type' => Lang::t('_'.strtoupper($row['course_type'])),

				'type_id' => $course_type,
				
				'wait' => (/*$row['course_type'] !== 'classroom' && */$row['course_edition'] != 1 && $row['pending'] != 0
						? '<a href="index.php?r='.$this->base_link_subscription.'/waitinguser&id_course='.$row['idCourse'].'" title="'.Lang::t('_WAITING', 'course').'">'.$row['pending'].'</a>'
						: '' ),
				'user' => ($row['course_type'] !== 'classroom' && $row['course_edition'] != 1 
						? '<a class="nounder" href="index.php?r='.$this->base_link_subscription.'/show&amp;id_course='.$row['idCourse'].'" title="'.Lang::t('_SUBSCRIPTION', 'course').'">'.$num_subscribed.' '.Get::img('standard/moduser.png', Lang::t('_SUBSCRIPTION', 'course')).'</a>'
						: ''),
				'edition' => ($row['course_type'] === 'classroom' 
						? '<a href="index.php?r='.$this->base_link_classroom.'/classroom&amp;id_course='.$row['idCourse'].'" title="'.Lang::t('_CLASSROOM_EDITION', 'course').'">'.$this->model->classroom_man->getDateNumber($row['idCourse'], true).'</a>' : ($row['course_edition'] == 1 ? '<a href="index.php?r='.$this->base_link_edition.'/show&amp;id_course='.$row['idCourse'].'" title="'.Lang::t('_EDITIONS', 'course').'">'.$this->model->edition_man->getEditionNumber($row['idCourse']).'</a>'
						: '')),
			);

			$perm_assign = checkPerm('assign', true, 'certificate', 'lms');
			$perm_release = checkPerm('release', true, 'certificate', 'lms');

			if ($perm_assign) {
				$list[ $row['idCourse'] ]['certificate'] = '<a href="index.php?r='.$this->base_link_course.'/certificate&amp;id_course='.$row['idCourse'].'">'.Get::sprite('subs_pdf'.(!isset($course_with_cert[$row['idCourse']]) ? '_grey' : ''), Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course')).'</a>';
			}

			if ($perm_release) {
				$list[ $row['idCourse'] ]['certreleased'] = '<a href="index.php?modname=certificate&op=view_report_certificate&amp;id_course='.$row['idCourse'].'&from=courselist&of_platform=lms">'.Get::sprite('subs_print'.(!isset($course_with_cert[$row['idCourse']]) ? '_grey' : ''), Lang::t('_CERTIFICATE_RELEASE', 'course')).'</a>';
                $list[ $row['idCourse'] ]['certreleased'] = '<a href="index.php?r=alms/course/list_certificate&amp;id_course='.$row['idCourse'].'&amp;from=courselist">'.Get::sprite('subs_print'.(!isset($course_with_cert[$row['idCourse']]) ? '_grey' : ''), Lang::t('_CERTIFICATE_RELEASE', 'course')).'</a>';
			}
			
			$list[ $row['idCourse'] ] = array_merge($list[ $row['idCourse'] ], [
                'competences' => '<a href="index.php?r='.$this->base_link_competence.'/man_course&amp;id_course='.$row['idCourse'].'">'.Get::sprite('subs_competence'.(!isset($course_with_competence[$row['idCourse']]) ? '_grey' : ''), Lang::t('_COMPETENCES', 'course')).'</a>',
				'menu' => '<a href="index.php?r='.$this->base_link_course.'/menu&amp;id_course='.$row['idCourse'].'">'.Get::sprite('subs_menu', Lang::t('_ASSIGN_MENU', 'course')).'</a>',
				'dup' => 'ajax.adm_server.php?r='.$this->base_link_course.'/dupcourse&id_course='.$row['idCourse'],
				'mod' => '<a href="index.php?r='.$this->base_link_course.'/modcourse&amp;id_course='.$row['idCourse'].'">'.Get::sprite('subs_mod', Lang::t('_MOD', 'standard')).'</a>',
				'del' => 'ajax.adm_server.php?r='.$this->base_link_course.'/delcourse&id_course='.$row['idCourse'].'&confirm=1',
			]);
		}

		if (!empty($list)) {
			$id_list = array_keys($list);
			$count_students = $this->model->getCoursesStudentsNumber($id_list);
			foreach ($list as $id_course => $cinfo) {
				$list[$id_course]['students'] = isset($count_students[$id_course]) ? $count_students[$id_course] : '0';
			}
		}

		$result = array(
			'totalRecords' => $total_course,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count( $list ),
			'records' => array_values( $list )
		);

		echo $this->json->encode($result);
	}

	protected function _createLO( $objectType, $idResource = NULL ) {
		if ($this->lo_types_cache === false) {
			$this->lo_types_cache = array();
			$query = "SELECT objectType, className, fileName FROM %lms_lo_types";
			$rs = sql_query( $query );
			while (list( $type, $className, $fileName ) = sql_fetch_row( $rs )) {
				$this->lo_types_cache[$type] = array( $className, $fileName );
			}
		}
		/*
		$query = "SELECT className, fileName FROM %lms_lo_types WHERE objectType='".$objectType."'";
		$rs = sql_query( $query );
		list( $className, $fileName ) = sql_fetch_row( $rs );
		*/
		if (!isset($this->lo_types_cache[$objectType])) return NULL;
		list( $className, $fileName ) = $this->lo_types_cache[$objectType];
		require_once(_lms_.'/class.module/'.$fileName );
		$lo =  new $className ( $idResource );
		return $lo;
	}



	public function dupcourse()
	{
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		//TO DO: make it a sqltransaction if possible

		if(isset($_POST['confirm']))
		{
			$id_dupcourse = Get::req('id_course', DOTY_INT, 0);
			$id_orgs = array();
			$array_new_testobject = array();

			// read the old course info
			$query_sel = "SELECT * FROM %lms_course WHERE idCourse = '".$id_dupcourse."' ";
			$result_sel = sql_query($query_sel);
			$list_sel = sql_fetch_array($result_sel);

			foreach($list_sel as $k=>$v)
				$list_sel[$k] = sql_escape_string($v);

			$new_course_dup = 0;

			$new_file_array = array();

			if($list_sel['imgSponsor'] != '')
			{
				$new_name_array = explode('_', str_replace('course_sponsor_logo_', '', $list_sel['imgSponsor']));
				$filename = 'course_sponsor_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_sponsor_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['imgSponsor']);

				$new_file_array[0]['old'] = $list_sel['imgSponsor'];
				$new_file_array[0]['new'] = $filename;
				$list_sel['imgSponsor'] = $filename;
			}

			if($list_sel['img_course'] != '')
			{
				$new_name_array = explode('_', str_replace('course_logo_', '', $list_sel['img_course']));
				$filename = 'course_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_course']);

				$new_file_array[1]['old'] = $list_sel['img_course'];
				$new_file_array[1]['new'] = $filename;
				$list_sel['img_course'] = $filename;
			}

			if($list_sel['img_material'] != '')
			{
				$new_name_array = explode('_', str_replace('course_user_material_', '', $list_sel['img_material']));
				$filename = 'course_user_material_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_user_material_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_material']);

				$new_file_array[2]['old'] = $list_sel['img_material'];
				$new_file_array[2]['new'] = $filename;
				$list_sel['img_material'] = $filename;
			}

			if($list_sel['img_othermaterial'] != '')
			{
				$new_name_array = explode('_', str_replace('course_otheruser_material_', '', $list_sel['img_othermaterial']));
				$filename = 'course_otheruser_material_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_otheruser_material_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_othermaterial']);

				$new_file_array[3]['old'] = $list_sel['img_othermaterial'];
				$new_file_array[3]['new'] = $filename;
				$list_sel['img_othermaterial'] = $filename;
			}

			if($list_sel['course_demo'] != '')
			{
				$new_name_array = explode('_', str_replace('course_demo_', '', $list_sel['course_demo']));
				$filename = 'course_demo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_demo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['course_demo']);

				$new_file_array[4]['old'] = $list_sel['course_demo'];
				$new_file_array[4]['new'] = $filename;
				$list_sel['course_demo'] = $filename;
			}

			// duplicate the entry of learning_course
			$query_ins = "INSERT INTO %lms_course
				( idCategory, code, name, description, lang_code, status, level_show_user,
				subscribe_method, linkSponsor, imgSponsor, img_course, img_material, img_othermaterial,
				course_demo, mediumTime, permCloseLO, userStatusOp, difficult, show_progress, show_time, show_extra_info,
				show_rules, valid_time, max_num_subscribe, min_num_subscribe,
				max_sms_budget, selling, prize, course_type, policy_point, point_to_all, course_edition, classrooms, certificates,
				create_date, security_code, imported_from_connection, course_quota, used_space, course_vote, allow_overbooking, can_subscribe,
				sub_start_date, sub_end_date, advance, show_who_online, direct_play, autoregistration_code, use_logo_in_courselist )
				VALUES
				( '".$list_sel['idCategory']."', '".$list_sel['code']."', '"."Copia di ".$list_sel['name']."', '".$list_sel['description']."', '".$list_sel['lang_code']."', '".$list_sel['status']."', '".$list_sel['level_show_user']."',
				'".$list_sel['subscribe_method']."', '".$list_sel['linkSponsor']."', '".$list_sel['imgSponsor']."', '".$list_sel['img_course']."', '".$list_sel['img_material']."', '".$list_sel['img_othermaterial']."',
				'".$list_sel['course_demo']."', '".$list_sel['mediumTime']."', '".$list_sel['permCloseLO']."', '".$list_sel['userStatusOp']."', '".$list_sel['difficult']."', '".$list_sel['show_progress']."', '".$list_sel['show_time']."', '".$list_sel['show_extra_info']."',
				'".$list_sel['show_rules']."', '".$list_sel['valid_time']."', '".$list_sel['max_num_subscribe']."', '".$list_sel['min_num_subscribe']."',
				'".$list_sel['max_sms_budget']."', '".$list_sel['selling']."', '".$list_sel['prize']."', '".$list_sel['course_type']."', '".$list_sel['policy_point']."', '".$list_sel['point_to_all']."', '".$list_sel['course_edition']."', '".$list_sel['classrooms']."', '".$list_sel['certificates']."',
				'".date('Y-m-d H:i:s')."', '".$list_sel['security_code']."', '".$list_sel['imported_from_connection']."', '".$list_sel['course_quota']."', '".$list_sel['used_space']."', '".$list_sel['course_vote']."', '".$list_sel['allow_overbooking']."', '".$list_sel['can_subscribe']."',
				'".$list_sel['sub_start_date']."', '".$list_sel['sub_end_date']."', '".$list_sel['advance']."', '".$list_sel['show_who_online']."', '".$list_sel['direct_play']."', '".$list_sel['autoregistration_code']."', '".$list_sel['use_logo_in_courselist']."' )";
			$result_ins = sql_query($query_ins);

			if(!$result_ins)
			{
				ob_clean();
				ob_start();
				echo $this->json->encode(array('success' => false));
				die();
			}

			// the id of the new course created
			$new_id_course = $new_course_dup = sql_insert_id();


			//Create the new course file
			if(isset($_POST['image']))
			{
				$path = Get::sett('pathcourse');
				$path = '/appLms/'.Get::sett('pathcourse').( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

				require_once(_base_.'/lib/lib.upload.php');

				sl_open_fileoperations();

				foreach($new_file_array as $file_info) {
					sl_copy($path.$file_info['old'], $path.$file_info['new']);
				}
				
				sl_close_fileoperations();
			}


			//--- copy menu data -----------------------------------------------------

			// copy the old course menu into the new one
			$query_selmen = "SELECT * FROM %lms_menucourse_main WHERE idCourse = '".$id_dupcourse."' ";
			$result_selmen = sql_query($query_selmen);
			while($list_selmen = sql_fetch_array($result_selmen))
			{
				$query_dupmen = "INSERT INTO %lms_menucourse_main ".
					" (idCourse, sequence, name, image) ".
					" VALUES ".
					" ( '".$new_course_dup."', '".$list_selmen['sequence']."', '".$list_selmen['name']."', '".$list_selmen['image']."' )";
				$result_dupmen = sql_query($query_dupmen);
				$array_seq[$list_selmen['idMain']] = sql_insert_id();
			}

			$query_insert_list = array();
			$query_selmenun = "SELECT * FROM %lms_menucourse_under WHERE idCourse = '".$id_dupcourse."' ";
			$result_selmenun = sql_query($query_selmenun);
			while($new_org = sql_fetch_array($result_selmenun)) {
				$valore_idn = $new_org['idMain'];
				$_idMain = $array_seq[$valore_idn];
				
				$query_insert_list[] = "('".$_idMain."', '".$new_course_dup."', '".$new_org['sequence']."', '".$new_org['idModule']."', '".$new_org['my_name']."')";
			}
			$result_dupmen = true;
			if (!empty($query_insert_list)) {
				$query_dupmen = "INSERT INTO %lms_menucourse_under
					(idMain, idCourse, sequence, idModule, my_name)
					VALUES ".implode(",", $query_insert_list);
				$result_dupmen = sql_query($query_dupmen);
			}

			//--- end menu -----------------------------------------------------------



			function &getCourseLevelSt($id_course) {
				$map 		= array();
				$levels 	= CourseLevel::getLevels();

				// find all the group created for this menu custom for permission management
				foreach($levels as $lv => $name_level) {
					$group_info = Docebo::aclm()->getGroup(FALSE, '/lms/course/'.$id_course.'/subscribed/'.$lv);
					$map[$lv] 	= $group_info[ACL_INFO_IDST];
				}
				return $map;
			}

			function funAccess($functionname, $mode, $returnValue = false, $custom_mod_name = false) { return true; }

			require_once(_lms_.'/lib/lib.course.php');
			require_once(_lms_.'/lib/lib.manmenu.php');
			require_once(_lms_.'/lib/lib.subscribe.php');

			$docebo_course = new DoceboCourse($id_dupcourse);
			$subscribe_man = new CourseSubscribe_Manager();

			$group_idst =& $docebo_course->createCourseLevel($new_course_dup);
			$group_of_from  =& $docebo_course->getCourseLevel($id_dupcourse);
			$perm_form   =& createPermForCoursebis($group_of_from, $new_course_dup, $id_dupcourse);
			$levels    =  $subscribe_man->getUserLevel();

			foreach($levels as $lv => $name_level) {
				foreach($perm_form[$lv] as $idrole => $v) {
					if($group_idst[$lv] != 0 && $idrole != 0) {
						Docebo::aclm()->addToRole( $idrole, $group_idst[$lv] );
					}
				}
			}




			if(isset($_POST['certificate']))
			{
				// duplicate the certificate assigned
				$query_insert_list = array();
				$query_selmenun = "SELECT * FROM %lms_certificate_course WHERE id_course = '".$id_dupcourse."' ";
				$result_selmenun = sql_query($query_selmenun);
				while($new_org = sql_fetch_assoc($result_selmenun)) {
					$query_insert_list[] = "('".$new_org['id_certificate']."', '".$new_course_dup."', 
						'".$new_org['available_for_status']."', '".$new_org['point_required']."' )";
				}
				$result_dupmen = true;
				if (!empty($query_insert_list)) {
					$query_dupmen = "INSERT INTO %lms_certificate_course
						(id_certificate, id_course, available_for_status, point_required)
						VALUES ".implode(",", $query_insert_list);
					$result_dupmen = sql_query($query_dupmen);
				}
			}



            require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
			require_once(_lms_.'/lib/lib.param.php');
			require_once(_lms_.'/class.module/track.object.php');
			require_once(_lms_.'/class.module/learning.object.php' );

			

			$nullVal = NULL;
			$id_orgs = array();
			$map_org = array();
			
			if(isset($_POST['lo']))
			{
				
				$org_map = array();
				$id_orgs = array();
				$prereq_map  = array();
				
				// retrive all the folders and object, the order by grant that folder are created before the elements contained in them
				$query = "SELECT * FROM %lms_organization WHERE idCourse = ".(int)$id_dupcourse." ORDER BY path ASC";
				$source_res = sql_query($query);
				
				// Go trough all the entry of learning_organization
				while($source = sql_fetch_object($source_res)) {
					// check if LO id is checked
					if (in_array($source->idOrg, $_POST['lo_list'])) {

						// if it's an object we must make a copy, if it's a folder we can create a row
						// inside learning_orgation and save the id for later use

						if($source->objectType == '') {

							// is a folder
							// create a new row in learning_organization
							$query_new_org = "INSERT INTO %lms_organization (
								idParent,
								path, lev, title,
								objectType, idResource, idCategory, idUser,
								idAuthor, version, difficult, description,
								language, resource, objective, dateInsert,
								idCourse, prerequisites, isTerminator, idParam,
								visible, milestone)
								VALUES
								('".( isset($id_orgs[ $source->idParent ]) ? $id_orgs[ $source->idParent ] : 0 )."',
								'".$source->path."', '".$source->lev."', '".sql_escape_string($source->title)."',
								'".$source->objectType."', '".$source->idResource."', '".$source->idCategory."', '".$source->idUser."',
								'".$source->idAuthor."', '".$source->version."', '".$source->difficult."', '".sql_escape_string($source->description)."',
								'".$source->language."', '".$source->resource."', '".$source->objective."', '".$source->dateInsert."',
								'".$new_id_course."', '".$source->prerequisites."', '".$source->isTerminator."', '".$source->idParam."',
								'".$source->visible."', '".$source->milestone."')";
							$re_new_org = sql_query($query_new_org);
							$new_id_reference = sql_insert_id();

							// map for later use
							$org_map['folder'][$source->idOrg] = $new_id_reference;
						} else {

							// is an object
							// make a copy
							$lo					= $this->_createLO($source->objectType);
							$new_id_resource	= $lo->copy($source->idResource);

							// create a new row in learning_organization
							$query_new_org = "INSERT INTO %lms_organization (
								idParent, path, lev, title,
								objectType, idResource, idCategory, idUser,
								idAuthor, version, difficult, description,
								language, resource, objective, dateInsert,
								idCourse, prerequisites, isTerminator, idParam,
								visible, milestone)
								VALUES
								('".( isset($id_orgs[ $source->idParent ]) ? $id_orgs[ $source->idParent ] : 0 )."',
								'".$source->path."', '".$source->lev."', '".sql_escape_string($source->title)."',
								'".$source->objectType."', '".$new_id_resource."', '".$source->idCategory."', '".$source->idUser."',
								'".$source->idAuthor."', '".$source->version."', '".$source->difficult."', '".sql_escape_string($source->description)."',
								'".$source->language."', '".$source->resource."', '".$source->objective."', '".$source->dateInsert."',
								'".$new_id_course."', '".$source->prerequisites."', '".$source->isTerminator."', '0',
								'".$source->visible."', '".$source->milestone."')";
							$re_new_org = sql_query($query_new_org);
							$new_id_reference = sql_insert_id();

							// for a learning_object we have to create a row in lo_param as well
							// with 4.1 or 4.2 we plan to remove this table, but until then we need this
							$query_lo_par  = "INSERT INTO %lms_lo_param (param_name, param_value) VALUES ('idReference', '".$new_id_reference."') ";
							$result_lo_par = sql_query($query_lo_par);
							$id_lo_par = sql_insert_id();

							$query_up_lo = "UPDATE %lms_lo_param SET idParam = '".$id_lo_par."' WHERE id = '".$id_lo_par."' ";
							$result_up_lo = sql_query($query_up_lo);

							$query_up_or = "UPDATE %lms_organization SET idParam = '".$id_lo_par."' WHERE idOrg = '".$new_id_reference."' ";
							$result_up_or = sql_query($query_up_or);

							// map for later use
							$org_map[$source->objectType][$source->idResource] = $new_id_resource;
						}
						// create a map for the olds and new idReferences
						$id_orgs[$source->idOrg] = $new_id_reference;
						if($source->prerequisites != '') $prereq_map[$new_id_reference] = $source->prerequisites;
					}
					
				}
				
				// updates prerequisites
				foreach($prereq_map as $new_id_reference => $old_prerequisites) {
					
					$new_prerequisites = array();
					$old_prerequisites = explode(",", $old_prerequisites);
					foreach($old_prerequisites as $old_p) {
						
						//a prerequisite can be a pure number or something like 7=NULL, or 7=incomplete
						$old_id = intval($old_p);
						if(isset($id_orgs[$old_id])) $new_prerequisites[] = str_replace($old_id, $id_orgs[$old_id], $old_p );
					}
					if(!empty($new_prerequisites)) {
						
						$query_updcor = "UPDATE %lms_organization "
							."SET prerequisites = '".implode(",", $new_prerequisites)."' "
							."WHERE idOrg = ".$new_id_reference." ";
						$result_upcor = sql_query($query_updcor);
					}
				}
				
				//--- copy forum data --------------------------------------------------

				$query_insert_list = array();
				$query_selmenun = "SELECT * FROM %lms_forum WHERE idCourse = '".$id_dupcourse."' ";
				$result_selmenun = sql_query($query_selmenun);
				while($new_org = sql_fetch_assoc($result_selmenun)) {
					$query_insert_list[] = "('".$new_course_dup."', '".sql_escape_string($new_org['title'])."', '".sql_escape_string($new_org['description'])."',
						'".$new_org['locked']."', '".$new_org['sequence']."', '".$new_org['emoticons']."')";
				}
				$result_dupmen = true;
				if (!empty($query_insert_list)) {
					$query_dupmen = "INSERT INTO %lms_forum
						(idCourse, title, description, locked, sequence, emoticons)
						VALUES ".implode(",", $query_insert_list);
					$result_dupmen = sql_query($query_dupmen);
				}

				//--- end forum --------------------------------------------------------




				//--- copy coursereports data ------------------------------------------

				//create a conversion table for tests and scoitems coursereports
				$array_organization = array(
					'test' => array(),
					'scoitem' => array()
				);
				$arr_items_flat = array(
					'test' => array(),
					'scoitem' => array()
				);
				$query_org = "SELECT source_of, id_source
					FROM %lms_coursereport WHERE id_course = '".$id_dupcourse."'
					AND source_of IN ('test', 'scoitem')";
				$res_org = sql_query($query_org);
				while (list($source_of, $id_source) = sql_fetch_row($res_org)) {
					switch ($source_of) {
						case 'scoitem': $arr_items_flat['scoitem'][] = $id_source; break;
					}
				}


				if (!empty($arr_items_flat['scoitem'])) {
					//retrieve idOrgs of scoitems' scormorgs
					$arr_old_idorg = array();
					$arr_old_ident = array();
					$query = "SELECT o.idOrg, o.idResource, s.idscorm_item, s.item_identifier
						FROM %lms_organization AS o
						JOIN %lms_scorm_items AS s
						ON (o.idResource = s.idscorm_organization)
						WHERE s.idscorm_item IN (".implode(",", $arr_items_flat['scoitem']).")
						AND o.objectType = 'scormorg'";
					$res = sql_query($query);
					while (list($idOrg, $idResource, $idscorm_item, $item_identifier) = sql_fetch_row($res)) {
						$arr_old_idorg[] = $idOrg;
						$arr_old_ident[$idOrg.'/'.$item_identifier] = $idscorm_item;
					}
					if (!empty($arr_old_idorg)) {
						$arr_new_idorg = array();
						foreach ($arr_old_idorg as $idOrg) {
							$arr_new_idorg[] = $id_orgs[$idOrg];
						}
						$query = "SELECT o.idOrg, o.idResource, s.idscorm_item, s.item_identifier
							FROM %lms_organization AS o
							JOIN %lms_scorm_items AS s
							ON (o.idResource = s.idscorm_organization)
							WHERE o.idOrg IN (".implode(",", $arr_new_idorg).")
							AND o.objectType = 'scormorg'";
						$res = sql_query($query);
						$new_to_old = array_flip($id_orgs);
						while (list($idOrg, $idResource, $idscorm_item, $item_identifier) = sql_fetch_row($res)) {
							$_key = $new_to_old[$idOrg].'/'.$item_identifier;
							if (array_key_exists($_key, $arr_old_ident)) {
								$_index = $arr_old_ident[ $_key ];
								$array_organization['scoitem'][$_index] = $idscorm_item;
							}
						}
					}
				}

				$query_insert_list = array();
				$query_selmenun = "SELECT * FROM %lms_coursereport WHERE id_course = '".$id_dupcourse."' ";
				$result_selmenun = sql_query($query_selmenun);
				while($new_org = sql_fetch_array($result_selmenun)) {
					
					$id_source_val = 0;
					switch ($new_org['source_of']) {
						case 'test': {
							$id_source_val = !isset($org_map['test'][$new_org['id_source']])
								? 0
								: $org_map['test'][$new_org['id_source']];
						} break;
						case 'scoitem': {
							$id_source_val = !isset($array_organization['scoitem'][$new_org['id_source']]) || $array_organization['scoitem'][$new_org['id_source']] == ""
								? 0
								:	$array_organization['scoitem'][$new_org['id_source']];
						} break;
					}

					$query_insert_list[] = "('".$new_course_dup."', '".sql_escape_string($new_org['title'])."', '".$new_org['max_score']."',
						'".$new_org['required_score']."', '".$new_org['weight']."', '".$new_org['show_to_user']."',
						'".$new_org['use_for_final']."', '".$new_org['sequence']."', '".$new_org['source_of']."',
						'".$id_source_val."')";
				}

				$result_dupman = true;
				if (!empty($query_insert_list)) {
					$query_dupmen = "INSERT INTO %lms_coursereport
						(id_course,title,max_score,required_score,weight,show_to_user,use_for_final,sequence,source_of,id_source)
						VALUES ".implode(",", $query_insert_list);
					$result_dupmen = sql_query($query_dupmen);
				}
				//--- end coursereports ------------------------------------------------




				//--- copy htmlfront data ----------------------------------------------

				$query_insert_list = array();
				$query_selmenun = "SELECT * FROM %lms_htmlfront WHERE id_course = '".$id_dupcourse."' ";
				$result_selmenun = sql_query($query_selmenun);
				while($new_org = sql_fetch_array($result_selmenun)){
					$query_insert_list[] = "('".$new_course_dup."', '".sql_escape_string($new_org['textof'])."')";
				}
				
				$result_dupmen = true;
				if (!empty($query_insert_list)) {
					$query_dupmen = "INSERT INTO %lms_htmlfront
						(id_course, textof)
						VALUES ".implode(",", $query_insert_list);
					$result_dupmen = sql_query($query_dupmen);
				}

				//--- end htmlfront ----------------------------------------------------

			}



			if(isset($_POST['advice']))
			{
				$query =	"SELECT * FROM %lms_advice WHERE idCourse = ".(int)$id_dupcourse;
				$result = sql_query($query);

				if(sql_num_rows($result) > 0) {
					$query_insert_list = array();

					$array_sub = array();
					$array_replace = array();

					foreach($id_orgs as $id_old_obj => $id_new_obj) {
						$array_sub[] = 'id_org='. $id_old_obj;
						$array_replace[] = 'id_org='.$id_new_obj;
						//convert direct links to LOs. TO DO: make sure you are changing only the correct link urls
						$array_sub[] = 'id_item='. $id_old_obj;
						$array_replace[] = 'id_item='.$id_new_obj;
					}

					while($row = sql_fetch_assoc($result)) {
						$new_description = (!empty($id_orgs)) ? str_replace($array_sub, $array_replace, $row['description']) : $row['description'];
						$query_insert_list[] = "(NULL, ".(int)$new_course_dup.", '".$row['posted']."', ".(int)$row['author'].", '".$row['title']."', '".$new_description."', ".(int)$row['important'].")";
					}

					if (!empty($query_insert_list)) {
						$query =	"INSERT INTO %lms_advice
							(idAdvice, idCourse, posted, author, title, description, important)
							VALUES ".implode(",", $query_insert_list);
						sql_query($query);
					}
				}
			}

			ob_clean();
			echo $this->json->encode(array('success' => true));
		}
	}

	public function certificate()
	{
		$perm_assign = checkPerm('assign', true, 'certificate', 'lms');

		if (!$perm_assign && !$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r='.$this->base_link_course.'/show'
			));
			return;
		}
		
		if(isset($_POST['undo']))
			Util::jump_to('index.php?r='.$this->base_link_course.'/show');

		require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
		$cert = new Certificate();

		$id_course = Get::req('id_course', DOTY_INT, 0);

		if(isset($_POST['assign']))
		{
			$point_required = Get::req('point_required', DOTY_INT, 0);
			
			// , $list_of_assign_obj, $list_of_who
			
			if(!$cert->updateCertificateCourseAssign($id_course, $_POST['certificate_assign'], $_POST['certificate_ex_assign'], $point_required))
				Util::jump_to('index.php?r='.$this->base_link_course.'/show&err=_up_cert_err');
			Util::jump_to('index.php?r='.$this->base_link_course.'/show&res=_up_cert_ok');
		}
		else
		{
			require_once(_base_.'/lib/lib.table.php');

			$all_languages 	= Docebo::langManager()->getAllLanguages(true);
			$languages = array();
			foreach($all_languages as $k => $v)
				$languages[$v['code']] = $v['description'];

			$query =	"SELECT code, name, course_type"
						." FROM %lms_course WHERE idCourse = '".$id_course."'";
			$course = sql_fetch_array(sql_query($query));

			$tb	= new Table(false, Lang::t('_TITLE_CERTIFICATE_TO_COURSE', 'course'), Lang::t('_TITLE_CERTIFICATE_TO_COURSE', 'course'));

			$certificate_list = $cert->getCertificateList();
			$course_cert = $cert->getCourseCertificate($id_course);
			$course_ex_cert = $cert->getCourseExCertificate($id_course);
			$released = $cert->numOfcertificateReleasedForCourse($id_course);
			$point_required = $cert->getPointRequiredForCourse($id_course);

			$possible_status = array(
				AVS_NOT_ASSIGNED 					=> Lang::t('_NOT_ASSIGNED', 'course'),
				AVS_ASSIGN_FOR_ALL_STATUS 			=> Lang::t('_ASSIGN_FOR_ALL_STATUS', 'course'),
				AVS_ASSIGN_FOR_STATUS_INCOURSE 		=> Lang::t('_ASSIGN_FOR_STATUS_INCOURSE', 'course'),
				AVS_ASSIGN_FOR_STATUS_COMPLETED 	=> Lang::t('_ASSIGN_FOR_STATUS_COMPLETED', 'course')
			);

			$type_h = array('nowrap', 'nowrap', '', '', 'image');
			$cont_h	= array(
				Lang::t('_TITLE', 'course'),
				Lang::t('_CERTIFICATE_LANGUAGE', 'course'),
				Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course'),
				Lang::t('_CERTIFICATE_EX_ASSIGN_STATUS', 'course'),
				Lang::t('_CERTIFICATE_RELEASED', 'course')
			);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);

			$view_cert = false;
			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
			{
				if(checkPerm('view', true, 'certificate', 'lms'))
					$view_cert = true;
			}
			else
				$view_cert = true;

			while(list($id_cert, $cert) = each($certificate_list))
			{
				$cont = array();
				$cont[] = '<label for="certificate_assign_'.$id_cert.'">'.$cert[CERT_NAME].'</label>';
				$cont[] = (isset($languages[$cert[CERT_LANG]]) ? $languages[$cert[CERT_LANG]] : $cert[CERT_LANG]); //lang description?
				$cont[] = Form::getInputDropdown(	'dropdown_nowh',
													'certificate_assign_'.$id_cert,
													'certificate_assign['.$id_cert.']',
													$possible_status,
                                                                ( isset($course_cert[$id_cert]['available_for_status']) ? $course_cert[$id_cert]['available_for_status'] : 0 ),
                                                                '' )
                                                                ."<br/>"
                                                                .Lang::t('_ASSIGN_FOR_AT_LEAST_MINUTES', 'course').' '
                                                                .Form::getInputTextfield( 'dropdown_nowh',
                                                                'certificate_assign_minutes_'.$id_cert,
                                                                'certificate_assign_minutes['.$id_cert.']',
                                                                $course_cert[$id_cert]['minutes_required'],
                                                                '',
                                                                6,
                                                                'style="width: 40px; text-align: right;"');
				$cont[] = Form::getInputDropdown(	'dropdown_nowh',
													'certificate_ex_assign_'.$id_cert,
													'certificate_ex_assign['.$id_cert.']',
													$possible_status,
													( isset($course_ex_cert[$id_cert]) ? $course_ex_cert[$id_cert] : 0 ),
													'' );
                $cont[] = (isset($course_cert[$id_cert]) && $course_cert[$id_cert] != 0 && $view_cert ? '<a href="index.php?r=alms/course/list_certificate&amp;id_certificate='.$id_cert.'&amp;id_course='.$id_course.'&amp;from=course&amp;of_platform=lms"><b><u>' : '').( isset($released[$id_cert]) ? $released[$id_cert] : '0' ).(isset($course_cert[$id_cert]) && $course_cert[$id_cert] != 0  ? '</b></u></a>' : '');

				$tb->addBody($cont);
			}

			$course_info = $this->model->getInfo($id_course);
			$course_name = ($course_info['code'] !== '' ? '['.$course_info['code'].'] ' : '').$course_info['name'];

			$this->render(
					'certificate', array(
					'id_course' => $id_course,
					'tb' => $tb,
					'point_required' => $point_required,
					'base_link_course' => $this->base_link_course,
					'course_name' => $course_name
			));
		}
	}

    
	public function menu()
	{
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r='.$this->base_link_course.'/show'
			));
			return;
		}

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r='.$this->base_link_course.'/show');

		$id_course = Get::req('id_course', DOTY_INT, 0);

		if(isset($_POST['assign']))
		{
			$id_custom = Get::req('selected_menu', DOTY_INT, 0);

			require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

			$acl_man =& Docebo::user()->getAclManager();
			$course_man = new Man_Course();

			$levels =& $course_man->getCourseIdstGroupLevel($id_course);
			if(empty($levels) || implode('', $levels) == '')
				$levels =& DoceboCourse::createCourseLevel($id_course);

			$course_man->removeCourseRole($id_course);
			$course_man->removeCourseMenu($id_course);
			$course_idst =& $course_man->getCourseIdstGroupLevel($id_course);

			$result = createCourseMenuFromCustom($id_custom, $id_course, $course_idst);

			if($_SESSION['idCourse'] == $id_course)
			{
				$query =	"SELECT module.idModule, main.idMain
							FROM ( ".$GLOBALS['prefix_lms']."_menucourse_main AS main JOIN
							".$GLOBALS['prefix_lms']."_menucourse_under AS un ) JOIN
							".$GLOBALS['prefix_lms']."_module AS module
							WHERE main.idMain = un.idMain AND un.idModule = module.idModule
							AND main.idCourse = '".(int)$_SESSION['idCourse']."'
							AND un.idCourse = '".(int)$_SESSION['idCourse']."'
							ORDER BY main.sequence, un.sequence
							LIMIT 0,1";

				list($id_module, $id_main) = sql_fetch_row(sql_query($query));

				$_SESSION['current_main_menu'] = $id_main;
				$_SESSION['sel_module_id'] = $id_module;

				//loading related ST
				Docebo::user()->loadUserSectionST('/lms/course/public/');
				Docebo::user()->SaveInSession();
			}

			if($result)
				Util::jump_to('index.php?r='.$this->base_link_course.'/show&res=_up_menu_ok');
			Util::jump_to('index.php?r='.$this->base_link_course.'/show&res=_up_menu_err');
		}
		else
		{
			require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
			$menu_custom = getAllCustom();
                        $sel_custom = getAssociatedCustom($id_course);

			$course_info = $this->model->getInfo($id_course);
			$course_name = ($course_info['code'] !== '' ? '['.$course_info['code'].'] ' : '').$course_info['name'];

			$this->render('menu', array(
				'menu_custom' => $menu_custom,
				'sel_custom' => $sel_custom,
				'id_course' => $id_course,
				'base_link_course' => $this->base_link_course,
				'course_name' => $course_name
			));
		}
	}

	public function newcourse()
	{
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r='.$this->base_link_course.'/show'
			));
			return;
		}

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r='.$this->base_link_course.'/show');

		if(isset($_POST['save']))
		{
			//resolve course type
			if($_POST['course_type'] == 'edition') {

				$_POST['course_type'] = 'elearning';
				$_POST['course_edition'] = 1;
			} else {

				$_POST['course_edition'] = 0;
			}

			$result = $this->model->insCourse($_POST);
			$url = 'index.php?r='.$this->base_link_course.'/show';
			foreach($result as $key => $value)
				$url .= '&'.$key.'='.$value;
			Util::jump_to($url);
		}
		else
			$this->coursemask();
	}

	public function modcourse()
	{
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r='.$this->base_link_course.'/show'
			));
			return;
		}

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r='.$this->base_link_course.'/show');

		$id_course = Get::req('id_course', DOTY_INT, 0);

		if(isset($_POST['save']))
		{
			//resolve course type
			if($_POST['course_type'] == 'edition') {

				$_POST['course_type'] = 'elearning';
				$_POST['course_edition'] = 1;
			} else {

				$_POST['course_edition'] = 0;
			}
			
			$result = $this->model->upCourse($id_course, $_POST);
			$url = 'index.php?r='.$this->base_link_course.'/show';
			foreach($result as $key => $value)
				$url .= '&'.$key.'='.$value;
			Util::jump_to($url);
		}
		else
			$this->coursemask($id_course);
	}

	public function delcourse()
	{
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		if(Get::cfg('demo_mode'))
			die('Cannot del course during demo mode.');

		if(isset($_GET['confirm']))
		{
			$id_course = Get::req('id_course', DOTY_INT, 0);

			$op_res = $this->model->delCourse($id_course);
			if ($op_res && isset($_SESSION['idCourse']) && $_SESSION['idCourse'] == $id_course) unset($_SESSION['idCourse']);
			$res = array('success' => $op_res);

			echo $this->json->encode($res);
		}
	}

	public function coursemask($id_course = false)
	{
		$perm_requested = $id_course ? 'mod' : 'add';
		if (!$this->permissions[$perm_requested]) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r='.$this->base_link_course.'/show'
			));
			return;
		}

		YuiLib::load();

		require_once(_lms_.'/lib/lib.levels.php');
		require_once(_lms_.'/admin/models/LabelAlms.php');
		$levels = CourseLevel::getLevels();
		$label_model = new LabelAlms();

		$array_lang = Docebo::langManager()->getAllLangCode();
		$array_lang[] = 'none';

		//status of course -----------------------------------------------------
		$status = array(
			CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
			CST_AVAILABLE 	=> Lang::t('_CST_AVAILABLE', 'course'),
			CST_EFFECTIVE 	=> Lang::t('_CST_CONFIRMED', 'course'),
			CST_CONCLUDED 	=> Lang::t('_CST_CONCLUDED', 'course'),
			CST_CANCELLED 	=> Lang::t('_CST_CANCELLED', 'course'));
		//difficult ------------------------------------------------------------
		$difficult_lang = array(
			'veryeasy' 		=> Lang::t('_DIFFICULT_VERYEASY', 'course'),
			'easy' 			=> Lang::t('_DIFFICULT_EASY', 'course'),
			'medium' 		=> Lang::t('_DIFFICULT_MEDIUM', 'course'),
			'difficult' 	=> Lang::t('_DIFFICULT_DIFFICULT', 'course'),
			'verydifficult' => Lang::t('_DIFFICULT_VERYDIFFICULT', 'course'));
		//type of course -------------------------------------------------------
		$course_type= array (
			'classroom' 	=> Lang::t('_CLASSROOM', 'course'),
			'elearning' 	=> Lang::t('_COURSE_TYPE_ELEARNING', 'course'),
			'edition'		=> Lang::t('_COURSE_TYPE_EDITION', 'course')
		);
			
		$show_who_online = array(
			0				=> Lang::t('_DONT_SHOW', 'course'),
			_SHOW_COUNT 	=> Lang::t('_SHOW_COUNT', 'course'),
			_SHOW_INSTMSG 	=> Lang::t('_SHOW_INSTMSG', 'course'));

		$hours = array('-1' => '- -', '0' =>'00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
					'10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
					'20', '21', '22', '23' );
		$quarter = array('-1' => '- -', '00' => '00', '15' => '15', '30' => '30', '45' => '45');

		$params = array(
			'id_course' => $id_course,
			'levels' => $levels,
			'array_lang' => $array_lang,
			'label_model' => $label_model,
			'status' => $status,
			'difficult_lang' => $difficult_lang,
			'course_type' => $course_type,
			'show_who_online' => $show_who_online,
			'hours' => $hours,
			'quarter' => $quarter,
			'model' => $this->model
		);

		if($id_course === false)
		{
			require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
			$menu_custom = getAllCustom();
			list($sel_custom) = current($menu_custom);
			reset($menu_custom);

			$params['menu_custom'] = $menu_custom;
			$params['sel_custom'] = $sel_custom;

			$params['name_category'] = $this->model->getCategoryName($this->_getSessionTreeData('id_category', 0));
		}

		$params['course'] = $this->model->getCourseModDetails($id_course);
		//resolve edition flag into type
		if($params['course']['course_edition'] == 1) $params['course']['course_type'] = 'edition';

		if ($id_course == false) {
			$params['has_editions_or_classrooms'] = false;
		} else {
			$params['has_editions_or_classrooms'] = $this->model->hasEditionsOrClassrooms($id_course);
		}

		if($params['course']['hour_begin'] != '-1') {
			$hb_sel = (int)substr($params['course']['hour_begin'], 0, 2);
			$qb_sel = substr($params['course']['hour_begin'], 3, 2);
		} else {
			$hb_sel = $qb_sel = '-1';
		}
		if($params['course']['hour_end'] != '-1')
		{
			$he_sel = (int)substr($params['course']['hour_end'], 0, 2);
			$qe_sel = substr($params['course']['hour_end'], 3, 2);
		} else {
			$he_sel = $qe_sel = '-1';
		}
		$params['hb_sel'] = $hb_sel;
		$params['qb_sel'] = $qb_sel;
		$params['he_sel'] = $he_sel;
		$params['qe_sel'] = $qe_sel;
		$params['base_link_course'] = $this->base_link_course;

		$params['use_unsubscribe_date_limit'] = (bool)($params['course']['unsubscribe_date_limit'] != '');
		$params['unsubscribe_date_limit'] = $params['course']['unsubscribe_date_limit'] != '' && $params['course']['unsubscribe_date_limit'] != "0000-00-00 00:00:00" 
			? Format::date($params['course']['unsubscribe_date_limit'], 'date')
			: "";

		$this->render('maskcourse', $params);
	}
    
    

    public function list_certificate(){
        
        $id_course = Get::req('id_course', DOTY_INT, 0);
        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);
        $from = Get::req('from');
        $op = Get::req('op');
        
        
        require_once(Forma::inc(_adm_.'/lib/lib.field.php'));
        $fman = new FieldList();
        $custom_field_array = $fman->getFlatAllFields();        
 
        $data_certificate = $this->model->getListTototalUserCertificate($id_course, $id_certificate, $custom_field_array);
        // pushing empty element at the top of array  
        foreach ($data_certificate as $key => $value) {
            array_unshift($data_certificate[$key], '');
        } 

        
        
        $course_info = $this->model->getCourseModDetails($id_course);
        $this->render(
                    'list_certificate', array(
                    'id_course' => $id_course,
                    'id_certificate' => $id_certificate,
                    'course_type' => $course_info['course_type'],
                    'course_name' => $course_info['name'],
                    'from' => $from ,
                    'data_certificate' => $data_certificate  ,
                    'custom_fields' =>$custom_field_array,
                    'op' => $op
                    
        ));        
        
    }

    
}
?>