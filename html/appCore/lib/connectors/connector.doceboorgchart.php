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

require_once dirname(__FILE__) . '/lib.connector.php';

/**
 * class for define docebo organization chart connection to data source.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectorFormaOrgChart extends FormaConnector
{
    public $last_error = '';
    public $all_cols = ['idOrg', 'idParent', 'path', 'level'];
    public $mandatory_cols = ['path'];
    public $default_cols = [];
    public $ignore_cols = ['idOrg', 'idParent', 'level'];
    public $valid_filed_type = ['textfield', 'date', 'dropdown', 'yesno'];
    public $cols_descriptor = null;
    public $dbconn = null;
    public $tree = 0;			// idst where to insert the imported tree
    public $tree_desc = 0;		// the descendant idst
    public $org_chart_destination = 0;
    public $default_lang = '';

    public $readwrite = 0; // read = 1, write = 2, readwrite = 3
    public $canceled = 1;  // don't remove = 1, remove = 2

    public $name = '';
    public $description = '';

    public $directory = null;
    public $tree_view = null;

    public $arr_folders = [];

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
        if ($params === null) {
            return;
        }	// connector
        else {
            $this->set_config($params);
        }	// connection
    }

    public function get_config()
    {
        return ['tree' => $this->tree,
                        'canceled' => $this->canceled,
                        'readwrite' => $this->readwrite,
                        'name' => $this->name,
                        'description' => $this->description,
                        'org_chart_destination' => $this->org_chart_destination,
                        'default_lang' => $this->default_lang, ];
    }

    public function set_config($params)
    {
        if (isset($params['tree'])) {
            $this->tree = $params['tree'];
        }
        if (isset($params['canceled'])) {
            $this->canceled = $params['canceled'];
        }
        if (isset($params['readwrite'])) {
            $this->readwrite = $params['readwrite'];
        }
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['org_chart_destination'])) {
            $this->org_chart_destination = $params['org_chart_destination'];
        }
        if (isset($params['default_lang'])) {
            $this->default_lang = $params['default_lang'];
        }
    }

    public function get_configUI()
    {
        return new FormaConnectorFormaOrgChartUI($this);
    }

    public function connect()
    {
        require_once _base_ . '/lib/lib.userselector.php';

        require_once _adm_ . '/modules/org_chart/tree.org_chart.php';
        $this->directory = new UserSelector();
        //$this->tree_view = $this->directory->getTreeView_OrgView();
        $orgDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'] . '_org_chart_tree');
        $this->tree_view = new TreeView_OrgView($orgDb, 'organization_chart', FormaLms\lib\Get::sett('title_organigram_chart'));
        $this->tree_view->aclManager = \FormaLms\lib\Forma::getAclManager();

        list($this->tree_desc) = $this->tree_view->tdb->getDescendantsSTFromST([$this->tree]);

        require_once _adm_ . '/lib/lib.field.php';
        // load language for fields names
        $lang_dir = FormaLanguage::createInstance('admin_directory', 'framework');
        $fl = new FieldList();
        $fl->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);
        $arr_fields = $fl->getAllFields();

        $this->cols_descriptor = null;
        if ($this->dbconn === null) {
            $this->dbconn = $GLOBALS['dbConn'];
        }

        $table_fields = [['Field' => 'idOrg', 'Type' => 'text'],
                                ['Field' => 'idParent', 'Type' => 'text'],
                                ['Field' => 'path', 'Type' => 'text'],
                                ['Field' => 'level', 'Type' => 'text'],
        ];

        $this->cols_descriptor = [];
        foreach ($table_fields as $field_info) {
            if (!in_array($field_info['Field'], $this->ignore_cols)) {
                $mandatory = in_array($field_info['Field'], $this->mandatory_cols);
                if (isset($this->default_cols[$field_info['Field']])) {
                    $this->cols_descriptor[] =
                                [DOCEBOIMPORT_COLNAME => $lang_dir->def('_DIRECTORY_FILTER_' . $field_info['Field']),
                                        DOCEBOIMPORT_COLID => $field_info['Field'],
                                        DOCEBOIMPORT_COLMANDATORY => $mandatory,
                                        DOCEBOIMPORT_DATATYPE => $field_info['Type'],
                                        DOCEBOIMPORT_DEFAULT => $this->default_cols[$field_info['Field']],
                                ];
                } else {
                    $this->cols_descriptor[] =
                                [DOCEBOIMPORT_COLNAME => $lang_dir->def('_DIRECTORY_FILTER_' . $field_info['Field']),
                                        DOCEBOIMPORT_COLID => $field_info['Field'],
                                        DOCEBOIMPORT_COLMANDATORY => $mandatory,
                                        DOCEBOIMPORT_DATATYPE => $field_info['Type'],
                                ];
                }
            }
        }

        foreach ($arr_fields as $field_id => $field_info) {
            if (in_array($field_info[FIELD_INFO_TYPE], $this->valid_filed_type)) {
                $this->cols_descriptor[] =
                            [DOCEBOIMPORT_COLNAME => $field_info[FIELD_INFO_TRANSLATION],
                                    DOCEBOIMPORT_COLID => $field_id,
                                    DOCEBOIMPORT_COLMANDATORY => false,
                                    DOCEBOIMPORT_DATATYPE => 'text',
                            ];
            }
        }

        $this->tree_view->tdb->setFolderLang($this->default_lang);
        $arr_foldersid = $this->tree_view->tdb->getFoldersIdFromIdst([$this->tree]);
        $folderid = $arr_foldersid[$this->tree];

        $root_folder = $this->tree_view->tdb->getFolderById($folderid);
        $arr_id = $this->tree_view->tdb->getDescendantsId($root_folder);
        $this->arr_folders = [];
        if ($arr_id !== false) {
            $coll_folders = $this->tree_view->tdb->getFoldersCollection($arr_id);
            // make the new structure
            $curr_path = [];
            while (($folder = $coll_folders->getNext()) !== false) {
                $curr_path = array_slice($curr_path, 0, $folder->level - $root_folder->level - 1);
                $curr_path[] = $folder->otherValues[ORGDB_POS_TRANSLATION];
                $this->arr_folders[implode('/', $curr_path)] = ['id' => $folder->id, 'inserted' => false];
            }
        }

        return true;
    }

    public function close()
    {
        $this->directory = null;
        $this->tree_view = null;
        $this->cols_descriptor = null;
        $this->arr_folders = [];
    }

    public function get_type_name()
    {
        return 'docebo-orgchart';
    }

    public function get_type_description()
    {
        return 'connector to docebo organization chart';
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
        return false;
    }

    /**
     * @return int the number of mandatory columns to import
     **/
    public function get_tot_mandatory_cols()
    {
        $result = [];
        foreach ($this->cols_descriptor as $col) {
            if ($col[DOCEBOIMPORT_COLMANDATORY]) {
                $result[] = $col;
            }
        }

        return count($result);
    }

    public function get_row_bypk($pk)
    {
        if (isset($this->arr_folders[$pk['path']])) {
            return $this->arr_folders[$pk['path']];
        } else {
            return false;
        }
    }

    public function add_row($row, $pk)
    {
        $path = $row['path'];
        $path_tokens = explode('/', $path);
        $parent_path = implode('/', array_slice($path_tokens, 0, -1));
        $name = $path_tokens[count($path_tokens) - 1];

        $arr_folder = $this->get_row_bypk(['path' => $path]);
        if ($parent_path == '') {
            $this->tree_view->tdb->setFolderLang($this->default_lang);
            $parent_id = (int) $this->org_chart_destination;
        } else {
            $arr_parent_folder = $this->get_row_bypk(['path' => $parent_path]);
            $parent_id = $arr_parent_folder['id'];
        }

        // ---- Extract extra languages title
        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        if (isset($row['lang_titles'])) {
            $folderName = addslashes($row['lang_titles']);
        } else {
            $folderName = [];
        }
        foreach ($array_lang as $lang) {
            if (!isset($folderName[$lang])) {
                $folderName[$lang] = addslashes($name);
            }
        }

        if ($arr_folder === false) {
            require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.usermanager.php');
            $umodel = new UsermanagementAdm();
            $id = $umodel->addFolder($parent_id, $folderName, $row['code']);

            $this->arr_folders[$path] = ['id' => $id, 'inserted' => true];
        } else {
            $this->tree_view->tdb->updateFolderByIdTranslation($arr_folder['id'], $folderName);
            $this->arr_folders[$path]['inserted'] = true;
        }

        // ---- Add custom fields
        if (isset($row['custom_fields'])) {
            $this->_add_custom_fields($this->arr_folders[$path]['id'], $row['custom_fields']);
        } else {
            $this->_add_custom_fields($this->arr_folders[$path]['id'], []);
        }

        return true;
    }

    public function delete_bypk($pk)
    {
        $arr_folder = $this->get_row_bypk($pk);
        if ($arr_folder === false) {
            return false;
        } else {
            if ($this->canceled == '2') {
                $folder = $this->tree_view->tdb->getFolderById($arr_folder['id']);
                if ($folder !== null) {	// already deleted
                    $this->tree_view->tdb->deleteTreeById($arr_folder['id']);
                }
            }

            return true;
        }
    }

    public function delete_all_filtered($arr_pk)
    {
        // retrieve all users idst
        if ($this->canceled == '1') {
            return true;
        }
        foreach ($this->arr_folders as $path => $arr_folder) {
            if (!in_array($path, $arr_pk)) {
                $this->delete_bypk(['path' => $path]);
            }
        }
    }

    public function delete_all_notinserted()
    {
        if ($this->canceled == '1') {
            return 0;
        }
        $counter = 0;
        foreach ($this->arr_folders as $path => $arr_folder) {
            if ($arr_folder['inserted'] === false) {
                $this->delete_bypk(['path' => $path]);
                ++$counter;
            }
        }

        return $counter;
    }

    public function get_error()
    {
        return $this->last_error;
    }

    /**
     * @param string $id_folder  folder destination of fields
     * @param array  $arr_fields an array with fields to attach to folder
     *                           any element of folder is an array:
     *                           key => array( 'fvalue', 'mandatory' )
     *                           - key is the field name in current language
     *                           - fvalue is the value of the field
     *                           - mandatory is TRUE if this field is mandatory
     */
    public function _add_custom_fields($id_folder, $arr_fields)
    {
        require_once _adm_ . '/lib/lib.field.php';

        $fl = new FieldList();
        $fl->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);
        $fl->setFieldEntryTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDENTRYTABLE);

        $arr_all_fields = $fl->getFlatAllFields(false, false, $this->default_lang);

        // remove all fields
        foreach ($arr_all_fields as $id_field => $ftranslation) {
            $fl->removeFieldFromGroup($id_field, $id_folder);
        }

        $arr_all_fields_translation = array_flip($arr_all_fields);

        $arr_fields_value = [];
        // add selected fields
        foreach ($arr_fields as $field_translation => $field_data) {
            if (isset($arr_all_fields_translation[$field_translation])) {
                $field_id = $arr_all_fields_translation[$field_translation];
                $fl->addFieldToGroup($field_id,
                                        $id_folder,
                                        $field_data['mandatory']
                                    );
                $arr_fields_value[$field_id] = $field_data['fvalue'];
            } else {
                exit("Field non trovato: $field_translation");
            }
        }
        $fl->storeDirectFieldsForUser($id_folder, $arr_fields_value, false);
    }
}

