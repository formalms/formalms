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

require_once( dirname(__FILE__).'/class.question.php' );

class ExtendedText_QuestionPoll extends QuestionPoll {
	
	var $id;
	
	/**
	 * function ExtendedText_QuestionPoll( $id )
	 *
	 * @param int $id 	the id of the question
	 * @return nothing
	 */
	function ExtendedText_QuestionPoll( $id ) {
		
		parent::QuestionPoll( $id );
	}
	
	/**
	 * function getQuestionType()
	 *
	 * Return the type of the question
	 *
	 * @return string the type of the question
	 */
	function getQuestionType() {
		return 'extended_text';
	}
	
	/**
	 * function create()
	 *
	 * @param $back_url	the url where the function retutn at the end of the operation
	 * @return nothing
	 */
	function create($id_poll, $back_poll) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_poll));
		
		if(isset($_POST['add_question'])) {
			if(!sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']	."_pollquest 
			( id_poll, id_category, type_quest, title_quest, sequence, page ) VALUES 
			( 	'".$id_poll."',
				'".''."',
				'".$this->getQuestionType()."', 
				'".$_POST['title_quest']."', 
				'".$this->_getNextSequence($id_poll)."', 
				'".$this->_getPageNumber($id_poll)."') ")) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question_poll&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;id_poll='.$id_poll.'&amp;back_poll='.$url_encode, $lang->def('_BACK')));
			}
			list($id_poll) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			
			if(!sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']	."_pollquestanswer 
			( id_quest, score_correct, is_correct ) VALUES 
			( 	'".$id_poll."', 
				'".$this->_checkScore($_POST['max_score'])."',
				'1') ")) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question_pool&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;id_poll='.$id_poll.'&amp;back_poll='.$url_encode, $lang->def('_BACK')));
			}
			Util::jump_to( ''.$back_poll);
		}
		//finding categories
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//create array of difficult
		$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
		
		$GLOBALS['page']->add(getTitleArea($lang->def('_POLL_SECTION'), 'poll')
			.'<div class="std_block">'
			.getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_add_quest', 'index.php?modname=question_poll&amp;op=create')
		
			.Form::openElementSpace()
		
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('id_poll', 'id_poll', $id_poll)
			.Form::getHidden('back_poll', 'back_poll', $url_encode)
		
			.Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest')
			
			.Form::getBreakRow()
			.Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
		
			.Form::closeForm()
			.'</div>', 'content');
	}
	
	function edit($back_poll) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_poll));
		
		if(isset($_POST['add_question'])) {
			if(!sql_query("
			UPDATE ".$GLOBALS['prefix_lms']	."_pollquest 
			SET title_quest = '".$_POST['title_quest']."' 
			WHERE id_quest = '".$this->id."'")) {
				errorCommunication($lang->def('_ERR_INS_QUEST')
					.getBackUi('index.php?modname=question_poll&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;id_quest='.$this->id.'&amp;back_poll='.$url_encode, $lang->def('_BACK')));
			}
			
			Util::jump_to( ''.$back_poll);
		}
		//finding categories
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//create array of difficult
		$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
		
		list($title_quest) = sql_fetch_row(sql_query("
		SELECT title_quest  
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_quest = '".$this->id."'"));
		
		$GLOBALS['page']->add(getTitleArea($lang->def('_POLL_SECTION'), 'poll')
			.'<div class="std_block">'
			.getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_mod_quest', 'index.php?modname=question_poll&amp;op=edit')
		
			.Form::openElementSpace()
		
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('id_quest', 'id_quest', $this->id)
			.Form::getHidden('back_poll', 'back_poll', $url_encode)
		
			.Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest', $title_quest), 'content');
	
		$GLOBALS['page']->add(''
			.Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
		
			.Form::closeForm()
			.'</div>', 'content');
	}
	
	function del() {
		
		
		//delete answer
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_polltrack_answer 
		WHERE id_quest = '".$this->id."'")) return false;
		
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_pollquestanswer 
		WHERE id_quest = '".$this->id."'")) return false;
		
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_pollquest 
		WHERE id_quest = '".$this->id."'");
	}
	
	/**
	 * this function create a copy of a question and return the corresponding id
	 * 
	 * @return int 	return the id of the new question if success else return false
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function copy( $new_id_test, $back_poll = NULL ) {
		
		
		return parent::copy($new_id_test, $back_poll);
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
		
		$lang =& DoceboLanguage::createInstance('test');
		
		list($id_poll, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest 
		FROM ".$GLOBALS['prefix_lms']	."_pollquest 
		WHERE id_quest = '".$this->id."'"));
		
		$find_prev = false;
		$id_answer_do = 0;
		if($id_track != 0) {
			
			//recover previous information
			$recover_answer = "
			SELECT more_info 
			FROM ".$GLOBALS['prefix_lms']	."_polltrack_answer 
			WHERE id_quest = '".(int)$this->id."' AND 
				id_track = '".(int)$id_track."'";
			$re_answer_do = sql_query($recover_answer);
			if(sql_num_rows($re_answer_do)) {
				
				//find previous answer
				$find_prev = true;
				list($answer_do) = sql_fetch_row($re_answer_do);
			}
		}
		
		return '<div class="play_question">'
			.'<div class="title_question"><label for="quest_'.$id_poll.'">'.$num_quest.') '
			.$title_quest.'</label></div>'
			.'<div class="answer_question">'
			.'<textarea cols="50" rows="7" id="quest_'.$id_poll.'" name="quest['.$id_poll.']"'
			.( $find_prev && $freeze ? ' disabled="disabled"' : '' ).'>'
			.( $find_prev ? $answer_do : $lang->def('_QUEST_FREEANSWER') ).'</textarea>'
			.'</div>'
			.'</div>';
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
	function storeAnswer( $id_track, &$source, $can_overwrite = false ) {
		
		
		$result = true;
		
		if($this->userDoAnswer($id_track)) {
			if(!$can_overwrite) return true;
			if(!$this->deleteAnswer($id_track)) return false;
		}
		
		if(isset($source['quest'][$this->id])) {
				
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
	 * save the answer to the question in an proper format overwriting the old entry
	 * 
	 * @param  int		$id_track	the relative id_track
	 * @param  array	$source		source of the answer send by the user
	 * 
	 * @return bool	true if success false otherwise
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function updateAnswer( $id_track, &$source ) {
		
		if(!$this->deleteAnswer($id_track)) return false;
		else return $this->storeAnswer($id_track, $source, false);
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
	function deleteAnswer( $id_track ) {
		
		
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_polltrack_answer 
		WHERE id_track = '".(int)$id_track."' AND 
			id_quest = '".$this->id."'");
	}
	
	/**
	 * get the method used to obtain result automatic or manual
	 * 
	 * @return string 	contain one of these value :
	 *					'none' if the question doesn't return any score (such as title or break_page)
	 *					'manual' if the score is set by a user, 
	 *					'auto' if the system automatical assign a result
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function getScoreSetType() {
		
		
		return 'manual';
	}
	
	/**
	 * display the question with the result of a user
	 * 
	 * @param  	int		$id_track		the test relative to this question
	 * @param  	int		$num_quest		the quest sequqnce number
     * @param  	int		$number_time	the quest attempt number
	 * 
	 * @return array	return an array with xhtml code in this way
	 * 					string	'quest' 			=> the quest, 
	 *					double	'score'				=> score obtained from this question, 
	 *					string	'comment'			=> relative comment to the quest 
	 * 					bool	'manual_assigned'	=> if the score is alredy assigned manually, this is true 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function displayUserResult( $id_track, $num_quest, $show_solution, $number_time = null ) {
		
		$lang =& DoceboLanguage::createInstance('test');
		
		$quest = '';
		$comment = '';
		
		list($id_poll, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest 
		FROM ".$GLOBALS['prefix_lms']	."_pollquest 
		WHERE id_quest = '".$this->id."'"));
		
		//recover previous information
		$recover_answer = "
		SELECT more_info, manual_assigned 
		FROM ".$GLOBALS['prefix_lms']	."_polltrack_answer 
		WHERE id_quest = '".(int)$this->id."' AND 
			id_track = '".(int)$id_track."'";
		list($answer_do, $manual_assigned ) = sql_fetch_row(sql_query($recover_answer));
		
		$quest = '<div class="play_question">'
			.'<div class="title_question"><label for="quest_'.$id_poll.'">'.$num_quest.') '
			.$title_quest.'</label></div>'
			.'<div class="answer_question">'
			.$answer_do
			.'</div>'
			.'</div>';
		
		return array(	'quest' 	=> $quest, 
						'score'		=> $this->userScore($id_track, $number_time),
						'comment'	=> '',
						'manual_assigned' => ( $manual_assigned ? true : false ) );
	}
	
		function playReport( $num_quest, $tot_tracks, &$valid_track ) {
    $idItem = $num_quest;
    $html='';
    $lang =& DoceboLanguage::createInstance('stats', 'lms');
    require_once(_base_.'/lib/lib.table.php');
		
		$query_quest = "SELECT id_quest, title_quest" .
									 " FROM ".$GLOBALS['prefix_lms']."_pollquest" .
									 " WHERE id_quest = '".(int)$this->id."'";
	
		$result_quest = sql_query($query_quest);
	
		$type_h = array('');
		$cont_h = array($lang->def('_ANSWER'));
		
		list($id_quest, $title_quest) = sql_fetch_row($result_quest);
	
		$tb = new Table(/*400, $title_quest*/);
		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);
			
		$query_answer = "SELECT more_info" .
										" FROM ".$GLOBALS['prefix_lms']."_polltrack_answer" .
										" WHERE id_quest = '".$id_quest."'";
		
		if(is_array($valid_track) && !empty($valid_track))
			$query_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		
		$result_answer = sql_query($query_answer);
	
		while (list($answer) = sql_fetch_row($result_answer)) {
			$cont = array();
			$cont[] = $answer;
		
			$tb->addBody($cont);
		}
	
		$html .= '<div class="play_question">'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">'
			.$tb->getTable().'<br/>'
			.'</div>'
			.'</div>';
	
    return $html;
  }
	
	function export_CSV( $num_quest, $tot_tracks, &$valid_track ) {
    $idItem = $num_quest;
    $csv='';
    $lang =& DoceboLanguage::createInstance('poll', 'lms');
    require_once(_base_.'/lib/lib.table.php');
		
		$query_quest = "SELECT id_quest, title_quest" .
									 " FROM ".$GLOBALS['prefix_lms']."_pollquest" .
									 " WHERE id_quest = '".(int)$this->id."'";
		
		$result_quest = sql_query($query_quest);
	
		/*$lang->def('_ANSWER')*/
		
		list($id_quest, $title_quest) = sql_fetch_row($result_quest);
	
		$csv .= '"'.$num_quest.'";"'.str_replace('"', '""', $title_quest).'";"'.$lang->def('_QUEST_EXTENDED_TEXT').'"'."\r\n";
			
		$query_answer = "SELECT more_info" .
										" FROM ".$GLOBALS['prefix_lms']."_polltrack_answer" .
										" WHERE id_quest = '".$id_quest."'";
		
		if(is_array($valid_track) && !empty($valid_track))
			$query_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		
		if(is_array($valid_track) && !empty($valid_track))
			$result_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		
		$result_answer = sql_query($query_answer);
	
		while (list($answer) = sql_fetch_row($result_answer)) {
			$csv .= ';"'.str_replace('"', '""', $answer).'";'."\r\n";
		}
				
    return $csv;
  }
}

?>