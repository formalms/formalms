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

if(!function_exists('importVar')) {
function importVar($index, $default_value = '') {
	
	if(isset($_POST[$index])) return $_POST[$index];
	if(isset($_GET[$index])) return $_GET[$index];
	return $default_value;
}
}

function loginTheUser($data) {
	
	$user = new User_VR(0);

	$re = $user->logUser(	false, 
							$data['userid'], 
							$data['lastname'], 
							$data['firstname'], 
							$data['id_room'], 
							$data['role'] );
	return $re;
}

/*
function importVar($name, $cast_int = false, $default = false) {
	
	if(isset($_POST[$name])) 	return ( $cast_int ? (int)$_POST[$name] 	: $_POST[$name] );
	if(isset($_GET[$name])) 	return ( $cast_int ? (int)$_GET[$name] 		: $_GET[$name] );
	return $default;
}
*/

/*
function fromDatetimeToTimestamp($datetime) {

	$timestamp = '';
	if($datetime == '') return $timestamp;

	// mktime ( int hour, int minute, int second, int month, int day, int year [, int is_dst])
	// 0123-56-89 12-45-78

	if(strlen($datetime) < 11) {

		$timestamp = mktime(	0, 0, 0,
			substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4) );
	} else {

		$timestamp = mktime(	substr($datetime, 11, 2), substr($datetime, 14, 2), substr($datetime, 17, 2),
			substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4) );
	}
	return $timestamp;
}
*/
/**
 * @return array 	os=>operating system version, 
 * 					browser=>browser name,
 *               	main_lang=>the main language used by the user
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
 /*
function getBrowserInfo() {

	$known_os 		= array('linux', 'macos', 'sunos', 'bsd', 'qnx', 'solaris', 'irix', 'aix', 'unix', 'amiga', 'os/2', 'beos', 'windows');
	$known_browser 	= array('firefox', 'netscape', 'konqueror', 'epiphany', 'mozilla', 'safari', 'opera', 'mosaic', 'lynx', 'amaya', 'omniweb', 'msie');

	$res = array('os' => 'unknown', 'browser' => 'unknown', 'main_lang' => 'unknown', 'http_user_agent' => $_SERVER['HTTP_USER_AGENT']);
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	// ----------------- Finding OS... -----------------------
	$knowned_os = count($known_os);
	for($i = 0, $found = false; ($i < $knowned_os) && !$found; $i++) {
		
		$pos = strpos($agent, $known_os[$i]);
		if($pos !== false) {
			$res["os"] = $known_os[$i];
			$found = true;
		}
	}

	// ----------------- Finding Browser... -----------------------
	$required["firefox"] 	= array("gecko", "mozilla", "firefox");
	$required["netscape"]	= array("gecko", "mozilla", "netscape");
	$required["konqueror"] 	= array("gecko", "mozilla", "konqueror");
	$required["epiphany"] 	= array("gecko", "mozilla", "epiphany");
	$required["mozilla"] 	= array("gecko", "mozilla");

	$knowned_browser = count($known_browser);
	for($i = 0, $found = false; ($i < $knowned_browser) && !$found; $i++) {
	
		$browser = $known_browser[$i];
		if(!isset($required[$browser])) {
			$pos = strpos($agent, $browser);
			if($pos !== false) {
				$res["browser"] = $browser;
				$found = true;
			}
		} else {

			$meets_req = true;
			foreach($required[$browser] as $key=>$val) {
				if (strpos($agent, $val) === false)
					$meets_req = false;
			}
			if ($meets_req) {
				$res["browser"] = $browser;
				$found = true;
			}
		}
	}
	// ----------------- Finding Main language... -----------------------
	$al_arr = explode(",",  $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	if(isset($al_arr[0])) $bl_arr = explode(";", $al_arr[0]);
	if(isset($bl_arr[0]) && ($bl_arr[0] != "")) $res["main_lang"] = $bl_arr[0];
	
	return $res;
}

function getPathImage() {
	
	return $GLOBALS['where_template_relative'].'/images/';
}
*/
?>