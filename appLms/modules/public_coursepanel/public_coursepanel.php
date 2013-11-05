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
 * COURSE PANEL
 * 
 * This module is a facilitator for the users re-training maded by the public administrators.
 * The public administrator will be informed of the users that are approching the expiration date for theirs
 * competences and allow the administrator to re-enroll them to courses that refresh those competences in order to keep
 * the final users up to date.
 */
function coursePanel() {
	//check permissions
	checkPerm('view');
	$can_mod = checkPerm('mod', true);

	//required libraries
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_adm_.'/lib/lib.publicadminmanager.php');
	require_once(_lms_.'/lib/lib.course.php');
	require_once(_lms_.'/lib/lib.date.php');
	require_once(_lms_.'/lib/lib.competences.php');

	//back page link
	$lang =& DoceboLanguage::CreateInstance('public_coursepanel', 'lms');
	$back_ui = getBackUi('index.php', $lang->def('_BACK'));

	$db = DbConn::getInstance();
	$man_competences = new Competences_Manager();

	//check the admin level of the current user, if it's not an admin or the idst is invalid, return an error
	$id_pa = getLogUserId();
	if (!$id_pa) {
		//error: the user is invalid
		cout($back_ui.$lang->def('_INVALID_ADMIN').$back_ui, 'content');
		return;
	}
