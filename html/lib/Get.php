<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace FormaLms\lib;

use FormaLms\appCore\Template\Services\ClientService;
use FormaLms\lib\Serializer\FormaSerializer;

defined('IN_FORMA') or exit('Direct access is forbidden.');

/*
 * Utils file, basic function for the nowadays use
 *
 * In this file you can find some usefull functions fot the html generation
 * and for recovering some common ifnormation about the user, the script and
 * so on.
 */

/*
 * docebo constant for var type
 */
define('DOTY_INT', 0);
define('DOTY_FLOAT', 1);
define('DOTY_DOUBLE', 2);
define('DOTY_STRING', 3);
define('DOTY_MIXED', 4);
define('DOTY_JSONDECODE', 5);
define('DOTY_JSONENCODE', 6);
define('DOTY_ALPHANUM', 7);
define('DOTY_NUMLIST', 8);
define('DOTY_BOOL', 9);
define('DOTY_MVC', 10);
define('DOTY_URL', 11);

class Get
{

    public const coreFolders = [
        'appLms',
        'appCore',
        'appScs',
        'api',
    ];

    /**
     * Import var from GET and POST, if the var exists in POST and GET, the post value will be preferred.
     *
     * @param string $name the var to import
     * @param int $typeof the type of the variable (used for casting)
     * @param mixed $default the default value
     * @param mixed $only_from false if can take from both post and get; else get or post
     *                          to force the reading from one method
     *
     * @return mixed return the var founded in post/get or the default value if the var doesn't exixst
     * @author Pirovano Fabio
     *
     */
    public static function req($var_name, $typeof = DOTY_MIXED, $default_value = '', $only_from = false)
    {
        $value = $default_value;
        if (empty($only_from)) {
            if (isset($_POST[$var_name])) {
                $value = $_POST[$var_name];
            } elseif (isset($_GET[$var_name])) {
                $value = $_GET[$var_name];
            } elseif (isset($_REQUEST[$var_name])) {
                $value = $_REQUEST[$var_name];
            }
        } elseif ($only_from === 'post' && isset($_POST[$var_name])) {
            $value = $_POST[$var_name];
        } elseif ($only_from === 'get' && isset($_GET[$var_name])) {
            $value = $_GET[$var_name];
        } elseif ($only_from === 'request' && isset($_REQUEST[$var_name])) {
            $value = $_REQUEST[$var_name];
        }

        return self::filter($value, $typeof);
    }

    /**
     * Filter the input value based on the specified type.
     *
     * @param mixed $value The value to be filtered.
     * @param int $typeof The type of filtering to be performed.
     *                    Possible values: DOTY_INT, DOTY_DOUBLE, DOTY_FLOAT,
     *                    DOTY_STRING, DOTY_ALPHANUM, DOTY_NUMLIST,
     *                    DOTY_JSONDECODE, DOTY_JSONENCODE, DOTY_BOOL, DOTY_MVC,
     *                    DOTY_URL, DOTY_MIXED.
     * @return mixed The filtered value based on the specified type.
     */
    public static function filter($value, $typeof)
    {
        switch ($typeof) {
            case DOTY_INT:
                $value = (int)$value;
                break;
            case DOTY_DOUBLE:
            case DOTY_FLOAT:
                $value = (float)$value;
                break;
            case DOTY_STRING:
                $value = strip_tags($value ?? '');
                break;
            case DOTY_ALPHANUM:
                $value = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $value);
                break;
            case DOTY_NUMLIST:
                $value = preg_replace('/[^0-9\-\_,]+/', '', $value);
                break;

            case DOTY_JSONDECODE:
                $value = FormaSerializer::getInstance()->decode($value, 'json');
                break;
            case DOTY_JSONENCODE:
                $value = FormaSerializer::getInstance()->encode($value, 'json');

                break;
            case DOTY_BOOL:
                $value = ($value ? true : false);
                break;
            case DOTY_MVC:
                $value = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $value);
                if ($value[0] === '/') {
                    $value = '';
                }

                break;
            case DOTY_URL:
                $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
                $value = preg_replace('/[<>\'\"\(\)\[\]]/', '', $value);
                $value = str_replace(['<', '>', '\'', '\"', ')', '('], '', $value);

