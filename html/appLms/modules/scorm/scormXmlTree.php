<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

define('LMS', true);
define('IN_FORMA', true);
//define("IS_AJAX", true);
define('_deeppath_', '../../../');
require __DIR__ . '/' . _deeppath_ . 'base.php';

// start buffer
ob_start();

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(BOOT_DATETIME);

if (!Docebo::user()->isLoggedIn()) {
    exit('Malformed request');
}

$prefix = $GLOBALS['prefix_lms'];
require_once Forma::inc(_lms_ . '/modules/scorm/config.scorm.php');
require_once Forma::inc(_lms_ . '/modules/scorm/scorm_utils.php');
require_once Forma::inc(_lms_ . '/modules/scorm/scorm_items_track.php');
require_once Forma::inc(_lms_ . '/modules/scorm/CPManagerDb.php');
require_once Forma::inc(_lms_ . '/modules/scorm/RendererXML.php');

$idscorm_organization = (int) $_GET['idscorm_organization'];
$idReference = (int) $_GET['idReference'];
$environment = (int) $_GET['environment'];

$query = 'SELECT %lms_scorm_package.idscorm_package, path, org_identifier, scormVersion'
        . ' FROM %lms_scorm_organizations, %lms_scorm_package '
        . ' WHERE %lms_scorm_organizations.idscorm_package = %lms_scorm_package.idscorm_package'
        . "   AND idscorm_organization = '" . $idscorm_organization . "'";

$resultProg = sql_query($query, $GLOBALS['dbConn']);
if (!$resultProg) {
    exit('Error in query ' . $query);
}

list($idscorm_package, $filepath, $organization, $scormVersion) = sql_fetch_row($resultProg);

ob_clean();
$it = new Scorm_ItemsTrack($GLOBALS['dbConn'], $GLOBALS['prefix_lms']);
$rb = new RendererXML();
$rb->idUser = getLogUserId();
$rb->itemtrack = $it;
$cpm = new CPManagerDb();

$filepath = dirname(__DIR__,2) . '/' . $filepath;
//die("->Open( $idReference, $idscorm_package, {$GLOBALS['dbConn']}, {$GLOBALS['prefix_lms']} ");

$bError = false;
if ($bError == false && !$cpm->Open($idReference, $idscorm_package, $GLOBALS['dbConn'], $GLOBALS['prefix_lms'])) {
    $sError = 'Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']';
    $bError = true;
}
if ($bError == false && !$cpm->ParseManifest()) {
    if ($cpm->errCode == SPSCORM_E_DB_ERROR) {
        $sError = 'Error: Generic db error';
    } else {
        $sError = 'Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']';
    }
    $bError = true;
}
if ($bError) {
    echo $sError;
    exit();
}

$idUser = (int) getLogUserId();

$rb->resBase = $filepath . '/';
$cpm->RenderOrganization($organization, $rb);

header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo $rb->getOut();

if ($cpm->errCode != 0) {
    echo 'Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']';
}

ob_end_flush();

exit;	// to avoid index.php to add additional and unuseful html
