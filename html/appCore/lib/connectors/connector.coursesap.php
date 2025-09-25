<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * esportazione dati relativi ai corsi   cid,codicecorso,anno.
 *
 * @version 	$id$
 *
 * @author		Pirovano Fabio <fabio (@) docebo (.) com>
 **/
require_once dirname(__FILE__) . '/lib.connector.php';

/**
 * class for define user report connection.
 *
 * @version 	1.0
 *
 * @author		Pirovano Fabio <fabio (@) docebo (.) com>
 **/
class FormaConnector_CourseSap extends FormaConnector
{
    public $name = '';

    public $description = '';

    public $cid_field = 1;

    public $export_field_list = '';

    public $_query_result;

    public $_readed_end;

    public $row_index;

    public $lang;

    public $first_row = false;

    public $acl_man;
    public $users_info;
    public $category_list;
    public $time_list;
    public $session_list;
    public $lastaccess_list;

    // name, type
    public $all_cols = [
        ['userid', 'text'],
        ['cid', 'text'],
        ['cod_course', 'text'],
        ['year', 'text'],
    ];

    public $default_cols = ['userid' => '',
                                'cid' => '',
                                'cod_course' => '',
                                'year' => '', ];
    public string $last_error;
    public array $_cid_list;

    /**
     * This constructor require the source file name.
     *
     * @param array $params the array of params
     *                      - 'filename' => name of the file (required)
     *                      - 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
     *                      - 'separator' => string a char with the fields separator (Optional, default = ,)
     **/
    public function __construct($params)
    {
        require_once _adm_ . '/lib/lib.directory.php';
        require_once _base_ . '/lib/lib.userselector.php';
        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');

        $this->set_config($params);
    }

    public function get_config()
    {
        return ['name' => $this->name,
                        'description' => $this->description,
                        'cid_field' => $this->cid_field, ];
    }

    public function set_config($params)
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['cid_field'])) {
            $this->cid_field = $params['cid_field'];
        }
    }

    public function get_configUI()
    {
        return new FormaConnector_CourseSapUI($this);
    }

    /**
     * execute the connection to source.
     **/
    public function connect()
    {
        $this->lang = FormaLanguage::createInstance('sap_report');

        // perform the query for data retriving

        $course_man = new Man_Course();

        $this->_query_result = false;
        $this->_readed_end = false;
        $this->row_index = 0;

        // find some information

        require_once _adm_ . '/lib/lib.field.php';

        $field_man = new FieldList();
        $field_man->setFieldEntryTable($GLOBALS['prefix_fw'] . '_field_userentry');
        $this->_cid_list = $field_man->getAllFieldEntryData($this->cid_field);

        $query_course_user = '
		SELECT cu.idUser, c.idCourse, c.code, cu.date_complete
		FROM  %lms_courseuser AS cu 
			JOIN ' . $GLOBALS['prefix_lms'] . "_course AS c
		WHERE cu.idCourse = c.idCourse 
			AND cu.status = '" . _CUS_END . "' ";
        $this->_query_result = sql_query($query_course_user);

        if (!$this->_query_result) {
            $this->last_error = sql_error();

            return false;
        }

        return true;
    }

    /**
     * execute the close of the connection.
     **/
    public function close()
    {
        return true;
    }

    /**
     * Return the type of the connector.
     **/
    public function get_type_name()
    {
        return 'sap-user-report-connector';
    }

    /**
     * Return the description of the connector.
     **/
    public function get_type_description()
    {
        return 'connector for user statistics';
    }

    /**
     * Return the name of the connection.
     **/
    public function get_name()
    {
        return $this->name;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function is_readonly()
    {
        return true;
    }

    public function is_writeonly()
    {
        return false;
    }

    public function get_tot_cols()
    {
        return count($this->cols_descriptor);
    }

    /**
     * @return array the array of columns descriptor
     *               - FORMAIMPORT_COLNAME => string the name of the column
     *               - FORMAIMPORT_COLID => string the id of the column (optional,
     *               same as COLNAME if not given)
     *               - FORMAIMPORT_COLMANDATORY => bool TRUE if col is mandatory
     *               - FORMAIMPORT_DATATYPE => the data type of the column
     *               - FORMAIMPORT_DEFAULT => the default value for the column (Optional)
     *               For readonly connectos only 	FORMAIMPORT_COLNAME and FORMAIMPORT_DATATYPE
     *               are required
     **/
    public function get_cols_descripor()
    {
        $lang = FormaLanguage::createInstance('userreport', 'lms');

        $col_descriptor = [];
        foreach ($this->all_cols as $k => $col) {
            $col_descriptor[] = [
                FORMAIMPORT_COLNAME => $lang->def('_' . strtoupper($col[0])),
                FORMAIMPORT_COLID => $col[0],
                FORMAIMPORT_COLMANDATORY => false,
                FORMAIMPORT_DATATYPE => $col[1],
                FORMAIMPORT_DEFAULT => (isset($this->default_cols[$col[0]]) ? $this->default_cols[$col[0]] : ''),
            ];
        }

        return $col_descriptor;
    }

    public function get_first_row()
    {
        $default = ['', '', '', ''];
        if ($this->first_row) {
            return $this->first_row;
        }
        if (!$this->_query_result) {
            return $default;
        }

        $result = sql_fetch_row($this->_query_result);

        if (!$result) {
            $this->_readed_end = true;

            return $default;
        }
        ++$this->row_index;

        list($id_user, $id_course, $code, $date_complete) = $result;

        if (!isset($this->_cid_list[$id_user])) {
            return $default;
        }

        $row = [
            $this->_cid_list[$id_user],
            $code,
            substr($date_complete, 0, 4),
        ];

        return $row;
    }

    public function get_next_row()
    {
        //$this->export_field_list

        $default = ['', '', '', ''];
        if (!$this->_query_result) {
            return false;
        }
        if (!$result = sql_fetch_row($this->_query_result)) {
            $this->_readed_end = true;

            return false;
        }
        ++$this->row_index;

        list($id_user, $id_course, $code, $date_complete) = $result;

        if (!isset($this->_cid_list[$id_user])) {
            return $default;
        }
        if (!$date_complete) {
            return $default;
        }

        $row = [
            $id_user,
            $this->_cid_list[$id_user],
            $code,
            substr($date_complete, 0, 4),
        ];

        return $row;
    }

    public function is_eof()
    {
        return $this->_readed_end;
    }

    public function get_row_index()
    {
        return $this->row_index;
    }

    public function get_tot_mandatory_cols()
    {
        return 0;
    }

    public function get_error()
    {
        return $this->last_error;
    }
}

