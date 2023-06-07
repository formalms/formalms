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
 * @version  $Id: class.field.php 985 2007-02-28 16:52:50Z giovanni $
 *
 * @category Field
 *
 * @author   Fabio Pirovano <fabio@docebo.com>
 * @abstract
 */

/**
 * ABSTRACT class for field implementation.
 **/
class Field
{
    /**
     * @var int contains the question identifier
     */
    public $id_common;

    /** @var string the field entry table name */
    public $field_entry_table;

    public $_url;

    public $field_son_table;
    public $field_main_table;

    // Array of default platform that has to be selected for
    // show in platform value; if can_select_platform is false the
    // values will be set as hidden fields.
    public $show_on_platform_default = [];
    // If true shows a list of checkbox that allow the user to specify in
    // wich platforms the field will be available.
    public $can_select_platform = true;

    public $use_multi_lang = false;

    public $_last_error = false;

    /**
     * class constructor.
     */
    public function __construct($id_common)
    {
        $this->field_entry_table = $GLOBALS['prefix_fw'] . '_field_userentry';
        $this->id_common = $id_common;

        $this->field_son_table = $GLOBALS['prefix_fw'] . '_field_son';
        $this->field_main_table = $GLOBALS['prefix_fw'] . '_field';
    }

    public function returnError($error_msg, $ret_value = false)
    {
        $this->_last_error = $error_msg;

        return $ret_value;
    }

    public function getLastError()
    {
        $error = $this->_last_error;
        $this->_last_error = false;

        return $error;
    }

