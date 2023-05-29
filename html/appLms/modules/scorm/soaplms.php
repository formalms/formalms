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

if (!defined('LMS')) {
    define('LMS', true);
    define('IN_FORMA', true);
    define('_deeppath_', '../../../');
    require __DIR__ . '/' . _deeppath_ . 'base.php';

    // start buffer
    ob_start();

    // initialize
    require _base_ . '/lib/lib.bootstrap.php';
    Boot::init(BOOT_PAGE_WR);
}
$prefix = $GLOBALS['prefix_lms'];

require_once Forma::inc(_lms_ . '/modules/scorm/scorm_tracking.php');
require_once Forma::inc(_lms_ . '/modules/scorm/scorm_items.php');
require_once Forma::inc(_lms_ . '/modules/scorm/config.scorm.php');
require_once Forma::inc(_lms_ . '/modules/scorm/scorm_items_track.php');

/**
 * SOAPLMS.
 *
 * @author ema Emanuele Sandri
 * @copyright Copyright (c) 2004
 *
 * @version $Id: soaplms.php 469 2006-07-21 09:33:46Z fabio $
 **/
class SOAPLMS
{
    public $__dispatch_map = [];

    // Required function by SOAP_Server
    public function __dispatch($methodname)
    {
        soap__dbgOut($methodname);
        if (isset($this->__dispatch_map[$methodname])) {
            return $this->__dispatch_map[$methodname];
        }

        return null;
    }

    // Constructor builds PEAR::SOAP Server
    public function __construct()
    {
        // Define the signature of the dispatch map
        $this->__dispatch_map['Finish'] =
        ['in' => ['idUser' => 'string',
                                'idReference' => 'string',
                                'idscorm_item' => 'string',
                                'environment' => 'string',
        ],
                'out' => ['status' => 'string',
                                'error' => 'string',
                                'errorString' => 'string', ],
        ];
        $this->__dispatch_map['GetValue'] =
        ['in' => ['idUser' => 'string',
                                'idReference' => 'string',
                                'idscorm_item' => 'string',
                                'environment' => 'string',
                                'param' => 'string', ],
                'out' => ['status' => 'string',
                                'error' => 'string',
                                'errorString' => 'string',
                                'value' => 'string', ],
        ];
        $this->__dispatch_map['SetValue'] =
        ['in' => ['idUser' => 'string',
                                'idReference' => 'string',
                                'environment' => 'string',
                                'idscorm_item' => 'string',
                                'param' => 'string',
                                'value' => 'string',
        ],
                'out' => ['status' => 'string',
                                'error' => 'string',
                                'errorString' => 'string',
                ],
        ];
    }

    public function Initialize($idUser, $idReference, $idscorm_item)
    {
        soap__dbgOut("+Initialize($idUser, $idReference, $idscorm_item )");

        $dbconn = $GLOBALS['dbConn'];

        $itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
        $rs = $itemtrack->getItemTrack($idUser, $idReference, $idscorm_item);

        $arrItemTrackData = sql_fetch_assoc($rs);

        $trackobj = new Scorm_Tracking(null, null, $arrItemTrackData['idscorm_tracking'], null, $dbconn, false, false);
        if ($trackobj->getErrorCode() != 0) {
            soap__dbgOut('Finish error: ' . $trackobj->getErrorText());
        }

        $xmldoc = $trackobj->getXmlDoc();

        // remove older interaction :/
        /*
        $context = new DDomXPath( $xmldoc );
        $temp = $context->query('//interactions');
        $lenght = $temp->getLength();

        for ($i=0; $i < $lenght; $i++) {

            $node =& $temp->item($i);
            if($node) $parent = $node->getParentNode();
            if($node && $parent) $parent->removeChild($node);
        }

        // remove old score
        $temp = $context->query('//score');
        if($temp) {
            $lenght = $temp->getLength();

            $node =& $temp->item(0);
            if($node) $parent = $node->getParentNode();
            if($node && $parent) $parent->removeChild($node);
        }
        */
        soap__dbgOut("-Initialize($idUser, $idReference, $idscorm_item )");

        return $xmldoc->saveXML();
    }

