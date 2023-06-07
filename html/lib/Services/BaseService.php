<?php

namespace FormaLms\lib\Services;



require_once _lms_.'/lib/lib.course.php';


class BaseService 
{
    protected $session;

    protected $request;


    public function __construct() {

        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        $this->request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
    }

}