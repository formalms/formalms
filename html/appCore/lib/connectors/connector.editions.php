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

require_once dirname(__FILE__) . '/lib.connector.php';
require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');
require_once _lms_ . '/lib/lib.edition.php';

/**
 * class for define editions connection to data source.
 *
 * @version 	1.0
 **/
class ConnectorEditions extends FormaConnector
{
    public $last_error = '';

    // name, type
    public $all_cols = [
        ['id_edition', 'int'],
        ['id_course', 'int'],
        ['code', 'text'],
        ['name', 'text'],
        ['description', 'text'],
        ['status', 'text'],
        ['date_begin', 'date'],
        ['date_end', 'date'],
        ['max_num_subscribe', 'int'],
        ['min_num_subscribe', 'int'],
        ['price', 'int'],
        ['overbooking', 'int'],
        ['can_subscribe', 'int'],
        ['sub_date_begin', 'date'],
        ['sub_date_end', 'date'],
    ];

    public $mandatory_cols = ['id_edition', 'id_course'];

    public $default_cols = [
        'code' => '',
        'name' => '',
        'description' => '',
        'status' => '0',
        'date_begin' => '',
        'date_end' => '',
        'max_num_subscribe' => '',
        'min_num_subscribe' => '',
        'price' => '',
        'overbooking' => '0',
        'can_subscribe' => '0',
        'sub_date_begin' => '',
        'sub_date_end' => '',
    ];

    public $valid_filed_type = ['text', 'date', 'dropdown', 'yesno'];

    public $dbconn = null;

    public $readwrite = 1; // read = 1, write = 2, readwrite = 3
    //var $sendnotify = 1; // send notify = 1, don't send notify = 2

    public $name = '';
    public $description = '';

    //var $on_delete = 1;  // unactivate = 1, delete = 2

    public $arr_id_inserted = [];

    public $first_row_header = '1';

    public $_readed_end;

    public $lang;

    public $first_row = false;

    public $today;

    public $all_data;

    public $position;

    public $tot_row;

    public function __construct($params)
    {
        if ($params === null) {
            return;
        } else {
            $this->set_config($params);
        }	// connection
    }

    public function get_config()
    {
        return ['name' => $this->name,
                        'description' => $this->description,
                        'readwrite' => $this->readwrite,
                        'first_row_header' => $this->first_row_header, /*,
                        'sendnotify' => $this->sendnotify,
                        'on_delete' => $this->on_delete*/];
    }

