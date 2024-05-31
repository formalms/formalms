<?php

use FormaLms\lib\Forma;

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

class CourseReportManager
{
    /** @var int */
    protected $idCourse;

    public function __construct($idCourse)
    {
        $this->idCourse = (int)$idCourse;
    }

    /**
     * @return int
     */
    public function getIdCourse()
    {
        return $this->idCourse;
    }

    public function getNextSequence()
    {
        $query_seq = "
		SELECT sequence
		FROM %lms_coursereport 
		WHERE id_course = '" . $this->idCourse . "' AND source_of = 'final_vote'";
        list($seq) = sql_fetch_row(sql_query($query_seq));

        $query_seq = "
		UPDATE %lms_coursereport 
		SET sequence = sequence + 1 
		WHERE id_course = '" . $this->idCourse . "' AND source_of = 'final_vote'";
        sql_query($query_seq);

        return $seq;
    }

    public function &getStudentId()
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.course.php');

        $course_user = Man_Course::getIdUserOfLevel($this->idCourse, 3);

        return $course_user;
    }

    public function &getTest()
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.orgchart.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/learning.test.php');

        $org_man = new OrganizationManagement($this->idCourse);
        $tests = &$org_man->getAllLoAbsoluteIdWhereType(Learning_Test::getTestTypes());

        return $tests;
    }

    public function initializeCourseReport($id_tests)
    {
        $this->addFinalVoteToReport();
        $this->addTestToReport($id_tests, 1);
    }

    public function addFinalVoteToReport()
    {
        $query_test = "INSERT IGNORE INTO %lms_coursereport 
		( id_course, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
			'" . $this->idCourse . "',
			'100', 
			'60',
			'0',
			'false',
			'false',
			'final_vote', 
			'0',
			'1'
		)";
        sql_query($query_test);
    }

    public function addTestToReport($id_tests, $from_sequence)
    {
        $test_man = new GroupTestManagement();

        $plus = count($id_tests);
        $query_seq = '
		UPDATE %lms_coursereport 
		SET sequence = sequence + ' . $plus . " 
		WHERE id_course = '" . $this->idCourse . "'";
        sql_query($query_seq);

        $test_info = $test_man->getTestInfo($id_tests);
        foreach ($id_tests as $id_test => $title) {
            $query_test = "
			INSERT IGNORE INTO %lms_coursereport 
			( id_course, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
				'" . $this->idCourse . "',
				'" . $test_man->getMaxScore($id_test) . "', 
				'" . $test_man->getRequiredScore($id_test) . "',
				'100',
				'" . ($test_info[$id_test]['show_score'] == 1 || $test_info[$id_test]['show_score_cat'] == 1 ? 'true' : 'false') . "',
				'true',
				'test',
				'" . $id_test . "',
				'" . $from_sequence++ . "'
			)";
            sql_query($query_test);
        }
    }

    public function removeDuplicatedReports($idCourse)
    {
        $report_man = new CourseReportManager($idCourse);
        $org_tests = &$report_man->getTest();
        foreach ($org_tests as $org_test) {
        }
    }

    public function testReportExists($idCourse, $idTest)
    {
        return count($this->getTestReports($idCourse, $idTest)) > 0;
    }

    public function getTestReports($idCourse, $idTest)
    {
        $query = "SELECT * from %lms_coursereport WHERE id_course=${idCourse} AND id_source=${$idTest} AND source_of='test'";

        $result = \FormaLms\db\DbConn::getInstance()->query($query);

        $reports = [];
        foreach ($result as $item) {
            $reports[] = $item;
        }

        return $reports;
    }

    public function updateTestReport($id_tests)
    {
        $test_man = new GroupTestManagement();
        $tests_list = $test_man->getTestInfo($id_tests);

        foreach ($tests_list as $id_test => $test_info) {
            $query_test = "
			UPDATE %lms_coursereport 
			SET required_score = '" . $test_info['point_required'] . "' "
                . ($test_info['order_type'] != 2 ? ', '
                    . " max_score = '" . $test_man->getMaxScore($id_test) . "' " : '') . ', '
                . ' required_score = ' . $test_man->getRequiredScore($id_test) . ' '
                . " WHERE id_course = '" . $this->idCourse . "' AND 
				source_of = 'test' AND 
				id_source = '" . $id_test . "'";
            sql_query($query_test);
        }
    }

    public function delTestToReport($id_tests)
    {
        if (empty($id_tests)) {
            return;
        }

        $query_test = "DELETE FROM %lms_coursereport 
		WHERE id_course = '" . $this->idCourse . "' AND
			source_of = 'test' AND 
			id_report IN ( " . implode(',', $id_tests) . ' )';
        sql_query($query_test);

        $this->repairSequence();
    }

    public function repairSequence()
    {
        $query_select = "SELECT id_report FROM %lms_coursereport WHERE id_course = '" . $this->idCourse . "' AND source_of <> 'final_vote' ORDER BY sequence";
        $re_select = sql_query($query_select);
        $i = 1;
        foreach ($re_select as $row) {
            [$id_report] = array_values($row);

            $query_seq = "UPDATE %lms_coursereport SET sequence = '" . $i++ . "' WHERE id_course = '" . $this->idCourse . "' AND id_report = '$id_report'";
            sql_query($query_seq);
        }
        $query_seq = "
		UPDATE %lms_coursereport  SET sequence = '" . $i . "' WHERE id_course = '" . $this->idCourse . "' AND source_of = 'final_vote'";
        sql_query($query_seq);
    }


    public function getReportsScores($reports_id, $id_user = false)
    {
        $data = $this->getReportsScoresAndDetails($reports_id, $id_user);

        return $data['reportScores'];
    }

    public function getReportsScoresAndDetails($reports_id, $id_user = false)
    {
        $data = [
            'reportScores' => [],
            'reportDetails' => [],
        ];
        if (empty($reports_id) || !is_array($reports_id)) {
            return $data;
        }
        if ($id_user !== false && !is_array($id_user)) {
            $id_user = [$id_user];
        }

        $query_scores = '
			SELECT id_report, id_user, date_attempt, score, score_status, comment 
			FROM %lms_coursereport_score 
			WHERE id_report IN ( ' . implode(',', $reports_id) . ' )';
        if ($id_user !== false && !empty($id_user)) {
            $query_scores .= ' AND id_user IN ( ' . implode(',', $id_user) . ' )';
        }
        $re_scores = sql_query($query_scores);
        foreach ($re_scores as $test_data) {
            if (!$test_data['date_attempt']) {
                $test_data['date_attempt'] = '';
            }

            $data['reportDetails'][$test_data['id_report']] = [];

            if ($test_data['score_status'] == 'valid') {
                // max
                if (!isset($data['reportDetails'][$test_data['id_report']]['max_score'])) {
                    $data['reportDetails'][$test_data['id_report']]['max_score'] = $test_data['score'];
                } elseif ($test_data['score'] > $data['reportDetails'][$test_data['id_report']]['max_score']) {
                    $data['reportDetails'][$test_data['id_report']]['max_score'] = $test_data['score'];
                }

                // min
                if (!isset($data['reportDetails'][$test_data['id_report']]['min_score'])) {
                    $data['reportDetails'][$test_data['id_report']]['min_score'] = $test_data['score'];
                } elseif ($test_data['score'] < $data['reportDetails'][$test_data['id_report']]['min_score']) {
                    $data['reportDetails'][$test_data['id_report']]['min_score'] = $test_data['score'];
                }

                //number of valid score
                if (!isset($data['reportDetails'][$test_data['id_report']]['num_result'])) {
                    $data['reportDetails'][$test_data['id_report']]['num_result'] = 1;
                } else {
                    ++$data['reportDetails'][$test_data['id_report']]['num_result'];
                }

                // average
                if (!isset($data['reportDetails'][$test_data['id_report']]['maxScore'])) {
                    $data['reportDetails'][$test_data['id_report']]['maxScore'] = $test_data['score'];
                } else {
                    $data['reportDetails'][$test_data['id_report']]['maxScore'] += $test_data['score'];
                }

                $data['reportDetails'][$test_data['id_report']]['average'] = $data['reportDetails'][$test_data['id_report']]['maxScore'] / $data['reportDetails'][$test_data['id_report']]['num_result'];
            }

            $data['reportScores'][$test_data['id_report']][$test_data['id_user']] = $test_data;
        }

        return $data;
    }

    public function saveReportScore($id_report, $users_scores, $date_attempts, $comments)
    {
        $old_scores = $this->getReportsScores([$id_report]);
        $re = true;
        foreach ($users_scores as $idst_user => $score) {
            if (!isset($old_scores[$id_report][$idst_user])) {
                $query_scores = "
				INSERT IGNORE INTO %lms_coursereport_score
				( id_report, id_user, date_attempt, score, score_status, comment ) VALUES ( 
					'" . $id_report . "', 
					'" . $idst_user . "', 
					'" . Format::dateDb($date_attempts[$idst_user], 'date') . "', 
					'" . $score . "', 
					'valid',
					'" . $comments[$idst_user] . "' )";
            } else {
                $query_scores = "
				UPDATE %lms_coursereport_score
				SET date_attempt = '" . Format::dateDb($date_attempts[$idst_user], 'date') . "', 
					score = '" . $score . "', 
					score_status = 'valid',
					comment = '" . $comments[$idst_user] . "'
					" . ($old_scores[$id_report][$idst_user] != $score
                    ? ", score_status = 'valid'"
                    : '') . " 
				WHERE id_report = '" . $id_report . "' AND id_user = '" . $idst_user . "'";
            }
            $re = sql_query($query_scores);
        }

        return $re;
    }

    /**
     * @param int $id_report the id of the report to manage
     * @param array $id_user filter for user
     *
     * @return bool true if success false otherwise
     */
    public function roundReportScore($id_report, $id_users = false)
    {
        $re = true;
        $query_scores = '
		SELECT id_user, score, score_status
		FROM %lms_coursereport_score
		WHERE id_report = ' . $id_report . ' ';
        if ($id_users !== false) {
            $query_scores .= ' AND idUser IN ( ' . implode(',', $id_users) . ' ) ';
        }
        $re_scores = sql_query($query_scores);
        foreach ($re_scores  as $row) {
            [$user, $score, $score_status] = array_values($row);
            if ($score_status == 'valid') {
                $query_scores = "
        UPDATE %lms_coursereport_score
        SET score = '" . round($score) . "'
        WHERE id_report = '" . $id_report . "' AND id_user = '" . $user . "'";
                $re &= sql_query($query_scores);
            }
        }

        return $re;
    }

    public function deleteReportScore($id_report)
    {
        $query_scores = "
		DELETE FROM %lms_coursereport_score
		WHERE id_report = '" . $id_report . "'";

        return sql_query($query_scores);
    }

    public function deleteReport($id_report)
    {
        $query_scores = "
		DELETE FROM %lms_coursereport 
		WHERE id_report = '" . $id_report . "'";
        $re = sql_query($query_scores);

        $this->repairSequence();

        return $re;
    }

    public function checkActivityData(&$source)
    {
        if ($source['required_score'] > $source['max_score']) {
            return [
                'error' => true,
                'message' => Lang::t('_REQUIRED_MUST_BE_LESS_THEN_MAX', 'coursereport', 'lms'),
            ];
        }

        return ['error' => false, 'message' => ''];
    }

    public function addActivity($id_course, &$source)
    {
        $query_ins_report = "
		INSERT IGNORE INTO %lms_coursereport 
		( id_course, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
			'" . $id_course . "', 
			'" . $source['title'] . "', 
			'" . $source['max_score'] . "', 
			'" . $source['required_score'] . "', 
			'" . $source['weight'] . "', 
			'" . $source['show_to_user'] . "', 
			'" . $source['use_for_final'] . "', 
			'activity',
			'0',
			'" . $this->getNextSequence() . "'
		)";

        return sql_query($query_ins_report);
    }

    public function updateActivity($id_report, $id_course, &$source)
    {
        if ($source instanceof ReportLms) {
            if ($source->getSourceOf()) {
                $source_of = $source->getSourceOf();
            } else {
                $source_of = CoursereportLms::SOURCE_OF_ACTIVITY;
            }

            if ($source->getIdSource()) {
                $id_source = $source->getIdSource();
            } else {
                $id_source = '0';
            }

            $query_upd_report = "
		UPDATE %lms_coursereport
		SET title = '" . $source->getTitle() . "',
			weight = '" . $source->getWeight() . "',
			max_score = '" . $source->getMaxScore() . "',
			required_score = '" . $source->getRequiredScore() . "', 
			use_for_final = '" . $source->isUseForFinalToString() . "',  
			show_to_user = '" . $source->isShowToUserToString() . "' 
		WHERE id_course = '" . $id_course . "' AND id_report = '" . $id_report . "' 
			AND source_of = '" . $source_of . "' AND id_source = '" . $id_source . "'";
        } else {
            $source_of = isset($source['source_of']) ? $source['source_of'] : 'activity';
            $id_source = isset($source['id_source']) ? $source['id_source'] : '0';

            $query_upd_report = "
		UPDATE %lms_coursereport
		SET title = '" . $source['title'] . "',
			weight = '" . $source['weight'] . "',
			max_score = '" . $source['max_score'] . "',
			required_score = '" . $source['required_score'] . "', 
			use_for_final = '" . $source['use_for_final'] . "',  
			show_to_user = '" . $source['show_to_user'] . "' 
		WHERE id_course = '" . $id_course . "' AND id_report = '" . $id_report . "' 
			AND source_of = '" . $source_of . "' AND id_source = '" . $id_source . "'";
        }
        // Save report modification

        return sql_query($query_upd_report);
    }

    public function deleteActivity($id_report, $id_course)
    {
        // Delete score
        if (!$this->deleteReportScore($id_report)) {
            return false;
        }

        // Delete report
        $query_del_report = "
		DELETE FROM %lms_coursereport
		WHERE id_course = '" . $id_course . "' AND id_report = '" . $id_report . "' 
			AND source_of = 'activity' AND id_source = '0'";

        return sql_query($query_del_report);
    }

    public function getAllUserFinalScore($id_user, $arr_courses = [])
    {
        $re = [];
        $query_scores = "
		SELECT s.id_user, r.id_course, s.score, s.score_status
		FROM %lms_coursereport AS r
			JOIN %lms_coursereport_score AS s
		WHERE r.source_of = 'final_vote' 
			AND s.id_report = r.id_report ";
        $query_scores .= " AND s.id_user = '" . $id_user . "'";
        if (!empty($arr_courses)) {
            $query_scores .= ' AND r.id_course IN ( ' . implode(',', $arr_courses) . ' ) ';
        }

        if (is_array($arr_courses) && empty($arr_courses)) {
            return $re;
        }
        $re_scores = sql_query($query_scores);
        foreach ($re_scores as $row) {
            [$user, $id_course, $score, $score_status] = array_values($row);
            if ($score_status == 'valid') {
                $re[$id_course] = $score;
            }
        }

        return $re;
    }

    public function getUserFinalScore($arr_users, $arr_courses = [])
    {
        $re = [];
        $query_scores = "
        SELECT s.id_user, r.id_course, r.max_score, COALESCE(s.score_status,'not_found') AS score_status
        LEFT JOIN %lms_coursereport_score AS s ON s.score_status = 'valid' AND s.id_report = r.id_report 
        WHERE 
        r.source_of = 'final_vote' ";

        if (!empty($arr_courses)) {
            $query_scores .= ' AND r.id_course IN ( ' . implode(',', $arr_courses) . ' ) ';
        }

        if (is_array($arr_courses) && empty($arr_courses)) {
            return $re;
        }
        $re_scores = sql_query($query_scores);
        $commonScores = [];
        foreach ($re_scores as $reScore) {
            $commonScores[$reScore['id_course']]['status'] = $reScore['score_status'];
            $commonScores[$reScore['id_course']]['max_score'] = $reScore['max_score'];
        }


        foreach ($arr_courses as $idCourse) {
            $scores[$idCourse] = $this->getCourseFinalScoreComputation($idCourse);
            foreach ($arr_users as $idUser) {
                $re[$idUser][$idCourse]['score'] = ($commonScores[$idCourse]['status'] == 'not_found') ? 0 : $scores[$idCourse][$idUser];
                $re[$idUser][$idCourse]['max_score'] = $commonScores[$idCourse]['max_score'];
            }
        }

        return $re;
    }

    public function getCourseFinalScoreComputation($idCourse, $idUser = null) {
        require_once Forma::inc(_lms_ . '/lib/lib.coursereport.php');
        require_once Forma::inc(_lms_ . '/lib/lib.test.php');
        require_once Forma::inc(_base_ . '/lib/lib.form.php');
        require_once Forma::inc(_base_ . '/lib/lib.table.php');
        $test_man = new GroupTestManagement();
        // XXX: Find students
        $id_students = $idUser ? [$idUser] : $this->getStudentId() ;
        // XXX: retrive info about the final score
        $courseReportLms = new CoursereportLms($idCourse);
        $info_final = $courseReportLms->getReportsFilteredBySourceOf(CoursereportLms::SOURCE_OF_FINAL_VOTE);

        // XXX: Retrive all reports (test and so), and set it
        $reports = $courseReportLms->getReportsForFinal();
        $sum_max_score = 0;
        $included_test = [];
        $other_source = [];
        foreach ($reports as $info_report) {
            $sum_max_score += $info_report->getMaxScore() * $info_report->getWeight();
            switch ($info_report->getSourceOf()) {
                case CoursereportLms::SOURCE_OF_ACTIVITY:
                    $other_source[$info_report->getIdReport()] = $info_report->getIdReport();
                    break;
                case CoursereportLms::SOURCE_OF_TEST:
                    $included_test[$info_report->getIdSource()] = $info_report->getIdSource();
                    break;
                default:
                    break;
            }
        }
        // XXX: Retrive Test score
        if (!empty($included_test)) {
            $tests_score = $test_man->getTestsScores($included_test, $id_students);
        }
        // XXX: Retrive other score
        if (!empty($other_source)) {
            $other_score = $report_man->getReportsScores($other_source);
        }
        $final_score = [];

        foreach ($id_students as $id_user) {
            $user_score = 0;

            foreach ($reports as $info_report) {
                switch ($info_report->getSourceOf()) {
                    case CoursereportLms::SOURCE_OF_ACTIVITY:
                        if (isset($other_score[$info_report->getIdReport()][$id_user]) && ($other_score[$info_report->getIdReport()][$id_user]['score_status'] == 'valid')) {
                            $user_score += ($other_score[$info_report->getIdReport()][$id_user]['score'] * $info_report->getWeight());
                        } else {
                            $user_score += 0;
                        }

                        break;
                    case CoursereportLms::SOURCE_OF_TEST:
                        if (isset($tests_score[$info_report->getIdSource()][$id_user]) && ($tests_score[$info_report->getIdSource()][$id_user]['score_status'] == 'valid')) {
                            $user_score += ($tests_score[$info_report->getIdSource()][$id_user]['score'] * $info_report->getWeight());
                        } else {
                            $user_score += 0;
                        }
                        break;
                    default:
                        break;
                }
            }
            // user final score
            if ($sum_max_score != 0) {
                $final_score[$id_user] = round(($user_score / $sum_max_score) * $info_final[0]->getMaxScore(), 2);
            } else {
                $final_score[$id_user] = 0;
            }
        }
        if($idUser) {
            return $final_score[$id_user];
        }
        return $final_score;
    }

    /**
     * @param int $id_course the id of the course to be deleted
     *
     * @return bool true if success false otherwise
     */
    public function deleteAllReports($id_course)
    {
        //validate input
        if ((int)$id_course <= 0) {
            return false;
        }

        $db = \FormaLms\db\DbConn::getInstance();

        $db->start_transaction();

        //get all existing report for the course
        $arr_id_report = [];
        $query = 'SELECT id_report FROM %lms_coursereport_score WHERE id_course = ' . (int)$id_course;
        $res = $db->query($query) ?? [];
        foreach ($res as $row) {
            [$id_report] = array_values($row);
            $arr_id_report[] = $id_report;
        }

        //delete all reports scores
        if (!empty($arr_id_report)) {
            $query = 'DELETE FROM %lms_coursereport_score WHERE id_report IN (' . implode(',', $arr_id_report) . ')';
            $res = $db->query($query);
            if (!$res) {
                $db->rollback();

                return false;
            }
        }

        //delete course reports
        $query = "DELETE FROM %lms_coursereport WHERE id_course = '" . (int)$id_course . "'";
        $res = $db->query($query);
        if (!$res) {
            $db->rollback();

            return false;
        }

        $db->commit();

        return true;
    }
}
