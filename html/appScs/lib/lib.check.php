<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

/**
 * This function remove the \ from \' in the data, so the display and managment of the string will be more simple,
 * just remeber to adapt the input data in the sql statement (not needed if you use lib.docebodb.sql)
 *	(This funciton reset the array internal pointer)
 *
 * 	@param &$arrData array of key=>values to be checked
 *	@param $deeper if this parameter is set to TRUE (default) the check
 *			is done recursively in array's values
 *
 *	@return TRUE if the original arrData was good FALSE otherwise.
 *			In this case arrData is modified to be safe
 *
 *	@author Emanuele Sandri <esandri@tiscali.it>
 *          Modify for charset translation
 *			Fabio Pirovano <fabio@docebo.com>
 **/
define("ADP_MAX_DEEP", 10);
function adapt_input_data(&$arr_data, $deeper = TRUE, $deep_reached = 0 ) {
	
	$good = TRUE;
	if($deep_reached > ADP_MAX_DEEP) return $good;
	while(list($key, $val) = each($arr_data)) {
		
		// control key
		$new_key = ( get_magic_quotes_gpc() ? stripslashes($key) : $key );
		if($new_key != $key) {
			$arr_data[$new_key] = $arr_data[$key];
			unset($arr_data[$key]);
			$good = FALSE;
		}
		// control value
		if(is_array($val) && $deeper) {
			
			if( !adapt_input_data($val, $deeper, $deep_reached++ ) ) {
				// if $val is changed reassign to containers array
				$arr_dataata[$key] = $val;
				$good = FALSE;
			}
		} elseif(is_string($val)) {
			
			$new_val = ( get_magic_quotes_gpc() ? stripslashes($val) : $val );
			if($new_val != $val) {
				$arr_data[$key] = $new_val;
				$good = FALSE;
			}
		}
	}
	return $good;
}

?>