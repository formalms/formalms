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
 * Utils file, basic function for the nowadays use
 *
 * In this file you can find some usefull functions fot the html generation
 * and for recovering some common ifnormation about the user, the script and
 * so on.
 * @author Fabio Pirovano <gishell@tiscali.it>
 * @version 1.0
 * @package main
 */

/**
 * docebo constant for signature
 */
define("MAX_SIGN_GAP",		3600*8);

class Util  {

	protected static $_js_loaded = array();

	public static function purge($html) {

		return addslashes(strip_tags($html));
	}


	public static function cut($html, $max_char = 100) {

		$html = strip_tags($html);
		if(strlen($html) > $max_char) $html = substr ($html, 0, $max_char).' ...';
		return $html;
	}

	/**
	 * Highlight parts of text strings with HTML tags
	 *  @param $string the text that will be checked for parts to highlight
	 *  @param $key the text to be highlighted
	 *  @param $classname class of the highlight <span> tag, "highlight" by default
	 *
	 *	@return the highlighted text
	 **/
	public static function highlight($string, $key, $classname = "highlight") {
		if ($key=="") return $string;
		return preg_replace("/".$key."/i", "<span class=\"highlight\">$0</span>", $string);
	}

	public static function get_css($css, $is_abspath = false, $print = false) {

		if(!$is_abspath) {
			$css = Get::tmpl_path('base').'style/'.$css;
		}

		if($print && function_exists('cout')) cout("\n\t\t".'<link rel="stylesheet" type="text/css" href="'.$css.'" />', 'page_head');
		else return "\n\t\t".'<link rel="stylesheet" type="text/css" href="'.$css.'" />';
	}

	public static function get_js($js, $is_abspath = false, $print = false) {

		if(!$is_abspath) $js = Get::rel_path('base').$js;
		if(isset(self::$_js_loaded[$js])) return '';
		else self::$_js_loaded[$js] = 1;
		if($print && function_exists('cout')) cout('<script type="text/javascript" src="'.$js.'"></script>', 'page_head');
		else return "\n\t\t".'<script type="text/javascript" src="'.$js.'"></script> ';
	}

	public static function jump_to($relative_url, $anchor = false) {

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
	 * able the user to download a specified file as an attachment
	 * @param string	$path		where the files is on the server filesystem without the filename
	 * @param string	$filename	the name of the file
	 * @param string	$ext		the extension of the file (.txt, .jpg ...)
	 * @param string	$sendname	the name given to the downlodable file, if not passed it will be constructed in this way:
	 *								assumed that $filename is [number]_[number]_[time]_[filename]
	 *								the file sended will have the name [filename].$ext
	 */
	function download($path, $filename, $ext = NULL, $sendname = NULL) {

		//empty and close buffer
		if(!($GLOBALS['where_files_relative'] == substr($path, 0, strlen($GLOBALS['where_files_relative'])))) {
			$path = $GLOBALS['where_files_relative'].$path;
		}
		if($sendname === NULL) {
			$sendname = implode('_', array_slice(explode('_', $filename), 3));
			if($sendname == '') $sendname = $filename;
		}

		if($ext === NULL || $ext === false) {
			$ext = array_pop(explode('.', $filename));

		}
		if(substr($sendname, - strlen($ext)) != $ext) $sendname .= '.'.$ext;

		if(!file_exists($path.$filename)) {

			Util::fatal('Error: the file that you are searching for no longer exists on the server.<br/>Please contact the system administrator');
		}

		$db = DbConn::getInstance();
		$db->close();

		ob_end_clean();
		session_write_close();
		//ini_set("output_buffering", 0);
		//Download file
		//send file length info
		header('Content-Length:'. filesize($path.$filename));
		//content type forcing dowlad
		header("Content-type: application/download; charset=utf-8\n");
		//cache control
		header("Cache-control: private");
		//sending creation time
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		//content type
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			header('Pragma: private');
		}
		header('Content-Disposition: attachment; filename="'.$sendname.'"');
		//sending file
		$file = fopen($path.$filename, "rb");
		$i=0;
		if(!$file) return false;
		while(!feof($file)) {
			$buffer = fread($file, 4096);
			echo $buffer;
			if ($i % 100 == 0) {
				$i=0;
				@ob_end_flush();
			}
			$i++;
		}
		fclose($file);

		//and now exit
		exit();
	}

