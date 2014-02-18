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

require_once( $GLOBALS['where_lms'].'/class.module/track.object.php');

//if( version_compare(phpversion(), "5.0.0") == -1 ) {
	define('_track_scorm_basepath',$GLOBALS['where_lms'].'/modules/scorm/');
//} else {
//	define('_track_scorm_basepath',$GLOBALS['where_lms'].'/modules/scorm5/');
//}
class Track_ScormOrg extends Track_Object {

	var $idTrack;
	var $idReference;
	var $idUser;
	var $dateAttempt;
	var $status;
	var $objectType;

	/**
	 * object constructor
	 * Table : learning_commontrack
	 * idReference | idUser | idTrack | objectType | date_attempt  | status |
	 **/
	function Track_ScormOrg( $idTrack, $idResource = false, $idParams = false, $back_url = NULL, $environment = false ) {
		$this->objectType = 'scormorg';
		parent::Track_Object($idTrack, $environment);

		$this->idResource = $idResource;
		$this->idParams = $idParams;
		if($back_url === NULL) $this->back_url = array();
		else $this->back_url = $back_url;
	}

	/**
	 * print in standard output
	 **/
	function loadReport( $idUser = FALSE, $mvc = false ) {

		require_once( _track_scorm_basepath.'scorm_stats.php' );
		require_once( $GLOBALS['where_lms'].'/lib/lib.param.php' );
		if($idUser !== false) {
			$this->idReference = getLOParam($this->idParams, 'idReference');
			return scorm_userstat( $this->idResource, $idUser, $this->idReference, $mvc );
		}
	}

	/**
	 * print in standard output the details of a track
	 **/
	function loadReportDetail( $idUser, $idItemDetail, $idItem ) {
		require_once( _track_scorm_basepath.'scorm_stats.php' );
		if($idUser !== false) {
			return scorm_userstat_detail( $this->idResource, $idUser, $idItemDetail, $idItem );
		}
	}

	/**
	 * print in standard output the details of a track
	 **/
	function loadReportDetailHistory( $idUser, $idItemDetail, $idItem ) {
		require_once( _track_scorm_basepath.'scorm_stats.php' );
		if($idUser !== false) {
			return scorm_userstat_detailhist( $this->idResource, $idUser, $idItemDetail, $idItem );
		}
	}
/**
	 * @return idTrack if exists or false
	 **/
	function deleteTrack( $idTrack ) {

		$query = "DELETE FROM ".$this->_table." "
				." WHERE idTrack='".(int)$idTrack."'"
				."   AND objectType='scormorg'";
		if(!sql_query( $query )) return false;
		return true;
	}

	/*
	 * delete all tracking info
	 */
	function deleteTrackInfo($id_lo, $id_user) {
		//first of all: make sure the object is of the correct type 'scormorg'
		$query = "SELECT idUser, idReference, idTrack FROM ".$this->_table." WHERE idUser=".(int)$id_user." AND idReference=".(int)$id_lo." AND objectType='scormorg'";
		$res = sql_query($query);
		if ($res && sql_num_rows($res)>0) {
			list($id_user, $id_lo, $idTrack) = sql_fetch_row($res);

			//collect data for tracking history table
			$arr_ids = array();
			$query = "SELECT idscorm_tracking FROM %lms_scorm_tracking WHERE idUser=".$id_user." AND idReference=".$id_lo;
			$res = sql_query($query);
			if ($res) {
				while (list($id_scorm_tracking) = sql_fetch_row($res)) {
					$arr_ids[] = $id_scorm_tracking;
				}
			}

			//delete tracking scorm data
			$query = "DELETE FROM %lms_scorm_tracking WHERE idUser=".$id_user." AND idReference=".$id_lo;
			$res1 = sql_query($query);
			$query = "DELETE FROM %lms_scorm_items_track WHERE idUser=".$id_user." AND idReference=".$id_lo;
			$res2 = sql_query($query);

			$res3 = true;
			if (count($arr_ids) > 0) {
				$query = "DELETE FROM %lms_scorm_tracking_history WHERE idscorm_tracking IN (".implode(",", $arr_ids).")";
				$res3 = sql_query($query);
			}

			if ($res1 && $res2 && $res3) {
				return $this->deleteTrack($idTrack);
			}
		}
		return false;
	}

}

?>
