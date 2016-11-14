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

require_once(_base_ . '/lib/lib.json.php');


class CoursereportLmsController extends LmsController
{

    public function init()
    {
        $this->model = new CoursereportLms($_SESSION['idCourse']);
        $this->json = new Services_JSON();
        $this->_mvc_name = "coursereport";
        $this->permissions = array(
            'view' => true,
            'mod' => true
        );
    }

    public function coursereport()
    {
        //checkPerm('view');
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');

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


        $type_filter = Get::pReq('type_filter', DOTY_MIXED, false);

        if ($type_filter == 'false') {
            $type_filter = false;
        }

        $students = getSubscribedInfo((int)$_SESSION['idCourse'], FALSE, $type_filter, TRUE, false, false, true);

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


        $tot_report = $this->model->getReportCount();

        $included_test = $this->model->getSourcesId(CoursereportLms::SOURCE_OF_TEST);
        $reports_id = $this->model->getReportsId();
        $included_test_report_id = $this->model->getReportsId(CoursereportLms::SOURCE_OF_TEST);

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


        $tests_score =& $test_man->getTestsScores($included_test, $id_students);
        // XXX: Calculate statistic
        $test_details = array();

        if (is_array($included_test)) {
            while (list($id_test, $users_result) = each($tests_score)) {
                while (list($id_user, $single_test) = each($users_result)) {
                    if ($single_test['score_status'] == 'valid') {
                        // max
                        if (!isset($test_details[$id_test]['max_score'])) {
                            $test_details[$id_test]['max_score'] = $single_test['score'];
                        } elseif ($single_test['score'] > $test_details[$id_test]['max_score']) {
                            $test_details[$id_test]['max_score'] = $single_test['score'];
                        }

                        // min
                        if (!isset($test_details[$id_test]['min_score'])) {
                            $test_details[$id_test]['min_score'] = $single_test['score'];
                        } elseif ($single_test['score'] < $test_details[$id_test]['min_score']) {
                            $test_details[$id_test]['min_score'] = $single_test['score'];
                        }

                        //number of valid score
                        if (!isset($test_details[$id_test]['num_result'])) {
                            $test_details[$id_test]['num_result'] = 1;
                        } else {
                            $test_details[$id_test]['num_result']++;
                        }

                        // averange
                        if (!isset($test_details[$id_test]['averange'])) {
                            $test_details[$id_test]['averange'] = $single_test['score'];
                        } else {
                            $test_details[$id_test]['averange'] += $single_test['score'];
                        }
                    }
                }
            }
            while (list($id_test, $single_detail) = each($test_details)) {
                if (isset($single_detail['num_result'])) {
                    $test_details[$id_test]['averange'] /= $test_details[$id_test]['num_result'];
                }
            }
            reset($test_details);
        }


        // XXX: Retrive other source scores
        $reports_score =& $report_man->getReportsScores((isset($included_test_report_id) && is_array($included_test_report_id) ? array_diff($reports_id, $included_test_report_id) : $reports_id), $id_students);

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
        while (list($id_report, $single_detail) = each($report_details)) {
            if (isset($single_detail['num_result'])) {
                $report_details[$id_report]['averange'] /= $report_details[$id_report]['num_result'];
            }
        }
        reset($report_details);

        $tests = array();
        foreach ($this->model->getCourseReports() as $info_report) {

            if ($info_report->getSourceOf() != "final_vote") {

                switch ($info_report->getSourceOf()) {
                    case CoursereportLms::SOURCE_OF_TEST : {

                        $name = strip_tags($tests_info[$info_report->getIdSource()]['title']);
                    };
                        break;
                    case CoursereportLms::SOURCE_OF_SCOITEM    : {
                        $name = strip_tags($info_report->getTitle());

                    };
                        break;
                    case CoursereportLms::SOURCE_OF_ACTIVITY    : {
                        $name = strip_tags($info_report->getTitle());
                    };
                        break;
                    case CoursereportLms::SOURCE_OF_FINAL_VOTE    : {
                        $name = strip_tags($lang->def('_FINAL_SCORE'));
                    };
                        break;
                    default: {
                        $name = "";
                    }
                }


                $test = array(
                    'name' => $name,
                    'type' => ucfirst($info_report->getSourceOf()),
                    'max' => $info_report->getMaxScore(),
                    'required' => $info_report->getRequiredScore(),
                    'weight' => $info_report->getWeight(),
                    'show' => ($info_report->isShowToUser() ? 'true' : 'false'),
                    'final' => ($info_report->isUseForFinal() ? 'true' : 'false'),
                    'passed' => array(
                        'value' => (isset($test_details[$id_test]['passed']) ? round($test_details[$id_test]['passed'], 2) : '-'),
                        'link' => 'javascript:void(0)'
                    ),
                    'not_passed' => array(
                        'value' => (isset($test_details[$id_test]['not_passed']) ? round($test_details[$id_test]['not_passed'], 2) : '-'),
                        'link' => 'javascript:void(0)'
                    ),
                    'checked' => array(
                        'value' => (isset($test_details[$id_test]['not_checked']) ? round($test_details[$id_test]['not_checked'], 2) : '-'),
                        'link' => 'javascript:void(0)'
                    ),
                    'average' => (isset($test_details[$id_test]['averange']) ? round($test_details[$id_test]['averange'], 2) : '-'),
                    'max_score' => (isset($test_details[$id_test]['max_score']) ? round($test_details[$id_test]['max_score'], 2) : '-'),
                    'min_score' => (isset($test_details[$id_test]['min_score']) ? round($test_details[$id_test]['min_score'], 2) : '-'),
                    'actions' => array(
                        array(
                            'icon' => 'bar-chart',
                            'link' => 'javascript:void(0)'
                        ),
                        array(
                            'icon' => 'edit',
                            'link' => 'javascript:void(0)'
                        ),
                        array(
                            'icon' => 'trash',
                            'link' => 'javascript:void(0)'
                        )
                    )
                );

                $tests[] = $test;
            }

            $ajaxResponse = array(
                'overview' => array(
                    'tests' => $tests
                ),
                'details' => array(
                    'activities' => array(
                        'Pre Assessment',
                        'Intermediate Test',
                        'Test in Scorm Object',
                        'Survey'
                    ),
                    'students' => array(
                        array(
                            'name' => 'Johnny Rotten',
                            'email' => 'email@email.com',
                            'activities_results' => array(
                                '75 (1)',
                                '81 (17)',
                                '81 (21)',
                                'false'
                            ),
                            'total_result' => '90'
                        ),
                        array(
                            'name' => 'Bill Frisell',
                            'email' => 'email@email.com',
                            'activities_results' => array(
                                '75',
                                '100 (1)',
                                '100 (1)',
                                'true'
                            ),
                            'total_result' => '90'
                        )
                    )
                )
            );


            $params = array(
                'overview' => array(
                    'tests' => array(
                        array(
                            'name' => 'Pre Assessment',
                            'type' => 'Test',
                            'max' => 100,
                            'required' => 75,
                            'weight' => 100,
                            'show' => 'true',
                            'final' => 'false',
                            'passed' => array(
                                'value' => 347,
                                'link' => 'javascript:void(0)'
                            ),
                            'not_passed' => array(
                                'value' => 0,
                                'link' => 'javascript:void(0)'
                            ),
                            'checked' => array(
                                'value' => 15,
                                'link' => 'javascript:void(0)'
                            ),
                            'average' => 75,
                            'max_score' => 33,
                            'min_score' => 10,
                            'actions' => array(
                                array(
                                    'icon' => 'bar-chart',
                                    'link' => 'javascript:void(0)'
                                ),
                                array(
                                    'icon' => 'edit',
                                    'link' => 'javascript:void(0)'
                                ),
                                array(
                                    'icon' => 'trash',
                                    'link' => 'javascript:void(0)'
                                )
                            )
                        ),
                        array(
                            'name' => 'Intermediate Test',
                            'type' => 'Test',
                            'max' => 100,
                            'required' => 75,
                            'weight' => 100,
                            'show' => 'true',
                            'final' => 'true',
                            'passed' => array(
                                'value' => 368,
                                'link' => 'javascript:void(0)'
                            ),
                            'not_passed' => array(
                                'value' => 10,
                                'link' => 'javascript:void(0)'
                            ),
                            'checked' => array(
                                'value' => 50,
                                'link' => 'javascript:void(0)'
                            ),
                            'average' => 81,
                            'max_score' => 100,
                            'min_score' => 21,
                            'actions' => array(
                                array(
                                    'icon' => 'bar-chart',
                                    'link' => 'javascript:void(0)'
                                ),
                                array(
                                    'icon' => 'edit',
                                    'link' => 'javascript:void(0)'
                                ),
                                array(
                                    'icon' => 'trash',
                                    'link' => 'javascript:void(0)'
                                )
                            )
                        )
                    )
                ),
                'details' => array(
                    'activities' => array(
                        'Pre Assessment',
                        'Intermediate Test',
                        'Test in Scorm Object',
                        'Survey'
                    ),
                    'students' => array(
                        array(
                            'name' => 'Johnny Rotten',
                            'email' => 'email@email.com',
                            'activities_results' => array(
                                '75 (1)',
                                '81 (17)',
                                '81 (21)',
                                'false'
                            ),
                            'total_result' => '90'
                        ),
                        array(
                            'name' => 'Bill Frisell',
                            'email' => 'email@email.com',
                            'activities_results' => array(
                                '75',
                                '100 (1)',
                                '100 (1)',
                                'true'
                            ),
                            'total_result' => '90'
                        )
                    )
                )
            );
        }

        $this->render('coursereport', $ajaxResponse);
    }

