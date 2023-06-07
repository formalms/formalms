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

include_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_utils.php');

/**
 * @class CPManagerDb
 **/
class CPManagerDb
{
    public $identifier = '';

    public $errCode = 0;
    public $errText = '';

    public $defaultOrg;
    public $idscorm_package;
    public $idReference;
    public $dbconn;
    public $prefix;
    public string $path;

    public function Open($idReference, $idscorm_package, $dbconn, $prefix)
    {
        $this->idReference = $idReference;
        $this->idscorm_package = $idscorm_package;
        $this->dbconn = $dbconn;
        $this->prefix = $prefix;

        return true;
    }

    /** Parse the imsmanifest.xml file contained in Content Package.
     *  @return true if success, FALSE otherwise; use GetLastError to get the
     *  last generated error
     */
    public function ParseManifest()
    {
        $query = 'SELECT idpackage, path, defaultOrg'
                . ' FROM ' . $this->prefix . '_scorm_package'
                . ' WHERE idscorm_package=' . $this->idscorm_package;
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: ' . sql_error($this->dbconn));

            return false;
        } elseif (sql_num_rows($rs) == 0) {
            $this->setError(SPSCORM_E_RECORDNOTFOUND, 'Package with id=' . $this->idscorm_package . ' not found');

            return false;
        }

        list($this->identifier, $this->path, $this->defaultOrg) = sql_fetch_row($rs);

