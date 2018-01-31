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

require_once($GLOBALS['where_lms'].'/class.module/track.object.php');

class Track_Poll extends Track_Object {
	
	/** 
	 * object constructor
	 * Table : learning_commontrack
	 * id_reference | idUser | id_track | objectType | date_attempt  | status |
	 **/
	function Track_Poll( $id_track, $idResource = false, $idParams = false, $back_url = NULL ) {
		$this->objectType = 'poll';
		parent::Track_Object($id_track);
		
		$this->idResource = $idResource;
		$this->idParams = $idParams;
		if($back_url === NULL) $this->back_url = array();
		else $this->back_url = $back_url;
	}
	
	
	/**
	 * function createTrack( $idUser, $idTest, $idReference )
	 *
	 * create a new row in the _testtrack table for tracking purpose
	 *
	 * @param int	$idUser			the id of the user that display the object
	 * @param int	$idTest			the id of the test that is displayed
	 * @param int	$idReference	the idReference from the table of the lesson
	 *
	 * @return int	idTrack if the row is created correctly otherwise false 
	 **/
	function createNewTrack( $id_user, $id_resource, $idReference ) {
		if ($_SESSION['levelCourse'] < 6)
		{
			$query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_polltrack 
			SET id_user = '".(int)$id_user."', 
				id_poll = '".(int)$id_resource."', 
				id_reference = '".(int)$idReference."', 
				date_attempt = '".date("Y-m-d H:i:s")."'";
			if(!sql_query($query)) return false;
			
			list($idTrack) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			if(!$idTrack) return false;
			else return $idTrack;
		}
		return 0;
	}
	
	/**
	 * @return id_track if exists or false 
	 **/
	function getTrack( $id_reference, $id_resource, $id_user ) {
		
		
		$query = "
		SELECT id_track 
		FROM ".$GLOBALS['prefix_lms']."_polltrack
		WHERE id_reference='".(int)$id_reference."' AND id_poll='".(int)$id_resource."' AND id_user='".(int)$id_user."'";
		$rs = sql_query( $query )
			or errorCommunication( 'Learning_Poll.existTrack' );
		
		if( sql_num_rows( $rs )  > 0 ) {
			list( $id_track ) = sql_fetch_row( $rs );
			return $id_track;
		}
		else return false;
	}
        
        function getIdTrack( $id_reference, $idUser, $idResource, $createOnFail = FALSE ) {
                
                $rsTrack = $this->getTrack($id_reference,$idResource,$idUser);
                if( $rsTrack !== FALSE ) {
                    return array( TRUE, $rsTrack );
                } else if ($createOnFail) {
                        $rsTrack = $this->createNewTrack( $idUser, $idResource, $id_reference );
                        return array( FALSE, $rsTrack );
                }
		return FALSE;
	}
	
	/**
	 * @return id_track if create row else false 
	 **/
	function setTrack( $id_reference, $id_resource, $id_user ) {
		
		
		$query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_polltrack
		SET id_poll = '".(int)$id_resource."',
			id_reference = '".(int)$id_reference."',
			id_user = '".(int)$id_user."',
			data_attempt = '".date("Y-m-d H:i:s")."'";
		if(!sql_query($query)) return false;
		
		list( $id_track ) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if($id_track) return $id_track;
		else return false;
	}
	
	/**
	 * function updateTrack( $idTrack, $new_info )
	 *
	 * create a new row in the _testtrack table for tracking purpose
	 *
	 * @param int	$idTrack		the track of the object
	 * @param array	$new_info		an array with the new information
	 *
	 * @return bool	true if success false otherwise 
	 **/
	function updateTrack( $idTrack, $new_info ) {
		
		
		$first = true;
		if(!is_array($new_info)) return true;
		$query = "
		UPDATE ".$GLOBALS['prefix_lms']."_polltrack 
		SET ";
		foreach($new_info as $field_name => $field_value) {
			
			$query .= ( $first ? '' : ', ' ).$field_name." = '".$field_value."'";
			if($first) $first = false;
		}
		$query .= " WHERE id_track = '".(int)$idTrack."'";
		if(!sql_query($query)) return false;
		else return true;
	}
	
	/**
	 * print in standard output 
	 **/
	function loadObjectReport( $mvc = false ) {
		
		require_once($GLOBALS['where_lms'].'/modules/poll/do.poll.php' );
		$output = writePollReport( $this->idResource, $this->idParams, $this->back_url, $mvc );
		if ($mvc) return $output;
	}
}

?>
