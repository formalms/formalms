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

require_once(_base_.'/lib/lib.urlmanager.php');

$um =& UrlManager::getInstance("message");
$um->setStdQuery("modname=message&op=message");

if(!defined('IN_LMS')) define("IN_LMS", TRUE);

define("_PATH_MESSAGE", '/appLms/'.Get::sett('pathmessage'));
define("_MESSAGE_VISU_ITEM", Get::sett('visuItem'));
define("_MESSAGE_PL_URL", Get::sett('url'));

class MessageLmsController extends LmsController {

	protected $db;
	protected $model;
	protected $json;
	protected $aclManager;


	public function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->model = new MessageLms();
		$this->json = new Services_JSON();
		$this->aclManager = Docebo::user()->getAClManager();
		$this->can_send = true;//checkPerm('send_all', true) || checkPerm('send_upper', true);
	}

	//std functions

	public function showTask() {
		require_once(_adm_.'/lib/lib.message.php');
		messageDispatch("message", true);

		//additional actions
		$params = array('id'=>'delete');
		$this->render('_events', $params);
	}


	public function addTask() {
		require_once(_adm_.'/lib/lib.message.php');
		messageDispatch("addmessage", true);
	}

	public function writeTask() {
		require_once(_adm_.'/lib/lib.message.php');
		messageDispatch("writemessage", true);
	}

	public function deleteTask() {
		require_once(_adm_.'/lib/lib.message.php');
		messageDispatch("delmessage", true);
	}

	public function readTask() {
		require_once(_adm_.'/lib/lib.message.php');
		messageDispatch("readmessage", true);
	}

	public function downloadTask() {
		require_once(_adm_.'/lib/lib.message.php');
		messageDispatch("download", true);
	}

	//ajax function

	public function delete_message() {
		$success = false;
		$id = Get::req('id', DOTY_INT, -1);
		if ($id > 0) $success = $this->model->deleteMessage($id);
		$output = array('success'=>$success);
		echo $this->json->encode($output);
	}
}

?>