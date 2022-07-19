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
       
        //se c'è un load balancer prima della chiamata potrebbe non essere riconosciuto l'https quindi iniettiamo come proxy affidabili o l'ip del balancer o lo stesso ip del chiamante
        Request::setTrustedProxies(array($this->request->server->get('REMOTE_ADDR')), Request::HEADER_X_FORWARDED_PROTO);
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