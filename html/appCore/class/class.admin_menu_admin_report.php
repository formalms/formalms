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

require_once(dirname(__FILE__).'/class.admin_menu.php');
//require_once(_i18n_.'/lib.lang.php');

class Admin_Framework_Report extends Admin {

	/**
	 * class constructor
	 * @param 	FormaUser 	$user	the object of the Forma User, for permission control
	 *
	 * @return	nothing
	 * @access public
	 */
	function Admin_Framework_Report(&$user) {
		$this->user =& $user;
		$this->platform = 'framework';
		$this->table_level_one = $GLOBALS['prefix_fw'].'_menu_report';
		$this->table_level_two = $GLOBALS['prefix_fw'].'_menu_under_report';

		//$lang =& DoceboLanguage::createInstance('menu', $this->platform);

		$query_menu = "
		SELECT idMenu, idUnder, module_name, default_name, default_op, associated_token, of_platform, mvc_path
		FROM ".$this->table_level_two."
		WHERE 1
		ORDER BY sequence";
        
       // echo "<br>step 1: ".$query_menu;
        
		$re_menu = sql_query($query_menu);


        $lang     =& DoceboLanguage::createInstance('menu', 'menu_report');
        
		$this->menu = array();
		while(list($idm, $id, $module_name, $name, $op, $token, $of_platform, $mvc_path) = sql_fetch_row($re_menu)) {

			if($this->user->matchUserRole('/'.( $of_platform === NULL ? $this->platform : $of_platform ).'/admin/'.$module_name.'/'.$token)) {
				 $this->menu[$idm][$id] = array('modname' => $module_name,
				 					'op' => $op,
									'link' => ( $mvc_path == ''
										? 'index.php?modname='.$module_name.'&op='.$op.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform )
										: 'index.php?r='.$mvc_path
									),
									'name' => ( $name != '' ? $lang->def($name) : $lang->def('_'.strtoupper($module_name)) ),
									'of_platform' => ( $of_platform === NULL ? $this->platform : $of_platform ) );
			}
		}

	}

	/**
	 * @return	mixed	a list of the first level menu
	 *					[id] (	[link]
	 *							[image]
	 *						 	[name]  )
	 * @access public
	 */
	 function getLevelOne() {
  
         
		$lang =& DoceboLanguage::createInstance('menu', $this->platform);

          
		$query_under = "
		SELECT tab.idMenu, menu.module_name, menu.associated_token, tab.name, tab.image, tab.collapse, menu.of_platform
		FROM ".$this->table_level_one." AS tab JOIN ".$this->table_level_two." AS menu
		WHERE tab.idMenu = menu.idMenu
		ORDER BY tab.sequence";

        
		$re_under = sql_query($query_under);
		
        $lang     =& DoceboLanguage::createInstance('menu', 'menu_2');

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
			'name' => ( $name != '' ? $lang->def($name) : '' ),
			'image' => getPathImage('framework').'area_title/'.$image
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

		 return $this->menu[$id_level_one];
	 }
}

class Admin_Managment_Framework_Report extends Admin_Managment {

	/**
	 * class constructor
	 * @return	nothing
	 * @access public
	 */
	function Admin_Managment_Framework() {

		$this->platform 		= 'framework';
		$this->table_level_one 	= $GLOBALS['prefix_fw'].'_menu_report';
		$this->table_level_two 	= $GLOBALS['prefix_fw'].'_menu_under_report';

		$this->lang_over 		=& DoceboLanguage::createInstance('menu', 'framework');
		$this->lang 			=& DoceboLanguage::createInstance('menu', 'framework');
		$this->lang_perm 		=& DoceboLanguage::createInstance('permission');
	}

	/* all the other method is inherited from the parent class*/

}

?>
