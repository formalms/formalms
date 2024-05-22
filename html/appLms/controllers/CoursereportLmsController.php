<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.json.php');
require_once \FormaLms\lib\Forma::inc(_adm_ . '/lib/lib.field.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');

class CoursereportLmsController extends LmsController
{
    private $baseUserFieldListArray;

    private $completeFieldListArray;

    protected  $courseReportManager;

    /** @var int */
    protected $idCourse;

    protected $json;

    protected $permissions;


    protected $model;

    public function init()
    {
        $this->idCourse = (int)$this->session->get('idCourse');

        $this->courseReportManager = new CourseReportManager($this->idCourse);
        /* @var Services_JSON json */
        $this->json = new Services_JSON();
        $this->_mvc_name = 'coursereport';
        $this->permissions = [
            'view' => true,
            'mod' => true,
        ];

        $this->baseUserFieldListArray = [
            'id' => Lang::t('_USER_ID', 'standard'),
            'userid' => Lang::t('_USERNAME', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'lastenter' => Lang::t('_DATE_LAST_ACCESS', 'profile'),
            'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
        ];

        $this->completeFieldListArray = $this->baseUserFieldListArray;

        $fman = new FieldList();
        $fields = $fman->getFlatAllFields(['framework', 'lms']);

        foreach ($fields as $key => $val) {
            $this->completeFieldListArray["$key"] = $val;
        }
    }

    public function coursereport()
    {
        checkPerm('view', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');

        $view_perm = checkPerm('view', true, $this->_mvc_name);
        $view_all_perm = checkPerm('view_all', true, $this->_mvc_name);
        $mod_perm = checkPerm('mod', true, $this->_mvc_name);
        $this->model = new CoursereportLms($this->idCourse);

        // XXX: Instance management
        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $testMan = new GroupTestManagement();
   

        $type_filter = FormaLms\lib\Get::pReq('type_filter', DOTY_MIXED, false);

        if ($type_filter == 'false') {
            $type_filter = false;
        }

        $students = getSubscribedInfo((int)$this->idCourse, false, $type_filter, true, false, false, true);
        $score = 0;
        //apply sub admin filters, if needed
        if (!$view_all_perm) {
            //filter users
            require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
            foreach ($students as $idst => $user_course_info) {
                if (!in_array($idst, $ctrl_users)) {
                    // Elimino gli studenti non amministrati
                    unset($students[$idst]);
                }
            }
        }

        $id_students = array_keys($students);
        $students_info = $aclMan->getUsers($id_students);

        $includedTest = $this->model->getSourcesId(CoursereportLms::SOURCE_OF_TEST);
        $reports_id = $this->model->getReportsId();
        $includedTestReportId = $this->model->getReportsId(CoursereportLms::SOURCE_OF_TEST);

        $courseReportTestScores = $testMan->getReportTestsScoresAndDetails($includedTest, $id_students);
        $testsScore = $courseReportTestScores['testScores'];
        $testDetails = $courseReportTestScores['testDetails'];

        $reportsScoresAndDetails = $this->courseReportManager->getReportsScoresAndDetails((isset($includedTestReportId) && is_array($includedTestReportId) ? array_diff($reports_id, $includedTestReportId) : $reports_id), $id_students);

        $reportsScores = $reportsScoresAndDetails['reportScores'];
        $reportDetails = $reportsScoresAndDetails['reportDetails'];

        $total_weight = 0;

        $tests = [];

        $testObjects = [];
        foreach ($includedTest as $idTest) {
            $testObjects[$idTest] = Learning_Test::load($idTest);
        }

        if (!empty($students_info)) {
            $resultsTest = [];

            foreach ($students_info as $idstUser => $userInfo) {
                foreach ($this->model->getCourseReports() as $infoReport) {
                    if ($infoReport->getSourceOf() != CoursereportLms::SOURCE_OF_FINAL_VOTE) {
                        switch ($infoReport->getSourceOf()) {
                            case CoursereportLms::SOURCE_OF_TEST:
                                $testObj = $testObjects[$infoReport->getIdSource()];

                                if (isset($testsScore[$infoReport->getIdSource()][$idstUser])) {
                                    $scoreStatus = $testsScore[$infoReport->getIdSource()][$idstUser]['score_status'];
                                    switch ($scoreStatus) {
                                        case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                                        case CoursereportLms::TEST_STATUS_NOT_CHECKED:
                                        case CoursereportLms::TEST_STATUS_NOT_PASSED:
                                        case CoursereportLms::TEST_STATUS_PASSED:
                                            if (!isset($testDetails[$infoReport->getIdSource()][$scoreStatus])) {
                                                $testDetails[$infoReport->getIdSource()][$scoreStatus] = 1;
                                            } else {
                                                ++$testDetails[$infoReport->getIdSource()][$scoreStatus];
                                            }

                                            break;
                                        case CoursereportLms::TEST_STATUS_DOING:
                                        case CoursereportLms::TEST_STATUS_VALID:
                                            $score = $testsScore[$infoReport->getIdSource()][$idstUser]['score'];

                                            if ($score >= $infoReport->getRequiredScore()) {
                                                if (!isset($testDetails[$infoReport->getIdSource()][CoursereportLms::TEST_STATUS_PASSED])) {
                                                    $testDetails[$infoReport->getIdSource()][CoursereportLms::TEST_STATUS_PASSED] = 1;
                                                } else {
                                                    ++$testDetails[$infoReport->getIdSource()][CoursereportLms::TEST_STATUS_PASSED];
                                                }
                                            } else {
                                                if (!isset($testDetails[$infoReport->getIdSource()][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                                    $testDetails[$infoReport->getIdSource()][CoursereportLms::TEST_STATUS_NOT_PASSED] = 1;
                                                } else {
                                                    ++$testDetails[$infoReport->getIdSource()][CoursereportLms::TEST_STATUS_NOT_PASSED];
                                                }
                                            }

                                            break;
                                    }
                                }
                                $resultsActivity[] = ['id' => CoursereportLms::SOURCE_OF_TEST . '_' . $infoReport->getIdSource(), 'name' => $testObj->getTitle()];
                                if ($infoReport->isUseForFinal()) {
                                    $resultsTest[] = $score * $infoReport->getWeight();
                                }

                                break;
                            case CoursereportLms::SOURCE_OF_SCOITEM:
                                break;
                            case CoursereportLms::SOURCE_OF_ACTIVITY:
                                if (isset($testsScore[$infoReport->getIdReport()][$idstUser])) {
                                    $scoreStatus = $testsScore[$infoReport->getIdReport()][$idstUser]['score_status'];
                                    switch ($scoreStatus) {
                                        case CoursereportLms::TEST_STATUS_PASSED:
                                        case CoursereportLms::TEST_STATUS_NOT_PASSED:
                                        case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                                        case CoursereportLms::TEST_STATUS_NOT_CHECKED:
                                            if (!isset($reportDetails[$infoReport->getIdReport()][$scoreStatus])) {
                                                $reportDetails[$infoReport->getIdReport()][$scoreStatus] = 1;
                                            } else {
                                                ++$reportDetails[$infoReport->getIdReport()][$scoreStatus];
                                            }

                                            break;
                                        case CoursereportLms::TEST_STATUS_DOING:
                                        case CoursereportLms::TEST_STATUS_VALID:
                                            $score = $testsScore[$infoReport->getIdReport()][$idstUser]['score'];

                                            if ($score >= $infoReport->getRequiredScore()) {
                                                if (!isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED])) {
                                                    $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED] = 1;
                                                } else {
                                                    ++$reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED];
                                                }
                                            } else {
                                                if (!isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                                    $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED] = 1;
                                                } else {
                                                    ++$reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED];
                                                }
                                            }

                                            break;
                                    }
                                }

                                break;
                            default:
                                $name = '';
                        }

                        if ($infoReport->isUseForFinal()) {
                            $total_weight += $infoReport->getWeight();
                        }
                    }
                }
            }

            $resultsActivity = [];

