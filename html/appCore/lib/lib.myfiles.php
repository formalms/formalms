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
 *
 * @author Fabio Pirovano
 * @version $Id:$
 */

define("MYFILE_ID_FILE", 0);
define("MYFILE_AREA", 1);
define("MYFILE_TITLE", 2);
define("MYFILE_DESCRIPTION", 3);
define("MYFILE_FILE_NAME", 4);
define("MYFILE_OWNER", 5);
define("MYFILE_POLICY", 6);

define("MF_POLICY_FREE", 0);
define("MF_POLICY_TEACHER", 1);
define("MF_POLICY_FRIENDS", 2);
define("MF_POLICY_NOONE", 3);
define("MF_POLICY_TEACHER_AND_FRIENDS", 4);

/**
 * this class is minded for database/filesystem access to the user file by the modules
 */
class MyFile {
	
	/**
	 * @var int the idst of the user
	 */
	var $id_user;
	
	var $arr_field = array(
		MYFILE_ID_FILE 		=> 'id_file', 
		MYFILE_AREA 		=> 'area', 
		MYFILE_TITLE 		=> 'title', 
		MYFILE_DESCRIPTION 	=> 'description', 
		MYFILE_FILE_NAME 	=> 'file_name', 
		MYFILE_OWNER 		=> 'owner',
		MYFILE_POLICY 		=> 'file_policy'
	);
	
	/**
	 * return the name of the main table that contanins the file name
	 */
	function getFilesTable() 	{ return $GLOBALS['prefix_fw'].'_user_myfiles'; }
	
	function getFilePath() 		{ return '/common/users/myfiles/'; }
	
	function getFileAddress($file_name) {
		
		return $this->getFilePath().$file_name;
	}
	
	function getUser() 			{ return $this->id_user; } 
	
	function setUser($id_user) 	{ return $this->id_user = $id_user; } 
	
	function _query($query) {
		
		$re_query = sql_query($query);
		return $re_query;
	}
	
	function _last_id() {
		
		return sql_insert_id();
	}
	
	function num_rows($resource) {
		
		return sql_num_rows($resource);
	}
	
	function fetch_row($resource) {
		
		return sql_fetch_row($resource);
	}
	
	function fetch_array($resource) {
		
		return sql_fetch_array($resource);
	}
	
	/**
	 * @param int $id_user the idst of the user
	 */
	function MyFile($id_user) {
		
		ksort($this->arr_field);
		reset($this->arr_field);
		$this->id_user = $id_user;
	}
	
	/**
	 * @return an array with all the files area 
	 */
	function getFilesAreas() {
		
		return array( 	'other' => '_MY_OTHER_FILES', 
						'image' => '_MY_IMAGES',
						'audio' => '_MY_AUDIO',
						'video' => '_MY_VIDEO' );
	}
	
	function getDefaultArea() {
		
		return 'other';
	}
	
	/**
	 * @param string $area the identifier of the area of the files
	 * @param string $extra_filter some extra filter to apply
	 * @param int $order_by the field to use for order
	 * 
	 * @return resource_id the sql resource
	 */
	function getFileList($area = false, $extra_filter = false, $order_by = false, $from = false, $num_elem = false) {
		
		$query = "
		SELECT ".implode(', ', $this->arr_field)." 
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->id_user."'";
		if($area !== false) $query .= " AND area = '".$area."'";
		if($extra_filter !== false) $query .= $extra_filter;
		if($order_by !== false) $query .= " ORDER BY ".$this->arr_field[$order_by]."";
		else $query .= " ORDER BY title";
		
		if($from !== false) {
			$query .= " LIMIT ".$from.", ".$num_elem;
		}
		
		$re_query = $this->_query($query);
		return $re_query;
	}
	
	/**
	 * @param string $area the identifier of the area of the files
	 * @param string $extra_filter some extra filter to apply
	 * 
	 * @return int the number of file founded
	 */
	function getFileCount($area = false, $extra_filter = false) {
		
		$query = "
		SELECT COUNT(*)
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->id_user."'";
		if($area !== false) $query .= " AND area = '".$area."'";
		if($extra_filter !== false) $query .= $extra_filter;
		
		if(!$re_query = $this->_query($query)) return '0';
		list($number) = $this->fetch_row($re_query);
		return $number;
	}
	
