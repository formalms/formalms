<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.question.php');

class Choice_Question extends Question
{
    /**
     * this function is useful for question recognize.
     *
     * @return string return the identifier of the quetsion
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function getQuestionType()
    {
        return 'choice';
    }

    /**
     * this function write a gui line for answer insertion.
     *
     * @param int $i indicate the line number
     *
     * @return nothing
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function _lineAnswer($i)
    {
        $lang = FormaLanguage::createInstance('test');

        $GLOBALS['page']->add('<tr class="line_answer">'
            . '<td rowspan="2" class=" valign_top align_center">'
            . '<label for="is_correct_' . $i . '">' . $lang->def('_TEST_CORRECT') . '</label><br /><br />'
            . '<input type="radio" id="is_correct_' . $i . '" name="is_correct" value="' . $i . '"'
            . ((isset($_REQUEST['is_correct']) && ($_REQUEST['is_correct'] == $i)) ? ' checked="checked"' : '')
            . ' />'
            . '</td>'
            . '<td rowspan="2" class="image">'
            //answer
            . '<label class="access-only" for="answer_' . $i . '">' . $lang->def('_TEST_TEXT_ANSWER') . '</label>'

            //.Form::getTextarea('', 'answer_'.$i, 'answer['.$i.']', ( isset($_REQUEST['answer'][$i]) ? stripslashes($_REQUEST['answer'][$i]) : ''),false,'','form_line_l','floating','textarea',true)

            . loadHtmlEditor('',
                            'answer_' . $i,
                            'answer[' . $i . ']',
                            (isset($_REQUEST['answer'][$i]) ? stripslashes($_REQUEST['answer'][$i]) : ''),
                            false,
                            '',
                            true)

            //.'<textarea class="test_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="25" rows="3">'

            //.( isset($_REQUEST['answer'][$i]) ? stripslashes($_REQUEST['answer'][$i]) : '')//$lang->def('_QUEST_ANSWER')
            //.'</textarea>'
            . '</td>'
            . '<td rowspan="2" class="image">'
            //comment
            . '<label class="access-only" for="comment_' . $i . '">' . $lang->def('_COMMENTS') . '</label>'
            . '<textarea class="test_comment" id="comment_' . $i . '" name="comment[' . $i . ']" rows="6">'
            . (isset($_REQUEST['comment'][$i]) ? stripslashes($_REQUEST['comment'][$i]) : '')
            . '</textarea>'
            . '</td>'
            . '<td class="test_ifcorrect">'
            . '<label for="score_correct_' . $i . '">' . $lang->def('_TEST_IFCORRECT') . '</label>'
            . '</td>'
            . '<td class="align_right">'
            //score correct
            . '<input type="text" class="test_point" id="score_correct_' . $i . '" name="score_correct[' . $i . ']" alt="' . $lang->def('_TEST_IFCORRECT') . '" size="5" value="'
            . (isset($_REQUEST['score_correct'][$i]) ? $_REQUEST['score_correct'][$i] : '0.0') . '" />'
            . '</td>'
            . '</tr>' . "\n"
            . '<tr class="line_answer">'
            . '<td class="test_ifcorrect">'
            . '<label for="score_incorrect_' . $i . '">' . $lang->def('_TEST_IFINCORRECT') . '</label>'
            . '</td>'
            . '<td class="align_right">'
            //score incorrect
            . '- <input type="text" class="test_point" id="score_incorrect_' . $i . '" name="score_incorrect[' . $i . ']" alt="' . $lang->def('_TEST_IFINCORRECT') . '" size="5" value="'
            . (isset($_REQUEST['score_incorrect'][$i]) ? $_REQUEST['score_incorrect'][$i] : '0.0') . '" />'
            . '</td>'
            . '</tr>' . "\n", 'content');
    }

    /**
     * this function write a gui line for answer insertion,projected for modify.
     *
     * @param int $i indicate the line number
     *
     * @return nothing
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function _lineModAnswer($i)
    {
        $lang = FormaLanguage::createInstance('test');

        $GLOBALS['page']->add('<tr class="line_answer">'
            . '<td rowspan="2" class=" valign_top align_center">'
            . '<label for="is_correct_' . $i . '">' . $lang->def('_TEST_CORRECT') . '</label><br /><br />', 'content');
        if (isset($_REQUEST['answer_id'][$i])) {
            $GLOBALS['page']->add('<input type="hidden" id="answer_id_' . $i . '" name="answer_id[' . $i . ']" value="' . $_REQUEST['answer_id'][$i] . '" />', 'content');
        }
        $GLOBALS['page']->add('<input type="radio" id="is_correct_' . $i . '" name="is_correct" value="' . $i . '"'
            . ((isset($_REQUEST['is_correct']) && ($_REQUEST['is_correct'] == $i)) ? ' checked="checked"' : '')
            . ' />'
            . '</td>'
            . '<td rowspan="2" class="image">'
            //answer
            . '<label class="access-only" for="answer_' . $i . '">' . $lang->def('_TEST_TEXT_ANSWER') . '</label>'

            //.Form::getTextarea('', 'answer_'.$i, 'answer['.$i.']', ( isset($_REQUEST['answer'][$i]) ? stripslashes($_REQUEST['answer'][$i]) : ''),false,'','form_line_l','floating','textarea',true)

            . loadHtmlEditor('',
                            'answer_' . $i,
                            'answer[' . $i . ']',
                            (isset($_REQUEST['answer'][$i]) ? stripslashes($_REQUEST['answer'][$i]) : ''),
                            false,
                            '',
                            true)

            //.'<textarea class="test_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="25" rows="3">'
            //.( isset($_REQUEST['answer'][$i]) ? stripslashes($_REQUEST['answer'][$i]) : '')
            //.'</textarea>'
            . '</td>'
            . '<td rowspan="2" class="image">'
            //comment
            . '<label class="access-only" for="comment_' . $i . '">' . $lang->def('_COMMENTS') . '</label>'
            . '<textarea class="test_comment" id="comment_' . $i . '" name="comment[' . $i . ']" rows="6">'
            . (isset($_REQUEST['comment'][$i]) ? stripslashes($_REQUEST['comment'][$i]) : '')
            . '</textarea>'
            . '</td>'
            . '<td class="test_ifcorrect">'
            . '<label for="score_correct_' . $i . '">' . $lang->def('_TEST_IFCORRECT') . '</label>'
            . '</td>'
            . '<td class="align_right">'
            //score correct
            . '<input type="text" class="test_point" id="score_correct_' . $i . '" name="score_correct[' . $i . ']" alt="' . $lang->def('_TEST_IFCORRECT') . '" size="5" value="'
            . (isset($_REQUEST['score_correct'][$i]) ? $_REQUEST['score_correct'][$i] : '0.0') . '" />'
            . '</td>'
            . '</tr>' . "\n"
            . '<tr class="line_answer">'
            . '<td class="test_ifcorrect">'
            . '<label for="score_incorrect_' . $i . '">' . $lang->def('_TEST_IFINCORRECT') . '</label>'
            . '</td>'
            . '<td class="align_right">'
            //score incorrect
            . '- <input type="text" class="test_point" id="score_incorrect_' . $i . '" name="score_incorrect[' . $i . ']" alt="' . $lang->def('_TEST_IFINCORRECT') . '" size="5" value="'
            . (isset($_REQUEST['score_incorrect'][$i]) ? $_REQUEST['score_incorrect'][$i] : '0.0') . '" />'
            . '</td>'
            . '</tr>' . "\n", 'content');
    }

    /**
     * this function create a new question.
     *
     * @param int    $idTest    indicates the test selected
     * @param string $back_test indicates the return url
     *
     * @return nothing
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function create($idTest, $back_test)
    {
        $lang = FormaLanguage::createInstance('test');

        require_once _base_ . '/lib/lib.form.php';
        $url_encode = htmlentities(urlencode($back_test));

        //manage number of answer
        $num_answer = importVar('num_answer', true, 2);
        if (isset($_REQUEST['more_answer'])) {
            ++$num_answer;
        }
        if (isset($_REQUEST['less_answer']) && ($num_answer > 1)) {
            --$num_answer;
        }

        if (isset($_REQUEST['add_question'])) {
            //insert the new question
            $ins_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
			( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle ) VALUES
			( 	'" . $idTest . "', 
				'" . (int) $_REQUEST['idCategory'] . "', 
				'" . $this->getQuestionType() . "', 
				'" . addslashes($_REQUEST['title_quest']) . "',
				'" . (int) $_REQUEST['difficult'] . "', 
				'" . (int) $_REQUEST['time_assigned'] . "', 
				'" . (int) $_REQUEST['sequence'] . "', 
				'" . $this->_getPageNumber($idTest) . "',
				'" . (isset($_REQUEST['shuffle']) ? 1 : 0) . "' ) "; //'".(int)$this->_getNextSequence($idTest)."',
            if (!sql_query($ins_query)) {
                errorCommunication($lang->def('_OPERATION_FAILURE')
                        . getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
                        . $this->getQuestionType() . '&amp;idTest=' . $idTest . '&amp;back_test=' . $url_encode, $lang->def('_BACK')));
            }
            //find id of auto_increment colum
            list($idQuest) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

            // Salvataggio CustomField
            require_once _adm_ . '/lib/lib.customfield.php';
            $extra_field = new CustomFieldList();
            $extra_field->setFieldArea('LO_TEST');
            $extra_field->storeFieldsForObj($idQuest);

            if (!$idQuest) {
                errorCommunication($lang->def('_OPERATION_FAILURE') . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
            }
            //insert answer
            if (!isset($_REQUEST['is_correct'])) {
                $_REQUEST['is_correct'] = -1;
            }
            for ($i = 0; $i < $num_answer; ++$i) {
                //insert answer
                $ins_answer_query = '
				INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
				( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
				( 	'" . $idQuest . "', 
					'" . ($_REQUEST['is_correct'] == $i ? 1 : 0) . "', 
					'" . addslashes($_REQUEST['answer'][$i]) . "', 
					'" . addslashes($_REQUEST['comment'][$i]) . "', 
					'" . $this->_checkScore($_REQUEST['score_correct'][$i]) . "', 
					'" . $this->_checkScore($_REQUEST['score_incorrect'][$i]) . "') ";
                if (!sql_query($ins_answer_query)) {
                    errorCommunication($lang->def('_OPERATION_FAILURE') . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
                }
            }
            //back to question list
            Util::jump_to('' . $back_test);
        }

        //insert form
        require_once _lms_ . '/lib/lib.questcategory.php';
        $categories = Questcategory::getCategory();
        //writing difficult array
        $arr_dufficult = [5 => '5 - ' . $lang->def('_VERY_HARD'), 4 => '4 - ' . $lang->def('_HARD'), 3 => '3 - ' . $lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - ' . $lang->def('_DIFFICULT_EASY'), 1 => '1 - ' . $lang->def('_DIFFICULT_VERYEASY')];

        $GLOBALS['page']->add(
            getTitleArea($lang->def('_TEST_SECTION'), 'test')
            . '<div class="std_block">'
            . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))

            . '<div class="title_big">'
            . $lang->def('_QUEST_ACRN_' . strtoupper($this->getQuestionType())) . ' - '
            . $lang->def('_QUEST_' . strtoupper($this->getQuestionType()))
            . '</div><br />'
            . Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=create')

            . Form::openElementSpace()
            . Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
            . Form::getHidden('idTest', 'idTest', $idTest)
            . Form::getHidden('back_test', 'back_test', $url_encode)
            . Form::getHidden('num_answer', 'num_answer', $num_answer)

            . Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest',
                (isset($_REQUEST['title_quest']) ? stripslashes($_REQUEST['title_quest']) : '')), 'content');

        // Visualizzazione CustomFields
        require_once _adm_ . '/lib/lib.customfield.php';
        $fman = new CustomFieldList();
        $fman->setFieldArea('LO_TEST');
        $fields_mask = $fman->playFields($this->id);
        $GLOBALS['page']->add($fields_mask, 'content');

        if (count($categories) > 1) {
            $GLOBALS['page']->add(Form::getDropdown($lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories,
                (isset($_REQUEST['idCategory']) ? $_REQUEST['idCategory'] : '')), 'content');
        }
        $GLOBALS['page']->add(Form::getDropdown($lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult,
                (isset($_REQUEST['difficult']) ? $_REQUEST['difficult'] : 3))
            . Form::getCheckbox($lang->def('_TEST_QUEST_SHUFFLE'), 'shuffle', 'shuffle', '1', (isset($_REQUEST['shuffle']) ? 1 : 0))
            . Form::getTextfield($lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5,
                (isset($_REQUEST['time_assigned']) ? $_REQUEST['time_assigned'] : '00000'), $lang->def('_TEST_QUEST_TIMEASS'),
            $lang->def('_SECONDS'))
            . Form::getTextfield($lang->def('_ORDER', 'manmenu'), 'sequence', 'sequence', 5,
                (isset($_REQUEST['sequence']) ? $_REQUEST['sequence'] : (int) $this->_getNextSequence($idTest)))
            . '<div class="nofloat"></div><br />', 'content');

        $GLOBALS['page']->add('<table class="test_answer" cellspacing="0" summary="' . $lang->def('_TEST_ANSWER') . '">' . "\n"
            . '<caption>' . $lang->def('_TEST_ANSWER') . '</caption>' . "\n"
            . '<tr>'
            . '<th class="image">' . $lang->def('_TEST_CORRECT') . '</th>'
            . '<th>' . $lang->def('_TEST_TEXT_ANSWER') . '</th>'
            . '<th>' . $lang->def('_COMMENTS') . '</th>'
            . '<th colspan="2">' . $lang->def('_SCORE') . '</th>'
            . '</tr>' . "\n", 'content');
        for ($i = 0; $i < $num_answer; ++$i) {
            $this->_lineAnswer($i);
        }
        $GLOBALS['page']->add('</table>'
            . Form::getButton('more_answer', 'more_answer', $lang->def('_TEST_ADD_ONE_ANSWER'), 'button_nowh'), 'content');
        if ($num_answer > 1) {
            $GLOBALS['page']->add(Form::getButton('less_answer', 'less_answer', $lang->def('_TEST_SUB_ONE_ANSWER'), 'button_nowh'), 'content');
        }
        $GLOBALS['page']->add(
            '' // Form::getButton( 'select_from_libraries', 'select_from_libraries', $lang->def('_TEST_SEL_LIBRARIES'), 'button_nowh' )
            . Form::closeElementSpace()

            . Form::openButtonSpace()
            . Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>', 'content');
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
        $lang = FormaLanguage::createInstance('test');

        require_once _base_ . '/lib/lib.form.php';
        $url_encode = htmlentities(urlencode($back_test));

        //manage number of answer
        $num_answer = importVar('num_answer', true, 2);
        if (isset($_REQUEST['more_answer'])) {
            ++$num_answer;
        }
        if (isset($_REQUEST['less_answer']) && ($num_answer > 1)) {
            --$num_answer;
        }

        if (isset($_REQUEST['add_question'])) {
            //update question
            $ins_query = '
			UPDATE ' . $GLOBALS['prefix_lms'] . "_testquest
			SET idCategory = '" . (int) $_REQUEST['idCategory'] . "', 
				type_quest = '" . $this->getQuestionType() . "', 
				title_quest = '" . addslashes($_REQUEST['title_quest']) . "', 
				difficult = '" . (int) $_REQUEST['difficult'] . "', 
				time_assigned = '" . (int) $_REQUEST['time_assigned'] . "',
				sequence = '" . (int) $_REQUEST['sequence'] . "',
				shuffle = '" . (isset($_REQUEST['shuffle']) ? 1 : 0) . "'
			WHERE idQuest = '" . (int) $this->id . "'";
            if (!sql_query($ins_query)) {
                errorCommunication($lang->def('_OPERATION_FAILURE')
                    . getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
                    . $this->getQuestionType() . '&amp;idQuest=' . $this->id . '&amp;back_test=' . $url_encode, $lang->def('_BACK')));
            }

            // Salvataggio CustomField
            require_once _adm_ . '/lib/lib.customfield.php';
            $extra_field = new CustomFieldList();
            $extra_field->setFieldArea('LO_TEST');
            $extra_field->storeFieldsForObj($this->id);

            //update answer
            if (!isset($_REQUEST['is_correct'])) {
                $_REQUEST['is_correct'] = -1;
            }

            //find saved answer
            $re_answer = sql_query('
			SELECT idAnswer
			FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
			WHERE idQuest = '" . (int) $this->id . "'");
            while (list($id_a) = sql_fetch_row($re_answer)) {
                $existent_answer[$id_a] = 1;
            }

            for ($i = 0; $i < $num_answer; ++$i) {
                //scannig answer
                if (isset($_REQUEST['answer_id'][$i])) {
                    //must update
                    $idAnswer = $_REQUEST['answer_id'][$i];
                    if (isset($existent_answer[$idAnswer])) {
                        unset($existent_answer[$idAnswer]);
                    }

                    $upd_ans_query = '
					UPDATE ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
					SET is_correct = '" . ($_REQUEST['is_correct'] == $i ? 1 : 0) . "',
						answer = '" . addslashes($_REQUEST['answer'][$i]) . "',
						comment = '" . addslashes($_REQUEST['comment'][$i]) . "',
						score_correct = '" . $this->_checkScore($_REQUEST['score_correct'][$i]) . "', 
						score_incorrect = '" . $this->_checkScore($_REQUEST['score_incorrect'][$i]) . "'
					WHERE idAnswer = '" . (int) $idAnswer . "'";
                    if (!sql_query($upd_ans_query)) {
                        errorCommunication($lang->def('_OPERATION_FAILURE') . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
                    }
                } else {
                    //insert new answer
                    $ins_answer_query = '
					INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
					( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
					( 	'" . $this->id . "', 
						'" . ($_REQUEST['is_correct'] == $i ? 1 : 0) . "', 
						'" . addslashes($_REQUEST['answer'][$i]) . "', 
						'" . addslashes($_REQUEST['comment'][$i]) . "', 
						'" . $this->_checkScore($_REQUEST['score_correct'][$i]) . "', 
						'" . $this->_checkScore($_REQUEST['score_incorrect'][$i]) . "') ";
                    if (!sql_query($ins_answer_query)) {
                        errorCommunication($lang->def('_OPERATION_FAILURE') . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
                    }
                }
            }
            foreach ($existent_answer as $idA => $v) {
                //i must delete these answer
                $del_answer_query = '
				DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer
				WHERE idQuest = '" . (int) $this->id . "' AND idAnswer = '" . (int) $idA . "'";
                if (!sql_query($del_answer_query)) {
                    errorCommunication($lang->def('_OPERATION_FAILURE') . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
                }
            }
            //back to question list
            Util::jump_to('' . $back_test);
        }

        //insert form
        require_once _lms_ . '/lib/lib.questcategory.php';
        $categories = Questcategory::getCategory();
        //writing difficult array
        $arr_dufficult = [5 => '5 - ' . $lang->def('_VERY_HARD'), 4 => '4 - ' . $lang->def('_HARD'), 3 => '3 - ' . $lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - ' . $lang->def('_DIFFICULT_EASY'), 1 => '1 - ' . $lang->def('_DIFFICULT_VERYEASY')];

        //load data
        if (!isset($_REQUEST['answer_id'])) {
            list($sel_cat, $quest, $sel_diff, $sel_time, $sel_sequence, $shuffle) = sql_fetch_row(sql_query('
			SELECT idCategory, title_quest, difficult, time_assigned, sequence, shuffle 
			FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
			WHERE idQuest = '" . (int) $this->id . "'"));

            $re_answer = sql_query('
			SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
			FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
			WHERE idQuest = '" . (int) $this->id . "'
			ORDER BY idAnswer");

            $i_load = 0;
            while (list(
                $_REQUEST['answer_id'][$i_load],
                $is_correct,
                $_REQUEST['answer'][$i_load],
                $_REQUEST['comment'][$i_load],
                $_REQUEST['score_correct'][$i_load],
                $_REQUEST['score_incorrect'][$i_load]) = sql_fetch_row($re_answer)) {
                if ($is_correct) {
                    $_REQUEST['is_correct'] = $i_load;
                }
                ++$i_load;
            }
            $num_answer = $i_load;
        }

        $GLOBALS['page']->add(
            getTitleArea('test', $lang->def('_TEST_SECTION'))
            . '<div class="std_block">'
            . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))
            . '<div class="title_big">'
            . $lang->def('_QUEST_ACRN_' . strtoupper($this->getQuestionType())) . ' - '
            . $lang->def('_QUEST_' . strtoupper($this->getQuestionType()))
            . '</div><br />'
            . Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=edit')

            . Form::openElementSpace()
            . Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
            . Form::getHidden('idQuest', 'idQuest', $this->id)
            . Form::getHidden('back_test', 'back_test', $url_encode)
            . Form::getHidden('num_answer', 'num_answer', $num_answer)

            . Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest',
                (isset($_REQUEST['title_quest']) ? stripslashes($_REQUEST['title_quest']) : $quest)), 'content');

        // Visualizzazione CustomFields
        require_once _adm_ . '/lib/lib.customfield.php';
        $fman = new CustomFieldList();
        $fman->setFieldArea('LO_TEST');
        $fields_mask = $fman->playFields($this->id);
        $GLOBALS['page']->add($fields_mask, 'content');

        if (count($categories) > 1) {
            $GLOBALS['page']->add(Form::getDropdown($lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories,
                (isset($_REQUEST['idCategory']) ? $_REQUEST['idCategory'] : $sel_cat)), 'content');
        }
        $GLOBALS['page']->add(Form::getDropdown($lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult,
                (isset($_REQUEST['difficult']) ? $_REQUEST['difficult'] : $sel_diff))
            . Form::getCheckbox($lang->def('_TEST_QUEST_SHUFFLE'), 'shuffle', 'shuffle', '1', $shuffle)
            . Form::getTextfield($lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5,
                (isset($_REQUEST['time_assigned']) ? $_REQUEST['time_assigned'] : $sel_time), $lang->def('_TEST_QUEST_TIMEASS'),
                $lang->def('_SECONDS'))
            . Form::getTextfield($lang->def('_ORDER', 'manmenu'), 'sequence', 'sequence', 5,
                (isset($_REQUEST['sequence']) ? $_REQUEST['sequence'] : $sel_sequence))
            . '<div class="nofloat"></div><br />', 'content');

        $GLOBALS['page']->add('<table class="test_answer" cellspacing="0" summary="' . $lang->def('_TEST_ANSWER') . '">' . "\n"
            . '<caption>' . $lang->def('_TEST_ANSWER') . '</caption>' . "\n"
            . '<tr>'
            . '<th class="image">' . $lang->def('_TEST_CORRECT') . '</th>'
            . '<th>' . $lang->def('_TEST_TEXT_ANSWER') . '</th>'
            . '<th>' . $lang->def('_COMMENTS') . '</th>'
            . '<th colspan="2">' . $lang->def('_SCORE') . '</th>'
            . '</tr>' . "\n", 'content');
        for ($i = 0; $i < $num_answer; ++$i) {
            $this->_lineModAnswer($i);
        }
        $GLOBALS['page']->add(
            '</table>'
            . Form::getButton('more_answer', 'more_answer', $lang->def('_TEST_ADD_ONE_ANSWER'), 'button_nowh'), 'content');
        if ($num_answer > 1) {
            $GLOBALS['page']->add(Form::getButton('less_answer', 'less_answer', $lang->def('_TEST_SUB_ONE_ANSWER'), 'button_nowh'), 'content');
        }
        $GLOBALS['page']->add(
            '' // Form::getButton( 'select_from_libraries', 'select_from_libraries', $lang->def('_TEST_SEL_LIBRARIES'), 'button_nowh' )
            . Form::closeElementSpace()

            . Form::openButtonSpace()
            . Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>', 'content');
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
        //delete answer
        if (!sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
		WHERE idQuest = '" . $this->id . "'")) {
            return false;
        }

        //remove answer
        if (!sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . $this->id . "'")) {
            return false;
        }
        //remove customfield
        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_fw'] . "_customfield_entry WHERE id_field IN (SELECT id_field FROM core_customfield WHERE area_code = 'LO_TEST') AND id_obj = '" . $this->id . "'")) {
            return false;
        }
        //remove question
        return sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . $this->id . "'");
    }

    /**
     * this function create a copy of a question and return the corresponding id.
     *
     * @return int return the id of the new question if success else return false
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function copy($new_id_test, $back_test = null)
    {
        //retriving question
        list($sel_cat, $quest, $sel_diff, $time_ass, $sequence, $page, $shuffle) = sql_fetch_row(sql_query('
		SELECT idCategory, title_quest, difficult, time_assigned, sequence, page, shuffle 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . (int) $this->id . "'"));

        //insert question
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
		( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle ) VALUES 
		( 	'" . (int) $new_id_test . "', 
			'" . (int) $sel_cat . "', 
			'" . $this->getQuestionType() . "', 
			'" . sql_escape_string($quest) . "',
			'" . (int) $sel_diff . "', 
			'" . $time_ass . "',
			'" . (int) $sequence . "',
			'" . (int) $page . "', 
			'" . (int) $shuffle . "' ) ";
        if (!sql_query($ins_query)) {
            return false;
        }
        //find id of auto_increment colum
        list($new_id_quest) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if (!$new_id_quest) {
            return false;
        }

        //insert customfields
        $re_customfields = sql_query('
		SELECT ce.id_field, ce.id_obj, ce.obj_entry  
		FROM ' . $GLOBALS['prefix_fw'] . '_customfield_entry AS ce
		INNER JOIN ' . $GLOBALS['prefix_fw'] . '_customfield c ON c.id_field = ce.id_field
		WHERE ce.id_obj = ' . (int) $this->id . " AND c.area_code IN ('LO_OBJECT', 'LO_TEST')
		ORDER BY ce.id_field");
        while (list($id_field, $id_obj, $obj_entry) = sql_fetch_row($re_customfields)) {
            //insert customfields
            $ins_customfields_query = '
			INSERT INTO ' . $GLOBALS['prefix_fw'] . "_customfield_entry
			( id_field, id_obj, obj_entry   ) VALUES
			( 	'" . (int) $id_field . "', 
				'" . (int) $new_id_quest . "', 
				'" . (int) $obj_entry . "' ) ";
            if (!sql_query($ins_customfields_query)) {
                return false;
            }
        }

        //retriving new answer
        $re_answer = sql_query('
		SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int) $this->id . "'
		ORDER BY idAnswer");
        while (list($idAnswer, $is_correct, $answer, $comment, $score_c, $score_inc) = sql_fetch_row($re_answer)) {
            //insert answer
            $ins_answer_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
			( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
			( 	'" . (int) $new_id_quest . "', 
				'" . (int) $is_correct . "', 
				'" . sql_escape_string($answer) . "', 
				'" . sql_escape_string($comment) . "',
				'" . $this->_checkScore($score_c) . "', 
				'" . $this->_checkScore($score_inc) . "') ";
            if (!sql_query($ins_answer_query)) {
                return false;
            }
        }

        return $new_id_quest;
    }

    /**
     * display the quest for play, if.
     *
     * @param int  $num_quest      the number of the quest to display in front of the quest title
     * @param bool $shuffle_answer randomize the answer display order
     * @param int  $id_track       where find the answer, if find -> load
     * @param bool $freeze         if true, when load disable the user interaction
     *
     * @return string of html question code
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function play($num_quest, $shuffle_answer = false, $id_track = 0, $freeze = false, $number_time = null)
    {
        $lang = FormaLanguage::createInstance('test');

        list($id_quest, $title_quest, $shuffle) = sql_fetch_row(sql_query('
		SELECT idQuest, title_quest, shuffle 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . $this->id . "'"));

        $query_answer = '
		SELECT idAnswer, answer 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int) $this->id . "'";
        if ($shuffle_answer || $shuffle) {
            $query_answer .= ' ORDER BY RAND()';
        } else {
            $query_answer .= ' ORDER BY idAnswer';
        }
        $re_answer = sql_query($query_answer);

        $find_prev = false;
        $id_answer_do = 0;
        if ($id_track != 0) {
            //recover previous information
            $recover_answer = '
			SELECT idAnswer 
			FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
			WHERE idQuest = '" . (int) $this->id . "' AND 
				idTrack = '" . (int) $id_track . "' AND ( user_answer = 1 OR user_answer = NULL ) AND number_time =  " . $number_time;
            $re_answer_do = sql_query($recover_answer);
            if (sql_num_rows($re_answer_do)) {
                //find previous answer
                $find_prev = true;
                list($id_answer_do) = sql_fetch_row($re_answer_do);
            }
        }

        $content = '</br></br>';
        // Visualizzazione CustomField
        require_once _adm_ . '/lib/lib.customfield.php';
        $fman = new CustomFieldList();
        $fman->setFieldArea('LO_TEST');
        $fields_mask = $fman->playFields($this->id, false, true);

        $content .= $fields_mask;

        $content .=
            '<div class="play_question">'
            . '<div>' . $lang->def('_QUEST_' . strtoupper($this->getQuestionType())) . '</div>'
            . '<div class="title_question">' . $num_quest . ') ' . $title_quest . '</div>'
            . '<div class="answer_question">';
        while (list($id_answer, $answer) = sql_fetch_row($re_answer)) {
            $content .= '<div class="answer-item"><div class="input-wrapper input-wrapper-radio">'
                . '<input type="radio" id="quest_' . $id_quest . '_' . $id_answer . '" '
                . 'name="quest[' . $id_quest . ']" value="' . $id_answer . '"'
                . (($find_prev && $id_answer == $id_answer_do) ? ' checked="checked"' : '')
                . ($find_prev && $freeze ? ' disabled="disabled"' : '') . ' />'
                . '</div> '
                . '<label class="text_answer" for="quest_' . $id_quest . '_' . $id_answer . '">' . $answer . '</label><br /></div>';
        }
        if (FormaLms\lib\Get::sett('no_answer_in_test') == 'on') {
            $content .= '<input type="radio" id="quest_' . $id_quest . '_0" '
                    . 'name="quest[' . $id_quest . ']" value="0" '
                    . ($find_prev ? ($id_answer == $id_answer_do ? ' checked="checked"' : '') : ' checked="checked"')
                    . ($find_prev && $freeze ? ' disabled="disabled"' : '') . ' /> '
                    . '<label class="text_answer_none" for="quest_' . $id_quest . '_0">' . $lang->def('_NO_ANSWER') . '</label>';
        }
        $content .= '</div>'
            . '</div>';

        return $content;
    }

    /**
     * save the answer to the question in an proper format.
     *
     * @param int   $id_track      the relative id_track
     * @param array $source        source of the answer send by the user
     * @param bool  $can_overwrite if the answer for this question exists and this is true, the old answer
     *                             is updated, else the old answer will be leaved
     *
     * @return bool true if success false otherwise
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function storeAnswer(Track_Test $trackTest, &$source, $can_overwrite = false)
    {
        $result = true;

        if ($this->userDoAnswer($trackTest->idTrack) && !$trackTest->getTestObj()->isRetainAnswersHistory()) {
            if (!$can_overwrite) {
                return true;
            }
            if (!$this->deleteAnswer($trackTest->idTrack)) {
                return false;
            }
        } elseif ($trackTest->getTestObj()->isRetainAnswersHistory() && $this->testQuestAnswerExists($trackTest)) {
            $this->deleteAnswer($trackTest->idTrack, ($trackTest->getNumberOfAttempt() + 1));
        }

        $re_answer = sql_query('
		SELECT idAnswer, is_correct, score_correct, score_incorrect 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int) $this->id . "'");
        while (list($id_answer, $is_correct, $score_corr, $score_incorr) = sql_fetch_row($re_answer)) {
            if (isset($source['quest'][$this->id]) && ($source['quest'][$this->id] == $id_answer)) {
                //answer checked by the user
                $track_query = '
				INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
				VALUES (
					'" . (int) $trackTest->idTrack . "',
					'" . (int) $this->id . "', 
					'" . (int) $id_answer . "', 
					'" . ($is_correct ? $score_corr : -$score_incorr) . "', 
					'',
					1,
					'" . (int) ($trackTest->getNumberOfAttempt() + 1) . "')";
                $result &= sql_query($track_query);
            } elseif ($is_correct && ($score_incorr != 0)) {
                //answer correct with penality but not checked by the user
                $track_query = '
				INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
				VALUES (
					'" . (int) $trackTest->idTrack . "',
					'" . (int) $this->id . "', 
					'" . (int) $id_answer . "', 
					'" . -$score_incorr . "', 
					'',
					0,
					'" . (int) ($trackTest->getNumberOfAttempt() + 1) . "')";
                $result &= sql_query($track_query);
            } elseif (!$is_correct && ($score_corr != 0)) {
                //answer correct with penality but not checked by the user
                $track_query = '
				INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
				VALUES (
					'" . (int) $trackTest->idTrack . "',
					'" . (int) $this->id . "', 
					'" . (int) $id_answer . "', 
					'" . $score_corr . "', 
					'',
					0,
					'" . (int) ($trackTest->getNumberOfAttempt() + 1) . "')";
                $result &= sql_query($track_query);
            }
        }

        return $result;
    }

    /**
     * save the answer to the question in an proper format overwriting the old entry.
     *
     * @param int   $id_track the relative id_track
     * @param array $source   source of the answer send by the user
     *
     * @return bool true if success false otherwise
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function updateAnswer($id_track, &$source, $numberTime = null)
    {
        if (!$this->deleteAnswer($id_track)) {
            return false;
        } else {
            return $this->storeAnswer($id_track, $source, false);
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
        return 'auto';
    }

    /**
     * display the question with the result of a user.
     *
     * @param int $id_track    the test relative to this question
     * @param int $num_quest   the quest sequqnce number
     * @param int $number_time the quest attempt number
     *
     * @return array return an array with xhtml code in this way
     *               string	'quest' 	=> the quest,
     *               double	'score'		=> score obtained from this question,
     *               string	'comment'	=> relative comment to the quest )
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function displayUserResult($id_track, $num_quest, $show_solution, $number_time = null)
    {
        $lang = FormaLanguage::createInstance('test');

        $quest = '';
        $comment = '';
        $com_is_correct = '';

        // extraction of the question
        list($title_quest) = sql_fetch_row(sql_query('
		SELECT title_quest 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . $this->id . "'"));

        // selection of the right answers to the question
        $query_answer = '
		SELECT idAnswer, is_correct, answer, comment 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
		WHERE idQuest = '" . (int) $this->id . "'
		ORDER BY idAnswer";
        $re_answer = sql_query($query_answer);

        // the user_answer = NULL is for backward compatibility
        $recover_answer = '
		SELECT idAnswer 
		FROM ' . $GLOBALS['prefix_lms'] . "_testtrack_answer 
		WHERE idQuest = '" . (int) $this->id . "' AND 
			idTrack = '" . (int) $id_track . "' AND ( user_answer = 1 OR user_answer IS NULL )";
        if ($number_time != null) {
            $recover_answer .= ' AND number_time = ' . $number_time;
        } else {
            $recover_answer .= ' ORDER BY number_time DESC LIMIT 1';
        }

        list($id_answer_do) = sql_fetch_row(sql_query($recover_answer));

        //**  recorver status test ** #11961 - Errata visualizzazione risposte corrette nei test
        $sql = 'select status from %lms_commontrack where idUser=' . \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . ' and idTrack=' . $id_track;
        list($status_test) = sql_fetch_row(sql_query($sql));

        $quest = '';
        // Visualizzazione CustomField
        require_once _adm_ . '/lib/lib.customfield.php';
        $fman = new CustomFieldList();
        $fman->setFieldArea('LO_TEST');
        $fields_mask = $fman->playFields((int) $this->id, false, true);

        $quest .= $fields_mask;

        $quest .=
            '<div class="play_question">'
            . '<div class="title_question">' . $num_quest . ') ' . $title_quest . '</div>'
            . '<div class="answer_question">';
        while (list($id_answer, $is_correct, $answer, $comm) = sql_fetch_row($re_answer)) {
            if ($id_answer == $id_answer_do) {
                $quest .= '<img src="' . getPathImage() . 'standard/dot_sel.png" title="' . $lang->def('_TEST_ANSWER_CHECK') . '" '
                        . 'alt="' . $lang->def('_TEST_ANSWER_CHECK') . '" />&nbsp;'
                    . $answer . '&nbsp;';
                if ($is_correct) {
                    $quest .= '<span class="test_answer_correct">' . $lang->def('_TEST_CORRECT') . '</span>';
                    $comment = $comm;
                } else {
                    $quest .= '<span class="test_answer_incorrect">' . $lang->def('_TEST_INCORRECT') . '</span>';
                    $comment = $comm;
                }
                $quest .= '<br />';
            } else {
                /* if($is_correct && $show_solution) {	*/

