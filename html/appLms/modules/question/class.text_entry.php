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

class TextEntry_Question extends Question {
	
	function TextEntry_Question( $id ) {
		parent::Question( $id );
	}
	
	function getQuestionType() {
		return 'text_entry';
	}
	
	/**
	 * function create()
	 *
	 * @param $back_url	the url where the function retutn at the end of the operation
	 * @return nothing
	 */
	function create( $idTest, $back_test ) {
		$lang =& DoceboLanguage::createInstance('test');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_test));
		
		if(isset($_POST['add_question'])) {
			//insert question
			if(substr_count($_POST['title_quest'], '[answer]') != 1) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;idTest='.$idTest.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			$ins_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']	."_testquest 
			( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
			( 	'".(int)$idTest."', 
				'".(int)$_POST['idCategory']."', 
				'".$this->getQuestionType()."', 
				'".$_POST['title_quest']."',
				'".(int)$_POST['difficult']."', 
				'".(int)$_POST['time_assigned']."', 
				'".$this->_getNextSequence($idTest)."', 
				'".$this->_getPageNumber($idTest)."' ) ";
			if(!sql_query($ins_query)) {
				
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;idTest='.$idTest.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			
			//find id of auto_increment colum
			list($idQuest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			if(!$idQuest) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
			}
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']	."_testquestanswer 
			( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES 
			( 	'".$idQuest."', 
				'1', 
				'".strtolower($_POST['answer'])."', 
				'".$_POST['comment']."', 
				'".$this->_checkScore($_POST['score_correct'])."', 
				'".$this->_checkScore($_POST['score_incorrect'])."' ) ";
			if(!sql_query($ins_answer_query)) {
				
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
			}
			//back to question list
			Util::jump_to($back_test);
		}
		//category form
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//writing difficult array
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
		
			.Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest', '[answer]'), 'content');
		if (count($categories) > 1)
			$GLOBALS['page']->add( Form::getDropdown($lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories), 'content');
		
		$GLOBALS['page']->add(Form::getDropdown( $lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult, 3)
			.Form::getTextfield( $lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5, 
			( isset($_POST['time_assigned']) ? $_POST['time_assigned'] : '00000' ), $lang->def('_TEST_QUEST_TIMEASS'),
			$lang->def('_SECONDS') )
			.'<div class="nofloat"></div><br />'
			.'<table class="test_answer"  summary="'.$lang->def('_TEST_ANSWER').'">'."\n"
			.'<caption>'.$lang->def('_TEST_ANSWER').'</caption>'."\n"
			.'<tr>'
			.'<th class="image">'.$lang->def('_TEST_CORRECT').'</th>'
			.'<th><label for="answer">'.$lang->def('_TEST_TEXT_ANSWER').'</label></th>'
			.'<th><label for="comment">'.$lang->def('_COMMENTS').'</label></th>'
			.'<th colspan="2">'.$lang->def('_SCORE').'</th>'
			.'</tr>'."\n"
			.'<tr class="line_answer">'
			.'<td rowspan="2" class="align_center">'
			//img si correct
			.'<img src="'.getPathImage().'test/correct.gif" alt="'.$lang->def('_TEST_ISCORRECT').'" />'
			.'</td>'
			.'<td rowspan="2">'
			//answer
			.'<input type="text" class="test_text_answer" id="answer" name="answer" alt="'.$lang->def('_TEST_TEXT_ANSWER').'" maxlength="255" value="'./*$lang->def('_QUEST_ANSWER').*/'" />'
			.'</td>'
			.'<td rowspan="2" class="image">'
			//comment
			.'<textarea class="test_comment" id="comment" name="comment" cols="14" rows="3">'.'</textarea>'
			.'</td>'
			.'<td class="test_ifcorrect">'
			.'<label for="score_correct">'.$lang->def('_TEST_IFCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score correct
			.'<input type="text" class="test_point" id="score_correct" name="score_correct" alt="'.$lang->def('_TEST_IFCORRECT').'" size="5" value="0.0" />'
			.'</td>'
			.'</tr>'."\n"
			.'<tr class="line_answer">'
			.'<td class="test_ifcorrect">'
			.'<label for="score_incorrect">'.$lang->def('_TEST_IFINCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score incorrect
			.'- <input type="text" class="test_point" id="score_incorrect" name="score_incorrect" alt="'.$lang->def('_TEST_IFINCORRECT').'" size="5" value="0.0" />'
			.'</td>'
			.'</tr>'."\n"
			.'</table>'
			.Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
		
			.Form::closeForm()
			.'</div>', 'content');
	}
	
	/**
	 * function edit()
	 *
	 * @param $back_url	the url where the function retutn at the end of the operation
	 * @return nothing
	 */
	function edit( $back_test ) {
		$lang =& DoceboLanguage::createInstance('test');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_test));
		
		if(isset($_POST['add_question'])) {
			//modify question
			if(substr_count($_POST['title_quest'], '[answer]') != 1) {
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;idQuest='.$this->id.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			
			$mod_query = "
			UPDATE ".$GLOBALS['prefix_lms']	."_testquest 
			SET idCategory = '".$_POST['idCategory']."', 
				type_quest = '".$this->getQuestionType()."', 
				title_quest = '".$_POST['title_quest']."', 
				difficult = '".$_POST['difficult']."',
				time_assigned = '".$_POST['time_assigned']."'
			WHERE idQuest = '".(int)$this->id."'";
			if(!sql_query($mod_query)) {
				
				errorCommunication($lang->def('_TEST_ERR_MOD_QUEST')
					.getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;idQuest='.$this->id.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			//modify answer
			$mod_answer_query = "
			UPDATE ".$GLOBALS['prefix_lms']	."_testquestanswer 
			SET answer = '".strtolower($_POST['answer'])."',
				comment = '".$_POST['comment']."',
				score_correct = '".$this->_checkScore($_POST['score_correct'])."', 
				score_incorrect = '".$this->_checkScore($_POST['score_incorrect'])."'
			WHERE idQuest = '".(int)$this->id."'";
			if(!sql_query($mod_answer_query)) {
				
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
			}
			//back to quest list
			Util::jump_to( ''.$back_test);
		}
		//finding categories
				require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//create array of difficult
		$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
		//load data
		list($cat_sel, $title_quest, $diff_sel, $sel_time) = sql_fetch_row(sql_query("
		SELECT idCategory, title_quest, difficult, time_assigned
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".(int)$this->id."'"));
		list($answer, $comment, $score_correct, $score_incorrect) = sql_fetch_row(sql_query("
		SELECT answer, comment, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'"));
		
		//drawing form
		$GLOBALS['page']->add(
			getTitleArea($lang->def('_TEST_SECTION'), 'test')
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
			.'<div class="nofloat"></div><br />'
			.'<table class="test_answer"  summary="'.$lang->def('_TEST_ANSWER').'">'."\n"
			.'<caption>'.$lang->def('_TEST_ANSWER').'</caption>'."\n"
			.'<tr>'."\n"
			.'<th class="image">'.$lang->def('_TEST_CORRECT').'</th>'
			.'<th><label for="answer">'.$lang->def('_TEST_TEXT_ANSWER').'</label></th>'
			.'<th><label for="comment">'.$lang->def('_COMMENTS').'</label></th>'
			.'<th colspan="2">'.$lang->def('_SCORE').'</th>'
			.'</tr>'."\n"
			
			.'<tr class="line_answer">'."\n"
			.'<td rowspan="2" class="align_center">'
			//img is correct
			.'<img src="'.getPathImage().'test/correct.gif" alt="'.$lang->def('_TEST_ISCORRECT').'" />'
			.'</td>'
			.'<td rowspan="2">'
			//answer
			.'<input type="text" class="test_text_anwer" id="answer" name="answer" alt="'.$lang->def('_TEST_TEXT_ANSWER').'" maxlength="255" value="'.$answer.'" />'
			.'</td>'
			.'<td rowspan="2" class="image">'
			//comment
			.'<textarea class="test_comment" id="comment" name="comment" cols="14" rows="3">'.$comment.'</textarea>'
			.'</td>'
			.'<td class="test_ifcorrect">'
			.'<label for="score_correct">'.$lang->def('_TEST_IFCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score correct
			.'<input type="text" class="test_point" id="score_correct" name="score_correct" alt="'.$lang->def('_TEST_IFCORRECT').'" size="5" value="'.$score_correct.'" />'
			.'</td>'
			.'</tr>'."\n"
			.'<tr class="line_answer">'."\n"
			.'<td class="test_ifcorrect">'
			.'<label for="score_incorrect">'.$lang->def('_TEST_IFINCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score incorrect
			.'- <input type="text" class="test_point" id="score_incorrect" name="score_incorrect" alt="'.$lang->def('_TEST_IFINCORRECT').'" size="5"  value="'.$score_incorrect.'" />'
			.'</td>'
			.'</tr>'."\n"
			.'</table>'."\n"
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
		
		//remove answer
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".$this->id."'")) {
			return false;
		}
		//remove question
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
		
		
		//retriving question
		list($sel_cat, $quest, $sel_diff, $time_ass, $sequence, $page) = sql_fetch_row(sql_query("
		SELECT idCategory, title_quest, difficult, time_assigned, sequence, page 
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".(int)$this->id."'"));
		//insert question
		$ins_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']	."_testquest 
		( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
		( 	'".(int)$new_id_test."', 
			'".(int)$sel_cat."', 
			'".$this->getQuestionType()."', 
			'".sql_escape_string($quest)."',
			'".(int)$sel_diff."', 
			'".$time_ass."',
			'".(int)$sequence."',
			'".(int)$page."' ) ";
		if(!sql_query($ins_query)) return false;
		//find id of auto_increment colum
		list($new_id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if(!$new_id_quest) return false;
		
		//retriving new answer
		$re_answer = sql_query("
		SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		while(list($idAnswer, $is_correct, $answer, $comment, $score_c, $score_inc) = sql_fetch_row($re_answer)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']	."_testquestanswer 
			( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
			( 	'".(int)$new_id_quest."', 
				'".(int)$is_correct."', 
				'".sql_escape_string($answer)."', 
				'".sql_escape_string($comment)."',
				'".$this->_checkScore($score_c)."', 
				'".$this->_checkScore($score_inc)."') ";
			if(!sql_query($ins_answer_query)) return false;
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
	function play( $num_quest, $shuffle_answer = false, $id_track = 0, $freeze = false, $number_time = null ) {
		$lang =& DoceboLanguage::createInstance('test');
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest 
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		$re_answer = sql_query("
		SELECT idAnswer, answer 
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		
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
		
		list($id_answer, $answer) = sql_fetch_row($re_answer);
		$num_char = strlen($answer);
		$text = '<input class="test_te_input" type="text" id="quest_'.$id_quest.'" name="quest['.$id_quest.']" '
				.'maxlength="'.strlen($answer).'" '.'autocomplete="off" '
				.'value="'.( $find_prev ? $answer_do : str_repeat($lang->def('_QUEST_TE_ANSWERHERE'), $num_char) ).'"'
				.( $find_prev && $freeze ? ' disabled="disabled"' : '' )
				.' />';
		
		
		return '<div class="play_question">'."\n"
            .'<div>'.$lang->def('_QUEST_'.strtoupper($this->getQuestionType())).'</div>'
			.'<div class="title_question">'.$num_quest.') '.$lang->def('_TEST_TE_TITLE').'</div>'."\n"
			.'<div class="answer_question">'
			.'<label for="quest_'.$id_quest.'">'.preg_replace('/\[answer\]/', $text, $title_quest).'</label>'
			.'</div>'."\n"
			.'</div>'."\n";
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

		$result = true;

		if ($this->userDoAnswer($trackTest->idTrack) && !$trackTest->getTestObj()->isRetainAnswersHistory()) {
			if($can_overwrite) {
				
				return $this->updateAnswer($trackTest->idTrack, $source);
			}
			else return false;
		}
		
		$re_answer = sql_query("
		SELECT idAnswer, answer, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'");
		list($id_answer, $answer, $score_corr, $score_incorr) = sql_fetch_row($re_answer);
		
		if(strtolower($answer) == strtolower(stripslashes($source['quest'][$this->id]))) $is_correct = true;
		else $is_correct = false;
		
		$track_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']	."_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
		VALUES (
			'".(int)$trackTest->idTrack."',
			'".(int)$this->id."', 
			'".(int)$id_answer."', 
			'".( $is_correct ? $score_corr : -$score_incorr )."', 
			'".$source['quest'][$this->id]."',
			1,
			'".(int)($trackTest->getNumberOfAttempt()+1)."')";
		return  sql_query($track_query);
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
		
		
		$re_answer = sql_query("
		SELECT idAnswer, answer, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'");
		list($id_answer, $answer, $score_corr, $score_incorr) = sql_fetch_row($re_answer);
		
		if(strtolower($answer) == strtolower(stripslashes($source['quest'][$this->id]))) $is_correct = true;
		else $is_correct = false;
		
		return sql_query("
		UPDATE ".$GLOBALS['prefix_lms']	."_testtrack_answer 
		SET score_assigned = '".( $is_correct ? $score_corr : -$score_incorr )."', 
			more_info = '".$source['quest'][$this->id]."' 
		WHERE idTrack = '".(int)$id_track."' AND 
			idQuest = '".$this->id."'");
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
		
		
		return 'auto';
	}
	
	/**
	 * display the question with the result of a user
	 * 
	 * @param  	int		$id_track		the test relative to this question
	 * @param  	int		$num_quest		the quest sequqnce number
	 * @param  	int		$number_time	the quest attempt number
	 * 
	 * @return array	return an array with xhtml code in this way
	 * 					string	'quest' 	=> the quest, 
	 *					double	'score'		=> score obtained from this question, 
	 *					string	'comment'	=> relative comment to the quest )
	 * 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function displayUserResult( $id_track, $num_quest, $show_solution, $number_time = null ) {
		$lang =& DoceboLanguage::createInstance('test');
		
		$quest = '';
		$comment = '';
		$com_is_correct = '';
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest 
		FROM ".$GLOBALS['prefix_lms']	."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		$re_answer = sql_query("
		SELECT idAnswer, answer, comment 
		FROM ".$GLOBALS['prefix_lms']	."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		
		//recover previous information
		$recover_answer = "
		SELECT more_info 
		FROM ".$GLOBALS['prefix_lms']	."_testtrack_answer 
		WHERE idQuest = '".(int)$this->id."' AND 
			idTrack = '".(int)$id_track."'";
        if ($number_time != null){
            $recover_answer .= " AND number_time = ".$number_time;
        } else {
			$recover_answer .= " ORDER BY number_time DESC LIMIT 1";
		}

		list($answer_do) = sql_fetch_row(sql_query($recover_answer));
		
		list($id_answer, $answer, $com) = sql_fetch_row($re_answer);
		
		
		$text = '<span class="text_bold">'.( trim($answer_do) != '' ? $answer_do : $lang->def('_EMPTY_ANSWER') ).'</span>';
		if(strtolower($answer_do) == strtolower($answer)) {
			$text .= ' <strong class="test_answer_correct">'.$lang->def('_TEST_CORRECT').'</strong>';
			$comment = $com;
		} else {
			$text .= ' <strong class="test_answer_incorrect">'.$lang->def('_TEST_INCORRECT').'</strong>';
			if($show_solution) $com_is_correct = '<strong>'.$lang->def('_TEST_ISNOT_THECORRECT').' : </strong>'.$answer;
			$comment = $com;
		}
		
		$quest = '<div class="play_question">'."\n"
			.'<div class="title_question">'.$num_quest.') '.$lang->def('_TEST_TE_TITLE').'</div>'."\n"
			.'<div class="answer_question">'
			.str_replace('[answer]', $text, $title_quest)
			.'</div>'."\n"
			.'</div>'."\n";
		
		return array(	'quest' 	=> $quest, 
						'score'		=> $this->userScore($id_track, $number_time),
						'comment'	=> ( $com_is_correct != '' ? $com_is_correct.'<br />' : '' ).$comment );
		
	}

   	public static function getTextEntryFromIdTrackAndIdQuest($idTrak, $idQuest)
    {
        $query_track_answer = "SELECT more_info"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer"
            . " WHERE idTrack = '" . $idTrak . "'"
            . " AND idQuest = '" . $idQuest . "'";

        $result_track_answer = sql_query($query_track_answer);

        $result = array();
        while (list($more_info) = sql_fetch_row($result_track_answer)) {
            $result[] = $more_info;
        }
        return $result;
    }
}

?>
