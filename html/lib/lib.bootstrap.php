<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
require_once _base_ . '/vendor/autoload.php';

use FormaLms\lib\Domain\DomainHandler;
use FormaLms\lib\System\SystemManager;
use FormaLms\Exceptions\FormaStatusException;

use function GuzzleHttp\default_ca_bundle;

defined('IN_FORMA') or exit('Direct access is forbidden.');




/**
 * This class manage the startup operation needed.
 * The file 'concept' is the bootstrap of drupal, i really like the idea in which it work.
 */
class Boot
{
    private static array $checkStatusFlags;

    private static bool $prettyRedirect;

    private static $_boot_seq = [
        BOOT_PHP => 'checkPhpVersion',
        BOOT_CONFIG => 'config',
        BOOT_UTILITY => 'utility',
        BOOT_REQUEST => 'request',
        BOOT_PLATFORM => 'checkPlatform',
        BOOT_SETTING => 'loadSetting',
        BOOT_DOMAIN_AND_TEMPLATE => 'domainAndTemplate',
        BOOT_PLUGINS => 'plugins',
        BOOT_USER => 'user',
        BOOT_SESSION_CHECK => 'sessionCheck',
        BOOT_INPUT => 'filteringInput',
        BOOT_INPUT_ALT => 'anonFilteringInput',
        BOOT_LANGUAGE => 'language',
        BOOT_HOOKS => 'hooks',
        BOOT_DATETIME => 'dateTime',
        BOOT_TEMPLATE => 'template',
        BOOT_PAGE_WR => 'loadPageWriter',
        CHECK_SYSTEM_STATUS => 'checkSystemStatus'
    ];

    public static $log_array = [];

    public static SystemManager $systemManager;

    /**
     * Load all the step requested.
     *
     * @param $load_option int or array, if you pass an int all the operation
     *                        with and index that is lower will be done,
     *                        if you pass an array you can tell the function
     *                        exactly which step you want to be done
     */
    public static function init($load_option = CHECK_SYSTEM_STATUS)
    {
        //inizializzazione
        self::$checkStatusFlags = [];

        self::$systemManager = SystemManager::getInstance();
        self::$prettyRedirect = self::$systemManager->checkWebServer();

        if (is_array($load_option)) {
            $last_step = CHECK_SYSTEM_STATUS;
            $step_list = $load_option;
        } else {
            // custom boot sequence given, use this one
            $last_step = $load_option;
            $step_list = self::$_boot_seq;
        }

        foreach ($step_list as $step_num => $step_method) {
            // custom boot sequence given, must retrive the correct method to call
            if (is_array($load_option)) {
                $step_method = self::$_boot_seq[$step_method];
            }

            if ($last_step >= $step_num) {
                self::log($step_num . ' ) ' . __CLASS__ . '->' . $step_method);
                self::$step_method();
            }
        }
    }

    /**
     * Load all the final operation, if something need to do some check or data
     * manipulation before the page is return to the browser this is the place.
     */
    public static function finalize()
    {
        if (\FormaLms\lib\FormaUser::getCurrentUser()->isLoggedIn()) {
            \FormaLms\lib\FormaUser::getCurrentUser()->saveInSession();
        }
        $db = \FormaLms\db\DbConn::getInstance();
        $db->close();
    }

