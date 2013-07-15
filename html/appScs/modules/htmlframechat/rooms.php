<?php //defined("IN_FORMA") or die('Direct access is forbidden.');

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

error_reporting(E_ALL ^ E_NOTICE); 
require_once(dirname(__FILE__)."/header.php");
// check for remote file inclusion attempt -------------------------------
$list = array('GLOBALS', '_POST', '_GET', '_COOKIE', '_SESSION'); 
while(list(, $elem) = each($list)) {
		
	if(isset($_REQUEST[$elem])) die('Request overwrite attempt detected');
}

define("IN_FORMA", true);
define("_deeppath_", '../../../');
require(dirname(__FILE__).'/../../../base.php');

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_UTILITY);

// ------------------------------------------------------------------------

$script = "
<script type=\"text/javascript\">
	<!--
	function refreshPage() {
		
		window.location.reload( false );
	}
	window.setTimeout('refreshPage()',30000);
	// -->
</script>";
$out->add($script, "page_head");

$op = importVar('op');
if(empty($op)) $op = "rooms";

switch ($op) {
	case "setroom": {
		setRoom($out, $lang);
	} break;
	case "rooms": 
	default: {
		$out->add(listRooms($out, $lang));
	} break;
}

// -------------------------------------------------------------------
require_once(dirname(__FILE__)."/footer.php");
// -------------------------------------------------------------------

?>