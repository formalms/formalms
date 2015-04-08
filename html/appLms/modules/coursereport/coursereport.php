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

if(Docebo::user()->isAnonymous()) die("You can't access");

function testreport($idTrack, $idTest, $testName, $studentName) {
        checkPerm('view');
        require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.test.php');

		$lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');
        $query_testreport = "
        SELECT DATE_FORMAT(date_attempt, '%d/%m/%Y %H:%i'), score
        FROM ".$GLOBALS['prefix_lms']."_testtrack_times
        WHERE idTrack = '".$idTrack."' AND idTest = '".$idTest."' ORDER BY date_attempt";
        $re_testreport = sql_query($query_testreport);

		$test_man       = new GroupTestManagement();
		$report_man = new CourseReportManager();
        $org_tests              =& $report_man->getTest();
        $tests_info             =& $test_man->getTestInfo($org_tests);

        $page_title = array(
            'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_TH_TEST_REPORT'),
            strip_tags($testName)
        );
        $out->add(
                getTitleArea($page_title, 'coursereport')
                .'<div class="std_block">'
				.getBackUi("javascript:history.go(-1)", Lang::t('_BACK','standard'))
		);
		$tb = new Table(0, $testName.' : '.$studentName);
		$tb->addHead(array(
			'N.',
			$lang->def('_DATE'),
			$lang->def('_SCORE'),
		), array('min-cell','',''));

		$i = 1;
		while(list($date_attempt, $score) = sql_fetch_row($re_testreport)) {

			$tb->addBody(array($i++, $date_attempt, $score));
        }
		$out->add(
			$tb->getTable()
			.'</div>'
		, 'content');

}

function scormreport($idTest) {
	checkPerm('view');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');

	$lang =& DoceboLanguage::createInstance('coursereport', 'lms');
	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$query_testreport = "
        SELECT DATE_FORMAT(date_action, '%d/%m/%Y %H:%i'), score_raw
        FROM ".$GLOBALS['prefix_lms']."_scorm_tracking_history
        WHERE idscorm_tracking = ".$idTest. " ORDER BY date_action";
	$re_testreport = sql_query($query_testreport);

	$test_man = new GroupTestManagement();
	$report_man = new CourseReportManager();
	$org_tests =& $report_man->getTest();
	$tests_info =& $test_man->getTestInfo($org_tests);

	$page_title = array(
			'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_TH_TEST_REPORT'),
			strip_tags($testName)
	);
	$out->add(getTitleArea($page_title, 'coursereport').'<div class="std_block">'.getBackUi("javascript:history.go(-1)", Lang::t('_BACK','standard')));
	$tb = new Table(0, $testName.' : '.$studentName);
	$tb->addHead(array(
			'N.',
			$lang->def('_DATE'),
			$lang->def('_SCORE'),
	), array('min-cell', '', ''));

	$i = 1;
	while(list($date_attempt, $score) = sql_fetch_row($re_testreport)) {
		$tb->addBody(array($i++, $date_attempt, $score));
	}
	$out->add($tb->getTable().'</div>', 'content');
}