	function getFilteredFileList($arr_file, $order_by = false) {
		
		if(!is_array($arr_file)) return false;
		
		$query = "
		SELECT ".implode(', ', $this->arr_field)." 
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->id_user."' AND id_file IN ( ".implode(',', $arr_file)." )";
		if($order_by !== false) $query .= " ORDER BY ".$this->arr_field[$order_by]."";
		else $query .= " ORDER BY title";
		
		$re_query = $this->_query($query);
		return $re_query;
	}
		
	function getFileInfo($id_file) {
		
		$query = "
		SELECT ".implode(', ', $this->arr_field)." 
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->id_user."' AND id_file = '".$id_file."'";
		
		$re_query = $this->_query($query);
		if(!$re_query) return false;
		$file_info  = $this->fetch_row($re_query);
		
		return $file_info;
	}
	
	function saveFile($area, $file_descriptor) {
		
		$file_name = '';
		if(!isset($file_descriptor['error'])) return $file_name;
		if($file_descriptor['error'] != UPLOAD_ERR_OK) return $file_name;
		if($file_descriptor['name'] == '') return $file_name;
		
		require_once(_base_.'/lib/lib.upload.php');
		
		// if the area need custom management the file can be manipulated here
		switch($area) {
			default : {
				
				$savefile = $this->id_user.'_'.mt_rand(0,100).'_'.time().'_'.$file_descriptor['name'];
				if(!file_exists($GLOBALS['where_files_relative'].$this->getFilePath().$savefile)) {
					
					sl_open_fileoperations();
					if(sl_upload($file_descriptor['tmp_name'], $this->getFilePath().$savefile)) {
						
						$file_name = $savefile;
					}
					sl_close_fileoperations();
				}
			}
		}
		return $file_name;
	}
	
	function insertFile($id_file, $area, $title, $description, $file_descriptor, $file_policy) {
		
		require_once(_base_.'/lib/lib.user.php');
		require_once(_base_.'/lib/lib.user_profile.php');
        $user_data = new DoceboUser(getLogUserId());
		$user_profile_data = new UserProfileData();
		
		$file_name = '';
		if($file_descriptor != '') {
			// save file
			$file_name = $this->saveFile($area, $file_descriptor);
		}
		$file_size = Get::file_size($GLOBALS['where_files_relative'].$this->getFilePath().$file_name);
		if (!$file_size)
			$file_size = 0;
		$total_used_quota = $file_size + $user_profile_data->getUsedQuota(getLogUserId());
		$max_quota = ($user_profile_data->getQuotaLimit(getLogUserId()))*1024*1024;
		if ($total_used_quota <= $max_quota)
		{
			if(!$id_file) {
				if($file_name == '') return false;
				$query = "
				INSERT INTO ".$this->getFilesTable()." ( owner, area, title, description, file_name, file_policy, size ) VALUES 
				(	'".$this->id_user."', 
					'".$area."', 
					'".$title."', 
					'".$description."', 
					'".addslashes($file_name)."', 
					'".$file_policy."', 
					'".$file_size."' )";
				if(!$this->_query($query)) return false;
				
				$result = $user_data->updateUserUsedSpace($this->id_user);
				
				$id_file = $this->_last_id();
				return $id_file;
			} else {
				
				$query = "
				UPDATE ".$this->getFilesTable()." 
				SET area = '".$area."', 
					title = '".$title."', 
					description = '".$description."', 
					file_policy = '".$file_policy."' ";
				if($file_name != '' || $file_name != false) $query .= ", file_name = '".addslashes($file_name)."'";
				$query .= " WHERE id_file = '".$id_file."' AND owner = '".$this->id_user."'";
				
				if(!$this->_query($query)) return false;
				return $id_file;
			}
		}
		sl_open_fileoperations();
		sl_unlink($this->getFilePath().$file_name);
		sl_close_fileoperations();
		return false;
	}
	
