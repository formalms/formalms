<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
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
class DoceboConnectorCourseReport extends DoceboConnector
{
    public $name = '';

    public $description = '';

    public $export_field_list = '';

    public $_query_result;

    public $_readed_end;

    public $row_index;

    public $lang;

    public $first_row = false;

    public $acl_man;

    public $users_info;
    public $id_courses;
    public $num_iscr;
    public $num_nobegin;
    public $num_itinere;
    public $num_end;
    public $time_in_course;

    // name, type
    public $all_cols = [
        ['code', 'text'],
        ['name', 'text'],
        ['enrolled_number', 'int'],
        ['no_begin', 'int'],
        ['no_begin_perc', 'text'],
        ['begin', 'int'],
        ['begin_perc', 'text'],
        ['end_course', 'int'],
        ['end_course_perc', 'text'],
        ['time_in_course', 'text'],
    ];

    public $default_cols = ['code' => '',
                                'name' => '',
                                'enrolled_number' => '0',
                                'no_begin' => '0',
                                'no_begin_perc' => '0%',
                                'begin' => '0',
                                'begin_perc' => '0%',
                                'end_course' => '0',
                                'end_course_perc' => '0%',
                                'time_in_course' => '00:00:00', ];

    /**
     * This constructor require the source file name.
     *
     * @param array $params the array of params
     *                      - 'filename' => name of the file (required)
     *                      - 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
     *                      - 'separator' => string a char with the fields separator (Optional, default = ,)
     **/
    public function DoceboConnectorCourseReport($params)
    {
        require_once _adm_ . '/lib/lib.directory.php';
        require_once _base_ . '/lib/lib.userselector.php';
        require_once _lms_ . '/lib/lib.course.php';

        $this->set_config($params);
    }

    public function get_config()
    {
        return ['name' => $this->name,
                        'description' => $this->description, ];
    }