function coursereport() {
    	checkPerm('view');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$included_test 	= array();
	$view_perm = checkPerm('view', true);
	$mod_perm = checkPerm('mod', true);

	// XXX: Instance management
	$acl_man 	= Docebo::user()->getAclManager();
	$test_man 	= new GroupTestManagement();
	$report_man = new CourseReportManager();

	// XXX: Find test from organization
	$org_tests 		=& $report_man->getTest();
	$tests_info		= $test_man->getTestInfo($org_tests);

	// XXX: Find students
	/*
	 * $id_students	=& $report_man->getStudentId();
	 * $students_info 	=& $acl_man->getUsers($id_students);
	 */

	$lang2 			=& DoceboLanguage::createInstance('levels', 'lms');

	if(isset($_POST['type_filter']))
		$type_filter = $_POST['type_filter'];
	else
		$type_filter = false;

	if($type_filter=="false")
		$type_filter = false;

	$lev = $type_filter;

	$students = getSubscribedInfo((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
	$id_students = array_keys($students);
	$students_info 	=& $acl_man->getUsers($id_students);
	$i=0;

	/*$students_info=array();
	foreach( $students as $idst => $user_course_info )
		$students_info[$idst] =& $acl_man->getUser( $idst, FALSE );
*/
	// XXX: Info for updates
	$query_tot_report = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'";
	list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

	$query_tests = "
	SELECT id_report, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND source_of = 'test'";
	$re_tests = sql_query($query_tests);
	while(list($id_r, $id_t) = sql_fetch_row($re_tests))
	{
		$included_test[$id_t] = $id_t;
		$included_test_report_id[$id_r] = $id_r;
	}

	// XXX: Update if needed
	if($tot_report == 0)
		$report_man->initializeCourseReport($org_tests);
	else {
		if(is_array($included_test)) $test_to_add = array_diff($org_tests, $included_test);
		else $test_to_add = $org_tests;
		if(is_array($included_test)) $test_to_del = array_diff($included_test, $org_tests);
		else $test_to_del = $org_tests;
		if(!empty($test_to_add) || !empty($test_to_del)) {
			$report_man->addTestToReport($test_to_add, 1);
			$report_man->delTestToReport($test_to_del);

			$included_test = $org_tests;
		}
	}
	$report_man->updateTestReport($org_tests);

	// XXX: Retrive all colums (test and so), and set it
	$img_mod = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />';

	$type_h = array('line_users');
	$cont_h = array(	$lang->def('_DETAILS'));

	$a_line_1 = array('');
	$a_line_2 = array('');
	$colums['max_score']		= array($lang->def('_MAX_SCORE'));
	$colums['required_score']	= array($lang->def('_REQUIRED_SCORE'));
	$colums['weight']	 		= array($lang->def('_WEIGHT'));
	$colums['show_to_user'] 	= array($lang->def('_SHOW_TO_USER'));
	$colums['use_for_final'] 	= array($lang->def('_USE_FOR_FINAL'));

	$query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'
	ORDER BY sequence ";
	$re_report = sql_query($query_report);
	$total_weight = 0;
	$i = 1;
	while($info_report = sql_fetch_assoc($re_report))
	{
		$id 									= $info_report['id_source'];
		$reports[$info_report['id_report']]		= $info_report;
		$reports_id[] 							= $info_report['id_report'];

		// XXX: set action colums

		$type_h[] = 'min-cell';

		switch($info_report['source_of']) {
			case "test" : {

				$title = strip_tags($tests_info[$info_report['id_source']]['title']);

				if(!$mod_perm) {
					if(!$view_perm) {
						$my_action = '';
						$a_line_2[] = '';
					} else {
						$my_action =	'<a class="ico-sprite subs_chart" href="index.php?modname=coursereport&amp;op=testQuestion&amp;id_test='.$id.'"><span><span>'.$lang->def('_TQ_LINK').'</span></a>';
						$a_line_2[] = '';
					}
				} else {
					$my_action =	'<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=testvote&amp;type_filter='.$type_filter.'&amp;id_test='.$id.'"><span><span>'.$lang->def('_EDIT_SCORE').'</span></a>'
									.' <a class="ico-sprite subs_chart" href="index.php?modname=coursereport&amp;op=testQuestion&amp;type_filter='.$type_filter.'&amp;id_test='.$id.'"><span><span>'.$lang->def('_TQ_LINK').'</span></a>';
					$a_line_2[] = '<a href="index.php?modname=coursereport&amp;op=roundtest&amp;id_test='.$id.'" '
								.'title="'.$lang->def('_ROUND_TEST_VOTE').'">'.$lang->def('_ROUND_VOTE').'</a>';
				}
			};break;
			case "scoitem" 	: {

				$title = strip_tags($info_report['title']);

				if(!$mod_perm) {
						$my_action = '';
						$a_line_2[] = '';
				} else {
					$my_action =	$my_action =	'<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=modactivityscore&amp;type_filter='.$type_filter.'&amp;id_report='.$info_report['id_report'].'&amp;source_of='.$info_report['source_of'].'&amp;id_source='.$info_report['id_source'].'"><span><span>'.$lang->def('_CHANGE_ACTIVITY_VOTE').'</span></a>'
									.' <a class="ico-sprite subs_del" href="index.php?modname=coursereport&amp;op=delactivity&amp;id_report='.$info_report['id_report'].'"><span><span>'.$lang->def('_DELETE_ACTIVITY_VOTE').'</span></a>';

					$a_line_2[] = '<a href="index.php?modname=coursereport&amp;op=roundreport&amp;id_report='.$info_report['id_report'].'" '
							.'title="'.$lang->def('_ROUND_ACTIVITY_VOTE_TITLE').'">'.$lang->def('_ROUND_VOTE').'</a>';
				}
			};break;
			case "activity" 	: {
				$title = strip_tags($info_report['title']);

				if(!$mod_perm) {
						$my_action = '';
						$a_line_2[] = '';
				} else {
					$my_action =	'<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=modactivityscore&amp;type_filter='.$type_filter.'&amp;id_report='.$info_report['id_report'].'&amp;source_of='.$info_report['source_of'].'&amp;id_source='.$info_report['id_source'].'"><span><span>'.$lang->def('_CHANGE_ACTIVITY_VOTE').'</span></a>'
									.' <a class="ico-sprite subs_del" href="index.php?modname=coursereport&amp;op=delactivity&amp;id_report='.$info_report['id_report'].'"><span><span>'.$lang->def('_DELETE_ACTIVITY_VOTE').'</span></a>';

					$a_line_2[] = '<a href="index.php?modname=coursereport&amp;op=roundreport&amp;id_report='.$info_report['id_report'].'" '
							.'title="'.$lang->def('_ROUND_VOTE').'">'.$lang->def('_ROUND_VOTE').'</a>';
				}
			};break;
			case "final_vote" 	: {

				$title = strip_tags($lang->def('_FINAL_SCORE'));
				$info_report['weight'] = $total_weight;

				if(!$mod_perm) {
						$my_action = '';
						$a_line_2[] = '';
				} else {
                    $my_action = '<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=finalvote&amp;type_filter='.$type_filter.'&amp;id_report='.$info_report['id_report'].'"><span><span>'.$lang->def('_EDIT_SCORE').'</span></a>';

					$a_line_2[] = ''
							.'<a href="index.php?modname=coursereport&amp;op=redofinal&amp;id_report='.$info_report['id_report'].'" '
								.'title="'.$lang->def('_REDO_FINAL_VOTE_TITLE').'">'.$lang->def('_REDO_FINAL_VOTE').'</a></li>'
							.'<br/><a href="index.php?modname=coursereport&amp;op=roundreport&amp;id_report='.$info_report['id_report'].'" '
								.'title="'.$lang->def('_ROUND_FINAL_VOTE_TITLE').'">'.$lang->def('_ROUND_VOTE').'</a></li>'
							.'';
				}
			};break;
		}

		$top = $title.'<br/>';
		if($mod_perm)
			if($i > 1 && $info_report['source_of'] != 'final_vote')
				$top .= '<a class="ico-sprite subs_left" href="index.php?modname=coursereport&amp;op=moveleft&amp;id_report='.$info_report['id_report'].'"><span><span>'.$lang->def('_MOVE_LEFT').'</span></a>';
		$top .= $my_action;
		if($mod_perm)
			if(($i < ($tot_report - 1)) && ($tot_report > 2) )
				$top .= '<a class="ico-sprite subs_right" href="index.php?modname=coursereport&amp;op=moveright&amp;id_report='.$info_report['id_report'].'"><span><span>'.$lang->def('_MOVE_RIGHT').'</span></a>';
		$cont_h[] = $top;
		$i++;

		//set info colums
		$colums['max_score'][] 		= $info_report['max_score'];
		$colums['required_score'][]	= $info_report['required_score'];
		$colums['weight'][] 			= $info_report['weight'];
		$colums['show_to_user'][] 		= ( $info_report['show_to_user'] == 'true' ? $lang->def('_YES') : $lang->def('_NO') );
		$colums['use_for_final'][] 	= ( $info_report['use_for_final'] == 'true' ? $lang->def('_YES') : $lang->def('_NO') );

		if($info_report['use_for_final'] == 'true') $total_weight += $info_report['weight'];
	}

	// XXX: Set table intestation
	$tb_report = new Table(0, $lang->def('_COURSE_REPORT_CAPTION'), $lang->def('_COURSE_REPORT_SUMMARY'));

	$tb_report->setColsStyle($type_h);
	$tb_report->addHead($cont_h);

	$tb_report->addBody($a_line_2);

	$tb_report->addBody($colums['max_score']);
	$tb_report->addBody($colums['required_score']);
	$tb_report->addBody($colums['weight']);
	$tb_report->addBody($colums['show_to_user']);
	$tb_report->addBody($colums['use_for_final']);

	//$tb->addBodyExpanded('<span class="text_bold title_big">'.$lang->def('_STUDENTS_VOTE').'</span>', 'align-center');
	$tb_score = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_COURSE_REPORT_SUMMARY'));
	$tb_score->setColsStyle($type_h);
	$cont_h[0] = $lang->def('_STUDENTS');
	$cont_old = $cont_h;

	// ALE aggiungo menu a tendina con valori utente:
	require_once(_adm_.'/lib/lib.field.php');

	$fman = new FieldList();
	$fields = $fman->getFlatAllFields(array('framework', 'lms'));

	$f_list = array(
            'userid'                 => Lang::t('_USERNAME', 'standard'),
            'firstname'                 => Lang::t('_FIRSTNAME', 'standard'),
            'lastname'                 => Lang::t('_LASTNAME', 'standard'),
			'email'                 => Lang::t('_EMAIL', 'standard'),
			'lastenter'             => Lang::t('_DATE_LAST_ACCESS', 'profile'),
			'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
			'language' => Lang::t('_LANGUAGE', 'standard'),
			'level' => Lang::t('_LEVEL', 'standard')
	);
	$f_list = $f_list + $fields;

	$js_arr = array();
	foreach ($f_list as $key=>$value)
		$js_arr[] = $key.': '.json_encode($value);
	$f_list_js = '{'.implode(',', $js_arr).'}';

	$fieldlist = $f_list;
	$dyn_labels = array();
	$dyn_filter = array();
	$num_var_fields = 1;
	$label = '<form name="formx" method="get">';
	for ($i=0; $i<$num_var_fields; $i++) {
		$label .= '<select onchange="document.formx.submit()" id="_dyn_field_selector_0" name="_dyn_field_selector_0">';
		foreach ($fieldlist as $key => $value) {
			if ($i==0)
				$first = $key;
			$label .= '<option value="'.$key.'"'
					.( $_GET['_dyn_field_selector_0'] == $key ? ' selected="selected"' : '' )
					.'>'.$value.'</option>';
		}
		$label .= '</select>';
	}
	$label .= '</form>';

	$field_selected = ($_GET['_dyn_field_selector_0']) ? $_GET['_dyn_field_selector_0'] : 'userid';
	$_SESSION['field_selected'] = $field_selected;
	// aggiungo un elemento in posizione 1
	$index = 1;
	$start = array_slice($cont_h, 0, $index);
	$end = array_slice($cont_h, $index);
	$start[] = $label;
	$cont_h = array_merge($start, $end);
	// fine inserimento

	// END ALE

	$tb_score->addHead($cont_h);

	$cont_h = $cont_old; // ripristino il vecchio array di intestazioni

// 	$tb_score->addHead($cont_h);

	// XXX: Retrive Test info and scores
	$tests_score 	=& $test_man->getTestsScores($included_test, $id_students);
	// XXX: Calculate statistic
	$test_details 	= array();
	if(is_array($included_test))
	{
		while(list($id_test, $users_result) = each($tests_score))
		{
			while(list($id_user, $single_test) = each($users_result))
			{
				if($single_test['score_status'] == 'valid')
				{
					// max
					if(!isset($test_details[$id_test]['max_score']))
						$test_details[$id_test]['max_score'] = $single_test['score'];
					elseif($single_test['score'] > $test_details[$id_test]['max_score'])
						$test_details[$id_test]['max_score'] = $single_test['score'];

					// min
					if(!isset($test_details[$id_test]['min_score']))
						$test_details[$id_test]['min_score'] = $single_test['score'];
					elseif($single_test['score'] < $test_details[$id_test]['min_score'])
						$test_details[$id_test]['min_score'] = $single_test['score'];

					//number of valid score
					if(!isset($test_details[$id_test]['num_result']))
						$test_details[$id_test]['num_result'] = 1;
					else
						$test_details[$id_test]['num_result']++;

					// averange
					if(!isset($test_details[$id_test]['averange']))
						$test_details[$id_test]['averange'] = $single_test['score'];
					else
						$test_details[$id_test]['averange'] += $single_test['score'];
				}
			}
		}
		while(list($id_test, $single_detail) = each($test_details))
			if(isset($single_detail['num_result']))
				$test_details[$id_test]['averange'] /= $test_details[$id_test]['num_result'];
		reset($test_details);
	}
	// XXX: Retrive other source scores
	$reports_score 	=& $report_man->getReportsScores(
		(isset($included_test_report_id) && is_array($included_test_report_id) ? array_diff($reports_id, $included_test_report_id) : $reports_id), $id_students);

	// XXX: Calculate statistic
	$report_details = array();
	while(list($id_report, $users_result) = each($reports_score))
	{
		while(list($id_user, $single_report) = each($users_result))
		{
			if($single_report['score_status'] == 'valid')
			{
				// max
				if(!isset($report_details[$id_report]['max_score']))
					$report_details[$id_report]['max_score'] = $single_report['score'];
				elseif($single_report['score'] > $report_details[$id_report]['max_score'])
					$report_details[$id_report]['max_score'] = $single_report['score'];

				// min
				if(!isset($report_details[$id_report]['min_score']))
					$report_details[$id_report]['min_score'] = $single_report['score'];
				elseif($single_report['score'] < $report_details[$id_report]['min_score'])
					$report_details[$id_report]['min_score'] = $single_report['score'];

				//number of valid score
				if(!isset($report_details[$id_report]['num_result']))
					$report_details[$id_report]['num_result'] = 1;
				else
					$report_details[$id_report]['num_result']++;

				// averange
				if(!isset($report_details[$id_report]['averange']))
					$report_details[$id_report]['averange'] = $single_report['score'];
				else
					$report_details[$id_report]['averange'] += $single_report['score'];
			}
		}
	}
	while(list($id_report, $single_detail) = each($report_details))
		if(isset($single_detail['num_result']))
			$report_details[$id_report]['averange'] /= $report_details[$id_report]['num_result'];
	reset($report_details);

	// XXX: Display user scores
	if(!empty($students_info))
	while(list($idst_user, $user_info) = each($students_info))
	{
		$user_name = ( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
						? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
						: $acl_man->relativeId($user_info[ACL_INFO_USERID]) );
	$cont = array($user_name);

		$fman = new FieldList();
		$field_entries = $fman->getUsersFieldEntryData($user_info[0],$field_selected,true);

		$user = array(
			'id'            => $user_info[ACL_INFO_IDST],
			'userid'        => $user_info[ACL_INFO_USERID],
			'firstname' => $user_info[ACL_INFO_FIRSTNAME],
			'lastname'      => $user_info[ACL_INFO_LASTNAME],
			'email'         => $user_info[ACL_INFO_EMAIL],
			'register_date' => $user_info[ACL_INFO_REGISTER_DATE],
			'lastenter' => $user_info[ACL_INFO_LASTENTER]
		);

		if (is_numeric($field_selected)) {
			$cont[] = $field_entries[$user_info[0]][$field_selected];
		} else {
          if ($field_selected=="userid"){
            $pos = strrpos($user[$field_selected], "/");
            if ($pos==0){
              $cont[] = substr($user[$field_selected], 1);
            }
          }else{
            $cont[] = $user[$field_selected];
          }
		}
		// for every colum
        $results_test = array();
		$results_activity = array();
		$results_scorm_test = array();
		foreach ($reports as $id_report => $info_report) {
                switch ($info_report['source_of']) {
                    case "test" : {
                            $id_test = $info_report['id_source'];
                            if (isset($tests_score[$id_test][$idst_user])) {
                                switch ($tests_score[$id_test][$idst_user]['score_status']) {
                                    case "not_complete" : $cont[] = '-';
                                        break;
                                    case "not_checked" : {
                                            $cont[] = '<span class="cr_not_check">' . $lang->def('_NOT_CHECKED') . '</span>';

                                            // Count not checked
                                            if (!isset($test_details[$id_test]['not_checked']))
                                                $test_details[$id_test]['not_checked'] = 1;
                                            else
                                                $test_details[$id_test]['not_checked'] ++;
                                        };
                                        break;
                                    case "passed" : {
                                            //$cont[] = '<span class="cr_passed">'.$lang->def('_PASSED').'</span>';
                                            $cont[] = '<img src="' . getPathImage('fw') . 'emoticons/thumbs_up.gif" alt="' . $lang->def('_PASSED') . '" />';
                                            // Count passed
                                            if (!isset($test_details[$id_test]['passed']))
                                                $test_details[$id_test]['passed'] = 1;
                                            else
                                                $test_details[$id_test]['passed'] ++;
                                        };
                                        break;
                                    case "not_passed" : {
                                            //$cont[] = '<span class="cr_not_passed">'.$lang->def('_NOT_PASSED').'</span>';
                                            $cont[] = '<img src="' . getPathImage('fw') . 'emoticons/thumbs_down.gif" alt="' . $lang->def('_NOT_PASSED') . '" />';
                                            // Count not passed
                                            if (!isset($test_details[$id_test]['not_passed']))
                                                $test_details[$id_test]['not_passed'] = 1;
                                            else
                                                $test_details[$id_test]['not_passed'] ++;
                                        };
                                        break;
                                    case "valid" : {
                                            $score = $tests_score[$id_test][$idst_user]['score'];

                                            if ($tests_score[$id_test][$idst_user]['times'] > 0)
                                                $tests_score[$id_test][$idst_user]['times'] = "<a href=\"index.php?modname=coursereport&op=testreport&idTest=" . $tests_score[$id_test][$idst_user]['idTest'] . "&idTrack=" . $tests_score[$id_test][$idst_user]['idTrack'] . "&testName=" . $tests_info[$info_report['id_source']]['title'] . "&studentName=" . $acl_man->relativeId($user_info[ACL_INFO_USERID]) . "\">" . $tests_score[$id_test][$idst_user]['times'] . "</a>";
                                            $tt = "(" . $tests_score[$id_test][$idst_user]['times'] . ")";

                                            if ($score >= $info_report['required_score']) {
                                                if ($score == $test_details[$id_test]['max_score'])
                                                    $cont[] = '<span class="cr_max_score">' . $score . " " . $tt . '</span>';
                                                else
                                                    $cont[] = $score . " " . $tt;

                                                // Count passed
                                                if (!isset($test_details[$id_test]['passed']))
                                                    $test_details[$id_test]['passed'] = 1;
                                                else
                                                    $test_details[$id_test]['passed'] ++;
                                            } else {
                                                if ($score == $test_details[$id_test]['max_score'])
                                                    $cont[] = '<span class="cr_max_score cr_not_passed">' . $score . " " . $tt . '</span>';

                                                    else$cont[] = '<span class="cr_not_passed">' . $score . " " . $tt . '</span>';

                                                // Count not passed
                                                if (!isset($test_details[$id_test]['not_passed']))
                                                    $test_details[$id_test]['not_passed'] = 1;
                                                else
                                                    $test_details[$id_test]['not_passed'] ++;
                                            }
                                            if (isset($test_details[$id_test]['varianza']) && isset($test_details[$id_test]['averange'])) {
                                                $test_details[$id_test]['varianza'] += pow(($tests_score[$id_test][$idst_user]['score'] - $test_details[$id_test]['averange']), 2);
                                            } else {
                                                $test_details[$id_test]['varianza'] = pow(($tests_score[$id_test][$idst_user]['score'] - $test_details[$id_test]['averange']), 2);
                                            }
                                        };
                                        break;
                                    default : {
                                            $cont[] = '-';
                                        }
                                }
                            } else
                                $cont[] = '-';
                            if ($info_report['use_for_final']=='true'){
                                array_push($results_test, $score * $info_report['weight']);
                            }
                        };
                        break;
                    case "scoitem" : {
                            $query_report = "
						SELECT *
						FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking
						WHERE idscorm_item = '" . $info_report['id_source'] . "' AND idUser = '" . $idst_user . "'
						";
                            //echo $query_report;
                            $report = sql_fetch_assoc(sql_query($query_report));
                            if ($report['score_raw'] == NULL)
                                $report['score_raw'] = "-";
                            //$cont[] = '<span class="cr_passed">'.$report['score_max'].'</span>';
                            if ($info_report['use_for_final']=='true'){
                                array_push($results_scorm_test, $report['score_raw'] * $info_report['weight']);
                            }
                            $id_track = (isset($report['idscorm_tracking']) ? $report['idscorm_tracking'] : 0);
                            $query_report = "
						SELECT *
						FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking_history
						WHERE idscorm_tracking = '" . $id_track . "'
						";
                            //echo $query_report;
                            $query = sql_query($query_report);
                            $num = sql_num_rows($query);
                            if ($num > 0)
                                $storico = " (<a href=\"index.php?modname=coursereport&op=scormreport&idTest=" . $id_track . "\">" . $num . "</a>)";
                            else
                                $storico = "";
                            $cont[] = '<span class="cr_not_check">' . $report['score_raw'] . '</span>' . $storico;
                        }break;
                    case "activity" : {
                            $id_report = $info_report['id_report'];
                            $score = 0;
                            if (isset($reports_score[$id_report][$idst_user])) {
                                switch ($reports_score[$id_report][$idst_user]['score_status']) {
                                    case "not_complete" : $cont[] = '-';
                                        break;
                                    case "valid" : {
                                            $score = $reports_score[$id_report][$idst_user]['score'];
                                            if ($score >= $info_report['required_score']) {
                                                if ($score == $info_report['max_score']) {
                                                    $cont[] = '<span class="cr_max_score">' . $score . '</span>';
                                                } else
                                                    $cont[] = $score;
                                                // Count passed
                                                if (!isset($report_details[$id_report]['passed']))
                                                    $report_details[$id_report]['passed'] = 1;
                                                else
                                                    $report_details[$id_report]['passed'] ++;
                                            } else {
                                                $cont[] = '<span class="cr_not_passed">' . $score . '</span>';
                                                // Count not passed
                                                if (!isset($report_details[$id_report]['not_passed']))
                                                    $report_details[$id_report]['not_passed'] = 1;
                                                else
                                                    $report_details[$id_report]['not_passed'] ++;
                                            }
                                            if (isset($report_details[$id_report]['varianza']) && isset($report_details[$id_report]['averange'])) {
                                                $report_details[$id_report]['varianza'] += round(pow(($reports_score[$id_report][$idst_user]['score'] - $report_details[$id_report]['averange']), 2), 2);
                                            } else {
                                                $report_details[$id_report]['varianza'] = round(pow(($reports_score[$id_report][$idst_user]['score'] - $report_details[$id_report]['averange']), 2), 2);
                                            }
                                        };
                                        break;
                                }
                            }else{
                                $cont[] = '<span class="cr_not_passed">-</span>';
                            }
                            if ($info_report['use_for_final']=='true'){
                                array_push($results_activity, $score * $info_report['weight']);
                            }
                        }break;
                    case "final_vote" : {
//                            $divid = (count($results_test)) + (count($results_scorm_test)) + (count($results_activity));
                            $first_value = 0;
                            foreach ($results_test as $value) {
                                if (!is_numeric($value)) {
                                    $value = 0;
                                }
                                $first_value += $value;
                            }
                            $second_value = 0;
                            foreach ($results_scorm_test as $value) {
                                if (!is_numeric($value)) {
                                    $value = 0;
                                }
                                $second_value += $value;
                            }
                            $third_value = 0;
                            foreach ($results_activity as $value) {
                                if (!is_numeric($value)) {
                                    $value = 0;
                                }
                                $third_value += $value;
                            }
                            // Reset array results
                            $results_scorm_test = array();
                            $results_test = array();
                            $results_activity = array();
                            $media = (($first_value + $second_value + $third_value ) / $total_weight);
                            $media = sprintf("%01.2f", round($media,2));
                            $cont[] = $media>0?$media:'<span class="cr_not_passed">-</span>';
                        };
                        break;
                }
            }
		$tb_score->addBody($cont);
	}
	// XXX: Display statistics
	$stats['passed'] 		= array($lang->def('_PASSED'));//, ''
	$stats['not_passed'] 	= array($lang->def('_NOT_PASSED'));//, ''
	$stats['not_checked'] 	= array($lang->def('_NOT_CHECKED'));//, ''
	$stats['averange'] 		= array($lang->def('_AVERANGE'));//, ''
	$stats['varianza'] 		= array($lang->def('_STANDARD_DEVIATION'));//, ''
	$stats['max_score'] 	= array($lang->def('_MAX_SCORE'));//, ''
	$stats['min_score'] 	= array($lang->def('_MIN_SCORE'));//, ''
	foreach($reports as $id_report => $info_report)
	{
		switch($info_report['source_of'])
		{
			case "test" :
			{
				$id_test = $info_report['id_source'];

				if(isset($test_details[$id_test]['passed']) || isset($test_details[$id_test]['not_passed']))
				{
					if(!isset($test_details[$id_test]['passed']))
						$test_details[$id_test]['passed'] = 0;
					if(!isset($test_details[$id_test]['not_passed']))
						$test_details[$id_test]['not_passed'] = 0;

					$test_details[$id_test]['varianza'] /= ($test_details[$id_test]['passed'] + $test_details[$id_test]['not_passed']);
					$test_details[$id_test]['varianza'] = sqrt($test_details[$id_test]['varianza']);
				}
				$stats['passed'][] 		= ( isset($test_details[$id_test]['passed']) ? round($test_details[$id_test]['passed'], 2) : '-' );
				$stats['not_passed'][] = ( isset($test_details[$id_test]['not_passed']) ? round($test_details[$id_test]['not_passed'], 2) : '-' );
				$stats['not_checked'][] = ( isset($test_details[$id_test]['not_checked']) ? round($test_details[$id_test]['not_checked'], 2) : '-' );
				$stats['averange'][] 	= ( isset($test_details[$id_test]['averange']) ? round($test_details[$id_test]['averange'], 2) : '-' );
				$stats['varianza'][]	= ( isset($test_details[$id_test]['varianza']) ? round($test_details[$id_test]['varianza'], 2) : '-' );
				$stats['max_score'][] 	= ( isset($test_details[$id_test]['max_score']) ? round($test_details[$id_test]['max_score'], 2) : '-' );
				$stats['min_score'][] 	= ( isset($test_details[$id_test]['min_score']) ? round($test_details[$id_test]['min_score'], 2) : '-' );
			};break;
			case "scoitem" :{
				$query_report = "
						SELECT *
						FROM ".$GLOBALS['prefix_lms']."_scorm_tracking
						WHERE idscorm_item = '".$info_report['id_source']."'";

						$passed=0;
						$total=0;
						$media=0;
						$varianza=0;
						$votomassimo=0;
						$votominimo=9999;
						$result = sql_query($query_report);
						while($report = sql_fetch_assoc($result))
						{
							if($report['score_raw']!=NULL)
							{
								if($report['score_raw']>$votomassimo)
									$votomassimo = $report['score_raw'];
								if($report['score_raw']<$votominimo)
									$votominimo = $report['score_raw'];
								$media=$media+$report['score_raw'];
								$total=$total+1;
							if($report['lesson_status'] == 'passed' ){
									$passed++;
								}
							}
						}
						$media=($total == 0 ? '0' : $media/$total);
						$result = sql_query($query_report);
						$var=0;
						while($report = sql_fetch_assoc($result))
							if($report['score_raw']!=NULL)
								$var=$var+pow($media-$report['score_raw'],2);
						$varianza=($total == 0 ? '0' : floor($var/$total));
						if($votominimo==9999)
							$votominimo="";

				$stats['passed'][] 		= $passed;
				$stats['not_passed'][]  = $total-$passed;
				$stats['not_checked'][] = "-";
				$stats['averange'][] 	= $media;
				$stats['varianza'][]	= $varianza;
				$stats['max_score'][] 	= $votomassimo;
				$stats['min_score'][] 	= $votominimo;
			};break;
			case "activity" :
			case "final_vote" :
			{
				if(isset($report_details[$id_report]['passed']) || isset($report_details[$id_report]['not_passed']))
				{
					if(!isset($report_details[$id_report]['passed']))
						$report_details[$id_report]['passed'] = 0;
					if(!isset($report_details[$id_report]['not_passed']))
						$report_details[$id_report]['not_passed'] = 0;

					$report_details[$id_report]['varianza'] /= ($report_details[$id_report]['passed'] + $report_details[$id_report]['not_passed']);
					$report_details[$id_report]['varianza'] = sqrt($report_details[$id_report]['varianza']);
				}
				$stats['passed'][] 		= ( isset($report_details[$id_report]['passed']) ? round($report_details[$id_report]['passed'], 2) : '-' );
				$stats['not_passed'][] = ( isset($report_details[$id_report]['not_passed']) ? round($report_details[$id_report]['not_passed'], 2) : '-' );
				$stats['not_checked'][] = ( isset($report_details[$id_report]['not_checked']) ? round($report_details[$id_report]['not_checked'], 2) : '-' );
				$stats['averange'][] 	= ( isset($report_details[$id_report]['averange']) ? round($report_details[$id_report]['averange'], 2) : '-' );
				$stats['varianza'][]	= ( isset($report_details[$id_report]['varianza']) ? round(sqrt($report_details[$id_report]['varianza']), 2) : '-' );
				$stats['max_score'][] 	= ( isset($report_details[$id_report]['max_score']) ? round($report_details[$id_report]['max_score'], 2) : '-' );
				$stats['min_score'][] 	= ( isset($report_details[$id_report]['min_score']) ? round($report_details[$id_report]['min_score'], 2) : '-' );
			};break;
		}
	}
	$tb_stat = new Table(0, $lang->def('_SUMMARY_VOTE'), $lang->def('_COURSE_REPORT_SUMMARY'));
	$tb_stat->setColsStyle($type_h);
	$cont_h[0] = $lang->def('_STATISTICS');
	$tb_stat->addHead($cont_h);

	//$tb->addBodyExpanded('<span class="text_bold title_big">'.$lang->def('_SUMMARY_VOTE').'</span>', 'align-center');
	$tb_stat->addBody($stats['passed']);
	$tb_stat->addBody($stats['not_passed']);
	$tb_stat->addBody($stats['not_checked']);
	$tb_stat->addBody($stats['averange']);
	$tb_stat->addBody($stats['varianza']);
	$tb_stat->addBody($stats['max_score']);
	$tb_stat->addBody($stats['min_score']);

	// Write in output
	$out->add( getTitleArea($lang->def('_COURSEREPORT', 'menu_course'), 'coursereport')
		.'<div class="std_block">' );
	$out->add('
		<div id="dhtmltooltip"></div>
	<style type="text/css">

#dhtmltooltip{
position: absolute;
width: 150px;
border: 2px solid black;
padding: 2px;
background-color: white;
visibility: hidden;
z-index: 100;
filter: progid:DXImageTransform.Microsoft.Shadow(color=gray,direction=135);
}
</style>
	<script>
	var posx = 0;
	var posy = 0;
		document.onmousemove=function doSomething(e) {
	 if (!e) e = window.event; // works on IE, but not NS (we rely on NS passing us the event)
  if (e)
  {
    if (e.pageX || e.pageY)
    {
      posx = e.pageX;
      posy = e.pageY;
    }
    else if (e.clientX || e.clientY)
    { // works on IE6,FF,Moz,Opera7
      posx = e.clientX + document.body.scrollLeft;
      posy = e.clientY + document.body.scrollTop;
    }
  }
}

var offsetxpoint=-60
var offsetypoint=20
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function ddrivetip(thetext, thecolor, thewidth,pos,html){
if (ns6||ie){

tipobj.innerHTML="'.$lang->def('_EXPORT').':"+html+"<a id=\"cambia_link\" class=\""+thetext+"\" href=\"./index.php?modname=coursereport&op=export&amp;type_filter='.$lev.'\">'.$lang->def('_EXPORT_STATS').'</a>";
enabletip=true
tipobj.style.width="200px"
tipobj.style.height="auto"

tipobj.style.left=posx+"px"
tipobj.style.top=posy+"px"
tipobj.style.visibility="visible"
return false
}
}

function positiontip(e){
if (enabletip){
var curX=(ns6)?e.pageX : event.x+ietruebody().scrollLeft;
var curY=(ns6)?e.pageY : event.y+ietruebody().scrollTop;

var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

if (rightedge<tipobj.offsetWidth)

tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
else if (curX<leftedge)
tipobj.style.left="5px"
else

tipobj.style.left=curX+offsetxpoint+"px"

if (bottomedge<tipobj.offsetHeight)
tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
else
tipobj.style.top=curY+offsetypoint+"px"
tipobj.style.visibility="visible"
}
}

function hideddrivetip(){
if (ns6||ie){
enabletip=false
tipobj.style.visibility="hidden"
tipobj.style.left="-1000px"
tipobj.style.backgroundColor=""
tipobj.style.width=""
}
}

//document.onmousemove=positiontip

var lista=new Array();
function tool(arm,pos,htm){

var tipobj=document.getElementById("dhtmltooltip").style.visibility;
if(tipobj=="visible"){
hideddrivetip()
lista=new Array();
}
else
	ddrivetip(arm,"","",pos,htm);
	//else
	//tooltip.hide();
	}
var url="./index.php?modname=coursereport&op=export&amp;type_filter='.$lev.'";

	function cambialink(num,fare){
	if(fare)
lista[lista.length+1]=num;
else
{
i=0;
while(i<lista.length)
	{if (lista[i]==num)
		lista[i]=null;
	i++;
	}
}
	document.getElementById("cambia_link").href=url;//+document.getElementById("cambia_link").className;

	i=0;
	stringaurl="&aggiuntivi=";
while(i<lista.length)
	{if (lista[i]!=null)
		stringaurl=stringaurl+lista[i]+",";
	i++;
	}

	document.getElementById("cambia_link").href=document.getElementById("cambia_link").href+stringaurl;
	//alert(document.getElementById("cambia_link").href);
	}
	</script>');

	$sql="SELECT * FROM core_field";
$filler="";
$filler="<br>";
    $result_quest = sql_query($sql) or die (mysql_error());
	while ($quests = sql_fetch_array($result_quest)){
		$filler.="<input type=\'checkbox\' onclick=\'cambialink(".$quests['idField'].",this.checked)\' value=\'".$quests['idField']."\'>".$quests['translation']."<br>";
	}
	$filler.="<input type=\'checkbox\' onclick=\'cambialink(9999,this.checked)\' value=\'999\'>".$lang->def('_QUESTION_ANSWERED')."<br>";
	$filler.="<input type=\'checkbox\' onclick=\'cambialink(1999,this.checked)\' value=\'1999\'>".$lang->def('_TOT_QUESTION')."<br>";
	$filler.="<input type=\'checkbox\' onclick=\'cambialink(1199,this.checked)\' value=\'1199\'>".$lang->def('_DATE')."<br>";
	$filler.="<input type=\'checkbox\' onclick=\'cambialink(1299,this.checked)\' value=\'1299\'>".$lang->def('_TOTAL')."<br>";
	if(checkPerm('mod', true)) {
		$out->add(
			'<div class="table-container-below">'.
			'<ul class="link_list_inline">'
				.'<li><a class="ico-wt-sprite subs_add" href="index.php?modname=coursereport&amp;op=addactivity" title="'.$lang->def('_ADD_ACTIVITY_TITLE').'">'
				.'<span>'.$lang->def('_ADD_ACTIVITY').'</span></a></li>'

				.'<li><a class="ico-wt-sprite subs_plus" href="index.php?modname=coursereport&amp;op=addscorm" title="'.$lang->def('_ADD_SCORM_RESULTS').'">'
				.'<span>'.$lang->def('_ADD_SCORM_RESULTS').'</span></a></li>'

				.'<li><a class="ico-wt-sprite subs_csv" href="index.php?modname=coursereport&amp;op=export&amp;type_filter='.$lev.'" title="'.$lang->def('_EXPORT_CSV').'" onclick="window.open(this.href); return false;">'
				.'<span>'.$lang->def('_EXPORT_CSV').'</span></a></li>
			</ul>'
			.'</div>'
		);
	}

	$out->add( Form::openForm( "statuserfilter" , "index.php?modname=coursereport&amp;op=coursereport" ) );
	$type_groups = array('false' => $lang->def('_ALL'),'1'=> $lang2->def('_LEVEL_1'),'2'=> $lang2->def('_LEVEL_2'),'3'=> $lang2->def('_LEVEL_3'),'4'=> $lang2->def('_LEVEL_4'),'5'=> $lang2->def('_LEVEL_5'),'6'=> $lang2->def('_LEVEL_6'),'7'=> $lang2->def('_LEVEL_7'));
	$out->add( Form::getDropdown( 	$lang->def('_LEVEL'),
									'type_filter',
									'type_filter',
									$type_groups ,
									$type_filter ));

	$out->add( Form::getButton('gofilter', 'gofilter', $lang->def('_SEARCH')) );

	$out->add( Form::closeForm() );

	$out->add(
		$tb_report->getTable().'<br /><br />'
		.$tb_score->getTable().'<br /><br />'
		.$tb_stat->getTable().'<br /><br />' );

	if(checkPerm('mod', true)) {
		$out->add(
			'<div class="table-container-below">'.
			'<ul class="link_list_inline">
				<li><a class="ico-wt-sprite subs_add" href="index.php?modname=coursereport&amp;op=addactivity" title="'.$lang->def('_ADD_ACTIVITY_TITLE').'">'
			.'<span>'
			.$lang->def('_ADD_ACTIVITY').'</span></a></li>'

			.'<li><a class="ico-wt-sprite subs_plus" href="index.php?modname=coursereport&amp;op=addscorm" title="'.$lang->def('_ADD_SCORM_RESULTS').'">'
			.'<span>'
			.$lang->def('_ADD_SCORM_RESULTS').'</span></a></li>'

			.'<li><a class="ico-wt-sprite subs_csv" href="index.php?modname=coursereport&amp;op=export&amp;type_filter='.$lev.'" title="'.$lang->def('_EXPORT_CSV').'" onclick="window.open(this.href); return false;">'
				.'<span>'.$lang->def('_EXPORT_CSV').'</span></a></li>
			</ul>'
			.'</div>'
		);
	}
	$out->add( '</div>');
}

function saveTestUpdate($id_test, &$test_man)
{
		// Save report modification
		if(isset($_POST['user_score']))
		{
			$query_upd_report = "
			UPDATE ".$GLOBALS['prefix_lms']."_coursereport
			SET weight = '".$_POST['weight']."',
				show_to_user = '".$_POST['show_to_user']."',
				use_for_final = '".$_POST['use_for_final']."'"
			.(isset($_POST['max_score']) && $_POST['max_score'] > 0 ? ", max_score = '".(float)$_POST['max_score']."'" : "")
			." WHERE  id_course = '".$_SESSION['idCourse']."' AND id_source = '".$id_test."' AND source_of = 'test'";
			$re = sql_query($query_upd_report);

			// save user score modification
			$re &= $test_man->saveTestUsersScores($id_test, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);
		} else {
			$query_upd_report = "
			UPDATE ".$GLOBALS['prefix_lms']."_coursereport
			SET weight = '".$_POST['weight']."',
				show_to_user = '".$_POST['show_to_user']."',
				use_for_final = '".$_POST['use_for_final']."'"
			.(isset($_POST['max_score']) && $_POST['max_score'] > 0 ? ", max_score = '".(float)$_POST['max_score']."'" : "")
			." WHERE  id_course = '".$_SESSION['idCourse']."' AND id_source = '".$id_test."' AND source_of = 'test'";
			$re = sql_query($query_upd_report);
		}
		return $re;
}

function testvote() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.json.php');

	// XXX: Initializaing
	$id_test 		= importVar('id_test', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: Instance management
	$acl_man 		= Docebo::user()->getAclManager();
	$test_man 		= new GroupTestManagement();
	$report_man 	= new CourseReportManager();

	// XXX: Find students
    $type_filter = false;
    if (isset($_GET['type_filter']) && $_GET['type_filter']!=null) {
		$type_filter = $_GET['type_filter'];
    }

	$lev = $type_filter;
    $students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
    $id_students = array_keys($students);
	$students_info 	=& $acl_man->getUsers($id_students);    

	// XXX: Find test
	$test_info		=& $test_man->getTestInfo(array($id_test));

	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($test_info[$id_test]['title'])
	);
	$GLOBALS['page']->add(
			getTitleArea($page_title, 'coursereport')
			.'<div class="std_block">', 'content');
	//==========================================================================================
	// XXX: Reset track of user
	if(isset($_POST['reset_track']))
	{
		$re = saveTestUpdate($id_test, $test_man);
		list($id_user, ) = each($_POST['reset_track']);

		$user_info = $acl_man->getUser($id_user, false);

		$GLOBALS['page']->add(
			Form::openForm('test_vote', 'index.php?modname=coursereport&amp;op=testvote')
			.Form::getHidden('id_test', 'id_test', $id_test)
			.Form::getHidden('id_user', 'id_user', $id_user)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_RESET').' : </span>'.strip_tags($test_info[$id_test]['title']).'<br />'
							.'<span>'.$lang->def('_OF_USER').' : </span>'.( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
									? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
									: $acl_man->relativeId($user_info[ACL_INFO_USERID]) ),
										false,
							'confirm_reset',
							'undo_reset')
			.Form::closeForm()
			.'</div>', 'content');
		return;
	}
	if(isset($_POST['confirm_reset']))
	{
		$id_user = importVar('id_user', true, 0);
		if($test_man->deleteTestTrack($id_test, $id_user))
			$GLOBALS['page']->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')), 'content');//($lang->def('_RESET_TRACK_SUCCESS')), 'content');
		else
			$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')), 'content');
	}

	//==========================================================================================

	if(isset($_POST['save']))
	{
		$re = saveTestUpdate($id_test, $test_man);
		Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&resul='.( $re ? 'ok' : 'err' ));
	}

	// retirive activity info
	$query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'
	AND source_of = 'test' AND id_source = '".$id_test."'";

	$info_report = sql_fetch_assoc(sql_query($query_report));

	$query =	"SELECT question_random_number"
				." FROM ".$GLOBALS['prefix_lms']."_test"
				." WHERE idTest = '".$id_test."'";

	list($question_random_number) = sql_fetch_row(sql_query($query));

	$json = new Services_JSON();
	$chart_options = $json->decode($chart_options_json);
	if (!property_exists($chart_options, 'use_charts')) $chart_options->use_charts = false;
	if (!property_exists($chart_options, 'selected_chart')) $chart_options->selected_chart = 'column';
	if (!property_exists($chart_options, 'show_chart')) $chart_options->show_chart = 'teacher';

	/* XXX: scores */
	$tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));

	if ($chart_options->use_charts) {
		$type_h = array('', 'align-center' , 'align-center', 'image', 'align-center', '', 'image');
		$cont_h = array( 	$lang->def('_STUDENTS'),
						$lang->def('_SCORE'),
						$lang->def('_SHOW_ANSWER'),
						'<img src="'.getPathImage('lms').'standard/stats22.gif" alt="'.$lang->def('_SHOW_CHART').'" title="'.$lang->def('_SHOW_CHART_TITLE').'" />',
						$lang->def('_DATE'),
						$lang->def('_COMMENTS'),
						'<img src="'.getPathImage('lms').'standard/delete.png" alt="'.$lang->def('_RESET').'" title="'.$lang->def('_RESET').'" />' );
	} else {
		$type_h = array('', 'align-center' , 'align-center', 'align-center', '', 'image');
		$cont_h = array( 	$lang->def('_STUDENTS'),
							$lang->def('_SCORE'),
							$lang->def('_SHOW_ANSWER'),
							$lang->def('_DATE'),
							$lang->def('_COMMENTS'),
							'<img src="'.getPathImage('lms').'standard/delete.png" alt="'.$lang->def('_RESET').'" title="'.$lang->def('_RESET').'" />' );
	}
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	$out->add(
		Form::openForm('test_vote', 'index.php?modname=coursereport&amp;op=testvote')
		.Form::getHidden('id_test', 'id_test', $id_test)
	);

	$out->add(
		// main form
		Form::openElementSpace()
		.Form::getOpenFieldSet($lang->def('_TEST_INFO'))

		.Form::getLinebox(	$lang->def('_TITLE_ACT'),
							strip_tags($test_info[$id_test]['title']) )
		.($question_random_number ? Form::getTextfield($lang->def('_MAX_SCORE'), 'max_score', 'max_score', '11', $info_report['max_score']) : Form::getLinebox($lang->def('_MAX_SCORE'), $info_report['max_score']))
		.Form::getLinebox(	$lang->def('_REQUIRED_SCORE'),
							$info_report['required_score'] )

		.Form::getTextfield(	$lang->def('_WEIGHT'),
								'weight',
								'weight',
								'11',
								$info_report['weight'] )
		.Form::getDropdown(		$lang->def('_SHOW_TO_USER'),
								'show_to_user',
								'show_to_user',
								array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
								$info_report['show_to_user'] )
		.Form::getDropdown(		$lang->def('_USE_FOR_FINAL'),
								'use_for_final',
								'use_for_final',
								array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
								$info_report['use_for_final'] )
		.Form::getCloseFieldSet()
		.Form::closeElementSpace()
	);

	// XXX: retrive scores
	$tests_score 	=& $test_man->getTestsScores(array($id_test), $id_students);

	// XXX: Display user scores
	$i = 0;
	while(list($idst_user, $user_info) = each($students_info))
	{
		$user_name = ( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
						? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
						: $acl_man->relativeId($user_info[ACL_INFO_USERID]) );

		$cont = array(Form::getLabel('user_score_'.$idst_user, $user_name));

		$id_test = $info_report['id_source'];
		if(isset($tests_score[$id_test][$idst_user]))
		{
			switch($tests_score[$id_test][$idst_user]['score_status'])
			{
				case "not_complete" : {
					$cont[] = '-';
				};break;
				case "not_checked" 	: {
					$cont[] = '<span class="cr_not_check">'.$lang->def('_NOT_CHECKED').'</span><br />'
								.Form::getInputTextfield(	'textfield_nowh',
															'user_score_'.$idst_user,
															'user_score['.$idst_user.']',
															$tests_score[$id_test][$idst_user]['score'],
															strip_tags($lang->def('_SCORE')),
															'8',
															' tabindex="'.$i++.'" ' );
				};break;
				case "not_passed" 	:
				case "passed" 		: {
				/*
					$cont[] = Form::getInputDropdown(	'dropdown',
															'user_score',
															'user_score',
															array('passed' => $lang->def('_PASSED'), 'not_passed' => $lang->def('_NOT_PASSED')),
															$tests_score[$id_test][$idst_user]['score_status'],
															'');
															*/
					$cont[] = Form::getInputTextfield(	'textfield_nowh',
														'user_score_'.$idst_user,
														'user_score['.$idst_user.']',
														$tests_score[$id_test][$idst_user]['score'],
														strip_tags($lang->def('_SCORE')),
														'8',
														' tabindex="'.$i++.'" ' );

				};break;
				case "valid" 		: {
					$cont[] = Form::getInputTextfield(	'textfield_nowh',
														'user_score_'.$idst_user,
														'user_score['.$idst_user.']',
														$tests_score[$id_test][$idst_user]['score'],
														strip_tags($lang->def('_SCORE')),
														'8',
														' tabindex="'.$i++.'" ' );
				};break;
				default : {

					$cont[] = '-';
				}
			}
			if($tests_score[$id_test][$idst_user]['score_status'] != 'not_comlete')
			{
				$cont[] = Form::getButton('view_anser_'.$idst_user, 'view_answer['.$idst_user.']', $lang->def('_SHOW_ANSWER'), 'button_nowh');

				if ($chart_options->use_charts) {
					$img = '<img src="'.getPathImage('lms').'standard/stats22.gif" alt="'.$lang->def('_SHOW_CHART').'" title="'.$lang->def('_SHOW_CHART_TITLE').'" />';
					$url = 'index.php?modname=coursereport&op=showchart&id_test='.(int)$id_test.'&id_user='.(int)$idst_user.'&chart_type='.$chart_options->selected_chart;
					$cont[] = '<a href="'.$url.'">'.$img.'</a>';
				}

				$cont[] = Form::getInputDatefield(	'textfield_nowh',
													'date_attempt_'.$idst_user,
													'date_attempt['.$idst_user.']',
													Format::date($tests_score[$id_test][$idst_user]['date_attempt']) );

				$cont[] = Form::getInputTextarea(	'comment_'.$idst_user,
													'comment['.$idst_user.']',
													$tests_score[$id_test][$idst_user]['comment'],
													'textarea_wh_full',
													2);

				$cont[] = '<input 	class="reset_track"
									type="image"
									src="'.getPathImage('lms').'standard/delete.png"
									alt="'.$lang->def('_RESET').'"
									id="reset_track_'.$idst_user.'"
									name="reset_track['.$idst_user.']"
									title="'.$lang->def('_RESET').'" />';
			}
		} else {
			$cont[] = '-'; //...
			$cont[] = '-';
			$cont[] = '-';
			$cont[] = '-';
			$cont[] = '-';
			// $cont[] = '-';
		}
		$tb->addBody($cont);
	}

	$out->add(
		Form::openButtonSpace()
		.Form::getButton('save_top', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo_top', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()

		.$tb->getTable()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>');
}

function testDetail()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');

	$lang =& DoceboLanguage::createInstance('coursereport', 'lms');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$id_test = importVar('id_test', true, 0);

	$test_man = new GroupTestManagement();
	$acl_man = Docebo::user()->getAclManager();

	$quests = array();
	$answers = array();
	$tracks = array();

	$test_info =& $test_man->getTestInfo(array($id_test));

    $page_title = array(	'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
							'index.php?modname=coursereport&amp;op=testdetail&amp;id_test='.$id_test => $test_info[$id_test]['title']
    );

    $out->add(	getTitleArea($page_title, 'coursereport')
				.'<div class="std_block">'
    );

    $query_test =	"SELECT title"
					." FROM ".$GLOBALS['prefix_lms']."_test"
					." WHERE idTest = '".$id_test."'";

    list($titolo_test) = sql_fetch_row(sql_query($query_test));

    $query_quest =	"SELECT idQuest, type_quest, title_quest"
					." FROM ".$GLOBALS['prefix_lms']."_testquest"
					." WHERE idTest = '".$id_test."'"
					." ORDER BY sequence";

	$result_quest = sql_query($query_quest);

	while (list($id_quest, $type_quest, $title_quest) = sql_fetch_row($result_quest))
	{
		$quests[$id_quest]['idQuest'] = $id_quest;
		$quests[$id_quest]['type_quest'] = $type_quest;
		$quests[$id_quest]['title_quest'] = $title_quest;

		$query_answer =	"SELECT idAnswer, is_correct, answer"
						." FROM ".$GLOBALS['prefix_lms']."_testquestanswer"
						." WHERE idQuest = '".$id_quest."'"
						." ORDER BY sequence";

		$result_answer = sql_query($query_answer);

		while (list($id_answer, $is_correct, $answer) = sql_fetch_row($result_answer))
		{
			$answers[$id_quest][$id_answer]['idAnswer'] = $id_answer;
			$answers[$id_quest][$id_answer]['is_correct'] = $is_correct;
			$answers[$id_quest][$id_answer]['answer'] = $answer;
		}
	}

	$query_track =	"SELECT idTrack"
					." FROM ".$GLOBALS['prefix_lms']."_testtrack"
					." WHERE idTest = '".$id_test."'";

	$result_track = sql_query($query_track);

	while(list($id_track) = sql_fetch_row($result_track))
	{
		$query_track_answer =	"SELECT idQuest, idAnswer"
								." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer"
								." WHERE idTrack = '".$id_track."'";

		$result_track_answer = sql_query($query_track_answer);

		while(list($id_quest, $id_answer) = sql_fetch_row($result_track_answer))
			$tracks[$id_track][$id_quest] = $id_answer;
	}
}

