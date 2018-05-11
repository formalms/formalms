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
 * @version  $Id: class.date.php 987 2007-02-28 17:25:05Z giovanni $
 * @category Field
 * @author   Fabio Pirovano <fabio@docebo.com>
 */

require_once(Forma::inc(_adm_.'/modules/field/class.field.php'));

class Field_Date extends Field {


	/**
	 * this function is useful for field recognize
	 *
	 * @return string	return the identifier of the field
	 *
	 * @access public
	 */
	function getFieldType() {
		return 'date';
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
			if(!isset($_POST['new_date'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_date'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_date'][$mand_lang]) == '') {
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
			('".$this->getFieldType()."', '".$mand_lang."', '".$_POST['new_date'][$mand_lang]."', '".$show_on."', '".$use_multilang."') ")) {
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
			foreach($_POST['new_date'] as $lang_code => $translation) {

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
			$form->getFormHeader($lang->def('_NEW_DATEFIELD'))
			.$form->openForm('create_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('back', 'back', $back_coded)
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_date_'.$lang_code,
									'new_date['.$lang_code.']',
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
	function edit( $back ) {
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
			if(!isset($_POST['new_date'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_date'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_date'][$mand_lang]) == '') {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
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
			foreach($_POST['new_date'] as $lang_code => $translation) {

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
			Util::jump_to($back.'&result='.( $re ? 'success' : 'fail'));
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
			.$form->getHidden('back', 'back', $back_coded)
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_date_'.$lang_code,
									'new_date['.$lang_code.']',
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

		return Format::date($user_entry, 'date');
	}

	function toString( $value ) {

		return Format::date($value, 'date');
	}

	/**
	 * display the field for interaction
	 *
	 * @param 	int		$id_user			if alredy exists a entry for the user load as default value
	 * @param 	bool	$freeze				if true, disable the user interaction
	 * @param 	bool	$mandatory			if true, the field is considered mandatory
	 * @param 	bool	$do_not_show_label	if true, do not show the label
	 *
	 * @return string 	of field xhtml code
	 *
	 * @access public
	 */
    function play( $id_user, $freeze, $mandatory = false, $do_not_show_label = false, $value = NULL, $registrationLayout=false, $registrationErrors = false ) {

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
			$user_entry = Format::date($user_entry, 'date');
		}

		$re_field = sql_query("
		SELECT translation
		FROM ".$this->_getMainTable()."
		WHERE lang_code = '".getLanguage()."' AND id_common = '".(int)$this->id_common."' AND type_field = '".$this->getFieldType()."'");
		list($translation) = sql_fetch_row($re_field);

		if ($value !== NULL) $user_entry = Format::date($value, 'date');

        if ($registrationLayout) {

            $error = (isset($registrationErrors) && $registrationErrors[$this->id_common]);
            $errorMessage = $registrationErrors[$this->id_common]['msg'];

            $formField = '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

            $formField .= '<div class="col-xs-12 col-sm-6">';
			$formField .= '<div class="input-group date">';
			$formField .= '<div class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div>';

            $formField .= Form::getInputDatefield(
                'form-control datepicker '.($error ? 'has-error' : ''),
                    'field_' . $this->getFieldType() . '_' . $this->id_common,
                    'field_' . $this->getFieldType() . '[' . $this->id_common . ']', $value, false, false, $translation,
                'placeholder="' . $translation . ($mandatory ? ' *' : '') . '"');

            if ($error) {
                $formField .= '<small class="form-text">* ' . $errorMessage . '</small>';
            }

			$formField .= '</div>';
            $formField .= '</div>';
            $formField .= '</div>';

            return $formField;
        }

		if($freeze) return Form::getLineBox($translation.' : ', $user_entry);

		return Form::getDatefield($translation.( $mandatory ? ' <span class="mandatory">*</span>' : '' ),
								'field_'.$this->getFieldType().'_'.$this->id_common,
								'field_'.$this->getFieldType().'['.$this->id_common.']',
								$user_entry,
								false,
								false,
								$translation);
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
	 * @param   mixed 	$field_special	(optional) not used
	 *
	 * @return string 	of field xhtml code
	 *
	 * @access public
	 */
	function play_filter( $id_field, $value = FALSE, $label = FALSE, $field_prefix = FALSE, $other_after = '', $other_before = '', $field_special = FALSE ) {
		require_once(_base_.'/lib/lib.form.php');

		if( $value === FALSE ) {
			$value = Field::getFieldValue_Filter( $_POST, $id_field, $field_prefix, '' );
		}
		if( $label === FALSE ) {
			$re_field = sql_query("
			SELECT translation
			FROM ".Field::_getMainTable()."
			WHERE id_common = '".(int)$id_field."' AND type_field = '".Field_Date::getFieldType()."'");
			list($label) = sql_fetch_row($re_field);
		}

		return Form::getDatefield($label,
								Field::getFieldId_Filter($id_field, $field_prefix),
								Field::getFieldName_Filter($id_field, $field_prefix),
								$value,
								false,
								false,
								$label,
								$other_after,
								$other_before );
	}


	/**
	 * function to get value of a filter field
	 *
	 * @param array		$array_values	the array to scan for search value
	 * @param string	$id_field		id of the field
	 * @param string 	$field_prefix	(optional) prefix of the field
	 * @return mixed	return the value of the field in filters
	 *
	 * @access public
	 **/
	function getFieldValue_Filter( $array_values, $id_field, $field_prefix = FALSE, $default_value = '' ) {

		if( $field_prefix !== NULL ) {
			if( isset( $array_values[$field_prefix] ) )
				$array_values = $array_values[$field_prefix];
			else
				return $default_value;
		}
		if( isset( $array_values['field_filter'])
			&& isset( $array_values['field_filter'][$id_field]) )
			return Format::dateDb($array_values['field_filter'][$id_field], 'date');
		else
			return $default_value;
	}

	/**
	 * check if the user as selected a valid value for the field
	 *
	 * @return 	bool 	true if operation success false otherwise
	 *
	 * @access public
	 */
	function isFilled( $id_user ) {

		$new_entry = Format::dateDb($_POST['field_'.$this->getFieldType()][$this->id_common], 'date');

		if(!isset($_POST['field_'.$this->getFieldType()][$this->id_common])) return false;
		elseif(trim($_POST['field_'.$this->getFieldType()][$this->id_common]) == '') return false;
		elseif(trim($new_entry) == '0000-00-00') return false;
		else return true;
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

		$new_entry = $_POST['field_'.$this->getFieldType()][$this->id_common];
		$new_entry = Format::dateDb($new_entry, 'date');
		$new_entry = Format::dateDb($_POST['field_'.$this->getFieldType()][$this->id_common], 'date');

		if($some_entry) {
			if($no_overwrite) return true;
			if(!sql_query("
			UPDATE ".$this->_getUserEntryTable()."
			SET user_entry = '".$new_entry."'
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
				'".$new_entry."')")) return false;
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
		
		if (($int_userid) || (empty($id_user)))
			$id_user=(int)$id_user;

		$re_entry = sql_query("
		SELECT user_entry
		FROM ".$this->_getUserEntryTable()."
		WHERE id_user = '".$id_user."' AND
			id_common = '".(int)$this->id_common."' AND
			id_common_son = '0'");

		$some_entry = sql_num_rows($re_entry);
		$new_entry = Format::dateDb($value, 'date');

		if($some_entry) {
			if($no_overwrite) return true;
			if(!sql_query("
			UPDATE ".$this->_getUserEntryTable()."
			SET user_entry = '".$new_entry."'
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
				'".$new_entry."')")) return false;
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


	/**
	 * check if the user has input a valid value for the field
	 *
	 * @return 	bool 	true if the field is valid success false otherwise
	 *
	 * @access public
	 */
	function isValid( $id_user ) {

		if(!isset($_POST['field_'.$this->getFieldType()][$this->id_common])) return true;
		if($_POST['field_'.$this->getFieldType()][$this->id_common] == '') return true;

		$new_entry = $_POST['field_'.$this->getFieldType()][$this->id_common];
		$new_entry = Format::dateDb($new_entry, 'date');
		if($new_entry == '0000-00-00') return true;

		$day 	= (int)substr($new_entry, 8, 2);
		$month 	= (int)substr($new_entry, 5, 2);
		$year 	= (int)substr($new_entry, 0, 4);

		if(checkdate($month, $day, $year)) { return true; }
		return false;
	}



  function getClientClassObject() {
		$format = Format::instance();
		$date_format = $format->date_token;
		Form::loadDatefieldScript($date_format);
		return 'YAHOO.dynamicFilter.renderTypes.get("'.$this->getFieldType().'", {format: "'.$date_format.'"})';
/*
    return '
      {
        type: "'.$this->getFieldType().'",
      
        getValue: function(id_sel, id_filter) {
          var o, id = "date_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
          return YAHOO.lang.JSON.stringify({cond: $D.get(id+"_sel").value, value: $D.get(id).value});
        },
        
        setValue: function(id_sel, id_filter, newValue) {
          if (!newValue) o = {cond: 0, value: ""};
          else o = YAHOO.lang.JSON.parse(newValue);
          var i, s, id = "date_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
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
          var id = "date_"+id_filter+"_"+id_sel, txt = document.createElement("INPUT"), but = document.createElement("BUTTON");
          txt.id = id;
          txt.className = "filter_value";
          txt.type = "text";
          but.id = "trigger_"+id;
          try{ but.type = "button"; } catch(e) {}
          but.className = "trigger_calendar";
          
          d = document.createElement("DIV"); d.className = "date_container";
          sel = document.createElement("SELECT"); sel.id = id+"_sel";
          sel.options[0] = new Option("<",0);
          sel.options[1] = new Option("<=",1);
          sel.options[2] = new Option("=",2);
          sel.options[3] = new Option(">=",3);
          sel.options[4] = new Option(">",4);
          sel.className = "condition_select";
          
          d.appendChild(sel);
          d.appendChild(document.createTextNode(" "));
          d.appendChild(txt);
          d.appendChild(but);
          oEl.appendChild(d);
          Calendar.setup({
            inputField  : id,
            ifFormat    : "'.$format->date_token.'",
            button      : "trigger_"+id,
            timeFormat  : '.(substr($format->time_token, 0, 2) == "%I" ? '12' : '24').',
            showsTime   : true
          });
        }
      }    
    ';
*/
  }


	function checkUserField($value, $filter) {

		$output = false;
		$d1 = substr($value, 0, 10);
		$d2 = substr(Format::dateDb($filter['value'], 'date'), 0, 10);
		switch ($filter['cond']) {
			case 0: { //<
				$output = ($d1 < $d2);
			} break;
			case 1: { //<=
				$output = ($d1 <= $d2);
			} break;
			case 2: { //=
				$output = ($d1 == $d2);
			} break;
			case 3: { //>=
				$output = ($d1 >= $d2);
			} break;
			case 4: { //>
				$output = ($d1 > $d2);
			} break;
			default: { $output = false; }
		} // end switch
		return $output;
	}

  
	function getFieldQuery($filter) {
    
		$date = Format::dateDb($filter['value'], 'date');
		$output = "SELECT id_user ".
			"FROM ".$GLOBALS['prefix_fw']."_field_userentry ".
			"WHERE id_common = '".$this->id_common."' AND ";
		$temp = " user_entry ";
		switch ($filter['cond']) {
			case 0: { $temp .= " < '".$date.".' 00:00:00'' "; } break; //<
			case 1: { $temp .= " <= '".$date.".' 23:59:59'' "; } break; //<=
			case 2: { $temp = " ( user_entry >= '".$date." 00:00:00' AND user_entry <= '".$date." 23:59:59' ) "; } break; //=
			case 3: { $temp .= " >= '".$date." 00:00:00' "; } break; //>=
			case 4: { $temp .= " > '".$date.".' 23:59:59'' "; } break; //>
			default: { $temp .= " NOT LIKE '%' "; } //unexistent
		}
		return $output.$temp;
  }
    
}

?>
