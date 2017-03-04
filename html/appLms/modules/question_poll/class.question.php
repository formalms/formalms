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

/**
 * Abstarct class for question (refer to Factory design pattners)
 *
 * @package 	Test Question
 * @category 	Question
 * @version 	$Id: class.question.php 573 2006-08-23 09:38:54Z fabio $
 * @author  	Fabio Pirovano (fabio@docebo.com)
 */

class QuestionPoll {
	
	/**
	 * @var int	$id 	contains the question identifier 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 **/
	
	var $id;
	
	/**
	 * class constructor
	 * 
	 * @param int	$id	the unique database identifer of a question 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function QuestionPoll( $id ) {
		$this->id = $id;
		return;
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
		return 'question';
	}
	
	/**
	 * this function return the sequence value for a new question
	 * 
	 * @param  int	$id_poll	indicates the test selected 
	 *
	 * @return int	is the first empty position in question sequencing for the test $id_poll
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _getNextSequence( $id_poll ) {
		
		
		//select max sequence number
		list($seq) = sql_fetch_row(sql_query("
		SELECT MAX(sequence)
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_poll = '".$id_poll."'"));
		return ($seq + 1);
	}
	
	/**
	 * this function correct the error in the sequence of the question's answer
	 * 
	 * @return nothing
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	 
	 function _fixAnswerSequence() {
		
		
		$re_answer = sql_query("
		SELECT id_answer 
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY sequence, id_answer");
		
		$seq = 0;
		while(list($id_answer ) = sql_fetch_row($re_answer)){
			sql_query("
			UPDATE ".$GLOBALS['prefix_lms']."_pollquestanswer
			SET sequence = '".(int)$seq."' 
			WHERE id_answer = '".(int)$id_answer."'");
			++$seq;
		}
	}
	
	/**
	 * this function return the page of the question
	 * 
	 * @param  int	$id_poll	indicates the test selected 
	 * @return int	is the correct number of page for the question
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _getPageNumber( $id_poll ) {
		
		
		list($seq, $page) = sql_fetch_row(sql_query("
		SELECT MAX(sequence), MAX(page)
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_poll = '".$id_poll."'"));
		if(!$page) return 1;
		
		list($type_quest) = sql_fetch_row(sql_query("
		SELECT type_quest 
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE sequence = '".$seq."'"));
		if($type_quest == 'break_page') return ($page + 1);
		else return $page;
	}
	
	/**
	 * this function return the score in a good format for a query
	 * 
	 * @param  double	$score	the score to format 
	 * @return double	the score formatted
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function _checkScore( $score ) {
		$score = preg_replace('/,/', '.', $score);
		if( $score{0} == '.') $score = '0'.$score;
		return $score;
	}
	
	/**
	 * this function create a new question
	 * 
	 * @param  int		$id_poll		indicates the test selected
	 * @param  string	$back_poll	indicates the return url
	 * @return nothing
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function create( $id_poll, $back_poll ) {
		
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
		
	}
	
	/**
	 * this function delete the question with the id_quest saved in the variable $this->id
	 * 
	 * @return bool	if the operation success return true else return false 
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function del() {
		
		//delete answer
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_polltrack_answer 
		WHERE id_quest = '".$this->id."'")) return false;
		
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_pollquest_extra 
		WHERE id_quest = '".$this->id."'")) return false;
		
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".$this->id."'")) return false;
		
		return sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_quest = '".$this->id."'");
	}
	
	/**
	 * this function create a copy of a question and return the corresponding id
	 * usually a son of this class don't need to redefine this function
	 * 
	 * @return int 	return the id of the new question if success else return false
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function copy( $new_id_poll, $back_poll = NULL ) {
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
		SELECT id_answer, sequence, answer 
		FROM ".$GLOBALS['prefix_lms']."_pollquestanswer 
		WHERE id_quest = '".(int)$this->id."'
		ORDER BY id_answer");
		
		$map_answer[0] = 0;
		while(list($id_answer, $seq, $answer) = sql_fetch_row($re_answer)) {
			
			//insert answer
			$ins_answer_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_pollquestanswer 
			( id_quest, sequence, answer ) VALUES
			( 	'".(int)$new_id_quest."', 
				'".(int)$seq."', 
				'".sql_escape_string($answer)."' ) ";
			if(!sql_query($ins_answer_query)) return false;
			
			list($map_answer[$id_answer]) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		}
		
		//retriving extra information for this question 
		$re_extra = sql_query("
		SELECT id_answer, extra_info 
		FROM ".$GLOBALS['prefix_lms']."_pollquest_extra 
		WHERE id_quest = '".(int)$this->id."'");
		
		// save all the extra info, if there are
		while(list($id_answer, $title_info) = sql_fetch_row($re_extra)) {
			if(!sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']."_pollquest_extra 
			( id_quest, id_answer, extra_info ) VALUES 
			( 	'".(int)$new_id_quest."', 
				'".(int)$map_answer[$id_answer]."', 
				'".sql_escape_string($title_info)."' )")) return false;
		}
		
		return $new_id_quest;
	}
	
	function import( $format, $back_poll = NULL ) {
		
	}
	
	function export( $id, $format, $back_poll = NULL ) {
		
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
		
		return '';
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
	function playReport( $num_quest ) {
		
		return '';
	}
		
	/**
	 * return true if the user as done this question
	 * 
	 * @param  int	$id_track	the relative id_track
	 * 
	 * @return bool	true if success false otherwise
	 * 
	 * @access public
	 * @author Fabio Pirovano ( fabio@docebo.com )
	 */
	function userDoAnswer( $id_track ) {
		
		
		$recover_answer = "
		SELECT id_answer 
		FROM ".$GLOBALS['prefix_lms']."_polltrack_answer 
		WHERE id_quest = '".(int)$this->id."' AND 
			id_track = '".(int)$id_track."'";
		$re_answer_do = sql_query($recover_answer);
		
		if(sql_num_rows($re_answer_do)) return true;
		else return false;
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
		
		return true;
	}
	
	/**
	 * save the answer to the question in an proper format overwriting the old entry
	 * 
	 * @param  int		$id_track	the relative id_track
	 * @param  array	$source		source of the answer send by the user
	 * 
	 * @return bool	true if success false otherwise
	 * 
	 * @access private
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function updateAnswer( $id_track, &$source ) {
		
		return true;
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
		
		return true;
	}

	/**
	 * export the data in CSV format
	 * 
	 * @param  $num_quest			the question number in the poll
	 * @param  $tot_tracks		tot_tracks
	 * @param  $valid_track		valid_tracks
	 * 		 
	 * @return string	of CSV file content
	 * 
	 * @access public
	 * @author Fabio Pirovano (fabio@docebo.com)
	 */
	function export_CSV( $num_quest, $tot_tracks, &$valid_track ) {
		
		return '';
	}	
}

?>