    /**
     * let's start the loading, we must perform these operation
     * - check for globals rewriting
     * - unset all the others globals
     * - load config file
     * - setup php setting.
     *
     * @return array
     */
    private static function config()
    {
        $cfg = null;

        if (!file_exists(dirname(__DIR__, 1).'/config.php')) {
            self::$checkStatusFlags[] = array_search(__FUNCTION__, self::$_boot_seq);
        } else {
            require dirname(__DIR__, 1).'/config.php';
        }

        if (!isset($cfg) || !is_array($cfg)) {
            self::$checkStatusFlags[] = array_search(__FUNCTION__, self::$_boot_seq);
        }
       
        $checkRoute = self::$systemManager->checkSystemRoutes();

        if (!$checkRoute && !self::$systemManager->fileLockExistence()) {
            static::customRedirect('install');
        }

        //controllare request

        //unset all the globals that aren't php setted
        if (ini_get('register_globals')) {
            self::log("Unset all the globals that aren't php setted. (Emulate register global = off)");
            $allowed = ['GLOBALS', '_GET', '_POST', '_COOKIE', '_FILES', '_ENV', '_SERVER', '_REQUEST'];
            foreach ($allowed as $elem) {
                if (!isset($allowed[$elem])) {
                    unset($GLOBALS[$elem]);
                }
            }
        }

        //start timer
        self::log('Start timer and memory usage counter.');
        self::start();

        // detect globals overwrite (old php bug)
        self::log('Detect globals overwrite attempts.');
        $list = ['GLOBALS', '_GET', '_POST', '_COOKIE', '_FILES', '_SESSION'];
        foreach ($list as $elem) {
            if (isset($_REQUEST[$elem])) {
                exit('Request overwrite attempt detected');
            }
        }

        //include config
        self::log('Include configuration file.');

        $cfg = [];
        if (file_exists(dirname(__DIR__, 1). '/config.php')) {
            require dirname(__DIR__, 1). '/config.php';
            $cfg['configExists'] = true;
        }
        else {
            $cfg['configExists'] = false;
        }

        $GLOBALS['cfg'] = $cfg;
        if (empty($cfg)) {
            $cfg['prefix_fw'] = 'core';
            $cfg['prefix_lms'] = 'learning';
            $cfg['prefix_cms'] = 'cms';
            $cfg['prefix_scs'] = 'conference';
            $cfg['prefix_ecom'] = 'ecom';
            $cfg['prefix_crm'] = 'crm';
        }

        $GLOBALS['prefix_fw'] = $cfg['prefix_fw'];
        $GLOBALS['prefix_lms'] = $cfg['prefix_lms'];
        $GLOBALS['prefix_scs'] = $cfg['prefix_scs'];
        $GLOBALS['prefix_ecom'] = $cfg['prefix_ecom'];
        $GLOBALS['prefix_crm'] = $cfg['prefix_crm'];


        // setup some php.ini things
        $step_report[] = 'Setup some php.ini settings.';
        ini_set('arg_separator.output', '&amp;');

        // set default time zone TZ
        if (!isset($cfg['timezone'])) {    // timezone not speficied in config
            $cfg['timezone'] = @date_default_timezone_get();
        }
        if (!@date_default_timezone_set($cfg['timezone'])) {
            $cfg['timezone'] = @date_default_timezone_get();
            date_default_timezone_set(@date_default_timezone_get());
        }
        self::log('Time zone setted to TZ= ' . @date_default_timezone_get());

        if (!isset($cfg['do_debug'])) {
            $cfg['do_debug'] = false;
        }
        // debugging ?

        self::log(($cfg['do_debug'] ? 'Enable (set: E_ALL) ' : 'Disable (set: E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR)') . ' error reporting.');
        if ($cfg['do_debug']) {
            if (!in_array('debug_level', $cfg)) {
                $cfg['debug_level'] = 'all';
            }

            switch ($cfg['debug_level']) {
                case 'error':
                    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
                    @ini_set('display_startup_errors', 1);
                    break;
                case 'warning':
                    error_reporting(E_WARNING);
                    break;
                case 'notice':
                    error_reporting(E_NOTICE);
                    break;
                case 'deprecated':
                    error_reporting(E_DEPRECATED);
                    break;
                default:
                    error_reporting(E_ALL ^ E_DEPRECATED);
                    break;
            }

            @ini_set('display_errors', 1);
        } else {
            @error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR);
        }

        // todo: backward compatibility
        $GLOBALS['where_framework_relative'] = (!defined('CORE') ? _deeppath_ : '') . _folder_adm_;
        $GLOBALS['where_lms_relative'] = (!defined('LMS') ? _deeppath_ : '') . _folder_lms_;
        $GLOBALS['where_scs_relative'] = _deeppath_ . _folder_scs_;
        $GLOBALS['where_files_relative'] = _deeppath_ . _folder_files_;
        $GLOBALS['where_templates_relative'] = _deeppath_ . _folder_templates_;

