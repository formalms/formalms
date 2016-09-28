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


class CoursereportLmsController extends LmsController {



	public function init() {
		$this->model = new CoursestatsLms();
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view' => true,//checkPerm('view', true, 'coursestats')
			'mod' => true
		);
	}



    public function showTask()
    {
        checkPerm('view');
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');
        $included_test = array();
        $view_perm = checkPerm('view', true);
        $view_all_perm = checkPerm('view_all', true);
        $mod_perm = checkPerm('mod', true);

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();

        // XXX: Find test from organization
        $org_tests =& $report_man->getTest();
        $tests_info = $test_man->getTestInfo($org_tests);

        // XXX: Find students
        /*
         * $id_students	=& $report_man->getStudentId();
         * $students_info 	=& $acl_man->getUsers($id_students);
         */

        $lang2 =& DoceboLanguage::createInstance('levels', 'lms');

        if (isset($_POST['type_filter']))
            $type_filter = $_POST['type_filter'];
        else
            $type_filter = false;

        if ($type_filter == "false")
            $type_filter = false;

        $lev = $type_filter;

        $students = getSubscribedInfo((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);

        //apply sub admin filters, if needed
        if (!$view_all_perm) {
            //filter users
            require_once(_base_ . '/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
            foreach ($students as $idst => $user_course_info) {
                if (!in_array($idst, $ctrl_users)) {
                    // Elimino gli studenti non amministrati
                    unset ($students[$idst]);
                }

            }
        }

        $id_students = array_keys($students);
        $students_info =& $acl_man->getUsers($id_students);
        $i = 0;

        /*$students_info=array();
        foreach( $students as $idst => $user_course_info )
            $students_info[$idst] =& $acl_man->getUser( $idst, FALSE );
    */

        $courseReportModel = new CoursereportLms();

        // XXX: Info for updates
        $tot_report = $courseReportModel->getTotalCourseReport($_SESSION['idCourse']);

        $test_and_reports = $courseReportModel->getTestsAndReports($_SESSION['idCourse']);

        $included_test = $test_and_reports['source'];
        $included_test_report_id = $test_and_reports['report'];

        // XXX: Update if needed
        if ($tot_report == 0)
            $report_man->initializeCourseReport($org_tests);
        else {
            if (is_array($included_test)) $test_to_add = array_diff($org_tests, $included_test);
            else $test_to_add = $org_tests;
            if (is_array($included_test)) $test_to_del = array_diff($included_test, $org_tests);
            else $test_to_del = $org_tests;
            if (!empty($test_to_add) || !empty($test_to_del)) {
                $report_man->addTestToReport($test_to_add, 1);
                $report_man->delTestToReport($test_to_del);

                $included_test = $org_tests;
            }
        }
        $report_man->updateTestReport($org_tests);

        // XXX: Retrive all colums (test and so), and set it
        $img_mod = '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />';

        $type_h = array('line_users');
        $cont_h = array($lang->def('_DETAILS'));

        $a_line_1 = array('');
        $a_line_2 = array('');
        $colums['max_score'] = array($lang->def('_MAX_SCORE'));
        $colums['required_score'] = array($lang->def('_REQUIRED_SCORE'));
        $colums['weight'] = array($lang->def('_WEIGHT'));
        $colums['show_to_user'] = array($lang->def('_SHOW_TO_USER'));
        $colums['use_for_final'] = array($lang->def('_USE_FOR_FINAL'));

        $query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	WHERE id_course = '" . $_SESSION['idCourse'] . "'
	ORDER BY sequence ";
        $re_report = sql_query($query_report);
        $total_weight = 0;
        $i = 1;
        while ($info_report = sql_fetch_assoc($re_report)) {
            $id = $info_report['id_source'];
            $reports[$info_report['id_report']] = $info_report;
            $reports_id[] = $info_report['id_report'];

            // XXX: set action colums

            $type_h[] = 'min-cell';

            switch ($info_report['source_of']) {
                case "test" : {

                    $title = strip_tags($tests_info[$info_report['id_source']]['title']);

                    if (!$mod_perm) {
                        if (!$view_perm) {
                            $my_action = '';
                            $a_line_2[] = '';
                        } else {
                            $my_action = '<a class="ico-sprite subs_chart" href="index.php?modname=coursereport&amp;op=testQuestion&amp;id_test=' . $id . '"><span><span>' . $lang->def('_TQ_LINK') . '</span></a>';
                            $a_line_2[] = '';
                        }
                    } else {
                        $my_action = '<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=testvote&amp;type_filter=' . $type_filter . '&amp;id_test=' . $id . '"><span><span>' . $lang->def('_EDIT_SCORE') . '</span></a>'
                            . ' <a class="ico-sprite subs_chart" href="index.php?modname=coursereport&amp;op=testQuestion&amp;type_filter=' . $type_filter . '&amp;id_test=' . $id . '"><span><span>' . $lang->def('_TQ_LINK') . '</span></a>';
                        $a_line_2[] = '<a href="index.php?modname=coursereport&amp;op=roundtest&amp;id_test=' . $id . '" '
                            . 'title="' . $lang->def('_ROUND_TEST_VOTE') . '">' . $lang->def('_ROUND_VOTE') . '</a>';
                    }
                };
                    break;
                case "scoitem"    : {

                    $title = strip_tags($info_report['title']);

                    if (!$mod_perm) {
                        $my_action = '';
                        $a_line_2[] = '';
                    } else {
                        $my_action = $my_action = '<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=modactivityscore&amp;type_filter=' . $type_filter . '&amp;id_report=' . $info_report['id_report'] . '&amp;source_of=' . $info_report['source_of'] . '&amp;id_source=' . $info_report['id_source'] . '"><span><span>' . $lang->def('_CHANGE_ACTIVITY_VOTE') . '</span></a>'
                            . ' <a class="ico-sprite subs_del" href="index.php?modname=coursereport&amp;op=delactivity&amp;id_report=' . $info_report['id_report'] . '"><span><span>' . $lang->def('_DELETE_ACTIVITY_VOTE') . '</span></a>';

                        $a_line_2[] = '<a href="index.php?modname=coursereport&amp;op=roundreport&amp;id_report=' . $info_report['id_report'] . '" '
                            . 'title="' . $lang->def('_ROUND_ACTIVITY_VOTE_TITLE') . '">' . $lang->def('_ROUND_VOTE') . '</a>';
                    }
                };
                    break;
                case "activity"    : {
                    $title = strip_tags($info_report['title']);

                    if (!$mod_perm) {
                        $my_action = '';
                        $a_line_2[] = '';
                    } else {
                        $my_action = '<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=modactivityscore&amp;type_filter=' . $type_filter . '&amp;id_report=' . $info_report['id_report'] . '&amp;source_of=' . $info_report['source_of'] . '&amp;id_source=' . $info_report['id_source'] . '"><span><span>' . $lang->def('_CHANGE_ACTIVITY_VOTE') . '</span></a>'
                            . ' <a class="ico-sprite subs_del" href="index.php?modname=coursereport&amp;op=delactivity&amp;id_report=' . $info_report['id_report'] . '"><span><span>' . $lang->def('_DELETE_ACTIVITY_VOTE') . '</span></a>';

                        $a_line_2[] = '<a href="index.php?modname=coursereport&amp;op=roundreport&amp;id_report=' . $info_report['id_report'] . '" '
                            . 'title="' . $lang->def('_ROUND_VOTE') . '">' . $lang->def('_ROUND_VOTE') . '</a>';
                    }
                };
                    break;
                case "final_vote"    : {

                    $title = strip_tags($lang->def('_FINAL_SCORE'));
                    $info_report['weight'] = $total_weight;

                    if (!$mod_perm) {
                        $my_action = '';
                        $a_line_2[] = '';
                    } else {
                        $my_action = '<a class="ico-sprite subs_mod" href="index.php?modname=coursereport&amp;op=finalvote&amp;type_filter=' . $type_filter . '&amp;id_report=' . $info_report['id_report'] . '"><span><span>' . $lang->def('_EDIT_SCORE') . '</span></a>';

                        $a_line_2[] = ''
                            . '<a href="index.php?modname=coursereport&amp;op=redofinal&amp;id_report=' . $info_report['id_report'] . '" '
                            . 'title="' . $lang->def('_REDO_FINAL_VOTE_TITLE') . '">' . $lang->def('_REDO_FINAL_VOTE') . '</a></li>'
                            . '<br/><a href="index.php?modname=coursereport&amp;op=roundreport&amp;id_report=' . $info_report['id_report'] . '" '
                            . 'title="' . $lang->def('_ROUND_FINAL_VOTE_TITLE') . '">' . $lang->def('_ROUND_VOTE') . '</a></li>'
                            . '';
                    }
                };
                    break;
            }

            $top = $title . '<br/>';
            if ($mod_perm)
                if ($i > 1 && $info_report['source_of'] != 'final_vote')
                    $top .= '<a class="ico-sprite subs_left" href="index.php?modname=coursereport&amp;op=moveleft&amp;id_report=' . $info_report['id_report'] . '"><span><span>' . $lang->def('_MOVE_LEFT') . '</span></a>';
            $top .= $my_action;
            if ($mod_perm)
                if (($i < ($tot_report - 1)) && ($tot_report > 2))
                    $top .= '<a class="ico-sprite subs_right" href="index.php?modname=coursereport&amp;op=moveright&amp;id_report=' . $info_report['id_report'] . '"><span><span>' . $lang->def('_MOVE_RIGHT') . '</span></a>';
            $cont_h[] = $top;
            $i++;

            //set info colums
            $colums['max_score'][] = $info_report['max_score'];
            $colums['required_score'][] = $info_report['required_score'];
            $colums['weight'][] = $info_report['weight'];
            $colums['show_to_user'][] = ($info_report['show_to_user'] == 'true' ? $lang->def('_YES') : $lang->def('_NO'));
            $colums['use_for_final'][] = ($info_report['use_for_final'] == 'true' ? $lang->def('_YES') : $lang->def('_NO'));

            if ($info_report['use_for_final'] == 'true') $total_weight += $info_report['weight'];
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
        require_once(_adm_ . '/lib/lib.field.php');

        $fman = new FieldList();

        $fields = $fman->getFlatAllFields(array('framework', 'lms'));

        $f_list = array(
            'userid' => Lang::t('_USERNAME', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'lastenter' => Lang::t('_DATE_LAST_ACCESS', 'profile'),
            'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
            'language' => Lang::t('_LANGUAGE', 'standard'),
            'level' => Lang::t('_LEVEL', 'standard')
        );
        $f_list = $f_list + $fields;

        $js_arr = array();
        foreach ($f_list as $key => $value)
            $js_arr[] = $key . ': ' . json_encode($value);

        $f_list_js = '{' . implode(',', $js_arr) . '}';

        $fieldlist = $f_list;
        $dyn_labels = array();
        $dyn_filter = array();
        $num_var_fields = 1;
        $label = '<form name="formx" method="get">';

        for ($i = 0; $i < $num_var_fields; $i++) {
            $label .= '<select onchange="document.formx.submit()" id="_dyn_field_selector_0" name="_dyn_field_selector_0">';
            foreach ($fieldlist as $key => $value) {
                if ($i == 0)
                    $first = $key;
                $label .= '<option value="' . $key . '"'
                    . ($_GET['_dyn_field_selector_0'] == $key ? ' selected="selected"' : '')
                    . '>' . $value . '</option>';
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
        $tests_score =& $test_man->getTestsScores($included_test, $id_students);
        // XXX: Calculate statistic
        $test_details = array();
        if (is_array($included_test)) {
            while (list($id_test, $users_result) = each($tests_score)) {
                while (list($id_user, $single_test) = each($users_result)) {
                    if ($single_test['score_status'] == 'valid') {
                        // max
                        if (!isset($test_details[$id_test]['max_score']))
                            $test_details[$id_test]['max_score'] = $single_test['score'];
                        elseif ($single_test['score'] > $test_details[$id_test]['max_score'])
                            $test_details[$id_test]['max_score'] = $single_test['score'];

                        // min
                        if (!isset($test_details[$id_test]['min_score']))
                            $test_details[$id_test]['min_score'] = $single_test['score'];
                        elseif ($single_test['score'] < $test_details[$id_test]['min_score'])
                            $test_details[$id_test]['min_score'] = $single_test['score'];

                        //number of valid score
                        if (!isset($test_details[$id_test]['num_result']))
                            $test_details[$id_test]['num_result'] = 1;
                        else
                            $test_details[$id_test]['num_result']++;

                        // averange
                        if (!isset($test_details[$id_test]['averange']))
                            $test_details[$id_test]['averange'] = $single_test['score'];
                        else
                            $test_details[$id_test]['averange'] += $single_test['score'];
                    }
                }
            }
            while (list($id_test, $single_detail) = each($test_details))
                if (isset($single_detail['num_result']))
                    $test_details[$id_test]['averange'] /= $test_details[$id_test]['num_result'];
            reset($test_details);
        }
        // XXX: Retrive other source scores
        $reports_score =& $report_man->getReportsScores(
            (isset($included_test_report_id) && is_array($included_test_report_id) ? array_diff($reports_id, $included_test_report_id) : $reports_id), $id_students);

        // XXX: Calculate statistic
        $report_details = array();
        while (list($id_report, $users_result) = each($reports_score)) {
            while (list($id_user, $single_report) = each($users_result)) {
                if ($single_report['score_status'] == 'valid') {
                    // max
                    if (!isset($report_details[$id_report]['max_score']))
                        $report_details[$id_report]['max_score'] = $single_report['score'];
                    elseif ($single_report['score'] > $report_details[$id_report]['max_score'])
                        $report_details[$id_report]['max_score'] = $single_report['score'];

                    // min
                    if (!isset($report_details[$id_report]['min_score']))
                        $report_details[$id_report]['min_score'] = $single_report['score'];
                    elseif ($single_report['score'] < $report_details[$id_report]['min_score'])
                        $report_details[$id_report]['min_score'] = $single_report['score'];

                    //number of valid score
                    if (!isset($report_details[$id_report]['num_result']))
                        $report_details[$id_report]['num_result'] = 1;
                    else
                        $report_details[$id_report]['num_result']++;

                    // averange
                    if (!isset($report_details[$id_report]['averange']))
                        $report_details[$id_report]['averange'] = $single_report['score'];
                    else
                        $report_details[$id_report]['averange'] += $single_report['score'];
                }
            }
        }
        while (list($id_report, $single_detail) = each($report_details))
            if (isset($single_detail['num_result']))
                $report_details[$id_report]['averange'] /= $report_details[$id_report]['num_result'];
        reset($report_details);

        // XXX: Display user scores
        if (!empty($students_info))
            while (list($idst_user, $user_info) = each($students_info)) {
                $user_name = ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
                    ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
                    : $acl_man->relativeId($user_info[ACL_INFO_USERID]));
                $cont = array($user_name);

                $fman = new FieldList();
                $field_entries = $fman->getUsersFieldEntryData($user_info[0], $field_selected, true);

                $user = array(
                    'id' => $user_info[ACL_INFO_IDST],
                    'userid' => $user_info[ACL_INFO_USERID],
                    'firstname' => $user_info[ACL_INFO_FIRSTNAME],
                    'lastname' => $user_info[ACL_INFO_LASTNAME],
                    'email' => $user_info[ACL_INFO_EMAIL],
                    'register_date' => $user_info[ACL_INFO_REGISTER_DATE],
                    'lastenter' => $user_info[ACL_INFO_LASTENTER]
                );

                if (is_numeric($field_selected)) {
                    $cont[] = $field_entries[$user_info[0]][$field_selected];
                } else {
                    if ($field_selected == "userid") {
                        $pos = strrpos($user[$field_selected], "/");
                        if ($pos == 0) {
                            $cont[] = substr($user[$field_selected], 1);
                        }
                    } else {
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
                            require_once($GLOBALS['where_lms'] . '/class.module/learning.test.php');
                            $testObj = Learning_Test::load($id_test);
                            if (isset($tests_score[$id_test][$idst_user])) {
                                switch ($tests_score[$id_test][$idst_user]['score_status']) {
                                    case "not_complete" :
                                        $cont[] = '-';
                                        break;
                                    case "not_checked" : {
                                        $cont[] = '<span class="cr_not_check">' . $lang->def('_NOT_CHECKED') . '</span>';

                                        // Count not checked
                                        if (!isset($test_details[$id_test]['not_checked']))
                                            $test_details[$id_test]['not_checked'] = 1;
                                        else
                                            $test_details[$id_test]['not_checked']++;
                                    };
                                        break;
                                    case "passed" : {
                                        //$cont[] = '<span class="cr_passed">'.$lang->def('_PASSED').'</span>';
                                        $cont[] = '<img src="' . getPathImage('fw') . 'emoticons/thumbs_up.gif" alt="' . $lang->def('_PASSED') . '" />';
                                        // Count passed
                                        if (!isset($test_details[$id_test]['passed']))
                                            $test_details[$id_test]['passed'] = 1;
                                        else
                                            $test_details[$id_test]['passed']++;
                                    };
                                        break;
                                    case "not_passed" : {
                                        //$cont[] = '<span class="cr_not_passed">'.$lang->def('_NOT_PASSED').'</span>';
                                        $cont[] = '<img src="' . getPathImage('fw') . 'emoticons/thumbs_down.gif" alt="' . $lang->def('_NOT_PASSED') . '" />';
                                        // Count not passed
                                        if (!isset($test_details[$id_test]['not_passed']))
                                            $test_details[$id_test]['not_passed'] = 1;
                                        else
                                            $test_details[$id_test]['not_passed']++;
                                    };
                                        break;
                                    case "doing" :
                                    case "valid" : {
                                        $score = $tests_score[$id_test][$idst_user]['score'];
                                        if ($tests_score[$id_test][$idst_user]['times'] > 0) {
                                            $tests_score[$id_test][$idst_user]['times'] = "<a href=\"index.php?modname=coursereport&op=testreport&idTest=" . $tests_score[$id_test][$idst_user]['idTest'] . "&idTrack=" . $tests_score[$id_test][$idst_user]['idTrack'] . "&testName=" . $tests_info[$info_report['id_source']]['title'] . "&studentName=" . $acl_man->relativeId($user_info[ACL_INFO_USERID]) . "\">" . $tests_score[$id_test][$idst_user]['times'] . "</a>";
                                        }
                                        $tt = "(" . $tests_score[$id_test][$idst_user]['times'] . ")";
                                        if ($testObj->obj_type == 'test360') {
                                            $cont[] = '<a href="index.php?r=test360/report&idTest=' . $tests_score[$id_test][$idst_user]['idTest'] . '&showAuto=1&showEtero=1" class="ico-wt-sprite subs_confirm">&nbsp;&nbsp;</a> ' . $tt;
                                        } else if ($score >= $info_report['required_score']) {
                                            if ($score == $test_details[$id_test]['max_score'])
                                                $cont[] = '<span class="cr_max_score">' . $score . " " . $tt . '</span>';
                                            else
                                                $cont[] = $score . " " . $tt;

                                            // Count passed
                                            if (!isset($test_details[$id_test]['passed']))
                                                $test_details[$id_test]['passed'] = 1;
                                            else
                                                $test_details[$id_test]['passed']++;
                                        } else {
                                            if ($score == $test_details[$id_test]['max_score'])
                                                $cont[] = '<span class="cr_max_score cr_not_passed">' . $score . " " . $tt . '</span>';

                                            else$cont[] = '<span class="cr_not_passed">' . $score . " " . $tt . '</span>';

                                            // Count not passed
                                            if (!isset($test_details[$id_test]['not_passed']))
                                                $test_details[$id_test]['not_passed'] = 1;
                                            else
                                                $test_details[$id_test]['not_passed']++;
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
                            if ($info_report['use_for_final'] == 'true') {
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
                            if ($info_report['use_for_final'] == 'true') {
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
                        }
                            break;
                        case "activity" : {
                            $id_report = $info_report['id_report'];
                            $score = 0;
                            if (isset($reports_score[$id_report][$idst_user])) {
                                switch ($reports_score[$id_report][$idst_user]['score_status']) {
                                    case "not_complete" :
                                        $cont[] = '-';
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
                                                $report_details[$id_report]['passed']++;
                                        } else {
                                            $cont[] = '<span class="cr_not_passed">' . $score . '</span>';
                                            // Count not passed
                                            if (!isset($report_details[$id_report]['not_passed']))
                                                $report_details[$id_report]['not_passed'] = 1;
                                            else
                                                $report_details[$id_report]['not_passed']++;
                                        }
                                        if (isset($report_details[$id_report]['varianza']) && isset($report_details[$id_report]['averange'])) {
                                            $report_details[$id_report]['varianza'] += round(pow(($reports_score[$id_report][$idst_user]['score'] - $report_details[$id_report]['averange']), 2), 2);
                                        } else {
                                            $report_details[$id_report]['varianza'] = round(pow(($reports_score[$id_report][$idst_user]['score'] - $report_details[$id_report]['averange']), 2), 2);
                                        }
                                    };
                                        break;
                                }
                            } else {
                                $cont[] = '<span class="cr_not_passed">-</span>';
                            }
                            if ($info_report['use_for_final'] == 'true') {
                                array_push($results_activity, $score * $info_report['weight']);
                            }
                        }
                            break;
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
                            $media = (($first_value + $second_value + $third_value) / $total_weight);
                            $media = sprintf("%01.2f", round($media, 2));
                            $cont[] = $media > 0 ? $media : '<span class="cr_not_passed">-</span>';
                        };
                            break;
                    }
                }
                $tb_score->addBody($cont);
            }
        // XXX: Display statistics
        $stats['passed'] = array($lang->def('_PASSED'));//, ''
        $stats['not_passed'] = array($lang->def('_NOT_PASSED'));//, ''
        $stats['not_checked'] = array($lang->def('_NOT_CHECKED'));//, ''
        $stats['averange'] = array($lang->def('_AVERANGE'));//, ''
        $stats['varianza'] = array($lang->def('_STANDARD_DEVIATION'));//, ''
        $stats['max_score'] = array($lang->def('_MAX_SCORE'));//, ''
        $stats['min_score'] = array($lang->def('_MIN_SCORE'));//, ''
        foreach ($reports as $id_report => $info_report) {
            switch ($info_report['source_of']) {
                case "test" : {
                    $id_test = $info_report['id_source'];

                    if (isset($test_details[$id_test]['passed']) || isset($test_details[$id_test]['not_passed'])) {
                        if (!isset($test_details[$id_test]['passed']))
                            $test_details[$id_test]['passed'] = 0;
                        if (!isset($test_details[$id_test]['not_passed']))
                            $test_details[$id_test]['not_passed'] = 0;

                        $test_details[$id_test]['varianza'] /= ($test_details[$id_test]['passed'] + $test_details[$id_test]['not_passed']);
                        $test_details[$id_test]['varianza'] = sqrt($test_details[$id_test]['varianza']);
                    }
                    $stats['passed'][] = (isset($test_details[$id_test]['passed']) ? round($test_details[$id_test]['passed'], 2) : '-');
                    $stats['not_passed'][] = (isset($test_details[$id_test]['not_passed']) ? round($test_details[$id_test]['not_passed'], 2) : '-');
                    $stats['not_checked'][] = (isset($test_details[$id_test]['not_checked']) ? round($test_details[$id_test]['not_checked'], 2) : '-');
                    $stats['averange'][] = (isset($test_details[$id_test]['averange']) ? round($test_details[$id_test]['averange'], 2) : '-');
                    $stats['varianza'][] = (isset($test_details[$id_test]['varianza']) ? round($test_details[$id_test]['varianza'], 2) : '-');
                    $stats['max_score'][] = (isset($test_details[$id_test]['max_score']) ? round($test_details[$id_test]['max_score'], 2) : '-');
                    $stats['min_score'][] = (isset($test_details[$id_test]['min_score']) ? round($test_details[$id_test]['min_score'], 2) : '-');
                };
                    break;
                case "scoitem" : {
                    $query_report = "
						SELECT *
						FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking
						WHERE idscorm_item = '" . $info_report['id_source'] . "'";

                    $passed = 0;
                    $total = 0;
                    $media = 0;
                    $varianza = 0;
                    $votomassimo = 0;
                    $votominimo = 9999;
                    $result = sql_query($query_report);
                    while ($report = sql_fetch_assoc($result)) {
                        if ($report['score_raw'] != NULL) {
                            if ($report['score_raw'] > $votomassimo)
                                $votomassimo = $report['score_raw'];
                            if ($report['score_raw'] < $votominimo)
                                $votominimo = $report['score_raw'];
                            $media = $media + $report['score_raw'];
                            $total = $total + 1;
                            if ($report['lesson_status'] == 'passed') {
                                $passed++;
                            }
                        }
                    }
                    $media = ($total == 0 ? '0' : $media / $total);
                    $result = sql_query($query_report);
                    $var = 0;
                    while ($report = sql_fetch_assoc($result))
                        if ($report['score_raw'] != NULL)
                            $var = $var + pow($media - $report['score_raw'], 2);
                    $varianza = ($total == 0 ? '0' : floor($var / $total));
                    if ($votominimo == 9999)
                        $votominimo = "";

                    $stats['passed'][] = $passed;
                    $stats['not_passed'][] = $total - $passed;
                    $stats['not_checked'][] = "-";
                    $stats['averange'][] = $media;
                    $stats['varianza'][] = $varianza;
                    $stats['max_score'][] = $votomassimo;
                    $stats['min_score'][] = $votominimo;
                };
                    break;
                case "activity" :
                case "final_vote" : {
                    if (isset($report_details[$id_report]['passed']) || isset($report_details[$id_report]['not_passed'])) {
                        if (!isset($report_details[$id_report]['passed']))
                            $report_details[$id_report]['passed'] = 0;
                        if (!isset($report_details[$id_report]['not_passed']))
                            $report_details[$id_report]['not_passed'] = 0;

                        $report_details[$id_report]['varianza'] /= ($report_details[$id_report]['passed'] + $report_details[$id_report]['not_passed']);
                        $report_details[$id_report]['varianza'] = sqrt($report_details[$id_report]['varianza']);
                    }
                    $stats['passed'][] = (isset($report_details[$id_report]['passed']) ? round($report_details[$id_report]['passed'], 2) : '-');
                    $stats['not_passed'][] = (isset($report_details[$id_report]['not_passed']) ? round($report_details[$id_report]['not_passed'], 2) : '-');
                    $stats['not_checked'][] = (isset($report_details[$id_report]['not_checked']) ? round($report_details[$id_report]['not_checked'], 2) : '-');
                    $stats['averange'][] = (isset($report_details[$id_report]['averange']) ? round($report_details[$id_report]['averange'], 2) : '-');
                    $stats['varianza'][] = (isset($report_details[$id_report]['varianza']) ? round(sqrt($report_details[$id_report]['varianza']), 2) : '-');
                    $stats['max_score'][] = (isset($report_details[$id_report]['max_score']) ? round($report_details[$id_report]['max_score'], 2) : '-');
                    $stats['min_score'][] = (isset($report_details[$id_report]['min_score']) ? round($report_details[$id_report]['min_score'], 2) : '-');
                };
                    break;
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
        $out->add(getTitleArea($lang->def('_COURSEREPORT', 'menu_course'), 'coursereport')
            . '<div class="std_block">');
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

tipobj.innerHTML="' . $lang->def('_EXPORT') . ':"+html+"<a id=\"cambia_link\" class=\""+thetext+"\" href=\"./index.php?modname=coursereport&op=export&amp;type_filter=' . $lev . '\">' . $lang->def('_EXPORT_STATS') . '</a>";
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
var url="./index.php?modname=coursereport&op=export&amp;type_filter=' . $lev . '";

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

        $sql = "SELECT * FROM core_field";
        $filler = "";
        $filler = "<br>";
        $result_quest = sql_query($sql) or die (mysql_error());
        while ($quests = sql_fetch_array($result_quest)) {
            $filler .= "<input type=\'checkbox\' onclick=\'cambialink(" . $quests['idField'] . ",this.checked)\' value=\'" . $quests['idField'] . "\'>" . $quests['translation'] . "<br>";
        }
        $filler .= "<input type=\'checkbox\' onclick=\'cambialink(9999,this.checked)\' value=\'999\'>" . $lang->def('_QUESTION_ANSWERED') . "<br>";
        $filler .= "<input type=\'checkbox\' onclick=\'cambialink(1999,this.checked)\' value=\'1999\'>" . $lang->def('_TOT_QUESTION') . "<br>";
        $filler .= "<input type=\'checkbox\' onclick=\'cambialink(1199,this.checked)\' value=\'1199\'>" . $lang->def('_DATE') . "<br>";
        $filler .= "<input type=\'checkbox\' onclick=\'cambialink(1299,this.checked)\' value=\'1299\'>" . $lang->def('_TOTAL') . "<br>";
        if (checkPerm('mod', true)) {
            $out->add(
                '<div class="table-container-below">' .
                '<ul class="link_list_inline">'
                . '<li><a class="ico-wt-sprite subs_add" href="index.php?modname=coursereport&amp;op=addactivity" title="' . $lang->def('_ADD_ACTIVITY_TITLE') . '">'
                . '<span>' . $lang->def('_ADD_ACTIVITY') . '</span></a></li>'

                . '<li><a class="ico-wt-sprite subs_plus" href="index.php?modname=coursereport&amp;op=addscorm" title="' . $lang->def('_ADD_SCORM_RESULTS') . '">'
                . '<span>' . $lang->def('_ADD_SCORM_RESULTS') . '</span></a></li>'

                . '<li><a class="ico-wt-sprite subs_csv" href="index.php?modname=coursereport&amp;op=export&amp;type_filter=' . $lev . '" title="' . $lang->def('_EXPORT_CSV') . '" onclick="window.open(this.href); return false;">'
                . '<span>' . $lang->def('_EXPORT_CSV') . '</span></a></li>
			</ul>'
                . '</div>'
            );
        }

        $out->add(Form::openForm("statuserfilter", "index.php?modname=coursereport&amp;op=coursereport"));
        $type_groups = array('false' => $lang->def('_ALL'), '1' => $lang2->def('_LEVEL_1'), '2' => $lang2->def('_LEVEL_2'), '3' => $lang2->def('_LEVEL_3'), '4' => $lang2->def('_LEVEL_4'), '5' => $lang2->def('_LEVEL_5'), '6' => $lang2->def('_LEVEL_6'), '7' => $lang2->def('_LEVEL_7'));
        $out->add(Form::getDropdown($lang->def('_LEVEL'),
            'type_filter',
            'type_filter',
            $type_groups,
            $type_filter));

        $out->add(Form::getButton('gofilter', 'gofilter', $lang->def('_SEARCH')));

        $out->add(Form::closeForm());

        $out->add(
            $tb_report->getTable() . '<br /><br />'
            . $tb_score->getTable() . '<br /><br />'
            . $tb_stat->getTable() . '<br /><br />');

        if (checkPerm('mod', true)) {
            $out->add(
                '<div class="table-container-below">' .
                '<ul class="link_list_inline">
				<li><a class="ico-wt-sprite subs_add" href="index.php?modname=coursereport&amp;op=addactivity" title="' . $lang->def('_ADD_ACTIVITY_TITLE') . '">'
                . '<span>'
                . $lang->def('_ADD_ACTIVITY') . '</span></a></li>'

                . '<li><a class="ico-wt-sprite subs_plus" href="index.php?modname=coursereport&amp;op=addscorm" title="' . $lang->def('_ADD_SCORM_RESULTS') . '">'
                . '<span>'
                . $lang->def('_ADD_SCORM_RESULTS') . '</span></a></li>'

                . '<li><a class="ico-wt-sprite subs_csv" href="index.php?modname=coursereport&amp;op=export&amp;type_filter=' . $lev . '" title="' . $lang->def('_EXPORT_CSV') . '" onclick="window.open(this.href); return false;">'
                . '<span>' . $lang->def('_EXPORT_CSV') . '</span></a></li>
			</ul>'
                . '</div>'
            );
        }
        $out->add('</div>');
    }


}

?>