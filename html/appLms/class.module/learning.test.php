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

require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/learning.object.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.question.php');

class Learning_Test extends Learning_Object
{
    public $id;

    public $idAuthor;

    public $title;

    public $back_url;

    public $retain_answers_history = false;

    public $mandatory_answer = false;

    public $hide_info = false;

    public $idCourse;

    public $idOrg;

    /**
     * function learning_Test()
     * class constructor.
     **/
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->obj_type = 'test';
        if ($id !== null) {
            $res = $this->db->query("SELECT author, title, obj_type, retain_answers_history FROM %lms_test WHERE idTest = '" . (int) $id . "'");
            if ($res && $this->db->num_rows($res) > 0) {
                list(
                    $this->idAuthor,
                    $this->title,
                    $this->obj_type,
                    $this->retain_answers_history
                ) = $this->db->fetch_row($res);
                $this->isPhysicalObject = true;
            }
            $res = $this->db->query("SELECT idOrg, idCourse FROM %lms_organization WHERE objectType='" . $this->obj_type . "' AND idResource = '" . (int) $id . "'");
            if ($res && $this->db->num_rows($res) > 0) {
                list($this->idOrg, $this->idCourse) = $this->db->fetch_row($res);
            }
        }
    }

    public static function load($id)
    {
        $testObj = new self($id);
        if ($testObj->getObjectType() == 'test') {
            return $testObj;
        }

        $res = sql_query("SELECT fileName, className FROM %lms_lo_types WHERE objectType = '" . $testObj->getObjectType() . "'");
        list($type_file, $type_class) = sql_fetch_row($res);
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/' . $type_file);

        return new $type_class($id);
    }

    public function getParamInfo()
    {
        $params = parent::getParamInfo();

        return $params;
    }

    public function renderCustomSettings($arrParams, $form, $lang)
    {
        $out = parent::renderCustomSettings($arrParams, $form, $lang);

        return $out;
    }

    /**
     * function create( $back_url ).
     *
     * @param string $back_url contains the back url
     *
     * @return nothing
     *                 attach the id of the created object at the end of back_url with the name, in attach the result in create_result
     *
     * static
     **/
    public function create($back_url)
    {
        $this->back_url = $back_url;

        \FormaLms\lib\Forma::removeErrors();

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/test.php');
        addtest($this);
    }

    /**
     * function edit.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url
     *
     * @return nothing
     *                 attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format
     **/
    public function edit($id, $back_url)
    {
        $this->id = $id;
        $this->back_url = $back_url;

        \FormaLms\lib\Forma::removeErrors();

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/test.php');
        modtestgui($this);
    }

    /**
     * function del.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url (not used yet)
     *
     * @return false if fail, else return the id lo
     **/
    public function del($id, $back_url = null)
    {
        checkPerm('view', false, 'storage');

        $deleteFinalVote = 0;
        $deleteCourseReportIds = [];
        \FormaLms\lib\Forma::removeErrors();

        //cancello registro valutazioni
        //seleziono i record da course report attenzionando l'id del corso e il campo use for final
        $learningCourseReportRecords = sql_query('
		SELECT *
		FROM %lms_coursereport
		WHERE id_source = "' . $id . '" AND source_of = "test"');
        foreach($learningCourseReportRecords as $learningCourseReportRecord) {
            if($learningCourseReportRecord['use_for_final'] === 'true') {
                $deleteFinalVote = (int) $learningCourseReportRecord['id_course'];
            }

            $deleteCourseReportIds[] = $learningCourseReportRecord['id_report'];
        }

        if(count($deleteCourseReportIds)) {
            $deleteCourseReportQuery = '
			DELETE FROM %lms_coursereport
			WHERE id_report IN ("' . implode(',', $deleteCourseReportIds) . '") ';

            if($deleteFinalVote) {
                $deleteCourseReportQuery .= 'OR (id_course="'.$deleteFinalVote.'" AND source_of="final_vote")'; 
            }

            $responsecourseReportQuery = sql_query($deleteCourseReportQuery);
        }

        // finding track
        $re_quest_track = sql_query("
		SELECT idTrack 
		FROM %lms_testtrack
		WHERE idTest = '" . $id . "'");
        $id_tracks = [];
        while (list($id_t) = sql_fetch_row($re_quest_track)) {
            $id_tracks[] = $id_t;
        }

        //finding quest
        $reQuest = sql_query('
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class 
		FROM %lms_testquest AS q JOIN ' . $GLOBALS['prefix_lms'] . "_quest_type AS t
		WHERE q.idTest = '" . $id . "' AND q.type_quest = t.type_quest");
        if (!sql_num_rows($reQuest)) {
            return true;
        }
        //deleting answer
        while (list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest)) {
            \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/' . $type_file);

            $quest_obj = eval("return new $type_class( $idQuest );");
            if (!$quest_obj->del()) {
                \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE'));

                return false;
            }
            if (!sql_query("
			DELETE FROM %lms_testtrack_quest
			WHERE idQuest = '" . (int) $idQuest . "'")) {
                \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE'));

                return false;
            }
        }
        // delete tracking
        if (!empty($id_tracks)) {
            if (!sql_query('
			DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_page 
			WHERE idTrack IN ('" . implode(',', $id_tracks) . "') ")) {
                \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE'));

                return false;
            }
        }

        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testtrack WHERE idTest = '" . $id . "'")) {
            \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE'));

            return false;
        }
        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testquest WHERE idTest = '" . $id . "'")) {
            \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE'));

            return false;
        }
        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_lms'] . "_test WHERE idTest = '" . $id . "'")) {
            \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE'));

            return false;
        }

 

        return $id;
    }

    /**
     * function copy( $id, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contain the back url (not used yet)
     *
     * @return int $id if success FALSE if fail
     **/
    public function copy($id, $back_url = null)
    {
        //find source info
        $test_info = sql_fetch_assoc(sql_query('
		SELECT author, title, description, 
			point_type, point_required, 
			display_type, order_type, shuffle_answer, question_random_number, 
			save_keep, mod_doanswer, can_travel, show_only_status, 
			show_score, show_score_cat, show_doanswer, show_solution, 
			time_dependent, time_assigned, penality_test, 
			penality_time_test, penality_quest, penality_time_quest, max_attempt,
			hide_info, order_info,
			use_suspension, suspension_num_attempts, suspension_num_hours, suspension_prerequisites, chart_options,
			mandatory_answer, obj_type, retain_answers_history
		FROM ' . $GLOBALS['prefix_lms'] . "_test 
		WHERE idTest = '" . (int) $id . "'"));

        //insert new item
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_test
		SET author = '" . (int) $test_info['author'] . "',
			title = '" . sql_escape_string($test_info['title']) . "',
			description = '" . sql_escape_string($test_info['description']) . "',
			point_type = '" . (int) $test_info['point_type'] . "',
			point_required = '" . (int) $test_info['point_required'] . "',
			display_type = '" . (int) $test_info['display_type'] . "',
			order_type = '" . (int) $test_info['order_type'] . "',
			shuffle_answer = '" . (int) $test_info['shuffle_answer'] . "',
			question_random_number = '" . (int) $test_info['question_random_number'] . "',
			save_keep = '" . (int) $test_info['save_keep'] . "', 
			mod_doanswer = '" . (int) $test_info['mod_doanswer'] . "', 
			can_travel = '" . (int) $test_info['can_travel'] . "',
			show_only_status = '" . (int) $test_info['show_only_status'] . "',
			show_score = '" . (int) $test_info['show_score'] . "',
			show_score_cat = '" . (int) $test_info['show_score_cat'] . "',
			show_doanswer = '" . (int) $test_info['show_doanswer'] . "',
			show_solution = '" . (int) $test_info['show_solution'] . "',
			time_dependent = '" . (int) $test_info['time_dependent'] . "',
			time_assigned = '" . (int) $test_info['time_assigned'] . "',
			penality_test = '" . (int) $test_info['penality_test'] . "',
			penality_time_test = '" . (int) $test_info['penality_time_test'] . "',
			penality_quest = '" . (int) $test_info['penality_quest'] . "',
			penality_time_quest = '" . (int) $test_info['penality_time_quest'] . "',
			max_attempt = '" . (int) $test_info['max_attempt'] . "',
			hide_info = '" . (int) $test_info['hide_info'] . "',
			order_info = '" . $test_info['order_info'] . "',
			use_suspension = '" . (int) $test_info['use_suspension'] . "',
			suspension_num_attempts = '" . (int) $test_info['suspension_num_attempts'] . "',
			suspension_num_hours = '" . (int) $test_info['suspension_num_hours'] . "',
			suspension_prerequisites = '" . (int) $test_info['suspension_prerequisites'] . "',
			chart_options = '" . $test_info['chart_options'] . "',
			mandatory_answer = '" . (int) $test_info['mandatory_answer'] . "',
			obj_type = '" . $test_info['obj_type'] . "',
			retain_answers_history = '" . $test_info['retain_answers_history'] . "'";
        if (!sql_query($ins_query)) {
            return false;
        }
        list($id_new_test) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if (!$id_new_test) {
            return false;
        }

        //finding quest
        $reQuest = sql_query("
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class 
		FROM %lms_testquest AS q JOIN %lms_quest_type AS t
		WHERE q.idTest = '" . $id . "' AND q.type_quest = t.type_quest");
        //retriving quest
        while (list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest)) {
            $quest_obj = new $type_class($idQuest);
            $new_id = $quest_obj->copy($id_new_test);
            if (!$new_id) {
                $this->del($id_new_test);
                \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE') . ' : ' . $type_class . '( ' . $idQuest . ' )');

                return false;
            }
        }

        //finding assessment_rule
        $reAssRule = sql_query("SELECT test_id, category_id, from_score, to_score, competences_list, courses_list, feedback_txt 
		                        FROM %lms_assessment_rule WHERE test_id='" . $id . "'");
        while (list($test_id, $category_id, $from_score, $to_score, $competences_list, $courses_list, $feedback_txt) = sql_fetch_row($reAssRule)) {
            //insert new assessment_rule
            $ins_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_assessment_rule
			SET test_id = '" . (int) $id_new_test . "',
			category_id = '" . (int) $category_id . "',
			from_score = '" . (int) $from_score . "',
			to_score = '" . (int) $to_score . "',
			competences_list = '" . sql_escape_string($competences_list) . "',
			courses_list = '" . sql_escape_string($courses_list) . "',
			feedback_txt = '" . sql_escape_string($feedback_txt) . "'";
            if (!sql_query($ins_query)) {
                return false;
            }
        }

        return $id_new_test;
    }

    /**
     * function play( $id, $id_param, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param int    $id_param contains the id needed for params retriving
     * @param string $back_url contain the back url
     *
     * @return nothing return
     **/
    public function play($id, $id_param, $back_url)
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/test/do.test.php');

        $this->id = $id;
        $this->back_url = $back_url;

        $step = importVar('next_step');
        switch ($step) {
            case 'test_review':
                review($this, $id_param);
                break;
            case 'play':
                playTestDispatch($this, $id_param);
                break;
            default:
                intro($this, $id_param);
                break;
        }
    }

    public function canBeMilestone()
    {
        return true;
    }

    /**
     * function search( $key ).
     *
     * @param string $key contains the keyword to search
     *
     * @return array with results found
     **/
    public function search($key)
    {
        $output = false;
        $query = "SELECT * FROM %lms_test WHERE title LIKE '%" . $key . "%' OR description LIKE '%" . $key . "%' ORDER BY title";
        $res = $this->db->query($query);
        $results = [];
        if ($res) {
            $output = [];
            while ($row = $this->db->fetch_obj($res)) {
                $output[] = [
                    'id' => $row->idTest,
                    'title' => $row->title,
                    'description' => $row->description,
                ];
            }
        }

        return $output;
    }

    final public static function getTestTypes()
    {
        $event['types'][] = 'test';
        $event = Events::trigger('lms.test.get_types', $event);
        return $event['types'];
    }

    /**
     * @param array $excludedTypes
     *
     * @return Question[]
     */
    public function getQuests($excludedTypes = ['break_page'])
    {
        $objList = [];
        $query = "SELECT q.idQuest, q.type_quest, t.type_file, t.type_class
                  FROM %lms_testquest AS q
                  JOIN %lms_quest_type AS t
                  WHERE idTest = '" . (int) $this->id . "'
                  AND q.type_quest = t.type_quest";
        $query .= ' AND q.type_quest NOT IN (';
        foreach ($excludedTypes as $excludedType) {
            $query .= "'" . $excludedType . "'";
            if (next($excludedTypes) == true) {
                $query .= ',';
            }
        }
        $query .= ')';
        $res = $this->db->query($query);
        while (list($idQuest, $type_quest, $type_file, $type_class) = $this->db->fetch_row($res)) {
            $objList[$idQuest] = new $type_class($idQuest);
        }

        return $objList;
    }

    /**
     * @return bool
     */
    public function isRetainAnswersHistory()
    {
        return $this->retain_answers_history;
    }

    /**
     * @param bool $retain_answers_history
     */
    public function setRetainAnswersHistory($retain_answers_history)
    {
        $this->retain_answers_history = $retain_answers_history;
    }

    /**
     * @return bool
     */
    public function isMandatoryAnswer()
    {
        return $this->mandatory_answer;
    }

    /**
     * @param bool $mandatory_answer
     */
    public function setMandatoryAnswer($mandatory_answer)
    {
        $this->mandatory_answer = $mandatory_answer;
    }

    /**
     * @return bool
     */
    public function isHideInfo()
    {
        return $this->hide_info;
    }

    /**
     * @param bool $hide_info
     */
    public function setHideInfo($hide_info)
    {
        $this->hide_info = $hide_info;
    }

    /**
     * @return mixed
     */
    public function getIdCourse()
    {
        return $this->idCourse;
    }

    /**
     * @param mixed $idCourse
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;
    }

    /**
     * @return mixed
     */
    public function getIdOrg()
    {
        return $this->idOrg;
    }

    /**
     * @param mixed $idOrg
     */
    public function setIdOrg($idOrg)
    {
        $this->idOrg = $idOrg;
    }

    public function canBeCategorized()
    {
        return false;
    }

    public function trackDetails($user, $org)
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orgresults.php');
        getCompilationTable($user, $org);
    }
}