    /**
     * Restituisce i campi utente
     */
    function getUserFieldsSelector()
    {

        require_once(_adm_ . '/lib/lib.field.php');

        $fman = new FieldList();
        $fields = $fman->getFlatAllFields(array('framework', 'lms'));

        $field_list = array(
            'userid' => Lang::t('_USERNAME', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'lastenter' => Lang::t('_DATE_LAST_ACCESS', 'profile'),
            'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
            'language' => Lang::t('_LANGUAGE', 'standard'),
            'level' => Lang::t('_LEVEL', 'standard')
        );
        $field_list = $field_list + $fields;

        /*$js_arr = array();
        foreach ($field_list as $key => $value) {
            $js_arr[] = $key . ': ' . json_encode($value);
        }
        $f_list_js = '{' . implode(',', $js_arr) . '}';

        $myList = json_encode($field_list);
        */

        echo $this->json->encode($field_list);
    }

    function testreport($idTrack, $idTest, $testName, $studentName)
    {
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');

        $idTrack = Get::gReq('idTrack');
        $idTest = Get::gReq('idTest');

        $testName = Get::gReq('testName');

        $studentName = Get::gReq('studentName');


        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');
        $query_testreport = "
        SELECT DATE_FORMAT(tt.date_attempt, '%d/%m/%Y %H:%i'), tt.score, tt.idTest, t.idUser, tt.number_time
        FROM " . $GLOBALS['prefix_lms'] . "_testtrack_times AS tt
        LEFT JOIN " . $GLOBALS['prefix_lms'] . "_testtrack AS t ON tt.idTrack=t.idTrack
        WHERE tt.idTrack = '" . $idTrack . "' AND tt.idTest = '" . $idTest . "' ORDER BY tt.date_attempt";
        $re_testreport = sql_query($query_testreport);

        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();
        $org_tests =& $report_man->getTest();
        $tests_info =& $test_man->getTestInfo($org_tests);

        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_TH_TEST_REPORT'),
            strip_tags($testName)
        );
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . getBackUi("javascript:history.go(-1)", Lang::t('_BACK', 'standard'))
        );

        $tb = new Table(0, $testName . ' : ' . $studentName);

        $tableHeaderArray = array(
            'N.',
            $lang->def('_DATE'),
            $lang->def('_SCORE'),
            $lang->def('_STATISTICS'),
            $lang->def('_DELETE'));


        $tb->addHead($tableHeaderArray, array('min-cell', '', ''));

        $i = 1;
        while (list($date_attempt, $score, $idTest, $idUser, $number_time) = sql_fetch_row($re_testreport)) {

            $tableBodyArray = array(
                $i++,
                $date_attempt,
                $score,
                '<a class="ico-sprite subs_chart" href="index.php?modname=coursereport&op=testreview&id_test=' . $idTest . '&id_user=' . $idUser . '&number_time=' . $number_time . '&idTrack=' . $idTrack . '"><span>' . $lang->def('_STATISTICS') . '</span></a>',
                '<a class="ico-sprite subs_del" href="index.php?modname=coursereport&op=testreview&delete_track=' . md5($idTest . "_" . $idUser . "_" . $number_time) . '&id_test=' . $idTest . '&id_user=' . $idUser . '&number_time=' . $number_time . '&idTrack=' . $idTrack . '"><span>' . $lang->def('_DELETE') . '</span></a>');

            $tb->addBody($tableBodyArray);
        }


