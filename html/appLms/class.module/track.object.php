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

class Track_Object {
	
	var $idTrack;
	var $idReference;
	var $idUser;
	var $dateAttempt;
	var $status;
	var $firstAttempt;
	var $first_complete;
	var $last_complete;

	var $objectType;
	var $environment = 'course_lo';

	var $_table = '';
	
	/** 
	 * object constructor
	 * Table : learning_commontrack
	 * idReference | idUser | idTrack | objectType | date_attempt  | status |
	 **/
	function Track_Object( $idTrack, $environment = false ) {

		$this->environment = ( $environment ? $environment : 'course_lo' );
		$this->_table = $this->getEnvironmentTable($environment);
		if($idTrack) {
			
			$this->idTrack = $idTrack;
			$query = "SELECT `idReference`, `idUser`, `idTrack`, `objectType`, `dateAttempt`, `status`, `firstAttempt`, `first_complete`, `last_complete` "
					." FROM `".$this->_table."`"
					." WHERE idTrack='".(int)$idTrack."'"
					."   AND objectType='".$this->objectType."'";
			$rs = sql_query( $query ) or
					errorCommunication( 'Track_Object.Track_Object' );
			if( sql_num_rows( $rs ) == 1 ) {
				list( $this->idReference, $this->idUser, $this->idTrack, 
					  $this->objectType, $this->dateAttempt, $this->status ) = sql_fetch_row( $rs );
			}
		}
	}

	function getEnvironmentTable($environment) {

		switch($environment) {
			case "communication" : {
				return $GLOBALS['prefix_lms']."_communication_track";
			};break;
			case "games" : {
				return $GLOBALS['prefix_lms']."_games_track";
			};break;
			case "course_lo" :
			default : {
				return $GLOBALS['prefix_lms']."_commontrack";
			};break;
		}
	}

	function setEnvGamesData($id_user, $id_reference, $score, $objectType) {

		// find prev info
		$query = "SELECT max_score "
				."FROM ".self::getEnvironmentTable('games')." "
				."WHERE objectType = '".$objectType."' "
				."	AND idReference = ".(int)$id_reference." "
				."	AND idUser = ".(int)$id_user." ";
		list($max_score) = sql_fetch_row( sql_query($query) );

		$query = "UPDATE ".self::getEnvironmentTable('games')." SET "
				." current_score = '".$score."', "
				." num_attempts = num_attempts + 1 "
				.( $score > $max_score ? ", max_score = '".$score."' " : '' )
				."WHERE objectType = '".$objectType."' "
				."	AND idReference = ".(int)$id_reference." "
				."	AND idUser = ".(int)$id_user."";
		sql_query( $query );
	}

	/** 
	 * object constructor
	 * @return bool
	 * create a row in global track
	 **/
	function createTrack( $idReference, $idTrack, $idUser, $dateAttempt, $status, $objectType = FALSE ) {
		
		if(!$idReference || !$idTrack || !$idUser) return false;
		if(isset($this)) $table = $this->_table;
		else $table = self::getEnvironmentTable('course_lo');
		$query = "INSERT INTO ".$table." "
				."( `idReference`, `idUser`, `idTrack`, `objectType`, `firstAttempt`, `dateAttempt`, `status` )"
				." VALUES ("
				." '".(int)$idReference."',"
				." '".(int)$idUser."',"
				." '".(int)$idTrack."',"
				." '".(($objectType==FALSE)?($this->objectType):($objectType))."',"
				." '".date("Y-m-d H:i:s")."', "
				." '".$dateAttempt."', "
				." '".$status."'"
				." )";
		
		$result = sql_query($query) 
			or errorCommunication( 'createTrack'.sql_error() );
		
		if(isset($this)) {
			
			$this->idReference = $idReference;
			$this->idUser = $idUser;
			$this->idTrack = $idTrack;
			$this->objectType = (($objectType==FALSE)?($this->objectType):($objectType));
			$this->dateAttempt = $dateAttempt;
			$this->status = $status;
						
			$this->_setCourseCompleted();
		}
	}
	
	function getObjectType() {
		return $this->objectType;
	}
	
	function getDate() {
		return $this->dateAttempt;
	}
	
