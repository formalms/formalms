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

class CourseReportManager
{

    function CourseReportManager()
    {
    }

    function getNextSequence()
    {

        $query_seq = "
		SELECT sequence
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport 
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND source_of = 'final_vote'";
        list($seq) = sql_fetch_row(sql_query($query_seq));

        $query_seq = "
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport 
		SET sequence = sequence + 1 
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND source_of = 'final_vote'";
        sql_query($query_seq);

        return $seq;
    }

    function &getStudentId()
    {

        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

        $course_user = array();
        $course_man = new Man_Course();
        $course_user = $course_man->getIdUserOfLevel($_SESSION['idCourse'], 3);

        return $course_user;
    }

    function &getTest()
    {

        require_once($GLOBALS['where_lms'] . '/lib/lib.orgchart.php');
        require_once(Docebo::inc(_folder_lms_ . '/class.module/learning.test.php'));

        $org_man = new OrganizationManagement($_SESSION['idCourse']);
        $tests =& $org_man->getAllLoAbsoluteIdWhereType(Learning_Test::getTestTypes());

        return $tests;
    }

    function initializeCourseReport($id_tests)
    {

        $query_test = "
		INSERT INTO " . $GLOBALS['prefix_lms'] . "_coursereport 
		( id_course, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
			'" . $_SESSION['idCourse'] . "',
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
        $this->addTestToReport($id_tests, 1);
    }

    function addTestToReport($id_tests, $from_sequence)
    {
        $test_man = new GroupTestManagement();

        $plus = count($id_tests);
        $query_seq = "
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport 
		SET sequence = sequence + " . $plus . " 
		WHERE id_course = '" . $_SESSION['idCourse'] . "'";
        sql_query($query_seq);

        $test_info = $test_man->getTestInfo($id_tests);

        while (list($id_test, $title) = each($id_tests)) {

            $query_test = "
			INSERT INTO " . $GLOBALS['prefix_lms'] . "_coursereport 
			( id_course, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, sequence ) VALUES (
				'" . $_SESSION['idCourse'] . "',
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

    function updateTestReport($id_tests)
    {

        $test_man = new GroupTestManagement();
        $tests_list = $test_man->getTestInfo($id_tests);

        while (list($id_test, $test_info) = each($tests_list)) {

            $query_test = "
			UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport 
			SET required_score = '" . $test_info['point_required'] . "' "
                . ($test_info['order_type'] != 2 ? ", "
                    . " max_score = '" . $test_man->getMaxScore($id_test) . "' " : '') . ", "
                . " required_score = " . $test_man->getRequiredScore($id_test) . " "
                . " WHERE id_course = '" . $_SESSION['idCourse'] . "' AND 
				source_of = 'test' AND 
				id_source = '" . $id_test . "'";
            sql_query($query_test);

        }

    }

    function delTestToReport($id_tests)
    {

        if (empty($id_tests)) return;

        $query_test = "DELETE FROM " . $GLOBALS['prefix_lms'] . "_coursereport 
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND
			source_of = 'test' AND 
			id_source IN ( " . implode(',', $id_tests) . " )";
        sql_query($query_test);

        $this->repairSequence();
    }

    function repairSequence()
    {

        $query_select = "
		SELECT id_report
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport 
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND source_of <> 'final_vote'
		ORDER BY sequence";
        $re_select = sql_query($query_select);
        $i = 1;
        while (list($id_report) = sql_fetch_row($re_select)) {

            $query_seq = "
			UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport 
			SET sequence = '" . $i++ . "' 
			WHERE id_course = '" . $_SESSION['idCourse'] . "' AND id_report = '$id_report'";
            sql_query($query_seq);
        }
        $query_seq = "
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport 
		SET sequence = '" . $i . "' 
		WHERE id_course = '" . $_SESSION['idCourse'] . "' AND source_of = 'final_vote'";
        sql_query($query_seq);

    }

    /**
     * @param int $reports_id the id of the reports for which you need to recover the users scores
     * @param array $id_user if != false filter result to this users
     *
     * @return array    an array with this structure ( id_report => ( id_user => (id_report, id_user, date_attempt, score, score_status, comment)), ...)
     */
    function &getReportsScores($reports_id, $id_user = false)
    {

        $data = array();
        if (empty($reports_id) || !is_array($reports_id)) return $data;
        if ($id_user !== false && !is_array($id_user))
            $id_user = array($id_user);
        if (!is_array($reports_id)) return $data;
        $query_scores = "
			SELECT id_report, id_user, date_attempt, score, score_status, comment 
			FROM " . $GLOBALS['prefix_lms'] . "_coursereport_score 
			WHERE id_report IN ( " . implode(',', $reports_id) . " )";
        if ($id_user !== false && !empty($id_user)) $query_scores .= " AND id_user IN ( " . implode(',', $id_user) . " )";
        $re_scores = sql_query($query_scores);
        while ($test_data = sql_fetch_assoc($re_scores)) {

            if ($test_data['date_attempt'] == '0000-00-00 00:00:00') $test_data['date_attempt'] = '';
            $data[$test_data['id_report']][$test_data['id_user']] = $test_data;
        }
        return $data;
    }

    function saveReportScore($id_report, $users_scores, $date_attempts, $comments)
    {

        $old_scores =& $this->getReportsScores(array($id_report));
        $re = true;
        while (list($idst_user, $score) = each($users_scores)) {

            if (!isset($old_scores[$id_report][$idst_user])) {

                $query_scores = "
				INSERT INTO " . $GLOBALS['prefix_lms'] . "_coursereport_score
				( id_report, id_user, date_attempt, score, score_status, comment ) VALUES ( 
					'" . $id_report . "', 
					'" . $idst_user . "', 
					'" . Format::dateDb($date_attempts[$idst_user], 'date') . "', 
					'" . $score . "', 
					'valid',
					'" . $comments[$idst_user] . "' )";
            } else {

                $query_scores = "
				UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport_score
				SET date_attempt = '" . Format::dateDb($date_attempts[$idst_user], 'date') . "', 
					score = '" . $score . "', 
					score_status = 'valid',
					comment = '" . $comments[$idst_user] . "'
					" . ($old_scores[$id_report][$idst_user] != $score
                        ? ", score_status = 'valid'"
                        : '') . " 
				WHERE id_report = '" . $id_report . "' AND id_user = '" . $idst_user . "'";
            }
            $re &= sql_query($query_scores);
        }
        return $re;
    }

    /**
     * @param int $id_report the id of the report to manage
     * @param array $id_user filter for user
     *
     * @return bool    true if success false otherwise
     */
    function roundReportScore($id_report, $id_users = FALSE)
    {

        $re = true;
        $query_scores = "
		SELECT id_user, score, score_status
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport_score
		WHERE id_report = " . $id_report . " ";
        if ($id_users !== FALSE) $query_scores .= " AND idUser IN ( " . implode(',', $id_users) . " ) ";
        $re_scores = sql_query($query_scores);
        while (list($user, $score, $score_status) = sql_fetch_row($re_scores)) {

            if ($score_status == 'valid') {

                $query_scores = "
				UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport_score
				SET score = '" . round($score) . "'
				WHERE id_report = '" . $id_report . "' AND id_user = '" . $user . "'";
                $re &= sql_query($query_scores);
            }
        }
        return $re;
    }

    function deleteReportScore($id_report)
    {

        $query_scores = "
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_coursereport_score
		WHERE id_report = '" . $id_report . "'";
        return sql_query($query_scores);
    }

    function deleteReport($id_report)
    {

        $query_scores = "
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_coursereport 
		WHERE id_report = '" . $id_report . "'";
        $re = sql_query($query_scores);

        $this->repairSequence();
        return $re;
    }

    function checkActivityData(&$source)
    {

        if ($source['required_score'] > $source['max_score']) {
            return array('error' => true,
                'message' => Lang::t('_REQUIRED_MUST_BE_LESS_THEN_MAX', 'coursereport', 'lms'));
        }
        return array('error' => false, 'message' => '');
    }

    function addActivity($id_course, &$source)
    {

        $query_ins_report = "
		INSERT INTO " . $GLOBALS['prefix_lms'] . "_coursereport 
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

    function updateActivity($id_report, $id_course, &$source)
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
                $id_source = "0";
            }

            $query_upd_report = "
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
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
		UPDATE " . $GLOBALS['prefix_lms'] . "_coursereport
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

    function deleteActivity($id_report, $id_course)
    {

        // Delete score
        if (!$this->deleteReportScore($id_report)) return false;

        // Delete report
        $query_del_report = "
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_coursereport
		WHERE id_course = '" . $id_course . "' AND id_report = '" . $id_report . "' 
			AND source_of = 'activity' AND id_source = '0'";
        return sql_query($query_del_report);
    }

    function getAllUserFinalScore($id_user, $arr_courses = false)
    {

        $re = array();
        $query_scores = "
		SELECT s.id_user, r.id_course, s.score, s.score_status
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport AS r
			JOIN " . $GLOBALS['prefix_lms'] . "_coursereport_score AS s
		WHERE r.source_of = 'final_vote' 
			AND s.id_report = r.id_report ";
        $query_scores .= " AND s.id_user = '" . $id_user . "'";
        if ($arr_courses !== false) $query_scores .= " AND r.id_course IN ( " . implode(',', $arr_courses) . " ) ";

        if (is_array($arr_courses) && empty($arr_courses)) return $re;
        $re_scores = sql_query($query_scores);
        while (list($user, $id_course, $score, $score_status) = sql_fetch_row($re_scores)) {

            if ($score_status == 'valid') {
                $re[$id_course] = $score;
            }
        }
        return $re;
    }


    function getUserFinalScore($arr_users, $arr_courses = false)
    {

        $re = array();
        $query_scores = "
		SELECT s.id_user, r.id_course, s.score, s.score_status, r.max_score
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport AS r
			JOIN " . $GLOBALS['prefix_lms'] . "_coursereport_score AS s
		WHERE r.source_of = 'final_vote' 
			AND s.id_report = r.id_report ";
        $query_scores .= " AND s.id_user IN ( " . implode(',', $arr_users) . " )";
        if ($arr_courses !== false) $query_scores .= " AND r.id_course IN ( " . implode(',', $arr_courses) . " ) ";

        if (is_array($arr_courses) && empty($arr_courses)) return $re;
        $re_scores = sql_query($query_scores);
        while (list($user, $id_course, $score, $score_status, $max_score) = sql_fetch_row($re_scores)) {

            if ($score_status == 'valid') {
                $re[$user][$id_course]['score'] = $score;
                $re[$user][$id_course]['max_score'] = $max_score;
            }
        }
        return $re;
    }

    /**
     * @param int $id_course the id of the course to be deleted
     *
     * @return bool    true if success false otherwise
     */
    function deleteAllReports($id_course)
    {
        //validate input
        if ((int)$id_course <= 0) return false;

        $db = DbConn::getInstance();

        $db->start_transaction();

        //get all existing report for the course
        $arr_id_report = array();
        $query = "SELECT id_report FROM %lms_coursereport_score WHERE id_course = " . (int)$id_course;
        $res = $db->query($query);
        while (list($id_report) = $db->fetch_row($res)) {
            $arr_id_report[] = $id_report;
        }

        //delete all reports scores
        if (!empty($arr_id_report)) {
            $query = "DELETE FROM %lms_coursereport_score WHERE id_report IN (" . implode(",", $arr_id_report) . ")";
            $res = $db->query($query);
            if (!res) {
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

?>