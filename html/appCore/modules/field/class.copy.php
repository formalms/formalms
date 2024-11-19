<?php

use FormaLms\lib\Forma;
use phpDocumentor\Reflection\PseudoTypes\False_;

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

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

require_once Forma::inc(_adm_ . '/modules/field/class.field.php');


class Copy extends Field
{
    public static $_obj;

    /**
     * class constructor.
     */
    public function __construct($id_common, $id_common_copy_ref = null)
    {
        parent::__construct($id_common, $id_common_copy_ref);

        if ($id_common) {
            list($copy_of) = sql_fetch_row(sql_query('SELECT copy_of FROM core_field WHERE id_common = ' . $id_common));
            list($type_field) = sql_fetch_row(sql_query('SELECT type_field FROM core_field WHERE id_common = ' . $copy_of));
            list($type_file, $type_class) = sql_fetch_row(sql_query('SELECT type_file, type_class FROM core_field_type WHERE type_field = "' . $type_field . '"'));

            try {
                require_once Forma::inc(_adm_ . '/modules/field/' . $type_file);

                self::$_obj = new $type_class($id_common, $copy_of);

            } catch (Exception $e) {
                self::$_obj = null;
            }
        }
    }

    public static function getFieldType()
    {
        if (self::$_obj) {
            return self::$_obj::getFieldType();
        } else {
            return 'copy';
        }
    }

