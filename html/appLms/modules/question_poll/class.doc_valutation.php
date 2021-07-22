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

if(!defined('IN_FORMA')) die('You cannot access this file directly');

require_once(_lms_.'/modules/question_poll/class.question.php' );

class DocValutation_QuestionPoll extends QuestionPoll {

	/**
	 * class constructor
	 *
	 * @param int	the unique database identifer of a question
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function DocValutation_QuestionPoll( $id ) {
		parent::QuestionPoll($id);
	}

	/**
	 * this function is useful for question recognize
	 *
	 * @return string	return the identifier of the quetsion
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function getQuestionType() {
		return 'doc_valutation';
	}

	/**
	 * this function write a gui line for answer insertion
	 *
	 * @param  int	$i	indicate the line number
	 * @return nothing
	 *
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _lineAnswer($i) {
		$lang =& DoceboLanguage::createInstance('poll');

		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td class="image">'
			//answer
			.'<label class="access-only" for="answer_'.$i.'">'.$lang->def('_ANSWER').'</label>'
			.'<textarea class="poll_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="50" rows="3">'
			.( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : $lang->def('_QUEST_ANSWER'))
			.'</textarea>'
			.'</td>'
			.'</tr>'."\n", 'content');
	}

	/**
	 * this function write a gui line for answer insertion,projected for modify
	 *
	 * @param  int	$i	indicate the line number
	 * @return nothing
	 *
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _lineModAnswer($i) {
		$lang =& DoceboLanguage::createInstance('poll');

		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td class="image">', 'content');
		if(isset($_POST['answer_id'][$i])) {
			$GLOBALS['page']->add('<input type="hidden" id="answer_id_'.$i.'" name="answer_id['.$i.']" value="'.$_POST['answer_id'][$i].'" />', 'content');
		}
		$GLOBALS['page']->add(
			'<label class="access-only" for="answer_'.$i.'">'.$lang->def('_ANSWER').'</label>'
			.'<textarea class="poll_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="50" rows="3">'
			.( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : '')
			.'</textarea>'
			.'</td>'
			.'</tr>'."\n", 'content');
	}

	/**
	 * this function create a new question
	 *
	 * @param  int		$id_poll 	indicates the poll selected
	 * @param  string	$back_poll	indicates the return url
	 * @return nothing
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function create( $id_poll, $back_poll )
	{
		$lang =& DoceboLanguage::createInstance('poll');


		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_poll));

		if(isset($_POST['add_question']))
		{
			//insert the new question

			$min_value = Get::req('min_value', DOTY_INT, 0);
			$max_value = Get::req('max_value', DOTY_INT, 0);
			$step_value = (float)str_replace(',', '.', $_POST['step_value']);

			$i = $min_value;
			$seq = 0;

			if($min_value >= $max_value || $step_value == 0)
				$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');
			else
			{
				$ins_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_pollquest
				( id_poll, id_category, type_quest, title_quest, sequence, page ) VALUES
				( 	'".$id_poll."',
					'".(int)$_POST['id_category']."',
					'".$this->getQuestionType()."',
					'".$_POST['title_quest']."',
					'".(int)$this->_getNextSequence($id_poll)."',
					'".$this->_getPageNumber($id_poll)."' ) ";
				if(!sql_query($ins_query))
				{
					$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_QUEST')
						.getBackUi('index.php?modname=question_poll&amp;op=create&amp;type_quest='
						.$this->getQuestionType().'&amp;id_poll='.$id_poll.'&amp;back_poll='.$url_encode, $lang->def('_BACK'))), 'content');
				}
				//find id of auto_increment colum
				list($id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
				if(!$id_quest)
					$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');

				$query =	"INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer"
							." (id_quest, answer, sequence)"
							." VALUES ('".$id_quest."', '".$min_value."', '0'),"
							." ('".$id_quest."', '".$max_value."', '1'),"
							." ('".$id_quest."', '".$step_value."', '2');";

				if(!sql_query($query))
						$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');
				else
					Util::jump_to( ''.$back_poll);
			}
		}

		//insert form
			require_once(_lms_.'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//writing difficult array

		$GLOBALS['page']->add(
			getTitleArea($lang->def('_POLL_SECTION'), 'poll')
			.'<div class="std_block">'
			.getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK'))

			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_add_quest', 'index.php?modname=question_poll&amp;op=create')

			.Form::openElementSpace()
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('id_poll', 'id_poll', $id_poll)
			.Form::getHidden('back_poll', 'back_poll', $url_encode)

			.Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest',
				( isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : '' ) )
			.Form::getDropdown( $lang->def('_CATEGORY'), 'id_category', 'id_category', $categories,
				( isset($_POST['id_category']) ? $_POST['id_category'] : 0 ))
			.'<div class="no_float"></div><br />'
			.Form::getTextfield($lang->def('_MIN_VALUE'), 'min_value', 'min_value', 255)
			.Form::getTextfield($lang->def('_MAX_VALUE'), 'max_value', 'max_value', 255)
			.Form::getTextfield($lang->def('_STEP_VALUE'), 'step_value', 'step_value', 255, '1'), 'content');
		$GLOBALS['page']->add(
			Form::closeElementSpace()

			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_INSERT'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>', 'content');
	}

	/**
	 * this function modify a question
	 *
	 * @param  string	$back_poll	indicates the return url
	 * @return nothing
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function edit( $back_poll )
	{
		$lang =& DoceboLanguage::createInstance('poll');


		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_poll));

		//manage number of answer
		if(isset($_POST['save_question']))
		{
			//update question
			$ins_query = "
			UPDATE ".$GLOBALS['prefix_lms']."_pollquest
			SET id_category = '".(int)$_POST['id_category']."',
				type_quest = '".$this->getQuestionType()."',
				title_quest = '".$_POST['title_quest']."'
			WHERE id_quest = '".(int)$this->id."'";
			if(!sql_query($ins_query)) {

				getErrorUi($lang->def('_POLL_ERR_INS_QUEST')
					.getBackUi('index.php?modname=question_poll&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;id_quest='.$this->id.'&amp;back_poll='.$url_encode, $lang->def('_BACK')));
			}
			//update answer
			if( !isset($_POST['is_correct']) ) $_POST['is_correct'] = -1;

			$min_value = Get::req('min_value', DOTY_INT, 0);
			$max_value = Get::req('max_value', DOTY_INT, 0);
			$step_value = (float)str_replace(',', '.', $_POST['step_value']);

			if($min_value >= $max_value || $step_value == 0)
				$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');
			else
			{
				$del_answer_query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
									WHERE id_quest = '".(int)$this->id."'";
				if(!sql_query($del_answer_query))
					getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK')));
				else
				{
					$query =	"INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer"
								." (id_quest, answer, sequence)"
								." VALUES ('".$this->id."', '".$min_value."', '0'),"
								." ('".$this->id."', '".$max_value."', '1'),"
								." ('".$this->id."', '".$step_value."', '2');";

					if(!sql_query($query))
						getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK')));
					else
						Util::jump_to($back_poll);
				}
			}
		}

		//insert form
			require_once(_lms_.'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//writing difficult

		//load data
		list($sel_cat, $quest) = sql_fetch_row(sql_query("
		SELECT id_category, title_quest
		FROM ".$GLOBALS['prefix_lms']."_pollquest
		WHERE id_quest = '".(int)$this->id."'"));

		$re_answer = sql_query("
		SELECT id_answer, answer
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY sequence");

		$array_answer = array();

		while(list($id_answer, $answer) = sql_fetch_row($re_answer))
			$array_answer[] = $answer;
		if(!empty($array_answer))
		{
			$min_value = $array_answer[0];
			$max_value = $array_answer[1];
			$step_value = $array_answer[2];
		}
		else
		{
			$min_value = '';
			$max_value = '';
			$step_value = '1';
		}

		$GLOBALS['page']->add(
			getTitleArea($lang->def('_POLL_SECTION'), 'poll')
			.'<div class="std_block">'
			.getBackUi(str_replace('&', '&amp;', $back_poll), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_add_quest', 'index.php?modname=question_poll&amp;op=edit')

			.Form::openElementSpace()
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('id_quest', 'id_quest', $this->id)
			.Form::getHidden('back_poll', 'back_poll', $url_encode)

			.Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest',
				( isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : $quest ) )
			.Form::getDropdown( $lang->def('_CATEGORY'), 'id_category', 'id_category', $categories,
				( isset($_POST['id_category']) ? $_POST['id_category'] : $sel_cat ))
			.'<div class="no_float"></div><br />'
			.Form::getTextfield($lang->def('_MIN_VALUE'), 'min_value', 'min_value', 255, $min_value)
			.Form::getTextfield($lang->def('_MAX_VALUE'), 'max_value', 'max_value', 255, $max_value)
			.Form::getTextfield($lang->def('_STEP_VALUE'), 'step_value', 'step_value', 255, $step_value), 'content');

		$GLOBALS['page']->add(
			Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('save_question', 'save_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>', 'content');
	}

	/**
	 * this function create a copy of a question and return the corresponding id
	 *
	 * @return int 	return the id of the new question if success else return false
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function copy( $new_id_poll, $back_poll = NULL ) {

		//retriving question
		list($sel_cat, $quest, $sequence, $page) = sql_fetch_row(sql_query("
		SELECT id_category, title_quest, sequence, page
		FROM ".$GLOBALS['prefix_lms']."_pollquest
		WHERE id_quest = '".(int)$this->id."'"));

		//insert question
		$ins_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_pollquest
		( id_poll, id_category, type_quest, title_quest, sequence, page ) VALUES
		( 	'".(int)$new_id_poll."',
			'".(int)$sel_cat."',
			'".$this->getQuestionType()."',
			'".sql_escape_string($quest)."',
			'".(int)$sequence."',
			'".(int)$page."' ) ";
		if(!sql_query($ins_query)) return false;
		//find id of auto_increment colum
		list($new_id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if(!$new_id_quest) return false;

		//retriving new answer
		$re_answer = sql_query("
		SELECT answer, sequence
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY id_answer");
		while(list($answer, $sequence) = sql_fetch_row($re_answer)) {

			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer
			( id_quest, answer, sequence ) VALUES
			( 	'".$new_id_quest."',
				'".sql_escape_string($answer)."',
				'".$sequence."' ) ";
			if(!sql_query($ins_answer_query)) {

				return false;
			}
		}
		return $new_id_quest;
	}

	/**
	 * display the quest for play, if
	 *
	 * @param 	int		$num_quest 			the number of the quest to display in front of the quest title
	 * @param 	bool	$shuffle_answer 	randomize the answer display order
	 * @param 	int		$id_track 			where find the answer, if find -> load
	 * @param 	bool	$freeze 			if true, when load disable the user interaction
	 *
	 * @return string of html question code
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function play( $num_quest, $shuffle_answer = false, $id_track = 0, $freeze = false ) {
		$lang =& DoceboLanguage::createInstance('poll');

		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest
		FROM ".$GLOBALS['prefix_lms']."_pollquest
		WHERE id_quest = '".$this->id."'"));

		$query_answer = "
		SELECT answer
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
		WHERE id_quest = '".(int)$this->id."'"
		." ORDER BY sequence";
		$re_answer = sql_query($query_answer);

		$find_prev = false;
		$id_answer_do = 0;
		if($id_track != 0)
		{
			//recover previous information
			$recover_answer = "
			SELECT more_info
			FROM ".$GLOBALS['prefix_lms']."_polltrack_answer
			WHERE id_quest = '".(int)$this->id."' AND
				id_track = '".(int)$id_track."'";
			$re_answer_do = sql_query($recover_answer);
			if(sql_num_rows($re_answer_do)) {

				//find previous answer
				$find_prev = true;
				list($answer_do) = sql_fetch_row($re_answer_do);
			}
		}

		$content =
			'<div class="play_question">'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">';

		$answer_info = array();

		while(list($answer) = sql_fetch_row($re_answer))
			$answer_info[] = $answer;

		$num_answer = 0;

		for($i = $answer_info[0]; $i <= $answer_info[1]; $i += $answer_info[2])
		{
			$content .=		'<input type="radio" id="quest_'.$id_quest.'_'.$num_answer.'" '
							.'name="quest['.$id_quest.']" value="'.$i.'"'
							.( ($find_prev && $i == $id_answer_do) ? ' checked="checked"' : '' )
							.( $find_prev && $freeze ? ' disabled="disabled"' : '' ).' /> '
							.'<label class="text_answer" for="quest_'.$id_quest.'_'.$num_answer.'">'.$i.'</label><br />';
			
			$num_answer++;
		}

		$content .= '</div>'
				.'</div>';
		return $content;
	}

	/**
	 * display the quest for report
	 *
	 * @param 	int		$num_quest 			the number of the quest to display in front of the quest title
	 *
	 * @return string of html question code
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function playReport( $num_quest, $tot_tracks, &$valid_track ) {
		$lang =& DoceboLanguage::createInstance('poll');

		require_once(_lms_.'/lib/lib.course.php');

		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest
		FROM ".$GLOBALS['prefix_lms']."_pollquest
		WHERE id_quest = '".$this->id."'"));

		$query_answer = "
		SELECT answer
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY sequence";
		$re_answer = sql_query($query_answer);

		$answer_info = array();

		while(list($answer) = sql_fetch_row($re_answer))
			$answer_info[] = $answer;

		//recover previous information
		$max = 0;
		$not_answer = $tot_tracks;
		$recover_answer = "
		SELECT more_info, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_polltrack_answer
		WHERE id_quest = '".(int)$this->id."' ";
		if(is_array($valid_track) && !empty($valid_track)) $recover_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		$recover_answer .= " GROUP BY more_info ";
		$re_answer_do = sql_query($recover_answer);

		//find previous answer
		while(list($id_a, $num) = sql_fetch_row($re_answer_do))
		{
			if($num > $max) $max = $num;
			$num_answer[$id_a] = $num;
			$not_answer -= $num;
		}
		$content =
			'<div class="play_question">'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">'
			.'<table summary="'.$lang->def('_SUMMARY_ANSWER').'" cellspancing="0" class="poll_report">'
			.'<caption>'.$lang->def('_CAPTION_ANSWER').'</caption>'
			.'<thead>'
				.'<tr><th class="text_answer" scope="col">'.$lang->def('_ANSWER_TEXT').'</th><th scope="col">'.$lang->def('_ANSWER_NUMBER').'</th></tr>'
			.'</thead>';

		for($i = $answer_info[0]; $i <= $answer_info[1]; $i += $answer_info[2])
		{
			if(isset($num_answer[(string)$i]) && $max != 0)
				$content .=	'<tr><td>'.$i.'</td><td><div class="colored_row" style="width: '.round($num_answer[(string)$i] / $max * 90, 2).'%;">'
							.$num_answer[(string)$i].'</div></td></tr>';
			else
				$content .=	'<tr><td>'.$i.'</td><td><div class="colored_row" style="width: 0%;">'
							.'</div></td></tr>';
		}

		$content .= '</table>'
				.'</div>'
				.'</div>';

		return $content;
	}

	/**
	 * save the answer to the question in an proper format
	 *
	 * @param  int		$id_track		the relative id_track
	 * @param  array	$source			source of the answer send by the user
	 * @param  bool		$can_overwrite	if the answer for this question exists and this is true, the old answer
	 *									is updated, else the old answer will be leaved
	 *
	 * @return bool	true if success false otherwise
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function storeAnswer( $id_track, &$source, $can_overwrite = false )
	{
		$result = true;

		if($this->userDoAnswer($id_track)) {
			if(!$can_overwrite) return true;
			if(!$this->deleteAnswer($id_track)) return false;
		}
		
		if(isset($source['quest'][$this->id]))
		{
			//answer checked by the user 
			$track_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']	."_polltrack_answer ( id_track, id_quest, id_answer, more_info ) 
			VALUES (
				'".(int)$id_track."', 
				'".(int)$this->id."', 
				'0', 
				'".$source['quest'][$this->id]."' )";
			return sql_query($track_query);
		}
	}

	/**
	 * delete the old answer
	 *
	 * @param  int		$id_track	the relative id_track
	 *
	 * @return bool	true if success false otherwise
	 *
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function deleteAnswer( $id_track )
	{
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_polltrack_answer
		WHERE id_track = '".(int)$id_track."' AND
			id_quest = '".$this->id."'");
	}

	function export_CSV( $num_quest, $tot_tracks, &$valid_track ) {
		$lang =& DoceboLanguage::createInstance('poll');

		require_once(_lms_.'/lib/lib.course.php');

		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest
		FROM ".$GLOBALS['prefix_lms']."_pollquest
		WHERE id_quest = '".$this->id."'"));

		$query_answer = "
		SELECT answer
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY sequence";
		$re_answer = sql_query($query_answer);

		$answer_info = array();

		while(list($answer) = sql_fetch_row($re_answer))
			$answer_info[] = $answer;

		//recover previous information
		$max = 0;
		$not_answer = $tot_tracks;
		$recover_answer = "
		SELECT more_info, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_polltrack_answer
		WHERE id_quest = '".(int)$this->id."' ";
		if(is_array($valid_track) && !empty($valid_track)) $recover_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		$recover_answer .= " GROUP BY more_info ";
		$re_answer_do = sql_query($recover_answer);

		//find previous answer
		while(list($id_a, $num) = sql_fetch_row($re_answer_do)) {

			if($num > $max) $max = $num;
			$num_answer[$id_a] = $num;
			$not_answer -= $num;
		}
		$content = '"'.$num_quest.'";"'.str_replace('"', '""', $title_quest).'";"'.$lang->def('_QUEST_DOC_VALUTATION').'"'."\r\n";

		for($i = $answer_info[0]; $i <= $answer_info[1]; $i += $answer_info[2])
		{
			if(isset($num_answer[(string)$i]) && $max != 0)
				$content .=  ';"'.str_replace('"', '""', $i).'";'.$num_answer[(string)$i]."\r\n";
			else
				$content .=  ';"'.str_replace('"', '""', $i).'";0'."\r\n";
		}

		return $content;
	}
}

?>