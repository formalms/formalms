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

class Field_Country extends Field {

	var $back;
	var $back_coded;
	
	var $_options = array();

	/**
	 * class constructor
	 */
	function __construct($id_common) {

		parent::__construct($id_common);
		
		$query_tax_country = "
		SELECT id_country, name_country
		FROM ".$this->getCountryTable()."
		ORDER BY name_country";
		$re_field_element = sql_query($query_tax_country);

		$this->_options[0] = '';
		while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {

			$this->_options[$id_common_son] = $this->convert_name($element);
		}
		
	}

	/**
	 * this function is useful for field recognize
	 *
	 * @return string	return the identifier of the field
	 *
	 * @access public
	 */
	function getFieldType() {
		return 'country';
	}

	function getCountryTable() {
		return $GLOBALS['prefix_fw'].'_country';
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
			$use_multilang = 0;
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
			if(!isset($_POST['new_country'][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_country'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_country'][$mand_lang]) == '') {
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
			foreach($_POST['new_country'] as $lang_code => $translation) {

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
			$form->getFormHeader($lang->def('_MODIFY_COUNTRY'))
			.$form->openForm('create_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('id_common', 'id_common', $this->id_common)
			.$form->getHidden('back', 'back', $back_coded)
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_country_'.$lang_code,
									'new_country['.$lang_code.']',
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
		DELETE FROM ".$this->_getMainTable()."
		WHERE id_common = '".(int)$this->id_common."'";
		$re = sql_query($query_del);

		Util::jump_to($back.'&result='.( $re ? 'success' : 'fail'));
	}

	function convert_name($name) {
		/*
		$exp_name = explode(' ', $name);
		while(list($i, $word) = each($exp_name)) {
			$exp_name[$i] = ucfirst(strtolower($word));
		}
		return implode(' ', $exp_name);*/
		
		return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
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
		
		/*
		$query_tax_country = "
		SELECT id_country, name_country
		FROM ".$this->getCountryTable()." 
		ORDER BY name_country";
		$re_field_element = sql_query($query_tax_country);
		
		$option = array();
		$option[0] = '';
		while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {
			$option[$id_common_son] = $this->convert_name($element);
		}*/
		$user_entry = (int)$user_entry;
		return $this->_options[$user_entry];

		//$option[$user_entry];
	}

	function toString( $value ) {

		$value = (int)$value;
		return $this->_options[$value];
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
	 * @param		mixed $value  if set different to NULL, force the field value to $value
	 *
	 * @return string 	of field xhtml code
	 *
	 * @access public
	 */
	function play( $id_user, $freeze, $mandatory = false, $do_not_show_label = false, $value = NULL, $registrationLayout = false, $registrationErrors = false) {
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

		if(!isset($this->_tr_field[$this->id_common])) {
			
			$re_field = sql_query("
			SELECT translation
			FROM ".$this->_getMainTable()."
			WHERE id_common = '".(int)$this->id_common."' AND
				type_field = '".$this->getFieldType()."' AND
				lang_code = '".getLanguage()."'");
			list($translation) = sql_fetch_row($re_field);
			
			$this->_tr_field[$this->id_common] = $translation;
		} else {
			$translation = $this->_tr_field[$this->id_common];
		}
		
		/*
		$query_tax_country = "
		SELECT id_country, name_country
		FROM ".$this->getCountryTable()." 
		ORDER BY name_country";
		$re_field_element = sql_query($query_tax_country);
		
		$option = array();
		$option[0] = Lang::t('_DROPDOWN_NOVALUE', 'field', 'framework');
		while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {
			$option[$id_common_son] = $this->convert_name($element);
		}*/
		$this->_options[0] = Lang::t('_DROPDOWN_NOVALUE', 'field', 'framework');

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
                    $this->_options,
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

		if($freeze) return Form::getLineBox($translation.' : ', $this->_options[$user_entry]);

		return Form::getDropdown($translation.( $mandatory ? ' <span class="mandatory">*</span>' : '' ),
								'field_'.$this->getFieldType().'_'.$this->id_common,
								'field_'.$this->getFieldType().'['.$this->id_common.']',
								$this->_options,
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

		
			$query_tax_country = "
			SELECT id_country, name_country
			FROM ".Field_Country::getCountryTable()." 
			ORDER BY name_country";
			$re_field_element = sql_query($query_tax_country);
			
			
			while(list($id_common_son, $element) = sql_fetch_row($re_field_element)) {
				$option[$id_common_son] = $this->convert_name($element);
			}
		}

		if( $label === FALSE ) {
			$re_field = sql_query("
			SELECT translation
			FROM ".Field::_getMainTable()."
			WHERE id_common = '".(int)$id_field."'
				AND type_field = '".Field_Country::getFieldType()."'");
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

			
			$query_tax_country = "
			SELECT name_country
			FROM ".Field_Country::getCountryTable()." 
			WHERE id_country = '".$grab_from['field_'.$this->getFieldType()][$this->id_common]."'";
			$re_field_element = sql_query($query_tax_country);
			
			list($translation) = sql_fetch_row($query_tax_country);

			return $this->convert_name($translation);
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
		if($is_id === false)
		{
			if(isset($GLOBALS['temp']['dropdown_value_'.$this->id_common]))
			{
				// alredy read form database, search in the array (cache)
				$index = array_search(strtoupper($value), $GLOBALS['temp']['dropdown_value_'.$this->id_common]);
				if($index === false || $index === NULL)
					$id_value = 0;
				else
					$id_value = $index;
			}
			else
			{
				$field_to_match =(strlen($value) == 2 ? 'iso_code_country' : 'name_country');
				$query_tax_country = "
					SELECT id_country, ".$field_to_match."
					FROM ".Field_Country::getCountryTable()." 
					ORDER BY ".$field_to_match;
				$re_values = sql_query($query_tax_country);
				while(list($id_country, $name_country) = sql_fetch_row($re_values))
				{
					// caching:
					$GLOBALS['temp']['dropdown_value_'.$this->id_common][$id_country] = $name_country;
					if($name_country == strtoupper($value)) $id_value = $id_country;
				}
			}
		}
		else
		{
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




	// NOTE: special functions ---------------------------------------

	function getAllSon() {

		$lang 			=& DoceboLanguage::createInstance('field');

		$sons = array();
		//find available son
		
		$query_tax_country = "
		SELECT id_country, name_country
		FROM ".Field_Country::getCountryTable()." 
		ORDER BY name_country";
		$re_field = sql_query($query_tax_country);
		
		if(!$re_field) return $sons;
		while(list($id_son, $elem) = sql_fetch_row($re_field)) {

			$sons[$id_son] = ucfirst(strtolower($elem));
		}
		return $sons;
	}
	
	
	
	function getClientClassObject() {
    $sons = $this->getAllSon();
    $temp=array();
    foreach ($sons as $key=>$val) {
      $temp[] = '{value: "'.$key.'", text: "'.$val.'"}';
    }
    $js_sons = '['.implode(",", $temp).']';
    return '
      {
        type: "'.$this->getFieldType().'",
      
        getValue: function(id_sel, id_filter) {
          return YAHOO.util.Dom.get("country_"+id_filter+"_"+id_sel).value;
        },
        
        setValue: function(id_sel, id_filter, newValue) {
          if (!newValue) newValue=0;
          var i, s = YAHOO.util.Dom.get("country_"+id_filter+"_"+id_sel);
          for (i=0; i<s.options.length; i++) {
            if (s.options[i].value == newValue) {
              s.selectedIndex = i;
              break;
            }
          }
        },
        
        render: function(id_sel, id_filter, oEl, id_field) {
          var i, sons = '.$js_sons.', s = document.createElement("SELECT"), d = document.createElement("DIV");
          if (id_field.split("_")[0] == "std") return; //at the moment country type is not allowed for standard fields
          d.className = "country_container"; s.id = "country_"+id_filter+"_"+id_sel;          
          for (i=0; i<sons.length; i++) {
            opt = document.createElement("OPTION");
            opt.value = sons[i].value;
            opt.text = sons[i].text;
            try { s.add(opt, null); } catch(e) { s.add(opt); }
          }
          d.appendChild(s);
          oEl.appendChild(d);
        }
      }    
    ';
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
  }

}

?>