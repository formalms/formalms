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
 * @version  $Id: class.cf.php 601 2006-09-01 10:50:52Z giovanni $
 * @category Field
 * @author   Claudio Cherubino <claudio.cherubino@docebo.com>
 */

require_once(Forma::inc(_adm_.'/modules/field/class.field.php'));

class Field_Cf extends Field {

    const REGEX_CODICEFISCALE = '/^[a-z]{6}[0-9]{2}[a-z][0-9]{2}[a-z][0-9]{3}[a-z]$/i';

	/**
	 * this function is useful for field recognize
	 *
	 * @return string	return the identifier of the field
	 *
	 * @access public
	 */
	function getFieldType() {
		return 'codicefiscale';
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
			if(!isset($_POST['new_'.$this->getFieldType()][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_'.$this->getFieldType()][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_'.$this->getFieldType()][$mand_lang]) == '') {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}

			// Insert mandatory field
			if(!sql_query("
			INSERT INTO ".$this->_getMainTable()."
			(type_field, lang_code, translation, show_on_platform, use_multilang) VALUES
			('".$this->getFieldType()."', '".$mand_lang."', '".$_POST['new_'.$this->getFieldType()][$mand_lang]."', '".$show_on."', '".$use_multilang."') ")) {
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
			foreach($_POST['new_'.$this->getFieldType()] as $lang_code => $translation) {

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
			$form->getFormHeader($lang->def('_NEW_TEXTFIELD'))
			.$form->openForm('create_'.$this->getFieldType(), $this->getUrl())
			.$form->openElementSpace()
			.$form->getHidden('type_field', 'type_field', $this->getFieldType())
			.$form->getHidden('back', 'back', $back_coded)
		);
		$mand_lang = getLanguage();
		foreach($array_lang as $k => $lang_code ) {

			$out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_'.$this->getFieldType().'_'.$lang_code,
									'new_'.$this->getFieldType().'['.$lang_code.']',
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

			//control if all is ok
			if(!isset($_POST['new_'.$this->getFieldType()][$mand_lang])) {
				$out->add(
					getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
					.getBackUi($this->getUrl().'&amp;type_field='
						.$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
					'content'
				);
				return;
			}
			if($_POST['new_'.$this->getFieldType()][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_'.$this->getFieldType()][$mand_lang]) == '') {
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
			if(isset($_POST['show_on_platform'])) {
				while(list($code, ) = each($_POST['show_on_platform']))
					$show_on .= $code.',';
			}
			//insert other field
			foreach($_POST['new_'.$this->getFieldType()] as $lang_code => $translation) {

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
					(type_field, id_common, lang_code, translation, show_on_platform, use_multilang ) VALUES
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
									'new_'.$this->getFieldType().'_'.$lang_code,
									'new_'.$this->getFieldType().'['.$lang_code.']',
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

		return $user_entry;
	}

	/**
	 * display the field for interaction
	 *
	 * @param 	int		$id_user	if alredy exists a entry for the user load as default value
	 * @param 	bool	$freeze		if true, disable the user interaction
	 * @param 	bool	$mandatory			if true, the field is considered mandatory
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

		$re_field = sql_query("
		SELECT translation
		FROM ".$this->_getMainTable()."
		WHERE lang_code = '".getLanguage()."' AND id_common = '".(int)$this->id_common."' AND type_field = '".$this->getFieldType()."'");
		list($translation) = sql_fetch_row($re_field);

        if ($registrationLayout) {
            $error = (isset($registrationErrors) && $registrationErrors[$this->id_common]);
            $errorMessage = $registrationErrors[$this->id_common]['msg'];

            $formField = '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

            $formField .= '<div class="col-xs-12 col-sm-6">';
                $formField .= Form::getInputTextfield(
                    'form-control '.($error ? 'has-error' : ''),
                    'field_' . $this->getFieldType() . '_' . $this->id_common,
                    'field_' . $this->getFieldType() . '[' . $this->id_common . ']',
                    $user_entry,
                    '',
                    255,
                    'placeholder="' . $translation . ($mandatory ? ' *' : '') . '"'
                );

                if ($error) {
                $formField .= '<small class="form-text">* ' . $errorMessage . '</small>';
            }

            $formField .= '</div>';
            $formField .= '</div>';

            return $formField;
        }

		if($freeze) return Form::getLineBox($translation.' : ', $user_entry);

		if ($value != NULL) $user_entry = $value;

		return Form::getTextfield($translation.( $mandatory ? ' <span class="mandatory">*</span>' : '' ),
								'field_'.$this->getFieldType().'_'.$this->id_common,
								'field_'.$this->getFieldType().'['.$this->id_common.']',
								255,
								$user_entry,
								$translation );
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
			WHERE id_common = '".(int)$id_field."' AND type_field = '".Field_Textfield::getFieldType()."'");
			list($label) = sql_fetch_row($re_field);
		}

		return Form::getTextfield($label,
								Field::getFieldId_Filter($id_field, $field_prefix),
								Field::getFieldName_Filter($id_field, $field_prefix),
								255,
								$value,
								$label,
								$other_after,
								$other_before );
	}


	/**
	 * check if the user has selected a valid value for the field
	 *
	 * @return 	bool 	true if operation success false otherwise
	 *
	 * @access public
	 */
	function isFilled( $id_user ) {

		if(!isset($_POST['field_'.$this->getFieldType()][$this->id_common])) return false;
		elseif(trim($_POST['field_'.$this->getFieldType()][$this->id_common]) == '') return false;
		else return $this->checkCF(trim($_POST['field_'.$this->getFieldType()][$this->id_common]));
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

		// check format
		if(! $this->checkCF(trim($_POST['field_'.$this->getFieldType()][$this->id_common])) ) {
			return false;
		}
		// check unique
		$re_entry = sql_query("
		SELECT id_user
		FROM ".$this->_getUserEntryTable()."
		WHERE user_entry = '".trim($_POST['field_'.$this->getFieldType()][$this->id_common])."' AND
			id_common = '".(int)$this->id_common."' AND
			id_common_son = '0'
			AND id_user != '".$id_user."'");
		if(sql_num_rows($re_entry)) return $this->returnError(Lang::t('_CF_DUPLICATED', 'field'));

		return true;
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
		if($some_entry) {
			if($no_overwrite) return true;
			if(!sql_query("
			UPDATE ".$this->_getUserEntryTable()."
			SET user_entry = '".$value."'
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
				'".$value."')")) return false;
		}

		return true;
	}

	/**
	 * check Italian Codice Fiscale
	 *
	 * @param	string	$cf 	the string to be checked
	 *
	 * @return 	bool 	true if success false otherwise
	 *
	 * @access public
	 */
	function CheckCF($cf)
	{
		if($cf == '') return false;
		if(strlen($cf) != 16) return false;

        $cf = strtoupper($cf);
        if( !preg_match(self::REGEX_CODICEFISCALE, $cf) )  return false;

		$s = 0;
		for($i = 1; $i <= 13; $i += 2)
		{
			$c = $cf[$i];
			if('0' <= $c && $c <= '9')
				$s += ord($c) - ord('0');
			else
				$s += ord($c) - ord('A');
		}

		for($i = 0; $i <= 14; $i += 2)
		{
			$c = $cf[$i];
			switch($c)
			{
				case '0':  $s += 1;  break;
				case '1':  $s += 0;  break;
				case '2':  $s += 5;  break;
				case '3':  $s += 7;  break;
				case '4':  $s += 9;  break;
				case '5':  $s += 13;  break;
				case '6':  $s += 15;  break;
				case '7':  $s += 17;  break;
				case '8':  $s += 19;  break;
				case '9':  $s += 21;  break;
				case 'A':  $s += 1;  break;
				case 'B':  $s += 0;  break;
				case 'C':  $s += 5;  break;
				case 'D':  $s += 7;  break;
				case 'E':  $s += 9;  break;
				case 'F':  $s += 13;  break;
				case 'G':  $s += 15;  break;
				case 'H':  $s += 17;  break;
				case 'I':  $s += 19;  break;
				case 'J':  $s += 21;  break;
				case 'K':  $s += 2;  break;
				case 'L':  $s += 4;  break;
				case 'M':  $s += 18;  break;
				case 'N':  $s += 20;  break;
				case 'O':  $s += 11;  break;
				case 'P':  $s += 3;  break;
				case 'Q':  $s += 6;  break;
				case 'R':  $s += 8;  break;
				case 'S':  $s += 12;  break;
				case 'T':  $s += 14;  break;
				case 'U':  $s += 16;  break;
				case 'V':  $s += 10;  break;
				case 'W':  $s += 22;  break;
				case 'X':  $s += 25;  break;
				case 'Y':  $s += 24;  break;
				case 'Z':  $s += 23;  break;
			}
		}

		if(chr($s%26 + ord('A')) != $cf[15]) return false;

		return true;
	}
}

?>