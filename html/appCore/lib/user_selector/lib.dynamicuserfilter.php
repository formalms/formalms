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

require_once(_base_.'/lib/lib.json.php');
require_once(_adm_.'/lib/lib.field.php');
require_once(_adm_.'/lib/user_selector/lib.otherfieldtypes.php');

define("_STANDARD_FIELDS_PREFIX", "std");
define("_CUSTOM_FIELDS_PREFIX", "cstm");
define("_OTHER_FIELDS_PREFIX", "oth");

define("_FIELD_TYPE_TEXT", "textfield");
define("_FIELD_TYPE_DATE", "date");


class DynamicUserFilter {

    public $id = '';
    public $use_form_input = true;

    protected $_initial_filters = array();
    protected $_initial_exclusive = true;
    protected $_use_other_fields = true;

    protected $db;
    protected $json;

    public function __construct($id) {
        $this->id = $id;
        $this->db = DbConn::getInstance();
        $this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    }

    public function init() {
        YuiLib::load('container,menu,button');
        Util::get_js(Get::rel_path('adm').'/lib/user_selector/lib.common.js', true, true);
        Util::get_js(Get::rel_path('adm').'/lib/user_selector/lib.dynamicuserfilter.js', true, true);
        if ($this->_use_other_fields)	Util::get_js(Get::rel_path('adm').'/lib/user_selector/lib.otherfieldtypes.js', true, true);
    }

    private function getStandardFieldsList() {

        $lang =& DoceboLanguage::createInstance('standard', 'framework');

        $fields = array(
            array('id'=>_STANDARD_FIELDS_PREFIX.'_0', 'name'=>addslashes(Lang::t('_USERNAME', 'standard')),         'type'=>_FIELD_TYPE_TEXT, 'standard'=>true),
            array('id'=>_STANDARD_FIELDS_PREFIX.'_1', 'name'=>addslashes(Lang::t('_FIRSTNAME', 'standard')),        'type'=>_FIELD_TYPE_TEXT, 'standard'=>true),
            array('id'=>_STANDARD_FIELDS_PREFIX.'_2', 'name'=>addslashes(Lang::t('_LASTNAME', 'standard')),         'type'=>_FIELD_TYPE_TEXT, 'standard'=>true),
            array('id'=>_STANDARD_FIELDS_PREFIX.'_3', 'name'=>addslashes(Lang::t('_EMAIL', 'standard')),            'type'=>_FIELD_TYPE_TEXT, 'standard'=>true),
            array('id'=>_STANDARD_FIELDS_PREFIX.'_4', 'name'=>addslashes(Lang::t('_REGISTER_DATE', 'standard')),    'type'=>_FIELD_TYPE_DATE, 'standard'=>true),
            array('id'=>_STANDARD_FIELDS_PREFIX.'_5', 'name'=>addslashes(Lang::t('_DATE_LAST_ACCESS', 'standard')), 'type'=>_FIELD_TYPE_DATE, 'standard'=>true)
        );
        return $fields;
    }

    private function getCustomFieldsList() {
        $fman = new FieldList();
        $fields = $fman->getAllFieldsInfo();
        for ($i=0; $i<count($fields); $i++) {
            $id = $fields[$i]['id'];
            $fields[$i]['standard'] = false;
            $fields[$i]['id'] = _CUSTOM_FIELDS_PREFIX.'_'.$id;
        }
        return $fields;
    }

    private function getFieldsList($js = false) {

        $fields = $this->getStandardFieldsList();
        if ($this->_use_other_fields) {
            $ofobj = new OtherFieldsTypes();
            $fields = array_merge($fields, $ofobj->getOtherFieldsList());
        }
        $fields = array_merge($fields, $this->getCustomFieldsList());

        if (!$js) return $fields;

        $temp = array();
        foreach ($fields as $val) {
            $temp[] = '{id: "'.$val['id'].'", name: "'.addslashes($val['name']).'", type: "'.$val['type'].'", standard: '.($val['standard'] ? 'true' : 'false').'}';
        }
        return '['.implode(',', $temp).']';
    }

    function getFieldTypesObjects() {
        $fman = new FieldList();
        $types = $fman->getFieldTypesList();

        $temp = array();
        foreach ($types as $key=>$val) {
            require_once(Forma::inc(_adm_.'/modules/field/'.$val['type_file']));
            $quest_obj = eval("return new ".$val['type_class']."( NULL );");
            $temp[] = $quest_obj->getClientClassObject();
        }

        return '['.implode(',', $temp).']';
    }

