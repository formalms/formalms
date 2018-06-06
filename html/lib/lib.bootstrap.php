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

define("BOOT_CONFIG", 	0);
define("BOOT_UTILITY", 	1);
define("BOOT_DATABASE", 2);
define("BOOT_SETTING", 	3);
define("BOOT_SESS_CKE", 4);
define("BOOT_USER", 	5);
define("BOOT_INPUT", 	6);
define("BOOT_LANGUAGE", 7);
define("BOOT_DATETIME", 8);
define("BOOT_TEMPLATE", 9);
define("BOOT_PLUGINS",  10);
define("BOOT_PAGE_WR", 	11);
define("BOOT_INPUT_ALT", 99);

/**
 * This class manage the startup operation needed.
 * The file 'concept' is the bootstrap of drupal, i really like the idea in which it work
 */
class Boot {

	public static $session_name = 'docebo_session';

	private static $_boot_seq = array(
		BOOT_CONFIG 	=> 'config',
		BOOT_UTILITY 	=> 'utility',
		BOOT_DATABASE 	=> 'database',
		BOOT_SETTING	=> 'loadSetting',
		BOOT_SESS_CKE 	=> 'sessionCookie',
		BOOT_USER 		=> 'user',
		BOOT_INPUT 		=> 'filteringInput',
		BOOT_INPUT_ALT	=> 'anonFilteringInput',
		BOOT_LANGUAGE 	=> 'language',
		BOOT_DATETIME 	=> 'dateTime',
        BOOT_PLUGINS 	=> 'plugins',
		BOOT_TEMPLATE 	=> 'template',
		BOOT_PAGE_WR 	=> 'loadPageWriter'
	);

	public static $log_array = array();

	/**
	 * Load all the step requested
	 * @param $load_option int or array, if you pass an int all the operation
	 * 						with and index that is lower will be done,
	 * 						if you pass an array you can tell the function
	 * 						exactly which step you want to be done
	 */
	public static function init($load_option = BOOT_PAGE_WR) {

		$boot_report = array();
		if(is_array($load_option)) {

			$last_step = BOOT_PAGE_WR;
			$step_list = $load_option;
		} else {
			// custom boot sequence given, use this one
			$last_step = $load_option;
			$step_list = self::$_boot_seq;
		}
		while(list($step_num, $step_method) = each($step_list)) {

			// custom boot sequence given, must retrive the correct method to call
			if(is_array($load_option)) $step_method  = self::$_boot_seq[$step_method];

			if($last_step >= $step_num) {

				self::log( $step_num.' ) '.__CLASS__.'->'.$step_method );
				self::$step_method();
			}
		}
	}

	/**
	 * Load all the final operation, if something need to do some check or data
	 * manipulation before the page is return to the browser this is the place
	 */
	public static function finalize() {

		if( isset($GLOBALS['current_user']) && Docebo::user()->isLoggedIn() ) {
			Docebo::user()->SaveInSession();
		}
		$db = DbConn::getInstance();
		$db->close();
	}

