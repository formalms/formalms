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

defined('IN_FORMA') or exit('You cannot access this file directly');

class Plugin
{
    public static function img($plugin_name)
    {
        return FormaLms\lib\Get::rel_path('plugins') . '/' . $plugin_name . '/images';
    }

    public static function css($plugin_name, $css_name)
    {
        Util::get_css(FormaLms\lib\Get::rel_path('plugins') . '/' . $plugin_name . '/style/' . $css_name, true, true);
    }

    public static function js($plugin_name, $js_name)
    {
        Util::get_js(FormaLms\lib\Get::rel_path('plugins') . '/' . $plugin_name . '/js/' . $js_name, true, true);
    }

    public function install()
    {
    }

    public function uninstall()
    {
    }
}

class PluginException extends Exception
{
}
