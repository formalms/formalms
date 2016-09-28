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

define("LR_ID", 		0);
define("LR_IDCOURSE", 	1);
define("LR_TITLE", 		2);
define("LR_DESCR", 		3);
define("LR_FILECOUNT", 	4);

define("LR_FILE_ID", 		0);
define("LR_FILE_ID_REPO", 	1);
define("LR_FILE_NAME", 		2);
define("LR_FILE_DESCR", 	3);
define("LR_FILE_AUTHOR", 	4);
define("LR_FILE_POSTDATE", 	5);

define("LR_FILE_FILECOUNT", 6);

define("LR_USER_ID_USER", 	0);
define("LR_USER_ID_REPO", 	1);
define("LR_USER_LASTENTER", 2);
define("LR_USER_LOCKED", 	3);

class LightRepoManager {

	var $_id_user;
	
	var $_id_course;
	
	var $_field_repo = array(
		LR_ID 		=> 'id_repository',
		LR_IDCOURSE => 'id_course',
		LR_TITLE 	=> 'repo_title',
		LR_DESCR 	=> 'repo_descr'
	);
	
	var $_field_file = array(
		LR_FILE_ID 			=> 'id_file', 
		LR_FILE_ID_REPO 	=> 'id_repository', 
		LR_FILE_NAME 		=> 'file_name', 
		LR_FILE_DESCR 		=> 'file_descr', 
		LR_FILE_AUTHOR 		=> 'id_author', 
		LR_FILE_POSTDATE 	=> 'post_date'
	);
	
	var $_field_user = array(
		LR_USER_ID_USER 	=> 'id_user', 
		LR_USER_ID_REPO 	=> 'id_repo', 
		LR_USER_LASTENTER 	=> 'last_enter', 
		LR_USER_LOCKED 		=> 'repo_lock'
	);
	
	function LightRepoManager($id_user, $id_course) {
		
		ksort($this->_field_repo);
		reset($this->_field_repo);
		ksort($this->_field_file);
		reset($this->_field_file);
		ksort($this->_field_user);
		reset($this->_field_user);
		
		$this->_id_user 	= $id_user;
		$this->_id_course 	= $id_course;
	}
	
	function _tableRepo() { return $GLOBALS['prefix_lms'].'_light_repo'; }
	
	function _tableFile() { return $GLOBALS['prefix_lms'].'_light_repo_files'; }
	
	function _tableUser() { return $GLOBALS['prefix_lms'].'_light_repo_user'; }
	
	function getRepoList($filter_by_author = false) {
		
		$query = " SELECT r.".implode(', r.', $this->_field_repo).", COUNT(f.".$this->_field_file[LR_FILE_ID].") "
			
			." FROM ".$this->_tableRepo()." AS r "
			." 		LEFT JOIN ".$this->_tableFile()." AS f ON ( r.".$this->_field_repo[LR_ID]." = f.".$this->_field_file[LR_FILE_ID_REPO]."  "
			.( $filter_by_author !== false ? " AND f.".$this->_field_file[LR_FILE_AUTHOR]." = ".(int)$this->_id_user." " : "" )
			." ) "
			
			." WHERE r.".$this->_field_repo[LR_IDCOURSE]." = ".(int)$this->_id_course." " ;
		
		$query .= " GROUP BY r.".$this->_field_repo[LR_ID];
		
		$re_query = sql_query($query);
		
		return $re_query;
	}
	
	function getRepoDetails($id_repo) {
		
		$query = " SELECT ".implode(',', $this->_field_repo).""
			." FROM ".$this->_tableRepo()." "
			." WHERE ".$this->_field_repo[LR_IDCOURSE]." = ".(int)$this->_id_course." "
			."   AND ".$this->_field_repo[LR_ID]." = ".(int)$id_repo;
		$re_query = sql_query($query); 
		return sql_fetch_row($re_query);
	}
	
	function saveRepo($id_repo, $data) {
		
		if($id_repo == 0) {
			
			$keys = array();
			foreach($data as $k => $v) { 
				$keys[] = $this->_field_repo[$k]; 
			}
			$query = " INSERT INTO ".$this->_tableRepo()
				." ( ".implode(',', $keys)." ) VALUES "
				." ( '".implode("','", $data)."' ) ";	
		} else {
			
			$query = " UPDATE ".$this->_tableRepo()." SET ";	
			foreach($data as $k => $v) { 
				
				$query .= " ".$this->_field_repo[$k]." = '".$v."', "; 
			}
			$query = substr($query, 0, -2)." WHERE ".$this->_field_repo[LR_ID]." = ".(int)$id_repo." ";
		}
		$re = sql_query($query);
		
		return $re;
	}
	
	function deleteRepo($id_repo) {
		
		require_once(_base_.'/lib/lib.upload.php');
		
		$query = " SELECT ".implode(',', $this->_field_file)." "
			." FROM ".$this->_tableFile()
			." WHERE id_repo = ".(int)$id_repo." ";
		$re = sql_query($query);
		while($old_file = sql_fetch_row($re)) {
		
			sl_unlink($this->getFilePath().$old_file[LR_FILE_NAME]);
		}
		
		$query = " DELETE FROM ".$this->_tableRepo()
			." WHERE ".$this->_field_repo[LR_ID]." = ".(int)$id_repo." ";
		$re = sql_query($query);
		
		return $re;
	}
	