/*
	//months considered
	$this_month = (int)date("m");
	$month_1 = ((int)$this_month-1+1) % 12; $month_1++;
	$month_2 = ((int)$this_month-1+2) % 12; $month_2++;

	$months = array(
		$lang->def('MONTH_'.((int)$this_month<10 ? '0' : '').(int)$this_month),
		$lang->def('MONTH_'.((int)$month_1<10 ? '0' : '').(int)$month_1),
		$lang->def('MONTH_'.((int)$month_2<10 ? '0' : '').(int)$month_2)
	);
*/
	$acl_man = Docebo::user()->getAclManager();
	$admin_manager = new PublicAdminManager();
	$array_users = array();
	$idst_associated = $admin_manager->getAdminTree($id_pa);
	$array_users =& $acl_man->getAllUsersFromIdst($idst_associated);
	$array_users = array_unique($array_users);

	if (empty($array_users)) {
		//error: no users to deal with
		cout($back_ui.$lang->def('_NO_USERS').$back_ui, 'content');
		return;
	}

	//already selected competence and course, if exist
	$sel_competence = Get::req('sel_competence', DOTY_INT, false);
	$sel_course = Get::req('sel_course', DOTY_INT, false);

	//$lang_code = Get::req('language', DOTY_ALPHANUM, getLanguage());
	$comps_1 = array();
	$comps_2 = array();

	//retrieve competences list for dropdown menu -- two steps filter
	//retrieve competences by users
	$query_competences_1 = "SELECT c.id_competence, cu.id_user FROM ".
				" %lms_competence as c JOIN ".
				" %lms_competence_user as cu ON ".
				" (c.id_competence=cu.id_competence AND cu.id_user IN (".implode(",", $array_users)."))";
	$res_competences_1 = $db->query($query_competences_1);
	while (list($id_comp, $id_user) = $db->fetch_row($res_competences_1)) {
		$comps_1[$id_comp] = $id_user;
	}

	if (empty($comps_1)) {
		//error, no competences
		cout($back_ui.$lang->def('_NO_COMPETENCES').$back_ui, 'content');
		return;
	}

	//get number of days in which check if a course or a edition is starting
	$course_check_time = Docebo::user()->preference->getAdminPreference('admin_rules.course_check_time'); //days from today

	//retrieve competences by courses
	$courses_dropdown = array();
	$query_competences_2 = "(SELECT c.id_competence, t.idCourse, t.name, t.course_type, cc.retraining FROM ".
				" %lms_competence as c JOIN ".
				" %lms_competence_course as cc JOIN ".
				" %lms_course as t ON ".
				"(c.id_competence=cc.id_competence AND cc.id_course=t.idCourse) ".
				"WHERE t.course_type IN ('classroom', 'elearning') AND c.id_competence IN (".implode(",", array_keys($comps_1)).")".
				")";/*." UNION (SELECT c.id_competence, t.idCourse, t.name, t.course_type, cc.retraining FROM ".
				$GLOBALS['prefix_lms']."_competence as c JOIN ".
				$GLOBALS['prefix_lms']."_competence_course as cc JOIN ".
				$GLOBALS['prefix_lms']."_course as t ON ".
				"(c.id_competence=cc.id_competence AND cc.id_course=t.idCourse) ".
				"WHERE t.course_type IN ('classroom', 'elearning') AND c.id_competence IN (".implode(",", array_keys($comps_1)).")".
				")";*/
	$res_competences_2 = $db->query($query_competences_2);
	while (list($id_comp, $id_course, $course_name, $c_type, $retraining) = $db->fetch_row($res_competences_2)) {
		$comps_2[$id_comp] = $id_course;
		
		if (!isset($courses_dropdown[$id_comp])) $courses_dropdown[$id_comp] = array();
		$courses_dropdown[$id_comp][$id_course] = $course_name.' ('.$c_type.')'.($retraining>0 ? ' ['.$lang->def('_RETRAINING').']' : '');
	}

	if (empty($comps_2)) {
		//error, no competences
		cout($back_ui.$lang->def('_NO_COMPETENCES').$back_ui, 'content');
		return;
	}

	$comps = array_keys($comps_2);

	unset($comps_1);
	unset($comps_2);

	$comps_names = $man_competences->getCompetencesName($comps);

	//----------------------------------------------------------------------------

	//retrieve courses for competences
	//$courses_dropdown = array();

	//check if selection for competence and course is valid
	if (!$sel_competence || !array_key_exists($sel_competence, $comps)) {
		$arr = array_keys($courses_dropdown);
		$sel_competence = $arr[0];//$comps[0];
	}
	if (!$sel_course || !isset($courses_dropdown[$sel_competence][$sel_course])) {
			foreach ($courses_dropdown as $key=>$val)
				if (count($val) > 0) {
					$sel_competence = $key;
					$arr = array_keys($val);
					$sel_course = $arr[0];
					break;
				}
	}

	//check the course type (base or retraining)
	$is_retraining = $man_competences->isRetrainingCourse($sel_course, $sel_competence);

	//make script for courses dropdown auto-updating
	require_once(_base_.'/lib/lib.json.php');
	$json = new Services_JSON();
	$var = array();
	foreach ($courses_dropdown as $id_comp=>$courses_list) {
		$content = "{id_competence: ".(int)$id_comp.", courses: [";
		$clist = array();
		foreach ($courses_list as $id_course=>$name) {
			$clist[] = '{id_course: '.(int)$id_course.', name: '.$json->encode($name).'}';
		}
		$content .= implode(',', $clist)."]}";
		$var[] = $content;
	}
	//addYahooJs(array('dom'=>'dom-min.js', 'event'=>'event-min.js', 'selector'=>'selector-beta-min.js'));
	YuiLib::load('selector');
	cout('<script type="text/javascript">
			var sel_options = ['.implode(',', $var).'];
			YAHOO.util.Event.onDOMReady(function() {
				var s1 = YAHOO.util.Dom.get("competence_selector");
				var s2 = YAHOO.util.Dom.get("course_selector");
				YAHOO.util.Event.addListener(s1, "change", function(e) {
					var opt, id_comp = this.value;
					for (var i=0; i<sel_options.length; i++) {
						if (sel_options[i].id_competence == id_comp) {
							s2.innerHTML = "";
							for (var j=0; j<sel_options[i].courses.length; j++) {
								opt = new Option(sel_options[i].courses[j].name, sel_options[i].courses[j].id_course);
								s2.add(opt, null);
							}
							break;
						}
					}
				});
			});
		</script>', 'page_head');

	//----------------------------------------------------------------------------

	$table_head_style = array('', 'image', 'align_center');
	$table_head_content = array($lang->def('_USERNAME'), $lang->def('_MANDATORY'), $lang->def('_DATE_EXPIRE'));

	$man_course = new Man_Course();
	$date_man = new DateManager();
	$course_info = $man_course->getCourseInfo($sel_course);
//echo '<pre>'.print_r($course_info, true).'</pre>';
	if ($course_info['course_type'] == 'elearning') {

		$editions[] = (int)$sel_course;
		$subscribed[$sel_course] = /*$man_course->*/getSubscribed($sel_course);
		$table_head_content[] = $course_info['name'];
		$table_head_style[] = 'align_center';

	} elseif ($course_info['course_type'] == 'classroom') {

		//retrieve editions for table columns and subscribed users
		$subscribed = array();
		$editions = array();
		if ($course_check_time > 0) {

			$id_list = array();
			$query_begindates = "SELECT dy.id_date, MIN(dy.date_begin) as date_begin "
				." FROM %lms_course_date as dt "
				." JOIN %lms_course_date_day as dy ON (dy.id_date = dt.id_date) "
				." WHERE dt.id_course='".(int)$sel_course."' GROUP BY dy.id_date ORDER BY dy.date_begin";
			$res = $db->query($query_begindates);
			while (list($id_date, $date_begin) = $db->fetch_row($res)) {
				if ($date_begin >= date("Y-m-d") && $date_begin <= date("Y-m-d", strtotime("+".$course_check_time." days"))) $id_list[] = $id_date;
			}
/*
			$query_editions = "SELECT dt.id_date, dt.id_course, dt.code, dt.name, MIN(dy.date_begin) as min "
				." FROM %lms_course_date as dt "
				." JOIN %lms_course_date_day as dy ON (dy.id_date = dt.id_date) "
				." WHERE dt.id_course='".(int)$sel_course."' AND dy.date_begin BETWEEN NOW() AND '"
				.date("Y-m-d", strtotime("+".$course_check_time." days"))."' GROUP BY dt.id_date ORDER BY dy.date_begin";
*/
			$query_editions = "SELECT id_date, id_course, code, name FROM %lms_course_date WHERE "
				.(count($id_list) > 0 ? "id_date IN (".implode(",", $id_list).") " : "0");
		} else {
			$query_editions = "SELECT id_date, id_course, code, name FROM %lms_course_date WHERE id_course='".(int)$sel_course."' ";
		}
		$res_editions = $db->query($query_editions);
		if (sql_num_rows($res_editions) > 0) {
			while (list($id_edition, $id_course, $code, $name) = $db->fetch_row($res_editions)) {
				$table_head_style[] = 'align_center';

				$date_info = $date_man->getDateInfo($id_edition);
				$table_head_content[] = $name.'<br />'
					.$lang->def('_DATE_BEGIN').': '.Format::date($date_info['date_begin'], 'date').'<br />'
					.$lang->def('_DATE_END').': '.Format::date($date_info['date_end'], 'date').'<br />'
					.$lang->def('_AVAIL_PLACES').': <span id="available_places_count_'.$id_edition.'">'.(int)($date_info['max_par'] - $date_info['user_subscribed']).'</span> ('.$date_info['max_par'].')';

				$editions[] = $id_edition;
				$subscribed[$id_edition] = $date_man->getDateSubscribed($id_edition);
			}
		} else {

		}
	}
	//retrieve expiration time for every user
	$expiries = array();
	$query_check = "SELECT id_user, date_expire "
				." FROM %lms_competence_user "
				." WHERE id_user IN (".implode(",", $array_users).") AND id_competence='".(int)$sel_competence."'";
	$res_check = $db->query($query_check);
	while (list($id_user, $date_expire) = $db->fetch_row($res_check)) {
		$expiries[$id_user] = $date_expire;
	}


	//filter array of user ids by competence
	$filtered_users = array();

//------------------------------------------------------------------------------
	$required_filter = Get::req('required_filter', DOTY_INT, 0);
	$expire_duration = $man_competences->getCompetenceExpirationCheckTime($id_comp);

	if ($is_retraining || $required_filter > 0) {

			$query_filter_time = "";
			if ($expire_duration > 0) { //if a time for checking expiration has been set, then filter on a time period
				$date_begin = date("Y-m-d", strtotime("-".$expire_duration." days"));
				$date_end = date("Y-m-d", strtotime("+".$expire_duration." days"));
				$query_filter_time = " AND date_expire>'".$date_begin."' AND date_expire<'".$date_end."'";
			}

			$query_filter = "SELECT id_user FROM %lms_competence_user WHERE id_user IN (".implode(",", $array_users).") AND id_competence='".(int)$sel_competence."' "
				.$query_filter_time;
			$res_filter = $db->query($query_filter);
			while (list($idst) = $db->fetch_row($res_filter)) {
				$filtered_users[] = $idst;
			}

	}

	if (!$is_retraining || $required_filter > 0) {

			//get user with required competence which is not yet obtained
			$required_users = array();

			$req_data = $man_competences->GetCompetence($sel_competence);
			$already = array();
			$query = "SELECT id_user, score_init, score_got FROM %lms_competence_user WHERE id_competence='".(int)$sel_competence."'";
			$res = $db->query($query);
			while (list($id_user, $score_init, $score_got) = $db->fetch_row($res)) {
				if ($req_data['type'] == 'score') {
					//if the competence assignment exists in DB, but the total score is 0, then it's considered as non-assigned
					if (((int)$score_init + (int)$score_got) > 0) $already[] = $id_user;
				} else {
					$already[] = $id_user;
				}
			}

			$already = array_unique($already);
			$req_users = array_diff($array_users, $already);

			//get required competences not got from users
			$query = "";
			if ($req_data['type'] == 'score') {
				$query = "SELECT u.idst FROM %lms_competence_required as cr "
					." JOIN %adm_user as u ON (cr.idst = u.idst)"
					." WHERE cr.idst IN (".implode(",", $req_users).") AND cr.id_competence=".(int)$sel_competence."";
			} else {
				$query = "SELECT u.idst FROM %lms_competence_required as cr "
					." JOIN %adm_user as u ON (cr.idst = u.idst) "
					." WHERE cr.idst IN (".implode(",", $req_users).") AND cr.id_competence=".(int)$sel_competence."";
			}

			$res = $db->query($query);
			if (sql_num_rows($res) > 0) {
				while (list($idst) = $db->fetch_row($res)) {
					$required_users[] = $idst;
				}
			}

			//get users expired from too long time
			if ($expire_duration > 0) {
				$query = "SELECT id_user FROM %lms_competence_user "
					." WHERE date_expire<'".date("Y-m-d", strtotime("-".$expire_duration." days"))."' AND date_expire<>'0000-00-00 00:00:00' "
					." AND id_competence=".(int)$sel_competence;
				$res = $db->query($query);
				while (list($idst) = $db->fetch_row($res)) {
					$required_users[] = $idst;
				}
			}

			//merge results
			if (count($required_users) > 0)
				if (count($filtered_users) > 0)
					$filtered_users = array_merge($filtered_users, $required_users);
				else
					$filtered_users = $required_users;

	}

//------------------------------------------------------------------------------

	$filtered_users = array_unique($filtered_users);

	//draw table
	$table = new Table(0);
	$form = new Form();

	$table->addHead($table_head_content, $table_head_style);

	$totals = array();
	for ($i=0; $i<count($editions); $i++) {
		$totals[$i] = 0;
	}

	//check the expiration time  of the current competence
	list($expiry_time) = $db->fetch_row($db->query("SELECT expiration FROM %lms_competence WHERE id_competence='".(int)$sel_competence."'"));
	if ($expiry_time === false) {
		//error, we need a number (it should never enter this branch though)
		//error, no competences
		cout($back_ui.$lang->def('_NO_EXPIRATION_TIME').$back_ui, 'content');
		return;
	}

	$checkbox_list_script = array();
	foreach ($editions as $id_edition) $checkbox_list_script[$id_edition] = array();
	$expiring_users_count = 0;
	$req_count = 0;

	//filtered data to consider in saving operation
	$to_consider = array('users'=>array(), 'editions'=>array());
	for ($i=0; $i<count($editions); $i++) $to_consider['editions'][] = $editions[$i];

	//retrieve users' data for table rows (little fast query)
	if (count($filtered_users) > 0) {
			$query_users = "SELECT idst, userid, lastname, firstname FROM %adm_user WHERE idst IN (".implode(",", $filtered_users/*$array_users*/).") ORDER BY userid, lastname, firstname";
			$res_users = $db->query($query_users);

			//retrieve users with required competence
			$just_required = array();
			$query_req = "SELECT idst FROM %lms_competence_required "
				." WHERE id_competence=".(int)$sel_competence." AND idst IN (".implode(",", $filtered_users).")";
			$res_req = $db->query($query_req);
			while (list($idst) = $db->fetch_row($res_req)) $just_required[] = $idst;

			while (list($idst, $userid, $firstname, $lastname) = $db->fetch_row($res_users)) {
				$line = array();

				//filtered users to consider in saving operations
				$to_consider['users'][] = $idst;

				//check if the expiring date of the competence is less than 30 days from now or it's already expired (change bg color then)
				$user_expiring = false;
				$background = "";
				if ($expiry_time>0) {
					if (isset($expiries[$idst])) {
						//$time1 = fromDateTimeToTimestamp($expiries[$idst]) + $expiry_time * 24 * 3600;
						//$time2 = time();
						//if (($time2-$time1) < 2592000) $user_expiring = true;
						if ($expiries[$idst] < date("Y-m-d H:i:s")) $user_expiring = true;
					}
				}
				if ($user_expiring) {
					$background .= 'bg_highlight';
					$expiring_users_count++;
				}

				$line[] = $acl_man->relativeId($userid)."&nbsp;(".$firstname."&nbsp;".$lastname.")"; //swap these
				$is_req = in_array($idst, $just_required);
				if ($is_req) $req_count++;
				$line[] = ($is_req ? '<image src="'.getPathImage('framework').'standard/flag.gif" />' : '');
				$line[] = '<div class="'.$background.'">'
					.(isset($expiries[$idst]) ? Format::date($expiries[$idst], "date") : '-')
					.'</div>';

				for ($i=0; $i<count($editions); $i++) {

					//check if the actual considered user is subscribed to this class' edition (then flag the checkbox)
					$is_subscribed = false;
					if (isset($subscribed[$editions[$i]][$idst])) {
							$is_subscribed = true;
							$totals[$i]++; //update total subscriptions
					}

					if ($course_info['course_type'] == 'elearning') {

						$line[] = '<div class="align_center">'
							.$form->getInputCheckbox('subscriptions_'.$idst, 'subscriptions['.$idst.']['.$editions[$i].']', 1, $is_subscribed, false)
							.'</div>';
						$checkbox_list_script[$editions[$i]][] = '"subscriptions_'.$idst.'"';

					} elseif ($course_info['course_type'] == 'classroom') {

						$line[] = '<div class="align_center">'
							.$form->getInputCheckbox('subscriptions_'.$idst.'_'.$editions[$i], 'subscriptions['.$idst.']['.$editions[$i].']', 1, $is_subscribed, false)
							.'</div>';
						$checkbox_list_script[$editions[$i]][] = '"subscriptions_'.$idst.'_'.$editions[$i].'"';

					}

				}

				$table->addBody($line);
			}

	}

	//totals line
	$line = array();
	$line[] = $lang->def('_TOTAL');
	$line[] = '<div class="align_center">'.(int)$req_count.'</div>';
	$line[] = '<div class="align_center">'.(int)$expiring_users_count.'</div>';
	for ($i=0; $i<count($editions); $i++) {
		$line[] = '<div class="align_center">'.(int)$totals[$i].'</div>';
	}
	$table->addFoot($line);
//echo '<pre class="align_left">'.print_r($checkbox_list_script, true).'</pre>';
	//script to check available places in real-time
	$avail_script = '<script type="text/javascript">
			function setupAvailablePlaces() {';
	foreach ($editions as $id_edition) {
		$date_info = $date_man->getDateInfo($id_edition);
		$avail_script .= '
				YAHOO.util.Event.addListener(['.implode(',', $checkbox_list_script[$id_edition]).'], "click", function(e, o) {
					max_avail_places_'.$id_edition.' = '.(int)($date_info['max_par'] - $date_info['user_subscribed']).';
					var i, edition_boxes = YAHOO.util.Dom.get(['.implode(',', $checkbox_list_script[$id_edition]).']);
					var edition_count = 0, edition_max = '.(int)$date_info['max_par'].';
					for (i=0; i<edition_boxes.length; i++) {
						if (edition_boxes[i].checked) edition_count++;
					}
					YAHOO.util.Dom.get("available_places_count_'.$id_edition.'").innerHTML = ""+(edition_max - edition_count);
					if (edition_count >= edition_max) {
						for (i=0; i<edition_boxes.length; i++) {
							if (!edition_boxes[i].checked) edition_boxes[i].disabled = true;
						}
					} else {
						for (i=0; i<edition_boxes.length; i++) {
							if (edition_boxes[i].disabled) edition_boxes[i].disabled = false;
						}
					}
				});';
	}
	$avail_script .= '}
			setupAvailablePlaces();
		</script>';
	cout($avail_script, 'page_head');

	//any error message from previous operations?
	$message = "";
	$err = Get::req('err', DOTY_MIXED, false);
	switch ($err) {
		case 'invalid': {
			$message .= getErrorUi($lang->def('_ERROR_WHILE_SUBSCRIBING').'.');
		} break;

		case 'ok': {
			$content = $lang->def('_SUBSCRIBE_SUCCESSFULL');
			$count = Get::req('count', DOTY_MIXED, false);
			if ($count !== false && is_numeric($count)) {
				$content .= "&nbsp;(".$lang->def('_NUM_SUBSCRIBED').":&nbsp;".$count.")";
			}
			$message .= getResultUi($content);
		} break;
	}

	//print page
	cout(getTitleArea($lang->def('_COURSEPANEL'), 'coursepanel')
		.'<div class="std_block">'.$message
		.$back_ui, 'content');


	cout('<link rel="stylesheet" type="text/css" href="'./*$GLOBALS['where_framework_relative']*/Get::rel_path('base').'/addons/yui/grids/grids-min.css">', 'page_head');

	$comps_list = $man_competences->GetCompetencesList();

	//write period of checking for expiration, from beginning date to ending date
	$date_1 = ($expire_duration > 0 ? date("Y-m-d H:i:s", strtotime("-".$expire_duration." days")) : "");
	$date_2 = ($expire_duration > 0 ? date("Y-m-d H:i:s", strtotime("+".$expire_duration." days")) : "");

	if ($date_1 != "" && $date_2 != "")
		$date_period = Format::date($date_1, "date").' - '.Format::date($date_2, "date");
	else
		$date_period = '('.$lang->def('_ALL').')';

	$selector = "";

	//$selector .= $form->openElementSpace();
	$selector .= '<div class="yui-g"><div id="select_1" class="yui-u first align_left">';

	$selector .= $form->openForm('action_panel', "index.php?modname=public_coursepanel&op=coursepanel");
	$selector .= $form->openElementSpace();
	$selector .= '<p class="align_left">'.$lang->def('_EXPIRING_USERS_FOR_PERIOD').':&nbsp;<b>'./*implode(", ", $months)*/$date_period.'</b>;</p>';
	//$selector .= $lang->def('_FOR_COMPETENCE').':&nbsp;';
	$selector .= $form->getDropDown($lang->def('_FOR_COMPETENCE').':&nbsp;', 'competence_selector', 'sel_competence', $comps_names, $sel_competence, '');
	$selector .= $form->getDropDown($lang->def('_FOR_COURSE').':&nbsp;', 'course_selector', 'sel_course', $courses_dropdown[$sel_competence], $sel_course, '');
	$selector .= $form->openButtonSpace();
	$selector .= $form->getButton('update', 'update', $lang->def('_UPDATE'));
	$selector .= $form->closeButtonSpace();
	$selector .= $form->closeElementSpace();
	$selector .= $form->closeForm();

	$selector .= '</div><div id="select_2" class="yui-u align_left">';

	//if (count($comps_list)>0) { ...
	$selector .= $form->openElementSpace();
	$selector .= $form->openForm('action_panel', "index.php?modname=public_coursepanel&op=expired");
	$selector .= $form->getHidden('expire_sel_competence', 'sel_competence', $sel_competence);
	$selector .= $form->getHidden('expire_sel_course', 'sel_course', $sel_course);
	$selector .= $form->getDropdown($lang->def('_CHOOSE_COMPETENCE_TO_SEE_EXPIRED'), 'expired_selector', 'expired_selector', $comps_list/*, $sel_expired*/);
	$selector .= $form->openButtonSpace();
	$selector .= $form->getButton('update_expired', 'update_expired', $lang->def('_UPDATE'));
	$selector .= $form->closeButtonSpace();
	$selector .= $form->closeForm();
	$selector .= $form->getBreakRow();
	$selector .= $form->openForm('action_panel', "index.php?modname=public_coursepanel&op=required");
	$selector .= $form->getHidden('expire_sel_competence_req', 'sel_competence_req', $sel_competence);
	$selector .= $form->getHidden('expire_sel_course_req', 'sel_course_req', $sel_course);
	$selector .= $form->getDropDown($lang->def('_CHOOSE_REQUIRED_COMPETENCES'), 'required_selector', 'required_selector', $comps_list/*, $sel_expired*/);
	$selector .= $form->openButtonSpace();
	$selector .= $form->getButton('update_required', 'update_required', $lang->def('_UPDATE'));
	$selector .= $form->closeButtonSpace();
	$selector .= $form->closeForm();
	$selector .= $form->closeElementSpace();

	$selector .= '</div></div><div class="no_float"></div>';
	//$selector .= $form->closeElementSpace();

	//legend which explains what highlighted users mean
	$legend = '<div class="align_left"><div style="display:inline-block;width:12px;height:12px;" class="bg_highlight"></div> = '
		.$lang->def('_EXPIRED_COMPETENCE').'</div>';

	cout('<div class="align_center">'.$selector.'</div>', 'content');
	cout($form->openForm('comp_panel', "index.php?modname=public_coursepanel&op=savepanel"), 'content');
	cout($form->getHidden('sel_competence', 'sel_competence', $sel_competence), 'content');
	cout($form->getHidden('sel_course', 'sel_course', $sel_course), 'content');
	cout($form->openElementSpace(), 'content');
	cout('<div class="align_left">', 'content');
	if ($is_retraining)
		cout($form->getCheckbox($lang->def('_SHOW_REQUIRED'), 'required_filter', 'required_filter', 1, $required_filter), 'content');
	else
		cout($form->getCheckbox($lang->def('_SHOW_EXPIRED'), 'required_filter', 'required_filter', 1, $required_filter), 'content');
	cout('</div>', 'content');
	cout($form->closeElementSpace(), 'content');
	cout('<div class="align_left">', 'content');
	cout('<p>'.($is_retraining ? $lang->def('_IS_RETRAINING_COURSE') : $lang->def('_IS_TRAINING_COURSE')).'</p>', 'content');
	if ($course_info['course_type'] == 'classroom' && $course_check_time > 0){
		$check_date_1 = Format::date(date("Y-m-d"), "date");
		$check_date_2 = Format::date(date("Y-m-d H:i:s", strtotime("+".$course_check_time." days")), "date");
		cout('<p>'.$lang->def('_COURSE_CHECKING_PERIOD').': '.$check_date_1.' - '.$check_date_2.'</p>', 'content');
	}
	cout('</div>', 'content');
	if (empty($editions)) {
		//no editions to display (it should have been pre-selected only courses with available editions)
		cout('<p>'.$lang->def('_NO_CONTENT').'</p>', 'content');
	} else {
		cout($table->getTable(), 'content');
	}
	cout($legend, 'content');
	cout($form->getHidden('to_consider', 'to_consider', base64_encode($json->encode($to_consider))), 'content');
	cout($form->openButtonSpace()
		.$form->getButton('save', 'save', $lang->def('_SAVE'))
		.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace(), 'content');
	cout($form->closeForm().$back_ui.'</div>', 'content');

	cout('<script type="text/javascript">
			YAHOO.util.Event.addListener("required_filter", "click", function(e) {
				var show_required = this.checked, comp_form = YAHOO.util.Dom.get("comp_panel");
				comp_form.action = comp_form.action.replace("&op=savepanel", "&op=coursepanel");
				comp_form.submit();
			});
		</script>', 'page_head');

}



function _removeCourseSubscription($id_course, $id_user, $lv_group, $edition_id=0) {
/*
	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt=new TimeTable();
	// ----------------------------------------
	$resource="user";
	$resource_id=$id_user;
	if ($edition_id > 0) {
		$consumer="course_edition";
		$consumer_id=$edition_id;
	}
	else {
		$consumer="course";
		$consumer_id=$id_course;
	}
	// ----------------------------------------
	$tt->deleteEvent(FALSE, $resource, $resource_id, $consumer, $consumer_id, $start_date, $end_date);
*/
	$db = DbConn::getInstance();
	$acl_man = Docebo::user()->getAclManager();
	$acl_man->removeFromGroup($lv_group, $id_user);

	if ($edition_id > 0) {
		$group ='/lms/course_edition/'.$edition_id.'/subscribed';
		$group_idst =$acl_man->getGroupST($group);
		$acl_man->removeFromGroup($group_idst, $id_user);
	}

	return $db->query("DELETE FROM %adm_courseuser
		WHERE idUser = '".$id_user."' AND idCourse = '".(int)$id_course."'
		AND edition_id='".(int)$edition_id."'");
}



function savePanel() {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_adm_.'/lib/lib.publicadminmanager.php');
	require_once(_lms_.'/lib/lib.course.php');
	require_once(_lms_.'/lib/lib.date.php');
	require_once(_lms_.'/lib/lib.competences.php');
	
	$save = Get::req('save', DOTY_MIXED, false);
	$undo = Get::req('undo', DOTY_MIXED, false);
	$update = Get::req('update', DOTY_MIXED, false);

	$db = DbConn::getInstance();
	$lang =& DoceboLanguage::CreateInstance('public_coursepanel', 'lms');
	$man_competences = new Competences_Manager();

	//back page link
	$back_ui = getBackUi('index.php?modname=public_coursepanel&op=coursepanel', $lang->def('_BACK'));
	
	cout(getTitleArea($lang->def('_COURSEPANEL'), 'coursepanel').'<div class="std_block">'.$back_ui, 'content');

	$sel_competence = Get::req('sel_competence', DOTY_INT, false);
	$sel_course = Get::req('sel_course', DOTY_INT, false);
	$required_filter = Get::req('required_filter', DOTY_INT, 0);
	$is_retraining = $man_competences->isRetrainingCourse($sel_course, $sel_competence);
	$back_url = "index.php?modname=public_coursepanel&op=coursepanel&sel_competence=".(int)$sel_competence."&sel_course=".(int)$sel_course;
	if ($required_filter > 0) $back_url .= '&required_filter=1';

	$to_consider = Get::req('to_consider', DOTY_MIXED, false);
	if ($to_consider) {
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$to_consider = $json->decode(base64_decode($to_consider));
	} else {
		$to_consider = array('users'=>array(), 'editions'=>array());
	}

	if ($undo) {

		Util::jump_to("index.php");

	} elseif ($save) {

		//change users' subscription to course editions
		$data = Get::req('subscriptions', DOTY_MIXED, array());

		$man_course = new Man_Course();
		$course_info = $man_course->getCourseInfo($sel_course);

		if (!$sel_competence || !$sel_course) {
			//error ...
			Util::jump_to($back_url."&err=invalid");
		}

		//check the admin level of the current user, if it's not an admin or the idst is invalid, return an error
		$id_pa = getLogUserId();
		if (!$id_pa) {
			Util::jump_to($back_url."&err=invalid");
		}

		$acl_man = new DoceboACLManager();
		$admin_manager = new PublicAdminManager();
		$array_users = array();
		$idst_associated = $admin_manager->getAdminTree($id_pa);
		$array_users =& $acl_man->getAllUsersFromIdst($idst_associated);
		$array_users = array_unique($array_users);

		if (empty($array_users)) {
			Util::jump_to($back_url."&err=invalid");
		}

		//filter array of user ids by competence
		$filtered_users = array();
		$query_filter = "SELECT id_user FROM %lms_competence_user WHERE id_user IN (".implode(",", $array_users).") AND id_competence='".(int)$sel_competence."'";
		$res_filter = $db->query($query_filter);
		while (list($idst) = $db->fetch_row($res_filter)) {
			$filtered_users[] = $idst;
		}

		$subs_limit = array();
		$subs_count = array();

		if ($course_info['course_type'] == 'elearning') {

				$editions[] = $sel_course;
				$subscribed[$sel_course] = /*$man_course->*/getSubscribed((int)$sel_course);
				$course_info = $man_course->getCourseInfo($sel_course);
				$subs_count[$sel_course] = count($data);

		} elseif ($course_info['course_type'] == 'classroom') {

				//get all combinations id_user - id_date
				$date_man = new DateManager();
				$editions = array();
				$query_editions = "SELECT id_date, id_course, code, name FROM %lms_course_date WHERE id_course='".(int)$sel_course."'";
				$res_editions = $db->query($query_editions);
				while (list($id_edition, $id_course, $code, $name) = $db->fetch_row($res_editions)) {
					$editions[] = $id_edition;
					$subscribed[$id_edition] = $date_man->getDateSubscribed($id_edition);

					$date_info = $date_man->getDateInfo($id_edition);
					$subs_limit[$id_edition] = array('max' =>	$date_info['max_par'], 'subs' => $date_info['user_subscribed']);
					$subs_count[$id_edition] = 0;
				}

		}

//------------------------------------------------------------------------------
		//count the users to be subscribed/unsubscribed and check subscription limits, if any

		//subtract users to de-subscribe from courses
		foreach ($subscribed as $id_edition => $users_list) {
			foreach ($users_list as $id_user) {
				if ($course_info['course_type'] == 'elearning') {
					if (!isset($data[$id_user])) $subs_count[$id_edition]--;
				} elseif ($course_info['course_type'] == 'classroom') {
					//check if the current selection match the current subscriptions
					if (!isset($data[$id_user][$id_edition])) $subs_count[$id_edition]--;
				}
			}
		}

		foreach ($data as $user=>$edition_list) { //id user
			foreach ($edition_list as $edition=>$val) { //id date edition
				if (isset($subscribed[$edition][$user])) {
					//already subscribed, do nothing
				} else {
					$subs_count[$edition]++;
				}
			}
		}

		//check if we have enough room to subscribe users
		//{at the moment, rely on js ...}

//------------------------------------------------------------------------------
		//unsubscribe deselected users

		foreach ($subscribed as $id_edition => $users_list) {
			foreach ($users_list as $id_user) {

				if ($course_info['course_type'] == 'elearning') {

					if (!isset($data[$id_user])) {
						if (in_array($id_user, $to_consider['users']) && (in_array($id_edition, $to_consider['editions']))) {
							$group_levels = DoceboCourse::getCourseLevel($sel_course);
							$user_levels = getSubscribedLevel($sel_course, false, false, 0);
							$_res = _removeCourseSubscription($sel_course, $id_user, $group_levels[$user_levels[$id_user]], 0);
						}
					}
					
				} elseif ($course_info['course_type'] == 'classroom') {

					//check if the current selection match the current subscriptions
					if (!isset($data[$id_user][$id_edition])) {
						if (in_array($id_user, $to_consider['users']) && (in_array($id_edition, $to_consider['editions'])))
							$date_man->removeUserFromDate($id_user, $id_edition, $sel_course);
					}

				}

			}
		}


//------------------------------------------------------------------------------

		//check every single user for inscription
		$count = 0;
		$lv_sel = 3; //student level
		$waiting = 0; //don't subscribe as "waiting for approvation"
		
		//retrive id of group of the course for the varioud level
		$level_idst = DoceboCourse::getCourseLevel((int)$sel_course);
		
		//if the group doesn't exists create it
		if(count($level_idst) == 0 || $level_idst[1] == '') $level_idst =& DoceboCourse::createCourseLevel((int)$sel_course);

		foreach ($data as $user=>$edition_list) { //id user
			foreach ($edition_list as $edition=>$val) { //id date edition

				if (isset($subscribed[$edition][$user])) {
					//already subscribed, do nothing
				} else {
					//this user is to be subscribed, do it

					//add to level group of the course
					$acl_man->addToGroup($level_idst[$lv_sel], $user);
					
					// Add in table
					$re = $db->query("INSERT INTO %lms_courseuser (idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)"
							." VALUES ( '".(int)$user."', '".(int)$sel_course."', '0', '".$lv_sel."', '".$waiting."', '".getLogUserId()."', '".date("Y-m-d H:i:s")."' )	");

					//additional operations for editions
					if ($course_info['course_type'] == 'elearning') {

						//...

					} elseif ($course_info['course_type'] == 'classroom') {

						$ret = $date_man->addUserToDate($edition, $user, getLogUserId());
						if ($ret) $count++;

					}

				}

			}
		}

		Util::jump_to($back_url."&err=ok&count=".(int)$count);

	} elseif ($update) {

		$sel_competence = Get::req('sel_competence', DOTY_INT, false);
		$sel_course = Get::req('sel_course', DOTY_INT, false);

		Util::jump_to($back_url);//."&sel_competence=".(int)$sel_competence."&sel_course=".(int)$sel_course);

	} else {
		//...
	}

	cout($back_ui.'</div>', 'content');

}



function expired() {
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_adm_.'/lib/lib.publicadminmanager.php');
	require_once(_lms_.'/lib/lib.course.php');
	require_once(_lms_.'/lib/lib.date.php');
	require_once(_lms_.'/lib/lib.competences.php');

	$db = DbConn::getInstance();
	$lang =& DoceboLanguage::CreateInstance('public_coursepanel', 'lms');
	$sel_competence = Get::req('sel_competence', DOTY_INT, false);
	$sel_course = Get::req('sel_course', DOTY_INT, false);
	$back_url = "index.php?modname=public_coursepanel&op=coursepanel&sel_competence=".(int)$sel_competence."&sel_course=".(int)$sel_course;
	$back_ui = getBackUi($back_url, $lang->def('_BACK'));
	$sel_comp = Get::req('expired_selector', DOTY_INT, 0);

	if ($sel_comp) {

		$id_pa = getLogUserId();
		if (!$id_pa) {
			//...
		}

		$acl_man = new DoceboACLManager();
		$admin_manager = new PublicAdminManager();
		$array_users = array();
		$idst_associated = $admin_manager->getAdminTree($id_pa);
		$array_users =& $acl_man->getAllUsersFromIdst($idst_associated);
		$array_users = array_unique($array_users);

		if (empty($array_users)) {
			//error: no users to deal with
			cout($back_ui.$lang->def('_NO_USERS').$back_ui, 'content');
			return;
		}

		cout(getTitleArea($lang->def('_EXPIRING_USERS'), 'coursepanel').'<div class="std_block">', 'content');

		$table = new Table();
		$head_labels = array($lang->def('_USERNAME'), $lang->def('_NAME'), $lang->def('_DATE_ASSIGN'), $lang->def('_DATE_EXPIRE'));
		$head_style = array('', '', 'align_center', 'align_center');
		$table->addHead($head_labels, $head_style);

		$date = date ("Y-m-d H:i:s", strtotime("+1 months"));
		$now = date("Y-m-d H:i:s");
		//get expiring competences
		$query = "SELECT u.idst, u.userid, u.lastname, u.firstname, cu.date_assign, cu.date_expire FROM %lms_competence_user as cu "
			." JOIN %adm_user as u ON (cu.id_user = u.idst)"
			." WHERE cu.id_user IN (".implode(",", $array_users).") AND date_expire<'".$date."' "
			." AND cu.id_competence=".(int)$sel_comp." ORDER BY u.userid";
		$res = $db->query($query);
		if (sql_num_rows($res) > 0) {
				while (list($idst, $userid, $lastname, $firstname, $assign_date, $expiry_date) = $db->fetch_row($res)) {
					$line = array();

					$line[] = $acl_man->relativeId($userid);
					$line[] = $lastname." ".$firstname;
					$line[] = Format::date($assign_date, 'datetime');
					$line[] = Format::date($expiry_date, 'datetime');

					$table->addBody($line);
				}

				$man_comp = new Competences_Manager();
				$comp_data = $man_comp->GetCompetence($sel_comp);

				//cout(cout(getTitleArea($lang->def('_EXPIRING_USERS'), 'coursepanel').'<div class="std_block">'));
				cout($lang->def('_COMPETENCE').': <b>'.$comp_data['name'].'</b>', 'content');
				cout($back_ui.$table->getTable().$back_ui, 'content');
		} else {
				cout($back_ui.$lang->def('_NO_EXPIRING_USER').$back_ui, 'content');
		}

	} else {
		//error, no competence selected
		cout($back_ui.$lang->def('_NO_COMPETENCE_SELECTED').$back_ui, 'content');
		return;
	}

}



function required() {
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_adm_.'/lib/lib.publicadminmanager.php');
	require_once(_lms_.'/lib/lib.course.php');
	require_once(_lms_.'/lib/lib.date.php');
	require_once(_lms_.'/lib/lib.competences.php');

	$db = DbConn::getInstance();
	$lang =& DoceboLanguage::CreateInstance('public_coursepanel', 'lms');
	$sel_competence = Get::req('sel_competence_req', DOTY_INT, false);
	$sel_course = Get::req('sel_course_req', DOTY_INT, false);
	$back_url = "index.php?modname=public_coursepanel&op=coursepanel&sel_competence=".(int)$sel_competence."&sel_course=".(int)$sel_course;
	$back_ui = getBackUi($back_url, $lang->def('_BACK'));
	$sel_comp = Get::req('required_selector', DOTY_INT, 0);

	if ($sel_comp) {

		$id_pa = getLogUserId();
		if (!$id_pa) {
			//...
		}

		$acl_man = new DoceboACLManager();
		$admin_manager = new PublicAdminManager();
		$array_users = array();
		$idst_associated = $admin_manager->getAdminTree($id_pa);
		$array_users =& $acl_man->getAllUsersFromIdst($idst_associated);
		$array_users = array_unique($array_users);

		if (empty($array_users)) {
			//error: no users to deal with
			cout($back_ui.$lang->def('_NO_USERS').$back_ui, 'content');
			return;
		}

		cout(getTitleArea($lang->def('_REQUIRED_USERS'), 'coursepanel').'<div class="std_block">', 'content');

		$table = new Table();
		$head_labels = array($lang->def('_USERNAME'), $lang->def('_NAME'));
		$head_style = array('', '', 'align_center', 'align_center');
		$table->addHead($head_labels, $head_style);

		//$date = date ("Y-m-d H:i:s", strtotime("+1 months"));
		//$now = date("Y-m-d H:i:s");

		$man_comp = new Competences_Manager();
		$comp_data = $man_comp->GetCompetence($sel_comp);

		$already = array();
		$query = "SELECT id_user, score_init, score_got FROM %lms_competence_user WHERE id_competence='".(int)$sel_comp."'";
		$res = $db->query($query);
		while (list($id_user, $score_init, $score_got) = $db->fetch_row($res)) {
			if ($comp_data['type'] == 'score') {
				//if the competence assignment exists in DB, but the total score is 0, then it's considered as non-assigned
				if (((int)$score_init + (int)$score_got) > 0) $already[] = $id_user;
			} else {
				$already[] = $id_user;
			}
		}

		$already = array_unique($already);
		$array_users = array_diff($array_users, $already);

		//get required competences not got from users
		$query = "";
		if ($comp_data['type'] == 'score') {
			$query = "SELECT u.idst, u.userid, u.lastname, u.firstname FROM %lms_competence_required as cr "
				." JOIN %adm_user as u ON (cr.idst = u.idst)"
				." WHERE cr.idst IN (".implode(",", $array_users).") AND cr.id_competence=".(int)$sel_comp." ORDER BY u.userid";
		} else {
			$query = "SELECT u.idst, u.userid, u.lastname, u.firstname FROM %lms_competence_required as cr "
				." JOIN %adm_user as u ON (cr.idst = u.idst) "
				." WHERE cr.idst IN (".implode(",", $array_users).") AND cr.id_competence=".(int)$sel_comp." ORDER BY u.userid";
		}

		$res = $db->query($query);
		if (sql_num_rows($res) > 0) {
				while (list($idst, $userid, $lastname, $firstname) = $db->fetch_row($res)) {
					$line = array();

					$line[] = $acl_man->relativeId($userid);
					$line[] = $lastname." ".$firstname;

					$table->addBody($line);
				}

				//cout(getTitleArea($lang->def('_EXPIRING_USERS'), 'coursepanel').'<div class="std_block">', 'content');
				cout($lang->def('_COMPETENCE').': <b>'.$comp_data['name'].'</b>', 'content');
				cout($back_ui.$table->getTable().$back_ui, 'content');
		} else {
				cout($back_ui.$lang->def('_NO_REQUIRED_USER').$back_ui, 'content');
		}

	} else {
		//error, no competence selected
		cout($back_ui.$lang->def('_NO_COMPETENCE_SELECTED').$back_ui, 'content');
		return;
	}

}





function publicCoursePanelDispatch($op) {

	switch ($op) {

		case "savepanel": {
			savePanel();
		} break;

		case "coursepanel": {
			coursePanel();
		} break;

		case "expired": {
			expired();
		} break;

		case "required": {
			required();
		} break;

	}

}

?>