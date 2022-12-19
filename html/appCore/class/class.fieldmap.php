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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class FieldMap
{
    public $lang = null;

    /**
     * class constructor.
     */
    public function __construct()
    {
        
    }

    public function _query($query)
    {
        if ($this->dbconn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbconn);
        }

        return $rs;
    }

    public function _insQuery($query)
    {
        if ($this->dbconn === null) {
            if (!sql_query($query)) {
                return false;
            }
        } else {
            if (!sql_query($query, $this->dbconn)) {
                return false;
            }
        }
        if ($this->dbconn === null) {
            return sql_insert_id();
        } else {
            return sql_insert_id($this->dbconn);
        }
    }

    public function _getMainTable()
    {
    }

    public function getPrefix()
    {
        return '';
    }

    public function getPredefinedFieldLabel($field_id)
    {
        return ucfirst($field_id);
    }

    public function getRawPredefinedFields()
    {
        return [];
    }

    public function getPredefinedFields($with_prefix = true)
    {
        $res = [];

        $pfx = ($with_prefix ? $this->getPrefix() . 'predefined_' : '');
        foreach ($this->getRawPredefinedFields() as $code) {
            $res[$pfx . $code] = $this->getPredefinedFieldLabel($code);
        }

        return $res;
    }

    public function getCustomFields($with_prefix = true)
    {
        return [];
    }

    /**
     * @param array $predefined_data
     * @param array $custom_data
     * @param mixed $id
     * @param bool  $dropdown_id     if true will take dropdown values as id;
     *                               else will search the id starting from the value
     */
    public function saveFields($predefined_data, $custom_data, $id = false)
    {
        return false;
    }
}
