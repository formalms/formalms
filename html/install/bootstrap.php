<?php

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

@error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR);

session_name("docebo_session");
session_start();
define('IN_FORMA', true);
define('INSTALL_ENV', 'install');
define("_deeppath_", "../");
require(dirname(__FILE__).'/../base.php');
define('_installer_', _base_.'/install');

include(_lib_.'/lib.docebo.php');
include(_installer_.'/lib/lib.php');
include(_installer_.'/lib/lib.lang.php');
include(_installer_.'/lib/lib.step.php');
include(_installer_.'/lib/lib.pagewriter.php');
include(_installer_.'/lib/lib.template.php');
PageWriter::init();

include(_base_.'/lib/lib.get.php');
include(_base_.'/lib/lib.utils.php');
include(_base_.'/lib/lib.yuilib.php');
include(_base_.'/lib/lib.form.php');


$GLOBALS['page']->setZone('page_head');
YuiLib::load();
$GLOBALS['page']->setZone('main');

//cout('ciao');

$GLOBALS['page']->add(Util::get_css(getTemplatePath().'style/base.css', true), 'page_head');
$GLOBALS['page']->add(Util::get_css(getTemplatePath().'style/form.css', true), 'page_head');
$GLOBALS['page']->add(Util::get_js('./lib/base.js', true), 'page_head');
$GLOBALS['page']->add(Util::get_js('../addons/yui/event-mouseenter/event-mouseenter-min.js', true), 'page_head');
?>