    public function updateCommonTrack($trackobj)
    {
        $query = sql_query('SELECT idReference FROM %lms_scorm_tracking WHERE idscorm_tracking = ' . $trackobj->idtrack);
        list($idReference) = sql_fetch_row($query);

        $query = 'SELECT first_complete, last_complete FROM %lms_commontrack WHERE idReference=' . (int) $idReference;
        $res = sql_query($query);
        if ($res && sql_num_rows($res) > 0) {
            $now = date('Y-m-d H:i:s');
            list($first_complete, $last_complete) = sql_fetch_row($res);

            $old_data = ['last_complete' => $last_complete];
            $new_data = ['last_complete' => $now];

            if (!$first_complete || $first_complete > $now) {
                $old_data['first_complete'] = $first_complete;
                $new_data['first_complete'] = $now;
            }

            $data = Events::trigger('lms.lo_user.updating', [
                'id_reference' => $this->idReference,
                'id_user' => $this->idUser,
                'object_type' => $this->objectType,
                'id_track' => $this->idTrack,
                'environment' => $this->environment,
                'old_data' => $old_data,
                'new_data' => $new_data,
            ])['new_data'];

            $query = "UPDATE %lms_commontrack SET last_complete='" . $data['last_complete'] . "'";
            if (array_key_exists('first_complete', $data)) {
                $query .= ", first_complete='" . $data['first_complete'] . "'";
            }
            $query .= ' WHERE idTrack=' . (int) $this->idTrack;
            $res = sql_query($query);

            Events::trigger('lms.lo_user.updated', [
                'id_reference' => $this->idReference,
                'id_user' => $this->idUser,
                'object_type' => $this->objectType,
                'id_track' => $this->idTrack,
                'environment' => $this->environment,
                'old_data' => $old_data,
                'new_data' => [
                    'last_complete' => $data['last_complete'],
                    'first_complete' => $data['first_complete'],
                ],
            ]);

            return $res;
        }
    }

