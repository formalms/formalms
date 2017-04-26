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
 * @subpackage platform
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.pagewriter.php 852 2006-12-16 14:04:44Z giovanni $
 */

class PlatformManager {

	var $db_conn;
	var $prefix;
	var $platform;

	function _getTable() {

		return $this->prefix.'_platform';
	}

	function _executeQuery($query) {
		if($this->db_conn !== NULL) return sql_query($query, $this->db_conn);
		else return sql_query($query);
	}

	function &createInstance() {

		if(!isset($GLOBALS['platform_manager'])) {
			$GLOBALS['platform_manager'] = new PlatformManager();
		}
		return $GLOBALS['platform_manager'];
	}

	function PlatformManager($db_conn = false, $prefix = false) {

		if($prefix === false) $this->prefix = $GLOBALS['prefix_fw'];
		else $this->prefix = $prefix;

		if($db_conn === false) $db_conn->prefix = NULL;
		else $this->db_conn = $db_conn;

		$this->platform = array();

		// load platforms information
		$query_platform = "
		SELECT platform, class_file, class_name,
			class_file_menu, class_name_menu, class_name_menu_managment,
			file_class_config, class_name_config,
			var_default_template,
			class_default_admin,
			mandatory, is_active, dependencies, main, hidden_in_config
		FROM ".$this->_getTable()."
		ORDER BY sequence";
		$re_platform = $this->_executeQuery($query_platform);
		while($assoc = sql_fetch_assoc($re_platform)){

			$this->platform[$assoc['platform']] = $assoc;
		}
	}

	function isLoaded($platform) {

		return (isset($this->platform[$platform]) && $this->platform[$platform]['is_active'] == 'true');
	}

	/**
	 * @param bool 	$return_lang 	if true return the name of the platofrm, else return code
	 *
	 * @return return the code or the name of the home platform, if none return false
	 */
	function getHomePlatform($return_lang = false) {

		$lang =& DoceboLanguage::createInstance('platform', 'framework');

		$platform_list = array();
		foreach($this->platform as $platform_code => $platform_info) {

			if($platform_info['main'] == 'true') {

				if($return_lang) return $lang->def('_'.strtoupper($platform_code));
				else return $platform_code;
			}
		}
		return false;
	}

	function getPlatformsInfo() {

		$lang =& DoceboLanguage::createInstance('platform', 'framework');

		$platform_list = $this->platform;
		foreach($this->platform as $platform_code => $platform_info) {

			$platform_list[$platform_code]['name'] = $platform_code;
		}
		return $platform_list;
	}

	function getPlatformList($exclude_framework = false, $also_inactive = false) {

		$lang =& DoceboLanguage::createInstance('platform', 'framework');

		$platform_list = array();
		foreach($this->platform as $platform_code => $platform_info) {

			if($platform_info['is_active'] == 'true') {

				$platform_list[$platform_code] = $platform_code;
			}
		}
		if($exclude_framework === true) unset($platform_list['framework']);
		return $platform_list;
	}

	function getActivePlatformList($exclude_framework = false) {

		$lang =& DoceboLanguage::createInstance('platform', 'framework');

		$platform_list = array();
		foreach($this->platform as $platform_code => $platform_info) {

			if($GLOBALS['where_'.$platform_code] !== false && $platform_info['is_active'] == 'true')
				$platform_list[$platform_code] = $platform_code;
		}
		if($exclude_framework === true) unset($platform_list['framework']);
		return $platform_list;
	}

	function activatePlatform($platform) {

		$query_platform = "UPDATE ".$this->_getTable()."
		SET is_active = 'true'
		WHERE platform = '".$platform."'";
		if($this->_executeQuery($query_platform)) {

			$this->platform[$platform]['is_active'] = 'true';
			return true;
		} else return false;
	}

	function deactivatePlatform($platform) {

		$query_platform = "UPDATE ".$this->_getTable()."
		SET is_active = 'false'
		WHERE platform = '".$platform."' AND mandatory = 'false'";
		if($this->_executeQuery($query_platform)) {

			$this->platform[$platform]['is_active'] = 'false';
			return true;
		} else return false;
	}

	function putInHome($platform) {

		$query_platform = "
		UPDATE ".$this->_getTable()."
		SET main = 'false'";
		if($this->_executeQuery($query_platform)) {

			foreach($this->platform as $code => $info) {

				$this->platform[$code]['main'] = 'false';
			}
		} else return false;

		$query_platform = "
		UPDATE ".$this->_getTable()."
		SET main = 'true'
		WHERE platform = '".$platform."'";

		if($this->_executeQuery($query_platform)) {

			$this->platform[$platform]['main'] = 'true';
			return true;
		} else return false;
	}

	function getLanguageForPlatform($platform = FALSE ) {

		return Get::sett('default_language');
	}

