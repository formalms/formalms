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

class ExtendedText_Question extends Question {
	
	var $id;
	
	/**
	 * function ExtendedText_Question( $id )
	 *
	 * @param int $id 	the id of the question
	 * @return nothing
	 */
	function ExtendedText_Question( $id ) {
		
		parent::Question( $id );
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
	function create($idTest, $back_test) {
		$lang =& DoceboLanguage::createInstance('test');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_test));
		
		if(isset($_POST['add_question'])) {
			if(!sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']	."_testquest 
			( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
			( 	'".$idTest."', 
				'".(int)$_POST['idCategory']."', 
				'".$this->getQuestionType()."', 
				'".$_POST['title_quest']."',
				'".(int)$_POST['difficult']."', 
				'".(int)$_POST['time_assigned']."', 
				'".$this->_getNextSequence($idTest)."', 
				'".$this->_getPageNumber($idTest)."') ")) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;idTest='.$idTest.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			list($id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			
			if(!sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']	."_testquestanswer 
			( idQuest, score_correct, is_correct ) VALUES 
			( 	'".$id_quest."', 
				'".$this->_checkScore($_POST['max_score'])."',
				'1') ")) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;idTest='.$idTest.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			Util::jump_to( ''.$back_test);
		}
		//finding categories
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//create array of difficult
		$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
		
		$GLOBALS['page']->add(getTitleArea($lang->def('_TEST_SECTION'), 'test')
			.'<div class="std_block">'
			.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=create')
		
			.Form::openElementSpace()
		
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('idTest', 'idTest', $idTest)
			.Form::getHidden('back_test', 'back_test', $url_encode)
		
			.Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest'), 'content');
		if (count($categories) > 1)
			$GLOBALS['page']->add(Form::getDropdown( $lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories), 'content');
		$GLOBALS['page']->add(Form::getDropdown( $lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult, 3)
			.Form::getTextfield( $lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5, 
			( isset($_POST['time_assigned']) ? $_POST['time_assigned'] : '00000' ), $lang->def('_TEST_QUEST_TIMEASS'),
			$lang->def('_SECONDS') )
		
			.Form::getBreakRow()
			.Form::getTextfield( $lang->def('_MAX_SCORE'), 'max_score', 'max_score', 255,
			( isset($_POST['max_score']) ? $_POST['max_score'] : '0.0' ), $lang->def('_MAX_SCORE') )
		
			.Form::getBreakRow()
			.Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
		
			.Form::closeForm()
			.'</div>', 'content');
	}
	
	function edit($back_test) {
		$lang =& DoceboLanguage::createInstance('test');
		
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_test));
		
		if(isset($_POST['add_question'])) {
			if(!sql_query("
			UPDATE ".$GLOBALS['prefix_lms']	."_testquest 
			SET idCategory = '".(int)$_POST['idCategory']."', 
				title_quest = '".$_POST['title_quest']."', 
				difficult = '".(int)$_POST['difficult']."', 
				time_assigned = '".(int)$_POST['time_assigned']."' 
			WHERE idQuest = '".$this->id."'")) {
				errorCommunication($lang->def('_ERR_INS_QUEST')
					.getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;idQuest='.$this->id.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			
			if(!sql_query("
			UPDATE ".$GLOBALS['prefix_lms']	."_testquestanswer 
			SET score_correct = '".$this->_checkScore($_POST['max_score'])."'
			WHERE idQuest = '".$this->id."'")) {
				errorCommunication($lang->def('_ERR_INS_QUEST')
					.getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;idQuest='.$this->id.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			
			Util::jump_to( ''.$back_test);
		}
		//finding categories
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//create array of difficult
		$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
		
		list($title_quest, $cat_sel, $diff_sel, $sel_time) = sql_fetch_row(sql_query("
		SELECT title_quest, idCategory, difficult, time_assigned 
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		list($max_score) = sql_fetch_row(sql_query("
		SELECT score_correct
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".$this->id."'"));
		
		$GLOBALS['page']->add(getTitleArea($lang->def('_TEST_SECTION'), 'test')
			.'<div class="std_block">'
			.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_mod_quest', 'index.php?modname=question&amp;op=edit')
		
			.Form::openElementSpace()
		
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('idQuest', 'idQuest', $this->id)
			.Form::getHidden('back_test', 'back_test', $url_encode)
		
			.Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest', $title_quest), 'content');
		if (count($categories) > 1)
			$GLOBALS['page']->add(Form::getDropdown( $lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories,
				( isset($_POST['idCategory']) ? $_POST['idCategory'] : $cat_sel )), 'content');
		$GLOBALS['page']->add(Form::getDropdown( $lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult, $diff_sel)
			.Form::getTextfield( $lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5, 
			( isset($_POST['time_assigned']) ? $_POST['time_assigned'] : $sel_time ), $lang->def('_TEST_QUEST_TIMEASS'),
			$lang->def('_SECONDS') )
			.Form::getBreakRow()
			.Form::getTextfield( $lang->def('_MAX_SCORE'), 'max_score', 'max_score', 255,
			( isset($_POST['max_score']) ? $_POST['max_score'] : $max_score ), $lang->def('_MAX_SCORE') )
		
			.Form::getBreakRow()
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
		DELETE FROM ".$GLOBALS['prefix_lms']	."_testtrack_answer 
		WHERE idQuest = '".$this->id."'")) return false;
		
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".$this->id."'")) return false;
		
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".$this->id."'");
	}
	
	/**
	 * this function create a copy of a question and return the corresponding id
	 * 
	 * @return int 	return the id of the new question if success else return false
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function copy( $new_id_test, $back_test = NULL ) {
		
		
		return parent::copy($new_id_test, $back_test);
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
	function play( $num_quest, $shuffle_answer = false, $id_track = 0, $freeze = false, $number_time = null ) {
		
		$lang =& DoceboLanguage::createInstance('test');
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest 
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		$find_prev = false;
		$id_answer_do = 0;
		if($id_track != 0) {
			
			//recover previous information
			$recover_answer = "
			SELECT more_info 
			FROM ".$GLOBALS['prefix_lms']	."_testtrack_answer 
			WHERE idQuest = '".(int)$this->id."' AND 
				idTrack = '".(int)$id_track."' AND number_time =  ".$number_time;
			$re_answer_do = sql_query($recover_answer);
			if(sql_num_rows($re_answer_do)) {
				
				//find previous answer
				$find_prev = true;
				list($answer_do) = sql_fetch_row($re_answer_do);
			}
		}
		
		return '<div class="play_question">'
            .'<div>'.$lang->def('_QUEST_'.strtoupper($this->getQuestionType())).'</div>'
			.'<div class="title_question"><label for="quest_'.$id_quest.'">'.$num_quest.') '
			.$title_quest.'</label></div>'
			.'<div class="answer_question">'
			.'<textarea cols="50" rows="7" id="quest_'.$id_quest.'" name="quest['.$id_quest.']"'
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
	function storeAnswer(Track_Test $trackTest, &$source, $can_overwrite = false ) {

		$can_overwrite = $can_overwrite && !$trackTest->getTestObj()->isRetainAnswersHistory();
		$result = true;
		
		if($this->userDoAnswer($trackTest->idTrack)) {
			if(!$can_overwrite) return true;
			if(!$this->deleteAnswer($trackTest->idTrack)) return false;
		}
		
		if(isset($source['quest'][$this->id])) {
				
			//answer checked by the user 
			$track_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
			VALUES (
				'".(int)$trackTest->idTrack."',
				'".(int)$this->id."', 
				'0', 
				'0', 
				'".$source['quest'][$this->id]."',
				1,
				'".(int)($trackTest->getNumberOfAttempt()+1)."')";
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
		DELETE FROM ".$GLOBALS['prefix_lms']	."_testtrack_answer 
		WHERE idTrack = '".(int)$id_track."' AND 
			idQuest = '".$this->id."'");
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
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest 
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		//recover previous information
		$recover_answer = "
		SELECT more_info, manual_assigned 
		FROM ".$GLOBALS['prefix_lms']	."_testtrack_answer 
		WHERE idQuest = '".(int)$this->id."' AND 
			idTrack = '".(int)$id_track."'";
        if ($number_time != null){
            $recover_answer .= " AND number_time = ".$number_time;
        }

		list($answer_do, $manual_assigned ) = sql_fetch_row(sql_query($recover_answer));
		
		$quest = '<div class="play_question">'
			.'<div class="title_question"><label for="quest_'.$id_quest.'">'.$num_quest.') '
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
}

?>