    public function Finish($idUser, $idReference, $idscorm_item, $environment = 'course_lo')
    {
        soap__dbgOut("+Finish($idUser, $idReference, $idscorm_item )");

        $status = 'success';
        $error = '';
        $errorString = '';
        $lesson_status = '';

        $scormVersion = getScormVersion('idscorm_item', $idscorm_item);
        require_once Forma::inc(_lms_ . '/modules/scorm/scorm-' . $scormVersion . '.php');

        $dbconn = $GLOBALS['dbConn'];

        $itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
        $rs = $itemtrack->getItemTrack($idUser, $idReference, $idscorm_item);

        $arrItemTrackData = sql_fetch_assoc($rs);

        $trackobj = new Scorm_Tracking(null, null, $arrItemTrackData['idscorm_tracking'], null, $dbconn, false, false);
        if ($trackobj->getErrorCode() != 0) {
            soap__dbgOut('Finish error: ' . $trackobj->getErrorText());
        }
        /* if it's not for credit don't evaluate lesson_staus/completion_status */

        // Update Common Track
        $this->updateCommonTrack($trackobj);

        if ($trackobj->getParam(SCORM_RTE_CREDIT, false) == 'credit') {
            soap__dbgOut('Finish: evaluate ' . SCORM_RTE_COMPLETIONSTATUS);
            $itemobj = new Scorm_Item(null, false, null, $dbconn, false, $arrItemTrackData['idscorm_item']);
            if ($itemobj) {
                // get ignoreScore
                $query = 'SELECT idCourse '
                . 'FROM %lms_organization '
                . "WHERE idOrg = '" . (int) $idReference . "' ";
                list($idCourse) = sql_fetch_row(sql_query($query));
                require_once Forma::inc(_lms_ . '/modules/organization/orglib.php');
                $repoDb = new OrgDirDb($idCourse);
                $item = $repoDb->getFolderById($idReference);
                $values = $item->otherValues;
                $ignoreScore = (isset($values[ORGFIELDIGNORESCORE]) && $values[ORGFIELDIGNORESCORE]);

                /* remember in 1.3 masteryscore = completionthreshold */

                // force ignorescore if score_raw not provided
                if ($trackobj->getParam(SCORM_RTE_PROGRESS, false) == null) {
                    $ignoreScore = true;
                }

                if (strlen($itemobj->adlcp_masteryscore) > 0 && !$ignoreScore) {
                    $lesson_status = computeCompletionStatus($trackobj, $itemobj->adlcp_masteryscore);
                } else {
                    $lesson_status = $trackobj->getParam(SCORM_RTE_LESSONSTATUS, false);
                    if ($scormVersion == '1.3') {
                        $success_status = $trackobj->getParam(SCORM_RTE_SUCCESSSTATUS, false);
                        if ($success_status == 'failed') {
                            $lesson_status = 'failed';
                        }
                    }
                    if ($lesson_status == 'passed' || $lesson_status == 'completed') {
                        $trackobj->setParam(SCORM_RTE_CREDIT, 'no-credit', false, true);
                    }
                }
            }
            $itemtrack->setStatus($idUser, $idReference, $idscorm_item, $lesson_status, $environment);
        }

        soap__dbgOut('Finish: evaluate ' . SCORM_RTE_ENTRY);
        $exitVal = $trackobj->getParam(SCORM_RTE_EXIT, false);
        /* logout is only in 1.3 scorm version */
        if ($exitVal == 'suspend' || $exitVal == 'logout') {
            $trackobj->setParam(SCORM_RTE_ENTRY, 'resume', false, true);
        } else {
            $trackobj->setParam(SCORM_RTE_ENTRY, '', false, true);
        }

        soap__dbgOut('Finish: evaluate ' . SCORM_RTE_TOTALTIME);
        $sessTime = $trackobj->getParam(SCORM_RTE_SESSIONTIME, false);
        $totTime = $trackobj->getParam(SCORM_RTE_TOTALTIME, false);
        if (strlen($sessTime) > 0) {
            $totTime = sumScormTime($sessTime, $totTime);
            $trackobj->setParam(SCORM_RTE_TOTALTIME, $totTime, false, true);
        }
        //update history
        if ($arr = $trackobj->getTrackData($trackobj->idtrack)) {
            if (!$trackobj->saveHistory($trackobj->idtrack, $arr['score_raw'], $arr['score_max'], $sessTime, $arr['lesson_status'])) {
            }

            // if is a game we update the score result
        }
        if ($environment == 'games' && $arr['score_raw'] !== false && $arr['score_raw'] !== null) {
            require_once Forma::inc(_lms_ . '/class.module/track.scorm.php');
            Track_ScormOrg::setEnvGamesData($idUser, $idReference, $arr['score_raw'], 'scormorg');
        }
        //end update
        soap__dbgOut("Finish return status = $status");

        return $this->makeResponse($status, $error, $errorString);
    }

    public function makeResponse($status, $error, $errorString)
    {
        return '<?xml version="1.0" encoding="utf-8" ?' . '>'
            . '<response>'
                . '<status>' . $status . '</status>'
                . '<error>' . $error . '</error>'
                . '<errorString>' . htmlentities($errorString) . '</errorString>'
            . '</response>';
    }