function testreview() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');

	// XXX: Initializaing
	$id_test 		= importVar('id_test', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: Instance management
	$acl_man 		= Docebo::user()->getAclManager();
	$test_man 		= new GroupTestManagement();
	$report_man 	= new CourseReportManager();

	// XXX: Save input if needed
	if(isset($_POST['view_answer'])) {
		$re = saveTestUpdate($id_test, $test_man);
		list($id_user, ) = each($_POST['view_answer']);
	} else {
		$id_user = importVar('id_user', true, 0);
	}

	if(isset($_POST['save_new_scores']))
	{
		$re = $test_man->saveReview($id_test, $id_user);
		Util::jump_to('index.php?modname=coursereport&amp;op=testvote&amp;id_test='.$id_test.'&result='.( $re ? 'ok' : 'err' ));
	}

	$user_name = $acl_man->getUserName($id_user);

	// XXX: Find test
	$test_info =& $test_man->getTestInfo(array($id_test));

	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		'index.php?modname=coursereport&amp;op=testvote&amp;id_test='.$id_test =>$test_info[$id_test]['title'],
		$user_name
	);
	$out->add(
		getTitleArea($page_title, 'coursereport')
		.'<div class="std_block">'
		.Form::openForm('test_vote', 'index.php?modname=coursereport&amp;op=testreview')
		.Form::getHidden('id_test', 'id_test', $id_test)
		.Form::getHidden('id_user', 'id_user', $id_user)
	);
	$test_man->editReview($id_test, $id_user);
	$out->add(
		Form::openButtonSpace()
		.Form::getButton('save_new_scores', 'save_new_scores', $lang->def('_SAVE'))
		.Form::getButton('undo_testreview', 'undo_testreview', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
	);
}