	function deleteFile($id_file) {
		
		require_once(_base_.'/lib/lib.upload.php');
		require_once(_base_.'/lib/lib.user.php');
		$user_data = new DoceboUser;
		
		$file_info = $this->getFileInfo($id_file);
		sl_open_fileoperations();
		sl_unlink($this->getFilePath().$file_info[MYFILE_FILE_NAME]);
		sl_close_fileoperations();
		$query = "
		DELETE FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->id_user."' AND id_file = '".$id_file."'";
		if(!$this->_query($query)) return false;
		$result = $user_data->updateUserUsedSpace($this->id_user);
		return true;
	}
}

class MyFilesPolicy extends MyFile {
	
	var $_id_user;
	
	var $_viewer;
	
	var $_file_list;
	
	var $_extra_filter_cahced = false;
	
	var $_file_number;
	
	function MyFilesPolicy($id_user, $viewer, $is_friend = NULL, $is_teacher = NULL) {
		
		$this->_id_user = $id_user;
		$this->_viewer 	= $viewer;
		$this->_viewer_friend 	= $is_friend;
		$this->_viewer_teacher 	= $is_teacher;
	}
	
	function isViewerFriend() {
		
		if($this->_viewer_friend != NULL) return $this->_viewer_friend;
		
		require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
		$mf = new MyFriends($this->_id_user);
		
		$this->_viewer_friend = $mf->isFriend($this->_viewer);
		return $this->_viewer_friend;
	}
	
	function isViewerTeacher() {
	
		if($this->_viewer_teacher != NULL) return $this->_viewer_teacher;
		
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		$re = Man_CourseUser::getUserWithLevelFilter( array('4', '5', '6', '7'), array($this->_viewer) );
		$this->_viewer_teacher = !empty($re);
		
		return $this->_viewer_teacher;
	}
	
	function getFileInfo($id_file) {
		
		$arr_policy = array( MF_POLICY_FREE );
		if($this->isViewerFriend() || $this->_viewer == $this->_id_user) {
			$arr_policy[] = MF_POLICY_FRIENDS;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->isViewerTeacher() || $this->_viewer == $this->_id_user) {
			$arr_policy[] = MF_POLICY_TEACHER;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->_viewer == $this->_id_user) $arr_policy = array(	MF_POLICY_FREE, 
																	MF_POLICY_FRIENDS, 
																	MF_POLICY_TEACHER,
																	MF_POLICY_TEACHER_AND_FRIENDS, 
																	MF_POLICY_NOONE );
		
		$query = "
		SELECT ".implode(', ', $this->arr_field)." 
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->_id_user."'
			AND ".$this->arr_field[MYFILE_POLICY]." IN ( ".implode(',', $arr_policy)." ) 
			AND id_file = '".$id_file."'";
		
		$re_query = $this->_query($query);
		if(!sql_num_rows($re_query)) return false;
		return sql_fetch_row($re_query);
	}
	
	function getFileList($area = false, $order_by = false, $from = false, $num_elem = false) {
		
		$arr_policy = array( MF_POLICY_FREE );
		if($this->isViewerFriend() || $this->_viewer == $this->_id_user) {
			$arr_policy[] = MF_POLICY_FRIENDS;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->isViewerTeacher() || $this->_viewer == $this->_id_user) {
			$arr_policy[] = MF_POLICY_TEACHER;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->_viewer == $this->_id_user) $arr_policy = array(	MF_POLICY_FREE, 
																	MF_POLICY_FRIENDS, 
																	MF_POLICY_TEACHER,
																	MF_POLICY_TEACHER_AND_FRIENDS, 
																	MF_POLICY_NOONE );
		
		$query = "
		SELECT ".implode(', ', $this->arr_field)." 
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->_id_user."'
			AND ".$this->arr_field[MYFILE_POLICY]." IN ( ".implode(',', $arr_policy)." ) ";
		if($area !== false) $query .= " AND area = '".$area."'";
		if($order_by !== false) $query .= " ORDER BY ".$this->arr_field[$order_by]."";
		else $query .= " ORDER BY title";		
		if($from !== false) {
			$query .= " LIMIT ".$from.", ".$num_elem;
		}
		
		$re_query = $this->_query($query);
		return $re_query;
	}
	