    public function GetValue($idUser, $idReference, $idscorm_item, $param)
    {
        //echo "<!-- SOAPLMS::GetValue( $userid, $scoid, $scormpackage, $param ) -->\n";
        soap__dbgOut("+GetValue( $idUser, $idReference, $idscorm_item, $param)");
        $dbconn = $GLOBALS['dbConn'];

        // get item_track
        $itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
        $rs = $itemtrack->getItemTrack($idUser, $idReference, $idscorm_item);

        $arrItemTrackData = sql_fetch_assoc($rs);

        // get tracking
        soap__dbgOut("before: Scorm_Tracking(NULL, NULL, {$arrItemTrackData['idscorm_tracking']}, NULL, $dbconn, FALSE, FALSE);");
        $trackobj = new Scorm_Tracking(null, null, $arrItemTrackData['idscorm_tracking'], null, $dbconn, false, false);
        soap__dbgOut("after: Scorm_Tracking(NULL, NULL, {$arrItemTrackData['idscorm_tracking']}, NULL, $dbconn, FALSE, FALSE);");

        if ($trackobj->getErrorCode() != 0) {
            soap__dbgOut('Scorm_Tracking error: ' . $trackobj->getErrorText());
        }

        if (($value = $trackobj->getParam($param)) === false) {
            soap__dbgOut("Scorm_Tracking getParam($param) return false");
            soap__dbgOut('Scorm_Tracking error: ' . $trackobj->getErrorCode() . "\n" . $trackobj->getErrorText());
            $status = 'error';
            $error = $trackobj->getErrorCode();
            $errorString = $trackobj->getErrorText();
        } else {
            soap__dbgOut("Scorm_Tracking getParam($param) return $value");
            $status = 'success';
            $error = '';
            $errorString = '';
        }
        $arr_result = [new SOAP_Value('status', 'string', $status),
            new SOAP_Value('error', 'string', $error),
            new SOAP_Value('errorString', 'string', $errorString),
            new SOAP_Value('value', 'string', $value),
        ];
        //soap__dbgOut($arr_result);
        soap__dbgOut("-GetValue return status = $status, value = $value");

        return $arr_result;
    }

    public function SetValuesFromXML($idUser, $idReference, $idscorm_item, $xmldoc)
    {
        soap__dbgOut("+SetValuesFromXML( $idUser, $idReference, $idscorm_item)");
        $dbconn = $GLOBALS['dbConn'];

        // get item_track
        $itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
        $rs = $itemtrack->getItemTrack($idUser, $idReference, $idscorm_item);

        $arrItemTrackData = sql_fetch_assoc($rs);

        // get tracking
        $trackobj = new Scorm_Tracking(null, null, $arrItemTrackData['idscorm_tracking'], null, $dbconn, false, false);

        if ($trackobj->setParamXML($xmldoc) === false) {
            //soap__dbgOut("Scorm_Tracking setParamXML($xmldoc) return false");
            soap__dbgOut('Scorm_Tracking error: ' . $trackobj->getErrorCode() . "\n" . $trackobj->getErrorText());
            $status = 'error';
            $error = $trackobj->getErrorCode();
            $errorString = $trackobj->getErrorText();
        } else {
            //soap__dbgOut("Scorm_Tracking setParam($param, $value) return true");
            $status = 'success';
            $error = '';
            $errorString = '';
        }

        //$arr_result = array('status' => $status, 'error' => $error, 'errorString' => $errorString );

        soap__dbgOut("-SetValuesFromXML return $status");

        return $this->makeResponse($status, $error, $errorString);
    }

