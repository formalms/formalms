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

namespace FormaLms\lib\System;

use FormaLms\lib\Request\RequestManager;

class SystemManager
{

    protected $request;

    private static ?SystemManager $instance = null;

    /**
     * @return SystemManager
     */
    public static function getInstance() : SystemManager
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }


    public function __construct() {

        $this->request = RequestManager::getInstance()->getRequest();
    }


    public function checkSystemRoutes($check = false)
    {
        $route = '/^(adm\/system\/)(\w+)+$/';
        if ($check) {
            $route = '/^(adm\/system\/checkSystemStatus)+$/';
        }
        return $this->request->query->get('r') && (bool)preg_match($route, $this->request->query->get('r'));
    }

    public function fileLockExistence()
    {
        return (bool) file_exists(_base_ . '/forma.lock');
    }


    //Disabled - htacces use not used at moment
    public function checkWebServer()
    {

        return (bool)preg_match('/^(Apache)/', $this->request->server->get('SERVER_SOFTWARE')??'');
    }


}