function finalvote() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_report 		= importVar('id_report', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: Instance management
	$acl_man 		= Docebo::user()->getAclManager();
	$report_man 	= new CourseReportManager();

	// XXX: Find students
	$type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int) $_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
        $id_students = array_keys($students);
        $students_info 	=& $acl_man->getUsers($id_students);

	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($lang->def('_FINAL_SCORE'))
	);
	$out->add(
		getTitleArea($page_title, 'coursereport')
		.'<div class="std_block">'
		.Form::openForm('finalvote', 'index.php?modname=coursereport&amp;op=finalvote&amp;type_filter='.$type_filter)
		.Form::getHidden('id_report', 'id_report', $id_report)
	);

	// XXX: Save input if needed
	if(isset($_POST['save']))
	{
		// Save report modification
		$query_upd_report = "
		UPDATE ".$GLOBALS['prefix_lms']."_coursereport
		SET max_score = '".$_POST['max_score']."',
			required_score = '".$_POST['required_score']."',
			show_to_user = '".$_POST['show_to_user']."'
		WHERE  id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'
			AND source_of = 'final_vote' AND id_source = '0'";
		sql_query($query_upd_report);
		// save user score modification

		$re = $report_man->saveReportScore($id_report, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);

		Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&result='.( $re ? 'ok' : 'err' ));
	}

	if(isset($_POST['save']))
	{
		// retirive activity info
		$info_report = array(
			'max_score' => importVar('max_score', true),
			'required_score' => importVar('required_score', true),
			'weight' => importVar('weight', true),
			'show_to_user' => importVar('show_to_user', false, 'true'),
			'id_source' => 0,
			'source_of' => 'final_vote'
		);
	} else {
		// retirive activity info
		$query_report = "
		SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
		FROM ".$GLOBALS['prefix_lms']."_coursereport
		WHERE id_course = '".$_SESSION['idCourse']."'
				AND source_of = 'final_vote' AND id_source = '0'";
		$info_report = sql_fetch_assoc(sql_query($query_report));
	}

	$out->add(
		// main form
		Form::openElementSpace()
		.Form::getOpenFieldSet($lang->def('_TEST_INFO'))

		.Form::getLinebox(	$lang->def('_TITLE_ACT'),
							$lang->def('_FINAL_SCORE') )
		.Form::getTextfield(	$lang->def('_MAX_SCORE'),
								'max_score',
								'max_score',
								'11',
								$info_report['max_score'] )
		.Form::getTextfield(	$lang->def('_REQUIRED_SCORE'),
								'required_score',
								'required_score',
								'11',
								$info_report['required_score'] )
		.Form::getDropdown(		$lang->def('_SHOW_TO_USER'),
								'show_to_user',
								'show_to_user',
								array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
								$info_report['show_to_user'] )
		.Form::getCloseFieldSet()
		.Form::closeElementSpace()
	);

	/* XXX: scores */
	$tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));
	$type_h = array('', 'align-center' , 'align-center', 'align-center', '');
	$cont_h = array( 	$lang->def('_STUDENTS'),
						$lang->def('_SCORE'),
						$lang->def('_DATE'),
						$lang->def('_COMMENTS') );
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	// XXX: retrive scores
	$report_score 	=& $report_man->getReportsScores(array($id_report));

	// XXX: Display user scores
	$i = 0;
	while(list($idst_user, $user_info) = each($students_info)) {

		$user_name = ( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
						? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
						: $acl_man->relativeId($user_info[ACL_INFO_USERID]) );
		$cont = array(Form::getLabel('user_score_'.$idst_user, $user_name));

		$cont[] = Form::getInputTextfield(	'textfield_nowh',
													'user_score_'.$idst_user,
													'user_score['.$idst_user.']',
													( isset($report_score[$id_report][$idst_user]['score'])
														? $report_score[$id_report][$idst_user]['score'] : '' ),
													strip_tags($lang->def('_SCORE')),
													'8',
													' tabindex="'.$i++.'" ' );
		$cont[] = Form::getInputDatefield(	'textfield_nowh',
													'date_attempt_'.$idst_user,
													'date_attempt['.$idst_user.']',
													Format::date(
														( isset($report_score[$id_report][$idst_user]['date_attempt'])
															? $report_score[$id_report][$idst_user]['date_attempt'] : '' ), 'date'));
		$cont[] = Form::getInputTextarea(	'comment_'.$idst_user,
											'comment['.$idst_user.']',
											( isset($report_score[$id_report][$idst_user]['comment'])
															? $report_score[$id_report][$idst_user]['comment'] : '' ),
											'textarea_wh_full',
											2);

		$tb->addBody($cont);
	}

	$out->add(
		Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()

		.$tb->getTable()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>');
}

