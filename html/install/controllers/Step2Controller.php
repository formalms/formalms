<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step2Controller extends StepController {

	var $step=2;


	public function validate() {
		return true;
	}

}


function checkRequirements() {
	$res =array();

	// phpversion();
	// PHP_VERSION version supported 5.2.x 5.3.x 5.4.x -- experimental 5.5.x 5.6.x
	$res['php']=((version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.5.0', '<')) ? 'ok' :
                     ((version_compare(PHP_VERSION, '5.5.0', '>=') && version_compare(PHP_VERSION, '5.7.0', '<')) ? 'warn' :  'err' ));
	// mysql version, in easyphp the version number is ina string regcut it
	preg_match( '/([0-9]+\.[\.0-9]+)/', mysql_get_client_info(), $version );
	if(empty($version[1])) $res['mysql']='ok';
	else $res['mysql']=(version_compare($version[1], '5.0') >= 0 ? 'ok' : 'err');
	$res['xml']=(extension_loaded('domxml') ? 'ok' : 'err');
	$res['mbstring']=(extension_loaded('mbstring') ? 'ok' : 'err');
	$res['ldap']=(extension_loaded('ldap') ? 'ok' : 'err');
	$res['openssl']=( extension_loaded('openssl') ? 'ok' : 'err');
	$res['allow_url_fopen']=  ($php_conf['allow_url_fopen']['local_value'] ? 'ok' : 'err');
	$res['allow_url_include'] = ($php_conf['allow_url_include']['local_value'] ? 'err' : 'ok' );
	$res['mime_ct']=(function_exists('mime_content_type') || (class_exists('file') && method_exists('finfo', 'file')) ? 'ok' : 'err');


	return $res;
}


function checkFolderPerm() {
	$res ='';

	$platform_folders=$_SESSION['platform_arr'];
	$file_to_check=array("config.php");
	$dir_to_check=array();
	$empty_dir_to_check=array();

	// common dir to check
	$dir_to_check = array(
		'files/tmp',
		'files/common/comment',
		'files/common/iofiles',
		'files/common/users'
		);

	foreach($platform_folders as $platform_code=>$dir_name) {

		$specific_file_to_check =array();
		$specific_dir_to_check =array();

		if(!is_dir(_base_.'/'.$dir_name.'/')) {
			$install[$platform_code]=FALSE;
		}
		else {
			$install[$platform_code]=TRUE;

			$empty_specific_dir_to_check=NULL;

			switch ($platform_code) {

				case "lms": {
					$specific_dir_to_check = array(
						'files/appLms/certificate',
						'files/appLms/chat',
						'files/appLms/forum',
						'files/appLms/htmlpages',
						'files/appLms/item',
						'files/appLms/label',
						'files/appLms/message',
						'files/appLms/project',
						'files/appLms/repo_light',
						'files/appLms/sponsor',
						'files/appLms/test'
						);
					$empty_specific_dir_to_check = array('files/appLms/course', 'files/appLms/scorm');
				} break;

				case "framework": {
					$specific_dir_to_check = array(
						'files/appCore/field',
						'files/appCore/photo',
						'files/appCore/newsletter',
						'files/common/users'
						);
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

?>