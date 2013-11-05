<?php
require_once(dirname(__FILE__).'/class.field.php');


class Field_Textlabel extends Field {
    
    /**
    * class constructor
    */
    function Field_Textlabel($id_common) {

            parent::Field($id_common);
    }
    
    /**
    * this function is useful for field recognize
    *
    * @return string	return the identifier of the field
    *
    * @access public
    */
    function getFieldType() {
            return 'textlabel';
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
                    /*if(!isset($_POST['new_textlabel']['$mand_lang'])) {
                            $out->add(
                                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                                    .getBackUi($this->getUrl().'&amp;type_field='
                                            .$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
                                    'content'
                            );
                            return;
                    }
                    if($_POST['new_textlabel'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_textfield'][$mand_lang]) == '') {
                            $out->add(
                                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                                    .getBackUi($this->getUrl().'&amp;type_field='
                                            .$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
                                    'content'
                            );
                            return;
                    }*/

                    // Insert mandatory field
                    if(!sql_query("
                    INSERT INTO ".$this->_getMainTable()."
                    (type_field, lang_code, translation, show_on_platform, use_multilang) VALUES
                    ('".$this->getFieldType()."', '".$mand_lang."', '".$_POST['new_textlabel'][$mand_lang]."', '".$show_on."', '".$use_multilang."') ")) {
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
                    foreach($_POST['new_textlabel'] as $lang_code => $translation) {

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
            $out->add('<div class="label_block"><p><b>'.$form->getLabel($for, Lang::t('_LABEL_ALERT', 'field')).'</b> '.$form->getLabel($for, Lang::t('_LABEL_ALERT_MESSAGE', 'field')).'</p></div>');
            $out->add(
                    $form->openForm('create_'.$this->getFieldType(), $this->getUrl())
                    .$form->openElementSpace()
                    .$form->getHidden('type_field', 'type_field', $this->getFieldType())
                    .$form->getHidden('back', 'back', $back_coded)
            );
            $mand_lang = getLanguage();
            foreach($array_lang as $k => $lang_code ) {
                    
                    $out->add(
				$form->getTextfield(( ($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '' ).$lang_code,
									'new_textlabel_'.$lang_code,
									'new_textlabel['.$lang_code.']',
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
                    /*if(!isset($_POST['new_textlabel'][$mand_lang])) {
                            $out->add(
                                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                                    .getBackUi($this->getUrl().'&amp;type_field='
                                            .$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
                                    'content'
                            );
                            return;
                    }
                    if($_POST['new_textlabel'][$mand_lang] == $lang->def('_FIELD_NAME') || trim($_POST['new_textlabel'][$mand_lang]) == '') {
                            $out->add(
                                    getErrorUi($lang->def('_ERR_MUST_DEF_MANADATORY_TRANSLATION'))
                                    .getBackUi($this->getUrl().'&amp;type_field='
                                            .$this->getFieldType().'&amp;back='.$back_coded, $std_lang->def('_BACK')),
                                    'content'
                            );
                            return;
                    }*/

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
                    foreach($_POST['new_textlabel'] as $lang_code => $translation) {

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
            $out->add('<div class="label_block"><p><b>'.$form->getLabel($for, Lang::t('_LABEL_ALERT', 'field')).'</b> '.$form->getLabel($for, Lang::t('_LABEL_ALERT_MESSAGE', 'field')).'</p></div>');
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
									'new_textlabel_'.$lang_code,
									'new_textlabel['.$lang_code.']',
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
//

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


    function showInLang( $id_user, $lang ) {

            list($user_entry) = sql_fetch_row(sql_query("
            SELECT user_entry
            FROM ".$this->_getUserEntryTable()."
            WHERE id_user = '".(int)$id_user."' AND
                    id_common = '".(int)$this->id_common."' AND
                    id_common_son = '0' AND language='".$lang."'"));

            return $user_entry;
    }


    /**
    * display the field for interaction
    *
    * @param 	int	$id_user	if alredy exists a entry for the user load as default value
    *
    * @return string 	of label xhtml code
    *
    * @access public
    */
    function play($id_user, $do_not_show_label = false, $value = NULL) {

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

            if ($value !== NULL) $user_entry = "".$value;

            return Form::getTextLabel($translation);
    }

    
    function multiLangPlay($id_user, $freeze, $mandatory = false) {
            $res ="";
            require_once(_base_.'/lib/lib.form.php');

            $found_in_post =FALSE;
            $larr=Docebo::langManager()->getAllLangCode();
            foreach ($larr as $lang) {
                    if( 	isset( $_POST['field_'.$this->getFieldType()] ) &&
                                isset( $_POST['field_'.$this->getFieldType()][$this->id_common][$lang] ) ) {

                            $found_in_post =TRUE;
                            $user_entry[$lang] = $_POST['field_'.$this->getFieldType()][$this->id_common][$lang];
                    }
            }

            if (!$found_in_post) {
                    $qtxt ="SELECT user_entry,language FROM ".$this->_getUserEntryTable()." ";
                    $qtxt.="WHERE id_user = '".(int)$id_user."' AND ";
                    $qtxt.="id_common = '".(int)$this->id_common."' AND ";
                    $qtxt.="id_common_son = '0'";

                    $q =sql_query($qtxt);

                    if (($q) && (sql_num_rows($q) > 0)) {
                            while($row=sql_fetch_assoc($q)) {
                                    $lang =$row["language"];
                                    $user_entry[$lang]=$row["user_entry"];
                            }
                    }
            }

            $re_field = sql_query("
            SELECT translation
            FROM ".$this->_getMainTable()."
            WHERE lang_code = '".getLanguage()."' AND id_common = '".(int)$this->id_common."' AND type_field = '".$this->getFieldType()."'");
            list($translation) = sql_fetch_row($re_field);

            foreach ($larr as $lang) {
                    $label =$translation.' ('.$lang.') <span class="mandatory">*</span>';
                    $field_id ='field_'.$this->getFieldType().'_'.$this->id_common.'_'.$lang;
                    $field_name ='field_'.$this->getFieldType().'['.$this->id_common.']['.$lang."]";
                    $field_val =(isset($user_entry[$lang]) ? $user_entry[$lang] : "");
                    $res.=Form::getTextfield($label, $field_id, $field_name, 255, $field_val);
            }

            return $res;
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
        * check if the user as selected a valid value for the field
        *
        * @return 	bool 	true if operation success false otherwise
        *
        * @access public
        */
    function isFilled( $id_user ) {

            if(!isset($_POST['field_'.$this->getFieldType()][$this->id_common])) return false;
            elseif(trim($_POST['field_'.$this->getFieldType()][$this->id_common]) == '') return false;
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


    function multiLangStore( $id_user, $no_overwrite, $int_userid=TRUE ) {
            $res =TRUE;

            if (($int_userid) || (empty($id_user)))
                    $id_user=(int)$id_user;

            $q = sql_query("
            SELECT user_entry, language
            FROM ".$this->_getUserEntryTable()."
            WHERE id_user = '".$id_user."' AND
                    id_common = '".(int)$this->id_common."' AND
                    id_common_son = '0'");

            $lang_with_entry =array();
            if (($q) && (sql_num_rows($q) > 0)) {
                    $some_entry =TRUE;
                    while($row=sql_fetch_assoc($q)) {
                            $lang_with_entry[]=$row["language"];
                    }
            }
            else {
                    $some_entry =FALSE;
            }


            if($some_entry) {
                    if($no_overwrite)
                            return $res; // (TRUE)

                    $larr=Docebo::langManager()->getAllLangCode();
                    foreach ($larr as $lang) {

                            if (isset($_POST['field_'.$this->getFieldType()][$this->id_common][$lang])) {
                                    $user_entry =$_POST['field_'.$this->getFieldType()][$this->id_common][$lang];
                            }
                            else {
                                    $user_entry ="";
                            }

                            if (in_array($lang, $lang_with_entry)) {
                                    $qtxt ="UPDATE ".$this->_getUserEntryTable()." ";
                                    $qtxt.="SET user_entry = '".$user_entry."' ";
                                    $qtxt.="WHERE id_user = '".$id_user."' AND ";
                                    $qtxt.="id_common = '".(int)$this->id_common."' AND ";
                                    $qtxt.="id_common_son = '0' AND language='".$lang."'";
                            }
                            else {
                                    $qtxt ="INSERT INTO ".$this->_getUserEntryTable()." ";
                                    $qtxt.="(id_user, id_common, id_common_son, language, user_entry) VALUES ";
                                    $qtxt.="(	'".$id_user."', '".(int)$this->id_common."', '0', '".$lang."', ";
                                    $qtxt.="'".$user_entry."')";
                            }

                            $q =sql_query($qtxt);
                            if (!$q) {
                                    $res =FALSE;
                            }
                    }
            }
            else {

                    $ins_arr =array();

                    $qtxt ="INSERT INTO ".$this->_getUserEntryTable()." ";
                    $qtxt.="(id_user, id_common, id_common_son, language, user_entry) VALUES ";

                    $larr=Docebo::langManager()->getAllLangCode();
                    foreach ($larr as $lang) {
                            if (isset($_POST['field_'.$this->getFieldType()][$this->id_common][$lang])) {
                                    $ins_line ="(	'".$id_user."', '".(int)$this->id_common."', '0', '".$lang."', ";
                                    $ins_line.="'".$_POST['field_'.$this->getFieldType()][$this->id_common][$lang]."')";
                                    $ins_arr[]=$ins_line;
                            }
                    }

                    if (!empty($ins_arr)) {
                            $qtxt.=implode(", ", $ins_arr);
                            $q =sql_query($qtxt);
                            $res =($q ? TRUE : FALSE);
                    }
            }

            return $res;
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

            $value = addslashes(stripslashes($value));

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


    function storeDirectMultiple( $idst_users, $value, $is_id, $no_overwrite, $int_userid=TRUE ) {
            if (is_numeric($idst_users)) $idst_users = array($idst_users);
            if (!is_array($idst_users)) return false;
            if (empty($idst_users)) return true;

            $value = addslashes(stripslashes($value));

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


    function multiLangStoreDirect( $id_user, $value, $is_id, $no_overwrite, $int_userid=TRUE ) {
            $res =TRUE;

            if (($int_userid) || (empty($id_user)))
                    $id_user=(int)$id_user;

            $q = sql_query("
            SELECT user_entry, language
            FROM ".$this->_getUserEntryTable()."
            WHERE id_user = '".$id_user."' AND
                    id_common = '".(int)$this->id_common."' AND
                    id_common_son = '0'");

            $lang_with_entry =array();
            if (($q) && (sql_num_rows($q) > 0)) {
                    $some_entry =TRUE;
                    while($row=sql_fetch_assoc($q)) {
                            $lang_with_entry[]=$row["language"];
                    }
            }
            else {
                    $some_entry =FALSE;
            }


            if($some_entry) {
                    if($no_overwrite)
                            return $res; // (TRUE)

                    $larr=Docebo::langManager()->getAllLangCode();
                    foreach ($larr as $lang) {

                            if (isset($value[$lang])) {
                                    $user_entry =$value[$lang];
                                    $user_entry = addslashes(stripslashes($user_entry));
                            }
                            else {
                                    $user_entry ="";
                            }

                            if (in_array($lang, $lang_with_entry)) {
                                    $qtxt ="UPDATE ".$this->_getUserEntryTable()." ";
                                    $qtxt.="SET user_entry = '".$user_entry."' ";
                                    $qtxt.="WHERE id_user = '".$id_user."' AND ";
                                    $qtxt.="id_common = '".(int)$this->id_common."' AND ";
                                    $qtxt.="id_common_son = '0' AND language='".$lang."'";
                            }
                            else {
                                    $qtxt ="INSERT INTO ".$this->_getUserEntryTable()." ";
                                    $qtxt.="(id_user, id_common, id_common_son, language, user_entry) VALUES ";
                                    $qtxt.="(	'".$id_user."', '".(int)$this->id_common."', '0', '".$lang."', ";
                                    $qtxt.="'".$user_entry."')";
                            }

                            $q =sql_query($qtxt);
                            if (!$q) {
                                    $res =FALSE;
                            }
                    }
            }
            else {

                    $ins_arr =array();

                    $qtxt ="INSERT INTO ".$this->_getUserEntryTable()." ";
                    $qtxt.="(id_user, id_common, id_common_son, language, user_entry) VALUES ";

                    $larr=Docebo::langManager()->getAllLangCode();
                    foreach ($larr as $lang) {
                            if (isset($value[$lang])) {
                                    $ins_line ="(	'".$id_user."', '".(int)$this->id_common."', '0', '".$lang."', ";
                                    $ins_line.="'".addslashes(stripslashes($value[$lang]))."')";
                                    $ins_arr[]=$ins_line;
                            }
                    }

                    if (!empty($ins_arr)) {
                            $qtxt.=implode(", ", $ins_arr);
                            $q =sql_query($qtxt);
                            $res =($q ? TRUE : FALSE);
                    }
            }

            return $res;
    }

}
?>
