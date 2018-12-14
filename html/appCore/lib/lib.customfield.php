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
 * @package admin-library
 * @subpackage field
 * @version  $Id:$
 */

define("CUSTOMFIELDTABLE", 		"_customfield");
define("CUSTOMFIELDLANGTABLE", 		"_customfield_lang");
define("CUSTOMFIELDTYPETABLE", 		"_customfield_type");
define("CUSTOMFIELDENTRYTABLE", 	"_customfield_entry");
define("CUSTOMFIELDAREATABLE", 		"_customfield_area");

define("FIELD_INFO_ID", 			0);
define("FIELD_INFO_TYPE", 			1);
define("FIELD_INFO_TRANSLATION", 	2);
define("FIELD_INFO_GROUPIDST", 		3);
define("FIELD_INFO_GROUPID", 		4);
define("FIELD_INFO_MANDATORY", 		5);
define("FIELD_INFO_USERACCESS", 	6);
define("FIELD_INFO_USERINHERIT", 	7);

define("FIELD_BASEINFO_FILE", 		0);
define("FIELD_BASEINFO_CLASS", 		1);

class CustomFieldList {

	/** @var string $field_table the main definition field table */
	var $field_table = '';
        
	/** @var string $field_lang_table the main definition field table */
	var $field_lang_table = '';

	/** @var string $type_field_table the fields type definition table */
	var $type_field_table = '';

	/** @var string $group_field_table the fields <-> group relation table */
	var $group_field_table = '';

	/** @var string $field_entry_table the fields value table */
	var $field_entry_table = FALSE;
        
	/** @var string $field_area_table the fields value table */
	var $field_area_table = '';

	/** @var string $use_multi_lang tell to the object if it has to use
   * or not the multi language features
	 */
	var $use_multi_lang = FALSE;

	/** @var string $field_area the main definition field area */
	var $field_area = '';
        
	function getFieldTable() { 			return $this->field_table; }
	function getFieldLangTable() { 			return $this->field_lang_table; }
	function getTypeFieldTable() { 		return $this->type_field_table; }
	function getGroupFieldsTable() { 	return $this->group_field_table; }
	function getFieldEntryTable() { 	return $this->field_entry_table; }
        function getFieldAreaTable() { 	return $this->field_area_table; }
        function getFieldArea() { 	return $this->field_area; }

	function setFieldTable( $field_table ) { 				$this->field_table = $field_table; }
	function setFieldLangTable( $field_lang_table ) { 			$this->field_lang_table = $field_lang_table; }
	function setTypeFieldTable( $type_field_table ) { 		$this->type_field_table = $type_field_table; }
	function setGroupFieldsTable( $group_field_table ) { 	$this->group_field_table = $group_field_table; }
	function setFieldEntryTable( $field_entry_table ) { 	$this->field_entry_table = $field_entry_table; }
        function setFieldAreaTable( $field_area_table ) { 	$this->field_area_table = $field_area_table; }
        function setFieldArea( $field_area ) { 	$this->field_area = $field_area; }

	function CustomFieldList() {
		$prefix = '%adm';
		$this->field_table = $prefix.CUSTOMFIELDTABLE;
		$this->field_lang_table = $prefix.CUSTOMFIELDLANGTABLE;
		$this->type_field_table = $prefix.CUSTOMFIELDTYPETABLE;
		$this->field_entry_table = $prefix.CUSTOMFIELDENTRYTABLE;
                $this->field_area_table = $prefix.CUSTOMFIELDAREATABLE;
	}

	function &getFieldInstance($id_field, $type_file = false, $type_class = false) {

		if($type_file === false && $type_class === false) {

			$query = "SELECT ft.id_field, tft.type_file, tft.type_class"
					."  FROM ".$this->getFieldTable() ." AS ft"
					."  JOIN ".$this->getTypeFieldTable(). " AS tft"
					." WHERE ft.id_field = '".$id_field."' AND ft.type_field = tft.type_field";
			if(!$rs = sql_query($query))  {
				$false_var = NULL;
				return $false_var;
			}
			list( $id_field, $type_file, $type_class ) = sql_fetch_row( $rs );
		} else {

			$id_field = $id_field;
		}
		require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
		$quest_obj = new $type_class( $id_field );

		return $quest_obj;
	}

