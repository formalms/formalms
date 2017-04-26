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

function coursecatalogue($id_block, $title, $option = array())
{
	YuiLib::load(array('animation' => 'my_animation', 'container' => 'container-min', 'container' => 'container_core-min'));


	if(!isset($_SESSION['chart']))
		$_SESSION['chart'] = array();

	$id_course = Get::req('id_course', DOTY_INT, 0);
	$action = Get::req('action', DOTY_STRING, '');

	if($id_course !== 0 && $action === '')
		$_SESSION['chart'][$id_course]['idCourse'] = $id_course;
	else
	{
		require_once(_lms_.'/lib/lib.subscribe.php');

		$man_subscribe = new CourseSubscribe_Management();

		$man_subscribe->subscribeToCourse(getLogUserId(), $id_course);
	}


	require_once(_base_.'/lib/lib.navbar.php');

	cout('<link href="./modules/catalog/catalog.css" type="text/css" rel="stylesheet"/>', 'page_head');

	$css_path = Get::tmpl_path('base').'yui-skin';
	cout(Util::get_css($css_path.'/tabview.css', true), 'page_head');

	$lang =& DoceboLanguage::CreateInstance('catalogue', 'cms');

	$man_cat = new Man_Catalog();

	$array_id_block = explode('_', $id_block);

	$page = Get::req('page', DOTY_INT, 0);
	if($page == 0)
		$page = $this->getBlockPage($array_id_block[0]);

	cout(	getTitleArea($title)
			.'<div class="std_block">');

	$catalogue = $man_cat->getCatalogueForBlock($array_id_block[0]);

	$all_course = false;
	if(array_search(0, $catalogue) !== false)
	{
		unset($catalogue[array_search(0, $catalogue)]);
		$all_course = true;
	}

	$id_catalogue = Get::req('id_catalogue', DOTY_INT, -2);
	if($id_catalogue == -2)
	{
		if(count($catalogue))
		{
			if(isset($catalogue[0]))
				$id_catalogue = $catalogue[0]['id_catalogue'];
			else
				$id_catalogue = $catalogue[1]['id_catalogue'];
		}
		else
			$id_catalogue = 0;
	}

	$id_category = Get::req('id_cat', DOTY_INT, 0);

	$number_courses = $man_cat->getCourseNumberForBlock($array_id_block[0], $id_catalogue, $id_category, $all_course);

	$nav_bar = new NavBar('ini', Get::sett('visuItem'), $number_courses);
	$nav_bar->setLink('index.php?pag='.$page.'&amp;id_catalogue='.$id_catalogue.($id_category != 0 ? '&amp;id_cat='.$id_category : ''));

	$ini = $nav_bar->getSelectedPage();
	$limit = ($ini - 1) * Get::sett('visuItem');

	cout(	$nav_bar->getNavBar()
			.'<div id="demo" class="yui-navset">'
			.'<ul class="yui-nav">');

	foreach($catalogue as $catalogue_info)
		cout('<li '.($catalogue_info['id_catalogue'] == $id_catalogue ? ' class="selected"' : '').'><a href="index.php?pag='.$page.'&amp;id_catalogue='.$catalogue_info['id_catalogue'].'"><em>'.$catalogue_info['name'].'</em></a></li>');

	cout(	'<li '.($id_catalogue == 0 ? ' class="selected"' : '').'><a href="index.php?pag='.$page.'&amp;id_catalogue=0"><em>'.$lang->def('_ALL_COURSES').'</em></a></li>'
			.'<li '.($id_catalogue == -1 ? ' class="selected"' : '').'><a href="index.php?pag='.$page.'&amp;id_catalogue=-1"><em>'.$lang->def('_CATEGORY').'</em></a></li>');

	reset($catalogue);

	cout('</ul>');

	cout('<div class="yui-content">');

	if($number_courses == 0 && $id_catalogue != -1)
		cout('<p>'.$lang->def('_NO_COURSE_FOR_CATALOG').'</p>');
	elseif($id_catalogue == -1)
	{
		//Category Visualization
		if($id_category == 0)
			$query =	"SELECT ca.idCategory, ca.path, ca.lev, ca.iLeft, ca.iRight, COUNT(co.idCourse)"
						." FROM ".$GLOBALS['prefix_lms']."_category AS ca"
						." LEFT JOIN ".$GLOBALS['prefix_lms']."_course AS co ON co.idCategory = ca.idCategory"
						." WHERE ca.lev = 1"
						." GROUP BY ca.idCategory"
						." ORDER BY ca.lev, ca.path";
		else
		{
			$query =	"SELECT iLeft, iRight"
						." FROM ".$GLOBALS['prefix_lms']."_category"
						." WHERE idCategory = ".$id_category;

			list($ileft, $iright) = sql_fetch_row(sql_query($query));

			$query =	"SELECT ca.idCategory, ca.path, ca.lev, ca.iLeft, ca.iRight, COUNT(co.idCourse)"
						." FROM ".$GLOBALS['prefix_lms']."_category AS ca"
						." LEFT JOIN ".$GLOBALS['prefix_lms']."_course AS co ON co.idCategory = ca.idCategory"
						." WHERE ca.iLeft >= ".$ileft
						." AND ca.iRight <= ".$iright
						." GROUP BY ca.idCategory"
						." ORDER BY ca.lev, ca.path";
		}

		$result = sql_query($query);
		$first = true;
		$num_folder = sql_num_rows($result);

		cout('<div class="cat_nav">');

		while(list($id_cat, $path, $lev, $ileft, $iright, $courses) = sql_fetch_row($result))
		{
			$cat_name = end(explode('/', $path));

			if($id_category == $id_cat)
			{
				cout(	'<div class="cat_position">'
						.'<a href="index.php?pag='.$page.'&id_catalogue=-1">'.$lang->def('_CATEGORY').'</a>');

				if($lev > 1)
				{
					$query_parent =	"SELECT idCategory, path"
									." FROM ".$GLOBALS['prefix_lms']."_category"
									." WHERE iLeft < ".$ileft
									." AND iRight > ".$iright
									." ORDER BY lev ASC";

					$result_parent = sql_query($query_parent);

					while(list($id_cat_pa, $path_pa) = sql_fetch_row($result_parent))
					{
						$pa_name = end(explode('/', $path_pa));

						cout(' - <a href="index.php?pag='.$page.'&id_catalogue=-1&amp;id_cat='.$id_cat_pa.'">'.$pa_name.'</a>');
					}
				}

				cout(	' - '.$cat_name
						.'</div>');
			}

			if($first && $num_folder > 0)
			{
				$first = false;
				cout('<ul class="cat_nav_list">');
			}

			if($id_category != $id_cat)
			{
				$sub_folder = ($iright - $ileft - 1) / 2;

				cout(	'<li>'
						.'<a href="index.php?pag='.$page.'&id_catalogue=-1&amp;id_cat='.$id_cat.'">'.$cat_name.'<br />'
						.($sub_folder > 0 ? '<span class="cat_item_info">'.$lang->def("_CATEGORIES").' ('.$sub_folder.')<br />' : '')
						.($courses > 0 ? '<span class="cat_item_info">'.$lang->def("_COURSES").' ('.$courses.')' : '')
						.'</a>'
						.'</li>');
			}
		}

		if($num_folder > 0)
			cout('</ul>');

		cout('</div>');

		//Course visualization
		cout('<div class="course_list">');
		if($number_courses == 0)
			cout('<p>'.$lang->def('_NO_COURSE_FOR_CATEGORY').'</p>');
		else
		{
			$courses = $man_cat->getCourseForBlock($array_id_block[0], $limit, $id_catalogue, $id_category, $all_course);

			foreach($courses as $course_info)
			{
				$action = $this->controlCourse($course_info, $page, $id_catalogue, $id_category, $ini);

				cout(	'<div class="course">'
						.'<div class="course_info">'
						.'<h3 class="course_title"><img src="'.getPathImage().'/language/'.strtolower($course_info['lang_code']).'.png" alt="" title="" /> '.$course_info['name'].'</h3>'
						.'<p class="course_description">'.$course_info['description'].'</p>'
						.'</div>'
						.'<div class="course_action">'.$action.'</div>'
						.'</div>');
			}
		}

		cout('</div>');
	}
	else
	{
		//Course in catalogue visualization
		$courses = $man_cat->getCourseForBlock($array_id_block[0], $limit, $id_catalogue, $id_category, $all_course);

		foreach($courses as $course_info)
		{
			$action = $this->controlCourse($course_info, $page, $id_catalogue, $id_category, $ini);

			cout(	'<div class="course">'
					.'<div class="course_info">'
					.'<h3 class="course_title"><img src="'.getPathImage().'/language/'.strtolower($course_info['lang_code']).'.png" alt="" title="" /> '.$course_info['name'].'</h3>'
					.'<p class="course_description">'.$course_info['description'].'</p>'
					.'</div>'
					.'<div class="course_action">'.$action.'</div>'
					.'</div>');
		}
	}

	cout(	'</div>'
			.'</div>'
			.$nav_bar->getNavBar()
			.'</div>');
}

