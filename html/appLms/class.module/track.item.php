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

require_once(_lms_.'/class.module/track.object.php');

class Track_Item extends Track_Object {
	
	public function  __construct( $lobj, $id_user ) {

		$this->lobj = $lobj;
		$this->objectType = $this->lobj->obj_type;

		//search for prev track
		$this->idTrack = $this->getIdTrack($this->lobj->id_reference, $id_user, $this->lobj->id, true);
		parent::__construct($this->idTrack, $this->lobj->environment);
		if($this->idReference == false) {

			$this->createTrack( $this->lobj->id_reference,
								$this->idTrack,
								$id_user,
								date("Y-m-d H:i:s"),
								'attempted',
								$this->objectType );
		}
	}

	/**
	 * Return a idTrack for this object, internal or external
	 * @param <int> $id_reference
	 * @param <int> $id_user
	 * @param <int> $id_resource
	 * @param <bool> $createOnFail create a new entry if not found
	 */
	public function getIdTrack( $id_reference, $id_user, $id_resource, $createOnFail = FALSE ) {

		$db = DbConn::getInstance();
		
		$query = "SELECT idTrack "
				."FROM %lms_materials_track "
				."WHERE idReference = ".(int)$id_reference." "
				."   AND idUser = ".(int)$id_user." "
				."   AND idResource = ".(int)$id_resource." ";
		$rs = $db->query( $query );

		if($db->num_rows($rs)  > 0 ) {
			
			list($idTrack) = $db->fetch_row($rs);
			return array( TRUE, $idTrack );
		} else if( $createOnFail ) {
			
			$query = "INSERT INTO %lms_materials_track "
					."( idResource, idReference, idUser ) VALUES "
					."( ".(int)$id_resource.", ".(int)$id_reference." , ".(int)$id_user." ) ";
			if(!$db->query( $query )) return false;
			$idTrack = $db->insert_id();
			return array(FALSE, $idTrack);
		}
		return FALSE;
	}

}
