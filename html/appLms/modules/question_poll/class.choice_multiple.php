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

require_once(_lms_.'/modules/question_poll/class.question.php' );

class ChoiceMultiple_QuestionPoll extends QuestionPoll {
	
	/**
	 * class constructor
	 * 
	 * @param int	the unique database identifer of a question 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function ChoiceMultiple_QuestionPoll( $id ) {
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
		$lang =& DoceboLanguage::createInstance('poll');
		$GLOBALS['page']->add('<tr class="line_answer">'
			.'<td class="image">'
			//answer
			.'<label class="access-only" for="answer_'.$i.'">'.$lang->def('_ANSWER').'</label>'
			.'<textarea class="test_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="50" rows="3">'
			.( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : '' )
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
			//answer
		$GLOBALS['page']->add(
			'<label class="access-only" for="answer_'.$i.'">'.$lang->def('_ANSWER').'</label>'
			.'<textarea class="test_area_answer" id="answer_'.$i.'" name="answer['.$i.']" cols="25" rows="3">'
				.( isset($_POST['answer'][$i]) ? stripslashes($_POST['answer'][$i]) : '')
			.'</textarea>'
			.'</td>'
			.'</tr>'."\n", 'content');
	}
	
	/**
	 * this function create a new question
	 * 
	 * @param  int		$id_poll 	indicates the test selected
	 * @param  string	$back_poll	indicates the return url
	 * @return nothing
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function create( $id_poll, $back_poll ) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_poll));
		
		//manage number of answer
		$num_answer = importVar('num_answer', true, 2);
		if(isset($_POST['more_answer'])) ++$num_answer;
		if(isset($_POST['less_answer']) && ($num_answer > 1) ) --$num_answer;
		
		if(isset($_POST['add_question'])) {
			//insert the new question
			$ins_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_pollquest 
			( id_poll, id_category, type_quest, title_quest, sequence, page ) VALUES 
			( 	'".(int)$id_poll."', 
				'".(int)$_POST['id_category']."', 
				'".$this->getQuestionType()."', 
				'".$_POST['title_quest']."',
				'".$this->_getNextSequence($id_poll)."', 
				'".$this->_getPageNumber($id_poll)."' ) ";
			if(!sql_query($ins_query)) {
				
				errorCommunication($lang->def('_POLL_ERR_INS_QUEST')
					.getBackUi('index.php?modname=question_poll&amp;op=create&amp;type_quest='
					.$this->getQuestionType().'&amp;id_poll='.$id_poll.'&amp;back_poll='.$url_encode, $lang->def('_BACK')));
			}
			//find id of auto_increment colum
			list($id_quest) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			if(!$id_quest) {
				errorCommunication($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK')));
			}
			//insert answer
			for($i = 0; $i < $num_answer; $i++) {
				//insert answer
				$ins_answer_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer 
				( id_quest, answer, sequence ) VALUES
				( 	'".$id_quest."', 
					'".$_POST['answer'][$i]."', 
					'".$i."') ";
				if(!sql_query($ins_answer_query)) {
					
					errorCommunication($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK')));
				}
			}	
			//back to question list
			Util::jump_to( ''.$back_poll);
		}
		
		//insert form
			require_once(_lms_.'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		//writing difficult array
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
			.Form::getHidden('num_answer', 'num_answer', $num_answer)
		
			.Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest', 
				( isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : $lang->def('_POLL_QUEST_TITLE') ) )
			.Form::getDropdown( $lang->def('_CATEGORY'), 'id_category', 'id_category', $categories,
				( isset($_POST['id_category']) ? $_POST['id_category'] : '' ))
			.'<div class="nofloat"></div><br />'
			.'<table class="test_answer" cellspacing="0" summary="'.$lang->def('_POLL_ANSWER_SUMMARY').'">'."\n"
			.'<caption>'.$lang->def('_ANSWER').'</caption>'."\n"
			.'<tr>'
			.'<th>'.$lang->def('_ANSWER').'</th>'
			.'</tr>'."\n", 'content');
		for($i = 0; $i < $num_answer; $i++) {
			$this->_lineAnswer($i);
		}
		$GLOBALS['page']->add('</table>'
			.Form::getButton( 'more_answer', 'more_answer', $lang->def('_POLL_ADD_ONE_ANSWER'), 'button_nowh' ), 'content');
		if($num_answer > 1) 	$GLOBALS['page']->add(Form::getButton( 'less_answer', 'less_answer', $lang->def('_POLL_SUB_ONE_ANSWER'), 'button_nowh' ), 'content');
		$GLOBALS['page']->add(
			Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_INSERT_POLL'))
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
	function edit( $back_poll ) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		require_once(_base_.'/lib/lib.form.php');
		$url_encode = htmlentities(urlencode($back_poll));
		
		//manage number of answer
		$num_answer = importVar('num_answer', true, 2);
		if(isset($_POST['more_answer'])) ++$num_answer;
		if(isset($_POST['less_answer']) && ($num_answer > 1) ) --$num_answer;
		
		if(isset($_POST['add_question'])) {
			//update question
			$ins_query = "
			UPDATE ".$GLOBALS['prefix_lms']."_pollquest
			SET id_category = '".(int)$_POST['id_category']."', 
				type_quest = '".$this->getQuestionType()."', 
				title_quest = '".$_POST['title_quest']."' 
			WHERE id_quest = '".(int)$this->id."'";
			if(!sql_query($ins_query)) {
				
				$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_QUEST')
					.getBackUi('index.php?modname=question_poll&amp;op=edit&amp;type_quest='
					.$this->getQuestionType().'&amp;id_quest='.$this->id.'&amp;back_poll='.$url_encode, $lang->def('_BACK'))), 'content');
			}
			//update answer
			if( !isset($_POST['is_correct']) ) $_POST['is_correct'] = -1;
			
			//find saved answer
			$re_answer = sql_query("
			SELECT id_answer 
			FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
			WHERE id_quest = '".(int)$this->id."'");
			while(list($id_a) = sql_fetch_row($re_answer)) $existent_answer[$id_a] = 1;
			
			for($i = 0; $i < $num_answer; $i++) {
				//scannig answer
				if( isset($_POST['answer_id'][$i]) ) {
					//must update
					$id_answer = $_POST['answer_id'][$i];
					if(isset($existent_answer[$id_answer])) unset($existent_answer[$id_answer]);
					
					$upd_ans_query = "
					UPDATE ".$GLOBALS['prefix_lms']."_pollquestanswer 
					SET answer = '".$_POST['answer'][$i]."'
					WHERE id_answer = '".(int)$id_answer."'";
					if(!sql_query($upd_ans_query)) {
						$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');
					}
				}
				else {
					//insert new answer
					$ins_answer_query = "
					INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer 
					( id_quest, answer, sequence ) VALUES
					( '".$this->id."', 
						'".$_POST['answer'][$i]."', 
						'".$i."') ";
					if(!sql_query($ins_answer_query)) {
						
						$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');
					}
				}
			}
			while(list($id_answer) = each($existent_answer)) {
				//i must delete these answer
				$del_answer_query = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_pollquestanswer
				WHERE id_quest = '".(int)$this->id."' AND id_answer = '".(int)$id_answer."'";
				if(!sql_query($del_answer_query)) {
					
					$GLOBALS['page']->add(getErrorUi($lang->def('_POLL_ERR_INS_ANSWER').getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK'))), 'content');
				}
			}
			//back to question list
			Util::jump_to( ''.$back_poll);
		}
		
		//insert form
			require_once(_lms_.'/lib/lib.questcategory.php');
			$categories = Questcategory::getCategory();
		
		//load data
		if(!isset($_POST['answer_id'])) {
			list($sel_cat, $quest) = sql_fetch_row(sql_query("
			SELECT id_category, title_quest 
			FROM ".$GLOBALS['prefix_lms']."_pollquest 
			WHERE id_quest = '".(int)$this->id."'"));
			
			$re_answer = sql_query("
			SELECT id_answer, answer 
			FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
			WHERE id_quest = '".(int)$this->id."'
			ORDER BY id_answer");
			
			$i_load = 0;
			while(list($_POST['answer_id'][$i_load], $_POST['answer'][$i_load]) = sql_fetch_row($re_answer)){
				++$i_load;
			}
			$num_answer = $i_load;
		}
		$GLOBALS['page']->add(getTitleArea($lang->def('_POLL_SECTION'), 'poll')
			.'<div class="std_block">'
			.getBackUi(Util::str_replace_once('&', '&amp;', $back_poll), $lang->def('_BACK'))
			.'<div class="title_big">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($this->getQuestionType())).' - '
			.$lang->def('_QUEST_'.strtoupper($this->getQuestionType()))
			.'</div><br />'
			.Form::openForm('form_add_quest', 'index.php?modname=question_poll&amp;op=edit')
		
			.Form::openElementSpace()
			.Form::getHidden('type_quest', 'type_quest', $this->getQuestionType())
			.Form::getHidden('id_quest', 'id_quest', $this->id)
			.Form::getHidden('back_poll', 'back_poll', $url_encode)
			.Form::getHidden('num_answer', 'num_answer', $num_answer)
		
			.Form::getTextarea($lang->def('_POLL_QUEST_TITLE'), 'title_quest', 'title_quest', 
			( isset($_POST['title_quest']) ? stripslashes($_POST['title_quest']) : $quest ) )
			.Form::getDropdown( $lang->def('_CATEGORY'), 'id_category', 'id_category', $categories,
			( isset($_POST['id_category']) ? $_POST['id_category'] : $sel_cat ))
			.'<div class="nofloat"></div><br />'
		
			.'<table class="test_answer" cellspacing="0" summary="'.$lang->def('_POLL_ANSWER_SUMMARY').'">'."\n"
			.'<caption>'.$lang->def('_ANSWER').'</caption>'."\n"
			.'<tr>'
			.'<th>'.$lang->def('_ANSWER').'</th>'
			.'</tr>'."\n", 'content');
		for($i = 0; $i < $num_answer; $i++) {
			$this->_lineModAnswer($i);
		}
		$GLOBALS['page']->add('</table>'
			.Form::getButton( 'more_answer', 'more_answer', $lang->def('_POLL_ADD_ONE_ANSWER'), 'button_nowh' ), 'content');
		if($num_answer > 1) $GLOBALS['page']->add(Form::getButton( 'less_answer', 'less_answer', $lang->def('_POLL_SUB_ONE_ANSWER'), 'button_nowh' ), 'content');
		$GLOBALS['page']->add(
			Form::closeElementSpace()
		
			.Form::openButtonSpace()
			.Form::getButton('add_question', 'add_question', $lang->def('_SAVE'))
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
		SELECT id_answer, answer, sequence 
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY id_answer");
		while(list($id_answer, $answer, $seq) = sql_fetch_row($re_answer)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer 
			( id_quest, answer, sequence ) VALUES
			( 	'".(int)$new_id_quest."',  
				'".sql_escape_string($answer)."',
				'".(int)$seq."' ) ";
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
	function play( $num_quest, $shuffle_answer = false, $id_track = 0, $freeze = false ) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest 
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_quest = '".$this->id."'"));
		
		$query_answer = "
		SELECT id_answer, answer 
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".(int)$this->id."'";
		if($shuffle_answer) $query_answer .= " ORDER BY RAND()";
		else $query_answer .= " ORDER BY sequence";
		$re_answer = sql_query($query_answer);
		
		$find_prev = false;
		$id_answer_do = 0;
		if($id_track != 0) {
			
			//recover previous information
			$recover_answer = "
			SELECT id_answer 
			FROM ".$GLOBALS['prefix_lms']."_polltrack_answer 
			WHERE id_quest = '".(int)$this->id."' AND 
				id_track = '".(int)$id_track."'";
			$re_answer_do = sql_query($recover_answer);
			if(sql_num_rows($re_answer_do)) {
				
				//find previous answer
				$find_prev = true;
				while(list($id_a) = sql_fetch_row($re_answer_do)) $answer_do[$id_a] = 1;
			}
		}
		
		$content = '<div class="play_question">'
			.'<div class="title_question">'.$num_quest.') '.$title_quest.'</div>'
			.'<div class="answer_question">';
		while(list($id_answer, $answer) = sql_fetch_row($re_answer)){
			
			$content .= '<input type="checkbox" id="quest_'.$id_quest.'_'.$id_answer.'" '
				.'name="quest['.$id_quest.']['.$id_answer.']" value="1"'
				.( ($find_prev && isset($answer_do[$id_answer])) ? ' checked="checked"' : '' )
				.( $find_prev && $freeze ? ' disabled="disabled"' : '' ).' /> '
				.'<label class="text_answer" for="quest_'.$id_quest.'_'.$id_answer.'">'.$answer.'</label><br />';
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
	function playReport( $num_quest, $tot_answer, &$valid_track ) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest 
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_quest = '".$this->id."'"));
		
		$query_answer = "
		SELECT id_answer, answer 
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY sequence";
		$re_answer = sql_query($query_answer);
		
		//recover previous information
		$max = 0;
		$recover_answer = "
		SELECT id_answer, COUNT(*) 
		FROM ".$GLOBALS['prefix_lms']."_polltrack_answer 
		WHERE id_quest = '".(int)$this->id."' ";
		if(is_array($valid_track) && !empty($valid_track)) $recover_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		$recover_answer .= " GROUP BY id_answer ";
		$re_answer_do = sql_query($recover_answer);
		//find previous answer
		while(list($id_a, $num) = sql_fetch_row($re_answer_do)) {
			
			if($num > $max) $max = $num;
			$num_answer[$id_a] = $num;
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
		
		while(list($id_answer, $answer) = sql_fetch_row($re_answer)) {
			
			if(isset($num_answer[$id_answer]) && $max != 0) {
				$content .=  '<tr><td>'.$answer.'</td><td><div class="colored_row" style="width: '.round($num_answer[$id_answer] / $max * 90, 2).'%;">'
					.$num_answer[$id_answer].'</div></td></tr>';
			} else {
				$content .=  '<tr><td>'.$answer.'</td><td><div class="colored_row" style="width: 0%;">'
					.'</div></td></tr>';
			}
			
		}
		$content .=  '</table>'
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
	function storeAnswer( $id_track, &$source, $can_overwrite = false ) {
		
		$result = true;
		if ($_SESSION['levelCourse'] < 6)
		{
			$this->deleteAnswer($id_track);
			
			$re_answer = sql_query("
			SELECT id_answer
			FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
			WHERE id_quest = '".(int)$this->id."'");
			while(list($id_answer) = sql_fetch_row($re_answer)) {
				
				if(isset($source['quest'][$this->id][$id_answer])) {
					
					//answer checked by the user 
					$track_query = "
					INSERT INTO ".$GLOBALS['prefix_lms']."_polltrack_answer ( id_track, id_quest, id_answer ) 
					VALUES (
						'".(int)$id_track."', 
						'".(int)$this->id."', 
						'".(int)$id_answer."' )";
					$result &= sql_query($track_query);
				}
			}
		}
		return $result;
	}
	
	function deleteAnswer( $id_track ) {
		
		
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_polltrack_answer 
		WHERE id_track = '".(int)$id_track."' AND 
			id_quest = '".$this->id."'");
	}
	
		function export_CSV( $num_quest, $tot_answer, &$valid_track ) {
		$lang =& DoceboLanguage::createInstance('poll');
		
		list($id_quest, $title_quest) = sql_fetch_row(sql_query("
		SELECT id_quest, title_quest 
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_quest = '".$this->id."'"));
		
		$query_answer = "
		SELECT id_answer, answer 
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY sequence";
		$re_answer = sql_query($query_answer);
		
		//recover previous information
		$max = 0;
		$recover_answer = "
		SELECT id_answer, COUNT(*) 
		FROM ".$GLOBALS['prefix_lms']."_polltrack_answer 
		WHERE id_quest = '".(int)$this->id."' ";
		if(is_array($valid_track) && !empty($valid_track)) $recover_answer .= " AND id_track IN ( ".implode(',', $valid_track)." ) ";
		$recover_answer .= " GROUP BY id_answer ";
		$re_answer_do = sql_query($recover_answer);
		//find previous answer
		while(list($id_a, $num) = sql_fetch_row($re_answer_do)) {
			
			if($num > $max) $max = $num;
			$num_answer[$id_a] = $num;
		}
		$content = '"'.$num_quest.'";"'.str_replace('"', '""', $title_quest).'";"'.$lang->def('_QUEST_CHOICE_MULTIPLE').'"'."\r\n";
				
		while(list($id_answer, $answer) = sql_fetch_row($re_answer)) {
			
			if(isset($num_answer[$id_answer]) && $max != 0) {
				$content .=  ';"'.str_replace('"', '""', $answer).'";'.$num_answer[$id_answer]."\r\n";//.round(($num_answer[$id_answer] / $max) * 100, 2).',"'.'%"'
			} else {
				$content .=  ';"'.str_replace('"', '""', $answer).'";0'."\r\n";
			}
			
		}
		
		return $content;
	}
	
}

?>