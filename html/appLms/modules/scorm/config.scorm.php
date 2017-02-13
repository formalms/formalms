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

/**
 * @module config.scorm.php
 * This file contains the "abstraction layer" to interface scorm library to platform
 *
 * @version $Id:$
 **/

if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');
if(!defined("IN_FORMA")) define("IN_FORMA", true);

/** debugging defines **/
define('SOAP_DBG_LEVEL_NONE',	99999);
define('SOAP_DBG_LEVEL_ALL',		0);
define('SOAP_DBG_LEVEL_LOG',		1);
define('SOAP_DBG_LEVEL_WARNING',	2);
define('SOAP_DBG_LEVEL_ERROR',		3);

define('SOAP_DBG_FILTER_NONE',		0x00000000);
define('SOAP_DBG_FILTER_ALL',		0xFFFFFFFF);
define('SOAP_DBG_FILTER_DEFAULT',	0x00000001);
define('SOAP_DBG_FILTER_SETPARAM',	0x00000002);
define('SOAP_DBG_FILTER_GETPARAM',	0x00000004);
define('SOAP_DBG_FILTER_SETERROR', 	0x00000008);

define('SOAP_DBG_OUTFILE',"c:\\temp\\soaperror.txt");
define('SOAP_DBG_CUTLEVEL',SOAP_DBG_LEVEL_NONE);
define('SOAP_DBG_FILTER',SOAP_DBG_FILTER_NONE);


function soap__dbgOut( $textOut, $level = SOAP_DBG_LEVEL_ALL, $filter = SOAP_DBG_FILTER_DEFAULT ) {
	//return;
	if( $level < SOAP_DBG_CUTLEVEL ) 
		return;
	if( !($filter & SOAP_DBG_FILTER) )
		return;
	$fout = fopen(SOAP_DBG_OUTFILE, "a");
	if( is_array($textOut) ) {
		fwrite($fout, print_r($textOut, true) );
	} else {
		fwrite($fout, "$textOut\n");
	}
	fflush($fout);
	fclose($fout);
}
 
/** sal means scorm abstraction layer */
function sal_wrapper() {
	return FALSE;
}

/************************ SpaghettiLearning custom abstraction layer *************/
//require_once(dirname(__FILE__) . '/../../config.php' );
//require_once($GLOBALS['where_config'].'/config.php');
require_once(dirname(__FILE__) . '/xmlwrapper.php');

$scormws = 'modules/scorm/soaplms.php';
$scormxmltree = 'modules/scorm/scormXmlTree.php';
$scormserviceid = "urn:SOAPLMS";

function sl_sal_getUserId() {
	return getLogUserId();
}

function sl_sal_getUserName() {
	/*$dbconn = $GLOBALS['dbConn'];
	$result = sql_query("SELECT surname,name FROM ".$GLOBALS['prefix_lms']."_user WHERE idUser=".$_SESSION['sesUser']."");
	list($surname, $name) = sql_fetch_array($result);*/
	$aclManager = Docebo::user()->getACLManager();
	$arr_result = $aclManager->getUser(Docebo::user()->getIdSt(), FALSE);
	return $arr_result[ACL_INFO_LASTNAME] . ',' . $arr_result[ACL_INFO_FIRSTNAME];
}

$sal_getUserId = 'sl_sal_getUserId';
$sal_getUserName = 'sl_sal_getUserName';
$sal_setValue = 'sal_wrapper';

define("SPSCORM_E_DB_ERROR",100);
define("SPSCORM_E_RECORDNOTFOUND",101);
define("SPSCORM_E_INVALIDMANIFEST",102);
define("SPSCORM_E_FILENOTFOND",103);

?>
