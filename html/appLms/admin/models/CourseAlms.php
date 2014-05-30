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

Class CourseAlms extends Model
{
	protected $acl_man;
	public $course_man;
	public $classroom_man;
	public $edition_man;

	protected $id_course;
	protected $id_date;
	protected $id_edition;

	public function __construct($id_course = 0, $id_date = 0, $id_edition = 0)
	{
		require_once(_lms_.'/lib/lib.date.php');
		require_once(_lms_.'/lib/lib.edition.php');
		require_once(_lms_.'/lib/lib.course.php');

		$this->id_course = $id_course;
		$this->id_date = $id_date;
		$this->id_edition = $id_edition;

		$this->course_man = new Man_Course();
		$this->classroom_man = new DateManager();
		$this->edition_man = new EditionManager();

		$this->acl_man =& Docebo::user()->getAclManager();
	}


	public function getPerm() {
		return array(
			'view'			=> 'standard/view.png',
			'add'				=> 'standard/add.png',
			'mod'				=> 'standard/edit.png',
			'del'				=> 'standard/rem.png',
			'moderate'	=> '',
			'subscribe'	=> ''
		);
	}

	public function getCourseNumber($filter = false)
	{
		$query =	"SELECT COUNT(*)"
					." FROM %lms_course"
					." WHERE course_type <> 'assessment'";

		if ($filter)
		{
			if (isset($filter['id_category']))
			{
				if (isset($filter['descendants']) && $filter['descendants'])
					$query .= " AND idCategory IN (".implode(',', $this->getCategoryDescendants($filter['id_category'])).")";
				else
					$query .= " AND idCategory = ".(int)$filter['id_category'];
			}
			if (isset($filter['text']) && $filter['text'] !== '')
				$query .=	" AND( name LIKE '%".$filter['text']."%'"
							." OR code LIKE '%".$filter['text']."%')";

			if(isset($filter['waiting']) && $filter['waiting'])
			{
				$query_course =	"SELECT idCourse"
								." FROM %lms_courseuser"
								." WHERE waiting = 1";

				$result = sql_query($query);
				$id_course_filter = array(0 => 0);

				while(list($id_course_tmp) = sql_fetch_row($result))
					$id_course_filter[$id_course_tmp] = $id_course_tmp;

				$query .= " AND idCourse IN (".implode(',', $id_course_filter).")";
			}

			if(isset($filter['classroom']) && $filter['classroom'])
				$query .=	" AND course_type = 'classroom'";
		}

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
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
					$query .= " AND 0 ";
				else
					$query .= " AND idCourse IN (".implode(',', $admin_courses['course']).") ";
			}
		}

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getCategoryDescendants($id_category)
	{
		$output = array();

		if($id_category != 0)
		{
			$query = "SELECT iLeft, iRight FROM %lms_category WHERE idCategory=".(int)$id_category;
			$res = sql_query($query);
			list($left, $right) = sql_fetch_row($res);

			$query = "SELECT idCategory FROM %lms_category WHERE iLeft>=".$left." AND iRight<=".$right;
			$res = sql_query($query);
			while (list($id_cat) =sql_fetch_row($res)) $output[] = $id_cat;
		}
		else
		{
			$output[] = 0;

			$query = "SELECT idCategory FROM %lms_category";
			$res = sql_query($query);
			while (list($id_cat) = sql_fetch_row($res)) $output[] = $id_cat;
		}

		return $output;
	}

	public function loadCourse($start_index, $results, $sort, $dir, $filter = false)
	{
		$userlevelid = Docebo::user()->getUserLevelId();
		if($userlevelid != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$acl_man =& Docebo::user()->getAclManager();

			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());

			$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
			$admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
		}

		$query = "SELECT c.*, COUNT(cu.idUser) as subscriptions, SUM(cu.waiting) as pending"
				." FROM %lms_course AS c"
				." LEFT JOIN %lms_courseuser AS cu ON c.idCourse = cu.idCourse"
				.($userlevelid != ADMIN_GROUP_GODADMIN
					? ( !empty($admin_users) ? " AND cu.idUser IN (".implode(',', $admin_users).")" : " AND cu.idUser IN (0)" )
					: '' )
				." WHERE c.course_type <> 'assessment'";

		if ($filter)
		{
			if (isset($filter['id_category']))
			{
				if (isset($filter['descendants']) && $filter['descendants'])
					$query .= " AND c.idCategory IN (".implode(',', $this->getCategoryDescendants($filter['id_category'])).")";
				else
					$query .= " AND c.idCategory = ".(int)$filter['id_category'];
			}
			if (isset($filter['text']) && $filter['text'] !== '')
				$query .=	" AND( c.name LIKE '%".$filter['text']."%'"
							." OR c.code LIKE '%".$filter['text']."%')";

			if(isset($filter['waiting']) && $filter['waiting'])
			{
				$query_course =	"SELECT idCourse"
								." FROM %lms_courseuser"
								." WHERE waiting = 1";

				$result = sql_query($query_course);
				$id_course_filter = array(0 => 0);

				while(list($id_course_tmp) = sql_fetch_row($result))
					$id_course_filter[$id_course_tmp] = $id_course_tmp;

				$query .= " AND c.idCourse IN (".implode(',', $id_course_filter).")";
			}

			if(isset($filter['classroom']) && $filter['classroom'])
				$query .=	" AND course_type = 'classroom'";
		}

		if($userlevelid != ADMIN_GROUP_GODADMIN)
		{
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
					$query .= " AND 0 ";
				else
					$query .= " AND c.idCourse IN (".implode(',', $admin_courses['course']).") ";
			}
		}

		$query .=	" GROUP BY c.idCourse"
					." ORDER BY ".$sort." ".$dir;

		if ((int)$results > 0) $query .= " LIMIT ".(int)$start_index.", ".(int)$results;

		return sql_query($query);
	}

	public function getCourseModDetails($id_course = false)
	{
		if($id_course === false)
			return array(
					'autoregistration_code' => '',
					'code' 				=> '',
					'name' 				=> '',
					'lang_code' 		=> getLanguage(),
					'difficult' 		=> 'medium',
					'course_type' 		=> 'classroom',//'elearning',
					'status' 			=> CST_EFFECTIVE,
					'course_edition' 	=> 0,
					'description' 		=> '',
					'can_subscribe' 	=> 1,
					'sub_start_date' 	=> '',
					'sub_end_date' 		=> '',
					'show_rules' 		=> 0,
					'credits'			=> 0,
					'show_progress' 	=> 1,
					'show_time' 		=> 1,
					'show_who_online' 	=> 1,
					'show_extra_info' 	=> 0,
					'level_show_user' 	=> 0,
					'subscribe_method' 	=> 2,
					'selling' 			=> 0,
					'prize' 			=> '',
					'advance' 			=> '',
					'permCloseLO' 		=> 0,
					'userStatusOp' 		=> (1 << _CUS_SUSPEND),
					'direct_play'		=> 0,
					'date_begin' 		=> '',
					'date_end' 			=> '',
					'hour_begin' 		=> '-1',
					'hour_end' 			=> '-1',
					'valid_time' 		=> '0',
					'mediumTime' 		=> '0',
					'min_num_subscribe' => '0',
					'max_num_subscribe' => '0',
					'allow_overbooking' => '',
					'course_quota' 		=> '',
					'show_result' 		=> '0',
					'linkSponsor' 		=> 'http://',
					'use_logo_in_courselist' => '1',
					'img_material' => '',
					'img_course' => '',
					'img_othermaterial' => '',
					'imgSponsor' => '',
					'course_demo' => '',
					'auto_unsubscribe' => '0',
					'unsubscribe_date_limit' => ''
				);
		else
		{
			$query_course = "
			SELECT idCourse,idCategory, code, name, description, lang_code, status, level_show_user, subscribe_method,
				linkSponsor, mediumTime, permCloseLO, userStatusOp, difficult,
				show_progress, show_time,

				show_who_online,

				show_extra_info, show_rules, date_begin, date_end, hour_begin, hour_end, sub_start_date, sub_end_date, valid_time,
				min_num_subscribe, max_num_subscribe, max_sms_budget,selling,prize,course_type,policy_point,point_to_all,course_edition,
				imgSponsor, img_course, img_material, img_othermaterial, course_demo, course_quota, allow_overbooking,
				can_subscribe, advance, autoregistration_code, direct_play, show_result, credits,

				use_logo_in_courselist, auto_unsubscribe, unsubscribe_date_limit
			FROM %lms_course
			WHERE idCourse = '".(int)$id_course."'";

			$course = sql_fetch_assoc(sql_query($query_course));
			if($course) {
				$course['date_begin'] 	= Format::date($course['date_begin'], 'date');
				$course['date_end'] 	= Format::date($course['date_end'], 'date');
				$course['sub_start_date'] = Format::date($course['sub_start_date'], 'date');
				$course['sub_end_date'] = Format::date($course['sub_end_date'], 'date');
			}
			return $course;
		}
	}

	public function insCourse()
	{
		require_once(_base_.'/lib/lib.upload.php');
		require_once(_base_.'/lib/lib.multimedia.php');
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.manmenu.php');

		$array_lang = Docebo::langManager()->getAllLangCode();
		$array_lang[] = 'none';

		$acl_man =& Docebo::user()->getAclManager();

		$id_custom = Get::req('selected_menu', DOTY_INT, 0);

		// calc quota limit
		$quota = $_POST['course_quota'];
		if(isset($_POST['inherit_quota']))
		{
			$quota = Get::sett('course_quota');
			$_POST['course_quota'] = COURSE_QUOTA_INHERIT;
		}

		$quota = $quota * 1024 * 1024;

		$path = Get::sett('pathcourse');
		$path = '/appLms/'.Get::sett('pathcourse').( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

		if($_POST['course_name'] == '')
			$_POST['course_name'] = Lang::t('_NO_NAME', 'course');

		// restriction on course status ------------------------------------------
		$user_status = 0;
		if(isset($_POST['user_status']))
			while(list($status) = each($_POST['user_status']))
				$user_status |= (1 << $status);

		// level that will be showed in the course --------------------------------
		$show_level = 0;
		if(isset($_POST['course_show_level']))
			while(list($lv) = each($_POST['course_show_level']))
				$show_level |= (1 << $lv);

		// save the file uploaded -------------------------------------------------
		$file_sponsor 		= '';
		$file_logo 			= '';
		$file_material 		= '';
		$file_othermaterial = '';
		$file_demo 			= '';

		$error 				= false;
		$quota_exceeded 	= false;
		$total_file_size 	= 0;

		if(is_array($_FILES) && !empty($_FILES))
			sl_open_fileoperations();
		// load user material ---------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_user_material',
												'',
												$path,
												($quota != 0 ? $quota - $total_file_size : false),
												false );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_material		= $arr_file['filename'];
		$total_file_size 	= $total_file_size + $arr_file['new_size'];

		// course otheruser material -------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_otheruser_material',
												'',
												$path,
												($quota != 0 ? $quota - $total_file_size : false),
												false );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_othermaterial	= $arr_file['filename'];
		$total_file_size 	= $total_file_size + $arr_file['new_size'];

		// course demo-----------------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_demo',
												'',
												$path,
												($quota != 0 ? $quota - $total_file_size : false),
												false );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_demo			= $arr_file['filename'];
		$total_file_size 	= $total_file_size + $arr_file['new_size'];

		// course sponsor---------------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_sponsor_logo',
												'',
												$path,
												($quota != 0 ? $quota - $total_file_size : false),
												false,
												true );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_sponsor		= $arr_file['filename'];
		$total_file_size 	= $total_file_size + $arr_file['new_size'];

		// course logo-----------------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_logo',
												'',
												$path,
												($quota != 0 ? $quota - $total_file_size : false),
												false,
												true );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_logo			= $arr_file['filename'];
		$total_file_size 	= $total_file_size + $arr_file['new_size'];

		// ----------------------------------------------------------------------------------------------
		sl_close_fileoperations();


		if ($_POST["can_subscribe"] == "2")
		{
			$sub_start_date = Format::dateDb($_POST["sub_start_date"], "date");
			$sub_end_date 	= Format::dateDb($_POST["sub_end_date"], "date");
		}

		$date_begin	= Format::dateDb($_POST['course_date_begin'], "date");
		$date_end 	= Format::dateDb($_POST['course_date_end'], "date");

		// insert the course in database -----------------------------------------------------------
		$hour_begin = '-1';
		$hour_end = '-1';
		if($_POST['hour_begin']['hour'] != '-1')
		{
			$hour_begin = ( strlen($_POST['hour_begin']['hour']) == 1 ? '0'.$_POST['hour_begin']['hour'] : $_POST['hour_begin']['hour'] );
			if($_POST['hour_begin']['quarter'] == '-1')
				$hour_begin .= ':00';
			else
				$hour_begin .= ':'.$_POST['hour_begin']['quarter'];
		}

		if($_POST['hour_end']['hour'] != '-1')
		{
			$hour_end = ( strlen($_POST['hour_end']['hour']) == 1 ? '0'.$_POST['hour_end']['hour'] : $_POST['hour_end']['hour'] );
			if($_POST['hour_end']['quarter'] == '-1')
				$hour_end .= ':00';
			else
				$hour_end .= ':'.$_POST['hour_end']['quarter'];
		}

		$query_course = "
		INSERT INTO %lms_course
		SET idCategory 			= '".( isset($_POST['idCategory']) ? $_POST['idCategory'] : 0 )."',
			code 				= '".$_POST['course_code']."',
			name 				= '".$_POST['course_name']."',
			description 		= '".$_POST['course_descr']."',
			lang_code 			= '".$array_lang[$_POST['course_lang']]."',
			status 				= '".(int)$_POST['course_status']."',
			level_show_user 	= '".$show_level."',
			subscribe_method 	= '".(int)$_POST['course_subs']."',
			credits			 	= '".(int)$_POST['credits']."',

			create_date			= '".date("Y-m-d H:i:s")."',

			linkSponsor 		= '".$_POST['course_sponsor_link']."',
			imgSponsor 			= '".$file_sponsor."',
			img_course 			= '".$file_logo."',
			img_material 		= '".$file_material."',
			img_othermaterial 	= '".$file_othermaterial."',
			course_demo 		= '".$file_demo."',

			mediumTime 			= '".$_POST['course_medium_time']."',
			permCloseLO 		= '".$_POST['course_em']."',
			userStatusOp 		= '".$user_status."',
			difficult 			= '".$_POST['course_difficult']."',

			show_progress 		= '".( isset($_POST['course_progress']) ? 1 : 0 )."',
			show_time 			= '".( isset($_POST['course_time']) ? 1 : 0 )."',

			show_who_online		= '".$_POST['show_who_online']."',

			show_extra_info 	= '".( isset($_POST['course_advanced']) ? 1 : 0 )."',
			show_rules 			= '".(int)$_POST['course_show_rules']."',

			direct_play 		= '".( isset($_POST['direct_play']) ? 1 : 0 )."',

			date_begin 			= '".$date_begin."',
			date_end 			= '".$date_end."',
			hour_begin 			= '".$hour_begin."',
			hour_end 			= '".$hour_end."',

			valid_time 			= '".(int)$_POST['course_day_of']."',

			min_num_subscribe 	= '".(int)$_POST['min_num_subscribe']."',
			max_num_subscribe 	= '".(int)$_POST['max_num_subscribe']."',
			selling 			= '".( isset($_POST['course_sell']) ? '1' : '0' )."',
			prize 				= '".$_POST['course_prize']."',

			course_type 		= '".$_POST['course_type']."',

			course_edition 		= '".( isset($_POST['course_edition']) && $_POST['course_edition'] == 1 ? 1 : 0) ."',

			course_quota 		= '".$_POST['course_quota']."',
			used_space			= '".$total_file_size."',
			allow_overbooking 	= '".( isset($_POST["allow_overbooking"]) ? 1 : 0 )."',
			can_subscribe 		= '".(int)$_POST["can_subscribe"]."',
			sub_start_date 		= ".( $_POST["can_subscribe"] == '2' ? "'".$sub_start_date."'" : 'NULL' ).",
			sub_end_date 		= ".( $_POST["can_subscribe"] == '2' ? "'".$sub_end_date."'" : 'NULL' ).",

			advance 			= '".$_POST['advance']."',
			show_result 		= '".( isset($_POST["show_result"]) ? 1 : 0 )."',

			use_logo_in_courselist = '".( isset($_POST['use_logo_in_courselist']) ? '1' : '0' )."',

			auto_unsubscribe = '".(int)$_POST['auto_unsubscribe']."',
			unsubscribe_date_limit = ".(isset($_POST['use_unsubscribe_date_limit']) && $_POST['use_unsubscribe_date_limit']>0 ? "'".Format::dateDb($_POST['unsubscribe_date_limit'], 'date')."'" : 'NULL')."";

		if (isset($_POST['random_course_autoregistration_code']))
		{
			$control = 1;
			$str = '';

			while ($control)
			{
				for($i = 0; $i < 10; $i++)
				{
					$seed = mt_rand(0, 10);
						if($seed > 5)
							$str .= mt_rand(0, 9);
						else
							$str .= chr(mt_rand(65, 90));

				}

				$control_query = "SELECT COUNT(*)" .
								" ".$GLOBALS['prefix_lms']."_course" .
								" WHERE autoregistration_code = '".$str."'";

				$control_result = sql_query($control_query);
				list($result) = sql_fetch_row($control_result);
				$control = $result;
			}

			$query_course .= ", autoregistration_code = '".$str."'";
		}
		else
			$query_course .= ", autoregistration_code = '".$_POST['course_autoregistration_code']."'";

		if(!sql_query($query_course))
		{
			// course save failed, delete uploaded file
			if($file_sponsor != '')			sl_unlink($path.$file_sponsor);
			if($file_logo != '')			sl_unlink($path.$file_logo);
			if($file_material != '')		sl_unlink($path.$file_material);
			if($file_othermaterial != '')	sl_unlink($path.$file_othermaterial);
			if($file_demo != '')			sl_unlink($path.$file_demo);

			return array('err' => '_err_course');
		}

		// recover the id of the course inserted --------------------------------------------
		list($id_course) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

		require_once(_lms_.'/admin/models/LabelAlms.php');
		$label_model = new LabelAlms();

		$label = Get::req('label', DOTY_INT, 0);

		$label_model->associateLabelToCourse($label, $id_course);

		// add this corse to the pool of course visible by the user that have create it -----
		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$adminManager->addAdminCourse($id_course, Docebo::user()->getIdSt());
		}

		//if the scs exist create a room ----------------------------------------------------
		if($GLOBALS['where_scs'] !== false)
		{
			require_once($GLOBALS['where_scs'].'/lib/lib.room.php');

			$rules = array(
						'room_name' => $_POST['course_name'],
						'room_type' => 'course',
						'id_source' => $id_course );
			$re = insertRoom($rules);
		}
		$course_idst =& DoceboCourse::createCourseLevel($id_course);

		// create the course menu -----------------------------------------------------------
		if(!cerateCourseMenuFromCustom($id_custom, $id_course, $course_idst))
			return array('err' => '_err_coursemenu');

		$res = array();

		if($quota_exceeded)
			$res['limit_reach'] = 1;

        if ($error)
            $res['err'] = '_err_course';
        else {
            //AUTO SUBSCRIPTION
            if (isset($_POST['auto_subscription']) && $_POST['auto_subscription'] == 1) {
                $userId = Docebo::user()->getIdSt();

                if (!$this->autoUserRegister($userId, $id_course)) {
                    die('Error during autosubscription');
                }
            }
            $res['res'] = '_ok_course';
        }
        
		return $res;
	}

	public function upCourse()
	{
		require_once(_base_.'/lib/lib.upload.php');
		require_once(_base_.'/lib/lib.multimedia.php');
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.manmenu.php');

		$array_lang = Docebo::langManager()->getAllLangCode();
		$array_lang[] = 'none';

		$acl_man =& Docebo::user()->getAclManager();

		$id_course = Get::req('id_course', DOTY_INT, 0);

		require_once(_lms_.'/admin/models/LabelAlms.php');
		$label_model = new LabelAlms();

		$label = Get::req('label', DOTY_INT, 0);

		$label_model->associateLabelToCourse($label, $id_course);

		// calc quota limit
		$quota = $_POST['course_quota'];
		if(isset($_POST['inherit_quota']))
		{
			$quota = Get::sett('course_quota');
			$_POST['course_quota'] = COURSE_QUOTA_INHERIT;
		}
		$quota = $quota*1024*1024;

		$course_man = new DoceboCourse($id_course);
		$used = $course_man->getUsedSpace();

		if($_POST['course_name'] == '') $_POST['course_name'] = Lang::t('_NO_NAME', 'course', 'lms');

		// restriction on course status ------------------------------------------
		$user_status = 0;
		if(isset($_POST['user_status']))
			while(list($status) = each($_POST['user_status']))
				$user_status |= (1 << $status);

		// level that will be showed in the course --------------------------------
		$show_level = 0;
		if(isset($_POST['course_show_level']))
			while(list($lv) = each($_POST['course_show_level']))
				$show_level |= (1 << $lv);

		// save the file uploaded -------------------------------------------------

		$error 			= false;
		$quota_exceeded = false;

		$path = Get::sett('pathcourse');
		$path = '/appLms/'.Get::sett('pathcourse').( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

		$old_file_size 	= 0;
		if ((is_array($_FILES) && !empty($_FILES)) || (is_array($_POST["file_to_del"])))
			sl_open_fileoperations();

		// load user material ---------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_user_material',
												$_POST["old_course_user_material"],
												$path,
												($quota != 0 ? $quota - $used : false),
												isset($_POST['file_to_del']['course_user_material']) );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_material		= $arr_file['filename'];
		$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
		$old_file_size 		+= $arr_file['old_size'];

		// course otheruser material -------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_otheruser_material',
												$_POST["old_course_otheruser_material"],
												$path,
												($quota != 0 ? $quota - $used : false),
												isset($_POST['file_to_del']['course_otheruser_material']) );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_othermaterial	= $arr_file['filename'];
		$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
		$old_file_size 		+= $arr_file['old_size'];

		// course demo-----------------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_demo',
												$_POST["old_course_demo"],
												$path,
												($quota != 0 ? $quota - $used : false),
												isset($_POST['file_to_del']['course_demo']) );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_demo			= $arr_file['filename'];
		$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
		$old_file_size 		+= $arr_file['old_size'];
		// course sponsor---------------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_sponsor_logo',
												$_POST["old_course_sponsor_logo"],
												$path,
												($quota != 0 ? $quota - $used : false),
												isset($_POST['file_to_del']['course_sponsor_logo']),
												true );
		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_sponsor		= $arr_file['filename'];
		$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
		$old_file_size 		+= $arr_file['old_size'];
		// course logo-----------------------------------------------------------------------------------
		$arr_file = $this->manageCourseFile(	'course_logo',
												$_POST["old_course_logo"],
												$path,
												($quota != 0 ? $quota - $used : false),
												isset($_POST['file_to_del']['course_logo']),
												true );

		$error 				|= $arr_file['error'];
		$quota_exceeded 	|= $arr_file['quota_exceeded'];
		$file_logo			= $arr_file['filename'];
		$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
		$old_file_size 		+= $arr_file['old_size'];
		// ----------------------------------------------------------------------------------------------
		sl_close_fileoperations();

		$date_begin	= Format::dateDb($_POST['course_date_begin'], "date");
		$date_end 	= Format::dateDb($_POST['course_date_end'], "date");

		if ($_POST["can_subscribe"] == "2")
		{
			$sub_start_date = Format::dateDb($_POST["sub_start_date"], "date");
			$sub_end_date 	= Format::dateDb($_POST["sub_end_date"], "date");
		}

		$hour_begin = '-1';
		$hour_end = '-1';
		if($_POST['hour_begin']['hour'] != '-1')
		{
			$hour_begin = ( strlen($_POST['hour_begin']['hour']) == 1 ? '0'.$_POST['hour_begin']['hour'] : $_POST['hour_begin']['hour'] );
			if($_POST['hour_begin']['quarter'] == '-1')
				$hour_begin .= ':00';
			else
				$hour_begin .= ':'.$_POST['hour_begin']['quarter'];
		}

		if($_POST['hour_end']['hour'] != '-1')
		{
			$hour_end = ( strlen($_POST['hour_end']['hour']) == 1 ? '0'.$_POST['hour_end']['hour'] : $_POST['hour_end']['hour'] );
			if($_POST['hour_end']['quarter'] == '-1')
				$hour_end .= ':00';
			else
				$hour_end .= ':'.$_POST['hour_end']['quarter'];
		}

		// update database ----------------------------------------------------
		$query_course = "
		UPDATE ".$GLOBALS['prefix_lms']."_course
		SET code 				= '".$_POST['course_code']."',
			name 				= '".$_POST['course_name']."',
			idCategory 			= '".(int)$_POST['idCategory']."',
			description 		= '".$_POST['course_descr']."',
			lang_code 			= '".$array_lang[$_POST['course_lang']]."',
			status 				= '".(int)$_POST['course_status']."',
			level_show_user 	= '".$show_level."',
			subscribe_method 	= '".(int)$_POST['course_subs']."',
			idCategory			= '".(int)$_POST['idCategory']."',
			credits				= '".(int)$_POST['credits']."',

			linkSponsor 		= '".$_POST['course_sponsor_link']."',

			imgSponsor 			= '".$file_sponsor."',
			img_course 			= '".$file_logo."',
			img_material 		= '".$file_material."',
			img_othermaterial 	= '".$file_othermaterial."',
			course_demo 		= '".$file_demo."',

			mediumTime 			= '".$_POST['course_medium_time']."',
			permCloseLO 		= '".$_POST['course_em']."',
			userStatusOp 		= '".$user_status."',
			difficult 			= '".$_POST['course_difficult']."',

			show_progress 		= '".( isset($_POST['course_progress']) ? 1 : 0 )."',
			show_time 			= '".( isset($_POST['course_time']) ? 1 : 0 )."',

			show_who_online		= '".$_POST['show_who_online']."',

			show_extra_info 	= '".( isset($_POST['course_advanced']) ? 1 : 0 )."',
			show_rules 			= '".(int)$_POST['course_show_rules']."',

			direct_play 		= '".( isset($_POST['direct_play']) ? 1 : 0 )."',

			date_begin 			= '".$date_begin."',
			date_end 			= '".$date_end."',
			hour_begin 			= '".$hour_begin."',
			hour_end 			= '".$hour_end."',

			valid_time 			= '".(int)$_POST['course_day_of']."',

			min_num_subscribe 	= '".(int)$_POST['min_num_subscribe']."',
			max_num_subscribe 	= '".(int)$_POST['max_num_subscribe']."',

			course_type 		= '".$_POST['course_type']."',
			point_to_all 		= '".( isset($_POST['point_to_all']) ? $_POST['point_to_all'] : 0 )."',
			course_edition 		= '".( isset($_POST['course_edition']) ? $_POST['course_edition'] : 0 )."',
			selling 			= '".( isset($_POST['course_sell']) ? 1 : 0 )."',
			prize 				= '".( isset($_POST['course_prize']) ? $_POST['course_prize'] : 0 )."',
			policy_point 		= '".$_POST['policy_point']."',

			course_quota 		= '".$_POST['course_quota']."',

			allow_overbooking 	= '".( isset($_POST["allow_overbooking"]) ? 1 : 0 )."',
			can_subscribe 		= '".(int)$_POST["can_subscribe"]."',
			sub_start_date 		= ".( $_POST["can_subscribe"] == "2" ? "'".$sub_start_date."'" : 'NULL' ).",
			sub_end_date 		= ".( $_POST["can_subscribe"] == "2" ? "'".$sub_end_date."'" : 'NULL' ).",

			advance 			= '".$_POST['advance']."',
			show_result 		= '".( isset($_POST['show_result']) ? 1 : 0 )."',


			use_logo_in_courselist = '".( isset($_POST['use_logo_in_courselist']) ? '1' : '0' )."',

			auto_unsubscribe = '".(int)$_POST['auto_unsubscribe']."',
			unsubscribe_date_limit = ".(isset($_POST['use_unsubscribe_date_limit']) && $_POST['use_unsubscribe_date_limit']>0 ? "'".Format::dateDb($_POST['unsubscribe_date_limit'], 'date')."'" : 'NULL')."";

			if (isset($_POST['random_course_autoregistration_code']))
			{
				$control = 1;
				$str = '';

				while ($control)
				{
					for($i = 0; $i < 10; $i++)
					{
						$seed = mt_rand(0, 10);
						if($seed > 5)
							$str .= mt_rand(0, 9);
						else
							$str .= chr(mt_rand(65, 90));
					}

					$control_query = "SELECT COUNT(*)" .
									" ".$GLOBALS['prefix_lms']."_course" .
									" WHERE autoregistration_code = '".$str."'" .
									" AND idCourse <> '".$id_course."'";

					$control_result = sql_query($control_query);
					list($result) = sql_fetch_row($control_result);
					$control = $result;
				}

				$query_course .= ", autoregistration_code = '".$str."'";
			}
			else
				$query_course .= ", autoregistration_code = '".$_POST['course_autoregistration_code']."'";

		$query_course .= " WHERE idCourse = '".$id_course."'";

		if(!sql_query($query_course)) {

	if($file_sponsor != '') 	sl_unlink($path.$file_sponsor);
			if($file_logo != '') 		sl_unlink($path.$file_logo);
			if($file_material != '') 	sl_unlink($path.$file_material);
			if($file_othermaterial != '') sl_unlink($path.$file_othermaterial);
			if($file_demo != '') 		sl_unlink($path.$file_demo);

			$course_man->subFileToUsedSpace(false, $old_file_size);
			return array('err' => '_err_course');
		}

		// cascade modify on all the edition of the course
		if (isset($_POST['cascade_on_ed']) && $id_course > 0) {

			$cinfo = $this->getInfo($id_course);
			$has_editions = $cinfo['course_edition'] > 0;
			$has_classrooms = $cinfo['course_type'] == 'classroom';

			if ($has_editions) {
				$query_editon = "UPDATE %lms_course_editions "
					." SET code = '".$_POST['course_code']."', "
					." name = '".$_POST['course_name']."', "
					." description  = '".$_POST['course_descr']."' "
					." WHERE id_course = '".$id_course."' ";
				sql_query($query_editon);
			}

			if ($has_classrooms) {
				$query_editon = "UPDATE %lms_course_date "
					." SET code = '".$_POST['course_code']."', "
					." name = '".$_POST['course_name']."', "
					." description  = '".$_POST['course_descr']."' "
					." WHERE id_course = '".$id_course."' ";
				sql_query($query_editon);
			}

		}

		$res = array();

		if($quota_exceeded)
			$res['limit_reach'] = 1;
        
        //AUTO SUBSCRIPTION
        $userId = Docebo::user()->getIdSt();
        $userSubscribed = $this->isUserSubscribedInCourse($userId, $id_course);
        if (intval($userSubscribed[0]) <= 0) {
            if (isset($_POST['auto_subscription']) && $_POST['auto_subscription'] == 1) {


                if (!$this->autoUserRegister($userId, $id_course)) {
                    die('Error during autosubscription');
                }
            }
        }
		$res['res'] = '_ok_course';

		return $res;
	}

	public function manageCourseFile($new_file_id, $old_file, $path, $quota_available, $delete_old, $is_image = false)
	{
		$arr_new_file = ( isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false);
		$return = array(	'filename' => $old_file,
							'new_size' => 0,
							'old_size' => 0,
							'error' => false,
							'quota_exceeded' => false);

		if(($delete_old || $arr_new_file !== false) && $old_file != '')
		{
			// the flag for file delete is checked or a new file was uploaded ---------------------
			$return['old_size'] = Get::file_size($GLOBALS['where_files_relative'].$path.$old_file);
			$quota_available -= $return['old_size'];
			sl_unlink($path.$old_file);
			$return['filename'] = '';
		}

		if(!empty($arr_new_file))
		{
			// if present load the new file --------------------------------------------------------
			$filename = $new_file_id.'_'.mt_rand(0, 100).'_'.time().'_'.$arr_new_file['name'];
			if($is_image) {

				$re = createImageFromTmp(	$arr_new_file['tmp_name'],
											$path.$filename,
											$arr_new_file['name'],
											150,
											150,
											true );

				if($re < 0) $return['error'] = true;
				else {

					// after resize check size ------------------------------------------------------------
					$size = Get::file_size($GLOBALS['where_files_relative'].$path.$filename);
					if($quota_available != 0 && $size > $quota_available)
					{
						$return['quota_exceeded'] = true;
						sl_unlink($path.$filename);
					}
					else
					{
						$return['new_size'] = $size;
						$return['filename'] = $filename;
					}
				}
			}
			else
			{
				// check if the filesize don't exceed the quota ----------------------------------------
				$size = Get::file_size($arr_new_file['tmp_name']);

				if($quota_available != 0 && $size > $quota_available)
					$return['quota_exceeded'] = true;
				else
				{
					// save file ---------------------------------------------------------------------------
					if(!sl_upload($arr_new_file['tmp_name'], $path.$filename))
						$return['error'] = true;
					else
					{
						$return['new_size'] = $size;
						$return['filename'] = $filename;
					}
				}
			}
		}
		return $return;
	}

	public function delCourse($id_course)
	{
		if ((int)$id_course <= 0) return false;

		require_once(_lms_.'/lib/lib.course.php');
		require_once(_base_.'/lib/lib.upload.php');

		$course_man = new Man_Course();

		//remove course subscribed------------------------------------------

		$levels =& $course_man->getCourseIdstGroupLevel($id_course);
		foreach($levels as $lv => $idst) {
			Docebo::aclm()->deleteGroup($idst);
		}

		$alluser = getIDGroupAlluser($id_course);
		Docebo::aclm()->deleteGroup($alluser);
		$course_man->removeCourseRole($id_course);
		$course_man->removeCourseMenu($id_course);

		$query = "DELETE FROM %lms_courseuser WHERE idCourse = '".(int)$id_course."'";
		$qres = sql_query($query);
		if (!$qres) return false;

		//--- remove course data ---------------------------------------------------

		$query_course = "SELECT imgSponsor, img_course, img_material, img_othermaterial, course_demo, course_type, has_editions
			FROM %lms_course
			WHERE idCourse = '".(int)$id_course."'";
		$qres = sql_query($query_course);
		list($file_sponsor, $file_logo, $file_material, $file_othermaterial, $file_demo, $course_type, $course_edition) = sql_fetch_row($qres);

		require_once(_base_.'/lib/lib.upload.php');

		$path = '/appLms/'.Get::sett('pathcourse');
		if( substr($path, -1) != '/' && substr($path, -1) != '\\') $path .= '/';
		sl_open_fileoperations();
		if($file_sponsor != '') 	sl_unlink($path.$file_sponsor);
		if($file_logo != '') 		sl_unlink($path.$file_logo);
		if($file_material != '') 	sl_unlink($path.$file_material);
		if($file_othermaterial != '') sl_unlink($path.$file_othermaterial);
		if($file_demo != '') 		sl_unlink($path.$file_demo);
		sl_close_fileoperations();

		//if the scs exist delete course rooms
		if ($GLOBALS['where_scs'] !== false) {
			require_once(_scs_.'/lib/lib.room.php');
			$re = deleteRoom(false, 'course', $id_course);
		}


		//--- delete classroom or editions -----------------------------------------
		if ($course_type == 'classroom')
		{
			require_once(_lms_.'/admin/model/ClassroomAlms.php');
			$classroom_model = new ClassroomAlms($id_course);

			$classroom = $classroom_model->classroom_man->getDateIdForCourse($id_course);

			foreach($classroom as $id_date)
				if(!$classroom_model->classroom_man->delDate($id_date))
					return false;
		}
		elseif ($course_edition == 1)
		{
			require_once(_lms_.'/admin/model/EditionAlms.php');
			$edition_model = new EditionAlms($id_course);

			$editions = $edition_model->classroom_man->getEditionIdFromCourse($id_course);

			foreach($editions as $id_edition)
				if(!$edition_model->edition_man->delEdition($id_edition))
					return false;
		}
		//--- end classrooms or editions -------------------------------------------



		//--- clear LOs ------------------------------------------------------------

		require_once(_lms_.'/lib/lib.module.php');
		require_once(_lms_.'/lib/lib.param.php');
		require_once(_lms_.'/class.module/track.object.php');

		$arr_lo_param = array();
		$arr_lo_track = array();
		$arr_org_access = array();

		$query = "SELECT * FROM %lms_organization WHERE idCourse = ".(int)$id_course;
		$ores = sql_query($query);
		while($obj = sql_fetch_object($ores)) {
			$deleted = true;
			if ($obj->idResource != 0 && $obj->objectType != "") {
				$lo = createLO(	$obj->objectType );
				$deleted = $lo->del($obj->idResource); //delete learning object
			}
			if ($deleted) {
				$arr_lo_track[] = $obj->idOrg;
				$arr_org_access[] = $obj->idOrg; //collect org access ids
				$arr_lo_param[] = $obj->idParam; //collect idParams ids
			}
		}

		//delete all organizations references for the course
		$query = "DELETE FROM %lms_organization WHERE idCourse = ".(int)$id_course;
		$res = sql_query($query);

		//delete LOs trackings
		if (!empty($arr_lo_track)) {
			$track_object = new Track_Object(false, 'course_lo');
			$track_object->delIdTrackFromCommon($arr_lo_track);
		}

		//delete org accesses
		if (!empty($arr_org_access)) {
			$query = "DELETE FROM %lms_organization_access
				WHERE idOrgAccess IN (".implode(",", $arr_org_access).")";
			$res = sql_query($query);
		}

		//delete lo params
		if (!empty($arr_lo_param)) {
			$query = "DELETE FROM %lms_lo_param
				WHERE idParam IN (".implode(",", $arr_lo_param).")";
		}

		//--- end LOs --------------------------------------------------------------


		
		
		//--- clear coursepath references ------------------------------------------
		require_once(_lms_.'/lib/lib.coursepath.php');
		$cman = new CoursePath_Manager();
		$cman->deleteCourseFromCoursePaths($id_course);
		//--- end coursepath references --------------------------------------------



		//--- clear certificates assignments ---------------------------------------
		require_once(_lms_.'/lib/lib.certificate.php');
		$cman = new Certificate();
		$cman->deleteCourseCertificateAssignments($id_course);
		//--- end certificates assignments -----------------------------------------



		//--- clear labels ---------------------------------------------------------
		$lmodel = new LabelAlms();
		$lmodel->clearCourseLabel($id_course);
		//--- end labels -----------------------------------------------------------



		//--- clear advices --------------------------------------------------------
		require_once(_lms_.'/lib/lib.advice.php');
		$aman = new Man_Advice();
		$aman->deleteAllCourseAdvices($id_course);
		//--- end advices ----------------------------------------------------------



		//--- clear coursereports --------------------------------------------------
		require_once(_lms_.'/lib/lib.coursereport.php');
		$cman = new CourseReportManager();
		$cman->deleteAllReports($id_course);
		//--- end coursereports ----------------------------------------------------



		//--- clear competences ----------------------------------------------------
		$cmodel = new CompetencesAdm();
		$cmodel->deleteAllCourseCompetences($id_course);
		//--- end competences ------------------------------------------------------



		//--- finally delete course from courses table -----------------------------
		if(!sql_query("DELETE FROM %lms_course WHERE idCourse = '".$id_course."'"))
			return false;
		return true;
	}

	public function hasEditionsOrClassrooms($id_course) {
		if($this->edition_man->getEditionNumber($id_course) > 0) return true;
		if($this->classroom_man->getDateNumber($id_course, true) > 0) return true;
		return false;
	}

	public function getInfo($id_course = false, $id_edition = false, $id_date = false) {
		$_id_course = ($id_course ? $id_course : $this->id_course);
		$_id_edition = ($id_edition ? $id_edition : $this->id_edition);
		$_id_date = ($id_date ? $id_date : $this->id_date);

		if (!$_id_course) return false;
		if (!$_id_edition && !$_id_date) return $this->course_man->getCourseInfo($_id_course);
		if ($_id_edition > 0) return $this->edition_man->getEditionInfo($_id_edition);
		if ($_id_date > 0) return $this->classroom_man->getDateInfo($_id_date);
		return false;
	}

	public function getCategoryForDropdown()
	{
		$query =	"SELECT idCategory, path, lev"
					." FROM %lms_category"
					." ORDER BY iLeft";

		$result = sql_query($query);
		$res = array('0' => 'root');

		while(list($id_cat, $path, $level) = sql_fetch_row($result))
		{
			$name = end(explode('/', $path));

			for($i = 0; $i < $level; $i++)
				$name = '&nbsp;&nbsp;'.$name;

			$res[$id_cat] = $name;
		}

		return $res;
	}

	public function getCategoryName($id_category)
	{
		if($id_category == 0)
			return 'root';

		$query =	"SELECT path"
					." FROM %lms_category"
					." WHERE idCategory = ".(int)$id_category;

		list($path) = sql_fetch_row(sql_query($query));

		return end(explode("/", $path));
	}

	public function getCourseWithCertificate()
	{
		$query =	"SELECT DISTINCT id_course"
					." FROM %lms_certificate_course";

		$result = sql_query($query);
		$res = array();

		while(list($id_course) = sql_fetch_row($result))
			$res[$id_course] = $id_course;

		return $res;
	}

	public function getCourseWithCompetence()
	{
		$query =	"SELECT DISTINCT id_course"
					." FROM %lms_competence_course";

		$result = sql_query($query);
		$res = array();

		while(list($id_course) = sql_fetch_row($result))
			$res[$id_course] = $id_course;

		return $res;
	}


	public function getCoursesStudentsNumber($courses) {
		if (is_numeric($courses)) $courses = array( (int)$courses );
		if (!is_array($courses) || empty($courses)) return false;

		$output = array();
		$query =	"SELECT idCourse"
				." FROM %lms_course"
				." WHERE course_type = 'elearning'"
				." AND course_edition = 0"
				." AND idCourse IN (".implode(",", $courses).")";
		$result = sql_query($query);
		while(list($id_course) = sql_fetch_row($result))
			$output[$id_course] = 0;

		$query = "SELECT idCourse, COUNT(*) FROM %lms_courseuser "
				." WHERE idCourse IN (".implode(",", $courses).") "
				." AND level = 3 AND waiting <= 0 "
				." GROUP BY idCourse";
		$res = sql_query($query);
		while (list($id_course, $count) = sql_fetch_row($res))
			$output[$id_course] = $count;

		return $output;
	}
    
    private function autoUserRegister($idMember, $idCourse) {
        
        $query = "SELECT idst FROM %adm_group WHERE groupid = ('/lms/course/".$idCourse."/subscribed/7')";
        $res = sql_query($query);
        
        $idst = sql_fetch_row($res);

        sql_query('START TRANSACTION');
        
        $query = "INSERT INTO %adm_group_members (idst, idstMember, filter) VALUES ('".$idst[0]."','".$idMember."','')";

        if(sql_query($query)){

           $row = $this->isUserSubscribedInCourse($idMember, $idCourse);

           if($row[0] == 0){
               $query = "INSERT INTO %lms_courseuser (idUser, idCourse, level, waiting, subscribed_by, date_inscr) VALUES ('".$idMember."', '".$idCourse."', '7', '0', '".$idMember."', 'now()')";
           
               if(sql_query($query)) {
                   sql_query('COMMIT');
                   return true;
               }
           }
           else{
               sql_query('ROLLBACK');
               return false;
           }
        }
        return false;
    }
    
    public function isUserSubscribedInCourse($idMember,$idCourse) {
        $query = "SELECT COUNT(*) FROM %lms_courseuser WHERE idUser = ".$idMember." AND idCourse = ".$idCourse;
           
        $res = sql_query($query);
           
        $row = sql_fetch_row($res);
        
        return $row;
    }    

}
?>