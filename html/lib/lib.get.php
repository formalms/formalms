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
 */

/**
 * docebo constant for var type
 */
define("DOTY_INT", 			0);
define("DOTY_FLOAT", 		1);
define("DOTY_DOUBLE", 		2);
define("DOTY_STRING", 		3);
define("DOTY_MIXED", 		4);
define("DOTY_JSONDECODE", 	5);
define("DOTY_JSONENCODE", 	6);
define("DOTY_ALPHANUM", 	7);
define("DOTY_NUMLIST",		8);
define("DOTY_BOOL",			9);
define("DOTY_MVC",			10);

class Get {

	/**
	 * Import var from GET and POST, if the var exists in POST and GET, the post value will be preferred
	 * @author Pirovano Fabio
	 *
	 * @param string 	$name 		the var to import
	 * @param int 		$typeof		the type of the variable (used for casting)
	 * @param mixed		$default	the default value
	 * @param mixed		$only_from false if can take from both post and get; else get or post
	 *                           to force the reading from one method.
	 *
	 * @return mixed	return the var founded in post/get or the default value if the var doesn't exixst
	 */
	static public function req($var_name, $typeof = DOTY_MIXED, $default_value = '', $only_from=false) {

		$value = $default_value;
		if (empty($only_from)) {
			if(isset($_POST[$var_name])) $value = $_POST[$var_name];
			elseif(isset($_GET[$var_name])) $value = $_GET[$var_name];
			elseif(isset($_REQUEST[$var_name])) $value = $_REQUEST[$var_name];
		}
		else if ($only_from == 'post' && isset($_POST[$var_name])) {
			$value = $_POST[$var_name];
		}
		else if ($only_from == 'get' && isset($_GET[$var_name])) {
			$value = $_GET[$var_name];
		}
		else if ($only_from == 'request' && isset($_REQUEST[$var_name])) {
			$value = $_REQUEST[$var_name];
		}
		
		return self::filter($value, $typeof);
	}