	function getUserLastEnterInRepo($id_repo) {
		$query = ""
		." SELECT last_enter "
		." FROM ".$this->_tableUser()." "
		." WHERE id_user = '".$this->_id_user."' "
		." 	AND id_repo = '".$id_repo."' ";
		if (sql_num_rows(sql_query($query)))
		{
			list($last_enter) = sql_fetch_row(sql_query($query));
			
			return $last_enter;
		}
		return false;
	}
	
	function setUserLastEnterInRepo($id_repo) {
		$query = ""
		." SELECT last_enter "
		." FROM ".$this->_tableUser()." "
		." WHERE id_user = '".$this->_id_user."' "
		." 	AND id_repo = '".$id_repo."' ";
		if( sql_num_rows(sql_query($query)) ) {
			
			$upd_query = "UPDATE ".$this->_tableUser()
				." SET last_enter = '".date("Y-m-d H:i:s")."'"
				." WHERE id_user = '".$this->_id_user."' "
				." 	AND id_repo = '".$id_repo."' ";
			$re = sql_query($upd_query);
		} else {
			$upd_query = " INSERT INTO ".$this->_tableUser()
				." ( id_repo, id_user, last_enter ) "
				." VALUES "
				." ( '".$id_repo."', '".(int)$this->_id_user."', '".date("Y-m-d H:i:s")."' )";
			$re = sql_query($upd_query);
		}
		return $re;
	}
	
	function getNumberOfFileInReport ($id_repo, $from_date = false)
	{
		$query = "" .
				" SELECT COUNT(*)" .
				" FROM ".$this->_tableFile()."" .
				" WHERE ".$this->_field_file[LR_FILE_ID_REPO]." = '".$id_repo."'";
		
		if($from_date !== false) {
			$query .= " AND ".$this->_field_file[LR_FILE_POSTDATE]." > '".$from_date."'";
		}
		
		list($new_file) = sql_fetch_row(sql_query($query));
		
		return $new_file;
	}
	
	function getNumberOfFile($group_by, $from_date = false) {
		
		$query = " SELECT COUNT(*)"
			." FROM ".$this->_tableRepo()." AS r JOIN ".$this->_tableFile()." AS f "
			
			." WHERE r.".$this->_field_repo[LR_IDCOURSE]." = ".(int)$this->_id_course." "
			." 		AND r.".$this->_field_repo[LR_ID]." = f.".$this->_field_file[LR_FILE_ID_REPO]." ";
		
		if($from_date !== false) {
			$query .= " AND r.".$this->_field_file[$group_by]." > '".$from_date."'";
		}
		$query .= " GROUP BY r.".$this->_field_file[$group_by];
		
		$re_query = sql_query($query);
		
		$row = sql_fetch_row($re_query);
		
		return $row[0];
	}
	
	function getRepoUserListWithFileCount($id_repo, $from_date = false) {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		
		$file_list = array();
		$acl_man 		= Docebo::user()->getAclManager();
                $view_all_perm = checkPerm('view_all', true);
		$course_man 	= new Man_Course();
		$course_user 	= $course_man->getIdUserOfLevel($this->_id_course, 3);
		
                //apply sub admin filters, if needed
                if( !$view_all_perm ) {
                        //filter users
                        require_once(_base_.'/lib/lib.preference.php');
                        $ctrlManager = new ControllerPreference();
                        $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
                        $course_user = array_intersect($course_user, $ctrl_users);
                }
		
		if(empty($course_user)) return $file_list;
		
		$users_list 	=& $acl_man->getUsers($course_user);
		
		while(list(,$user) = each($users_list)) {
			
			$file_list[$user[ACL_INFO_IDST]] = array();
			$file_list[$user[ACL_INFO_IDST]]['id_user'] = $user[ACL_INFO_IDST];
			$file_list[$user[ACL_INFO_IDST]]['username'] = $acl_man->getConvertedUserName($user);
		}
		
		$query = " SELECT f.id_author, COUNT(*) "
			." FROM ".$this->_tableFile()." AS f "
			." WHERE f.id_repository = ".(int)$id_repo." "
			."   AND f.id_author IN ( ".implode(',', $course_user)." ) "
			." GROUP BY f.id_author";
		$re_query = sql_query($query);
		
		while($row = sql_fetch_row($re_query)) {
			
			$file_list[$row[0]]['file_count'] 	= $row[1];
		}
		
		// if from_date is passed
		if($from_date !== false) {
		
			$query = " SELECT f.id_author, COUNT(*) "
				." FROM ".$this->_tableFile()." AS f "
				." WHERE f.id_repository = ".(int)$id_repo." "
				."   AND f.id_author IN ( ".implode(',', $course_user)." ) "
				." 	 AND f.post_date > '".$from_date."' "
				." GROUP BY f.id_author";
			$re_query = sql_query($query);
			
			while($row = sql_fetch_row($re_query)) {
				
				$file_list[$row[0]]['file_new'] = $row[1];
			}
		}
		
		return $file_list;
	}
	
