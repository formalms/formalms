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

class GroupTestManagement
{
    public function __construct()
    {
    }

    public function getTestInfo($id_tests)
    {
        $tests = [];
        if (empty($id_tests)) {
            return [];
        }
        $query_test = '
		SELECT idTest, title, point_required, show_only_status, show_score, point_type,	order_type, retain_answers_history
		FROM %lms_test 
		WHERE idTest IN  ( ' . implode(',', $id_tests) . ' )';
        $re_test = sql_query($query_test);
        foreach ($re_test as $test) {
            $id_t = $test['idTest'];
            $tests[$id_t] = $test;
        }

        return $tests;
    }

    /**
     * return the max score for this course.
     *
     * @param int $id_test the id of the test
     *
     * @return int the max score
     */
    public function getMaxScore($id_test)
    {
        [$question_random_number] = sql_fetch_row(sql_query('SELECT question_random_number FROM %lms_test WHERE idTest = ' . $id_test));

        if (isset($this->_max_score_cache[$id_test])) {
            return $this->_max_score_cache[$id_test];
        }

        $test = $this->getTestInfo([$id_test]);
        if ($test[$id_test]['point_type'] == '1') {
            $this->_max_score_cache[$id_test] = '100';

            return '100';
        }

        $query_question = '
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class 
		FROM %lms_testquest AS q JOIN ' . $GLOBALS['prefix_lms'] . "_quest_type AS t 
		WHERE q.idTest = '" . $id_test . "' AND q.type_quest = t.type_quest";
        $re_quest = sql_query($query_question);

        $max_score = 0;
        $question_number = 0;
        foreach ($re_quest as $row) {
            $idQuest = $row['idQuest'];
            $type_quest = $row['type_quest'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/' . $type_file);
            $quest_obj = eval("return new $type_class( $idQuest );");

            $max_score += $quest_obj->getMaxScore();
            ++$question_number;
        }

        if ((int)$question_random_number !== 0) {
            $single_question_point = $max_score / $question_number;
            $max_score = $question_random_number * $single_question_point;
        }

        $this->_max_score_cache[$id_test] = $max_score;

        return $max_score;
    }

    /**
     * @param int $id_test the id of the test
     *
     * @return int the score setted as the required score
     */
    public function getRequiredScore($id_test)
    {
        $query_select = '
		SELECT point_required
		FROM ' . $GLOBALS['prefix_lms'] . "_test 
		WHERE idTest = '" . $id_test . "'";
        [$score_req] = sql_fetch_row(sql_query($query_select));

        return $score_req;
    }

    /**
     * returns the users score for a list of test.
     *
     * @param array $id_tests an array with the id of the test for which the function must retrive scores
     * @param array $id_students the students of the course
     *
     * @return array a matrix with the index [id_test] [id_user] and the values in
     *               ['idTest',' idUser', 'date_attempt', 'type_of_result', 'result', 'score_status', 'comment']
     */
    public function getTestsScores($id_tests, $id_students = false, $pure = false)
    {
        $data = $this->getReportTestsScoresAndDetails($id_tests, $id_students, $pure);

        return $data['testScores'];
    }

    public function getReportTestsScoresAndDetails($id_tests, $id_students = false, $pure = false): array
    {
        $data = [
            'testScores' => [],
            'testDetails' => [],
        ];
        if (empty($id_tests)) {
            return $data;
        }
        if (empty($id_students)) {
            $id_students = false;
        }
        $query_scores = '
		SELECT lt.idTest, lt.idTrack, lt.idUser, lt.date_attempt, lt.date_attempt_mod, lt.score, lt.score_status, lt.comment, lt.bonus_score, count(ltt.idReference) as times
		FROM %lms_testtrack as lt join %lms_testtrack_times as ltt on lt.idTrack=ltt.idTrack and lt.idTest=ltt.idTest
		WHERE lt.idTest IN ( ' . implode(',', $id_tests) . ' ) ';
        if ($id_students !== false) {
            $query_scores .= ' AND lt.idUser IN ( ' . implode(',', $id_students) . ' )';
        }
        $query_scores .= ' GROUP by idTest, idTrack,idUser';
        $re_scores = sql_query($query_scores);
        foreach ($re_scores as $test_data) {

            if ($test_data['date_attempt_mod']) {
                $test_data['date_attempt'] = $test_data['date_attempt_mod'];
            }
            if (!$pure) {
                $test_data['score'] = $test_data['score'] + $test_data['bonus_score'];
            }

            $data['testDetails'][$test_data['idTest']] = [];

            if ($test_data['score_status'] == 'valid') {
                // max
                if (!isset($data['testDetails'][$test_data['idTest']]['max_score'])) {
                    $data['testDetails'][$test_data['idTest']]['max_score'] = $test_data['score'];
                } elseif ($test_data['score'] > $data['testDetails'][$test_data['idTest']]['max_score']) {
                    $data['testDetails'][$test_data['idTest']]['max_score'] = $test_data['score'];
                }

                // min
                if (!isset($data['testDetails'][$test_data['idTest']]['min_score'])) {
                    $data['testDetails'][$test_data['idTest']]['min_score'] = $test_data['score'];
                } elseif ($test_data['score'] < $data['testDetails'][$test_data['idTest']]['min_score']) {
                    $data['testDetails'][$test_data['idTest']]['min_score'] = $test_data['score'];
                }

                //number of valid score
                if (!isset($data['testDetails'][$test_data['idTest']]['num_result'])) {
                    $data['testDetails'][$test_data['idTest']]['num_result'] = 1;
                } else {
                    ++$data['testDetails'][$test_data['idTest']]['num_result'];
                }


                if (!isset($data['testDetails'][$test_data['idTest']]['maxScore'])) {
                    $data['testDetails'][$test_data['idTest']]['maxScore'] = $test_data['score'];
                } else {
                    $data['testDetails'][$test_data['idTest']]['maxScore'] += $test_data['score'];
                }
                // average
                if ($data['testDetails'][$test_data['idTest']]['num_result']) {
                    $data['testDetails'][$test_data['idTest']]['average'] = $data['testDetails'][$test_data['idTest']]['maxScore'] / $data['testDetails'][$test_data['idTest']]['num_result'];
                }
            }

            $data['testScores'][$test_data['idTest']][$test_data['idUser']] = $test_data;

        }
        return $data;
    }


    /**
     * returns the users score for a list of test.
     *
     * @param array $id_tests an array with the id of the test for which the function must retrive scores
     * @param array $id_students the students of the course
     *
     * @return array a matrix with the index [id_test] [id_user] and values array( score, max_score )
     */
    public function getSimpleTestsScores($id_tests, $id_students = false, $pure = false)
    {
        $data = [];
        if (empty($id_tests)) {
            return $data;
        }
        if (empty($id_students)) {
            $id_students = false;
        }
        $query_scores = '
		SELECT idTest, idUser,  score, bonus_score, number_of_attempt
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack
		WHERE score_status IN ('passed', 'valid', 'completed') AND idTest IN ( " . implode(',', $id_tests) . ' ) ';
        if ($id_students !== false) {
            $query_scores .= ' AND idUser IN ( ' . implode(',', $id_students) . ' )';
        }
        $re_scores = sql_query($query_scores);
        foreach ($re_scores as $test_data) {
            if (!$pure) {
                $score = $test_data['score'] + $test_data['bonus_score'];
            } else {
                $score = $test_data['score'];
            }
            $data[$test_data['idTest']][$test_data['idUser']]['score'] = $score;
            $data[$test_data['idTest']][$test_data['idUser']]['max_score'] = $this->getMaxScore($test_data['idTest']);
            $required_score = $this->getRequiredScore($test_data['idTest']);
            $data[$test_data['idTest']][$test_data['idUser']]['passed_score'] = ($score >= $required_score);
            $data[$test_data['idTest']][$test_data['idUser']]['number_of_attempt'] = $test_data['number_of_attempt'];
        }

        return $data;
    }

    /**
     * save some score info related with id_test and is_user.
     *
     * @param int $id_test the id of the test,
     * @param array $users_scores the score of the users associated with the proper idst_userid
     * @param array $date_attempts the date of the attempt time
     * @param array $comments comments to the test
     */
    public function saveTestUsersScores($id_test, $users_scores, $date_attempts, $comments)
    {
        require_once _lms_ . '/class.module/track.test.php';

        $query_test = '
		SELECT point_required, show_only_status 
		FROM ' . $GLOBALS['prefix_lms'] . "_test 
		WHERE idTest = '" . $id_test . "'";
        $re_test = sql_query($query_test);
        [$point_required, $show_only_status] = sql_fetch_row($re_test);
        $old_scores = &$this->getTestsScores([$id_test], false, true);
        $re = true;

        foreach ($users_scores as $idst_user => $score) {
            $query_scores = '
			UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack
			SET date_attempt_mod = '" . Format::dateDb($date_attempts[$idst_user], 'date') . "', 
				bonus_score = '" . ($score - $old_scores[$id_test][$idst_user]['score']) . "', 
				score_status = 'valid',
				comment = '" . $comments[$idst_user] . "'
			WHERE idTest = '" . $id_test . "' AND idUser = '" . $idst_user . "'";
            $re &= sql_query($query_scores);
            if ($score >= $point_required) {
                // update status in lesson
                $id_track = Track_Test::getTrack($id_test, $idst_user);
                if ($id_track) {
                    $test_track = new Track_Test($id_track);
                    $test_track->setDate(date('Y-m-d H:i:s'));
                    $test_track->status = 'passed';
                    $test_track->update();
                }
            } else {
                $id_track = Track_Test::getTrack($id_test, $idst_user);
                if ($id_track) {
                    $test_track = new Track_Test($id_track);
                    $test_track->setDate(date('Y-m-d H:i:s'));
                    $test_track->status = 'failed';
                    $test_track->update();
                }
            }

            $test_man = new TestManagement($id_test);
            $play_man = new PlayTestManagement($id_test, $idst_user, $id_track, $test_man);
            $test_info = $test_man->getTestAllInfo();
            $track_info = $play_man->getTrackAllInfo();
            $test_status = $score >= $point_required ? 'passed' : 'failed';
            if ($test_info['use_suspension']) {
                $suspend_info = [];
                if ($test_status == 'failed') {
                    $suspend_info['attempts_for_suspension'] = $track_info['attempts_for_suspension'] + 1;
                    if ($suspend_info['attempts_for_suspension'] >= $test_info['suspension_num_attempts']) {
                        //should we reset learning_test.suspension_num_attempts ??
                        $suspend_info['attempts_for_suspension'] = 0; //from now on, it uses the suspended_until parameter, so only the date is needed, we can reset the attempts count
                        $suspend_info['suspended_until'] = date('Y-m-d H:i:s', time() + $test_info['suspension_num_hours'] * 3600);
                    }
                    $re = Track_Test::updateTrack($id_track, $suspend_info);
                } else {
                    if ($test_status == 'completed' || $test_status == 'passed') {
                        $suspend_info['attempts_for_suspension'] = 0;
                        $re = Track_Test::updateTrack($id_track, $suspend_info);
                    }
                }
            }
        }

        return $re;
    }

    /**
     * @param int $id_test the id of the test to manage
     * @param array $id_user filter for user
     *
     * @return bool true if success false otherwise
     */
    public function roundTestScore($id_test, $id_users = false)
    {
        require_once _lms_ . '/class.module/track.test.php';

        $query_test = '
		SELECT point_required, show_only_status 
		FROM ' . $GLOBALS['prefix_lms'] . "_test 
		WHERE idTest = '" . $id_test . "'";
        $re_test = sql_query($query_test);
        [$point_required, $show_only_status] = sql_fetch_row($re_test);

        $re = true;
        $query_scores = '
		SELECT idTrack, idUser, score, score_status, bonus_score 
		FROM %lms_testtrack
		WHERE idTest = ' . $id_test . '';
        if ($id_users !== false) {
            $query_scores .= ' AND idUser IN ( ' . implode(',', $id_users) . ' ) ';
        }
        $re_scores = sql_query($query_scores);
        foreach ($re_scores as $row) {
            [$id_track, $user, $score, $score_status, $bonus_score] = array_values($row);
            $new_score = round($score + $bonus_score);
            if ($score_status == 'valid') {
                $query_scores = '
				UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack
				SET bonus_score = '" . ($new_score - $score) . "'
				WHERE idTest = '" . $id_test . "' AND idUser = '" . $user . "'";
                $re &= sql_query($query_scores);

                // update status in lesson
                if ($new_score >= $point_required) {
                    $test_track = new Track_Test($id_track);
                    $test_track->setDate(date('Y-m-d H:i:s'));
                    $test_track->status = 'passed';
                    $test_track->update();
                }
            }
            if (($score_status == 'passed' || $score_status == 'not_passed') && ($show_only_status == 1)
                && ($score < $point_required) && ($new_score >= $point_required)) {
                $query_scores = '
				UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack
				SET bonus_store = '" . $new_score . "',
					score_status = 'passed'
				WHERE idTest = '" . $id_test . "' AND idUser = '" . $user . "'";
                $re &= sql_query($query_scores);

                // update status in lesson
                $test_track = new Track_Test($id_track);
                $test_track->setDate(date('Y-m-d H:i:s'));
                $test_track->status = 'passed';
                $test_track->update();
            }
        }

        return $re;
    }

    public function editReview($id_test, $id_user, $number_time = null, $edit_new_score = true)
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/do.test.php');

        $query = '
		SELECT idTrack 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idTest='" . $id_test . "' AND idUser='" . $id_user . "'";
        $rs = sql_query($query);
        [$id_track] = sql_fetch_row($rs);

        editUserReport($id_user, $id_test, $id_track, $number_time, $edit_new_score);
    }


    public function saveReview($id_test, $id_user)
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/do.test.php');

        $query = '
		SELECT idTrack 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idTest='" . $id_test . "' AND idUser='" . $id_user . "'";
        $rs = sql_query($query);
        [$id_track] = sql_fetch_row($rs);

        saveManualUserReport($id_user, $id_test, $id_track);
    }

