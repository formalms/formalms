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

require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/question/class.question.php');

class BreakPage_Question extends Question
{
    /**
     * function getQuestionType().
     *
     * Return the type of the question
     *
     * @return string the type of the question
     */
    public function getQuestionType()
    {
        return 'break_page';
    }

    /**
     * function create().
     *
     * @param $back_url	the url where the function retutn at the end of the operation
     *
     * @return nothing
     */
    public function create($idTest, $back_test)
    {
        if (!sql_query('
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
		( idTest, type_quest, title_quest, sequence, page, difficult ) VALUES 
		( 	'" . $idTest . "', 
			'" . $this->getQuestionType() . "', 
			'<span class=\"text_bold\">" . Lang::t('_QUEST_BREAK_PAGE') . "</span>',
			'" . $this->_getNextSequence($idTest) . "', 
			'" . $this->_getPageNumber($idTest) . "',
			'0') ")) {
            errorCommunication(def('_OPERATION_FAILURE')
                . getBackUi(Util::str_replace_once('&', '&amp;', $back_test), Lang::t('_BACK')));
        }
        Util::jump_to('' . $back_test);
    }

    public function edit($back_test)
    {
        Util::jump_to('' . $back_test);
    }

    public function del()
    {
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
        list($sel_cat, $quest, $sel_diff, $time_ass, $sequence, $page) = sql_fetch_row(sql_query('
		SELECT idCategory, title_quest, difficult, time_assigned, sequence, page 
		FROM ' . $GLOBALS['prefix_lms'] . "_testquest 
		WHERE idQuest = '" . (int) $this->id . "'"));

        //insert question
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_testquest 
		( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
		( 	'" . (int) $new_id_test . "', 
			'" . (int) $sel_cat . "', 
			'" . $this->getQuestionType() . "', 
			'" . sql_escape_string($quest) . "',
			'" . (int) $sel_diff . "', 
			'" . $time_ass . "',
			'" . (int) $sequence . "',
			'" . (int) $page . "' ) ";
        if (!sql_query($ins_query)) {
            return false;
        }
        //find id of auto_increment colum
        list($new_id_quest) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if (!$new_id_quest) {
            return false;
        }

        return $new_id_quest;
    }
}
