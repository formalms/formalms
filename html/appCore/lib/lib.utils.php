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

require_once(_base_.'/lib/lib.donotdo.php');

/**
 *	@return array contains the html translation charset
 **/
/*
function &getTranslateTable() {
	$table = get_html_translation_table(HTML_ENTITIES);
	//unset this for html code posted by html text editor
	unset($table[' ']);
	unset($table['&']);
    unset($table['"']);
    unset($table['<']);
    unset($table['>']);

	return $table;
}
*/

/**
 * function translateChr
 *	Do html charset translation
 *
 *  @param $text the text that will be translated
 *  @param &$translate_tabel the array that contain the charset substitution rule usualy return by getTranslateTable()
 *  @param $reverse if is true flip the translate_table array
 *
 *	@return the translated text
 **/
/*
function translateChr( &$text, &$translate_table, $reverse = false ) {

	if(!is_array($translate_table)) return $text;
	if(!isset($GLOBALS['is_utf'])) $GLOBALS['is_utf'] = ( strpos(getUnicode(), 'utf-8') === false ? false : true );

	if($GLOBALS['is_utf'] === false) {
		if($reverse) $translate_table = array_flip($translate_table);
		return str_replace($translate_table, array_keys($translate_table), $text);
	} else {

		return $text;
	}
}
*/

/**
 * function chkInput
 *	Control the array for security (eg. SQL Iniection)
 *	(This funciton reset the array internal pointer)
 *
 * 	@param &$arrData array of key=>values to be checked
 *	@param $deeper if this parameter is set to TRUE (default) the check
 *			is done recursively in array's values
 *
 *	@return TRUE if the original arrData was good FALSE otherwise.
 *			In this case arrData is modified to be safe
 *
 *	@author Emanuele Sandri <esandri@tiscali.it>
 *          Modify for charset translation
 *			Fabio Pirovano <fabio@docebo.com>
 **/
require_once(_base_.'/lib/lib.donotdo.php');
require_once(_base_.'/addons/kses/kses.php');
define("CHK_MAX_DEEP", 10);

function chkInput(&$arrData, $deeper = TRUE, $deep_reached = 0) {
	$good = TRUE;

	if($deep_reached > CHK_MAX_DEEP) return;

	while(list($key, $val) = each($arrData)) {

		// check key ----------------------------------------------------------
		$new_key = $key;
		if(get_magic_quotes_gpc()) $new_key = stripslashes($new_key);
		if(!dontCleanHtml($key)) $new_key = kses($new_key);
		$new_key = mysql_escape_string($new_key);
		
		if( $new_key != $key ) {
			$arrData[$new_key] = $arrData[$key];
			unset($arrData[$key]);
			$key = $new_key;
			$good = FALSE;
		}

		// check value --------------------------------------------------------
		if( is_array($val) && $deeper ) {

			// if $val is array and deeper is TRUE we call chkInput recursively
			if(!chkInput($val, $deeper, $deep_reached++ )) {
				// if $val is changed reassign to containers array
				$arrData[$key] = $val;
				$good = FALSE;
			}
		} elseif(is_string($val)) {

			$new_val = $val;
			if(!dontReplaceBaseUrl($key)) $new_val = putSiteBaseUrlTag($new_val);
			if(get_magic_quotes_gpc()) $new_val = stripslashes($new_val);
			if(!dontCleanHtml($key)) $new_val = kses($new_val);
			$new_val = mysql_escape_string($new_val);
			
			if($new_val != $val) {
				$arrData[$key] = $new_val;
				$good = FALSE;
			}
		}
	} // end while ------------------------------------------------------------
	return $good;
}

/**
 *  return value of name_var from array _POST and _GET
 *
 * @param string 	$name_var 		name of the imported variable
 * @param boolean 	$cast_to_int 	if true the var value is casted to int
 * @param string 	$default_value 	the value returned if the var_name isn't isset
 * @param bool 		$strip 			if true, when the value is read from get or post stripslashes the value
 *
 * @return  value of the name_var passed from $_POST or $_GET if one
 * 			of them is set else return default_value NOT casted
 **/
/*
function importVar($name_var, $cast_to_int = false, $default_value = '', $strip = false) {
	
	return Get::req($name_var, ( $cast_to_int ? DOTY_INT : DOTY_MIXED ), $default_value);
	
	if( isset($_POST[$name_var]) ) {

		$value = ( $cast_to_int ? (int)$_POST[$name_var] : $_POST[$name_var] );
		return ( $strip ? stripslashes($value) : $value );
	} elseif( isset($_GET[$name_var]) ) {

		$value = ( $cast_to_int ? (int)$_GET[$name_var] : $_GET[$name_var] );
		return ( $strip ? stripslashes($value) : $value );
	} else {
		return $default_value;
	}
}
*/