    public function deleteReview($id_test, $id_user, $id_track, $number_time)
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/do.test.php');

        return deleteUserReport($id_user, $id_test, $id_track, $number_time);
    }

    public function deleteTestTrack($id_test, $id_user)
    {
        require_once _lms_ . '/class.module/track.test.php';
        require_once _base_ . '/lib/lib.upload.php';

        $query = '
		SELECT idTrack 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idTest='" . $id_test . "' AND idUser='" . $id_user . "'";
        $rs = sql_query($query);
        if (!$rs) {
            return false;
        }
        $id_track = sql_fetch_row($rs);

        if (!$id_track) {
            return false;
        }

        $query_question = '
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class 
		FROM %lms_testquest AS q JOIN ' . $GLOBALS['prefix_lms'] . "_quest_type AS t 
		WHERE q.idTest = '" . $id_test . "' AND q.type_quest = t.type_quest 
		ORDER BY q.sequence";
        $re_quest = sql_query($query_question);
        foreach ($re_quest as $row) {
            $idQuest = $row['idQuest'];
            $type_quest = $row['type_quest'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/' . $type_file);
            $quest_obj = eval("return new $type_class( $idQuest );");

            if (!$quest_obj->deleteAnswer($id_track)) {
                return false;
            }
        }

        $query_page = '
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_page 
		WHERE idTrack = '" . $id_track . "'";
        $query_quest = '
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_quest 
		WHERE idTrack = '" . $id_track . "'";

        if (!sql_query($query_page)) {
            return false;
        }
        if (!sql_query($query_quest)) {
            return false;
        }

        $re_update = Track_Test::deleteTrack($id_track);

        return $re_update;
    }
}

