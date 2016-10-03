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
 * @package admin-core
 * @subpackage menu
 */
 
class Admin {
	
	var $user;
	
	var $platform;
	
	var $table_level_one;
	
	var $table_level_two;
	
	/**
	 * class constructor
	 * @param 	FormaUser 	$user				the object of the Forma User, for permission control
	 * @param 	string 		$table_level_one	table of level one menu
	 * @param 	string 		$table_level_two	table of level two menu
	 *
	 * @return	nothing
	 * @access public 
	 */
	function Admin(&$user, $table_level_one, $table_level_two) {
		
	}
	
	/**
	 * @return	mixed	a list of the first level menu 
	 *					[id] (	[link]
	 *							[image]
	 *						 	[name]  )
	 * @access public 
	 */
	 function getLevelOne() {
		 
	 }
	 
	 /**
	  * @param 	int 	$id_level_one	the id of a level one menu voice
	  *
	  * @return	mixed	a list of the second level menu of a passed first level menu, 
	  *					if not passed return all the voice of the second level 
	  *					[id] (	[link]
	  *							[image]
	  *						 	[name]  )
	  * @access public 
	  */
	 function getLevelTwo($id_level_one = false) {
		 
	 }
}

class Admin_Managment {
	
	var $platform;
	
	var $table_level_one;
	
	var $table_level_two;
	
	var $lang;
	
	var $lang_over;
	
	var $lang_perm;
	
	/**
	 * class constructor
	 * @param 	string 		$table_level_one	table of level one menu
	 * @param 	string 		$table_level_two	table of level two menu
	 *
	 * @return	nothing
	 * @access public 
	 */
	function Admin_Managment() {
		
	}
	
	function getLevelOne() {
	
		$query_menu = "
		SELECT idMenu, name, image
		FROM ".$this->table_level_one." 
		ORDER BY sequence";
		$re_menu = sql_query($query_menu);
		
		$menu = array();
		while(list($id, $name, $image) = sql_fetch_row($re_menu)) {
	 
			$menu[$id] = array('name' => ( $name != '' ? $this->lang_over->def($name) : '' ),
							'image' => '<img src="'.getPathImage($this->platform).'/menu/'.$image.'" alt="'
									.( $name != '' ? $this->lang_over->def($name) : '' ).'" />');
		}
		return $menu;
	}
	
	
	function savePreferences(&$source_array, $adminidst, $all_admin_permission) {
		
		require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
		
		$aclManager 	=& Docebo::user()->getAclManager();
		$admin_manager 	= new AdminManager();
		
		// Retriving main menu
		$main_area 		= $this->getLevelOne();
		$re = true;
		while(list($id_page, $area_info) = each($main_area)) {
			
			// retriving modules of the main menu
			$query_menu = "
			SELECT idUnder, module_name, default_name, default_op, associated_token, class_file, class_name, of_platform
			FROM ".$this->table_level_two." 
			WHERE idMenu = '".$id_page."' 
			ORDER BY sequence";
			$re_menu = sql_query($query_menu);
			
			while(list($id, $modname, $name, $op, $token, $class_file, $class_name, $of_platform) = sql_fetch_row($re_menu)) {
				
				if(($class_file != '') || ($class_name != '')) {
					
					if($of_platform == NULL) $of_platform = $this->platform;
					
					require_once($GLOBALS['where_'.$of_platform].($of_platform != 'framework' ? '/admin' : '' ).'/class.module/'.$class_file);
					$module = eval("return new $class_name();");
					
					// Retriving all token for this module
					$all_module_token =& $module->getAllToken($op);
					
					
					// Retriving appropiated idst
					$all_module_idst =& $admin_manager->fromRolePathToIdst('/'.$of_platform.'/admin/'.$modname, $all_module_token); 
					
					// Match with the real user permission
					$module_perm =& $admin_manager->modulePermissionAsToken($all_admin_permission, $all_module_idst);
					
					// Retrive new permission
					$selected_token = $module->getSelectedPermission($source_array, $modname, $op);
					
					// Convert selected token to idst
					$selected_idst =& $admin_manager->convertTokenToIdst($selected_token, $all_module_idst );
					
					// Add and remove permission if necessary
					$token_to_add 		= array_diff($selected_idst, $module_perm);
					$token_to_remove 	= array_diff($module_perm, $selected_idst);
					
					$re &= $admin_manager->addRoleToAdmin($token_to_add, $adminidst);
					$re &= $admin_manager->delRoleToAdmin($token_to_remove, $adminidst);
					
				} //end if
				
			} //end inner while
			
		} //end while
		return $re;
	}
	