/**
 * Emulate register_globals off
 */
function unregister_GLOBALS() {
    if (!ini_get('register_globals')) { return; }

    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
        die('GLOBALS overwrite attempt detected'); //GLOBALS overwrite attempt detected
    }

    // Variables that shouldn't be unset
    $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',     '_FILES',
                     '_SESSION');

    foreach ($GLOBALS as $k => $v) {
        if (is_numeric($k) || !in_array($k, $noUnset)) {
            unset($GLOBALS[$k]);
        }
    }
}

/**
 * redirect the user to a specified url
 *
 * @return string	 relative destination url (eg. index.php?...)
 *
 * @return nothing
 **/

function jump_to($relative_url, $anchor = false) {

	$relative_url = trim(str_replace('&amp;', '&', $relative_url));

	session_write_close();
		//Header('Location: http' . ( ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' )  ) ? 's' : '' ).'://'.$_SERVER['HTTP_HOST']
		Header('Location: http' . ( ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) 
		                          or (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') 
		                          or (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on') ) ? 's' : '' ).'://'
		     .( (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] )
    	.( strlen(dirname($_SERVER['SCRIPT_NAME'])) != 1 ? dirname($_SERVER['SCRIPT_NAME']) : '' )
		.'/'.$relative_url
		//.( strpos($relative_url, '?') === false ? '?' : '&' ).session_name().'='.session_id()
		.( $anchor !== false ? $anchor : '' ) );
	ob_clean();
	exit();
}

/**
 * @param string 	$text the text of the code that as created the error (usually a query)
 *
 * @return string with the a html comment with various information for debug porpouse
 */
function doDebug($text) {
	if(Get::sett('do_debug') == 'on') {}
}

function errorCommunication($text) {

	doDebug($text);
}

/**
 * @param string 	$text 	the result identifier it must be (err_'int' or ok_'int'),
 *							this function search for the specified _RESULT_'int' as constant to use for message diaply
 *
 * @return string with the html
 */
function guiResultStatus(&$lang, $text) {

	$numeric_code = substr($text, (strrpos($text, '_') + 1));

	if(strpos($text, 'ok') !== false) {
		if(!defined('_SUCCESS_'.$numeric_code)) return getResultUi('_SUCCESS_'.$numeric_code);
		else return getResultUi($lang->def(constant('_SUCCESS_'.$numeric_code)));
	} elseif(strpos($text, 'err') !== false) {
		if(!defined('_FAIL_'.$numeric_code)) return getErrorUi('_FAIL_'.$numeric_code);
		else return getErrorUi($lang->def(constant('_FAIL_'.$numeric_code)));
	}
}

function getAppendAlert($message_text, $message_type = false) {

	$class_name = "notice_display";
	switch($message_type) {
		case "notice" 	: { $class_name .= ' notice_display_notice'; };break;
		case "success" 	: { $class_name .= ' notice_display_success'; };break;
		case "failure" 	: { $class_name .= ' notice_display_failure'; };break;
		case "error" 	: { $class_name .= ' notice_display_error'; };break;
		default 		: { $class_name .= ' notice_display_default'; };break;
	}
	$html = '<div class="'.$class_name.'">'
		.'<p>'.$message_text
		.'<a class="close_link" href="javascript:void(0)" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);">'
		.'close</a>'
		.'</p>'
		.'</div>';
	return $html;
}

/**
 * return true if the current user agent is a known bot.
 * update [dd/mm/yyyy]: 17/02/2005
 *
 * @return int 1 if the user agent is a bot
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
function isBot() {

	$res = 0;
	$agent = strtolower($_SERVER["HTTP_USER_AGENT"]);

	if (!(strpos($agent, "googlebot") === false))		$res=1; // Google
	if (!(strpos($agent, "scooter") === false))			$res=1; // Altavista
	if (!(strpos($agent, "altavista") === false))		$res=1; // Altavista UK
	if (!(strpos($agent, "webcrawler") === false))		$res=1; // AllTheWeb
	if (!(strpos($agent, "architextspider") === false))	$res=1; // Excite
	if (!(strpos($agent, "slurp") === false))			$res=1; // Inktomi
	if (!(strpos($agent, "iltrovatore") === false))		$res=1; // Il Trovatore
	if (!(strpos($agent, "ultraseek") === false))		$res=1; // Infoseek
	if (!(strpos($agent, "lookbot") === false))			$res=1; // look.com
	if (!(strpos($agent, "mantraagent") === false))		$res=1; // looksmart.com
	if (!(strpos($agent, "lycos_spider") === false))	$res=1; // Lycos
	if (!(strpos($agent, "msnbot") === false))			$res=1; // Msn Search (the hated)
	if (!(strpos($agent, "shinyseek") === false))		$res=1; // ShinySeek
	if (!(strpos($agent, "robozilla") === false))		$res=1; // dmoz.org

	return $res;
}


/**
 * @return array os=>operating system version, browser=>browser name,
 *               main_lang=>the main language used by the user
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
function getBrowserInfo() {

	$res=array();
	$res["os"]="unknown";
	$res["browser"]="unknown";
	$res["main_lang"]="unknown";
	$res['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];


	$agent=strtolower($_SERVER['HTTP_USER_AGENT']);

	$known_os="linux macos sunos bsd qnx solaris irix aix unix amiga os/2 beos windows";
	$known_browser="firefox netscape konqueror epiphany mozilla safari opera mosaic lynx amaya omniweb msie";

	$known_os_arr=explode(" ", $known_os);
	$known_browser_arr=explode(" ", $known_browser);

	// ----------------- Finding OS... -----------------------
	$i=0;
	$found=false;
	while(($i<count($known_os_arr)) && (!$found)) {

		$pos=strpos($agent, $known_os_arr[$i]);
		if ($pos !== false) {
			$res["os"]=$known_os_arr[$i];
			$found=true;
		}

		$i++;
	}

	// ----------------- Finding Browser... -----------------------
	$required["firefox"]=array("gecko", "mozilla", "firefox");
	$required["netscape"]=array("gecko", "mozilla", "netscape");
	$required["konqueror"]=array("gecko", "mozilla", "konqueror");
	$required["epiphany"]=array("gecko", "mozilla", "epiphany");
	$required["mozilla"]=array("gecko", "mozilla");

	$i=0;
	$found=false;
	while(($i<count($known_browser_arr)) && (!$found)) {

		$browser=$known_browser_arr[$i];

		if (!isset($required[$browser])) {
			$pos=strpos($agent, $browser);
			if ($pos !== false) {
				$res["browser"]=$browser;
				$found=true;
			}
		}
		else {

			$meets_req=true;
			foreach($required[$browser] as $key=>$val) {
				if (strpos($agent, $val) === false)
					$meets_req=false;
			}

			if ($meets_req) {
				$res["browser"]=$browser;
				$found=true;
			}

		}

		$i++;
	}


	// ----------------- Finding Main language... -----------------------
	$accept_language=$_SERVER["HTTP_ACCEPT_LANGUAGE"];
	$al_arr=explode(",", $accept_language);
	if (isset($al_arr[0]))
		$bl_arr=explode(";", $al_arr[0]);

	if ((isset($bl_arr[0])) && ($bl_arr[0] != "")) {
		$res["main_lang"]=
			$browser_language = mysql_escape_string($bl_arr[0]);
	}


	// -----
	return $res;
}


/**
 * cleanUrlPath
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 *
 * @param string $path the path/url to clean
 * @return string the cleaned path.
 * 	example input: http://127.0.0.1:88/folder/folder//appCore/mod_media/../../../test/ok/
 * 	example output: http://127.0.0.1:88/folder/test/ok/
 */
function cleanUrlPath($path) {

	$path = str_replace("/./", "/", $path);
	$found = array();
	$regexp = "^http[^\\:]?:\\/\\/(.*?)\\/";
	if (preg_match("/".$regexp."/si", $path, $found)) {
		$path = preg_replace("/".$regexp."/si", "", $path);
	}
	$path = str_replace("//", "/", $path);

	$path_arr = array_reverse(explode("/", $path));

	$skip = 0;
	$clean_arr = array();
	foreach($path_arr as $cf) {
		if ($cf  ==  "..")
			$skip++;
		else {
			if ($skip > 0)
				$skip--;
			else
				$clean_arr[] = $cf;
		}
	}
	$res = implode("/", array_reverse($clean_arr));
	if (isset($found[0]))
		$res = $found[0].$res;

	return $res;
}

/**
 * unhtmlentities
 * This function convert all html entities code on rispective char
 * (from php manual)
 * @author Emanuele Sandri <esandri[AT]tiscali-it>
 *
 * @param string $string the text to be converted
 * @return string the converted string
 */
function unhtmlentities($string)
{
   // replace numeric entities
   $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
   $string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
   // replace literal entities
   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
   $trans_tbl = array_flip($trans_tbl);
   return strtr($string, $trans_tbl);
}

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


function createDateDistance( $date, $m_name = false, $on_over_return_date = false )
{
	$year 	= substr($date, 0, 4);
	$month 	= substr($date, 5, 2);
	$day	= substr($date, 8, 2);

	$hour 	= substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$second	= substr($date, 17 , 2);

	$distance = time() - mktime($hour, $minute, $second, $month, $day, $year);
	//second -> minutes
	$distance = (int)($distance / 60);
	// < 1 hour print minutes
	if( ($distance >= 0 ) && ($distance < 60) ) return $distance.' '. Lang::t('_MINUTES', $m_name);

	//minutes -> hour
	$distance = (int)($distance / 60);
	if( ($distance >= 0 ) && ($distance < 48) ) return $distance.' '. Lang::t('_HOURS', $m_name);

	//hour -> day
	$distance = (int)($distance / 24);
	if( ($distance >= 0 ) && ($distance < 30 ) ) return $distance.' '. Lang::t('_DAYS', $m_name);

	//echo > 1 month
	if($on_over_return_date) return Format::date($date, 'date');
	return Lang::t('_ONEMONTH', $m_name);
}
*/



/**
 * @param string $ip_addr	the i to check
 *
 * @return bool	 true if the ip passed
 */
function validIp($ip_addr) {

	return (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip_addr) > 0);
}

/**
 * return  diagnostic about the user ip and a set of rules
 * @param string 	$user_ip 	the ip to check
 * @param string 	$all_rules	the set of rules
 *
 * @return bool true if the ip is allowed, false otherwise
 **/
function internalFirewall($user_ip = false, $all_rules = false) {

	// set the ip of the logged user if false
	if($user_ip === false) $user_ip = $_SERVER['REMOTE_ADDR'];

	// recover the rules
	if($all_rules === false) {

		$all_rules = Get::sett('session_ip_filter');
	}
	// no rules, free access
	if($all_rules == '') return true;

	// convert dotted ip into long
	$int_ip = sprintf('%u', ip2long($user_ip));

	// scan rules
	$allow = false;
	$deny = false;
	$deny_general = false;
	$allow_general = false;

	// explode rules in rows
	$array_of_rules = preg_split("/[\n\r]+/", $all_rules);
	$is_first = true;

	while(list(, $rule) = each($array_of_rules)) {

		// explode rule in command
		$command = preg_split('/ /', trim($rule), -1, PREG_SPLIT_NO_EMPTY);
		if(is_array($command)) {

			// command scomposed successfully
			$special = $command[0].' '.$command[1];
			// special command
			if($special == 'deny all' || $special == 'allow none') $deny_general = true;
			elseif($special == 'deny none' || $special == 'allow all') $allow_general = true;
			else {

				if($is_first == true) {
					if($command[0] == 'deny') $allow_general = true;
					if($command[0] == 'allow') $deny_general = true;
				}
				if(strpos($command[1], '*') !== false) {
					// is a ip with some *
					$start_addr 	= str_replace('*', '0', $command[1]);
					$end_addr 		= str_replace('*', '255', $command[1]);
				} elseif(isset($command[2])) {
					// is range ?
					$start_addr 	= $command[1];
					$end_addr 		= $command[2];
				} else {
					// a single ip
					$start_addr 	= $command[1];
					$end_addr 		= $command[1];
				}
				if(validIp($start_addr) && validIp($end_addr)) {

					$start_addr = sprintf('%u', ip2long($start_addr));
					$end_addr = sprintf('%u', ip2long($end_addr));

					switch($command[0]) {
						case "deny" : {
							if($start_addr <= $int_ip && $int_ip <= $end_addr) $deny = true;
						};break;
						case "allow" : {
							if($start_addr <= $int_ip && $int_ip <= $end_addr) $allow = true;
						};break;
					}
				}
			}
			$is_first = false;
		}
	}

	/* map of accepted cases
	 *	allow_general 	\ allow
	 *	deny_general 	 \ deny
	 *						 00  01  11  10
	 *						|---|---|---|---|
	 *					00	| 1 |   |   | 1 |
	 *						|---+---+---+---|
	 *					01	|   |   |   | 1 |
	 *						|---+---+---+---|
	 *					11	| 1 |   |   | 1 |
	 *						|---+---+---+---|
	 *					10	| 1 |   |   | 1 |
	 *						|---|---|---|---|
	 */
	if(($allow && !$deny) || (!$deny && !$deny_general) || ($allow_general && !$deny))return true;
	return false;
}


/**
 * @param string $table the name of the table
 * @param string $ord_field the name of field that contain the order value of the item
 * @param string $where (optional) additional conditions for the where statment
 *
 * @return int highest value of the ord_field column
 */
function utilGetLastOrd($table, $ord_field, $where=FALSE) {

	$qtxt ="SELECT ".$ord_field." FROM ".$table." ";
	if ($where !== FALSE)
		$qtxt.="WHERE ".$where." ";
	$qtxt.="ORDER BY ".$ord_field." DESC";
	$q=sql_query($qtxt);

	$res=0;

	if (($q) && (mysql_num_rows($q) > 0)) {
		$row=mysql_fetch_array($q);
		$res=$row[$ord_field];
	}

	return $res;
}


/**
 * @param string $direction 	in wich direction the item has to be moved (up | down)
 * @param string $table 		the name of the table
 * @param string $id_name 		the name of the table index
 * @param string $id_val 		the value of the table index corresponding to the item to move
 * @param string $ord_field 	the name of field that contain the order value of the item
 * @param string $where 		additional conditions for the where statment
 *
 * @return bool true if success; false if fails.
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
function utilMoveItem($direction, $table, $id_name, $id_val, $ord_field, $where=FALSE) {

	// Let's find current ord value
	$qtxt ="SELECT ".$ord_field." FROM ".$table." ";
	$qtxt.="WHERE ".$id_name."='".$id_val."' ";
	if ($where !== FALSE)
		$qtxt.="AND ".$where." ";
	$qtxt.="LIMIT 0,1";
	$q=sql_query($qtxt);

	if (($q) && (mysql_num_rows($q) > 0)) {
		$row=mysql_fetch_array($q);
		$current_ord=$row[$ord_field];
	}
	else
		return FALSE;


	// Let's find the new ord value
	switch ($direction) {
		case "up": {
			$new_ord=$current_ord-1;
			$look_for="<";
			$order_dir="DESC";
			if ($new_ord < 0)
				$new_ord=0;
		} break;
		case "down": {
			$new_ord=$current_ord+1;
			$look_for=">";
			$order_dir="ASC";
		} break;
	}

	$do_switch=TRUE;


	// Let's find the item to switch with
	$qtxt ="SELECT ".$id_name.",".$ord_field." FROM ".$table." ";
	$qtxt.="WHERE ".$ord_field.$look_for."'".$current_ord."' ";
	if ($where !== FALSE)
		$qtxt.="AND ".$where." ";
	$qtxt.="ORDER BY ".$ord_field." ".$order_dir." LIMIT 0,1";
	$q=sql_query($qtxt);

	if (($q) && (mysql_num_rows($q) > 0)) {
		$row=mysql_fetch_array($q);
		$switch_with=$row[$id_name];
	}
	else {
		$do_switch=FALSE;
	}

	$qtxt="UPDATE ".$table." SET ".$ord_field."='".$new_ord."' WHERE ".$id_name."='".$id_val."'";
	$q=sql_query($qtxt);

	if ($do_switch) {
		$qtxt="UPDATE ".$table." SET ".$ord_field."='".$current_ord."' WHERE ".$id_name."='".$switch_with."'";
		$q=sql_query($qtxt);

	}

}


/**
 * @param int $percent the current value of the progress (0-100)
 * @param bool $show_percent if TRUE will show the percent amount as default text
 * @param mixed $bar_class if not FALSE will use a custom css class for the bar
 * @param mixed $fill_class if not FALSE will use a custom css class for bar's fill
 * @param mixed $txt_class  if not FALSE will use a custom css class for the text
 * @param mixed $text 	if not false the text will be used instead of the percentual value
 *
 * @return string html code of the progress bar
 */
function drawProgressBar($percent, $show_percent=TRUE, $bar_class=FALSE, $fill_class=FALSE,
                         $txt_class=FALSE, $text=FALSE) {
	$res="";

	if ($bar_class === FALSE)
		$bar_class="progress_bar";

	if ($fill_class === FALSE)
		$fill_class="bar_fill";

	if ($txt_class === FALSE)
		$txt_class="bar_text";


	$res.="<div class=\"".$bar_class."\">\n";

	$res.="<div class=\"".$txt_class."\">\n";

	if ($text !== FALSE)
		$res.=$text;
	else if ($show_percent)
		$res.=$percent."%";

	$res.="</div>"; // $txt_class

	if ($percent > 0) {
		$res.="<div class=\"".$fill_class."\" style=\"width:".$percent."%;\">&nbsp;\n";
		$res.="</div>"; // $fill_class
	}

	$res.="</div>"; // $bar_class

	return $res;
}

function getArrayGap($from, $to, $convert = false) {

	// yyyy-mm-dd hh:mm:ss
	// 0123456789012345678
	if($convert !== false) {
		$from 	= fromDatetimeToTimestamp($from);
		$to 	= fromDatetimeToTimestamp($to);
	}

	$distance = abs($from-$to);

	$gap =
	//Second


	$distance = (int)($distance / 60);
	//< 1 hour print minutes
	if( ($distance >= 0 ) && ($distance < 60) ) return $distance.' '. Lang::t('_MINUTES');

	//minutes -> hour
	$distance = (int)($distance / 60);
	if( ($distance >= 0 ) && ($distance < 60) ) return $distance.' '. Lang::t('_HOURS');

	//hour -> day
	$distance = (int)($distance / 24);
	if( ($distance >= 0 ) && ($distance < 30 ) ) return $distance.' '. Lang::t('_DAYS');

	//echo > 1 month
	return Lang::t('_ONEMONTH');
}


/**
 * @param string $txt  the string we want to remove accents from
 * @return string the original text with all the accents removed
 *
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
/*
function removeAccents($txt) {
	$res =$txt;

	$res =preg_replace("/[\\xC0..\\xC5]/u", "A", $res);
	$res =preg_replace("/[\\xE7]/u", "c", $res); // ç
	$res =preg_replace("/[\\xC8..\\xCB]/u", "E", $res);
	$res =preg_replace("/[\\xCC..\\xCF]/u", "I", $res);
	$res =preg_replace("/[\\xD1\\xF1]/u", "n", $res); //ñ
	$res =preg_replace("/[\\xD2..\\xD6]/u", "O", $res);
	$res =preg_replace("/[\\xD9..\\xDC]/u", "U", $res);
	$res =preg_replace("/[\\xE0..\\xE5]/u", "a", $res);
	$res =preg_replace("/[\\xE8..\\xEB]/u", "e", $res);
	$res =preg_replace("/[\\xEC..\\xEF]/u", "i", $res);
	$res =preg_replace("/[\\xF2..\\xF6]/u", "o", $res);
	$res =preg_replace("/[\\xF9..\\xFC]/u", "u", $res);

	return $res;
}
*/



/**
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 * @param string $str our original string
 * @param string $len the lenght of the resulting string
 *
 * @return string example: leadingZero("2", 3) => "002"
 */
/*
function leadingZero($str, $len) {

	$zero_to_add=$len-strlen($str);
	if ($zero_to_add > 0)
		$res=str_repeat("0", $zero_to_add).$str;
	else
		$res=$str;

	return $res;
}
*/

/*
function getPLSetting($platform, $param_name, $default=FALSE) {
	$res=$default;

	require_once(_base_.'/lib/lib.platform.php');
	$pl_man =& PlatformManager::CreateInstance();

	if ($pl_man->isLoaded($platform)) {


		$res=$GLOBALS[$platform][$param_name];
	}

	return $res;
}


function addCss($name, $platform=FALSE, $folder=FALSE, $add_start=FALSE) {

	if(!isset($GLOBALS["page"])) return;
	if ($platform === FALSE) {
		$platform = Get::cur_plat();
	}

	$clean_name=getCleanTitle($name);
	$clean_folder=($folder !== FALSE ? "_".getCleanTitle($folder) : "");
	$css_id=$platform.$clean_folder."_".$clean_name;

	if (!isset($GLOBALS["_css_cache"])) {
		$GLOBALS["_css_cache"]=array();
	}

	if (!in_array($css_id, $GLOBALS["_css_cache"])) {
		$GLOBALS["_css_cache"][]=$css_id;

		$css=getPathTemplate($platform)."style".($folder !== FALSE ? $folder : "")."/".$name.".css";

		$code="<link href=\"".$css."\" rel=\"stylesheet\" type=\"text/css\" />\n";

		if(isset($GLOBALS["page"])) {
			if (!$add_start)
				$GLOBALS["page"]->add($code, "page_head");
			else
				$GLOBALS["page"]->addStart($code, "page_head");
		}
	}
}
*/

/**
 * Add a js file in the page head
 * @param string $path the relative path to the js file
 * @param string $name the name of the file with the extension .js included i.e. "functions.js"
 */
/*
function addJs($path, $name) {

	if(!isset($GLOBALS["page"])) return;
	if(!isset($GLOBALS["_js_cache"])) $GLOBALS["_js_cache"] = array();
	if(!in_array($path.$name, $GLOBALS["_js_cache"])) {

		$GLOBALS["_js_cache"][] = $path.$name;
		$GLOBALS["page"]->add('<script type="text/javascript" src="'.$path.$name.'"></script>'."\n", "page_head");
	}
}
*/



/**
 * Add the standard js used for ajax working
 */
function addAjaxJs() {
	return;

	if(!isset($GLOBALS["page"])) return;
	if(!isset($GLOBALS["_js_cache"])) $GLOBALS["_js_cache"] = array();
	if(!in_array('_ajax_std_js', $GLOBALS["_js_cache"])) {

		$GLOBALS["_js_cache"][] = '_ajax_std_js';
		$GLOBALS["page"]->add(
			 '<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/lib/prototype.js"></script>'."\n"
			.'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/lib/events_onload.js"></script>'."\n"
			//.'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/json/json.js"></script>'."\n"
		, "page_head");
	}
}

function addScriptaculousJs() {
	return;
	
	if(!isset($GLOBALS["page"])) return;
	addCss('windows', 'framework');
	addAjaxJs();
	if(!isset($GLOBALS["_js_cache"])) $GLOBALS["_js_cache"] = array();
	if(!in_array('_scriptacolus_js', $GLOBALS["_js_cache"])) {

		$GLOBALS["_js_cache"][] = '_scriptacolus_js';
		$GLOBALS["page"]->add(
			 '<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/src/scriptaculous.js"></script>'."\n"
			 .'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/lib/prototype.improvements.js "></script>'."\n"
			 .'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/src/scriptaculous.js"></script>'."\n"

			 .'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/src/windows.js"></script>'."\n"
			.'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/src/HelpBalloon.js"></script>'."\n"
			 .'<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/addons/scriptaculous/src/notice.js"></script>'."\n"

			.'<script type="text/javascript">'
		    .'	setImgBalloonPath(\''.getPathImage('fw').'balloon/images/\');'
		    .'</script>'
		, "page_head");

	}
}

/**
 * If needed insert Yahoo base and additional script and css.
 * 
 * @param array $js An array with additionl js file to include. Array form : array('folder' => 'file_name')
 * @param array $css An array with additionl css file to include. Array form : array('folder' => 'file_name')
 */
/*
function YuiLib::load($js = false, $css = false)
{
	if(!isset($GLOBALS["page"]))
		return;
	
	if(!isset($GLOBALS["_js_cache"]))
		$GLOBALS["_js_cache"] = array();
	
	if(!in_array('_yahoo_js', $GLOBALS["_js_cache"])) {
		$GLOBALS["_js_cache"][] = '_yahoo_js';
		
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/yahoo/yahoo-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/event/event-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/dom/dom-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/connection/connection-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/element/element-beta-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/yahoo-dom-event/yahoo-dom-event-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/dragdrop/dragdrop-min.js';
		$GLOBALS["_js_cache"][] = $GLOBALS['where_framework_relative'].'/addons/yui/animation/animation-min.js';
		
		addJs($GLOBALS['where_framework_relative'].'/addons/yui/utilities/', 'utilities.js');
		addJs($GLOBALS['where_framework_relative'].'/addons/yui/animation/', 'my_animation.js');
		addJs($GLOBALS['where_framework_relative'].'/addons/yui/json/', 'json-min.js');
	
		$GLOBALS["page"]->addEnd(
			'<link href="'.getPathTemplate('lms').'style/style_yui_docebo.css" rel="stylesheet" type="text/css" />'."\n"
		, "page_head");
	}
	
	if($js && is_array($js)) {
		
		
		foreach($js as $folder => $file) {
			if(is_array($file)) addJs($GLOBALS['where_framework_relative'].'/addons/yui/'.$file[0].'/', $file[1]);
			else addJs($GLOBALS['where_framework_relative'].'/addons/yui/'.$folder.'/', $file);
			
		}
	}
	if(!isset($GLOBALS["_css_cache"])) $GLOBALS["_css_cache"]=array();
	if($css && is_array($css)) {
		foreach($css as $folder => $file) {
			
			if(!isset($GLOBALS["_css_cache"][$folder.'|'.$file])) {
				$GLOBALS["page"]->add(
					'<link rel="stylesheet" type="text/css" href="'.$GLOBALS['where_framework_relative'].'/addons/yui/'.$folder.'/'.$file.'" />'."\n"
					, "page_head");
				$GLOBALS["_css_cache"][$folder.'|'.$file] = 1;
			}
		}
	}
}

*/

/**
 * return the size in bytes of the specified file
 * @param string $file_path the target file
 *
 * @return int the size of the file in bytes
 */
function getFileSize($file_path) {

	return filesize($file_path);
}

/**
 * return the size in bytes of the specified directory
 * @param string $path the target dir
 *
 * @return int the size of the dir in bytes
 */
function getDirSize($path) {

	if(!is_dir($path)) return Get::file_size($path);
	if($scan_dir = opendir($path)) {

		$size = 0;
		while($file = readdir($scan_dir)) {

			if($file != '..' && $file !='.' && $file != '') {

            	$size += self::dir_size($path.'/'.$file);
			}
		}
	}
	closedir($scan_dir);
	return $size;
}

function fileExceedQuota($file_path, $quota, $used, $manual_file_size = false) {

	if($quota == 0) return false;
	$quota = $quota * 1024 *1024;
	if($manual_file_size === false) $filesize = getDirSize($file_path);
	else $filesize = $manual_file_size;

	return ( ($used + $filesize) > $quota);
}

/**
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 * @param mixed $original string or array of items to be quoted
 * @param string $quote_char the char used as quote
 *
 * @return mixed quoted string or array items
 */
function addSurroundingQuotes($original, $quote_char="'") {
	$res="";

	if (is_array($original)) {
		$func_add_quote=create_function('&$val, $key, $qc', '$val=$qc.addcslashes(trim($val, $qc), $qc).$qc;');
		array_walk($original, $func_add_quote, $quote_char);

		$res=$original;
	}
	else {
		$res=$quote_char.addcslashes(trim($original, $quote_char), $quote_char).$quote_char;
	}

	return $res;
}


/**
 * Returns the site base url of a website that means the full website
 * url without the platform-specific folder
 * example: browsing "www.mysite.com/test/appCore" you'll get
 * "www.mysite.com/test"
 */
/*
function getSiteBaseUrl() {
	$current_pl = Get::cur_plat();

	$url = substr(getPLSetting($current_pl, "url"), 0, -1);

	$search 	= str_replace("\\", '/', $GLOBALS["where_".$current_pl]);
	$cut_from 	= strrpos($search, "/");
	$search 	= substr($search, $cut_from);
	$search 	= str_replace('/', '\/', $search);
	$base_url 	= preg_replace('/'.$search.'/i', "", $url);

	return $base_url;
}
*/

/**
 * Replaces any {site_base_url} tag with the
 * base url of the website. (see the getSiteBaseUrl function)
 */
/*
function fillSiteBaseUrlTag($text) {

	$res =str_replace("{site_base_url}", getSiteBaseUrl(), $text);

	return $res;
}
*/

/**
 * Replaces any site base url (see the getSiteBaseUrl function)
 * with the {site_base_url} tag.
 * example: www.mysite.com/test/appCore -> {site_base_url}/appCore
 */
/*
function putSiteBaseUrlTag($text) {

	$base_url =getSiteBaseUrl();

	$text = str_replace($base_url, "{site_base_url}", $text);

	return $text;
}
*/


function sendMail($recipient, $subject, $body, $from='', $replyTo='', $params=false) {
	require_once($GLOBALS['where_framework_relative'].'/addons/phpmailer/class.phpmailer.php');
	
	$acl_man = new DoceboACLManager();
	$mail = new PHPMailer();
	
	$mail->IsMail();
  
  if ($from!='') {
  	$mail->From = $from;
  	$temp = $acl_man->getUserByEmail($from);
  	$mail->FromName = $temp[ACL_INFO_FIRSTNAME].' '.$temp[ACL_INFO_LASTNAME];
	}
	
	if (is_string($recipient)) {
		$temp = $acl_man->getUserByEmail($recipient);
		$mail->AddAddress($recipient, $temp[ACL_INFO_FIRSTNAME].' '.$temp[ACL_INFO_LASTNAME]);
	} elseif (is_array($recipient)) {
		// TO DO: multiple sending ...
		return false; //not supported at the moment
	} else
		return false;
		
  if  ($replyTo!='') {
  	$temp = $acl_man->getUserByEmail($replyTo);
		$mail->AddReplyTo($replyTo, $temp[ACL_INFO_FIRSTNAME].' '.$temp[ACL_INFO_LASTNAME]);
  }
  
	//$mail->WordWrap = 50;
  $mail->IsHTML(true);
  $mail->Subject = $subject;
  $mail->Body    = $body;
  $mail->AltBody = html_entity_decode(strip_tags($body),  ENT_COMPAT, 'UTF-8');
      	
  return $mail->Send();
}

?>