    public function create($back)
    {
        $back_coded = htmlentities(urlencode($back));

        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_POST['undo'])) {
            //undo action
            Util::jump_to($back . '&result=undo');
        }

        if (isset($_POST['save_field_copy']) && isset($_POST['copy_of']) && isset($_POST['new_copylabel'])) {
            $mand_lang = Lang::get();

            $arr = explode('-', $_POST['copy_of']);

            $copy_of = $arr[0];
            $father = $arr[1];
            $son = $arr[2];

            $show_on = '';
            if (isset($_POST['show_on_platform'])) {
                foreach ($_POST['show_on_platform']  as $code => $value) {
                    $show_on .= $code . ',';
                }
            } else {
                $show_on = 'framework,lms,scs,';
            }

            $use_multilang = (isset($_POST['use_multi_lang']) ? 1 : 0);

            $max_sequence = sql_fetch_row(sql_query('SELECT MAX(sequence) FROM ' . $this->_getMainTable()));

            if (is_array($max_sequence)) {
                $max_sequence = reset($max_sequence);
            }

            $query = 'INSERT INTO ' . $this->_getMainTable() . "(type_field, lang_code, translation, show_on_platform, use_multilang, sequence, copy_of, father, son) VALUES ('" . self::getFieldType() . "', '" . $mand_lang . "', '" . $_POST['new_copylabel'][$mand_lang] . "', '" . $show_on . "', '" . $use_multilang . "', '" . $max_sequence . "', '" . $copy_of . "', '" . $father . "', '" . $son . "')";

            if (!sql_query($query)) {
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
            foreach ($_POST['new_copylabel'] as $lang_code => $translation) {
                if ($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $query = 'INSERT INTO ' . $this->_getMainTable() . "
                                    (type_field, id_common, lang_code, translation, show_on_platform, use_multilang, sequence, copy_of, father, son) VALUES
                                    ('" . self::getFieldType() . "', '" . (int) $id_common . "', '" . $lang_code . "', '" . $translation . "', '" . $show_on . "', '" . $use_multilang . "', '" . $max_sequence . "', '" . $copy_of . "', '" . $father . "', '" . $son . "') ";

                    $re_ins = sql_query($query);
                    $re = $re && $re_ins;
                }
            }

            Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail'));
        } else {
            $query = 'SELECT id_common, father, son, translation  FROM ' . $this->_getMainTable() . " WHERE copy_of IS NULL AND type_field != 'copy' AND lang_code LIKE '" . Lang::get() . "' GROUP BY id_common ORDER BY sequence ASC";

            $res = sql_query($query);

            $fields = [];

            while ($row = sql_fetch_assoc($res)) {
                $fields[$row['id_common'] . '-' . $row['father'] . '-' . $row['son']] = $row['translation'];
            }

            require_once _base_ . '/lib/lib.form.php';

            $form = new Form();

            $out->setWorkingZone('content');
            $out->add('<div class="std_block">');
            $out->add('<div class="label_block"><p>' . $form->getLabel($for, Lang::t('COPY_MESSAGE', 'field')) . '</p></div>');
            $out->add(
                $form->openForm('create_' . self::getFieldType(), $this->getUrl())
                . $form->openElementSpace()
                . $form->getHidden('type_field', 'type_field', self::getFieldType())
                . $form->getHidden('back', 'back', $back_coded)
            );

            $mand_lang = Lang::get();

            $out->add($form->getDropdown($lang->def('_COPY_OF'), 'ciao2', 'copy_of', $fields));

            foreach ($array_lang as $k => $lang_code) {
                $out->add(
                    $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                        'new_copylabel_' . $lang_code,
                        'new_copylabel[' . $lang_code . ']',
                        255,
                        '',
                        $lang_code . ' ' . $lang->def('_FIELD_NAME'))
                );
            }

            $out->add(
                $form->closeElementSpace()
                . $form->openButtonSpace()
                . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
                . $form->getButton('save_field', 'save_field_copy', $std_lang->def('_CREATE', 'standard'))
                . $form->closeButtonSpace()
                . $form->closeForm()
            );
            $out->add('</div>');
        }
    }

    public function edit($back)
    {
        $back_coded = htmlentities(urlencode($back));

     
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_GET['fo']) && $_GET['fo'] = 'edit' && !isset($_POST['save_field_copy'])) {
            $id_common = $_REQUEST['id_common'];

            if (isset($_POST['undo'])) {
                //undo action
                Util::jump_to($back . '&result=undo');
            }

            $res = sql_query('SELECT * FROM ' . $this->_getMainTable() . ' WHERE id_common = ' . $id_common);

            $fields = [];
            $fieldNames = [];
            while ($row = sql_fetch_assoc($res)) {
                $fieldNames[$row['lang_code']] = $row['translation'];
            }

            $res = sql_query('SELECT id_common, father, son, translation  FROM ' . $this->_getMainTable() . " WHERE copy_of IS NULL AND type_field != 'copy' AND lang_code LIKE '" . Lang::get() . "' GROUP BY id_common ORDER BY sequence ASC");

            while ($row = sql_fetch_assoc($res)) {
                $fields[$row['id_common'] . '-' . $row['father'] . '-' . $row['son']] = $row['translation'];
            }

            require_once Forma::inc(_base_ . '/lib/lib.form.php');

            $form = new Form();

            $out->setWorkingZone('content');
            $out->add('<div class="std_block">');
            $out->add('<div class="label_block"><p>' . $form->getLabel($for, Lang::t('COPY_MESSAGE', 'field')) . '</p></div>');
            $out->add(
                $form->openForm('edit_' . self::getFieldType(), $this->getUrl())
                . $form->openElementSpace()
                . $form->getHidden('type_field', 'type_field', 'copy')
                . $form->getHidden('id_common', 'id_common', $id_common)
                . $form->getHidden('back', 'back', $back_coded)
            );

            $mand_lang = Lang::get();

            $out->add($form->getDropdown($lang->def('_COPY_OF'), 'ciao2', 'copy_of', $fields));

            foreach ($array_lang as $k => $lang_code) {
                $out->add(
                    $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                        'edit_copylabel_' . $lang_code,
                        'edit_copylabel[' . $lang_code . ']',
                        255,
                        $fieldNames[$lang_code],
                        $lang_code . ' ' . $lang->def('_FIELD_NAME'))
                );
            }

            $out->add(
                $form->closeElementSpace()
                . $form->openButtonSpace()
                . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
                . $form->getButton('save_field', 'save_field_copy', $std_lang->def('_SAVE', 'standard'))
                . $form->closeButtonSpace()
                . $form->closeForm()
            );
            $out->add('</div>');
        } elseif (isset($_POST['save_field_copy'])) {
            $arr = explode('-', $_POST['copy_of']);
            $id_common = $_REQUEST['id_common'];
            $mand_lang = Lang::get();

            $copy_of = $arr[0];
            $father = $arr[1];
            $son = $arr[2];

            $re = true;

            //insert other field
            foreach ($_POST['edit_copylabel'] as $lang_code => $translation) {
                if ($translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $query = 'UPDATE ' . $this->_getMainTable() . " set translation = '" . $translation . "', son = " . $son . ' , copy_of = ' . $copy_of . ', father = ' . $father . " WHERE lang_code like '" . $lang_code . "' AND id_common = $id_common";
                    $re_ins = sql_query($query);
                    $re = $re && $re_ins;
                }
            }
            Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail'));
        }
    }

    public function show($id_user)
    {
        return self::$_obj ? self::$_obj->show($id_user) : false;
    }

    public function play($id_user, $freeze, $mandatory = false, $do_not_show_label = false, $value = null, $registrationLayout = false, $registrationErrors = false)
    {
        return self::$_obj ? self::$_obj->play($id_user, $freeze, $mandatory, $do_not_show_label, $value, $registrationLayout, $registrationErrors) : false;
    }

    public function play_filter($id_field, $value = false, $label = false, $field_prefix = false, $other_after = '', $other_before = '', $field_special = false)
    {
        return self::$_obj ? self::$_obj->play_filter($id_field, $value, $label, $field_prefix, $other_after, $other_before, $field_special) : false;
    }

    public function getFieldValue_Filter($array_values, $id_field, $field_prefix = false, $default_value = '')
    {
        return self::$_obj ? self::$_obj->getFieldValue_Filter($array_values, $id_field, $field_prefix, $default_value) : false;
    }

    public function isFilled($id_user)
    {
        return self::$_obj ? self::$_obj->isFilled($id_user) : false;
    }

    public function store($id_user, $no_overwrite, $int_userid = true)
    {
        return self::$_obj ? self::$_obj->store($id_user, $no_overwrite, $int_userid) : null;
    }

    public function storeDirect($id_user, $value, $is_id, $no_overwrite, $int_userid = true)
    {
        return self::$_obj ? self::$_obj->storeDirect($id_user, $value, $is_id, $no_overwrite, $int_userid) : false;
    }

    public function isValid($id_user)
    {
        return self::$_obj ? self::$_obj->isValid($id_user) : false;
    }
}
