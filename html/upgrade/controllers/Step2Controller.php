<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/StepController.php');

Class Step2Controller extends StepController {
	
	public $step = 2;

	public function validate() {
		$_SESSION['start_version'] = Get::req('start_version', DOTY_ALPHANUM, '3603');
		return true;
	}

	public function getNextStep($current_step) {
		$version = Get::req('start_version', DOTY_ALPHANUM, '3603');
		if($version < '4000') return ($current_step + 1);
		else return ($current_step + 3); // skip step 3 and 4 (config upgrade, upgrade from 3 to 4)
	}


	function connectToDb() {
		require_once(_base_.'/config.php');

		if (isset($GLOBALS['dbhost'])) {
			$_SESSION['db_info']['db_host'] = $GLOBALS['dbhost']; //$cfg['db_host'];
			$_SESSION['db_info']['db_user'] = $GLOBALS['dbuname']; //$cfg['db_user'];
			$_SESSION['db_info']['db_pass'] = $GLOBALS['dbpass']; //$cfg['db_pass'];
			$_SESSION['db_info']['db_name'] = $GLOBALS['dbname']; //$cfg['db_name'];
		}
		else if (isset($GLOBALS['cfg']['db_host'])) {
			$_SESSION['db_info']['db_host'] = $GLOBALS['cfg']['db_host'];
			$_SESSION['db_info']['db_user'] = $GLOBALS['cfg']['db_user'];
			$_SESSION['db_info']['db_pass'] = $GLOBALS['cfg']['db_pass'];
			$_SESSION['db_info']['db_name'] = $GLOBALS['cfg']['db_name'];
		}

		$db = mysql_connect($_SESSION['db_info']['db_host'], $_SESSION['db_info']['db_user'], $_SESSION['db_info']['db_pass']);
		mysql_select_db($_SESSION['db_info']['db_name']);

		return $db;
	}

	
	public function getCurrentVersion() {
		$db =$this->connectToDb();
		list($current_version) = mysql_fetch_row(mysql_query("SELECT param_value FROM core_setting WHERE param_name = 'core_version' "));
		mysql_close($db);
		
		$current_version =getVersionIntNumber($current_version);
		
		return $current_version;
	}
	

	function versionList() {

		$current_version =$this->getCurrentVersion();
		$end_version =getVersionIntNumber($GLOBALS['cfg']['endversion']);
		
		if ($current_version == $end_version) {
			$current_version =end(array_keys($GLOBALS['cfg']['versions']));
		}
		
		$txt = '<select id="start_version" name="start_version">';
		foreach($GLOBALS['cfg']['versions'] as $k => $v) {

			$txt .= '<option value="'.$k.'"'.($k == $current_version ? ' selected="selected"' : '' ).'>'.$v.'</option>';
		}
		$txt .= '</select>';
		return $txt;
	}

	function checkRequirements() {
		$res =array();

		phpversion();
		$res['php']=(version_compare(PHP_VERSION, '5.2.0') >= 0 ? 'ok' : 'err');
		$res['mysql']=(version_compare(mysql_get_client_info(), '5.1') >= 0 ? 'ok' : 'err');
		$res['xml']=(extension_loaded('domxml') ? 'ok' : 'err');
		$res['ldap']=(extension_loaded('ldap') ? 'ok' : 'err');
		$res['mbstring']=(extension_loaded('mbstring') ? 'ok' : 'err');
		$res['strict_mode']=($this->checkStrictMode() ? 'ok' : 'err');
		$res['mime_ct']=(function_exists('mime_content_type') || method_exists('finfo', 'file') ? 'ok' : 'err');

		$current_version =$this->getCurrentVersion();
		$end_version =getVersionIntNumber($GLOBALS['cfg']['endversion']);
		
		if ($current_version == $end_version) {
			$res['upg_not_needed']=true;
		}
		
		if ($current_version < 4000) {
			// Upgrader: we check if we are starting from a valid (old) config.php file:
			require_once(_base_.'/config.php');
			$res['config_v3']=(!empty($GLOBALS['dbhost']) ? 'ok' : 'err');			
			$res['config_v4']='err';
		}
		else {
			require_once(_base_.'/config.php');
			$res['config_v4']=(!empty($GLOBALS['cfg']['db_host']) ? 'ok' : 'err');
			$res['config_v3']='err';
		}

		return $res;
	}


	function checkStrictMode() {
		$db =$this->connectToDb();

		$qtxt ="SELECT @@GLOBAL.sql_mode AS res";
		$q =mysql_query($qtxt, $db);
		list($r1)=mysql_fetch_row($q);
		$qtxt ="SELECT @@SESSION.sql_mode AS res";
		$q =mysql_query($qtxt, $db);
		list($r2)=mysql_fetch_row($q);
		$res =((strpos($r1.$r2, 'STRICT_') === false) ? true : false);

		mysql_close($db);
		
		return $res;
	}


	function checkFolderPerm() {
		$res ='';

		$platform_folders=$_SESSION['platform_arr'];
		$file_to_check=array("config.php");
		$dir_to_check=array();
		$empty_dir_to_check=array();

		foreach($platform_folders as $platform_code=>$dir_name) {

			$specific_file_to_check =array();
			$specific_dir_to_check =array();

			if(!is_dir(_base_.'/'.$dir_name.'/')) {
				$install[$platform_code]=FALSE;
			}
			else {
				$install[$platform_code] = TRUE;

				$empty_specific_dir_to_check = NULL;

				switch ($platform_code) {

					case "lms": {
						$specific_dir_to_check = array(
							'files/doceboLms/course',
							'files/doceboLms/forum',
							'files/doceboLms/item',
							'files/doceboLms/message',
							'files/doceboLms/project',
							'files/doceboLms/scorm',
							'files/doceboLms/test' );
						$empty_specific_dir_to_check = array('files/doceboLms/course', 'files/doceboLms/scorm');
					} break;

					case "framework": {
						$specific_dir_to_check = array("files/doceboCore/photo", "files/common/users");
					} break;

				}

				$dir_to_check=array_merge($dir_to_check, $specific_dir_to_check);
				$file_to_check =array_merge($file_to_check , $specific_file_to_check);

				if ((is_array($specific_dir_to_check)) && (count($specific_dir_to_check) > 0) && (is_array($empty_specific_dir_to_check)))
					$empty_dir_to_check=array_merge($empty_dir_to_check, $empty_specific_dir_to_check);
			}
		}

		// Write permission
		$checked_dir 	= array();
		foreach($dir_to_check as $dir_name) {

			if(!is_dir(_base_.'/'.$dir_name.'/')) {
				$checked_dir[] = $dir_name;
			} elseif(!is_writable(_base_.'/'.$dir_name.'/')) {
				$checked_dir[] = $dir_name;
			}
		}
		if(!empty($checked_dir)) {

			$res.='<h3>'.Lang::t('_CHECKED_DIRECTORIES').'</h3>'
				.'<ul class="info"><li class="err">'.implode('</li><li class="err">',$checked_dir).'</li></ul>';
		}

		$checked_file 	= array();
		foreach($file_to_check as $file_name) {
			if(!is_writable(_base_.'/'.$file_name)) {
				$checked_file[] = $file_name;
			}
		}
		if(!empty($checked_file)) {

			$res.='<h3>'.Lang::t('_CHECKED_FILES').'</h3>'
				.'<ul class="info"><li class="err">'.implode('</li><li class="err">',$checked_file).'</li></ul>';
		}

		return $res;
	}

}

?>