function controlCourse($course_info, $page, $id_catalogue, $id_category, $ini)
{
	require_once(_lms_.'/lib/lib.course.php');

	$acl_manger = Docebo::user()->getAclManager();

	$lang =& DoceboLanguage::CreateInstance('catalogue', 'cms');

	if($course_info['course_type'] !== 'elearning')
	{
		if(!isset($course_info['dates']))
			return '<p class="cannot_subscribe">'.$lang->def('_NO_EDITIONS').'</p>';
		elseif(count($course_info['dates']) == 0)
			return '<p class="cannot_subscribe">'.$lang->def('_NO_EDITIONS').'</p>';

		require_once(_lms_.'/lib/lib.date.php');
		$man_date = new DateManager();

		$user_date = $man_date->getUserDates(getLogUserId());
		$date_id = array();
		$date_full = $man_date->getFullDateForCourse($course_info['idCourse']);
		$date_not_confirmed = $man_date->getNotConfirmetDateForCourse($course_info['idCourse']);

		foreach($course_info['dates'] as $date_info)
			$date_id[] = $date_info['id_date'];

		reset($course_info['dates']);

		$control = array_diff($date_id, $user_date, $date_full, $date_not_confirmed);

		if(count($control) == 0)
			return '<p class="cannot_subscribe">'.$lang->def('_NO_EDITIONS').'</p>';

		if($course_info['selling'] == 0)
		{
			if(Docebo::user()->isAnonymous())
				return '<p class="cannot_subscribe">'.$lang->def('_NEED_TO_LOGIN').'</p>';
			else
				return '<a href="javascript:;" onClick="datePrenotationPopUp(\''.$course_info['idCourse'].'\', \''.$lang->def('_CHART_EDITION_FOR').' : '.addslashes($course_info['name']).'\')"><p class="can_subscribe">'.$lang->def('_CAN_SUBSCRIBE').'</p></a>';
		}
		else
		{
			$date_in_chart = array();

			if(isset($_SESSION['chart'][$course_info['idCourse']]))
				$date_in_chart = $_SESSION['chart'][$course_info['idCourse']]['dates'];

			$control = array_diff($control, $date_in_chart);

			if(count($control) == 0)
				return '<p class="subscribed">'.$lang->def('_ALL_EDITION_BUYED').'</p>';

			$query =	"SELECT id_date"
						." FROM ".$GLOBALS['prefix_lms']."_transaction_info"
						." WHERE id_course = ".$course_info['idCourse']
						." AND id_transaction IN"
						." ("
						." SELECT id_transaction"
						." FROM ".$GLOBALS['prefix_lms']."_transaction"
						." WHERE id_user = ".getLogUserId()
						." AND status = 0"
						." )";

			$res = sql_query($query);

			if(sql_num_rows($res))
			{
				$waiting_payment = array();

				while(list($id_date) = sql_fetch_row($query))
					$waiting_payment[$id_date] = $id_date;

				$control = array_diff($control, $waiting_payment);

				if(count($control) == 0)
					return '<p class="subscribed">'.$lang->def('_WAITING_PAYMENT_FOL_LAST_EDITION').'</p>';
			}

			return '<a href="javascript:;" onClick="datePrenotationPopUp(\''.$course_info['idCourse'].'\', \''.$lang->def('_CHART_EDITION_FOR').' : '.addslashes($course_info['name']).'\')"><p class="can_subscribe">'.$lang->def('_CAN_SUBSCRIBE').'</p></a>';
		}
	}
	else
	{
		$course_in_chart = array_keys($_SESSION['chart']);

		if(array_search($course_info['idCourse'], $course_in_chart) !== false)
			return '<p class="subscribed">'.$lang->def('_COURSE_IN_CART').'</p>';

		$query =	"SELECT status, waiting"
					." FROM ".$GLOBALS['prefix_lms']."_courseuser"
					." WHERE idCourse = ".$course_info['idCourse']
					." AND idUser = ".getLogUserId();

		$result = sql_query($query);

		if(sql_num_rows($result) > 0)
		{
			list($status, $waiting) = sql_fetch_row($result);

			if($waiting)
				return '<p class="subscribed">'.$lang->def('_WAITING').'</p>';
			else
				return '<p class="subscribed">'.$lang->def('_USER_STATUS_SUBS').'</p>';

		}

		if($course_info['max_num_subscribe'] !== 0)
		{
			$query =	"SELECT COUNT(*)"
						." FROM ".$GLOBALS['prefix_lms']."courseuser"
						." WHERE idCourse = ".$course_info['idCourse'];

			list($control) = sql_fetch_row(sql_query($query));

			if($control >= $course_info['max_num_subscribe'])
				return '<p class="cannot_subscribe">'.$lang->def('_MAX_NUM_SUBSCRIBE').'</p>';
		}

		if($course_info['selling'] == 0)
		{
			if(Docebo::user()->isAnonymous())
				return '<p class="cannot_subscribe">'.$lang->def('_NEED_TO_LOGIN').'</p>';
			else
				return '<a href="index.php?pag='.$page.'&amp;id_catalogue='.$id_catalogue.($id_category != 0 ? '&amp;id_cat='.$id_category : '').'&amp;id_course='.$course_info['idCourse'].'&amp;ini='.$ini.'&amp;action=subscribe"><p class="can_subscribe">'.$lang->def('_CAN_SUBSCRIBE').'</p></a>';
		}
		else
		{
			$query =	"SELECT COUNT(*)"
						." FROM ".$GLOBALS['prefix_lms']."_transaction_info"
						." WHERE id_date = 0"
						." AND id_course = ".$course_info['idCourse']
						." AND id_transaction IN"
						." ("
						." SELECT id_transaction"
						." FROM ".$GLOBALS['prefix_lms']."_transaction"
						." WHERE id_user = ".getLogUserId()
						." AND status = 0"
						." )";

			list($control) = sql_fetch_row(sql_query($query));

			if($control > 0)
				return '<p class="subscribed">'.$lang->def('_WAITING_PAYMENT').'</p>';

			return '<a href="index.php?pag='.$page.'&amp;id_catalogue='.$id_catalogue.($id_category != 0 ? '&amp;id_cat='.$id_category : '').'&amp;id_course='.$course_info['idCourse'].'&amp;ini='.$ini.'"><p class="can_subscribe">'.$lang->def('_CAN_SUBSCRIBE').'</p></a>';
		}
	}
}

