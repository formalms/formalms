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
require_once _base_ . '/Exceptions/PathNotFoundException.php';
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
     * @param $path
     * @param $file
     * @param $pattern
     * 
     * @throws PathNotFoundException
     * @return string
     */
    public static function include($path, $file, $pattern = '/[a-zA-Z0-9.]+\.php/')
    {
      
        //clean file from injection in protocol xxxxxx.php
        preg_match($pattern, $file, $matches);
         
        if(count($matches)) {
            $path = $path . $matches[0];
        } else {
            throw new PathNotFoundException();
        }
               
        return static::inc($path);

    }

    /** 
     * @deprecated It will become private
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

    public static function initErrors(): void
    {
        if (!is_array($_SESSION['errors'])) {
            $_SESSION['errors'] = [];
        }
    }

    public static function addError(string $error): string
    {
        self::initErrors();

        $_SESSION['errors'][] = $error;

        return $error;
    }

    public static function removeLastError(): void
    {
        if (is_array($_SESSION['errors']) && count($_SESSION['errors']) > 0) {
            unset($_SESSION['errors'][count($_SESSION['errors']) - 1]);
        }
    }

    public static function removeErrors(): void
    {
        $_SESSION['errors'] = [];
    }

    public static function errorsExists(): bool
    {
        self::initErrors();
        return count($_SESSION['errors']) > 0;
    }

    public static function getLastError($removeErrors = false): string
    {
        $errors = self::getErrors();
        return end($errors);
    }

    public static function getErrors($removeErrors = false): array
    {
        self::initErrors();
        $errors = $_SESSION['errors'];
        if ($removeErrors) {
            self::removeErrors();
        }
        return $errors;
    }

    public static function getFormattedErrors($removeErrors = false): string
    {
        $errors = self::getErrors();
        $errorString = '';
        foreach ($errors as $error) {
            if (empty($errorString)) {
                $errorString .= sprintf('%s', $error);
            } else {
                $errorString .= sprintf('\n%s', $error);
            }
        }
        if ($removeErrors) {
            self::removeErrors();
        }
        return $errorString;
    }
}