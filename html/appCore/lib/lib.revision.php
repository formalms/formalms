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

/**
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 *
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

// TODO: this class should work stand alone but at this time is not complete and
// works only in the extended version WikiRevisionManager

class RevisionManager
{
    public $prefix = null;
    public $dbconn = null;

    public $table_keys = [];
    public $default_keys_val = [];

    /** Table default fields are: author, version and rev_date **/
    public $table_extra_fields = [];

    public $revision_info = [];

    public function __construct($default_keys_val = [], $prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_fw']);
        $this->dbconn = $dbconn;

        $this->setDefaultKeys($default_keys_val);
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

    public function _getRevisionTable()
    {
        return $this->prefix . '_revision';
    }

    public function cleanInput($arr)
    {
        if (isset($arr['author'])) {
            $arr['author'] = (int) $arr['author'];
        }

        if (isset($arr['version'])) {
            $arr['version'] = (int) $arr['version'];
        }

        return $arr;
    }

    public function getTableKeys()
    {
        return $this->table_keys;
    }

    public function getDefaultKeysVal()
    {
        return $this->default_keys_val;
    }

    public function setDefaultKeys($default_keys_val)
    {
        if (!function_exists('array_combine')) {
            foreach ($this->table_keys as $field_name) {
                $current = current($default_keys_val);
                next($default_keys_val);
                $this->default_keys_val[$field_name] = $current;
            }
        } else {
            $this->default_keys_val = array_combine($this->table_keys, $default_keys_val);
        }
    }

    public function getTableExtraFields()
    {
        return $this->table_extra_fields;
    }

    public function getLastRevision()
    {
        $res = [];

        $table_keys = $this->getTableKeys();
        $default_keys_val = $this->cleanInput($this->getDefaultKeysVal());

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' WHERE ';

        $where_arr = [];
        foreach ($table_keys as $field_name) {
            $where_arr[] = $field_name . "='" . $default_keys_val[$field_name] . "'";
        }
        $qtxt .= implode(' AND ', $where_arr) . ' ';

        $qtxt .= 'ORDER BY version DESC ';
        $qtxt .= 'LIMIT 0,1';
        $q = $this->_query($qtxt);

        if ($q) {
            if (sql_num_rows($q) > 0) {
                $row = sql_fetch_assoc($q);
                $version = $row['version'];
                $this->revision_info[$version] = $row;
                $res = $row;
            } else {
                $res = $this->getEmptyRevision();
            }
        }

        return $res;
    }

    public function getEmptyRevision()
    {
        $res = [];

        $default_keys_val = $this->getDefaultKeysVal();
        foreach ($this->getTableKeys() as $field_name) {
            $res[$field_name] = $default_keys_val[$field_name];
        }

        $res['version'] = 0;
        $res['rev_date'] = date('Y-m-d H:i:s');

        foreach ($this->getTableExtraFields() as $field_name) {
            $res[$field_name] = '';
        }

        $res = $this->cleanInput($res); //print_r($res);

        return $res;
    }

    public function loadRevision($version)
    {
        $res = [];

        $table_keys = $this->getTableKeys();
        $default_keys_val = $this->cleanInput($this->getDefaultKeysVal());

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
        $qtxt .= "WHERE version='" . (int) $version . "'";

        $where_arr = [];
        foreach ($table_keys as $field_name) {
            $where_arr[] = $field_name . "='" . $default_keys_val[$field_name] . "'";
        }
        if (count($where_arr) > 1) {
            $qtxt .= ' AND ' . implode(' AND ', $where_arr);
        }

        $q = $this->_query($qtxt);

        if ($q) {
            if (sql_num_rows($q) > 0) {
                $res = sql_fetch_assoc($q);
            } else {
                $res = $this->getEmptyRevision();
            }
        }

        return $res;
    }

    public function getRevision($version)
    {
        if (!isset($this->revision_info[$version])) {
            $this->revision_info[$version] = $this->loadRevision($version);
        }

        return $this->revision_info[$version];
    }

    public function getRevisionList($ini = false, $vis_item = false)
    {
        $idst_arr = [];
        $data_info = [];
        $data_info['data_arr'] = [];

        $table_keys = $this->getTableKeys();
        $default_keys_val = $this->cleanInput($this->getDefaultKeysVal());

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
        $qtxt .= 'WHERE ';

        $where_arr = [];
        foreach ($table_keys as $field_name) {
            $where_arr[] = $field_name . "='" . $default_keys_val[$field_name] . "'";
        }
        $qtxt .= implode(' AND ', $where_arr) . ' ';

        $qtxt .= 'ORDER BY version DESC';
        $q = $this->_query($qtxt);

        if ($q) {
            $data_info['data_tot'] = sql_num_rows($q);
        } else {
            $data_info['data_tot'] = 0;
        }

        if (($ini !== false) && ($vis_item !== false)) {
            $qtxt .= ' LIMIT ' . $ini . ',' . $vis_item;
            $q = $this->_query($qtxt);
        }

        if (($q) && (sql_num_rows($q) > 0)) {
            $i = 0;
            while ($row = sql_fetch_assoc($q)) {
                $version = $row['version'];
                $data_info['data_arr'][$i] = $row;
                $this->revision_info[$version] = $row;

                if (!in_array($row['author'], $idst_arr)) {
                    $idst_arr[] = $row['author'];
                }

                ++$i;
            }
        }

        if (count($idst_arr) > 0) {
            $acl_manager = \FormaLms\lib\Forma::getAclManager();
            $user_info = $acl_manager->getUsers($idst_arr);
            foreach ($idst_arr as $idst) {
                $data_info['user'][$idst] = $user = $acl_manager->getUserName($idst);
            }
        }

        return $data_info;
    }

    public function addRevision($data, $author = false)
    {
        if ($author === false) {
            $author = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();
        }

        if (!is_array($data)) {
            $data = [];
        }

        $default_keys_val = $this->getDefaultKeysVal();
        foreach ($this->getTableKeys() as $field_name) {
            $data[$field_name] = $default_keys_val[$field_name];
        }

        $data['author'] = $author;
        $data = $this->cleanInput($data);

        $last = $this->getLastRevision();
        $version = $last['version'] + 1;

        $field_list = 'version, rev_date';
        $field_val = "'" . (int) $version . "', NOW()";

        $field_list_arr = [];
        $field_val_arr = [];
        foreach ($data as $key => $val) {
            $field_list_arr[] = $key;
            $field_val_arr[] = "'" . $val . "'";
        }
        if (count($field_list_arr) > 0) {
            $field_list .= ', ' . implode(', ', $field_list_arr);
            $field_val .= ', ' . implode(', ', $field_val_arr);
        }

        $qtxt = 'INSERT INTO ' . $this->_getRevisionTable() . ' (' . $field_list . ') VALUES (' . $field_val . ')';
        $this->_query($qtxt);

        $res = $version;

        return $res;
    }

    /**
     * Returns all the latest revisions of a specified type
     * and, if available, the specified subkey.
     */
    public function getLatestRevisionList($ini = false, $vis_item = false)
    {
        // TODO: make this works with standard core_revision table

        $fields = 'author, rev_date,  MAX(version) as version ';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';

        //$qtxt.="GROUP BY type, parent_id, sub_key ";
        $qtxt .= 'ORDER BY version DESC';

        $data_info = $this->getLatestRevisionListData($qtxt, $ini, $vis_item);

        return $data_info;
    }

    public function getLatestRevisionListData($qtxt, $ini = false, $vis_item = false)
    {
        $idst_arr = [];
        $data_info = [];
        $data_info['data_arr'] = [];

        $q = $this->_query($qtxt);

        if ($q) {
            $data_info['data_tot'] = sql_num_rows($q);
        } else {
            $data_info['data_tot'] = 0;
        }

        if (($ini !== false) && ($vis_item !== false)) {
            $qtxt .= ' LIMIT ' . $ini . ',' . $vis_item;
            $q = $this->_executeQuery($qtxt);
        }

        if (($q) && (sql_num_rows($q) > 0)) {
            $i = 0;
            while ($row = sql_fetch_assoc($q)) {
                $version = $row['version'];
                $data_info['data_arr'][$i] = $row;
                $this->revision_info[$version] = $row;

                if (!in_array($row['author'], $idst_arr)) {
                    $idst_arr[] = $row['author'];
                }

                ++$i;
            }
        }

        if (count($idst_arr) > 0) {
            $acl_manager = \FormaLms\lib\Forma::getAclManager();
            $user_info = $acl_manager->getUsers($idst_arr);
            foreach ($idst_arr as $idst) {
                $data_info['user'][$idst] = $acl_manager->relativeId($user_info[$idst][ACL_INFO_USERID]);
            }
        }

        return $data_info;
    }

    /**
     * Returns all the parent_id values of the revision
     * having content that matches the searched text for
     * the current revision type and, if available, subkey.
     */
    public function searchInLatestRevision($return_val, $search, $ini = false, $vis_item = false)
    {
        $data = $this->getLatestRevisionList($ini, $vis_item);

        $res = $this->searchInLatestRevisionData($return_val, $data);

        return $res;
    }

    public function searchInLatestRevisionData($return_val, $data)
    {
        $res = ['found' => []];

        $data_arr = $data['data_arr'];
        $cached = [];

        foreach ($data_arr as $row) {
            $parent_id = $row[$return_val];
            if (!in_array($parent_id, $res)) {
                $res['found'][] = $parent_id;
                $res['cached'][$parent_id] = $this->getRevision($row['version']);
            }
        }

        return $res;
    }
}

