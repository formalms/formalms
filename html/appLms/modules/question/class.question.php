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

/**
 * Abstarct class for question (refer to Factory design pattners).
 *
 * @category    Question
 *
 * @version    $Id: class.question.php 662 2006-09-22 15:22:38Z fabio $
 *
 * @author    Fabio Pirovano (fabio@docebo.com)
 * @abstract
 */
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.associate.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.break_page.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.choice.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.choice_multiple.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.extended_text.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.inline_choice.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.numerical.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.text_entry.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.title.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.upload.php');

class Question
{
    protected $db;

    /**
     * @var int contains the question identifier
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     **/
    protected $id;

    protected $_table_category;

    protected $title;

    protected $categoryId;

    protected $type;

    protected $difficult;

    protected $testId;

    /**
     * class constructor.
     *
     * @param int $id the unique database identifer of a question
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function __construct($id)
    {
        $this->db = \FormaLms\db\DbConn::getInstance();
        if ($id !== null) {
            $this->id = $id;
            $res = $this->db->query("SELECT idTest, idCategory, type_quest, title_quest, difficult FROM %lms_testquest WHERE idQuest = '" . (int)$id . "'");
            if ($res && $this->db->num_rows($res) > 0) {
                [$this->testId, $this->categoryId, $this->type, $this->title, $this->difficult] = $this->db->fetch_row($res);
            }
        }
        $this->_table_category = $GLOBALS['prefix_lms'] . '_quest_category';

        return;
    }

    /**
     * this function is useful for question recognize.
     *
     * @return string return the identifier of the quetsion
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function getQuestionType()
    {
        return 'question';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Question
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * this function return the sequence value for a new question.
     *
     * @param int $idTest indicates the test selected
     *
     * @return int is the first empty position in question sequencing for the test $idTest
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function _getNextSequence($idTest)
    {
        //select max sequence number
        [$seq] = sql_fetch_row(sql_query('
		SELECT MAX(sequence)
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idTest = '" . $idTest . "'"));

        return $seq + 1;
    }

    /**
     * this function correct the error in the sequence of the question's answer.
     *
     * @return nothing
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function _fixAnswerSequence()
    {
        $re_answer = sql_query('
		SELECT idAnswer 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY sequence, idAnswer");

        $seq = 0;
        while (list($id_answer) = sql_fetch_row($re_answer)) {
            sql_query('
			UPDATE ' . $GLOBALS['prefix_lms'] . "_testquestanswer
			SET sequence = '" . (int)$seq . "' 
			WHERE idAnswer = '" . (int)$id_answer . "'");
            ++$seq;
        }
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
    public function _getPageNumber($idTest)
    {
        [$seq, $page] = sql_fetch_row(sql_query('
		SELECT MAX(sequence), MAX(page)
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idTest = '" . $idTest . "'"));
        if (!$page) {
            return 1;
        }

        [$type_quest] = sql_fetch_row(sql_query('
		SELECT type_quest 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE sequence = '" . $seq . "' AND idTest = '" . $idTest . "'"));
        if ($type_quest == 'break_page') {
            return $page + 1;
        } else {
            return $page;
        }
    }

    /**
     * this function return the score in a good format for a query.
     *
     * @param float $score the score to format
     *
     * @return float the score formatted
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function _checkScore($score)
    {
        $score = preg_replace('[,]', '.', $score);
        if ($score[0] == '.') {
            $score = '0' . $score;
        }

        return $score;
    }

    /**
     * this function create a new question.
     *
     * @param int $idTest indicates the test selected
     * @param string $back_test indicates the return url
     *
     * @return nothing
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function create($idTest, $back_test)
    {
    }

    /**
     * this function modify a question.
     *
     * @param string $back_test indicates the return url
     *
     * @return nothing
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function edit($back_test)
    {
    }

    /**
     * this function delete the question with the idQuest saved in the variable $this->id.
     *
     * @return bool if the operation success return true else return false
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function del()
    {
        return true;
    }

    /**
     * this function create a copy of a question and return the corresponding id
     * usually a son of this class don't need to redefine this function.
     *
     * @return int return the id of the new question if success else return false
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function copy($new_id_test, $back_test = null)
    {
        //retriving question information
        [$idCategory, $type_quest, $title_quest, $difficult, $time_assigned, $sequence, $page, $shuffle] = sql_fetch_row(sql_query('
		SELECT idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . (int)$this->id . "'"));

        //insert the question copy
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
		( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle ) VALUES 
		( 	'" . (int)$new_id_test . "', 
			'" . (int)$idCategory . "', 
			'" . $this->getQuestionType() . "', 
			'" . sql_escape_string(addslashes($title_quest)) . "',
			'" . (int)$difficult . "', 
			'" . $time_assigned . "',
			'" . (int)$sequence . "',
			'" . (int)$page . "', 
			'" . (int)$shuffle . "' ) ";
        if (!sql_query($ins_query)) {
            return false;
        }

        //find the id of the inserted question
        [$new_id_quest] = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if (!$new_id_quest) {
            return false;
        }

        //retriving new answer
        $re_answer = sql_query('
		SELECT idAnswer, sequence, is_correct, answer, comment, score_correct, score_incorrect 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY idAnswer");

        $map_answer[0] = 0;
        while (list($idAnswer, $seq, $is_correct, $answer, $comment, $score_c, $score_inc) = sql_fetch_row($re_answer)) {
            //insert answer
            $ins_answer_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
			( idQuest, sequence, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
			( 	'" . (int)$new_id_quest . "', 
				'" . (int)$seq . "', 
				'" . (int)$is_correct . "', 
				'" . sql_escape_string(addslashes($answer)) . "', 
				'" . sql_escape_string(addslashes($comment)) . "',
				'" . $this->_checkScore($score_c) . "', 
				'" . $this->_checkScore($score_inc) . "') ";
            if (!sql_query($ins_answer_query)) {
                return false;
            }

            [$map_answer[$idAnswer]] = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        }

        //retriving extra information for this question
        $re_extra = sql_query('
		SELECT idAnswer, extra_info 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest_extra 
		WHERE idQuest = '" . (int)$this->id . "'");

        // save all the extra info, if there are
        while (list($id_answer, $title_info) = sql_fetch_row($re_extra)) {
            if (!sql_query('
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest_extra 
			( idQuest, idAnswer, extra_info ) VALUES 
			( 	'" . (int)$new_id_quest . "', 
				'" . (int)$map_answer[$id_answer] . "', 
				'" . sql_escape_string($title_info) . "' )")) {
                return false;
            }
        }

        return $new_id_quest;
    }

    public function import($format, $back_test = null)
    {
    }

    public function export($id, $format, $back_test = null)
    {
    }

    /**
     * display the quest for play, if.
     *
     * @param int $num_quest the number of the quest to display in front of the quest title
     * @param bool $shuffle_answer randomize the answer display order
     * @param int $id_track where find the answer, if find -> load
     * @param bool $freeze if true, when load disable the user interaction
     * @param int $number_time the actual number of attempt
     *
     * @return string of html question code
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function play($num_quest, $shuffle_answer = false, $id_track = 0, $freeze = false, $number_time = null)
    {
        return '';
    }

    /**
     * return true if the user as done this question.
     *
     * @param int $id_track the relative id_track
     *
     * @return bool true if success false otherwise
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function userDoAnswer($id_track)
    {
        $recover_answer = '
		SELECT idAnswer 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
		WHERE idQuest = '" . (int)$this->id . "' AND 
			idTrack = '" . (int)$id_track . "'";
        $re_answer_do = sql_query($recover_answer);

        if (sql_num_rows($re_answer_do)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * save the answer to the question in an proper format.
     *
     * @param int $id_track the relative id_track
     * @param array $source source of the answer send by the user
     * @param bool $can_overwrite if the answer for this question exists and this is true, the old answer
     *                             is updated, else the old answer will be leaved
     *
     * @return bool true if success false otherwise
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function storeAnswer(Track_Test $trackTest, &$source, $can_overwrite = false)
    {
        return true;
    }

    /**
     * save the answer to the question in an proper format overwriting the old entry.
     *
     * @param int $id_track the relative id_track
     * @param array $source source of the answer send by the user
     *
     * @return bool true if success false otherwise
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function updateAnswer($id_track, &$source, $numberTime = null)
    {
        return true;
    }

    /**
     * delete the old answer.
     *
     * @param int $id_track the relative id_track
     *
     * @return bool true if success false otherwise
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function deleteAnswer($id_track, $numberTime = '')
    {
        $query = 'DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
		WHERE idTrack = '" . (int)$id_track . "' AND 
			idQuest = '" . $this->id . "'";

        if (!empty($numberTime) && is_numeric($numberTime)) {
            $query .= " AND number_time = '" . $numberTime . "'";
        }

        return sql_query($query);
    }

    /**
     * force a score to a question.
     */
    public function setUserScore($id_track, $id_quest, $new_score)
    {
        switch ($this->getScoreSetType()) {
            case 'manual':
                $query_update = '
				UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
				SET score_assigned = '" . $new_score . "', 
					manual_assigned = '1'
				WHERE idTrack = '" . $id_track . "' AND idQuest = '" . $id_quest . "'";

                return sql_query($query_update);

                break;
            case 'auto':
                $sel_answer = '
				SELECT idAnswer
				FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
				WHERE idTrack = '" . $id_track . "' AND idQuest = '" . $id_quest . "'";
                [$id_answer] = sql_fetch_row(sql_query($sel_answer));

                $query_update = '
				UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
				SET score_assigned = '0'
				WHERE idTrack = '" . $id_track . "' AND idQuest = '" . $id_quest . "'";
                $re = sql_query($query_update);

                $query_update = '
				UPDATE ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
				SET score_assigned = '" . $new_score . "'
				WHERE idTrack = '" . $id_track . "' AND idQuest = '" . $id_quest . "' AND idAnswer = '" . $id_answer . "'";
                $re &= sql_query($query_update);

                return $re;

                break;
        }
    }

