<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_tracking.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_items.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/config.scorm.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_items_track.php');

class PlayerscormLms extends Model {
    
    public $user;

    public function __construct() {
        $this->user = \FormaLms\lib\FormaUser::getCurrentUser();
    }

    public function getSCORM($id) {
        $query =
<<<SQL
SELECT so.idscorm_organization AS id, sp.scormVersion AS version, so.title AS title
FROM %lms_scorm_organizations so
INNER JOIN %lms_scorm_package sp ON so.idscorm_package = sp.idscorm_package
WHERE so.idscorm_organization = $id;
SQL;
        $scorm = sql_fetch_object(sql_query($query));
        $scorm->scoes = $this->getSCOes($id);

        $return["result"]["scorm"] = $scorm;
        return $return;
    }

    public function config($id) {

        $config["autoplay"] = true;
        $config["openToc"] = true;

        $return["result"] = $config;

        $return["success"] = true;

        return $return;
    }

    public function getSCOes($id, $parent = null) {
        if($parent === null) {
            $parent = "IS NULL";
        } else {
            $parent = "= $parent";
        }
        $query =
<<<SQL
SELECT idscorm_item AS id, title, '' AS prerequisites, idscorm_parentitem AS parent, CONCAT("../../files/appLms/scorm/", sp.path, '/', sr.href) AS resource, true AS visible
FROM %lms_scorm_items si
LEFT JOIN (%lms_scorm_resources sr
INNER JOIN %lms_scorm_package sp ON sp.idscorm_package = sr.idscorm_package)
    ON si.idscorm_resource = sr.idscorm_resource
WHERE si.idscorm_organization = $id AND idscorm_parentitem $parent
ORDER BY idscorm_item;
SQL;
        $res = sql_query($query);
        $scoes = array();
        while($sco = sql_fetch_object($res)) {
            $sco->children = $this->getSCOes($id, $sco->id);
            $sco->tracking = $this->getSCOTracking($sco->id);
            $scoes[] = $sco;
        }
        return $scoes;
    }

    public function getSCOTracking($id) {
        $query =
<<<SQL
SELECT IF(sit.status = 'not attempted', 'not-attempted', sit.status) AS completionStatus,
IF(sit.status = 'not attempted', 'not-attempted', sit.status) AS successStatus
FROM %lms_scorm_items_track sit
WHERE sit.idscorm_item = $id AND sit.idUser = {$this->user->getIdSt()};
SQL;
        return sql_fetch_object(sql_query($query));
    }

    private function prepareXMLforJSON($xml) {
        foreach($xml->childNodes as $node) {
            if($node->hasChildNodes()) {
                $this->prepareXMLforJSON($node);
            } else {
                if($xml->hasAttributes() && strlen($xml->nodeValue)) {
                    $xml->setAttribute("nodeValue", $node->textContent);
                    $node->nodeValue = "";
                }
            }
        }
    }

    private function XMLtoJSON($xml) {
        $this->prepareXMLforJSON($xml);
        $sxml = simplexml_load_string($xml->saveXML());
        return json_decode(json_encode($sxml));
    }

    public function initialize($id) {
        $dbconn = $GLOBALS['dbConn'];

        $query = 'SELECT path, href, scormtype, scormVersion'
                . ' FROM %lms_scorm_resources, %lms_scorm_package'
                . ' WHERE %lms_scorm_resources.idscorm_package = %lms_scorm_package.idscorm_package'
                . "   AND idscorm_resource = '" . $id . "'";
        $result = sql_query($query)
                    or exit('Error on load sco: ' . sql_error() . "[ $query ]");
        [$path, $href, $scormtype, $scormVersion] = sql_fetch_array($result);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm-' . $scormVersion . '.php');

		$itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
        $rs = $itemtrack->getItemTrack($this->user->getIdst(), null, $id);
        	
        $arrItemTrackData = sql_fetch_assoc( $rs );

        $trackobj = new Scorm_Tracking( $this->user->getIdst(), $arrItemTrackData['idReference'], ($arrItemTrackData['idscorm_tracking'] != NULL ? $arrItemTrackData['idscorm_tracking']: $arrItemTrackData['idscorm_item']), NULL, $dbconn, TRUE, TRUE );		

        scormInitializeParams($trackobj, $scormtype, $arrItemTrackData['idscorm_item']);
        
        $return["result"]["track"] = $this->XMLtoJSON($trackobj->getXmlDoc());
        return $return;
    }

    public function commit($id, $data) {
        $dbconn = $GLOBALS['dbConn'];
		$itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
		$rs = $itemtrack->getItemTrack($this->user->getIdst(), null, $id);		
		$arrItemTrackData = sql_fetch_assoc( $rs );

        $trackobj = new Scorm_Tracking($this->user->getIdst(), $arrItemTrackData['idReference'], ($arrItemTrackData['idscorm_tracking'] != NULL ? $arrItemTrackData['idscorm_tracking']: $arrItemTrackData['idscorm_item']), NULL, $dbconn, TRUE, TRUE );
        
        $flatdata = $this->flatData($data);
        foreach($flatdata as $parampath => $value) {
            $trackobj->setParam($parampath, $value);
        }
    }