    public function SetValue($idUser, $idReference, $idscorm_item, $param, $value)
    {
        soap__dbgOut("+SetValue( $idUser, $idReference, $idscorm_item, $param, $value)");
        $dbconn = $GLOBALS['dbConn'];

        // get item_track
        $itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
        $rs = $itemtrack->getItemTrack($idUser, $idReference, $idscorm_item);

        $arrItemTrackData = sql_fetch_assoc($rs);

        // get tracking
        soap__dbgOut("before: Scorm_Tracking(NULL, NULL, {$arrItemTrackData['idscorm_tracking']}, NULL, $dbconn, FALSE, FALSE);");
        $trackobj = new Scorm_Tracking(null, null, $arrItemTrackData['idscorm_tracking'], null, $dbconn, false, false);
        soap__dbgOut("after: Scorm_Tracking(NULL, NULL, {$arrItemTrackData['idscorm_tracking']}, NULL, $dbconn, FALSE, FALSE);");

        if (($trackobj->setParam($param, $value)) === false) {
            soap__dbgOut("Scorm_Tracking setParam($param, $value) return false");
            soap__dbgOut('Scorm_Tracking error: ' . $trackobj->getErrorCode() . "\n" . $trackobj->getErrorText());
            $status = 'error';
            $error = $trackobj->getErrorCode();
            $errorString = $trackobj->getErrorText();
        } else {
            soap__dbgOut("Scorm_Tracking setParam($param, $value) return true");
            $status = 'success';
            $error = '';
            $errorString = '';
        }
        $arr_result = [new SOAP_Value('status', 'string', $status),
            new SOAP_Value('error', 'string', $error),
            new SOAP_Value('errorString', 'string', $errorString),
        ];
        soap__dbgOut("-SetValue return $status");

        return $arr_result;
    }
}

function err_handler($errno, $errstr, $errfile, $errline)
{
    $fout = fopen('/tmp/soaperror.txt', 'a');
    fwrite($fout, 'error number ' . $errno . "\n: " . $errstr . "\n file: " . $errfile . "\n line: " . $errline . "\n");
    fclose($fout);

    return;
}

function sanitize_xml($data)
{
    $data = str_replace("\0", ' ', $data);

    return $data;
}

$soaplms = new SOAPLMS();
// Switch off notices to all GET

// Instantiate PEAR::SOAP SOAP_Server
//$soapServer = new SOAP_Server;
// Build the object map (using this instance) + add a namespace
//$soapServer->addObjectMap($soaplms, 'urn:SOAPLMS');