/**
 * The configurator for csv connectors.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnector_CourseSapUI extends FormaConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public function __construct(&$connector)
    {
        $this->connector = $connector;
    }

    public function _get_base_name()
    {
        return 'userreportconfig';
    }

    public function get_old_name()
    {
        return $this->post_params['old_name'];
    }

    /**
     * All post fields are in array 'csvuiconfig'.
     **/
    public function parse_input($get, $post)
    {
        if (!isset($post[$this->_get_base_name()])) {
            // first call - first step, initialize variables
            $this->post_params = $this->connector->get_config();

            $this->post_params['step'] = '0';
            $this->post_params['old_name'] = $this->post_params['name'];
            if ($this->post_params['name'] == '') {
                $this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');
            }
            if ($this->post_params['cid_field'] == '') {
                $this->post_params['cid_field'] = 0;
            }
        } else {
            // get previous values
            $this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
            $arr_new_params = $post[$this->_get_base_name()];
            // overwrite with the new posted values
            foreach ($arr_new_params as $key => $val) {
                if ($key != 'memory' && $key != 'reset') {
                    $this->post_params[$key] = stripslashes($val);
                }
            }
        }
        $this->_load_step_info();
    }

    public function _set_step_info($next, $prev, $sh_next, $sh_prev, $sh_finish)
    {
        $this->step_next = $next;
        $this->step_prev = $prev;
        $this->sh_next = $sh_next;
        $this->sh_prev = $sh_prev;
        $this->sh_finish = $sh_finish;
    }

    public function _load_step_info()
    {
        $this->_set_step_info('1', '0', false, false, true);
    }

    public function go_next()
    {
        $this->post_params['step'] = $this->step_next;
        $this->_load_step_info();
    }

    public function go_prev()
    {
        $this->post_params['step'] = $this->step_prev;
        $this->_load_step_info();
    }

    public function go_finish()
    {
        $this->filterParams($this->post_params);
        $this->connector->set_config($this->post_params);
    }

    public function show_next()
    {
        return $this->sh_next;
    }

    public function show_prev()
    {
        return $this->sh_prev;
    }

    public function show_finish()
    {
        return $this->sh_finish;
    }

    public function get_htmlheader()
    {
        return '';
    }

    public function get_html($get = null, $post = null)
    {
        $out = '';
        switch ($this->post_params['step']) {
            case '0':
                $out .= $this->_step0();
            break;
        }
        // save parameters
        $out .= $this->form->getHidden($this->_get_base_name() . '_memory',
                                        $this->_get_base_name() . '[memory]',
                                        urlencode(Util::serialize($this->post_params)));

        return $out;
    }

    public function _step0()
    {
        require_once _adm_ . '/lib/lib.field.php';
        $field_man = new FieldList();
        $field_list = $field_man->getFlatAllFields();

        $out = '';

        // ---- name -----
        $out = $this->form->getTextfield($this->lang->def('_NAME'),
                                            $this->_get_base_name() . '_name',
                                            $this->_get_base_name() . '[name]',
                                            255,
                                            $this->post_params['name']);
        // ---- description -----
        $out .= $this->form->getSimpleTextarea($this->lang->def('_DESCRIPTION'),
                                            $this->_get_base_name() . '_description',
                                            $this->_get_base_name() . '[description]',
                                            $this->post_params['description']);
        // --- cid field --------
        $out .= $this->form->getDropdown($this->lang->def('_CID'),
                                            $this->_get_base_name() . '_cid_field',
                                            $this->_get_base_name() . '[cid_field]',
                                            $field_list,
                                            $this->post_params['cid_field']);

        return $out;
    }
}

function coursesap_factory()
{
    return new FormaConnector_CourseSap([]);
}
