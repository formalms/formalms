<?php

namespace FormaLms\lib\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class RequestManager
{
    private static ?RequestManager $instance = null;

    private Request $request;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setSession(Session $session){
        $this->request->setSession($session);
    }
}