	function setDate( $new_date ) {
		$this->dateAttempt = $new_date;
	}
	
	function getStatus() {
		return $this->status;
	}
	
	function setStatus( $new_status ) {
		$this->status = $new_status;
	}
	
	function update()
	{
		$query = "UPDATE ".$this->_table." SET "
				." dateAttempt ='".$this->dateAttempt."',"
				." status ='".$this->status."'"
				." WHERE idTrack = '".(int)$this->idTrack."' AND objectType = '".$this->objectType."'";
		if(!sql_query($query))
			return false;
		$this->_setCourseCompleted();
		return true;
	}
	
	function _setCourseCompleted() {
		
		if($this->environment != 'course_lo') return;
		if( $this->status == 'completed' || $this->status == 'passed' ) {

			//update complete dates in DB
			$query = "SELECT first_complete, last_complete FROM %lms_commontrack WHERE idTrack=".(int)$this->idTrack;
			$res = sql_query($query);
			if ($res && sql_num_rows($res)>0) {
				$now = date("Y-m-d H:i:s");
				list($first_complete, $last_complete) = sql_fetch_row($res);
				$query = "UPDATE %lms_commontrack SET last_complete='".$now."'";
				if (!$first_complete || $first_complete>$now) $query .= ", first_complete='".$now."'";
				$query .= " WHERE idTrack=".(int)$this->idTrack;
				$res = sql_query($query);
			}
			//---
/*
			if(isset($_SESSION['idCourse'])) {
				
				$idCourse = $_SESSION['idCourse'];
			} else {
				*/
				// the only way is a direct query :(, or else if more than one course is open only the last one will complete
				$query = "SELECT idCourse "
					."FROM %lms_organization "
					."WHERE idOrg = '".(int)$this->idReference."' ";
				list($idCourse) = sql_fetch_row(sql_query($query));
			//}
			$useridst = $this->idUser;
			require_once( Docebo::inc( _lms_.'/modules/organization/orglib.php' ) );
			$repoDb = new OrgDirDb( $idCourse );
			$item = $repoDb->getFolderById( $this->idReference );
			$values = $item->otherValues;
			$isTerminator = (isset($values[ORGFIELDISTERMINATOR]) && $values[ORGFIELDISTERMINATOR]);
			
			if( $isTerminator ) {
				require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
				require_once($GLOBALS['where_lms'].'/lib/lib.stats.php');
				saveTrackStatusChange((int)$useridst, (int)$idCourse , _CUS_END);
			}
			
		}
		
	}
	
	/**
	 * print in standard output ($mvc parameter: to be set if we are in a mvc module)
	 **/
	function loadReport( $idUser = false, $mvc = false ) {
		
	}
	
	/**
	 * print in standard output the details of a track 
	 **/
	function loadReportDetail( $idUser, $idItemDetail ) {
		
	}
	
	/**
	* print in standard output 
	 * @return nothing
	 **/
	function loadObjectReport( ) {
		return;
	}
	
