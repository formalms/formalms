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
 * @version  $Id:$
 * @author	 Fabio Pirovano <fabio [at] docebo-com>
 * @package course
 */

function displayCourseList(&$url, $order_type) {

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.user_profile.php');
	require_once(_base_.'/lib/lib.navbar.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS["where_framework"]."/lib/lib.ajax_comment.php");

	require_once($GLOBALS['where_lms'].'/lib/lib.classroom.php');

	// cahce classroom
	$classroom_man 	= new ClassroomManager();
	$classrooms = $classroom_man->getClassroomNameList();

	$lang 	=& DoceboLanguage::createInstance('catalogue');
	$lang_c =& DoceboLanguage::createInstance('course');

	$nav_bar 		= new NavBar('ini', Get::sett('visuItem'), 0);
	$man_course 	= new Man_Course();

	$id_parent = importVar('id_parent', false, 0);

	$nav_url = ( $id_parent != 0 ? $url->getUrl('id_parent='.$id_parent) : $url->getUrl() );
	$nav_bar->setLink($nav_url);
	$ini = $nav_bar->getSelectedElement();

	$profile = new UserProfile( getLogUserId() );
	$profile->init('profile', 'framework', '', 'ap');
	$profile->addStyleSheet('lms');
	
	// searching courses
	$use_category = ($order_type == 'category');

	$select_course = ""
	." SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.lang_code, c.difficult, "
	."	c.subscribe_method, c.date_begin, c.date_end, c.max_num_subscribe, "
	."	c.selling, c.prize, c.create_date, c.status AS course_status, c.course_edition, "
	."	c.classrooms, c.img_material, c.course_demo, c.course_vote, COUNT(*) as enrolled, "
	."	c.can_subscribe, c.sub_start_date, c.sub_end_date, c.allow_overbooking, c.max_num_subscribe, c.min_num_subscribe, c.direct_play, "
	."	c.valid_time, c.userStatusOp, u.level, u.date_inscr, u.date_first_access, u.date_complete, u.status AS user_status, u.waiting, c.advance ";

	$from_course = " FROM ".$GLOBALS['prefix_lms']."_course AS c "
	."	LEFT JOIN ".$GLOBALS['prefix_lms']."_courseuser AS u "
	."		ON ( c.idCourse = u.idCourse ) ";
	$where_course 		= " c.status <> '".CST_PREPARATION."' ";
	
	if(Get::sett('catalogue_hide_ended') == 'on')
	{
		$where_course .=	" AND ( c.date_end = '0000-00-00'"
							." OR c.date_end > '".date('Y-m-d')."' ) ";
	}
	
	$group_by_course	= " GROUP BY c.idCourse ";
	switch($order_type) {
		case "mostscore" : $order_course = " ORDER BY c.course_vote DESC ";break;
		case "popular" : $order_course = " ORDER BY enrolled DESC ";break;
		case "recent" : $order_course = " ORDER BY c.create_date DESC ";break;
		default : $order_course = " ORDER BY c.name ";
	}
	$limit_course 		= " LIMIT ".$ini.", ".Get::sett('visuItem');
	$where_course .= " AND c.course_type <> 'assessment'";

	if(Docebo::user()->isAnonymous()) $where_course .= " AND c.show_rules = 0";
	else $where_course .= " AND c.show_rules  <> 2";

	// maybe a must apply some filter to remove from the list some courses --------------
	$cat_man 		= new Catalogue_Manager();
	$catalogues 	=& $cat_man->getUserAllCatalogueId( getLogUserId() );

	// at least one catalogue is assigned to this user
	if(!empty($catalogues)) {

		$cat_courses = $cat_man->getAllCourseOfUser( getLogUserId() );
		if(empty($cat_courses)) $where_course .= " AND 0 ";
		else  $where_course .= " AND c.idCourse IN ( ".implode(',', $cat_courses)." ) ";

	} elseif(Get::sett('on_catalogue_empty') == 'off') {
		$where_course .= " AND 0 ";
	}

	if(!Docebo::user()->isAnonymous()) {

		if(!isset($_SESSION['cp_assessment_effect'])) {

			$pa_man = new AssessmentList();
			$arr_assessment = $pa_man->getUserAssessmentSubsription(Docebo::user()->getArrSt());

			$report = new CourseReportManager();
			$user_result = $report->getAllUserFinalScore(getLogUserId(), $arr_assessment['course_list']);

			$rule_man = new AssessmentRule();
			$ass_elem = $rule_man->getCompleteEffectListForAssessmentWithUserResult($arr_assessment['course_list'], $user_result);
			$_SESSION['cp_assessment_effect'] = urlencode(Util::serialize($ass_elem));
		} else {

			$ass_elem = Util::unserialize(urldecode($_SESSION['cp_assessment_effect']));
		}
		if(!empty($ass_elem['parsed']['course'])) {

			$where_course = " ( ( ".$where_course." ) OR c.idCourse IN (".implode(',', $ass_elem['parsed']['course']).") ) ";
		}
	}

	// apply search filter --------------------------------------------------------------

	$s_searched = get_searched('simple_search', '');
	$filter_lang = get_searched('filter_lang', 'all');

	$filter_date_begin = get_searched('filter_date_begin', '');
	if($filter_date_begin != '') $filter_date_begin = Format::dateDb($filter_date_begin, 'date').' 00:00:00';

	$filter_date_end = get_searched('filter_date_end', '');
	if($filter_date_end != '') $filter_date_end = Format::dateDb($filter_date_end, 'date').' 00:00:00';

	$all_lang = Docebo::langManager()->getAllLangCode();

	if(must_search_filter()) {

		if(trim($s_searched) != '') {
			$where_course .= " AND ( c.code LIKE '%".$s_searched."%' "
						." OR c.name LIKE '%".$s_searched."%' "
						." OR c.description LIKE '%".$s_searched."%' ) ";
		}
		if($filter_lang != 'all') { $where_course .= " AND c.lang_code = '".$all_lang[$filter_lang]."' "; }
		if($filter_date_begin != '') { $where_course .= " AND ( c.date_begin >= '".$filter_date_begin."' OR c.course_edition = 1 ) ";  }
		if($filter_date_end != '') { $where_course .= " AND ( c.date_end <= '".$filter_date_end."' OR c.course_edition = 1 ) "; }

	} 
	if($use_category) $where_course .= " AND c.idCategory = '".(int)$id_parent."'";

	$re_course = sql_query($select_course.$from_course." WHERE ".$where_course.$group_by_course
		.$order_course.$limit_course);
	
	list($course_number) = sql_fetch_row(sql_query("SELECT COUNT(*) "
		." FROM ".$GLOBALS['prefix_lms']."_course AS c "
		." WHERE ".$where_course ));
	$nav_bar->setElementTotal($course_number);

	// retrive editions ----------------------------------------------------------------
	$select_edition = " SELECT e.* ";
	$from_edition 	= " FROM ".$GLOBALS["prefix_lms"]."_course_edition AS e";
	$where_edition 	= " WHERE e.status <> '".CST_PREPARATION."' ";

	$where_edition 	.= " AND (e.date_begin > '".date("Y-m-d H:i:s")."' OR e.date_begin = '0000-00-00 00:00:00')";

	$order_edition 	= " ORDER BY date_begin ";

	if(must_search_filter()) {

		if($filter_date_begin != '') { $where_edition .= " AND date_begin >= '".$filter_date_begin."' "; }
		if($filter_date_end != '') { $where_edition .= " AND date_end <= '".$filter_date_end."' "; }
	}

	$re_edition = sql_query($select_edition.$from_edition.$where_edition.$order_edition);
	$editions = array();
	if($re_edition)
	while($edition_elem = sql_fetch_assoc($re_edition)) {

		$edition_elem['classrooms'] = ( isset($classrooms[$edition_elem['classrooms']]) ? $classrooms[$edition_elem['classrooms']] : '' );
		$edition_elem['waiting'] = 0;
		$edition_elem['user_count'] = 0;
		$edition_elem['theacher_list'] = getSubscribed($edition_elem["idCourse"], false, 6, true, $edition_elem["idCourseEdition"]);
		$editions[$edition_elem["idCourse"]][$edition_elem["idCourseEdition"]] = $edition_elem;
	}

	// retrive editions subscribed -----------------------------------------------------
	$select_ed_count 	= "SELECT u.idCourse, u.edition_id, sum(u.waiting) as waiting, COUNT(*) as user_count ";
	$from_ed_count 		= " FROM ".$GLOBALS["prefix_lms"]."_courseuser AS u";
	$where_ed_count 	= " WHERE u.edition_id <> 0 " .
			" AND u.level = '3'" .
			" AND u.status IN ('"._CUS_CONFIRMED."', '"._CUS_SUBSCRIBED."', '"._CUS_BEGIN."', '"._CUS_END."', '"._CUS_SUSPEND."', '"._CUS_WAITING_LIST."')" .
			" AND u.absent = '0'";
	$group_ed_count 	= "GROUP BY u.edition_id ";
	$re_ed_count = sql_query($select_ed_count.$from_ed_count.$where_ed_count.$group_ed_count );
	if($re_ed_count)
	while($ed_count_elem = sql_fetch_assoc($re_ed_count)) {

		if(isset($editions[$ed_count_elem["idCourse"]][$ed_count_elem["edition_id"]])) {
			$editions[$ed_count_elem["idCourse"]][$ed_count_elem["edition_id"]]['waiting'] = $ed_count_elem['waiting'];
			$editions[$ed_count_elem["idCourse"]][$ed_count_elem["edition_id"]]['user_count'] = $ed_count_elem['user_count'];
		}
	}

	// retrive course subscription -----------------------------------------------------
	$man_courseuser = new Man_CourseUser();
	$usercourses = $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), false);
	$user_score = $man_courseuser->getUserCourseScored(getLogUserId());
	
	require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
	$first_is_scorm = OrganizationManagement::objectFilter(array_keys($usercourses), 'scormorg');

	// load search form ----------------------------------------------------------------

	$GLOBALS['page']->add(searchForm($url, $lang), 'content');

	if($use_category && !must_search_filter()) {

		// show category selection -----------------------------------------------------
		$descendant = $man_course->getCategoryCourseAndSonCount();
		$GLOBALS['page']->add(
			'<p class="category_path">'
				.'<b>'.$lang->def('_CATEGORY_PATH', 'course').' :</b> '
			.$man_course->getCategoryPath(	$id_parent,
											$lang->def('_MAIN_CATEGORY', 'course'),
											$lang->def('_TITLE_CATEGORY_JUMP', 'course'),
											$url->getUrl(),
											'id_parent' )
			.'</p>'
		, 'content');

		$categories =& $man_course->getCategoriesInfo($id_parent);
		if(!empty($categories)) {

			$GLOBALS['page']->add('<ul class="category_list">', 'content');
			while(list($id_cat, $cat) = each($categories)) {

				$GLOBALS['page']->add('<li'.( !isset($descendant[$id_cat])  ? ' class="empty_folder"' : '' ).'>'
					.'<a href="'.$url->getUrl('id_parent='.$id_cat).'">'.$cat['name'].'<br />'
					.'<b>'.str_replace(	array('[course]', '[category]'),
										array(	( isset($descendant[$id_cat]['course']) ? $descendant[$id_cat]['course'] : 0 ),
												( isset($descendant[$id_cat]['category']) ? $descendant[$id_cat]['category'] : 0 )	),
										$lang->def('_COURSE_CONTENT', 'course')).'</b>'
					.'</a></li>', 'content');
			}
			$GLOBALS['page']->add(
				'</ul>'
				.'<div class="nofloat"></div>', 'content');
		}
	}
	if(!$re_course || !sql_num_rows($re_course)) {

		// no course found for the criteria --------------------------------------------
		$GLOBALS['page']->add(
			'<p class="no_course_found">'.$lang->def('_NO_COURSE_FOUND').'</p>'
			.'</div>', 'content');
		return;
	}

	$ax_comm = new AjaxComment('course', 'lms');
	$comment_count = $ax_comm->getResourceCommentCount();

	$GLOBALS['page']->add($nav_bar->getNavBar($ini), 'content');

	$i = 0;
	$direct_play = false;
	while($cinfo = sql_fetch_assoc($re_course)) {

		if(must_search_filter()) {

			$s_searched = get_searched('simple_search', '');
			if($s_searched != '') {

				$cinfo['code'] = preg_replace("/".$s_searched."/i", '<b class="filter_evidence">'.$s_searched.'</b>', $cinfo['code']);
				$cinfo['name'] = preg_replace("/".$s_searched."/i", '<b class="filter_evidence">'.$s_searched.'</b>', $cinfo['name']);
				$cinfo['description'] = preg_replace("/".$s_searched."/i", '<b class="filter_evidence">'.$s_searched.'</b>', $cinfo['description']);
			}
		}
		$cinfo['theacher_list'] 	= getSubscribed($cinfo['idCourse'], false, 6, true);
		$cinfo['edition_list'] 		= ( isset($editions[$cinfo['idCourse']]) ? $editions[$cinfo['idCourse']] : array() );
		$cinfo['edition_available'] = count($cinfo['edition_list']);
		$cinfo['user_score'] 		= ( isset($user_score[$cinfo['idCourse']]) ? $user_score[$cinfo['idCourse']] : NULL );
		$cinfo['classrooms'] 		= ( isset($classrooms[$cinfo['classrooms']]) ? $classrooms[$cinfo['classrooms']] : '' );
		if(isset($first_is_scorm[$cinfo['idCourse']])) $cinfo['first_is_scorm'] = $first_is_scorm[$cinfo['idCourse']];
		else $cinfo['first_is_scorm'] = false;
		
		if(isset($comment_count[$cinfo['idCourse']])) $cinfo['comment_count'] = $comment_count[$cinfo['idCourse']];

		$view = true;
		if(must_search_filter()) {

			if($cinfo['course_edition'] == 1 && empty($cinfo['edition_list'])) {
				$view = false;
			}
		}
		if($view) $GLOBALS['page']->add(dashcourse(	$url,
										$lang_c,
										$cinfo,
										( isset($usercourses[$cinfo['idCourse']]) ? $usercourses[$cinfo['idCourse']] : false ),
										$i++), 'content');
		if($cinfo['direct_play'] == 1)$direct_play = true;
	}
	if($direct_play) {
		$GLOBALS['page']->add( ''
		
		.'	<link href="'.getPathTemplate().'/style/shadowbox.css" rel="stylesheet" type="text/css" />'
		
		.'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/shadowbox/shadowbox-yui.js"></script>'."\n"
		.'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/shadowbox/shadowbox.js"></script>'."\n", 'page_head');
		
		$GLOBALS['page']->add( '<script type="text/javascript">
	
		YAHOO.util.Event.onDOMReady(function() {
			var options = { listenOverlay:false, overlayOpacity:"0.8", 
				loadingImage:"'.getPathImage('lms').'standard/loading.gif", overlayBgImage:"'.getPathImage('lms').'standard/overlay-85.png", 
				text: {close: "'. Lang::t('_CLOSE').'", cancel: "'. Lang::t('_UNDO').'", loading:"'. Lang::t('_LOADING').'" },
				onOpen: function (gallery) { window.onbeforeunload = function() { return "'. Lang::t('_CONFIRM_EXIT', 'organization', 'lms').'"; } }
		    }; 
			Shadowbox.init(options); 
			Shadowbox.close = function() {
				window.onbeforeunload = null;
				window.frames[\'shadowbox_content\'].uiPlayer.closePlayer(true, window);
			}
		});
		</script>' );
	}
	$GLOBALS['page']->add($nav_bar->getNavBar($ini), 'content');
}

function displayCoursePathList(&$url, $selected_tab) {

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.user_profile.php');
	require_once(_base_.'/lib/lib.navbar.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');

	$lang 	=& DoceboLanguage::createInstance('catalogue');
	$lang_c =& DoceboLanguage::createInstance('course');

	$nav_bar = new NavBar('ini', Get::sett('visuItem'), 0);
	$nav_bar->setLink($url->getUrl());
	$ini = $nav_bar->getSelectedElement();

	$course_man 	= new Man_Course();
	$path_man 		= new CoursePath_Manager();
	$cat_man 		= new Catalogue_Manager();
	$man_courseuser = new Man_CourseUser();


	$profile = new UserProfile( getLogUserId() );
	$profile->init('profile', 'framework', '', 'ap');
	$profile->addStyleSheet('lms');

	$catalogues 	=& $cat_man->getUserAllCatalogueId( getLogUserId() );

	if(!empty($catalogues)) {

		// at least one catalogue is assigned to this user
		$cat_path =& $cat_man->getAllCoursepathOfUser( getLogUserId() );
		if(!empty($cat_path)) $path_man->filterInPath($cat_path);
	} elseif(Get::sett('on_catalogue_empty') == 'off') {

		$path_man->filterInPath(array(0));
	}

	if(!Docebo::user()->isAnonymous()) {

		if(!isset($_SESSION['cp_assessment_effect'])) {

			$pa_man = new AssessmentList();
			$arr_assessment = $pa_man->getUserAssessmentSubsription(Docebo::user()->getArrSt());

			$report = new CourseReportManager();
			$user_result = $report->getAllUserFinalScore(getLogUserId(), $arr_assessment['course_list']);

			$rule_man = new AssessmentRule();
			$ass_elem = $rule_man->getCompleteEffectListForAssessmentWithUserResult($arr_assessment['course_list'], $user_result);
			$_SESSION['cp_assessment_effect'] = urlencode(Util::serialize($ass_elem));
		} else {
			$ass_elem = Util::unserialize(urldecode($_SESSION['cp_assessment_effect']));
		}
		if(!empty($ass_elem['parsed']['coursepath'])) {
			$path_man->filterOrInPath($ass_elem['parsed']['coursepath']);
		}
	}

	// retrive all the classroorm

	// search for the coursepath ------------------------------------------------------
	$coursepath = $path_man->getCoursepathList($ini, Get::sett('visuItem'));
	if(empty($coursepath)) {
		// no path found for the criteria ---------------------------------------------
		$GLOBALS['page']->add('<p class="no_course_found">'.$lang->def('_NO_COURSE_FOUND').'</p></div>', 'content');
		return;
	}
	// find structures of the course path ---------------------------------------------
	$courses 		= $path_man->getPathStructure(array_keys($coursepath));
	$path_slot 		= $path_man->getPathSlot(array_keys($coursepath));

	// fin user subscription needed ---------------------------------------------------
	$user_paths 	=& $path_man->getUserSubscriptionsInfo(getLogUserId(), false);
	$usercourses 	=& $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), false);

	// find course basilar information ------------------------------------------------
	$course_info = $course_man->getAllCourses(false, false, $courses['all_items'], true);

	$GLOBALS['page']->add($nav_bar->getNavBar($ini), 'content');
	
	while(list($id_path, $path) = each($coursepath)) {

		$html = '<div class="coursepath_container">';

		$in_h = ' <span class="coursepath_subscribe">';
		$can_subs = true;
		if(isset($ass_elem['parsed']['coursepath'])) {

			if(isset($ass_elem['not_done']['coursepath']) && in_array($id_path, $ass_elem['not_done']['coursepath'])) {

				// the assosiacted preassessment is not done
				$in_h .= '';//$lang->def('_MUST_DO_PREASSESSMENT');
				$can_subs = false;
			} elseif(isset($ass_elem['to_apply']['coursepath']) && in_array($id_path, $ass_elem['to_apply']['coursepath'])) {

				// the assosiacted preassessment suggest this coursepath
				$in_h .= $lang->def('_PREASSESSMENT_SUGGESTION');
			}
		} else {

			switch($path[COURSEPATH_METHOD]) {
				case METHOD_WAIT 	: { $in_h .= $lang->def('_METHOD_WAIT'); };break;
				case METHOD_AUTO 	: { $in_h .= $lang->def('_METHOD_AUTO'); };break;
				case METHOD_MANUAL 	:
				default : { $in_h .= $lang->def('_METHOD_MANUAL'); $can_subs = false; };break;
			}
		}
		$in_h .= '</span>';
		$in_h .= ' <span class="coursepath_status">';
		if(isset($usercourses[$id_path])) {
			// user is alredy subscribed to this coursepath
			if($usercourses[$id_path]['waiting']) {

				$in_h .= $lang->def('_COURSEPATH_WAITING');
			} else {

				$in_h .= $lang->def('_USER_STATUS_SUBS');
			}
		}
		$in_h .= '</span>';
		// -------------------------------------------------------------
		$html .= '<div class="coursepath_info_container">';

		$html .= '<h2 class="pathtitle">'
			.$in_h
			.( $path[COURSEPATH_CODE] != '' ? '['.$path[COURSEPATH_CODE].'] ': '' ).$path[COURSEPATH_NAME]
			
			.'</h2>'
			.'<p class="course_support_info">'.str_replace('[enrolled]', $path[CP_ENROLLED], $lang->def('_COURSEPATH_INTRO')).'</p>';
			
		if(!isset($courses[$id_path]) || empty($courses[$id_path])) {
			$html .= $lang->def('_NO_COURSE_ASSIGNED_TO_COURSEPATH').'<br />';
		} else {

			// display the slots
			foreach($path_slot[$id_path]  as $id_slot => $slot_info) {
				if($id_slot == 0) {

					$html .= '<h4>'.$lang->def('_MANDATORY').'</h4>';
					if(!empty($courses[$id_path][$id_slot])) $html .= '<ul class="coursepath_mainslot">';
				} else {

					if($slot_info['min_selection'] > 0 && $slot_info['max_selection'] > 0) {

						$title = str_replace( 	array('[min_selection]', '[max_selection]'),
												array($slot_info['min_selection'], $slot_info['max_selection']),
												$lang->def('_COURSE_PATH_SLOT_MIN_MAX'));
					} elseif($slot_info['max_selection'] > 0) {

						$title = str_replace( 	'[max_selection]',
												$slot_info['max_selection'],
												$lang->def('_COURSE_PATH_SLOT_MAX'));
					} else {

						$title = $lang->def('_COURSE_PATH_SLOT');
					}
					$html .= '<h4>'.$title.'</h4>';
					if(!empty($courses[$id_path][$id_slot])) $html .= '<ul class="coursepath_otherslot">';
				}
				$i = 0;
				while(list($id) = each($courses[$id_path][$id_slot])) {

					$html .= '<li class="path_course '.( $i%2 ? 'path_odd' : '' ).'">'
						.'<a class="show_details_more" href="javascript:;" onclick="course_dash(this, \''.$id.'\',\'info_'.$id_path.'_'.$id.'\', \''.$can_subs.'\');">'.$lang->def('_DETAILS').'</a>'
						.( $course_info[$id]['code'] != '' ? ' ['.$course_info[$id]['code'].'] ' : '' ).$course_info[$id]['name']
						.'<div id="info_'.$id_path.'_'.$id.'"></div>'
						.'</li>';
					$i++;
				}
				if(!empty($courses[$id_path][$id_slot])) $html .= '</ul>';
			}
			
		}
		$html .= '</div>';

		$html .= '</div>';

		$GLOBALS['page']->add($html, 'content');
	}

	$GLOBALS['page']->add($nav_bar->getNavBar($ini), 'content');
}