    /**
     * @param <type> $data array of arrays in the form {'field_id', 'field_value'}, or a json string
     * @return <type>
     */
    public function setInitialSelection($data) {

        $output = true;
        $_data = false;
        switch (gettype($data)) {
            case "string": {
                $_data = $this->json->decode(urldecode($data));
            };break;
            case "array": {
                $_data = $data;
            };break;
            default: {
                $output = false;
            }
        }
        if ($output && $_data) {

            $this->_initial_exclusive = ($_data['exclusive'] ? true : false);
            foreach ($_data['filters'] as $filter) {

                $this->_initial_filters[] = array(
                    'id_field' => $filter['id_field'],
                    'value' => addslashes($filter['value'])
                );
            }
        }
        return $output;
    }

    public function get($domready = true, $tags = true) {

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $output = array();

        $js_initsel = '';
        if (count($this->_initial_filters)>0) {
            $temp = array();
            foreach ($this->_initial_filters as $filter) {
                $temp[] = '{id_field: "'.$filter['id_field'].'", value: "'.$filter['value'].'"}';
            }
            $js_initsel = '['.implode(',', $temp).']';
        }

        $js_function = 'YAHOO.namespace("dynFilter");'
            .'YAHOO.dynFilter = new DynamicUserFilter("'.$this->id.'", {'."\n"
            .'		id: "'.$this->id.'",'."\n"
            .'		fields: '.$this->getFieldsList(true).','."\n"
            .'		fieldTypes: '.$this->getFieldTypesObjects().','."\n"
            .'		use_form_input: '.($this->use_form_input ? 'true' : 'false').','."\n"
            .'    initial_exclusiveness: "'.($this->_initial_exclusive ? "AND" : "OR").'",'."\n"
            .($js_initsel!='' ? '    initial_filters: '.$js_initsel.','."\n" : '')
            .'		lang: {'
            .'      _ADD_FILTER: "'.addslashes(Lang::t('_ADD', 'standard')).'",'
            .'      _ADD: "'.addslashes(Lang::t('_ADD', 'standard')).'",'
            .'      _DEL: "'.addslashes(Lang::t('_DEL', 'standard')).'",'
            .'      _RESET: "'.addslashes(Lang::t('_RESET', 'standard')).'",'
            .'      _FILTER_ONE_COND: "'.addslashes(Lang::t('_FILTER_ONE_COND', 'standard')).'",'
            .'      _FILTER_ALL_CONDS: "'.addslashes(Lang::t('_FILTER_ALL_CONDS', 'standard')).'"'
            .'     }'."\n"
            .'	}); '."\n";

        if ($this->_use_other_fields) {

            $ofman = new OtherFieldsTypes();
            $initotherfields = $ofman->getInitData(true);
            //,courses: '.$initotherfields['courses'].'
            $js_function .= 'YAHOO.namespace("otherFields");'."\n"
                .'YAHOO.otherFields = new DynamicFilterOtherFieldTypes('."\n"
                .'{languages: '.$initotherfields['languages'].','."\n"
                .'levels: '.$initotherfields['levels'].'});'
                .'YAHOO.dynFilter.loadFieldTypes(YAHOO.otherFields.getFieldTypesList());';
        }

        if ($domready) {
            $js_temp = 'YAHOO.util.Event.onDOMReady(function(e) { '.$js_function.' });';
        } else {
            $js_temp = $js_function;
        }

        $output['js'] = ($tags ? '<script type="text/javascript">'.$js_temp.'</script>' : $js_temp);
        $output['html'] = '<div id="'.$this->id.'" class="dyn_filter"></div>';

        return $output;
    }

