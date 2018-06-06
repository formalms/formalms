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

define("LMS", true);
define("IN_FORMA", true);
define("IS_AJAX", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PLUGINS);

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if(!function_exists("aout")) {
	function aout($string) { $GLOBALS['operation_result'] .= $string; }
}
require_once(_lms_.'/lib/lib.permission.php');

// load the correct module
$aj_file = '';
$mn = Get::req('mn', DOTY_ALPHANUM, '');
$plf = Get::req('plf', DOTY_ALPHANUM, ( $_SESSION['current_action_platform'] ? $_SESSION['current_action_platform'] : Get::cur_plat() ));

if(isset($_GET['r'])) { $GLOBALS['req'] = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $_GET['r']); }
if (!empty($GLOBALS['req'])){

    $requesthandler = new RequestHandler($GLOBALS['req'],'lms');
    $requesthandler->run(true);
} else {
	if($mn == '') {

		$fl = Get::req('file', DOTY_ALPHANUM, '');
		$sf = Get::req('sf', DOTY_ALPHANUM, '');
		$aj_file = $GLOBALS['where_'.$plf].'/lib/'.( $sf ? $sf.'/' : '' ).'ajax.'.$fl.'.php';
	} else {

		if($plf == 'framework') $aj_file = $GLOBALS['where_'.$plf].'/modules/'.$mn.'/ajax.'.$mn.'.php';
		else $aj_file = $GLOBALS['where_'.$plf].'/admin/modules/'.$mn.'/ajax.'.$mn.'.php';
	}
}
include(Forma::inc($aj_file));

// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();

?>