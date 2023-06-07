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

define('CORE', true);
define('IN_FORMA', true);
define('IS_API', true);
define('_deeppath_', '../');
require dirname(__DIR__, 1) . '/base.php';

// start buffer
ob_start();

// ********************************************
// TEMPORARY SOLUTION TO AUTHENTICATION PROBLEM
// ********************************************
$GLOBALS['UNFILTERED_POST'] = $_POST;

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(BOOT_HOOKS);

$GLOBALS['output'] = '';
function rest_cout($string)
{
    $GLOBALS['output'] .= $string;
}

// -----------------------------------------------------------------------------

if (FormaLms\lib\Get::sett('use_rest_api', 'off') !== 'on') {
    rest_cout(RestAPI::HandleError('Error: API not enabled.', $GLOBALS['REST_API_ACCEPT']));
    exit();
}

require_once _base_ . '/api/lib/lib.api.php';
require_once _base_ . '/api/lib/lib.rest.php';

// parsing request
if (!isset($_GET[_REST_PARAM_NAME])) {
    rest_cout(RestAPI::HandleError('Error: no input parameters.', $GLOBALS['REST_API_ACCEPT']));
    exit();
}

//code provided by the user in the request
$auth_code = FormaLms\lib\Get::req('auth', DOTY_STRING, false);

$rest_params = explode('/', $_GET[_REST_PARAM_NAME]);
$numparams = count($rest_params);
if ($numparams < _REST_MINIMUM_PARAMS) {
    rest_cout(RestAPI::HandleError('Error: not enough input parameters.', $GLOBALS['REST_API_ACCEPT']));
    exit();
}
$last_index = $numparams - 1;

// check if this is a valid call
if ($rest_params[0] != '' || $rest_params[1] != _REST_VALIDATOR_PARAM) {
    rest_cout(RestAPI::HandleError('Error: Invalid request.', $GLOBALS['REST_API_ACCEPT']));
    exit();
}

// you may force a different REQUEST_METHOD
$matches = [];
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
switch ($_SERVER['HTTP_ACCEPT']) {
    case _MIME_TYPE_JSON:
        $GLOBALS['REST_API_ACCEPT'] = _REST_OUTPUT_JSON;
        break;
    case _MIME_TYPE_XML:
        $GLOBALS['REST_API_ACCEPT'] = _REST_OUTPUT_XML;
        break;
    default:
        $matches = [];
        if (preg_match('/^(.*)\.(xml|json)$/', $rest_params[$last_index], $matches)) {
            // backward compatibility way
            $rest_params[$last_index] = $matches[1];
            $GLOBALS['REST_API_ACCEPT'] = $matches[2];
        } else {
            $GLOBALS['REST_API_ACCEPT'] = _REST_OUTPUT_XML;
        }
        break;
}

//set MIME type
$content_type = '';
switch ($GLOBALS['REST_API_ACCEPT']) {
    case _REST_OUTPUT_JSON:
        $content_type = _MIME_TYPE_JSON;
        break;
    case _REST_OUTPUT_XML:
    default:
        $content_type = _MIME_TYPE_XML;
        break;
}
header('Content-type:' . $content_type . '; charset=utf-8');

$rest_obj = false;
$rest_module = $rest_params[_REST_APINAME_INDEX]; //the module specification
$rest_function = $rest_params[_REST_APIMETHOD_INDEX]; //the name of module's method to call

// extract additional parameters from GET string, void and outputtype parameter should be already avoided
$i = _REST_APIMETHOD_INDEX + 1;
$rest_subparams = [];
while ($i < count($rest_params)) {//$numparams) {
    $rest_subparams[] = $rest_params[$i];
    ++$i;
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