        $out->add(
            $tb->getTable()
            . '</div>'
            , 'content');

    }

    function scormreport($idTest)
    {
        checkPerm('view');
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');
        $query_testreport = "
        SELECT DATE_FORMAT(date_action, '%d/%m/%Y %H:%i'), score_raw
        FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking_history
        WHERE idscorm_tracking = " . $idTest . " ORDER BY date_action";
        $re_testreport = sql_query($query_testreport);

        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();
        $org_tests =& $report_man->getTest();
        $tests_info =& $test_man->getTestInfo($org_tests);

        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_TH_TEST_REPORT'),
            strip_tags($testName)
        );
        $out->add(getTitleArea($page_title, 'coursereport') . '<div class="std_block">' . getBackUi("javascript:history.go(-1)", Lang::t('_BACK', 'standard')));
        $tb = new Table(0, $testName . ' : ' . $studentName);
        $tb->addHead(array(
            'N.',
            $lang->def('_DATE'),
            $lang->def('_SCORE'),
        ), array('min-cell', '', ''));

        $i = 1;
        while (list($date_attempt, $score) = sql_fetch_row($re_testreport)) {
            $tb->addBody(array($i++, $date_attempt, $score));
        }
        $out->add($tb->getTable() . '</div>', 'content');
    }

    function saveTestUpdate($id_test, &$test_man)
    {
        // Save report modification
        if (isset($_POST['user_score'])) {
            $query_upd_report = "
			UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
			SET weight = '" . $_POST['weight'] . "',
				show_to_user = '" . $_POST['show_to_user'] . "',
				use_for_final = '" . $_POST['use_for_final'] . "'"
                . (isset($_POST['max_score']) && $_POST['max_score'] > 0 ? ", max_score = '" . (float)$_POST['max_score'] . "'" : "")
                . " WHERE  id_course = '" . $_SESSION['idCourse'] . "' AND id_source = '" . $id_test . "' AND source_of = 'test'";
            $re = sql_query($query_upd_report);

            // save user score modification
            $re &= $test_man->saveTestUsersScores($id_test, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);
        } else {
            $query_upd_report = "
			UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
			SET weight = '" . $_POST['weight'] . "',
				show_to_user = '" . $_POST['show_to_user'] . "',
				use_for_final = '" . $_POST['use_for_final'] . "'"
                . (isset($_POST['max_score']) && $_POST['max_score'] > 0 ? ", max_score = '" . (float)$_POST['max_score'] . "'" : "")
                . " WHERE  id_course = '" . $_SESSION['idCourse'] . "' AND id_source = '" . $id_test . "' AND source_of = 'test'";
            $re = sql_query($query_upd_report);
        }
        return $re;
    }

    function testvote()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');
        require_once(_base_ . '/lib/lib.json.php');

        // XXX: Initializaing
        $id_test = importVar('id_test', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();

        // XXX: Find students
        $type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
        $id_students = array_keys($students);
        $students_info =& $acl_man->getUsers($id_students);

        // XXX: Find test
        $test_info =& $test_man->getTestInfo(array($id_test));

        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($test_info[$id_test]['title'])
        );
        $GLOBALS['page']->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">', 'content');
        //==========================================================================================
        // XXX: Reset track of user
        if (isset($_POST['reset_track'])) {
            $re = saveTestUpdate($id_test, $test_man);
            list($id_user,) = each($_POST['reset_track']);

            $user_info = $acl_man->getUser($id_user, false);

            $GLOBALS['page']->add(
                Form::openForm('test_vote', 'index.php?modname=coursereport&amp;op=testvote')
                . Form::getHidden('id_test', 'id_test', $id_test)
                . Form::getHidden('id_user', 'id_user', $id_user)
                . getDeleteUi($lang->def('_AREYOUSURE'),
                    '<span>' . $lang->def('_RESET') . ' : </span>' . strip_tags($test_info[$id_test]['title']) . '<br />'
                    . '<span>' . $lang->def('_OF_USER') . ' : </span>' . ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
                        ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
                        : $acl_man->relativeId($user_info[ACL_INFO_USERID])),
                    false,
                    'confirm_reset',
                    'undo_reset')
                . Form::closeForm()
                . '</div>', 'content');
            return;
        }
        if (isset($_POST['confirm_reset'])) {
            $id_user = importVar('id_user', true, 0);
            if ($test_man->deleteTestTrack($id_test, $id_user))
                $GLOBALS['page']->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')), 'content');//($lang->def('_RESET_TRACK_SUCCESS')), 'content');
            else
                $GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')), 'content');
        }

        //==========================================================================================

        if (isset($_POST['save'])) {
            $re = saveTestUpdate($id_test, $test_man);
            Util::jump_to('index.php?r=coursereport/coursereport&resul=' . ($re ? 'ok' : 'err'));
        }

        // retirive activity info
        $query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	WHERE id_course = '" . $_SESSION['idCourse'] . "'
	AND source_of = 'test' AND id_source = '" . $id_test . "'";

        $info_report = sql_fetch_assoc(sql_query($query_report));

        $query = "SELECT question_random_number"
            . " FROM " . $GLOBALS['prefix_lms'] . "_test"
            . " WHERE idTest = '" . $id_test . "'";

        list($question_random_number) = sql_fetch_row(sql_query($query));

        $json = new Services_JSON();
        $chart_options = $json->decode($chart_options_json);
        if (!property_exists($chart_options, 'use_charts')) $chart_options->use_charts = false;
        if (!property_exists($chart_options, 'selected_chart')) $chart_options->selected_chart = 'column';
        if (!property_exists($chart_options, 'show_chart')) $chart_options->show_chart = 'teacher';

        /* XXX: scores */
        $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));

        if ($chart_options->use_charts) {
            $type_h = array('', 'align-center', 'align-center', 'image', 'align-center', '', 'image');
            $cont_h = array($lang->def('_STUDENTS'),
                $lang->def('_SCORE'),
                $lang->def('_SHOW_ANSWER'),
                '<img src="' . getPathImage('lms') . 'standard/stats22.gif" alt="' . $lang->def('_SHOW_CHART') . '" title="' . $lang->def('_SHOW_CHART_TITLE') . '" />',
                $lang->def('_DATE'),
                $lang->def('_COMMENTS'),
                '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . $lang->def('_RESET') . '" title="' . $lang->def('_RESET') . '" />');
        } else {
            $type_h = array('', 'align-center', 'align-center', 'align-center', '', 'image');
            $cont_h = array($lang->def('_STUDENTS'),
                $lang->def('_SCORE'),
                $lang->def('_SHOW_ANSWER'),
                $lang->def('_DATE'),
                $lang->def('_COMMENTS'),
                '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . $lang->def('_RESET') . '" title="' . $lang->def('_RESET') . '" />');
        }
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        $out->add(
            Form::openForm('test_vote', 'index.php?modname=coursereport&amp;op=testvote')
            . Form::getHidden('id_test', 'id_test', $id_test)
        );

        $out->add(
        // main form
            Form::openElementSpace()
            . Form::getOpenFieldSet($lang->def('_TEST_INFO'))

            . Form::getLinebox($lang->def('_TITLE_ACT'),
                strip_tags($test_info[$id_test]['title']))
            . ($question_random_number ? Form::getTextfield($lang->def('_MAX_SCORE'), 'max_score', 'max_score', '11', $info_report->getMaxScore()) : Form::getLinebox($lang->def('_MAX_SCORE'), $info_report->getMaxScore()))
            . Form::getLinebox($lang->def('_REQUIRED_SCORE'),
                $info_report->getRequiredScore())

            . Form::getTextfield($lang->def('_WEIGHT'),
                'weight',
                'weight',
                '11',
                $info_report->getWeight())
            . Form::getDropdown($lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                $info_report->isShowToUserToString())
            . Form::getDropdown($lang->def('_USE_FOR_FINAL'),
                'use_for_final',
                'use_for_final',
                array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                $info_report->isUseForFinalToString())
            . Form::getCloseFieldSet()
            . Form::closeElementSpace()
        );

        // XXX: retrive scores
        $tests_score =& $test_man->getTestsScores(array($id_test), $id_students);

        // XXX: Display user scores
        $i = 0;
        while (list($idst_user, $user_info) = each($students_info)) {
            $user_name = ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
                ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
                : $acl_man->relativeId($user_info[ACL_INFO_USERID]));

            $cont = array(Form::getLabel('user_score_' . $idst_user, $user_name));

            $id_test = $info_report->getIdSource();
            if (isset($tests_score[$id_test][$idst_user])) {
                switch ($tests_score[$id_test][$idst_user]['score_status']) {
                    case "not_complete" : {
                        $cont[] = '-';
                    };
                        break;
                    case "not_checked"    : {
                        $cont[] = '<span class="cr_not_check">' . $lang->def('_NOT_CHECKED') . '</span><br />'
                            . Form::getInputTextfield('textfield_nowh',
                                'user_score_' . $idst_user,
                                'user_score[' . $idst_user . ']',
                                $tests_score[$id_test][$idst_user]['score'],
                                strip_tags($lang->def('_SCORE')),
                                '8',
                                ' tabindex="' . $i++ . '" ');
                    };
                        break;
                    case "not_passed"    :
                    case "passed"        : {
                        /*
                        $cont[] = Form::getInputDropdown(	'dropdown',
                                                                'user_score',
                                                                'user_score',
                                                                array('passed' => $lang->def('_PASSED'), 'not_passed' => $lang->def('_NOT_PASSED')),
                                                                $tests_score[$id_test][$idst_user]['score_status'],
                                                                '');
                                                                */
                        $cont[] = Form::getInputTextfield('textfield_nowh',
                            'user_score_' . $idst_user,
                            'user_score[' . $idst_user . ']',
                            $tests_score[$id_test][$idst_user]['score'],
                            strip_tags($lang->def('_SCORE')),
                            '8',
                            ' tabindex="' . $i++ . '" ');

                    };
                        break;
                    case "valid"        : {
                        $cont[] = Form::getInputTextfield('textfield_nowh',
                            'user_score_' . $idst_user,
                            'user_score[' . $idst_user . ']',
                            $tests_score[$id_test][$idst_user]['score'],
                            strip_tags($lang->def('_SCORE')),
                            '8',
                            ' tabindex="' . $i++ . '" ');
                    };
                        break;
                    default : {

                        $cont[] = '-';
                    }
                }
                if ($tests_score[$id_test][$idst_user]['score_status'] != 'not_comlete') {
                    $cont[] = Form::getButton('view_anser_' . $idst_user, 'view_answer[' . $idst_user . ']', $lang->def('_SHOW_ANSWER'), 'button_nowh');

                    if ($chart_options->use_charts) {
                        $img = '<img src="' . getPathImage('lms') . 'standard/stats22.gif" alt="' . $lang->def('_SHOW_CHART') . '" title="' . $lang->def('_SHOW_CHART_TITLE') . '" />';
                        $url = 'index.php?modname=coursereport&op=showchart&id_test=' . (int)$id_test . '&id_user=' . (int)$idst_user . '&chart_type=' . $chart_options->selected_chart;
                        $cont[] = '<a href="' . $url . '">' . $img . '</a>';
                    }

                    $cont[] = Form::getInputDatefield('textfield_nowh',
                        'date_attempt_' . $idst_user,
                        'date_attempt[' . $idst_user . ']',
                        Format::date($tests_score[$id_test][$idst_user]['date_attempt']));

                    $cont[] = Form::getInputTextarea('comment_' . $idst_user,
                        'comment[' . $idst_user . ']',
                        $tests_score[$id_test][$idst_user]['comment'],
                        'textarea_wh_full',
                        2);

                    $cont[] = '<input 	class="reset_track"
									type="image"
									src="' . getPathImage('lms') . 'standard/delete.png"
									alt="' . $lang->def('_RESET') . '"
									id="reset_track_' . $idst_user . '"
									name="reset_track[' . $idst_user . ']"
									title="' . $lang->def('_RESET') . '" />';
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
            . Form::getButton('save_top', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo_top', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()

            . $tb->getTable()
            . Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>');
    }

    function testDetail()
    {
        checkPerm('mod');

        require_once(_base_ . '/lib/lib.table.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');

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

        $page_title = array('index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            'index.php?modname=coursereport&amp;op=testdetail&amp;id_test=' . $id_test => $test_info[$id_test]['title']
        );

        $out->add(getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
        );

        $query_test = "SELECT title"
            . " FROM " . $GLOBALS['prefix_lms'] . "_test"
            . " WHERE idTest = '" . $id_test . "'";

        list($titolo_test) = sql_fetch_row(sql_query($query_test));

        $query_quest = "SELECT idQuest, type_quest, title_quest"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testquest"
            . " WHERE idTest = '" . $id_test . "'"
            . " ORDER BY sequence";

        $result_quest = sql_query($query_quest);

        while (list($id_quest, $type_quest, $title_quest) = sql_fetch_row($result_quest)) {
            $quests[$id_quest]['idQuest'] = $id_quest;
            $quests[$id_quest]['type_quest'] = $type_quest;
            $quests[$id_quest]['title_quest'] = $title_quest;

            $query_answer = "SELECT idAnswer, is_correct, answer"
                . " FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer"
                . " WHERE idQuest = '" . $id_quest . "'"
                . " ORDER BY sequence";

            $result_answer = sql_query($query_answer);

            while (list($id_answer, $is_correct, $answer) = sql_fetch_row($result_answer)) {
                $answers[$id_quest][$id_answer]['idAnswer'] = $id_answer;
                $answers[$id_quest][$id_answer]['is_correct'] = $is_correct;
                $answers[$id_quest][$id_answer]['answer'] = $answer;
            }
        }

        $query_track = "SELECT idTrack"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack"
            . " WHERE idTest = '" . $id_test . "'";

        $result_track = sql_query($query_track);

        while (list($id_track) = sql_fetch_row($result_track)) {
            $query_track_answer = "SELECT idQuest, idAnswer"
                . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer"
                . " WHERE idTrack = '" . $id_track . "'";

            $result_track_answer = sql_query($query_track_answer);

            while (list($id_quest, $id_answer) = sql_fetch_row($result_track_answer))
                $tracks[$id_track][$id_quest] = $id_answer;
        }
    }

    function testreview()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');

        // XXX: Initializaing
        $id_test = importVar('id_test', true, 0);
        $id_track = importVar('idTrack', true, 0);
        $number_time = importVar('number_time', true, null);
        $delete = importVar('delete_track', false, null);

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');


        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();

        // XXX: Save input if needed
        if (isset($_POST['view_answer'])) {
            $re = saveTestUpdate($id_test, $test_man);
            list($id_user,) = each($_POST['view_answer']);
        } else {
            $id_user = importVar('id_user', true, 0);
        }

        if (isset($_POST['save_new_scores'])) {
            $re = $test_man->saveReview($id_test, $id_user);
            Util::jump_to('index.php?modname=coursereport&amp;op=testvote&amp;id_test=' . $id_test . '&result=' . ($re ? 'ok' : 'err'));
        }

        $user_name = $acl_man->getUserName($id_user);

        // XXX: Find test
        $test_info =& $test_man->getTestInfo(array($id_test));

        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            'index.php?modname=coursereport&amp;op=testvote&amp;id_test=' . $id_test => $test_info[$id_test]['title'],
            $user_name
        );
        if (isset($_POST['view_answer'])) {
            $out->add(
                getTitleArea($page_title, 'coursereport')
                . '<div class="std_block">'
                . Form::openForm('test_vote', 'index.php?modname=coursereport&amp;op=testreview')
                . Form::getHidden('id_test', 'id_test', $id_test)
                . Form::getHidden('id_user', 'id_user', $id_user)
            );
            $test_man->editReview($id_test, $id_user, $number_time);
            $out->add(
                Form::openButtonSpace()
                . Form::getButton('save_new_scores', 'save_new_scores', $lang->def('_SAVE'))
                . Form::getButton('undo_testreview', 'undo_testreview', $lang->def('_UNDO'))
                . Form::closeButtonSpace()
                . Form::closeForm()
                . '</div>'
            );
        } else {
            $out->add(
                getTitleArea($page_title, 'coursereport')
                . '<div class="std_block">'
                . Form::openForm('test_vote', 'index.php?modname=coursereport&op=testreport&idTest=' . $id_test . '&idTrack=' . $id_track)
            );
            $test_man->editReview($id_test, $id_user, $number_time, false);
            $out->add(
                Form::openButtonSpace()
                . Form::getButton('go_back', 'go_back', $lang->def('_UNDO'))

            );

            if ($delete == md5($id_test . "_" . $id_user . "_" . $number_time)) {

                $out->add(Form::getButton('delete_track_button', $lang->def('_DELETE_TEST_TRACK'), $lang->def('_DELETE_TEST_TRACK'), 'btn btn-default', 'data-toggle="modal" data-target="#delete_test_track_modal"', false, false)
                    . Form::closeButtonSpace()
                    . Form::closeForm()
                    . '</div></div>');

                $modal = '<div class="modal fade" tabindex="-1" role="dialog" id="delete_test_track_modal">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">' . $lang->def('_DELETE_TEST_TRACK_MODAL_TITLE') . '</h4>
                                </div>
                                <div class="modal-body">
                                    <p>' . $lang->def("_DELETE_TEST_TRACK_MODAL_BODY") . '</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">' . $lang->def('_CLOSE') . '</button>
                                    <button id="detele-test-track" type="button" class="btn btn-default">' . $lang->def('_DELETE') . '</button>
                                </div>
                            </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  <script type="text/javascript">
                           $( document ).ready(function() {
                               
                               $("#detele-test-track").on( "click", function(event) {
                                   
                                   event.preventDefault();
                                   
                                   window.location.href = "index.php?modname=coursereport&op=testdelete&delete_track=' . md5($id_test . "_" . $id_user . "_" . $number_time) . '&id_test=' . $id_test . '&id_user=' . $id_user . '&number_time=' . $number_time . '&idTrack=' . $id_track . '";
                               });
                               
                               
                               $("#delete_test_track_modal").modal("show");
                           });

                    </script>';
                $out->add($modal);

            } else {
                $out->add(Form::closeButtonSpace()
                    . Form::closeForm()
                    . '</div>');
            }
        }


    }

    /**
     * Mostra la view di riepilogo del test con il pulsante per l'eliminazione del test.
     */
    function testdelete()
    {

        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');

        // XXX: Initializaing
        $id_test = importVar('id_test', true, 0);
        $id_track = importVar('idTrack', true, 0);
        $number_time = importVar('number_time', true, null);
        $delete = importVar('delete_track', false, null);
        $id_user = importVar('id_user', true, 0);

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        if ($delete == md5($id_test . "_" . $id_user . "_" . $number_time)) {

            $test_man = new GroupTestManagement();

            $test_man->deleteReview($id_test, $id_user, $id_track, $number_time);

            $acl_man = Docebo::user()->getAclManager();

            $test_man = new GroupTestManagement();

            $user_name = $acl_man->getUserName($id_user);

            // XXX: Find test
            $test_info =& $test_man->getTestInfo(array($id_test));

            Util::jump_to('index.php?modname=coursereport&op=testreport&idTest=' . $id_test . '&idTrack=' . $id_track . '&testName=' . $test_info[$id_test]['title'] . '&studentName=' . $user_name);

        } else {
            die("You can't access");
        }
    }

    function finalvote()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $report_man = new CourseReportManager();

        // XXX: Find students
        $type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
        $id_students = array_keys($students);
        $students_info =& $acl_man->getUsers($id_students);

        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_FINAL_SCORE'))
        );
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . Form::openForm('finalvote', 'index.php?modname=coursereport&amp;op=finalvote&amp;type_filter=' . $type_filter)
            . Form::getHidden('id_report', 'id_report', $id_report)
        );

        // XXX: Save input if needed
        if (isset($_POST['save'])) {
            // Save report modification
            $query_upd_report = "
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
		SET max_score = '" . $_POST['max_score'] . "',
			required_score = '" . $_POST['required_score'] . "',
			show_to_user = '" . $_POST['show_to_user'] . "'
		WHERE  id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '" . $id_report . "'
			AND source_of = 'final_vote' AND id_source = '0'";
            sql_query($query_upd_report);
            // save user score modification

            $re = $report_man->saveReportScore($id_report, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);

            Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($re ? 'ok' : 'err'));
        }

        if (isset($_POST['save'])) {
            // retirive activity info
            //__construct($id_report, $title, $max_score, $required_score, $weight, $show_to_user, $use_for_final, $source_of, $id_source)
            $info_report = new ReportLms(null, null, importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', false, 'true'), null, 'final_vote', importVar('max_score', true));


        } else {
            // retirive activity info

            $info_report = CoursereportLms::getReportFinalScore($_SESSION['idCourse']);
        }

        $out->add(
        // main form
            Form::openElementSpace()
            . Form::getOpenFieldSet($lang->def('_TEST_INFO'))

            . Form::getLinebox($lang->def('_TITLE_ACT'),
                $lang->def('_FINAL_SCORE'))
            . Form::getTextfield($lang->def('_MAX_SCORE'),
                'max_score',
                'max_score',
                '11',
                $info_report->getMaxScore())
            . Form::getTextfield($lang->def('_REQUIRED_SCORE'),
                'required_score',
                'required_score',
                '11',
                $info_report->getRequiredScore())
            . Form::getDropdown($lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                $info_report->isShowToUserToString())
            . Form::getCloseFieldSet()
            . Form::closeElementSpace()
        );

        /* XXX: scores */
        $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));
        $type_h = array('', 'align-center', 'align-center', 'align-center', '');
        $cont_h = array($lang->def('_STUDENTS'),
            $lang->def('_SCORE'),
            $lang->def('_DATE'),
            $lang->def('_COMMENTS'));
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        // XXX: retrive scores
        $report_score =& $report_man->getReportsScores(array($id_report));

        // XXX: Display user scores
        $i = 0;
        while (list($idst_user, $user_info) = each($students_info)) {

            $user_name = ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
                ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
                : $acl_man->relativeId($user_info[ACL_INFO_USERID]));
            $cont = array(Form::getLabel('user_score_' . $idst_user, $user_name));

            $cont[] = Form::getInputTextfield('textfield_nowh',
                'user_score_' . $idst_user,
                'user_score[' . $idst_user . ']',
                (isset($report_score[$id_report][$idst_user]['score'])
                    ? $report_score[$id_report][$idst_user]['score'] : ''),
                strip_tags($lang->def('_SCORE')),
                '8',
                ' tabindex="' . $i++ . '" ');
            $cont[] = Form::getInputDatefield('textfield_nowh',
                'date_attempt_' . $idst_user,
                'date_attempt[' . $idst_user . ']',
                Format::date(
                    (isset($report_score[$id_report][$idst_user]['date_attempt'])
                        ? $report_score[$id_report][$idst_user]['date_attempt'] : ''), 'date'));
            $cont[] = Form::getInputTextarea('comment_' . $idst_user,
                'comment[' . $idst_user . ']',
                (isset($report_score[$id_report][$idst_user]['comment'])
                    ? $report_score[$id_report][$idst_user]['comment'] : ''),
                'textarea_wh_full',
                2);

            $tb->addBody($cont);
        }

        $out->add(
            Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()

            . $tb->getTable()
            . Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>');
    }

    function roundtest()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_test = importVar('id_test', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();

        // XXX: Find test from organization
        $re = $test_man->roundTestScore($id_test);

        Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    function roundreport()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);

        // XXX: Instance management
        $report_man = new CourseReportManager();

        // XXX: Find test from organization
        $re = $report_man->roundReportScore($id_report);

        Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    /**
     *    final_score =
     *
     *    sum( (score[n] * weight[n]) / total_weigth )
     *    ----------------------------------------------------  * final_max_score
     *    sum( (max_score[n] * weight[n]) / total_weigth )
     *
     * equal to :
     *    sum( score[n] * weight[n] )
     *    --------------------------------  * final_max_score
     *    sum( max_score[n] * weight[n] )
     */

    function redofinal()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();

        // XXX: Find students
        $id_students =& $report_man->getStudentId();

        // XXX: retrive info about the final score

        $courseReportLms = new CoursereportLms($_SESSION['idCourse']);


        $info_final = $courseReportLms->getReportsFilteredBySourceOf('final_vote');

        // XXX: Retrive all reports (test and so), and set it

        $reports = $courseReportLms->getReportsForFinal();

        if (count($reports) == 0) {
            Util::jump_to('index.php?r=coursereport/coursereport&amp;result=ok');
        }

        $sum_max_score = 0;
        $included_test = array();
        $other_source = array();

        foreach ($reports as $info_report) {

            $sum_max_score += $info_report->getMaxScore() * $info_report->getWeight();

            switch ($info_report->getSourceOf()) {
                case "activity" :
                    $other_source[$info_report->getIdReport()] = $info_report->getIdReport();
                    break;
                case "test" :
                    $included_test[$info_report->getIdSource()] = $info_report->getIdSource();
                    break;
            }
        }
        // XXX: Retrive Test score
        if (!empty($included_test))
            $tests_score =& $test_man->getTestsScores($included_test, $id_students);

        // XXX: Retrive other score
        if (!empty($other_source))
            $other_score =& $report_man->getReportsScores($other_source);

        $final_score = array();

        while (list(, $id_user) = each($id_students)) {
            $user_score = 0;

            foreach ($reports as $info_report) {
                switch ($info_report->getSourceOf()) {
                    case "activity" : {
                        if (isset($other_score[$info_report->getIdReport()][$id_user]) && ($other_score[$info_report->getIdReport()][$id_user]['score_status'] == 'valid')) {
                            $user_score += ($other_score[$info_report->getIdReport()][$id_user]['score'] * $info_report->getWeight());
                        } else {
                            $user_score += 0;
                        }
                    };
                        break;
                    case "test" : {
                        if (isset($tests_score[$info_report->getIdSource()][$id_user]) && ($tests_score[$info_report->getIdSource()][$id_user]['score_status'] == 'valid')) {
                            $user_score += ($tests_score[$info_report->getIdSource()][$id_user]['score'] * $info_report->getWeight());
                        } else {
                            $user_score += 0;
                        }
                    };
                        break;
                }
            }

            // user final score
            if ($sum_max_score != 0)
                $final_score[$id_user] = round(($user_score / $sum_max_score) * $info_final[0]->getMaxScore(), 2);
            else
                $final_score[$id_user] = 0;
        }
        // Save final scores
        $exists_final = array();
        $query_final_score = "
	SELECT id_user
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport_score
	WHERE id_report = '" . $info_final['id_report'] . "'";

        $re_final = sql_query($query_final_score);
        while (list($id_user) = sql_fetch_row($re_final)) {
            $exists_final[$id_user] = $id_user;
        }

        $re = true;

        while (list($user, $score) = each($final_score)) {
            if (isset($exists_final[$user])) {
                $query_scores = "
			UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport_score
			SET score = '" . $score . "',
				date_attempt = '" . date("Y-m-d H:i:s") . "'
			WHERE id_report = '" . $info_final['id_report'] . "' AND id_user = '" . $user . "'";
                $re &= sql_query($query_scores);
            } else {
                $query_scores = "
			INSERT INTO  " . $GLOBALS['prefix_lms'] . "_coursereport_score
			( id_report, id_user, score, date_attempt ) VALUES (
				'" . $info_final['id_report'] . "',
				'" . $user . "',
				'" . $score . "',
				'" . date("Y-m-d H:i:s") . "' )";
                $re &= sql_query($query_scores);
            }
        }
        Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    function modscorm()
    {
        checkPerm('mod');

        require_once(_lms_ . '/lib/lib.coursereport.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = Get::req('id_report', DOTY_INT, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: undo
        if (isset($_POST['undo']))
            jumpTo('index.php?r=coursereport/coursereport');

        // XXX: Retrive all colums (test and so), and set it
        if ($id_report == 0) {

            $info_report = new ReportLms(importVar('id_report', true, 0), importVar('title'), importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', true, true), importVar('use_for_final', true, true), '', 0);


        } elseif (!isset($_POST['save'])) {

            $this->model = new CoursereportLms($_SESSION['idCourse'], $id_report, 'activity', '0');

            $info_report = $this->model->getCourseReports()[0];
        }

        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY'))
        );
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'

            . getBackUi('index.php?r=coursereport/coursereport', $lang->def('_BACK'))
        );
        // XXX: Save input if needed
        if (isset($_POST['save']) && is_numeric($_POST['id_source'])) {

            $report_man = new CourseReportManager();
            // check input
            if ($_POST['titolo'] == '')
                $_POST['titolo'] = $lang->def('_NOTITLE');
            //MODIFICHE NUOVISSIMISSIME
            $query_report = "
		SELECT  *
		FROM " . $GLOBALS['prefix_lms'] . "_scorm_items
		WHERE idscorm_item=" . $_POST['id_source'];
            //echo $query_report;
            $risultato = sql_query($query_report);
            $titolo2 = sql_fetch_assoc($risultato);

            // if module title is equals to main title don't append it
            if ($titolo2['title'] != $_POST['titolo']) {
                $_POST['titolo'] = $_POST['titolo'] . " - " . addslashes($titolo2['title']);
            }

            $_POST['title'] = $_POST['titolo'];
            $re_check = $report_man->checkActivityData($_POST);

            if (!$re_check['error']) {
                if ($id_report == 0) {
                    $numero = $report_man->getNextSequence();
                    $query_ins_report = "
				INSERT INTO " . $GLOBALS['prefix_lms'] . "_coursereport
				( id_course, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
					'" . $_SESSION['idCourse'] . "',
					'" . $_POST['title'] . "',
					'0',
					'0',
					'" . $_POST['weight'] . "',
					'" . $_POST['show_to_user'] . "',
					'" . $_POST['use_for_final'] . "',
					'" . $_POST['source_of'] . "',
					'" . $_POST['id_source'] . "',
					'" . $numero . "'
				)";
                    echo $query_ins_report;

                    $re = sql_query($query_ins_report);
                } else {
                    $query_upd_report = "
				UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
				SET title = '" . $_POST['title'] . "',
					weight = '" . $_POST['weight'] . "',
					max_score = '0',
					required_score = '0',
					use_for_final = '" . $_POST['use_for_final'] . "',
					show_to_user = '" . $_POST['show_to_user'] . "'
				WHERE id_course = '" . $_POST['id_course'] . "' AND id_report = '" . $id_report . "'";
                    $re = sql_query($query_upd_report);
                }
                Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($re ? 'ok' : 'err'));
            } else
                $out->add(getErrorUi($re_check['message']));
        }

        if (isset($_POST['filtra'])) {
            if ($_POST['source_of'] == 'scoitem' && is_numeric($_POST['title'])) {//richiesto lo scorm item
                $query_report = "
			SELECT  title
			FROM " . $GLOBALS['prefix_lms'] . "_organization
			WHERE objectType='scormorg' and idResource=" . (int)$_POST['title'] . "";
                $risultato = sql_query($query_report);
                $titolo = sql_fetch_assoc($risultato);
                $titolo = $titolo['title'];

                $query_report = "
			SELECT  *
			FROM " . $GLOBALS['prefix_lms'] . "_scorm_items
			WHERE idscorm_organization=" . (int)$_POST['title'] . "
			ORDER BY idscorm_item";
                //echo $query_report;
                $risultato = sql_query($query_report);
                while ($scorm = sql_fetch_assoc($risultato))
                    $array_scorm[$scorm['idscorm_item']] = $scorm['title'];

                $out->add(
                    Form::openForm('addscorm', 'index.php?modname=coursereport&amp;op=addscorm')
                    . Form::openElementSpace()
                    . Form::getHidden('id_report', 'id_report', $id_report)
                    . Form::getDropdown($lang->def('_SCORM_ITEM'),
                        'id_source',
                        'id_source',
                        $array_scorm,
                        $info_report->getIdSource())


                    . Form::getTextfield($lang->def('_WEIGHT'),
                        'weight',
                        'weight',
                        '11',
                        $info_report->getWeight())
                    . Form::getDropdown($lang->def('_SHOW_TO_USER'),
                        'show_to_user',
                        'show_to_user',
                        array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                        $info_report->isShowToUserToString())
                    . Form::getDropdown($lang->def('_USE_FOR_FINAL'),
                        'use_for_final',
                        'use_for_final',
                        array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                        $info_report->isUseForFinalToString())
                    . Form::getHidden('title', 'title', $_POST['title'])
                    . Form::getHidden('source_of', 'source_of', $_POST['source_of'])
                    . Form::getHidden('titolo', 'titolo', $titolo)
                    . Form::closeElementSpace()
                    . Form::openButtonSpace()
                    . Form::getButton('save', 'save', $lang->def('_SAVE'))
                    . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
                    . Form::closeButtonSpace()
                    . Form::closeForm()
                    . '</div>');
            }
        }
        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY'))
        );

        if (!isset($_POST['filtra'])) {
            $query_report = "
			SELECT  idResource,title
			FROM " . $GLOBALS['prefix_lms'] . "_organization
			WHERE objectType='scormorg' and idCourse=" . $_SESSION['idCourse'] . "";
            $risultato = sql_query($query_report);
            while ($scorm = sql_fetch_assoc($risultato))
                $array_scorm[$scorm['idResource']] = $scorm['title'];

            $out->add(
                Form::openForm('addscorm', 'index.php?modname=coursereport&amp;op=addscorm')
                . Form::openElementSpace()
                . Form::getHidden('id_report', 'id_report', $id_report)
                . Form::getDropdown($lang->def('_TITLE'),
                    'title',
                    'title',
                    $array_scorm,
                    $info_report->getTitle())

                . Form::getRadioSet($lang->def('_SCORE'),
                    'source_of',
                    'source_of',
                    array("Scorm Item" => 'scoitem'),//,  "Somma" => 'scormorg_sum', "Media"  =>'scormorg_avg'),
                    'scoitem')

                . Form::closeElementSpace()
                . Form::openButtonSpace()
                . Form::getButton('filtra', 'filtra', $lang->def('_SAVE'))
                . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
                . Form::closeButtonSpace()
                . Form::closeForm()
                . '</div>');
        }
    }

    function modactivity()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: undo
        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }

        // XXX: Retrive all colums (test and so), and set it

        if ($id_report == 0) {

            $info_report = new ReportLms(importVar('id_report', true, 0), importVar('title'), importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', true, true), importVar('use_for_final', true, true), '', 0);


        } elseif (!isset($_POST['save'])) {

            $this->model = new CoursereportLms($_SESSION['idCourse'], $id_report, 'activity', '0');

            $info_report = $this->model->getCourseReports()[0];
        }


        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY'))
        );
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . getBackUi('index.php?r=coursereport/coursereport', $lang->def('_BACK'))
        );
        // XXX: Save input if needed
        if (isset($_POST['save'])) {
            $report_man = new CourseReportManager();
            // check input
            if ($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');

            $re_check = $report_man->checkActivityData($_POST);
            if (!$re_check['error']) {
                if ($id_report == 0) $re = $report_man->addActivity($_SESSION['idCourse'], $_POST);
                else $re = $report_man->updateActivity($id_report, $_SESSION['idCourse'], $_POST);
                Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($re ? 'ok' : 'err'));
            } else
                $out->add(getErrorUi($re_check['message']));
        }

        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY'))
        );
        $out->add(
            Form::openForm('addactivity', 'index.php?modname=coursereport&amp;op=addactivity')
            . Form::openElementSpace()
            . Form::getHidden('id_report', 'id_report', $id_report)
            . Form::getTextfield($lang->def('_TITLE_ACT'),
                'title',
                'title',
                '255',
                $info_report->getTitle())
            . Form::getTextfield($lang->def('_MAX_SCORE'),
                'max_score',
                'max_score',
                '11',
                $info_report->getMaxScore())
            . Form::getTextfield($lang->def('_REQUIRED_SCORE'),
                'required_score',
                'required_score',
                '11',
                $info_report->getRequiredScore())
            . Form::getTextfield($lang->def('_WEIGHT'),
                'weight',
                'weight',
                '11',
                $info_report->getWeight())
            . Form::getDropdown($lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                $info_report->isShowToUserToString())
            . Form::getDropdown($lang->def('_USE_FOR_FINAL'),
                'use_for_final',
                'use_for_final',
                array('true' => $lang->def('_YES'), 'false' => $lang->def('_NO')),
                $info_report->isUseForFinalToString())
            . Form::closeElementSpace()
            . Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>');
    }

    function modactivityscore()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $report_man = new CourseReportManager();

        // XXX: Find users
        $type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
        $id_students = array_keys($students);
        $students_info =& $acl_man->getUsers($id_students);

        if (isset($_POST['save'])) {

            $info_report = new ReportLms(importVar('id_report', true, 0), importVar('title'), importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', true, true), importVar('use_for_final', true, true), '', 0);

        } else {

            $this->model = new CoursereportLms($_SESSION['idCourse'], $id_report, ['activity', 'scoitem'], '0');

            $info_report = $this->model->getCourseReports()[0];

            $report_score =& $report_man->getReportsScores($info_report->getIdReport());
        }

        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($info_report->getTitle())
        );
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . Form::openForm('activity', 'index.php?modname=coursereport&amp;op=modactivityscore')
        );

        // XXX: Save input if needed
        if (isset($_POST['save'])) {
            if ($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');
            $re_check = $report_man->checkActivityData($_POST);
            if (!$re_check['error']) {

                if (!$report_man->updateActivity($id_report, $_SESSION['idCourse'], $info_report)) {
                    $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
                } else {
                    // save user score modification
                    $query_upd_report = "
				UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
				SET weight = '" . $info_report->getWeight() . "',
					use_for_final = '" . $info_report->isUseForFinalToString() . "',
					show_to_user = '" . $info_report->isShowToUserToString() . "'
				WHERE id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '" . $id_report . "'";
                    $re = sql_query($query_upd_report);

                    $re = $report_man->saveReportScore($id_report, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);
                    Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($re ? 'ok' : 'err'));
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
            . Form::getHidden('id_source', 'id_source', $info_report->getIdSource())
            . Form::getHidden('source_of', 'source_of', $info_report->getSourceOf())
        );
        // for scorm object changing title, maxScore and requiredScore is not allowed
        switch ($info_report->getSourceOf()) {
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
                        strip_tags($info_report->getRequiredScore()))
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
                        $info_report->getRequiredScore())
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

        if ($info_report->getSourceOf() != 'scoitem') {
            /* XXX: scores */
            $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));
            $type_h = array('', 'align-center', 'align-center', '');
            $tb->setColsStyle($type_h);
            $cont_h = array($lang->def('_STUDENTS'),
                $lang->def('_SCORE'),
                $lang->def('_DATE'),
                $lang->def('_COMMENTS'));
            $tb->addHead($cont_h);

            // XXX: Display user scores
            $i = 0;
            while (list($idst_user, $user_info) = each($students_info)) {
                $user_name = ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
                    ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
                    : $acl_man->relativeId($user_info[ACL_INFO_USERID]));
                $cont = array(Form::getLabel('user_score_' . $idst_user, $user_name));

                $cont[] = Form::getInputTextfield('textfield_nowh',
                    'user_score_' . $idst_user,
                    'user_score[' . $idst_user . ']',
                    (isset($report_score[$id_report][$idst_user]['score'])
                        ? $report_score[$id_report][$idst_user]['score']
                        : (isset($_POST['user_score'][$idst_user]) ? $_POST['user_score'][$idst_user] : '')),
                    strip_tags($lang->def('_SCORE')),
                    '8',
                    ' tabindex="' . $i++ . '" ');
                $cont[] = Form::getInputDatefield('textfield_nowh',
                    'date_attempt_' . $idst_user,
                    'date_attempt[' . $idst_user . ']',
                    Format::date(
                        (isset($report_score[$id_report][$idst_user]['date_attempt'])
                            ? $report_score[$id_report][$idst_user]['date_attempt']
                            : (isset($_POST['date_attempt'][$idst_user]) ? $_POST['date_attempt'][$idst_user] : '')), 'date'));
                $cont[] = Form::getInputTextarea('comment_' . $idst_user,
                    'comment[' . $idst_user . ']',
                    (isset($report_score[$id_report][$idst_user]['comment'])
                        ? $report_score[$id_report][$idst_user]['comment']
                        : (isset($_POST['comment'][$idst_user]) ? stripslashes($_POST['comment'][$idst_user]) : '')),
                    'textarea_wh_full',
                    2);

                $tb->addBody($cont);
            }
        }

        $out->add(
            Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
        );
        if ($info_report->getSourceOf() != 'scoitem') {
            $out->add(
                $tb->getTable()
                . Form::openButtonSpace()
                . Form::getButton('save', 'save', $lang->def('_SAVE'))
                . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
                . Form::closeButtonSpace()
            );
        }
        $out->add(
            Form::closeForm()
            . '</div>'
        );

    }

    function delactivity()
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $acl_man = Docebo::user()->getAclManager();
        $report_man = new CourseReportManager();

        if (isset($_POST['confirm'])) {

            if (!$report_man->deleteReportScore($id_report)) {
                Util::jump_to('index.php?r=coursereport/coursereport&amp;result=err');
            }

            $re = $report_man->deleteReport($id_report);

            Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
        }

        // retirive activity info
        $query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	WHERE id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '" . $id_report . "'
			AND source_of = 'activity' AND id_source = '0'";
        $info_report = sql_fetch_assoc(sql_query($query_report));

        // XXX: Write in output
        $page_title = array(
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            $lang->def('_DEL') . ' : ' . strip_tags($info_report['title'])
        );
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . Form::openForm('delactivity', 'index.php?modname=coursereport&amp;op=delactivity')
            . Form::getHidden('id_report', 'id_report', $id_report)
            . getDeleteUi($lang->def('_AREYOUSURE'),
                $lang->def('_TITLE_ACT') . ' : ' . $info_report['title'],
                false,
                'confirm',
                'undo')
            . Form::closeForm()
            . '</div>');
    }

    function movereport($direction)
    {
        checkPerm('mod');

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');

        // XXX: Instance management
        $report_man = new CourseReportManager();

        list($seq) = sql_fetch_row(sql_query("
	SELECT sequence
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	WHERE id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '" . $id_report . "'"));

        if ($direction == 'left') {
            $re = sql_query("
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
		SET sequence = '" . $seq . "'
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND sequence = '" . ($seq - 1) . "'");
            $re &= sql_query("
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
		SET sequence = sequence - 1
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '" . $id_report . "'");

        }
        if ($direction == 'right') {
            $re = sql_query("
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
		SET sequence = '$seq'
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND sequence = '" . ($seq + 1) . "'");
            $re &= sql_query("
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
		SET sequence = sequence + 1
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '" . $id_report . "'");
        }

        Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    function export()
    {
        checkPerm('view');
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.table.php');

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');
        $included_test = array();
        $mod_perm = checkPerm('mod', true);
        $csv = '';

        $acl_man = Docebo::user()->getAclManager();
        $test_man = new GroupTestManagement();
        $report_man = new CourseReportManager();

        $org_tests =& $report_man->getTest();
        $tests_info = $test_man->getTestInfo($org_tests);

        $id_students =& $report_man->getStudentId();
        $students_info =& $acl_man->getUsers($id_students);

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

        $i = 0;
        $students_info = array();
        foreach ($students as $idst => $user_course_info)
            $students_info[$idst] =& $acl_man->getUser($idst, FALSE);

        $query_tot_report = "
	SELECT COUNT(*)
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	WHERE id_course = '" . $_SESSION['idCourse'] . "'";
        list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

        $query_tests = "
	SELECT id_report, id_source
	FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	WHERE id_course = '" . $_SESSION['idCourse'] . "' AND source_of = 'test'";
        $re_tests = sql_query($query_tests);
        while (list($id_r, $id_t) = sql_fetch_row($re_tests)) {
            $included_test[$id_t] = $id_t;
            $included_test_report_id[$id_r] = $id_r;
        }

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

        $img_mod = '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />';

        $cont_h[] = $lang->def('_DETAILS');
        $csv .= '"' . $lang->def('_DETAILS') . '"';

        $a_line_1 = array('');
        $a_line_2 = array('');
        $colums['max_score'] = array($lang->def('_MAX_SCORE'));
        $colums['required_score'] = array($lang->def('_REQUIRED_SCORE'));
        $colums['weight'] = array($lang->def('_WEIGHT'));
        $colums['show_to_user'] = array($lang->def('_SHOW_TO_USER'));
        $colums['use_for_final'] = array($lang->def('_USE_FOR_FINAL'));

        $this->model = new CoursereportLms($_SESSION['idCourse']);

        $total_weight = 0;
        $i = 1;
        foreach ($this->model->getCourseReports() as $info_report) {
            $id = $info_report->getIdSource();
            $reports[$info_report->getIdReport()] = $info_report;
            $reports_id[] = $info_report->getIdReport();

            // XXX: set action colums

            switch ($info_report->getSourceOf()) {
                case "test" : {

                    $title = strip_tags($tests_info[$info_report->getIdSource()]['title']);
                };
                    break;
                case "scoitem"    : {
                    $title = strip_tags($info_report['title']);
                };
                    break;
                case "activity"    : {
                    $title = strip_tags($info_report['title']);
                };
                    break;
                case "final_vote"    : {
                    $title = strip_tags($lang->def('_FINAL_SCORE'));
                };
                    break;
            }

            $top = $title;

            $cont_h[] = $top;
            $csv .= ';"' . $top . '"';
            $i++;

            //set info colums
            $colums['max_score'][] = $info_report['max_score'];
            $colums['required_score'][] = $info_report->getRequiredScore();
            $colums['weight'][] = $info_report['weight'];
            $colums['show_to_user'][] = ($info_report['show_to_user'] == 'true' ? $lang->def('_YES') : $lang->def('_NO'));
            $colums['use_for_final'][] = ($info_report['use_for_final'] == 'true' ? $lang->def('_YES') : $lang->def('_NO'));

            if ($info_report['use_for_final'] == 'true') $total_weight += $info_report['weight'];
        }

        $csv .= "\n";
        $first = true;
        foreach ($colums['max_score'] as $content)
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else
                $csv .= ';"' . $content . '"';

        $csv .= "\n";
        $first = true;
        foreach ($colums['required_score'] as $content)
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else
                $csv .= ';"' . $content . '"';

        $csv .= "\n";
        $first = true;
        foreach ($colums['weight'] as $content)
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else
                $csv .= ';"' . $content . '"';

        $csv .= "\n";
        $first = true;
        foreach ($colums['show_to_user'] as $content)
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else
                $csv .= ';"' . $content . '"';

        $csv .= "\n";
        $first = true;
        foreach ($colums['use_for_final'] as $content)
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else
                $csv .= ';"' . $content . '"';

        $csv .= "\n\n\n";
        $first = true;
        foreach ($cont_h as $content)
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else
                $csv .= ';"' . $content . '"';

        $csv .= "\n";

        $tests_score =& $test_man->getTestsScores($included_test, array_keys($students));

        $test_details = array();
        if (is_array($included_test)) {
            while (list($id_test, $users_result) = each($tests_score)) {
                while (list($id_user, $single_test) = each($users_result)) {
                    if ($single_test['score_status'] == 'valid') {
                        if (!isset($test_details[$id_test]['max_score']))
                            $test_details[$id_test]['max_score'] = $single_test['score'];
                        elseif ($single_test['score'] > $test_details[$id_test]['max_score'])
                            $test_details[$id_test]['max_score'] = $single_test['score'];

                        if (!isset($test_details[$id_test]['min_score']))
                            $test_details[$id_test]['min_score'] = $single_test['score'];
                        elseif ($single_test['score'] < $test_details[$id_test]['min_score'])
                            $test_details[$id_test]['min_score'] = $single_test['score'];

                        if (!isset($test_details[$id_test]['num_result']))
                            $test_details[$id_test]['num_result'] = 1;
                        else
                            $test_details[$id_test]['num_result']++;

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
        $reports_score =& $report_man->getReportsScores(
            (isset($included_test_report_id) && is_array($included_test_report_id) ? array_diff($reports_id, $included_test_report_id) : $reports_id));

        $report_details = array();
        while (list($id_report, $users_result) = each($reports_score)) {
            while (list($id_user, $single_report) = each($users_result)) {
                if ($single_report['score_status'] == 'valid') {
                    if (!isset($report_details[$id_report]['max_score']))
                        $report_details[$id_report]['max_score'] = $single_report['score'];
                    elseif ($single_report['score'] > $report_details[$id_report]['max_score'])
                        $report_details[$id_report]['max_score'] = $single_report['score'];

                    if (!isset($report_details[$id_report]['min_score']))
                        $report_details[$id_report]['min_score'] = $single_report['score'];
                    elseif ($single_report['score'] < $report_details[$id_report]['min_score'])
                        $report_details[$id_report]['min_score'] = $single_report['score'];

                    if (!isset($report_details[$id_report]['num_result']))
                        $report_details[$id_report]['num_result'] = 1;
                    else
                        $report_details[$id_report]['num_result']++;

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

        if (!empty($students_info))
            while (list($idst_user, $user_info) = each($students_info)) {
                $user_name = ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
                    ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
                    : $acl_man->relativeId($user_info[ACL_INFO_USERID]));
                $csv .= '"' . $user_name . '"';

                foreach ($this->model->getCourseReports() as $info_report) {

                    switch ($info_report->getSourceOf()) {
                        case "test" : {
                            $id_test = $info_report->getIdSource();
                            if (isset($tests_score[$id_test][$idst_user])) {
                                switch ($tests_score[$id_test][$idst_user]['score_status']) {
                                    case "not_complete" :
                                        $csv .= ';"-"';
                                        break;
                                    case "not_checked"    : {
                                        $csv .= ';"' . $lang->def('_NOT_CHECKED') . '"';

                                        if (!isset($test_details[$id_test]['not_checked'])) $test_details[$id_test]['not_checked'] = 1;
                                        else $test_details[$id_test]['not_checked']++;
                                    };
                                        break;
                                    case "passed"        : {
                                        $csv .= ';"' . $lang->def('_PASSED') . '"';
                                        if (!isset($test_details[$id_test]['passed'])) $test_details[$id_test]['passed'] = 1;
                                        else $test_details[$id_test]['passed']++;
                                    };
                                        break;
                                    case "not_passed"    : {
                                        $csv .= ';"' . $lang->def('_NOT_PASSED') . '"';
                                        if (!isset($test_details[$id_test]['not_passed'])) $test_details[$id_test]['not_passed'] = 1;
                                        else $test_details[$id_test]['not_passed']++;
                                    };
                                        break;
                                    case "valid"        : {
                                        $score = $tests_score[$id_test][$idst_user]['score'];

                                        if ($score >= $info_report->getRequiredScore()) {
                                            if ($score == $test_details[$id_test]['max_score']) $csv .= ';"' . $score . " " . $tt . '"';
                                            else $csv .= ';"' . $score . " " . $tt . '"';

                                            if (!isset($test_details[$id_test]['passed'])) $test_details[$id_test]['passed'] = 1;
                                            else $test_details[$id_test]['passed']++;
                                        } else {
                                            if ($score == $test_details[$id_test]['max_score']) $csv .= ';"' . $score . " " . $tt . '"';
                                            else $csv .= ';"' . $score . " " . $tt . '"';

                                            if (!isset($test_details[$id_test]['not_passed'])) $test_details[$id_test]['not_passed'] = 1;
                                            else $test_details[$id_test]['not_passed']++;
                                        }
                                        if (isset($test_details[$id_test]['varianza']) && isset($test_details[$id_test]['averange'])) {
                                            $test_details[$id_test]['varianza'] += pow(($tests_score[$id_test][$idst_user]['score'] - $test_details[$id_test]['averange']), 2);
                                        } else {
                                            $test_details[$id_test]['varianza'] = pow(($tests_score[$id_test][$idst_user]['score'] - $test_details[$id_test]['averange']), 2);
                                        }
                                    };
                                        break;
                                    default : {
                                        $csv .= ';"-"';
                                    }
                                }
                            }
                        }
                            break;
                        case
                        "scoitem" : {
                            $query_report = "
						SELECT *
						FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking
						WHERE idscorm_item = '" . $info_report->getIdSource() . "' AND idUser = '" . $idst_user . "'
						";
                            $report = sql_fetch_assoc(sql_query($query_report));
                            if ($report['score_raw'] == NULL) $report['score_raw'] = "-";

                            $id_track = (isset($report['idscorm_tracking']) ? $report['idscorm_tracking'] : 0);
                            $query_report = "
						SELECT *
						FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking_history
						WHERE idscorm_tracking = '" . $id_track . "'
						";

                            $query = sql_query($query_report);
                            $num = sql_num_rows($query);
                            $csv .= ';"' . $report['score_raw'] . '"';

                        }
                            break;
                        case "activity" :
                        case "final_vote" : {
                            if (isset($reports_score[$info_report->getIdReport()][$idst_user])) {
                                switch ($reports_score[$info_report->getIdReport()][$idst_user]['score_status']) {
                                    case "not_complete" :
                                        $csv .= ';"-"';
                                        break;
                                    case "valid"        : {
                                        if ($reports_score[$info_report->getIdReport()][$idst_user]['score'] >= $info_report->getRequiredScore()) {
                                            if ($reports_score[$info_report->getIdReport()][$idst_user]['score'] == $info_report->getMaxScore()) {
                                                $csv .= ';"' . $reports_score[$info_report->getIdReport()][$idst_user]['score'] . '"';
                                            } else $csv .= ';"' . $reports_score[$info_report->getIdReport()][$idst_user]['score'] . '"';

                                            // Count passed
                                            if (!isset($report_details[$info_report->getIdReport()]['passed'])) $report_details[$info_report->getIdReport()]['passed'] = 1;
                                            else $report_details[$info_report->getIdReport()]['passed']++;
                                        } else {
                                            $csv .= ';"' . $reports_score[$info_report->getIdReport()][$idst_user]['score'] . '"';

                                            // Count not passed
                                            if (!isset($report_details[$info_report->getIdReport()]['not_passed'])) $report_details[$info_report->getIdReport()]['not_passed'] = 1;
                                            else $report_details[$info_report->getIdReport()]['not_passed']++;
                                        }
                                        if (isset($report_details[$info_report->getIdReport()]['varianza']) && isset($report_details[$info_report->getIdReport()]['averange'])) {
                                            $report_details[$info_report->getIdReport()]['varianza'] += round(pow(($reports_score[$info_report->getIdReport()][$idst_user]['score'] - $report_details[$info_report->getIdReport()]['averange']), 2), 2);
                                        } else {
                                            $report_details[$info_report->getIdReport()]['varianza'] = round(pow(($reports_score[$info_report->getIdReport()][$idst_user]['score'] - $report_details[$info_report->getIdReport()]['averange']), 2), 2);
                                        }
                                    };
                                        break;
                                }
                            } else
                                $csv .= ';"-"';
                        };
                            break;
                    }
                }
                $csv .= "\n";
            }

        $file_name = date('YmdHis') . '_report_export.csv';

        require_once(_base_ . '/lib/lib.download.php');
        sendStrAsFile($csv, $file_name);
    }

    function testQuestion()
    {
        checkPerm('view');

        YuiLib::load(array('animation' => 'my_animation.js'));
        addJs($GLOBALS['where_lms_relative'] . '/modules/coursereport/', 'ajax.coursereport.js');

        require_once(_base_ . '/lib/lib.table.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');

        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        $out->add('<script type="text/javascript">'
// 			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/modules/coursereport/ajax.coursereport.php\'); '
// // 			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/ajax.server.php?id_quest=3&id_test=3\'); '
            . ' setup_coursereport(\'' . $GLOBALS['where_lms_relative'] . '/ajax.server.php?plf=lms&mn=coursereport&\'); '
            . '</script>', 'page_head');

        $id_test = importVar('id_test', true, 0);

        $test_man = new GroupTestManagement();

        $lev = FALSE;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $lev = $_GET['type_filter'];
        }
        $students = getSubscribed((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, false, false, true);
        $id_students = array_keys($students);

        $quests = array();
        $answers = array();
        $tracks = array();

        $test_info = $test_man->getTestInfo(array($id_test));

        $page_title = array('index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            $test_info[$id_test]['title']
        );

        $out->add(getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
        );

        $query_test = "SELECT title"
            . " FROM " . $GLOBALS['prefix_lms'] . "_test"
            . " WHERE idTest = '" . $id_test . "'";

        list($titolo_test) = sql_fetch_row(sql_query($query_test));

        $query_quest = "SELECT idQuest, type_quest, title_quest"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testquest"
            . " WHERE idTest = '" . $id_test . "'"
            . " ORDER BY sequence";

        $result_quest = sql_query($query_quest);

        while (list($id_quest, $type_quest, $title_quest) = sql_fetch_row($result_quest)) {
            $quests[$id_quest]['idQuest'] = $id_quest;
            $quests[$id_quest]['type_quest'] = $type_quest;
            $quests[$id_quest]['title_quest'] = $title_quest;

//		$query_answer =	"SELECT idAnswer, is_correct, answer"
//						." FROM ".$GLOBALS['prefix_lms']."_testquestanswer"
//						." WHERE idQuest = '".$id_quest."'"
//						." ORDER BY sequence";

            $query_answer = "SELECT tqa.idAnswer, tqa.is_correct, tqa.answer"
                . " FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer AS tqa"
                . " LEFT JOIN"
                . " " . $GLOBALS['prefix_lms'] . "_testtrack_answer tta ON tqa.idAnswer = tta.idAnswer"
                . " LEFT JOIN"
                . " " . $GLOBALS['prefix_lms'] . "_testtrack tt ON tt.idTrack = tta.idTrack"
                . " WHERE tqa.idQuest = '" . $id_quest . "'";
            $query_answer .= " and tt.idUser in (" . implode(",", $id_students) . ")";
            $query_answer .= " ORDER BY tqa.sequence";

            $result_answer = sql_query($query_answer);


            while (list($id_answer, $is_correct, $answer) = sql_fetch_row($result_answer)) {
                $answers[$id_quest][$id_answer]['idAnswer'] = $id_answer;
                $answers[$id_quest][$id_answer]['is_correct'] = $is_correct;
                $answers[$id_quest][$id_answer]['answer'] = $answer;
            }
            if ($type_quest == 'choice_multiple' || $type_quest == 'choice' || $type_quest == 'inline_choice') {
                $answers[$id_quest][0]['idAnswer'] = 0;
                $answers[$id_quest][0]['is_correct'] = 0;
                $answers[$id_quest][0]['answer'] = $lang->def('_NO_ANSWER');
            }
        }


        $query_track = "SELECT idTrack"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack"
            . " WHERE idTest = '" . $id_test . "'"
            . " AND score_status = 'valid'"
            . " AND idUser in (" . implode(",", $id_students) . ")";

        $result_track = sql_query($query_track);

        while (list($id_track) = sql_fetch_row($result_track)) {
            $query_track_answer = "SELECT idQuest, idAnswer, more_info"
                . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer"
                . " WHERE idTrack = '" . $id_track . "'";
// COMMENTATO MA NON E' CHIARO COME MAI C'E'????
            //." AND user_answer = 1";
//print_r($query_track_answer.'<br />');
            $result_track_answer = sql_query($query_track_answer);

//echo $query_track_answer."<br>";
            while (list($id_quest, $id_answer, $more_info) = sql_fetch_row($result_track_answer)) {
                $tracks[$id_track][$id_quest][$id_answer]['more_info'] = $more_info;
//echo " -> ".$id_quest." - ".$id_answer." - ".$more_info."<br>";
            }
        }

        $query_total_play = "SELECT COUNT(*)"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack"
            . " WHERE idTest = '" . $id_test . "'"
            . " AND score_status = 'valid'"
            . " AND idUser in (" . implode(",", $id_students) . ")";

        list($total_play) = sql_fetch_row(sql_query($query_total_play));

        /*if ($total_play == 0) {
                    $query_total_play =     "SELECT COUNT(*)"
                                                    ." FROM ".$GLOBALS['prefix_lms']."_testtrack"
                                                    ." WHERE idTest = '".$id_test."' AND score_status = 'not_checked'";
                    list($total_play2) = sql_fetch_row(sql_query($query_total_play));
    $total_play += $total_play2;

            }*/
//print_r($tracks);
        foreach ($quests as $quest) {
            switch ($quest['type_quest']) {
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

                    foreach ($answers[$quest['idQuest']] as $answer) {
                        $cont = array();

                        if ($answer['is_correct'])
                            $txt = '<img src="' . getPathImage('lms') . 'standard/publish.png" alt="' . $lang->def('_ANSWER_CORRECT') . '" title="' . $lang->def('_ANSWER_CORRECT') . '" align="left" /> ';
                        else
                            $txt = '';

                        $cont[] = '<p>' . $txt . ' ' . $answer['answer'] . '</p>';

                        $answer_given = 0;
                        reset($tracks);
                        $i = 0;
                        foreach ($tracks as $track) {
                            $i++;
                            if (isset($track[$quest['idQuest']][$answer['idAnswer']])) {
                                $answer_given++;
                            } elseif (!isset($track[$quest['idQuest']]) && $answer['idAnswer'] == 0) {
                                $answer_given++;
                            }
                        }
                        if ($answer['idAnswer'] == 0 && $i < $total_play) {
                            //			if ($i < $total_play) {
                            $answer_given = $answer_given + ($total_play - $i);
                        }
                        if ($total_play > 0)
                            $percentage = ($answer_given / $total_play) * 100;
                        else
                            $percentage = 0;

                        $percentage = number_format($percentage, 2);

                        $cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

                        $tb->addBody($cont);
                    }

                    $out->add($tb->getTable() . '<br/>');
                    break;

                case "upload":
                case "extended_text":
                    $out->add('<div>');
                    $out->add('<p><a href="#" onclick="getQuestDetail(' . $quest['idQuest'] . ', ' . $id_test . ', \'' . $quest['type_quest'] . '\'); return false;" id="more_quest_' . $quest['idQuest'] . '"><img src="' . getPathImage('fw') . 'standard/more.gif" alt="' . $lang->def('_MORE_INFO') . '" />' . str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST')) . '</a></p>');
                    $out->add('<p><a href="#" onclick="closeQuestDetail(' . $quest['idQuest'] . '); return false;" id="less_quest_' . $quest['idQuest'] . '" style="display:none"><img src="' . getPathImage('fw') . 'standard/less.gif" alt="' . $lang->def('_CLOSE') . '" />' . str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST')) . '</a></p>');
                    $out->add('</div>');
                    $out->add('<div id="quest_' . $quest['idQuest'] . '">');
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

                    foreach ($answers[$quest['idQuest']] as $answer) {
                        $cont = array();

                        $answer_correct = 0;

                        foreach ($tracks as $track) {
                            if ($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['answer'])
                                $answer_correct++;
                        }

                        $percentage = ($answer_correct / $total_play) * 100;

                        $percentage = number_format($percentage, 2);

                        $cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

                        $tb->addBody($cont);
                    }

                    $out->add($tb->getTable() . '<br/>');
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

                    foreach ($answers[$quest['idQuest']] as $answer) {
                        $cont = array();

                        $cont[] = $answer['answer'];

                        $answer_correct = 0;

                        foreach ($tracks as $track) {
                            if ($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['is_correct'])
                                $answer_correct++;
                        }

                        $percentage = ($answer_correct / $total_play) * 100;
                        echo "risp corrette: " . $answer_correct . " totale: " . $total_play;

                        $percentage = number_format($percentage, 2);

                        $cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

                        $tb->addBody($cont);
                    }

                    $out->add($tb->getTable() . '<br/>');
                    break;
            }

            reset($answers);
            reset($tracks);
        }

        $out->add('</div>');
    }


    function showchart()
    {
        require_once(_lms_ . '/modules/test/charts.test.php');

        $idTest = Get::req('id_test', DOTY_INT, -1);
        $idUser = Get::req('id_user', DOTY_INT, -1);
        $chartType = Get::req('chart_type', DOTY_STRING, 'column');

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');
        $acl_man = Docebo::user()->getAclManager();
        $user_info = $acl_man->getUser($idUser, false);
        list($title) = sql_fetch_row(sql_query("SELECT title FROM " . $GLOBALS['prefix_lms'] . "_test WHERE idTest=" . (int)$idTest));
        $backUrl = 'index.php?modname=coursereport&op=testvote&id_test=' . (int)$idTest;
        $backUi = getBackUi($backUrl, $lang->def('_BACK'));

        $page_title = array(
            'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            $backUrl => strip_tags($title),
            $acl_man->relativeId($user_info[ACL_INFO_USERID])
        );
        cout(getTitleArea($page_title, 'coursereport', $lang->def('_TH_ALT')));
        cout('<div class="stdblock">');
        cout($backUi);

        cout('<div><h2>' . $lang->def('_USER_DETAILS') . '</h2>');
        cout('<div class="form_line_l"><p><label class="floating">' . $lang->def('_USERNAME') . ':&nbsp;</label></p>' . $acl_man->relativeId($user_info[ACL_INFO_USERID]) . '</div>');
        cout('<div class="form_line_l"><p><label class="floating">' . $lang->def('_LASTNAME') . ':&nbsp;</label></p>' . $user_info[ACL_INFO_LASTNAME] . '</div>');
        cout('<div class="form_line_l"><p><label class="floating">' . $lang->def('_FIRSTNAME') . ':&nbsp;</label></p>' . $user_info[ACL_INFO_FIRSTNAME] . '</div>');
        cout('<div class="no_float"></div>');

        $charts = new Test_Charts($idTest, $idUser);
        $charts->render($chartType, true);

        cout($backUi);
        cout('</div>');
    }
}

?>