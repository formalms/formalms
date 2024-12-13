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
require_once _base_ . '/lib/lib.eventmanager.php';

/**
 * class for define edition subscription connection to data source.
 *
 * @version 	1.0
 **/
class ConnectorEditionUser extends FormaConnector
{
    public $last_error = '';

    public $acl_man = false;

    public $sub_man = false;

    // name, type
    public $all_cols = [
        ['course_code', 'text'],
        ['edition_code', 'text'],
        ['userid', 'text'],
        ['level', 'int'],
        ['date_subscription', 'date'],
        ['last_finish', 'text'],
    ];

    public $mandatory_cols = ['course_code', 'edition_code', 'userid', 'level'];

    public $default_cols = ['course_code' => '',
                                'edition_code' => '',
                                'userid' => '',
                                'level' => '3',
                                'date_subscription' => false, ];

    public $name = '';
    public $description = '';

    public $readwrite = 1; // read = 1, write = 2, readwrite = 3
    public $sendnotify = 1; // send notify = 1, don't send notify = 2
    public $on_delete = 1;  // unactivate = 1, delete = 2

    public $arr_pair_inserted = [];

    public $first_row_header = '1';

    public $cache = ['courses' => [], 'editions' => [], 'users' => []];
    public bool $_readed_end;
    public $first_row;
    public array $all_data;
    public $tot_row;
    public int $position;
    public $today;
    /**
     * @var FormaLanguage|mixed
     */
    public mixed $lang;

    /**
     * constructor.
     *
     * @param array params
     **/
    public function __construct($params)
    {
        require_once _lms_ . '/lib/lib.edition.php';

        $this->acl_man = new FormaACLManager();
        $this->sub_man = new EditionManager();

        if ($params === null) {
            return;
        } else {
            $this->set_config($params);
        }	// connection
    }

