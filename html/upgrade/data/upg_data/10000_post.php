<?php if (!defined('IN_FORMA')) { die('You can\'t access!'); }



// if this file is not needed for a specific version,
// just don't create it.


/**
 * This function must always return a boolean value
 * Error message can be appended to $GLOBALS['debug']
 */
function postUpgrade10000() {
	// echo "post-upgrade 10000";

	return true;
}