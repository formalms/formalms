<?php

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

if (!defined('IN_FORMA')) die('You cannot access this file directly');

require_once(_lms_ . '/modules/question/class.question.php');

class CourseValutation_Question extends Question
{

    /**
     * class constructor
     *
     * @param int    the unique database identifer of a question
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function CourseValutation_Question($id)
    {
        parent::Question($id);
    }

    /**
     * this function is useful for question recognize
     *
     * @return string    return the identifier of the quetsion
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function getQuestionType()
    {
        return 'course_valutation';
    }

    /**
     * this function write a gui line for answer insertion
     *
     * @param  int $i indicate the line number
     * @return nothing
     *
     * @access private
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function _lineAnswer($i)
    {
        $lang =& DoceboLanguage::createInstance('test');

        $GLOBALS['page']->add('<tr class="line_answer">'
            . '<td class="image">'
            //answer
            . '<label class="access-only" for="answer_' . $i . '">' . $lang->def('_ANSWER') . '</label>'
            . '<textarea class="test_area_answer" id="answer_' . $i . '" name="answer[' . $i . ']" cols="50" rows="3">'
            . (isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : $lang->def('_QUEST_ANSWER'))
            . '</textarea>'
            . '</td>'
            . '</tr>' . "\n", 'content');
    }

    /**
     * this function write a gui line for answer insertion,projected for modify
     *
     * @param  int $i indicate the line number
     * @return nothing
     *
     * @access private
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function _lineModAnswer($i)
    {
        $lang =& DoceboLanguage::createInstance('test');

        $GLOBALS['page']->add('<tr class="line_answer">'
            . '<td class="image">', 'content');
        if (isset($_POST['answer_id'][$i])) {
            $GLOBALS['page']->add('<input type="hidden" id="answer_id_' . $i . '" name="answer_id[' . $i . ']" value="' . $_POST['answer_id'][$i] . '" />', 'content');
        }
        $GLOBALS['page']->add(
            '<label class="access-only" for="answer_' . $i . '">' . $lang->def('_ANSWER') . '</label>'
            . '<textarea class="test_area_answer" id="answer_' . $i . '" name="answer[' . $i . ']" cols="50" rows="3">'
            . (isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : '')
            . '</textarea>'
            . '</td>'
            . '</tr>' . "\n", 'content');
    }

    /**
     * this function create a new question
     *
     * @param  int $idTest indicates the test selected
     * @param  string $back_test indicates the return url
     * @return nothing
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function create($idTest, $back_test)
    {
        $lang =& DoceboLanguage::createInstance('test');

        require_once(_base_ . '/lib/lib.form.php');
        $url_encode = htmlentities(urlencode($back_test));

        if (isset($_POST['add_question'])) {
            //insert the new question

            $min_value = Get::req('min_value', DOTY_INT, 0);
            $max_value = Get::req('max_value', DOTY_INT, 0);
            $step_value = (float)str_replace(',', '.', $_POST['step_value']);

            $i = $min_value;
            $seq = 0;

            if ($min_value >= $max_value || $step_value == 0)
                $GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER') . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');
            else {
                $ins_query = "
				INSERT INTO " . $GLOBALS['prefix_lms'] . "_testquest
				( idTest, idCategory, type_quest, title_quest, sequence, page ) VALUES
				( 	'" . $idTest . "',
					'" . (int)$_POST['idCategory'] . "',
					'" . $this->getQuestionType() . "',
					'" . $_POST['title_quest'] . "',
					'" . (int)$this->_getNextSequence($idTest) . "',
					'" . $this->_getPageNumber($idTest) . "' ) ";
                if (!sql_query($ins_query)) {
                    $GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_QUEST')
                        . getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
                            . $this->getQuestionType() . '&amp;idTest=' . $idTest . '&amp;back_test=' . $url_encode, $lang->def('_BACK'))), 'content');
                }
                //find id of auto_increment colum
                list($idQuest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
                if (!$idQuest)
                    $GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER') . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');

                $query = "INSERT INTO " . $GLOBALS['prefix_lms'] . "_testquestanswer"
                    . " (idQuest, answer, sequence)"
                    . " VALUES ('" . $idQuest . "', '" . $min_value . "', '0'),"
                    . " ('" . $idQuest . "', '" . $max_value . "', '1'),"
                    . " ('" . $idQuest . "', '" . $step_value . "', '2');";

                if (!sql_query($query))
                    $GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER') . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');
                else
                    Util::jump_to('' . $back_test);
            }
        }

        //insert form
        require_once(_lms_ . '/lib/lib.questcategory.php');
        $categories = Questcategory::getCategory();
        //writing difficult array

        $GLOBALS['page']->add(
            getTitleArea($lang->def('_POLL_SECTION'), 'test')
            . '<div class="std_block">'
            . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK'))

            . '<div class="title_big">'
            . $lang->def('_QUEST_ACRN_' . strtoupper($this->getQuestionType())) . ' - '
            . $lang->def('_QUEST_' . strtoupper($this->getQuestionType()))
            . '</div><br />'
            . Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=create')

            . Form::openElementSpace()
            . Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
            . Form::getHidden('idTest', 'idTest', $idTest)
            . Form::getHidden('back_test', 'back_test', $url_encode)

            . Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest',
                (isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : ''))
            . Form::getDropdown($lang->def('_CATEGORY'), 'idCategory', 'idCategory', $categories,
                (isset($_POST['idCategory']) ? $_POST['idCategory'] : 0))
            . '<div class="no_float"></div><br />'
            . Form::getTextfield($lang->def('_MIN_VALUE'), 'min_value', 'min_value', 255)
            . Form::getTextfield($lang->def('_MAX_VALUE'), 'max_value', 'max_value', 255)
            . Form::getTextfield($lang->def('_STEP_VALUE'), 'step_value', 'step_value', 255, '1'), 'content');
        $GLOBALS['page']->add(
            Form::closeElementSpace()

            . Form::openButtonSpace()
            . Form::getButton('add_question', 'add_question', $lang->def('_INSERT'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>', 'content');
    }

    /**
     * this function modify a question
     *
     * @param  string $back_test indicates the return url
     * @return nothing
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function edit($back_test)
    {
        $lang =& DoceboLanguage::createInstance('test');

        require_once(_base_ . '/lib/lib.form.php');
        $url_encode = htmlentities(urlencode($back_test));

        //manage number of answer
        if (isset($_POST['save_question'])) {
            //update question
            $ins_query = "
			UPDATE " . $GLOBALS['prefix_lms'] . "_testquest
			SET idCategory = '" . (int)$_POST['idCategory'] . "',
				type_quest = '" . $this->getQuestionType() . "',
				title_quest = '" . $_POST['title_quest'] . "'
			WHERE idQuest = '" . (int)$this->id . "'";
            if (!sql_query($ins_query)) {

                getErrorUi($lang->def('_POLL_ERR_INS_QUEST')
                    . getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
                        . $this->getQuestionType() . '&amp;idQuest=' . $this->id . '&amp;back_test=' . $url_encode, $lang->def('_BACK')));
            }
            //update answer
            if (!isset($_POST['is_correct'])) $_POST['is_correct'] = -1;

            $min_value = Get::req('min_value', DOTY_INT, 0);
            $max_value = Get::req('max_value', DOTY_INT, 0);
            $step_value = (float)str_replace(',', '.', $_POST['step_value']);

            if ($min_value >= $max_value || $step_value == 0)
                $GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER') . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');
            else {
                $del_answer_query = "DELETE FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
									WHERE idQuest = '" . (int)$this->id . "'";
                if (!sql_query($del_answer_query))
                    getErrorUi($lang->def('_POLL_ERR_INS_ANSWER') . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK')));
                else {
                    $query = "INSERT INTO " . $GLOBALS['prefix_lms'] . "_testquestanswer"
                        . " (idQuest, answer, sequence)"
                        . " VALUES ('" . $this->id . "', '" . $min_value . "', '0'),"
                        . " ('" . $this->id . "', '" . $max_value . "', '1'),"
                        . " ('" . $this->id . "', '" . $step_value . "', '2');";

                    if (!sql_query($query))
                        getErrorUi($lang->def('_POLL_ERR_INS_ANSWER') . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK')));
                    else
                        Util::jump_to($back_test);
                }
            }
        }

        //insert form
        require_once(_lms_ . '/lib/lib.questcategory.php');
        $categories = Questcategory::getCategory();
        //writing difficult

        //load data
        list($sel_cat, $quest) = sql_fetch_row(sql_query("
		SELECT idCategory, title_quest
		FROM " . $GLOBALS['prefix_lms'] . "_testquest
		WHERE idQuest = '" . (int)$this->id . "'"));

        $re_answer = sql_query("
		SELECT idAnswer, answer
		FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY sequence");

        $array_answer = array();

        while (list($id_answer, $answer) = sql_fetch_row($re_answer))
            $array_answer[] = $answer;
        if (!empty($array_answer)) {
            $min_value = $array_answer[0];
            $max_value = $array_answer[1];
            $step_value = $array_answer[2];
        } else {
            $min_value = '';
            $max_value = '';
            $step_value = '1';
        }

        $GLOBALS['page']->add(
            getTitleArea($lang->def('_POLL_SECTION'), 'test')
            . '<div class="std_block">'
            . getBackUi(str_replace('&', '&amp;', $back_test), $lang->def('_BACK'))
            . '<div class="title_big">'
            . $lang->def('_QUEST_ACRN_' . strtoupper($this->getQuestionType())) . ' - '
            . $lang->def('_QUEST_' . strtoupper($this->getQuestionType()))
            . '</div><br />'
            . Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=edit')

            . Form::openElementSpace()
            . Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
            . Form::getHidden('idQuest', 'idQuest', $this->id)
            . Form::getHidden('back_test', 'back_test', $url_encode)

            . Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest',
                (isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : $quest))
            . Form::getDropdown($lang->def('_CATEGORY'), 'idCategory', 'idCategory', $categories,
                (isset($_POST['idCategory']) ? $_POST['idCategory'] : $sel_cat))
            . '<div class="no_float"></div><br />'
            . Form::getTextfield($lang->def('_MIN_VALUE'), 'min_value', 'min_value', 255, $min_value)
            . Form::getTextfield($lang->def('_MAX_VALUE'), 'max_value', 'max_value', 255, $max_value)
            . Form::getTextfield($lang->def('_STEP_VALUE'), 'step_value', 'step_value', 255, $step_value), 'content');

        $GLOBALS['page']->add(
            Form::closeElementSpace()
            . Form::openButtonSpace()
            . Form::getButton('save_question', 'save_question', $lang->def('_SAVE'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>', 'content');
    }

    /**
     * this function delete the question with the idQuest saved in the variable $this->id
     *
     * @return bool    if the operation success return true else return false
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function del()
    {


        //delete answer
        if (!sql_query("
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer
		WHERE idQuest = '" . $this->id . "'")
        ) return false;

        //remove answer
        if (!sql_query("
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
		WHERE idQuest = '" . $this->id . "'")
        ) {
            return false;
        }
        //remove question
        return sql_query("
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_testquest
		WHERE idQuest = '" . $this->id . "'");
    }

    /**
     * this function create a copy of a question and return the corresponding id
     *
     * @return int    return the id of the new question if success else return false
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function copy($new_idTest, $back_test = NULL)
    {

        //retriving question
        list($sel_cat, $quest, $sequence, $page) = sql_fetch_row(sql_query("
		SELECT idCategory, title_quest, sequence, page
		FROM " . $GLOBALS['prefix_lms'] . "_testquest
		WHERE idQuest = '" . (int)$this->id . "'"));

        //insert question
        $ins_query = "
		INSERT INTO " . $GLOBALS['prefix_lms'] . "_testquest
		( idTest, idCategory, type_quest, title_quest, sequence, page ) VALUES
		( 	'" . (int)$new_idTest . "',
			'" . (int)$sel_cat . "',
			'" . $this->getQuestionType() . "',
			'" . sql_escape_string($quest) . "',
			'" . (int)$sequence . "',
			'" . (int)$page . "' ) ";
        if (!sql_query($ins_query)) return false;
        //find id of auto_increment colum
        list($new_idQuest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
        if (!$new_idQuest) return false;

        //retriving new answer
        $re_answer = sql_query("
		SELECT answer, sequence
		FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY idAnswer");
        while (list($answer, $sequence) = sql_fetch_row($re_answer)) {

            //insert answer
            $ins_answer_query = "
			INSERT INTO " . $GLOBALS['prefix_lms'] . "_testquestanswer
			( idQuest, answer, sequence ) VALUES
			( 	'" . $new_idQuest . "',
				'" . sql_escape_string($answer) . "',
				'" . $sequence . "' ) ";
            if (!sql_query($ins_answer_query)) {

                return false;
            }
        }
        return $new_idQuest;
    }

    /**
     * display the quest for play, if
     *
     * @param    int $num_quest the number of the quest to display in front of the quest title
     * @param    bool $shuffle_answer randomize the answer display order
     * @param    int $idTrack where find the answer, if find -> load
     * @param    bool $freeze if true, when load disable the user interaction
     *
     * @return string of html question code
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function play($num_quest, $shuffle_answer = false, $idTrack = 0, $freeze = false)
    {
        $lang =& DoceboLanguage::createInstance('test');

        list($idQuest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest
		FROM " . $GLOBALS['prefix_lms'] . "_testquest
		WHERE idQuest = '" . $this->id . "'"));

        $query_answer = "
		SELECT answer
		FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
		WHERE idQuest = '" . (int)$this->id . "'"
            . " ORDER BY sequence";
        $re_answer = sql_query($query_answer);

        $find_prev = false;
        $id_answer_do = 0;
        if ($idTrack != 0) {
            //recover previous information
            $recover_answer = "
			SELECT more_info
			FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer
			WHERE idQuest = '" . (int)$this->id . "' AND
				idTrack = '" . (int)$idTrack . "'";
            $re_answer_do = sql_query($recover_answer);
            if (sql_num_rows($re_answer_do)) {

                //find previous answer
                $find_prev = true;
                list($answer_do) = sql_fetch_row($re_answer_do);
            }
        }

        $content =
            '<div class="play_question">'
            . '<div class="title_question">' . $num_quest . ') ' . $title_quest . '</div>'
            . '<div class="answer_question">';

        $answer_info = array();

        while (list($answer) = sql_fetch_row($re_answer))
            $answer_info[] = $answer;

        $num_answer = 0;

        for ($i = $answer_info[0]; $i <= $answer_info[1]; $i += $answer_info[2]) {
            $content .= '<input type="radio" id="quest_' . $idQuest . '_' . $num_answer . '" '
                . 'name="quest[' . $idQuest . ']" value="' . $i . '"'
                . (($find_prev && $i == $id_answer_do) ? ' checked="checked"' : '')
                . ($find_prev && $freeze ? ' disabled="disabled"' : '') . ' /> '
                . '<label class="text_answer" for="quest_' . $idQuest . '_' . $num_answer . '">' . $i . '</label><br />';

            $num_answer++;
        }

        $content .= '</div>'
            . '</div>';
        return $content;
    }

    /**
     * display the quest for report
     *
     * @param    int $num_quest the number of the quest to display in front of the quest title
     *
     * @return string of html question code
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function playReport($num_quest, $tot_tracks, &$validTrack)
    {
        $lang =& DoceboLanguage::createInstance('test');

        require_once(_lms_ . '/lib/lib.course.php');

        list($idQuest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest
		FROM " . $GLOBALS['prefix_lms'] . "_testquest
		WHERE idQuest = '" . $this->id . "'"));

        $query_answer = "
		SELECT answer
		FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY sequence";
        $re_answer = sql_query($query_answer);

        $answer_info = array();

        while (list($answer) = sql_fetch_row($re_answer))
            $answer_info[] = $answer;

        //recover previous information
        $max = 0;
        $not_answer = $tot_tracks;
        $recover_answer = "
		SELECT more_info, COUNT(*)
		FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer
		WHERE idQuest = '" . (int)$this->id . "' ";
        if (is_array($validTrack) && !empty($validTrack)) $recover_answer .= " AND idTrack IN ( " . implode(',', $validTrack) . " ) ";
        $recover_answer .= " GROUP BY more_info ";
        $re_answer_do = sql_query($recover_answer);

        //find previous answer
        while (list($id_a, $num) = sql_fetch_row($re_answer_do)) {
            if ($num > $max) $max = $num;
            $num_answer[$id_a] = $num;
            $not_answer -= $num;
        }
        $content =
            '<div class="play_question">'
            . '<div class="title_question">' . $num_quest . ') ' . $title_quest . '</div>'
            . '<div class="answer_question">'
            . '<table summary="' . $lang->def('_SUMMARY_ANSWER') . '" cellspancing="0" class="test_report">'
            . '<caption>' . $lang->def('_CAPTION_ANSWER') . '</caption>'
            . '<thead>'
            . '<tr><th class="text_answer" scope="col">' . $lang->def('_ANSWER_TEXT') . '</th><th scope="col">' . $lang->def('_ANSWER_NUMBER') . '</th></tr>'
            . '</thead>';

        for ($i = $answer_info[0]; $i <= $answer_info[1]; $i += $answer_info[2]) {
            if (isset($num_answer[(string)$i]) && $max != 0)
                $content .= '<tr><td>' . $i . '</td><td><div class="colored_row" style="width: ' . round($num_answer[(string)$i] / $max * 90, 2) . '%;">'
                    . $num_answer[(string)$i] . '</div></td></tr>';
            else
                $content .= '<tr><td>' . $i . '</td><td><div class="colored_row" style="width: 0%;">'
                    . '</div></td></tr>';
        }

        $content .= '</table>'
            . '</div>'
            . '</div>';

        return $content;
    }

    /**
     * save the answer to the question in an proper format
     *
     * @param  int $idTrack the relative idTrack
     * @param  array $source source of the answer send by the user
     * @param  bool $can_overwrite if the answer for this question exists and this is true, the old answer
     *                                    is updated, else the old answer will be leaved
     *
     * @return bool    true if success false otherwise
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function storeAnswer($idTrack, &$source, $can_overwrite = false)
    {
        $result = true;

        if ($this->userDoAnswer($idTrack)) {
            if (!$can_overwrite) return true;
            if (!$this->deleteAnswer($idTrack)) return false;
        }

        if (isset($source['quest'][$this->id])) {
            //answer checked by the user
            $track_query = "
			INSERT INTO " . $GLOBALS['prefix_lms'] . "_testtrack_answer ( idTrack, idQuest, idAnswer, more_info )
			VALUES (
				'" . (int)$idTrack . "',
				'" . (int)$this->id . "',
				'0',
				'" . $source['quest'][$this->id] . "' )";
            return sql_query($track_query);
        }
    }

    /**
     * delete the old answer
     *
     * @param  int $idTrack the relative idTrack
     *
     * @return bool    true if success false otherwise
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function deleteAnswer($idTrack)
    {
        return sql_query("
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer
		WHERE idTrack = '" . (int)$idTrack . "' AND
			idQuest = '" . $this->id . "'");
    }

    function export_CSV($num_quest, $tot_tracks, &$validTrack)
    {
        $lang =& DoceboLanguage::createInstance('test');

        require_once(_lms_ . '/lib/lib.course.php');

        list($idQuest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest
		FROM " . $GLOBALS['prefix_lms'] . "_testquest
		WHERE idQuest = '" . $this->id . "'"));

        $query_answer = "
		SELECT answer
		FROM " . $GLOBALS['prefix_lms'] . "_testquestanswer
		WHERE idQuest = '" . (int)$this->id . "'
		ORDER BY sequence";
        $re_answer = sql_query($query_answer);

        $answer_info = array();

        while (list($answer) = sql_fetch_row($re_answer))
            $answer_info[] = $answer;

        //recover previous information
        $max = 0;
        $not_answer = $tot_tracks;
        $recover_answer = "
		SELECT more_info, COUNT(*)
		FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer
		WHERE idQuest = '" . (int)$this->id . "' ";
        if (is_array($validTrack) && !empty($validTrack)) $recover_answer .= " AND idTrack IN ( " . implode(',', $validTrack) . " ) ";
        $recover_answer .= " GROUP BY more_info ";
        $re_answer_do = sql_query($recover_answer);

        //find previous answer
        while (list($id_a, $num) = sql_fetch_row($re_answer_do)) {

            if ($num > $max) $max = $num;
            $num_answer[$id_a] = $num;
            $not_answer -= $num;
        }
        $content = '"' . $num_quest . '";"' . str_replace('"', '""', $title_quest) . '";"' . $lang->def('_QUEST_COURSE_VALUTATION') . '"' . "\r\n";

        for ($i = $answer_info[0]; $i <= $answer_info[1]; $i += $answer_info[2]) {
            if (isset($num_answer[(string)$i]) && $max != 0)
                $content .= ';"' . str_replace('"', '""', $i) . '";' . $num_answer[(string)$i] . "\r\n";
            else
                $content .= ';"' . str_replace('"', '""', $i) . '";0' . "\r\n";
        }

        return $content;
    }

    /**
     * display the question with the result of a user
     *
     * @param    int $id_track the test relative to this question
     * @param    int $num_quest the quest sequence number
     * @param    int $num_quest the quest sequence number
     *
     * @return array    return an array with xhtml code in this way
     *                    string    'quest'    => the quest,
     *                    double    'score'        => score obtained from this question,
     *                    string    'comment'    => relative comment to the quest
     *                    bool    'manual_assigned'    => if the score is alredy assigned manually, this is true
     *
     * @access public
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    function displayUserResult($id_track, $num_quest, $show_solution)
    {
        require_once($GLOBALS['where_lms'] . '/class.module/track.test.php');
        require_once($GLOBALS['where_lms'] . '/class.module/track.testAnswer.php');

        $lang =& DoceboLanguage::createInstance('test');


        $quest = '';
        $comment = '';
        $com_is_correct = '';


        // extraction of the question
        $title_quest = $this->getTitle();


        $trackTest = new Track_Test($id_track);
        $testAnswers = $trackTest->getAnswers();

        $quest =
            '<div class="play_question">'
            . '<div class="title_question">' . $num_quest . ') ' . $title_quest . '</div>'
            . '<div class="answer_question">';
        foreach ($testAnswers as $testAnswer) {
            if ($testAnswer->getQuestId() == $this->getId()) {
                $quest .= '<img src="' . getPathImage() . 'standard/dot_sel.png" title="' . $lang->def('_TEST_ANSWER_CHECK') . '" '
                    . 'alt="' . $lang->def('_TEST_ANSWER_CHECK') . '" />&nbsp;'
                    . $testAnswer->getMoreInfo() . '&nbsp;';
            }
        }
        $quest .= '</div>'
            . '</div>';

        return array('quest' => $quest,
            'score' => false,
            'comment' => ($com_is_correct != '' ? $com_is_correct . '<br />' : '') . $comment);
    }
}

?>