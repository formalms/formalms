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

/*
 * @package admin-library
 * @subpackage field
 * @version  $Id:$
 */

define('FIELDTABLE', '_field');
define('TYPEFIELDTABLE', '_field_type');
define('GROUPFIELDSTABLE', '_group_fields');
define('FIELDUSERENTRYTABLE', '_field_userentry');

if(!defined('FIELD_INFO_ID')) define('FIELD_INFO_ID', 0);
if(!defined('FIELD_INFO_TYPE')) define('FIELD_INFO_TYPE', 1);
if(!defined('FIELD_INFO_TRANSLATION')) define('FIELD_INFO_TRANSLATION', 2);
if(!defined('FIELD_INFO_GROUPIDST')) define('FIELD_INFO_GROUPIDST', 3);
if(!defined('FIELD_INFO_GROUPID')) define('FIELD_INFO_GROUPID', 4);
if(!defined('FIELD_INFO_MANDATORY')) define('FIELD_INFO_MANDATORY', 5);
if(!defined('FIELD_INFO_USERACCESS')) define('FIELD_INFO_USERACCESS', 6);
if(!defined('FIELD_INFO_USERINHERIT')) define('FIELD_INFO_USERINHERIT', 7);

if(!defined('FIELD_BASEINFO_FILE')) define('FIELD_BASEINFO_FILE', 0);
if(!defined('FIELD_BASEINFO_CLASS')) define('FIELD_BASEINFO_CLASS', 1);

class FieldList
{
    /** @var string the main definition field table */
    public $field_table = '';

    /** @var string the fields type definition table */
    public $type_field_table = '';

    /** @var string the fields <-> group relation table */
    public $group_field_table = '';

    /** @var string the fields value table */
    public $field_entry_table = false;

    /** @var string tell to the object if it has to use
     * or not the multi language features
     */
    public $use_multi_lang = false;

    public function __construct()
    {
        $prefix = '%adm';
        $this->field_table = $prefix . FIELDTABLE;
        $this->type_field_table = $prefix . TYPEFIELDTABLE;
        $this->group_field_table = $prefix . GROUPFIELDSTABLE;
        $this->field_entry_table = $prefix . FIELDUSERENTRYTABLE;
    }

    public function getFieldTable()
    {
        return $this->field_table;
    }

    public function getTypeFieldTable()
    {
        return $this->type_field_table;
    }

    public function getGroupFieldsTable()
    {
        return $this->group_field_table;
    }

    public function getFieldEntryTable()
    {
        return $this->field_entry_table;
    }

    public function setFieldTable($field_table)
    {
        $this->field_table = $field_table;
    }

    public function setTypeFieldTable($type_field_table)
    {
        $this->type_field_table = $type_field_table;
    }

    public function setGroupFieldsTable($group_field_table)
    {
        $this->group_field_table = $group_field_table;
    }

    public function setFieldEntryTable($field_entry_table)
    {
        $this->field_entry_table = $field_entry_table;
    }

    public function &getFieldInstance($id_field, $type_file = false, $type_class = false)
    {
        if ($type_file === false && $type_class === false) {
            $query = 'SELECT ft.id_common, tft.type_file, tft.type_class'
                . '  FROM ' . $this->getFieldTable() . ' AS ft'
                . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
                . ' WHERE ft.id_common = ' . FormaLms\lib\Get::filter($id_field, DOTY_INT) . ' AND ft.type_field = tft.type_field';
            if (!$rs = sql_query($query)) {
                $false_var = null;

                return $false_var;
            }
            list($id_common, $type_file, $type_class) = sql_fetch_row($rs);
        } else {
            $id_common = $id_field;
        }

        require_once \FormaLms\lib\Forma::include(_adm_ . '/modules/field/', $type_file);

        $quest_obj = new $type_class($id_common);

        return $quest_obj;
    }

    public function getArrFieldFromQuery($query_field)
    {
        $output = [];
        $query = 'SELECT ft.id_common, tft.type_field,  tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . '	ON (ft.type_field = tft.type_field) '
            . ' WHERE ft.id_common IN ( ' . $query_field . ' ) ';
        if (!$rs = sql_query($query)) {
            return false;
        }
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            $output[$id_common] = [
                'id' => $id_common,
                'type' => $type_field,
                'file' => $type_file,
                'class' => $type_class,
            ];
        }