	/**
	 * Return if the page was requested in POST by the client
	 * @return <bool>
	 */
	public static function requestIsPost() {

		return (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST');
	}

	public static function generateSignature($addendum = false) {

		if($addendum == false) $addendum = time();
		if(!isset($_SESSION['mdsign'])) {
			$_SESSION['mdsign'] = md5(uniqid(rand(), true) ."|". mt_rand() ."|". $addendum);
			$_SESSION['mdsign_timestamp'] = time();
		}
	}

	/**
	 * Get the signature saved in session or generate a new signature if needed
	 * @return string
	 */
	public static function getSignature($for_link = false) {

		if(!isset($_SESSION['mdsign'])) Util::generateSignature();
		if($for_link) return 'authentic_request='.$_SESSION['mdsign'];
		return $_SESSION['mdsign'];
	}

	/**
	 * Check if the signature attached to the page request is valid, user in order to detect foreigns requests
	 */
	public static function checkSignature() {

		//signature from a post or get
		$authentic_request = Get::req('authentic_request', DOTY_STRING, '');
		// signature from a ajax request
		if(!$authentic_request && isset($_SERVER['HTTP_X_SIGNATURE'])) $authentic_request = $_SERVER['HTTP_X_SIGNATURE'];

		if(!isset($_SESSION['mdsign'])
			|| $authentic_request != $_SESSION['mdsign']
		) {
			// Invalid request
			if (!defined('IS_AJAX')) {
				Util::jump_to(Get::rel_path('lms').'/index.php?modname=login&op=logout&msg=101');
			}
			Util::fatal('Security issue, the request seem invalid ! Try a new login and retry.');
		}
	}

	/**
	 * Display a fatal app message
	 * @param <string> $msg  the errore message
	 */
	public static function fatal($msg) {
		// empty output buffer
		@ob_end_clean();

		if(defined("IS_AJAX")) {
			// ajax request, json response
			$value = array('fatal' => $msg);
			require_once(_base_.'/lib/lib.json.php');
			$json = new Services_JSON();
			$msg = $json->encode($value);
		} else {
			// Browser request, html response
			$msg = '<p style="">'
				.'<p style="margin:4em auto;text-align:center;width:50%;padding: 14px 12px 14px 42px;font-weight: bold;font-size: 100%;background: url('.Get::tmpl_path().'/images/standard/error_32.png) no-repeat 8px 50% #FFFFDD;border:4px solid red;">'
				.$msg
				.'</p>'
				.'</p>';
		}
		die($msg);
	}

	public static function load_setting($from_table, $into_globals) {

		if(isset($GLOBALS[$into_globals])) return;

		$db =& DbConn::getInstance();

		$re_sett = $db->query("SELECT param_name, param_value, value_type ".
		"FROM ".$from_table." ".
		"WHERE param_load = '1'");

		while(list($var_name, $var_value, $value_type) = $db->fetch_row($re_sett)) {

			switch( $value_type ) {
				//if is int cast it
				case "int" : {
					$GLOBALS[$into_globals][$var_name] = (int)$var_value;
				};break;
				//if is enum switch value to on or off
				case "enum" : {
					if( $var_value == 'on' ) $GLOBALS[$into_globals][$var_name] = 'on';
					else $GLOBALS[$into_globals][$var_name] = 'off';
				};break;
				//else simple assignament
				default : {
					$GLOBALS[$into_globals][$var_name] = $var_value;
				}
			} // end switch

		} // end while

    }

    public static function draw_progress_bar($percent, $show_percent=TRUE, $bar_class=FALSE, $fill_class=FALSE, $txt_class=FALSE, $text=FALSE) {
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

		if ($percent > 0)
		{
			$res.="<div class=\"".$fill_class."\" style=\"width:".$percent."%;\">&nbsp;\n";
			$res.="</div>"; // $fill_class
		}

		$res.="</div>"; // $bar_class

		return $res;
	}

    public static function exceed_quota($file_path, $quota, $used, $manual_file_size = false) {
        if($quota == 0)
            return false;

        $quota = $quota * 1024 *1024;

        if($manual_file_size === false)
            $filesize = Get::dir_size($file_path);
        else
            $filesize = $manual_file_size;

        return (($used + $filesize) > $quota);
    }

	/**
	 * Returns the escaped string, only if magic quotes are off.
	 * @param string $str
	 * @return string Returns the escaped string.
	 */
	public static function add_slashes($str) {

		if (!get_magic_quotes_gpc()) {
			$res = addslashes($str);
		}
		else {
			$res = $str;
		}

		return $res;
	}

	/**
	 * Returns a string with backslashes stripped off, only if
	 * magic quotes are on.
	 * @param string $str
	 * @return string Returns a string with backslashes stripped off.
	 */
	public static function strip_slashes($str) {

		if (!get_magic_quotes_gpc()) {
			$res = $str;
		}
		else {
			$res = stripslashes($str);
		}

		return $res;
	}


	public function str_replace_once($search, $replace, $subject, &$count=false) {
		if (strpos($subject, $replace) === false) {
			if ($count === false) {
				return str_replace($search, $replace, $subject);
			}
			else {
				return str_replace($search, $replace, $subject, $count);
			}
		}
		else {
			return $subject;
		}
	}

	/**
	 * unhtmlentities
	 * This function convert all html entities code on rispective char
	 * (from php manual)
	 *
	 * @param string $string the text to be converted
	 * @return string the converted string
	 */
	function unhtmlentities($string) {
	   // replace numeric entities
	   $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	   $string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
	   // replace literal entities
	   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
	   $trans_tbl = array_flip($trans_tbl);
	   return strtr($string, $trans_tbl);
	}

	public function widget($widget_name, $params = null, $return=false) {

		ob_start();
		$widget =new Widget();
		$widget->widget($widget_name, $params);
		$my_widget=ob_get_contents();
		ob_clean();

		if (!$return)
			cout($my_widget);
		else
			return $my_widget;
	}

	public function array_validate(&$arr, $type) {
		if (!is_array($arr)) return false;
		for ($i=0; $i<count($arr); $i++) {
			switch ($type) {
				case DOTY_INT: $arr[$i] = (int)$arr[$i]; break;
				case DOTY_STRING: $arr[$i] = (string)$arr[$i]; break;
			}
		}
		return true;
	}


	public function getIsAjaxRequest() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}


