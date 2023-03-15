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

/**
 * class for define user report connection.
 *
 * @version 	1.0
 *
 * @author		Pirovano Fabio <fabio (@) docebo (.) com>
 **/
class DoceboConnectorUserReport extends DoceboConnector
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
    public $time_list;
    public $session_list;
    public $lastaccess_list;

    // name, type
    public $all_cols = [
        ['id_user', 'text'],
        ['login', 'text'],
        ['user_name', 'text'],
        ['id_course', 'text'],
        ['category', 'text'],
        ['code', 'text'],
        ['course', 'text'],
        ['course_status', 'text'],
        ['subscribe_date', 'datetime'],
        ['begin_date', 'datetime'],
        ['complete_date', 'datetime'],
        ['user_status', 'text'],
        ['score', 'text'],
    ];

    public $default_cols = ['id_user' => '0',
                                'login' => '',
                                'user_name' => '',
                                'id_course' => '0',
                                'category' => '',
                                'code' => '',
                                'course' => '',
                                'course_status' => '',
                                'subscribe_date' => '0000-00-00 00:00:00',
                                'begin_date' => '0000-00-00 00:00:00',
                                'complete_date' => '0000-00-00 00:00:00',
                                'user_status' => '',
                                'score' => '', ];

    /**
     * This constructor require the source file name.
     *
     * @param array $params the array of params
     *                      - 'filename' => name of the file (required)
     *                      - 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
     *                      - 'separator' => string a char with the fields separator (Optional, default = ,)
     **/
    public function DoceboConnectorUserReport($params)
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
        return new DoceboConnectorUserReportUI($this);
    }

    /**
     * execute the connection to source.
     */
    public function connect()
    {
        $this->lang = DoceboLanguage::createInstance('ru_report');

        $query_course_user = "
                SELECT u.idst, REPLACE(u.userid,'/','') login, CONCAT (u.firstname, ' ', u.lastname) user_name , c.idCourse id_course,
                cat.path category, c.code, c.name course, c.status course_status,
                cu.date_inscr subscribe_date,
                cu.date_first_access  begin_date,
                cu.date_complete  complete_date,
                cu.status  user_status,
                cu.score_given  score 
                FROM  learning_courseuser AS cu 
                join learning_course as c on cu.idCourse = c.idCourse
                join core_user as u on cu.idUser = u.idst
                left join learning_category as cat on c.idCategory = cat.idCategory
         ";

        $this->_query_result = sql_query($query_course_user);

        if (!$this->_query_result) {
            $this->last_error = sql_error();

            return false;
        }

        return true;
    }

    /**
     * execute the close of the connection.
     */
    public function close()
    {
        return true;
    }

    /**
     * Return the type of the connector.
     **/
    public function get_type_name()
    {
        return 'user-report-connector';
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

    public function _converStatusCourse($status)
    {
        switch ($status) {
            case '0': return $this->lang->def('_NOACTIVE');
            case '1': return $this->lang->def('_ACTIVE');
            case '2': return $this->lang->def('_DEACTIVATE');
        }
    }

    public function _convertStatusUser($status)
    {
        switch ($status) {
            case _CUS_SUBSCRIBED: return $this->lang->def('_USER_STATUS_SUBS');
            case _CUS_BEGIN: return $this->lang->def('_USER_STATUS_BEGIN');
            case _CUS_END: return $this->lang->def('_USER_STATUS_END');
            case _CUS_SUSPEND: return $this->lang->def('_USER_STATUS_SUSPEND');
        }
    }

    public function get_first_row()
    {
        if ($this->first_row) {
            return $this->first_row;
        }
        if (!$this->_query_result) {
            return false;
        }
        $result = sql_fetch_row($this->_query_result);
        if (!$result) {
            $this->_readed_end = true;

            return [];
        }
        ++$this->row_index;

        list($id_user, $login, $user_name, $id_course, $category,
            $code, $name, $course_status, $subscribe_date, $date_first_access, $date_complete, $status_user, $score) = $result;

        $row = [
            $id_user,
            $login,
            $user_name,
            $id_course,
            $category,
            $code,
            $name,
            $this->_converStatusCourse($course_status),
            $subscribe_date,
            ($date_first_access !== null ? $date_first_access : '&nbsp;'),
            ($date_complete !== null ? $date_complete : '&nbsp;'),
            $this->_convertStatusUser($status_user),
            $score,
        ];

        return $row;
    }

    public function get_next_row()
    {
        //$this->export_field_list

        $row = [];
        if (!$this->_query_result) {
            return false;
        }
        if (!$result = sql_fetch_row($this->_query_result)) {
            $this->_readed_end = true;

            return false;
        }
        ++$this->row_index;

        list($id_user, $login, $user_name, $id_course, $category,
            $code, $name, $course_status, $subscribe_date, $date_first_access, $date_complete, $status_user, $score) = $result;

        $row = [
            $id_user,
            $login,
            $user_name,
            $id_course,
            $category,
            $code,
            $name,
            $this->_converStatusCourse($course_status),
            $subscribe_date,
            ($date_first_access !== null ? $date_first_access : '&nbsp;'),
            ($date_complete !== null ? $date_complete : '&nbsp;'),
            $this->_convertStatusUser($status_user),
            $score,
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
class DoceboConnectorUserReportUI extends DoceboConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public function DoceboConnectorUserReportUI(&$connector)
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

function userreport_factory()
{
    return new DoceboConnectorUserReport([]);
}
