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

require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/config.scorm.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/CPManager.php');

class Scorm_Organization
{
    public $idscorm_organization;
    public $org_identifier;
    public $idscorm_package;

    public $title;

    public $dbconn;
    public $err_code = 0;
    public $err_text = '';

    public $orgtable = 'scorm_organizations';

    public function __construct($org_identifier, $idscorm_package, $connection, $createonfail = false, $title = '')
    {
        $this->org_identifier = $org_identifier;
        $this->idscorm_package = $idscorm_package;
        $this->dbconn = $connection;

        $this->orgtable = $GLOBALS['prefix_lms'] . '_scorm_organizations';

        // Find the idresource for this idsco, idscorm_package
        $query = 'SELECT idscorm_organization, title '
                . ' FROM ' . $this->orgtable
                . ' WHERE idscorm_package = ' . $idscorm_package
                . " AND org_identifier = '" . addslashes($org_identifier) . "'";

        //die($query);
        $rs = sql_query($query, $this->dbconn);
        if ($rs == false || sql_num_rows($rs) == 0) {
            if ($createonfail) {
                // not found => create new resource record
                $query = "INSERT INTO $this->orgtable "
                . '(org_identifier,idscorm_packege,title) VALUES '
                . "( '$this->org_identifier', $this->idscorm_package, '$this->title' )";
                if (sql_query($query, $this->dbconn)) {
                    if (sql_affected_rows($this->dbconn) == 1) {
                        // get the id of the last insert = idscorm_tracking
                        $this->idscorm_organization = sql_insert_id($this->dbconn);
                    } else {
                        $this->setError(1, 'Scorm_Organization::Scorm_Organization ' . sql_error($this->dbconn) . '[' . $query . ']');
                    }
                } else {
                    $this->setError(2, 'Scorm_Organization::Scorm_Organization ' . sql_error($this->dbconn) . '[' . $query . ']');
                }
            } else {
                $this->setError(3, 'Scorm_Organization::Scorm_Organization ' . sql_error($this->dbconn) . '[' . $query . ']');
            }
        } else {
            list($this->idscorm_organization,
                    $this->title) = sql_fetch_array($rs);
            sql_free_result($rs);
        }
    }

    public function extractFromCPManager($cpm)
    {
    }

    public function save()
    {
        $query = 'UPDATE ' . $this->orgtable
                . " SET title = '" . $this->title . "',"
                . " WHERE idscorm_organization = '" . (int) $this->idscorm_organization . "'";
        if (sql_query === false) {
            $this->setError(4, 'Scorm_Organization::save 1 ' . sql_error($this->dbconn) . '[' . $query . ']');

            return false;
        } else {
            if (sql_affected_rows($this->dbconn) == 0 && sql_errno($this->dbconn) != 0) {
                $this->setError(5, 'Scorm_Organization::save 2 ' . sql_error($this->dbconn) . '[' . $query . ']');

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