        $GLOBALS['where_files_lms_relative'] = _deeppath_ . _folder_files_lms_;
        $GLOBALS['where_files_app_relative'] = _deeppath_ . _folder_files_app_;
        $GLOBALS['where_files_com_relative'] = _deeppath_ . _folder_files_com_;

        $GLOBALS['where_framework'] = _adm_;
        $GLOBALS['where_lms'] = _lms_;
        $GLOBALS['where_scs'] = _scs_;
        $GLOBALS['where_files'] = _files_;

        $GLOBALS['where_files_lms'] = _files_lms_;
        $GLOBALS['where_files_app'] = _files_app_;
        $GLOBALS['where_files_com'] = _files_com_;

        /*
        self::log( "Register error handler." );
        set_error_handler(array('Boot', 'error_catcher'));
        */
    }

    /**
     * - load the error handling lib
     * - load utility libraries (not all but some thing that help the programmers life).
     *
     * @return array
     */
    private static function utility()
    {
        self::log('Include autoload file.');
        require_once _base_ . '/lib/lib.autoload.php';

        self::log('Include log file.');
        require_once _base_ . '/lib/loggers/lib.logger.php';

        // config manager
        self::log('Include configuration file.');
        require_once _base_ . '/lib/lib.utils.php';

        // UTF8 Support
        \Patchwork\Utf8\Bootup::initAll();
        \Patchwork\Utf8\Bootup::filterRequestInputs();

        // filter input
        self::log('Load filter input library.');
        require_once _base_ . '/lib/lib.filterinput.php';

        // yui
        self::log('Load yui library.');
        require_once _base_ . '/lib/lib.yuilib.php';

        // mimetype
        self::log('Load mimetype library.');
        require_once _base_ . '/lib/lib.mimetype.php';

        require_once _lib_ . '/lib.acl.php';

        self::log('Load Calendar library.');
        require_once _lib_ . '/calendar/CalendarManager.php';
        require_once _lib_ . '/calendar/CalendarDataContainer.php';
        require_once _lib_ . '/calendar/CalendarMailer.php';
    }

    private static function domainAndTemplate()
    {
        //create the handler who will fix values in session
        $domainHandler = DomainHandler::getInstance();


        // template
        self::log('Load template library.');
        require_once _base_ . '/lib/lib.template.php';

        // i set mail later because it has a dependancy on li.template
        $domainHandler->attachDefaultMailer();
    }

    /**
     * - step to load the plugins.
     */
    private static function plugins()
    {
        self::log('Initialize plugins.');
        PluginManager::initialize();
    }

    /**
     * - step to load core and plugins' event listeners.
     */
    private static function hooks()
    {
        self::log('Prepare core listeners.');
        foreach (glob(_base_ . '/eventListeners/listeners.*.php') as $listeners) {
            include $listeners;
        }

        self::log("Prepare plugins' listeners.");
        PluginManager::hook();
    }

    /**
     * - load the appropiate database driver
     * - connect to the database
     * - load setting from database.
     * - check config
     *
     * @return array
     */
    private static function checkPlatform()
    {
        self::log('Load database funtion management library.');

        $cfg = null;
        $configExist = true;
        if (!file_exists(dirname(__DIR__, 1).'/config.php')) {
            $configExist = false;
        } else {
            require dirname(__DIR__, 1) .'/config.php';
        }

        if (!isset($cfg) || !is_array($cfg)) {
            $configExist = false;

            $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
            // i'm in installer
            if ($session->has('setValues')) {
                $values = $session->get('setValues');

                $cfg['db_type'] = 'mysqli';
                $cfg['db_user'] = $values['dbUser'];
                $cfg['db_pass'] = $values['dbPass'];
                $cfg['db_name'] = $values['dbName'];
                $cfg['db_host'] = $values['dbHost'];
            }
        }


        self::log('Load database funtion management library.');
        

        // utf8 support
        self::log('Connect to database.');
        \FormaLms\db\DbConn::getInstance(null, $cfg);

        $dbIsEmpty = true;
        if (\FormaLms\db\DbConn::$connected) {
            try {
                $dbIsEmpty = !(bool)sql_query("SELECT * FROM `core_setting`");
            } catch (\Exception $exception) {
            }
        }

        //controllare request
        $checkRoute = self::$systemManager->checkSystemRoutes();

        if ($dbIsEmpty) {
            self::$checkStatusFlags[] = array_search(__FUNCTION__, self::$_boot_seq);
        }
        if (!$checkRoute && !self::$systemManager->fileLockExistence()) {
            static::customRedirect('install');
        }
    }

    /**
     * - read the specific setting for the adm platform and for the current one.
     *
     * @return null
     */
    private static function loadSetting()
    {
        self::log(' Load settings from database.');
        Util::load_setting(FormaLms\lib\Get::cfg('prefix_fw') . '_setting', 'framework');

        if (FormaLms\lib\Get::sett('do_debug') === 'on') {
            @error_reporting(E_ALL);
        }
    }

    private static function request()
    {
        $request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
        if (!$request->hasSession()) {
            if (file_exists(dirname(__DIR__, 1). '/config.php')) {
                require dirname(__DIR__, 1). '/config.php';
            }
            $config = [];
            if (!empty($cfg)) {
                $config = $cfg['session'] ?? [];
            }
            FormaLms\lib\Session\SessionManager::getInstance()->initSession($config);

            $session = FormaLms\lib\Session\SessionManager::getInstance()->getSession();
            self::log(" Start session '" . $session->getName() . "'");
            $request->setSession($session);
        }
    }

    private static function sessionCheck()
    {
        if (FormaLms\lib\Session\SessionManager::getInstance()->isSessionExpired()) {
            $session = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest()->getSession();
            $session->invalidate();
            $session->save();
            \Util::jump_to(FormaLms\lib\Get::rel_path('base') . '/index.php?msg=103');
        }
    }

    /**
     * - create the user representative object, load anonymoous data if needed or load the info of the
     *        user logged into the session (also manage the user login here ? will be usefull for kerberos
     *        and similar approach)
     * - load user personal data (language, date/time setting, template preference)
     * - load user system data (role).
     *
     * @return array
     */
    private static function user()
    {
        $session = FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        self::log("Load user from session '" . $session->getName() . "'");

        // load current user from session

        // ip coerency check
        self::log('Ip coerency check.');
        if (FormaLms\lib\Get::sett('session_ip_control', 'on') == 'on') {
            $fallbackIp = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '::1';
            $ip = (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $fallbackIp;
            if (strpos($ip, ',') !== false) {
                $ip = substr($ip, 0, strpos($ip, ','));
            }
            if (\FormaLms\lib\FormaUser::getCurrentUser()->isLoggedIn() && (\FormaLms\lib\FormaUser::getCurrentUser()->getLogIp() != $ip)) {
                \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->invalidate();
                Util::jump_to(FormaLms\lib\Get::rel_path('base') . '/index.php?msg=104');
                //Util::fatal("logip: ".\FormaLms\lib\FormaUser::getCurrentUser()->getLogIp()."<br/>"."addr: ".$_SERVER['REMOTE_ADDR']."<br/>".'Ip incoherent!');
                //unlog the user
                exit();
            }
        }
        // Generate a session signature or regenerate it if needed
        self::log('Generating session signature');
        Util::generateSignature();
    }

    private static function anonFilteringInput()
    {
        $step_report = [];

        // Convert ' and " (quote or unquote)
        self::log('Sanitize the input.');

        $filter_input = new FilterInput();
        $filter_input->tool = FormaLms\lib\Get::cfg('filter_tool', 'htmlpurifier');

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        // Whitelist some tags if we're a teacher in a course:
        if ($session->has('idCourse') && $session->get('levelCourse') >= 6) {
            $filter_input->appendToWhitelist([
                'tag' => ['object', 'param'],
                'attrib' => [
                    'object.data', 'object.type', 'object.width',
                    'object.height', 'param.name', 'param.value',
                ],
            ]);
        }

        $filter_input->sanitize();
    }

    private static function filteringInput()
    {
        $step_report = [];

        // todo: check if we can do in other way the same thing
        // save login password from modification
        $ldap_used = FormaLms\lib\Get::sett('ldap_used');
        if (($ldap_used === 'on') && isset($_POST['modname']) && ($_POST['modname'] === 'login') && isset($_POST['passIns'])) {
            $password_login = $_POST['passIns'];
        }

        // Convert ' and " (quote or unquote)
        self::log('Sanitize the input.');

        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() == ADMIN_GROUP_GODADMIN) {
            $filter_input = new FilterInput();
            $filter_input->tool = 'none';
            $filter_input->sanitize();
        } else {
            $filter_input = new FilterInput();
            $filter_input->tool = FormaLms\lib\Get::cfg('filter_tool', 'htmlpurifier');
            $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
            // Whitelist some tags if we're a teacher in a course:
            if ($session->has('idCourse') && $session->get('levelCourse') >= 6) {
                $filter_input->appendToWhitelist([
                    'tag' => ['object', 'param'],
                    'attrib' => [
                        'object.data', 'object.type', 'object.width',
                        'object.height', 'param.name', 'param.value',
                    ],
                ]);
            }

            $filter_input->sanitize();
        }
        if (($ldap_used === 'on') && isset($_POST['modname']) && ($_POST['modname'] === 'login') && isset($_POST['passIns'])) {
            $_POST['passIns'] = \voku\helper\UTF8::clean(stripslashes($password_login));
        }
        $request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();

        if ((!defined('IS_API') && !defined('IS_PAYPAL') && ($request->isMethod('post') || defined('IS_AJAX'))) && !self::$systemManager->checkSystemRoutes()) {
            // If this is a post or a ajax request then we must have a signature attached
            Util::checkSignature();
        }
    }

    /**
     * - load language from source
     * - set the system language looking at ( user preference, browser detect, user force ).
     *
     * @return array
     */
    private static function language()
    {
        self::log('Loading session language functions');

        require_once \FormaLms\lib\Forma::inc(_i18n_ . '/lib.lang.php');
        $sop = FormaLms\lib\Get::req('sop', DOTY_ALPHANUM, false);
        if (!$sop) {
            $sop = FormaLms\lib\Get::req('special', DOTY_ALPHANUM, false);
        }
        switch ($sop) {
            case 'changelang':
                $new_lang = FormaLms\lib\Get::req('new_lang', DOTY_ALPHANUM, false);

                self::log("Sop 'changelang' intercepted, changing lang to : $new_lang");
                Lang::set($new_lang, isset($_GET['logout']));
                break;
        }

        //$glang =& FormaLanguage::createInstance( 'standard', 'framework');
        //$glang->setGlobal();
    }

    /**
     * - load the user preference about the date and time display and GMT.
     *
     * @return array
     */
    private static function dateTime()
    {
        self::log('Loading regional settings functions');

        // todo : change this class
        require_once _i18n_ . '/lib.format.php';
        Format::instance();
    }

    /**
     * - load the library needed for image / css / skin retrivial process
     * - detect the user associated template ( webdomain | admin setting | user preference ).
     *
     * @return array
     */
    private static function template()
    {
        self::log('Include layout manager file.');
        require_once _lib_ . '/layout/lib.layout.php';
    }

    /**
     * - load the page writer, that is a output cache mechanism that allow the use of different
     * - page-zone to be cached (head, menu, content).
     *
     * @return null
     */
    private static function loadPageWriter()
    {
        self::log('Include page writer manager file.');
        require_once _base_ . '/lib/lib.pagewriter.php';
    }

    public static function start()
    {
        list($usec, $sec) = explode(' ', microtime());
        $GLOBALS['start'] = [
            'time' => ((float)$usec + (float)$sec),
            'memory' => function_exists('memory_get_usage') ? memory_get_usage() : 0,
        ];
    }

    public static function current_time()
    {
        list($usec, $sec) = explode(' ', microtime());
        $now = ((float)$usec + (float)$sec);

        return $now - $GLOBALS['start']['time'];
    }

    public static function current_memory()
    {
        $current = function_exists('memory_get_usage') ? memory_get_usage() : 0;

        return $current - $GLOBALS['start']['memory'];
    }

    public static function log($str)
    {
        self::$log_array[] = $str;
    }

    public static function get_log($as_string = false)
    {
        return $as_string ? implode("\n", self::$log_array) : self::$log_array;
    }

    public static function error_catcher($code, $message, $file, $line)
    {
        if ($code & error_reporting()) {
            restore_error_handler();

            echo "<h1 style=\"font-size:16px;font-family:Arial;\">PHP Error [$code]</h1>\n"
                . "<p style=\"font-size:12px;font-family:Arial;\">$message ($file:$line)</p>\n";

            if (isset($_SERVER['REQUEST_URI'])) {
                echo '<p style="font-size:12px;font-family:Arial;">REQUEST_URI = ' . $_SERVER['REQUEST_URI'] . '</p>';
            }

            echo '<table style="margin:0; padding:0; font-size:12px;font-family: Arial; border-spacing:0;">'
                . '<tr>'
                . '<td style="background:#eeeeee;"></td>'
                . '<td colspan="2" style="background:#eeeeee;color:gray;padding-left:10px;border-bottom:1px solid gray;">'
                . 'Stack trace'
                . '</td>'
                . '</tr>';

            $trace = debug_backtrace();
            foreach ($trace as $i => $t) {
                if (!isset($t['file'])) {
                    $t['file'] = 'unknown';
                }
                if (!isset($t['line'])) {
                    $t['line'] = 0;
                }
                if (!isset($t['function'])) {
                    $t['function'] = 'unknown';
                }

                echo '<tr><td style="width:5px;background:#eeeeee;color:gray;padding:3px 5px 3px 10px;border-right:1px solid gray;">' . $i . '</td>'
                    . '<td style="' . ($i % 2 ? 'background:#F7F7F7;' : '') . 'padding:3px 10px 3px 10px;">'
                    . "{$t['file']} ({$t['line']}) "
                    . '</td>'
                    . '<td style="' . ($i % 2 ? 'background:#F7F7F7;' : '') . 'padding:3px 10px 3px 10px;"><i>';
                if (isset($t['object']) && is_object($t['object'])) {
                    echo get_class($t['object']) . '->';
                }
                if ($t['function'] !== 'error_catcher') {
                    echo "{$t['function']}</i>()<i>";
                }
                echo '</i></td></tr>';
            }
            echo '</table>';
            exit();
        }
    }


    public static function checkSystemStatus()
    {
        $request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();

        if (count(self::$checkStatusFlags) && self::$systemManager->fileLockExistence() && !self::$systemManager->checkSystemRoutes(true) && !defined('IS_AJAX')) {
            $params['errorStatus'] = base64_encode(implode("_", array_unique(self::$checkStatusFlags)));
            static::customRedirect('checkSystemStatus', $params);
        }
    }


    public static function customRedirect($route, $params = [])
    {
        $baseRoute = FormaLms\lib\Get::rel_path('base');
        if(substr($baseRoute, -1) === '/') {
            $baseRoute = substr($baseRoute, 0, -1);
        }
        $sistemPrefix = '/index.php?r=adm/system/';
        $baseRoute .= ($sistemPrefix . $route);

        if (count($params)) {
            $baseRoute .=  '&';
            foreach ($params as $key => $param) {
                $baseRoute .= $key . '=' . $param;
            }
        }

        header('Location: ' . $baseRoute);
    }

    public static function checkPhpVersion()
    {

        if (\FormaLms\lib\Version\VersionChecker::comparePhpVersion()) {
            self::$checkStatusFlags[] = array_search(__FUNCTION__, self::$_boot_seq);
        }
    }
}
