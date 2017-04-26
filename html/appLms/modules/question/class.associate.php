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

class Associate_Question extends Question {
	
	/**
	 * class constructor
	 * 
	 * @param int	the unique database identifer of a question 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function Associate_Question( $id ) {
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
		return 'associate';
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
			.'<td class="access-only valign_top align_center">'
				.'<label for="elem_a_'.$i.'">'.$lang->def('_TEST_QUEST_ELEM').': '.($i + 1).'</label>'
			.'</td>'
			.'<td class="image">'
				//.Form::getTextarea('', 'elem_a_'.$i, 'elem_a['.$i.']', ( isset($_POST['elem_a'][$i]) ? stripslashes($_POST['elem_a'][$i]) : '' ),false,'','form_line_l','floating','textarea',true)
				
				
			.loadHtmlEditor('',
							'elem_a_'.$i, 
							'elem_a['.$i.']', 
							( isset($_POST['elem_a'][$i]) ? stripslashes($_POST['elem_a'][$i]) : ''),
							false, 
							'', 
							true)
				
				//.'<textarea class="test_area_answer" id="elem_a_'.$i.'" name="elem_a['.$i.']" cols="19" rows="3">'
				//.( isset($_POST['elem_a'][$i]) ? stripslashes($_POST['elem_a'][$i]) : '' ) //$lang->def('_QUEST_ANSWER')
				//.'</textarea>'
			.'</td>'
			.'<td class="access-only valign_top align_center">'
				.'<label for="elem_b_'.$i.'">'.$lang->def('_TEST_QUEST_ELEM').': '.($i + 1).'</label>'
			.'</td>'
			.'<td class="image">'

			.loadHtmlEditor('',
							'elem_b_'.$i, 
							'elem_b['.$i.']', 
							( isset($_POST['elem_b'][$i]) ? stripslashes($_POST['elem_b'][$i]) : ''),
							false, 
							'', 
							true)

				//.Form::getTextarea('', 'elem_b_'.$i, 'elem_b['.$i.']', ( isset($_POST['elem_b'][$i]) ? stripslashes($_POST['elem_b'][$i]) : '' ),false,'','form_line_l','floating','textarea',true)
				//.'<textarea class="test_area_answer" id="elem_b_'.$i.'" name="elem_b['.$i.']" cols="19" rows="3">'
				//.( isset($_POST['elem_b'][$i]) ? stripslashes($_POST['elem_b'][$i]) : '' ) //$lang->def('_QUEST_ANSWER')
				//.'</textarea>'
			.'</td>'
			.'</tr>'."\n", 'content');
	}
	
	/**
	 * this function write a gui for answer insertion
	 * 
	 * @param  int	$i	indicate the line number
	 * @return nothing
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _lineAssociateAnswer($i, $content_field_b) {
		$lang =& DoceboLanguage::createInstance('test');
		
		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td rowspan="2">', 'content');
		if(isset($_POST['elem_a'][$i])) {
			
			$GLOBALS['page']->add('<label for="associate_b_'.$i.'">'.($i + 1).') '.stripslashes($_POST['elem_a'][$i]).'</label>'
				.'<input type="hidden" name="elem_a['.$i.']" value="'.base64_encode($_POST['elem_a'][$i]).'" />', 'content');
		}
		$GLOBALS['page']->add('</td>'
			.'<td rowspan="2">', 'content');
		if(isset($_POST['elem_b'][$i])) {
			$GLOBALS['page']->add('<input type="hidden" name="elem_b['.$i.']" value="'.base64_encode($_POST['elem_b'][$i]).'" />'
				.'<select id="associate_b_'.$i.'" name="associate_b['.$i.']">'
				.$content_field_b
				.'</select>', 'content');
		}
		$GLOBALS['page']->add('</td>'
			.'<td rowspan="2" class="image">'
			//comment
			.'<label for="comment_'.$i.'">'.$lang->def('_COMMENTS').'</label>'
			.'<textarea class="test_comment" id="comment_'.$i.'" name="comment['.$i.']" cols="14" rows="3">'
			.'</textarea>'
			.'</td>'
			.'<td class="test_ifcorrect">'
			.'<label for="score_correct_'.$i.'">'.$lang->def('_TEST_IFCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score correct
			.'<input type="text" class="test_point" id="score_correct_'.$i.'" name="score_correct['.$i.']" alt="'.$lang->def('_TEST_IFCORRECT').'" size="5" value="0.0" />'
			.'</td>'
			.'</tr>'."\n"
			.'<tr class="line_answer">'
			.'<td class="test_ifcorrect">'
			.'<label for="score_incorrect_'.$i.'">'.$lang->def('_TEST_IFINCORRECT').'</label>'
			.'</td>'
			.'<td class="align_right">'
			//score incorrect
			.'- <input type="text" class="test_point" id="score_incorrect_'.$i.'" name="score_incorrect['.$i.']" alt="'.$lang->def('_TEST_IFINCORRECT').'" size="5" value="0.0" />'
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
			.'<td class="access-only valign_top align_center">'
				.'<input type="hidden" name="elem_a_id['.$i.']" value="'
					.( isset($_POST['elem_a_id'][$i]) ? $_POST['elem_a_id'][$i] : 0).'">'
				.'<label for="elem_a_'.$i.'">'.$lang->def('_TEST_QUEST_ELEM').': '.($i + 1).'</label>'
			.'</td>'
			.'<td class="image">'
				//.Form::getTextarea('', 'elem_a_'.$i, 'elem_a['.$i.']', ( isset($_POST['elem_a'][$i]) ? stripslashes($_POST['elem_a'][$i]) : '' ),false,'','form_line_l','floating','textarea',true)
			
			.loadHtmlEditor('',
							'elem_a_'.$i, 
							'elem_a['.$i.']', 
							( isset($_POST['elem_a'][$i]) ? stripslashes($_POST['elem_a'][$i]) : ''),
							false, 
							'', 
							true)
							
				//.'<textarea class="test_area_answer" id="elem_a_'.$i.'" name="elem_a['.$i.']" cols="19" rows="3">'
				//.( isset($_POST['elem_a'][$i]) ? stripslashes($_POST['elem_a'][$i]) : '' )
				//.'</textarea>'
			.'</td>'
			.'<td class="access-only valign_top align_center">'
				.'<input type="hidden" name="elem_b_id['.$i.']" value="'
					.( isset($_POST['elem_b_id'][$i]) ? $_POST['elem_b_id'][$i] : 0).'">'
				.'<label for="elem_b_'.$i.'">'.$lang->def('_TEST_QUEST_ELEM').': '.($i + 1).'</label>'
			.'</td>'
			.'<td class="image">' 
				//.Form::getTextarea('', 'elem_b_'.$i, 'elem_b['.$i.']', ( isset($_POST['elem_b'][$i]) ? stripslashes($_POST['elem_b'][$i]) : '' ),false,'','form_line_l','floating','textarea',true)
				
			/*.loadHtmlEditor('',
							'elem_b_'.$i, 
							'elem_b['.$i.']', 
							( isset($_POST['elem_b'][$i]) ? stripslashes($_POST['elem_b'][$i]) : ''),
							false, 
							'', 
							true)*/
				.'<textarea class="test_area_answer" id="elem_b_'.$i.'" name="elem_b['.$i.']" cols="19" rows="3">'
				.( isset($_POST['elem_b'][$i]) ? stripslashes($_POST['elem_b'][$i]) : '' )
				.'</textarea>'    
			.'</td>'
			.'</tr>'."\n", 'content');   
	}
	
	/**
	 * this function write a gui for answer insertion
	 * 
	 * @param  int	$i	indicate the line number
	 * @return nothing
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _lineModAssociateAnswer($i, $content_field_b) {
		$lang =& DoceboLanguage::createInstance('test');
		
		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td rowspan="2">', 'content');
		if(isset($_POST['elem_a_id'][$i])) {
			$GLOBALS['page']->add('<input type="hidden" id="elem_a_id_'.$i.'" name="elem_a_id['.$i.']" value="'.$_POST['elem_a_id'][$i].'" />', 'content');
		}
		if(isset($_POST['elem_a'][$i])) {
			
			$GLOBALS['page']->add('<input type="hidden" name="elem_a['.$i.']" value="'.base64_encode($_POST['elem_a'][$i]).'" />'
				.'<label for="associate_b_'.$i.'">'.($i + 1).') '.stripslashes($_POST['elem_a'][$i]).'</label>', 'content');
		}
		$GLOBALS['page']->add('</td>'
			.'<td rowspan="2">', 'content');
		if(isset($_POST['elem_b_id'][$i])) {
			$GLOBALS['page']->add('<input type="hidden" id="elem_b_id_'.$i.'" name="elem_b_id['.$i.']" value="'.$_POST['elem_b_id'][$i].'" />', 'content');
		}
		if(isset($_POST['elem_b'][$i])) {
			$GLOBALS['page']->add('<input type="hidden" name="elem_b['.$i.']" value="'.base64_encode($_POST['elem_b'][$i]).'" />'
				.'<select id="associate_b_'.$i.'" name="associate_b['.$i.']">'
				.$content_field_b
				.'</select>', 'content');
		}
		$GLOBALS['page']->add('</td>'
			.'<td rowspan="2" class="image">'
			//comment
			.'<label for="comment_'.$i.'">'.$lang->def('_COMMENTS').'</label>'
			.'<textarea class="test_comment" id="comment_'.$i.'" name="comment['.$i.']" cols="14" rows="3">'
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
			//insert second group
			$num_group = count($_POST['elem_b']);
			$id_assigned = array();
			for($j = 0; $j < $num_group; $j++) {
				
				$content = base64_decode($_POST['elem_b'][$j]);
				if($content != '') {
					$ins_answer_query = "
					INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
					( idQuest, answer ) VALUES
					( 	'".$_POST['idQuest']."', 
						'".$content."') ";
					if(!sql_query($ins_answer_query)) {
						
						errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
					}
					list($id_a) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
					$id_assigned[$j] = $id_a;
				}
			}
			//insert answer of first group
			for($i = 0; $i < $num_answer; $i++) {
				//insert answer
				$elem_asso = $_POST['associate_b'][$i];
				$ins_answer_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer 
				( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
				( 	'".$_POST['idQuest']."', 
					'".$id_assigned[$elem_asso]."', 
					'".base64_decode($_POST['elem_a'][$i])."', 
					'".$_POST['comment'][$i]."', 
					'".$this->_checkScore($_POST['score_correct'][$i])."', 
					'".$this->_checkScore($_POST['score_incorrect'][$i])."') ";
				if(!sql_query($ins_answer_query)) {
					
					errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
				}
			}
			
			//back to question list
			Util::jump_to( ''.$back_test);
		} elseif(isset($_POST['do_association'])) {
			
			//----------------------------------------------------------------------------------------
			//insert the new question
			$ins_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testquest 
			( idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page, shuffle ) VALUES
			( 	'".$idTest."', 
				'".(int)$_POST['idCategory']."', 
				'".$this->getQuestionType()."', 
				'".$_POST['title_quest']."',
				'".(int)$_POST['difficult']."', 
				'".(int)$_POST['time_assigned']."', 
				'".(int)$this->_getNextSequence($idTest)."', 
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
			//save groups a and b
			$content_a = $content_b = '';
			for($i = 0; $i < $num_answer; $i++) {
				if($_POST['elem_a'][$i] != '') {
					$content_a .= '<option value="'.$i.'">'.stripslashes($_POST['elem_a'][$i]).'</option>';
				}
				if($_POST['elem_b'][$i] != '') {
					$content_b .= '<option value="'.$i.'">'.stripslashes($_POST['elem_b'][$i]).'</option>';
				}
			}
			
			$GLOBALS['page']->add(
				getTitleArea($lang->def('_TEST_SECTION'), 'test')
				.'<div class="std_block">'
				.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))
				.'<div class="title_big">'
				.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
				.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
				.'</div><br />'
				.Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=create') , 'content');
			
			$GLOBALS['page']->add(
				Form::openElementSpace()
				.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
				.Form::getHidden('idTest', 'idTest', $idTest)
				.Form::getHidden('back_test', 'back_test', $url_encode)
				.Form::getHidden('num_answer', 'num_answer', $num_answer)
				.Form::getHidden('idQuest', 'idQuest', $idQuest)
				.'<div class="nofloat"></div><br />'
				
				.'<table class="test_answer" cellspacing="0" summary="'.$lang->def('_TEST_ASSOCIATE').'">'."\n"
				.'<caption>'.$lang->def('_TEST_ASSOCIATE').'</caption>'."\n"
				.'<tr>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_A').'</th>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_B').'</th>'
					.'<th>'.$lang->def('_COMMENTS').'</th>'
					.'<th colspan="2">'.$lang->def('_SCORE').'</th>'
				.'</tr>'."\n", 'content');
			for($i = 0; $i < $num_answer; $i++) {
				$this->_lineAssociateAnswer($i, $content_b);
			}
			$GLOBALS['page']->add(
				'</table>'
				.Form::closeElementSpace()
				
				.Form::openButtonSpace()
				.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
				.Form::closeButtonSpace()
				.Form::closeForm()
				.'</div>', 'content');
			
		} else {
			
			//insert form
			
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
			
			//writing difficult array
			$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
			
			
			
			$GLOBALS['page']->add(
				getTitleArea($lang->def('_TEST_SECTION'), 'test')
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
					.'<th class="access-only">'.$lang->def('_TEST_QUEST_ELEM_NUM').'</th>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_A').'</th>'
					.'<th class="access-only ">'.$lang->def('_TEST_QUEST_ELEM_NUM').'</th>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_B').'</th>'
				.'</tr>'."\n", 'content');
			for($i = 0; $i < $num_answer; $i++) {
				$this->_lineAnswer($i);
			}
			$GLOBALS['page']->add(
				'</table>'
				.Form::getButton( 'more_answer', 'more_answer', $lang->def('_TEST_ADD_ONE_ANSWER'), 'button_nowh' ), 'content');
			if($num_answer > 1) $GLOBALS['page']->add(Form::getButton( 'less_answer', 'less_answer', $lang->def('_TEST_SUB_ONE_ANSWER'), 'button_nowh' ), 'content');
			$GLOBALS['page']->add(
				'' // Form::getButton( 'select_from_libraries', 'select_from_libraries', $lang->def('_TEST_SEL_LIBRARIES'), 'button_nowh' )
				.Form::closeElementSpace()
				.Form::openButtonSpace()
				.Form::getButton('do_association', 'do_association', $lang->def('_TEST_QUEST_SEL_ASSOCIATION'))
				.Form::closeButtonSpace()
				.Form::closeForm()
				.'</div>', 'content');
		}
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
			
			//save second group-----------------------------------------------
			
			$correct_answer = array();
			$existent_associate = array();
			
			$re_answer_asso = sql_query("
			SELECT idAnswer
			FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
			WHERE idQuest = '".(int)$this->id."'");
			while(list($id_aa) = sql_fetch_row($re_answer_asso)) $existent_associate[$id_aa] = 1;
			
			for($j = 0; $j < $num_answer; $j++) {
				
				$content = base64_decode($_POST['elem_b'][$j]);
				if($content != '') {
					
					if( isset($_POST['elem_b_id'][$j]) && ($_POST['elem_b_id'][$j] != 0) ) {
						
						//must update
						$id_old_a = $_POST['elem_b_id'][$j];
						if(isset($existent_associate[$id_old_a])) unset($existent_associate[$id_old_a]);
						
						$upd_ans_query = "
						UPDATE ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
						SET answer = '".$content."' 
						WHERE idAnswer = '".(int)$id_old_a."'";
						if(!sql_query($upd_ans_query)) {
							
							errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
						}
						$id_assigned[$j] = $id_old_a;
					} else {
						//insert new answer
						$ins_answer_query = "
						INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
						( idQuest, answer ) VALUES 
						( 	'".(int)$this->id."', 
							'".$content."' ) ";
						if(!sql_query($ins_answer_query)) {
							
							errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
						}
						$id_assigned[$j] = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
					}
				}
			}
			
			while(list($id_aa) = each($existent_associate)) {
				//i must delete these answer
				$del_answer_query = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate
				WHERE idQuest = '".(int)$this->id."' AND idAnswer = '".(int)$id_aa."'";
				if(!sql_query($del_answer_query)) {
					
					errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
				}
			}
			
			//first group-----------------------------------------------------
			//find saved answer
			$re_answer = sql_query("
			SELECT idAnswer
			FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
			WHERE idQuest = '".(int)$this->id."'");
			while(list($id_a) = sql_fetch_row($re_answer)) $existent_answer[$id_a] = 1;
			
			for($i = 0; $i < $num_answer; $i++) {
				//scannig answer
				$content = base64_decode($_POST['elem_a'][$i]);
				$elem_asso = $_POST['associate_b'][$i];
				
				if($content != '') {
					
					if( isset($_POST['elem_a_id'][$i]) && ($_POST['elem_a_id'][$i] != 0) ) {
						//must update
						$idAnswer = $_POST['elem_a_id'][$i];
						if(isset($existent_answer[$idAnswer])) unset($existent_answer[$idAnswer]);
						
						$upd_ans_query = "
						UPDATE ".$GLOBALS['prefix_lms']."_testquestanswer 
						SET is_correct = '".$id_assigned[$elem_asso]."',
							answer = '".$content."',
							comment = '".$_POST['comment'][$i]."',
							score_correct = '".$this->_checkScore($_POST['score_correct'][$i])."', 
							score_incorrect = '".$this->_checkScore($_POST['score_incorrect'][$i])."'
						WHERE idAnswer = '".(int)$idAnswer."'";
						if(!sql_query($upd_ans_query)) {
							errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
						}
					} else {
						//insert new answer
						$ins_answer_query = "
						INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer 
						( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
						( 	'".$this->id."', 
							'".$id_assigned[$elem_asso]."', 
							'".$content."', 
							'".$_POST['comment'][$i]."', 
							'".$this->_checkScore($_POST['score_correct'][$i])."', 
							'".$this->_checkScore($_POST['score_incorrect'][$i])."') ";
						if(!sql_query($ins_answer_query)) {
							
							errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
						}
					}
				}
			}
			while(list($idA) = each($existent_answer)) {
				//i must delete these answer
				
				$del_answer_query = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_testquestanswer
				WHERE idQuest = '".(int)$this->id."' AND idAnswer = '".(int)$idA."'";
				if(!sql_query($del_answer_query)) {
					
					errorCommunication($lang->def('_OPERATION_FAILURE').getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK')));
				}
			}
			//back to question list
			Util::jump_to( ''.$back_test);
		} elseif(isset($_POST['do_association'])) {
			
			//----------------------------------------------------------------------------------------
			//insert the new question
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
				
				errorCommunication($lang->def('_OPERATION_FAILURE')
					.getBackUi('index.php?modname=question&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;idQuest='.$this->id.'&amp;back_test='.$url_encode, $lang->def('_BACK')));
			}
			//save groups a and b
			$content_a = $content_b = '';
			for($i = 0; $i < $num_answer; $i++) {
				if($_POST['elem_a'][$i] != '') {
					$content_a .= '<option value="'.$i.'">'.stripslashes($_POST['elem_a'][$i]).'</option>';
				}
				if($_POST['elem_b'][$i] != '') {
					$content_b .= '<option value="'.$i.'">'.stripslashes($_POST['elem_b'][$i]).'</option>';
				}
			}
			//load comment and scores
			$re_answer = sql_query("
			SELECT comment, score_correct, score_incorrect 
			FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
			WHERE idQuest = '".(int)$this->id."'
			ORDER BY idAnswer");
			
			$i_load = 0;
			while(list($_POST['comment'][$i_load],
				$_POST['score_correct'][$i_load],
				$_POST['score_incorrect'][$i_load] ) = sql_fetch_row($re_answer)){
				++$i_load;
			}
				
			$GLOBALS['page']->add(
				getTitleArea($lang->def('_TEST_SECTION'), 'test')
				.'<div class="std_block">'
				.getBackUi(Util::str_replace_once('&', '&amp;', $back_test), $lang->def('_BACK'))
				
				.'<div class="title_big">'
				.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
				.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
				.'</div><br />'
				.Form::openForm('form_add_quest', 'index.php?modname=question&amp;op=edit')
				
				.Form::openElementSpace()
				.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
				.Form::getHidden('back_test', 'back_test', $url_encode)
				.Form::getHidden('num_answer', 'num_answer', $num_answer)
				.Form::getHidden('idQuest', 'idQuest', $this->id)
				.'<div class="nofloat"></div><br />'
				
				.'<table class="test_answer" cellspacing="0" summary="'.$lang->def('_TEST_ASSOCIATE').'">'."\n"
				.'<caption>'.$lang->def('_TEST_ASSOCIATE').'</caption>'."\n"
				.'<tr>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_A').'</th>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_B').'</th>'
					.'<th>'.$lang->def('_COMMENTS').'</th>'
					.'<th colspan="2">'.$lang->def('_SCORE').'</th>'
				.'</tr>'."\n", 'content');
			for($i = 0; $i < $num_answer; $i++) {
				$this->_lineModAssociateAnswer($i, $content_b);
			}
			$GLOBALS['page']->add(
				'</table>'
				.Form::closeElementSpace()
				
				.Form::openButtonSpace()
				.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
				.Form::closeButtonSpace()
				.Form::closeForm()
				.'</div>', 'content');
			
		} else {
			//load data
			if(!isset($_POST['elem_a_id'])) {
				list($sel_cat, $quest, $sel_diff, $sel_time, $shuffle ) = sql_fetch_row(sql_query("
				SELECT idCategory, title_quest, difficult, time_assigned, shuffle 
				FROM ".$GLOBALS['prefix_lms']."_testquest 
				WHERE idQuest = '".(int)$this->id."'"));
				
				$re_answer = sql_query("
				SELECT idAnswer, answer 
				FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
				WHERE idQuest = '".(int)$this->id."'
				ORDER BY idAnswer");
				$j_load = $i_load = 0;
				while(list($_POST['elem_a_id'][$i_load], $_POST['elem_a'][$i_load]) = sql_fetch_row($re_answer)){
					++$i_load;
				}
				$re_answer_2 = sql_query("
				SELECT idAnswer, answer 
				FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
				WHERE idQuest = '".(int)$this->id."'
				ORDER BY idAnswer");
				while(list($_POST['elem_b_id'][$j_load], $_POST['elem_b'][$j_load]) = sql_fetch_row($re_answer_2)){
					++$j_load;
				}
				$num_answer = ( $i_load > $j_load ? $i_load : $j_load );
			}
			
			
			//insert form
			require_once($GLOBALS['where_lms'].'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
			//writing difficult array
			$arr_dufficult = array(5 => '5 - '.$lang->def('_VERY_HARD'), 4 => '4 - '.$lang->def('_HARD'), 3 => '3 - '.$lang->def('_DIFFICULT_MEDIUM'), 2 => '2 - '.$lang->def('_DIFFICULT_EASY'), 1 => '1 - '.$lang->def('_DIFFICULT_VERYEASY'));
			
			
			$GLOBALS['page']->add(
				getTitleArea($lang->def('_TEST_SECTION'), 'test')
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
					.'<th class="access-only">'.$lang->def('_TEST_QUEST_ELEM_NUM').'</th>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_A').'</th>'
					.'<th class="access-only">'.$lang->def('_TEST_QUEST_ELEM_NUM').'</th>'
					.'<th>'.$lang->def('_TEST_QUEST_ELEMENTS_B').'</th>'
				.'</tr>'."\n", 'content');
			for($i = 0; $i < $num_answer; $i++) {
				$this->_lineModAnswer($i);
			}
			$GLOBALS['page']->add(
				'</table>'
				.Form::getButton( 'more_answer', 'more_answer', $lang->def('_TEST_ADD_ONE_ANSWER'), 'button_nowh' ), 'content');
			if($num_answer > 1) $GLOBALS['page']->add(Form::getButton( 'less_answer', 'less_answer', $lang->def('_TEST_SUB_ONE_ANSWER'), 'button_nowh' ), 'content');
			$GLOBALS['page']->add(
				'' // Form::getButton( 'select_from_libraries', 'select_from_libraries', $lang->def('_TEST_SEL_LIBRARIES'), 'button_nowh' )
				.Form::closeElementSpace()
				
				.Form::openButtonSpace()
				.Form::getButton('do_association', 'do_association', $lang->def('_TEST_QUEST_SEL_ASSOCIATION'))
				.Form::closeButtonSpace()
				.Form::closeForm()
				.'</div>', 'content');
		}
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
		DELETE FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
		WHERE idQuest = '".$this->id."'")) {
			return false;
		}
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
			'".(int)$shuffle."') ";
		if(!sql_query($ins_query)) return false;
		//find id of auto_increment colum
		list($new_id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if(!$new_id_quest) return false;
		
		//retriving new answer
		$re_answer = sql_query("
		SELECT idAnswer, answer  
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		$new_correct = array();
		while(list($idAnswer, $answer) = sql_fetch_row($re_answer)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
			( idQuest, answer ) VALUES
			( 	'".(int)$new_id_quest."',
				'".sql_escape_string($answer)."' ) ";
			if(!sql_query($ins_answer_query)) return false;
			
			list($new_correct[$idAnswer]) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		}
		
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
				'".(int)$new_correct[$is_correct]."', 
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
		
		$re_associate = sql_query("
		SELECT idAnswer, answer 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		
		$answer_do = array();
		$find_prev = false;
		if($id_track != 0) {
			
			//recover previous information
			$recover_answer = "
			SELECT idAnswer, more_info 
			FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
			WHERE idQuest = '".(int)$this->id."' AND 
				idTrack = '".(int)$id_track."' AND number_time =  ".$number_time;
			$re_answer_do = sql_query($recover_answer);
			if(sql_num_rows($re_answer_do)) {
				
				//find previous answer
				$find_prev = true;
				while(list($id_a, $id_sel) = sql_fetch_row($re_answer_do)) $answer_do[$id_a] = $id_sel;
			}
		}
		
		$option_associate = array();
		$option_associate[0]['prefix'] = '<option value="0"';
		$option_associate[0]['suffix'] = '>'.$lang->def('_NO_ANSWER').'</option>';
		while(list($id_aa, $answer_associate) = sql_fetch_row($re_associate)) {
				$option_associate[$id_aa]['prefix'] = '<option value="'.$id_aa.'"';
				$option_associate[$id_aa]['suffix'] = '>'.$answer_associate.'</option>';
		}
		
		$content = '<div class="play_question">'
			.'<div>'.$lang->def('_QUEST_'.strtoupper($this->getQuestionType())).'</div>'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">';
		while(list($id_answer, $answer) = sql_fetch_row($re_answer)){
			
			$content .= '<div class="form_line_l">'
					.'<label  for="quest_'.$id_quest.'_'.$id_answer.'">'.$answer.'</label>'
					.'&nbsp;<select class="test_as_select" id="quest_'.$id_quest.'_'.$id_answer.'" '
						.'name="quest['.$id_quest.']['.$id_answer.']"'
						.( $find_prev && $freeze ? ' disabled="disabled"' : '' ).'>';
			foreach($option_associate as $id_aa => $text ) {
				
				$content .= $text['prefix']
						.(($find_prev && $answer_do[$id_answer] == $id_aa) ? ' selected="selected"' : '')
						.$text['suffix'];
			}
			$content .= '</select></div>';
		}
		$content .=  '</div>'
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
				INSERT INTO ".$GLOBALS['prefix_lms']."_testtrack_answer ( idTrack, idQuest, idAnswer, score_assigned, more_info, number_time )
				VALUES (
					'".(int)$trackTest->idTrack."',
					'".(int)$this->id."', 
					'".(int)$id_answer."', 
					'".( $source['quest'][$this->id][$id_answer] == $is_correct ? $score_corr : -$score_incorr )."', 
					'".(int)$source['quest'][$this->id][$id_answer]."',
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
		DELETE FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
		WHERE idTrack = '".(int)$id_track."' AND 
			idQuest = '".$this->id."'");
	}
	
	/**
	 * set the maximum score for the question
	 * 
	 * @param 	double 	$score	the score that you want to set
	 * 
	 * @return 	double	return the effective point that will be assigned to the question
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function getRealMaxScore( $score ) {
		
		
		list($num_correct) = sql_fetch_row(sql_query("
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'"));
		
		if(!$num_correct) $score_assigned = 0;
		else $score_assigned = round($score / $num_correct, 2);
		
		return round($score_assigned * $num_correct, 2);
	}
	
	/**
	 * set the maximum score for the question
	 * 
	 * @param 	double 	$score	the score assigned to the question
	 * @param 	double 	$try	if true the function return the effective point that will be assigned
	 * 
	 * @return 	double	contain the new maximum score for the question, can be different from the param $score 
	 *					because can be round
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function setMaxScore( $score, $try = false  ) {
		
		
		list($num_correct) = sql_fetch_row(sql_query("
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'"));
		
		if(!$num_correct) $score_assigned = 0;
		else $score_assigned = round($score / $num_correct, 2);
		
		if($try) return round($score_assigned * $num_correct, 2);
		
		$re_assign = sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_testquestanswer
		SET score_correct = '".$score_assigned."'
		WHERE idQuest = '".(int)$this->id."'");
		if(!$re_assign) return 0;
		else return round($score_assigned * $num_correct, 2);
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
		FROM ".$GLOBALS['prefix_lms']."_testquest 
		WHERE idQuest = '".$this->id."'"));
		
		$query_answer = "
		SELECT idAnswer, answer, is_correct, comment  
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer";
		$re_answer = sql_query($query_answer);
		
		$re_associate = sql_query("
		SELECT idAnswer, answer 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		
		
		//recover previous information
		$recover_answer = "
		SELECT idAnswer, more_info 
		FROM ".$GLOBALS['prefix_lms']."_testtrack_answer 
		WHERE idQuest = '".(int)$this->id."' AND 
			idTrack = '".(int)$id_track."'";
        if ($number_time != null){
            $recover_answer .= " AND number_time = ".$number_time;
        }

		$re_answer_do = sql_query($recover_answer);
		if(sql_num_rows($re_answer_do)) {
			
			while(list($id_a, $id_sel) = sql_fetch_row($re_answer_do)) $answer_do[$id_a] = $id_sel;
		}
		
		$option_associate = array();
		$option_associate[0] = $lang->def('_NO_ANSWER');
		while(list($id_aa, $answer_associate) = sql_fetch_row($re_associate)) {
			
			$option_associate[$id_aa] = $answer_associate;
		}
		
		$quest = '<div class="play_question">'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">';
		while(list($id_answer, $answer, $is_correct, $comm) = sql_fetch_row($re_answer)){
			
			$comm_corret 	= '';
			$answer_comment = '';
			
			$quest .= '<div>'
					.'<div class="associate_colum_float">'.$answer.'</div>'
					.'<div class="associate_colum_float">';
			foreach($option_associate as $id_aa => $text ) {
				
				if(isset($answer_do[$id_answer]) && $answer_do[$id_answer] == $id_aa) {
					if($is_correct == $id_aa) {
						$quest .= $text.'&nbsp;<span class="test_answer_correct">'.$lang->def('_TEST_CORRECT').'</span>';
					} else {
						$quest .= $text.'&nbsp;<span class="test_answer_incorrect">'.$lang->def('_TEST_INCORRECT').'</span>';
						$answer_comment = $comm;
					}
				} elseif($id_aa == $is_correct && $show_solution) {
					$comm_corret = $answer.'&nbsp;<span class="text_bold">'.$lang->def('_TEST_NOT_AS_THECORRECT').' : </span>'.$text;
				}
			}
			if($comm_corret != '') {
				$comment .= '<br />'.$comm_corret.( $answer_comment != '' ? '<br />' : '' ).$answer_comment.'<br />';
			}
			$quest .= '</div></div><div class="nofloat"></div>';
		}
		$quest .=  '</div>'
			.'</div>';
		
		return array(	'quest' 	=> $quest, 
						'score'		=> $this->userScore($id_track, $number_time),
						'comment'	=> $comment );
		
	}
	
	function importFromRaw($raw_quest, $id_test = false) {
		
		if($id_test === false) $id_test = 0;
		
		//insert question
		$ins_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_testquest 
		( idQuest, idTest, idCategory, type_quest, title_quest, difficult, time_assigned, sequence, page ) VALUES 
		( 	NULL,
			'".(int)$id_test."', 
			'".(int)$raw_quest->id_category."', 
			'".$this->getQuestionType()."', 
			'".$raw_quest->quest_text."',
			'".(int)$raw_quest->difficult."', 
			'".$raw_quest->time_assigned."',
			'1',
			'1' ) ";
		if(!sql_query($ins_query)) return false;
		
		//find id of auto_increment colum
		list($new_id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if(!$new_id_quest) return false;
		
		if(!is_array($raw_quest->answers)) return $new_id_quest;
		
		//retriving new answer
		reset($raw_quest->extra_info);
		while(list($k ,$raw_answer) = each($raw_quest->extra_info)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
			( idQuest, answer ) VALUES
			( 	'".(int)$new_id_quest."',
				'".$raw_answer->text."' ) ";
			if(!sql_query($ins_answer_query)) return false;
			list($new_correct[$k]) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		}
		
		reset($raw_quest->answers);
		while(list($k, $raw_answer) = each($raw_quest->answers)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_testquestanswer 
			( idQuest, is_correct, answer, comment, score_correct, score_incorrect ) VALUES
			( 	'".(int)$new_id_quest."', 
				'".(int)$new_correct[$k]."', 
				'".$raw_answer->text."', 
				'".$raw_answer->comment."',
				'".$this->_checkScore($raw_answer->score_correct)."', 
				'".$this->_checkScore($raw_answer->score_penalty)."') ";
			if(!sql_query($ins_answer_query)) return false;
		}
		
		return $new_id_quest;
	}
	
	function exportToRaw($id_test = false) {
		
		//retriving question information
		list($idCategory, $type_quest, $title_quest, $difficult, $time_assigned, ) = sql_fetch_row(sql_query("
		SELECT idCategory, type_quest, title_quest, difficult, time_assigned 
		FROM ".$GLOBALS['prefix_lms']."_testquest 
		WHERE idQuest = '".(int)$this->id."'")); 
		
		//insert the question copy
		$oQuest = new QuestionRaw();
		$oQuest->id 	= $this->id;
		$oQuest->qtype 	= $this->getQuestionType();
			
		$oQuest->id_category 	= $this->getCategoryName($idCategory);
		$oQuest->quest_text 	= $title_quest;
		$oQuest->difficult 		= $difficult;
		$oQuest->time_assigned 	= $time_assigned;
		
		$oQuest->answers 		= array();
		$oQuest->extra_info 	= array();
		
		//retriving new answer
		$i = 0;
		$corres = array();
		$re_answer = sql_query("
		SELECT idAnswer, is_correct, answer, comment, score_correct, score_incorrect 
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		while(list($idAnswer, $is_correct, $answer, $comment, $score_c, $score_inc) = sql_fetch_row($re_answer)) {
			
			$oAnswer = new AnswerRaw();
			$oAnswer->id_answer 		= $idAnswer;
			$oAnswer->is_correct 		= $is_correct;
			$oAnswer->text 				= $answer;
			$oAnswer->comment 			= $comment;
			$oAnswer->score_correct 	= $score_c;
			$oAnswer->score_penalty 	= $score_inc;
			
			$oQuest->answers[$i] = $oAnswer;
			$corres[$is_correct] = $i;
			$i++;
		}
		
		//retriving new answer
		$re_answer = sql_query("
		SELECT idAnswer, answer  
		FROM ".$GLOBALS['prefix_lms']."_testquestanswer_associate 
		WHERE idQuest = '".(int)$this->id."'
		ORDER BY idAnswer");
		
		$oQuest->extra_info = array();
		while(list($idAnswer, $answer) = sql_fetch_row($re_answer)) {
			
			$oAnswer = new AnswerRaw();
			$oAnswer->text = $answer;
			
			$oQuest->extra_info[$corres[$idAnswer]] = $oAnswer;
		}
				
		return $oQuest;
	}
}

?>