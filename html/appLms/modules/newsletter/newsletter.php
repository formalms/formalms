<?php defined("IN_FORMA") or die('Direct access is forbidden.');



if(Docebo::user()->isAnonymous()) die('You can\'t access!');


if(!defined('IN_LMS')) define("IN_LMS", TRUE);

require_once($GLOBALS["where_framework"]."/modules/newsletter/newsletter.php");



?>