	public function objectToArray($object) { return is_object($object) ? get_object_vars($object) : false; }
	public function arrayToObject($array) { return is_array($array) ? (object)$array : false; }
}

class UIFeedback {

	static public function info($message_text, $return = false) {
		$result = '<a href="#"><div id="container-feedback" class="container-feedback"><span class="ico-sprite fd_info"><span>'.Lang::t('_DETAILS').'</span></span>&nbsp;'.$message_text.'</div></a>';
		if($return) return $result;
		cout($result, 'feedback');
	}

	static public function notice($message_text, $return = false) {
		$result = '<a href="#"><div id="container-feedback" class="container-feedback"><span class="ico-sprite fd_notice"><span>'.Lang::t('_NOTICE').'</span></span>&nbsp;'.$message_text.'</div></a>';
		if($return) return $result;
		cout($result, 'feedback');
	}

	static public function error($message_text, $return = false) {
		$result = '<a href="#"><div id="container-feedback" class="container-feedback"><span class="ico-sprite fd_notice"><span>'.Lang::t('_OPERATION_FAILURE').'</span></span>&nbsp;'.$message_text.'</div></a>';
		if($return) return $result;
		cout($result, 'feedback');
	}

	static public function pinfo($message_text) {
		$result = '<p class="pcontainer-feedback"><span class="ico-sprite fd_info"><span>'.Lang::t('_DETAILS').'</span></span> '.$message_text.'</p>';
		return $result;
	}