/**
 * The configurator for docebousers connectors.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectorFormaOrgChartUI extends FormaConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public $directory = null;

    public function __construct(&$connector)
    {
        require_once _base_ . '/lib/lib.userselector.php';
        $this->connector = $connector;
        $this->directory = new UserSelector();
    }

    public function _get_base_name()
    {
        return 'doceboorgchartuiconfig';
    }

    public function get_old_name()
    {
        return $this->post_params['old_name'];
    }

    /**
     * All post fields are in array 'doceboorgchartuiconfig'.
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
            if (isset($arr_new_params['reset'])) {
                $this->post_params['tree'] = '';
            } elseif ($this->directory->isParseDataAvailable($post)) {
                $arr_selection = $this->directory->getSelection($post);
                $this->post_params['tree'] = implode(',', $arr_selection);
            }
            $this->directory->resetSelection([$this->post_params['tree']]);
            $this->post_params['org_chart_destination'] =
                            isset($arr_new_params['org_chart_destination'])
                            ? (int) $arr_new_params['org_chart_destination']
                            : $this->post_params['org_chart_destination'];
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
                $this->_set_step_info('1', '0', true, false, false);
            break;
            case '1':
                $this->_set_step_info('1', '0', false, true, true);
            break;
            case '2':
                $this->_set_step_info('2', '1', false, true, true);
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
            case '1':
                $out .= $this->_step1();
            break;
            case '2':
                $out .= $this->_step2();
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
        // ---- remove or not folders ----
        $out .= $this->form->getRadioSet($this->lang->def('_CANCELED_FOLDER'),
                                            $this->_get_base_name() . '_canceled',
                                            $this->_get_base_name() . '[canceled]',
                                            [$this->lang->def('_DONTDELETE') => '1',
                                                    $this->lang->def('_DEL') => '2', ],
                                            $this->post_params['canceled']);
        // ---- default lang ----
        $languages = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $lang_key = [];
        for ($index = 0; $index < count($languages); ++$index) {
            $lang_key[$languages[$index]] = $languages[$index];
        }
        $out .= $this->form->getDropdown($this->lang->def('_LANGUAGE'),
                                            $this->_get_base_name() . 'default_lang',
                                            $this->_get_base_name() . '[default_lang]',
                                            $lang_key,
                                            $this->post_params['default_lang']);

        return $out;
    }

    public function _step1()
    {
        /*$GLOBALS['page']->add($this->form->getLineBox( 	$this->lang->def('_NAME'),
                                            $this->post_params['name'] ));

*/
        // ---- the tree selector -----
        //$GLOBALS['page']->add($this->lang->def('_TREE_INSERT_FOLDER'));
        /*	$this->directory->show_user_selector = false;
            $this->directory->show_group_selector = false;
            $this->directory->show_orgchart_selector = true;
            $this->directory->show_orgchart_simple_selector = true;
            $this->directory->show_fncrole_selector = false;

            $this->directory->multi_choice = FALSE;
            $this->directory->selector_mode = TRUE;
            $this->directory->loadSelector(
                'index.php?modname=iotask&op=display&addconnection&gotab=connections',
                $this->lang->def('_TREE_INSERT_FOLDER'),
                '',
                false
            );//loadOrgChartView();
            // ---- add a button to reset selection -----
            $out = $this->form->getButton(	$this->_get_base_name().'_reset',
                                            $this->_get_base_name().'[reset]',
                                            $this->lang->def('_RESET'));  */

        $umodel = new UsermanagementAdm();
        $out = $this->form->getDropdown(
                Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'),
                $this->_get_base_name() . '_org_chart_destination',
                $this->_get_base_name() . '[org_chart_destination]',
                $umodel->getOrgChartDropdownList(),
                $this->post_params['org_chart_destination']
            );

        return $out;
    }

    public function _step2()
    {
        $out = $this->form->getLineBox($this->lang->def('_NAME'),
                                            $this->post_params['name']);

        $out .= $this->form->getTextfield($this->lang->def('_GROUP_FILTER'),
                                            $this->_get_base_name() . '_group',
                                            $this->_get_base_name() . '[group]',
                                            255,
                                            $this->post_params['group']);

        return $out;
    }
}

function doceboorgchart_factory()
{
    return new FormaConnectorFormaOrgChart([]);
}