function roundtest()
{
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_test 		= importVar('id_test', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: Instance management
	$acl_man 		= Docebo::user()->getAclManager();
	$test_man 		= new GroupTestManagement();
	$report_man 	= new CourseReportManager();

	// XXX: Find test from organization
	$re = $test_man->roundTestScore($id_test);

	Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result='.( $re ? 'ok' : 'err' ));
}

function roundreport() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_report 		= importVar('id_report', true, 0);

	// XXX: Instance management
	$report_man		= new CourseReportManager();

	// XXX: Find test from organization
	$re = $report_man->roundReportScore($id_report);

	Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result='.( $re ? 'ok' : 'err' ));
}

/**
 *	final_score =
 *
 *	sum( (score[n] * weight[n]) / total_weigth )
 *	----------------------------------------------------  * final_max_score
 *	sum( (max_score[n] * weight[n]) / total_weigth )
 *
 * equal to :
 *	sum( score[n] * weight[n] )
 *	--------------------------------  * final_max_score
 *	sum( max_score[n] * weight[n] )
 */

function redofinal() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');

	// XXX: Instance management
	$acl_man 	= Docebo::user()->getAclManager();
	$test_man 	= new GroupTestManagement();
	$report_man = new CourseReportManager();

	// XXX: Find students
	$id_students	=& $report_man->getStudentId();

	// XXX: retrive info about the final score
	 $query_final = "
	SELECT id_report, max_score
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND source_of = 'final_vote'";
	$info_final = sql_fetch_assoc(sql_query($query_final));

	// XXX: Retrive all reports (test and so), and set it

	$query_report = "
	SELECT id_report, max_score, weight, source_of, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND use_for_final = 'true' AND source_of <> 'final_vote'
	ORDER BY sequence ";

	$re_report = sql_query($query_report);
	if(!sql_num_rows($re_report)) {
		Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result=ok');
	}

	$sum_max_score = 0;
	$included_test 	= array();
	$other_source = array();
	while($info_report = sql_fetch_assoc($re_report)) {

		$sum_max_score 	+= $info_report['max_score'] * $info_report['weight'];

		$reports_info[$info_report['id_report']] = $info_report;

		switch($info_report['source_of']) {
			case "activity" : $other_source[$info_report['id_report']] = $info_report['id_report'];break;
			case "test" : $included_test[$info_report['id_source']] = $info_report['id_source'];break;
		}
	}

	// XXX: Retrive Test score
	if(!empty($included_test))
		$tests_score =& $test_man->getTestsScores($included_test, $id_students);

	// XXX: Retrive other score
	if(!empty($other_source))
		$other_score =& $report_man->getReportsScores($other_source);

	$final_score = array();
	while(list(, $id_user) = each($id_students))
	{
		$user_score = 0;
		while(list($id_report, $rep_info) = each($reports_info))
		{
			$id_source = $rep_info['id_source'];
			switch($rep_info['source_of'])
			{
				case "activity" : {
					if(isset($other_score[$id_report][$id_user]) && ($other_score[$id_report][$id_user]['score_status'] == 'valid')) {
						$user_score += ($other_score[$id_report][$id_user]['score'] * $rep_info['weight']);
					} else {
						$user_score += 0;
					}
				};break;
				case "test" : {
					if(isset($tests_score[$id_source][$id_user]) && ($tests_score[$id_source][$id_user]['score_status'] == 'valid')) {
						$user_score += ($tests_score[$id_source][$id_user]['score'] * $rep_info['weight']);
					} else {
						$user_score += 0;
					}
				};break;
			}
		}

		reset($reports_info);
		// user final score
		if($sum_max_score != 0)
			$final_score[$id_user] = round(($user_score / $sum_max_score) * $info_final['max_score'], 2);
		else
			$final_score[$id_user] = 0;
	}
	// Save final scores
	$exists_final = array();
	$query_final_score = "
	SELECT id_user
	FROM ".$GLOBALS['prefix_lms']."_coursereport_score
	WHERE id_report = '".$info_final['id_report']."'";
	$re_final = sql_query($query_final_score);
	while(list($id_user) = sql_fetch_row($re_final))
		$exists_final[$id_user] = $id_user;
	$re = true;
	while(list($user, $score) = each($final_score))
	{
		if(isset($exists_final[$user]))
		{
			$query_scores = "
			UPDATE ".$GLOBALS['prefix_lms']."_coursereport_score
			SET score = '".$score."',
				date_attempt = '".date("Y-m-d H:i:s")."'
			WHERE id_report = '".$info_final['id_report']."' AND id_user = '".$user."'";
			$re &= sql_query($query_scores);
		} else {
			$query_scores = "
			INSERT INTO  ".$GLOBALS['prefix_lms']."_coursereport_score
			( id_report, id_user, score, date_attempt ) VALUES (
				'".$info_final['id_report']."',
				'".$user."',
				'".$score."',
				'".date("Y-m-d H:i:s")."' )";
			$re &= sql_query($query_scores);
		}
	}
	Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result='.( $re ? 'ok' : 'err' ));
}

