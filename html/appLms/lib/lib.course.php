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

/**
 * @package DoceboLms
 * @subpackage Course managment
 * @author Fabio Pirovano <fabio [at] docebo-com>
 * @version  $Id: lib.course.php 1002 2007-03-24 11:55:51Z fabio $
 */

define("CST_PREPARATION", 	0);
define("CST_AVAILABLE", 	1);
define("CST_EFFECTIVE", 	2);
define("CST_CONCLUDED", 	3);
define("CST_CANCELLED", 	4);

// course quota
require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');
define("COURSE_QUOTA_INHERIT", -1);
define("COURSE_QUOTA_UNLIMIT", 	0);

define("_SHOW_COUNT", 	1);
define("_SHOW_INSTMSG", 2);

class Selector_Course {

	var $treeview = NULL;

	var $treeDB = NULL;

	var $show_filter = true;

	var $filter = array();

	var $current_page = array();

	var $current_selection = array();

	/**
	 * Class constructor
	 */
	function Selector_Course() {

		require_once($GLOBALS['where_lms'].'/admin/modules/category/tree.category.php');
		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');

		$lang =& DoceboLanguage::createInstance('course', 'lms');

		$this->show_filter = true;
		$this->treeDB 		= new TreeDb_CatDb($GLOBALS['prefix_lms'].'_category');
		$this->treeview 	= new TreeView_CatView($this->treeDB , 'course_category', $lang->def('_CATEGORY'));
		$this->treeview->hideInlineAction();
	}

	function enableFilter() {

		$this->show_filter = true;
	}

	function disableFilter() {

		$this->show_filter = false;
	}

	/**
	 * return the current status in a pratic format
	 * @return string a string with the data used for reloading the current status
	 */
	function getStatus() {

		$status = array(
			'page' 					=> $this->current_page,
			'filter' 				=> serialize($this->filter),
			'show_filter' 			=> $this->show_filter,
			'current_selection' 	=> $this->current_selection,
			'treeview_status' 		=> serialize($this->treeview) );
		return serialize($status);
	}

	/**
	 * reset the current status to te given one
	 * @param string	$status_serialized a valid status saved using getStatus
	 */
	function loadStatus(&$status_serialized) {

		if($status_serialized == '') return ;
		$status = unserialize($status_serialized);
		$this->current_page			= $status['page'];
		$this->filter				= unserialize($status['filter']);
		$this->show_filter			= $status['show_filter'];
		$this->current_selection	= $status['current_selection'];
		$this->treeview				= unserialize($status['treeview_status']);
		$this->treeDB 				=& $this->treeview->getTreeDb();
	}

	function parseForAction($array_action) {

	}

	function parseForState($array_state) {

		// load change in treeview
		$this->treeview->parsePositionData($array_state, $array_state, $array_state);
		// older selection
		if(isset($array_state['course_selected'])) {

			$this->current_selection = Util::unserialize(urldecode($array_state['course_selected']));
		}
		// add last selection
		if(isset($_POST['new_course_selected'])) {
			while(list($id_c) = each($array_state['new_course_selected'])) {

				$this->current_selection[$id_c] = $id_c;
			}
		}
	}

	function stateSelection() {

		return Form::getHidden('course_selected', 'course_selected', urlencode(Util::serialize($this->current_selection)) );
	}

	function getSelection() {

		return $this->current_selection;
	}


	function resetSelection($new_selection) {

		$this->current_selection = $new_selection;
	}

	function loadCourseSelector($noprint=false, $with_assesment=false) {

		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_.'/lib/lib.form.php');

		$lang =& DoceboLanguage::createInstance('course', 'lms');

		$output='';
		$output.=$this->treeview->load();

		// Filter

		$this->filter['course_flat'] = isset($_POST['c_flatview']);
		//$this->filter['course_code'] = ( isset($_POST['c_filter_code']) ? $_POST['c_filter_code'] : '' );
		$this->filter['course_name'] = ( isset($_POST['c_filter_name']) ? $_POST['c_filter_name'] : '' );
		if($this->show_filter === true) {
			$output .= '<div class="quick_search_form">'
				.'<div class="common_options">'
				.Form::getInputCheckbox('c_flatview', 'c_flatview', '1', (Get::req('c_flatview', DOTY_INT, '0') == '1' ? true : false), ' onclick="submit();" ')
					.' <label class="label_normal" for="c_flatview">'.Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory').'</label>'
					.'&nbsp;&nbsp;&nbsp;&nbsp;'
				.'</div>'
				.'<div>'
				.Form::getInputTextfield( "search_t", "c_filter_name", "c_filter_name", Get::req('c_filter_name', DOTY_ALPHANUM, ''), '', 255, '' )
				.Form::getButton( "c_filter_set", "c_filter_set", Lang::t('_SEARCH', 'standard'), "search_b")
				.'</div>'
				.'</div>';
		}
		// End Filter

		$tb = new Table(Get::sett('visu_course'), $lang->def('_COURSE_LIST'), $lang->def('_COURSE_LIST_SUMMARY'));

		$tb->initNavBar('ini', 'button');
		$ini = $tb->getSelectedElement();