    public function set_config($params)
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['readwrite'])) {
            $this->readwrite = $params['readwrite'];
        }
        if (isset($params['first_row_header'])) {
            $this->first_row_header = $params['first_row_header'];
        }
        //if( isset($params['sendnotify']) )			$this->sendnotify = $params['sendnotify'];
        //if( isset($params['on_delete']) )			$this->on_delete = $params['on_delete'];
    }

    public function get_configUI()
    {
        return new ConnectorEditionsUI($this);
    }

    public function connect()
    {
        $this->lang = FormaLanguage::createInstance('rg_report');

        $this->_readed_end = false;
        $this->today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $this->position = 1;

        $query = 'SELECT COUNT(*) FROM %lms_course_editions';

        list($tot_row) = sql_fetch_row(sql_query($query));

        $this->tot_row = $tot_row;

        $query = ' SELECT *'
                . ' FROM %lms_course_editions ce';

        $result = sql_query($query);

        $data = [];
        $fields = [];

        while ($col = sql_fetch_field($result)) {
            $fields[] = $col->name;
        }

        if ($this->first_row_header) {
            $data[0] = $fields;
        }

        while ($row = sql_fetch_array($result)) {
            $_data = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'date_end':
                        $row[$field] = Format::date($row[$field], 'datetime');
                        break;
                    case 'date_begin':
                    case 'sub_date_begin':
                    case 'sub_date_end':
                        $row[$field] = Format::date($row[$field], 'date');
                        break;
                    default: break;
                }
                $_data[] = $row[$field];
            }
            $data[] = $_data;
        }

        $this->all_data = $data;

        return true;
    }

    public function close()
    {
    }

    public function get_type_name()
    {
        return 'course-editions';
    }

    public function get_type_description()
    {
        return 'Connector to course editions';
    }

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
        return (bool) ($this->readwrite & 1);
    }

    public function is_writeonly()
    {
        return (bool) ($this->readwrite & 2);
    }

    public function get_tot_cols()
    {
        return count($this->all_cols);
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
        $lang = FormaLanguage::createInstance('course', 'lms');

        $col_descriptor = [];
        foreach ($this->all_cols as $k => $col) {
            $col_descriptor[] = [
                FORMAIMPORT_COLNAME => $lang->def('_' . strtoupper($col[0])),
                FORMAIMPORT_COLID => $col[0],
                FORMAIMPORT_COLMANDATORY => (array_search($col[0], $this->mandatory_cols) === false
                                                    ? false
                                                    : true),
                FORMAIMPORT_DATATYPE => $col[1],
                FORMAIMPORT_DEFAULT => ($in = array_search($col[0], $this->default_cols) === false
                                                    ? ''
                                                    : $this->default_cols[$in]),
            ];
        }

        return $col_descriptor;
    }

    public function get_first_row()
    {
        if ($this->first_row) {
            return $this->first_row;
        }
        $this->first_row = $this->all_data[0];

        return $this->first_row;
    }

    public function get_next_row()
    {
        $row = [];
        if ($this->first_row_header) {
            if ($this->tot_row >= $this->position) {
                $row = $this->all_data[$this->position];

                ++$this->position;

                return $row;
            } else {
                $this->_readed_end = true;

                return false;
            }
        } else {
            if ($this->tot_row > $this->position) {
                $row = $this->all_data[$this->position];

                ++$this->position;

                return $row;
            } else {
                $this->_readed_end = true;

                return false;
            }
        }
    }

    public function is_eof()
    {
        return $this->_readed_end;
    }

    public function get_row_index()
    {
        return $this->position;
    }

    public function get_tot_mandatory_cols()
    {
        return count($this->mandatory_cols);
    }

    public function add_row($row, $pk)
    {
        $id_edition = false;

        foreach ($this->default_cols as $key => $default) {
            if ($row[$key] == '') {
                $row[$key] = $default;
            }
        }

        $id_edition = $row['id_edition'];

        $id_course = self::getIdCourseFromCode($row['id_course']);
        $id_edition = self::getIdEditionFromCode($row['code']);
        $date_end_datetime = $row['date_end'] . ' 00:00'; //for database type
        if ($id_course != null) {
            $em = new EditionManager();

            $is_add = false;
            if (!$id_edition) {
                $is_add = true;

                $em->insertEdition($id_course, $row['code'], Util::add_slashes($row['name']), Util::add_slashes($row['description']), $row['status'], $row['max_num_subscribe'], $row['min_num_subscribe'], $row['price'], $row['date_begin'], $date_end_datetime, $row['overbooking'], $row['can_subscribe'], $row['sub_date_begin'], $row['sub_date_end'], $id_edition);
                $id_edition = self::getIdEditionFromCode($row['code']);
            } else {
                // edition is to update

                $em->modEdition($id_edition, $row['code'], Util::add_slashes($row['name']), Util::add_slashes($row['description']), $row['status'], $row['max_num_subscribe'], $row['min_num_subscribe'], $row['price'], $row['date_begin'], $date_end_datetime, $row['overbooking'], $row['can_subscribe'], $row['sub_date_begin'], $row['sub_date_end']);
            }
            if ($id_edition != false) {
                if ($this->cache_inserted) {
                    $this->arr_id_inserted[] = $id_edition;
                }

                return true;
            }
        }
        $this->last_error = 'Unknow error';

        return false;
    }

    public function get_error()
    {
        return $this->last_error;
    }

    public function getIdCourseFromCode($code)
    {
        $query = '';
        $ret = [];
        $query = "select idCourse from %lms_course where code = '" . $code . "'";
        $rs = sql_query($query);
        $ret = sql_fetch_array($rs)['idCourse'];
        //course_edition must be 1
        if ($ret != null) {
            $query = '';
            $query = 'update %lms_course set course_edition=1 where idCourse=' . $ret . ' and course_edition<>1';
            sql_query($query);
        }

        return $ret;
    }

    public function getIdEditionFromCode($edition_code)
    {
        $query = '';
        $ret = [];
        $query = "select id_edition from %lms_course_editions where code = '" . $edition_code . "'";
        $rs = sql_query($query);
        $ret = sql_fetch_array($rs)['id_edition'];

        return $ret;
    }
}

/**
 * class for define editions UI connection.
 *
 * @version 	1.0
 **/
class ConnectorEditionsUI extends FormaConnectorUI
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
        return 'editionsuiconfig';
    }

    public function get_old_name()
    {
        return $this->post_params['old_name'];
    }

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
        // ---- access type read/write -----
        $out .= $this->form->getRadioSet($this->lang->def('_ACCESSTYPE'),
                                            $this->_get_base_name() . '_readwrite',
                                            $this->_get_base_name() . '[readwrite]',
                                            [$this->lang->def('_READ') => '1',
                                                    $this->lang->def('_WRITE') => '2', ],
                                            $this->post_params['readwrite']);

        $out .= $this->form->getRadioSet($this->lang->def('_FIRST_ROW_HEADER'),
                                            $this->_get_base_name() . '_first_row_header',
                                            $this->_get_base_name() . '[first_row_header]',
                                            [$this->lang->def('_YES') => '1',
                                                    $this->lang->def('_NO') => '0', ],
                                            $this->post_params['first_row_header']);

        return $out;
    }
}

function editions_factory()
{
    return new ConnectorEditions([]);
}