        return $output;
    }

    public function &getFieldInstanceFromString($id_field, $type_file, $type_class)
    {
        $query = 'SELECT ft.id_common, tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . ' WHERE ft.id_common = ' . FormaLms\lib\Get::filter($id_field, DOTY_INT) . ' AND ft.type_field = tft.type_field';
        if (!$rs = sql_query($query)) {
            $false_var = null;

            return $false_var;
        }

        [$id_common, $type_file, $type_class] = sql_fetch_row($rs);
        require_once \FormaLms\lib\Forma::include(_adm_ . '/modules/field/', $type_file);
        $quest_obj = new $type_class($id_common);

        return $quest_obj;
    }

    /**
     * @param string    the content of the field mandatory of the GroupFieldsTable
     *
     * @return bool true if the field is mandatory
     **/
    public function _mandatoryField($mandatory)
    {
        return $mandatory == 'true';
    }

    public function getUseMultiLang()
    {
        return (bool) $this->use_multi_lang;
    }

    public function setUseMultiLang($val)
    {
        $this->use_multi_lang = (bool) $val;
    }

    /**
     * @return array array of all fields; index is numeric, value is array with
     *               - idcommon (id_field)
     *               -
     *               - translation (in current language)
     **/
    public function getAllFields($platform = false, $type_field = false)
    {
        $query = 'SELECT id_common, type_field, translation'
            . '  FROM ' . $this->getFieldTable()
            . " WHERE lang_code = '" . Lang::get() . "'";
        if ($type_field != false) {
            $query .= " AND type_field = '" . $type_field . "'";
        }
        $query .= ' ORDER BY sequence';
        $rs = sql_query($query);
        $result = [];

        foreach ($rs as $row) {
            $result[$row['id_common']] = [$row['id_common'], $row['type_field'], $row['translation']];
        }

        return $result;
    }

    public function getFlatAllFields($platform = false, $type_field = false, $lang_code = false)
    {
        $db = \FormaLms\db\DbConn::getInstance();

        if ($lang_code === false) {
            $lang_code = Lang::get();
        }
        $query = 'SELECT id_common, type_field, translation'
            . ' FROM ' . $this->getFieldTable()
            . " WHERE lang_code = '" . $lang_code . "' AND type_field != 'textlabel' ";
        if ($type_field != false) {
            $query .= " AND type_field = '" . $type_field . "'";
        }
        $query .= ' ORDER BY sequence';
        $rs = $db->query($query);
        $result = [];

        foreach ($rs as $row) {
            $result[$row['id_common']] = $row['translation'];
        }

        return $result;
    }

    public function getAllFieldsInfo($lang_code = false)
    {
        $db = \FormaLms\db\DbConn::getInstance();

        if ($lang_code === false) {
            $lang_code = Lang::get();
        }

        $query = 'SELECT ft.id_common, ft.type_field, ft.translation, tft.type_file, tft.type_class '
            . '  FROM ' . $this->getFieldTable() . ' AS ft '
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft ON ( tft.type_field = ft.type_field ) '
            . "  WHERE lang_code = '" . $lang_code . "' ORDER BY sequence ";

        if (!$rs = $db->query($query)) {
            $false_var = null;

            return $false_var;
        }

        $output = [];
        foreach ($rs as $row) {
            $output[] = [
                'id' => $row['id_common'],
                'type' => $row['type_field'],
                'name' => $row['translation'],
            ];
        }

        return $output;
    }

    /**
     * @return array array of fields; index is numeric, value is array with
     *               - idcommon (id_field)
     *               -
     *               - translation (in current language)
     **/
    public function getFieldsFromArray($field_list_arr)
    {
        if (!is_array($field_list_arr) || count($field_list_arr) < 1) {
            return false;
        }

        $query = 'SELECT id_common, type_field, translation'
            . '  FROM ' . $this->getFieldTable()
            . " WHERE lang_code = '" . Lang::get() . "' ";

        $query .= 'AND id_common IN (' . implode(',', $field_list_arr) . ') ';

        $query .= 'ORDER BY sequence';
        $rs = sql_query($query);
        $result = [];

        foreach ($rs as $row) {
            $result[$row['id_common']] = [$row['id_common'], $row['type_field'], $row['translation']];
        }

        return $result;
    }

    /**
     * @param array $arr_idst idst to search
     *
     * @return array array of fields that is associated to an idst;
     *               index is numeric, value is array with
     *               - idcommon (id_field)
     *               - translation (in current language)
     **/
    public function getFieldsFromIdst($arr_idst, $use_group = true, $platform = false)
    {
        $query = 'SELECT ft.id_common, ft.type_field, ft.translation, gft.idst,'
            . ($use_group ? ' g.groupid,' : '0,') . ' gft.mandatory, gft.useraccess, gft.user_inherit '
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . ($use_group ? '  JOIN %adm_group AS g' : '')
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '   AND ft.id_common = gft.id_field'
            . ($use_group ? '   AND gft.idst = g.idst' : '')
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')"
            . ' ORDER BY ft.sequence';
        $rs = sql_query($query);
        $result = [];
        while ($arr = sql_fetch_row($rs)) {
            $result[$arr[FIELD_INFO_ID]] = $arr;
        }

        return $result;
    }

    /**
     * return the info and the value of the field assigned to a user.
     *
     * @param int   $id_user         the idst of the user
     * @param array $manual_id_field if != false the function filter the field with this and not for the field associated to the user
     * @param array $filter_category filter for type_category
     */
    public function getFieldsAndValueFromUser($id_user, $manual_id_field = false, $show_invisible_to_user = false, $filter_category = false)
    {
        $acl = new FormaACL();
        if ($manual_id_field === false) {
            $user_groups = $acl->getUserGroupsST($id_user);
        }

        if (count($user_groups) > 2 && isset($user_groups[1]) && isset($user_groups[2])) {
            // Not only roots ocd_0 and oc_0
            unset($user_groups[1]);
        }

        $query = 'SELECT ft.id_common, ft.type_field, ftt.type_file, ftt.type_class, ft.translation, gft.mandatory, gft.useraccess '
            . 'FROM ' . $this->getFieldTable() . ' AS ft '
            . '	JOIN ' . $this->getGroupFieldsTable() . ' AS gft '
            . ' 	JOIN ' . $this->getTypeFieldTable() . ' AS ftt '
            . 'WHERE ft.id_common = gft.id_field '
            . ' 	AND ft.type_field = ftt.type_field '
            . " 	AND ft.lang_code = '" . Lang::get() . "'"
            . ($show_invisible_to_user === false
                ? " AND gft.useraccess <> 'readwrite' "
                : '')
            . ($manual_id_field !== false
                ? "  AND ft.id_common IN ('" . implode("','", $manual_id_field) . "')"
                : "  AND gft.idst IN ('" . implode("','", $user_groups) . "')")
            . ($filter_category !== false
                ? " AND ftt.type_category IN ( '" . implode("','", $filter_category) . "' ) "
                : '')
            . 'ORDER BY ft.sequence';

        $re_fields = sql_query($query);

        $result = [];
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            $translation = $row['translation'];
            $mandatory = $row['mandatory'];
            $useraccess = $row['useraccess'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = new $type_class($id_common);
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());

            $result[$id_common] = [
                0 => $translation,
                1 => !$this->getUseMultiLang() ? $quest_obj->show($id_user) : $quest_obj->showInLang($id_user, Lang::get()),
                2 => $mandatory,
                3 => $useraccess,
                4 => $type_field,
                5 => $type_file,
                6 => $type_class,
            ];
        }

        return $result;
    }

    /**
     * @param array $arr_idst  idst to search
     * @param int   $value_key the required information that has to be filled in the array
     *                         For example FIELD_INFO_ID or FIELD_INFO_TRANSLATION
     *
     * @return array [field id] => [value required]
     **/
    public function getFieldsArrayFromIdst($arr_idst, $value_key, $use_group = true, $platform = false)
    {
        $fields = $this->getFieldsFromIdst($arr_idst, $use_group = true, $platform = false);

        $res = [];
        foreach ($fields as $field) {
            $res[$field[FIELD_INFO_ID]] = $field[$value_key];
        }

        return $res;
    }

    /**
     * find the value for the fields correlated with all the  user.
     *
     * @param int $id_field the id of the field
     *
     * @return array with the value saved for the users
     **/
    public function getAllFieldEntryData($id_field)
    {
        $query = '
		SELECT id_user, user_entry
		FROM ' . $this->getFieldEntryTable() . '
		WHERE id_common = ' . (int) $id_field . '';
        $rs = sql_query($query);

        $result = [];
        foreach ($rs as $row) {
            $result[$row['id_user']] = [$row['id_user'], $row['user_entry']];
        }

        return $result;
    }

    /**
     * find the number of value filled for the field correlated with all the  user.
     *
     * @param int $id_field the id of the field
     *
     * @return array with the value saved for the users
     **/
    public function getNumberOfFieldEntryData($id_field, $exclude_blank = false)
    {
        $query = '
		SELECT COUNT(*)
		FROM ' . $this->getFieldEntryTable() . '
		WHERE id_common = ' . (int) $id_field . '';
        if ($exclude_blank === true) {
            $query .= " AND user_entry <> '' ";
        }
        if (!$rs = sql_query($query)) {
            return false;
        }

        list($num) = sql_fetch_row($rs);

        return $num;
    }

    /**
     * find the value for the fields correlated with the user.
     *
     * @param int   $id_user   the idst f the user
     * @param array $arr_field the id of the fields
     *
     * @return array with the value saved for the user
     **/
    public function getUserFieldEntryData($id_user, $arr_field = false)
    {
        $query = '
		SELECT id_common, user_entry
		FROM ' . $this->getFieldEntryTable() . "
		WHERE id_user = '" . (int) $id_user . "' ";
        if ($arr_field) {
            $query .= ' AND id_common IN ( ' . implode(',', $arr_field) . ' ) ';
        }
        $rs = sql_query($query);

        $result = [];
        foreach ($rs as $row) {
            $result[$row['id_common']] = $row['user_entry'];
        }

        return $result;
    }

    /**
     * find the value for the fields correlated with a list of user.
     *
     * @param int|array $users     the idst of the user(s)
     * @param array     $arr_field the id of the fields
     *
     * @return array with the value saved for the user
     **/
    public function getUsersFieldEntryData($users, $fields = false, $translate = true)
    {
        if (is_numeric($users)) {
            $users = [$users];
        }
        if (!is_array($users)) {
            return false;
        }

        if (is_numeric($fields)) {
            $fields = [$fields];
        }
        if (!is_array($fields)) {
            $fields = false;
        }

        if ($translate) {
            $sons_arr = [];
            $sons_query = 'SELECT cf.idField, id_common_son, cfs.translation'
                . " FROM %adm_field_son as cfs JOIN %adm_field cf ON cf.idField=cfs.idField WHERE cfs.lang_code='" . Lang::get() . "' AND cf.type_field NOT in ('textfield','freetext') ";
            if (!empty($fields)) {
                $sons_query .= ' AND idField IN (' . implode(',', $fields) . ')';
            }
            $sons_rs = sql_query($sons_query);

            foreach ($sons_rs as $row) {
                $sons_arr[$row['idField']][$row['id_common_son']] = $row['translation'];
            }

            $yesno_fields = [];
            $yn_query = "SELECT id_common FROM %adm_field WHERE type_field = 'yesno' ";
            if (!empty($fields)) {
                $yn_query .= ' AND id_common IN ( ' . implode(',', $fields) . ' )';
            }
            $yn_rs = sql_query($yn_query);

            foreach ($yn_rs as $row) {
                $yesno_fields[] = $row['id_common'];
            }
        }

        $query = 'SELECT id_user, id_common, user_entry '
            . ' FROM %adm_field_userentry'
            . ' WHERE id_user IN (' . implode(',', $users) . ') ';
        if (!empty($fields)) {
            $query .= ' AND id_common IN ( ' . implode(',', $fields) . ' ) ';
        }

        $rs = sql_query($query);

        $result = [];
        foreach ($rs as $row){
            $id_user = $row['id_user'];
            $id_field = $row['id_common'];
            $value = $row['user_entry'];
            if ($translate) {
                if (array_key_exists((int)$id_field, $sons_arr)) {
                    $result[$id_user][$id_field] = $sons_arr[(int)$id_field][(int)$value] ?? '';
                } elseif (in_array($id_field, $yesno_fields)) {
                    $yntrans = Lang::t('_NOT_ASSIGNED', 'field');
                    switch ($value) {
                        case 1:
                            $yntrans = Lang::t('_YES', 'standard');
                            break;
                        case 2:
                            $yntrans = Lang::t('_NO', 'standard');
                            break;
                        default:
                            break;
                    }
                    $result[$id_user][$id_field] = $yntrans;
                } else {
                    $result[$id_user][$id_field] = $value;
                }
            } else {
                $result[$id_user][$id_field] = $value;
            }
        }

        return $result;
    }

    /**
     * find the id of the entity that have the given value for the given field.
     *
     * @param int   $id_field       the id of the field
     * @param mixed $value_to_check the value to check
     *
     * @return array with the id of the entity
     **/
    public function getOwnerData($id_field, $value_to_check)
    {
        $query = '
		SELECT id_user
		FROM ' . $this->getFieldEntryTable() . "
		WHERE id_common = '" . $id_field . "' AND user_entry = '" . $value_to_check . "'";
        $rs = sql_query($query);
        $result = [];
        while (list($owner) = sql_fetch_row($rs)) {
            $result[] = $owner;
        }

        return $result;
    }

    /**
     * find the id of the entity that have the given value for the given field.
     *
     * @param int   $id_field       the id of the field
     * @param mixed $value_to_check the value to check
     *
     * @return array with the id of the entity
     **/
    public function getOwnerDataWithLike($id_field, $value_to_check)
    {
        $query = '
		SELECT id_user
		FROM ' . $this->getFieldEntryTable() . "
		WHERE id_common = '" . $id_field . "' AND user_entry LIKE '%" . $value_to_check . "%'";
        $rs = sql_query($query);
        $result = [];
        while (list($owner) = sql_fetch_row($rs)) {
            $result[] = $owner;
        }

        return $result;
    }

    /**
     * return info about a field.
     *
     * @param int $type_field the type of the field
     *
     * @return array with 0 => type_file 1 => type_class
     **/
    public function getBaseFieldInfo($type_field)
    {
        $arr_result = sql_fetch_row(sql_query(
            'SELECT type_file, type_class '
            . ' FROM ' . $this->getTypeFieldTable()
            . " WHERE type_field = '" . $type_field . "'"
        ));

        return $arr_result;
    }

    /**
     * @param int  $id_st    idst to be associated to the user
     * @param int  $id_field id of the field to get
     * @param bool $freeze   TRUE to get static text, false to get input control
     *
     * @return html with the form code for play a set of fields
     **/
    public function showFieldForUser($idst_user, $id_field)
    {
        $query = 'SELECT tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . ' WHERE ft.type_field = tft.type_field'
            . "   AND ft.id_common = '" . $id_field . "'"
            . ' ORDER BY ft.sequence';

        $rs = sql_query($query);

        if (sql_num_rows($rs) < 1) {
            return 'NULL';
        }
        list($type_file, $type_class) = sql_fetch_row($rs);
        require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
        $quest_obj = eval("return new $type_class( $id_field );");
        if ($this->field_entry_table !== false) {
            $quest_obj->setFieldEntryTable($this->field_entry_table);
        }

        $quest_obj->setMainTable($this->getFieldTable());
        if (!$this->getUseMultiLang()) {
            return $quest_obj->show($idst_user);
        } else {
            return $quest_obj->showInLang($idst_user, Lang::get());
        }
    }

    /**
     * @param int   $idst_user idst to be associated to the user
     * @param array $arr_field optional you can filter the field to show
     *
     * @return html with the info about yhe field for the user passed
     **/
    public function showAllFieldForUser($idst_user, $arr_field = false)
    {
        $acl = \FormaLms\lib\Forma::getAcl();
        $arr_idst = $acl->getUserGroupsST($idst_user);
        $index = count($arr_idst);

        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $tmp = $acl_man->getGroup(false, '/oc_0');
        $arr_idst[] = $tmp[0];
        $tmp = $acl_man->getGroup(false, '/ocd_0');
        $arr_idst[] = $tmp[0];
        $index += 2;

        if (count($arr_idst) > $index) {
            // Not only roots ocd_0 and oc_0
            for ($i = 0, $iMax = count($arr_idst); $i < $iMax; ++$i) {
                if ($arr_idst[$i] == 1) {
                    unset($arr_idst[$i]);
                }
            }
        }

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class, gft.mandatory'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . '   AND ft.id_common = gft.id_field'
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')"
            . ($arr_field !== false && is_array($arr_field) && !empty($arr_field)
                ? " AND ft.id_common IN ('" . implode("','", $arr_field) . "') "
                : '')
            . ' GROUP BY ft.id_common '
            . ' ORDER BY ft.sequence, gft.id_field';

        $play_txt = '';
        $re_fields = sql_query($query);
        if (!sql_num_rows($re_fields)) {
            return '';
        }

        //while (list($id_common, $type_field, $type_file, $type_class, $mandatory) = sql_fetch_row($re_fields)) {
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            $mandatory = $row['mandatory'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            if (!$this->getUseMultiLang()) {
                $play_txt .= $quest_obj->show($idst_user);
            } else {
                $play_txt .= $quest_obj->showInLang($idst_user, Lang::get());
            }
        }

        return $play_txt;
    }

    public function getAllFieldValue($id_common)
    {
        $query = 'SELECT ft.id_common, tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . ' WHERE ft.type_field = tft.type_field'
            . " AND ft.id_common = '" . $id_common . "'"
            . ' ORDER BY ft.sequence';

        $res = [];
        $rs = sql_query($query);
        if (!$rs) {
            return $res;
        }

        if (sql_num_rows($rs) < 1) {
            return $res;
        }
        list($id_common, $type_file, $type_class) = sql_fetch_row($rs);
        require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
        $quest_obj = eval("return new $type_class( $id_common );");
        if ($this->field_entry_table !== false) {
            $quest_obj->setFieldEntryTable($this->field_entry_table);
        }

        $quest_obj->setMainTable($this->getFieldTable());

        return $quest_obj->getSon();
    }

    /**
     * @param array $idst_user_arr idst to be associated to the user
     * @param int   $id_field_arr  id of the field to get
     *
     * @return array with values for the specified fields for each user
     *               array[user_idst][field_idcommon]=field_value
     *               you can find an usage example in /lib/lib.usernotifier.php
     **/
    public function showFieldForUserArr($idst_user_arr, $id_field_arr)
    {
        $query = 'SELECT ft.id_common, tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . ' WHERE ft.type_field = tft.type_field'
            . '   AND ft.id_common IN (' . implode(',', $id_field_arr) . ')'
            . ' ORDER BY ft.sequence';

        $res = [];

        $rs = sql_query($query);
        if ($rs == false) {
            return 'NULL';
        }

        if (sql_num_rows($rs) < 1) {
            return 'NULL';
        }

        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());

            $lang = Lang::get();
            foreach ($idst_user_arr as $idst_user) {
                if (!$this->getUseMultiLang()) {
                    $res[$idst_user][$id_common] = $quest_obj->show($idst_user);
                } else {
                    $res[$idst_user][$id_common] = $quest_obj->showInLang($idst_user, $lang);
                }
            }
        }

        return $res;
    }

    /**
     * @param array $idst_user_arr idst to be associated to the user
     * @param int   $id_field_arr  id of the field to get
     *
     * @return array with values for the specified fields for each user
     *               array[user_idst][field_idcommon]=field_value
     *               you can find an usage example in /lib/lib.usernotifier.php
     **/
    public function fieldValue($id_field, $idst_user_arr)
    {
        $query = 'SELECT ft.id_common, tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . ' WHERE ft.type_field = tft.type_field'
            . "   AND ft.id_common = '" . $id_field . "'"
            . ' ORDER BY ft.sequence';

        $res = [];

        $rs = sql_query($query);
        if ($rs == false) {
            return 'NULL';
        }

        if (sql_num_rows($rs) < 1) {
            return 'NULL';
        }

        list($id_common, $type_file, $type_class) = sql_fetch_row($rs);

        require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
        $quest_obj = new $type_class($id_common);
        if ($this->field_entry_table !== false) {
            $quest_obj->setFieldEntryTable($this->field_entry_table);
        }

        $quest_obj->setMainTable($this->getFieldTable());

        $lang = Lang::get();
        foreach ($idst_user_arr as $idst_user) {
            if (!$this->getUseMultiLang()) {
                $res[$idst_user] = $quest_obj->show($idst_user);
            } else {
                $res[$idst_user] = $quest_obj->showInLang($idst_user, $lang);
            }
        }

        return $res;
    }

    /**
     * @param int  $id_st     idst to be associated to the user
     * @param int  $id_field  id of the field to get
     * @param bool $freeze    TRUE to get static text, false to get input control
     * @param bool $mandatory specified if the field is a mandatory one or not
     *
     * @return html with the form code for play a set of fields
     **/
    public function playFieldForUser($idst_user, $id_field, $freeze, $mandatory = false)
    {
        $query = 'SELECT tft.type_file, tft.type_class'
            . '  FROM ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft'
            . ' WHERE ft.type_field = tft.type_field'
            . "   AND ft.id_common = '" . $id_field . "'"
            . ' ORDER BY ft.sequence';

        $rs = sql_query($query);

        if (sql_num_rows($rs) < 1) {
            return 'NULL';
        }
        list($type_file, $type_class) = sql_fetch_row($rs);
        require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
        $quest_obj = eval("return new $type_class( $id_field );");
        if ($this->field_entry_table !== false) {
            $quest_obj->setFieldEntryTable($this->field_entry_table);
        }

        $quest_obj->setMainTable($this->getFieldTable());
        if (!$this->getUseMultiLang()) {
            return $quest_obj->play($idst_user, $freeze, $mandatory);
        } else {
            return $quest_obj->multiLangPlay($idst_user, $freeze, $mandatory);
        }
    }

    /**
     * @param int   $id_st    idst to be associated to the user
     * @param array $arr_idst (optional) array of idst of groups
     *                        if this parameter is skipped the groups will be taken
     *                        from $idst_user
     *
     * @return string with the form code for play a set of fields
     **/
    public function playFieldsForUser($idst_user, $arr_idst = false, $freeze = false, $add_root = true, $useraccess = false, $separate_output = false, $check_precompiled = false, $registrationLayout = false, $registrationErrors = false)
    {
        $acl = \FormaLms\lib\Forma::getAcl();
        $index = 0;
        if ($arr_idst === false) {
            $arr_idst = $acl->getArrSTGroupsST($acl->getUserGroupsST($idst_user));
            $index += count($arr_idst);
        }

        if ($add_root) {
            $acl_man = \FormaLms\lib\Forma::getAclManager();
            $tmp = $acl_man->getGroup(false, '/oc_0');
            $arr_idst[] = (int) $tmp[0];
            $tmp = $acl_man->getGroup(false, '/ocd_0');
            $arr_idst[] = (int) $tmp[0];
            $index += 2;
        }

        if (!$registrationLayout) {
            if (count($arr_idst) >= $index) {
                // Not only roots ocd_0 and oc_0
                for ($i = 0, $iMax = count($arr_idst); $i < $iMax; ++$i) {
                    if ($arr_idst[$i] == 1) {
                        unset($arr_idst[$i]);
                    }
                }
            }
        }

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class, gft.mandatory'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . '   AND ft.id_common = gft.id_field'
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')";

        switch (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId()) {
            case ADMIN_GROUP_ADMIN:
            case ADMIN_GROUP_USER:
                $query .= "   AND gft.useraccess <> 'readwrite'"; // Hide invisible;
                break;
            default:
                break;
        }

        if ($useraccess !== 'false' && is_array($useraccess)) {
            $query .= ' AND ( ';
            $first = true;
            foreach ($useraccess as $k => $v) {
                if (!$first) {
                    $query .= ' OR ';
                } else {
                    $first = false;
                }
                $query .= " gft.useraccess = '" . $v . "' ";
            }
            $query .= ' ) ';
        }
        $query .= ' GROUP BY ft.id_common '
            . ' ORDER BY ft.sequence, gft.idst, gft.id_field';

        $play_txt = [];
        $re_fields = sql_query($query);

        $precompiled = false;
        if ($check_precompiled > 0) {
            $precompiled = $this->getInheritedAdminFields($check_precompiled);
        }

        if (!sql_num_rows($re_fields)) {
            return '';
        }

        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            $mandatory = $row['mandatory'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $field_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $field_obj->setFieldEntryTable($this->field_entry_table);
            }

            $field_obj->setMainTable($this->getFieldTable());
            if (!$this->getUseMultiLang()) {
                $precompiled_value = is_array($precompiled) && isset($precompiled[$id_common]) ? $precompiled[$id_common] : null;
                $play_txt[$id_common] = $field_obj->play($idst_user, $freeze, $this->_mandatoryField($mandatory), false, $precompiled_value, $registrationLayout, $registrationErrors);
            } else {
                $precompiled_value = is_array($precompiled) && isset($precompiled[$id_common]) ? $precompiled[$id_common] : null;
                $play_txt[$id_common] = $field_obj->multiLangPlay($idst_user, $freeze, $this->_mandatoryField($mandatory), false, $precompiled_value, $registrationLayout, $registrationErrors);
            }
        }

        return $separate_output ? $play_txt : implode('', array_values($play_txt));
    }

    /**
     * @param array $idst_user_arr idst to be associated to the user
     * @param int   $id_field_arr  id of the field to get
     *
     * @return array with values for the specified fields for each user
     *               array[user_idst][field_idcommon]=field_value
     *               you can find an usage example in /lib/lib.usernotifier.php
     **/
    public function hiddenFieldForUserArr($idst_user, $arr_idst = false, $freeze = false, $add_root = true, $useraccess = false)
    {
        $index = 0;
        $acl = \FormaLms\lib\Forma::getAcl();
        if ($arr_idst === false) {
            $arr_idst = $acl->getUserGroupsST($idst_user);
            $index = count($arr_idst);
        }

        if ($add_root) {
            $acl_man = \FormaLms\lib\Forma::getAclManager();
            $tmp = $acl_man->getGroup(false, '/oc_0');
            $arr_idst[] = $tmp[0];
            $tmp = $acl_man->getGroup(false, '/ocd_0');
            $arr_idst[] = $tmp[0];
            $index += 2;
        }

        if (count($arr_idst) > $index) {
            // Not only roots ocd_0 and oc_0
            for ($i = 0, $iMax = count($arr_idst); $i < $iMax; ++$i) {
                if ($arr_idst[$i] == 1) {
                    unset($arr_idst[$i]);
                }
            }
        }

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class, gft.mandatory'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . '   AND ft.id_common = gft.id_field'
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')";

        if ($useraccess !== 'false' && is_array($useraccess)) {
            $query .= ' AND ( ';
            $first = true;
            foreach ($useraccess as $k => $v) {
                if (!$first) {
                    $query .= ' OR ';
                } else {
                    $first = false;
                }
                $query .= " gft.useraccess = '" . $v . "' ";
            }
            $query .= ' ) ';
        }
        $query .= ' GROUP BY ft.id_common '
            . ' ORDER BY ft.sequence, gft.idst, gft.id_field';

        $play_txt = '';
        $re_fields = sql_query($query);

        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            $mandatory = $row['mandatory'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());

            $play_txt .= $quest_obj->get_hidden_filled(false, false);
        }

        return $play_txt;
    }

    /**
     * @param int   $id_st    idst to be associated to the user
     * @param array $arr_idst (optional) array of idst of groups
     *                        if this parameter is skipped the groups will be taken
     *                        from $idst_user
     *
     * @return true if all the mandatory field is filled and all field is valid, an array with the error messsage
     **/
    public function isFilledFieldsForUser($idst_user, $arr_idst = false)
    {
        $acl = \FormaLms\lib\Forma::getAcl();
        $error_message = [];

        // #BUG - 19799
        $acl_man = \FormaLms\lib\Forma::getAclManager();

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $selectedNode = $session->get('usermanagement_selected_node');

        if (!empty($selectedNode) && $selectedNode != 0) {
            $arr_idst = [];
            $tmp = $acl_man->getGroup(false, '/oc_' . $selectedNode);
            $arr_idst[] = $tmp[0];
            $tmp = $acl_man->getGroup(false, '/ocd_' . $selectedNode);
            $arr_idst[] = $tmp[0];
            $acl = \FormaLms\lib\Forma::getAcl();
            $arr_idst = $acl->getArrSTGroupsST($arr_idst);
        }

        $index = 0;
        if ($arr_idst === false) {
            $arr_idst = $acl->getArrSTGroupsST($acl->getUserGroupsST($idst_user));
            $index += count($arr_idst);
        }

        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $tmp = $acl_man->getGroup(false, '/oc_0');
        $arr_idst[] = $tmp[0];
        $tmp = $acl_man->getGroup(false, '/ocd_0');
        $arr_idst[] = $tmp[0];
        $index += 2;

        if (count($arr_idst) > $index) {
            // Not only roots ocd_0 and oc_0
            for ($i = 0, $iMax = count($arr_idst); $i < $iMax; ++$i) {
                if ($arr_idst[$i] == 1) {
                    unset($arr_idst[$i]);
                }
            }
        }

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class, gft.mandatory'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '     AND ft.type_field = tft.type_field'
            . '   AND ft.id_common = gft.id_field'
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')";

        $query .= ' GROUP BY ft.id_common ';

        $mandatory_filled = true;
        $field_valid = true;
        $re_fields = sql_query($query);
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            $id_mandatory = $row['mandatory'];
            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = new $type_class($id_common);

            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());

            if (!$quest_obj->isValid($idst_user)) {
                $error_text = $quest_obj->getLastError();
                if ($error_text !== false) {
                    $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), $error_text);
                } else {
                    $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_FIELD_VALUE_NOT_VALID', 'field', 'framework'));
                }
            } elseif ($is_mandatory == 'true' && !$quest_obj->isFilled($idst_user)) {
                $error_text = $quest_obj->getLastError();
                if ($error_text !== false) {
                    $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), $error_text);
                } else {
                    $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_SOME_MANDATORY_EMPTY', 'register', 'framework'));
                }
            }
        }
        if (empty($error_message)) {
            return true;
        }

        return $error_message;
    }

    /**
     * @param int   $id_st    idst to be associated to the user
     * @param array $arr_idst (optional) array of idst of groups
     *                        if this parameter is skipped the groups will be taken
     *                        from $idst_user
     *
     * @return true if all the mandatory field is filled and all field is valid, an array with the error messsage
     **/
    public function isFilledFieldsForUserInRegistration($idst_user, $arr_idst = false)
    {
        $index = 0;
        $acl = \FormaLms\lib\Forma::getAcl();
        if ($arr_idst === false) {
            $arr_idst = $acl->getUserGroupsST($idst_user);
            $index = count($arr_idst);
        }
        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $tmp = $acl_man->getGroup(false, '/oc_0');
        $arr_idst[] = $tmp[0];
        $tmp = $acl_man->getGroup(false, '/ocd_0');
        $arr_idst[] = $tmp[0];
        $index += 2;

        if (count($arr_idst) > $index) {
            // Not only roots ocd_0 and oc_0
            for ($i = 0, $iMax = count($arr_idst); $i < $iMax; ++$i) {
                if ($arr_idst[$i] == 1) {
                    unset($arr_idst[$i]);
                }
            }
        }

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class, gft.mandatory'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . '   AND ft.id_common = gft.id_field'
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')"
            . ' GROUP BY ft.id_common ';

        $error_message = [];

        $mandatory_filled = true;
        $field_valid = true;
        $re_fields = sql_query($query);
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];
            $is_mandatory = $row['mandatory'];
            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = new $type_class($id_common);

            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());

            if (!$quest_obj->isValid($idst_user)) {
                $error_text = $quest_obj->getLastError();
                if ($error_text !== false) {
                    $error_message[$id_common] = [
                        'error' => true,
                        'msg' => str_replace('[field_name]', $quest_obj->getFieldName(), $error_text),
                    ];
                } else {
                    $error_message[$id_common] = [
                        'error' => true,
                        'msg' => str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_FIELD_VALUE_NOT_VALID', 'field', 'framework')),
                    ];
                }
            } elseif ($is_mandatory == 'true' && !$quest_obj->isFilled($idst_user)) {
                $error_text = $quest_obj->getLastError();
                if ($error_text !== false) {
                    $error_message[$id_common] = [
                        'error' => true,
                        'msg' => str_replace('[field_name]', $quest_obj->getFieldName(), $error_text),
                    ];
                } else {
                    $error_message[$id_common] = [
                        'error' => true,
                        'msg' => str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_SOME_MANDATORY_EMPTY', 'register', 'framework')),
                    ];
                }
            }
        }
        if (empty($error_message)) {
            return true;
        }

        return $error_message;
    }

    /**
     * @param int   $id_st    idst to be associated to the user
     * @param array $arr_idst (optional) array of idst of groups
     *                        if this parameter is skipped the groups will be taken
     *                        from $idst_user
     *
     * @return true if success, FALSE otherwise
     **/
    public function storeFieldsForUser($idst_user, $arr_idst = false, $add_root = true, $int_userid = true)
    {
        $index = 0;
        $acl = \FormaLms\lib\Forma::getAcl();
        if ($arr_idst === false) {
            $arr_idst = $acl->getUserGroupsST($idst_user);
            $index = count($arr_idst);
        }

        if ($add_root) {
            $acl_man = \FormaLms\lib\Forma::getAclManager();
            $tmp = $acl_man->getGroup(false, '/oc_0');
            $arr_idst[] = $tmp[0];
            $tmp = $acl_man->getGroup(false, '/ocd_0');
            $arr_idst[] = $tmp[0];
            $index += 2;
        }

        if (count($arr_idst) > $index) {
            // Not only roots ocd_0 and oc_0
            for ($i = 0, $iMax = count($arr_idst); $i < $iMax; ++$i) {
                if ($arr_idst[$i] == 1) {
                    unset($arr_idst[$i]);
                }
            }
        }

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . '   AND ft.id_common = gft.id_field'
            . "   AND gft.idst IN ('" . implode("','", $arr_idst) . "')"
            . ' GROUP BY ft.id_common ';

        $save_result = true;
        $re_fields = sql_query($query);
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            if (!$this->getUseMultiLang()) {
                $save_result &= $quest_obj->store($idst_user, false, $int_userid);
            } else {
                $save_result &= $quest_obj->multiLangStore($idst_user, false, $int_userid);
            }
        }

        return $save_result;
    }

    /**
     * @param int $id_st idst to be associated to the user
     * @param array array of fields to be set idfield=>value
     * @param bool $is_id if true will consider the passed data as the field id;
     *                    else the value is taken and reconverted to the id
     *
     * @return true if success, FALSE otherwise
     **/
    public function storeDirectFieldsForUser($idst_user, $arr_fields, $is_id = false, $int_userid = true)
    {
        //return is_numeric($idst_user) && (int)$idst_user > 0 ? $this->storeDirectFieldsForUsers((int)$idst_user, $arr_fields, $is_id, $int_userid) : FALSE;

        $acl = \FormaLms\lib\Forma::getAcl();

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . "   AND ft.id_common IN ('" . implode("','", array_keys($arr_fields)) . "')"
            . ' GROUP BY ft.id_common ';

        $save_result = true;
        $re_fields = sql_query($query);
        if ($re_fields === false) {
            return false;
        }
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            if (!$this->getUseMultiLang()) {
                $save_result &= $quest_obj->storeDirect($idst_user, $arr_fields[$id_common], $is_id, false, $int_userid);
            } else {
                $save_result &= $quest_obj->multiLangStoreDirect($idst_user, $arr_fields[$id_common], $is_id, false, $int_userid);
            }
        }

        return $save_result;
    }

    /**
     * @param int/array $idst_users list of idst to be associated to the users
     * @param array array of fields to be set idfield=>value
     * @param bool $is_id if true will consider the passed data as the field id;
     *                    else the value is taken and reconverted to the id
     *
     * @return true if success, FALSE otherwise
     **/
    public function storeDirectFieldsForUsers($idst_users, $arr_fields, $is_id = false, $int_userid = true)
    {
        if (is_numeric($idst_users)) {
            $idst_users = [$idst_users];
        }
        if (!is_array($idst_users)) {
            return false;
        }
        if (empty($idst_users)) {
            return true;
        }

        $acl = \FormaLms\lib\Forma::getAcl();

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . "   AND ft.id_common IN ('" . implode("','", array_keys($arr_fields)) . "')"
            . ' GROUP BY ft.id_common ';

        $save_result = true;
        $re_fields = sql_query($query);
        if ($re_fields === false) {
            return false;
        }
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = new $type_class($id_common);
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }
            $quest_obj->setMainTable($this->getFieldTable());
            if (!$this->getUseMultiLang()) {
                $save_result &= $quest_obj->storeDirectMultiple($idst_users, $arr_fields[$id_common], $is_id, false, $int_userid);
            } else {
                $save_result &= $quest_obj->multiLangStoreDirectMultiple($idst_users, $arr_fields[$id_common], $is_id, false, $int_userid);
            }
        }

        return $save_result;
    }

    /**
     * @param array $arr_field
     * @param array $custom_mandatory (optional)
     *
     * @return html with the form code for play a set of specified fields
     **/
    public function playSpecFields($arr_field, $custom_mandatory = false, $user_id = false)
    {
        $acl = \FormaLms\lib\Forma::getAcl();

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            //				."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            //				."   AND ft.id_common = gft.id_field"
            . "   AND ft.id_common IN ('" . implode("','", $arr_field) . "')";

        $query .= ' GROUP BY ft.id_common ';
        //				." ORDER BY ft.sequence";

        if ($user_id === false || empty($user_id)) {
            $user_id = -1;
        }

        $play_txt = '';
        $play_arr = [];
        $re_fields = sql_query($query);

        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (isset($custom_mandatory[$id_common]) && $custom_mandatory[$id_common]) {
                $mandatory = true;
            } else {
                $mandatory = false;
            }

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            if (!$this->getUseMultiLang()) {
                $play_arr[$id_common] = $quest_obj->play($user_id, false, $this->_mandatoryField($mandatory));
            } else {
                $play_arr[$id_common] = $quest_obj->multiLangPlay($user_id, false, $this->_mandatoryField($mandatory));
            }
        }

        // This way we get it in the order passed in the $arr_field array:
        foreach ($arr_field as $key => $val) {
            if (isset($play_arr[$val])) {
                $play_txt .= $play_arr[$val];
            }
        }

        return $play_txt;
    }

    public function playFilters($arr_field, $values, $field_prefix = false)
    {
        $res = '';

        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . "   AND ft.id_common IN ('" . implode("','", $arr_field) . "')";
        $query .= ' GROUP BY ft.id_common ';

        $re_fields = sql_query($query);
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            $value = isset($values[$id_common]) ? $values[$id_common] : false;
            $res .= $quest_obj->play_filter($id_common, $value, false, $field_prefix);
        }

        return $res;
    }

    /**
     * @param array $arr_field array of field id that are mandatory
     *
     * @return true if all the mandatory field is filled, FALSE otherwise
     **/
    public function isFilledSpecFields($arr_field)
    {
        $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . "   AND ft.id_common IN ('" . implode("','", $arr_field) . "')"
            . ' GROUP BY ft.id_common ';

        $save_result = true;
        $re_fields = sql_query($query);
        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            $save_result &= $quest_obj->isFilled(-1);
        }

        return $save_result;
    }

    /**
     * @param array $arr_field
     * @param array $grab_form    (optional)
     * @param bool  $dropdown_val (optional). If true will get the value of a dropdown item instead of its id.
     *
     * @return array with the filled value of the specified fields
     **/
    public function getFilledSpecVal($arr_field, $grab_from = false, $dropdown_val = false)
    {
        if ($grab_from === false) {
            $grab_from = $_POST;
        }

        $query = 'SELECT ft.id_common, ft.translation, ft.type_field, tft.type_file, tft.type_class'
            . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
            . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
            . " WHERE ft.lang_code = '" . Lang::get() . "'"
            . '	 AND ft.type_field = tft.type_field'
            . "   AND ft.id_common IN ('" . implode("','", $arr_field) . "')"
            . ' GROUP BY ft.id_common ';

        $filled_val = [];
        $re_fields = sql_query($query);

        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $translation = $row['translation'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            $filled_val[$id_common]['description'] = $translation;

            if ($type_field == 'dropdown') {
                $filled_val[$id_common]['value'] = $quest_obj->getFilledVal($grab_from, $dropdown_val);
            } else {
                $filled_val[$id_common]['value'] = $quest_obj->getFilledVal($grab_from);
            }
        }

        return $filled_val;
    }

    /**
     * @param int   $id_field
     * @param array $associate_owner if true the owner of the data is associated
     *
     * @return array with the stored value of the specific field
     **/
    public function getAllStoredValue($id_field, $associate_owner = false)
    {
        $query = '
		SELECT DISTINCT user_entry ' . ($associate_owner === true ? ', id_user' : '') . '
		FROM ' . $this->getFieldEntryTable() . "
		WHERE id_common = '" . $id_field . "'";
        $rs = sql_query($query);

        $result = [];
        while ($data = sql_fetch_row($rs)) {
            if ($associate_owner === true) {
                $result[$data[1]] = $data[0];
            } else {
                $result[] = $data[0];
            }
        }

        return $result;
    }

    /**
     * @param int $id_field id of the field to be associated to $id_st
     * @param int $id_st    idst to be associated to field
     *
     * @return true if success, FALSE otherwise
     **/
    public function addFieldToGroup($id_field, $idst, $mandatory = 'false', $useraccess = 'readonly', $user_inherit = '0')
    {
        $query = 'SELECT idst FROM ' . $this->getGroupFieldsTable()
            . " WHERE idst = '" . $idst . "' AND id_field = '" . $id_field . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) > 0) {
            $query = 'UPDATE ' . $this->getGroupFieldsTable()
                . " SET idst = '" . (int) $idst . "',"
                . "     id_field = '" . (int) $id_field . "',"
                . "     mandatory = '" . $mandatory . "',"
                . "     useraccess = '" . $useraccess . "', "
                . "     user_inherit = '" . ((int) $user_inherit > 0 ? '1' : '0') . "' "
                . " WHERE idst = '" . $idst . "' AND id_field = '" . $id_field . "'";
        } else {
            $query = 'INSERT INTO ' . $this->getGroupFieldsTable()
                . ' (idst, id_field, mandatory, useraccess, user_inherit) '
                . " VALUES ('" . (int) $idst . "','" . (int) $id_field . "',"
                . "'" . $mandatory . "','" . $useraccess . "', '" . ((int) $user_inherit > 0 ? '1' : '0') . "')";
        }

        return sql_query($query);
    }

    /**
     * @param int $id_field id of the field to be removed from $id_st
     * @param int $id_st    idst to be removed to field
     *
     * @return true if success, FALSE otherwise
     **/
    public function removeFieldFromGroup($id_field, $idst)
    {
        $query = 'DELETE FROM ' . $this->getGroupFieldsTable()
            . " WHERE idst = '" . (int) $idst . "'"
            . "   AND id_field = '" . (int) $id_field . "'";

        return sql_query($query);
    }

    public function quickRemoveUserEntry($idst_user)
    {
        $query_del = "DELETE FROM %adm_field_userentry 
			WHERE id_user = '" . (int) $idst_user . "'";

        return sql_query($query_del);
    }

    /**
     * @param int   $idst_user  the user
     * @param int   $id_group   cast the delete action only to the field of this group
     * @param array $arr_fields cast the delete action only to the field specified
     *
     * @return true if success, FALSE otherwise
     **/
    public function removeUserEntry($idst_user, $id_group = false, $arr_field = false)
    {
        $save_result = true;
        $arr_idst = [];
        if ($arr_field !== false) {
            $to_remove = &$arr_field;
        } elseif ($id_group !== false) {
            $acl = \FormaLms\lib\Forma::getAcl();
            $allgroup_idst = $acl->getUserGroupsST($idst_user);
            // Leave the passed group
            $inc_group = array_search($id_group, $allgroup_idst);
            unset($allgroup_idst[$inc_group]);

            if (!empty($allgroup_idst)) {
                $query = 'SELECT gft.id_field '
                    . '  FROM ' . $this->getGroupFieldsTable() . ' AS gft'
                    . " WHERE gft.idst IN ('" . implode("','", $allgroup_idst) . "')";
                $rs = sql_query($query);
                $result = [];
                foreach ($rs as $row) {
                    $all_field[$row['id_field']] = $row['id_field'];
                }
            }
            $query = 'SELECT gft.id_field '
                . '  FROM ' . $this->getGroupFieldsTable() . ' AS gft'
                . " WHERE gft.idst = '" . $id_group . "'";
            $rs = sql_query($query);
            $to_remove = [];
            foreach ($rs as $row) {
                if (!isset($all_field[$row['id_field']])) {
                    $to_remove[] = $row['id_field'];
                }
            }
        }

        if (empty($to_remove)) { // no group or fields specified, so we remove all..
            //return $save_result;

            $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
                . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
                . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
                . " WHERE ft.lang_code = '" . Lang::get() . "'"
                . '	 AND ft.type_field = tft.type_field'
                . ' GROUP BY ft.id_common ';
        } else {    // remove specific fields
            $query = 'SELECT ft.id_common, ft.type_field, tft.type_file, tft.type_class'
                . '  FROM ( ' . $this->getFieldTable() . ' AS ft'
                . '  JOIN ' . $this->getTypeFieldTable() . ' AS tft )'
                . '  JOIN ' . $this->getGroupFieldsTable() . ' AS gft'
                . " WHERE ft.lang_code = '" . Lang::get() . "'"
                . '	 AND ft.type_field = tft.type_field'
                . '   AND ft.id_common = gft.id_field'
                . "   AND gft.idst IN ('" . implode("','", $to_remove) . "')"
                . ' GROUP BY ft.id_common ';
        }

        $re_fields = sql_query($query);

        foreach ($re_fields as $row) {
            $id_common = $row['id_common'];
            $type_field = $row['type_field'];
            $type_file = $row['type_file'];
            $type_class = $row['type_class'];

            if (!class_exists($type_class)) {
                require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/' . $type_file);
            }
            $quest_obj = eval("return new $type_class( $id_common );");
            if ($this->field_entry_table !== false) {
                $quest_obj->setFieldEntryTable($this->field_entry_table);
            }

            $quest_obj->setMainTable($this->getFieldTable());
            $save_result &= $quest_obj->deleteUserEntry($idst_user);
        }

        return $save_result;
    }

    /**
     * Find wich users entries matches with search information.
     *
     * @param array  $fields     list of id_common values
     * @param string $method     "OR" or "AND"
     * @param array  $like       array($id_common => [off, both, start, end])
     * @param array  $search     array($id_common => $what_to_search)
     * @param bool   $return_raw if TRUE then will return the raw array
     *
     * @return array list of user idst found (if $return_raw is FALSE)
     *
     * @author Giovanni Derks
     */
    public function quickSearchUsersFromEntry($fields, $method, $like, $search, $return_raw = false)
    {
        $res = [];

        if (FormaLms\lib\Get::sett('do_debug') == 'on' && count($fields) != count($search)) {
            echo '<b>Warning</b>: (lib.field.php) ';
            echo 'Please make sure that the search array have the same size of the fields one.<br />';
        }

        // -------------------------

        $qtxt = 'SELECT * FROM ' . $this->getFieldEntryTable() . ' ';
        $qtxt .= 'WHERE id_common IN (' . implode(',', $fields) . ') AND (';

        $where_arr = [];
        foreach ($fields as $id_common) {
            $where = '';

            if (isset($search[$id_common])) {
                $where .= "(id_common='" . $id_common . "' AND user_entry ";

                $search_val = $search[$id_common];

                if (!isset($like[$id_common]) || $like[$id_common] == 'off') {
                    $where .= "='" . $search_val . "'";
                } elseif ($like[$id_common] == 'both') {
                    $where .= " LIKE '%" . $search_val . "%'";
                } elseif ($like[$id_common] == 'start') {
                    $where .= " LIKE '%" . $search_val . "'";
                } elseif ($like[$id_common] == 'end') {
                    $where .= " LIKE '" . $search_val . "%'";
                }

                $where .= ')';

                $where_arr[] = $where;
            }
        }

        $qtxt .= implode(' OR ', $where_arr);

        $qtxt .= ')';

        // -------------------------

        $q = sql_query($qtxt);

        $raw_res = [];
        $raw_res['field'] = [];
        $raw_res['user'] = [];
        if ($q && sql_num_rows($q) > 0) {
            foreach ($q as $row) {
                $id_common = $row['id_common'];
                $id_user = $row['id_user'];

                // ----------------------------------------------------------

                if (!isset($raw_res[$id_common])) {
                    $raw_res['field'][$id_common] = [];
                }

                if (!in_array($id_user, $raw_res['field'][$id_common])) {
                    $raw_res['field'][$id_common][] = $id_user;
                }

                // ----------------------------------------------------------

                if (!isset($raw_res['user'][$id_user])) {
                    $raw_res['user'][$id_user] = [];
                }

                if (!in_array($id_common, $raw_res['user'][$id_user])) {
                    $raw_res['user'][$id_user][] = $id_common;
                }

                // ----------------------------------------------------------

                if ($method == 'OR' && !in_array($row['id_user'], $res)) {
                    $res[] = $row['id_user'];
                }
            }
        }

        if ($return_raw) {
            return $raw_res;
        } elseif ($method == 'AND') {
            $tot = count($fields);
            foreach ($raw_res['user'] as $user_id => $field_arr) {
                $tot_found = count($field_arr);
                if ($tot_found > 0 && $tot_found == $tot) {
                    $res[] = $user_id;
                }
            }
        }

        return $res;
    }

    public function getFieldIdCommonFromTranslation($translation)
    {
        $query = 'SELECT id_common' .
            ' FROM ' . $this->getFieldTable() . '' .
            " WHERE translation LIKE '" . $translation . "'";

        list($res) = sql_fetch_row(sql_query($query));

        return $res;
    }

    public function getFieldTypesList()
    {
        $db = \FormaLms\db\DbConn::getInstance();
        $query = 'SELECT * FROM ' . $this->getTypeFieldTable();

        if (!$rs = $db->query($query)) {
            $false_var = null;

            return $false_var;
        }

        $output = [];
        foreach ($rs as $row) {
            $output[] = $row;
        }

        return $output;
    }

    public function getFieldTypeById($field_id)
    {
        $db = \FormaLms\db\DbConn::getInstance();
        $query = 'SELECT type_field FROM ' . $this->getFieldTable() . " WHERE id_common='$field_id'";

        if (!$rs = $db->query($query)) {
            $false_var = null;

            return $false_var;
        }

        $output = false;
        $temp = $db->fetch_row($rs);
        if (count($temp) > 0) {
            $output = $temp[0];
        }

        return $output;
    }

    //----------------------------------------------------------------------------
    public function checkUserMandatoryFields($id_user = false, $only_accessible = false)
    {
        $id_user = $id_user ? (int) $id_user : \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();
        $acl = new FormaACL();
        $user_groups = $acl->getUserGroupsST($id_user);
        $output = true;

        if (!empty($user_groups)) {
            if (count($user_groups) > 2 && isset($user_groups[1]) && isset($user_groups[2])) {
                // Not only roots ocd_0 and oc_0
                unset($user_groups[1]);
            }
            //extract mandatory fields and checks if there are any fields with null value (not compiled)
            $query = 'SELECT ft.id_common, gft.useraccess, fet.user_entry '
                . ' FROM (' . $this->getFieldTable() . ' AS ft '
                . ' JOIN ' . $this->getGroupFieldsTable() . ' AS gft '
                . " ON (ft.id_common = gft.id_field AND ft.lang_code = '" . Lang::get() . "')) "
                . ' LEFT JOIN ' . $this->getFieldEntryTable() . ' AS fet '
                . ' ON (fet.id_common = ft.id_common AND fet.id_user = ' . (int) $id_user . ') '
                . ' WHERE gft.mandatory = 1 '
                . ($only_accessible ? " AND gft.useraccess = 'readwrite'" : '')
                . " AND gft.idst IN ('" . implode("','", $user_groups) . "')";
            $res = sql_query($query);

            if ($res) {
                if (sql_num_rows($res) == 0) {
                    return true;
                }
                foreach ($res as $row) {
                    if (!$row['user_entry']) {
                        $output = false;
                        break;
                    }
                }
            }
        }

        return $output;
    }

    public function getUserMandatoryFields($id_user)
    {
        $acl = new FormaACL();
        $user_groups = $acl->getUserGroupsST($id_user);

        $output = [];

        if (!empty($user_groups)) {
            if (count($user_groups) > 2 && isset($user_groups[1]) && isset($user_groups[2])) {
                // Not only roots ocd_0 and oc_0
                unset($user_groups[1]);
            }

            $query = 'SELECT ft.id_common, ft.translation, ft.type_field, gft.useraccess, fet.user_entry '
                . ' FROM (' . $this->getFieldTable() . ' AS ft '
                . ' JOIN ' . $this->getGroupFieldsTable() . ' AS gft '
                . ' JOIN ' . $this->getTypeFieldTable() . ' AS ftt '
                . " ON (ft.id_common = gft.id_field AND ft.lang_code = '" . Lang::get() . "' AND ft.type_field = ftt.type_field)) "
                . ' LEFT JOIN ' . $this->getFieldEntryTable() . ' AS fet '
                . ' ON (fet.id_common = ft.id_common AND fet.id_user = ' . (int) $id_user . ') '
                . " WHERE gft.idst IN ('" . implode("','", $user_groups) . "') "
                . ' AND gft.mandatory = 1 '
                . ' ORDER BY ft.sequence';
            $res = sql_query($query);

            if ($res) {
                foreach ($res as $row) {
                    $output[$row['id_common']] = [
                        'translation' => $row['translation'],
                        'type_field' => $row['type_field'],
                        'useraccess' => $row['useraccess'],
                        'user_entry' => $row['user_entry'],
                    ];
                }
            }
        }

        return $output;
    }

    public function getFieldsByType($type)
    {
        $output = false;
        $query = 'SELECT id_common FROM ' . $this->getFieldTable() . " WHERE type_field = '" . $type . "'";
        $res = sql_query($query);
        if ($res) {
            $output = [];
            foreach ($res as $row) {
                $output[] = $row['id_common'];
            }
        }

        return $output;
    }

    public function getInheritedAdminFields($id_admin)
    {
        if ($id_admin <= 0) {
            return false;
        }

        $output = [];

        //retrieve admin's groups and read groups associated fields
        $groups = [];
        $query = 'SELECT gm.idst FROM %adm_group_members AS gm JOIN %adm_group AS g ON (gm.idst = g.idst) '
            . " WHERE (g.groupid LIKE '/oc\_%' OR g.groupid LIKE '/ocd\_%' ) AND gm.idstMember = " . (int) $id_admin;
        $res = sql_query($query);
        foreach ($res as $row) {
            $groups[] = (int) $row['idst'];
        }

        if (!empty($groups)) {
            $fields = [];
            $query = 'SELECT * FROM ' . $this->getGroupFieldsTable() . ' '
                . ' WHERE idst IN (' . implode(',', $groups) . ') '
                . ' AND user_inherit = 1';
            $res = sql_query($query);
            foreach ($res as $row) {
                $fields[] = $row['id_field'];
            }

            if (!empty($fields)) {
                $output = $this->getUserFieldEntryData($id_admin, $fields);
            }
        }

        return $output;
    }
}