function modscorm()
{
	checkPerm('mod');

	require_once(_lms_.'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_report 		= Get::req('id_report', DOTY_INT, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: undo
	if(isset($_POST['undo']))
		jumpTo('index.php?modname=coursereport&amp;op=coursereport');

	// XXX: Retrive all colums (test and so), and set it
	if($id_report == 0)
	{
		$info_report = array(
			'id_report' => importVar('id_report', true, 0),
			'title' => importVar('title'),
			'max_score' => importVar('max_score', true),
			'required_score' => importVar('required_score', true),
			'weight' => importVar('weight', true),
			'show_to_user' => importVar('show_to_user', false, 'true'),
			'use_for_final' => importVar('use_for_final', false, 'true'),
			'source_of' => '',
			'id_source' => '0'
		);
	}
	elseif(!isset($_POST['save']))
	{
		$query_report = "
		SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
		FROM ".$GLOBALS['prefix_lms']."_coursereport
		WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'
				AND source_of = 'activity' AND id_source = '0'";
		$info_report = sql_fetch_assoc(sql_query($query_report));
	}

	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($lang->def('_ADD_ACTIVITY'))
	);
	$out->add(
		getTitleArea($page_title, 'coursereport')
		.'<div class="std_block">'

		.getBackUi('index.php?modname=coursereport&amp;op=coursereport', $lang->def('_BACK'))
	);
	// XXX: Save input if needed
	if(isset($_POST['save']) && is_numeric($_POST['id_source']))
	{
		$report_man = new CourseReportManager();
		// check input
		if($_POST['titolo'] == '' )
            $_POST['titolo'] = $lang->def('_NOTITLE');
		//MODIFICHE NUOVISSIMISSIME
		$query_report = "
		SELECT  *
		FROM ".$GLOBALS['prefix_lms']."_scorm_items
		WHERE idscorm_item=".$_POST['id_source'];
		//echo $query_report;
		$risultato=sql_query($query_report);
		$titolo2=sql_fetch_assoc($risultato);

        // if module title is equals to main title don't append it
        if ($titolo2['title']!=$_POST['titolo']){
			$_POST['titolo']=$_POST['titolo']." - ".addslashes($titolo2['title']);
        }

		$_POST['title']=$_POST['titolo'];
		$re_check = $report_man->checkActivityData($_POST);

		if(!$re_check['error'])
		{
			if($id_report == 0)
			{
				$numero = $report_man->getNextSequence();
				$query_ins_report = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_coursereport
				( id_course, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
					'".$_SESSION['idCourse']."',
					'".$_POST['title']."',
					'0',
					'0',
					'".$_POST['weight']."',
					'".$_POST['show_to_user']."',
					'".$_POST['use_for_final']."',
					'".$_POST['source_of']."',
					'".$_POST['id_source']."',
					'".$numero."'
				)";
				echo $query_ins_report;

				$re = sql_query($query_ins_report);
			}
			else
			{
					$query_upd_report = "
				UPDATE ".$GLOBALS['prefix_lms']."_coursereport
				SET title = '".$_POST['title']."',
					weight = '".$_POST['weight']."',
					max_score = '0',
					required_score = '0',
					use_for_final = '".$_POST['use_for_final']."',
					show_to_user = '".$_POST['show_to_user']."'
				WHERE id_course = '".$_POST['id_course']."' AND id_report = '".$id_report."'";
				$re = sql_query($query_upd_report);
			}
			Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&result='.( $re ? 'ok' : 'err' ));
		}
		else
			$out->add(getErrorUi($re_check['message']));
	}

	if(isset($_POST['filtra']))
	{
		if($_POST['source_of']=='scoitem' && is_numeric($_POST['title']))
		{//richiesto lo scorm item
			$query_report = "
			SELECT  title
			FROM ".$GLOBALS['prefix_lms']."_organization
			WHERE objectType='scormorg' and idResource=".(int)$_POST['title']."";
			$risultato=sql_query($query_report);
			$titolo=sql_fetch_assoc($risultato);
			$titolo=$titolo['title'];

			$query_report = "
			SELECT  *
			FROM ".$GLOBALS['prefix_lms']."_scorm_items
			WHERE idscorm_organization=".(int)$_POST['title']."
			ORDER BY idscorm_item";
			//echo $query_report;
			$risultato=sql_query($query_report);
			while($scorm=sql_fetch_assoc($risultato))
				$array_scorm[$scorm['idscorm_item']]=$scorm['title'];

			$out->add(
			Form::openForm('addscorm', 'index.php?modname=coursereport&amp;op=addscorm')
			.Form::openElementSpace()
			.Form::getHidden('id_report', 'id_report', $id_report)
			.Form::getDropdown(	$lang->def('_SCORM_ITEM'),
									'id_source',
									'id_source',
									$array_scorm,
									$info_report['id_source'] )



			.Form::getTextfield(	$lang->def('_WEIGHT'),
									'weight',
									'weight',
									'11',
									$info_report['weight'] )
			.Form::getDropdown(		$lang->def('_SHOW_TO_USER'),
									'show_to_user',
									'show_to_user',
									array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
									$info_report['show_to_user'] )
			.Form::getDropdown(		$lang->def('_USE_FOR_FINAL'),
									'use_for_final',
									'use_for_final',
									array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
									$info_report['use_for_final'] )
			.Form::getHidden( 'title', 'title', $_POST['title'] )
			.Form::getHidden('source_of','source_of',$_POST['source_of'] )
			.Form::getHidden('titolo','titolo',$titolo )
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('save', 'save', $lang->def('_SAVE'))
			.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>');
		}
	}
	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($lang->def('_ADD_ACTIVITY'))
	);

	if(!isset($_POST['filtra']))
	{
		$query_report = "
			SELECT  idResource,title
			FROM ".$GLOBALS['prefix_lms']."_organization
			WHERE objectType='scormorg' and idCourse=".$_SESSION['idCourse']."";
			$risultato=sql_query($query_report);
			while($scorm=sql_fetch_assoc($risultato))
				$array_scorm[$scorm['idResource']]=$scorm['title'];

		$out->add(
			Form::openForm('addscorm', 'index.php?modname=coursereport&amp;op=addscorm')
			.Form::openElementSpace()
			.Form::getHidden('id_report', 'id_report', $id_report)
			.Form::getDropdown(		$lang->def('_TITLE'),
									'title',
									'title',
									$array_scorm,
									$info_report['title'] )

			.Form::getRadioSet(	$lang->def('_SCORE'),
									'source_of',
									'source_of',
									array(  "Scorm Item"  =>'scoitem'),//,  "Somma" => 'scormorg_sum', "Media"  =>'scormorg_avg'),
									'scoitem' )

			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('filtra', 'filtra', $lang->def('_SAVE'))
			.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>');
	}
}

function modactivity() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_report 		= importVar('id_report', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: undo
	if(isset($_POST['undo'])) {
		Util::jump_to('index.php?modname=coursereport&amp;op=coursereport');
	}

	// XXX: Retrive all colums (test and so), and set it
	if($id_report == 0) {

		$info_report = array(
			'id_report' => importVar('id_report', true, 0),
			'title' => importVar('title'),
			'max_score' => importVar('max_score', true),
			'required_score' => importVar('required_score', true),
			'weight' => importVar('weight', true),
			'show_to_user' => importVar('show_to_user', false, 'true'),
			'use_for_final' => importVar('use_for_final', false, 'true')
		);
	} elseif(!isset($_POST['save'])) {
		$query_report = "
		SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final
		FROM ".$GLOBALS['prefix_lms']."_coursereport
		WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'
				AND source_of = 'activity' AND id_source = '0'";
		$info_report = sql_fetch_assoc(sql_query($query_report));
	}

	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($lang->def('_ADD_ACTIVITY'))
	);
	$out->add(
		getTitleArea($page_title, 'coursereport')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=coursereport&amp;op=coursereport', $lang->def('_BACK'))
	);
	// XXX: Save input if needed
	if(isset($_POST['save'])) {
		$report_man = new CourseReportManager();
		// check input
		if($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');

		$re_check = $report_man->checkActivityData($_POST);
		if(!$re_check['error']) {
			if($id_report == 0) $re = $report_man->addActivity($_SESSION['idCourse'], $_POST);
			else $re = $report_man->updateActivity($id_report, $_SESSION['idCourse'], $_POST);
			Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&result='.( $re ? 'ok' : 'err' ));
		} else
			$out->add(getErrorUi($re_check['message']));
	}

	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($lang->def('_ADD_ACTIVITY'))
	);
	$out->add(
		Form::openForm('addactivity', 'index.php?modname=coursereport&amp;op=addactivity')
		.Form::openElementSpace()
		.Form::getHidden('id_report', 'id_report', $id_report)
		.Form::getTextfield(	$lang->def('_TITLE_ACT'),
								'title',
								'title',
								'255',
								$info_report['title'] )
		.Form::getTextfield(	$lang->def('_MAX_SCORE'),
								'max_score',
								'max_score',
								'11',
								$info_report['max_score'] )
		.Form::getTextfield(	$lang->def('_REQUIRED_SCORE'),
								'required_score',
								'required_score',
								'11',
								$info_report['required_score'] )
		.Form::getTextfield(	$lang->def('_WEIGHT'),
								'weight',
								'weight',
								'11',
								$info_report['weight'] )
		.Form::getDropdown(		$lang->def('_SHOW_TO_USER'),
								'show_to_user',
								'show_to_user',
								array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
								$info_report['show_to_user'] )
		.Form::getDropdown(		$lang->def('_USE_FOR_FINAL'),
								'use_for_final',
								'use_for_final',
								array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
								$info_report['use_for_final'] )
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>');
}

function modactivityscore() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_report 		= importVar('id_report', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: Instance management
	$acl_man 		= Docebo::user()->getAclManager();
	$report_man 	= new CourseReportManager();

	// XXX: Find users
    $type_filter = false;
    if (isset($_GET['type_filter']) && $_GET['type_filter']!=null) {
		$type_filter = $_GET['type_filter'];
    }

	$lev = $type_filter;
    $students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
    $id_students = array_keys($students);
	$students_info 	=& $acl_man->getUsers($id_students);

	if(isset($_POST['save']))
	{
		// retirive activity info
		$info_report = array(
			'id_report' => importVar('id_report', true, 0),
			'title' => importVar('title'),
			'max_score' => importVar('max_score', true),
			'required_score' => importVar('required_score', true),
            'source_of' => importVar('source_of'),
			'weight' => importVar('weight', true),
			'show_to_user' => importVar('show_to_user', false, 'true'),
			'use_for_final' => importVar('use_for_final', false, 'true')
		);
		// XXX: retrive scores
	} else {
		// retirive activity info
		$query_report = "
		SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, id_source, source_of
		FROM ".$GLOBALS['prefix_lms']."_coursereport
		WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'
				AND (source_of = 'scoitem' OR source_of = 'activity')"; // TBD AND id_source = '0'";
		$info_report = sql_fetch_assoc(sql_query($query_report));

		// XXX: retrive scores
		$report_score 	=& $report_man->getReportsScores(array($id_report));
	}

	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		strip_tags($info_report['title'])
	);
	$out->add(
		getTitleArea($page_title, 'coursereport')
		.'<div class="std_block">'
		.Form::openForm('activity', 'index.php?modname=coursereport&amp;op=modactivityscore')
	);

	// XXX: Save input if needed
	if(isset($_POST['save'])) {
		if($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');
		$re_check = $report_man->checkActivityData($_POST);
		if(!$re_check['error']) {
			if(!$report_man->updateActivity($id_report, $_SESSION['idCourse'], $info_report)) {
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			} else {
				// save user score modification
                $query_upd_report = "
				UPDATE ".$GLOBALS['prefix_lms']."_coursereport
				SET weight = '".$info_report['weight']."',
					use_for_final = '".$info_report['use_for_final']."',
					show_to_user = '".$info_report['show_to_user']."'
				WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'";
				$re = sql_query($query_upd_report);
				$re = $report_man->saveReportScore($id_report, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);
				Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&result='.( $re ? 'ok' : 'err' ));
			}
		} else {
			$out->add(getErrorUi($re_check['message']));
		}
	}

	// main form
    $out->add(
            Form::openElementSpace()
            . Form::getOpenFieldSet($lang->def('_ACTIVITY_INFO'))
            . Form::getHidden('id_report', 'id_report', $id_report)
            . Form::getHidden('id_source', 'id_source', $info_report['id_source'])
            . Form::getHidden('source_of', 'source_of', $info_report['source_of'])
    );
    // for scorm object changing title, maxScore and requiredScore is not allowed
    switch ($info_report['source_of']) {
        case 'scoitem':
            $out->add(
                    Form::getLinebox(
                            $lang->def('_TITLE_ACT'),
                            strip_tags($info_report['title']))
                    . Form::getLinebox(
                            $lang->def('_MAX_SCORE'),
                            strip_tags($info_report['max_score']))
                    . Form::getLinebox(
                            $lang->def('_REQUIRED_SCORE'),
                            strip_tags($info_report['required_score']))
            );
            break;
        case 'activity':
            $out->add(
                    Form::getTextfield(
                            $lang->def('_TITLE_ACT'),
                            'title',
                            'title',
                            '255',
                            $info_report['title'])
                    . Form::getTextfield(
                            $lang->def('_MAX_SCORE'),
                            'max_score',
                            'max_score',
                            '11',
                            $info_report['max_score'])
                    . Form::getTextfield(
                            $lang->def('_REQUIRED_SCORE'),
                            'required_score',
                            'required_score',
                            '11',
                            $info_report['required_score'])
            );
            break;
    }
    $out->add(
            Form::getTextfield(
                    $lang->def('_WEIGHT'),
                    'weight',
                    'weight',
                    '11',
                    $info_report['weight'])
            . Form::getDropdown(
                    $lang->def('_SHOW_TO_USER'),
                    'show_to_user',
                    'show_to_user',
                    array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                    $info_report['show_to_user'])
            . Form::getDropdown(
                    $lang->def('_USE_FOR_FINAL'),
                    'use_for_final',
                    'use_for_final',
                    array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                    $info_report['use_for_final'])
            . Form::getCloseFieldSet()
            . Form::closeElementSpace()
    );

    if ($info_report['source_of']!='scoitem'){
        /* XXX: scores */
        $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));
        $type_h = array('', 'align-center', 'align-center', '');
        $tb->setColsStyle($type_h);
        $cont_h = array( 	$lang->def('_STUDENTS'),
                            $lang->def('_SCORE'),
                            $lang->def('_DATE'),
                            $lang->def('_COMMENTS') );
        $tb->addHead($cont_h);

        // XXX: Display user scores
        $i = 0;
        while(list($idst_user, $user_info) = each($students_info)) {
            $user_name = ( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
                            ? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
                            : $acl_man->relativeId($user_info[ACL_INFO_USERID]) );
            $cont = array(Form::getLabel('user_score_'.$idst_user, $user_name));

            $cont[] = Form::getInputTextfield(	'textfield_nowh',
                                                        'user_score_'.$idst_user,
                                                        'user_score['.$idst_user.']',
                                                        ( isset($report_score[$id_report][$idst_user]['score'])
                                                            ? $report_score[$id_report][$idst_user]['score']
                                                            : (isset($_POST['user_score'][$idst_user]) ? $_POST['user_score'][$idst_user] : '') ),
                                                        strip_tags($lang->def('_SCORE')),
                                                        '8',
                                                        ' tabindex="'.$i++.'" ' );
            $cont[] = Form::getInputDatefield(	'textfield_nowh',
                                                        'date_attempt_'.$idst_user,
                                                        'date_attempt['.$idst_user.']',
                                                        Format::date(
                                                            ( isset($report_score[$id_report][$idst_user]['date_attempt'])
                                                                ? $report_score[$id_report][$idst_user]['date_attempt']
                                                                : (isset($_POST['date_attempt'][$idst_user]) ? $_POST['date_attempt'][$idst_user] : '') ), 'date'));
            $cont[] = Form::getInputTextarea(	'comment_'.$idst_user,
                                                'comment['.$idst_user.']',
                                                ( isset($report_score[$id_report][$idst_user]['comment'])
                                                                ? $report_score[$id_report][$idst_user]['comment']
                                                                : (isset($_POST['comment'][$idst_user]) ? stripslashes($_POST['comment'][$idst_user]) : '') ),
                                                'textarea_wh_full',
                                                2);

            $tb->addBody($cont);
        }
    }

    $out->add(
        Form::openButtonSpace()
        .Form::getButton('save', 'save', $lang->def('_SAVE'))
        .Form::getButton('undo', 'undo', $lang->def('_UNDO'))
        .Form::closeButtonSpace()
    );
    if ($info_report['source_of']!='scoitem'){
        $out->add(
            $tb->getTable()
            .Form::openButtonSpace()
            .Form::getButton('save', 'save', $lang->def('_SAVE'))
            .Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            .Form::closeButtonSpace()
        );
    }
    $out->add(
        Form::closeForm()
        .'</div>'
    );
   
}

function delactivity() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	// XXX: Initializaing
	$id_report 		= importVar('id_report', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 			=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	// XXX: Instance management
	$acl_man 		= Docebo::user()->getAclManager();
	$report_man 	= new CourseReportManager();

	if(isset($_POST['confirm'])) {

		if(!$report_man->deleteReportScore($id_report)) {
			Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result=err');
		}

		$re = $report_man->deleteReport($id_report);

		Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result='.( $re ? 'ok' : 'err' ));
	}

	// retirive activity info
	$query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'
			AND source_of = 'activity' AND id_source = '0'";
	$info_report = sql_fetch_assoc(sql_query($query_report));

	// XXX: Write in output
	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		$lang->def('_DEL').' : '.strip_tags($info_report['title'])
	);
	$out->add(
		getTitleArea($page_title, 'coursereport')
		.'<div class="std_block">'
		.Form::openForm('delactivity', 'index.php?modname=coursereport&amp;op=delactivity')
		.Form::getHidden('id_report', 'id_report', $id_report)
		.getDeleteUi(	$lang->def('_AREYOUSURE'),
				$lang->def('_TITLE_ACT').' : '.$info_report['title'],
				false,
				'confirm',
				'undo' )
		.Form::closeForm()
		.'</div>');
}

