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
 * @package admin-core
 * @subpackage user
 * @version 	$Id: lib.userselection.php 113 2006-03-08 18:08:42Z ema $
 */

class userSelection {


	function userSelection( $title = '' ) {
		if($title != '') echo '<div class="title">'.$title.'</div>';
	}
	
	function leftColum( $title, $element ) {
		//EFFECTS : construct the left colum with the array passed
		
		echo '<div class="user-colum-50">'."\n"
			.( ($title != '' ? $title : '' ) )."\n"
			.'<div class="user-selection-colum">'."\n";
		if(is_array($element)){
			$i = 0;
			foreach( $element as $key => $val ) {
				echo '<div class="color-line'.( !($i++%2) ? '' : '-alt').'">'.$val.'</div>'."\n";
			}
		}
		echo '</div>'."\n"
			.'</div>'."\n";
	}
	
	function rightColum( $title, $element ) {
		//EFFECTS : construct the right colum with the array passed
		
		echo '<div class="user-colum-50">'."\n"
			.( ($title != '' ? $title : '' ) )."\n"
			.'<div class="user-selection-colum">'."\n";
		if(is_array($element)){
			$i = 0;
			foreach( $element as $key => $val ) {
				echo '<div class="color-line'.( ($i++%2) ? '' : '-alt').'">'.$val.'</div>'."\n";
			}
		}
		echo '</div>'."\n"
			.'</div>'."\n";
	}
	
	function noFloat() {
		echo '<div class="nofloat"></div>'."\n";
	}
}

?>