	static public function pnotice($message_text) {
		$result = '<p class="pcontainer-feedback"><span class="ico-sprite fd_notice"><span>'.Lang::t('_NOTICE').'</span></span>&nbsp;'.$message_text.'</p>';
		return $result;
	}

	static public function perror($message_text) {
		$result = '<p class="pcontainer-feedback"><span class="ico-sprite fd_notice"><span>'.Lang::t('_OPERATION_FAILURE').'</span></span>&nbsp;'.$message_text.'</p>';
		return $result;
	}

}

/**
 * Returns the site base url of a website that means the full website
 * url without the platform-specific folder
 * example: browsing "www.mysite.com/test/appCore" you'll get
 * "www.mysite.com/test"
 */
function getSiteBaseUrl() {
	$current_pl = Get::cur_plat();
	$url		= substr(getPLSetting($current_pl, "url"), 0, -1);

	$search 	= str_replace("\\", '/', $GLOBALS["where_".$current_pl]);
	$cut_from 	= strrpos($search, "/");
	$search 	= substr($search, $cut_from);
	$search 	= str_replace('/', '\/', $search);
	$base_url 	= preg_replace('/'.$search.'/i', "", $url);

	return $base_url;
}

/**
 * Replaces any {site_base_url} tag with the
 * base url of the website. (see the getSiteBaseUrl function)
 */
function fillSiteBaseUrlTag($text) {
	$res = str_replace("{site_base_url}", getSiteBaseUrl(), $text);
	return $res;
}

/**
 * Replaces any site base url (see the getSiteBaseUrl function)
 * with the {site_base_url} tag.
 * example: www.mysite.com/test/appCore -> {site_base_url}/appCore
 */
function putSiteBaseUrlTag($text) {

	$base_url = getSiteBaseUrl();
	$text = str_replace($base_url, "{site_base_url}", $text);
	return $text;
}

/**
 * Return a specific setting from a platform
 * @param string $platform the code of the platform
 * @param string $param_name the name of the value
 * @param string $default the default value to return if noone is found
 * @return mixed the param required
 */
function getPLSetting($platform, $param_name, $default=FALSE) {

	require_once(_base_."/lib/lib.platform.php");
	$pl_man =& PlatformManager::CreateInstance();

	$res = $default;
	if ($pl_man->isLoaded($platform)) {
		/*
		if(!defined("LMS")) Util::load_setting(Get::cfg('prefix_lms').'_setting', 'lms');
		elseif(!defined("CMS")) Util::load_setting(Get::cfg('prefix_cms').'_setting', 'cms');
		elseif(!defined("SCS")) Util::load_setting(Get::cfg('prefix_scs').'_setting', 'scs');
		*/
		$res = Get::sett($param_name);
	}
	return $res;
}

/**
 * Add a css file in the page head
 * @param string $name
 * @param string $platform
 * @param string $folder
 * @param string $add_start
 * @return nothing
 */
function addCss($name, $platform=FALSE, $folder=FALSE, $add_start=FALSE) {

	if(!isset($GLOBALS["page"])) return;
	if ($platform === FALSE) {
		$platform=Get::cur_plat();
	}

	$clean_name=getCleanTitle($name);
	$clean_folder=($folder !== FALSE ? "_".getCleanTitle($folder) : "");
	$css_id=$platform.$clean_folder."_".$clean_name;

	if (!isset($GLOBALS["_css_cache"])) {
		$GLOBALS["_css_cache"]=array();
	}

	if (!in_array($css_id, $GLOBALS["_css_cache"])) {
		$GLOBALS["_css_cache"][]=$css_id;

		$css=Get::tmpl_path($platform)."style".($folder !== FALSE ? $folder : "")."/".$name.".css";

		$code="<link href=\"".$css."\" rel=\"stylesheet\" type=\"text/css\" />\n";

		if(isset($GLOBALS["page"])) {
			if (!$add_start)
				$GLOBALS["page"]->add($code, "page_head");
			else
				$GLOBALS["page"]->addStart($code, "page_head");
		}
	}
}

