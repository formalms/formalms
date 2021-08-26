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

if (!class_exists('Model')) {
    require_once(_lib_ . '/mvc/lib.model.php');
}

require_once _lib_ . '/lib.pluginmanager.php';

class Forma
{

    public static function usePlugins()
    {

        return empty($GLOBALS['notuse_plugin']) && empty($_SESSION['notuse_plugin']);
    }

    public static function useCustomScripts()
    {

        return Get::cfg('enable_customscripts', false) && empty($GLOBALS['notuse_customscript']) && empty($_SESSION['notuse_customscript']);
    }

    /**
     * @param $file
     * @return string
     */
    public static function inc($file)
    {

        $file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $file);
        $_file = $file;

        if (substr($file, 0, strlen(_base_)) === _base_) {
            $_file = substr($file, strlen(_base_ . '/'));
        }

        if (self::usePlugins()) {
            $plugins = PluginManager::get_all_plugins();
            foreach ($plugins as $plugin) {
                $plugin_folder = _plugins_ . '/' . $plugin['name'];
                $plugin_file = "$plugin_folder/Features/$_file";
                if (file_exists($plugin_file)) {
                    return $plugin_file;
                }
            }
        }

        if (self::useCustomScripts()) {
            $customscript_file = _base_ . "/customscripts/$_file";
            if (file_exists($customscript_file)) {
                return $customscript_file;
            }
        }

        return $file;
    }


    public static function setError(string $error)
    {
        $_SESSION['last_error'] = $error;
    }

    public static function removeError()
    {
        unset($_SESSION['last_error']);
    }

    public static function errorExists()
    {
        return array_key_exists('last_error', $_SESSION) && !empty($_SESSION['last_error']);
    }

    public static function getError()
    {
        return $_SESSION['last_error'];
    }


}