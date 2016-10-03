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
 * @package appLms
 * @subpackage e-portfolio 
 * @author	 Fabio Pirovano
 * @version  $Id: lib.eportfolio.php 1002 2007-03-24 11:55:51Z fabio $
 * @since 3.1.0
 */

define("_MOD_ANSWER_FOREVER", -1);
define("_MOD_ANSWER_NEVER", 0);

define("EPF_ID", 0);
define("EPF_TITLE", 1);
define("EPF_DESCRIPTION", 2);
define("EPF_CUSTOM_PDP", 3);
define("EPF_CUSTOM_COMPETENCE", 4);

define("CURRICULUM_FILE", 0);
define("CURRICULUM_DATE", 1);

define("PDP_ID", 0);
define("PDP_TEXTOF", 1);
define("PDP_ALLOW_ANSWER", 2);
define("PDP_MAX_ANSWER", 3);
define("PDP_ANSWER_MOD_FOR_DAY", 4);
define("PDP_SEQUENCE", 5);
define("PDP_ID_PORTFOLIO", 6);

define("PDP_ANSWER_ID", 0);
define("PDP_ANSWER_ID_USER", 1);
define("PDP_ANSWER_ID_PDP", 2);
define("PDP_ANSWER_TEXTOF", 3);
define("PDP_ANSWER_POST_DATE", 4);

define("COMPETENCE_ID", 0);
define("COMPETENCE_TEXTOF", 1);
define("COMPETENCE_MIN_SCORE", 2);
define("COMPETENCE_MAX_SCORE", 3);
define("COMPETENCE_SEQUENCE", 4);
define("COMPETENCE_BLOCK", 5);

define("COMPETENCE_SCORE_ID_PROTFOLIO", 0);
define("COMPETENCE_SCORE_ID_COMPETENCE", 1);
define("COMPETENCE_SCORE_ESTIMATED_USER", 2);
define("COMPETENCE_SCORE_FROM_USER", 3);
define("COMPETENCE_SCORE_SCORE", 4);
define("COMPETENCE_SCORE_COMMENT", 5);
define("COMPETENCE_SCORE_STATUS", 6);

define("INVITE_INVITED_USER", 0);
define("INVITE_SENDER", 1);
define("INVITE_ID_PORTFOLIO",2);
define("INVITE_MESSAGE_TEXT",3);
define("INVITE_REFUSED",4);
				
define("C_SCORE_VALID", 1);
define("C_SCORE_WAITING", 0);

define("PRES_ID", 0);
define("PRES_ID_PROTFOLIO", 1);
define("PRES_TITLE", 2);
define("PRES_TEXTOF", 3);
define("PRES_OWNER", 4);
define("PRES_SHOW_PDP", 5);
define("PRES_SHOW_COMPETENCE", 6);
define("PRES_SHOW_CURRICULUM", 7);
define("PRES_PUBBLICATION_DATE", 8);
define("PRES_REMOTE_CODE", 9);

/**
 * This class abstract the database structure of the eportfolio, 
 * if you need information about the content of an eportfolio you must use this class
 */
class Man_Eportfolio {
	
	/**
	 * Class constructor, initialize the instance
	 */
	function Man_Eportfolio() {
		
	}
	