    public function pull($id, $data) {
        // PULL
    }

    public function finish($id) {
		$dbconn = $GLOBALS['dbConn'];
        $scormVersion = getScormVersion('idscorm_item', $id);
		require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm-'.$scormVersion.'.php');
		
		$itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
		$rs = $itemtrack->getItemTrack( $this->user->getIdst(), null, $id );
		
		$arrItemTrackData = sql_fetch_assoc( $rs );
		
        $trackobj = new Scorm_Tracking( $this->user->getIdst(), $arrItemTrackData['idReference'], ($arrItemTrackData['idscorm_tracking'] != NULL ? $arrItemTrackData['idscorm_tracking']: $arrItemTrackData['idscorm_item']), NULL, $dbconn, TRUE, TRUE );

		$query = sql_query("SELECT idReference FROM %lms_scorm_tracking WHERE idscorm_tracking = ".$trackobj->idtrack);
		list($idReference) = sql_fetch_row($query);

		$query = "SELECT first_complete, last_complete FROM %lms_commontrack WHERE idReference=".(int)$idReference;
		$res = sql_query($query);
		if ($res && sql_num_rows($res)>0) {
			$now = date("Y-m-d H:i:s");
			list($first_complete, $last_complete) = sql_fetch_row($res);
			$query = "UPDATE %lms_commontrack SET last_complete='".$now."'";
			if (!$first_complete) $query .= ", first_complete='".$now."'";
			$query .= " WHERE idReference=".(int)$idReference;
			$res = sql_query($query);
		}

		if( $trackobj->getParam(SCORM_RTE_CREDIT, false) == 'credit' ) {
			soap__dbgOut("Finish: evaluate ".SCORM_RTE_COMPLETIONSTATUS);
			$itemobj = new Scorm_Item( NULL, FALSE, NULL, $dbconn, false, $arrItemTrackData['idscorm_item'] );
			if( $itemobj ) {
                // get ignoreScore
                $query = 'SELECT idCourse '
                . 'FROM %lms_organization '
                . "WHERE idOrg = '" . (int) $idReference . "' ";
                [$idCourse] = sql_fetch_row(sql_query($query));
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orglib.php');
                $repoDb = new OrgDirDb($idCourse);
                $item = $repoDb->getFolderById($idReference);
                $values = $item->otherValues;
                $ignoreScore = (isset($values[ORGFIELDIGNORESCORE]) && $values[ORGFIELDIGNORESCORE]);

                /* remember in 1.3 masteryscore = completionthreshold */

                // force ignorescore if score_raw not provided
                if ($trackobj->getParam(SCORM_RTE_PROGRESS, false) == null) {
                    $ignoreScore = true;
                }

				if( strlen($itemobj->adlcp_masteryscore) > 0 && !$ignoreScore) {
					$lesson_status = computeCompletionStatus($trackobj, $itemobj->adlcp_masteryscore );
				} else {
					$lesson_status =  $trackobj->getParam(SCORM_RTE_LESSONSTATUS, false);
					if($scormVersion == '1.3') {
						$success_status = $trackobj->getParam(SCORM_RTE_SUCCESSSTATUS, false);
						if($success_status == 'failed') $lesson_status = 'failed';
					}
					if( $lesson_status == 'passed' || $lesson_status == 'completed' )
						$trackobj->setParam(SCORM_RTE_CREDIT, 'no-credit', false, true);							
				}
			}
			$itemtrack->setStatus($this->user->getIdst(), $idReference, $id, $lesson_status, $environment );
		}
		
		$exitVal = $trackobj->getParam(SCORM_RTE_EXIT, false);
		if( $exitVal == "suspend" || $exitVal == "logout" ) {
			$trackobj->setParam(SCORM_RTE_ENTRY, 'resume', false, true);
		} else {
			$trackobj->setParam(SCORM_RTE_ENTRY, '', false, true);
		}

		$sessTime = $trackobj->getParam(SCORM_RTE_SESSIONTIME, false);
		$totTime = $trackobj->getParam(SCORM_RTE_TOTALTIME, false);
		if( strlen($sessTime) > 0 ) {
			$totTime = sumScormTime($sessTime,$totTime);
			$trackobj->setParam(SCORM_RTE_TOTALTIME, $totTime, false, true);
		}
		if ($arr = $trackobj->getTrackData($trackobj->idtrack)) {
			if (!$trackobj->saveHistory($trackobj->idtrack, $arr['score_raw'], $arr['score_max'], $sessTime, $arr['lesson_status'])) {}
		}
		if($environment == 'games' && $arr['score_raw'] !== false && $arr['score_raw'] !== NULL) {
			require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/track.scorm.php');
			Track_ScormOrg::setEnvGamesData($this->user->getIdst(), $idReference, $arr['score_raw'], 'scormorg');
		}
    }

    private function flatData($data, $flat = [], $path = []) {
        foreach($data as $subpath => $item) {
            if(substr($subpath, 0, 1) === '@') continue;
            $_path = $path;
            $_path[] = $subpath;
            if(is_object($item)) {
                $flat = $this->flatData($item, $flat, $_path);
            } else {
                $flat[implode('.', $_path)] = $item;
            }
        }
        return $flat;
    }
}
