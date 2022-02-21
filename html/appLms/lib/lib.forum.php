<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Man_Forum {
	
	function getCountUnreaded($id_user, $courses, $last_access) {
		/*
		//$time_start = getmicrotime();
		$unreaded = array();
		if(empty($courses)) return $unreaded;
		
		$reLast = sql_query("
		SELECT idCourse, new_forum_post
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".$id_user."' AND idCourse IN ( ".implode(',', $courses)." ) ");
		while(list($id_c, $new_post) = sql_fetch_row($reLast)) {
			
			list($unreaded[$id_c]) = $new_post;
		}
		return $unreaded;*/
		
			
		//$time_start = getmicrotime();
		$unreaded = [];
		if(empty($courses)) return $unreaded;
		
		$reLast = sql_query("
		SELECT idCourse, new_forum_post
		FROM ".$GLOBALS['prefix_lms']."_courseuser
		WHERE idUser = '".$id_user."' AND idCourse IN ( ".implode(',', $courses)." ) ");
		while(list($id_c, $new_post) = sql_fetch_row($reLast)) {
			
			list($unreaded[$id_c]) = $new_post;
		}
		return $unreaded;
	}
	
	function getUserForumPostLms($id_user) {
		
		$query_forum_post="
			SELECT COUNT(*)
			FROM ".$GLOBALS['prefix_lms']."_forummessage
			WHERE author = '".$id_user."'";
		
		$forum_post = sql_fetch_row(sql_query($query_forum_post));
		
		return $forum_post[0];
	}

}

?>