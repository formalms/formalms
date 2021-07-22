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

class ReservationRoomPermissions {

	function ReservationRoomPermissions()
	{
	}
	
	function _getReservationPermTable()
	{
		return 'learning_reservation_perm';
	}
	
	function addReservationPerm($perm, $event_id, $idst_arr) {
		$res=TRUE;
		
		if (empty($perm))
			return FALSE;
		
		foreach($idst_arr as $user_idst)
		{
			$qtxt ="INSERT INTO ".$this->_getReservationPermTable()." (event_id, user_idst, perm) ";
			$qtxt.="VALUES ('".$event_id."', '".$user_idst."', '".$perm."')";
			
			$q=sql_query($qtxt);
			if (!$q)
				$res=FALSE;
		}
		
		return $res;
	}
	
	function removeReservationPerm($perm, $event_id, $idst_arr) {
		$res=TRUE;

		if (empty($perm))
			return FALSE;

		if ((is_array($idst_arr)) && (count($idst_arr) > 0)) {

			$qtxt ="DELETE FROM ".$this->_getReservationPermTable()." WHERE event_id='".$event_id."' AND ";
			$qtxt.="perm='".$perm."' AND ";
			$qtxt.="user_idst IN (".implode(",", $idst_arr).")";

			$q=sql_query($qtxt);
			if (!$q)
				$res=FALSE;
		}

		return $res;
	}
	
	function getAllReservationPerm($event_id) {
		$res=array();

		$fields="user_idst, perm";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getReservationPermTable()." WHERE ";
		$qtxt.="event_id='".$event_id."'";

		$q=sql_query($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			while ($row=sql_fetch_assoc($q)) {

				$user_idst=$row["user_idst"];
				$perm=$row["perm"];
				$res[$perm][$user_idst]=$user_idst;

			}
		}

		return $res;
	}
}


?>