	/**
	 * @param string $area the identifier of the area of the files
	 * @param string $extra_filter some extra filter to apply
	 * 
	 * @return int the number of file founded
	 */
	function getFileCount($area = false, $extra_filter = false) {
		
		// return cahced data if availables
		if($this->_extra_filter_cahced == $extra_filter && !empty($this->_file_number)) {
			
			return ( $area !== false 
				? ( isset($this->_file_number[$area]) ? $this->_file_number[$area] : 0 ) 
				: $this->_file_number['total'] 
			);
		}
		
		// extract data from database
		$arr_policy = array( MF_POLICY_FREE );
		if($this->isViewerFriend() || $this->_viewer == $this->_id_user) {
			$arr_policy[] = MF_POLICY_FRIENDS;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->isViewerTeacher() || $this->_viewer == $this->_id_user) {
			$arr_policy[] = MF_POLICY_TEACHER;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->_viewer == $this->_id_user) $arr_policy = array(	MF_POLICY_FREE, 
																	MF_POLICY_FRIENDS, 
																	MF_POLICY_TEACHER,
																	MF_POLICY_TEACHER_AND_FRIENDS, 
																	MF_POLICY_NOONE );
		
		$query = "
		SELECT area, COUNT(*)
		FROM ".$this->getFilesTable()."
		WHERE owner = '".$this->_id_user."'
			AND ".$this->arr_field[MYFILE_POLICY]." IN ( ".implode(',', $arr_policy)." ) ";
		//if($area !== false) $query .= " AND area = '".$area."'";
		if($extra_filter !== false) $query .= $extra_filter;
		$query .= " GROUP BY area ";
		
		$this->_file_number = array('total' => 0);
		if(!$re_query = $this->_query($query)) return '0';
		while(list($in_area, $number) = $this->fetch_row($re_query)) {
			
			$this->_file_number[$in_area] = $number;
			$this->_file_number['total'] += $number;
		}
		
		$this->_extra_filter_cahced = $extra_filter;
		return ( $area !== false 
			? ( isset($this->_file_number[$area]) ? $this->_file_number[$area] : 0 ) 
			: $this->_file_number['total'] 
		);
	}
	
	function isFileAccessible($id_file) {
	
		$arr_policy = array( MF_POLICY_FREE );
		if($this->isViewerFriend()) {
			$arr_policy[] = MF_POLICY_FRIENDS;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->isViewerTeacher()) {
			$arr_policy[] = MF_POLICY_TEACHER;
			$arr_policy[] = MF_POLICY_TEACHER_AND_FRIENDS;
		}
		if($this->_viewer == $this->_id_user) $arr_policy = array(	MF_POLICY_FREE, 
																	MF_POLICY_FRIENDS, 
																	MF_POLICY_TEACHER,
																	MF_POLICY_TEACHER_AND_FRIENDS, 
																	MF_POLICY_NOONE );
		$query = "
		SELECT ".implode(', ', $this->arr_field)." 
		FROM ".$this->getFilesTable()." 
		WHERE owner = '".$this->_id_user."'
			AND ".$this->arr_field[MYFILE_POLICY]." IN ( ".implode(',', $arr_policy)." ) 
			AND ".$this->arr_field[MYFILE_ID_FILE]." = '".$id_file."'";
		
		if(!$re_query = $this->_query($query)) return false;
		if($this->num_rows($re_query)) return true;
		return false;
	}
}

class MyFileSelector {
	
	var $current_selection;
	
	function MyFileSelector() {
		
		$this->current_selection = array();
	}
	
	function getSelection() {
		$this->parse();
		return $this->current_selection;
	}
	
