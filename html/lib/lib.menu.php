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
 * @package  admin-library
 * @subpackage menu
 * @author 		jt
 * @version 	
 */

class MenuManager {

	var $db_conn;
	var $prefix;
	var $menu;
	var $platform;

	function _setPrefix() {
            switch ($this->platform){
                case 'adm':
                case 'framework':
                case 'alms':
                    $this->prefix = $GLOBALS['prefix_fw'];
                    break;
                case 'lms':
                    $this->prefix = $GLOBALS['prefix_lms'];
                    break;
                default:
                    break;
            }
	}

        function _getTableMenu() {
		return $this->prefix.'_menu';
	}

        function _getTableMenuUnder() {
		return $this->prefix.'_menu_under';
	}

	function _executeQuery($query) {
		if($this->db_conn !== NULL){
                    return sql_query($query, $this->db_conn);
                }
		else{
                    return sql_query($query);
                }
	}

	function &createInstance($platform) {
            //if(!isset($GLOBALS['menu_manager'])) {
            $GLOBALS['menu_manager'] = new MenuManager($platform);
            //}
            return $GLOBALS['menu_manager'];
	}

	function MenuManager($platform) {
            $this->platform = $platform;
            $this->_setPrefix();
            $lang =& DoceboLanguage::createInstance('menu', $this->platform);
            $this->user =Docebo::user();//& $user;

            $this->menu = array();

            // load menus information
            $query_menu = "SELECT m.idMenu, m.name, m.image, m.idParent"; //-- , m.sequence, m.is_active -- , mu.idUnder, mu.idMenu
            $query_menu .= ", m.collapse, mu.idUnder, mu.module_name, mu.default_name, mu.default_op, mu.associated_token, mu.of_platform , mu.sequence";
            $query_menu .= ", mu.class_file, mu.class_name, mu.mvc_path";
            $query_menu .= " FROM ".$this->_getTableMenu()." m";
            $query_menu .= " left outer join ".$this->_getTableMenuUnder()." mu on m.idMenu = mu.idMenu";
            $query_menu .= " WHERE 1 and m.is_active=1";//-- idParent is null
            $query_menu .= " ORDER BY m.sequence, mu.sequence";
            $re_menu = $this->_executeQuery($query_menu);
            while($menu = sql_fetch_assoc($re_menu)){
                $id_main = $menu['idMenu'];
                $name = $menu['name'];
                $module_name = $menu['module_name'];
                $mvc_path = $menu['mvc_path'];
                $image = $menu['image'];
                $collapse = $menu['collapse'];
                $of_platform = $menu['of_platform'];
                $id_under = $menu['idUnder'];
                $op = $menu['default_op'];
                if(!isset($menu[$idUnder]) && checkPerm($token, true, $module_name, ( $of_platform === NULL ? $this->platform : $of_platform ) )) {

                    $menu['name'] = ( $name != '' ? $lang->def($name)  : '' );
                    $menu['image'] = 'area_title/'.$image;
                    $menu['collapse'] = ( $collapse == 'true' ? true : false );
                    $menu['of_platform'] = ( $of_platform === NULL ? $this->platform : $of_platform );
                    //$menu['link'] = 'index.php?op=change_main&new_main='.$id_main.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform );
                    if ($id_under){
                        if($this->user->matchUserRole('/'.( $of_platform === NULL ? $this->platform : $of_platform ).'/admin/'.$module_name.'/'.$token)) {
                            $menu['link'] = ( $mvc_path == ''
                                                ? 'index.php?modname='.$module_name.'&op='.$op.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform )
                                                : 'index.php?r='.$mvc_path
									);
                            $menu['op'] = $op;
                            $menu['modname'] = $module_name;
//				 $this->menu[$idm][$id] = array('modname' => $module_name,
//				 					'op' => $op,
//									'link' => ( $mvc_path == ''
//										? 'index.php?modname='.$module_name.'&op='.$op.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform )
//										: 'index.php?r='.$mvc_path
//									),
//									'name' => ( $name != '' ? $lang->def($name) : $lang->def('_'.strtoupper($module_name)) ),
//									'of_platform' => ( $of_platform === NULL ? $this->platform : $of_platform ) 
//									, 'idparent' => $idparent 
//                                                                );
			}
                    }
                        
                }
                $this->menu[$id_main] = $menu;
            }
	}

	function isLoaded($menu) {

		return (isset($this->menu[$menu]) && $this->menu[$menu]['is_active'] == 'true');
	}

	function getLevel($idParent = null) {
//die(__FILE__.'->'.__FUNCTION__.'(...)');
		//$lang =& DoceboLanguage::createInstance('menu', 'framework');

            $menu_list = array();
            foreach($this->menu as $menu_code => $menu_info) {
                if($menu_info['idParent'] == $idParent) {
                    $menu_list[$menu_code] = $menu_info;
                }
            }
            
            if($exclude_framework === true) unset($menu_list['framework']);
            return $menu_list;
	}

    function &getMenuInstanceFramework($menu) {
        if($GLOBALS['where_framework'] === false) {
            $false_var = false;
            return $false_var;
	}


$class_file_name = $this->menu[$menu]['class_file_menu'] != '' ? $this->menu[$menu]['class_file_menu'] : 'class.admin_menu_fw.php';
        if(!file_exists($GLOBALS['where_framework'].'/class/'.$class_file_name))  {
            $false_var = false;
            return $false_var;
	}
$class_menu_name = $this->menu[$menu]['class_name_menu'] != '' ? $this->menu[$menu]['class_name_menu'] : 'Admin_Framework';
        require_once($GLOBALS['where_framework'].'/class/'.$class_file_name);
        $menu = eval(" return new ".$class_menu_name."( \$GLOBALS['current_user']); ");

        return $menu;
	}

	function getAdminMenu() {

		$html = '';
		foreach($this->menu as $menu_code => $pl_info) {

			if($GLOBALS['where_'.$menu_code] !== false && $pl_info['is_active'] == 'true') {

				require_once($GLOBALS['where_'.$menu_code].'/class/'.$pl_info['class_file_menu']);

				$menu = eval(" return new ".$pl_info['class_name_menu']."( \$GLOBALS['current_user']); ");
				$main_voice = $menu->getLevelOne();

				if(!isset($_SESSION['current_admin_id_menu'])) {
					$_SESSION['current_admin_id_menu'] = key($main_voice);
				}
				foreach($main_voice as $id_m => $voice) {

					$html .= '<li '.
					( $_SESSION['current_admin_id_menu'] == $id_m && $_SESSION['current_action_menu'] == $menu_code ?
						' class="active"' :
						'' ).'>'
					.'<a href="'.Util::str_replace_once('&', '&amp;',  $voice['link'].'">'.$voice['name']).'</a></li>';
				}
			}
		}
		return $html;
	}

    
    


}

// MenuManager::createInstance();




?>