/**
 * this course simply print the course box, we need to provide all the information ,
 * this function only display the information collected by other function
 * @param array $cinfo it must containt [ idCourse, code, name, description, create_date,
 * 						number_of_subscription, type_of, materials, demo, teacher_list, score, if_user_alredy_scored_this,
 * 						 prize, is_sell, alredy_subscribed, type_of_subscription
 *
 * 						idCourse, course_type, idCategory, code, name, description, lang_code, difficult,
 *						subscribe_method, date_begin, date_end, max_num_subscribe,
 *						selling, prize, create_date, course_status, course_edition,
 *						classrooms, course_demo, course_vote, enrolled
 *
 * @param int $index the number of object visualized
 */

function dashcourse(&$url, &$lang, &$cinfo, $uc_status, $index, $enable_actions = true, $h_number = 2) {

	$has_edition 	= $cinfo['course_edition'];
	
	YuiLib::load(array('animation' => 'my_animation'));
	
	$course_type 	= $cinfo['course_type'];
	$action 		= relationWithCourse($cinfo['idCourse'], $cinfo, $uc_status, false);
	$there_material	= array();

	$lang_c =& DoceboLanguage::createInstance('course', 'lms');

	if (!defined("_ECOM_CURRENCY")) {
		$currency_label = getPLSetting("ecom", "currency_label", "");
		define("_ECOM_CURRENCY", $currency_label);
	}

	$cs = array(
		CST_PREPARATION => $lang_c->def('_CST_PREPARATION', 'course', 'lms'),
		CST_AVAILABLE 	=> $lang_c->def('_CST_AVAILABLE', 'course', 'lms'),
		CST_EFFECTIVE 	=> $lang_c->def('_CST_CONFIRMED', 'course', 'lms'),
		CST_CONCLUDED 	=> $lang_c->def('_CST_CONCLUDED', 'course', 'lms'),
		CST_CANCELLED 	=> $lang_c->def('_CST_CANCELLED', 'course', 'lms') );

	if($cinfo['img_material'] != '') $there_material[] = '&id_course='.$cinfo['idCourse'];

	$html = '<div class="course_container'
		.( Get::sett('use_social_courselist') == 'on' ? ' double_height' : ' normal_height' )
		.( $index == 0 ? ' course_container_first' : '' ).'">';
	$html .= '<div class="course_info_container">'
			.'<h'.$h_number.'>'.
		( $cinfo['lang_code'] ? '<img src="'.getPathImage('cms').'language/'.$cinfo['lang_code'].'.png" alt="'.$cinfo['lang_code'].'" /> ' : '' ).
		//'['.$cinfo['code'].'] '.
		$cinfo['name'].
	'</h'.$h_number.'>';

	if($cinfo['classrooms'] != '') {

		$html .= str_replace(	array('[classrooms_name]', '[classrooms_location]'),
								array($cinfo['classrooms']['classroom'], $cinfo['classrooms']['location']),
								$lang->def('_IN_THE_CLASSROOM')
							);
	}
	// -----------------------------------------------------------------
	if(!$has_edition) {

		$html .= '<p class="course_support_info">';
		// number of subscription not limited
		if($cinfo['max_num_subscribe'] == 0) {

			$html .=  str_replace(	array('[course_type]', '[create_date]', '[enrolled]', '[course_status]'),
							array($course_type, createDateDistance($cinfo['create_date'], 'catalogue', true), $cinfo['enrolled'], $cs[$cinfo['course_status']]),
							$lang->def('_COURSE_INTRO'))
					.' ['.$cinfo['code'].'] ';
		} else {

			// limited number of subscription
			$html .=  str_replace(	array('[course_type]', '[create_date]', '[enrolled]', '[course_status]', '[max_subscribe]'),
							array($course_type, createDateDistance($cinfo['create_date'], 'catalogue', true), $cinfo['enrolled'], $cs[$cinfo['course_status']], $cinfo['max_num_subscribe']),
							$lang->def('_COURSE_INTRO_WITH_MAX'));

			if($cinfo['enrolled'] >= $cinfo['max_num_subscribe'] && $cinfo['allow_overbooking'] == '1') {

			// limited number of subscription reached
				$html .= '<br/>'.$lang->def('_CAN_JOIN_WAITING_LIST');
			}
		}
		if($cinfo['min_num_subscribe'] != 0) {
			$html .= '<br/>'.str_replace('[min_subscribe]', $cinfo['min_num_subscribe'], $lang->def('_MIN_SUBSCRIBE_FOR_COURSE'));
		}
		$html .= '</p>';
	}
	// --------------------
	if(trim($cinfo['description']) == '')   $html .= '';
	elseif(strpos($cinfo['description'], '<p') === false) $html .= '<p class="course_description">'.$cinfo['description'].'</p>';
	else  $html .= '<div class="course_description">'.$cinfo['description'].'</div>';

	if(empty($cinfo['edition_list']) && $has_edition) {

		$html .= '<img src="'.getPathImage('lms').'coursecatalogue/editions.png" alt="'.$lang->def('_EDITIONs').'" /> '
			.$lang->def('_NO_CONTENT');

	} elseif($has_edition) {

		// edition list actions ---------------------------------------------------
		$html .= '<p class="editions_actions">';
		$html .= '<img src="'.getPathImage('lms').'coursecatalogue/editions.png" alt="'.$lang->def('_EDITIONs').'" /> ';
		$html .= '<a id="course_edition_'.$cinfo['idCourse'].'_open" class="course_editions_expand" href="javascript:;" onclick="
			YAHOO.Animation.BlindIn(\'course_edition_'.$cinfo['idCourse'].'\', \'\');
			YAHOO.util.Dom.get(\'course_edition_'.$cinfo['idCourse'].'_open\').style.display = \'none\';
			YAHOO.util.Dom.get(\'course_edition_'.$cinfo['idCourse'].'_close\').style.display = \'inline\';
			return false;">'

			.str_replace(	array('[edition_count]', '[edition_available]'),
							array(count($cinfo['edition_list']), $cinfo['edition_available']),
							$lang->def('_SHOW_COURSE_EDITION'))
		.'</a>';

		$html .= '<a id="course_edition_'.$cinfo['idCourse'].'_close" class="course_editions_collapse" href="javascript:;" onclick="
			YAHOO.Animation.BlindOut(\'course_edition_'.$cinfo['idCourse'].'\');
			YAHOO.util.Dom.get(\'course_edition_'.$cinfo['idCourse'].'_close\').style.display = \'none\';
			YAHOO.util.Dom.get(\'course_edition_'.$cinfo['idCourse'].'_open\').style.display = \'inline\';
			return false;">'

			.$lang->def('_HIDE_COURSE_EDITION')
		.'</a>'
		.'</p>';

		// edition list show -------------------------------------------------------------------------
		$html .= '<ul id="course_edition_'.$cinfo['idCourse'].'" class="course_editions">';

		while(list($id_edition, $ed_info) = each($cinfo['edition_list'])) {

			if($ed_info['img_material'] != '') $there_material[] = '&id_course='.$cinfo['idCourse'].'&edition_id'.$ed_info['idCourseEdition'];

			$html .= '<li><b class="course_title">['.$ed_info['code'].'] '.$ed_info['name'].'</b><p>';

			if(($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00') || $ed_info['classrooms'] != '') {
				$html .= $lang->def('_EDITIONS');
			}
			if($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00') {
				$html .= ' '.str_replace(	array('[date_begin]', '[date_end]'),
										array(Format::date($ed_info['date_begin'], 'date'),
											Format::date($ed_info['date_end'], 'date')),
										$lang->def('_EDTION_TIME'));
			}
			if($ed_info['classrooms'] != '') {

				$html .= str_replace(	array('[classrooms_name]', '[classrooms_location]'),
											array($ed_info['classrooms']['classroom'], $ed_info['classrooms']['location']),
											$lang->def('_IN_THE_CLASSROOM') 	);
			}
			if(($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00') || $ed_info['classrooms'] != '') {
				$html .= '<br />';
			}
			if($ed_info['max_num_subscribe'] == 0)
				$html .= str_replace(	array('[user_count]', '[waiting_count]', ' su [max_user]'),
										array($ed_info['user_count'], $ed_info['waiting'], ''),
										$lang->def('_USER_EDITION_SUBSCRIBE') ).'</p>';
			else
				$html .= str_replace(	array('[user_count]', '[waiting_count]', '[max_user]'),
										array($ed_info['user_count'], $ed_info['waiting'], $ed_info['max_num_subscribe']),
										$lang->def('_USER_EDITION_SUBSCRIBE') ).'</p>';

			if(($ed_info['user_count'] != '' && $ed_info['date_end'] != '0000-00-00') || $ed_info['classrooms'] != '') {
				$html .= '<br />';
			}

			// number of subscription not limited
			/*if($ed_info['max_num_subscribe'] == 0) {

				$html .= str_replace(	array('[user_count]', '[waiting_count]', ' su [max_user]'),
										array($ed_info['user_count'], $ed_info['waiting'], ''),
										$lang->def('_USER_EDITION_SUBSCRIBE') );
			} else {

				// limited number of subscription
				$html .= str_replace(	array('[user_count]', '[max_subscribe]', '[waiting_count]'),
										array($ed_info['user_count'], $ed_info['max_num_subscribe'], $ed_info['waiting']),
										$lang->def('_USER_EDITION_SUBSCRIBE_WITH_MAX') );

				if($ed_info['user_count'] >= $ed_info['max_num_subscribe'] && $ed_info['allow_overbooking'] == '1') {

					// limited number of subscription reached
					$html .= '<br/>'.$lang->def('_CAN_JOIN_WAITING_LIST');
				}
			}
			if($ed_info['min_num_subscribe'] != 0) {
				$html .= '<br/>'.str_replace('[min_subscribe]', $cinfo['min_num_subscribe'], $lang->def('_MIN_SUBSCRIBE_FOR_EDITION'));
			}
			$html .= '</p>';*/

			// theacher list ----------------------------------------------------------
			if(Get::sett('use_social_courselist') == 'on') {

				if(isset($ed_info['theacher_list']) && is_array($ed_info['theacher_list']) && !empty($ed_info['theacher_list'])) {

					$html .= '<h3 class="course_teacher_list">'.$lang->def('_THEACER_LIST').'</h3>'
						.'<ul class="course_teacher_list">';

					while(list(, $id_teach) = each($ed_info['theacher_list'])) {

						$profile = new UserProfile( $id_teach );
						$profile->init('profile', 'framework', '', 'ap');
						$html .= '<li class="the_course">'
							.'<a href="'.$url->getUrl('op=showprofile&id_course='.$cinfo['idCourse'].'&id_user='.$id_teach).'">'
							.$profile->getUserPhotoOrAvatar('micro').' '.$profile->resolveUsername()
							.'</a></li>';
					}
					$html .= '</ul>';
				}
			}

			$html .= '</li>';
		}
		$html .= '</ul>';

		$html .= '<script type="text/javascript">
			YAHOO.util.Dom.get(\'course_edition_'.$cinfo['idCourse'].'\').style.display = \'none\';
			YAHOO.util.Dom.get(\'course_edition_'.$cinfo['idCourse'].'_close\').style.display = \'none\';
		</script>';

	} elseif(Get::sett('use_social_courselist') == 'on') {

		// theacher list ----------------------------------------------------------
		if(isset($cinfo['theacher_list']) && is_array($cinfo['theacher_list']) && !empty($cinfo['theacher_list'])) {

			$html .= '<h3 class="course_teacher_list">'.$lang->def('_THEACER_LIST').'</h3>'
				.'<ul class="course_teacher_list">';

			while(list(, $id_teach) = each($cinfo['theacher_list'])) {

				$profile = new UserProfile( $id_teach );
				$profile->init('profile', 'framework', '', 'ap');
				$html .= '<li>'
					.'<a href="'.$url->getUrl('op=showprofile&id_course='.$cinfo['idCourse'].'&id_user='.$id_teach).'">'
					.$profile->getUserPhotoOrAvatar('micro').' '.$profile->resolveUsername()
					.'</a></li>';
			}
			$html .= '</ul>';
		}

	}
	// course related extra option ---------------------------------------------
	if(Get::sett('use_social_courselist') == 'on' || !empty($there_material) || ($cinfo['course_demo'] != '')) {

		$html .= '<ul class="course_related_actions">';
		if(Get::sett('use_social_courselist') == 'on') {

			$html .= '<li class="course_comment">'
					.'<a href="javascript:;" onclick="openComment(\''.$cinfo['idCourse'].'\'); return false;">'
					.'<span>'.$lang->def('_COMMENTS').' ('
					.( isset($cinfo['comment_count']) ? $cinfo['comment_count'] : '0' ).')</span></a></li>';
			
		}
		// the course material -----------------------------------------------------------------
		if(!empty($there_material))  {

			if(count($there_material) == 1) {

				// direct download of material -------------------------------------------------
				$html .= '<li class="course_materials">'
					.'<a href="'.$url->getUrl('op=donwloadmaterials'.array_pop($there_material)).'">'
					.'<span>'.$lang->def('_MATERIALS').'</span></a></li>';
			} else {

				// popup download of material --------------------------------------------------
				$html .= '<li class="course_materials">'
					.'<a href="javascript:;" onclick="openWindowWithAction(\''.$cinfo['idCourse'].'\', \'course_materials\'); return false;">'
					.'<span>'.$lang->def('_MATERIALS').'</span></a></li>';
			}
		}

		// the course demo link ----------------------------------------------------------------
		if($cinfo['course_demo'] != '') {

			require_once(_base_.'/lib/lib.multimedia.php');
			$ext = end(explode('.', $cinfo['course_demo']));
			if(isPossibleEmbedPlay('/appLms/'.Get::sett('pathcourse'), $cinfo['course_demo'], $ext)) {

				// play demo in popup ---------------------------------------------------------
				$html .= '<li class="course_demo">'
					.'<a href="javascript:;" onclick="openWindowWithAction(\''.$cinfo['idCourse'].'\', \'play_demo\'); return false;">'
					.'<span>'.$lang->def('_DEMO').'</span></a></li>';
			} else {

				// download demo --------------------------------------------------------------
				$html .= '<li class="course_demo">'
					.'<a href="'.$url->getUrl('op=showdemo&id_course='.$cinfo['idCourse']).'">'
					.'<span>'.$lang->def('_DEMO').'</span></a></li>';
			}
		}
		$html .= '</ul>';
	}
	$html .= '</div>';
	// score and subscribe action ----------------------------------------------
	$html .= '<ul class="course_score">';
	if($enable_actions) {
		if($has_edition)
			list($edition_for_enter) = sql_fetch_row(sql_query(	"SELECT edition_id"
																	." FROM ".$GLOBALS['prefix_lms']."_courseuser"
																	." WHERE idUser = '".getLogUserId()."'"
																	." AND idCourse = '".$cinfo['idCourse']."'"
																	." ORDER BY edition_id DESC"
																	." LIMIT 0,1"));
		
		
		if($cinfo['first_is_scorm'] != false && $cinfo['direct_play']) {
			$lb_param = "";
			if($cinfo['first_is_scorm'][0] != '' && $cinfo['first_is_scorm'][0] != '0')
					$lb_param .= ";width=".$cinfo['first_is_scorm'][0]."";
	
			if($cinfo['first_is_scorm'][1] != '' && $cinfo['first_is_scorm'][1] != '0')
				$lb_param .= ";height=".$cinfo['first_is_scorm'][1]."";
		}
		if($action[0] == 'subscribed' ) {
			
			$access = Man_Course::canEnterCourse($cinfo);
		}
		$html .= '<li id="action_of_'.$cinfo['idCourse'].'" class="third_action '.$action[0].'">'
				/*
				.(  '<a href="index.php?modname=course_autoregistration&op=course_autoregistration"'.' >'
							.$lang->def('_SUBSCRIBE')
							.'<br /></a>'
						 )
				*/
				.( $action[1] != false ? '<a href="javascript:;"'
					.' onclick="openWindowWithAction(\''.$cinfo['idCourse'].'\', \'course_action_confirm'.( $has_edition ? '_edition' : '' ).'\'); return false;">' : '' )
				.($action[0] == 'subscribed' && $access['can']
					? '<a href="index.php?modname=course&op=aula&idCourse='.$cinfo['idCourse'].($has_edition ? '&amp;id_e='.$edition_for_enter : '').'"'
						.($cinfo['direct_play'] == 1 && $cinfo['level'] <= 3 && $cinfo['first_is_scorm']
							? ' rel="shadowbox'.$lb_param.'" title="'.$cinfo['name'].'"' 
							: ' title="'.$lang->def('_ENTER').'"' )
						.'>'.$lang->def('_ENTER').'</a>' 
					: $lang->def('_'.strtoupper($action[0])).'<br />'
				);
		switch($action[0]) {
			case "can_buy" :
			case "can_reserve" : { $html .= $cinfo['prize'].' '._ECOM_CURRENCY; };break;
		}
		$html .= ( $action[2] != false ? '<img src="'.getPathImage().'coursecatalogue/'.$action[2].'" '
					.'alt="'.$lang->def('_ALT_'.strtoupper($action[0])).'"/>' : '' )
				.( $action[1] != false ? '</a>' : '' )
				.'</li>';
	}
	if(Get::sett('use_social_courselist') == 'on') {

		$html .= '<li class="current_score"><span>'.$lang->def('_SCORE').'</span><br />'
			.'<strong id="course_score_'.$cinfo['idCourse'].'">'.$cinfo['course_vote'].'</strong></li>';
		if($uc_status != false && $uc_status['waiting'] == 0)  {

			$html .= '<li class="score_it">'
					.'<a class="good" href="javascript:;" '
						.'onclick="course_vote(\''.$cinfo['idCourse'].'\', \'good\'); return false;" '
						.'title="'.$lang->def('_VOTE_GOOD_TITLE').'">'

						.'<img id="score_image_good_'.$cinfo['idCourse'].'" src="'.getPathImage().'coursecatalogue/good'
							.( $cinfo['user_score'] == '1' ? '_grey' : '' )
							.'.png" alt="'.$lang->def('_VOTE_GOOD_ALT').' : '.strip_tags($cinfo['name']).'" />'
					.'</a> '
					.'<a class="bad" href="javascript:;" '
						.'onclick="course_vote(\''.$cinfo['idCourse'].'\', \'bad\'); return false;" '
						.'title="'.$lang->def('_VOTE_BAD_TITLE').'">'

						.'<img id="score_image_bad_'.$cinfo['idCourse'].'" src="'.getPathImage().'coursecatalogue/bad'
							.( $cinfo['user_score'] == '-1' ? '_grey' : '' )
							.'.png" alt="'.$lang->def('_VOTE_BAD_ALT').' : '.strip_tags($cinfo['name']).'" />'
					.'</a>'
				.'</li>';
		} else {

			$html .= '<li class="score_it" id="score_action_'.$cinfo['idCourse'].'">'
					.'<img src="'.getPathImage().'coursecatalogue/good_grey.png" alt="'.$lang->def('_VOTE_GOOD_ALT').' : '.strip_tags($cinfo['name']).'" /> '
					.'<img src="'.getPathImage().'coursecatalogue/bad_grey.png" alt="'.$lang->def('_VOTE_BAD_ALT').' : '.strip_tags($cinfo['name']).'" />'
				.'</li>';
		}
	}
	$html .= '</ul>';
	$html .= '</div>';
	return $html;
}

function must_search_filter() {

	return ( isset($_SESSION['coursecatalogue']['in_search']) && $_SESSION['coursecatalogue']['in_search'] == true );
}

function get_searched($var, $default) {

	$prefix = 'coursecatalogue';
	if(isset($_POST['do_search'])) {
		$_SESSION[$prefix]['in_search'] = true;
	}
	if(isset($_POST['reset_search'])) {
		if(isset($_SESSION[$prefix])) {

			$_SESSION[$prefix] = array();
			unset($_SESSION[$prefix]);
		}
		return $default;
	}

	if(isset($_POST[$var])) {

		$_SESSION[$prefix][$var] = $_POST[$var];
		return $_POST[$var];
	}
	if(isset($_GET[$var])) {

		$_SESSION[$prefix][$var] = $_GET[$var];
		return $_GET[$var];
	}
	return ( isset($_SESSION[$prefix][$var]) ? $_SESSION[$prefix][$var] : $default );
}

function searchForm(&$url, &$lang) {

	//$filter_type = get_searched('filter_type', array('free'=>1, 'editions'=>1, 'sale'=>1));

	$langs = Docebo::langManager()->getAllLangCode();
	$all_lang = array( 'all' => $lang->def('_ALL_LANGUAGE') );
	$all_lang = array_merge($all_lang, $langs);

	$html = '';
	$html .= Form::openForm('search_coursecatalogue', $url->getUrl())

	.Form::getTextfield(	$lang->def('_WORD_TO_SEARCH'),
							'simple_search',
							'simple_search',
							'255',
							get_searched('simple_search', ''),
							false )
	//.'<a id="advanced_search_link" class="adv_link" href="'.$url->getUrl().'" onclick="toggle_adv_search(); return false;">'.$lang->def('_ADVANCED_SEARCH').'</a>'

	//.'<div id="advanced_search">'
	.Form::getDropdown( $lang->def('_FILTER'),
						'filter_lang',
						'filter_lang',
						$all_lang,
						get_searched('filter_lang', 'all') )
						


	.'<div class="nofloat align_right">'
	.Form::getButton('do_search', 'do_search', $lang->def('_SEARCH'), '')
	.( isset($_SESSION['coursecatalogue']['in_search']) && $_SESSION['coursecatalogue']['in_search'] == true
		? ' '.Form::getButton('reset_search', 'reset_search', $lang->def('_CANCEL'), '') : '')
	.'</div>'

	//.'</div>'

	//.'<script type="text/javascript">
	//	$(\'advanced_search\').style.display = \'none\';
	//</script>'
	.Form:: closeForm();

	return $html;
}

/**
 * @return array 0 => can_buy		=> user can put the course in the cart
 * 					can_prenote 	=> user can
 * 					can_subscribe 	=> user can subscribe freely
 * 					can_reserve		=> user can put the course in the buyer cart
 * 					can_overbook	=> user can put the course in the buyer cart, but in overbooking
 *
 * 					impossible 		=> the user can do nothing with the course
 * 					in_cart 		=> the course is in the user cart
 * 					subscribed 		=> user is alredy enrolled to the course
 *
 * 					waiting_admin 	=> alredy request subs. and waiting for admin approvation
 * 					waiting_buyer 	=> alredy request, waiting for buyer approvation
 * 					waiting_payment => waiting for payment confirmation
 * 					waiting_overbooking => in overbooking, cannot be approved
 *
 * 				1=> link for action
 * 				2=> associated icon
 * 				3=> if the subscrition is impossibile here you can find the problem
 *
 */
function relationWithCourse($id_course, &$course, $uc_details, $edition_id = false) {

// 	require_once($GLOBALS['where_ecom'].'/lib/lib.cart.php');
// 	$cart =& Cart::createInstance();
	
	list($enrolled) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM ".$GLOBALS['prefix_lms']."_courseuser WHERE idCourse = '".$id_course."' AND edition_id = '0'"));
	
	$course['enrolled'] = $enrolled;
	
	$base_link = 'index.php?modname='.( Docebo::user()->isAnonymous() ? 'login' : 'coursecatalogue' ).'&op=';

	$bought_items 	=& getEcomItems();
	$product_type 	= ( $edition_id !== FALSE ? "course_edition" : "course" );
	$search_item 	= ( $edition_id !== FALSE ? $product_type."_".$edition_id : $product_type."_".$id_course );

	if($uc_details != false) {

		// user is in relation with the course, alredy subscribed or waiting for admin approvation
		switch($uc_details['waiting']) {
			case '0' : return array('subscribed', false, false); break;
			case '1' : return array('waiting_admin', false, false); break;
			case '2' : return array('waiting_overbooking', false, false); break;
		}
	}
	switch($course['can_subscribe']) {
		case "0" : { return array('impossible', false, false, 'subscribe_lock'); };break;
		case "2" : {
			$today = date("Y-m-d H:i:s");
			if($course['sub_start_date'] != 'NULL' && strcmp($course['sub_start_date'], $today) > 0) return array('impossible', false, false, 'date_range');
			if( $course['sub_end_date'] != 'NULL' && strcmp($course['sub_end_date'], $today) < 0) return array('impossible', false, false, 'date_range');
		};break;
	}
	if($course['subscribe_method'] > 0) {

		$pl_man =& PlatformManager::CreateInstance();
		if(!$pl_man->isLoaded('ecom')) {
			$course['selling'] = 0;
		}
		if($course['selling'] == 1) {

			$ecom_type = getPLSetting("ecom", "ecom_type", "none");
			if($ecom_type == "standard") {

				// maybe if the course is with edition there is an editon in the cart
				if($edition_id !== FALSE && isset($course['edition_list']) && !empty($course['edition_list'])) {

					while(list($id) = each($course['edition_list'])) {
						/*
						if(isset($bought_items['transaction'][$product_type]) && in_array($product_type."_".$id, $bought_items['transaction'][$product_type])) {
							// find in bought item
							return array('waiting_payment', false, false);
						} else*/
						if($cart->isInCart($product_type."_".$id)) {
							// find in cart
							return array('in_cart', false, false);
						}
					}
					reset($course['edition_list']);
				} else {

					/*if(isset($bought_items['transaction'][$product_type]) && in_array($search_item, $bought_items['transaction'][$product_type])) {
						// find in bought item
						return array('waiting_payment', false, false);
					} else*/
					if($cart->isInCart($search_item)) {
						// find in cart
						return array('in_cart', false, false);
					}
				}

				// max number of subscription ? overbooking ? ---------------------------------------
				if($course['max_num_subscribe'] != 0 && $course['max_num_subscribe'] <= $course['enrolled']) {

					if($course['allow_overbooking'] == 1) return array('can_overbook', $base_link.'overbook&amp;id='.$id_course, false);
					else return array('impossible', false, false, 'full_course');
				}
				return array('can_buy', $base_link.'addToCart&id='.$id_course, 'can_buy.png');
			} elseif($ecom_type == "with_buyer") {

				// ecom is with buyer --------------------------------------------------------------------------

				// maybe if the course is with edition there is an editon in the cart
				if($edition_id !== FALSE && isset($course['edition_list']) && !empty($course['edition_list'])) {

					while(list($id) = each($course['edition_list'])) {

						if(isset($bought_items['reservation'][$product_type]) && in_array($product_type."_".$id, $bought_items['reservation'][$product_type])) {
							// find in bought item
							return array('waiting_payment', false, false);
						} elseif($cart->isInCart($product_type."_".$id)) {
							// find in cart
							return array('in_cart', false, false);
						}
					}
					reset($course['edition_list']);
				} else {

					// searching in the buyer assigned to the user the course
					if(isset($bought_items['reservation'][$product_type]) && in_array($search_item, $bought_items['reservation'][$product_type])) {
						return array('waiting_buyer', false, false);
					} elseif($cart->isInCart($search_item)) {
						// find in cart
						return array('in_cart', false, false);
					}
				}

				// max number of subscription ? overbooking ? ---------------------------------------
				if($course['max_num_subscribe'] != 0 && $course['max_num_subscribe'] <= $course['enrolled']) {

					if($course['allow_overbooking'] == 1) return array('can_overbook', $base_link.'overbook&amp;id='.$id_course, false);
					else return array('impossible', false, false, 'full_course');
				}
				return array('can_reserve', $base_link.'reserve&amp;id='.$id_course, 'can_prenote.png');
			}
		} else {

			if($course['subscribe_method'] == 1) {

				// max number of subscription ? overbooking ? ---------------------------------------
				if($course['max_num_subscribe'] != 0 && $course['max_num_subscribe'] <= $course['enrolled']) {

					if($course['allow_overbooking'] == 1) return array('can_overbook', false, false);
					else return array('impossible', false, false, 'full_course');
				}
				return array('can_prenote', $base_link.'subscribecourse&amp;id='.$id_course, 'can_prenote.png');
			}
			if($course['subscribe_method'] == 2) {

				// max number of subscription ? overbooking ? ---------------------------------------
				if($course['max_num_subscribe'] != 0 && $course['max_num_subscribe'] <= $course['enrolled']) {

					if($course['allow_overbooking'] == 1) return array('can_overbook', false, false);
					else return array('impossible', false, false, 'full_course');
				}
				return array('can_subscribe', $base_link.'subscribecourse&amp;id='.$id_course, 'can_subscribe.png');
			}
		}
	}
	return array('impossible', false, false, 'only_admin');
}

function getCourseEditionList($course_id) {
	$res="";

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	$lang_c =& DoceboLanguage::createInstance('catalogue');
	$lang =& DoceboLanguage::createInstance('course');

	$man_course = new Man_Course();
	$course = $man_course->getCourseInfo($course_id);
	$course_name=$course["name"];

	$subs_lang = array(
		0 => $lang->def('_COURSE_S_GODADMIN'),
		1 => $lang->def('_COURSE_S_MODERATE'),
		2 => $lang->def('_COURSE_S_FREE'),
		3 => $lang->def('_COURSE_S_SECURITY_CODE') );

	$qtxt ="SELECT t1.*, COUNT(t2.idUser) as enrolled FROM ".$GLOBALS["prefix_lms"]."_course_edition as t1 ";
	$qtxt.="LEFT JOIN ".$GLOBALS['prefix_lms']."_courseuser AS t2 ON ( t1.idCourseEdition = t2.edition_id ) ";
	$qtxt.="WHERE t1.idCourse='".(int)$course_id."'  ";
	$qtxt.=" AND t1.status <> '".CST_PREPARATION."' ";
	
	$qtxt.=" AND (t1.date_begin > '".date("Y-m-d H:i:s")."' OR t1.date_begin = '0000-00-00 00:00:00')";

	$qtxt.=" GROUP BY t1.idCourseEdition ";
	$qtxt.="ORDER BY t1.date_begin";
	if(!$q=sql_query($qtxt)) return '';

	$html = '<ul class="course_editions">';
	while($ed_info=sql_fetch_assoc($q)) {

		$html .= '<li><b>['.$ed_info['code'].'] '.$ed_info['name'].'</b><br/><p>';

		if(($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00') || $ed_info['classrooms'] != '') {
			$html .= $lang->def('_EDITIONS');
		}
		if($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00') {
			$html .= ' '.str_replace(	array('[date_begin]', '[date_end]'),
									array(Format::date($ed_info['date_begin'], 'date'),
										Format::date($ed_info['date_end'], 'date')),
									$lang->def('_EDTION_TIME'));
		}
		$course['advance'] = $ed_info['advance'];
		$course['prize'] = $ed_info['price'];

		$html .= '<div class="align_right">'
			.getSubscribeActionLink($course_id, $course, $lang, $ed_info['idCourseEdition'])
			.'</div>';

		$html .= '</li>';
	}
	$html .= '</ul>';
	return $html;
}


function getCourseEditionTable($course_id) {
	$res="";

	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	$lang_c =& DoceboLanguage::createInstance('catalogue');
	$lang =& DoceboLanguage::createInstance('course');

/*
	$qtxt ="SELECT name FROM ".$GLOBALS["prefix_lms"]."_course ";
	$qtxt.="WHERE idCourse = '".(int)$course_id."'";
	list($course_name)=sql_fetch_row(sql_query($qtxt));
*/

	$man_course = new Man_Course();
	$course = $man_course->getCourseInfo($course_id);
	$course_name=$course["name"];

	$subs_lang = array(
		0 => $lang->def('_COURSE_S_GODADMIN'),
		1 => $lang->def('_COURSE_S_MODERATE'),
		2 => $lang->def('_COURSE_S_FREE'),
		3 => $lang->def('_COURSE_S_SECURITY_CODE') );

	$tab	= new Table(0, $lang->def('_EDITIONS').": ".$course_name, $lang->def('_EDITIONS'));
	$tab->setTableStyle('edition_block');

	$cont_h[] = $lang->def('_CODE');
	$type_h[] = 'code_course';

	$cont_h[] = $lang->def('_COURSE');
	$type_h[] = '';

	$cont_h[] = $lang->def('_SUBSCRIBE_METHOD');
	$type_h[] = 'image nowrap';

	$cont_h[] = $lang->def('_ENROL_COUNT');
	$type_h[] = 'image nowrap';

	$cont_h[] = $lang->def('_CREATION_DATE');
	$type_h[] = 'image nowrap';

	$cont_h[] = $lang->def('_SUBSCRIPTION', 'course');
	$type_h[] = 'image nowrap';


	$tab->setColsStyle($type_h);
	$tab->addHead($cont_h);

	$qtxt ="SELECT t1.*, COUNT(t2.idUser) as enrolled ";
	$qtxt.="FROM ".$GLOBALS["prefix_lms"]."_course_edition as t1 ";
	$qtxt.="	LEFT JOIN ".$GLOBALS['prefix_lms']."_courseuser AS t2 ON ( t1.idCourseEdition = t2.edition_id ) ";
	$qtxt.="WHERE t1.idCourse='".(int)$course_id."' ";
	$qtxt.="	AND t1.status <> '".CST_PREPARATION."' ";
	$qtxt.="GROUP BY t1.idCourseEdition  ";
	$qtxt.="ORDER BY t1.date_begin "; //$res.=$qtxt;

	$q=sql_query($qtxt);

	if (($q) && (sql_num_rows($q) > 0)) {
		while($row=sql_fetch_assoc($q)) {

			$cont=array();
			$cont[]=$row["code"];

			$edition_id=$row["idCourseEdition"];

			$url ="index.php?modname=coursecatalogue&amp;op=editiondetails&amp;edition_id=".$edition_id;
			$url.="&amp;course_id=".$course_id;
			$cont[]='<a href="'.$url.'">'.$row["name"]."</a>\n";

			$cont[]=$subs_lang[$course["subscribe_method"]];
			$cont[]=$row["enrolled"];
			$cont[]=createDateDistance($course["create_date"], "coursecatalogue");

			$cont[]=getSubscribeActionLink($course_id, $course, $lang, $edition_id);

			$tab->addBody($cont);
		}

		$res.=$tab->getTable();
	}
	else {
		$res=FALSE;
	}

	return $res;
}


function getSubscribeActionLink($id_course, $course, & $lang, $edition_id=FALSE) {
	$res="";

	$bought_items=& getEcomItems(); //print_r($bought_items);
	$product_type=($edition_id !== FALSE ? "course_edition" : "course");
	$search_item=($edition_id !== FALSE ? $product_type."_".$edition_id : $product_type."_".$id_course);


	if(isUserCourseSubcribed(getLogUserId(), $id_course, $edition_id)) {
		$res.=$lang->def('_SUBSCRIBED_T');
	}
	elseif($course['subscribe_method'] == 1 || $course['subscribe_method'] == 2 || $course['subscribe_method'] == 3) {

		$ecom_type = getPLSetting("ecom", "ecom_type", "none");

		$subscr_img = '<img src="'.getPathImage().'coursecatalogue/can_subscribe.png" alt="'.$lang->def('_SUBSCRIBE', 'catalogue').'" />';
		$selling_img = '<img src="'.getPathImage().'coursecatalogue/can_buy.png" alt="'.$lang->def('_GO_SELLING', 'catalogue').'" />';

		if (($course['selling'] == 1) && ($ecom_type == "standard")) {

			$action="transaction";
			if (in_array($search_item, $bought_items[$action][$product_type])) {
				$res.="x";
			}
			else {
				$url ='index.php?modname=coursecatalogue&amp;op=addToCart&amp;id='.$id_course;
				$url.=($edition_id !== FALSE ? '&amp;course_edition='.$edition_id : "");
				$res.='<a href="'.$url.'"'
					.'title="'.$lang->def('_BUY_COURSE_T', 'catalogue').'">'.$selling_img.' '.$lang->def('_BUY_COURSE', 'catalogue').' ('.$course['prize'].')'.'</a>';
			}

		} else if (($course['selling'] == 1) && ($ecom_type == "with_buyer")) {

			$action="reservation";
			$in_reservation =((in_array($search_item, $bought_items[$action][$product_type])) ? TRUE : FALSE);
			$in_transaction =((in_array($search_item, $bought_items["transaction"][$product_type])) ? TRUE : FALSE);
			if (($in_reservation) || ($in_transaction)) {
				$res.=$lang->def('_WAITING_APPROVAL', 'catalogue');
			} else {
				$url ='index.php?modname=coursecatalogue&amp;op=reserve&amp;id='.$id_course;
				$url.=($edition_id !== FALSE ? '&amp;course_edition='.$edition_id : "");
				$res.='<a href="'.$url.'" '
					.'title="'.$lang->def('_RESERVE_COURSE', 'catalogue').'">'.$selling_img.' '.$lang->def('_RESERVE_COURSE').' ('.$course['prize'].')'.'</a>';
			}

		} else {

			$url ='index.php?modname=coursecatalogue&amp;op=subscribecourse&amp;id='.$id_course;
			$url.=($edition_id !== FALSE ? '&amp;edition_id='.$edition_id : "");
			$res.='<a href="'.$url.'" '
				.'title="'.$lang->def('_SUBSCRIBE_COURSE_T', 'catalogue').'">'.$subscr_img.' '.$lang->def('_SUBSCRIBE').'</a>';
		}

	}

	return $res;
}


/**
 * Load in a global variable an array with information on bought courses or edition
 * or their reservations if ecommerce is modered by a buyer.
 */
function loadEcomItems() {

	$GLOBALS["lms_bought_items"]=array();
	$ecom_type=getPLSetting("ecom", "ecom_type", "none");

	if ($ecom_type !== "none") {
		require_once($GLOBALS["where_ecom"]."/admin/modules/reservation/lib.reservation.php");

		$res=array();
		$user_id=getLogUserId();

		// --- Transactions:  ------------------------------------

		// TODO: move this on a ecom lib / class[*] :
		$qtxt="SELECT * FROM ".$GLOBALS['prefix_ecom']."_transaction_product WHERE id_user='".$user_id."'";
		$q=sql_query($qtxt);

		$action="transaction";
		$res[$action] = array();
		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {

				if ((!isset($res[$action][$row["type"]])) || (!in_array($row["id_prod"], $res[$action][$row["type"]]))) {
					$key=$row["id_prod"];
					$res[$action][$row["type"]][$key]=$key;
				}
			}
		}

		// --- Reservations:  ------------------------------------

		// [*]something like this:
		$rm=new ReservationManager();
		$data_info=$rm->getReservationList(FALSE, FALSE, "user_id='".$user_id."'");
		$data_arr=& $data_info["data_arr"];

		$action="reservation";
		$res[$action] = array();
		foreach($data_arr as $item) {
			if ((!isset($res[$action][$item["type"]])) || (!in_array($item["product_code"], $res[$action][$item["type"]]))) {
				$key=$item["product_code"];
				$res[$action][$item["type"]][$key]=$key;
			}
		}

		$GLOBALS["lms_bought_items"]=$res;
	}

}


function &getEcomItems() {

	if (!isset($GLOBALS["lms_bought_items"])) {
		loadEcomItems();
	}

	return $GLOBALS["lms_bought_items"];
}


/**
 * Unset the global information about bought items
 */
function unsetEcomItems() {

	if (isset($GLOBALS["lms_bought_items"])) {
		unset($GLOBALS["lms_bought_items"]);
	}

}


function hasClassroom($type) {

	if (($type == "classroom") || ($type == "blended")) {
		$res=TRUE;
	}
	else {
		$res=FALSE;
	}

	return $res;
}


?>