                if (($status_test == 'passed' && $show_solution == 2 && $is_correct) || ($show_solution == 1 && $is_correct)) {
                    $com_is_correct .= '<span class="text_bold">' . $lang->def('_TEST_NOT_THECORRECT') . ' : </span>' . $answer . '<br />';
                }

                $quest .= '<img src="' . getPathImage() . 'standard/dot_uns.png" title="' . $lang->def('_TEST_ANSWER_NOTCHECK') . '" '
                    . 'alt="' . $lang->def('_TEST_ANSWER_NOTCHECK') . '" />&nbsp;'
                    . $answer . '<br />';
            }
        }
        $quest .= '</div>'
            . '</div>';

        return ['quest' => $quest,
                        'score' => $this->userScore($id_track, $number_time),
                        'comment' => ($com_is_correct != '' ? $com_is_correct . '<br />' : '') . $comment, ];
    }

    public function importFromRaw($raw_quest, $id_test = false)
    {
        if ($id_test === false) {
            $id_test = 0;
        }

        //insert question
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
		( idQuest, idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
		( 	NULL,
			'" . (int) $id_test . "', 
			'" . (int) $raw_quest->id_category . "', 
			'" . $this->getQuestionType() . "', 
			'" . $raw_quest->quest_text . "',
			'" . (int) $raw_quest->difficult . "', 
			'" . $raw_quest->time_assigned . "',
			'1',
			'1' ) ";
        if (!sql_query($ins_query)) {
            return false;
        }

        //find id of auto_increment colum
        list($new_id_quest) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if (!$new_id_quest) {
            return false;
        }

        //customfield
        if (is_array($raw_quest->customfield)) {
            foreach ($raw_quest->customfield as $field) {
                //insert customfield
                $ins_cf_query = '
			INSERT INTO ' . $GLOBALS['prefix_fw'] . "_customfield_entry 
			( id_field, id_obj, obj_entry ) VALUES
			( 	'" . (int) $field['idField'] . "', 
				" . (int) $new_id_quest . ", 
				'" . (int) $field['idSon'] . "' ) ";
                if (!sql_query($ins_cf_query)) {
                    return false;
                }
            }
        }

        if (!is_array($raw_quest->answers)) {
            return $new_id_quest;
        }

        reset($raw_quest->answers);
        foreach ($raw_quest->answers as $raw_answer) {
            if ($raw_answer->score_correct > 0 && $raw_answer->score_correct < 1 && $raw_answer->is_correct) {
                // a littel bit tricky but needed in order tu full support
                $raw_answer->score_penalty = (-1) * $raw_answer->score_correct;
                $raw_answer->score_correct = 0;
            }
            //insert answer
            $ins_answer_query = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquestanswer 
			( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
			( 	'" . (int) $new_id_quest . "', 
				'" . (int) $raw_answer->is_correct . "', 
				'" . $raw_answer->text . "', 
				'" . $raw_answer->comment . "',
				'" . $this->_checkScore($raw_answer->score_correct) . "', 
				'" . $this->_checkScore($raw_answer->score_penalty) . "') ";
            if (!sql_query($ins_answer_query)) {
                return false;
            }
        }

        return $new_id_quest;
    }
}