    function checkUser($user_to_check, $param = false) {

        $output		= false;
        $_testvar	= '';
        $f_arr = ($param ? $param : Get::req($this->id."_input", DOTY_MIXED, false) );

        if(!$user_to_check) return $output;
        if (!$f_arr) return $output;

        $a_obj	= Docebo::user()->getAclManager();
        $fman	= new FieldList();

        $filter				= $this->json->decode(stripslashes($f_arr));
        $user_data_std		= $a_obj->getUser($user_to_check, false);
        $user_data_extra	= $fman->getUserFieldEntryData($user_to_check, false);

        $exclusive = $filter['exclusive'];
        $conds = $filter['filters'];
        if (count($conds)<=0) return true; //if no conditions, return true anyway
        $output = $exclusive;

        foreach ($conds as $cond) {

            $id_field = $cond['id_field'];
            $params = $this->json->decode($cond['value']);
            if($params == null) $params = $cond['value'];
            $res = $exclusive;

            list($id_type, $id) = explode('_', $id_field);

            switch ($id_type) {
                // stadard core_user fields
                case _STANDARD_FIELDS_PREFIX: {
                    require_once(_adm_.'/modules/field/class.field.php');
                    require_once(_adm_.'/modules/field/class.date.php');

                    switch ($id) {
                        case 0: { //userid
                            $user_data_std[ACL_INFO_USERID] = $a_obj->relativeId($user_data_std[ACL_INFO_USERID]);
                            $res = Field::checkUserField($user_data_std[ACL_INFO_USERID], $params);
                        } break;
                        case 1: { //firstname
                            $res = Field::checkUserField($user_data_std[ACL_INFO_FIRSTNAME], $params);
                        } break;
                        case 2: { //lastname
                            $res = Field::checkUserField($user_data_std[ACL_INFO_LASTNAME], $params);
                        } break;
                        case 3: { //email
                            $res = Field::checkUserField($user_data_std[ACL_INFO_EMAIL], $params);
                        } break;
                        case 4: { //register date
                            $res = Field_Date::checkUserField($user_data_std[ACL_INFO_REGISTER_DATE], $params);
                        } break;
                        case 5: { //lastenter
                            $res = Field_Date::checkUserField($user_data_std[ACL_INFO_LASTENTER], $params);
                        } break;
                        default: { $res = false; }
                    }
                } break;
                // custom fields -----------------------------------
                case _CUSTOM_FIELDS_PREFIX: {
                    //first check if the user own this extra field
                    if (isset($user_data_extra[$id])) {
                        $fobj = $fman->getFieldInstance($id);
                        $res = $fobj->checkUserField($user_data_extra[$id], $params); //check if the field value match the condition
                    } else {
                        $res = false;
                    }
                };break;
                // other fields -------------------------------------
                case _OTHER_FIELDS_PREFIX: {
                    $ofobj = new OtherFieldTypes();
                    $res = $ofobj->checkUserField($id, $user_to_check, $params);
                } break;
                default: { $res = false; }
            }
            if ($exclusive) { //AND of conditions
                if (!$res) { $output = false; break; }
            } else { //OR of conditions
                if ($res) { $output = true; break; }
            }
        }

        return $output;
    }

    function getConditions($param = false) {
        $json		= new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

        $f_arr = ($param ? $param : urldecode(stripslashes(Get::req($this->id."_input", DOTY_STRING, false))) );

        $filter		= is_string($f_arr) ? $json->decode(stripslashes($f_arr)) : $f_arr;
        $conds		= $filter['filters'];

        return $conds;
    }

