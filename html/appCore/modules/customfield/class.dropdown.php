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
 * @version  $Id: class.dropdown.php 987 2007-02-28 17:25:05Z giovanni $
 *
 * @category Field
 *
 * @author   Fabio Pirovano <fabio@docebo.com>
 */
require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/customfield/class.field.php');

class Field_Dropdown extends Field
{
    public $back;
    public $back_coded;

    /**
     * class constructor.
     */
    public function __construct($id_field)
    {
        parent::__construct($id_field);
    }

    /**
     * this function is useful for field recognize.
     *
     * @return string return the identifier of the field
     */
    public static function getFieldType()
    {
        return 'dropdown';
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
        $back_coded = htmlentities(urlencode($back));

        $array_lang = [];
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_POST['undo'])) {
            //undo action
            Util::jump_to($back . '&result=undo');
        }
        if (isset($_POST['save_field_' . $this->getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();
            $show_on = '';
            if (isset($_POST['show_on_platform'])) {
                foreach ($_POST['show_on_platform']  as $code) {
                    $show_on .= $code . ',';
                }
            }
            //control if all is ok
            if (!isset($_POST['new_dropdown'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . $this->getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_dropdown'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_dropdown'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . $this->getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $code = importVar('code', false, '');

            $filter_area_field = importVar('filter_area_field', false, '');

            //insert mandatory field
            if (!sql_query('
			INSERT INTO ' . $this->_getMainTable() . "
			(type_field, show_on_platform, use_multilang, area_code, code) VALUES
			('" . $this->getFieldType() . "', '" . $show_on . "', '" . $use_multilang . "', '" . $filter_area_field . "', '" . $code . "') ")) {
                Util::jump_to($back . '&result=fail');
            }

            list($id_field) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

            if (!sql_query('
			INSERT INTO ' . $this->_getMainLangTable() . "
			(id_field, lang_code, translation) VALUES
			('" . $id_field . "', '" . $mand_lang . "', '" . $_POST['new_dropdown'][$mand_lang] . "') ")) {
                Util::jump_to($back . '&result=fail');
            }

            $re = true;
            //insert other field
            foreach ($_POST['new_dropdown'] as $lang_code => $translation) {
                if ($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $re_ins = sql_query('
					INSERT INTO ' . $this->_getMainLangTable() . "
					(id_field, lang_code, translation) VALUES
					('" . (int) $id_field . "', '" . $lang_code . "', '" . $translation . "') ");
                    $re = $re && $re_ins;
                }
            }

            Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail'));
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->getFormHeader($lang->def('_NEW_DROPDOWN'))
            . $form->openForm('create_' . $this->getFieldType(), $this->getUrl())
            . $form->openElementSpace()
            . $form->getHidden('type_field', 'type_field', $this->getFieldType())
            . $form->getHidden('back', 'back', $back_coded)
        );
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $out->add(
                $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                                    'new_dropdown_' . $lang_code,
                                    'new_dropdown[' . $lang_code . ']',
                                    255,
                                    '',
                                    $lang_code . ' ' . $lang->def('_FIELD_NAME'))
            );
        }

        $out->add(
                        $form->getTextfield($lang->def('_CODE'), 'code', 'code', 255, '')
                );

        // Combo Box Con Area del campo
        $re_field = sql_query('
                    SELECT area_code, area_name FROM '
                    . $GLOBALS['prefix_fw']
                    . '_customfield_area ORDER BY area_name');
        $field_av = [];
        $field_select = ['' => ''];
        while (list($area_code, $area_name) = sql_fetch_row($re_field)) {
            $field_select[$area_code] = $area_name;
        }

        $out->add(
            $form->getDropdown($lang->def('_FIELD_AREA'), 'filter_area_field', 'filter_area_field',
            $field_select, $filter_area_field)
                );

        $GLOBALS['page']->add($this->getMultiLangCheck(), 'content');
        $GLOBALS['page']->add($this->getShowOnPlatformFieldset(), 'content');

        $out->add(
            $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_field', 'save_field_' . $this->getFieldType(), $std_lang->def('_CREATE', 'standard'))
            . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
            . $form->closeButtonSpace()
            . $form->closeForm()
        );
        $out->add('</div>');
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
        $this->back_coded = htmlentities(urlencode($back));
        $this->back = $back;
        $internal_op = importVar('iop');

        switch ($internal_op) {
            case 'add': $this->_add_son(); break;
            case 'mod': $this->_mod_son(); break;
            case 'del': $this->_del_son(); break;
            case 'modmain': $this->_edit_field(); break;
            default: $this->_show_son();
        }
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
		WHERE id_field = '" . (int) $this->id_field . "'";
        $re = sql_query($query_del);
        if (!$re) {
            Util::jump_to($back . '&result=fail');
        }

        $query_sel = '
		SELECT id_field_son FROM ' . $this->_getElementTable() . "
		WHERE id_field = '" . (int) $this->id_field . "'";
        $re_field_element = sql_query($query_sel);
        $arr_field_son = [];
        while (list($id_field_son) = sql_fetch_row($re_field_element)) {
            $arr_field_son[] = $id_field_son;
        }

        if (count($arr_field_son) > 0) {
            $query_del = '
		DELETE FROM ' . $this->_getElementLangTable() . '
		WHERE id_field_son IN (' . implode($arr_field_son) . ')';
            $re = sql_query($query_del);
            if (!$re) {
                Util::jump_to($back . '&result=fail');
            }
        }

        $query_del = '
		DELETE FROM ' . $this->_getElementTable() . "
		WHERE id_field = '" . (int) $this->id_field . "'";
        $re = sql_query($query_del);
        if (!$re) {
            Util::jump_to($back . '&result=fail');
        }

        $query_del = '
		DELETE FROM ' . $this->_getMainLangTable() . "
		WHERE id_field = '" . (int) $this->id_field . "'";
        $re = sql_query($query_del);
        if (!$re) {
            Util::jump_to($back . '&result=fail');
        }

        $query_del = '
		DELETE FROM ' . $this->_getMainTable() . "
		WHERE id_field = '" . (int) $this->id_field . "'";
        $re = sql_query($query_del);

        Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail'));
    }

    /**
     * display the entry of this field for the passed user.
     *
     * @param int $id_user if alredy exists a enty for the user load it
     *
     * @return string of field xhtml code
     */
    public function show($id_obj)
    {
        list($user_entry) = sql_fetch_row(sql_query('
		SELECT obj_entry
		FROM ' . $this->_getUserEntryTable() . "
		WHERE id_obj = '" . (int) $id_obj . "' 
                AND id_field = '" . (int) $this->id_field . "'"));

        $user_entry = (int) $user_entry;

        $re_field_element = sql_query('
		SELECT id_field_son, translation
		FROM ' . $this->_getElementTable() . "
		WHERE id_field = '" . (int) $this->id_field . "' AND lang_code = '" . Lang::get() . "'
		ORDER BY sequence");
        $option = [];
        $option[0] = '';
        while (list($id_field_son, $element) = sql_fetch_row($re_field_element)) {
            $option[$id_field_son] = $element;
        }
        $user_entry = (int) $user_entry;

        return isset($option[$user_entry]) ? $option[$user_entry] : '';
    }

    public function getTranslation()
    {
        $re_field = sql_query('
		SELECT translation
		FROM ' . $this->_getMainTable() . "
		WHERE id_field = '" . (int) $this->id_field . "' AND
			type_field = '" . $this->getFieldType() . "' AND
			lang_code = '" . Lang::get() . "'");
        list($translation) = sql_fetch_row($re_field);

        return $translation;
    }

    /**
     * display the field for interaction.
     *
     * @param int  $id_user           if alredy exists a entry for the user load as default value
     * @param bool $freeze            if true, disable the user interaction
     * @param bool $mandatory         if true, the field is considered mandatory
     * @param bool $do_not_show_label if true, do not show the label in freeze mode
     *
     * @return string of field xhtml code
     */
    public function play($id_obj, $freeze, $mandatory = false, $do_not_show_label = false, $value = null)
    {
        require_once _base_ . '/lib/lib.form.php';

        if (isset($_POST['field_' . $this->getFieldType()])
            && isset($_POST['field_' . $this->getFieldType()][$this->id_field])) {
            $obj_entry = $_POST['field_' . $this->getFieldType()][$this->id_field];
        } else {
            list($obj_entry) = sql_fetch_row(sql_query('
			SELECT obj_entry
			FROM ' . $this->_getUserEntryTable() . "
			WHERE id_obj = '" . (int) $id_obj . "' 
                        AND id_field = '" . (int) $this->id_field . "'"));
        }
        $obj_entry = (int) $obj_entry;

        $re_field = sql_query('
		SELECT cl.translation
		FROM ' . $this->_getMainTable() . ' AS c, ' . $this->_getMainLangTable() . " AS cl
		WHERE c.id_field = cl.id_field
                AND c.id_field = '" . (int) $this->id_field . "' 
                AND c.type_field = '" . $this->getFieldType() . "' 
                AND cl.lang_code = '" . Lang::get() . "'");
        list($translation) = sql_fetch_row($re_field);

        $re_field_element = sql_query('
		SELECT cs.id_field_son, csl.translation
		FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
		WHERE cs.id_field_son = csl.id_field_son
                AND cs.id_field = '" . (int) $this->id_field . "' AND csl.lang_code = '" . Lang::get() . "'
		ORDER BY cs.sequence");

        $option = [];
        $option[0] = Lang::t('_DROPDOWN_NOVALUE', 'field', 'framework');
        while (list($id_field_son, $element) = sql_fetch_row($re_field_element)) {
            $option[$id_field_son] = $element;
        }

        if ($value !== null) {
            $obj_entry = (int) $value;
        }
        if ($freeze) {
            return '<p>' . ($do_not_show_label ? '' : '<b>' . $translation . '</b> : ') . $option[$obj_entry] . '</p>';
        }

        return Form::getDropdown($translation . ($mandatory ? ' <span class="mandatory">*</span>' : ''),
                                'field_' . $this->getFieldType() . '_' . $this->id_field,
                                'field_' . $this->getFieldType() . '[' . $this->id_field . ']',
                                $option,
                                (int) $obj_entry,
                                '',
                                '');
    }

    public function playFlat($id_obj, $ret_code = false)
    {
        require_once _base_ . '/lib/lib.form.php';

        if ($id_obj != -1) {
            // restituisce il valore
            list($obj_entry) = sql_fetch_row(sql_query('
                    SELECT obj_entry
                    FROM ' . $this->_getObjEntryTable() . "
                    WHERE id_obj = '" . (int) $id_obj . "' 
                    AND id_field = '" . (int) $this->id_field . "'"));

            $obj_entry = (int) $obj_entry;

            $re_field_element = sql_query('
                    SELECT csl.translation, cs.code
                    FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
                    WHERE cs.id_field_son = csl.id_field_son
                    AND cs.id_field = '" . (int) $this->id_field . "' AND cs.id_field_son = '" . $obj_entry . "' AND csl.lang_code = '" . Lang::get() . "' ");

            list($translation, $code) = sql_fetch_row($re_field_element);

            if ($ret_code == true) {
                return $code;
            } else {
                return $translation;
            }
        } else {
            // restituisce tutti i valori
            $re_field_element = sql_query('
                    SELECT cs.id_field_son, csl.translation, cs.code
                    FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
                    WHERE cs.id_field_son = csl.id_field_son
                    AND cs.id_field = '" . (int) $this->id_field . "' AND csl.lang_code = '" . Lang::get() . "'
                    ORDER BY cs.sequence");
            $option = [];
            // $option[0] = Lang::t('_DROPDOWN_NOVALUE', 'field', 'framework');
            while (list($id_field_son, $element, $code) = sql_fetch_row($re_field_element)) {
                if ($ret_code == true) {
                    $option[$id_field_son] = $code;
                } else {
                    $option[$id_field_son] = $element;
                }
            }

            return $option;
        }
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
     * @param mixed  $field_special (optional) if is an array the elements are
     *                              the options of dropdown, if is numeric is trated
     *                              as a field id and used to retrieve options
     *                              if not given the elements will be retrieved from
     *                              custom field $id_field
     *
     * @return string of field xhtml code
     */
    public function play_filter($id_field, $value = false, $label = false, $field_prefix = false, $other_after = '', $other_before = '', $field_special = false)
    {
        require_once _base_ . '/lib/lib.form.php';

        if ($value === false) {
            $value = Field::getFieldValue_Filter($_POST, $id_field, $field_prefix, '0');
        }

        $option = [];
        $option[0] = Lang::t('_DROPDOWN_NOVALUE', 'field');
        if (is_array($field_special)) {
            foreach ($field_special as $key_opt => $label_opt) {
                $option[$key_opt] = $label_opt;
            }
        } else {
            $re_field_element = sql_query('
			SELECT id_field_son, translation
			FROM ' . Field_Dropdown::_getElementTable() . "
			WHERE id_field = '" . (int) (($field_special !== false) ? $field_special : $id_field) . "'
				AND lang_code = '" . Lang::get() . "'
			ORDER BY sequence");
            while (list($id_field_son, $element) = sql_fetch_row($re_field_element)) {
                $option[$id_field_son] = $element;
            }
        }

        if ($label === false) {
            $re_field = sql_query('
			SELECT translation
			FROM ' . Field::_getMainTable() . "
			WHERE id_field = '" . (int) $id_field . "'
				AND type_field = '" . Field_Dropdown::getFieldType() . "'");
            list($label) = sql_fetch_row($re_field);
        }

        return Form::getDropdown($label,
                                Field::getFieldId_Filter($id_field, $field_prefix),
                                Field::getFieldName_Filter($id_field, $field_prefix),
                                $option,
                                $value,
                                $other_after,
                                $other_before);
    }

    /**
     * check if the user as selected a valid value for the field.
     *
     * @return bool true if operation success false otherwise
     */
    public function isFilled($id_user)
    {
        if (!isset($_POST['field_' . $this->getFieldType()][$this->id_field])) {
            return false;
        } elseif ($_POST['field_' . $this->getFieldType()][$this->id_field] == '0') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * return the filled value of the selected field.
     *
     * @param mixed $grab_from    (optional) the array to retrieve the value from
     *                            ($_POST will be used as default)
     * @param bool  $dropdown_val (optional). If true will get the value of a dropdown item instead of its id.
     *
     * @return bool true if operation success false otherwise
     */
    public function getFilledVal($grab_from = false, $dropdown_val = false)
    {
        if ($grab_from === false) {
            $grab_from = $_POST;
        }

        if ((!$dropdown_val) && (isset($grab_from['field_' . $this->getFieldType()][$this->id_field]))) {
            return $grab_from['field_' . $this->getFieldType()][$this->id_field];
        } elseif (($dropdown_val) && (isset($grab_from['field_' . $this->getFieldType()][$this->id_field]))) {
            $re_field = sql_query('
			SELECT translation
			FROM ' . $this->_getElementTable() . "
			WHERE id_field = '" . $this->id_field . "' AND lang_code = '" . Lang::get() . "'
				AND id_field_son='" . $grab_from['field_' . $this->getFieldType()][$this->id_field] . "'");
            list($translation) = sql_fetch_row($re_field);

            return $translation;
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
    public function store($id_obj)
    {
        $id_obj = (int) $id_obj;

        if (!isset($_POST['field_' . $this->getFieldType()][$this->id_field])) {
            return true;
        }
        $re_entry = sql_query('
		SELECT obj_entry
		FROM ' . $this->_getObjEntryTable() . "
		WHERE id_obj = '" . $id_obj . "' AND
			id_field = '" . (int) $this->id_field . "'");
        $some_entry = sql_num_rows($re_entry);
        if ($some_entry) {
            if (!sql_query('
			UPDATE ' . $this->_getObjEntryTable() . "
			SET obj_entry = '" . $_POST['field_' . $this->getFieldType()][$this->id_field] . "'
			WHERE id_obj = '" . $id_obj . "' AND
			id_field = '" . (int) $this->id_field . "'")) {
                return false;
            }
        } else {
            if (!sql_query('
			INSERT INTO ' . $this->_getObjEntryTable() . "
			( id_obj, id_field, obj_entry ) VALUES
			(	'" . $id_obj . "',
				'" . (int) $this->id_field . "',
				'" . $_POST['field_' . $this->getFieldType()][$this->id_field] . "')")) {
                return false;
            }
        }

        return true;
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
    public function storeDirect($id_obj, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        $value = stripslashes(strtolower($value));
        if (($int_userid) || (empty($id_user))) {
            $id_user = (int) $id_user;
        }

        $re_entry = sql_query('
		SELECT user_entry
		FROM ' . $this->_getUserEntryTable() . "
		WHERE id_obj = '" . $id_obj . "' 
                AND id_field = '" . (int) $this->id_field . "'");
        $some_entry = sql_num_rows($re_entry);
        if ($some_entry && $no_overwrite) {
            return true;
        }

        $id_value = 0;
        if ($is_id === false) {
            if (isset($GLOBALS['temp']['dropdown_value_' . $this->id_field])) {
                // alredy read form database, search in the array
                $index = array_search($value, $GLOBALS['temp']['dropdown_value_' . $this->id_field]);
                if ($index === false || $index === null) {
                    $id_value = 0;
                } else {
                    $id_value = end(explode('_', $index));
                }
            } else {
                // first time, recover data from database
                $query_value = '
				SELECT idSon, id_field_son, lang_code, translation
				FROM ' . $this->_getElementTable() . "
				WHERE id_field = '" . $this->id_field . "'
					 AND lang_code = '" . Lang::get() . "'";
                $re_values = sql_query($query_value);
                while (list($id_son, $id_field_son, $lang_code, $value_com) = sql_fetch_row($re_values)) {
                    $value_com = strtolower($value_com);
                    $GLOBALS['temp']['dropdown_value_' . $this->id_field][$lang_code . '_' . $id_field_son] = $value_com;
                    if ($value_com == $value) {
                        $id_value = $id_field_son;
                    }
                }
            }
        } else {
            // tha value is the id

            $id_value = $value;
        }

        if ($some_entry) {
            if (!sql_query('
			UPDATE ' . $this->_getUserEntryTable() . "
			SET obj_entry = '" . $id_value . "'
			WHERE id_obj = '" . $id_obj . "' 
                        AND id_field = '" . (int) $this->id_field . "'")) {
                return false;
            }
        } else {
            if (!sql_query('
			INSERT INTO ' . $this->_getUserEntryTable() . "
			( id_user, id_field, user_entry ) VALUES
			(	'" . $id_user . "',
				'" . (int) $this->id_field . "',
				'" . $id_value . "')")) {
                return false;
            }
        }

        return true;
    }

    public function storeDirectMultiple($idst_users, $value, $is_id, $no_overwrite, $int_userid = true)
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

        $arr_existent = [];
        $arr_new = $idst_users;

        $query = 'SELECT id_user, user_entry FROM ' . $this->_getUserEntryTable() . ' '
            . ' WHERE id_user IN (' . implode(',', $idst_users) . ') '
            . " AND id_field = '" . (int) $this->id_field . "'";
        $res = sql_query($query);
        if ($res) {
            if (sql_num_rows($res) > 0) {
                while (list($idst, $entry) = sql_fetch_row($res)) {
                    $arr_existent[] = $idst;
                    unset($arr_new[array_search($idst, $arr_new)]);
                }
            }

            if (!empty($arr_existent) && !$no_overwrite) {
                if ($no_overwrite) {
                    return true;
                }
                $query = 'UPDATE ' . $this->_getUserEntryTable() . " SET user_entry = '" . $value . "' "
                    . ' WHERE id_user IN (' . implode(',', $arr_existent) . ') '
                    . " AND id_field = '" . (int) $this->id_field . "'";
                $res1 = sql_query($query);
            }

            if (!empty($arr_new)) {
                $insert_values = [];
                foreach ($arr_new as $idst) {
                    $insert_values[] = "(	'" . (int) $idst . "', '" . (int) $this->id_field . "', '" . $value . "')";
                }
                $query = 'INSERT INTO ' . $this->_getUserEntryTable() . ' '
                    . '( id_user, id_field, user_entry ) VALUES '
                    . implode(',', $insert_values);
                $res2 = sql_query($query);
            }
        }

        return true;
    }

    // NOTE: special function ---------------------------------------

    public function _move_up($id_son)
    {
        $query = 'SELECT sequence, id_field_son'
                    . ' FROM ' . $this->_getElementTable() . ''
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND lang_code = '" . Lang::get() . "'"
                    . " AND idSon = '" . $id_son . "'";

        list($sequence, $id_field_son) = sql_fetch_row(sql_query($query));

        $up_sequence = $sequence - 1;

        $query = 'UPDATE ' . $this->_getElementTable() . ''
                    . " SET sequence = '" . $sequence . "'"
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND sequence = '" . $up_sequence . "'";

        $result = sql_query($query);

        $query = 'UPDATE ' . $this->_getElementTable() . ''
                    . " SET sequence = '" . $up_sequence . "'"
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND id_field_son = '" . $id_field_son . "'";

        $result = sql_query($query);
    }

    public function _move_down($id_son)
    {
        $query = 'SELECT sequence, id_field_son'
                    . ' FROM ' . $this->_getElementTable() . ''
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND lang_code = '" . Lang::get() . "'"
                    . " AND idSon = '" . $id_son . "'";

        list($sequence, $id_field_son) = sql_fetch_row(sql_query($query));

        $up_sequence = $sequence + 1;

        $query = 'UPDATE ' . $this->_getElementTable() . ''
                    . " SET sequence = '" . $sequence . "'"
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND sequence = '" . $up_sequence . "'";

        $result = sql_query($query);

        $query = 'UPDATE ' . $this->_getElementTable() . ''
                    . " SET sequence = '" . $up_sequence . "'"
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND id_field_son = '" . $id_field_son . "'";

        $result = sql_query($query);
    }

    public function _fix_sequence()
    {
        $new_sequence = 1;

        $query = 'SELECT id_field_son'
                    . ' FROM ' . $this->_getElementTable() . ''
                    . " WHERE id_field = '" . $this->id_field . "'"
                    . " AND lang_code = '" . Lang::get() . "'";

        $result = sql_query($query);

        while (list($id_field) = sql_fetch_row($result)) {
            $query = 'UPDATE ' . $this->_getElementTable() . ''
                        . " SET sequence = '" . $new_sequence . "'"
                        . " WHERE id_field = '" . $this->id_field . "'"
                        . " AND id_field_son = '" . $id_field . "'";

            sql_query($query);

            ++$new_sequence;
        }
    }

    public function _show_son()
    {
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $out = &$GLOBALS['page'];

        $out->setWorkingZone('content');

        require_once _base_ . '/lib/lib.table.php';
        require_once _base_ . '/lib/lib.form.php';

        $counter = 0;
        $img_up = '<img class="valing-middle" src="' . getPathImage() . 'standard/up.png" alt="' . $std_lang->def('_MOVE_UP') . '" />';
        $img_down = '<img class="valing-middle" src="' . getPathImage() . 'standard/down.png" alt="' . $std_lang->def('_MOVE_DOWN') . '" />';

        $id_son = FormaLms\lib\Get::req('idSon', DOTY_INT, 0);
        $iop = FormaLms\lib\Get::req('iop', DOTY_STRING, '');

        if ($iop == 'moveup') {
            $this->_move_up($id_son);
        } elseif ($iop == 'movedown') {
            $this->_move_down($id_son);
        } elseif ($iop == 'fixsequence') {
            $this->_fix_sequence();
        }

        list($total_son) = sql_fetch_row(sql_query('
		SELECT COUNT(*)
		FROM ' . $this->_getElementTable() . "
		WHERE id_field = '" . $this->id_field . "'"));

        $re_main = sql_query('
		SELECT cl.translation
		FROM ' . $this->_getMainLangTable() . ' AS cl, ' . $this->_getMainTable() . " AS c 
		WHERE c.id_field = cl.id_field 
                AND cl.id_field = '" . $this->id_field . "' AND cl.lang_code = '" . Lang::get() . "'
		ORDER BY c.sequence");
        list($translation) = sql_fetch_row($re_main);

        //find available son
        $re_field = sql_query('
		SELECT csl.id_field_son, csl.translation, cs.code
		FROM ' . $this->_getElementTable() . ' AS cs,  ' . $this->_getElementLangTable() . " AS csl
		WHERE cs.id_field_son = csl.id_field_son
                AND cs.id_field = '" . $this->id_field . "' AND csl.lang_code = '" . Lang::get() . "'
		ORDER BY cs.sequence");

        $base_path = $this->getUrl() . '&amp;id_field='
                . $this->id_field . '&amp;type_field=' . $this->getFieldType() . '&amp;back=' . $this->back_coded;

        $out->add('<div class="std_block">'
            . getBackUi($this->back, $std_lang->def('_BACK'))
            . '<div class="title"><b>' . $lang->def('_TITLE') . ' :</b> '
            . '<a class="ico-wt-sprite subs_mod" href="' . $base_path . '&amp;iop=modmain"><span>'
            . $translation . '</span></a>'
            . '</div><br />');

        $tb_son = new Table(0, $lang->def('_DROPDOWN_SON_CAPTION'));
        $content_h = [
            $lang->def('_CODE'),
            $lang->def('_DROPDOWN_ELEMENT'),
            $img_up,
            $img_down,
            '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $std_lang->def('_MOD') . '" />',
            '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $std_lang->def('_DEL') . '" />',
        ];
        $type_h = ['', '', 'image', 'image', 'image', 'image'];
        $tb_son->addHead($content_h, $type_h);
        while (list($idSon, $elem, $code) = sql_fetch_row($re_field)) {
            ++$counter;

            $content = [];

            $content[] = $code;

            $content[] = $elem;

            if ($counter != 1 && $counter != $total_son) {
                $content[] = '<a href="' . $base_path . '&amp;iop=moveup&amp;idSon=' . $idSon . '" title="' . $std_lang->def('_MOVE_UP') . '">' . $img_up . '</a>';
                $content[] = '<a href="' . $base_path . '&amp;iop=movedown&amp;idSon=' . $idSon . '" title="' . $std_lang->def('_MOVE_DOWN') . '">' . $img_down . '</a>';
            } elseif ($counter == 1) {
                $content[] = '';
                $content[] = '<a href="' . $base_path . '&amp;iop=movedown&amp;idSon=' . $idSon . '" title="' . $std_lang->def('_MOVE_DOWN') . '">' . $img_down . '</a>';
            } else {
                $content[] = '<a href="' . $base_path . '&amp;iop=moveup&amp;idSon=' . $idSon . '" title="' . $std_lang->def('_MOVE_UP') . '">' . $img_up . '</a>';
                $content[] = '';
            }

            $content[] = '<a href="' . $base_path . '&amp;iop=mod&amp;idSon=' . $idSon . '" title="' . $std_lang->def('_MOD') . ' : ' . $elem . '">'
                    . '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $std_lang->def('_MOD') . ' : ' . $elem . '" /></a>';

            $content[] = '<a href="' . $base_path . '&amp;iop=del&amp;idSon=' . $idSon . '" title="' . $std_lang->def('_DEL') . ' : ' . $elem . '">'
                    . '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $std_lang->def('_DEL') . ' : ' . $elem . '" /></a>';

            $tb_son->addBody($content);
        }
        $tb_son->addActionAdd(
            '<a class="ico-wt-sprite subs_add" href="' . $this->getUrl()
                . '&amp;id_field=' . $this->id_field
                . '&amp;type_field=' . $this->getFieldType()
                . '&amp;back=' . $this->back_coded
                . '&amp;iop=add"><span>'
            . $lang->def('_DROPDOWN_SON_ADD') . '</span></a>'
        );
        $out->add($tb_son->getTable());
        $out->add('<a href="' . $base_path . '&amp;iop=fixsequence"'
                . ' title="' . $lang->def('_FIX_SEQUENCE') . '">' . $lang->def('_FIX_SEQUENCE') . '</a>');
        $out->add(getBackUi($this->back, $std_lang->def('_BACK'))
            . '</div>');
    }

    public function _edit_field()
    {
        $array_lang = [];
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_POST['undo'])) {
            //undo action
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['save_field_' . $this->getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();
            $show_on = '';
            if (isset($_POST['show_on_platform'])) {
                foreach ($_POST['show_on_platform']  as $code) {
                    $show_on .= $code . ',';
                }
            }
            //control if all is ok
            if (!isset($_POST['new_textfield'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . $this->getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_textfield'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_textfield'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . $this->getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $existsing_translation = [];
            $re_trans = sql_query('
			SELECT lang_code
			FROM ' . $this->_getMainLangTable() . "
			WHERE id_field = '" . $this->id_field . "'");
            while (list($l_code) = sql_fetch_row($re_trans)) {
                $existsing_translation[$l_code] = 1;
            }

            $use_multilang = (isset($_POST['use_multi_lang']) ? 1 : 0);

            $code = importVar('code', false, '');

            $filter_area_field = importVar('filter_area_field', false, '');

            $re = true;
            //insert other field
            foreach ($_POST['new_textfield'] as $lang_code => $translation) {
                if (isset($existsing_translation[$lang_code])) {
                    if (!sql_query('
					UPDATE ' . $this->_getMainTable() . "
					SET 	show_on_platform = '" . $show_on . "',
						use_multilang = '" . $use_multilang . "',
                                                area_code = '" . $filter_area_field . "',
                                                code = '" . $code . "'
					WHERE id_field = '" . (int) $this->id_field . "'")) {
                        $re = false;
                    }

                    if (!sql_query('
					UPDATE ' . $this->_getMainLangTable() . "
					SET translation = '" . $translation . "'
					WHERE id_field = '" . (int) $this->id_field . "' AND lang_code = '" . $lang_code . "'")) {
                        $re = false;
                    }
                } else {
                    if (!sql_query('
					UPDATE ' . $this->_getMainTable() . "
					SET 	show_on_platform = '" . $show_on . "',
						use_multilang = '" . $use_multilang . "',
                                                area_code = '" . $filter_area_field . "',
                                                code = '" . $code . "'
					WHERE id_field = '" . (int) $this->id_field . "'")) {
                        $re = false;
                    }

                    if (!sql_query('
					INSERT INTO ' . $this->_getMainLangTable() . "
					(id_field, lang_code, translation) VALUES
					('" . (int) $this->id_field . "', '" . $lang_code . "', '" . $translation . "') ")) {
                        $re = false;
                    }
                }
            }
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded
                . '&result=' . ($re ? 'success' : 'fail'));
        }

        //load value form database
        $re_trans = sql_query('
		SELECT cl.lang_code, cl.translation, c.show_on_platform, c.use_multilang, c.area_code, c.code
		FROM ' . $this->_getMainTable() . ' AS c, ' . $this->_getMainLangTable() . " AS cl
		WHERE c.id_field = cl.id_field
                AND c.id_field = '" . $this->id_field . "'");
        while (list($l_code, $trans, $show_on, $db_use_multilang, $area_code, $field_code) = sql_fetch_row($re_trans)) {
            $translation[$l_code] = $trans;
            if (!isset($show_on_platform)) {
                $show_on_platform = array_flip(explode(',', $show_on));
            }
            if (!isset($use_multilang)) {
                $use_multilang = $db_use_multilang;
            }
            $filter_area_field = $area_code;
            $code = $field_code;
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->openForm('create_' . $this->getFieldType(), $this->getUrl())
            . $form->openElementSpace()
            . $form->getHidden('type_field', 'type_field', $this->getFieldType())
            . $form->getHidden('id_field', 'id_field', $this->id_field)
            . $form->getHidden('back', 'back', $this->back_coded)
            . $form->getHidden('iop', 'iop', 'modmain')
        );
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $out->add(
                $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                                    'new_textfield_' . $lang_code,
                                    'new_textfield[' . $lang_code . ']',
                                    255,
                                    (isset($translation[$lang_code]) ? $translation[$lang_code] : ''),
                                    $lang_code . ' ' . $lang->def('_FIELD_NAME'))
            );
        }

        $out->add(
                        $form->getTextfield($lang->def('_CODE'), 'code', 'code', 255, $code)
                );

        // Combo Box Con Area del campo
        $re_field = sql_query('
                    SELECT area_code, area_name FROM '
                    . $GLOBALS['prefix_fw']
                    . '_customfield_area ORDER BY area_name');
        $field_av = [];
        $field_select = ['' => ''];
        while (list($area_code, $area_name) = sql_fetch_row($re_field)) {
            $field_select[$area_code] = $area_name;
        }

        $out->add(
            $form->getDropdown($lang->def('_FIELD_AREA'), 'filter_area_field', 'filter_area_field',
            $field_select, $filter_area_field)
                );

        $GLOBALS['page']->add($this->getMultiLangCheck($use_multilang), 'content');
        $GLOBALS['page']->add($this->getShowOnPlatformFieldset($show_on_platform), 'content');

        $out->add(
            $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_field', 'save_field_' . $this->getFieldType(), $std_lang->def('_SAVE', 'standard'))
            . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
            . $form->closeButtonSpace()
            . $form->closeForm()
        );
        $out->add('</div>');
    }

    public function _add_son()
    {
        $array_lang = [];
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_POST['undo'])) {
            //undo action
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['save_field_' . $this->getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();

            //control if all is ok
            if (!isset($_POST['new_dropdown_son'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;id_field='
                        . $this->id_field . '&amp;type_field=' . $this->getFieldType() . '&amp;back=' . $this->back_coded,
                        $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_dropdown_son'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_dropdown_son'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;id_field='
                        . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded,
                        $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $code = importVar('code', false, '');

            list($sequence) = sql_fetch_row(sql_query('
			SELECT COUNT(*)
			FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
			WHERE cs.id_field_son = csl.id_field_son
                        AND cs.id_field = '" . $this->id_field . "' AND csl.lang_code = '" . Lang::get() . "'"));

            ++$sequence;

            //insert mandatory field
            if (!sql_query('
			INSERT INTO ' . $this->_getElementTable() . "
			(id_field, sequence, code) VALUES
			('" . $this->id_field . "', '" . $sequence . "', '" . $code . "') ")) {
                Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded
                . '&result=fail');
            }
            list($id_field_son) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

            if (!sql_query('
			INSERT INTO ' . $this->_getElementLangTable() . "
			(id_field_son, lang_code, translation) VALUES
			('" . $id_field_son . "', '" . $mand_lang . "', '" . $_POST['new_dropdown_son'][$mand_lang] . "' ) ")) {
                Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded
                . '&result=fail');
            }

            $re = true;
            //insert other field
            foreach ($_POST['new_dropdown_son'] as $lang_code => $translation) {
                if ($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $re_ins = sql_query('
					INSERT INTO ' . $this->_getElementLangTable() . "
					(id_field_son, lang_code, translation) VALUES
					('" . (int) $id_field_son . "', '" . $lang_code . "', '" . $translation . "')");
                    $re = $re && $re_ins;
                }
            }
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded
                . '&result=' . ($re ? 'success' : 'fail'));
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->getFormHeader($lang->def('_DROPDOWN_SON_NEW'))
            . $form->openForm('create_' . $this->getFieldType(), $this->getUrl())
            . $form->openElementSpace()
            . $form->getHidden('type_field', 'type_field', $this->getFieldType())
            . $form->getHidden('id_field', 'id_field', $this->id_field)
            . $form->getHidden('back', 'back', $this->back_coded)
            . $form->getHidden('iop', 'iop', 'add')
        );
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $out->add(
                $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                                    'new_dropdown_son_' . $lang_code,
                                    'new_dropdown_son[' . $lang_code . ']',
                                    255,
                                    '',
                                    $lang_code . ' ' . $lang->def('_FIELD_NAME'))
            );
        }

        $out->add(
                        $form->getTextfield($lang->def('_CODE'), 'code', 'code', 255, $code)
                );

        $out->add(
            $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_field', 'save_field_' . $this->getFieldType(), $std_lang->def('_CREATE', 'standard'))
            . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
            . $form->closeButtonSpace()
            . $form->closeForm()
        );
        $out->add('</div>');
    }

    public function _mod_son()
    {
        $idSon = importVar('idSon', true, 0);
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $out = &$GLOBALS['page'];
        $array_lang = [];
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();

        if (isset($_POST['undo'])) {
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['save_field_' . $this->getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();

            //control if all is ok
            if (!isset($_POST['mod_dropdown_son'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . $this->getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['mod_dropdown_son'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['mod_dropdown_son'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . $this->getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $existsing_translation = [];
            $re_trans = sql_query('
			SELECT csl.lang_code
			FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
			WHERE cs.id_field_son = csl.id_field_son
                        AND csl.id_field_son = '" . (int) $idSon . "' 
                        AND cs.id_field = '" . (int) $this->id_field . "'");
            while (list($l_code) = sql_fetch_row($re_trans)) {
                $existsing_translation[$l_code] = 1;
            }

            $code = importVar('code', false, '');

            $re = true;
            //insert other field
            foreach ($_POST['mod_dropdown_son'] as $lang_code => $translation) {
                if (isset($existsing_translation[$lang_code])) {
                    if (!sql_query('
					UPDATE ' . $this->_getElementLangTable() . "
					SET translation = '" . $translation . "'
					WHERE id_field_son = '" . (int) $idSon . "' 
                                        AND lang_code = '" . $lang_code . "'")) {
                        $re = false;
                    }

                    if (!sql_query('
					UPDATE ' . $this->_getElementTable() . "
					SET code = '" . $code . "'
					WHERE id_field_son = '" . (int) $idSon . "'")) {
                        $re = false;
                    }
                } else {
                    list($tot_son) = sql_fetch_row(sql_query('
                                    SELECT COUNT(*)
                                    FROM ' . $this->_getElementTable() . "
                                    WHERE id_field = '" . $this->id_field . "' AND id_field_son = '" . $idSon . "'"));
                    if ($tot_son == 0) {
                        if (!sql_query('
					INSERT INTO ' . $this->_getElementTable() . "
					(id_field, id_field_son, code) VALUES
					('" . (int) $this->id_field . "', '" . (int) $idSon . "', $code) ")) {
                            $re = false;
                        }
                    }

                    if (!sql_query('
					INSERT INTO ' . $this->_getElementLangTable() . "
					(id_field_son, lang_code, translation) VALUES
					('" . (int) $idSon . "', '" . $lang_code . "', '" . $translation . "') ")) {
                        $re = false;
                    }
                }
            }
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded
                . '&result=' . ($re ? 'success' : 'fail'));
        }

        //load value form database
        $re_trans = sql_query('
		SELECT csl.lang_code, csl.translation, cs.code
		FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
		WHERE cs.id_field_son = csl.id_field_son
                AND cs.id_field_son = '" . $idSon . "' AND cs.id_field = '" . (int) $this->id_field . "'");
        while (list($l_code, $trans, $code_field_son) = sql_fetch_row($re_trans)) {
            $translation[$l_code] = $trans;
            $code = $code_field_son;
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->openForm('del_' . $this->getFieldType(), $this->getUrl())
            . $form->openElementSpace()
            . $form->getHidden('type_field', 'type_field', $this->getFieldType())
            . $form->getHidden('id_field', 'id_field', $this->id_field)
            . $form->getHidden('idSon', 'idSon', $idSon)
            . $form->getHidden('back', 'back', $this->back_coded)
            . $form->getHidden('iop', 'iop', 'mod')
        );
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $out->add(
                $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                                    'mod_dropdown_son_' . $lang_code,
                                    'mod_dropdown_son[' . $lang_code . ']',
                                    255,
                                    (isset($translation[$lang_code]) ? $translation[$lang_code] : ''),
                                    $lang_code . ' ' . $lang->def('_FIELD_NAME'))
            );
        }

        $out->add(
                        $form->getTextfield($lang->def('_CODE'), 'code', 'code', 255, $code)
                );

        $out->add(
            $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_field', 'save_field_' . $this->getFieldType(), $std_lang->def('_SAVE', 'standard'))
            . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
            . $form->closeButtonSpace()
            . $form->closeForm()
        );
        $out->add('</div>');
    }

    public function _del_son()
    {
        $idSon = importVar('idSon');
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $out = &$GLOBALS['page'];

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        if (isset($_POST['undo'])) {
            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['confirm'])) {
            $query_del = '
			DELETE FROM ' . $this->_getUserEntryTable() . "
			WHERE obj_entry = '" . $idSon . "'
			AND id_field = " . (int) $this->id_field;
            $re = sql_query($query_del);
            if (!$re) {
                Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded . '&amp;result=fail');
            }

            $query_del = '
			DELETE FROM ' . $this->_getElementTable() . "
			WHERE id_field = '" . (int) $this->id_field . "' 
                        AND id_field_son = '" . (int) $idSon . "'";
            $re = sql_query($query_del);
            if (!$re) {
                Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded . '&amp;result=fail');
            }

            $query_del = '
			DELETE FROM ' . $this->_getElementLangTable() . "
			WHERE  id_field_son = '" . (int) $idSon . "'";
            $re = sql_query($query_del);

            Util::jump_to($this->getUrl() . '&id_field='
                . $this->id_field . '&type_field=' . $this->getFieldType() . '&back=' . $this->back_coded
                . '&amp;result=' . ($re ? 'success' : 'fail'));
        }

        $re_main = sql_query('
		SELECT csl.translation
		FROM ' . $this->_getElementTable() . ' AS cs, ' . $this->_getElementLangTable() . " AS csl
		WHERE cs.id_field_son = csl.id_field_son
                AND cs.id_field_son = '" . $idSon . "' AND csl.lang_code = '" . Lang::get() . "'
		ORDER BY cs.sequence");
        list($translation) = sql_fetch_row($re_main);

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->openForm('del_' . $this->getFieldType(), $this->getUrl())
            . $form->getHidden('type_field', 'type_field', $this->getFieldType())
            . $form->getHidden('id_field', 'id_field', $this->id_field)
            . $form->getHidden('idSon', 'idSon', $idSon)
            . $form->getHidden('back', 'back', $this->back_coded)
            . $form->getHidden('iop', 'iop', 'del')
            . '<div class="boxinfo_title">'
                . $lang->def('_AREYOUSURE')
            . '</div>'

            . '<div class="boxinfo_container">'
                . $lang->def('_DROPDOWN_ELEMENT') . ' : ' . $translation
            . '</div>'

            . '<div class="del_container">'
            . $form->getButton('confirm', 'confirm', $std_lang->def('_CONFIRM', 'standard'), 'transparent_del_button') . '&nbsp;'
            . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'), 'transparent_undo_button')
            . '</div>'

            . $form->closeForm()
        );
        $out->add('</div>');
    }

    public function getAllSon($language = false)
    {
        $lang = &FormaLanguage::createInstance('field');
        if (!$language) {
            $language = Lang::get();
        }

        if (!isset($GLOBALS['temp']['dropdown_cache_' . $this->id_field][$language])) {
            $GLOBALS['temp']['dropdown_cache_' . $this->id_field][$language] = [];
            $re_field = sql_query('
				SELECT idSon, translation
				FROM ' . $this->_getElementTable() . "
				WHERE id_field = '" . $this->id_field . "' AND lang_code = '" . $language . "'
				ORDER BY sequence");
            if (!$re_field) {
                return $sons;
            }
            while (list($id_son, $elem) = sql_fetch_row($re_field)) {
                $GLOBALS['temp']['dropdown_cache_' . $this->id_field][$language][$id_son] = $elem;
            }
        }

        return $GLOBALS['temp']['dropdown_cache_' . $this->id_field][$language];
        /*
        $sons = array();
        //find available son
        $re_field = sql_query("
        SELECT idSon, translation
        FROM ".$this->_getElementTable()."
        WHERE id_field = '".$this->id_field."' AND lang_code = '".Lang::get()."'
        ORDER BY sequence");
        if(!$re_field) return $sons;
        while(list($id_son, $elem) = sql_fetch_row($re_field)) {

            $sons[$id_son] = $elem;
        }
        return $sons;
        */
    }

    public function getFlatAllSon()
    {
        $lang = &FormaLanguage::createInstance('field');

        $sons = [];
        //find available son
        $re_field = sql_query('
		SELECT idSon, id_field, id_field_son, translation
		FROM ' . $this->_getElementTable() . "
		WHERE lang_code = '" . Lang::get() . "'
		ORDER BY sequence");
        if (!$re_field) {
            return $sons;
        }
        while (list($id_son, $id_field, $id_cson, $elem) = sql_fetch_row($re_field)) {
            $sons[$id_field][$id_son] = $elem;
        }

        return $sons;
    }

    public function getClientClassObject()
    {
        $sons = $this->getFlatAllSon();
        $temp = [];
        foreach ($sons as $key => $val) {
            $temp2 = [];
            foreach ($val as $k2 => $v2) {
                $temp2[] = '{value: "' . $k2 . '", text: "' . $v2 . '"}';
            }
            $temp[] = 'field_' . $key . ': [' . implode(',', $temp2) . ']';
        }
        $js_sons = '{' . implode(',', $temp) . '}';

        return 'YAHOO.dynamicFilter.renderTypes.get("' . $this->getFieldType() . '", ' . $js_sons . ')';
    }

    public function checkUserField($value, $filter)
    {
        return $value == $filter;
    }

    public function getFieldQuery($filter)
    {
        if ($filter == '') {
            return '0';
        }

        $output = 'SELECT id_user as idst ' .
            'FROM  ' . $this->_getUserEntryTable() . ' ' .
            "WHERE id_field = '" . $this->id_field . "' AND user_entry = " . (int) $filter;

        return $output;
    }

    public function toString($field_value)
    {
        $fields = $this->getAllSon();

        return $fields[$field_value];
    }
}
