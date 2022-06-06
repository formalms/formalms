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

class Docebo
{
    /**
     * Cache file.
     *
     * @var DCache
     */
    protected static $_cache = null;

    private static $currentUser = false;

    private function __construct()
    {
    }

    public static function init()
    {
        self::$currentUser = false;
    }

    /**
     * Return an object that describe the current user logged in.
     *
     * @return DoceboUser
     */
    public static function user()
    {
        if (!self::$currentUser) {

            $sessionUser = \Forma\lib\Session\SessionManager::getInstance()->getSession()->get('user');

            self::$currentUser = $sessionUser ?? DoceboUser::createDoceboUserFromSession('public_area');
        }

        return self::$currentUser;

    }

    /**
     * Return an object that describe the current acl.
     *
     * @return DoceboAcl
     */
    public static function acl()
    {
        return self::user()->getAcl();
    }

    /**
     * Return an object that describe the current aclmanager.
     *
     * @return DoceboAclManager
     */
    public static function aclm()
    {
        return self::user()->getAclManager();
    }

    /**
     * @return DCache
     */
    public static function cache()
    {
        // change the cache based on the config
        if (!self::$_cache) {
            $type = 'dummy';
            $cfg = Forma\lib\Get::cfg('cache', false);
            if (!empty($cfg['type'])) {
                $type = $cfg['type'];
            }
            switch ($type) {
                case 'apc' :
                    if (!extension_loaded('apc')) {
                        self::$_cache = new DApcCache();
                        self::$_cache->init();
                    } else {
                        Log::add('APC functionality was not available on the server.');
                        self::$_cache = new DDummyCache();
                        self::$_cache->init();
                    }
                ; break;
                case 'file' :
                    Log::add('File functionality was not available on the server.');
                    self::$_cache = new DFileCache();
                    self::$_cache->init();
                ; break;
                case 'memcache' :
                    if (class_exists('Memcache')) {
                        self::$_cache = new DMemcache();
                        self::$_cache->init();
                    } else {
                        Log::add('Memcache functionality was not available on the server.');
                        self::$_cache = new DDummyCache();
                        self::$_cache->init();
                    }
                ; break;
                case 'dummy' :
                default:
                    self::$_cache = new DDummyCache();
                    self::$_cache->init();
            }
        }

        return self::$_cache;
    }

    public static function setCourse($id_course)
    {
        require_once _lms_ . '/lib/lib.course.php';
        $GLOBALS['course_descriptor'] = new DoceboCourse($id_course);
    }

    /**
     * Return an object that describe the current user logged in.
     *
     * @return DoceboCourse
     */
    public static function course()
    {
        return  isset($GLOBALS['course_descriptor']) ? $GLOBALS['course_descriptor'] : false;
    }

    /**
     * Return the current database connector handler.
     *
     * @return DbConn
     */
    public static function db()
    {
        return DbConn::getInstance();
    }

    /**
     * Return an object that describe the system languages.
     *
     * @return DoceboLangManager
     */
    public static function langManager()
    {
        return DoceboLangManager::getInstance();
    }

    /**
     * Return an object that describe the system languages.
     *
     * @return DoceboLangManager
     */
    public static function inc_all($file, $function = 'include')
    {
        $file = str_replace(_base_ . '/', '', $file);
        include $file;

        return;
    }
}
