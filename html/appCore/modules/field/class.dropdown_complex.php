<?php

use FormaLms\lib\Forma;

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

require_once Forma::include(_adm_ . '/modules/field/', 'class.dropdown.php');


class Field_DropdownComplex extends Field_Dropdown
{

    public $id_common_copy_ref;


    public static function getFieldType()
    {
        return 'dropdown_complex';
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
    public function play($id_user, $freeze, $mandatory = false, $do_not_show_label = false, $value = null, $registrationLayout = false, $registrationErrors = false, $id_selected = null, $ajax = false)
    {
        require_once _base_ . '/lib/lib.form.php';

        if (isset($_POST['field_' . $this->getFieldType()]) && isset($_POST['field_' . $this->getFieldType()][$this->id_common])) {
            $user_entry = $_POST['field_' . $this->getFieldType()][$this->id_common];
        } else {
            list($user_entry) = sql_fetch_row(sql_query('
			SELECT user_entry
			FROM ' . $this->_getUserEntryTable() . "
			WHERE id_user = '" . (int) $id_user . "' AND
				id_common = '" . (int) $this->id_common . "' AND
				id_common_son = '0'"));
        }

        $user_entry = (int) $user_entry;

        $re_field = sql_query('
            SELECT translation, son, father
            FROM ' . $this->_getMainTable() . "
            WHERE id_common = '" . (int) $this->id_common . "' AND lang_code = '" .  \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . "'");

        list($translation, $son, $father) = sql_fetch_row($re_field);

        if ($father) {
            $open_tag = '<span id="span_for_' . $this->id_common . '">';
            $end_tag = '</span>';
            list($father_user_selected) = sql_fetch_row(sql_query('
                SELECT user_entry
                FROM ' . $this->_getUserEntryTable() . '
                WHERE id_common = ' . $father . ' AND id_user = ' . $id_user));
        }

        if ($id_selected !== null) {
            $belongs_to = $id_selected;
            $user_entry = null;
        } else {
            if ($user_entry) {
                list($belongs_to) = sql_fetch_row(sql_query('
                    SELECT belongs_to
                    FROM ' . $this->_getElementTable() . '
                    WHERE id_common_son = ' . $user_entry . " AND lang_code = '" . \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . "'"));
            } else {
                if ($father_user_selected) {
                    $belongs_to = $father_user_selected;
                } else {
                    $belongs_to = null;
                }
            }
        }

        (($belongs_to || $belongs_to == 0) && !empty($father)) ? $filter = 'AND belongs_to = ' . $belongs_to : $filter = '';
        $idField = ($this->id_common_copy_ref ? $this->id_common_copy_ref : $this->id_common);

        $sql = 'SELECT  translation, id_common_son, selected FROM ' . $this->_getElementTable() . " WHERE idField = '" . (int) $idField . "' AND lang_code = '" . \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . "' " . $filter . ' ORDER BY translation';

        $re_field_element = sql_query($sql);
        $option = [];
        $option[] = $translation . ($mandatory ? ' *' : '');
        $selectedCommonSon = 0;
        while ($row = sql_fetch_assoc($re_field_element)) {
            $option[$row['id_common_son']] = $row['translation'];
            if ($row['selected']) {
                $selectedCommonSon = $row['id_common_son'];
            }
        }

        if ($son) {
            $re_field = sql_query('
                SELECT translation, son, father
                FROM ' . $this->_getMainTable() . "
                WHERE id_common = '" . (int) $son . "' AND
                lang_code = '" . \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . "'");

            list($translation_son, $son_son, $father_son) = sql_fetch_row($re_field);

            $values = [];


            while ($row = sql_fetch_assoc($result)) {
                $values[$row['param_name']] = $row;
            }

            if ($id_selected === null) {
                YuiLib::load('selector');
                $js_accrocchio = '';

                if ($selectedCommonSon !== 0) {
                    $js_accrocchio .= "frontend.functions.change_sons('$son','$idField',0,$selectedCommonSon,'$registrationLayout');";
                }
                $js_new = '<script type="text/javascript">$( document ).ready(function() {' . $js_accrocchio . '});</script>';
            } else {
                $ret = 'disable_son(' . $son . ',"' . $translation_son . ($mandatory ? ' *' : '') . '");id_html=' . $this->id_common . ';%%';
            }

            $ajaxChange = '';
            if (!$id_user) {
                $id_user = 0;
            }

            $sqlCopy = 'SELECT * FROM ' . $this->_getMainTable() . ' WHERE father = ' . (int) $this->id_common . " AND lang_code = '" . \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . "' ";

            $result = sql_query($sqlCopy);

            $js_inject = '';
            $copies_father = '';
            while ($copy_row = sql_fetch_assoc($result)) {
                $copies_father = $this->id_common;
                $copies[] = $copy_row['id_common'];

                $js_inject .= 'frontend.functions.change_sons(' . $copy_row['id_common'] . ',' . $this->id_common . ',' . $id_user . ',this.value,' . $son . ',' . $registrationLayout . ');';
            }

            if ($this->id_common == $copies_father) {
                $ajaxChange .= 'onchange="';
                $ajaxChange .= $js_inject;
                $ajaxChange .= '"';
            } else {
                $ajaxChange .= 'onchange="frontend.functions.change_sons(' . $copy_row['id_common'] . ',' . $this->id_common . ',' . $id_user . ',this.value,' . $registrationLayout . ');"';
            }
        }

        $sql = 'SELECT * FROM ' . $this->_getMainTable() . ' WHERE id_common = ' . (int) $this->id_common;
        $result = sql_fetch_assoc(sql_query($sql));

        if (($this->id_common_copy_ref === $this->id_common || ($this->id_common_copy_ref === (int) $result['copy_of'] && (int) $result['copy_of'] !== 0)) || $ajax === true) {
            $ret = 'id_html=' . $this->id_common . ';%%';
        }

        $disabled = (count($option) == 1 && $father) ? ' disabled="disabled"' : '';

        if ($freeze) {
            return Form::getLineBox($translation . ' : ', $option[$user_entry]);
        }

        if ($registrationLayout) {
            $error = (isset($registrationErrors) && $registrationErrors[$this->id_common]);
            $errorMessage = $registrationErrors[$this->id_common]['msg'];

            if ($this->id_common_copy_ref === $this->id_common || $ajax === true) {
                $formField = '';
            } else {
                $ret = '';
                $formField = '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';
            }

            $formField .= '<div data-father="' . $father . '" id="span_for_' . $this->id_common . '" class="col-xs-12 col-sm-6 forma-select-no-label ' . ($mandatory ? 'mandatory' : '') . '">';

            $formField .= Form::getInputDropdown(
                'form-control ' . ($error ? 'has-error' : ''),
                'field_' . $this->getFieldType() . '_' . $this->id_common,
                'field_' . $this->getFieldType() . '[' . $this->id_common . ']',
                $option,
                ($user_entry ? (int) $user_entry : $selectedCommonSon),
                $ajaxChange . $disabled,
                true
            );

            if ($error) {
                $formField .= '<small class="form-text">* ' . $errorMessage . '</small>';
            }
            $formField .= '</div>';

            if ($this->id_common_copy_ref === $this->id_common || $ajax === true) {
                $formField .= '';
            } else {
                $formField .= '</div>';
            }
        } else {
            $formField = $open_tag . Form::getDropdown(
                $translation . ($mandatory ? ' <span class="mandatory">*</span>' : ''),
                'field_' . $this->getFieldType() . '_' . $this->id_common,
                'field_' . $this->getFieldType() . '[' . $this->id_common . ']',
                $option,
                ($user_entry ? (int) $user_entry : $selectedCommonSon),
                '',
                '',
                $ajaxChange . $disabled
            ) . $end_tag;
        }

        if ($ajax !== true) {
            $js_new .= '<script type="text/javascript"> 
                var registration_jayout = ' . ($registrationLayout ? 'true' : 'false') . ';
                frontend.functions.initDropdownComplex();'
                . '</script>';
        }

        return $js_new . $ret . $formField;
    }

    public function store($id_user, $no_overwrite, $int_userid = true)
    {
        parent::store($id_user, $no_overwrite, $int_userid);
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
        if (isset($_POST['save_field_' . static::getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();
            $show_on = '';
            if (isset($_POST['show_on_platform'])) {
                foreach ($_POST['show_on_platform']  as $code => $value) {
                    $show_on .= $code . ',';
                }
            }
            //control if all is ok
            if (!isset($_POST['new_dropdown'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi($this->getUrl() . '&amp;type_field='
                            . self::getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_dropdown'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_dropdown'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi($this->getUrl() . '&amp;type_field='
                            . self::getFieldType() . '&amp;back=' . $back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            //insert mandatory field
            if (!sql_query('
			INSERT INTO ' . $this->_getMainTable() . "
			(type_field, lang_code, translation, show_on_platform, use_multilang, father) VALUES
			('" . self::getFieldType() . "', '" . $mand_lang . "', '" . $_POST['new_dropdown'][$mand_lang] . "', '" . $show_on . "', '" . $use_multilang . "', '" . $_POST['father_id'] . "') ")) {
                Util::jump_to($back . '&result=fail');
            }
            list($id_common) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

            //if father id is greater than zero update the father with son id
            if ((int) $_POST['father_id'] > 0) {
                sql_query('
                UPDATE ' . $this->_getMainTable() . "
                SET son = '" . (int) $id_common . "'
                WHERE id_common = '" . (int) $_POST['father_id'] . "'");
            }

            if (!sql_query('
			UPDATE ' . $this->_getMainTable() . "
			SET id_common = '" . (int) $id_common . "'
			WHERE idField = '" . (int) $id_common . "'")) {
                Util::jump_to($back . '&result=fail');
            }
            $re = true;
            //insert other field
            foreach ($_POST['new_dropdown'] as $lang_code => $translation) {
                if ($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $re_ins = sql_query('
					INSERT INTO ' . $this->_getMainTable() . "
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang, father) VALUES
					('" . self::getFieldType() . "', '" . (int) $id_common . "', '" . $lang_code . "', '" . $translation . "', '" . $show_on . "', '" . $use_multilang . "', '" . $_POST['father_id'] . "') ");
                    $re = $re && $re_ins;
                }
            }
            Util::jump_to($back . '&result=' . ($re ? 'success' : 'fail'));
        }

        $re_field = sql_query('SELECT id_common, translation FROM ' . $this->_getMainTable() . '
                                WHERE (son IS NULL OR son = 0)
                                AND type_field = "dropdown_complex"
                                AND lang_code = "' . \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . '"
                                ORDER BY translation');



        $field_select = [0 => Lang::t('_NO_SELECTION')];
        foreach ($re_field ?? [] as $field) {
            $field_select[$field['id_common']] = $field['translation'];
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->getFormHeader($lang->def('_NEW_DROPDOWN'))
                . $form->openForm('create_' . static::getFieldType(), $this->getUrl())
                . $form->openElementSpace()
                . $form->getHidden('type_field', 'type_field', static::getFieldType())
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
                    $lang_code . ' ' . $lang->def('_FIELD_NAME')
                )
            );
        }

        $GLOBALS['page']->add($this->getMultiLangCheck(), 'content');
        $GLOBALS['page']->add($this->getShowOnPlatformFieldset(), 'content');

        $out->add(
            $form->getDropdown(
                $lang->def('_FATHER_AREA'),
                'father_id',
                'father_id',
                $field_select
            )
        );

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

    public function _edit_field()
    {
        $array_lang = [];
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_POST['undo'])) {
            //undo action
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['save_field_' . static::getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();
            $show_on = '';
            $father_id = $_POST['father_id'];
            $changeFather = null;
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
                            . static::getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['new_textfield'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_textfield'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi($this->getUrl() . '&amp;type_field='
                            . static::getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $existsing_translation = [];
            $re_trans = sql_query('
			SELECT lang_code, father
			FROM ' . $this->_getMainTable() . "
			WHERE id_common = '" . $this->id_common . "'");
            while (list($l_code, $father) = sql_fetch_row($re_trans)) {
                $existsing_translation[$l_code] = 1;

                if($father != $father_id) {
                    $changeFather = $father;
                }
            }

            $use_multilang = (isset($_POST['use_multi_lang']) ? 1 : 0);

            $re = true;
            //insert other field
            foreach ($_POST['new_textfield'] as $lang_code => $translation) {
                if (isset($existsing_translation[$lang_code])) {
                    if (!sql_query('
					UPDATE ' . $this->_getMainTable() . "
					SET translation = '" . $translation . "',
						show_on_platform = '" . $show_on . "',
						use_multilang = '" . $use_multilang . "',
                        father = '". $father_id . "'
					WHERE id_common = '" . (int) $this->id_common . "' AND lang_code = '" . $lang_code . "'")) {
                        $re = false;
                    }
                } else {
                    if (!sql_query('
					INSERT INTO ' . $this->_getMainTable() . "
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang, father) VALUES
					('" . static::getFieldType() . "', '" . (int) $this->id_common . "', '" . $lang_code . "', '" . $translation . "', '" . $show_on . "', '" . $use_multilang . "', '" . $father_id . "') ")) {
                        $re = false;
                    }
                }
            }

            //i'm vhanging father so i change son  references
            if($changeFather) {
                sql_query('
					UPDATE ' . $this->_getMainTable() . "
					SET son = '" . (int) $this->id_common . "'
						WHERE id_common = '" . $father_id . "'");
                sql_query('
                        UPDATE ' . $this->_getMainTable() . "
                        SET son = 0
                            WHERE id_common = '" . $changeFather . "'");
            }
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded
                . '&result=' . ($re ? 'success' : 'fail'));
        }

        $re_field = sql_query('SELECT id_common, translation FROM ' . $this->_getMainTable() . '
        WHERE (son IS NULL OR son = 0 OR son = '.$this->id_common.')
        AND id_common <> '.$this->id_common.'
        AND type_field = "dropdown_complex"
        AND lang_code = "' . \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getLanguage() . '"
        ORDER BY translation');



        $field_select = [0 => Lang::t('_NO_SELECTION')];
        foreach ($re_field ?? [] as $field) {
            $field_select[$field['id_common']] = $field['translation'];
        }


        //load value form database
        $re_trans = sql_query('
		SELECT lang_code, translation, show_on_platform, use_multilang, father
		FROM ' . $this->_getMainTable() . "
		WHERE id_common = '" . $this->id_common . "'");
        while (list($l_code, $trans, $show_on, $db_use_multilang, $father) = sql_fetch_row($re_trans)) {
            $translation[$l_code] = $trans;
            if (!isset($show_on_platform)) {
                $show_on_platform = array_flip(explode(',', $show_on));
            }
            if (!isset($use_multilang)) {
                $use_multilang = $db_use_multilang;
            }

            $father_id = $father;
        }

        //if it has a father i can't change it if some of his choices are already joined to users 
        if ($father_id) {
            list($countEntries) = sql_fetch_row(sql_query('
            SELECT count(*) as cnt
            FROM %adm_field_userentry
            WHERE id_common = "' . $this->id_common . '"'));
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->openForm('create_' . static::getFieldType(), $this->getUrl())
                . $form->openElementSpace()
                . $form->getHidden('type_field', 'type_field', static::getFieldType())
                . $form->getHidden('id_common', 'id_common', $this->id_common)
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
                    $lang_code . ' ' . $lang->def('_FIELD_NAME')
                )
            );
        }

        $GLOBALS['page']->add($this->getMultiLangCheck($use_multilang), 'content');
        $GLOBALS['page']->add($this->getShowOnPlatformFieldset($show_on_platform), 'content');

        if(!$father_id || !$countEntries) {
            $out->add(
                $form->getDropdown(
                    $lang->def('_FATHER_AREA'),
                    'father_id',
                    'father_id',
                    $field_select,
                    $father_id
                )
            );
        } else {
            $form->getHidden('father_id', 'father_id', $father_id);
        }
       

        $out->add(
            $form->closeElementSpace()
                . $form->openButtonSpace()
                . $form->getButton('save_field', 'save_field_' . static::getFieldType(), $std_lang->def('_SAVE', 'standard'))
                . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
                . $form->closeButtonSpace()
                . $form->closeForm()
        );
        $out->add('</div>');
    }



    public function _add_son()
    {
        $array_lang = [];
        $choicesDropdown = [];
        $std_lang = &FormaLanguage::createInstance('standard');
        $lang = &FormaLanguage::createInstance('field');
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $out = &$GLOBALS['page'];

        if (isset($_POST['undo'])) {
            //undo action
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['save_field_' . static::getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();

            //control if all is ok
            if (!isset($_POST['new_dropdown_son'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi(
                            $this->getUrl() . '&amp;id_common='
                                . $this->id_common . '&amp;type_field=' . static::getFieldType() . '&amp;back=' . $this->back_coded,
                            $std_lang->def('_BACK')
                        ),
                    'content'
                );

                return;
            }
            if ($_POST['new_dropdown_son'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_dropdown_son'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi(
                            $this->getUrl() . '&amp;id_common='
                                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded,
                            $std_lang->def('_BACK')
                        ),
                    'content'
                );

                return;
            }

            list($sequence) = sql_fetch_row(sql_query('
			SELECT COUNT(*)
			FROM ' . $this->_getElementTable() . "
			WHERE idField = '" . $this->id_common . "' AND lang_code = '" . Lang::get() . "'"));

            ++$sequence;

            //insert mandatory field
            if (!sql_query('
			INSERT INTO ' . $this->_getElementTable() . "
			(idField, lang_code, translation, sequence) VALUES
			('" . $this->id_common . "', '" . $mand_lang . "', '" . $_POST['new_dropdown_son'][$mand_lang] . "', '" . $sequence . "') ")) {
                Util::jump_to($this->getUrl() . '&id_common='
                    . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded
                    . '&result=fail');
            }
            list($id_common_son) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            if (!sql_query('
			UPDATE ' . $this->_getElementTable() . "
			SET id_common_son = '" . (int) $id_common_son . "'
			WHERE idSon = '" . (int) $id_common_son . "'")) {
                Util::jump_to($this->getUrl() . '&id_common='
                    . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded
                    . '&result=fail');
            }
            $re = true;
            //insert other field
            foreach ($_POST['new_dropdown_son'] as $lang_code => $translation) {
                if ($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
                    $re_ins = sql_query('
					INSERT INTO ' . $this->_getElementTable() . "
					(idField, id_common_son, lang_code, translation, sequence) VALUES
					('" . $this->id_common . "', '" . (int) $id_common_son . "', '" . $lang_code . "', '" . $translation . "', '" . $sequence . "') ");
                    $re = $re && $re_ins;
                }
            }
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded
                . '&result=' . ($re ? 'success' : 'fail'));
        }


        //check if id_common has father
        list($father_id) = sql_fetch_row(sql_query('
        SELECT father
        FROM ' . $this->_getMainTable() .
            ' WHERE id_common = "' . $this->id_common . '"'));



        if ($father_id) {
            $choices =   sql_query('
                SELECT idSon, translation
                FROM ' . $this->_getElementTable() .
                ' WHERE idField = "' . $father_id . '" and lang_code = "' . Lang::get() . '"');
            foreach ($choices ?? [] as $choice) {
                $choicesDropdown[$choice['idSon']] = $choice['translation'];
            }
        }

        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->getFormHeader($lang->def('_DROPDOWN_SON_NEW'))
                . $form->openForm('create_' . static::getFieldType(), $this->getUrl())
                . $form->openElementSpace()
                . $form->getHidden('type_field', 'type_field', static::getFieldType())
                . $form->getHidden('id_common', 'id_common', $this->id_common)
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
                    $lang_code . ' ' . $lang->def('_FIELD_NAME')
                )
            );
        }


        if (count($choicesDropdown)) {
            $out->add(
                $form->getDropdown(
                    $lang->def('_SON_AREA'),
                    'belongs_to',
                    'belongs_to',
                    $choicesDropdown
                )
            );
        }


        $out->add(
            $form->closeElementSpace()
                . $form->openButtonSpace()
                . $form->getButton('save_field', 'save_field_' . static::getFieldType(), $std_lang->def('_CREATE', 'standard'))
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
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['save_field_' . static::getFieldType()])) {
            //insert mandatory translation
            $mand_lang = Lang::get();

            $belongsToChanges = $_POST['belongs_to'];
            //control if all is ok
            if (!isset($_POST['mod_dropdown_son'][$mand_lang])) {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi($this->getUrl() . '&amp;type_field='
                            . static::getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }
            if ($_POST['mod_dropdown_son'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['mod_dropdown_son'][$mand_lang]) == '') {
                $out->add(
                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                        . getBackUi($this->getUrl() . '&amp;type_field='
                            . static::getFieldType() . '&amp;back=' . $this->back_coded, $std_lang->def('_BACK')),
                    'content'
                );

                return;
            }

            $existsing_translation = [];
            $re_trans = sql_query('
			SELECT lang_code, belongs_to
			FROM ' . $this->_getElementTable() . "
			WHERE id_common_son = '" . (int) $idSon . "' AND
				idField = '" . (int) $this->id_common . "'");
            while (list($l_code, $belongs_to) = sql_fetch_row($re_trans)) {
                $existsing_translation[$l_code] = 1;

                $belongsTo = $belongs_to;
            }

            $re = true;
            //insert other field
            foreach ($_POST['mod_dropdown_son'] as $lang_code => $translation) {
                if (isset($existsing_translation[$lang_code])) {
                    if (!sql_query('
					UPDATE ' . $this->_getElementTable() . "
					SET translation = '" . $translation . "',
                    belongs_to = '" . ($belongsToChanges ?? $belongsTo) . "'
					 WHERE id_common_son = '" . (int) $idSon . "' AND
						idField = '" . (int) $this->id_common . "' AND
						lang_code = '" . $lang_code . "'")) {
                        $re = false;
                    }
                } else {
                    if (!sql_query('
					INSERT INTO ' . $this->_getElementTable() . "
					(idField, id_common_son, lang_code, translation, belongs_to) VALUES
					('" . (int) $this->id_common . "', '" . (int) $idSon . "', '" . $lang_code . "', '" . $translation . "','" . ($belongsToChanges ?? $belongsTo) ."') ")) {
                        $re = false;
                    }
                }
            }
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded
                . '&result=' . ($re ? 'success' : 'fail'));
        }

        //check if id_common has father
        list($father_id) = sql_fetch_row(sql_query('
           SELECT father
           FROM ' . $this->_getMainTable() .
            ' WHERE id_common = "' . $this->id_common . '"'));



        if ($father_id) {
            $choices =   sql_query('
                   SELECT idSon, translation
                   FROM ' . $this->_getElementTable() .
                ' WHERE idField = "' . $father_id . '" and lang_code = "' . Lang::get() . '"');
            foreach ($choices ?? [] as $choice) {
                $choicesDropdown[$choice['idSon']] = $choice['translation'];
            }
        }

        $belongsTo = null;

        //load value form database
        $re_trans = sql_query('
		SELECT lang_code, translation, belongs_to
		FROM ' . $this->_getElementTable() . "
		WHERE id_common_son = '" . $idSon . "' AND idField = '" . (int) $this->id_common . "'");
        while (list($l_code, $trans, $belongs_to) = sql_fetch_row($re_trans)) {
            $translation[$l_code] = $trans;
            $belongsTo = $belongs_to;
        }

        //check if any user entered as entry this choice
        $re_already_chosen = sql_query('
        SELECT count(*) as cnt
        FROM %adm_field_userentry
        WHERE user_entry = "' . $idSon . '" AND id_common = "' . (int) $this->id_common . '"');
        list($countChosen) = sql_fetch_row($re_already_chosen);



        require_once _base_ . '/lib/lib.form.php';

        $form = new Form();

        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        $out->add(
            $form->openForm('del_' . static::getFieldType(), $this->getUrl())
                . $form->openElementSpace()
                . $form->getHidden('type_field', 'type_field', static::getFieldType())
                . $form->getHidden('id_common', 'id_common', $this->id_common)
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
                    $lang_code . ' ' . $lang->def('_FIELD_NAME')
                )
            );
        }

        if (count($choicesDropdown ?? []) && !$countChosen) {
            $out->add(
                $form->getDropdown(
                    $lang->def('_SON_AREA'),
                    'belongs_to',
                    'belongs_to',
                    $choicesDropdown,
                    $belongsTo
                )
            );
        }


        $out->add(
            $form->closeElementSpace()
                . $form->openButtonSpace()
                . $form->getButton('save_field', 'save_field_' . static::getFieldType(), $std_lang->def('_SAVE', 'standard'))
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
            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded);
        }
        if (isset($_POST['confirm'])) {
            $query_del = '
			DELETE FROM ' . $this->_getUserEntryTable() . "
			WHERE user_entry = '" . $idSon . "'
			AND id_common = " . (int) $this->id_common;
            $re = sql_query($query_del);
            if (!$re) {
                Util::jump_to($this->getUrl() . '&id_common='
                    . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded . '&amp;result=fail');
            }

            $query_del = '
			DELETE FROM ' . $this->_getElementTable() . "
			WHERE idField = '" . (int) $this->id_common . "' AND
				id_common_son = '" . (int) $idSon . "'";
            $re = sql_query($query_del);

            Util::jump_to($this->getUrl() . '&id_common='
                . $this->id_common . '&type_field=' . static::getFieldType() . '&back=' . $this->back_coded
                . '&amp;result=' . ($re ? 'success' : 'fail'));
        }

        $re_main = sql_query('
		SELECT translation
		FROM ' . $this->_getElementTable() . "
		WHERE id_common_son = '" . $idSon . "' AND lang_code = '" . Lang::get() . "'
		ORDER BY sequence");
        list($translation) = sql_fetch_row($re_main);



        //check if any user entered as entry this choice
        $re_already_chosen = sql_query('
		SELECT count(*) as cnt
		FROM %adm_field_userentry
		WHERE user_entry = "' . $idSon . '" AND id_common = "' . (int) $this->id_common . '"');
        list($countChosen) = sql_fetch_row($re_already_chosen);
        $out->setWorkingZone('content');
        $out->add('<div class="std_block">');
        if ($countChosen) {

            $out->add($form->openForm('del_' . static::getFieldType(), $this->getUrl())
                . $form->getHidden('type_field', 'type_field', static::getFieldType())
                . $form->getHidden('id_common', 'id_common', $this->id_common)
                . $form->getHidden('idSon', 'idSon', $idSon)
                . $form->getHidden('back', 'back', $this->back_coded)
                . '<div class="boxinfo_container">'
                . $lang->def('_EXISTING_CHOICE_CANNOT_PROCEED') . ' : ' . $translation
                . '</div>'
                . '<div class="del_container">'
                . $form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'), 'transparent_undo_button')
                . '</div>'

                . $form->closeForm());
        } else {

            $out->add(
                $form->openForm('del_' . static::getFieldType(), $this->getUrl())
                    . $form->getHidden('type_field', 'type_field', static::getFieldType())
                    . $form->getHidden('id_common', 'id_common', $this->id_common)
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
        }

        $out->add('</div>');
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
		WHERE idField = '" . $this->id_common . "' AND lang_code = '" . Lang::get() . "'"));

        $re_main = sql_query('
		SELECT translation
		FROM ' . $this->_getMainTable() . "
		WHERE id_common = '" . $this->id_common . "' AND lang_code = '" . Lang::get() . "'
		ORDER BY sequence");
        list($translation) = sql_fetch_row($re_main);

        //find available son
        $re_field = sql_query('
		SELECT cfs.id_common_son, cfs.translation, coalesce(cff.translation, "'.    $lang->def('_NO_SELECTION').'") as father_translation
		FROM ' . $this->_getElementTable() . ' cfs
        LEFT JOIN ' . $this->_getElementTable() . ' cff ON cfs.belongs_to = cff.idSon AND cff.lang_code = "' . Lang::get() . '"
		WHERE cfs.idField = "' . $this->id_common . '" AND cfs.lang_code = "' . Lang::get() . '"
		ORDER BY cfs.sequence');


        $base_path = $this->getUrl() . '&amp;id_common='
                . $this->id_common . '&amp;type_field=' . static::getFieldType() . '&amp;back=' . $this->back_coded;

        $out->add('<div class="std_block">'
            . getBackUi($this->back, $std_lang->def('_BACK'))
            . '<div class="title"><b>' . $lang->def('_TITLE') . ' :</b> '
            . '<a class="ico-wt-sprite subs_mod" href="' . $base_path . '&amp;iop=modmain"><span>'
            . $translation . '</span></a>'
            . '</div><br />');

        $tb_son = new Table(0, $lang->def('_DROPDOWN_SON_CAPTION'));
        $content_h = [
            $lang->def('_DROPDOWN_ELEMENT'),
            $lang->def('_SON_AREA'),
            $img_up,
            $img_down,
            '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $std_lang->def('_MOD') . '" />',
            '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $std_lang->def('_DEL') . '" />',
        ];
        $type_h = ['', '','image', 'image', 'image', 'image'];
        $tb_son->addHead($content_h, $type_h);
        while (list($idSon, $elem, $father_elem) = sql_fetch_row($re_field)) {
            ++$counter;

            $content = [];

            $content[] = $elem;
            $content[] = $father_elem;

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
                . '&amp;id_common=' . $this->id_common
                . '&amp;type_field=' . static::getFieldType()
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
}
