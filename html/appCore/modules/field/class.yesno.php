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
 * @version  $Id: class.yesno.php 987 2007-02-28 17:25:05Z giovanni $
 *
 * @category Field
 *
 * @author   Fabio Pirovano <fabio@docebo.com>
 */
require_once \FormaLms\lib\Forma::inc(_adm_ . '/modules/field/class.field.php');

class Field_YesNo extends Field
{
    /**
     * this function is useful for field recognize.
     *
     * @return string return the identifier of the field
     */
    public static function getFieldType()
    {
        return 'yesno';
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
        if (isset($_POST['save_field_' . self::getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();
            $show_on = '';
            if (isset($_POST['show_on_platform'])) {
                foreach ($_POST['show_on_platform']  as $code) {
                    $show_on .= $code . ',';
                }
            }
            //control if all is ok
            if (!isset($_POST['new_yesno'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . self::getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_yesno'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_yesno'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . self::getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            // Insert mandatory field
            if (!sql_query('
			INSERT INTO ' . $this->_getMainTable() . "
			(type_field, lang_code, translation, show_on_platform, use_multilang) VALUES
			('" . self::getFieldType() . "', '" . $mand_lang . "', '" . $_POST['new_yesno'][$mand_lang] . "', '" . $show_on . "', '" . $use_multilang . "') ")) {
                Util::jump_to($back . '&result=fail');
            }
            list($id_common) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            if (!sql_query('
			UPDATE ' . $this->_getMainTable() . "
			SET id_common = '" . (int) $id_common . "'
			WHERE idField = '" . (int) $id_common . "'")) {
                Util::jump_to($back . '&result=fail');
            }
            $re = true;
            //insert other field
            foreach ($_POST['new_yesno'] as $lang_code => $translation) {
                if ($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $re_ins = sql_query('
					INSERT INTO ' . $this->_getMainTable() . "
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang) VALUES
					('" . self::getFieldType() . "', '" . (int) $id_common . "', '" . $lang_code . "', '" . $translation . "', '" . $show_on . "', '" . $use_multilang . "') ");
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
            $form->getFormHeader($lang->def('_NEW_YESNO'))
            . $form->openForm('create_' . self::getFieldType(), $this->getUrl())
            . $form->openElementSpace()
            . $form->getHidden('type_field', 'type_field', self::getFieldType())
            . $form->getHidden('back', 'back', $back_coded)
        );
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $out->add(
                $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                                    'new_yesno_' . $lang_code,
                                    'new_yesno[' . $lang_code . ']',
                                    255,
                                    '',
                                    $lang_code . ' ' . $lang->def('_FIELD_NAME'))
            );
        }

        $GLOBALS['page']->add($this->getMultiLangCheck(), 'content');
        $GLOBALS['page']->add($this->getShowOnPlatformFieldset(), 'content');

        $out->add(
            $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_field', 'save_field_' . self::getFieldType(), $std_lang->def('_CREATE', 'standard'))
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
        if (isset($_POST['save_field_' . self::getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();
            $show_on = '';
            if (isset($_POST['show_on_platform'])) {
                foreach ($_POST['show_on_platform']  as $code) {
                    $show_on .= $code . ',';
                }
            }
            //control if all is ok
            if (!isset($_POST['new_yesno'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . self::getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_yesno'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_yesno'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                    . getBackUi($this->getUrl() . '&amp;type_field='
                        . self::getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $existsing_translation = [];
            $re_trans = sql_query('
			SELECT lang_code
			FROM ' . $this->_getMainTable() . "
			WHERE id_common = '" . $this->id_common . "'");
            while (list($l_code) = sql_fetch_row($re_trans)) {
                $existsing_translation[$l_code] = 1;
            }

            $use_multilang = (isset($_POST['use_multi_lang']) ? 1 : 0);

            $re = true;
            //insert other field
            foreach ($_POST['new_yesno'] as $lang_code => $translation) {
                if (isset($existsing_translation[$lang_code])) {
                    if (!sql_query('
					UPDATE ' . $this->_getMainTable() . "
					SET translation = '" . $translation . "',
						show_on_platform = '" . $show_on . "',
						use_multilang = '" . $use_multilang . "'
					WHERE id_common = '" . (int) $this->id_common . "' AND lang_code = '" . $lang_code . "'")) {
                        $re = false;
                    }
                } else {
                    if (!sql_query('
					INSERT INTO ' . $this->_getMainTable() . "
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang) VALUES
					('" . self::getFieldType() . "', '" . (int) $this->id_common . "', '" . $lang_code . "', '" . $translation . "', '" . $show_on . "', '" . $use_multilang . "') ")) {
                        $re = false;
                    }
                }
            }
            Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail'));
        }

        //load value form database
        $re_trans = sql_query('
		SELECT lang_code, translation, show_on_platform, use_multilang
		FROM ' . $this->_getMainTable() . "
		WHERE id_common = '" . $this->id_common . "'");
        while (list($l_code, $trans, $show_on, $db_use_multilang) = sql_fetch_row($re_trans)) {
            $translation[$l_code] = $trans;
            if (!isset($show_on_platform)) {
                $show_on_platform = array_flip(explode(',', $show_on));
            }
            if (!isset($use_multilang)) {
                $use_multilang = $db_use_multilang;
            }
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->openForm('create_' . self::getFieldType(), $this->getUrl())
            . $form->openElementSpace()
            . $form->getHidden('type_field', 'type_field', self::getFieldType())
            . $form->getHidden('id_common', 'id_common', $this->id_common)
            . $form->getHidden('back', 'back', $back_coded)
        );
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $out->add(
                $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                                    'new_yesno_' . $lang_code,
                                    'new_yesno[' . $lang_code . ']',
                                    255,
                                    (isset($translation[$lang_code]) ? $translation[$lang_code] : ''),
                                    $lang_code . ' ' . $lang->def('_FIELD_NAME'))
            );
        }

        $GLOBALS['page']->add($this->getMultiLangCheck($use_multilang), 'content');
        $GLOBALS['page']->add($this->getShowOnPlatformFieldset($show_on_platform), 'content');

        $out->add(
            $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_field', 'save_field_' . self::getFieldType(), $std_lang->def('_SAVE', 'standard'))
            . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
            . $form->closeButtonSpace()
            . $form->closeForm()
        );
        $out->add('</div>');
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
        $lang = &FormaLanguage::createInstance('field', 'framework');

        list($user_entry) = sql_fetch_row(sql_query('
		SELECT user_entry
		FROM ' . $this->_getUserEntryTable() . "
		WHERE id_user = '" . (int) $id_user . "' AND
			id_common = '" . (int) $this->id_common . "' AND
			id_common_son = '0'"));
        switch ($user_entry) {
            case 1 : return $lang->def('_YES'); break;
            case 2 : return $lang->def('_NO'); break;
            default: return $lang->def('_NOT_ASSIGNED'); break;
        }
    }

    public function toString($field_value)
    {
        $lang = &FormaLanguage::createInstance('field', 'framework');
        switch ($field_value) {
            case 1 : return $lang->def('_YES'); break;
            case 2 : return $lang->def('_NO'); break;
            default: return $lang->def('_NOT_ASSIGNED'); break;
        }
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
        $lang = &FormaLanguage::createInstance('field', 'framework');

        require_once _base_ . '/lib/lib.form.php';

        if (isset($_POST['field_' . self::getFieldType()])
            && isset($_POST['field_' . self::getFieldType()][$this->id_common])) {
            $user_entry = $_POST['field_' . self::getFieldType()][$this->id_common];
        } else {
            list($user_entry) = sql_fetch_row(sql_query('
			SELECT user_entry
			FROM ' . $this->_getUserEntryTable() . "
			WHERE id_user = '" . (int) $id_user . "' AND
				id_common = '" . (int) $this->id_common . "' AND
				id_common_son = '0'"));
        }
        $re_field = sql_query('
		SELECT translation
		FROM ' . $this->_getMainTable() . "
		WHERE lang_code = '" . Lang::get() . "' AND id_common = '" . (int) $this->id_common . "' AND type_field = '" . self::getFieldType() . "'");
        list($translation) = sql_fetch_row($re_field);

        switch ((int) $user_entry) {
            case 1 :
                $field_value = $lang->def('_YES');
                break;
            case 2 :
                $field_value = $lang->def('_NO');
                break;
            default:
                $field_value = $lang->def('_NOT_ASSIGNED');
                break;
        }

        if ($value !== null) {
            switch ((int) $value) {
                case 1 :
                    $field_value = $lang->def('_YES');
                    break;
                case 2 :
                    $field_value = $lang->def('_NO');
                    break;
                default:
                    $field_value = $lang->def('_NOT_ASSIGNED');
                    break;
            }
        }

        if ($registrationLayout) {
            $formField = '<div class="homepage__row homepage__row--gray homepage__row--text-left">'
                . $translation . ($mandatory ? ' <span class="mandatory">*</span>' : '')
                . '</div>'
                . '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

            $all_value = [
                $lang->def('_YES') => 1,
                $lang->def('_NO') => 2,
                $lang->def('_NOT_ASSIGNED') => 0,
            ];

            $count = 0;
            $id = 'field_' . self::getFieldType() . '_' . $this->id_common . '_' . $count;
            foreach ($all_value as $label_item => $val_item) {
                $formField .= '<div class="col-xs-12 col-sm-3">';
                $formField .= Form::getInputRadio(
                    $id,
                    'field_' . self::getFieldType() . '[' . $this->id_common . ']',
                    $val_item,
                    $val_item == (int) $user_entry,
                    '',
                    '');
                $formField .= '<label class="checkbox-inline" for="' . $id . '_' . $count . '">' . $label_item . '</label>';

                $formField .= '</div>';
                ++$count;
            }

            $formField .= '</div>';

            return $formField;
        }

        if ($freeze) {
            return Form::getLineBox($translation . ' : ', $field_value);
        }

        return Form::getRadioSet($translation . ($mandatory ? ' <span class="mandatory">*</span>' : ''),
            'field_' . self::getFieldType() . '_' . $this->id_common,
            'field_' . self::getFieldType() . '[' . $this->id_common . ']',
            [$lang->def('_YES') => 1,
                $lang->def('_NO') => 2,
                $lang->def('_NOT_ASSIGNED') => 0, ],
            (int) $user_entry,
            '',
            '');
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
     * @param mixed  $field_special (optional) not used
     *
     * @return string of field xhtml code
     */
    public function play_filter($id_field, $value = false, $label = false, $field_prefix = false, $other_after = '', $other_before = '', $field_special = false)
    {
        require_once _base_ . '/lib/lib.form.php';

        $lang = &FormaLanguage::createInstance('field');

        if ($value === false) {
            $value = Field::getFieldValue_Filter($_POST, $id_field, $field_prefix, '');
        }

        if ($label === false) {
            $re_field = sql_query('
			SELECT translation
			FROM ' . Field::_getMainTable() . "
			WHERE id_common = '" . (int) $id_field . "' AND type_field = '" . Field_YesNo::getFieldType() . "'");
            list($label) = sql_fetch_row($re_field);
        }

        return Form::getRadioSet($label,
                                    Field::getFieldId_Filter($id_field, $field_prefix),
                                    Field::getFieldName_Filter($id_field, $field_prefix),
                                    [$lang->def('_YES') => 1,
                                            $lang->def('_NO') => 2,
                                            $lang->def('_NOT_ASSIGNED') => 0, ],
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
        if (!isset($_POST['field_' . self::getFieldType()][$this->id_common])) {
            return false;
        } elseif ($_POST['field_' . self::getFieldType()][$this->id_common] != 1 &&
            $_POST['field_' . self::getFieldType()][$this->id_common] != 2) {
            return false;
        } else {
            return true;
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
        if (($int_userid) || (empty($id_user))) {
            $id_user = (int) $id_user;
        }

        if (!isset($_POST['field_' . self::getFieldType()][$this->id_common])) {
            return true;
        }
        $re_entry = sql_query('
		SELECT user_entry
		FROM ' . $this->_getUserEntryTable() . "
		WHERE id_user = '" . $id_user . "' AND
			id_common = '" . (int) $this->id_common . "' AND
			id_common_son = '0'");
        $some_entry = sql_num_rows($re_entry);
        if ($some_entry) {
            if ($no_overwrite) {
                return true;
            }
            if (!sql_query('
			UPDATE ' . $this->_getUserEntryTable() . "
			SET user_entry = '" . $_POST['field_' . self::getFieldType()][$this->id_common] . "'
			WHERE id_user = '" . $id_user . "' AND
			id_common = '" . (int) $this->id_common . "' AND
			id_common_son = '0'")) {
                return false;
            }
        } else {
            if (!sql_query('
			INSERT INTO ' . $this->_getUserEntryTable() . "
			( id_user, id_common, id_common_son, user_entry ) VALUES
			(	'" . $id_user . "',
				'" . (int) $this->id_common . "',
				'0',
				'" . $_POST['field_' . self::getFieldType()][$this->id_common] . "')")) {
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
    public function storeDirect($id_user, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        if (($int_userid) || (empty($id_user))) {
            $id_user = (int) $id_user;
        }

        switch (strtolower($value)) {
            case 'yes':
            case 'true':
            case '1':
                $value = 1;
             break;

            case '':
            case '0':
            case 'no':
            case 'null':
            case 'false':
                $value = 2;
             break;

            default: $value = 0; break;
        }

        $re_entry = sql_query('
		SELECT user_entry
		FROM ' . $this->_getUserEntryTable() . "
		WHERE id_user = '" . $id_user . "' AND
			id_common = '" . (int) $this->id_common . "' AND
			id_common_son = '0'");
        $some_entry = sql_num_rows($re_entry);
        if ($some_entry) {
            if ($no_overwrite) {
                return true;
            }
            if (!sql_query('
			UPDATE ' . $this->_getUserEntryTable() . "
			SET user_entry = '" . $value . "'
			WHERE id_user = '" . $id_user . "' AND
				id_common = '" . (int) $this->id_common . "' AND
				id_common_son = '0'")) {
                return false;
            }
        } else {
            if (!sql_query('
			INSERT INTO ' . $this->_getUserEntryTable() . "
			( id_user, id_common, id_common_son, user_entry ) VALUES
			(	'" . $id_user . "',
				'" . (int) $this->id_common . "',
				'0',
				'" . $value . "')")) {
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

        switch (strtolower($value)) {
            case 'yes':
            case 'true':
            case '1':
                $value = 1;
             break;

            case '':
            case '0':
            case 'no':
            case 'null':
            case 'false':
                $value = 2;
             break;

            default: $value = 0; break;
        }

        $query = 'SELECT id_user, user_entry FROM ' . $this->_getUserEntryTable() . ' '
            . ' WHERE id_user IN (' . implode(',', $idst_users) . ') '
            . " AND id_common = '" . (int) $this->id_common . "' AND id_common_son = '0'";
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
                    . " AND id_common = '" . (int) $this->id_common . "' AND id_common_son = '0'";
                $res1 = sql_query($query);
            }

            if (!empty($arr_new)) {
                $insert_values = [];
                foreach ($arr_new as $idst) {
                    $insert_values[] = "(	'" . (int) $idst . "', '" . (int) $this->id_common . "', '0', '" . $value . "')";
                }
                $query = 'INSERT INTO ' . $this->_getUserEntryTable() . ' '
                    . '( id_user, id_common, id_common_son, user_entry ) VALUES '
                    . implode(',', $insert_values);
                $res2 = sql_query($query);
            }
        }

        return true;
    }

    public function getClientClassObject()
    {

        return '
      {
        type: "' . self::getFieldType() . '",
      
        getValue: function(id_sel, id_filter) {
          var $D = YAHOO.util.Dom, t = [], c;
          c=$D.get("yesno_1_"+id_filter+"_"+id_sel); if (c.checked) t.push(c.value);
          c=$D.get("yesno_2_"+id_filter+"_"+id_sel); if (c.checked) t.push(c.value);
          return t.join(",");
        },
        
        setValue: function(id_sel, id_filter, newValue) {
          var i, $D = YAHOO.util.Dom, t = ((typeof newValue=="string") ? newValue.split(",") : []);
          for (i=0; i<t.length; i++) {
            switch (t[i]) {
              case "1": $D.get("yesno_1_"+id_filter+"_"+id_sel).checked=true; break;
              case "2": $D.get("yesno_2_"+id_filter+"_"+id_sel).checked=true; break;            
            }
          }
        },
        
        render: function(id_sel, id_filter, oEl) {
          var s, c, l, d = document.createElement("DIV"); d.className = "yesno_container";
          
          s = document.createElement("SPAN");
          c = document.createElement("INPUT"); c.type="checkbox"; c.id="yesno_1_"+id_filter+"_"+id_sel; c.value="1";
          l = document.createElement("LABEL"); l.htmlFor="yesno_1_"+id_filter+"_"+id_sel; l.innerHTML="' . Lang::t('_YES') . '";
          s.appendChild(c); s.appendChild(l); d.appendChild(s);
          
          s = document.createElement("SPAN");
          c = document.createElement("INPUT"); c.type="checkbox"; c.id="yesno_2_"+id_filter+"_"+id_sel; c.value="2";
          l = document.createElement("LABEL"); l.htmlFor="yesno_2_"+id_filter+"_"+id_sel; l.innerHTML="' . Lang::t('_NO') . '";
          s.appendChild(c); s.appendChild(l); d.appendChild(s);
          oEl.appendChild(d);
        }
      }    
    ';
    }

    public function checkUserField($value, $filter)
    {
        return in_array($value, explode(',', $filter));
    }

    public function getFieldQuery($filter)
    { //0:not do; 1:yes; 2:no
        $yes_trans = strtolower(def('_YES'));
        $no_trans = strtolower(def('_NO'));

        switch ($filter['value']) {
            case $yes_trans :  $value[] = '1'; break;
            case $no_trans :  $value[] = '2'; break;
            case 1:  $value[] = '1'; break;
            case 2:  $value[] = '2'; break;
            default:return '0';
        }
        $output = 'SELECT id_user ' .
            'FROM  ' . $this->_getUserEntryTable() . '  ' .
            "WHERE id_common = '" . $this->id_common . "' AND user_entry IN ( " . implode(',', $value) . ' ) ';

        return $output;
    }
}
