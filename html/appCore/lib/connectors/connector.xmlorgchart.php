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
 * class for define csv connection to data source.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectorXmlOrgChart extends FormaConnector
{
    public $curr_file = '';
    public $filename = '';
    public $dom_doc = null;
    public $folder_nodes = null;

    public $cols_descriptor = null;
    public $row_index = 0;
    public $readwrite = 0; // read = 1, write = 2, readwrite = 3
    public $last_error = '';
    public $name = '';
    public $description = '';

    public function __construct($params)
    {
        $this->set_config($params);
    }

    public function get_config()
    {
        return ['filepattern' => $this->filename,
                        'readwrite' => $this->readwrite,
                        'name' => $this->name,
                        'description' => $this->description, ];
    }

    public function set_config($params)
    {
        if (isset($params['filepattern'])) {
            $this->filename = $params['filepattern'];
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
    }

    public function get_configUI()
    {
        return new FormaConnectorXmlOrgChartUI($this);
    }

    /**
     * execute the connection to source.
     **/
    public function connect()
    {
        $this->close();
        /* search for file with pattern */
        $pat = str_replace(['*', '?'], ['.*', '.{1}'], $this->filename);
        $arr_files = $this->_preg_ls(DOCEBOIMPORT_BASEDIR, false, '/^' . $pat . '/');
        if (count($arr_files) == 0) {
            //$this->last_error = 'file not found: '.DOCEBOIMPORT_BASEDIR.$this->filename;
            return DOCEBO_IMPORT_NOTHINGTOPROCESS;
        }
        $this->curr_file = $arr_files[0];
        require_once _base_ . '/lib/lib.domxml.php';
        $this->dom_doc = new FormaDOMDocument();
        $this->dom_doc->loadXML(file_get_contents($this->curr_file));
        //$this->dom_doc = FormaDOMDocument::loadXML($this->curr_file);
        //$error = '';
        //$this->dom_doc = domxml_open_file($this->curr_file,DOMXML_LOAD_VALIDATING ,$error);

        if ($this->dom_doc === null) {
            $this->last_error = 'Error parsing xml org chart file: ' . DOCEBOIMPORT_BASEDIR . $this->curr_file;

            return false;
        }

        $this->folder_nodes = $this->dom_doc->getElementsByTagName('folder');

        return true;
    }

    /**
     * execute the close of the connection.
     **/
    public function close()
    {
        if ($this->dom_doc !== null) {
            $this->dom_doc = null;
            rename($this->curr_file, DOCEBOIMPORT_BASEDIR . 'processed' . basename($this->curr_file));
        }
        $this->row_index = 0;

        return true;
    }

    /**
     * Return the type of the connector.
     **/
    public function get_type_name()
    {
        return 'xmlorgchart-connector';
    }

    /**
     * Return the description of the connector.
     **/
    public function get_type_description()
    {
        return 'connector to xml organization chart descriptor';
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
        return (bool) ($this->readwrite & 1);
    }

    public function is_writeonly()
    {
        return (bool) ($this->readwrite & 2);
    }

    public function is_raw_producer()
    {
        return true;
    }

    public function _get_folder_data()
    {
        $arr_data = [];
        $elem_folder = $this->folder_nodes->item($this->row_index);
        if ($elem_folder === null) {
            return false;
        }
        // extract folder title in other languages
        $title_nodes = $elem_folder->getElementsByTagName('title');
        $arr_data['lang_titles'] = [];
        for ($indexLang = 0; $indexLang < $title_nodes->length; ++$indexLang) {
            $elem_title = $title_nodes->item($indexLang);
            $arr_data['lang_titles'][$elem_title->getAttribute('lang')] = $elem_title->firstChild->nodeValue;
        }
        // extract folder fields
        $child_list = $elem_folder->childNodes;
        $elem_fields = null;
        for ($indexChild = 0; $indexChild < $child_list->length; ++$indexChild) {
            $curr_child = $child_list->item($indexChild);
            $nodeType = $curr_child->nodeType;
            if ($nodeType == XML_ELEMENT_NODE) {
                $tagName = $curr_child->tagName;
                if ($tagName == 'fields') {
                    $elem_fields = $curr_child;
                    break;
                }
            }
        }
        if ($elem_fields !== null) {
            $field_nodes = $elem_fields->getElementsByTagName('field');
            $arr_data['custom_fields'] = [];
            for ($indexField = 0; $indexField < $field_nodes->length; ++$indexField) {
                $elem_field = $field_nodes->item($indexField);
                $field_name = false;
                $field_value = false;
                $field_mandatory = ($elem_field->getAttribute('mandatory') == 'yes');
                $child_field = $elem_field->firstChild;
                $nodeType = $child_field->nodeType;

                if ($nodeType == XML_ELEMENT_NODE) {
                    $tagName = $child_field->tagName;
                } else {
                    $tagName = '';
                }

                while ($child_field !== null && $child_field !== false) {
                    if ($child_field->nodeType == XML_ELEMENT_NODE) {
                        switch ($tagName) {
                            case 'name':
                                $field_name = $child_field->textContent;
                            break;
                            case 'value':
                                $field_value = $child_field->textContent;
                            break;
                        }
                    }
                    $child_field = $child_field->nextSibling;
                    $nodeType = $child_field->nodeType;
                    if ($nodeType == XML_ELEMENT_NODE) {
                        $tagName = $child_field->tagName;
                    } else {
                        $tagName = '';
                    }
                }
                if ($field_name !== false && $field_value !== false) {
                    $arr_data['custom_fields'][$field_name] = ['fvalue' => $field_value, 'mandatory' => $field_mandatory];
                }
            }
        }
        $path_elem = [];
        $path_elem[] = $elem_folder->getAttribute('name');
        $arr_data['code'] = $elem_folder->getAttribute('code');
        $parent = $elem_folder->parentNode;
        while ($parent->tagName == 'folder') {
            $path_elem[] = $parent->getAttribute('name');
            $parent = $parent->parentNode;
        }
        $path_elem = array_reverse($path_elem);
        $path = implode('/', $path_elem);
        $arr_data['path'] = $path;

        return $arr_data;
    }

    public function get_first_row()
    {
        if ($this->dom_doc === null) {
            return false;
        }
        $this->row_index = 0;

        return $this->_get_folder_data();
    }

    public function get_next_row()
    {
        if ($this->dom_doc === null) {
            return false;
        }
        ++$this->row_index;

        return $this->_get_folder_data();
    }

    public function is_eof()
    {
        return $this->row_index >= $this->folder_nodes->length;
    }

    public function get_row_index()
    {
        return $this->row_index;
    }

    public function get_tot_mandatory_cols()
    {
        return 0;
    }

    public function add_row($row = null, $pk = null)
    {
        return false;
    }

    public function get_error()
    {
        return $this->last_error;
    }

    public function _preg_ls($path = '.', $rec = false, $pat = '/.*/')
    {
        $pat = preg_replace('|(/.*/[^S]*)|s', '\\1S', $pat);
        while (substr($path, -1, 1) == '/') {
            $path = substr($path, 0, -1);
        }
        if (!is_dir($path)) {
            $path = dirname($path);
        }
        if ($rec !== true) {
            $rec = false;
        }
        $d = dir($path);
        $ret = [];
        while (false !== ($e = $d->read())) {
            if (($e == '.') || ($e == '..')) {
                continue;
            }
            if ($rec && is_dir($path . '/' . $e)) {
                $ret = array_merge($ret, $this->_preg_ls($path . '/' . $e, $rec, $pat));
                continue;
            }
            if (!preg_match($pat, $e)) {
                continue;
            }
            if (strncmp($e, 'processed', 9) === 0) {
                continue;
            }
            $ret[] = $path . '/' . $e;
        }

        return (empty($ret) && preg_match($pat, basename($path))) ? [$path . '/'] : $ret;
    }
}

/**
 * The configurator for csv connectors.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectorXmlOrgChartUI extends FormaConnectorUI
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
        return 'xmlocuiconfig';
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
            if ($this->post_params['filepattern'] == '') {
                $this->post_params['filepattern'] = $this->lang->def('_FILEPATTERN_EXAMPLE');
            }
        } else {
            // get previous values
            $this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
            $arr_new_params = $post[$this->_get_base_name()];
            // overwrite with the new posted values
            foreach ($arr_new_params as $key => $val) {
                if ($key != 'memory') {
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
            case '1':
                $out .= $this->_step1();
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

        // ---- file pattern ----
        $out .= $this->form->getTextfield($this->lang->def('_FILEPATTERN'),
                                            $this->_get_base_name() . '_filepattern',
                                            $this->_get_base_name() . '[filepattern]',
                                            255,
                                            $this->post_params['filepattern']);

        return $out;
    }
}

function xmlorgchart_factory()
{
    return new FormaConnectorXmlOrgChart([]);
}