class TestManagement
{
    public $id_test;

    public $test_info;

    /**
     * class constructor, load info about the test.
     *
     * @param int $id_test the id of the test
     */
    public function __construct($id_test)
    {
        $this->id_test = $id_test;
        $this->_load($id_test);
    }

    public function getNumberOfQuestion()
    {
        if ($this->test_info['order_type'] == 2 && $this->test_info['question_random_number']) {
            return $this->test_info['question_random_number'];
        }
        if ($this->test_info['order_type'] == 3 && $this->test_info['order_info']) {
            require_once _base_ . '/lib/lib.json.php';
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            $arr = $json->decode($this->test_info['order_info']);
            $total = 0;
            if (is_array($arr)) {
                foreach ($arr as $value) {
                    $total += $value['selected'];
                }
            }

            return $total;
        }
        if ($this->test_info['order_type'] >= 4 && $this->test_info['cf_info']) {
            require_once _base_ . '/lib/lib.json.php';
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            $arr = $json->decode($this->test_info['cf_info']);
            $total = 0;
            if (is_array($arr)) {
                foreach ($arr as $value) {
                    $total += $value['selected'];
                }
            }

            return $total;
        }
        $query = 'SELECT COUNT(*)'
            . ' FROM %lms_testquest'
            . " WHERE type_quest <> 'title'"
            . " AND type_quest <> 'break_page'"
            . " AND idTest = '" . $this->id_test . "'";

        [$result] = sql_fetch_row(sql_query($query));

        return $result;
    }

