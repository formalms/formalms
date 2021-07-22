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
 * Class for tracking purpose
 */

class TrackUser {
	
	function createSessionCourseTrack() {
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return;
		
		$now = date("Y-m-d H:i:s");
		//retriving last access to thecourse
		list($last_course_access) = sql_fetch_row(sql_query("
		SELECT UNIX_TIMESTAMP(MAX(lastTime)) 
		FROM ".$GLOBALS['prefix_lms']."_tracksession 
		WHERE idCourse = '".$_SESSION['idCourse']."' AND idUser = '".getLogUserId()."'"));
		$_SESSION['lastCourseAccess'] = $last_course_access;
		
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_tracksession
		SET active = 0
		WHERE idUser = ".(int)getLogUserId()." and active = 1");
		
		sql_query("
		INSERT INTO ".$GLOBALS['prefix_lms']."_tracksession 
		( idCourse, idUser, session_id, enterTime, lastTime, ip_address, active ) VALUES ( 
			'".$_SESSION['idCourse']."', 
			'".getLogUserId()."',
			'".''/*session_id()*/."',
			'$now',
			'$now',
			'".$_SERVER['REMOTE_ADDR']."',
			1 ) ");
		list($id) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if($id) $_SESSION['id_enter_course'] = $id;
	}
	
	function setActionTrack($id_user, $id_course, $mod_name, $mode) {
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return;
		
		$now = date("Y-m-d H:i:s");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_tracksession 
		SET numOp = numOp+1, 
			lastFunction = '".$mod_name."', 
			lastOp = '".$mode."', 
			lastTime = '".$now."',
			ip_address = '".$_SERVER['REMOTE_ADDR']."'
		WHERE idEnter = '".$_SESSION['id_enter_course']."' "
			."AND idCourse = '".$id_course."' AND idUser = '".$id_user."'");
		
		if(Get::sett('tracking') == 'on' && $_SESSION['levelCourse'] != '2') {
			
			$query_track = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_trackingeneral
			( idUser, idEnter, idCourse, function, type, timeof, session_id, ip ) VALUES (
				'".$id_user."',
				'".$_SESSION['id_enter_course']."',
				'".$id_course."',
				'".$mod_name."',
				'".$mode."',
				'".$now."',
				'".''/*.session_id()*/."',
				'".$_SERVER['REMOTE_ADDR']."' )";
			sql_query($query_track);
		}
	}
	
	function closeSessionCourseTrack() {
		
		TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], '_COURSE_LIST', 'view');
	}
	
	
	function logoutSessionCourseTrack() {
		
		if(isset($_SESSION['idCourse'])) {
			TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], '_LOGOUT', 'view');
		}
	}
	
	function getUserTotalCourseTime($idst_user, $id_course){
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return 0;
		
		$tot_time = 0;
		$query_time = "
		SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))
		FROM ".$GLOBALS['prefix_lms']."_tracksession 
		WHERE idCourse = '".$id_course."' AND idUser = '".$idst_user."'";
		$re = sql_query($query_time);
		if($re && sql_num_rows($re)) list($tot_time) = sql_fetch_row(sql_query($query_time));
		
		return $tot_time;
	}
	
	function getUserPreviousSessionCourseTime($idst_user, $id_course){
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return 0;
		
		$tot_time = 0;
		$query_time = "
		SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))
		FROM ".$GLOBALS['prefix_lms']."_tracksession 
		WHERE idCourse = '".$id_course."' AND idUser = '".$idst_user."' "
				." AND idEnter <> '".$_SESSION['id_enter_course']."'";
		$re = sql_query($query_time);
		if($re && sql_num_rows($re)) list($tot_time) = sql_fetch_row($re);
		
		return $tot_time;
	}
	
	function getUserCurrentSessionCourseTime($id_course) {
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return 0;
		
		if(isset($_SESSION['id_enter_course'])) {
			
			$query_time = "
			SELECT UNIX_TIMESTAMP(enterTime)
			FROM ".$GLOBALS['prefix_lms']."_tracksession 
			WHERE idCourse = '".$id_course."' AND idUser = '".getLogUserId()."' 
				AND idEnter = '".$_SESSION['id_enter_course']."'";
			list($partial_time) = sql_fetch_row(sql_query($query_time));
			
			return time() - $partial_time;
		} else return false;
	}
	
	/**
	 * @param int	$id_course	id of thecourse
	 * @param int	$gep_time 	minute of last action
	 *
	 * @return int the number of user in the course in the gap of time (logged included)
	 */
	function getWhoIsOnline($id_course, $gap_minute = 5) {
		
		$gap_time = date("Y-m-d H:i:s", time() - ( 60 * $gap_minute ));
		$query_time = "
		SELECT COUNT(DISTINCT idUser)
		FROM ".$GLOBALS['prefix_lms']."_tracksession 
		WHERE idCourse = '".$id_course."' AND active = 1 AND lastTime > '".$gap_time."'";
		list($who_is_online) = sql_fetch_row(sql_query($query_time));
		
		return $who_is_online;
	}
	
	/* the same as above, returning also the array of the idSt's of the users online */
	function getListWhoIsOnline($id_course, $gap_minute = 5) {
		
		$gap_time = date("Y-m-d H:i:s", time() - ( 60 * $gap_minute ));
		$query_time = "
		SELECT DISTINCT idUser
		FROM ".$GLOBALS['prefix_lms']."_tracksession 
		WHERE idCourse = '".$id_course."' AND active = 1 AND (lastTime) > '".$gap_time."'";
		
		$result=sql_query($query_time);
		$who_is_online_list=array();
		while ($row=sql_fetch_array($result)) {
			$who_is_online_list[]=$row["idUser"];
		};
		
		return $who_is_online_list;
	}
	
	function getLastAccessToCourse($id_user) {
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return  0;
		
		$last_access = array();
		$query_time = "
		SELECT idCourse, UNIX_TIMESTAMP(MAX(lastTime)) 
		FROM ".$GLOBALS['prefix_lms']."_tracksession 
		WHERE idUser = '".$id_user."' 
		GROUP BY idCourse";
		$re_time = sql_query($query_time);
		while(list($id_c, $access) = sql_fetch_row($re_time)) {
			
			$last_access[$id_c] = $access;
		}
		return $last_access;
	}
	
	function checkSession($id_user) {
		
		if(isset($_SESSION['is_ghost']) &&  $_SESSION['is_ghost'] === true) return true;
		
		if(isset($_SESSION['id_enter_course'])) {
			
			$query_time = "
			SELECT COUNT(*) 
			FROM ".$GLOBALS['prefix_lms']."_tracksession 
			WHERE idUser = '".$id_user."' AND idEnter = '".$_SESSION['id_enter_course']."' "
					." AND active = 1";
			list($num_active) = sql_fetch_row(sql_query($query_time));
			
			return ($num_active == 1);
		} else return true;
	}
	
	function resetUserSession($id_user) {
		
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_tracksession 
		SET active = 0 
		WHERE idUser = '".$id_user."'");
		
	}
	
}

?>