soap__dbgOut('+Arequest');
if ((isset($_GET['op']) && $_GET['op'] == 'Finish')) {
    // load xml document
    $xmlRequest = new DDomDocument();
    if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        $postdata = $GLOBALS['HTTP_RAW_POST_DATA'];
    } else {
        $postdata = file_get_contents('php://input');
    }
    $postdata = sanitize_xml($postdata);
    $xmlRequest->loadXML($postdata);

    $rootRequest = $xmlRequest->getDocumentElement();
    // get idUser from xml document
    $node_array = $rootRequest->getElementsByTagname('idUser');
    $tmpItem = $node_array->item(0);
    $idUser = $tmpItem->getContent();
    // get idReference from xml document
    $node_array = $rootRequest->getElementsByTagname('idReference');
    $tmpItem = $node_array->item(0);
    $idReference = $tmpItem->getContent();

    $node_array = $rootRequest->getElementsByTagname('environment');
    $tmpItem = $node_array->item(0);
    $environment = $tmpItem->getContent();
    // get idscorm_item from xml document
    $node_array = $rootRequest->getElementsByTagname('idscorm_item');
    $tmpItem = $node_array->item(0);
    $idscorm_item = $tmpItem->getContent();

    // remove the "remove" tag
    $node_array = $rootRequest->getElementsByTagname('remove');
    $tmpItem = $node_array->item(0);
    $node_parent = $tmpItem->getParentNode();
    $node_parent->removeChild($tmpItem);

    header('Content-type: text/xml');
    // set parameters
    $soaplms->SetValuesFromXML($idUser, $idReference, $idscorm_item, $xmlRequest);

    // call to finish
    echo $soaplms->Finish($idUser, $idReference, $idscorm_item, $environment);
} elseif ((isset($_GET['op']) && $_GET['op'] == 'Commit')) {
    // load xml document
    $xmlRequest = new DDomDocument();
    if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        $postdata = $GLOBALS['HTTP_RAW_POST_DATA'];
    } else {
        $postdata = file_get_contents('php://input');
    }
    $postdata = sanitize_xml($postdata);
    $xmlRequest->loadXML($postdata);
    $rootRequest = $xmlRequest->getDocumentElement();

    // get idUser from xml document
    $node_array = $rootRequest->getElementsByTagname('idUser');
    $tmpItem = $node_array->item(0);
    $idUser = $tmpItem->getContent();
    // get idReference from xml document
    $node_array = $rootRequest->getElementsByTagname('idReference');
    $tmpItem = $node_array->item(0);
    $idReference = $tmpItem->getContent();
    // get idscorm_item from xml document
    $node_array = $rootRequest->getElementsByTagname('idscorm_item');
    $tmpItem = $node_array->item(0);
    $idscorm_item = $tmpItem->getContent();

    // remove the "remove" tag
    $node_array = $rootRequest->getElementsByTagname('remove');
    $tmpItem = $node_array->item(0);
    $node_parent = $tmpItem->getParentNode();
    $node_parent->removeChild($tmpItem);

    header('Content-type: text/xml');
    // set parameters
    echo $soaplms->SetValuesFromXML($idUser, $idReference, $idscorm_item, $xmlRequest);
} elseif ((isset($_GET['op']) && $_GET['op'] == 'Initialize')) {
    // load xml document
    $xmlRequest = new DDomDocument();
    if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        $postdata = $GLOBALS['HTTP_RAW_POST_DATA'];
    } else {
        $postdata = file_get_contents('php://input');
    }
    $postdata = sanitize_xml($postdata);
    $xmlRequest->loadXML($postdata);
    $rootRequest = $xmlRequest->getDocumentElement();

    // get idUser from xml document
    $node_array = $rootRequest->getElementsByTagname('idUser');
    $tmpItem = $node_array->item(0);
    $idUser = $tmpItem->getContent();
    // get idReference from xml document
    $node_array = $rootRequest->getElementsByTagname('idReference');
    $tmpItem = $node_array->item(0);
    $idReference = $tmpItem->getContent();
    // get idscorm_item from xml document
    $node_array = $rootRequest->getElementsByTagname('idscorm_item');
    $tmpItem = $node_array->item(0);
    $idscorm_item = $tmpItem->getContent();

    header('Content-type: text/xml');
    echo $soaplms->Initialize($idUser, $idReference, $idscorm_item);
} elseif (isset($_GET['op']) && $_GET['op'] == 'scoload') {
    /* Load sco!
    // called to load sco with
    // $_GET parameters are
        idReference
        idUser
        idscorm_resource
        idscorm_item
        idscorm_organization
        idscorm_package
    */
    soap__dbgOut('+Sco launcher: 	idReference = ' . $_GET['idReference']
                                . ', idUser = ' . $_GET['idUser']
                                . ', idscorm_resource = ' . $_GET['idscorm_resource']
                                . ', idscorm_item = ' . $_GET['idscorm_item']
                                . ', idscorm_organization = ' . $_GET['idscorm_organization']
                                . ', idscorm_package = ' . $_GET['idscorm_package']);
    //is_numeric ( $_GET['idReference'] ) &&
    if ($_GET['idReference'] == '') {
        $_GET['idReference'] = 0;
    }
    if (!(is_numeric($_GET['idUser'])
        && is_numeric($_GET['idscorm_resource']) && is_numeric($_GET['idscorm_item'])
        && is_numeric($_GET['idscorm_organization']) && is_numeric($_GET['idscorm_package']))) {
        exit('Malformed input scoload');
        echo 'idReference = ' . $_GET['idReference']
                                . ', idUser = ' . $_GET['idUser']
                                . ', idscorm_resource = ' . $_GET['idscorm_resource']
                                . ', idscorm_item = ' . $_GET['idscorm_item']
                                . ', idscorm_organization = ' . $_GET['idscorm_organization']
                                . ', idscorm_package = ' . $_GET['idscorm_package'];
    }
    $dbconn = $GLOBALS['dbConn'];
    /*
        query for tracking record based on
        - userid: identifier of user
        - scoid: identifier of sco from imsmanifest
        - idscormpackage: identifier of content package from imsmanifest



        - userid: identifier of user
        - scoid: identifier of resource in db
        - idscormpackage: identifier of content package from imsmanifest
     */
    /**
     * we need
     * path to find base of content package
     * href to find relative resource
     * scormtype to know how manage resource
     * scormVersion to map correct RET
     * idscorm_tracking to initialize fields.
     **/
    $query = 'SELECT path, href, scormtype, scormVersion'
            . ' FROM %lms_scorm_resources, %lms_scorm_package'
            . ' WHERE %lms_scorm_resources.idscorm_package = %lms_scorm_package.idscorm_package'
            . "   AND idscorm_resource = '" . $_GET['idscorm_resource'] . "'";
    $result = sql_query($query)
                or exit('Error on load sco: ' . sql_error() . "[ $query ]");
    list($path, $href, $scormtype, $scormVersion) = sql_fetch_array($result);

    require_once Forma::inc(_lms_ . '/modules/scorm/scorm-' . $scormVersion . '.php');

    // get item_track
    $itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
    $rs = $itemtrack->getItemTrack($_GET['idUser'], $_GET['idReference'], $_GET['idscorm_item']);

    $arrItemTrackData = sql_fetch_assoc($rs);

    if ($arrItemTrackData['idscorm_tracking'] === null) {
        // The record don't exist => create a new one

        $trackobj = new Scorm_Tracking($_GET['idUser'], $_GET['idReference'], $_GET['idscorm_item'], $_GET['idscorm_package'], $dbconn, true, true);
        if ($trackobj->getErrorCode() != 0) {
            exit("record don't exist then try to create but error:" . $trackobj->getErrorText());
        }

        scormInitializeParams($trackobj, $scormtype, $_GET['idscorm_item']);

        if ($trackobj->getErrorCode() != 0) {
            exit('setParam error:' . $trackobj->getErrorText());
        }
        // -- already set by precompileXmlDoc in ScormTraking object
        // $trackobj->setParam('cmi.core.student_id', $sal_getUserId(), false, true);
        /*$trackobj->setParam(SCORM_RTE_STUDENTNAME, $sal_getUserName(), false, true);
        $trackobj->setParam(SCORM_RTE_CREDIT, 'credit', false, true);
        $trackobj->setParam(SCORM_RTE_LESSONMODE, 'normal', false, true);
        $trackobj->setParam(SCORM_RTE_ENTRY, 'ab-initio', false, true);
        $trackobj->setParam(SCORM_RTE_TOTALTIME, '0000:00:00.00', false, true);
        $trackobj->setParam(SCORM_RTE_MASTERYSCORE, $adlcp_masteryscore, false, true);
        $trackobj->setParam(SCORM_RTE_MAXTIMEALLOWED, $adlcp_maxtimeallowed, false, true);
        $trackobj->setParam(SCORM_RTE_LAUNCH_DATA, $adlcp_datafromlms, false, true);
        $trackobj->setParam(SCORM_RTE_TIMELIMITACTION, $adlcp_timelimitaction, false, true);*/
        //$trackobj->setParam(SCORM_RTE_COMPLETIONTHRESHOLD, $adlcp_completionthreshold, false, true);

        $itemtrack->setTracking($arrItemTrackData['idscorm_item_track'], $trackobj->idtrack);
        if ($scormtype == 'asset') {
            $itemtrack->setStatus($_GET['idUser'], $_GET['idReference'], $_GET['idscorm_item'], 'completed');
        }
    }
    $scopath = str_replace('\\', '/', $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathscorm'));

    $parameters = '';
    $re = sql_query('SELECT parameters FROM ' . $GLOBALS['prefix_lms'] . "_scorm_items WHERE idscorm_item = '" . $_GET['idscorm_item'] . "'");
    if ($re) {
        list($parameters) = sql_fetch_row($re);
    }

    /*echo $scopath.$path."/".$href.$parameters;
    exit;*/
    Util::jump_to($scopath . $path . '/' . $href . $parameters);
} else {
    // Deal with WSDL / Disco here
    echo '';
    exit;
}
