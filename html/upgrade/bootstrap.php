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
define('IN_DOCEBO', true);			// need for upgrade from doceboce
define('INSTALL_ENV', 'upgrade');

define("_deeppath_", "../");
require(dirname(__FILE__).'/../base.php');
require(_base_.'/config.php');
define('_installer_', _base_.'/install');
define('_upgrader_', _base_.'/upgrade');

require_once _base_.'/lib/loggers/lib.logger.php';

include(_lib_.'/installer/lib.php');
include(_lib_.'/installer/lib.lang.php');
include(_lib_.'/installer/lib.step.php');
include(_lib_.'/installer/lib.pagewriter.php');
include(_lib_.'/installer/lib.template.php');

PageWriter::init();

include(_base_.'/lib/lib.get.php');
include(_base_.'/lib/lib.utils.php');
include(_base_.'/lib/lib.yuilib.php');
include(_base_.'/lib/lib.form.php');
include(_upgrader_.'/config.php');

$GLOBALS['page']->setZone('page_head');
YuiLib::load();
$GLOBALS['page']->setZone('main');

?>