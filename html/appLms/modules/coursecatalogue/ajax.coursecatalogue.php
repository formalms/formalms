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
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 */

if(Docebo::user()->isAnonymous()) {
	require_once($GLOBALS["where_lms"]."/lib/lib.course.php");
	require_once($GLOBALS["where_lms"]."/modules/coursecatalogue/lib.coursecatalogue.php");
	$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
	$lang->setGlobal();

	$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');
	$lang =& DoceboLanguage::createInstance( 'course', 'lms');

	$man_course 	= new DoceboCourse(importVar('id_course', true, 0));
	$course_name 	= $man_course->getValue('name');

	$string = $lang->def('_THANKS_LOGIN_OR_REGISTER');
	$string = substr($string, strpos($string, '<a'));

	$subst = array( '[name]' => $course_name, '[link_register]' => Get::rel_path("base") . "/index.php?r=" . _register_ );
	$value = array(
		"next_op" 	=> '',
		"id" 		=> 'course_editions',
		"title" 	=> $lang->def('_COURSE_SUBSCRIPTION', 'catalogue'),
		"content" 	=> str_replace(array_keys($subst), $subst, $string)
	);
	require_once(_base_.'/lib/lib.json.php');

	$json = new Services_JSON();
	$output = $json->encode($value);
	aout($output);
	
} else {

	$op = Get::req('op', DOTY_ALPHANUM, '');
	switch($op) {
		case "getLang": {
			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'cart', 'ecom');

			$idst = getLogUserId();
			$acl_man =& Docebo::user()->getAclManager();
			$userid =Docebo::user()->getUserId();
			$user_info =$acl_man->getUser($idst, false);

			$user_email =$user_info[ACL_INFO_EMAIL];

			$lang_obj='{
				"_CART_POPUP_GO":"'.$lang->def('_CART_POPUP_GO').'",
				"_CART_POPUP_CLOSE":"'.$lang->def('_CART_POPUP_CLOSE').'",
				"_CART_POPUP_EMPTY":"'.$lang->def('_CART_POPUP_EMPTY').'"
			}';

			aout($lang_obj);
		} break;


		case "getCartSummary" : {
			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'cart', 'ecom');

			require_once($GLOBALS["where_ecom"]."/lib/lib.cart.php");
			$cart=& Cart::createInstance();

			$code = $cart->getCart(TRUE);

			$buttons = '<input type="submit" value="'.$lang->def('_CART_POPUP_GO').'" /> '
				.'<input type="button" id="close_cart_command" value="'.$lang->def('_CART_POPUP_CLOSE').'" />'
				.'<input type="submit" name="empty_cart" id="empty_cart" value="'.$lang->def('_CART_POPUP_EMPTY').'" />';

			$value = array('code' => $code, 'button' => $buttons);

			require_once(_base_.'/lib/lib.json.php');
			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		}; break;
		case "getCourseEditionsTable": {
			require_once($GLOBALS["where_lms"]."/modules/coursecatalogue/lib.coursecatalogue.php");
			aout(getCourseEditionTable((int)$_POST["course_id"]));
		} break;
		case "getdashcourse" : {

			require_once(_base_.'/lib/lib.form.php');
			require_once(_base_.'/lib/lib.user_profile.php');
			require_once(_base_.'/lib/lib.navbar.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
			require_once($GLOBALS["where_lms"]."/modules/coursecatalogue/lib.coursecatalogue.php");


			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'course', 'lms');

			$id_course = importVar('id_course', true, 0);
			$normal_subs = importVar('normal_subs', true, 0);

			$man_course	= new DoceboCourse($id_course);
			$cinfo = $man_course->getAllInfo();


			require_once($GLOBALS['where_lms'].'/lib/lib.classroom.php');
			// cahce classroom
			$classroom_man 	= new ClassroomManager();
			$classrooms = $classroom_man->getClassroomNameList();

			$cinfo['classrooms'] = ( isset($classrooms[$cinfo['classrooms']]) ? $classrooms[$cinfo['classrooms']] : false );

			$man_courseuser = new Man_CourseUser();
			$usercourses =& $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), false);

			$select_edition = " SELECT * ";
			$from_edition 	= " FROM ".$GLOBALS["prefix_lms"]."_course_edition";
			$where_edition 	= " WHERE idCourse = '".$id_course."' ";
			$order_edition 	= " ORDER BY date_begin ";
			$re_edition = sql_query($select_edition.$from_edition.$where_edition.$order_edition);
			$editions = array();
			if($re_edition)
			while($edition_elem = sql_fetch_assoc($re_edition)) {

				$edition_elem['waiting'] = 0;
				$edition_elem['user_count'] = 0;
				$edition_elem['theacher_list'] = getSubscribed($edition_elem["idCourse"], false, 6, true, $edition_elem["idCourseEdition"]);
				$edition_elem['classrooms'] = ( isset($classrooms[$edition_elem['classrooms']]) ? $classrooms[$edition_elem['classrooms']] : false );
				$editions[$edition_elem["idCourse"]][$edition_elem["idCourseEdition"]] = $edition_elem;
			}

			$select_ed_count 	= "SELECT idCourse, edition_id, sum(waiting) as waiting, COUNT(*) as user_count ";
			$from_ed_count 		= "FROM ".$GLOBALS["prefix_lms"]."_courseuser ";
			$where_ed_count 	= "WHERE edition_id <> 0 AND idCourse = '".$id_course."'";
			$group_ed_count 	= "GROUP BY edition_id ";
			$re_ed_count = sql_query($select_ed_count.$from_ed_count.$where_ed_count.$group_ed_count );
			if($re_ed_count)
			while($ed_count_elem = sql_fetch_assoc($re_ed_count)) {

				$editions[$ed_count_elem["idCourse"]][$ed_count_elem["edition_id"]]['waiting'] = $ed_count_elem['waiting'];
				$editions[$ed_count_elem["idCourse"]][$ed_count_elem["edition_id"]]['user_count'] = $ed_count_elem['user_count'];
			}

			$cinfo['theacher_list'] = getSubscribed($cinfo['idCourse'], false, 6, true);
			$cinfo['edition_list'] = ( isset($editions[$cinfo['idCourse']]) ? $editions[$cinfo['idCourse']] : array() );
			$cinfo['edition_available'] = count($cinfo['edition_list']);
			$cinfo['user_score'] = ( isset($user_score[$cinfo['idCourse']]) ? $user_score[$cinfo['idCourse']] : NULL );

			require_once(_base_.'/lib/lib.urlmanager.php');
			$url =& UrlManager::getInstance('catalogue');
			$url->setStdQuery('modname=coursecatalogue&op=coursecatalogue');
			if($normal_subs == 0) $cinfo['can_subscribe'] = 0;
			$html = dashcourse($url, $lang, $cinfo, ( isset($usercourses[$cinfo['idCourse']]) ? $usercourses[$cinfo['idCourse']] : false ), 0);

			$value = array("content" => $html, "elem_id" => importVar('elem_id'), "id_course" => importVar('id_course', false, 0));

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		} break;
		// -------------------------------------------------------------------------------
		case "course_vote" : {

			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");
			require_once($GLOBALS["where_lms"]."/modules/coursecatalogue/lib.coursecatalogue.php");

			$id_course 	= importVar('id_course', true, 0);
			$evaluation = importVar('evaluation');

			$man_course 	= new DoceboCourse($id_course);
			$man_courseuser = new Man_CourseUser();

			$user_score = $man_courseuser->getUserCourseScored(getLogUserId());

			$score = 0;
			switch($evaluation) {
				case "good" : {

					$userscore_to_save = 1;
					$score = 1;
					if(isset($user_score[$id_course])) {

						if($user_score[$id_course] > 0) $score = 0;
						else $score = 2;
					}
				};break;
				case "bad" : {
					$userscore_to_save = -1;
					$score = -1;
					if(isset($user_score[$id_course])) {
						if($user_score[$id_course] > 0) $score = -2;
						else $score = 0;
					}
				};break;
			}

			$new_score = $man_course->voteCourse(getLogUserId(), $score, $userscore_to_save);

			$value = array( "id_course" => importVar('id_course', true, 0),
						"evaluation" => $evaluation,
						"new_score" => $new_score,
						"path_image" => getPathImage().'coursecatalogue/' );

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		};break;

		// -----------------------------------------------------------------------------------

		case "course_action_confirm" : {
			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");
			require_once($GLOBALS["where_lms"]."/modules/coursecatalogue/lib.coursecatalogue.php");
			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();

			if(Docebo::user()->isAnonymous()) {

				$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');
				$lang =& DoceboLanguage::createInstance( 'course', 'lms');

				$man_course 	= new DoceboCourse(importVar('id_course', true, 0));
				$course_name 	= $man_course->getValue('name');

				$string = $lang->def('_THANKS_LOGIN_OR_REGISTER');
				$string = substr($string, strpos($string, '<a'));

				$subst = array( '[name]' => $course_name, '[link_register]' => Get::rel_path("base") . "/index.php?r=" . _register_ );
				$value = array(
					"next_op" 	=> '',
					"id" 		=> 'course_editions',
					"title" 	=> $lang->def('_COURSE_SUBSCRIPTION', 'catalogue'),
					"content" 	=> str_replace(array_keys($subst), $subst, $string)
				);
			} else {

				if (!defined("_ECOM_CURRENCY")) {
					$currency_label = getPLSetting("ecom", "currency_label", "");
					define("_ECOM_CURRENCY", $currency_label);
				}

				$lang =& DoceboLanguage::createInstance( 'course', 'lms');

				$id_course = importVar('id_course', true, 0);

				$man_course	= new DoceboCourse($id_course);
				$cinfo = $man_course->getAllInfo();

				$man_courseuser = new Man_CourseUser();
				$usercourses =& $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), false);

				// retrive subscribed -----------------------------------------------------
				$select_count 	= "SELECT COUNT(*) as user_count ";
				$from_count 		= " FROM ".$GLOBALS["prefix_lms"]."_courseuser AS u";
				$where_count 	= " WHERE u.idCourse = '".$id_course."' " .
						" AND u.level = '3'" .
						" AND u.status IN ('"._CUS_CONFIRMED."', '"._CUS_SUBSCRIBED."', '"._CUS_BEGIN."', '"._CUS_END."', '"._CUS_SUSPEND."', '"._CUS_WAITING_LIST."')" .
						" AND u.absent = '0'";
				$re_count = sql_query($select_count.$from_count.$where_count );
				list($cinfo['enrolled']) = sql_fetch_row($re_count);

				$action = relationWithCourse($cinfo['idCourse'],
					$cinfo,
					( isset($usercourses[$cinfo['idCourse']]) ? $usercourses[$cinfo['idCourse']] : false ),
					false);

				$html = $lang->def('_'.strtoupper($action[0]).'_DESCR');

				$search = array('[course_name]');
				$replace = array('<b>&quot;'.$cinfo['name'].'&quot;</b>');
				switch($action[0]) {
					case "can_buy" :
					case "can_reserve" : {
						$search[] = '[price]';
						$replace[] = $cinfo['prize'].' '._ECOM_CURRENCY;
					};break;
				}
				$html = str_replace($search, $replace, $html);

				$value = array(
					"next_op" 	=> ( $action[1] != false ? str_replace('&amp;', '&', $action[1]) : '' ),
					"id" 		=> 'course_action_'.$id_course,
					"title" 	=> $lang->def('_'.strtoupper($action[0])),
					"content" 	=> $html,
					"button" 	=> '<input type="submit" value="'.$lang->def('_CONFIRM').'" name="confirm" id="confirm" />'
							.'<input type="button" value="'.$lang->def('_UNDO').'" onclick="destroyWindow(\'course_action_'.$id_course.'\')"  id="undo_course_action'.$$id_course.'" />'
				);
			}
			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		} break;
		case "course_action_confirm_edition" : {

			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");
			require_once($GLOBALS["where_lms"]."/modules/coursecatalogue/lib.coursecatalogue.php");
			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();

			if(Docebo::user()->isAnonymous()) {

				$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');
				$lang =& DoceboLanguage::createInstance( 'course', 'lms');

				$man_course 	= new DoceboCourse(importVar('id_course', true, 0));
				$course_name 	= $man_course->getValue('name');

				$string = $lang->def('_THANKS_LOGIN_OR_REGISTER');
				$string = substr($string, strpos($string, '<a'));

				$subst = array( '[name]' => $course_name, '[link_register]' => Get::rel_path("base") . "/index.php?r=" . _register_ );
				$value = array(
					"next_op" 	=> '',
					"id" 		=> 'course_editions',
					"title" 	=> $lang->def('_COURSE_EDITON_SUBSCRIBE', 'catalogue'),
					"content" 	=> str_replace(array_keys($subst), $subst, $string)
				);
			} else {

				$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');

				$value = array(
					"next_op" 	=> '',
					"id" 		=> 'course_editions',
					"title" 	=> $lang->def('_COURSE_EDITON_SUBSCRIBE', 'catalogue'),
					"content" 	=> getCourseEditionList(importVar('id_course'))
				);
			}
			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		} break;

		// ------------------------------------------------------------------------------

		case "addnewcomment" : {

			require_once($GLOBALS["where_framework"]."/lib/lib.ajax_comment.php");
			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");

			$id_course = importVar('id_course', true, 0);
			$ax_comm = new AjaxComment('course', 'lms');

			$comment_data = array(
				AJCOMM_EXTKEY 		=> $id_course,
				AJCOMM_AUTHOR		=> getLogUserId(),
				AJCOMM_POSTED 		=> date("Y-m-d H:i:s"),
				AJCOMM_TEXTOF 		=> importVar('text_of'),
				AJCOMM_TREE 		=> '',
				AJCOMM_PARENT 		=> importVar('reply_to'),
				AJCOMM_MODERATED 	=> '0'
			);

			$ax_comm->addComment($comment_data);


			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');

			$ax_rend = new AjaxCommentRender('catalogue', 'lms');

			$man_courseuser 	= new Man_CourseUser();
			$usercourses 		=& $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), true);

			if(Docebo::user()->isAnonymous()) { $ax_comm->canReply(false); }
			else { $ax_comm->canReply(isset($usercourses[$id_course])); }

			$content = '<div style="overflow:scroll;height:500px">';
			$comments = $ax_comm->getCommentByResourceKey($id_course);
			$ax_rend->setCommentToDisplay($comments);
			while(!$ax_rend->isEnd()) {

				$content .= $ax_rend->nextComment();
			}
			//$ax_commgetAddCommentMask($id_course);
			if($ax_comm->isReplyActive()) {

				$content.=$ax_rend->getAddCommentMask_2($id_course);
			}
			$content.="</div>";
			$value = array(
				"next_op" 	=> '',
				"id" 		=> 'course_comment',
				"title" 	=> $lang->def('_COMMENTS'),
				"content" 	=> $content
			);

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);

			break;
		}

		case "delcomment" : {

			require_once($GLOBALS["where_framework"]."/lib/lib.ajax_comment.php");
			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");

			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');

			$comment_id = importVar('comment_id', true, 0);
			$id_course = importVar('id_course', true, 0);
			$ax_comm = new AjaxComment('course', 'lms');
			$ax_comm->deleteComment($comment_id);

			$ax_rend = new AjaxCommentRender('catalogue', 'lms');


			$man_courseuser 	= new Man_CourseUser();
			$usercourses 		=& $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), true);

			if(Docebo::user()->isAnonymous()) { $ax_comm->canReply(false); }
			else { $ax_comm->canReply(isset($usercourses[$id_course])); }

			$content = '<div style="overflow:auto;height:500px">';
			$comments = $ax_comm->getCommentByResourceKey($id_course);
			$ax_rend->setCommentToDisplay($comments);
			while(!$ax_rend->isEnd()) {

				$content .= $ax_rend->nextComment();
			}
			//$content.= $ax_rend->getAddCommentMask($id_course);
			if($ax_comm->isReplyActive()) {


				$content.=$ax_rend->getAddCommentMask_2($id_course);
			}

			$content.="</div>";
			$value = array(
				"next_op" 	=> '',
				"id" 		=> 'course_comment',
				"title" 	=> $lang->def('_COMMENTS'),
				"content" 	=> $content
			);

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
			break;
		}

		case "comment_it" : {

			require_once($GLOBALS["where_framework"]."/lib/lib.ajax_comment.php");
			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");

			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'catalogue', 'lms');

			$id_course = importVar('id_course', true, 0);
			$ax_comm = new AjaxComment('course', 'lms');
			$ax_rend = new AjaxCommentRender('catalogue', 'lms');

			$man_courseuser 	= new Man_CourseUser();
			$usercourses 		=& $man_courseuser->getUserSubscriptionsInfo(getLogUserId(), true);

			if(Docebo::user()->isAnonymous()) { $ax_comm->canReply(false); }
			else { $ax_comm->canReply(isset($usercourses[$id_course])); }

			$content = '<div style="overflow:scroll;height:500px">';
			$comments = $ax_comm->getCommentByResourceKey($id_course);
			$ax_rend->setCommentToDisplay($comments);
			while(!$ax_rend->isEnd()) {

				$content .= $ax_rend->nextComment();
			}
			//$content.= $ax_rend->getAddCommentMask($id_course);
			if($ax_comm->isReplyActive()) {

				$content.=$ax_rend->getAddCommentMask_2($id_course);
			}
			$content.="</div>";
			$value = array(
				"next_op" 	=> '',
				"id" 		=> 'course_comment',
				"title" 	=> $lang->def('_COMMENTS'),
				"content" 	=> $content
			);

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		};break;
		case "course_materials" : {
			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");
			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'course', 'lms');

			$id_course = importVar('id_course', true);
			$course_man = new DoceboCourse($id_course);
			$course_mat = $course_man->getValue('img_material');

			$html = '<ul class="course_editions">';
			if($course_mat != '') {

				$html .= '<li><b>['.$course_man->getValue('code').'] '.$course_man->getValue('name').' '.$course_mat.'</b>'
					.'<div class="align_right">'
					.'<a href="index.php?modname='.( Docebo::user()->isAnonymous()
								? 'login'
								: 'coursecatalogue' ) .'&amp;op=donwloadmaterials'
							.'&amp;id_course='.$id_course.'">'
					.$lang->def('_DOWNLOAD').'</a>'
					.'</div>'
					.'</li>';
			}
			$select_edition = " SELECT idCourseEdition, idCourse, code, name, img_material, date_begin, date_end ";
			$from_edition 	= " FROM ".$GLOBALS["prefix_lms"]."_course_edition";
			$where_edition 	= " WHERE idCourse = '".$id_course."' ";
			$order_edition 	= " ORDER BY date_begin ";
			$re_edition = sql_query($select_edition.$from_edition.$where_edition.$order_edition);

			while($ed_info = sql_fetch_assoc($re_edition)) {

				if($ed_info['img_material'] != '') {

					$html .= '<li><b>['.$ed_info['code'].'] '.$ed_info['name'].'</b><br/><p>';
					if(($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00')) {
						$html .= $lang->def('_EDITIONS');
					}
					if($ed_info['date_begin'] != '0000-00-00' && $ed_info['date_end'] != '0000-00-00') {

						$html .= ' '.str_replace(	array('[date_begin]', '[date_end]'),
												array(Format::date($ed_info['date_begin'], 'date'),
													Format::date($ed_info['date_end'], 'date')),
												$lang->def('_EDTION_TIME'));
					}
					$html .= '<div class="popup_materials">'
							.'<a href="index.php?modname='.( Docebo::user()->isAnonymous()
								? 'login'
								: 'coursecatalogue' ) .'&amp;op=donwloadmaterials'
								.'&amp;id_course='.$ed_info['idCourse'].'&amp;edition_id='.$ed_info['idCourseEdition'].'">'
							.'<span>'.$lang->def('_DOWNLOAD').'</span></a>'
							.'</div>';

					$html .= '</li>';
				}
			}
			$html .= '</ul>';

			$value = array(
				"next_op" 	=> '',
				"id" 		=> 'course_materials',
				"title" 	=> $lang->def('_MATERIALS'),
				"content" 	=> $html
			);

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		};break;
		case "play_demo" : {
			require_once($GLOBALS["where_lms"]."/lib/lib.course.php");
			require_once(_base_.'/lib/lib.multimedia.php');

			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'course', 'lms');

			$id_course = importVar('id_course', true);
			$course_man = new DoceboCourse($id_course);
			$course_demo = $course_man->getValue('course_demo');

			$ext = end(explode('.', $course_demo));

			$value = array(
				"next_op" 	=> '',
				"id" 		=> 'course_materials',
				"title" 	=> $lang->def('_DEMO'),
				"content" 	=> getEmbedPlay('/appLms/'.Get::sett('pathcourse'), $course_demo, $ext, '450', '450', false, false, '../../'.$GLOBALS['where_files_relative'])
			);
			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		};break;
		default : {
			$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
			$lang->setGlobal();
			$lang =& DoceboLanguage::createInstance( 'catalogue', 'framework');

			$value = array(
				"next_op" 	=> 'prova',
				"id" 		=> 'prova',
				"title" 	=> 'creazione window',
				"content" 	=> 'prova di creazione di una window'
			);

			require_once(_base_.'/lib/lib.json.php');

			$json = new Services_JSON();
			$output = $json->encode($value);
			aout($output);
		};break;

	}

}

?>