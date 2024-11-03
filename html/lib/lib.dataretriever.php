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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version $Id: lib.dataretriever.php 908 2007-01-15 17:55:24Z giovanni $
 */
class DataRetriever
{
    // the database connection
    public $dbConn = null;
    // prefix for table access
    public $prefix = '';
    // the recordset
    public $rs = null;
    // array of order columns
    public $orderCols = null;

    public function __construct($dbConn, $prefix)
    {
        $this->dbConn = $dbConn;
        $this->prefix = $prefix;
        $this->orderCols = [];
    }

    public function setOrderCol($filedName, $descendant)
    {
        $this->orderCols[] = [$filedName, $descendant];
    }

    public function getFieldCount()
    {
        return sql_num_fields($this->rs);
    }

    public function getFieldsInfo()
    {
        $result = [];
        while (($fInfo = sql_fetch_field($this->rs)) != null) {
            $result[$fInfo->name] = $fInfo;
        }

        return $result;
    }

    public function _getData($query, $startRow = false, $numRows = false)
    {
        if (count($this->orderCols) > 0) {
            $query .= ' ORDER BY ';
            $index = 0;
            for (; $index < (count($this->orderCols) - 1); ++$index) {
                $oc = $this->orderCols[$index];
                if ($oc[0] != '') {
                    if ($oc[1]) {
                        $query .= $oc[0] . ' DESC, ';
                    } else {
                        $query .= $oc[0] . ', ';
                    }
                }
            }
            $oc = $this->orderCols[$index];
            if ($oc[0] != '') {
                if ($oc[1]) {
                    $query .= $oc[0] . ' DESC';
                } else {
                    $query .= $oc[0];
                }
            }
        }
        if (((int) $startRow !== false) && ((int) $numRows > 0)) {
            $query .= " LIMIT $startRow,$numRows";
        }

        if ($this->dbConn === null) {
            $this->rs = sql_query($query);
        } else {
            $this->rs = sql_query($query, $this->dbConn);
        }

        return $this->rs;
    }

    public function getRows($startRow, $numRows)
    {
        // put here your query
        // tipical query is:
        // SELECT field1, field2, field3
        // 	FROM myTable
        // 	WHERE field1 = 'something'
        $query = "SELECT 'Hello', 'World.', 'How', 'are', 'you?' ";

        return $this->_getData($query, $startRow, $numRows);
    }

    public function fetchRecord()
    {
        return sql_fetch_assoc($this->rs);
    }

    public function getTotalRows()
    {
        return -1;
    }
}