    /**
     * set configuration.
     *
     * @param array $params
     **/
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
        if (isset($params['sendnotify'])) {
            $this->sendnotify = $params['sendnotify'];
        }
        //if( isset($params['on_delete']) )			$this->on_delete = $params['on_delete'];
    }

    /**
     * get configuration.
     *
     * @return array
     **/
    public function get_config()
    {
        return ['name' => $this->name,
                        'description' => $this->description,
                        'readwrite' => $this->readwrite,
                        'sendnotify' => $this->sendnotify,
                        //'on_delete' => $this->on_delete,
                        'first_row_header' => $this->first_row_header, ];
    }

    /**
     * get configuration UI.
     *
     * @return FormaConnectorUI
     **/
    public function get_configUI()
    {
        return new ConnectorUI_EditionUserUI($this);
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

        $query = 'SELECT COUNT(*) FROM %lms_course_editions_user';

        list($tot_row) = sql_fetch_row(sql_query($query));

        $this->tot_row = $tot_row;

        $query = ' SELECT'
                . '     c.code AS course_code'
                . '   , ce.code AS edition_code'
                . '   , u.userid'
                . '   , cu.level'
                . '   , ceu.date_subscription'
                . '   , ceu.date_complete'
                . ' FROM %lms_course_editions_user ceu'
                . ' INNER JOIN %lms_course_editions ce'
                . '     ON ceu.id_edition = ce.id_edition'
                . ' INNER JOIN %lms_courseuser cu'
                . '     ON ce.id_course = cu.idCourse'
                . '         AND ceu.id_user = cu.idUser'
                . ' INNER JOIN %lms_course c'
                . '     ON cu.idCourse = c.idCourse'
                . ' INNER JOIN %adm_user u'
                . '     ON cu.idUser = u.idst';

        $result = sql_query($query);

        $data = [];

        $counter = 0;

        if ($this->first_row_header) {
            $data[$counter][] = 'course_code';
            $data[$counter][] = 'edition_code';
            $data[$counter][] = 'userid';
            $data[$counter][] = 'level';
            $data[$counter][] = 'date_subscription';
            $data[$counter][] = 'last_finish';

            ++$counter;
        }

        while ($row = sql_fetch_array($result)) {
            $data[$counter][] = $row[0];
            $data[$counter][] = $row[1];
            $data[$counter][] = substr($row[2], 1);
            $data[$counter][] = $row[3];
            $data[$counter][] = $row[4];
            $data[$counter][] = $row[5];

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
    }

    public function get_type_name()
    {
        return 'editionuser';
    }

    public function get_type_description()
    {
        return 'connector to user edition subscription';
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

    public function is_raw_producer()
    {
        return false;
    }

    public function get_tot_cols()
    {
        return count($this->all_cols);
    }

    public function get_cols_descripor()
    {
        $lang = FormaLanguage::createInstance('subscribe', 'lms');

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

    public function get_row_bypk($pk)
    {
        if (!isset($pk['course_code']) || !isset($pk['edition_code']) || !isset($pk['userid'])) {
            return false;
        }

        $course_code = $pk['course_code'];
        $edition_code = $pk['edition_code'];
        $userid = $pk['userid'];

        if (!isset($this->cache['courses'][$course_code])) {
            $query = "SELECT idCourse FROM %lms_course WHERE code = '$course_code'";
            list($id_course) = sql_fetch_row(sql_query($query));
            $this->cache['courses'][$course_code] = $id_course;
        } else {
            $id_course = $this->cache['courses'][$course_code];
        }

        if (!isset($this->cache['editions'][$edition_code])) {
            $query = "SELECT id_edition FROM %lms_course_editions WHERE code = '$edition_code' AND id_course = $id_course";
            list($id_edition) = sql_fetch_row(sql_query($query));
            $this->cache['editions'][$edition_code] = $id_edition;
        } else {
            $id_edition = $this->cache['editions'][$edition_code];
        }

        if (!isset($this->cache['users'][$userid])) {
            $id_user = $this->acl_man->getUser(false, addslashes($userid))[ACL_INFO_IDST];
            $this->cache['users'][$userid] = $id_user;
        } else {
            $id_user = $this->cache['users'][$userid];
        }

        return [
                'course' => $id_course, 'edition' => $id_edition, 'user' => $id_user,
        ];
    }

    public function add_row($row, $pk)
    {
        $arr_id = $this->get_row_bypk($pk);

        if (!$arr_id) {
            $this->last_error = 'missing mandatory pk ' . sql_error();

            return false;
        }

        if (!$arr_id['course']) {
            $this->last_error = 'notA found the requested course ' . sql_error();

            return false;
        }

        if (!$arr_id['edition']) {
            $this->last_error = 'not found the requested edition ' . sql_error();

            return false;
        }

        if (!$arr_id['user']) {
            $this->last_error = 'not found the requested user ' . sql_error();

            return false;
        }

        if (!$row['level']) {
            $row['level'] = 3;
        }

        $re_ins = $this->sub_man->subscribeUserToEdition($arr_id['user'], $arr_id['course'], $arr_id['edition'], $row['level'], 0, Format::dateDb($row['date_subscription'], 'date'), Format::dateDb($row['last_finish'], 'date'));

        if ($re_ins === 'jump') {
            return true;
        }

        return $re_ins;
    }

    public function get_error()
    {
        return $this->last_error;
    }
}

class ConnectorUI_EditionUserUI extends FormaConnectorUI
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
        return 'editionuseruiconfig';
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
                                                    $this->lang->def('_WRITE') => '2',
                                                    $this->lang->def('_READWRITE') => '3', ],
                                            $this->post_params['readwrite']);
        // ---- access type read/write -----
        $out .= $this->form->getRadioSet($this->lang->def('_SENDNOTIFY'),
                                            $this->_get_base_name() . '_sendnotify',
                                            $this->_get_base_name() . '[sendnotify]',
                                            [$this->lang->def('_SEND') => '1',
                                                    $this->lang->def('_DONTSEND') => '2', ],
                                            $this->post_params['sendnotify']);

        $out .= $this->form->getRadioSet($this->lang->def('_FIRST_ROW_HEADER'),
                                            $this->_get_base_name() . '_first_row_header',
                                            $this->_get_base_name() . '[first_row_header]',
                                            [$this->lang->def('_YES') => '1',
                                                    $this->lang->def('_NO') => '0', ],
                                            $this->post_params['first_row_header']);

        return $out;
    }
}

function editionuser_factory()
{
    return new ConnectorEditionUser([]);
}
