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

class PollManagement {
	
	var $id_poll;
	
	var $test_info;
	
	/**
	 * class constructor, load info about the test
	 * @param int	$id_poll	the id of the test
	 */
	function PollManagement($id_poll) {
		
		$this->id_poll 		= $id_poll;
		$this->_load($id_poll);
	}
	
	function _load($id_poll) {
		
		$query_poll = "
		SELECT id_poll, title, description 
		FROM ".$GLOBALS['prefix_lms']."_poll 
		WHERE id_poll = '".$id_poll."'";
		$re_poll = sql_query($query_poll);
		
		$this->test_info = sql_fetch_assoc($re_poll);
	}
	
	/**
	 * return all the caracteristic for the test
	 * @return array all the info for the test
	 */
	function getPollAllInfo() {
		
		return $this->test_info;
	}
	
	/**
	 * return a specific caracteristic for the test
	 * @param string 	$info_name	the name of the carachteristic for the test
	 *
	 * @return mixed the value of the caracteristic
	 */
	function getPollInfo($info_name) {
		
		return $this->test_info[$info_name];
	}
	/**
	 * @return int 	return the total number of page for the test 
	 */
	function getTotalQuestionNumber() {
		
		list($tot_quest) = sql_fetch_row(sql_query("
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_poll = '".$this->id_poll."'"));
		return $tot_quest;
	}
	
	/**
	 * @return int 	return the total number of page for the test 
	 */
	function getTotalPageNumber() {
		
		list($tot_page) = sql_fetch_row(sql_query("
		SELECT MAX(page) 
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_poll = '".$this->id_poll."'"));
		return $tot_page;
	}
	
	/**
	 * @param 	int	$page_number	the number of the page
	 *
	 * @return 	int 	return the initial sequence number of the question for the page
	 */
	function getInitQuestSequenceNumberForPage($page_number) {
		
		list($quest_sequence_number) = sql_fetch_row(sql_query("
		SELECT COUNT(*) + 1 
		FROM ".$GLOBALS['prefix_lms']."_pollquest 
		WHERE id_poll = '".$this->id_poll."' AND page < '".$page_number."' 
			AND type_quest <> 'title' AND type_quest <> 'break_page'"));
		
		return $quest_sequence_number;
	}
	
}

class PlayPollManagement {
	
	var $id_poll;
	
	var $id_track;
	
	var $id_user;
	
	/**
	 * @param	PollMnagement
	 */
	var $test_man;
	
	var $track_info;
	
	function PlayPollManagement($id_poll, $id_user, $id_track, &$test_man) {
		
		$this->id_poll 		= $id_poll;
		$this->id_track 	= $id_track;
		$this->id_user 		= $id_user;
		$this->test_man 	=& $test_man;
		
		$this->_load($id_track);
	}
	
	function _load($id_track) {
		
		$query_track_info 	= "
		SELECT date_attempt, status
		FROM ".$GLOBALS['prefix_lms']."_polltrack
		WHERE id_track = '".$id_track."'";
		$re_track_info 		= sql_query($query_track_info);
		$this->track_info 	= sql_fetch_assoc($re_track_info);
	}
	
	/**
	 * return all the track stats for the test
	 * @return array all the info for the track
	 */
	function getTrackAllInfo() {
		
		return $this->track_info;
	}
	
	/**
	 * @return int score tracking status
	 */
	function getStatus() {
		
		return $this->track_info['status'];
	}
	
	/**
	 * return a sql query text for question mining
	 *
	 * @return string return the query for question retrivier
	 */
	function getQuestionsForPage($page_number) {
		
		// Retrive info about a test
		
		// Query base
		$query_question = "
		SELECT q.id_quest, q.type_quest, t.type_file, t.type_class 
		FROM ".$GLOBALS['prefix_lms']."_pollquest AS q 
			JOIN ".$GLOBALS['prefix_lms']."_quest_type_poll AS t 
		WHERE  q.type_quest = t.type_quest AND q.id_poll = '".$this->id_poll."' 
			AND q.page = '".$page_number."' 
		 ORDER BY q.sequence";
		 return $query_question;
	}
	
	function storePage($page_to_save, $can_overwrite) {
		
		$query_question = $this->getQuestionsForPage($page_to_save);
		$re_question = sql_query($query_question);
		while(list($id_quest, $type_quest, $type_file, $type_class) = sql_fetch_row($re_question)) {
			
			require_once($GLOBALS['where_lms'].'/modules/question_poll/'.$type_file);
			$quest_obj 	= eval("return new $type_class( $id_quest );");
			$storing 	= $quest_obj->storeAnswer( $this->id_track, $_POST, $can_overwrite );
		}
	}
	
}

class ReportPollManagement {
	
	/**
	 * return all the track id for the poll
	 * @param int	$status		a filter applied to the status
	 *
	 * @return array all the info for the tracks
	 */
	function getAllTrackId($id_poll, $status = false) {
		
		$query_track_info 	= "
		SELECT id_track
		FROM ".$GLOBALS['prefix_lms']."_polltrack
		WHERE id_poll = '".$id_poll."'";
		if($status !== false) {
			$query_track_info 	.= " AND status = '".$status."'";
		}
		$re_track_info 		= sql_query($query_track_info);
		$polls_track = array();
		if(sql_num_rows($re_track_info)) {
			while(list($id_track) = sql_fetch_row($re_track_info)) {
				
				$polls_track[$id_track] = $id_track;
			}
		}
		return $polls_track;
	}
	
	function getHowMuchStat($id_poll, $status = false) {
		
		$num_track = 0;
		$query_track_info 	= "
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_polltrack
		WHERE id_poll = '".$id_poll."'";
		if($status !== false) {
			$query_track_info 	.= " AND status = '".$status."'";
		}
		$re = sql_query($query_track_info);
		if($re) list($num_track) = sql_fetch_row($re);
		return $num_track;
	}
	
	/**
	 * return a sql query text for question mining
	 *
	 * @return string return the query for question retrivier
	 */
	function getQuestions($id_poll) {
		
		// Query base
		$query_question = "
		SELECT q.id_quest, q.type_quest, t.type_file, t.type_class 
		FROM ".$GLOBALS['prefix_lms']."_pollquest AS q 
			JOIN ".$GLOBALS['prefix_lms']."_quest_type_poll AS t 
		WHERE  q.type_quest = t.type_quest AND q.id_poll = '".$id_poll."' 
		 ORDER BY q.sequence";
		 return $query_question;
	}
	
}

?>
