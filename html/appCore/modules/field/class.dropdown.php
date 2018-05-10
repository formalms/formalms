<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

/**
 * @package  DoceboCore
 * @version  $Id: class.dropdown.php 987 2007-02-28 17:25:05Z giovanni $
 * @category Field
 * @author   Fabio Pirovano <fabio@docebo.com>
 */

require_once(Forma::inc(_adm_.'/modules/field/class.field.php'));

class Field_Dropdown extends Field {

	var $back;
	var $back_coded;

	/**
	 * this function is useful for field recognize
	 *
	 * @return string	return the identifier of the field
	 *
	 * @access public
	 */
	function getFieldType() {
		return 'dropdown';
	}

	/**
	 * this function create a new field for future use
	 *
	 * @param  string	$back	indicates the return url
	 * @return nothing
	 *
	 * @access public
	 */
	function create($back) {

		$back_coded = htmlentities(urlencode($back));

		$array_lang = array();
		$std_lang 		=& DoceboLanguage::createInstance('standard');
		$lang 			=& DoceboLanguage::createInstance('field');
		$array_lang 	= Docebo::langManager()->getAllLangCode();
		$out 			=& $GLOBALS['page'];

		if(isset($_POST['undo'])) {
			//undo action
			Util::jump_to($back.'&result=undo');
		}
		if(isset($_POST['save_field_'.$this->getFieldType()])) {

			//insert mandatory translation
			$mand_lang = getLanguage();
			$show_on = '';
			if(isset($_POST['show_on_platform'])) {
				while(list($code, ) = each($_POST['show_on_platform']))
					$show_on .= $code.',';
			}
			//control if all is ok
			if(!isset($_POST['new_dropdown'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_dropdown'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_dropdown'][$mand_lang]) == '') {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}

			//insert mandatory field
			if(!sql_query("
			INSERT INTO ".$this->_getMainTable()."
			(type_field, lang_code, translation, show_on_platform, use_multilang) VALUES
			('".$this->getFieldType()."', '".$mand_lang."', '".$_POST['new_dropdown'][$mand_lang]."', '".$show_on."', '".$use_multilang."') ")) {
				Util::jump_to($back.'&result=fail');
			}
			list($id_common) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			if(!sql_query("
			UPDATE ".$this->_getMainTable()."
			SET id_common = '".(int)$id_common."'
			WHERE idField = '".(int)$id_common."'")) {
				Util::jump_to($back.'&result=fail');
			}
			$re = true;
			//insert other field
			foreach($_POST['new_dropdown'] as $lang_code => $translation) {

				if($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
					$re_ins = sql_query("
					INSERT INTO ".$this->_getMainTable()."
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang) VALUES
					('".$this->getFieldType()."', '".(int)$id_common."', '".$lang_code."', '".$translation."', '".$show_on."', '".$use_multilang."') ");
					$re = $re && $re_ins;
				}
			}
			Util::jump_to($back.'&result='.( $re ? 'success' : 'fail'));
		}

		require_once(_base_.'/lib/lib.form.php');

		$form = new Form();

		$out->setWorkingZone('content');
		$out->add('<div class="std_block">');
		$out->add(
			$form->getFormHeader($lang->def('_NEW_DROPDOWN'))
			.$form->openForm('create_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('back', 'back', $back_coded)
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_dropdown_'.$lang_code,
									'new_dropdown['.$lang_code.']',
									255,
									'',
									$lang_code.' '.$lang->def('_FIELD_NAME') )
			);
		}

		$GLOBALS['page']->add($this->getMultiLangCheck(), 'content');
		$GLOBALS['page']->add($this->getShowOnPlatformFieldset(), 'content');

		$out->add(
			$form->closeElementSpace()
			.$form->openButtonSpace()
			.$form->getButton('save_field', 'save_field_'.$this->getFieldType(), $std_lang->def('_CREATE', 'standard'))
			.$form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
			.$form->closeButtonSpace()
			.$form->closeForm()
		);
		$out->add('</div>');
	}

	/**
	 * this function manage a field
	 *
	 * @param  string	$back	indicates the return url
	 * @return nothing
	 *
	 * @access public
	 */
	function edit($back) {

		$this->back_coded = htmlentities(urlencode($back));
		$this->back = $back;
		$internal_op = importVar('iop');

		switch($internal_op) {
			case "add" : $this->_add_son();break;
			case "mod" : $this->_mod_son();break;
			case "del" : $this->_del_son();break;

			case "modmain" : $this->_edit_field();break;
			default : $this->_show_son();
		}
	}

	/**
	 * this function completely remove a field
	 *
	 * @param  string	$back	indicates the return url
	 * @return nothing
	 *
	 * @access public
	 */
	function del($back) {

		$query_del = "
		DELETE FROM ".$this->_getUserEntryTable()."
		WHERE id_common = '".(int)$this->id_common."'";
		$re = sql_query($query_del);
		if(!$re) Util::jump_to($back.'&result=fail');

		$query_del = "
		DELETE FROM ".$this->_getElementTable()."
		WHERE idField  = '".(int)$this->id_common."'";
		$re = sql_query($query_del);
		if(!$re) Util::jump_to($back.'&result=fail');

		$query_del = "
		DELETE FROM ".$this->_getMainTable()."
		WHERE id_common = '".(int)$this->id_common."'";
		$re = sql_query($query_del);

		Util::jump_to($back.'&result='.( $re ? 'success' : 'fail'));
	}

	/**
	 * display the entry of this field for the passed user
	 *
	 * @param 	int		$id_user 			if alredy exists a enty for the user load it
	 *
	 * @return string 	of field xhtml code
	 *
	 * @access public
	 */
	function show( $id_user ) {

		list($user_entry) = sql_fetch_row(sql_query("
		SELECT user_entry
		FROM ".$this->_getUserEntryTable()."
		WHERE id_user = '".(int)$id_user."' AND
			id_common = '".(int)$this->id_common."' AND
			id_common_son = '0'"));

		$user_entry = (int)$user_entry;

		$re_field_element = sql_query("
		SELECT id_common_son, translation
		FROM ".$this->_getElementTable()."
		WHERE idField = '".(int)$this->id_common."' AND lang_code = '".getLanguage()."'
		ORDER BY sequence");
		$option = array();
		$option[0] = '';
		while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {
			$option[$id_common_son] = $element;
		}
		$user_entry = (int)$user_entry;
		return isset($option[$user_entry]) ? $option[$user_entry] : "";
	}

	function getTranslation() {

		$re_field = sql_query("
		SELECT translation
		FROM ".$this->_getMainTable()."
		WHERE id_common = '".(int)$this->id_common."' AND
			type_field = '".$this->getFieldType()."' AND
			lang_code = '".getLanguage()."'");
		list($translation) = sql_fetch_row($re_field);

		return $translation;
	}

	/**
	 * display the field for interaction
	 *
	 * @param 	int		$id_user			if alredy exists a entry for the user load as default value
	 * @param 	bool	$freeze				if true, disable the user interaction
	 * @param 	bool	$mandatory			if true, the field is considered mandatory
	 * @param 	bool	$do_not_show_label	if true, do not show the label in freeze mode
	 *
	 * @return string 	of field xhtml code
	 *
	 * @access public
	 */
	function play( $id_user, $freeze, $mandatory = false, $do_not_show_label = false, $value = NULL, $registrationLayout =false, $registrationErrors = false ) {
		require_once(_base_.'/lib/lib.form.php');

		if( 	isset( $_POST['field_'.$this->getFieldType()] )
			&& 	isset( $_POST['field_'.$this->getFieldType()][$this->id_common] ) ) {
			$user_entry = $_POST['field_'.$this->getFieldType()][$this->id_common];
		} else {
			list($user_entry) = sql_fetch_row(sql_query("
			SELECT user_entry
			FROM ".$this->_getUserEntryTable()."
			WHERE id_user = '".(int)$id_user."' AND
				id_common = '".(int)$this->id_common."' AND
				id_common_son = '0'"));
		}
		$user_entry = (int)$user_entry;

		$re_field = sql_query("
		SELECT translation
		FROM ".$this->_getMainTable()."
		WHERE id_common = '".(int)$this->id_common."' AND
			type_field = '".$this->getFieldType()."' AND
			lang_code = '".getLanguage()."'");
		list($translation) = sql_fetch_row($re_field);

		$re_field_element = sql_query("
		SELECT id_common_son, translation
		FROM ".$this->_getElementTable()."
		WHERE idField = '".(int)$this->id_common."' AND lang_code = '".getLanguage()."'
		ORDER BY sequence");
		$option = array();
		//$option[0] = Lang::t('_DROPDOWN_NOVALUE', 'field', 'framework');
        $option[] =  $translation.($mandatory ? ' *' : '');
		while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {
			$option[$id_common_son] = $element;
		}

		if ($value !== NULL) $user_entry = (int)$value;

        if ($registrationLayout) {

            $error = (isset($registrationErrors) && $registrationErrors[$this->id_common]);
            $errorMessage = $registrationErrors[$this->id_common]['msg'];

            $formField = '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

            $formField .= '<div class="col-xs-12 col-sm-6">';

            $formField .= Form::getInputDropdown(
                    'form-control '.($error ? 'has-error' : ''),
                    'field_' . $this->getFieldType() . '_' . $this->id_common,
                    'field_' . $this->getFieldType() . '[' . $this->id_common . ']',
                    $option,
                    (int)$user_entry,
                    '',
                    true);

            if ($error) {
                $formField .= '<small class="form-text">* ' . $errorMessage . '</small>';
            }

            $formField .= '</div>';
            $formField .= '</div>';

            return $formField;
        }

		if($freeze) return Form::getLineBox($translation.' : ', $option[$user_entry]);

		return Form::getDropdown($translation.( $mandatory ? ' <span class="mandatory">*</span>' : '' ),
								'field_'.$this->getFieldType().'_'.$this->id_common,
								'field_'.$this->getFieldType().'['.$this->id_common.']',
								$option,
								(int)$user_entry,
								'',
								'');
	}

	/**
	 * display the field for filters
	 *
	 * @param	string	$field_id		the id of the field used for id/name
	 * @param 	mixed 	$value 			(optional) the value to put in the field
	 *										retrieved from $_POST if not given
	 * @param	string	$label			(optional) the label to use if not given the
	 *									value will be retrieved from custom field
	 *									$id_field
	 * @param	string	$field_prefix 	(optional) the prefix to give to
	 *									the field id/name
	 * @param 	string 	$other_after 	optional html code added after the input element
	 * @param	string 	$other_before 	optional html code added before the label element
	 * @param   mixed 	$field_special	(optional) if is an array the elements are
	 *									the options of dropdown, if is numeric is trated
	 *									as a field id and used to retrieve options
	 *									if not given the elements will be retrieved from
	 *									custom field $id_field
	 *
	 * @return string 	of field xhtml code
	 *
	 * @access public
	 */
	function play_filter( $id_field, $value = FALSE, $label = FALSE, $field_prefix = FALSE, $other_after = '', $other_before = '', $field_special = FALSE ) {
		require_once(_base_.'/lib/lib.form.php');

		if( $value === FALSE ) {
			$value = Field::getFieldValue_Filter( $_POST, $id_field, $field_prefix, '0' );
		}

		$option = array();
		$option[0] = Lang::t('_DROPDOWN_NOVALUE', 'field');
		if( is_array( $field_special ) ) {
			foreach( $field_special as $key_opt => $label_opt ) {
				$option[$key_opt] = $label_opt;
			}
		} else {

			$re_field_element = sql_query("
			SELECT id_common_son, translation
			FROM ".Field_Dropdown::_getElementTable()."
			WHERE idField = '".(int)(($field_special !== FALSE)?$field_special:$id_field)."'
				AND lang_code = '".getLanguage()."'
			ORDER BY sequence");
			while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {
				$option[$id_common_son] = $element;
			}
		}

		if( $label === FALSE ) {
			$re_field = sql_query("
			SELECT translation
			FROM ".Field::_getMainTable()."
			WHERE id_common = '".(int)$id_field."'
				AND type_field = '".Field_Dropdown::getFieldType()."'");
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
	 * check if the user as selected a valid value for the field
	 *
	 * @return 	bool 	true if operation success false otherwise
	 *
	 * @access public
	 */
	function isFilled( $id_user ) {

		if(!isset($_POST['field_'.$this->getFieldType()][$this->id_common])) return false;
		elseif($_POST['field_'.$this->getFieldType()][$this->id_common] == '0') return false;
		else return true;
	}

	/**
	 * return the filled value of the selected field
	 *
	 * @param 	mixed 	$grab_from 			(optional) the array to retrieve the value from
	 *	($_POST will be used as default)
	 * @param bool $dropdown_val (optional). If true will get the value of a dropdown item instead of its id.
	 *
	 * @return 	bool 	true if operation success false otherwise
	 *
	 * @access public
	 */
	function getFilledVal($grab_from=FALSE, $dropdown_val=FALSE) {

		if ($grab_from === FALSE)
			$grab_from=$_POST;

		if ((!$dropdown_val) && (isset($grab_from['field_'.$this->getFieldType()][$this->id_common])))
			return $grab_from['field_'.$this->getFieldType()][$this->id_common];
		else if (($dropdown_val) && (isset($grab_from['field_'.$this->getFieldType()][$this->id_common]))) {

			$re_field = sql_query("
			SELECT translation
			FROM ".$this->_getElementTable()."
			WHERE idField = '".$this->id_common."' AND lang_code = '".getLanguage()."'
				AND id_common_son='".$grab_from['field_'.$this->getFieldType()][$this->id_common]."'");
			list($translation) = sql_fetch_row($re_field);

			return $translation;
		}
		else
			return NULL;
	}

	/**
	 * store the value inserted by a user into the database, if a entry exists it will be overwrite
	 *
	 * @param	int		$id_user 		the user
	 * @param	int		$no_overwrite 	if a entry exists do not overwrite it
	 *
	 * @return 	bool 	true if operation success false otherwise
	 *
	 * @access public
	 */
	function store( $id_user, $no_overwrite, $int_userid=TRUE ) {

		if (($int_userid) || (empty($id_user)))
			$id_user=(int)$id_user;

		if(!isset($_POST['field_'.$this->getFieldType()][$this->id_common])) return true;
		$re_entry = sql_query("
		SELECT user_entry
		FROM ".$this->_getUserEntryTable()."
		WHERE id_user = '".$id_user."' AND
			id_common = '".(int)$this->id_common."' AND
			id_common_son = '0'");
		$some_entry = sql_num_rows($re_entry);
		if($some_entry) {
			if($no_overwrite) return true;
			if(!sql_query("
			UPDATE ".$this->_getUserEntryTable()."
			SET user_entry = '".$_POST['field_'.$this->getFieldType()][$this->id_common]."'
			WHERE id_user = '".$id_user."' AND
			id_common = '".(int)$this->id_common."' AND
			id_common_son = '0'")) return false;
		} else {

			if(!sql_query("
			INSERT INTO ".$this->_getUserEntryTable()."
			( id_user, id_common, id_common_son, user_entry ) VALUES
			(	'".$id_user."',
				'".(int)$this->id_common."',
				'0',
				'".$_POST['field_'.$this->getFieldType()][$this->id_common]."')")) return false;
		}

		return true;
	}


	/**
	 * store the value passed into the database, if a entry exists it will be overwrite
	 *
	 * @param	int		$id_user 		the user
	 * @param	int		$value 			the value of the field
	 * @param	bool	$is_id 			if false the param must be reconverted
	 * @param	int		$no_overwrite 	if a entry exists do not overwrite it
	 *
	 * @return 	bool 	true if success false otherwise
	 *
	 * @access public
	 */
	function storeDirect( $id_user, $value, $is_id, $no_overwrite, $int_userid=TRUE ) {
		$value = stripslashes(strtolower($value));
		if (($int_userid) || (empty($id_user)))
			$id_user=(int)$id_user;

		$re_entry = sql_query("
		SELECT user_entry
		FROM ".$this->_getUserEntryTable()."
		WHERE id_user = '".$id_user."' AND
			id_common = '".(int)$this->id_common."' AND
			id_common_son = '0'");
		$some_entry = sql_num_rows($re_entry);
		if($some_entry && $no_overwrite) return true;

		$id_value = 0;
		if($is_id === false) {

			if(isset($GLOBALS['temp']['dropdown_value_'.$this->id_common])) {

				// alredy read form database, search in the array
				$index = array_search($value, $GLOBALS['temp']['dropdown_value_'.$this->id_common]);
				if($index === false || $index === NULL) $id_value = 0;
				else {
					$id_value = end(explode('_', $index));
				}
			} else {

				// first time, recover data from database
				$query_value = "
				SELECT idSon, id_common_son, lang_code, translation
				FROM ".$this->_getElementTable()."
				WHERE idField = '".$this->id_common."'
					 AND lang_code = '".getLanguage()."'";
				$re_values = sql_query($query_value);
				while(list($id_son, $id_common_son, $lang_code, $value_com) = sql_fetch_row($re_values)) {
					
					$value_com = strtolower($value_com);
					$GLOBALS['temp']['dropdown_value_'.$this->id_common][$lang_code.'_'.$id_common_son] = $value_com;
					if($value_com == $value) $id_value = $id_common_son;
				}
			}
		} else {
			// tha value is the id

			$id_value = $value;
		}

		if($some_entry) {
			if(!sql_query("
			UPDATE ".$this->_getUserEntryTable()."
			SET user_entry = '".$id_value."'
			WHERE id_user = '".$id_user."' AND
				id_common = '".(int)$this->id_common."' AND
				id_common_son = '0'")) return false;
		} else {

			if(!sql_query("
			INSERT INTO ".$this->_getUserEntryTable()."
			( id_user, id_common, id_common_son, user_entry ) VALUES
			(	'".$id_user."',
				'".(int)$this->id_common."',
				'0',
				'".$id_value."')")) return false;
		}
		return true;
	}


	function storeDirectMultiple( $idst_users, $value, $is_id, $no_overwrite, $int_userid=TRUE ) {
		if (is_numeric($idst_users)) $idst_users = array($idst_users);
		if (!is_array($idst_users)) return false;
		if (empty($idst_users)) return true;

		$arr_existent = array();
		$arr_new = $idst_users;

		$query = "SELECT id_user, user_entry FROM ".$this->_getUserEntryTable()." "
			." WHERE id_user IN (".implode(",", $idst_users).") "
			." AND id_common = '".(int)$this->id_common."' AND id_common_son = '0'";
		$res = sql_query($query);
		if ($res) {
			if (sql_num_rows($res) > 0) {
				while (list($idst, $entry) = sql_fetch_row($res)) {
					$arr_existent[] = $idst;
					unset($arr_new[array_search($idst, $arr_new)]);
				}
			}


			if (!empty($arr_existent) && !$no_overwrite) {
				if($no_overwrite) return true;
				$query = "UPDATE ".$this->_getUserEntryTable()." SET user_entry = '".$value."' "
					." WHERE id_user IN (".implode(",", $arr_existent).") "
					." AND id_common = '".(int)$this->id_common."' AND id_common_son = '0'";
				$res1 = sql_query($query);
			}

			if (!empty($arr_new)) {
				$insert_values = array();
				foreach ($arr_new as $idst) {
					$insert_values[] = "(	'".(int)$idst."', '".(int)$this->id_common."', '0', '".$value."')";
				}
				$query = "INSERT INTO ".$this->_getUserEntryTable()." "
					."( id_user, id_common, id_common_son, user_entry ) VALUES "
					.implode(",", $insert_values);
				$res2 = sql_query($query);
			}
		}

		return true;
	}




	// NOTE: special function ---------------------------------------
	
	function _move_up($id_son)
	{
		$query =	"SELECT sequence, id_common_son"
					." FROM ".$this->_getElementTable().""
					." WHERE idField = '".$this->id_common."'"
					." AND lang_code = '".getLanguage()."'"
					." AND idSon = '".$id_son."'";
		
		list($sequence, $id_common_son) = sql_fetch_row(sql_query($query));
		
		$up_sequence = $sequence - 1;
		
		$query =	"UPDATE ".$this->_getElementTable().""
					." SET sequence = '".$sequence."'"
					." WHERE idField = '".$this->id_common."'"
					." AND sequence = '".$up_sequence."'";
		
		$result = sql_query($query);
		
		$query =	"UPDATE ".$this->_getElementTable().""
					." SET sequence = '".$up_sequence."'"
					." WHERE idField = '".$this->id_common."'"
					." AND id_common_son = '".$id_common_son."'";
		
		$result = sql_query($query);
	}
	
	function _move_down($id_son)
	{
		$query =	"SELECT sequence, id_common_son"
					." FROM ".$this->_getElementTable().""
					." WHERE idField = '".$this->id_common."'"
					." AND lang_code = '".getLanguage()."'"
					." AND idSon = '".$id_son."'";
		
		list($sequence, $id_common_son) = sql_fetch_row(sql_query($query));
		
		$up_sequence = $sequence + 1;
		
		$query =	"UPDATE ".$this->_getElementTable().""
					." SET sequence = '".$sequence."'"
					." WHERE idField = '".$this->id_common."'"
					." AND sequence = '".$up_sequence."'";
		
		$result = sql_query($query);
		
		$query =	"UPDATE ".$this->_getElementTable().""
					." SET sequence = '".$up_sequence."'"
					." WHERE idField = '".$this->id_common."'"
					." AND id_common_son = '".$id_common_son."'";
		
		$result = sql_query($query);
	}
	
	function _fix_sequence()
	{
		$new_sequence = 1;
		
		$query =	"SELECT id_common_son"
					." FROM ".$this->_getElementTable().""
					." WHERE idField = '".$this->id_common."'"
					." AND lang_code = '".getLanguage()."'";
		
		$result = sql_query($query);
		
		while (list($id_common) = sql_fetch_row($result))
		{
			$query =	"UPDATE ".$this->_getElementTable().""
						." SET sequence = '".$new_sequence."'"
						." WHERE idField = '".$this->id_common."'"
						." AND id_common_son = '".$id_common."'";
			
			sql_query($query);
			
			$new_sequence++;
		}
	}
	
	function _show_son() {

		$std_lang 		=& DoceboLanguage::createInstance('standard');
		$lang 			=& DoceboLanguage::createInstance('field');
		$out 			=& $GLOBALS['page'];

		$out->setWorkingZone('content');

		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_.'/lib/lib.form.php');
		
		$counter = 0;
		$img_up = '<img class="valing-middle" src="'.getPathImage().'standard/up.png" alt="'.$std_lang->def('_MOVE_UP').'" />';
		$img_down = '<img class="valing-middle" src="'.getPathImage().'standard/down.png" alt="'.$std_lang->def('_MOVE_DOWN').'" />';
		
		$id_son = Get::req('idSon', DOTY_INT, 0);
		$iop = Get::req('iop', DOTY_STRING, '');
		
		if($iop == 'moveup')
			$this->_move_up($id_son);
		elseif($iop == 'movedown')
			$this->_move_down($id_son);
		elseif($iop == 'fixsequence')
			$this->_fix_sequence();
		
		list($total_son) = sql_fetch_row(sql_query("
		SELECT COUNT(*)
		FROM ".$this->_getElementTable()."
		WHERE idField = '".$this->id_common."' AND lang_code = '".getLanguage()."'"));
		
		$re_main = sql_query("
		SELECT translation
		FROM ".$this->_getMainTable()."
		WHERE id_common = '".$this->id_common."' AND lang_code = '".getLanguage()."'
		ORDER BY sequence");
		list($translation) = sql_fetch_row($re_main);

		//find available son
		$re_field = sql_query("
		SELECT id_common_son, translation
		FROM ".$this->_getElementTable()."
		WHERE idField = '".$this->id_common."' AND lang_code = '".getLanguage()."'
		ORDER BY sequence");

		$base_path = $this->getUrl().'&amp;id_common='
				.$this->id_common.'&amp;type_field='.$this->getFieldType().'&amp;back='.$this->back_coded;

		$out->add('<div class="std_block">'
			.getBackUi($this->back, $std_lang->def('_BACK'))
			.'<div class="title"><b>'.$lang->def('_TITLE').' :</b> '
			.'<a class="ico-wt-sprite subs_mod" href="'.$base_path.'&amp;iop=modmain"><span>'
			.$translation.'</span></a>'
			.'</div><br />');

		$tb_son = new Table(0, $lang->def('_DROPDOWN_SON_CAPTION'));
		$content_h 	= array(
			$lang->def('_DROPDOWN_ELEMENT'),
			$img_up,
			$img_down,
			'<img src="'.getPathImage().'standard/edit.png" alt="'.$std_lang->def('_MOD').'" />',
			'<img src="'.getPathImage().'standard/delete.png" alt="'.$std_lang->def('_DEL').'" />'
		);
		$type_h = array('','image','image','image','image');
		$tb_son->addHead($content_h, $type_h);
		while(list($idSon, $elem) = sql_fetch_row($re_field)) {
			$counter++;
			
			$content = array();
			
			$content[] = $elem;
			
			if($counter != 1 && $counter != $total_son)
			{
				$content[] = '<a href="'.$base_path.'&amp;iop=moveup&amp;idSon='.$idSon.'" title="'.$std_lang->def('_MOVE_UP').'">'.$img_up.'</a>';
				$content[] = '<a href="'.$base_path.'&amp;iop=movedown&amp;idSon='.$idSon.'" title="'.$std_lang->def('_MOVE_DOWN').'">'.$img_down.'</a>';
			}
			elseif($counter == 1)
			{
				$content[] = '';
				$content[] = '<a href="'.$base_path.'&amp;iop=movedown&amp;idSon='.$idSon.'" title="'.$std_lang->def('_MOVE_DOWN').'">'.$img_down.'</a>';
			}
			else
			{
				$content[] = '<a href="'.$base_path.'&amp;iop=moveup&amp;idSon='.$idSon.'" title="'.$std_lang->def('_MOVE_UP').'">'.$img_up.'</a>';
				$content[] = '';
			}
			
			$content[] = '<a href="'.$base_path.'&amp;iop=mod&amp;idSon='.$idSon.'" title="'.$std_lang->def('_MOD').' : '.$elem.'">'
					.'<img src="'.getPathImage().'standard/edit.png" alt="'.$std_lang->def('_MOD').' : '.$elem.'" /></a>'; 
			
			$content[] = '<a href="'.$base_path.'&amp;iop=del&amp;idSon='.$idSon.'" title="'.$std_lang->def('_DEL').' : '.$elem.'">'
					.'<img src="'.getPathImage().'standard/delete.png" alt="'.$std_lang->def('_DEL').' : '.$elem.'" /></a>'; 
			
			$tb_son->addBody($content);
		}
		$tb_son->addActionAdd(
			'<a class="ico-wt-sprite subs_add" href="'.$this->getUrl()
				.'&amp;id_common='.$this->id_common
				.'&amp;type_field='.$this->getFieldType()
				.'&amp;back='.$this->back_coded
				.'&amp;iop=add"><span>'
			.$lang->def('_DROPDOWN_SON_ADD').'</span></a>'
		);
		$out->add($tb_son->getTable());
		$out->add('<a href="'.$base_path.'&amp;iop=fixsequence"'
				.' title="'.$lang->def('_FIX_SEQUENCE').'">'.$lang->def('_FIX_SEQUENCE').'</a>');
		$out->add(getBackUi($this->back, $std_lang->def('_BACK'))
			.'</div>');
	}

	function _edit_field() {

		$array_lang = array();
		$std_lang 		=& DoceboLanguage::createInstance('standard');
		$lang 			=& DoceboLanguage::createInstance('field');
		$array_lang 	= Docebo::langManager()->getAllLangCode();
		$out 			=& $GLOBALS['page'];

		if(isset($_POST['undo'])) {
			//undo action
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded);
		}
		if(isset($_POST['save_field_'.$this->getFieldType()])) {

			//insert mandatory translation
			$mand_lang = getLanguage();
			$show_on = '';
			if(isset($_POST['show_on_platform'])) {
				while(list($code, ) = each($_POST['show_on_platform']))
					$show_on .= $code.',';
			}
			//control if all is ok
			if(!isset($_POST['new_textfield'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$this->back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_textfield'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_textfield'][$mand_lang]) == '') {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$this->back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}

			$existsing_translation = array();
			$re_trans = sql_query("
			SELECT lang_code
			FROM ".$this->_getMainTable()."
			WHERE id_common = '".$this->id_common."'");
			while(list($l_code) = sql_fetch_row($re_trans)) {
				$existsing_translation[$l_code] = 1;
			}

			$use_multilang =(isset($_POST['use_multi_lang']) ? 1 : 0);

			$re = true;
			//insert other field
			foreach($_POST['new_textfield'] as $lang_code => $translation) {

				if(isset($existsing_translation[$lang_code])) {

					if(!sql_query("
					UPDATE ".$this->_getMainTable()."
					SET translation = '".$translation."',
						show_on_platform = '".$show_on."',
						use_multilang = '".$use_multilang."'
					WHERE id_common = '".(int)$this->id_common."' AND lang_code = '".$lang_code."'")) $re = false;
				} else {

					if(!sql_query("
					INSERT INTO ".$this->_getMainTable()."
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang) VALUES
					('".$this->getFieldType()."', '".(int)$this->id_common."', '".$lang_code."', '".$translation."', '".$show_on."', '".$use_multilang."') ")) $re= false;
				}
			}
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded
				.'&result='.( $re ? 'success' : 'fail'));
		}

		//load value form database
		$re_trans = sql_query("
		SELECT lang_code, translation, show_on_platform, use_multilang
		FROM ".$this->_getMainTable()."
		WHERE id_common = '".$this->id_common."'");
		while(list($l_code, $trans, $show_on, $db_use_multilang) = sql_fetch_row($re_trans)) {
			$translation[$l_code] = $trans;
			if(!isset($show_on_platform)) $show_on_platform = array_flip(explode(',', $show_on));
			if(!isset($use_multilang)) $use_multilang = $db_use_multilang;
		}

		require_once(_base_.'/lib/lib.form.php');

		$form = new Form();

		$out->setWorkingZone('content');
		$out->add('<div class="std_block">');
		$out->add(
			$form->openForm('create_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('id_common', 'id_common', $this->id_common)
			.$form->getHidden('back', 'back', $this->back_coded)
			.$form->getHidden('iop', 'iop', 'modmain')
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_textfield_'.$lang_code,
									'new_textfield['.$lang_code.']',
									255,
									( isset($translation[$lang_code]) ? $translation[$lang_code] : '' ),
									$lang_code.' '.$lang->def('_FIELD_NAME') )
			);
		}

		$GLOBALS['page']->add($this->getMultiLangCheck($use_multilang), 'content');
		$GLOBALS['page']->add($this->getShowOnPlatformFieldset($show_on_platform), 'content');

		$out->add(
			$form->closeElementSpace()
			.$form->openButtonSpace()
			.$form->getButton('save_field', 'save_field_'.$this->getFieldType(), $std_lang->def('_SAVE', 'standard'))
			.$form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
			.$form->closeButtonSpace()
			.$form->closeForm()
		);
		$out->add('</div>');
	}

	function _add_son() {

		$array_lang = array();
		$std_lang 		=& DoceboLanguage::createInstance('standard');
		$lang 			=& DoceboLanguage::createInstance('field');
		$array_lang 	= Docebo::langManager()->getAllLangCode();
		$out 			=& $GLOBALS['page'];

		if(isset($_POST['undo'])) {
			//undo action
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded);
		}
		if(isset($_POST['save_field_'.$this->getFieldType()])) {

			//insert mandatory translation
			$mand_lang = getLanguage();

			//control if all is ok
			if(!isset($_POST['new_dropdown_son'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;id_common='
						.$this->id_common.'&amp;type_field='.$this->getFieldType().'&amp;back='.$this->back_coded,
						$std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_dropdown_son'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_dropdown_son'][$mand_lang]) == '') {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;id_common='
						.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded,
						$std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			
			list($sequence) = sql_fetch_row(sql_query("
			SELECT COUNT(*)
			FROM ".$this->_getElementTable()."
			WHERE idField = '".$this->id_common."' AND lang_code = '".getLanguage()."'"));
			
			$sequence++;
			
			//insert mandatory field
			if(!sql_query("
			INSERT INTO ".$this->_getElementTable()."
			(idField, lang_code, translation, sequence) VALUES
			('".$this->id_common."', '".$mand_lang."', '".$_POST['new_dropdown_son'][$mand_lang]."', '".$sequence."') ")) {
				Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded
				.'&result=fail');
			}
			list($id_common_son) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			if(!sql_query("
			UPDATE ".$this->_getElementTable()."
			SET id_common_son = '".(int)$id_common_son."'
			WHERE idSon = '".(int)$id_common_son."'")) {

				Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded
				.'&result=fail');
			}
			$re = true;
			//insert other field
			foreach($_POST['new_dropdown_son'] as $lang_code => $translation) {

				if($mand_lang != $lang_code && $translation != $lang->def('_FIELD_NAME') && trim($translation) != '') {
					$re_ins = sql_query("
					INSERT INTO ".$this->_getElementTable()."
					(idField, id_common_son, lang_code, translation, sequence) VALUES
					('".$this->id_common."', '".(int)$id_common_son."', '".$lang_code."', '".$translation."', '".$sequence."') ");
					$re = $re && $re_ins;
				}
			}
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded
				.'&result='.( $re ? 'success' : 'fail'));
		}

		require_once(_base_.'/lib/lib.form.php');

		$form = new Form();

		$out->setWorkingZone('content');
		$out->add('<div class="std_block">');
		$out->add(
			$form->getFormHeader($lang->def('_DROPDOWN_SON_NEW'))
			.$form->openForm('create_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('id_common', 'id_common', $this->id_common)
			.$form->getHidden('back', 'back', $this->back_coded)
			.$form->getHidden('iop', 'iop', 'add')
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_dropdown_son_'.$lang_code,
									'new_dropdown_son['.$lang_code.']',
									255,
									'',
									$lang_code.' '.$lang->def('_FIELD_NAME') )
			);
		}
		$out->add(
			$form->closeElementSpace()
			.$form->openButtonSpace()
			.$form->getButton('save_field', 'save_field_'.$this->getFieldType(), $std_lang->def('_CREATE', 'standard'))
			.$form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
			.$form->closeButtonSpace()
			.$form->closeForm()
		);
		$out->add('</div>');
	}

	function _mod_son() {
		$idSon			= importVar('idSon', true, 0);
		$std_lang 		=& DoceboLanguage::createInstance('standard');
		$lang 			=& DoceboLanguage::createInstance('field');
		$out 			=& $GLOBALS['page'];
		$array_lang 	= array();
		$array_lang 	= Docebo::langManager()->getAllLangCode();

		if(isset($_POST['undo'])) {
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded);
		}
		if(isset($_POST['save_field_'.$this->getFieldType()])) {

			//insert mandatory translation
			$mand_lang = getLanguage();

			//control if all is ok
			if(!isset($_POST['mod_dropdown_son'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$this->back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['mod_dropdown_son'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['mod_dropdown_son'][$mand_lang]) == '') {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$this->back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}

			$existsing_translation = array();
			$re_trans = sql_query("
			SELECT lang_code
			FROM ".$this->_getElementTable()."
			WHERE id_common_son = '".(int)$idSon."' AND
				idField = '".(int)$this->id_common."'");
			while(list($l_code) = sql_fetch_row($re_trans)) {
				$existsing_translation[$l_code] = 1;
			}

			$re = true;
			//insert other field
			foreach($_POST['mod_dropdown_son'] as $lang_code => $translation) {

				if(isset($existsing_translation[$lang_code])) {

					if(!sql_query("
					UPDATE ".$this->_getElementTable()."
					SET translation = '".$translation."'
					WHERE id_common_son = '".(int)$idSon."' AND
						idField = '".(int)$this->id_common."' AND
						lang_code = '".$lang_code."'")) $re = false;
				} else {

					if(!sql_query("
					INSERT INTO ".$this->_getElementTable()."
					(idField, id_common_son, lang_code, translation) VALUES
					('".(int)$this->id_common."', '".(int)$idSon."', '".$lang_code."', '".$translation."') ")) $re = false;
				}
			}
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded
				.'&result='.( $re ? 'success' : 'fail'));
		}

		//load value form database
		$re_trans = sql_query("
		SELECT lang_code, translation
		FROM ".$this->_getElementTable()."
		WHERE id_common_son = '".$idSon."' AND idField = '".(int)$this->id_common."'");
		while(list($l_code, $trans) = sql_fetch_row($re_trans)) {
			$translation[$l_code] = $trans;
		}

		require_once(_base_.'/lib/lib.form.php');

		$form = new Form();

		$out->setWorkingZone('content');
		$out->add('<div class="std_block">');
		$out->add(
			$form->openForm('del_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('id_common', 'id_common', $this->id_common)
			.$form->getHidden('idSon', 'idSon', $idSon)
			.$form->getHidden('back', 'back', $this->back_coded)
			.$form->getHidden('iop', 'iop', 'mod')
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'mod_dropdown_son_'.$lang_code,
									'mod_dropdown_son['.$lang_code.']',
									255,
									( isset($translation[$lang_code]) ? $translation[$lang_code] : '' ),
									$lang_code.' '.$lang->def('_FIELD_NAME') )
			);
		}
		$out->add(
			$form->closeElementSpace()
			.$form->openButtonSpace()
			.$form->getButton('save_field', 'save_field_'.$this->getFieldType(), $std_lang->def('_SAVE', 'standard'))
			.$form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'))
			.$form->closeButtonSpace()
			.$form->closeForm()
		);
		$out->add('</div>');
	}

	function _del_son() {

		$idSon			= importVar('idSon');
		$std_lang 		=& DoceboLanguage::createInstance('standard');
		$lang 			=& DoceboLanguage::createInstance('field');
		$out 			=& $GLOBALS['page'];

		require_once(_base_.'/lib/lib.form.php');

		$form = new Form();

		if(isset($_POST['undo'])) {
			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded);
		}
		if(isset($_POST['confirm'])) {

			$query_del = "
			DELETE FROM ".$this->_getUserEntryTable()."
			WHERE user_entry = '".$idSon."'
			AND id_common = ".(int)$this->id_common;
			$re = sql_query($query_del);
			if(!$re) Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded.'&amp;result=fail');

			$query_del = "
			DELETE FROM ".$this->_getElementTable()."
			WHERE idField = '".(int)$this->id_common."' AND
				id_common_son = '".(int)$idSon."'";
			$re = sql_query($query_del);

			Util::jump_to($this->getUrl().'&id_common='
				.$this->id_common.'&type_field='.$this->getFieldType().'&back='.$this->back_coded
				.'&amp;result='.( $re ? 'success' : 'fail' ));
		}

		$re_main = sql_query("
		SELECT translation
		FROM ".$this->_getElementTable()."
		WHERE id_common_son = '".$idSon."' AND lang_code = '".getLanguage()."'
		ORDER BY sequence");
		list($translation) = sql_fetch_row($re_main);

		$out->setWorkingZone('content');
		$out->add('<div class="std_block">');
		$out->add(
			$form->openForm('del_'.$this->getFieldType(), $this->getUrl())
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('id_common', 'id_common', $this->id_common)
			.$form->getHidden('idSon', 'idSon', $idSon)
			.$form->getHidden('back', 'back', $this->back_coded)
			.$form->getHidden('iop', 'iop', 'del')
			.'<div class="boxinfo_title">'
				.$lang->def('_AREYOUSURE')
			.'</div>'

			.'<div class="boxinfo_container">'
				.$lang->def('_DROPDOWN_ELEMENT').' : '.$translation
			.'</div>'

			.'<div class="del_container">'
			.$form->getButton('confirm', 'confirm', $std_lang->def('_CONFIRM', 'standard'), 'transparent_del_button').'&nbsp;'
			.$form->getButton('undo', 'undo', $std_lang->def('_UNDO', 'standard'), 'transparent_undo_button')
			.'</div>'

			.$form->closeForm()
		);
		$out->add('</div>');
	}

	function getAllSon($language = false) {

		$lang =& DoceboLanguage::createInstance('field');
		if (!$language) $language = getLanguage();

		if (!isset($GLOBALS['temp']['dropdown_cache_'.$this->id_common][$language])) {
			$GLOBALS['temp']['dropdown_cache_'.$this->id_common][$language] = array();
			$re_field = sql_query("
				SELECT idSon, translation
				FROM ".$this->_getElementTable()."
				WHERE idField = '".$this->id_common."' AND lang_code = '".$language."'
				ORDER BY sequence");
				if(!$re_field) return $sons;
				while(list($id_son, $elem) = sql_fetch_row($re_field)) {
					$GLOBALS['temp']['dropdown_cache_'.$this->id_common][$language][$id_son] = $elem;
				}
		}
		return $GLOBALS['temp']['dropdown_cache_'.$this->id_common][$language];
		/*
		$sons = array();
		//find available son
		$re_field = sql_query("
		SELECT idSon, translation
		FROM ".$this->_getElementTable()."
		WHERE idField = '".$this->id_common."' AND lang_code = '".getLanguage()."'
		ORDER BY sequence");
		if(!$re_field) return $sons;
		while(list($id_son, $elem) = sql_fetch_row($re_field)) {

			$sons[$id_son] = $elem;
		}
		return $sons;
		*/
	}
	
	
	function getFlatAllSon() {
		$lang =& DoceboLanguage::createInstance('field');

		$sons = array();
		//find available son
		$re_field = sql_query("
		SELECT idSon, idField, id_common_son, translation
		FROM ".$this->_getElementTable()."
		WHERE lang_code = '".getLanguage()."'
		ORDER BY sequence");
		if(!$re_field) return $sons;
		while(list($id_son, $id_field, $id_cson, $elem) = sql_fetch_row($re_field)) {
			$sons[$id_field][$id_son] = $elem;
		}
		return $sons;
	}
	
	function getClientClassObject() {
    $sons = $this->getFlatAllSon();
    $temp=array();
    foreach ($sons as $key=>$val) {
      $temp2 = array();
      foreach ($val as $k2=>$v2) {
        $temp2[] = '{value: "'.$k2.'", text: "'.$v2.'"}';
      }
      $temp[] = 'field_'.$key.': ['.implode(",", $temp2).']';
    }
    $js_sons = '{'.implode(",", $temp).'}';
		return 'YAHOO.dynamicFilter.renderTypes.get("'.$this->getFieldType().'", '.$js_sons.')';
		/*
    return '
      {
        type: "'.$this->getFieldType().'",
      
        getValue: function(id_sel, id_filter) {
          return YAHOO.util.Dom.get("dropdown_"+id_filter+"_"+id_sel).value;
        },
        
        setValue: function(id_sel, id_filter, newValue) {
          if (!newValue) newValue=0;
          var i, s = YAHOO.util.Dom.get("dropdown_"+id_filter+"_"+id_sel);
          for (i=0; i<s.options.length; i++) {
            if (s.options[i].value == newValue) {
              s.selectedIndex = i;
              break;
            }
          }
        },
        
        render: function(id_sel, id_filter, oEl, id_field) {
          var i, sons = '.$js_sons.', s = document.createElement("SELECT"), d = document.createElement("DIV");

          s.className = "dropdon_filter_value";
          if (id_field.split("_")[0] == "std") return; //at the moment dropdown are not allowed for standard fields
          var t = sons["field_"+id_field.split("_")[1]];
          d.className = "dropdown_container"; s.id = "dropdown_"+id_filter+"_"+id_sel;          
          for (i=0; i<t.length; i++) {
            opt = document.createElement("OPTION");
            opt.value = t[i].value;
            opt.text = t[i].text;
            try { s.add(opt, null); } catch(e) { s.add(opt); }
          }
          d.appendChild(s);
          oEl.appendChild(d);  
        }
      }    
    ';
		*/
  }
  
	function checkUserField($value, $filter) {    
		return ($value == $filter);
	}

	function getFieldQuery($filter) {
		if ($filter == "") return "0";

		$output = "SELECT id_user as idst ".
			"FROM  ".$this->_getUserEntryTable()." ".
			"WHERE id_common = '".$this->id_common."' AND user_entry = ".(int)$filter;
		return $output;
/*
		if ($filter['value'] == "") return "1";

		$son = $this->getAllSon();

		$selected = array();
		foreach ($son as $id=>$value) {
			switch ($filter['cond']) {
				case 0: { //equal
					if ($value == $filter['value']) $selected[] = $id;
				} break;

				case 1: { //contains
					if (stristr($value, $filter['value'])) $selected[] = $id;
				} break;

				case 2: { //not equal
					if ($value != $filter['value']) $selected[] = $id;
				} break;

				case 3: { //do not contains
					if (!stristr($value, $filter['value'])) $selected[] = $id;
				} break;
			}
		}

		$output = (count($selected)>0 ? "SELECT id_user as idst ".
			"FROM  ".$this->_getUserEntryTable()." ".
			"WHERE id_common = '".$this->id_common."' AND user_entry IN ('".implode("','", $selected)."')" : "0");
		return $output;
*/
	}

	function toString( $field_value ) {
		$fields = $this->getAllSon();
		return $fields[$field_value];
	}
}

?>