	function getPermissionUi($all_admin_permission, $form_name) {
		
		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
		
		$out 			=& $GLOBALS['page'];
		$aclManager 	=& Docebo::user()->getAclManager();
		$admin_manager 	= new AdminManager();
		
		// Retriving main menu
		$main_area 	= $this->getLevelOne();
		$html 		= '';
		while(list($id_page, $area_info) = each($main_area)) {
			
			// retriving modules of the main menu
			$query_menu = "
			SELECT idUnder, idMenu, module_name, default_name, default_op, associated_token, class_file, class_name, of_platform 
			FROM ".$this->table_level_two." 
			WHERE idMenu = '".$id_page."' 
			ORDER BY sequence";
			$re_menu = sql_query($query_menu);

			if(sql_num_rows($re_menu)) {
				
				$all_tokens = array();
				$name_menu = array();
				
				$html .= '<div class="admin_menu_perm_modules">';
				while(list($id, $son_of, $modname, $name, $op, $token, $class_file, $class_name, $of_platform) = sql_fetch_row($re_menu)) {
					
					if(($class_file != '') && ($class_name != '')) {
						
						if($of_platform == NULL) $of_platform = $this->platform;
							
						$real_class_file = $GLOBALS['where_'.$of_platform].($of_platform != 'framework' ? '/admin' : '' ).'/class.module/'.$class_file;
						if(file_exists($real_class_file)) {
							
							require_once($real_class_file);
							if(class_exists($class_name)) {
							
							$module = new $class_name();
							
								// Retriving all token for this module
								$all_module_token =& $module->getAllToken($op);
								
								$all_tokens = array_merge($all_tokens, $all_module_token);
								if(!isset($name_menu[$son_of])) {
									$name_menu[$son_of] = ( $name != '' ? $this->lang->def($name) : $this->lang->def('_'.strtoupper($modname)) );
								}
							}
						}
					} //end if
					
				} //end inner while
				
				$tb = new Table(0, '', $this->lang->def('_EDIT_SETTINGS'));
				
				$type 	= array('align_left');
				$c_head = array();
				$c_head[] = ( $area_info['name'] != '' 
					? $area_info['name'].' : '.$this->lang->def('_MODULE_NAME') 
					: ( isset($name_menu[$id_page]) ? $name_menu[$id_page] : '') );
				foreach($all_tokens as $k => $token) {
				
					$type[] 	= 'image';
					$c_head[] = '<img src="'.getPathImage($this->platform).$token['image'].'" alt="'.$this->lang_perm->def($token['name']).'"'
								.' title="'.$this->lang_perm->def($token['name']).'" />';
				}
				
				$tb->setColsStyle($type);
				$tb->addHead($c_head);
				
				$re_menu = sql_query($query_menu);
				while(list($id, $son_of, $modname, $name, $op, $token, $class_file, $class_name, $of_platform) = sql_fetch_row($re_menu)) {
					
					if(($class_file != '') && ($class_name != '')) {
						
						if($of_platform == NULL) $of_platform = $this->platform;
						
						$real_class_file = $GLOBALS['where_'.$of_platform].($of_platform != 'framework' ? '/admin' : '' ).'/class.module/'.$class_file;
						if(file_exists($real_class_file)) {
							
							require_once($real_class_file);
							
							if(class_exists($class_name)) {
								
								$module = new $class_name();
								
								// Retriving all token for this module
								$all_module_token =& $module->getAllToken($op);
								
								// Retriving appropiated idst
								$all_module_idst =& $admin_manager->fromRolePathToIdst('/'.$of_platform.'/admin/'.$modname, $all_module_token); 
								
								// Match with the real user permission
								$module_perm =& $admin_manager->modulePermissionAsToken($all_admin_permission, $all_module_idst);
								
								$line = $module->getPermissionUi(
										( $name != '' ? $this->lang->def($name) : $this->lang->def('_'.strtoupper($modname)) ),
										$modname,
										$op,
										$form_name, 
										$module_perm,
										$all_tokens );
								//echo '<pre>'.print_r($line, true);
								$tb->addBody($line);
							}
						}
					} //end if
					
				} //end inner while
				$html .= $tb->getTable().'</div>';
				
			} // if no module
			
		} //end while
		
		return $html;
	}
}

?>