    public function setUrl($url)
    {
        $this->_url = $url;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setShowOnPlatformDefaultArr($arr)
    {
        if (is_array($arr)) { // Set the keys of the array the same as the values
            foreach ($arr as $key => $val) {
                if (!isset($arr[$val])) {
                    $arr[$val] = $val;
                    unset($arr[$key]);
                }
            }
        } else {
            $arr = [];
        }

        $this->show_on_platform_default = $arr;
    }

    public function getShowOnPlatformDefaultArr()
    {
        return (array) $this->show_on_platform_default;
    }

    public function canSelectPlatform()
    {
        return false; //(bool)$this->can_select_platform;
    }

    public function setCanSelectPlatform($val)
    {
        $this->can_select_platform = (bool) $val;
    }

    public function getUseMultiLang()
    {
        return (bool) $this->use_multi_lang;
    }

    public function setUseMultiLang($val)
    {
        $this->use_multi_lang = (bool) $val;
    }

    public function getShowOnPlatformFieldset($show_on_platform = false)
    {
        $res = '';

        if ($this->canSelectPlatform()) {
            if ($show_on_platform === false) {
                $show_on_platform = $this->getShowOnPlatformDefaultArr();
            }

            $plt_man = &PlatformManager::createInstance();
            $plt_list = $plt_man->getPlatformList(true);

            $res .= Form::getOpenFieldset(Lang::t('_SHOW_ON_PLATFORM', 'field'));
            $res .= Form::getHidden('show_on_platform_framework', 'show_on_platform[framework]', 1);
            foreach ($plt_list as $code => $name) {
                $sel = (isset($show_on_platform[$code]) ? true : false);
                $res .= Form::getCheckbox($name, 'show_on_platform_' . $code, 'show_on_platform[' . $code . ']', 1, $sel);
            }

            $res .= Form::getCloseFieldset();
        } else {
            $res .= Form::getHidden('show_on_platform_framework', 'show_on_platform[framework]', 1);
            foreach ($this->getShowOnPlatformDefaultArr() as $code) {
                $res .= Form::getHidden('show_on_platform_' . $code, 'show_on_platform[' . $code . ']', 1);
            }
        }

        return $res;
    }

    public function getMultiLangCheck($use_multi_lang = false)
    {
        $res = '';

        $label = Lang::t('_USE_MULTI_LANG_WHEN_AVAILABLE', 'field');

        if ($this->getUseMultiLang()) {
            $res .= Form::getCheckBox($label, 'use_multi_lang', 'use_multi_lang', 1, $use_multi_lang);
        }

        return $res;
    }

    /**
     * this function is useful for field recognize.
     *
     * @return string return the identifier of the field
     */
    public static function getFieldType()
    {
        return 'field';
    }

    /**
     * function to generate filter field xhtml id.
     *
     * @param string $id_field     id of the field
     * @param string $field_prefix (optional) prefix to make id
     *
     * @return string return the id of the field in filters
     *
     **/
    public function getFieldId_Filter($id_field, $field_prefix = false)
    {
        if ($field_prefix === false) {
            return 'field_filter_' . $id_field;
        } else {
            return $field_prefix . 'field_filter_' . $id_field;
        }
    }

    /**
     * function to generate filter field xhtml name.
     *
     * @param string $field_id     id of the field
     * @param string $field_prefix (optional) prefix to make name
     *
     * @return string return the name of the field in filters
     *
     **/
    public function getFieldName_Filter($id_field, $field_prefix = false)
    {
        if ($field_prefix === false) {
            return 'field_filter[' . $id_field . ']';
        } else {
            return $field_prefix . '[field_filter][' . $id_field . ']';
        }
    }

    /**
     * function to get value of a filter field.
     *
     * @param array  $array_values the array to scan for search value
     * @param string $id_field     id of the field
     * @param string $field_prefix (optional) prefix of the field
     *
     * @return mixed return the value of the field in filters
     *
     **/
    public function getFieldValue_Filter($array_values, $id_field, $field_prefix = false, $default_value = '')
    {
        if ($field_prefix !== null) {
            if (isset($array_values[$field_prefix])) {
                $array_values = $array_values[$field_prefix];
            } else {
                return $default_value;
            }
        }
        if (isset($array_values['field_filter'])
            && isset($array_values['field_filter'][$id_field])) {
            return $array_values['field_filter'][$id_field];
        } else {
            return $default_value;
        }
    }

    /**
     * function to get values of a array of filter field.
     *
     * @param array  $array_values the array to scan for search value
     * @param array  $arr_field_id array of id of the fields (the keys)
     * @param string $field_prefix (optional) prefix of the field
     * @param mixed  $skipchar     (optional) if is a number skip the first
     *                             $skipchar char in $arr_field_id search
     *                             if is a string remove all char to the left
     *                             of given string in $arr_field_id search
     *
     * @return mixed return the value of the field in filters
     *
     **/
    public function getArrFieldValue_Filter($array_values, $arr_field_id, $field_prefix = false, $skipchar = 0)
    {
        $result = [];
        if ($field_prefix !== false) {
            if (isset($array_values[$field_prefix])) {
                $array_values = $array_values[$field_prefix];
            } else {
                return $result;
            }
        }
        if (isset($array_values['field_filter'])) {
            foreach ($array_values['field_filter'] as $fname => $fval) {
                if (is_numeric($skipchar)) {
                    $search_key = substr($fname, $skipchar);
                } else {
                    $pos = strpos($fname, strval($skipchar));
                    if ($pos !== false) {
                        $search_key = substr($fname, $pos + 1);
                    } else {
                        $search_key = $fname;
                    }
                }
                if (isset($arr_field_id[$search_key])) {
                    $result[$fname] = $arr_field_id[$search_key];
                    $result[$fname]['value'] = $fval;
                }
            }
        }

        return $result;
    }

    /**
     * @return string the main table for database save
     */
    public function _getMainTable()
    {
        return $this->field_main_table;
    }

    public function setMainTable($table)
    {
        $this->field_main_table = $table;
    }

    /**
     * @return string the lement table for database save
     */
    public function _getElementTable()
    {
        return $this->field_son_table;
    }

    public function setElementTable($table)
    {
        $this->field_son_table = $table;
    }

    /**
     * @return string the main table for database user entry save
     */
    public function _getUserEntryTable()
    {
        return $this->field_entry_table;
    }

    /**
     * Set the field entry table.
     *
     * @param string $field_entry_table the name of the table
     **/
    public function setFieldEntryTable($field_entry_table)
    {
        $this->field_entry_table = $field_entry_table;
    }

    /**
     * this function create a new field for future use.
     *
     * @param string $back indicates the return url
     *
     * @return nothing
     */
    public function create($back)
    {
    }

    /**
     * this function manage a field.
     *
     * @param string $back indicates the return url
     *
     * @return nothing
     */
    public function edit($back)
    {
    }

    /**
     * this function completely remove a field.
     *
     * @param string $back indicates the return url
     *
     * @return nothing
     */
    public function deleteUserEntry($id_user)
    {
        $query_del = '
		DELETE FROM ' . $this->_getUserEntryTable() . "
		WHERE id_common = '" . (int) $this->id_common . "' AND id_user = '" . (int) $id_user . "'";
        $re = sql_query($query_del);

        return $re;
    }

    /**
     * this function completely remove a field.
     *
     * @param string $back indicates the return url
     *
     * @return nothing
     */
    public function del($back)
    {
        $query_del = '
		DELETE FROM ' . $this->_getUserEntryTable() . "
		WHERE id_common = '" . (int) $this->id_common . "'";
        $re = sql_query($query_del);

        if (!$re) {
            Util::jump_to($back . '&result=fail_del');
        }

        $query_del = '
		DELETE FROM ' . $this->_getMainTable() . "
		WHERE id_common = '" . (int) $this->id_common . "'";
        $re = sql_query($query_del);

        Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail_del'));
    }

    /**
     * display the entry of this field for the passed user.
     *
     * @param int $id_user if alredy exists a enty for the user load it
     *
     * @return string of field xhtml code
     */
    public function show($id_user)
    {
        return '';
    }

    public function toString($field_value)
    {
        return $field_value;
    }

    public function showInLang($id_user, $lang)
    {
        return $this->show($id_user);
    }

    /**
     * display the field for interaction.
     *
     * @param int  $id_user   if alredy exists a entry for the user load as default value
     * @param bool $freeze    if true, disable the user interaction
     * @param bool $mandatory if true, the field is considered mandatory
     *
     * @return string of field xhtml code
     */
    public function play($id_user, $freeze, $mandatory = false, $do_not_show_label = false, $value = null, $registrationLayout = false)
    {
        return '';
    }

    public function multiLangPlay($id_user, $freeze, $mandatory = false, $value = null, $registrationLayout = false)
    {
        return $this->play($id_user, $freeze, $mandatory, $value);
    }

    /**
     * display the field for filters.
     *
     * @param string $field_id      the id of the field used for id/name
     * @param mixed  $value         (optional) the value to put in the field
     *                              retrieved from $_POST if not given
     * @param string $label         (optional) the label to use if not given the
     *                              value will be retrieved from custom field
     *                              $id_field
     * @param string $field_prefix  (optional) the prefix to give to
     *                              the field id/name
     * @param string $other_after   optional html code added after the input element
     * @param string $other_before  optional html code added before the label element
     * @param mixed  $field_special (optional) special param used in some field type
     *                              see documentation in specific field type
     *
     * @return string of field xhtml code
     */
    public function play_filter($id_field, $value = false, $label = false, $field_prefix = false, $other_after = '', $other_before = '', $field_special = false)
    {
        return '';
    }

    /**
     * check if the user as selected a valid value for the field.
     *
     * @return bool true if operation success false otherwise
     */
    public function isFilled($id_user)
    {
        return true;
    }

    /**
     * check if the user as filled the field whita a valid value.
     *
     * @return bool true if operation success or a phrase with the error type
     */
    public function isValid($id_user)
    {
        return true;
    }

    public function get_hidden_filled($grab_from = false, $dropdown_val = false)
    {
        require_once _base_ . '/lib/lib.form.php';

        return Form::getHidden('field_' . self::getFieldType() . '_' . $this->id_common . '',
                                'field_' . self::getFieldType() . '[' . $this->id_common . ']',
                                htmlentities($this->getFilledVal($grab_from, $dropdown_val), ENT_COMPAT, 'UTF-8'));
    }

    /**
     * return the filled value of the selected field.
     *
     * @param mixed $grab_from (optional) the array to retrieve the value from
     *                         ($_POST will be used as default)
     *
     * @return bool true if operation success false otherwise
     */
    public function getFilledVal($grab_from = false, $dropdown_val = false)
    {
        if ($grab_from === false) {
            $grab_from = $_POST;
        }

        if (isset($grab_from['field_' . self::getFieldType()][$this->id_common])) {
            return $grab_from['field_' . self::getFieldType()][$this->id_common];
        } else {
            return null;
        }
    }

    /**
     * store the value inserted by a user into the database, if a entry exists it will be overwrite.
     *
     * @param int $id_user      the user
     * @param int $no_overwrite if a entry exists do not overwrite it
     *
     * @return bool true if operation success false otherwise
     */
    public function store($id_user, $no_overwrite, $int_userid = true)
    {
        return true;
    }

    public function multiLangStore($id_user, $no_overwrite, $int_userid = true)
    {
        return $this->store($id_user, $no_overwrite, $int_userid);
    }

    /**
     * store the value passed into the database, if a entry exists it will be overwrite.
     *
     * @param int  $id_user      the user
     * @param int  $value        the value of the field
     * @param bool $is_id        if false the param must be reconverted
     * @param int  $no_overwrite if a entry exists do not overwrite it
     *
     * @return bool true if success false otherwise
     */
    public function storeDirect($id_user, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        return true;
    }

    public function storeDirectMultiple($idst_users, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        return true;
    }

    public function multiLangStoreDirect($id_user, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        return $this->storeDirect($id_user, $value, $is_id, $no_overwrite, $int_userid);
    }

    public function multiLangStoreDirectMultiple($idst_users, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        return $this->storeDirectMultiple($idst_users, $value, $is_id, $no_overwrite, $int_userid);
    }

    /**
     * use only for special operation.
     */
    public function specialop()
    {
    }

    public function movetoposition($new_position)
    {
        $query_del = '
		UPDATE ' . $this->_getMainTable() . "
		SET sequence = '" . $new_position . "'
		WHERE id_common = '" . (int) $this->id_common . "'";

        return sql_query($query_del);
    }

    public function getFieldName()
    {
        $re_field = sql_query('
		SELECT translation
		FROM ' . $this->_getMainTable() . "
		WHERE lang_code = '" . Lang::get() . "' AND id_common = '" . (int) $this->id_common . "' AND type_field = '" . self::getFieldType() . "'");
        list($translation) = sql_fetch_row($re_field);

        return $translation;
    }

    /*
    function getClientClassObject() {
      return '
        {
          type: "'.self::getFieldType().'",
          getValue: function(id_sel, id_filter) { return ""; },
          setValue: function(id_sel, id_filter, newValue) {},
          render: function(id_sel, id_filter, oEl) {}
        }
      ';
    }
    */

    public function getClientClassObject()
    {
        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        /*
        return 'YAHOO.dynamicFilter.renderTypes.get("'.self::getFieldType().'", {'
            .'_EQUAL:'.$json->encode(Lang::t('_EQUAL')).','
            .'_CONTAINS:'.$json->encode(Lang::t('_CONTAINS')).','
            .'_NOT_EQUAL:'.$json->encode(Lang::t('_NOT_EQUAL')).','
            .'_NOT_CONTAINS:'.$json->encode(Lang::t('_NOT_CONTAINS'))
            .'})';
        */
        return '
      {
        type: "' . self::getFieldType() . '",
      
        getValue: function(id_sel, id_filter) {
          var o, id = "' . self::getFieldType() . '_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
          return YAHOO.lang.JSON.stringify({cond: $D.get(id+"_sel").value, value: $D.get(id).value});
        },
        
        setValue: function(id_sel, id_filter, newValue) {
          if (!newValue) o = {cond: 0, value: ""};
          else o = YAHOO.lang.JSON.parse(newValue);
          var i, s, id = "' . self::getFieldType() . '_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
          $D.get(id).value = o.value;
          s = $D.get(id+"_sel");
          for (i=0; i<s.options.length; i++) {
            if (s.options[i].value == o.cond) {
              s.selectedIndex = i;
              break;
            }
          }
        },
        
        render: function(id_sel, id_filter, oEl, id_field) {
          var t = document.createElement("INPUT"), d = document.createElement("DIV"), s = document.createElement("SELECT");
          s.className = "condition_select";
          d.className = "textfield_container";
          
          d.className = "' . self::getFieldType() . '_container";
          t.type = "text"; t.id = "' . self::getFieldType() . '_"+id_filter+"_"+id_sel; s.id = t.id+"_sel";t.className = "filter_value";
          
          s.options[0] = new Option("' . Lang::t('_CONTAINS', 'standard') . '",0);
					s.options[1] = new Option("' . Lang::t('_NOT_CONTAINS', 'standard') . '",1);
          s.options[2] = new Option("' . Lang::t('_EQUAL', 'standard') . '",2);
          s.options[3] = new Option("' . Lang::t('_NOT_EQUAL', 'standard') . '",3);
					s.options[4] = new Option("' . Lang::t('_STARTS_WITH', 'standard') . '",4);
					s.options[5] = new Option("' . Lang::t('_ENDS_WITH', 'standard') . '",5);

					s.selectedIndex = 0;

          oEl.appendChild(s);
          oEl.appendChild(document.createTextNode(" "));
          oEl.appendChild(t);
        }
      }    
    ';
    }

    public function checkUserField($value, $filter)
    {
        require_once _base_ . '/lib/lib.json.php';

        $output = false;
        switch ($filter['cond']) {
            case 2:  //equal
                $output = ($value == $filter['value']);
       break;
            case 0:  //contains
                $output = (strpos($value, strval($filter['value'])) === false ? false : true);
             break;
            case 3:  //not equal
                $output = ($value != $filter['value']);
             break;
            case 1:  //do not contains
                $output = (strpos($value, strval($filter['value'])) === false ? true : false);
             break;
            case 4:  //starts with
                $output = (strpos($value, strval($filter['value'])) === 0 ? true : false);
             break;
            case 5:  //ends with
                $output = (strpos($value, strval($filter['value'])) === (strlen($value) - strlen($filter['value'])) ? true : false);
             break;
            default:  $output = false;
        } // end switch

        return $output;
    }

    //translates a filter string into a query string for the given field type
    public function getFieldQuery($filter)
    {
        $output = 'SELECT id_user ' .
            'FROM ' . $this->_getUserEntryTable() . ' ' .
            "WHERE id_common = '" . $this->id_common . "' AND user_entry ";

        switch ($filter['cond']) {
            case 2:  //equal
                $output .= " = '" . $filter['value'] . "' ";
             break;

            case 0:  //contains
                $output .= " LIKE '%" . $filter['value'] . "%' ";
             break;

            case 3:  //not equal
                $output .= " <> '" . $filter['value'] . "' ";
             break;

            case 1:  //do not contains
                $output .= " NOT LIKE '%" . $filter['value'] . "%' ";
             break;

            case 4:  //starts with
                $output .= " LIKE '" . $filter['value'] . "%' ";
             break;

            case 5:  //ends with
                $output .= " LIKE '%" . $filter['value'] . "' ";
             break;

            default:  $output = " NOT LIKE '%' ";
        } // end switch

        return $output;
    }

    //------------------------------------------------------------------------------
    public function getFilteredUsers($filter, $exec = true)
    {
        $grab = ['field_' . self::getFieldType() => [$this->id_common => $filter]];
        $request = [
            'cond' => 1,
            'value' => $this->getFilledVal($grab, true),
        ];
        $query = $this->getFieldQuery($request);
        ////"SELECT id_user FROM ".$this->_getUserEntryTable()." WHERE id_common = '".$this->id_common."' AND user_entry LIKE '%".$this->getFilledVal($filter)."'";
        if ($exec) {
            $result = [];
            $res = sql_query($query);
            while (list($id_user) = sql_fetch_row($res)) {
                $result[] = $id_user;
            }

            return $result;
        } else {
            return $query;
        }
    }

    public function getIMBrowserHref($id_user, $field_value)
    {
        return '';
    }

    public function getIMBrowserHead($id_user, $field_value)
    {
        return '';
    }

    public function getIMBrowserImageSrc($id_user, $field_value)
    {
        return '';
    }
}