/**
 * Add a js file in the page head
 * @param string $path the relative path to the js file
 * @param string $name the name of the file with the extension .js included i.e. "functions.js"
 */
function addJs($path, $name) {

	if(!isset($GLOBALS["page"])) return;
	if(!isset($GLOBALS["_js_cache"])) $GLOBALS["_js_cache"] = array();
	if(!in_array($path.$name, $GLOBALS["_js_cache"])) {

		$GLOBALS["_js_cache"][] = $path.$name;
		$GLOBALS["page"]->add('<script type="text/javascript" src="'._deeppath_.$path.$name.'"></script>'."\n", "page_head");
	}
}

/**
 * @param string $txt  the string we want to remove accents from
 * @return string the original text with all the accents removed
 *
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
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

/**
 * @param string $title  the title we want to clean up
 * @param mixed  $max_lengthth  the max length of the resulting string; if FALSE
 *                              is passed then length is unlimited.
 *
 * @return string the cleaned up title; removes all spaces and characters that doesn't
 *                looks, for example, in a web url.
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 */
function getCleanTitle($title, $max_length=FALSE) {

	$to_underscore=array(" ", "/", "\\", "-", ".", "'", ":");

	$to_null=array("&lt;", "&gt;", ",", ";");
	for ($i=33; $i<48; $i++) {
		$chr=chr($i);
		if (!in_array($chr, $to_underscore)) {
			$to_null[]=$chr;
		}
	}
	for ($i=123; $i<256; $i++) {
		$chr=chr($i);
		if (!in_array($chr, $to_underscore)) {
			$to_null[]=$chr;
		}
	}

	$res =trim($title);
	$res =removeAccents($res);

	$res =preg_replace("/&#(.);/", "_", $res);
	$res =preg_replace("/&(.)grave/", "$1", $res);
	$res =preg_replace("/&(.)acute/", "$1", $res);

	$res =str_replace($to_underscore, "_", $res);
	$res =str_replace($to_null, "", $res);

	$res =preg_replace("/_+/", "_", $res);

	$res =rawurlencode($res);

	if (($max_length !== FALSE) && (strlen($res) > $max_length)) {
		$res=substr($res, 0, $max_length);
	}

	$res =trim($res, "_%-");

	return $res;
}

function importVar($var, $cast_int = false, $default_value = '') {

	if($cast_int) return Get::req($var, DOTY_INT, $default_value);
	return Get::req($var, DOTY_MIXED, $default_value);
}

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

/**
 * @author Giovanni Derks <virtualdarkness[AT]gmail-com>
 * @param string $str our original string
 * @param string $len the lenght of the resulting string
 *
 * @return string example: leadingZero("2", 3) => "002"
 */
function leadingZero($str, $len)
{
	$zero_to_add=$len-strlen($str);
	if ($zero_to_add > 0)
		$res=str_repeat("0", $zero_to_add).$str;
	else
		$res=$str;

	return $res;
}


/**
 *	@return array contains the html translation charset
 **/
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


/**
 * function highlightText
 *	Highlight parts of text strings with HTML tags
 *
 *  @param $string the text that will be checked for parts to highlight
 *  @param $key the text to be highlighted
 *  @param $classname class of the highlight <span> tag, "highlight" by default
 *
 *	@return the highlighted text
 **/
function highlightText($string, $key, $classname = "highlight") {
		//return str_ireplace($key, '<span class="highlight">'.$key.'</span>', $string); //str_ireplace is php5 only
		if ($key=="") return $string;
		return preg_replace("/".$key."/i", "<span class=\"highlight\">$0</span>", $string);
}
function doDebug($text) {
	if(Get::sett('do_debug') == 'on') {}
}

function errorCommunication($text) {

	doDebug($text);
}

?>