		$category_selected 	= ($this->treeview->getSelectedFolderId() != $this->treeview->GetRootName() ? $this->treeview->getSelectedFolderId() : "0" );
		if($this->filter['course_flat']) {
			$id_categories 		= $this->treeDB->getDescendantsId($this->treeDB->getFolderById($category_selected));
			$id_categories[] 	= $category_selected;
		}
		$select = "
		SELECT c.idCourse, c.code, c.name, c.description, c.status, c.difficult,
			c.subscribe_method, c.permCloseLo, c.show_rules, c.max_num_subscribe ";
		$query_course = "
		FROM ".$GLOBALS['prefix_lms']."_course AS c
		WHERE ".($with_assesment ? '1' : "c.course_type <> 'assessment'").
			" AND c.idCategory IN ( ".( !$this->filter['course_flat'] ? $category_selected  : implode(",", $id_categories) )." )";
		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			$all_courses = false;

			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
			$all_courses = false;
			if(isset($admin_courses['course'][0]))
				$all_courses = true;
			elseif(isset($admin_courses['course'][-1]))
			{
				require_once(_lms_.'/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if(count($user_catalogue) > 0)
				{
					$courses = array(0);

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach($courses as $id_course)
						if($id_course != 0)
							$admin_courses['course'][$id_course] = $id_course;
				}
				elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			}
			else
			{
				$array_courses = array();
				$array_courses = array_merge($array_courses, $admin_courses['course']);

				if(!empty($admin_courses['coursepath']))
				{
					require_once(_lms_.'/lib/lib.coursepath.php');
					$path_man = new CoursePath_Manager();
					$coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if(!empty($admin_courses['catalogue']))
				{
					require_once(_lms_.'/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach($admin_courses['catalogue'] as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
			}

			if(!$all_courses)
			{
				if(empty($admin_courses['course']))
					$query_course .= " AND 0 ";
				else
					$query_course .= " AND c.idCourse IN (".implode(',', $admin_courses['course']).") ";
			}
		}/*
		if($this->filter['course_code'] != '') {
			$query_course .= " AND c.code LIKE '%".$this->filter['course_code']."%'";
		}*/
		if($this->filter['course_name'] != '') {
			$query_course .= " AND ( c.code LIKE '%".$this->filter['course_name']."%' OR c.name LIKE '%".$this->filter['course_name']."%' ) ";
		}
		list($tot_course) = sql_fetch_row(sql_query("SELECT COUNT(*) ".$query_course));
		$query_course .= " ORDER BY c.name
							LIMIT ".$ini.",".(int)Get::sett('visuItem', 25);

		$re_course = sql_query($select.$query_course);

		$type_h = array('image', '', '', '');
		$cont_h = array(
			'<span class="access-only">'.$lang->def('_COURSE_SELECTION').'</span>',
			$lang->def('_CODE'),
			$lang->def('_COURSE_NAME'),
			$lang->def('_STATUS')
		);
		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		$status_array = array(	CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
							CST_AVAILABLE 	=> Lang::t('_CST_AVAILABLE', 'course'),
							CST_EFFECTIVE 	=> Lang::t('_CST_CONFIRMED', 'course'),
							CST_CONCLUDED 	=> Lang::t('_CST_CONCLUDED', 'course'),
							CST_CANCELLED 	=> Lang::t('_CST_CANCELLED', 'course'));

		while(list($id_course, $code, $name, $desc, $status, $difficult, $auto_sub, $end_mode, $show_rules, $max_user_sub) = sql_fetch_row($re_course)) {

			$tb_content = array(
				Form::getInputCheckbox('new_course_selected_'.$id_course, 'new_course_selected['.$id_course.']', $id_course,
					isset($this->current_selection[$id_course]), ''),
				'<label for="new_course_selected_'.$id_course.'">'.$code.'</label>',
				'<label for="new_course_selected_'.$id_course.'">'.$name.'</label>'
			);

			$tb_content[] = $status_array[$status];

			$tb->addBody($tb_content);
			if(isset($this->current_selection[$id_course])) unset($this->current_selection[$id_course]);
		}

		$output.=//$GLOBALS['page']->add(
			$tb->getTable()
			.$tb->getNavBar($ini, $tot_course)
			.$this->stateSelection();//, 'content');
		if ($noprint) return $output; else cout($output);
	}
}

class Man_Course {

	function Man_Course() {

	}

	function saveCourseStatus() {/*
		require_once($GLOBALS['where_lms'].'/admin/modules/category/tree.category.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');

		$categoryDb = new TreeDb_CatDb($GLOBALS['prefix_lms'].'_category');
		$treeView = new TreeView_CatView($categoryDb, 'course_category', Lang::t('_CATEGORY', 'course', 'lms'));
		$treeView->parsePositionData($_POST, $_POST, $_POST);

		//save status
		$o_save = new Session_Save();
		$tree_status = $o_save->getName('course_category', true);
		$o_save->save($tree_status, $treeView);*/
	}

	/**
	 * @param int 	$id_course			the id of the course
	 *
	 * @return array	return som info about the course [code, name, description, status, difficult, subscribe_method, max_num_subscribe]
	 */
	function getCourseInfo($id_course) {

		$query = "
		SELECT *
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE idCourse = '".$id_course."'";
		$re = sql_query($query);

		return sql_fetch_assoc($re);
	}

	/**
   * @param int 	$edition_id			the id of the edition
	 * @param int 	$course_id			the id of the course
	 *
	 * @return array	return som info about the course [code, name, description, status, difficult, subscribe_method, max_num_subscribe]
	 */
	function getEditionInfo($edition_id, $course_id=FALSE) {

		$query = "
		SELECT *
		FROM ".$GLOBALS['prefix_lms']."_course_edition
		WHERE idCourseEdition = '".$edition_id."'";
		if (($course_id !== FALSE) && ($course_id > 0)) {
			$query.=" AND idCourse = '".$course_id."'";
		}
		$re = sql_query($query);

		return sql_fetch_assoc($re);
	}

	/**
	 * return the list of all the courses in the platform, or fillter by category
	 * @param int 	$id_category	filter for passed category
	 *
	 * @return array	[id_course] => ( [id_course], [name], [course] )
	 */
	function getAllCourses($id_category = false, $type_of = false, $arr_courses = false, $no_status = false) {

		$courses = array();
		$query_course = "
		SELECT idCourse, code, name, description
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE 1 ";
		if($no_status) $query_course.=" AND status <> '".CST_PREPARATION."' ";
		if($type_of !== 'assessment' && $type_of !== 'all' && $type_of !== 'edition') $query_course .= " AND course_type <> 'assessment' ";
		if($id_category !== false) $query_course .= " AND idCategory = '".$id_category."' ";
		if($type_of !== false && $type_of !== 'all' && $type_of !== 'edition') $query_course .= " AND course_type = '".$type_of."' ";
		if($type_of === 'edition') $query_course .= " AND course_edition = '1' ";
		if($arr_courses !== false) $query_course .= " AND idCourse IN ( ".implode(',', $arr_courses)." )";
		$query_course .= " ORDER BY name";

		$re_course = sql_query($query_course);
		while(list($id, $code, $name, $description) = sql_fetch_row($re_course)) {
			$courses[$id] = array(
				'id_course' => $id,
				'code' => $code,
				'name' => $name,
				'description' => $description
			);
		}

		return $courses;
	}

	/**
	 * Return the names of the courses with the id in the array
	 * @param array $arr_courses an array of id courses
	 * @return array the key will be the id_course and the name will be the value
	 */
	function arrCourseName($arr_courses) {

		$courses = array();
		if(!is_array($arr_courses) || empty($arr_courses)) return $courses;

		$query_course = "SELECT idCourse, name "
			." FROM ".$GLOBALS['prefix_lms']."_course "
			." WHERE idCourse IN ( ".implode(',', $arr_courses)." ) "
			." ORDER BY name";

		$re_course = sql_query($query_course);
		while(list($id, $name) = sql_fetch_row($re_course)) {

			$courses[$id] = $name;
		}
		return $courses;
	}

	function listCourseName($arr_courses) {

		$list = '';
		if(!is_array($arr_courses) || count($arr_courses) == 0) return $list;
		$courses = array();
		$query_course = "
		SELECT name
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE idCourse IN ( ".implode(',', $arr_courses)." ) ";
		$query_course .= " ORDER BY name";

		$re_course = sql_query($query_course);
		$first = true;
		while(list($name) = sql_fetch_row($re_course)) {

			$list .= ( $first ? '' : ', ' ).$name;
			$first = false;
		}
		return $list;
	}

	function getCoursesCount($only_visible = false) {

		$courses = array();
		$query_course = "
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_course ";
		if($only_visible == true) $query_course .= " WHERE show_rules = 0";

		if(!$re_course = sql_query($query_course)) return 0;
		list($number) = sql_fetch_row($re_course);
		return $number;
	}

	/**
	 * return the list of all the courses in the platform, or fillter by category
	 * @param int 	$id_category	filter for passed category
	 *
	 * @return array	[id_course] => ( idCourse, idCategory, code, name, description, lang_code, status, subscribe_method, mediumTime, selling, prize  )
	 */
	function &getAllCoursesWithMoreInfo($id_category = false) {

		$courses = array();
		$query_course = "
		SELECT idCourse, idCategory, code, name, description, lang_code, status,
			subscribe_method, mediumTime, show_rules, selling, prize, course_demo, create_date, course_edition,
			sub_start_date, sub_end_date, date_begin, date_end
		FROM ".$GLOBALS['prefix_lms']."_course ";
		if($id_category !== false) $query_course .= " WHERE idCategory = '".$id_category."' ";
		$query_course .= " ORDER BY name";
		$re_course = sql_query($query_course);
		while($course = sql_fetch_array($re_course)) {

			$courses[$course['idCourse']] = $course;
		}
		return $courses;
	}

	function addCourse($course_info) {

		$field = array();
		$value = array();

		foreach($course_info as $key => $v) {
			$field[] = $key;
			$value[] = "'".$v."'";
		}
		$query_course = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_course
		( ".implode(',', $field)." ) VALUES (".implode(',', $value).") ";

		if(!sql_query($query_course)) return false;

		list($id_course) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

		return $id_course;
	}


	function saveCourse($id_course, $course_info) {

		$field = array();
		$value = array();

		if(!is_array($course_info) || empty($course_info)) return $id_course;

		foreach($course_info as $key => $v) {
			$field[] = $key." = '".$v."'";
		}
		$query_course = "
		UPDATE ".$GLOBALS['prefix_lms']."_course
		SET ".implode(',', $field)."
		WHERE idCourse = '".$id_course."' ";
		if(!sql_query($query_course)) return false;

		return $id_course;
	}

	function deleteCourse($id_course) {

		require_once($GLOBALS['where_lms'].'/admin/modules/course/course.php');
		// delete the course
		if(removeCourse($id_course)) return true;
		return false;
	}

	/**
	 * @param int 	$id_course			the id of the course
	 *
	 * @return array	contains the info of the waiting usersin [user_info] and all the id_user occurrency in [all_user_id]
	 */
	function &getWaitingSubscribed($id_course, $edition_id = 0) {

        
        
        $userlevelid = Docebo::user()->getUserLevelId();
        if($userlevelid != ADMIN_GROUP_GODADMIN)
        {
            // BUG FIX 2469: GETTING THE USERS OF THE ADMIN
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $acl_man =& Docebo::user()->getAclManager();
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
        }
        
        

		$users['users_info'] 	= array();
		$users['all_users_id'] 	= array();

		$cinfo = $this->getCourseInfo($id_course);
		if ($cinfo['course_type'] == 'classroom') {

			$query = "SELECT cu.idUser, cu.level, cu.subscribed_by, cu.status, cdu.overbooking, cd.id_date, cd.code, cd.name "
				." FROM %lms_courseuser AS cu  JOIN %lms_course_date AS cd "
				." JOIN %lms_course_date_user AS cdu ON (cd.id_course = cu.idCourse AND "
				." cd.id_date = cdu.id_date AND cu.idUser=cdu.id_user) "
				." WHERE cd.id_course = ".(int)$id_course." AND (cu.waiting = 1 OR cdu.overbooking = 1)";
            
            // BUG FIX 2469: SELECT ONLY THE USER BELONGING TO THE ADMIN                
            $query .= ($userlevelid != ADMIN_GROUP_GODADMIN
                    ? ( !empty($admin_users) ? " AND cu.idUser IN (".implode(',', $admin_users).")" : " AND cu.idUser IN (0)" )
                    : '' );
        

                
			$res = sql_query($query);
			while ($obj = sql_fetch_object($res)) {
				$users['users_info'][$obj->idUser] = array(
					'id_user' => $obj->idUser,
					'level' => $obj->level,
					'subscribed_by' => $obj->subscribed_by,
					'status' => $obj->status,
					'overbooking' => $obj->overbooking > 0,
					'id_date' => $obj->id_date,
					'code' => $obj->code,
					'name' => $obj->name
				);

				$users['all_users_id'][$obj->idUser] 		= $obj->idUser;
				$users['all_users_id'][$obj->subscribed_by] = $obj->subscribed_by;
			}

		} else {
			$query_courseuser = "
			SELECT idUser, level, subscribed_by, status
			FROM ".$GLOBALS['prefix_lms']."_courseuser
			WHERE idCourse = '".$id_course."' AND waiting = '1' AND  edition_id = '".$edition_id."'";
            $query_courseuser .= ($userlevelid != ADMIN_GROUP_GODADMIN
                ? ( !empty($admin_users) ? " AND idUser IN (".implode(',', $admin_users).")" : " AND idUser IN (0)" )
                : '' );
            
			$re_courseuser = sql_query($query_courseuser);
			while(list($id_user, $lv, $subscribed_by, $status) = sql_fetch_row($re_courseuser)) {

				$users['users_info'][$id_user] 			= array(
					'id_user' => $id_user,
					'level' => $lv,
					'subscribed_by' => $subscribed_by,
					'status' => $status
				);

				$users['all_users_id'][$id_user] 		= $id_user;
				$users['all_users_id'][$subscribed_by] = $subscribed_by;
			}
		}
		return $users;
	}

	/**
	 * Find the idst of the group of a course that represent the level
	 * @param 	int 	$id_course 	the id of the course
	 *
	 * @return 	array	[lv] => idst, [lv] => idst
	 */
	function &getCourseIdstGroupLevel($id_course) {

		$map 		= array();
		$levels 	= CourseLevel::getLevels();
		$acl_man	=& Docebo::user()->getAclManager();


		// find all the group created for this menu custom for permission management
		foreach($levels as $lv => $name_level) {

			$group_info = $acl_man->getGroup(FALSE, '/lms/course/'.$id_course.'/subscribed/'.$lv);
			$map[$lv] 	= $group_info[ACL_INFO_IDST];
		}
		/*
		if($also_waiting === true) {
			$group_info = $acl_man->getGroup(FALSE, '/lms/course/'.$id_course.'/subscribed/waiting');
			$map['waiting'] 	= $group_info[ACL_INFO_IDST];
		}*/
		return $map;
	}

	function getIdUserOfLevel($id_course, $level_number = false, $id_edition = false) {

		$users = array();
		$query_courseuser = "
		SELECT c.idUser
		FROM ".$GLOBALS['prefix_lms']."_courseuser AS c";
		$query_courseuser .= " WHERE c.idCourse = '".$id_course."'";
		if ($id_edition)
			$query_courseuser .= " AND c.edition_id = '".$id_edition."'";
		if($level_number !== false) $query_courseuser .= " AND c.level = '".$level_number."'";
		$re_courseuser = sql_query($query_courseuser);
		while(list($id_user) = sql_fetch_row($re_courseuser)) {

			$users[$id_user] = $id_user;
		}
		return $users;
	}

	/**
	 * @param int 		$id_course		the id of the course
	 * @param array 	$arr_users		if specified filter the user
	 *
	 * @return array	contains the id_user as key and level number as value
	 */
	function getLevelsOfUsers($id_course, $arr_users = false, $edition_id = false) {

		$id_users 	= array();
		if(!is_array($arr_users)) $arr_users = array($arr_users);
		if(count($arr_users) == 0) return $id_users;
		$query_courseuser = "
		SELECT idUser, level
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idCourse = '".$id_course."'";
		if($arr_users !== false) {
			$query_courseuser .= " AND idUser IN ( ".implode(',', $arr_users)." )";
		}
		if($edition_id !== false) {
			$query_courseuser .= " AND edition_id  = '".$edition_id."' ";
		}

		$re_courseuser = sql_query($query_courseuser);
		while(list($id_user, $lv) = sql_fetch_row($re_courseuser)) {

			$id_users[$id_user] = $lv;
		}
		return $id_users;
	}

	function getUserCourses($id_user) {

		// List of  courses
		$re_courses = sql_query("
		SELECT course.idCourse, course.name
		FROM ".$GLOBALS['prefix_lms']."_course AS course JOIN ".$GLOBALS['prefix_lms']."_courseuser AS user
		WHERE course.idCourse = user.idCourse
			AND user.idUser = '".$id_user."'");

		$courses_subscribed = array();
		while(list($id_c, $name_c) = sql_fetch_row($re_courses)) {
			$courses_subscribed[$id_c] = $name_c;
		}
		return $courses_subscribed;
	}

	function &getModulesName($id_course) {

		$mods_names = array();
		$query_menu = "
		SELECT mo.module_name, mo.default_op, mo.default_name, mo.token_associated, under.my_name
		FROM ".$GLOBALS['prefix_lms']."_module AS mo JOIN
			".$GLOBALS['prefix_lms']."_menucourse_under AS under
		WHERE mo.idModule = under.idModule AND under.idCourse = '".$id_course."'";
		$re_menu_voice = sql_query($query_menu);
		while(list($module_name, $default_op, $default_name, $token, $my_name) = sql_fetch_row($re_menu_voice)) {

			$mods_names[$module_name] = ( ($my_name != '' ) ? $my_name : Lang::t($default_name, 'menu_course', false, false, $default_name) );
		}
		$mods_names['_LOGOUT'] = Lang::t('_LOGOUT', 'standard');
		$mods_names['_ELECOURSE'] = Lang::t('_COURSE_LIST', 'menu_course');
		return $mods_names;
	}

	function addMainToCourse($id_course, $name) {

		$id_main = false;
		if(!sql_query("
		INSERT INTO ".$GLOBALS['prefix_lms']."_menucourse_main ( idCourse, sequence, name, image )
			VALUES ( '".$id_course."','0', '".$name."', '')")) return false;
		list($id_main) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		return $id_main;
	}

	/**
	 * this function detect modules useing id_module, module_name and
	 * default_op and add the module to the specified course, alsoassign
	 * the specified permission to the level_idst
	 * @param int 		$id_course 				the id of the course
	 * @param array		$level_idst 			the list of the idst assigned to each level
	 * @param int 		$id_amin 				the id of the main menu
	 * @param string 	$m_name 				the module name
	 * @param string 	$d_op 					the default module op
	 * @param array 	$level_token_to_assign 	for each level the token to assign array(level => array(token, token))
	 *
	 * @return bool true if success, false otherwise
	 */
	function addModuleToCourse($id_course, $level_idst, $id_main, $id_m = false, $m_name = false, $d_op = false, $level_token_to_assign = false) {

		require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.istance.php');

		$acl_man =& Docebo::user()->getAclManager();

		$re = true;
		$query_menu = "
		SELECT idModule, module_name, default_op, file_name, class_name
		FROM ".$GLOBALS['prefix_lms']."_module
		WHERE 1";
		if($id_m !== false) 	$query_menu .= " AND idModule = '".$id_m."' ";
		if($m_name !== false) 	$query_menu .= " AND module_name = '".$m_name."' ";
		if($d_op !== false) 	$query_menu .= " AND default_op = '".$d_op."' ";

		$re_query = sql_query($query_menu);
		if(!$re_query || (sql_num_rows($re_query) == 0)) return false;

		$i = 0;
		while(list($id_module, $module_name, $module_op, $file_name, $class_name) = sql_fetch_row($re_query)) {

			$module_obj 	=& createLmsModule($module_name);
			$tokens 		= $module_obj->getAllToken($module_op);
			$module_role 	=& createModuleRoleForCourse($id_course, $module_name, $tokens);

			foreach($level_token_to_assign as $level => $token_list) {

				foreach($token_list as $token) {

					$re &= $acl_man->addToRole( $module_role[$token], $level_idst[$level] );

				} // end foreach

			} // end foreach

			$re &= sql_query("INSERT INTO ".$GLOBALS['prefix_lms']."_menucourse_under ( idCourse, idModule, idMain, sequence, my_name )
			VALUES ('".$id_course."', '".$id_module."', '".$id_main."', '".$i++."', '')");

		} // end while
		return $re;
	}

	function removeCourseRole($id_course) {

		$acl_man =& Docebo::user()->getAclManager();
		$base_path = '/lms/course/private/'.$id_course.'/';
		$acl_man->deleteRoleFromPath($base_path);
	}

	function removeCourseMenu($id_course) {
                
		$query_del = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_menucourse_main
		WHERE idCourse = '".$id_course."'";

		$query_del_voice = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_menucourse_under
		WHERE idCourse = '".$id_course."'";

		if(!sql_query($query_del)) return false;
		if(!sql_query($query_del_voice)) return false;
	}

	/**
	 * this function need the user course stat and calculate if the user can enter into the course or not
	 * return the access status and the reason for a blocked access
	 *
	 * @return array 	on key 'can'' => true or false
	 * 					on key 'reason' => it's possible to find this values : 'prerequisites', 'waiting',
	 * 						'course_date', 'course_valid_time', 'user_status', 'course_status'
	 * 					on key 'expiring_in' => report the day remaining before the course expire
	 *
	 */
	function canEnterCourse(&$course, $id_path = 0) {

		$db = DbConn::getInstance();

		// control if the user is in a status that cannot enter
		$now = time();
		$expiring = false;

        // if course has editions, evaluate these first of all
        if ($course['course_edition']=="1"){
            // retrieve editions 
            $select_edition  = " SELECT e.date_begin, e.date_end ";
            $select_edition .= " FROM ".$GLOBALS["prefix_lms"]."_course_editions AS e "
                ." JOIN ".$GLOBALS["prefix_lms"]."_course_editions_user AS u ";
            $select_edition .= " WHERE e.status IN ('".CST_AVAILABLE."','".CST_EFFECTIVE."') AND e.id_course = '".$course['idCourse']."' AND e.id_edition = u.id_edition AND u.id_user = '".getLogUserId()."'";
            $re_edition = sql_query($select_edition);
            $canEnd   = false;
            $canStart = false;
            // evaluate date_begin and date_end only for active editions
            // if no editions is active returns subscription_expired
            while ($edition_elem = sql_fetch_assoc($re_edition)) {
				$datetime1 = new DateTime('now');
				$datetime2 = new DateTime($edition_elem['date_end']);
            	$interval = $datetime1->getTimestamp() - $datetime2->getTimestamp();

                if (is_null($edition_elem['date_end']) || $edition_elem['date_end'] == '0000-00-00 00:00:00' || $interval <= 0) {
                    $canEnd = $canEnd || true;
                }
                if ($canEnd && (is_null($edition_elem['date_begin']) || $edition_elem['date_begin'] == '0000-00-00' || strcmp(date('Y-m-d'), $edition_elem['date_begin']) >= 0)) {
                    $canStart = $canStart || true;
                }                    
            }
            if (!$canEnd){
                return array('can' => false, 'reason' => 'course_edition_date_end');
            }            
            if (!$canStart){
                return array('can' => false, 'reason' => 'course_edition_date_begin', 'expiring_in' => 1);
            }                
        }
        
		if($course['date_end'] != '0000-00-00') {
			$date01=new DateTime($course['date_end']);
			$time_end=$date01->format('U');
			if(isset($course['hour_end']) && $course['hour_end']!=-1){
				$hour_end = $course['hour_end'];
				$seconds = strtotime("1970-01-01 $hour_end UTC");
				$time_end += seconds;
			}
			$exp_time = $time_end - $now;
			if($exp_time > 0) $expiring = round($exp_time / (24*60*60));
		}
		if($course['valid_time'] != '0' && $course['valid_time'] != '' && $course['date_first_access'] != '' && $course['level']<=3) {

			$time_first_access = fromDatetimeToTimestamp($course['date_first_access']);

			$exp_time = ( $time_first_access + ($course['valid_time'] * 24 * 3600 ) ) - $now;
            $expiring = round($exp_time / (24*60*60));
            $expiring = $expiring ==-0?0:$expiring;			
			if($exp_time < 0) {
                return array('can' => false, 'reason' => 'time_elapsed', 'expiring_in' => $expiring);
            }
		}
		
		$query =	"SELECT date_begin_validity, date_expire_validity, status, level"
					." FROM ".$GLOBALS['prefix_lms']."_courseuser"
					." WHERE idCourse = '".$course['idCourse']."'"
					." AND idUser = '".getLogUserId()."'";

		list($date_begin_validity, $date_expire_validity, $status, $level) = $db->fetch_row(sql_query($query));

		if(!is_null($date_begin_validity) && $date_begin_validity !== '0000-00-00 00:00:00' && strcmp(date('Y-m-d H:i:s'), $date_begin_validity) <= 0)
			return array('can' => false, 'reason' => 'subscription_not_started', 'expiring_in' => $expiring);

		if(!is_null($date_expire_validity) && $date_expire_validity !== '0000-00-00 00:00:00' && strcmp(date('Y-m-d H:i:s'), $date_expire_validity) >= 0)
			return array('can' => false, 'reason' => 'subscription_expired', 'expiring_in' => $expiring);

		if($course['course_status'] == CST_CANCELLED)
			return array('can' => false, 'reason' => 'course_status', 'expiring_in' => $expiring);

		if($course['course_status'] == CST_PREPARATION)
		{
			if($level > 3)
				return array('can' => true, 'reason' => 'user_status', 'expiring_in' => $expiring);
			else
				return array('can' => false, 'reason' => 'course_status', 'expiring_in' => $expiring);
		}

		if($course['course_status'] == CST_CONCLUDED)
		{
			if($status == _CUS_END || $level > 3)
				return array('can' => true, 'reason' => 'user_status', 'expiring_in' => $expiring);
			else
				return array('can' => false, 'reason' => 'course_status', 'expiring_in' => $expiring);
		}

		//if($course['user_status'] == _CUS_CANCELLED) return array('can' => false, 'reason' => 'user_status');
		if($course['level'] > 3) return array('can' => true, 'reason' => '', 'expiring_in' => $expiring);
		if(isset($course['prerequisites_satisfied']) && $course['prerequisites_satisfied'] == false) return array('can' => false, 'reason' => 'prerequisites', 'expiring_in' => $expiring);
		if(isset($course['waiting']) && $course['waiting'] >= 1) return array('can' => false, 'reason' => 'waiting', 'expiring_in' => $expiring);
		// control if the course is elapsed
		if($course['date_begin'] != '0000-00-00') {

			$time_begin = fromDatetimeToTimestamp($course['date_begin']);

			if($now < $time_begin) return array('can' => false, 'reason' => 'course_date', 'expiring_in' => $expiring);
		}
		if($course['date_end'] != '0000-00-00') {
			$date01=new DateTime($course['date_end']);
			$time_end=$date01->format('U');
			if(isset($course['hour_end']) && $course['hour_end']!=-1){
				$hour_end = $course['hour_end'];
				$seconds = strtotime("1970-01-01 $hour_end UTC");
				$time_end = (int)$time_end + (int)$seconds;
			}
			if($now > $time_end) return array('can' => false, 'reason' => 'course_date', 'expiring_in' => $expiring);
		}
		if($course['valid_time'] != '0' && $course['valid_time'] != '' && $course['date_first_access'] != '') {

			$time_first_access = fromDatetimeToTimestamp($course['date_first_access']);

			if($now > ( $time_first_access + ($course['valid_time'] * 24 * 3600 ) )) return array('can' => true, 'reason' => 'course_valid_time', 'expiring_in' => $expiring);
		}
		if( $course['userStatusOp'] & (1 << $course['user_status']) ) return array('can' => false, 'reason' => 'user_status', 'expiring_in' => $expiring);

		//Control user coursepath prerequisite
		$query =	"SELECT cc.prerequisites "
					." FROM %lms_coursepath_courses AS cc "
					." JOIN %lms_coursepath_user AS cu ON cc.id_path = cu.id_path "
					." WHERE cu.idUser = ".(int)Docebo::user()->getIdSt()." "
					." AND cc.id_item = ".(int)$course['idCourse']." "
					.($id_path != 0 ? " AND cc.id_path = ".(int)$id_path : '');

		$result = sql_query($query);

		$control_cicle = 0;

		while(list($prerequisites) = sql_fetch_row($result))
			if($prerequisites !== '')
			{
				$num_prerequisites = count(explode(',', $prerequisites));
				$control_cicle++;

				$query =	"SELECT COUNT(*) "
							." FROM %lms_courseuser "
							." WHERE idCourse IN (".$prerequisites.") "
							." AND idUser = ".(int)Docebo::user()->getIdSt()." "
							." AND status = "._CUS_END;

				list($control) = sql_fetch_row(sql_query($query));

				if($control >= $num_prerequisites)
					return array('can' => true, 'reason' => 'prerequisites', 'expiring_in' => $expiring);
			}

		if($control_cicle > 0)
			return array('can' => false, 'reason' => 'prerequisites', 'expiring_in' => $expiring);

		// user is not a tutor or a prof and the course isn't active
		if($course['course_status'] != 1 && $course['level'] < 4) return array('can' => true, 'reason' => 'course_status', 'expiring_in' => $expiring);
		return array('can' => true, 'reason' => '', 'expiring_in' => $expiring);
	}

	function getNumberOfCoursesForCategories($show_rules  = 0) {

		$courses = array();
		$query_course = "
		SELECT idCategory, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE show_rules  = '".$show_rules ."'
		GROUP BY idCategory";

		$re_course = sql_query($query_course);
		while(list($id_cat, $number_of_course) = sql_fetch_row($re_course)) {

			$courses[$id_cat] = $number_of_course;
		}
		return $courses;
	}

	function getCategoryCourseAndSonCount($id_parent = false) {

		$count = array();
		$query_cat = "
		SELECT idCategory, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE show_rules = '0' ".( !Docebo::user()->isAnonymous() ? " OR show_rules = '1' " : "" )."
		GROUP BY idCategory";
		$re_category = sql_query($query_cat);
		while(list($id, $num) = sql_fetch_array($re_category)) {

			$count[$id]['course'] = $num;
		}

		$query_cat = "
		SELECT idCategory, idParent
		FROM ".$GLOBALS['prefix_lms']."_category ";
		if($id_parent !== false) $query_cat .= " WHERE idParent = '".$id_parent."' ";
		$query_cat .= " ORDER BY path DESC";

		$re_category = sql_query($query_cat);
		while(list($id_cat, $id_parent) = sql_fetch_array($re_category)) {

			$categories[$id_cat]['category'] = 0;
			if(isset($count[$id_parent]['category'])) $count[$id_parent]['category'] += 1;
			else $count[$id_parent]['category'] = 1;
			if(isset($categories[$id_cat])) $count[$id_parent]['category'] += $categories[$id_cat]['category'];
		}
		return $count;
	}

	function getCategory($id_cat) {

		$categories = array();
		$query_cat = "
		SELECT idCategory, idParent, lev, path, description
		FROM ".$GLOBALS['prefix_lms']."_category
		WHERE idCategory = '".$id_cat."'";

		$re_category = sql_query($query_cat);
		return sql_fetch_array($re_category);
	}

	function &getCategoriesInfo($id_parent = false, $also_itself = false, $entire_path = false) {

		$categories = array();
		$query_cat = "
		SELECT idCategory, idParent, lev, path, description
		FROM ".$GLOBALS['prefix_lms']."_category  ";
		if($id_parent !== false) {
			$query_cat .= " WHERE idParent = '".$id_parent."'";
			if($also_itself !== false) $query_cat .= " OR idCategory = '".$id_parent."' ";
		}
		$query_cat .= " ORDER BY description";

		$re_category = sql_query($query_cat);
		while($cat = sql_fetch_array($re_category)) {

			if($entire_path === false) {

				$categories[$cat['idCategory']] = $cat;
				$categories[$cat['idCategory']]['name'] = (
					($pos = strrpos($cat['path'], '/')) === FALSE
						? $cat['path']
						: substr($cat['path'], $pos+1)
				);
			} else {

				$categories[$cat['idCategory']] = substr($cat['path'], strlen('/root/'));
			}
		}
		return $categories;
	}

	function _recurseCategory($id_cat, $title_link, $link, $parent_name) {

		if(!$id_cat) return '';
		$query_cat = "
		SELECT idParent, lev, path
		FROM ".$GLOBALS['prefix_lms']."_category
		WHERE idCategory = '".$id_cat."'";
		if(!$re_category = sql_query($query_cat)) return '';
		list($id_parent, $lev, $path) = sql_fetch_row($re_category);

		$name = ( ($pos = strrpos($path, '/')) === FALSE ? $path : substr($path, $pos+1) );
		if($lev <= 1) {

			return ' &gt; '.( $link !== false
					? '<a title="'.$title_link.' : '.$name.'" href="'.$link.'&amp;'.$parent_name.'='.$id_cat.'">'.$name.'</a>'
					: $name );
		} else {

			return $this->_recurseCategory($id_parent, $title_link, $link, $parent_name)
				.' &gt; '.( $link !== false
					? '<a title="'.$title_link.' : '.$name.'" href="'.$link.'&amp;'.$parent_name.'='.$id_cat.'">'.$name.'</a>'
					: $name );
		}
	}

	function getCategoryPath($id_cat, $lang_main, $title_link, $link, $parent_name) {

		$categories = array();
		return ( $link !== false
					? '<a title="'.$title_link.' : '.$lang_main.'"  href="'.$link.'">'.$lang_main.'</a>'
					: $lang_main )
			.$this->_recurseCategory($id_cat, $title_link, $link, $parent_name);
	}


	//----------------------------------------------------------------------------

	function getCoursesRequest($startIndex = false, $records = false, $sort = false, $dir = false, $filter = false) {
		if (!$startIndex) $startIndex = 0;
		if (!$records) $records = Get::sett('visuItem');

		$filter_conds = '';
		if ($filter) {

			if (isset($filter['c_category'])) {
				$flat = false;
				if (isset($filter['c_flatview'])) $flat = ($filter['c_flatview']['value']=='true'? true : false);
				$categories = array((int)$filter['c_category']['value']);
				if ($flat) {
					//retrieve category's sub-categories ids
					if($filter['c_category']['value'] != 0)
					{
						$bounds_query = "SELECT iLeft, iRight FROM ".$GLOBALS['prefix_lms']."_category WHERE idCategory=".$filter['c_category']['value'];
						$res = sql_query( $bounds_query );
						list( $c_left, $c_right ) = sql_fetch_row( $res );
						$categories_query = "SELECT idCategory FROM ".$GLOBALS['prefix_lms']."_category WHERE iLeft>".$c_left." AND iRight<".$c_right;
						$res = sql_query( $categories_query );
						while ( list($sub_category) = sql_fetch_row($res) ) $categories[] = (int)$sub_category;
					}
					else
					{
						$categories_query = "SELECT idCategory FROM ".$GLOBALS['prefix_lms']."_category WHERE 1";
						$res = sql_query( $categories_query );
						while ( list($sub_category) = sql_fetch_row($res) ) $categories[] = (int)$sub_category;
					}
				}
				$filter_conds .= " AND c.idCategory IN (".implode(",", $categories).") ";
			}

			if (isset($filter['c_filter'])) {
				$filter_conds .= " AND ( "
						." c.code LIKE '%".$filter['c_filter']['value']."%' OR "
						." c.name LIKE '%".$filter['c_filter']['value']."%' OR "
						." c.description LIKE '%".$filter."%' ) ";
			}

			if (isset($filter['c_expire'])) {
				if($filter['c_expire']['value'] != '') {
						$temp = '';
						switch($filter['c_expire']['value']) {
								case 1 : $temp .= " AND c.date_end = '0000-00-00' "; break;
								case 2 : $temp .= " AND c.date_end >= '".date("Y-m-d")."' "; break;
								case 3 : $temp .= " AND c.date_end <= '".date("Y-m-d")."' AND c.date_end <> '0000-00-00' "; break;
						}
						$filter_conds .= $temp;
				}
			}

			if(isset($filter['c_waiting']))
			{
				if($filter['c_waiting']['value'] == '1')
				{
					$filter_conds .=	" AND c.idCourse IN"
										." ("
										." SELECT idCourse"
										." FROM ".$GLOBALS['prefix_lms']."_courseuser"
										." WHERE waiting = 1"
										." )";
				}
			}

		}

		$userlevelid = Docebo::user()->getUserLevelId();
		$is_subadmin = false;
		$all_courses = false;
		if( $userlevelid != ADMIN_GROUP_GODADMIN )
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$acl_man =& Docebo::user()->getAclManager();

			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
			$all_courses = false;
			if(isset($admin_courses['course'][0]))
				$all_courses = true;
			elseif(isset($admin_courses['course'][-1]))
			{
				require_once(_lms_.'/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if(count($user_catalogue) > 0)
				{
					$courses = array(0);

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach($courses as $id_course)
						if($id_course != 0)
							$admin_courses['course'][$id_course] = $id_course;
				}
				elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			}
			else
			{
				$array_courses = array();
				$array_courses = array_merge($array_courses, $admin_courses['course']);

				if(!empty($admin_courses['coursepath']))
				{
					require_once(_lms_.'/lib/lib.coursepath.php');
					$path_man = new Catalogue_Manager();
					$coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if(!empty($admin_courses['catalogue']))
				{
					require_once(_lms_.'/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach($admin_courses['catalogue'] as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
			}

			$is_subadmin = true;
		}

		$query = "SELECT c.*, COUNT(cu.idUser) as subscriptions, SUM(cu.waiting) as pending "
				." FROM ".$GLOBALS['prefix_lms']."_course as c "
				." LEFT JOIN ".$GLOBALS['prefix_lms']."_courseuser as cu ON (c.idCourse=cu.idCourse) "
				." WHERE c.course_type = 'elearning' ".$filter_conds
				.($is_subadmin && !$all_courses ? (!empty($admin_courses['course']) ? ' AND c.idCourse IN ('.implode(',', $admin_courses['course']).')' : ' AND c.idCourse = 0') : '')
				.($is_subadmin ? " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'cu.idUser') : '')
				." GROUP BY c.idCourse "
				.($sort ? " ORDER BY ".$sort." ".$dir." " : "")
				." LIMIT ".$startIndex.", ".$records;

		return sql_query($query);
	}


	function getCoursesCountFiltered($filter = false, $only_visible = false) {

		$filter_conds = '';
		if ($filter) {

			if (isset($filter['c_category'])) {
				$flat = false;
				if (isset($filter['c_flatview'])) $flat = ($filter['c_flatview']['value']=='true' ? true : false);

				$categories = array((int)$filter['c_category']['value']);
				if ($flat && $filter['c_category']['value'] != 0) {
					//retrieve category's sub-categories ids
					$bounds_query = "SELECT iLeft, iRight FROM ".$GLOBALS['prefix_lms']."_category WHERE idCategory=".$filter['c_category']['value'];
					$res = sql_query( $bounds_query );
					list( $c_left, $c_right ) = sql_fetch_row( $res );
					$categories_query = "SELECT idCategory FROM ".$GLOBALS['prefix_lms']."_category WHERE iLeft>".$c_left." AND iRight<".$c_right;
					$res = sql_query( $categories_query );
					while ( list($sub_category) = sql_fetch_row($res) ) $categories[] = (int)$sub_category;
				}
				if(!($flat && $filter['c_category']['value'] == 0))	$filter_conds .= " AND c.idCategory IN (".implode(",", $categories).") ";

			}

			if (isset($filter['c_filter'])) {
				$filter_conds .= " AND ( "
						." c.code LIKE '%".$filter['c_filter']['value']."%' OR "
						." c.name LIKE '%".$filter['c_filter']['value']."%' OR "
						." c.description LIKE '%".$filter."%' ) ";
			}

			if (isset($filter['c_expire'])) {
				if($filter['c_expire']['value'] != '') {
						$temp = '';
						switch($filter['c_expire']['value']) {
								case 1 : $temp .= " AND c.date_end = '0000-00-00' "; break;
								case 2 : $temp .= " AND UNIX_TIMESTAMP(c.date_end) >= '".time()."' "; break;
								case 3 : $temp .= " AND UNIX_TIMESTAMP(c.date_end) <= '".time()."' AND c.date_end <> '0000-00-00' "; break;
						}
						$filter_conds .= $temp;
				}
			}

		}

		$userlevelid = Docebo::user()->getUserLevelId();
		$is_subadmin = false;
		$all_courses = false;
		if( $userlevelid != ADMIN_GROUP_GODADMIN )
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
			$all_courses = false;
			if(isset($admin_courses['course'][0]))
				$all_courses = true;
			elseif(isset($admin_courses['course'][-1]))
			{
				require_once(_lms_.'/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if(count($user_catalogue) > 0)
				{
					$courses = array(0);

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach($courses as $id_course)
						if($id_course != 0)
							$admin_courses['course'][$id_course] = $id_course;
				}
				elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			}
			else
			{
				$array_courses = array();
				$array_courses = array_merge($array_courses, $admin_courses['course']);

				if(!empty($admin_courses['coursepath']))
				{
					require_once(_lms_.'/lib/lib.coursepath.php');
					$path_man = new Catalogue_Manager();
					$coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if(!empty($admin_courses['catalogue']))
				{
					require_once(_lms_.'/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach($admin_courses['catalogue'] as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
			}

			$is_subadmin = true;
		}

		$query = "SELECT COUNT(*) FROM ".$GLOBALS['prefix_lms']."_course AS c "
				." WHERE course_type = 'elearning' ".$filter_conds
				.($is_subadmin && !$all_courses ? (!empty($admin_courses['course']) ? ' AND c.idCourse IN ('.implode(',', $admin_courses['course']).')' : '') : '');
		$re_course = sql_query($query);
		list($number) = sql_fetch_row($re_course);
		return $number;

	}

	public function getClassroomsNumber($categories = false, $filter_text = false, $filter_waiting = false)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$GLOBALS['prefix_lms']."_course"
					." WHERE course_type = 'classroom' ";

		if ($categories) {
			if (!is_array($categories))
				$query .= " AND idCategory = ".(int)$categories." ";
			else
				$query .= " AND idCategory IN (".implode(',', $categories).") ";
		}

		if ($filter_text) {
			if (is_string($filter_text))
				$query .= " AND (code LIKE '%".$filter_text."%' "
					." OR name LIKE '%".$filter_text."%' "
					." OR description LIKE '%".$filter_text."%') ";
		}

		if($filter_waiting)
			$query .=	" AND c.idCourse IN"
						." ("
						." SELECT idCOurse"
						." FROM %lms_courseuser"
						." WHERE waiting = 1"
						." )";

		$userlevelid = Docebo::user()->getUserLevelId();
		$is_subadmin = false;
		$all_courses = false;
		if( $userlevelid != ADMIN_GROUP_GODADMIN )
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
			$all_courses = false;
			if(isset($admin_courses['course'][0]))
				$all_courses = true;
			elseif(isset($admin_courses['course'][-1]))
			{
				require_once(_lms_.'/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if(count($user_catalogue) > 0)
				{
					$courses = array(0);

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach($courses as $id_course)
						if($id_course != 0)
							$admin_courses['course'][$id_course] = $id_course;
				}
				elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			}
			else
			{
				$array_courses = array();
				$array_courses = array_merge($array_courses, $admin_courses['course']);

				if(!empty($admin_courses['coursepath']))
				{
					require_once(_lms_.'/lib/lib.coursepath.php');
					$path_man = new Catalogue_Manager();
					$coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if(!empty($admin_courses['catalogue']))
				{
					require_once(_lms_.'/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach($admin_courses['catalogue'] as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
			}

			$is_subadmin = true;
		}

		$query .= ($is_subadmin && !$all_courses ? (!empty($admin_courses['course']) ? ' AND c.idCourse IN ('.implode(',', $admin_courses['course']).')' : ' AND c.idCourse = 0') : '');

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getClassrooms($start_index = false, $results = false, $sort = false, $dir = false, $categories = false, $filter_text = false, $filter_waiting = false)
	{
		require_once(_lms_.'/lib/lib.date.php');

		$date_man = new DateManager();

		$status_list = array(	CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
								CST_AVAILABLE 	=> Lang::t('_CST_AVAILABLE', 'course'),
								CST_EFFECTIVE 	=> Lang::t('_CST_CONFIRMED', 'course'),
								CST_CONCLUDED 	=> Lang::t('_CST_CONCLUDED', 'course'),
								CST_CANCELLED 	=> Lang::t('_CST_CANCELLED', 'course'));

		$query =	"SELECT c.idCourse, c.code, c.name, c.status, COUNT(cd.id_date) as classroom_number "
					." FROM %lms_course as c LEFT JOIN %lms_course_date as cd ON c.idCourse=cd.id_course "
					." WHERE c.course_type = 'classroom' ";

		//if ($categories) {
			if (!is_array($categories))
				$query .= " AND c.idCategory = ".(int)$categories." ";
			else
				$query .= " AND c.idCategory IN (".implode(',', $categories).") ";
		//}

		if ($filter_text) {
			if (is_string($filter_text))
				$query .= " AND (c.code LIKE '%".$filter_text."%' "
					." OR c.name LIKE '%".$filter_text."%' "
					." OR c.description LIKE '%".$filter_text."%') ";
		}

		if($filter_waiting)
			$query .=	" AND c.idCourse IN"
						." ("
						." SELECT idCOurse"
						." FROM %lms_courseuser"
						." WHERE waiting = 1"
						." )";

		$userlevelid = Docebo::user()->getUserLevelId();
		$is_subadmin = false;
		$all_courses = false;
		if( $userlevelid != ADMIN_GROUP_GODADMIN )
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
			$all_courses = false;
			if(isset($admin_courses['course'][0]))
				$all_courses = true;
			elseif(isset($admin_courses['course'][-1]))
			{
				require_once(_lms_.'/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if(count($user_catalogue) > 0)
				{
					$courses = array(0);

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach($courses as $id_course)
						if($id_course != 0)
							$admin_courses['course'][$id_course] = $id_course;
				}
				elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			}
			else
			{
				$array_courses = array();
				$array_courses = array_merge($array_courses, $admin_courses['course']);

				if(!empty($admin_courses['coursepath']))
				{
					require_once(_lms_.'/lib/lib.coursepath.php');
					$path_man = new Catalogue_Manager();
					$coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if(!empty($admin_courses['catalogue']))
				{
					require_once(_lms_.'/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach($admin_courses['catalogue'] as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
			}

			$is_subadmin = true;
		}

		$query .= ($is_subadmin && !$all_courses ? (!empty($admin_courses['course']) ? ' AND c.idCourse IN ('.implode(',', $admin_courses['course']).')' : ' AND c.idCourse = 0') : '');

		$query .= " GROUP BY c.idCourse ";

		switch($sort)
		{
			case 'code':
				$query .= " ORDER BY c.code ".$dir;
			break;

			case 'name':
				$query .= " ORDER BY c.name ".$dir;
			break;
		}

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);
		$res = array();

		while(list($id_course, $code, $name, $status, $classroom_number) = sql_fetch_row($result))
		{
			//$classroom_number = $date_man->getCourseDateNumber($id_course);

			$res[] = array(
							'id_course' => $id_course,
							'code' => $code,
							'name' => $name,
							'status' => $status_list[$status],
							'classroom_number' => $classroom_number
						);
		}

		return $res;
	}

	//----------------

	function getCourseIdByName($name) {
		$output = false;
		$query = "SELECT idCourse FROM %lms_course WHERE name LIKE '".$name."'";
		$res = sql_query($query);
		if ($res && sql_num_rows($res)>0) {
			list($id_course) = sql_fetch_row($res);
			$output = $id_course;
		}
		return $output;
	}

}


/**
 * This class purpose is to retrive information about users in relation with courses
 */
class Man_CourseUser {

	/**
	 * @access private
	 * @var resource_id 	the db_connection
	 */
	var $_db_conn;

	/**
	 * @access private
	 * @var string 	the name of the course table
	 */
	var $_table_course;

	/**
	 * @access private
	 * @var string 	the name of the table that contains users course subscription
	 */
	var $_table_user_subscription;

	/**
	 * class constructor
	 * @access public
	 * @param resource_id	$db_conn	the resource id for database connection
	 */
	function Man_CourseUser($db_conn = NULL) {

		$this->_db_conn 					= $db_conn;
		$this->_table_course 				= $GLOBALS['prefix_lms'].'_course';
		$this->_table_user_subscription 	= $GLOBALS['prefix_lms'].'_courseuser';

	}

	/**
	 * return the current name of the course table
	 * @access public
	 * @return string	the name of the course table
	 */
	function getTableCourse() {

		return $this->_table_course;
	}

	/**
	 * set the name of the course table
	 * @access public
	 * @param string	$table_course the name of the course table
	 */
	function setTableCourse($table_course) {

		$this->_table_course = $table_course;
	}

	/**
	 * return the current name of the table that associate users with course
	 * @access public
	 * @return string	the name of the course table
	 */
	function getTableUserSubscription() {

		return $this->_table_user_subscription;
	}

	/**
	 * set the name of the course table
	 * @access public
	 * @param string	$table_course the name of the course table
	 */
	function setTableUserSubscription($table_user_subscription) {

		$this->_table_user_subscription = $table_user_subscription;
	}

	/**
	 * execute a query
	 * @access private
	 * @param string	$query_text	the query that you want to exe
	 *
	 * @return resource_id 	the id of the mysql resource
	 */
	function _query($query_text) {

		if($this->_db_conn)
			$re = sql_query($query_text, $this->_db_conn);
		else
			$re = sql_query($query_text);
		return $re;
	}

	/**
	 * Return the complete course list in which a user is subscribe, you can filter the result with
	 * course status or user status in the course
	 *
	 * @access public
	 *
	 * @param int 	$id_user 		the idst of the user
	 * @param int 	$id_category 	filter for course category
	 * @param int 	$course_status 	filter for course statsus the result
	 * @param int 	$user_status 	filter for the user status in the course
	 *
	 * @return array the list of the course with the carachteristic of it array( id_course => array(
	 * 					idCourse, code, name, description, date_begin, date_end, valid_time, course_status,
	 * 					waiting, userStatusOp, level, user_status, date_inscr, date_first_access,date_complete), ...)
	 */
	 function &getUserCourses($id_user, $id_category = false, $course_status = false, $user_status = false) {

		$courses = array();
		$query_courses = "
		SELECT c.idCourse, c.idCategory, c.code, c.name, c.description,
			c.date_begin, c.date_end, c.valid_time, c.status AS course_status, u.waiting,
			c.userStatusOp, u.level, u.status as user_status, u.date_inscr, u.date_first_access, u.date_complete
		FROM ".$GLOBALS['prefix_lms']."_course AS c JOIN
			".$GLOBALS['prefix_lms']."_courseuser AS u
		WHERE c.idCourse = u.idCourse AND u.idUser = '".$id_user."'";

		if($id_category !== false) 		$query_courses .= " AND c.idCategory = '".$id_category."' ";
		if($course_status !== false) 	$query_courses .= " AND c.status = '".$course_status."' ";
		if($user_status !== false) 		$query_courses .= " AND u.status = '".$user_status."' ";

		$query_courses .= "ORDER BY c.name";

		$re_course = sql_query($query_courses);
		while($course = sql_fetch_assoc($re_course)) {

			$courses[$course['idCourse']] = $course;
		}
		return $courses;
	}

	/**
	 * Return the complete id list in which a user is subscribe, you can filter the result with
	 * course status or user status in the course
	 *
	 * @access public
	 *
	 * @param int 	$id_user 		the idst of the user
	 * @param int 	$id_category 	filter for course category
	 * @param int 	$course_status 	filter for course statsus the result
	 * @param int 	$user_status 	filter for the user status in the course
	 *
	 * @return array the list of the course with the carachteristic of it array( id_course => array(
	 * 					idCourse, code, name, description, date_begin, date_end, valid_time, course_status,
	 * 					waiting, userStatusOp, level, user_status, date_inscr, date_first_access,date_complete), ...)
	 */
	 function getUserCourseList($id_user, $id_category = false, $course_status = false, $user_status = false) {

		$courses = array();
		$query_courses = "
		SELECT c.idCourse
		FROM ".$GLOBALS['prefix_lms']."_course AS c JOIN
			".$GLOBALS['prefix_lms']."_courseuser AS u
		WHERE c.idCourse = u.idCourse AND u.idUser = '".$id_user."'";

		if($id_category !== false) 		$query_courses .= " AND c.idCategory = '".$id_category."' ";
		if($course_status !== false) 	$query_courses .= " AND c.status = '".$course_status."' ";
		if($user_status !== false) 		$query_courses .= " AND u.status = '".$user_status."' ";

		$query_courses .= "ORDER BY c.name";

		$re_course = sql_query($query_courses);
		while($course = sql_fetch_assoc($re_course)) {

			$courses[$course['idCourse']] = $course['idCourse'];
		}
		return $courses;
	}

	/**
	 * Return the complete course list in which a user is subscribe with the level requested
	 *
	 * @access public
	 *
	 * @param int 	$id_user 		the idst of the user
	 * @param int 	$id_category 	filter for course category
	 *
	 * @return array the list of the course with the carachteristic of it array( id_course => array(
	 * 					idCourse, code, name, description
	 */
	 function &getUserCoursesLevelFilter($id_user, $level_number, $not_assessment = false) {

		$courses = array();
		$query_courses = "
		SELECT c.idCourse, c.code, c.name, c.description
		FROM ".$GLOBALS['prefix_lms']."_course AS c JOIN
			".$GLOBALS['prefix_lms']."_courseuser AS u
		WHERE c.idCourse = u.idCourse
			AND u.idUser = '".$id_user."'
			AND u.level = '".$level_number."'";
		if($not_assessment === true) $query_courses .= " AND c.course_type <> 'assessment' ";
		$query_courses .= " ORDER BY c.name";

		$re_course = sql_query($query_courses);
		while($course = sql_fetch_assoc($re_course)) {

			$courses[$course['idCourse']] = $course;
		}
		return $courses;
	}

	/**
	 * Return the complete user list that have the requested level
	 *
	 * @access public
	 *
	 * @param int 	$id_user 	the idst of the user
	 * @param int 	$level 		the level number
	 *
	 * @return array the list of the course with the carachteristic of it array( id_course => array(
	 * 					idCourse, code, name, description
	 */
	 function getUserWithLevelFilter($level, $arr_user = false) {

		$users = array();
		$query_courses = "
		SELECT DISTINCT idUser
		FROM ".$GLOBALS['prefix_lms']."_courseuser AS u
		WHERE ";
		if(is_array($level)) $query_courses .= " level IN ( ".implode(',', $level)." ) ";
		else $query_courses .= " level = '".$level."'";
		if($arr_user != false) $query_courses .= " AND idUser IN ( ".implode(',', $arr_user)." )";

		$re_course = sql_query($query_courses);

		while(list($id) = sql_fetch_row($re_course)) {

			$users[$id] = $id;
		}
		return $users;
	}

	function getUserSubscriptionsInfo($id_user, $exclude_waiting = false) {

		$courses 	= array();

		$query_courseuser = "
		SELECT idCourse, level, waiting, status
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".$id_user."'";
		if($exclude_waiting) $query_courseuser .= " AND waiting = 0";
		$re_courseuser = sql_query($query_courseuser);
		while(list($id_course, $lv, $is_waiting, $status) = sql_fetch_row($re_courseuser)) {

			$courses[$id_course] = array( 	'idUser' => $id_user,
											'level' => $lv,
											'waiting' => $is_waiting,
											'status' => $status );
		}
		return $courses;
	}

	/**
	 * return the courses that the user have score
	 * @param int $id_user the id of the user
	 *
	 * @return array (id_course => score, id_course => score, ...)
	 */
	function getUserCourseScored($id_user) {

		$courses = array();
		$query_courseuser = "
		SELECT idCourse, score_given
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".$id_user."' AND score_given IS NOT NULL ";
		$re_courseuser = sql_query($query_courseuser);
		while(list($id_course, $score_given) = sql_fetch_row($re_courseuser)) {

			$courses[$id_course] = $score_given;
		}
		return $courses;

	}

	function subscribeUserWithCode ($code, $id_user, $level = 3)
	{
		require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');

		$subscriber = new CourseSubscribe_Management();

		$acl_man =& Docebo::user()->getAclManager();

		$query_course = "SELECT idCourse" .
						" FROM ".$GLOBALS['prefix_lms']."_course" .
						" WHERE autoregistration_code = '".$code."'"
						." AND autoregistration_code <> ''";

		$result_course = sql_query($query_course);
        
		$query_course_active = "SELECT idCourse" .
						" FROM ".$GLOBALS['prefix_lms']."_course" .
						" WHERE autoregistration_code = '".$code."'"
						." AND autoregistration_code <> ''"
                        ." AND (                       
                            (can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '".date('Y-m-d')."') AND (sub_start_date = '0000-00-00' OR '".date('Y-m-d')."' >= sub_start_date)) OR
                            (can_subscribe=1)
                         ) ";

		$result_course_active = sql_query($query_course_active);        

		$counter = 0;
		$subs = $this->getUserSubscriptionsInfo($id_user);

		if(!sql_num_rows($result_course)) return 0;
        // return -2 if course subscription is not allowed
        if(!sql_num_rows($result_course_active)) return -2;
		while (list($id_course) = sql_fetch_row($result_course))
		{
			if(!isset($subs[$id_course])) {

				$result = $subscriber->subscribeUser($id_user, $id_course, $level);
				if($result) $counter++;
			}
		}
		if(sql_num_rows($result_course)!= 0 && $counter == 0) return -1;
		return $counter;
	}

	function checkCode ($code) {
		$query_course = "SELECT idCourse" .
						" FROM ".$GLOBALS['prefix_lms']."_course" .
						" WHERE autoregistration_code = '".$code."'";
		$result_course = sql_query($query_course);

		if(!sql_num_rows($result_course)) return 0;
		return sql_num_rows($result_course);
	}

}

class DoceboCourse {

	var $id_course;

	var $course_info;

	function _executeQuery($query_text) {

		$re = sql_query($query_text);
		return $re;
	}

	function _load() {

		$query_load = "
		SELECT *
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE idCourse = '".$this->id_course."'";
		$re_load = $this->_executeQuery($query_load);
		$this->course_info = sql_fetch_assoc($re_load);
	}

	function DoceboCourse($id_course) {

		$this->id_course = $id_course;
		$this->_load();
	}

	function getAllInfo() {

		return $this->course_info;
	}

	function getValue($param) {

		return $this->course_info[$param];
	}

	function setValues($arr_new_values) {

		$re = true;
		if(empty($arr_new_values)) return $re;
		while(list($key, $value) = each($arr_new_values)) {

			$params[] = " ".$key." = '".$value."'";
		}
		$query = "
		UPDATE ".$GLOBALS['prefix_lms']."_course
		SET ".implode(',', $params)
		." WHERE idCourse = '".$this->id_course."'";
		return $this->_executeQuery($query);
	}

	function voteCourse($id_user, $score, $user_score_to_save) {

		$query = "
		UPDATE ".$GLOBALS['prefix_lms']."_courseuser
		SET score_given = ".$user_score_to_save." "
		." WHERE idCourse = '".$this->id_course."' AND idUser = '".$id_user."'";
		if(!$this->_executeQuery($query)) return false;

		$query = "
		UPDATE ".$GLOBALS['prefix_lms']."_course
		SET course_vote  = course_vote  ".( $score > 0 ? '+' : '-' ).abs($score)." "
		." WHERE idCourse = '".$this->id_course."'";
		if(!$this->_executeQuery($query)) return false;

		$this->course_info['course_vote'] = $this->course_info['course_vote'] + $score;
		return $this->course_info['course_vote'];
	}

	/**
	 * Find the idst of all the user subscribed to the course
	 * @param 	int 	$id_course 	the id of the course
	 *
	 * @return 	array	idst
	 */
	function getSubscribed() {

		$acl_man	=& Docebo::user()->getAclManager();
		/*
		$group_info = $acl_man->getGroup(FALSE, '/lms/course/'.$this->id_course.'/subscribed/alluser');
		$idst_group = $group_info[ACL_INFO_IDST];

		$members = $acl_man->getGroupAllUser($idst_group);*/
		$members = array();
		$re_course= sql_query("SELECT idUser FROM ".$GLOBALS['prefix_lms']."_courseuser WHERE idCourse = '".(int)$this->id_course."'");
		while(list($idu) = sql_fetch_row($re_course)) {
			$members[$idu] = $idu;
		}
		return $members;
	}

	function getQuotaLimit() {

		$course_quota = $this->course_info['course_quota'];
		if($course_quota == COURSE_QUOTA_INHERIT) $course_quota = Get::sett('course_quota');
		return $course_quota;
	}

	function getUsedSpace() {

		$course_size = $this->course_info['used_space'];
		return $course_size;
	}

	function addFileToUsedSpace($path = false, $manual_size = false) {

		if($manual_size === false) $size = Get::file_size($path);
		else $size = $manual_size;

		$this->course_info['used_space'] = $this->course_info['used_space'] + $size;

		return $this->setValues(array('used_space' => $this->course_info['used_space']));
	}

	function subFileToUsedSpace($path = false, $manual_size = false) {

		if($manual_size === false) $size = Get::file_size($path);
		else $size = $manual_size;

		$course_size = $this->course_info['used_space'] - $size;
		$this->course_info['used_space'] = ( $course_size < 0 ? 0 : $course_size);

		return $this->setValues(array('used_space' => $course_size));
	}

	/**
	 * Find the idst of the group of a course that represent the level
	 * @param 	int 	$id_course 	the id of the course
	 *
	 * @return 	array	[lv] => idst, [lv] => idst
	 */
	function getCourseLevel($id_course, $also_waiting = false) {
		require_once(_lms_.'/lib/lib.subscribe.php');

		$subscribe_man = new CourseSubscribe_Manager();

		$map 		= array();
		$levels 	= $subscribe_man->getUserLevel();//CourseLevel::getLevels();
		$acl_man	=& Docebo::user()->getAclManager();


		// find all the group created for this menu custom for permission management
		$arr_groupid = array();
		foreach($levels as $lv => $name_level) {
			$arr_groupid[$lv] = '/lms/course/'.$id_course.'/subscribed/'.$lv;
		}

		$arr_idst = Docebo::aclm()->getArrGroupST($arr_groupid);

		$map = array();
		$flip = array_flip($arr_groupid);
		foreach ($arr_idst as $groupid => $idst) {
			$lv = $flip[$groupid];
			$map[$lv] = (int)$idst;
		}

		return $map;
	}


	/**
	 * Create the group of a course that represent the level
	 * @param 	int 	$id_course 	the id of the course
	 *
	 * @return 	array	[lv] => idst, [lv] => idst
	 */
	function createCourseLevel($id_course) {

		require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');

		$map 		= array();
		$levels 	= CourseLevel::getLevels();
		$acl_man	=& Docebo::user()->getAclManager();

		$idst_main = $acl_man->registerGroup( '/lms/course/'.$id_course.'/group/alluser',
									'all the user of a course',
									true );

		foreach($levels as $lv => $value) {
			$idst = $acl_man->registerGroup( '/lms/course/'.$id_course.'/subscribed/'.$lv,
									'for course subscription in lms',
									true );
			$map[$lv] = $idst;
		}

		foreach($map as $k => $id_g) {

			$acl_man->addToGroup($idst_main, $id_g);
		}
		return $map;
	}
}

/*******************************************************************************************/

/**
 * @param int 	$id_course			the id of the course
 * @param bool 	$subdived_for_level	if is true the array is in the form
 *									[id_lv] => ([] => id_user, [] => id_user, ...), [id_lv] => ([] => id_user, ...)
 * @param int	$id_level			if is not false the array contains only a list of id_user of the level passed
 * @param bool	$exclude_waiting	if true exclude the user in wait status
 *
 * @return array	contains the id_user of the user subscribed, the structure is dependent of the other param
 */
function getSubscribed($id_course, $subdived_for_level = false, $id_level = false, $exclude_waiting = false, $edition_id=0) {

	$acl_man	=& Docebo::user()->getAclManager();
	$id_users 	= array();

	$query_courseuser = "
	SELECT idUser, level, waiting
	FROM ".$GLOBALS['prefix_lms']."_courseuser
	WHERE idCourse = '".$id_course."' AND edition_id='".(int)$edition_id."'";
	if($exclude_waiting) $query_courseuser .= " AND waiting = 0";
	if($id_level !== false) {
		$query_courseuser .= " AND level = '".$id_level."'";
	}
	$re_courseuser = sql_query($query_courseuser);
	while(list($id_user, $lv, $is_waiting) = sql_fetch_row($re_courseuser)) {

		if($subdived_for_level === false) {

			$id_users[$id_user] = $id_user;
		} else {

			if( $is_waiting) {

				$id_users['waiting'][$id_user] = $id_user;
			} else {

				$id_users[$lv][$id_user] = $id_user;
			}
		}
	}
	return $id_users;
}

/**
 * @param int 	$id_course			the id of the course
 * @param bool 	$subdived_for_level	if is true the array is in the form
 *									[id_lv] => ([] => id_user, [] => id_user, ...), [id_lv] => ([] => id_user, ...)
 * @param int	$id_level			if is not false the array contains only a list of id_user of the level passed
 * @param bool	$exclude_waiting	if true exclude the user in wait status
 *
 * @return array	contains the id_user of the user subscribed, the structure is dependent of the other param
 */
function getSubscribedInfo($id_course, $subdived_for_level = false, $id_level = false, $exclude_waiting = false, $status = false, $edition_id = false, $sort = false, $user_filter = '', $group_all_members = false, $limit = false, $date_id = false) {

	$acl_man	=& Docebo::user()->getAclManager();
	$id_users 	= array();

	$query_courseuser = "
	SELECT c.idUser, c.level, c.waiting, c.status, c.absent
	FROM ".$GLOBALS['prefix_lms']."_courseuser AS c";
	if ($sort || $user_filter !== '')
		$query_courseuser .= " JOIN ".$GLOBALS['prefix_fw']."_user AS u ON u.idst = c.idUser";
	$query_courseuser .= " WHERE c.idCourse = '".$id_course."'";
	if($exclude_waiting) $query_courseuser .= " AND c.waiting = 0";
	if($id_level !== false) {
		$query_courseuser .= " AND c.level = '".$id_level."'";
	}
	if($status !== false)
		$query_courseuser .= " AND c.status = '".$status."'";
	if($group_all_members !== false)
		$query_courseuser .= " AND c.idUser IN (".implode(',', $group_all_members).")";
	if($edition_id !== false && $edition_id > 0) {
		require_once(_lms_.'/lib/lib.edition.php');
		$ed_man = new EditionManager();
		$ed_users = $ed_man->getEditionSubscribed($edition_id);
		if (!empty($ed_users))
			$query_courseuser .= " AND c.idUser IN (".implode(",", $ed_users).")";
	}
	if($date_id !== false && $date_id > 0) {
                require_once(_lms_.'/lib/lib.date.php');
		$date_man = new DateManager();
		$dt_users_arr = $date_man->getUserForPresence($date_id);
                $dt_users = array_keys ($dt_users_arr);
		if (!empty($dt_users)){
			$query_courseuser .= " AND c.idUser IN (".implode(",", $dt_users).")";
                } else {
                    // se per quella data o edizione non è iscritto nessun utente
                    $query_courseuser .= " AND c.idUser IN (-1)";
                }
	}
	if($user_filter !== '')
		$query_courseuser .= " AND (u.firstname LIKE '%".$user_filter."%' OR u.lastname LIKE '%".$user_filter."%' OR u.userid LIKE '%".$user_filter."%')";
	if ($sort)
		$query_courseuser .= " ORDER BY u.lastname, u.firstname, u.userid";
	if($limit !== false)
		$query_courseuser .= " LIMIT ".(int)$limit.", ".(int)Get::sett('visuItem');
	$re_courseuser = sql_query($query_courseuser);
	while(list($id_user, $lv, $is_waiting, $status, $absent) = sql_fetch_row($re_courseuser)) {

		if($subdived_for_level === false) {

			$id_users[$id_user] = array( 	'idUser' => $id_user,
											'level' => $lv,
											'waiting' => $is_waiting,
											'status' => $status,
											'absent' => $absent );
		} else {

			if( $is_waiting) {

				$id_users['waiting'][$id_user] = array( 'idUser' => $id_user,
														'level' => $lv,
														'waiting' => $is_waiting,
														'status' => $status,
														'absent' => $absent );
			} else {

				$id_users[$lv][$id_user] = array( 	'idUser' => $id_user,
													'level' => $lv,
													'waiting' => $is_waiting,
													'status' => $status,
													'absent' => $absent );
			}
		}
	}
	return $id_users;
}

/**
 * @param int 	$id_course			the id of the course
 * @param bool 	$subdived_for_level	if is true the array is in the form
 *									[id_lv] => ([] => id_user, [] => id_user, ...), [id_lv] => ([] => id_user, ...)
 * @param int	$id_level			if is not false the array contains only a list of id_user of the level passed
 *
 * @return array	contains the id_user of the user subscribed and the relative level
 */
function getSubscribedLevel($id_course, $subdived_for_level = false, $id_level = false, $edition_id=0) {

	$acl_man	=& Docebo::user()->getAclManager();
	$id_users 	= array();

	$query_courseuser = "
	SELECT idUser, level, waiting
	FROM ".$GLOBALS['prefix_lms']."_courseuser
	WHERE idCourse = '".$id_course."' AND edition_id='".(int)$edition_id."'";
	if($id_level !== false) {
		$query_courseuser .= " AND level = '".$id_level."'";
	}
	$re_courseuser = sql_query($query_courseuser);
	while(list($id_user, $lv, $is_waiting) = sql_fetch_row($re_courseuser)) {

		if($subdived_for_level === false) {

			$id_users[$id_user] = $lv;
		} else {

			if( $is_waiting) {

				$id_users['waiting'][$id_user] = $id_user;
			} else {

				$id_users[$lv][$id_user] = $id_user;
			}
		}
	}
	return $id_users;
}

function getIDGroupAlluser($id_course) {

	$acl_man	=& Docebo::user()->getAclManager();
	$info = $acl_man->getGroup(FALSE, '/lms/course/'.$id_course.'/group/alluser');

	return $info[ACL_INFO_IDST];
}

/**
 * @param int	$id_user 	the idst of the user
 *
 * @return
 */
function &fromIdstToUser($id_user) {

	$users = array();
	if(!is_array($id_user) || (count($id_user) == 0) ) {
		return $users;
	}

	$acl_man	=& Docebo::user()->getAclManager();

	while(list(, $id_u) = each($id_user)) {

		$user_info = $acl_man->getUser($id_u, false);
		if( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME] == '') {

			$users[] = $user_info[ACL_INFO_USERID];
		} else {

			$users[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];
		}
	}
	return $users;
}


function getCoursesInfo(&$courses) { //return by reference? & ...

	if(empty($courses)) return array();

	$select = "
	SELECT idCourse, code, name, description
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse IN (".implode(',', $courses).")";
	$re_select = sql_query($select);
	while($assoc = sql_fetch_assoc($re_select)) {

		$re_courses[$assoc['idCourse']] = array(
			'id' => $assoc['idCourse'],
			'code' => $assoc['code'],
			'name' => $assoc['name'],
			'description' => $assoc['description'],
		);
	}
	return $re_courses;
}


function getCoursesName(&$courses) {

	if(empty($courses)) return array();

	$select = "
	SELECT idCourse, name
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse IN (".implode(',', $courses).")";
	$re_select = sql_query($select);
	while(list($id, $name) = sql_fetch_row($re_select)) {

		$re_courses[$id] = $name;
	}
	return $re_courses;
}


function isUserCourseSubcribed($id_user, $id_course, $edition_id=FALSE) {

	$course = array();
	$query_course = "
	SELECT idCourse
	FROM ".$GLOBALS['prefix_lms']."_courseuser
	WHERE idUser = '".$id_user."' AND idCourse = '".$id_course."'";

	if (($edition_id !== FALSE) && ($edition_id > 0)) {
		$query_course.=" AND edition_id='".$edition_id."'";
	}

	$re_course = sql_query($query_course);
	return (sql_num_rows($re_course) > 0) ;
}

function logIntoCourse($id_course, $gotofirst_page = true) {

	require_once(_lms_.'/lib/lib.track_user.php');

	// Reset previous opened track session if any
	if(!Docebo::user()->isAnonymous() && isset($_SESSION['idCourse'])) {
		TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], '', '');
	}
	// Unset possibile previous session setting
	if(isset($_SESSION['direct_play'])) unset($_SESSION['direct_play']);

	$re_course = sql_query("
	SELECT level, status, waiting
	FROM %lms_courseuser
	WHERE idCourse = ".(int)$id_course." AND idUser = ".(int)Docebo::user()->getId()."");
	list($level_c, $status_user, $waiting_user) = sql_fetch_row($re_course);

	Docebo::setCourse($id_course);
	$course_info = Docebo::course()->getAllInfo();
	$course_info['course_status'] 	= $course_info['status'];
	$course_info['user_status'] 	= $status_user;
	$course_info['waiting'] 		= $waiting_user;
	$course_info['level'] 			= $level_c;

	// Can the user enter into the course ?
	if(!Man_Course::canEnterCourse($course_info)) return false;

	// Disable tracking for ghost level
	$_SESSION['is_ghost'] = ($course_info['level'] == 2 ? true : false );

	// If it's the first time we need to change the course status
	if($course_info['user_status'] == _CUS_SUBSCRIBED) {

		require_once(_lms_.'/lib/lib.stats.php');
		saveTrackStatusChange(getLogUserId(), $id_course, _CUS_BEGIN);
	}
	// Setup some session data
	$_SESSION['timeEnter']		= date("Y-m-d H:i:s");
	$_SESSION['idCourse'] 		= $id_course;
	$_SESSION['levelCourse'] 	= $course_info['level'];

	//we need to redo this
	//$_SESSION['idEdition'] 		= $id_e;

	Docebo::user()->loadUserSectionST('/lms/course/private/'.$course_info['level'].'/');
	Docebo::user()->SaveInSession();

	// Initialize the session into the course
	TrackUser::createSessionCourseTrack();

	$first_page = firstPage();
	$_SESSION['current_main_menu']	= $first_page['idMain'];
	$_SESSION['sel_module_id']		= $first_page['idModule'];
	$jumpurl = 'index.php?modname='.$first_page['modulename'].'&op='.$first_page['op'].'&id_module_sel='.$first_page['idModule'];

	// course in direct play or assessment
	if($course_info['direct_play'] == 1 || $course_info['course_type'] == 'assessment') {

		if($_SESSION['levelCourse'] >= 4) {

			// direct play with a teacher, basically it's not ok
			// check if we are managing the LOs from admin: if yes, jump into the test management
			if($course_info['course_type'] == 'assessment' && Get::req('from_admin', DOTY_INT, 0) > 0) {

				// enter the assessment course and go to test editing if there is a test with no question in it
				$query = "SELECT idOrg, idResource "
					." FROM %lms_organization "
					." WHERE idCourse = ".(int)$_SESSION['idCourse']." AND objectType = 'test' "
					." ORDER BY path ASC, title ASC "
					." LIMIT 0,1";
				$res = sql_query($query);
				if($res && sql_num_rows($res)>0) {

					list($id_org, $id_test) = sql_fetch_row($res);
					if ($id_test > 0) {
						require_once(_lms_.'/lib/lib.test.php');
						$tman = new TestManagement($id_test);
						if ($tman->getNumberOfQuestion() <= 0) {
							Util::jump_to('index.php?modname=test&op=modtestgui&idTest='.$id_test.'&back_url='.urlencode($jumpurl));
						}
					}
				}
			}
		} else {
			// direct play with a student
			// i need to play directly the course if it's not completed and is the only object of the course
			require_once(_lms_.'/lib/lib.orgchart.php');
			$orgman = new OrganizationManagement($_SESSION['idCourse']);
			$first_lo =& $orgman->getInfoWhereType(false, $_SESSION['idCourse']);


			if(count($first_lo) == 1) {

				$_SESSION['direct_play'] = 1;
				$obj = array_shift($first_lo);
				Util::jump_to('index.php?modname=organization&op=custom_playitem&id_item='.$obj['id_org'].'');
			} elseif(count($first_lo) >= 2) {

				$obj = array_shift($first_lo);
				// if we have more than an object we need to play the first one until it's completed
				$query = "SELECT status FROM %lms_commontrack WHERE idReference = ".(int)$obj['id_org']." AND idUser = ".(int)Docebo::user()->getId();
				list($status) = sql_fetch_row(sql_query($query));
				if(($status == 'completed' || $status == 'passed') && $gotofirst_page) Util::jump_to($jumpurl);
				else {
					$_SESSION['direct_play'] = 1;
					Util::jump_to('index.php?modname=organization&op=custom_playitem&id_item='.$obj['id_org'].'');
				}
			}
		}
	}
	// if everyhings fail
	if($gotofirst_page) Util::jump_to($jumpurl);
	else return true;
/*

		// i need to play directly the course if it's not completed and is the only object of the course
		require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
		$orgman = new OrganizationManagement($_SESSION['idCourse']);
		$first_lo =& $orgman->getInfoWhereType(false, $_SESSION['idCourse']);

		if(count($first_lo) >= 1) {
			$obj = array_shift($first_lo);
			$_SESSION['direct_play'] = 1;
			// is it completed ?
			if(count($first_lo) >= 2) {


			} else {
				Util::jump_to('index.php?modname=organization&op=custom_playitem&id_item='.$obj['id_org'].'');
			}
		}
		if($gotofirst_page) Util::jump_to($jumpurl);
		else return true;
	} else {
		if ($course_info['course_type'] == "assessment") {
			//check if we are managing the LOs from admin: if yes, jump into the test management
			if (Get::req('from_admin', DOTY_INT, 0) > 0) {
				//enter the assessment course and go to test editing
				$query = "SELECT idOrg, idResource FROM %lms_organization WHERE idCourse=".(int)$_SESSION['idCourse']." AND objectType='test' "
					." ORDER BY path ASC, title ASC LIMIT 0,1";
				$res = sql_query($query);
				if ($res && sql_num_rows($res)>0) {

					list($id_org, $id_test) = sql_fetch_row($res);

					if ($id_test > 0) {
						require_once(_lms_.'/lib/lib.test.php');
						$tman = new TestManagement($id_test);

						if ($tman->getNumberOfQuestion() <= 0) {
							Util::jump_to('index.php?modname=test&op=modtestgui&idTest='.$id_test.'&back_url='.urlencode($jumpurl));
						}
					}
				}
			}
		}
		if($gotofirst_page) Util::jump_to($jumpurl);
		else return true;
	}


	switch($course_info['course_type']) {
		case "assessment" : {

			if($_SESSION['levelCourse'] <= 3) {

				// i need to play directly the test
				require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
				$orgman = new OrganizationManagement($_SESSION['idCourse']);
				$test =& $orgman->getInfoWhereType('test', $_SESSION['idCourse']);

				if(count($test) == 1) {
					$obj = array_shift($test);
					$_SESSION['test_assessment'] = 1;
					Util::jump_to('index.php?modname=organization&op=custom_playitem&id_item='.$obj['id_org'].'');
				}
				if($gotofirst_page) Util::jump_to($jumpurl);
				else return true;
			} else {
				if($gotofirst_page) {
					//...
					Util::jump_to($jumpurl);
				}
				else return true;
			}
		};break;
		default: {
			if($gotofirst_page) Util::jump_to($jumpurl);
			else return true;
		}
	}
	/* not used in
	// now analyze the course type and select the acton to perform
	if(isset($_GET['showresult'])) {

		require_once(_lms_.'/lib/lib.orgchart.php');
		$orgman = new OrganizationManagement($_SESSION['idCourse']);
		$scorm =& $orgman->getInfoWhereType('scormorg', $_SESSION['idCourse']);

		if(count($scorm) == '1') {
			$obj = array_shift($scorm);
			Util::jump_to('index.php?modname=organization&op=scorm_track&id_user='.getLogUserId().'&id_org='.$obj['id_resource'].'&amp;back='.$GLOBALS['course_descriptor']->getValue('direct_play'));
		}
		Util::jump_to('index.php?modname=course&op=showresults&id_course='.$_SESSION['idCourse']);
	}
	*/
}

function getModuleFromId($id_module) {

	$query_menu = "
	SELECT module_name, default_op
	FROM ".$GLOBALS['prefix_lms']."_module
	WHERE idModule = ".(int)$id_module." ";

	$re_module = sql_query($query_menu);
	if(!$re_module || sql_num_rows($re_module) == 0) return false;
	$result = sql_fetch_row($re_module);
	return $result;
}

/**
 * @param 	int $idMain if passed return the first voice of the relative menu
 *
 * @return 	array 	with three element modulename and op that contains the first accessible menu element
 *					indicate in idMain  array( [idMain], [modulename], [op] )
 **/
function firstPage( $idMain = false ) {
    
    require_once(_lms_ . '/lib/lib.permission.php');

	$query_main = "
	SELECT module.idModule, main.idMain, module.module_name, module.default_op, module.token_associated
	FROM ( ".$GLOBALS['prefix_lms']."_menucourse_main AS main JOIN
		".$GLOBALS['prefix_lms']."_menucourse_under AS un ) JOIN
		".$GLOBALS['prefix_lms']."_module AS module
	WHERE main.idMain = un.idMain AND un.idModule = module.idModule
		AND main.idCourse = '".(int)$_SESSION['idCourse']."'
		AND un.idCourse = '".(int)$_SESSION['idCourse']."'
		".( $idMain !== false ? " AND main.idMain='".$idMain."' "  : '' )."
	ORDER BY main.sequence, un.sequence";
	$re_main = sql_query($query_main);

	while(list($id_module, $main, $module_name, $default_op, $token) = sql_fetch_row($re_main)) {

		if(checkPerm($token, true, $module_name)) {

			return array('idModule'=> $id_module, 'idMain' => $main, 'modulename' => $module_name, 'op' => $default_op);
		}
	}
}




//retrieve course types
function getCourseTypes() {
	return array(
		'elearning' 	=> Lang::t('_COURSE_TYPE_ELEARNING', 'course'),
		'blended' 		=> Lang::t('_COURSE_TYPE_BLENDED', 'course'),
		'classroom' 	=> Lang::t('_CLASSROOM', 'course')
	);
}


?>