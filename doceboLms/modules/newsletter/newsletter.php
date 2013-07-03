<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

if(Docebo::user()->isAnonymous()) die('You can\'t access!');


if(!defined('IN_LMS')) define("IN_LMS", TRUE);

require_once($GLOBALS["where_framework"]."/modules/newsletter/newsletter.php");



?>