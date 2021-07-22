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
 * @package DoceboCore
 * @subpackage user_management 
 * @category library
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.1.0
 * 
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,GEF,EMF], tabwidth = 4, font = Courier New )
 */

define("MYFRIEND_ID_USER", 0);
define("MYFRIEND_ID_FRIEND", 1);
define("MYFRIEND_WAITING", 2);
define("MYFRIEND_REQUEST", 3);

define("MF_APPROVED", 0);
define("MF_WAITING", 1);

class MyFriends {
	
	/**
	 * the main user
	 * @access private
	 */
	var $_id_user;
	
	var $arr_field = array(
		MYFRIEND_ID_USER	=> 'id_user', 
		MYFRIEND_ID_FRIEND 	=> 'id_friend', 
		MYFRIEND_WAITING 	=> 'waiting',
		MYFRIEND_REQUEST 	=> 'request_msg' 
	);
	
	// methods -----------------------------------------------------------------------
	
	function _getFriendsTable() { return $GLOBALS['prefix_fw'].'_user_friend'; }

	function _query($query) {
		
		$re_query = sql_query($query);
		return $re_query;
	}
	
	function _last_id() 			{ return sql_insert_id(); }
	
	function num_rows($resource) 	{ return sql_num_rows($resource); }
	
	function fetch_row($resource) 	{ return sql_fetch_row($resource); }
	
	function fetch_array($resource) { return sql_fetch_array($resource); }
		
	function getUser() 				{ return $this->id_user; } 
	
	function setUser($id_user) 		{ return $this->id_user = $id_user; } 
	
	/**
	 * class constructor
	 */
	function MyFriends($id_user) {
		
		ksort($this->arr_field);
		reset($this->arr_field);
		$this->_id_user = $id_user;
	}
	
	/**
	 * give the complete list of the user's friends
	 * @param int 	$limit 			limit the result to this number
	 * @param bool 	$random_order 	if true the result will be randomized in order
	 * @param mixed	$waiting		if false, return all friend, if MF_APPROVED return only approved friend, if MF_WAITING return waiting friend 	
	 * @param mixed	$waiting		if true the return array will contains only the id of the friend and not the waiting status
	 *  
	 * @return array the list of the idst of the user 's friends
	 */
	function getFriendsList($limit = false, $random_order = false, $waiting = '0', $only_id = false) {
		
		$query = "
		SELECT ".$this->arr_field[MYFRIEND_ID_FRIEND].", ".$this->arr_field[MYFRIEND_WAITING]." 
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_USER]." = '".$this->_id_user."' ";
		if($waiting !== false) $query .= " AND ".$this->arr_field[MYFRIEND_WAITING]." = '".$waiting."'";
		
		if($random_order !== false) $query .= " ORDER BY RAND() ";
		if($limit !== false) $query .= " LIMIT 0,".$limit;
		