	function getRepoFileListOfAuthor($id_repo, $author) {
		
		$query = " SELECT ".implode(',', $this->_field_file)." "
			." FROM ".$this->_tableFile()
			." WHERE ".$this->_field_file[LR_FILE_ID_REPO]." = ".(int)$id_repo." "
			." 		AND ".$this->_field_file[LR_FILE_AUTHOR]." = ".(int)$author." "
			." ORDER BY post_date DESC";
		$re = sql_query($query);
		
		return $re;
	}
	
	function getFileInfo($arr_file) {
		
		if(!is_array($arr_file)) $arr_file = array($arr_file);
		$query = " SELECT ".implode(',', $this->_field_file)." "
			." FROM ".$this->_tableFile()
			." WHERE id_file IN ( ".implode(',', $arr_file)." ) ";
		$re = sql_query($query);
		
		return $re;
	}
	
	function getFilePath() { return '/appLms/repo_light/'; }
	
	function saveFile($id_file, $file_info) {

		require_once(_base_.'/lib/lib.upload.php');
		
		if($id_file != '0') { 
			$old_file = sql_fetch_row($this->getFileInfo($id_file));
			if($old_file == false || count($old_file) == 0) $old_file = false;
		} else {
			$old_file = false;
		} 
		
		// the first time i need the file
		$saved_file = '';
		if($file_info[LR_FILE_NAME] == false && $old_file == false) return false;
		elseif($file_info[LR_FILE_NAME] != false) {
			
			// if the file is uploaded
			$saved_file = $this->uploadFile($file_info[LR_FILE_NAME]);
			
			if($saved_file === false) return false;
			
			if (!get_magic_quotes_gpc())
				$saved_file = addslashes($saved_file);
		}
		
		if($old_file == false) {
			
			// insert new ------------------------------------------------------------
			$query = ""
			." INSERT INTO ".$this->_tableFile()." "
			." ( id_file, id_repository, file_name, file_descr, id_author, post_date ) VALUES ( "
			." 	NULL, "
			."	".(int)$file_info[LR_FILE_ID_REPO].", "
			."	'".$saved_file."', "
			."	'".$file_info[LR_FILE_DESCR]."', "
			."	".(int)$file_info[LR_FILE_AUTHOR].", "
			."	'".$file_info[LR_FILE_POSTDATE]."' "
			." ) ";
			$re = sql_query($query);
		} else {
			if ($file_info[LR_FILE_NAME] !== $old_file[LR_FILE_NAME])
				sl_unlink($this->getFilePath().$old_file[LR_FILE_NAME]);
		
			// update prev ----------------------------------------------------------
			$query = ""
			." UPDATE ".$this->_tableFile()." "
			." SET id_repository = ".(int)$file_info[LR_FILE_ID_REPO].", "
			.( $saved_file != '' ? "	file_name = '".$saved_file."', " : '' )
			."	file_descr = '".$file_info[LR_FILE_DESCR]."', "
			."	id_author = ".(int)$file_info[LR_FILE_AUTHOR].", "
			."	post_date = '".$file_info[LR_FILE_POSTDATE]."' "
			." WHERE id_file = ".(int)$id_file."";
			$re = sql_query($query);
		}
		
		return $re;
	}
	
	function uploadFile($file_descriptor) {
		
		$file_name = '';
		if(!isset($file_descriptor['error'])) return $file_name;
		if($file_descriptor['error'] != UPLOAD_ERR_OK) return $file_name;
		if($file_descriptor['name'] == '') return $file_name;
		
		require_once(_base_.'/lib/lib.upload.php');
		
		// if the area need custom management the file can be manipulated here
		$savefile = $this->id_user.'_'.mt_rand(0,100).'_'.time().'_'.$file_descriptor['name'];
		if(!file_exists($GLOBALS['where_files_relative'].$this->getFilePath().$savefile)) {
			
			sl_open_fileoperations();
			if(sl_upload($file_descriptor['tmp_name'], $this->getFilePath().$savefile)) {
				
				$file_name = $savefile;
			}
			sl_close_fileoperations();
		}
		return $file_name;
	}
	
	function deleteFile($id_file) {
		
		require_once(_base_.'/lib/lib.upload.php');
		
		$old_file = sql_fetch_row($this->getFileInfo($id_file));
		sl_open_fileoperations();
		sl_unlink($this->getFilePath().$old_file[LR_FILE_NAME]);
		sl_close_fileoperations();
		
		$query = ""
		." DELETE FROM ".$this->_tableFile()." "
		." WHERE id_author = ".(int)$old_file[LR_FILE_AUTHOR]." "
		." 		AND id_file = ".(int)$id_file."";
		if(!sql_query($query)) return false;
		return true;
	}
	
}

?>