function subscribeToCourse($id_user, $id_course, $id_date = 0)
{
	require_once (_lms_.'/lib/lib.subscribe.php');
	//require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
	require_once (_lms_.'/lib/lib.date.php');
	require_once (_lms_.'/lib/lib.course.php');

	$subscribe_man = new CourseSubscribe_Management();
	$date_man = new DateManager();
	$acl_man =& Docebo::user()->getAclManager();

	$query =	"SELECT idCourse"
				." FROM ".$this->table_courseuser
				." WHERE idUser = ".$id_user;

	$result = sql_query($query);
	$courses = array();

	while(list($id_c) = sql_fetch_row($result))
		$courses[$id_c] = $id_c;

	$dates = $date_man->getUserDates($id_user);

	$docebo_course = new DoceboCourse($id_course);

	$level_idst =& $docebo_course->getCourseLevel($id_course);

	if(count($level_idst) == 0)
		$level_idst =& $docebo_course->createCourseLevel($id_course);

	$waiting = 0;
	if($subscribe_method == '1')
		$waiting = 1;

	if($id_date != 0)
	{
		if(array_search($id_course, $courses) !== false)
		{
			if(array_search($id_date, $dates) === false)
			{
				if(!$date_man->addUserToDate($id_date, $id_user, getLogUserId()))
					return false;
			}
		}
		else
		{
			$acl_man->addToGroup($level_idst[3], $id_user);

			$re = sql_query(	"INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
								(idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)
								VALUES ('".$id_user."', '".$id_course."', '0', '3', '".$waiting."', '".getLogUserId()."', '".date("Y-m-d H:i:s")."')");

			if($re)
			{
				addUserToTimeTable($id_user, $id_course, 0);

				if(!$date_man->addUserToDate($id_date, $id_user, getLogUserId()))
					return false;
			}
		}
	}
	else
	{
		if(array_search($id_course, $courses) === false)
		{
			$acl_man->addToGroup($level_idst[3], $id_user);

			$re = sql_query(	"INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
								(idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)
								VALUES ('".$id_user."', '".$id_course."', '0', '3', '".$waiting."', '".getLogUserId()."', '".date("Y-m-d H:i:s")."')");
			if($re)
				addUserToTimeTable($id_user, $id_course, 0);
		}
	}

	return true;
}

// Course catalogue function dispatcher -----------------------------------------------------

function coursecatalogueDispatch($op)
{
	switch($op)
	{
		case 'coursecatalogue':
		default:
			coursecatalogue();
		break;
	}
}

?>