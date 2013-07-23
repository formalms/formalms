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

require_once(_adm_.'/class/class.admin_menu.php');
//require_once(_i18n_.'/lib.lang.php');

class Admin_Scs extends Admin {

	/**
	 * class constructor
	 * @param 	FormaUser 	$user	the object of the Forma User, for permission control
	 *
	 * @return nothing
	 * @access public
	 */
	function Admin_Scs(&$user) {
		$this->user =& $user;
		$this->platform = 'scs';
		$this->table_level_one = $GLOBALS['prefix_scs'].'_menu';
		$this->table_level_two = $GLOBALS['prefix_scs'].'_menu_under';
	}

	/**
	 * @return	mixed	a list of the first level menu
	 *					[id] (	[link]
	 *							[image]
	 *						 	[name]  )
	 * @access public
	 */
	 function getLevelOne() {

		return array(); // disabling the menu

		$lang =& DoceboLanguage::createInstance('menu', $this->platform);

		$query_under = "
		SELECT tab.idMenu, menu.module_name, menu.associated_token, tab.name, tab.image, tab.collapse, menu.of_platform
		FROM ".$this->table_level_one." AS tab JOIN ".$this->table_level_two." AS menu
		WHERE tab.idMenu = menu.idMenu
		ORDER BY tab.sequence";
		$re_under = sql_query($query_under);
		

		$menu = array();
		while(list($id_main, $module_name, $token, $name, $image, $collapse, $of_platform) = sql_fetch_row($re_under)) {

			if(!isset($menu[$id_main]) && checkPerm($token, true, $module_name, ( $of_platform === NULL ? $this->platform : $of_platform ) )) {

				$menu[$id_main] = array('link' => 'index.php?op=change_main&new_main='.$id_main.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform ),
									'name' => ( $name != '' ? $lang->def($name)  : '' ),
									'image' => 'area_title/'.$image,
									'collapse' => ( $collapse == 'true' ? true : false ),
									'of_platform' => ( $of_platform === NULL ? $this->platform : $of_platform ));
			}
		}
		return $menu;
	 }

	 function getLevelOneIntest($idMenu) {
		$lang =& DoceboLanguage::createInstance('menu', $this->platform);

		$query_menu = "
		SELECT name, image
		FROM ".$this->table_level_one."
		WHERE idMenu = '".(int)$idMenu."'";
		$re_menu = sql_query($query_menu);
		list($name, $image) = sql_fetch_row($re_menu);

		return array(
			'name' => $lang->def($name),
			'image' => getPathImage('scs').'area_title/'.$image
		);
	 }

	 /**
	  * @param 	int 	$id_level_one	the id of a level one menu voice
	  *
	  * @return	mixed	a list of the second level menu of a passed first level menu,
	  *					if not passed return all the voice of the second level
	  *					[id] (	[link]
	  *						 	[name]  )
	  * @access public
	  */
	 function getLevelTwo($id_level_one = false) {

		 $lang =& DoceboLanguage::createInstance('menu', $this->platform);

		  $query_menu = "
		 SELECT idUnder, module_name, default_name, default_op, associated_token, of_platform
		 FROM ".$this->table_level_two."
		 ".( $id_level_one !== false  ? " WHERE idMenu = '".$id_level_one."'" : "")."
		 ORDER BY sequence";
		 $re_menu = sql_query($query_menu);

		 $menu = array();
		 while(list($id, $modname, $name, $op, $token, $of_platform) = sql_fetch_row($re_menu)) {

			 if($this->user->matchUserRole('/'.( $of_platform === NULL ? $this->platform : $of_platform ).'/admin/'.$modname.'/'.$token)) {
				 $menu[$id] = array('modname' => $modname,
				 					'op' => $op,
									'link' => 'index.php?modname='.$modname.'&op='.$op.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform ),
									'name' => ( $name != '' ? $lang->def($name) : $lang->def('_'.strtoupper($modname)) ),
									'of_platform' => ( $of_platform === NULL ? $this->platform : $of_platform ) );
			 }
		 }
		 return $menu;
	 }
}

class Admin_Managment_Scs extends Admin_Managment {