function movereport($direction) {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');

	// XXX: Initializaing
	$id_report 		= importVar('id_report', true, 0);
	$lang 			=& DoceboLanguage::createInstance('coursereport', 'lms');

	// XXX: Instance management
	$report_man 	= new CourseReportManager();

	list($seq) = sql_fetch_row(sql_query("
	SELECT sequence
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'"));

	if($direction == 'left') {
		$re = sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_coursereport
		SET sequence = '".$seq."'
		WHERE id_course = '".$_SESSION['idCourse']."' AND sequence = '".($seq - 1)."'");
		$re &= sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_coursereport
		SET sequence = sequence - 1
		WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'");

	}
	if($direction == 'right') {
		$re = sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_coursereport
		SET sequence = '$seq'
		WHERE id_course = '".$_SESSION['idCourse']."' AND sequence = '".($seq + 1)."'");
		$re &= sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_coursereport
		SET sequence = sequence + 1
		WHERE id_course = '".$_SESSION['idCourse']."' AND id_report = '".$id_report."'");
	}

	Util::jump_to('index.php?modname=coursereport&amp;op=coursereport&amp;result='.( $re ? 'ok' : 'err' ));
}

function export()
{
	checkPerm('view');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang 	=& DoceboLanguage::createInstance('coursereport', 'lms');
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$included_test 	= array();
	$mod_perm = checkPerm('mod', true);
	$csv = '';

	$acl_man 	= Docebo::user()->getAclManager();
	$test_man 	= new GroupTestManagement();
	$report_man = new CourseReportManager();

	$org_tests 		=& $report_man->getTest();
	$tests_info		= $test_man->getTestInfo($org_tests);

	$id_students	=& $report_man->getStudentId();
	$students_info 	=& $acl_man->getUsers($id_students);

	$lang2 =& DoceboLanguage::createInstance('levels', 'lms');

	if(isset($_POST['type_filter']))
		$type_filter = $_POST['type_filter'];
	else
		$type_filter = false;

	if($type_filter=="false")
		$type_filter = false;

	$lev = $type_filter;

	$students = getSubscribedInfo((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
	$i=0;
	$students_info=array();
	foreach( $students as $idst => $user_course_info )
		$students_info[$idst] =& $acl_man->getUser( $idst, FALSE );

	$query_tot_report = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'";
	list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

	$query_tests = "
	SELECT id_report, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND source_of = 'test'";
	$re_tests = sql_query($query_tests);
	while(list($id_r, $id_t) = sql_fetch_row($re_tests))
	{
		$included_test[$id_t] = $id_t;
		$included_test_report_id[$id_r] = $id_r;
	}

	if($tot_report == 0)
		$report_man->initializeCourseReport($org_tests);
	else {
		if(is_array($included_test)) $test_to_add = array_diff($org_tests, $included_test);
		else $test_to_add = $org_tests;
		if(is_array($included_test)) $test_to_del = array_diff($included_test, $org_tests);
		else $test_to_del = $org_tests;
		if(!empty($test_to_add) || !empty($test_to_del)) {
			$report_man->addTestToReport($test_to_add, 1);
			$report_man->delTestToReport($test_to_del);

			$included_test = $org_tests;
		}
	}
	$report_man->updateTestReport($org_tests);

	$img_mod = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />';

	$cont_h[] = $lang->def('_DETAILS');
	$csv .= '"'.$lang->def('_DETAILS').'"';

	$a_line_1 = array('');
	$a_line_2 = array('');
	$colums['max_score']		= array($lang->def('_MAX_SCORE'));
	$colums['required_score']	= array($lang->def('_REQUIRED_SCORE'));
	$colums['weight']	 		= array($lang->def('_WEIGHT'));
	$colums['show_to_user'] 	= array($lang->def('_SHOW_TO_USER'));
	$colums['use_for_final'] 	= array($lang->def('_USE_FOR_FINAL'));

	$query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'
	ORDER BY sequence ";
	$re_report = sql_query($query_report);
	$total_weight = 0;
	$i = 1;
	while($info_report = sql_fetch_assoc($re_report))
	{
		$id 									= $info_report['id_source'];
		$reports[$info_report['id_report']]		= $info_report;
		$reports_id[] 							= $info_report['id_report'];

		// XXX: set action colums

		switch($info_report['source_of']) {
			case "test" : {

				$title = strip_tags($tests_info[$info_report['id_source']]['title']);
			};break;
			case "scoitem" 	: {
				$title = strip_tags($info_report['title']);
			};break;
			case "activity" 	: {
				$title = strip_tags($info_report['title']);
			};break;
			case "final_vote" 	: {
				$title = strip_tags($lang->def('_FINAL_SCORE'));
			};break;
		}

		$top = $title;

		$cont_h[] = $top;
		$csv .= ';"'.$top.'"';
		$i++;

		//set info colums
		$colums['max_score'][] 		= $info_report['max_score'];
		$colums['required_score'][]	= $info_report['required_score'];
		$colums['weight'][] 			= $info_report['weight'];
		$colums['show_to_user'][] 		= ( $info_report['show_to_user'] == 'true' ? $lang->def('_YES') : $lang->def('_NO') );
		$colums['use_for_final'][] 	= ( $info_report['use_for_final'] == 'true' ? $lang->def('_YES') : $lang->def('_NO') );

		if($info_report['use_for_final'] == 'true') $total_weight += $info_report['weight'];
	}

	$csv .= "\n";
	$first = true;
	foreach($colums['max_score'] as $content)
		if($first)
		{
			$first = false;
			$csv .= '"'.$content.'"';
		}
		else
			$csv .= ';"'.$content.'"';

	$csv .= "\n";
	$first = true;
	foreach($colums['required_score'] as $content)
		if($first)
		{
			$first = false;
			$csv .= '"'.$content.'"';
		}
		else
			$csv .= ';"'.$content.'"';

	$csv .= "\n";
	$first = true;
	foreach($colums['weight'] as $content)
		if($first)
		{
			$first = false;
			$csv .= '"'.$content.'"';
		}
		else
			$csv .= ';"'.$content.'"';

	$csv .= "\n";
	$first = true;
	foreach($colums['show_to_user'] as $content)
		if($first)
		{
			$first = false;
			$csv .= '"'.$content.'"';
		}
		else
			$csv .= ';"'.$content.'"';

	$csv .= "\n";
	$first = true;
	foreach($colums['use_for_final'] as $content)
		if($first)
		{
			$first = false;
			$csv .= '"'.$content.'"';
		}
		else
			$csv .= ';"'.$content.'"';

	$csv .= "\n\n\n";
	$first = true;
	foreach($cont_h as $content)
		if($first)
		{
			$first = false;
			$csv .= '"'.$content.'"';
		}
		else
			$csv .= ';"'.$content.'"';

	$csv .= "\n";

	$tests_score 	=& $test_man->getTestsScores($included_test, $id_students);

	$test_details 	= array();
	if(is_array($included_test))
	{
		while(list($id_test, $users_result) = each($tests_score))
		{
			while(list($id_user, $single_test) = each($users_result))
			{
				if($single_test['score_status'] == 'valid')
				{
					if(!isset($test_details[$id_test]['max_score']))
						$test_details[$id_test]['max_score'] = $single_test['score'];
					elseif($single_test['score'] > $test_details[$id_test]['max_score'])
						$test_details[$id_test]['max_score'] = $single_test['score'];

					if(!isset($test_details[$id_test]['min_score']))
						$test_details[$id_test]['min_score'] = $single_test['score'];
					elseif($single_test['score'] < $test_details[$id_test]['min_score'])
						$test_details[$id_test]['min_score'] = $single_test['score'];

					if(!isset($test_details[$id_test]['num_result']))
						$test_details[$id_test]['num_result'] = 1;
					else
						$test_details[$id_test]['num_result']++;

					if(!isset($test_details[$id_test]['averange']))
						$test_details[$id_test]['averange'] = $single_test['score'];
					else
						$test_details[$id_test]['averange'] += $single_test['score'];
				}
			}
		}
		while(list($id_test, $single_detail) = each($test_details))
			if(isset($single_detail['num_result']))
				$test_details[$id_test]['averange'] /= $test_details[$id_test]['num_result'];
		reset($test_details);
	}
	$reports_score 	=& $report_man->getReportsScores(
		(isset($included_test_report_id) && is_array($included_test_report_id) ? array_diff($reports_id, $included_test_report_id) : $reports_id));

	$report_details = array();
	while(list($id_report, $users_result) = each($reports_score))
	{
		while(list($id_user, $single_report) = each($users_result))
		{
			if($single_report['score_status'] == 'valid')
			{
				if(!isset($report_details[$id_report]['max_score']))
					$report_details[$id_report]['max_score'] = $single_report['score'];
				elseif($single_report['score'] > $report_details[$id_report]['max_score'])
					$report_details[$id_report]['max_score'] = $single_report['score'];

				if(!isset($report_details[$id_report]['min_score']))
					$report_details[$id_report]['min_score'] = $single_report['score'];
				elseif($single_report['score'] < $report_details[$id_report]['min_score'])
					$report_details[$id_report]['min_score'] = $single_report['score'];

				if(!isset($report_details[$id_report]['num_result']))
					$report_details[$id_report]['num_result'] = 1;
				else
					$report_details[$id_report]['num_result']++;

				if(!isset($report_details[$id_report]['averange']))
					$report_details[$id_report]['averange'] = $single_report['score'];
				else
					$report_details[$id_report]['averange'] += $single_report['score'];
			}
		}
	}
	while(list($id_report, $single_detail) = each($report_details))
		if(isset($single_detail['num_result']))
			$report_details[$id_report]['averange'] /= $report_details[$id_report]['num_result'];
	reset($report_details);

	if(!empty($students_info))
	while(list($idst_user, $user_info) = each($students_info))
	{
		$user_name = ( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
						? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
						: $acl_man->relativeId($user_info[ACL_INFO_USERID]) );
		$csv .= '"'.$user_name.'"';

		foreach($reports as $id_report => $info_report)
		{
			switch($info_report['source_of'])
			{
				case "test" : {
					$id_test = $info_report['id_source'];
					if(isset($tests_score[$id_test][$idst_user]))
					{
						switch($tests_score[$id_test][$idst_user]['score_status'])
						{
							case "not_complete" : $csv .= ';"-"';break;
							case "not_checked" 	: {
								$csv .= ';"'.$lang->def('_NOT_CHECKED').'"';

								if(!isset($test_details[$id_test]['not_checked'])) $test_details[$id_test]['not_checked'] = 1;
								else $test_details[$id_test]['not_checked']++;
							};break;
							case "passed" 		: {
								$csv .= ';"'.$lang->def('_PASSED').'"';
								if(!isset($test_details[$id_test]['passed'])) $test_details[$id_test]['passed'] = 1;
								else $test_details[$id_test]['passed']++;
							};break;
							case "not_passed" 	: {
								$csv .= ';"'.$lang->def('_NOT_PASSED').'"';
								if(!isset($test_details[$id_test]['not_passed'])) $test_details[$id_test]['not_passed'] = 1;
								else $test_details[$id_test]['not_passed']++;
							};break;
							case "valid" 		: {
								$score = $tests_score[$id_test][$idst_user]['score'];

								if($score >= $info_report['required_score']) {
									if($score == $test_details[$id_test]['max_score']) $csv .= ';"'.$score." ".$tt.'"';
									else $csv .= ';"'." ".$tt.'"';

									if(!isset($test_details[$id_test]['passed'])) $test_details[$id_test]['passed'] = 1;
									else $test_details[$id_test]['passed']++;
								} else {
									if($score == $test_details[$id_test]['max_score']) $csv .= ';"'.$score." ".$tt.'"';
									else $csv .= ';"'.$score." ".$tt.'"';

									if(!isset($test_details[$id_test]['not_passed'])) $test_details[$id_test]['not_passed'] = 1;
									else $test_details[$id_test]['not_passed']++;
								}
								if(isset($test_details[$id_test]['varianza']) && isset($test_details[$id_test]['averange'])) {
									$test_details[$id_test]['varianza'] += pow(($tests_score[$id_test][$idst_user]['score'] - $test_details[$id_test]['averange']), 2);
								} else {
									$test_details[$id_test]['varianza'] = pow(($tests_score[$id_test][$idst_user]['score'] - $test_details[$id_test]['averange']), 2);
								}
							};break;
							default : {
								$csv .= ';"-"';
							}
						}
					}
					else
						$csv .= ';"-"';
				};break;
				case "scoitem" : {
						$query_report = "
						SELECT *
						FROM ".$GLOBALS['prefix_lms']."_scorm_tracking
						WHERE idscorm_item = '".$info_report['id_source']."' AND idUser = '".$idst_user."'
						";
						$report = sql_fetch_assoc(sql_query($query_report));
						if($report['score_raw']==NULL) $report['score_raw']="-";

						$id_track=(isset($report['idscorm_tracking']) ? $report['idscorm_tracking'] : 0);
						$query_report = "
						SELECT *
						FROM ".$GLOBALS['prefix_lms']."_scorm_tracking_history
						WHERE idscorm_tracking = '".$id_track."'
						";

						$query = sql_query($query_report);
						$num=sql_num_rows($query);
						$csv .= ';"'.$report['score_raw'].'"';

				}break;
				case "activity" :
				case "final_vote" : {
					$id_report = $info_report['id_report'];
					if(isset($reports_score[$id_report][$idst_user]))
					{
						switch($reports_score[$id_report][$idst_user]['score_status'])
						{
							case "not_complete" : $csv .= ';"-"';break;
							case "valid" 		: {
								if($reports_score[$id_report][$idst_user]['score'] >= $info_report['required_score']) {
									if($reports_score[$id_report][$idst_user]['score'] == $info_report['max_score']) {
										$csv .= ';"'.$reports_score[$id_report][$idst_user]['score'].'"';
									} else $csv .= ';"'.$reports_score[$id_report][$idst_user]['score'].'"';

									// Count passed
									if(!isset($report_details[$id_report]['passed'])) $report_details[$id_report]['passed'] = 1;
									else $report_details[$id_report]['passed']++;
								} else {
									$csv .= ';"'.$reports_score[$id_report][$idst_user]['score'].'"';

									// Count not passed
									if(!isset($report_details[$id_report]['not_passed'])) $report_details[$id_report]['not_passed'] = 1;
									else $report_details[$id_report]['not_passed']++;
								}
								if(isset($report_details[$id_report]['varianza']) && isset($report_details[$id_report]['averange'])) {
									$report_details[$id_report]['varianza'] += round(pow(($reports_score[$id_report][$idst_user]['score'] - $report_details[$id_report]['averange']), 2), 2);
								} else {
									$report_details[$id_report]['varianza'] = round(pow(($reports_score[$id_report][$idst_user]['score'] - $report_details[$id_report]['averange']), 2), 2);
								}
							};break;
						}
					}
					else
						$csv .= ';"-"';
				};break;
			}
		}
		$csv .= "\n";
	}

	$file_name = date('YmdHis').'_report_export.csv';

	require_once(_base_.'/lib/lib.download.php');
	sendStrAsFile($csv, $file_name);
}

function testQuestion()
{
	checkPerm('view');

	YuiLib::load(array('animation' => 'my_animation.js'));
	addJs($GLOBALS['where_lms_relative'].'/modules/coursereport/', 'ajax.coursereport.js');

	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');

	$lang =& DoceboLanguage::createInstance('coursereport', 'lms');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$out->add('<script type="text/javascript">'
// 			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/modules/coursereport/ajax.coursereport.php\'); '
// // 			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/ajax.server.php?id_quest=3&id_test=3\'); '
			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/ajax.server.php?plf=lms&mn=coursereport&\'); '
			.'</script>', 'page_head');

	$id_test = importVar('id_test', true, 0);

	$test_man = new GroupTestManagement();

	$lev = FALSE;
	if (isset($_GET['type_filter']) && $_GET['type_filter']!=null) {
		$lev = $_GET['type_filter'];
	}
	$students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
	$id_students = array_keys($students);

	$quests = array();
	$answers = array();
	$tracks = array();

	$test_info = $test_man->getTestInfo(array($id_test));

    $page_title = array(	'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
							$test_info[$id_test]['title']
    );

    $out->add(	getTitleArea($page_title, 'coursereport')
				.'<div class="std_block">'
    );

    $query_test =	"SELECT title"
					." FROM ".$GLOBALS['prefix_lms']."_test"
					." WHERE idTest = '".$id_test."'";

    list($titolo_test) = sql_fetch_row(sql_query($query_test));

    $query_quest =	"SELECT idQuest, type_quest, title_quest"
					." FROM ".$GLOBALS['prefix_lms']."_testquest"
					." WHERE idTest = '".$id_test."'"
					." ORDER BY sequence";

	$result_quest = sql_query($query_quest);

	while (list($id_quest, $type_quest, $title_quest) = sql_fetch_row($result_quest))
	{
		$quests[$id_quest]['idQuest'] = $id_quest;
		$quests[$id_quest]['type_quest'] = $type_quest;
		$quests[$id_quest]['title_quest'] = $title_quest;

//		$query_answer =	"SELECT idAnswer, is_correct, answer"
//						." FROM ".$GLOBALS['prefix_lms']."_testquestanswer"
//						." WHERE idQuest = '".$id_quest."'"
//						." ORDER BY sequence";

		$query_answer =	"SELECT tqa.idAnswer, tqa.is_correct, tqa.answer"
			." FROM ".$GLOBALS['prefix_lms']."_testquestanswer AS tqa"
			." LEFT JOIN"
			." ".$GLOBALS['prefix_lms']."_testtrack_answer tta ON tqa.idAnswer = tta.idAnswer"
			." LEFT JOIN"
			." ".$GLOBALS['prefix_lms']."_testtrack tt ON tt.idTrack = tta.idTrack"
			." WHERE tqa.idQuest = '".$id_quest."'";
			$query_answer .= " and tt.idUser in (".implode(",", $id_students).")";
			$query_answer .= " ORDER BY tqa.sequence";

		$result_answer = sql_query($query_answer);


		while (list($id_answer, $is_correct, $answer) = sql_fetch_row($result_answer))
		{
			$answers[$id_quest][$id_answer]['idAnswer'] = $id_answer;
			$answers[$id_quest][$id_answer]['is_correct'] = $is_correct;
			$answers[$id_quest][$id_answer]['answer'] = $answer;
		}
		if ($type_quest == 'choice_multiple' || $type_quest == 'choice' || $type_quest == 'inline_choice')
		{
			$answers[$id_quest][0]['idAnswer'] = 0;
			$answers[$id_quest][0]['is_correct'] = 0;
			$answers[$id_quest][0]['answer'] = $lang->def('_NO_ANSWER');
		}
	}



	$query_track =	"SELECT idTrack"
					." FROM ".$GLOBALS['prefix_lms']."_testtrack"
					." WHERE idTest = '".$id_test."'"
					." AND score_status = 'valid'"
					." AND idUser in (".implode(",", $id_students).")";

	$result_track = sql_query($query_track);

	while(list($id_track) = sql_fetch_row($result_track))
	{
		$query_track_answer =	"SELECT idQuest, idAnswer, more_info"
								." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer"
								." WHERE idTrack = '".$id_track."'";
// COMMENTATO MA NON E' CHIARO COME MAI C'E'????
								//." AND user_answer = 1";
//print_r($query_track_answer.'<br />');
		$result_track_answer = sql_query($query_track_answer);

//echo $query_track_answer."<br>";
		while(list($id_quest, $id_answer, $more_info) = sql_fetch_row($result_track_answer)) {
			$tracks[$id_track][$id_quest][$id_answer]['more_info'] = $more_info;
//echo " -> ".$id_quest." - ".$id_answer." - ".$more_info."<br>";
}
	}

	$query_total_play =	"SELECT COUNT(*)"
						." FROM ".$GLOBALS['prefix_lms']."_testtrack"
						." WHERE idTest = '".$id_test."'"
						." AND score_status = 'valid'"
						." AND idUser in (".implode(",", $id_students).")";

	list($total_play) = sql_fetch_row(sql_query($query_total_play));

        /*if ($total_play == 0) {
                $query_total_play =     "SELECT COUNT(*)"
                                                ." FROM ".$GLOBALS['prefix_lms']."_testtrack"
                                                ." WHERE idTest = '".$id_test."' AND score_status = 'not_checked'";
                list($total_play2) = mysql_fetch_row(mysql_query($query_total_play));
$total_play += $total_play2;

        }*/
//print_r($tracks);
	foreach($quests as $quest)
	{
		switch ($quest['type_quest'])
		{
			case "inline_choice":
			case "hot_text":
			case "choice_multiple":
			case "choice":
				$cont_h = array
					(
						$lang->def('_ANSWER'),
						$lang->def('_PERCENTAGE')
					);
				$type_h = array(
					'', 'image nowrap');

				$tb = new Table(0, str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST')));
				$tb->setColsStyle($type_h);
				$tb->addHead($cont_h);

				foreach($answers[$quest['idQuest']] as $answer)
				{
					$cont = array();

					if($answer['is_correct'])
						$txt = '<img src="'.getPathImage('lms').'standard/publish.png" alt="'.$lang->def('_ANSWER_CORRECT').'" title="'.$lang->def('_ANSWER_CORRECT').'" align="left" /> ';
					else
						$txt = '';

					$cont[] = '<p>'.$txt.' '.$answer['answer'].'</p>';

					$answer_given = 0;
					reset($tracks);
					$i=0;
					foreach($tracks as $track)
					{
					$i++;
						if(isset($track[$quest['idQuest']][$answer['idAnswer']])){
							$answer_given++;
						} elseif(!isset($track[$quest['idQuest']]) && $answer['idAnswer'] == 0){
							$answer_given++;
						}
					}
					if ($answer['idAnswer'] == 0 && $i < $total_play) {
		//			if ($i < $total_play) {
						$answer_given = $answer_given + ($total_play - $i);
					}
					if($total_play > 0)
						$percentage = ($answer_given / $total_play) * 100;
					else
						$percentage = 0;

					$percentage = number_format($percentage, 2);

					$cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

					$tb->addBody($cont);
				}

				$out->add($tb->getTable().'<br/>');
			break;

			case "upload":
			case "extended_text":
				$out->add('<div>');
				$out->add('<p><a href="#" onclick="getQuestDetail('.$quest['idQuest'].', '.$id_test.', \''.$quest['type_quest'].'\'); return false;" id="more_quest_'.$quest['idQuest'].'"><img src="'.getPathImage('fw').'standard/more.gif" alt="'.$lang->def('_MORE_INFO').'" />'.str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST')).'</a></p>');
				$out->add('<p><a href="#" onclick="closeQuestDetail('.$quest['idQuest'].'); return false;" id="less_quest_'.$quest['idQuest'].'" style="display:none"><img src="'.getPathImage('fw').'standard/less.gif" alt="'.$lang->def('_CLOSE').'" />'.str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST')).'</a></p>');
				$out->add('</div>');
				$out->add('<div id="quest_'.$quest['idQuest'].'">');
				$out->add('</div>');
			break;

			case "text_entry":
				$cont_h = array
					(
						$lang->def('_PERCENTAGE_CORRECT')
					);
				$type_h = array('align-center');

				$tb = new Table(0, str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_CORRECT_TXT')));
				$tb->setColsStyle($type_h);
				$tb->addHead($cont_h);

				foreach($answers[$quest['idQuest']] as $answer)
				{
					$cont = array();

					$answer_correct = 0;

					foreach($tracks as $track)
					{
						if($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['answer'])
							$answer_correct++;
					}

					$percentage = ($answer_correct / $total_play) * 100;

					$percentage = number_format($percentage, 2);

					$cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

					$tb->addBody($cont);
				}

				$out->add($tb->getTable().'<br/>');
			break;

			case "associate":
				$cont_h = array
					(
						$lang->def('_ANSWER'),
						$lang->def('_PERCENTAGE_CORRECT')
					);
				$type_h = array('', 'align-center');

				$tb = new Table(0, str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_CORRECT_ASS')));
				$tb->setColsStyle($type_h);
				$tb->addHead($cont_h);

				foreach($answers[$quest['idQuest']] as $answer)
				{
					$cont = array();

					$cont[] = $answer['answer'];

					$answer_correct = 0;

					foreach($tracks as $track)
					{
						if($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['is_correct'])
							$answer_correct++;
					}

					$percentage = ($answer_correct / $total_play) * 100;
echo "risp corrette: ".$answer_correct." totale: ".$total_play;

					$percentage = number_format($percentage, 2);

					$cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

					$tb->addBody($cont);
				}

				$out->add($tb->getTable().'<br/>');
			break;
		}

		reset($answers);
		reset($tracks);
	}

	$out->add('</div>');
}


function showchart() {
	require_once(_lms_.'/modules/test/charts.test.php');

	$idTest = Get::req('id_test', DOTY_INT, -1);
	$idUser = Get::req('id_user', DOTY_INT, -1);
	$chartType = Get::req('chart_type', DOTY_STRING, 'column');

	$lang =& DoceboLanguage::createInstance('coursereport', 'lms');
	$acl_man 	= Docebo::user()->getAclManager();
	$user_info = $acl_man->getUser($idUser, false);
	list($title) = sql_fetch_row( sql_query("SELECT title FROM ".$GLOBALS['prefix_lms']."_test WHERE idTest=".(int)$idTest) );
	$backUrl = 'index.php?modname=coursereport&op=testvote&id_test='.(int)$idTest;
	$backUi = getBackUi($backUrl, $lang->def('_BACK'));

	$page_title = array(
		'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
		$backUrl => strip_tags($title),
		$acl_man->relativeId($user_info[ACL_INFO_USERID])
	);
	cout(getTitleArea($page_title, 'coursereport', $lang->def('_TH_ALT')));
	cout('<div class="stdblock">');
	cout($backUi);

	cout('<div><h2>'.$lang->def('_USER_DETAILS').'</h2>');
	cout('<div class="form_line_l"><p><label class="floating">'.$lang->def('_USERNAME').':&nbsp;</label></p>'.$acl_man->relativeId($user_info[ACL_INFO_USERID]).'</div>');
	cout('<div class="form_line_l"><p><label class="floating">'.$lang->def('_LASTNAME').':&nbsp;</label></p>'.$user_info[ACL_INFO_LASTNAME].'</div>');
	cout('<div class="form_line_l"><p><label class="floating">'.$lang->def('_FIRSTNAME').':&nbsp;</label></p>'.$user_info[ACL_INFO_FIRSTNAME].'</div>');
	cout('<div class="no_float"></div>');

	$charts = new Test_Charts($idTest, $idUser);
	$charts->render($chartType, true);

	cout($backUi);
	cout('</div>');
}


//------------------------------------------------------------------------------



function coursereportDispatch($op) {

	if(isset($_POST['undo'])) $op = 'coursereport';
	if(isset($_POST['undo_testreview'])) $op = 'testvote';
	if(isset($_POST['undo_reset'])) $op = 'testvote';
	if(isset($_POST['view_answer'])) $op = 'testreview';

	switch($op) {

		case "export":
			export();
		break;
		case "coursereport" : {
			coursereport();
		};break;
		case "testvote" : {
			testvote();
		};break;
		case "testreview" : {
			testreview();
		};break;
		case "testQuestion" :
			testQuestion();
		break;
		case "finalvote" : {
			finalvote();
		};break;

		case "roundtest" : {
			roundtest();
		};break;

		case "roundreport" : {
			roundreport();
		};break;
		case "redofinal" : {
			redofinal();
		};break;

		case "addactivity" :{
			modactivity();
		};break;
		case "modactivityscore" : {
			modactivityscore();
		};break;

		case "delactivity" : {
			delactivity();
		};break;

		case "moveright" : {
			movereport('right');
		};break;
		case "moveleft" : {
			movereport('left');
		};break;
		case "testreport" : {
            testreport($_GET['idTrack'],$_GET['idTest'],$_GET['testName'],$_GET['studentName']);
        };break;
        case "scormreport" : {
        	scormreport($_GET['idTest']);
        };break;
		case "showchart": {
			showchart();
		};break;
		case "addscorm" :{
			modscorm();
		};break;

	}

}

?>