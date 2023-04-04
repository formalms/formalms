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

class Model
{
    public $_record = [];

    protected \Symfony\Component\HttpFoundation\Session\Session $session;

    protected \FormaLms\lib\Alert\AlertService $alertService; 

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $this->alertService = \FormaLms\lib\Alert\AlertService::getInstance();
    }

    public function __get($name)
    {
        if (!isset($this->_record[$name])) {
            return null;
        }

        return $this->$this->_record[$name];
    }

    public function __set($name, $value)
    {
        $this->_record[$name] = $value;
    }

    /**
     * This method is usefull if you need to convert or verify the dir recived by a ajax request.
     *
     * @param string $dir the sort direction
     *
     * @return string the cleaned direction
     */
    public function clean_dir($dir)
    {
        switch ($dir) {
            case 'desc' :
            case 'DESC' :
            case 'yui-dt-desc' :
                $dir = 'desc';
             break;
            case 'asc' :
            case 'ASC' :
            case 'yui-dt-asc' :
            default:
                $dir = 'asc';
             break;
        }

        return $dir;
    }

    /**
     * This method will check if the sort recived from the ajax request is valid checking it's value with a whitelist of possibile value.
     * If a dirty value is passed the default value will be returned or the first sortable_list if the default value is missing.
     *
     * @param string $sort          the sort column
     * @param array  $sortable_list the sort values whitelist
     * @param string $default       the default sort direction
     *
     * @return string the cleaned sort value
     */
    public function clean_sort($sort, $sortable_list, $default = false)
    {
        if (in_array($sort, $sortable_list)) {
            return $sort;
        }
        if (!$default) {
            return array_shift($sortable_list);
        }

        return $default;
    }


    public function sendAlert(array $users) : void{

        $this->alertService->send($users);

    }
}