	/**
	 * static function to fast compute prerequisites
	 **/
	function isPrerequisitesSatisfied( $arrId, $idUser,$environment=false ) {
		
		if( is_string($arrId) )
			if( strlen($arrId)>0 )
				if( $arrId{0} == ',' )
					$arrId = substr($arrId,1);
		if( $arrId == '' ) { 
			return TRUE;
		} else {
			// in this brach we extract two array
			// 1) $idList array of id for use in query
			// 2) $arrPre array composed by $id => $status
			$idList = array();
			$arrTokens = explode( ',', $arrId );
			while( ($val = current( $arrTokens )) !== FALSE ) {
				$arrPeer = explode( '=', $val );
				if( $arrPeer[0] !== 'rray' ) { 	// patch to skip wrong prerequisites 
												// saved in db in first version of 3.0.1
					if( count($arrPeer) > 1 ) {
						$arrPre[$arrPeer[0]] = $arrPeer[1];
					} else {
						$arrPre[$arrPeer[0]] = 'completed';
					}
					$idList[] = $arrPeer[0];
				}
				next( $arrTokens );				
			}
		}
		if(empty($idList)) {
			return true;
		} else {
			$query = "SELECT idReference, status "
					." FROM ".self::getEnvironmentTable($environment).""
					." WHERE ((idReference IN ( ".implode( ',', $idList)." ))"
					."   AND (idUser = '".(int)$idUser."'))";
		}
				// ."   AND ((status = 'completed') OR (status = 'passed')))";
		$rs = sql_query( $query )
			or die( "Error in query=[ $query ] ". sql_error() );
			
		//echo "\n".'<!-- sto controllando i prerequisiti con questa query : '.$query.' -->';
		while( list( $id, $status ) = sql_fetch_row( $rs ) ) 
			$arrStatus[$id] = $status;
		
		//if(isset($arrStatus)) echo "\n".'<!-- gli stati letti per i prerequisiti chiesti sono : '.print_r($arrStatus, true).' -->';
		//else echo "\n".'<!-- nessuno dei prerequisiti � stato tracciato -->';
		foreach( $arrPre as $id => $status ) {
			switch( $status ) {
				case 'NULL':
					if( isset( $arrStatus[$id] ) )
						return FALSE;
				break;
				case 'completed':
				case 'passed':
					if( !isset( $arrStatus[$id] ) 
						|| ($arrStatus[$id] != 'completed' && $arrStatus[$id] != 'passed') )
						return FALSE;
				break;
				case 'failed':
				case 'incomplete':
				case 'not attempted':
				case 'attempted':
				case 'ab-initio':
					if( isset( $arrStatus[$id] ) 
						&& ($arrStatus[$id] != 'failed' 
						&&  $arrStatus[$id] != 'incomplete'
						&&  $arrStatus[$id] != 'not attempted'
						&&  $arrStatus[$id] != 'attempted'
						&&  $arrStatus[$id] != 'ab-initio') )
						return FALSE;
				break;
			}
		}
			
		return TRUE;
	}
	
	/**
	 * static function to get status
	 **/
	function getStatusFromId( $idReference, $idUser, $environment = false ) {
		
		$query = "SELECT status "
				." FROM ".self::getEnvironmentTable($environment).""
				." WHERE (idReference = ".(int)$idReference.")"
				."   AND (idUser = '".(int)$idUser."')";
		$rs = sql_query( $query )
			or die( "Error in query=[ $query ] ". sql_error() );
			
		if( sql_num_rows( $rs ) == 0 )
			return 'not attempted';
		else {
			list( $status ) = sql_fetch_row( $rs );
			return $status;
		}				
	}
	/**
	 * @return idTrack if found else false
	 **/
	function getIdTrackFromCommon( $idReference, $idUser, $environment = false) {
		
		$query = "SELECT idTrack "
				." FROM ".self::getEnvironmentTable($environment).""
				." WHERE (idReference = ".(int)$idReference.")"
				."   AND (idUser = '".(int)$idUser."')";
		$rs = sql_query( $query )
			or die( "Error in query=[ $query ] ". sql_error() );
			
		if( sql_num_rows( $rs ) == 0 )
			return false;
		else {
			list( $idTrack ) = sql_fetch_row( $rs );
			return $idTrack;
		}	
	}
	
	function delIdTrackFromCommon( $idReference ) {
		if (is_numeric($idReference)) {
			$query = "DELETE FROM ".$this->_table.""
				." WHERE (idReference = ".(int)$idReference.")";
		} elseif (is_array($idReference)) {
			$query = "DELETE FROM ".$this->_table.""
				." WHERE (idReference IN (".implode(",", $idReference)."))";
		}
		$rs = sql_query( $query )
			or die( "Error in query=[ $query ] ". sql_error() );
		return $rs;
	}
		
	/**
	 * @return bool	true if this object use extra colum in user report
	 */
	function otherUserField() {
		return false;
	}
	
	/**
	 * @return array	an array with the header of extra colum
	 */
	function getHeaderUserField() {
		return array();
	}
	
	/**
	 * @return array	an array with the extra colum
	 */
	function getUserField() {
		return array();
	}
	
