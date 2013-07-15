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

if(Docebo::user()->isAnonymous()) die("You can't access");

require_once(_base_.'/lib/lib.urlmanager.php');

$um =& UrlManager::getInstance("message");
$um->setStdQuery("modname=message&op=message");


if(!defined('IN_LMS')) define("IN_LMS", TRUE);

define("_PATH_MESSAGE", '/appLms/'.Get::sett('pathmessage'));
define("_MESSAGE_VISU_ITEM", Get::sett('visuItem'));
define("_MESSAGE_PL_URL", Get::sett('url'));

require_once(_adm_.'/lib/lib.message.php');


?>