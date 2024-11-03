<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from FORMA 4.0.5 CE 2008-2012 (c) FORMA
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once dirname(__FILE__) . '/lib.connector.php';

/**
 * Class for define FORMA users connection to data source.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) FORMA (.) com>
 **/
class FormaConnectorFormaAdmin extends FormaConnector
{
    public $last_error = '';

    public $cols_descriptor = [];

    public $name = '';
    public $description = '';

    /**
     * @var DbConn
     */
    public $db = null;
    public $aclm = false;
    public $data = [];
    public $org_chart = [];
    public $root_oc = false;
    public $root_ocd = false;
    public $levels = [];

    public $m_ar = false;
    public $admin_profiles = [];

    public $preference = false;

    public $m_pr = false;
    public $public_profiles = [];

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
        if ($params !== null) {
            $this->set_config($params);
        }

        $this->aclm = \FormaLms\lib\Forma::getAclManager();
        $this->cols_descriptor = [
            [
                FORMAIMPORT_COLNAME => Lang::t('_TYPE'),
                FORMAIMPORT_COLID => 'admin_type',
                FORMAIMPORT_COLMANDATORY => true,
                FORMAIMPORT_DATATYPE => 'text',
                FORMAIMPORT_DEFAULT => '',
            ],
            [
                FORMAIMPORT_COLNAME => Lang::t('_PROFILE'),
                FORMAIMPORT_COLID => 'profile',
                FORMAIMPORT_COLMANDATORY => true,
                FORMAIMPORT_DATATYPE => 'text',
                FORMAIMPORT_DEFAULT => '',
            ],
            [
                FORMAIMPORT_COLNAME => Lang::t('_USERNAME'),
                FORMAIMPORT_COLID => 'username',
                FORMAIMPORT_COLMANDATORY => true,
                FORMAIMPORT_DATATYPE => 'text',
                FORMAIMPORT_DEFAULT => '',
            ],
            [
                FORMAIMPORT_COLNAME => Lang::t('_FOLDER_NAME'),
                FORMAIMPORT_COLID => 'folder_name',
                FORMAIMPORT_COLMANDATORY => false,
                FORMAIMPORT_DATATYPE => 'text',
                FORMAIMPORT_DEFAULT => 'root',
            ],
            [
                FORMAIMPORT_COLNAME => Lang::t('_COURSEPATH'),
                FORMAIMPORT_COLID => 'course_path',
                FORMAIMPORT_COLMANDATORY => false,
                FORMAIMPORT_DATATYPE => 'text',
                FORMAIMPORT_DEFAULT => 'root',
            ],
            [
                FORMAIMPORT_COLNAME => Lang::t('_CATALOGUE'),
                FORMAIMPORT_COLID => 'course_cat',
                FORMAIMPORT_COLMANDATORY => false,
                FORMAIMPORT_DATATYPE => 'text',
                FORMAIMPORT_DEFAULT => 'root',
            ],
        ];
    }

    public function get_configUI()
    {
        return new FormaConnectorFormaAdminUI($this);
    }

    public function get_config()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
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
    }

    public function connect()
    {
        $this->db = \FormaLms\db\DbConn::getInstance();

        $this->org_chart = [];

        // Cache org_chart group
        $query = ' SELECT oc.id_dir, oc.translation, oct.idst_oc, oct.idst_ocd '
                . ' FROM %adm_org_chart AS oc '
                . '	JOIN %adm_org_chart_tree AS oct '
                . '		ON (oc.id_dir = oct.idOrg) '
                . " WHERE lang_code = '" . Lang::get() . "'";
        $result = $this->db->query($query);

        while ($o = $this->db->fetch_obj($result)) {
            $name_index = strtolower(trim(addslashes($o->translation)));
            $this->org_chart[$name_index] = $o;
        }

        $tmp = $this->aclm->getGroup(false, '/oc_0');
        $this->root_oc = $tmp[0];

        $tmp = $this->aclm->getGroup(false, '/ocd_0');
        $this->root_ocd = $tmp[0];

        // Cache user levels
        $this->levels = $this->aclm->getAdminLevels();

        $this->preference = new AdminPreference();

        // Cache admin profiles
        $this->m_ar = new AdminrulesAdm();
        $tmp = $this->m_ar->getGroupForDropdown();
        unset($tmp[0]);
        $this->admin_profiles = array_flip($tmp);

        return true;
    }

    public function close()
    {
    }

    public function get_type_name()
    {
        return 'forma-admin';
    }

    public function get_type_description()
    {
        return 'Connector to forma admin';
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
        return count($this->cols_descriptor);
    }

    public function get_cols_descripor()
    {
        return $this->cols_descriptor;
    }

    public function get_first_row()
    {
        return false;
    }

    public function get_next_row()
    {
        return false;
    }

    public function is_eof()
    {
        return false;
    }

    public function get_row_index()
    {
        return $this->index;
    }

    /**
     * @return int the number of mandatory columns to import
     **/
    public function get_tot_mandatory_cols()
    {
        $result = [];
        foreach ($this->cols_descriptor as $col) {
            if ($col[FORMAIMPORT_COLMANDATORY]) {
                $result[] = $col;
            }
        }

        return count($result);
    }

    public function get_row_bypk($pk)
    {
        return false;
    }

    public function add_row($row, $pk)
    {
        foreach ($pk as $key => $val) {
            $pk[$key] = addslashes(trim($val));
        }
        foreach ($row as $key => $val) {
            $row[$key] = addslashes(trim($val));
        }

        $user = $this->aclm->getUser(false, $pk['username']);
        if ($user != false) {
            // admin type and profile ?
            switch ($row['admin_type']) {
                case 'admin':
                    $level = $this->levels[ADMIN_GROUP_ADMIN];
                    if (!isset($this->admin_profiles[$row['profile']])) {
                        $this->last_error = 'Profile not found : ' . $row['profile'] . '<br />';

                        return false;
                    }

                    $this->db->start_transaction();

                    //remove from the user group
                    $this->aclm->removeFromGroup($this->levels[ADMIN_GROUP_USER], $user[ACL_INFO_IDST]);

                    //add to the correct admin group
                    $this->aclm->addToGroup($level, $user[ACL_INFO_IDST]);

                    $idst_profile = $this->admin_profiles[$row['profile']];
                    $this->m_ar->saveSingleAdminAssociation($idst_profile, $user[ACL_INFO_IDST]);
                 break;
            }

            // associated org_chart ?
            if ($row['folder_name'] == 'root' && isset($this->root_ocd)) {
                $this->preference->saveAdminTree($user[ACL_INFO_IDST], [$this->root_ocd]);
            } elseif (isset($this->org_chart[strtolower($row['folder_name'])])) {
                $oc = $this->org_chart[strtolower($row['folder_name'])];
                $this->preference->saveAdminTree($user[ACL_INFO_IDST], [$oc->idst_ocd]);
            } else {
                $this->last_error = 'Users to manage not found <br />';
            }

            // associated courses by path
            if ($row['course_path'] == 'root') {
                $this->preference->saveAdminCourse($user[ACL_INFO_IDST], [0], [], []);
            } elseif ($row['course_path'] != '') {
                $this->preference->saveAdminCourse($user[ACL_INFO_IDST], [], [$this->getIDbyName($row['course_path'], 'course_path')], []);
            }

            // associated courses by catalogue
            if ($row['course_cat'] == 'root') {
                $this->preference->saveAdminCourse($user[ACL_INFO_IDST], [0], [], []);
            } elseif ($row['course_cat'] != '') {
                $this->preference->saveAdminCourse($user[ACL_INFO_IDST], [], [], [$this->getIDbyName($row['course_cat'], 'course_cat')]);
            }

            $this->db->commit();

            return true;
        } else {
            $this->last_error = 'User not found : ' . $pk['username'] . '<br />';

            return false;
        }
    }

    public function delete_bypk($pk)
    {
    }

    public function delete_all_filtered($arr_pk)
    {
    }

    public function delete_all_notinserted()
    {
    }

    public function get_error()
    {
        return $this->last_error;
    }

    private function getIDbyName($strName, $typeC)
    {
        if ($typeC == 'course_cat') {
            $sql = "Select idCatalogue from learning_catalogue where name='" . addslashes($strName) . "'";
        }

        if ($typeC == 'course_path') {
            $sql = "Select id_path from learning_coursepath where path_name='" . addslashes($strName) . "'";
        }

        list($idRet) = sql_fetch_row(sql_query($sql));

        return $idRet;
    }
}

/**
 * The configurator for FORMAadmin connectors.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) FORMA (.) com>
 **/
class FormaConnectorFormaAdminUI extends FormaConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public function __construct($connector)
    {
        $this->connector = $connector;
    }

    public function _get_base_name()
    {
        return 'FORMAadminuiconfig';
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
            // $this->post_params['org_chart_destination'] = $this->org_chart_destination;
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
        switch ($this->post_params['step']) {
            case '0':
                $this->_set_step_info('1', '0', false, false, true);
            break;
        }
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
        $out = $this->form->getTextfield($this->lang->def('_NAME'),
                                            $this->_get_base_name() . '_name',
                                            $this->_get_base_name() . '[name]',
                                            255,
                                            $this->post_params['name']);

        $out .= $this->form->getSimpleTextarea($this->lang->def('_DESCRIPTION'),
                                            $this->_get_base_name() . '_description',
                                            $this->_get_base_name() . '[description]',
                                            $this->post_params['description']);

        return $out;
    }
}

function formaadmin_factory()
{
    return new FormaConnectorFormaAdmin([]);
}