                break;
            case DOTY_MIXED:
            default:
        }

        return $value;
    }

    /**
     * Retrieves a value from the $_GET superglobal array.
     *
     * @param string $var_name The name of the variable to retrieve from the $_GET array.
     * @param int $typeof [optional] The type of the variable being retrieved. Defaults to DOTY_MIXED.
     * @param mixed $default_value [optional] The default value to return if the variable is not found in the $_GET array. Defaults to an empty string.
     * @return mixed The value of the variable specified by $var_name in the $_GET array, or $default_value if the variable is not found.
     */
    public static function gReq($var_name, $typeof = DOTY_MIXED, $default_value = '')
    {
        return self::req($var_name, $typeof, $default_value, 'get');
    }

    /**
     * @param string $var_name the name of the variable to retrieve from POST request
     * @param int $typeof the data type of the variable (optional, default: DOTY_MIXED)
     * @param mixed $default_value the default value to return if the variable is not found (optional, default: '')
     * @return mixed the value of the variable if found, otherwise the default value
     */
    public static function pReq($var_name, $typeof = DOTY_MIXED, $default_value = '')
    {
        return self::req($var_name, $typeof, $default_value, 'post');
    }

    /**
     * Return the value of a configuration.
     *
     * @param string $cfg_name The configuration name
     * @param mixed $default The default value return if the configuration is not found or not set
     *
     * @return mixed The value of the configuration param
     */
    public static function cfg($cfg_name, $default = false)
    {
        $value = $GLOBALS['cfg'][$cfg_name] ?? $default;

        return $value;
    }

    /**
     * Return the value of a plugin configuration.
     *
     * @param string $plugin_name The plugin name
     * @param string $cfg_name The configuration name
     * @param mixed $default The default value return if the configuration is not found or not set
     *
     * @return mixed The value of the configuration param
     */
    public static function pcfg($plugin_name, $cfg_name, $default = false)
    {
        if (!isset($GLOBALS['cfg'][$plugin_name][$cfg_name])) {
            $value = $default;
        }
        $value = $GLOBALS['cfg'][$plugin_name][$cfg_name];

        return $value;
    }

    /**
     * Return the value of a pl atform setting.
     *
     * @param string $sett_name
     * @param string $default
     *
     * @return mixed the value of the setting or the default value
     */
    public static function sett($sett_name, $fallback = false)
    {
        $result = $fallback;

        $platform = 'framework';

        if (array_key_exists($sett_name, $GLOBALS[$platform] ?? [])) {
            $result = $GLOBALS[$platform][$sett_name];
        } else {
            $notLoadedParams = static::_loadOption($GLOBALS[$platform] ?? []);
            $result = array_key_exists($sett_name, (array)$notLoadedParams) ? $notLoadedParams[$sett_name] : $fallback;
        }

        $eventData = \Events::trigger('core.settings.read', ['key' => $sett_name]);

        if (array_key_exists('value', $eventData)) {
            $result = $eventData['value'];
        }

        return $result;
    }

    /**
     * Loads the option values from the 'core_setting' table.
     *
     * @param array $exclusions An array of parameter names to exclude from the result
     * @return array An associative array containing the loaded options
     */
    public static function _loadOption($exclusions = []): array
    {
        $result = [];
        $basequery = '
		SELECT param_name, param_value, value_type, max_size
		FROM `core_setting` WHERE 1 ';

        if (count($exclusions)) {
            $basequery .= ' AND param_name NOT IN (' . sprintf("'%s'", implode("','", array_keys($exclusions))) . ') ';
        }

        $basequery .= 'ORDER BY sequence';
        $reSetting = sql_query($basequery);

        while (list($var_name, $var_value, $value_type) = sql_fetch_row($reSetting)) {
            switch ($value_type) {
                //if is int cast it
                case 'int':
                    $result[$var_name] = (int)$var_value;

                    break;
                //if is enum switch value to on or off
                case 'enum':
                    if ($var_value == 'on') {
                        $result[$var_name] = 'on';
                    } else {
                        $result[$var_name] = 'off';
                    }

                    break;
                //else simple assignament
                default:
                    $result[$var_name] = $var_value;
            }
        }

        return $result;
    }

    /**
     * Return the current platform code.
     *
     * @return string the platform path
     */
    public static function cur_plat()
    {
        // where are we ?
        if (defined('LMS')) {
            return 'lms';
        } elseif (defined('ECOM')) {
            return 'ecom';
        } elseif (defined('CRM')) {
            return 'crm';
        }

        return 'framework';
    }


    public static function getBaseUrl($onlyBasePath = false)
    {

        $request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();

        $possiblePhpEndpoints = [];
        $path = '';
        $basePath = '/';
        $requestUri = '';
        try {
            $basePath = $request->getSchemeAndHttpHost();
            $requestUri = $request->getBaseUrl();
        } catch (\Error $e) {
            // non deve mai andare qui, ma ci passa se vengono chiamate shell exec come le migrate
        }


        if (!$onlyBasePath) {
            preg_match('/\/(.*?).php/', $requestUri, $match);
            if (!empty($match)) {
                $explodedMatch = explode('/', $match[0]);
                $possiblePhpEndpoint = '';
                foreach ($explodedMatch as $item) {
                    if (!empty($item) && str_contains($item, '.php')) {
                        $possiblePhpEndpoint .= str_replace(self::coreFolders, '', $item);
                    }
                }

                $possiblePhpEndpoints[] = $possiblePhpEndpoint;
            }

            $possiblePhpEndpoints[] = '/?';
            $possiblePhpEndpoints[] = '/api';

            $requestUriArray = [];

            foreach ($possiblePhpEndpoints as $possiblePhpEndpoint) {
                if (str_contains($requestUri, $possiblePhpEndpoint)) {
                    $requestUriArray = explode($possiblePhpEndpoint, $requestUri);
                    $requestUriArray = explode('/', $requestUriArray[0]);
                    break;
                }
            }

            if (empty($requestUriArray) && !empty($requestUri)) {
                $requestUriArray = explode('/', $requestUri);
            }

            foreach ($requestUriArray as $requestUriItem) {
                if (!empty($requestUriItem) && !in_array($requestUriItem, self::coreFolders, true)) {
                    $path .= sprintf('/%s', $requestUriItem);
                }
            }

            $path = $basePath . $path;
        }

        return $path != '' ? $path : $basePath;
    }

    /**
     * Return the calculated relative path form the current zone (platform) to the requested one.
     *
     * @param string $item (base, lms, ...)
     *
     * @return string the relative path
     */
    public static function rel_path($to = false)
    {
        // where are we ?
        if ($to === false) {
            if (defined('CORE')) {
                $to = 'adm';
            } elseif (defined('LMS')) {
                $to = 'lms';
            }
        }
        if (!defined('_' . $to . '_')) {
            $to = 'base';
        }
        $path = _deeppath_
            . str_replace(_base_, '.', constant('_' . $to . '_'));

        return str_replace(['//', '\\/', '/./'], '/', $path);
    }

    /**
     * Return the absolute path of the platform.
     *
     * @param string $item (base, lms, ...)
     *
     * @return string the absolute path
     */
    public static function abs_path($to = false)
    {
        $folder = '';
        if ($to === false) {
            if (defined('CORE')) {
                $folder = _folder_adm_;
            } elseif (defined('LMS')) {
                $folder = _folder_lms_;
            } elseif (defined('SCS')) {
                $folder = _folder_scs_;
            }
        } else {
            switch (strtolower($to)) {
                case 'adm':
                    $folder = _folder_adm_;
                    break;
                case 'lms':
                    $folder = _folder_lms_;
                    break;
                case 'scs':
                    $folder = _folder_scs_;
                    break;
            }
        }
        $folder = str_replace(['//', '\\/', '/./'], '/', $folder);
        $path = self::site_url(true) . $folder;

        return rtrim($path, '/') . '/';
    }

    /**
     * Return the calculated relative path form the current zone (platform) to the requested one.
     *
     * @param string $item (base, lms, ...)
     *
     * @return string the relative path
     */
    public static function tmpl_path($item = false)
    {
        if ($item === false) {
            $platform = self::cur_plat();
        } else {
            $platform = $item;
        }
        $path = $GLOBALS['where_templates_relative'] . '/' . getTemplate() . '/';

        return str_replace('/./', '/', $path);
    }

    /**
     * Return html code and resolved path for an image.
     *
     * @param string $src the img[src] attribute, the path can be absolute or relative to the images/ folder of the current template
     * @param string $alt the img[alt] attribute
     * @param string $class_name the img[class] attribute
     * @param string $extra some extra code that you need to add into the image
     * @param bool $is_abspath if true the src is assumed absolute, if false the relative path is added to the src attr
     *
     * @return string the html code (sample <img ... />)
     */
    public static function img($src, $alt = false, $class_name = false, $extra = false, $is_abspath = false)
    {
        // where are we ?
        if (!$is_abspath) {
            $src = self::tmpl_path('base') . 'images/' . $src;
        }

        return '<img src="' . $src . '" '
            . 'alt="' . ($alt ?: substr($src, 0, -4)) . '" '
            . 'title="' . ($alt ?: substr($src, 0, -4)) . '" '
            . ($class_name != false ? 'class="' . $class_name . '" ' : '')
            . ($extra != false ? $extra . ' ' : '')
            . '/>';
    }

    /**
     * Return html code.
     */
    public static function sprite($class, $name, $title = false)
    {
        // where are we ?
        if (!$title) {
            $title = $name;
        }

        return '<span class="ico-sprite ' . $class . '" title="' . $title . '"><span>' . $name . '</span></span>';
    }

    /**
     * Return html code and for a.
     */
    public static function sprite_link($class, $href, $name, $title = false)
    {
        // where are we ?
        if (!$title) {
            $title = $name;
        }

        return '<a class="ico-sprite ' . $class . '" href="' . $href . '" title="' . $title . '"><span>' . $name . '</span></a>';
    }

    /**
     * Build an html for an image encapsulated into a link.
     *
     * @param string $url the url for the a[href]
     * @param string $title the title for the a[title]
     * @param string $src the img[src]
     * @param string $alt the img[alt]
     * @param <array> $extra the content of the 'link' key is used as extra in the a element if specified, the 'img' key content is used into the img element
     *
     * @return string html code (sample: <a ...><img ...></a> )
     */
    public static function link_img($url, $title, $src, $alt, $extra = [])
    {
        // Define base path in a variable for clarity
        $templateBasePath = $GLOBALS['where_templates_relative'] . '/standard/images/';
        $src = $templateBasePath . $src;

        // Simplify ternary operations
        $linkExtra = $extra['link'] ?? '';
        $imgExtra = $extra['img'] ?? '';

        // Set default alt text if not provided
        $altText = $alt ?? substr($src, 0, -4);

        // Use string interpolation for better readability and performance
        return "<a href=\"{$url}\" title=\"{$title}\" {$linkExtra}>
              <img src=\"{$src}\" alt=\"{$altText}\" title=\"{$title}\" {$imgExtra}/>
            </a>\n";
    }


    /**
     * This function try to evaluate the current site address.
     *
     * @return string (i.e. http://localhost)
     */
    public static function site_url($disableUrlSetting = true, $onlyBaseUrl = false)
    {
        if (!($url = self::sett('url')) || $disableUrlSetting) {
            $url = Get::getBaseUrl($onlyBaseUrl);
        }

        return rtrim($url, '/') . '/';
    }

    public static function home_page_req()
    {
        $home_page = self::sett('home_page');

        if (!$home_page) {
            $home_page_option = self::sett('home_page_option');
            switch ($home_page_option) {
                case 'my_courses':
                    $home_page = 'lms/mycourses/home';
                    break;
                case 'dashboard':
                    $home_page = 'lms/dashboard/show';
                    break;
                case 'catalogue':
                default:
                    $home_page = 'lms/catalog/show';
                    break;
            }
        }

        return $home_page;
    }

    public static function home_page_query()
    {
        $req = self::home_page_req();
        $query = "r=$req&sop=unregistercourse";

        return $query;
    }

    public static function home_page_abs_path()
    {
        $home_page = self::abs_path('lms') . '?' . self::home_page_query();

        return $home_page;
    }

    public static function home_page_rel_path()
    {
        $home_page = self::rel_path('lms') . '?' . self::home_page_query();

        return $home_page;
    }

    /**
     * Return the scheme to use.
     *
     * @return string scheme
     */
    public static function scheme()
    {
        if ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ||
            (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on')
        ) {
            return 'https';
        } else {
            return 'http';
        }
    }

    /**
     * Return the server name.
     *
     * @return string server_name
     */
    public static function server_name()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        } else {
            return $_SERVER['SERVER_NAME'];
        }
    }

    /**
     * Return installation subdirectory.
     *
     * @return string subdirectory
     */
    public static function subdirectory()
    {
        $script_arr = explode('/', ltrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
        $deeppath_arr = explode('/', trim(_deeppath_, '/'));
        foreach ($deeppath_arr as $value) {
            switch ($value) {
                case '..':
                    array_pop($script_arr);
                    break;
                case '.':
                    break;
                default:
                    $script_arr[] = $value;
                    break;
            }
        }

        return implode('/', $script_arr);
    }

    /**
     * Get the path of std images.
     *
     * @return string
     */
    public static function path_image()
    {
        return self::tmpl_path() . 'images/';
    }

    /**
     * Draw the page title of a mvc or module.
     *
     * @param array $text_array the title of the page or an array with  the breadcrmbs elements (key => value)
     *                          if the key is a string it will be userd as a link
     * @param bool $echo if true the output will be automaticaly echoed
     *
     * @return string
     */
    public static function title($text_array, $echo = true)
    {
        $is_first = true;
        if (!is_array($text_array)) {
            $text_array = [$text_array];
        }

        $html = '<div class="title_block">' . "\n";
        foreach ($text_array as $link => $title) {
            if ($is_first) {
                $is_first = false;
                // Retrive, if exists, name customized by the user for the module
                /*if(!$ignore_glob && isset($GLOBALS['module_assigned_name'][$GLOBALS['modname']]) && $GLOBALS['module_assigned_name'][$GLOBALS['modname']] != '') {
                    $title = $GLOBALS['module_assigned_name'][$GLOBALS['modname']];
                }*/
                // Area title
                $html .= '<h1>'
                    . (!is_int($link) ? '<a href="' . $link . '">' : '')
                    . $title
                    . (!is_int($link) ? '</a>' : '')
                    . '</h1>' . "\n";

                if (!defined('IS_AJAX')) {
                    $GLOBALS['page']->add('<li><a href="#main_area_title">' . \Lang::t('_JUMP_TO', 'standard') . ' ' . $title . '</a></li>', 'blind_navigation');
                }

                // Init navigation
                if (count($text_array) > 1) {
                    $html .= '<ul class="navigation">';
                    //	if(!is_int($link)) {
                    //		$html .= '<li><a href="'.$link.'">'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</a></li>';
                    //	} else $html .= '<li>'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</li>';
                }
            } else {
                if (is_int($link)) {
                    $html .= '<li> &rsaquo; ' . $title . '</li>';
                } else {
                    $html .= ' <li> &rsaquo; <a href="' . $link . '">' . $title . '</a></li>';
                }
            }
        }
        if (count($text_array) > 1) {
            $html .= '</ul>' . "\n";
        }
        $html .= '</div>' . "\n";
        if ($echo) {
            echo $html;
        }

        return $html;
    }

    /**
     * Return the user ip, also check for proxy http header.
     *
     * @return string ip (i.e. 127.0.0.1)
     */
    public static function user_ip()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'];
    }

    /**
     * This funciont try to find the user SO and return it, if the so isn't in the internal list return 'unknown'.
     *
     * @return string (ie. windows)
     */
    public static function user_os()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $known_os_arr = explode(' ', 'linux macos sunos bsd qnx solaris irix aix unix amiga os/2 beos windows');
        foreach ($known_os_arr as $os) {
            if (strpos($agent, (string)$os) !== false) {
                return $os;
            }
        }

        return 'unknown';
    }

    /**
     * This funciont try to find the user browser and return it, if the browser isn't in the internal list return 'unknown'.
     *
     * @return string (ie. firefox)
     */
    public static function user_agent()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $known_browser_arr = explode(' ', 'firefox netscape konqueror epiphany mozilla safari opera mosaic lynx amaya omniweb msie chrome iphone');
        $required = [
            'firefox' => ['gecko', 'mozilla', 'firefox'],
            'netscape' => ['gecko', 'mozilla', 'netscape'],
            'konqueror' => ['gecko', 'mozilla', 'konqueror'],
            'epiphany' => ['gecko', 'mozilla', 'epiphany'],
            'mozilla' => ['gecko', 'mozilla'],
        ];

        $founded = false;
        foreach ($known_browser_arr as $browser) {
            if (strpos($agent, $browser) !== false) {
                $founded = $browser;
            }
        }
        // the browser is not in the list
        if (!$founded) {
            return 'unknown';
        }

        // founded
        if (!isset($required[$founded])) {
            return $founded;
        }

        // more distinction needed
        $refined = false;
        foreach ($required[$founded] as $browser) {
            if (strpos($agent, $browser) !== false) {
                $refined = $browser;
            }
        }
        if (!$refined) {
            return $refined;
        }

        return 'unknown';
    }

    /**
     * Retrieves the accepted languages from the HTTP_ACCEPT_LANGUAGE header.
     *
     * @param bool $main_only Determines whether to return only the main language or all accepted languages.
     * @return array|string The array of accepted languages or the main language if $main_only is set to true.
     */
    public static function user_acceptlang($main_only = true)
    {
        $lang_list = [];
        $main_langs = explode(',', array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
        foreach ($main_langs as $lang_set) {
            $single_lang = explode(';', $lang_set);
            foreach ($single_lang as $i => $lang_code) {
                //discard q=N entries
                if (strpos($lang_code, 'q=') === false) {
                    $lang_list[] = addslashes($lang_code);
                    if ($main_only) {
                        return $lang_code;
                    }
                }
            }
        } // foreach

        return $lang_list;
    }

    /**
     * Determines if the user is a bot based on the user agent string.
     *
     * @return int Returns 1 if the user is a bot, and 0 otherwise.
     */
    public static function user_is_bot()
    {
        $to_test = [
            'googlebot',        // Google
            'scooter',            // Altavista
            'altavista',        // Altavista UK
            'webcrawler',        // AllTheWeb
            'architextspider',    // Excite
            'slurp',            // Inktomi
            'iltrovatore',        // Il Trovatore
            'ultraseek',        // Infoseek
            'lookbot',            // look.com
            'mantraagent',        // looksmart.com
            'lycos_spider',        // Lycos
            'msnbot',            // Msn Search (the hated)
            'shinyseek',        // ShinySeek
            'robozilla',            // dmoz.org
        ];
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        foreach ($to_test as $botname) {
            if (strpos($agent, $botname) !== false) {
                return 1;
            }
        }

        return 0;
    }

    public static function accessibilty()
    {
        return self::sett('boh');
    }

    /**
     * Get the size of a file
     *
     * @param string $file_path The path to the file
     * @return int|false The size of the file in bytes, or false if an error occurred
     */
    public static function file_size($file_path)
    {
        return @filesize($file_path);
    }

    /**
     * Calculate the size of a directory recursively.
     *
     * @param string $path The path to the directory.
     * @return int The size of the directory in bytes.
     */
    public static function dir_size($path)
    {
        if (!is_dir($path)) {
            return self::file_size($path);
        }
        if ($scan_dir = opendir($path)) {
            $size = 0;
            while ($file = readdir($scan_dir)) {
                if ($file != '..' && $file != '.' && $file != '') {
                    $size += self::dir_size($path . '/' . $file);
                }
            }
        }

        closedir($scan_dir);

        return $size;
    }

    /**
     * @param string $message_text The text for the alert
     * @param string $message_type The type of the message to append
     *
     * @return string The html code for the alert
     */
    public static function append_alert($message_text, $message_type = false)
    {
        $class_name = 'notice_display';
        switch ($message_type) {
            case 'notice':
                $class_name .= ' notice_display_notice';

                break;
            case 'success':
                $class_name .= ' notice_display_success';

                break;
            case 'failure':
                $class_name .= ' notice_display_failure';

                break;
            case 'error':
                $class_name .= ' notice_display_error';

                break;
            default:
                $class_name .= ' notice_display_default';

                break;
        }
        $html = '<div class="' . $class_name . '">'
            . '<p>' . $message_text
            . '<a class="close_link" href="javascript:void(0)" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);">'
            . 'close</a>'
            . '</p>'
            . '</div>';

        return $html;
    }

    /**
     * @param string $type a type specified for now only filterCourse
     */
    public static function getRegexUrlMatches(string $type): array
    {
        $results = [];

        switch ($type) {
            case 'filterCourse':
                $pattern = '/filter_+[^=;]+/';
                break;

            default:
                return $results;
                break;
        }

        preg_match($pattern, http_build_query($_REQUEST), $results);

        return $results;
    }
} // end of class Get