// ------------------------------------------------------------------------- //

class OldRevisionManager
{
    public $prefix = null;
    public $dbconn = null;
    public $type = null;
    public $parent_id = 0;

    public $revision_info = [];
    /**
     * @var bool|null
     */
    public bool $sub_key;

    public function __construct($type, $parent_id, $sub_key = false, $prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_fw']);
        $this->dbconn = $dbconn;

        $this->type = $type;
        $this->parent_id = $parent_id;
        $this->sub_key = $sub_key;
    }

    public function _executeQuery($query)
    {
        if ($this->dbconn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbconn);
        }

        return $rs;
    }

    public function _executeInsert($query)
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

    public function _getRevisionTable()
    {
        return $this->prefix . '_revision';
    }

    public function getRevisionType()
    {
        return $this->type;
    }

    public function getParentId()
    {
        return (int) $this->parent_id;
    }

    public function getSubKey()
    {
        return $this->sub_key;
    }

    public function getLastRevision()
    {
        $res = [];

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
        $qtxt .= "WHERE type='" . $this->getRevisionType() . "' AND ";
        $qtxt .= "parent_id='" . $this->getParentId() . "' ";
        $qtxt .= 'AND ' . ($this->getSubKey() !== false ? "sub_key='" . $this->getSubKey() . "'" : "sub_key='0'") . ' ';
        $qtxt .= 'ORDER BY version DESC ';
        $qtxt .= 'LIMIT 0,1';
        $q = $this->_executeQuery($qtxt);

        if ($q) {
            if (sql_num_rows($q) > 0) {
                $row = sql_fetch_assoc($q);
                $version = $row['version'];
                $this->revision_info[$version] = $row;
                $res = $row;
            } else {
                $res = $this->getEmptyRevision();
            }
        }

        return $res;
    }

    public function loadRevision($version)
    {
        $res = [];

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
        $qtxt .= "WHERE type='" . $this->getRevisionType() . "' AND ";
        $qtxt .= "parent_id='" . $this->getParentId() . "' AND version='" . (int) $version . "'";
        $qtxt .= ' AND ' . ($this->getSubKey() !== false ? "sub_key='" . $this->getSubKey() . "'" : "sub_key='0'");
        $q = $this->_executeQuery($qtxt);

        if ($q) {
            if (sql_num_rows($q) > 0) {
                $res = sql_fetch_assoc($q);
            } else {
                $res = $this->getEmptyRevision();
            }
        }

        return $res;
    }

    public function getRevision($version)
    {
        if (!isset($this->revision_info[$version])) {
            $this->revision_info[$version] = $this->loadRevision($version);
        }

        return $this->revision_info[$version];
    }

    public function getEmptyRevision()
    {
        $res = [];

        $res['type'] = $this->getRevisionType();
        $res['parent_id'] = $this->getParentId();
        $res['sub_key'] = false;
        $res['version'] = 0;
        $res['author'] = '';
        $res['rev_date'] = date('Y-m-d H:i:s');
        $res['content'] = '';

        return $res;
    }

    public function getRevisionList($ini = false, $vis_item = false)
    {
        $idst_arr = [];
        $data_info = [];
        $data_info['data_arr'] = [];

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
        $qtxt .= "WHERE type='" . $this->getRevisionType() . "' AND ";
        $qtxt .= "parent_id='" . $this->getParentId() . "' ";
        $qtxt .= 'AND ' . ($this->getSubKey() !== false ? "sub_key='" . $this->getSubKey() . "'" : "sub_key='0'") . ' ';
        $qtxt .= 'ORDER BY version DESC';
        $q = $this->_executeQuery($qtxt);

        if ($q) {
            $data_info['data_tot'] = sql_num_rows($q);
        } else {
            $data_info['data_tot'] = 0;
        }

        if (($ini !== false) && ($vis_item !== false)) {
            $qtxt .= ' LIMIT ' . $ini . ',' . $vis_item;
            $q = $this->_executeQuery($qtxt);
        }

        if (($q) && (sql_num_rows($q) > 0)) {
            $i = 0;
            while ($row = sql_fetch_assoc($q)) {
                $version = $row['version'];
                $data_info['data_arr'][$i] = $row;
                $this->revision_info[$version] = $row;

                if (!in_array($row['author'], $idst_arr)) {
                    $idst_arr[] = $row['author'];
                }

                ++$i;
            }
        }

        if (count($idst_arr) > 0) {
            $acl_manager = \FormaLms\lib\Forma::getAclManager();
            $user_info = $acl_manager->getUsers($idst_arr);
            foreach ($idst_arr as $idst) {
                $data_info['user'][$idst] = $acl_manager->relativeId($user_info[$idst][ACL_INFO_USERID]);
            }
        }

        return $data_info;
    }

    public function addRevision($content, $author = false)
    {
        if ($author === false) {
            $author = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();
        }

        $type = $this->getRevisionType();
        $parent_id = $this->getParentId();
        $sub_key = $this->getSubKey();

        $last = $this->getLastRevision();
        $version = $last['version'] + 1;

        $field_list = 'type, parent_id, version, sub_key, author, rev_date, content';
        $field_val = "'" . $type . "', '" . (int) $parent_id . "', '" . (int) $version . "', ";
        $field_val .= ($sub_key !== false ? "'" . $sub_key . "'" : "'0'") . ', ';
        $field_val .= "'" . (int) $author . "', NOW(), '" . $content . "' ";

        $qtxt = 'INSERT INTO ' . $this->_getRevisionTable() . ' (' . $field_list . ') VALUES (' . $field_val . ')';
        $this->_executeQuery($qtxt);

        $res = $version;

        return $res;
    }

    /**
     * Returns all the latest revisions of a specified type
     * and, if available, the specified subkey.
     */
    public function getLatestRevisionList($search = false, $ini = false, $vis_item = false, $use_subkey = true, $parent_id_in = false)
    {
        $type = $this->getRevisionType();
        $sub_key = ($use_subkey ? $this->getSubKey() : false);

        $idst_arr = [];
        $data_info = [];
        $data_info['data_arr'] = [];

        $fields = 'type, parent_id, MAX(version) as version, sub_key, ';
        $fields .= 'author, rev_date, content';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
        $qtxt .= "WHERE type='" . $type . "' ";
        if (($parent_id_in !== false) && (is_array($parent_id_in))) {
            $qtxt .= (count($parent_id_in) > 0 ? 'AND parent_id IN (' . implode(',', $parent_id_in) . ') ' : "AND parent_id='0' ");
        }
        $qtxt .= ($sub_key !== false ? "AND sub_key='" . $sub_key . "' " : '');
        $qtxt .= ($search !== false ? "AND content LIKE '%" . $search . "%' " : '');
        $qtxt .= 'GROUP BY type, parent_id, sub_key ';
        $qtxt .= 'ORDER BY version DESC';
        $q = $this->_executeQuery($qtxt);

        if ($q) {
            $data_info['data_tot'] = sql_num_rows($q);
        } else {
            $data_info['data_tot'] = 0;
        }

        if (($ini !== false) && ($vis_item !== false)) {
            $qtxt .= ' LIMIT ' . $ini . ',' . $vis_item;
            $q = $this->_executeQuery($qtxt);
        }

        if (($q) && (sql_num_rows($q) > 0)) {
            $i = 0;
            while ($row = sql_fetch_assoc($q)) {
                $version = $row['version'];
                $data_info['data_arr'][$i] = $row;
                $this->revision_info[$version] = $row;

                if (!in_array($row['author'], $idst_arr)) {
                    $idst_arr[] = $row['author'];
                }

                ++$i;
            }
        }

        if (count($idst_arr) > 0) {
            $acl_manager = \FormaLms\lib\Forma::getAclManager();
            $user_info = $acl_manager->getUsers($idst_arr);
            foreach ($idst_arr as $idst) {
                $data_info['user'][$idst] = $acl_manager->relativeId($user_info[$idst][ACL_INFO_USERID]);
            }
        }

        return $data_info;
    }

    /**
     * Returns all the parent_id values of the revision
     * having content that matches the searched text for
     * the current revision type and, if available, subkey.
     */
    public function searchInLatestRevision($search, $ini = false, $vis_item = false, $use_subkey = true, $parent_id_in = false)
    {
        $res = ['found' => []];

        $data = $this->getLatestRevisionList($search, $ini, $vis_item, $use_subkey, $parent_id_in);
        $data_arr = $data['data_arr'];

        foreach ($data_arr as $row) {
            $parent_id = $row['parent_id'];
            if (!in_array($parent_id, $res)) {
                $res['found'][] = $parent_id;
            }
        }

        $res['data_arr'] = $data['data_arr'];

        return $res;
    }
}