    public function _load($id_test)
    {
        $query_test = "
		SELECT idTest, title, description, 
			point_type, point_required, 
			display_type, order_type, shuffle_answer, question_random_number, 
			save_keep, mod_doanswer, can_travel, 
			show_only_status, show_score, show_score_cat, show_doanswer, show_solution, 
			time_dependent, time_assigned, penality_test, penality_time_test, penality_quest, penality_time_quest, 
			max_attempt, hide_info, order_info, cf_info,
			use_suspension, suspension_num_attempts, suspension_num_hours, suspension_prerequisites, chart_options, mandatory_answer
		FROM %lms_test
		WHERE idTest = '" . $id_test . "'";
        $re_test = sql_query($query_test);

        $this->test_info = sql_fetch_assoc($re_test);
    }

    /**
     * return all the caracteristic for the test.
     *
     * @return array all the info for the test
     */
    public function getTestAllInfo()
    {
        return $this->test_info;
    }

    /**
     * return a specific caracteristic for the test.
     *
     * @param string $info_name the name of the carachteristic for the test
     *
     * @return mixed the value of the caracteristic
     */
    public function getTestInfo($info_name)
    {
        return $this->test_info[$info_name];
    }

    /**
     * @return int return the total number of page for the test
     */
    public function getTotalPageNumber()
    {
        if ($this->test_info['order_type'] == 3) {
            if (!$this->test_info['display_type']) {
                return 1;
            } else {
                require_once _base_ . '/lib/lib.json.php';
                $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                $arr = $json->decode($this->test_info['order_info']);
                $tot_page = 0;
                foreach ($arr as $value) {
                    $tot_page += (int)$value['selected'];
                }

                return $tot_page;
            }
        }

        if ($this->test_info['question_random_number'] != 0) {
            $tot_page = 1;
            if ($this->test_info['display_type'] == 0) {
                $tot_page = 1;
            } else {
                $tot_page = $this->test_info['question_random_number'];
            }
        } elseif (!$this->test_info['display_type']) {
            [$tot_page] = sql_fetch_row(sql_query('
			SELECT MAX(page) 
			FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
			WHERE idTest = '" . $this->id_test . "'"));
        } else {
            if ($this->test_info['order_type'] == 0) {
                [$tot_page] = sql_fetch_row(sql_query('
				SELECT COUNT(*)
				FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
				WHERE idTest = '" . $this->id_test . "' "
                    . " AND type_quest <> 'break_page'"));
            } else {
                [$tot_page] = sql_fetch_row(sql_query('
				SELECT COUNT(*)
				FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
				WHERE idTest = '" . $this->id_test . "' "
                    . " AND type_quest <> 'title' AND type_quest <> 'break_page'"));
            }
        }

        return $tot_page;
    }

    /**
     * this function return the page of the question.
     *
     * @param int $idTest indicates the test selected
     *
     * @return int is the correct number of page for the question
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function _getPageNumber()
    {
        [$seq, $page] = sql_fetch_row(sql_query('
		SELECT MAX(sequence), MAX(page)
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idTest = '" . $this->id_test . "'"));
        if (!$page) {
            return 1;
        }

        [$type_quest] = sql_fetch_row(sql_query('
		SELECT type_quest 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE sequence = '" . $seq . "' AND idTest = '" . $this->id_test . "'"));
        if ($type_quest == 'break_page') {
            return $page + 1;
        } else {
            return $page;
        }
    }

    /**
     * return the number of question in the test.
     *
     * @return int the maximum value of sequence
     */
    public function getMaxSequence()
    {
        [$quest_sequence_number] = sql_fetch_row(sql_query('
		SELECT COUNT(*)
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idTest = '" . $this->id_test . "'"));

        return $quest_sequence_number;
    }

    /**
     * @param int $page_number the number of the page
     *
     * @return int return the initial sequence number of the question for the page
     */
    public function getInitQuestSequenceNumberForPage($page_number)
    {
        if (!$this->test_info['display_type']) {
            [$quest_sequence_number] = sql_fetch_row(sql_query('
			SELECT COUNT(*) + 1 
			FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
			WHERE idTest = '" . $this->id_test . "' AND page < '" . $page_number . "' 
				AND type_quest <> 'title' AND type_quest <> 'break_page'"));
        } else {
            return $page_number;
        }

        return $quest_sequence_number;
    }

    /**
     * @return int return the maximum score ammount for this test
     */
    public function getMaxScore()
    {
        $query_question = '
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class 
		FROM %lms_testquest AS q JOIN ' . $GLOBALS['prefix_lms'] . "_quest_type AS t 
		WHERE q.idTest = '" . $this->id_test . "' AND q.type_quest = t.type_quest";
        $re_quest = sql_query($query_question);

        $max_score = 0;
        foreach ($re_quest as $row) {
            $idQuest = $row['idQuest'];
            $type_quest = $row['type_quest'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/' . $type_file);
            $quest_obj = eval("return new $type_class( $idQuest );");

            $max_score += $quest_obj->getMaxScore();
        }

        return $max_score;
    }

    public function importQuestionFromXml($filename)
    {
        require_once _base_ . '/lib/lib.domxml.php';

        // initialize DOM class
        $xml_doc = new FormaDOMDocument();
        if (!$xml_doc) {
            return false;
        }
        if (!$xml_doc->load($filename)) {
            return false;
        }
        if (!$xpath = new FormaDOMXPath($xml_doc)) {
            return false;
        }

        // get all the question in the document
        $NodeList_question = $xpath->query('/question_collection/question');

        $seq = $this->getMaxSequence() + 1;
        $page = $this->_getPageNumber();

        for ($i = 0; $i < $NodeList_question->length; ++$i) {
            $quest = $NodeList_question->item($i);

            // read text quest
            $xre_quest_text = $xpath->query('prompt/text()', $quest);
            $node_quest_text = $xre_quest_text->item(0);

            //$node_quest_text->textContent // contains the question

            //insert the new question
            $ins_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
			( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
			( 	'" . $this->id_test . "', 
				'0', 
				'choice', 
				'" . addslashes($node_quest_text->textContent) . "',
				'3', 
				'0', 
				'" . ($seq + $i) . "', 
				'" . $page . "' ) ";
            if (!sql_query($ins_query)) {
                return false;
            }
            [$id_quest] = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            if (!$id_quest) {
                return false;
            }

            $re = true;
            // find all the answer
            $NodeList_answer = $xpath->query('answers/answer', $quest);

            for ($j = 0; $j < $NodeList_answer->length; ++$j) {
                $answer = $NodeList_answer->item($j);

                $is_correct = $answer->getAttribute('is_correct');
                $score_if_correct = $answer->getAttribute('score_if_correct');
                $score_if_error = $answer->getAttribute('score_if_error');

                //$answer->textContent
                $ins_answer_query = '
				INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
				( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
				( 	'" . $id_quest . "', 
					'" . ($is_correct == 'true' ? 1 : 0) . "', 
					'" . addslashes($answer->textContent) . "', 
					'', 
					'" . (float)$score_if_correct . "', 
					'" . (float)$score_if_error . "') ";
                if (!sql_query($ins_answer_query)) {
                    $re = false;
                }
            }

            echo '-------------------------------<br/><br/>';
        }

        return $re;
    }

    public function getPrerequisite()
    {
        $query_prerequisite = 'SELECT prerequisites'
            . ' FROM %lms_organization'
            . " WHERE idResource = '" . $this->id_test . "'"
            . "	AND objectType = 'test'";

        [$prerequisites] = sql_fetch_row(sql_query($query_prerequisite));

        return $prerequisites;
    }
}

class PlayTestManagement
{
    public $id_test;

    public $id_track;

    public $id_user;

    /**
     * @param TestMnagement
     */
    public $test_man;

    public $track_info;

    public function __construct($id_test, $id_user, $id_track, &$test_man)
    {
        $this->id_test = $id_test;
        $this->id_track = $id_track;
        $this->id_user = $id_user;
        $this->test_man = &$test_man;

        $this->_load($id_track);
    }

    public function _load($id_track)
    {
        $query_track_info = "
		SELECT date_attempt, date_attempt_mod, date_end_attempt, 
			last_page_seen, last_page_saved, 
			number_of_save, 
			score, bonus_score, score_status, comment,
			attempts_for_suspension, suspended_until
		FROM %lms_testtrack
		WHERE idTrack = '" . $id_track . "'";
        $re_track_info = sql_query($query_track_info);
        $this->track_info = sql_fetch_assoc($re_track_info);
    }

    /**
     * return all the track stats for the test.
     *
     * @return array all the info for the track
     */
    public function getTrackAllInfo()
    {
        return $this->track_info;
    }

    /**
     * @return int return in seconds the time spended in the test by the user
     */
    public function userTimeInTheTest()
    {
        $time_accumulated = 0;
        $query_time = '
		SELECT UNIX_TIMESTAMP(display_from) as display_from_time, display_to, UNIX_TIMESTAMP(display_to) as display_to_time, accumulated 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_page
		WHERE idTrack = '" . $this->id_track . "'";
        $re_time = sql_query($query_time);
        if (!sql_num_rows($re_time)) {
            return $time_accumulated;
        }

        foreach ($re_time as $row) {

            $from_ts = $row['display_from_time'];
            $to = $row['display_to'];
            $to_ts = $row['display_to_time'];
            $accumulated = $row['accumulated'];
            if ($to !== null) {
                $time_accumulated += abs($to_ts - $from_ts);
            }
            $time_accumulated += $accumulated;
        }

        return $time_accumulated;
    }

    public function userTimeInThePage($page)
    {
        $time_accumulated = 0;
        $query_time = '
		SELECT UNIX_TIMESTAMP(display_from), display_to, UNIX_TIMESTAMP(display_to), accumulated 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_page
		WHERE idTrack = '" . $this->id_track . "' AND page = '" . $page . "'";
        $re_time = sql_query($query_time);
        if (!sql_num_rows($re_time)) {
            return $time_accumulated;
        }

        [$from_ts, $to, $to_ts, $accumulated] = sql_fetch_row($re_time);

        if ($to !== null) {
            $time_accumulated += abs($to_ts - $from_ts);
        }
        $time_accumulated += $accumulated;

        return $time_accumulated;
    }

    public function updateTrackForPage($page)
    {
        $now = date('Y-m-d H:i:s');
        $query_time = '
		SELECT display_from, UNIX_TIMESTAMP(display_from), display_to, UNIX_TIMESTAMP(display_to), accumulated 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_page
		WHERE idTrack = '" . $this->id_track . "' AND page = '" . $page . "'";
        $re_time = sql_query($query_time);

        if (!sql_num_rows($re_time)) {
            $query_track = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testtrack_page
			( idTrack, page, display_from, display_to ) VALUES (
				'" . $this->id_track . "',
				'" . $page . "',
				'" . $now . "', 
				NULL )";
            sql_query($query_track);
        } else {
            $time_accumulated = 0;
            [$from, $from_ts, $to, $to_ts, $accumulated] = sql_fetch_row($re_time);

            if ($to == null) {
                $time_accumulated = time() - $from_ts;
                $to = null;
            } else {
                $time_accumulated = abs($to_ts - $from_ts) + $accumulated;
                $from = $now;
                $to = null;
            }
            $query_track = '
			UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack_page
			SET display_from = '" . $from . "', 
				display_to = " . ($to === null ? 'NULL' : "'" . $to . "'") . ",
				accumulated = '" . $time_accumulated . "'
			WHERE idTrack = '" . $this->id_track . "' AND page = '" . $page . "'";
            sql_query($query_track);
        }
    }

    public function closeTrackPageSession($page)
    {
        $query_time = '
		SELECT display_to 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_page
		WHERE idTrack = '" . $this->id_track . "' AND page = '" . $page . "'";
        $re_time = sql_query($query_time);
        if (sql_num_rows($re_time)) {
            [$to] = sql_fetch_row($re_time);
            if ($to === null) {
                $query_track = '
				UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack_page
				SET display_to = '" . date('Y-m-d H:i:s') . "' 
				WHERE idTrack = '" . $this->id_track . "' AND page = '" . $page . "'";
                sql_query($query_track);
            }
        }
    }

    /**
     * @return int score tracking status
     */
    public function getScoreStatus()
    {
        return $this->track_info['score_status'];
    }

    /**
     * @return int return the last page seen by the user
     */
    public function getLastPageSeen()
    {
        return $this->track_info['last_page_seen'];
    }

    /**
     * @return int return the last page saved by the user
     */
    public function getLastPageSaved()
    {
        return $this->track_info['last_page_saved'];
    }

    /**
     * return a sql query text for question mining.
     *
     * @return int return the number of question showed to the user
     */
    public function numberOfQuestionShow()
    {
        $question_number = 0;
        $query_quest_seen = '
		SELECT COUNT(*) 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_quest
		WHERE idTrack = '" . $this->id_track . "'";
        [$question_number] = sql_fetch_row(sql_query($query_quest_seen));

        return $question_number;
    }

    /**
     * return a sql query text for question mining.
     *
     * @return string return the query for question retrivier
     */
    public function getQuestionsForPage($page_number)
    {
        // Retrive info about a test
        $time_dependent = $this->test_man->getTestInfo('time_dependent');
        $order_type = $this->test_man->getTestInfo('order_type');
        $shuffle_answer = $this->test_man->getTestInfo('shuffle_answer');
        $question_random_number = $this->test_man->getTestInfo('question_random_number');
        $order_info = $this->test_man->getTestInfo('order_info');
        $cf_info = $this->test_man->getTestInfo('cf_info');

        // cast display to one quest at time if the time is by quest
        if ($time_dependent == 2) {
            $display_type = 1;
        } else {
            $display_type = $this->test_man->getTestInfo('display_type');
        }

        // Query base
        $query_question = '
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class, q.time_assigned 
		FROM %lms_testquest AS q 
			JOIN ' . $GLOBALS['prefix_lms'] . "_quest_type AS t 
		WHERE  q.type_quest = t.type_quest AND q.idTest = '" . $this->id_test . "' ";

        $query_quest = '
		SELECT idQuest 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_quest
		WHERE idTrack = '" . $this->id_track . "' AND page = '" . $page_number . "'";
        $re_quest = sql_query($query_quest);

        if (sql_num_rows($re_quest)) {
            // page alredy seen, retrive the question alredy displayed
            foreach ($re_quest as $row) {
                [$id_quest] = array_values($row);
                $quest_displayed[] = $id_quest;
            }

            $query_question .= ' AND q.idQuest IN (' . implode(',', $quest_displayed) . ')';
            if ($order_type == 0) {
                $query_question .= ' ORDER BY q.sequence ';
            }

            return $query_question;
        }

        if (!$display_type) {
            // Patch X customfield
            if ($order_type > 4) {
                $order_type = 4;
            }

            // Respect page number
            switch ($order_type) {
                case '0':
                    // sequential
                    return $query_question
                        . " AND q.page = '" . $page_number . "' "
                        . ' ORDER BY q.sequence';
                    break;
                case '1':
                    // shuffle
                    return $query_question
                        . " AND q.page = '" . $page_number . "' "
                        . " AND q.type_quest <> 'title' "
                        . ' ORDER BY RAND() ';
                    break;
                case '2':
                    // Random X quest on a total of N quest
                    return $query_question
                        . " AND q.type_quest <> 'title'  AND q.type_quest <> 'break_page' "
                        . ' ORDER BY RAND() '
                        . ' LIMIT 0, ' . $question_random_number;
                    break;
                case '3':
                    // Random X quest on a set of selected categories, each of N(idCategory) quests
                    require_once _base_ . '/lib/lib.json.php';
                    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                    $arr = $json->decode($order_info);

                    $queries = [];
                    if (is_array($arr)) {
                        foreach ($arr as $value) {
                            if ((int)$value['selected'] > 0) {
                                $queries[] = $query_question
                                    . " AND q.type_quest <> 'title'  AND q.type_quest <> 'break_page' "
                                    . " AND q.idCategory = '" . (int)$value['id_category'] . "' ORDER BY RAND() "
                                    . ' LIMIT 0, ' . $value['selected'];
                            }
                        }
                    }

                    if (count($queries) > 0) {
                        return '(' . implode(') UNION (', $queries) . ') ORDER BY RAND() ';
                    } else {
                        return '';
                    }
                    break;
                case '4':
                    // Random X quest on a set of selected categories, each of N(idCategory) quests
                    require_once _base_ . '/lib/lib.json.php';
                    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                    $arr = $json->decode($cf_info);

                    $queries = [];
                    if (is_array($arr)) {
                        foreach ($arr as $value) {
                            if ((int)$value['selected'] > 0) {
                                $queries[] = $query_question
                                    . " AND q.type_quest <> 'title'  AND q.type_quest <> 'break_page' "
                                    //." AND q.idCategory = '".(int)$value['id_cf_son']."' ORDER BY RAND() "
                                    //q.idQuest   prefix_fw
                                    . ' AND q.idQuest IN ( SELECT id_obj FROM ' . $GLOBALS['prefix_fw'] . "_customfield_entry WHERE obj_entry = '" . (int)$value['id_cf_son'] . "' ) ORDER BY RAND() "
                                    . ' LIMIT 0, ' . $value['selected'];
                            }
                        }
                    }

                    if (count($queries) > 0) {
                        return '(' . implode(') UNION (', $queries) . ') ORDER BY RAND() ';
                    } else {
                        return '';
                    }
                    break;
            }
        } else {
            // One question per page
            $query_question .= " AND q.type_quest <> 'break_page'";

            // Retrive question alredy displayed
            $query_quest_seen = '
			SELECT idQuest 
			FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_quest
			WHERE idTrack = '" . $this->id_track . "'";
            $re_quest_seen = sql_query($query_quest_seen);
            foreach ($re_quest_seen as $row) {
                [$id_quest] = array_values($row);
                $quest_seen[] = $id_quest;
            }

            if (!empty($quest_seen)) {
                $query_question .= ' AND q.idQuest NOT IN (' . implode(',', $quest_seen) . ') ';
            }
            switch ($order_type) {
                case '0':
                    // Sequential
                    return $query_question
                        . ' ORDER BY q.sequence '
                        . ' LIMIT 0,1';
                    break;
                case '1':
                    // Shuffle
                    return $query_question
                        . " AND q.type_quest <> 'title' "
                        . ' ORDER BY RAND() '
                        . ' LIMIT 0,1';
                    break;
                case '2':
                    // Random X quest on a total of N quest
                    return $query_question
                        . " AND q.type_quest <> 'title' "
                        . ' ORDER BY RAND()'
                        . ' LIMIT 0, 1';
                    break;
                case '3':
                    $cat_seen = [];
                    $query_cat_seen = '
			     SELECT idCategory, COUNT(*)
			     FROM %lms_testtrack_quest as ttq JOIN ' . $GLOBALS['prefix_lms'] . "_testquest as tq
			     ON (ttq.idQuest = tq.idQuest) WHERE idTrack = '" . $this->id_track . "' GROUP BY idCategory";
                    $re_seen = sql_query($query_cat_seen);
                    foreach ($re_seen as $row) {
                        [$id_cat, $num] = array_values($row);
                        $cat_seen[$id_cat] = $num;
                    }

                    // Random X quest on a set of selected categories, each of N(idCategory) quests
                    require_once _base_ . '/lib/lib.json.php';
                    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                    $arr = $json->decode($order_info);

                    $queries = [];
                    if (is_array($arr)) {
                        foreach ($arr as $value) {
                            if (!isset($cat_seen[$value['id_category']])) {
                                $cat_seen[$value['id_category']] = 0;
                            }
                            if ($cat_seen[$value['id_category']] < $value['selected']) {
                                if ((int)$value['selected'] > 0) {
                                    $queries[] = $query_question
                                        . " AND q.type_quest <> 'title'  AND q.type_quest <> 'break_page' "
                                        . " AND q.idcategory = '" . (int)$value['id_category'] . "' ORDER BY RAND() "
                                        . ' LIMIT 0, ' . (int)($value['selected'] - $cat_seen[$value['id_category']]);
                                }
                            }
                        }
                    }

                    if (count($queries) > 0) {//NODEBUG echo "(".implode(") UNION (", $queries).") ORDER BY RAND() LIMIT 0,1";
                        return '(' . implode(') UNION (', $queries) . ') ORDER BY RAND() LIMIT 0,1';
                    } else {
                        return '';
                    }
                    break;
            }
        }
    }

    public function storePage($page_to_save, $can_overwrite)
    {
        $query_question = $this->getQuestionsForPage($page_to_save);
        $re_question = sql_query($query_question);
        foreach ($re_question as $question) {
            $id_quest = $question['idQuest'];
            $type_quest = $question['type_quest'];
            $type_file = $question['type_file'];
            $type_class = $question['type_class'];

            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/' . $type_file);
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/track.test.php');
            $trackTest = new Track_Test($this->id_track);
            $quest_obj = new $type_class($id_quest);
            $storing = $quest_obj->storeAnswer($trackTest, $_POST, $can_overwrite);
        }
    }
}
