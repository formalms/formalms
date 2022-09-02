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

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (!class_exists('Model')) {
    require_once _lib_ . '/mvc/lib.model.php';
}

require_once _lib_ . '/lib.pluginmanager.php';
require_once _base_ . '/Exceptions/PathNotFoundException.php';
class Forma
{
    public static function usePlugins()
    {
        return empty($GLOBALS['notuse_plugin']) && empty(\FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('notuse_plugin'));
    }

    public static function useCustomScripts()
    {
        return FormaLms\lib\Get::cfg('enable_customscripts', false) && empty($GLOBALS['notuse_customscript']) && empty(\FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('notuse_customscript'));
    }

    /**
     * @param $path
     * @param $file
     * @param $pattern
     *
     * @throws PathNotFoundException
     *
     * @return string
     */
    public static function include($path, $file, $pattern = '/[a-zA-Z0-9.]+\.php/')
    {
        //clean file from injection in protocol xxxxxx.php
        preg_match($pattern, $file, $matches);

        if (count($matches)) {
            $path = $path . $matches[0];
        } else {
            throw new PathNotFoundException();
        }

        return static::inc($path);
    }

    /**
     * @deprecated It will become private
     *
     * @param $file
     *
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

    public static function addError(string $error): string
    {
       
        \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->getFlashBag()->add('error',$error);

        return $error;
    }

    public static function removeErrors(): void
    {
        \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->getFlashBag()->set('error',[]);
    }

    public static function errorsExists(): bool
    {
        return \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->getFlashBag()->has('error');
    }

    public static function getLastError($removeErrors = false): string
    {
        $errors = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->getFlashBag()->get('error');

        return end($errors);
    }

    public static function getErrors($removeErrors = false): array
    {
        $errors = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->getFlashBag()->get('error');

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