    public function set_config($params)
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
    }

    public function get_configUI()
    {
        return new DoceboConnectorCourseReportUI($this);
    }

    /**
     * execute the connection to source.
     **/
    public function connect()
    {
        $this->lang = DoceboLanguage::createInstance('rg_report');

        // perform the query for data retriving

        $course_man = new Man_Course();
        $this->acl_man = new DoceboACLManager();
        $p_dr = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
        $re_people = $p_dr->getAllRowsIdst();

        $this->_readed_end = false;
        $this->row_index = 0;

        $user_selected = [];
        if (!$re_people) {
            $this->_readed_end = true;

            return true;
        }

        while (list($idst) = sql_fetch_row($re_people)) {
            $user_selected[$idst] = $idst;
        }

        $this->users_info = $this->acl_man->getUsers($user_selected);
        $this->id_courses = $course_man->getAllCourses();
        $this->num_iscr = [];
        $this->num_nobegin = [];
        $this->num_itinere = [];
        $this->num_end = [];
        $this->time_in_course = [];
        $effective_user = [];

        $query_course_user = '
		SELECT cu.idUser, cu.idCourse, cu.date_first_access, cu.date_complete
		FROM ' . $GLOBALS['prefix_lms'] . '_courseuser AS cu 
		WHERE idUser IN ( ' . implode(',', $user_selected) . ' ) ';

        $re_course_user = sql_query($query_course_user);
        while (list($id_u, $id_c, $fisrt_access, $date_complete) = sql_fetch_row($re_course_user)) {
            if (isset($this->num_iscr[$id_c])) {
                ++$this->num_iscr[$id_c];
            } else {
                $this->num_iscr[$id_c] = 1;
            }

            if ($fisrt_access === null) {
                //never enter
                if (isset($this->num_nobegin[$id_c])) {
                    ++$this->num_nobegin[$id_c];
                } else {
                    $this->num_nobegin[$id_c] = 1;
                }
            } elseif ($date_complete === null) {
                //enter
                if (isset($this->num_itinere[$id_c])) {
                    ++$this->num_itinere[$id_c];
                } else {
                    $this->num_itinere[$id_c] = 1;
                }
            } else {
                //complete
                if (isset($this->num_end[$id_c])) {
                    ++$this->num_end[$id_c];
                } else {
                    $this->num_end[$id_c] = 1;
                }
            }
            $effective_user[] = $id_u;
        }
        if (!empty($effective_user)) {
            $query_time = '
			SELECT idCourse, SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)) 
			FROM ' . $GLOBALS['prefix_lms'] . '_tracksession 
			GROUP BY idCourse ';

            $re_time = sql_query($query_time);
            while (list($id_c, $time_num) = sql_fetch_row($re_time)) {
                $this->time_in_course[$id_c] = $time_num;
            }
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
        return 'course-report-connector';
    }

    /**
     * Return the description of the connector.
     **/
    public function get_type_description()
    {
        return 'connector for course statistics';
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
     *               - DOCEBOIMPORT_COLNAME => string the name of the column
     *               - DOCEBOIMPORT_COLID => string the id of the column (optional,
     *               same as COLNAME if not given)
     *               - DOCEBOIMPORT_COLMANDATORY => bool TRUE if col is mandatory
     *               - DOCEBOIMPORT_DATATYPE => the data type of the column
     *               - DOCEBOIMPORT_DEFAULT => the default value for the column (Optional)
     *               For readonly connectos only 	DOCEBOIMPORT_COLNAME and DOCEBOIMPORT_DATATYPE
     *               are required
     **/
    public function get_cols_descripor()
    {
        $lang = DoceboLanguage::createInstance('userreport', 'lms');

        $col_descriptor = [];
        foreach ($this->all_cols as $k => $col) {
            $col_descriptor[] = [
                DOCEBOIMPORT_COLNAME => $lang->def('_' . strtoupper($col[0])),
                DOCEBOIMPORT_COLID => $col[0],
                DOCEBOIMPORT_COLMANDATORY => false,
                DOCEBOIMPORT_DATATYPE => $col[1],
                DOCEBOIMPORT_DEFAULT => ($in = array_search($col[0], $this->default_cols) === false
                                                    ? ''
                                                    : $this->default_cols[$in]),
            ];
        }

        return $col_descriptor;
    }

    public function get_first_row()
    {
        $row = false;
        if ($this->first_row) {
            return $this->first_row;
        }
        if (empty($this->id_courses)) {
            return $row;
        }

        $idc = key($this->id_courses);
        $course_info = current($this->id_courses);

        $row = [
            $course_info['code'],
            $course_info['name'], ];
        if (isset($this->num_iscr[$idc])) {
            $row[] = $this->num_iscr[$idc];

            //no begin course
            if (isset($this->num_nobegin[$idc])) {
                $perc = (($this->num_nobegin[$idc] / $this->num_iscr[$idc]) * 100);
                $row[] = $this->num_nobegin[$idc];
                $row[] = number_format($perc, 2, '.', '') . '%';
            } else {
                $row[] = '0';
                $row[] = '0%';
            }

            //begin
            if (isset($this->num_itinere[$idc])) {
                $perc = (($this->num_itinere[$idc] / $this->num_iscr[$idc]) * 100);
                $row[] = $this->num_itinere[$idc];
                $row[] = number_format($perc, 2, '.', '') . '%';
            } else {
                $row[] = '0';
                $row[] = '0%';
            }

            //end course
            if (isset($this->num_end[$idc])) {
                $perc = (($this->num_end[$idc] / $this->num_iscr[$idc]) * 100);
                $row[] = $this->num_end[$idc];
                $row[] = number_format($perc, 2, '.', '') . '%';
            } else {
                $row[] = '0';
                $row[] = '0%';
            }
            if (isset($this->time_in_course[$idc])) {
                $row[] = ((int) ($this->time_in_course[$idc] / 3600)) . 'h '
                    . substr('0' . ((int) (($this->time_in_course[$idc] % 3600) / 60)), -2) . 'm '
                    . substr('0' . ((int) ($this->time_in_course[$idc] % 60)), -2) . 's ';
            } else {
                $row[] = '-';
            }
        } else {
            $row[] = '0';
            $row[] = '0';
            $row[] = '0%';
            $row[] = '0';
            $row[] = '0%';
            $row[] = '0';
            $row[] = '0%';
            $row[] = '-';
        }
        $this->_first_row = $row;

        return $row;
    }

    public function get_next_row()
    {
        $row = [];
        $course_info = next($this->id_courses);
        if (!$course_info) {
            $this->_readed_end = true;

            return false;
        }
        ++$this->row_index;
        $idc = key($this->id_courses);

        $row = [
            $course_info['code'],
            $course_info['name'], ];
        if (isset($this->num_iscr[$idc])) {
            $row[] = $this->num_iscr[$idc];

            //no begin course
            if (isset($this->num_nobegin[$idc])) {
                $perc = (($this->num_nobegin[$idc] / $this->num_iscr[$idc]) * 100);
                $row[] = $this->num_nobegin[$idc];
                $row[] = number_format($perc, 2, '.', '') . '%';
            } else {
                $row[] = '0';
                $row[] = '0%';
            }

            //begin
            if (isset($this->num_itinere[$idc])) {
                $perc = (($this->num_itinere[$idc] / $this->num_iscr[$idc]) * 100);
                $row[] = $this->num_itinere[$idc];
                $row[] = number_format($perc, 2, '.', '') . '%';
            } else {
                $row[] = '0';
                $row[] = '0%';
            }

            //end course
            if (isset($this->num_end[$idc])) {
                $perc = (($this->num_end[$idc] / $this->num_iscr[$idc]) * 100);
                $row[] = $this->num_end[$idc];
                $row[] = number_format($perc, 2, '.', '') . '%';
            } else {
                $row[] = '0';
                $row[] = '0%';
            }
            if (isset($this->time_in_course[$idc])) {
                $row[] = ((int) ($this->time_in_course[$idc] / 3600)) . 'h '
                    . substr('0' . ((int) (($this->time_in_course[$idc] % 3600) / 60)), -2) . 'm '
                    . substr('0' . ((int) ($this->time_in_course[$idc] % 60)), -2) . 's ';
            } else {
                $row[] = '-';
            }
        } else {
            $row[] = '0';
            $row[] = '0';
            $row[] = '0%';
            $row[] = '0';
            $row[] = '0%';
            $row[] = '0';
            $row[] = '0%';
            $row[] = '-';
        }

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
class DoceboConnectorCourseReportUI extends DoceboConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public function DoceboConnectorCourseReportUI(&$connector)
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

    public function get_html()
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

        return $out;
    }
}

function coursereport_factory()
{
    return new DoceboConnectorCourseReport([]);
}