	function updateObjectTitle($idResource, $objectType, $new_title) {
		
		$new_title = str_replace('/', '', $new_title);
		
		$re = true;
		
		$query_search = "
		SELECT path
		FROM ".$GLOBALS['prefix_lms']."_homerepo 
		WHERE idResource = '".(int)$idResource."'  
			AND objectType = '".$objectType."'
		LIMIT 1";
		$re_search = sql_query($query_search);
		while(list($path) = sql_fetch_row($re_search)) {
			
			$path_piece = explode('/', $path);
			unset($path_piece[count($path_piece)-1]);
			$new_path = implode('/', $path_piece).   "/" . $new_title;
			
			$query_lo = "
			UPDATE ".$GLOBALS['prefix_lms']."_homerepo
			SET path = '".$new_path."', title = '".$new_title."' 
			WHERE idResource = '".(int)$idResource."'  
				AND objectType = '".$objectType."'";
			$re &= sql_query($query_lo);
		}
		
		$query_lo = "
		UPDATE ".$GLOBALS['prefix_lms']."_organization
		SET title = '".$new_title."' 
		WHERE idResource = '".(int)$idResource."'  
			AND objectType = '".$objectType."'";
		$re &= sql_query($query_lo);
		
		$query_search = "
		SELECT path
		FROM ".$GLOBALS['prefix_lms']."_repo 
		WHERE idResource = '".(int)$idResource."'  
			AND objectType = '".$objectType."'
		LIMIT 1";
		$re_search = sql_query($query_search);
		while(list($path) = sql_fetch_row($re_search)) {
			
			$path_piece = explode('/', $path);
			unset($path_piece[count($path_piece)-1]);
			$new_path = implode('/', $path_piece).   "/" . $new_title;
			
			$query_lo = "
			UPDATE ".$GLOBALS['prefix_lms']."_repo
			SET path = '".$new_path."', title = '".$new_title."' 
			WHERE idResource = '".(int)$idResource."'  
				AND objectType = '".$objectType."'";
			$re &= sql_query($query_lo);
		}
		
		return $re;
	}



	function updateTrackInfo($new_data = false) {
		//validate input parameters
		if (!$this->idTrack) return false;
		if (!$new_data) return true;
		if (is_object($new_data)) $new_data = Util::objectToArray($new_data);
		if (!is_array($new_data)) return false;

		//set values to set in the query
		$values = array();
		foreach ($new_data as $key => $value) {
			switch ($key) {
				case "status": $values[] = " status='".$value."' "; break;
				case "firstAttempt":
				case "first_access": $values[] = " firstAttempt='".$value."'"; break;
				case "dateAttempt":
				case "last_access": $values[] = " dateAttempt='".$value."'"; break;
				case "first_complete": $values[] = " first_complete='".$value."'"; break;
				case "last_complete": $values[] = " last_complete='".$value."'"; break;
			}
		}

		//don't do anything if no values have been provided
		if (!empty($values)) {
			$query = "UPDATE ".$this->_table." SET ".implode(",", $values)." WHERE idTrack=".(int)$this->idTrack;
			if (isset($new_data['id_user']) && $new_data['id_user'] > 0) $query .= " AND idUser=".(int)$id_user;
			$res = sql_query($query);

			if ($res) {
				//update data in the object and make "deep" status change if needed
				foreach ($new_data as $key => $value) {
					switch ($key) {
						case "status": $this->status = (int)$value; break;
						case "firstAttempt":
						case "first_access": $this->firstAttempt = $value; break;
						case "dateAttempt":
						case "last_access": $this->dateAttempt = $value; break;
						case "first_complete": $this->first_complete = $value; break;
						case "last_complete": $this->last_complete = $value; break;
					}
				}
			}

			$this->_setCourseCompleted();

			return $res ? true : false;
		}
		return true;
	}


	/**
	 * @return idTrack if exists or false
	 **/
	function deleteTrack( $idTrack ) {

		return true;
	}

	function deleteTrackInfo($id_lo, $id_user) {
		$query = "DELETE FROM ".$this->_table." WHERE idUser=".(int)$id_user." AND idReference=".(int)$id_lo;
		$res = sql_query($query);
        
        $query = "DELETE FROM %lms_materials_track WHERE idUser=".(int)$id_user." AND idReference=".(int)$id_lo;
        $res = sql_query($query);

        
		return $res;
	}
}
