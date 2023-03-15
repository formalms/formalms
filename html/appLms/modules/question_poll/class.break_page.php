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

require_once _lms_ . '/modules/question_poll/class.question.php';

class BreakPage_QuestionPoll extends QuestionPoll
{
    public $id;

    /**
     * function BreakPage_QuestionPoll( $id ).
     *
     * @param int $id the id of the question
     *
     * @return nothing
     */
    public function BreakPage_QuestionPoll($id)
    {
        parent::QuestionPoll($id);
    }

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
    public function create($id_poll, $back_poll)
    {
        if (!sql_query('
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_pollquest 
		( id_poll, type_quest, title_quest, sequence, page ) VALUES 
		( 	'" . $id_poll . "', 
			'" . $this->getQuestionType() . "', 
			'<span class=\"text_bold\">" . Lang::t('_QUEST_BREAK_PAGE') . "</span>',
			'" . $this->_getNextSequence($id_poll) . "', 
			'" . $this->_getPageNumber($id_poll) . "' ) ")) {
            errorCommunication(def('_POLL_ERR_INS_QUEST')
                . getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), Lang::t('_BACK')));
        }
        Util::jump_to('' . $back_poll);
    }

    public function edit($back_poll)
    {
        Util::jump_to('' . $back_poll);
    }

    /**
     * this function create a copy of a question and return the corresponding id.
     *
     * @return int return the id of the new question if success else return false
     *
     * @author Fabio Pirovano (fabio@docebo.com)
     */
    public function copy($new_id_poll, $back_poll = null)
    {
        list($sel_cat, $quest, $sequence, $page) = sql_fetch_row(sql_query('
		SELECT id_category, title_quest, sequence, page 
		FROM ' . $GLOBALS['prefix_lms'] . "_pollquest 
		WHERE id_quest = '" . (int) $this->id . "'"));

        //insert question
        $ins_query = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_pollquest 
		( id_poll, id_category, type_quest, title_quest, sequence, page ) VALUES 
		( 	'" . (int) $new_id_poll . "', 
			'" . (int) $sel_cat . "', 
			'" . $this->getQuestionType() . "', 
			'" . sql_escape_string($quest) . "',
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