	function setSelection($selection) {
		
		$this->current_selection = $selection;
	}
	
	function parse() {
	
		// older selection
		if(isset($_POST['old_selection'])) {
			
			$this->current_selection = Util::unserialize(urldecode($_POST['old_selection']));
		}
		// add last selection
		if(isset($_POST['displayed'])) $displayed = Util::unserialize(urldecode($_POST['displayed']));
		else $displayed = array();
		if(isset($_POST['new_file_selected'])) {
			
			$displayed = array_diff($displayed, $_POST['new_file_selected']);
			while(list($id_f) = each($_POST['new_file_selected'])) {
				
				$this->current_selection[$id_f] = $id_f;
			}
		}
		// remove old selection
		if(is_array($displayed) && count($displayed)) {
		
			while(list($id_f) = each($displayed)) {
				
				if(isset($this->current_selection[$id_f])) unset($this->current_selection[$id_f]);
			}
		}
	}
	
	function loadSelector() {
		
		require_once(_base_.'/lib/lib.tab.php');
		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.table.php');
	
		$file_man 	= new MyFile( getLogUserId() );
		$tab_man 	= new TabView('myfiles', '');
		
		$lang 		=& DoceboLanguage::createInstance('myfiles');
			
		$areas = $file_man->getFilesAreas();
		while(list($id_page, $area_name) = each($areas)) {
			
			$new_tab = new TabElemDefault(	$id_page, 
											$lang->def($area_name), 
											getPathImage('fw').'myfiles/'.$id_page.'.gif');
			$tab_man->addTab($new_tab);
			
		}
		$this->parse();
		$tab_man->parseInput($_POST, $_SESSION);
		
		$active_tab = $tab_man->getActiveTab();
		if(!$active_tab) {
			$active_tab = importVar('working_area', true, $file_man->getDefaultArea());
			$tab_man->setActiveTab($active_tab);
		}
		$tb = new Table(	0, 
							$lang->def('_MYFILES_CAPTION'), 
							$lang->def('_MYFILES_SUMMARY') );
		
		$cont_h = array(
			'<span class="access-only">'.$lang->def('_FILE_SELECTION').'</span>', 
			$lang->def('_TITLE').'</label>'
		);	
		$type_h = array('image', '');
		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);
		
		$id_list = array();
		$re_files = $file_man->getFileList($active_tab, false, MYFILE_TITLE);
		while($file_info = $file_man->fetch_row($re_files)) {
			
			$id_file = $file_info[MYFILE_ID_FILE];
			$id_list[$id_file] = $id_file;
			$cont = array(
				Form::getInputCheckbox('new_file_selected_'.$id_file, 'new_file_selected['.$id_file.']', $id_file, 
					isset($this->current_selection[$id_file]), ''), 
				'<label for="new_file_selected_'.$id_file.'">'.$file_info[MYFILE_TITLE].'</label>'
			);	
			$tb->addBody($cont);
		}
		// print selector
		$GLOBALS['page']->add(
			Form::getHidden('working_area', 'working_area', $active_tab)
			.Form::getHidden('old_selection', 'old_selection', urlencode(Util::serialize($this->current_selection)))
			.Form::getHidden('displayed', 'displayed', urlencode(Util::serialize($id_list)))
			.$tab_man->printTabView_Begin('', false)
			.$tb->getTable()
			.$tab_man->printTabView_End()
		, 'content');
	}
	
	function loadButton() {
		
		require_once(_base_.'/lib/lib.form.php');
		
		$lang 		=& DoceboLanguage::createInstance('myfiles');
		
		$GLOBALS['page']->add(
			Form::openButtonSpace()
			.Form::getButton('save_file_sel', 'save_file_sel', $lang->def('_SAVE'))
			.Form::getButton('undo_file_sel', 'undo_file_sel', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
		, 'content');
	}
	
	function pressedSave() {
		
		return isset($_POST['save_file_sel']);
	}
	
	function pressedUndo() {
	
		return isset($_POST['undo_file_sel']);
	}
	
}

?>