    function getUsers($param = false) {
        //retrieve all users matching given conditions

        $output		= array();
        $json		= new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $a_obj		= new DoceboACLManager();
        $fman		= new FieldList();

        $user_to_check = Get::req('user', DOTY_INT, false);
        $f_arr = ($param ? $param : urldecode(stripslashes(Get::req($this->id."_input", DOTY_STRING, false))) );

        $filter		= is_string($f_arr) ? $json->decode(stripslashes($f_arr)) : $f_arr;
        $exclusive	= $filter['exclusive'];
        $conds		= $filter['filters'];

        //return a void array if no conditions specified
        if (count($conds)<=0) return array();

        //compose nested query
        // base query /Anonymous
        $base_query = "SELECT idst, userid "
            ." FROM %adm_user ";
        $std_condition    = array();
        $in_conditions    = array();
        $other_conditions = array();
        foreach ($conds as $cond) {

            $id_field	= $cond['id_field'];
            $params		= $json->decode($cond['value']);
            if($params == null) $params = $cond['value'];
            $res		= $exclusive;

            list($id_type, $id) = explode('_', $id_field);

            switch ($id_type) {

                case _STANDARD_FIELDS_PREFIX: {
                    require_once(_adm_.'/modules/field/class.field.php');
                    require_once(_adm_.'/modules/field/class.date.php');

                    switch ($id) {
                        case 0: { //userid
                            $temp = " userid ";
                            switch ($params['cond']) {
                                case 2: { $temp .= " = '".$a_obj->absoluteId($params['value'])."' "; } break; //equals
                                case 0: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
                                case 3: { $temp .= " <> '".$a_obj->absoluteId($params['value'])."' "; } break; //not equal
                                case 1: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
                                case 4: { $temp .= " LIKE '".$a_obj->absoluteId($params['value'])."%' "; } break; //starts with
                                case 5: { $temp .= " LIKE '%".$params['value']."' "; } break; //ends with
                                default: { $temp .= " NOT LIKE '%' "; } //unexistent
                            }
                            $std_condition[] = $temp;
                        } break;

                        case 1: { //firstname
                            $temp = " firstname ";
                            switch ($params['cond']) {
                                case 2: { $temp .= " = '".$params['value']."' "; } break; //equals
                                case 0: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
                                case 3: { $temp .= " <> '".$params['value']."' "; } break; //not equal
                                case 1: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
                                case 4: { $temp .= " LIKE '".$params['value']."%' "; } break; //starts with
                                case 5: { $temp .= " LIKE '%".$params['value']."' "; } break; //ends with
                                default: { $temp .= " NOT LIKE '%' "; } //unexistent
                            }
                            $std_condition[] = $temp;
                        } break;

                        case 2: { //lastname
                            $temp = " lastname ";
                            switch ($params['cond']) {
                                case 2: { $temp .= " = '".$params['value']."' "; } break; //equals
                                case 0: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
                                case 3: { $temp .= " <> '".$params['value']."' "; } break; //not equal
                                case 1: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
                                case 4: { $temp .= " LIKE '".$params['value']."%' "; } break; //starts with
                                case 5: { $temp .= " LIKE '%".$params['value']."' "; } break; //ends with
                                default: { $temp .= " NOT LIKE '%' "; } //unexistent
                            }
                            $std_condition[] = $temp;
                        } break;

                        case 3: { //email
                            $temp = " email ";
                            switch ($params['cond']) {
                                case 2: { $temp .= " = '".$params['value']."' "; } break; //equals
                                case 0: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
                                case 3: { $temp .= " <> '".$params['value']."' "; } break; //not equal
                                case 1: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
                                case 4: { $temp .= " LIKE '".$params['value']."%' "; } break; //starts with
                                case 5: { $temp .= " LIKE '%".$params['value']."' "; } break; //ends with
                                default: { $temp .= " NOT LIKE '%' "; } //unexistent
                            }
                            $std_condition[] = $temp;
                        } break;

                        case 4: { //register date
                            $date = substr(Format::dateDb($params['value'], 'date'), 0, 10);
                            $temp = " register_date ";
                            switch ($params['cond']) {
                                case 0: { $temp .= " < '".$date." 00:00:00' "; } break; //<
                                case 1: { $temp .= " <= '".$date." 23:59:59' "; } break; //<=
                                case 2: { $temp = " ( register_date >= '".$date." 00:00:00' AND register_date <= '".$date." 23:59:59' ) "; } break; //=
                                case 3: { $temp .= " >= '".$date." 00:00:00' "; } break; //>=
                                case 4: { $temp .= " > '".$date." 23:59:59' "; } break; //>
                                default: { $temp .= " NOT LIKE '%' "; } //unexistent
                            }
                            $std_condition[] = $temp;
                        } break;

                        case 5: { //lastenter
                            $date = substr(Format::dateDb($params['value'], 'date'), 0, 10);
                            $temp = " lastenter ";
                            switch ($params['cond']) {
                                case 0: { $temp .= " < '".$date." 00:00:00' "; } break; //<
                                case 1: { $temp .= " <= '".$date." 23:59:59' "; } break; //<=
                                case 2: { $temp = " ( lastenter >= '".$date." 00:00:00' AND lastenter <= '".$date." 23:59:59' ) "; } break; //=
                                case 3: { $temp .= " >= '".$date." 00:00:00' "; } break; //>=
                                case 4: { $temp .= " > '".$date." 23:59:59' "; } break; //>
                                default: { $temp .= " NOT LIKE '%' "; } //unexistent
                            }
                            $std_condition[] = $temp;
                        } break;

                        default: {}
                    }
                } break;
                // filter on a custom field
                case _CUSTOM_FIELDS_PREFIX: {

                    $fobj = $fman->getFieldInstance($id);
                    $in_conditions[] = $fobj->getFieldQuery($params);
                } break;
                // other special field
                case _OTHER_FIELDS_PREFIX: {

                    $ofobj = new OtherFieldsTypes();
                    $other_conditions[] = $ofobj->getFieldQuery($id, $params);
                } break;
                default: { }

            } //end switch

        } //end foreach

        if ($exclusive) {
            $query = $base_query.' WHERE 1 '
                .( !empty($std_condition)
                    ? " AND ".implode(" AND ", $std_condition)
                    : ''
                )
                .( !empty($in_conditions)
                    ? ' AND idst IN ( '.implode(" ) AND idst IN ( ", $in_conditions).' ) '
                    : ''
                )
                .( !empty($other_conditions)
                    ? ' AND idst IN ( '.implode(" ) AND idst IN ( ", $other_conditions).' ) '
                    : ''
                );
        } else {
            $query = $base_query.' WHERE 0 '
                .( !empty($std_condition)
                    ? ' OR  ( '.implode(" ) OR idst IN ( ", $std_condition).' ) '
                    : ''
                )
                .( !empty($in_conditions)
                    ? ' OR idst IN ( '.implode(" ) OR idst IN ( ", $in_conditions).' ) '
                    : ''
                )
                .( !empty($other_conditions)
                    ? ' OR idst IN ( '.implode(" ) OR idst IN ( ", $other_conditions).' ) '
                    : ''
                );
        }

        //produce output
        $output = array();
        $re = $this->db->query($query);
        while ($rw = $this->db->fetch_assoc($re)) {
            if ($rw['userid'] != '/Anonymous') $output[] = $rw['idst'];
        }

        return $output;
    }

} //end class