    /**
     * get the method used to obtain result automatic or manual.
     *
     * @return string contain one of these value :
     *                'none' if the question doesn't return any score (such as title or break_page)
     *                'manual' if the score is set by a user,
     *                'auto' if the system automatical assign a result
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function getScoreSetType()
    {
        return 'none';
    }

    /**
     * get the maximum score for the question.
     *
     * @return float the maximum score for a correct answer
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function getMaxScore()
    {
        $max_score = 0;
        $re_answer = sql_query('
		SELECT score_correct
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int)$this->id . "'");
        while (list($score_correct) = sql_fetch_row($re_answer)) {
            $max_score = $max_score + $score_correct;
        }

        return round($max_score, 2);
    }

    /**
     * set the maximum score for the question.
     *
     * @param float $score the score that you want to set
     *
     * @return float return the effective point that will be assigned to the question
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function getRealMaxScore($score)
    {
        [$num_correct] = sql_fetch_row(sql_query('
		SELECT COUNT(*)
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int)$this->id . "' AND is_correct = '1'"));

        if (!$num_correct) {
            $score_assigned = 0;
        } else {
            $score_assigned = round($score / $num_correct, 3);
        }

        return round($score_assigned * $num_correct, 2);
    }

    /**
     * set the maximum score for the question.
     *
     * @param float $score the score assigned to the question
     *
     * @return float contain the new maximum score for the question, can be different from the param $score
     *               because can be round
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function setMaxScore($score)
    {
        [$num_correct] = sql_fetch_row(sql_query('
		SELECT COUNT(*)
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int)$this->id . "' AND is_correct = '1'"));

        if (!$num_correct) {
            $score_assigned = 0;
        } else {
            $score_assigned = round($score / $num_correct, 3);
        }

        $re_assign = sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		SET score_correct = '0' 
		WHERE idQuest = '" . (int)$this->id . "' AND is_correct = '0'");

        $re_assign = sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		SET score_correct = '" . $score_assigned . "' 
		WHERE idQuest = '" . (int)$this->id . "' AND is_correct = '1'");
        if (!$re_assign) {
            return 0;
        } else {
            return round($score_assigned * $num_correct, 2);
        }
    }

    /**
     * return the user score for this question.
     *
     * @param int $id_track the test relative to this question
     * @param int $number_time the number of the attempt
     *
     * @return float return the score for the user or 0 if there isn't a track for the question
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function userScore($id_track, $number_time = null)
    {
        $score = 0;
        $query = 'SELECT score_assigned
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer
		WHERE idQuest = '" . (int)$this->id . "'
		AND idTrack = '" . (int)$id_track . "'";
        if ($number_time != null) {
            $query .= ' AND number_time = ' . $number_time;
        }
        $query .= ' ORDER BY number_time DESC LIMIT 1';
        $re_answer = sql_query($query);
        if (!sql_num_rows($re_answer)) {
            return $score;
        }
        while (list($score_assigned) = sql_fetch_row($re_answer)) {
            $score = round($score + $score_assigned, 2);
        }

        return $score;
    }

    /**
     * display the question with the result of a user.
     *
     * @param int $id_track the test relative to this question
     * @param int $num_quest the quest sequence number
     * @param int $number_time the quest attempt number
     *
     * @return array return an array with xhtml code in this way
     *               string    'quest'    => the quest,
     *               double    'score'        => score obtained from this question,
     *               string    'comment'    => relative comment to the quest
     *               bool    'manual_assigned'    => if the score is alredy assigned manually, this is true
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function displayUserResult($id_track, $num_quest, $show_solution, $number_time = null)
    {
        return [
            'quest' => $num_quest . ' : displayUserResult() not defined ! ' . $this->getQuestionType() . '<br />',
            'score' => 0,
            'comment' => '',
            'manual_assigned' => 0,
        ];
    }

    public function importFromRaw($raw_quest, $id_test = false, $sequence = 1)
    {
        if ($id_test === false) {
            $id_test = 0;
        }

        //insert question
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
		( idQuest, idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
		( 	NULL,
			'" . (int)$id_test . "', 
			'" . (int)$raw_quest->id_category . "', 
			'" . $this->getQuestionType() . "',
			'" . $raw_quest->quest_text . "',
			'" . (int)$raw_quest->difficult . "', 
			'" . $raw_quest->time_assigned . "',
			'" . $sequence . "',
			'1' ) ";
        if (!sql_query($ins_query)) {
            return false;
        }

        //find id of auto_increment colum
        [$new_id_quest] = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if (!$new_id_quest) {
            return false;
        }

        if (!is_array($raw_quest->answers)) {
            return $new_id_quest;
        }

        //reset($raw_quest->answers);
        foreach ($raw_quest->answers as $raw_answer) {
            //insert answer
            $ins_answer_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
			( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
			( 	'" . (int)$new_id_quest . "', 
				'" . (int)$raw_answer->is_correct . "', 
				'" . addslashes($raw_answer->text) . "', 
				'" . addslashes($raw_answer->comment) . "',
				'" . $this->_checkScore($raw_answer->score_correct) . "', 
				'" . $this->_checkScore($raw_answer->score_penalty) . "') ";
            if (!sql_query($ins_answer_query)) {
                return false;
            }
        }

        return $new_id_quest;
    }

    public function getCategoryName($id_cat = null)
    {
        if ($id_cat == null) {
            $id_cat = $this->categoryId;
        }

        $name = '';
        $qtxt = 'SELECT name '
            . 'FROM ' . $this->_table_category . ' '
            . "WHERE idCategory = '" . $id_cat . "' ";
        $re = sql_query($qtxt);
        [$name] = sql_fetch_row($re);

        return $name;
    }

    public function exportToRaw($id_test = false)
    {
        //retriving question information
        [$idCategory, $type_quest, $title_quest, $difficult, $time_assigned] = sql_fetch_row(sql_query('
		SELECT idCategory, type_quest, title_quest, difficult, time_assigned 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . (int)$this->id . "'"));

        //insert the question copy
        $oQuest = new QuestionRaw();
        $oQuest->id = $this->id;
        $oQuest->qtype = $this->getQuestionType();

        $oQuest->id_category = $this->getCategoryName($idCategory);
        $oQuest->quest_text = $title_quest;
        $oQuest->difficult = $difficult;
        $oQuest->time_assigned = $time_assigned;

        $oQuest->answers = [];
        $oQuest->extra_info = [];

        //retriving new answer
        $re_answer = sql_query('
		SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY idAnswer");
        while (list($idAnswer, $is_correct, $answer, $comment, $score_c, $score_inc) = sql_fetch_row($re_answer)) {
            $oAnswer = new AnswerRaw();

            $oAnswer->is_correct = $is_correct;
            $oAnswer->text = $answer;
            $oAnswer->comment = $comment;
            $oAnswer->score_correct = $score_c;
            $oAnswer->score_penalty = $score_inc;

            $oQuest->answers[] = $oAnswer;
        }

        //retriving extra information for this question
        $re_extra = sql_query('
		SELECT idAnswer, extra_info 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest_extra 
		WHERE idQuest = '" . (int)$this->id . "'");

        // save all the extra info, if there are
        while (list($id_answer, $title_info) = sql_fetch_row($re_extra)) {
            $oAnswer = new AnswerRaw();
            $oAnswer->text = $title_info;

            $oQuest->extra_info[] = $oAnswer;
        }

        // Customfield
        require_once _adm_ . '/lib/lib.customfield.php';
        $fman = new CustomFieldList();
        $fman->setFieldArea('LO_TEST');
        $oQuest->customfield = $fman->playFieldsFlat($this->id);

        return $oQuest;
    }

    public static function getTestQuestsFromTest($idTest)
    {
        $query_quest = 'SELECT idQuest, type_quest, title_quest'
            . ' FROM %lms_testquest'
            . " WHERE idTest = '" . $idTest . "'"
            . ' ORDER BY sequence';

        $result_quest = sql_query($query_quest);

        $quests = [];
        while (list($idQuest, $type_quest, $title_quest) = sql_fetch_row($result_quest)) {
            $quests[$idQuest]['idQuest'] = $idQuest;
            $quests[$idQuest]['type_quest'] = $type_quest;
            $quests[$idQuest]['title_quest'] = $title_quest;
        }

        return $quests;
    }

    public static function getTestQuestAnswerFromQuestAndStudents($idQuest, $idStudents)
    {
        $query_answer = 'SELECT tqa.idAnswer, tqa.is_correct, tqa.answer'
            . ' FROM %lms_testquestanswer AS tqa'
            . ' LEFT JOIN'
            . ' %lms_testtrack_answer tta ON tqa.idAnswer = tta.idAnswer'
            . ' LEFT JOIN'
            . ' %lms_testtrack tt ON tt.idTrack = tta.idTrack'
            . " WHERE tqa.idQuest = '" . $idQuest . "'";
        $query_answer .= ' and tt.idUser in (' . implode(',', $idStudents) . ')';
        $query_answer .= ' ORDER BY tqa.sequence';

        $result_answer = sql_query($query_answer);

        $answers = [];

        while (list($id_answer, $is_correct, $answer) = sql_fetch_row($result_answer)) {
            $answers[$idQuest][$id_answer] = ['idAnswer' => $id_answer, 'is_correct' => $is_correct, 'answer' => $answer];
        }

        return $answers;
    }

    protected function testQuestAnswerExists($trackTest)
    {
        $idTrack = (int)$trackTest->idTrack;
        $idQuest = (int)$this->id;
        $number_time = (int)($trackTest->getNumberOfAttempt() + 1);

        $track_query = 'SELECT idTrack FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer
										WHERE idTrack = $idTrack AND idQuest = $idQuest AND number_time = $number_time";

        $res = sql_query($track_query);
        [$exists] = sql_fetch_row($res);

        return (bool)$exists;
    }
}

class QuestionRaw
{
    public $qtype = null;

    public $id_category = 0;
    public $prompt = false;
    public $quest_text = false;
    public $difficult = 3;
    public $time_assigned = 0;
    public $answers = [];
    public $extra_info = [];
    public int $id;
    public $customfield;
    public int $textformat;

    public function setCategoryFromName($category_name, $autocreate_categories = false)
    {
        $qtxt = 'SELECT idCategory '
            . 'FROM %lms_quest_category '
            . "WHERE name = '" . $category_name . "' AND ( author = 0 OR author = " . (int)\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . ' ) ';
        $re = sql_query($qtxt);
        if (!$re || !sql_num_rows($re)) {
            if ($autocreate_categories && $category_name) {
                $qins = 'INSERT INTO ' . $GLOBALS['prefix_lms'] . "_quest_category (name, author) VALUES ('$category_name', 0)";
                if (sql_query($qins)) {
                    $this->id_category = sql_insert_id();
                }
            } else {
                $this->id_category = 0;
            }
        } else {
            [$this->id_category] = sql_fetch_row($re);
        }
    }
}

class AnswerRaw
{
    public $is_correct = 0;
    public $text = false;
    public $comment = false;
    public $score_correct = 0;
    public $score_penalty = 0;
    public int $id_answer;
    public $tolerance;
}