	/**
	 * class constructor
	 * @return	nothing
	 * @access public
	 */
	function Admin_Managment_Scs() {
		$this->platform = 'scs';
		$this->table_level_one = $GLOBALS['prefix_scs'].'_menu';
		$this->table_level_two = $GLOBALS['prefix_scs'].'_menu_under';
	}


	function getLevelOne() {

		$lang =& DoceboLanguage::createInstance('menu', $this->platform);

		$query_menu = "
		SELECT idMenu, name, image
		FROM ".$this->table_level_one."
		ORDER BY sequence";
		$re_menu = sql_query($query_menu);

		$menu = array();
		while(list($id, $name, $image) = sql_fetch_row($re_menu)) {

		 $menu[$id] = array('name' => $lang->def($name),
							'image' => '<img src="'.getPathImage('lms').'/menu/'.$image.'" alt="'.$lang->def($name).'" />');
		}
		return $menu;
	}

	function savePreferences(&$source_array, $base_path, $adminidst, $all_admin_permission) {

		require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
		$aclManager 	=& Docebo::user()->getAclManager();
		$admin_manager 	= new AdminManager();

		// Retriving main menu
		$main_area 		= $this->getLevelOne();
		$re = true;
		while(list($id_page, $area_info) = each($main_area)) {

			// retriving modules of the main menu
			$query_menu = "
			SELECT idUnder, module_name, default_name, default_op, associated_token, class_file, class_name
			FROM ".$this->table_level_two."
			WHERE idMenu = '".$id_page."'
			ORDER BY sequence";
			$re_menu = sql_query($query_menu);

			while(list($id, $modname, $name, $op, $token, $class_file, $class_name) = sql_fetch_row($re_menu)) {

				if(($class_file != '') || ($class_name != '')) {

					require_once($GLOBALS['where_lms'].'/admin/class.module/'.$class_file);
					$module = eval("return new $class_name();");

					// Retriving all token for this module
					$all_module_token =& $module->getAllToken($op);

					// Retriving appropiated idst
					$all_module_idst =& $admin_manager->fromRolePathToIdst($base_path.'/'.$modname, $all_module_token);

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

	function getPermissionUi($all_admin_permission, $base_path, $form_name) {

		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');

		$lang 			=& DoceboLanguage::createInstance('menu', 'lms');
		$out 			=& $GLOBALS['page'];

		$aclManager 	=& Docebo::user()->getAclManager();
		$admin_manager 	= new AdminManager();

		// Retriving main menu
		$main_area 		= $this->getLevelOne();
		$html = '';
		while(list($id_page, $area_info) = each($main_area)) {

			// retriving modules of the main menu
			$query_menu = "
			SELECT idUnder, module_name, default_name, default_op, associated_token, class_file, class_name
			FROM ".$this->table_level_two."
			WHERE idMenu = '".$id_page."'
			ORDER BY sequence";
			$re_menu = sql_query($query_menu);

			$html .= '<div class="admin_menu_perm_title">'.$area_info['name'].'</div>'
				.'<div class="admin_menu_perm_modules">';

			while(list($id, $modname, $name, $op, $token, $class_file, $class_name) = sql_fetch_row($re_menu)) {

				if(($class_file != '') || ($class_name != '')) {

					require_once($GLOBALS['where_lms'].'/admin/class.module/'.$class_file);
					$module = eval("return new $class_name();");

					// Retriving all token for this module
					$all_module_token =& $module->getAllToken($op);

					// Retriving appropiated idst
					$all_module_idst =& $admin_manager->fromRolePathToIdst($base_path.'/'.$modname, $all_module_token);

					// Match with the real user permission
					$module_perm =& $admin_manager->modulePermissionAsToken($all_admin_permission, $all_module_idst);

					$html .= '<div class="edit_menu_module">'
						.$module->getPermissionUi(
							( $name != '' ? $lang->def($name) : $lang->def('_'.strtoupper($modname)) ),
							$modname,
							$op,
							$form_name,
							$module_perm )
						.'</div>';
				} //end if

			} //end inner while

			$html .= '</div>';
		} //end while

		return $html;
	}

}

?>
