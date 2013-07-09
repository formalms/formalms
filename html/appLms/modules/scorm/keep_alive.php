<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @version $Id:$
 *
 */
define("LMS", true);
define("IN_DOCEBO", true);
define("_deeppath_", '../../../');
require(dirname(__FILE__).'/'._deeppath_.'base.php');

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

// here all the specific code ==========================================================

require_once($GLOBALS['where_lms'].'/lib/lib.track_user.php');
TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], 'scorm', 'close');

// update the tracking

// =====================================================================================

// close database connection
// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();

?>
