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
class FormaConnectorCourseExport extends FormaConnector
{
    public $name = '';

    public $description = '';

    public $export_field_list = '';

    public $_query_result;

    public $first_row_header = '1';

    public $_readed_end;

    public $lang;

    public $first_row = false;

    public $today;

    public $all_data;

    public $position;

    public $tot_row;

    // name, type
    // COURSE_TYPE, COURSE_EDITION
    public $all_cols = [
        ['code', 'text'],
        ['name', 'text'],
        ['description', 'text'],
        ['lang_code', 'text'],
        ['status', 'text'],
        ['subscribe_method', 'int'],
        ['permCloseLO', 'int'],
        ['difficult', 'dropdown'],
        ['show_progress', 'int'],
        ['show_time', 'int'],
        ['medium_time', 'int'],
        ['show_extra_info', 'int'],
        ['show_rules', 'int'],
        ['date_begin', 'date'],
        ['date_end', 'date'],
        ['valid_time', 'int'],
        ['min_num_subscribe', 'int'],
        ['max_num_subscribe', 'int'],
        ['selling', 'int'],
        ['prize', 'int'],
        ['create_date', 'date'],
        ['id_course', 'int'],
        ['course_type', 'dropdown'],
        ['course_edition', 'int'],
    ];

    public $default_cols = ['description' => '',
                                'lang_code' => '',
                                'status' => '0',
                                'subscribe_method' => '',
                                'permCloseLO' => '',
                                'difficult' => 'medium',
                                'show_progress' => '1',
                                'show_time' => '1',
                                'medium_time' => '0',
                                'show_extra_info' => '0',
                                'show_rules' => '0',
                                'date_begin' => '1970-01-01',
                                'date_end' => '1970-01-01',
                                'valid_time' => '0',
                                'min_num_subscribe' => '0',
                                'max_num_subscribe' => '0',
                                'selling' => '0',
                                'prize' => '',
                                'create_date' => '1970-01-01',
                                'id_course' => '0',
                                                                'course_type' => 'elearning',
                                                                'course_edition' => '0', ];

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
        require_once _lms_ . '/lib/lib.course.php';

        $this->set_config($params);
    }

    public function get_config()
    {
        return ['name' => $this->name,
            'description' => $this->description,
            'first_row_header' => $this->first_row_header,
        ];
    }

    public function set_config($params)
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['first_row_header'])) {
            $this->first_row_header = (int) $params['first_row_header'];
        }
    }

    public function get_configUI()
    {
        return new FormaConnectorCourseExportUI($this);
    }

    /**
     * execute the connection to source.
     **/
    public function connect()
    {
        $this->lang = FormaLanguage::createInstance('rg_report');

        $this->_readed_end = false;
        $this->today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $this->position = 1;

        $course_man = new Man_Course();

        // perform the query for data retriving

        $query = 'SELECT COUNT(*)'
                    . ' FROM %lms_course';

        list($number_of_course) = sql_fetch_row(sql_query($query));

        $this->tot_row = $number_of_course;

        $query = 'SELECT `code`, `name`, `description`, `lang_code`, `status`, `subscribe_method`, `mediumTime`, `permCloseLO`, `difficult`, `show_progress`, `show_time`, `show_extra_info`, `show_rules`, `date_begin`, `date_end`, `valid_time`, `max_num_subscribe`, `min_num_subscribe`, `selling`, `prize`, `create_date`, `idCourse`, `course_type`, `course_edition`'
                    . ' FROM %lms_course'
                    . ' ORDER BY name';

        $result = sql_query($query);

        $data = [];

        $counter = 0;

        if ($this->first_row_header) {
            $data[$counter][] = 'code';
            $data[$counter][] = 'name';
            $data[$counter][] = 'description';
            $data[$counter][] = 'lang_code';
            $data[$counter][] = 'status';
            $data[$counter][] = 'subscribe_method';
            $data[$counter][] = 'mediumTime';
            $data[$counter][] = 'permCloseLO';
            $data[$counter][] = 'difficult';
            $data[$counter][] = 'show_progress';
            $data[$counter][] = 'show_time';
            $data[$counter][] = 'show_extra_info';
            $data[$counter][] = 'show_rules';
            $data[$counter][] = 'date_begin';
            $data[$counter][] = 'date_end';
            $data[$counter][] = 'valid_time';
            $data[$counter][] = 'max_num_subscribe';
            $data[$counter][] = 'min_num_subscribe';
            $data[$counter][] = 'selling';
            $data[$counter][] = 'prize';
            $data[$counter][] = 'create_date';
            $data[$counter][] = 'idCourse';
            $data[$counter][] = 'course_type';
            $data[$counter][] = 'course_edition';

            ++$counter;
        }

        while ($row = sql_fetch_array($result)) {
            $data[$counter][] = $row[0];
            $data[$counter][] = $row[1];
            $data[$counter][] = $row[2];
            $data[$counter][] = $row[3];
            $data[$counter][] = $row[4];
            $data[$counter][] = $row[5];
            $data[$counter][] = $row[6];
            $data[$counter][] = $row[7];
            $data[$counter][] = $row[8];
            $data[$counter][] = $row[9];
            $data[$counter][] = $row[10];
            $data[$counter][] = $row[11];
            $data[$counter][] = $row[12];
            $data[$counter][] = $row[13];
            $data[$counter][] = $row[14];
            $data[$counter][] = $row[15];
            $data[$counter][] = $row[16];
            $data[$counter][] = $row[17];
            $data[$counter][] = $row[18];
            $data[$counter][] = $row[19];
            $data[$counter][] = $row[20];
            $data[$counter][] = $row[21];
            $data[$counter][] = $row[22];
            $data[$counter][] = $row[23];
            ++$counter;
        }
        --$counter;
        $this->all_data = $data;

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
        return 'course-export-connector';
    }

    /**
     * Return the description of the connector.
     **/
    public function get_type_description()
    {
        return 'connector for course export';
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
class FormaConnectorCourseExportUI extends FormaConnectorUI
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
        return 'coursereportuiconfig';
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

        $out .= $this->form->getRadioSet($this->lang->def('_FIRST_ROW_HEADER'),
                                            $this->_get_base_name() . '_first_row_header',
                                            $this->_get_base_name() . '[first_row_header]',
                                            [$this->lang->def('_YES') => '1',
                                                    $this->lang->def('_NO') => '0', ],
                                            $this->post_params['first_row_header']);

        return $out;
    }
}

function courseexport_factory()
{
    return new FormaConnectorCourseExport([]);
}
