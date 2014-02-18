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

function preUpgrade10100() {

	$sts = upgrade_dbtbl();

	return $sts;
}


function upgrade_dbtbl() {

/******
-- ALTER TABLE `learning_middlearea` ADD `sequence` INT( 5 ) NOT NULL;
-- SELECT IFNULL(column_name, '')  FROM information_schema.columns WHERE table_schema = 'connjur' AND table_name = 'my_table' AND column_name = 'my_column';
-- SELECT count(*) FROM information_schema.COLUMNS WHERE COLUMN_NAME='sequence' AND TABLE_NAME='learning_middlearea'  AND TABLE_SCHEMA='forma_devel'
-- SHOW COLUMNS FROM forma_d405_initial.learning_middlearea LIKE 'sequence'
-- SHOW COLUMNS FROM learning_middlearea LIKE 'sequence'
******/
	$qry="SHOW COLUMNS FROM learning_middlearea LIKE 'sequence'";
	$q = mysql_query($qry);
	if (!$q) {
		$GLOBALS['debug'].="<br/>" . mysql_error();
	} else {
		$count =mysql_num_rows($q);
		if ($count == 0 )  {
			// create missing columns
			$qry="ALTER TABLE `learning_middlearea` ADD `sequence` INT( 5 ) NOT NULL;";
			$q = mysql_query($qry);
			if (!$q) {
				$GLOBALS['debug'].= "<br/>" . mysql_error();
			}
		}

	}

	return true;

}