            foreach ($this->model->getCourseReports() as $infoReport) {
                if ($infoReport->getSourceOf() != CoursereportLms::SOURCE_OF_FINAL_VOTE) {
                    $passedLink = 'javascript:void(0)';
                    $passedLinkActive = false;
                    $notPassedLink = 'javascript:void(0)';
                    $notPassedLinkActive = false;
                    $notCheckedLink = 'javascript:void(0)';
                    $notCheckedLinkActive = false;

                    $chartLink = 'javascript:void(0)';
                    $chartLinkVisible = true;
                    $editLink = 'javascript:void(0)';
                    $editLinkVisible = true;
                    $trashLink = 'javascript:void(0)';
                    $trashLinkVisible = true;

                    $passed = '-';
                    $notPassed = '-';
                    $notChecked = '-';
                    $average = '-';
                    $maxScore = '-';
                    $minScore = '-';
                    $varianza = '-';

                    switch ($infoReport->getSourceOf()) {
                        case CoursereportLms::SOURCE_OF_TEST:
                            $id = $infoReport->getIdSource();
                            $testObj = $testObjects[$id];
                            $type = $testObj->getObjectType();
                            $name = $testObj->getTitle();

                            $resultsActivity[] = ['id' => $testObj->getObjectType() . '_' . $id, 'name' => $name];

                            if ($mod_perm) {
                                $chartLink = 'index.php?r=lms/coursereport/testQuestion&type_filter=' . $type_filter . '&id_test=' . $id;


                                $editLink = 'index.php?r=lms/coursereport/testvote&type_filter=' . $type_filter . '&id_test=' . $id;
                                $trashLinkVisible = false;
                            } elseif ($view_perm) {
                                $chartLink = 'index.php?r=lms/coursereport/testQuestion&type_filter=' . $type_filter . '&id_test=' . $id;

                                $trashLinkVisible = false;
                            }

                            if (isset($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED]) || isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED])) {
                                    $testDetails[$id][CoursereportLms::TEST_STATUS_PASSED] = 0;
                                }
                                if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                    $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED] = 0;
                                }

                                if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA])) {
                                    $testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA] = 0;
                                }

                                $totalTests = $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED] + $testDetails[$id][CoursereportLms::TEST_STATUS_PASSED];
                                if ($totalTests > 0) {

                                }

                                if (array_key_exists(CoursereportLms::TEST_STATUS_PASSED, $testDetails[$id]) && array_key_exists(CoursereportLms::TEST_STATUS_NOT_PASSED, $testDetails[$id])) {
                                    ($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED] + $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED]) > 0 ? $testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA] /= ($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED] + $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED]) : $testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA] = 0;


                                    $testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA] = array_key_exists(CoursereportLms::TEST_STATUS_VARIANZA, $testDetails[$id]) ? sqrt($testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA]) : 0;

                                }
                            }

                            $passed = (isset($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED]) ? round($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED], 2) : '-');
                            $notPassed = (isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED]) ? round($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED], 2) : '-');
                            $notChecked = (isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_CHECKED]) ? round($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_CHECKED], 2) : '-');
                            $average = (isset($testDetails[$id]['average']) ? round($testDetails[$id]['average'], 2) : '-');
                            $maxScore = (isset($testDetails[$id]['max_score']) ? round($testDetails[$id]['max_score'], 2) : '-');
                            $minScore = (isset($testDetails[$id]['min_score']) ? round($testDetails[$id]['min_score'], 2) : '-');

                            $eventResult = Events::trigger('lms.test.coursereport.coursereport', ['object_test' => $testObj, 'overViewTestQuestionLink' => $chartLink]);
                            $chartLink = $eventResult['overViewTestQuestionLink'];

                            break;
                        case CoursereportLms::SOURCE_OF_SCOITEM:
                            $id = $infoReport->getIdReport();
                            $name = strip_tags($infoReport->getTitle());
                            $type = $infoReport->getSourceOf();

                            if ($mod_perm) {
                                //$chartLink = 'index.php?modname=coursereport&op=testQuestion&type_filter=' . $type_filter . '&id_test=' . $id;
                                $chartLink = 'index.php?r=lms/coursereport/testQuestion&type_filter=' . $type_filter . '&id_report=' . $infoReport->getIdReport();
                                $chartLinkVisible = false;
                                $editLink = 'index.php?r=lms/coursereport/modactivityscore&type_filter=' . $type_filter . '&id_report=' . $infoReport->getIdReport() . '&source_of=' . $infoReport->getSourceOf() . '&id_source=' . $id;

                                $trashLink = 'index.php?r=lms/coursereport/delactivity&type_filter=' . $type_filter . '&id_report=' . $infoReport->getIdReport();
                            }

                            $scormItem = new ScormLms($id);

                            $resultsActivity[] = ['id' => $infoReport->getSourceOf() . '_' . $scormItem->getIdSource(), 'name' => $name];

                            $passed = $scormItem->getPassed() > 0 ? $scormItem->getPassed() : '-';
                            $notPassed = $scormItem->getNotPassed() > 0 ? $scormItem->getNotPassed() : '-';
                            $notChecked = $scormItem->getNotChecked() > 0 ? $scormItem->getNotChecked() : '-';
                            $average = $scormItem->getAverage();
                            $varianza = $scormItem->getVarianza();
                            $maxScore = $scormItem->getMaxScore();
                            $minScore = $scormItem->getMinScore();

                            break;
                        case CoursereportLms::SOURCE_OF_ACTIVITY:
                            $id = $infoReport->getIdReport();
                            $name = strip_tags($infoReport->getTitle());
                            $type = $infoReport->getSourceOf();

                            $resultsActivity[] = ['id' => $infoReport->getSourceOf() . '_' . $id, 'name' => $name];

                            if ($mod_perm) {
                                //$chartLink = 'index.php?modname=coursereport&op=testQuestion&type_filter=' . $type_filter . '&id_test=' . $id;
                                //$chartLink = 'index.php?r=lms/coursereport/testQuestion&type_filter=' . $type_filter . '&id_test=' . $id;
                                $chartLinkVisible = false;
                                $editLink = 'index.php?r=lms/coursereport/modactivityscore&type_filter=' . $type_filter . '&id_report=' . $infoReport->getIdReport();
                                $trashLink = 'index.php?r=lms/coursereport/delactivity&type_filter=' . $type_filter . '&id_report=' . $infoReport->getIdReport();
                            }

                            $passed = ((isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED]) && $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED] > 0) ? round($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED], 2) : '-');
                            $notPassed = ((isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED]) && $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED] > 0) ? round($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED], 2) : '-');
                            $notChecked = ((isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_CHECKED]) && $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_CHECKED] > 0) ? round($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_CHECKED], 2) : '-');
                            $average = (isset($reportDetails[$infoReport->getIdReport()]['average']) ? round($reportDetails[$infoReport->getIdReport()]['average'], 2) : '-');
                            $maxScore = (isset($reportDetails[$infoReport->getIdReport()]['max_score']) ? round($reportDetails[$infoReport->getIdReport()]['max_score'], 2) : '-');
                            $minScore = (isset($reportDetails[$infoReport->getIdReport()]['min_score']) ? round($reportDetails[$infoReport->getIdReport()]['min_score'], 2) : '-');

                            break;
                        default:
                    }

                    $test = [
                        'id' => $id,
                        'idReport' => $infoReport->getIdReport(),
                        'name' => $name,
                        'typeString' => ucfirst($type),
                        'type' => $type,
                        'max' => $infoReport->getMaxScore(),
                        'required' => $infoReport->getRequiredScore(),
                        'weight' => $infoReport->getWeight(),
                        'show' => $infoReport->isShowToUser(),
                        'final' => $infoReport->isUseForFinal(),
                        'showInDetail' => $infoReport->isShowInDetail(),
                        CoursereportLms::TEST_STATUS_PASSED => [
                            'value' => $passed,
                            'link' => $passedLink,
                            'visible' => true,
                            'active' => $passedLinkActive,
                        ],
                        CoursereportLms::TEST_STATUS_NOT_PASSED => [
                            'value' => $notPassed,
                            'link' => $notPassedLink,
                            'visible' => true,
                            'active' => $notPassedLinkActive,
                        ],
                        CoursereportLms::TEST_STATUS_NOT_CHECKED => [
                            'value' => $notChecked,
                            'link' => $notCheckedLink,
                            'visible' => true,
                            'active' => $notCheckedLinkActive,
                        ],
                        'average' => $average,
                        'max_score' => $maxScore,
                        'min_score' => $minScore,
                        CoursereportLms::TEST_STATUS_VARIANZA => $varianza,
                        'actions' => [
                            [
                                'icon' => 'bar-chart',
                                'link' => $chartLink,
                                'visible' => $chartLinkVisible,
                            ],
                            [
                                'icon' => 'edit',
                                'link' => $editLink,
                                'visible' => $editLinkVisible,
                            ],
                            [
                                'icon' => 'trash',
                                'link' => $trashLink,
                                'visible' => $trashLinkVisible,
                            ],
                        ],
                    ];

                    $tests[] = $test;
                }
            }

            $ajaxResponse = [
                'overview' => [
                    'tests' => $tests,
                ],
            ];
        }

        $ajaxResponse['details'] = [
            'activities' => $resultsActivity,
        ];

        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/appLms/views/coursereport/js/coursereport.js', true, true);
        Util::get_css(FormaLms\lib\Get::rel_path('base') . '/appLms/views/coursereport/css/coursereport.css', true, true);

        $this->render('coursereport', $ajaxResponse);
    }

    public function getDetailCourseReport() //ajax json
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.course.php');
        require_once \FormaLms\lib\Forma::inc(_adm_ . '/lib/lib.field.php');


        $redoFinal = FormaLms\lib\Get::pReq('redo_final', DOTY_MIXED, false);
        $roundReport = FormaLms\lib\Get::pReq('round_report', DOTY_MIXED, false);
        $roundTest = FormaLms\lib\Get::pReq('round_test', DOTY_MIXED, false);

        $currentPage = FormaLms\lib\Get::pReq('pagination', DOTY_INT, 0);
        /**
         * Set default students limit pagination at 50.
         **/
        $paginationLimit = FormaLms\lib\Get::pReq('limit', DOTY_INT, 50);

        if ($paginationLimit == 0) {
            $currentPage = 0;
        }

        if ($redoFinal && !$roundReport && !$roundTest) {
            $this->redofinal();
        }

        if ($roundReport && !$redoFinal && !$roundTest) {
            $this->roundreport($roundReport);
        }

        if ($roundTest && !$redoFinal && !$roundReport) {
            $this->roundtest($roundTest);
        }

        $this->model = new CoursereportLms($this->idCourse);

        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $testMan = new GroupTestManagement();
   

        $view_all_perm = checkPerm('view_all', true, $this->_mvc_name);
        $type_filter = FormaLms\lib\Get::pReq('type_filter', DOTY_MIXED, false);
        $edition_filter = FormaLms\lib\Get::pReq('edition_filter', DOTY_MIXED, false);


        if ($type_filter == 'false') {
            $type_filter = false;
        }

        if ($edition_filter == 'false') {
            $edition_filter = false;
        }

        $reportsArray = $this->model->getCourseReportsVisibleInDetail();

        $students = getSubscribedInfo((int)$this->idCourse, false, $type_filter, true, false, false, true, null, false, false, $edition_filter);

        if (!$view_all_perm) {
            //filter users
            require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
            foreach ($students as $idst => $user_course_info) {
                if (!in_array($idst, $ctrl_users)) {
                    // Elimino gli studenti non amministrati
                    unset($students[$idst]);
                }
            }
        }

        $pagesCount = ceil(count($students) / $paginationLimit);
        $nextPage = false;
        if ($currentPage + 1 < $pagesCount) {
            $nextPage = $currentPage + 1;
        }

        if ($paginationLimit > 0) {
            $offset = $currentPage * $paginationLimit;
            $length = $paginationLimit;

            $students = array_slice($students, $offset, $length, true);
        }

        $id_students = array_keys($students);
        $students_info = $aclMan->getUsers($id_students);

        $reportsArrayTest = $this->model->getReportsFilteredBySourceOf(CoursereportLms::SOURCE_OF_TEST);

        foreach ($reportsArrayTest as $reportLms) {
            $idSource = $reportLms->getIdSource();
            $idReport = $reportLms->getIdSource();
            $includedTest[$idSource] = $idSource;
            $includedTestReportId[$idReport] = $idReport;
        }

        $testsScore = $testMan->getTestsScores($includedTest, $id_students);

        $testDetails = [];

        if (is_array($includedTest)) {
            foreach ($testsScore as $idTest => $users_result) {
                foreach ($users_result as $idUser => $single_test) {
                    if ($single_test['score_status'] == 'valid') {
                        // max
                        if (!isset($testDetails[$idTest]['max_score'])) {
                            $testDetails[$idTest]['max_score'] = $single_test['score'];
                        } elseif ($single_test['score'] > $testDetails[$idTest]['max_score']) {
                            $testDetails[$idTest]['max_score'] = $single_test['score'];
                        }

                        // min
                        if (!isset($testDetails[$idTest]['min_score'])) {
                            $testDetails[$idTest]['min_score'] = $single_test['score'];
                        } elseif ($single_test['score'] < $testDetails[$idTest]['min_score']) {
                            $testDetails[$idTest]['min_score'] = $single_test['score'];
                        }

                        //number of valid score
                        if (!isset($testDetails[$idTest]['num_result'])) {
                            $testDetails[$idTest]['num_result'] = 1;
                        } else {
                            ++$testDetails[$idTest]['num_result'];
                        }

                        // average
                        if (!isset($testDetails[$idTest]['average'])) {
                            $testDetails[$idTest]['average'] = $single_test['score'];
                        } else {
                            $testDetails[$idTest]['average'] += $single_test['score'];
                        }
                    }
                }
            }
            foreach ($testDetails as $idTest => $single_detail) {
                if (isset($single_detail['num_result'])) {
                    $testDetails[$idTest]['average'] /= $testDetails[$idTest]['num_result'];
                }
            }
            reset($testDetails);
        }

        foreach ($reportsArray as $infoReport) {
            $reports_id[] = $infoReport->getIdReport();
        }
        $reports_score = $this->courseReportManager->getReportsScores((isset($includedTestReportId) && is_array($includedTestReportId) ? array_diff($reports_id, $includedTestReportId) : $reports_id), $id_students);

        $results_names = [];
  
        $students_array = [];

        if (!empty($students_info)) {
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/learning.test.php');

            foreach ($students_info as $idstUser => $userInfo) {
                $user_name = ($userInfo[ACL_INFO_LASTNAME] . $userInfo[ACL_INFO_FIRSTNAME]
                    ? $userInfo[ACL_INFO_LASTNAME] . ' ' . $userInfo[ACL_INFO_FIRSTNAME]
                    : $aclMan->relativeId($userInfo[ACL_INFO_USERID]));

                $student = [];
                $student['name'] = $user_name;

                $fman = new FieldList();

                foreach ($this->completeFieldListArray as $key => $translate) {
                    switch ($key) {
                        case 'id':
                            $studendVal = $userInfo[ACL_INFO_IDST];
                            break;
                        case 'userid':
                            $studendVal = str_replace('/', '', $userInfo[ACL_INFO_USERID]);
                            break;
                        case 'firstname':
                            $studendVal = $userInfo[ACL_INFO_FIRSTNAME];
                            break;
                        case 'lastname':
                            $studendVal = $userInfo[ACL_INFO_LASTNAME];
                            break;
                        case 'email':
                            $studendVal = $userInfo[ACL_INFO_EMAIL];
                            break;
                        case 'register_date':
                            $studendVal = $userInfo[ACL_INFO_REGISTER_DATE];
                            break;
                        case 'lastenter':
                            $studendVal = $userInfo[ACL_INFO_LASTENTER];
                            break;
                        default:
                            $fieldEntries = $fman->getUsersFieldEntryData($userInfo[ACL_INFO_IDST], $key, true);

                            $studendVal = $fieldEntries[$userInfo[0]][$key];
                            break;
                    }

                    $student[$key] = $studendVal;
                }

                $student['activities_results'] = [];
                $student['total_result'] = '-';
                $event = [
                    'object_test' => $testObj,
                    'info_report' => $infoReport,
                    'idst_user' => $idstUser,
                    'test_score' => $testsScore,
                    'values' => [],
                ];

                foreach ($reportsArray as $infoReport) {
                    if ($infoReport->getSourceOf() != CoursereportLms::SOURCE_OF_FINAL_VOTE) {
                        $id = $infoReport->getIdSource();
                        $testObj = Learning_Test::load($id);

                        $courseReportDetailValues = [];

                        switch ($infoReport->getSourceOf()) {
                            case CoursereportLms::SOURCE_OF_TEST:
                                $key = sprintf('%s_%s_%s', $infoReport->getSourceOf(), $id, $testObj->getTitle());

                                if (!in_array($key, $results_names)) {
                                    $results_names[$key] = $testObj->getTitle();
                                }

                                if (isset($testsScore[$id][$idstUser])) {
                                    switch ($testsScore[$id][$idstUser]['score_status']) {
                                        case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                                        case CoursereportLms::TEST_STATUS_NOT_CHECKED:
                                        case CoursereportLms::TEST_STATUS_NOT_PASSED:
                                        case CoursereportLms::TEST_STATUS_PASSED:
                                            $value = [
                                                'icon' => '',
                                                'showIcon' => false,
                                                'value' => '-',
                                                'link' => 'javascript:void(0)',
                                                'active' => false,
                                            ];


                                            $courseReportDetailValues[] = $value;

                                            break;
                                        case CoursereportLms::TEST_STATUS_DOING:
                                        case CoursereportLms::TEST_STATUS_VALID:
                                            $score = $testsScore[$id][$idstUser]['score'];

                                            if ($score >= $infoReport->getRequiredScore()) {
                                                if ($score == $testDetails[$id]['max_score']) {
                                                    $value = [
                                                        'icon' => 'cr_max_score',
                                                        'showIcon' => false,
                                                        'value' => $score,
                                                        'link' => 'javascript:void(0)',
                                                        'active' => false,
                                                    ];

                                                    $courseReportDetailValues[] = $value;

                                                    $value = [
                                                        'icon' => 'cr_max_score',
                                                        'showIcon' => false,
                                                        'value' => '(' . $testsScore[$id][$idstUser]['times'] . ')',
                                                        'link' => 'index.php?r=lms/coursereport/testreport&idTest=' . $testsScore[$id][$idstUser]['idTest'] . '&idTrack=' . $testsScore[$id][$idstUser]['idTrack'] . '&testName=' . $testObj->getTitle() . '&studentName=' . $aclMan->relativeId($userInfo[ACL_INFO_USERID]),
                                                        'active' => true,
                                                    ];

                                                    $courseReportDetailValues[] = $value;
                                                } else {
                                                    $value = [
                                                        'icon' => 'cr_max_score',
                                                        'showIcon' => false,
                                                        'value' => $score,
                                                        'link' => 'javascript:void(0)',
                                                        'active' => false,
                                                    ];


                                                    $courseReportDetailValues[] = $value;

                                                    $value = [
                                                        'icon' => '',
                                                        'showIcon' => false,
                                                        'value' => '(' . $testsScore[$id][$idstUser]['times'] . ')',
                                                        'link' => 'index.php?r=lms/coursereport/testreport&idTest=' . $testsScore[$id][$idstUser]['idTest'] . '&idTrack=' . $testsScore[$id][$idstUser]['idTrack'] . '&testName=' . $testObj->getTitle() . '&studentName=' . $aclMan->relativeId($userInfo[ACL_INFO_USERID]),
                                                        'active' => true,
                                                    ];


                                                    $courseReportDetailValues[] = $value;
                                                }
                                            } else {
                                                if ($score == $testDetails[$idTest]['max_score']) {
                                                    $value = [
                                                        'icon' => 'cr_max_score cr_not_passed',
                                                        'showIcon' => false,
                                                        'value' => $score,
                                                        'link' => 'javascript:void(0)',
                                                        'active' => false,
                                                    ];


                                                    $courseReportDetailValues[] = $value;

                                                    $value = [
                                                        'icon' => 'cr_max_score cr_not_passed',
                                                        'showIcon' => false,
                                                        'value' => '(' . $testsScore[$id][$idstUser]['times'] . ')',
                                                        'link' => 'index.php?r=lms/coursereport/testreport&idTest=' . $testsScore[$id][$idstUser]['idTest'] . '&idTrack=' . $testsScore[$id][$idstUser]['idTrack'] . '&testName=' . $testObj->getTitle() . '&studentName=' . $aclMan->relativeId($userInfo[ACL_INFO_USERID]),
                                                        'active' => true,
                                                    ];


                                                    $courseReportDetailValues[] = $value;
                                                } else {
                                                    $value = [
                                                        'icon' => 'cr_not_passed',
                                                        'showIcon' => false,
                                                        'value' => $score,
                                                        'link' => 'javascript:void(0)',
                                                        'active' => false,
                                                    ];


                                                    $courseReportDetailValues[] = $value;

                                                    $value = [
                                                        'icon' => 'cr_not_passed',
                                                        'showIcon' => false,
                                                        'value' => '(' . $testsScore[$id][$idstUser]['times'] . ')',
                                                        'link' => 'index.php?r=lms/coursereport/testreport&idTest=' . $testsScore[$id][$idstUser]['idTest'] . '&idTrack=' . $testsScore[$id][$idstUser]['idTrack'] . '&testName=' . $testObj->getTitle() . '&studentName=' . $aclMan->relativeId($userInfo[ACL_INFO_USERID]),
                                                        'active' => true,
                                                    ];


                                                    $courseReportDetailValues[] = $value;
                                                }
                                            }

                                            break;
                                        default:
                                            $value = [
                                                'icon' => '',
                                                'showIcon' => false,
                                                'value' => '-',
                                                'link' => 'javascript:void(0)',
                                                'active' => false,
                                            ];


                                            $courseReportDetailValues[] = $value;
                                    }
                                } else {
                                    $value = [
                                        'icon' => '',
                                        'showIcon' => false,
                                        'value' => '-',
                                        'link' => 'javascript:void(0)',
                                        'active' => false,
                                    ];


                                    $courseReportDetailValues[] = $value;
                                }

                                $student['activities_results'][] = $courseReportDetailValues;

                                break;
                            case CoursereportLms::SOURCE_OF_SCOITEM:
                                $scormItem = new ScormLms($id, $idstUser);

                                $key = sprintf('%s_%s_%s', $infoReport->getSourceOf(), $id, $infoReport->getTitle());

                                if (!in_array($key, $results_names)) {
                                    $results_names[$key] = $infoReport->getTitle();
                                }

                                $value = [
                                    'icon' => 'cr_not_check',
                                    'showIcon' => false,
                                    'value' => $scormItem->getScoreRaw(),
                                    'link' => 'javascript:void(0)',
                                    'active' => false,
                                ];


                                $courseReportDetailValues[] = $value;

                                $history = $scormItem->getHistory();

                                if ($history > 0) {
                                    $value = [
                                        'icon' => 'cr_not_check',
                                        'showIcon' => false,
                                        'value' => '(' . $history . ')',
                                        'link' => 'index.php?r=lms/coursereport/scormreport&idTest=' . $scormItem->getIdTrack(),
                                        'active' => true,
                                    ];


                                    $courseReportDetailValues[] = $value;
                                }
                                Events::trigger('lms.coursereport.detail', $event);

                                if (count($event['values'])) {
                                    $courseReportDetailValues = array_merge($event['values'], $courseReportDetailValues);
                                }
                                $student['activities_results'][] = $courseReportDetailValues;

                                break;
                            case CoursereportLms::SOURCE_OF_ACTIVITY:
                                $key = sprintf('%s_%s_%s', $infoReport->getSourceOf(), $infoReport->getIdReport(), $infoReport->getTitle());

                                if (!in_array($key, $results_names)) {
                                    $results_names[$key] = $infoReport->getTitle();
                                }

                                if (isset($reports_score[$infoReport->getIdReport()][$idstUser])) {
                                    switch ($reports_score[$infoReport->getIdReport()][$idstUser]['score_status']) {
                                        case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                                            $value = [
                                                'icon' => '',
                                                'showIcon' => false,
                                                'value' => '-',
                                                'link' => 'javascript:void(0)',
                                                'active' => false,
                                            ];


                                            $courseReportDetailValues[] = $value;

                                            break;
                                        case CoursereportLms::TEST_STATUS_VALID:
                                            $score = $reports_score[$infoReport->getIdReport()][$idstUser]['score'];
                                            if ($score >= $infoReport->getRequiredScore()) {
                                                if ($score == $infoReport->getMaxScore()) {
                                                    $value = [
                                                        'icon' => 'cr_max_score',
                                                        'showIcon' => false,
                                                        'value' => $score,
                                                        'link' => 'javascript:void(0)',
                                                        'active' => false,
                                                    ];


                                                    $courseReportDetailValues[] = $value;
                                                } else {
                                                    $value = [
                                                        'icon' => '',
                                                        'showIcon' => false,
                                                        'value' => $score,
                                                        'link' => 'javascript:void(0)',
                                                        'active' => false,
                                                    ];


                                                    $courseReportDetailValues[] = $value;
                                                }
                                            } else {
                                                $value = [
                                                    'icon' => 'cr_not_passed',
                                                    'showIcon' => false,
                                                    'value' => $score,
                                                    'link' => 'javascript:void(0)',
                                                    'active' => false,
                                                ];


                                                $courseReportDetailValues[] = $value;
                                            }

                                            break;
                                        default:
                                            $value = [
                                                'icon' => '',
                                                'showIcon' => false,
                                                'value' => '-',
                                                'link' => 'javascript:void(0)',
                                                'active' => false,
                                            ];


                                            $courseReportDetailValues[] = $value;
                                    }
                                } else {
                                    $value = [
                                        'icon' => 'cr_not_passed',
                                        'showIcon' => false,
                                        'value' => '-',
                                        'link' => 'javascript:void(0)',
                                        'active' => false,
                                    ];


                                    $courseReportDetailValues[] = $value;
                                }
                                Events::trigger('lms.coursereport.detail', $event);

                                if (count($event['values'])) {
                                    $courseReportDetailValues = array_merge($event['values'], $courseReportDetailValues);
                                }
                                $student['activities_results'][] = $courseReportDetailValues;

                                break;
                            default:
                        }
                    }
                }

                $students_array[] = $student;
            }
        }
        $info_final = $this->model->getReportsFilteredBySourceOf(CoursereportLms::SOURCE_OF_FINAL_VOTE);

        $reports = $this->model->getReportsForFinal();

        $sumMaxScore = 0;
        $includedTest = [];
        $otherSource = [];
        $scormSource = [];

        foreach ($reports as $infoReport) {
            $sumMaxScore += $infoReport->getMaxScore() * $infoReport->getWeight();

            switch ($infoReport->getSourceOf()) {
                case CoursereportLms::SOURCE_OF_ACTIVITY:
                    $otherSource[$infoReport->getIdReport()] = $infoReport->getIdReport();
                    break;
                case CoursereportLms::SOURCE_OF_TEST:
                    $included_test[$infoReport->getIdSource()] = $infoReport->getIdSource();
                    break;
                case CoursereportLms::SOURCE_OF_SCOITEM:
                    $scormSource[$infoReport->getIdSource()] = $infoReport->getIdSource();
                    break;
            }
        }
        // XXX: Retrive Test score
        if (!empty($includedTest)) {
            $testsScore = $testMan->getTestsScores($includedTest, $id_students);
        }

        // XXX: Retrive other score
        if (!empty($otherSource)) {
            $otherScore = $this->courseReportManager->getReportsScores($otherSource);
        }

        $finalScore = [];

        foreach ($id_students as $idUser) {
            $userScore = 0;
            $sumMaxScoreScorm = 0;

            foreach ($reports as $infoReport) {
                switch ($infoReport->getSourceOf()) {
                    case CoursereportLms::SOURCE_OF_ACTIVITY:
                        if (isset($otherScore[$infoReport->getIdReport()][$idUser]) && ($otherScore[$infoReport->getIdReport()][$idUser]['score_status'] === CoursereportLms::TEST_STATUS_VALID)) {
                            $userScore += ($otherScore[$infoReport->getIdReport()][$idUser]['score'] * $infoReport->getWeight());
                        }

                        break;
                    case CoursereportLms::SOURCE_OF_TEST:
                        if (isset($testsScore[$infoReport->getIdSource()][$idUser]) && ($testsScore[$infoReport->getIdSource()][$idUser]['score_status'] === CoursereportLms::TEST_STATUS_VALID)) {
                            $userScore += ($testsScore[$infoReport->getIdSource()][$idUser]['score'] * $infoReport->getWeight());
                        }

                        break;
                    case CoursereportLms::SOURCE_OF_SCOITEM:
                        $idscormItem = $infoReport->getIdSource();
                        $query = sql_query("SELECT score_raw, score_max FROM %lms_scorm_tracking WHERE idscorm_item = $idscormItem AND idUser = $idUser");
                        if ($result = sql_fetch_object($query)) {
                            $sumMaxScoreScorm += $result->score_max * $infoReport->getWeight();
                            $userScore += $result->score_raw * $infoReport->getWeight();
                        }

                        break;
                }
            }

            // user final score
            if (($sumMaxScore + $sumMaxScoreScorm) > 0) {
                $finalScore[$idUser] = round(($userScore / ($sumMaxScore + $sumMaxScoreScorm)) * $info_final[0]->getMaxScore(), 2);
                $sql = "
					SELECT score FROM %lms_coursereport_score
					WHERE id_user = $idUser 
					AND id_report = " . $info_final[0]->getIdReport() . '
					ORDER BY date_attempt DESC LIMIT 1
				';
                $q = sql_query($sql);

                [$score] = sql_fetch_array($q);

                if (FormaLms\lib\Get::req('round_report') || FormaLms\lib\Get::req('redo_final') || !$score) {
                  
                    $usersScores = [$idUser => $finalScore[$idUser]];
                    $this->courseReportManager->saveReportScore($info_final[0]->getIdReport(), $usersScores, [$idUser => date('d-m-Y H:i:s')], '');
                } elseif ($score && $finalScore[$idUser] != $score) {
                    $finalScore[$idUser] .= ' (' . (float)$score . ')';
                }
            } else {
                $finalScore[$idUser] = 0;
            }
        }

        foreach ($students_array as $k => $student) {
            if ($finalScore[$student['id']] && $finalScore[$student['id']] > 0) {
                $student['total_result'] = $finalScore[$student['id']];

                $students_array[$k] = $student;
            }
        }

        //retrieve edition
        $query = 'SELECT * FROM %lms_course_date WHERE id_course = ' . (int)$this->idCourse;
        $res = sql_query($query);

        //is there more any edition ?
        if (sql_num_rows($res) > 0) {
            $lang = FormaLanguage::createInstance('stats', 'lms');
            $arr_editions[] = $lang->def('_FILTEREDITIONSELECTONEOPTION');

            //list of editions for the dropdown, in the format: "[code] name (date_begin - date_end)"
            foreach ($res as $einfo) {
                $_label = '';
                if ($einfo['code'] != '') {
                    $_label .= '[' . $einfo['code'] . '] ';
                }
                if ($einfo['name'] != '') {
                    $_label .= $einfo['name'];
                }
                if (($einfo['sub_start_date'] != '' || $einfo['sub_start_date']) && ($einfo['sub_end_date'] != '' || $einfo['sub_end_date'])) {
                    $_label .= ' (' . Format::date($einfo['sub_start_date'], 'date') . ' - ' . Format::date($einfo['sub_end_date'], 'date') . ')';
                }
                if ($_label == '') {
                    //...
                }
                $arr_editions[$einfo['id_date']] = $_label;
            }
        }

        $resposeArray = [
            'names' => $results_names,
            'details' => [
                'editions' => $arr_editions,
                'students' => $students_array,
                'redo-final' => ['idReport' => $info_final[0]->getIdReport()],
                'round-report' => ['idReport' => $info_final[0]->getIdReport()],
                'edit-final' => ['idReport' => $info_final[0]->getIdReport(), 'link' => 'index.php?r=lms/coursereport/finalvote&type_filter=&id_report=' . $info_final[0]->getIdReport()],
            ],
            'pagination' => [
                'currentPage' => $currentPage,
                'nextPage' => $nextPage,
                'currentPaginationLimit' => $paginationLimit,
                'countPages' => $pagesCount,
            ],
        ];

        echo $this->json->encode($resposeArray);
    }

    /**
     * Restituisce i campi utente.
     */
    public function getUserFieldsSelector() //array associativo
    {
        echo $this->json->encode($this->completeFieldListArray);
    }

    public function setVisibleInDetail()
    {
        $idReport = FormaLms\lib\Get::pReq('idReport');
        $show_in_detail = FormaLms\lib\Get::pReq('showInDetail', DOTY_INT, 0);

        $report = new ReportLms($idReport);
        $report->setShowInDetail(($show_in_detail === 1 ? true : false));

        $result = $report->updateShowInDetail();

        if (!$result) {
            $response = [
                'status' => 500,
                'error' => sql_error(),
            ];
        } else {
            $response = [
                'status' => 200,
            ];
        }

        echo $this->json->encode($response);
    }

    public function testreport()
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');

        $idTrack = FormaLms\lib\Get::gReq('idTrack');
        $idTest = FormaLms\lib\Get::gReq('idTest');

        $testName = FormaLms\lib\Get::gReq('testName');

        $studentName = FormaLms\lib\Get::gReq('studentName');

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');
        $query_testreport = "
        SELECT DATE_FORMAT(tt.date_attempt, '%d/%m/%Y %H:%i'), tt.score, tt.idTest, t.idUser, tt.number_time
        FROM %lms_testtrack_times AS tt
        LEFT JOIN %lms_testtrack AS t ON tt.idTrack=t.idTrack
        WHERE tt.idTrack = '" . $idTrack . "' AND tt.idTest = '" . $idTest . "' ORDER BY tt.date_attempt";
        $re_testreport = sql_query($query_testreport);

        $testMan = new GroupTestManagement();
        $test_info = current($testMan->getTestInfo([$idTest]));
        $retainAnswersHistory = (bool)$test_info['retain_answers_history'];

        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_TH_TEST_REPORT'),
            strip_tags($testName),
        ];
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . getBackUi('javascript:history.go(-1)', Lang::t('_BACK', 'standard'))
        );

        $tb = new Table(0, $testName . ' : ' . $studentName);

        $tableHeaderArray = [
            'N.',
            $lang->def('_DATE'),
            $lang->def('_SCORE'),
            $lang->def('_STATISTICS'),
            $lang->def('_DELETE'),
        ];

        $tb->addHead($tableHeaderArray, ['min-cell', '', '']);

        $i = 1;
        foreach ($re_testreport as $row) {
            [$date_attempt, $score, $idTest, $idUser, $number_time] = array_values($row);
            $tableBodyArray = [
                $i++,
                $date_attempt,
                $score,
                $retainAnswersHistory ? '<a class="ico-sprite subs_chart" href="index.php?r=lms/coursereport/testreview&id_test=' . $idTest . '&id_user=' . $idUser . '&number_time=' . $number_time . '&idTrack=' . $idTrack . '"><span>' . $lang->def('_STATISTICS') . '</span></a>' : '',
                '<a class="ico-sprite subs_del" href="index.php?r=lms/coursereport/testreview&delete_track=' . md5($idTest . '_' . $idUser . '_' . $number_time) . '&id_test=' . $idTest . '&id_user=' . $idUser . '&number_time=' . $number_time . '&idTrack=' . $idTrack . '"><span>' . $lang->def('_DELETE') . '</span></a>',
            ];

            $tb->addBody($tableBodyArray);
        }

        $out->add(
            $tb->getTable()
            . '</div>',
            'content'
        );
    }

    public function scormreport()
    {
        $idTest = FormaLms\lib\Get::gReq('idTest');
        checkPerm('view', true, $this->_mvc_name);
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');
        $query_testreport = "
        SELECT DATE_FORMAT(date_action, '%d/%m/%Y %H:%i'), score_raw
        FROM %lms_scorm_tracking_history
        WHERE idscorm_tracking = " . $idTest . ' ORDER BY date_action';
        $re_testreport = sql_query($query_testreport);

        $testMan = new GroupTestManagement();
        $test_info = current($testMan->getTestInfo([$idTest]));
        $retainAnswersHistory = (bool)$test_info['retain_answers_history'];

        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_TH_TEST_REPORT'),
            strip_tags($testName),
        ];
        $out->add(getTitleArea($page_title, 'coursereport') . '<div class="std_block">' . getBackUi('javascript:history.go(-1)', Lang::t('_BACK', 'standard')));
        $tb = new Table(0, $testName . ' : ' . $studentName);
        $tb->addHead([
            'N.',
            $lang->def('_DATE'),
            $lang->def('_SCORE'),
        ], ['min-cell', '', '']);

        $i = 1;
        foreach ($re_testreport as $row) {
            [$date_attempt, $score] = array_values($row);
            $tb->addBody([$i++, $date_attempt, $score]);
        }
        $out->add($tb->getTable() . '</div>', 'content');
    }

    public function saveTestUpdate($idTest, &$testMan)
    {
        // Save report modification
        if (isset($_POST['user_score'])) {
            $query_upd_report = "
			UPDATE %lms_coursereport SET weight = '" . $_POST['weight'] . "',
				show_to_user = '" . $_POST['show_to_user'] . "',
				use_for_final = '" . $_POST['use_for_final'] . "'"
                . (isset($_POST['max_score']) && $_POST['max_score'] > 0 ? ", max_score = '" . (float)$_POST['max_score'] . "'" : '')
                . " WHERE  id_course = '" . $this->idCourse . "' AND id_source = '" . $idTest . "' AND source_of = '" . CoursereportLms::SOURCE_OF_TEST . "'";
            $re = sql_query($query_upd_report);

            // save user score modification
            $re &= $testMan->saveTestUsersScores($idTest, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);
        } else {
            $query_upd_report = "
			UPDATE %lms_coursereport
			SET weight = '" . $_POST['weight'] . "',
				show_to_user = '" . $_POST['show_to_user'] . "',
				use_for_final = '" . $_POST['use_for_final'] . "'"
                . (isset($_POST['max_score']) && $_POST['max_score'] > 0 ? ", max_score = '" . (float)$_POST['max_score'] . "'" : '')
                . " WHERE  id_course = '" . $this->idCourse . "' AND id_source = '" . $idTest . "' AND source_of = '" . CoursereportLms::SOURCE_OF_TEST . "'";
            $re = sql_query($query_upd_report);
        }

        return $re;
    }

    public function testvote()
    {
        if (isset($_POST['view_answer'])) {
            $this->testreview();

            return;
        }
        checkPerm('mod', true, $this->_mvc_name);
        $undo = FormaLms\lib\Get::pReq('undo', DOTY_MIXED, false);

        if ($undo) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.json.php');

        // XXX: Initializaing
        $idTest = importVar('id_test', true, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $testMan = new GroupTestManagement();
  

        // XXX: Find students
        $type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int)$this->idCourse, false, $lev, true, false, false, true);
        $id_students = array_keys($students);
        $students_info = &$aclMan->getUsers($id_students);

        // XXX: Find test
        $test_info = &$testMan->getTestInfo([$idTest]);

        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($test_info[$idTest]['title']),
        ];
        $GLOBALS['page']->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">',
            'content'
        );
        //==========================================================================================
        // XXX: Reset track of user
        if (isset($_POST['reset_track'])) {
            $re = $this->saveTestUpdate($idTest, $testMan);
            $idUser = reset($_POST['reset_track']);

            $userInfo = $aclMan->getUser($idUser, false);

            $GLOBALS['page']->add(
                Form::openForm('test_vote', 'index.php?r=lms/coursereport/testvote')
                . Form::getHidden('id_test', 'id_test', $idTest)
                . Form::getHidden('id_user', 'id_user', $idUser)
                . getDeleteUi(
                    $lang->def('_AREYOUSURE'),
                    '<span>' . $lang->def('_RESET') . ' : </span>' . strip_tags($test_info[$idTest]['title']) . '<br />'
                    . '<span>' . $lang->def('_OF_USER') . ' : </span>' . ($userInfo[ACL_INFO_LASTNAME] . $userInfo[ACL_INFO_FIRSTNAME]
                        ? $userInfo[ACL_INFO_LASTNAME] . ' ' . $userInfo[ACL_INFO_FIRSTNAME]
                        : $aclMan->relativeId($userInfo[ACL_INFO_USERID])),
                    false,
                    'confirm_reset',
                    'undo_reset'
                )
                . Form::closeForm()
                . '</div>',
                'content'
            );

            return;
        }
        if (isset($_POST['confirm_reset'])) {
            $idUser = importVar('id_user', true, 0);
            if ($testMan->deleteTestTrack($idTest, $idUser)) {
                $GLOBALS['page']->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')), 'content');
            } //($lang->def('_RESET_TRACK_SUCCESS')), 'content');
            else {
                $GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')), 'content');
            }
        }

        //==========================================================================================

        if (isset($_POST['save'])) {
            $re = $this->saveTestUpdate($idTest, $testMan);
            Util::jump_to('index.php?r=coursereport/coursereport&resul=' . ($re ? 'ok' : 'err'));
        }

        // retirive activity info
        $query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM %lms_coursereport
	WHERE id_course = '" . $this->idCourse . "'
	AND source_of = '" . CoursereportLms::SOURCE_OF_TEST . "' AND id_source = '" . $idTest . "'";

        $infoReport = sql_fetch_assoc(sql_query($query_report));

        $query = 'SELECT question_random_number'
            . ' FROM %lms_test'
            . " WHERE idTest = '" . $idTest . "'";

        [$question_random_number] = sql_fetch_row(sql_query($query));
        /* XXX: scores */
        $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));

        $type_h = ['', 'align-center', 'align-center', 'align-center', '', 'image'];
        $cont_h = [
            $lang->def('_STUDENTS'),
            $lang->def('_SCORE'),
            $lang->def('_SHOW_ANSWER'),
            $lang->def('_DATE'),
            $lang->def('_COMMENTS'),
            '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . $lang->def('_RESET') . '" title="' . $lang->def('_RESET') . '" />',
        ];
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        $out->add(
            Form::openForm('test_vote', 'index.php?r=lms/coursereport/testvote')
            . Form::getHidden('id_test', 'id_test', $idTest)
        );

        $out->add(
        // main form
            Form::openElementSpace()
            . Form::getOpenFieldSet($lang->def('_TEST_INFO'))

            . Form::getLinebox(
                $lang->def('_TITLE_ACT'),
                strip_tags($test_info[$idTest]['title'])
            )
            . ($question_random_number ? Form::getTextfield($lang->def('_MAX_SCORE'), 'max_score', 'max_score', '11', $infoReport['max_score']) : Form::getLinebox($lang->def('_MAX_SCORE'), $infoReport['max_score']))
            . Form::getLinebox(
                $lang->def('_REQUIRED_SCORE'),
                $infoReport['required_score']
            )

            . Form::getTextfield(
                $lang->def('_WEIGHT'),
                'weight',
                'weight',
                '11',
                $infoReport['weight']
            )
            . Form::getDropdown(
                $lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport['show_to_user']
            )
            . Form::getDropdown(
                $lang->def('_USE_FOR_FINAL'),
                'use_for_final',
                'use_for_final',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport['use_for_final']
            )
            . Form::getCloseFieldSet()
            . Form::closeElementSpace()
        );

        // XXX: retrive scores
        $testsScore = &$testMan->getTestsScores([$idTest], $id_students);

        // XXX: Display user scores
        $i = 0;
        foreach ($students_info as $idstUser => $userInfo) {
            $user_name = ($userInfo[ACL_INFO_LASTNAME] . $userInfo[ACL_INFO_FIRSTNAME]
                ? $userInfo[ACL_INFO_LASTNAME] . ' ' . $userInfo[ACL_INFO_FIRSTNAME]
                : $aclMan->relativeId($userInfo[ACL_INFO_USERID]));

            $cont = [Form::getLabel('user_score_' . $idstUser, $user_name)];

            $idTest = $infoReport['id_source'];
            if (isset($testsScore[$idTest][$idstUser])) {
                switch ($testsScore[$idTest][$idstUser]['score_status']) {
                    case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                        $cont[] = '-';

                        break;
                    case CoursereportLms::TEST_STATUS_NOT_CHECKED:
                        $cont[] = '<span class="cr_not_check">' . $lang->def('_NOT_CHECKED') . '</span><br />'
                            . Form::getInputTextfield(
                                'textfield_nowh',
                                'user_score_' . $idstUser,
                                'user_score[' . $idstUser . ']',
                                $testsScore[$idTest][$idstUser]['score'],
                                strip_tags($lang->def('_SCORE')),
                                '8',
                                ' tabindex="' . $i++ . '" '
                            );

                        break;
                    case CoursereportLms::TEST_STATUS_NOT_PASSED:
                    case CoursereportLms::TEST_STATUS_PASSED:
                        /*
                    $cont[] = Form::getInputDropdown(	'dropdown',
                                                            'user_score',
                                                            'user_score',
                                                            array(CoursereportLms::TEST_STATUS_PASSED => $lang->def('_PASSED'), CoursereportLms::TEST_STATUS_NOT_PASSED => $lang->def('_NOT_PASSED')),
                                                            $testsScore[$idTest][$idstUser]['score_status'],
                                                            '');
                                                            */
                        $cont[] = Form::getInputTextfield(
                            'textfield_nowh',
                            'user_score_' . $idstUser,
                            'user_score[' . $idstUser . ']',
                            $testsScore[$idTest][$idstUser]['score'],
                            strip_tags($lang->def('_SCORE')),
                            '8',
                            ' tabindex="' . $i++ . '" '
                        );

                        break;
                    case CoursereportLms::TEST_STATUS_VALID:
                        $cont[] = Form::getInputTextfield(
                            'textfield_nowh',
                            'user_score_' . $idstUser,
                            'user_score[' . $idstUser . ']',
                            $testsScore[$idTest][$idstUser]['score'],
                            strip_tags($lang->def('_SCORE')),
                            '8',
                            ' tabindex="' . $i++ . '" '
                        );

                        break;
                    default:
                        $cont[] = '-';
                }
                if ($testsScore[$idTest][$idstUser]['score_status'] != 'not_comlete') {
                    $cont[] = Form::getButton('view_anser_' . $idstUser, 'view_answer[' . $idstUser . ']', $lang->def('_SHOW_ANSWER'), 'button_nowh');

                    if ($chart_options->use_charts) {
                        $img = '<img src="' . getPathImage('lms') . 'standard/stats22.gif" alt="' . $lang->def('_SHOW_CHART') . '" title="' . $lang->def('_SHOW_CHART_TITLE') . '" />';
                        $url = 'index.php?r=lms/coursereport/showchart&id_test=' . (int)$idTest . '&id_user=' . (int)$idstUser . '&chart_type=' . $chart_options->selected_chart;
                        $cont[] = '<a href="' . $url . '">' . $img . '</a>';
                    }

                    $cont[] = Form::getInputDatefield(
                        'textfield_nowh',
                        'date_attempt_' . $idstUser,
                        'date_attempt[' . $idstUser . ']',
                        Format::date($testsScore[$idTest][$idstUser]['date_attempt'])
                    );

                    $cont[] = Form::getInputTextarea(
                        'comment_' . $idstUser,
                        'comment[' . $idstUser . ']',
                        $testsScore[$idTest][$idstUser]['comment'],
                        'textarea_wh_full',
                        2
                    );

                    $cont[] = '<input 	class="reset_track"
									type="image"
									src="' . getPathImage('lms') . 'standard/delete.png"
									alt="' . $lang->def('_RESET') . '"
									id="reset_track_' . $idstUser . '"
									name="reset_track[' . $idstUser . ']"
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
            . '</div>'
        );
    }

    public function testDetail()
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');

        $lang = FormaLanguage::createInstance('coursereport', 'lms');

        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        $idTest = importVar('id_test', true, 0);

        $testMan = new GroupTestManagement();


        $quests = [];
        $answers = [];
        $tracks = [];

        $test_info = &$testMan->getTestInfo([$idTest]);

        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            'index.php?r=lms/coursereport/testdetail&amp;id_test=' . $idTest => $test_info[$idTest]['title'],
        ];

        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
        );

        $query_test = 'SELECT title'
            . ' FROM %lms_test'
            . " WHERE idTest = '" . $idTest . "'";

        [$titolo_test] = sql_fetch_row(sql_query($query_test));

        $query_quest = 'SELECT idQuest, type_quest, title_quest'
            . ' FROM %lms_testquest'
            . " WHERE idTest = '" . $idTest . "'"
            . ' ORDER BY sequence';

        $result_quest = sql_query($query_quest);

        while (list($id_quest, $type_quest, $title_quest) = sql_fetch_row($result_quest)) {
            $quests[$id_quest]['idQuest'] = $id_quest;
            $quests[$id_quest]['type_quest'] = $type_quest;
            $quests[$id_quest]['title_quest'] = $title_quest;

            $query_answer = 'SELECT idAnswer, is_correct, answer'
                . ' FROM %lms_testquestanswer'
                . " WHERE idQuest = '" . $id_quest . "'"
                . ' ORDER BY sequence';

            $result_answer = sql_query($query_answer);

            while (list($id_answer, $is_correct, $answer) = sql_fetch_row($result_answer)) {
                $answers[$id_quest][$id_answer]['idAnswer'] = $id_answer;
                $answers[$id_quest][$id_answer]['is_correct'] = $is_correct;
                $answers[$id_quest][$id_answer]['answer'] = $answer;
            }
        }

        $query_track = 'SELECT idTrack'
            . ' FROM %lms_testtrack'
            . " WHERE idTest = '" . $idTest . "'";

        $result_track = sql_query($query_track);

        while (list($id_track) = sql_fetch_row($result_track)) {
            $query_track_answer = 'SELECT idQuest, idAnswer'
                . ' FROM %lms_testtrack_answer'
                . " WHERE idTrack = '" . $id_track . "'";

            $result_track_answer = sql_query($query_track_answer);

            while (list($id_quest, $id_answer) = sql_fetch_row($result_track_answer)) {
                $tracks[$id_track][$id_quest] = $id_answer;
            }
        }
    }

    public function testreview()
    {
        checkPerm('mod', true, $this->_mvc_name);
        $undo = FormaLms\lib\Get::pReq('undo_testreview', DOTY_MIXED, false);
        $idTest = importVar('id_test', true, 0);
        if ($undo) {
            Util::jump_to('index.php?r=coursereport/testvote&id_test=' . $idTest);
        }
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');

        // XXX: Initializaing
        $id_track = importVar('idTrack', true, 0);
        $number_time = importVar('number_time', true, null);
        $delete = importVar('delete_track', false, null);

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $testMan = new GroupTestManagement();
  

        // XXX: Save input if needed
        if (isset($_POST['view_answer'])) {
            $re = $this->saveTestUpdate($idTest, $testMan);
            $idUser = reset(array_keys($_POST['view_answer']));
        } else {
            $idUser = importVar('id_user', true, 0);
        }

        if (isset($_POST['save_new_scores'])) {
            $re = $testMan->saveReview($idTest, $idUser);
            Util::jump_to('index.php?r=lms/coursereport/testvote&amp;id_test=' . $idTest . '&result=' . ($re ? 'ok' : 'err'));
        }

        $user_name = $aclMan->getUserName($idUser);

        // XXX: Find test
        $test_info = &$testMan->getTestInfo([$idTest]);

        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            'index.php?r=lms/coursereport/testvote&amp;id_test=' . $idTest => $test_info[$idTest]['title'],
            $user_name,
        ];
        if (isset($_POST['view_answer'])) {
            $out->add(
                getTitleArea($page_title, 'coursereport')
                . '<div class="std_block">'
                . Form::openForm('test_vote', 'index.php?r=lms/coursereport/testreview')
                . Form::getHidden('id_test', 'id_test', $idTest)
                . Form::getHidden('id_user', 'id_user', $idUser)
            );
            $testMan->editReview($idTest, $idUser, $number_time);
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
                . Form::openForm('test_vote', 'index.php?r=lms/coursereport/testreport&idTest=' . $idTest . '&idTrack=' . $id_track)
            );
            $testMan->editReview($idTest, $idUser, $number_time, false);
            $out->add(
                Form::openButtonSpace()
                . Form::getButton('go_back', 'go_back', $lang->def('_UNDO'))
            );

            if ($delete == md5($idTest . '_' . $idUser . '_' . $number_time)) {
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
                                    <p>' . $lang->def('_DELETE_TEST_TRACK_MODAL_BODY') . '</p>
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
                                   
                                   window.location.href = "index.php?r=lms/coursereport/testdelete&delete_track=' . md5($idTest . '_' . $idUser . '_' . $number_time) . '&id_test=' . $idTest . '&id_user=' . $idUser . '&number_time=' . $number_time . '&idTrack=' . $id_track . '";
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
    public function testdelete()
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');

        // XXX: Initializaing
        $idTest = importVar('id_test', true, 0);
        $id_track = importVar('idTrack', true, 0);
        $number_time = importVar('number_time', true, null);
        $delete = importVar('delete_track', false, null);
        $idUser = importVar('id_user', true, 0);

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        if ($delete == md5($idTest . '_' . $idUser . '_' . $number_time)) {
            $testMan = new GroupTestManagement();

            $testMan->deleteReview($idTest, $idUser, $id_track, $number_time);

            $aclMan = \FormaLms\lib\Forma::getAclManager();

            $testMan = new GroupTestManagement();

            $user_name = $aclMan->getUserName($idUser);

            // XXX: Find test
            $test_info = &$testMan->getTestInfo([$idTest]);

            Util::jump_to('index.php?r=lms/coursereport/testreport&idTest=' . $idTest . '&idTrack=' . $id_track . '&testName=' . html_entity_decode(strip_tags(urldecode($test_info[$idTest]['title']))) . '&studentName=' . $user_name);
        } else {
            exit("You can't access");
        }
    }

    public function finalvote()
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $aclMan = \FormaLms\lib\Forma::getAclManager();
     

        // XXX: Find students
        $type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int)$this->idCourse, false, $lev, true, false, false, true);
        $id_students = array_keys($students);
        $students_info = &$aclMan->getUsers($id_students);

        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_FINAL_SCORE')),
        ];
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . Form::openForm('finalvote', 'index.php?r=lms/coursereport/finalvote&amp;type_filter=' . $type_filter)
            . Form::getHidden('id_report', 'id_report', $id_report)
        );

        // XXX: Save input if needed
        if (isset($_POST['save'])) {
            // Save report modification
            $query_upd_report = "
		UPDATE %lms_coursereport
		SET max_score = '" . $_POST['max_score'] . "',
			required_score = '" . $_POST['required_score'] . "',
			show_to_user = '" . $_POST['show_to_user'] . "'
		WHERE  id_course = '" . $this->idCourse . "' AND id_report = '" . $id_report . "'
			AND source_of = 'final_vote' AND id_source = '0'";
            sql_query($query_upd_report);
            // save user score modification

            $re = $this->courseReportManager->saveReportScore($id_report, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);

            Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($re ? 'ok' : 'err'));
        }

        if (isset($_POST['save'])) {
            // retirive activity info
            //__construct($id_report, $title, $max_score, $required_score, $weight, $show_to_user, $use_for_final, $source_of, $id_source)
            $infoReport = new ReportLms(null, null, importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', false, 'true'), null, 'final_vote', importVar('max_score', true));
        } else {
            // retirive activity info

            $infoReport = CoursereportLms::getReportFinalScore($this->idCourse);
        }

        $out->add(
        // main form
            Form::openElementSpace()
            . Form::getOpenFieldSet($lang->def('_TEST_INFO'))

            . Form::getLinebox(
                $lang->def('_TITLE_ACT'),
                $lang->def('_FINAL_SCORE')
            )
            . Form::getTextfield(
                $lang->def('_MAX_SCORE'),
                'max_score',
                'max_score',
                '11',
                $infoReport->getMaxScore()
            )
            . Form::getTextfield(
                $lang->def('_REQUIRED_SCORE'),
                'required_score',
                'required_score',
                '11',
                $infoReport->getRequiredScore()
            )
            . Form::getDropdown(
                $lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport->isShowToUserToString()
            )
            . Form::getCloseFieldSet()
            . Form::closeElementSpace()
        );

        /* XXX: scores */
        $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));
        $type_h = ['', 'align-center', 'align-center', 'align-center', ''];
        $cont_h = [
            $lang->def('_STUDENTS'),
            $lang->def('_SCORE'),
            $lang->def('_DATE'),
            $lang->def('_COMMENTS'),
        ];
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        // XXX: retrive scores
        $report_score = &$this->courseReportManager->getReportsScores([$id_report]);

        // XXX: Display user scores
        $i = 0;
        foreach ($students_info as $idstUser => $userInfo) {
            $user_name = ($userInfo[ACL_INFO_LASTNAME] . $userInfo[ACL_INFO_FIRSTNAME]
                ? $userInfo[ACL_INFO_LASTNAME] . ' ' . $userInfo[ACL_INFO_FIRSTNAME]
                : $aclMan->relativeId($userInfo[ACL_INFO_USERID]));
            $cont = [Form::getLabel('user_score_' . $idstUser, $user_name)];

            $cont[] = Form::getInputTextfield(
                'textfield_nowh',
                'user_score_' . $idstUser,
                'user_score[' . $idstUser . ']',
                (isset($report_score[$id_report][$idstUser]['score'])
                    ? $report_score[$id_report][$idstUser]['score'] : ''),
                strip_tags($lang->def('_SCORE')),
                '8',
                ' tabindex="' . $i++ . '" '
            );
            $cont[] = Form::getInputDatefield(
                'textfield_nowh',
                'date_attempt_' . $idstUser,
                'date_attempt[' . $idstUser . ']',
                Format::date(
                    (isset($report_score[$id_report][$idstUser]['date_attempt'])
                        ? $report_score[$id_report][$idstUser]['date_attempt'] : ''),
                    'date'
                )
            );
            $cont[] = Form::getInputTextarea(
                'comment_' . $idstUser,
                'comment[' . $idstUser . ']',
                (isset($report_score[$id_report][$idstUser]['comment'])
                    ? $report_score[$id_report][$idstUser]['comment'] : ''),
                'textarea_wh_full',
                2
            );

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
            . '</div>'
        );
    }

    public function roundtest($idTest)
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        //$idTest = importVar('id_test', true, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');

        $testMan = new GroupTestManagement();
      

        // XXX: Find test from organization
        $re = $testMan->roundTestScore($idTest);

        //Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    public function roundreport($idReport)
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Instance management
   

        // XXX: Find test from organization
        $re = $this->courseReportManager->roundReportScore($idReport);

        //Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    /**
     *    final_score =.
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
    public function redofinal()
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $lang = FormaLanguage::createInstance('coursereport', 'lms');

        // XXX: Instance management
        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $testMan = new GroupTestManagement();
      

        // XXX: Find students
        $id_students = &$this->courseReportManager->getStudentId();

        // XXX: retrive info about the final score

        $courseReportLms = new CoursereportLms($this->idCourse);

        $info_final = $courseReportLms->getReportsFilteredBySourceOf(CoursereportLms::SOURCE_OF_FINAL_VOTE);

        // XXX: Retrive all reports (test and so), and set it

        $reports = $courseReportLms->getReportsForFinal();

        $sumMaxScore = 0;
        $includedTest = [];
        $otherSource = [];

        foreach ($reports as $infoReport) {
            $sumMaxScore += $infoReport->getMaxScore() * $infoReport->getWeight();

            switch ($infoReport->getSourceOf()) {
                case CoursereportLms::SOURCE_OF_ACTIVITY:
                    $otherSource[$infoReport->getIdReport()] = $infoReport->getIdReport();
                    break;
                case CoursereportLms::SOURCE_OF_TEST:
                    $includedTest[$id] = $id;
                    break;
                default:
                    break;
            }
        }
        // XXX: Retrive Test score
        if (!empty($includedTest)) {
            $testsScore = &$testMan->getTestsScores($includedTest, $id_students);
        }

        // XXX: Retrive other score
        if (!empty($otherSource)) {
            $otherScore = &$this->courseReportManager->getReportsScores($otherSource);
        }

        $finalScore = [];
        foreach ($id_students as $idUser) {
            $userScore = 0;
            foreach ($reports as $infoReport) {
                switch ($infoReport->getSourceOf()) {
                    case CoursereportLms::SOURCE_OF_ACTIVITY:
                        if (isset($otherScore[$infoReport->getIdReport()][$idUser]) && ($otherScore[$infoReport->getIdReport()][$idUser]['score_status'] == 'valid')) {
                            $userScore += ($otherScore[$infoReport->getIdReport()][$idUser]['score'] * $infoReport->getWeight());
                        } else {
                            $userScore += 0;
                        }

                        break;
                    case CoursereportLms::SOURCE_OF_TEST:
                        if (isset($testsScore[$id][$idUser]) && ($testsScore[$id][$idUser]['score_status'] == 'valid')) {
                            $userScore += ($testsScore[$id][$idUser]['score'] * $infoReport->getWeight());
                        } else {
                            $userScore += 0;
                        }

                        break;
                    default:
                        break;
                }
            }

            // user final score
            if ($sumMaxScore != 0) {
                $finalScore[$idUser] = round(($userScore / $sumMaxScore) * $info_final[0]->getMaxScore(), 2);
            } else {
                $finalScore[$idUser] = 0;
            }
        }
        // Save final scores
        $exists_final = [];
        $query_final_score = "SELECT id_user
	                          FROM %lms_coursereport_score
	                          WHERE id_report = '" . $info_final['id_report'] . "'";

        $re_final = sql_query($query_final_score);
        foreach ($re_final as $item) {
            $exists_final[$item['id_user']] = $item['id_user'];
        }

        $re = true;
        foreach ($finalScore as $user => $score) {
            if (isset($exists_final[$user])) {
                $query_scores = "
                    UPDATE %lms_coursereport_score
                    SET score = '" . $score . "',
                        date_attempt = '" . date('Y-m-d H:i:s') . "'
                    WHERE id_report = '" . $info_final['id_report'] . "' AND id_user = '" . $user . "'";
                $re &= sql_query($query_scores);
            } else {
                $query_scores = "
                INSERT IGNORE INTO  %lms_coursereport_score
                ( id_report, id_user, score, date_attempt ) VALUES (
                    '" . $info_final['id_report'] . "',
                    '" . $user . "',
                    '" . $score . "',
                    '" . date('Y-m-d H:i:s') . "' )";
                $re &= sql_query($query_scores);
            }
        }
    }

    public function addscorm()
    {
        $this->modscorm();
    }

    public function modscorm()
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = FormaLms\lib\Get::req('id_report', DOTY_INT, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: undo
        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }

        // XXX: Retrive all colums (test and so), and set it
        if ($id_report == 0) {
            $infoReport = new ReportLms(importVar('id_report', true, null), importVar('title'), importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', true, true), importVar('use_for_final', true, true), '', 0);
        } elseif (!isset($_POST['save'])) {
            $this->model = new CoursereportLms($this->idCourse, $id_report, 'activity', '0');

            $infoReport = $this->model->getCourseReports()[0];
        }

        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY')),
        ];
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'

            . getBackUi('index.php?r=coursereport/coursereport', $lang->def('_BACK'))
        );
        // XXX: Save input if needed
        if (isset($_POST['save']) && is_numeric($_POST['id_source'])) {
     
            // check input
            if ($_POST['titolo'] == '') {
                $_POST['titolo'] = $lang->def('_NOTITLE');
            }
            //MODIFICHE NUOVISSIMISSIME
            $query_report = '
		SELECT  *
		FROM %lms_scorm_items
		WHERE idscorm_item=' . $_POST['id_source'];
            //echo $query_report;
            $risultato = sql_query($query_report);
            $titolo2 = sql_fetch_assoc($risultato);

            // if module title is equals to main title don't append it
            if ($titolo2['title'] != $_POST['titolo']) {
                $_POST['titolo'] = $_POST['titolo'] . ' - ' . addslashes($titolo2['title']);
            }

            $_POST['title'] = $_POST['titolo'];
            $re_check = $this->courseReportManager->checkActivityData($_POST);

            if (!$re_check['error']) {
                if ($id_report == 0) {
                    $numero = $this->courseReportManager->getNextSequence();
                    $query_ins_report = "
				INSERT IGNORE INTO %lms_coursereport
				( id_course, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
					'" . $this->idCourse . "',
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
				UPDATE %lms_coursereport
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
            } else {
                $out->add(getErrorUi($re_check['message']));
            }
        }

        if (isset($_POST['filtra'])) {
            if ($_POST['source_of'] === 'scoitem' && is_numeric($_POST['title'])) {
                //richiesto lo scorm item
                $query_report = "SELECT  title FROM %lms_organization
								WHERE objectType='scormorg' AND idResource=" . (int)$_POST['title'];

                $risultato = sql_query($query_report);
                $titolo = sql_fetch_assoc($risultato);
                $titolo = $titolo['title'];

                $query_report = '
			SELECT  *
			FROM %lms_scorm_items
			WHERE idscorm_organization=' . (int)$_POST['title'] . '
			ORDER BY idscorm_item';
                //echo $query_report;
                $risultato = sql_query($query_report);
                while ($scorm = sql_fetch_assoc($risultato)) {
                    $array_scorm[$scorm['idscorm_item']] = $scorm['title'];
                }

                $out->add(
                    Form::openForm('addscorm', 'index.php?r=lms/coursereport/addscorm')
                    . Form::openElementSpace()
                    . Form::getHidden('id_report', 'id_report', $id_report)
                    . Form::getDropdown(
                        $lang->def('_SCORM_ITEM'),
                        'id_source',
                        'id_source',
                        $array_scorm,
                        $id
                    )

                    . Form::getTextfield(
                        $lang->def('_WEIGHT'),
                        'weight',
                        'weight',
                        '11',
                        $infoReport->getWeight()
                    )
                    . Form::getDropdown(
                        $lang->def('_SHOW_TO_USER'),
                        'show_to_user',
                        'show_to_user',
                        ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                        $infoReport->isShowToUserToString()
                    )
                    . Form::getDropdown(
                        $lang->def('_USE_FOR_FINAL'),
                        'use_for_final',
                        'use_for_final',
                        ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                        $infoReport->isUseForFinalToString()
                    )
                    . Form::getHidden('title', 'title', $_POST['title'])
                    . Form::getHidden('source_of', 'source_of', $_POST['source_of'])
                    . Form::getHidden('titolo', 'titolo', $titolo)
                    . Form::closeElementSpace()
                    . Form::openButtonSpace()
                    . Form::getButton('save', 'save', $lang->def('_SAVE'))
                    . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
                    . Form::closeButtonSpace()
                    . Form::closeForm()
                    . '</div>'
                );
            }
        }
        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY')),
        ];

        if (!isset($_POST['filtra'])) {
            $query_report = 'SELECT  idResource,title FROM ' . $GLOBALS['prefix_lms'] . "_organization
			WHERE objectType='scormorg' AND idCourse=" . $this->idCourse;

            $risultato = sql_query($query_report);
            while ($scorm = sql_fetch_assoc($risultato)) {
                $array_scorm[$scorm['idResource']] = $scorm['title'];
            }

            $out->add(
                Form::openForm('addscorm', 'index.php?r=lms/coursereport/addscorm')
                . Form::openElementSpace()
                . Form::getHidden('id_report', 'id_report', $id_report)
                . Form::getDropdown(
                    $lang->def('_TITLE'),
                    'title',
                    'title',
                    $array_scorm,
                    $infoReport->getTitle()
                )

                . Form::getRadioSet(
                    $lang->def('_SCORE'),
                    'source_of',
                    'source_of',
                    ['Scorm Item' => 'scoitem'], //,  "Somma" => 'scormorg_sum', "Media"  =>'scormorg_avg'),
                    'scoitem'
                )

                . Form::closeElementSpace()
                . Form::openButtonSpace()
                . Form::getButton('filtra', 'filtra', $lang->def('_SAVE'))
                . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
                . Form::closeButtonSpace()
                . Form::closeForm()
                . '</div>'
            );
        }
    }

    public function addactivity()
    {
        $this->modactivity();
    }

    public function modactivity()
    {
        checkPerm('mod', true, $this->_mvc_name);
        $undo = FormaLms\lib\Get::pReq('undo', DOTY_MIXED, false);

        if ($undo) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: undo
        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }

        // XXX: Retrive all colums (test and so), and set it

        if ($id_report == 0) {
            $infoReport = new ReportLms(importVar('id_report', true, null), importVar('title'), importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', true, true), importVar('use_for_final', true, true), '', 0);
        } elseif (!isset($_POST['save'])) {
            $this->model = new CoursereportLms($this->idCourse, $id_report, 'activity', '0');

            $infoReport = $this->model->getCourseReports()[0];
        }

        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY')),
        ];
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . getBackUi('index.php?r=coursereport/coursereport', $lang->def('_BACK'))
        );
        // XXX: Save input if needed
        if (isset($_POST['save'])) {
           ;
            // check input
            if ($_POST['title'] == '') {
                $_POST['title'] = $lang->def('_NOTITLE');
            }

            $re_check = $this->courseReportManager->checkActivityData($_POST);
            if (!$re_check['error']) {
                if ($id_report == 0) {
                    $re = $this->courseReportManager->addActivity($this->idCourse, $_POST);
                } else {
                    $re = $this->courseReportManager->updateActivity($id_report, $this->idCourse, $_POST);
                }
                Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($re ? 'ok' : 'err'));
            } else {
                $out->add(getErrorUi($re_check['message']));
            }
        }

        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($lang->def('_ADD_ACTIVITY')),
        ];
        $out->add(
            Form::openForm('addactivity', 'index.php?r=lms/coursereport/addactivity')
            . Form::openElementSpace()
            . Form::getHidden('id_report', 'id_report', $id_report)
            . Form::getTextfield(
                $lang->def('_TITLE_ACT'),
                'title',
                'title',
                '255',
                $infoReport->getTitle()
            )
            . Form::getTextfield(
                $lang->def('_MAX_SCORE'),
                'max_score',
                'max_score',
                '11',
                $infoReport->getMaxScore()
            )
            . Form::getTextfield(
                $lang->def('_REQUIRED_SCORE'),
                'required_score',
                'required_score',
                '11',
                $infoReport->getRequiredScore()
            )
            . Form::getTextfield(
                $lang->def('_WEIGHT'),
                'weight',
                'weight',
                '11',
                $infoReport->getWeight()
            )
            . Form::getDropdown(
                $lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport->isShowToUserToString()
            )
            . Form::getDropdown(
                $lang->def('_USE_FOR_FINAL'),
                'use_for_final',
                'use_for_final',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport->isUseForFinalToString()
            )
            . Form::closeElementSpace()
            . Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>'
        );
    }

    public function modactivityscore()
    {
        checkPerm('mod', true, $this->_mvc_name);

        $undo = FormaLms\lib\Get::pReq('undo', DOTY_MIXED, false);

        if ($undo) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');

        // XXX: Instance management
        $aclMan = \FormaLms\lib\Forma::getAclManager();
      

        // XXX: Find users
        $type_filter = false;
        if (isset($_GET['type_filter']) && $_GET['type_filter'] != null) {
            $type_filter = $_GET['type_filter'];
        }

        $lev = $type_filter;
        $students = getSubscribed((int)$this->idCourse, false, $lev, true, false, false, true);
        $id_students = array_keys($students);
        $students_info = &$aclMan->getUsers($id_students);

        if (isset($_POST['save'])) {
            $infoReport = new ReportLms(importVar('id_report', true, null), importVar('title'), importVar('max_score', true), importVar('required_score', true), importVar('weight', true), importVar('show_to_user', true, true), importVar('use_for_final', true, true), '', 0);
        } else {
            $this->model = new CoursereportLms($this->idCourse, $id_report, ['activity', 'scoitem']);

            $infoReport = $this->model->getCourseReports()[0];

            $report_score = &$this->courseReportManager->getReportsScores($infoReport->getIdReport());
        }

        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            strip_tags($infoReport->getTitle()),
        ];
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . Form::openForm('activity', 'index.php?r=lms/coursereport/modactivityscore')
        );

        // XXX: Save input if needed
        if (isset($_POST['save'])) {
            if ($_POST['title'] == '') {
                $_POST['title'] = $lang->def('_NOTITLE');
            }
            $re_check = $this->courseReportManager->checkActivityData($_POST);
            if (!$re_check['error']) {
                if (!$this->courseReportManager->updateActivity($id_report, $this->idCourse, $infoReport)) {
                    $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
                } else {
                    // save user score modification
                    $query_upd_report = "
				UPDATE %lms_coursereport
				SET weight = '" . $infoReport->getWeight() . "',
					use_for_final = '" . $infoReport->isUseForFinalToString() . "',
					show_to_user = '" . $infoReport->isShowToUserToString() . "'
				WHERE id_course = '" . $this->idCourse . "' AND id_report = '" . $id_report . "'";
                    $re = sql_query($query_upd_report);

                    $response = $this->courseReportManager->saveReportScore($id_report, $_POST['user_score'], $_POST['date_attempt'], $_POST['comment']);
                    Util::jump_to('index.php?r=coursereport/coursereport&result=' . ($response ? 'ok' : 'err'));
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
            . Form::getHidden('id_source', 'id_source', $id)
            . Form::getHidden('source_of', 'source_of', $infoReport->getSourceOf())
        );
        // for scorm object changing title, maxScore and requiredScore is not allowed
        switch ($infoReport->getSourceOf()) {
            case 'scoitem':
                $out->add(
                    Form::getLinebox(
                        $lang->def('_TITLE_ACT'),
                        strip_tags($infoReport->getTitle())
                    )
                    . Form::getLinebox(
                        $lang->def('_MAX_SCORE'),
                        strip_tags($infoReport->getMaxScore())
                    )
                    . Form::getLinebox(
                        $lang->def('_REQUIRED_SCORE'),
                        strip_tags($infoReport->getRequiredScore())
                    )
                );
                break;
            case 'activity':
                $out->add(
                    Form::getTextfield(
                        $lang->def('_TITLE_ACT'),
                        'title',
                        'title',
                        '255',
                        $infoReport->getTitle()
                    )
                    . Form::getTextfield(
                        $lang->def('_MAX_SCORE'),
                        'max_score',
                        'max_score',
                        '11',
                        $infoReport->getMaxScore()
                    )
                    . Form::getTextfield(
                        $lang->def('_REQUIRED_SCORE'),
                        'required_score',
                        'required_score',
                        '11',
                        $infoReport->getRequiredScore()
                    )
                );
                break;
        }
        $out->add(
            Form::getTextfield(
                $lang->def('_WEIGHT'),
                'weight',
                'weight',
                '11',
                $infoReport->getWeight()
            )
            . Form::getDropdown(
                $lang->def('_SHOW_TO_USER'),
                'show_to_user',
                'show_to_user',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport->isShowToUser()
            )
            . Form::getDropdown(
                $lang->def('_USE_FOR_FINAL'),
                'use_for_final',
                'use_for_final',
                ['true' => $lang->def('_YES'), 'false' => $lang->def('_NO')],
                $infoReport->isUseForFinal()
            )
            . Form::getCloseFieldSet()
            . Form::closeElementSpace()
        );

        if ($infoReport->getSourceOf() != 'scoitem') {
            /* XXX: scores */
            $tb = new Table(0, $lang->def('_STUDENTS_VOTE'), $lang->def('_STUDENTS_VOTE'));
            $type_h = ['', 'align-center', 'align-center', ''];
            $tb->setColsStyle($type_h);
            $cont_h = [
                $lang->def('_STUDENTS'),
                $lang->def('_SCORE'),
                $lang->def('_DATE'),
                $lang->def('_COMMENTS'),
            ];
            $tb->addHead($cont_h);

            // XXX: Display user scores
            $i = 0;
            foreach ($students_info as $idstUser => $userInfo) {
                $user_name = ($userInfo[ACL_INFO_LASTNAME] . $userInfo[ACL_INFO_FIRSTNAME]
                    ? $userInfo[ACL_INFO_LASTNAME] . ' ' . $userInfo[ACL_INFO_FIRSTNAME]
                    : $aclMan->relativeId($userInfo[ACL_INFO_USERID]));
                $cont = [Form::getLabel('user_score_' . $idstUser, $user_name)];

                $cont[] = Form::getInputTextfield(
                    'textfield_nowh',
                    'user_score_' . $idstUser,
                    'user_score[' . $idstUser . ']',
                    (isset($report_score[$id_report][$idstUser]['score'])
                        ? $report_score[$id_report][$idstUser]['score']
                        : (isset($_POST['user_score'][$idstUser]) ? $_POST['user_score'][$idstUser] : '')),
                    strip_tags($lang->def('_SCORE')),
                    '8',
                    ' tabindex="' . $i++ . '" '
                );
                $cont[] = Form::getInputDatefield(
                    'textfield_nowh',
                    'date_attempt_' . $idstUser,
                    'date_attempt[' . $idstUser . ']',
                    Format::date(
                        (isset($report_score[$id_report][$idstUser]['date_attempt'])
                            ? $report_score[$id_report][$idstUser]['date_attempt']
                            : (isset($_POST['date_attempt'][$idstUser]) ? $_POST['date_attempt'][$idstUser] : '')),
                        'date'
                    )
                );
                $cont[] = Form::getInputTextarea(
                    'comment_' . $idstUser,
                    'comment[' . $idstUser . ']',
                    (isset($report_score[$id_report][$idstUser]['comment'])
                        ? $report_score[$id_report][$idstUser]['comment']
                        : (isset($_POST['comment'][$idstUser]) ? stripslashes($_POST['comment'][$idstUser]) : '')),
                    'textarea_wh_full',
                    2
                );

                $tb->addBody($cont);
            }
        }

        $out->add(
            Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
        );
        if ($infoReport->getSourceOf() != 'scoitem') {
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

    public function delactivity()
    {
        checkPerm('mod', true, $this->_mvc_name);
        $undo = FormaLms\lib\Get::pReq('undo', DOTY_MIXED, false);

        if ($undo) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        // XXX: Initializaing
        $id_report = FormaLms\lib\Get::gReq('id_report', DOTY_MIXED, 0);

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');



        if (isset($_POST['confirm'])) {
            $id_report = FormaLms\lib\Get::pReq('id_report', DOTY_MIXED, 0);

            if (!$this->courseReportManager->deleteReportScore($id_report)) {
                Util::jump_to('index.php?r=coursereport/coursereport&amp;result=err');
            }

            $re = $this->courseReportManager->deleteReport($id_report);

            Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
        }

        // retirive activity info
        $query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final
	FROM %lms_coursereport
	WHERE id_course = '" . $this->idCourse . "' AND id_report = '" . $id_report . "'
			AND source_of = 'activity' AND id_source = '0'";
        $infoReport = sql_fetch_assoc(sql_query($query_report));

        // XXX: Write in output
        $page_title = [
            'index.php?r=coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            $lang->def('_DEL') . ' : ' . strip_tags($infoReport['title']),
        ];
        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
            . Form::openForm('delactivity', 'index.php?r=lms/coursereport/delactivity')
            . Form::getHidden('id_report', 'id_report', $id_report)
            . getDeleteUi(
                $lang->def('_AREYOUSURE'),
                $lang->def('_TITLE_ACT') . ' : ' . $infoReport['title'],
                false,
                'confirm',
                'undo'
            )
            . Form::closeForm()
            . '</div>'
        );
    }

    public function movereport($direction)
    {
        checkPerm('mod', true, $this->_mvc_name);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');

        // XXX: Initializaing
        $id_report = importVar('id_report', true, 0);
        $lang = FormaLanguage::createInstance('coursereport', 'lms');

    

        [$seq] = sql_fetch_row(sql_query("
	SELECT sequence
	FROM %lms_coursereport
	WHERE id_course = '" . $this->idCourse . "' AND id_report = '" . $id_report . "'"));

        if ($direction == 'left') {
            $re = sql_query("
		UPDATE %lms_coursereport
		SET sequence = '" . $seq . "'
		WHERE id_course = '" . $this->idCourse . "' AND sequence = '" . ($seq - 1) . "'");
            $re &= sql_query("
		UPDATE %lms_coursereport
		SET sequence = sequence - 1
		WHERE id_course = '" . $this->idCourse . "' AND id_report = '" . $id_report . "'");
        }
        if ($direction == 'right') {
            $re = sql_query("
		UPDATE %lms_coursereport
		SET sequence = '$seq'
		WHERE id_course = '" . $this->idCourse . "' AND sequence = '" . ($seq + 1) . "'");
            $re &= sql_query("
		UPDATE %lms_coursereport
		SET sequence = sequence + 1
		WHERE id_course = '" . $this->idCourse . "' AND id_report = '" . $id_report . "'");
        }

        Util::jump_to('index.php?r=coursereport/coursereport&amp;result=' . ($re ? 'ok' : 'err'));
    }

    public function export()
    {
        checkPerm('view', true, $this->_mvc_name);
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.form.php');
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $out = $GLOBALS['page'];
        $out->setWorkingZone('content');
        $includedTest = [];
        $mod_perm = checkPerm('mod', true);
        $view_all_perm = checkPerm('view_all', true, $this->_mvc_name);
        $csv = '';

        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $testMan = new GroupTestManagement();

        $org_tests = &$this->courseReportManager->getTest();
        $tests_info = $testMan->getTestInfo($org_tests);

        $id_students = &$this->courseReportManager->getStudentId();
        $students_info = &$aclMan->getUsers($id_students);

        if (isset($_POST['type_filter'])) {
            $type_filter = $_POST['type_filter'];
        } else {
            $type_filter = false;
        }

        if ($type_filter == 'false') {
            $type_filter = false;
        }

        $lev = $type_filter;

        $students = getSubscribedInfo((int)$this->idCourse, false, $lev, true, false, false, true);

        //apply sub admin filters, if needed
        if (!$view_all_perm) {
            //filter users
            require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
            foreach ($students as $idst => $user_course_info) {
                if (!in_array($idst, $ctrl_users)) {
                    // Elimino gli studenti non amministrati
                    unset($students[$idst]);
                }
            }
        }

        $i = 0;
        $students_info = [];
        foreach ($students as $idst => $user_course_info) {
            $students_info[$idst] = &$aclMan->getUser($idst, false);
        }

        $query_tot_report = "SELECT COUNT(*) FROM %lms_coursereport WHERE id_course = '" . $this->idCourse . "'";
        [$tot_report] = sql_fetch_row(sql_query($query_tot_report));

        $query_tests = "SELECT id_report, id_source FROM %lms_coursereport WHERE id_course = '" . $this->idCourse . "' AND source_of = '" . CoursereportLms::SOURCE_OF_TEST . "'";

        $re_tests = sql_query($query_tests);
        while (list($id_r, $id_t) = sql_fetch_row($re_tests)) {
            $includedTest[$id_t] = $id_t;
            $includedTestReportId[$id_r] = $id_r;
        }

        if ((int)$tot_report === 0) {
            $this->courseReportManager->initializeCourseReport($org_tests);
        } else {
            if (is_array($includedTest)) {
                $test_to_add = array_diff($org_tests, $includedTest);
            } else {
                $test_to_add = $org_tests;
            }
            if (is_array($includedTest)) {
                $test_to_del = array_diff($includedTest, $org_tests);
            } else {
                $test_to_del = $org_tests;
            }

            if (!empty($test_to_add) || !empty($test_to_del)) {
                $this->courseReportManager->addTestToReport($test_to_add, 1);
                $this->courseReportManager->delTestToReport($test_to_del);

                $includedTest = $org_tests;
            }
        }
        $this->courseReportManager->updateTestReport($org_tests);

        $img_mod = '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />';

        $cont_h[] = $lang->def('_DETAILS');
        $csv .= '"' . $lang->def('_DETAILS') . '"';

        $a_line_1 = [''];
        $a_line_2 = [''];
        $colums['max_score'] = [$lang->def('_MAX_SCORE')];
        $colums['required_score'] = [$lang->def('_REQUIRED_SCORE')];
        $colums['weight'] = [$lang->def('_WEIGHT')];
        $colums['show_to_user'] = [$lang->def('_SHOW_TO_USER')];
        $colums['use_for_final'] = [$lang->def('_USE_FOR_FINAL')];

        $this->model = new CoursereportLms($this->idCourse);

        $total_weight = 0;
        $i = 1;
        foreach ($this->model->getCourseReports() as $infoReport) {
            $id = $id;
            $reports[$infoReport->getIdReport()] = $infoReport;
            $reports_id[] = $infoReport->getIdReport();

            // XXX: set action colums

            switch ($infoReport->getSourceOf()) {
                case CoursereportLms::SOURCE_OF_TEST:
                    $title = strip_tags($tests_info[$id]['title']);

                    break;
                case CoursereportLms::SOURCE_OF_SCOITEM:
                case CoursereportLms::SOURCE_OF_ACTIVITY:
                    $title = strip_tags($infoReport->getTitle());

                    break;
                case CoursereportLms::SOURCE_OF_FINAL_VOTE:
                    $title = strip_tags($lang->def('_FINAL_SCORE'));

                    break;
            }

            $top = $title;

            $cont_h[] = $top;
            $csv .= ';"' . $top . '"';

            //set info colums
            $colums['max_score'][] = $infoReport->getMaxScore();
            $colums['required_score'][] = $infoReport->getRequiredScore();
            $colums['weight'][] = $infoReport->getWeight();
            $colums['show_to_user'][] = ($infoReport->isShowToUser() == 'true' ? $lang->def('_YES') : $lang->def('_NO'));
            $colums['use_for_final'][] = ($infoReport->isUseForFinal() == 'true' ? $lang->def('_YES') : $lang->def('_NO'));

            if ($infoReport->isUseForFinal() == 'true') {
                $total_weight += $infoReport->getWeight();
            }
        }

        $csv .= "\n";
        $first = true;
        foreach ($colums['max_score'] as $content) {
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else {
                $csv .= ';"' . $content . '"';
            }
        }

        $csv .= "\n";
        $first = true;
        foreach ($colums['required_score'] as $content) {
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else {
                $csv .= ';"' . $content . '"';
            }
        }

        $csv .= "\n";
        $first = true;
        foreach ($colums['weight'] as $content) {
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else {
                $csv .= ';"' . $content . '"';
            }
        }

        $csv .= "\n";
        $first = true;
        foreach ($colums['show_to_user'] as $content) {
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else {
                $csv .= ';"' . $content . '"';
            }
        }

        $csv .= "\n";
        $first = true;
        foreach ($colums['use_for_final'] as $content) {
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else {
                $csv .= ';"' . $content . '"';
            }
        }

        $csv .= "\n\n\n";
        $first = true;
        foreach ($cont_h as $content) {
            if ($first) {
                $first = false;
                $csv .= '"' . $content . '"';
            } else {
                $csv .= ';"' . $content . '"';
            }
        }

        $csv .= "\n";

        $testsScore = &$testMan->getTestsScores($includedTest, array_keys($students));

        $testDetails = [];
        if (is_array($includedTest)) {
            foreach ($testsScore as $idTest => $users_result) {
                foreach ($users_result as $idUser => $single_test) {
                    if ($single_test['score_status'] == 'valid') {
                        if (!isset($testDetails[$idTest]['max_score'])) {
                            $testDetails[$idTest]['max_score'] = $single_test['score'];
                        } elseif ($single_test['score'] > $testDetails[$idTest]['max_score']) {
                            $testDetails[$idTest]['max_score'] = $single_test['score'];
                        }

                        if (!isset($testDetails[$idTest]['min_score'])) {
                            $testDetails[$idTest]['min_score'] = $single_test['score'];
                        } elseif ($single_test['score'] < $testDetails[$idTest]['min_score']) {
                            $testDetails[$idTest]['min_score'] = $single_test['score'];
                        }

                        if (!isset($testDetails[$idTest]['num_result'])) {
                            $testDetails[$idTest]['num_result'] = 1;
                        } else {
                            ++$testDetails[$idTest]['num_result'];
                        }

                        if (!isset($testDetails[$idTest]['average'])) {
                            $testDetails[$idTest]['average'] = $single_test['score'];
                        } else {
                            $testDetails[$idTest]['average'] += $single_test['score'];
                        }
                    }
                }
            }
            foreach ($testDetails as $idTest => $single_detail) {
                if (isset($single_detail['num_result'])) {
                    $testDetails[$idTest]['average'] /= $testDetails[$idTest]['num_result'];
                }
            }
            reset($testDetails);
        }
        $reports_score = &$this->courseReportManager->getReportsScores(
            (isset($includedTestReportId) && is_array($includedTestReportId) ? array_diff($reports_id, $includedTestReportId) : $reports_id)
        );

        $reportDetails = [];
        foreach ($reports_score as $id_report => $users_result) {
            foreach ($users_result as $idUser => $single_report) {
                if ($single_report['score_status'] == 'valid') {
                    if (!isset($reportDetails[$id_report]['max_score'])) {
                        $reportDetails[$id_report]['max_score'] = $single_report['score'];
                    } elseif ($single_report['score'] > $reportDetails[$id_report]['max_score']) {
                        $reportDetails[$id_report]['max_score'] = $single_report['score'];
                    }

                    if (!isset($reportDetails[$id_report]['min_score'])) {
                        $reportDetails[$id_report]['min_score'] = $single_report['score'];
                    } elseif ($single_report['score'] < $reportDetails[$id_report]['min_score']) {
                        $reportDetails[$id_report]['min_score'] = $single_report['score'];
                    }

                    if (!isset($reportDetails[$id_report]['num_result'])) {
                        $reportDetails[$id_report]['num_result'] = 1;
                    } else {
                        ++$reportDetails[$id_report]['num_result'];
                    }

                    if (!isset($reportDetails[$id_report]['average'])) {
                        $reportDetails[$id_report]['average'] = $single_report['score'];
                    } else {
                        $reportDetails[$id_report]['average'] += $single_report['score'];
                    }
                }
            }
        }
        foreach ($reportDetails as $id_report => $single_detail) {
            if (isset($single_detail['num_result'])) {
                $reportDetails[$id_report]['average'] /= $reportDetails[$id_report]['num_result'];
            }
        }
        reset($reportDetails);

        if (!empty($students_info)) {
            foreach ($students_info as $idstUser => $userInfo) {
                $user_name = ($userInfo[ACL_INFO_LASTNAME] . $userInfo[ACL_INFO_FIRSTNAME]
                    ? $userInfo[ACL_INFO_LASTNAME] . ' ' . $userInfo[ACL_INFO_FIRSTNAME]
                    : $aclMan->relativeId($userInfo[ACL_INFO_USERID]));
                $csv .= '"' . $user_name . '"';

                foreach ($this->model->getCourseReports() as $infoReport) {
                    switch ($infoReport->getSourceOf()) {
                        case CoursereportLms::SOURCE_OF_TEST:
                            $id = $infoReport->getIdSource();
                            if (isset($testsScore[$id][$idstUser])) {
                                switch ($testsScore[$id][$idstUser]['score_status']) {
                                    case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                                        $csv .= ';"-"';
                                        break;
                                    case CoursereportLms::TEST_STATUS_NOT_CHECKED:
                                        $csv .= ';"' . $lang->def('_NOT_CHECKED') . '"';

                                        if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_CHECKED])) {
                                            $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_CHECKED] = 1;
                                        } else {
                                            ++$testDetails[$id][CoursereportLms::TEST_STATUS_NOT_CHECKED];
                                        }

                                        break;
                                    case CoursereportLms::TEST_STATUS_PASSED:
                                        $csv .= ';"' . $lang->def('_PASSED') . '"';
                                        if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED])) {
                                            $testDetails[$id][CoursereportLms::TEST_STATUS_PASSED] = 1;
                                        } else {
                                            ++$testDetails[$id][CoursereportLms::TEST_STATUS_PASSED];
                                        }

                                        break;
                                    case CoursereportLms::TEST_STATUS_NOT_PASSED:
                                        $csv .= ';"' . $lang->def('_NOT_PASSED') . '"';
                                        if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                            $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED] = 1;
                                        } else {
                                            ++$testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED];
                                        }

                                        break;
                                    case CoursereportLms::TEST_STATUS_VALID:
                                        $score = $testsScore[$id][$idstUser]['score'];

                                        if ($score >= $infoReport->getRequiredScore()) {
                                            if ($score == $testDetails[$id]['max_score']) {
                                                $csv .= ';"' . $score . ' ' . $tt . '"';
                                            } else {
                                                $csv .= ';"' . $score . ' ' . $tt . '"';
                                            }

                                            if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_PASSED])) {
                                                $testDetails[$id][CoursereportLms::TEST_STATUS_PASSED] = 1;
                                            } else {
                                                ++$testDetails[$id][CoursereportLms::TEST_STATUS_PASSED];
                                            }
                                        } else {
                                            if ($score == $testDetails[$id]['max_score']) {
                                                $csv .= ';"' . $score . ' ' . $tt . '"';
                                            } else {
                                                $csv .= ';"' . $score . ' ' . $tt . '"';
                                            }

                                            if (!isset($testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                                $testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED] = 1;
                                            } else {
                                                ++$testDetails[$id][CoursereportLms::TEST_STATUS_NOT_PASSED];
                                            }
                                        }
                                        if (isset($testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA]) && isset($testDetails[$id]['average'])) {
                                            $testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA] += pow(($testsScore[$id][$idstUser]['score'] - $testDetails[$id]['average']), 2);
                                        } else {
                                            $testDetails[$id][CoursereportLms::TEST_STATUS_VARIANZA] = pow(($testsScore[$id][$idstUser]['score'] - $testDetails[$id]['average']), 2);
                                        }

                                        break;
                                    default:
                                        $csv .= ';"-"';
                                }
                            }

                            break;
                        case CoursereportLms::SOURCE_OF_SCOITEM:
                            $query_report = "
						SELECT *
						FROM %lms_scorm_tracking
						WHERE idscorm_item = '" . $infoReport->getIdSource() . "' AND idUser = '" . $idstUser . "'
						";
                            $report = sql_fetch_assoc(sql_query($query_report));
                            if ($report['score_raw'] == null) {
                                $report['score_raw'] = '-';
                            }

                            $id_track = (isset($report['idscorm_tracking']) ? $report['idscorm_tracking'] : 0);
                            $query_report = "
						SELECT *
						FROM %lms_scorm_tracking_history
						WHERE idscorm_tracking = '" . $id_track . "'
						";

                            $query = sql_query($query_report);
                            $num = sql_num_rows($query);
                            $csv .= ';"' . $report['score_raw'] . '"';

                            break;
                        case CoursereportLms::SOURCE_OF_ACTIVITY:
                        case CoursereportLms::SOURCE_OF_FINAL_VOTE:
                            if (isset($reports_score[$infoReport->getIdReport()][$idstUser])) {
                                switch ($reports_score[$infoReport->getIdReport()][$idstUser]['score_status']) {
                                    case CoursereportLms::TEST_STATUS_NOT_COMPLETED:
                                        $csv .= ';"-"';
                                        break;
                                    case CoursereportLms::TEST_STATUS_VALID:
                                        if ($reports_score[$infoReport->getIdReport()][$idstUser]['score'] >= $infoReport->getRequiredScore()) {
                                            if ($reports_score[$infoReport->getIdReport()][$idstUser]['score'] == $infoReport->getMaxScore()) {
                                                $csv .= ';"' . $reports_score[$infoReport->getIdReport()][$idstUser]['score'] . '"';
                                            } else {
                                                $csv .= ';"' . $reports_score[$infoReport->getIdReport()][$idstUser]['score'] . '"';
                                            }

                                            // Count passed
                                            if (!isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED])) {
                                                $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED] = 1;
                                            } else {
                                                ++$reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_PASSED];
                                            }
                                        } else {
                                            $csv .= ';"' . $reports_score[$infoReport->getIdReport()][$idstUser]['score'] . '"';

                                            // Count not passed
                                            if (!isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED])) {
                                                $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED] = 1;
                                            } else {
                                                ++$reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_NOT_PASSED];
                                            }
                                        }
                                        if (isset($reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_VARIANZA]) && isset($reportDetails[$infoReport->getIdReport()]['average'])) {
                                            $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_VARIANZA] += round(pow(($reports_score[$infoReport->getIdReport()][$idstUser]['score'] - $reportDetails[$infoReport->getIdReport()]['average']), 2), 2);
                                        } else {
                                            $reportDetails[$infoReport->getIdReport()][CoursereportLms::TEST_STATUS_VARIANZA] = round(pow(($reports_score[$infoReport->getIdReport()][$idstUser]['score'] - $reportDetails[$infoReport->getIdReport()]['average']), 2), 2);
                                        }

                                        break;
                                }
                            } else {
                                $csv .= ';"-"';
                            }

                            break;
                    }
                }
                $csv .= "\n";
            }
        }

        $file_name = date('YmdHis') . '_report_export.csv';

        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.download.php');
        sendStrAsFile($csv, $file_name);
    }

    public function testQuestion()
    {
        checkPerm('view', true, $this->_mvc_name);
        $responseValue = [];
        $undo = FormaLms\lib\Get::pReq('undo', DOTY_MIXED, false);

        if ($undo) {
            Util::jump_to('index.php?r=coursereport/coursereport');
        }

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.question.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/track.test.php');

        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/appLms/views/coursereport/js/testquestion.js', true, true);
        Util::get_css(FormaLms\lib\Get::rel_path('base') . '/appLms/views/coursereport/css/testquestion.css', true, true);

        $lang = FormaLanguage::createInstance('coursereport', 'lms');

        $idTest = importVar('id_test', true, 0);

        $testMan = new GroupTestManagement();

        $lev = false;
        $type_filter = FormaLms\lib\Get::gReq('type_filter', DOTY_MIXED, false);

        if (isset($type_filter) && $type_filter != null) {
            $lev = $type_filter;
        }

        $students = getSubscribed((int)$this->idCourse, false, $lev, true, false, false, true);
        $id_students = array_keys($students);

        $test_info = $testMan->getTestInfo([$idTest]);

        $responseValue['title'] = array_key_exists($idTest, $test_info) ? $test_info[$idTest]['title'] : '';

        $answersNew = [];
        $tracks = [];
        $quests = Question::getTestQuestsFromTest($idTest);

        foreach ($quests as $quest) {
            $resAnswers = Question::getTestQuestAnswerFromQuestAndStudents($quest['idQuest'], $id_students);

            foreach ($resAnswers as $k => $resAnswer) {
                $answersNew[$k] = $resAnswer;
            }

            if ($quest['type_quest'] == 'choice_multiple' || $quest['type_quest'] == 'choice' || $quest['type_quest'] == 'inline_choice') {
                $answersNew[$quest['idQuest']][0]['idAnswer'] = 0;
                $answersNew[$quest['idQuest']][0]['is_correct'] = 0;
                $answersNew[$quest['idQuest']][0]['answer'] = $lang->def('_NO_ANSWER');
            }
        }

        $validIdTracks = Track_Test::getValidTestTrackFromTestAndUsers($idTest, $id_students);
        foreach ($validIdTracks as $validIdTrack) {
            $trackAnswers = Track_Test::getTestTrackAnswersFromTrack($validIdTrack);

            foreach ($trackAnswers as $trackAnswer) {
                $tracks[$validIdTrack][$trackAnswer['idQuest']][$trackAnswer['idAnswer']]['more_info'] = $trackAnswer['more_info'];
            }
        }

        $total_play = Track_Test::getValidTotalPlaysTestTrackFromTestAndUsers($idTest, $id_students);

        foreach ($quests as $quest) {
            $question = [];
            $answersArray = [];

            switch ($quest['type_quest']) {
                case 'inline_choice':
                case 'choice_multiple':
                case 'choice':
                    $question['title'] = str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST'));

                    foreach ($answersNew[$quest['idQuest']] as $answer) {
                        $answerObj = [];
                        $cont = [];

                        if ($answer['is_correct']) {
                            $answerObj['showIcon'] = true;
                        } else {
                            $answerObj['showIcon'] = false;
                        }

                        $answerObj['title'] = $answer['answer'];

                        $answer_given = 0;
                        reset($tracks);
                        $i = 0;

                        foreach ($tracks as $track) {
                            ++$i;
                            if (isset($track[$quest['idQuest']][$answer['idAnswer']])) {
                                ++$answer_given;
                            } elseif (!isset($track[$quest['idQuest']]) && $answer['idAnswer'] == 0) {
                                ++$answer_given;
                            }
                        }
                        if ($answer['idAnswer'] == 0 && $i < $total_play) {
                            //			if ($i < $total_play) {
                            $answer_given += ($total_play - $i);
                        }

                        $percentage = $total_play ? ($answer_given / $total_play) * 100 : 0;

                        $percentage = number_format($percentage, 2);

                        $answerObj['percent'] = number_format($percentage, 2);

                        $answersArray[] = $answerObj;
                    }

                    $question['answers'] = $answersArray;

                    break;
                case 'upload':
                case 'extended_text':
                    $question['title'] = str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST'));
                    $question['idQuest'] = $quest['idQuest'];
                    $question['idTest'] = $idTest;

                    break;

                case 'text_entry':
                    $question['title'] = str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_CORRECT_TXT'));

                    foreach ($answersNew[$quest['idQuest']] as $answer) {
                        $answerObj = [];

                        $answer_correct = 0;

                        foreach ($tracks as $track) {
                            if ($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['answer']) {
                                ++$answer_correct;
                            }
                        }

                        $percentage = $total_play ? ($answer_correct / $total_play) * 100 : 0;

                        $percentage = number_format($percentage, 2);

                        $answerObj['percent'] = number_format($percentage, 2);

                        $answersArray[] = $answerObj;
                    }

                    $question['answers'] = $answersArray;

                    break;

                case 'associate':
                    $question['title'] = str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_CORRECT_ASS'));

                    foreach ($answersNew[$quest['idQuest']] as $answer) {
                        $answerObj = [];

                        $answerObj['title'] = $answer['answer'];

                        $answer_correct = 0;

                        foreach ($tracks as $track) {
                            if ($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['is_correct']) {
                                ++$answer_correct;
                            }
                        }

                        $percentage = $total_play ? ($answer_correct / $total_play) * 100 : 0;
                        //echo "risp corrette: " . $answer_correct . " totale: " . $total_play;

                        $percentage = number_format($percentage, 2);

                        $answerObj['percent'] = $percentage;

                        $answersArray[] = $answerObj;
                    }

                    $question['answers'] = $answersArray;

                    break;
                default:
                    break;
            }

            $question['type'] = $quest['type_quest'];
            $responseValue['questions'][] = $question;
        }

        //echo json_encode($responseValue);
        $this->render('testquestion', ['data' => $responseValue]);
    }

    public function extendedQuestDetails()
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/track.test.php');
        $idTest = FormaLms\lib\Get::pReq('id_test', DOTY_MIXED, 0);
        $idQuest = FormaLms\lib\Get::pReq('id_quest', DOTY_MIXED, 0);

        $result = ['id_quest' => $idQuest];

        $idTracks = Track_Test::getIdTracksFromTest($idTest);

        foreach ($idTracks as $idTrack) {
            $textEntries = TextEntry_Question::getTextEntryFromIdTrackAndIdQuest($idTrack, $idQuest);
            foreach ($textEntries as $textEntry) {
                $result['answers'][] = ['answer' => $textEntry];
            }
        }

        echo $this->json->encode($result);
    }

    public function fileUploadQuestDetails()
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/track.test.php');
        $idTest = FormaLms\lib\Get::pReq('id_test', DOTY_MIXED, 0);
        $idQuest = FormaLms\lib\Get::pReq('id_quest', DOTY_MIXED, 0);

        $result = ['id_quest' => $idQuest];

        $idTracks = Track_Test::getIdTracksFromTest($idTest);

        foreach ($idTracks as $idTrack) {
            $textEntries = TextEntry_Question::getTextEntryFromIdTrackAndIdQuest($idTrack, $idQuest);
            foreach ($textEntries as $textEntry) {
                $result['answers'][] = ['answer' => $textEntry, 'filePath' => 'index.php?modname=question&amp;op=quest_download&type_quest=upload&id_quest=' . $idQuest . '&id_track=' . $idTrack];
            }
        }

        echo $this->json->encode($result);
    }

    public function testQuestionOld()
    {
        $responseValue = [];

        checkPerm('view', true, $this->_mvc_name);

        YuiLib::load(['animation' => 'my_animation.js']);
        addJs($GLOBALS['where_lms_relative'] . '/modules/coursereport/', 'ajax.coursereport.js');

        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.table.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');

        $lang = FormaLanguage::createInstance('coursereport', 'lms');

        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        $out->add('<script type="text/javascript">'
            // 			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/modules/coursereport/ajax.coursereport.php\'); '
            // // 			.' setup_coursereport(\''.$GLOBALS['where_lms_relative'].'/ajax.server.php?id_quest=3&id_test=3\'); '
            . ' setup_coursereport(\'' . $GLOBALS['where_lms_relative'] . '/ajax.server.php?plf=lms&mn=coursereport&\'); '
            . '</script>', 'page_head');

        $idTest = importVar('id_test', true, 0);

        $testMan = new GroupTestManagement();

        $lev = false;
        $type_filter = FormaLms\lib\Get::gReq('type_filter', DOTY_MIXED, false);

        if (isset($type_filter) && $type_filter != null) {
            $lev = $type_filter;
        }

        $students = getSubscribed((int)$this->idCourse, false, $lev, true, false, false, true);
        $id_students = array_keys($students);

        $quests = [];
        $answers = [];
        $tracks = [];

        $test_info = $testMan->getTestInfo([$idTest]);

        $page_title = [
            'index.php?r=lms/coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            $test_info[$idTest]['title'],
        ];

        $out->add(
            getTitleArea($page_title, 'coursereport')
            . '<div class="std_block">'
        );

        $query_test = 'SELECT title'
            . ' FROM %lms_test'
            . " WHERE idTest = '" . $idTest . "'";

        [$titolo_test] = sql_fetch_row(sql_query($query_test));

        $query_quest = 'SELECT idQuest, type_quest, title_quest'
            . ' FROM %lms_testquest'
            . " WHERE idTest = '" . $idTest . "'"
            . ' ORDER BY sequence';

        $result_quest = sql_query($query_quest);

        while (list($id_quest, $type_quest, $title_quest) = sql_fetch_row($result_quest)) {
            $quests[$id_quest]['idQuest'] = $id_quest;
            $quests[$id_quest]['type_quest'] = $type_quest;
            $quests[$id_quest]['title_quest'] = $title_quest;

            //		$query_answer =	"SELECT idAnswer, is_correct, answer"
            //						." FROM ".$GLOBALS['prefix_lms']."_testquestanswer"
            //						." WHERE idQuest = '".$id_quest."'"
            //						." ORDER BY sequence";

            $query_answer = 'SELECT tqa.idAnswer, tqa.is_correct, tqa.answer'
                . ' FROM %lms_testquestanswer AS tqa'
                . ' LEFT JOIN'
                . ' %lms_testtrack_answer tta ON tqa.idAnswer = tta.idAnswer'
                . ' LEFT JOIN'
                . ' %lms_testtrack tt ON tt.idTrack = tta.idTrack'
                . " WHERE tqa.idQuest = '" . $id_quest . "'";
            $query_answer .= ' and tt.idUser in (' . implode(',', $id_students) . ')';
            $query_answer .= ' ORDER BY tqa.sequence';

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

        $query_track = 'SELECT idTrack'
            . ' FROM %lms_testtrack'
            . " WHERE idTest = '" . $idTest . "'"
            . " AND score_status = 'valid'"
            . ' AND idUser in (' . implode(',', $id_students) . ')';

        $result_track = sql_query($query_track);

        while (list($id_track) = sql_fetch_row($result_track)) {
            $query_track_answer = 'SELECT idQuest, idAnswer, more_info'
                . ' FROM %lms_testtrack_answer'
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

        $query_total_play = 'SELECT COUNT(*)'
            . ' FROM %lms_testtrack'
            . " WHERE idTest = '" . $idTest . "'"
            . " AND score_status = 'valid'"
            . ' AND idUser in (' . implode(',', $id_students) . ')';

        [$total_play] = sql_fetch_row(sql_query($query_total_play));

        /*if ($total_play == 0) {
                    $query_total_play =     "SELECT COUNT(*)"
                                                    ." FROM ".$GLOBALS['prefix_lms']."_testtrack"
                                                    ." WHERE idTest = '".$idTest."' AND score_status = CoursereportLms::TEST_STATUS_NOT_CHECKED";
                    list($total_play2) = sql_fetch_row(sql_query($query_total_play));
    $total_play += $total_play2;

            }*/
        //print_r($tracks);
        foreach ($quests as $quest) {
            switch ($quest['type_quest']) {
                case 'inline_choice':
                case 'choice_multiple':
                case 'choice':
                    $cont_h = [
                        $lang->def('_ANSWER'),
                        $lang->def('_PERCENTAGE'),
                    ];
                    $type_h = [
                        '', 'image nowrap',
                    ];

                    $tb = new Table(0, str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST')));
                    $tb->setColsStyle($type_h);
                    $tb->addHead($cont_h);

                    foreach ($answers[$quest['idQuest']] as $answer) {
                        $cont = [];

                        if ($answer['is_correct']) {
                            $txt = '<img src="' . getPathImage('lms') . 'standard/publish.png" alt="' . $lang->def('_ANSWER_CORRECT') . '" title="' . $lang->def('_ANSWER_CORRECT') . '" align="left" /> ';
                        } else {
                            $txt = '';
                        }

                        $cont[] = '<p>' . $txt . ' ' . $answer['answer'] . '</p>';

                        $answer_given = 0;
                        reset($tracks);
                        $i = 0;
                        foreach ($tracks as $track) {
                            ++$i;
                            if (isset($track[$quest['idQuest']][$answer['idAnswer']])) {
                                ++$answer_given;
                            } elseif (!isset($track[$quest['idQuest']]) && $answer['idAnswer'] == 0) {
                                ++$answer_given;
                            }
                        }
                        if ($answer['idAnswer'] == 0 && $i < $total_play) {
                            //			if ($i < $total_play) {
                            $answer_given += ($total_play - $i);
                        }
                        if ($total_play > 0) {
                            $percentage = ($answer_given / $total_play) * 100;
                        } else {
                            $percentage = 0;
                        }

                        $percentage = number_format($percentage, 2);

                        $cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

                        $tb->addBody($cont);
                    }

                    $out->add($tb->getTable() . '<br/>');
                    break;

                case 'upload':
                case 'extended_text':
                    $out->add('<div>');
                    $out->add('<p><a href="#" onclick="getQuestDetail(' . $quest['idQuest'] . ', ' . $idTest . ', \'' . $quest['type_quest'] . '\'); return false;" id="more_quest_' . $quest['idQuest'] . '"><img src="' . getPathImage('fw') . 'standard/more.gif" alt="' . $lang->def('_MORE_INFO') . '" />' . str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST')) . '</a></p>');
                    $out->add('<p><a href="#" onclick="closeQuestDetail(' . $quest['idQuest'] . '); return false;" id="less_quest_' . $quest['idQuest'] . '" style="display:none"><img src="' . getPathImage('fw') . 'standard/less.gif" alt="' . $lang->def('_CLOSE') . '" />' . str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_LIST')) . '</a></p>');
                    $out->add('</div>');
                    $out->add('<div id="quest_' . $quest['idQuest'] . '">');
                    $out->add('</div>');
                    break;

                case 'text_entry':
                    $cont_h = [
                        $lang->def('_PERCENTAGE_CORRECT'),
                    ];
                    $type_h = ['align-center'];

                    $tb = new Table(0, str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_CORRECT_TXT')));
                    $tb->setColsStyle($type_h);
                    $tb->addHead($cont_h);

                    foreach ($answers[$quest['idQuest']] as $answer) {
                        $cont = [];

                        $answer_correct = 0;

                        foreach ($tracks as $track) {
                            if ($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['answer']) {
                                ++$answer_correct;
                            }
                        }

                        $percentage = ($answer_correct / $total_play) * 100;

                        $percentage = number_format($percentage, 2);

                        $cont[] = Util::draw_progress_bar($percentage, true, false, false, false, false);

                        $tb->addBody($cont);
                    }

                    $out->add($tb->getTable() . '<br/>');
                    break;

                case 'associate':
                    $cont_h = [
                        $lang->def('_ANSWER'),
                        $lang->def('_PERCENTAGE_CORRECT'),
                    ];
                    $type_h = ['', 'align-center'];

                    $tb = new Table(0, str_replace('[title]', $quest['title_quest'], $lang->def('_TABLE_QUEST_CORRECT_ASS')));
                    $tb->setColsStyle($type_h);
                    $tb->addHead($cont_h);

                    foreach ($answers[$quest['idQuest']] as $answer) {
                        $cont = [];

                        $cont[] = $answer['answer'];

                        $answer_correct = 0;

                        foreach ($tracks as $track) {
                            if ($track[$quest['idQuest']][$answer['idAnswer']]['more_info'] === $answer['is_correct']) {
                                ++$answer_correct;
                            }
                        }

                        $percentage = ($answer_correct / $total_play) * 100;
                        echo 'risp corrette: ' . $answer_correct . ' totale: ' . $total_play;

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

    public function showchart()
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/charts.test.php');

        $idTest = FormaLms\lib\Get::req('id_test', DOTY_INT, -1);
        $idUser = FormaLms\lib\Get::req('id_user', DOTY_INT, -1);
        $chartType = FormaLms\lib\Get::req('chart_type', DOTY_STRING, 'column');

        $lang = FormaLanguage::createInstance('coursereport', 'lms');
        $aclMan = \FormaLms\lib\Forma::getAclManager();
        $userInfo = $aclMan->getUser($idUser, false);
        [$title] = sql_fetch_row(sql_query('SELECT title FROM %lms_test WHERE idTest=' . (int)$idTest));
        $backUrl = 'index.php?r=lms/coursereport/testvote&id_test=' . (int)$idTest;
        $backUi = getBackUi($backUrl, $lang->def('_BACK'));

        $page_title = [
            'index.php?r=lms/coursereport/coursereport' => $lang->def('_COURSEREPORT', 'menu_course'),
            $backUrl => strip_tags($title),
            $aclMan->relativeId($userInfo[ACL_INFO_USERID]),
        ];
        cout(getTitleArea($page_title, 'coursereport', $lang->def('_TH_ALT')));
        cout('<div class="stdblock">');
        cout($backUi);

        cout('<div><h2>' . $lang->def('_USER_DETAILS') . '</h2>');
        cout('<div class="form_line_l"><p><label class="floating">' . $lang->def('_USERNAME') . ':&nbsp;</label></p>' . $aclMan->relativeId($userInfo[ACL_INFO_USERID]) . '</div>');
        cout('<div class="form_line_l"><p><label class="floating">' . $lang->def('_LASTNAME') . ':&nbsp;</label></p>' . $userInfo[ACL_INFO_LASTNAME] . '</div>');
        cout('<div class="form_line_l"><p><label class="floating">' . $lang->def('_FIRSTNAME') . ':&nbsp;</label></p>' . $userInfo[ACL_INFO_FIRSTNAME] . '</div>');
        cout('<div class="no_float"></div>');

        $charts = new Test_Charts($idTest, $idUser);
        $charts->render($chartType, true);

        cout($backUi);
        cout('</div>');
    }
}
