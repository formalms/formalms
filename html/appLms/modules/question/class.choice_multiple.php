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

class ChoiceMultiple_Question extends Question {
	
	/**
	 * class constructor
	 * 
	 * @param int	the unique database identifer of a question 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function ChoiceMultiple_Question( $id ) {
		parent::Question($id);
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
		return 'choice_multiple';
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
		$lang =& DoceboLanguage::createInstance('test');
		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td rowspan="2" class=" valign_top align_center">'
			.'<label for="is_correct_'.$i.'">'.$lang->def('_TEST_CORRECT').'</label><br /><br />'
			.'<input type="checkbox" id="is_correct_'.$i.'" name="is_correct['.$i.']" value="1"'
			.( isset($_POST['is_correct'][$i])  ? ' checked="checked"' : '').' />'
			.'</td>'
			.'<td rowspan="2" class="image">'
			//answer
			.'<label class="access-only" for="answer_'.$i.'">'.$lang->def('_TEST_TEXT_ANSWER').'</label>'
			//.Form::getTextarea('', 'answer_'.$i, 'answer['.$i.']', ( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : ''),false,'','form_line_l','floating','textarea',true)

			.loadHtmlEditor('',
							'answer_'.$i, 
							'answer['.$i.']', 
							( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : ''),
							false, 
							'', 
							true)
							
			//.'<textarea class="test_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="25" rows="3">'
			//.( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : '')//$lang->def('_QUEST_ANSWER')
			//.'</textarea>'
			.'</td>'
			.'<td rowspan="2" class="image">'
			//comment
			.'<label class="access-only" for="comment_'.$i.'">'.$lang->def('_COMMENTS').'</label>'
			.'<textarea class="test_comment" id="comment_'.$i.'" name="comment['.$i.']" rows="6">'
			.( isset($_POST['comment'][$i]) ? stripslashes($_POST['comment'][$i]) : '')
			.'</textarea>'
			.'</td>'
			.'<td class="test_ifcorrect">'
			.'<label for="score_correct_'.$i.'">'.$lang->def('_TEST_IFCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score correct
			.'<input type="text" class="test_point" id="score_correct_'.$i.'" name="score_correct['.$i.']" alt="'.$lang->def('_TEST_IFCORRECT').'" size="5" value="'
			.( isset($_POST['score_correct'][$i]) ? $_POST['score_correct'][$i] : '0.0').'" />'
			.'</td>'
			.'</tr>'."\n"
			.'<tr class="line_answer">'
			.'<td class="test_ifcorrect">'
			.'<label for="score_incorrect_'.$i.'">'.$lang->def('_TEST_IFINCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score incorrect
			.'- <input type="text" class="test_point" id="score_incorrect_'.$i.'" name="score_incorrect['.$i.']" alt="'.$lang->def('_TEST_IFINCORRECT').'" size="5" value="'
			.( isset($_POST['score_incorrect'][$i]) ? $_POST['score_incorrect'][$i] : '0.0').'" />'
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
		$lang =& DoceboLanguage::createInstance('test');
		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td rowspan="2" class=" valign_top align_center">'
			.'<label for="is_correct_'.$i.'">'.$lang->def('_TEST_CORRECT').'</label><br /><br />', 'content');
		if(isset($_POST['answer_id'][$i])) {
			$GLOBALS['page']->add('<input type="hidden" id="answer_id_'.$i.'" name="answer_id['.$i.']" value="'.$_POST['answer_id'][$i].'" />', 'content');
		}
		$GLOBALS['page']->add('<input type="checkbox" id="is_correct_'.$i.'" name="is_correct['.$i.']" value="1"'
			.( isset($_POST['is_correct'][$i])  ? ' checked="checked"' : '').' />'
			.'</td>'
			.'<td rowspan="2" class="image">'
			//answer
			.'<label class="access-only" for="answer_'.$i.'">'.$lang->def('_TEST_TEXT_ANSWER').'</label>'
			
			//.Form::getTextarea('', 'answer_'.$i, 'answer['.$i.']', ( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : ''),false,'','form_line_l','floating','textarea',true)

			.loadHtmlEditor('',
							'answer_'.$i, 
							'answer['.$i.']', 
							( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : ''),
							false, 
							'', 
							true)
							
			//.'<textarea class="test_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="25" rows="3">'
			//.( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : '')
			//.'</textarea>'
			.'</td>'
			.'<td rowspan="2" class="image">'
			//comment
			.'<label class="access-only" for="comment_'.$i.'">'.$lang->def('_COMMENTS').'</label>'
			.'<textarea class="test_comment" id="comment_'.$i.'" name="comment['.$i.']" rows="6">'
			.( isset($_POST['comment'][$i]) ? stripslashes($_POST['comment'][$i]) : '')
			.'</textarea>'
			.'</td>'
			.'<td class="test_ifcorrect">'
			.'<label for="score_correct_'.$i.'">'.$lang->def('_TEST_IFCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score correct
			.'<input type="text" class="test_point" id="score_correct_'.$i.'" name="score_correct['.$i.']" alt="'.$lang->def('_TEST_IFCORRECT').'" size="5" value="'
			.( isset($_POST['score_correct'][$i]) ? $_POST['score_correct'][$i] : '0.0').'" />'
			.'</td>'
			.'</tr>'."\n"
			.'<tr class="line_answer">'
			.'<td class="test_ifcorrect">'
			.'<label for="score_incorrect_'.$i.'">'.$lang->def('_TEST_IFINCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score incorrect
			.'- <input type="text" class="test_point" id="score_incorrect_'.$i.'" name="score_incorrect['.$i.']" alt="'.$lang->def('_TEST_IFINCORRECT').'" size="5" value="'
			.( isset($_POST['score_incorrect'][$i]) ? $_POST['score_incorrect'][$i] : '0.0').'" />'
			.'</td>'
			.'</tr>'."\n", 'content');
	}
	
	/**
	 * this function create a new question
	 * 
	 * @param  int		$idTest 	indicates the test selected
	 * @param  string	$back_test	indicates the return url
	 * @return nothing
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function create( $idTest, $back_test ) {
		$lang =& DoceboLanguage::createInstance('test');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_test));
		
		//manage number of answer
		$num_answer = importVar('num_answer', true, 2);
		if(isset($_POST['more_answer'])) ++$num_answer;
		if(isset($_POST['less_answer']) && ($num_answer > 1) ) --$num_answer;
		
		if(isset($_POST['add_question'])) {
			//insert the new question
			$ins_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testquest 
			( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle ) VALUES
			( 	'".(int)$idTest."', 
				'".(int)$_POST['idCategory']."', 
				'".$this->getQuestionType()."', 
				'".$_POST['title_quest']."',
				'".(int)$_POST['difficult']."',
				'".(int)$_POST['time_assigned']."', 
				'".$this->_getNextSequence($idTest)."', 
				'".$this->_getPageNumber($idTest)."',
				'".( isset($_POST['shuffle']) ? 1 : 0 )."' ) ";
			if(!sql_query($ins_query)) {
				
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;idTest='.$idTest.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			//find id of auto_increment colum
			list($idQuest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			if(!$idQuest) {
				errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
			}
			//insert answer
			for($i = 0; $i < $num_answer; $i++) {
				//insert answer
				$ins_answer_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer 
				( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
				( 	'".$idQuest."', 
					'".( isset($_POST['is_correct'][$i]) ? 1 : 0 )."', 
					'".$_POST['answer'][$i]."', 
					'".$_POST['comment'][$i]."', 
					'".$this->_checkScore($_POST['score_correct'][$i])."', 
					'".$this->_checkScore($_POST['score_incorrect'][$i])."') ";
				if(!sql_query($ins_answer_query)) {
					
					errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
				}
			}	
			//back to question list
			Util::jump_to( ''.$back_test);
		}
		
		//insert form
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
			.Form::getHidden('num_answer', 'num_answer', $num_answer)
		
			.Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest', 
				( isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : '' ) ), 'content');
		if (count($categories) > 1)
			$GLOBALS['page']->add(Form::getDropdown( $lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories,
				( isset($_POST['idCategory']) ? $_POST['idCategory'] : '' )), 'content');
		$GLOBALS['page']->add(Form::getDropdown( $lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult,
				( isset($_POST['difficult']) ? $_POST['difficult'] : 3 ))
			.Form::getCheckbox($lang->def('_TEST_QUEST_SHUFFLE'), 'shuffle', 'shuffle', '1', ( isset($_POST['shuffle']) ? 1 : 0 ) )
			.Form::getTextfield( $lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5, 
				( isset($_POST['time_assigned']) ? $_POST['time_assigned'] : '00000' ), $lang->def('_TEST_QUEST_TIMEASS'),
				$lang->def('_SECONDS') )
			.'<div class="nofloat"></div><br />'
			.'<table class="test_answer" cellspacing="0" summary="'.$lang->def('_TEST_ANSWER').'">'."\n"
			.'<caption>'.$lang->def('_TEST_ANSWER').'</caption>'."\n"
			.'<tr>'
			.'<th class="image">'.$lang->def('_TEST_CORRECT').'</th>'
			.'<th>'.$lang->def('_TEST_TEXT_ANSWER').'</th>'
			.'<th>'.$lang->def('_COMMENTS').'</th>'
			.'<th colspan="2">'.$lang->def('_SCORE').'</th>'
			.'</tr>'."\n", 'content');
		for($i = 0; $i < $num_answer; $i++) {
			$this->_lineAnswer($i);
		}
		$GLOBALS['page']->add('</table>'
			.Form::getButton( 'more_answer', 'more_answer', $lang->def('_TEST_ADD_ONE_ANSWER'), 'button_nowh' ), 'content');
		if($num_answer > 1) 	$GLOBALS['page']->add(Form::getButton( 'less_answer', 'less_answer', $lang->def('_TEST_SUB_ONE_ANSWER'), 'button_nowh' ), 'content');
		$GLOBALS['page']->add(
			'' // Form::getButton( 'select_from_libraries', 'select_from_libraries', $lang->def('_TEST_SEL_LIBRARIES'), 'button_nowh' )
			.Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>', 'content');
	}
	
	/**
	 * this function modify a question
	 * 
	 * @param  string	$back_test	indicates the return url
	 * @return nothing
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function edit( $back_test ) {
		$lang =& DoceboLanguage::createInstance('test');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_test));
		
		//manage number of answer
		$num_answer = importVar('num_answer', true, 2);
		if(isset($_POST['more_answer'])) ++$num_answer;
		if(isset($_POST['less_answer']) && ($num_answer > 1) ) --$num_answer;
		
		if(isset($_POST['add_question'])) {
			//update question
			$ins_query = "
			UPDATE ".$GLOBALS['prefix_lms']."_testquest
			SET idCategory = '".(int)$_POST['idCategory']."', 
				type_quest = '".$this->getQuestionType()."', 
				title_quest = '".$_POST['title_quest']."', 
				difficult = '".(int)$_POST['difficult']."', 
				time_assigned = '".(int)$_POST['time_assigned']."',
				shuffle = '".(isset($_POST['shuffle']) ? 1 : 0)."'
			WHERE idQuest = '".(int)$this->id."'";
			if(!sql_query($ins_query)) {
				
				$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;idQuest='.$this->id.'&amp;back_test='.$url_encode, $lang->def('_BACK'))), 'content');
			}
			//update answer
			if( !isset($_POST['is_correct']) ) $_POST['is_correct'] = -1;
			
			//find saved answer
			$re_answer = sql_query("
			SELECT idAnswer 
			FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
			WHERE idQuest = '".(int)$this->id."'");
			while(list($id_a) = sql_fetch_row($re_answer)) $existent_answer[$id_a] = 1;
			
			for($i = 0; $i < $num_answer; $i++) {
				//scannig answer
				if( isset($_POST['answer_id'][$i]) ) {
					//must update
					$idAnswer = $_POST['answer_id'][$i];
					if(isset($existent_answer[$idAnswer])) unset($existent_answer[$idAnswer]);
					
					$upd_ans_query = "
					UPDATE ".$GLOBALS['prefix_lms']."_testquestanswer 
					SET is_correct = '".( isset($_POST['is_correct'][$i]) ? 1 : 0 )."',
						answer = '".$_POST['answer'][$i]."',
						comment = '".$_POST['comment'][$i]."',
						score_correct = '".$this->_checkScore($_POST['score_correct'][$i])."', 
						score_incorrect = '".$this->_checkScore($_POST['score_incorrect'][$i])."'
					WHERE idAnswer = '".(int)$idAnswer."'";
					if(!sql_query($upd_ans_query)) {
						$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');
					}
				}
				else {
					//insert new answer
					$ins_answer_query = "
					INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer 
					( idQuest, is_correct, 
						answer, comment,
						score_correct, score_incorrect ) VALUES
					( '".$this->id."', '".( isset($_POST['is_correct'][$i]) ? 1 : 0 )."', 
						'".$_POST['answer'][$i]."', '".$_POST['comment'][$i]."', 
						'".$this->_checkScore($_POST['score_correct'][$i])."', 
						'".$this->_checkScore($_POST['score_incorrect'][$i])."') ";
					if(!sql_query($ins_answer_query)) {
						
						$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');
					}
				}
			}
			while(list($idA) = each($existent_answer)) {
				//i must delete these answer
				$del_answer_query = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_testquestanswer
				WHERE idQuest = '".(int)$this->id."' AND idAnswer = '".(int)$idA."'";
				if(!sql_query($del_answer_query)) {
					
					$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))), 'content');
				}
			}
			//back to question list
			Util::jump_to( ''.$back_test);
		}
		
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//writing difficult array
		$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
		
		//load data
		if(!isset($_POST['answer_id'])) {
			list($sel_cat, $quest, $sel_diff, $sel_time, $shuffle) = sql_fetch_row(sql_query("
			SELECT idCategory, title_quest, difficult, time_assigned, shuffle 
			FROM ".$GLOBALS['prefix_lms']."_testquest 
			WHERE idQuest = '".(int)$this->id."'"));
			
			$re_answer = sql_query("
			SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
			FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
			WHERE idQuest = '".(int)$this->id."'
			ORDER BY idAnswer");
			
			$i_load = 0;
			while(list(
				$_POST['answer_id'][$i_load],
				$is_correct,
				$_POST['answer'][$i_load],
				$_POST['comment'][$i_load],
				$_POST['score_correct'][$i_load],
				$_POST['score_incorrect'][$i_load] ) = sql_fetch_row($re_answer)){
				if($is_correct) $_POST['is_correct'][$i_load] = 1;
				++$i_load;
			}
			$num_answer = $i_load;
		}
		$GLOBALS['page']->add(getTitleArea($lang->def('_TEST_SECTION'), 'test')
			.'<div class="std_block">'
			.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=edit')
		
			.Form::openElementSpace()
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('idQuest', 'idQuest', $this->id)
			.Form::getHidden('back_test', 'back_test', $url_encode)
			.Form::getHidden('num_answer', 'num_answer', $num_answer)
		
			.Form::getTextarea($lang->def('_QUESTION'), 'title_quest', 'title_quest', 
			( isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : $quest ) ), 'content');
		if (count($categories) > 1)
			$GLOBALS['page']->add(Form::getDropdown( $lang->def('_TEST_QUEST_CATEGORY'), 'idCategory', 'idCategory', $categories,
				( isset($_POST['idCategory']) ? $_POST['idCategory'] : $sel_cat )), 'content');
		$GLOBALS['page']->add(Form::getDropdown( $lang->def('_DIFFICULTY'), 'difficult', 'difficult', $arr_dufficult,
			( isset($_POST['difficult']) ? $_POST['difficult'] : $sel_diff ))
			.Form::getCheckbox($lang->def('_TEST_QUEST_SHUFFLE'), 'shuffle', 'shuffle', '1', $shuffle)
			.Form::getTextfield( $lang->def('_TEST_QUEST_TIMEASS'), 'time_assigned', 'time_assigned', 5, 
			( isset($_POST['time_assigned']) ? $_POST['time_assigned'] : $sel_time ), $lang->def('_TEST_QUEST_TIMEASS'),
			$lang->def('_SECONDS') )
			.'<div class="nofloat"></div><br />'
		
			.'<table class="test_answer" cellspacing="0" summary="'.$lang->def('_TEST_ANSWER').'">'."\n"
			.'<caption>'.$lang->def('_TEST_ANSWER').'</caption>'."\n"
			.'<tr>'
			.'<th class="image">'.$lang->def('_TEST_CORRECT').'</th>'
			.'<th>'.$lang->def('_TEST_TEXT_ANSWER').'</th>'
			.'<th>'.$lang->def('_COMMENTS').'</th>'
			.'<th colspan="2">'.$lang->def('_SCORE').'</th>'
			.'</tr>'."\n", 'content');
		for($i = 0; $i < $num_answer; $i++) {
			$this->_lineModAnswer($i);
		}
		$GLOBALS['page']->add('</table>'
			.Form::getButton( 'more_answer', 'more_answer', $lang->def('_TEST_ADD_ONE_ANSWER'), 'button_nowh' ), 'content');
		if($num_answer > 1) 	$GLOBALS['page']->add(Form::getButton( 'less_answer', 'less_answer', $lang->def('_TEST_SUB_ONE_ANSWER'), 'button_nowh' ), 'content');
		$GLOBALS['page']->add('' // Form::getButton( 'select_from_libraries', 'select_from_libraries', $lang->def('_TEST_SEL_LIBRARIES'), 'button_nowh' )
			.Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>', 'content');
	}
	
	/**
	 * this function delete the question with the idQuest saved in the variable $this->id
	 * 
	 * @return bool	if the operation success return true else return false 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function del() {
		
		
		//delete answer
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
		WHERE idQuest = '".$this->id."'")) return false;
		
		//remove answer
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".$this->id."'")) {
			return false;
		}
		//remove question
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_testquest 
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
		list($sel_cat, $quest, $sel_diff, $time_ass, $sequence, $page, $shuffle) = sql_fetch_row(sql_query("
		SELECT idCategory, title_quest, difficult, time_assigned, sequence, page, shuffle 
		FROM ".$GLOBALS['prefix_lms']."_testquest 
		WHERE idQuest = '".(int)$this->id."'"));
		//insert question
		$ins_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_testquest 
		( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle ) VALUES 
		( 	'".(int)$new_id_test."', 
			'".(int)$sel_cat."', 
			'".$this->getQuestionType()."', 
			'".sql_escape_string($quest)."',
			'".(int)$sel_diff."', 
			'".$time_ass."',
			'".(int)$sequence."',
			'".(int)$page."', 
			'".(int)$shuffle."' ) ";
		if(!sql_query($ins_query)) return false;
		//find id of auto_increment colum
		list($new_id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if(!$new_id_quest) return false;
		
		//retriving new answer
		$re_answer = sql_query("
		SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		while(list($idAnswer, $is_correct, $answer, $comment, $score_c, $score_inc) = sql_fetch_row($re_answer)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer 
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
		
		list($id_quest, $title_quest, $shuffle) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest, shuffle 
		FROM ".$GLOBALS['prefix_lms']."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		$query_answer = "
		SELECT idAnswer, answer 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'";
		if($shuffle_answer || $shuffle) $query_answer .= " ORDER BY RAND()";
		else $query_answer .= " ORDER BY idAnswer";
		$re_answer = sql_query($query_answer);
		
		$find_prev = false;
		$id_answer_do = 0;
		if($id_track != 0) {
			
			//recover previous information
			$recover_answer = "
			SELECT idAnswer 
			FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
			WHERE idQuest = '".(int)$this->id."' AND 
				idTrack = '".(int)$id_track."' AND ( user_answer = 1 OR user_answer IS NULL ) AND number_time =  ".$number_time;
			$re_answer_do = sql_query($recover_answer);
			if(sql_num_rows($re_answer_do)) {
				
				//find previous answer
				$find_prev = true;
				while(list($id_a) = sql_fetch_row($re_answer_do)) $answer_do[$id_a] = 1;
			}
		}
		
		$content = '<div class="play_question">'
            .'<div>'.$lang->def('_QUEST_'.strtoupper($this->getQuestionType())).'</div>'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">';
		while(list($id_answer, $answer) = sql_fetch_row($re_answer)){
			
			// $content .= '<input type="checkbox" id="quest_'.$id_quest.'_'.$id_answer.'" '
			// 	.'name="quest['.$id_quest.']['.$id_answer.']" value="1"'
			// 	.( ($find_prev && isset($answer_do[$id_answer])) ? ' checked="checked"' : '' )
			// 	.( $find_prev && $freeze ? ' disabled="disabled"' : '' ).' /> '
			// 	.'<label class="text_answer" for="quest_'.$id_quest.'_'.$id_answer.'">'.$answer.'</label><br />';

			// wrapping checkbox: mod by Andrea Fiadone - 10/03/2016
			$content .= '<div class="answer-item"><div class="input-wrapper input-wrapper-checkbox">'
				.'<input type="checkbox" id="quest_'.$id_quest.'_'.$id_answer.'" '
				.'name="quest['.$id_quest.']['.$id_answer.']" value="1"'
				.( ($find_prev && isset($answer_do[$id_answer])) ? ' checked="checked"' : '' )
				.( $find_prev && $freeze ? ' disabled="disabled"' : '' ).' /> '
				.'</div>'
				.'<label class="text_answer" for="quest_'.$id_quest.'_'.$id_answer.'">'.$answer.'</label><br /></div>';

		}
		$content .= '</div>'
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
	function storeAnswer(Track_Test $trackTest, &$source, $can_overwrite = false ) {

		$result = true;

		if ($this->userDoAnswer($trackTest->idTrack) && !$trackTest->getTestObj()->isRetainAnswersHistory()) {
			if(!$can_overwrite) return true;
			if(!$this->deleteAnswer($trackTest->idTrack)) return false;
		}
		
		$re_answer = sql_query("
		SELECT idAnswer, is_correct, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'");
		while(list($id_answer, $is_correct, $score_corr, $score_incorr) = sql_fetch_row($re_answer)) {
			
			if(isset($source['quest'][$this->id][$id_answer])) {
				
				//answer checked by the user 
				$track_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
				VALUES (
					'".(int)$trackTest->idTrack."',
					'".(int)$this->id."', 
					'".(int)$id_answer."', 
					'".( $is_correct ? $score_corr : -$score_incorr )."', 
					'',
					1,
					'".(int)($trackTest->getNumberOfAttempt()+1)."')";
				$result &= sql_query($track_query);
			} elseif($is_correct && ($score_incorr != 0)) {
				
				//answer correct with penality but not checked by the user 
				$track_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
				VALUES (
					'".(int)$trackTest->idTrack."',
					'".(int)$this->id."', 
					'".(int)$id_answer."', 
					'".-$score_incorr."', 
					'',
					0,
					'".(int)($trackTest->getNumberOfAttempt()+1)."')";
				$result &= sql_query($track_query);
			} elseif(!$is_correct && ($score_corr != 0)) {
			//answer correct with penality but not checked by the user 
				$track_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, user_answer, number_time )
				VALUES (
					'".(int)$trackTest->idTrack."',
					'".(int)$this->id."', 
					'".(int)$id_answer."', 
					'".$score_corr."', 
					'',
					0,
					'".(int)($trackTest->getNumberOfAttempt()+1)."')";
				$result &= sql_query($track_query);
			}
		}
		return $result;
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
	function _updateAnswer( $id_track, &$source ) {
		
		
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
		DELETE FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
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

		// extraction of the question
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT idQuest, title_quest 
		FROM ".$GLOBALS['prefix_lms']."_testquest 
		WHERE idQuest = '".$this->id."'"));

		// selection of the right answers to the question
		$query_answer = "
		SELECT idAnswer, is_correct, answer, comment 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer";
		$re_answer = sql_query($query_answer);


		// the user_answer = NULL is for backward compatibility
		$recover_answer = "
		SELECT idAnswer 
		FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
		WHERE idQuest = ".(int)$this->id." AND 
			idTrack = ".(int)$id_track." AND ( user_answer = 1 OR user_answer = NULL ) ";
        if ($number_time != null){
            $recover_answer .= " AND number_time = ".$number_time;
        } else {
			$recover_answer .= " AND number_time = ( SELECT MAX(number_time) FROM learning_testtrack_answer WHERE idQuest = ".(int)$this->id." AND idTrack = ".(int)$id_track." )";
		}

        
        //**  recorver status test ** #11961 - Errata visualizzazione risposte corrette nei test
        $sql = "select status from ".$GLOBALS['prefix_lms']."_commontrack where idUser=".Docebo::user()->getIdSt()." and idTrack=".$id_track;
        list($status_test) = sql_fetch_row(sql_query($sql));
                
        
        
        
		$re_answer_do = sql_query($recover_answer);
		while(list($id_a) = sql_fetch_row($re_answer_do)) {
			$answer_do[$id_a] = 1;
		}
		
		$quest = 
			'<div class="play_question">'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">';
		while(list($id_answer, $is_correct, $answer, $comm) = sql_fetch_row($re_answer)){
			
			if(isset($answer_do[$id_answer] )) {

				$quest .= '<img src="'.getPathImage().'standard/dot_sel.png" title="'.$lang->def('_TEST_ANSWER_CHECK').'" '
						.'alt="'.$lang->def('_TEST_ANSWER_CHECK').'" />&nbsp;'
					.$answer.'&nbsp;';
				if($is_correct) {
					$quest .= '<span class="test_answer_correct">'.$lang->def('_TEST_CORRECT').'</span>';
					$comment .= '<br />'.$comm.'<br />';
				} else {
					$quest .= '<span class="test_answer_incorrect">'.$lang->def('_TEST_INCORRECT').'</span>';
					$comment .= '<br />'.$answer.' <span class="text_bold">'.$lang->def('_TEST_NOT_MC_THECORRECT').' : </span>'
							.$comm.'<br />';
				}
				$quest .= '<br />';


			} else{
				
				//if($is_correct && $show_solution) {
                 if(($status_test=='passed' && $show_solution==2 && $is_correct) || ($show_solution==1 && $is_correct)) {
					$com_is_correct .= '<span class="text_bold">'.$lang->def('_TEST_NOT_THECORRECT').' : </span>'.$answer.'<br />';
				}
				$quest .= '<img src="'.getPathImage().'standard/dot_uns.png" title="'.$lang->def('_TEST_ANSWER_NOTCHECK').'" '
					.'alt="'.$lang->def('_TEST_ANSWER_NOTCHECK').'" />&nbsp;'
					.$answer.'<br />';
			}
		}
		$quest .= '</div>'
			.'</div>';
		
		return array(	'quest' 	=> $quest, 
						'score'		=> $this->userScore($id_track, $number_time),
						'comment'	=> $com_is_correct.$comment );
	}
	
	function userScore( $id_track, $number_time = null ) {

		$score = 0;
		$query = "SELECT SUM(score_assigned)
		FROM ".$GLOBALS['prefix_lms']."_testtrack_answer
		WHERE idQuest = '".(int)$this->id."'
		AND idTrack = '".(int)$id_track."'";
		if ($number_time != null){
			$query .= " AND number_time = ".$number_time;
		}
		$query .= " GROUP BY number_time ORDER BY number_time DESC LIMIT 1";
		$re_answer = sql_query($query);
		if(!sql_num_rows($re_answer)) return $score;
		while(list($score_assigned) = sql_fetch_row($re_answer)) {
			$score = round($score + $score_assigned, 2);
		}
		return $score;
	}
	
}

?>
