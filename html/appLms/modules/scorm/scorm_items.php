<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

require_once Forma::inc(_lms_ . '/modules/scorm/config.scorm.php');
require_once Forma::inc(_lms_ . '/modules/scorm/CPManager.php');

class Scorm_Item
{
    public $idscorm_item;
    public $idscorm_organization;
    public $item_identifier;
    public $identifierref;
    public $idscormresource;
    public $isvisible;
    public $parameters;
    public $title;

    public $adlcp_prerequisites = '';
    public $adlcp_maxtimeallowed = '';
    public $adlcp_timelimitaction = '';
    public $adlcp_datafromlms = '';
    public $adlcp_masteryscore = '';

    public $dbconn;
    public $err_code = 0;
    public $err_text = '';

    public $itemtable = 'scorm_items';
    public $idscorm_resource;

    public function __construct($item_identifier, $idscorm_organization, $idpackage, $connection, $createonfail = false, $idscorm_item = null)
    {
        $this->item_identifier = $item_identifier;
        $this->idscorm_organization = $idscorm_organization;
        $this->dbconn = $connection;

        $this->itemtable = $GLOBALS['prefix_lms'] . '_scorm_items';

        // Find the idresource for this idsco, idscormpackage
        if ($idscorm_item !== null) {
            $query = 'SELECT idscorm_item, idscorm_organization, item_identifier, identifierref, idscorm_resource, isvisible, parameters, title, '
                    . 'adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction, adlcp_datafromlms, adlcp_masteryscore'
                    . ' FROM ' . $this->itemtable
                    . " WHERE idscorm_item = '" . (int) $idscorm_item . "'";
        } elseif ($idscorm_organization != false) {
            $query = 'SELECT idscorm_item, idscorm_organization, item_identifier, identifierref, idscorm_resource, isvisible, parameters, title, '
                    . 'adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction, adlcp_datafromlms, adlcp_masteryscore'
                    . ' FROM ' . $this->itemtable
                    . ' WHERE idscorm_organization = ' . $idscorm_organization
                    . " AND item_identifier = '" . $item_identifier . "'";
        } else {
            $query = 'SELECT item.idscorm_item, item.idscorm_organization, item.item_identifier, item.identifierref, item.idscorm_resource, item.isvisible, item.parameters, item.title, '
                    . 'item.adlcp_prerequisites, item.adlcp_maxtimeallowed, item.adlcp_timelimitaction, item.adlcp_datafromlms, item.adlcp_masteryscore'
                    . ' FROM ' . $this->itemtable . ' item, %lms_scorm_organization org'
                    . ' WHERE item.idscorm_organization = org.idscorm_organization'
                    . ' AND org.idscorm_package = ' . $idpackage
                    . " AND item.item_identifier = '" . $item_identifier . "'";
        }

        //die($query);
        $rs = sql_query($query, $this->dbconn);
        if ($rs == false || sql_num_rows($rs) == 0) {
            if ($createonfail) {
                // not found => create new item record
                $query = "INSERT INTO $this->itemtable "
                . "(item_identifier,idscorm_organization) VALUES ( $this->item_identifier, $this->idscorm_organization )";
                if (sql_query($query, $this->dbconn)) {
                    if (sql_affected_rows($this->dbconn) == 1) {
                        // get the id of the last insert = idscorm_tracking
                        $this->idscorm_item = sql_insert_id($this->dbconn);
                    } else {
                        $this->setError(1, 'Scorm_Item::Scorm_Item ' . sql_error($this->dbconn) . '[' . $query . ']');

                        return false;
                    }
                } else {
                    $this->setError(1, 'Scorm_Item::Scorm_Item ' . sql_error($this->dbconn) . '[' . $query . ']');

                    return false;
                }
            } else {
                $this->setError(1, 'Scorm_Item::Scorm_Item ' . sql_error($this->dbconn) . '[' . $query . ']');

                return false;
            }
        } else {
            list($this->idscorm_item,
                    $this->idscorm_organization,
                    $this->item_identifier,
                    $this->identifierref,
                    $this->idscorm_resource,
                    $this->isvisible,
                    $this->parameters,
                    $this->title,
                    $this->adlcp_prerequisites,
                    $this->adlcp_maxtimeallowed,
                    $this->adlcp_timelimitaction,
                    $this->adlcp_datafromlms,
                    $this->adlcp_masteryscore) = sql_fetch_array($rs);
            sql_free_result($rs);
        }

        return true;
    }

    public function save()
    {
        $query = "UPDATE $this->itemtable"
                . " SET identifierref = '$this->identifierref',"
                . " idscorm_resource = $this->idscorm_resource,"
                . " isvisible = '$this->isvisible',"
                . " title = '$this->title',"
                . " idscorm_resource = '$this->idscorm_resource',"
                . " adlcp_prerequisites = '$this->adlcp_prerequisites',"
                . " adlcp_maxtimeallowed = '$this->adlcp_maxtimeallowed',"
                . " adlcp_timelimitaction = '$this->adlcp_timelimitaction',"
                . " adlcp_datafromlms = '$this->adlcp_datafromlms',"
                . " adlcp_masteryscore = '$this->adlcp_masteryscore',"
                . " WHERE idscorm_item = $this->idscorm_item";
        if (sql_query === false) {
            $this->setError(2, 'Scorm_Item::save 1 ' . sql_error($this->dbconn) . '[' . $query . ']');

            return false;
        } else {
            if (sql_affected_rows($this->dbconn) == 0 && sql_errno($this->dbconn) != 0) {
                $this->setError(2, 'Scorm_Item::save 2 ' . sql_error($this->dbconn) . '[' . $query . ']');

                return false;
            }
        }

        return true;
    }

    public function setError($ecode, $etext)
    {
        $this->err_code = $ecode;
        $this->err_text = $etext;
    }

    public function getErrorCode()
    {
        return $this->err_code;
    }

    public function getErrorText()
    {
        return $this->err_text;
    }
}
