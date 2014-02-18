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
Boot::init(BOOT_DATETIME);

// -----------------------------------------------------------------------------

require_once(_base_.'/api/lib/lib.api.php');
require_once(_base_.'/api/lib/lib.rest.php');
 
$GLOBALS['output'] = '';
function rest_cout($string) { $GLOBALS['output'] .= $string; }

// parsing request
if(!isset($_GET[_REST_PARAM_NAME])) {
	die('Error: no input parameters.');
}

//code provided by the user in the request
$auth_code = Get::req('auth', DOTY_STRING, false);

$rest_params = explode('/', $_GET[_REST_PARAM_NAME]);
$numparams = count( $rest_params );
if ($numparams < _REST_MINIMUM_PARAMS) {
	
	die('Error: not enough input parameters.');
}
$last_index = $numparams-1;

// check if this is a valid call
if ($rest_params[0]!='' || $rest_params[1]!=_REST_VALIDATOR_PARAM) {

	die('Error: Invalid request.');
}

// you may force a different REQUEST_METHOD
$matches = array();
$rest_method = $_SERVER['REQUEST_METHOD'];
if (preg_match('/^(.*)!(DELETE|PUT|GET|POST|OPTIONS|HEAD|TRACE|CONNECT)$/', $rest_params[$last_index], $matches)) {
	//if ($rest_method == 'POST' && preg_match('/^(.*)!(DELETE|PUT|GET|POST|OPTIONS|HEAD|TRACE|CONNECT)$/', $rest_params[$last_index], $matches)) {
	$rest_params[$last_index] = $matches[1];
	if ($rest_method == 'POST') {
		//$rest_params[$last_index] = $matches[1];
		$rest_method = $matches[2];
	}
}

// set the output data type (XML or JSON for now)
$GLOBALS['REST_API_ACCEPT']='';
$matches = array();
if (preg_match('/^(.*)\.(xml|json)$/', $rest_params[$last_index], $matches)) {
	$rest_params[$last_index] = $matches[1];
	$GLOBALS['REST_API_ACCEPT'] = $matches[2];
} else {
	$GLOBALS['REST_API_ACCEPT'] = 'xml';
}

//set MIME type
$content_type = '';
switch ($GLOBALS['REST_API_ACCEPT']) {
	case _REST_OUTPUT_JSON: { $content_type = 'application/json'; } break;
	case _REST_OUTPUT_XML:  { $content_type = 'application/xml';  } break;
	default: {
		$GLOBALS['REST_API_ACCEPT'] = _REST_OUTPUT_XML;
		$content_type = 'application/xml';
	} break;
}
header('Content-type:'.$content_type.'; charset=utf-8');

$rest_obj		= false;
$rest_module	= $rest_params[_REST_APINAME_INDEX]; //the module specification
$rest_function	= $rest_params[_REST_APIMETHOD_INDEX]; //the name of module's method to call

// extract additional parameters from GET string, void and outputtype parameter should be already avoided
$i = _REST_APIMETHOD_INDEX + 1;
$rest_subparams = array();
while ($i<count($rest_params)) {//$numparams) {
	$rest_subparams[] = $rest_params[$i];
	$i++;
}

$res = API::Execute($auth_code, $rest_module, $rest_function, $rest_subparams);

if (!$res['success']) {
	$err_msg = $res['message'];
	rest_cout(RestAPI::HandleError($err_msg, $GLOBALS['REST_API_ACCEPT']));
} else {
	rest_cout(RestAPI::HandleOutput($res, $GLOBALS['REST_API_ACCEPT']));
}

// finalize
Boot::finalize();

//clear debug messages and clean buffer for output
$debug = ob_get_contents();
ob_clean();

echo $GLOBALS['output'];

// flush buffer
ob_end_flush();

?>