		$friends = array();
		if(!$re_query = $this->_query($query)) return $friends;
		while(list($id,$waiting ) = $this->fetch_row($re_query)) {
			
			if($waiting === false || $only_id === true) $friends[$id] = $id;
			else $friends[$id] = array('id' => $id, 'waiting' => $waiting );
		}
		return $friends;
	}
	
	/**
	 * give the complete list of the user's friends
	 * @param int 	$limit 			limit the result to this number
	 * @param bool 	$random_order 	if true the result will be randomized in order
	 * @param mixed	$waiting		if false, return all friend, if MF_APPROVED return only approved friend, if MF_WAITING return waiting friend 	
	 * @param mixed	$waiting		if true the return array will contains only the id of the friend and not the waiting status
	 *  
	 * @return array the list of the idst of the user 's friends in 'effective' and the waiting in 'waiting'
	 */
	function getAllFriendsSubdivided() {
		
		$query = "
		SELECT ".$this->arr_field[MYFRIEND_ID_FRIEND].", ".$this->arr_field[MYFRIEND_WAITING]." 
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_USER]." = '".$this->_id_user."' ";
		
		$friends = array('waiting' => array(), 'effective' => array());
		if(!$re_query = $this->_query($query)) return $friends;
		while(list($id, $waiting) = $this->fetch_row($re_query)) {
			
			if($waiting) $friends['waiting'][$id] = $id;
			else $friends['effective'][$id] = $id;
		}
		return $friends;
	}
	
	/**
	 * give the complete list of the user's friends
	 * @param int 	$limit 			limit the result to this number
	 * @param bool 	$random_order 	if true the result will be randomized in order
	 * @param mixed	$waiting		if false, return all friend, if MF_APPROVED return only approved friend, if MF_WAITING return waiting friend 	
	 * @param mixed	$waiting		if true the return array will contains only the id of the friend and not the waiting status
	 *  
	 * @return array the list of the idst of the user 's friends in 'effective' and the waiting in 'waiting'
	 */
	function getAllFriendsSubdividedForUsers($arr_user) {
		
		$query = "
		SELECT id_user, ".$this->arr_field[MYFRIEND_ID_FRIEND].", ".$this->arr_field[MYFRIEND_WAITING]." 
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_USER]." IN ( ".implode(',', $arr_user)." ) ";
		
		$friends = array('waiting' => array(), 'effective' => array());
		if(!$re_query = $this->_query($query)) return $friends;
		while(list($id_user, $friend, $waiting) = $this->fetch_row($re_query)) {
			
			if($waiting) $friends[$id_user]['waiting'][$friend] = $friend;
			else $friends[$id_user]['effective'][$friend] = $friend;
		}
		return $friends;
	}
	
	function getFriendsCount($waiting = '0') {
		
		$query = "
		SELECT COUNT(*) 
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_USER]." = '".$this->_id_user."' ";
		if($waiting !== false) $query .= " AND ".$this->arr_field[MYFRIEND_WAITING]." = '".$waiting."'";
		
		if(!$re_query = $this->_query($query)) return 0;
		list($number) = $this->fetch_row($re_query);
		return $number;
	}	
	
	/**
	 * return the userid/username of the user that are friends
	 */
	function &getFriendsInfo($arr_id_friends = false, $limit = false, $waiting = 0) {
		
		if($arr_id_friends === false) $arr_id_friends = $this->getFriendsList($limit, false, $waiting, true);
		
		$acl_man =& Docebo::user()->getAclManager();
		
		$users_info =& $acl_man->getUsers($arr_id_friends);
		return $users_info;
	}
	/*
	 * Control if in the Pendent Request there is a request from your friend.
	 * If there is change the flag "MYFRIEND_WAITING" to "MF_APPROVED"
	*/
	function controlPendentRequest()
	{
		$controlled_id = array();
		$control_query = "
			SELECT ".$this->arr_field[MYFRIEND_ID_USER]."
			FROM ".$this->_getFriendsTable()."
			WHERE ".$this->arr_field[MYFRIEND_WAITING]." = '".MF_WAITING."'
				AND ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$this->_id_user."'
				AND ".$this->arr_field[MYFRIEND_ID_USER]." IN 
				(
					SELECT ".$this->arr_field[MYFRIEND_ID_FRIEND]."
					FROM ".$this->_getFriendsTable()."
					WHERE ".$this->arr_field[MYFRIEND_ID_USER]." = '".$this->_id_user."'
						AND ".$this->arr_field[MYFRIEND_WAITING]." = '".MF_APPROVED."'
				)";
		
		$control = $this->_query($control_query);
		while (list($control_id_user) = $this->fetch_row($control))
		{
			$update_query = "
				UPDATE ".$this->_getFriendsTable()."
				SET ".$this->arr_field[MYFRIEND_WAITING]." = '".MF_APPROVED."',
					".$this->arr_field[MYFRIEND_REQUEST]." = ''
				WHERE ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$this->_id_user."' 
				AND ".$this->arr_field[MYFRIEND_ID_USER]." = '".$control_id_user."'";
			
			$controlled_id[] = $control_id_user;
			$Control_update = $this->_query($update_query);
		}
		return $controlled_id;
	}
	/**
	 * return the npendent request
	 */
	function getPendentRequest() {
		$query = "
		SELECT ".$this->arr_field[MYFRIEND_ID_USER].", ".$this->arr_field[MYFRIEND_REQUEST]." 
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$this->_id_user."' 
			 AND ".$this->arr_field[MYFRIEND_WAITING]." = '".MF_WAITING."'";
		
		$friends = array();
		if(!$re_query = $this->_query($query)) return $friends;
		while(list($id, $request) = $this->fetch_row($re_query)) {
			
			$friends[$id] = array('id' => $id, 'request' => $request );
		}
		return $friends;
	}
	
	
	/**
	 * check if the user is a friend
	 * @param itn $id_friend the idst of the user to check
	 * 
	 * @return bool true if the user is a friend, false otherwise
	 */
	function getPendentRequestCount() {
				
		$query = "
		SELECT COUNT(*)
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$this->_id_user."' 
			 AND ".$this->arr_field[MYFRIEND_WAITING]." = '".MF_WAITING."'";
		
		if(!$re_query = $this->_query($query)) return 0;
		list($number) = $this->fetch_row($re_query);
		return $number;
	}
	
	function approveFriend($id_friend) {
		
		$query = "
		UPDATE ".$this->_getFriendsTable()."
		SET ".$this->arr_field[MYFRIEND_WAITING]." = '".MF_APPROVED."',
			".$this->arr_field[MYFRIEND_REQUEST]." = ''
		WHERE ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$this->_id_user."' 
			 AND ".$this->arr_field[MYFRIEND_ID_USER]." = '".$id_friend."'";
		
		if(!$re_query = $this->_query($query)) return false;
		return true;
	}
	
	function refuseFriend($id_friend) {
		
		$query = "
		DELETE FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$this->_id_user."' 
			 AND ".$this->arr_field[MYFRIEND_ID_USER]." = '".$id_friend."'";
		
		if(!$re_query = $this->_query($query)) return false;
		return true;
	}
	
	/**
	 * check if the user is a friend
	 * @param itn $id_friend the idst of the user to check
	 * 
	 * @return bool true if the user is a friend, false otherwise
	 */
	function isFriend($id_friend, $also_waiting = false) {
		
		$query = "
		SELECT ".$this->arr_field[MYFRIEND_ID_FRIEND]." 
		FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_USER]." = '".$this->_id_user."'
			AND ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$id_friend."'";
		if($also_waiting === false) $query .= " AND waiting = '".MF_APPROVED."'";
		
		if(!$re_query = $this->_query($query)) return false;
		
		return ($this->num_rows($re_query) != 0);
	}
	
	/**
	 * add a new friend to the user friend list
	 * @param int $id_friend the id of the user to add
	 * 
	 * @return bool true if the user is added, false otherwise
	 */
	function addFriend($id_friend, $waiting, $request_text) {
		
		if($this->isFriend($id_friend)) return true;
		
		$query = "INSERT INTO ".$this->_getFriendsTable()."
		( ".$this->arr_field[MYFRIEND_ID_USER].", ".$this->arr_field[MYFRIEND_ID_FRIEND].", ".$this->arr_field[MYFRIEND_WAITING]."
				, ".$this->arr_field[MYFRIEND_REQUEST]." ) VALUES (
			'".$this->_id_user."',
			'".$id_friend."', 
			'".$waiting."',
			'".$request_text."'
		)";
		return $this->_query($query);
	}
	
	/**
	 * remove friend from the user friend list
	 * @param int $id_friend the id of the user to remove
	 * 
	 * @return bool true if the user is removed, false otherwise
	 */
	function delFriend($id_friend) {
		
		$query = "
		DELETE FROM ".$this->_getFriendsTable()."
		WHERE ".$this->arr_field[MYFRIEND_ID_USER]." = '".$this->_id_user."'
			AND ".$this->arr_field[MYFRIEND_ID_FRIEND]." = '".$id_friend."'";
		return $this->_query($query);
	}
	
}

?>