	function getTemplateForPlatform($platform = FALSE ) {

		if(!isset($this->platform[$platform]['var_default_template'])) {
			$platform = 'framework';
		}
		$temp_var = $this->platform[$platform]['var_default_template'];

		if(!isset($GLOBALS[$platform][$temp_var])) {
			$temp_var = $this->platform['framework']['var_default_template'];
			$platform = 'framework';
		}
		return $GLOBALS[$platform][$temp_var];
	}

	function getAdminMenu() {

		$html = '';
		foreach($this->platform as $platform_code => $pl_info) {

			if($GLOBALS['where_'.$platform_code] !== false && $pl_info['is_active'] == 'true') {

				require_once($GLOBALS['where_'.$platform_code].'/class/'.$pl_info['class_file_menu']);

				$menu = eval(" return new ".$pl_info['class_name_menu']."( \$GLOBALS['current_user']); ");
				$main_voice = $menu->getLevelOne();

				if(!isset($_SESSION['current_admin_id_menu'])) {
					$_SESSION['current_admin_id_menu'] = key($main_voice);
				}
				foreach($main_voice as $id_m => $voice) {

					$html .= '<li '.
					( $_SESSION['current_admin_id_menu'] == $id_m && $_SESSION['current_action_platform'] == $platform_code ?
						' class="active"' :
						'' ).'>'
					.'<a href="'.Util::str_replace_once('&', '&amp;',  $voice['link'].'">'.$voice['name']).'</a></li>';
				}
			}
		}
		return $html;
	}

	function &getPlatofmMenuInstance($platform) {

		if($GLOBALS['where_'.$platform] === false) {
			$false_var = false;
			return $false_var;
		}
		if(!file_exists($GLOBALS['where_'.$platform].'/class/'.$this->platform[$platform]['class_file_menu']))  {
			$false_var = false;
			return $false_var;
		}

		require_once($GLOBALS['where_'.$platform].'/class/'.$this->platform[$platform]['class_file_menu']);
		$menu = eval(" return new ".$this->platform[$platform]['class_name_menu']."( \$GLOBALS['current_user']); ");

		return $menu;
	}
    
    
    
    function &getPlatofmMenuInstanceFramework($platform) {
 
        
        if($GLOBALS['where_framework'] === false) {
            $false_var = false;
            return $false_var;
        }
  
  
  
        if(!file_exists($GLOBALS['where_framework'].'/class/'.$this->platform[$platform]['class_file_menu']))  {
            $false_var = false;
            return $false_var;
        }

        require_once($GLOBALS['where_framework'].'/class/'.$this->platform[$platform]['class_file_menu']);
        $menu = eval(" return new ".$this->platform[$platform]['class_name_menu']."( \$GLOBALS['current_user']); ");

        return $menu;
    }    
    
    
    
    
	function &getPlatformAdminMenuInstance($platform) {

		if($GLOBALS['where_'.$platform] === false) return false;
		if(!file_exists($GLOBALS['where_'.$platform].'/class/'.$this->platform[$platform]['class_file_menu'])
			|| $this->platform[$platform]['class_name_menu_managment'] == ''
			|| !class_exists($this->platform[$platform]['class_name_menu_managment']) )  {

			$false_var = false;
			return $false_var;
		}

		require_once($GLOBALS['where_'.$platform].'/class/'.$this->platform[$platform]['class_file_menu']);
		$class = $this->platform[$platform]['class_name_menu_managment'];
		$menu = new $class();

		return $menu;
	}

	function &getPlatofmConfigInstance($platform) {

		if($GLOBALS['where_'.$platform] === false)  {
			$false_var = false;
			return $false_var;
		}

		require_once($GLOBALS['where_framework'].'/class/class.conf.php');
		require_once($GLOBALS['where_'.$platform].'/class/'.$this->platform[$platform]['file_class_config']);
		$conf = eval(" return new ".$this->platform[$platform]['class_name_config']."(); ");

		return $conf;
	}


	function doCommonOperations($action) {
		$valid_actions=array("login", "logout");
		$action=strtolower($action);

		if (!in_array($action, $valid_actions))
			return FALSE;

		$platform_list=$this->getActivePlatformList();

		foreach($platform_list as $platform_code=>$label) {

			if (isset($GLOBALS["where_".$platform_code])) {

				$fname=$GLOBALS["where_".$platform_code]."/lib/lib.commonoperations.php";
				if (file_exists($fname)) {

					require_once($fname);
					$run_function=$platform_code.ucfirst($action)."Operation";

					if (function_exists($run_function)) {
						$run_function();
					}
				}
			}
		}
	}


}

PlatformManager::createInstance();



// ----------------------------------------------------------------------------
// Generic platform related utility functions


function isPlatformActive($platform) {
	$pl_man =& PlatformManager::CreateInstance();
	$res =$pl_man->isLoaded($platform);

	return $res;
}


function isPlatformInstalled($platform) {
	$res =file_exists($GLOBALS['where_'.$platform]);
	return $res;
}


function canUsePlatform($platform) {
	$res =(isPlatformActive($platform) && isPlatformInstalled($platform) ? TRUE : FALSE);

	return $res;
}



?>