	/**
	 * let's start the loading, we must perform these operation
	 * - check for globals rewriting
	 * - unset all the others globals
	 * - load config file
	 * - setup php setting
	 * @return array
	 */
	private static function config() {

		$step_report = array();

		//unset all the globals that aren't php setted
		if(ini_get('register_globals')) {

			self::log( "Unset all the globals that aren't php setted. (Emulate register global = off)" );
			$allowed = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_FILES', '_ENV', '_SERVER', '_REQUEST');
			while(list(, $elem) = each($allowed)) {
		 		if (!isset($allowed[$elem])) unset($GLOBALS[$elem]);
			}
		}

		//start timer
		self::log( "Start timer and memory usage counter." );
		self::start();

		// detect globals overwrite (old php bug)
		self::log( "Detect globals overwrite attempts." );
		$list = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_FILES', '_SESSION');
		while(list(, $elem) = each($list)) {
			if(isset($_REQUEST[$elem])) die('Request overwrite attempt detected');
		}

		//include config
		self::log( "Include configuration file." );

		$cfg = array();
		if (!file_exists(dirname(__FILE__).'/../config.php')){
            $path = _deeppath_
                .str_replace(_base_, '.', constant('_base_'));
            Header("Location: ". str_replace(array('//', '\\/', '/./'), '/', $path) ."/install/");
        }
		require_once dirname(__FILE__).'/../config.php';
		$GLOBALS['cfg'] = $cfg;

		$GLOBALS['prefix_fw']	= $cfg['prefix_fw'];
		$GLOBALS['prefix_lms']	= $cfg['prefix_lms'];
		$GLOBALS['prefix_scs']	= $cfg['prefix_scs'];
		$GLOBALS['prefix_ecom'] = $cfg['prefix_ecom'];
		$GLOBALS['prefix_crm']	= $cfg['prefix_crm'];

		// setup some php.ini things
		$step_report[] = "Setup some php.ini settings.";
		ini_set('arg_separator.output',     '&amp;');
		ini_set('magic_quotes_runtime',     0);
		ini_set('magic_quotes_sybase',      0);
		ini_set('session.cache_expire',     (int)$cfg['session_lenght']);
		ini_set('session.cache_limiter',    'none');
		ini_set('session.cookie_lifetime',  (int)$cfg['session_lenght']);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.use_trans_sid',    0);
		ini_set('url_rewriter.tags',        '');
		if($cfg['session_save_path'] !== false) ini_set("session.save_path", $cfg['session_save_path']);
        if (isset($cfg['session_save_handler']) && $cfg['session_save_handler'] === 'memcached') {
            ini_set('session.save_handler', 'memcached');
            ini_set('memcached.sess_prefix', $_SERVER['HTTP_HOST'].'.forma.sess.key.');
            ini_set('memcached.sess_locking', '1');
        }

		// set default time zone TZ
		if( ! isset($cfg['timezone']) ) {	// timezone not speficied in config
			$cfg['timezone'] = @date_default_timezone_get();
		}
		if ( ! @date_default_timezone_set($cfg['timezone']) ) {
			$cfg['timezone'] = @date_default_timezone_get();
			date_default_timezone_set(@date_default_timezone_get());
		}
		self::log( "Time zone setted to TZ= " . @date_default_timezone_get() );

		// debugging ?
		self::log( ( $cfg['do_debug'] ? 'Enable (set: E_ALL) ' : 'Disable (set: E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR)' )." error reporting." );
		if($cfg['do_debug']) {
			@error_reporting(E_ALL);
			@ini_set('display_errors', 1);
		} else {
			@error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR);
		}

		// todo: backward compatibility
		$GLOBALS['where_framework_relative'] = ( !defined("CORE") ? _deeppath_ : '' )._folder_adm_;
		$GLOBALS['where_lms_relative']		 = ( !defined("LMS") ? _deeppath_ : '' )._folder_lms_;
		$GLOBALS['where_scs_relative']		 = _deeppath_._folder_scs_;
		$GLOBALS['where_files_relative']	 = _deeppath_._folder_files_;

		$GLOBALS['where_files_lms_relative'] = _deeppath_._folder_files_lms_;
		$GLOBALS['where_files_app_relative'] = _deeppath_._folder_files_app_;
		$GLOBALS['where_files_com_relative'] = _deeppath_._folder_files_com_;


		$GLOBALS['where_framework'] = _adm_;
		$GLOBALS['where_lms']		= _lms_;
		$GLOBALS['where_scs']		= _scs_;
		$GLOBALS['where_files']		= _files_;

		$GLOBALS['where_files_lms']	= _files_lms_;
		$GLOBALS['where_files_app']	= _files_app_;
		$GLOBALS['where_files_com']	= _files_com_;

