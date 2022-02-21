<?php defined("IN_FORMA") or die('Direct access is forbidden.');



require_once($GLOBALS['where_lms'].'/class.module/track.object.php');

class Track_Glossary extends Track_Object {
	
	function __construct( $idTrack ) {
		$this->objectType = 'glossary';
		parent::__construct($idTrack);
	}

	function getIdTrack( $idReference, $idUser, $idResource, $createOnFail = FALSE ) {
		
		$query = "SELECT idTrack FROM ".$GLOBALS['prefix_lms']."_materials_track"
				." WHERE idReference='".(int)$idReference."'"
				."   AND idUser='".(int)$idUser."'";
		$rs = sql_query( $query )
			or errorCommunication( 'getIdTrack' );
		if( sql_num_rows( $rs )  > 0 ) {
			list( $idTrack ) = sql_fetch_row( $rs );
			return [TRUE, $idTrack];
		} else if( $createOnFail ) {
			$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_materials_track"
					."( idResource, idReference, idUser ) VALUES ("
					."'".(int)$idResource."','".(int)$idReference."','".(int)$idUser."')";
			sql_query( $query )
				or errorCommunication( 'getIdTrack' );
			$idTrack = sql_insert_id();
			return [FALSE, $idTrack];
		}
		return FALSE;
	}

}

?>
