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

require_once( dirname( __FILE__ ).'/learning.object.php' );

class Learning_Item extends Learning_Object {
	
	function __construct( $id_resource = NULL, $environment = false ) {
		parent::__construct( $id_resource, $environment );
		$this->obj_type = 'item';
		if($id_resource != false) $this->load();
	}

	function load() {

		$res = $this->db->query("SELECT author, title FROM %lms_materials_lesson WHERE idLesson = ".(int)$this->id." ");
		if($res && $this->db->num_rows($res)>0) {
			list($this->idAuthor, $this->title) = $this->db->fetch_row($res);
		}
	}
		
	/**
	 * attach the id of the created object at the end of back_url with the name, in attach the result in create_result
	 * @param string $back_url contains the back url
	 */
	function create( $back_url ) {
		
		$this->back_url = $back_url;
		
		unset($_SESSION['last_error']);
		
		require_once( $GLOBALS['where_lms'].'/modules/item/item.php' );
		additem( $this );
	}
	
	/**
	 * attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format 
	 * @param int $id contains the resource id
	 * @param string $back_url contains the back url
	 */
	function edit( $id, $back_url ) {
		
		$this->id = $id;
		$this->back_url = $back_url;
		
		unset($_SESSION['last_error']);
		
		require_once( $GLOBALS['where_lms'].'/modules/item/item.php' );
		moditem( $this );
	}
	
	/**
	 * @param int $id contains the resource id
	 * @param string $back_url contains the back url (not used yet)
	 */
	function del( $id, $back_url = NULL ) {
		//checkPerm('view', false, 'storage');
		
		unset($_SESSION['last_error']);
		
		require_once(_base_.'/lib/lib.upload.php');
		
		$path_to_file = '/appLms/'.Get::sett('pathlesson');
		
		list($old_file) = sql_fetch_row(sql_query("
		SELECT path 
		FROM ".$GLOBALS['prefix_lms']."_materials_lesson 
		WHERE idLesson = '".$id."'"));
		
		$size = Get::file_size($GLOBALS['where_files_relative'].$path_to_file.$old_file);
		if($old_file != '') {
			
			sl_open_fileoperations();
			if(!sl_unlink( $path_to_file.$old_file )) {
				sl_close_fileoperations();
				$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'item');
				return false;
			}
			sl_close_fileoperations();
			if(isset($_SESSION['idCourse']) && defined("LMS")) $GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);
		}
		$delete_query = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_materials_lesson 
		WHERE idLesson = '".$id."'";
		
		if(!sql_query( $delete_query )) {
			
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'item');
			return false;
		}
		return $id;

	}
	
	/**
	 * @param int $id contains the resource id
	 * @param string $back_url contain the back url (not used yet)
	 */
	function copy( $id, $back_url = NULL ) {
		
		require_once( _base_.'/lib/lib.upload.php' );
		
		//find source info
		list($title, $descr, $file) = sql_fetch_row(sql_query("
		SELECT title, description, path 
		FROM ".$GLOBALS['prefix_lms']."_materials_lesson 
		WHERE idLesson = '".(int)$id."'"));
		
		//create the copy filename 
		$path_to_file = '/appLms/'.Get::sett('pathlesson');
		$savefile = $_SESSION['idCourse'].'_'.mt_rand(0, 100).'_'.time().'_'
			.implode('_', array_slice(explode('_', $file), 3));
		
		//copy fisic file
		sl_open_fileoperations();
		if(!sl_copy( $path_to_file.$file, $path_to_file.$savefile )) {
			sl_close_fileoperations();
			return false;
		}
		
		//insert new item
		$insertQuery = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_materials_lesson 
		SET author = '".getLogUserId()."',
			title = '".sql_escape_string($title)."',
			description = '".sql_escape_string($descr)."',
			path = '".sql_escape_string($savefile)."'";
			
		
		if(!sql_query($insertQuery)) {
			sl_unlink( $path_to_file.$savefile );
			sl_close_fileoperations();
			return false;
		}
		sl_close_fileoperations();
		
		list($idLesson) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		return $idLesson;
	}
	
	/**
	 * @param int $id contains the resource id
	 * @param int $id_param contains the id needed for params retriving
	 * @param string $back_url contain the back url
	 */
	function play( $id, $id_param, $back_url ) {
		
		require_once(_lms_.'/modules/item/do.item.php');
		
		$this->id = $id;
		$this->back_url = $back_url;
		play( $id, $id_param, $back_url );
	}

	function env_play($id_reference, $back_url, $options = array()) {

		require_once(_lms_.'/modules/item/do.item.php');
		//$this->id;
		//$this->obj_type;
		
		//$this->environment;
		$this->id_reference = $id_reference;
		$this->back_url = $back_url;
		env_play( $this, $options );
	}
	
}