		/*
		self::log( "Register error handler." );
		set_error_handler(array('Boot', 'error_catcher'));
		*/
	}

	/**
	 * - load the error handling lib
	 * - load utility libraries (not all but some thing that help the programmers life)
	 * @return array
	 */
	private static function utility() {

		// composer autoload
		self::log( "Load composer autoload." );
		require_once(_base_.'/vendor/autoload.php');

		self::log( "Include autoload file." );
		require_once _base_.'/lib/lib.autoload.php';

		self::log( "Include log file." );
		require_once _base_.'/lib/loggers/lib.logger.php';

		// config manager
		self::log( "Include configuration file." );
		require_once _base_.'/lib/lib.get.php';
		require_once Docebo::inc( _base_.'/lib/lib.utils.php' );

		// utf8 support
		self::log( "Load utf8 management library." );
		require_once _base_.'/addons/utf8/lib.utf8.php';

		// filter input
		self::log( "Load filter input library." );
		require_once _base_.'/lib/lib.filterinput.php';

		// yui
		self::log( "Load yui library." );
		require_once _base_.'/lib/lib.yuilib.php';

		// template
		self::log( "Load template library." );
		require_once(_base_.'/lib/lib.template.php');

		// mimetype
		self::log( "Load mimetype library." );
		require_once(_base_.'/lib/lib.mimetype.php');

		require_once(_lib_.'/lib.acl.php');

	}

    /**
     * - step to load the plugins
     */
    private static function plugins() {

        self::log( "Prepare plugin's listeners." );
        PluginManager::hook();

    }

	/**
	 * - load the appropiate database driver
	 * - connect to the database
	 * - load setting from database
	 * @return array
	 */
	private static function database() {

		self::log( "Load database funtion management library." );
		require_once _base_.'/db/lib.docebodb.php';

		// utf8 support
		self::log( "Connect to database." );
		DbConn::getInstance();
		if(!DbConn::$connected && file_exists(_base_.'/install')) {

			Header("Location: ". Get::rel_path('base') ."/install/");
		}
	}

	/**
	 * - read the specific setting for the adm platform and for the current one
	 * @return null
	 */
	private static function loadSetting() {

		self::log( " Load settings from database." );
		Util::load_setting(Get::cfg('prefix_fw').'_setting', 'framework');

		if (Get::sett('do_debug') == 'on'){
            @error_reporting(E_ALL);
        }
	}

	/**
	 * - setup session configuration
	 * - init session
	 * - read cookie information (not used right now)
	 * @return array
	 */
	private static function sessionCookie()
	{
		// start session
		self::log( " Start session '".self::$session_name."'" );

		session_set_cookie_params( 0 );
		ini_set('session.gc_maxlifetime', Get::cfg('session_lenght', 3600));

		session_name(self::$session_name);
		session_start();

		$session_time = Get::sett('ttlSession', 3600);
		if(!isset($_SESSION['session_timeout']))
			$_SESSION['session_timeout'] = time();
		$session_time_passed = time() - $_SESSION['session_timeout'];

		if($session_time_passed > $session_time && isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
		{
			session_destroy();
			Util::jump_to(Get::rel_path('base').'/index.php?msg=103');
		}

		$_SESSION['session_timeout'] = time();
	}

	/**
	 * - create the user representative object, load anonymoous data if needed or load the info of the
	 * 		user logged into the session (also manage the user login here ? will be usefull for kerberos
	 * 		and similar approach)
	 * - load user personal data (language, date/time setting, template preference)
	 * - load user system data (role)
	 * @return array
	 */
	private static function user() {

		self::log( "Load user from session '".self::$session_name."'" );

		// load current user from session
		require_once(_base_.'/lib/lib.user.php');
		$GLOBALS['current_user'] =& DoceboUser::createDoceboUserFromSession('public_area');

		// ip coerency check
		self::log( "Ip coerency check.");
		if(Get::sett('session_ip_control', 'on') == 'on') {

			if(Docebo::user()->isLoggedIn() && (Docebo::user()->getLogIp() != $_SERVER['REMOTE_ADDR']))
			{
				session_destroy();
				Util::jump_to(Get::rel_path('base').'/index.php?msg=104');
				//Util::fatal("logip: ".Docebo::user()->getLogIp()."<br/>"."addr: ".$_SERVER['REMOTE_ADDR']."<br/>".'Ip incoherent!');
				//unlog the user
				die();
			}
		}
		// Generate a session signature or regenerate it if needed
		self::log( "Generating session signature" );
		Util::generateSignature();
	}
	private static function anonFilteringInput() {

		$step_report = array();

		// Convert to Utf-8.
		self::log( "Convert to Utf-8." );
		$_GET 		= utf8::clean($_GET);
		$_POST 		= utf8::clean($_POST);
		$_COOKIE 	= utf8::clean($_COOKIE);
		$_SERVER 	= utf8::clean($_SERVER);
		if(isset($_FILES)) 	$_FILES = utf8::clean($_FILES);

		// Convert ' and " (quote or unquote)
		self::log( "Sanitize the input." );

		$filter_input  = new FilterInput();
		$filter_input->tool = Get::cfg('filter_tool', 'htmlpurifier');

		// Whitelist some tags if we're a teacher in a course:
		if (isset($_SESSION['idCourse']) && $_SESSION['levelCourse'] >= 6) {
			$filter_input->appendToWhitelist(array(
				'tag'=>array('object', 'param'),
				'attrib'=>array(
					'object.data', 'object.type', 'object.width',
					'object.height', 'param.name', 'param.value',
				),
			));
		}

		$filter_input->sanitize();

	}

	private static function filteringInput() {

		$step_report = array();

		// todo: check if we can do in other way the same thing
		// save login password from modification
		$ldap_used = Get::sett('ldap_used');
		if( ($ldap_used == 'on') && isset($_POST['modname']) && ($_POST['modname'] == 'login') && isset($_POST['passIns'])) {
			$password_login = $_POST['passIns'];
		}

		// Convert to Utf-8.
		self::log( "Convert to Utf-8." );
		$_GET 		= utf8::clean($_GET);
		$_POST 		= utf8::clean($_POST);
		$_COOKIE 	= utf8::clean($_COOKIE);
		$_SERVER 	= utf8::clean($_SERVER);
		if(isset($_FILES)) 	$_FILES = utf8::clean($_FILES);

		// Convert ' and " (quote or unquote)
		self::log( "Sanitize the input." );

		if(Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) {
			$filter_input  = new FilterInput();
			$filter_input->tool = 'none';
			$filter_input->sanitize();
		}
		else {

			$filter_input  = new FilterInput();
			$filter_input->tool = Get::cfg('filter_tool', 'htmlpurifier');

			// Whitelist some tags if we're a teacher in a course:
			if (isset($_SESSION['idCourse']) && $_SESSION['levelCourse'] >= 6) {
				$filter_input->appendToWhitelist(array(
					'tag'=>array('object', 'param'),
					'attrib'=>array(
						'object.data', 'object.type', 'object.width',
						'object.height', 'param.name', 'param.value',
					),
				));
			}

			$filter_input->sanitize();
		}
		if( ($ldap_used == 'on') && isset($_POST['modname']) && ($_POST['modname'] == 'login') && isset($_POST['passIns'])) {
			$_POST['passIns'] = utf8::clean(stripslashes($password_login));
		}

		if(!defined("IS_API") && !defined("IS_PAYPAL") && ( strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' || defined("IS_AJAX"))) {
			// If this is a post or a ajax request then we must have a signature attached
			Util::checkSignature();
		}
	}

	/**
	 * - load language from source
	 * - set the system language looking at ( user preference, browser detect, user force )
	 * @return array
	 */
	private static function language() {

		self::log( "Loading session language functions" );

		require_once( Docebo::inc( _i18n_.'/lib.lang.php' ) );
		$sop = Get::req('sop', DOTY_ALPHANUM, false);
		if(!$sop) $sop = Get::req('special', DOTY_ALPHANUM, false);
		switch($sop) {
			case "changelang" : {
				$new_lang = Get::req('new_lang', DOTY_ALPHANUM, false);

				self::log( "Sop 'changelang' intercepted, changing lang to : $new_lang" );
				Lang::set($new_lang, isset($_GET['logout']));
			};break;
		}

		//$glang =& DoceboLanguage::createInstance( 'standard', 'framework');
		//$glang->setGlobal();
	}

	/**
	 * - load the user preference about the date and time display and GMT
	 * @return array
	 */
	private static function dateTime() {

		self::log( "Loading regional settings functions" );

		// todo : change this class
		require_once(_i18n_.'/lib.format.php');
		Format::instance();
	}

	/**
	 * - load the library needed for image / css / skin retrivial process
	 * - detect the user associated template ( webdomain | admin setting | user preference )
	 * @return array
	 */
	private static function template() {

		self::log( "Include layout manager file." );
		require_once _lib_.'/layout/lib.layout.php';
	}

	/**
	 * - load the page writer, that is a output cache mechanism that allow the use of different
	 * - page-zone to be cached (head, menu, content)
	 * @return null
	 */
	private static function loadPageWriter() {

		self::log( "Include page writer manager file." );
		require_once _base_.'/lib/lib.pagewriter.php';
	}

	public static function start() {

	    list($usec, $sec) = explode(" ", microtime());
		$GLOBALS['start'] = array(
			'time' => ((float)$usec + (float)$sec),
			'memory' => function_exists('memory_get_usage') ? memory_get_usage() : 0
		);
	}

	public static function current_time() {

	    list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);
		return $now - $GLOBALS['start']['time'];
	}

	public static function current_memory() {

		$current = function_exists('memory_get_usage') ? memory_get_usage() : 0;
		return $current - $GLOBALS['start']['memory'];
	}

	public static function log($str) { self::$log_array[] = $str; }

	public static function get_log($as_string = false) { return ( $as_string ? implode(self::$log_array, "\n") : self::$log_array ); }

	public static function error_catcher($code,$message,$file,$line) {

		if($code & error_reporting()) {

			restore_error_handler();

			echo "<h1 style=\"font-size:16px;font-family:Arial;\">PHP Error [$code]</h1>\n"
				."<p style=\"font-size:12px;font-family:Arial;\">$message ($file:$line)</p>\n";

			if(isset($_SERVER['REQUEST_URI']))
				echo '<p style="font-size:12px;font-family:Arial;">REQUEST_URI = '.$_SERVER['REQUEST_URI'].'</p>';

			echo '<table style="margin:0; padding:0; font-size:12px;font-family: Arial; border-spacing:0;">'
				.'<tr>'
					.'<td style="background:#eeeeee;"></td>'
					.'<td colspan="2" style="background:#eeeeee;color:gray;padding-left:10px;border-bottom:1px solid gray;">'
					.'Stack trace'
					.'</td>'
				.'</tr>';

			$trace = debug_backtrace();
			foreach($trace as $i=>$t) {

				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';

				echo '<tr><td style="width:5px;background:#eeeeee;color:gray;padding:3px 5px 3px 10px;border-right:1px solid gray;">'.$i.'</td>'
					.'<td style="'.($i%2?'background:#F7F7F7;':'').'padding:3px 10px 3px 10px;">'
					."{$t['file']} ({$t['line']}) "
					.'</td>'
					.'<td style="'.($i%2?'background:#F7F7F7;':'').'padding:3px 10px 3px 10px;"><i>';
				if(isset($t['object']) && is_object($t['object']))
					echo get_class($t['object']).'->';
				if($t['function'] != 'error_catcher')
					echo "{$t['function']}</i>()<i>";
				echo '</i></td></tr>';
			}
			echo '</table>';
			die();
		}
	}

}

?>