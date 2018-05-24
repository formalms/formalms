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

class Learning_Object {
	
	var $id;
	var $environment = 'course_lo';
	var $id_reference = false;
	
	var $idAuthor;
	
	var $title;
	var $obj_type = 'object';

	var $db;
	var $aclManager;
	var $table;

	var $isPhysicalObject = false;
	var $no_restrictions = false;
	
	/**
	 * function learning_Object()
	 * class constructor
	 **/
	function Learning_Object( $id = NULL, $environment = false ) {
		$this->id = $id;
		$this->environment = ( $environment ? $environment : 'course_lo' );
		
		$this->idAuthor = '';
		$this->title = '';

		$this->db = DbConn::getInstance();
		$this->aclManager = Docebo::user()->getAclManager();
		$this->table = '';
	}
	
	function getIdParam($env = false) {
		$res =false;

		$env = ( $env ? $env : $this->environment );
		switch($env) {
			case "communication" : {
				return 0;
			};break;
			case "games" : {
				return 0;
			};break;
			case "course_lo" :
			default: {
				$qtxt ="SELECT idParam FROM %lms_organization WHERE idResource = ".(int)$this->id." AND objectType = '".$this->getObjectType()."' ";
			};break;
		}

		if (!empty($qtxt)) {
			$re = $this->db->query($qtxt);
			list($id_param) = $this->db->fetch_row($re);
			$res =$id_param;
		}

		return $res;
	}

	/**
	 * function getId()
	 * @return int resource id
	 */
	function getId() {
		return $this->id;
	}
	
	/**
	 * function getIdAuthor()
	 * @return int resource author id
	 */
	function getIdAuthor() {
		return $this->idAuthor;
	}
	
	/**
	 * function getTitle()
	 * @return string title
	 */
	function getTitle() {
		return $this->title;
	}
	
	/**
	 * function getObjectType()
	 * @return string Learning_Object type
	 */
	function getObjectType() {
		return $this->obj_type;
	}
	
	/**
	 * function create( $back_url )
	 * @param string $back_url contains the back url
	 * @return bool TRUE if success FALSE if fail
	 * attach the id of the created object at the end of back_url with the name id_lo
	 *
	 * static
	 */
	function create( $back_url ) {
	
	}
	
	/**
	 * function edit
	 * @param int $id contains the resource id
	 * @param string $back_url contains the back url
	 * @return bool TRUE if success FALSE if fail
	 * attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format 
	 */
	function edit( $id, $back_url ) {}
	
	/**
	 * function del
	 * @param int $id contains the resource id
	 * @param string $back_url contains the back url (not used yet)
	 * @return int $id if success FALSE if fail
	 */
	function del( $id, $back_url = NULL ) {
	
	}
	
	/**
	 * function copy( $id, $back_url )
	 * @param int $id contains the resource id
	 * @param string $back_url contain the back url (not used yet)
	 * @return int $id if success FALSE if fail
	 */
	function copy( $id, $back_url = NULL ) {
	
	}
	
	/**
	 * function getParamInfo()
	 * return array of require params for play
	 * @return an example of associative array returned is:
	 *	[0] => (
	 *		['label'] => _DEFINITION,
	 *		['param_name'] => parameter name;
	 *	),
	 *	[1] = >(
	 *		['label'] => _DEFINITION,
	 *		['param_name'] => parameter name;
	 * ) ...
	 */
	function getParamInfo() {
		return FALSE;
	}
	
	/**
	 * function play( $id, $id_param, $back_url )
	 * @param int $id contains the resource id
	 * @param int $id_param contains the id needed for params retriving
	 * @param string $back_url contain the back url
	 * @return nothing return
	 */
	function play( $id, $id_param, $back_url ) {
		
	}
	
	/**
	 * function import( $source, $back_url ) NOT IMPLEMENTED YET
	 * @param string $source contains the filename 
	 * @return bool TRUE if success FALSE if fail
	 * if operation success attach the new id at the back url with the name id_lo 
	 */
	function import( $source, $back_url ) {
	
	}
	
	/**
	 * function export( $id, $format, $back_url ) NOT IMPLEMENTED YET
	 * @param string $id contain resource id
	 * @param string $format contain output format
	 * @param string $back_url contain the back url 
	 * @return bool TRUE if success FALSE if fail
	 */
	function export( $id, $format, $back_url ) {
		
	}
	
	/** 
	 * function getMultipleResource( $idMultiResource )
	 * @param int $idMultiResource identifier of the multi resource
	 * @return array an array with the ids of all resources
	 */
	function getMultipleResource( $idMultiResource ) {
		return array();
	}
	
	/**
	 * function canBeMilestone() 
	 * @return TRUE if this object can be a milestone
	 *			FALSE otherwise
	 */
	function canBeMilestone() {
		return FALSE;
	}

	/**
	 * @param string $key contains the keyword to search
	 * @return array with results found
	 */
	function search( $key ) {
		return array();
	}


	public function setNoRestrictions($val) {
		$this->no_restrictions =(bool)$val;
	}


	public function checkObjPerm() {

		if (!$this->no_restrictions) {
			//die();
		}

	}

    /**
     * @param $visible
     */
	public function setVisibileInCoursereportDetail($visible){
    }

}