	function getArrFieldFromQuery($query_field) {

		$output = array();
		$query = "SELECT ft.id_field, tft.type_field,  tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				."	ON (ft.type_field = tft.type_field) "
				." WHERE ft.id_field IN ( ".$query_field." ) ";
		if(!$rs = sql_query($query)) return false;
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row( $rs )) {

			$output[$id_field] = array(
				'id' => $id_field ,
				'type' => $type_field,
				'file' => $type_file,
				'class' => $type_class
			);
		}
		return $output;
	}

	function &getFieldInstanceFromString($id_field, $type_file, $type_class) {

		$query = "SELECT ft.id_field, tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				." WHERE ft.id_field = '".$id_field."' AND ft.type_field = tft.type_field";
		if(!$rs = sql_query($query))  {
			$false_var = NULL;
			return $false_var;
		}

		list( $id_field, $type_file, $type_class ) = sql_fetch_row( $rs );
		require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
		$quest_obj =  new $type_class( $id_field );

		return $quest_obj;
	}

	/**
	 * @param  string	the content of the field mandatory of the GroupFieldsTable
 	 * @return bool 	true if the field is mandatory
	 **/
	function _mandatoryField($mandatory) {
		return ($mandatory == 'true');
	}


	function getUseMultiLang() {
		return (bool)$this->use_multi_lang;
	}


	function setUseMultiLang($val) {
		$this->use_multi_lang =(bool)$val;
	}


	/**
 	 * @return array array of all fields; index is numeric, value is array with
	 * 				 - idfield (id_field)
	 *				 -
	 *				 - translation (in current language)
	 **/
	function getAllFields($platform = false, $type_field = false) {

		$query = "SELECT id_field, type_field, translation"
				."  FROM ".$this->getFieldTable()
				." WHERE lang_code = '".getLanguage()."'";
		if($type_field != false) {
			$query .= " AND type_field = '".$type_field."'";
		}
		$query .= " ORDER BY sequence";
		$rs = sql_query( $query );
		$result = array();

		while( $arr = sql_fetch_row($rs) )
			$result[$arr[FIELD_INFO_ID]] = $arr;
		return $result;
	}

	function getFlatAllFields($platform = false, $type_field = false, $lang_code = false) {
		$db = DbConn::getInstance();

		if( $lang_code === false )
			$lang_code = getLanguage();
		$query = "SELECT id_field, type_field, translation"
				." FROM ".$this->getFieldTable()
				." WHERE lang_code = '".$lang_code."' AND type_field != 'textlabel' ";
		if($type_field != false) {
			$query .= " AND type_field = '".$type_field."'";
		}
		$query .= " ORDER BY sequence";
		$rs = $db->query( $query );
		$result = array();

		while( $arr = $db->fetch_row($rs) )
			$result[$arr[FIELD_INFO_ID]] = $arr[FIELD_INFO_TRANSLATION];
		return $result;
	}

    function getCustomFields($area) {
        $db = DbConn::getInstance();

        $query = "SELECT %adm_customfield.id_field, type_field, code, translation "
                ." FROM %adm_customfield, %adm_customfield_lang"
                ." WHERE area_code = '".$area."' and %adm_customfield_lang.id_field=%adm_customfield.id_field and lang_code='".getLanguage()."' ORDER BY sequence";
        $rs = $db->query( $query );
        $result = array();

        while( $arr = $db->fetch_row($rs) ){
            $result[$arr[FIELD_INFO_ID]] = $arr[3];
        }     
            
        return $result;
    }    
    



  function getAllFieldsInfo($lang_code = false) {
    $db = DbConn::getInstance();
		
    if( $lang_code === false )
			$lang_code = getLanguage();
		
		$query = "SELECT ft.id_field, ft.type_field, ft.translation, tft.type_file, tft.type_class "
					."  FROM ".$this->getFieldTable() ." AS ft "
					."  JOIN ".$this->getTypeFieldTable(). " AS tft ON ( tft.type_field = ft.type_field ) "
					."  WHERE lang_code = '".$lang_code."' ORDER BY sequence ";
		
    if(!$rs = $db->query($query))  {
			$false_var = NULL;
			return $false_var;
		}
		
		$output = array();
    while (list( $id_field, $type_field, $name_field, $type_file, $type_class ) = $db->fetch_row( $rs )) {
			$output[] = array(
        'id' => $id_field ,
        'type' => $type_field,
        'name' => $name_field
      );
		}
		return $output;
	}



	/**
 	 * @return array array of fields; index is numeric, value is array with
	 * 				 - idfield (id_field)
	 *				 -
	 *				 - translation (in current language)
	 **/
	function getFieldsFromArray ($field_list_arr) {

		if ((!is_array($field_list_arr)) || (count($field_list_arr) < 1))
			return FALSE;

		$query = "SELECT id_field, type_field, translation"
				."  FROM ".$this->getFieldTable()
				." WHERE lang_code = '".getLanguage()."' ";

		$query .= "AND id_field IN (".implode(",", $field_list_arr).") ";

		$query .= "ORDER BY sequence";
		$rs = sql_query( $query );
		$result = array();

		while( $arr = sql_fetch_row($rs) )
			$result[$arr[FIELD_INFO_ID]] = $arr;
		return $result;
	}


	/**
	 * @param array $arr_idst idst to search
 	 * @return array array of fields that is associated to an idst;
	 *					index is numeric, value is array with
	 * 				 - idfield (id_field)
	 *				 - translation (in current language)
	 **/
	function getFieldsFromIdst($arr_idst, $use_group = TRUE, $platform = false ) {
		$query = "SELECT ft.id_field, ft.type_field, ft.translation, gft.idst,"
				.($use_group?" g.groupid,":"0,")." gft.mandatory, gft.useraccess, gft.user_inherit "
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
				.($use_group?("  JOIN %adm_group AS g"):"")
				." WHERE ft.lang_code = '".getLanguage()."'"
				."   AND ft.id_field = gft.id_field"
				.($use_group?("   AND gft.idst = g.idst"):"")
				."   AND gft.idst IN ('".implode("','", $arr_idst)."')"
				." ORDER BY ft.sequence";
		$rs = sql_query( $query );
		$result = array();
		while( $arr = sql_fetch_row($rs) )
			$result[$arr[FIELD_INFO_ID]] = $arr;
		return $result;
	}

	/**
	 * return the info and the value of the field assigned to a user
	 * @param int $id_user the idst of the user
	 * @param array $manual_id_field if != false the function filter the field with this and not for the field associated to the user
	 * @param array $filter_category filter for type_category
	 */
	function getFieldsAndValueFromUser($id_user, $manual_id_field = false, $show_invisible_to_user = false, $filter_category = false) {

		$acl = new DoceboACL();
		if($manual_id_field === false)
			$user_groups = $acl->getUserGroupsST($id_user);

		$query = "SELECT ft.id_field, ft.type_field, ftt.type_file, ftt.type_class, ft.translation, gft.mandatory, gft.useraccess "
				."FROM ".$this->getFieldTable()." AS ft "
				."	JOIN ".$this->getGroupFieldsTable()." AS gft "
 				." 	JOIN ".$this->getTypeFieldTable()." AS ftt "
				."WHERE ft.id_field = gft.id_field "
				." 	AND ft.type_field = ftt.type_field "
				." 	AND ft.lang_code = '".getLanguage()."'"
				.( $show_invisible_to_user === false
					? " AND gft.useraccess <> 'readwrite' "
					: "" )
				.( $manual_id_field !== false
					? "  AND ft.id_field IN ('".implode("','", $manual_id_field)."')"
					: "  AND gft.idst IN ('".implode("','", $user_groups)."')" )
				.( $filter_category !== false
					? " AND ftt.type_category IN ( '".implode("','", $filter_category)."' ) "
					: "" )
				."ORDER BY ft.sequence";

		$rs = sql_query( $query );
		

		$result = array();
		while(list($id_field, $type_field, $type_file, $type_class, $translation, $mandatory, $useraccess) = sql_fetch_row($rs)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = new $type_class( $id_field );
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());

			$result[$id_field] = array(
				0 => $translation,
				1 => (!$this->getUseMultiLang() ? $quest_obj->show( $id_user ) : $quest_obj->showInLang( $id_user, getLanguage() )),
				2 => $mandatory,
				3 => $useraccess,
				4 => $type_field,
				5 => $type_file,
				6 => $type_class );
		}

		return $result;
	}

	/**
	 * @param array $arr_idst idst to search
	 * @param int $value_key the required information that has to be filled in the array
	 *        For example FIELD_INFO_ID or FIELD_INFO_TRANSLATION
	 * @return array [field id] => [value required]
	 **/
	function getFieldsArrayFromIdst($arr_idst, $value_key, $use_group = TRUE, $platform = false ) {
		$fields=$this->getFieldsFromIdst($arr_idst, $use_group = TRUE, $platform = false);

		$res=array();
		foreach ($fields as $field) {
			$res[$field[FIELD_INFO_ID]]=$field[$value_key];
		}

		return $res;
	}

	/**
	 * find the value for the fields correlated with all the  user
	 * @param int 	$id_field 		the id of the field
	 *
	 * @return array with the value saved for the users
	 **/
	function getAllFieldEntryData($id_field) {

		$query = "
		SELECT id_user, user_entry
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_field = ".(int)$id_field."";
		$rs = sql_query( $query );

		$result = array();
		while( list($id, $value) = sql_fetch_row($rs) )
			$result[$id] = $value;
		return $result;
	}

	/**
	 * find the number of value filled for the field correlated with all the  user
	 * @param int 	$id_field 		the id of the field
	 *
	 * @return array with the value saved for the users
	 **/
	function getNumberOfFieldEntryData($id_field, $exclude_blank = false) {

		$query = "
		SELECT COUNT(*)
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_field = ".(int)$id_field."";
		if($exclude_blank === true) $query .= " AND user_entry <> '' ";
		if(!$rs = sql_query( $query )) return false;

		list($num) = sql_fetch_row($rs);
		return $num;
	}

	/**
	 * find the number of value filled for the field correlated with all the  user
	 * @param int 	$obj_entry 		the id of the field
	 *
	 * @return array with the value saved for the users
	 **/
	function getNumberOfObjFieldEntryData($id_field, $obj_entry, $sub_obj = NULL) {

		$query = "
		SELECT COUNT(*)
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_field = ".(int)$id_field." AND obj_entry = ".(int)$obj_entry."
                AND id_field IN (SELECT id_field FROM %adm_customfield WHERE area_code='".$this->getFieldArea()."')";
                if (is_array($sub_obj)){
                    $query = $query." AND id_obj IN (".implode(",",$sub_obj).")";
                }
		if(!$rs = sql_query( $query )) return false;

		list($num) = sql_fetch_row($rs);
		return $num;
	}
        
	/**
	 * find the number of value filled for area
	 *
	 * @return array with the value saved for the users
	 **/
	function getNumberFieldbyArea() {

		$query = "
		SELECT COUNT(*)
		FROM ".$this->getFieldTable() ."
		WHERE area_code='".$this->getFieldArea()."'";
                
		if(!$rs = sql_query( $query )) return false;

		list($num) = sql_fetch_row($rs);
		return $num;
	}
        
	/**
	 * find the value for the fields correlated with the user
	 * @param int 		$id_user 		the idst f the user
	 * @param array 	$arr_field 		the id of the fields
	 *
	 * @return array with the value saved for the user
	 **/
	function getUserFieldEntryData($id_user, $arr_field = false) {

		$query = "
		SELECT id_field, user_entry
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_user = '".(int)$id_user."' ";
		if($arr_field) $query .= " AND id_field IN ( ".implode(',', $arr_field)." ) ";
		$rs = sql_query( $query );
		
		$result = array();
		while( list($id, $value) = sql_fetch_row($rs) )
			$result[$id] = $value;
		return $result;
	}


	/**
	 * find the value for the fields correlated with a list of user
	 * @param int|array		$users				the idst of the user(s)
	 * @param array				$arr_field 		the id of the fields
	 *
	 * @return array with the value saved for the user
	 **/
	function getUsersFieldEntryData($users, $fields = false, $translate = true) {

		if (is_numeric($users)) $users = array($users);
		if (!is_array($users)) return false;

		if (is_numeric($fields)) $fields = array($fields);
		if (!is_array($fields)) $fields = false;

		if ($translate) {
			$sons_arr = array();
			$sons_query = "SELECT id_field, id_field_son, translation "
				." FROM %adm_customfield_son WHERE lang_code='".Lang::get()."' ";
			if (!empty($fields)) $sons_query .= " AND id_field IN (".implode(',', $fields).")";
			$sons_rs = sql_query($sons_query);
			while (list($id_field, $id_son, $translation) = sql_fetch_row($sons_rs)) {
				$sons_arr[$id_field][$id_son] = $translation;
			}

			$yesno_fields = array();
			$yn_query = "SELECT id_field FROM %adm_customfield WHERE type_field = 'yesno' ";
			if (!empty($fields)) $yn_query .= " AND id_field IN ( ".implode(',', $fields)." )";
			$yn_rs = sql_query($yn_query);
			while (list($id_field) = sql_fetch_row($yn_rs)) {
				$yesno_fields[] = $id_field;
			}
		}

		$query = "SELECT id_user, id_field, user_entry AS uentry "
			." FROM ".$this->getFieldEntryTable() ." "
			." WHERE id_user IN (".implode(",", $users).") ";
		if (!empty($fields)) $query .= " AND id_field IN ( ".implode(',', $fields)." ) ";

		$rs = sql_query( $query );

		$result = array();
		while( list($id_user, $id_field, $value) = sql_fetch_row($rs) ) {
			if ($translate) {
				if (array_key_exists($id_field, $sons_arr)) {
					$result[$id_user][$id_field] = isset($sons_arr[$id_field][$value]) ? $sons_arr[$id_field][$value] : '';
				} elseif (in_array($id_field, $yesno_fields)) {
					$yntrans = Lang::t('_NOT_ASSIGNED', 'field');
					switch ($value) {
						case 1 : $yntrans = Lang::t('_YES', 'standard'); break;
						case 2 : $yntrans = Lang::t('_NO', 'standard'); break;
					}
					$result[$id_user][$id_field] = $yntrans;
				} else {
					$result[$id_user][$id_field] = $value;
				}
			} else {
				$result[$id_user][$id_field] = $value;
			}
		}
		return $result;
	}


	/**
	 * find the id of the entity that have the given value for the given field
	 * @param int 		$id_field 			the id of the field
	 * @param mixed 	$value_to_check 	the value to check
	 *
	 * @return array with the id of the entity
	 **/
	function getOwnerData($id_field, $value_to_check) {

		$query = "
		SELECT id_user
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_field = '".$id_field."' AND obj_entry = '".$value_to_check."'";
		$rs = sql_query( $query );
		$result = array();
		while( list($owner) = sql_fetch_row($rs) )
			$result[] = $owner;
		return $result;
	}
	
	/**
	 * find the id of the entity that have the given value for the given field
	 * @param int 		$id_field 			the id of the field
	 * @param mixed 	$value_to_check 	the value to check
	 *
	 * @return array with the id of the entity
	 **/
	function getOwnerDataWithLike($id_field, $value_to_check) {

		$query = "
		SELECT id_user
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_field = '".$id_field."' AND user_entry LIKE '%".$value_to_check."%'";
		$rs = sql_query( $query );
		$result = array();
		while( list($owner) = sql_fetch_row($rs) )
			$result[] = $owner;
		return $result;
	}

	/**
	 * return info about a field
	 * @param int $type_field the type of the field
	 * @return array with 0 => type_file 1 => type_class
	 **/
	 function getBaseFieldInfo( $type_field ) {
		 $arr_result = sql_fetch_row( sql_query(
		 				"SELECT type_file, type_class "
						." FROM ".$this->getTypeFieldTable()
						." WHERE type_field = '".$type_field."'"));
		return $arr_result;
	 }


	/**
	 * @param int $id_st idst to be associated to the user
	 * @param int $id_field id of the field to get
	 * @param bool $freeze TRUE to get static text, false to get input control
 	 * @return html with the form code for play a set of fields
	 **/
	function showFieldForUser($idst_user, $id_field) {

		$query = "SELECT tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				." WHERE ft.type_field = tft.type_field"
				."   AND ft.id_field = '".$id_field."'"
				." ORDER BY ft.sequence";

		$rs = sql_query($query);
		
		if( sql_num_rows($rs) < 1 )
			return 'NULL';
		list( $type_file, $type_class ) = sql_fetch_row( $rs );
		require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
		$quest_obj = eval("return new $type_class( $id_field );");
		if( $this->field_entry_table !== FALSE )
			$quest_obj->setFieldEntryTable($this->field_entry_table);

		$quest_obj->setMainTable($this->getFieldTable());
		if (!$this->getUseMultiLang()) {
			return $quest_obj->show( $idst_user );
		}
		else {
			return $quest_obj->showInLang( $idst_user, getLanguage() );
		}
	}


	 /**
	 * @param int 		$idst_user 	idst to be associated to the user
	 * @param array 	$arr_field 	optional you can filter the field to show
 	 * @return html with the info about yhe field for the user passed
	 **/
	 function showAllFieldForUser($idst_user, $arr_field = false) {

		$acl =& Docebo::user()->getACL();
		$arr_idst = $acl->getUserGroupsST($idst_user);

		$acl_man =& $acl->getAclManager();
		$tmp = $acl_man->getGroup( false, '/oc_0' );
		$arr_idst[] = $tmp[0];
		$tmp = $acl_man->getGroup( false, '/ocd_0' );
		$arr_idst[] = $tmp[0];

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class, gft.mandatory"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field = gft.id_field"
				."   AND gft.idst IN ('".implode("','", $arr_idst)."')"
				.( $arr_field !== false && is_array($arr_field) && !empty($arr_field)
					? " AND ft.id_field IN ('".implode("','", $arr_field)."') "
					: "" )
				." GROUP BY ft.id_field "
				." ORDER BY ft.sequence, gft.id_field";

		$play_txt = '';
		$re_fields = sql_query($query);
		if(!sql_num_rows($re_fields)) return '';
		
		while(list($id_field, $type_field, $type_file, $type_class, $mandatory) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			if (!$this->getUseMultiLang()) {
				$play_txt .= $quest_obj->show( $idst_user );
			}
			else {
				$play_txt .= $quest_obj->showInLang( $idst_user, getLanguage() );
			}
		}
		return $play_txt;
	 }


	function getAllFieldValue($id_field) {
		$query = "SELECT ft.id_field, tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				." WHERE ft.type_field = tft.type_field"
				." AND ft.id_field = '".$id_field."'"
				." ORDER BY ft.sequence";

		$res=array();
		$rs = sql_query($query);
		if(!$rs)
			return $res;
		
		if( sql_num_rows($rs) < 1 ){
			return $res;
		}
		list( $id_field, $type_file, $type_class ) = sql_fetch_row( $rs );
			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
		return $quest_obj->getSon();

	}


	/**
	 * @param array $idst_user_arr idst to be associated to the user
	 * @param int $id_field_arr id of the field to get
 	 * @return array with values for the specified fields for each user
 	 * 	array[user_idst][field_idfield]=field_value
 	 * 	you can find an usage example in /lib/lib.usernotifier.php
	 **/
	function showFieldForUserArr($idst_user_arr, $id_field_arr) {

		$query = "SELECT ft.id_field, tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				." WHERE ft.type_field = tft.type_field"
				."   AND ft.id_field IN (".implode(",", $id_field_arr).")"
				." ORDER BY ft.sequence";

		$res=array();

		$rs = sql_query($query);
		if($rs == false)
			return 'NULL';
		
		if( sql_num_rows($rs) < 1 )
			return 'NULL';

		while(list( $id_field, $type_file, $type_class ) = sql_fetch_row( $rs )) {
			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());

			$lang =getLanguage();
			foreach($idst_user_arr as $idst_user) {
				if (!$this->getUseMultiLang()) {
					$res[$idst_user][$id_field]=$quest_obj->show( $idst_user );
				}
				else {
					$res[$idst_user][$id_field]=$quest_obj->showInLang( $idst_user, $lang );
				}
			}
		}

		return $res;
	}

	/**
	 * @param array $idst_user_arr idst to be associated to the user
	 * @param int $id_field_arr id of the field to get
 	 * @return array with values for the specified fields for each user
 	 * 	array[user_idst][field_idfield]=field_value
 	 * 	you can find an usage example in /lib/lib.usernotifier.php
	 **/
	function fieldValue($id_field, $idst_user_arr) {

		$query = "SELECT ft.id_field, tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				." WHERE ft.type_field = tft.type_field"
				."   AND ft.id_field = '".$id_field."'"
				." ORDER BY ft.sequence";

		$res=array();

		$rs = sql_query($query);
		if($rs == false)
			return 'NULL';
		
		if( sql_num_rows($rs) < 1 )
			return 'NULL';

		list( $id_field, $type_file, $type_class ) = sql_fetch_row( $rs );
			
			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
		$quest_obj = new $type_class( $id_field );
		if( $this->field_entry_table !== FALSE )
			$quest_obj->setFieldEntryTable($this->field_entry_table);

		$quest_obj->setMainTable($this->getFieldTable());

		$lang =getLanguage();
		foreach($idst_user_arr as $idst_user) {
			if (!$this->getUseMultiLang()) {
				$res[$idst_user]=$quest_obj->show( $idst_user );
			}
			else {
				$res[$idst_user]=$quest_obj->showInLang( $idst_user, $lang );
			}
		}

		return $res;
	}


	/**
	 * @param int $id_st idst to be associated to the user
	 * @param int $id_field id of the field to get
	 * @param bool $freeze TRUE to get static text, false to get input control
	 * @param bool $mandatory Specified if the field is a mandatory one or not.
 	 * @return html with the form code for play a set of fields
	 **/
	function playFieldForUser($idst_user, $id_field, $freeze, $mandatory=FALSE) {

		$query = "SELECT tft.type_file, tft.type_class"
				."  FROM ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft"
				." WHERE ft.type_field = tft.type_field"
				."   AND ft.id_field = '".$id_field."'"
				." ORDER BY ft.sequence";

		$rs = sql_query($query);
		
		if( sql_num_rows($rs) < 1 )
			return 'NULL';
		list( $type_file, $type_class ) = sql_fetch_row( $rs );
		require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
		$quest_obj = eval("return new $type_class( $id_field );");
		if( $this->field_entry_table !== FALSE )
			$quest_obj->setFieldEntryTable($this->field_entry_table);

		$quest_obj->setMainTable($this->getFieldTable());
		if (!$this->getUseMultiLang()) {
			return $quest_obj->play( $idst_user, $freeze, $mandatory );
		}
		else {
			return $quest_obj->multiLangPlay( $idst_user, $freeze, $mandatory );
		}
	}


	/**
	 * @param int $id_st idst to be associated to the user
	 * @param array $arr_idst (optional) array of idst of groups
	 *					if this parameter is skipped the groups will be taken
	 *					from $idst_user
 	 * @return html with the form code for play a set of fields
	 **/
	function playFields($idst_obj = -1, $arr_idst = FALSE, $freeze = FALSE, $add_root = TRUE, $useraccess = FALSE, $separate_output = FALSE, $check_precompiled = FALSE ) {

		$acl =& Docebo::user()->getACL();

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class, 'false' as mandatory"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft JOIN ".$this->getFieldLangTable(). " AS flt )"
				." WHERE flt.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."	 AND ft.id_field = flt.id_field"
				."   AND ft.area_code = '".$this->getFieldArea()."'";

		$query .=" GROUP BY ft.id_field "
				." ORDER BY ft.sequence";
                
		$play_txt = array();
		$re_fields = sql_query($query);
		
		$precompiled = FALSE;
		if ($check_precompiled > 0) {
			$precompiled = $this->getInheritedAdminFields($check_precompiled);
		}

		if(!sql_num_rows($re_fields)) return '';
		while(list($id_field, $type_field, $type_file, $type_class, $mandatory) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/customfield/'.$type_file);
			$field_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$field_obj->setFieldEntryTable($this->field_entry_table);

			$field_obj->setMainTable($this->getFieldTable());
			if (!$this->getUseMultiLang()) {
				$precompiled_value =  is_array($precompiled) && isset($precompiled[$id_field]) ? $precompiled[$id_field] : NULL;
				$play_txt[$id_field] = $field_obj->play( $idst_obj, $freeze, $this->_mandatoryField($mandatory), false, $precompiled_value);
			}
			else {
				$precompiled_value =  is_array($precompiled) && isset($precompiled[$id_field]) ? $precompiled[$id_field] : NULL;
				$play_txt[$id_field] = $field_obj->multiLangPlay( $idst_obj, $freeze, $this->_mandatoryField($mandatory), false, $precompiled_value);
			}
		}
		
		return $separate_output ? $play_txt : implode("", array_values($play_txt));
	}

        
	/**
	 * @param int $id_st idst to be associated to the user
	 * @param array $arr_idst (optional) array of idst of groups
	 *					if this parameter is skipped the groups will be taken
	 *					from $idst_user
 	 * @return html with the form code for play a set of fields
	 **/
	function playFieldsFlat($idst_obj = -1 ) {

		$acl =& Docebo::user()->getACL();

		$query = "SELECT ft.id_field, ft.code, ft.type_field, tft.type_file, tft.type_class, flt.translation as name"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft JOIN ".$this->getFieldLangTable(). " AS flt )"
				." WHERE flt.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."	 AND ft.id_field = flt.id_field"
				."   AND ft.area_code = '".$this->getFieldArea()."'";

		$query .=" GROUP BY ft.id_field "
				." ORDER BY ft.sequence";

		$play_txt = array();
		$re_fields = sql_query($query);
		
		$precompiled = FALSE;
		if ($check_precompiled > 0) {
			$precompiled = $this->getInheritedAdminFields($check_precompiled);
		}

		if(!sql_num_rows($re_fields)) return '';
                
                $ret = array();
                
		while(list($id_field, $code, $type_field, $type_file, $type_class, $name) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/customfield/'.$type_file);
			$field_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$field_obj->setFieldEntryTable($this->field_entry_table);

			$field_obj->setMainTable($this->getFieldTable());
			$ret[$id_field] = array ("id" => $id_field, "code" => $code, "name" =>$name , "code_value" => $field_obj->playFlat( $idst_obj, true) , "value" => $field_obj->playFlat( $idst_obj) );
		}
		
                return $ret;
		//return $separate_output ? $play_txt : implode("", array_values($play_txt));
	}

	/**
	 * @param array $idst_user_arr idst to be associated to the user
	 * @param int $id_field_arr id of the field to get
 	 * @return array with values for the specified fields for each user
 	 * 	array[user_idst][field_idfield]=field_value
 	 * 	you can find an usage example in /lib/lib.usernotifier.php
	 **/
	function hiddenFieldForUserArr($idst_user, $arr_idst = FALSE, $freeze = FALSE, $add_root = TRUE, $useraccess = FALSE) {

		$acl =& Docebo::user()->getACL();
		if( $arr_idst === FALSE )
			$arr_idst = $acl->getUserGroupsST($idst_user);

		if( $add_root ) {
			$acl_man =& $acl->getAclManager();
			$tmp = $acl_man->getGroup( false, '/oc_0' );
			$arr_idst[] = $tmp[0];
			$tmp = $acl_man->getGroup( false, '/ocd_0' );
			$arr_idst[] = $tmp[0];
		}

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class, gft.mandatory"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field = gft.id_field"
				."   AND gft.idst IN ('".implode("','", $arr_idst)."')";

		if($useraccess !== 'false' && is_array($useraccess)) {
			$query .= " AND ( ";
			$first = true;
			foreach($useraccess AS $k => $v) {
				if(!$first) $query .= " OR ";
				else $first = false;
				$query .= " gft.useraccess = '".$v."' ";
			}
			$query .=" ) ";
		}
		$query .=" GROUP BY ft.id_field "
				." ORDER BY ft.sequence, gft.idst, gft.id_field";

		$play_txt = '';
		$re_fields = sql_query($query);
		


		while(list($id_field, $type_field, $type_file, $type_class, $mandatory) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());

			$play_txt .= $quest_obj->get_hidden_filled( false, false );




		}

		return $play_txt;
	}
	
	/**
	 * @param int $id_st idst to be associated to the user
	 * @param array $arr_idst (optional) array of idst of groups
	 *					if this parameter is skipped the groups will be taken
	 *					from $idst_user
 	 * @return TRUE if all the mandatory field is filled and all field is valid, an array with the error messsage
	 **/
	function isFilledFieldsForUser($idst_user, $arr_idst = FALSE ) {

		$acl =& Docebo::user()->getACL();
		if( $arr_idst === FALSE )
			$arr_idst = $acl->getUserGroupsST($idst_user);
		$acl_man =& $acl->getAclManager();
		$tmp = $acl_man->getGroup( false, '/oc_0' );
		$arr_idst[] = $tmp[0];
		$tmp = $acl_man->getGroup( false, '/ocd_0' );
		$arr_idst[] = $tmp[0];

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class, gft.mandatory"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field = gft.id_field"
				."   AND gft.idst IN ('".implode("','", $arr_idst)."')"
				." GROUP BY ft.id_field ";

		

		$error_message = array();

		$mandatory_filled 	= true;
		$field_valid 		= true;
		$re_fields 			= sql_query($query);
		while(list($id_field, $type_field, $type_file, $type_class, $is_mandatory) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = new $type_class( $id_field );

			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());

			if(!$quest_obj->isValid( $idst_user )) {

				$error_text = $quest_obj->getLastError();
				if($error_text !== false) $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), $error_text);
				else $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_FIELD_VALUE_NOT_VALID', 'field', 'framework'));

			} elseif($is_mandatory == 'true' && !$quest_obj->isFilled( $idst_user ) ) {

				$error_text = $quest_obj->getLastError();
				if($error_text !== false) $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), $error_text);
				else $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_SOME_MANDATORY_EMPTY', 'register', 'framework'));
			}
		}
		if(empty($error_message)) return true;
		return $error_message;
	}

	/**
	 * @param int $id_st idst to be associated to the user
	 * @param array $arr_idst (optional) array of idst of groups
	 *					if this parameter is skipped the groups will be taken
	 *					from $idst_obj
 	 * @return TRUE if success, FALSE otherwise
	 **/
	function storeFieldsForObj($idst_obj ) {

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class, 'false' as mandatory"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft JOIN ".$this->getFieldLangTable(). " AS flt )"
				." WHERE flt.lang_code = '".getLanguage()."'"
				." AND ft.type_field = tft.type_field"
				." AND ft.id_field = flt.id_field"
				." AND ft.area_code = '".$this->getFieldArea()."'"
				." GROUP BY ft.id_field ";
                                
		$save_result = true;
		$re_fields = sql_query($query);
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/customfield/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE ){
				$quest_obj->setFieldEntryTable($this->field_entry_table);
			}
				$quest_obj->setMainTable($this->getFieldTable());
                                
                                $error_message = array();
                                
                                if(!$quest_obj->isValid( $idst_obj )) {

                                        $error_text = $quest_obj->getLastError();
                                        if($error_text !== false) $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), $error_text);
                                        else $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_FIELD_VALUE_NOT_VALID', 'field', 'framework'));

                                } elseif($is_mandatory == 'true' && !$quest_obj->isFilled( $idst_obj ) ) {

                                        $error_text = $quest_obj->getLastError();
                                        if($error_text !== false) $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), $error_text);
                                        else $error_message[] = str_replace('[field_name]', $quest_obj->getFieldName(), Lang::t('_SOME_MANDATORY_EMPTY', 'register', 'framework'));
                                }
                                if (!empty($error_message)){
                                        return $error_message[0];
                                }
                        
				if (!$this->getUseMultiLang()) {
					$save_result &= $quest_obj->store( $idst_obj, $this->getFieldArea() );
				}
				else {
					$save_result &= $quest_obj->multiLangStore( $idst_obj, $this->getFieldArea() );
				}
		}
		return $save_result;
	}

	/**
	 * @param int $id_st idst to be associated to the user
	 * @param array array of fields to be set idfield=>value
	 * @param bool $is_id if true will consider the passed data as the field id;
	 *                    else the value is taken and reconverted to the id
 	 * @return TRUE if success, FALSE otherwise
	 **/
	function storeDirectFieldsForUser($idst_user, $arr_fields, $is_id = FALSE, $int_userid=TRUE) {
		
		//return is_numeric($idst_user) && (int)$idst_user > 0 ? $this->storeDirectFieldsForUsers((int)$idst_user, $arr_fields, $is_id, $int_userid) : FALSE;

		$acl = Docebo::user()->getACL();

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field IN ('".implode("','", array_keys($arr_fields))."')"
				." GROUP BY ft.id_field ";

		$save_result = true;
		$re_fields = sql_query($query);
		if( $re_fields === FALSE ) {
			return FALSE;
		}
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			if (!$this->getUseMultiLang()) {
				$save_result &= $quest_obj->storeDirect( $idst_user, $arr_fields[$id_field], $is_id, FALSE, $int_userid );
			}
			else {
				$save_result &= $quest_obj->multiLangStoreDirect( $idst_user, $arr_fields[$id_field], $is_id, FALSE, $int_userid );
			}
		}
		return $save_result;
	}


	/**
	 * @param int/array $idst_users list of idst to be associated to the users
	 * @param array array of fields to be set idfield=>value
	 * @param bool $is_id if true will consider the passed data as the field id;
	 *                    else the value is taken and reconverted to the id
 	 * @return TRUE if success, FALSE otherwise
	 **/
	function storeDirectFieldsForUsers($idst_users, $arr_fields, $is_id = FALSE, $int_userid=TRUE) {

		if (is_numeric($idst_users)) $idst_users = array($idst_users);
		if (!is_array($idst_users)) return false;
		if (empty($idst_users)) return true;

		$acl =& Docebo::user()->getACL();

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field IN ('".implode("','", array_keys($arr_fields))."')"
				." GROUP BY ft.id_field ";

		$save_result = true;
		$re_fields = sql_query($query);
		if( $re_fields === FALSE ) {
			return FALSE;
		}
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once(_adm_.'/modules/field/'.$type_file);
			$quest_obj = new $type_class( $id_field );
			if( $this->field_entry_table !== FALSE ) {
				$quest_obj->setFieldEntryTable($this->field_entry_table);
			}
			$quest_obj->setMainTable($this->getFieldTable());
			if (!$this->getUseMultiLang()) {
				$save_result &= $quest_obj->storeDirectMultiple( $idst_users, $arr_fields[$id_field], $is_id, FALSE, $int_userid );
			} else {
				$save_result &= $quest_obj->multiLangStoreDirectMultiple( $idst_users, $arr_fields[$id_field], $is_id, FALSE, $int_userid );
			}
		}
		return $save_result;
	}



	/**
	 * @param array $arr_field
	 * @param array $custom_mandatory (optional)
	 *
 	 * @return html with the form code for play a set of specified fields
	 **/
	function playSpecFields($arr_field, $custom_mandatory=FALSE, $user_id=FALSE) {

		$acl =& Docebo::user()->getACL();

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
//				."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
//				."   AND ft.id_field = gft.id_field"
				."   AND ft.id_field IN ('".implode("','", $arr_field)."')";

		$query .=" GROUP BY ft.id_field ";
//				." ORDER BY ft.sequence";

		if (($user_id === FALSE) || (empty($user_id))) {
			$user_id=-1;
		}

		$play_txt = '';
		$play_arr=array();
		$re_fields = sql_query($query);
		
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			if ((isset($custom_mandatory[$id_field])) && ($custom_mandatory[$id_field]))
				$mandatory=true;
			else
				$mandatory=false;


			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			if (!$this->getUseMultiLang()) {
				$play_arr[$id_field] = $quest_obj->play( $user_id, false, $this->_mandatoryField($mandatory) );
			}
			else {
				$play_arr[$id_field] = $quest_obj->multiLangPlay( $user_id, false, $this->_mandatoryField($mandatory) );
			}
		}

		// This way we get it in the order passed in the $arr_field array:
		foreach($arr_field as $key=>$val) {
			if (isset($play_arr[$val]))
				$play_txt.=$play_arr[$val];
		}

		return $play_txt;
	}


	function playFilters($arr_field, $values, $field_prefix=FALSE) {
		$res="";

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field IN ('".implode("','", $arr_field)."')";
		$query .=" GROUP BY ft.id_field ";

		$re_fields = sql_query($query);
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			$value=(isset($values[$id_field]) ? $values[$id_field] : FALSE);
			$res.=$quest_obj->play_filter($id_field, $value, FALSE, $field_prefix);
		}

		return $res;
	}


	/**
	 * @param array $arr_field array of field id that are mandatory
 	 *
 	 * @return TRUE if all the mandatory field is filled, FALSE otherwise
	 **/
	function isFilledSpecFields($arr_field) {

		$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field IN ('".implode("','", $arr_field)."')"
				." GROUP BY ft.id_field ";

		$save_result = true;
		$re_fields = sql_query($query);
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			$save_result &= $quest_obj->isFilled( -1 );
		}
		return $save_result;
	}


	/**
	 * @param array $arr_field
	 * @param array $grab_form (optional)
	 * @param bool $dropdown_val (optional). If true will get the value of a dropdown item instead of its id.
 	 *
 	 * @return array with the filled value of the specified fields
	 **/
	function getFilledSpecVal($arr_field, $grab_from=false, $dropdown_val=false) {

		if ($grab_from === FALSE)
			$grab_from=$_POST;

		$query = "SELECT ft.id_field, ft.translation, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				."   AND ft.id_field IN ('".implode("','", $arr_field)."')"
				." GROUP BY ft.id_field ";

		$filled_val=array();
		$re_fields = sql_query($query);
		while(list($id_field, $translation, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			$filled_val[$id_field]["description"]=$translation;

			if ($type_field == "dropdown")
				$filled_val[$id_field]["value"]=$quest_obj->getFilledVal( $grab_from, $dropdown_val );
			else
				$filled_val[$id_field]["value"]=$quest_obj->getFilledVal( $grab_from );
		}

		return $filled_val;
	}


	/**
	 * @param int $id_field
	 * @param array $associate_owner if true the owner of the data is associated
 	 *
 	 * @return array with the stored value of the specific field
	 **/
	function getAllStoredValue($id_field, $associate_owner = false) {

		$query = "
		SELECT DISTINCT user_entry ".( $associate_owner === true ? ", id_user" : '' )."
		FROM ".$this->getFieldEntryTable() ."
		WHERE id_field = '".$id_field."'";
		$rs = sql_query( $query );

		$result = array();
		while($data = sql_fetch_row($rs)){

			if($associate_owner === true)  $result[$data[1]] = $data[0];
			else $result[] = $data[0];
		}
		return $result;
	}


	/**
	 * @param int $id_field id of the field to be associated to $id_st
	 * @param int $id_st idst to be associated to field
 	 * @return TRUE if success, FALSE otherwise
	 **/
	function addFieldToGroup($id_field, $idst, $mandatory = 'false', $useraccess = 'readonly', $user_inherit = '0' ) {
		$query = "SELECT idst FROM ".$this->getGroupFieldsTable()
				." WHERE idst = '".$idst."' AND id_field = '".$id_field."'";
		$rs = sql_query( $query );
		if( sql_num_rows( $rs ) > 0 ) {
			$query = "UPDATE ".$this->getGroupFieldsTable()
					." SET idst = '".(int)$idst."',"
					."     id_field = '".(int)$id_field."',"
					."     mandatory = '".$mandatory."',"
					."     useraccess = '".$useraccess."', "
					."     user_inherit = '".((int)$user_inherit > 0 ? '1' : '0')."' "
					." WHERE idst = '".$idst."' AND id_field = '".$id_field."'";
		} else {
			$query = "INSERT INTO ".$this->getGroupFieldsTable()
					." (idst, id_field, mandatory, useraccess, user_inherit) "
					." VALUES ('".(int)$idst."','".(int)$id_field."',"
					."'".$mandatory."','".$useraccess."', '".((int)$user_inherit > 0 ? '1' : '0')."')";
		}
		return sql_query( $query );
	}

	/**
	 * @param int $id_field id of the field to be removed from $id_st
	 * @param int $id_st idst to be removed to field
 	 * @return TRUE if success, FALSE otherwise
	 **/
	function removeFieldFromGroup($id_field, $idst) {
		$query = "DELETE FROM ".$this->getGroupFieldsTable()
				." WHERE idst = '".(int)$idst."'"
				."   AND id_field = '".(int)$id_field."'";
		return sql_query( $query );
	}

	function quickRemoveUserEntry($id_obj) {
		
		$query_del = "DELETE FROM %adm_customfield_entry 
			WHERE id_obj = '".(int)$id_obj."'";
		
		return sql_query($query_del);
	}
	
	/**
	 * @param int 	$idst_user 	the user
	 * @param int 	$id_group 	cast the delete action only to the field of this group
	 * @param array $arr_fields cast the delete action only to the field specified
 	 * @return TRUE if success, FALSE otherwise
	 **/
	function removeUserEntry($idst_user, $id_group = FALSE, $arr_field = FALSE) {

		$save_result = true;
		$arr_idst = array();
		if( $arr_field !== FALSE ) {

			$to_remove =& $arr_field;
		} elseif( $id_group !== FALSE ) {

			$acl =& Docebo::user()->getACL();
			$allgroup_idst = $acl->getUserGroupsST($idst_user);
			// Leave the passed group
			$inc_group = array_search($id_group, $allgroup_idst);
			unset($allgroup_idst[$inc_group]);

			if(!empty($allgroup_idst)) {
				$query = "SELECT gft.id_field "
						."  FROM ".$this->getGroupFieldsTable(). " AS gft"
						." WHERE gft.idst IN ('".implode("','", $allgroup_idst)."')";
				$rs = sql_query( $query );$result = array();
				while( list($id) = sql_fetch_row($rs) )
					$all_field[$id] = $id;
			}
			$query = "SELECT gft.id_field "
					."  FROM ".$this->getGroupFieldsTable(). " AS gft"
					." WHERE gft.idst = '".$id_group."'";
			$rs = sql_query( $query );
			$to_remove = array();
			while( list($id) = sql_fetch_row($rs) ) {
				if(!isset($all_field[$id])) $to_remove[] = $id;
			}
		}
		
		
		if(empty($to_remove)) { // no group or fields specified, so we remove all..
			//return $save_result;

			$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
				."  FROM ( ".$this->getFieldTable() ." AS ft"
				."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
				." WHERE ft.lang_code = '".getLanguage()."'"
				."	 AND ft.type_field = tft.type_field"
				." GROUP BY ft.id_field ";
		}
		else {	// remove specific fields
		
			$query = "SELECT ft.id_field, ft.type_field, tft.type_file, tft.type_class"
					."  FROM ( ".$this->getFieldTable() ." AS ft"
					."  JOIN ".$this->getTypeFieldTable(). " AS tft )"
					."  JOIN ".$this->getGroupFieldsTable(). " AS gft"
					." WHERE ft.lang_code = '".getLanguage()."'"
					."	 AND ft.type_field = tft.type_field"
					."   AND ft.id_field = gft.id_field"
					."   AND gft.idst IN ('".implode("','", $to_remove)."')"
					." GROUP BY ft.id_field ";
		}

		$re_fields = sql_query($query);
		while(list($id_field, $type_field, $type_file, $type_class) = sql_fetch_row($re_fields)) {

			require_once($GLOBALS['where_framework'].'/modules/field/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_field );");
			if( $this->field_entry_table !== FALSE )
				$quest_obj->setFieldEntryTable($this->field_entry_table);

			$quest_obj->setMainTable($this->getFieldTable());
			$save_result &= $quest_obj->deleteUserEntry( $idst_user );
		}
		return $save_result;
	}


	/**
	 * Find wich users entries matches with search information
	 * @author Giovanni Derks
	 *
	 * @param array $fields list of id_field values
	 * @param string $method "OR" or "AND".
	 * @param array $like array($id_field => [off, both, start, end])
	 * @param array $search array($id_field => $what_to_search)
	 * @param bool $return_raw if TRUE then will return the raw array
	 *
	 * @return array list of user idst found (if $return_raw is FALSE)
	 */
	function quickSearchUsersFromEntry($fields, $method, $like, $search, $return_raw=FALSE) {
		$res=array();


		if ((Get::sett('do_debug') == 'on') && (count($fields) != count($search))) {
			echo "<b>Warning</b>: (lib.field.php) ";
			echo "Please make sure that the search array have the same size of the fields one.<br />";
		}

		// -------------------------

		$qtxt ="SELECT * FROM ".$this->getFieldEntryTable()." ";
		$qtxt.="WHERE id_field IN (".implode(",", $fields).") AND (";

		$where_arr=array();
		foreach($fields as $id_field) {

			$where ="";

			if (isset($search[$id_field])) {

				$where.="(id_field='".$id_field."' AND user_entry ";

				$search_val=$search[$id_field];

				if ((!isset($like[$id_field])) || ($like[$id_field] == "off")) {
					$where.="='".$search_val."'";
				}
				else if ($like[$id_field] == "both") {
					$where.=" LIKE '%".$search_val."%'";
				}
				else if ($like[$id_field] == "start") {
					$where.=" LIKE '%".$search_val."'";
				}
				else if ($like[$id_field] == "end") {
					$where.=" LIKE '".$search_val."%'";
				}

				$where.=")";

				$where_arr[]=$where;
			}
		}

		$qtxt.=implode(" OR ", $where_arr);

		$qtxt.=")";

		// -------------------------

		$q=sql_query($qtxt);

		$raw_res=array();
		$raw_res["field"]=array();
		$raw_res["user"]=array();
		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {

				$id_field=$row["id_field"];
				$id_user=$row["id_user"];

				// ----------------------------------------------------------

				if (!isset($raw_res[$id_field]))
					$raw_res["field"][$id_field]=array();

				if (!in_array($id_user, $raw_res["field"][$id_field]))
					$raw_res["field"][$id_field][]=$id_user;

				// ----------------------------------------------------------

				if (!isset($raw_res["user"][$id_user]))
					$raw_res["user"][$id_user]=array();

				if (!in_array($id_field, $raw_res["user"][$id_user]))
					$raw_res["user"][$id_user][]=$id_field;

				// ----------------------------------------------------------

				if (($method == "OR") && (!in_array($row["id_user"], $res)))
					$res[]=$row["id_user"];

			}
		}


		if ($return_raw) {
			return $raw_res;
		}
		else if ($method == "AND") {

			$tot=count($fields);
			foreach($raw_res["user"] as $user_id=>$field_arr) {

				$tot_found=count($field_arr);
				if (($tot_found > 0) && ($tot_found == $tot)) {
					$res[]=$user_id;
				}
			}

		}


		return $res;
	}
	
	function getFieldIdCommonFromTranslation($translation) {
		
		$query = "SELECT id_field" .
			" FROM ".$this->getFieldTable()."" .
			" WHERE translation LIKE '".$translation."'";
  
		list($res) = sql_fetch_row(sql_query($query));
  
  		return $res;
	}

	function getFieldTypesList() {
		$db = DbConn::getInstance();
		$query = "SELECT * FROM ".$this->getTypeFieldTable();

		if(!$rs = $db->query($query))  {
			$false_var = NULL;
			return $false_var;
		}

		$output = array();
		while ($row = $db->fetch_assoc( $rs )) $output[] = $row;
		return $output;
	}

	function getFieldTypeById($field_id) {
		$db = DbConn::getInstance();
		$query = "SELECT type_field FROM ".$this->getFieldTable()." WHERE id_field='$field_id'";

		if(!$rs = $db->query($query))  {
			$false_var = NULL;
			return $false_var;
		}

		$output = false;
		$temp = $db->fetch_row( $rs );
		if (count($temp)>0) $output=$temp[0];
		return $output;
	}

	//----------------------------------------------------------------------------
	function checkUserMandatoryFields($id_user = false, $only_accessible = false) {
		$id_user = $id_user ? (int)$id_user : Docebo::user()->getIdSt();
		$acl = new DoceboACL();
		$user_groups = $acl->getUserGroupsST($id_user);
		$output = true;

		if (!empty($user_groups)) {
			//extract mandatory fields and checks if there are any fields with null value (not compiled)
			$query = "SELECT ft.id_field, gft.useraccess, fet.user_entry "
					." FROM (".$this->getFieldTable()." AS ft "
					." JOIN ".$this->getGroupFieldsTable()." AS gft "
					." ON (ft.id_field = gft.id_field AND ft.lang_code = '".getLanguage()."')) "
					." LEFT JOIN ".$this->getFieldEntryTable()." AS fet "
					." ON (fet.id_field = ft.id_field AND fet.id_user = ".(int)$id_user.") "
					." WHERE gft.mandatory = 1 "
					.($only_accessible ? " AND gft.useraccess = 'readwrite'" : '')
					." AND gft.idst IN ('".implode("','", $user_groups)."')";
			$res = sql_query($query);

			if ($res) {
				if(sql_num_rows($res) == 0) return true;
				while ($obj = sql_fetch_object($res)) {
					if (!$obj->user_entry) {
						$output = false;
						break;
					}
				}
			}
		}

		return $output;
	}



	function getUserMandatoryFields($id_user) {
		$acl = new DoceboACL();
		$user_groups = $acl->getUserGroupsST($id_user);
		$output = array();

		if (!empty($user_groups)) {
			$query = "SELECT ft.id_field, ft.translation, ft.type_field, gft.useraccess, fet.user_entry "
					." FROM (".$this->getFieldTable()." AS ft "
					." JOIN ".$this->getGroupFieldsTable()." AS gft "
					." JOIN ".$this->getTypeFieldTable()." AS ftt "
					." ON (ft.id_field = gft.id_field AND ft.lang_code = '".getLanguage()."' AND ft.type_field = ftt.type_field)) "
					." LEFT JOIN ".$this->getFieldEntryTable()." AS fet "
					." ON (fet.id_field = ft.id_field AND fet.id_user = ".(int)$id_user.") "
					." WHERE gft.idst IN ('".implode("','", $user_groups)."') "
					." AND gft.mandatory = 1 "
					." ORDER BY ft.sequence";
			$res = sql_query($query);

			if ($res) {
				while ($obj = sql_fetch_object($res)) {
					$output[$obj->id_field] = array(
						'translation' => $obj->translation,
						'type_field' => $obj->type_field,
						'useraccess' => $obj->useraccess,
						'user_entry' => $obj->user_entry
					);
				}
			}
		}

		return $output;
	}



	function getFieldsByType($type) {
		$output = false;
		$query = "SELECT id_field FROM ".$this->getFieldTable()." WHERE type_field = '".$type."'";
		$res = sql_query($query);
		if ($res) {
			$output = array();
			while (list($id_field) = sql_fetch_row($res)) {
				$output[] = $id_field;
			}
		}
		return $output;
	}



	function getInheritedAdminFields($id_admin) {
		if ($id_admin <= 0) return false;

		$output = array();

		//retrieve admin's groups and read groups associated fields
		$groups = array();
		$query = "SELECT gm.idst FROM %adm_group_members AS gm JOIN %adm_group AS g ON (gm.idst = g.idst) "
			." WHERE (g.groupid LIKE '/oc\_%' OR g.groupid LIKE '/ocd\_%' ) AND gm.idstMember = ".(int)$id_admin;
		$res = sql_query($query);
		while (list($id_group) = sql_fetch_row($res)) {
			$groups[] = (int)$id_group;
		}

		if (!empty($groups)) {
			$fields = array();
			$query = "SELECT * FROM ".$this->getGroupFieldsTable()." "
				." WHERE idst IN (".implode(",", $groups).") "
				." AND user_inherit = 1";
			$res = sql_query($query);
			while ($obj = sql_fetch_object($res)) {
				$fields[] = $obj->id_field;
			}

			if (!empty($fields)) {
				$output = $this->getUserFieldEntryData($id_admin, $fields);
			}
		}

		return $output;
	}
    
    // get value of custom field type ORG_CHART 
    function getValueCustomOrg($field_name, $node_name){
        
       $node_name_array = explode('/',$node_name);
       $node_name = end($node_name_array);
       $query = "select %adm_customfield_entry.obj_entry, %adm_customfield.type_field, %adm_customfield_lang.id_field
                  from %adm_customfield_entry, %adm_customfield_lang, %adm_org_chart, %adm_customfield 
                  where
                  %adm_customfield_lang.lang_code = '".getLanguage()."' and %adm_customfield_lang.translation = '".$field_name."' and
                  %adm_customfield_lang.id_field = %adm_customfield_entry.id_field and
                  %adm_org_chart.lang_code = 'italian' and %adm_org_chart.translation = '".$node_name."' and                   
                  %adm_customfield_entry.id_obj= %adm_org_chart.id_dir and
                  %adm_customfield.id_field = %adm_customfield_lang.id_field";
       
        if(!$rs = sql_query( $query )) return false;

        list($obj_entry, $type_field, $id_field) = sql_fetch_row($rs);
        if($type_field=='textfield'){
            return $obj_entry;       
        }
        if($type_field=='dropdown'){
            return $this->getCheckValueCustom( $id_field, $obj_entry);       
        }
        
        return '';
        
    }
    
    
   function getValueCustomCourse($id_corso, $id_field){
        
         $query = 'select 
                    obj_entry, type_field, %adm_customfield_entry.id_field from 
                        %adm_customfield_entry, %adm_customfield
                    where %adm_customfield.id_field = %adm_customfield_entry.id_field and  %adm_customfield_entry.id_field='.$id_field.' and 
                    id_obj='.$id_corso; 
   
   
      if(!$rs = sql_query( $query )) return false;
   
      list($obj_entry, $type_field, $id_field) = sql_fetch_row($rs);
        if($type_field=='textfield'){
            return $obj_entry;       
        }
        if($type_field=='dropdown'){
            return $this->getCheckValueCustom( $id_field, $obj_entry);       
        }    
   
                 
    }

    
    
    private function getCheckValueCustom($id_field, $valueOption){
           $query = "Select  translation from %adm_customfield_son_lang, %adm_customfield_son
                where 
                lang_code='".getLanguage()."' 
                and %adm_customfield_son_lang.id_field_son=%adm_customfield_son.id_field_son
                and %adm_customfield_son.id_field=".$id_field." and %adm_customfield_son_lang.id_field_son=".$valueOption;
                
           if(!$rs = sql_query( $query )) return false;

        list($translation) = sql_fetch_row($rs);
        
        return  $translation; 
        
    }

    

}
?>
