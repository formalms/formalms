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
Boot::init(BOOT_DATETIME);
if(Get::cfg('enable_plugins', false)) { PluginManager::initPlugins(); }

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if(!function_exists("aout")) {
	function aout($string) { $GLOBALS['operation_result'] .= $string; }
}

// load the correct module
$aj_file = '';

if(isset($_GET['r'])) {
	$request = $_GET['r'];
	$r = explode('/', $request);
	$mvc = ucfirst($r[0]).( count($r) == 2 ? 'Lms' : '' ).'Controller';
	$action = $r[1];

	$controller = new $mvc( strtolower($r[0]) );
	ob_clean();
	$controller->request($action);

	aout(ob_get_contents());
	ob_clean();

} else {
	$mn = Get::req('mn', DOTY_ALPHANUM, '');
	$plf = Get::req('plf', DOTY_ALPHANUM, ( !empty($_SESSION['current_action_platform']) ? $_SESSION['current_action_platform'] : Get::cur_plat() ));

	if($mn == '') {

		$fl = Get::req('file', DOTY_ALPHANUM, '');
		$sf = Get::req('sf', DOTY_ALPHANUM, '');
		$aj_file = $GLOBALS['where_'.$plf].'/lib/'.( $sf ? $sf.'/' : '' ).'ajax.'.$fl.'.php';
	} else {

		if($plf == 'framework') $aj_file = $GLOBALS['where_'.$plf].'/modules/'.$mn.'/ajax.'.$mn.'.php';
		else $aj_file = $GLOBALS['where_'.$plf].'/modules/'.$mn.'/ajax.'.$mn.'.php';
	}
	
	include( Docebo::inc($aj_file) );
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