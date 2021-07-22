<?php //if (!defined('IN_FORMA')) { die('You can\'t access!'); }

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

// if this file is not needed for a specific version,
// just don't create it.


/**
 * This function must always return a boolean value
 * Error message can be appended to $GLOBALS['debug']
 */

require_once('bootstrap.php');
require_once('../config.php');

function preUpgrade10000() {

	$sts = upgrade_folders();
	if ( $sts ) {
		$sts = create_folders();
	}

	return $sts;
}


function upgrade_folders() {

	$dirs_to_move=array();

	// common dir to check
	$dirs_to_move = array(
		array(	'old' => 'files/doceboLms',
				'new' => 'files/appLms'),
		array(	'old' => 'files/doceboCore',
				'new' => 'files/appCore')
		);

	foreach($dirs_to_move as $move_dir) {
		   $GLOBALS['debug'] .=  "<br/>" . "Check Old folder '". $move_dir['old'] ."'";

		if ( is_dir(_base_.'/'.$move_dir['old'].'/') &&
		     ! is_dir(_base_.'/'.$move_dir['new'].'/')	) {
		   $GLOBALS['debug'] .=  "<br/>" . "Move folder from: '". _base_.'/'.$move_dir['old']
		                                 . "' to '" . _base_.'/'.$move_dir['new'] ."'";
		    @rename(_base_.'/'.$move_dir['old'],_base_.'/'.$move_dir['new']);
		}
	}

	return true;

}

function create_folders() {

	$dirs_to_create=array();

	// common dir to check
	$dirs_to_create = array(
		'files/appLms/htmlpages'
		);

	foreach($dirs_to_create as $new_dir) {

		if ( ! is_dir(_base_.'/'.$new_dir .'/')	) {
		   $GLOBALS['debug'] .=  "<br/>" . "Create new folder '". $new_dir  ."'";
		   $sts = mkdir(_base_.'/'.$new_dir);
		}
	}

	return true;

}
