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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class FilterInput
{
    public $tool = '';

    /**
     * @var HTMLPurifier
     */
    protected $htmlpurifier = null;

    protected $use_xss_clean = true;

    protected $session_whitelist = ['tag' => [], 'attrib' => []];

    public function __construct()
    {
    }

    /**
     * The function that follow are a modified version of the Khoana Input library.
     *
     * @author     Kohana Team
     * @copyright  (c) 2007-2008 Kohana Team
     * @license    http://kohanaphp.com/license.html
     */
    public function sanitize()
    {
        // load the tool that we want to use in the xss filtering process
        $this->loadTool();

        if (is_array($_GET) and count($_GET) > 0) {
            $_GET = $this->clean_input_data($_GET);
        }
        if (is_array($_POST) and count($_POST) > 0) {
            $_POST = $this->clean_input_data($_POST);
        }
        if (is_array($_COOKIE) and count($_COOKIE) > 0) {
            $_COOKIE = $this->clean_input_data($_COOKIE);
        }
        if (is_array($_FILES) and count($_FILES) > 0) {
            //$_FILES = $this->clean_input_data($_FILES, true);
        }

        $_SERVER['REQUEST_URI'] = html_entity_decode($this->clean_input_data(urldecode($_SERVER['REQUEST_URI'])));
        $_SERVER['QUERY_STRING'] = html_entity_decode($this->clean_input_data(urldecode($_SERVER['QUERY_STRING'])));
    }

    protected function loadTool()
    {
        // load the tool that we want to use in the xss filtering process
        switch ($this->tool) {
            case 'none' :
                    //only used for a god admin
                ;
                break;
            case 'htmlpurifier' :
                    //htmlpurifier  is the best class in order to clean and validate the user input
                    //his major drawback is that it requires a lot of resource to operate, so is better
                    //to use it only if really needed

                    $config = HTMLPurifier_Config::createDefault();

                    if (count($this->getWhitelist('tag')) > 0) {
                        $default = $this->getHtmlPurifierDefaultElements(); // this has to be the first thing to be setup!
                        $allowed = array_unique(array_merge($default, $this->getWhitelist('tag')));
                        $config->set('HTML.AllowedElements', $allowed);
                        if (in_array('object', $this->getWhitelist('tag'))) {
                            $config->set('HTML.SafeObject', true);
                            $config->set('Output.FlashCompat', true);
                        }
                    }

                    if (count($this->getWhitelist('attrib')) > 0) {
                        $default = $this->getHtmlPurifierDefaultAttributes();
                        $allowed = array_unique(array_merge($default, $this->getWhitelist('attrib')));
                        $config->set('HTML.AllowedAttributes', $allowed);
                    }

                    $config->set('HTML.TidyLevel', 'none');
                    $config->set('Cache.SerializerPath', _files_ . '/cache/twig');
                    $this->htmlpurifier = new HTMLPurifier($config);

                break;
            case 'htmlawed' :
                    //another class aganist xss
                    require_once _base_ . '/addons/htmlawed/htmlawed.php';

                break;
            case 'kses' :
            default:
                    if ($this->getWhitelist('tag')) {
                        foreach ($this->getWhitelist('tag') as $val) {
                            if (!isset($GLOBALS['allowed_html'][$val])) {
                                $GLOBALS['allowed_html'][$val] = [];
                            }
                        }
                    }
                    if ($this->getWhitelist('attrib')) {
                        foreach ($this->getWhitelist('attrib') as $val) {
                            list($tag, $attrib) = explode('.', $val);
                            if (!isset($GLOBALS['allowed_html'][$tag])) {
                                $GLOBALS['allowed_html'][$tag] = [];
                            }
                            $GLOBALS['allowed_html'][$tag][$attrib] = [];
                        }
                    }

                break;
        }
    }

    /**
     * Append items (tag or attributes) to the session whitelist.
     *
     * @param <array> $items ('tag'=>array(), 'attrib'=>array())
     */
    public function appendToWhitelist($items)
    {
        if (isset($items['tag'])) {
            $this->session_whitelist['tag'] = array_merge($this->session_whitelist['tag'], $items['tag']);
        }
        if (isset($items['attrib'])) {
            $this->session_whitelist['attrib'] = array_merge($this->session_whitelist['attrib'], $items['attrib']);
        }
    }

    public function getWhitelist($item_type)
    {
        $res = [];
        if (!empty($this->session_whitelist[$item_type])) {
            $res = $this->session_whitelist[$item_type];
        }

        return $res;
    }

    protected function getHtmlPurifierDefaultElements()
    {
        $temp = HTMLPurifier_Config::createDefault();
        $temp->set('Cache.SerializerPath', _files_ . '/cache/twig');
        $def = $temp->getHTMLDefinition();
        ksort($def->info);
        $res = array_keys($def->info);
        unset($temp);

        return $res;
    }

    protected function getHtmlPurifierDefaultAttributes()
    {
        $temp = HTMLPurifier_Config::createDefault();
        $temp->set('Cache.SerializerPath', _files_ . '/cache/twig');
        $def = $temp->getHTMLDefinition();
        ksort($def->info);
        $res = [];
        foreach ($def->info as $key => $value) {
            foreach ($value->attr as $attr => $attr_data) {
                $res[] = $key . '.' . $attr;
            }
        }
        unset($temp);

        return $res;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function clean($data)
    {
        // load the tool that we want to use in the xss filtering process
        $this->loadTool();

        return $this->clean_input_data($data);
    }

    /**
     * This is a helper function. It escapes data and standardizes newline characters to '\n'.
     *
     * @param unknown_type  string to clean
     *
     * @return string
     */
    protected function clean_input_data($str, $is_files_arr = false)
    {
        if (is_array($str)) {
            $new_array = [];
            foreach ($str as $key => $val) {
                if (!$is_files_arr || $key == 'tmp_name') {
                    $new_array[$this->clean_input_keys($key)] = $this->clean_input_data($val);
                }
            }

            return $new_array;
        }

        if ($this->use_xss_clean === true) {
            $str = $this->xss_clean($str);
        }

        // Backward compatibility :(
        $str = addslashes($str);

        // Standardize newlines
        return str_replace(["\r\n", "\r"], "\n", $str);
    }

    /**
     * This is a helper function. To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     *
     * @param string  string to clean
     *
     * @return string
     */
    protected function clean_input_keys($str)
    {
        /*
        if (!preg_match('#^[&a-zA-Z0-9\.\/\\:_\s-]+$#uD', $str)) {
            exit('Disallowed key characters in global data.');
        }
        */
        return $str;
    }

    public function xss_clean($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->xss_clean($val);
            }

            return $data;
        }

        // It is a string
        $string = $data;

        // Do not clean empty strings
        if (trim($string) == '') {
            return $string;
        }

        switch ($this->tool) {
            case 'none':
                    // Only used for a god admin
                ;
                break;
            case 'htmlpurifier':
                    // Run HTMLPurifier
                    $string = $this->htmlpurifier->purify($string);

                break;
            case 'htmlawed':
                    // Run htmLawed
                    $string = htmlawed($string, ['safe' => 1]);

                break;
            case 'kses':
            default:
                    $allowedProtocols = ['http', 'https', 'ftp', 'mailto', 'color', 'background-color'];
                    // Run htmLawed
                    $config = HTMLPurifier_Config::createDefault();
                    $allowed_elements = [];
                    $allowed_attributes = [];
                    foreach ($GLOBALS['allowed_html'] as $element => $attributes) {
                        $allowed_elements[$element] = true;
                        foreach ($attributes as $attribute => $x) {
                            $allowed_attributes["$element.$attribute"] = true;
                        }
                    }
                    $config->set('HTML.AllowedElements', $allowed_elements);
                    $config->set('HTML.AllowedAttributes', $allowed_attributes);
                    if ($allowedProtocols !== null) {
                        $config->set('URI.AllowedSchemes', $allowedProtocols);
                    }
                    $purifier = new HTMLPurifier($config);
                    $string = $purifier->purify($string);

                break;
        }

        return $string;
    }

    /*
     * End of khoana like functions.
     */
}