        return true;
    }

    public function GetOrganizationId($identifier)
    {
        $query = 'SELECT idscorm_organization'
                . ' FROM ' . $this->prefix . '_scorm_organizations'
                . " WHERE org_identifier='" . addslashes($identifier) . "'"
                . ' AND idscorm_package=' . $this->idscorm_package;
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: ' . sql_error($this->dbconn));

            return false;
        } elseif (sql_num_rows($rs) == 0) {
            $this->setError(SPSCORM_E_RECORDNOTFOUND, 'Organization with identifier=' . addslashes($identifier) . ' and idscorm_package=' . $this->idscorm_package . ' not found');

            return false;
        }
        $row = sql_fetch_row($rs);

        return $row[0];
    }

    public function GetResourceInfo($identifier)
    {
        $query = 'SELECT idscorm_resource, scormtype, href'
                . ' FROM ' . $this->prefix . '_scorm_resources'
                . " WHERE idsco='" . addslashes($identifier) . "'"
                . ' AND idscorm_package=' . $this->idscorm_package;
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: ' . sql_error($this->dbconn));

            return false;
        } elseif (sql_num_rows($rs) == 0) {
            $this->setError(SPSCORM_E_RECORDNOTFOUND, 'Resource with scoid=' . addslashes($identifier) . ' and idscorm_package=' . $this->idscorm_package . ' not found');

            return false;
        }

        $row = sql_fetch_assoc($rs);

        $info['href'] = $row['href'];
        $info['identifier'] = $identifier;
        $info['type'] = 'webcontent';
        $info['scormtype'] = $row['scormtype'];
        $info['uniqueid'] = $row['idscorm_resource'];

        return $info;
    }

    /**
     * This function render the organization identified by $identifier using
     *  the $renderer object; this must be inherited from RendererAbstract
     *  and must implement all the functions declared in this class.
     *
     *  @param $identifier idendifier of the organization to render
     *  @param &$renderer object that render the organization
     */
    public function RenderOrganization($identifier, &$renderer)
    {
        $query = 'SELECT idscorm_organization, title'
                . ' FROM ' . $this->prefix . '_scorm_organizations'
                . " WHERE org_identifier='" . addslashes($identifier) . "'"
                . ' AND idscorm_package=' . $this->idscorm_package;
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: ' . sql_error($this->dbconn));

            return false;
        } elseif (sql_num_rows($rs) == 0) {
            $this->setError(SPSCORM_E_RECORDNOTFOUND, 'Organization with identifier=' . $identifier . ' and idscorm_package=' . $this->idscorm_package . ' not found');

            return false;
        }

        list($idscorm_organization, $title) = sql_fetch_row($rs);

        $itemInfo['identifier'] = $identifier;
        $itemInfo['isLast'] = true;     // the organization is always the last
        $itemInfo['identifierref'] = false;
        $itemInfo['isvisible'] = true;
        $itemInfo['parameters'] = false;
        $itemInfo['prerequisites'] = true;
        $itemInfo['title'] = $title;
        $itemInfo['uniqueid'] = $idscorm_organization;
        $itemInfo['idscorm_package'] = $this->idscorm_package;

        $query = 'SELECT idscorm_item, idscorm_organization, idscorm_parentitem, item_identifier,'
                . ' identifierref, idscorm_resource, isvisible, parameters, title,'
                . ' adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction,'
                . ' adlcp_datafromlms, adlcp_masteryscore'
                . ' FROM ' . $this->prefix . '_scorm_items'
                . ' WHERE idscorm_organization=' . $idscorm_organization
                . ' AND idscorm_parentitem IS NULL'
                . ' ORDER BY idscorm_item';
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: ' . sql_error($this->dbconn));

            return false;
        }

        if (sql_num_rows($rs) == 0) {
            // is ok a organization without resources?
            $itemInfo['isLeaf'] = true;
        // debug -- echo "<!-- Organization is leaf [$query] -->";
        } else {
            $itemInfo['isLeaf'] = false;
        }

        $renderer->RenderStartItem($this, $itemInfo);

        while (false !== ($record = sql_fetch_assoc($rs))) {
            $this->RenderItem($record, $renderer, true);
        }

        $renderer->RenderStopItem($this, $itemInfo);
    }

    public function RenderItem(&$record, &$renderer, $isLast)
    {
        // collect infos about item

        $itemInfo['uniqueid'] = $record['idscorm_item'];
        $itemInfo['identifier'] = $record['item_identifier'];

        $itemInfo['isLast'] = $isLast;

        if (strlen($record['identifierref']) > 0) {
            $itemInfo['identifierref'] = $record['identifierref'];
        } else {
            $itemInfo['identifierref'] = false;
        }

        $itemInfo['isvisible'] = (strcmp($record['isvisible'], 'true') == 0) ? true : false;

        if (strlen($record['parameters']) > 0) {
            $itemInfo['parameters'] = $record['parameters'];
        } else {
            $itemInfo['parameters'] = false;
        }

        $itemInfo['title'] = $record['title'];
        $itemInfo['adlcp_prerequisites'] = $record['adlcp_prerequisites'];
        $itemInfo['adlcp_maxtimeallowed'] = $record['adlcp_maxtimeallowed'];
        $itemInfo['adlcp_timelimitaction'] = $record['adlcp_timelimitaction'];
        $itemInfo['adlcp_datafromlms'] = $record['adlcp_datafromlms'];
        $itemInfo['adlcp_masteryscore'] = $record['adlcp_masteryscore'];

        if (strlen($record['adlcp_prerequisites']) > 0 && $this->idReference !== null) {
            $scorm12seq = new SCORM12_Sequencing($this->idReference, false, $record['idscorm_organization'], $this->dbconn, $this->prefix);
            $itemInfo['prerequisites'] = $scorm12seq->evauatePrerequisites($record['adlcp_prerequisites']);
        } else {
            $itemInfo['prerequisites'] = true;
        }

        $query = 'SELECT idscorm_item, idscorm_organization, idscorm_parentitem, item_identifier,'
                . ' identifierref, idscorm_resource, isvisible, parameters, title,'
                . ' adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction,'
                . ' adlcp_datafromlms, adlcp_masteryscore'
                . ' FROM ' . $this->prefix . '_scorm_items'
                . ' WHERE idscorm_organization=' . $record['idscorm_organization']
                . ' AND idscorm_parentitem=' . $record['idscorm_item']
                . ' ORDER BY idscorm_item';

        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            echo '<!-- Generic db error: ' . sql_error($this->dbconn) . " [$query] -->";
            $this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: ' . sql_error($this->dbconn));

            return false;
        }

        if (sql_num_rows($rs) == 0) {
            $itemInfo['isLeaf'] = true;
        // debug echo "<!-- Organization is leaf [$query] -->";
        } else {
            $itemInfo['isLeaf'] = false;
            // debug echo "<!-- Organization is not leaf [$query] -->";
        }

        $renderer->RenderStartItem($this, $itemInfo);

        $subrecord = sql_fetch_assoc($rs);

        while ($subrecord) {
            $nextRecord = sql_fetch_assoc($rs);

            /* pass the info about the last element */
            if ($nextRecord === false) {
                $this->RenderItem($subrecord, $renderer, true);
            } else {
                $this->RenderItem($subrecord, $renderer, false);
            }

            $subrecord = $nextRecord;
        }

        $renderer->RenderStopItem($this, $itemInfo);
    }

    /**
     * Set the error.
     */
    public function setError($errCode, $errText)
    {
        $this->errCode = $errCode;
        $this->errText = $errText;
    }
}