	/**
	 * @return string the main table of the eportfolio
	 */
	function getTableEpf() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio';
	}
	
	/**
	 * @return string the table with the member of the eporflio (simple user and eportfolio tutor)
	 */
	function getTableEpfMember() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio_member';
	}
	
	/**
	 * @return string the table with the pdp queston of te eportfolio
	 */
	function getTablePdp() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio_pdp';
	}
	
	/**
	 * @return string the table with the competence of the eportfolio
	 */
	function getTableCompetences() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio_competence';
	}
	
	/**
	 * @return string the table with the competence score for the user
	 */
	function getTableScoreCompetences() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio_competence_score';
	}
	
	/**
	 * @return string the table that contain the user answer to the pdp
	 */
	function getTablePdpAnswer() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio_pdp_answer';
	}
	
	/**
	 * @return string the table that contain the invite
	 */
	function getTableCompetenceInvite() {
		
		return $GLOBALS['prefix_lms'].'_eportfolio_competence_invite';
	}
	
	/**
	 * @return string the main table for the presentaton
	 */
	function getTablePresentation() {
	
		return $GLOBALS['prefix_lms'].'_eportfolio_presentation';
	}
	
	/**
	 * @return string the main table for the presentaton invite
	 */
	function getTablePresentationInvite() {
	
		return $GLOBALS['prefix_lms'].'_eportfolio_presentation_invite';
	}
	
	/**
	 * @return string the main table for the presentaton attached file
	 */
	function getTablePresentationAttach() {
	
		return $GLOBALS['prefix_lms'].'_eportfolio_presentation_attach';
	}
	
	/**
	 * @return string the main table for the curriculum
	 */
	function getTableCurriculum() {
	
		return $GLOBALS['prefix_lms'].'_eportfolio_curriculum';
	}
	
	/**
	 * Perform the query ( also perform debug )
	 * @return mixed the resource id created by the execution of the query 
	 */
	function _query($query) {
		
		$re = sql_query($query);
		return $re;
	}
	
	/**
	 * @return mixed	the last id inserted or false 
	 */
	function _lastInsertId() {
		
		$id = false;
		$query = "SELECT LAST_INSERT_ID()";
		if(!$re = $this->_query($query)) return false;
		
		list($id) = sql_fetch_row($re);
		return $id;
	}
	
	/**
	 * save an e-portfolio (new one or update)
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * @param	array	$array_data		data of the eportfolio (title=> '', description => '')
	 * 
	 * @return 	int the id of the eportfolio saved (the new id if created) or false if fail
	 */
	function savePortfolio($id_portfolio, $array_data) {
		
		if($id_portfolio === false) {
			// insert a new one
			$query = "
			INSERT INTO ".$this->getTableEpf()." ( title, description, custom_pdp_descr, custom_competence_descr ) VALUES (
				'".$array_data['title']."', 
				'".$array_data['description']."', 
				'".$array_data['custom_pdp_descr']."', 
				'".$array_data['custom_competence_descr']."' ) ";
			
			if(!$this->_query($query)) return false;
			$id_portfolio = $this->_lastInsertId();
			return $id_portfolio;
		} else {
			// update previous
			$query = "
			UPDATE ".$this->getTableEpf()." 
			SET title = '".$array_data['title']."', 
				description = '".$array_data['description']."', 
				custom_pdp_descr = '".$array_data['custom_pdp_descr']."', 
				custom_competence_descr = '".$array_data['custom_competence_descr']."'
			WHERE id_portfolio = '".$id_portfolio."'";
			
			if(!$this->_query($query)) return false;
			return $id_portfolio;
		}
	}
	
	/**
	 * delete an e-portfolio (new one or update)
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * 
	 * @return 	bool true if success, false otherwise
	 */
	function deletePortfolio($id_portfolio) {
		
		if($id_portfolio == 0) return true;
		
		$query = "
		DELETE FROM ".$this->getTableEpfMember()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		if(!$this->_query($query)) return false;
		
		if(!$this->deletePdpOfEportfolio($id_portfolio)) return false;
		
		if(!$this->deleteCompetenceOfEportfolio($id_portfolio)) return false;
		
		$query = "
		DELETE FROM ".$this->getTableEpf()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		return $this->_query($query);
	}
	
	/**
	 * return data about an e-portfolio
	 * @param	int	$id_portfolio	the id of the eportfolio 
	 * 
	 * @return 	array 	an array with the searched info with this structure array(id_portfolio, title, description)
	 */
	function getEportfolio($id_portfolio) {
		
		$data = array();
		if($id_portfolio == 0) return $data;
		$query = "
		SELECT id_portfolio, title, description, custom_pdp_descr, custom_competence_descr 
		FROM ".$this->getTableEpf()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		if(!$re_epf = $this->_query($query)) return $data;
		
		list(	$data['id_portfolio'], 
				$data['title'], 
				$data['description'], 
				$data['custom_pdp_descr'], 
				$data['custom_competence_descr']) = sql_fetch_row($re_epf);
		return $data;
	}
	
	/**
	 * return the complete list of all the eportfoli
	 * @param 	text	$filter_title	fulltext research based on this string
	 * @param	int 	$ini			result starting from row
	 * @param	int 	$limit			the maximum nuber of result (ini and limit must be defined togheter)
	 *
	 * @return 	array 	an array with the searched info wth this structure array([id_portfolio] => portfolio_info, ...)
	 *					portfolio_info -> array(id_portfolio, title, description)
	 */
	function getAllEportfolio($filter_title = '', $ini = false, $limit = false) {
		
		$data = array();
		$query = "
		SELECT id_portfolio, title, description, custom_pdp_descr, custom_competence_descr 
		FROM ".$this->getTableEpf()." ";
		if($filter_title != '') $query .= " WHERE title LIKE '%".$filter_title."%' ";
		$query .= " ORDER BY title ";
		if($ini !== false && $limit !== false) $query .= "LIMIT $ini, $limit";
		if(!$re_epf = $this->_query($query)) return $data;
		while($row = sql_fetch_row($re_epf)) {
			
			$data[$row[0]] = array(
				'id_portfolio'				=> $row[0], 
				'title'						=> $row[1], 
				'description' 				=> $row[2],
				'custom_pdp_descr' 			=> $row[3],
				'custom_competence_descr' 	=> $row[4]
			);
		}
		return $data;
	}
	
	
	/**
	 * return the info list for the eportfolio passed
	 * @param 	array	$arr_epf	the id for the eportfoli
	 * @param	int 	$ini		result starting from row
	 * @param	int 	$limit		the maximum nuber of result (ini and limit must be defined togheter)
	 *
	 * @return 	resource 	the mysql resource for result (id_portfolio, title, description) 
	 */
	function getEportfolioInfo($arr_epf, $ini = false, $limit = false) {
		
		$data = array();
		$query = "
		SELECT id_portfolio, title, description, custom_pdp_descr, custom_competence_descr 
		FROM ".$this->getTableEpf()." 
		WHERE id_portfolio IN ( ".implode(',', $arr_epf)." )
		ORDER BY title ";
		if($ini !== false && $limit !== false) $query .= "LIMIT $ini, $limit";
		if(!$re_epf = $this->_query($query)) return false;
		return $re_epf;
	}
	
	/**
	 * return the complete list of all the eportfoli
	 * @param 	text	$filter_title	fulltext research based on this string
	 * @param	int 	$ini			result starting from row
	 * @param	int 	$limit			the maximum nuber of result (ini and limit must be defined togheter)
	 *
	 * @return 	#resource_id the result of the mysql query
	 */
	function getQueryResultEportfolio($filter_title = '', $ini = false, $limit = false) {
		
		$data = array();
		$query = "
		SELECT id_portfolio, title, description, custom_pdp_descr, custom_competence_descr  
		FROM ".$this->getTableEpf()." ";
		if($filter_title != '') $query .= "WHERE title LIKE '%".$filter_title."%' ";
		$query .= " ORDER BY title ";
		if($ini !== false && $limit !== false) $query .= "LIMIT $ini, $limit";
		return $re_epf = $this->_query($query);
	}
	
	/**
	 * return the number of all the eportfoli
	 * @param 	text	$filter_title	fulltext research based on this string
	 *
	 * @return 	int 	the number of eportfolio found
	 */
	function getTotalOfEportfolio($filter_title = '') {
		
		$number_of = 0;
		$query = "
		SELECT COUNT(*) 
		FROM ".$this->getTableEpf()." ";
		if($filter_title != '') $query .= "WHERE title LIKE '%".$filter_title."%' ";
		if(!$re_epf = $this->_query($query)) return $number_of;
		
		list($number_of) = sql_fetch_row($re_epf);
		return $number_of;
	}
	
	/**
	 * return the number of member (user and admin by default) associated to the eportfolio passed or to all the eportfolii
	 * @param	array	$arr_portfolio		the id of the eportfolio 
	 * @param	mixed	$type_of_assoc		if is passed 'admin' the function count only the admin ,if 'user' only the user
	 *
	 * @return 	array 	the id_portfolio with the number of member associated if different from zero
	 */
	function getNumberOfAssociatedMember($arr_portfolio = false, $type_of_assoc = false) {
		
		$members = array();
		$query = "SELECT id_portfolio, COUNT(*) 
		FROM ".$this->getTableEpfMember()." 
		WHERE 1 ";
		if($arr_portfolio !== false && is_array($arr_portfolio) && !empty($arr_portfolio)) {
			$query .= " AND id_portfolio IN  ( ".implode(',', $arr_portfolio)." ) ";
		}
		if($type_of_assoc !== false) {
			if($type_of_assoc == 'admin') $query .= " AND user_is_admin = 'true' ";
			else $query .= " AND user_is_admin = 'false' ";
		}
		$query .= " GROUP BY id_portfolio";
		
		$re_members = $this->_query($query);
		while(list($id_portfolio, $num_members) = sql_fetch_row($re_members)) {
			
			$members[$id_portfolio] = $num_members;
		}
		return $members;
	}
	
	/**
	 * return the id of the eportfolio
	 * @param	array	$arr_members		the id of the members to check
	 *
	 * @return 	array 	array([admin] => array(id, id, ...) [user] => array(id, id, ...))
	 */
	function getEportfolioAssignedTo($arr_members) {
		
		$portfoli['user'] = array();
		$portfoli['admin'] = array();
		$re_members = $this->_query("
		SELECT id_portfolio, user_is_admin 
		FROM ".$this->getTableEpfMember()." 
		WHERE idst_member IN ( ".implode(',', $arr_members)." ) ");
		
		while(list($id_p, $user_is_admin) = sql_fetch_row($re_members)) {
			
			if($user_is_admin == 'false') $portfoli['user'][$id_p] = $id_p;
			else $portfoli['admin'][$id_p] = $id_p;
		}
		return $portfoli;
	}
	
	/**
	 * return the member associated to th eportfolio
	 * @param	int	$id_portfolio	the id of the eportfolio 
	 *
	 * @return 	array 	the id of the members
	 */
	function &getAssociatedMember($id_portfolio) {
		
		$members = array();
		$re_members = $this->_query("
		SELECT idst_member 
		FROM ".$this->getTableEpfMember()." 
		WHERE id_portfolio = '".$id_portfolio."' AND user_is_admin = 'false'");
		
		while(list($id_members) = sql_fetch_row($re_members)) {
			
			$members[$id_members] = $id_members;
		}
		return $members;
	}
	
	/**
	 * return the administrator associated to the eportfolio
	 * @param	int	$id_portfolio	the id of the eportfolio 
	 *
	 * @return 	array 	the id of the administrator
	 */
	function &getAssociatedAdmin($id_portfolio) {
		
		$members = array();
		$re_members = $this->_query("
		SELECT idst_member
		FROM ".$this->getTableEpfMember()." 
		WHERE id_portfolio = '".$id_portfolio."' AND user_is_admin = 'true'");
		
		while(list($id_members) = sql_fetch_row($re_members)) {
			
			$members[$id_members] = $id_members;
		}
		return $members;
	}
	
	/**
	 * add passed members to the portfolio
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * @param	array	$members_to_add	the id of the members to add 
	 * @param	bool	$user_is_admin		if the used passed are administrator
	 *
	 * @return 	bool the operation result
	 */
	function addMembers($id_portfolio, $members_to_add, $user_is_admin = false) {
		
		if(!is_array($members_to_add)) return false;
		if(empty($members_to_add)) return true;
		$result = true;
		reset($members_to_add);
		while(list(, $idst_member) = each($members_to_add)) {
			
			$query = "
			INSERT INTO ".$this->getTableEpfMember()." ( id_portfolio, idst_member, user_is_admin ) VALUES (
				'".$id_portfolio."', 
				'".$idst_member."',
				'".( $user_is_admin === true ? 'true' : 'false' )."'
			)";
			$result &= $this->_query($query);
		}
		return $result;
	}
	
	/**
	 * change the level of the members of the portfolio
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * @param	array	$members_to_add	the id of the members to add 
	 * @param	bool	$user_is_admin		if the used passed are administrator
	 *
	 * @return 	bool the operation result
	 */
	function updateMembers($id_portfolio, $members_to_add, $user_is_admin = false) {
		
		if(!is_array($members_to_add)) return false;
		if(empty($members_to_add)) return true;
		$result = true;
		reset($members_to_add);
		while(list(, $idst_member) = each($members_to_add)) {
			
			$query = "
			UPDATE ".$this->getTableEpfMember()." 
			SET user_is_admin = '".( $user_is_admin === true ? 'true' : 'false' )."' 
			WHERE id_portfolio = '".$id_portfolio."' AND 
				idst_member = '".$idst_member."'";
			$result &= $this->_query($query);
		}
		return $result;
	}
	
	/**
	 * remove passed members from the portfolio
	 * @param	int		$id_portfolio			the id of the eportfolio 
	 * @param	array	$members_to_remove		the id of the members to remove 
	 *
	 * @return 	bool the operation result
	 */
	function removeMembers($id_portfolio, $members_to_remove, $user_is_admin = false) {
		
		if(!is_array($members_to_remove)) return false;
		if(empty($members_to_remove)) return true;
		$result = true;
		reset($members_to_remove);
		while(list(, $idst_member) = each($members_to_remove)) {
			
			$query = "
			DELETE FROM ".$this->getTableEpfMember()." 
			WHERE id_portfolio = '".$id_portfolio."' AND 
					idst_member = '".$idst_member."' AND 
					user_is_admin = '".( $user_is_admin === true ? 'true' : 'false' )."'";
			$result &= $this->_query($query);
		}
		return $result;
	}
	
	/* Curriculum ==================================================================*/
	
	/**
	 * @return string the path where the user curriculum is saved
	 */
	function getCurriculumPath() {
		
		return '/common/users/curriculum/';
	}
	
	/**
	 * @param int $id_portfolio the id of the eportfolio
	 * @param int $id_user	 	the id of the user
	 * 
	 * @return array the curriculum file and the update date
	 */
	function getCurriculum($id_portfolio, $id_user) {
		
		$query = "
		SELECT curriculum_file, update_date
		FROM ".$this->getTableCurriculum()." 
		WHERE id_portfolio = '".$id_portfolio."' 
			AND id_user = '".$id_user."' ";
		if(!$re_curriculum = $this->_query($query)) return false;
		if(sql_num_rows($re_curriculum) == 0) return false;
		
		$row = sql_fetch_row($re_curriculum);
		return $row;
	}
	
	function saveCurriculum($id_portfolio, $id_user, $file_descriptor) {
		
		$curriculum_file = '';
		if(!isset($file_descriptor['error'])) return false;
		if($file_descriptor['error'] != UPLOAD_ERR_OK) return false;
		if($file_descriptor['name'] == '') return false;
		
		require_once(_base_.'/lib/lib.upload.php');
		
		$curriculum_file = $id_user.'_'.mt_rand(0,100).'_'.time().'_'.$file_descriptor['name'];
		if(!file_exists($GLOBALS['where_files_relative'].$this->getCurriculumPath().$curriculum_file)) {
					
			sl_open_fileoperations();
			$upload = sl_upload($file_descriptor['tmp_name'], $this->getCurriculumPath().$curriculum_file);
			sl_close_fileoperations();
			if(!$upload) return false;
		}
		
		$sel_query = "
		SELECT curriculum_file 
		FROM ".$this->getTableCurriculum()." 
		WHERE id_portfolio = '".$id_portfolio."' 
			AND id_user = '".$id_user."' ";
		if(!$re_curriculum = $this->_query($sel_query)) {
			die('table problem '.sql_error());
			return false;
		}
		
		if(!sql_num_rows($re_curriculum)) {
		
			$query = "
			INSERT INTO ".$this->getTableCurriculum()." 
			( id_portfolio, id_user, curriculum_file, update_date ) VALUES 
			(	'".$id_portfolio."', 
				'".$id_user."', 
				'".$curriculum_file."',
				'".date("Y-m-d H:i:s")."' )";
		} else {
			
			list($old_curriculum_file) = sql_fetch_row($re_curriculum);
			sl_unlink($old_curriculum_file);
			
			$query = "
			UPDATE ".$this->getTableCurriculum()."
			SET curriculum_file = '".$curriculum_file."',
				update_date  = '".date("Y-m-d H:i:s")."'
			WHERE   id_portfolio = '".$id_portfolio."' 
				AND id_user = '".$id_user."'";
		}	
			
		if(!$this->_query($query)) {
		
		die('table problem 2');	
			return false;
		}
		return true;
	}
	
	function delCurriculum($id_portfolio, $id_user) {
		
		$sel_query = "
		SELECT curriculum_file 
		FROM ".$this->getTableCurriculum()." 
		WHERE id_portfolio = '".$id_portfolio."' 
			AND id_user = '".$id_user."' ";
		if(!$re_curriculum = $this->_query($sel_query)) return false;
		list($old_curriculum_file) = sql_fetch_row($re_curriculum);
		sl_unlink($old_curriculum_file);
		
		$query = "
		DELETE FROM ".$this->getTableCurriculum()." 
		WHERE id_portfolio = '".$id_portfolio."' 
			AND id_user = '".$id_user."'";	
			
		if(!$this->_query($query)) return false;
		return true;
	}
	
	/* Personal Developmnet Plan ===================================================*/
	
	/**
	 * Return all the details of a pdp 
	 * @param int $id_pdp the id of the pdp
	 * 
	 * @return mixed	an array with the info of the eportfolio or false (use constants to read form it)
	 */
	function getPdpDetails($id_pdp) {
		
		$data = array();
		$query = "
		SELECT id_pdp, textof, allow_answer, max_answer, answer_mod_for_day, sequence, id_portfolio  
		FROM ".$this->getTablePdp()." 
		WHERE id_pdp = '".$id_pdp."'";
		if(!$re_epf = $this->_query($query)) return $data;
		
		return sql_fetch_assoc($re_epf);
	}
	
	/**
	 * Get all the  pdp question of the eportfolio specified
	 * @param int $id_portfolio	the identifier portfolio
	 * @param int $from	limit result form the result number $form
	 * @param int $for	the maximum number of result
	 * 
	 * @return resource_id all the pdp question found
	 */
	function getQueryPdpOfEportfolio($id_portfolio, $from = false, $for = false) {
		
		$data = array();
		$query = "
		SELECT id_pdp, textof, allow_answer, max_answer, answer_mod_for_day, sequence, id_portfolio 
		FROM ".$this->getTablePdp()." 
		WHERE id_portfolio = '".$id_portfolio."' 
		ORDER BY sequence ";
		if($from !== false && $for !== false) $query .= " LIMIT ".$from.", ".$for;
		
		if(!$re_pdp = $this->_query($query)) return false;
		
		return $re_pdp;
	}
	
	/**
	 * Get the total number of pdp question in the eportfolio specified
	 * @param int $id_portfolio	the identifier portfolio
	 * 
	 * @return int the number of pdp found
	 */
	function getTotalOfPdp($id_portfolio) {
		
		$number_of = 0;
		$query = "
		SELECT COUNT(*) 
		FROM ".$this->getTablePdp()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		if(!$re_epf = $this->_query($query)) return $number_of;
		
		list($number_of) = sql_fetch_row($re_epf);
		return $number_of;
	}
	
	/**
	 * Get the next free space in the sequence of the pdp
	 * @param int $id_portfolio	the identifier portfolio
	 * 
	 * @return int the next sequence number
	 */
	function getNextPdpSequence($id_portfolio) {
		
		$data = array();
		$query = "
		SELECT MAX(sequence) 
		FROM ".$this->getTablePdp()." 
		WHERE id_portfolio = '".$id_portfolio."' ";
		
		if(!$re_pdp = $this->_query($query)) return 1;
		list($seq)  = sql_fetch_row($re_pdp);
		return $seq + 1;
	}
	
	/**
	 * save a pdp question 
	 * @param	int		$id_pdp			the id of the question 
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * @param	array	$array_data		data of the eportfolio (title=> '', description => '')
	 * 
	 * @return 	int the id of the eportfolio saved (the new id if created) or false if fail
	 */
	function savePdpQuestion($id_pdp, $id_portfolio, $array_data) {
		
		if($id_pdp === false) {
			
			$sequence = $this->getNextPdpSequence($id_portfolio);
			
			// insert a new one
			$query = "
			INSERT INTO ".$this->getTablePdp()." ( id_portfolio, textof, allow_answer, max_answer, answer_mod_for_day, sequence ) VALUES (
				'".$array_data['id_portfolio']."', 
				'".$array_data['textof']."', 
				'".$array_data['allow_answer']."', 
				'".$array_data['max_answer']."', 
				'".$array_data['answer_mod_for_day']."', 
				'".$sequence."' )";
			
			if(!$this->_query($query)) return false;
			//return false;
			$id_pdp = $this->_lastInsertId();
			return $id_pdp;
		} else {
			// update previous
			$query = "
			UPDATE ".$this->getTablePdp()." 
			SET textof = '".$array_data['textof']."' , 
				allow_answer = '".$array_data['allow_answer']."' , 
				max_answer = '".$array_data['max_answer']."' , 
				answer_mod_for_day = '".$array_data['answer_mod_for_day']."' 
			WHERE id_pdp = '".$id_pdp."'";
			
			if(!$this->_query($query)) return false;
			return $id_pdp;
		}
	}
	
	/**
	 * Move a pdp question up or down
	 * @param string $direction 'up' or 'down'
	 * @param int $id_pdp the identifier of the pdp to move
	 * @param int $id_portfolio	the identifier portfolio
	 * 
	 * @return 	bool true if success, false otherwise
	 */
	function movePdp($direction, $id_pdp, $id_portfolio) {
		
		
		return utilMoveItem(	$direction, 
								$this->getTablePdp(), 
								'id_pdp', 
								$id_pdp, 
								'sequence', 
								" id_portfolio = '".$id_portfolio."' ");
	}
	
	/**
	 * delete a pdp question
	 * @param	int		$id_pdp	the id of the pdp question 
	 * 
	 * @return 	bool true if success, false otherwise
	 */
	function deletePdpQuestion($id_pdp) {
		
		if($id_pdp == 0) return true;
		
		$info = $this->getPdpDetails($id_pdp);
		
		$query = "
		UPDATE ".$this->getTablePdp()."
		SET sequence = sequence - 1
		WHERE sequence > '".$info[PDP_SEQUENCE]."'";
		$this->_query($query);
		
		$query = "
		DELETE FROM ".$this->getTablePdp()." 
		WHERE id_pdp = '".$id_pdp."'";
		return $this->_query($query);
	}
	
	/**
	 * delete all the pdp question of an eportfolio
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * 
	 * @return 	bool true if success, false otherwise
	 */
	function deletePdpOfEportfolio($id_portfolio) {
		
		$query = "
		SELECT id_pdp 
		FROM ".$this->getTablePdp()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		$re_pdp = $this->_query($query);
		while(list($id_pdp) = sql_fetch_row($re_pdp)) {
			
			$query = "
			DELETE FROM ".$this->getTablePdpAnswer()." 
			WHERE id_pdp = '".$id_pdp."'";
			if(!$this->_query($query)) return false;
		}
		
		$query = "
		DELETE FROM ".$this->getTablePdp()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		if(!$this->_query($query)) return false;
		
		return true;
	}
	
	/**
	 * Get all the  answer given form a user to a pdp question
	 * @param int $id_pdp	the identifier of the pdp question
	 * @param int $id_user	the identifier of the user
	 * 
	 * @return resource_id all the pdp question found
	 */
	function getQueryPdpUserAnswer($id_pdp, $id_user) {
		
		$data = array();
		$query = "
		SELECT id_answer, id_user, id_pdp, textof, post_date 
		FROM ".$this->getTablePdpAnswer()." 
		WHERE id_pdp = '".$id_pdp."' AND id_user = '".$id_user."'
		ORDER BY post_date ";
		
		if(!$re_pdp = $this->_query($query)) return false;
		
		return $re_pdp;
	}
	
	/**
	 * Get the total number of answer given form a user to a pdp question
	 * @param int $id_pdp	the identifier of the pdp question
	 * @param int $id_user	the identifier of the user
	 * 
	 * @return int the numeber of answer found
	 */
	function getCountPdpUserAnswer($id_pdp, $id_user) {
		
		$data = array();
		$query = "
		SELECT COUNT(*)
		FROM ".$this->getTablePdpAnswer()." 
		WHERE id_pdp = '".$id_pdp."' AND id_user = '".$id_user."'";
		
		if(!$re_pdp = $this->_query($query)) return false;
		list($num) = sql_fetch_row($re_pdp);
		
		return $num;
	}
	
	/**
	 * Get all the info about a specific pdp question's answer 
	 * @param int $id_answer	the identifier of the answer
	 * @param int $id_user		the identifier of the user
	 * 
	 * @return array all the answer info found
	 */
	function getPdpAnswer($id_answer, $id_user) {
		
		$data = array();
		$query = "
		SELECT id_answer, id_user, id_pdp, textof, post_date 
		FROM ".$this->getTablePdpAnswer()." 
		WHERE id_answer = '".$id_answer."' AND id_user = '".$id_user."'
		ORDER BY post_date ";
		
		if(!$re_pdp = $this->_query($query)) return $data;
		
		$data = sql_fetch_array($re_pdp);
		return $data;
	}
	
	/**
	 * Careate or save a pdp answer
	 * @param int $id_answer	the identifier of the answer (if 0 the answer will be created, if != 0 the answer will be update)
	 * @param int $id_pdp		the identifier of the pdp question
	 * @param int $id_user		the identifier of the user
	 * @param string $textof	the answer given
	 * 
	 * @return true if success false otherwise
	 */
	function savePdpAnswer($id_answer, $id_pdp, $id_user, $textof) {
		
		if($id_answer == 0) {
			
			// insert a new one
			$query = "
			INSERT INTO ".$this->getTablePdpAnswer()." ( id_pdp, id_user, post_date, textof ) VALUES (
				'".$id_pdp."', 
				'".$id_user."', 
				'".date("Y-m-d H:i:s")."', 
				'".$textof."' )";
			
			if(!$this->_query($query)) return false;
			//return false;
			return $this->_lastInsertId();
		} else {
			// update previous
			$query = "
			UPDATE ".$this->getTablePdpAnswer()." 
			SET textof = '".$textof."' 
			WHERE id_answer = '".$id_answer."' 
				AND id_user = '".$id_user."'";
			
			if(!$this->_query($query)) return false;
			return $id_answer;
		}
	}
	
	/* Competence part ==============================================================*/
	
	/**
	 * A usefull function that return all the available info on a competence
	 * @param int $id_competence the id of the competence
	 * 
	 * @return array an associative array with the requested info 
	 */ 
	function getCompetenceDetails($id_competence) {
		
		$data = array();
		$query = "
		SELECT id_portfolio, textof, min_score, max_score, sequence, block_competence  
		FROM ".$this->getTableCompetences()." 
		WHERE id_competence = '".$id_competence."'";
		if(!$re_competence = $this->_query($query)) return $data;
		
		list(	$data['id_portfolio'], 
				$data['textof'], 
				$data['min_score'], 
				$data['max_score'], 
				$data['sequence'], 
				$data['block_competene'] ) = sql_fetch_row($re_competence);
		return $data;
	}
	
	/**
	 * return the list (in a sql ref) of the competences of an eportfolio
	 * @param int $id_portfolio the id of the portfolio
	 * @param int $from start from this row
	 * @param int $for limit the result number to this
	 * 
	 * @return resource_id	the resource dentifier of the query or false
	 */
	function getQueryCompetenceOfEportfolio($id_portfolio, $from = '', $for = '') {
		
		$data = array();
		$query = "
		SELECT id_competence, textof, min_score, max_score, sequence, block_competence 
		FROM ".$this->getTableCompetences()." 
		WHERE id_portfolio = '".$id_portfolio."' 
		ORDER BY sequence ";
		if($from != '' && $for != '') $query .= " LIMIT ".$from.", ".$for;
		
		if(!$re_competence = $this->_query($query)) return false;
		
		return $re_competence;
	}
	
	/**
	 * return the total number of competence for the eportfolio
	 * @param int $id_portfolio the id of the portfolio
	 * 
	 * @return int the numbers of competence found
	 */
	function getTotalOfCompetence($id_portfolio) {
		
		$number_of = 0;
		$query = "
		SELECT COUNT(*) 
		FROM ".$this->getTableCompetences()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		if(!$re_competence = $this->_query($query)) return $number_of;
		
		list($number_of) = sql_fetch_row($re_competence);
		return $number_of;
	}
	
	/**
	 * return the next free number in the sequence of the competence
	 * @param int $id_portfolio the id of the eportfolio
	 * 
	 * @return int the sequence number to assign to the next competence
	 */
	function getNextCompetenceSequence($id_portfolio) {
		
		$data = array();
		$query = "
		SELECT MAX(sequence) 
		FROM ".$this->getTableCompetences()." 
		WHERE id_portfolio = '".$id_portfolio."' ";
		
		if(!$re_pdp = $this->_query($query)) return 1;
		list($seq)  = sql_fetch_row($re_pdp);
		return $seq + 1;
	}
	
	/**
	 * this function move a competence up and down in the sequence
	 * @param string $direction the direction 'up' or 'down' of the movement
	 * @param int $id_competence the id of the competence to move
	 * @param int $id_portfolio	the id of the eportfolio that contain the competence
	 */
	function moveCompetence($direction, $id_competence, $id_portfolio) {
		
		return utilMoveItem(	$direction, 
								$this->getTableCompetences(), 
								'id_competence', 
								$id_competence, 
								'sequence', 
								" id_portfolio = '".$id_portfolio."' ");
	}
	
	/**
	 * save a competence question 
	 * @param	int		$id_pdp			the id of the question 
	 * @param	int		$id_portfolio	the id of the eportfolio 
	 * @param	array	$array_data		data of the eportfolio (title=> '', description => '')
	 * @param 	int 	$block_competence 0 = normal, 1 = block competence score 
	 * 
	 * @return 	int the id of the competece saved (the new id if created) or false if fail
	 */
	function saveCompeteceQuestion($id_competence, $id_portfolio, $array_data, $block_competence = 0) {
		
		if($id_competence === false) {
			
			$sequence = $this->getNextCompetenceSequence($id_portfolio);
			
			// insert a new one
			$query = "
			INSERT INTO ".$this->getTableCompetences()." ( id_portfolio, textof, min_score, max_score, sequence, block_competence ) VALUES (
				'".$id_portfolio."', 
				'".$array_data['textof']."', 
				'".$array_data['min_score']."', 
				'".$array_data['max_score']."', 
				'".$sequence."', 
				'".$block_competence."' )";
			
			if(!$this->_query($query)) return false;
			//return false;
			$id_competence = $this->_lastInsertId();
			return $id_competence;
		} else {
			// update previous
			$query = "
			UPDATE ".$this->getTableCompetences()." 
			SET textof = '".$array_data['textof']."' , 
				min_score = '".$array_data['min_score']."', 
				max_score = '".$array_data['max_score']."', 
				block_competence = '".$block_competence."' 
			WHERE id_competence = '".$id_competence."'";
			
			if(!$this->_query($query)) return false;
			return $id_competence;
		}
	}
	
	/**
	 * Block or unblock the evaluation for a competence
	 * @param int $id_competence the id of the competence
	 * @param int $block_competence if = 1 lock the competence, if = 0 unlock the competence
	 * 
	 */
	function modBlockCompeteceQuestion($id_competence, $block_competence) {
		
		$query = "
		UPDATE ".$this->getTableCompetences()." 
		SET block_competence = '".$block_competence."' 
		WHERE id_competence = '".$id_competence."'";
		
		if(!$this->_query($query)) return false;
		return $id_competence;
	}
	
	/**
	 * delete a competence question
	 * @param int $id_competence the id of the competence 
	 * 
	 * @return 	bool true if success, false otherwise
	 */
	function deleteCompetence($id_competence) {
		
		if($id_competence == 0) return true;
		
		$info = $this->getCompetenceDetails($id_competence);
		
		$query = "
		UPDATE ".$this->getTableCompetences()."
		SET sequence = sequence - 1
		WHERE sequence > '".$info['sequence']."'";
		$this->_query($query);
		
		$query = "
		DELETE FROM ".$this->getTableCompetences()." 
		WHERE id_competence = '".$id_competence."'";
		return $this->_query($query);
	}
	
	/**
	 * delete all the competence from a eportfolio
	 * @param int $id_portfolio the id of the eportfolio 
	 * 
	 * @return 	bool true if success, false otherwise
	 */
	function deleteCompetenceOfEportfolio($id_portfolio) {
		
		$query = "
		DELETE FROM ".$this->getTableScoreCompetences()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		if(!$this->_query($query)) return false;
		
		$query = "
		DELETE FROM ".$this->getTableCompetences()." 
		WHERE id_portfolio = '".$id_portfolio."'";
		return $this->_query($query);
	}
	
	/**
	 * this function return the score and comment assigned to a user
	 * @param int $id_portfolio the id of the portfolio
	 * @param int $estimated_user the idst of the user valutatede
	 * @param int $from_user filter the score, if != false only the score/comment assigned by this user is returned
	 * 
	 * @return resource_id the result of the query with the filter specified
	 */
	function getDetailedCompetenceScore($id_portfolio, $estimated_user, $from_user = false) {
		
		$data = array();
		$query = "
		SELECT id_portfolio, id_competence, estimated_user, from_user, score, comment, status 
		FROM ".$this->getTableScoreCompetences()." 
		WHERE id_portfolio = '".$id_portfolio."' 
			AND estimated_user = '".$estimated_user."' ";
		if($from_user !== false) $query .= " AND from_user = '".$from_user."'";
		
		if(!$re_score = $this->_query($query)) return false;
		
		return $re_score;
	}
	
	/**
	 * this function save the score and comment assigned by a user to another user for the eportfolio competence
	 * @param int $id_portfolio the id of the eportfolio
	 * @param int $estimated_user the idst of the user that is evaluated
	 * @param int $form_user the idst of the evaluator
	 * @param array $arr_score an array with the score for the competence (id_competence => score)
	 * @param array $arr_comment the array with the comment to the score assigned
	 * 
	 * @return bool true if succed false otherwise
	 */
	function saveAllCompetenceScore($id_portfolio, $estimated_user, $from_user, $arr_score, $arr_comment) {
		
		$re = true;
		
		// find the score alredy inserted by the from_user for the estimated_user
		
		$previous_exists = array();
		$re_score = $this->getDetailedCompetenceScore($id_portfolio, $estimated_user, $from_user);
		while($scores = sql_fetch_row($re_score)) {
			
			$previous_exists[$scores[COMPETENCE_SCORE_ID_COMPETENCE]] = true;
		}
		
		foreach($arr_score as $id_competence => $score) {
			
			if(!isset($previous_exists[$id_competence])) {
				
				//update older score
				$query = "
				INSERT INTO ".$this->getTableScoreCompetences()." 
				( id_portfolio, id_competence, estimated_user, from_user, score, comment, status ) VALUES (
					'".$id_portfolio."', 
					'".$id_competence."',
					'".$estimated_user."',
					'".$from_user."',
					'".$score."',
					'".$arr_comment[$id_competence]."',
					'".C_SCORE_VALID."'
				)";
			} else {
				
				// create new score
				$query = "
				UPDATE ".$this->getTableScoreCompetences()." 
				SET score = '".$score."',
					comment ='".$arr_comment[$id_competence]."',
					status = '".C_SCORE_VALID."'
				WHERE id_portfolio = '".$id_portfolio."' 
					AND id_competence = '".$id_competence."'
					AND estimated_user = '".$estimated_user."'
					AND from_user = '".$from_user."' ";
			}
			$re &= $this->_query($query);
		}
		
		return $re;
	}
	
	/* Invite *****************************************************************************/
	
	/**
	 * Return a specific invite
	 * @param int $invited_user the reciver of the invite
	 * @param int $sender the user that send the ivite
	 * @param int $id_portfolio the id of the eportfolio
	 * 
	 * @return mixed an array with the invite info or false 
	 */
	function getCompetenceInvite($invited_user, $sender, $id_portfolio) {
		
		$query = "
		SELECT invited_user, sender, id_portfolio, message_text, refused
		FROM ".$this->getTableCompetenceInvite()." 
		WHERE invited_user = '".$invited_user."'
			AND sender = '".$sender."' 
			AND id_portfolio = '".$id_portfolio."' 
			AND refused = 0";
		if(!$re_invite = $this->_query($query)) return false;
		
		return sql_fetch_row($re_invite);
	}
	
	/**
	 * Return the list of the invitation recived 
	 * @param int $id_user idst of the user
	 * 
	 * @return resource_id the details of the invitation recived
	 */
	function getAllCompetenceInvite($id_user, $id_portfolio = false) {
		
		$query = "
		SELECT invited_user, sender, id_portfolio, message_text, refused
		FROM ".$this->getTableCompetenceInvite()." 
		WHERE invited_user = '".$id_user."' AND refused = 0";
		if($id_portfolio !== false) $query .= " AND id_portfolio = '".$id_portfolio."'";
		if(!$re_invite = $this->_query($query)) return false;
		
		return $re_invite;
	}
	
	/**
	 * return the list of the invitation refused 
	 * @param int $id_user idst of the user
	 * 
	 * @return resource_id the details of the invitation recived
	 */
	function getAllCompetenceRefusedInvite($id_user, $id_portfolio = false) {
		
		$query = "
		SELECT invited_user, sender, id_portfolio, message_text, refused
		FROM ".$this->getTableCompetenceInvite()." 
		WHERE invited_user = '".$id_user."' AND refused = 1";
		if($id_portfolio !== false) $query .= " AND id_portfolio = '".$id_portfolio."'";
		if(!$re_invite = $this->_query($query)) return false;
		
		return $re_invite;
	}
	
	/**
	 * return the number of invitation recived 
	 * @param int $id_user idst of the user
	 * 
	 * @return int the number of invitation recived
	 */
	function getCompetenceInviteNumber($id_user, $id_portfolio = false) {
		
		$query = "
		SELECT COUNT(*)
		FROM ".$this->getTableCompetenceInvite()." 
		WHERE invited_user = '".$id_user."' AND refused = 0";
		if($id_portfolio !== false) $query .= " AND id_portfolio = '".$id_portfolio."'";
		if(!$re_invite = $this->_query($query)) return 0;
		
		list($num_invites) = sql_fetch_row($re_invite);
		
		return $num_invites;
	}
	
	/**
	 * return the list of invitation sended 
	 * @param int $id_user idst of the user
	 * @param int $id_portfolio the id of the eportfolio
	 * 
	 * @return int the number of invitation recived
	 */
	function getUserInvtedByUser($id_user, $id_portfolio = false) {
		
		$query = "
		SELECT invited_user
		FROM ".$this->getTableCompetenceInvite()." 
		WHERE sender = '".$id_user."' AND refused = 0";
		if($id_portfolio !== false) $query .= " AND id_portfolio = '".$id_portfolio."'";
		
		if(!$re_invite = $this->_query($query)) return false;
		
		return $re_invite;
	}
	
	/**
	 * create an invite
	 * @param int $id_portfolio the id of the eportfolio
	 * @param int $sender the idst of the invite sender
	 * @param int $invited_user the idst of the invited user
	 * @param string $message_text the text of the message
	 * 
	 * @return bool true if success false otherwise
	 */
	function createCompetenceInvite($invited_user, $sender, $id_portfolio, $message_text) {
		
		$query = "
		INSERT INTO ".$this->getTableCompetenceInvite()." 
		( invited_user, sender, id_portfolio, message_text, refused ) VALUES
		( 	'".$invited_user."', 
			'".$sender."', 
			'".$id_portfolio."', 
			'".$message_text."', 
			'0' )";
		if(!$re_invite = $this->_query($query)) return false;
		
		return $re_invite;
	}
	
	/**
	 * refuse an invite
	 * @param int $id_portfolio the id of the eportfolio
	 * @param int $sender the idst of the invite sender
	 * @param int $invited_user the idst of the invited user
	 * 
	 * @return bool true if success false otherwise
	 */
	function refuseCompetenceInvite($invited_user, $sender, $id_portfolio) {
		
		$query = "
		UPDATE ".$this->getTableCompetenceInvite()."
		SET refused = 1 
		WHERE sender = '".$sender."' 
			AND id_portfolio = '".$id_portfolio."' 
			AND invited_user = '".$invited_user."' ";
		
		return $this->_query($query);
	}
	
	/**
	 * delete invite
	 * @param int $id_portfolio the id of the eportfolio
	 * @param int $sender the idst of the invite sender
	 * @param int $invited_user the idst of the invited user
	 * 
	 * @return bool true if success false otherwise
	 */
	 function deleteCompetenceInvite($id_portfolio, $sender, $invited_user) {
		
		$query = "
		DELETE FROM ".$this->getTableCompetenceInvite()." 
		WHERE sender = '".$sender."' 
			AND id_portfolio = '".$id_portfolio."' 
			AND invited_user = '".$invited_user."' ";
		
		return $this->_query($query);
	}
	
	/**
	 * Retrive all the information of a presentation
	 * @param int 		$id_presentation the id of the presentation
	 * 
	 * @return array 	the array with the info if the system find the presentatin, false otherwise
	 */
	function getPresentation($id_presentation) {
	
		$query = "
		SELECT id_presentation, id_portfolio, title, textof, owner, show_pdp, show_competence, show_curriculum, pubblication_date 
		FROM ".$this->getTablePresentation()." 
		WHERE id_presentation = '".$id_presentation."'";
		
		if(!$re_prese = $this->_query($query)) return false;
		return sql_fetch_row($re_prese);
	}
	
	/**
	 * This fnction return all the presentation for a given user into a specific eportfolio 
	 * @param int $id_portfolio the id of the eportfolio
	 * @param int $id_user the id of the user
	 * 
	 * @return mixed a sql resource identifier or false
	 */
	function getQueryPresentation($id_portfolio, $id_user) {
	
		$query = "
		SELECT id_presentation, id_portfolio, title, textof, owner, show_pdp, show_competence, show_curriculum, pubblication_date
		FROM ".$this->getTablePresentation()." 
		WHERE owner = '".$id_user."' 
			AND id_portfolio = '".$id_portfolio."'";
		
		if(!$re_prese = $this->_query($query)) return false;
		
		return $re_prese;
	}
	
	/**
	 * This function will create a presentation referred to a user for an eportfolio
	 * @param int 		$id_portfolio 	the id of the eportfolio
	 * @param string 	$title 			title of the presentation
	 * @param string 	$textof 		a comment of the presentation
	 * @param int 		$owner 			owner of the presentation
	 * @param int 		$show_pdp 		if 1 in the presentation is showed the pdp
	 * @param int 		$show_competence if 1 in the presentation is showed the competence
	 * @param int 		$show_curriculum if 1 in the presentation is showed the curriculum
	 * @param string 	$publish_date 	pubblication date (Y-m-d H:i:s)
	 * 
	 * @return bool 	id if succed, false otherwise
	 */
	function createPresentation($id_portfolio, $title, $textof, $owner, $show_pdp, $show_competence, $show_curriculum, $publish_date) {
	
		$query = "
		INSERT INTO ".$this->getTablePresentation()."  
		( id_portfolio, title, textof, owner, show_pdp, show_competence, show_curriculum, pubblication_date ) VALUES 
		( 	'".$id_portfolio."', 
			'".$title."', 
			'".$textof."', 
			'".$owner."', 
			'".$show_pdp."', 
			'".$show_competence."', 
			'".$show_curriculum."', 
			'".$publish_date."'
		)";
		
		if(!$re_prese = $this->_query($query)) return false;
		return $this->_lastInsertId();
	}
	
	/**
	 * This function will update an existing presentation referred to a user for an eportfolio
	 * @param int 		$id_portfolio 	the id of the eportfolio
	 * @param string 	$title 			title of the presentation
	 * @param string 	$textof 		a comment of the presentation
	 * @param int 		$owner 			owner of the presentation
	 * @param int 		$show_pdp 		if 1 in the presentation is showed the pdp
	 * @param int 		$show_competence if 1 in the presentation is showed the competence
	 * @param int 		$show_curriculum if 1 in the presentation is showed the curriculum
	 * @param string 	$publish_date 	pubblication date (Y-m-d H:i:s)
	 * 
	 * @return bool 	true if succed, false otherwise
	 */
	function updatePresentation($id_presentation, $id_portfolio, $title, $textof, $owner, $show_pdp, $show_competence, $show_curriculum, $publish_date) {
	
		$query = "
		UPDATE ".$this->getTablePresentation()."  
		SET id_portfolio = '".$id_portfolio."', 
			title = '".$title."', 
			textof = '".$textof."', 
			owner = '".$owner."', 
			show_pdp = '".$show_pdp."', 
			show_competence = '".$show_competence."', 
			show_curriculum = '".$show_curriculum."', 
			pubblication_date = '".$publish_date."'
		WHERE id_presentation = '".$id_presentation."'";
		
		if(!$re_prese = $this->_query($query)) return false;
		return true;
	}
	
	function updatePresentationAttach($id_presentation, $id_user, $file_selection) {
		
		$old_file = $this->getPresentationAttach($id_presentation, $id_user);
		$to_add = array_diff($file_selection, $old_file);
		$to_del = array_diff($old_file, $file_selection);
		
		$result = true;
		while(list(, $add) = each($to_add)) {
			
			$query = "INSERT INTO ".$this->getTablePresentationAttach()." 
			( id_presentation, id_user , id_file) VALUES
			(	'".$id_presentation."',
				'".$id_user."',
				'".$add."' )";
				echo sql_error();
			$result &= $this->_query($query);
		}
		while(list(, $del) = each($to_del)) {
			
			$query = "DELETE FROM ".$this->getTablePresentationAttach()."  
			WHERE id_presentation = '".$id_presentation."'
				AND id_user = '".$id_user."'
				AND id_file = '".$del."'";
			$result &= $this->_query($query);
		}
		return $result;
	}
	
	function getPresentationAttach($id_presentation, $id_user) {
		
		$files = array();
		$query = "
		SELECT id_file
		FROM ".$this->getTablePresentationAttach()."  
		WHERE id_presentation = '".$id_presentation."'
			AND id_user = '".$id_user."'";
		$re_attach = $this->_query($query);
		if(!$re_attach) return $files;
		if(!sql_num_rows($re_attach)) return $files;
		
		while(list($id) = sql_fetch_row($re_attach)) {
			
			$files[$id] = $id;
		}
		return $files;
	}
	
	/**
	 * Delete a presentation and it's dependency
	 * @param int $id_presentation the id of the resentation to delete
	 * @param int $id_user identifier for the user owner of the presentation
	 * 
	 * @return bool true if the presentation was deleted correctly, false otherwise
	 */
	function delPresentation($id_presentation, $id_user = false) {
	
		$query = "
		DELETE FROM ".$this->getTablePresentation()."  
		WHERE id_presentation = '".$id_presentation."'";
		if($id_user !== false) $query .= " AND owner = '".$id_user."'";
		
		if(!$re_prese = $this->_query($query)) return false;
		return true;
	}
	
	/**
	 * save the info of the invite sended
	 * @param int 		$id_presentation 	the id of the presentation
	 * @param string 	$email 				the email of the recipent
	 * @param string 	$code 				the code of the presentation
	 * @param string 	$send_date 			the date of the invitation
	 * 
	 * @return bool true if the invite is saved correctly
	 */
	function savePresentationInvite($id_presentation, $email, $code, $send_date) {
		
		$query = "
		SELECT security_code
		FROM ".$this->getTablePresentationInvite()."  
		WHERE id_presentation = '".$id_presentation."'
			AND recipient_mail = '".$email."'";
		$re_invite = $this->_query($query);
		if(sql_num_rows($re_invite)) {
			
			$query = "
			UPDATE ".$this->getTablePresentationInvite()."  
			SET security_code = '".$code."', 
				send_date = '".$send_date."'
			WHERE id_presentation = '".$id_presentation."'
				AND recipient_mail = '".$email."'";
		
			if(!$re_prese = $this->_query($query)) return false;
			return true;
		} else {
			
			$query = "
			INSERT INTO ".$this->getTablePresentationInvite()." 
				( id_presentation, recipient_mail, security_code, send_date ) 
			VALUES ( '".$id_presentation."',
					'".$email."',
					'".$code."', 
					'".$send_date."' )";
		
			if(!$re_prese = $this->_query($query)) return false;
			return true;
		}	
	}
	
	/**
	 * check if the code is valid for the presenation assocated
	 * @param int 		$id_presentation 	the id of the presentation
	 * @param string 	$security_code 		the code of the presentation
	 * 
	 * @return bool true if the security code is valid
	 */
	function validateInvite($id_presentation, $security_code) {
		
		$query = "
		SELECT id_presentation
		FROM ".$this->getTablePresentationInvite()."  
		WHERE id_presentation = '".$id_presentation."'
			AND security_code = '".$security_code."'";
		$re_invite = $this->_query($query);
		if(sql_num_rows($re_invite)) return true;
		return false;
	}
}

/**
 * This class perform the drawing of a presentation 
 */
class EpfShowPresentation {
	
	/**
	 * @var int the id of the presentation
	 */
	var $id_presentation;
	
	/**
	 * @var int the id of the eportfolio
	 */
	var $id_portfolio;
	
	/**
	 * @var int the id of the user
	 */
	var $id_user;
	
	/**
	 * @var array the info of the presentation
	 */
	var $details;
	
	/**
	 * @var Man_Eportfolio ef manager instance
	 */
	var $man_epf;
	
	/**
	 * Update internal info of the presentation
	 * @access private
	 */
	function _setPresentationDetail() {
	
		if($this->id_presentation != false) {
			
			$this->details = $this->man_epf->getPresentation($this->id_presentation);
			$this->id_portfolio = $this->details[PRES_ID_PROTFOLIO];
			$this->id_user = $this->details[PRES_OWNER];
		} else {
			
			$this->details = false;
			$this->id_portfolio = false;
			$this->id_user = false;
		}
	}
	
	/**
	 * Class constructor, initialize the instance
	 * @param Man_Eportfolio 	$man_epf 	a valid instance of the eportfolio manager class
	 * @param int 				$id_presentation 		the id of the presentation
	 */
	function EpfShowPresentation($man_epf, $id_presentation = false) {
		
		$this->id_presentation 	= $id_presentation;
		$this->man_epf 			= $man_epf;
		
		$this->_setPresentationDetail();
	}
	
	/**
	 * Change the internal instance of the class Man_Eportfolio, update the presentation info
	 * @param Man_Eportfolio 	$man_epf 	a valid instance of the eportfolio manager class
	 */
	function changeEpfManager($man_epf) {
		
		$this->man_epf = $man_epf;
		$this->_setPresentationDetail();
	}
	
	/**
	 * Change the presentation, update the presentation info
	 * @param int $id_presentation the id of the presentation
	 */
	function changePresentation($id_presentation) {
		
		$this->id_presentation = $id_presentation;
		$this->_setPresentationDetail();
	}
	
	function getTitle() {
		
		return $this->details[PRES_TITLE];
	}
	
	function getOwnerComment() {
	
		return $this->details[PRES_TEXTOF];
	}
	
	function getPdp() {
		
		$html = '';
		if($this->details[PRES_SHOW_PDP] == '0') return $html;
		
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('eportfolio');
		
		$re_pdp = $this->man_epf->getQueryPdpOfEportfolio($this->id_portfolio);
		
		if(!$re_pdp || !sql_num_rows($re_pdp)) return $html;
		
		// print presentation -------------------------------------------------------------
				
		$tb = new Table(0, '', $lang->def('_SUMMARY_PDP_ANSWER'));
		$tb->setTableStyle('epf_answer');
		
		$tb->setColsStyle(array('epf_post_date', '') );
		$tb->addHead(array($lang->def('_POST_DATE'), $lang->def('_ANSWER')) );
		
		while($row = sql_fetch_row($re_pdp)) {
			
			$re_pdp_answer 	= $this->man_epf->getQueryPdpUserAnswer($row[PDP_ID], getLogUserId());
			$num_answer 	= sql_num_rows($re_pdp_answer);
			
			$html .= '<div class="pdp_question_display">';
			
			if($num_answer != 0) {
				
				$tb->setCaption($row[PDP_TEXTOF]);
				$tb->emptyBody();
				while($answer = sql_fetch_row($re_pdp_answer)) {
					$cont = array(
						Format::date($answer[PDP_ANSWER_POST_DATE], 'date'), 
						$answer[PDP_ANSWER_TEXTOF]
					);
					$tb->addBody($cont);
				}
				$html .= $tb->getTable();
			} else {
				
				$html .= '<h2>'.$row[PDP_TEXTOF].'</h2>';
			}
			$html .= '</div>';
		}
		
		return $html;
		
	}
	
	function getCompetence() {
		
		$html = '';
		if($this->details[PRES_SHOW_COMPETENCE] == '0') return $html;
			
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('eportfolio');
		
		$re_competence = $this->man_epf->getQueryCompetenceOfEportfolio($this->id_portfolio);
		
		$self_score = array();
		// retrive only the score assigned to the user bby itself
		$re_user_score = $this->man_epf->getDetailedCompetenceScore($this->id_portfolio, $this->id_user, $this->id_user);
		
		while($s_row = sql_fetch_row($re_user_score)) {
			
			if($s_row[COMPETENCE_SCORE_STATUS] == C_SCORE_VALID) {
				
				$score = $s_row[COMPETENCE_SCORE_SCORE];
				
				$self_score[$s_row[COMPETENCE_SCORE_ID_COMPETENCE]]['score'] = $s_row[COMPETENCE_SCORE_SCORE];
				$self_score[$s_row[COMPETENCE_SCORE_ID_COMPETENCE]]['comment'] = $s_row[COMPETENCE_SCORE_COMMENT];
			}
		}
		
		$tb = new Table(0, $lang->def('_CAPTION_COMPETENCE_SCORE'), $lang->def('_SUMMARY_COMPETENCE_SCORE'));
		$tb->setTableStyle('epf_competence_score');
		
		$type_h = array('', 'competence_score', '');
		$cont_h = array(	$lang->def('_TEXTOF_COMPETENCE'),
							$lang->def('_SELF_EVALUATION'), 
							$lang->def('_MY_COMMENT') );
		
		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);
		while($competence = sql_fetch_row($re_competence)) {
			
			$cont = array($competence[COMPETENCE_TEXTOF]);
			if(isset($self_score[$competence[COMPETENCE_ID]])) {
				
				$cont[] = $self_score[$competence[COMPETENCE_ID]]['score'];
				$cont[] = $self_score[$competence[COMPETENCE_ID]]['comment'];
			} else {
				
				$cont[] = '';
				$cont[] = '';
			}
			
			$tb->addBody($cont);
		}
		$html .= $tb->getTable();
		return $html;
	}
	
	function getCurriculum($ext = false, $code = false) {
		
		$html = '';
		if($this->details[PRES_SHOW_CURRICULUM] == '0') return $html;
		
		$lang =& DoceboLanguage::createInstance('eportfolio');
		
		$curriculum = $this->man_epf->getCurriculum($this->id_portfolio, $this->id_user);
		
		if($curriculum === false) {
			 
			$html .= '<p class="curriculum_not_loaded">'
					.'<i>'.$lang->def('_NO_CURRICULUM').'</i>'
				.'</p>';
		} else {
			
			if($ext) $link = 'index.php?modname=eportfolio&amp;type=ext&amp;op=downloadcurriculum&amp;id_presentation='.$this->id_presentation.'&amp;id_portfolio='.$this->id_portfolio.'&amp;id_user='.$this->id_user.'&amp;code='.$code.'&amp;no_redirect=1';
			else $link = 'index.php?modname=eportfolio&amp;op=downloadcurriculum&amp;id_portfolio='.$this->id_portfolio.'&amp;id_user='.$this->id_user;
			
			$html .= '<p class="curriculum_loaded">'
					.' <a class="down_curriculum" href="'.$link.'">'
					.$lang->def('DOWNLOAD_USER_CURRICULUM').'</a>'
					.'</p>';
		}
		return $html;
	}
	
	function getAttachedFile($ext = false, $code = false) {
		
		$html = '';
		require_once($GLOBALS['where_framework'].'/lib/lib.myfiles.php');
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('eportfolio');
		
		$file_man = new  MyFile($this->id_user);
		$files = $this->man_epf->getPresentationAttach($this->id_presentation, $this->id_user);
		
		if(!count($files)) return $html;
		
		$files_info = $file_man->getFilteredFileList($files);
		
		// print presentation -------------------------------------------------------------
				
		$tb = new Table(0, '', $lang->def('_SUMMARY_FILES'));
		$tb->setTableStyle('epf_files');
		
		$tb->setColsStyle(array('') );
		$tb->addHead(array($lang->def('_NAME')) );
		
		while($row = sql_fetch_row($files_info)) {
		
			if($ext) $link = 'index.php?modname=eportfolio&amp;type=ext&amp;op=downloadfile&amp;id_presentation='.$this->id_presentation.'&amp;id_portfolio='.$this->id_portfolio.'&amp;id_user='.$this->id_user.'&amp;id_file='.$row[MYFILE_ID_FILE].'&amp;code='.$code.'&amp;no_redirect=1';
			else $link = 'index.php?modname=eportfolio&amp;op=downloadfile&amp;id_portfolio='.$this->id_portfolio.'&amp;id_user='.$this->id_user.'&amp;id_file='.$row[MYFILE_ID_FILE];
			$cont = array('<a href="'.$link.'">'.$row[MYFILE_TITLE].'</a>');
			
			$tb->addBody($cont);
		}
		$html .= $tb->getTable();
		
		return $html;
	}
	
}

?>