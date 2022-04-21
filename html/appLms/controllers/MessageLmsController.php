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

require_once _base_ . '/lib/lib.urlmanager.php';

$um = &UrlManager::getInstance('message');
$um->setStdQuery('modname=message&op=message');

if (!defined('IN_LMS')) {
    define('IN_LMS', true);
}

define('_PATH_MESSAGE', '/appLms/' . Forma\lib\Get::sett('pathmessage'));
define('_MESSAGE_VISU_ITEM', Forma\lib\Get::sett('visuItem'));
define('_MESSAGE_PL_URL', Forma\lib\Get::site_url());

class MessageLmsController extends LmsController
{
    protected $db;
    protected $model;
    protected $json;
    protected $aclManager;

    public function init()
    {
        require_once _base_ . '/lib/lib.json.php';
        $this->db = DbConn::getInstance();
        $this->model = new MessageLms();
        $this->json = new Services_JSON();
        $this->aclManager = Docebo::user()->getAClManager();
        $this->can_send = true; //checkPerm('send_all', true) || checkPerm('send_upper', true);
    }

    //std functions

    public function showTask()
    {
        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('message', true);

        //additional actions
        $params = ['id' => 'delete'];
        $this->render('_events', $params);
    }

    public function addTask()
    {
        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('addmessage', true);
    }

    public function writeTask()
    {
        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('writemessage', true);
    }

    public function deleteTask()
    {
        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('delmessage', true);
    }

    public function readTask()
    {
        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('readmessage', true);
    }

    public function downloadTask()
    {
        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('download', true);
    }

    public function directWriteTask()
    {
        $_POST['userselector_input']['main_selector'] = Forma\lib\Get::gReq('idst', DOTY_MIXED, '');
        $_POST['authentic_request'] = Forma\lib\Get::gReq('signature');
        $_POST['okselector'] = 'Save changes';

        require_once _adm_ . '/lib/lib.message.php';
        messageDispatch('writemessage', true);
    }

    //ajax function

    public function delete_message()
    {
        $success = false;
        $id = Forma\lib\Get::req('id', DOTY_INT, -1);
        if ($id > 0) {
            $success = $this->model->deleteMessage($id);
        }
        $output = ['success' => $success];
        echo $this->json->encode($output);
    }
}
