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

define("CORE", true);
define("IN_FORMA", true);
define("IS_API", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
//Boot::init(array("BOOT_CONFIG", "BOOT_UTILITY", "BOOT_DATABASE", "BOOT_SETTING", "BOOT_INPUT", "BOOT_LANGUAGE", "BOOT_DATETIME"));
Boot::init(BOOT_DATETIME);

// -----------------------------------------------------------------------------

if(Get::sett('use_rest_api', 'off') != 'on') {
	die('Error: API not enabled.');
}

$GLOBALS['output'] = '';
function soap_cout($string) { $GLOBALS['output'] .= $string; }

//set MIME type
header('Content-type:application/xml; charset=utf-8');

//read wsdl soap request
$request = ( isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '' );

//WSDL SOAP server library
require(_base_.'/api/lib/soap.server.php');

//create server object
$SOAP_server =& getSOAPServer();

//clear debug messages and clean buffer for output
$debug = ob_get_contents();
ob_clean();

$result = $SOAP_server->service($request);

// fix finalize db connection [auth a.b.]
// finalize
Boot::finalize();

// flush buffer
ob_end_flush();

?>