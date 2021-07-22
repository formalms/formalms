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

class GroupScormObjMan {
	
	function GroupScormObjMan() {}
	
	
	/**
	 * returns the users score for a list of scorm obj
	 * @param array		$id_scorms		an array with the id of the scorm obj for which the function must retrive scores
	 * @param array		$id_students	the students of the course 
	 *
	 * @return array 	a matrix with the index [id_scorm] [id_user] and values array( score, max_score )
	 */
	function &getSimpleScormScores($id_scorms, $id_students = false) {
		
		$data = array();
		if(empty($id_scorms)) return $data;
		if(empty($id_students)) $id_students = false;
		$query_scores = "
		SELECT idReference, idUser, score_raw, score_max 
		FROM ".$GLOBALS['prefix_lms']."_scorm_tracking 
		WHERE idReference IN ( ".implode(',', $id_scorms)." ) ";
		if($id_students !== false) $query_scores .= " AND idUser IN ( ".implode(',', $id_students)." )";
		$query_scores .= " AND score_raw > 0 AND score_raw IS NOT NULL ";
		$re_scores = sql_query($query_scores);
		while($scorm_data = sql_fetch_assoc($re_scores)) {
			
			$data[$scorm_data['idReference']][$scorm_data['idUser']]['score'] = $scorm_data['score_raw'];
			$data[$scorm_data['idReference']][$scorm_data['idUser']]['max_score'] = $scorm_data['score_max'];
		}
		return $data;
	}
	
}

?>