	/**
	 * Data filtering
	 * @param mixed $value the value to clean
	 * @param int $typeof the type of the variable
	 * @return mixede the cleaned value
	 */
	static public function filter($value, $typeof) {

		switch($typeof) {
			case DOTY_INT 		: $value = (int)$value;break;
			case DOTY_DOUBLE 	:
			case DOTY_FLOAT 	: $value = (float)$value;break;
			case DOTY_STRING 	: $value = strip_tags($value);break;
			case DOTY_ALPHANUM 	: $value = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $value);break;
			case DOTY_NUMLIST 	: $value = preg_replace('/[^0-9\-\_,]+/', '', $value);break;

			case DOTY_JSONDECODE : {
				if(!isset($GLOBALS['obj']['json_service'])) {
					require_once(_base_.'/lib/lib.json.php');
					$GLOBALS['obj']['json_service'] = new Services_JSON();
				}
				$value = $GLOBALS['obj']['json_service']->decode($value);
			};break;
			case DOTY_JSONDECODE : {
				if(!isset($GLOBALS['obj']['json_service'])) {
					require_once(_base_.'/lib/lib.json.php');
					$GLOBALS['obj']['json_service'] = new Services_JSON();
				}
				$value = $GLOBALS['obj']['json_service']->encode($value);
			};break;
			case DOTY_BOOL 		: $value = ( $value ? true : false );break;
			case DOTY_MVC		: {

				$value = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $value);
				if($value{0} == '/') $value = '';
			};break;
			case DOTY_MIXED 	:
			default 			: {}
		}

		return $value;
	}

	/**
	 * calls the req method and forces the type to 'get'
	 * @param <type> $var_name
	 * @param <type> $typeof
	 * @param <type> $default_value
	 * @return <type>
	 */
	public static function gReq($var_name, $typeof = DOTY_MIXED, $default_value = '') {
		return self::req($var_name, $typeof, $default_value, 'get');
	}


	/**
	 * calls the req method and forces the type to 'post'
	 * @param <type> $var_name
	 * @param <type> $typeof
	 * @param <type> $default_value
	 * @return <type>
	 */
	public static function pReq($var_name, $typeof = DOTY_MIXED, $default_value = '') {
		return self::req($var_name, $typeof, $default_value, 'post');
	}


	/**
	 * Return the value of a configuration
	 * @param string $cfg_name The configuration name
	 * @param mixed $default The default value return if the configuration is not found or not set
	 * @return Mixed The value of the configuration param
	 */
	public static function cfg($cfg_name, $default = false) {

        if(!isset($GLOBALS['cfg'][$cfg_name])) 
                    $value = $default;
                else {
                    $value = $GLOBALS['cfg'][$cfg_name];
                }
        return $value;
    }
	
	/**
	 * Return the value of a plugin configuration
	 * @param string $plugin_name The plugin name
	 * @param string $cfg_name The configuration name
	 * @param mixed $default The default value return if the configuration is not found or not set
	 * @return Mixed The value of the configuration param
	 */
	public static function pcfg($plugin_name, $cfg_name, $default = false) {

		if(!isset($GLOBALS['cfg'][$plugin_name][$cfg_name])) $value = $default;
		$value = $GLOBALS['cfg'][$plugin_name][$cfg_name];

		return $value;
	}

	/**
	 * Return the value of a platform setting
	 * @param string $sett_name 
	 * @param string $default
	 * @return mixed the value of the setting or the default value 
	 */
	public static function sett($sett_name, $default = false) {
		$platform = 'framework';
		if(!isset($GLOBALS[$platform][$sett_name])) return $default;
		return $GLOBALS[$platform][$sett_name];
	}
	
	/**
	 * Return the current platform code
	 * @return <string> the platform path
	 */
	public static function cur_plat() {
		// where are we ?
		if(defined("LMS"))		return 'lms';
		elseif(defined("CMS"))	return 'cms';
		elseif(defined("ECOM"))	return 'ecom';
		elseif(defined("CRM"))	return 'crm';
		return 'framework';
	}

	/**
	 * Return the calculated relative path form the current zone (platform) to the requested one
	 * @param <string> $item (base, lms, cms, ...)
	 * @return <string> the relative path
	 */
	public static function rel_path($to = false) {
		// where are we ?
		if($to === false) {
			if(defined("CORE"))		$to = 'adm';
			elseif(defined("LMS"))	$to = 'lms';
			elseif(defined("CMS"))	$to = 'cms';
		}
		if(!defined('_'.$to.'_')) $to = 'base';
		$path = _deeppath_
			.str_replace(_base_, '.', constant('_'.$to.'_'));
		return str_replace(array('//', '\\/', '/./'), '/', $path);
	}

	/**
	 * Return the absolute path of the platform
	 * @param <string> $item (base, lms, cms, ...)
	 * @return <string> the absolute path
	 */
	public static function abs_path($to = false) {
		$folder = '';
		if($to === false) {
			if(defined("CORE"))		$folder = _folder_adm_;
			elseif(defined("LMS"))	$folder = _folder_lms_;
			elseif(defined("CMS"))	$folder = _folder_cms_;
			elseif(defined("SCS"))	$folder = _folder_scs_;
		} else {
			switch (strtolower($to)) {
				case 'adm': $folder = _folder_adm_; break;
				case 'lms': $folder = _folder_lms_; break;
				case 'cms': $folder = _folder_cms_; break;
				case 'scs': $folder = _folder_scs_; break;
			}
		}
		$folder = str_replace(array('//', '\\/', '/./'), '/', $folder);
		$path = Get::sett('url').$folder;
		return $path;
	}

	/**
	 * Return the calculated relative path form the current zone (platform) to the requested one
	 * @param <string> $item (base, lms, cms, ...)
	 * @return <string> the relative path
	 */
	public static function tmpl_path($item = false) {

		if($item === false) $platform = Get::cur_plat();
		else $platform = $item;
		$path = Get::rel_path('base').'/templates/'.getTemplate().'/';
		return str_replace('/./','/', $path);
	}

	/**
	 * Return html code and resolved path for an image
	 * @param <string> $src the img[src] attribute, the path can be absolute or relative to the images/ folder of the current template
	 * @param <string> $alt the img[alt] attribute
	 * @param <string> $class_name the img[class] attribute
	 * @param <string> $extra some extra code that you need to add into the image
	 * @param <bool> $is_abspath if true the src is assumed absolute, if false the relative path is added to the src attr
	 * @return <string> the html code (sample <img ... />)
	 */
	public static function img($src, $alt = false, $class_name = false, $extra = false, $is_abspath = false) {
		// where are we ?
		if(!$is_abspath) $src = Get::tmpl_path('base').'images/'.$src;

		return '<img src="'.$src.'" '
					.'alt="'.( $alt ? $alt : substr($src, 0 , -4) ).'" '
                    .'title="'.( $alt ? $alt : substr($src, 0 , -4) ).'" '
					.( $class_name != false ? 'class="'.$class_name.'" ' : '' )
					.( $extra != false ? $extra.' ' : '' )
					.'/>';
	}

	/**
	 * Return html code
	 */
	public static function sprite($class, $name, $title = false) {
		// where are we ?
		if(!$title) $title = $name;

		return '<span class="ico-sprite '.$class.'" title="'.$title.'"><span>'.$name.'</span></span>';
	}


	/**
	 * Return html code and for a
	 */
	public static function sprite_link($class, $href, $name, $title = false) {
		// where are we ?
		if(!$title) $title = $name;

		return '<a class="ico-sprite '.$class.'" href="'.$href.'" title="'.$title.'"><span>'.$name.'</span></a>';
	}

	/**
	 * Build an html for an image encapsulated into a link
	 * @param <string> $url the url for the a[href]
	 * @param <string> $title the title for the a[title]
	 * @param <string> $src the img[src]
	 * @param <string> $alt the img[alt]
	 * @param <array> $extra the content of the 'link' key is used as extra in the a element if specified, the 'img' key content is used into the img element
	 * @return <string> html code (sample: <a ...><img ...></a> )
	 */
	public static function link_img($url, $title, $src, $alt, $extra = false) {
		// where are we ?
		$src = Get::rel_path(_base_).'/templates/standard/images/'.$src;

		return '<a href="'.$url.'" title="'.$title.'"'.
			( !empty($extra['link']) != false ? ' '.$extra['link'] : '' ).
			'>'.
			'<img src="'.$src.'" '.
				'alt="'.( $alt ? $alt : substr($src, 0 , -4) ).'" '.
				'title="'.$title.'" '.
				( !empty($extra['img']) != false ? $extra.' ' : '' ).
				'/>'.
			'</a>'.
			"\n";
	}
	
	/**
	 * This function try to evaluate the current site address
	 * @return <string> (i.e. http://localhost)
	 */
	public static function site_url() {
		
		return 'http' . ( ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) 
		                or (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') 
		                or (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on') ) ? 's' : '' ).'://'
		    .( (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] )
	    	.( strlen(dirname($_SERVER['SCRIPT_NAME'])) != 1 ? dirname($_SERVER['SCRIPT_NAME']) : '' ).'/';

//		return 'http'.( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '' ).'://'
//			.$_SERVER['HTTP_HOST']
//	    	.( strlen(dirname($_SERVER['SCRIPT_NAME'])) != 1 ? dirname($_SERVER['SCRIPT_NAME']) : '' )
//			.'/';
	}

	/**
	 * Draw the page title of a mvc or module
	 * @param array $text_array the title of the page or an array with  the breadcrmbs elements (key => value)
	 *							if the key is a string it will be userd as a link
	 * @param bool $echo if true the output will be automaticaly echoed
	 * @return string
	 */
	function title($text_array, $echo = true) {

		$is_first = true;
		if(!is_array($text_array))
			$text_array = array($text_array);

		$html = '<div class="title_block">'."\n";
		foreach($text_array as $link => $title) {

			if($is_first) {

				$is_first = false;
				// Retrive, if exists, name customized by the user for the module
				/*if(!$ignore_glob && isset($GLOBALS['module_assigned_name'][$GLOBALS['modname']]) && $GLOBALS['module_assigned_name'][$GLOBALS['modname']] != '') {
					$title = $GLOBALS['module_assigned_name'][$GLOBALS['modname']];
				}*/
				// Area title
				$html .= '<h1>'
					.(!is_int($link) ? '<a href="'.$link.'">' : '' )
					.$title
					.(!is_int($link) ? '</a>' : '' )
					.'</h1>'."\n";

				if (!defined("IS_AJAX")) $GLOBALS['page']->add('<li><a href="#main_area_title">'. Lang::t('_JUMP_TO', 'standard').' '.$title.'</a></li>', 'blind_navigation');

				if ($title) {
					if (!defined("IS_AJAX")) $GLOBALS['page_title'] = Get::sett('page_title', '').' &rsaquo; '.$title;
				}

				// Init navigation
				if(count($text_array) > 1) {
					$html .= '<ul class="navigation">';
				//	if(!is_int($link)) {
				//		$html .= '<li><a href="'.$link.'">'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</a></li>';
				//	} else $html .= '<li>'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</li>';
				}
			} else {

				if(is_int($link)) $html .= '<li> &rsaquo; '.$title.'</li>';
				else $html .= ' <li> &rsaquo; <a href="'.$link.'">'.$title.'</a></li>';
			}
		}
		if(count($text_array) > 1) $html .= '</ul>'."\n";
		$html .= '</div>'."\n";
		if($echo) echo $html;
		return $html;
	}

	/**
	 * Return the user ip, also check for proxy http header
	 * @return <string> ip (i.e. 127.0.0.1)
	 */
	public static function user_ip() {

        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
		if (isset($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
        }
		return $_SERVER["REMOTE_ADDR"];
	}

	/**
	 * This funciont try to find the user SO and return it, if the so isn't in the internal list return 'unknown'
	 * @return <string> (ie. windows)
	 */
	public static function user_os() {

		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$known_os_arr = explode(" ", "linux macos sunos bsd qnx solaris irix aix unix amiga os/2 beos windows");
		foreach($known_os_arr as $os) {

			if(strpos($agent, $os) !== false) return $os;
		}
		return 'unknown';
	}

	/**
	 * This funciont try to find the user browser and return it, if the browser isn't in the internal list return 'unknown'
	 * @return <string> (ie. firefox)
	 */
	public static function user_agent() {

		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$known_browser_arr = explode(" ", "firefox netscape konqueror epiphany mozilla safari opera mosaic lynx amaya omniweb msie chrome iphone");
		$required = array(
			"firefox"	=> array("gecko", "mozilla", "firefox"),
			"netscape"	=> array("gecko", "mozilla", "netscape"),
			"konqueror" => array("gecko", "mozilla", "konqueror"),
			"epiphany"	=> array("gecko", "mozilla", "epiphany"),
			"mozilla"	=> array("gecko", "mozilla")
		);

		$founded = false;
		foreach($known_browser_arr as $browser) {

			if(strpos($agent, $browser) !== false) $founded = $browser;
		}
		// the browser is not in the list
		if(!$founded) return 'unknown';

		// founded
		if(!isset($required[$founded])) return $founded;

		// more distinction needed
		$refined = false;
		foreach($required[$founded] as $browser) {

			if(strpos($agent, $browser) !== false) $refined = $browser;
		}
		if(!$refined) return $refined;

		return 'unknown';
	}

	/**
	 * Parse the HTTP_ACCEPT_LANGUAGE in order to have a more usable language selction
	 * @param <bool> $main_only true if you want only the main language from the browser, false if you wnat the entire list
	 * @return <mixed> string if $main_only = true (ie. en-EN), array if $main_only = false
	 */
	public static function user_acceptlang($main_only = true) {

		$lang_list = array();
		$main_langs = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		foreach($main_langs as $lang_set) {

			$single_lang = explode(";", $lang_set);
			foreach($single_lang as $i => $lang_code) {
				//discard q=N entries
				if(strpos($lang_code, 'q=') === false) {
					$lang_list[] = addslashes($lang_code);
					if($main_only) return $lang_code;
				}
			}
		} // foreach
		return $lang_list;
	}

	/**
	 * Check if the user is a bot
	 * @return <int> 1 if the user is a bot, 0 otherwise
	 */
	public static function user_is_bot() {

		$to_test = array(
			"googlebot",		// Google
			"scooter",			// Altavista
			"altavista",		// Altavista UK
			"webcrawler",		// AllTheWeb
			"architextspider",	// Excite
			"slurp",			// Inktomi
			"iltrovatore",		// Il Trovatore
			"ultraseek",		// Infoseek
			"lookbot",			// look.com
			"mantraagent",		// looksmart.com
			"lycos_spider",		// Lycos
			"msnbot",			// Msn Search (the hated)
			"shinyseek",		// ShinySeek
			"robozilla"			// dmoz.org
		);
		$agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
		foreach($to_test as $botname) {

			if(strpos($agent, $botname) !== false) return 1;
		}
		return 0;
	}

	public static function accessibilty() {
		return Get::sett('boh');
	}

    /**
	 * Return the size in bytes of the specified file
	 * @param <string> $file_path The target file
	 * @return <int> The size of the file in bytes
	 */
    public static function file_size($file_path)
    {
        return @filesize($file_path);
    }

    /**
	 * Return the size in bytes of the specified directory
	 * @param <string> $path The target dir
	 * @return <int> The size of the dir in bytes
	 */
    public static function dir_size($path)
    {
        if(!is_dir($path))
            return Get::file_size($path);
        if($scan_dir = opendir($path))
        {
            $size = 0;
            while($file = readdir($scan_dir))
			if($file != '..' && $file !='.' && $file != '')
            	$size += self::dir_size($path.'/'.$file);
		}

        closedir($scan_dir);

        return $size;
    }

	/**
	 *
	 * @param string $message_text The text for the alert
	 * @param string $message_type The type of the message to append
	 * @return string The html code for the alert
	 */
    public static function append_alert($message_text, $message_type = false)
    {
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

} // end of class Get
