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

define("IN_FORMA", true);
define("IS_AJAX", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if(!function_exists("aout")) {
	function aout($string) { $GLOBALS['operation_result'] .= $string; }
}

// load the correct widget
if(isset($_GET['r'])) {
	$request = $_GET['r'];
	$r = explode('/', $request);
	$action = $r[1];
	if (count($r) == 2) {
		// Position, class and method defined in the path requested
		$mvc = ucfirst(strtolower($r[0])).'WidgetController';
		$action = $r[1];
	}
	ob_clean();
	$controller = new $mvc( strtolower($r[1]) );
	$controller->request($action);

	aout(ob_get_contents());
	ob_clean();

}

// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();

?>