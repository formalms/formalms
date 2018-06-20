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

function preUpgrade20000() {

	$sts = create_folders();
	if ($sts){
		$sts = setRoles();
	}

	return $sts;
}


function create_folders() {

	$dirs_to_create=array();

	// common dir to check
	$dirs_to_create = array(
		'files/cache'
		);

	foreach($dirs_to_create as $new_dir) {

		if ( ! is_dir(_base_.'/'.$new_dir .'/')	) {
		   $sts = mkdir(_base_.'/'.$new_dir);
		   $GLOBALS['debug'] .=  "<br/>" . "Create new folder '". $new_dir  ."' result= " . $sts;
		}
	}

	return true;

}


function setRoles()
{
    $query = "SELECT DISTINCT idCourse"
        . " FROM %lms_course";
    //$query .= " where idcourse in (4,5)";

    $result = sql_query($query);
    $res = array();

    require_once(_lib_.'/installer/lib.role.php');
    $roleids = array();
    while (list($id_course) = sql_fetch_row($result)){
        $roleids[]='/lms/course/private/'.$id_course.'/statistic/view_all';
        $roleids[]='/lms/course/private/'.$id_course.'/stats/view_all_statuser';
        $roleids[]='/lms/course/private/'.$id_course.'/stats/view_all_statcourse';
        $roleids[]='/lms/course/private/'.$id_course.'/coursestats/view_all';
        $roleids[]='/lms/course/private/'.$id_course.'/coursereport/view_all';
        $roleids[]='/lms/course/private/'.$id_course.'/light_repo/view_all';
    }
    if (!empty($roleids)){
        addRoles($roleids);
    }

    return true;
}
