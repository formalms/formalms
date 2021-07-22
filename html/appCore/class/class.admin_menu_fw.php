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

class Admin_Framework extends Admin {

	/**
	 * class constructor
	 * @param 	FormaUser 	$user	the object of the Forma User, for permission control
	 *
	 * @return	nothing
	 * @access public
	 */
function reorder() {
    $asrc = $this->menu;
    $adst = array();

    if (is_array($asrc)) {
        foreach ($asrc as $key=>$value) {
            if (is_array($value)) {
                $adst[$key] = $this->arrorder($value);
            }
        }
    }
$_a=$adst;
    $this->menu = $adst;
    
}

function arrorder($asrc = array(), $idparent = false) {
    $adst = array();
    if (is_array($asrc)) {
        //get target level
        foreach ($asrc as $key=>$value) {
            if (is_array($value)) {
                $_idparent = $value['idparent'];
                if ($_idparent == $idparent){
                    $adst[$key] = $value;
                }
            }
        }
        //exclude elements got
        foreach ($asrc as $key=>$value) {
            if (array_key_exists($key, $adst)) {
                unset($asrc[$key]);
            }
        }
        //inspect below levels
        foreach ($adst as $key=>$value) {
            $aunder = $this->arrorder($asrc, $key);
            if (is_array($aunder) && ! empty($aunder)) {
                $adst[$key]['under'] = $aunder;
            }
        }
    }
    return $adst;
}
        function Admin_Framework(&$user) {
		$this->user =& $user;
		$this->platform = 'framework';
		$this->table_level_one = $GLOBALS['prefix_fw'].'_menu';
		$this->table_level_two = $GLOBALS['prefix_fw'].'_menu_under';

		$lang =& DoceboLanguage::createInstance('menu', $this->platform);

		$query_menu = "
		SELECT idMenu, idUnder, module_name, default_name, default_op, associated_token, of_platform, mvc_path, idParent
		FROM ".$this->table_level_two."
		WHERE 1
		ORDER BY sequence";
		$re_menu = sql_query($query_menu);


		$this->menu = array();
		while(list($idm, $id, $module_name, $name, $op, $token, $of_platform, $mvc_path, $idparent) = sql_fetch_row($re_menu)) {

			if($this->user->matchUserRole('/'.( $of_platform === NULL ? $this->platform : $of_platform ).'/admin/'.$module_name.'/'.$token)) {
				 $this->menu[$idm][$id] = array('modname' => $module_name,
				 					'op' => $op,
									'link' => ( $mvc_path == ''
										? 'index.php?modname='.$module_name.'&op='.$op.'&of_platform='.( $of_platform === NULL ? $this->platform : $of_platform )
										: 'index.php?r='.$mvc_path
									),
									'name' => ( $name != '' ? $lang->def($name) : $lang->def('_'.strtoupper($module_name)) ),
									'of_platform' => ( $of_platform === NULL ? $this->platform : $of_platform ) 
									, 'idparent' => $idparent 
                                                                );
			}
		}
                $this->reorder();

	}

	/**
	 * @return	mixed	a list of the first level menu
	 *					[id] (	[link]
	 *							[image]
	 *						 	[name]  )
	 * @access public
	 */
	 function getLevelOne($idmenu = false) {

		$lang =& DoceboLanguage::createInstance('menu', $this->platform);


		$query_under = "
		SELECT tab.idMenu, menu.module_name, menu.associated_token, tab.name, tab.image, tab.collapse, menu.of_platform
		FROM ".$this->table_level_one." AS tab JOIN ".$this->table_level_two." AS menu
		WHERE tab.idMenu = menu.idMenu";
		if ($idmenu){
                    $query_under .= " and tab.idMenu=".$idmenu;
                }
		$query_under .= " ORDER BY tab.sequence";
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
	/**
	 * @return	mixed	a list of the first level menu
	 *					[id] (	[link]
	 *							[image]
	 *						 	[name]  )
	 * @access public
	 */
	 function getLevel($idmenu = false, $idparent = false) {

		$lang =& DoceboLanguage::createInstance('menu', $this->platform);


		$query_under = "
		SELECT tab.idMenu, menu.module_name, menu.associated_token, tab.name, tab.image, tab.collapse, menu.of_platform
		FROM ".$this->table_level_one." AS tab JOIN ".$this->table_level_two." AS menu
		WHERE tab.idMenu = menu.idMenu";
		if ($idmenu){
                    $query_under .= " and tab.idMenu=".$idmenu;
                }
		if ($idparent){
                    $query_under .= " and menu.idParent=".$idparent;
                }
                else{
                    $query_under .= " and menu.idParent is null";
                }
		$query_under .= " ORDER BY tab.sequence";
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
}

class Admin_Managment_Framework extends Admin_Managment {

	/**
	 * class constructor
	 * @return	nothing
	 * @access public
	 */
	function Admin_Managment_Framework() {

		$this->platform 		= 'framework';
		$this->table_level_one 	= $GLOBALS['prefix_fw'].'_menu';
		$this->table_level_two 	= $GLOBALS['prefix_fw'].'_menu_under';

		$this->lang_over 		=& DoceboLanguage::createInstance('menu', 'framework');
		$this->lang 			=& DoceboLanguage::createInstance('menu', 'framework');
		$this->lang_perm 		=& DoceboLanguage::createInstance('permission');
	}

	/* all the other method is inherited from the parent class*/

}

?>
